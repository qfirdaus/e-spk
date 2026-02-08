<?php
// pages/project-monitoring.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/ProjectMonitoringController.php';
$controller = new ProjectMonitoringController();
$filterTeras = $_GET['filter_teras'] ?? '';
$controller->loadDashboardData($filterTeras);
$terasList = $controller->getActiveTerasList();

$stats = $controller->stats;
$projects = $controller->projects;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="<?= h($controller->lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <style>
        .neo-stats-row>[class*="col-"]{display:flex}
        .neo-stat{
            --neo-accent:108,117,125;
            position:relative;overflow:hidden;width:100%;
            border-radius:16px;padding:16px 16px 14px;
            background:linear-gradient(180deg,rgba(var(--neo-base),.60),rgba(var(--neo-base),.36));
            border:1px solid rgba(var(--neo-ink),.08);
            box-shadow:0 8px 24px rgba(0,0,0,.08);
            backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
            transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease
        }
        .neo-stat:hover{transform:translateY(-3px);box-shadow:0 18px 36px rgba(0,0,0,.12)}
        .neo-stat::before{
            content:"";position:absolute;inset:-2px;pointer-events:none;
            background:
                radial-gradient(1100px 240px at -10% -15%,rgba(var(--neo-accent),.18),transparent 55%),
                radial-gradient(900px 220px at 110% -5%,rgba(var(--neo-accent),.10),transparent 60%)
        }
        [data-bs-theme="dark"] .neo-stat{
            border-color:rgba(255,255,255,.07);
            box-shadow:0 10px 28px rgba(0,0,0,.5)
        }
        .neo-head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.5rem}
        .neo-title{font-size:.84rem;letter-spacing:.2px;margin:0;opacity:.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .neo-icon{width:44px;height:44px;border-radius:12px;flex:0 0 44px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;background:radial-gradient(200% 140% at 30% 20%,rgba(var(--neo-accent),.35),rgba(var(--neo-accent),.06));border:1px solid rgba(var(--neo-accent),.35);box-shadow:inset 0 0 0 1px rgba(255,255,255,.04),0 6px 16px rgba(var(--neo-accent),.28)}
        .neo-value{margin:0;font-weight:800;font-size:1.75rem;line-height:1.1;text-align:right;font-variant-numeric:tabular-nums lining-nums;font-feature-settings:"tnum" 1,"lnum" 1}
        .neo-sub{margin:0;font-size:.78rem;opacity:.72;text-align:right}
        .neo-progress{height:6px;background:rgba(var(--neo-ink),.08);border-radius:999px;overflow:hidden;margin:.25rem 0 0 auto;width:100%;max-width:220px}
        .neo-bar{height:100%;background:linear-gradient(90deg,rgba(var(--neo-accent),.88),rgba(var(--neo-accent),.55))}
        .neo-primary{--neo-accent:13,110,253}.neo-success{--neo-accent:25,135,84}.neo-warning{--neo-accent:255,193,7}.neo-info{--neo-accent:13,202,240}.neo-danger{--neo-accent:220,53,69}.neo-secondary{--neo-accent:108,117,125}
        :root{--neo-base:255,255,255;--neo-ink:0,0,0}
        [data-bs-theme="dark"]{--neo-base:25,27,31;--neo-ink:255,255,255}
    </style>
</head>
<body data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>" data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>" data-layout="vertical">

<div class="wrapper">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">
                
                <!-- Page Title -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="page-title mb-0" style="font-weight: 700; letter-spacing: -0.5px;">
                                <?= h(__('project_monitoring_title')) ?>
                            </h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><?= h(__('dashboard_breadcrumb')) ?></a></li>
                                    <li class="breadcrumb-item active"><?= h(__('project_monitoring_breadcrumb')) ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Stats -->
                <div class="row g-3 neo-stats-row mb-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
                    <div class="col">
                        <div class="neo-stat neo-primary">
                            <div class="neo-head">
                                <p class="neo-title"><?= h(__('project_monitoring_total_projects')) ?></p>
                                <div class="neo-icon"><i class="ri-folder-chart-line"></i></div>
                            </div>
                            <p class="neo-value"><?= $stats['total'] ?></p>
                            <div class="neo-progress"><div class="neo-bar" style="width: 100%"></div></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="neo-stat neo-success">
                            <div class="neo-head">
                                <p class="neo-title"><?= h(__('project_monitoring_on_track')) ?></p>
                                <div class="neo-icon"><i class="ri-check-double-line"></i></div>
                            </div>
                            <p class="neo-value"><?= $stats['on_track'] ?></p>
                            <div class="neo-progress"><div class="neo-bar" style="width: <?= $stats['total'] ? ($stats['on_track']/$stats['total']*100) : 0 ?>%"></div></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="neo-stat neo-warning">
                            <div class="neo-head">
                                <p class="neo-title"><?= h(__('project_monitoring_delayed')) ?></p>
                                <div class="neo-icon"><i class="ri-time-line"></i></div>
                            </div>
                            <p class="neo-value"><?= $stats['delayed'] ?></p>
                            <div class="neo-progress"><div class="neo-bar" style="width: <?= $stats['total'] ? ($stats['delayed']/$stats['total']*100) : 0 ?>%"></div></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="neo-stat neo-danger">
                            <div class="neo-head">
                                <p class="neo-title"><?= h(__('project_monitoring_critical')) ?></p>
                                <div class="neo-icon"><i class="ri-alarm-warning-line"></i></div>
                            </div>
                            <p class="neo-value"><?= $stats['critical'] ?></p>
                            <div class="neo-progress"><div class="neo-bar" style="width: <?= $stats['total'] ? ($stats['critical']/$stats['total']*100) : 0 ?>%"></div></div>
                        </div>
                    </div>
                </div>

                <!-- Projects Table -->
                <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-heart-pulse"></i> <?= h(__('project_monitoring_overall_status')) ?></h5>
                        <!-- Filter Teras -->
                        <form method="GET" action="" class="d-flex align-items-center gap-2">
                            <label for="filter_teras" class="form-label mb-0 text-muted font-13"><?= h(__('project_monitoring_filter_teras_label')) ?></label>
                            <select name="filter_teras" id="filter_teras" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 280px; width: 320px;">
                                <option value=""><?= h(__('project_monitoring_all')) ?></option>
                                <?php foreach($terasList as $t): ?>
                                    <?php
                                        $kod = trim((string)($t['f_kodTeras'] ?? ''));
                                        $nama = trim((string)($t['f_namaTeras'] ?? ''));
                                        $label = $kod !== '' ? ($nama !== '' ? $kod . ' - ' . $nama : $kod) : $nama;
                                    ?>
                                    <option value="<?= h($kod) ?>" <?= $filterTeras === $kod ? 'selected' : '' ?>><?= h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="monitorTable">
                                <thead class="table-light">
                                            <tr>
                                                <th><?= __('teras_code') ?></th>
                                                <th><?= __('project_name') ?></th>
                                                <th><?= h(__('project_monitoring_owner')) ?></th>
                                                <th style="width: 200px;"><?= h(__('project_monitoring_progress')) ?></th>
                                                <th><?= h(__('project_monitoring_status')) ?></th>
                                                <th><?= h(__('project_monitoring_countdown')) ?></th>
                                                <th><?= __('actions') ?></th>
                                            </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($projects)): ?>
                                        <tr><td colspan="7" class="text-center text-muted"><?= h(__('project_monitoring_no_data')) ?></td></tr>
                                    <?php else: ?>
                                        <?php foreach($projects as $p): ?>
                                            <tr>
                                                <td><?= getTerasKodBadgeHtml($p['f_kodTeras'], 'badge') ?></td>
                                                <td>
                                                    <strong><?= h($p['f_projectName']) ?></strong>
                                                </td>
                                                <td><?= h($p['ownerName'] ?? '-') ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 6px;">
                                                            <div class="progress-bar bg-<?= $p['status_color'] ?>" 
                                                                 style="width: <?= $p['progress'] ?>%"></div>
                                                        </div>
                                                        <span class="fw-bold"><?= $p['progress'] ?>%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-subtle-<?= $p['status_color'] ?> text-<?= $p['status_color'] ?> border border-<?= $p['status_color'] ?>">
                                                        <?= h($p['status_label']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $p['countdown']['badge_color'] ?> d-inline-flex align-items-center gap-1">
                                                        <i class="<?= $p['countdown']['icon'] ?>"></i>
                                                        <?= h($p['countdown']['label']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="project-details.php?id=<?= $p['f_projectID'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <?= h(__('project_monitoring_btn_detail')) ?> <i class="bi bi-arrow-right"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
<script>
    // Simple Client-Side Search
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const val = this.value.toLowerCase();
        const rows = document.querySelectorAll('#monitorTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(val) ? '' : 'none';
        });
    });
</script>
</body>
</html>
