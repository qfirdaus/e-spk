<div class="modal fade" id="penglibatanAddModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ri-add-circle-line"></i>
          <?= h(tr('profile_penglibatan_program','Tambah Penglibatan Program')) ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
        <input type="hidden" name="icares_form" value="istar_konvo_penglibatan_program">

        <div class="modal-body">
          <div class="row">

            <!-- LEFT COLUMN -->
            <div class="col-md-6">

              <!-- Nama Program -->
              <div class="mb-3">
                <label class="form-label">
                  <?= h(tr('nama_program_pertandingan','Nama Program / Nama Pertandingan')) ?>
                </label>
                <input type="text" name="nama_penuh" class="form-control">
              </div>

              <!-- Tarikh -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('profile_tarikh','Tarikh')) ?></label>
                <input type="date" name="tarikh" class="form-control">
              </div>

              <!-- Wakil -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('wakil','Wakil')) ?></label>
                <select name="wakil" class="form-select">
                  <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                  <option value="individu"><?= h(tr('istar_option_individual_residential_college','Individu / Kolej Kediaman')) ?></option>
                  <option value="badan_pelajar"><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                  <option value="fakulti"><?= h(tr('istar_option_faculty','Fakulti')) ?></option>
                  <option value="universiti"><?= h(tr('istar_option_university','Universiti')) ?></option>
                  <option value="negeri"><?= h(tr('istar_option_state','Negeri')) ?></option>
                  <option value="negara"><?= h(tr('profile_negara','Negara')) ?></option>
                </select>
              </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-md-6">

              <!-- Peringkat -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('peringkat','Peringkat')) ?></label>
                <select name="peringkat" class="form-select">
                  <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                  <option value="kolej"><?= h(tr('istar_option_residential_college','Kolej Kediaman')) ?></option>
                  <option value="badan_pelajar"><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                  <option value="fakulti"><?= h(tr('istar_option_faculty','Fakulti')) ?></option>
                  <option value="universiti"><?= h(tr('istar_option_university','Universiti')) ?></option>
                  <option value="negeri"><?= h(tr('istar_option_state','Negeri')) ?></option>
                  <option value="kebangsaan"><?= h(tr('istar_option_national','Kebangsaan')) ?></option>
                </select>
              </div>

              <!-- Pencapaian -->
              <div class="mb-3">
                <label class="form-label"><?= h(tr('pencapaian','Pencapaian')) ?></label>
                <select name="pencapaian" class="form-select">
                  <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                  <option value="emas"><?= h(tr('istar_result_gold','Johan / Emas')) ?></option>
                  <option value="perak"><?= h(tr('istar_result_silver','Naib Johan / Perak')) ?></option>
                  <option value="gangsa"><?= h(tr('istar_result_bronze','Tempat Ketiga / Gangsa')) ?></option>
                  <option value="peserta"><?= h(tr('istar_result_participant','Peserta')) ?></option>
                </select>
              </div>

              <!-- Dokumen -->
              <div class="mb-3">
                <label class="form-label">
                  <?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?>
                </label>
                <input type="file" name="dokumen" class="form-control"
                       accept=".jpg,.jpeg,.pdf"
                       onchange="checkFileSize(this)">
                <small class="text-danger">
                  <?= h(tr('profile_dokumen_sokongan_note','Max 5MB (JPG/JPEG/PDF)')) ?>
                </small>
              </div>

            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ri-save-3-line me-1"></i>
            <?= h(tr('profile_save_button','Simpan')) ?>
          </button>
        </div>

      </form>

    </div>
  </div>
</div>