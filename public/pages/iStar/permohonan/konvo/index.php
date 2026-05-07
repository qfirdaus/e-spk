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
  require_once __DIR__ . '/../../../../controllers/ProfileController.php'; 
  require_once __DIR__ . '/../../../../controllers/PeribadiController.php'; 
  require_once __DIR__ . '/../../../../controllers/PenglibatanController.php';   
  require_once __DIR__ . '/../../../../includes/functions-page.php'; 
  include __DIR__ . '/../../../../includes/header.php';

  // Check active session status
  $profile_controller = new ProfileController();
  $profile = $profile_controller->getCurrentUserProfile();
  $profileView = $profile;
  $loginActivity = $profile_controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $isActive = hasActiveSession($loginActivity);
  
  $peribadiController = new PeribadiController();
  $peribadi = $peribadiController->getCurrentUserDetailsInfo();

  $penglibatanController = new PenglibatanController();
  $penglibatanData = $penglibatanController->getAllPenglibatan();

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
                  <?php include __DIR__ . '/f-penglibatan-program.php'; ?>        
              </div>

              <!-- Tab 3: Jawatan Yang Disandang -->
              <div class="tab-pane fade" id="jawatan-disandang-tab" role="tabpanel">
                  <?php //include __DIR__ . '/f-jawatan-disandang.php'; ?> 
              </div>

              <!-- Tab 4: Anugerah dan Pengiktirafan -->
              <div class="tab-pane fade" id="anugerah-pengiktirafan-tab" role="tabpanel">
                  <?php //include __DIR__ . '/f-anugerah-pengiktirafan.php'; ?>
              </div>

              <!-- Tab 5: Perakauan Pemohon -->
              <div class="tab-pane fade" id="perakuan-pemohon-tab" role="tabpanel">
                  <?php //include __DIR__ . '/f-perakuan.php'; ?>
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
  <script src="<?= base_url('assets/js/pages/penglibatan.js') ?>"></script>

