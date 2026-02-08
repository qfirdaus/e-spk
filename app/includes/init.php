<?php
// ===============================================
// ✅ INIT Sistem e-Prestasi (header-safe & no-legacy echo)
// ===============================================

// (Opsyen debug sementara; tukar ke false bila siap)
// define('AUDIT_DEBUG', true);

// 1) Session + (optional) output buffering
// Sanitize incoming session id from cookie to avoid PHP warnings when clients
// supply an invalid or malformed session id (e.g., pasted values). Only allow
// characters A-Z a-z 0-9 - and , which are permitted by PHP session id rules.
if (isset($_COOKIE[session_name()])) {
    $rawSid = (string)$_COOKIE[session_name()];
    if (!preg_match('/^[A-Za-z0-9\-,]+$/', $rawSid)) {
        // Remove invalid cookie to allow PHP to generate a fresh session id
        unset($_COOKIE[session_name()]);
        // Also clear from $_REQUEST/$_SERVER to be safe
        if (isset($_REQUEST[session_name()])) unset($_REQUEST[session_name()]);
        if (isset($_GET[session_name()])) unset($_GET[session_name()]);
        if (isset($_POST[session_name()])) unset($_POST[session_name()]);
    }
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!headers_sent()) {
    ob_start(); // elak "headers already sent" semasa redirect lang
}

// Force a temp directory inside project to satisfy open_basedir (avoid C:\Windows\TEMP)
$__safeTemp = realpath(__DIR__ . '/../cache/tmp') ?: (__DIR__ . '/../cache/tmp');
if (!is_dir($__safeTemp)) {
    @mkdir($__safeTemp, 0777, true);
}
ini_set('sys_temp_dir', $__safeTemp);

// Pastikan semua halaman dihantar sebagai UTF-8 supaya pelayar tidak
// salah tafsirkan bait -- ini juga membantu mencegah mojibake pada teks
// yang mempunyai watak seperti en-dash dan ellipsis.
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// 2) LANG: proses ?lang=... PALING AWAL, sebelum sebarang output
function __current_url_without_lang(): string {
    $uri   = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path  = $parts['path'] ?? '/';
    $qsArr = [];
    if (!empty($parts['query'])) parse_str($parts['query'], $qsArr);
    unset($qsArr['lang']);
    $q = http_build_query($qsArr);
    return $path . ($q ? ('?' . $q) : '');
}
if (isset($_GET['lang'])) {
    $allowed = ['ms','en','zh','ta'];
    $v = (string)$_GET['lang'];
    if (in_array($v, $allowed, true)) {
        $_SESSION['lang'] = $v;
    }
    header('Location: ' . __current_url_without_lang(), true, 302);
    exit;
}

// 3) Requires (tiada output)
require_once __DIR__ . '/../setting/function.php';
require_once __DIR__ . '/../classes/HelperLoader.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Config.php';

// 4) Autoload helpers dalam setting/helper
$loader = new HelperLoader(__DIR__ . '/../setting/helper');
$loader->loadAll();

/* -------------------------------------------------------
   4.1) AUDIT REQUEST START (hook awal request)
   - Guna helper audit_* yang di-autoload dari setting/helper/audit_helper.php
   - Simpan $__REQUEST_ID & $__REQ_START untuk tamatkan di shutdown.
   - Isi 'route' awal (nama skrip/path) supaya audit_request.route tak kosong.
-------------------------------------------------------- */
$__REQ_START   = microtime(true);
$__REQUEST_ID  = null;

// Tentukan routeName auto (nama skrip atau path)
$__routeName = (function (): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($script !== '') return ltrim($script, '/');
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    return ltrim($path, '/');
})();

