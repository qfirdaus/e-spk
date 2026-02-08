<?php
// ajax/my-projects-handler.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/MyProjectsController.php';

header('Content-Type: application/json');

// CSRF Check
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Token tidak sah']);
    exit;
}

$action = $_POST['action'] ?? '';
$controller = new MyProjectsController();

try {
    switch ($action) {
        case 'getProject':
            $projectID = (int)($_POST['projectID'] ?? 0);
            $data = $controller->getProjectDetails($projectID);
            if ($data) {
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Projek tidak dijumpai atau anda tidak mempunyai akses']);
            }
            break;

        case 'updateDates':
            $data = [
                'f_projectID' => $_POST['f_projectID'] ?? null,
                'activities' => json_decode($_POST['activities'] ?? '[]', true)
            ];
            $result = $controller->updateActivityDates($data);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Tindakan tidak sah']);
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ralat sistem: ' . $e->getMessage()]);
}
