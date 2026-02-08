<?php
// ajax/user-list-rows.php
// Return HTML rows for user table tbody (for AJAX reload)
declare(strict_types=1);

// Suppress ALL output
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Clean ALL output buffers first
while (ob_get_level() > 0) {
    @ob_end_clean();
}

// Start new output buffer
ob_start();

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("[user-list-rows] PHP Error: $errstr in $errfile:$errline");
    return true;
}, E_ALL);

// Set exception handler
set_exception_handler(function($e) {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    error_log('[user-list-rows] Uncaught Exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>true, 'message'=>'Ralat server. Sila hubungi pentadbir sistem.'], JSON_UNESCAPED_UNICODE);
    exit;
});

try {
    // Suppress any output from init.php
    ob_start();
    require_once __DIR__ . '/../includes/init.php';
    $initOutput = ob_get_clean();
    
    // Log if there's any output from init.php (should be empty)
    if (!empty($initOutput)) {
        error_log("[user-list-rows] WARNING: init.php produced output: " . substr($initOutput, 0, 200));
    }
    
    // Check login without redirect (for AJAX)
    if (empty($_SESSION['f_stafID'])) {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error'=>true, 'message'=>'Sila log masuk terlebih dahulu.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Prevent sync from running in AJAX request
    $_GET['manual_sync'] = true;
    
    // Suppress any output from requires
    ob_start();
    require_once __DIR__ . '/../controllers/UserListController.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../setting/constants/prestasi_constants.php';
    $requireOutput = ob_get_clean();
    
    // Log if there's any output from requires (should be empty)
    if (!empty($requireOutput)) {
        error_log("[user-list-rows] WARNING: requires produced output: " . substr($requireOutput, 0, 200));
    }
    
    // Get user list
    ob_start();
    $controller = new UserListController();
    $controllerOutput = ob_get_clean();
    
    // Log if there's any output from controller (should be empty)
    if (!empty($controllerOutput)) {
        error_log("[user-list-rows] WARNING: UserListController produced output: " . substr($controllerOutput, 0, 200));
    }
    
    $senaraiUser = $controller->senaraiUser ?? [];
    
    // User model untuk getAvatarUrl dan permission check
    $userModel = new User(Database::getInstance('mysql')->getConnection());
    
    // Get current user's group for permission control
    $currentStafID = $_SESSION['f_stafID'] ?? '';
    $currentProfile = $userModel->getProfile($currentStafID);
    $currentUserGroupId = (int)($currentProfile['f_groupID'] ?? 0);
    $roleAdminSaId = defined('PRESTASI_ROLE_ID_ADM_SA') ? (int)PRESTASI_ROLE_ID_ADM_SA : 0;
    $roleAdminHrId = defined('PRESTASI_ROLE_ID_ADM_HR') ? (int)PRESTASI_ROLE_ID_ADM_HR : 0;
    $roleAdminKeId = defined('PRESTASI_ROLE_ID_ADM_KE') ? (int)PRESTASI_ROLE_ID_ADM_KE : 0;
    $isADM_SA = ($roleAdminSaId > 0 && $currentUserGroupId === $roleAdminSaId);
    
    // Helper function format_stafid (h() already exists in html_helper.php)
    if (!function_exists('format_stafid')) {
        function format_stafid(?string $id): string {
            $id = trim((string)$id);
            $raw = str_replace('-', '', $id);
            if ($raw !== '' && ctype_digit($raw) && strlen($raw) === 6) {
                return substr($raw,0,4) . '-' . substr($raw,4,2);
            }
            return $id;
        }
    }
    
    // Generate HTML rows
    $htmlRows = '';
    $rows = [];
    if (!empty($senaraiUser)) {
        foreach ($senaraiUser as $u) {
            $userID  = (int)($u['f_userID'] ?? 0);
            $nama    = (string)($u['f_nama'] ?? '');
            $stafID  = format_stafid((string)($u['f_stafID'] ?? ''));
            $jabatan = (string)($u['f_namajabatan'] ?? '');
            $jawatan = (string)($u['f_jawatan'] ?? '');
            $gId     = (int)($u['f_groupID'] ?? 0);
            $gKod    = (string)($u['f_groupKod'] ?? '');
            $gName   = (string)($u['f_groupName'] ?? $gKod);
            $extraRoles = $u['extra_roles'] ?? [];
            if (!is_array($extraRoles)) $extraRoles = [];
            $extraCount = (int)($u['extra_roles_count'] ?? count($extraRoles));
            $f_flag  = (int)($u['f_flag'] ?? 0);
            $f_nopekerja = (string)($u['f_nopekerja'] ?? '');
            $avatarUrl = $userModel->getAvatarUrl($f_nopekerja);
            
            // Determine badge color
            $badgeClass = 'bg-secondary';
            if ($roleAdminSaId > 0 && $gId === $roleAdminSaId) {
                $badgeClass = 'bg-danger';
            } elseif ($roleAdminHrId > 0 && $gId === $roleAdminHrId) {
                $badgeClass = 'bg-warning';
            } elseif ($roleAdminKeId > 0 && $gId === $roleAdminKeId) {
                $badgeClass = 'bg-info';
            }
            
            // Determine row class
            $rowClass = '';
            if ($roleAdminSaId > 0 && $gId === $roleAdminSaId) {
                $rowClass = 'row-group-adm-sa';
            } elseif ($roleAdminHrId > 0 && $gId === $roleAdminHrId) {
                $rowClass = 'row-group-adm-hr';
            }
            
            $htmlRows .= '<tr data-user-id="' . h((string)$userID) . '" data-group-id="' . h((string)$gId) . '" data-group-kod="' . h($gKod) . '" data-flag="' . h((string)$f_flag) . '" data-extra-count="' . h((string)$extraCount) . '" data-extra-roles="' . h(implode(', ', $extraRoles)) . '" class="' . h($rowClass) . '">';
            $htmlRows .= '<td class="col-bil"></td>';
            $htmlRows .= '<td class="col-nama"><span class="truncate-1line">' . h($nama) . ' (' . h($stafID) . ')</span></td>';
            $htmlRows .= '<td class="col-jabatan"><span class="truncate-1line">' . h($jabatan) . '</span></td>';
            $htmlRows .= '<td class="col-jawatan"><span class="truncate-1line">' . h($jawatan) . '</span></td>';
            $htmlRows .= '<td class="col-group">';
            $htmlRows .= '<span class="badge ' . h($badgeClass) . '">' . h($gName) . '</span>';
            $title = !empty($extraRoles) ? implode(', ', $extraRoles) : (__('userList_role_none') ?? 'Tiada peranan tambahan.');
            $htmlRows .= '<i class="ri-information-line ms-1 text-muted extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" title="' . h($title) . '"></i>';
            $htmlRows .= '</td>';
            $htmlRows .= '<td class="col-akses">';
            if ($f_flag == 1) {
              $htmlRows .= '<span class="badge bg-success">' . h(__('userList_access_granted') ?? 'Dibenarkan') . '</span>';
            } else {
              $htmlRows .= '<span class="badge bg-danger">' . h(__('userList_access_blocked') ?? 'Disekat') . '</span>';
            }
            $htmlRows .= '</td>';
            $htmlRows .= '<td class="col-actions">';
            if ($isADM_SA) {
              $htmlRows .= '<button type="button" class="btn btn-outline-primary btn-sm icon-btn btn-edit-group" ';
              $htmlRows .= 'title="Ubah Kumpulan" ';
              $htmlRows .= 'data-user-id="' . h((string)$userID) . '" ';
              $htmlRows .= 'data-nama="' . h($nama) . '" ';
              $htmlRows .= 'data-stafid="' . h($stafID) . '" ';
              $htmlRows .= 'data-nopekerja="' . h($f_nopekerja) . '" ';
              $htmlRows .= 'data-avatar-url="' . h($avatarUrl) . '" ';
              $htmlRows .= 'data-jabatan="' . h($jabatan) . '" ';
              $htmlRows .= 'data-group-id="' . h((string)$gId) . '" ';
              $htmlRows .= 'data-group-kod="' . h($gKod) . '" ';
              $htmlRows .= 'data-group-name="' . h($gName) . '" ';
              $htmlRows .= 'data-flag="' . h((string)$f_flag) . '">';
              $htmlRows .= '<i class="ri-pencil-line"></i>';
              $htmlRows .= '</button>';
              $htmlRows .= '<button type="button" class="btn btn-outline-danger btn-sm icon-btn btn-delete-user ms-1" ';
              $htmlRows .= 'title="Padam Pengguna" ';
              $htmlRows .= 'data-user-id="' . h((string)$userID) . '" ';
              $htmlRows .= 'data-nama="' . h($nama) . '" ';
              $htmlRows .= 'data-stafid="' . h($stafID) . '">';
              $htmlRows .= '<i class="ri-delete-bin-line"></i>';
              $htmlRows .= '</button>';
            }
            $htmlRows .= '</td>';
            $htmlRows .= '</tr>';
            // Also build structured row for JSON consumers
            $rows[] = [
                'f_userID' => $userID,
                'f_nama' => $nama,
                'f_stafID' => $stafID,
                'f_namajabatan' => $jabatan,
                'f_jawatan' => $jawatan,
                'f_groupID' => $gId,
                'f_groupKod' => $gKod,
                'f_groupName' => $gName,
                'extra_roles' => $extraRoles,
                'extra_roles_count' => $extraCount,
                'f_flag' => $f_flag,
                'f_nopekerja' => $f_nopekerja,
                'avatarUrl' => $avatarUrl
            ];
        }
    }
    
    // Clean output buffer before sending JSON
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    
    // Set header
    header('Content-Type: application/json; charset=utf-8');
    
    // Output JSON
    echo json_encode([
        'error' => false,
        'html' => $htmlRows,
        'rows' => $rows,
        'count' => count($senaraiUser)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Throwable $e) {
    // Clean all output
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    error_log("[user-list-rows] Fatal: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => true,
        'message' => 'Ralat server. Sila hubungi pentadbir sistem.'
    ], JSON_UNESCAPED_UNICODE);
}
