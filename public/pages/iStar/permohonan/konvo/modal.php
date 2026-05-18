<?php
  //$lookupAll call from index.php
  $lookupWakil = $lookupAll['wakil'] ?? [];
  $lookupPeringkat = $lookupAll['peringkat'] ?? [];
  $lookupPencapaian = $lookupAll['pencapaian'] ?? [];
?>
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
                <input type="text" name="nama_penuh" class="form-control">
              </div>

              <!-- Tarikh -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('profile_tarikh','Tarikh')) ?></label>
                <input type="date" name="tarikh" class="form-control">
              </div>

              <!-- Wakil -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('wakil','Wakil')) ?></label>
                <select name="wakil" class="form-select form-select-sm">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupWakil as $opt): ?>
                        <option value="<?= h($opt['wakil_code']) ?>">
                            <?= h($opt['wakil_my']) ?>
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
                <select name="peringkat" class="form-select form-select-sm">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupPeringkat as $opt): ?>
                        <option value="<?= h($opt['peringkat_code']) ?>">
                            <?= h($opt['peringkat_my']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
              </div>

              <!-- Pencapaian -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('pencapaian','Pencapaian')) ?></label>
                <select name="pencapaian" class="form-select form-select-sm">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupPencapaian as $opt): ?>
                        <option value="<?= h($opt['pencapaian_code']) ?>">
                            <?= h($opt['pencapaian_my']) ?>
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
                       onchange="checkFileSize(this)">
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
