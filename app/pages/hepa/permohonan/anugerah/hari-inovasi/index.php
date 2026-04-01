<?php
// pages/m-anugerah-konvo.php
  declare(strict_types=1);
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = false;

  require_once __DIR__ . '/../../../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../../../controllers/KeluargaController.php'; 
  require_once __DIR__ . '/../../../../../includes/functions-page.php'; 
  include __DIR__ . '/../../../../../includes/header.php';
  include __DIR__ . '/../../../../../actions/retrieve-data-keluarga.php';

  // Check active session status
  $isActive = hasActiveSession($loginActivity);
?>

<body
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.sidebar'] ?? 'dark') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../../../../../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../../../../../includes/sidebar.php'; ?>

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
        <div class="card border-0 shadow-sm profile-card">
          <?php include __DIR__ . '/../../../../../includes/profile-card.php'; ?>

          <!-- Tab Navigasi -->
          <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#kepimpinan-siswa-tab" role="tab">
                <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_kepimpinan_siswa','Kepimpinan Siswa')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#kesukarelawan-siswa-tab" role="tab">
                <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_kesukarelawan_siswa','Kesukarelawan Siswa')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#pingat-emas-lpu-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_pingat_emas_lpu','Pingat Emas LPU (Kepimpinan)')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#pingat-emas-nc-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_pingat_emas_nc','Emas Naib Canselor')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#buku-degree-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_buku_degree','Buku (Ijazah Sarjana Muda)')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#diploma-terbaik-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_diploma_terbaik','Diploma Terbaik')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#buku-diploma-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_diploma_buku','Buku (Diploma)')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#senarai-pencalonan-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_senarai_pencalonan','Senarai Pencalonan')) ?>
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

            <!-- Tab 1: Kepimpinan Siswa  -->
            <div class="tab-pane fade show active" id="kepimpinan-siswa-tab" role="tabpanel">
                <?php include __DIR__ . '/f-kepimpinan-siswa.php'; ?>
            </div>

            <!-- Tab 2: Kesukarelawan Siswa -->
            <div class="tab-pane fade" id="kesukarelawan-siswa-tab" role="tabpanel">    
                <?php include __DIR__ . '/f-kesukarelawan-siswa.php'; ?>        
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
            <div class="tab-pane fade" id="perakauan-pemohon-tab" role="tabpanel">
                <?php include __DIR__ . '/f-perakuan.php'; ?>
            </div>

            <!-- Tab 5: Perakauan Pemohon -->
            <div class="tab-pane fade" id="perakauan-pemohon-tab" role="tabpanel">
                <?php include __DIR__ . '/f-perakuan.php'; ?>
            </div>

            <!-- Tab 5: Perakauan Pemohon -->
            <div class="tab-pane fade" id="perakauan-pemohon-tab" role="tabpanel">
                <?php include __DIR__ . '/f-perakuan.php'; ?>
            </div>

            <!-- Tab 11: Senarai Pencalonan -->
            <div class="tab-pane fade" id="senarai-pencalonan-tab" role="tabpanel">
                <?php include __DIR__ . '/f-senarai-pencalonan.php'; ?>
            </div>

          </div>
        </div>
        <!-- /Profile Card with Tabs -->    

      </div>
    </div>
    <?php include __DIR__ . '/../../../../../includes/footer.php'; ?>
  </div>
</div>

<?php 
  include __DIR__ . '/../../../../../includes/script.php'; 
  include __DIR__ . '/../../../../../includes/script-pages.php';  
  include __DIR__ . '/../../../../../includes/script-custom.php';
?>

<div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
