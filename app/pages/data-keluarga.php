<?php
// pages/data-peribadi.php
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
              <a class="nav-link active" data-bs-toggle="tab" href="#maklumat-bapa-tab" role="tab">
                <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_maklumat_bapa', 'Maklumat Bapa')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#maklumat-ibu-tab" role="tab">
                <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_maklumat_ibu','Maklumat Ibu')) ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#maklumat-penjaga-tab" role="tab">
                <i class="ri-briefcase-line me-1"></i> <?= h(tr('tab_maklumat_penjaga', 'Maklumat Penjaga')) ?>
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

            <!-- Tab 1: Maklumat Bapa -->
            <div class="tab-pane fade show active" id="maklumat-bapa-tab" role="tabpanel">
              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row">
                  <div class="col-12">
                    <div class="row">
                      <div class="col-md-6 gx-4">
                        <!-- Nama bapa -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama','Nama Bapa')) ?></label>
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
                        
                        <!-- No Passport -->
                        <div class="mb-2 row">
                          <label class="col-sm-4 col-form-label text-nowrap">No Passport</label>
                          <div class="col-sm-8">
                            <input type="text" name="no_passport" class="form-control" value="<?= h($noPassport) ?>" readonly>
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

                        <!-- Status Kesihatan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_kesihatan','Status Kesihatan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="status_kesihatan" class="form-control" value="<?= h($status_kesihatan) ?>" >
                          </div>
                        </div>                       
                        
                        <!-- Dokumen OKU -->
                        <div class="mb-2 row align-items-center">
                          <div class="col-sm-4">
                              <label class="col-form-label text-nowrap">
                              <?= h(tr('profile_dokumen_oku','Dokumen OKU')) ?>
                              </label>
                              <br>
                              <small class="text-danger">
                              <?= h(tr('profile_dokumen_oku_note','(Sila sertakan Kad OKU / Dokumen OKU / No. Pendaftaran dalam format JPG/JPEG/PDF, maks 5MB)')) ?>
                              </small>
                          </div>
                          <div class="col-sm-8">
                              <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                              <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                          </div>
                        </div>                          
                        
                        <!-- Bil. Anak -->
                        <!-- <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bil_anak','Bil. Anak')) ?></label>
                          <div class="col-sm-8">
                            <input type="number" name="bil_anak" class="form-control" value="<?= h($bil_anak ?? '') ?>" >
                          </div>                 
                        </div>               -->
                        
                        <!-- Bil. Tanggungan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bil_tanggungan','Bil. Tanggungan')) ?></label>
                          <div class="col-sm-8">
                            <input type="number" name="bil_tanggungan" class="form-control" value="<?= h($bil_tanggungan ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Tahap Pendidikan  -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tahap_pendidikan','Tahap Pendidikan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="tahap_pendidikan" class="form-control" value="<?= h($tahap_pendidikan ?? '') ?>" >
                          </div>
                        </div>   

                        <br>
                        <h5 style="color: #0B2C4D"><?= h(tr('profile_alamat_permanent','Maklumat Pekerjaan')) ?></h5>
                        <hr>
                        <!-- Status Pekerjaan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Status Pekerjaan')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_pekerjaan" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Bekerja</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak Bekerja</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Pesara</option>
                            </select>
                          </div>                 
                        </div>

                        <!-- Sektor Pekerjaan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sektor_pekerjaan','Sektor Pekerjaan')) ?></label>
                          <div class="col-sm-8">
                            <select name="sektor_pekerjaan" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Kerajaan</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Swasta</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Persendirian</option>
                            </select>
                          </div>                 
                        </div> 

                        <!-- Majikan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_majikan','Nama Majikan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="majikan" class="form-control" value="<?= h($majikan ?? '') ?>" >
                          </div>                 
                        </div>      

                        <!-- Perkhidmatan Beruniform -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_perkhidmatan_beruniform','Perkhidmatan Beruniform')) ?></label>
                          <div class="col-sm-8">
                            <select name="perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Ya</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak</option>
                            </select>
                          </div>                 
                        </div>      

                        <!-- Jenis Perkhidmatan Beruniform -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jenis_perkhidmatan_beruniform','Jenis Perkhidmatan Beruniform')) ?></label>
                          <div class="col-sm-8">
                            <select name="jenis_perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Polis</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tentera</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Bomba</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Lain-lain (Sila Nyatakan)</option>
                            </select>
                          </div>                 
                        </div>      

                        <!-- Status Perkhidmatan Beruniform -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('status_perkhidmatan_beruniform','Status Perkhidmatan Beruniform')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Dalam Perkhidmatan</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Pesara</option>
                            </select>
                          </div>                 
                        </div>   

                        <!-- Pendapatan Bulanan Kasar (RM) -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('jumlah_pendapatan_bulanan_kasar','Pendapatan Bulanan Kasar (RM)')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>><1,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>1,001-2,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>2,001-3,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>3,001-4,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>4,001-5,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>5,001-6,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>6,001-7,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>7,001-8,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>8,001-9,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>9,001-10,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>10,001-11,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>11,001-12,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>12,001-13,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>13,001-14,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>14,001-15,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><15,001</option>
                            </select>
                          </div>                 
                        </div>  

                        <!-- Perakuan Pendapatan -->
                        <div class="mb-2 row align-items-center">
                          <div class="col-sm-4">
                              <label class="col-form-label text-nowrap">
                              <?= h(tr('profile_dokumen_akaun','Perakuan Pendapatan')) ?>
                              </label>
                              <br>
                              <small class="text-danger">
                              <?= h(tr('profile_dokumen_akaun_note','(Sila sertakan Penyata Pendapatan atau Surat Pengesahan Pendapatan dalam format JPG/JPEG/PDF, maks 5MB)')) ?>
                              </small>
                          </div>
                          <div class="col-sm-8">
                              <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                              <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                          </div>
                        </div>                                      
                      </div>

                      <div class="col-md-6 gx-4">
                        <!-- Kategori Tempat Tinggal -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kategori_tempat_tinggal','Kategori Tempat Tinggal')) ?></label>
                          <div class="col-sm-8">
                            <select name="kategori_tempat_tinggal" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Persendirian</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Sewaan</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Lain-lain (Sila Nyatakan)</option>
                            </select>
                          </div>
                        </div>  

                        <!-- Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" >
                          </div>
                        </div>                        

                        <!-- Poskod -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Bandar -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
                          </div>
                        </div>   

                        <br><br><br><br><br><br><br><br><br><br><br><br><br>
                        <!-- Pekerjaan Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" >
                          </div>
                        </div>                        

                        <!-- Poskod -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Bandar -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
                          </div>
                        </div>                         
                      <br><br><br><br><br><br><br>
                      <!-- Submit Button -->
                      <div class="col-4 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>

                    </div>
                  </div>
                </div>
              </form>       
            </div>

            <!-- Tab 2: Maklumat Ibu -->
            <div class="tab-pane fade" id="maklumat-ibu-tab" role="tabpanel">
              <div id="auditEventsLoading" class="skeleton-loader" style="display: none;">
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
              </div>

              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row">
                  <div class="col-12">
                    <div class="row">
                      <div class="col-md-6 gx-4">                        
                        <br>
                        <h5><?= h(tr('profile_alamat_permanent','Alamat Tempat Tinggal')) ?></h5>
                        <hr>
                        <!-- Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" >
                          </div>
                        </div>                        

                        <!-- Poskod -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Bandar -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
                          </div>
                        </div>    
                        
                        
                        <br>
                        <h5><?= h(tr('profile_alamat_permanent','Penginapan Semasa Pengajian')) ?></h5>
                        <hr>
                        <!-- Kategori Penginapan : dropdown Dalam / Luar Kampus check asrama -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('kategori_penginapan','Kategori Penginapan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? 'Dalam / Luar Kampus') ?>" readonly>
                          </div>                 
                        </div>                        
                        <!-- Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" >
                          </div>
                        </div>                        

                        <!-- Poskod -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Bandar -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
                          </div>
                        </div>                         
                      </div>

                      <div class="col-md-6 gx-4">
                        <h5><?= h(tr('profile_alamat_surat_menyurat','Alamat Surat Menyurat')) ?></h5>
                        <hr>                        
                        <!-- Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>">
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>">
                          </div>
                        </div>                        

                        <!-- Poskod -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>">
                          </div>
                        </div>

                        <!-- Bandar -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>">
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>">
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>">
                          </div>
                        </div>                       
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-center mt-3">
                  <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                  </button>
                </div>                
              </form>              
            </div>

            <!-- Tab 4: Maklumat Pekerjaan -->
            <div class="tab-pane fade" id="maklumat-pekerjaan-tab" role="tabpanel">
              <div id="auditEventsLoading" class="skeleton-loader" style="display: none;">
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
                <div class="skeleton-row"></div>
              </div>

              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row">
                  <div class="col-12">
                    <div class="row">
                      <div class="col-md-6 gx-4">
                        <!-- Status Pekerjaan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Status Pekerjaan')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_pekerjaan" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Bekerja</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak Bekerja</option>
                            </select>
                          </div>                 
                        </div>

                        <!-- Sektor Pekerjaan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sektor_pekerjaan','Sektor Pekerjaan')) ?></label>
                          <div class="col-sm-8">
                            <select name="sektor_pekerjaan" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Kerajaan</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Swasta</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Persendirian</option>
                            </select>
                          </div>                 
                        </div>   

                        <!-- Status Pekerjaan Sambilan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan_sambilan','Status Pekerjaan Sambilan')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_pekerjaan_sambilan" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Ya</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak</option>
                            </select>
                          </div>                 
                        </div>                  

                        <!-- Jenis Pekerjaan Sambilan -->
                        <div class="mb-2 row align-items-center">
                          <div class="col-sm-4">
                              <label class="col-form-label text-nowrap">
                              <?= h(tr('profile_jenis_pekerjaan_sambilan','Jenis Pekerjaan Sambilan')) ?>
                              </label>
                              <br>
                              <small class="text-danger">
                              <?= h(tr('profile_jenis_pekerjaan_sambilan_note','(Sila nyatakan jenis pekerjaan sambilan, jika ada)')) ?>
                              </small>
                          </div>
                          <div class="col-sm-8">
                            <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Bandar -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" readonly>
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" readonly>
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6 gx-4">                   
                        <!-- Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>">
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>">
                          </div>
                        </div>                        

                        <!-- Poskod -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>">
                          </div>
                        </div>

                        <!-- Bandar -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>">
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>">
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>">
                          </div>
                        </div>                       
                      </div>

                      <!-- Submit Button -->
                      <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>

                    </div>
                  </div>
                </div>
              </form>              
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
