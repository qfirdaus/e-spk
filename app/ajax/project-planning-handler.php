<?php
// ajax/project-planning-handler.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

// CSRF Check
$csrf = $_POST['csrf_token'] ?? '';
if (empty($csrf) || $csrf !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

require_once __DIR__ . '/../controllers/ProjectPlanningController.php';
$controller = new ProjectPlanningController();

$action = $_POST['action'] ?? '';

try {
    switch($action) {
        case 'saveProject':
            $activities = json_decode($_POST['activities'] ?? '[]', true);
            $data = [
                'f_projectID' => $_POST['f_projectID'] ?? null,
                'f_terasID' => $_POST['f_terasID'] ?? null,
                'f_projectName' => $_POST['f_projectName'] ?? '',
                'f_ownerStafID' => $_POST['f_ownerStafID'] ?? '',
                'f_startDate' => $_POST['f_startDate'] ?? null,
                'f_endDate' => $_POST['f_endDate'] ?? null,
                'activities' => $activities
            ];
            
            // Basic Validation
            if (empty($data['f_projectName'])) throw new Exception("Nama projek diperlukan");
            if (empty($data['f_terasID'])) throw new Exception("Sila pilih Teras Strategik");
            if (empty($activities)) throw new Exception("Sila tambah sekurang-kurangnya satu aktiviti");
            
            // Validate Weightage Sum
            $totalWeight = array_sum(array_column($activities, 'weight'));
            if ($totalWeight != 100) {
                throw new Exception("Jumlah pemberat mesti 100%. (Jumlah Semasa: $totalWeight%)");
            }

            echo json_encode($controller->saveProject($data));
            break;

        case 'getProject':
            $id = (int)($_POST['projectID'] ?? 0);
            $data = $controller->getProjectDetails($id);
            if ($data) {
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Projek tidak dijumpai']);
            }
            break;

        default:
            throw new Exception("Invalid Action");
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
