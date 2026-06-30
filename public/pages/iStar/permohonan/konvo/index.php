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

  $penglibatanController = new PenglibatanController();
  $lookupAll = $penglibatanController->getAllLookup();
  $configSesi = $penglibatanController->getSesiPermohonan('APPLICATION','pingat_graduan');
  
  $alreadyApplied = false;
  if (!empty($configSesi)) {
      $alreadyApplied = $penglibatanController ->checkPermohonanSediaAda($stafID, $configSesi['id']);
  }

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

            <?php if ($alreadyApplied): ?>

            <div class="d-flex justify-content-center my-4">

                <div class="card border-0 shadow-sm text-center p-4"
                    style="max-width: 520px; width: 100%; border-radius: 14px;">

                    <!-- Icon -->
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center"
                            style="width:70px;height:70px;border-radius:50%;background:#e8f7ee;">
                            <i class="ri-checkbox-circle-line"
                              style="font-size:36px;color:#28a745;"></i>
                        </div>
                    </div>

                    <!-- Title -->
                    <h5 class="mb-2">
                        <?= h(tr('permohonan','Permohonan')) ?>
                        <strong><?= h($configSesi['award_desc'] ?? '') ?></strong>
                    </h5>

                    <p class="text-muted mb-1">
                        <?= h(tr('bagi','bagi')) ?>
                        <strong><?= h($configSesi['config_name'] ?? '') ?></strong>
                    </p>

                    <p class="text-success fw-semibold mb-3">
                        <?= h(tr('telah_dihantar','telah dihantar.')) ?>
                    </p>

                    <!-- Note -->
                    <small class="text-muted d-block mb-3">
                        <?= h(tr(
                            'semakan_permohonan_notice',
                            'Anda boleh membuat semakan status permohonan di menu Semakan.'
                        )) ?>
                    </small>

                    <!-- Button -->
                    <a href="<?= base_url('pages/iStar/semakan/permohonan/index.php') ?>"
                      class="btn btn-success px-4 rounded-pill">
                        <i class="ri-search-line me-1"></i>
                        <?= h(tr('btn_semakan_permohonan', 'Semakan Permohonan')) ?>
                    </a>

                </div>

            </div>

            <?php endif; ?>
            
            <!-- check if already applied for current application session -->
            <?php if (!$alreadyApplied): ?> 
              <!-- Tab Navigasi -->
              <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
                <li class="nav-item">
                  <a class="nav-link active" data-bs-toggle="tab" href="#maklumat-peribadi-tab" role="tab">
                    <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_maklumat-peribadi','Maklumat Peribadi')) ?>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#maklumat-akademik-tab" role="tab">
                    <i class="ri-book-line me-1"></i> <?= h(tr('tab_maklumat-akademik','Maklumat Akademik')) ?>                  
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

                <!-- Tab 2: Maklumat Akademik -->
                <div class="tab-pane fade" id="maklumat-akademik-tab" role="tabpanel">

                  <div class="row">
                    <!-- LEFT SUB TAB -->
                    <div class="col-md-3">
                      <div class="icares-address-nav" role="tablist" aria-label="<?= h(tr('tab_maklumat_alamat', 'Maklumat Alamat')) ?>">
                        <button class="icares-address-nav__item active" id="tab-akademik-sumber" data-bs-toggle="pill" data-bs-target="#akademik-sumber-tab" type="button" role="tab" aria-controls="alamat-tetap-panel" aria-selected="true">
                          <i class="ri-book-line"></i>
                          <span><?= h(tr('maklumat_akademik', 'Maklumat Akademik')) ?></span>
                        </button>
                        <button class="icares-address-nav__item" id="tab-akademik-tambahan-tab" data-bs-toggle="pill" data-bs-target="#akademik-tambahan-tab" type="button" role="tab" aria-controls="alamat-tinggal-panel" aria-selected="false">
                          <i class="ri-folder-add-line"></i>
                          <span><?= h(tr('maklumat_tambahan', 'Maklumat Tambahan')) ?></span>
                        </button>
                      </div>      
                    </div>

                    <!-- RIGHT CONTENT -->
                    <div class="col-md-9">
                      <div class="tab-content">
                        <!-- TAB 1: DATA SAP -->
                        <div class="tab-pane fade show active" id="akademik-sumber-tab">
                            <?php  include __DIR__ . '/../../../rekod-utama/data-akademik/f-akademik.php'; ?>
                        </div>

                        <!-- TAB 2: TAMBAHAN -->
                        <div class="tab-pane fade" id="akademik-tambahan-tab">
                            <?php //include __DIR__ . '/f-akademik-tambahan.php'; ?>
                            <div id="akademik-tambahan-content" class="text-center py-3">
                                <?= h(tr('data_loading_records', 'Memuatkan Data...')) ?>
                            </div>                          
                        </div>
                      </div>
                    </div>
                  </div>
                </div>              

                <!-- Tab 3: Penglibatan Program -->
                <div class="tab-pane fade" id="penglibatan-program-tab" role="tabpanel">    
                    <div id="penglibatan-content" class="text-center py-3">
                        <?= h(tr('data_loading_records', 'Memuatkan Data...')) ?>
                    </div>
                </div>

                <!-- Tab 4: Jawatan Yang Disandang -->
                <div class="tab-pane fade" id="jawatan-disandang-tab" role="tabpanel">
                    <div id="jawatan-content" class="text-center py-3">
                        <?= h(tr('data_loading_records', 'Memuatkan Data...')) ?>
                    </div>
                </div>

                <!-- Tab 5: Anugerah dan Pengiktirafan -->
                <div class="tab-pane fade" id="anugerah-pengiktirafan-tab" role="tabpanel">
                    <div id="anugerah-content" class="text-center py-3">
                        <?= h(tr('data_loading_records', 'Memuatkan Data...')) ?>
                    </div>
                </div>

                <!-- Tab 6: Perakauan Pemohon -->
                <div class="tab-pane fade" id="perakuan-pemohon-tab" role="tabpanel">
                    <?php include __DIR__ . '/f-perakuan.php'; ?>
                </div>

              </div>
            <?php endif; ?>
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
          akademikTambahan: {},
          gredPSM: {},
          penglibatan: {},
          jawatan: {},
          anugerah: {},
          perakuan: {}
      };        
  </script> 

  <script src="<?= base_url('pages/iStar/permohonan/konvo/helpers/TranslationHelper.php?v=' . time()) ?>"></script>
  <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script> 
  <?php if (!$alreadyApplied): ?> 
    <script src="<?= base_url('assets/js/pages/istar-konvo.js?v=' . time()) ?>"></script> 
    <script src="<?= base_url('assets/js/pages/istar-akademik-tambahan.js?v=' . time()) ?>"></script> 
    <script src="<?= base_url('assets/js/pages/istar-penglibatan.js?v=' . time()) ?>"></script> 
    <script src="<?= base_url('assets/js/pages/istar-jawatan-disandang.js?v=' . time()) ?>"></script>   
    <script src="<?= base_url('assets/js/pages/istar-anugerah-pengiktirafan.js?v=' . time()) ?>"></script>   
    <link rel="stylesheet" href="<?= base_url('assets/css/pages/konvo.css') ?>">
  <?php endif; ?>
  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
