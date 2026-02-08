<?php
// ajax/profile-kill-session.php
// Kill/end a user session from audit_session table
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
function checkRateLimit(string $key, int $maxRequests = 10, int $windowSeconds = 60): bool {
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
            'success' => false,
            'message' => 'Sila log masuk terlebih dahulu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Check rate limit
    if (!checkRateLimit('kill_session', 10, 60)) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Terlalu banyak percubaan. Sila tunggu sebentar.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Check method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON input'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validate CSRF token
    $csrfToken = $data['csrf_token'] ?? '';
    if (empty($csrfToken) || $csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'CSRF token tidak sah. Sila muat semula halaman.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validate session_id
    $sessionId = trim($data['session_id'] ?? '');
    if (empty($sessionId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID sesi tidak ditentukan.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Prevent killing current session
    $currentSessionId = session_id();
    if ($sessionId === $currentSessionId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Tidak boleh tamatkan sesi semasa.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get database connection
    require_once __DIR__ . '/../classes/Database.php';
    $pdo = Database::getInstance('mysql')->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user info for validation
    $stafID = trim($_SESSION['f_stafID'] ?? '');
    if (empty($stafID)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Sila log masuk terlebih dahulu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get user_nopekerja for validation
    $sqlUser = "SELECT f_nopekerja FROM tbl_m_user WHERE f_stafID = :stafID LIMIT 1";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([':stafID' => $stafID]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
    if (!$userRow) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Pengguna tidak ditemui.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $userNopekerja = $userRow['f_nopekerja'] ?? '';
    
    // Verify session belongs to current user
    // Use distinct placeholders to avoid "Invalid parameter number" on some PDO drivers
    $sqlCheck = "
        SELECT id, session_id, user_nopekerja, ended_at
        FROM audit_session
        WHERE session_id = :sid
        AND (user_nopekerja = :nopek OR user_id = :uid)
        LIMIT 1
    ";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([
        ':sid' => $sessionId,
        ':nopek' => $userNopekerja,
        ':uid' => $userNopekerja
    ]);
    $sessionRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$sessionRow) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Sesi tidak ditemui atau tidak milik anda.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Check if session already ended
    if (!empty($sessionRow['ended_at'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Sesi ini sudah tamat.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Update session to end it
    $sqlUpdate = "
        UPDATE audit_session
        SET ended_at = NOW(6)
        WHERE session_id = :sid
        AND ended_at IS NULL
    ";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([':sid' => $sessionId]);
    
    if ($stmtUpdate->rowCount() > 0) {
        // Log audit event
        try {
            if (function_exists('audit_event')) {
                $userId = null;
                if (!empty($_SESSION['f_nopekerja']) && is_numeric($_SESSION['f_nopekerja'])) {
                    $userId = (int)$_SESSION['f_nopekerja'];
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
                
                // Build message safely (audit helper may not be available in all contexts)
                if (function_exists('audit_format_message')) {
                    $message = audit_format_message('Session terminated', $actorLabel);
                } else {
                    $message = 'Session terminated' . ($actorLabel ? ' by ' . $actorLabel : '');
                }
                
                audit_event([
                    'event_type'  => 'LOGOUT',
                    'severity'    => 'INFO',
                    'outcome'     => 'SUCCESS',
                    'target_type' => 'session',
                    'target_id'   => $sessionId,
                    'target_label' => 'Session terminated by user',
                    'message'     => $message,
                    'session_id'  => $currentSessionId,
                    'user_id'     => $userId,
                    'actor_label' => $actorLabel,
                    'meta'        => [
                        'terminated_session_id' => $sessionId,
                        'terminated_by' => $stafID
                    ]
                ]);
            }
        } catch (\Throwable $e) {
            error_log('[profile-kill-session] audit_event error: ' . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Sesi berjaya ditamatkan.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal tamatkan sesi. Sesi mungkin sudah tamat.'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    error_log('[profile-kill-session] PDO Exception: ' . $e->getMessage());
    http_response_code(500);
    $resp = [
        'success' => false,
        'message' => 'Ralat pangkalan data. Sila cuba lagi.'
    ];
    if (function_exists('is_development_mode') && is_development_mode()) {
        $resp['debug'] = $e->getMessage();
    }
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    error_log('[profile-kill-session] Error: ' . $e->getMessage());
    http_response_code(500);
    $resp = [
        'success' => false,
        'message' => 'Ralat sistem. Sila cuba lagi.'
    ];
    if (function_exists('is_development_mode') && is_development_mode()) {
        $resp['debug'] = $e->getMessage();
    }
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
}


