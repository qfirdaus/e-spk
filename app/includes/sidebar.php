<?php
/**
 * Sidebar Navigation Component
 * 
 * Displays user profile, modules, and menu items based on user access.
 * Uses SidebarController for business logic and implements security validations.
 * 
 * @package e-prestasi
 * @author UPNM, Seksyen Aplikasi Digital, BTMK
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../controllers/SidebarController.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/SystemConfigConstants.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../setting/function.php'; // untuk base_path(), base_url()

/**
 * Validate icon class against whitelist
 * 
 * @param string $icon Icon class name
 * @return string Validated icon class (default: 'ri-folder-fill')
 */
function validate_sidebar_icon(string $icon): string {
    $allowed = SystemConfigConstants::ALLOWED_SIDEBAR_ICONS;
    return in_array($icon, $allowed, true) ? $icon : 'ri-folder-fill';
}

/**
 * Sanitize menu path to prevent path traversal
 * 
 * @param string $path Menu path from database
 * @return string|null Sanitized path or null if invalid
 */
function sanitize_menu_path(string $path): ?string {
    // Remove any path traversal attempts
    if (str_contains($path, '..') || str_contains($path, '//')) {
        return null;
    }
    
    // Remove leading/trailing slashes and whitespace
    $path = trim($path);
    $path = ltrim($path, '/');
    
    // Only allow alphanumeric, dash, underscore, dot, and forward slash
    if (!preg_match('/^[a-zA-Z0-9_\-.\/]+$/', $path)) {
        return null;
    }
    
    // Limit path length
    if (strlen($path) > 255) {
        return null;
    }
    
    return $path;
}

/**
 * Detect if a menu item is active based on current file
 * 
 * @param string $currentFile Current page filename (e.g., 'dashboard.php')
 * @param string $menuPath Menu path from database
 * @return bool True if menu is active, false otherwise
 */
function is_menu_active(string $currentFile, string $menuPath): bool {
    $sanitizedPath = sanitize_menu_path($menuPath);
    if (!$sanitizedPath) {
        return false;
    }
    return $currentFile === basename($sanitizedPath);
}

/**
 * Check if profile data is empty or invalid
 * 
 * @param array $profile Profile data array
 * @return bool True if profile is empty/invalid, false otherwise
 */
function is_profile_empty(array $profile): bool {
    return empty($profile) || 
           (empty($profile['f_stafID']) && empty($profile['f_nopekerja']) && empty($profile['f_nama']));
}

function student_avatar_url_sidebar(?string $matrik): string {
    $clean = preg_replace('/\D+/', '', (string)$matrik) ?? '';
    if ($clean === '') return base_url('assets/images/no-image.jpg');
    return 'https://kemasukan.upnm.edu.my/tawaran/pelajar/student_image/' . rawurlencode($clean) . '.jpg';
}

// Initialize controller and load sidebar data
$currentFile = basename($_SERVER['PHP_SELF'] ?? '');
$sidebarController = new SidebarController();
$sidebarController->loadSidebarData($currentFile);

// Get data from controller
$profile = $sidebarController->getProfile();
$senaraiModul = $sidebarController->getSenaraiModul();
$modulMenus = $sidebarController->getModulMenus();
$modulAktifID = $sidebarController->getModulAktifID();
$lang = $sidebarController->getLang();

// Student fallback profile (pra-SSO): SidebarController guna MySQL user profile.
// Jika student tiada dalam tbl_m_user, bina profil minimum dari session.
$authType = (string)($_SESSION['auth_type'] ?? '');
if ($authType === 'student') {
    $studentProfile = is_array($_SESSION['student_profile'] ?? null) ? $_SESSION['student_profile'] : [];
    $sessionUser = is_array($_SESSION['user'] ?? null) ? $_SESSION['user'] : [];
    if (empty($profile)) {
        $profile = [
            'f_stafID'    => (string)($_SESSION['f_stafID'] ?? ($studentProfile['matrik'] ?? '')),
            'f_nopekerja' => (string)($_SESSION['f_nopekerja'] ?? ($studentProfile['matrik'] ?? '')),
            'f_nama'      => (string)($sessionUser['f_nama'] ?? ($studentProfile['nama'] ?? ($_SESSION['user_name'] ?? 'Pengguna'))),
            'f_nickname'  => (string)($sessionUser['f_nickname'] ?? ($studentProfile['nama'] ?? '')),
            'f_groupName' => (string)($sessionUser['f_groupName'] ?? 'Student'),
            'f_groupID'   => (int)($sessionUser['f_groupID'] ?? 0),
        ];
    }
}

