<?php

require_once __DIR__ . '/../../../../../controllers/PenglibatanController.php';

session_start();

$controller = new PenglibatanController();
$action = $_GET['action'] ?? '';

header('Content-Type: application/json; charset=utf-8');

if ($action === 'addDraft') {
    $controller->addAnugerahDraft();
    exit;
}

if ($action === 'deleteDraft') {
    $controller->deleteAnugerahDraft();
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action',
]);
exit;