// user_id mungkin belum wujud di awal init — isi null dulu
audit_safe(function() use (&$__REQUEST_ID, $__routeName) {
    $ctx = [
        'session_id' => session_id() ?: null,
        'user_id'    => $_SESSION['user']['f_userID'] ?? null, // kalau dah ada
        'route'      => $__routeName,                           // SET ROUTE AWAL
    ];
    $__REQUEST_ID = audit_logger()->logRequestStart($ctx);

    // Simpan global utk helper bind (fallback kuat)
    $GLOBALS['__AUDIT_REQUEST_ID'] = $__REQUEST_ID;

    if (defined('AUDIT_DEBUG') && AUDIT_DEBUG) {
        error_log("[AUDIT] START rid=" . ($__REQUEST_ID ?? 'null') . " route=" . ($__routeName ?? 'null') . " sid=" . session_id());
    }
});

// 5) MySQL + User
$pdo_mysql = Database::getInstance('mysql')->getConnection();
$user      = new User($pdo_mysql);

$f_stafID  = $_SESSION['f_stafID'] ?? null;
$profile   = $f_stafID ? ($user->getProfile($f_stafID) ?: []) : [];

// Ensure active/default role are initialized for the session (role switch safety)
if (!isset($_SESSION['group_default_id']) && !empty($profile['f_groupID'])) {
    $_SESSION['group_default_id'] = (int)$profile['f_groupID'];
}
if (!isset($_SESSION['group_active_id']) && !empty($profile['f_groupID'])) {
    $_SESSION['group_active_id'] = (int)$profile['f_groupID'];
}

/* -------------------------------------------------------
   5.1) KEMASKINI audit_request DENGAN user_id & route SELEPAS PROFILE SIAP
   - Kalau permulaan tadi user_id null, dan sekarang kita dah tahu f_userID,
     bind semula audit_request supaya rekod terikat pada user & route tepat.
-------------------------------------------------------- */
// Bind user_id for audit. Prefer `f_userID` (MySQL user PK) if available,
// fallback to numeric `f_nopekerja` when `f_userID` is not present.
$userIdToBind = null;
if (!empty($profile['f_userID']) && is_numeric($profile['f_userID'])) {
    $userIdToBind = (int)$profile['f_userID'];
} elseif (!empty($_SESSION['user']['f_userID']) && is_numeric($_SESSION['user']['f_userID'])) {
    $userIdToBind = (int)$_SESSION['user']['f_userID'];
} else {
    // Try to derive numeric id from formatted staff numbers like "0530-09" or "530"
    $candidate = $profile['f_nopekerja'] ?? $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? null;
    if ($candidate) {
        if (is_numeric($candidate)) {
            $userIdToBind = (int)$candidate;
        } elseif (preg_match('/^(\d+)/', $candidate, $m)) {
            $userIdToBind = (int)$m[1];
        }
    }
}
if ($userIdToBind !== null) {
    audit_request_bind_user($userIdToBind, $__REQUEST_ID ?? null);
}

// Pastikan route juga direkod (kalau belum)
if (!empty($__routeName)) {
    audit_request_set_route($__routeName, $__REQUEST_ID ?? null);
}

if (defined('AUDIT_DEBUG') && AUDIT_DEBUG) {
    error_log("[AUDIT] BIND uid=" . (($profile['f_userID'] ?? ($_SESSION['user']['f_userID'] ?? 'null'))) . " rid=" . ($__REQUEST_ID ?? 'null') . " route=" . ($__routeName ?? 'null'));
}

// 6) Bahasa: default ikut profil jika belum ada, else 'ms'
if (!isset($_SESSION['lang'])) {
    $pref = $profile['f_lang'] ?? null;
    $_SESSION['lang'] = in_array($pref, ['ms','en','zh','ta'], true) ? $pref : 'ms';
}
$lang = $_SESSION['lang'];

// 7) translations_js (optional untuk front-end) – hanya jika belum diset oleh page
if (!isset($translations_js)) {
    $langFile = __DIR__ . "/../lang/{$lang}.php";
    $all = file_exists($langFile) ? (require $langFile) : [];
    $whitelistFile = __DIR__ . '/../includes/js_keys_whitelist.php';
    if (file_exists($whitelistFile)) {
        $allow = require $whitelistFile; // array of keys
        $translations_js = array_intersect_key($all, array_flip((array)$allow));
    } else {
        $translations_js = []; // fallback selamat
    }
}

