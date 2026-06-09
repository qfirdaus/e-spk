<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../includes/functions-page.php'; 
require_once __DIR__ . '/../../../../../controllers/iStarConfigController.php';

$controller = new iStarConfigController(); 
$list_dateConfig = $controller->getAllDateConfig();

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['f_stafID'];

include __DIR__ . '/../list-tetapan-tarikh.php';