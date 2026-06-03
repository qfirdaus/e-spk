<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../includes/functions-page.php'; 
require_once __DIR__ . '/../../../../../controllers/ListPermohonaniCaresController.php';

$controller = new ListPermohonaniCaresController(); 
$list_pengesahanPelajar = $controller->getAllPengesahanPelajar();

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['f_stafID'];

include __DIR__ . '/../list-pengesahan-pelajar.php';