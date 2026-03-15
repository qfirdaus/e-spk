<?php
// pages/tetapan-sistem.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

/* ================= Authorization Check ================= */
// Akses halaman dikawal melalui menu & kumpulan pengguna (tiada semakan role di page)

/**
 * ⚠️ JANGAN tutup session sebelum controller proses POST.
 * Jika nak lepaskan lock, buat HANYA untuk GET:
 *
 * if ($_SERVER['REQUEST_METHOD'] === 'GET' && session_status() === PHP_SESSION_ACTIVE) session_write_close();
 */

/* ================= CSRF Protection ================= */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Paksa temp/cache path dalam root projek (elak open_basedir C:\Windows\TEMP)
$TS_CACHE_DIR = realpath(__DIR__ . '/../cache/ts') ?: (__DIR__ . '/../cache/ts');
if (!is_dir($TS_CACHE_DIR)) {
    @mkdir($TS_CACHE_DIR, 0777, true);
}

/* ========= Micro-cache helper (APCu -> file) ========= */
// Cache TTL akan guna constants dari SystemConfigConstants
function ts_cache_supported(): bool {
  return function_exists('apcu_fetch') || (function_exists('apcu_enabled') && apcu_enabled());
}
function ts_cache_key(string $name): string {
  return 'tetapan-sistem:v1:' . $name;
}
// Gunakan cache dir dalam projek (elak open_basedir C:\Windows\TEMP)
function ts_cache_dir(): string {
  static $dir = null;
  if ($dir === null) {
    $dir = isset($GLOBALS['TS_CACHE_DIR']) ? (string)$GLOBALS['TS_CACHE_DIR'] : (__DIR__ . '/../cache/ts');
    $real = realpath($dir);
    if ($real !== false) $dir = $real;
  }
  return $dir;
}
function ts_cache_get(string $key, int $ttl = 600) {
  if (ts_cache_supported() && function_exists('apcu_fetch')) {
    try {
      $ok = false;
      $v = apcu_fetch($key, $ok);
      if ($ok) return $v;
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] APCu fetch failed for key {$key}: " . $e->getMessage());
    }
  }
  $f = ts_cache_dir().'/ts-cache-'.md5($key).'.json';
  if (is_file($f) && filemtime($f) > time()-$ttl) {
    try {
      $raw = file_get_contents($f);
      if ($raw !== false) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
          return $decoded;
        }
      }
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] File cache read failed for key {$key}: " . $e->getMessage());
    }
  }
  return null;
}
function ts_cache_set(string $key, $val, int $ttl = 600): void {
  if (ts_cache_supported() && function_exists('apcu_store')) {
    try {
      apcu_store($key, $val, $ttl);
      return;
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] APCu store failed for key {$key}: " . $e->getMessage());
      // Fall through to file cache
    }
  }
  $f = ts_cache_dir().'/ts-cache-'.md5($key).'.json';
  try {
    $json = json_encode($val, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if ($json !== false) {
      file_put_contents($f, $json, LOCK_EX);
      // Set secure permissions (owner read/write only)
      if (file_exists($f)) {
        @chmod($f, 0600);
      }
    }
  } catch (\Throwable $e) {
    error_log("[TetapanSistem] File cache write failed for key {$key}: " . $e->getMessage());
  }
}

/* ================= Controller & data ================= */
require_once __DIR__ . '/../controllers/TetapanSistemController.php';
require_once __DIR__ . '/../classes/SystemConfigConstants.php';
$controller = new TetapanSistemController();
$controller->handleRequest(); // Handle POST requests

$lang     = $controller->lang;
$profile  = $controller->profile;
$version  = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));

/* ===== Micro-cache data ringan dengan TTL berbeza ===== */
$dbAktif = ts_cache_get(ts_cache_key('dbcfg'), SystemConfigConstants::CACHE_TTL_DB_CONFIG);
if (!is_array($dbAktif)) { 
  $dbAktif = $controller->getActiveDBConfig(); 
  ts_cache_set(ts_cache_key('dbcfg'), $dbAktif, SystemConfigConstants::CACHE_TTL_DB_CONFIG); 
}

$mysqlInfo = ts_cache_get(ts_cache_key('mysqlinfo'), SystemConfigConstants::CACHE_TTL_MYSQL_INFO);
if (!is_array($mysqlInfo)) { 
  $mysqlInfo = $controller->getMysqlInfo(); 
  ts_cache_set(ts_cache_key('mysqlinfo'), $mysqlInfo, SystemConfigConstants::CACHE_TTL_MYSQL_INFO); 
}

$emailSettings = ts_cache_get(ts_cache_key('email'), SystemConfigConstants::CACHE_TTL_EMAIL);
if (!is_array($emailSettings)) { 
  $emailSettings = $controller->getEmailSettings(); 
  ts_cache_set(ts_cache_key('email'), $emailSettings, SystemConfigConstants::CACHE_TTL_EMAIL); 
}

$languageData  = ts_cache_get(ts_cache_key('lang'), SystemConfigConstants::CACHE_TTL_LANGUAGE);
if (!is_array($languageData)) { 
  $languageData  = $controller->getLanguageList(); 
  ts_cache_set(ts_cache_key('lang'), $languageData, SystemConfigConstants::CACHE_TTL_LANGUAGE); 
}
$senaraiBahasa = $languageData['list']   ?? [];
$bahasaAktif   = $languageData['active'] ?? [];

