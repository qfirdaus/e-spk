<?php
// ajax/user-import-student.php
declare(strict_types=1);

// Aggressive output buffering
while (ob_get_level() > 0) {
  @ob_end_clean();
}

// Set custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
  error_log("[user-import-student] PHP Error: $errstr in $errfile:$errline");
  return true;
}, E_ALL);

// Set exception handler
set_exception_handler(function($e) {
  error_log('[user-import-student] Uncaught Exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
  while (ob_get_level() > 0) {
    @ob_end_clean();
  }
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error'=>true, 'message'=>'Ralat server. Sila hubungi pentadbir sistem.'], JSON_UNESCAPED_UNICODE);
  exit;
});

require_once __DIR__ . '/../includes/init.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

// Helper functions
function json_ok($data = []) {
  while (ob_get_level() > 0) {
    @ob_end_clean();
  }
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(array_merge(['error' => false], $data), JSON_UNESCAPED_UNICODE);
  exit;
}

function json_fail($message, $code = 400) {
  while (ob_get_level() > 0) {
    @ob_end_clean();
  }
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error' => true, 'message' => $message], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  require_once __DIR__ . '/../controllers/UserListController.php';
  
  $controller = new UserListController();
  
  // Call manual import method
  $result = $controller->importStudentsFromSybase();
  
  if ($result['success']) {
    json_ok([
      'message' => $result['message'],
      'updated' => $result['inserted'] ?? 0,
      'skipped' => $result['skipped'] ?? 0,
      'errors' => $result['errors'] ?? 0,
      'total' => $result['total'] ?? 0
    ]);
  } else {
    json_fail($result['message'] ?? 'Gagal import data dari Sybase.', 500);
  }
  
} catch (Throwable $e) {
  error_log('[user-import-student] Exception: ' . $e->getMessage());
  error_log('[user-import-student] Trace: ' . $e->getTraceAsString());
  json_fail('Ralat sistem semasa import data.', 500);
}

