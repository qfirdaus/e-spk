<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../setting/function.php';
require_once __DIR__ . '/../classes/Config.php';

// Helper escape
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// Helper: bina URL semasa + ganti/tambah 1 query param
if (!function_exists('url_with_param')) {
  function url_with_param(string $key, string $val): string {
    $req = $_SERVER['REQUEST_URI'] ?? '/';
    $p   = parse_url($req);
    $path= $p['path'] ?? '/';
    parse_str($p['query'] ?? '', $q);
    $q[$key] = $val;
    $qs = http_build_query($q);
    return $path . ($qs ? '?'.$qs : '');
  }
}

$pdo_mysql  = Database::getInstance('mysql')->getConnection();
$user       = new User($pdo_mysql);
$f_stafID   = $_SESSION['f_stafID'] ?? null;
$profile    = $f_stafID ? ($user->getProfile($f_stafID) ?: []) : [];

$nama_pengguna     = $profile['f_nama'] ?? ($profile['f_nickname'] ?? 'Pengguna');
$peranan_pengguna  = $profile['f_groupName'] ?? 'Pengguna';
$avatarUrl         = $user->getAvatarUrl($profile['f_nopekerja'] ?? null);
$lang              = $_SESSION['lang'] ?? 'ms';

// CSRF for AJAX actions in topbar
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['csrf_token'];

// Role switching: determine default/active role in session
$defaultGroupId = (int)($profile['f_groupID'] ?? 0);
if (!isset($_SESSION['group_default_id']) && $defaultGroupId > 0) {
  $_SESSION['group_default_id'] = $defaultGroupId;
}
if (!isset($_SESSION['group_active_id']) && $defaultGroupId > 0) {
  $_SESSION['group_active_id'] = $defaultGroupId;
}
$activeGroupId = (int)($_SESSION['group_active_id'] ?? $defaultGroupId);

// Fetch active group name for display (do not change user default role)
if ($activeGroupId > 0 && $activeGroupId !== $defaultGroupId) {
  try {
    $stmtAct = $pdo_mysql->prepare("SELECT f_groupName FROM tbl_m_group WHERE f_groupID = :gid LIMIT 1");
    $stmtAct->execute([':gid' => $activeGroupId]);
    $rowAct = $stmtAct->fetch(PDO::FETCH_ASSOC);
    if (!empty($rowAct['f_groupName'])) {
      $peranan_pengguna = (string)$rowAct['f_groupName'];
    }
  } catch (Throwable $e) {
    // keep original label on failure
  }
}

