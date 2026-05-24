<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../../includes/functions-page.php'; 
// require_once __DIR__ . '/../../../../../controllers/PengeshanPelajarController.php';

// $controller = new PengeshanPelajarController();

// $lookup = $controller->getLookupPenerima(); 
include __DIR__ . '/../f-penerima.php';

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

session_start();
$user_id = $_SESSION['f_stafID'];

$file = __DIR__ . '/../temp/' . $user_id . '_penerima.json';
$data = [];

if (file_exists($file)) {

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $data = [];
    }
}
?>