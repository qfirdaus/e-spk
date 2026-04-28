<?php
const PROFILE_CONFIG = [
  'LOGIN_ACTIVITY_LIMIT' => 50,
  'AUDIT_EVENTS_LIMIT' => 100,
  'DATATABLES_PAGE_LENGTH' => 10,
  'DATATABLES_INIT_DELAY' => 300,
  'TOAST_DURATION' => 1400,
  'POLLING_INTERVAL' => 100,
  'POLLING_MAX_ATTEMPTS' => 50,
  'COPY_RATE_LIMIT' => 1000
];

$errorMessage = null;
if (!function_exists('count_age')) {
  function count_age(DateTime $tarikh_lahir): string {
    $today = new DateTime();
    $diff = $today->diff($tarikh_lahir);
    return $diff->y . ' tahun ' . $diff->m . ' bulan ' . $diff->d . ' hari';
  }
}

try {
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $auditEvents = $profile_controller->getAuditEvents(PROFILE_CONFIG['AUDIT_EVENTS_LIMIT']);

  $controller = new PeribadiController();
  $lang = $controller->getLang();
  $version = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));
  $userDetails = $controller->getCurrentUserDetailsInfo();
} catch (Throwable $e) {
  error_log('[retrieve-data-peribadi.php] Error loading data: ' . $e->getMessage());
  $profile = [];
  $profileView = [];
  $loginActivity = [];
  $auditEvents = [];
  $userDetails = [];
  $errorMessage = tr('profile_error_load', 'Ralat memuat data profil. Sila cuba lagi atau hubungi pentadbir sistem.');
}

$avatarUrl = $profileView['avatar_url'] ?? base_url('assets/images/no-image.jpg');
$namaPenuh = $profileView['nama_penuh'] ?? '';
$nickname = $profileView['nickname'] ?? '';
$jawatan = $profileView['jawatan'] ?? '';
$gred = $profileView['gred'] ?? '';
$jabatan = $profileView['jabatan'] ?? '';
$stafID = $profileView['stafID'] ?? '';
$nopek = $profileView['nopekerja'] ?? '';
$emel = $profileView['emel'] ?? '';
$jawGred = trim($jawatan . ($gred ? ' • ' . $gred : ''));
$jantina = $profileView['jantina'] ?? '';

$notel_terkini = $userDetails['notel_terkini'] ?? '';
$notel = $notel_terkini;
$nokp = $userDetails['nokp'] ?? '';
$noKadPengenalan = $nokp;
$nomatrik = $userDetails['matrik'] ?? '';
$email = $userDetails['email'] ?? '';
$jantina = $userDetails['jantina'] ?? $jantina;
$agama = $userDetails['agama'] ?? '';
$bangsa = $userDetails['bangsa'] ?? '';
$warganegara = $userDetails['warganegara'] ?? '';
$negeri_lahir = $userDetails['negeri_lahir'] ?? '';
$status_kahwin = $userDetails['status_kahwin'] ?? '';
$tarikh_lahir = $userDetails['tarikh_lahir'] ?? '';
$age = '';
if ($tarikh_lahir !== '') {
  try {
    $age = count_age(new DateTime($tarikh_lahir));
  } catch (Throwable $e) {
    $age = '';
  }
}
$alamat1 = $userDetails['alamat1'] ?? '';
$alamat2 = $userDetails['alamat2'] ?? '';
$poskod = $userDetails['alamat3'] ?? '';
$bandar = $userDetails['alamat4'] ?? '';
$negeri = $userDetails['negeri'] ?? '';
$kategori_kadet = $userDetails['kategori_kadet'] ?? '';
$kadet = $userDetails['kadet'] ?? '';
$status_pelajar = $kategori_kadet === 'Pkdt' ? 'Kadet ' . $kadet : $kadet;

$fakulti = $userDetails['fakulti'] ?? '';
$kdfakulti = $userDetails['kdfakulti'] ?? '';
$program = $userDetails['program'] ?? '';
$kdprogram = $userDetails['kdprogram'] ?? '';
$program_pengajian = $program;
$peringkat_pengajian = $userDetails['tahap_pengajian'] ?? '';
$kdtahap = $userDetails['kdtahap'] ?? '';
$tempoh_pengajian = $userDetails['tempoh_pengajian'] ?? '';
$status_pengajian = $userDetails['statusketerangan'] ?? ($userDetails['status'] ?? '');
$statusPengajian = $status_pengajian;
$statusPelajar = $status_pelajar;
$semester_terkini = $userDetails['semester_terkini'] ?? '';
$sesi_akademik_masuk = $userDetails['semester_masuk'] ?? '';
$sesi_akademik_tamat = $userDetails['semester_tamat'] ?? '';
$pngs = $userDetails['pngs'] ?? '';
$pngk = $userDetails['pngk'] ?? '';
$pembiayaan_pengajian = $userDetails['pembiayaan_pengajian'] ?? '';
$pembiayaan = $pembiayaan_pengajian;

$negara = $userDetails['negara'] ?? 'Malaysia';
$namaPenerima = $namaPenuh;
$kewarganegaraan = $fakulti;

$alamat1x = $alamat1x ?? '';
$alamat2x = $alamat2x ?? '';
$poskodx = $poskodx ?? '';
$bandarx = $bandarx ?? '';
$negerix = $negerix ?? '';
$negarax = $negarax ?? 'Malaysia';
