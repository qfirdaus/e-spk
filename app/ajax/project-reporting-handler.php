<?php
// ajax/project-reporting-handler.php
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

require_once __DIR__ . '/../controllers/ProjectReportingController.php';
$controller = new ProjectReportingController();

$action = $_POST['action'] ?? '';

try {
    switch($action) {
        case 'getReport':
            $projectID = (int)$_POST['projectID'];
            $month = (int)$_POST['month'];
            $year = (int)$_POST['year'];
            
            $data = $controller->getReportData($projectID, $month, $year);
            if (isset($data['error'])) {
                echo json_encode(['success' => false, 'message' => $data['error']]);
            } else {
                echo json_encode(['success' => true, 'data' => $data]);
            }
            break;

        case 'saveReport':
            $data = [
                'month' => $_POST['month'],
                'year' => $_POST['year'],
                'reports' => json_decode($_POST['reports'] ?? '[]', true)
            ];
            
            $result = $controller->saveReport($data);
            echo json_encode($result);
            break;

        default:
            throw new Exception("Invalid Action");
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
