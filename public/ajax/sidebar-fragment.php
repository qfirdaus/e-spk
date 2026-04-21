<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();
require_once __DIR__ . '/_helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $requestedFile = isset($_GET['currentFile']) ? trim((string)$_GET['currentFile']) : '';
    $requestedFile = str_replace('\\', '/', $requestedFile);
    $requestedFile = strtok($requestedFile, '?') ?: $requestedFile;
    $requestedFile = ltrim($requestedFile, '/');
    if ($requestedFile !== '' && !preg_match('/^[a-zA-Z0-9_\-.\/]+$/', $requestedFile)) {
        $requestedFile = '';
    }
    if ($requestedFile === '') {
        $refererPath = str_replace('\\', '/', (string)parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH));
        $pagesPos = stripos($refererPath, 'pages/');
        $requestedFile = $pagesPos !== false ? substr($refererPath, $pagesPos) : basename($refererPath);
    }

    $currentFile = $requestedFile !== '' ? $requestedFile : basename($_SERVER['PHP_SELF'] ?? '');
    $currentPagePath = str_starts_with($currentFile, 'pages/') ? $currentFile : ('pages/' . strtolower($currentFile));
    $pdo = Database::getInstance('mysql')->getConnection();
    $ui = buildAccessUiPayload($pdo, [
        'activeGroupId' => (int)($_SESSION['group_active_id'] ?? 0),
        'currentFile' => $currentFile,
        'currentPagePath' => $currentFile !== '' ? $currentPagePath : '',
        'currentPageAllowed' => true,
        'includeSidebar' => true,
    ]);

    echo json_encode([
        'error' => false,
        'ui' => $ui,
        'html' => $ui['sidebar']['html'] ?? null,
        'activeGroupId' => $ui['activeGroupId'] ?? 0,
        'group_name' => $ui['role']['name'] ?? '',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
