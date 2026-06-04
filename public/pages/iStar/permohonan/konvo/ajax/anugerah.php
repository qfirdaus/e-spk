<?php

require_once __DIR__ . '/../../../../../controllers/PenglibatanController.php';

session_start();

$controller = new PenglibatanController();

$action = $_GET['action'] ?? '';

header('Content-Type: application/json; charset=utf-8');

if ($action === 'updateAnugerahDraft') {
    $controller->updateAnugerahDraft();
    exit;
}

if ($action === 'addAnugerahDraft') {
    $controller->addAnugerahDraft();
    exit;
}

if ($action === 'deleteAnugerahDraft') {
    $controller->deleteAnugerahDraft();
    exit;
}

if ($action === 'updateDokumenAnugerah') {
    $controller->updateDokumenAnugerah();
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action'
]);
exit;