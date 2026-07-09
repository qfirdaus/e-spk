<div class="modal fade modal-gradient" id="tambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line me-1"></i>
          <?= h(tr('TTL-TAMBAH-PENYELARAS-JABATAN', 'Tambah Ketua Jabatan')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" method="POST">
        <div class="modal-body">
                    
          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-NOSTAF','No Staf')) ?>
            </label>
            <div class="col-sm-9 mb-2 mb-sm-0">
              <input name="txtnostaf" id="txtnostaf" type="text" class="form-control form-control-sm" autocomplete="off" readonly="">
              <input name="txtnokp" id="txtnokp" type="hidden" class="form-control form-control-sm" autocomplete="off" readonly="">
              <input name="txtkodjabatan" id="txtkodjabatan" type="hidden" class="form-control form-control-sm" autocomplete="off" readonly="">              
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-NAMA', 'Nama')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtnama" id="txtnama" type="text" class="form-control form-control-sm" autocomplete="off" readonly="">    
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-JABATAN', 'Jabatan')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtjabatan" id="txtjabatan" type="text" class="form-control form-control-sm" autocomplete="off" readonly="">                               
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-NOTELEFON', 'No Telefon')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtnotel" id="txtnotel" type="text" class="form-control form-control-sm" autocomplete="off" readonly="">                               
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-EMEL', 'Emel')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtemel" id="txtemel" type="text" class="form-control form-control-sm" autocomplete="off" readonly="">                               
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', 'Batal')) ?>
          </button>
          <button type="button" id="btnTambahKetuaJabatan" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('BTN-SIMPAN', 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>