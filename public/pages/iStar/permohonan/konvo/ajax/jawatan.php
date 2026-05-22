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

if ($action === 'addJawatanDraft') {
    $controller->addJawatanDraft();
    exit;
}

if ($action === 'deleteJawatanDraft') {
    $controller->deleteJawatanDraft();
    exit;
}

if ($action === 'syncIstadJawatan') {
    $controller->syncIstadJawatan();
    exit;
}

if ($action === 'updateDokumenJawatan') {
    $controller->updateDokumenJawatan();
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action'
]);
exit;