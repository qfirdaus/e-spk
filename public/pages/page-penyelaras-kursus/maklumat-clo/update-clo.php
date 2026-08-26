<?php
require_once __DIR__ . '/../../../controllers/MaklumatCLOController.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak sah.']);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$stafID = $_SESSION['id_staf'] ?? $_SESSION['f_stafID'] ?? '';

$controller = new MaklumatCLOController();

$action = $_POST['action'] ?? 'update'; // Semak jika ada flag 'delete'

if ($action === 'delete') {
    $idClo = (int)($_POST['id_clo'] ?? 0);
    $response = $controller->deleteCLO($stafID, $idClo);
} else {
    $response = $controller->updateCLO($stafID, $_POST);
}

echo json_encode($response);