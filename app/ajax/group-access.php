<?php
// ======================================
// ✅ AJAX: Group Access (modules + menus + flags)
// Pulangkan senarai modul (ikut f_modulAccess kumpulan) dan menu di bawahnya,
// termasuk status ON/OFF dari tbl_m_menu.f_flag. Jika f_menuAccess ditetapkan,
// hanya menu yang tersenarai akan dipulangkan.
// ======================================
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();
require_once __DIR__ . '/_helpers.php';

header('Content-Type: application/json; charset=UTF-8');

try {
  $gid = isset($_GET['groupID']) ? (int)$_GET['groupID'] : 0;
  if ($gid <= 0) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'groupID tidak sah'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  
  // Rate limiting: max 30 requests per 60 seconds (read operation)
  if (!checkRateLimit('group_access', 30, 60)) {
    http_response_code(429);
    echo json_encode(['error' => true, 'message' => 'Terlalu banyak permintaan. Sila cuba lagi selepas beberapa saat.'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  
  // Check cache (30 min TTL)
  $cacheKey = 'group_access_' . $gid . '_' . ($_SESSION['lang'] ?? 'ms');
  $cached = GroupDataCache::get($cacheKey, 1800);
  if ($cached !== null) {
    header('X-Cache: HIT');
    echo json_encode($cached, JSON_UNESCAPED_UNICODE);
    exit;
  }

  $pdo  = Database::getInstance('mysql')->getConnection();
  // Only support 'ms' and 'en' (zh/ta columns removed)
  $lang = $_SESSION['lang'] ?? 'ms';
  $lang = in_array($lang, ['ms','en'], true) ? $lang : 'ms';

  // 1) Dapatkan akses kumpulan (modul & menu)
  $stmt = $pdo->prepare("SELECT f_modulAccess, f_menuAccess FROM tbl_m_group WHERE f_groupID = :id LIMIT 1");
  $stmt->execute([':id' => $gid]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo json_encode(['modules' => [], 'totals' => ['modulCt' => 0, 'menuCt' => 0]], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $modCsv = trim((string)$row['f_modulAccess']);
  $menuCsv = trim((string)$row['f_menuAccess']);

  // CSV → array int
  $modIds = array_values(array_filter(array_map(function($v){
    $v = trim($v);
    return ctype_digit($v) ? (int)$v : null;
  }, explode(',', $modCsv)), fn($v) => $v !== null));

  $menuFilterIds = array_values(array_filter(array_map(function($v){
    $v = trim($v);
    return ctype_digit($v) ? (int)$v : null;
  }, explode(',', $menuCsv)), fn($v) => $v !== null));

  if (!$modIds) {
    echo json_encode(['modules' => [], 'totals' => ['modulCt' => 0, 'menuCt' => 0]], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 2) Bina COALESCE nama modul ikut kolum yang wujud
  $colsMod = $pdo->query("SHOW COLUMNS FROM tbl_m_modul")->fetchAll(PDO::FETCH_COLUMN, 0);
  $candsMod = array_filter([
    "f_modulName_{$lang}",
    "f_modulName_ms", "f_modulName_en",
  ], fn($c) => in_array($c, $colsMod, true));
  $nameExprMod = $candsMod ? ('COALESCE('.implode(',', $candsMod).', CONCAT("Modul ", f_modulID))')
                           : 'CONCAT("Modul ", f_modulID)';

  // 3) Ambil nama modul
  $modulesMap = [];
  foreach ($modIds as $mid) {
    $modulesMap[$mid] = ['id' => $mid, 'nama' => 'Modul '.$mid, 'menus' => []];
  }

  $in = implode(',', array_fill(0, count($modIds), '?'));
  $sqlMod = "SELECT f_modulID, {$nameExprMod} AS nama
             FROM tbl_m_modul
             WHERE f_modulID IN ($in)
             ORDER BY COALESCE(f_order, f_modulID), f_modulID";
  $stmMod = $pdo->prepare($sqlMod);
  $stmMod->execute($modIds);
  while ($r = $stmMod->fetch(PDO::FETCH_ASSOC)) {
    $mid = (int)$r['f_modulID'];
    if (isset($modulesMap[$mid])) {
      $modulesMap[$mid]['nama'] = (string)$r['nama'];
    }
  }

  // 4) Bina COALESCE nama menu ikut kolum yang wujud
  $colsMenu = $pdo->query("SHOW COLUMNS FROM tbl_m_menu")->fetchAll(PDO::FETCH_COLUMN, 0);
  $candsMenu = array_filter([
    "f_menuName_{$lang}",
    "f_menuName_ms", "f_menuName_en",
  ], fn($c) => in_array($c, $colsMenu, true));
  $nameExprMenu = $candsMenu ? ('COALESCE('.implode(',', $candsMenu).', CONCAT("Menu ", f_menuID))')
                             : 'CONCAT("Menu ", f_menuID)';

  // 5) Tarik menu di bawah modul; jika ada f_menuAccess → tapis guna CSV
  $menuCt = 0;
  if ($modIds) {
    $place = implode(',', array_fill(0, count($modIds), '?'));

    if (!empty($menuFilterIds)) {
      // Guna FIND_IN_SET dengan CSV original supaya kekal pantas & ringkas
      $sqlMenu = "
        SELECT f_menuID, f_modulID, {$nameExprMenu} AS nama, f_path, f_flag
        FROM tbl_m_menu
        WHERE f_modulID IN ($place)
          AND FIND_IN_SET(f_menuID, ?) > 0
        ORDER BY f_modulID, COALESCE(f_order, 99999), f_menuID
      ";
      $params = array_merge($modIds, [implode(',', $menuFilterIds)]);
    } else {
      $sqlMenu = "
        SELECT f_menuID, f_modulID, {$nameExprMenu} AS nama, f_path, f_flag
        FROM tbl_m_menu
        WHERE f_modulID IN ($place)
        ORDER BY f_modulID, COALESCE(f_order, 99999), f_menuID
      ";
      $params = $modIds;
    }

    $stmMenu = $pdo->prepare($sqlMenu);
    $stmMenu->execute($params);

    while ($m = $stmMenu->fetch(PDO::FETCH_ASSOC)) {
      $mid = (int)$m['f_modulID'];
      if (!isset($modulesMap[$mid])) continue;
      $modulesMap[$mid]['menus'][] = [
        'id'   => (int)$m['f_menuID'],
        'nama' => (string)$m['nama'],
        'path' => $m['f_path'] !== null ? (string)$m['f_path'] : null,
        'flag' => (int)$m['f_flag'] === 1 ? 1 : 0,
      ];
      $menuCt++;
    }
  }

  // 6) Susun ikut urutan ID modul asal
  $modules = array_map(fn($id) => $modulesMap[$id], $modIds);

  $result = [
    'modules' => $modules,
    'totals'  => ['modulCt' => count($modules), 'menuCt' => $menuCt]
  ];
  
  // Store in cache
  GroupDataCache::set($cacheKey, $result);
  header('X-Cache: MISS');
  echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => true, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
