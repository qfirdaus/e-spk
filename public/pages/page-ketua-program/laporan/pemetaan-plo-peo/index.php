<?php
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
  $NEED_SELECT2    = true;   

  $PAGE_TITLE       = tr('spk_title', 'SPK');
  $pageHeading      = tr('pemetaan_plo_peo', 'Pemetaan PLO - PEO'); 
  $profileCardLabel = tr('profile_staf_label', 'Profil Staf');
  $copyIdLabel      = tr('profile_btn_copy_no_staf', 'Salin No. Staf');
  
  include __DIR__ . '/../../../../includes/header.php';
  require_once __DIR__ . '/../../../../controllers/ProfileController.php'; 

  // Check active session status
  $profile_controller = new ProfileController();
  $profile            = $profile_controller->getCurrentUserProfile();
  $profileView        = $profile;
  $loginActivity      = $profile_controller->getLoginActivity();
  $isActive           = hasActiveSession($loginActivity);
  
  $errorMessage = "" ; 
  $istarPerakuanIdPrefix = 'spk';
?>

<body data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>" data-menu-color="<?= h($_SESSION['theme.sidebar'] ?? 'dark') ?>" data-layout="vertical" class="loading">
  <div class="wrapper">
    <?php include __DIR__ . '/../../../../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../../../../includes/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">
          <div class="row mb-3">
            <div class="col-12">
              <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="page-title"><i class="ri-grid-fill me-1"></i> <?= h($pageHeading) ?></h4>
              </div>
            </div>
          </div>

          <div class="card border-0 shadow-sm profile-card">
            <?php include __DIR__ . '/../../../../includes/profile-card.php'; ?>
            
            <ul class="nav nav-tabs profile-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pemetaan-tab" role="tab">
                  <i class="ri-node-tree me-1"></i> <?= h(tr('tab_pemetaan_plopeo','Pemetaan PLO & PEO')) ?>
                </a>
              </li>            
            </ul>

            <div class="tab-content p-4">
              <div class="tab-pane fade show active" id="pemetaan-tab" role="tabpanel">
                <?php include __DIR__ . '/list-pemetaan.php'; ?>
              </div>
            </div>
          </div>    
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
        processing: <?= json_encode(tr('data_processing', 'Sedang diproses...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        loading: <?= json_encode(tr('data_loading', 'Sedang memuatkan...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        syncronizing: <?= json_encode(tr('data_synchronizing', 'Menyelaraskan data...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
      };        
  </script> 

  <?php if ($NEED_SELECT2): ?>
    <script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>?v=<?= time(); ?>"></script>
  <?php endif; ?>
  
  <script src="<?= base_url('assets/js/pages/pages-main.js?v=' . time()) ?>"></script>  
  <script src="<?= base_url('assets/js/pages/spk-pemetaan-plo-peo.js?v=' . time()) ?>"></script>
  <link rel="stylesheet" href="<?= base_url('assets/css/pages/spk-main.css') ?>">

  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>     
</body>
</html>