<div class="modal fade modal-gradient" id="tambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line me-1"></i>
          <?= h(tr('TTL-TAMBAH-KURSUS', 'Tambah Kursus Baharu')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form autocomplete="off" id="formTambahKursus">
        <div class="modal-body">

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-SESI-KEMASUKAN', 'Sesi Kemasukan')) ?>
            </label>
            <div class="col-sm-3 mb-2 mb-sm-0">
              <input name="txtsesiid" id="txtsesiid" type="text" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-sm-6">
              <input name="txtsesi" id="txtsesi" type="text" class="form-control form-control-sm" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-PROGRAM', 'Program')) ?>
            </label>
            <div class="col-sm-9 mb-2 mb-sm-0">
              <input name="txtprogramid" id="txtprogramid" type="hidden" readonly>
              <input name="txtprogram" id="txtprogram" type="text" class="form-control form-control-sm" readonly>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KURSUS', 'Kursus')) ?>
            </label>
            <div class="col-sm-9">
              <select class="form-select form-select-sm select2" name="selectkursus" id="selectkursus" required>
                <option value="" disabled selected>- <?= h(tr('SELECT-PILIH', 'Sila Pilih')) ?> -</option>
                <?php foreach ($data['list_offer_kursus'] as $offer): ?>
                  <option value="<?= h($offer['kodk']) ?>">
                    <?= h($offer['kodk']) ?> - <?= h($offer['subjekbm']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-3 row align-items-center">
            <label class="col-sm-3 col-form-label fw-semibold">
              <?= h(tr('LBL-KATEGORI-KURSUS', 'Kategori Kursus')) ?>
            </label>
            <div class="col-sm-9">
              <select class="form-select form-select-sm select2" name="selectKategoriKursus" id="selectKategoriKursus" required>
                  <option value="" disabled selected>- <?= h(tr('SELECT-PILIH', 'Sila Pilih')) ?> -</option>
                  <option value="Teras">Teras</option>
                  <option value="Elektif">Elektif</option>
              </select>                   
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('BTN-BATAL', 'Batal')) ?>
          </button>
          <button type="button" id="btnHantarKursus" class="btn btn-sm btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('BTN-SIMPAN', 'Simpan')) ?>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>