<!-- <script>
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) {
      return;
    }

    const addModalEl = document.getElementById('sampleAddModal');
    const editModalEl = document.getElementById('sampleEditModal');
    const viewModalEl = document.getElementById('sampleViewModal');
    const addModal = (window.bootstrap && addModalEl) ? new bootstrap.Modal(addModalEl) : null;
    const editModal = (window.bootstrap && editModalEl) ? new bootstrap.Modal(editModalEl) : null;
    const viewModal = (window.bootstrap && viewModalEl) ? new bootstrap.Modal(viewModalEl) : null;

    const dt = jQuery('#userDT').DataTable({
      pageLength: 10,
      lengthChange: true,
      lengthMenu: [10, 25, 50, 100, 200],
      ordering: true,
      order: [[1,'asc']],
      autoWidth: false,
      scrollX: false,
      dom:
        '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>' +
        't' +
        '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
      language: {
        lengthMenu: <?= json_encode('Show _MENU_ records') ?>,
        search: '',
        info: <?= json_encode('Showing _START_ to _END_ of _TOTAL_ records') ?>,
        infoEmpty: <?= json_encode('Showing 0 to 0 of 0 records') ?>,
        emptyTable: <?= json_encode('No records') ?>,
        paginate: {
          previous: <?= json_encode('Previous') ?>,
          next: <?= json_encode('Next') ?>
        },
        zeroRecords: <?= json_encode('No matching records found') ?>
      },
      columnDefs: [
        { targets: 0, orderable:false, searchable:false, width: 56 },
        { targets: 5, orderable:false, searchable:false, width: 110 }
      ],
      rowCallback: function(row, data, displayIndex){
        const api = this.api();
        const info = api.page.info();
        jQuery('td:eq(0)', row).text(info.start + displayIndex + 1);
      },
      initComplete: function() {
        if (window.DataTableStandard && typeof window.DataTableStandard.decorate === 'function') {
          window.DataTableStandard.decorate('#userDT', {
            searchPlaceholder: <?= json_encode('Search') ?>
          });
        }
      }
    });

    function parseRowPayload(btn) {
      try {
        return JSON.parse(btn.getAttribute('data-row') || '{}');
      } catch (err) {
        return {};
      }
    }

    function accessLabel(flag) {
      return String(flag) === '1'
        ? <?= json_encode(tr('template_senarai_crud_access_allowed', 'Allowed')) ?>
        : <?= json_encode(tr('template_senarai_crud_access_blocked', 'Blocked')) ?>;
    }

    function openAddModal() {
      if (!addModal) return;
      const addForm = document.getElementById('sampleAddForm');
      if (addForm) {
        addForm.reset();
      }
      addModal.show();
    }

    function openViewModal(data) {
      if (!viewModal) return;
      document.getElementById('sampleViewName').textContent = data.name || '-';
      document.getElementById('sampleViewDepartment').textContent = data.department || '-';
      document.getElementById('sampleViewGroup').textContent = data.group_name || '-';
      document.getElementById('sampleViewAccess').textContent = accessLabel(data.access_flag ?? 1);
      document.getElementById('sampleViewDescription').textContent = data.description || '-';
      document.getElementById('sampleViewUpdatedAt').textContent = data.updated_at || '-';
      viewModal.show();
    }

    function openEditModal(data) {
      if (!editModal) return;
      document.getElementById('sampleEditId').value = data.id || '';
      document.getElementById('sampleEditName').value = data.name || '';
      document.getElementById('sampleEditDepartment').value = data.department || '';
      document.getElementById('sampleEditGroup').value = data.group_name || '';
      document.getElementById('sampleEditAccess').value = String(data.access_flag ?? 1);
      editModal.show();
    }

    jQuery(document).on('click', '#btnSampleAction', function(){
      openAddModal();
    });

    jQuery(document).on('click', '.js-view-row', function(){
      openViewModal(parseRowPayload(this));
    });

    jQuery(document).on('click', '.js-edit-row', function(){
      openEditModal(parseRowPayload(this));
    });

    jQuery(document).on('click', '.js-delete-row', function(){
      const data = parseRowPayload(this);
      if (!window.Swal) return;
      Swal.fire({
        icon: 'warning',
        title: <?= json_encode(tr('template_senarai_crud_delete_title', 'Delete Sample Record?')) ?>,
        text: <?= json_encode(tr('template_senarai_crud_delete_text', 'This is only a frontend sample. No backend action will be triggered.')) ?>,
        showCancelButton: true,
        confirmButtonText: <?= json_encode(tr('template_senarai_crud_btn_yes', 'Yes')) ?>,
        cancelButtonText: <?= json_encode(tr('template_senarai_crud_btn_no', 'No')) ?>
      }).then(function(result){
        if (!result.isConfirmed) return;
        Swal.fire({
          icon: 'success',
          title: <?= json_encode(tr('template_senarai_crud_delete_success_title', 'Sample Delete Complete')) ?>,
          text: (data.name || 'Record') + ' ' + <?= json_encode(tr('template_senarai_crud_delete_success_text', 'was processed as a sample action without backend interaction.')) ?>,
          confirmButtonText: <?= json_encode(tr('template_senarai_crud_btn_ok', 'OK')) ?>
        });
      });
    });

    jQuery('#sampleEditSaveBtn').on('click', function(){
      if (!window.Swal) return;
      if (editModal) {
        editModal.hide();
      }
      Swal.fire({
        icon: 'success',
        title: <?= json_encode(tr('template_senarai_crud_edit_success_title', 'Sample Save Complete')) ?>,
        text: <?= json_encode(tr('template_senarai_crud_edit_success_text', 'The sample edit flow completed without sending any data to the backend.')) ?>,
        confirmButtonText: <?= json_encode(tr('template_senarai_crud_btn_ok', 'OK')) ?>
      });
    });

    jQuery('#sampleAddSaveBtn').on('click', function(){
      if (!window.Swal) return;
      if (addModal) {
        addModal.hide();
      }
      Swal.fire({
        icon: 'success',
        title: <?= json_encode(tr('template_senarai_crud_sample_add_success_title', 'Sample Add Complete')) ?>,
        text: <?= json_encode(tr('template_senarai_crud_sample_add_success_text', 'The sample add flow completed without sending any data to the backend.')) ?>,
        confirmButtonText: <?= json_encode(tr('template_senarai_crud_btn_ok', 'OK')) ?>
      });
    });

    if (window.bootstrap) {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        new bootstrap.Tooltip(element);
      });
    }
  });
})();
</script> -->


  <div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
