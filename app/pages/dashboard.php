<?php
// pages/dashboard.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/DashboardController.php';
$controller = new DashboardController();

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function t(string $key, string $fallback): string {
  $v = __($key);
  return ($v === $key || $v === null || $v === '') ? $fallback : (string)$v;
}

$profile = $controller->profile ?? ($_SESSION['profile'] ?? []);
$userName = $profile['f_nama'] ?? $profile['nama'] ?? ($_SESSION['user_name'] ?? 'User');
$avatarUrl = $profile['avatar_url'] ?? $profile['avatar'] ?? base_url('assets/images/no-image.jpg');
$jawatan = $profile['jawatan'] ?? $profile['f_jawatan'] ?? '';
$jabatan = $profile['jabatan'] ?? $profile['f_jabatan'] ?? '';
$lastLoginRaw = $profile['last_login'] ?? ($_SESSION['last_login'] ?? '');

// Fill jabatan/jawatan from tbl_m_user by f_stafID (if missing)
try {
  if (($jabatan === '' || $jawatan === '') && !empty($_SESSION['f_stafID'])) {
    $pdo = Database::getInstance('mysql')->getConnection();
    $stmt = $pdo->prepare("SELECT f_jawatan, f_namajabatan FROM tbl_m_user WHERE f_stafID = :sid LIMIT 1");
    $stmt->execute([':sid' => (string)$_SESSION['f_stafID']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    if ($jabatan === '' && !empty($row['f_namajabatan'])) $jabatan = (string)$row['f_namajabatan'];
    if ($jawatan === '' && !empty($row['f_jawatan'])) $jawatan = (string)$row['f_jawatan'];
  }
} catch (Throwable $e) {
  // keep fallback
}

// Last login from audit_session (if available)
try {
  $pdo = Database::getInstance('mysql')->getConnection();
  $userId = (int)($profile['f_userID'] ?? $profile['user_id'] ?? 0);
  $nopek = (string)($profile['f_nopekerja'] ?? $profile['nopekerja'] ?? $_SESSION['f_nopekerja'] ?? '');
  if ($userId > 0 || $nopek !== '') {
    $sql = "SELECT started_at FROM audit_session WHERE " .
           ($userId > 0 ? "user_id = :uid OR " : "") .
           "user_nopekerja = :nopek ORDER BY started_at DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    if ($userId > 0) $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':nopek', $nopek, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['started_at'])) {
      $lastLoginRaw = (string)$row['started_at'];
    }
  }
} catch (Throwable $e) {
  // keep fallback
}

function fmt_dt($v): string {
  if (!$v) return '-';
  $ts = is_numeric($v) ? (int)$v : strtotime((string)$v);
  if (!$ts) return (string)$v;
  return date('d/m/Y H:i', $ts);
}
$lastLogin = fmt_dt($lastLoginRaw);

$dashboard = [];
if (method_exists($controller, 'getBaseDashboardData')) {
  try { $dashboard = $controller->getBaseDashboardData() ?? []; } catch (Throwable $e) { $dashboard = []; }
} elseif (property_exists($controller, 'dashboard')) {
  $dashboard = $controller->dashboard ?? [];
}

$kpis = $dashboard['kpis'] ?? [];
$quickActions = $dashboard['actions'] ?? [];
$activity = $dashboard['activity'] ?? [];
$health = $dashboard['health'] ?? [];
$announcements = $dashboard['announcements'] ?? [];

$activeRoleId = (int)($_SESSION['group_active_id'] ?? ($profile['f_groupID'] ?? 0));

// ===== System Resources (OPTIONAL, admin-only) =====
// SECURITY CRITICAL – DO NOT MODIFY: admin-only gating for optional resources panel
$roleAdminSaId = defined('PRESTASI_ROLE_ID_ADM_SA') ? (int)PRESTASI_ROLE_ID_ADM_SA : 0;
$roleAdminPeId = defined('PRESTASI_ROLE_ID_ADM_PE') ? (int)PRESTASI_ROLE_ID_ADM_PE : 0;
$isSystemAdmin = ($activeRoleId > 0) && in_array($activeRoleId, array_filter([$roleAdminSaId, $roleAdminPeId]), true);
$showSystemResources = (defined('ENABLE_SYSTEM_RESOURCES') && ENABLE_SYSTEM_RESOURCES) && $isSystemAdmin;

function resourceStatus(float $pct, float $okMax, float $warnMax): string {
  if ($pct < $okMax) return 'OK';
  if ($pct < $warnMax) return 'Warning';
  return 'Critical';
}

$systemResources = [];
$cpuUsagePct = null;
$memUsagePct = null;
if ($showSystemResources) {
  // CPU Usage (best-effort)
$cpuInfo = ['usage' => null, 'status' => 'Unknown'];
  if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    $load1 = is_array($load) ? (float)$load[0] : null;
    $cpuCores = null;
    $cpuInfoFile = '/proc/cpuinfo';
    if (is_file($cpuInfoFile) && is_readable($cpuInfoFile)) {
      $cpuCores = preg_match_all('/^processor\\s*:/m', (string)@file_get_contents($cpuInfoFile));
    }
    if ($load1 !== null && $cpuCores && $cpuCores > 0) {
      $pct = min(100, max(0, ($load1 / $cpuCores) * 100));
      $cpuInfo = ['usage' => $pct, 'status' => resourceStatus($pct, 70, 85)];
      $cpuUsagePct = $pct;
    }
  }

  // Memory Usage (best-effort)
  $memInfo = ['usage' => null, 'status' => 'Unknown'];
  $memInfoFile = '/proc/meminfo';
  if (is_file($memInfoFile) && is_readable($memInfoFile)) {
    $data = (string)@file_get_contents($memInfoFile);
    if (preg_match('/MemTotal:\\s+(\\d+)/', $data, $m1) && preg_match('/MemAvailable:\\s+(\\d+)/', $data, $m2)) {
      $total = (float)$m1[1];
      $avail = (float)$m2[1];
      if ($total > 0) {
        $usedPct = min(100, max(0, (1 - ($avail / $total)) * 100));
        $memInfo = ['usage' => $usedPct, 'status' => resourceStatus($usedPct, 75, 90)];
        $memUsagePct = $usedPct;
      }
    }
  }

  // Disk Usage
  $basePath = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
  $free = @disk_free_space($basePath);
  $total = @disk_total_space($basePath);
  $diskInfo = ['usage' => null, 'status' => 'Unknown'];
  if ($free !== false && $total !== false && $total > 0) {
    $usedPct = min(100, max(0, (1 - ($free / $total)) * 100));
    $diskInfo = ['usage' => $usedPct, 'status' => resourceStatus($usedPct, 80, 90)];
  }

  $systemResources = [
    ['name' => 'CPU', 'usage' => $cpuInfo['usage'], 'status' => $cpuInfo['status']],
    ['name' => 'Memory', 'usage' => $memInfo['usage'], 'status' => $memInfo['status']],
    ['name' => 'Disk', 'usage' => $diskInfo['usage'], 'status' => $diskInfo['status']],
  ];
}

