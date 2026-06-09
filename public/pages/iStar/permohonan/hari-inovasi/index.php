<?php
// pages/iStar/permohonan/hari-inovasi/index.php
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
  
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = false;

  require_once __DIR__ . '/../../../../includes/init.php';
  require_login();
  require_once __DIR__ . '/../../../../includes/functions-page.php'; 
  $pageHeading     = tr('page_heading_anugerah_pingat_graduan', 'Anugerah Pingat Graduan');
  $profileCardLabel = tr('profile_student_label', 'Profil Pelajar');
  $copyIdLabel      = tr('profile_btn_copy_no_matrik', 'Salin No. Matrik');
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
        processing: <?= json_encode(tr('data_processing', 'Sedang diproses...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        loading: <?= json_encode(tr('data_loading', 'Sedang memuatkan...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        syncronizing: <?= json_encode(tr('data_synchronizing', 'Menyelaraskan data...'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
      };
      window.konvoI18n = <?= json_encode([
        'add_new' => tr('button_add_new', 'Tambah Baru'),
        'sync_istad' => tr('sync_istad', 'Sync IStAD'),
        'load_data_failed' => tr('load_data_failed', 'Gagal load data'),
        'datatable_search_placeholder' => tr('datatable_search_placeholder', 'Search'),
        'datatable_length_menu' => tr('datatable_length_menu', 'Show _MENU_ records'),
        'datatable_info' => tr('datatable_info', 'Showing _START_ to _END_ of _TOTAL_ records'),
        'datatable_info_empty' => tr('datatable_info_empty', 'Showing 0 to 0 of 0 records'),
        'datatable_empty_table' => tr('datatable_empty_table', 'No records found'),
        'datatable_zero_records' => tr('datatable_zero_records', 'No matching records'),
        'datatable_next' => tr('datatable_next', 'Next'),
        'datatable_previous' => tr('datatable_previous', 'Previous'),
        'swal_failed_title' => tr('swal_failed_title', 'Gagal'),
        'swal_success_title' => tr('swal_success_title', 'Berjaya'),
        'swal_system_error_title' => tr('swal_system_error_title', 'Ralat Sistem'),
        'swal_try_again_later' => tr('swal_try_again_later', 'Cuba lagi sebentar lagi'),
        'swal_try_again' => tr('swal_try_again', 'Cuba lagi'),
        'swal_delete_record_title' => tr('swal_delete_record_title', 'Padam rekod ini?'),
        'swal_delete_award_title' => tr('swal_delete_award_title', 'Padam rekod anugerah ini?'),
        'swal_delete_warning' => tr('swal_delete_warning', 'Tindakan ini tidak boleh dibatalkan!'),
        'swal_confirm_delete' => tr('swal_confirm_delete', 'Ya, padam'),
        'swal_cancel' => tr('swal_cancel', 'Batal'),
        'swal_ok' => tr('swal_ok', 'OK'),
        'record_delete_success' => tr('record_delete_success', 'Rekod berjaya dipadam'),
        'record_delete_failed' => tr('record_delete_failed', 'Gagal padam rekod'),
        'award_add_success' => tr('award_add_success', 'Rekod anugerah berjaya ditambah'),
        'award_save_failed' => tr('award_save_failed', 'Gagal simpan rekod anugerah'),
        'award_delete_success' => tr('award_delete_success', 'Rekod anugerah berjaya dipadam'),
        'award_delete_failed' => tr('award_delete_failed', 'Gagal padam rekod anugerah'),
        'award_invalid_id' => tr('award_invalid_id', 'ID rekod anugerah tidak sah'),
        'record_update_failed' => tr('record_update_failed', 'Gagal kemaskini rekod'),
        'system_error_try_again' => tr('system_error_try_again', 'Ralat sistem. Cuba lagi.'),
        'sync_istad_title' => tr('sync_istad_title', 'Sync data IStAD?'),
        'sync_istad_text' => tr('sync_istad_text', 'Data IStAD akan dikemaskini semula. Data Tambahan tidak akan berubah.'),
        'sync_istad_confirm' => tr('sync_istad_confirm', 'Ya, sync'),
        'sync_success_title' => tr('sync_success_title', 'Penyelarasan Data Berjaya'),
        'sync_failed' => tr('sync_failed', 'Penyelarasan data gagal'),
      ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
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
              searchPlaceholder: <?= json_encode(tr('datatable_search_placeholder', 'Search'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
              lengthMenu: <?= json_encode(tr('datatable_length_menu', 'Show _MENU_ records'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
              info: <?= json_encode(tr('datatable_info', 'Showing _START_ to _END_ of _TOTAL_ records'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
              infoEmpty: <?= json_encode(tr('datatable_info_empty', 'Showing 0 to 0 of 0 records'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
              emptyTable: <?= json_encode(tr('datatable_empty_table', 'No records found'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
              zeroRecords: <?= json_encode(tr('datatable_zero_records', 'No matching records'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
              paginate: {
                next: <?= json_encode(tr('datatable_next', 'Next'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                previous: <?= json_encode(tr('datatable_previous', 'Previous'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
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
