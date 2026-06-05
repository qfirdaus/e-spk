<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../includes/functions-page.php'; 
require_once __DIR__ . '/../../../../../controllers/ListPermohonaniStarController.php';

$controller = new ListPermohonaniStarController(); 
$list_pingatGraduan = $controller->getAllPingatGraduan();

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['f_stafID'];

include __DIR__ . '/../list-pingat-graduan.php';