// Allowed roles for role switcher (tbl_ref_access + tbl_m_group)
$allowedRoles = [];
$hasExtraRole = false;
try {
  $stafID = (string)($profile['f_stafID'] ?? $_SESSION['f_stafID'] ?? '');
  $stafRaw = trim($stafID);
  $stafNorm = str_replace('-', '', $stafRaw);
  if ($stafRaw !== '' || $stafNorm !== '') {
    // Lightweight eligibility check
    $stmtHas = $pdo_mysql->prepare("
      SELECT 1
      FROM tbl_ref_access a
      WHERE (TRIM(a.f_stafID) = :staf OR REPLACE(TRIM(a.f_stafID), '-', '') = :staf_norm)
        AND a.f_status = 1
      LIMIT 1
    ");
    $stmtHas->execute([':staf' => $stafRaw, ':staf_norm' => $stafNorm]);
    $hasExtraRole = (bool)$stmtHas->fetchColumn();

    $stmtRoles = $pdo_mysql->prepare("
      SELECT a.f_groupID, g.f_groupKod, g.f_groupName
      FROM tbl_ref_access a
      JOIN tbl_m_group g ON g.f_groupID = a.f_groupID
      WHERE (TRIM(a.f_stafID) = :staf OR REPLACE(TRIM(a.f_stafID), '-', '') = :staf_norm)
        AND a.f_status = 1
      ORDER BY g.f_groupName ASC, g.f_groupKod ASC
    ");
    $stmtRoles->execute([':staf' => $stafRaw, ':staf_norm' => $stafNorm]);
    $allowedRoles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
} catch (Throwable $e) {
  $allowedRoles = [];
  $hasExtraRole = false;
}

// Default role label (from tbl_m_user)
$defaultGroupName = $profile['f_groupName'] ?? 'Pengguna';

// Tetapan bahasa aktif dari DB
$config = new Config($pdo_mysql);
$bahasaAktif = array_values(array_filter($config->getBahasaAktif() ?: []));
if (!$bahasaAktif) $bahasaAktif = ['ms'];

// Bendera & label
$langFlag = [
  'ms' => 'malaysia.png',
  'en' => 'united-kingdom.png',
];
$langLabel = [
  'ms' => 'Bahasa Melayu',
  'en' => 'English',
];

$topbarColor = $_SESSION['theme.topbar'] ?? 'light';

// Flash message for role switch success
$roleSwitchFlash = $_SESSION['role_switch_success'] ?? null;
if ($roleSwitchFlash !== null) {
  unset($_SESSION['role_switch_success']);
}
?>

<!-- Global Loader -->
<!-- <div id="global-loader" aria-live="polite" aria-busy="true">
  <div class="loader-inner">
    <span
      class="spinner"
      role="status"
      aria-label="<?= htmlspecialchars(__('config_js_loading') ?: 'Loading…', ENT_QUOTES, 'UTF-8') ?>">
    </span>
  </div>
</div> -->



<!-- ========== Development Mode Banner (Overlay) ========== -->
<?php if (function_exists('is_development_mode') && is_development_mode()): ?>
  <div class="dev-mode-tab" role="alert" aria-live="polite">
    <div class="container-fluid d-flex align-items-center justify-content-center gap-2">
      <i class="ri-code-s-slash-line fs-16"></i>
      <span class="fw-semibold">DEVELOPMENT MODE</span>
      <span class="small d-none d-md-inline" style="opacity: 0.9;">| Database: <?= h(defined('SYBASE_ACTIVE_BASE') ? SYBASE_ACTIVE_BASE : 'Unknown') ?></span>
    </div>
  </div>
<?php endif; ?>

<!-- ========== Topbar Start ========== -->
<div id="topbar" class="navbar-custom" data-topbar-color="<?= h($topbarColor) ?>">
  <div class="topbar container-fluid">
    <div class="d-flex align-items-center gap-lg-2 gap-1">
      <!-- Logo -->
      <div class="logo-topbar">
        <a href="<?= h(base_url('pages/dashboard.php')) ?>" class="logo-light">
          <span class="logo-lg"><img src="<?= h(base_url('assets/images/logo.png')) ?>" alt="logo"></span>
          <span class="logo-sm"><img src="<?= h(base_url('assets/images/logo-sm.png')) ?>" alt="small logo"></span>
        </a>
        <a href="<?= h(base_url('pages/dashboard.php')) ?>" class="logo-dark">
          <span class="logo-lg"><img src="<?= h(base_url('assets/images/logo-dark.png')) ?>" alt="dark logo"></span>
          <span class="logo-sm"><img src="<?= h(base_url('assets/images/logo-sm.png')) ?>" alt="small logo"></span>
        </a>
      </div>

      <!-- Sidebar Menu Toggle Button -->
      <button class="button-toggle-menu">
        <i class="ri-menu-2-fill"></i>
      </button>
      
    </div>

    <ul class="topbar-menu d-flex align-items-center gap-3">

      <?php
        $activeLangCount = count($bahasaAktif);
        $currentFlag  = $langFlag[$lang] ?? 'malaysia.png';
        $currentLabel = $langLabel[$lang] ?? strtoupper($lang);
      ?>

      <!-- Language -->
      <?php if ($activeLangCount <= 1): ?>
        <li>
          <div class="nav-link">
            <img src="<?= h(base_url('assets/images/flags/'.$currentFlag)) ?>" height="16" alt="flag"> 
            <span class="d-none d-lg-inline-block"><?= h($currentLabel) ?></span>
          </div>
        </li>
      <?php else: ?>
        <li class="dropdown">
          <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
            <img src="<?= h(base_url('assets/images/flags/'.$currentFlag)) ?>" height="16" alt="flag">&nbsp;
            <span class="d-none d-lg-inline-block"><?= h($currentLabel) ?></span>
            <i class="ri-arrow-down-s-line d-none d-sm-inline-block align-middle"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated" data-bs-auto-close="outside">
            <?php foreach ($bahasaAktif as $key): if (!isset($langLabel[$key], $langFlag[$key])) continue; ?>
              <?php $href = url_with_param('lang', $key); ?>
              <a href="<?= h($href) ?>"
                 class="dropdown-item<?= ($lang === $key ? ' active fw-semibold text-primary':'') ?>"
                 data-lang-link="1">
                <img src="<?= h(base_url('assets/images/flags/'.$langFlag[$key])) ?>" height="12" class="me-1" alt="">
                <?= h($langLabel[$key]) ?>
                <?= ($lang === $key) ? '<i class="ri-check-line ms-1"></i>' : '' ?>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
      <?php endif; ?>

      <!-- Notification (dummy) -->
      <li class="dropdown notification-list">
        <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
          <i class="ri-notification-3-fill fs-22"></i><span class="noti-icon-badge"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0" data-bs-auto-close="outside">
          <div class="p-2 border-dashed border-top-0 d-flex justify-content-between align-items-center">
            <h6 class="fs-16 fw-medium m-0">Notification</h6>
            <a href="#" class="text-dark text-decoration-underline"><small>Clear All</small></a>
          </div>
          <div style="max-height: 300px;" data-simplebar>
            <h5 class="text-muted fs-12 fw-bold p-2 text-uppercase mb-0">Today</h5>
            <a href="#" class="dropdown-item p-0 notify-item unread-noti card m-0 shadow-none">
              <div class="card-body d-flex align-items-center">
                <div class="notify-icon bg-primary me-2"><i class="ri-message-3-line fs-18"></i></div>
                <div class="flex-grow-1 text-truncate">
                  <h5 class="noti-item-title fw-medium fs-14">Contoh Notifikasi
                    <small class="float-end text-muted ms-1">1 min ago</small>
                  </h5>
                  <small class="text-muted">Notifikasi sistem e-Prestasi</small>
                </div>
              </div>
            </a>
          </div>
          <a href="#" class="dropdown-item text-center text-primary fw-bold border-top py-2">View All</a>
        </div>
      </li>

      <!-- Theme / Fullscreen -->
      <li class="d-none d-sm-inline-block">
        <a class="nav-link" data-bs-toggle="offcanvas" href="#theme-settings-offcanvas" role="button" aria-controls="theme-settings-offcanvas">
          <i class="ri-settings-3-fill fs-22"></i>
        </a>
      </li>
      <li class="d-none d-sm-inline-block"><div class="nav-link" id="light-dark-mode"><i class="ri-moon-fill fs-22" id="theme-mode-icon"></i></div></li>
      <li class="d-none d-md-inline-block"><a class="nav-link" href="#" id="toggle-fullscreen"><i class="ri-fullscreen-line fs-22"></i></a></li>

      <!-- Akaun Pengguna -->
      <li class="dropdown me-md-2">
        <a class="nav-link dropdown-toggle nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
          <span class="account-user-avatar">
            <img src="<?= h($avatarUrl) ?>" width="32" class="rounded-circle"
                 onerror="this.onerror=null;this.src='<?= h(base_url('assets/images/no-image.jpg')) ?>';" alt="">
          </span>
          <span class="d-lg-flex flex-column gap-1 d-none">
            <h5 class="my-0"><?= h($nama_pengguna) ?></h5>
            <h6 class="my-0 fw-normal"><?= h($peranan_pengguna) ?></h6>
          </span>
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown" data-bs-auto-close="outside">
          <div class="dropdown-header noti-title"><h6 class="text-overflow m-0"><?= h(__('topbar_welcome')) ?></h6></div>
          <a href="<?= h(base_url('pages/profile.php')) ?>" class="dropdown-item"><i class="ri-account-circle-fill me-1"></i> Profil Saya</a>
          <?php if ($hasExtraRole): ?>
            <a href="#" class="dropdown-item" id="btnSwitchRole">
              <i class="ri-shuffle-line me-1"></i> <?= h(__('topbar_switch_role') ?? 'Tukar Peranan') ?>
            </a>
          <?php endif; ?>
          <!-- <a href="<?= h(base_url('pages/pages-settings.html')) ?>" class="dropdown-item"><i class="ri-settings-4-fill me-1"></i> Tetapan</a>
          <a href="<?= h(base_url('pages/pages-faq.html')) ?>" class="dropdown-item"><i class="ri-customer-service-2-fill me-1"></i> Sokongan</a> -->
          <!-- <a href="<?= h(base_url('pages/auth-lock-screen.html')) ?>" class="dropdown-item"><i class="ri-lock-password-fill me-1"></i> Kunci Skrin</a> -->

          <!-- Logout -->
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item text-danger" onclick="return confirmLogout();">
            <i class="ri-logout-box-fill me-1"></i> <?= __('topbar_keluar') ?>
          </a>

        </div>
      </li>

    </ul>
  </div>
</div>
<!-- ========== Topbar End ========== -->

<!-- ========== Role Switcher Modal (Topbar) ========== -->
<div class="modal fade" id="switchRoleModal" tabindex="-1" aria-hidden="true" aria-labelledby="switchRoleTitle">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="switchRoleTitle">
          <i class="ri-shuffle-line me-2"></i> <?= h(__('topbar_switch_role_title') ?? 'Tukar Peranan') ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= h(__('userList_modal_btn_close')) ?>"></button>
      </div>
      <div class="modal-body">
        <form id="switchRoleForm" autocomplete="off">
          <input type="hidden" id="sr_csrf" value="<?= h($csrfToken) ?>">

          <div class="form-section">
            <div class="form-section-title">
              <i class="ri-shield-user-line me-1"></i> <?= h(__('topbar_switch_role_select') ?? 'Pilih Peranan') ?>
            </div>

            <div class="mb-2 text-muted small" id="switchRolePrimary">
              <?= h(__('topbar_switch_role_primary_label') ?? 'Peranan utama') ?>: <strong id="switchRolePrimaryName"><?= h($defaultGroupName) ?></strong>
            </div>
            <?php if (!empty($allowedRoles) || $defaultGroupId > 0): ?>
              <div class="role-list" id="switchRoleList">
                <?php if ($defaultGroupId > 0): ?>
                  <label class="role-item">
                    <input type="radio" name="group_active_id" value="<?= h((string)$defaultGroupId) ?>" <?= ($defaultGroupId === $activeGroupId ? 'checked' : '') ?>>
                    <span class="role-label">
                      <?= h($defaultGroupName) ?> <span class="text-muted">(<?= h(__('topbar_switch_role_primary_tag') ?? 'Peranan Utama') ?>)</span>
                    </span>
                  </label>
                <?php endif; ?>
                <?php foreach ($allowedRoles as $r): 
                  $rid  = (int)($r['f_groupID'] ?? 0);
                  $rkod = (string)($r['f_groupKod'] ?? '');
                  $rnam = (string)($r['f_groupName'] ?? '');
                  if ($rid <= 0 || $rid === $defaultGroupId) continue;
                  $checked = ($rid === $activeGroupId) ? 'checked' : '';
                ?>
                  <label class="role-item">
                    <input type="radio" name="group_active_id" value="<?= h((string)$rid) ?>" <?= $checked ?>>
                    <span class="role-label"><?= h($rnam) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <div class="text-muted d-none" id="switchRoleEmpty"><?= h(__('topbar_switch_role_none') ?? 'Tiada peranan lain yang dibenarkan.') ?></div>
            <?php else: ?>
              <div class="text-muted" id="switchRoleEmpty"><?= h(__('topbar_switch_role_none') ?? 'Tiada peranan lain yang dibenarkan.') ?></div>
              <div class="role-list d-none" id="switchRoleList"></div>
            <?php endif; ?>
          </div>
        </form>
        <div id="switchRoleError" class="modal-error alert alert-danger d-none mt-3"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> <?= __('userList_modal_btn_cancel') ?>
        </button>
        <button type="button" class="btn btn-primary" id="switchRoleSaveBtn" <?= (empty($allowedRoles) && $defaultGroupId <= 0) ? 'disabled' : '' ?>>
          <i class="ri-save-3-line me-1"></i> <?= __('userList_modal_btn_save') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ========== Development Mode Banner CSS ========== -->
<style>
  /* Role Switcher Modal - match Tambah Pengguna style */
  #switchRoleModal .modal-dialog { max-width: 700px; }
  #switchRoleModal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-bottom: none;
    padding: 1.25rem 1.75rem;
  }
  #switchRoleModal .modal-header .modal-title {
    color: #fff;
    font-weight: 600;
    font-size: 1.15rem;
    letter-spacing: 0.3px;
  }
  #switchRoleModal .modal-header .btn-close { filter: invert(1); opacity: 0.9; }
  #switchRoleModal .modal-header .btn-close:hover { opacity: 1; }
  #switchRoleModal .modal-body { padding: 1.5rem 1.75rem; }
  #switchRoleModal .modal-footer {
    border-top: none;
    padding: 1rem 1.75rem;
    background-color: #f8f9fa;
  }
  #switchRoleModal .modal-content { border: none; }
  #switchRoleModal .form-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 2px solid #e9ecef;
  }
  #switchRoleModal .form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
  }
  #switchRoleModal .form-section-title {
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
  #switchRoleModal .form-section-title i { margin-right: 0.5rem; color: #667eea; }
  #switchRoleModal .modal-footer .btn {
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    border-radius: 0.5rem;
  }
  #switchRoleModal .role-list { display: grid; gap: 0.75rem; }
  #switchRoleModal .role-item {
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
  #switchRoleModal .role-item:hover {
    border-color: #667eea;
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.12);
  }
  #switchRoleModal .role-item input[type="radio"] { transform: scale(1.1); }
  #switchRoleModal .role-label { font-weight: 600; color: #212529; }

  /* Development Mode Tab - RDC-like control tab centered at top edge */
  .dev-mode-tab {
    position: fixed;
    top: 0; /* flush to top edge */
    left: 50%;
    transform: translateX(-50%);
    z-index: 10001; /* above topbar */

    /* Target width expanded to ~380-520px */
    min-width: 380px;
    max-width: 520px;
    width: min(520px, calc(100% - 32px));
    height: 28px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: linear-gradient(180deg, #12264a 0%, #0b2340 100%); /* dark blue gradient */
    color: #ffffff;
    padding: 0 12px;

    /* Top flat, bottom rounded using pseudo-element for smoother curve */
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;

    box-shadow: 0 2px 8px rgba(0,0,0,0.35);

    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;

    pointer-events: none; /* allow clicks through to topbar */
  }

  /* Use pseudo-element to accentuate the bottom curve (subtle) */
  .dev-mode-tab::after {
    content: "";
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    bottom: -10px;
    width: 80%;
    height: 16px;
    background: linear-gradient(180deg, rgba(11,35,64,0.95), rgba(8,24,46,0.95));
    border-bottom-left-radius: 999px;
    border-bottom-right-radius: 999px;
    box-shadow: 0 6px 10px rgba(0,0,0,0.25);
    pointer-events: none;
  }

  .dev-mode-tab .fw-semibold { color: #fff !important; pointer-events: none; }
  .dev-mode-tab i { color: rgba(255,255,255,0.95); margin-right: 8px; pointer-events: none; }
  .dev-mode-tab .small { margin-left: 8px; opacity: 0.9; font-size: 0.68rem; pointer-events: none; }

  @media (max-width: 340px) {
    .dev-mode-tab { width: calc(100% - 24px); min-width: 220px; }
    .dev-mode-tab .small { display: none; }
  }
</style>

<!-- ========== Topbar JS (kalis block) ========== -->
<script>
  // Paksa toggle dropdown + auto-tutup dropdown lain dalam #topbar
  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('#topbar [data-bs-toggle="dropdown"]');
      if (!toggle) return;

      e.preventDefault();
      e.stopImmediatePropagation();

      const thisLi = toggle.closest('.dropdown');

      // Tutup SEMUA dropdown lain yang sedang terbuka
      document.querySelectorAll('#topbar .dropdown-menu.show').forEach(menu => {
        const li = menu.closest('.dropdown');
        if (!thisLi || li !== thisLi) {
          const otherT = li?.querySelector('[data-bs-toggle="dropdown"]');
          if (otherT) bootstrap.Dropdown.getOrCreateInstance(otherT).hide();
          else menu.classList.remove('show'); // fallback
        }
      });

      // Toggle dropdown yang diklik
      const dd   = bootstrap.Dropdown.getOrCreateInstance(toggle);
      const menu = thisLi?.querySelector('.dropdown-menu');
      (menu && menu.classList.contains('show')) ? dd.hide() : dd.show();
    }, { capture: true });
