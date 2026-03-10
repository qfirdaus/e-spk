<?php
// ======================================
// ✅ Controller: Tetapan Sistem (Clean, no-legacy)
// ======================================

declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Config.php';
require_once __DIR__ . '/../classes/SystemConfigConstants.php';
require_once __DIR__ . '/../setting/constants/prestasi_constants.php';

class TetapanSistemController {
  public string $lang;
  public array $profile;
  public array $db_configs;
  public array $active_db_flags;

  /** @var ?string base key sybase aktif, cth: 'sybase_ehrmdb' */
  public ?string $active_sybase_name = null;

  private PDO $pdo;
  private Config $configModel;

  public function __construct() {
    $this->lang = $_SESSION['lang'] ?? SystemConfigConstants::DEFAULT_LANGUAGE;

    // ✅ MySQL untuk user/profile & config
    $pdo_mysql         = Database::getInstance('mysql')->getConnection();
    $this->pdo         = $pdo_mysql;
    $this->configModel = new Config($this->pdo);

    // ✅ Profil user
    $userModel  = new User($pdo_mysql);
    $f_stafID   = $_SESSION['f_stafID'] ?? null;
    $this->profile = $f_stafID ? $userModel->getProfile($f_stafID) : [];

    // ✅ Config DB (senarai sambungan tersedia) + flag aktif (ehrmdb/ehrmdb_dev/stafdb)
    $this->db_configs         = require __DIR__ . '/../configuration/db_config.php';
    $this->active_db_flags    = $this->getActiveDBConfig();
    $this->active_sybase_name = $this->findActiveSybaseName(); // base key, contoh 'sybase_ehrmdb'

    // Selaraskan flags dengan sumber SSoT (session/constant/DB)
    if ($this->active_sybase_name) {
      $logical = $this->baseToLogical($this->active_sybase_name);
      if ($logical) {
        $this->active_db_flags = ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
        $this->active_db_flags[$logical] = true;
      }
    }
  }

  /**
   * Handle POST requests - dipanggil dari page
   */
  public function handleRequest(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
      return; // Hanya proses POST
    }

    $formType = $_POST['form_type'] ?? '';
    
