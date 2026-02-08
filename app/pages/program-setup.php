<?php
// pages/program-setup.php
declare(strict_types=1);

// 1. ✅ INITIALIZATION - Always load init.php first
require_once __DIR__ . '/../includes/init.php';

// 2. ✅ AUTHENTICATION - Require login
require_login();

// 3. ✅ AUTHORIZATION - Check if user has admin rights
$currentUserGroup = $profile['f_groupKod'] ?? '';
$allowedRoles = ['ADM-SA', 'ADM-HR'];
if (!in_array($currentUserGroup, $allowedRoles)) {
    redirect('pages/dashboard.php');
}

// 4. ✅ LOAD CONTROLLER
require_once __DIR__ . '/../controllers/ProgramSetupController.php';
$controller = new ProgramSetupController();

// 5.✅ EXTRACT DATA FROM CONTROLLER
$lang = $controller->lang ?? 'ms';
$profile = $controller->profile ?? [];
$programList = $controller->programList ?? [];
$terasList = $controller->terasList ?? [];
$userList = $controller->userList ?? [];
$systemSettings = $controller->systemSettings ?? [];

// 6. ✅ CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

// 7. ✅ HTML HELPER FUNCTION
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// 8. ✅ Get current program (default to latest)
$currentProgram = !empty($programList) ? $programList[0] : [];
$currentProgramID = $currentProgram['f_programID'] ?? null;
?>
<!DOCTYPE html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <meta name="csrf-token" content="<?= h($csrf) ?>">
  
  <style>
    /* Neo Styles Consolidation */
    :root{--neo-base:255,255,255;--neo-ink:0,0,0}
    [data-bs-theme="dark"]{--neo-base:30,32,36;--neo-ink:255,255,255}

    .neo-card {
        background: linear-gradient(180deg, rgba(var(--neo-base),.8) 0%, rgba(var(--neo-base),.6) 100%);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(var(--neo-ink),.08);
        box-shadow: 0 10px 40px rgba(0,0,0,.05);
        border-radius: 20px;
        padding: 0;
        overflow: hidden;
    }
    
    .neo-card-header {
        padding: 1.25rem 2rem;
        border-bottom: 1px solid rgba(var(--neo-ink),.06);
        background: rgba(var(--neo-ink),.01);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .neo-card-body { padding: 2rem; }

    /* Neo Tabs */
    .neo-nav-pills {
        display: inline-flex;
        background: rgba(var(--neo-ink), .04);
        padding: 5px;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    .neo-nav-pills .nav-link {
        border-radius: 8px;
        padding: 0.6rem 1.2rem;
        color: rgba(var(--neo-ink), .6);
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .neo-nav-pills .nav-link:hover { color: rgba(var(--neo-ink), .9); }
    .neo-nav-pills .nav-link.active {
        background: #fff;
        color: #2563eb;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        font-weight: 600;
    }
    [data-bs-theme="dark"] .neo-nav-pills .nav-link.active { background: #2d3035; color: #60a5fa; }

    /* Neo Table */
    .neo-table { width: 100%; border-collapse: separate; border-spacing: 0 0.5rem; }
    .neo-table th {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(var(--neo-ink),.5);
        font-weight: 600; padding: 0 1rem 0.75rem; border: none; text-align: left;
    }
    .neo-table td {
        background: rgba(var(--neo-base),.5);
        padding: 1rem; vertical-align: middle;
        border-top: 1px solid rgba(var(--neo-ink),.03);
        border-bottom: 1px solid rgba(var(--neo-ink),.03);
    }
    .neo-table td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 1px solid rgba(var(--neo-ink),.03); }
    .neo-table td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-right: 1px solid rgba(var(--neo-ink),.03); }
    .neo-table tr:hover td { background: rgba(59, 130, 246,.03); transform: translateY(-1px); transition: all .2s; }

    /* Neo Badge */
    .neo-badge {
        display: inline-flex; align-items: center; padding: 0.35em 0.8em;
        font-size: 0.75rem; font-weight: 600; line-height: 1;
        border-radius: 50rem; letter-spacing: 0.3px;
    }
    .neo-badge.bg-success { background: rgba(16, 185, 129, 0.1) !important; color: #059669; }
    .neo-badge.bg-primary { background: rgba(59, 130, 246, 0.1) !important; color: #2563eb; }
    .neo-badge.bg-warning { background: rgba(245, 158, 11, 0.1) !important; color: #d97706; }
    .neo-badge.bg-info { background: rgba(14, 165, 233, 0.1) !important; color: #0284c7; }
    .neo-badge.bg-danger { background: rgba(239, 68, 68, 0.12) !important; color: #b91c1c; }

    /* Form Styles */
    .neo-label { font-weight: 600; font-size: 0.85rem; color: rgba(var(--neo-ink), .7); margin-bottom: 0.5rem; }
    .neo-input {
        border-radius: 10px; border: 1px solid rgba(var(--neo-ink), .1);
        padding: 0.7rem 1rem; font-size: 0.95rem; background: rgba(var(--neo-base), .5);
        transition: all 0.2s;
    }
    .neo-input:focus {
        border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); background: #fff;
    }
  </style>
    <style>
        /* Ensure Bootstrap modal appears above optional global loader-overlay */
        .modal { z-index: 3100 !important; }
        .modal-backdrop { z-index: 3000 !important; }
    </style>
</head>
<body data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>" data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>" data-layout="vertical" class="loading">

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
                                    <?= h(__('program_setup_title') ?: 'Penyediaan Sistem Program') ?>
              </h4>
              <nav aria-label="breadcrumb">
                  <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboard.php"><?= h(__('dashboard_breadcrumb') ?: 'Dashboard') ?></a></li>
                                            <li class="breadcrumb-item active"><?= h(__('program_setup_breadcrumb_settings') ?: 'Tetapan') ?></li>
                  </ol>
              </nav>
            </div>
          </div>
        </div>

        <!-- Main Neo Card -->
        <div class="neo-card">
            
            <div class="neo-card-body">
                <!-- Tabs -->
                <ul class="nav neo-nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-general-tab" data-bs-toggle="pill" data-bs-target="#pills-general" type="button" role="tab"><i class="ri-settings-3-line me-1"></i> <?= h(__('program_setup_tab_general') ?: 'Tetapan Umum') ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-teras-tab" data-bs-toggle="pill" data-bs-target="#pills-teras" type="button" role="tab"><i class="ri-stack-line me-1"></i> <?= h(__('program_setup_tab_teras') ?: 'Teras Strategik') ?></button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    
                    <!-- TAB 1: General Settings (Split Layout) -->
                    <div class="tab-pane fade show active" id="pills-general" role="tabpanel">
                        <div class="row g-5">
                            <!-- Left: Program info -->
                            <div class="col-lg-7">
                                <h5 class="mb-4 text-primary"><i class="ri-file-list-3-line me-2"></i> <?= h(__('program_setup_section_general') ?: 'Maklumat Induk UPNM30') ?></h5>
                                <form id="formProgram">
                                    <input type="hidden" name="f_programID" value="<?= h($currentProgram['f_programID'] ?? '') ?>">
                                    <div class="mb-3">
                                        <label class="neo-label"><?= h(__('program_setup_label_program_name') ?: 'Nama Program') ?></label>
                                        <input type="text" class="form-control neo-input" name="f_programName" value="<?= h($currentProgram['f_programName'] ?? __('program_setup_default_program_name')) ?>" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="neo-label"><?= h(__('program_setup_label_year') ?: 'Tahun Pelaksanaan') ?></label>
                                            <select class="form-select neo-input" name="f_tahun">
                                                <?php $curY = (int)($currentProgram['f_tahun'] ?? date('Y')); for($y=2024; $y<=2030; $y++): ?>
                                                <option value="<?= $y ?>" <?= $y==$curY?'selected':''?>><?= $y ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="neo-label"><?= h(__('program_setup_label_description') ?: 'Deskripsi') ?></label>
                                        <textarea class="form-control neo-input" name="f_description" rows="3"><?= h($currentProgram['f_description'] ?? '') ?></textarea>
                                    </div>
                                    <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSaveProgram"><?= h(__('program_setup_btn_save_program') ?: __('save_changes')) ?></button>
                                </form>
                            </div>

                            <!-- Right: System Settings -->
                            <div class="col-lg-5" style="border-left: 1px solid rgba(0,0,0,0.05);">
                                <h5 class="mb-4 text-info"><i class="ri-equalizer-line me-2"></i> <?= h(__('program_setup_section_system_settings') ?: 'Konfigurasi Sistem') ?></h5>
                                <form id="formSettings">
                                    <div class="mb-3">
                                        <label class="neo-label"><?= h(__('program_setup_label_reporting_cycle') ?: 'Kitaran Pelaporan') ?></label>
                                        <select class="form-select neo-input" name="reporting_cycle">
                                            <?php $cycle = $systemSettings['reporting_cycle']['value'] ?? 'monthly'; ?>
                                            <option value="monthly" <?= $cycle=='monthly'?'selected':''?>><?= h(__('program_setup_cycle_monthly') ?: 'Bulanan (Setiap 30hb)') ?></option>
                                            <option value="quarterly" <?= $cycle=='quarterly'?'selected':''?>><?= h(__('program_setup_cycle_quarterly') ?: 'Suku Tahun (Q1-Q4)') ?></option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="neo-label"><?= h(__('program_setup_label_color_code') ?: 'Kod Warna Status') ?></label>
                                        <div class="d-flex gap-2 flex-wrap mt-2">
                                            <span class="neo-badge bg-danger"><?= h(__('program_setup_badge_critical') ?: '0-39% (Kritikal)') ?></span>
                                            <span class="neo-badge bg-warning"><?= h(__('program_setup_badge_delayed') ?: '40-79% (Lewat)') ?></span>
                                            <span class="neo-badge bg-success"><?= h(__('program_setup_badge_good') ?: '80-100% (Baik)') ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-4 p-3 rounded" style="background: rgba(59,130,246,0.05); border: 1px dashed rgba(59,130,246,0.2);">
                                        <h6 class="text-primary font-13 fw-bold mb-2"><?= h(__('program_setup_section_reports') ?: 'Janaan Laporan Rasmi') ?></h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100" id="btnGenerateReport"><?= h(__('program_setup_btn_generate_pdf') ?: 'Generate PDF (EXCO/LPU)') ?></button>
                                    </div>
                                    <div class="mt-3 text-end">
                                        <button type="button" class="btn btn-light rounded-pill px-3" id="btnSaveSettings"><?= __('update_settings') ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Teras Strategik -->
                    <div class="tab-pane fade" id="pills-teras" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="m-0 text-dark"><?= h(__('program_setup_teras_title') ?: 'Senarai Teras & Projek Khas') ?></h5>
                            <button class="btn btn-primary rounded-pill btn-sm px-3" id="btnAddTeras"><i class="ri-add-line me-1"></i> <?= h(__('program_setup_btn_add_teras') ?: 'Tambah Baru') ?></button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="neo-table">
                                <thead>
                                        <tr>
                                                <th class="text-center" style="width: 5%"><?= h(__('program_setup_th_code') ?: 'Kod') ?></th>
                                                <th class="text-start" style="width: 40%"><?= h(__('program_setup_th_name') ?: 'Nama Teras / Projek') ?></th>
                                                <th class="text-start" style="width: 25%"><?= h(__('program_setup_th_owner') ?: 'Pemilik') ?></th>
                                                <th class="text-start" style="width: 10%"><?= h(__('program_setup_th_type') ?: 'Jenis') ?></th>
                                                <th class="text-center" style="width: 10%"><?= h(__('program_setup_th_status') ?: 'Status') ?></th>
                                                <th class="text-center" style="width: 10%"><?= h(__('program_setup_th_actions') ?: 'Tindakan') ?></th>
                                            </tr>
                                </thead>
                                <tbody id="terasTable">
                                <?php if(empty($terasList)): ?>
                                    <tr><td colspan="6" class="text-center p-4 opacity-50"><?= h(__('program_setup_no_data') ?: 'Belum ada data didaftarkan.') ?></td></tr>
                                <?php else: foreach($terasList as $t): ?>
                                    <tr>
                                        <td class="text-center"><?= getTerasKodBadgeHtml($t['f_kodTeras']) ?></td>
                                        <td class="text-start"><?= h($t['f_namaTeras']) ?></td>
                                        <td class="text-start">
                                            <?php if (!empty($t['ownerName'])): ?>
                                                <span class="neo-badge bg-primary"><i class="ri-user-line me-1"></i><?= h($t['ownerName']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted font-13">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start"><span class="neo-badge <?= $t['f_jenis']=='Teras'?'bg-info':'bg-warning' ?>"><?= h($t['f_jenis']) ?></span></td>
                                        <td class="text-center"><span class="neo-badge bg-success"><?= h(__('program_setup_status_active') ?: 'Aktif') ?></span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light rounded-circle btnEditTeras" 
                                                data-id="<?= $t['f_terasID'] ?>" data-kod="<?= h($t['f_kodTeras']) ?>" 
                                                data-nama="<?= h($t['f_namaTeras']) ?>" data-jenis="<?= h($t['f_jenis']) ?>" 
                                                data-owner="<?= h($t['f_ownerStafID'] ?? '') ?>"><i class="ri-pencil-fill"></i></button>
                                            <button class="btn btn-sm btn-light rounded-circle text-danger btnDeleteTeras" data-id="<?= $t['f_terasID'] ?>"><i class="ri-delete-bin-line"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                                        <!-- Modal: Add / Edit Teras -->
                                        <div class="modal fade" id="modalTeras" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalTerasTitle"><?= h(__('program_setup_swal_add_title') ?: 'Tambah Teras') ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form id="modalTerasForm">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="f_terasID" id="modal-teras-id" value="">
                                                            <input type="hidden" name="f_programID" id="modal-program-id" value="<?= $currentProgramID ?? '' ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label neo-label"><?= h(__('program_setup_th_code') ?: 'Kod') ?></label>
                                                                <input type="text" class="form-control neo-input" id="modal-kod" name="f_kodTeras" placeholder="<?= h(__('teras_code_placeholder') ?: 'Kod Teras') ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label neo-label"><?= h(__('program_setup_th_name') ?: 'Nama Teras / Projek') ?></label>
                                                                <input type="text" class="form-control neo-input" id="modal-nama" name="f_namaTeras" placeholder="<?= h(__('program_setup_placeholder_teras_name') ?: 'Nama Teras') ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label neo-label"><?= h(__('program_setup_th_type') ?: 'Jenis') ?></label>
                                                                <select class="form-select neo-input" id="modal-jenis" name="f_jenis">
                                                                    <option value="Teras"><?= h(__('program_setup_type_teras') ?: 'Teras Strategik') ?></option>
                                                                    <option value="Special Project"><?= h(__('program_setup_type_special') ?: 'Special Project') ?></option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label neo-label"><?= h(__('program_setup_th_owner') ?: 'Pemilik Teras') ?></label>
                                                                <select class="form-select neo-input" id="modal-owner" name="f_ownerStafID">
                                                                    <option value="">-- <?= h(__('select_owner') ?: 'Pilih Pemilik') ?> --</option>
                                                                    <?php foreach($userList as $user): ?>
                                                                        <option value="<?= h($user['f_stafID']) ?>"><?= h($user['f_nama']) ?> (<?= h($user['f_groupKod'] ?? '') ?>)</option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <small class="text-muted"><?= h(__('program_setup_owner_help') ?: 'Pemilik teras boleh mengedit semua projek di bawah teras ini') ?></small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= h(__('cancel') ?: 'Batal') ?></button>
                                                            <button type="submit" class="btn btn-primary" id="modalTerasSave"><?= h(__('program_setup_btn_save') ?: 'Simpan') ?></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>



                </div><!-- end tab-content -->
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
const csrf = '<?= h($csrf) ?>';

// JS translations for this page (built in PHP and injected as JSON)
<?php
$ps_i18n = [
    'alert_success' => __('program_setup_alert_success') ?: 'Berjaya',
    'alert_error' => __('program_setup_alert_error') ?: 'Ralat',
    'swal_add_title' => __('program_setup_swal_add_title') ?: 'Tambah Teras',
    'swal_edit_title' => __('program_setup_swal_edit_title') ?: 'Kemaskini',
    'swal_btn_save' => __('program_setup_btn_save') ?: 'Simpan',
    'swal_confirm_delete_title' => __('program_setup_confirm_delete_title') ?: 'Pasti?',
    'swal_confirm_delete_text' => __('program_setup_confirm_delete_text') ?: 'Data akan dipadam.'
];
?>
window.PS_I18N = <?= json_encode($ps_i18n, JSON_UNESCAPED_UNICODE) ?>;

        // Save Program
        document.getElementById('btnSaveProgram')?.addEventListener('click', async function() {
            const formData = new FormData(document.getElementById('formProgram'));
            formData.append('action', 'saveProgram');
            formData.append('csrf_token', csrf);
            try {
                const res = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', {method:'POST', body:formData});
                const result = await res.json();
                if(result.success) Swal.fire(PS_I18N.alert_success || 'Berjaya', result.message, 'success').then(()=>location.reload());
                else throw new Error(result.message);
            } catch(e) { Swal.fire(PS_I18N.alert_error || 'Ralat', e.message, 'error'); }
        });

        // Save Settings
        document.getElementById('btnSaveSettings')?.addEventListener('click', async function() {
            const formData = new FormData(document.getElementById('formSettings'));
            formData.append('action', 'updateSettings');
            formData.append('csrf_token', csrf);
            try {
                const res = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', {method:'POST', body:formData});
                const result = await res.json();
                if(result.success) Swal.fire(PS_I18N.alert_success || 'Berjaya', result.message, 'success');
                else throw new Error(result.message);
            } catch(e) { Swal.fire(PS_I18N.alert_error || 'Ralat', e.message, 'error'); }
        });

        // Add Teras: ensure modal lives under <body> (avoid ancestor stacking context issues)
        const modalTerasEl = document.getElementById('modalTeras');
        if (modalTerasEl && modalTerasEl.parentNode !== document.body) {
            document.body.appendChild(modalTerasEl);
        }
        const modalTeras = modalTerasEl ? new bootstrap.Modal(modalTerasEl, { backdrop: true, keyboard: true }) : null;

        // Ensure any loader overlays are removed while modal is open
        if (modalTerasEl) {
            modalTerasEl.addEventListener('show.bs.modal', function () {
                // Remove any blocking overlays or global loader
                document.querySelectorAll('.loader-overlay, #global-loader').forEach(el => {
                    try { el.remove(); } catch (e) { el.style.display = 'none'; el.style.pointerEvents = 'none'; }
                });
                // Remove any existing modal backdrops to avoid stale/backdrop stacking
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                // Also remove any stray full-page elements that may block clicks
                document.querySelectorAll('[data-blocking-loader]').forEach(el => el.remove());
            });
            modalTerasEl.addEventListener('hidden.bs.modal', function () {
                // Remove any leftover backdrops and restore scroll/body state
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                // Safety: ensure AppLoader isn't stuck showing
                if (window.AppLoader && typeof window.AppLoader.hide === 'function') {
                    try { window.AppLoader.hide(); } catch (e) {}
                }
            });
        }
        document.getElementById('btnAddTeras')?.addEventListener('click', function() {
            document.getElementById('modalTerasTitle').textContent = PS_I18N.swal_add_title || 'Tambah Teras';
            document.getElementById('modal-teras-id').value = '';
            document.getElementById('modal-kod').value = '';
            document.getElementById('modal-nama').value = '';
            document.getElementById('modal-jenis').value = 'Teras';
            document.getElementById('modal-owner').value = '';
            // Safety: hide and disable any global loader overlay that may block clicks
            document.querySelectorAll('.loader-overlay').forEach(el => {
                try { el.classList.remove('show'); } catch(e){}
                el.style.display = 'none';
                el.style.pointerEvents = 'none';
            });
            modalTeras?.show();
        });

        // Modal form submit → save Teras via AJAX
        document.getElementById('modalTerasForm')?.addEventListener('submit', async function(ev) {
            ev.preventDefault();
            const form = ev.target;
            const fd = new FormData(form);
            fd.append('action', 'saveTeras');
            fd.append('csrf_token', csrf);

            try {
                const res = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', { method: 'POST', body: fd });
                const data = await res.json();
                if (data && data.success) {
                    Swal.fire(PS_I18N.alert_success || 'Berjaya', data.message || '', 'success');
                    modalTeras?.hide();
                    setTimeout(() => location.reload(), 400);
                } else {
                    throw new Error((data && data.message) ? data.message : 'Ralat semasa menyimpan');
                }
            } catch (err) {
                Swal.fire(PS_I18N.alert_error || 'Ralat', err.message || String(err), 'error');
            }
        });

        // Edit/Delete Delegation
        document.getElementById('terasTable')?.addEventListener('click', function(e) {
            // Edit
            const editBtn = e.target.closest('.btnEditTeras');
            if (editBtn) {
                document.getElementById('modalTerasTitle').textContent = PS_I18N.swal_edit_title || 'Kemaskini';
                document.getElementById('modal-teras-id').value = editBtn.dataset.id || '';
                document.getElementById('modal-kod').value = editBtn.dataset.kod || '';
                document.getElementById('modal-nama').value = editBtn.dataset.nama || '';
                document.getElementById('modal-jenis').value = editBtn.dataset.jenis || 'Teras';
                document.getElementById('modal-owner').value = editBtn.dataset.owner || '';
                // Safety: hide and disable any global loader overlay that may block clicks
                document.querySelectorAll('.loader-overlay').forEach(el => {
                    try { el.classList.remove('show'); } catch(e){}
                    el.style.display = 'none';
                    el.style.pointerEvents = 'none';
                });
                modalTeras?.show();
            }
      
            // Delete
            const delBtn = e.target.closest('.btnDeleteTeras');
            if (delBtn) {
                    Swal.fire({
                            title: PS_I18N.swal_confirm_delete_title || 'Pasti?', text: PS_I18N.swal_confirm_delete_text || 'Data akan dipadam.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33'
                    }).then(async(res)=>{
                            if(res.isConfirmed){
                                    const fd = new FormData(); fd.append('action','deleteTeras'); fd.append('terasID',delBtn.dataset.id); fd.append('csrf_token',csrf);
                                    await fetch('<?= base_url("ajax/program-setup-handler.php") ?>',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
                                            if(d.success) Swal.fire(PS_I18N.alert_success || 'Padam',d.message,'success').then(()=>location.reload());
                                    });
                            }
                    });
            }
        });
</script>
</body>
</html>
