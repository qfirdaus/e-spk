<?php
// ajax/user-set-group.php
declare(strict_types=1);

// Suppress warnings/notices that might output HTML
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors, we'll handle them
ini_set('log_errors', '1');

// Set custom error handler to catch all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Log error but don't output
    error_log("[user-set-group] PHP Error: $errstr in $errfile:$errline");
    return true; // Suppress default error handler
}, E_ALL);

// Set exception handler
set_exception_handler(function($e) {
    error_log('[user-set-group] Uncaught Exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
    // Clean all output
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>true, 'message'=>'Ralat server. Sila hubungi pentadbir sistem.'], JSON_UNESCAPED_UNICODE);
    exit;
});

// Prevent any output before JSON
while (ob_get_level() > 0) {
    @ob_end_clean();
}
ob_start();

/**
 * Simple rate limiting (per session)
 */
function checkRateLimit(string $key, int $maxRequests = 30, int $windowSeconds = 60): bool {
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
    require_login();

    // Clean any output from init.php or requires
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    
    // Rate limiting: max 30 requests per 60 seconds
    if (!checkRateLimit('user_set_group', 30, 60)) {
        json_fail('Terlalu banyak permintaan. Sila cuba lagi selepas beberapa saat.', 429);
    }
} catch (Throwable $initError) {
    // Clean all output
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    error_log('[user-set-group] Init Error: '.$initError->getMessage().' in '.$initError->getFile().':'.$initError->getLine());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>true, 'message'=>'Ralat sistem. Sila hubungi pentadbir sistem.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_fail(string $msg, int $code = 400, array $extra = []): never {
    // Clean all output buffers
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    // Clear any previous output
    if (ob_get_length()) {
        @ob_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode(['error'=>true, 'message'=>$msg] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}
function json_ok(array $data = []): never {
    // Clean all output buffers
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    // Clear any previous output
    if (ob_get_length()) {
        @ob_clean();
    }
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode(['error'=>false] + $data, JSON_UNESCAPED_UNICODE);
    exit;
}
function actor_id(): int {
    return (int)($_SESSION['auth']['f_userID'] ?? $_SESSION['user']['f_userID'] ?? $_SESSION['user_id'] ?? 0);
}
function actor_name(): string {
    return (string)($_SESSION['auth']['f_nama'] ?? $_SESSION['user']['f_nama'] ?? $_SESSION['d_name'] ?? '');
}

try {
    // ===== CSRF =====
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $csrfHdr = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? '';
    if (!$csrfHdr || !hash_equals($_SESSION['csrf_token'] ?? '', $csrfHdr)) {
        json_fail('CSRF token tidak sah', 400);
    }

    // ===== Input =====
    $rawInput = file_get_contents('php://input');
    error_log("[user-set-group] RAW INPUT: " . $rawInput);
    
    $data     = json_decode($rawInput, true) ?: [];
    error_log("[user-set-group] PARSED DATA: " . json_encode($data));
    
    $userID   = (int)($data['userID'] ?? 0);
    $groupID  = (int)($data['groupID'] ?? 0);
    // Flag: check terus dari $data, bukan dari variable (untuk handle flag = 0)
    // Use array_key_exists instead of isset to detect flag = 0
    $hasFlag  = array_key_exists('flag', $data);
    $flag     = $hasFlag ? (int)$data['flag'] : null;
    
    error_log("[user-set-group] PARSED VALUES: userID=$userID, groupID=$groupID, hasFlag=" . ($hasFlag ? 'true' : 'false') . ", flag=" . ($flag !== null ? $flag : 'null'));

    // Allow update if: userID valid AND (group provided OR flag provided)
    if ($userID <= 0) {
        json_fail('Parameter tidak lengkap (userID diperlukan).', 422);
    }
    
    // At least one of group or flag must be provided
    $hasGroup = ($groupID > 0);
    if (!$hasGroup && !$hasFlag) {
        json_fail('Parameter tidak lengkap (groupID atau flag diperlukan).', 422);
    }

    /** @var PDO $db */
    $db = Database::getInstance('mysql')->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ===== Helper: semak kolum wujud =====
    $colExists = function(PDO $db, string $table, string $col): bool {
        $q = $db->prepare("
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c
            LIMIT 1
        ");
        $q->execute([':t'=>$table, ':c'=>$col]);
        return (bool)$q->fetchColumn();
    };

    $hasGroupID  = $colExists($db, 'tbl_m_user', 'f_groupID');
    $hasGroupKod = $colExists($db, 'tbl_m_user', 'f_groupKod');

    if (!$hasGroupID && !$hasGroupKod) {
        json_fail('Skema pengguna tiada f_groupID atau f_groupKod.', 500);
    }

    // ===== Dapatkan USER + group lama (SELECT dinamik) =====
    $selCols = "u.f_userID, u.f_nama, u.f_stafID, u.f_flag AS old_flag";
    $selCols .= $hasGroupID  ? ", u.f_groupID  AS old_groupID"  : ", NULL AS old_groupID";
    $selCols .= $hasGroupKod ? ", u.f_groupKod AS old_groupKod" : ", NULL AS old_groupKod";

    // JOIN dinamik: ikut kolum yang ada
    if ($hasGroupID) {
        $join = "LEFT JOIN tbl_m_group g ON g.f_groupID = u.f_groupID";
    } elseif ($hasGroupKod) {
        $join = "LEFT JOIN tbl_m_group g ON g.f_groupKod = u.f_groupKod";
    } else {
        $join = ""; // tak patut berlaku sebab dah guard atas
    }

    $sqlUser = "
        SELECT $selCols, g.f_groupName AS old_groupName
        FROM tbl_m_user u
        $join
        WHERE u.f_userID = :uid
        LIMIT 1
    ";
    $stmtU = $db->prepare($sqlUser);
    $stmtU->execute([':uid'=>$userID]);
    $user = $stmtU->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        json_fail('Pengguna tidak ditemui.', 404);
    }

    $oldID   = (int)($user['old_groupID'] ?? 0);
    $oldKod  = (string)($user['old_groupKod'] ?? '');
    $oldName = (string)($user['old_groupName'] ?? ($oldKod ?: ($oldID ?: '')));
    $oldFlag = (int)($user['old_flag'] ?? 0);

    // ===== Dapatkan GROUP sasaran (hanya jika group diupdate) =====
    $gid  = $oldID;
    $gkod = $oldKod;
    $gnam = $oldName;
    
    // Only query group if group is being updated
    if ($hasGroup) {
        $stmt = $db->prepare("SELECT f_groupID, f_groupKod, f_groupName FROM tbl_m_group WHERE f_groupID = :gid LIMIT 1");
        $stmt->execute([':gid'=>$groupID]);
        $grp = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$grp) {
            json_fail('Kumpulan tidak ditemui.', 404);
        }
        $gid  = (int)$grp['f_groupID'];
        $gkod = (string)$grp['f_groupKod'];
        $gnam = (string)$grp['f_groupName'];
    }

    // ===== No-op? Check both group AND flag - only no-op if BOTH are same =====
    $groupSame = false;
    $flagSame = false;
    
    // Check if group is same (only if group is being updated)
    if ($hasGroup) {
        if ($hasGroupID) {
            $groupSame = ($oldID === $gid);
        } elseif ($hasGroupKod && $oldKod !== '') {
            $groupSame = ($oldKod === $gkod);
        }
    } else {
        // If group not being updated, consider it "same" (no change)
        $groupSame = true;
    }
    
    // Check if flag is same (only if flag is being updated)
    if ($hasFlag) {
        $flagSame = ($oldFlag === $flag);
    } else {
        // If flag not being updated, consider it "same" (no change)
        $flagSame = true;
    }
    
    // Only no-op if BOTH group and flag are same (or not being updated)
    $same = $groupSame && $flagSame;
    
    error_log("[user-set-group] No-op check: hasGroup=" . ($hasGroup ? 'true' : 'false') . ", hasFlag=" . ($hasFlag ? 'true' : 'false') . ", groupSame=" . ($groupSame ? 'true' : 'false') . ", flagSame=" . ($flagSame ? 'true' : 'false') . ", same=" . ($same ? 'true' : 'false'));
    error_log("[user-set-group] Values: oldKod='$oldKod', gkod='$gkod', oldFlag=$oldFlag, flag=" . ($flag !== null ? (string)$flag : 'null'));

    $metaCommon = [
        'stafID'     => (string)($user['f_stafID'] ?? ''),
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'csrf_hash'  => substr(hash('sha256', (string)($_SESSION['csrf_token'] ?? '')), 0, 16),
        'route'      => $_SERVER['REQUEST_URI'] ?? 'ajax/user-set-group.php',
    ];

    if ($same) {
        // Event (noop)
        $eventId = audit_event([
            'type'       => 'user.group.update',
            'action'     => 'UPDATE',
            'status'     => 'noop',
            'actor_id'   => actor_id(),
            'actor_name' => actor_name(),
            'target_type'=> 'user',
            'target_id'  => (string)$user['f_userID'],
            'target_name'=> (string)$user['f_nama'],
            'meta'       => $metaCommon,
        ]) ?? 0;

        if ($eventId) {
            $csId = audit_begin_change($eventId, 'user', (string)$user['f_userID'], 'No change', ['context'=>'set-group']) ?? 0;
            if ($csId) {
                if ($hasGroup && $hasGroupID)  audit_change($csId, 'f_groupID',  $oldID,  $gid,  'number', false, 'noop');
                if ($hasGroup && $hasGroupKod) audit_change($csId, 'f_groupKod', $oldKod, $gkod, 'string', false, 'noop');
                if (!$hasGroup && $hasFlag)    audit_change($csId, 'f_flag', $oldFlag, $flag, 'number', false, 'noop');
            }
        }

        $noopMessage = $hasGroup 
            ? 'Tiada perubahan — kumpulan kekal sama.'
            : 'Tiada perubahan — akses kekal sama.';
        
        json_ok([
            'message' => $noopMessage,
            'group'   => $hasGroup ? ['id'=>$gid, 'kod'=>$gkod, 'nama'=>$gnam] : null,
            'groupName' => $hasGroup ? $gnam : null, // ✅ Add groupName at top level for easier access
            'flag'    => $hasFlag ? $flag : null,
            'audit'   => ['event_id'=>$eventId, 'status'=>'noop']
        ]);
    }

    // ===== Transaksi: update + audit =====
    $db->beginTransaction();
    error_log("[user-set-group] Transaction started");

    // Build UPDATE query with group and/or flag (allow update salah satu sahaja)
    $setParts = [];
    $params = [':uid'=>$userID];
    
    // SECURITY CRITICAL – DO NOT MODIFY: primary role assignment (f_groupID) drives access control
    // Add group if provided (check $hasGroup = input parameter, not column existence)
    if ($hasGroup) {
        // Add group fields based on which columns exist in database
        if ($hasGroupID && $hasGroupKod) {
            $setParts[] = "f_groupID = :gid";
            $setParts[] = "f_groupKod = :gkod";
            $params[':gid'] = $gid;
            $params[':gkod'] = $gkod;
            error_log("[user-set-group] Group update: userID=$userID, groupID=$gid, groupKod=$gkod");
        } elseif ($hasGroupID) {
            $setParts[] = "f_groupID = :gid";
            $params[':gid'] = $gid;
            error_log("[user-set-group] Group update: userID=$userID, groupID=$gid");
        } elseif ($hasGroupKod) {
            $setParts[] = "f_groupKod = :gkod";
            $params[':gkod'] = $gkod;
            error_log("[user-set-group] Group update: userID=$userID, groupKod=$gkod");
        }
    }
    
    // Add flag if provided (always update flag if sent, even if 0)
    if ($hasFlag) {
        $setParts[] = "f_flag = :flag";
        $params[':flag'] = $flag;
        error_log("[user-set-group] Flag update: userID=$userID, flag=$flag, hasGroup=" . ($hasGroup ? 'true' : 'false'));
    }
    
    // Must have at least one field to update
    if (empty($setParts)) {
        error_log("[user-set-group] ERROR: setParts is empty! hasGroup=" . ($hasGroup ? 'true' : 'false') . ", hasFlag=" . ($hasFlag ? 'true' : 'false'));
        json_fail('Tiada field untuk dikemas kini.', 422);
    }
    
    error_log("[user-set-group] Final setParts: " . json_encode($setParts) . ", params keys: " . json_encode(array_keys($params)));
    
    $sql = "UPDATE tbl_m_user SET " . implode(', ', $setParts) . " WHERE f_userID = :uid";
    error_log("[user-set-group] ===== EXECUTING UPDATE =====");
    error_log("[user-set-group] SQL: $sql");
    error_log("[user-set-group] Params: " . json_encode($params));
    error_log("[user-set-group] setParts count: " . count($setParts));
    
    try {
        $upd = $db->prepare($sql);
        $result = $upd->execute($params);
        $rowsAffected = $upd->rowCount();
        error_log("[user-set-group] Execute result: " . ($result ? 'SUCCESS' : 'FAILED'));
        error_log("[user-set-group] Rows affected: $rowsAffected");
        
        if (!$result) {
            $errorInfo = $upd->errorInfo();
            error_log("[user-set-group] PDO Error: " . json_encode($errorInfo));
            json_fail('Gagal kemas kini rekod pengguna: ' . ($errorInfo[2] ?? 'Unknown error'), 500);
        }
        
        if ($rowsAffected === 0) {
            error_log("[user-set-group] WARNING: No rows affected! Checking if user exists...");
            // Check if user exists
            $checkStmt = $db->prepare("SELECT f_userID, f_flag FROM tbl_m_user WHERE f_userID = :uid");
            $checkStmt->execute([':uid' => $userID]);
            $checkUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
            error_log("[user-set-group] User check: " . json_encode($checkUser));
            
            if (!$checkUser) {
                json_fail('Pengguna tidak ditemui dalam database.', 404);
            } else {
                error_log("[user-set-group] User exists but no rows affected - possible no-op (value already set)");
                // Continue - might be no-op
            }
        }
    } catch (PDOException $e) {
        error_log("[user-set-group] PDO Exception: " . $e->getMessage());
        error_log("[user-set-group] SQL: $sql");
        error_log("[user-set-group] Params: " . json_encode($params));
        json_fail('Database error: ' . $e->getMessage(), 500);
    }

    // Commit transaction FIRST (before audit to ensure update succeeds)
    $db->commit();
    error_log("[user-set-group] Transaction committed successfully");

    // Audit: Log user group/flag update dengan field changes
    $eventId = null;
    try {
        if (function_exists('audit_event')) {
            $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;
            
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
            
            // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
            $message = audit_format_message('User group or access flag updated', $actorLabel);
            
            // Derive numeric user_id for audit (prefer f_userID then parse staff no; DB fallback)
            $derivedUserId = null;
            if (!empty($_SESSION['user']['f_userID']) && is_numeric($_SESSION['user']['f_userID'])) {
                $derivedUserId = (int)$_SESSION['user']['f_userID'];
            } elseif (!empty($_SESSION['f_userID']) && is_numeric($_SESSION['f_userID'])) {
                $derivedUserId = (int)$_SESSION['f_userID'];
            } else {
                $cand = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? $_SESSION['f_stafID'] ?? null;
                if ($cand) {
                    if (is_numeric($cand)) $derivedUserId = (int)$cand;
                    elseif (preg_match('/^(\d+)/', (string)$cand, $m)) $derivedUserId = (int)$m[1];
                }
                if ($derivedUserId === null && !empty($_SESSION['f_stafID'])) {
                    try {
                        $up = (new User($db))->getProfile($_SESSION['f_stafID']);
                        if (!empty($up['f_nopekerja'])) {
                            $c = $up['f_nopekerja'];
                            if (is_numeric($c)) $derivedUserId = (int)$c;
                            elseif (preg_match('/^(\d+)/', (string)$c, $m2)) $derivedUserId = (int)$m2[1];
                        }
                    } catch (Throwable $e) {
                        error_log('[user-set-group] user_id derivation DB lookup failed: ' . $e->getMessage());
                    }
                }
            }

            $eventId = audit_event([
                'event_type'  => 'UPDATE',
                'severity'    => 'INFO',
                'outcome'     => 'SUCCESS',
                'target_type' => 'user',
                'target_id'   => (string)$userID,
                'target_label' => 'User: ' . ($user['f_nama'] ?? 'Unknown'),
                'message'     => $message,
                'request_id'  => $requestId,
                'session_id'  => session_id() ?: null,
                'user_id'     => $derivedUserId,
                'actor_label' => $actorLabel,
                'meta'        => array_merge($metaCommon, [
                    'updated_fields' => array_keys($setParts)
                ])
            ]);

            // Log field changes
            if ($eventId) {
                $changeSetId = audit_begin_change($eventId, 'user', (string)$userID, 'User group/access update');
                if ($changeSetId) {
                    if ($hasGroup && $hasGroupID && $oldID !== $gid) {
                        audit_change($changeSetId, 'f_groupID', (string)$oldID, (string)$gid, 'integer', false);
                    }
                    if ($hasGroup && $hasGroupKod && $oldKod !== $gkod) {
                        audit_change($changeSetId, 'f_groupKod', $oldKod, $gkod, 'string', false);
                    }
                    if ($hasFlag && $oldFlag !== $flag) {
                        audit_change($changeSetId, 'f_flag', (string)$oldFlag, (string)$flag, 'integer', false);
                    }
                }
            }
        }
    } catch (\Throwable $auditError) {
        error_log("[user-set-group] Audit error (non-fatal): " . $auditError->getMessage());
        // Continue even if audit fails - update already succeeded
    }

    json_ok([
        'message' => 'Kumpulan dan akses berjaya dikemas kini.',
        'group'   => ['id'=>$gid, 'kod'=>$gkod, 'nama'=>$gnam],
        'groupName' => $gnam, // ✅ Add groupName at top level for easier access
        'flag'    => $hasFlag ? $flag : null,
        'audit'   => ['event_id'=>$eventId, 'status'=>'updated']
    ]);

} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        try {
            $db->rollBack();
        } catch (Throwable $rollbackErr) {
            // Ignore rollback errors
        }
    }
    // Clean all output buffers before sending error
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    if (ob_get_length()) {
        @ob_clean();
    }
    error_log('[user-set-group] Error: '.$e->getMessage().' | File: '.$e->getFile().' | Line: '.$e->getLine().' | Trace: '.$e->getTraceAsString());
    // Don't expose full error message in production, but log it
    $errorMsg = 'Ralat server. Sila hubungi pentadbir sistem.';
    if (defined('APP_DEBUG') && APP_DEBUG) {
        $errorMsg = 'Ralat server: '.$e->getMessage().' (File: '.basename($e->getFile()).', Line: '.$e->getLine().')';
    }
    json_fail($errorMsg, 500);
}
