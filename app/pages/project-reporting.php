<?php
// pages/project-reporting.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/ProjectReportingController.php';
$controller = new ProjectReportingController();

$projects = $controller->userProjects;
$currentMonth = date('n');
$currentYear = date('Y');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="<?= h($controller->lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <meta name="csrf-token" content="<?= h($csrf) ?>">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <?php
    // JS translations for this page (available as window.PR_I18N)
    $pr_i18n = [
        'placeholder_select_project' => __("project_reporting_placeholder_select_project") ?: '-- Sila Pilih Projek --',
        'placeholder_search_project' => __("project_reporting_placeholder_search_project") ?: 'Cari projek...',
        'no_projects_found' => __("project_reporting_no_projects_found") ?: 'Tiada projek dijumpai.',
        'alert_outdated_title' => __("project_reporting_alert_outdated_title") ?: 'Maklumat Belum Dikemaskini',
        'alert_outdated_text' => __("project_reporting_alert_outdated_text") ?: 'Laporan untuk bulan ini belum dihantar. Data di bawah menunjukkan kemajuan terkini dari bulan sebelumnya.',
        'warn_period_title' => __("project_reporting_warn_period_title") ?: 'Amaran Tempoh Projek',
        'warn_period_text_tpl' => __("project_reporting_warn_period_text_tpl") ?: 'Tarikh pelaporan (%s/%s) adalah selepas tarikh tamat projek (%s).',
        'confirm_after_end_title' => __("project_reporting_confirm_after_end_title") ?: 'Projek Telah Tamat',
        'confirm_after_end_text' => __("project_reporting_confirm_after_end_text") ?: 'Anda sedang menghantar laporan untuk tarikh selepas projek tamat. Adakah anda pasti?',
        'confirm_yes' => __("project_reporting_confirm_yes") ?: 'Ya, Hantar',
        'cancel' => __("project_reporting_cancel") ?: 'Batal',
        'success_title' => __("project_reporting_success_title") ?: 'Berjaya',
        'success_saved' => __("project_reporting_success_saved") ?: 'Laporan berjaya disimpan!',
        'error_title' => __("project_reporting_error") ?: 'Ralat',
        'upload_soon_title' => __("project_reporting_upload_comingsoon_title") ?: 'Akan Datang',
        'upload_soon_text' => __("project_reporting_upload_comingsoon_text") ?: 'Fungsi muat naik akan tersedia tidak lama lagi.'
    ];
    ?>
    <script>window.PR_I18N = <?= json_encode($pr_i18n, JSON_UNESCAPED_UNICODE) ?>;</script>
    <style>
        [x-cloak] { display: none !important; }
        .stat-display { font-size: 1.5rem; font-weight: bold; color: var(--bs-primary); }
    </style>
</head>
<body data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>" data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>" data-layout="vertical">

