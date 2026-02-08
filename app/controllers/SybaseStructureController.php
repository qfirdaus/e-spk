<?php
// controllers/SybaseStructureController.php
// =======================================================
// Sybase Structure Inspector (guna Database singleton aktif)
// - listObjects($owner)  : senarai TABLE/VIEW (boleh tapis owner)
// - listOwners()         : senarai owner (schema) unik
// - spHelp(), spHelptext(): metadata & definisi (ODBC/DBLIB-safe)
// - spHelpIndex()        : senarai index (ada fallback untuk ODBC)
// - Caching senarai objek dalam SESSION (TTL = 300s)
// - PATCH: buang PARSENAME() (Sybase tiada). Guna :owner + :name.
// - PATCH: TABLE+ODBC -> fallbackColumns() supaya confirm keluar struktur.
// =======================================================
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../classes/Database.php';

final class SybaseStructureController
{
    public ?PDO $pdo_sybase = null;

    public string $lang = 'ms';
    public array $profile = [];

    /** @var array<int, array<int, array<string, mixed>>> */
    public array $helpSets = [];
    public string $helpText = '';

    /** @var array<int, array<string, mixed>> */
    public array $helpIndex = []; // sp_helpindex result

    /** @var string base key sybase aktif (cth: sybase_ehrmdb_dev) */
    public string $sybaseBaseKey = '';

    /** @var array<int, array{full:string,name:string,owner:string,type:string}> */
    public array $objects = []; // utk dropdown list objek

    /** @var array<int, string> */
    public array $owners = []; // utk dropdown owner/schema

    public string $sybaseError = '';

    /** TTL cache senarai objek (dalam saat) */
    public int $cacheTtlSeconds = 300;

    public function __construct()
    {
        $this->lang    = $_SESSION['lang'] ?? 'ms';
        $this->profile = $_SESSION['profile'] ?? [];
    }

    /**
     * Entry:
     *  - Init Sybase aktif
     *  - Muat owners & objects (dengan penapis owner jika diberi)
     *  - (Opsyen) jalankan sp_help / sp_helptext / sp_helpindex untuk $obj
     */
    public function run(?string $obj, ?string $owner = null, bool $refresh = false): void
    {
        $this->initActiveSybase(null);

        // Senarai owner untuk dropdown
        $this->owners  = $this->listOwners();

        // Senarai objek ikut owner (cached)
        $this->objects = $this->listObjects($owner, $refresh);

        if ($obj && $this->pdo_sybase) {
            $safe = $this->sanitizeObject($obj);
            $full = $this->normalizeFull($safe);

            $type = $this->getObjectType($full); // 'U' (TABLE), 'V' (VIEW), dll
            $drv  = $this->driver();

            if ($type === 'U' && $drv === 'odbc') {
                // TABLE + ODBC → bypass sp_help; confirm keluarkan struktur
                $this->helpSets  = [ $this->fallbackColumns($full) ];
                $this->helpText  = ''; // TABLE takde definisi text
                $this->helpIndex = $this->spHelpIndex($full);
            } else {
                // VIEW / DBLIB → behaviour asal
                $this->helpSets  = $this->spHelp($safe);
                $this->helpText  = $this->spHelpText($safe);
                $this->helpIndex = $this->spHelpIndex($safe);
            }
        }
    }

    /**
     * Inisialisasi sambungan Sybase **aktif** (1 sahaja)
     */
    public function initActiveSybase(?string $base = null): void
    {
        try {
            if ($base && trim($base) !== '') {
                $chosen = trim($base);
                $this->sybaseBaseKey = $chosen;
                $this->pdo_sybase = Database::getInstance($chosen)->getConnection();
            } else {
                $this->pdo_sybase = sybase_pdo(); // helper dari init.php
                $this->sybaseBaseKey = defined('SYBASE_ACTIVE_BASE') ? (string)SYBASE_ACTIVE_BASE : 'sybase_ehrmdb';
            }
            $this->pdo_sybase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $e) {
            try {
                $chosen = defined('SYBASE_ACTIVE_BASE') ? (string)SYBASE_ACTIVE_BASE : 'sybase_ehrmdb';
                $this->sybaseBaseKey = $chosen;
                $this->pdo_sybase = Database::getInstance($chosen)->getConnection();
                $this->pdo_sybase->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Throwable $e2) {
                $this->pdo_sybase = null;
                $this->sybaseError = 'Gagal sambung Sybase: ' . $e2->getMessage();
            }
        }
    }

