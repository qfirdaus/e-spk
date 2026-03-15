<?php
// pages/senarai-pengguna.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

// Set flag untuk Select2 (untuk load CSS & JS)
$NEED_SELECT2 = true;

require_once __DIR__ . '/../controllers/UserListController.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Database.php';

$controller   = new UserListController();

$lang         = $controller->lang ?? 'ms';
$profile      = $controller->profile ?? [];
$senaraiUser  = $controller->senaraiUser ?? [];

// User model untuk getAvatarUrl
$dbMySQL = Database::getInstance('mysql')->getConnection();
$userModel = new User($dbMySQL);

// CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

// helper escape
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// format staf id: XXXX-XX jika 6 digit
function format_stafid(?string $id): string {
  $id = trim((string)$id);
  $raw = str_replace('-', '', $id);
  if ($raw !== '' && ctype_digit($raw) && strlen($raw) === 6) {
    return substr($raw,0,4) . '-' . substr($raw,4,2);
  }
  return $id;
}

/**
 * Cache helper untuk user list page (session-based cache dengan TTL)
 */
final class UserListCache {
    private static string $namespace = 'userlist_cache';
    
    public static function get(string $key, int $ttl): mixed {
        $now = time();
        $c = $_SESSION[self::$namespace][$key] ?? null;
        if (!$c) return null;
        if (($c['ts'] + $ttl) < $now) {
            unset($_SESSION[self::$namespace][$key]);
            return null;
        }
        return $c['val'];
    }
    
    public static function set(string $key, mixed $val): void {
        if (!isset($_SESSION[self::$namespace])) {
            $_SESSION[self::$namespace] = [];
        }
        $_SESSION[self::$namespace][$key] = ['ts' => time(), 'val' => $val];
    }
    
    public static function clear(?string $prefix = null): void {
        if (!isset($_SESSION[self::$namespace])) return;
        if ($prefix === null) {
            unset($_SESSION[self::$namespace]);
            return;
        }
        foreach (array_keys($_SESSION[self::$namespace]) as $k) {
            if (str_starts_with($k, $prefix)) {
                unset($_SESSION[self::$namespace][$k]);
            }
        }
    }
}

// Get current user's group (used for tracking/logging only; no page-level permission checks)
$currentUserGroup = $profile['f_groupKod'] ?? '';
$roleAdminSaId = defined('PRESTASI_ROLE_ID_ADM_SA') ? (int)PRESTASI_ROLE_ID_ADM_SA : 0;
$roleAdminHrId = defined('PRESTASI_ROLE_ID_ADM_HR') ? (int)PRESTASI_ROLE_ID_ADM_HR : 0;
$roleAdminKeId = defined('PRESTASI_ROLE_ID_ADM_KE') ? (int)PRESTASI_ROLE_ID_ADM_KE : 0;
$roleAdminSaKod = defined('PRESTASI_ROLE_KOD_ADM_SA') ? (string)PRESTASI_ROLE_KOD_ADM_SA : (defined('PRESTASI_ROLE_ADM_SA') ? (string)PRESTASI_ROLE_ADM_SA : 'ADM-SA');
$roleAdminHrKod = defined('PRESTASI_ROLE_ADM_HR') ? (string)PRESTASI_ROLE_ADM_HR : 'ADM-HR';
$roleAdminKeKod = defined('PRESTASI_ROLE_ADM_KE') ? (string)PRESTASI_ROLE_ADM_KE : 'ADM-KE';
$isSuperAdmin = function_exists('is_user_super_admin') ? is_user_super_admin($profile, $dbMySQL) : ($roleAdminSaId > 0 && (int)($profile['f_groupID'] ?? 0) === $roleAdminSaId);

// ======================= Load Group List (fresh DB for style consistency) =======================
$senaraiGroup = [];
try {
    $groupSql = "SELECT f_groupID, f_groupKod, f_groupName, f_badge_class, f_row_class, f_color FROM tbl_m_group ORDER BY f_groupName ASC";
    $groupStmt = $dbMySQL->query($groupSql);
    $senaraiGroup = $groupStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    error_log('[senarai-pengguna] Error loading groups: ' . $e->getMessage());
    $senaraiGroup = [];
}

// ======================= Data-Driven UI Style Map (Group) =======================
$groupUiMaps = prestasi_group_ui_load_maps($dbMySQL, $senaraiGroup);
$groupUiById = $groupUiMaps['by_id'] ?? [];
$groupUiByCode = $groupUiMaps['by_code'] ?? [];
$showGroupUiDebug = $isSuperAdmin && (string)($_GET['debug_group_ui'] ?? '') === '1';

// Build dynamic row highlight CSS based on tbl_m_group.f_color (no manual CSS per role needed).
$groupDynamicCssRules = [];
foreach ($senaraiGroup as $gRow) {
  $gId = (int)($gRow['f_groupID'] ?? 0);
  $gKod = (string)($gRow['f_groupKod'] ?? '');
  $resolved = prestasi_group_ui_resolve($groupUiMaps, $gId, $gKod);
  $rowClass = trim((string)($resolved['rowClass'] ?? ''));
  $rowColor = trim((string)($resolved['rowColor'] ?? ''));
  if ($rowClass === '' || $rowColor === '') continue;
  $safeClass = preg_replace('/[^a-zA-Z0-9_-]+/', '', $rowClass) ?? '';
  if ($safeClass === '') continue;
  $safeColor = trim((string)$rowColor);
  if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $safeColor) && !preg_match('/^[a-zA-Z]+$/', $safeColor)) continue;
  $groupDynamicCssRules[$safeClass] = $safeColor;
}

function group_badge_inline_style(array $groupStyle): string {
  $badgeClass = trim((string)($groupStyle['badgeClass'] ?? ''));
  $rowColor = trim((string)($groupStyle['rowColor'] ?? ''));
  if ($badgeClass !== '' && $badgeClass !== 'bg-secondary') return '';
  if ($rowColor === '') return '';
  if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $rowColor) && !preg_match('/^[a-zA-Z]+$/', $rowColor)) return '';
  return 'background-color: ' . $rowColor . '; color: #fff;';
}