$statusLabelMap = [
  'OK' => t('dashboard_status_ok','OK'),
  'Warning' => t('dashboard_status_warning','Warning'),
  'Critical' => t('dashboard_status_critical','Critical'),
  'Unknown' => t('dashboard_status_unknown','Unknown'),
  'Degraded' => t('dashboard_status_degraded','Degraded'),
];
$resourceLabelMap = [
  'CPU' => t('dashboard_resource_cpu','CPU'),
  'Memory' => t('dashboard_resource_memory','Memory'),
  'Disk' => t('dashboard_resource_disk','Disk'),
];

// CPU sparkline history (last 5 minutes, 10 points max)
$cpuHistory = [];
if ($showSystemResources) {
  $now = time();
  $hist = $_SESSION['sysres_cpu_history'] ?? [];
  if (is_array($hist)) {
    $hist = array_filter($hist, function($p) use ($now) {
      return is_array($p) && isset($p['t'], $p['v']) && ($now - (int)$p['t'] <= 300);
    });
  } else {
    $hist = [];
  }
  if ($cpuUsagePct !== null) {
    $hist[] = ['t' => $now, 'v' => (float)$cpuUsagePct];
  }
  // keep last 10 points
  $hist = array_slice($hist, -10);
  $_SESSION['sysres_cpu_history'] = $hist;
  $cpuHistory = array_map(fn($p) => (float)$p['v'], $hist);
}

