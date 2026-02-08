<?php
// pages/sybase-structure.php
// =======================================================
// Sybase Structure Inspector (UI)
// - Guna SybaseStructureController yg support owners, refresh cache, sp_helpindex
// =======================================================
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/SybaseStructureController.php';
$controller = new SybaseStructureController();

$version = time();
$lang    = $controller->lang ?? ($_SESSION['lang'] ?? 'ms');
$profile = $controller->profile ?? [];

// Params
$obj     = isset($_GET['obj']) ? trim((string)$_GET['obj']) : '';
$owner   = isset($_GET['owner']) ? trim((string)$_GET['owner']) : '';
$refresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

$hadError = null;
try {
  // Auto connect ke Sybase aktif + owners + objects (ikut owner) + optional sp_help/teks/index
  $controller->run($obj ?: null, $owner ?: null, $refresh);
} catch (Throwable $e) {
  $hadError = $e->getMessage();
}

// Helper render jadual kecil
function render_table(array $rows): void {
  if (!$rows) return;
  echo "<div class='table-responsive' style='border:1px solid #e9ecef;border-radius:8px;margin:12px 0;'>";
  echo "<table class='table table-sm table-striped mb-0'>";
  echo "<thead class='table-light'><tr>";
  foreach (array_keys($rows[0]) as $col) echo "<th>".h($col)."</th>";
  echo "</tr></thead><tbody>";
  foreach ($rows as $r) {
    echo "<tr>";
    foreach ($r as $v) echo "<td>".h((string)$v)."</td>";
    echo "</tr>";
  }
  echo "</tbody></table></div>";
}
?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">

