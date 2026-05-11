<?php
  // pages/data-peribadi.php
  declare(strict_types=1);
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = false;
  $pageHeading     = 'Anugerah Pingat Graduan';
  $profileCardLabel = 'Profil Pelajar';
  $copyIdLabel      = 'Salin No. Matrik';

  require_once __DIR__ . '/../../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../../includes/functions-page.php'; 
  include __DIR__ . '/../../../../includes/header.php';

  require_once __DIR__ . '/../../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../../controllers/PeribadiController.php';       
  // require_once __DIR__ . '/../../../../controllers/PenglibatanController.php';     

  // Check active session status
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $isActive = hasActiveSession($loginActivity);
  
  $peribadiController = new PeribadiController();
  $peribadi = $peribadiController->getCurrentUserDetailsInfo();

  // $penglibatanController = new PenglibatanController();
  // $jawatanData = $penglibatanController->getAllJawatanDisandang();

  //print_r($lookupWakil);
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
                  <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_maklumat-peribadi','Maklumat Peribadi')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#penglibatan-program-tab" role="tab">
                  <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_peglibatan_program','Penglibatan Program')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#jawatan-disandang-tab" role="tab">
                  <i class="ri-user-line me-1"></i> <?= h(tr('tab_jawatan_disandang','Jawatan Yang Disandang')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#anugerah-pengiktirafan-tab" role="tab">
                  <i class="ri-user-line me-1"></i> <?= h(tr('tab_anugerah_pengiktirafan','Anugerah dan Pengiktirafan')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#perakuan-pemohon-tab" role="tab">
                  <i class="ri-file-paper-line me-1"></i> <?= h(tr('tab_perakuan_pemohon','Perakuan Pemohon')) ?>
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

              <!-- Tab 2: Penglibatan Program -->
              <div class="tab-pane fade" id="penglibatan-program-tab" role="tabpanel">    
                  <div id="penglibatan-content" class="text-center py-3">
                      Memuatkan Data...
                  </div>
                  <?php // include __DIR__ . '/f-penglibatan-program.php'; ?>        
              </div>

              <!-- Tab 3: Jawatan Yang Disandang -->
              <div class="tab-pane fade" id="jawatan-disandang-tab" role="tabpanel">
                  <?php include __DIR__ . '/f-jawatan-disandang.php'; ?> 
              </div>

              <!-- Tab 4: Anugerah dan Pengiktirafan -->
              <div class="tab-pane fade" id="anugerah-pengiktirafan-tab" role="tabpanel">
                  <?php include __DIR__ . '/f-anugerah-pengiktirafan.php'; ?>
              </div>

              <!-- Tab 5: Perakauan Pemohon -->
              <div class="tab-pane fade" id="perakuan-pemohon-tab" role="tabpanel">
                  <?php include __DIR__ . '/f-perakuan.php'; ?>
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
    include __DIR__ . '/../../../../includes/script-custom.php';
    include __DIR__ . '/modal.php';
  ?>

  <script> const base_url = "<?= rtrim(base_url(), '/') . '/' ?>"; </script> 
  <script src="<?= base_url('assets/js/pages/konvo.js') ?>"></script> 
  <link rel="stylesheet" href="<?= base_url('assets/css/pages/penglibatan.css') ?>">

  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