// ===== System Health Check (read-only, safe) =====
$healthChecks = [];
function addHealthCheck(array &$list, string $name, string $status, string $info): void {
  $list[] = ['name' => $name, 'status' => $status, 'info' => $info];
}

// 1) Database Connection
try {
  $pdo = Database::getInstance('mysql')->getConnection();
  $pdo->query('SELECT 1');
  addHealthCheck($healthChecks, t('dashboard_health_db','Database'), 'OK', t('dashboard_health_connected','Connected'));
} catch (Throwable $e) {
  addHealthCheck($healthChecks, t('dashboard_health_db','Database'), 'Critical', t('dashboard_health_conn_failed','Connection failed'));
}

// 2) Application Status (config + bootstrap)
$appOk = class_exists('Config') && function_exists('base_url');
addHealthCheck(
  $healthChecks,
  t('dashboard_health_app','Application'),
  $appOk ? 'OK' : 'Degraded',
  $appOk ? t('dashboard_health_bootstrap_ok','Bootstrap loaded') : t('dashboard_health_config_incomplete','Configuration incomplete')
);

// 3) Storage / Disk Space
$basePath = realpath(__DIR__ . '/..') ?: (__DIR__ . '/..');
$free = @disk_free_space($basePath);
$total = @disk_total_space($basePath);
if ($free !== false && $total !== false && $total > 0) {
  $pct = (int)floor(($free / $total) * 100);
  if ($pct < 10) $st = 'Critical';
  elseif ($pct < 20) $st = 'Warning';
  else $st = 'OK';
  $freeTpl = t('dashboard_health_storage_free','%s%% free');
  addHealthCheck($healthChecks, t('dashboard_health_storage','Storage'), $st, sprintf($freeTpl, $pct));
} else {
  addHealthCheck($healthChecks, t('dashboard_health_storage','Storage'), 'Unknown', t('dashboard_health_unavailable','Unavailable'));
}

// 4) Cache Status (folder accessibility)
$cacheDir = realpath(__DIR__ . '/../cache') ?: (__DIR__ . '/../cache');
if (is_dir($cacheDir)) {
  $cacheWritable = is_writable($cacheDir);
  addHealthCheck(
    $healthChecks,
    t('dashboard_health_cache','Cache'),
    $cacheWritable ? 'OK' : 'Warning',
    $cacheWritable ? t('dashboard_health_enabled','Enabled') : t('dashboard_health_readonly','Read-only')
  );
} else {
  addHealthCheck($healthChecks, t('dashboard_health_cache','Cache'), 'Warning', t('dashboard_health_disabled','Disabled'));
}

// 5) Environment / Debug Safety
$env = function_exists('is_development_mode') && is_development_mode() ? 'development' : 'production';
$debugOn = (ini_get('display_errors') === '1' || ini_get('display_errors') === 1);
$envStatus = ($env === 'production' && $debugOn) ? 'Warning' : 'OK';
$envLabel = ($env === 'production') ? t('dashboard_env_production', 'production') : t('dashboard_env_development', 'development');
$debugLabel = $debugOn ? t('dashboard_env_debug_on', 'debug ON') : t('dashboard_env_debug_off', 'debug OFF');
$envInfo = $envLabel . ' (' . $debugLabel . ')';
addHealthCheck($healthChecks, 'Environment', $envStatus, $envInfo);

// 6) Audit / Log Writable
$logDir = realpath(__DIR__ . '/../logs') ?: (__DIR__ . '/../logs');
$logWritable = is_dir($logDir) && is_writable($logDir);
addHealthCheck(
  $healthChecks,
  t('dashboard_health_audit','Audit/Log'),
  $logWritable ? 'OK' : 'Critical',
  $logWritable ? t('dashboard_health_writable','Writable') : t('dashboard_health_not_writable','Not writable')
);

