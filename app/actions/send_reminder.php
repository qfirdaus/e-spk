<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../classes/Mailer.php';

header('Content-Type: application/json; charset=utf-8');

try {
  /* 1) Method guard */
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'success'=>false,'message'=>'Method not allowed']); exit;
  }

  /* 2) CSRF */
  $csrf_session = $_SESSION['csrf_token'] ?? '';
  $csrf_form    = $_POST['csrf_token']   ?? '';
  if (!$csrf_session || !$csrf_form || !hash_equals($csrf_session, $csrf_form)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'success'=>false,'message'=>'CSRF token tidak sah']); exit;
  }

  /* 3) Ambil & normalkan input (sokong nama field lama & baru) */
  $role       = trim((string)($_POST['role'] ?? $_POST['peranan'] ?? ''));
  $email      = trim((string)($_POST['email'] ?? ''));
  $targetNama = trim((string)($_POST['nama'] ?? $_POST['target_nama'] ?? '')); // alias
  $stafNama   = trim((string)($_POST['staf_nama'] ?? $_POST['staff_nama'] ?? $_POST['nama_staf'] ?? ''));
  // sokong pelbagai kunci untuk no. staf
  $stafNopek  = trim((string)(
                    $_POST['staf_nopek'] ??
                    $_POST['nopekerja'] ??
                    $_POST['nopek']     ??
                    $_POST['nostaf']    ?? ''
                  ));
  $tahun      = trim((string)($_POST['tahun'] ?? date('Y')));

  // ID tambahan (tak wajib, untuk logging/masa depan)
  $penilaiID  = trim((string)($_POST['penilaiID'] ?? ''));
  $stafID     = trim((string)($_POST['stafID']    ?? ''));

  /* 4) Validasi minima */
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'success'=>false,'message'=>'Alamat emel tidak sah']); exit;
  }
  if ($tahun === '') {
    http_response_code(400);
    echo json_encode(['ok'=>false,'success'=>false,'message'=>'Tahun tidak diberikan']); exit;
  }

  /* 5) Subjek & badan emel - guna template professional */
  $subRole = $role !== '' ? " ({$role})" : '';
  $subject = "Peringatan Penilaian SKT {$tahun}{$subRole}";

  // Fixed action URL - semua user akan terima URL ini
  $actionUrl = 'https://elppt.upnm.edu.my/';

  // Render template HTML professional
  [$bodyHtml, $bodyTxt] = Mailer::render('reminder', [
    'targetNama'  => $targetNama ?: 'Tuan/Puan',
    'stafNama'    => $stafNama ?: '-',
    'stafNopek'   => $stafNopek ?: '-',
    'tahun'       => $tahun,
    'jabatanNama' => '', // Optional: boleh tambah jika ada
    'deadline'    => '', // Optional: boleh tambah jika ada
    'actionUrl'   => $actionUrl,
    'systemName'  => 'e-Prestasi',
    'footerNote'  => 'Emel ini dijana secara automatik. Sila jangan balas emel ini.'
  ]);

  /* 6) Hantar emel via Mailer */
  $pdoMysql = Database::pdoMysql();          // guna helper DB projek
  $mailer   = Mailer::fromConfig($pdoMysql); // cipta mailer ikut config DB
  $ok       = $mailer->send($email, $subject, $bodyHtml, $bodyTxt);

  if (!$ok) {
    http_response_code(500);
    $err = method_exists($mailer, 'getLastError') ? $mailer->getLastError() : 'Gagal menghantar emel';
    echo json_encode(['ok'=>false,'success'=>false,'message'=>$err]); exit;
  }

  /* 7) Response berjaya */
  echo json_encode([
    'ok'      => true,
    'success' => true,
    'message' => 'Emel berjaya dihantar',
  ]);
  exit;

} catch (Throwable $e) {
  error_log('[send_reminder] '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['ok'=>false,'success'=>false,'message'=>$e->getMessage()]);
  exit;
}