// 8) Environment + Error reporting
// SECURITY CRITICAL – DO NOT MODIFY: centralized environment detection & debug exposure control
// Centralized environment detection (dev/staging/production)
if (!function_exists('app_env')) {
    function app_env(): string {
        static $env = null;
        if ($env !== null) return $env;
        $raw = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? ($_ENV['ENVIRONMENT'] ?? getenv('ENVIRONMENT') ?? '');
        $raw = strtolower(trim((string)$raw));
        if ($raw === '') {
            // Fallback: derive from existing dev indicator (safe if helper not yet defined)
            $raw = (function_exists('is_development_mode') && is_development_mode()) ? 'development' : 'production';
        }
        $map = [
            'dev' => 'development',
            'development' => 'development',
            'staging' => 'staging',
            'stage' => 'staging',
            'prod' => 'production',
            'production' => 'production',
        ];
        $env = $map[$raw] ?? 'production'; // STABLE: environment detection
        return $env;
    }
}

// SECURITY CRITICAL – DO NOT MODIFY: environment flags drive production safety
$__APP_ENV = app_env();
$__IS_DEV = ($__APP_ENV === 'development');

// SECURITY CRITICAL – DO NOT MODIFY: production must not leak debug output
// Error reporting: ON for dev, OFF for production/staging
ini_set('display_errors', $__IS_DEV ? '1' : '0');
ini_set('display_startup_errors', $__IS_DEV ? '1' : '0');
error_reporting($__IS_DEV ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);

