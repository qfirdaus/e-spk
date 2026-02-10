<?php
// pages/kumpulan-pengguna.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/GroupController.php';
$controller    = new GroupController();

$lang          = $controller->lang ?? 'ms';
$profile       = $controller->profile ?? [];
$senaraiGroup  = $controller->senaraiGroup ?? [];
$version       = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));

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

// CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

// Default order for new module: max(f_order) + 1
$nextModuleOrder = 1;
try {
  $pdoOrder = Database::getInstance('mysql')->getConnection();
  $nextStmt = $pdoOrder->query("SELECT COALESCE(MAX(f_order), 0) + 1 AS next_order FROM tbl_m_modul");
  $nextVal = (int)($nextStmt->fetchColumn() ?: 1);
  $nextModuleOrder = ($nextVal > 0) ? $nextVal : 1;
} catch (Throwable $e) {
  $nextModuleOrder = 1;
}

// Add Module (POST, non-AJAX)
$moduleFormData = [
  'modulNameMs' => '',
  'modulNameEn' => '',
  'icon' => '',
  'order' => (string)$nextModuleOrder,
];
$moduleFormOpen = false;
$moduleSwal = null;

if (!empty($_SESSION['module_add_flash']) && is_array($_SESSION['module_add_flash'])) {
  $flash = $_SESSION['module_add_flash'];
  unset($_SESSION['module_add_flash']);
  $moduleSwal = [
    'icon' => (string)($flash['icon'] ?? 'success'),
    'title' => (string)($flash['title'] ?? ''),
    'text' => (string)($flash['text'] ?? ''),
  ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['action'] ?? '') === 'add_module') {
  $moduleFormOpen = true;
  $moduleFormData['modulNameMs'] = trim((string)($_POST['modulNameMs'] ?? ''));
  $moduleFormData['modulNameEn'] = trim((string)($_POST['modulNameEn'] ?? ''));
  $moduleFormData['icon'] = trim((string)($_POST['icon'] ?? ''));
  $moduleFormData['order'] = trim((string)($_POST['order'] ?? ''));

  $postedCsrf = (string)($_POST['csrf_token'] ?? '');
  if ($postedCsrf === '' || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $postedCsrf)) {
    $moduleSwal = [
      'icon' => 'error',
      'title' => (string)__('modul_ralat_title'),
      'text' => (string)__('userGroup_error_unknown'),
    ];
  } elseif (!$canManageGroups) {
    $moduleSwal = [
      'icon' => 'error',
      'title' => (string)__('modul_ralat_title'),
      'text' => (string)__('userList_err_no_permission'),
    ];
  } elseif ($moduleFormData['modulNameMs'] === '') {
    $moduleSwal = [
      'icon' => 'warning',
      'title' => (string)__('modul_ralat_title'),
      'text' => (string)__('modul_ralat_wajib'),
    ];
  } else {
    try {
      $pdo = Database::getInstance('mysql')->getConnection();
      $nameMs = $moduleFormData['modulNameMs'];
      $nameEn = $moduleFormData['modulNameEn'];

      $dupSql = "
        SELECT 1
        FROM tbl_m_modul
        WHERE LOWER(TRIM(f_modulName_ms)) = LOWER(TRIM(:name_ms_1))
           OR LOWER(TRIM(f_modulName_en)) = LOWER(TRIM(:name_ms_2))
      ";
      $dupParams = [
        ':name_ms_1' => $nameMs,
        ':name_ms_2' => $nameMs,
      ];
      if ($nameEn !== '') {
        $dupSql .= "
           OR LOWER(TRIM(f_modulName_ms)) = LOWER(TRIM(:name_en_1))
           OR LOWER(TRIM(f_modulName_en)) = LOWER(TRIM(:name_en_2))
        ";
        $dupParams[':name_en_1'] = $nameEn;
        $dupParams[':name_en_2'] = $nameEn;
      }
      $dupSql .= " LIMIT 1";

      $dupStmt = $pdo->prepare($dupSql);
      $dupStmt->execute($dupParams);
      $isDuplicate = (bool)$dupStmt->fetchColumn();

      if ($isDuplicate) {
        $moduleSwal = [
          'icon' => 'error',
          'title' => (string)__('modul_ralat_title'),
          'text' => (string)__('modul_ralat_duplikat'),
        ];
      } else {
        $orderRaw = $moduleFormData['order'];
        $orderVal = ($orderRaw !== '' && is_numeric($orderRaw)) ? (int)$orderRaw : $nextModuleOrder;

        $ins = $pdo->prepare("
          INSERT INTO tbl_m_modul (f_modulName_ms, f_modulName_en, f_icon, f_order)
          VALUES (:name_ms, :name_en, :icon, :f_order)
        ");
        $ins->execute([
          ':name_ms' => $nameMs,
          ':name_en' => ($nameEn !== '' ? $nameEn : null),
          ':icon' => ($moduleFormData['icon'] !== '' ? $moduleFormData['icon'] : null),
          ':f_order' => $orderVal,
        ]);

        $_SESSION['module_add_flash'] = [
          'icon' => 'success',
          'title' => (string)__('modul_berjaya_title'),
          'text' => (string)__('modul_berjaya_msg'),
        ];
        header('Location: ' . base_url('pages/kumpulan-pengguna.php'));
        exit;
      }
    } catch (Throwable $e) {
      error_log('[kumpulan-pengguna:add-module] ' . $e->getMessage());
      $moduleSwal = [
        'icon' => 'error',
        'title' => (string)__('modul_ralat_title'),
        'text' => (string)__('userGroup_error_unknown'),
      ];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>

  <meta name="csrf-token" content="<?= h($csrf) ?>">
  <!-- ✅ Standard DataTables CSS (shared) -->
  <link href="<?= base_url('assets/css/datatables-standard.css') ?>?v=<?= h($version ?? date('ymdHis')) ?>" rel="stylesheet">
  <style>
    .icon-btn { line-height:1; }

    /* Kolum jadual kumpulan - use percentages to match requested layout */
    table.table th.th-nowrap { white-space: nowrap; }
    table.table th.th-mod,  table.table td.td-mod  { width: 10%; }
    table.table th.th-menu, table.table td.td-menu { width: 10%; }
    table.table th.th-grp,  table.table td.td-grp  { width: 10%; }

    /* Reorder view */
    .modul-badge { font-size:.75rem; }
    .menu-path { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; font-size:.775rem; opacity:.8; }
    .menu-row { display:grid; grid-template-columns: 1fr auto; gap:.75rem; padding:.6rem 0; border-bottom:1px dashed var(--bs-border-color); }
    .menu-row:last-child { border-bottom:0; }
    .reorder-group .btn { padding:.25rem .55rem; }
    .menu-row.saving { opacity:.6; pointer-events:none; }

    /* DataTable: top & bottom bars */
    .dt-topbar{
      display:flex; align-items:center; justify-content:space-between;
      gap:.75rem; border-bottom:1px solid var(--bs-border-color); padding-bottom:.5rem; margin-bottom:.75rem;
    }
    .dt-topbar .left, .dt-topbar .right{ display:flex; align-items:center; gap:.5rem; }
    .dt-topbar .right .form-control{ width:260px; max-width:100%; }
    .dt-bottom-row { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
    @media (max-width:575.98px){ .dt-topbar{ flex-wrap:wrap; } .dt-topbar .right{ margin-left:auto; } }

    /* Fix DataTables layout untuk groupTable - prevent horizontal scroll */
    #groupTable_wrapper {
      overflow-x: hidden;
      width: 100%;
      max-width: 100%;
    }
    
    /* ✅ Table styling sama seperti senarai-pengguna.php */
    #groupTable {
      border-radius: 0.75rem;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
      table-layout: fixed;
    }
    #groupTable thead {
      background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
      color: #ffffff;
    }
    #groupTable thead th {
      font-weight: 700;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 1rem 0.75rem;
      border: none;
      color: #ffffff;
    }
    #groupTable tbody tr {
      transition: all 0.2s ease;
    }
    #groupTable tbody tr:hover {
      background: #f8fafc !important;
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    #groupTable tbody td {
      padding: 0.875rem 0.75rem;
      border-color: #f1f5f9;
      vertical-align: middle;
    }
    /* Dark theme support */
    html[data-bs-theme="dark"] #groupTable thead {
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    }
    html[data-bs-theme="dark"] #groupTable tbody tr:hover {
      background: #334155 !important;
    }
    /* Remove stripline effect */
    #groupTable tbody tr,
    #groupTable tbody tr:nth-of-type(odd),
    #groupTable tbody tr:nth-of-type(even) {
      background-color: transparent !important;
    }
    #groupTable_wrapper .dataTables_length {
      display: flex;
      align-items: center;
      white-space: nowrap;
      overflow: hidden;
      flex-wrap: nowrap;
      max-width: 100%;
    }
    #groupTable_wrapper .dataTables_length label {
      display: flex;
      align-items: center;
      white-space: nowrap;
      margin-bottom: 0;
      flex-wrap: nowrap;
      gap: 0.5rem;
      max-width: 100%;
      overflow: hidden;
    }
    #groupTable_wrapper .dataTables_length select {
      margin: 0;
      width: auto;
      min-width: 70px;
      max-width: 100px;
      display: inline-block;
    }
    #groupTable_wrapper .dataTables_filter {
      text-align: right;
      margin-left: auto;
      max-width: 100%;
      overflow: hidden;
    }
    #groupTable_wrapper .dataTables_filter label {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      margin-bottom: 0;
      width: 100%;
      white-space: nowrap;
      max-width: 100%;
      overflow: hidden;
    }
    #groupTable_wrapper .dataTables_filter input {
      margin-left: 0.5rem;
      width: auto;
      min-width: 150px;
      max-width: 250px;
    }
    #groupTable_wrapper .dataTables_info {
      display: flex;
      align-items: center;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }
    #groupTable_wrapper .dataTables_paginate {
      text-align: right;
      margin-left: auto;
      max-width: 100%;
      overflow: hidden;
    }
    #groupTable_wrapper .dataTables_paginate .pagination {
      justify-content: flex-end;
      margin-bottom: 0;
      flex-wrap: nowrap;
    }
    #groupTable_wrapper .row {
      margin-left: -0.75rem;
      margin-right: -0.75rem;
      max-width: 100%;
      overflow-x: hidden;
    }
    #groupTable_wrapper .row > [class*="col-"] {
      padding-left: 0.75rem;
      padding-right: 0.75rem;
      max-width: 100%;
      overflow: hidden;
    }
    /* Fix bottom row layout */
    #groupTable_wrapper .row.mt-3 {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 100%;
      overflow-x: hidden;
    }
    #groupTable_wrapper .row.mt-3 > [class*="col-md-5"] {
      display: flex;
      align-items: center;
      max-width: 100%;
      overflow: hidden;
    }
    #groupTable_wrapper .row.mt-3 > [class*="col-md-7"] {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      max-width: 100%;
      overflow: hidden;
    }
    /* Ensure table container doesn't overflow */
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 991.98px) {
      .table-responsive {
        overflow-x: auto;
      }
    }
    @media (max-width: 767.98px) {
      #groupTable_wrapper .dataTables_length,
      #groupTable_wrapper .dataTables_filter {
        margin-bottom: 0.75rem;
      }
      #groupTable_wrapper .dataTables_filter {
        text-align: left;
        margin-left: 0;
      }
      #groupTable_wrapper .dataTables_filter label {
        justify-content: flex-start;
      }
      #groupTable_wrapper .dataTables_info,
      #groupTable_wrapper .dataTables_paginate {
        text-align: center;
        margin-top: 0.5rem;
      }
      #groupTable_wrapper .row.mt-3 {
        flex-direction: column;
        align-items: stretch;
      }
      #groupTable_wrapper .row.mt-3 > [class*="col-md-5"],
      #groupTable_wrapper .row.mt-3 > [class*="col-md-7"] {
        justify-content: center;
        margin-top: 0.5rem;
      }
    }

    /* Buang class lama yang tak digunakan */
    #menuDT_wrapper .dt-top-right, #grpCnt .dt-top-right { display:none!important; }

    /* Akses Kumpulan – kolum “Menu”: kecil & 1 baris */
    #groupPermsDT th.col-menu, #groupPermsDT td.col-menu {
      width: 140px; white-space: nowrap;
    }

    /* Akses Menu – status & actions kekal sebaris */
    #menuDT th.col-status, #menuDT td.col-status { width: 200px; white-space: nowrap; }
    #menuDT th.col-actions, #menuDT td.col-actions { width: 170px; white-space: nowrap; }

    /* Multi-modal stacking */
    .modal-backdrop.show + .modal-backdrop.show { z-index: 1065; }
    .modal.show { z-index: 1055; }
    .modal.show + .modal.show { z-index: 1070; }

    /* Professional Modal Styling */
    .modal-content {
      border: none;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15), 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .modal-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1.5rem 1.75rem;
      border-bottom: none;
      position: relative;
    }

    .modal-header.bg-body-tertiary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .modal-header .modal-title {
      color: white;
      font-weight: 600;
      font-size: 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .modal-header .modal-title i {
      font-size: 1.5rem;
      opacity: 0.95;
    }

    .modal-header .btn-close {
      filter: brightness(0) invert(1);
      opacity: 0.9;
      transition: opacity 0.2s;
    }

    .modal-header .btn-close:hover {
      opacity: 1;
    }

    .modal-subtitle {
      padding: 0.75rem 1.75rem;
      background: rgba(102, 126, 234, 0.08);
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      font-size: 0.875rem;
      color: #6c757d;
      font-weight: 500;
    }

    .modal-body {
      padding: 1.75rem;
      background: #fff;
    }

    .modal-footer {
      padding: 1.25rem 1.75rem;
      background: #f8f9fa;
      border-top: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 0 0 12px 12px;
    }

    .modal-footer .btn {
      padding: 0.5rem 1.5rem;
      font-weight: 500;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .modal-footer .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .modal-footer .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }

    /* Loading States */
    .modal-loading {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem 2rem;
      text-align: center;
    }

    .modal-loading .spinner-border {
      width: 3rem;
      height: 3rem;
      border-width: 3px;
      color: #667eea;
      margin-bottom: 1rem;
    }

    .modal-loading span {
      color: #6c757d;
      font-size: 0.95rem;
      font-weight: 500;
    }

    /* Error States */
    .modal-error {
      padding: 1rem 1.25rem;
      border-radius: 8px;
      background: #fee;
      border-left: 4px solid #dc3545;
      margin-bottom: 1rem;
    }

    /* Form Styling in Modals */
    .modal-body .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .modal-body .form-control,
    .modal-body .form-select {
      border-radius: 8px;
      border: 1.5px solid #e0e0e0;
      padding: 0.625rem 0.875rem;
      transition: all 0.2s ease;
    }

    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    /* Make module/menu multi-selects in the create modal taller for easier selection */
    #gc_moduls, #gc_menus {
      min-height: 200px;
      height: 200px;
      overflow: auto;
    }
    @media (max-width: 767.98px) {
      #gc_moduls, #gc_menus { min-height: 140px; height: 140px; }
    }

    /* Search Input in Modals */
    .modal-body input[type="search"] {
      border-radius: 10px;
      padding: 0.75rem 1rem;
      border: 2px solid #e9ecef;
      transition: all 0.2s ease;
    }

    .modal-body input[type="search"]:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    /* Themed Modal Accent */
    .modal-themed .modal-content {
      position: relative;
    }

    .modal-themed .modal-content::before {
      content: "";
      position: absolute;
      inset: 0 0 auto 0;
      height: 4px;
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }

    /* Edit Modal Specific */
    #menuEditModal .modal-header {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    #menuEditModal .modal-dialog {
      max-width: 900px;
    }

    #menuEditModal .modal-footer .btn-primary {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
    }

    #menuEditModal .modal-footer .btn-primary:hover {
      box-shadow: 0 6px 16px rgba(245, 87, 108, 0.4);
    }

    /* Accordion in Modals */
    .modal-body .accordion {
      border-radius: 8px;
      overflow: hidden;
    }

    .modal-body .accordion-item {
      border: 1px solid #e9ecef;
      margin-bottom: 0.5rem;
      border-radius: 8px;
    }

    .modal-body .accordion-button {
      background: #f8f9fa;
      font-weight: 600;
      padding: 1rem 1.25rem;
      border-radius: 8px;
    }

    .modal-body .accordion-button:not(.collapsed) {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
      color: #667eea;
    }

    /* Table in Modals */
    .modal-body .table {
      border-radius: 8px;
      overflow: hidden;
    }

    .modal-body .table thead {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }

    /* Badge Styling */
    .modal-body .badge {
      padding: 0.4rem 0.75rem;
      border-radius: 6px;
      font-weight: 500;
    }

    /* Input Group Styling */
    .modal-body .input-group-text {
      border-radius: 8px 0 0 8px;
      background: #f8f9fa;
      border-color: #e0e0e0;
    }

    .modal-body .input-group .form-control {
      border-left: none;
      border-radius: 0 8px 8px 0;
    }

    /* Button Group in Forms */
    .modal-body .btn-group .btn {
      border-radius: 6px;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .modal-body .btn-group .btn:hover {
      transform: translateY(-1px);
    }

    /* List Group in Modals */
    .modal-body .list-group-item {
      border-radius: 8px;
      margin-bottom: 0.5rem;
      border: 1.5px solid #e9ecef;
      transition: all 0.2s ease;
    }

    .modal-body .list-group-item:hover {
      border-color: #667eea;
      background: rgba(102, 126, 234, 0.05);
      transform: translateX(4px);
    }

    /* Smooth Transitions */
    .modal.fade .modal-dialog {
      transition: transform 0.3s ease-out, opacity 0.3s ease-out;
    }

    .modal.show .modal-dialog {
      transform: none;
    }

    /* Content Animation */
    .modal-body > div:not(.d-none) {
      animation: fadeInUp 0.3s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    #groupPermsDT { table-layout: fixed; }
    #groupPermsDT th.col-check, #groupPermsDT td.col-check { width: 60px; }
    #groupPermsDT th.col-menu,  #groupPermsDT td.col-menu  { width: 140px; white-space: nowrap; }

    /* Loading indicators */
    .btn-loading {
      position: relative;
      pointer-events: none;
      opacity: 0.7;
    }
    .btn-loading::after {
      content: "";
      position: absolute;
      width: 16px;
      height: 16px;
      top: 50%;
      left: 50%;
      margin-left: -8px;
      margin-top: -8px;
      border: 2px solid transparent;
      border-top-color: currentColor;
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .btn-loading .btn-text {
      opacity: 0;
    }

    /* Undo notification */
    .undo-notification {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Controls & buttons: align with senarai-pengguna.php styles */
    .dt-bottom-row { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
    .dataTables_length, #groupTable_wrapper .dataTables_length {
      white-space: nowrap !important;
      line-height: 1.4;
      display: inline-block;
    }
    .dataTables_length label, #groupTable_wrapper .dataTables_length label {
      white-space: nowrap !important;
      display: inline-flex !important;
      align-items: center;
      gap: 0.4rem;
      margin-bottom: 0;
      flex-wrap: nowrap !important;
      font-size: 0.875rem !important;
    }
    .dataTables_length select, #groupTable_wrapper .dataTables_length select {
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
    .dataTables_length select:hover, #groupTable_wrapper .dataTables_length select:hover { border-color: #ced4da !important; }
    .dataTables_length select:focus, #groupTable_wrapper .dataTables_length select:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25) !important;
      outline: none !important;
    }
    .dt-top-left { white-space: nowrap !important; flex-wrap: nowrap !important; }
    /* Buttons in top-right: sizing, borders and gap consistent with senarai-pengguna */
    .dt-top-right button, #groupTable_wrapper .dt-top-right button {
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
    .dt-top-right button:hover, #groupTable_wrapper .dt-top-right button:hover { border-color: #ced4da !important; }
    .dt-top-right button:focus, #groupTable_wrapper .dt-top-right button:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25) !important;
    }
    .dt-top-right { gap: 0.5rem !important; }
    .dt-top-right button + button { margin-left: 0 !important; }


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
              <h4 class="page-title"><?= __('userGroup_page_title') ?></h4>
              <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">
                    <a href="dashboard.php">
                      <i class="ri-home-4-line align-middle me-1"></i> <?= __('breadcrumb_home') ?? 'Home' ?>
                    </a>
                  </li>
                  <li class="breadcrumb-item active"><?= __('userGroup_page_title') ?></li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <!-- Jadual Kumpulan -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <p class="text-muted mb-3"><?= __('userGroup_intro') ?></p>

                <div class="table-responsive">
                  <table class="table table-bordered align-middle" id="groupTable">
                    <thead>
                        <tr>
                          <th style="width:5%" class="th-nowrap">#</th>
                          <th style="width:15%" class="th-nowrap"><?= __('userGroup_col_code') ?></th>
                          <th style="width:35%" class="th-nowrap"><?= __('userGroup_col_name') ?></th>
                          <th style="width:15%" class="text-center th-nowrap th-grp"><?= __('userGroup_col_group_access') ?></th>
                          <th style="width:15%" class="text-center th-nowrap th-mod"><?= __('userGroup_col_module_access') ?></th>
                          <th style="width:16%" class="text-center th-nowrap th-menu"><?= __('userGroup_col_menu_access') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($senaraiGroup)): ?>
                        <?php foreach ($senaraiGroup as $i => $g): ?>
                          <?php
                            $groupID = (int)($g['f_groupID'] ?? 0);
                            $kod     = (string)($g['f_groupKod'] ?? '');
                            $nama    = (string)($g['f_groupName'] ?? '');
                            $modAks  = (string)($g['f_modulAccess'] ?? '');
                            $menuAks = (string)($g['f_menuAccess'] ?? '');
                            $hasAccess = (trim($modAks) !== '' || trim($menuAks) !== '');
                          ?>
                          <tr data-group-id="<?= $groupID ?>">
                            <td><?= $i + 1 ?></td>
                            <td><?= h($kod) ?></td>
                            <td><?= h($nama) ?></td>

                            <!-- Akses Kumpulan -->
                            <td class="text-center td-grp">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary icon-btn view-group-perms<?= $permDisabledClass ?>"
                                <?= $permDisabledAttr ?>
                                data-group-id="<?= $groupID ?>"
                                data-group-kod="<?= h($kod) ?>"
                                data-group-nama="<?= h($nama) ?>"
                                title="<?= h(__('userGroup_col_group_access')) ?>">
                                <i class="ri-user-settings-line"></i>
                              </button>
                            </td>

                            <!-- Akses Modul -->
                            <td class="text-center td-mod">
                              <?php if ($hasAccess): ?>
                                <button
                                  type="button"
                                  class="btn btn-sm btn-outline-primary icon-btn view-access<?= $permDisabledClass ?>"
                                  <?= $permDisabledAttr ?>
                                  data-group-id="<?= $groupID ?>"
                                  data-group-kod="<?= h($kod) ?>"
                                  data-group-nama="<?= h($nama) ?>"
                                  title="<?= h(__('userGroup_col_module_access')) ?>">
                                  <i class="ri-links-line"></i>
                                </button>
                              <?php else: ?>
                                <span class="text-muted"><i class="ri-link-unlink-m"></i></span>
                              <?php endif; ?>
                            </td>

                            <!-- Akses Menu -->
                            <td class="text-center td-menu">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-success icon-btn view-menu<?= $permDisabledClass ?>"
                                <?= $permDisabledAttr ?>
                                data-group-id="<?= $groupID ?>"
                                data-group-kod="<?= h($kod) ?>"
                                data-group-nama="<?= h($nama) ?>"
                                title="<?= h(__('userGroup_col_menu_access')) ?>">
                                <i class="ri-menu-2-line"></i>
                              </button>
                            </td>

                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center text-muted"><?= __('userGroup_no_records') ?></td>
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
<script src="<?= base_url('assets/js/group-utils.js') ?>?v=<?= $version ?? date('ymdHis') ?>"></script>
<script src="<?= base_url('assets/js/group-state.js') ?>?v=<?= $version ?? date('ymdHis') ?>"></script>
<script src="<?= base_url('assets/js/group-menu-refresh.js') ?>?v=<?= $version ?? date('ymdHis') ?>"></script>
<script src="<?= base_url('assets/js/group-module-access.js') ?>?v=<?= $version ?? date('ymdHis') ?>"></script>
<script src="<?= base_url('assets/js/group-menu-access.js') ?>?v=<?= $version ?? date('ymdHis') ?>"></script>
<script src="<?= base_url('assets/js/group-permissions.js') ?>?v=<?= $version ?? date('ymdHis') ?>"></script>
<!-- MODAL: Akses Modul (REORDER) -->
<div class="modal fade modal-themed" id="aksesModal" tabindex="-1" aria-hidden="true" aria-labelledby="aksesModalTitle">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aksesModalTitle">
          <i class="ri-shield-keyhole-line"></i>
          <span><?= h(__('userGroup_col_module_access')) ?></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userGroup_btn_close')) ?>"></button>
      </div>
      <div class="modal-subtitle" id="aksesModalSub"></div>
      <div class="modal-body">
        <div class="mb-4">
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
              <i class="ri-search-line text-muted"></i>
            </span>
            <input type="search" id="aksesSearch" class="form-control border-start-0" placeholder="<?= h(__('userGroup_search_label')) ?>…">
        </div>
        </div>
        <div id="aksesLoading" class="modal-loading">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
          <span><?= h(__('userGroup_loading')) ?>…</span>
        </div>
        <div id="aksesError" class="modal-error alert alert-danger d-none"></div>
        <div id="aksesContent" class="d-none"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= h(__('userGroup_btn_close')) ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Akses Menu (DataTable) -->
