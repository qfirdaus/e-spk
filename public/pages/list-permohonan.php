<?php
// pages/list-permohonan.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/ListPermohonanController.php';

$controller = new ListPermohonanController();

$lang                = $controller->lang ?? 'ms';
$profile             = $controller->profile ?? [];
$senaraiPermohonan   = $controller->senaraiPermohonan ?? [];
$version             = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = (string) $_SESSION['csrf_token'];

// UI permission guard (use existing backend helper)
$canManageGroups = false;
try {
  require_once __DIR__ . '/../ajax/_helpers.php';
  $pdoPerm = Database::getInstance('mysql')->getConnection();
  $canManageGroups = hasGroupManagePermission($pdoPerm);
} catch (Throwable $e) {
  $canManageGroups = false;
}
$permDisabledAttr = $canManageGroups ? '' : 'disabled aria-disabled="true"';
$permDisabledClass = $canManageGroups ? '' : ' disabled';

// helper escape
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>

  <meta name="csrf-token" content="<?= h($csrf) ?>">
  <!-- ✅ Standard DataTables CSS (shared) -->
  <link href="<?= base_url('assets/css/datatables-standard.css') ?>?v=<?= h($version ?? date('ymdHis')) ?>" rel="stylesheet">
  <style>
    #permohonanTable { table-layout: fixed; width: 100%; }
    .form-access-table { width: 100%; }
    .form-access-table th, .form-access-table td { vertical-align: middle; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .form-access-table th.col-bil, .form-access-table td.col-bil { width: 5%; text-align: center; }
    .form-access-table th.col-no, .form-access-table td.col-no { width: 22%; text-align: left; }
    .form-access-table th.col-jenis, .form-access-table td.col-jenis { width: 13%; text-align: center; }
    .form-access-table th.col-servis, .form-access-table td.col-servis { width: 24%; text-align: left; }
    .form-access-table th.col-status, .form-access-table td.col-status { width: 14%; text-align: center; }
    .form-access-table th.col-tarikh, .form-access-table td.col-tarikh { width: 12%; text-align: center; }
    .form-access-table th.col-actions, .form-access-table td.col-actions { width: 10%; text-align: center; }
    .truncate-1line { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .icon-btn { padding: .25rem .5rem; line-height: 1; }
    .form-access-table .cell-inline {
      display: inline-flex;
      align-items: center;
      gap: .28rem;
      max-width: 100%;
      min-height: 1.4rem;
    }
    .form-access-table .group-chip,
    .form-access-table .access-chip {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: .12rem .34rem;
      min-height: 1rem;
      font-size: .64rem;
      line-height: 1;
      font-weight: 600;
      border-radius: 999px;
      border: 1px solid transparent;
      vertical-align: middle;
    }
    .form-access-table .group-chip {
      max-width: 100%;
      background: rgba(59, 130, 246, .10);
      color: #1d4ed8;
      border-color: rgba(59, 130, 246, .16);
    }
    .form-access-table .access-chip {
      min-width: 4.1rem;
      background: rgba(15, 23, 42, .04);
      color: #334155;
      border-color: rgba(148, 163, 184, .22);
    }
    .form-access-table .access-chip.is-allowed {
      background: rgba(16, 185, 129, .12);
      color: #0f766e;
      border-color: rgba(20, 184, 166, .16);
    }
    .form-access-table .access-chip.is-blocked {
      background: rgba(239, 68, 68, .10);
      color: #b91c1c;
      border-color: rgba(239, 68, 68, .14);
    }
    .form-access-table td.col-actions .btn,
    .form-access-table td.col-actions a.btn {
      box-shadow: none !important;
      width: 1.75rem;
      height: 1.75rem;
      padding: 0;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .form-access-table td.col-actions .btn + .btn,
    .form-access-table td.col-actions .btn + a.btn,
    .form-access-table td.col-actions a.btn + .btn,
    .form-access-table td.col-actions a.btn + a.btn {
      margin-left: .25rem !important;
    }
    .dt-bottom-row { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
    .dt-bottom-row .dataTables_info { margin: .25rem 0; white-space: nowrap; line-height: 1.5; }
    .dt-bottom-row .dataTables_paginate { margin-left: auto; }
    .dataTables_length, #permohonanTable_wrapper .dataTables_length {
      white-space: nowrap !important;
      line-height: 1.4;
      display: inline-block;
    }
    .dataTables_length label, #permohonanTable_wrapper .dataTables_length label {
      white-space: nowrap !important;
      display: inline-flex !important;
      align-items: center;
      gap: 0.4rem;
      margin-bottom: 0;
      flex-wrap: nowrap !important;
      font-size: 0.875rem !important;
    }
    .dataTables_length select, #permohonanTable_wrapper .dataTables_length select {
      display: inline-block !important;
      margin: 0 0.4rem !important;
      flex-shrink: 0 !important;
      height: 36px !important;
      min-height: 36px !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.4 !important;
      border: 2px solid #e9ecef !important;
      border-radius: 0.5rem !important;
      min-width: 70px !important;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }
    .dataTables_length select:hover, #permohonanTable_wrapper .dataTables_length select:hover { border-color: #ced4da !important; }
    .dataTables_length select:focus, #permohonanTable_wrapper .dataTables_length select:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25) !important;
      outline: none !important;
    }
    .dataTables_length label > * { white-space: nowrap !important; display: inline !important; }
    .dt-top-left { white-space: nowrap !important; flex-wrap: nowrap !important; }
    .dt-top-left .dataTables_length { white-space: nowrap !important; }
    /* Buttons in top-right: sizing, borders and gap consistent with senarai-pengguna */
    .dt-top-right button, #permohonanTable_wrapper .dt-top-right button {
      height: 36px !important;
      min-height: 36px !important;
      border: 2px solid #e9ecef !important;
      border-radius: 0.5rem !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.4 !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      white-space: nowrap !important;
    }
    .dt-top-right button:hover, #permohonanTable_wrapper .dt-top-right button:hover { border-color: #ced4da !important; }
    .dt-top-right button:focus, #permohonanTable_wrapper .dt-top-right button:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25) !important;
    }
    .dt-top-right { gap: 0.5rem !important; }
    .dt-top-right button + button { margin-left: 0 !important; }
    #permohonanTable_wrapper .dt-top-right,
    #permohonanTable_wrapper .dt-top-left {
      align-items: flex-start !important;
    }
    #permohonanTable_wrapper .dt-top-right > * {
      position: relative !important;
      top: 0 !important;
    }
    .form-access-table {
      border-radius: .75rem;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
    }
    .form-access-table thead th {
      background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
      color: #475569;
      font-size: .82rem;
      font-weight: 700;
      letter-spacing: .02em;
      text-transform: uppercase;
      padding: .82rem .78rem;
      border-bottom-color: rgba(148, 163, 184, .18);
    }
    .form-access-table tbody tr {
      height: 3rem;
      background: #fff;
      transition: background-color .18s ease;
    }
    .form-access-table tbody tr:hover,
    .form-access-table.table > tbody > tr:hover > *,
    .form-access-table > tbody > tr:hover > td {
      background: rgba(59, 130, 246, .045) !important;
    }
    .form-access-table tbody td {
      padding: .5rem .72rem;
      font-size: .84rem;
      line-height: 1.22;
      border-color: rgba(226, 232, 240, .78);
      background: transparent !important;
    }
    .form-access-table td.col-jenis,
    .form-access-table td.col-status,
    .form-access-table td.col-tarikh,
    .form-access-table td.col-actions {
      white-space: nowrap;
    }
    [data-bs-theme="dark"] .form-access-table thead th {
      background: rgba(30, 41, 59, .92);
      color: rgba(226, 232, 240, .94);
    }
    [data-bs-theme="dark"] .form-access-table tbody td {
      border-color: rgba(51, 65, 85, .72);
    }
    [data-bs-theme="dark"] .form-access-table tbody tr:hover,
    [data-bs-theme="dark"] .form-access-table.table > tbody > tr:hover > *,
    [data-bs-theme="dark"] .form-access-table > tbody > tr:hover > td {
      background: rgba(148, 163, 184, .1) !important;
    }
    [data-bs-theme="dark"] .form-access-table .group-chip {
      background: rgba(59, 130, 246, .18);
      color: #bfdbfe;
      border-color: rgba(96, 165, 250, .22);
    }
    [data-bs-theme="dark"] .form-access-table .access-chip {
      background: rgba(148, 163, 184, .10);
      color: #e2e8f0;
      border-color: rgba(148, 163, 184, .18);
    }
    [data-bs-theme="dark"] .form-access-table .access-chip.is-allowed {
      background: rgba(16, 185, 129, .18);
      color: #99f6e4;
      border-color: rgba(45, 212, 191, .18);
    }
    [data-bs-theme="dark"] .form-access-table .access-chip.is-blocked {
      background: rgba(239, 68, 68, .18);
      color: #fecaca;
      border-color: rgba(248, 113, 113, .20);
    }

