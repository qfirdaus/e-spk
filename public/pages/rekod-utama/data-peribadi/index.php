<?php
  // pages/data-peribadi.php
  declare(strict_types=1);
  const PROFILE_CONFIG = [
  'LOGIN_ACTIVITY_LIMIT' => 30,
  'AUDIT_EVENTS_LIMIT' => 30,
  'DATATABLES_PAGE_LENGTH' => 10,
  'DATATABLES_INIT_DELAY' => 300,
  'TOAST_DURATION' => 1400,
  'POLLING_INTERVAL' => 100,
  'POLLING_MAX_ATTEMPTS' => 50,
  'COPY_RATE_LIMIT' => 1000
  ];

  require_once __DIR__ . '/../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../includes/functions-page.php'; 

  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = true;
  $pageHeading     = 'Maklumat Peribadi';
  $profileCardLabel = 'Profil Pelajar';
  $copyIdLabel      = 'Salin No. Matrik';

  include __DIR__ . '/../../../includes/header.php';
  require_once __DIR__ . '/../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../controllers/PeribadiController.php';
  require_once __DIR__ . '/../../../controllers/RekodPeribadiController.php';

  // Check active session status
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $isActive = hasActiveSession($loginActivity);

  $peribadiController = new PeribadiController();
  $peribadi = $peribadiController->getCurrentUserDetailsInfo();
  $dataKolej = $peribadiController->getPenginapanSemasaPengajian();
  $errorMessage = $peribadiController->getErrorMessage();
  $stafID = trim((string)($_SESSION['f_stafID'] ?? ''));
  
  $rekodPeribadiController = new RekodPeribadiController();
  $dataPekerjaan = $rekodPeribadiController->getPekerjaanData($stafID);
  $dataKesihatan = $rekodPeribadiController->getKesihatanData($stafID);
  $dataAkaun = $rekodPeribadiController->getAkaunData($stafID);
  $lookupAll = $rekodPeribadiController->getAllLookup();
?>
<body
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.sidebar'] ?? 'dark') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

  <div class="wrapper">
    <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">

          <!-- Title + breadcrumb -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="page-title">
                  <i class="ri-user-3-line me-1"></i>
                  <?= h($pageHeading) ?>
                </h4>
                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">
                      <a href="<?= base_url('pages/dashboard.php') ?>">
                        <i class="ri-home-4-line align-middle me-1"></i>
                        <?= h(tr('dashboard_breadcrumb','Papan Pemuka')) ?>
                      </a>
                    </li>
                    <li class="breadcrumb-item active">
                      <?= h($pageHeading) ?>
                    </li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Card with Tabs -->
          <div class="card border-0 shadow-sm profile-card">
            <?php include __DIR__ . '/../../../includes/profile-card.php'; ?>
            
            <!-- Tab Navigasi -->
            <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#maklumat-peribadi-tab" role="tab">
                  <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_profil_pengguna','Maklumat Peribadi')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-alamat-tab" role="tab">
                  <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_maklumat_alamat', 'Maklumat Alamat')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-pekerjaan-tab" role="tab">
                  <i class="ri-briefcase-line me-1"></i> <?= h(tr('tab_maklumat_pekerjaan', 'Maklumat Pekerjaan')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-kesihatan-tab" role="tab">
                  <i class="ri-health-book-line me-1"></i> <?= h(tr('tab_maklumat_kesihatan', 'Maklumat Kesihatan')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-akaun-tab" role="tab">
                  <i class="ri-money-dollar-box-line me-1"></i> <?= h(tr('tab_maklumat_akaun', 'Maklumat Akaun')) ?>
                </a>
              </li>
            </ul>

            <!-- Kandungan Tab -->
            <div class="tab-content p-4">
              <?php if ($errorMessage): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                  <i class="ri-error-warning-line me-2"></i>
                  <div>
                    <?= h($errorMessage) ?>
                  </div>
                </div>
              <?php endif; ?>
              
              <?php if ($stafID === '' && !$errorMessage): ?>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                  <i class="ri-alert-line me-2"></i>
                  <div>
                    <?= h(tr(
                      'profile_empty_notice',
                      'Profil tidak dijumpai. Sesi login mungkin tamat atau rekod tiada.'
                    )) ?>
                  </div>
                </div>
              <?php endif; ?>

              <!-- Tab 1: Maklumat Peribadi -->
              <div class="tab-pane fade show active" id="maklumat-peribadi-tab" role="tabpanel"> 
                <?php include __DIR__ . '/f-peribadi.php'; ?>
              </div>

              <!-- Tab 2: Maklumat Alamat -->
              <div class="tab-pane fade" id="maklumat-alamat-tab" role="tabpanel">                
                <?php include __DIR__ . '/f-alamat.php'; ?>
              </div>    

              <!-- Tab 3: Maklumat Pekerjaan -->
              <div class="tab-pane fade" id="maklumat-pekerjaan-tab" role="tabpanel">
                <?php  include __DIR__ . '/f-pekerjaan.php'; ?>
              </div>

              <!-- Tab 4: Maklumat Kesihatan -->
              <div class="tab-pane fade" id="maklumat-kesihatan-tab" role="tabpanel"> 
                <?php  include __DIR__ . '/f-kesihatan.php'; ?>
              </div>

              <!-- Tab 5: Maklumat Akaun -->
              <div class="tab-pane fade" id="maklumat-akaun-tab" role="tabpanel"> 
                <?php  include __DIR__ . '/f-akaun.php'; ?>
              </div>

            </div>
          </div>        
          <!-- /Profile Card with Tabs -->

        </div>
      </div>
      <?php include __DIR__ . '/../../../includes/footer.php'; ?>
    </div>
  </div>

  <?php 
    include __DIR__ . '/../../../includes/script.php'; 
    include __DIR__ . '/../../../includes/script-pages.php';  
  ?>
  <script> 
      const base_url = "<?= rtrim(base_url(), '/') . '/' ?>";
      const msg_load = {
        processing: "<?= h(tr('data_processing', 'Sedang diproses...')) ?>",
        loading: "<?= h(tr('data_loading', 'Sedang memuatkan...')) ?>",
        syncronizing: "<?= h(tr('data_synchronizing', 'Menyelaraskan data...')) ?>"
      };        
  </script>

  <?php if ($NEED_SELECT2): ?>
    <script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>?v=<?= time(); ?>"></script>
  <?php endif; ?>

  <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
  <script src="<?= base_url('assets/js/pages/hepa-data-peribadi.js?v=' . time()) ?>"></script>
  <link rel="stylesheet" href="<?= base_url('assets/css/pages/rekod-utama.css') ?>">

  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
