<?php
  // pages/data-peribadi.php
  declare(strict_types=1);
  $NEED_DATERANGE  = false;
  $NEED_VECTORMAP  = false;
  $NEED_DATATABLES = true;
  $NEED_SELECT2    = false;

  require_once __DIR__ . '/../includes/init.php';
  require_login();
  require_once __DIR__ . '/../controllers/PeribadiController.php'; 
  require_once __DIR__ . '/../includes/functions-page.php'; //profileView[]
  include __DIR__ . '/../includes/header.php';
  include __DIR__ . '/../actions/retrieve-data-peribadi.php';

  // Check active session status
  $isActive = hasActiveSession($loginActivity);
?>
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
                  <?= h(tr('peribadi_title','Peribadi')) ?>
                </h4>
                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">
                      <a href="<?= base_url('pages/dashboard.php') ?>">
                        <i class="ri-home-4-line align-middle me-1"></i>
                        <?= h(tr('dashboard_breadcrumb','Papan Pemuka')) ?>
                      </a>
                    </li>
                    <li class="breadcrumb-item active">
                      <?= h(tr('peribadi_breadcrumb','Peribadi')) ?>
                    </li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Card with Tabs -->
          <div class="card border-0 shadow-sm profile-card">
            <?= include __DIR__ . '/../includes/profile-card.php'; ?>

            <!-- Tab Navigasi -->
            <ul class="nav nav-tabs profile-tabs" role="tablist" aria-label="<?= h(tr('profile_tabs_label','Tab profil pengguna')) ?>">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#maklumat-peribadi-tab" role="tab">
                  <i class="ri-login-box-line me-1"></i> <?= h(tr('tab_profil_pengguna','Maklumat Peribadi')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-alamat-tab" role="tab">
                  <i class="ri-file-list-3-line me-1"></i> <?= h(tr('tab_maklumat_alamat', 'Maklumat Alamat')) ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#maklumat-pekerjaan-tab" role="tab">
                  <i class="ri-briefcase-line me-1"></i> <?= h(tr('tab_maklumat_pekerjaan', 'Maklumat Pekerjaan')) ?>
                </a>
              </li>
              <!-- <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#perakauan-pemohon-tab" role="tab">
                  <i class="ri-user-line me-1"></i> <?= h(tr('tab_perakuan','Perakuan')) ?>
                </a>
              </li> -->
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
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
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
                          
                          <!-- No Passport -->
                          <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-nowrap">No Passport</label>
                            <div class="col-sm-8">
                              <input type="text" name="no_passport" class="form-control" value="<?= h($noPassport) ?>" readonly>
                            </div>
                          </div>

                          <!-- No Matrik -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_matrik','No. Matrik')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="no_matrik" class="form-control" value="<?= h($nomatrik) ?>" readonly>
                            </div>
                          </div>                        
                          
                          <!-- Tarikh Lahir -->
                          <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-nowrap">Tarikh Lahir</label>
                            <div class="col-sm-8">
                              <input type="text" id="tarikh_lahir" name="tarikh_lahir" class="form-control"
                                placeholder="dd/mm/yyyy"
                                value="<?= !empty($tarikhLahir) ? date('d/m/Y', strtotime($tarikhLahir)) : '' ?>" readonly>
                            </div>
                          </div>

                          <!-- Umur -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_umur','Umur')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="umur" class="form-control" value="<?= h($umur) ?>" readonly>
                            </div>
                          </div>  

                          <!-- Negeri Kelahiran -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri_kelahiran','Negeri Kelahiran')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri_kelahiran" class="form-control" value="<?= h($negeri_kelahiran) ?>" readonly>
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
                        </div>

                        <div class="col-md-6 gx-4">
                          <!-- Jantina -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jantina','Jantina')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="jantina" class="form-control" value="<?= h($jantina) ?>" readonly>
                            </div>                 
                          </div>

                          <!-- Bangsa -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bangsa','Bangsa')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bangsa" class="form-control" value="<?= h($bangsa) ?>" readonly>
                            </div>
                          </div>                        
                          
                          <!-- Agama -->
                          <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_agama','Agama')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="agama" class="form-control" value="<?= h($agama) ?>" readonly>
                            </div>
                          </div>

                          <!-- Status Perkahwinan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_perkahwinan','Status Perkahwinan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="status_perkahwinan" class="form-control" value="<?= h($status_perkahwinan) ?>" readonly>
                            </div>
                          </div>         

                          <!-- Bilangan Adik-Beradik -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_adikberadik','Bilangan Adik-Beradik')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="adik_beradik" class="form-control" value="<?= h($adik_beradik) ?>" >
                            </div>
                          </div>                        

                          <!-- Anak Ke Berapa dalam Keluarga -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_anak_ke','Anak Ke Berapa')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="anak_ke" class="form-control" value="<?= h($anak_ke) ?>" >
                            </div>
                          </div>

                          <!-- Kewarganegaraan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kewarganegaraan','Kewarganegaraan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="kewarganegaraan" class="form-control" value="<?= h($kewarganegaraan) ?>" readonly>
                            </div>
                          </div>

                          <!-- Akaun Bank -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_akaun_bank','Akaun Bank')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="akaun_bank" class="form-control" value="<?= h($akaun_bank) ?>" >
                            </div>
                          </div>

                          <!-- Dokumen Akaun -->
                          <div class="mb-2 row align-items-center">
                            <div class="col-sm-4">
                                <label class="col-form-label text-nowrap">
                                <?= h(tr('profile_dokumen_akaun','Dokumen Akaun')) ?>
                                </label> <i class="ri-information-line me-2"></i>
                                <br>
                                <small class="text-danger">
                                <?= h(tr('profile_dokumen_akaun_note','(Sila sertakan Penyata No. Akaun Bank (Aktif) dalam format JPG/JPEG/PDF, maks 5MB)')) ?>
                                </small>
                            </div>
                            <div class="col-sm-8">
                                <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                                <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
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

              <!-- Tab 2: Maklumat Alamat -->
              <div class="tab-pane fade" id="maklumat-alamat-tab" role="tabpanel">
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
                          <h5><?= h(tr('profile_alamat_permanent','Alamat Tetap')) ?></h5>
                          <hr>
                          <!-- Alamat Baris 1 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" readonly>
                            </div>                 
                          </div>

                          <!-- Alamat Baris 2 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" readonly>
                            </div>
                          </div>                        

                          <!-- Poskod -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" readonly>
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

              <!-- Tab 4: Perakauan Pemohon -->
              <!-- <div class="tab-pane fade" id="perakauan-pemohon-tab" role="tabpanel">
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
                <p class="mb-0 fw-semibold" style="margin-left: 10px;">Tarikh: <?= date('d-m-Y') ?></p> -->
                
                  <!-- Simpan Maklumat -->
                  <!-- <div class="mb-4 row">
                      <div class="col-sm-8 offset-sm-4">
                      <button type="submit" class="btn btn-primary px-4">
                          <i class="ri-save-3-line me-2"></i> <?= h(tr('profile_btn_submit','Simpan')) ?>
                      </button>
                      </div>
                  </div>                             -->
              <!-- </div> -->

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