/* ===== Tentukan DB aktif untuk UI — ikut SSoT runtime (session/constant) dahulu ===== */
// 1) Session / constant (ditetapkan oleh init.php)
$activeBase = $_SESSION['SYBASE_ACTIVE_BASE'] ?? (defined('SYBASE_ACTIVE_BASE') ? (string)SYBASE_ACTIVE_BASE : null);
$activeBase_ASIS = $_SESSION['SYBASE_ACTIVE_BASE_ASIS'] ?? (defined('SYBASE_ACTIVE_BASE_ASIS') ? (string)SYBASE_ACTIVE_BASE_ASIS : null);

// 2) Persisted DB config (system)
if (!$activeBase) {
  $configModelUI = new Config(Database::getInstance('mysql')->getConnection());
  $activeBase = $configModelUI->getSybaseActiveBase(null); // baca terus dari tbl_m_config (persist)
}

// 3) JSON flags (legacy) jika masih tiada
if (!$activeBase && is_array($dbAktif)) {
  if (!empty($dbAktif['ehrmdb']))         $activeBase = 'sybase_ehrmdb';
  elseif (!empty($dbAktif['ehrmdb_dev'])) $activeBase = 'sybase_ehrmdb_dev';
  elseif (!empty($dbAktif['stafdb']))     $activeBase = 'sybase_stafdb';
}

if (!$activeBase_ASIS && is_array($dbAktif)) {
  if (!empty($dbAktif['asisdb']))         $activeBase_ASIS = 'sybase_asisdb';
  elseif (!empty($dbAktif['asisdb_dev'])) $activeBase_ASIS = 'sybase_asisdb_dev';
}

$activeBase = $activeBase ?: 'sybase_ehrmdb';
$activeBase_ASIS = $activeBase_ASIS ?: 'sybase_asisdb';

$base = strtolower($activeBase);
$base_ASIS = strtolower($activeBase_ASIS);

if (str_contains($base, 'ehrmdb_dev'))      $activeLogical = 'ehrmdb_dev';
else                                        $activeLogical = 'ehrmdb';

if (str_contains($base_ASIS, 'asisdb_dev'))      $activeLogical_ASIS = 'asisdb_dev';
else                                        $activeLogical_ASIS = 'asisdb';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" data-bs-theme="<?= htmlspecialchars($_SESSION['theme.layout'] ?? 'light', ENT_QUOTES, 'UTF-8') ?>">

