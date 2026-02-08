<?php
// pages/project-details.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

$projectID = (int)($_GET['id'] ?? 0);
if ($projectID === 0) {
    header('Location: project-monitoring.php');
    exit;
}

require_once __DIR__ . '/../controllers/ProjectMonitoringController.php';
$controller = new ProjectMonitoringController();
$viewMode = $_GET['view'] ?? 'monthly';
$data = $controller->getProjectMatrix($projectID, $viewMode);

if (!$data) {
    die(h(__('project_details_not_found') ?: 'Projek tidak dijumpai.'));
}

$project = $data['project'];
$activities = $data['activities'];
$dateCols = $data['columns'];

// Filter out any empty/invalid activity rows to avoid rendering stray blank table rows
$activities = array_values(array_filter($activities, function($a){
    if (!is_array($a)) return false;
    $name = trim((string)($a['f_namaAktiviti'] ?? ''));
    // Also reject rows that have no weight and no history
    $hasWeight = isset($a['f_weightage']) && $a['f_weightage'] !== '';
    $hasHistory = !empty($a['history']) && is_array($a['history']);
    return $name !== '' || $hasWeight || $hasHistory;
}));

// Fetch Progress Data for Header Summary
$progData = $controller->calculateProjectProgress($projectID, $project['f_endDate'] ?? null);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Helper to determine color class based on percentage tiers
function getCellColor($percentStr) {
    if ($percentStr === '' || $percentStr === '-') return ''; 
    $p = (int)$percentStr;
    if ($p == 100) return 'status-100';
    if ($p >= 80) return 'status-80';
    if ($p >= 60) return 'status-60';
    if ($p >= 40) return 'status-40';
    if ($p >= 20) return 'status-20';
    return 'status-0';
}
// Custom CSS colors to match reference exactly if needed

// Dynamic Breadcrumb Logic
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$backLink = 'dashboard.php';
$backLabel = 'Dashboard';
$parentLabel = ''; // Optional middle crumb

if (strpos($referer, 'project-monitoring.php') !== false) {
    $backLink = 'project-monitoring.php';
    $backLabel = 'Pemantauan Projek';
    $parentLabel = 'Pemantauan';
} elseif (strpos($referer, 'dashboard.php') !== false) {
    $backLink = 'dashboard.php';
    $backLabel = 'Dashboard';
}

