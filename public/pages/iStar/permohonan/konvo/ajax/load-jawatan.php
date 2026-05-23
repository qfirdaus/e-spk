<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../../../includes/init.php';
require_once __DIR__ . '/../../../../../includes/functions-page.php';

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

$jawatanData = $penglibatanController->getAllJawatanDisandang();
$lookupAll = $penglibatanController->getAllLookup();

include __DIR__ . '/../f-jawatan-disandang.php';
