<?php
require_once __DIR__ . '/../controllers/PeribadiController.php';

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
$userDetails = [];
try {
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $auditEvents = $profile_controller->getAuditEvents(PROFILE_CONFIG['AUDIT_EVENTS_LIMIT']);

  $controller = new KeluargaController();
  $lang = $controller->getLang();
  $version = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));
  $parentDetails = $controller->getCurrentParentDetailsInfo();

  $salaryrange = $controller->getSalaryRange();
  $employmentStatus = $controller->getEmploymentStatus();
  $employmentSector = $controller->getEmploymentSector();
  $uniformService = $controller->getUniformService();
} catch (Throwable $e) {
  error_log('[retrieve-data-keluarga.php] Error loading data: ' . $e->getMessage());
  $profile = [];
  $profileView = [];
  $parentDetails = [];
  $salaryrange = [];
  $employmentStatus = [];
  $employmentSector = [];
  $uniformService = [];
  $loginActivity = [];
  $auditEvents = [];
  $errorMessage = tr('profile_error_load_family', 'Ralat memuat data keluarga. Sila cuba lagi atau hubungi pentadbir sistem.');
}

try {
  $peribadiController = new PeribadiController();
  $userDetails = $peribadiController->getCurrentUserDetailsInfo();
} catch (Throwable $e) {
  error_log('[retrieve-data-keluarga.php] Error loading student detail aliases: ' . $e->getMessage());
  $userDetails = [];
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

$nama_bapa = $parentDetails['nama_bapa'] ?? '';
$nokpbapa = $parentDetails['nokpbapa'] ?? '';
$nohp_bapa = $parentDetails['nohp_bapa'] ?? '';
$nama_ibu = $parentDetails['nama_ibu'] ?? '';
$nokpibu = $parentDetails['nokpibu'] ?? '';
$nohp_ibu = $parentDetails['nohp_ibu'] ?? '';

$notel_terkini = $userDetails['notel_terkini'] ?? '';
$notel = $notel_terkini;
$nokp = $userDetails['nokp'] ?? '';
$noKadPengenalan = $nokp;
$nomatrik = $userDetails['matrik'] ?? '';
$email = $userDetails['email'] ?? '';
$alamat1 = $userDetails['alamat1'] ?? '';
$alamat2 = $userDetails['alamat2'] ?? '';
$poskod = $userDetails['alamat3'] ?? '';
$bandar = $userDetails['alamat4'] ?? '';
$negeri = $userDetails['negeri'] ?? '';
$negara = $userDetails['negara'] ?? 'Malaysia';
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
$sesi_akademik = $userDetails['sesi_akademik'] ?? '';
$sesi_akademik_masuk = $userDetails['semester_masuk'] ?? '';
$sesi_akademik_tamat = $userDetails['semester_tamat'] ?? '';
$status_pengajian = $userDetails['statusketerangan'] ?? ($userDetails['status'] ?? '');
$statusPengajian = $status_pengajian;
$statusPelajar = $status_pelajar;
$semester_terkini = $userDetails['semester_terkini'] ?? '';
$pngs = $userDetails['pngs'] ?? '';
$pngk = $userDetails['pngk'] ?? '';
$pembiayaan_pengajian = $userDetails['pembiayaan_pengajian'] ?? '';
$pembiayaan = $pembiayaan_pengajian;

if (!function_exists('count_age')) {
  function count_age(DateTime $tarikh_lahir): string {
    $today = new DateTime();
    $diff = $today->diff($tarikh_lahir);
    return $diff->y . ' tahun ' . $diff->m . ' bulan ' . $diff->d . ' hari';
  }
}
