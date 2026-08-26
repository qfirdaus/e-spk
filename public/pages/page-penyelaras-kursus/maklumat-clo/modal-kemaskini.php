<div class="modal fade modal-gradient" id="kemaskini" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-edit-2-line me-1"></i>
          <?= h(tr('ttl_kemaskini_clo', 'Kemaskini Maklumat CLO')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" id="formKemaskiniCLO">
        <div class="modal-body">
          
          <!-- Hidden Inputs -->
          <input name="txtidclo" id="txtidclo" type="hidden" readonly>
          <input name="action" value="update" type="hidden">

          <!-- Paparan Sesi & Kursus -->
          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="fw-semibold"><?= h(tr('lbl_sesi', 'Sesi')) ?></label>
                  <input type="text" id="edit_txtsesi" class="form-control form-control-sm bg-light" readonly>
              </div>
              <div class="col-md-6 mb-3">
                  <label class="fw-semibold"><?= h(tr('lbl_kursus', 'Kursus')) ?></label>
                  <input type="text" id="edit_txtkursus" class="form-control form-control-sm bg-light" readonly>
              </div>
          </div>

          <!-- Input Kod CLO -->
          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('lbl_kod_clo', 'Kod CLO')) ?>
            </label>
            <div class="col-sm-4">                                                
               <input name="txtkodclo" id="txtkodclo" type="text" class="form-control form-control-sm bg-light" readonly>
            </div>
          </div>

          <!-- Keterangan CLO -->
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('lbl_keterangan_clo', 'Keterangan CLO')) ?> <span class="text-danger">*</span>
            </label>
            <div class="col-sm-9">
              <textarea name="txtketeranganclo" id="txtketeranganclo" class="form-control form-control-sm" rows="3" required></textarea>
            </div>
          </div>

          <hr>

          <!-- Checkbox Mapping PLO -->
          <div class="mb-3 row">
            <div class="col-12">
              <label class="fw-semibold text-primary mb-2"><?= h(tr('panel_senarai_plo', 'Senarai PLO (Tandakan yang berkaitan)')) ?></label>
              <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                <?php if (!empty($data['list_plo'])): ?>
                    <div class="row">
                    <?php foreach ($data['list_plo'] as $plo): ?>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input edit-chk-plo" type="checkbox" name="chkplo[]" value="<?= h((string)$plo["id_plo"]) ?>" id="edit_plo_<?= h((string)$plo["id_plo"]) ?>">
                                <label class="form-check-label" for="edit_plo_<?= h((string)$plo["id_plo"]) ?>">
                                    <strong><?= h($plo["kod_plo"]) ?></strong> : <?= h($plo["keterangan_bm"]) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted small"><i>Sila pilih Sesi & Kursus di halaman carian terlebih dahulu.</i></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Checkbox Kaedah & Penilaian -->
          <div class="row">
            <!-- Kaedah Pengajaran -->
            <div class="col-md-6 mb-3">
              <label class="fw-semibold text-primary mb-2"><?= h(tr('panel_kaedah', 'Kaedah Pengajaran')) ?></label>
              <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                <?php if (!empty($data['list_kaedah'])): ?>
                    <?php foreach ($data['list_kaedah'] as $kaedah): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input edit-chk-kaedah" type="checkbox" name="chkkaedah[]" value="<?= h((string)$kaedah["id_kaedah_pengajaran"]) ?>" id="edit_kdh_<?= h((string)$kaedah["id_kaedah_pengajaran"]) ?>">
                            <label class="form-check-label" for="edit_kdh_<?= h((string)$kaedah["id_kaedah_pengajaran"]) ?>">
                                <?= h($kaedah["kaedah_pengajaran"]) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted small"><i>Tiada rekod.</i></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Penilaian -->
            <div class="col-md-6 mb-3">
              <label class="fw-semibold text-primary mb-2"><?= h(tr('panel_penilaian', 'Penilaian')) ?></label>
              <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                <?php if (!empty($data['list_penilaian'])): ?>
                    <?php foreach ($data['list_penilaian'] as $nilai): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input edit-chk-penilaian" type="checkbox" name="chkpenilaian[]" value="<?= h((string)$nilai["id_penilaian"]) ?>" id="edit_nil_<?= h((string)$nilai["id_penilaian"]) ?>">
                            <label class="form-check-label" for="edit_nil_<?= h((string)$nilai["id_penilaian"]) ?>">
                                <?= h($nilai["penilaian"]) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted small"><i>Tiada rekod.</i></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('btn_batal', 'Batal')) ?>
          </button>
          <button type="submit" id="btnSimpanKemaskini" class="btn btn-sm btn-success">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('btn_simpan', 'Simpan Perubahan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>