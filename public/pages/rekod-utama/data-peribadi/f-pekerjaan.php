<div class="skeleton-loader" style="display: none;">
  <div class="skeleton-row"></div>
  <div class="skeleton-row"></div>
  <div class="skeleton-row"></div>
</div>

<?php
  $lookupStatusKerja = $lookupAll['status_kerja'] ?? [];
  $lookupSektorKerja = $lookupAll['sektor_kerja'] ?? [];
  $lookupNegeri = $lookupAll['negeri'] ?? [];
  $lookupNegara = $lookupAll['negara'] ?? [];
?>

<div class="icares-address-content">
  <div class="tab-pane show active">
    <div class="icares-address-panel-header">
      <h5><?= h(tr('tab_maklumat_pekerjaan', 'Maklumat Pekerjaan')) ?></h5>
      <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
    </div>

    <form id="form-pekerjaan" method="post" enctype="multipart/form-data">
      <div class="row">
        <div class="col-12">
          <div class="row">
            <div class="col-md-6 gx-4">
              <!-- Status Pekerjaan -->
              <div class="mb-2 row align-items-center">
                <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Status Pekerjaan')) ?></label>
                <div class="col-sm-8">
                  <select name="status_pekerjaan" class="form-select form-select-sm select2">
                      <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                      <?php foreach ($lookupStatusKerja as $opt): ?>
                      <option value="<?= h($opt['emp_status_code']) ?>"
                          <?= h($dataPekerjaan['emp_status'] ?? '') == $opt['emp_status_code'] ? 'selected' : '' ?>>
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
                  <select name="sektor_pekerjaan" class="form-select form-select-sm select2">
                      <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                      <?php foreach ($lookupSektorKerja as $opt): ?>
                      <option value="<?= h($opt['emp_sector_code']) ?>"
                          <?= h($dataPekerjaan['emp_sector'] ?? '') == $opt['emp_sector_code'] ? 'selected' : '' ?>>
                          <?= h(strtoupper($opt['emp_sector_my'])) ?>
                      </option>
                      <?php endforeach; ?>
                  </select>                  
                </div>                 
              </div>   

              <!-- Status Pekerjaan Sambilan -->
              <div class="mb-2 row align-items-center">
                <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan_sambilan','Status Pekerjaan Sambilan')) ?></label>
                <div class="col-sm-8">
                  <select name="status_pekerjaan_sambilan" class="form-select form-select-sm select2">
                    <option value="">-- Sila Pilih --</option>
                    <option value="YA" <?= h($dataPekerjaan['parttime_status'] ?? '') === 'YA' ? 'selected' : '' ?>>YA</option>
                    <option value="TIDAK" <?= h($dataPekerjaan['parttime_status'] ?? '') === 'TIDAK' ? 'selected' : '' ?>>TIDAK</option>
                  </select>
                </div>                 
              </div>                  

              <!-- Jenis Pekerjaan Sambilan -->
              <div class="mb-2 row align-items-center">
                <div class="col-sm-4">
                    <label class="col-form-label text-nowrap"> <?= h(tr('profile_jenis_pekerjaan_sambilan','Jenis Pekerjaan Sambilan')) ?> </label> 
                    <i class="ri-information-line ms-1 text-info extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                    aria-label="<?= h(tr('profile_jenis_pekerjaan','Jenis Pekerjaan Sambilan')) ?>" data-bs-original-title="<?= h(tr('profile_jenis_pekerjaan','Sila nyatakan jenis pekerjaan sambilan, jika ada')) ?>"></i>
                </div>
                <div class="col-sm-8">
                  <input type="text" name="jenis_pekerjaan_sambilan" class="form-control" value="<?= h($dataPekerjaan['parttime_type'] ?? '') ?>" >
                </div>
              </div>
            </div>

            <div class="col-md-6 gx-4">                   
              <!-- Alamat Baris 1 -->
              <div class="mb-2 row align-items-center">
                <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                <div class="col-sm-8">
                  <input type="text" name="alamat1" class="form-control" value="<?= h($dataPekerjaan['address1'] ?? '') ?>">
                </div>                 
              </div>

              <!-- Alamat Baris 2 -->
              <div class="mb-2 row align-items-center">
                <label class="col-sm-4 col-form-label text-nowrap"></label>
                <div class="col-sm-8">
                  <input type="text" name="alamat2" class="form-control" value="<?= h($dataPekerjaan['address2'] ?? '') ?>">
                </div>
              </div>                                

              <!-- Poskod -->
              <div class="mb-2 row align-items-center">
                <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                <div class="col-sm-8">
                  <input type="text" name="poskod" class="form-control" value="<?= h($dataPekerjaan['address3'] ?? '') ?>">
                </div>
              </div>

              <!-- Bandar -->
              <div class="mb-2 row align-items-center">
                <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                <div class="col-sm-8">
                  <input type="text" name="bandar" class="form-control" value="<?= h($dataPekerjaan['address4'] ?? '') ?>">
                </div>
              </div>

              <!-- Negeri -->
              <div class="mb-2 row align-items-center">
                <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                <div class="col-sm-8">
                  <select name="negeri" class="form-select form-select-sm select2">
                      <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                      <?php foreach ($lookupNegeri as $opt): ?>
                      <option value="<?= h($opt['state_code']) ?>"
                          <?= h($dataPekerjaan['state']  ?? '') == $opt['state_code'] ? 'selected' : '' ?>>
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
                  <select name="negara" class="form-select form-select-sm select2">
                      <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                      <?php foreach ($lookupNegara as $opt): ?>
                      <option value="<?= h($opt['country_code']) ?>"
                          <?= h($dataPekerjaan['country'] ?? '') == $opt['country_code'] ? 'selected' : '' ?>>
                          <?= h(strtoupper($opt['country'])) ?>
                      </option>
                      <?php endforeach; ?>
                  </select>   
                </div>
              </div>                      
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-end pt-1">
              <button type="submit" class="btn btn-primary rounded-3 px-4">
                <i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
              </button>
            </div>

          </div>
        </div>
      </div>
    </form>
  </div>
</div>