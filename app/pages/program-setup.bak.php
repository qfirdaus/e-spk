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
  
  <!-- Page-specific CSS -->
  <link href="<?= base_url('assets/css/datatables-standard.css') ?>?v=<?= h($version) ?>" rel="stylesheet">
  
  <style>
    /* Card styling */
    .setup-card {
      margin-bottom: 1.5rem;
      border-radius: 0.75rem;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    
    .setup-card .card-header {
      background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
      color: white;
      border-bottom: none;
      padding: 1.25rem 1.5rem;
      border-radius: 0.75rem 0.75rem 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .setup-card .card-title {
      color: white;
      font-weight: 600;
      font-size: 1.1rem;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .setup-card .card-body {
      padding: 1.5rem;
    }
    
    /* Grid layouts */
    .grid-2 {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.25rem;
    }
    
    .grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
    }
    
    @media (max-width: 768px) {
      .grid-2, .grid-3 {
        grid-template-columns: 1fr;
      }
    }
    
    /* Form styling */
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
      display: block;
      font-size: 0.95rem;
    }
    
    .form-control, .form-select {
      min-height: 50px;
      padding: 0.875rem 1rem;
      font-size: 1rem;
      border: 2px solid #e9ecef;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #86b7fe;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    /* Table styling */
    .table-container {
      overflow-x: auto;
    }
    
    .table {
      width: 100%;
      margin-bottom: 0;
    }
    
    .table thead {
      background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
      color: white;
    }
    
    .table thead th {
      font-weight: 700;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 1rem 0.75rem;
      border: none;
      color: white;
    }
    
    .table tbody tr {
      transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
      background: #f8fafc !important;
      transform: scale(1.01);
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    
    .table tbody td {
      padding: 0.875rem 0.75rem;
      border-color: #f1f5f9;
      vertical-align: middle;
    }
    
    /* Badge styling */
    .badge {
      padding: 0.35rem 0.75rem;
      font-size: 0.875rem;
      font-weight: 600;
      border-radius: 0.375rem;
    }
    
    .badge-info {
      background-color: #3b82f6;
      color: white;
    }
    
    .badge-warning {
      background-color: #f59e0b;
      color: white;
    }
    
    .badge-success {
      background-color: #10b981;
      color: white;
    }
    
    /* Button styling */
    .btn-sm {
      padding: 0.375rem 0.75rem;
      font-size: 0.875rem;
    }
    
    /* Highlight card */
    .card-highlight {
      border-left: 5px solid #3b82f6;
    }
  </style>
</head>
<body id="body-layout"
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-2">
          <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
              <h4 class="page-title mb-0">
                <i class="bi bi-gear-fill"></i> <?= __('monitoring_setup_title') ?: 'Penyediaan Sistem Program' ?>
              </h4>
              <div class="page-title-right">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">
                    <i class="ri-home-4-line align-middle me-1"></i> <?= __('dash_sidebar_dashboard') ?: 'Dashboard' ?>
                  </li>
                  <li class="breadcrumb-item active"><?= __('monitoring_setup_title') ?: 'Penyediaan Sistem' ?></li>
                </ol>
              </div>
            </div>
          </div>
        </div>

    <section class="section">
      <!-- Card 1: Master Data Setup -->
      <div class="card setup-card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="bi bi-database-fill"></i> 1. <?= __('monitoring_master_data') ?: 'Maklumat Induk UPNM30' ?>
          </h2>
          <button class="btn btn-light" id="btnSaveProgram">
            <i class="bi bi-save"></i> <?= __('btn_save') ?: 'Simpan Data' ?>
          </button>
        </div>
        <div class="card-body">
          <form id="formProgram">
            <input type="hidden" name="f_programID" id="f_programID" value="<?= h($currentProgram['f_programID'] ?? '') ?>">
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label"><?= __('program_name') ?: 'Nama Program Induk' ?></label>
                <input type="text" class="form-control" name="f_programName" id="f_programName" 
                       value="<?= h($currentProgram['f_programName'] ?? 'UPNM30 Strategik Plan') ?>"
                       placeholder="<?= __('enter_program_name') ?: 'Enter program name' ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label"><?= __('implementation_year') ?: 'Tahun Pelaksanaan' ?></label>
                <select class="form-select" name="f_tahun" id="f_tahun" required>
                  <?php 
                  $currentYear = (int)($currentProgram['f_tahun'] ?? date('Y'));
                  for ($y = 2024; $y <= 2030; $y++): 
                  ?>
                    <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="form-group" style="grid-column: span 2;">
                <label class="form-label"><?= __('program_description') ?: 'Deskripsi Program' ?></label>
                <textarea class="form-control" name="f_description" id="f_description" rows="3" 
                          placeholder="<?= __('program_objective') ?: 'Objektif utama program...' ?>"><?= h($currentProgram['f_description'] ?? '') ?></textarea>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Card 2: Strategy Registration -->
      <div class="card setup-card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="bi bi-stack"></i> 2. <?= __('monitoring_teras_register') ?: 'Daftar Teras Strategik & Special Project' ?>
          </h2>
          <button class="btn btn-light" id="btnAddTeras">
            <i class="bi bi-plus-circle"></i> <?= __('add_teras') ?: 'Tambah Teras' ?>
          </button>
        </div>
        <div class="card-body">
          <div class="table-container">
            <table class="table table-hover" id="terasTable">
              <thead>
                <tr>
                  <th><?= __('teras_code') ?: 'Kod Teras' ?></th>
                  <th><?= __('teras_name') ?: 'Nama Teras / Projek' ?></th>
                  <th><?= __('type') ?: 'Jenis' ?></th>
                  <th><?= __('status') ?: 'Status' ?></th>
                  <th><?= __('actions') ?: 'Tindakan' ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($terasList)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">
                    <?= __('no_data') ?: 'Tiada data' ?>
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach ($terasList as $teras): ?>
                  <tr>
                    <td><strong><?= h($teras['f_kodTeras']) ?></strong></td>
                    <td><?= h($teras['f_namaTeras']) ?></td>
                    <td>
                      <span class="badge <?= $teras['f_jenis'] === 'Teras' ? 'badge-info' : 'badge-warning' ?>">
                        <?= h($teras['f_jenis']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge badge-success">
                        <?= __('status_active') ?: 'Aktif' ?>
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary btnEditTeras" 
                              data-id="<?= h($teras['f_terasID']) ?>"
                              data-kod="<?= h($teras['f_kodTeras']) ?>"
                              data-nama="<?= h($teras['f_namaTeras']) ?>"
                              data-jenis="<?= h($teras['f_jenis']) ?>">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger btnDeleteTeras" 
                              data-id="<?= h($teras['f_terasID']) ?>">
                        <i class="bi bi-trash"></i>
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

      <!-- Card 3: User Management -->
      <div class="card setup-card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="bi bi-people-fill"></i> 3. <?= __('monitoring_user_management') ?: 'Pendaftaran Pengguna & Peranan' ?>
          </h2>
          <a href="senarai-pengguna.php" class="btn btn-light">
            <i class="bi bi-person-plus"></i> <?= __('manage_users') ?: 'Urus Pengguna' ?>
          </a>
        </div>
        <div class="card-body">
          <p class="text-muted">
            <i class="bi bi-info-circle"></i>
            <?= __('user_management_note') ?: 'Pengurusan pengguna penuh boleh diakses melalui halaman Senarai Pengguna.' ?>
          </p>
          <div class="grid-3">
            <div class="text-center p-3 bg-light rounded">
              <h3 class="fw-bold text-primary"><?= count(array_filter($userList, fn($u) => ($u['f_groupKod'] ?? '') === 'ADM-SA')) ?></h3>
              <div class="text-muted"><?= __('role_admin') ?: 'Admin' ?></div>
            </div>
            <div class="text-center p-3 bg-light rounded">
              <h3 class="fw-bold text-info"><?= count(array_filter($userList, fn($u) => strpos($u['f_groupKod'] ?? '', 'PIC') !== false)) ?></h3>
              <div class="text-muted"><?= __('role_pic') ?: 'PIC (Person In Charge)' ?></div>
            </div>
            <div class="text-center p-3 bg-light rounded">
              <h3 class="fw-bold text-success"><?= count($userList) ?></h3>
              <div class="text-muted"><?= __('total_users') ?: 'Jumlah Pengguna' ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 4: System Settings -->
      <div class="card setup-card card-highlight">
        <div class="card-header">
          <h2 class="card-title">
            <i class="bi bi-sliders"></i> 4. <?= __('monitoring_system_settings') ?: 'Tetapan Sistem & Struktur Pelaporan' ?>
          </h2>
          <button class="btn btn-light" id="btnSaveSettings">
            <i class="bi bi-arrow-clockwise"></i> <?= __('update_settings') ?: 'Kemaskini Tetapan' ?>
          </button>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            <?= __('settings_description') ?: 'Konfigurasi formula, kitaran pelaporan, dan parameter sistem.' ?>
          </p>
          <form id="formSettings">
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label"><?= __('progress_formula') ?: 'Formula Status Kemajuan (%)' ?></label>
                <input type="text" class="form-control" readonly
                       style="background-color: #f1f5f9; color: #64748b;"
                       value="[(a/100*p1) + (b/100*p2) + ...]">
                <small class="text-muted"><?= __('formula_note') ?: 'Formula standard ditetapkan oleh sistem.' ?></small>
              </div>
              <div class="form-group">
                <label class="form-label"><?= __('reporting_cycle') ?: 'Kitaran Pelaporan' ?></label>
                <select class="form-select" name="reporting_cycle" id="reporting_cycle">
                  <?php $cycle = $systemSettings['reporting_cycle']['value'] ?? 'monthly'; ?>
                  <option value="monthly" <?= $cycle === 'monthly' ? 'selected' : '' ?>><?= __('monthly') ?: 'Bulanan (Setiap 30hb)' ?></option>
                  <option value="quarterly" <?= $cycle === 'quarterly' ? 'selected' : '' ?>><?= __('quarterly') ?: 'Suku Tahun (Q1, Q2, Q3, Q4)' ?></option>
                  <option value="weekly" <?= $cycle === 'weekly' ? 'selected' : '' ?>><?= __('weekly') ?: 'Mingguan (Setiap Jumaat)' ?></option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label"><?= __('status_color_code') ?: 'Kod Warna Status' ?></label>
                <div style="display: flex; gap: 10px; margin-top: 5px;">
                  <span class="badge bg-danger">0-39% (<?= __('critical') ?: 'Kritikal' ?>)</span>
                  <span class="badge bg-warning">40-79% (<?= __('delayed') ?: 'Lewat' ?>)</span>
                  <span class="badge bg-success">80-100% (<?= __('good') ?: 'Baik' ?>)</span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label"><?= __('official_report_generation') ?: 'Janaan Laporan Rasmi' ?></label>
                <button type="button" class="btn btn-outline-primary w-100" id="btnGenerateReport">
                  <i class="bi bi-file-pdf"></i> <?= __('generate_report') ?: 'Generate Laporan EXCO / LPU' ?>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      </section>

      </div><!-- /.container-fluid -->
    </div><!-- /.content -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
  </div><!-- /.content-page -->
</div><!-- /.wrapper -->

<?php include __DIR__ . '/../includes/script.php'; ?>
  
  <!-- Page-specific JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const csrf = '<?= h($csrf) ?>';
    
    // Save Program
    document.getElementById('btnSaveProgram')?.addEventListener('click', async function() {
      const formData = new FormData(document.getElementById('formProgram'));
      formData.append('action', 'saveProgram');
      formData.append('csrf_token', csrf);
      
      try {
        const response = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: result.message,
            confirmButtonText: 'OK'
          }).then(() => location.reload());
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Ralat',
          text: error.message
        });
      }
    });
    
    // Add/Edit Teras - Modal functionality
    document.getElementById('btnAddTeras')?.addEventListener('click', function() {
      Swal.fire({
        title: 'Tambah Teras Strategik',
        html: `
          <input id="swal-kod" class="swal2-input" placeholder="Kod Teras (cth: TS-01)">
          <input id="swal-nama" class="swal2-input" placeholder="Nama Teras">
          <select id="swal-jenis" class="swal2-select">
            <option value="Teras">Teras Strategik</option>
            <option value="Special Project">Special Project</option>
          </select>
        `,
        confirmButtonText: 'Simpan',
        showCancelButton: true,
        cancelButtonText: 'Batal',
        preConfirm: () => {
          return {
            f_kodTeras: document.getElementById('swal-kod').value,
            f_namaTeras: document.getElementById('swal-nama').value,
            f_jenis: document.getElementById('swal-jenis').value,
            f_programID: <?= $currentProgramID ?? 'null' ?>
          }
        }
      }).then(async (result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('action', 'saveTeras');
          formData.append('csrf_token', csrf);
          Object.keys(result.value).forEach(key => {
            formData.append(key, result.value[key]);
          });
          
          try {
            const response = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', {
              method: 'POST',
              body: formData
            });
            const data = await response.json();
            
            if (data.success) {
              Swal.fire('Berjaya!', data.message, 'success').then(() => location.reload());
            } else {
              throw new Error(data.message);
            }
          } catch (error) {
            Swal.fire('Ralat', error.message, 'error');
          }
        }
      });
    });
    
    // Save Settings
    document.getElementById('btnSaveSettings')?.addEventListener('click', async function() {
      const formData = new FormData(document.getElementById('formSettings'));
      formData.append('action', 'updateSettings');
      formData.append('csrf_token', csrf);
      
      try {
        const response = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: result.message
          });
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Ralat',
          text: error.message
        });
      }
    });

    // ✅ FIXED: Add Event Delegation for Edit/Delete Buttons
    document.getElementById('terasTable')?.addEventListener('click', function(e) {
      // Handle Edit Click
      const editBtn = e.target.closest('.btnEditTeras');
      if (editBtn) {
        const id = editBtn.dataset.id;
        const kod = editBtn.dataset.kod;
        const nama = editBtn.dataset.nama;
        const jenis = editBtn.dataset.jenis;

        Swal.fire({
          title: 'Kemaskini Teras',
          html: `
            <input id="swal-kod" class="swal2-input" placeholder="Kod Teras" value="${kod}">
            <input id="swal-nama" class="swal2-input" placeholder="Nama Teras" value="${nama}">
            <select id="swal-jenis" class="swal2-select">
              <option value="Teras" ${jenis === 'Teras' ? 'selected' : ''}>Teras Strategik</option>
              <option value="Special Project" ${jenis === 'Special Project' ? 'selected' : ''}>Special Project</option>
            </select>
          `,
          confirmButtonText: 'Kemaskini',
          showCancelButton: true,
          preConfirm: () => {
            return {
              f_terasID: id,
              f_kodTeras: document.getElementById('swal-kod').value,
              f_namaTeras: document.getElementById('swal-nama').value,
              f_jenis: document.getElementById('swal-jenis').value,
              f_programID: <?= $currentProgramID ?? 'null' ?>
            }
          }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'saveTeras');
                formData.append('csrf_token', csrf);
                // Append all updated fields
                Object.keys(result.value).forEach(key => formData.append(key, result.value[key]));

                try {
                    const response = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', { method: 'POST', body: formData });
                    const res = await response.json();
                    if(res.success) {
                        Swal.fire('Berjaya!', res.message, 'success').then(() => location.reload());
                    } else {
                        throw new Error(res.message);
                    }
                } catch(err) {
                    Swal.fire('Ralat', err.message, 'error');
                }
            }
        });
      }

      // Handle Delete Click
      const deleteBtn = e.target.closest('.btnDeleteTeras');
      if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        Swal.fire({
          title: 'Anda pasti?',
          text: "Data ini akan diletakkan dalam status 'Deleted'.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          confirmButtonText: 'Ya, padam!'
        }).then(async (result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'deleteTeras');
            formData.append('terasID', id);
            formData.append('csrf_token', csrf);

            try {
                const response = await fetch('<?= base_url("ajax/program-setup-handler.php") ?>', { method: 'POST', body: formData });
                const res = await response.json();
                if(res.success) {
                    Swal.fire('Dipadam!', res.message, 'success').then(() => location.reload());
                } else {
                    throw new Error(res.message);
                }
            } catch(err) {
                Swal.fire('Ralat', err.message, 'error');
            }
          }
        });
      }
    });

    // Generate Report Placeholder
    document.getElementById('btnGenerateReport')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Janaan Laporan',
            text: 'Fungsi janaan laporan PDF (EXCO/LPU) akan tersedia dalam kemaskini akan datang (Fasa 5).',
            icon: 'info'
        });
    });

  </script>
</body>
</html>
