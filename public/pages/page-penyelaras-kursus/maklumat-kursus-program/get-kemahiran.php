<?php
require_once __DIR__ . '/../../../controllers/MaklumatKursusPKController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([]);
    exit;
}

$kursusId = isset($_POST['kursus_id']) ? (int)$_POST['kursus_id'] : 0;

if ($kursusId > 0) {
    $controller = new MaklumatKursusPKController();
    $senaraiKemahiran = $controller->getSenaraiKemahiran($kursusId);
    echo json_encode($senaraiKemahiran);
} else {
    echo json_encode([]);
}