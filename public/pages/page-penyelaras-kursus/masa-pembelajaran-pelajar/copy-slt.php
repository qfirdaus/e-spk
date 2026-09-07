<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../../controllers/MaklumatSLTController.php';

$input = $_POST; 
$user_id = $_SESSION['f_stafID'] ?? null;

if (!$user_id || empty($input['txtcourseid'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesi telah tamat atau ralat pada ID Kursus.'
    ]);
    exit;
}

$controller = new MaklumatSLTController(true);
$result = $controller->copySLT($user_id, $input);

echo json_encode($result);