<div class="modal fade modal-themed" id="aksesMenuModal" tabindex="-1" aria-hidden="true" aria-labelledby="aksesMenuTitle">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aksesMenuTitle">
          <i class="ri-list-settings-line"></i>
          <span><?= h(__('userGroup_col_menu_access')) ?></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userGroup_btn_close')) ?>"></button>
      </div>
      <div class="modal-subtitle" id="aksesMenuSub"></div>
      <div class="modal-body">
        <div id="menuLoading" class="modal-loading">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
          <span><?= h(__('userGroup_loading')) ?>…</span>
        </div>
        <div id="menuError" class="modal-error alert alert-danger d-none"></div>
        <div id="menuContent" class="d-none"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= h(__('userGroup_btn_close')) ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Edit Menu -->
<div class="modal fade" id="menuEditModal" tabindex="-1" aria-hidden="true" aria-labelledby="menuEditTitle" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="menuEditTitle">
          <i class="ri-pencil-line"></i>
          <span id="menuEditTitleText" data-title-create="<?= h(__('userGroup_modal_add_menu_title')) ?>" data-title-edit="<?= h(__('userGroup_modal_edit_menu_title')) ?>"><?= h(__('userGroup_modal_edit_menu_title')) ?></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userGroup_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <form id="menuEditForm" autocomplete="off">
          <input type="hidden" name="menuID" id="em_menuID">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label"><?= h(__('userGroup_field_modul')) ?></label>
              <select class="form-select" name="modulID" id="em_modulID"></select>
            </div>
            <div class="col-md-6">
              <label class="form-label"><?= h(__('userGroup_field_path')) ?></label>
              <input type="text" class="form-control" name="path" id="em_path" placeholder="<?= h(__('userGroup_field_path_placeholder')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label"><?= h(__('userGroup_field_name_ms')) ?></label>
              <input type="text" class="form-control" name="name_ms" id="em_name_ms">
            </div>
            <div class="col-md-6">
              <label class="form-label"><?= h(__('userGroup_field_name_en')) ?></label>
              <input type="text" class="form-control" name="name_en" id="em_name_en">
            </div>
            <!-- Removed zh/ta language fields: menu names now only use ms + en -->
            <div class="col-md-6">
              <label class="form-label d-block"><?= h(__('userGroup_field_status')) ?></label>
              <div class="btn-group mt-1" role="group" aria-label="<?= h(__('userGroup_field_status')) ?>">
                <input type="radio" class="btn-check" name="flag" id="em_flag_on" value="1">
                <label class="btn btn-outline-success" for="em_flag_on"><?= h(__('userGroup_status_on')) ?></label>
                <input type="radio" class="btn-check" name="flag" id="em_flag_off" value="0">
                <label class="btn btn-outline-secondary" for="em_flag_off"><?= h(__('userGroup_status_off')) ?></label>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label"><?= h(__('userGroup_field_position_label')) ?></label>
              <select class="form-select" id="em_position">
                <option value="bottom" selected><?= h(__('userGroup_position_bottom')) ?></option>
                <option value="top"><?= h(__('userGroup_position_top')) ?></option>
              </select>
            </div>
          </div>
        </form>
        <div id="menuEditError" class="modal-error alert alert-danger d-none mt-3"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= h(__('userGroup_btn_close')) ?>
        </button>
        <button class="btn btn-primary" id="menuEditSaveBtn" <?= $permDisabledAttr ?>>
          <i class="ri-save-3-line me-1"></i> <span id="menuEditSaveBtnText"><?= h(__('userGroup_btn_save')) ?></span>
        </button>
      </div>
    </div>
  </div>