</script>

<!-- ========== Role Switcher JS ========== -->
<script>
  (function(){
    let btn = null;
    let modalEl = null;
    let saveBtn = null;
    let errEl = null;
    let modal = null;
    let csrf = '';
    let listEl = null;
    let emptyEl = null;
    let primaryNameEl = null;

    function initRoleSwitch(){
      btn = document.getElementById('btnSwitchRole');
      modalEl = document.getElementById('switchRoleModal');
      saveBtn = document.getElementById('switchRoleSaveBtn');
      errEl = document.getElementById('switchRoleError');
      listEl = document.getElementById('switchRoleList');
      emptyEl = document.getElementById('switchRoleEmpty');
      primaryNameEl = document.getElementById('switchRolePrimaryName');
      if (!btn || !modalEl || !saveBtn || !window.bootstrap || !bootstrap.Modal) return;
      csrf = document.getElementById('sr_csrf')?.value || '';
      modal = bootstrap.Modal.getOrCreateInstance(modalEl);

      // Clean up duplicate backdrops and modal state
      function cleanupBackdrops(){
        const backs = document.querySelectorAll('.modal-backdrop');
        if (backs.length <= 1) return;
        for (let i = 0; i < backs.length - 1; i++) {
          backs[i].parentNode && backs[i].parentNode.removeChild(backs[i]);
        }
      }
      function cleanupModalState(){
        cleanupBackdrops();
        const anyOpen = document.querySelectorAll('.modal.show').length > 0;
        if (!anyOpen) {
          document.body.classList.remove('modal-open');
          document.body.style.removeProperty('overflow');
          document.body.style.removeProperty('padding-right');
        }
      }
      modalEl.addEventListener('shown.bs.modal', cleanupBackdrops);
      modalEl.addEventListener('hidden.bs.modal', cleanupModalState);

      btn.addEventListener('click', async function(e){
        e.preventDefault();
        hideErr();
        await loadRoleOptions();
        modal.show();
      });

      saveBtn.addEventListener('click', async function(){
        hideErr();
        const selected = modalEl.querySelector('input[name=\"group_active_id\"]:checked');
      if (!selected) {
        showErr('<?= h(__('topbar_switch_role_err_select') ?? 'Sila pilih peranan.') ?>');
        return;
      }
      const groupID = parseInt(selected.value || '0', 10);
      if (!groupID) {
        showErr('<?= h(__('topbar_switch_role_err_invalid') ?? 'Sila pilih peranan yang sah.') ?>');
        return;
      }

        saveBtn.disabled = true;
        const originalHtml = saveBtn.innerHTML;
      saveBtn.innerHTML = '<i class=\"ri-loader-4-line ri-spin me-1\"></i> <?= h(__('topbar_switch_role_saving') ?? 'Menyimpan...') ?>';
        try {
          const r = await fetch('<?= h(base_url('ajax/role-switch.php')) ?>', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': csrf
            },
            body: JSON.stringify({ groupID })
          });
          const text = await r.text();
          let j = null;
          try { j = JSON.parse(text); } catch (e) {}
          if (!r.ok || !j || j.error) {
            throw new Error((j && j.message) || 'Gagal menukar peranan.');
          }
          modal.hide();
          window.location.reload();
        } catch (e) {
          showErr(e.message || 'Gagal menukar peranan.');
        } finally {
          saveBtn.disabled = false;
          saveBtn.innerHTML = originalHtml;
        }
      });
    }

    function showErr(msg){
      if (!errEl) return;
      errEl.textContent = msg || 'Ralat tidak diketahui.';
      errEl.classList.remove('d-none');
    }
    function hideErr(){
      if (!errEl) return;
      errEl.classList.add('d-none');
    }

    async function loadRoleOptions(){
      if (!listEl || !emptyEl) return;
      try {
        const r = await fetch('<?= h(base_url('ajax/role-switch-roles.php')) ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': csrf },
          body: JSON.stringify({ action: 'list' })
        });
        const text = await r.text();
        let j = null;
        try { j = JSON.parse(text); } catch (e) {}
        if (!r.ok || !j || j.error) {
          return;
        }

        const defaultId = parseInt(j.default?.id || 0, 10);
        const defaultName = String(j.default?.name || '').trim();
        const activeId = parseInt(j.active_id || 0, 10);
        const roles = Array.isArray(j.roles) ? j.roles : [];

        if (primaryNameEl && defaultName) {
          primaryNameEl.textContent = defaultName;
        }

        listEl.innerHTML = '';
        const primaryTag = '<?= h(__('topbar_switch_role_primary_tag') ?? 'Peranan Utama') ?>';
        function addRole(id, name, isPrimary, checked){
          const label = document.createElement('label');
          label.className = 'role-item';
          const input = document.createElement('input');
          input.type = 'radio';
          input.name = 'group_active_id';
          input.value = String(id);
          if (checked) input.checked = true;
          const span = document.createElement('span');
          span.className = 'role-label';
          span.textContent = name || '';
          if (isPrimary) {
            const tag = document.createElement('span');
            tag.className = 'text-muted';
            tag.textContent = ` (${primaryTag})`;
            span.appendChild(tag);
          }
          label.appendChild(input);
          label.appendChild(span);
          listEl.appendChild(label);
        }

        if (defaultId > 0) {
          addRole(defaultId, defaultName || '<?= h(__('topbar_switch_role_primary_label') ?? 'Peranan utama') ?>', true, activeId ? activeId === defaultId : true);
        }
        roles.forEach(r => {
          const rid = parseInt(r.id || r.f_groupID || 0, 10);
          const rname = String(r.name || r.f_groupName || '').trim();
          if (!rid || rid === defaultId) return;
          addRole(rid, rname, false, activeId === rid);
        });

        const hasAny = listEl.querySelectorAll('input[name="group_active_id"]').length > 0;
        if (!hasAny) {
          emptyEl.classList.remove('d-none');
          listEl.classList.add('d-none');
        } else {
          emptyEl.classList.add('d-none');
          listEl.classList.remove('d-none');
        }
        saveBtn.disabled = !hasAny;
      } catch (e) {
        // silent fallback to existing server-rendered list
      }
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initRoleSwitch);
    } else {
      initRoleSwitch();
    }
  })();