// Extract profile data with fallbacks
$isProfileEmpty = is_profile_empty($profile);
$namaPendek = 'Pengguna';
$avatarUrl = base_url('assets/images/no-image.jpg');
$perananLabel = 'Pengguna';
$profileMessage = null;

// Active role (session-based)
$activeGroupId = (int)($_SESSION['group_active_id'] ?? 0);

if (!$isProfileEmpty) {
    // Extract nickname or first name
    $namaPendek = $profile['f_nickname'] ?? '';
    if (empty($namaPendek) && !empty($profile['f_nama'])) {
        $namaPendek = explode(' ', $profile['f_nama'])[0];
    }
    if (empty($namaPendek)) {
        $namaPendek = 'Pengguna';
    }
    
    // Get avatar URL
    try {
        if ($authType === 'student') {
            $avatarUrl = (string)($studentProfile['avatar_url']
                ?? ($sessionUser['avatar_url']
                ?? ($_SESSION['avatar_url']
                ?? student_avatar_url_sidebar((string)($studentProfile['matrik'] ?? ($_SESSION['f_stafID'] ?? ''))))));
        } else {
            $userModel = new User(Database::getInstance()->getConnection());
            $avatarUrl = $userModel->getAvatarUrl($profile['f_nopekerja'] ?? null);
            if (empty($avatarUrl)) {
                $avatarUrl = base_url('assets/images/no-image.jpg');
            }
        }
    } catch (Throwable $e) {
        error_log("Sidebar: Failed to get avatar URL: " . $e->getMessage());
        $avatarUrl = base_url('assets/images/no-image.jpg');
    }
    
    $perananLabel = $profile['f_groupName'] ?? 'Pengguna';
    if ($activeGroupId > 0) {
        try {
            $stmtAct = Database::getInstance()->getConnection()->prepare("SELECT f_groupName FROM tbl_m_group WHERE f_groupID = :gid LIMIT 1");
            $stmtAct->execute([':gid' => $activeGroupId]);
            $rowAct = $stmtAct->fetch(PDO::FETCH_ASSOC);
            if (!empty($rowAct['f_groupName'])) {
                $perananLabel = (string)$rowAct['f_groupName'];
            }
        } catch (Throwable $e) {
            // keep default label
        }
    }
} else {
    // Profile is empty - set fallback message
    $profileMessage = __('sidebar_profile_empty') ?: 'Profil tidak ditemui';
}

// Get theme settings
$sidebarColor = $_SESSION['theme.sidebar'] ?? SystemConfigConstants::DEFAULT_THEME_SIDEBAR;

// Get notification count (optional - hide badge if null)
$notificationCount = $sidebarController->getNotificationCount();
?>

