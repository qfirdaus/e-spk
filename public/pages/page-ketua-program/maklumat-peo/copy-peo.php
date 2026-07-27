<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../../controllers/MaklumatPEOController.php';

$input = $_POST; 

$user_id = $_SESSION['f_stafID'] ?? $_SESSION['id_staf'] ?? null;

if (!$user_id || empty($input['txtsesi']) || empty($input['selectSesiModal'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Permintaan tidak sah atau maklumat sesi tidak lengkap.'
    ]);
    exit;
}

$controller = new MaklumatPEOController();
$result = $controller->copyPEO($user_id, $input);

echo json_encode($result);