<form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
  <input type="hidden" name="icares_form" value="istar_konvo_penglibatan_program">
  <h5 class="text-h5"><?= h(tr('profile_penglibatan_program','Tambah Penglibatan Program')) ?></h5>
  <hr>
  <div class="row">
    <div class="col-12">
      <div class="row">
        <div class="col-md-6 gx-4">
          <!-- Nama Program / Nama Pertandingan -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label"><?= h(tr('nama_program_pertandingan','Nama Program / Nama Pertandingan')) ?></label>
            <div class="col-sm-8">
              <input type="text" name="nama_penuh" class="form-control" value=" " >
            </div>                 
          </div>

          <!-- Tarikh -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tarikh','Tarikh')) ?></label>
            <div class="col-sm-8">
              <!-- <input type="date" name="tarikh" class="form-control" value="<?= h($tarikh ?? '') ?>"  > -->
            </div>
          </div>  

          <!-- Wakil -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('wakil','Wakil')) ?></label>
            <div class="col-sm-8">
                <select name="jantina" class="form-select">
                <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>><?= h(tr('istar_option_individual_residential_college','Individu / Kolej Kediaman')) ?></option>
                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_faculty','Fakulti')) ?></option> 
                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_university','Universiti')) ?></option>
                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_state','Negeri')) ?></option>
                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('profile_negara','Negara')) ?></option>
                </select>
            </div>
          </div> 

        </div>

        <div class="col-md-6 gx-4">  
          <!-- Peringkat -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('peringkat','Peringkat')) ?></label>
            <div class="col-sm-8">
                <select name="peringkat" class="form-select">
                <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                <option value="Lelaki" <?= $peringkat=='Lelaki'?'selected':'' ?>><?= h(tr('istar_option_residential_college','Kolej Kediaman')) ?></option>
                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_faculty','Fakulti')) ?></option> 
                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_university','Universiti')) ?></option>
                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_state','Negeri')) ?></option>
                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_national','Kebangsaan')) ?></option>
                </select>
            </div>                 
          </div>                                                         

          <!-- Pencapaian -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('pencapaian','Pencapaian')) ?></label>
            <div class="col-sm-8">
                <select name="pencapaian" class="form-select">
                <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                <option value="Lelaki" <?= $pencapaian=='Lelaki'?'selected':'' ?>><?= h(tr('istar_result_gold','Johan / Emas')) ?></option>
                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_result_silver','Naib Johan / Perak')) ?></option> 
                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_result_bronze','Tempat Ketiga / Gangsa')) ?></option>
                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_result_participant','Peserta')) ?></option>
                </select>
            </div>
          </div>                            
          
          <!-- Dokumen Sokongan -->
          <div class="mb-2 row align-items-center">
            <div class="col-sm-4">
                <label class="col-form-label text-nowrap"> <?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?> </label> 
                <i class="ri-information-line ms-1 text-danger extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                aria-label="<?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?>" data-bs-original-title="<?= h(tr('profile_dokumen_sokongan_note','Sila sertakan Dokumen Sokongan dalam format JPG/JPEG/PDF, maks 5MB')) ?>"></i>
            </div>
            <div class="col-sm-8">
                <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
            </div>
          </div>                        
        </div>   

        <!-- Submit Button -->
        <div class="col-12 text-end mt-3">
          <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form> 
<div class="col-12 text-end mt-3">
  <button type="button" id="penglibatanBtnAdd" class="btn btn-primary sync-groups-btn">
    <i class="ri-add-line"></i><span><?= h(tr('button_add_new', 'Tambah Baru')) ?></span>
  </button>                
</div>
<h5 class="text-h5"><?= h(tr('profile_senarai_penglibatan_program','Senarai Penglibatan Program')) ?></h5>
<hr>
<div class="table-responsive dt-standard">
  <table id="penglibatanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil"><?= h(tr('template_senarai_crud_col_no', 'No.')) ?></th>
        <th class="small w-25">Nama Program / Pertandingan</th>
        <th class="small">Tarikh</th>
        <th class="small">Wakil</th>
        <th class="small">Peringkat</th>
        <th class="small">Pencapaian</th>
        <th class="small text-center">Tindakan</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($penglibatanData as $row): ?>
        <?php $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>

        <tr>
          <td class="col-bil"></td>
          <td><?= h($row['nama_program'] ?? '-') ?></td>
          <td>
            <?= !empty($row['tarikh_mula'])
              ? h(date('d/m/Y', strtotime($row['tarikh_mula'])))
              : '-' ?>
          </td>
          <td><?= h($row['wakil'] ?? '-') ?></td>
          <td><?= h($row['peringkat'] ?? '-') ?></td>
          <td><?= h($row['pencapaian'] ?? '-') ?></td>

          <td class="text-center">
            <button class="btn btn-sm btn-outline-warning js-view-row" data-row='<?= $rowJson ?>'>
              <i class="ri-eye-line"></i>
            </button>

            <button class="btn btn-sm btn-outline-primary js-edit-row" data-row='<?= $rowJson ?>'>
              <i class="ri-pencil-line"></i>
            </button>

            <button class="btn btn-sm btn-outline-danger js-delete-row" data-row='<?= $rowJson ?>'>
              <i class="ri-delete-bin-line"></i>
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>     


                