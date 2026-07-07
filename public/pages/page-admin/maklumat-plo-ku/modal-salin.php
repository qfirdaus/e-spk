<div class="modal fade modal-gradient" id="salin" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-file-copy-line me-1"></i>
          <?= h(tr('TTL-SALIN-PLO', $lang['TTL-SALIN-PLO'] ?? 'Salin PLO')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" method="POST">
        <div class="modal-body">
          
          <input name="txtsesi" id="txtsesi" type="hidden" readonly>
          <input name="txtprogramid" id="txtprogramid" type="hidden" readonly>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', $lang['LBL-SESI-KEMASUKAN'] ?? 'Sesi Kemasukan')) ?>
            </label>
            <div class="col-sm-9">
              <select class="form-select form-select-sm select2" name="selectSesiModal" id="selectSesi" required>
                <option value="" disabled selected>- <?= h(tr('SELECT-PILIH', 'Sila Pilih')) ?> -</option>
                <?php while ($result = @sybase_fetch_array($sql_result_termList1)) { ?>
                  <option value="<?= h($result["f005term"]) ?>">
                    <?= h($result["f005term"]) ?> - <?= h($result["semester"]) ?>
                  </option>
                <?php } ?>
              </select>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', $lang['BTN-BATAL'] ?? 'Batal')) ?>
          </button>
          <button type="button" id="btnSalinPloSubmit" class="btn btn-sm btn-primary">
            <i class="ri-file-copy-line me-1"></i>
            <?= h(tr('BTN-SALIN', $lang['BTN-SALIN'] ?? 'Salin')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>