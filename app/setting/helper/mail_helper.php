<?php
// setting/helper/mail_helper.php
declare(strict_types=1);

// Naik 2 level dari setting/helper → /var/www/html
$ROOT = dirname(__DIR__, 2); // /var/www/html

require_once $ROOT . '/classes/Mailer.php';
require_once $ROOT . '/classes/Database.php';

/**
 * Hantar emel generic
 */
function mail_send(array|string $to, string $subject, string $html, ?string $text=null, array $opts=[]): bool {
    $pdo = Database::pdoMysql();
    return Mailer::quickSend($pdo, $to, $subject, $html, $text, $opts);
}

/**
 * Contoh helper khusus reminder LPPT
 */
function mail_send_reminder(string $to, string $role, string $stafNama, string $stafNopek, string $tahun, ?string $targetNama=null): bool {
    [$html, $text] = Mailer::render('reminder', [
        'role'       => $role,
        'targetNama' => $targetNama ?? $role,
        'stafNama'   => $stafNama,
        'stafNopek'  => $stafNopek,
        'tahun'      => $tahun,
        'actionUrl'  => 'https://elppt.upnm.edu.my/', // Fixed URL
        'systemName' => 'e-Prestasi',
    ]);
    $subject = "[LPPT {$tahun}] Peringatan penilaian untuk {$stafNama}" . ($stafNopek ? " ({$stafNopek})" : "");
    $pdo = Database::pdoMysql();
    return Mailer::quickSend($pdo, $to, $subject, $html, $text);
}