</style>
</head>

<body
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>"
  data-layout="vertical"
  data-sidebar-size="default"
  class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <!-- Tajuk + breadcrumb -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
              <h4 class="page-title"><?= __('permohonan_list_intro') ?></h4>
              <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">
                    <a href="dashboard.php">
                      <i class="ri-home-4-line align-middle me-1"></i> <?= __('breadcrumb_home') ?? 'Home' ?>
                    </a>
                  </li>
                  <li class="breadcrumb-item active"><?= __('permohonan_list_intro') ?></li>
                </ol>
              </div>
            </div>
          </div>
        </div>

      
        <!-- Jadual Borang -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <p class="text-muted mb-3"><?= __('permohonan_list_intro') ?></p>

                <div class="table-responsive dt-standard">
                  <table class="table table-bordered align-middle form-access-table" id="permohonanTable">
                    <thead>
                       <tr>
                        <th class="col-bil">#</th>
                        <th class="col-no"><?= __('permohonan_col_no') ?></th>
                        <th class="col-jenis"><?= __('permohonan_col_jenis') ?></th>
                        <th class="col-servis"><?= __('permohonan_col_servis') ?></th>
                        <th class="col-status"><?= __('permohonan_col_status') ?></th>
                        <th class="col-tarikh"><?= __('permohonan_col_tarikh') ?></th>
                        <th class="col-actions"><?= __('permohonan_col_pdf') ?></th>
                      </tr>
                    </thead>
                  <tbody>
                    <?php if (!empty($senaraiPermohonan)): ?>
                     <?php foreach ($senaraiPermohonan as $i => $p): ?>

                      <?php
                      $id     = (int)$p['f_permohonanID'];
                      $no     = $p['f_no_permohonan'];
                      $jenis  = $p['jenis'];
                      $servis = $p['perkhidmatan'];
                      $status = $p['f_status'];
                      $tarikh = $p['f_created_at'] 
                          ? date('d/m/Y', strtotime($p['f_created_at'])) 
                          : '-';
                      ?>

                      <tr>

                      <td class="col-bil"></td>

                      <td class="col-no">
                        <span class="truncate-1line"><?= h($no) ?></span>
                      </td>

                      <td class="col-jenis">
                      <?php if ($jenis == 'EMAIL'): ?>
                      <span class="cell-inline justify-content-center">
                        <span class="group-chip"><?= __('permohonan_type_email') ?></span>
                      </span>
                      <?php else: ?>
                      <span class="cell-inline justify-content-center">
                        <span class="group-chip"><?= h($jenis) ?></span>
                      </span>
                      <?php endif; ?>
                      </td>

                      <td class="col-servis">
                        <span class="truncate-1line"><?= h($servis) ?></span>
                      </td>

                      <td class="col-status">

                      <?php if ($status == 'SUBMITTED'): ?>
                      <span class="access-chip"><?= __('permohonan_status_submitted') ?></span>

                      <?php elseif ($status == 'APPROVED'): ?>
                      <span class="access-chip is-allowed"><?= __('permohonan_status_approved') ?></span>

                      <?php elseif ($status == 'PROCESSING'): ?>
                      <span class="access-chip"><?= __('permohonan_status_processing') ?></span>

                      <?php elseif ($status == 'REJECTED'): ?>
                      <span class="access-chip is-blocked"><?= __('permohonan_status_rejected') ?></span>

                      <?php else: ?>
                      <span class="access-chip"><?= h($status) ?></span>

                      <?php endif; ?>

                      </td>

                      <td class="col-tarikh"><?= h($tarikh) ?></td>

                      <td class="col-actions">
                      <?php if ($jenis === 'EMAIL'): ?>
                      <a href="pdf_permohonan_email.php?id=<?= h((string)$id) ?>"
                      target="_blank"
                      class="btn btn-outline-danger btn-sm icon-btn"
                      title="<?= h(__('permohonan_action_pdf_email')) ?>">
                      <i class="ri-file-pdf-line"></i>
                      </a>
                      <?php else: ?>
                      <button type="button"
                      class="btn btn-outline-secondary btn-sm icon-btn"
                      title="<?= h(__('permohonan_action_pdf_unavailable')) ?>"
                      disabled>
                      <i class="ri-file-pdf-line"></i>
                      </button>
                      <?php endif; ?>
                      </td>

                      </tr>

                      <?php endforeach; ?>

                    <?php else: ?>

                      <tr>
                        <td colspan="8" class="text-center text-muted">
                          <?= __('permohonan_no_record') ?>
                        </td>
                      </tr>

                    <?php endif; ?>

                    </tbody>

                  </table>

                </div>

              </div>
            </div>
          </div>
        </div>

      </div><!-- /.container-fluid -->
    </div><!-- /.content -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
  </div><!-- /.content-page -->
