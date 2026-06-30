<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../controllers/RekodPeribadiController.php';

$input = $_POST; 

if (isset($_FILES['dokumen_oku'])) {
    $input['dokumen_oku_file'] = $_FILES['dokumen_oku'];
}

$user_id = $_SESSION['f_stafID'] ?? null;

if (!$user_id || (empty($input) && !isset($_FILES['dokumen_oku']))) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request atau tiada data dihantar'
    ]);
    exit;
}

$controller = new RekodPeribadiController();
$result = $controller->submitKesihatan($user_id, $input);

echo json_encode($result);