<div class="wrapper">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="content-page">
        <div class="content">
                <div class="container-fluid" x-data="reportingApp()">
                    <?php if (isset($_GET['debug']) && $_GET['debug']): ?>
                        <div class="alert alert-secondary small mb-3">Debug: projects count: <?= (int)count($projects) ?>; first: <?= h($projects[0]['f_projectName'] ?? '-') ?></div>
                    <?php endif; ?>
                
                <!-- Page Title -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="page-title mb-0" style="font-weight: 700; letter-spacing: -0.5px;">
                                <?= h(__('project_reporting_title') ?: 'Laporan Kemajuan Projek') ?>
                            </h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><?= h(__('dashboard_breadcrumb') ?: 'Dashboard') ?></a></li>
                                    <li class="breadcrumb-item" :class="{'active': !selectedProject}">
                                        <template x-if="!selectedProject">
                                            <a href="#" @click.prevent="selectedProject = ''; activities = [];;"><?= h(__('project_reporting_breadcrumb_admin') ?: 'Pentadbiran') ?></a>
                                        </template>
                                        <template x-if="selectedProject">
                                            <a href="#" @click.prevent="selectedProject = ''; activities = [];;"><?= h(__('project_reporting_breadcrumb_admin') ?: 'Pentadbiran') ?></a>
                                        </template>
                                    </li>
                                    <li class="breadcrumb-item active" x-show="selectedProject" x-text="getSelectedProjectName()" x-cloak></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?= h(__('project_reporting_label_select_project') ?: 'Pilih Projek Anda') ?></label>
                                
                                <!-- Custom Searchable Dropdown -->
                                <div class="position-relative" @click.outside="openDropdown = false">
                                    <button class="form-control form-control-lg d-flex justify-content-between align-items-center bg-white text-start" 
                                            type="button" 
                                            @click="openDropdown = !openDropdown; if(openDropdown) $nextTick(() => $refs.searchInput.focus())">
                                        <span x-text="getSelectedProjectName() || PR_I18N.placeholder_select_project" :class="{'text-muted': !selectedProject}"></span>
                                        <i class="bi bi-chevron-down small text-muted"></i>
                                    </button>

                                    <div class="card position-absolute w-100 shadow-lg border-0 mt-1" 
                                         style="z-index: 1000; max-height: 300px; overflow: hidden;"
                                         x-show="openDropdown" 
                                         x-transition.opacity.duration.200ms
                                         x-cloak>
                                        
                                        <!-- Search Input -->
                                        <div class="p-2 border-bottom bg-light sticky-top">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                                      <input type="text" class="form-control border-start-0" 
                                                          x-ref="searchInput" 
                                                          x-model="searchQuery" 
                                                          :placeholder="PR_I18N.placeholder_search_project"
                                                          autocomplete="off">
                                            </div>
                                        </div>

                                        <!-- List Options -->
                                        <div class="list-group list-group-flush" style="overflow-y: auto; max-height: 250px;">
                                            <template x-for="p in filteredProjects" :key="p.f_projectID">
                                                <button type="button" 
                                                        class="list-group-item list-group-item-action py-2 font-14"
                                                        :class="{'active': selectedProject == p.f_projectID}"
                                                        @click="selectProjectFromDropdown(p.f_projectID)">
                                                    <div class="fw-bold" x-text="p.f_projectName"></div>
                                                    <small class="text-muted" x-show="p.f_projectCode" x-text="p.f_projectCode"></small>
                                                </button>
                                            </template>
                                            
                                            <!-- Empty State -->
                                            <div x-show="filteredProjects.length === 0" class="p-3 text-center text-muted small">
                                                <?= h(__('project_reporting_no_projects_found') ?: 'Tiada projek dijumpai.') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold"><?= h(__('project_reporting_label_month') ?: 'Bulan') ?></label>
                                <select class="form-select form-select-lg" x-model="selectedMonth" @change="fetchData()">
                                    <?php for($m=1; $m<=12; $m++): ?>
                                        <option value="<?= $m ?>"><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold"><?= h(__('project_reporting_label_year') ?: 'Tahun') ?></label>
                                <select class="form-select form-select-lg" x-model="selectedYear" @change="fetchData()">
                                    <?php for($y=$currentYear-1; $y<=$currentYear+1; $y++): ?>
                                        <option value="<?= $y ?>"><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div x-show="selectedProject" x-cloak>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><?= h(__('project_reporting_update_project_status') ?: __('update_project_status')) ?></h5>
                            <span class="badge bg-soft-primary text-primary fs-6">
                                <?= h(__('project_reporting_update_status') ?: __('update_status')) ?>: <span x-text="monthName"></span> <span x-text="selectedYear"></span>
                            </span>
                        </div>
                        
                        <!-- Notification if data is from previous month -->
                        <div class="alert alert-info border-0 rounded-0 mb-0" role="alert" x-show="isPreviousData" x-transition>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="ri-information-fill fs-4 me-2"></i>
                                    <div>
                                        <h5 class="alert-heading fw-bold mb-1 font-14"><?= h(__('project_reporting_alert_outdated_title') ?: 'Maklumat Belum Dikemaskini') ?></h5>
                                        <p class="mb-0 font-13"><?= h(__('project_reporting_alert_outdated_text') ?: 'Laporan untuk bulan ini belum dihantar. Data di bawah menunjukkan kemajuan terkini dari bulan sebelumnya.') ?></p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" @click="isPreviousData = false" aria-label="Close"></button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                                <th style="width: 25%"><?= h(__('project_reporting_th_activity') ?: 'Aktiviti') ?></th>
                                            <th style="width: 15%"><?= h(__('project_reporting_th_kpi') ?: 'KPI / Sasaran') ?></th>
                                            <th style="width: 10%" class="text-center"><?= h(__('project_reporting_th_weight') ?: 'Pemberat') ?></th>
                                            <th style="width: 15%"><?= h(__('project_reporting_th_current_status') ?: 'Status Terkini (%)') ?></th>
                                            <th style="width: 25%"><?= h(__('project_reporting_th_justification') ?: 'Justifikasi / Catatan') ?></th>
                                            <th style="width: 10%"><?= h(__('project_reporting_th_evidence') ?: 'Bukti') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Empty state when no activities -->
                                        <template x-if="activities.length === 0">
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                                    <h5 class="text-muted"><?= h(__('project_reporting_no_activities_title') ?: 'Tiada Aktiviti Dijumpai') ?></h5>
                                                    <p class="text-muted mb-0"><?= h(__('project_reporting_no_activities_text') ?: 'Projek ini belum mempunyai aktiviti untuk bulan yang dipilih.') ?></p>
                                                </td>
                                            </tr>
                                        </template>
                                        
                                        <!-- Activity rows -->
                                        <template x-for="(act, index) in activities" :key="act.f_aktivitiID">
                                            <tr>
                                                <td>
                                                    <strong x-text="act.f_namaAktiviti"></strong>
                                                </td>
                                                <td>
                                                    <div class="small text-muted">KPI: <span x-text="act.f_kpi"></span></div>
                                                    <div class="fw-bold text-dark"><span x-text="act.f_target"></span></div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary" x-text="parseFloat(act.f_weightage) + '%'"></span>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" x-model="act.currentPercent" 
                                                            @input="if(act.currentPercent > 100) act.currentPercent = 100; if(act.currentPercent < 0) act.currentPercent = 0;"
                                                            min="0" max="100" placeholder="0">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                    <!-- Progress Bar Visual -->
                                                    <div class="progress mt-1" style="height: 5px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                            :style="`width: ${act.currentPercent}%`" 
                                                            :class="getStatusColor(act.currentPercent)"></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <textarea class="form-control form-control-sm" rows="2" x-model="act.currentCatatan" placeholder="Isu / Masalah / Kemajuan..."></textarea>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-secondary" @click="uploadFile(act)">
                                                        <i class="bi bi-paperclip"></i> Upload
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Footer / Summary -->
                        <div class="card-footer bg-light p-4">
                            <div class="row align-items-center">
                                    <div class="col-md-8 text-end">
                                    <div class="text-muted small text-uppercase"><?= h(__('project_reporting_label_overall_achievement') ?: 'Pencapaian Keseluruhan') ?></div>
                                    <div class="stat-display" x-text="calculateOverall().toFixed(1) + '%'">0.0%</div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-success w-100 btn-lg" @click="saveReport()">
                                        <i class="bi bi-check-circle-fill"></i> <?= h(__('project_reporting_btn_save') ?: __('save_report')) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project List State (When no project selected) -->
                <div x-show="!selectedProject" x-cloak>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="bi bi-list-task"></i> <?= h(__('project_reporting_list_title') ?: 'Senarai Projek Anda') ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle" id="reportMonitorTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th><?= __('teras_code') ?></th>
                                                        <th><?= __('project_name') ?></th>
                                                        <th><?= h(__('project_monitoring_owner') ?: 'Pemilik Projek') ?></th>
                                                        <th style="width: 200px;"><?= h(__('project_monitoring_progress') ?: 'Kemajuan (%)') ?></th>
                                                        <th><?= h(__('project_monitoring_status') ?: 'Status') ?></th>
                                                        <th><?= __('actions') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(empty($projects)): ?>
                                                        <tr><td colspan="6" class="text-center text-muted"><?= h(__('project_monitoring_no_data') ?: 'Tiada data projek.') ?></td></tr>
                                                    <?php else: ?>
                                                        <?php foreach($projects as $p): ?>
                                                            <tr>
                                                                <td><?= getTerasKodBadgeHtml($p['f_kodTeras'] ?? '', 'badge') ?></td>
                                                                <td>
                                                                    <strong><?= h($p['f_projectName'] ?? '') ?></strong>
                                                                </td>
                                                                <td><?= h($p['ownerName'] ?? '-') ?></td>
                                                                <td>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <div class="progress flex-grow-1" style="height: 6px;">
                                                                            <div class="progress-bar bg-<?= $p['status_color'] ?? 'primary' ?>" 
                                                                                 style="width: <?= $p['progress'] ?? 0 ?>%"></div>
                                                                        </div>
                                                                        <span class="fw-bold"><?= $p['progress'] ?? 0 ?>%</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-subtle-<?= $p['status_color'] ?? 'primary' ?> text-<?= $p['status_color'] ?? 'primary' ?> border border-<?= $p['status_color'] ?? 'primary' ?>">
                                                                        <?= h($p['status_label'] ?? '-') ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-outline-primary" @click="selectProject(<?= $p['f_projectID'] ?>, '<?= $p['f_endDate'] ?>')">
                                                                        <?= h(__('project_reporting_btn_report') ?: 'Lapor') ?> <i class="bi bi-arrow-right"></i>
                                                                    </button>
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
                    </div>
                </div>

            </div>
        </div>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportingApp', () => ({
            availableProjects: <?= json_encode($projects) ?>,
            selectedProject: '',
            selectedEndDate: null, // Store selected project end date
            selectedMonth: <?= $currentMonth ?>,
            selectedYear: <?= $currentYear ?>,
            activities: [],
            isPreviousData: false,
            monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            
            // Dropdown State
            openDropdown: false,
            searchQuery: '',

            get filteredProjects() {
                if (!this.searchQuery) return this.availableProjects;
                const lower = this.searchQuery.toLowerCase();
                return this.availableProjects.filter(p => 
                    (p.f_projectName && p.f_projectName.toLowerCase().includes(lower)) || 
                    (p.f_projectCode && p.f_projectCode.toLowerCase().includes(lower))
                );
            },


            init() {
                this.$watch('selectedProject', (val) => {
                    if (val) {
                         const p = this.availableProjects.find(p => p.f_projectID == val);
                         if (p) {
                             this.selectedEndDate = p.f_endDate;
                             this.checkDateWarning();
                         }
                    }
                });
            },

            get monthName() {
                return this.monthNames[this.selectedMonth - 1];
            },


            getSelectedProjectName() {
                if (!this.selectedProject) return '';
                const p = this.availableProjects.find(p => p.f_projectID == this.selectedProject);
                return p ? p.f_projectName : '';
            },

            selectProjectFromDropdown(id) {
                this.selectedProject = id;
                this.openDropdown = false;
                this.searchQuery = ''; // Reset search
                this.onProjectChange(); // Trigger change logic
            },

            // Handle project change from dropdown
            onProjectChange() {

                if (!this.selectedProject) {
                    this.activities = [];
                    return;
                }
                
                const project = this.availableProjects.find(p => p.f_projectID == this.selectedProject);
                if (project && project.f_startDate) {
                    // Parse the start date and set month/year to project start
                    const startDate = new Date(project.f_startDate);
                    this.selectedMonth = startDate.getMonth() + 1; // JS months are 0-indexed
                    this.selectedYear = startDate.getFullYear();
                    
                    // Auto-fetch data for the first month
                    this.$nextTick(() => {
                        this.fetchData();
                    });
                }
            },

            // New: Select Project from Table
            selectProject(id, endDate) {
                this.selectedProject = id;
                
                // Find the project details
                const project = this.availableProjects.find(p => p.f_projectID == id);
                if (project && project.f_startDate) {
                    // Parse the start date and set month/year to project start
                    const startDate = new Date(project.f_startDate);
                    this.selectedMonth = startDate.getMonth() + 1; // JS months are 0-indexed
                    this.selectedYear = startDate.getFullYear();
                    
                    // Auto-fetch data for the first month
                    this.$nextTick(() => {
                        this.fetchData();
                    });
                }
                // watcher will handle the rest
            },

            // New: Check Date Validation
            checkDateWarning() {
                if (!this.selectedEndDate) return;
                
                // Construct logic date (End of selected month)
                // JS Month is 0-indexed
                let repDate = new Date(this.selectedYear, this.selectedMonth, 0); // Last day of selected month
                let endDate = new Date(this.selectedEndDate);

                // If reporting period is significantly after project end (e.g. next month)
                if (repDate > endDate) {
                            // Use translated template and button from PR_I18N
                            const tpl = PR_I18N.warn_period_text_tpl || 'Tarikh pelaporan (%s/%s) adalah selepas tarikh tamat projek (%s).';
                            const msg = tpl.replace('%s', this.selectedMonth).replace('%s', this.selectedYear).replace('%s', this.selectedEndDate);
                            Swal.fire({
                                title: PR_I18N.warn_period_title || 'Amaran Tempoh Projek',
                                text: msg,
                                icon: 'warning',
                                confirmButtonText: PR_I18N.btn_continue || 'Teruskan'
                            });
                }
            },

            async fetchData(silent = false) {
                if (!this.selectedProject) return;
                
                // Reset state
                this.isPreviousData = false;

                // Re-check warning when date changes via dropdown (if project selected)
                if (!silent) this.checkDateWarning();

                const fd = new FormData();
                fd.append('action', 'getReport');
                fd.append('projectID', this.selectedProject);
                fd.append('month', this.selectedMonth);
                fd.append('year', this.selectedYear);
                fd.append('csrf_token', '<?= $csrf ?>');

                try {
                    let res = await fetch('<?= base_url("ajax/project-reporting-handler.php") ?>', {method: 'POST', body: fd});
                    let json = await res.json();
                    
                    if (json.success) {
                        // Check if we are showing previous data
                        const hasCurrent = json.data.activities.some(a => a.f_percentComplete != null && a.f_percentComplete !== '');
                        const hasPrevious = json.data.activities.some(a => a.last_percent != null && a.last_percent > 0);
                        this.isPreviousData = !hasCurrent && hasPrevious;

                        // Map and initialize fields
                        this.activities = json.data.activities.map(a => ({
                            ...a,
                            // Logic: Use existing record OR last month's record OR 0
                            currentPercent: (a.f_percentComplete !== null && a.f_percentComplete !== '')  
                                            ? a.f_percentComplete 
                                            : (a.last_percent || 0),
                            currentCatatan: a.f_catatan || ''
                        }));
                    } else {
                        Swal.fire(PR_I18N.error_title || 'Ralat', json.message, 'error');
                    }
                } catch (e) { console.error(e); }
            },

            calculateOverall() {
                // Formula: Sum of (Weight * Percent / 100)
                return this.activities.reduce((sum, act) => {
                    let w = parseFloat(act.f_weightage) || 0;
                    let p = parseFloat(act.currentPercent) || 0;
                    return sum + (w * (p / 100));
                }, 0);
            },

            getStatusColor(percent) {
                if (percent >= 80) return 'bg-success';
                if (percent >= 40) return 'bg-warning';
                return 'bg-danger';
            },

            async saveReport() {
                // Check Validation again
                if (this.selectedEndDate) {
                    let repDate = new Date(this.selectedYear, this.selectedMonth, 0); 
                    let endDate = new Date(this.selectedEndDate);
                    if (repDate > endDate) {
                        let confirm = await Swal.fire({
                            title: PR_I18N.confirm_after_end_title || 'Projek Telah Tamat',
                            text: PR_I18N.confirm_after_end_text || "Anda sedang menghantar laporan untuk tarikh selepas projek tamat. Adakah anda pasti?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: PR_I18N.confirm_yes || 'Ya, Hantar',
                            cancelButtonText: PR_I18N.cancel || 'Batal'
                        });
                        if (!confirm.isConfirmed) return;
                    }
                }

                // Prepare Payload
                const reports = this.activities.map(a => ({
                    aktivitiID: a.f_aktivitiID,
                    percent: a.currentPercent,
                    catata: a.currentCatatan
                }));

                const fd = new FormData();
                fd.append('action', 'saveReport');
                fd.append('month', this.selectedMonth);
                fd.append('year', this.selectedYear);
                fd.append('reports', JSON.stringify(reports));
                fd.append('csrf_token', '<?= $csrf ?>');

                try {
                    let res = await fetch('<?= base_url("ajax/project-reporting-handler.php") ?>', {method: 'POST', body: fd});
                    let json = await res.json();
                    
                    if (json.success) {
                        Swal.fire(PR_I18N.success_title || 'Berjaya', PR_I18N.success_saved || 'Laporan berjaya disimpan!', 'success');
                        this.fetchData(true); // Refresh data to update notification status (silent mode)
                    } else {
                        Swal.fire(PR_I18N.error_title || 'Ralat', json.message, 'error');
                    }
                } catch(e) {
                    Swal.fire(PR_I18N.error_title || 'Ralat', e.message, 'error');
                }
            },

            uploadFile(act) {
                Swal.fire(PR_I18N.upload_soon_title || 'Coming Soon', PR_I18N.upload_soon_text || 'Fungsi muat naik akan tersedia tidak lama lagi.', 'info');
            }
        }))
    });
</script>
</body>
</html>
