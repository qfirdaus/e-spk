<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../../controllers/iStarConfigController.php';

$input = $_POST; 

$user_id = $_SESSION['f_stafID'] ?? null;

if (!$user_id || empty($input)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request atau tiada data dihantar'
    ]);
    exit;
}

$controller = new iStarConfigController();
$result = $controller->submitApplicationDate($user_id, $input);

echo json_encode($result);