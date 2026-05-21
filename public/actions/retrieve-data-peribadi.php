<?php

require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../controllers/PeribadiController.php';

if (!defined('PROFILE_CONFIG')) {
  define('PROFILE_CONFIG', [
    'LOGIN_ACTIVITY_LIMIT' => 50,
    'AUDIT_EVENTS_LIMIT' => 100,
    'DATATABLES_PAGE_LENGTH' => 10,
    'DATATABLES_INIT_DELAY' => 300,
    'TOAST_DURATION' => 1400,
    'POLLING_INTERVAL' => 100,
    'POLLING_MAX_ATTEMPTS' => 50,
    'COPY_RATE_LIMIT' => 1000
  ]);
}

$errorMessage = null;
$peribadi = [];

try {
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $lang = method_exists($profile_controller, 'getLang')
    ? $profile_controller->getLang()
    : (string)($_SESSION['lang'] ?? 'ms');
  $version = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));

  $peribadiController = new PeribadiController();
  $peribadi = $peribadiController->getCurrentUserDetailsInfo();
  $controllerError = $peribadiController->getErrorMessage();

  if ($controllerError !== '') {
    $errorMessage = $controllerError;
  }
} catch (Throwable $e) {
  error_log('[retrieve-data-peribadi.php] Error loading data: ' . $e->getMessage());
  $profile = [];
  $profileView = [];
  $peribadi = [];
  $loginActivity = [];
  $lang = (string)($_SESSION['lang'] ?? 'ms');
  $version = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));
  $errorMessage = tr('profile_error_load', 'Ralat memuat data profil. Sila cuba lagi atau hubungi pentadbir sistem.');
}

$avatarUrl = $profileView['avatar_url'] ?? base_url('assets/images/no-image.jpg');
$namaPenuh = $profileView['nama_penuh'] ?? ($peribadi['nama_penuh'] ?? '');
$nickname = $profileView['nickname'] ?? '';
$jawatan = $profileView['jawatan'] ?? '';
$gred = $profileView['gred'] ?? '';
$jabatan = $profileView['jabatan'] ?? '';
$stafID = $profileView['stafID'] ?? trim((string)($_SESSION['f_stafID'] ?? ''));
$nopek = $profileView['nopekerja'] ?? '';
$emel = $profileView['emel'] ?? ($peribadi['email'] ?? '');
$jawGred = trim($jawatan . ($gred ? ' • ' . $gred : ''));

$notel_terkini = $peribadi['notel_terkini'] ?? '';
$notel = $notel_terkini;
$nokp = $peribadi['nokp'] ?? '';
$noKadPengenalan = $nokp;
$nomatrik = $peribadi['matrik'] ?? '';
$email = $peribadi['email'] ?? '';
$alamat1 = $peribadi['alamat1'] ?? '';
$alamat2 = $peribadi['alamat2'] ?? '';
$poskod = $peribadi['alamat3'] ?? '';
$bandar = $peribadi['alamat4'] ?? '';
$negeri = $peribadi['negeri'] ?? '';
$negara = $peribadi['negara'] ?? 'Malaysia';
$namaPenerima = $namaPenuh;
$kategori_kadet = $peribadi['kategori_kadet'] ?? '';
$kadet = $peribadi['kadet'] ?? '';
$status_pelajar = $kategori_kadet === 'Pkdt' ? 'Kadet ' . $kadet : $kadet;

$fakulti = $peribadi['fakulti'] ?? '';
$kdfakulti = $peribadi['kdfakulti'] ?? '';
$kewarganegaraan = $fakulti !== '' ? $fakulti : ($peribadi['warganegara'] ?? '');
$program = $peribadi['program'] ?? '';
$kdprogram = $peribadi['kdprogram'] ?? '';
$program_pengajian = $program;
$peringkat_pengajian = $peribadi['tahap_pengajian'] ?? '';
$kdtahap = $peribadi['kdtahap'] ?? '';
$tempoh_pengajian = $peribadi['tempoh_pengajian'] ?? '';
$tempoh_program = $tempoh_pengajian;
$sesi_akademik = $peribadi['sesi_akademik'] ?? '';
$sesi_akademik_masuk = $peribadi['semester_masuk'] ?? '';
$sesi_akademik_tamat = $peribadi['semester_tamat'] ?? '';
$sesiAkademikMula = $sesi_akademik_masuk;
$sesiAkademikTamat = $sesi_akademik_tamat;
$status_pengajian = $peribadi['statusketerangan'] ?? ($peribadi['status'] ?? '');
$statusPengajian = $status_pengajian;
$statusPelajar = $status_pelajar;
$semester_terkini = $peribadi['semester_terkini'] ?? '';
$pngs = $peribadi['pngs'] ?? '';
$pngk = $peribadi['pngk'] ?? '';
$pembiayaan_pengajian = $peribadi['pembiayaan_pengajian'] ?? '';
$pembiayaan = $pembiayaan_pengajian;