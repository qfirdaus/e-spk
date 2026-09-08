<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../controllers/LaporanKursusKPController.php'; 

$id_kursus = $_POST['id_kursus'] ?? '';

if (empty($id_kursus)) {
    echo json_encode(['status' => 'error', 'message' => 'ID Kursus tidak sah.']);
    exit;
}

$controller = new LaporanKursusKPController();
$data = $controller->getExcelReportData($id_kursus); 

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Rekod tidak dijumpai.']);
}