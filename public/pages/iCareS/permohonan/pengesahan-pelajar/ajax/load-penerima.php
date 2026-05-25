<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../../includes/functions-page.php'; 
require_once __DIR__ . '/../../../../../controllers/PengesahanPelajarController.php';

$pengesahanPelajarController = new PengesahanPelajarController();
$lookupAll = $pengesahanPelajarController->getAllLookup();

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['f_stafID'];

$file = __DIR__ . '/../temp/' . $user_id . '_penerima.json';
$data = [];

if (file_exists($file)) {

    $json = file_get_contents($file);

    $decoded = json_decode($json, true);

    if (is_array($decoded)) {
        $data = $decoded['data'] ?? [];
    }
}

include __DIR__ . '/../f-penerima.php';
?>