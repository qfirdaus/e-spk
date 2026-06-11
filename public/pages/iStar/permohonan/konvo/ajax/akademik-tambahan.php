<?php

require_once __DIR__ . '/../../../../../controllers/PenglibatanController.php';

session_start();

$controller = new PenglibatanController();

$action = $_GET['action'] ?? '';

header('Content-Type: application/json; charset=utf-8');

if ($action === 'updateDekanDraft') {
    $controller->updateDekanDraft();
    exit;
}

if ($action === 'addDekanDraft') {
    $controller->addDekanDraft();
    exit;
}

if ($action === 'deleteDekanDraft') {
    $controller->deleteDekanDraft();
    exit;
}

if ($action === 'updateDokumenDekan') {
    $controller->updateDokumenDekan();
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action'
]);
exit;