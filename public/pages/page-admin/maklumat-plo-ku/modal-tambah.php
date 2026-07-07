<div class="modal fade modal-gradient" id="tambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line me-1"></i>
          <?= h(tr('TTL-TAMBAH-PLO', 'Tambah PLO Baharu')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" action="sql_add_plo.php" method="POST">
        <div class="modal-body">
          
          <input name="txtprogramid" id="txtprogramid" type="hidden" readonly>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', 'Sesi Kemasukan')) ?>
            </label>
            <div class="col-sm-3 mb-2 mb-sm-0">
              <input name="txtsesiid" id="txtsesiid" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
            <div class="col-sm-6">
              <input name="txtsesi" id="txtsesi" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KOD-PLO', 'Kod PLO')) ?>
            </label>
            <div class="col-sm-9">
              <select class="form-select form-select-sm select2" name="selectkodplo" id="selectkodplo" required>
                <option value="" disabled selected>- <?= h(tr('SELECT-PILIH', 'Sila Pilih')) ?> -</option>
                <?php if (isset($plo) && is_array($plo)): ?>
                  <?php foreach ($plo as $kod_plo): ?>
                    <option value="<?= h($kod_plo) ?>"><?= h($kod_plo) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-semibold pt-2">
              <?= h(tr('LBL-KETERANGAN-PLO', 'Keterangan PLO')) ?>
            </label>
            <div class="col-sm-9">
              <textarea name="txtketeranganplo" id="txtketeranganplo" class="form-control" rows="3" autocomplete="off" required></textarea>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KOD-MQF', 'Kod MQF')) ?>
            </label>
            <div class="col-sm-9">
              <select class="form-select form-select-sm select2" name="selectkodmqf" id="selectkodmqf" required>
                  <option value="" disabled selected>- <?= h(tr('SELECT-PILIH', 'Sila Pilih')) ?> -</option>
                  <?php foreach ($data['list_mqf'] as $row): ?>
                  <option value="<?= h($row['kod_mqf']) ?>">
                      <?= h($row['kod_mqf']) ?>
                  </option>
                  <?php endforeach; ?>
              </select>                   
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', 'Batal')) ?>
          </button>
          <button type="button" id="btnHantarPlo" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('BTN-SIMPAN', 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>