<div class="modal fade modal-gradient" id="kemaskini" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line me-1"></i>
          <?= h(tr('TTL-KEMASKINI-LOC', 'Kemaskini Kluster Hasil Pembelajaran Baharu')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" method="POST">
        <div class="modal-body">

        <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-LOC', 'Kluster Hasil Pembelajaran')) ?>
            </label>
            <div class="col-sm-9">
              <input name="txtidloc_edit" id="txtidloc_edit" type="hidden" class="form-control  form-control-sm" autocomplete="off">  
              <input name="txtloc_edit" id="txtloc_edit" type="text" class="form-control  form-control-sm" autocomplete="off">   
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', 'Batal')) ?>
          </button>
          <button type="button" id="btnKemaskiniLOC" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('BTN-SIMPAN', 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>