// ======================= Staf List: Lazy Load via AJAX (removed from page load) =======================
// Staf list akan di-load via AJAX endpoint (user-list-staf-options.php) dengan caching
// Ini mengurangkan initial page load time
// Namun, sediakan fallback dari session cache supaya dropdown ada data jika cache wujud
$senaraiStaf = [];
$existingStafIDs = [];
try {
  if (isset($_SESSION['userlist_cache']['staf_options_list']['val']) && is_array($_SESSION['userlist_cache']['staf_options_list']['val'])) {
    $senaraiStaf = $_SESSION['userlist_cache']['staf_options_list']['val'];
  }

  // Existing staf IDs digunakan untuk disable option jika user sudah wujud
  $dbMySQL = Database::getInstance('mysql')->getConnection();
  $existingStmt = $dbMySQL->query("SELECT DISTINCT f_stafID FROM tbl_m_user WHERE f_stafID IS NOT NULL AND f_stafID <> ''");
  $existingRows = $existingStmt->fetchAll(PDO::FETCH_COLUMN);
  $existingRaw = array_map('trim', array_filter($existingRows));
  $existingStafIDs = array_map(function($id){ return str_replace('-', '', $id); }, $existingRaw);
} catch (Throwable $e) {
  // Silent fallback - if DB/cache not available, JS will lazy-load via AJAX when modal opens
  $senaraiStaf = $senaraiStaf ?? [];
  $existingStafIDs = $existingStafIDs ?? [];
}
?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <meta name="csrf-token" content="<?= h($csrf) ?>">
  
  <!-- ✅ Select2 CSS (untuk dropdown) -->
  <link href="<?= base_url('assets/vendor/select2/css/select2.min.css') ?> ?>" rel="stylesheet">
  <!-- ✅ Standard DataTables CSS (shared) -->
  <link href="<?= base_url('assets/css/datatables-standard.css') ?> ?>" rel="stylesheet">
  
  <!-- ✅ Senarai APC Admin CSS (untuk table, dropdown, textbox styling) -->
  
  <style>
    /* Pastikan table tak overflow & kekal satu baris */
    #userDT { table-layout: fixed; width:100%; }
    #userDT th, #userDT td { vertical-align: middle; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    /* Lebar kolum */
    #userDT th.col-bil,     #userDT td.col-bil     { width:5%;  text-align:center; }
    #userDT th.col-nama,    #userDT td.col-nama    { width:25%; }
    #userDT th.col-jabatan, #userDT td.col-jabatan { width:20%; }
    #userDT th.col-jawatan, #userDT td.col-jawatan { width:20%; }
    #userDT th.col-group,   #userDT td.col-group   { width:10%; }
    #userDT th.col-akses,   #userDT td.col-akses   { width:10%; text-align:center; }
    #userDT th.col-actions, #userDT td.col-actions { width:10%; text-align:center; }
    .truncate-1line { display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .icon-btn { padding:.25rem .5rem; line-height:1; }
    /* Bottom bar: info kiri, pagination kanan */
    .dt-bottom-row { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
    .dt-bottom-row .dataTables_info { 
      margin:.25rem 0; 
      white-space: nowrap; /* ✅ Pastikan "Papar X rekod" dalam satu baris */
      line-height: 1.5;
    }
    .dt-bottom-row .dataTables_paginate { margin-left:auto; }
    /* ✅ DataTables length selector - pastikan dalam satu baris */
    .dataTables_length,
    #userDT_wrapper .dataTables_length {
      white-space: nowrap !important;
      line-height: 1.4;
      display: inline-block;
    }
    .dataTables_length label,
    #userDT_wrapper .dataTables_length label {
      white-space: nowrap !important;
      display: inline-flex !important;
      align-items: center;
      gap: 0.4rem;
      margin-bottom: 0;
      flex-wrap: nowrap !important;
      font-size: 0.875rem !important;
    }
    .dataTables_length select,
    #userDT_wrapper .dataTables_length select {
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
    .dataTables_length select:hover,
    #userDT_wrapper .dataTables_length select:hover {
      border-color: #ced4da !important;
    }
    .dataTables_length select:focus,
    #userDT_wrapper .dataTables_length select:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
      outline: none !important;
    }
    /* ✅ Pastikan text "Papar" dan "rekod" tidak wrap */
    .dataTables_length label > * {
      white-space: nowrap !important;
      display: inline !important;
    }
    /* ✅ Pastikan dt-top-left container tidak wrap */
    .dt-top-left {
      white-space: nowrap !important;
      flex-wrap: nowrap !important;
    }
    .dt-top-left .dataTables_length {
      white-space: nowrap !important;
    }
    /* ✅ Professional SweetAlert styling untuk sync success */
    .swal2-popup-custom {
      border-radius: 1rem !important;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
    }
    .swal2-title-custom {
      font-size: 1.5rem !important;
      font-weight: 700 !important;
      color: #1e293b !important;
      margin-bottom: 1rem !important;
    }
    .swal2-confirm-custom {
      padding: 0.75rem 2rem !important;
      font-size: 1rem !important;
      font-weight: 600 !important;
      border-radius: 0.5rem !important;
      box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3) !important;
      transition: all 0.2s ease !important;
    }
    .swal2-confirm-custom:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 16px rgba(25, 135, 84, 0.4) !important;
    }
    /* ✅ Button styling untuk selari dengan dropdown list (Select2) */
    .dt-top-right button,
    #userDT_wrapper .dt-top-right button {
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
    .dt-top-right button:hover,
    #userDT_wrapper .dt-top-right button:hover {
      border-color: #ced4da !important;
    }
    .dt-top-right button:focus,
    #userDT_wrapper .dt-top-right button:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }
    #userDT_wrapper #dtGroupFilter {
      min-width: 240px !important;
    }
    #userDT_wrapper .dt-top-right,
    #userDT_wrapper .dt-top-left {
      align-items: flex-start !important;
    }
    #userDT_wrapper .dt-top-right > * {
      position: relative !important;
      top: 0 !important;
    }
    /* ✅ Remove gap antara button - gunakan gap pada container */
    .dt-top-right {
      gap: 0.5rem !important; /* Consistent gap */
    }
    .dt-top-right button + button {
      margin-left: 0 !important; /* Remove default margin */
    }
    /* Modal - Professional Styling */
    #userGroupModal .modal-dialog { 
      max-width: 720px;
    }
    #userGroupModal .modal-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-bottom: none;
      padding: 1.25rem 1.75rem;
    }
    #userGroupModal .modal-header .modal-title {
      color: white;
      font-weight: 600;
      font-size: 1.15rem;
      letter-spacing: 0.3px;
    }
    #userGroupModal .modal-header .btn-close {
      filter: invert(1);
      opacity: 0.9;
    }
    #userGroupModal .modal-header .btn-close:hover {
      opacity: 1;
    }
    #userGroupModal .modal-body {
      padding: 1.25rem 1.5rem;
    }
    #userGroupModal .modal-footer {
      border-top: none;
      padding: 1rem 1.75rem;
      background-color: #f8f9fa;
    }
    #userGroupModal .modal-content {
      border: none;
    }
    #userGroupModal .user-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #667eea;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
      margin: 0;
      display: block;
    }
    #userGroupModal .user-info-row {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
    }
    #userGroupModal .avatar-container {
      text-align: left;
      margin-bottom: 0;
      flex: 0 0 auto;
    }
    #userGroupModal .info-card {
      flex: 1 1 auto;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border: 2px solid #e9ecef;
      border-radius: 0.75rem;
      padding: 0.9rem;
      margin-bottom: 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    #userGroupModal .info-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 0.75rem;
      padding: 0.625rem;
      background-color: rgba(255,255,255,0.7);
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }
    #userGroupModal .info-item:hover {
      background-color: rgba(255,255,255,0.9);
      transform: translateX(2px);
    }
    #userGroupModal .info-item:last-child {
      margin-bottom: 0;
    }
    #userGroupModal .info-icon {
      color: #667eea;
      font-size: 1.35rem;
      margin-right: 0.875rem;
      margin-top: 0.125rem;
      flex-shrink: 0;
    }
    #userGroupModal .info-content {
      flex: 1;
    }
    #userGroupModal .info-label {
      font-size: 0.75rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }
    #userGroupModal .info-value {
      font-size: 0.95rem;
      color: #212529;
      font-weight: 600;
      line-height: 1.4;
    }
    #userGroupModal .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.75rem;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
    }
    #userGroupModal .form-label i {
      margin-right: 0.5rem;
      font-size: 1.1rem;
    }
    #userGroupModal .form-select {
      min-height: 50px;
      border: 2px solid #e9ecef;
      border-radius: 0.5rem;
      padding: 0.875rem 1rem;
      font-size: 1rem;
      transition: all 0.2s ease;
    }
    /* Compact select to match +Peranan button height */
    #userGroupModal .form-select.compact-select {
      height: 36px !important;
      min-height: 36px !important;
      padding: 0.375rem 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.4 !important;
      box-sizing: border-box !important;
    }
    #userGroupModal .btn.compact-btn {
      height: 36px !important;
      min-height: 36px !important;
      padding: 0.375rem 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.4 !important;
      display: inline-flex !important;
      align-items: center !important;
      border-width: 2px !important;
      box-sizing: border-box !important;
      vertical-align: middle !important;
    }
    #userGroupModal #ug_groupKod,
    #addUserModal #au_groupKod {
      width: 100% !important;
      max-width: 100% !important;
      display: block !important;
    }
    #userGroupModal .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    #userGroupModal .modal-footer .btn {
      padding: 0.5rem 1.25rem;
      font-weight: 500;
      border-radius: 0.5rem;
    }
    /* Extra Role Modal (match Tambah Menu style) */
    #roleExtraModal .modal-dialog { max-width: 640px; }
    #roleExtraModal .modal-header {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: #fff;
    }
    #roleExtraModal .modal-footer .btn-primary {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
      border: none;
    }
    #roleExtraModal .role-list {
      display: grid;
      gap: 0.75rem;
    }
    #roleExtraModal .role-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      border: 2px solid #e9ecef;
      border-radius: 0.75rem;
      background: #fff;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    #roleExtraModal .role-item:hover {
      border-color: #f093fb;
      box-shadow: 0 4px 10px rgba(245, 87, 108, 0.12);
    }
    #roleExtraModal .role-item input[type="checkbox"] { transform: scale(1.1); }
    #roleExtraModal .role-label { font-weight: 600; color: #212529; }
    #userGroupModal #ug_error {
      margin-top: 1rem;
      border-radius: 0.5rem;
      border-left: 4px solid #dc3545;
    }
    /* Section divider untuk layout yang lebih professional */
    #userGroupModal .form-section {
      margin-bottom: 1.25rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid #e9ecef;
    }
    #userGroupModal .form-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    #userGroupModal .form-section-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: #495057;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 1.25rem;
      padding-bottom: 0.625rem;
      border-bottom: 3px solid #667eea;
      display: flex;
      align-items: center;
    }
    #userGroupModal .form-section-title i {
      margin-right: 0.5rem;
      font-size: 1rem;
      color: #667eea;
    }
    /* Validation blink effect */
    #userGroupModal .field-invalid {
      animation: fieldBlinkEdit 0.5s ease-in-out 3;
      border-color: #dc3545 !important;
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    @keyframes fieldBlinkEdit {
      0%, 100% { 
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
      }
      50% { 
        border-color: #ff6b7a !important;
        box-shadow: 0 0 0 0.3rem rgba(220, 53, 69, 0.4) !important;
      }
    }
    /* Form field spacing */
    #userGroupModal .mb-3 {
      margin-bottom: 1.25rem !important;
    }
    #userGroupModal .mb-0 {
      margin-bottom: 0 !important;
    }
    
    /* ✅ Table styling sama seperti senarai-apc-admin.php */
    #userDT {
      border-radius: 0.75rem;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    #userDT thead {
      background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
      color: #ffffff;
    }
    #userDT thead th {
      font-weight: 700;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 1rem 0.75rem;
      border: none;
      color: #ffffff;
    }
    #userDT tbody td {
      vertical-align: top;
    }
    #userDT tbody tr {
      transition: all 0.2s ease;
    }
    #userDT tbody tr:hover {
      background: #f8fafc !important;
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    #userDT tbody td {
      padding: 0.875rem 0.75rem;
      border-color: #f1f5f9;
      vertical-align: middle;
    }
    /* Dark theme support */
    html[data-bs-theme="dark"] #userDT thead {
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    }
    html[data-bs-theme="dark"] #userDT tbody tr:hover {
      background: #334155 !important;
    }
    /* Remove stripline effect untuk semua rows */
    #userDT tbody tr,
    #userDT tbody tr:nth-of-type(odd),
    #userDT tbody tr:nth-of-type(even) {
      background-color: transparent !important;
    }
    
    /* Row highlighting berdasarkan group - override semua Bootstrap styles */
    #userDT tbody tr.row-group-adm-sa {
      background-color: #ffe8e8 !important; /* Merah lembut */
    }
    #userDT tbody tr.row-group-adm-sa td {
      background-color: #ffe8e8 !important;
    }
    #userDT tbody tr.row-group-adm-sa:hover {
      background-color: #ffdcdc !important;
    }
    #userDT tbody tr.row-group-adm-sa:hover td {
      background-color: #ffdcdc !important;
    }
    
    #userDT tbody tr.row-group-adm-hr {
      background-color: #fffef0 !important; /* Kuning lembut */
    }
    #userDT tbody tr.row-group-adm-hr td {
      background-color: #fffef0 !important;
    }
    #userDT tbody tr.row-group-adm-hr:hover {
      background-color: #fff9d6 !important;
    }
    #userDT tbody tr.row-group-adm-hr:hover td {
      background-color: #fff9d6 !important;
    }
    /* Peranan lain tidak perlu highlight */
    <?php foreach ($groupDynamicCssRules as $cssClass => $cssColor): ?>
    #userDT tbody tr.<?= h($cssClass) ?> {
      background-color: <?= h($cssColor) ?> !important;
    }
    #userDT tbody tr.<?= h($cssClass) ?> td {
      background-color: <?= h($cssColor) ?> !important;
      background-image: linear-gradient(rgba(255,255,255,.58), rgba(255,255,255,.58)) !important;
    }
    #userDT tbody tr.<?= h($cssClass) ?>:hover {
      background-color: <?= h($cssColor) ?> !important;
    }
    #userDT tbody tr.<?= h($cssClass) ?>:hover td {
      background-color: <?= h($cssColor) ?> !important;
      background-image: linear-gradient(rgba(255,255,255,.46), rgba(255,255,255,.46)) !important;
    }
    <?php endforeach; ?>
    
    /* Highlight effect untuk row yang baru dikemas kini */
    #userDT tbody tr.row-updated-highlight {
      border-left: 4px solid #28a745 !important;
      box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.30) !important;
      animation: highlightPulse 1.2s ease-in-out infinite;
    }
    #userDT tbody tr.row-updated-highlight:hover {
      box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.40) !important;
    }
    @keyframes highlightPulse {
      0%, 100% { box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.28); }
      50% { box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.48); }
    }

    /* Carian di kanan, kemas dan sebaris */
    #userDT_wrapper .row.mb-2 { align-items: center; }
    #userDT_wrapper .dataTables_filter { text-align: right; }
    #userDT_wrapper .dataTables_filter label { 
      margin: 0 !important;
      font-size: 0.875rem !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
    }
    #userDT_wrapper .dataTables_filter input {
      display: inline-block !important;
      width: 160px !important;
      max-width: 100% !important;
      height: 36px !important;
      min-height: 36px !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.4 !important;
      border: 2px solid #e9ecef !important;
      border-radius: 0.5rem !important;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }
    #userDT_wrapper .dataTables_filter input:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
      outline: none !important;
    }
    
    /* ✅ Dropdown Filter Kumpulan - Compact size (sama dengan input carian dan button) */
    #dtGroupFilter {
      height: 36px !important;
      min-height: 36px !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
      line-height: 1.4 !important;
      border: 2px solid #e9ecef !important;
      border-radius: 0.5rem !important;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
      white-space: nowrap !important;
      display: inline-block !important;
    }
    #dtGroupFilter:hover {
      border-color: #ced4da !important;
    }
    #dtGroupFilter:focus {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
      outline: none !important;
    }

    /* Utility: larger select for group filter / modal */
    .big-select {
      font-size: 18px !important;
      padding: 0.6rem 1rem !important;
      min-width: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
      line-height: 1.4 !important;
      display: block !important;
      box-sizing: border-box !important;
    }
    
    /* Modal Tambah Pengguna - Professional Styling */
    #addUserModal .modal-dialog {
      max-width: 850px; /* Larger modal */
    }
    #addUserModal .modal-header {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border-bottom: none;
      padding: 1.25rem 1.75rem;
    }
    #addUserModal .modal-header .modal-title {
      color: white;
      font-weight: 600;
      font-size: 1.15rem;
      letter-spacing: 0.3px;
    }
    #addUserModal .modal-header .btn-close {
      filter: invert(1);
      opacity: 0.9;
    }
    #addUserModal .modal-header .btn-close:hover {
      opacity: 1;
    }
    #addUserModal .modal-body {
      padding: 1.25rem 1.5rem;
    }
    #addUserModal .modal-footer {
      border-top: none;
      padding: 1rem 1.75rem;
      background-color: #f8f9fa;
    }
    #addUserModal .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.75rem;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
    }
    #addUserModal .form-label i {
      margin-right: 0.5rem;
      font-size: 1.1rem;
    }
    /* ✅ Select2 styling sama seperti senarai-apc-admin.php (untuk semua dropdowns) */
    .select2-container--default {
      font-size: 1rem !important;
      width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
      height: 50px !important;
      border: 2px solid #e9ecef !important;
      border-radius: 0.5rem !important;
      padding: 0.875rem 1rem !important;
      font-size: 1rem !important;
      display: flex;
      align-items: center;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .select2-container--default .select2-selection--single:hover {
      border-color: #ced4da !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
      border-color: #86b7fe !important;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
      padding-left: 0.5rem !important;
      padding-right: 30px !important;
      font-size: 1rem !important;
      color: #212529;
      font-weight: 500;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 48px !important;
      right: 10px !important;
      width: 30px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
      border-color: #6c757d transparent transparent transparent;
      border-width: 6px 5px 0 5px;
      margin-top: -3px;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #6c757d;
      font-size: 1rem !important;
    }
    /* Select2 dropdown - lebih besar */
    .select2-dropdown {
      font-size: 1rem !important;
      border: 2px solid #e9ecef !important;
      border-radius: 0.5rem !important;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .select2-results__option {
      padding: 0.75rem 1rem !important;
      font-size: 1rem !important;
      line-height: 1.5 !important;
      transition: background-color 0.15s ease-in-out;
    }
    .select2-results__option--highlighted {
      background-color: #0d6efd !important;
      color: #fff !important;
    }
    .select2-results__option[aria-selected="true"] {
      background-color: #e7f1ff !important;
      color: #0d6efd !important;
    }
    /* Disabled state */
    .select2-container--default.select2-container--disabled .select2-selection--single {
      background-color: #e9ecef !important;
      cursor: not-allowed !important;
      opacity: 0.6;
    }
    /* ✅ Form controls styling sama seperti senarai-apc-admin.php */
    /* Pastikan semua select dalam modal tambah pengguna sama tinggi */
    #addUserModal .form-select,
    #userGroupModal .form-select,
    .form-select {
      min-height: 50px;
      padding: 0.875rem 1rem;
      font-size: 1rem;
      border: 2px solid #e9ecef;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }
    #addUserModal .form-select:focus,
    #userGroupModal .form-select:focus,
    .form-select:focus {
      border-color: #86b7fe;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    /* ✅ Textbox styling sama seperti senarai-apc-admin.php */
    .form-control {
      min-height: 50px;
      padding: 0.875rem 1rem;
      font-size: 1rem;
      border: 2px solid #e9ecef;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }
    .form-control:focus {
      border-color: #86b7fe;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    /* Validation blink effect */
    #addUserModal .field-invalid {
      animation: fieldBlink 0.5s ease-in-out 3;
      border-color: #dc3545 !important;
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    #addUserModal .select2-container.field-invalid .select2-selection--single {
      border-color: #dc3545 !important;
      animation: fieldBlink 0.5s ease-in-out 3;
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    @keyframes fieldBlink {
      0%, 100% { 
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
      }
      50% { 
        border-color: #ff6b7a !important;
        box-shadow: 0 0 0 0.3rem rgba(220, 53, 69, 0.4) !important;
      }
    }
    /* Section divider untuk layout yang lebih professional */
    #addUserModal .form-section {
      margin-bottom: 1.25rem;
      padding-bottom: 1.1rem;
      border-bottom: 2px solid #e9ecef;
    }
    #addUserModal .form-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    #addUserModal .form-section-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: #495057;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 1.25rem;
      padding-bottom: 0.625rem;
      border-bottom: 3px solid #28a745;
      display: flex;
      align-items: center;
    }
    #addUserModal .form-section-title i {
      margin-right: 0.5rem;
      font-size: 1rem;
      color: #28a745;
    }
    /* Form field spacing */
    #addUserModal .mb-3 {
      margin-bottom: 1.25rem !important;
    }
    #addUserModal .mb-0 {
      margin-bottom: 0 !important;
    }
    /* Info card styling - professional look */
    #addUserModal .info-card {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border: 2px solid #e9ecef;
      border-radius: 0.75rem;
      padding: 1rem;
      margin-bottom: 1.25rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    #addUserModal .info-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 0.75rem;
      padding: 0.625rem;
      background-color: rgba(255,255,255,0.7);
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }
    #addUserModal .info-item:hover {
      background-color: rgba(255,255,255,0.9);
      transform: translateX(2px);
    }
    #addUserModal .info-item:last-child {
      margin-bottom: 0;
    }
    #addUserModal .info-icon {
      color: #28a745;
      font-size: 1.35rem;
      margin-right: 0.875rem;
      margin-top: 0.125rem;
      flex-shrink: 0;
    }
    #addUserModal .info-content {
      flex: 1;
    }
    #addUserModal .info-label {
      font-size: 0.75rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }
    #addUserModal .info-value {
      font-size: 0.95rem;
      color: #212529;
      font-weight: 600;
      line-height: 1.4;
    }
    #addUserModal .modal-content {
      border: none;
    }
    #addUserModal .modal-footer .btn {
      padding: 0.5rem 1.25rem;
      font-weight: 500;
      border-radius: 0.5rem;
    }
    #addUserModal #au_error {
      margin-top: 1rem;
      border-radius: 0.5rem;
      border-left: 4px solid #dc3545;
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
              <h4 class="page-title"><?= __('userList_page_heading_main') ?>: <?= __('userList_page_heading_sub') ?></h4>
              <div class="page-title-right">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">
                    <a href="dashboard.php">
                      <i class="ri-home-4-line align-middle me-1"></i> <?= __('breadcrumb_home') ?? 'Home' ?>
                    </a>
                  </li>
                  <li class="breadcrumb-item active"><?= __('userList_page_heading_main') ?></li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <!-- Kad DataTable -->
        <?php if ($showGroupUiDebug): ?>
        <div class="row mb-2">
          <div class="col-12">
            <div class="alert alert-warning py-2 px-3 mb-2">
              <strong>DEBUG GROUP UI</strong>
              <span class="ms-2">groups: <?= h((string)count($senaraiGroup)) ?>, by_id: <?= h((string)count($groupUiById)) ?>, by_code: <?= h((string)count($groupUiByCode)) ?>, css_rules: <?= h((string)count($groupDynamicCssRules)) ?></span>
              <div class="small mt-1">Append <code>?debug_group_ui=1</code> to view this panel.</div>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">

                <table class="table table-bordered align-middle" id="userDT">
                  <thead>
                    <tr>
                      <th class="col-bil"><?= __('userList_col_no') ?></th>
                      <th class="col-nama"><?= __('userList_col_name_staffid') ?></th>
                      <th class="col-jabatan"><?= __('userList_col_department') ?></th>
                      <th class="col-jawatan"><?= __('userList_col_position') ?></th>
                      <th class="col-group"><?= __('userList_col_group') ?></th>
                      <th class="col-akses"><?= __('userList_col_access') ?></th>
                      <th class="col-actions"><?= __('userList_col_actions') ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($senaraiUser)): ?>
                      <?php foreach ($senaraiUser as $u): ?>
                        <?php
                          $userID  = (int)($u['f_userID'] ?? 0);
                          $nama    = (string)($u['f_nama'] ?? '');
                          $stafID  = format_stafid((string)($u['f_stafID'] ?? ''));
                          $jabatan = (string)($u['f_namajabatan'] ?? '');
                          $jawatan = (string)($u['f_jawatan'] ?? '');
                          $gId     = (int)($u['f_groupID'] ?? 0);
                          $gKod    = (string)($u['f_groupKod'] ?? '');
                          $gName   = (string)($u['f_groupName'] ?? $gKod);
                          $extraRoles = $u['extra_roles'] ?? [];
                          if (!is_array($extraRoles)) $extraRoles = [];
                          $extraCount = (int)($u['extra_roles_count'] ?? count($extraRoles));
                          $f_flag  = (int)($u['f_flag'] ?? 0);
                          // f_nopekerja dari MySQL (sudah dalam format betul dari sync: idpekerja -> f_nopekerja)
                          $f_nopekerja = (string)($u['f_nopekerja'] ?? '');
                          // Generate avatar URL menggunakan User class
                          $avatarUrl = $userModel->getAvatarUrl($f_nopekerja);
                        ?>
                        <?php
                          $groupStyle = prestasi_group_ui_resolve($groupUiMaps, $gId, $gKod);
                          $badgeClass = (string)($groupStyle['badgeClass'] ?? 'bg-secondary');
                          $rowClass = (string)($groupStyle['rowClass'] ?? '');
                          $badgeInlineStyle = group_badge_inline_style($groupStyle);
                        ?>
                        <tr data-user-id="<?= h((string)$userID) ?>" data-group-id="<?= h((string)$gId) ?>" data-group-kod="<?= h($gKod) ?>" data-row-class="<?= h($rowClass) ?>" data-flag="<?= h((string)$f_flag) ?>" data-extra-count="<?= h((string)$extraCount) ?>" data-extra-roles="<?= h(implode(', ', $extraRoles)) ?>" class="<?= h($rowClass) ?>">
                          <td class="col-bil"></td>
                          <td class="col-nama"><span class="truncate-1line"><?= h($nama) ?> (<?= h($stafID) ?>)</span></td>
                          <td class="col-jabatan"><span class="truncate-1line"><?= h($jabatan) ?></span></td>
                          <td class="col-jawatan"><span class="truncate-1line"><?= h($jawatan) ?></span></td>
                          <td class="col-group">
                            <span class="badge <?= h($badgeClass) ?>"<?= $badgeInlineStyle !== '' ? ' style="' . h($badgeInlineStyle) . '"' : '' ?>><?= h($gName) ?></span>
                            <i class="ri-information-line ms-1 text-muted extra-roles-info"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="<?= h(!empty($extraRoles) ? implode(', ', $extraRoles) : (__('userList_role_none') ?? "Tiada peranan tambahan.")) ?>">
                            </i>
                          </td>
                          <td class="col-akses">
                            <?php if ($f_flag == 1): ?>
                              <span class="badge bg-success"><?= __('userList_access_granted') ?></span>
                            <?php else: ?>
                              <span class="badge bg-danger"><?= __('userList_access_blocked') ?></span>
                            <?php endif; ?>
                          </td>
                          <td class="col-actions">
                            <?php if ($isSuperAdmin): ?>
                              <button type="button"
                                class="btn btn-outline-primary btn-sm icon-btn btn-edit-group"
                                title="<?= h(__('userList_action_change_group')) ?>"
                                data-user-id="<?= h((string)$userID) ?>"
                                data-nama="<?= h($nama) ?>"
                                data-stafid="<?= h($stafID) ?>"
                                data-nopekerja="<?= h($f_nopekerja) ?>"
                                data-avatar-url="<?= h($avatarUrl) ?>"
                                data-jabatan="<?= h($jabatan) ?>"
                                data-group-id="<?= h((string)$gId) ?>"
                                data-group-kod="<?= h($gKod) ?>"
                                data-group-name="<?= h($gName) ?>"
                                data-flag="<?= h((string)$f_flag) ?>">
                                <i class="ri-pencil-line"></i>
                              </button>
                              <button type="button"
                                class="btn btn-outline-danger btn-sm icon-btn btn-delete-user ms-1"
                                title="<?= h(__('userList_action_delete_user')) ?>"
                                data-user-id="<?= h((string)$userID) ?>"
                                data-nama="<?= h($nama) ?>"
                                data-stafid="<?= h($stafID) ?>">
                                <i class="ri-delete-bin-line"></i>
                              </button>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr><td colspan="7" class="text-center text-muted"><?= __('userList_no_records') ?></td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>

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

