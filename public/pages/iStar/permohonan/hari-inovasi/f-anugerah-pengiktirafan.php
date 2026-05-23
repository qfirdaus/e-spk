<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('profile_senarai_anugerah_pengiktirafan','Senarai Anugerah dan Pengiktirafan')) ?></h5>
  </div>

  <div class="konvo-form-box">
    <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
      <input type="hidden" name="icares_form" value="istar_hari_inovasi_anugerah_pengiktirafan">

      <div class="row gx-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label"><?= h(tr('nama_anugerah_pengiktirafan','Nama Anugerah / Pengiktirafan')) ?></label>
            <input type="text" name="nama_penuh" class="form-control" value="">
          </div>

          <div class="mb-3">
            <label class="form-label"><?= h(tr('tahun_pengiktirafan','Tahun')) ?></label>
            <input type="text" name="tahun" class="form-control" value="">
          </div>

          <div class="mb-3">
            <label class="form-label"><?= h(tr('kurniaan_pemberian','Kurniaan / Pemberian')) ?></label>
            <input type="text" name="kurniaan_pemberian" class="form-control" value="">
          </div>
        </div>

        <div class="col-md-6">
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

          <div class="mb-3">
            <label class="form-label">
              <?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?>
              <i class="ri-information-line ms-1 text-danger extra-roles-info"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 aria-label="<?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?>"
                 data-bs-original-title="<?= h(tr('profile_dokumen_sokongan_note','Sila sertakan Dokumen Sokongan dalam format JPG/JPEG/PDF, maks 5MB')) ?>"></i>
            </label>
            <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)">
            <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
          </div>
        </div>

        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary rounded-3 px-4">
            <i class="ri-save-3-line me-2"></i><?= h(tr('profile_save_button','Simpan')) ?>
          </button>
        </div>
      </div>
    </form>
  </div>

  <div class="table-responsive dt-standard">
    <table id="hariInovasiAnugerahTable" class="table table-bordered align-middle w-100 hari-inovasi-table">
      <thead>
        <tr>
          <th class="col-bil text-center"><?= h(tr('bil_no','No.')) ?></th>
          <th class="small w-35"><?= h(tr('nama_anugerah_pengiktirafan','Nama Anugerah / Pengiktirafan')) ?></th>
          <th class="small w-12 text-center"><?= h(tr('tahun_pengiktirafan','Tahun')) ?></th>
          <th class="small w-20 text-center"><?= h(tr('kurniaan_pemberian','Kurniaan / Pemberian')) ?></th>
          <th class="small w-15 text-center"><?= h(tr('peringkat','Peringkat')) ?></th>
          <th class="small text-center" style="width:110px;"><?= h(tr('istar_col_document','Dokumen')) ?></th>
          <th class="small text-center" style="width:130px;"><?= h(tr('istar_col_action','Tindakan')) ?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td></td>
          <td class="text-start js-tooltip-cell">PERSEMBAHAN TAEKWONDO SEMPENA SAMBUTAN HARI WANITA SEDUNIA PERINGKAT UPNM</td>
          <td class="text-center">2025</td>
          <td class="text-center">UPNM</td>
          <td class="text-center"><?= h(tr('istar_option_university','Universiti')) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-info rounded-3" type="button"><i class="ri-file-pdf-line"></i></button>
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center gap-2">
              <button class="btn btn-sm btn-outline-warning rounded-3" type="button"><i class="ri-eye-line"></i></button>
              <button class="btn btn-sm btn-outline-primary rounded-3" type="button"><i class="ri-pencil-line"></i></button>
            </div>
          </td>
        </tr>
        <tr>
          <td></td>
          <td class="text-start js-tooltip-cell">PENGANJURAN HAWK INTENSE CHALLENGE 2025</td>
          <td class="text-center">2025</td>
          <td class="text-center">UPNM</td>
          <td class="text-center"><?= h(tr('istar_option_university','Universiti')) ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-info rounded-3" type="button"><i class="ri-file-pdf-line"></i></button>
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center gap-2">
              <button class="btn btn-sm btn-outline-warning rounded-3" type="button"><i class="ri-eye-line"></i></button>
              <button class="btn btn-sm btn-outline-primary rounded-3" type="button"><i class="ri-pencil-line"></i></button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
