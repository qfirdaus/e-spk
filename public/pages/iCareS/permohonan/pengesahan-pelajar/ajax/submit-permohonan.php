<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../../controllers/PengesahanPelajarController.php';

$input = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['f_stafID'] ?? null;

if (!$user_id || !$input) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

$controller = new PengesahanPelajarController();

$result = $controller->submitPermohonan($user_id, $input);

echo json_encode($result);