<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu" id="leftside-menu" data-menu-color="<?= $sidebarColor ?>" data-sidebar-loaded="true">
<style>
/* Sidebar Loading State */
#leftside-menu[data-sidebar-loaded="false"] .sidebar-loading-overlay {
    display: flex;
}
#leftside-menu[data-sidebar-loaded="true"] .sidebar-loading-overlay {
    display: none;
}
.sidebar-loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(2px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10;
    flex-direction: column;
    gap: 12px;
}
html[data-bs-theme="dark"] .sidebar-loading-overlay {
    background: rgba(0, 0, 0, 0.75);
}
.sidebar-loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid rgba(0, 0, 0, 0.1);
    border-top-color: #0d6efd;
    border-radius: 50%;
    animation: sidebar-spin 0.8s linear infinite;
}
html[data-bs-theme="dark"] .sidebar-loading-spinner {
    border-color: rgba(255, 255, 255, 0.1);
    border-top-color: #0d6efd;
}
.sidebar-loading-text {
    font-size: 0.875rem;
    color: #6c757d;
    opacity: 0.8;
}
html[data-bs-theme="dark"] .sidebar-loading-text {
    color: #adb5bd;
}
@keyframes sidebar-spin {
    to { transform: rotate(360deg); }
}
/* Sidebar Chart Icon (Dashboard) */
.side-nav .sidebar-chart-icon {
    position: absolute;
    right: calc(var(--ct-menu-item-padding-x) * 1.5);
    top: 50%;
    transform: translateY(-50%);
    font-size: calc(var(--ct-menu-item-font-size) * 1.1);
    color: var(--ct-menu-item-color);
    opacity: 0.6;
    transition: opacity 0.2s ease;
}
.side-nav .sidebar-chart-icon:hover {
    opacity: 1;
}
/* Sidebar Logout Icon */
.side-nav .sidebar-logout-icon {
    position: absolute;
    right: calc(var(--ct-menu-item-padding-x) * 1.5);
    top: 50%;
    transform: translateY(-50%);
    font-size: calc(var(--ct-menu-item-font-size) * 1.1);
    color: var(--bs-danger);
    opacity: 0.7;
    transition: opacity 0.2s ease;
}
.side-nav .sidebar-logout-icon:hover {
    opacity: 1;
}
</style>
<div class="sidebar-loading-overlay">
    <div class="sidebar-loading-spinner"></div>
    <div class="sidebar-loading-text"><?= __('sidebar_loading') ?: 'Memuatkan...' ?></div>
