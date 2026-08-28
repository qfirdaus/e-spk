<?php
require_once __DIR__ . '/../../../controllers/MaklumatKursusPKController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([]);
    exit;
}

$kursusId = isset($_POST['kursus_id']) ? (int)$_POST['kursus_id'] : 0;
$term     = $_POST['term'] ?? '';

if ($kursusId > 0 && $term !== '') {
    $controller = new MaklumatKursusPKController();
    $senaraiRujukan = $controller->getRujukanList($kursusId, $term);
    echo json_encode($senaraiRujukan);
} else {
    echo json_encode([]);
}