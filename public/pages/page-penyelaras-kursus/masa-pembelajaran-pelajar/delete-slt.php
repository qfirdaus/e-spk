<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../../controllers/MaklumatSLTController.php';

$input = $_POST; 
$user_id = $_SESSION['f_stafID'] ?? null;

if (!$user_id || empty($input['sltid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tamat atau ID tidak sah.']);
    exit;
}

$controller = new MaklumatSLTController(true); // Panggil sebagai API
$result = $controller->deleteSLT($user_id, $input);

echo json_encode($result);