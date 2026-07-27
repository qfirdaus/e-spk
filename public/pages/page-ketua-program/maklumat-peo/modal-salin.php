<div class="modal fade modal-gradient" id="salin" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-file-copy-line me-1"></i>
          <?= h(tr('TTL-SALIN-PEO', $lang['TTL-SALIN-PEO'] ?? 'Salin PEO')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" method="POST">
        <div class="modal-body">

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', $lang['LBL-SESI-KEMASUKAN'] ?? 'Sesi Kemasukan')) ?>
            </label>
            <div class="col-sm-9">      
                <input name="txtsesi" id="txtsesi" type="hidden" readonly>
                <input name="txtsesiid" id="txtsesiid" type="hidden" readonly>
                <input name="txtprogramid" id="txtprogramid" type="hidden" readonly>
                <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesiModal" id="selectSesiModal">
                    <option value="" <?= (empty($_SESSION["sesi"])) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                    <?php foreach ($data['list_sesi'] as $sesi): ?>
                    <option value="<?= h($sesi['sesi2']) ?>" <?= ($sesi['sesi2'] === ($data['selected_term']['sesi2'] ?? '')) ? 'selected' : '' ?> > 
                        <?= h($sesi['term']) ?> - <?= h($sesi['sesi2']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>           
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', $lang['LBL-SESI-KEMASUKAN'] ?? 'Program')) ?>
            </label>
            <div class="col-sm-9">
              <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectProgramModal" id="selectProgramModal">
                  <option value="" <?= (empty($_SESSION["program"])) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                  <?php foreach ($data['list_program'] as $program): ?>
                  <option value="<?= h($program['id_program']) ?>"  <?= ($program['id_program'] === ($data['selected_program']['id_program'] ?? '')) ? 'selected' : '' ?> >
                      <?= h($program['program']) ?>
                  </option>
                  <?php endforeach; ?>
              </select>              
            </div>
          </div>          

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', $lang['BTN-BATAL'] ?? 'Batal')) ?>
          </button>
          <button type="button" id="btnSalinPeoSubmit" class="btn btn-sm btn-primary">
            <i class="ri-file-copy-line me-1"></i>
            <?= h(tr('BTN-SALIN', $lang['BTN-SALIN'] ?? 'Salin')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>