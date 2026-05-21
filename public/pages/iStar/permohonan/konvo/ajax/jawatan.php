<?php

require_once __DIR__ . '/../../../../../controllers/PenglibatanController.php';

session_start();

$controller = new PenglibatanController();

$action = $_GET['action'] ?? '';

header('Content-Type: application/json; charset=utf-8');

if ($action === 'updateJawatanDraft') {
    $controller->updateJawatanDraft();
    exit;
}

if ($action === 'addDraft') {
    $controller->addDraft();
    exit;
}

if ($action === 'deleteDraft') {
    $controller->deleteDraft();
    exit;
}

if ($action === 'syncIstad') {
    $controller->syncIstad();
    exit;
}

if ($action === 'updateDokumen') {
    $controller->updateDokumen();
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action'
]);
exit;