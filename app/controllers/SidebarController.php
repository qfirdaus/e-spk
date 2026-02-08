<?php
/**
 * SidebarController
 * 
 * Handles business logic for sidebar navigation component.
 * Manages user profile, modules, menus, and active page detection.
 * 
 * @package e-prestasi
 * @author UPNM, Seksyen Aplikasi Digital, BTMK
 */

declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Modul.php';
require_once __DIR__ . '/../classes/SystemConfigConstants.php';

/**
 * SidebarController class
 * 
 * Provides methods to retrieve sidebar data including:
 * - User profile information
 * - Accessible modules and menus
 * - Active module detection
 * - Caching support
 */
class SidebarController
{
    private PDO $pdoMysql;
    private User $userModel;
    private Modul $modulModel;
    private string $lang;
    private ?string $groupKod;
    private ?int $groupId;
    private array $profile;
    private array $senaraiModul;
    private array $modulMenus;
    private ?int $modulAktifID;

    /**
     * Constructor
     * 
     * @param PDO|null $pdoMysql Optional PDO connection (uses singleton if not provided)
     */
    public function __construct(?PDO $pdoMysql = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdoMysql = $pdoMysql ?: Database::getInstance()->getConnection();
        $this->userModel = new User($this->pdoMysql);
        $this->modulModel = new Modul($this->pdoMysql);
        $this->lang = $_SESSION['lang'] ?? SystemConfigConstants::DEFAULT_LANGUAGE;
        $this->groupKod = null;
        $this->groupId = null;
        $this->profile = [];
        $this->senaraiModul = [];
        $this->modulMenus = [];
        $this->modulAktifID = null;
    }

    /**
     * Get user profile data
     * 
     * @return array User profile array
     */
    public function getProfile(): array
    {
        return $this->profile;
    }

    /**
     * Get list of accessible modules
     * 
     * @return array Array of modules
     */
    public function getSenaraiModul(): array
    {
        return $this->senaraiModul;
    }

    /**
     * Get menus grouped by modulID
     * 
     * @return array Associative array: [modulID => [menus...]]
     */
    public function getModulMenus(): array
    {
        return $this->modulMenus;
    }

    /**
     * Get active modul ID
     * 
     * @return int|null Active modul ID or null if none
     */
    public function getModulAktifID(): ?int
    {
        return $this->modulAktifID;
    }

    /**
     * Get language code
     * 
     * @return string Language code (ms, en, ta, zh)
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * Get group code
     * 
     * @return string|null Group code or null
     */
    public function getGroupKod(): ?string
    {
        return $this->groupKod;
    }

    /**
     * Get group ID
     *
     * @return int|null Group ID or null
     */
    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    /**
     * Load all sidebar data
     * 
     * This method loads user profile, modules, menus, and detects active module.
     * Uses caching to improve performance.
     * 
     * @param string $currentFile Current page filename (e.g., 'dashboard.php')
     * @return void
     */
    public function loadSidebarData(string $currentFile): void
    {
        try {
            // Load user profile
            $this->loadUserProfile();
            
            // Load group access
            $akses = $this->loadGroupAccess();
            
            // Extract access arrays
            $modulAccess = array_map('intval', explode(',', $akses['f_modulAccess'] ?? ''));
            $menuAccess = array_map('intval', explode(',', $akses['f_menuAccess'] ?? ''));
            
            // Load modules and menus (with caching)
            $this->loadModulesAndMenus($modulAccess, $menuAccess);
            
            // Detect active module
            $this->detectActiveModul($currentFile);
            
        } catch (Throwable $e) {
            error_log("SidebarController: Error loading sidebar data: " . $e->getMessage());
            // Continue with empty state
        }
    }

    /**
     * Load user profile from database
     * 
     * @return void
     */
    private function loadUserProfile(): void
    {
        $f_stafID = $_SESSION['f_stafID'] ?? null;
        
        if (!$f_stafID) {
            $this->profile = [];
            return;
        }

        try {
            $this->profile = $this->userModel->getProfile($f_stafID);
            if (!is_array($this->profile)) {
                $this->profile = [];
            } else {
                $defaultGroupId = isset($this->profile['f_groupID']) ? (int)$this->profile['f_groupID'] : null;
                $activeGroupId = isset($_SESSION['group_active_id']) ? (int)$_SESSION['group_active_id'] : 0;
                $this->groupId = ($activeGroupId > 0) ? $activeGroupId : $defaultGroupId;
                $this->groupKod = $this->profile['f_groupKod'] ?? null; // display/logging only
            }
        } catch (Throwable $e) {
            error_log("SidebarController: Failed to get user profile for f_stafID={$f_stafID}: " . $e->getMessage());
            $this->profile = [];
        }
    }