<!-- ✅ Select2 JS (untuk dropdown) -->
<script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>?v=<?= h($version) ?>" defer></script>

<!-- Select2 JS (untuk dropdown staf dalam modal tambah pengguna) - NO defer, must load before our code -->
<script>
// Load Select2 synchronously to ensure it's available
(function() {
  var script = document.createElement('script');
  script.src = '<?= base_url('assets/vendor/select2/js/select2.full.min.js') ?>?v=<?= time() ?>';
  script.onload = function() {
    window.__select2ScriptLoaded = true;
  };
  document.head.appendChild(script);
})();
</script>

<!-- MODAL: Tukar Kumpulan -->
<div class="modal fade" id="userGroupModal" tabindex="-1" aria-hidden="true" aria-labelledby="userGroupTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="userGroupTitle">
          <i class="ri-user-settings-line me-2"></i> <?= __('userList_modal_title') ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userList_modal_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <form id="userGroupForm" autocomplete="off">
          <input type="hidden" id="ug_userID" value="">
          <input type="hidden" id="ug_nopekerja" value="">
          
          <!-- Section 1: Maklumat Pengguna -->
          <div class="form-section">
            <div class="form-section-title">
              <i class="ri-user-line me-1"></i> <?= __('userList_modal_section_user_info') ?>
            </div>
            
            <div class="user-info-row">
              <!-- User Avatar -->
              <div class="avatar-container">
                <img id="ug_avatar" src="" alt="User Avatar" class="user-avatar" onerror="this.src='<?= base_url('assets/images/no-image.jpg') ?>'">
              </div>
              
              <!-- User Information Card -->
              <div class="info-card">
                <div class="info-item">
                  <i class="ri-user-line info-icon"></i>
                  <div class="info-content">
                    <div class="info-label"><?= __('userList_modal_label_name') ?></div>
                    <div class="info-value" id="ug_nama">-</div>
                  </div>
                </div>
                <div class="info-item">
                  <i class="ri-building-line info-icon"></i>
                  <div class="info-content">
                    <div class="info-label"><?= __('userList_modal_label_department') ?></div>
                    <div class="info-value" id="ug_jabatan">-</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Section 2: Tetapan Pengguna -->
          <div class="form-section">
            <div class="form-section-title">
              <i class="ri-settings-3-line me-1"></i> <?= __('userList_modal_section_settings') ?>
            </div>
            
            <!-- Group Selection + Extra Roles (inline) -->
            <div class="mb-3">
              <label class="form-label">
                <i class="ri-group-line"></i> <?= __('userList_modal_label_group') ?>
              </label>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="flex-grow-1">
                  <select class="form-select compact-select" id="ug_groupKod">
                    <option value=""><?= __('userList_group_filter_placeholder') ?? '-- Pilih kumpulan --' ?></option>
                  </select>
                </div>
                <button type="button" class="btn btn-primary compact-btn" id="ug_addRoleBtn">
                  <i class="ri-add-line me-1"></i> <?= h(preg_replace('/^\+\s*/', '', __('userList_modal_add_role') ?? 'Peranan')) ?>
                </button>
              </div>
            </div>
            
            <!-- Access Selection -->
            <div class="mb-0">
              <label class="form-label">
                <i class="ri-shield-check-line"></i> <?= __('userList_modal_label_access') ?>
              </label>
              <select class="form-select compact-select" id="ug_flag">
                <option value="1"><?= __('userList_access_granted') ?></option>
                <option value="0"><?= __('userList_access_blocked') ?></option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= __('userList_modal_btn_close') ?>
        </button>
        <button type="button" class="btn btn-primary" id="ug_saveBtn">
          <i class="ri-save-3-line me-1"></i> <?= __('userList_modal_btn_save') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Peranan Tambahan -->
