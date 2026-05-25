<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['f_stafID'] ?? null;

if (!$user_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired'
    ]);
    exit;
}

$file = __DIR__ . '/../temp/' . $user_id . '_draft.json';

if (!file_exists($file)) {
    echo json_encode([
        'draft_initialized' => false,
        'dataStudent' => new stdClass(),
        'penerima' => new stdClass(),
        'perakuan' => new stdClass()
    ]);
    exit;
}

echo file_get_contents($file);