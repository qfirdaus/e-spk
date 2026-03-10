<?php
// ajax/clear-dashcache.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_login();
require_once __DIR__ . '/../setting/constants/prestasi_constants.php';

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../controllers/DashboardController.php';

    // Only admin roles allowed to clear this cache
    $dash = new DashboardController();
    $groupId = (int)($_SESSION['f_groupID'] ?? ($dash->profile['f_groupID'] ?? 0));
    if (!in_array($groupId, [PRESTASI_ROLE_ID_ADM_SA, PRESTASI_ROLE_ID_ADM_KE], true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'msg' => 'Akses tidak dibenarkan']);
        exit;
    }

    // Clear specific keys in current session
    \DashCache::clear('staf_jppsm_all');
    \DashCache::clear('jppsm_by_'); // clears all jppsm_by_* keys

    // Also optionally clear any user-specific keys that might affect view
    \DashCache::clear('user_jab:');

    echo json_encode(['ok' => true, 'msg' => 'Dash cache cleared (current session)']);
    exit;
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[ajax/clear-dashcache.php] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Ralat semasa membersihkan cache']);
    exit;
}
