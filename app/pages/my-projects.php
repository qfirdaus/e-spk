<?php
// pages/my-projects.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/MyProjectsController.php';
$controller = new MyProjectsController();

$profile = $controller->profile;
$projects = $controller->projects;
$terasOpts = $controller->terasOptions;

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
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .wizard-step { border-left: 4px solid #e2e8f0; padding-left: 1rem; margin-bottom: 2rem; }
        .wizard-step.active { border-color: #3b82f6; }
        .wizard-step-title { font-weight: bold; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.05em; }
        .wizard-step.active .wizard-step-title { color: #3b82f6; }
        .read-only-badge { display: inline-block; font-size: 0.7rem; background: #e2e8f0; color: #64748b; padding: 0.15rem 0.5rem; border-radius: 4px; margin-left: 0.5rem; }
    </style>
</head>
<body data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>" data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>" data-layout="vertical" x-data="projectViewer()">

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
                                <?= __('my_projects_title') ?: 'Projek Saya' ?>
                            </h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><?= __('project_planning_breadcrumb_dashboard') ?></a></li>
                                    <li class="breadcrumb-item active"><?= __('my_projects_breadcrumb') ?: 'Projek Saya' ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Projects List -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0"><?= __('my_projects_card_title') ?: 'Senarai Projek Saya' ?></h5>
                            <span class="text-muted"><i class="bi bi-info-circle"></i> <?= __('my_projects_hint') ?: 'Anda hanya boleh mengedit tarikh aktiviti' ?></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= __('project_name') ?></th>
                                        <th><?= __('project_planning_label_teras') ?></th>
                                        <th><?= __('project_planning_label_duration') ?></th>
                                        <th><?= __('actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($projects)): ?>
                                        <tr><td colspan="4" class="text-center text-muted"><?= __('my_projects_no_projects') ?: 'Tiada projek ditugaskan kepada anda' ?></td></tr>
                                    <?php else: ?>
                                        <?php foreach($projects as $p): ?>
                                        <tr>
                                            <td class="fw-bold"><?= h($p['f_projectName']) ?></td>
                                            <td><?= getTerasKodBadgeHtml($p['f_kodTeras']) ?> <small><?= h($p['f_namaTeras']) ?></small></td>
                                            <td><?= h($p['f_startDate']) ?> / <?= h($p['f_endDate']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" @click="viewProject(<?= h($p['f_projectID']) ?>)">
                                                    <i class="bi bi-calendar-check"></i> <?= __('my_projects_btn_manage') ?: 'Urus Tarikh' ?>
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


            </div> <!-- container -->
        </div> <!-- content -->
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<!-- View/Edit Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><?= __('my_projects_modal_title') ?: 'Lihat & Edit Tarikh Aktiviti' ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Warning Banner -->
                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong><?= __('my_projects_warning_title') ?: 'Amaran:' ?></strong>
                        <?= __('my_projects_warning_message') ?: 'Anda hanya boleh mengedit tarikh setiap aktiviti SEKALI sahaja. Setelah disimpan, tarikh tersebut akan dikunci.' ?>
                    </div>
                </div>
                
                <form id="projectForm">
                    <!-- Step 1: Info (READ-ONLY) -->
                    <div class="wizard-step active">
                        <div class="wizard-step-title">
                            <?= __('project_planning_step1_title') ?> 
                            <span class="read-only-badge"><?= __('read_only') ?: 'Baca Sahaja' ?></span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('project_name') ?></label>
                                <input type="text" class="form-control" x-model="form.f_projectName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('project_planning_label_teras') ?></label>
                                <select class="form-select" x-model="form.f_terasID" disabled>
                                    <option value=""><?= __('project_planning_placeholder_select_teras') ?></option>
                                    <?php foreach($terasOpts as $t): ?>
                                        <option value="<?= h($t['f_terasID']) ?>"><?= h($t['f_kodTeras']) ?> - <?= h($t['f_namaTeras']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?= __('project_planning_label_start_date') ?></label>
                                <input type="date" class="form-control" x-model="form.f_startDate" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?= __('project_planning_label_end_date') ?></label>
                                <input type="date" class="form-control" x-model="form.f_endDate" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Activities (ONLY DATES EDITABLE) -->
                    <div class="wizard-step active">
                        <div class="wizard-step-title">
                            <?= __('project_planning_step2_title') ?>
                            <span class="badge bg-success"><?= __('my_projects_dates_editable') ?: 'Tarikh Boleh Edit' ?></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">No.</th>
                                        <th style="width: 25%"><?= __('project_planning_label_activity_name') ?></th>
                                        <th style="width: 20%"><?= __('project_planning_label_kpi_target') ?></th>
                                        <th style="width: 15%" class="table-success"><?= __('project_planning_label_start_date') ?> ✏️</th>
                                        <th style="width: 15%" class="table-success"><?= __('project_planning_label_end_date') ?> ✏️</th>
                                        <th style="width: 12%"><?= __('project_planning_label_weight_percent') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(act, index) in form.activities" :key="index">
                                        <tr>
                                            <td x-text="index + 1" class="text-center align-middle"></td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" x-model="act.name" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm mb-1" x-model="act.kpi" readonly>
                                                <input type="text" class="form-control form-control-sm" x-model="act.target" readonly>
                                            </td>
                                            <td :class="act.canEdit ? 'table-success' : 'table-secondary'">
                                                <div class="input-group input-group-sm">
                                                    <input type="date" class="form-control form-control-sm" 
                                                           x-model="act.startDate" 
                                                           :readonly="!act.canEdit">
                                                    <span class="input-group-text" x-show="!act.canEdit">
                                                        <i class="bi bi-lock-fill text-danger"></i>
                                                    </span>
                                                </div>
                                            </td>
                                            <td :class="act.canEdit ? 'table-success' : 'table-secondary'">
                                                <div class="input-group input-group-sm">
                                                    <input type="date" class="form-control form-control-sm" 
                                                           x-model="act.endDate" 
                                                           :readonly="!act.canEdit">
                                                    <span class="input-group-text" x-show="!act.canEdit">
                                                        <i class="bi bi-lock-fill text-danger"></i>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" x-model="act.weight" readonly>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold"><?= __('project_planning_label_total_weight') ?></td>
                                        <td class="text-center fw-bold" x-text="totalWeight + '%'"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('btn_close') ?></button>
                <button type="button" class="btn btn-primary" @click="updateDates()">
                    <i class="bi bi-calendar-check"></i> <?= __('my_projects_btn_update_dates') ?: 'Kemaskini Tarikh' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('projectViewer', () => ({
            modal: null,
            form: {
                f_projectID: null,
                f_projectName: '',
                f_terasID: '',
                f_startDate: '',
                f_endDate: '',
                activities: []
            },
            get totalWeight() {
                return this.form.activities.reduce((sum, act) => sum + (parseInt(act.weight) || 0), 0);
            },
            init() {
                this.modal = new bootstrap.Modal(document.getElementById('projectModal'));
            },

            async viewProject(id) {
                // Fetch Data
                const fd = new FormData();
                fd.append('action', 'getProject');
                fd.append('projectID', id);
                fd.append('csrf_token', '<?= $csrf ?>');

                try {
                    let res = await fetch('<?= base_url("ajax/my-projects-handler.php") ?>', {method: 'POST', body: fd});
                    let json = await res.json();
                    
                    if(json.success) {
                        const d = json.data;
                        this.form = {
                            f_projectID: d.f_projectID,
                            f_projectName: d.f_projectName,
                            f_terasID: d.f_terasID,
                            f_startDate: d.f_startDate,
                            f_endDate: d.f_endDate,
                            activities: d.activities.map(a => ({
                                id: a.f_aktivitiID,
                                name: a.f_namaAktiviti,
                                kpi: a.f_kpi,
                                target: a.f_target,
                                weight: a.f_weightage,
                                startDate: a.f_startDate || '',
                                endDate: a.f_endDate || '',
                                canEdit: a.canEditDates  // Add edit permission flag
                            }))
                        };
                        this.modal.show();
                    } else {
                        Swal.fire(<?= json_encode(__('project_planning_alert_error')) ?>, json.message, 'error');
                    }
                } catch(e) { console.error(e); }
            },

            async updateDates() {
                // Only submit activities that can be edited (not locked)
                const editableActivities = this.form.activities.filter(a => a.canEdit);
                
                if (editableActivities.length === 0) {
                    Swal.fire(<?= json_encode(__('project_planning_alert_error')) ?>, 
                              <?= json_encode(__('my_projects_no_editable') ?: 'Tiada aktiviti yang boleh diedit') ?>, 
                              'warning');
                    return;
                }
                
                const fd = new FormData();
                fd.append('action', 'updateDates');
                fd.append('csrf_token', '<?= $csrf ?>');
                fd.append('f_projectID', this.form.f_projectID);
                fd.append('activities', JSON.stringify(editableActivities));

                try {
                    let res = await fetch('<?= base_url("ajax/my-projects-handler.php") ?>', {method: 'POST', body: fd});
                    let json = await res.json();
                    if(json.success) {
                        Swal.fire(<?= json_encode(__('project_planning_alert_success')) ?>, json.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire(<?= json_encode(__('project_planning_alert_error')) ?>, json.message, 'error');
                    }
                } catch(e) {
                    Swal.fire(<?= json_encode(__('project_planning_alert_server_error')) ?>, e.message, 'error');
                }
            }
        }))
    });
</script>
</body>
</html>