</div><!-- /.wrapper -->

<?php include __DIR__ . '/../includes/script.php'; ?>
<!-- Extracted JavaScript Modules -->

 

<script>
document.addEventListener('DOMContentLoaded', function () {

  if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) {
    console.warn('DataTables not loaded');
    return;
  }

  /* =====================================================
     TRANSLATION
  ===================================================== */
  const T = {
    no_records: <?= json_encode(__('permohonan_dt_no_records')) ?>,
    search_placeholder: <?= json_encode(__('permohonan_dt_search_label')) ?>,
    dt_length_menu: <?= json_encode(__('permohonan_dt_length_menu')) ?>,
    dt_info: <?= json_encode(__('permohonan_dt_info')) ?>,
    dt_info_empty: <?= json_encode(__('permohonan_dt_info_empty')) ?>,
    dt_paginate_prev: <?= json_encode(__('permohonan_dt_paginate_prev')) ?>,
    dt_paginate_next: <?= json_encode(__('permohonan_dt_paginate_next')) ?>,
    success_title: <?= json_encode(__('permohonan_js_success_title')) ?>,
    error_title: <?= json_encode(__('permohonan_js_error_title')) ?>
  };

  /* =====================================================
     DATATABLE
  ===================================================== */
  const table = jQuery('#permohonanTable').DataTable({
    pageLength: 10,
    lengthChange: true,
    lengthMenu: [10, 25, 50, 100, 200],
    ordering: true,
    order: [[1, 'asc']],
    autoWidth: false,
    columnDefs: [
      { targets: [0, 6], orderable: false }
    ],
    language: {
      search: "",
      lengthMenu: T.dt_length_menu,
      info: T.dt_info,
      infoEmpty: T.dt_info_empty,
      zeroRecords: T.no_records,
      paginate: {
        previous: T.dt_paginate_prev,
        next: T.dt_paginate_next
      }
    },
    dom:
      '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>' +
      't' +
      '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',

    drawCallback: function () {
      const api = this.api();
      const pageInfo = api.page.info();
      let rowIndex = 0;

      api.rows({ page: 'current' }).every(function () {
        jQuery(this.node()).find('td:first')
          .text(pageInfo.start + rowIndex + 1);
        rowIndex++;
      });
    },

    initComplete: function () {
      jQuery('#permohonanTable_length select').addClass('form-select w-auto');
      jQuery('#permohonanTable_length label').addClass('mb-0');
      const topLeft = jQuery('#permohonanTable_wrapper .dt-top-left').addClass('d-flex align-items-center gap-2 flex-nowrap');
      const topRight = jQuery('#permohonanTable_wrapper .dt-top-right').addClass('align-items-center gap-2 flex-nowrap');
      const filter = jQuery('#permohonanTable_filter');
      const input = filter.find('input');

      filter.find('label').contents().filter(function () {
        return this.nodeType === 3;
      }).remove();

      input.attr('placeholder', T.search_placeholder);
    }
  });

});
</script>


</body>
</html>
