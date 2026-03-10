<?php
// includes/functions-db.php
// ======================================
// ✅ Funksi Database Tambahan - e-Prestasi
// - Support Sybase: ehrmdb, ehrmdb_dev, stafdb, student
// - Auto pilih DSN vs DBLIB ikut driver tersedia
// - Fallback + ping connection
// - Compat untuk PHP lama (tanpa str_ends_with)
// ======================================

declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';

/* ----------------------------------------------------
 * 🔧 Polyfill: str_ends_with (PHP < 8)
 * ---------------------------------------------------- */
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        if ($needle === '') return true;
        $len = strlen($needle);
        return substr($haystack, -$len) === $needle;
    }
}

/* ----------------------------------------------------
 * 🔐 HELPER TRANSAKSI (ODBC-safe untuk SAP ASE)
 * - ODBC (Windows/ASE): guna T-SQL BEGIN/COMMIT/ROLLBACK
 * - Selain ODBC (cth dblib): guna transaksi PDO biasa
 * ---------------------------------------------------- */
if (!function_exists('txBegin')) {
    function txBegin(\PDO $pdo) /*: void*/ { // (avoid 'void' for PHP<7.1)
        $drv = strtolower((string)$pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
        if ($drv === 'odbc') {
            $pdo->exec('BEGIN TRAN');
        } else {
            if (!$pdo->inTransaction()) { $pdo->beginTransaction(); }
        }
    }
}
if (!function_exists('txCommit')) {
    function txCommit(\PDO $pdo) /*: void*/ {
        $drv = strtolower((string)$pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
        if ($drv === 'odbc') {
            $pdo->exec('COMMIT TRAN');
        } else {
            if ($pdo->inTransaction()) { $pdo->commit(); }
        }
    }
}
if (!function_exists('txRollback')) {
    function txRollback(\PDO $pdo) /*: void*/ {
        $drv = strtolower((string)$pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
        if ($drv === 'odbc') {
            $pdo->exec('ROLLBACK TRAN');
        } else {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
        }
    }
}

/**
 * Pulangkan key config aktif untuk Sybase, driver-aware (ODBC/DBLIB).
 * Baca toggle dari configuration/config_db_active.json:
 *  {
 *    "ehrmdb": true,
 *    "ehrmdb_dev": false,
 *    "stafdb": false
 *  }
 */
function get_active_sybase_key(): string
{
    $drivers  = \PDO::getAvailableDrivers();          // contoh: ['mysql','dblib','odbc']
    $hasOdbc  = in_array('odbc',  $drivers, true);
    $hasDblib = in_array('dblib', $drivers, true);

    // Logical DB yang digunakan sekarang
    $map = [
        'ehrmdb'     => ['dsn' => 'sybase_ehrmdb_dsn',     'dblib' => 'sybase_ehrmdb_dblib'],
        'ehrmdb_dev' => ['dsn' => 'sybase_ehrmdb_dev_dsn', 'dblib' => 'sybase_ehrmdb_dev_dblib'],
        'stafdb'     => ['dsn' => 'sybase_stafdb_dsn',     'dblib' => 'sybase_stafdb_dblib'],
    ];

    // Cari logical active dari file
    $activeLogical = 'ehrmdb'; // default
    $configFile = __DIR__ . '/../configuration/config_db_active.json';
    if (is_file($configFile)) {
        $cfg = json_decode((string)file_get_contents($configFile), true) ?: [];
        foreach (['ehrmdb','ehrmdb_dev','stafdb'] as $logical) {
            if (!empty($cfg[$logical]) && isset($map[$logical])) {
                $activeLogical = $logical;
                break;
            }
        }
    }

    // Prefer ikut OS (Windows→ODBC/DSN, selainnya→DBLIB)
    $prefer = (PHP_OS_FAMILY === 'Windows') ? 'dsn' : 'dblib';

    $primary   = $map[$activeLogical][$prefer];
    $alternate = $map[$activeLogical][$prefer === 'dsn' ? 'dblib' : 'dsn'];

    // Jika driver untuk pilihan utama tiada, guna alternatif
    if ($prefer === 'dsn'   && !$hasOdbc)  { $primary = $alternate; }
    if ($prefer === 'dblib' && !$hasDblib) { $primary = $alternate; }

    // Cuba ping primary; kalau gagal cuba alternate; jika dua-dua gagal, pulangkan primary (biar caller lapor error sebenar)
    try {
        $pdoTmp = Database::getInstance($primary)->getConnection();
        $pdoTmp->query('select 1');
        return $primary;
    } catch (\Throwable $e) {
        try {
            $pdoTmp2 = Database::getInstance($alternate)->getConnection();
            $pdoTmp2->query('select 1');
            return $alternate;
        } catch (\Throwable $e2) {
            return $primary;
        }
    }
}

/**
 * Dapatkan PDO Sybase aktif (ikut driver-aware key).
 * Throw exception kalau gagal — senang ditangkap caller untuk mesej jelas.
 */
function get_active_sybase_pdo(): \PDO
{
    $key = get_active_sybase_key();

    try {
        $pdo = Database::getInstance($key)->getConnection();
        // Ping
        $pdo->query('select 1');
        return $pdo;
    } catch (\Throwable $e) {
        // Cuba swap DSN <-> DBLIB sekali lagi sebagai guard
        $swap = str_ends_with($key, '_dsn') ? str_replace('_dsn', '_dblib', $key)
                                            : str_replace('_dblib', '_dsn', $key);
        try {
            $pdo2 = Database::getInstance($swap)->getConnection();
            $pdo2->query('select 1');
            return $pdo2;
        } catch (\Throwable $e2) {
            throw new \RuntimeException('Gagal sambung Sybase (driver/DSN): ' . $e->getMessage());
        }
    }
}

/**
 * Auto-detect Sybase EHRMDB (shortcut, ikut driver & toggle aktif global jika ada).
 */
function getSybaseEHRMDB(): ?\PDO
{
    if (isset($GLOBALS['sybase_active']['ehrmdb']) && !$GLOBALS['sybase_active']['ehrmdb']) {
        return null;
    }

    $drivers  = \PDO::getAvailableDrivers();
    $hasOdbc  = in_array('odbc',  $drivers, true);
    $hasDblib = in_array('dblib', $drivers, true);

    $primary   = (PHP_OS_FAMILY === 'Windows') ? 'sybase_ehrmdb_dsn' : 'sybase_ehrmdb_dblib';
    $alternate = str_ends_with($primary, '_dsn') ? 'sybase_ehrmdb_dblib' : 'sybase_ehrmdb_dsn';

    if (str_ends_with($primary, '_dsn') && !$hasOdbc)    { $primary = $alternate; }
    if (str_ends_with($primary, '_dblib') && !$hasDblib) { $primary = $alternate; }

    try {
        $pdo = Database::getInstance($primary)->getConnection();
        $pdo->query('select 1');
        return $pdo;
    } catch (\Throwable $e) {
        try {
            $pdo2 = Database::getInstance($alternate)->getConnection();
            $pdo2->query('select 1');
            return $pdo2;
        } catch (\Throwable $e2) {
            return null;
        }
    }
}

/**
 * Auto-detect Sybase STAFDB (shortcut)
 */
function getSybaseSTAFDB(): ?\PDO
{
    if (isset($GLOBALS['sybase_active']['stafdb']) && !$GLOBALS['sybase_active']['stafdb']) {
        return null;
    }

    $drivers  = \PDO::getAvailableDrivers();
    $hasOdbc  = in_array('odbc',  $drivers, true);
    $hasDblib = in_array('dblib', $drivers, true);

    $primary   = (PHP_OS_FAMILY === 'Windows') ? 'sybase_stafdb_dsn' : 'sybase_stafdb_dblib';
    $alternate = str_ends_with($primary, '_dsn') ? 'sybase_stafdb_dblib' : 'sybase_stafdb_dsn';

    if (str_ends_with($primary, '_dsn') && !$hasOdbc)    { $primary = $alternate; }
    if (str_ends_with($primary, '_dblib') && !$hasDblib) { $primary = $alternate; }

    try {
        $pdo = Database::getInstance($primary)->getConnection();
        $pdo->query('select 1');
        return $pdo;
    } catch (\Throwable $e) {
        try {
            $pdo2 = Database::getInstance($alternate)->getConnection();
            $pdo2->query('select 1');
            return $pdo2;
        } catch (\Throwable $e2) {
            return null;
        }
    }
}

/**
 * Auto-detect Sybase EHRMDB_DEV (shortcut)
 */
function getSybaseEHRMDBDev(): ?\PDO
{
    if (isset($GLOBALS['sybase_active']['ehrmdb_dev']) && !$GLOBALS['sybase_active']['ehrmdb_dev']) {
        return null;
    }

    $drivers  = \PDO::getAvailableDrivers();
    $hasOdbc  = in_array('odbc',  $drivers, true);
    $hasDblib = in_array('dblib', $drivers, true);

    $primary   = (PHP_OS_FAMILY === 'Windows') ? 'sybase_ehrmdb_dev_dsn' : 'sybase_ehrmdb_dev_dblib';
    $alternate = str_ends_with($primary, '_dsn') ? 'sybase_ehrmdb_dev_dblib' : 'sybase_ehrmdb_dev_dsn';

    if (str_ends_with($primary, '_dsn') && !$hasOdbc)    { $primary = $alternate; }
    if (str_ends_with($primary, '_dblib') && !$hasDblib) { $primary = $alternate; }

    try {
        $pdo = Database::getInstance($primary)->getConnection();
        $pdo->query('select 1');
        return $pdo;
    } catch (\Throwable $e) {
        try {
            $pdo2 = Database::getInstance($alternate)->getConnection();
            $pdo2->query('select 1');
            return $pdo2;
        } catch (\Throwable $e2) {
            return null;
        }
    }
}

/**
 * Auto-detect Sybase STUDENT (shortcut)
 */
function getSybaseStudent(): ?\PDO
{
    $drivers  = \PDO::getAvailableDrivers();
    $hasOdbc  = in_array('odbc',  $drivers, true);
    $hasDblib = in_array('dblib', $drivers, true);

    $primary   = (PHP_OS_FAMILY === 'Windows') ? 'sybase_student_dsn' : 'sybase_student_dblib';
    $alternate = str_ends_with($primary, '_dsn') ? 'sybase_student_dblib' : 'sybase_student_dsn';

    if (str_ends_with($primary, '_dsn') && !$hasOdbc)    { $primary = $alternate; }
    if (str_ends_with($primary, '_dblib') && !$hasDblib) { $primary = $alternate; }

    try {
        $pdo = Database::getInstance($primary)->getConnection();
        $pdo->query('select 1');
        return $pdo;
    } catch (\Throwable $e) {
        try {
            $pdo2 = Database::getInstance($alternate)->getConnection();
            $pdo2->query('select 1');
            return $pdo2;
        } catch (\Throwable $e2) {
            return null;
        }
    }
}

/**
 * Uji sambungan semua DB dalam configuration/db_config.php
 * Pulangkan array ringkas status.
 */
function testAllDatabaseConnections(): array
{
    $results = [];
    $configsFile = __DIR__ . '/../configuration/db_config.php';
    if (!is_file($configsFile)) return ['error' => 'db_config.php not found'];

    $configs = require $configsFile;

    foreach ($configs as $key => $cfg) {
        try {
            $pdo = Database::getInstance($key)->getConnection();
            if ($pdo) { $pdo->query('select 1'); }
            $status = '✅ Berjaya';
        } catch (\Throwable $e) {
            $status = '❌ ' . $e->getMessage();
        }
        $results[$key] = $status;
    }
    return $results;
}

/**
 * Info diagnostik pantas — boleh dipanggil bila perlu.
 */
function sybase_diag_info(): array
{
    return [
        'os'       => PHP_OS_FAMILY,
        'php'      => PHP_VERSION,
        'ini'      => php_ini_loaded_file(),
        'drivers'  => \PDO::getAvailableDrivers(),
        'active'   => get_active_sybase_key(),
        'testAll'  => testAllDatabaseConnections(),
    ];
}