</script>

<?php if (!empty($roleSwitchFlash) && is_array($roleSwitchFlash)): 
  $roleName = trim((string)($roleSwitchFlash['group_name'] ?? ''));
?>
<script>
  (function(){
    const roleName = <?= json_encode($roleName, JSON_UNESCAPED_UNICODE) ?> || 'peranan';
    const titleTpl = <?= json_encode(__('topbar_switch_role_success_title') ?? 'Peranan {role}', JSON_UNESCAPED_UNICODE) ?>;
    const textTpl = <?= json_encode(__('topbar_switch_role_success_text') ?? 'Paparan dan akses sistem telah dikemas kini mengikut pilihan peranan baru iaitu <strong>{role}</strong>.', JSON_UNESCAPED_UNICODE) ?>;
    const title = String(titleTpl).replace('{role}', roleName);
    const html = String(textTpl).replace('{role}', roleName);
    function showMsg(){
      if (window.Swal) {
        Swal.fire({
          icon: 'success',
          title: title,
          html: html,
          confirmButtonText: 'OK',
          confirmButtonColor: '#198754'
        });
      } else {
        alert(title + '\n' + html.replace(/<[^>]+>/g, ''));
      }
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', showMsg);
    } else {
      showMsg();
    }
  })();
</script>
<?php endif; ?>

