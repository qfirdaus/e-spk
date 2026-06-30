<?php
  // pages/pengesahan-pelajar.php
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
  require_once __DIR__ . '/../../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../../includes/functions-page.php'; 

  $PAGE_TITLE = tr('istar_title', 'iStar');
  $pageHeading     = 'Pengesahan Pelajar';
  $profileCardLabel = 'Profil Pelajar';
  $copyIdLabel      = 'Salin No. Matrik';
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = true;

  include __DIR__ . '/../../../../includes/header.php';  
  require_once __DIR__ . '/../../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../../controllers/PeribadiController.php'; 
  require_once __DIR__ . '/../../../../controllers/RekodPeribadiController.php';

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
  $dataSponsor = $rekodPeribadiController->getSponsorData($stafID);
  $lookupAll = $rekodPeribadiController->getAllLookup();
  $lookupSponsor = $lookupAll['sponsor'] ?? [];  
?>
<body
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.sidebar'] ?? 'dark') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

  <div class="wrapper">
    <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

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
            <?php include __DIR__ . '/../../../../includes/profile-card.php'; ?>
            
            <!-- Tab Navigasi -->
            <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#maklumat-peribadi-tab" role="tab">
                  <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_profil_pengguna','Maklumat Peribadi')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-akademik-tab" role="tab">
                  <i class="ri-book-2-line me-1"></i> <?= h(tr('tab_maklumat_akademik', 'Maklumat Akademik')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-penerima-tab" role="tab">
                  <i class="ri-briefcase-line me-1"></i> <?= h(tr('tab_maklumat_penerima', 'Maklumat Penerima')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-perakuan-tab" role="tab">
                  <i class="ri-file-paper-line me-1"></i> <?= h(tr('tab_maklumat_perakuan', 'Maklumat Perakuan')) ?>
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
              
              <?php $hideButton = true; // hide button simpan & readonly ?>
              
              <!-- Tab 1: Maklumat Peribadi -->
              <div class="tab-pane fade show active" id="maklumat-peribadi-tab" role="tabpanel"> 
                <?php include __DIR__ . '/../../../rekod-utama/data-peribadi/f-peribadi.php'; ?>
              </div>

              <!-- Tab 2: Maklumat Akademik -->
              <div class="tab-pane fade show" id="maklumat-akademik-tab" role="tabpanel">                
                <?php include __DIR__ . '/../../../rekod-utama/data-akademik/f-akademik.php'; ?>
              </div>    

              <!-- Tab 3: Maklumat Penerima -->
              <div class="tab-pane fade show" id="maklumat-penerima-tab" role="tabpanel">
                <div id="penerima-content"></div>
              </div> 

              <!-- Tab 4: Perakuan -->
              <div class="tab-pane fade show" id="maklumat-perakuan-tab" role="tabpanel">
                <?php  include __DIR__ . '/f-perakuan.php'; ?>
              </div>
              
            </div>
          </div>        
          <!-- /Profile Card with Tabs -->

        </div>
      </div>
      <?php include __DIR__ . '/../../../../includes/footer.php'; ?>
    </div>
  </div>

  <?php 
    include __DIR__ . '/../../../../includes/script.php'; 
    include __DIR__ . '/../../../../includes/script-pages.php';  
  ?>
  <script> 
      const base_url = "<?= rtrim(base_url(), '/') . '/' ?>"; 
      const msg_load = {
        processing: "<?= h(tr('data_processing', 'Sedang diproses...')) ?>",
        loading: "<?= h(tr('data_loading', 'Sedang memuatkan...')) ?>",
        syncronizing: "<?= h(tr('data_synchronizing', 'Menyelaraskan data...')) ?>"
      };
      let DRAFT = {
          dataStudent: <?= json_encode($peribadi ?? []) ?>,
          penerima: {},
          perakuan: {}
      };      
  </script> 
  <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
  <script src="<?= base_url('assets/js/pages/icares-pengesahan-pelajar.js?v=' . time()) ?>"></script> 

  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
