<div class="modal fade modal-gradient" id="kemaskini" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-edit-circle-line me-1"></i>
          <?= h(tr('TTL-KEMASKINI-PEO', $lang['TTL-KEMASKINI-PEO'] ?? 'Kemaskini PEO')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" method="POST">
        <div class="modal-body">
          
          <input name="txtidpeo_edit" id="txtidpeo_edit" type="hidden" readonly>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', $lang['LBL-SESI-KEMASUKAN'] ?? 'Sesi Kemasukan')) ?>
            </label>
            <input name="txtptj_edit" id="txtptj_edit" type="hidden" class="form-control form-control-sm" autocomplete="off" readonly>
            <div class="col-sm-3 mb-2 mb-sm-0">
              <input name="txtsesiid_edit" id="txtsesiid_edit" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
            <div class="col-sm-6">
              <input name="txtsesi_edit" id="txtsesi_edit" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-PROGRAM', $lang['LBL-PROGRAM'] ?? 'Program')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtprogramid_edit" id="txtprogramid_edit" type="hidden" class="form-control form-control-sm" autocomplete="off">
              <input name="txtprogram_edit" id="txtprogram_edit" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KOD-PEO', $lang['LBL-KOD-PEO'] ?? 'Kod PEO')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtkodpeo_edit" id="txtkodpeo_edit" type="text" class="form-control form-control-sm" autocomplete="off" readonly>
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-semibold pt-2">
              <?= h(tr('LBL-KETERANGAN-PEO', $lang['LBL-KETERANGAN-PEO'] ?? 'Keterangan PEO')) ?>
            </label>
            <div class="col-sm-9">
              <textarea name="txtketeranganpeo_edit" id="txtketeranganpeo_edit" class="form-control" rows="3" autocomplete="off" required></textarea>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-TARIKH-SENAT', 'Tarikh Senat')) ?>
            </label>
            <div class="col-sm-9">
              <input type="text" name="txttarikhsenat_edit" id="txttarikhsenat_edit" class="form-control datepicker" placeholder="dd/mm/yyyy" required>               
            </div>
          </div>          

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', $lang['BTN-BATAL'] ?? 'Batal')) ?>
          </button>
          <button type="button" id="btnKemaskiniPeo" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('BTN-SIMPAN', $lang['BTN-SIMPAN'] ?? 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>