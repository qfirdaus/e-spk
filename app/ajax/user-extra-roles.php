<?php
// ajax/user-extra-roles.php
// Manage additional roles for a user (tbl_ref_access)
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

while (ob_get_level() > 0) {
    @ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../includes/init.php';
    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/AuditLogger.php';

    if (empty($_SESSION['f_stafID'])) {
        http_response_code(401);
        echo json_encode(['error' => true, 'message' => 'Sila log masuk terlebih dahulu.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $csrfHdr = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? '';
    $csrfSession = (string)($_SESSION['csrf_token'] ?? '');
    if ($csrfSession === '' || !hash_equals($csrfSession, (string)$csrfHdr)) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'CSRF token tidak sah.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Data tidak sah.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $action = (string)($data['action'] ?? 'get');
    $userID = (int)($data['userID'] ?? 0);
    if ($userID <= 0) {
        http_response_code(422);
        echo json_encode(['error' => true, 'message' => 'Parameter tidak lengkap.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = Database::getInstance('mysql')->getConnection();
    $currentUserModel = new User($pdo);
    $currentProfile = $currentUserModel->getProfile($_SESSION['f_stafID'] ?? '');
    $isSuperAdmin = $currentProfile && function_exists('is_user_super_admin') && is_user_super_admin($currentProfile, $pdo);
    if (!$isSuperAdmin) {
        http_response_code(403);
        echo json_encode(['error' => true, 'message' => 'Anda tidak mempunyai kebenaran untuk mengurus peranan tambahan.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Load target user data (stafID + primary role)
    $stmtU = $pdo->prepare("SELECT f_userID, f_stafID, f_groupID FROM tbl_m_user WHERE f_userID = :uid LIMIT 1");
    $stmtU->execute([':uid' => $userID]);
    $userRow = $stmtU->fetch(PDO::FETCH_ASSOC);
    if (!$userRow) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Pengguna tidak ditemui.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $targetStafID = trim((string)($userRow['f_stafID'] ?? ''));
    $primaryGroupId = (int)($userRow['f_groupID'] ?? 0);
    if ($targetStafID === '') {
        http_response_code(422);
        echo json_encode(['error' => true, 'message' => 'Maklumat staf tidak sah.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'get') {
        // All roles
        $roles = $pdo->query("SELECT f_groupID, f_groupName FROM tbl_m_group ORDER BY f_groupName ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        // Existing extra roles
        $stmtE = $pdo->prepare("SELECT f_groupID FROM tbl_ref_access WHERE TRIM(f_stafID) = :staf AND f_status = 1");
        $stmtE->execute([':staf' => $targetStafID]);
        $existing = array_map('intval', $stmtE->fetchAll(PDO::FETCH_COLUMN) ?: []);
        $existingMap = array_fill_keys($existing, true);

        $out = [];
        foreach ($roles as $r) {
            $rid = (int)($r['f_groupID'] ?? 0);
            if ($rid <= 0 || $rid === $primaryGroupId) continue;
            $out[] = [
                'id' => $rid,
                'name' => (string)($r['f_groupName'] ?? ''),
                'checked' => isset($existingMap[$rid])
            ];
        }

        echo json_encode(['error' => false, 'roles' => $out], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action !== 'save') {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Aksi tidak sah.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Save roles (sync)
    $selected = $data['roles'] ?? [];
    if (!is_array($selected)) $selected = [];
    $selectedIdsRaw = array_values(array_unique(array_filter(array_map('intval', $selected), fn($v) => $v > 0)));
    $attemptedPrimary = in_array($primaryGroupId, $selectedIdsRaw, true);
    $selectedIds = $selectedIdsRaw;
    // SECURITY CRITICAL – DO NOT MODIFY: primary role must never be added as extra role
    // Exclude primary role
    $selectedIds = array_values(array_diff($selectedIds, [$primaryGroupId]));

    // Valid roles lookup
    $allRoleIds = $pdo->query("SELECT f_groupID FROM tbl_m_group")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $allRoleIds = array_map('intval', $allRoleIds);
    $validMap = array_fill_keys($allRoleIds, true);
    $selectedIds = array_values(array_filter($selectedIds, fn($id) => isset($validMap[$id])));

    $stmtE = $pdo->prepare("SELECT f_groupID FROM tbl_ref_access WHERE TRIM(f_stafID) = :staf AND f_status = 1");
    $stmtE->execute([':staf' => $targetStafID]);
    $existingIds = array_map('intval', $stmtE->fetchAll(PDO::FETCH_COLUMN) ?: []);
    // SECURITY CRITICAL – DO NOT MODIFY: never remove primary role even if present
    $existingIds = array_values(array_diff($existingIds, [$primaryGroupId]));

    $toAdd = array_values(array_diff($selectedIds, $existingIds));
    $toRemove = array_values(array_diff($existingIds, $selectedIds));

    $pdo->beginTransaction();
    try {
        $logger = new AuditLogger($pdo);

        // Adds
        if (!empty($toAdd)) {
            $ins = $pdo->prepare("
                INSERT INTO tbl_ref_access (f_stafID, f_groupID, f_status, f_createdby, f_createddt)
                VALUES (:staf, :gid, 1, :by, NOW())
            ");
            foreach ($toAdd as $gid) {
                $ins->execute([
                    ':staf' => $targetStafID,
                    ':gid' => $gid,
                    ':by' => ($_SESSION['f_stafID'] ?? null)
                ]);

                // GOVERNANCE CRITICAL – DO NOT MODIFY: audit logging must remain in-transaction
                $logger->logEvent([
                    'event_type'  => 'UPDATE',
                    'severity'    => 'INFO',
                    'outcome'     => 'SUCCESS',
                    'target_type' => 'role_assignment',
                    'target_id'   => (string)$userID,
                    'target_label'=> 'ADD_ROLE',
                    'message'     => 'Role added',
                    'session_id'  => session_id() ?: null,
                    'user_id'     => !empty($_SESSION['f_nopekerja']) && is_numeric($_SESSION['f_nopekerja']) ? (int)$_SESSION['f_nopekerja'] : null,
                    'actor_label' => ($_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null),
                    'meta'        => [
                        'action' => 'ADD_ROLE',
                        'role_id' => $gid,
                        'performed_by' => ($_SESSION['f_stafID'] ?? null),
                        'target_stafID' => $targetStafID
                    ]
                ]);
            }
        }

        // Removes
        if (!empty($toRemove)) {
            $placeholders = implode(',', array_fill(0, count($toRemove), '?'));
            $params = array_merge([$targetStafID], $toRemove);
            $del = $pdo->prepare("DELETE FROM tbl_ref_access WHERE TRIM(f_stafID) = ? AND f_groupID IN ($placeholders)");
            $del->execute($params);

            // GOVERNANCE CRITICAL – DO NOT MODIFY: audit logging must remain in-transaction
            foreach ($toRemove as $gid) {
                $logger->logEvent([
                    'event_type'  => 'UPDATE',
                    'severity'    => 'INFO',
                    'outcome'     => 'SUCCESS',
                    'target_type' => 'role_assignment',
                    'target_id'   => (string)$userID,
                    'target_label'=> 'REMOVE_ROLE',
                    'message'     => 'Role removed',
                    'session_id'  => session_id() ?: null,
                    'user_id'     => !empty($_SESSION['f_nopekerja']) && is_numeric($_SESSION['f_nopekerja']) ? (int)$_SESSION['f_nopekerja'] : null,
                    'actor_label' => ($_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null),
                    'meta'        => [
                        'action' => 'REMOVE_ROLE',
                        'role_id' => $gid,
                        'performed_by' => ($_SESSION['f_stafID'] ?? null),
                        'target_stafID' => $targetStafID
                    ]
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    // GOVERNANCE CRITICAL – DO NOT MODIFY: blocked attempts must be auditable (non-blocking)
    // Audit blocked attempt (primary role) without affecting valid changes
    if ($attemptedPrimary) {
        try {
            $logger = new AuditLogger($pdo);
            $logger->logEvent([
                'event_type'  => 'UPDATE',
                'severity'    => 'WARN',
                'outcome'     => 'DENIED',
                'target_type' => 'role_assignment',
                'target_id'   => (string)$userID,
                'target_label'=> 'BLOCKED_PRIMARY_ROLE',
                'message'     => 'Blocked attempt to add primary role as additional role',
                'session_id'  => session_id() ?: null,
                'user_id'     => !empty($_SESSION['f_nopekerja']) && is_numeric($_SESSION['f_nopekerja']) ? (int)$_SESSION['f_nopekerja'] : null,
                'actor_label' => ($_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null),
                'meta'        => [
                    'action' => 'BLOCKED_PRIMARY_ROLE',
                    'role_id' => $primaryGroupId,
                    'performed_by' => ($_SESSION['f_stafID'] ?? null),
                    'target_stafID' => $targetStafID
                ]
            ]);
        } catch (Throwable $e) {
            error_log('[user-extra-roles] Blocked primary role audit failed: ' . $e->getMessage());
        }
    }

    echo json_encode([
        'error' => false,
        'message' => 'Peranan tambahan berjaya dikemas kini.',
        'added' => $toAdd,
        'removed' => $toRemove
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[user-extra-roles] Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Ralat sistem semasa mengemas kini peranan.'], JSON_UNESCAPED_UNICODE);
}
