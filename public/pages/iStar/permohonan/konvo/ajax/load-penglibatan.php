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

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $host = $_SERVER['HTTP_HOST'];

        // hardcode root project kalau perlu
        $base = '/e-hepa/public/';

        return 'http://' . $host . $base . ltrim($path, '/');
    }
}

require_once __DIR__ . '/../../../../../controllers/PenglibatanController.php'; 

$penglibatanController = new PenglibatanController();

$penglibatanData = $penglibatanController->getAllPenglibatan();
$lookupAll = $penglibatanController->getAllLookup();

include __DIR__ . '/../f-penglibatan-program.php';