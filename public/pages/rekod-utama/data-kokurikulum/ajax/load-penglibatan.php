<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tr')) {
    function tr($key, $default = '') {
        static $lines = null;
        if ($lines === null) {
            $lang = strtolower((string)($_SESSION['lang'] ?? $_SESSION['user.lang'] ?? 'ms'));
            if (!in_array($lang, ['ms', 'en'], true)) {
                $lang = 'ms';
            }
            $file = __DIR__ . '/../../../../../lang/' . $lang . '.php';
            $loaded = is_file($file) ? require $file : [];
            $lines = is_array($loaded) ? $loaded : [];
        }
        $value = $lines[$key] ?? null;
        return ($value === null || $value === '') ? $default : (string)$value;
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
