<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

$user_id = $_SESSION['f_stafID'];
$input = json_decode(file_get_contents('php://input'), true);

// kalau JSON invalid
if (!is_array($input)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'INVALID JSON INPUT'
    ]);
    exit;
}

if (!$user_id || !is_array($input)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

$dir = __DIR__ . '/../temp';

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$file = $dir . '/' . $user_id . '_draft.json';

$draft = [
    'draft_initialized' => true,
    'updated_at' => date('Y-m-d H:i:s'),

    // DATA CORE
    'dataStudent' => $input['dataStudent'] ?? [],
    'penglibatan'    => $input['penglibatan'] ?? [],
    'jawatan'    => $input['jawatan'] ?? [],
    'anugerah'    => $input['anugerah'] ?? [],
    'perakuan'    => $input['perakuan'] ?? []
];

file_put_contents(
    $file,
    json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode([
    'status' => 'success',
    'updated_at' => $draft['updated_at']
]);

