<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');

// ================= Authentication Check =================
require_login();

// ================= Authorization Check =================
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../setting/constants/prestasi_constants.php';
$pdo_mysql = Database::getInstance('mysql')->getConnection();
$userModel = new User($pdo_mysql);
$f_stafID = $_SESSION['f_stafID'] ?? null;
$profile = $f_stafID ? $userModel->getProfile($f_stafID) : [];
$isSuperAdmin = $profile && function_exists('is_user_super_admin') && is_user_super_admin($profile, $pdo_mysql);
if (!$isSuperAdmin) {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Hanya Super Admin dibenarkan.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ================= CSRF Protection =================
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (empty($csrfHeader) || empty($sessionToken) || !hash_equals($sessionToken, $csrfHeader)) {
    echo json_encode([
        'success' => false,
        'message' => 'CSRF token tidak sah. Sila muat semula halaman dan cuba lagi.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ================= Rate Limiting =================
require_once __DIR__ . '/_helpers.php';
if (!checkRateLimit('test_email', 5, 60)) {
    echo json_encode([
        'success' => false,
        'message' => 'Terlalu banyak percubaan. Sila cuba lagi selepas 1 minit.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../assets/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../assets/vendor/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../assets/vendor/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ambil nilai dari POST
$driver     = $_POST['mail_driver'] ?? '';
$host       = $_POST['mail_host'] ?? '';
$port       = $_POST['mail_port'] ?? '';

$username   = $_POST['mail_username'] ?? '';
$password   = trim($_POST['mail_password'] ?? '');

$encryption = $_POST['mail_encryption'] ?? '';
$fromAddr   = $_POST['mail_from_address'] ?? '';
$fromName   = $_POST['mail_from_name'] ?? '';
$to         = $_POST['uji_email'] ?? $username;

// Jika password kosong, ambil dari existing settings
if ($password === '') {
    require_once __DIR__ . '/../classes/Config.php';
    $configModel = new Config($pdo_mysql);
    $existingSettings = $configModel->getGroup('email');
    $password = $existingSettings['mail_password'] ?? '';
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $username;
    $mail->Password   = $password;
    $mail->Port       = (int)$port;
    $mail->SMTPSecure = $encryption ?: PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom($fromAddr, $fromName);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = 'Ujian Emel dari Sistem e-Prestasi';
    $mail->Body    = 'Ini adalah ujian sambungan emel dari sistem e-Prestasi.';

    $mail->send();
    
    // Get translation for success message (lang_helper.php should be loaded via init.php)
    if (!function_exists('__')) {
        require_once __DIR__ . '/../setting/helper/lang_helper.php';
    }
    
    $successKey = 'config_js_emel_uji_berjaya';
    $successMsg = __($successKey);
    
    // If translation not found, use default
    if ($successMsg === $successKey) {
        $successMsg = "Emel ujian berjaya dihantar ke <strong>{$to}</strong>.";
    } else {
        // Replace placeholder
        $successMsg = str_replace(':email', "<strong>{$to}</strong>", $successMsg);
    }
    
    echo json_encode([
        'success' => true,
        'message' => $successMsg
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // Get translation for error message (lang_helper.php should be loaded via init.php)
    if (!function_exists('__')) {
        require_once __DIR__ . '/../setting/helper/lang_helper.php';
    }
    
    $errorKey = 'config_js_emel_uji_gagal';
    $errorTemplate = __($errorKey);
    
    // If translation not found, use default
    if ($errorTemplate === $errorKey) {
        $errorTemplate = "❌ Gagal hantar emel: :error";
    }
    
    $errorMsg = str_replace(':error', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'), $errorTemplate);
    
    echo json_encode([
        'success' => false,
        'message' => $errorMsg
    ], JSON_UNESCAPED_UNICODE);
}