<head>
  <?php
    // Matikan plugin berat untuk page ni (kalau head.php guna flags)
    $NEED_DATERANGE  = false;
    $NEED_VECTORMAP  = false;
    $NEED_DATATABLES = false;
    $NEED_SELECT2    = false;
    include __DIR__ . '/../includes/head.php';
  ?>
  <script>const BASE_URL = "<?= rtrim(base_url(), '/') ?>/";</script>
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

  <style>
    .color-sample{display:inline-block;width:24px;height:16px;border-radius:4px;border:1px solid #ccc;box-shadow:0 0 3px rgba(0,0,0,0.1);}
  </style>

  <!-- Translation map (senyap) -->
  <script>
    window.__translations = <?= json_encode($translations_js ?? [], JSON_UNESCAPED_UNICODE) ?>;
    window.__ = function (key) {
      var dict = window.__translations || {};
      if (Object.prototype.hasOwnProperty.call(dict, key)) {
        var val = dict[key];
        return (val && val !== '') ? val : key;
      }
      return key;
    };
  </script>
</head>

<body id="body-layout"
      data-topbar-color="<?= htmlspecialchars($_SESSION['theme.topbar'] ?? 'light', ENT_QUOTES, 'UTF-8') ?>"
      data-menu-color="<?= htmlspecialchars($_SESSION['theme.menu']   ?? 'light', ENT_QUOTES, 'UTF-8') ?>"
      data-layout="vertical"
      data-sidebar-size="default">

  <div class="wrapper">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">

          <!-- Tajuk & Breadcrumb -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="page-title"><i class="ri-settings-3-line me-1"></i> <?= __('config_system') ?? 'Konfigurasi Sistem' ?></h4>
                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="ri-home-4-line align-middle me-1"></i> <?= __('breadcrumb_home') ?? 'Home' ?></a></li>
                    <li class="breadcrumb-item active">
                      <i class="ri-settings-3-line align-middle me-1"></i> <?= __('config_system') ?? 'Konfigurasi Sistem' ?>
                    </li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab Navigasi -->
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link <?= ($_GET['tab'] ?? '') === 'email' ? 'active' : '' ?>" data-bs-toggle="tab" href="#email-tab" role="tab">
                <i class="ri-mail-settings-line me-1"></i> <?= __('config_tab_emel') ?? 'Emel' ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= (($_GET['tab'] ?? '') === 'db' || !isset($_GET['tab'])) ? 'active' : '' ?>" data-bs-toggle="tab" href="#db-tab" role="tab">
                <i class="ri-database-2-line me-1"></i> <?= __('config_tab_db') ?? 'Pangkalan Data' ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= ($_GET['tab'] ?? '') === 'theme' ? 'active' : '' ?>" data-bs-toggle="tab" href="#theme-tab" role="tab">
                <i class="ri-palette-line me-1"></i> <?= __('config_tab_tema') ?? 'Tema' ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= ($_GET['tab'] ?? '') === 'lang' ? 'active' : '' ?>" data-bs-toggle="tab" href="#lang-tab" role="tab">
                <i class="ri-translate-2 me-1"></i> <?= __('config_tab_bahasa') ?? 'Bahasa' ?>
              </a>
            </li>
          </ul>

          <!-- Kandungan Tab -->
          <div class="tab-content pt-3">

            <!-- Tab 1: Emel -->
            <div class="tab-pane fade <?= ($_GET['tab'] ?? '') === 'email' ? 'show active' : '' ?>" id="email-tab" role="tabpanel">
              <form method="POST" id="form-emel-aktif">
                <input type="hidden" name="form_type" value="email_settings" />
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                <div class="row g-4">
                  <!-- Pelayan Emel -->
                  <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="mdi mdi-server-network text-primary fs-5"></i>
                      </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-primary"><?= __('config_tab_emel_header_setting') ?? 'Konfigurasi Pelayan Emel' ?></h5>
                            <small class="text-muted">Server configuration settings</small>
                        </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="mb-3">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-settings-3-line me-1 text-muted"></i> <?= __('config_tab_emel_driver') ?? 'Mail Driver' ?>
                          </label>
                          <input type="text" name="mail_driver" class="form-control form-control-lg" value="<?= htmlspecialchars($emailSettings['mail_driver'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="smtp" maxlength="50">
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-global-line me-1 text-muted"></i> <?= __('config_tab_emel_host') ?? 'Mail Host' ?>
                          </label>
                          <input type="text" name="mail_host" class="form-control form-control-lg" value="<?= htmlspecialchars($emailSettings['mail_host'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="smtp.gmail.com" maxlength="255">
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-plug-line me-1 text-muted"></i> <?= __('config_tab_emel_port') ?? 'Port' ?>
                          </label>
                          <input type="text" name="mail_port" class="form-control form-control-lg" value="<?= htmlspecialchars($emailSettings['mail_port'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="587" maxlength="5">
                        </div>
                        <div class="mb-0">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-shield-check-line me-1 text-muted"></i> <?= __('config_tab_emel_encryption') ?? 'Encryption' ?>
                          </label>
                          <select name="mail_encryption" class="form-select form-select-lg">
                            <option value=""><?= __('config_tab_emel_sel_tiada') ?? 'Tiada' ?></option>
                            <option value="tls" <?= ($emailSettings['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= ($emailSettings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Akaun Emel -->
                  <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-info bg-opacity-10 border-bottom border-info border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="mdi mdi-account-outline text-info fs-5"></i>
                      </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-info"><?= __('config_tab_emel_header_emel') ?? 'Butiran Akaun Emel' ?></h5>
                            <small class="text-muted">Account credentials</small>
                        </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="mb-3">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-mail-line me-1 text-muted"></i> <?= __('config_tab_emel_account_emel') ?? 'Email Account (Username)' ?>
                          </label>
                          <input type="email" name="mail_username" class="form-control form-control-lg" value="<?= htmlspecialchars($emailSettings['mail_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="255">
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-lock-password-line me-1 text-muted"></i> <?= __('config_tab_emel_katalaluan_emel') ?? 'Kata Laluan Emel' ?>
                          </label>
                          <input type="password" name="mail_password" class="form-control form-control-lg" placeholder="Biarkan kosong jika tidak mahu tukar" autocomplete="new-password">
                          <small class="text-muted d-block mt-1">
                            <i class="ri-information-line me-1"></i> <?= __('config_tab_emel_password_hint') ?? 'Biarkan kosong untuk mengekalkan kata laluan semasa' ?>
                          </small>
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-send-plane-line me-1 text-muted"></i> <?= __('config_tab_emel_from') ?? 'Email daripada?' ?>
                          </label>
                          <input type="email" name="mail_from_address" class="form-control form-control-lg" value="<?= htmlspecialchars($emailSettings['mail_from_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="255">
                        </div>
                        <div class="mb-0">
                          <label class="form-label fw-semibold mb-2">
                            <i class="ri-user-line me-1 text-muted"></i> <?= __('config_tab_emel_from_name') ?? 'Nama Pemilik Email' ?>
                          </label>
                          <input type="text" name="mail_from_name" class="form-control form-control-lg" value="<?= htmlspecialchars($emailSettings['mail_from_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="255">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                  <button type="button" class="btn btn-outline-secondary px-4" id="btn-uji-emel">
                    <i class="ri-mail-send-line me-2"></i> <?= __('config_tab_emel_uji_emel') ?? 'Uji Sambungan Emel' ?>
                  </button>
                  <button type="button" class="btn btn-primary px-4" id="btn-simpan-emel">
                    <i class="ri-save-3-line me-2"></i> <?= __('config_tab_emel_simpan_tetapan_emel') ?? 'Simpan Tetapan Emel' ?>
                  </button>
                </div>
              </form>
            </div>

            <!-- Tab 2: Pangkalan Data -->
            <div class="tab-pane fade <?= (($_GET['tab'] ?? '') === 'db' || !isset($_GET['tab'])) ? 'show active' : '' ?>" id="db-tab" role="tabpanel">
              <form method="post" id="form-db-aktif">
                <!-- Hidden fields for submit to database : function in TetapanSistemController -->
                <input type="hidden" name="submit_db" value="1">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                <div class="row g-4">
                  <!-- Sybase EHRM-->
                  <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="mdi mdi-database-marker text-warning fs-5"></i>
                      </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-warning"><?= __('config_tab_db_header') ?? 'Sybase EHRM (Pilih Satu Sahaja)' ?></h5>
                            <small class="text-muted">Select one connection only</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                          <i class="ri-information-line me-2"></i>
                          <small><?= __('config_tab_db_sybase_header') ?? 'Hanya satu sambungan EHRM dibenarkan aktif dalam satu masa.' ?></small>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-hover align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px">
                                  <i class="ri-radio-button-line text-muted"></i>
                                </th>
                                <th style="width:220px" class="fw-semibold"><?= __('config_tab_db_sybase_sambungan') ?? 'Nama Sambungan' ?></th>
                                <th class="fw-semibold"><?= __('config_tab_db_sybase_keterangan') ?? 'Keterangan' ?></th>
                            </tr>
                          </thead>
                          <tbody>
                              <tr class="<?= ($activeLogical === 'ehrmdb') ? 'table-primary' : '' ?>">
                              <td class="text-center">
                                  <div class="form-check form-check-primary">
                                <input class="form-check-input" type="radio" name="active_db" id="ehrmdb"
                                  value="ehrmdb" <?= ($activeLogical === 'ehrmdb') ? 'checked' : '' ?>>
                                  </div>
                              </td>
                                <td>
                                  <label class="form-check-label fw-bold cursor-pointer" for="ehrmdb">
                                    <?= __('config_tab_db_sybase_nama_production') ?? 'e-HRMDB (Production)' ?>
                                  </label>
                                </td>
                                <td>
                                  <span class="badge bg-success-subtle text-success me-2">✅</span>
                                  <?= __('config_tab_db_sybase_nama_production_penerangan') ?? 'Pangkalan data utama sistem e-Prestasi' ?>
                                </td>
                            </tr>
                              <tr class="<?= ($activeLogical === 'ehrmdb_dev') ? 'table-primary' : '' ?>">
                              <td class="text-center">
                                  <div class="form-check form-check-primary">
                                <input class="form-check-input" type="radio" name="active_db" id="ehrmdb_dev"
                                  value="ehrmdb_dev" <?= ($activeLogical === 'ehrmdb_dev') ? 'checked' : '' ?>>
                                  </div>
                              </td>
                                <td>
                                  <label class="form-check-label fw-bold cursor-pointer" for="ehrmdb_dev">
                                    <?= __('config_tab_db_sybase_nama_development') ?? 'e-HRMDB (Development)' ?>
                                  </label>
                                </td>
                                <td>
                                  <span class="badge bg-info-subtle text-info me-2">🧪</span>
                                  <?= __('config_tab_db_sybase_nama_development_penerangan') ?? 'Pangkalan data pembangunan e-HRMDB (dev)' ?>
                                </td>
                            </tr>
                          </tbody>
                        </table>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Sybase ASIS-->
                  <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-info bg-opacity-10 border-bottom border-info border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="mdi mdi-database-marker text-info fs-5"></i>
                      </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-info"><?= __('config_tab_db_header_2') ?? 'Sybase ASIS (Pilih Satu Sahaja)' ?></h5>
                            <small class="text-muted">Select one connection only</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                          <i class="ri-information-line me-2"></i>
                          <small><?= __('config_tab_db_sybase_header_asis') ?? 'Hanya satu sambungan Sybase ASIS dibenarkan aktif dalam satu masa.' ?></small>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-hover align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:50px">
                                  <i class="ri-radio-button-line text-muted"></i>
                                </th>
                                <th style="width:220px" class="fw-semibold"><?= __('config_tab_db_sybase_sambungan') ?? 'Nama Sambungan' ?></th>
                                <th class="fw-semibold"><?= __('config_tab_db_sybase_keterangan') ?? 'Keterangan' ?></th>
                            </tr>
                          </thead>
                          <tbody>
                              <tr class="<?= ($activeLogical_ASIS === 'asisdb') ? 'table-primary' : '' ?>">
                              <td class="text-center">
                                  <div class="form-check form-check-primary">
                                <input class="form-check-input" type="radio" name="active_db_asis" id="asisdb"
                                  value="asisdb" <?= ($activeLogical_ASIS === 'asisdb') ? 'checked' : '' ?>>
                                  </div>
                              </td>
                                <td>
                                  <label class="form-check-label fw-bold cursor-pointer" for="asisdb">
                                    <?= __('config_tab_db_sybase_nama_production_asis') ?? 'SAP (Production)' ?>
                                  </label>
                                </td>
                                <td>
                                  <span class="badge bg-success-subtle text-success me-2">✅</span>
                                  <?= __('config_tab_db_sybase_nama_production_penerangan_asis') ?? 'Pangkalan data utama sistem SAP' ?>
                                </td>
                            </tr>
                              <tr class="<?= ($activeLogical_ASIS === 'asisdb_dev') ? 'table-primary' : '' ?>">
                              <td class="text-center">
                                  <div class="form-check form-check-primary">
                                <input class="form-check-input" type="radio" name="active_db_asis" id="asisdb_dev"
                                  value="asisdb_dev" <?= ($activeLogical_ASIS === 'asisdb_dev') ? 'checked' : '' ?>>
                                  </div>
                              </td>
                                <td>
                                  <label class="form-check-label fw-bold cursor-pointer" for="asisdb_dev">
                                    <?= __('config_tab_db_sybase_nama_development_asis') ?? 'SAP (Development)' ?>
                                  </label>
                                </td>
                                <td>
                                  <span class="badge bg-info-subtle text-info me-2">🧪</span>
                                  <?= __('config_tab_db_sybase_nama_development_penerangan_asis') ?? 'Pangkalan data pembangunan SAP (dev)' ?>
                                </td>
                            </tr>
                          </tbody>
                        </table>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- MySQL -->
                  <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="mdi mdi-database-outline text-success fs-5"></i>
                      </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-success"><?= __('config_tab_db_mysql') ?? 'MySQL (Sentiasa Aktif)' ?></h5>
                            <small class="text-muted">Always active connection</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                          <i class="ri-checkbox-circle-line me-2"></i>
                          <small><?= __('config_tab_db_mysql_header') ?? 'Sambungan ini sentiasa aktif untuk sistem utama.' ?></small>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-hover align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                                <th style="width:180px" class="fw-semibold"><?= __('config_tab_db_mysql_sambungan') ?? 'Medan' ?></th>
                                <th class="fw-semibold"><?= __('config_tab_db_mysql_keterangan') ?? 'Maklumat' ?></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <i class="ri-server-line text-primary me-2"></i>
                                    <strong><?= __('config_tab_db_mysql_host') ?? 'Host' ?></strong>
                                  </div>
                                </td>
                                <td>
                                  <code class="text-primary"><?= htmlspecialchars($mysqlInfo['dsn'] ?? '-', ENT_QUOTES, 'UTF-8') ?></code>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <i class="ri-user-line text-info me-2"></i>
                                    <strong><?= __('config_tab_db_mysql_user') ?? 'User' ?></strong>
                                  </div>
                                </td>
                                <td>
                                  <code class="text-info"><?= htmlspecialchars($mysqlInfo['user'] ?? '-', ENT_QUOTES, 'UTF-8') ?></code>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <i class="ri-checkbox-circle-line text-success me-2"></i>
                                    <strong><?= __('config_tab_db_mysql_status') ?? 'Status' ?></strong>
                                  </div>
                                </td>
                                <td>
                                  <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <i class="ri-checkbox-circle-fill me-1"></i> Aktif
                                  </span>
                                </td>
                            </tr>
                          </tbody>
                        </table>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>            

                <div class="d-flex justify-content-end mt-3">
                  <button type="submit" class="btn btn-primary px-4" id="btn-simpan-db">
                    <i class="ri-save-3-line me-2"></i> <?= __('config_tab_db_simpan_tetapan_db') ?? 'Simpan Tetapan Pangkalan Data' ?>
                  </button>
                </div>
              </form>
            </div>

            <!-- Tab 3: Tema -->
            <div class="tab-pane fade <?= ($_GET['tab'] ?? '') === 'theme' ? 'show active' : '' ?>" id="theme-tab" role="tabpanel">
              <?php
                require_once __DIR__ . '/../classes/Config.php';
                $configModel = new Config(Database::getInstance('mysql')->getConnection());
                $temaDefault = $configModel->getTema();
                $topbar  = $temaDefault['topbarColor']  ?? 'light';
                $sidebar = $temaDefault['sidebarColor'] ?? 'light';
                $layout  = $temaDefault['layoutMode']   ?? 'light';
              ?>
              <form method="post" id="form-tema-aktif">
                <input type="hidden" name="form_type" value="theme_settings">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                <div class="row g-4">
                  <!-- Layout Mode -->
                  <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="ri-layout-line text-primary fs-5"></i>
                          </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-primary"><?= __('config_tab_tema_komponen_layout') ?? 'Mod Susun Atur' ?></h5>
                            <small class="text-muted">Layout mode</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="d-flex flex-column gap-2">
                          <label class="theme-option <?= $layout === 'light' ? 'active' : '' ?>" for="layout_light">
                            <input class="form-check-input" type="radio" name="layout_mode" id="layout_light" value="light" <?= $layout === 'light' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #dee2e6; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_layout_terang') ?? 'Warna Terang' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-sun-line me-1"></i> Standard mod terang
                                </div>
                              </div>
                            </div>
                          </label>
                          <label class="theme-option <?= $layout === 'dark' ? 'active' : '' ?>" for="layout_dark">
                            <input class="form-check-input" type="radio" name="layout_mode" id="layout_dark" value="dark" <?= $layout === 'dark' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #1e1e2d 0%, #2b2e4a 100%); border: 2px solid #343a40; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_layout_gelap') ?? 'Warna Gelap' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-moon-line me-1"></i> Sesuai untuk malam
                                </div>
                              </div>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Topbar Color -->
                  <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-info bg-opacity-10 border-bottom border-info border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="ri-layout-top-line text-info fs-5"></i>
                          </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-info"><?= __('config_tab_tema_komponen_topbar') ?? 'Warna Topbar' ?></h5>
                            <small class="text-muted">Topbar color</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="d-flex flex-column gap-2">
                          <label class="theme-option <?= $topbar === 'light' ? 'active' : '' ?>" for="topbar_light">
                            <input class="form-check-input" type="radio" name="topbar_color" id="topbar_light" value="light" <?= $topbar === 'light' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #dee2e6; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_topbar_terang') ?? 'Warna Terang' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-sun-line me-1"></i> Sesuai mod terang
                                </div>
                              </div>
                            </div>
                          </label>
                          <label class="theme-option <?= $topbar === 'dark' ? 'active' : '' ?>" for="topbar_dark">
                            <input class="form-check-input" type="radio" name="topbar_color" id="topbar_dark" value="dark" <?= $topbar === 'dark' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #343a40 0%, #212529 100%); border: 2px solid #495057; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_topbar_gelap') ?? 'Warna Gelap' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-moon-line me-1"></i> Sesuai mod gelap
                                </div>
                              </div>
                            </div>
                          </label>
                          <label class="theme-option <?= $topbar === 'brand' ? 'active' : '' ?>" for="topbar_brand">
                            <input class="form-check-input" type="radio" name="topbar_color" id="topbar_brand" value="brand" <?= $topbar === 'brand' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3f51b5 0%, #283593 100%); border: 2px solid #1a237e; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_layout_brand') ?? 'Warna Brand' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-palette-line me-1"></i> Warna rasmi sistem
                                </div>
                              </div>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Sidebar Color -->
                  <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                      <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="ri-layout-left-line text-success fs-5"></i>
                          </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-success"><?= __('config_tab_tema_komponen_sidebar') ?? 'Warna Sidebar' ?></h5>
                            <small class="text-muted">Sidebar color</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="d-flex flex-column gap-2">
                          <label class="theme-option <?= $sidebar === 'light' ? 'active' : '' ?>" for="sidebar_light">
                            <input class="form-check-input" type="radio" name="sidebar_color" id="sidebar_light" value="light" <?= $sidebar === 'light' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #dee2e6; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_sidebar_terang') ?? 'Warna Terang' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-sun-line me-1"></i> Latar putih bersih
                                </div>
                              </div>
                            </div>
                          </label>
                          <label class="theme-option <?= $sidebar === 'dark' ? 'active' : '' ?>" for="sidebar_dark">
                            <input class="form-check-input" type="radio" name="sidebar_color" id="sidebar_dark" value="dark" <?= $sidebar === 'dark' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #2b2e4a 0%, #1a1d2e 100%); border: 2px solid #343a40; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_sidebar_gelap') ?? 'Warna Gelap' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-moon-line me-1"></i> Selesa untuk mata
                                </div>
                              </div>
                            </div>
                          </label>
                          <label class="theme-option <?= $sidebar === 'brand' ? 'active' : '' ?>" for="sidebar_brand">
                            <input class="form-check-input" type="radio" name="sidebar_color" id="sidebar_brand" value="brand" <?= $sidebar === 'brand' ? 'checked' : '' ?>>
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                              <div class="theme-preview" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3f51b5 0%, #283593 100%); border: 2px solid #1a237e; border-radius: 6px;"></div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold small"><?= __('config_tab_tema_pilihan_sidebar_brand') ?? 'Warna Brand' ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                  <i class="ri-palette-line me-1"></i> Warna jenama utama
                                </div>
                              </div>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button type="button" class="btn btn-primary px-4" id="btn-simpan-tema">
                    <i class="ri-save-3-line me-2"></i> <?= __('config_tab_db_simpan_tetapan_tema') ?? 'Simpan Tetapan Tema' ?>
                  </button>
                </div>
              </form>
            </div>


            <!-- Tab 4: Bahasa -->
            <div class="tab-pane fade <?= ($_GET['tab'] ?? '') === 'lang' ? 'show active' : '' ?>" id="lang-tab" role="tabpanel">
              <form id="form-bahasa" method="post">
                <input type="hidden" name="form_type" value="update_languages">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                <div class="row g-4">
                  <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                      <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="mdi mdi-translate text-danger fs-5"></i>
                      </div>
                          <div>
                            <h5 class="mb-0 fw-semibold text-danger"><?= __('config_tab_bahasa_header') ?? 'Bahasa yang Tersedia' ?></h5>
                            <small class="text-muted">Available languages</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-body p-4">
                        <div class="alert alert-info mb-4" role="alert">
                          <i class="ri-information-line me-2"></i>
                          <small><?= __('config_tab_bahasa_header_details') ?? 'Tandakan bahasa yang ingin diaktifkan untuk digunakan dalam sistem.' ?></small>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-hover align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:60px">
                                  <i class="ri-checkbox-line text-muted"></i>
                                </th>
                                <th style="width:200px" class="fw-semibold"><?= __('config_tab_bahasa_kodBahasa') ?? 'Kod Bahasa' ?></th>
                                <th class="fw-semibold"><?= __('config_tab_bahasa_peneranganBahasa') ?? 'Penerangan Bahasa' ?></th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              foreach ($senaraiBahasa as $code):
                                $label = strtoupper($code);
                                $flagFile = 'default.png';
                                  $isActive = in_array($code, $bahasaAktif, true);
                                switch ($code) {
                                  case 'ms': $label .= ' - Bahasa Melayu'; $flagFile = 'malaysia.png'; break;
                                  case 'en': $label .= ' - English';       $flagFile = 'united-kingdom.png'; break;
                                  case 'ta': $label .= ' - தமிழ்';         $flagFile = 'india.png'; break;
                                  case 'zh': $label .= ' - 中文';          $flagFile = 'china.png'; break;
                                }
                            ?>
                              <tr class="<?= $isActive ? 'table-success' : '' ?>">
                                <td class="text-center align-middle">
                                  <div class="form-check form-check-success">
                                <input class="form-check-input" type="checkbox"
                                       name="languages[]" id="lang_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                                       value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                                           <?= $isActive ? 'checked' : '' ?>>
                                  </div>
                              </td>
                                <td class="align-middle">
                                  <label class="form-check-label fw-bold d-flex align-items-center gap-2 cursor-pointer"
                                       for="lang_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>">
                                  <img loading="lazy"
                                    src="<?= asset_url('images/flags/' . $flagFile) ?>"
                                    alt="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                                      width="28" height="20" 
                                      class="rounded border border-2 shadow-sm"
                                      style="object-fit: cover;">
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1"><?= strtoupper($code) ?></span>
                                </label>
                              </td>
                                <td class="align-middle">
                                  <div class="d-flex align-items-center">
                                    <span class="me-2"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if ($isActive): ?>
                                      <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="ri-checkbox-circle-fill me-1"></i> Aktif
                                      </span>
                                    <?php endif; ?>
                                  </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                  <button type="button" class="btn btn-primary px-4" id="btn-simpan-bahasa">
                    <i class="ri-save-3-line me-2"></i> <?= __('config_tab_bahasa_simpan_tetapan_bahasa') ?? 'Simpan Tetapan Bahasa' ?>
                  </button>
                </div>
              </form>
            </div>

          </div><!-- /tab-content -->
        </div>
      </div>

      <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
  </div>

  <?php
    // Flags JS vendor – hanya yang perlu
    $NEED_JQUERY     = true;
    $NEED_SWEETALERT = true;
    $NEED_DT_JS      = false;
    $NEED_SELECT2_JS = false;
    include __DIR__ . '/../includes/script.php';
  ?>

  <style>
    .cursor-pointer { cursor: pointer; }
    .color-preview { transition: transform 0.2s ease; }
    .color-preview:hover { transform: scale(1.1); }
    .form-check-input:checked ~ .form-check-label { color: var(--ct-primary); }
    .table-hover tbody tr:hover { background-color: rgba(var(--ct-primary-rgb), 0.05); }
    .avatar-sm { flex-shrink: 0; }
    .text-purple { color: #6f42c1 !important; }
    .bg-purple { background-color: #6f42c1 !important; }
    .border-purple { border-color: #6f42c1 !important; }
    
    /* Theme Option Styling */
    .theme-option {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      background: #fff;
    }
    .theme-option:hover {
      border-color: var(--ct-primary);
      background: rgba(var(--ct-primary-rgb), 0.05);
    }
    .theme-option.active {
      border-color: var(--ct-primary);
      background: rgba(var(--ct-primary-rgb), 0.1);
    }
    .theme-option input[type="radio"] {
      margin: 0;
      flex-shrink: 0;
    }
    .theme-preview {
      flex-shrink: 0;
      transition: transform 0.2s ease;
    }
    .theme-option:hover .theme-preview {
      transform: scale(1.1);
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const __ = window.__ || (k => k);

      // Pilih tab: utamakan ?tab=...
      (function(){
        const urlTab = new URLSearchParams(location.search).get('tab');
        const wanted = urlTab ? ('#' + urlTab + '-tab') : (window.location.hash || localStorage.getItem('lastActiveTab') || '#db-tab');
        const el = document.querySelector(`a[href="${wanted}"]`);
        if (el) new bootstrap.Tab(el).show();

        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
          tab.addEventListener('shown.bs.tab', e => {
            localStorage.setItem('lastActiveTab', e.target.getAttribute('href'));
          });
        });
      })();

      // ================= Real-time Validation =================
      function validateField(field) {
        const name = field.name;
        const value = field.value.trim();
        let isValid = true;
        let message = '';
        
        // Remove existing feedback
        const existingFeedback = field.parentElement.querySelector('.invalid-feedback');
        if (existingFeedback) existingFeedback.remove();
        field.classList.remove('is-invalid', 'is-valid');
        
        if (!value) {
          return; // Don't validate empty fields
        }
        
        if (name === 'mail_port') {
          const port = parseInt(value);
          if (isNaN(port) || port < 1 || port > 65535) {
            isValid = false;
            message = 'Port mesti antara 1 hingga 65535';
          }
        }
        
        if (name === 'mail_host') {
          // Simple domain/IP validation
          const domainRegex = /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
          const ipRegex = /^(\d{1,3}\.){3}\d{1,3}$/;
          if (!domainRegex.test(value) && !ipRegex.test(value)) {
            isValid = false;
            message = 'Format host tidak sah (domain atau IP)';
          }
        }
        
        if (name === 'mail_username' || name === 'mail_from_address') {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Format emel tidak sah';
          }
        }
        
        // Show validation feedback
        if (isValid) {
          field.classList.add('is-valid');
        } else {
          field.classList.add('is-invalid');
          const div = document.createElement('div');
          div.className = 'invalid-feedback';
          div.textContent = message;
          field.parentElement.appendChild(div);
        }
      }
      
      // Attach real-time validation
      document.querySelectorAll('input[name="mail_host"], input[name="mail_port"], input[name="mail_username"], input[name="mail_from_address"]').forEach(input => {
        input.addEventListener('blur', function() {
          validateField(this);
        });
        input.addEventListener('input', function() {
          // Remove validation on input (user is typing)
          this.classList.remove('is-invalid', 'is-valid');
          const feedback = this.parentElement.querySelector('.invalid-feedback');
          if (feedback) feedback.remove();
        });
      });

      // ================= Loading States =================
      function setButtonLoading(button, loading) {
        if (loading) {
          button.disabled = true;
          button.dataset.originalHtml = button.innerHTML;
          button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        } else {
          button.disabled = false;
          if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
          }
        }
      }

      // Emel – confirm sebelum submit dengan loading state
      const formEmel = document.getElementById('form-emel-aktif');
      const btnEmel  = document.getElementById('btn-simpan-emel');
      if (formEmel && btnEmel) {
        btnEmel.addEventListener('click', function (e) {
          e.preventDefault();
          set_confirm(__('config_js_confirm_emel'), __('config_js_btn_ya_simpan'), null, () => {
            setButtonLoading(btnEmel, true);
            formEmel.submit();
            // Fallback: re-enable after 10 seconds (kalau redirect gagal)
            setTimeout(() => setButtonLoading(btnEmel, false), 10000);
          });
        });
      }

      // Uji Emel (AJAX)
      const btnUji = document.getElementById('btn-uji-emel');
      btnUji?.addEventListener('click', function () {
        const form = document.getElementById('form-emel-aktif');
        // Get email from form inputs (prefer from_address, fallback to username)
        const mailFrom = form?.querySelector('input[name="mail_from_address"]')?.value || '';
        const mailUsername = form?.querySelector('input[name="mail_username"]')?.value || '';
        const defaultEmail = mailFrom || mailUsername || '';
        set_confirm(__('config_js_confirm_uji_emel'), __('config_js_btn_ya_teruskan'), null, () => {
          Swal.fire({
            title: __('config_js_input_uji_emel'),
            input: 'email',
            inputLabel: __('config_js_label_uji_emel'),
            inputValue: defaultEmail,
            inputPlaceholder: __('config_js_placeholder_uji_emel'),
            showCancelButton: true,
            confirmButtonText: __('config_js_uji_emel_btn'),
            cancelButtonText: __('config_alert_no'),
            preConfirm: (email) => {
              if (!email) {
                Swal.showValidationMessage(__('config_js_valid_emel_kosong'));
                return false;
              }
              const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
              if (!emailRegex.test(email)) {
                Swal.showValidationMessage('Format emel tidak sah. Sila masukkan emel yang betul.');
                return false;
              }
              return email;
            }
          }).then(result => {
            if (result.isConfirmed) {
              const formData = new FormData(form);
              formData.append('uji_email', result.value);
              btnUji.disabled = true;
              btnUji.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + __('config_js_uji_emel_btn_loading');

              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
              formData.append('csrf_token', csrfToken);
              
              fetch(`${BASE_URL}ajax/uji-emel.php`, { 
                method: 'POST', 
                body: formData,
                headers: {
                  'X-CSRF-Token': csrfToken
                }
              })
                .then(res => res.json())
                .then(data => {
                  if (data.success) {
                    const title = __('config_js_berjaya');
                    const finalTitle = (title && title !== 'config_js_berjaya') ? title : 'Berjaya';
                    Swal.fire({ 
                      icon: 'success', 
                      title: finalTitle, 
                      html: data.message || __('config_js_emel_berjaya') || 'Emel berjaya dihantar.' 
                    });
                  } else {
                    const title = __('config_js_ralat');
                    const finalTitle = (title && title !== 'config_js_ralat') ? title : 'Ralat';
                    Swal.fire({ 
                      icon: 'error', 
                      title: finalTitle, 
                      text: data.message || __('config_js_emel_gagal') || 'Gagal hantar emel.' 
                    });
                  }
                })
                .catch(() => {
                  Swal.fire({ icon: 'error', title: __('config_js_ralat'), text: __('config_js_ralat_sistem') });
                })
                .finally(() => {
                  btnUji.disabled = false;
                  btnUji.innerHTML = '<i class="ri-mail-send-line me-1"></i> ' + __('config_js_uji_emel_btn_default');
                });
            }
          });
        });
      });

      // DB – confirm sebelum submit dengan loading state
      const formDB = document.getElementById('form-db-aktif');
      const btnDB  = document.getElementById('btn-simpan-db');
      if (formDB && btnDB) {
        btnDB.addEventListener('click', function (e) {
          e.preventDefault();
          set_confirm(__('config_js_confirm_db'), __('config_js_btn_ya_simpan'), null, () => {
            setButtonLoading(btnDB, true);
            formDB.submit();
            setTimeout(() => setButtonLoading(btnDB, false), 10000);
          });
        });
      }

      // Bahasa – confirm + submit dengan loading state
      const formBahasa = document.getElementById('form-bahasa');
      const btnBahasa  = document.getElementById('btn-simpan-bahasa');
      if (formBahasa && btnBahasa) {
        btnBahasa.addEventListener('click', function (e) {
          e.preventDefault();
          const checked = formBahasa.querySelectorAll('input[name="languages[]"]:checked');
          if (checked.length === 0) {
            Swal.fire({ icon: 'warning', title: __('config_js_tiada_bahasa'), text: __('config_js_pilih_bahasa'), confirmButtonText: __('config_js_btn_ok') });
            return;
          }
          set_confirm(__('config_js_confirm_bahasa'), __('config_js_btn_ya_simpan'), null, () => {
            setButtonLoading(btnBahasa, true);
            formBahasa.submit();
            setTimeout(() => setButtonLoading(btnBahasa, false), 10000);
          });
        });
      }

      // Tema – confirm + submit dengan loading state
      const formTema = document.getElementById('form-tema-aktif');
      const btnTema  = document.getElementById('btn-simpan-tema');
      if (formTema && btnTema) {
        btnTema.addEventListener('click', function (e) {
          e.preventDefault();
          set_confirm(__('config_js_confirm_tema'), __('config_js_btn_ya_simpan'), null, () => {
            setButtonLoading(btnTema, true);
            formTema.submit();
            setTimeout(() => setButtonLoading(btnTema, false), 10000);
          });
        });
      }
    });
  </script>
</body>
</html>
