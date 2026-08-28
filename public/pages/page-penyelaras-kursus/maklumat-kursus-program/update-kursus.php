<?php
require_once __DIR__ . '/../../../controllers/MaklumatKursusPKController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak sah.']);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$stafID = $_SESSION['f_stafID'] ?? '';

if (empty($stafID)) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi telah tamat. Sila log masuk semula.']);
    exit;
}

try {
    // Panggil Controller
    $controller = new MaklumatKursusPKController();
    $result = $controller->saveUpdateKursus($stafID, $_POST);
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ralat Pelayan: ' . $e->getMessage()]);
}
?>