</div>
<script>
// Hide sidebar loading overlay once sidebar is loaded
(function() {
    const sidebar = document.getElementById('leftside-menu');
    if (sidebar && sidebar.getAttribute('data-sidebar-loaded') === 'true') {
        // Sidebar already loaded, hide overlay immediately
        const overlay = sidebar.querySelector('.sidebar-loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
})();
</script>

    <!-- ✅ Logo Sidebar -->
    <a href="<?= base_path('pages/dashboard.php') ?>" class="logo logo-dark">
        <span class="logo-lg"><img src="<?= base_url('assets/images/new-logo.png') ?>" alt="logo" style="width: 200px; height: auto;"></span>
        <span class="logo-sm"><img src="<?= base_url('assets/images/new-logo.png') ?>" alt="small logo" style="height: 30px; width: auto;"></span>
    </a>

    <a href="<?= base_path('pages/dashboard.php') ?>" class="logo logo-light">
        <span class="logo-lg"><img src="<?= base_url('assets/images/new-logo.png') ?>" alt="logo" style="width: 200px; height: auto;"></span>
        <span class="logo-sm"><img src="<?= base_url('assets/images/new-logo.png') ?>" alt="small logo" style="height: 30px; width: auto;"></span>
    </a>

    <div class="h-100" id="leftside-menu-container" data-simplebar>

        <!-- ✅ Paparan Pengguna -->
        <div class="leftbar-user p-3 text-white">
            <?php if ($isProfileEmpty): ?>
                <!-- Fallback: Profile tidak ditemui -->
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle shadow d-flex align-items-center justify-content-center bg-white bg-opacity-10" style="width: 42px; height: 42px;">
                            <i class="ri-user-line fs-18"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <span class="fw-semibold fs-15 d-block"><?= htmlspecialchars($namaPendek) ?></span>
                        <span class="fs-12 text-white-50"><?= htmlspecialchars($profileMessage) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= base_path('pages/profile.php') ?>" class="d-flex align-items-center text-reset">
                    <div class="flex-shrink-0">
                        <img src="<?= $avatarUrl ?>" onerror="this.onerror=null;this.src='<?= base_url('assets/images/no-image.jpg') ?>';" alt="user-image" height="42" class="rounded-circle shadow">
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <span class="fw-semibold fs-15 d-block"><?= htmlspecialchars($namaPendek) ?></span>
                        <span class="fs-13"><?= htmlspecialchars($perananLabel) ?></span>
                    </div>
                    <div class="ms-auto">
                        <i class="ri-arrow-right-s-fill fs-20"></i>
                    </div>
                </a>
            <?php endif; ?>
        </div>

        <!-- ✅ Menu Sidebar -->
        <ul class="side-nav" style="padding-bottom: 70px;">

            <!-- Dashboard -->
            <li class="side-nav-title mt-1"><?= __('sidebar_main') ?></li>
            <li class="side-nav-item">
                <a href="<?= base_path('pages/dashboard.php') ?>" class="side-nav-link">
                    <i class="ri-dashboard-fill"></i>
                    <span><?= __('sidebar_dashboard') ?></span>
                    <i class="ri-bar-chart-line sidebar-chart-icon" title="<?= __('sidebar_dashboard_stats') ?: 'Statistik' ?>"></i>
                </a>
            </li>
            
            <!-- Modul Sistem -->
            <li class="side-nav-title mt-2"><?= __('sidebar_modul') ?></li>
            <?php foreach ($senaraiModul as $modul): 
                $modulID = (int)$modul['f_modulID'];
                $modulId = 'sidebarModul' . $modulID;
                
                // ✅ VALIDATE ICON CLASS
                $icon = validate_sidebar_icon($modul['f_icon'] ?? 'ri-folder-fill');
                
                $nama = htmlspecialchars($modul['modulName'] ?? '', ENT_QUOTES, 'UTF-8');
                
                // ✅ USE BATCH LOADED MENUS (no N+1 query)
                $childs = $modulMenus[$modulID] ?? [];
                if (empty($childs)) continue;

                $isActive     = ($modulID === $modulAktifID);
                $collapseCls  = $isActive ? 'collapse show' : 'collapse';
                $linkCls      = 'side-nav-link' . ($isActive ? '' : ' collapsed');
                $ariaExpanded = $isActive ? 'true' : 'false';
            ?>
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse"
                       href="#<?= $modulId ?>"
                       class="<?= $linkCls ?>"
                       aria-expanded="<?= $ariaExpanded ?>"
                       aria-controls="<?= $modulId ?>">
                        <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
                        <span><?= $nama ?></span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="<?= $collapseCls ?>" id="<?= $modulId ?>">
                        <ul class="side-nav-second-level">
                            <?php foreach ($childs as $menu): 
                                // ✅ SANITIZE MENU PATH
                                $menuPath = sanitize_menu_path($menu['f_path'] ?? '');
                                if (!$menuPath) continue; // Skip invalid paths
                                
                                // ✅ USE HELPER FUNCTION FOR ACTIVE DETECTION
                                $menuActive = is_menu_active($currentFile, $menuPath);
                                $menuHref = base_path('pages/' . $menuPath);
                                $menuName = htmlspecialchars($menu['menuName'] ?? '-', ENT_QUOTES, 'UTF-8');
                            ?>
                                <li>
                                    <a class="<?= $menuActive ? 'active' : '' ?>"
                                       href="<?= htmlspecialchars($menuHref, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $menuName ?>
                                    </a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </li>
            <?php endforeach ?>

            <!-- Kawalan Sistem -->
            <li class="side-nav-title mt-2"><?= __('sidebar_kawalan') ?></li>
            <li class="side-nav-item">
                <a href="javascript:void(0);" onclick="confirmLogout();" class="side-nav-link text-danger">
                    <i class="ri-logout-box-r-fill"></i>
                    <span><?= __('sidebar_keluar') ?></span>
                    <i class="ri-logout-box-r-line sidebar-logout-icon" title="<?= __('sidebar_keluar') ?>"></i>
                </a>
            </li>

        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
