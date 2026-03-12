<?php
// pages/m-anugerah-konvo.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();
// Controller
require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../includes/functions-page.php';

?>

<!DOCTYPE html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php
    $NEED_DATERANGE  = false;
    $NEED_VECTORMAP  = false;
    $NEED_DATATABLES = true;
    $NEED_SELECT2    = false;
    include __DIR__ . '/../includes/head.php';
  ?>
  <!-- ✅ Standard DataTables CSS (shared) -->
  <link href="<?= base_url('assets/css/datatables-standard.css') ?>?v=<?= h($version) ?>" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>  
  <link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet">
</head>
<body
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.sidebar'] ?? 'dark') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <!-- Title + breadcrumb -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
              <h4 class="page-title">
                <i class="ri-user-3-line me-1"></i>
                <?= h(tr('profile_title','Profil Pengguna')) ?>
              </h4>
              <div class="page-title-right">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">
                    <a href="<?= base_url('pages/dashboard.php') ?>">
                      <i class="ri-home-4-line align-middle me-1"></i>
                      <?= h(tr('profile_breadcrumb_dashboard','Papan Pemuka')) ?>
                    </a>
                  </li>
                  <li class="breadcrumb-item active">
                    <?= h(tr('profile_breadcrumb','Profil')) ?>
                  </li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <?php
          $avatarUrl = $profileView['avatar_url'] ?? base_url('assets/images/no-image.jpg');
          $namaPenuh = $profileView['nama_penuh'] ?? '';
          $nickname  = $profileView['nickname']   ?? '';
          $jawatan   = $profileView['jawatan']    ?? '';
          $gred      = $profileView['gred']       ?? '';
          $jabatan   = $profileView['jabatan']    ?? '';
          $stafID    = $profileView['stafID']     ?? '';
          $nopek     = $profileView['nopekerja']  ?? '';
          $emel      = $profileView['emel']       ?? '';
          $jawGred   = trim($jawatan . ($gred ? ' • '.$gred : ''));
          
          // Check active session status
          $isActive = hasActiveSession($loginActivity);
        ?>
        <!-- Profile Card with Tabs -->
        <div class="card border-0 shadow-sm profile-card">
          <div class="profile-hero">
            <div class="d-flex align-items-center gap-3 flex-wrap position-relative">
              <div class="position-relative">
                <img src="<?= h($avatarUrl) ?>"
                     alt="<?= h(tr('profile_avatar_alt','Avatar pengguna')) ?>"
                     class="avatar"
                     onerror="this.onerror=null;this.src='<?= h(base_url('assets/images/no-image.jpg')) ?>';">
                <span class="status-dot <?= $isActive ? 'status-active' : 'status-inactive' ?>"
                      title="<?= h($isActive ? tr('profile_status_active','Aktif') : tr('profile_status_inactive','Tidak Aktif')) ?>"></span>
              </div>

              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="display-name fs-4 mb-0">
                    <?= h($namaPenuh !== '' ? $namaPenuh : '—') ?>
                  </span>
                </div>

                <div class="subline mt-1">
                  <?php if ($jawGred !== ''): ?>
                    <span class="chip">
                      <i class="ri-briefcase-2-line"></i><?= h($jawGred) ?>
                    </span>
                  <?php endif; ?>
                  <?php if ($jabatan !== ''): ?>
                    <span class="chip">
                      <i class="ri-building-2-line"></i><?= h($jabatan) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="quick-actions d-flex align-items-center gap-2 ms-auto">
                <?php if ($stafID !== ''): ?>
                  <button class="btn btn-sm btn-copy-staf" 
                          type="button"
                          aria-label="<?= h(tr('profile_btn_copy_no_staf','Salin No. Staf')) ?>"
                          data-copy-value="<?= h($stafID) ?>">
                    <i class="ri-file-copy-2-line me-1" aria-hidden="true"></i>
                    <?= h(tr('profile_btn_copy_no_staf','Salin No. Staf')) ?>
                  </button>
                <?php endif; ?>

                <?php if ($emel !== ''): ?>
                  <button class="btn btn-sm btn-copy-email" 
                          type="button"
                          aria-label="<?= h(tr('profile_btn_copy_email','Salin Emel')) ?>"
                          data-copy-value="<?= h($emel) ?>">
                    <i class="ri-clipboard-line me-1" aria-hidden="true"></i>
                    <?= h(tr('profile_btn_copy_email','Salin Emel')) ?>
                  </button>
                <?php endif; ?>
                
                <!-- refresh button removed (redundant near copy buttons) -->
              </div>
            </div>
          </div>

          <!-- Tab Navigasi -->
          <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#maklumat-peribadi-tab" role="tab">
                <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_profil_pengguna','Maklumar Peribadi')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#penglibatan-program-tab" role="tab">
                <i class="ri-hospital-line me-1"></i> <?= h(tr('tab_maklumat_penerima','Maklumat Hospital')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#perakauan-pemohon-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_perakuan_pemohon','Perakuan Pemohon')) ?>
              </a>
            </li>
          </ul>

          <!-- Kandungan Tab -->
          <div class="tab-content p-4">
            <?php if ($errorMessage): ?>
              <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <div>
                  <?= h($errorMessage) ?>
                </div>
              </div>
            <?php endif; ?>
            
            <?php if ($stafID === '' && !$errorMessage): ?>
              <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="ri-alert-line me-2"></i>
                <div>
                  <?= h(tr(
                    'profile_empty_notice',
                    'Profil tidak dijumpai. Sesi login mungkin tamat atau rekod tiada.'
                  )) ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Tab 1: Maklumat Peribadi -->
            <div class="tab-pane fade show active" id="maklumat-peribadi-tab" role="tabpanel">
              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row">
                  <div class="col-12 col-md-8">
                    <!-- Nama Pemohon -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama','Nama Pemohon')) ?></label>
                      <div class="col-sm-8">
                         <input type="text" name="nama_penuh" class="form-control" value="<?= h($namaPenuh) ?>" readonly>
                      </div>                 
                    </div>

                    <!-- No Kad Pengenalan -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_kad_pengenalan','No Kad Pengenalan')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="no_kad_pengenalan" class="form-control" value="<?= h($noKadPengenalan) ?>" readonly>
                      </div>
                    </div>

                    <!-- No Matrik -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_matrik','No. Matrik')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="no_matrik" class="form-control" value="<?= h($nomatrik) ?>" readonly>
                      </div>
                    </div>

                    <!-- Telefon -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_telefon','No Telefon')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="no_telefon" class="form-control" value="<?= h($notel) ?>" readonly>
                      </div>
                    </div>

                    <!-- Emel -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_emel','Emel')) ?></label>
                      <div class="col-sm-8">
                        <input type="email" name="emel" class="form-control" value="<?= h($emel) ?>" readonly>
                      </div>
                    </div>

                    <!-- Fakulti -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kewarganegaraan','Fakulti / Pusat / Akademi')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="kewarganegaraan" class="form-control" value="<?= h($kewarganegaraan) ?>" readonly>
                      </div>
                    </div>

                    <!-- Program -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_program','Program Pengajian')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="program" class="form-control" value="<?= h($program) ?>" readonly>
                      </div>
                    </div>

                    <!-- Sesi Akademik Masuk -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sesi_akademik_masuk','Sesi Akademik Masuk')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="sesi_akademik_masuk" class="form-control" value="<?= h($sesiAkademikMula) ?>" readonly>
                      </div>
                    </div>

                    <!-- Sesi Akademik Tamat -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sesi_akademik_tamat','Sesi Akademik Tamat')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="sesi_akademik_tamat" class="form-control" value="<?= h($sesiAkademikTamat) ?>" readonly>
                      </div>
                    </div>

                    <!-- Pembiayaan Pengajian-->   
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_pembiayaan','Pembiayaan Pengajian')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="pembiayaan" class="form-control" value="<?= h($pembiayaan) ?>" readonly>
                      </div>
                    </div>
                    
                    <!-- Status Pengajian -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pengajian','Status Pengajian')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="status_pengajian" class="form-control" value="<?= h($statusPengajian) ?>" readonly>
                      </div>
                    </div>

                    <!-- Status Pelajar -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pelajar','Status Pelajar')) ?></label>
                      <div class="col-sm-8">
                         <input type="text" name="status_pelajar" class="form-control" value="<?= h($statusPelajar) ?>" readonly>
                      </div>
                    </div>

                    <!-- Submit Button -->
                    <!-- <div class="mb-3 row">
                      <div class="col-sm-8 offset-sm-4">
                        <button type="submit" class="btn btn-primary px-4">
                          <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>
                    </div> -->

                  </div>
                </div>
              </form>
            </div>

            <!-- Tab 2: Maklumat Hospital -->
            <div class="tab-pane fade" id="penglibatan-program-tab" role="tabpanel">
              <div id="auditEventsLoading" class="skeleton-loader" style="display: none;">
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
              </div>

              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row ">
                  <div class="col-12 col-md-8">

                    <!-- Kelayakan Pembiayaan (RM) -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kelayakan_pembiayaan','Kelayakan Pembiayaan (RM)')) ?></label>
                      <div class="col-sm-8">
                         <input type="text" name="nama_hospital" class="form-control" value="<?= h($kelayakanPembiayaan) ?>" readonly>
                      </div>
                    </div>

                    <!-- Nama Hospital -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama_hospital','Nama Hospital')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="nama_hospital" class="form-control" value="<?= h($namaHospital) ?>" >
                      </div>
                    </div>

                    <!-- Alamat Hospital -->
                    <div class="mb-2 row align-items-center">
                      <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat_hospital','Alamat Hospital')) ?></label>
                      <div class="col-sm-8">
                        <input type="text" name="alamat_hospital" class="form-control" value="<?= h($alamatHospital) ?>" >
                      </div>
                    </div>                    
                    
                    <!-- Surat Rujukan -->
                    <div class="mb-2 row align-items-center">
                    <div class="col-sm-4">
                        <label class="col-form-label text-nowrap">
                        <?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?>
                        </label>
                        <br>
                        <small class="text-danger">
                        <?= h(tr('profile_dokumen_sokongan_note','(Surat rujukan pegawai perubatan / Kad / Buku Temujanji / Rawatan dalam format JPG/JPEG/PDF, maks 5MB)')) ?>
                        </small>
                    </div>

                    <div class="col-sm-8">
                        <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                        <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                    </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-3 row">
                      <div class="col-sm-8 offset-sm-4">
                        <button type="submit" class="btn btn-primary px-4">
                          <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>
                    </div>

                  </div>
                </div>
              </form>               
            </div>

            <!-- Tab 3: Perakauan Pemohon -->
            <div class="tab-pane fade" id="perakauan-pemohon-tab" role="tabpanel">
              <div class="card-text">
                <ul style="list-style: none; padding: 0;">
                  <li>1&#41;<span style="margin-left: 10px;">Saya mengaku bahawasannya saya tidak pernah dikenakan tindakan tatatertib sepanjang pengajian saya di UPNM.</span></li>
                  <li>2&#41;<span style="margin-left: 10px;">Adalah dengan ini sya mengaku bahawa maklumat yang diberikan di atas adalah benar. </span><br>
                    <span style="margin-left: 22px;">Pihak Universiti berhak menolak permohonan ini dan menarik balik anugerah yang diberikan sekirannya maklumat yang diberikan didapati tidak benar.</span></li>
                </ul>
              <br>
              <div class="form-check" style="margin-left: 10px;">
                <input class="form-check-input" type="checkbox" value="" id="consentCheck" required>
                <label class="form-check-label" for="consentCheck">
                  Setuju
                </label>
              </div>
              <br> 
              <p class="mb-0 fw-semibold" style="margin-left: 10px;">Nama: </p>
              <p class="mb-0 fw-semibold" style="margin-left: 10px;">Tarikh: <?= date('d-m-Y') ?></p>

                <!-- Hantar Permohonan -->
                <div class="mb-4 row">
                    <div class="col-sm-8 offset-sm-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ri-save-3-line me-2"></i> <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>
                    </button>
                    </div>
                </div>                            
            </div>

          </div>
        </div>
        <!-- /Profile Card with Tabs -->    

      </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
  </div>
</div>

<?php 
  include __DIR__ . '/../includes/script.php'; 
  include __DIR__ . '/../includes/script-pages.php';  
  include __DIR__ . '/../includes/script-custom.php';
?>

<div class="toast-lite" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
