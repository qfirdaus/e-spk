<div class="modal fade modal-gradient" id="tambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line me-1"></i>
          <?= h(tr('ttl_tambah_clo', 'Tambah Maklumat CLO')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" id="formTambahCLO">
        <div class="modal-body">
          
          <input name="txtsesiid" id="txtsesiid" type="hidden" readonly>
          <input name="txtkursusid" id="txtkursusid" type="hidden" readonly>
          <input name="action" value="add" type="hidden">

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('lbl_sesi', 'Sesi')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtsesi" id="txtsesi" type="text" class="form-control form-control-sm bg-light" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('lbl_kursus', 'Kursus')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtkursus" id="txtkursus" type="text" class="form-control form-control-sm bg-light" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('lbl_kod_clo', 'Kod CLO')) ?> <span class="text-danger">*</span>
            </label>
            <div class="col-sm-4">                                                
              <select class="form-select form-select-sm" name="txtkodclo" id="txtkodclo" required>
                  <option value="" disabled selected>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                  <?php for ($i = 1; $i <= 15; $i++): ?>
                      <option value="CLO<?= $i ?>">CLO<?= $i ?></option>
                  <?php endfor; ?>
              </select>
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('lbl_keterangan_clo', 'Keterangan CLO')) ?> <span class="text-danger">*</span>
            </label>
            <div class="col-sm-9">
              <textarea name="txtketeranganclo" id="txtketeranganclo" class="form-control form-control-sm" rows="3" required></textarea>
            </div>
          </div>

          <hr>

          <div class="mb-3 row">
            <div class="col-12">
              <label class="fw-semibold text-primary mb-2"><?= h(tr('panel_senarai_plo', 'Senarai PLO (Tandakan yang berkaitan)')) ?></label>
              <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                <?php if (!empty($data['list_plo'])): ?>
                    <div class="row">
                    <?php foreach ($data['list_plo'] as $plo): ?>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="chkplo[]" value="<?= h((string)$plo["id_plo"]) ?>" id="plo_<?= h((string)$plo["id_plo"]) ?>">
                                <label class="form-check-label" for="plo_<?= h((string)$plo["id_plo"]) ?>">
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

          <div class="row">
            <!-- Kaedah Pengajaran -->
            <div class="col-md-6 mb-3">
              <label class="fw-semibold text-primary mb-2"><?= h(tr('panel_kaedah', 'Kaedah Pengajaran')) ?></label>
              <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                <?php if (!empty($data['list_kaedah'])): ?>
                    <?php foreach ($data['list_kaedah'] as $kaedah): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="chkkaedah[]" value="<?= h((string)$kaedah["id_kaedah_pengajaran"]) ?>" id="kdh_<?= h((string)$kaedah["id_kaedah_pengajaran"]) ?>">
                            <label class="form-check-label" for="kdh_<?= h((string)$kaedah["id_kaedah_pengajaran"]) ?>">
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
                            <input class="form-check-input" type="checkbox" name="chkpenilaian[]" value="<?= h((string)$nilai["id_penilaian"]) ?>" id="nil_<?= h((string)$nilai["id_penilaian"]) ?>">
                            <label class="form-check-label" for="nil_<?= h((string)$nilai["id_penilaian"]) ?>">
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
          <button type="submit" id="btnSimpanCLO" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('btn_simpan', 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>