<!-- ========== TOGGLE BUTTON ========== -->
<script>
  (function(){
    const KEY = 'sidenav-size';                  // simpan state
    const html = document.documentElement;
    const body = document.body;
    const safeStorage = {
      get(k){ try { return localStorage.getItem(k); } catch(e){ return null; } },
      set(k,v){ try { localStorage.setItem(k,v); return true; } catch(e){ return false; } }
    };

    // Apply saved state on load (ikut layout asal)
    document.addEventListener('DOMContentLoaded', () => {
      const saved = safeStorage.get(KEY);
      if (saved === 'condensed' || saved === 'default') {
        html.setAttribute('data-sidenav-size', saved);
        body.setAttribute('data-sidebar-size', saved); // compat
      }
    });

    // Toggle via button template asal
    document.addEventListener('click', function(e){
      const btn = e.target.closest('.button-toggle-menu');
      if (!btn) return;
      e.preventDefault();
      e.stopImmediatePropagation();

      const curr = html.getAttribute('data-sidenav-size') || 'default';
      const next = (curr === 'condensed') ? 'default' : 'condensed';

      html.setAttribute('data-sidenav-size', next);
      body.setAttribute('data-sidebar-size', next); // compat lama
      safeStorage.set(KEY, next);

      // Optional: tutup dropdown yang terbuka supaya UI tak pelik
      document.querySelectorAll('#topbar .dropdown-menu.show').forEach(m => m.classList.remove('show'));

      // Optional: trigger resize untuk chart/table
      window.dispatchEvent(new Event('resize'));
    }, { capture: true });
  })();
</script>
