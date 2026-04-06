<?php
  // pages/data-keluarga.php
  declare(strict_types=1);
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = false;

  require_once __DIR__ . '/../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../controllers/KeluargaController.php'; 
  require_once __DIR__ . '/../../../includes/functions-page.php'; 
  include __DIR__ . '/../../../includes/header.php';
  include __DIR__ . '/../../../actions/retrieve-data-keluarga.php';

  // Check active session status
  $isActive = hasActiveSession($loginActivity);
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
                <?= h(tr('profile_title','Profil Pengguna')) ?>
              </h4>
              <div class="page-title-right">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">
                    <a href="<?= base_url('pages/dashboard.php') ?>">
                      <i class="ri-home-4-line align-middle me-1"></i>
                      <?= h(tr('profile_breadcrumb_dashboard','Papan Pemuka')) ?>
                    </a>
                  </li>
                  <li class="breadcrumb-item active">
                    <?= h(tr('profile_breadcrumb','Profil')) ?>
                  </li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <!-- Profile Card with Tabs -->
          <!-- Profile Card with Tabs -->
          <div class="card border-0 shadow-sm profile-card">
            <?php include __DIR__ . '/../../../includes/profile-card.php'; ?>
            
            <!-- Tab Navigasi -->
            <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#maklumat-bapa-tab" role="tab">
                  <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_maklumat_bapa','Maklumat Bapa')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-ibu-tab" role="tab">
                  <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_maklumat_ibu', 'Maklumat Ibu')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-penjaga-tab" role="tab">
                  <i class="ri-briefcase-line me-1"></i> <?= h(tr('tab_maklumat_penjaga', 'Maklumat Penjaga')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-adik-beradik-tab" role="tab">
                  <i class="ri-team-fill me-1"></i> <?= h(tr('tab_maklumat_adik_beradik', 'Maklumat Adik Beradik')) ?>
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

              <!-- Tab 1: Maklumat Bapa -->
              <div class="tab-pane fade show active" id="maklumat-bapa-tab" role="tabpanel"> 
                <?php include __DIR__ . '/f-bapa.php'; ?>
              </div>

              <!-- Tab 2: Maklumat Ibu -->
              <div class="tab-pane fade show" id="maklumat-ibu-tab" role="tabpanel">                
                <?php include __DIR__ . '/f-ibu.php'; ?>
              </div>    

              <!-- Tab 3: Maklumat Penjaga -->
              <div class="tab-pane fade show" id="maklumat-penjaga-tab" role="tabpanel">
                <?php  include __DIR__ . '/f-penjaga.php'; ?>
              </div>

              <!-- Tab 3: Maklumat Adik Beradik -->
              <div class="tab-pane fade show" id="maklumat-adik-beradik-tab" role="tabpanel">
                <?php  include __DIR__ . '/f-adik-beradik.php'; ?>
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
    include __DIR__ . '/../../../includes/script-custom.php';    
  ?>

<div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
