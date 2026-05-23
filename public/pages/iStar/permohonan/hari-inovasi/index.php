<?php
// pages/iStar/permohonan/hari-inovasi/index.php
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

  // Check active session status
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $isActive = hasActiveSession($loginActivity);

  $peribadiController = new PeribadiController();
  $peribadi = $peribadiController->getCurrentUserDetailsInfo();
  $errorMessage = $peribadiController->getErrorMessage();
  $stafID = trim((string)($_SESSION['f_stafID'] ?? ''));
  $namaPenuh = (string)($peribadi['nama_penuh'] ?? ($profileView['nama_penuh'] ?? ''));
  $nokp = (string)($peribadi['nokp'] ?? '');
  $istarPerakuanIdPrefix = 'hari-inovasi-perakuan';
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
                      <?= h(tr('profile_breadcrumb_dashboard','Papan Pemuka')) ?>
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
                <?php include __DIR__ . '/f-penglibatan-program.php'; ?>        
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

  // Layout helpers used by Konvo page (load base_url + messages and page script)
  ?>
  <script>
      const base_url = "<?= rtrim(base_url(), '/') . '/' ?>";
      const msg_load = {
        processing: "<?= h(tr('data_processing', 'Sedang diproses...')) ?>",
        loading: "<?= h(tr('data_loading', 'Sedang memuatkan...')) ?>",
        syncronizing: "<?= h(tr('data_synchronizing', 'Menyelaraskan data...')) ?>"
      };
  </script>
  <script src="<?= base_url('assets/js/pages/konvo.js?v=' . time()) ?>"></script>
  <link rel="stylesheet" href="<?= base_url('assets/css/pages/konvo.css') ?>">
  <script>
    jQuery(function(){
      try {
        jQuery('.hari-inovasi-table').each(function () {
          const table = this;

          if (jQuery.fn.DataTable.isDataTable(table)) {
            jQuery(table).DataTable().destroy();
          }

          jQuery(table).DataTable({
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [10, 25, 50, 100],
            ordering: true,
            autoWidth: false,
            scrollX: false,
            responsive: true,
            dom:
              "<'row mb-2'<'col-sm-12 col-md-6 dt-top-left'l><'col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right'f>>" +
              "<'row'<'col-sm-12'tr>>" +
              "<'dt-bottom-row mt-2 d-flex justify-content-between align-items-center'<'dt-info-left'i><'dt-paging-right d-flex justify-content-end'p>>",
            language: {
              search: "",
              searchPlaceholder: "Search",
              lengthMenu: "Show _MENU_ records",
              info: "Showing _START_ to _END_ of _TOTAL_ records",
              infoEmpty: "Showing 0 to 0 of 0 records",
              emptyTable: "No records found",
              zeroRecords: "No matching records",
              paginate: {
                next: "Next",
                previous: "Previous"
              }
            },
            order: [],
            columnDefs: [
              {
                targets: 0,
                orderable: false,
                searchable: false,
                width: '56px',
                className: 'col-bil text-center'
              },
              {
                targets: -1,
                orderable: false,
                searchable: false,
                className: 'text-center'
              }
            ],
            createdRow: function (row) {
              jQuery('.js-tooltip-cell', row).each(function () {
                const cellText = jQuery(this).text().trim();

                if (!cellText) return;

                this.setAttribute('title', cellText);
                this.setAttribute('data-bs-toggle', 'tooltip');
                this.setAttribute('data-bs-placement', 'top');

                if (typeof bootstrap !== 'undefined' && !this._bsTooltip) {
                  this._bsTooltip = new bootstrap.Tooltip(this, {
                    boundary: 'window',
                    customClass: 'konvo-tooltip'
                  });
                }
              });
            },
            rowCallback: function (row, data, index) {
              const info = this.api().page.info();
              jQuery('td:eq(0)', row).html(info.start + index + 1);
            },
            destroy: true
          });
        });

        jQuery('[data-bs-toggle="tooltip"]').each(function () {
          if (!this._bsTooltip && typeof bootstrap !== 'undefined') {
            this._bsTooltip = new bootstrap.Tooltip(this, { boundary: 'window', customClass: 'konvo-tooltip' });
          }
        });
      } catch (e) {
        console.log('Init konvo helpers failed', e);
      }
    });
  </script>
  <?php
?>

<div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
