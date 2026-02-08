<?php
declare(strict_types=1);


// 🔐 Security Headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; object-src 'none';");

// 🔒 Secure Session Setup
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 🧩 Init
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/controllers/LoginController.php';
require_once __DIR__ . '/classes/Database.php'; // untuk query f_jabatanKod
// SSO helper optional; login form will use direct credentials
@include_once __DIR__ . '/sso_sp_client.php';

// 🛑 POST only
// if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
//     http_response_code(405);
//     exit("Access denied");
// }

// ✅ CSRF Check
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
//     exit("CSRF token tidak sah");
// }

// ✅ Helper Function
function GET_REALIPADDRESS(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function sanitize_string(?string $val): string {
    return htmlspecialchars(trim($val ?? ''), ENT_QUOTES, 'UTF-8');
}

function log_login_event(string $status, string $id): void {
    $ip = GET_REALIPADDRESS();
    $time = date('Y-m-d H:i:s');
    $log_path = __DIR__ . '/log/login_attempts.log';
    @file_put_contents($log_path, "[$time] [$status] $id - $ip\n", FILE_APPEND);
}

// 📥 Input
//$f_stafID   = sanitize_string($_POST['f_stafID'] ?? '');
$f_stafID   = sanitize_string($_POST['f_stafID'] ?? '');
$f_password = $_POST['f_password'] ?? '';
$now        = time();

// 🔁 Init session array if not set
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? [];
$_SESSION['locked_until']   = $_SESSION['locked_until'] ?? [];

// Tetapan kadar cuba & masa kunci
$MAX_ATTEMPTS = 3;
$LOCK_SECONDS = 60;

// Pastikan default attempts untuk ID ni
if (!isset($_SESSION['login_attempts'][$f_stafID])) {
    $_SESSION['login_attempts'][$f_stafID] = $MAX_ATTEMPTS;
}
$attempts    = (int)($_SESSION['login_attempts'][$f_stafID] ?? $MAX_ATTEMPTS);
$lockedUntil = (int)($_SESSION['locked_until'][$f_stafID] ?? 0);

// ⛔ Locked
if ($lockedUntil > $now) {
    $wait = $lockedUntil - $now;
    set_alert([
        'type'  => 'sweet',
        'title' => 'login_locked_title',
        'text'  => __('login_locked_msg') . ' ' . $wait . ' ' . __('login_seconds'),
        'icon'  => 'error',
        'timer' => 4000
    ]);
    log_login_event("LOCKED_ID", $f_stafID);
    redirect('alert_akses.php'); // redirect() sepatutnya exit; jika tidak, tambah exit;
    exit;
}

// 🔓 Unlock after timeout
if ($lockedUntil > 0 && $lockedUntil <= $now) {
    unset($_SESSION['locked_until'][$f_stafID], $_SESSION['login_attempts'][$f_stafID]);
    set_alert([
        'type'  => 'sweet',
        'title' => 'login_unlocked_title',
        'text'  => 'login_unlocked_msg',
        'icon'  => 'info',
        'timer' => 4000
    ]);
    redirect('alert_akses.php');
    exit;
}

// ❌ Empty input
//if ($f_stafID === '' || $f_password === '') {
if ($f_stafID === '' || $f_password === '') {
    set_alert([
        'type'  => 'sweet',
        'title' => 'login_form_validation_error',
        'icon'  => 'warning',
        'timer' => 4000
    ]);
    redirect('alert_akses.php');
    exit;
}

// ==========================
// ✅ Proses login
// ==========================

$loginOk = false;
try {
    $loginController = new LoginController();
    $loginOk = $loginController->authenticate($f_stafID, $f_password);
} catch (Throwable $e) {

    // Check jika exception adalah untuk access blocked
    if ($e->getMessage() === 'ACCESS_BLOCKED') {
        
        // set_alert([
        //     'type'  => 'sweet',
        //     'title' => 'login_access_blocked_title',
        //     'text'  => 'login_access_blocked_msg',
        //     'icon'  => 'error',
        //     'timer' => 5000
        // ]);
         
        log_login_event("DISEKAT", $f_stafID);
        redirect('alert_akses.php');
        exit;
    }
    
    // Exception masa authenticate (DB down, dsb.)
    error_log(sprintf('LOGIN ERROR: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));

    set_alert([
        'type'  => 'sweet',
        'title' => 'config_login_error_title',   // ikut standard prefix config_
        'text'  => 'config_login_error_message',
        'icon'  => 'error'
    ]);
    redirect('alert_akses.php');
    exit;
}

// ==========================
// 🎯 Selepas try/catch (redirect di luar)
// ==========================
if ($loginOk) {
    // ✅ Berjaya login
    unset($_SESSION['login_attempts'][$f_stafID], $_SESSION['locked_until'][$f_stafID]);

    // 🆕 SET session: kod jabatan dari MySQL (tbl_m_user.f_jabatanKod)
    try {
        $pdo = Database::getInstance('mysql')->getConnection();
        $stmt = $pdo->prepare("SELECT f_jabatanKod FROM tbl_m_user WHERE f_stafID = :id LIMIT 1");
        $stmt->execute([':id' => $f_stafID]);
        $_SESSION['f_jabatanKod'] = trim((string)($stmt->fetchColumn() ?: ''));
    } catch (Throwable $e) {
        $_SESSION['f_jabatanKod'] = '';
        error_log('LOGIN set f_jabatanKod: ' . $e->getMessage());
    }

    // (optional) simpan stafID dalam session — authenticate() pun dah set; ini sebagai kesinambungan
    $_SESSION['f_stafID'] = $f_stafID;

    // Buang alert lama
    unset($_SESSION['alert']);

    log_login_event("BERJAYA", $f_stafID);

    set_alert([
        'type'  => 'sweet',
        'title' => 'login_welcome',
        'text'  => 'login_welcome',
        'icon'  => 'success',
        'timer' => 3000
    ]);

    // 🔑 Lepaskan lock session sebelum redirect
    session_write_close();

    // ✅ Terus ke dashboard dgn flag first=1 (supaya data load melalui AJAX)
    redirect('pages/dashboard.php?first=1');
    exit;

} else {
    // ❌ Gagal login (ID/Password salah)
    $attempts--;
    if ($attempts < 0) $attempts = 0;
    $_SESSION['login_attempts'][$f_stafID] = $attempts;

    if ($attempts <= 0) {
        $_SESSION['locked_until'][$f_stafID] = $now + $LOCK_SECONDS;
        set_alert([
            'type'  => 'sweet',
            'title' => 'login_locked_title',
            'text'  => __('login_locked_msg') . ' ' . $LOCK_SECONDS . ' ' . __('login_seconds'),
            'icon'  => 'error',
            'timer' => 4000
        ]);
        log_login_event("TERKUNCI_ID", $f_stafID);
    } else {
        // Papar baki cubaan dalam mesej
        set_alert([
            'type'  => 'sweet',
            'title' => 'login_fail_title',
            'text'  => __('login_fail_msg') . ' ' . $attempts,
            'icon'  => 'warning',
            'timer' => 4000
        ]);
        log_login_event("GAGAL", $f_stafID);
    }

    redirect('alert_akses.php');
    exit;
}
