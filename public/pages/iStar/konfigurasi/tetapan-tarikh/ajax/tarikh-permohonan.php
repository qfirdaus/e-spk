<?php

require_once __DIR__ . '/../../../../../controllers/iStarConfigController.php';

session_start();

$controller = new iStarConfigController();

$action = $_GET['action'] ?? '';

header('Content-Type: application/json; charset=utf-8');

if ($action === 'updateDateAppDraft') {
    $controller->updateDateAppDraft();
    exit;
}

if ($action === 'addDateAppDraft') {
    $controller->addDateAppDraft();
    exit;
}

if ($action === 'deleteDateAppDraft') {
    $controller->deleteDateAppDraft();
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid action'
]);
exit;