// 7) Scheduled Jobs / Cron (best-effort)
$cronInfo = t('dashboard_health_unknown','Unknown');
$cronStatus = 'Unknown';
$cronFiles = [
  __DIR__ . '/../cache/cron_last_run.txt',
  __DIR__ . '/../cache/last_cron_run.txt',
  __DIR__ . '/../cache/cron.last'
];
foreach ($cronFiles as $f) {
  if (is_file($f)) {
    $ts = trim((string)@file_get_contents($f));
    if ($ts !== '') {
      $cronInfo = $ts;
      $cronStatus = 'OK';
    }
    break;
  }
}
addHealthCheck($healthChecks, t('dashboard_health_cron','Scheduled Jobs'), $cronStatus, $cronInfo);

// 8) Time & Timezone
$tzApp = date_default_timezone_get();
$tzIni = (string)ini_get('date.timezone');
$tzStatus = ($tzIni !== '' && $tzIni === $tzApp) ? 'OK' : 'Warning';
$tzInfo = $tzApp . ($tzIni ? (' / ini: ' . $tzIni) : '');
addHealthCheck($healthChecks, t('dashboard_health_tz','Time & Timezone'), $tzStatus, $tzInfo);
?>
<!DOCTYPE html>
<html lang="<?= h($controller->lang ?? 'ms') ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    .profile-card { overflow: hidden; border-radius: 18px; }
    .profile-hero {
      position: relative;
      border-radius: 16px;
      background: linear-gradient(135deg, rgba(59,130,246,.10), rgba(16,185,129,.10));
      padding: 1.25rem 1.25rem 1rem 1.25rem;
      border: 1px solid rgba(15, 23, 42, 0.06);
    }
    [data-bs-theme="dark"] .profile-hero {
      border-color: rgba(255,255,255,0.08);
      background: linear-gradient(135deg, rgba(59,130,246,.18), rgba(16,185,129,.12));
    }
    .profile-hero .avatar{
      width: 96px; height: 96px; border-radius: 50%;
      object-fit: cover; border: 3px solid rgba(0,0,0,.06);
      box-shadow: 0 6px 20px rgba(0,0,0,.08);
      background:#fff;
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

    .dash-card {
      background: #fff;
      border: 1px solid rgba(15, 23, 42, 0.06);
      border-radius: 14px;
      padding: 1rem 1.25rem;
      height: 100%;
    }
    [data-bs-theme="dark"] .dash-card { background: #0f172a; border-color: rgba(255,255,255,0.08); }

    .kpi-card {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      padding: 1rem;
      border-radius: 14px;
      background: #f8fafc;
      border: 1px solid rgba(15, 23, 42, 0.06);
      height: 100%;
    }
    [data-bs-theme="dark"] .kpi-card { background: #0b1220; }
    .kpi-label { font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; }
    .kpi-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; }
    [data-bs-theme="dark"] .kpi-value { color: #e2e8f0; }
    .kpi-sub { font-size: 0.8rem; color: #94a3b8; }

    .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; }
    .action-card {
      border: 1px dashed rgba(15, 23, 42, 0.15);
      border-radius: 12px;
      padding: 0.9rem 1rem;
      text-decoration: none;
      color: inherit;
      display: flex;
      gap: 0.6rem;
      align-items: center;
      background: #ffffff;
    }
    .action-card:hover { border-style: solid; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08); }
    [data-bs-theme="dark"] .action-card { background: #0f172a; }
    .action-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; background: rgba(2, 132, 199, 0.12); color: #0369a1; }

    .table-neutral th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
    .table-neutral td { vertical-align: top; }

    .muted-empty { color: #94a3b8; font-size: 0.9rem; }
    .resource-panel { position: sticky; top: 1rem; }
    .sparkline { display: inline-flex; align-items: flex-end; gap: 2px; height: 24px; vertical-align: middle; }
    .sparkline .bar { width: 5px; min-height: 6px; background: #94a3b8; border-radius: 2px; opacity: .9; display: inline-block; }
    .sparkline.ok .bar { background: #16a34a; }
    .sparkline.warn .bar { background: #f59e0b; }
    .sparkline.crit .bar { background: #ef4444; }
    .mini-progress { height: 6px; background: rgba(15,23,42,.08); border-radius: 999px; overflow: hidden; width: 80px; display: inline-block; vertical-align: middle; }
    .mini-progress .fill { height: 100%; background: #16a34a; display: block; }
    .mini-progress.warn .fill { background: #f59e0b; }
    .mini-progress.crit .fill { background: #ef4444; }
    .sysres-loader{
      display: inline-flex;
      align-items: center;
    }
  </style>
</head>
<body data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>" data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>" data-layout="vertical">

<div class="wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <div class="row mb-3">
          <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
              <h4 class="page-title mb-0"><?= h(t('dashboard_title','Dashboard')) ?></h4>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item active"><?= h(t('dashboard_breadcrumb','Dashboard')) ?></li>
                </ol>
              </nav>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-lg-8">
            <div class="profile-card">
              <div class="profile-hero">
                <div class="d-flex align-items-start gap-3 flex-nowrap justify-content-between">
                  <div class="position-relative">
                    <img src="<?= h($avatarUrl) ?>"
                         alt="Avatar"
                         class="avatar"
                         onerror="this.onerror=null;this.src='<?= h(base_url('assets/images/no-image.jpg')) ?>';">
                  </div>
                  <div class="flex-grow-1">
                    <div class="display-name fs-5 mb-2"><?= h(t('dashboard_welcome','Welcome')) ?>, <?= h($userName) ?></div>
                    <div class="subline d-flex flex-wrap gap-2">
                      <?php if ($jabatan !== ''): ?>
                        <span class="chip"><i class="ri-building-2-line"></i> <?= h($jabatan) ?></span>
                      <?php endif; ?>
                      <?php if ($jawatan !== ''): ?>
                        <span class="chip"><i class="ri-briefcase-2-line"></i> <?= h($jawatan) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="ms-auto text-end align-self-start" style="min-width: 160px; margin-left: auto; padding-top: 2px;">
                    <div class="text-muted small"><?= h(t('dashboard_last_login','Last login')) ?></div>
                    <div class="fw-semibold"><?= h($lastLogin) ?></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card border-0 shadow-sm">
              <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(t('dashboard_tabs_label','Dashboard tabs')) ?>">
                <li class="nav-item">
                  <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview" role="tab"><?= h(t('dashboard_tab_overview','Overview')) ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-activity" role="tab"><?= h(t('dashboard_tab_activity','My Activity')) ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-tasks" role="tab"><?= h(t('dashboard_tab_tasks','My Tasks')) ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-access" role="tab"><?= h(t('dashboard_tab_access','Access & Roles')) ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-security" role="tab"><?= h(t('dashboard_tab_security','Security')) ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-health" role="tab"><?= h(t('dashboard_tab_health','System Health Check')) ?></a>
                </li>
              </ul>
              <div class="tab-content p-4">
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                  <div class="muted-empty"><?= h(t('dashboard_tab_overview_empty','Overview content will appear here.')) ?></div>
                </div>
                <div class="tab-pane fade" id="tab-activity" role="tabpanel">
                  <div class="muted-empty"><?= h(t('dashboard_tab_activity_empty','My Activity content will appear here.')) ?></div>
                </div>
                <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
                  <div class="muted-empty"><?= h(t('dashboard_tab_tasks_empty','My Tasks content will appear here.')) ?></div>
                </div>
                <div class="tab-pane fade" id="tab-access" role="tabpanel">
                  <div class="muted-empty"><?= h(t('dashboard_tab_access_empty','Access & Roles content will appear here.')) ?></div>
                </div>
                <div class="tab-pane fade" id="tab-security" role="tabpanel">
                  <div class="muted-empty"><?= h(t('dashboard_tab_security_empty','Security content will appear here.')) ?></div>
                </div>
                <div class="tab-pane fade" id="tab-health" role="tabpanel">
                  <div class="table-responsive">
                    <table class="table table-sm table-hover table-neutral mb-0">
                      <thead>
                        <tr>
                          <th><?= h(t('dashboard_health_col_check','Check')) ?></th>
                          <th><?= h(t('dashboard_health_col_status','Status')) ?></th>
                          <th><?= h(t('dashboard_health_col_info','Info')) ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($healthChecks as $hc):
                          $status = $hc['status'] ?? 'Unknown';
                          $badgeClass = 'bg-secondary';
                          if ($status === 'OK') $badgeClass = 'bg-success';
                          elseif ($status === 'Warning' || $status === 'Degraded') $badgeClass = 'bg-warning';
                          elseif ($status === 'Critical') $badgeClass = 'bg-danger';
                        ?>
                        <tr>
                          <td><?= h($hc['name'] ?? '-') ?></td>
                          <td><span class="badge <?= h($badgeClass) ?>"><?= h($statusLabelMap[$status] ?? $status) ?></span></td>
                          <td><?= h($hc['info'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <?php if ($showSystemResources): ?>
              <div class="dash-card resource-panel">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0"><?= h(t('dashboard_resources_title','System Resources')) ?></h5>
                <div class="d-flex align-items-center gap-2">
                  <span class="sysres-loader d-none">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  </span>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSysResRefresh" title="<?= h(t('dashboard_refresh','Refresh')) ?>">
                    <i class="ri-refresh-line"></i>
                  </button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm table-hover table-neutral mb-0" id="sysResTable">
                  <thead>
                    <tr>
                      <th><?= h(t('dashboard_resources_col_resource','Resource')) ?></th>
                      <th><?= h(t('dashboard_resources_col_usage','Usage')) ?></th>
                      <th><?= h(t('dashboard_resources_col_status','Status')) ?></th>
                    </tr>
                  </thead>
                  <tbody id="sysResBody">
                    <?php foreach ($systemResources as $r):
                      $status = $r['status'] ?? 'Unknown';
                        $badgeClass = 'bg-secondary';
                        if ($status === 'OK') $badgeClass = 'bg-success';
                        elseif ($status === 'Warning') $badgeClass = 'bg-warning';
                        elseif ($status === 'Critical') $badgeClass = 'bg-danger';
                      ?>
                    <tr>
                      <td><?= h($resourceLabelMap[$r['name'] ?? ''] ?? ($r['name'] ?? '-')) ?></td>
                      <td>
                        <span class="d-inline-flex align-items-center gap-2">
                          <span>
                            <?php
                          $usageText = $r['usage'] === null ? t('dashboard_status_unknown','Unknown') : h(number_format((float)$r['usage'], 0)) . '%';
                              echo $usageText;
                            ?>
                          </span>
                          <?php if (($r['name'] ?? '') === 'CPU'): ?>
                          <?php
                            $sparkClass = ($status === 'OK') ? 'ok' : (($status === 'Warning') ? 'warn' : (($status === 'Critical') ? 'crit' : ''));
                            $points = $cpuHistory ?: [];
                            if (empty($points) && $cpuUsagePct !== null) {
                              $points = array_fill(0, 5, (float)$cpuUsagePct);
                            }
                            if (!empty($points) && count($points) < 5) {
                              $last = (float)end($points);
                              while (count($points) < 5) { $points[] = $last; }
                            }
                          ?>
                          <span class="sparkline <?= h($sparkClass) ?>">
                            <?php if (!empty($points)): ?>
                              <?php foreach ($points as $v):
                                $h = max(8, min(24, (int)round(($v / 100) * 24)));
                              ?>
                                <span class="bar" style="height: <?= $h ?>px;"></span>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <span class="bar" style="height: 6px;"></span>
                              <span class="bar" style="height: 6px;"></span>
                              <span class="bar" style="height: 6px;"></span>
                            <?php endif; ?>
                          </span>
                          <?php elseif (($r['name'] ?? '') === 'Memory'): ?>
                          <?php
                            $memPct = $memUsagePct;
                            $pClass = ($status === 'OK') ? '' : (($status === 'Warning') ? 'warn' : (($status === 'Critical') ? 'crit' : ''));
                            $fill = $memPct === null ? 0 : (int)round(max(0, min(100, $memPct)));
                          ?>
                          <span class="mini-progress <?= h($pClass) ?>">
                            <span class="fill" style="width: <?= $fill ?>%;"></span>
                          </span>
                          <?php endif; ?>
                        </span>
                      </td>
                      <td><span class="badge <?= h($badgeClass) ?>"><?= h($statusLabelMap[$status] ?? $status) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <?php else: ?>
              <div class="dash-card resource-panel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h5 class="mb-0"><?= h(t('dashboard_announcements_title','Announcements')) ?></h5>
                  <span class="text-muted small"><?= h(t('dashboard_announcements_sub','System notices')) ?></span>
                </div>
                <?php if (!empty($announcements) && is_array($announcements)): ?>
                  <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($announcements, 0, 6) as $note): ?>
                      <li class="list-group-item px-0">
                        <div class="fw-semibold"><?= h($note['title'] ?? t('dashboard_notice','Notice')) ?></div>
                        <div class="text-muted small"><?= h($note['body'] ?? '-') ?></div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="muted-empty"><?= h(t('dashboard_announcements_empty','No announcements.')) ?></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
<script>
  (function(){
    const btn = document.getElementById('btnSysResRefresh');
    const body = document.getElementById('sysResBody');
    const loader = document.querySelector('.sysres-loader');
    const i18nStatus = {
      OK: <?= json_encode(t('dashboard_status_ok','OK')) ?>,
      Warning: <?= json_encode(t('dashboard_status_warning','Warning')) ?>,
      Critical: <?= json_encode(t('dashboard_status_critical','Critical')) ?>,
      Unknown: <?= json_encode(t('dashboard_status_unknown','Unknown')) ?>,
      Degraded: <?= json_encode(t('dashboard_status_degraded','Degraded')) ?>
    };
    const i18nUnknown = <?= json_encode(t('dashboard_status_unknown','Unknown')) ?>;
    const i18nResource = {
      CPU: <?= json_encode(t('dashboard_resource_cpu','CPU')) ?>,
      Memory: <?= json_encode(t('dashboard_resource_memory','Memory')) ?>,
      Disk: <?= json_encode(t('dashboard_resource_disk','Disk')) ?>
    };
    const badgeClass = (status) => {
      if (status === 'OK') return 'bg-success';
      if (status === 'Warning') return 'bg-warning';
      if (status === 'Critical') return 'bg-danger';
      return 'bg-secondary';
    };

    function renderSparkline(points, status){
      const cls = status === 'OK' ? 'ok' : (status === 'Warning' ? 'warn' : (status === 'Critical' ? 'crit' : ''));
      if (!Array.isArray(points) || points.length === 0) {
        points = [0,0,0];
      }
      if (points.length < 5) {
        const last = points[points.length - 1] || 0;
        while (points.length < 5) points.push(last);
      }
      const bars = points.map(v => {
        const h = Math.max(8, Math.min(24, Math.round((v / 100) * 24)));
        return `<span class="bar" style="height:${h}px;"></span>`;
      }).join('');
      return `<span class="sparkline ${cls}">${bars}</span>`;
    }

    function renderProgress(pct, status){
      const cls = status === 'Warning' ? 'warn' : (status === 'Critical' ? 'crit' : '');
      const fill = pct === null ? 0 : Math.round(Math.max(0, Math.min(100, pct)));
      return `<span class="mini-progress ${cls}"><span class="fill" style="width:${fill}%;"></span></span>`;
    }

    async function refreshResources(){
      if (!btn || !body) return;
      btn.disabled = true;
      if (loader) loader.classList.remove('d-none');
      try {
        const r = await fetch('<?= h(base_url('ajax/system-resources.php')) ?>', {
          method: 'POST',
          headers: { 'Accept': 'application/json' }
        });
        const j = await r.json();
        if (!r.ok || !j || j.error) return;
        const cpuHistory = Array.isArray(j.cpu_history) ? j.cpu_history : [];
        const rows = (j.resources || []).map(res => {
          const name = res.name || '-';
          const status = res.status || 'Unknown';
          const usageText = res.usage === null ? i18nUnknown : `${Math.round(res.usage)}%`;
          let usageHtml = `<span class="d-inline-flex align-items-center gap-2"><span>${usageText}</span>`;
          if (name === 'CPU') {
            usageHtml += renderSparkline(cpuHistory, status);
          } else if (name === 'Memory') {
            usageHtml += renderProgress(res.usage, status);
          }
          usageHtml += `</span>`;
          return `<tr>
            <td>${i18nResource[name] || name}</td>
            <td>${usageHtml}</td>
            <td><span class="badge ${badgeClass(status)}">${i18nStatus[status] || status}</span></td>
          </tr>`;
        }).join('');
        body.innerHTML = rows || '';
      } catch (e) {
        // silent fail
      } finally {
        if (loader) loader.classList.add('d-none');
        btn.disabled = false;
      }
    }

    btn?.addEventListener('click', function(e){
      e.preventDefault();
      refreshResources();
    });
  })();
</script>
</body>
</html>
