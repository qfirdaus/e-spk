<?php
// pages/project-planning.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

require_once __DIR__ . '/../controllers/ProjectPlanningController.php';
$controller = new ProjectPlanningController();

$profile = $controller->profile;
$projects = $controller->projects;
$terasOpts = $controller->terasOptions;
$ownerOpts = $controller->ownerOptions;

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
    </style>
</head>
<body data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>" data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>" data-layout="vertical" x-data="projectWizard()">

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
                                <?= __('project_planning_title') ?>
                            </h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php"><?= __('project_planning_breadcrumb_dashboard') ?></a></li>
                                    <li class="breadcrumb-item active"><?= __('project_planning_breadcrumb') ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Projects List -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0"><?= __('project_planning_card_title') ?></h5>
                            <button class="btn btn-primary" @click="openModal()"><i class="bi bi-plus-circle"></i> <?= __('project_planning_btn_create') ?></button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start" style="width: 10%"><?= __('project_planning_label_teras') ?></th>
                                        <th class="text-start" style="width: 45%"><?= __('project_name') ?></th>
                                        <th class="text-start" style="width: 25%"><?= __('project_planning_label_owner') ?></th>
                                        <th class="text-start" style="width: 10%"><?= __('project_planning_label_duration') ?></th>
                                        <th class="text-center" style="width: 10%"><?= __('actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($projects)): ?>
                                        <tr><td colspan="5" class="text-center text-muted"><?= __('project_planning_no_projects') ?></td></tr>
                                    <?php else: ?>
                                        <?php foreach($projects as $p): ?>
                                        <tr>
                                            <td class="text-start">
                                                <?= getTerasKodBadgeHtml($p['f_kodTeras']) ?>
                                                <small><?= h($p['f_namaTeras']) ?></small>
                                            </td>
                                            <td class="text-start fw-bold"><?= h($p['f_projectName']) ?></td>
                                            <td class="text-start"><?= h($p['ownerName'] ?? '-') ?></td>
                                            <td class="text-start"><?= h($p['f_startDate']) ?> / <?= h($p['f_endDate']) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" @click="editProject(<?= h($p['f_projectID']) ?>)">
                                                    <i class="bi bi-pencil"></i>
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

<!-- Main Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" x-text="isEdit ? '<?= __('update_project') ?>' : '<?= __('create_new_project') ?>'"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="projectForm">
                    <!-- Step 1: Info -->
                    <div class="wizard-step active">
                        <div class="wizard-step-title"><?= __('project_planning_step1_title') ?></div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('project_name') ?></label>
                                <input type="text" class="form-control" x-model="form.f_projectName" placeholder="<?= __('project_planning_placeholder_project_name') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('project_planning_label_teras') ?></label>
                                <select class="form-select" x-model="form.f_terasID">
                                    <option value=""><?= __('project_planning_placeholder_select_teras') ?></option>
                                    <?php foreach($terasOpts as $t): ?>
                                        <option value="<?= h($t['f_terasID']) ?>"><?= h($t['f_kodTeras']) ?> - <?= h($t['f_namaTeras']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('project_planning_label_owner') ?></label>
                                <select class="form-select" x-model="form.f_ownerStafID">
                                    <option value=""><?= __('project_planning_placeholder_select_owner') ?></option>
                                    <?php foreach($ownerOpts as $u): ?>
                                        <option value="<?= h($u['f_stafID']) ?>"><?= h($u['f_nama']) ?> (<?= h($u['f_groupKod']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?= __('project_planning_label_start_date') ?></label>
                                <input type="date" class="form-control" x-model="form.f_startDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?= __('project_planning_label_end_date') ?></label>
                                <input type="date" class="form-control" x-model="form.f_endDate">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Activities -->
                    <div class="wizard-step active">
                        <div class="wizard-step-title"><?= __('project_planning_step2_title') ?></div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">No.</th>
                                        <th style="width: 25%"><?= __('project_planning_label_activity_name') ?></th>
                                        <th style="width: 20%"><?= __('project_planning_label_kpi_target') ?></th>
                                        <th style="width: 15%"><?= __('project_planning_label_start_date') ?></th>
                                        <th style="width: 15%"><?= __('project_planning_label_end_date') ?></th>
                                        <th style="width: 12%"><?= __('project_planning_label_weight_percent') ?></th>
                                        <th style="width: 8%"><?= __('project_planning_label_actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(act, index) in form.activities" :key="index">
                                        <tr>
                                        <td x-text="index + 1" class="text-center align-middle"></td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm mb-1" x-model="act.name" :title="act.name" placeholder="<?= __('project_planning_label_activity_name') ?>">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm mb-1" x-model="act.kpi" :title="act.kpi" placeholder="<?= __('project_planning_label_kpi_target') ?>">
                                                <input type="text" class="form-control form-control-sm" x-model="act.target" :title="act.target" placeholder="<?= __('project_planning_label_kpi_target') ?>">
                                            </td>
                                            <td>
                                                <input type="date" class="form-control form-control-sm" x-model="act.startDate">
                                            </td>
                                            <td>
                                                <input type="date" class="form-control form-control-sm" x-model="act.endDate">
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" class="form-control" x-model="act.weight" @input="adjustWeights(index)" min="0" max="100" :disabled="act.locked">
                                                    <button class="btn" :class="act.locked ? 'btn-danger' : 'btn-outline-secondary'" type="button" @click="toggleLock(index)" title="<?= __('project_planning_title_lock_percentage') ?>">
                                                        <i class="bi" :class="act.locked ? 'bi-lock-fill' : 'bi-unlock'"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeActivity(index)"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr>
                                        <td colspan="7">
                                            <button type="button" class="btn btn-sm btn-outline-success" @click="addActivity()"><i class="bi bi-plus"></i> <?= __('project_planning_btn_add_activity') ?></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold"><?= __('project_planning_label_total_weight') ?></td>
                                        <td class="text-center fw-bold" :class="totalWeight == 100 ? 'text-success' : 'text-danger'">
                                            <span x-text="totalWeight"></span>%
                                        </td>
                                        <td></td>
                                    </tr>
                                        <tr x-show="totalWeight !== 100">
                                        <td colspan="7" class="text-center text-danger small">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            <span x-text="totalWeight > 100 ? <?= json_encode(__('project_planning_warn_weight_exceed')) ?> : <?= json_encode(__('project_planning_warn_weight_less')) ?>"></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('btn_close') ?></button>
                <button type="button" class="btn btn-primary" @click="saveProject()">
                    <span x-text="isEdit ? '<?= __('btn_update') ?>' : '<?= __('btn_save') ?>'"></span>
                </button>
            </div>
        </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('projectWizard', () => ({
            isEdit: false,
            modal: null,
            form: {
                f_projectID: null,
                f_projectName: '',
                f_terasID: '',
                f_ownerStafID: '',
                f_startDate: '',
                f_endDate: '',
                activities: [
                    { id: null, name: '', kpi: '', target: '', weight: 0, locked: false, startDate: '', endDate: '' }
                ]
            },
            get totalWeight() {
                return this.form.activities.reduce((sum, act) => sum + (parseInt(act.weight) || 0), 0);
            },
            init() {
                this.modal = new bootstrap.Modal(document.getElementById('projectModal'));
            },

            openModal() {
                this.isEdit = false;
                this.resetForm();
                this.modal.show();
            },

            resetForm() {
                this.form = {
                    f_projectID: null,
                    f_projectName: '',
                    f_terasID: '',
                    f_ownerStafID: '',
                    f_startDate: '',
                    f_endDate: '',
                    activities: [
                        { id: null, name: '', kpi: '', target: '', weight: 0, locked: false, startDate: '', endDate: '' }
                    ]
                };
            },

            addActivity() {
                this.form.activities.push({ id: null, name: '', kpi: '', target: '', weight: 0, locked: false, startDate: '', endDate: '' });
            },

            removeActivity(index) {
                this.form.activities.splice(index, 1);
            },

            toggleLock(index) {
                this.form.activities[index].locked = !this.form.activities[index].locked;
            },

            adjustWeights(editedIndex) {
                const count = this.form.activities.length;
                if (count < 2) return;

                // 1. Identify locked vs unlocked
                const lockedIndices = this.form.activities
                    .map((act, i) => ({ i, locked: act.locked }))
                    .filter(item => item.locked && item.i !== editedIndex) // Exclude current edit even if locked (should be unlocked to edit)
                    .map(item => item.i);

                // If currently editing item is locked, user shouldn't be able to edit anyway (input disabled),
                // but just in case:
                if (this.form.activities[editedIndex].locked) {
                     // Force value back?? Or just let UI handle it. 
                     // Since input is disabled, this function shouldn't trigger via input.
                     return;
                }

                // 2. Calculate Total Locked Weight
                let totalLockedWeight = 0;
                lockedIndices.forEach(i => {
                    totalLockedWeight += (parseInt(this.form.activities[i].weight) || 0);
                });

                // 3. Get the new value for the edited item
                let currentVal = parseInt(this.form.activities[editedIndex].weight) || 0;
                
                // Clamp current val to what's available (100 - locked)
                const maxAvailable = 100 - totalLockedWeight;
                if (currentVal > maxAvailable) {
                    currentVal = maxAvailable;
                    this.form.activities[editedIndex].weight = currentVal;
                }
                currentVal = Math.max(0, currentVal);

                // 4. Calculate what's left for OTHER UNLOCKED items
                const remainingTotal = 100 - currentVal - totalLockedWeight;
                
                // 5. Identify "other unlocked" activities
                const otherUnlockedIndices = this.form.activities
                    .map((_, i) => i)
                    .filter(i => i !== editedIndex && !this.form.activities[i].locked);
                
                if (otherUnlockedIndices.length === 0) {
                    // No one else to take the slack. 
                    // Use standard validation (total != 100 warning)
                    return;
                }

                if (remainingTotal < 0) {
                    // Should be handled by step 3 clamping, but double check
                     otherUnlockedIndices.forEach(i => this.form.activities[i].weight = 0);
                     return;
                }

                // 6. Distribute remainingTotal among other unlocked
                // Strategy: Distribute proportional to their current weights.
                
                let sumOthers = otherUnlockedIndices.reduce((sum, i) => sum + (parseInt(this.form.activities[i].weight) || 0), 0);
                
                if (sumOthers === 0) {
                    // Case A: Others are all 0 -> Split equally
                    const equalShare = Math.floor(remainingTotal / otherUnlockedIndices.length);
                    let remainder = remainingTotal % otherUnlockedIndices.length;
                    
                    otherUnlockedIndices.forEach(i => {
                        let share = equalShare;
                        if (remainder > 0) { share++; remainder--; }
                        this.form.activities[i].weight = share;
                    });
                } else {
                    // Case B: Others have existing ratios -> Maintain proportion
                    let distributedSoFar = 0;
                    
                    otherUnlockedIndices.forEach((i, idx) => {
                        // Last item acts as the "plug"
                        if (idx === otherUnlockedIndices.length - 1) {
                            this.form.activities[i].weight = remainingTotal - distributedSoFar;
                        } else {
                            const currentWeight = parseInt(this.form.activities[i].weight) || 0;
                            const ratio = currentWeight / sumOthers;
                            const newWeight = Math.round(remainingTotal * ratio);
                            this.form.activities[i].weight = newWeight;
                            distributedSoFar += newWeight;
                        }
                    });
                }
            },

            async editProject(id) {
                // Fetch Data
                const fd = new FormData();
                fd.append('action', 'getProject');
                fd.append('projectID', id);
                fd.append('csrf_token', '<?= $csrf ?>');

                try {
                    let res = await fetch('<?= base_url("ajax/project-planning-handler.php") ?>', {method: 'POST', body: fd});
                    let json = await res.json();
                    
                    if(json.success) {
                        this.isEdit = true;
                        const d = json.data;
                        this.form = {
                            f_projectID: d.f_projectID,
                            f_projectName: d.f_projectName,
                            f_terasID: d.f_terasID,
                            f_ownerStafID: d.f_ownerStafID,
                            f_startDate: d.f_startDate,
                            f_endDate: d.f_endDate,
                            activities: d.activities.map(a => ({
                                id: a.f_aktivitiID,
                                name: a.f_namaAktiviti,
                                kpi: a.f_kpi,
                                target: a.f_target,
                                weight: a.f_weightage,
                                locked: false, // Default to unlocked when editing
                                startDate: a.f_startDate || '', 
                                endDate: a.f_endDate || ''
                            }))
                        };
                        this.modal.show();
                    } else {
                        Swal.fire(<?= json_encode(__('project_planning_alert_error')) ?>, json.message, 'error');
                    }
                } catch(e) { console.error(e); }
            },

            async saveProject() {
                if(this.totalWeight !== 100) {
                    Swal.fire(<?= json_encode(__('project_planning_alert_error')) ?>, <?= json_encode(__('project_planning_alert_weight_must_100')) ?>, 'warning');
                    return;
                }

                const fd = new FormData();
                fd.append('action', 'saveProject');
                fd.append('csrf_token', '<?= $csrf ?>');
                
                // Append Form Data
                Object.keys(this.form).forEach(k => {
                    if (k === 'activities') {
                        // Remove locked property before sending if backend doesn't expect it, 
                        // or backend will just ignore it.
                        fd.append(k, JSON.stringify(this.form[k]));
                    } else if (this.form[k] !== null) {
                        fd.append(k, this.form[k]);
                    }
                });

                try {
                    let res = await fetch('<?= base_url("ajax/project-planning-handler.php") ?>', {method: 'POST', body: fd});
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