    /** Senarai OWNER (schema) unik daripada sysobjects/sysusers */
    public function listOwners(): array
    {
        $this->assertPdo();

        $sql = "
            SELECT name
            FROM sysusers
            WHERE suid IS NOT NULL
              AND uid >= 0
              AND name NOT IN ('dbo','guest','public','sys','sa')
            ORDER BY name
        ";

        try {
            $rows = $this->pdo_sybase->query($sql)->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $owners = array_values(array_map('strval', $rows));
            // Tambah 'dbo' di awal senarai supaya mudah dicapai
            array_unshift($owners, 'dbo');
            $owners = array_values(array_unique($owners));
            return $owners;
        } catch (Throwable $e) {
            // fallback minimum: distinct dari sysobjects
            try {
                $rows = $this->pdo_sybase->query("SELECT DISTINCT user_name(uid) FROM sysobjects ORDER BY 1")->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $owners = array_values(array_map(fn($v)=> $v ?: 'dbo', $rows));
                if (!in_array('dbo', $owners, true)) array_unshift($owners, 'dbo');
                return $owners;
            } catch (Throwable $e2) {
                $this->sybaseError = 'Gagal ambil owners: ' . $e->getMessage();
                return ['dbo'];
            }
        }
    }

    /**
     * Senarai TABLE/VIEW dari DB aktif
     * @param ?string $owner  Contoh 'dbo' (null = semua owner)
     * @param bool    $refresh Paksa clear cache
     * @return array<int, array{full:string,name:string,owner:string,type:string}>
     */
    public function listObjects(?string $owner = null, bool $refresh = false): array
    {
        $this->assertPdo();

        $ownerKey = $owner ? strtolower(trim($owner)) : '*';
        $cacheKey = $this->cacheKey($ownerKey);

        if ($refresh) {
            $this->cacheForget($cacheKey);
        } else {
            $cached = $this->cacheGet($cacheKey);
            if ($cached !== null) return $cached;
        }

        $params = [];
        $whereOwner = '';
        if ($owner && $ownerKey !== '') {
            $whereOwner = "AND user_name(o.uid) = :owner";
            $params[':owner'] = $owner;
        }

        $sql = "
            SELECT
              user_name(o.uid) AS owner,
              o.name           AS name,
              CASE o.type
                WHEN 'U' THEN 'TABLE'
                WHEN 'V' THEN 'VIEW'
                ELSE o.type
              END AS type
            FROM sysobjects o
            WHERE o.type IN ('U','V')
              AND o.name NOT LIKE 'sys%'
              {$whereOwner}
            ORDER BY type, owner, name
        ";

        try {
            $stmt = $this->pdo_sybase->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $out = [];
            foreach ($rows as $r) {
                $own = (string)($r['owner'] ?? 'dbo');
                $nam = (string)($r['name']  ?? '');
                $typ = (string)($r['type']  ?? '');
                if ($nam === '') continue;
                $out[] = [
                    'full'  => $own . '.' . $nam,
                    'name'  => $nam,
                    'owner' => $own,
                    'type'  => $typ,
                ];
            }

            $this->cacheSet($cacheKey, $out);
            return $out;

        } catch (Throwable $e) {
            $this->sybaseError = 'Gagal ambil objek: ' . $e->getMessage();
            return [];
        }
    }

