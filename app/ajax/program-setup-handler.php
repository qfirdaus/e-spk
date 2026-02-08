<?php
// ajax/program-setup-handler.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();
require_once __DIR__ . '/../setting/constants/prestasi_constants.php';

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// CSRF validation
$csrf = $_POST['csrf_token'] ?? '';
if (empty($csrf) || $csrf !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Authorization check
$currentUserGroupId = (int)($_SESSION['user']['f_groupID'] ?? $profile['f_groupID'] ?? $_SESSION['f_groupID'] ?? 0);
$allowedRoleIds = [PRESTASI_ROLE_ID_ADM_SA, PRESTASI_ROLE_ID_ADM_HR];
if (!in_array($currentUserGroupId, $allowedRoleIds, true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Load controller
require_once __DIR__ . '/../controllers/ProgramSetupController.php';
$controller = new ProgramSetupController();

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'saveProgram':
            $data = [
                'f_program ID' => $_POST['f_programID'] ?? null,
                'f_programName' => trim($_POST['f_programName'] ?? ''),
                'f_tahun' => (int)($_POST['f_tahun'] ?? date('Y')),
                'f_description' => trim($_POST['f_description'] ?? '')
            ];
            
            if (empty($data['f_programName'])) {
                throw new Exception('Nama program diperlukan');
            }
            
            $result = $controller->saveProgram($data);
            echo json_encode($result);
            break;
            
        case 'saveTeras':
            $data = [
                'f_terasID' => $_POST['f_terasID'] ?? null,
                'f_kodTeras' => strtoupper(trim($_POST['f_kodTeras'] ?? '')),
                'f_namaTeras' => trim($_POST['f_namaTeras'] ?? ''),
                'f_jenis' => $_POST['f_jenis'] ?? 'Teras',
                'f_description' => trim($_POST['f_description'] ?? ''),
                'f_programID' => $_POST['f_programID'] ?? null,
                'f_ownerStafID' => !empty($_POST['f_ownerStafID']) ? $_POST['f_ownerStafID'] : null
            ];
            
            if (empty($data['f_kodTeras']) || empty($data['f_namaTeras'])) {
                throw new Exception('Kod dan nama teras diperlukan');
            }
            
            $result = $controller->saveTeras($data);
            echo json_encode($result);
            break;
            
        case 'deleteTeras':
            $terasID = (int)($_POST['terasID'] ?? 0);
            if ($terasID <= 0) {
                throw new Exception('ID teras tidak sah');
            }
            
            $result = $controller->deleteTeras($terasID);
            echo json_encode($result);
            break;
            
        case 'updateSettings':
            $data = [];
            if (isset($_POST['reporting_cycle'])) {
                $data['reporting_cycle'] = $_POST['reporting_cycle'];
            }
            if (isset($_POST['status_thresholds'])) {
                $data['status_thresholds'] = $_POST['status_thresholds'];
            }
            
            $result = $controller->updateSettings($data);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Throwable $e) {
    error_log('[program-setup-handler] Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