    /**
     * Load group access permissions
     * 
     * @return array Access array with f_modulAccess and f_menuAccess
     */
    private function loadGroupAccess(): array
    {
        if (!$this->groupId || $this->groupId <= 0) {
            return [];
        }

        try {
            $stmt = $this->pdoMysql->prepare(
                "SELECT f_modulAccess, f_menuAccess 
                 FROM tbl_m_group 
                 WHERE f_groupID = :groupId 
                 LIMIT 1"
            );
            $stmt->execute(['groupId' => $this->groupId]);
            $akses = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            return is_array($akses) ? $akses : [];
        } catch (PDOException $e) {
            error_log("SidebarController: Failed to get group access for groupId={$this->groupId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Load modules and menus with caching
     * 
     * @param array $modulAccess Array of allowed modul IDs
     * @param array $menuAccess Array of allowed menu IDs
     * @return void
     */
    private function loadModulesAndMenus(array $modulAccess, array $menuAccess): void
    {
        // Check cache
        $cacheKey = $this->getCacheKey();
        $cachedData = $this->getCache($cacheKey);
        
        if ($cachedData !== null && is_array($cachedData)) {
            $this->senaraiModul = $cachedData['moduls'] ?? [];
            $this->modulMenus = $cachedData['menus'] ?? [];
            return;
        }

        // Load from database
        try {
            $this->senaraiModul = $this->modulModel->getAllModulByGroup($modulAccess, $this->lang);
            
            // Batch load all menus (fix N+1 query)
            if (!empty($this->senaraiModul)) {
                $modulIDs = array_column($this->senaraiModul, 'f_modulID');
                $this->modulMenus = $this->modulModel->getAllMenusByModulIDs($modulIDs, $menuAccess, $this->lang);
            } else {
                $this->modulMenus = [];
            }
            
            // Cache the results
            $this->setCache($cacheKey, [
                'moduls' => $this->senaraiModul,
                'menus' => $this->modulMenus
            ]);
            
        } catch (Throwable $e) {
            error_log("SidebarController: Failed to load modules/menus: " . $e->getMessage());
            $this->senaraiModul = [];
            $this->modulMenus = [];
        }
    }

    /**
     * Detect which module is currently active based on current file
     * 
     * @param string $currentFile Current page filename
     * @return void
     */
    private function detectActiveModul(string $currentFile): void
    {
        foreach ($this->senaraiModul as $modul) {
            $modulID = (int)$modul['f_modulID'];
            $childs = $this->modulMenus[$modulID] ?? [];
            
            foreach ($childs as $menu) {
                $menuPath = $this->sanitizeMenuPath($menu['f_path'] ?? '');
                if ($menuPath && $currentFile === basename($menuPath)) {
                    $this->modulAktifID = $modulID;
                    return; // Found, exit early
                }
            }
        }
    }

    /**
     * Get cache key for sidebar data
     * 
     * @return string Cache key
     */
    private function getCacheKey(): string
    {
        return 'sidebar:v1:' . md5((string)($this->groupId ?? 'guest') . '_' . $this->lang);
    }

    /**
     * Get cached sidebar data
     * 
     * @param string $key Cache key
     * @return mixed|null Cached data or null
     */
    private function getCache(string $key)
    {
        // Try APCu first
        if (function_exists('apcu_fetch')) {
            try {
                $ok = false;
                $v = apcu_fetch($key, $ok);
                if ($ok) return $v;
            } catch (Throwable $e) {
                error_log("SidebarController: Cache fetch error: " . $e->getMessage());
            }
        }
        
        // Fallback to session cache
        if (isset($_SESSION['sidebar_cache'][$key])) {
            $cached = $_SESSION['sidebar_cache'][$key];
            if (is_array($cached) && isset($cached['ts'], $cached['data'])) {
                $ttl = SystemConfigConstants::CACHE_TTL_SIDEBAR;
                if ((time() - $cached['ts']) < $ttl) {
                    return $cached['data'];
                }
                unset($_SESSION['sidebar_cache'][$key]);
            }
        }
        
        return null;
    }

    /**
     * Set cache for sidebar data
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @return void
     */
    private function setCache(string $key, mixed $data): void
    {
        $ttl = SystemConfigConstants::CACHE_TTL_SIDEBAR;
        
        // Try APCu first
        if (function_exists('apcu_store')) {
            try {
                apcu_store($key, $data, $ttl);
            } catch (Throwable $e) {
                error_log("SidebarController: Cache store error: " . $e->getMessage());
            }
        }
        
        // Fallback to session cache
        if (!isset($_SESSION['sidebar_cache'])) {
            $_SESSION['sidebar_cache'] = [];
        }
        $_SESSION['sidebar_cache'][$key] = [
            'ts' => time(),
            'data' => $data
        ];
    }

    /**
     * Sanitize menu path to prevent path traversal
     * 
     * @param string $path Menu path from database
     * @return string|null Sanitized path or null if invalid
     */
    private function sanitizeMenuPath(string $path): ?string
    {
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
     * Get notification count for dashboard badge
     * 
     * Currently returns null (no notification system implemented).
     * Can be extended to query actual notification count.
     * 
     * @return int|null Notification count or null if not available
     */
    public function getNotificationCount(): ?int
    {
        // TODO: Implement actual notification count query
        // Example:
        // $stmt = $this->pdoMysql->prepare("SELECT COUNT(*) FROM tbl_notifications WHERE f_userID = :userID AND f_read = 0");
        // $stmt->execute(['userID' => $_SESSION['f_userID'] ?? null]);
        // return (int)$stmt->fetchColumn();
        
        return null; // No notifications for now
    }
}