    /** Dapatkan jenis objek: 'U'=TABLE, 'V'=VIEW, dll */
    private function getObjectType(string $fullName): ?string
    {
        $this->assertPdo();
        [$owner, $name] = $this->splitFull($fullName);
        $sql = "
            SELECT o.type
            FROM sysobjects o
            WHERE user_name(o.uid) = :owner
              AND o.name           = :name
        ";
        try {
            $s = $this->pdo_sybase->prepare($sql);
            $s->execute([':owner' => $owner, ':name' => $name]);
            $t = $s->fetchColumn();
            return $t ? (string)$t : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** sp_help: pulangkan semua result set (ODBC-safe + TABLE fallback) */
    public function spHelp(string $objectName): array
    {
        $this->assertPdo();
        $full = $this->normalizeFull($objectName);

        // Jika TABLE + ODBC, terus fallback (lebih stabil & pasti keluar struktur)
        if ($this->driver() === 'odbc') {
            $t = $this->getObjectType($full);
            if ($t === 'U') {
                return [ $this->fallbackColumns($full) ];
            }
        }

        $sql  = "exec sp_help '{$full}'";
        try {
            return $this->execMultiRowset($sql);
        } catch (\PDOException $e) {
            if ($this->isDescriptorError($e) && $this->driver() === 'odbc') {
                return [ $this->fallbackColumns($full) ];
            }
            $this->sybaseError = 'sp_help gagal: ' . $e->getMessage();
            return [];
        } catch (\Throwable $e) {
            $this->sybaseError = 'sp_help gagal: ' . $e->getMessage();
            return [];
        }
    }

    /** sp_helptext: gabungkan definisi (view/proc) — ODBC baca syscomments */
    public function spHelpText(string $objectName): string
    {
        $this->assertPdo();
        $full = $this->normalizeFull($objectName);

        if ($this->driver() === 'odbc') {
            return $this->readTextFromSyscomments($full);
        }

        // DBLIB: cuba sp_helptext
        try {
            $sets = $this->execMultiRowset("exec sp_helptext '{$full}'");
            $buf  = [];
            foreach ($sets as $rows) {
                foreach ($rows as $r) {
                    $buf[] = (string)($r['text'] ?? $r['Text'] ?? $r['line'] ?? $r['Line'] ?? implode('', $r));
                }
            }
            $out = trim(implode("", $buf));
            if ($out !== '') return $out;
            // fallback juga kalau kosong
            return $this->readTextFromSyscomments($full);
        } catch (\PDOException $e) {
            if ($this->isDescriptorError($e) && $this->driver() === 'odbc') {
                return $this->readTextFromSyscomments($full);
            }
            return $this->readTextFromSyscomments($full);
        } catch (\Throwable $e) {
            return $this->readTextFromSyscomments($full);
        }
    }

    /** sp_helpindex: senarai index untuk jadual — ODBC fallback */
    public function spHelpIndex(string $objectName): array
    {
        $this->assertPdo();
        $full = $this->normalizeFull($objectName);
        try {
            $sets = $this->execMultiRowset("exec sp_helpindex '{$full}'");
            return $sets[0] ?? [];
        } catch (\PDOException $e) {
            if ($this->isDescriptorError($e) && $this->driver() === 'odbc') {
                return $this->fallbackIndexes($full);
            }
            // View memang tiada indeks → anggap kosong
            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Utility */
    public function sanitizeObject(string $name): string
    {
        $name = trim($name);
        if ($name === '' || !preg_match('/^[A-Za-z0-9_.]+$/', $name)) {
            throw new InvalidArgumentException('Nama objek tidak sah.');
        }
        return $name;
    }

    private function assertPdo(): void
    {
        if (!$this->pdo_sybase) {
            throw new RuntimeException('Tiada sambungan Sybase. ' . ($this->sybaseError ?: ''));
        }
    }

    private function driver(): string
    {
        try { return strtolower((string)$this->pdo_sybase->getAttribute(PDO::ATTR_DRIVER_NAME)); }
        catch (\Throwable $e) { return ''; }
    }

    private function normalizeFull(string $full): string
    {
        $full = trim($full);
        if (strpos($full, '.') === false) return 'dbo.' . $full;
        return $full;
    }

    /** Split owner.name → [owner, name] */
    private function splitFull(string $full): array
    {
        $full = trim($full);
        if ($full === '') return ['dbo',''];
        $pos = strpos($full, '.');
        if ($pos === false) return ['dbo', $full];
        return [substr($full, 0, $pos), substr($full, $pos + 1)];
    }

    /** Loop rowset satu-satu — elak isu ODBC bila multi-rowset (sp_help*) */
    private function execMultiRowset(string $sql): array
    {
        $stmt = $this->pdo_sybase->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->execute();

        $all = [];
        do {
            $cols = $stmt->columnCount();
            if ($cols <= 0) { continue; }

            $rows = [];
            while (true) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row === false) break;
                $rows[] = $row;
            }
            if ($rows) $all[] = $rows;

        } while (@$stmt->nextRowset()); // suppress warning jika driver tak support

        return $all;
    }

    private function isDescriptorError(\PDOException $e): bool
    {
        $msg = strtolower($e->getMessage());
        return str_contains($msg, 'invalid descriptor index')
            || str_contains($msg, '07009')
            || str_contains($msg, 'sqlfetchscroll');
    }

    /* ========================= Fallbacks untuk ODBC ========================= */

    /** Sekurang-kurangnya pulangkan senarai kolum (mirip seksyen Columns dalam sp_help) */
    private function fallbackColumns(string $fullName): array
    {
        [$owner, $name] = $this->splitFull($fullName);
        $sql = "
            SELECT
                c.name   AS Column_name,
                t.name   AS Type,
                c.length AS Length,
                c.prec   AS Prec,
                c.scale  AS Scale,
                CASE WHEN (c.status & 8) = 8 THEN 0 ELSE 1 END AS Nulls
            FROM sysobjects o
            JOIN syscolumns c ON c.id = o.id
            JOIN systypes   t ON t.usertype = c.usertype
            WHERE user_name(o.uid) = :owner
              AND o.name           = :name
            ORDER BY c.colid
        ";
        try {
            $s = $this->pdo_sybase->prepare($sql);
            $s->execute([':owner' => $owner, ':name' => $name]);
            $rows = $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$r) {
                $r['Nulls'] = (string)((int)$r['Nulls']);
            }
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Fallback ringkas indeks (nama & jenis sahaja) */
    private function fallbackIndexes(string $fullName): array
    {
        [$owner, $name] = $this->splitFull($fullName);
        $sql = "
            SELECT i.name AS index_name,
                   CASE WHEN i.indid = 1 THEN 'clustered' ELSE 'nonclustered' END AS index_type
            FROM sysobjects o
            JOIN sysindexes i ON i.id = o.id
            WHERE user_name(o.uid) = :owner
              AND o.name           = :name
              AND i.indid BETWEEN 1 AND 254
              AND (i.status & 2048) = 0
            ORDER BY i.indid
        ";
        try {
            $s = $this->pdo_sybase->prepare($sql);
            $s->execute([':owner' => $owner, ':name' => $name]);
            return $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Bacaan definisi (view/proc) terus dari syscomments (lebih stabil untuk ODBC) */
    private function readTextFromSyscomments(string $fullName): string
    {
        [$owner, $name] = $this->splitFull($fullName);
        $sql = "
            SELECT c.text
            FROM sysobjects o
            JOIN syscomments c ON c.id = o.id
            WHERE user_name(o.uid) = :owner
              AND o.name           = :name
            ORDER BY c.number, c.colid
        ";
        try {
            $s = $this->pdo_sybase->prepare($sql);
            $s->execute([':owner' => $owner, ':name' => $name]);
            $buf = [];
            while ($t = $s->fetchColumn()) {
                $buf[] = (string)$t;
            }
            return trim(implode("", $buf));
        } catch (\Throwable $e) {
            return '';
        }
    }

    // =========================
    //       SESSION CACHE
    // =========================
    private function cacheNs(): string
    {
        return '__syb_cache';
    }

    private function cacheKey(string $ownerKey = '*'): string
    {
        $base = $this->sybaseBaseKey ?: (defined('SYBASE_ACTIVE_BASE') ? (string)SYBASE_ACTIVE_BASE : 'sybase_ehrmdb');
        return 'objects:' . $base . ':' . $ownerKey;
    }

    /** @return array<int, array{full:string,name:string,owner:string,type:string}>|null */
    private function cacheGet(string $key): ?array
    {
        if (empty($_SESSION[$this->cacheNs()][$key])) return null;
        $entry = $_SESSION[$this->cacheNs()][$key];
        if (!is_array($entry) || !isset($entry['ts'], $entry['data'])) return null;

        if ((time() - (int)$entry['ts']) > $this->cacheTtlSeconds) {
            unset($_SESSION[$this->cacheNs()][$key]);
            return null;
        }
        return is_array($entry['data']) ? $entry['data'] : null;
    }

    /** @param array<int, array{full:string,name:string,owner:string,type:string}> $data */
    private function cacheSet(string $key, array $data): void
    {
        $_SESSION[$this->cacheNs()][$key] = [
            'ts'   => time(),
            'data' => $data,
        ];
    }

    private function cacheForget(string $key): void
    {
        unset($_SESSION[$this->cacheNs()][$key]);
    }
}
