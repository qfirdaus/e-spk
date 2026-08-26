<?php
require_once __DIR__ . '/../../../controllers/MaklumatCLOController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak sah.']);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$stafID = $_SESSION['f_stafID'] ?? '';

$controller = new MaklumatCLOController();

$response = $controller->saveCLO($stafID, $_POST);
echo json_encode($response);