</div>
<!-- MODAL: Akses Kumpulan -->
<div class="modal fade modal-themed" id="aksesGroupModal" tabindex="-1" aria-hidden="true" aria-labelledby="aksesGroupTitle">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aksesGroupTitle">
          <i class="ri-user-settings-line"></i>
          <span><?= h(__('userGroup_modal_group_access_title')) ?></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userGroup_btn_close')) ?>"></button>
      </div>
      <div class="modal-subtitle" id="aksesGroupSub"></div>
      <div class="modal-body">
        <div id="grpLoading" class="modal-loading">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
          <span><?= h(__('userGroup_loading')) ?>…</span>
        </div>
        <div id="grpError" class="modal-error alert alert-danger d-none"></div>
        <div id="grpCnt" class="d-none"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= h(__('userGroup_btn_close')) ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Ringkasan (SEPARATE) -->
<div class="modal fade modal-themed" id="ringkasanModal" tabindex="-1" aria-hidden="true" aria-labelledby="ringkasanTitle">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ringkasanTitle">
          <i class="ri-file-list-3-line"></i>
          <span><?= h(__('userGroup_modal_summary_title')) ?></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userGroup_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <div id="ringkasanLoading" class="modal-loading">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
          <span><?= h(__('userGroup_loading')) ?>…</span>
        </div>
        <div id="ringkasanError" class="modal-error alert alert-danger d-none"></div>
        <div id="ringkasanContent" class="d-none"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= h(__('userGroup_btn_close')) ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Pemilih Menu (SEPARATE) -->
