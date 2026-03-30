<?php
  // ==================== CONFIGURATION CONSTANTS ====================
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

  // Error boundary - catch all exceptions
  $errorMessage = null;
  try {
    $profile_controller   = new ProfileController();
    $profile      = $profile_controller->getCurrentUserProfile();
    $profileView  = $profile; // freeze to avoid include collisions
    $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
    $auditEvents = $profile_controller->getAuditEvents(PROFILE_CONFIG['AUDIT_EVENTS_LIMIT']);

    $controller   = new PeribadiController();
    $lang         = $controller->getLang();
    $version      = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));
    $userDetails  = $controller->getCurrentUserDetailsInfo();
    
  } catch (Throwable $e) {
    error_log('[profile.php] Error loading data: ' . $e->getMessage());
    $profile = [];
    $profileView = [];
    $loginActivity = [];
    $auditEvents = [];
    $errorMessage = 'Ralat memuat data profil. Sila cuba lagi atau hubungi pentadbir sistem.';
  }

  $namaPenuh = $profileView['nama_penuh'] ?? '';
  $nickname  = $profileView['nickname']   ?? '';
  $jawatan   = $profileView['jawatan']    ?? '';
  $gred      = $profileView['gred']       ?? '';
  $jabatan   = $profileView['jabatan']    ?? '';
  $stafID    = $profileView['stafID']     ?? '';
  $nopek     = $profileView['nopekerja']  ?? '';
  $emel      = $profileView['emel']       ?? '';
  $jawGred   = trim($jawatan . ($gred ? ' • '.$gred : ''));
  $jantina   = $profileView['jantina']    ?? '';

  $notel_terkini   = $userDetails['notel_terkini'] ?? '';
  $nokp            = $userDetails['nokp'] ?? '';
  $nomatrik        = $userDetails['matrik'] ?? ''; 
  $email           = $userDetails['email'] ?? '';
  $jantina         = $userDetails['jantina'] ?? '';
  $agama           = $userDetails['agama'] ?? '';
  $bangsa          = $userDetails['bangsa'] ?? '';
  $warganegara     = $userDetails['warganegara'] ?? '';
  $negeri_lahir    = $userDetails['negeri_lahir'] ?? '';
  $status_kahwin   = $userDetails['status_kahwin'] ?? '';
  $tarikh_lahir    = $userDetails['tarikh_lahir'] ?? '';
  $age             = count_age(new DateTime($tarikh_lahir));
  $alamat1         = $userDetails['alamat1'] ?? '';
  $alamat2         = $userDetails['alamat2'] ?? '';
  $poskod         = $userDetails['alamat3'] ?? '';
  $bandar         = $userDetails['alamat4'] ?? '';
  $negeri          = $userDetails['negeri'] ?? '';

function count_age(DateTime $tarikh_lahir) {
  $today = new DateTime();

  $diff = $today->diff($tarikh_lahir);

  return $diff->y . " tahun " . $diff->m . " bulan " . $diff->d . " hari";
}

  
?>