?>
<!DOCTYPE html>
<html lang="<?= h($controller->lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <style>
        /* Rounded table container */
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
        }

        /* Sidebar and overlay handling left as global defaults */

        /* Ensure progress bar is visible on-screen (not only in print) */
        .progress {
            display: block !important;
            visibility: visible !important;
            height: 8px !important;
            background: rgba(0,0,0,0.05) !important;
        }
        .progress-bar {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            -webkit-transition: none !important;
            transition: none !important;
        }
        
        .matrix-table {
            border-radius: 0.75rem;
            overflow: hidden;
        }

        /* Layout: place legend on the right when printing; keep UI unchanged */
        .matrix-container { display: block; }
        .matrix-container .table-responsive { width: 100%; }
        .legend-floating { position: relative; display: none; }
        /* UI: ensure table uses full available width */
        .matrix-table { width: 100% !important; }
        
        .matrix-table thead th { 
            background-color: #0d47a1 !important; /* Dark Blue */
            color: #ffffff !important; 
            border: 1px solid #ffffff; 
            text-align: center; 
            vertical-align: middle; 
            font-size: 0.85rem;
        }
        .matrix-table td { border: 1px solid #ddd; text-align: center; vertical-align: middle; font-weight: bold; }
        .matrix-table .activity-col { text-align: left; background-color: #fff3e0; font-weight: 500; min-width: 250px; padding-left: 10px; }
        /* Slightly larger row padding for readability */
        .matrix-table tbody td { padding: 10px 8px !important; }
        
        /* Custom Status Colors matching reference roughly */
        /* Custom Status Colors matching reference roughly - High Specificity */
        table.matrix-table tbody td.status-100 { background-color: #00BB77 !important; color: black !important; } /* Green */
        table.matrix-table tbody td.status-80 { background-color: #2CFF05 !important; color: black !important; } /* Light Green/Info */
        table.matrix-table tbody td.status-60 { background-color: #FFF700 !important; color: black !important; } /* Yellow */
        table.matrix-table tbody td.status-40 { background-color: #ffaa00dd !important; color: black !important; } /* Orange */
        table.matrix-table tbody td.status-20 { background-color: #ff3d00 !important; color: black !important; } /* Dark Orange/Red */
        table.matrix-table tbody td.status-0  { background-color: #b52b3eff !important; color: black !important; } /* Red */

        /* Generic classes for Legend or other uses */
        .status-100 { background-color: #00BB77 !important; }
        .status-80 { background-color: #2CFF05 !important; }
        .status-60 { background-color: #FFF700 !important; }
        .status-40 { background-color: #ffaa00dd !important; }
        .status-20 { background-color: #ff3d00 !important; }
        .status-0  { background-color: #b52b3eff !important; }

        .legend-box { width: 18px; height: 18px; display: inline-block; margin-right: 6px; vertical-align: middle; }
        
        /* =====================================================
         * PRINT STYLES FOR A4 - SHRINK TO FIT
         * ===================================================== */
        @media print {
            /* Hide navigation and non-essential elements */
            .no-print,
            .topbar,
            .leftside-menu,
            .page-title-box,
            nav,
            .breadcrumb,
            button,
            .btn,
            .dropdown-menu,
            /* Hide any development overlays/banners during print */
            .dev-banner,
            .dev-overlay,
            .development-mode,
            .development-banner,
            .page-overlay,
            #global-loader,
            .loader-overlay {
                display: none !important;
            }
            
            /* Reset body and layout for print */
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .wrapper,
            .content-page,
            .content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            
            .container-fluid {
                max-width: 100% !important;
                padding: 10mm !important;
            }
            
            /* A4 page setup - shrink to fit */
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            
            /* Card styling for print */
            .card {
                border: none !important;
                box-shadow: none !important;
                page-break-inside: avoid;
                margin-bottom: 0 !important;
            }
            
            .card-body {
                padding: 0 !important;
            }
            
            /* Shrink content to fit A4 */
            /* Make matrix table more readable on print: larger font and padding */
            .matrix-table {
                width: 100%;
                font-size: 9.5pt !important;
                transform-origin: top left;
            }

            .matrix-table th,
            .matrix-table td {
                padding: 8px !important;
                font-size: 9.5pt !important;
                border: 1px solid #333 !important;
                line-height: 1.25 !important;
            }

            /* Ensure progress bars render in print */
            .progress {
                height: 8px !important;
                display: block !important;
            }
            .progress-bar {
                display: block !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .matrix-table thead th {
                background-color: #0d47a1 !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Preserve color coding in print */
            .status-100,
            table.matrix-table tbody td.status-100 {
                background-color: #00c853 !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .status-80,
            table.matrix-table tbody td.status-80 {
                background-color: #64dd17 !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .status-60,
            table.matrix-table tbody td.status-60 {
                background-color: #ffd600 !important;
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .status-40,
            table.matrix-table tbody td.status-40 {
                background-color: #ffab00 !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .status-20,
            table.matrix-table tbody td.status-20 {
                background-color: #ff3d00 !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .status-0,
            table.matrix-table tbody td.status-0 {
                background-color: #d50000 !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Activity column styling */
            .activity-col {
                background-color: #fff3e0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                font-size: 8pt !important;
                min-width: auto !important;
            }
            
            /* Project header for print */
            .print-header {
                margin-bottom: 10px !important;
                padding-bottom: 5px !important;
                border-bottom: 2px solid #0d47a1 !important;
            }
            
            .print-header h3 {
                font-size: 14pt !important;
                margin: 0 0 5px 0 !important;
            }
            
            .print-header .text-muted {
                font-size: 9pt !important;
            }
            
            /* Metrics grid for print */
            .row.g-4 {
                gap: 5px !important;
                margin-bottom: 10px !important;
            }
            
            .col-md-4 {
                font-size: 9pt !important;
                padding: 5px !important;
            }
            
            /* Responsive table wrapper */
            .table-responsive {
                overflow: visible !important;
                border-radius: 0 !important;
            }
            
            /* Auto-scale table if too wide */
            @supports (zoom: 1) {
                .matrix-table {
                    zoom: 0.7; /* Fallback zoom for better fit */
                }
            }
            
            /* PAGE BREAKS - Header on Page 1, Table on Page 2 */
            .neo-card.mb-4:first-of-type {
                page-break-after: always !important;
            }
            
            /* Allow legend/summary to appear above the table in print */
            .table-responsive {
                page-break-before: auto !important;
            }

            /* Render the floating legend as normal block element in print so it appears above the table. */
            .legend-floating {
                position: static !important;
                float: none !important;
                right: auto !important;
                top: auto !important;
                width: auto !important;
                display: block !important;
                margin-bottom: 6mm !important;
                z-index: auto !important;
            }

            /* Print: show legend at top-right (small) and place table below it */
            .matrix-container { display: block !important; }
            /* Slightly larger legend on print for readability */
            .legend-floating { display: block !important; float: right !important; width: 240px !important; margin-left: 8mm !important; margin-bottom: 6mm !important; }
            .legend-floating .card { min-width: auto !important; width: 100% !important; padding: 10px !important; }
            .legend-floating .row .col-6 small { font-size: 10pt !important; }
            /* Ensure legend boxes print larger */
            .legend-floating .legend-box { width: 20px !important; height: 20px !important; margin-right: 8px !important; }
            .matrix-container .table-responsive { clear: both !important; margin-top: 6mm !important; }
            .matrix-table { width: 100% !important; }

            /* Hide the print-only overall-summary (we'll show the in-table overall row instead) */
            .overall-summary { display: none !important; }
            
            /* Ensure cards don't break across pages */
            .card {
                page-break-inside: avoid !important;
            }
            /* Ensure the in-table overall row is visible in print so table matches UI */
            .matrix-table tbody tr.overall-row { display: table-row !important; }
        }
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
                                            <?= h(__('project_details_title') ?: 'Pelaporan Projek') ?>
                                        </h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><?= h(__('dashboard_breadcrumb') ?: 'Dashboard') ?></a></li>
                                    <?php if ($parentLabel && $parentLabel !== 'Dashboard'): ?>
                                        <li class="breadcrumb-item"><a href="<?= $backLink ?>"><?= h(__('project_details_parent') ?: $parentLabel) ?></a></li>
                                    <?php endif; ?>
                                    <li class="breadcrumb-item active"><?= h($project['f_projectName']) ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Modern Hero Card -->
                <div class="card neo-card mb-4 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <!-- Header & Title -->
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2">
                                        <?= h($project['f_kodTeras']) ?>
                                    </span>
                                    <small class="text-uppercase text-muted fw-bold" style="letter-spacing: 0.5px;"><?= h(__('project_details_plan') ?: 'Pelan Strategik') ?></small>
                                </div>
                                <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;"><?= h($project['f_projectName']) ?></h3>
                                <div class="text-muted small">
                                    <i class="bi bi-person-circle me-1"></i> <?= h($project['ownerName'] ?? 'Tiada Pemilik') ?>
                                </div>
                            </div>
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <!-- Print Button -->
                                <button class="btn btn-sm btn-primary shadow-sm no-print" type="button" onclick="window.print()">
                                    <i class="bi bi-printer me-1"></i> <?= h(__('project_details_btn_print') ?: 'Cetak') ?>
                                </button>
                                
                                <!-- Legend Trigger Button (Alpine Dropdown) -->
                                <div class="position-relative" x-data="{ open: false }">
                                    <button class="btn btn-sm btn-light border shadow-sm no-print" type="button" @click="open = !open">
                                        <i class="bi bi-palette me-1"></i> <?= h(__('project_details_btn_legend') ?: 'Petunjuk Warna') ?>
                                    </button>
                                
                                <!-- Dropdown Menu -->
                                <div x-show="open" 
                                     @click.outside="open = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 translate-y-2"
                                     class="dropdown-menu show p-3 shadow-lg border-0 position-absolute end-0 mt-2" 
                                     style="width: 320px; z-index: 1050; display: block;"
                                     style="display: none;">
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="dropdown-header text-uppercase font-12 fw-bold p-0 text-dark"><?= h(__('project_details_legend_header') ?: 'Indikator Warna Status') ?></h6>
                                        <button type="button" class="btn-close btn-sm" aria-label="Close" @click="open = false"></button>
                                    </div>
                                    
                                    <div class="row g-2">
                                        <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-0 rounded me-2"></span> <small>0 - 19%</small></div></div>
                                        <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-20 rounded me-2"></span> <small>20 - 39%</small></div></div>
                                        <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-40 rounded me-2"></span> <small>40 - 59%</small></div></div>
                                        <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-60 rounded me-2"></span> <small>60 - 79%</small></div></div>
                                        <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-80 rounded me-2"></span> <small>80 - 99%</small></div></div>
                                        <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-100 rounded me-2"></span> <small>100%</small></div></div>
                                    </div>
                                    <div class="dropdown-divider my-2"></div>
                                    <small class="text-muted d-block text-center font-10"><?= h(__('project_details_legend_note') ?: 'Warna menunjukkan tahap kemajuan.') ?></small>
                                </div>
                                </div>
                            </div>
                        </div>



                        <!-- Key Metrics Grid -->
                        <div class="row g-4 border-top pt-4">
                            <!-- Duration -->
                            <div class="col-md-4 border-end">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-light rounded-circle p-3 me-3 text-primary">
                                        <i class="bi bi-calendar-range fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-uppercase text-muted fw-bold font-11"><?= h(__('project_details_label_duration') ?: 'Tempoh Pelaksanaan') ?></small>
                                        <h5 class="mb-0 fw-bold text-dark mt-1">
                                            <?= date('d M Y', strtotime($project['f_startDate'])) ?> - <?= date('d M Y', strtotime($project['f_endDate'])) ?>
                                    <!-- Print-only overall summary placed under the color legend -->
                                    <div class="overall-summary d-none d-print-block" style="z-index:20; min-width:320px;">
                                        <table class="table table-bordered align-middle overall-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" class="bg-light text-dark small"></th>
                                                    <?php foreach ($dateCols as $dc): ?>
                                                        <th class="bg-light text-dark small text-center"><?= h($dc['label']) ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="bg-light small"></th>
                                                    <th class="bg-light small"></th>
                                                    <th class="bg-light small"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="fw-bold">
                                                    <td colspan="2" class="text-end pe-3 bg-light small"><?= h(__('project_details_overall_percent_label') ?: 'PERATUSAN STATUS KEMAJUAN KESELURUHAN (%)') ?></td>
                                                    <?php foreach ($dateCols as $dc): ?>
                                                        <?php
                                                            $colTotal = 0;
                                                            $hasData = false;
                                                            foreach($activities as $act) {
                                                                $raw = $act['history'][$dc['key']] ?? 0;
                                                                if (isset($act['history'][$dc['key']]) && $act['history'][$dc['key']] !== '-' && $act['history'][$dc['key']] !== '') {
                                                                    $hasData = true;
                                                                }

                                                                if ($raw === '-' || $raw === '') $raw = 0;
                                                                $pct = (float)$raw;
                                                                $colTotal += ((float)$act['f_weightage'] * ($pct/100));
                                                            }
                                                            $val = round($colTotal, 1);
                                                            if (!$hasData && $val == 0) {
                                                                $display = '-';
                                                                $cls = 'bg-white';
                                                            } else {
                                                                $display = $val . '%';
                                                                $cls = getCellColor((string)$val);
                                                                if ($cls == '') $cls = 'status-0';
                                                            }
                                                        ?>
                                                        <td class="<?= $cls ?> small"><?= $display ?></td>
                                                    <?php endforeach; ?>
                                                    <td class="bg-light small"></td>
                                                    <td class="bg-light small"></td>
                                                    <td class="bg-light small"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                        </h5>
                                        <small class="text-secondary"><?= round((strtotime($project['f_endDate']) - strtotime($project['f_startDate'])) / (60 * 60 * 24)) ?> <?= h(__('project_details_label_days') ?: 'Hari') ?></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Status (left aligned in UI) -->
                            <div class="col-md-4 text-start">
                                <div class="d-flex align-items-center justify-content-start">
                                    <div class="icon-box bg-light rounded-circle p-3 me-3 text-<?= $progData['color'] ?>">
                                        <i class="bi bi-flag fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-uppercase text-muted fw-bold font-11 d-block"><?= h(__('project_details_label_status') ?: 'Status Projek') ?></small>
                                        <div class="mt-1">
                                            <span class="badge bg-<?= $progData['color'] ?>-subtle text-<?= $progData['color'] ?> border border-<?= $progData['color'] ?>-subtle px-2 py-1 rounded-pill">
                                                <?= h($progData['label']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Progress: percentage left, bar right (UI) -->
                            <div class="col-md-4 text-start border-start">
                                <div class="d-flex align-items-center gap-2 w-100">
                                    <div class="icon-box bg-light rounded-circle p-3 me-2 text-<?= $progData['color'] ?>">
                                        <i class="bi bi-graph-up-arrow fs-4"></i>
                                    </div>

                                    <div class="text-start me-1">
                                        <small class="text-uppercase text-muted fw-bold font-11 d-block"><?= h(__('project_details_label_progress_current') ?: 'Kemajuan Semasa') ?></small>
                                        <span class="fw-bold text-<?= $progData['color'] ?>"><?= $progData['percent'] ?>%</span>
                                    </div>

                                    <div class="flex-fill">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-<?= $progData['color'] ?>" role="progressbar" style="width: <?= $progData['percent'] ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Matrix Table -->
                <div class="card">
                    <div class="card-body">
                                <div class="matrix-container">
                                    <div class="legend-floating d-none d-print-block" style="z-index:20;">
                                        <div class="card p-2 shadow-sm bg-white" style="min-width:220px;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="small"><?= h(__('project_details_legend_header') ?: 'Indikator Warna Status') ?></strong>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-0 rounded me-2"></span> <small>0 - 19%</small></div></div>
                                                <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-20 rounded me-2"></span> <small>20 - 39%</small></div></div>
                                                <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-40 rounded me-2"></span> <small>40 - 59%</small></div></div>
                                                <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-60 rounded me-2"></span> <small>60 - 79%</small></div></div>
                                                <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-80 rounded me-2"></span> <small>80 - 99%</small></div></div>
                                                <div class="col-6"><div class="d-flex align-items-center"><span class="legend-box status-100 rounded me-2"></span> <small>100%</small></div></div>
                                            </div>
                                            <div class="mt-2 small text-muted"><?= h(__('project_details_legend_note') ?: 'Warna menunjukkan tahap kemajuan.') ?></div>
                                        </div>
                                    </div>
                                    <!-- Print-only overall summary placed under the color legend -->
                                    <div class="overall-summary position-absolute end-0 d-none d-print-block" style="top:120px; right:0; z-index:20; min-width:320px;">
                                        <table class="table table-bordered align-middle overall-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" class="bg-light text-dark small"></th>
                                                    <?php foreach ($dateCols as $dc): ?>
                                                        <th class="bg-light text-dark small text-center"><?= h($dc['label']) ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="bg-light small"></th>
                                                    <th class="bg-light small"></th>
                                                    <th class="bg-light small"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="fw-bold">
                                                    <td colspan="2" class="text-end pe-3 bg-light small"><?= h(__('project_details_overall_percent_label') ?: 'PERATUSAN STATUS KEMAJUAN KESELURUHAN (%)') ?></td>
                                                    <?php foreach ($dateCols as $dc): ?>
                                                        <?php
                                                            $colTotal = 0;
                                                            $hasData = false;
                                                            foreach($activities as $act) {
                                                                $raw = $act['history'][$dc['key']] ?? 0;
                                                                if (isset($act['history'][$dc['key']]) && $act['history'][$dc['key']] !== '-' && $act['history'][$dc['key']] !== '') {
                                                                    $hasData = true;
                                                                }

                                                                if ($raw === '-' || $raw === '') $raw = 0;
                                                                $pct = (float)$raw;
                                                                $colTotal += ((float)$act['f_weightage'] * ($pct/100));
                                                            }
                                                            $val = round($colTotal, 1);
                                                            if (!$hasData && $val == 0) {
                                                                $display = '-';
                                                                $cls = 'bg-white';
                                                            } else {
                                                                $display = $val . '%';
                                                                $cls = getCellColor((string)$val);
                                                                if ($cls == '') $cls = 'status-0';
                                                            }
                                                        ?>
                                                        <td class="<?= $cls ?> small"><?= $display ?></td>
                                                    <?php endforeach; ?>
                                                    <td class="bg-light small"></td>
                                                    <td class="bg-light small"></td>
                                                    <td class="bg-light small"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive">
                            <table class="table table-bordered align-middle matrix-table mb-0">
                                <thead>
                                    <tr>
                                        <!-- Header Row 1 -->
                                        <th colspan="2" class="bg-dark text-white"><?= h(__('project_details_col_date_month') ?: '*Tarikh/Bulan') ?></th>
                                        <?php foreach ($dateCols as $dc): ?>
                                            <th class="bg-primary"><?= h($dc['label']) ?></th>
                                        <?php endforeach; ?>
                                        <th rowspan="2" class="bg-light text-dark"><?= h(__('project_details_label_start_date') ?: 'Tarikh Mula') ?></th>
                                        <th rowspan="2" class="bg-light text-dark"><?= h(__('project_details_label_end_date') ?: 'Tarikh Tamat') ?></th>
                                        <th rowspan="2" class="bg-light text-dark" style="min-width: 250px;"><?= h(__('project_details_label_notes') ?: 'Catatan (Sekiranya perlu)') ?></th>
                                    </tr>
                                    <tr>
                                        <!-- Header Row 2 -->
                                        <th style="background-color: #0b1d35 !important;"><?= h(__('project_details_col_activity') ?: 'Aktiviti') ?></th>
                                        <th style="width: 80px; background-color: #0b1d35 !important;"><?= h(__('project_details_col_weight') ?: 'Pemberat<br>(100%)') ?></th>
                                        <!-- Subheaders (Just Labels like (a), (b) etc or blank) -->
                                        <?php $char = 'a'; foreach ($dateCols as $dc): ?>
                                            <th class="bg-light text-dark fw-bold text-center">(<?= $char++ ?>)</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $index => $act): ?>
                                        <tr>
                                            <td class="activity-col"><?= ($index + 1) ?>. <?= h($act['f_namaAktiviti']) ?></td>
                                            <td class="fw-bold bg-light"><?= (float)$act['f_weightage'] ?>%</td>
                                            
                                            <?php foreach ($dateCols as $dc): ?>
                                                <?php 
                                                    $val = $act['history'][$dc['key']] ?? '-'; 
                                                    $cls = getCellColor($val); // Use Helper
                                                    $displayVal = ($val !== '-' && $val !== '') ? $val . '%' : '-';
                                                ?>
                                                <td class="<?= $cls ?>"><?= $displayVal ?></td>
                                            <?php endforeach; ?>
                                            
                                            <!-- Dates -->
                                            <td class="bg-light small">
                                                <?= !empty($act['f_startDate']) ? date('d/m/y', strtotime($act['f_startDate'])) : date('d/m/y', strtotime($project['f_startDate'])) ?>
                                            </td>
                                            <td class="bg-light small">
                                                <?= !empty($act['f_endDate']) ? date('d/m/y', strtotime($act['f_endDate'])) : date('d/m/y', strtotime($project['f_endDate'])) ?>
                                            </td>

                                            <!-- Last Note -->
                                            <?php 
                                                $lastRep = end($act['reports']); 
                                                $note = $lastRep ? $lastRep['f_catatan'] : '-';
                                                $noteDate = $lastRep ? date('M Y', mktime(0,0,0, (int)$lastRep['f_bulan'], 1, (int)$lastRep['f_tahun'])) : '';
                                            ?>
                                            <td class="text-start small bg-white text-muted" title="<?= h($note) ?>">
                                                <?php if($lastRep): ?>
                                                    <strong><?= $noteDate ?>:</strong> <?= h(substr((string)$note, 0, 50)) ?><?= strlen($note) > 50 ? '...' : '' ?>
                                                <?php else: ?>
                                                    <?= h(__('project_details_note_none') ?: '-') ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                            <!-- Overall Row -->
                                            <tr class="fw-bold border-top border-3 border-dark overall-row">
                                        <td colspan="2" class="text-end pe-3 bg-light"><?= h(__('project_details_overall_percent_label') ?: 'PERATUSAN STATUS KEMAJUAN KESELURUHAN (%)') ?></td>
                                        <?php foreach ($dateCols as $dc): ?>
                                            <!-- Calculation for specific column -->
                                            <?php
                                                $colTotal = 0;
                                                $hasData = false;
                                                foreach($activities as $act) {
                                                    $raw = $act['history'][$dc['key']] ?? 0;
                                                    // Check if any report exists for this month
                                                    if (isset($act['history'][$dc['key']]) && $act['history'][$dc['key']] !== '-' && $act['history'][$dc['key']] !== '') {
                                                        $hasData = true;
                                                    }

                                                    if ($raw === '-' || $raw === '') $raw = 0;
                                                    $pct = (float)$raw;
                                                    $colTotal += ((float)$act['f_weightage'] * ($pct/100));
                                                }
                                                // Round
                                                $val = round($colTotal, 1);
                                                
                                                // Color Logic: If no data at all in column, show dash. If 0 but has data, show 0% (Red).
                                                if (!$hasData && $val == 0) {
                                                    $display = '-';
                                                    $cls = 'bg-white';
                                                } else {
                                                    $display = $val . '%';
                                                    $cls = getCellColor((string)$val);
                                                    // Fallback if getCellColor returns empty for 0
                                                    if ($cls == '') $cls = 'status-0'; 
                                                }
                                            ?>
                                            <td class="<?= $cls ?>"><?= $display ?></td>
                                        <?php endforeach; ?>
                                        <td class="bg-light"></td>
                                        <td class="bg-light"></td>
                                        <td class="bg-light"></td>
                                    </tr>
                                
                                </tbody>
                            </table>
                        </div>
                </div>

                <!-- <div class="mt-3 text-muted text-center small">
                    <?= h(__('project_details_formula') ?: 'Formula: Sum of (Weightage * Percent / 100) per month.') ?>
                </div> -->

            </div>
        </div>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

        </div>

        <!-- Overlay cleanup: remove development/loader overlays and prevent re-creation on this page -->
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            function removeOverlays(){
                try{ if(window.AppLoader && typeof AppLoader.hide === 'function') AppLoader.hide(); }catch(e){}

                const whitelist = ['.leftside-menu', '.sidebar', '.vertical-menu', '#sidebar', '.main-sidebar'];
                // Containers we should never remove elements from (topbar/header areas)
                const keepContainers = ['.topbar', '.page-title-box', '.page-header', '#topbar', '.header', '.page-title'];

                const sel = [
                    '#global-loader', '.loader-overlay', '.modal-backdrop', '.swal2-container', '.swal2-backdrop', '.sweet-alert', '.swal2'
                ];
                sel.forEach(s => {
                    document.querySelectorAll(s).forEach(el => {
                        // Remove sidebar loading overlay even if inside the leftside menu
                        if(s === '.sidebar-loading-overlay') { try{ el.remove(); }catch(e){}; return; }
                        if(whitelist.some(w => el.closest(w))) return;
                        el.remove();
                    });
                });

                // Remove fixed/absolute elements with very high z-index outside the main content
                document.querySelectorAll('body *').forEach(el => {
                    try{
                        if (whitelist.some(w => el.closest(w))) return;
                        // Never remove elements inside keepContainers (topbar/header)
                        if (keepContainers.some(k => el.closest(k))) return;
                        const cs = getComputedStyle(el);
                        const pos = cs.position;
                        const zi = parseInt(cs.zIndex) || 0;
                        // Only remove very-high z-index overlays that are not in content or top/header
                        if((pos === 'fixed' || pos === 'absolute') && zi >= 900){
                            if(!el.closest('.content-page') && el !== document.documentElement && el !== document.body){
                                el.remove();
                            }
                        }
                        // Keep heuristic removal limited to known loader/backdrop classes; avoid removing development banners or topbar items.
                    }catch(e){}
                });
            }

            removeOverlays();
            // Repeat removal a few times to catch overlays created slightly later
            var _tries = 0;
            var _maxTries = 8;
            var _interval = setInterval(function(){
                removeOverlays();
                _tries++;
                if(_tries >= _maxTries) clearInterval(_interval);
            }, 300);

            // Observe DOM for overlays added later and remove them
            const mo = new MutationObserver(muts => {
                muts.forEach(m => {
                    m.addedNodes.forEach(n => {
                        if(n.nodeType !== 1) return;
                        try{
                            const cs = getComputedStyle(n);
                            const pos = cs.position;
                            const zi = parseInt(cs.zIndex) || 0;
                            // If node is inside topbar or other header container, keep it
                            var _keep = false;
                            try{ _keep = (n.closest && (n.closest('.topbar') || n.closest('.page-title-box') || n.closest('.page-header') || n.closest('#topbar') || n.closest('.header') || n.closest('.page-title'))); }catch(e){}
                            if((pos === 'fixed' || pos === 'absolute') && zi >= 900 && !_keep){
                                if(!n.closest('.content-page') && n !== document.documentElement && n !== document.body){ n.remove(); return; }
                            }
                        }catch(e){}
                        const node = /** @type {Element} */ (n);
                        if(node.matches){
                            var patterns = ['#global-loader','.loader-overlay','.modal-backdrop','.swal2-container','.swal2-backdrop','.sweet-alert','.swal2'];
                            for(var i=0;i<patterns.length;i++){
                                try{ if(node.matches(patterns[i])){ node.remove(); return; } }catch(e){}
                            }
                        }
                    });
                });
            });
            mo.observe(document.body, { childList: true, subtree: true });

            // (No special sidebar overrides on this page.)

            // Second cleanup pass after short delay
            setTimeout(removeOverlays, 500);
        });
        </script>

                <!-- Persist dev-banner: aggressively enforce visibility and reinsertion -->
                <script>
                document.addEventListener('DOMContentLoaded', function(){
                    try{
                        var sel = '.dev-banner, .development-banner, #development-overlay, .dev-overlay, .development-mode';
                        function queryBanner(){ return document.querySelector(sel); }

                        function enforce(el){
                            if(!el) return;
                            try{
                                el.setAttribute('data-dev-persist','1');
                                el.style.setProperty('display','block','important');
                                el.style.setProperty('visibility','visible','important');
                                el.style.setProperty('opacity','1','important');
                                el.style.setProperty('pointer-events','auto','important');
                                el.style.setProperty('z-index','2000','important');
                            }catch(e){}
                        }

                        var originalClone = null;
                        var first = queryBanner();
                        if(first) originalClone = first.cloneNode(true);

                        function ensure(){
                            var el = queryBanner();
                            if(el){
                                enforce(el);
                                document.querySelectorAll('.reinserted-dev-banner').forEach(function(d){ if(d !== el) d.remove(); });
                                return;
                            }
                            if(originalClone){
                                var top = document.querySelector('.topbar') || document.querySelector('#topbar') || document.querySelector('.page-title-box') || document.body;
                                var clone = originalClone.cloneNode(true);
                                clone.classList.add('reinserted-dev-banner');
                                enforce(clone);
                                if(top && top.parentNode) top.parentNode.insertBefore(clone, top.nextSibling);
                                else document.body.insertBefore(clone, document.body.firstChild);
                            }
                        }

                        ensure();

                        var obsAttrs = new MutationObserver(function(muts){
                            muts.forEach(function(m){ if(m.type === 'attributes') enforce(m.target); });
                        });
                        var elNow = queryBanner();
                        if(elNow) obsAttrs.observe(elNow, { attributes:true, attributeFilter:['style','class','hidden'] });

                        var mo = new MutationObserver(function(muts){
                            muts.forEach(function(m){
                                if(m.removedNodes && m.removedNodes.length){
                                    var reinsert=false;
                                    m.removedNodes.forEach(function(n){ try{ if(n.nodeType===1 && (n.matches && n.matches(sel))) reinsert=true; }catch(e){} });
                                    if(reinsert) setTimeout(ensure,50);
                                }
                                if(m.addedNodes && m.addedNodes.length){
                                    m.addedNodes.forEach(function(n){
                                        try{
                                            if(n.nodeType===1 && (n.matches && n.matches(sel))){
                                                enforce(n);
                                                obsAttrs.observe(n, { attributes:true, attributeFilter:['style','class','hidden'] });
                                            }
                                        }catch(e){}
                                    });
                                }
                            });
                        });
                        mo.observe(document.body, { childList:true, subtree:true });

                        var ticks=0, tmax=240; // ~60s
                        var interval = setInterval(function(){ ensure(); ticks++; if(ticks>=tmax) clearInterval(interval); }, 250);
                    }catch(e){}
                });
                </script>

        <!-- Ensure progress-bar has an explicit inline background color and width for UI + print -->
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            function ensureProgressBars(){
                document.querySelectorAll('.progress-bar').forEach(function(pb){
                    try{
                        // If server-side set width, keep it; otherwise fallback to aria-valuenow
                        var width = pb.style.width || pb.getAttribute('aria-valuenow') ? (pb.getAttribute('aria-valuenow') + '%') : pb.style.width;
                        if(width) pb.style.width = width;

                        // Copy computed background into inline style so print preserves color
                        var cs = getComputedStyle(pb);
                        var bg = cs.backgroundColor;
                        if(bg && bg !== 'transparent' && bg !== 'rgba(0, 0, 0, 0)'){
                            pb.style.backgroundColor = bg;
                        } else {
                            // Try to find a colored class applied by Bootstrap on the element
                            // fallback: try computed style of first classed ancestor
                            var anc = pb.closest('[class*="bg-"]');
                            if(anc){
                                var ancCs = getComputedStyle(anc);
                                if(ancCs.backgroundColor && ancCs.backgroundColor !== 'transparent') pb.style.backgroundColor = ancCs.backgroundColor;
                            }
                        }

                        // Force visible in UI and print
                        pb.style.display = 'block';
                        pb.style.visibility = 'visible';
                        pb.style.opacity = '1';
                        pb.style.minHeight = pb.style.minHeight || '6px';
                    }catch(e){/* ignore */}
                });
            }

            ensureProgressBars();
            // Re-apply before printing
            if(window.matchMedia){
                window.addEventListener('beforeprint', ensureProgressBars);
            } else {
                window.onbeforeprint = ensureProgressBars;
            }

            // Observe attribute/style changes on progress bars and reapply inline bg
            const obs = new MutationObserver(function(muts){
                muts.forEach(function(m){
                    if(m.type === 'attributes') ensureProgressBars();
                });
            });
            document.querySelectorAll('.progress-bar').forEach(function(pb){ obs.observe(pb, { attributes: true }); });
        });
        </script>

            <!-- Remove trailing empty rows in the matrix table (safety cleanup + observer) -->
            <script>
            (function(){
                function isEmptyRow(row){
                    if(!row) return true;
                    var tds = row.querySelectorAll('td');
                    if(!tds || tds.length === 0) return true;
                    var allEmpty = true;
                    tds.forEach(function(td){
                        var txt = (td.textContent || '').replace(/\u00A0/g,'').trim();
                        var lower = (txt || '').toLowerCase();
                        if(txt !== '' && txt !== '-' && lower !== 'undefined') allEmpty = false;
                    });
                    return allEmpty;
                }

                function cleanupTrailingRows(){
                    try{
                        var tb = document.querySelector('.matrix-table tbody');
                        if(!tb) return;

                        // Remove trailing empty rows (limit loop to avoid infinite)
                        var safety = 50;
                        var removed = 0;
                        while(removed < safety){
                            var last = tb.lastElementChild;
                            if(!last) break;
                            if(last.classList && last.classList.contains('overall-row')) break;
                            if(isEmptyRow(last)){
                                last.remove(); removed++; continue;
                            }
                            break;
                        }

                        // Remove any rows that appear after the overall-row (unexpected extra rows)
                        var overall = tb.querySelector('tr.overall-row');
                        if(overall){
                            var limit = 50;
                            var count = 0;
                            var nxt = overall.nextElementSibling;
                            while(nxt && count < limit){
                                var toRemove = nxt;
                                nxt = nxt.nextElementSibling;
                                toRemove.remove();
                                count++;
                            }
                        }
                    }catch(e){/* ignore */}
                }

                document.addEventListener('DOMContentLoaded', function(){
                    cleanupTrailingRows();

                    // Re-run on window load as some scripts may modify DOM later
                    window.addEventListener('load', cleanupTrailingRows);

                    // Ensure cleanup before printing
                    if(window.matchMedia){
                        window.addEventListener('beforeprint', cleanupTrailingRows);
                    } else {
                        window.onbeforeprint = cleanupTrailingRows;
                    }

                    // Observe tbody for new rows and cleanup if extras appear
                    try{
                        var tb = document.querySelector('.matrix-table tbody');
                        if(tb){
                            var mo = new MutationObserver(function(muts){
                                muts.forEach(function(m){
                                    if(m.type === 'childList' && (m.addedNodes.length || m.removedNodes.length)){
                                        // small debounce
                                        setTimeout(cleanupTrailingRows, 50);
                                    }
                                });
                            });
                            mo.observe(tb, { childList: true });
                        }
                    }catch(e){}
                });
            })();
            </script>

        <?php include __DIR__ . '/../includes/script.php'; ?>
</body>
</html>