<div class="modal fade modal-themed" id="menuPickModal" tabindex="-1" aria-hidden="true" aria-labelledby="menuPickTitle">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="menuPickTitle">
          <i class="ri-list-check-2"></i>
          <span><?= h(__('userGroup_modal_pick_menu_title')) ?></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userGroup_btn_close')) ?>"></button>
      </div>
      <div class="modal-subtitle" id="menuPickSub"></div>
      <div class="modal-body">
        <div id="menuPickLoading" class="modal-loading">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
          <span><?= h(__('userGroup_loading')) ?>…</span>
        </div>
        <div id="menuPickError" class="modal-error alert alert-danger d-none"></div>
        <div id="menuPickContent" class="d-none"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= h(__('userGroup_btn_close')) ?>
        </button>
      </div>
    </div>
  </div>
</div>

      <!-- MODAL: Tambah Kumpulan -->
      <div class="modal fade modal-themed" id="groupCreateModal" tabindex="-1" aria-hidden="true" aria-labelledby="groupCreateTitle">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="groupCreateTitle"><i class="ri-add-line"></i> <span>Tambah Kumpulan</span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userGroup_btn_close')) ?>"></button>
            </div>
            <div class="modal-body">
              <form id="groupCreateForm" autocomplete="off">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Kod Kumpulan</label>
                    <input type="text" class="form-control" id="gc_groupKod" name="groupKod" placeholder="e.g. ADM-XX" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Nama Kumpulan</label>
                    <input type="text" class="form-control" id="gc_groupName" name="groupName" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Keutamaan</label>
                    <input type="number" class="form-control" id="gc_priority" name="priority" value="0">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Mod</label>
                    <select class="form-select" id="gc_mod" name="mod">
                      <option value="0">0</option>
                      <option value="1">1</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Warna</label>
                    <input type="text" class="form-control" id="gc_color" name="color" placeholder="#50a4c1">
                  </div>
                </div>

                <div class="row g-3 mt-2">
                  <div class="col-md-6">
                    <label class="form-label">Pilih Modul</label>
                    <select class="form-select" id="gc_moduls" name="modulAccess" multiple size="6">
                      <!-- options populated dynamically -->
                    </select>
                    <div class="form-text">Pilih satu atau lebih modul untuk kumpulan ini.</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Pilih Menu (bergantung pada Modul)</label>
                    <select class="form-select" id="gc_menus" name="menuAccess" multiple size="6">
                      <!-- options populated dynamically based on selected modul -->
                    </select>
                    <div class="form-text">Menu akan dipaparkan mengikut modul yang dipilih.</div>
                  </div>
                </div>
              </form>
              <div id="groupCreateError" class="modal-error alert alert-danger d-none mt-3"></div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line me-1"></i> <?= __('btn_close') ?></button>
              <button class="btn btn-primary" id="groupCreateSaveBtn" <?= $permDisabledAttr ?>><i class="ri-save-3-line me-1"></i> <?= __('btn_save') ?></button>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL: Tambah Modul -->
      <div class="modal fade modal-themed" id="moduleCreateModal" tabindex="-1" aria-hidden="true" aria-labelledby="moduleCreateTitle">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="moduleCreateTitle"><i class="ri-stack-line"></i> <span><?= h(__('modul_tambah_title')) ?></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('modul_batal')) ?>"></button>
            </div>
            <div class="modal-body">
              <form id="moduleCreateForm" method="post" autocomplete="off">
                <input type="hidden" name="action" value="add_module">
                <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label"><?= h(__('modul_nama_ms')) ?></label>
                    <input type="text" class="form-control" id="mc_modulNameMs" name="modulNameMs" value="<?= h($moduleFormData['modulNameMs']) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label"><?= h(__('modul_nama_en')) ?></label>
                    <input type="text" class="form-control" id="mc_modulNameEn" name="modulNameEn" value="<?= h($moduleFormData['modulNameEn']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label"><?= h(__('modul_icon')) ?></label>
                    <input type="text" class="form-control" id="mc_icon" name="icon" value="<?= h($moduleFormData['icon']) ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label"><?= h(__('modul_susunan')) ?></label>
                    <input type="number" class="form-control" id="mc_order" name="order" value="<?= h($moduleFormData['order']) ?>" data-default-order="<?= h((string)$nextModuleOrder) ?>">
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal"><i class="ri-close-line me-1"></i> <?= h(__('modul_batal')) ?></button>
              <button class="btn btn-primary" id="moduleCreateSaveBtn" <?= $permDisabledAttr ?>><i class="ri-save-3-line me-1"></i> <?= h(__('modul_simpan')) ?></button>
            </div>
          </div>
        </div>
      </div>

