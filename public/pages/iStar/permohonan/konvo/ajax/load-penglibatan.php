<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tr')) {
    function tr($key, $default = '') {
        return $default;
    }
}

require_once __DIR__ . '/../../../../../controllers/PenglibatanController.php'; 

$penglibatanController = new PenglibatanController();

$penglibatanData = $penglibatanController->getAllPenglibatan();
$lookupPencapaian = $penglibatanController->getLookupPencapaian();
$lookupPeringkat = $penglibatanController->getLookupPeringkat();
$lookupWakil = $penglibatanController->getLookupWakil();

include __DIR__ . '/../f-penglibatan-program.php';