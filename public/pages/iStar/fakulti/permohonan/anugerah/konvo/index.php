<?php
// pages/iStar/fakulti/permohonan/anugerah/konvo/index.php
  declare(strict_types=1);
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = false;

  require_once __DIR__ . '/../../../../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../../../../controllers/KeluargaController.php'; 
  require_once __DIR__ . '/../../../../../../includes/functions-page.php'; 
  $PAGE_TITLE = tr('istar_title', 'iStar');
  include __DIR__ . '/../../../../../../includes/header.php';
  include __DIR__ . '/../../../../../../actions/retrieve-data-keluarga.php';

  // Check active session status
  $isActive = hasActiveSession($loginActivity);
?>

<body
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.sidebar'] ?? 'dark') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../../../../../../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../../../../../../includes/sidebar.php'; ?>

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
          <?php include __DIR__ . '/../../../../../../includes/profile-card.php'; ?>

          <!-- Tab Navigasi -->
          <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#pelajar-diraja-tab" role="tab">
                <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_pelajar_diraja','Pelajar Diraja')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#pingat-emas-canselor-tab" role="tab">
                <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_pingat_emas_canselor','Pingat Emas Canselor')) ?>
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
              <a class="nav-link" data-bs-toggle="tab" href="#tokoh-keusahawanan-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_tokoh_keusahawanan','Tokoh Keusahawanan')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#khas-bem-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_khas_bem','Khas BEM')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#senarai-pencalonan-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_senarai_akhir_pencalonan','Senarai Akhir Pencalonan')) ?>
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

            <!-- Tab 1: Anugerah  -->
            <div class="tab-pane fade show active" id="pelajar-diraja-tab" role="tabpanel">
                <?php include __DIR__ . '/f-pelajar-diraja.php'; ?>
            </div>

            <!-- Tab 2: Pingat Emas Canselor -->
            <div class="tab-pane fade" id="pingat-emas-canselor-tab" role="tabpanel">    
                <?php include __DIR__ . '/../../../../permohonan/konvo/f-penglibatan-program.php'; ?>        
            </div>

            <!-- Tab 3: Pingat Emas LPU -->
            <div class="tab-pane fade" id="pingat-emas-lpu-tab" role="tabpanel">
                <?php include __DIR__ . '/../../../../permohonan/konvo/f-jawatan-disandang.php'; ?> 
            </div>

            <!-- Tab 4: Pingat Emas Naib Canselor -->
            <div class="tab-pane fade" id="pingat-emas-nc-tab" role="tabpanel">
                <?php include __DIR__ . '/../../../../permohonan/konvo/f-anugerah-pengiktirafan.php'; ?>
            </div>

            <!-- Tab 5: Buku Ijazah Sarjana Muda -->
            <div class="tab-pane fade" id="buku-degree-tab" role="tabpanel">
                <?php $istarPerakuanIdPrefix = 'konvo-buku-degree'; include __DIR__ . '/../../../../permohonan/konvo/f-perakuan.php'; unset($istarPerakuanIdPrefix); ?>
            </div>

            <!-- Tab 6: Diploma Terbaik -->
            <div class="tab-pane fade" id="diploma-terbaik-tab" role="tabpanel">
                <?php $istarPerakuanIdPrefix = 'konvo-diploma-terbaik'; include __DIR__ . '/../../../../permohonan/konvo/f-perakuan.php'; unset($istarPerakuanIdPrefix); ?>
            </div>

            <!-- Tab 7: Buku Diploma -->
            <div class="tab-pane fade" id="buku-diploma-tab" role="tabpanel">
                <?php $istarPerakuanIdPrefix = 'konvo-buku-diploma'; include __DIR__ . '/../../../../permohonan/konvo/f-perakuan.php'; unset($istarPerakuanIdPrefix); ?>
            </div>

            <!-- Tab 8: Tokoh Keusahawanan -->
            <div class="tab-pane fade" id="tokoh-keusahawanan-tab" role="tabpanel">
                <?php $istarPerakuanIdPrefix = 'konvo-tokoh-keusahawanan'; include __DIR__ . '/../../../../permohonan/konvo/f-perakuan.php'; unset($istarPerakuanIdPrefix); ?>
            </div>

            <!-- Tab 9: Khas BEM -->
            <div class="tab-pane fade" id="khas-bem-tab" role="tabpanel">
                <?php $istarPerakuanIdPrefix = 'konvo-khas-bem'; include __DIR__ . '/../../../../permohonan/konvo/f-perakuan.php'; unset($istarPerakuanIdPrefix); ?>
            </div>

            <!-- Tab 10: Senarai Pencalonan -->
            <div class="tab-pane fade" id="senarai-pencalonan-tab" role="tabpanel">
                <?php include __DIR__ . '/f-senarai-pencalonan.php'; ?>
            </div>

          </div>
        </div>
        <!-- /Profile Card with Tabs -->    

      </div>
    </div>
    <?php include __DIR__ . '/../../../../../../includes/footer.php'; ?>
  </div>
</div>

<?php 
  include __DIR__ . '/../../../../../../includes/script.php'; 
  include __DIR__ . '/../../../../../../includes/script-pages.php';  
  include __DIR__ . '/../../../../../../includes/script-custom.php';
?>

<div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
