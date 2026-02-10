<?php
// ajax/group-create.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_login();
require_once __DIR__ . '/_helpers.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $headers = function_exists('getallheaders') ? getallheaders() : [];
  $csrf = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? '';
  if (!$csrf || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    http_response_code(400);
    echo json_encode(['error'=>true,'message'=>'CSRF token tidak sah'], JSON_UNESCAPED_UNICODE); exit;
  }

  if (!checkRateLimit('group_create', 10, 60)) {
    http_response_code(429);
    echo json_encode(['error'=>true,'message'=>'Terlalu banyak permintaan'], JSON_UNESCAPED_UNICODE); exit;
  }

  $db = Database::getInstance('mysql')->getConnection();
  if (!hasGroupManagePermission($db)) {
    http_response_code(403);
    echo json_encode(['error'=>true,'message'=>'Anda tidak mempunyai kebenaran untuk menambah kumpulan'], JSON_UNESCAPED_UNICODE); exit;
  }

  $json = json_decode(file_get_contents('php://input'), true) ?: [];
  $kod = trim((string)($json['groupKod'] ?? ''));
  $nama = trim((string)($json['groupName'] ?? ''));
  $priority = (int)($json['priority'] ?? 0);
  $mod = (int)($json['mod'] ?? 0);
  $color = trim((string)($json['color'] ?? ''));
  // allow modul/menu selections (arrays or CSV)
  $modulAccessArr = [];
  if (!empty($json['modulAccess'])) {
    if (is_array($json['modulAccess'])) $modulAccessArr = array_values(array_filter($json['modulAccess'], fn($v)=>trim((string)$v) !== ''));
    else $modulAccessArr = array_filter(array_map('trim', explode(',', (string)$json['modulAccess'])), fn($v)=>$v !== '');
  }
  $menuAccessArr = [];
  if (!empty($json['menuAccess'])) {
    if (is_array($json['menuAccess'])) $menuAccessArr = array_values(array_filter($json['menuAccess'], fn($v)=>trim((string)$v) !== ''));
    else $menuAccessArr = array_filter(array_map('trim', explode(',', (string)$json['menuAccess'])), fn($v)=>$v !== '');
  }
  $modulAccessCsv = $modulAccessArr ? implode(',', $modulAccessArr) : '';
  $menuAccessCsv = $menuAccessArr ? implode(',', $menuAccessArr) : '';

  if ($kod === '' || $nama === '') {
    http_response_code(422);
    echo json_encode(['error'=>true,'message'=>'Kod dan Nama Kumpulan diperlukan'], JSON_UNESCAPED_UNICODE); exit;
  }

  // Insert
  $stmt = $db->prepare("INSERT INTO tbl_m_group (f_groupKod, f_groupName, f_modulAccess, f_menuAccess, f_priority, f_mod, f_color, f_insertdt) VALUES (:kod, :nama, :modulAccess, :menuAccess, :prio, :mod, :color, NOW())");
  try {
    $stmt->execute([':kod'=>$kod, ':nama'=>$nama, ':modulAccess'=>$modulAccessCsv, ':menuAccess'=>$menuAccessCsv, ':prio'=>$priority, ':mod'=>$mod, ':color'=>$color]);
  } catch (PDOException $e) {
    if ($e->getCode() === '23000') {
      http_response_code(409);
      echo json_encode(['error'=>true,'message'=>'Kod Kumpulan sudah wujud'], JSON_UNESCAPED_UNICODE); exit;
    }
    throw $e;
  }

  $newId = (int)$db->lastInsertId();

  // Invalidate group-related caches (group list/style maps/access summaries)
  clearGroupUiCaches($newId);

  echo json_encode(['error'=>false,'group'=>['id'=>$newId,'kod'=>$kod,'nama'=>$nama]], JSON_UNESCAPED_UNICODE);

  // Audit: GROUP_CREATE (non-blocking)
  try {
    if (!function_exists('audit_event')) {
      $auditHelperPath = __DIR__ . '/../setting/helper/audit_helper.php';
      if (file_exists($auditHelperPath)) {
        require_once $auditHelperPath;
      }
    }
    if (function_exists('audit_event')) {
      $actorLabel = null;
      if (function_exists('audit_format_actor_label')) {
        $namaUser = $profile['f_nama'] ?? null;
        $noStaf = $profile['f_stafID'] ?? ($_SESSION['f_stafID'] ?? null);
        $actorLabel = audit_format_actor_label($namaUser, $noStaf);
      }
      $msg = function_exists('audit_format_message')
        ? audit_format_message('Group created', $actorLabel)
        : 'Group created';
      audit_event([
        'action' => 'GROUP_CREATE',
        'message' => $msg,
        'target_type' => 'group',
        'target_id' => (string)$newId,
        'target_label' => $nama !== '' ? $nama : $kod,
        'meta' => [
          'group_id' => $newId,
          'group_code' => $kod,
          'group_name' => $nama,
        ],
      ]);
    }
  } catch (Throwable $e) {
    // non-blocking: ignore audit failures
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>true,'message'=>'Ralat server: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
