<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../../controllers/KetuaProgramController.php';

    $input = $_POST; 
    $user_id = $_SESSION['f_stafID'] ?? null;

    if (!$user_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Sesi tamat atau ID staf tidak sah. Sila log masuk semula.'
        ]);
        exit;
    }

    $controller = new KetuaProgramController();
    $result = $controller->deleteHeadProgramme($user_id, $input);

    echo json_encode($result);

} catch (\Throwable $e) {

    http_response_code(500); // Set status kod ralat pelayan
    echo json_encode([
        'status' => 'error',
        'message' => 'Ralat PHP: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}


