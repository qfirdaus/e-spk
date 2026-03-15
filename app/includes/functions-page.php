<?php

// Close session lock after reading
if (session_status() === PHP_SESSION_ACTIVE) session_write_close();

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tr')) {
    function tr(string $key, string $fallback): string {

        // cek dulu kalau function __() wujud
        if (!function_exists('__')) {
            return $fallback;
        }

        $t = __($key);
        return ($t === $key || $t === null || $t === '') 
            ? $fallback 
            : (string)$t;
    }
}

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