<div class="modal fade" id="roleExtraModal" tabindex="-1" aria-hidden="true" aria-labelledby="roleExtraTitle" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roleExtraTitle">
          <i class="ri-shield-user-line me-1"></i> <?= __('userList_modal_extra_role_title') ?? 'Peranan Tambahan' ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userList_modal_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <form id="roleExtraForm" autocomplete="off">
          <input type="hidden" id="re_userID" value="">
          <div class="text-muted small mb-2">
            <?= h(__('userList_primary_role_label') ?? 'Peranan Utama') ?>:
            <strong id="re_primaryRole">-</strong>
          </div>
          <div class="role-list" id="roleExtraList"></div>
        </form>
        <div id="roleExtraError" class="modal-error alert alert-danger d-none mt-3"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= __('userList_modal_btn_cancel') ?>
        </button>
        <button class="btn btn-primary" id="roleExtraSaveBtn">
          <i class="ri-save-3-line me-1"></i> <?= __('userList_modal_btn_save') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Tambah Pengguna -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true" aria-labelledby="addUserModalTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalTitle">
          <i class="ri-user-add-line me-2"></i> <?= __('userList_modal_add_title') ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userList_modal_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <form id="addUserForm" autocomplete="off">
          <!-- Section 1: Pilih Staf -->
          <div class="form-section">
            <div class="form-section-title">
              <i class="ri-user-line me-1"></i> <?= __('userList_modal_section_staff_info') ?>
            </div>
            <div class="mb-3">
              <label for="au_stafSelect" class="form-label">
                <i class="ri-user-line"></i> <?= __('userList_modal_label_staff') ?> <span class="text-danger">*</span>
              </label>
              <select class="form-select js-staf-select" id="au_stafSelect" data-placeholder="<?= h(__('userList_modal_placeholder_select_staff')) ?>">
                <option value=""></option>
                <?php if (!empty($senaraiStaf)): ?>
                  <?php foreach ($senaraiStaf as $s): ?>
                    <?php
                      $nopekerja = trim((string)($s['nopekerja'] ?? ''));
                      $idpekerja = trim((string)($s['idpekerja'] ?? ''));
                      $nama = trim((string)($s['nama'] ?? ''));
                      $jawatan = trim((string)($s['jawatan'] ?? ''));
                      $jabatan = trim((string)($s['jabatan'] ?? ''));
                      
                      if ($nopekerja === '') continue;
                      
                      $nopekerjaNormalized = str_replace('-', '', $nopekerja);
                      $isDisabled = in_array($nopekerjaNormalized, $existingStafIDs, true);
                      
                      $displayText = $nama;
                      if ($nopekerja) {
                        $displayText .= ' (' . $nopekerja . ')';
                      }
                      if ($isDisabled) {
                        $displayText .= ' [' . __('userList_staff_already_exists') . ']';
                      }
                    ?>
                    <option
                      value="<?= h($nopekerja) ?>"
                      data-idpekerja="<?= h($idpekerja) ?>"
                      data-nama="<?= h($nama) ?>"
                      data-jawatan="<?= h($jawatan) ?>"
                      data-jabatan="<?= h($jabatan) ?>"
                      <?= $isDisabled ? 'disabled' : '' ?>
                    >
                      <?= h($displayText) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
            
            <!-- Info Card (Jabatan & Jawatan) -->
            <div id="au_infoCard" class="info-card" style="display: block;">
              <div class="info-item">
                <i class="ri-building-line info-icon"></i>
                <div class="info-content">
                  <div class="info-label"><?= __('userList_modal_label_department') ?></div>
                  <div class="info-value" id="au_jabatan">-</div>
                </div>
              </div>
              <div class="info-item">
                <i class="ri-briefcase-line info-icon"></i>
                <div class="info-content">
                  <div class="info-label"><?= __('userList_modal_label_position') ?></div>
                  <div class="info-value" id="au_jawatan">-</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Section 2: Tetapan Pengguna -->
          <div class="form-section">
            <div class="form-section-title">
              <i class="ri-settings-3-line me-1"></i> <?= __('userList_modal_section_settings') ?>
            </div>
            <div class="mb-3">
              <label for="au_groupKod" class="form-label">
                <i class="ri-group-line"></i> <?= __('userList_modal_label_group') ?> <span class="text-danger">*</span>
              </label>
              <select class="form-select big-select" id="au_groupKod" required>
                <option value=""><?= __('userList_modal_placeholder_select_group') ?></option>
                <?php foreach ($senaraiGroup as $g): ?>
                  <option value="<?= h((string)($g['f_groupID'] ?? '')) ?>"><?= h($g['f_groupName']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="mb-0">
              <label for="au_flag" class="form-label">
                <i class="ri-shield-check-line"></i> <?= __('userList_modal_label_access') ?> <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="au_flag" required>
                <option value="1"><?= __('userList_access_granted') ?></option>
                <option value="0"><?= __('userList_access_blocked') ?></option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= __('userList_modal_btn_cancel') ?>
        </button>
        <button type="button" class="btn btn-success" id="au_saveBtn">
          <i class="ri-save-3-line me-1"></i> <?= __('userList_modal_btn_save') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  if (!window.bootstrap || !bootstrap.Modal) {
    return;
  }
  const hasDT = () => !!(window.jQuery && jQuery.fn && jQuery.fn.DataTable);
  const CSRF  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const table = document.getElementById('userDT');

  // ==================== CONFIGURATION CONSTANTS ====================
  const CONFIG = {
    HIGHLIGHT_DURATION: 20000,        // 20 seconds
    ANIMATION_DELAY: 300,             // 300ms
    SELECT2_RETRY_DELAY: 50,         // 50ms
    SELECT2_MAX_RETRIES: 100,        // Max 5 seconds
    RATE_LIMIT_DELAY: 1000,          // 1 second between requests
    RETRY_MAX_ATTEMPTS: 3,            // Max retry attempts
    RETRY_BASE_DELAY: 1000,           // Base delay for exponential backoff
    DEBUG: false,                     // Debug mode
    GROUP_UI_BY_ID: <?= json_encode($groupUiById, JSON_UNESCAPED_UNICODE) ?>,
    GROUP_UI_BY_CODE: <?= json_encode($groupUiByCode, JSON_UNESCAPED_UNICODE) ?>,
    COLORS: {
      GROUP_ADM_SA: '#ffe8e8',
      GROUP_ADM_HR: '#fffef0',
      HIGHLIGHT_SUCCESS: '#d4edda'
    }
  };

  // Global variable untuk DataTable instance
  let dtInstance = null;
  
  // Request cancellation controller
  let currentRequestController = null;
  
  // Rate limiting tracker
  const rateLimitTracker = new Map();

  // Permission check
  const currentUserGroup = '<?= h($currentUserGroup) ?>';
  const isSuperAdmin = <?= $isSuperAdmin ? 'true' : 'false' ?>;

  // ==================== HELPER FUNCTIONS ====================
  
  /**
   * Sanitize error messages untuk prevent exposing system details
   */
  function sanitizeError(error) {
    if (!error) return '<?= h(__('userList_err_unknown')) ?>';
    const msg = error.message || error.toString() || '<?= h(__('userList_err_unknown')) ?>';
    // Remove technical details
    return msg
      .replace(/in \/.*?\.php:\d+/g, '')
      .replace(/SQLSTATE\[.*?\]/g, '')
      .replace(/PDOException:/g, '')
      .replace(/Exception:/g, '')
      .substring(0, 200); // Limit length
  }

  /**
   * Check permission dengan user-friendly error
   */
  /**
   * Rate limiting untuk prevent spam clicks
   */
  function checkRateLimit(key, delay = CONFIG.RATE_LIMIT_DELAY) {
    const now = Date.now();
    const lastCall = rateLimitTracker.get(key) || 0;
    
    if (now - lastCall < delay) {
      return false;
    }
    
    rateLimitTracker.set(key, now);
    return true;
  }

  /**
   * Create rate-limited handler
   */
  function createRateLimitedHandler(handler, delay = CONFIG.RATE_LIMIT_DELAY) {
    return async function(...args) {
      const handlerKey = handler.name || 'anonymous';
      if (!checkRateLimit(handlerKey, delay)) {
        await Swal.fire({
          icon: 'warning',
          title: '<?= h(__('userList_rate_limit_title')) ?>',
          text: '<?= h(__('userList_rate_limit_text')) ?>',
          timer: 2000,
          timerProgressBar: true,
          confirmButtonText: '<?= h(__('userList_btn_ok')) ?>'
        });
        return;
      }
      return handler.apply(this, args);
    };
  }

  /**
   * Input validation functions
   */
  function validateStafID(stafID) {
    if (!stafID || stafID.trim() === '') return false;
    // Format: XXXX-XX atau 6 digits
    const normalized = stafID.replace(/-/g, '');
    return /^\d{6}$/.test(normalized);
  }

  function validateGroupId(groupId) {
    if (groupId === null || groupId === undefined) return false;
    const n = parseInt(String(groupId), 10);
    return Number.isFinite(n) && n > 0;
  }

  /**
   * Fetch with retry mechanism (exponential backoff)
   */
  async function fetchWithRetry(url, options = {}, maxRetries = CONFIG.RETRY_MAX_ATTEMPTS) {
    for (let i = 0; i < maxRetries; i++) {
      try {
        const response = await fetch(url, options);
        if (response.ok) return response;
        
        // Retry on 5xx errors only
        if (i < maxRetries - 1 && response.status >= 500) {
          const delay = Math.pow(2, i) * CONFIG.RETRY_BASE_DELAY; // 1s, 2s, 4s
          await new Promise(resolve => setTimeout(resolve, delay));
          continue;
        }
        
        throw new Error(`HTTP ${response.status}`);
      } catch (e) {
        if (i === maxRetries - 1) throw e;
        // Network errors - retry with backoff
        if (e.name !== 'AbortError') {
          const delay = Math.pow(2, i) * CONFIG.RETRY_BASE_DELAY;
          await new Promise(resolve => setTimeout(resolve, delay));
        } else {
          throw e; // Don't retry aborted requests
        }
      }
    }
  }

  /**
   * Loading overlay management
   */
  function showLoading(message = '<?= h(__('userList_processing')) ?>') {
    hideLoading(); // Remove existing if any
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.className = 'loading-overlay';
    overlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    `;
    overlay.innerHTML = `
      <div class="loading-spinner text-center" style="background: white; padding: 2rem; border-radius: 0.5rem;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden"><?= h(__('userList_loading')) ?></span>
        </div>
        <p class="mt-3 mb-0">${message}</p>
      </div>
    `;
    document.body.appendChild(overlay);
  }

  function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.remove();
  }

  // Select2 loading is handled inline where needed; remove unused helper to keep bundle small.

  /**
   * Get badge class berdasarkan group ID
   */
  function normalizeGroupCode(code) {
    return String(code || '').toUpperCase().replace(/[^A-Z0-9]+/g, '');
  }

  function getGroupStyle(groupId, groupKod = '') {
    const idKey = String(parseInt(groupId || 0, 10) || 0);
    const codeKey = normalizeGroupCode(groupKod);
    const style = CONFIG.GROUP_UI_BY_ID[idKey] || (codeKey !== '' ? CONFIG.GROUP_UI_BY_CODE[codeKey] : null) || {};
    return {
      badgeClass: String(style.badgeClass || 'bg-secondary').trim() || 'bg-secondary',
      rowClass: String(style.rowClass || '').trim(),
      rowColor: String(style.rowColor || '').trim()
    };
  }

  function getBadgeClass(groupId, groupKod = '') {
    return getGroupStyle(groupId, groupKod).badgeClass;
  }

  function getBadgeInlineStyle(groupId, groupKod = '') {
    const style = getGroupStyle(groupId, groupKod);
    if (style.badgeClass && style.badgeClass !== 'bg-secondary') return '';
    if (!style.rowColor) return '';
    return `background-color:${style.rowColor};color:#fff;`;
  }

  /**
   * Get row class berdasarkan group ID
   */
  function getRowClass(groupId, groupKod = '') {
    return getGroupStyle(groupId, groupKod).rowClass;
  }

  function isValidCssColor(value) {
    const v = String(value || '').trim();
    if (!v) return false;
    return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(v) || /^[a-zA-Z]+$/.test(v);
  }

  function toSoftRowBg(color) {
    const v = String(color || '').trim();
    const m3 = /^#([0-9a-f]{3})$/i.exec(v);
    if (m3) {
      const h = m3[1];
      const r = parseInt(h[0] + h[0], 16);
      const g = parseInt(h[1] + h[1], 16);
      const b = parseInt(h[2] + h[2], 16);
      return `rgba(${r}, ${g}, ${b}, 0.18)`;
    }
    const m6 = /^#([0-9a-f]{6})$/i.exec(v);
    if (m6) {
      const h = m6[1];
      const r = parseInt(h.slice(0, 2), 16);
      const g = parseInt(h.slice(2, 4), 16);
      const b = parseInt(h.slice(4, 6), 16);
      return `rgba(${r}, ${g}, ${b}, 0.18)`;
    }
    return v;
  }

  function applyRowClass($row) {
    const groupId = parseInt($row.attr('data-group-id') || '0', 10);
    const groupKod = String($row.attr('data-group-kod') || '');
    const mapStyle = getGroupStyle(groupId, groupKod);
    const nextClass = String(mapStyle.rowClass || '').trim();
    const nextColor = String($row.attr('data-row-color') || mapStyle.rowColor || '').trim();
    const oldClass = String($row.attr('data-row-class') || '').trim();
    if (oldClass) {
      $row.removeClass(oldClass);
    }
    const finalClass = nextClass || oldClass;
    if (finalClass) $row.addClass(finalClass);
    if (isValidCssColor(nextColor)) {
      const bg = toSoftRowBg(nextColor);
      const trEl = $row.get(0);
      if (trEl && trEl.style && typeof trEl.style.setProperty === 'function') {
        trEl.style.setProperty('background-color', bg, 'important');
      }
      $row.find('td').each(function() {
        if (this && this.style && typeof this.style.setProperty === 'function') {
          this.style.setProperty('background-color', bg, 'important');
          this.style.setProperty('background-image', 'linear-gradient(rgba(255,255,255,.58), rgba(255,255,255,.58))', 'important');
        }
      });
    } else {
      const trEl = $row.get(0);
      if (trEl && trEl.style) {
        trEl.style.removeProperty('background-color');
      }
      $row.find('td').each(function() {
        if (this && this.style) {
          this.style.removeProperty('background-color');
          this.style.removeProperty('background-image');
        }
      });
    }
    $row.attr('data-row-class', finalClass);
  }

  /**
   * Render extra roles tooltip on info icon
   */
  function renderExtraRolesInfo(iconEl, roles) {
    if (!iconEl) return;
    const list = Array.isArray(roles) ? roles : [];
    const title = list.length ? list.join(', ') : '<?= h(__('userList_role_none') ?? 'Tiada peranan tambahan.') ?>';
    iconEl.setAttribute('data-bs-toggle', 'tooltip');
    iconEl.setAttribute('data-bs-placement', 'top');
    iconEl.setAttribute('title', title);
  }

  /**
   * Init tooltips safely
   */
  function initTooltips(root = document) {
    if (!window.bootstrap || !bootstrap.Tooltip) return;
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      try { bootstrap.Tooltip.getInstance(el)?.dispose(); } catch(e) {}
      new bootstrap.Tooltip(el);
    });
  }

  /**
   * Track event untuk analytics/debugging
   */
  function trackEvent(eventName, data = {}) {
    if (CONFIG.DEBUG) {
      console.log('[Event]', eventName, data);
    }
    // Send to server for audit (optional, non-blocking)
    try {
      fetch('<?= base_url('ajax/track-event.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          event: eventName, 
          data, 
          timestamp: Date.now(),
          userGroup: currentUserGroup
        })
      }).catch(() => {}); // Ignore errors
    } catch (e) {
      // Ignore tracking errors
    }
  }

  /**
   * Update user row in-place (optimized)
   */
  function updateUserRow(userID, newData) {
    const $row = $(`#userDT tbody tr[data-user-id="${userID}"]`);
    if ($row.length === 0) {
      // Row not visible, trigger full reload
      return reloadUserTable(userID);
    }
    
    // Update row attributes
    if (newData.groupID) {
      $row.attr('data-group-id', newData.groupID);
      const rowGroupKod = (newData.groupKod !== undefined) ? newData.groupKod : $row.attr('data-group-kod');
      $row.attr('data-group-kod', rowGroupKod || '');
      const style = getGroupStyle(newData.groupID, rowGroupKod || '');
      $row.attr('data-row-color', style.rowColor || '');
      applyRowClass($row);
    }
    if (newData.groupKod) {
      $row.attr('data-group-kod', newData.groupKod);
      const style = getGroupStyle(newData.groupID || $row.attr('data-group-id'), newData.groupKod);
      $row.attr('data-row-color', style.rowColor || $row.attr('data-row-color') || '');
      applyRowClass($row);
    }
    if (Array.isArray(newData.extraRoles)) {
      const listText = newData.extraRoles.join(', ');
      $row.attr('data-extra-roles', listText);
      $row.attr('data-extra-count', String(newData.extraRoles.length));
      const $info = $row.find('.extra-roles-info');
      renderExtraRolesInfo($info[0], newData.extraRoles);
      initTooltips($info[0] || document);
    }
    if (newData.flag !== undefined) {
      $row.attr('data-flag', newData.flag);
    }
    
    // Update group badge
    if (newData.groupID && newData.groupName) {
      const $badge = $row.find('.col-group .badge');
      const groupKod = newData.groupKod || $row.attr('data-group-kod') || '';
      const badgeClass = getBadgeClass(newData.groupID, groupKod);
      const badgeInlineStyle = getBadgeInlineStyle(newData.groupID, groupKod);
      $badge.attr('class', `badge ${badgeClass}`).text(newData.groupName);
      if (badgeInlineStyle) {
        $badge.attr('style', badgeInlineStyle);
      } else {
        $badge.removeAttr('style');
      }
    }
    
    // Update access badge
    if (newData.flag !== undefined) {
      const $accessBadge = $row.find('.col-akses .badge');
      if (newData.flag == 1) {
        $accessBadge
          .removeClass('bg-danger')
          .addClass('bg-success')
          .text('<?= h(__('userList_access_granted')) ?>');
      } else {
        $accessBadge
          .removeClass('bg-success')
          .addClass('bg-danger')
          .text('<?= h(__('userList_access_blocked')) ?>');
      }
    }
    
    // Update button data attributes if needed
    if (isSuperAdmin) {
      if (newData.groupID) {
        $row.find('.btn-edit-group').attr('data-group-id', newData.groupID);
      }
      if (newData.groupKod) {
        $row.find('.btn-edit-group').attr('data-group-kod', newData.groupKod);
        if (newData.groupName) {
          $row.find('.btn-edit-group').attr('data-group-name', newData.groupName);
        }
      }
    }
    
    // Highlight row
    $row.addClass('row-updated-highlight');
    setTimeout(() => {
      $row.removeClass('row-updated-highlight');
    }, CONFIG.HIGHLIGHT_DURATION);
    
    // Scroll to row if not visible
    const rowOffset = $row.offset();
    if (rowOffset) {
      const windowTop = $(window).scrollTop();
      const windowBottom = windowTop + $(window).height();
      const rowTop = rowOffset.top;
      const rowBottom = rowTop + $row.outerHeight();
      
      if (rowTop < windowTop || rowBottom > windowBottom) {
        $('html, body').animate({
          scrollTop: rowTop - 100
        }, 500);
      }
    }
  }

  /**
   * Build a <tr> DOM node from a structured row object returned by server.
   * This avoids injecting raw HTML from server and improves XSS safety.
   */
  function buildRowFromData(r) {
    // Normalise possible server keys
    const userID = String(r.f_userID || r.userID || r.id || '');
    const nama = String(r.f_nama || r.nama || r.name || '');
    const stafID = String(r.f_stafID || r.stafID || r.staf_id || '');
    const jabatan = String(r.f_namajabatan || r.jabatan || r.department || '');
    const jawatan = String(r.f_jawatan || r.jawatan || r.position || '');
    const gId  = parseInt(r.f_groupID || r.groupID || r.group_id || 0, 10);
    const gKod = String(r.f_groupKod || r.groupKod || r.group_kod || r.group || '');
    const gName = String(r.f_groupName || r.groupName || r.group_name || gKod);
    const explicitBadgeClass = String(r.f_badge_class || r.badgeClass || '').trim();
    const explicitRowClass = String(r.f_row_class || r.rowClass || '').trim();
    const explicitRowColor = String(r.f_row_color || r.rowColor || '').trim();
    const extraRoles = Array.isArray(r.extra_roles) ? r.extra_roles : (Array.isArray(r.extraRoles) ? r.extraRoles : []);
    const flag = (typeof r.f_flag !== 'undefined') ? r.f_flag : (typeof r.flag !== 'undefined' ? r.flag : 0);
    const nopekerja = String(r.f_nopekerja || r.nopekerja || '');
    const avatarUrl = String(r.avatarUrl || r.avatar || '');

    // Create row element using jQuery to avoid unsafe innerHTML with server HTML
    const $tr = $('<tr>')
      .attr('data-user-id', userID)
      .attr('data-group-id', String(gId || ''))
      .attr('data-group-kod', gKod)
      .attr('data-row-color', explicitRowColor || getGroupStyle(gId, gKod).rowColor || '')
      .attr('data-row-class', explicitRowClass || getRowClass(gId, gKod))
      .attr('data-flag', String(flag))
      .attr('data-extra-count', String(extraRoles.length))
      .attr('data-extra-roles', extraRoles.join(', '))
      .addClass(explicitRowClass || getRowClass(gId, gKod));

    // Column: bil (filled by DataTable rowCallback)
    $tr.append($('<td>').addClass('col-bil'));

    // Column: nama (with stafID)
    const nameText = nama + (stafID ? (' (' + stafID + ')') : '');
    $tr.append($('<td>').addClass('col-nama').append($('<span>').addClass('truncate-1line').text(nameText)));

    // Column: jabatan
    $tr.append($('<td>').addClass('col-jabatan').append($('<span>').addClass('truncate-1line').text(jabatan)));

    // Column: jawatan
    $tr.append($('<td>').addClass('col-jawatan').append($('<span>').addClass('truncate-1line').text(jawatan)));

    // Column: group badge
    const $groupTd = $('<td>').addClass('col-group');
    const $badgeClass = explicitBadgeClass || getBadgeClass(gId, gKod);
    const $badge = $('<span>').addClass('badge').addClass($badgeClass).text(gName);
    const inlineBadgeStyle = explicitRowColor
      ? `background-color:${explicitRowColor};color:#fff;`
      : getBadgeInlineStyle(gId, gKod);
    if ((!explicitBadgeClass || explicitBadgeClass === 'bg-secondary') && inlineBadgeStyle) {
      $badge.attr('style', inlineBadgeStyle);
    }
    const $info = $('<i>')
      .addClass('ri-information-line ms-1 text-muted extra-roles-info')
      .attr('data-bs-toggle', 'tooltip')
      .attr('data-bs-placement', 'top');
    renderExtraRolesInfo($info[0], extraRoles);
    $groupTd.append($badge).append($info);
    $tr.append($groupTd);

    // Column: akses badge
    const $aksesTd = $('<td>').addClass('col-akses');
    const $aksesBadge = $('<span>').addClass('badge');
    if (parseInt(flag, 10) === 1) {
      $aksesBadge.addClass('bg-success').text('<?= h(__('userList_access_granted')) ?>');
    } else {
      $aksesBadge.addClass('bg-danger').text('<?= h(__('userList_access_blocked')) ?>');
    }
    $aksesTd.append($aksesBadge);
    $tr.append($aksesTd);

    // Column: actions
    const $actionsTd = $('<td>').addClass('col-actions');
    if (isSuperAdmin) {
      const $editBtn = $('<button>').attr('type','button').addClass('btn btn-outline-primary btn-sm icon-btn btn-edit-group')
        .attr('title', '<?= h(__('userList_action_change_group')) ?>')
        .attr('data-user-id', userID)
        .attr('data-nama', nama)
        .attr('data-stafid', stafID)
        .attr('data-nopekerja', nopekerja)
        .attr('data-avatar-url', avatarUrl)
        .attr('data-jabatan', jabatan)
        .attr('data-group-id', String(gId || ''))
        .attr('data-group-kod', gKod)
        .attr('data-group-name', gName)
        .attr('data-flag', String(flag))
        .html('<i class="ri-pencil-line"></i>');

      const $delBtn = $('<button>').attr('type','button').addClass('btn btn-outline-danger btn-sm icon-btn btn-delete-user ms-1')
        .attr('title', '<?= h(__('userList_action_delete_user')) ?>')
        .attr('data-user-id', userID)
        .attr('data-nama', nama)
        .attr('data-stafid', stafID)
        .html('<i class="ri-delete-bin-line"></i>');

      $actionsTd.append($editBtn).append($delBtn);
    }
    $tr.append($actionsTd);

    return $tr;
  }

  // Function untuk reload table via AJAX (tanpa refresh page)
  async function reloadUserTable(highlightUserID = null) {
    // Cancel previous request if exists
    if (currentRequestController) {
      currentRequestController.abort();
    }
    
    currentRequestController = new AbortController();
    
    showLoading('<?= __('loading_user_list') ?>');
    
    try {
      trackEvent('user_list_reload', { highlightUserID });
      
      const r = await fetchWithRetry('<?= base_url('ajax/user-list-rows.php') ?>', {
        headers: { 'Accept': 'application/json' },
        signal: currentRequestController.signal
      });
      
      if (!r.ok) {
        let errorText = 'HTTP ' + r.status;
        try {
          const errorData = await r.text();
          try {
            const errorJson = JSON.parse(errorData);
            errorText = errorJson.message || errorText;
          } catch (e) {
            errorText = errorData.substring(0, 200);
          }
        } catch (e) {
          // Ignore
        }
        throw new Error(errorText);
      }
      
      const contentType = r.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        const text = await r.text();
        throw new Error('<?= h(__('userList_err_non_json')) ?>');
      }
      
      const j = await r.json();
      if (j.error) throw new Error(j.message || '<?= h(__('userList_err_load_data')) ?>');
      
      // Jika DataTable sudah wujud, update dengan destroy(false) dan re-init untuk maintain layout
      if ($.fn.DataTable.isDataTable('#userDT')) {
        // Ensure a global safe HTML setter is available
        if (typeof window.setSafeInnerHTML !== 'function') {
          window.setSafeInnerHTML = function(el, html) {
            if (!el) return;
            if (!html) { el.innerHTML = ''; return; }
            if (window.DOMPurify && typeof DOMPurify.sanitize === 'function') {
              el.innerHTML = DOMPurify.sanitize(html);
              return;
            }
            try {
              var doc = new DOMParser().parseFromString('<div>' + html + '</div>', 'text/html');
              doc.querySelectorAll('script').forEach(function(s){ s.remove(); });
              doc.querySelectorAll('*').forEach(function(n){
                Array.from(n.attributes).forEach(function(a){
                  if (/^on/i.test(a.name)) n.removeAttribute(a.name);
                  if ((a.name === 'src' || a.name === 'href') && /^javascript:/i.test(a.value)) n.removeAttribute(a.name);
                });
              });
              el.innerHTML = doc.body.firstChild ? doc.body.firstChild.innerHTML : '';
            } catch (e) {
              el.innerHTML = html;
            }
          };
        }

        const dt = $('#userDT').DataTable();
        
        // Preserve current state
        const currentPage = dt.page();
        const currentSearch = dt.search();
        const currentOrder = dt.order();
        const currentLength = dt.page.len();
        
        // Prefer structured rows if provided: j.rows is an array of objects
        let $newRows;
        if (Array.isArray(j.rows) && j.rows.length > 0) {
          // Build DOM rows from structured data (safer than injecting HTML)
          const nodes = j.rows.map(r => buildRowFromData(r).get(0));
          $newRows = $(nodes);
        } else {
          // Parse HTML rows (response may contain <tr> elements)
          const tempDiv = document.createElement('div');
          window.setSafeInnerHTML(tempDiv, j.html || '');
          $newRows = $(tempDiv).find('tr');
        }
        
        // Clear existing rows (tanpa destroy untuk maintain layout)
        dt.clear();
        
        // Add new rows - pastikan rows match dengan table structure
        if ($newRows.length > 0) {
          const rowsArray = [];
          $newRows.each(function() {
            const $row = $(this);
            // Pastikan row ada semua columns yang diperlukan (7 columns: bil, nama, jabatan, jawatan, group, akses, actions)
            const tdCount = $row.find('td').length;
            if (tdCount === 7) {
              rowsArray.push(this);
            }
          });
          
          if (rowsArray.length > 0) {
            try {
              dt.rows.add(rowsArray);
            } catch (e) {
              // Fallback: destroy dan re-init
              dt.destroy();
              const $tbody = $('#userDT tbody');
              if (Array.isArray(j.rows) && j.rows.length > 0) {
                // Render rows from structured data
                const nodes = j.rows.map(r => buildRowFromData(r).get(0));
                $tbody.html('');
                $tbody.append($(nodes));
              } else {
                // Use safe innerHTML setter to avoid XSS from server HTML fallback
                if (typeof window.setSafeInnerHTML === 'function') {
                  window.setSafeInnerHTML($tbody.get(0), j.html || '');
                } else {
                  $tbody.html(j.html || '');
                }
              }
              dtInstance = $('#userDT').DataTable({
                pageLength: currentLength || 10,
                lengthChange: true,
                lengthMenu: [10, 25, 50, 100, 200],
                ordering: true,
                order: currentOrder.length > 0 ? currentOrder : [[1,'asc']],
                autoWidth: false,
                scrollX: false,
                dom: '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>' +
                  't' +
                  '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
                // ✅ Pastikan length selector tidak wrap
                lengthMenu: [10, 25, 50, 100, 200],
                language: {
                  lengthMenu: "<?= h(__('userList_dt_length_menu')) ?>",
                  search: "",
                  info: "<?= h(__('userList_dt_info')) ?>",
                  infoEmpty: "<?= h(__('userList_dt_info_empty')) ?>",
                  paginate: { previous: "<?= h(__('userList_dt_paginate_prev')) ?>", next: "<?= h(__('userList_dt_paginate_next')) ?>"},
                  zeroRecords: "<?= h(__('userList_dt_zero_records')) ?>"
                },
                columnDefs: [
                  { targets: 0, orderable:false, searchable:false, width: 56 },
                  { targets: 6, orderable:false, searchable:false, width: 110 }
                ],
                rowCallback: function(row, data, displayIndex){
                  const api  = this.api();
                  const info = api.page.info();
                  $('td:eq(0)', row).text(info.start + displayIndex + 1);
                  
                  const $row = $(row);
                  applyRowClass($row);
                },
                initComplete: function() {
                  setupTableControls();
                  try {
                    const _lbl = <?= json_encode(h(__('userList_dt_search_label'))) ?>;
                    const _ph = String(_lbl).replace(/[:：\s]+$/, '').trim();
                    $('#userDT_filter input').attr('placeholder', _ph);
                  } catch(e) { /* ignore */ }
                }
              });
              dt = dtInstance;
              if (currentSearch) {
                dt.search(currentSearch);
              }
              if (currentLength) {
                dt.page.len(currentLength);
              }
              const pageInfo = dt.page.info();
              const targetPage = Math.min(currentPage, Math.max(0, pageInfo.pages - 1));
              if (targetPage >= 0 && targetPage < pageInfo.pages) {
                dt.page(targetPage);
              }
              dt.draw();
              return; // Exit early
            }
          }
        }
        
        // Restore state
        dt.order(currentOrder);
        dt.search(currentSearch);
        if (currentLength) {
          dt.page.len(currentLength);
        }
        
        // Restore page position
        const pageInfo = dt.page.info();
        const targetPage = Math.min(currentPage, Math.max(0, pageInfo.pages - 1));
        if (targetPage >= 0 && targetPage < pageInfo.pages) {
          dt.page(targetPage);
        }
        
        // Draw dengan false untuk avoid full redraw dan maintain layout
        dt.draw(false);
        
        // Update row numbers dan highlighting (tanpa trigger layout change)
        // Re-get pageInfo selepas draw untuk accurate row numbers
        const currentPageInfo = dt.page.info();
        dt.rows().every(function() {
          const row = this.node();
          const displayIndex = this.index();
          $('td:eq(0)', row).text(currentPageInfo.start + displayIndex + 1);
          
          const $row = $(row);
          applyRowClass($row);
        });
        
        // Highlight row jika ada userID yang perlu di-highlight
        if (highlightUserID) {
          setTimeout(() => {
            // Cari row di semua halaman (termasuk yang filtered)
            const $targetRow = $(`#userDT tbody tr[data-user-id="${highlightUserID}"]`);
            if ($targetRow.length > 0) {
              // Pastikan row visible (jika filtered, navigate ke page yang betul)
              const rowIndex = dt.rows({ search: 'applied' }).nodes().indexOf($targetRow[0]);
              if (rowIndex >= 0) {
                const pageInfo = dt.page.info();
                const targetPage = Math.floor(rowIndex / pageInfo.length);
                if (targetPage !== pageInfo.page) {
                  dt.page(targetPage).draw(false);
                }
              }
              
              // Add highlight class
              $targetRow.addClass('row-updated-highlight');
              
              // Remove highlight after configured duration
              setTimeout(() => {
                $targetRow.removeClass('row-updated-highlight');
              }, CONFIG.HIGHLIGHT_DURATION);
            }
          }, CONFIG.ANIMATION_DELAY);
        }
        
        // Update dtInstance reference
        dtInstance = dt;
        
        // Re-setup table controls
        setupTableControls();
        initTooltips(document);
        
        hideLoading();
        return;
      }
      
      // Fallback: jika DataTable belum wujud, init seperti biasa
      const $tbody = $('#userDT tbody');
      if (Array.isArray(j.rows) && j.rows.length > 0) {
        const nodes = j.rows.map(r => buildRowFromData(r).get(0));
        $tbody.html('');
        $tbody.append($(nodes));
      } else {
        window.setSafeInnerHTML($tbody.get(0), j.html || '');
      }
      
      dtInstance = $('#userDT').DataTable({
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [10, 25, 50, 100, 200],
        ordering: true,
        order: [[1,'asc']],
        autoWidth: false,
        scrollX: false,
        dom: '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>' +
          't' +
          '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
        language: {
          lengthMenu: "<?= h(__('userList_dt_length_menu')) ?>",
          search: "",
          info: "<?= h(__('userList_dt_info')) ?>",
          infoEmpty: "<?= h(__('userList_dt_info_empty')) ?>",
          paginate: { previous: "<?= h(__('userList_dt_paginate_prev')) ?>", next: "<?= h(__('userList_dt_paginate_next')) ?>"},
          zeroRecords: "<?= h(__('userList_dt_zero_records')) ?>"
        },
        columnDefs: [
          { targets: 0, orderable:false, searchable:false, width: 56 },
          { targets: 5, orderable:false, searchable:false, width: 110 }
        ],
        rowCallback: function(row, data, displayIndex){
          const api  = this.api();
          const info = api.page.info();
          $('td:eq(0)', row).text(info.start + displayIndex + 1);
        },
        initComplete: function() {
          setupTableControls();
          initTooltips(document);
          
          // Highlight row jika ada userID yang perlu di-highlight (fallback case)
          if (highlightUserID) {
            setTimeout(() => {
              const $targetRow = $(`#userDT tbody tr[data-user-id="${highlightUserID}"]`);
              if ($targetRow.length > 0) {
                // Scroll to row jika tidak visible
                const rowOffset = $targetRow.offset();
                if (rowOffset) {
                  $('html, body').animate({
                    scrollTop: rowOffset.top - 100
                  }, 500);
                }
                
                // Add highlight class
                $targetRow.addClass('row-updated-highlight');
                
                // Remove highlight after configured duration
                setTimeout(() => {
                  $targetRow.removeClass('row-updated-highlight');
                }, CONFIG.HIGHLIGHT_DURATION);
              }
            }, CONFIG.ANIMATION_DELAY);
          }
        }
      });
      
      hideLoading();
      
    } catch (e) {
      hideLoading();
      
      // Handle abort error gracefully
      if (e.name === 'AbortError') {
        console.log('Request cancelled');
        return;
      }
      
      // Show user-friendly error
      const errorMsg = sanitizeError(e);
      await Swal.fire({
        icon: 'error',
        title: '<?= h(__('userList_error_title')) ?>',
        text: errorMsg || '<?= h(__('userList_err_load_data')) ?>',
        confirmButtonText: '<?= h(__('userList_btn_ok')) ?>',
        confirmButtonColor: '#dc3545'
      });
      
      trackEvent('user_list_reload_error', { error: errorMsg });
      throw e;
    }
  }

  // Function untuk setup table controls (buttons, filters, etc)
  function setupTableControls() {
    // Styling
    // ✅ Removed form-select-sm untuk besarkan saiz dropdown
    $('#userDT_length select').addClass('form-select w-auto');
    $('#userDT_length label').addClass('mb-0');
    const $topLeft  = $('#userDT_wrapper .dt-top-left').addClass('d-flex align-items-center gap-2 flex-nowrap');
    const $topRight = $('#userDT_wrapper .dt-top-right').addClass('align-items-center gap-2 flex-nowrap');
    
    // Remove existing buttons jika ada
    $('#btnSyncSybase').remove();
    $('#btnAddUser').remove();
    $('#btnImportStudent').remove();    
    
    // Button Sync
    if (!document.getElementById('btnSyncSybase')) {
      const $syncBtn = $('<button type="button" id="btnSyncSybase" class="btn btn-primary">' +
          '<i class="ri-refresh-line me-1"></i> <?= h(__('userList_sync_button')) ?>' +
        '</button>');
      
      // Append button ke akhir topRight container (kanan sekali)
      if ($topRight.length) {
        $topRight.append($syncBtn);
      } else {
        // Fallback: append ke filter jika topRight tidak wujud
        const $filter = $('#userDT_filter');
        if ($filter.length) {
          $filter.append($syncBtn);
        }
      }
      
      $syncBtn.on('click', createRateLimitedHandler(async function(e){
        e.preventDefault();
        
          const $btn = $(this);
          const originalHtml = $btn.html();
          const originalDisabled = $btn.prop('disabled');
          
          $btn.prop('disabled', true);
          $btn.html('<i class="ri-loader-4-line ri-spin me-1"></i> <?= h(__('userList_sync_processing')) ?>');
          
          try {
            trackEvent('user_sync_sybase', {});
            
            const r = await fetchWithRetry('<?= base_url('ajax/user-sync-sybase.php') ?>', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF,
                'Accept': 'application/json'
              }
            });
            
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const j = await r.json();
            if (j.error) throw new Error(j.message || '<?= h(__('userList_sync_error')) ?>');
            
            trackEvent('user_sync_sybase_success', { updated: j.updated || 0 });
            
            await Swal.fire({
              icon: 'success',
              title: '<div class="d-flex align-items-center justify-content-center gap-2">' +
                '<i class="ri-checkbox-circle-line" style="font-size: 2rem; color: #198754;"></i>' +
                '<span><?= h(__('userList_sync_success_title')) ?></span>' +
                '</div>',
              html: '<div class="text-start" style="padding: 0.5rem 0;">' +
                '<div class="alert alert-success d-flex align-items-start mb-3" style="border-left: 4px solid #198754; background: #f0f9ff;">' +
                '<i class="ri-information-line me-2 mt-1" style="font-size: 1.25rem; color: #198754;"></i>' +
                '<div>' + (j.message || '<?= h(__('userList_sync_success_message')) ?>') + '</div>' +
                '</div>' +
                '<div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">' +
                '<div class="card-body p-3">' +
                '<h6 class="mb-3 fw-bold text-dark" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">' +
                '<i class="ri-bar-chart-line me-2"></i><?= h(__('userList_sync_summary_title')) ?>' +
                '</h6>' +
                '<div class="row g-2">' +
                '<div class="col-6">' +
                '<div class="d-flex align-items-center p-2 rounded" style="background: rgba(25, 135, 84, 0.1);">' +
                '<i class="ri-refresh-line me-2" style="font-size: 1.25rem; color: #198754;"></i>' +
                '<div class="flex-grow-1">' +
                '<div class="small text-muted"><?= h(__('userList_sync_updated')) ?></div>' +
                '<div class="fw-bold text-success" style="font-size: 1.1rem;">' + (j.updated || 0) + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col-6">' +
                '<div class="d-flex align-items-center p-2 rounded" style="background: rgba(255, 193, 7, 0.1);">' +
                '<i class="ri-skip-forward-line me-2" style="font-size: 1.25rem; color: #ffc107;"></i>' +
                '<div class="flex-grow-1">' +
                '<div class="small text-muted"><?= h(__('userList_sync_skipped')) ?></div>' +
                '<div class="fw-bold text-warning" style="font-size: 1.1rem;">' + (j.skipped || 0) + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col-6 mt-2">' +
                '<div class="d-flex align-items-center p-2 rounded" style="background: rgba(220, 53, 69, 0.1);">' +
                '<i class="ri-error-warning-line me-2" style="font-size: 1.25rem; color: #dc3545;"></i>' +
                '<div class="flex-grow-1">' +
                '<div class="small text-muted"><?= h(__('userList_sync_errors')) ?></div>' +
                '<div class="fw-bold text-danger" style="font-size: 1.1rem;">' + (j.errors || 0) + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col-6 mt-2">' +
                '<div class="d-flex align-items-center p-2 rounded" style="background: rgba(13, 110, 253, 0.1);">' +
                '<i class="ri-database-2-line me-2" style="font-size: 1.25rem; color: #0d6efd;"></i>' +
                '<div class="flex-grow-1">' +
                '<div class="small text-muted"><?= h(__('userList_sync_total')) ?></div>' +
                '<div class="fw-bold text-primary" style="font-size: 1.1rem;">' + (j.total || 0) + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>',
              confirmButtonText: '<i class="ri-check-line me-1"></i><?= h(__('userList_btn_ok')) ?>',
              confirmButtonColor: '#198754',
              buttonsStyling: true,
              allowOutsideClick: false,
              allowEscapeKey: false,
              showCloseButton: false,
              width: '500px',
              customClass: {
                popup: 'swal2-popup-custom',
                title: 'swal2-title-custom',
                confirmButton: 'swal2-confirm-custom'
              }
            });
            
            await reloadUserTable();
          } catch (e) {
            const errorMsg = sanitizeError(e);
            trackEvent('user_sync_sybase_error', { error: errorMsg });
            
            await Swal.fire({
              icon: 'error',
              title: '<?= h(__('userList_sync_error_title')) ?>',
              text: errorMsg || '<?= h(__('userList_sync_error')) ?>',
              confirmButtonText: '<?= h(__('userList_btn_ok')) ?>',
              confirmButtonColor: '#dc3545'
            });
          } finally {
            $btn.prop('disabled', originalDisabled);
            $btn.html(originalHtml);
          }
      }, 2000));
    }
    
    // Button Tambah Pengguna (Super Admin sahaja)
    if (isSuperAdmin && !document.getElementById('btnAddUser')) {
      const $addBtn = $('<button type="button" id="btnAddUser" class="btn btn-success">' +
          '<i class="ri-user-add-line me-1"></i> <?= h(__('userList_add_button')) ?>' +
        '</button>');
      
      // Append button ke akhir topRight container (kanan sekali, selepas btnImportStudent jika ada)
      if ($topRight.length) {
        if (document.getElementById('btnImportStudent')) {
          $('#btnImportStudent').after($addBtn);
        } else {
          $topRight.append($addBtn);
        }
      } else {
        // Fallback: append ke filter jika topRight tidak wujud
        const $filter = $('#userDT_filter');
        if ($filter.length) {
          if (document.getElementById('btnImportStudent')) {
            $('#btnImportStudent').after($addBtn);
          } else {
            $filter.append($addBtn);
          }
        }
      }
      
      $addBtn.on('click', function(e){
        e.preventDefault();
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addUserModal'));
        modal.show();
      });
    }
    // Ensure search input has placeholder from translation (strip trailing colon)
    try {
      const _lbl = <?= json_encode(h(__('userList_dt_search_label'))) ?>;
      const _ph = String(_lbl).replace(/[:：\s]+$/, '').trim();
      const $inp = $('#userDT_filter input');
      if ($inp.length) $inp.attr('placeholder', _ph);
    } catch(e) { /* ignore */ }
  }

  // Helper: auto-size select ikut teks option yang terpilih
  function fitSelectWidth(sel){
    if (!sel) return;
    sel.style.width = 'auto';
    const span = document.createElement('span');
    span.style.visibility = 'hidden';
    span.style.position   = 'fixed';
    span.style.whiteSpace = 'pre';
    const cs = window.getComputedStyle(sel);
    span.style.font = cs.font || `${cs.fontSize} ${cs.fontFamily}`;
    span.style.fontSize   = cs.fontSize;
    span.style.fontFamily = cs.fontFamily;
    span.textContent = sel.options[sel.selectedIndex]?.text || sel.value || '';
    document.body.appendChild(span);
    const padX = 28;
    const w = Math.ceil(span.getBoundingClientRect().width) + padX;
    document.body.removeChild(span);
    sel.style.width = w + 'px';
  }

  document.addEventListener('DOMContentLoaded', function(){
    if (!hasDT()) { return; }

    // Re-init guard
    if ($.fn.DataTable.isDataTable('#userDT')) {
      $('#userDT').DataTable().destroy();
    }

    const dt = $('#userDT').DataTable({
      pageLength: 10,
      lengthChange: true,
      lengthMenu: [10, 25, 50, 100, 200],
      ordering: true,
      order: [[1,'asc']],                 // ikut kolum Nama (StafID)
      autoWidth: false,
      scrollX: false,
      dom:
        '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>' +
        't' +
        '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
      language: {
        lengthMenu: "<?= h(__('userList_dt_length_menu')) ?>",
        search: "",
        info: "<?= h(__('userList_dt_info')) ?>",
        infoEmpty: "<?= h(__('userList_dt_info_empty')) ?>",
        paginate: { previous: "<?= h(__('userList_dt_paginate_prev')) ?>", next: "<?= h(__('userList_dt_paginate_next')) ?>"},
        zeroRecords: "<?= h(__('userList_dt_zero_records')) ?>"
      },
      columnDefs: [
        { targets: 0, orderable:false, searchable:false, width: 56 },  // Bil
        { targets: 6, orderable:false, searchable:false, width: 110 }  // Tindakan (ikon)
      ],
        initComplete: function() {
          try {
            const _lbl = <?= json_encode(h(__('userList_dt_search_label'))) ?>;
            const _ph = String(_lbl).replace(/[:：\s]+$/, '').trim();
            $('#userDT_filter input').attr('placeholder', _ph);
          } catch(e) { /* ignore */ }
        },
      rowCallback: function(row, data, displayIndex){
        const api  = this.api();
        const info = api.page.info();
        $('td:eq(0)', row).text(info.start + displayIndex + 1);
        
        // Apply row highlighting based on group (if not already applied from server-side)
        const $row = $(row);
        applyRowClass($row);
      },
      initComplete: function() {
        setupTableControls();
      }
    });
    
    // Set dtInstance untuk digunakan dalam functions lain
    dtInstance = dt;

    // === Styling & susun kiri/kanan (sebaris, tak berbalut) ===
    // ✅ Removed form-select-sm untuk besarkan saiz dropdown
    $('#userDT_length select')
      .addClass('form-select w-auto');

    $('#userDT_length label').addClass('mb-0');
    const $topLeft  = $('#userDT_wrapper .dt-top-left').addClass('d-flex align-items-center gap-2 flex-nowrap');
    const $topRight = $('#userDT_wrapper .dt-top-right').addClass('align-items-center gap-2 flex-nowrap');

    // === Dropdown Filter Kumpulan (auto width) — duduk di sebelah carian, sebelum button ===
    const $grp = $(`
      <select id="dtGroupFilter" class="form-select">
        <option value=""><?= h(__('userList_group_filter_placeholder')) ?></option>
      </select>
    `);
    // Append ke topRight selepas search box tapi sebelum button
    // Search box adalah #userDT_filter, jadi kita append selepas filter
    const $filter = $('#userDT_filter');
    if ($filter.length) {
      $filter.after($grp);
    } else {
      // Fallback: append ke topRight (akan duduk sebelum button kerana button di-append selepas)
      $topRight.append($grp);
    }

    // Ambil senarai kumpulan & populate option (guna ID untuk penapisan tepat)
    (async () => {
      try {
        const res = await fetch('<?= base_url('ajax/group-list.php') ?>', { headers: { 'Accept':'application/json' } });
        const j = await res.json();
        (j.groups || []).forEach(g => {
          const id = g.id || g.f_groupID || '';
          const name = g.nama || g.f_groupName || g.kod || g.f_groupKod || '';
          if (!id || !name) return;
          $grp.append(new Option(name, String(id)));
        });
      } catch (e) { }
      fitSelectWidth($grp[0]);
    })();

    // Helper: escape regex
    function escRx(s){ return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

    // Tapis ikut kumpulan berdasarkan data-group-id + auto-size bila berubah
    let groupFilterId = '';
    if (!window.__userDTGroupFilterAdded) {
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (!settings || settings.nTable?.id !== 'userDT') return true;
        if (!groupFilterId) return true;
        const rowNode = dt.row(dataIndex).node();
        const gid = rowNode ? (rowNode.getAttribute('data-group-id') || '') : '';
        return String(gid) === String(groupFilterId);
      });
      window.__userDTGroupFilterAdded = true;
    }
    $('#dtGroupFilter').on('change', function(){
      groupFilterId = this.value || '';
      dt.draw();
      fitSelectWidth(this);
    });

    // Resize semula bila window berubah (optional)
    window.addEventListener('resize', () => fitSelectWidth(document.getElementById('dtGroupFilter')));

    // Setup table controls (buttons, filters) - ini akan handle semua termasuk dropdown filter
    setupTableControls();

    // ===== Modal Tukar Kumpulan =====
    const modalEl = document.getElementById('userGroupModal');
    const modal   = modalEl ? new bootstrap.Modal(modalEl) : null;
    const errEl   = document.getElementById('ug_error');
    const roleModalEl = document.getElementById('roleExtraModal');
    let roleModal = roleModalEl ? new bootstrap.Modal(roleModalEl) : null;
    let currentPrimaryRoleName = '';
    const roleListEl = document.getElementById('roleExtraList');
    const roleErrEl = document.getElementById('roleExtraError');

    function showRoleErr(msg){ if(!roleErrEl) return; roleErrEl.textContent = msg || '<?= h(__('userList_err_unknown')) ?>'; roleErrEl.classList.remove('d-none'); }
    function hideRoleErr(){ if(!roleErrEl) return; roleErrEl.classList.add('d-none'); }

    function setRoleButton(count, list) {
      const btn = document.getElementById('ug_addRoleBtn');
      if (!btn) return;
      const label = '<?= h(__('userList_modal_add_role') ?? '+ Peranan') ?>';
      const cleanLabel = String(label).replace(/^\+\s*/, '').trim();
      const c = (typeof count === 'number') ? count : 0;
      btn.setAttribute('type', 'button');
      btn.innerHTML = `<i class="ri-add-line me-1"></i> ${cleanLabel} (${c})`;
      const title = Array.isArray(list) && list.length ? list.join(', ') : '<?= h(__('userList_role_none') ?? 'Tiada peranan tambahan.') ?>';
      btn.setAttribute('data-bs-toggle', 'tooltip');
      btn.setAttribute('data-bs-placement', 'top');
      btn.setAttribute('title', title);
      initTooltips(btn);
    }

    async function loadExtraRoles(userID){
      if (!roleListEl) return;
      roleListEl.innerHTML = '';
      try {
        const r = await fetch('<?= base_url('ajax/user-extra-roles.php') ?>', {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-Token': CSRF, 'Accept':'application/json'},
          body: JSON.stringify({ action: 'get', userID })
        });
        const j = await r.json();
        if (!r.ok || !j || j.error) throw new Error((j && j.message) || '<?= h(__('userList_err_load_data')) ?>');
        const roles = j.roles || [];
        if (!roles.length) {
          roleListEl.innerHTML = '<div class="text-muted"><?= h(__('userList_role_none') ?? 'Tiada peranan tambahan.') ?></div>';
          setRoleButton(0, []);
          return;
        }
        const checkedNames = [];
        roles.forEach(role => {
          const rid = role.id || role.f_groupID;
          const rname = role.name || role.f_groupName || '';
          const checked = role.checked ? 'checked' : '';
          if (role.checked) checkedNames.push(rname);
          const item = document.createElement('label');
          item.className = 'role-item';
          item.innerHTML = `
            <input type="checkbox" value="${rid}" ${checked}>
            <span class="role-label">${rname}</span>
          `;
          roleListEl.appendChild(item);
        });
        setRoleButton(checkedNames.length, checkedNames);
      } catch (e) {
        showRoleErr(e.message || '<?= h(__('userList_err_load_data')) ?>');
      }
    }

    function getPrimaryRoleNameFromSelect() {
      const sel = document.getElementById('ug_groupKod');
      if (!sel) return '';
      const opt = sel.selectedOptions && sel.selectedOptions[0] ? sel.selectedOptions[0] : null;
      if (!opt) return '';
      return (opt.textContent || '').trim();
    }

    function showErr(msg){ if(!errEl) return; errEl.textContent = msg || '<?= h(__('userList_err_unknown')) ?>'; errEl.classList.remove('d-none'); }
    function hideErr(){ if(!errEl) return; errEl.classList.add('d-none'); }

    async function populateGroups(selectedId){
      try{
        const r = await fetch('<?= base_url('ajax/group-list.php') ?>', { headers:{'Accept':'application/json'} });
        const j = await r.json();
        const sel = document.getElementById('ug_groupKod'); if (!sel) return;
        sel.innerHTML = '';
        (j.groups || []).forEach(g=>{
          const id   = g.id || g.f_groupID || '';
          const kod  = g.kod || g.f_groupKod || '';
          const name = g.nama || g.f_groupName || kod;
          const opt = document.createElement('option');
          opt.value = id; opt.textContent = name;
          if (selectedId && String(selectedId) === String(id)) opt.selected = true;
          sel.appendChild(opt);
        });
      }catch(e){ }
    }

    if (table){
      table.addEventListener('click', async function(e){
        // Handle delete button click
        const deleteBtn = e.target.closest('.btn-delete-user');
        if (deleteBtn) {
          e.preventDefault();
          if (!isSuperAdmin) {
            await Swal.fire({
              icon: 'info',
              title: '<?= h(__('userList_error_title')) ?>',
              text: '<?= h(__('userList_err_no_permission') ?? 'Anda tidak mempunyai kebenaran untuk melakukan tindakan ini.') ?>',
              confirmButtonText: '<?= h(__('userList_btn_ok')) ?>',
              confirmButtonColor: '#6c757d'
            });
            return;
          }
          
          // Rate limiting check
          if (!checkRateLimit('user_delete', 2000)) {
            await Swal.fire({
              icon: 'warning',
              title: '<?= h(__('userList_rate_limit_title')) ?>',
              text: '<?= h(__('userList_rate_limit_text')) ?>',
              timer: 2000,
              timerProgressBar: true,
              confirmButtonText: '<?= h(__('userList_btn_ok')) ?>'
            });
            return;
          }
          
          const userID = deleteBtn.getAttribute('data-user-id');
          const nama = deleteBtn.getAttribute('data-nama') || '<?= h(__('userList_user_default')) ?>';
          const stafID = deleteBtn.getAttribute('data-stafid') || '';
          
          // Confirmation dialog
          const result = await Swal.fire({
            icon: 'warning',
            title: '<?= h(__('userList_delete_confirm_title')) ?>',
            html: `<p><?= h(__('userList_delete_confirm_message')) ?></p>
                   <p><strong>${nama}</strong> (${stafID})</p>
                   <p class="text-danger"><small><?= h(__('userList_delete_confirm_warning')) ?></small></p>`,
            showCancelButton: true,
            confirmButtonText: '<?= h(__('userList_delete_confirm_yes')) ?>',
            cancelButtonText: '<?= h(__('userList_modal_btn_cancel')) ?>',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
          });
          
          if (!result.isConfirmed) return;
          
          trackEvent('user_delete', { userID, nama, stafID });
          
          // Disable button during request
          deleteBtn.disabled = true;
          const originalHTML = deleteBtn.innerHTML;
          deleteBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';
          
          try {
            const r = await fetchWithRetry('<?= base_url('ajax/user-delete.php') ?>', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                userID: userID,
                csrf_token: CSRF
              })
            });
            
            // Read response once
            let responseText = '';
            let j = null;
            
            try {
              responseText = await r.text();
              j = JSON.parse(responseText);
            } catch (e) {
              throw new Error(`<?= h(__('userList_err_invalid_response')) ?> (${r.status}).`);
            }
            
            if (!r.ok) {
              let errorMsg = '<?= h(__('userList_err_delete_failed')) ?>';
              if (j && j.message) {
                errorMsg = j.message;
              } else {
                errorMsg = `HTTP ${r.status}: ${r.statusText || '<?= h(__('userList_err_server')) ?>'}`;
              }
              throw new Error(errorMsg);
            }
            
            if (!j || j.error) {
              throw new Error((j && j.message) || '<?= h(__('userList_err_delete_failed')) ?>');
            }
            
            trackEvent('user_delete_success', { userID });
            
            // Reload table
            await reloadUserTable();
            
            // Re-setup table controls (buttons, filters, etc) after reload
            setupTableControls();
            
            // Refresh dropdown staf dalam add user modal (untuk update disabled status)
            await refreshStafDropdown();
            
            // Show success message
            await Swal.fire({
              icon: 'success',
              title: '<?= h(__('userList_success_title')) ?>',
              text: (j.message || '<?= h(__('userList_success_delete')) ?>'),
              confirmButtonText: '<?= h(__('userList_btn_ok')) ?>',
              confirmButtonColor: '#28a745',
              timer: 2000,
              timerProgressBar: true
            });
          } catch (e) {
            const errorMsg = sanitizeError(e);
            trackEvent('user_delete_error', { userID, error: errorMsg });
            
            await Swal.fire({
              icon: 'error',
              title: '<?= h(__('userList_error_title')) ?>',
              text: errorMsg || '<?= h(__('userList_err_delete_failed')) ?>',
              confirmButtonText: '<?= h(__('userList_btn_ok')) ?>',
              confirmButtonColor: '#dc3545'
            });
          } finally {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalHTML;
          }
          
          return;
        }
        
        // Handle edit button click
        const btn = e.target.closest('.btn-edit-group'); 
        if (!btn || !modal) return;
        if (!isSuperAdmin) {
          await Swal.fire({
            icon: 'info',
            title: '<?= h(__('userList_error_title')) ?>',
            text: '<?= h(__('userList_err_no_permission') ?? 'Anda tidak mempunyai kebenaran untuk melakukan tindakan ini.') ?>',
            confirmButtonText: '<?= h(__('userList_btn_ok')) ?>',
            confirmButtonColor: '#6c757d'
          });
          return;
        }

        hideErr();
        const userID  = btn.getAttribute('data-user-id');
        const nama    = btn.getAttribute('data-nama') || '-';
        const stafid  = btn.getAttribute('data-stafid') || '-';
        const nopekerja = btn.getAttribute('data-nopekerja') || '';
        const avatarUrl = btn.getAttribute('data-avatar-url') || '';
        const jabatan = btn.getAttribute('data-jabatan') || '-';
        const gId     = btn.getAttribute('data-group-id') || '';
        const gKod    = btn.getAttribute('data-group-kod') || '';
        const flag    = btn.getAttribute('data-flag') || '0';

        trackEvent('user_edit_group_open', { userID, currentGroupId: gId, currentGroup: gKod });

        document.getElementById('ug_userID').value = userID;
        const $row = btn.closest('tr');
        const extraCount = parseInt($row?.getAttribute('data-extra-count') || '0', 10);
        const extraList = String($row?.getAttribute('data-extra-roles') || '').split(',').map(s=>s.trim()).filter(Boolean);
        setRoleButton(extraCount, extraList);
        document.getElementById('ug_nopekerja').value = nopekerja;
        currentPrimaryRoleName = (btn.getAttribute('data-group-name') || '').trim();
        
        // Store original values for comparison
        document.getElementById('ug_userID').setAttribute('data-original-group', gId);
        document.getElementById('ug_userID').setAttribute('data-original-flag', flag);
        
        const namaEl = document.getElementById('ug_nama');
        const jabatanEl = document.getElementById('ug_jabatan');
        const avatarEl = document.getElementById('ug_avatar');
        const flagEl = document.getElementById('ug_flag');
        
        if (namaEl) namaEl.textContent = `${nama} (${stafid})`;
        if (jabatanEl) jabatanEl.textContent = jabatan || '-';
        if (flagEl) flagEl.value = flag;
        
        // Set avatar URL - guna URL dari User::getAvatarUrl() (PHP)
        if (avatarEl) {
          avatarEl.src = avatarUrl || '<?= base_url('assets/images/no-image.jpg') ?>';
        }

        await populateGroups(gId);
        if (!currentPrimaryRoleName) {
          currentPrimaryRoleName = getPrimaryRoleNameFromSelect();
        }
        modal.show();
      });
    }

    // Open extra role modal
    document.getElementById('ug_addRoleBtn')?.addEventListener('click', async function(e){
      e.preventDefault();
      const userID = parseInt(document.getElementById('ug_userID').value || '0', 10);
      if (!userID) {
        showErr('<?= h(__('userList_err_param') ?? 'Parameter tidak lengkap.') ?>');
        return;
      }
      if (!roleModal && roleModalEl && window.bootstrap && bootstrap.Modal) {
        roleModal = new bootstrap.Modal(roleModalEl);
      }
      hideRoleErr();
      document.getElementById('re_userID').value = String(userID);
      const primaryName = currentPrimaryRoleName || getPrimaryRoleNameFromSelect() || '-';
      const primEl = document.getElementById('re_primaryRole');
      if (primEl) primEl.textContent = primaryName;
      await loadExtraRoles(userID);
      roleModal?.show();
    });

    // Save extra roles
    document.getElementById('roleExtraSaveBtn')?.addEventListener('click', createRateLimitedHandler(async function(){
      hideRoleErr();
      const userID = parseInt(document.getElementById('re_userID').value || '0', 10);
      if (!userID) {
        showRoleErr('<?= h(__('userList_err_param') ?? 'Parameter tidak lengkap.') ?>');
        return;
      }
      const selected = Array.from(roleListEl?.querySelectorAll('input[type="checkbox"]:checked') || [])
        .map(el => parseInt(el.value || '0', 10))
        .filter(v => v > 0);

      const saveBtn = document.getElementById('roleExtraSaveBtn');
      const originalText = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> <?= h(__('userList_btn_saving')) ?>';

      try {
        const r = await fetch('<?= base_url('ajax/user-extra-roles.php') ?>', {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-Token': CSRF, 'Accept':'application/json'},
          body: JSON.stringify({ action: 'save', userID, roles: selected })
        });
        const j = await r.json();
        if (!r.ok || !j || j.error) throw new Error((j && j.message) || '<?= h(__('userList_err_update_group') ?? 'Gagal kemas kini kumpulan.') ?>');
        // Update row + button with current selections
        const selectedNames = Array.from(roleListEl?.querySelectorAll('input[type="checkbox"]:checked') || [])
          .map(el => el.parentElement?.querySelector('.role-label')?.textContent?.trim() || '')
          .filter(Boolean);
        updateUserRow(userID, { extraRoles: selectedNames });
        setRoleButton(selectedNames.length, selectedNames);
        roleModal?.hide();
        if (window.Swal) {
          await Swal.fire({
            icon: 'success',
            title: '<?= h(__('userList_success_title') ?? 'Berjaya') ?>',
            text: j.message || '<?= h(__('userList_success_update_roles') ?? 'Peranan tambahan berjaya dikemas kini.') ?>',
            confirmButtonText: '<?= h(__('userList_btn_ok') ?? 'OK') ?>',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          });
        }
      } catch (e) {
        showRoleErr(e.message || '<?= h(__('userList_err_update_group') ?? 'Gagal kemas kini kumpulan.') ?>');
      } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
      }
    }, 1000));

    // Helper function untuk validation dengan blink effect (modal edit)
    function validateFieldEdit(fieldElement, isValid) {
      if (!fieldElement) return;
      
      // Remove existing invalid class
      fieldElement.classList.remove('field-invalid');
      
      // If invalid, add blink effect
      if (!isValid) {
        fieldElement.classList.add('field-invalid');
        
        // Scroll to field if not visible
        setTimeout(() => {
          fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
        
        // Remove class after animation
        setTimeout(() => {
          fieldElement.classList.remove('field-invalid');
        }, 1500);
      }
    }
    
    document.getElementById('ug_saveBtn')?.addEventListener('click', createRateLimitedHandler(async function(){
      hideErr();
      
      // Remove all invalid classes first
      document.querySelectorAll('#userGroupModal .field-invalid').forEach(el => {
        el.classList.remove('field-invalid');
      });
      
      const userID   = parseInt(document.getElementById('ug_userID').value || '0', 10);
      const groupID = document.getElementById('ug_groupKod').value || '';
      const flag     = parseInt(document.getElementById('ug_flag').value || '0', 10);
      
      // Validation dengan blink effect
      let isValid = true;
      
      // Validate userID
      if (!userID) {
        showErr('<?= h(__('userList_err_param') ?? 'Parameter tidak lengkap.') ?>');
        return;
      }
      
      // Validate Group dengan validateGroupId function
      const groupSelect = document.getElementById('ug_groupKod');
      if (!groupID || groupID === '' || !validateGroupId(groupID)) {
        validateFieldEdit(groupSelect, false);
        isValid = false;
      } else {
        groupSelect.classList.remove('field-invalid');
      }
      
      if (!isValid) {
        return; // Stop submission if validation fails
      }
      
      // Get original values
      const originalGroup = document.getElementById('ug_userID').getAttribute('data-original-group') || '';
      const originalFlag = parseInt(document.getElementById('ug_userID').getAttribute('data-original-flag') || '0', 10);
      
      // Check if anything changed
      const groupChanged = (String(groupID) !== String(originalGroup));
      const flagChanged = (flag !== originalFlag);
      
      if (!groupChanged && !flagChanged) {
        // Blink pada group field untuk indicate no changes
        validateFieldEdit(groupSelect, false);
        return;
      }

      // Disable button during request
      const saveBtn = document.getElementById('ug_saveBtn');
      const originalText = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> <?= h(__('userList_btn_saving')) ?>';

      try{
        // Use same pattern as other AJAX endpoints in this file
        const url = '<?= base_url('ajax/user-set-group.php') ?>';
        
        // Build request body - always include current groupID (even if not changing) and flag if changed
        const requestBody = { userID };
        
        // Always include groupID (current value, even if not changing) - server needs it for validation
        if (groupID) {
          requestBody.groupID = parseInt(groupID, 10);
        }
        
        // Include flag if it changed (always include if changed, even if 0)
        if (flagChanged) {
          requestBody.flag = flag;
        }
        
        trackEvent('user_edit_group_save', { userID, groupID: parseInt(groupID, 10), flag });
        
        const r = await fetchWithRetry(url, {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-Token': CSRF, 'Accept':'application/json'},
          body: JSON.stringify(requestBody)
        });
        
        // Check if response is OK
        if (!r.ok) {
          let errorMsg = '<?= h(__('userList_err_update_group') ?? 'Gagal kemas kini kumpulan.') ?>';
          try {
            const errorData = await r.json();
            if (errorData && errorData.message) {
              errorMsg = errorData.message;
            }
          } catch (e) {
            // If JSON parsing fails, use status text
            errorMsg = `HTTP ${r.status}: ${r.statusText || '<?= h(__('userList_err_server')) ?>'}`;
          }
          throw new Error(errorMsg);
        }
        
        // Parse JSON response
        let j;
        try {
          j = await r.json();
        } catch (e) {
          throw new Error('<?= h(__('userList_err_invalid_json')) ?>');
        }
        
        if (!j || j.error){
          throw new Error((j && j.message) || '<?= h(__('userList_err_update_group') ?? 'Gagal kemas kini kumpulan.') ?>');
        }

        trackEvent('user_edit_group_success', { userID, groupID: parseInt(groupID, 10), flag });

        // Close modal first
        modal?.hide();
        
        // Try to update row in-place first (optimized)
        try {
          // Extract groupName from response - check both j.groupName and j.group.nama
          const groupIdResp = j.group && (j.group.id || j.group.f_groupID) ? (j.group.id || j.group.f_groupID) : parseInt(groupID, 10);
          const groupKodResp = j.group && (j.group.kod || j.group.f_groupKod) ? (j.group.kod || j.group.f_groupKod) : '';
          const groupName = j.groupName || (j.group && j.group.nama) || groupKodResp || groupID;
          updateUserRow(userID, {
            groupID: groupIdResp,
            groupKod: groupKodResp,
            groupName: groupName,
            flag: flag
          });
        } catch (e) {
          // Fallback to full reload if in-place update fails
          await reloadUserTable(userID);
        }
        
        // Show success message with SweetAlert
        if (window.Swal) {
          await Swal.fire({
            icon: 'success',
            title: '<?= h(__('userList_success_title') ?? 'Berjaya') ?>',
            text: (j.message || '<?= h(__('userList_success_update_group') ?? 'Kumpulan dan akses pengguna berjaya dikemas kini.') ?>'),
            confirmButtonText: '<?= h(__('userList_btn_ok') ?? 'OK') ?>',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
          });
        }
      }catch(e){
        // Better error handling - sanitize error message
        const errorMsg = sanitizeError(e);
        trackEvent('user_edit_group_error', { userID, error: errorMsg });
        showErr(errorMsg);
      } finally {
        // Re-enable button
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerHTML = originalText;
        }
      }
    }, 1000));

    // ===== Modal Tambah Pengguna =====
    const addUserModalEl = document.getElementById('addUserModal');
    const auStafSelect = document.getElementById('au_stafSelect');
    const auErrorEl = document.getElementById('au_error');
    
    function showAuErr(msg) {
      if (!auErrorEl) return;
      auErrorEl.textContent = msg || '<?= h(__('userList_err_unknown')) ?>';
      auErrorEl.classList.remove('d-none');
    }
    
    function hideAuErr() {
      if (!auErrorEl) return;
      auErrorEl.classList.add('d-none');
    }
    
    // Handle focus + reset when modal hides
    if (addUserModalEl) {
      // Before hiding: ensure no element inside modal keeps focus (fixes aria-hidden warning)
      addUserModalEl.addEventListener('hide.bs.modal', function() {
        try {
          const active = document.activeElement;
          if (active && addUserModalEl.contains(active)) {
            // Blur focused element inside modal so it isn't hidden from AT
            active.blur();
          }
        } catch (e) { /* ignore */ }

        try {
          // Close Select2 dropdown if open to prevent focus retention
          if (window.jQuery && auStafSelect && jQuery(auStafSelect).data('select2')) {
            jQuery(auStafSelect).select2('close');
          }
        } catch (e) { /* ignore */ }

        try {
          // Return focus to the Add User button or a sensible fallback
          const trigger = document.getElementById('btnAddUser') || document.querySelector('[data-bs-target="#addUserModal"]');
          if (trigger) trigger.focus(); else document.body.focus();
        } catch (e) { /* ignore */ }
      });

      // Reset form when modal is fully hidden
      addUserModalEl.addEventListener('hidden.bs.modal', function() {
        if (auStafSelect) {
          if (window.jQuery && jQuery(auStafSelect).data('select2')) {
            jQuery(auStafSelect).val(null).trigger('change');
          } else {
            auStafSelect.value = '';
          }
        }
        document.getElementById('au_groupKod').value = '';
        document.getElementById('au_flag').value = '1';
        // Clear jabatan and jawatan fields
        const jabatanEl = document.getElementById('au_jabatan');
        const jawatanEl = document.getElementById('au_jawatan');
        if (jabatanEl) {
          jabatanEl.textContent = '-';
          jabatanEl.className = 'info-value';
        }
        if (jawatanEl) {
          jawatanEl.textContent = '-';
          jawatanEl.className = 'info-value';
        }
        // auInfoCard remains visible
        hideAuErr();
      });
    }
    
    // Initialize Select2 untuk dropdown staf (simple, tanpa retry loop)
    function initSelect2ForModal() {
      jQuery(function($) {
        if (typeof $.fn.select2 === 'undefined') {
          return;
        }

        // Setup Select2 dengan lazy loading staf list bila modal dibuka
        if (addUserModalEl && auStafSelect) {
          addUserModalEl.addEventListener('shown.bs.modal', async function() {
            const $sel = $(auStafSelect);
            const placeholderText = $sel.data('placeholder') || '<?= h(__('userList_modal_placeholder_select_staff')) ?>';

            // Destroy existing instance jika ada
            if ($sel.data('select2')) {
              $sel.select2('destroy');
            }

            // Lazy load staf options via AJAX (with caching on server)
            // Helper: safe innerHTML setter (prefer DOMPurify when available)
            function setSafeInnerHTML(el, html) {
              if (!el) return;
              if (!html) { el.innerHTML = ''; return; }
              if (window.DOMPurify && typeof DOMPurify.sanitize === 'function') {
                el.innerHTML = DOMPurify.sanitize(html);
                return;
              }
              try {
                var doc = new DOMParser().parseFromString('<div>' + html + '</div>', 'text/html');
                doc.querySelectorAll('script').forEach(function(s){ s.remove(); });
                doc.querySelectorAll('*').forEach(function(n){
                  Array.from(n.attributes).forEach(function(a){
                    if (/^on/i.test(a.name)) n.removeAttribute(a.name);
                    if ((a.name === 'src' || a.name === 'href') && /^javascript:/i.test(a.value)) n.removeAttribute(a.name);
                  });
                });
                el.innerHTML = doc.body.firstChild ? doc.body.firstChild.innerHTML : '';
              } catch (e) {
                el.innerHTML = html;
              }
            }

            function ensureStaffPlaceholder() {
              if (!auStafSelect) return;
              const first = auStafSelect.options[0];
              if (!first || first.value !== '') {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = placeholderText;
                auStafSelect.insertBefore(opt, auStafSelect.firstChild);
              }
            }

            try {
              // Show loading state
              auStafSelect.innerHTML = '<option value=""><?= h(__('userList_loading_staff') ?? 'Memuat senarai staf') ?>...</option>';

              const r = await fetch('<?= base_url('ajax/user-list-staf-options.php') ?>', {
                headers: { 'Accept': 'application/json' }
              });

              if (r.ok) {
                const text = await r.text();
                let j = null;
                try {
                  j = JSON.parse(text);
                } catch (pe) {
                  // parse failed; show fallback message in select
                  auStafSelect.innerHTML = '<option value=""><?= h(__('userList_err_invalid_response') ?? 'Respons tidak sah') ?></option>';
                }

                if (j) {
                  if (!j.error && Array.isArray(j.options) && j.options.length > 0) {
                    auStafSelect.innerHTML = '';
                    ensureStaffPlaceholder();
                    j.options.forEach(opt => {
                      try {
                        const option = document.createElement('option');
                        option.value = opt.value || '';
                        option.setAttribute('data-idpekerja', opt.idpekerja || '');
                        option.setAttribute('data-nama', opt.nama || '');
                        option.setAttribute('data-jawatan', opt.jawatan || '');
                        option.setAttribute('data-jabatan', opt.jabatan || '');
                        if (opt.disabled) option.disabled = true;
                        option.textContent = opt.display || opt.nama || opt.value || '';
                        auStafSelect.appendChild(option);
                      } catch (e) { /* ignore malformed option */ }
                    });
                  } else if (!j.error && j.html) {
                    setSafeInnerHTML(auStafSelect, j.html || '');
                    ensureStaffPlaceholder();
                  } else {
                    auStafSelect.innerHTML = '<option value=""><?= h(__('userList_err_load_staff') ?? 'Ralat memuat staf') ?></option>';
                  }
                }
              } else {
                // Non-OK response: show fallback in select
                auStafSelect.innerHTML = '<option value=""><?= h(__('userList_err_load_staff') ?? 'Ralat memuat staf') ?></option>';
              }
            } catch (e) {
              // Network or other error; show fallback
              auStafSelect.innerHTML = '<option value=""><?= h(__('userList_err_load_staff') ?? 'Ralat memuat staf') ?></option>';
            }

            // Ensure placeholder is selected by default
            if (auStafSelect) {
              auStafSelect.value = '';
            }

            // Initialize Select2 (ikut test-select2.php)
            $sel.select2({
              width: '100%',
              allowClear: true,
              placeholder: placeholderText,
              dropdownParent: $(addUserModalEl) // Pastikan dropdown muncul dalam modal
            });
            $sel.val('').trigger('change');
          });

          // Auto isi jabatan dan jawatan bila pilih staf (ikut test-select2.php)
          $(auStafSelect).on('change', function() {
            const opt = this.selectedOptions && this.selectedOptions[0]
              ? this.selectedOptions[0]
              : null;
            const jabatan = opt ? (opt.getAttribute('data-jabatan') || '') : '';
            const jawatan = opt ? (opt.getAttribute('data-jawatan') || '') : '';

            const jabatanEl = document.getElementById('au_jabatan');
            const jawatanEl = document.getElementById('au_jawatan');
            const auInfoCard = document.getElementById('au_infoCard');

            if (jabatanEl) {
              jabatanEl.textContent = jabatan || '-';
              jabatanEl.className = 'info-value';
            }
            if (jawatanEl) {
              jawatanEl.textContent = jawatan || '-';
              jawatanEl.className = 'info-value';
            }

            // Pastikan info card sentiasa visible
            if (auInfoCard) {
              auInfoCard.style.display = 'block';
            }
          });
        }
      });
    }
    
    // Function untuk refresh dropdown staf selepas delete/tambah user
    async function refreshStafDropdown() {
      const auStafSelect = document.getElementById('au_stafSelect');
      if (!auStafSelect) return;
      const placeholderText = auStafSelect.getAttribute('data-placeholder') || '<?= h(__('userList_modal_placeholder_select_staff')) ?>';

      function ensureStaffPlaceholder() {
        const first = auStafSelect.options[0];
        if (!first || first.value !== '') {
          const opt = document.createElement('option');
          opt.value = '';
          opt.textContent = placeholderText;
          auStafSelect.insertBefore(opt, auStafSelect.firstChild);
        }
      }
      
      try {
        // Fetch staf list terkini dari server dengan retry
        const r = await fetchWithRetry('<?= base_url('ajax/user-list-staf-options.php') ?>', {
          headers: { 'Accept': 'application/json' }
        });

        if (!r.ok) return;

        const text = await r.text();
        let j = null;
        try {
          j = JSON.parse(text);
        } catch (pe) {
          return; // silently ignore malformed response
        }
        if (j && j.error) return;

        // Destroy Select2 jika sudah initialized
        const $sel = jQuery(auStafSelect);
        if ($sel.data('select2')) {
          $sel.select2('destroy');
        }

        // Populate options prefer structured data
        if (Array.isArray(j.options) && j.options.length > 0) {
          auStafSelect.innerHTML = '';
          ensureStaffPlaceholder();
          j.options.forEach(opt => {
            try {
              const option = document.createElement('option');
              option.value = opt.value || '';
              option.setAttribute('data-idpekerja', opt.idpekerja || '');
              option.setAttribute('data-nama', opt.nama || '');
              option.setAttribute('data-jawatan', opt.jawatan || '');
              option.setAttribute('data-jabatan', opt.jabatan || '');
              if (opt.disabled) option.disabled = true;
              option.textContent = opt.display || opt.nama || opt.value || '';
              auStafSelect.appendChild(option);
            } catch (e) { /* ignore malformed option */ }
          });
        } else if (j.html) {
          auStafSelect.innerHTML = j.html || '';
          ensureStaffPlaceholder();
        } else {
          return;
        }
        auStafSelect.value = '';
        
        // Re-init Select2 jika modal sedang dibuka
        const addUserModalEl = document.getElementById('addUserModal');
        if (addUserModalEl && bootstrap.Modal.getInstance(addUserModalEl)?.isShown) {
          $sel.select2({
            width: '100%',
            allowClear: true,
            placeholder: placeholderText,
            dropdownParent: jQuery(addUserModalEl)
          });
          $sel.val('').trigger('change');
        }
      } catch (e) {
        // Silently ignore refresh errors to avoid noisy console in production
      }
    }
    
    // Start initialization - wait for DOM ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initSelect2ForModal);
    } else {
      // DOM already ready, start immediately
      initSelect2ForModal();
    }
    
    // Helper function untuk validation dengan blink effect
    function validateField(fieldElement, isValid) {
      if (!fieldElement) return;
      
      // Remove existing invalid class
      fieldElement.classList.remove('field-invalid');
      
      // If invalid, add blink effect
      if (!isValid) {
        fieldElement.classList.add('field-invalid');
        
        // Scroll to field if not visible
        setTimeout(() => {
          fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
        
        // Remove class after animation
        setTimeout(() => {
          fieldElement.classList.remove('field-invalid');
        }, 1500);
      }
    }
    
    // Save button handler untuk Add User
    document.getElementById('au_saveBtn')?.addEventListener('click', createRateLimitedHandler(async function() {
      hideAuErr();
      
      // Remove all invalid classes first
      document.querySelectorAll('#addUserModal .field-invalid').forEach(el => {
        el.classList.remove('field-invalid');
      });
      
      const stafId = auStafSelect ? auStafSelect.value : '';
      const groupID = document.getElementById('au_groupKod').value || '';
      const flag = parseInt(document.getElementById('au_flag').value || '1', 10);
      
      // Get idpekerja from selected option
      let idpekerja = '';
      let selectedOption = null;
      if (auStafSelect && auStafSelect.selectedOptions && auStafSelect.selectedOptions[0]) {
        selectedOption = auStafSelect.selectedOptions[0];
        idpekerja = selectedOption.getAttribute('data-idpekerja') || '';
      }
      
      // Validation dengan blink effect
      let isValid = true;
      
      // Validate Staf dengan validateStafID function
      if (!stafId || stafId === '' || !validateStafID(stafId)) {
        const $stafSelect2 = jQuery(auStafSelect).data('select2');
        if ($stafSelect2) {
          const $container = jQuery(auStafSelect).next('.select2-container');
          if ($container.length) {
            validateField($container[0], false);
          }
        } else {
          validateField(auStafSelect, false);
        }
        isValid = false;
      } else {
        // Valid - remove invalid class
        const $stafSelect2 = jQuery(auStafSelect).data('select2');
        if ($stafSelect2) {
          const $container = jQuery(auStafSelect).next('.select2-container');
          if ($container.length) {
            $container[0].classList.remove('field-invalid');
          }
        } else {
          auStafSelect.classList.remove('field-invalid');
        }
      }
      
      // Validation for disabled option
      if (selectedOption && selectedOption.disabled) {
        const $stafSelect2 = jQuery(auStafSelect).data('select2');
        if ($stafSelect2) {
          const $container = jQuery(auStafSelect).next('.select2-container');
          if ($container.length) {
            validateField($container[0], false);
          }
        } else {
          validateField(auStafSelect, false);
        }
        isValid = false;
      }
      
      // Validate Group dengan validateGroupId function
      const groupSelect = document.getElementById('au_groupKod');
      if (!groupID || groupID === '' || !validateGroupId(groupID)) {
        validateField(groupSelect, false);
        isValid = false;
      } else {
        groupSelect.classList.remove('field-invalid');
      }
      
      if (!isValid) {
        return; // Stop submission if validation fails
      }
      
      const saveBtn = this;
      const originalText = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> <?= h(__('userList_btn_saving')) ?>';
      
      try {
        const url = '<?= base_url('ajax/user-add.php') ?>';
        const requestBody = {
          nopekerja: stafId || '',
          idpekerja: idpekerja,
          groupID: parseInt(groupID, 10),
          flag: flag,
          csrf_token: CSRF
        };
        
        // Removed sensitive console.logs for security
        
        trackEvent('user_add', { stafId, groupID: parseInt(groupID, 10), flag });
        
        const r = await fetchWithRetry(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(requestBody)
        });
        
        // Read response once
        let responseText = '';
        let j = null;
        
        try {
          responseText = await r.text();
          j = JSON.parse(responseText);
        } catch (e) {
          throw new Error(`<?= h(__('userList_err_invalid_response')) ?> (${r.status}).`);
        }
        
        if (!r.ok) {
          let errorMsg = '<?= h(__('userList_err_add_failed')) ?>';
          if (j && j.message) {
            errorMsg = j.message;
          } else {
            errorMsg = `HTTP ${r.status}: ${r.statusText || '<?= h(__('userList_err_server')) ?>'}`;
          }
          throw new Error(errorMsg);
        }
        
        if (!j || j.error) {
          throw new Error((j && j.message) || '<?= h(__('userList_err_add_failed')) ?>');
        }
        
        trackEvent('user_add_success', { userID: j.userID, stafId, groupID: parseInt(groupID, 10) });
        
        // Close modal
        const addUserModal = bootstrap.Modal.getInstance(addUserModalEl);
        if (addUserModal) {
          addUserModal.hide();
        }
        
        // Reload table via AJAX
        await reloadUserTable(j.userID || null); // Pass userID to highlight the new row
        
        // Refresh dropdown staf dalam add user modal (untuk update disabled status)
        await refreshStafDropdown();
        
        // Show success message
        await Swal.fire({
          icon: 'success',
          title: '<?= h(__('userList_success_title')) ?>',
          text: (j.message || '<?= h(__('userList_success_add')) ?>'),
          confirmButtonText: '<?= h(__('userList_btn_ok')) ?>',
          confirmButtonColor: '#28a745',
          timer: 2000,
          timerProgressBar: true
        });
      } catch (e) {
        const errorMsg = sanitizeError(e);
        trackEvent('user_add_error', { stafId, error: errorMsg });
        showAuErr(errorMsg || '<?= h(__('userList_err_add_failed')) ?>');
      } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
      }
    }, 1000));

  });
})();
</script>

</body>
</html>
