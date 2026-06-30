<?php
  $lookupKategoriOKU = $lookupAll['kategori_oku'] ?? [];
  $lookupResidenceCategory = $lookupAll['residence_category'] ?? [];
  $lookupNegeri = $lookupAll['negeri'] ?? [];
  $lookupNegara = $lookupAll['negara'] ?? [];
  $lookupEmploymentStatus = $lookupAll['employment_status'] ?? [];
  $lookupEmploymentSector = $lookupAll['employment_sector'] ?? [];
  $lookupUniformService = $lookupAll['uniform_service'] ?? [];
  $lookupUniformServiceStatus = $lookupAll['uniform_service_status'] ?? [];
  $lookupSalaryRange = $lookupAll['salary_range'] ?? [];
?>
<form id="form-ibu" method="post" enctype="multipart/form-data">
  <input type="hidden" name="icares_form" value="keluarga_ibu">

  <div class="icares-address-layout">
    <div class="icares-address-nav" role="tablist" aria-label="<?= h(tr('tab_maklumat_ibu','Maklumat Ibu')) ?>">
      <button class="icares-address-nav__item active" id="ibu-peribadi-tab" data-bs-toggle="pill" data-bs-target="#ibu-peribadi-panel" type="button" role="tab" aria-controls="ibu-peribadi-panel" aria-selected="true">
        <i class="ri-user-line"></i>
        <span><?= h(tr('profile_maklumat_peribadi', 'Maklumat Peribadi')) ?></span>
      </button>
      <button class="icares-address-nav__item" id="ibu-alamat-tab" data-bs-toggle="pill" data-bs-target="#ibu-alamat-panel" type="button" role="tab" aria-controls="ibu-alamat-panel" aria-selected="false">
        <i class="ri-home-4-line"></i>
        <span><?= h(tr('profile_alamat_tempat_tinggal', 'Alamat Tempat Tinggal')) ?></span>
      </button>
      <button class="icares-address-nav__item" id="ibu-pekerjaan-tab" data-bs-toggle="pill" data-bs-target="#ibu-pekerjaan-panel" type="button" role="tab" aria-controls="ibu-pekerjaan-panel" aria-selected="false">
        <i class="ri-briefcase-line"></i>
        <span><?= h(tr('profile_maklumat_pekerjaan', 'Maklumat Pekerjaan')) ?></span>
      </button>
      <button class="icares-address-nav__item" id="ibu-majikan-tab" data-bs-toggle="pill" data-bs-target="#ibu-majikan-panel" type="button" role="tab" aria-controls="ibu-majikan-panel" aria-selected="false">
        <i class="ri-building-2-line"></i>
        <span><?= h(tr('profile_alamat_majikan', 'Alamat Majikan')) ?></span>
      </button>
    </div>

    <div class="icares-address-content tab-content">
      <div class="tab-pane fade show active" id="ibu-peribadi-panel" role="tabpanel" aria-labelledby="ibu-peribadi-tab" tabindex="0">
        <div class="icares-address-panel-header">
          <h5><?= h(tr('profile_maklumat_peribadi', 'Maklumat Peribadi')) ?></h5>
          <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
        </div>
        <div class="row">
    <!-- LEFT: Maklumat Peribadi -->
    <div class="col-12 gx-4">
      <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Maklumat Peribadi')) ?></h5>
      <hr>

      <!-- Nama ibu -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama_ibu','Nama Ibu')) ?></label>
        <div class="col-sm-8">
          <input type="text" class="form-control" value="<?= ucwords(strtolower(h($dataFamilySAP['namaibu'] ?? ''))) ?>" readonly>          
          <input type="hidden" name="nama_ibu" class="form-control" value="<?= ucwords(strtolower(h($dataFamilySAP['namaibu'] ?? ''))) ?>">
        </div>                 
      </div>

      <!-- No Kad Pengenalan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_kad_pengenalan','No Kad Pengenalan')) ?></label>
        <div class="col-sm-8">
          <input type="text" class="form-control" value="<?= h($dataFamilySAP['nokpibu'] ?? '') ?>" readonly>
          <input type="hidden" name="no_ic" class="form-control" value="<?= h($dataFamilySAP['nokpibu'] ?? '') ?>" readonly>
        </div>
      </div>

      <!-- No Passport -->
      <div class="mb-2 row">
        <label class="col-sm-4 col-form-label text-nowrap">No Passport</label>
        <div class="col-sm-8">
          <input type="text" name="no_passport" class="form-control" value="<?= h($dataMother['passport_no'] ?? '') ?>" >
        </div>
      </div>         

      <!-- Telefon -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_telefon','No Telefon')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="no_telefon" class="form-control" value="<?= h($dataFamilySAP['nohp_ibu'] ?? '') ?>" readonly>
        </div>
      </div>

      <!-- Emel -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_emel','Emel')) ?></label>
        <div class="col-sm-8">
          <input type="email" name="emel" class="form-control" value="<?= h($dataMother['email'] ?? '') ?>" >
        </div>
      </div>

      <!-- Status Kesihatan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_kesihatan','Status Kesihatan')) ?></label>
        <div class="col-sm-8">
          <select name="status_kesihatan" class="form-select form-select-sm select2">
              <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
              <?php foreach ($lookupKategoriOKU as $opt): ?>
              <option value="<?= h($opt['OKU_code']) ?>"
                  <?= h($dataMother['health_status'] ?? '') == $opt['OKU_code'] ? 'selected' : '' ?>>
                  <?= h(strtoupper($opt['OKU_desc'])) ?>
              </option>
              <?php endforeach; ?>
          </select>  
        </div>                 
      </div>  

      <!-- Dokumen OKU -->
      <div class="mb-2 row align-items-center">
        <div class="col-sm-4">
          <label class="col-form-label text-nowrap"><?= h(tr('profile_dokumen_oku','Dokumen OKU')) ?></label> 
          <i class="ri-information-line ms-1 text-info extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
            title="<?= h(tr('profile_dokumen_oku_note','(JPG/JPEG/PDF, maks 5MB)')) ?>"></i>
        </div>
        
        <div class="col-sm-8">
          <div class="upload-wrapper"> <?php if (!empty($dataMother['document_path'])): ?>
                  <div class="existing-file-section d-flex align-items-center gap-2">
                      <a href="<?= base_url(($dataMother['document_path']))?>" target="_blank" class="btn btn-sm btn-outline-warning">
                          <i class="ri-eye-line"></i> Lihat Dokumen OKU
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-secondary btn-tukar-fail">
                          <i class="ri-upload-2-line"></i> Tukar Fail Baru
                      </button>
                  </div>

                  <div class="new-file-section d-none mt-2">
                      <div class="input-group input-group-sm">
                          <input type="file" name="dokumen_oku" class="form-control form-control-sm input-upload-fail" accept=".jpg, .jpeg, .pdf" />
                          <button type="button" class="btn btn-danger btn-batal-tukar">Batal</button>
                      </div>
                  </div>
              <?php else: ?>
                  <input type="file" name="dokumen_oku" class="form-control form-control-sm input-upload-fail" accept=".jpg, .jpeg, .pdf" />
              <?php endif; ?>
          </div>                
        </div>
      </div>

      <!-- Bil. Tanggungan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bil_tanggungan','Bil. Tanggungan')) ?></label>
        <div class="col-sm-8">
          <input type="number" name="bil_tanggungan" class="form-control" value="<?= h($dataMother['dependents_count'] ?? '') ?>" >
        </div>                 
      </div>

      <!-- Tahap Pendidikan Tertinggi  -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tahap_pendidikan_tertinggi','Tahap Pendidikan Tertinggi')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="tahap_pendidikan" class="form-control" value="<?= h($dataMother['highest_education'] ?? '') ?>" >
        </div>
      </div>   
    </div>
        </div>
      </div>

      <div class="tab-pane fade" id="ibu-alamat-panel" role="tabpanel" aria-labelledby="ibu-alamat-tab" tabindex="0">
        <div class="icares-address-panel-header">
          <h5><?= h(tr('profile_alamat_tempat_tinggal', 'Alamat Tempat Tinggal')) ?></h5>
          <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
        </div>
        <div class="row">
          <!-- RIGHT: Alamat Tempat Tinggal -->
          <div class="col-12 gx-4">
            <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Alamat Tempat Tinggal')) ?></h5>
            <hr>

            <!-- Kategori Tempat Tinggal -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kategori_tempat_tinggal','Kategori Tempat Tinggal')) ?></label>
              <div class="col-sm-8">
                <select name="kategori_tempat_tinggal" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupResidenceCategory as $opt): ?>
                    <option value="<?= h($opt['residence_code']) ?>"
                        <?= h($dataMother['residence_category'] ?? '') == $opt['residence_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['residence_desc'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>                
              </div>
            </div>  

            <!-- Alamat Baris 1 -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
              <div class="col-sm-8">
                <input type="text" name="alamat_ibu1" class="form-control" value="<?= h($dataMother['address1'] ?? '') ?>" >
              </div>                 
            </div>

            <!-- Alamat Baris 2 -->
            <div class="mb-2 row">
              <div class="offset-sm-4 col-sm-8">
                <input type="text" name="alamat_ibu2" class="form-control" value="<?= h($dataMother['address2'] ?? '') ?>" >
              </div>
            </div>                        

            <!-- Poskod -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
              <div class="col-sm-8">
                <input type="text" name="alamat_ibu3" class="form-control" value="<?= h($dataMother['address3'] ?? '') ?>" >
              </div>
            </div>

            <!-- Bandar -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
              <div class="col-sm-8">
                <input type="text" name="alamat_ibu4" class="form-control" value="<?= h($dataMother['address4'] ?? '') ?>" >
              </div>
            </div>

            <!-- Negeri -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
              <div class="col-sm-8">
                <select name="negeri_ibu" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupNegeri as $opt): ?>
                    <option value="<?= h($opt['state_code']) ?>"
                        <?= h($dataMother['state_code'] ?? '') == $opt['state_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['state'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select> 
              </div>
            </div>

            <!-- Negara -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
              <div class="col-sm-8">
                <select name="negara_ibu" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupNegara as $opt): ?>
                    <option value="<?= h($opt['country_code']) ?>"
                        <?= h($dataMother['country_code'] ?? '') == $opt['country_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['country'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select> 
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="ibu-pekerjaan-panel" role="tabpanel" aria-labelledby="ibu-pekerjaan-tab" tabindex="0">
        <div class="icares-address-panel-header">
          <h5><?= h(tr('profile_maklumat_pekerjaan', 'Maklumat Pekerjaan')) ?></h5>
          <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
        </div>

        <div class="row">
          <!-- LEFT: Maklumat Pekerjaan -->
          <div class="col-12 gx-4">
            <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Maklumat Pekerjaan')) ?></h5>
            <hr>

            <!-- Status Pekerjaan -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Status Pekerjaan')) ?></label>
              <div class="col-sm-8">
                <select name="status_pekerjaan_ibu" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupEmploymentStatus as $opt): ?>
                    <option value="<?= h($opt['emp_status_code']) ?>"
                        <?= h($dataMother['employment_status'] ?? '') == $opt['emp_status_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['emp_status_my'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select> 
              </div>
            </div>

            <!-- Sektor Pekerjaan -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sektor_pekerjaan','Sektor Pekerjaan')) ?></label>
              <div class="col-sm-8">
                <select name="sektor_pekerjaan_ibu" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupEmploymentSector as $opt): ?>
                    <option value="<?= h($opt['emp_sector_code']) ?>"
                        <?= h($dataMother['employment_sector'] ?? '') == $opt['emp_sector_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['emp_sector_my'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Perkhidmatan Beruniform -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_perkhidmatan_beruniform','Perkhidmatan Beruniform')) ?></label>
              <div class="col-sm-8">
                  <select name="perkhidmatan_beruniform_ibu" class="form-select form-select-sm select2">
                    <option value="">-- Sila Pilih --</option>
                    <option value="YA" <?= h($dataMother['is_uniform_service'] ?? '') === 'YA' ? 'selected' : '' ?>>YA</option>
                    <option value="TIDAK" <?= h($dataMother['is_uniform_service'] ?? '') === 'TIDAK' ? 'selected' : '' ?>>TIDAK</option>
                  </select>     
              </div>
            </div>

            <!-- Jenis Perkhidmatan Beruniform -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jenis_perkhidmatan_beruniform','Jenis Perkhidmatan Beruniform')) ?></label>
              <div class="col-sm-8">
                <select id="select-jenis-uniform" name="jenis_perkhidmatan_beruniform_ibu" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupUniformService as $opt): ?>
                    <option value="<?= h($opt['service_code']) ?>"
                        <?= h($dataMother['uniform_service_type'] ?? '') == $opt['service_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['service_my'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Perkhidmatan Beruniform Lain -->
            <?php $isOthers = (h($dataMother['uniform_service_type'] ?? '') == 'OTH'); ?>
            <div id="section-uniform-lain" class="mb-2 row align-items-center" style="<?= $isOthers ? '' : 'display: none;' ?>">
              <label class="col-sm-4 col-form-label text-nowrap">Sila Nyatakan:</label>
              <div class="col-sm-8">
                <input type="text" id="perkhidmatan_beruniform_lain" name="perkhidmatan_beruniform_lain_ibu" class="form-control form-control-sm" value="<?= h($dataMother['uniform_service_others'] ?? '') ?>" placeholder="Contoh: Pengawal Keselamatan Swasta">
              </div>
            </div>

            <!-- Status Perkhidmatan Beruniform -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('status_perkhidmatan_beruniform','Status Perkhidmatan Beruniform')) ?></label>
              <div class="col-sm-8">
                <select name="status_perkhidmatan_beruniform_ibu" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupUniformServiceStatus as $opt): ?>
                    <option value="<?= h($opt['service_status_code']) ?>"
                        <?= h($dataMother['uniform_service_status'] ?? '') == $opt['service_status_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['service_status_my'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Pendapatan Bulanan Kasar -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('jumlah_pendapatan_bulanan_kasar','Pendapatan Bulanan Kasar')) ?></label>
              <div class="col-sm-8">
                <select name="pendapatan_bulanan_ibu" class="form-select form-select-sm select2">
                    <option value="0.00"><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupSalaryRange as $opt): ?>
                    <option value="<?= h($opt['value']) ?>"
                        <?= h($dataMother['gross_monthly_income'] ?? '') == $opt['value'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['label'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>     
              </div>
            </div>

            <!-- Perakuan Pendapatan -->
            <div class="mb-2 row align-items-center">
              <div class="col-sm-4">
                <label class="col-form-label text-nowrap"><?= h(tr('perakuan_pendapatan','Perakuan Pendapatan')) ?></label> 
                <i class="ri-information-line ms-1 text-info extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                  title="<?= h(tr('perakuan_pendapatan_note','(JPG/JPEG/PDF, maks 5MB)')) ?>"></i>
              </div>
              
              <div class="col-sm-8">
                <div class="upload-wrapper"> <?php if (!empty($dataMother['income_proof_docpath'])): ?>
                        <div class="existing-file-section d-flex align-items-center gap-2">
                            <a href="<?= base_url(($dataMother['income_proof_docpath']))?>" target="_blank" class="btn btn-sm btn-outline-warning">
                                <i class="ri-eye-line"></i> Lihat Perakuan Pendapatan
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-tukar-fail">
                                <i class="ri-upload-2-line"></i> Tukar Fail Baru
                            </button>
                        </div>

                        <div class="new-file-section d-none mt-2">
                            <div class="input-group input-group-sm">
                                <input type="file" name="dokumen_income" class="form-control form-control-sm input-upload-fail" accept=".jpg, .jpeg, .pdf" />
                                <button type="button" class="btn btn-danger btn-batal-tukar">Batal</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <input type="file" name="dokumen_income" class="form-control form-control-sm input-upload-fail" accept=".jpg, .jpeg, .pdf" />
                    <?php endif; ?>
                </div>                
              </div>
            </div>    
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="ibu-majikan-panel" role="tabpanel" aria-labelledby="ibu-majikan-tab" tabindex="0">
        <div class="icares-address-panel-header">
          <h5><?= h(tr('profile_alamat_majikan', 'Alamat Majikan')) ?></h5>
          <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
        </div>
        <div class="row">
          <!-- RIGHT: Alamat Majikan -->
          <div class="col-12 gx-4">
            <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Alamat Majikan')) ?></h5>
            <hr>

            <!-- Nama Majikan -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_majikan','Nama Majikan')) ?></label>
              <div class="col-sm-8">
                <textarea type="text" name="majikan" class="form-control"><?= h($dataMother['employer_name'] ?? '') ?></textarea>
              </div>
            </div>

            <!-- Alamat Baris 1 -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
              <div class="col-sm-8">
                <input type="text" name="alamat_majikan1" class="form-control" value="<?= h($dataMother['employer_address1'] ?? '') ?>" >
              </div>                 
            </div>

            <!-- Alamat Baris 2 -->
            <div class="mb-2 row">
              <div class="offset-sm-4 col-sm-8">
                <input type="text" name="alamat_majikan2" class="form-control" value="<?= h($dataMother['employer_address2'] ?? '') ?>" >
              </div>
            </div>                        

            <!-- Poskod -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
              <div class="col-sm-8">
                <input type="text" name="alamat_majikan3" class="form-control" value="<?= h($dataMother['employer_address3'] ?? '') ?>" >
              </div>
            </div>

            <!-- Bandar -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
              <div class="col-sm-8">
                <input type="text" name="alamat_majikan4" class="form-control" value="<?= h($dataMother['employer_address4'] ?? '') ?>" >
              </div>
            </div>

            <!-- Negeri -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
              <div class="col-sm-8">
                <select name="negeri_majikan" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupNegeri as $opt): ?>
                    <option value="<?= h($opt['state_code']) ?>"
                        <?= h($dataMother['employer_state_code'] ?? '') == $opt['state_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['state'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Negara -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
              <div class="col-sm-8">
                <select name="negara_majikan" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupNegara as $opt): ?>
                    <option value="<?= h($opt['country_code']) ?>"
                        <?= h($dataMother['employer_country_code'] ?? '') == $opt['country_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['country'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

      <!-- BUTTON -->
      <div class="row">
        <div class="col-12 text-end mt-4">
          <button type="submit" class="btn btn-primary rounded-3 px-4">
            <i class="ri-save-3-line me-2"></i>
            <?= h(tr('profile_save_button','Simpan')) ?>
          </button>
        </div>
      </div>

</form>
