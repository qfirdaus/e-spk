<?php
// ajax/user-delete.php
// Delete user from tbl_m_user
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Clean output buffers
while (ob_get_level() > 0) {
    @ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

/**
 * Simple rate limiting (per session)
 */
function checkRateLimit(string $key, int $maxRequests = 20, int $windowSeconds = 60): bool {
    $now = time();
    $rateKey = 'rate_limit_' . $key;
    
    if (!isset($_SESSION[$rateKey])) {
        $_SESSION[$rateKey] = ['count' => 0, 'reset' => $now + $windowSeconds];
    }
    
    $rate = &$_SESSION[$rateKey];
    
    // Reset if window expired
    if ($now >= $rate['reset']) {
        $rate = ['count' => 0, 'reset' => $now + $windowSeconds];
    }
    
    // Check limit
    if ($rate['count'] >= $maxRequests) {
        return false;
    }
    
    $rate['count']++;
    return true;
}

try {
    require_once __DIR__ . '/../includes/init.php';
    
    // Check login
    if (empty($_SESSION['f_stafID'])) {
        http_response_code(401);
        echo json_encode([
            'error' => true,
            'message' => 'Sila log masuk terlebih dahulu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Rate limiting: max 20 requests per 60 seconds
    if (!checkRateLimit('user_delete', 20, 60)) {
        http_response_code(429);
        echo json_encode([
            'error' => true,
            'message' => 'Terlalu banyak permintaan. Sila cuba lagi selepas beberapa saat.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // SECURITY CRITICAL – DO NOT MODIFY: backend permission guard
    // Check permission - hanya Super Admin boleh delete
    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../setting/constants/prestasi_constants.php';
    
    $pdo = Database::getInstance('mysql')->getConnection();
    $userModel = new User($pdo);
    
    $currentStafID = $_SESSION['f_stafID'] ?? '';
    $currentProfile = $userModel->getProfile($currentStafID);
    
    if (!$currentProfile || (int)($currentProfile['f_groupID'] ?? 0) !== PRESTASI_ROLE_ID_ADM_SA) {
        http_response_code(403);
        echo json_encode([
            'error' => true,
            'message' => 'Anda tidak mempunyai kebenaran untuk memadam pengguna. Hanya Super Admin dibenarkan.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check CSRF
    $csrfSession = (string)($_SESSION['csrf_token'] ?? '');
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => 'Data tidak sah.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $csrfPosted = (string)($data['csrf_token'] ?? $_POST['csrf_token'] ?? '');
    
    if ($csrfSession === '' || !hash_equals($csrfSession, $csrfPosted)) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => 'CSRF token tidak sah.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get input
    $userID = isset($data['userID']) ? (int)$data['userID'] : 0;

    // Validation
    if ($userID <= 0) {
        echo json_encode([
            'error' => true,
            'message' => 'ID pengguna tidak sah.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check if user exists
    $checkSql = "SELECT f_userID, f_stafID, f_nama FROM tbl_m_user WHERE f_userID = :userID LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':userID' => $userID]);
    $userData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        echo json_encode([
            'error' => true,
            'message' => 'Pengguna tidak dijumpai dalam sistem.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get logged in user for audit
    $loggedInStafID = $_SESSION['f_stafID'] ?? null;
    $remarks = 'Deleted via user management';

    // Hard delete: DELETE FROM tbl_m_user
    $deleteSql = "DELETE FROM tbl_m_user WHERE f_userID = :userID";

    $deleteStmt = $pdo->prepare($deleteSql);
    $result = $deleteStmt->execute([
        ':userID' => $userID
    ]);

    if (!$result) {
        throw new Exception('Gagal memadam data pengguna.');
    }

    // Audit: Log user deletion
    try {
        error_log('[user-delete] Starting audit logging...');
        
        // Check if audit_event function exists
        if (!function_exists('audit_event')) {
            error_log('[user-delete] ERROR: audit_event() function not found - check if audit_helper.php is loaded');
            // Try to manually load it
            $auditHelperPath = __DIR__ . '/../setting/helper/audit_helper.php';
            if (file_exists($auditHelperPath)) {
                require_once $auditHelperPath;
                error_log('[user-delete] Manually loaded audit_helper.php');
            }
        }
        
        if (function_exists('audit_event')) {
            $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;
            $sessionId = session_id() ?: null;
            
            // Get user_id from f_nopekerja (no staf) - convert to integer if numeric
            $userId = null;
            $stafID = $_SESSION['f_stafID'] ?? null;
            
            // Method 1: Direct from session (fastest)
            if (!empty($_SESSION['f_nopekerja'])) {
                $nopek = $_SESSION['f_nopekerja'];
                $userId = is_numeric($nopek) ? (int)$nopek : null;
                error_log("[user-delete] Found user_id from \$_SESSION['f_nopekerja']: {$userId}");
            } elseif (!empty($_SESSION['user']['f_nopekerja'])) {
                $nopek = $_SESSION['user']['f_nopekerja'];
                $userId = is_numeric($nopek) ? (int)$nopek : null;
                error_log("[user-delete] Found user_id from \$_SESSION['user']['f_nopekerja']: {$userId}");
            } elseif (!empty($currentProfile['f_nopekerja'])) {
                $nopek = $currentProfile['f_nopekerja'];
                $userId = is_numeric($nopek) ? (int)$nopek : null;
                error_log("[user-delete] Found user_id from currentProfile f_nopekerja: {$userId}");
            } else {
                // Method 2: Get from database using stafID (most reliable)
                if ($stafID) {
                    $userProfile = $userModel->getProfile($stafID);
                    if (!empty($userProfile['f_nopekerja'])) {
                        $nopek = $userProfile['f_nopekerja'];
                        $userId = is_numeric($nopek) ? (int)$nopek : null;
                        error_log("[user-delete] Found user_id from DB profile f_nopekerja (stafID={$stafID}): {$userId}");
                    } else {
                        error_log("[user-delete] WARNING: getProfile() returned no f_nopekerja for stafID={$stafID}");
                    }
                } else {
                    error_log("[user-delete] WARNING: No stafID in session to query user_id");
                }
            }
            
            // Normalize/override user_id derivation with standardized logic
            try {
                $derivedUserId = null;
                if (!empty($_SESSION['user']['f_userID']) && is_numeric($_SESSION['user']['f_userID'])) {
                    $derivedUserId = (int)$_SESSION['user']['f_userID'];
                } elseif (!empty($_SESSION['f_userID']) && is_numeric($_SESSION['f_userID'])) {
                    $derivedUserId = (int)$_SESSION['f_userID'];
                } else {
                    $cand = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? $_SESSION['f_stafID'] ?? null;
                    if ($cand) {
                        if (is_numeric($cand)) $derivedUserId = (int)$cand;
                        elseif (preg_match('/^(\d+)/', (string)$cand, $mm)) $derivedUserId = (int)$mm[1];
                    }
                    if ($derivedUserId === null && !empty($_SESSION['f_stafID'])) {
                        $up = $userModel->getProfile($_SESSION['f_stafID']);
                        if (!empty($up['f_nopekerja'])) {
                            $c2 = $up['f_nopekerja'];
                            if (is_numeric($c2)) $derivedUserId = (int)$c2;
                            elseif (preg_match('/^(\d+)/', (string)$c2, $mm2)) $derivedUserId = (int)$mm2[1];
                        }
                    }
                }
                if ($derivedUserId !== null) {
                    $userId = $derivedUserId;
                }
            } catch (Throwable $e) {
                error_log('[user-delete] Derived user_id override failed: ' . $e->getMessage());
            }

            // ✅ FIX: Format actor_label dengan nostaf full: "[nama] (nostaf)"
            $nama = $_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null;
            $nostaf = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? null;
            $actorLabel = null;
            if (function_exists('audit_format_actor_label')) {
                $actorLabel = audit_format_actor_label($nama, $nostaf);
            } else {
                // Fallback: guna nama sahaja jika helper tidak available
                $actorLabel = $nama;
            }
            
            error_log("[user-delete] Audit prep: request_id={$requestId}, session_id={$sessionId}, user_id={$userId}, actor={$actorLabel}, target_userID={$userID}");
            
            // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
            $message = audit_format_message('User permanently deleted', $actorLabel);
            
            // Prepare audit data
            $auditData = [
                'event_type'  => 'DELETE',
                'severity'    => 'WARN',
                'outcome'     => 'SUCCESS',
                'target_type' => 'user',
                'target_id'   => (string)$userID,
                'target_label' => 'User: ' . ($userData['f_nama'] ?? 'Unknown'),
                'message'     => $message,
                'request_id'  => $requestId,
                'session_id'  => $sessionId,
                'user_id'     => $userId,
                'actor_label' => $actorLabel,
                'meta'        => [
                    'deleted_stafID' => $userData['f_stafID'] ?? null,
                    'deleted_nopekerja' => $userData['f_nopekerja'] ?? null,
                    'reason' => 'Deleted via user management',
                    'deleted_by' => $loggedInStafID
                ]
            ];
            
            error_log("[user-delete] Calling audit_event with data: " . json_encode($auditData));
            
            $eventId = audit_event($auditData);
            
            error_log("[user-delete] Audit event result: event_id=" . ($eventId ?? 'null') . ", request_id={$requestId}, session_id={$sessionId}, user_id={$userId}");
            
            if (!$eventId) {
                error_log('[user-delete] WARNING: audit_event() returned null/0 - trying direct AuditLogger fallback');
                // Try direct insert as fallback
                try {
                    require_once __DIR__ . '/../classes/AuditLogger.php';
                    $pdo = Database::getInstance('mysql')->getConnection();
                    $logger = new AuditLogger($pdo);
                    $eventId = $logger->logEvent($auditData);
                    error_log("[user-delete] Direct AuditLogger call result: event_id={$eventId}");
                } catch (\Throwable $fallbackError) {
                    error_log('[user-delete] Direct AuditLogger fallback also failed: ' . $fallbackError->getMessage() . ' | Trace: ' . $fallbackError->getTraceAsString());
                }
            } else {
                error_log("[user-delete] SUCCESS: Audit event logged with event_id={$eventId}");
            }
        } else {
            error_log('[user-delete] ERROR: audit_event() function still not available after manual load attempt');
        }
    } catch (\Throwable $e) {
        // Don't block user deletion if audit fails
        error_log('[user-delete] Audit logging EXCEPTION: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine() . ' | Trace: ' . $e->getTraceAsString());
    }

    // Clear staf options cache after successful delete
    if (isset($_SESSION['userlist_cache']['staf_options_list'])) {
        unset($_SESSION['userlist_cache']['staf_options_list']);
    }
    
    echo json_encode([
        'error' => false,
        'message' => 'Pengguna berjaya dipadam.',
        'userID' => $userID
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('[user-delete] PDO Error: ' . $e->getMessage());
    echo json_encode([
        'error' => true,
        'message' => 'Ralat database: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[user-delete] Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Ralat sistem semasa memadam pengguna.'
    ], JSON_UNESCAPED_UNICODE);
}
