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
        $profile        = $profile_controller->getCurrentUserProfile();
        $profileView    = $profile; // freeze to avoid include collisions
        $loginActivity  = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
        $auditEvents    = $profile_controller->getAuditEvents(PROFILE_CONFIG['AUDIT_EVENTS_LIMIT']);

        $controller     = new KeluargaController();
        $lang           = $controller->getLang();
        $version        = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));
        $parentDetails  = $controller->getCurrentParentDetailsInfo();

        $salaryrange      = $controller->getSalaryRange();
        $employmentStatus = $controller->getEmploymentStatus();
        $employmentSector = $controller->getEmploymentSector();
        $uniformService   = $controller->getUniformService();
        
    } catch (Throwable $e) {
        error_log('[retreive-data-keluarga.php] Error loading data: ' . $e->getMessage());
        $profile = [];
        $profileView = [];
        $parentDetails = [];
        $salaryrange = [];
        $employmentStatus = [];
        $employmentSector = [];
        $uniformService = [];
        
        $loginActivity = [];
        $auditEvents = [];
        $errorMessage = 'Ralat memuat data keluarga. Sila cuba lagi atau hubungi pentadbir sistem.';
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

    $nama_bapa   = $parentDetails['nama_bapa'] ?? '';
    $nokpbapa    = $parentDetails['nokpbapa'] ?? '';
    $nohp_bapa   = $parentDetails['nohp_bapa'] ?? '';
    $nama_ibu    = $parentDetails['nama_ibu'] ?? '';
    $nokpibu     = $parentDetails['nokpibu'] ?? '';
    $nohp_ibu    = $parentDetails['nohp_ibu'] ?? ''; 

    function count_age(DateTime $tarikh_lahir) {
        $today = new DateTime();

        $diff = $today->diff($tarikh_lahir);

        return $diff->y . " tahun " . $diff->m . " bulan " . $diff->d . " hari";
    }

  
?>