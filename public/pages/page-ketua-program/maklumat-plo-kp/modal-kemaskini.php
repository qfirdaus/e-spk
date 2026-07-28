<div class="modal fade modal-gradient" id="kemaskini" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-edit-circle-line me-1"></i>
          <?= h(tr('TTL-KEMASKINI-PLO', $lang['TTL-KEMASKINI-PLO'] ?? 'Kemaskini PLO')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" method="POST">
        <div class="modal-body">
          
          <input name="txtidplo" id="txtidplo" type="hidden" readonly>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', $lang['LBL-SESI-KEMASUKAN'] ?? 'Sesi Kemasukan')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtsesiid" id="txtsesiid" type="hidden" class="form-control form-control-sm" autocomplete="off">
              <input name="txtsesi" id="txtsesi" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-PROGRAM', $lang['LBL-PROGRAM'] ?? 'Program')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtprogramid" id="txtprogramid" type="hidden" class="form-control form-control-sm" autocomplete="off">
              <input name="txtprogram" id="txtprogram" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KOD-PLO', $lang['LBL-KOD-PLO'] ?? 'Kod PLO')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtkodplo" id="txtkodplo" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-semibold pt-2">
              <?= h(tr('LBL-KETERANGAN-PLO', $lang['LBL-KETERANGAN-PLO'] ?? 'Keterangan PLO')) ?>
            </label>
            <div class="col-sm-9">
              <textarea name="txtketeranganplo" id="txtketeranganplo" class="form-control" rows="3" autocomplete="off" required></textarea>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KOD-MQF', $lang['LBL-KOD-MQF'] ?? 'Kod MQF')) ?>
            </label>
            <div class="col-sm-9"> 
              <select class="form-select form-select-sm select2" name="selectkodmqf_edit" id="selectkodmqf_edit" required>
                  <option value="" disabled selected>- <?= h(tr('SELECT-PILIH', 'Sila Pilih')) ?> -</option>
                  <?php foreach ($data['list_mqf'] as $row): ?>
                  <option value="<?= h($row['kod_mqf']) ?>">
                      <?= h($row['kod_mqf']) ?>
                  </option>
                  <?php endforeach; ?>
              </select>                               
            </div>
          </div>

          <!-- PEO -->
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-semibold pt-1">
              <?= h(tr('LBL-PEMETAAN-PEO', $lang['LBL-PEMETAAN-PEO'] ?? 'Pemetaan PEO')) ?>
            </label>
            <div class="col-sm-9">
              <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                <?php if (!empty($data['list_peo']) && is_array($data['list_peo'])): ?>
                  <?php foreach ($data['list_peo'] as $peo): ?>
                    <div class="form-check mb-1">
                      <input class="form-check-input chk-peo-edit" 
                            type="checkbox" 
                            name="peo_ids[]" 
                            id="edit_peo_<?= h($peo['id_peo']) ?>" 
                            value="<?= h($peo['id_peo']) ?>">
                      <label class="form-check-label ms-1" for="edit_peo_<?= h($peo['id_peo']) ?>">
                        <span class="badge bg-primary me-1"><?= h($peo['kod_peo']) ?></span>
                        <small class="text-muted"><?= h($peo['keterangan_bm']) ?></small>
                      </label>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <small class="text-muted italic"><?= h(tr('LBL-TIADA-PEO', 'Tiada rekod PEO dijumpai')) ?></small>
                <?php endif; ?>
              </div>
            </div>
          </div>          
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', $lang['BTN-BATAL'] ?? 'Batal')) ?>
          </button>
          <button type="button" id="btnKemaskiniPlo" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('BTN-SIMPAN', $lang['BTN-SIMPAN'] ?? 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>