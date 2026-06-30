<?php
  $lookupBank = $lookupAll['bank'] ?? [];
?>

  <div class="icares-address-content">
    <div class="tab-pane show active">
      <div class="icares-address-panel-header">
        <h5><?= h(tr('tab_maklumat_akaun', 'Maklumat Akaun')) ?></h5>
        <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
      </div>

  <form id="form-akaun" method="post" enctype="multipart/form-data">
    <div class="row">
      <div class="col-12">
        <div class="row">
          <div class="col-md-6 gx-4">

            <!-- Nama Bank -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('nama_bank','Nama Bank')) ?></label>
              <div class="col-sm-8">
                <select name="kod_bank" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupBank as $opt): ?>
                    <option value="<?= h($opt['bank_code']) ?>"
                        <?= h($dataAkaun['bank_code'] ?? '') == $opt['bank_code'] ? 'selected' : '' ?>>
                        <?= h(strtoupper($opt['bank_name'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>  
              </div>                 
            </div>                        
            
            <!-- No. Akaun -->
            <div class="mb-2 row align-items-center">
              <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('no_akaun','No. Akaun')) ?></label>
              <div class="col-sm-8">
                <input type="text" name="no_akaun" class="form-control" value="<?= h($dataAkaun['account_no'] ?? '') ?>" >
              </div>                 
            </div> 

            <!-- Dokumen OKU -->
            <div class="mb-2 row align-items-center">
              <div class="col-sm-4">
                <label class="col-form-label text"><?= h(tr('dokumen_akaun','Dokumen Pengesahan Akaun')) ?></label> 
                <i class="ri-information-line ms-1 text-info extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                  title="<?= h(tr('dokumen_akaun_note','(JPG/JPEG/PDF, maks 5MB)')) ?>"></i>
              </div>
              
              <div class="col-sm-8">
              <div class="upload-wrapper"> <?php if (!empty($dataAkaun['document_path'])): ?>
                      <div class="existing-file-section d-flex align-items-center gap-2">
                          <a href="<?= base_url($dataAkaun['document_path']) ?>" target="_blank" class="btn btn-sm btn-outline-warning">
                              <i class="ri-eye-line"></i> Lihat Penyata Bank
                          </a>
                          <button type="button" class="btn btn-sm btn-outline-secondary btn-tukar-fail">
                              <i class="ri-upload-2-line"></i> Tukar Fail Baru
                          </button>
                      </div>

                      <div class="new-file-section d-none mt-2">
                          <div class="input-group input-group-sm">
                              <input type="file" name="dokumen_akaun" class="form-control form-control-sm input-upload-fail" accept=".jpg, .jpeg, .pdf" />
                              <button type="button" class="btn btn-danger btn-batal-tukar">Batal</button>
                          </div>
                      </div>
                  <?php else: ?>
                      <input type="file" name="dokumen_akaun" class="form-control form-control-sm input-upload-fail" accept=".jpg, .jpeg, .pdf" />
                  <?php endif; ?>
              </div>
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
