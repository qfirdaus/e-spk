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

// Fallback paparan untuk student login (pra-SSO)
if (($jabatan === '' || $jawatan === '') && !empty($_SESSION['auth_type']) && $_SESSION['auth_type'] === 'student') {
  $sp = $_SESSION['student_profile'] ?? [];
  if ($jabatan === '') $jabatan = (string)($sp['fakulti'] ?? '');
  if ($jawatan === '') $jawatan = (string)($sp['program'] ?? '');
}

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

// Statistik pelajar aktif ikut tahap_pengajian (Sybase student v210)
$showStudentTahapStats = false;
$studentTahapScopeLabel = '';
$studentTahapStats = [];
$studentProgramByTahap = [];
$studentTahapTotal = 0;
try {
  $authType = (string)($_SESSION['auth_type'] ?? '');
  if ($authType !== 'student') {
    $pdoMysql = Database::getInstance('mysql')->getConnection();
    $pdoStudent = Database::getInstance('sybase_student')->getConnection();

    $activeGroupCode = (string)($profile['f_groupKod'] ?? $_SESSION['f_groupKod'] ?? ($_SESSION['user']['f_groupKod'] ?? ''));
    $activeGroupName = (string)($profile['f_groupName'] ?? ($_SESSION['user']['f_groupName'] ?? ''));
    if ($activeRoleId > 0) {
      $stmtGroup = $pdoMysql->prepare("SELECT f_groupKod, f_groupName FROM tbl_m_group WHERE f_groupID = :gid LIMIT 1");
      $stmtGroup->execute([':gid' => $activeRoleId]);
      $groupMeta = $stmtGroup->fetch(PDO::FETCH_ASSOC) ?: [];
      if (!empty($groupMeta['f_groupKod'])) $activeGroupCode = (string)$groupMeta['f_groupKod'];
      if (!empty($groupMeta['f_groupName'])) $activeGroupName = (string)$groupMeta['f_groupName'];
    }

    $isSuperAdmin = function_exists('is_user_super_admin') ? is_user_super_admin((array)$profile, $pdoMysql) : false;
    $isHepa = (stripos($activeGroupCode, 'HEPA') !== false) || (stripos($activeGroupName, 'HEPA') !== false);
    $canViewAllStats = $isSuperAdmin || $isHepa;

    $where = [
      "statuskategori = 'AKTIF'",
      "matrik IS NOT NULL",
      "COALESCE(LTRIM(RTRIM(tahap_pengajian)), '') <> ''",
    ];
    $bind = [];

    if ($canViewAllStats) {
      $studentTahapScopeLabel = 'Semua Fakulti';
      $showStudentTahapStats = true;
    } else {
      $staffJabatanKod = trim((string)($profile['f_jabatanKod'] ?? $profile['f_jabatankod'] ?? $_SESSION['f_jabatanKod'] ?? ''));
      if ($staffJabatanKod === '' && !empty($_SESSION['f_stafID'])) {
        $stmtJbtn = $pdoMysql->prepare("SELECT f_jabatanKod, f_namajabatan FROM tbl_m_user WHERE f_stafID = :sid LIMIT 1");
        $stmtJbtn->execute([':sid' => (string)$_SESSION['f_stafID']]);
        $rowJbtn = $stmtJbtn->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($rowJbtn['f_jabatanKod'])) {
          $staffJabatanKod = trim((string)$rowJbtn['f_jabatanKod']);
        }
        if (empty($profile['f_namajabatan']) && !empty($rowJbtn['f_namajabatan'])) {
          $profile['f_namajabatan'] = (string)$rowJbtn['f_namajabatan'];
        }
      }

      if ($staffJabatanKod !== '') {
        $where[] = "kdfakulti = :kdfakulti";
        $bind[':kdfakulti'] = $staffJabatanKod;
        $fakultiName = '';
        $fakultiSingkatan = '';
        try {
          $stmtFak = $pdoStudent->prepare("
            SELECT TOP 1 fakulti, fakulti_singkatan
            FROM v210
            WHERE statuskategori = 'AKTIF'
              AND kdfakulti = :kdfakulti
          ");
          $stmtFak->execute([':kdfakulti' => $staffJabatanKod]);
          $rowFak = $stmtFak->fetch(PDO::FETCH_ASSOC) ?: [];
          $fakultiName = trim((string)($rowFak['fakulti'] ?? ''));
          $fakultiSingkatan = trim((string)($rowFak['fakulti_singkatan'] ?? ''));
        } catch (Throwable $e) {
          // fallback to mysql/profile name only
        }
        if ($fakultiName === '') {
          $fakultiName = trim((string)($profile['f_namajabatan'] ?? $profile['f_jabatan'] ?? $jabatan ?? ''));
        }
        if ($fakultiName !== '' && $fakultiSingkatan !== '') {
          $studentTahapScopeLabel = $fakultiName . ' (' . $fakultiSingkatan . ')';
        } elseif ($fakultiName !== '') {
          $studentTahapScopeLabel = $fakultiName;
        } else {
          $studentTahapScopeLabel = 'Fakulti';
        }
        $showStudentTahapStats = true;
      }
    }

    if ($showStudentTahapStats) {
      $sqlTahap = "
        SELECT
          tahap_pengajian,
          COUNT(*) AS jumlah
        FROM (
          SELECT DISTINCT kdfakulti, fakulti, fakulti_singkatan, tahap_pengajian, matrik
          FROM v210
          WHERE " . implode(' AND ', $where) . "
        ) x
        GROUP BY tahap_pengajian
        ORDER BY tahap_pengajian
      ";
      $stmtTahap = $pdoStudent->prepare($sqlTahap);
      foreach ($bind as $k => $v) {
        $stmtTahap->bindValue($k, (string)$v, PDO::PARAM_STR);
      }
      $stmtTahap->execute();
      $studentTahapStats = $stmtTahap->fetchAll(PDO::FETCH_ASSOC) ?: [];
      foreach ($studentTahapStats as $rowTahap) {
        $studentTahapTotal += (int)($rowTahap['jumlah'] ?? 0);
      }

      // Pecahan program ikut tahap_pengajian (untuk paparan collapsible di card)
      $sqlProgram = "
        SELECT
          tahap_pengajian,
          program,
          COUNT(*) AS jumlah
        FROM (
          SELECT DISTINCT kdfakulti, fakulti, fakulti_singkatan, tahap_pengajian, program, matrik
          FROM v210
          WHERE " . implode(' AND ', $where) . "
        ) x
        GROUP BY tahap_pengajian, program
        ORDER BY tahap_pengajian, program
      ";
      $stmtProgram = $pdoStudent->prepare($sqlProgram);
      foreach ($bind as $k => $v) {
        $stmtProgram->bindValue($k, (string)$v, PDO::PARAM_STR);
      }
      $stmtProgram->execute();
      $programRows = $stmtProgram->fetchAll(PDO::FETCH_ASSOC) ?: [];
      foreach ($programRows as $pr) {
        $tahapKey = trim((string)($pr['tahap_pengajian'] ?? ''));
        if ($tahapKey === '') $tahapKey = 'Tidak Dinyatakan';
        $studentProgramByTahap[$tahapKey][] = [
          'program' => trim((string)($pr['program'] ?? '')) !== '' ? (string)$pr['program'] : 'Tidak Dinyatakan',
          'jumlah'  => (int)($pr['jumlah'] ?? 0),
        ];
      }
    }
  }
} catch (Throwable $e) {
  $showStudentTahapStats = false;
  $studentTahapScopeLabel = '';
  $studentTahapStats = [];
  $studentProgramByTahap = [];
  $studentTahapTotal = 0;
}

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
  <link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet">
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
          <div class="<?= $showSystemResources ? 'col-lg-8' : 'col-12' ?>">
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

            <?php if ($showStudentTahapStats): ?>
            <div class="row g-3 mt-1">
              <div class="col-12">
                <div class="dash-card">
                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">Statistik Pelajar Aktif Mengikut Tahap Pengajian</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <?php if ($studentTahapScopeLabel !== ''): ?>
                        <span class="badge bg-info-subtle text-info-emphasis"><?= h($studentTahapScopeLabel) ?></span>
                      <?php endif; ?>
                      <span class="badge bg-primary-subtle text-primary-emphasis">
                        Jumlah Pelajar: <?= h(number_format($studentTahapTotal)) ?>
                      </span>
                    </div>
                  </div>

                  <?php if (!empty($studentTahapStats)): ?>
                    <div class="student-stats-grid">
                      <?php foreach ($studentTahapStats as $idxTahap => $rowTahap): ?>
                        <?php
                          $tahap = trim((string)($rowTahap['tahap_pengajian'] ?? ''));
                          $jumlah = (int)($rowTahap['jumlah'] ?? 0);
                          if ($tahap === '') $tahap = 'Tidak Dinyatakan';
                          $collapseId = 'prog-by-tahap-' . substr(md5($tahap . '|' . (string)$idxTahap), 0, 12);
                          $programRows = $studentProgramByTahap[$tahap] ?? [];
                        ?>
                        <div class="kpi-card h-100">
                          <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="kpi-label mb-0"><?= h($tahap) ?></div>
                            <button
                              type="button"
                              class="btn btn-link btn-sm p-0 text-decoration-none js-program-toggle"
                              data-bs-target="#<?= h($collapseId) ?>"
                              aria-expanded="false"
                              aria-controls="<?= h($collapseId) ?>"
                              data-label-show="Papar Program"
                              data-label-hide="Hide Program">
                              Papar Program
                            </button>
                          </div>
                          <div class="kpi-value"><?= h(number_format($jumlah)) ?></div>
                          <div class="kpi-sub">Pelajar Aktif</div>
                          <div class="collapse mt-2" id="<?= h($collapseId) ?>">
                            <?php if (!empty($programRows)): ?>
                              <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                  <thead>
                                    <tr>
                                      <th class="small">Program</th>
                                      <th class="small text-end">Jumlah</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php foreach ($programRows as $pr): ?>
                                      <tr>
                                        <td class="small"><?= h((string)$pr['program']) ?></td>
                                        <td class="small text-end"><?= h(number_format((int)$pr['jumlah'])) ?></td>
                                      </tr>
                                    <?php endforeach; ?>
                                  </tbody>
                                </table>
                              </div>
                            <?php else: ?>
                              <div class="small text-muted">Tiada data program.</div>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <div class="muted-empty">Tiada data statistik pelajar aktif untuk dipaparkan.</div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endif; ?>

          </div>
          <?php if ($showSystemResources): ?>
          <div class="col-lg-4">
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
          </div>
          <?php endif; ?>
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
<script>
  (function(){
    const toggles = document.querySelectorAll('.js-program-toggle[data-bs-target]');
    if (!toggles.length) return;

    if (!(window.bootstrap && window.bootstrap.Collapse)) return;

    toggles.forEach(btn => {
      const target = btn.getAttribute('data-bs-target') || '';
      if (!target) return;
      const el = document.querySelector(target);
      if (!el) return;
      const instance = window.bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
      const showLabel = btn.getAttribute('data-label-show') || 'Papar Program';
      const hideLabel = btn.getAttribute('data-label-hide') || 'Hide Program';

      const setLabel = (expanded) => {
        btn.textContent = expanded ? hideLabel : showLabel;
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      };

      setLabel(el.classList.contains('show'));
      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        instance.toggle();
      });
      el.addEventListener('shown.bs.collapse', () => setLabel(true));
      el.addEventListener('hidden.bs.collapse', () => setLabel(false));
    });
  })();
</script>
</body>
</html>
