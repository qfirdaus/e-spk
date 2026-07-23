<div class="modal fade modal-gradient" id="tambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line me-1"></i>
          <?= h(tr('TTL-TAMBAH-PEO', 'Tambah PEO Baharu')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" method="POST">
        <div class="modal-body">

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', 'Sesi Kemasukan')) ?>
            </label>
            <input name="txtptj" id="txtptj" type="hidden" class="form-control form-control-sm" autocomplete="off" readonly>
            <div class="col-sm-3 mb-2 mb-sm-0">
              <input name="txtsesiid" id="txtsesiid" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
            <div class="col-sm-6">
              <input name="txtsesi" id="txtsesi" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-Program', 'Program')) ?>
            </label>
            <div class="col-sm-9 mb-2 mb-sm-0">
              <input name="txtprogramid" id="txtprogramid" type="hidden" readonly>
              <input name="txtprogram" id="txtprogram" type="text" class="form-control form-control-sm" autocomplete="off" readonly="">
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KOD-PEO', 'Kod PEO')) ?>
            </label>
            <div class="col-sm-9">
              <select class="form-select form-select-sm select2" name="selectkodpeo" id="selectkodpeo" required>
                <option value="" disabled selected>- <?= h(tr('SELECT-PILIH', 'Sila Pilih')) ?> -</option>
                <?php if (isset($peo) && is_array($peo)): ?>
                  <?php foreach ($peo as $kod_peo): ?>
                    <option value="<?= h($kod_peo) ?>"><?= h($kod_peo) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>              
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-semibold pt-2">
              <?= h(tr('LBL-KETERANGAN-PEO', 'Keterangan PEO')) ?>
            </label>
            <div class="col-sm-9">
              <textarea name="txtketeranganpeo" id="txtketeranganpeo" class="form-control" rows="3" autocomplete="off" required></textarea>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-TARIKH-SENAT', 'Tarikh Senat')) ?>
            </label>
            <div class="col-sm-9">
              <input type="text" name="txttarikhsenat" class="form-control datepicker" placeholder="dd/mm/yyyy" required>               
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', 'Batal')) ?>
          </button>
          <button type="button" id="btnHantarPeo" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('BTN-SIMPAN', 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>