<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../includes/functions-page.php'; 
require_once __DIR__ . '/../../../../../controllers/MaklumatPLOController.php';

$controller = new MaklumatPLOController(); 
$list_dataPLO = $controller->getAllDataPLO();
$lookupAll = $controller->getAllLookup();

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['f_stafID'];

include __DIR__ . '/../list-plo.php';