<script>
// ✅ Global utility function untuk check DataTables availability
window.hasDT = function() {
  return !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable);
};

(function(){
  const canManageGroups = <?= $canManageGroups ? 'true' : 'false' ?>;
  // =========================================================
  // 🔧 KONFIG: Lock semua AJAX ke path yang betul sahaja
  // Tukar ikut deploy path (root → '/ajax/', subfolder → '/e-prestasi/ajax/')
  // =========================================================
  //const AJAX_BASE = '/ajax/'; // <-- UBAH jika perlu
  // Base path projek yang betul di semua environment (dev subfolder / production root)
  const __BASE_PATH =
    document.querySelector('meta[name="base-path"]')?.getAttribute('content') ||
    // fallback kalau meta tak wujud: buang /pages atau /ajax dari pathname
    (location.pathname.replace(/\/(pages|ajax)(\/.*)?$/, '') || '');

  const AJAX_BASE = (__BASE_PATH || '') + '/ajax/';


  const CSRF  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const hasDT = window.hasDT; // Use global function
  const esc = (s)=> (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');

  // Utility untuk bina URL endpoint di bawah AJAX_BASE
  function apiUrl(file, params){
    const u = new URL(AJAX_BASE + file, window.location.origin);
    if (params && typeof params === 'object'){
      Object.entries(params).forEach(([k,v])=>u.searchParams.set(k, String(v)));
    }
    u.searchParams.set('_', Date.now()); // cache-bust
    return u.toString();
  }
  // ↓↓↓ tambah baris ni supaya boleh guna di console
  window.apiUrl = apiUrl;

  // Helper: fetch → cuba parse JSON, kalau HTML/teks lain bagi error mesra
  async function fetchJSONSafe(url, opts){
    const r = await fetch(url, Object.assign({ headers:{'Accept':'application/json'} }, opts||{}));
    const txt = await r.text();
    try { return JSON.parse(txt); }
    catch(e){
      const snippet = txt.slice(0, 240).replace(/\s+/g,' ').trim();
      throw new Error('Server did not return JSON. Preview: ' + snippet);
    }
  }

  // ===================== I18N ringkas ======================
    const T = {
    label_module: <?= json_encode(__('userGroup_label_module')) ?>,
    label_menu: <?= json_encode(__('userGroup_label_menu')) ?>,
    modul_fallback: <?= json_encode(__('userGroup_label_modul_fallback')) ?>,
    no_records: <?= json_encode(__('userGroup_no_records')) ?>,
    col_menu: <?= json_encode(__('userGroup_col_menu')) ?>,
    col_reorder: <?= json_encode(__('userGroup_col_reorder')) ?>,
    move_up: <?= json_encode(__('userGroup_move_up')) ?>,
    move_down: <?= json_encode(__('userGroup_move_down')) ?>,
    btn_add_menu: <?= json_encode(__('userGroup_btn_add_menu')) ?>,
    btn_add_module: <?= json_encode(__('modul_tambah')) ?>,
    col_status: <?= json_encode(__('userGroup_col_status')) ?>,
    col_actions: <?= json_encode(__('userGroup_col_actions')) ?>,
    status_on: <?= json_encode(__('userGroup_status_on')) ?>,
    status_off: <?= json_encode(__('userGroup_status_off')) ?>,
    loading: <?= json_encode(__('userGroup_loading')) ?>,
    error_network: <?= json_encode(__('userGroup_error_network')) ?>,
    error_unknown: <?= json_encode(__('userGroup_error_unknown')) ?>,
    error_reorder: <?= json_encode(__('userGroup_error_reorder')) ?>,
    error_load_access: <?= json_encode(__('userGroup_error_load_access')) ?>,
    error_load_menu: <?= json_encode(__('userGroup_error_load_menu')) ?>,
    error_get_menu: <?= json_encode(__('userGroup_error_get_menu')) ?>,
    err_path_required: <?= json_encode(__('userGroup_err_path_required')) ?>,
    err_modul_required: <?= json_encode(__('userGroup_err_modul_required')) ?>,
    err_add_menu: <?= json_encode(__('userGroup_err_add_menu')) ?>,
    err_save_menu: <?= json_encode(__('userGroup_err_save_menu')) ?>,
    delete_fail: <?= json_encode(__('userGroup_delete_fail')) ?>,
    field_modul: <?= json_encode(__('userGroup_field_modul')) ?>,
    edit: <?= json_encode(__('userGroup_edit')) ?>,
    loading_modules: <?= json_encode(__('userGroup_loading_modules')) ?>,
    search_group_placeholder: <?= json_encode(__('userGroup_search_group_placeholder')) ?>,
    undo_btn: <?= json_encode(__('userGroup_undo_btn')) ?>,
    undo_message: <?= json_encode(__('userGroup_undo_message')) ?>,
    undo_title: <?= json_encode(__('userGroup_undo_title')) ?>,
    undo_info: <?= json_encode(__('userGroup_undo_info')) ?>,
    dt_length_menu: <?= json_encode(__('userGroup_dt_length_menu')) ?>,
    dt_info: <?= json_encode(__('userGroup_dt_info')) ?>,
    dt_info_empty: <?= json_encode(__('userGroup_dt_info_empty')) ?>,
    dt_info_filtered: <?= json_encode(__('userGroup_dt_info_filtered')) ?>,
    dt_paginate_first: <?= json_encode(__('userGroup_dt_paginate_first')) ?>,
    dt_paginate_last: <?= json_encode(__('userGroup_dt_paginate_last')) ?>,
    dt_paginate_next: <?= json_encode(__('userGroup_dt_paginate_next')) ?>,
    dt_paginate_previous: <?= json_encode(__('userGroup_dt_paginate_previous')) ?>
  };

  // Initialize modules dan setup event handlers
  function initializeModules() {
    if (typeof ModuleAccess !== 'undefined') {
      try { 
        ModuleAccess.init(T); 
      } catch(e) { 
        console.error('ModuleAccess init error:', e); 
      }
    }
    if (typeof MenuAccess !== 'undefined') {
      try { 
        MenuAccess.init(T); 
      } catch(e) { 
        console.error('MenuAccess init error:', e); 
      }
    }
    if (typeof GroupPermissions !== 'undefined') {
      try { 
        GroupPermissions.init(); 
      } catch(e) { 
        console.error('GroupPermissions init error:', e); 
      }
    }
    
    // Setup event handlers after modules are initialized
    setupEventHandlers();
  }
  
  function setupEventHandlers() {
    // Hook menu order dirty flag untuk extracted modules
    (function hookModalClose() {
      const aksesModalEl = document.getElementById('aksesModal');
      aksesModalEl?.addEventListener('hidden.bs.modal', () => {
        if (typeof GroupState !== 'undefined' && !GroupState.isMenuOrderDirty()) return;
        if (typeof GroupState !== 'undefined') GroupState.setMenuOrderDirty(false);
        if (typeof MenuRefresh !== 'undefined') MenuRefresh.refreshMainMenu().catch(console.warn);
      });
    })();

    // View access button handler (delegated to ModuleAccess) - using event delegation
    document.body.addEventListener('click', function(e) {
      const btn = e.target.closest('.view-access');
      if (btn && btn.classList.contains('view-access')) {
        if (!canManageGroups) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (typeof ModuleAccess !== 'undefined' && typeof ModuleAccess.openAccess === 'function') {
          ModuleAccess.openAccess(btn);
    } else {
          console.error('ModuleAccess.openAccess is not available');
        }
        return false;
      }
    }, true);

    // View menu button handler (delegated to MenuAccess) - using event delegation
    document.body.addEventListener('click', function(e) {
      const btn = e.target.closest('.view-menu');
      if (btn && btn.classList.contains('view-menu')) {
        if (!canManageGroups) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        if (typeof MenuAccess !== 'undefined' && typeof MenuAccess.openMenuFromBtn === 'function') {
          if (typeof GroupState !== 'undefined' && typeof GroupState.setLastMenuBtn === 'function') {
            GroupState.setLastMenuBtn(btn);
          }
          MenuAccess.openMenuFromBtn(btn);
        } else {
          // fallback: try to show modal directly and call editor if available
          try {
            const el = document.getElementById('aksesMenuModal');
            GroupState && typeof GroupState.setLastMenuBtn === 'function' && GroupState.setLastMenuBtn(btn);
            if (el && window.bootstrap && bootstrap.Modal) {
              GroupUtils.ensureInBody(el);
              bootstrap.Modal.getOrCreateInstance(el, { backdrop: true, focus: true, keyboard: true }).show();
            }
            const gid = btn.getAttribute('data-group-id');
            if (typeof MenuAccess !== 'undefined' && typeof MenuAccess.openMenuEditor === 'function') {
              MenuAccess.openMenuEditor(gid);
            }
          } catch (e) {
            console.error('Menu fallback failed', e);
          }
        }
        return false;
      }
    }, true);

    // View group perms button handler (delegated to GroupPermissions)
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.view-group-perms');
      if (btn) {
        if (!canManageGroups) return;
        e.preventDefault();
        if (typeof GroupPermissions !== 'undefined' && GroupPermissions.openGroupPermsFromBtn) {
          GroupPermissions.openGroupPermsFromBtn(btn);
        }
      }
    }, true);
  }
  
  // Initialize immediately if DOM is ready, otherwise wait
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeModules);
    } else {
    // Use setTimeout to ensure all scripts are loaded
    setTimeout(initializeModules, 100);
  }

  // Tooltip ringan
  document.querySelectorAll('[title]').forEach(el => {
    if (window.bootstrap && bootstrap.Tooltip) {
      new bootstrap.Tooltip(el, { container: 'body' });
    }
  });
})();

