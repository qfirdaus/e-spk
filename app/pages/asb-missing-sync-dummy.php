<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

function h($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function normalize_nokp(?string $nokp): string
{
    return preg_replace('/\D+/', '', (string)$nokp) ?? '';
}

function nokp_variants(string $normalized): array
{
    $v = [];
    $n = trim($normalized);
    if ($n === '') {
        return $v;
    }

    $v[] = $n;
    if (strlen($n) === 12) {
        $v[] = substr($n, 0, 6) . '-' . substr($n, 6, 2) . '-' . substr($n, 8, 4);
        $v[] = substr($n, 0, 6) . ' ' . substr($n, 6, 2) . ' ' . substr($n, 8, 4);
        $v[] = substr($n, 0, 6) . '/' . substr($n, 6, 2) . '/' . substr($n, 8, 4);
        $v[] = substr($n, 0, 6) . '.' . substr($n, 6, 2) . '.' . substr($n, 8, 4);
    }

    return array_values(array_unique($v));
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = (string)$_SESSION['csrf_token'];

$limit = 2000;
$result = null;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postedCsrf = (string)($_POST['csrf_token'] ?? '');
    if ($postedCsrf === '' || !hash_equals($csrf, $postedCsrf)) {
        $errors[] = 'Token keselamatan tidak sah.';
    } else {
        $limitInput = (int)($_POST['limit'] ?? 2000);
        $limit = max(1, min(50000, $limitInput));

        try {
            $pdoMysql = Database::getInstance('mysql')->getConnection();
            $pdoStudent = Database::getInstance('sybase_student')->getConnection();

            $stmtList = $pdoMysql->prepare("
                SELECT asbMissingID, nokp, matrik, nama, status
                FROM tbl_m_asb_missing
                WHERE status = 'BELUM_UPDATE'
                  AND COALESCE(TRIM(nokp), '') <> ''
                ORDER BY asbMissingID ASC
                LIMIT :lim
            ");
            $stmtList->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmtList->execute();
            $rows = $stmtList->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $stmtUpdate = $pdoMysql->prepare("
                UPDATE tbl_m_asb_missing
                SET matrik = :matrik,
                    nama = :nama,
                    updated_at = NOW()
                WHERE asbMissingID = :id
            ");

            $summary = [
                'selected' => count($rows),
                'updated' => 0,
                'not_found' => 0,
                'invalid_nokp' => 0,
                'update_error' => 0,
                'items' => [],
            ];

            foreach ($rows as $row) {
                $id = (int)($row['asbMissingID'] ?? 0);
                $rawNokp = (string)($row['nokp'] ?? '');
                $nokpNorm = normalize_nokp($rawNokp);
                $item = [
                    'id' => $id,
                    'nokp' => $rawNokp,
                    'status' => '',
                    'matrik' => null,
                    'nama' => null,
                    'message' => '',
                ];

                if ($nokpNorm === '') {
                    $summary['invalid_nokp']++;
                    $item['status'] = 'SKIP';
                    $item['message'] = 'NOKP tidak sah.';
                    $summary['items'][] = $item;
                    continue;
                }

                $variants = nokp_variants($nokpNorm);
                if (empty($variants)) {
                    $summary['invalid_nokp']++;
                    $item['status'] = 'SKIP';
                    $item['message'] = 'NOKP tidak sah.';
                    $summary['items'][] = $item;
                    continue;
                }

                $orParts = [];
                $bind = [];
                foreach ($variants as $idx => $val) {
                    $ph = ':nokp' . $idx;
                    $orParts[] = "LTRIM(RTRIM(nokp)) = {$ph}";
                    $bind[$ph] = $val;
                }

                $sqlStudent = "
                    SELECT TOP 1
                        matrik,
                        nama
                    FROM v210
                    WHERE statuskategori = 'AKTIF'
                      AND (" . implode(' OR ', $orParts) . ")
                ";
                $stmtStudent = $pdoStudent->prepare($sqlStudent);
                foreach ($bind as $ph => $val) {
                    $stmtStudent->bindValue($ph, $val, PDO::PARAM_STR);
                }
                $stmtStudent->execute();
                $student = $stmtStudent->fetch(PDO::FETCH_ASSOC) ?: null;

                if (!$student) {
                    $summary['not_found']++;
                    $item['status'] = 'TIADA_PADANAN';
                    $item['message'] = 'Pelajar aktif tidak dijumpai dalam v210.';
                    $summary['items'][] = $item;
                    continue;
                }

                $matrik = trim((string)($student['matrik'] ?? ''));
                $nama = trim((string)($student['nama'] ?? ''));
                if ($matrik === '') {
                    $summary['not_found']++;
                    $item['status'] = 'TIADA_MATRIK';
                    $item['message'] = 'Rekod Sybase tiada matrik.';
                    $summary['items'][] = $item;
                    continue;
                }

                try {
                    $stmtUpdate->execute([
                        ':matrik' => $matrik,
                        ':nama' => $nama !== '' ? $nama : null,
                        ':id' => $id,
                    ]);

                    if ($stmtUpdate->rowCount() > 0) {
                        $summary['updated']++;
                        $item['status'] = 'SELESAI';
                        $item['matrik'] = $matrik;
                        $item['nama'] = $nama;
                        $item['message'] = 'Berjaya kemaskini.';
                    } else {
                        $summary['update_error']++;
                        $item['status'] = 'GAGAL';
                        $item['message'] = 'Tiada perubahan pada rekod MySQL.';
                    }
                } catch (Throwable $e) {
                    $summary['update_error']++;
                    $item['status'] = 'GAGAL';
                    $item['message'] = 'Ralat update: ' . $e->getMessage();
                }

                $summary['items'][] = $item;
            }

            $result = $summary;
        } catch (Throwable $e) {
            $errors[] = 'Ralat semasa sync: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="<?= h($_SESSION['lang'] ?? 'ms') ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php
    $NEED_DATERANGE = false;
    $NEED_VECTORMAP = false;
    $NEED_DATATABLES = false;
    $NEED_SELECT2 = false;
    include __DIR__ . '/../includes/head.php';
  ?>
</head>
<body id="body-layout"
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>"
  data-layout="vertical"
  data-sidebar-size="default"
  class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
              <h4 class="page-title">Dummy Sync ASB Missing (Sybase Student -> MySQL)</h4>
            </div>
          </div>
        </div>

        <?php if ($errors): ?>
          <div class="row">
            <div class="col-12">
              <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?>
                  <div><?= h($err) ?></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">Jalankan Sync</h5>
              </div>
              <div class="card-body">
                <form method="post">
                  <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                  <div class="mb-3">
                    <label for="limit" class="form-label">Bilangan maksimum rekod (1 - 50000)</label>
                    <input type="number" class="form-control" id="limit" name="limit" min="1" max="50000" value="<?= h((string)$limit) ?>">
                    <small class="text-muted">Hanya rekod status BELUM_UPDATE dengan NOKP tidak kosong akan diproses.</small>
                  </div>
                  <button type="submit" class="btn btn-primary">
                    Run Sync Sekarang
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <?php if (is_array($result)): ?>
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Ringkasan</h5>
                </div>
                <div class="card-body">
                  <div class="row g-3">
                    <div class="col-md-2"><strong>Dipilih:</strong> <?= h((string)$result['selected']) ?></div>
                    <div class="col-md-2"><strong>Updated:</strong> <?= h((string)$result['updated']) ?></div>
                    <div class="col-md-2"><strong>Tiada Padanan:</strong> <?= h((string)$result['not_found']) ?></div>
                    <div class="col-md-2"><strong>NOKP Tak Sah:</strong> <?= h((string)$result['invalid_nokp']) ?></div>
                    <div class="col-md-2"><strong>Ralat Update:</strong> <?= h((string)$result['update_error']) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">Butiran Proses</h5>
                </div>
                <div class="card-body table-responsive">
                  <table class="table table-sm table-bordered align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>NOKP</th>
                        <th>Status</th>
                        <th>Matrik</th>
                        <th>Nama</th>
                        <th>Mesej</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach (($result['items'] ?? []) as $it): ?>
                        <tr>
                          <td><?= h((string)($it['id'] ?? '')) ?></td>
                          <td><?= h((string)($it['nokp'] ?? '')) ?></td>
                          <td><?= h((string)($it['status'] ?? '')) ?></td>
                          <td><?= h((string)($it['matrik'] ?? '')) ?></td>
                          <td><?= h((string)($it['nama'] ?? '')) ?></td>
                          <td><?= h((string)($it['message'] ?? '')) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
</body>
</html>
