<?php
// pages/profile_diag.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// ---- PRINT BASIC CONTEXT (atas sekali, supaya nampak jelas) ----
header('Content-Type: text/html; charset=utf-8');
echo "<pre style='background:#111;color:#0f0;padding:10px;border-radius:6px'>";
echo "[HOST]       " . ($_SERVER['HTTP_HOST'] ?? '-') . "\n";
echo "[URI]        " . ($_SERVER['REQUEST_URI'] ?? '-') . "\n";
echo "[SID]        " . session_id() . "\n";
echo "[COOKIE PATH] " . ini_get('session.cookie_path') . "\n";
$params = session_get_cookie_params();
echo "[COOKIE PARAMS] " . json_encode($params, JSON_PRETTY_PRINT) . "\n\n";

echo "[SESSION]\n";
echo "  f_stafID      = " . ($_SESSION['f_stafID'] ?? '(null)') . "\n";
echo "  f_nopekerja   = " . ($_SESSION['f_nopekerja'] ?? '(null)') . "\n";
echo "  auth.nopekerja= " . ($_SESSION['auth.nopekerja'] ?? '(null)') . "\n\n";

// ---- AMBIL INPUT (override untuk test) ----
$stafID = trim((string)($_GET['staf'] ?? ($_SESSION['f_stafID'] ?? '')));
$nopek  = trim((string)($_GET['nopek'] ?? ($_SESSION['auth.nopekerja'] ?? $_SESSION['f_nopekerja'] ?? '')));

echo "[EFFECTIVE INPUT]\n";
echo "  stafID = {$stafID}\n";
echo "  nopek  = {$nopek}\n\n";

// ---- DB CONNECT (guna helper yang confirm jalan masa debug) ----
$pdo = Database::pdoMysql();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Show database name to confirm DSN betul
$dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
echo "[DATABASE] {$dbName}\n\n";

// ---- QUERY (tiada JOIN, tiada filter status) ----
$sqlBase = "
  SELECT u.f_userID, u.f_stafID, u.f_nopekerja, u.f_nama, u.f_nickname,
         u.f_email, u.f_handphone, u.f_jawatan, u.f_kumpjawatan, u.f_namajabatan
  FROM tbl_m_user u
  WHERE %s
  LIMIT 1
";

$row = null;
if ($stafID !== '') {
  $stmt = $pdo->prepare(sprintf($sqlBase, "u.f_stafID = :id"));
  $stmt->execute([':id' => $stafID]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  echo "[QUERY BY f_stafID] " . ($row ? "FOUND\n" : "NOT FOUND\n");
}
if (!$row && $nopek !== '') {
  $stmt = $pdo->prepare(sprintf($sqlBase, "u.f_nopekerja = :np"));
  $stmt->execute([':np' => $nopek]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  echo "[QUERY BY f_nopekerja] " . ($row ? "FOUND\n" : "NOT FOUND\n");
}

echo "\n[ROW]\n";
var_dump($row);

echo "</pre>";

// ---- SIMPLE HTML PREVIEW (kalau jumpa) ----
$avatarUrl = null;
if ($row && !empty($row['f_nopekerja'])) {
  $avatarUrl = "https://esmartcard.upnm.edu.my/img/staf/{$row['f_nopekerja']}.jpg";
}
?>
<!doctype html>
<html>
  <head><meta charset="utf-8"><title>Profile DIAG</title></head>
  <body style="font-family:system-ui,Arial,sans-serif">
  <?php if ($row): ?>
    <h2>Preview</h2>
    <img src="<?= htmlspecialchars($avatarUrl ?: 'assets/images/no-image.jpg') ?>" alt="avatar" width="120"
         onerror="this.onerror=null;this.src='assets/images/no-image.jpg'">
    <div>Nama: <?= htmlspecialchars($row['f_nama'] ?: $row['f_nickname'] ?: 'Pengguna') ?></div>
    <div>No. Staf: <?= htmlspecialchars($row['f_stafID']) ?></div>
    <div>No. Pekerja: <?= htmlspecialchars($row['f_nopekerja']) ?></div>
    <div>Jawatan/Gred: <?= htmlspecialchars($row['f_jawatan'].' • '.$row['f_kumpjawatan']) ?></div>
    <div>Jabatan: <?= htmlspecialchars($row['f_namajabatan']) ?></div>
    <div>Emel: <?= htmlspecialchars($row['f_email']) ?></div>
  <?php else: ?>
    <h2 style="color:#c00">TIADA PROFIL</h2>
    <p>Cuba: <a href="?staf=0530-09">?staf=0530-09</a> / <a href="?nopek=530">?nopek=530</a></p>
  <?php endif; ?>
  </body>
</html>
