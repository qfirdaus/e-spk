<?php
// ajax/user-add.php
// Add new user to tbl_m_user from Sybase data
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
    if (!checkRateLimit('user_add', 20, 60)) {
        http_response_code(429);
        echo json_encode([
            'error' => true,
            'message' => 'Terlalu banyak permintaan. Sila cuba lagi selepas beberapa saat.'
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
    $nopekerja = trim((string)($data['nopekerja'] ?? ''));
    $idpekerja = trim((string)($data['idpekerja'] ?? ''));
    $groupID = (int)($data['groupID'] ?? 0);
    $flag = isset($data['flag']) ? (int)$data['flag'] : 1;

    // Validation
    if ($nopekerja === '') {
        echo json_encode([
            'error' => true,
            'message' => 'No. pekerja tidak boleh kosong.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!in_array($flag, [0, 1], true)) {
        $flag = 1; // Default to allowed
    }

    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../classes/User.php';

    $pdo = Database::getInstance('mysql')->getConnection();
    $userModel = new User($pdo);
    $currentProfile = $userModel->getProfile($_SESSION['f_stafID'] ?? '');
    $isSuperAdmin = $currentProfile && function_exists('is_user_super_admin') && is_user_super_admin($currentProfile, $pdo);
    if (!$isSuperAdmin) {
        http_response_code(403);
        echo json_encode([
            'error' => true,
            'message' => 'Anda tidak mempunyai kebenaran untuk menambah pengguna.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate group exists in database (prefer f_groupID)
    $groupRow = null;
    if ($groupID > 0) {
        $groupCheckSql = "SELECT f_groupID, f_groupKod FROM tbl_m_group WHERE f_groupID = :groupID LIMIT 1";
        $groupCheckStmt = $pdo->prepare($groupCheckSql);
        $groupCheckStmt->execute([':groupID' => $groupID]);
        $groupRow = $groupCheckStmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$groupRow) {
        echo json_encode([
            'error' => true,
            'message' => 'Kumpulan pengguna tidak sah atau tidak wujud dalam sistem.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $groupID = (int)($groupRow['f_groupID'] ?? 0);
    $groupKod = (string)($groupRow['f_groupKod'] ?? '');

    // Check if user already exists
    $checkSql = "SELECT f_userID FROM tbl_m_user WHERE f_stafID = :stafID";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':stafID' => $nopekerja]);
    
    if ($checkStmt->fetch()) {
        echo json_encode([
            'error' => true,
            'message' => 'Pengguna dengan no. pekerja ini sudah wujud dalam sistem.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get staf data from Sybase
    $pdoSybase = Database::pdoSybaseActive();
    
    $sybaseSql = "
        SELECT 
            nopekerja,
            idpekerja,
            gelar_nama,
            nama,
            nokp,
            email,
            handphone,
            kdjwtsemasa,
            jawatansemasa,
            kdjenis,
            jenis,
            kdjbtnsemasa,
            jabatansemasa,
            kumpjwt,
            kodstatus,
            status
        FROM v630staf_service_skim_all
        WHERE nopekerja = :nopekerja
          AND CONVERT(INT, kodstatus) = 1
    ";
    
    $sybaseStmt = $pdoSybase->prepare($sybaseSql);
    $sybaseStmt->execute([':nopekerja' => $nopekerja]);
    $sybaseUser = $sybaseStmt->fetch(PDO::FETCH_ASSOC);
    
    // Log for debugging
    error_log('[user-add] Sybase query result: ' . ($sybaseUser ? 'found' : 'not found') . ' for nopekerja: ' . $nopekerja);

    if (!$sybaseUser) {
        echo json_encode([
            'error' => true,
            'message' => 'Staf tidak dijumpai dalam sistem Sybase atau tidak aktif.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get logged in user for audit
    $loggedInStafID = $_SESSION['f_stafID'] ?? null;
    $remarks = 'Added via Tambah Pengguna form';
    
    // Generate password from f_nokp (hash nokp)
    $nokp = $sybaseUser['nokp'] ?? '';
    $hashedPassword = '';
    if (!empty($nokp)) {
        // Hash nokp untuk password default
        $hashedPassword = password_hash($nokp, PASSWORD_DEFAULT);
    }

    // Insert into tbl_m_user
    $insertSql = "
        INSERT INTO tbl_m_user (
            f_stafID,
            f_nopekerja,
            f_nama,
            f_nickname,
            f_nokp,
            f_password,
            f_email,
            f_handphone,
            f_jawatanKod,
            f_jawatan,
            f_jenisID,
            f_jenis,
            f_jabatanKod,
            f_namajabatan,
            f_kumpjawatan,
            f_statusID,
            f_status,
            f_groupID,
            f_groupKod,
            f_flag,
            f_insertdt,
            f_updatedt,
            f_updateby,
            f_remarks
        ) VALUES (
            :stafID,
            :idpekerja,
            :gelar_nama,
            :nama,
            :nokp,
            :password,
            :email,
            :handphone,
            :kdjwtsemasa,
            :jawatansemasa,
            :kdjenis,
            :jenis,
            :kdjbtnsemasa,
            :jabatansemasa,
            :kumpjwt,
            :kodstatus,
            :status,
            :groupID,
            :groupKod,
            :flag,
            NOW(),
            NOW(),
            :updateby,
            :remarks
        )
    ";

    $insertStmt = $pdo->prepare($insertSql);
    $result = $insertStmt->execute([
        ':stafID' => $nopekerja,
        ':idpekerja' => $idpekerja ?: ($sybaseUser['idpekerja'] ?? null),
        ':gelar_nama' => $sybaseUser['gelar_nama'] ?? null,
        ':nama' => $sybaseUser['nama'] ?? null,
        ':nokp' => $sybaseUser['nokp'] ?? null,
        ':password' => $hashedPassword,
        ':email' => $sybaseUser['email'] ?? null,
        ':handphone' => $sybaseUser['handphone'] ?? null,
        ':kdjwtsemasa' => $sybaseUser['kdjwtsemasa'] ?? null,
        ':jawatansemasa' => $sybaseUser['jawatansemasa'] ?? null,
        ':kdjenis' => !empty($sybaseUser['kdjenis']) ? (int)$sybaseUser['kdjenis'] : null,
        ':jenis' => $sybaseUser['jenis'] ?? null,
        ':kdjbtnsemasa' => $sybaseUser['kdjbtnsemasa'] ?? null,
        ':jabatansemasa' => $sybaseUser['jabatansemasa'] ?? null,
        ':kumpjwt' => $sybaseUser['kumpjwt'] ?? null,
        ':kodstatus' => !empty($sybaseUser['kodstatus']) ? (int)$sybaseUser['kodstatus'] : null,
        ':status' => $sybaseUser['status'] ?? null,
        ':groupID' => $groupID,
        ':groupKod' => $groupKod,
        ':flag' => $flag,
        ':updateby' => $loggedInStafID,
        ':remarks' => $remarks
    ]);

    if (!$result) {
        throw new Exception('Gagal menyimpan data pengguna.');
    }

    $newUserId = (int)$pdo->lastInsertId();

    // Audit: Log user creation
    try {
        if (function_exists('audit_event')) {
            $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;
            $sessionId = session_id() ?: null;
            
            // Determine numeric user_id for audit. Prefer session f_userID, then parse staff no.
            $userId = null;
            if (!empty($_SESSION['user']['f_userID']) && is_numeric($_SESSION['user']['f_userID'])) {
                $userId = (int)$_SESSION['user']['f_userID'];
            } elseif (!empty($_SESSION['f_userID']) && is_numeric($_SESSION['f_userID'])) {
                $userId = (int)$_SESSION['f_userID'];
            } else {
                $candidate = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? $_SESSION['f_stafID'] ?? null;
                if ($candidate) {
                    if (is_numeric($candidate)) {
                        $userId = (int)$candidate;
                    } elseif (preg_match('/^(\d+)/', (string)$candidate, $m)) {
                        $userId = (int)$m[1];
                    }
                }
                // If still null, try DB lookup by stafID
                if ($userId === null && !empty($_SESSION['f_stafID'])) {
                    try {
                        $pdo = Database::getInstance('mysql')->getConnection();
                        $userModel = new User($pdo);
                        $userProfile = $userModel->getProfile($_SESSION['f_stafID']);
                        if (!empty($userProfile['f_nopekerja'])) {
                            $cand = $userProfile['f_nopekerja'];
                            if (is_numeric($cand)) $userId = (int)$cand;
                            elseif (preg_match('/^(\d+)/', (string)$cand, $m2)) $userId = (int)$m2[1];
                        }
                    } catch (Throwable $e) {
                        error_log('[user-add] user_id derivation DB lookup failed: ' . $e->getMessage());
                    }
                }
            }
            error_log("[user-add] Derived audit user_id={$userId}");
            
            $actorLabel = $_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null;
            
            error_log("[user-add] Audit prep: request_id={$requestId}, session_id={$sessionId}, user_id={$userId}, actor={$actorLabel}");
            
            // ✅ FIX: Format actor_label dengan nostaf full: "[nama] (nostaf)"
            $nama = $actorLabel;
            $nostaf = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? null;
            $formattedActorLabel = null;
            if (function_exists('audit_format_actor_label')) {
                $formattedActorLabel = audit_format_actor_label($nama, $nostaf);
            } else {
                // Fallback: guna nama sahaja jika helper tidak available
                $formattedActorLabel = $nama;
            }
            
            // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
            $message = audit_format_message('User created from Sybase data', $formattedActorLabel);
            
            // Build target_label: prefer gelar_nama (full with titles), fallback to nama, then staf id
            $targetName = trim((string)($sybaseUser['gelar_nama'] ?? $sybaseUser['nama'] ?? $nopekerja));
            $statusSuffix = '';
            if (!empty($sybaseUser['status'])) {
                $st = trim((string)$sybaseUser['status']);
                if ($st !== '' && stripos($targetName, $st) === false) {
                    $statusSuffix = ' (' . $st . ')';
                }
            }

            $eventId = audit_event([
                'event_type'  => 'CREATE',
                'severity'    => 'INFO',
                'outcome'     => 'SUCCESS',
                'target_type' => 'user',
                'target_id'   => (string)$nopekerja,
                'target_label' => 'User: ' . $targetName . $statusSuffix,
                'message'     => $message,
                'request_id'  => $requestId,
                'session_id'  => $sessionId,
                'user_id'     => $userId,
                'actor_label' => $formattedActorLabel,
                'meta'        => [
                    'groupID' => $groupID,
                    'groupKod' => $groupKod,
                    'flag' => $flag,
                    'source' => 'user_add_ajax',
                    'userID' => $newUserId
                ]
            ]);
            
            error_log("[user-add] Audit event result: event_id=" . ($eventId ?? 'null') . ", request_id={$requestId}, session_id={$sessionId}, user_id={$userId}");
        } else {
            error_log('[user-add] audit_event() function not found');
        }
    } catch (\Throwable $e) {
        // Don't block user creation if audit fails
        error_log('[user-add] Audit logging failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    }

    // Clear staf options cache after successful add
    if (isset($_SESSION['userlist_cache']['staf_options_list'])) {
        unset($_SESSION['userlist_cache']['staf_options_list']);
    }
    
    echo json_encode([
        'error' => false,
        'message' => 'Pengguna berjaya ditambah.',
        'userID' => $newUserId
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('[user-add] PDO Error: ' . $e->getMessage());
    
    // Check for duplicate entry
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
        echo json_encode([
            'error' => true,
            'message' => 'Pengguna dengan no. pekerja ini sudah wujud dalam sistem.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Ralat database: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    error_log('[user-add] Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Ralat sistem semasa menambah pengguna.'
    ], JSON_UNESCAPED_UNICODE);
}