<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <script>const BASE_URL = "<?= base_url() ?>";</script>

  <!-- Select2 -->
  <link href="<?= base_url('assets/vendor/select2/css/select2.min.css') ?>?v=<?= $version ?>" rel="stylesheet">

  <style>
    .card-ujian { border-radius: 8px; border: 1px solid #dee2e6; }
    .card-ujian .card-body { padding: 1rem; }
    .ujian-header { font-weight: bold; font-size: 15px; margin-bottom: 0.5rem; }
    .ujian-sub { font-size: 13px; color: #6c757d; }
    pre.ddl { background:#f7f7f9;border:1px solid #e1e1e8;padding:12px;border-radius:6px; }

    /* Select2 jumbo align dengan butang */
    .select2-container .select2-selection--single {
      height: 56px; padding: 0 16px; font-size: 1.1rem; border-radius: .5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 56px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 56px; }
  </style>
</head>

<body
  id="body-layout"
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? '') ?>"
  data-menu-color="<?= h($_SESSION['theme.menu'] ?? '') ?>"
  data-layout="vertical"
  data-sidebar-size="default"
  class="loading">

  <div class="wrapper">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">

          <!-- ✅ Tajuk & Breadcrumb -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="page-title">🧪 <?= __('ujian_db') ?> — Sybase Structure</h4>
                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('pages/dashboard.php') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Sybase Structure</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          <!-- 🔎 Papar base Sybase aktif (hint) -->
          <div class="alert alert-secondary mb-3">
            Aktif Sybase base: <code><?= h(defined('SYBASE_ACTIVE_BASE') ? SYBASE_ACTIVE_BASE : 'sybase_ehrmdb') ?></code>
          </div>

          <!-- 🔍 Form (Owner + Objek + Butang) -->
          <div class="card card-ujian mb-3">
            <div class="card-body">
              <form class="row gy-2 gx-2 align-items-end" method="get">
                <div class="col-12 mb-2">
                  <div class="ujian-sub">
                    Pilih <b>Owner</b> (schema) → senarai objek akan ditapis dan di-cache (5 min). Tekan <b>Refresh Senarai</b> untuk muat semula.
                  </div>
                </div>

                <!-- Owner dropdown -->
                <div class="col-md-3">
                  <label class="form-label">Owner (Schema)</label>
                  <select name="owner" class="form-select select2">
                    <option value="">— Semua Owner —</option>
                    <?php foreach (($controller->owners ?? []) as $o): ?>
                      <option value="<?= h($o) ?>" <?= ($owner !== '' && strcasecmp($owner, $o) === 0) ? 'selected' : '' ?>>
                        <?= h($o) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Objek dropdown -->
                <div class="col-md-7">
                  <label class="form-label">Pilih Table / View</label>
                  <select name="obj" class="form-select select2" required>
                    <option value="" disabled <?= $obj===''?'selected':''; ?>>— Pilih objek —</option>
                    <?php
                      $currentType = null;
                      foreach ($controller->objects as $o) {
                        if ($currentType !== $o['type']) {
                          if ($currentType !== null) echo "</optgroup>";
                          $currentType = $o['type'];
                          echo "<optgroup label='".h($currentType)."'>";
                        }
                        $val = $o['full']; // owner.name
                        $label = $o['owner'].'.'.$o['name'];
                        $sel = ($obj === $val) ? 'selected' : '';
                        echo "<option value='".h($val)."' $sel>".h($label)."</option>";
                      }
                      if ($currentType !== null) echo "</optgroup>";
                    ?>
                  </select>
                </div>

                <!-- Actions -->
                <div class="col-md-2 d-flex gap-2">
                  <button class="btn btn-primary w-100" type="submit">Paparkan</button>
                  <!-- Refresh senarai objek (clear cache) -->
                  <button class="btn btn-outline-secondary" type="submit" name="refresh" value="1" title="Refresh Senarai">
                    <i class="ri-refresh-line"></i>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <?php if ($hadError): ?>
            <div class="alert alert-danger">❌ Ralat: <?= h($hadError) ?></div>

          <?php elseif (!$controller->pdo_sybase): ?>
            <div class="alert alert-danger">❌ Tiada sambungan Sybase. <?= h($controller->sybaseError ?: 'Semak konfigurasi Database singleton & base key aktif.') ?></div>

          <?php elseif ($obj === ''): ?>
            <div class="alert alert-info">Pilih owner (jika perlu), pilih objek dan tekan <b>Paparkan</b>.</div>

          <?php else: ?>
            <div class="alert alert-success">
              ✅ Sambungan Sybase tersedia — base: <code><?= h($controller->sybaseBaseKey) ?></code>
            </div>

            <!-- sp_help -->
            <div class="card card-ujian mb-3">
              <div class="card-header bg-light"><b>sp_help '<?= h($obj) ?>'</b></div>
              <div class="card-body">
                <?php
                if (!$controller->helpSets) {
                  echo "<div class='text-muted'><i>Tiada output (objek mungkin tidak wujud / tiada akses).</i></div>";
                } else {
                  foreach ($controller->helpSets as $rows) render_table($rows);
                }
                ?>
              </div>
            </div>

            <!-- sp_helpindex -->
            <div class="card card-ujian mb-3">
              <div class="card-header bg-light"><b>sp_helpindex '<?= h($obj) ?>'</b></div>
              <div class="card-body">
                <?php
                  if (!empty($controller->helpIndex)) {
                    render_table($controller->helpIndex);
                  } else {
                    echo "<div class='text-muted'><i>Tiada index (mungkin objek ialah view atau jadual tanpa index).</i></div>";
                  }
                ?>
              </div>
            </div>

            <!-- sp_helptext -->
            <div class="card card-ujian mb-3">
              <div class="card-header bg-light"><b>sp_helptext '<?= h($obj) ?>'</b></div>
              <div class="card-body">
                <?php if ($controller->helpText !== ''): ?>
                  <pre class="ddl mb-0"><?= h($controller->helpText) ?></pre>
                <?php else: ?>
                  <div class="text-muted"><i>Tiada definisi (kemungkinan objek ialah jadual, bukan view/proc).</i></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/script.php'; ?>
  <script src="<?= base_url('assets/vendor/select2/js/select2.full.min.js') ?>?v=<?= $version ?>" defer></script>

  <script defer>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof $.fn.select2 !== 'function') {
        console.error('Select2 belum attach.');
        return;
      }
      $('.select2').select2({ width: '100%', placeholder: 'Pilih…', allowClear: true });

      // Match height dengan butang submit (visual polish)
      function syncSelect2Height() {
        const $btn = document.querySelector('.btn.btn-primary.w-100');
        if (!$btn) return;
        const h = Math.ceil($btn.getBoundingClientRect().height);
        document.querySelectorAll('.select2-selection--single').forEach(el => {
          el.style.height = h + 'px';
          const rendered = el.querySelector('.select2-selection__rendered');
          const arrow = el.querySelector('.select2-selection__arrow');
          if (rendered) rendered.style.lineHeight = h + 'px';
          if (arrow) arrow.style.height = h + 'px';
        });
      }
      setTimeout(syncSelect2Height, 50);
      window.addEventListener('resize', syncSelect2Height);
    });
  </script>
</body>
</html>
