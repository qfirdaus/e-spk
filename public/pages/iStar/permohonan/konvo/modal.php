<?php
  //$lookupAll call from index.php
  $lookupWakil = $lookupAll['wakil'] ?? [];
  $lookupPeringkat = $lookupAll['peringkat'] ?? [];
  $lookupPencapaian = $lookupAll['pencapaian'] ?? [];
  $lookupJawatan = $lookupAll['jawatan'] ?? [];
  $lookupKategoriPerjawatan = $lookupAll['kategori_perjawatan'] ?? [];
?>
<!-- // Modal: Add Penglibatan Program -->
<div class="modal fade" id="penglibatanAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line"></i>
          <?= h(tr('profile_penglibatan_program','Tambah Penglibatan Program')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="penglibatanForm" method="post" enctype="multipart/form-data">
        <input type="hidden" name="icares_form" value="istar_konvo_penglibatan_program">

        <div class="modal-body">
          <div class="row">

            <!-- LEFT COLUMN -->
            <div class="col-md-6">

              <!-- Nama Program -->
              <div class="mb-3">
                <label class="form-label">
                  <?= h(tr('nama_program_pertandingan','Nama Program / Nama Pertandingan')) ?>
                </label>
                <input type="text" name="nama_penuh" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
              </div>

              <!-- Tarikh -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('profile_tarikh','Tarikh')) ?></label>
                <input type="text" name="tarikh" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
              </div>              

              <!-- Wakil -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('wakil','Wakil')) ?></label>
                <select name="wakil" class="form-select form-select-sm" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupWakil as $opt): ?>
                        <option value="<?= h($opt['idwakil']) ?>">
                            <?= h(strtoupper($opt['wakil_my'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-md-6">

              <!-- Peringkat -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('peringkat','Peringkat')) ?></label>
                <select name="peringkat" class="form-select form-select-sm" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupPeringkat as $opt): ?>
                        <option value="<?= h($opt['peringkat_code']) ?>">
                            <?= h(strtoupper($opt['peringkat_my'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              </div>

              <!-- Pencapaian -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('pencapaian','Pencapaian')) ?></label>
                <select name="pencapaian" class="form-select form-select-sm" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupPencapaian as $opt): ?>
                        <option value="<?= h($opt['pencapaian_code']) ?>">
                            <?= h(strtoupper($opt['pencapaian_my'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              </div>

              <!-- Dokumen -->
              <div class="mb-3">
                <label class="form-label">
                  <?= h(tr('dokumen_penglibatan','Dokumen Sokongan')) ?>
                </label>
                <input type="file" name="dokumen-penglibatan" class="form-control"
                       accept=".jpg,.jpeg,.pdf"
                       onchange="checkFileSize(this)" required>
                <small class="text-danger">
                  <?= h(tr('dokumen_penglibatan_note','Max 5MB (JPG/JPEG/PDF)')) ?>
                </small>
              </div>

            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('profile_save_button','Simpan')) ?>
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- ########################################## -->

<!-- MODAL: Add Jawatan Disandang -->
<div class="modal fade" id="jawatanAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line"></i>
          <?= h(tr('profile_jawatan_disandang','Tambah Jawatan Disandang')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="jawatanForm" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">

            <!-- LEFT COLUMN -->
            <div class="col-md-6">
              <!-- Kategori Perjawatan -->
              <div class="mb-3">
                <label class="form-label">
                  <?= h(tr('kategori_perjawatan','Kategori Perjawatan')) ?>
                </label>
                <select name="kategori_aktiviti" id="kategoriAktiviti" class="form-select form-select-sm" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupKategoriPerjawatan as $opt): ?>
                        <option value="<?= h($opt['kod_kategori_aktiviti']) ?>"
                            data-idaktiviti="<?= h($opt['id']) ?>"
                            data-aktiviti_text="<?= h($opt['kategori_aktiviti']) ?>" >
                            <?= h(strtoupper($opt['kategori_aktiviti'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="id_aktiviti" id="idAktiviti">
                <input type="hidden" name="aktiviti_text" id="aktivitiText">
              </div>

              <!-- Nama Program -->
              <div class="mb-3">
                <label class="form-label">
                  <?= h(tr('nama_badan_pelajar_program','Nama Badan Pelajar / Program')) ?>
                </label>
                <input type="text" name="nama_bp_program" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
              </div>

              <!-- Tarikh -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('profile_tarikh','Tarikh')) ?></label>
                <input type="text" name="tarikh" class="form-control datepicker" placeholder="dd-mm-yyyy" required>
              </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-md-6">

              <!-- Jawatan -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('jawatan','Jawatan')) ?></label>
                <select name="jawatan" id="jawatan" class="form-select form-select-sm" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php 
                      foreach ($lookupJawatan as $opt): 
                        if (h(strtoupper($opt['keteranganBP'])) != ''):  $str = ' / '; 
                        else: $str = ''; 
                        endif;                       
                    ?>
                        <option value="<?= h($opt['id_jawatan']) ?>"
                                data-jawatan_text="<?= h(strtoupper($opt['keterangan']))  . $str . h(strtoupper($opt['keteranganBP'])) ?>" >
                            <?= h(strtoupper($opt['keterangan']))  . $str . h(strtoupper($opt['keteranganBP'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="jawatan_text" id="jawatanText">
              </div>

              <!-- Peringkat -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('peringkat','Peringkat')) ?></label>
                <select name="peringkat" id="peringkat" class="form-select form-select-sm" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupPeringkat as $opt): ?>
                        <option value="<?= h($opt['peringkat_code']) ?>">
                            <?= h(strtoupper($opt['peringkat_my'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              </div>

              <!-- Dokumen -->
              <div class="mb-3">
                <label class="form-label">
                  <?= h(tr('dokumen_penglibatan','Dokumen Sokongan')) ?>
                </label>
                <input type="file" name="dokumen-jawatan" class="form-control"
                       accept=".jpg,.jpeg,.pdf"
                       onchange="checkFileSize(this)" required>
                <small class="text-danger">
                  <?= h(tr('dokumen_penglibatan_note','Max 5MB (JPG/JPEG/PDF)')) ?>
                </small>
              </div>

            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('template_senarai_crud_btn_cancel', 'Cancel')) ?>
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('profile_save_button', 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