// 8.1) Ensure application logs directory exists and route PHP error_log there
$__LOG_DIR = realpath(__DIR__ . '/../logs') ?: (__DIR__ . '/../logs');
if (!is_dir($__LOG_DIR)) {
    @mkdir($__LOG_DIR, 0777, true);
}
$__APP_LOG_FILE = rtrim($__LOG_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'app.log';
// Prefer app-level error log so error_log() writes end up in project folder
@ini_set('error_log', $__APP_LOG_FILE);
// Provide a simple helper for application code to write structured logs
require_once __DIR__ . '/logger.php';

// 9) CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 10) Theme (ikut user, fallback Config default)
$config = new Config($pdo_mysql);
$themeSetting = [];
if (!empty($profile['f_themeSetting'])) {
    $dec = json_decode((string)$profile['f_themeSetting'], true);
    if (is_array($dec)) $themeSetting = $dec;
}
if (!$themeSetting) {
    // ambil default dari tbl_m_config (group=system, key=default_theme)
    $themeSetting = $config->getTema();
}
$_SESSION['theme.menu']   = $themeSetting['sidebarColor'] ?? 'light';
$_SESSION['theme.topbar'] = $themeSetting['topbarColor']  ?? 'light';
$_SESSION['theme.layout'] = $themeSetting['layoutMode']   ?? 'light';

// 11) Flags JSON (untuk paparan sahaja – fallback)
$configJsonPath = __DIR__ . '/../configuration/config_db_active.json';
if (file_exists($configJsonPath)) {
    $GLOBALS['sybase_active'] = json_decode((string)file_get_contents($configJsonPath), true) ?? [
        'ehrmdb' => false, 'ehrmdb_dev' => false, 'stafdb' => false,
    ];
} else {
    $GLOBALS['sybase_active'] = ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
}

// 12) SYBASE_ACTIVE_BASE – SSoT
// Keutamaan sumber: SESSION → DB Config (system/SYBASE_ACTIVE_BASE) → JSON flags → 'sybase_ehrmdb'
// Keutamaan pilihan: ehrmdb → ehrmdb_dev → stafdb
if (!defined('SYBASE_ACTIVE_BASE')) {
    $activeBase = $_SESSION['SYBASE_ACTIVE_BASE'] ?? null;

    // Cuba DB config jika kosong
    if (!$activeBase) {
        try {
            $sys = $config->getGroup('system'); // ['SYBASE_ACTIVE_BASE' => 'sybase_ehrmdb' ...]
            if (!empty($sys['SYBASE_ACTIVE_BASE'])) {
                $activeBase = (string)$sys['SYBASE_ACTIVE_BASE'];
                $_SESSION['SYBASE_ACTIVE_BASE'] = $activeBase;
            }
        } catch (Throwable $e) {
            // ignore, fallback ke JSON
        }
    }

    if (!$activeBase) {
        $flags = $GLOBALS['sybase_active'] ?? ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
        $map = function(string $k): ?string {
            return match ($k) {
                'ehrmdb'     => 'sybase_ehrmdb',
                'ehrmdb_dev' => 'sybase_ehrmdb_dev',
                'stafdb'     => 'sybase_stafdb',
                default      => null,
            };
        };
        foreach (['ehrmdb','ehrmdb_dev','stafdb'] as $k) {
            if (!empty($flags[$k])) { $activeBase = $map($k); break; }
        }
    }

    if (!$activeBase) $activeBase = 'sybase_ehrmdb';

    define('SYBASE_ACTIVE_BASE', $activeBase);
    if (empty($_SESSION['SYBASE_ACTIVE_BASE'])) {
        $_SESSION['SYBASE_ACTIVE_BASE'] = $activeBase;
    }
}

// 12.5) Helper untuk check development mode
if (!function_exists('is_development_mode')) {
    /**
     * Check if system is running in development mode
     * Development mode is detected when SYBASE_ACTIVE_BASE contains '_dev'
     * 
     * @return bool True if development mode, false if production
     */
    function is_development_mode(): bool {
        static $cached = null;
        if ($cached !== null) return $cached;
        
        // Check 1: Constant SYBASE_ACTIVE_BASE (primary source)
        if (defined('SYBASE_ACTIVE_BASE')) {
            $activeBase = SYBASE_ACTIVE_BASE;
            // Development jika mengandungi '_dev' (flexible untuk future)
            $cached = (strpos($activeBase, '_dev') !== false);
            return $cached;
        }
        
        // Check 2: Fallback ke JSON flags (backup)
        $flags = $GLOBALS['sybase_active'] ?? [];
        $cached = !empty($flags['ehrmdb_dev']);
        
        return $cached;
    }
}

// 13) Helper cepat untuk dapatkan PDO Sybase aktif
if (!function_exists('sybase_pdo')) {
    function sybase_pdo(): PDO {
        return Database::getInstance(SYBASE_ACTIVE_BASE)->getConnection();
    }
}

// 14) Nama & avatar (berguna untuk topbar)
$nama_pengguna    = $profile['f_nama'] ?? ($profile['f_nickname'] ?? 'Pengguna');
$peranan_pengguna = $profile['f_groupName'] ?? 'Pengguna';
$avatarUrl        = $user->getAvatarUrl($profile['f_nopekerja'] ?? null);

// 15) Auto BASE_URL (jika diperlukan oleh view)
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    define('BASE_URL', ($basePath === '' ? '/' : $basePath . '/'));
}

// 16) Gate helpers
if (!function_exists('require_login')) {
    function require_login(string $redirectTo = '../index.php'): void {
        if (empty($_SESSION['f_stafID'])) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}
if (!function_exists('require_role')) {
    function require_role(string $requiredRole, string $redirectTo = '../index.php'): void {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}

/* -------------------------------------------------------
   17) AUDIT REQUEST END (shutdown hook)
   - Pastikan sentiasa log status code & latency walaupun page error.
-------------------------------------------------------- */
register_shutdown_function(function() use ($__REQ_START, $__REQUEST_ID) {
    audit_safe(function() use ($__REQ_START, $__REQUEST_ID) {
        if (!$__REQUEST_ID) return;
        $lat = (int) round((microtime(true) - $__REQ_START) * 1000);
        $status = http_response_code() ?: 200;
        audit_logger()->logRequestEnd($__REQUEST_ID, $status, $lat);
    });
});

// 17.1) PENTING: JANGAN render alert/HTML dalam init.php.
//       (Render SweetAlert/confirm dsb. dalam footer.php)
