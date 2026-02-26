<?php
// pages/keluarga.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

// ==================== CONFIGURATION CONSTANTS ====================
const PROFILE_CONFIG = [
  'LOGIN_ACTIVITY_LIMIT' => 50,
  'AUDIT_EVENTS_LIMIT' => 100,
  'DATATABLES_PAGE_LENGTH' => 10,
  'DATATABLES_INIT_DELAY' => 300,
  'TOAST_DURATION' => 1400,
  'POLLING_INTERVAL' => 100,
  'POLLING_MAX_ATTEMPTS' => 50,
  'COPY_RATE_LIMIT' => 1000
];

// Controller
require_once __DIR__ . '/../controllers/ProfileController.php';

// Error boundary - catch all exceptions
$errorMessage = null;
try {
  $controller   = new ProfileController();
  $lang         = $controller->getLang();
  $version      = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));
  $profile      = $controller->getCurrentUserProfile();
  $profileView  = $profile; // freeze to avoid include collisions
  $loginActivity = $controller->getLoginActivity(PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT']);
  $auditEvents = $controller->getAuditEvents(PROFILE_CONFIG['AUDIT_EVENTS_LIMIT']);
} catch (Throwable $e) {
  error_log('[profile.php] Error loading data: ' . $e->getMessage());
  $profile = [];
  $profileView = [];
  $loginActivity = [];
  $auditEvents = [];
  $errorMessage = 'Ralat memuat data profil. Sila cuba lagi atau hubungi pentadbir sistem.';
}

// Close session lock after reading
if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function tr(string $key, string $fallback): string {
  $t = __($key);
  return ($t === $key || $t === null || $t === '') ? $fallback : (string)$t;
}

/**
 * Safe DateTime creation dengan error handling
 */
function safeDateTime(?string $dateString): ?DateTime {
  if (empty($dateString)) return null;
  try {
    return new DateTime($dateString);
  } catch (Exception $e) {
    error_log('[profile.php] Invalid date: ' . $dateString . ' - ' . $e->getMessage());
    return null;
  }
}

/**
 * Format duration dengan proper handling
 */
function formatDuration(?int $seconds): string {
  if ($seconds === null || $seconds < 0) {
    return '—';
  }
  
  if ($seconds < 60) {
    return $seconds . 's';
  } elseif ($seconds < 3600) {
    return floor($seconds / 60) . 'm';
  } elseif ($seconds < 86400) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return $hours . 'j ' . $minutes . 'm';
  } else {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    return $days . 'h ' . $hours . 'j';
  }
}

/**
 * Detect device type dari user agent dengan better parsing
 */
function detectDeviceType(string $userAgent): array {
  $ua = strtolower($userAgent);
  $icon = 'ri-device-line';
  $type = 'Unknown';
  
  // Mobile detection (check first)
  if (preg_match('/ipad/i', $ua)) {
    $icon = 'ri-tablet-line';
    $type = 'iPad';
  } elseif (preg_match('/iphone|ipod/i', $ua)) {
    $icon = 'ri-smartphone-line';
    $type = 'iPhone';
  } elseif (preg_match('/android/i', $ua)) {
    $icon = 'ri-smartphone-line';
    $type = 'Android';
  } elseif (preg_match('/mobile|blackberry|iemobile|opera mini/i', $ua)) {
    $icon = 'ri-smartphone-line';
    $type = 'Mobile';
  }
  // Desktop OS detection
  elseif (preg_match('/windows/i', $ua)) {
    $icon = 'ri-computer-line';
    $type = 'Windows';
  } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
    $icon = 'ri-macbook-line';
    $type = 'macOS';
  } elseif (preg_match('/linux/i', $ua)) {
    $icon = 'ri-ubuntu-line';
    $type = 'Linux';
  } elseif (preg_match('/chrome os|cros/i', $ua)) {
    $icon = 'ri-computer-line';
    $type = 'Chrome OS';
  }
  
  return ['icon' => $icon, 'type' => $type];
}

/**
 * Check if user has active session
 */