    // Proses berdasarkan form type
    if (isset($_POST['submit_db'])) {
      $this->handleDatabaseUpdate();
    } elseif ($formType === 'email_settings') {
      $this->handleEmailUpdate();
    } elseif ($formType === 'update_languages') {
      $this->handleLanguageUpdate();
    } elseif ($formType === 'theme_settings') {
      $this->handleThemeUpdate();
    }
  }

  /**
   * Handle database update
   */
  private function handleDatabaseUpdate(): void {
    $this->checkAuthorization();
    $this->validateCSRF();
    $this->prosesSimpananDB();
  }

  /**
   * Handle email settings update
   */
  private function handleEmailUpdate(): void {
    $this->checkAuthorization();
    $this->validateCSRF();
    $this->ensureSession();
    
    // Ambil password lama jika password baru kosong
    $existingSettings = $this->getEmailSettings();
    $newPassword = trim($_POST['mail_password'] ?? '');
    
    $emailData = [
      'mail_driver'       => trim($_POST['mail_driver'] ?? ''),
      'mail_host'         => trim($_POST['mail_host'] ?? ''),
      'mail_port'         => trim($_POST['mail_port'] ?? ''),
      'mail_username'     => trim($_POST['mail_username'] ?? ''),
      'mail_password'     => $newPassword !== '' ? $newPassword : ($existingSettings['mail_password'] ?? ''),
      'mail_encryption'   => trim($_POST['mail_encryption'] ?? ''),
      'mail_from_address' => trim($_POST['mail_from_address'] ?? ''),
      'mail_from_name'    => trim($_POST['mail_from_name'] ?? ''),
    ];
    
    // Validate input
    $validationErrors = $this->validateEmailSettings($emailData);
    if (!empty($validationErrors)) {
      set_alert([
        'title' => 'Ralat Validasi',
        'text' => implode('<br>', $validationErrors),
        'icon' => 'error'
      ]);
      header('Location: tetapan-sistem.php?tab=email');
      exit;
    }
    
    // Save settings with audit logging
    try {
      $oldSettings = $this->getEmailSettings();
      $result = $this->saveEmailSettings($emailData);
        if ($result) {
        $this->auditEmailUpdate($oldSettings, $emailData);
        set_alert(['title'=>'emel_title','text'=>'emel_title_save','icon'=>'success','confirm'=>true,'confirmText'=>'config_js_btn_tutup']);
      } else {
        set_alert([
          'title' => 'Ralat Menyimpan',
          'text' => 'Gagal menyimpan tetapan emel. Sila cuba lagi atau hubungi pentadbir sistem.',
          'icon' => 'error'
        ]);
      }
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Save email settings failed: " . $e->getMessage());
      set_alert([
        'title' => 'Ralat Sistem',
        'text' => 'Ralat berlaku semasa menyimpan tetapan emel: ' . htmlspecialchars($e->getMessage()),
        'icon' => 'error'
      ]);
    }
    
    header('Location: tetapan-sistem.php?tab=email');
    exit;
  }

  /**
   * Handle language update
   */
  private function handleLanguageUpdate(): void {
    $this->checkAuthorization();
    $this->validateCSRF();
    $this->ensureSession();
    $languages = $_POST['languages'] ?? [];
    
    // Validate input
    $validationErrors = $this->validateLanguageSettings($languages);
    if (!empty($validationErrors)) {
      set_alert([
        'title' => 'Ralat Validasi',
        'text' => implode('<br>', $validationErrors),
        'icon' => 'error'
      ]);
      header('Location: tetapan-sistem.php?tab=lang');
      exit;
    }
    
    // Save languages with audit logging
    try {
      $oldLanguages = $this->configModel->getBahasaAktif();
      $result = $this->configModel->saveBahasa($languages);
        if ($result) {
        $this->auditLanguageUpdate($oldLanguages, $languages);
        if (!in_array($_SESSION['lang'] ?? SystemConfigConstants::DEFAULT_LANGUAGE, $languages)) {
          $_SESSION['lang'] = $languages[0] ?? SystemConfigConstants::DEFAULT_LANGUAGE;
        }
        set_alert(['title'=>'bahasa_title','text'=>'bahasa_title_save','icon'=>'success','confirm'=>true,'confirmText'=>'config_js_btn_tutup']);
      } else {
        set_alert([
          'title' => 'Ralat Menyimpan',
          'text' => 'Gagal menyimpan tetapan bahasa. Sila cuba lagi atau hubungi pentadbir sistem.',
          'icon' => 'error'
        ]);
      }
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Save languages failed: " . $e->getMessage());
      set_alert([
        'title' => 'Ralat Sistem',
        'text' => 'Ralat berlaku semasa menyimpan tetapan bahasa: ' . htmlspecialchars($e->getMessage()),
        'icon' => 'error'
      ]);
    }
    
    header('Location: tetapan-sistem.php?tab=lang');
    exit;
  }

  /**
   * Handle theme update
   */
  private function handleThemeUpdate(): void {
    $this->checkAuthorization();
    $this->validateCSRF();
    $this->ensureSession();
    $topbar  = trim($_POST['topbar_color'] ?? SystemConfigConstants::DEFAULT_THEME_TOPBAR);
    $sidebar = trim($_POST['sidebar_color'] ?? SystemConfigConstants::DEFAULT_THEME_SIDEBAR);
    $layout  = trim($_POST['layout_mode']   ?? SystemConfigConstants::DEFAULT_THEME_LAYOUT);
    $themeSetting = ['topbarColor'=>$topbar,'sidebarColor'=>$sidebar,'layoutMode'=>$layout];

    // Validate input
    $validationErrors = $this->validateThemeSettings($themeSetting);
    if (!empty($validationErrors)) {
      set_alert([
        'title' => 'Ralat Validasi',
        'text' => implode('<br>', $validationErrors),
        'icon' => 'error'
      ]);
      header('Location: tetapan-sistem.php?tab=theme');
      exit;
    }

    // Save theme with audit logging
    try {
      $oldTheme = $this->configModel->getTema();
      $result = $this->configModel->saveTema($themeSetting);
      if ($result) {
        $this->auditThemeUpdate($oldTheme, $themeSetting);
        $_SESSION['theme.topbar'] = $topbar;
        $_SESSION['theme.menu']   = $sidebar;
        $_SESSION['theme.layout'] = $layout;
        set_alert(['title'=>'tema_title','text'=>'tema_title_save','icon'=>'success','confirm'=>true,'confirmText'=>'config_js_btn_tutup']);
      } else {
        set_alert([
          'title' => 'Ralat Menyimpan',
          'text' => 'Gagal menyimpan tetapan tema. Sila cuba lagi atau hubungi pentadbir sistem.',
          'icon' => 'error'
        ]);
      }
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Save theme failed: " . $e->getMessage());
      set_alert([
        'title' => 'Ralat Sistem',
        'text' => 'Ralat berlaku semasa menyimpan tetapan tema: ' . htmlspecialchars($e->getMessage()),
        'icon' => 'error'
      ]);
    }

    header('Location: tetapan-sistem.php?tab=theme');
    exit;
  }

  /** Pastikan sesi terbuka sebelum tulis $_SESSION / set_alert */
  private function ensureSession(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      try {
        session_start();
      } catch (\Throwable $e) {
        error_log("[TetapanSistem] Session start failed: " . $e->getMessage());
        // Continue - session might already be started
      }
    }
  }

  /** Validate CSRF token */
  private function validateCSRF(): void {
    $this->ensureSession();
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
      set_alert([
        'title' => 'Ralat Keselamatan',
        'text' => 'CSRF token tidak sah. Sila muat semula halaman dan cuba lagi.',
        'icon' => 'error'
      ]);
      header('Location: tetapan-sistem.php');
      exit;
    }
  }

  /** Check authorization - hanya Super Admin */
  private function checkAuthorization(): void {
    $f_stafID = $_SESSION['f_stafID'] ?? null;
    if (!$f_stafID) {
      set_alert(['title'=>'Akses Ditolak','text'=>'Sila log masuk terlebih dahulu.','icon'=>'error']);
      header('Location: ../index.php');
      exit;
    }
    
    $userGroupId = (int)($this->profile['f_groupID'] ?? 0);
    if ($userGroupId !== PRESTASI_ROLE_ID_ADM_SA) {
      set_alert([
        'title' => 'Akses Ditolak',
        'text' => 'Hanya Super Admin dibenarkan mengakses halaman Konfigurasi Sistem.',
        'icon' => 'error'
      ]);
      header('Location: dashboard.php');
      exit;
    }
  }

  // ---------------------------
  // 🔧 Helpers & DB settings
  // ---------------------------

  /** logical -> base (tanpa _dsn/_dblib) */
  private function logicalToBase(string $logical): ?string {
    return match (strtolower($logical)) {
      'ehrmdb'     => 'sybase_ehrmdb',
      'ehrmdb_dev' => 'sybase_ehrmdb_dev',
      'stafdb'     => 'sybase_stafdb',
      default      => null,
    };
  }

  /** Baca flags JSON untuk paparan */
  public function getActiveDBConfig(): array {
    $path = __DIR__ . '/../configuration/config_db_active.json';
    if (is_file($path)) {
      try {
        $content = file_get_contents($path);
        if ($content === false) {
          error_log("[TetapanSistem] Failed to read config file: {$path}");
          return ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
        }
        $data = json_decode($content, true);
        if (!is_array($data)) {
          error_log("[TetapanSistem] Invalid JSON in config file: {$path}");
          return ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
        }
        $data += ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
        return array_intersect_key($data, ['ehrmdb'=>1,'ehrmdb_dev'=>1,'stafdb'=>1]);
      } catch (\Throwable $e) {
        error_log("[TetapanSistem] Error reading config file: " . $e->getMessage());
        return ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
      }
    }
    return ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
  }

  /** Normalise pilihan POST */
  private function normalizeSelected(string $selected): string {
    return match (strtolower(trim($selected))) {
      'ehrmdb','ehrmdb_dev','stafdb' => strtolower(trim($selected)),
      default => '',
    };
  }

  /** Simpan flags JSON */
  public function saveActiveDBConfig(array $config): bool {
    $path = __DIR__ . '/../configuration/config_db_active.json';
    $dir = dirname($path);
    
    // Ensure directory exists and is writable
    if (!is_dir($dir)) {
      try {
        if (!mkdir($dir, 0755, true)) {
          error_log("[TetapanSistem] Failed to create directory: {$dir}");
          return false;
        }
      } catch (\Throwable $e) {
        error_log("[TetapanSistem] Error creating directory: " . $e->getMessage());
        return false;
      }
    }
    
    if (!is_writable($dir)) {
      error_log("[TetapanSistem] Directory not writable: {$dir}");
      return false;
    }
    
    $json = json_encode([
      'ehrmdb'     => (bool)($config['ehrmdb']     ?? false),
      'ehrmdb_dev' => (bool)($config['ehrmdb_dev'] ?? false),
      'stafdb'     => (bool)($config['stafdb']     ?? false),
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    
    try {
      $result = file_put_contents($path, $json, LOCK_EX);
      if ($result === false) {
        error_log("[TetapanSistem] Failed to write config file: {$path}");
        return false;
      }
      return true;
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Error writing config file: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Uji sambungan Sybase berdasarkan **base key** (suffix auto oleh Database::getInstance)
   */
  public function testSybaseConnection(string $logicalName): bool {
    $logicalName = $this->normalizeSelected($logicalName);
    if ($logicalName === '') return false;
    $base = $this->logicalToBase($logicalName);
    if (!$base) return false;
    try {
      $pdo  = Database::getInstance($base)->getConnection(); // auto _dsn/_dblib
      $stmt = $pdo->query('SELECT getdate()');
      return $stmt !== false;
    } catch (Throwable $e) {
      return false;
    }
  }

  public function getMysqlInfo(): array {
    return $this->db_configs['mysql'] ?? [];
  }

  /**
   * ❗API: Aktifkan Sybase base (persist ke DB + session + JSON).
   */
  public function activateSybaseBase(string $logical): bool {
    $this->ensureSession();

    $selected = $this->normalizeSelected($logical);
    if ($selected === '') {
      throw new \RuntimeException('Pilihan DB tidak sah.');
    }
    if (!$this->testSybaseConnection($selected)) {
      throw new \RuntimeException('Sambungan ke '.$selected.' gagal.');
    }

    $base = $this->logicalToBase($selected); // 'sybase_ehrmdb' | 'sybase_ehrmdb_dev' | 'sybase_stafdb'

    // Session (immediate effect)
    $_SESSION['SYBASE_ACTIVE_BASE'] = $base;

    // Persist ke DB config (system)
    try {
      if (method_exists($this->configModel, 'setSybaseActiveBase')) {
        $this->configModel->setSybaseActiveBase($base);
      } elseif (method_exists($this->configModel, 'setValue')) {
        $this->configModel->setValue('SYBASE_ACTIVE_BASE', $base, 'system');
      } else {
        $this->configModel->saveGroup('system', ['SYBASE_ACTIVE_BASE' => $base]);
      }
    } catch (\Throwable $e) {
      // abaikan; session sudah cover runtime
    }

    // JSON flags (hanya satu true)
    $flags = ['ehrmdb'=>false,'ehrmdb_dev'=>false,'stafdb'=>false];
    $flags[$selected] = true;
    $this->saveActiveDBConfig($flags);

    // Refresh state dalaman
    $this->active_db_flags    = $flags;
    $this->active_sybase_name = $base;

    // Bersih micro-cache page tetapan (kalau ada)
    $this->invalidateTsCache('dbcfg');

    return true;
  }

  /** Proses POST tab DB */
  private function prosesSimpananDB(): void {
    $this->ensureSession();
    $selectedRaw = trim($_POST['active_db'] ?? '');
    
    if (empty($selectedRaw)) {
      set_alert([
        'title' => 'Ralat Validasi',
        'text' => 'Sila pilih pangkalan data yang ingin diaktifkan.',
        'icon' => 'error'
      ]);
      header('Location: tetapan-sistem.php?tab=db');
      exit;
    }
    
    try {
      $oldBase = $this->active_sybase_name;
      $this->activateSybaseBase($selectedRaw);
      $dbName = strtolower($this->normalizeSelected($selectedRaw));
      
      // Audit logging
      $this->auditDatabaseUpdate($oldBase, $this->active_sybase_name);
      
      set_alert([
        'title'   => 'Berjaya',
        'text'    => "Pangkalan data '{$dbName}' telah berjaya diaktifkan.",
        'icon'    => 'success',
        'confirm' => true,
        'confirmText' => 'config_js_btn_tutup',
        'is_key'  => false,
        'title_is_key' => false,
        'text_is_key'  => false,
      ]);
    } catch (\RuntimeException $e) {
      $message = match(true) {
        str_contains($e->getMessage(), 'tidak sah') => 'Pilihan pangkalan data tidak sah. Sila pilih salah satu pilihan yang tersedia (ehrmdb, ehrmdb_dev, atau stafdb).',
        str_contains($e->getMessage(), 'gagal') => 'Sambungan ke pangkalan data gagal. Sila semak konfigurasi sambungan database atau hubungi pentadbir sistem.',
        default => $e->getMessage()
      };
      set_alert([
        'title' => 'Ralat Sambungan Database',
        'text' => $message,
        'icon' => 'error'
      ]);
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Database activation error: " . $e->getMessage());
      set_alert([
        'title' => 'Ralat Sistem',
        'text' => 'Ralat berlaku semasa mengaktifkan pangkalan data. Sila cuba lagi atau hubungi pentadbir sistem.',
        'icon' => 'error'
      ]);
    }
    header('Location: tetapan-sistem.php?tab=db');
    exit;
  }

  /**
   * Cari base key aktif untuk paparan.
   * Priority: flag JSON → nilai dalam DB (system) → null
   */
  private function findActiveSybaseName(): ?string {
    $this->ensureSession();

    // 1) Session / constant (runtime SSoT)
    if (!empty($_SESSION['SYBASE_ACTIVE_BASE'])) return (string)$_SESSION['SYBASE_ACTIVE_BASE'];
    if (defined('SYBASE_ACTIVE_BASE')) return (string)SYBASE_ACTIVE_BASE;

    // 2) Persisted in DB config (system)
    $fromDb = $this->configModel->getSybaseActiveBase(null);
    if ($fromDb) return $fromDb;

    // 3) JSON flags (legacy)
    foreach (['ehrmdb','ehrmdb_dev','stafdb'] as $k) {
      if (!empty($this->active_db_flags[$k])) {
        return $this->logicalToBase($k);
      }
    }

    return null;
  }

  /** base -> logical helper */
  private function baseToLogical(string $base): ?string {
    $base = strtolower($base);
    return match (true) {
      str_contains($base, 'ehrmdb_dev') => 'ehrmdb_dev',
      str_contains($base, 'stafdb')     => 'stafdb',
      str_contains($base, 'ehrmdb')     => 'ehrmdb',
      default                           => null,
    };
  }

  // ---------------------------
  // Validation
  // ---------------------------

  /**
   * Validate email settings
   * @return array Array of error messages, empty if valid
   */
  private function validateEmailSettings(array $data): array {
    $errors = [];
    
    // Mail Host validation
    if (!empty($data['mail_host'])) {
      $host = trim($data['mail_host']);
      // Check if it's a valid domain or IP
      if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && 
          !filter_var($host, FILTER_VALIDATE_IP)) {
        $errors[] = 'Mail Host tidak sah. Sila masukkan domain atau alamat IP yang sah.';
      }
      if (strlen($host) > SystemConfigConstants::MAX_STRING_LENGTH) {
        $errors[] = 'Mail Host terlalu panjang (maksimum ' . SystemConfigConstants::MAX_STRING_LENGTH . ' aksara).';
      }
    }
    
    // Mail Port validation
    if (!empty($data['mail_port'])) {
      $port = trim($data['mail_port']);
      if (!is_numeric($port)) {
        $errors[] = 'Port mesti nombor.';
      } else {
        $portNum = (int)$port;
        if ($portNum < SystemConfigConstants::MIN_PORT || $portNum > SystemConfigConstants::MAX_PORT) {
          $errors[] = 'Port mesti antara ' . SystemConfigConstants::MIN_PORT . ' hingga ' . SystemConfigConstants::MAX_PORT . '.';
        }
      }
    }
    
    // Mail Username validation
    if (!empty($data['mail_username'])) {
      $username = trim($data['mail_username']);
      if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email Username tidak sah. Sila masukkan alamat emel yang sah.';
      }
      if (strlen($username) > SystemConfigConstants::MAX_STRING_LENGTH) {
        $errors[] = 'Email Username terlalu panjang (maksimum ' . SystemConfigConstants::MAX_STRING_LENGTH . ' aksara).';
      }
    }
    
    // Mail From Address validation
    if (!empty($data['mail_from_address'])) {
      $fromAddr = trim($data['mail_from_address']);
      if (!filter_var($fromAddr, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Alamat Emel "Dari" tidak sah. Sila masukkan alamat emel yang sah.';
      }
      if (strlen($fromAddr) > SystemConfigConstants::MAX_STRING_LENGTH) {
        $errors[] = 'Alamat Emel "Dari" terlalu panjang (maksimum ' . SystemConfigConstants::MAX_STRING_LENGTH . ' aksara).';
      }
    }
    
    // Mail Encryption validation
    if (!empty($data['mail_encryption'])) {
      $encryption = strtolower(trim($data['mail_encryption']));
      if (!in_array($encryption, SystemConfigConstants::ALLOWED_MAIL_ENCRYPTION, true)) {
        $errors[] = 'Encryption tidak sah. Hanya ' . implode(' atau ', SystemConfigConstants::ALLOWED_MAIL_ENCRYPTION) . ' dibenarkan.';
      }
    }
    
    // Mail Driver validation
    if (!empty($data['mail_driver'])) {
      $driver = strtolower(trim($data['mail_driver']));
      if (!in_array($driver, SystemConfigConstants::ALLOWED_MAIL_DRIVERS, true)) {
        $errors[] = 'Mail Driver tidak sah. Hanya ' . implode(', ', SystemConfigConstants::ALLOWED_MAIL_DRIVERS) . ' dibenarkan.';
      }
    }
    
    // Mail From Name validation
    if (!empty($data['mail_from_name']) && strlen($data['mail_from_name']) > SystemConfigConstants::MAX_STRING_LENGTH) {
      $errors[] = 'Nama Pemilik Email terlalu panjang (maksimum ' . SystemConfigConstants::MAX_STRING_LENGTH . ' aksara).';
    }
    
    return $errors;
  }

  /**
   * Validate theme settings
   * @return array Array of error messages, empty if valid
   */
  private function validateThemeSettings(array $data): array {
    $errors = [];
    
    if (!empty($data['topbarColor']) && !in_array($data['topbarColor'], SystemConfigConstants::ALLOWED_THEME_COLORS, true)) {
      $errors[] = 'Warna Topbar tidak sah. Hanya ' . implode(', ', SystemConfigConstants::ALLOWED_THEME_COLORS) . ' dibenarkan.';
    }
    
    if (!empty($data['sidebarColor']) && !in_array($data['sidebarColor'], SystemConfigConstants::ALLOWED_THEME_COLORS, true)) {
      $errors[] = 'Warna Sidebar tidak sah. Hanya ' . implode(', ', SystemConfigConstants::ALLOWED_THEME_COLORS) . ' dibenarkan.';
    }
    
    if (!empty($data['layoutMode']) && !in_array($data['layoutMode'], SystemConfigConstants::ALLOWED_THEME_MODES, true)) {
      $errors[] = 'Mod Layout tidak sah. Hanya ' . implode(', ', SystemConfigConstants::ALLOWED_THEME_MODES) . ' dibenarkan.';
    }
    
    return $errors;
  }

  /**
   * Validate language settings
   * @return array Array of error messages, empty if valid
   */
  private function validateLanguageSettings(array $languages): array {
    $errors = [];
    
    if (empty($languages) || !is_array($languages)) {
      $errors[] = 'Sila pilih sekurang-kurangnya satu bahasa untuk diaktifkan.';
      return $errors;
    }
    
    foreach ($languages as $lang) {
      if (!in_array($lang, SystemConfigConstants::SUPPORTED_LANGUAGES, true)) {
        $errors[] = "Bahasa '{$lang}' tidak sah. Hanya " . implode(', ', SystemConfigConstants::SUPPORTED_LANGUAGES) . " dibenarkan.";
      }
    }
    
    return $errors;
  }

  // ---------------------------
  // Emel / Bahasa / Tema
  // ---------------------------

  public function getEmailSettings(): array {
    return $this->configModel->getGroup('email');
  }

  public function saveEmailSettings(array $data): bool {
    return $this->configModel->saveGroup('email', $data);
  }

  public function getLanguageList(): array {
    $dir = __DIR__ . '/../lang/';
    $languages = [];
    
    try {
      if (!is_dir($dir)) {
        error_log("[TetapanSistem] Language directory not found: {$dir}");
        return ['list' => [], 'active' => []];
      }
      
      $files = scandir($dir);
      if ($files === false) {
        error_log("[TetapanSistem] Failed to scan language directory: {$dir}");
        return ['list' => [], 'active' => []];
      }
      
      foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
          $code = basename($file, '.php');
          try {
            $languages[$code] = include $dir . $file;
          } catch (\Throwable $e) {
            error_log("[TetapanSistem] Failed to include language file {$file}: " . $e->getMessage());
          }
        }
      }
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Error reading language files: " . $e->getMessage());
      return ['list' => [], 'active' => []];
    }
    
    try {
      $aktif = $this->configModel->getBahasaAktif();
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Failed to get active languages: " . $e->getMessage());
      $aktif = [];
    }
    
    return ['list'=>array_keys($languages),'active'=>$aktif];
  }

  // ---------------------------
  // Audit Logging
  // ---------------------------

  /**
   * Audit email settings update
   */
  private function auditEmailUpdate(array $oldSettings, array $newSettings): void {
    if (!function_exists('audit_event')) return;
    
    try {
      // ✅ FIX: Format actor_label dengan nostaf full: "[nama] (nostaf)"
      $nama = $this->profile['f_nama'] ?? null;
      $nostaf = $this->profile['f_nopekerja'] ?? $_SESSION['f_nopekerja'] ?? null;
      $actorLabel = null;
      if (function_exists('audit_format_actor_label')) {
        $actorLabel = audit_format_actor_label($nama, $nostaf);
      } else {
        // Fallback: guna nama sahaja jika helper tidak available
        $actorLabel = $nama;
      }
      
      // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
      $message = audit_format_message('Email settings updated', $actorLabel);
      
      $eventId = audit_event([
        'event_type'  => SystemConfigConstants::AUDIT_EVENT_EMAIL_UPDATE,
        'severity'    => 'INFO',
        'outcome'     => 'SUCCESS',
        'target_type' => SystemConfigConstants::AUDIT_TARGET_EMAIL,
        'target_id'   => 'email_config',
        'target_label' => 'Email Configuration',
        'message'     => $message,
        'user_id'     => $_SESSION['user']['f_userID'] ?? $_SESSION['f_userID'] ?? $_SESSION['f_stafID'] ?? null,
        'actor_label' => $actorLabel,
        'meta'        => [
          'changed_fields' => array_keys(array_diff_assoc($newSettings, $oldSettings))
        ]
      ]);
      
      if ($eventId) {
        $changeSetId = audit_begin_change(
          $eventId,
          SystemConfigConstants::AUDIT_TARGET_EMAIL,
          'email_config',
          null,
          [
            'form' => 'emailSettings',
            'action' => 'update',
            'changed_fields' => array_keys(array_diff_assoc($newSettings, $oldSettings)),
            'source_page' => strtok($_SERVER['REQUEST_URI'] ?? ($_SERVER['SCRIPT_NAME'] ?? '/'), '?') ?: '/'
          ]
        );
        if ($changeSetId) {
          foreach ($newSettings as $key => $value) {
            $oldValue = $oldSettings[$key] ?? null;
            if ((string)$oldValue !== (string)$value) {
              $sensitive = ($key === 'mail_password');
              audit_change($changeSetId, $key, $oldValue, $value, 'string', $sensitive);
            }
          }
        }
      }
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Audit logging failed: " . $e->getMessage());
    }
  }

  /**
   * Audit database update
   */
  private function auditDatabaseUpdate(?string $oldBase, ?string $newBase): void {
    if (!function_exists('audit_event')) return;
    
    try {
      // ✅ FIX: Format actor_label dengan nostaf full: "[nama] (nostaf)"
      $nama = $this->profile['f_nama'] ?? null;
      $nostaf = $this->profile['f_nopekerja'] ?? $_SESSION['f_nopekerja'] ?? null;
      $actorLabel = null;
      if (function_exists('audit_format_actor_label')) {
        $actorLabel = audit_format_actor_label($nama, $nostaf);
      } else {
        // Fallback: guna nama sahaja jika helper tidak available
        $actorLabel = $nama;
      }
      
      // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
      $message = audit_format_message("Database changed from '{$oldBase}' to '{$newBase}'", $actorLabel);
      
      audit_event([
        'event_type'  => SystemConfigConstants::AUDIT_EVENT_DB_UPDATE,
        'severity'    => 'WARN',
        'outcome'     => 'SUCCESS',
        'target_type' => SystemConfigConstants::AUDIT_TARGET_DB,
        'target_id'   => 'active_database',
        'target_label' => 'Active Database',
        'message'     => $message,
        'user_id'     => $_SESSION['user']['f_userID'] ?? $_SESSION['f_userID'] ?? $_SESSION['f_stafID'] ?? null,
        'actor_label' => $actorLabel,
        'meta'        => [
          'old_base' => $oldBase,
          'new_base' => $newBase
        ]
      ]);
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Audit logging failed: " . $e->getMessage());
    }
  }

  /**
   * Audit theme update
   */
  private function auditThemeUpdate(array $oldTheme, array $newTheme): void {
    if (!function_exists('audit_event')) return;
    
    try {
      // ✅ FIX: Format actor_label dengan nostaf full: "[nama] (nostaf)"
      $nama = $this->profile['f_nama'] ?? null;
      $nostaf = $this->profile['f_nopekerja'] ?? $_SESSION['f_nopekerja'] ?? null;
      $actorLabel = null;
      if (function_exists('audit_format_actor_label')) {
        $actorLabel = audit_format_actor_label($nama, $nostaf);
      } else {
        // Fallback: guna nama sahaja jika helper tidak available
        $actorLabel = $nama;
      }
      
      // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
      $message = audit_format_message('Default theme settings updated', $actorLabel);
      
      $eventId = audit_event([
        'event_type'  => SystemConfigConstants::AUDIT_EVENT_THEME_UPDATE,
        'severity'    => 'INFO',
        'outcome'     => 'SUCCESS',
        'target_type' => SystemConfigConstants::AUDIT_TARGET_THEME,
        'target_id'   => 'default_theme',
        'target_label' => 'Default Theme',
        'message'     => $message,
        'user_id'     => $_SESSION['user']['f_userID'] ?? $_SESSION['f_userID'] ?? $_SESSION['f_stafID'] ?? null,
        'actor_label' => $actorLabel
      ]);
      
      if ($eventId) {
        $changeSetId = audit_begin_change(
          $eventId,
          SystemConfigConstants::AUDIT_TARGET_THEME,
          'default_theme',
          null,
          [
            'form' => 'themeSettings',
            'action' => 'update',
            'source_page' => strtok($_SERVER['REQUEST_URI'] ?? ($_SERVER['SCRIPT_NAME'] ?? '/'), '?') ?: '/'
          ]
        );
        if ($changeSetId) {
          foreach ($newTheme as $key => $value) {
            $oldValue = $oldTheme[$key] ?? null;
            if ((string)$oldValue !== (string)$value) {
              audit_change($changeSetId, $key, $oldValue, $value, 'string', false);
            }
          }
        }
      }
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Audit logging failed: " . $e->getMessage());
    }
  }

  /**
   * Audit language update
   */
  private function auditLanguageUpdate(array $oldLanguages, array $newLanguages): void {
    if (!function_exists('audit_event')) return;
    
    try {
      // ✅ FIX: Format actor_label dengan nostaf full: "[nama] (nostaf)"
      $nama = $this->profile['f_nama'] ?? null;
      $nostaf = $this->profile['f_nopekerja'] ?? $_SESSION['f_nopekerja'] ?? null;
      $actorLabel = null;
      if (function_exists('audit_format_actor_label')) {
        $actorLabel = audit_format_actor_label($nama, $nostaf);
      } else {
        // Fallback: guna nama sahaja jika helper tidak available
        $actorLabel = $nama;
      }
      
      // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
      $message = audit_format_message('Active languages updated', $actorLabel);
      
      audit_event([
        'event_type'  => SystemConfigConstants::AUDIT_EVENT_LANGUAGE_UPDATE,
        'severity'    => 'INFO',
        'outcome'     => 'SUCCESS',
        'target_type' => SystemConfigConstants::AUDIT_TARGET_LANGUAGE,
        'target_id'   => 'active_languages',
        'target_label' => 'Active Languages',
        'message'     => $message,
        'user_id'     => $_SESSION['user']['f_userID'] ?? $_SESSION['f_userID'] ?? $_SESSION['f_stafID'] ?? null,
        'actor_label' => $actorLabel,
        'meta'        => [
          'old_languages' => $oldLanguages,
          'new_languages' => $newLanguages,
          'added' => array_diff($newLanguages, $oldLanguages),
          'removed' => array_diff($oldLanguages, $newLanguages)
        ]
      ]);
    } catch (\Throwable $e) {
      error_log("[TetapanSistem] Audit logging failed: " . $e->getMessage());
    }
  }

  // ---------------------------
  // Micro-cache invalidation
  // ---------------------------
  private function invalidateTsCache(string $name): void {
    $key  = 'tetapan-sistem:v1:'.$name;
    
    // Try to delete from APCu
    if (function_exists('apcu_delete')) {
      try {
        apcu_delete($key);
      } catch (\Throwable $e) {
        error_log("[TetapanSistem] APCu delete failed: " . $e->getMessage());
      }
    }
    
    // Delete file cache (gunakan cache dir projek)
    $cacheDir = realpath(__DIR__ . '/../cache/ts') ?: (__DIR__ . '/../cache/ts');
    $file = rtrim($cacheDir, DIRECTORY_SEPARATOR) . '/ts-cache-' . md5($key) . '.json';
    if (is_file($file)) {
      try {
        unlink($file);
      } catch (\Throwable $e) {
        error_log("[TetapanSistem] File delete failed: " . $e->getMessage());
      }
    }
  }
}
