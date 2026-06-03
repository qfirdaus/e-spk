<?php
  // pages/pengesahan-pelajar.php
  declare(strict_types=1);
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = false;
  $NEED_SELECT2    = true;
  $pageHeading     = 'Pengesahan Pelajar';
  $profileCardLabel = 'Profil Pelajar';
  $copyIdLabel      = 'Salin No. Matrik';

  require_once __DIR__ . '/../../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../../includes/functions-page.php'; 
  include __DIR__ . '/../../../../includes/header.php';
  include __DIR__ . '/../../../../actions/retrieve-data-peribadi.php';

  // Check active session status
  $isActive = hasActiveSession($loginActivity);
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
                <a class="nav-link active" data-bs-toggle="tab" href="#semak_pengesahan_pelajar-tab" role="tab">
                    <i class="ri-todo-line me-1"></i> <?= h(tr('tab_semak_pengesahan_pelajar','Pengesahan Pelajar')) ?>
                </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#semak_surat_jaminan-tab" role="tab">
                    <i class="ri-shield-check-line me-1"></i> <?= h(tr('tab_semak_surat_jaminan', 'Surat Jaminan (GL)')) ?>
                </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#semak_pengesahan_insuran-tab" role="tab">
                    <i class="ri-health-book-line me-1"></i> <?= h(tr('tab_semak_pengesahan_insuran', 'Pengesahan Insuran')) ?>
                </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#semak_bantuan_kes_khas-tab" role="tab">
                    <i class="ri-hand-heart-line me-1"></i> <?= h(tr('tab_semak_bantuan_kes_khas', 'Bantuan Kes Khas')) ?>
                </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#semak_bantuan_pembelajaran-tab" role="tab">
                    <i class="ri-book-open-line me-1"></i> <?= h(tr('tab_semak_bantuan_pembelajaran', 'Bantuan Pembelajaran')) ?>
                </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#semak_pembiayaan_pengajian-tab" role="tab">
                    <i class="ri-graduation-cap-line me-1"></i> <?= h(tr('tab_semak_pembiayaan_pengajian', 'Pembiayaan Pengajian')) ?>
                </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#semak_kemudahan_kenderaan-tab" role="tab">
                    <i class="ri-bus-line me-1"></i> <?= h(tr('tab_semak_kemudahan_kenderaan', 'Kemudahan Kenderaan')) ?>
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
              
              <!-- Tab 1: Semak Pengesahan Pelajar -->
              <div class="tab-pane fade show active" id="semak_pengesahan_pelajar-tab" role="tabpanel"> 
                <?php //include __DIR__ . '/list-pengesahan-pelajar.php'; ?>
                <div id="pengesahan-pelajar-container">
                    <div class="text-muted">Memuatkan data...</div>
                </div>
              </div>

              <!-- Tab 2: Semak Surat Jaminan -->
              <div class="tab-pane fade show" id="semak_surat_jaminan-tab" role="tabpanel">                
                <?php include __DIR__ . '/../../../rekod-utama/data-akademik/f-akademik.php'; ?>
              </div>    

              <!-- Tab 3: Semak Pengesahan Insuran -->
              <div class="tab-pane fade show" id="semak_pengesahan_insuran-tab" role="tabpanel">
                <?php  //include __DIR__ . '/f-penerima.php'; ?>
                <div id="penerima-content"></div>
              </div>   

              <!-- Tab 4: Semak Bantuan Kes Khas -->
              <div class="tab-pane fade show" id="semak_bantuan_kes_khas-tab" role="tabpanel">
                <?php  //include __DIR__ . '/f-penerima.php'; ?>
                <div id="penerima-content"></div>
              </div>   

              <!-- Tab 5: Semak Bantuan Pembelajaran -->
              <div class="tab-pane fade show" id="tab_semak_bantuan_pembelajaran-tab" role="tabpanel">
                <?php  //include __DIR__ . '/f-penerima.php'; ?>
                <div id="penerima-content"></div>
              </div>   

              <!-- Tab 6: Semak Pembiayaan Pengajian -->
              <div class="tab-pane fade show" id="semak_pembiayaan_pengajian-tab" role="tabpanel">
                <?php  //include __DIR__ . '/f-penerima.php'; ?>
                <div id="penerima-content"></div>
              </div> 

              <!-- Tab 7: Semak Kemudahan Kenderaan -->
              <div class="tab-pane fade show" id="semak_kemudahan_kenderaan-tab" role="tabpanel">
                <?php  include __DIR__ . '/f-kemudahan-kenderaan.php'; ?>
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
  ?>
  <script> 
      const base_url = "<?= rtrim(base_url(), '/') . '/' ?>"; 
      const msg_load = {
        processing: "<?= h(tr('data_processing', 'Sedang diproses...')) ?>",
        loading: "<?= h(tr('data_loading', 'Sedang memuatkan...')) ?>",
        syncronizing: "<?= h(tr('data_synchronizing', 'Menyelaraskan data...')) ?>"
      };    
  </script> 
  <script src="<?= base_url('assets/js/pages/icares-semakan-permohonan.js?v=' . time()) ?>"></script> 
  <link rel="stylesheet" href="<?= base_url('assets/css/pages/semakan.css') ?>">
  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
