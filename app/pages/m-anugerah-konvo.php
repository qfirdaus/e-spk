<?php
// pages/m-anugerah-konvo.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();
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
                <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_peglibatan_program','Penglibatan Program')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#jawatan-disandang-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_jawatan_disandang','Jawatan Yang Disandang')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#anugerah-pengiktirafan-tab" role="tab">
                <i class="ri-user-line me-1"></i> <?= h(tr('tab_anugerah_pengiktirafan','Anugerah dan Pengiktirafan')) ?>
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
                    <!-- Kategori Anugerah -->
                    <!-- <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label fw-semibold"><?= h(tr('anugerah_kategori','Kategori Anugerah')) ?></label>
                      <div class="col-sm-9">
                        <select name="kategori" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="Lelaki">Anugerah Pelajaran Diraja</option>
                          <option value="Perempuan">Anugerah Pingat Emas Canselor</option>
                          <option value="Keluarga">Anugerah Pingat Emas Lembaga Pengarah Universiti (Kepimpinan)</option>
                          <option value="Lain-lain">Anugerah Pingat Emas Naib Canselor </option>
                          <option value="Lain-lain">Anugerah Hadiah Buku (Ijazah Sarjan Muda)</option>
                          <option value="Lain-lain">Anugerah Pelajar Diploma Terbaik</option>
                          <option value="Lain-lain">Anugerah Hadiah Buku (Diploma)</option>
                          <option value="Lain-lain">Anugerah Keusahawanan</option>
                        </select>
                      </div>
                    </div> -->

                    <!-- Nama Pemohon -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_nama','Nama Pemohon')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($namaPenuh) ?>" readonly>
                      </div>
                    </div>

                    <!-- No Kad Pengenalan -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_no_kad_pengenalan','No Kad Pengenalan')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" name="no_kp" class="form-control" value="<?= h($noKadPengenalan) ?>" readonly>
                      </div>
                    </div>

                    <!-- No Passport -->
                    <!-- <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label fw-semibold"><?= h(tr('profile_no_passport','No Passport')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" name="no_passport" class="form-control" value="<?= h($noPassport) ?>" readonly>
                      </div>
                    </div> -->

                    <!-- No Matrik -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_no_matrik','No. Matrik')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($nopek) ?>" readonly>
                      </div>
                    </div>

                    <!-- Jantina -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_jantina','Jantina')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($jantina) ?>" readonly>
                      </div>
                    </div>

                    <!-- Telefon -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_telefon','No Telefon')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" name="telefon" class="form-control" value="<?= h($notel) ?>" readonly>
                      </div>
                    </div>

                    <!-- Emel -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_emel','Emel')) ?></label>
                      <div class="col-sm-9">
                        <input type="email" name="emel" class="form-control" value="<?= h($emel) ?>" readonly>
                      </div>
                    </div>

                    <!-- Fakulti -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_kewarganegaraan','Fakulti / Pusat / Akademi')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" name="kewarganegaraan" class="form-control" value="<?= h($kewarganegaraan) ?>" readonly>
                      </div>
                    </div>

                    <!-- Program -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_program','Program Pengajian')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" name="program" class="form-control" value="<?= h($program) ?>" readonly>
                      </div>
                    </div>

                    <!-- Tempoh Program -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_tempoh_program','Tempoh Program')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" name="tempoh_program" class="form-control" value="<?= h($tempohProgram) ?>" readonly>
                      </div>
                    </div>

                    <!-- PNGK Semasa -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_pngk_semasa','PNGK Semasa')) ?></label>
                      <div class="col-sm-3">
                        <input type="text" name="pngk_semasa" class="form-control" value="<?= h($pngkSemasa) ?>" readonly>
                      </div>   
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_pngk_akhir','PNGK Akhir')) ?></label>     
                      <div class="col-sm-3">
                        <input type="text" name="pngk_akhir" class="form-control" value="<?= h($pngkAkhir) ?>" readonly>
                      </div>
                    </div>      
                    
                    <!-- Bil. Anugerah Dekan-->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_bil_anugerah_dekan','Bil. Anugerah Dekan')) ?></label>
                      <div class="col-sm-1">
                        <input type="number" name="bil_anugerah_dekan" class="form-control" value="<?= h($bilAnugerahDekan) ?>" min="1" max="10" >
                      </div>       
                      <div class="col-sm-8">
                        <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                        <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                      </div>
                    </div>

                    <!-- Gred Kursus PSM -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_gred_kursus_psm','Gred Kursus PSM')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" name="gred_kursus_psm" class="form-control" value="<?= h($gredKursusPsm) ?>" readonly>
                      </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-4 row">
                      <div class="col-sm-9 offset-sm-2">
                        <button type="submit" class="btn btn-primary px-4">
                          <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>
                    </div>

                  </div>
                </div>
              </form>
            </div>

            <!-- Tab 2: Penglibatan Program -->
            <div class="tab-pane fade" id="penglibatan-program-tab" role="tabpanel">
              <div id="auditEventsLoading" class="skeleton-loader" style="display: none;">
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
              </div>

              <div class="table-responsive">
                <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                  <thead>
                    <tr>
                      <th class="small w-25">Nama Program / Nama Pertandingan </th>
                      <th class="small">Tarikh</th> 
                      <th class="small">Wakil</th>
                      <th class="small">Peringkat</th>
                      <th class="small">Pencapaian</th>
                      <th class="small text-center">Dokumen</th>
                      <th class="small text-center">Tindakan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>PERSEMBAHAN TAEKWONDO SEMPENA SAMBUTAN HARI WANITA SEDUNIA PERINGKAT UPNM</td>
                      <td>14-03-2025 - 14-03-2025</td>
                      <td></td>
                      <td>Universiti</td>
                      <td>Tempat Ketiga</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-primary" type="button">
                          <i class="ri-eye-line me-1"></i>
                        </button>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-danger" type="button">
                          <i class="ri-pencil-line me-1"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>PENGANJURAN HAWK INTENSE CHALLENGE 2025</td>
                      <td>31-05-2025 - 31-05-2025</td>
                      <td></td>
                      <td>Universiti</td>
                      <td>Peserta</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-primary" type="button">
                          <i class="ri-eye-line me-1"></i>
                        </button>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-danger" type="button">
                          <i class="ri-pencil-line me-1"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>               
              </div>
              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row ">
                  <div class="col-12 col-md-8">
                    <!-- Nama Program / Nama Pertandingan -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_nama_program','Nama Program / Nama Pertandingan')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($nopek) ?>" >
                      </div>
                    </div>

                    <!-- Tarikh -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_tarikh','Tarikh')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($tarikh) ?>" >
                      </div>
                    </div>

                    <!-- Wakil -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_wakil','Wakil')) ?></label>
                      <div class="col-sm-9">
                        <select name="jantina" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Individu / Kolej Kediaman</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Badan Pelajar</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Fakulti</option> 
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Universiti</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Negeri</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Negara</option>
                        </select>
                      </div>
                    </div>

                    <!-- Peringkat -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_peringkat','Peringkat')) ?></label>
                      <div class="col-sm-9">
                        <select name="peringkat" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="Lelaki" <?= $peringkat=='Lelaki'?'selected':'' ?>>Kolej Kediaman</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Badan Pelajar</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Fakulti</option> 
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Universiti</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Negeri</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Negara</option>
                        </select>
                      </div>
                    </div>     

                    <!-- Pencapaian -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_pencapaian','Pencapaian')) ?></label>
                      <div class="col-sm-9">
                        <select name="pencapaian" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="Lelaki" <?= $pencapaian=='Lelaki'?'selected':'' ?>>Johan / Emas</option>
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Badan Pelajar</option>
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Naib Johan / Perak </option> 
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Tempat Ketiga / Gangsa</option>
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Peserta</option>
                        </select>
                      </div>
                    </div>    
                    
                    <!-- Dokumen Sokongan -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?></label>     
                      <div class="col-sm-9">
                        <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                        <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                      </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-4 row">
                      <div class="col-sm-9 offset-sm-2">
                        <button type="submit" class="btn btn-primary px-4">
                          <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>
                    </div>

                  </div>
                </div>
              </form>               
            </div>

            <!-- Tab 3: Jawatan Yang Disandang -->
            <div class="tab-pane fade" id="jawatan-disandang-tab" role="tabpanel">
              <div class="table-responsive">
                <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                  <thead>
                    <tr>
                      <th class="small w-25">Nama Program / Nama Pertandingan </th>
                      <th class="small">Tarikh</th> 
                      <th class="small">Wakil</th>
                      <th class="small">Peringkat</th>
                      <th class="small">Pencapaian</th>
                      <th class="small text-center">Dokumen</th>
                      <th class="small text-center">Tindakan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>PERSEMBAHAN TAEKWONDO SEMPENA SAMBUTAN HARI WANITA SEDUNIA PERINGKAT UPNM</td>
                      <td>14-03-2025 - 14-03-2025</td>
                      <td></td>
                      <td>Universiti</td>
                      <td>Tempat Ketiga</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-primary" type="button">
                          <i class="ri-eye-line me-1"></i>
                        </button>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-danger" type="button">
                          <i class="ri-pencil-line me-1"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>PENGANJURAN HAWK INTENSE CHALLENGE 2025</td>
                      <td>31-05-2025 - 31-05-2025</td>
                      <td></td>
                      <td>Universiti</td>
                      <td>Peserta</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-primary" type="button">
                          <i class="ri-eye-line me-1"></i>
                        </button>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-danger" type="button">
                          <i class="ri-pencil-line me-1"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>               
              </div>
                            
              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row ">
                  <div class="col-12 col-md-8">
                    <!-- Nama Badan Pelajar / Nama Program -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_nama_badanpelajar','Nama Badan Pelajar / Nama Program')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($nopek) ?>" >
                      </div>
                    </div>

                    <!-- Tarikh Lantikan -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_tarikh','Tarikh Lantikan')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($tarikh) ?>" >
                      </div>
                    </div>

                    <!-- Jawatan -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_jawatan','Jawatan')) ?></label>
                      <div class="col-sm-9">
                        <select name="jantina" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Yang Dipertua</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Majlis Tertinggi</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Exco</option> 
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Presiden</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Pengarah</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Majlis Tertinggi</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>AJK</option>
                          <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Ahli</option>
                        </select>
                      </div>
                    </div>

                    <!-- Peringkat -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_peringkat','Peringkat')) ?></label>
                      <div class="col-sm-9">
                        <select name="peringkat" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="Lelaki" <?= $peringkat=='Lelaki'?'selected':'' ?>>Kolej Kediaman</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Badan Pelajar</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Fakulti</option> 
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Universiti</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Negeri</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Kebangsaan</option>
                          <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>>Antarabangsa</option>
                        </select>
                      </div>
                    </div>     

                    <!-- Pencapaian -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_pencapaian','Pencapaian')) ?></label>
                      <div class="col-sm-9">
                        <select name="pencapaian" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="Lelaki" <?= $pencapaian=='Lelaki'?'selected':'' ?>>Johan / Emas</option>
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Badan Pelajar</option>
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Naib Johan / Perak </option> 
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Tempat Ketiga / Gangsa</option>
                          <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>>Peserta</option>
                        </select>
                      </div>
                    </div>    
                    
                    <!-- Dokumen Sokongan -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?></label>     
                      <div class="col-sm-9">
                        <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                        <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                      </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-4 row">
                      <div class="col-sm-9 offset-sm-2">
                        <button type="submit" class="btn btn-primary px-4">
                          <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>
                    </div>

                  </div>
                </div>
              </form>                
            </div>

            <!-- Tab 4: Anugerah dan Pengiktirafan -->
            <div class="tab-pane fade" id="anugerah-pengiktirafan-tab" role="tabpanel">
              <div class="table-responsive">
                <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                  <thead>
                    <tr>
                      <th class="small w-25">Nama Anugerah / Pengiktirafan </th>
                      <th class="small">Tahun</th> 
                      <th class="small">Wakil</th>
                      <th class="small">Peringkat</th>
                      <th class="small">Pencapaian</th>
                      <th class="small text-center">Dokumen</th>
                      <th class="small text-center">Tindakan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>PERSEMBAHAN TAEKWONDO SEMPENA SAMBUTAN HARI WANITA SEDUNIA PERINGKAT UPNM</td>
                      <td>14-03-2025 - 14-03-2025</td>
                      <td></td>
                      <td>Universiti</td>
                      <td>Tempat Ketiga</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-primary" type="button">
                          <i class="ri-eye-line me-1"></i>
                        </button>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-danger" type="button">
                          <i class="ri-pencil-line me-1"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>PENGANJURAN HAWK INTENSE CHALLENGE 2025</td>
                      <td>31-05-2025 - 31-05-2025</td>
                      <td></td>
                      <td>Universiti</td>
                      <td>Peserta</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-primary" type="button">
                          <i class="ri-eye-line me-1"></i>
                        </button>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-danger" type="button">
                          <i class="ri-pencil-line me-1"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>               
              </div>

              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row ">
                  <div class="col-12 col-md-8">
                    <!-- Nama Anugerah / Pengiktirafan -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_nama_badanpelajar','Nama Anugerah / Pengiktirafan')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($nopek) ?>" >
                      </div>
                    </div>

                    <!-- Tahun -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_tarikh','Tahun')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($tarikh) ?>" >
                      </div>
                    </div>

                    <!-- Kurniaan / Pemberian -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_kurniaan','Kurniaan / Pemberian')) ?></label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control" value="<?= h($kurniaan) ?>" >
                      </div>
                    </div>

                    <!-- Peringkat -->
                    <div class="mb-4 row align-items-center">
                      <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('mohon_anugerah_konvo_peringkat','Peringkat')) ?></label>
                      <div class="col-sm-9">
                        <select name="peringkat" class="form-select">
                          <option value="">-- Sila Pilih --</option>
                          <option value="kolej" <?= $peringkat=='kolej'?'selected':'' ?>>Kolej Kediaman</option>
                          <option value="badan_pelajar" <?= $peringkat=='badan_pelajar'?'selected':'' ?>>Badan Pelajar</option>
                          <option value="fakulti" <?= $peringkat=='fakulti'?'selected':'' ?>>Fakulti</option> 
                          <option value="universiti" <?= $peringkat=='universiti'?'selected':'' ?>>Universiti</option>
                          <option value="negeri" <?= $peringkat=='negeri'?'selected':'' ?>>Negeri</option>
                          <option value="kebangsaan" <?= $peringkat=='kebangsaan'?'selected':'' ?>>Kebangsaan</option>
                          <option value="antarabangsa" <?= $peringkat=='antarabangsa'?'selected':'' ?>>Antarabangsa</option>
                        </select>
                      </div>
                    </div>    

                    <!-- Submit Button -->
                    <div class="mb-4 row">
                      <div class="col-sm-9 offset-sm-2">
                        <button type="submit" class="btn btn-primary px-4">
                          <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>
                    </div>

                  </div>
                </div>
              </form>              
            </div>

            <!-- Tab 5: Perakauan Pemohon -->
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
              <div class="d-flex justify-content-start mt-3" style="padding: 0 2.5rem;">
                <button type="submit" class="btn btn-primary px-4" id="btn-submit">
                  <i class="ri-save-3-line me-2"></i> <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>
                </button>
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
