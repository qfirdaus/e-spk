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

  require_once __DIR__ . '/../../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../../includes/functions-page.php'; 

  $NEED_DATERANGE  = true;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = false;  

  $PAGE_TITLE = tr('istar_title', 'iStar');
  $pageHeading     = tr('page_heading_anugerah_pingat_graduan', 'Anugerah Pingat Graduan');
  $profileCardLabel = tr('profile_student_label', 'Profil Pelajar');
  $copyIdLabel      = tr('profile_btn_copy_no_matrik', 'Salin No. Matrik');
  include __DIR__ . '/../../../../includes/header.php';

  require_once __DIR__ . '/../../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../../controllers/PeribadiController.php';        
  require_once __DIR__ . '/../../../../controllers/PenglibatanController.php';

  $penglibatanController = new PenglibatanController();
  $lookupAll = $penglibatanController->getAllLookup();

  // Check active session status
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity();
  $isActive = hasActiveSession($loginActivity);
  
  $peribadiController = new PeribadiController();
  $peribadi = $peribadiController->getCurrentUserDetailsInfo();
  $errorMessage = $peribadiController->getErrorMessage();
  $stafID = trim((string)($_SESSION['f_stafID'] ?? ''));
  $anugerahData = $penglibatanController->getAllAnugerah();
  $namaPenuh = (string)($peribadi['nama_penuh'] ?? ($profileView['nama_penuh'] ?? ''));
  $nokp = (string)($peribadi['nokp'] ?? '');
  $istarPerakuanIdPrefix = 'istar-konvo';

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
                  <i class="ri-medal-line me-1"></i> <?= h(tr('tab_anugerah_pengiktirafan','Anugerah dan Pengiktirafan')) ?>
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
                      <?= h(tr('data_loading_records', 'Memuatkan Data...')) ?>
                  </div>
              </div>

              <!-- Tab 3: Jawatan Yang Disandang -->
              <div class="tab-pane fade" id="jawatan-disandang-tab" role="tabpanel">
                  <div id="jawatan-content" class="text-center py-3">
                      <?= h(tr('data_loading_records', 'Memuatkan Data...')) ?>
                  </div>
              </div>

              <!-- Tab 4: Anugerah dan Pengiktirafan -->
              <div class="tab-pane fade" id="anugerah-pengiktirafan-tab" role="tabpanel">
                  <div id="anugerah-content" class="text-center py-3">
                      <?= h(tr('data_loading_records', 'Memuatkan Data...')) ?>
                  </div>
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
    // include __DIR__ . '/../../../../includes/script-test.php';
    include __DIR__ . '/modal.php';
  ?>

  <script> 
      const base_url = "<?= rtrim(base_url(), '/') . '/' ?>";
      const msg_load = {
        processing: <?= json_encode(tr('data_processing', 'Sedang diproses...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        loading: <?= json_encode(tr('data_loading', 'Sedang memuatkan...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        syncronizing: <?= json_encode(tr('data_synchronizing', 'Menyelaraskan data...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
      };
      let DRAFT_KONVO = {
          dataStudent: <?= json_encode($peribadi ?? []) ?>,
          penglibatan: {},
          jawatan: {},
          anugerah: {},
          perakuan: {}
      };        
  </script> 
  <script src="<?= base_url('pages/iStar/permohonan/konvo/helpers/TranslationHelper.php?v=' . time()) ?>"></script>
  <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
  <script src="<?= base_url('assets/js/pages/istar-konvo.js?v=' . time()) ?>"></script> 
  <link rel="stylesheet" href="<?= base_url('assets/css/pages/konvo.css') ?>">

  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