// =========================================================
// Priority 3: UX Improvements
// =========================================================

(function() {
  'use strict';
  const canManageGroups = <?= $canManageGroups ? 'true' : 'false' ?>;

  // =========================================================
  // 1. Search/Filter untuk Group List (DataTable)
  // =========================================================
  let groupTableDT = null;
  function initGroupTable() {
    const table = document.getElementById('groupTable');
    if (!table || !window.hasDT()) return;
    
    // Get translations from T object (defined in main script)
    // Get translations from T object (defined in main script)
    const dtLang = (typeof T !== 'undefined') ? {
      searchPlaceholder: T.search_group_placeholder || "Search...",
      lengthMenu: T.dt_length_menu || "Show _MENU_ entries",
      info: T.dt_info || "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: T.dt_info_empty || "No entries",
      infoFiltered: T.dt_info_filtered || "(filtered from _MAX_ total entries)",
      paginate: {
        first: T.dt_paginate_first || "First",
        last: T.dt_paginate_last || "Last",
        next: T.dt_paginate_next || "Next",
        previous: T.dt_paginate_previous || "Previous"
      }
    } : {
      searchPlaceholder: "Search...",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: "No entries",
      infoFiltered: "(filtered from _MAX_ total entries)",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous"
      }
    };
    // canManageGroups already defined in parent scope

    groupTableDT = jQuery('#groupTable').DataTable({
      pageLength: 10,
      lengthChange: true,
      ordering: true,
      order: [[2, 'asc']], // Sort by code
      columnDefs: [
        { targets: [0], orderable: false }, // Disable sorting on first column
        { targets: [3, 4, 5], orderable: false, searchable: false }
      ],
      language: {
        search: "",
        searchPlaceholder: dtLang.searchPlaceholder,
        lengthMenu: dtLang.lengthMenu,
        info: dtLang.info,
        infoEmpty: dtLang.infoEmpty,
        infoFiltered: dtLang.infoFiltered,
        paginate: dtLang.paginate
      },
      stateSave: false,
      processing: false,
      // Remove the search filter (f) but keep the top-right container for buttons
      dom: '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right">>' +
        't' +
        '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
      drawCallback: function(settings) {
        try {
          const api = this.api();
          if (!api || typeof api.page !== 'function') return;
          const pageInfo = api.page.info();
          if (!pageInfo || typeof pageInfo.start === 'undefined') return;
          
          // Update nombor Bil untuk setiap row pada current page
          let rowIndex = 0;
          api.rows({page: 'current'}).every(function() {
            const bilNumber = pageInfo.start + rowIndex + 1;
            const $row = jQuery(this.node());
            $row.find('td:first').text(bilNumber);
            rowIndex++;
            return true;
          });
        } catch (e) {
          // Silent fail
        }
      },
      initComplete: function() {
        // Add "Tambah Kumpulan" (and page-level Tambah Menu) buttons beside controls
        const $right = jQuery('#groupTable_wrapper .dt-top-right');
        if ($right.length && canManageGroups) {
          // Add "Tambah Menu" (page-level) and "Tambah Kumpulan" buttons
          // Use full action labels for visible buttons
          const fullMenuLabel = 'Menu';
          const fullModuleLabel = 'Modul';
          const fullGroupLabel = 'Kumpulan';

          // Visible text shows full labels; also include title/aria-label for accessibility
          const $btnMenu = jQuery('<button type="button" id="btnAddMenuPage" class="btn btn-sm btn-primary me-2" title="' + GroupUtils.esc(fullMenuLabel) + '" aria-label="' + GroupUtils.esc(fullMenuLabel) + '"><i class="ri-menu-2-line"></i> ' + GroupUtils.esc(fullMenuLabel) + '</button>');
          const $btnModule = jQuery('<button type="button" id="btnAddModule" class="btn btn-sm btn-primary me-2" title="' + GroupUtils.esc(fullModuleLabel) + '" aria-label="' + GroupUtils.esc(fullModuleLabel) + '"><i class="ri-stack-line"></i> ' + GroupUtils.esc(fullModuleLabel) + '</button>');
          const $btn = jQuery('<button type="button" id="btnAddGroup" class="btn btn-sm btn-primary" title="' + GroupUtils.esc(fullGroupLabel) + '" aria-label="' + GroupUtils.esc(fullGroupLabel) + '"><i class="ri-group-line"></i> ' + GroupUtils.esc(fullGroupLabel) + '</button>');

          // Append in order: Menu, Modul, Group
          $right.append($btnMenu).append($btnModule).append($btn);

          // Page-level Add Menu handler: delegate to MenuAccess.handleAddMenu when available
          $btnMenu.off('click').on('click', function(){
            try {
              if (window.MenuAccess && typeof MenuAccess.handleAddMenu === 'function') {
                // Attempt to call handler which will resolve group context or show warnings
                MenuAccess.handleAddMenu();
                return;
              }
            } catch (e) { /* ignore and fallback */ }
            // Fallback: show guidance (no modal) to avoid overlay-only state
            if (window.Swal && typeof Swal.fire === 'function') {
              Swal.fire({
                icon: 'info',
                title: 'Makluman',
                text: 'Sila pilih kumpulan dahulu melalui butang Akses Menu.',
                confirmButtonText: 'OK'
              });
            } else {
              alert('Sila pilih kumpulan dahulu melalui butang Akses Menu.');
            }
          });

          $btnModule.off('click').on('click', function(){
            const modal = new bootstrap.Modal(document.getElementById('moduleCreateModal'));
            const form = document.getElementById('moduleCreateForm');
            if (form && !form.dataset.keepValues) {
              form.reset();
            }
            if (form) delete form.dataset.keepValues;
            modal.show();
          });

          // Existing Add Group handler
          $btn.off('click').on('click', function(){
            const modal = new bootstrap.Modal(document.getElementById('groupCreateModal'));
            // reset form
            document.getElementById('gc_groupKod').value = '';
            document.getElementById('gc_groupName').value = '';
            document.getElementById('gc_color').value = '';
            document.getElementById('gc_priority').value = '0';
            // populate modul/menu selects before showing
            try { if (window.MenuAccess && typeof window.MenuAccess.populateCreateModal === 'function') window.MenuAccess.populateCreateModal().finally(()=>modal.show()); else modal.show(); } catch(e){ modal.show(); }
          });
        }
      }
    });
  }

  // =========================================================
  // 2. Loading Indicators untuk Button Clicks
  // =========================================================
  function setButtonLoading(btn, isLoading) {
    if (!btn) return;
    
    // For icon-only buttons, just add/remove loading class without changing content
    const isIconButton = btn.classList.contains('icon-btn') || 
                         (btn.querySelector('i') && !btn.textContent.trim());
    
    if (isLoading) {
      btn.classList.add('btn-loading');
      btn.disabled = true;
      
      // Only modify content for buttons with text, not icon-only buttons
      if (!isIconButton) {
        const text = btn.querySelector('.btn-text') || btn.childNodes[0];
        if (text && text.nodeType === 3) {
          btn.dataset.originalText = text.textContent;
          text.textContent = '';
        } else if (text && btn.innerHTML) {
          btn.dataset.originalText = btn.innerHTML;
          btn.innerHTML = '<span class="btn-text" style="opacity:0">' + btn.innerHTML + '</span>';
        }
      }
    } else {
      btn.classList.remove('btn-loading');
      btn.disabled = false;
      
      // Only restore content for buttons with text
      if (!isIconButton && btn.dataset.originalText) {
        btn.innerHTML = btn.dataset.originalText;
        delete btn.dataset.originalText;
      }
    }
  }

  // Add loading to action buttons (skip icon-only buttons to preserve icons)
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.view-access, .view-menu, .view-group-perms, #menuEditSaveBtn');
    if (btn && !btn.disabled) {
      const isIconButton = btn.classList.contains('icon-btn');
      
      // Skip loading indicator for icon-only buttons to preserve icon appearance
      if (isIconButton) {
        // Just briefly disable to prevent double-clicks, no visual change
        btn.disabled = true;
        const reEnable = () => {
          btn.disabled = false;
        };
        // Re-enable after modal opens or after timeout
        const timeout = setTimeout(reEnable, 2000);
        document.addEventListener('shown.bs.modal', () => {
          clearTimeout(timeout);
          reEnable();
        }, { once: true });
        return; // Don't apply loading indicator
      }
      
      // For buttons with text, apply loading indicator
      if (!btn.classList.contains('btn-loading')) {
        const originalDisabled = btn.disabled;
        setButtonLoading(btn, true);
        
        const cleanup = () => {
          setButtonLoading(btn, false);
          btn.disabled = originalDisabled;
        };
        
        const timeout = setTimeout(cleanup, 3000);
        document.addEventListener('shown.bs.modal', () => {
          clearTimeout(timeout);
          cleanup();
        }, { once: true });
      }
    }
    }, true);

  // =========================================================
  // 3. Undo Functionality untuk Menu Delete
  // =========================================================
  let undoStack = [];
  const UNDO_TIMEOUT = 10000; // 10 seconds

  function showUndoNotification(message, undoAction, cancelAction) {
    // Remove existing notifications
    document.querySelectorAll('.undo-notification').forEach(el => el.remove());
    
    // Get translations from T object (defined in main script)
    const undoBtnText = (typeof T !== 'undefined' && T.undo_btn) ? T.undo_btn : 'Cancel';

    const notification = document.createElement('div');
    notification.className = 'alert alert-info alert-dismissible fade show undo-notification';
    notification.innerHTML = `
      <div class="d-flex align-items-center justify-content-between">
        <span>${GroupUtils.esc(message)}</span>
        <div class="ms-3">
          <button type="button" class="btn btn-sm btn-outline-light undo-btn">${GroupUtils.esc(undoBtnText)}</button>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
    `;

    document.body.appendChild(notification);

    const undoBtn = notification.querySelector('.undo-btn');
    undoBtn.addEventListener('click', () => {
      if (typeof undoAction === 'function') undoAction();
      notification.remove();
    });

    const closeBtn = notification.querySelector('.btn-close');
    closeBtn.addEventListener('click', () => {
      if (typeof cancelAction === 'function') cancelAction();
    });

    // Auto-remove after timeout
    setTimeout(() => {
      if (notification.parentNode) {
        if (typeof cancelAction === 'function') cancelAction();
        notification.remove();
      }
    }, UNDO_TIMEOUT);
  }

  // Hook into MenuAccess.deleteMenu untuk undo
  if (typeof MenuAccess !== 'undefined') {
    const originalDeleteMenu = MenuAccess.deleteMenu;
    if (originalDeleteMenu) {
      MenuAccess.deleteMenu = async function(menuID, tr) {
        // Store original data for undo
        const menuData = {
          id: menuID,
          name: tr ? (tr.querySelector('td:nth-child(2) .fw-semibold')?.textContent || '') : '',
          row: tr ? tr.outerHTML : null,
          tr: tr
        };

        // Call original delete
        await originalDeleteMenu.call(this, menuID, tr);

        // Show undo notification
        const undoMsgTemplate = (typeof T !== 'undefined' && T.undo_message) ? T.undo_message : 'Menu "%s" has been deleted.';
        const undoMsg = undoMsgTemplate.replace('%s', GroupUtils.esc(menuData.name));
        const undoTitle = (typeof T !== 'undefined' && T.undo_title) ? T.undo_title : 'Cancel';
        const undoInfo = (typeof T !== 'undefined' && T.undo_info) ? T.undo_info : 'Undo function requires server-side endpoint. Please contact admin.';
        
        showUndoNotification(
          undoMsg,
          () => {
            // Undo: Restore menu (would need server-side endpoint)
            if (window.Swal && Swal.fire) {
              Swal.fire({
                icon: 'info',
                title: undoTitle,
                text: undoInfo,
                timer: 2000,
                showConfirmButton: false
              });
            }
          },
          () => {
            // Cancel: Confirm deletion
            console.log('Deletion confirmed');
          }
        );
      };
    }
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(initGroupTable, 100);
    });
      } else {
    setTimeout(initGroupTable, 100);
  }
})();

document.addEventListener('DOMContentLoaded', function(){
  const saveBtn = document.getElementById('moduleCreateSaveBtn');
  const form = document.getElementById('moduleCreateForm');
  if (saveBtn && form) {
    saveBtn.addEventListener('click', function(e){
      e.preventDefault();
      form.submit();
    });
  }

  <?php if (!empty($moduleSwal) && is_array($moduleSwal)): ?>
  if (window.Swal && typeof Swal.fire === 'function') {
    Swal.fire({
      icon: <?= json_encode((string)($moduleSwal['icon'] ?? 'info')) ?>,
      title: <?= json_encode((string)($moduleSwal['title'] ?? '')) ?>,
      text: <?= json_encode((string)($moduleSwal['text'] ?? '')) ?>,
      confirmButtonText: <?= json_encode((string)__('config_js_btn_ok')) ?>
    });
  }
  <?php endif; ?>

  <?php if ($moduleFormOpen): ?>
  const modalEl = document.getElementById('moduleCreateModal');
  if (modalEl && window.bootstrap && bootstrap.Modal) {
    const formEl = document.getElementById('moduleCreateForm');
    if (formEl) formEl.dataset.keepValues = '1';
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
  <?php endif; ?>
});
</script>



</body>
</html>