function hasActiveSession(array $loginActivity): bool {
  foreach ($loginActivity as $activity) {
    if (!empty($activity['is_active']) && $activity['is_active'] === true) {
      return true;
    }
  }
  return false;
}
?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php
    $NEED_DATERANGE  = false;
    $NEED_VECTORMAP  = false;
    $NEED_DATATABLES = true;
    $NEED_SELECT2    = false;
    include __DIR__ . '/../includes/head.php';
  ?>
  <!-- ✅ Standard DataTables CSS (shared) -->
  <link href="<?= base_url('assets/css/datatables-standard.css') ?>?v=<?= h($version) ?>" rel="stylesheet">
  <style>
    :root{
      --card-radius: 18px;
      --ring: 3px solid rgba(0,0,0,.06);
    }
    .profile-card{
      overflow: hidden;
      border-radius: var(--card-radius);
    }
    .profile-hero{
      position: relative;
      border-radius: calc(var(--card-radius) - 2px);
      background: linear-gradient(135deg, rgba(59,130,246,.10), rgba(16,185,129,.10));
      padding: 1.25rem 1.25rem 1rem 1.25rem;
    }
    .profile-hero .avatar{
      width: 120px;height: 120px;border-radius: 50%;
      object-fit: cover;border: var(--ring);
      box-shadow: 0 6px 20px rgba(0,0,0,.08);
      background:#fff;
    }
    .status-dot{
      position:absolute; right: 6px; bottom: 10px;
      width:14px; height:14px; border-radius:50%;
      background: #22c55e; border:2px solid #fff;
      box-shadow:0 0 0 2px rgba(0,0,0,.05);
    }
    .display-name{ font-weight: 700; letter-spacing:.2px; }
    .subline{ color: var(--tb-muted, #6b7280); }

    .chip{
      display:inline-flex; align-items:center; gap:.4rem;
      padding:.35rem .6rem; border-radius:999px;
      border:1px solid rgba(0,0,0,.08);
      background: rgba(0,0,0,.03);
      font-size:.875rem;
    }
    [data-bs-theme="dark"] .chip{
      border-color: rgba(255,255,255,.1);
      background: rgba(255,255,255,.06);
    }

    .kv{
      display:grid; grid-template-columns: 220px 1fr; gap:.6rem 1rem;
    }
    @media (max-width: 576px){ .kv{ grid-template-columns: 1fr } }

    .quick-actions .btn{
      border-radius: 999px;
      padding: .4rem .8rem;
      border: 1px solid rgba(0,0,0,.06);
      background: #fff;
    }
    [data-bs-theme="dark"] .quick-actions .btn{
      background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.1);
    }

    /* tiny toast */
    .toast-lite{
      position: fixed; right: 16px; bottom: 16px;
      background: rgba(0,0,0,.85); color:#fff;
      padding:.6rem .8rem; border-radius:10px; font-size:.9rem;
      opacity:0; transform: translateY(10px);
      transition: all .2s ease;
      z-index: 9999;
    }
    .toast-lite.show{ opacity:1; transform: translateY(0); }
    .toast-lite.toast-success{ background: rgba(34,197,94,.9); }
    .toast-lite.toast-error{ background: rgba(239,68,68,.9); }
    .toast-lite.toast-warning{ background: rgba(251,191,36,.9); }
    
    /* Remove inline styles - move to CSS */
    .profile-tabs{
      border-bottom: 2px solid #e9ecef;
      padding: 0 1.25rem;
      margin: 0;
    }
    .profile-table-col-no{ width:5%; text-align:center; }
    /* Ensure numbering cells are centered */
    #loginActivityTable td:first-child, #loginActivityTable th.profile-table-col-no { text-align:center; vertical-align:middle; }
    .profile-table-col-date{ width: 180px; }
    .profile-table-col-ip{ width: 150px; }
    .profile-table-col-duration{ width: 120px; }
    .profile-table-col-status{ width: 100px; }
    .empty-state-icon{ font-size: 3rem; }
    
    /* ✅ Table styling sama seperti senarai-pengguna.php */
    #loginActivityTable, #auditEventsTable {
      table-layout: auto;
      width: 100%;
      border-radius: 0.75rem;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    /* ✅ Loader overlay (reuse report-skt-cemerlang implementation) */
    .table-responsive { position: relative; }
    .table-loader {
      position: absolute;
      top: 8px; /* show at top-center of table */
      left: 0;
      right: 0;
      margin: 0 auto;
      height: auto;
      padding: 1rem 0;
      background: rgba(255,255,255,0.95);
      backdrop-filter: saturate(120%) blur(1px);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: auto;
    }
    [data-bs-theme="dark"] .table-loader { background: rgba(0,0,0,.5); }
    .table-loader.d-none { display: none !important; }
    #loginActivityTable thead, #auditEventsTable thead {
      background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
      color: #ffffff;
    }
    #loginActivityTable thead th, #auditEventsTable thead th {
      font-weight: 700;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 1rem 0.75rem;
      border: none;
      color: #ffffff;
    }
    #loginActivityTable tbody tr, #auditEventsTable tbody tr {
      transition: all 0.2s ease;
    }
    #loginActivityTable tbody tr:hover, #auditEventsTable tbody tr:hover {
      background: #f8fafc !important;
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    #loginActivityTable tbody td, #auditEventsTable tbody td {
      padding: 0.875rem 0.75rem;
      border-color: #f1f5f9;
      vertical-align: middle;
    }
    /* Dark theme support */
    html[data-bs-theme="dark"] #loginActivityTable thead,
    html[data-bs-theme="dark"] #auditEventsTable thead {
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    }
    html[data-bs-theme="dark"] #loginActivityTable tbody tr:hover,
    html[data-bs-theme="dark"] #auditEventsTable tbody tr:hover {
      background: #334155 !important;
    }
    /* Remove stripline effect */
    #loginActivityTable tbody tr,
    #loginActivityTable tbody tr:nth-of-type(odd),
    #loginActivityTable tbody tr:nth-of-type(even),
    #auditEventsTable tbody tr,
    #auditEventsTable tbody tr:nth-of-type(odd),
    #auditEventsTable tbody tr:nth-of-type(even) {
      background-color: transparent !important;
    }
    
    /* Status dot variants */
    .status-dot.status-active{
      background: #22c55e;
    }
    .status-dot.status-inactive{
      background: #6b7280;
    }
    
    /* Audit metadata modal button */
    .btn-view-meta{
      min-width: 32px;
    }
    
    /* Audit metadata modal styling */
    .audit-meta-modal .modal-xl{
      max-width: 1300px; /* wider to keep two columns side-by-side */
    }
    
    /* Header text visibility improvements */
    .audit-meta-modal .modal-header{
      box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    
    .audit-meta-modal .modal-header .modal-title{
      letter-spacing: 0.3px;
    }
    
    /* Professional table styling for changes */
    .audit-meta-modal .table th{
      font-weight: 600;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #dee2e6;
    }
    
    .audit-meta-modal .table td{
      vertical-align: middle;
      font-size: 0.9rem;
    }
    
    .audit-meta-modal .table code{
      background: rgba(13,110,253,.1);
      padding: 0.2rem 0.4rem;
      border-radius: 4px;
      font-size: 0.85rem;
    }
    
    /* Change set card styling */
    .change-set-card{
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .change-set-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,.1) !important;
    }
    
    /* Value comparison styling */
    .old-value{
      background: rgba(220,53,69,.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      display: inline-block;
    }
    
    .new-value{
      background: rgba(25,135,84,.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      display: inline-block;
    }
    
    /* Enhanced Tab styling */
    .audit-meta-modal .audit-tabs .nav-link{
      border: none;
      border-radius: 0.5rem;
      color: #64748b;
      font-weight: 600;
      transition: all 0.2s ease;
      background: transparent;
      margin-right: 0.5rem;
    }
    
    .audit-meta-modal .audit-tabs .nav-link:hover{
      color: #3b82f6;
      background: rgba(59,130,246,.08);
    }
    
    .audit-meta-modal .audit-tabs .nav-link.active{
      color: #ffffff;
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      box-shadow: 0 2px 8px rgba(59,130,246,.3);
    }
    
    .audit-meta-modal .audit-tabs .nav-link.active .badge{
      background: rgba(255,255,255,.2) !important;
      color: #ffffff !important;
    }
    
    /* Card hover effects */
    .audit-change-card:hover{
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0,0,0,.12) !important;
    }
    
    .audit-info-card{
      border-left: 4px solid #667eea !important;
    }
    
    /* Table row hover */
    .audit-changes-table tbody tr:hover{
      background: rgba(13,110,253,.05) !important;
      transition: background 0.2s ease;
    }
    
    /* Modal content animation */
    .audit-meta-modal .modal-content{
      animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp{
      from{
        opacity: 0;
        transform: translateY(30px);
      }
      to{
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Badge enhancements */
    .audit-meta-modal .badge{
      font-weight: 600;
      letter-spacing: 0.3px;
    }
    
    /* Dark theme support */
    [data-bs-theme="dark"] .audit-meta-modal .table th{
      border-bottom-color: rgba(255,255,255,.1);
    }
    
    [data-bs-theme="dark"] .audit-meta-modal .table code{
      background: rgba(13,110,253,.2);
    }
    
    /* Loading skeleton */
    .skeleton-loader{
      padding: 1rem;
    }
    .skeleton-row{
      height: 50px;
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: skeleton-loading 1.5s ease-in-out infinite;
      border-radius: 4px;
      margin-bottom: 0.5rem;
    }
    @keyframes skeleton-loading{
      0%{ background-position: 200% 0; }
      100%{ background-position: -200% 0; }
    }
  </style>
</head>
<body
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.sidebar'] ?? 'dark') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <!-- Title + breadcrumb -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
              <h4 class="page-title">
                <i class="ri-user-3-line me-1"></i>
                <?= h(tr('family_title','Maklumat Keluarga')) ?>
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

        <?php
          $avatarUrl = $profileView['avatar_url'] ?? base_url('assets/images/no-image.jpg');
          $namaPenuh = $profileView['nama_penuh'] ?? '';
          $nickname  = $profileView['nickname']   ?? '';
          $jawatan   = $profileView['jawatan']    ?? '';
          $gred      = $profileView['gred']       ?? '';
          $jabatan   = $profileView['jabatan']    ?? '';
          $stafID    = $profileView['stafID']     ?? '';
          $nopek     = $profileView['nopekerja']  ?? '';
          $emel      = $profileView['emel']       ?? '';
          $jawGred   = trim($jawatan . ($gred ? ' • '.$gred : ''));
          
          // Check active session status
          $isActive = hasActiveSession($loginActivity);
        ?>

        <!-- Profile Card with Tabs -->
        <div class="card border-0 shadow-sm profile-card">
          <div class="profile-hero">
            <div class="d-flex align-items-center gap-3 flex-wrap position-relative">
              <div class="position-relative">
                <img src="<?= h($avatarUrl) ?>"
                     alt="<?= h(tr('profile_avatar_alt','Avatar pengguna')) ?>"
                     class="avatar"
                     onerror="this.onerror=null;this.src='<?= h(base_url('assets/images/no-image.jpg')) ?>';">
                <span class="status-dot <?= $isActive ? 'status-active' : 'status-inactive' ?>"
                      title="<?= h($isActive ? tr('profile_status_active','Aktif') : tr('profile_status_inactive','Tidak Aktif')) ?>"></span>
              </div>

              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="display-name fs-4 mb-0">
                    <?= h($namaPenuh !== '' ? $namaPenuh : '—') ?>
                  </span>
                </div>

                <div class="subline mt-1">
                  <?php if ($jawGred !== ''): ?>
                    <span class="chip">
                      <i class="ri-briefcase-2-line"></i><?= h($jawGred) ?>
                    </span>
                  <?php endif; ?>
                  <?php if ($jabatan !== ''): ?>
                    <span class="chip">
                      <i class="ri-building-2-line"></i><?= h($jabatan) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="quick-actions d-flex align-items-center gap-2 ms-auto">
                <?php if ($stafID !== ''): ?>
                  <button class="btn btn-sm btn-copy-staf" 
                          type="button"
                          aria-label="<?= h(tr('profile_btn_copy_no_staf','Salin No. Staf')) ?>"
                          data-copy-value="<?= h($stafID) ?>">
                    <i class="ri-file-copy-2-line me-1" aria-hidden="true"></i>
                    <?= h(tr('profile_btn_copy_no_staf','Salin No. Staf')) ?>
                  </button>
                <?php endif; ?>

                <?php if ($emel !== ''): ?>
                  <button class="btn btn-sm btn-copy-email" 
                          type="button"
                          aria-label="<?= h(tr('profile_btn_copy_email','Salin Emel')) ?>"
                          data-copy-value="<?= h($emel) ?>">
                    <i class="ri-clipboard-line me-1" aria-hidden="true"></i>
                    <?= h(tr('profile_btn_copy_email','Salin Emel')) ?>
                  </button>
                <?php endif; ?>
                
                <!-- refresh button removed (redundant near copy buttons) -->
              </div>
            </div>
          </div>

          <!-- Tab Navigasi -->
          <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab maklumat keluarga')) ?>">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#profil-keluarga-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('profile_tab_maklumat_keluarga','Maklumat Keluarga')) ?>
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

            <!-- Tab 1: Maklumat Keluarga -->
            <div class="tab-pane fade show active" id="profil-keluarga-tab" role="tabpanel">
              <div class="kv">
                <div class="text-muted"><?= h(tr('profile_no_staf','No. Staf')) ?></div>
                <div class="fw-semibold"><?= h($stafID !== '' ? $stafID : '—') ?></div>

                <div class="text-muted"><?= h(tr('profile_no_pekerja','No. Pekerja')) ?></div>
                <div class="fw-semibold"><?= h($nopek !== '' ? $nopek : '—') ?></div>

                <div class="text-muted"><?= h(tr('profile_jabatan','Jabatan')) ?></div>
                <div class="fw-semibold"><?= h($jabatan !== '' ? $jabatan : '—') ?></div>

                <div class="text-muted"><?= h(tr('profile_emel','Emel')) ?></div>
                <div class="fw-semibold">
                  <?php if ($emel !== ''): ?>
                    <a href="mailto:<?= h($emel) ?>" class="text-decoration-underline">
                      <?= h($emel) ?>
                    </a>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /Profile Card with Tabs -->

      </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
<script>
(function(){
  'use strict';
  
  // ==================== CONFIGURATION ====================
  const CONFIG = {
    LOGIN_ACTIVITY_LIMIT: <?= PROFILE_CONFIG['LOGIN_ACTIVITY_LIMIT'] ?>,
    AUDIT_EVENTS_LIMIT: <?= PROFILE_CONFIG['AUDIT_EVENTS_LIMIT'] ?>,
    DATATABLES_PAGE_LENGTH: <?= PROFILE_CONFIG['DATATABLES_PAGE_LENGTH'] ?>,
    DATATABLES_INIT_DELAY: <?= PROFILE_CONFIG['DATATABLES_INIT_DELAY'] ?>,
    TOAST_DURATION: <?= PROFILE_CONFIG['TOAST_DURATION'] ?>,
    POLLING_INTERVAL: <?= PROFILE_CONFIG['POLLING_INTERVAL'] ?>,
    POLLING_MAX_ATTEMPTS: <?= PROFILE_CONFIG['POLLING_MAX_ATTEMPTS'] ?>,
    COPY_RATE_LIMIT: <?= PROFILE_CONFIG['COPY_RATE_LIMIT'] ?>
  };
  // Current user's group (from server-side) — used to restrict metadata view
  
  // ==================== NAMESPACE ====================
  const ProfilePage = {
    // DataTable instances
    loginActivityDT: null,
    auditEventsDT: null,
    
    // Rate limiting untuk copy
    lastCopyTime: 0,
    
    // Loading states
    isLoading: false,
    
    /**
     * Copy text to clipboard dengan rate limiting
     */
    copyText: async function(text) {
      if (!text) {
        this.toast('Tiada teks untuk disalin', 'error');
        return;
      }
      
      // Rate limiting
      const now = Date.now();
      if (now - this.lastCopyTime < CONFIG.COPY_RATE_LIMIT) {
        this.toast('Sila tunggu sebentar sebelum menyalin lagi', 'warning');
        return;
      }
      this.lastCopyTime = now;
      
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(text);
          this.toast("<?= h(tr('profile_js_copied','Disalin')) ?>", 'success');
        } else {
          this.fallbackCopy(text);
        }
      } catch (e) {
        console.error('Clipboard API failed:', e);
        this.fallbackCopy(text);
      }
    },

    _escapeHtml: function(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    },
    
    /**
     * Fallback copy method
     */
    fallbackCopy: function(text) {
      const el = document.createElement('textarea');
      el.value = text;
      el.setAttribute('readonly', '');
      el.style.position = 'fixed';
      el.style.left = '-9999px';
      document.body.appendChild(el);
      el.select();
      
      try {
        document.execCommand('copy');
        this.toast("<?= h(tr('profile_js_copied','Disalin')) ?>", 'success');
      } catch (e) {
        console.error('Fallback copy failed:', e);
        this.toast('Gagal menyalin teks', 'error');
      } finally {
        document.body.removeChild(el);
      }
    },
    
    /**
     * Toast notification dengan type support
     */
    toast: function(msg, type = 'info') {
      let toast = document.querySelector('.toast-lite');
      if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-lite';
        toast.setAttribute('aria-live', 'polite');
        toast.setAttribute('aria-atomic', 'true');
        document.body.appendChild(toast);
      }
      
      toast.textContent = msg;
      toast.className = 'toast-lite toast-' + type;
      toast.classList.add('show');
      
      setTimeout(() => {
        toast.classList.remove('show');
      }, CONFIG.TOAST_DURATION);
    },
    
    /**
     * Show loading state
     */
    showLoading: function(selector) {
      const el = document.querySelector(selector);
      if (el) {
        el.style.display = 'block';
        this.isLoading = true;
      }
    },
    
    /**
     * Hide loading state
     */
    hideLoading: function(selector) {
      const el = document.querySelector(selector);
      if (el) {
        el.style.display = 'none';
        this.isLoading = false;
      }
    },
    
    /**
     * Wait for DataTables dengan Promise-based approach
     */
    waitForDataTables: function(maxWait = 5000) {
      return new Promise((resolve, reject) => {
        const startTime = Date.now();
        const checkInterval = setInterval(() => {
          if (typeof $.fn.DataTable !== 'undefined' && typeof $ !== 'undefined') {
            clearInterval(checkInterval);
            resolve();
          } else if (Date.now() - startTime > maxWait) {
            clearInterval(checkInterval);
            reject(new Error('DataTables failed to load within timeout'));
          }
        }, CONFIG.POLLING_INTERVAL);
      });
    },
    
    /**
     * Kill session handler
     */
    killSession: async function(sessionId) {
      if (!sessionId) {
        this.toast('<?= h(tr('profile_login_kill_error_no_session','ID sesi tidak sah')) ?>', 'error');
        return;
      }
      
      // Confirm dialog
      const confirmed = await Swal.fire({
        title: '<?= h(tr('profile_login_kill_confirm_title','Tamatkan Sesi?')) ?>',
        text: '<?= h(tr('profile_login_kill_confirm_text','Anda pasti mahu tamatkan sesi ini? Pengguna akan dipaksa log keluar.')) ?>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<?= h(tr('profile_login_kill_confirm_yes','Ya, Tamatkan')) ?>',
        cancelButtonText: '<?= h(tr('profile_login_kill_confirm_no','Batal')) ?>',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
      });
      
      if (!confirmed.isConfirmed) {
        return;
      }
      
      try {
        // Show shared AJAX loader (report-style)
        const loader = document.getElementById('loginAjaxLoader');
        if (loader) loader.classList.remove('d-none');

        const response = await fetch('<?= base_url('ajax/profile-kill-session.php') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            session_id: sessionId,
            csrf_token: '<?= h($_SESSION['csrf_token'] ?? '') ?>'
          })
        });

        const data = await response.json();

        if (data.success) {
          if (loader) loader.classList.add('d-none');

          // If server indicates the killed session is the current session, force client logout after countdown
          if (data.force_logout) {
            const countdown = parseInt(data.countdown || 10, 10);
            let remaining = countdown;
            await Swal.fire({
              icon: 'warning',
              title: '<?= h(tr('profile_login_kill_force_title','Sesi anda akan ditamatkan')) ?>',
              html: '<div id="swal-logout-count"><?= h(tr('profile_login_kill_force_text','Anda akan dilog keluar dalam')) ?> <strong>' + remaining + '</strong>s</div>',
              showConfirmButton: false,
              allowOutsideClick: false,
              allowEscapeKey: false,
              didOpen: () => {
                const el = document.getElementById('swal-logout-count');
                const timer = setInterval(() => {
                  remaining -= 1;
                  if (el) el.querySelector('strong').textContent = remaining;
                  if (remaining <= 0) {
                    clearInterval(timer);
                    // Redirect to logout to destroy session on server
                    window.location.href = '<?= base_url('logout.php') ?>';
                  }
                }, 1000);
              }
            });
            return;
          }

          // Otherwise show normal success and refresh table
          await Swal.fire({
            icon: 'success',
            title: '<?= h(tr('profile_login_kill_success','Sesi berjaya ditamatkan')) ?>',
            text: data.message || '<?= h(tr('profile_login_kill_success_text','Sesi telah ditamatkan')) ?>',
            confirmButtonText: 'OK'
          });

          if (this.loginActivityDT && this.loginActivityDT.ajax) {
            this.loginActivityDT.ajax.reload(null, true);
          } else {
            setTimeout(() => this.initLoginActivityTable(), 200);
          }
        } else {
          if (loader) loader.classList.add('d-none');
          await Swal.fire({ icon: 'error', title: '<?= h(tr('profile_login_kill_error','Gagal tamatkan sesi')) ?>', text: data.message || '<?= h(tr('profile_login_kill_error','Gagal tamatkan sesi')) ?>' });
        }
      } catch (error) {
        console.error('Kill session error:', error);
        const loader = document.getElementById('loginAjaxLoader'); if (loader) loader.classList.add('d-none');
        await Swal.fire({ icon: 'error', title: '<?= h(tr('profile_login_kill_error_network','Ralat rangkaian. Sila cuba lagi.')) ?>' });
      }
    },
    
    /**
     * Initialize Login Activity DataTable
     */
    initLoginActivityTable: function() {
      if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded yet');
        return;
      }
      
      if (!$('#loginActivityTable').length) {
        return;
      }
      
      // Check if table is visible
      if (!$('#login-aktiviti-tab').hasClass('active') && !$('#login-aktiviti-tab').hasClass('show')) {
        return;
      }
      
      // Check if already initialized
      if ($.fn.DataTable.isDataTable('#loginActivityTable')) {
        return;
      }
      
      // Destroy existing instance if any
      if (this.loginActivityDT) {
        try {
          this.loginActivityDT.destroy();
        } catch(e) {
          // Ignore errors
        }
        this.loginActivityDT = null;
      }
      
      this.loginActivityDT = $('#loginActivityTable').DataTable({
        ajax: {
          url: '<?= base_url('ajax/profile-login-activity.php') ?>',
          dataSrc: 'data'
        },
        columns: [
          { data: null, title: 'No.' },
          { data: 'started' },
          { data: 'ip' },
          { data: 'device' },
          { data: 'duration' },
          { data: 'status' },
          { data: 'actions' }
        ],
        order: [[1, 'desc']],
        pageLength: CONFIG.DATATABLES_PAGE_LENGTH,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        language: {
          lengthMenu: '<?= h(tr('profile_dt_show','Papar')) ?> _MENU_ <?= h(tr('profile_dt_records','rekod')) ?>',
          search: '<?= h(tr('profile_dt_search','Cari')) ?>:',
          zeroRecords: '<?= h(tr('profile_dt_no_records','Tiada rekod ditemui')) ?>',
          info: '<?= h(tr('profile_dt_info','Paparan _START_ hingga _END_ daripada _TOTAL_ rekod')) ?>',
          infoEmpty: '<?= h(tr('profile_dt_info_empty','Paparan 0 hingga 0 daripada 0 rekod')) ?>',
          infoFiltered: '(<?= h(tr('profile_dt_filtered','ditapis daripada _MAX_ jumlah rekod')) ?>)',
          paginate: {
            previous: '<?= h(tr('profile_dt_previous','Sebelum')) ?>',
            next: '<?= h(tr('profile_dt_next','Seterusnya')) ?>'
          }
        },
        responsive: true,
        autoWidth: false,
        stateSave: false,
        processing: false,
        deferRender: true,
        dom: '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>' +
          't' +
          '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
        columnDefs: [
          { orderable: false, searchable: false, targets: [0] }, // No. column
          { orderable: true, targets: [1, 3, 4] }, // date and others
          { orderable: false, targets: [6] }, // Actions column
          { className: 'text-center', targets: [4, 5, 6] },
          { targets: [2,3,5,6], render: function(data, type, row, meta){ return data; } }
        ],
        createdRow: function(row, data, dataIndex){
          // allow HTML in IP, device, status, actions columns
        }
      });

      // Show loader for initial AJAX and subsequent reloads
      $('#loginActivityTable').on('preXhr.dt', function(){
        const loader = document.getElementById('loginAjaxLoader'); if (loader) loader.classList.remove('d-none');
      });

      // Hide loader after data arrived and update numbering
      this.loginActivityDT.on('xhr.dt draw.dt', function(e, settings, json){
        const loader = document.getElementById('loginAjaxLoader'); if (loader) loader.classList.add('d-none');
        try {
          const api = $('#loginActivityTable').DataTable();
          const info = api.page.info();
          api.rows({ page: 'current' }).nodes().each(function(el, i){
            $(el).find('td').eq(0).html(info.start + i + 1);
          });
        } catch (err) {
          console.error('Numbering update failed:', err);
        }
      });

      // Global AJAX error handler for this table
      $('#loginActivityTable').on('error.dt', function(e, settings, techNote, message){
        const loader = document.getElementById('loginAjaxLoader'); if (loader) loader.classList.add('d-none');
        console.error('DataTable error:', techNote, message);
        Swal.fire({ icon: 'error', title: '<?= h(tr('profile_dt_error','Ralat memuat data')) ?>', text: message || techNote || '<?= h(tr('profile_dt_error_msg','Gagal dapatkan data.')) ?>' });
      });
    },
    
    /**
     * Initialize Audit Events DataTable
     */
    initAuditEventsTable: function() {
      if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded yet');
        return;
      }
      
      if (!$('#auditEventsTable').length) {
        return;
      }
      
      // Check if table is visible
      if (!$('#jejak-audit-tab').hasClass('active') && !$('#jejak-audit-tab').hasClass('show')) {
        return;
      }
      
      // Check if already initialized
      if ($.fn.DataTable.isDataTable('#auditEventsTable')) {
        return;
      }
      
      // Destroy existing instance if any
      if (this.auditEventsDT) {
        try {
          this.auditEventsDT.destroy();
        } catch(e) {
          // Ignore errors
        }
        this.auditEventsDT = null;
      }
      
      this.auditEventsDT = $('#auditEventsTable').DataTable({
        ajax: {
          url: '<?= base_url('ajax/profile-audit-events.php') ?>',
          dataSrc: 'data'
        },
          columns: [
            { data: null, title: 'No.' },
            { data: 'occurred_at' },
            { data: 'user' },
            { data: 'ip' },
            { data: 'activity' },
            { data: 'outcome' },
            { data: 'severity' },
            { data: 'actions' }
          ],
        order: [[1, 'desc']], // Sort by date/time column (latest first)
        pageLength: CONFIG.DATATABLES_PAGE_LENGTH,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
        language: {
          lengthMenu: '<?= h(tr('profile_dt_show','Papar')) ?> _MENU_ <?= h(tr('profile_dt_records','rekod')) ?>',
          search: '<?= h(tr('profile_dt_search','Cari')) ?>:',
          zeroRecords: '<?= h(tr('profile_dt_no_records','Tiada rekod ditemui')) ?>',
          info: '<?= h(tr('profile_dt_info','Paparan _START_ hingga _END_ daripada _TOTAL_ rekod')) ?>',
          infoEmpty: '<?= h(tr('profile_dt_info_empty','Paparan 0 hingga 0 daripada 0 rekod')) ?>',
          infoFiltered: '(<?= h(tr('profile_dt_filtered','ditapis daripada _MAX_ jumlah rekod')) ?>)',
          paginate: {
            previous: '<?= h(tr('profile_dt_previous','Sebelum')) ?>',
            next: '<?= h(tr('profile_dt_next','Seterusnya')) ?>'
          }
        },
        responsive: true,
        autoWidth: false,
        stateSave: false,
        processing: false,
        deferRender: true,
        dom: '<"row mb-2"<"col-sm-12 col-md-6 dt-top-left"l><"col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right"f>>' +
          't' +
          '<"dt-bottom-row mt-2 d-flex justify-content-between align-items-center"<"dt-info-left"i><"dt-paging-right d-flex justify-content-end"p>>',
        columnDefs: [
          { orderable: false, searchable: false, targets: [0] }, // No. column (numbering)
          { orderable: true, targets: [1,4,5,6] }, // date, activity/outcome/severity
          { orderable: false, targets: [7] }, // Actions column
          { className: 'text-center', targets: [0, 5, 6, 7] },
          { targets: [2,3,4,5,6,7], render: function(data, type, row, meta){ return data; } }
        ]
      });
      // Update numbering for No. column according to applied order and paging
      const updateAuditNumbering = function() {
        try {
          const api = $('#auditEventsTable').DataTable();
          const info = (typeof api.page === 'function' && api.page.info) ? api.page.info() : { start: 0 };
          const nodes = api.rows({ order: 'applied', page: 'current' }).nodes();
          $(nodes).each(function(i, el){
            $(el).find('td').eq(0).html(info.start + i + 1);
          });
        } catch (err) {
          console.error('Audit numbering failed:', err);
        }
      };
      // Show loader for initial AJAX and subsequent reloads (if any)
      $('#auditEventsTable').on('preXhr.dt', function(){
        const loader = document.getElementById('auditEventsLoading');
        if (loader) loader.style.display = 'block';
      });
      // Hide loader and update numbering after data arrives or table redraw
      this.auditEventsDT.on('xhr.dt draw.dt', function(e, settings, json){
        const loader = document.getElementById('auditEventsLoading');
        if (loader) loader.style.display = 'none';
        updateAuditNumbering();
      });
      // Run once after init to number any server-rendered rows
      setTimeout(updateAuditNumbering, 0);
    },

    /**
     * Build and show a dynamic modal for audit metadata/change-sets
     */
    openAuditMetaModal: function(metaJson, changeSetsJson, eventId) {
      try {
        console.debug('openAuditMetaModal called', eventId);
      } catch (e) { console.debug('openAuditMetaModal debug failed', e); }

      // Parse JSON safely
      let metaObj = null;
      try { metaObj = metaJson ? JSON.parse(metaJson) : null; } catch (e) { metaObj = null; }
      let csObj = null;
      try { csObj = changeSetsJson ? JSON.parse(changeSetsJson) : null; } catch (e) { csObj = null; }

      // Robust change-set detection: try many common keys and simple recursive search
      const findChangeSets = (obj) => {
        if (!obj || typeof obj !== 'object') return null;
        const tryParse = (v) => {
          if (typeof v === 'string') {
            try { return JSON.parse(v); } catch (e) { return v; }
          }
          return v;
        };

        const looksLikeChangeArray = (a) => {
          if (!Array.isArray(a) || a.length === 0) return false;
          const sample = a[0];
          if (typeof sample !== 'object') return false;
          // check for common keys
          return ('field' in sample) || ('before' in sample) || ('after' in sample) || ('old' in sample && 'new' in sample);
        };

        const keysToTry = ['change_sets','changeSets','changes','change','change_set','diff','diffs','delta','deltas','changes_json','change_sets_json','payload','data','extra','details'];
        for (const k of keysToTry) {
          if (k in obj) {
            const v = tryParse(obj[k]);
            if (looksLikeChangeArray(v)) return v;
            if (v && typeof v === 'object' && !Array.isArray(v)) {
              // object map of field -> {before,after}
              const vals = Object.values(v);
              if (vals.length && (('before' in vals[0]) || ('after' in vals[0]) || ('old' in vals[0] && 'new' in vals[0]))) return v;
            }
            if (Array.isArray(v) && looksLikeChangeArray(v)) return v;
          }
        }

        // shallow recursive: check child objects
        for (const k of Object.keys(obj)) {
          const v = obj[k];
          if (v && typeof v === 'object') {
            const found = findChangeSets(v);
            if (found) return found;
          }
        }
        return null;
      };

      try {
        if (!csObj && metaJson) {
          const parsedMeta = metaJson ? JSON.parse(metaJson) : null;
          csObj = findChangeSets(parsedMeta) || null;
        }
      } catch (e) { /* ignore */ }

      // Helper renderers
      const renderDl = (obj, keysOrder = []) => {
        if (!obj || typeof obj !== 'object' || Array.isArray(obj)) return '<div class="text-muted">—</div>';
        const keys = keysOrder.length ? keysOrder.filter(k => obj[k] !== undefined) : Object.keys(obj);
        const otherKeys = Object.keys(obj).filter(k => !keys.includes(k)).sort();
        const allKeys = keys.concat(otherKeys);
        const pieces = ['<dl class="row mb-0 small">'];
        allKeys.forEach(k => {
          const v = obj[k];
          pieces.push(`<dt class="col-5 text-muted text-truncate">${this._escapeHtml(String(k))}</dt>`);
          let val = '';
          if (v === null || v === undefined || v === '') val = '—';
          else if (typeof v === 'object') val = `<pre class="mb-0 small bg-light p-2 rounded" style="max-height:160px; overflow:auto; white-space:pre-wrap;">${this._escapeHtml(JSON.stringify(v, null, 2))}</pre>`;
          else val = `<div class="fw-semibold text-break">${this._escapeHtml(String(v))}</div>`;
          pieces.push(`<dd class="col-7 mb-2">${val}</dd>`);
        });
        pieces.push('</dl>');
        return pieces.join('');
      };

      const renderChangeSetsTable = (cs) => {
        if (!cs) return '<div class="text-muted">Tiada perubahan</div>';
        let rows = [];
        const details = [];
        if (Array.isArray(cs) && cs.length > 0) {
          cs.forEach((ch, idx) => {
            const field = this._escapeHtml(ch.field || ch.key || `field_${idx}`);
            const beforeRaw = ch.before === undefined ? null : ch.before;
            const afterRaw = ch.after === undefined ? null : ch.after;
            const beforeCell = (beforeRaw === null) ? '—' : (typeof beforeRaw === 'object' ? `<span class="old-value text-break">${this._escapeHtml(JSON.stringify(beforeRaw))}</span>` : `<span class="old-value text-break">${this._escapeHtml(String(beforeRaw))}</span>`);
            const afterCell = (afterRaw === null) ? '—' : (typeof afterRaw === 'object' ? `<span class="new-value text-break">${this._escapeHtml(JSON.stringify(afterRaw))}</span>` : `<span class="new-value text-break">${this._escapeHtml(String(afterRaw))}</span>`);
            rows.push(`<tr data-cs-idx="${idx}"><td class="align-middle small text-muted"><button class="btn btn-sm btn-link btn-expand-change p-0 me-2" data-idx="${idx}"><i class="ri-add-line"></i></button>${field}</td><td>${beforeCell}</td><td>${afterCell}</td></tr>`);
            details.push(`<tr class="cs-detail-row" data-cs-idx="${idx}" style="display:none;"><td colspan="3"><div class="p-2 bg-light rounded"><pre class="mb-0 small" style="white-space:pre-wrap; max-height:220px; overflow:auto;">Before:\n${this._escapeHtml(JSON.stringify(beforeRaw, null, 2))}\n\nAfter:\n${this._escapeHtml(JSON.stringify(afterRaw, null, 2))}</pre></div></td></tr>`);
          });
        } else if (typeof cs === 'object') {
          Object.keys(cs).forEach((k, idx) => {
            const entry = cs[k] || {};
            const beforeRaw = entry.before === undefined ? null : entry.before;
            const afterRaw = entry.after === undefined ? null : entry.after;
            const beforeCell = (beforeRaw === null) ? '—' : (typeof beforeRaw === 'object' ? `<span class="old-value text-break">${this._escapeHtml(JSON.stringify(beforeRaw))}</span>` : `<span class="old-value text-break">${this._escapeHtml(String(beforeRaw))}</span>`);
            const afterCell = (afterRaw === null) ? '—' : (typeof afterRaw === 'object' ? `<span class="new-value text-break">${this._escapeHtml(JSON.stringify(afterRaw))}</span>` : `<span class="new-value text-break">${this._escapeHtml(String(afterRaw))}</span>`);
            rows.push(`<tr data-cs-idx="${idx}"><td class="align-middle small text-muted"><button class="btn btn-sm btn-link btn-expand-change p-0 me-2" data-idx="${idx}"><i class="ri-add-line"></i></button>${this._escapeHtml(k)}</td><td>${beforeCell}</td><td>${afterCell}</td></tr>`);
            details.push(`<tr class="cs-detail-row" data-cs-idx="${idx}" style="display:none;"><td colspan="3"><div class="p-2 bg-light rounded"><pre class="mb-0 small" style="white-space:pre-wrap; max-height:220px; overflow:auto;">Before:\n${this._escapeHtml(JSON.stringify(beforeRaw, null, 2))}\n\nAfter:\n${this._escapeHtml(JSON.stringify(afterRaw, null, 2))}</pre></div></td></tr>`);
          });
        }
        if (!rows.length) return '<div class="text-muted">Tiada perubahan</div>';
        return `<div class="table-responsive"><table class="table table-sm table-bordered audit-changes-table mb-0"><thead><tr><th class="small text-muted">Medan</th><th class="small text-muted">Sebelum</th><th class="small text-muted">Selepas</th></tr></thead><tbody>${rows.join('')}${details.join('')}</tbody></table></div>`;
      };

      const modalId = 'auditMetaDynamic-' + (eventId || Date.now());
      const prettyMeta = metaObj ? JSON.stringify(metaObj, null, 2) : '';
      const prettyCs = csObj ? JSON.stringify(csObj, null, 2) : '';
      const metaForCopy = encodeURIComponent(prettyMeta || '');
      const csForCopy = encodeURIComponent(prettyCs || '');

      const leftMetaKeysOrder = ['occurred_at','timestamp','module','action','ip','user_agent','device','browser'];
      const leftColumn = renderDl(metaObj, leftMetaKeysOrder);

      // Helper: find first available key value from root or common nested containers
      const findFirstKey = (obj, keys) => {
        if (!obj || typeof obj !== 'object') return null;
        for (const k of keys) {
          if (obj[k] !== undefined && obj[k] !== null && obj[k] !== '') return obj[k];
        }
        // common nested containers to search
        const nestedContainers = ['user','actor','subject','performed_by','payload','data','meta','details'];
        for (const nc of nestedContainers) {
          const sub = obj[nc];
          if (sub && typeof sub === 'object') {
            for (const k of keys) {
              if (sub[k] !== undefined && sub[k] !== null && sub[k] !== '') return sub[k];
            }
          }
        }
        // shallow recursive pass: check any child object for matching keys
        for (const kk of Object.keys(obj)) {
          const v = obj[kk];
          if (v && typeof v === 'object') {
            for (const k of keys) {
              if (v[k] !== undefined && v[k] !== null && v[k] !== '') return v[k];
            }
          }
        }
        return null;
      };

      const userId = findFirstKey(metaObj, ['user_id','stafID','staf_id','stafId','staf','id','user_id_internal']) || null;
      const noPekerja = findFirstKey(metaObj, ['f_nopekerja','f_nopek','nopek','f_stafID','no_pekerja','f_no','employee_no','staff_no']) || null;
      const dateVal = findFirstKey(metaObj, ['occurred_at','timestamp','time','created_at','date','datetime']) || '';

      const modalHtml = `
        <style>
          .audit-meta-modal .modal-xl { max-width: 1320px; }
          .audit-meta-modal .modal-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(15,23,42,.2);
          }
          .audit-meta-modal .modal-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #2563eb 100%);
            color: #fff;
            border-bottom: 0;
            padding: .9rem 1.1rem;
          }
          .audit-meta-modal .modal-title { color: #fff; font-weight: 700; letter-spacing: .2px; }
          .audit-meta-modal .audit-subtitle { color: rgba(255,255,255,.78); font-size: .82rem; }
          .audit-meta-modal .modal-body { background: #f8fafc; }
          .audit-meta-modal .audit-left {
            background: #fff;
            border-right: 1px solid #e2e8f0;
            min-height: 72vh;
          }
          .audit-meta-modal .audit-right { background: #f8fafc; min-height: 72vh; }
          .audit-meta-modal .audit-title { font-size: .76rem; text-transform: uppercase; letter-spacing: .08em; color: #64748b; margin-bottom: .55rem; }
          .audit-meta-modal .audit-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 999px;
            padding: .25rem .55rem;
            font-size: .76rem;
            font-weight: 600;
          }
          .audit-meta-modal .audit-summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            padding: .8rem;
            box-shadow: 0 2px 8px rgba(15,23,42,.04);
          }
          .audit-meta-modal .audit-meta-table td {
            padding: .46rem .55rem;
            font-size: .86rem;
            vertical-align: top;
            border-color: #eef2f7;
          }
          .audit-meta-modal .audit-meta-table td:first-child {
            width: 38%;
            color: #64748b;
            font-weight: 600;
          }
          .audit-meta-modal .audit-meta-table td:last-child {
            word-break: break-word;
            color: #0f172a;
          }
          .audit-meta-modal .audit-tabs .nav-link {
            border: 0;
            border-radius: 10px;
            font-weight: 600;
            color: #475569;
            background: transparent;
            margin-right: .4rem;
            padding: .45rem .85rem;
          }
          .audit-meta-modal .audit-tabs .nav-link:hover {
            color: #1d4ed8;
            background: #e8f0ff;
          }
          .audit-meta-modal .audit-tabs .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 6px 14px rgba(37,99,235,.28);
          }
          .audit-meta-modal .audit-pane-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            padding: .85rem;
          }
          .audit-meta-modal .audit-changes-table {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
          }
          .audit-meta-modal .audit-changes-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .75rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
          }
          .audit-meta-modal .audit-changes-table tbody td {
            font-size: .87rem;
            vertical-align: middle;
          }
          .audit-meta-modal .audit-changes-table tbody tr:hover { background: #f8fbff; }
          .audit-meta-modal .old-value {
            display: inline-block;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 6px;
            padding: .2rem .45rem;
            font-size: .8rem;
          }
          .audit-meta-modal .new-value {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 6px;
            padding: .2rem .45rem;
            font-size: .8rem;
          }
          .audit-meta-modal .json-block {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: .75rem;
            font-size: .8rem;
            max-height: 54vh;
            overflow: auto;
            white-space: pre-wrap;
          }
          .audit-meta-modal .btn-copy-meta-modal { min-width: 94px; }
          .audit-meta-modal .btn-download-json { min-width: 100px; }
          [data-bs-theme="dark"] .audit-meta-modal .modal-body,
          [data-bs-theme="dark"] .audit-meta-modal .audit-right { background: #0f172a; }
          [data-bs-theme="dark"] .audit-meta-modal .audit-left,
          [data-bs-theme="dark"] .audit-meta-modal .audit-summary-card,
          [data-bs-theme="dark"] .audit-meta-modal .audit-pane-card,
          [data-bs-theme="dark"] .audit-meta-modal .json-block {
            background: #111827;
            border-color: #334155;
            color: #e2e8f0;
          }
          [data-bs-theme="dark"] .audit-meta-modal .audit-meta-table td { border-color: #273246; }
          [data-bs-theme="dark"] .audit-meta-modal .audit-meta-table td:first-child { color: #94a3b8; }
          [data-bs-theme="dark"] .audit-meta-modal .audit-meta-table td:last-child { color: #e2e8f0; }
          [data-bs-theme="dark"] .audit-meta-modal .audit-tabs .nav-link { color: #94a3b8; }
          [data-bs-theme="dark"] .audit-meta-modal .audit-tabs .nav-link:hover { background: #1f2937; color: #bfdbfe; }
          [data-bs-theme="dark"] .audit-meta-modal .audit-changes-table { border-color: #334155; }
          [data-bs-theme="dark"] .audit-meta-modal .audit-changes-table thead th {
            background: #0b1220;
            color: #94a3b8;
            border-bottom-color: #334155;
          }
          [data-bs-theme="dark"] .audit-meta-modal .audit-changes-table tbody tr:hover { background: #1a2436; }
        </style>
        <div class="modal fade audit-meta-modal" id="${modalId}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <div>
                  <h5 class="modal-title mb-0">Jejak Audit</h5>
                  <div class="audit-subtitle">Event ID: ${this._escapeHtml(String(eventId || '—'))}</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body p-0">
                <div class="row g-0">
                  <div class="col-lg-5 audit-left">
                    <div class="p-4">
                      <div class="audit-title">Maklumat Ringkas</div>
                      <div class="table-responsive">
                        <table class="table table-sm audit-meta-table mb-0">
                          <tbody>
                            ${(() => {
                              try {
                                if (!metaObj || typeof metaObj !== 'object') return '<tr><td class="text-muted">Tiada maklumat</td><td>—</td></tr>';
                                const keys = ['occurred_at','module','action','ip','user_agent','device','browser','user_id','nopek'];
                                const present = keys.filter(k=> metaObj[k] !== undefined).concat(Object.keys(metaObj).filter(k=> keys.indexOf(k)===-1));
                                return present.slice(0,20).map(k=> `<tr><td>${this._escapeHtml(k)}</td><td>${this._escapeHtml(String(metaObj[k]===undefined||metaObj[k]===null||metaObj[k]===''? '—' : (typeof metaObj[k] === 'object' ? JSON.stringify(metaObj[k]) : metaObj[k])) )}</td></tr>`).join('');
                              } catch (e) { return '<tr><td class="text-muted">Data</td><td>—</td></tr>'; }
                            })()}
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-7 audit-right">
                    <div class="p-3 p-lg-4">
                      <ul class="nav audit-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" id="${modalId}-tab-summary" data-bs-toggle="tab" data-bs-target="#${modalId}-summary" type="button" role="tab">Ringkasan</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="${modalId}-tab-changes" data-bs-toggle="tab" data-bs-target="#${modalId}-changes" type="button" role="tab">Perubahan</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="${modalId}-tab-extra" data-bs-toggle="tab" data-bs-target="#${modalId}-extra" type="button" role="tab">Extra Info</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="${modalId}-tab-raw" data-bs-toggle="tab" data-bs-target="#${modalId}-raw" type="button" role="tab">Raw</button></li>
                      </ul>

                      <div class="tab-content">
                        <div class="tab-pane fade show active" id="${modalId}-summary" role="tabpanel">
                          <div class="audit-pane-card">
                            <div class="audit-title">Perubahan Utama</div>
                            <div id="${modalId}-summary-changes">${renderChangeSetsTable(csObj)}</div>
                          </div>
                        </div>

                        <div class="tab-pane fade" id="${modalId}-changes" role="tabpanel">
                          <div class="audit-pane-card">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                              <div class="input-group input-group-sm" style="max-width:300px;">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input id="${modalId}-changes-search" class="form-control" placeholder="Cari perubahan...">
                              </div>
                            </div>
                            <div id="${modalId}-cs-table">${renderChangeSetsTable(csObj)}</div>
                          </div>
                        </div>

                        <div class="tab-pane fade" id="${modalId}-extra" role="tabpanel">
                          <div class="audit-pane-card">
                            <div class="audit-title">Extra Info (readable)</div>
                            <div class="json-block">${this._escapeHtml(prettyMeta)}</div>
                          </div>
                        </div>

                        <div class="tab-pane fade" id="${modalId}-raw" role="tabpanel">
                          <div class="audit-pane-card">
                            <div class="audit-title">Raw Data</div>
                            <pre class="json-block mb-0">${this._escapeHtml(prettyMeta)}\n\n-- Changes --\n\n${this._escapeHtml(prettyCs)}</pre>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
              </div>
            </div>
          </div>
        </div>
      `;

      const wrapper = document.createElement('div');
      wrapper.innerHTML = modalHtml;
      // Move all generated children (style + modal) into document body so IDs and elements exist
      while (wrapper.firstChild) {
        document.body.appendChild(wrapper.firstChild);
      }
      const createdModal = document.getElementById(modalId);

      // Initialize interactive bits AFTER the modal is in the DOM
      try {
        if (createdModal) {
          const toggle = (btnSelector, targetSelector) => {
            createdModal.querySelectorAll(btnSelector).forEach(b => {
              b.addEventListener('click', (ev) => {
                ev.preventDefault();
                const t = createdModal.querySelector(targetSelector);
                const formatted = createdModal.querySelector(targetSelector.replace('-raw', '-formatted'));
                if (!t) return;
                if (t.style.display === 'none') { t.style.display = ''; if (formatted) formatted.style.display = 'none'; }
                else { t.style.display = 'none'; if (formatted) formatted.style.display = ''; }
              });
            });
          };
          toggle('.btn-toggle-meta-raw', `#${modalId}-meta-raw`);
          toggle('.btn-toggle-cs-raw', `#${modalId}-cs-raw`);

          const searchInput = createdModal.querySelector(`#${modalId}-changes-search`);
          if (searchInput) {
            searchInput.addEventListener('input', function(){
              const q = this.value.trim().toLowerCase();
              const table = createdModal.querySelector('.audit-changes-table');
              if (!table) return;
              const allRows = Array.from(table.tBodies[0].rows).filter(r => !r.classList.contains('cs-detail-row'));
              allRows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = q === '' || text.indexOf(q) !== -1 ? '' : 'none';
                const idx = r.getAttribute('data-cs-idx');
                const detail = createdModal.querySelector(`.cs-detail-row[data-cs-idx="${idx}"]`);
                if (detail) detail.style.display = r.style.display === 'none' ? 'none' : detail.style.display; // hide details if parent hidden
              });
            });
          }

          // Expand/collapse per-row details
          createdModal.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.btn-expand-change');
            if (!btn) return;
            ev.preventDefault();
            const idx = btn.getAttribute('data-idx');
            const detailRow = createdModal.querySelector(`.cs-detail-row[data-cs-idx="${idx}"]`);
            if (!detailRow) return;
            if (detailRow.style.display === 'none') { detailRow.style.display = ''; btn.querySelector('i')?.classList.remove('ri-add-line'); btn.querySelector('i')?.classList.add('ri-subtract-line'); }
            else { detailRow.style.display = 'none'; btn.querySelector('i')?.classList.remove('ri-subtract-line'); btn.querySelector('i')?.classList.add('ri-add-line'); }
          });

          // Download JSON handlers
          createdModal.querySelectorAll('.btn-download-json').forEach(b => {
            b.addEventListener('click', (e) => {
              e.preventDefault();
              const payload = b.getAttribute('data-json') || '{}';
              try {
                const blob = new Blob([decodeURIComponent(payload)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url; a.download = (b.getAttribute('data-fname') || 'data') + '.json';
                document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
              } catch (err) { console.error('Download failed', err); }
            });
          });

          createdModal.addEventListener('hidden.bs.modal', () => { try { createdModal.remove(); } catch (e) {} });
        }
      } catch (e) {
        console.error('Modal init error', e);
      }

      try {
        const bsModal = new bootstrap.Modal(createdModal, { backdrop: true, keyboard: true, focus: true });
        bsModal.show();
      } catch (e) {
        console.error('Failed to show audit meta modal:', e);
      }
    },
    
    /**
     * Refresh profile data via AJAX
     */
    refreshProfile: async function() {
      if (this.isLoading) return;
      
      const $btn = $('.btn-refresh-profile');
      const originalHtml = $btn.html();
      
      $btn.prop('disabled', true);
      $btn.html('<i class="ri-loader-4-line ri-spin"></i>');
      
      this.showLoading('#loginActivityLoading');
      this.showLoading('#auditEventsLoading');
      
      try {
        // Reload page untuk get fresh data (simple approach)
        // In future, boleh implement AJAX reload
        window.location.reload();
      } catch (e) {
        console.error('Refresh failed:', e);
        this.toast('Ralat memuat semula data', 'error');
        $btn.prop('disabled', false);
        $btn.html(originalHtml);
        this.hideLoading('#loginActivityLoading');
        this.hideLoading('#auditEventsLoading');
      }
    },
    
    /**
     * Initialize all event handlers
     */
    init: function() {
      // Copy button handlers
      document.querySelectorAll('.btn-copy-staf, .btn-copy-email').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const text = btn.dataset.copyValue;
          if (text) {
            this.copyText(text);
          }
        });
      });
      
      // Copy metadata button handlers (in modal)
      document.querySelectorAll('.btn-copy-meta-modal').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const metaJson = btn.dataset.metaJson;
          if (metaJson) {
            try {
              const decoded = decodeURIComponent(metaJson);
              this.copyText(decoded);
            } catch (err) {
              this.copyText(metaJson);
            }
          }
        });
      });
      
      // Kill session button handlers (delegated for dynamic content)
      document.addEventListener('click', (e) => {
        if (e.target.closest('.btn-kill-session')) {
          e.preventDefault();
          e.stopPropagation();
          const btn = e.target.closest('.btn-kill-session');
          const sessionId = btn.dataset.sessionId;
          if (sessionId) {
            this.killSession(sessionId);
          }
        }
      });

      // Open audit metadata modal for dynamic rows (delegated)
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-open-audit-modal');
        if (btn) {
          e.preventDefault();
          e.stopPropagation();
          const payload = btn.getAttribute('data-event-payload') || '';
          if (!payload) return;
          try {
            const jsonStr = atob(payload);
            const obj = JSON.parse(jsonStr);
            const metaJson = obj.meta ? JSON.stringify(obj.meta) : '';
            const changeSets = obj.change_sets ? JSON.stringify(obj.change_sets) : '';
            const eventId = obj.id || obj.event_id || '';
            this.openAuditMetaModal(metaJson, changeSets, eventId);
          } catch (err) {
            console.error('Failed to decode audit payload', err);
          }
        }
      });

      // Open audit metadata modal for server-rendered buttons
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-open-audit-meta');
        if (btn) {
          e.preventDefault();
          e.stopPropagation();
          const eventId = btn.getAttribute('data-event-id') || btn.dataset.eventId || '';
          if (!eventId) return;

          // Fetch metadata lazily to keep table payload small
          fetch('<?= base_url('ajax/profile-audit-event-meta.php') ?>?event_id=' + encodeURIComponent(eventId), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          }).then(r => r.json()).then(data => {
            const metaJson = data.meta ? JSON.stringify(data.meta) : '';
            const changeSets = data.change_sets ? JSON.stringify(data.change_sets) : '';
            this.openAuditMetaModal(metaJson, changeSets, eventId);
          }).catch(err => {
            console.error('Failed to load audit meta for event', eventId, err);
            this.toast('Gagal muat metadata acara', 'error');
          });
        }
      });

      // Delegated copy handler for dynamically created copy buttons
      document.addEventListener('click', (e) => {
        const copyBtn = e.target.closest('.btn-copy-meta-modal');
        if (copyBtn) {
          e.preventDefault();
          e.stopPropagation();
          const meta = copyBtn.getAttribute('data-meta-json') || '';
          if (meta) this.copyText(meta);
        }
      });
      
      // Refresh button removed from UI; no event binding required
      
      // Tab event handlers
      $('a[data-bs-toggle="tab"][href="#login-aktiviti-tab"]').on('shown.bs.tab', () => {
        this.waitForDataTables().then(() => {
          setTimeout(() => this.initLoginActivityTable(), CONFIG.DATATABLES_INIT_DELAY);
        }).catch(e => {
          console.error('DataTables init failed:', e);
        });
      });
      
      $('a[data-bs-toggle="tab"][href="#jejak-audit-tab"]').on('shown.bs.tab', () => {
        this.waitForDataTables().then(() => {
          setTimeout(() => this.initAuditEventsTable(), CONFIG.DATATABLES_INIT_DELAY);
        }).catch(e => {
          console.error('DataTables init failed:', e);
        });
      });
      
      // Initialize on page load
      $(document).ready(async () => {
        try {
          await this.waitForDataTables();
          setTimeout(() => {
            this.initLoginActivityTable();
            this.initAuditEventsTable();
          }, CONFIG.DATATABLES_INIT_DELAY);
        } catch (e) {
          console.error('DataTables initialization failed:', e);
          this.toast('Ralat memuat jadual data', 'error');
        }
      });
    }
  };
  
  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ProfilePage.init());
  } else {
    ProfilePage.init();
  }
  
  // Expose untuk backward compatibility (jika ada code lain yang guna)
  window.copyText = (text) => ProfilePage.copyText(text);
  window.toast = (msg, type) => ProfilePage.toast(msg, type);
})();
</script>
<div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
