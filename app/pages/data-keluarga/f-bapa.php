<form method="post" action="<?= base_url('profile/update') ?>">

  <!-- ================= ROW 1 ================= -->
  <div class="row">
    <!-- LEFT: Maklumat Peribadi -->
    <div class="col-md-6 gx-4">
      <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Maklumat Peribadi')) ?></h5>
      <hr>

      <!-- Nama bapa -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama_bapa','Nama Bapa')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="nama_penuh" class="form-control" value="<?= ucwords(strtolower(h($nama_bapa))) ?>" readonly>
        </div>                 
      </div>

      <!-- No Kad Pengenalan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_kad_pengenalan','No Kad Pengenalan')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="no_kad_pengenalan" class="form-control" value="<?= h($nokpbapa) ?>" readonly>
        </div>
      </div>

      <!-- No Passport -->
      <div class="mb-2 row">
        <label class="col-sm-4 col-form-label text-nowrap">No Passport</label>
        <div class="col-sm-8">
          <input type="text" name="no_passport" class="form-control" value=" " >
        </div>
      </div>         

      <!-- Telefon -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_telefon','No Telefon')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="no_telefon" class="form-control" value="<?= h($nohp_bapa) ?>" readonly>
        </div>
      </div>

      <!-- Emel -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_emel','Emel')) ?></label>
        <div class="col-sm-8">
          <input type="email" name="emel" class="form-control" value=" " >
        </div>
      </div>

      <!-- Status Kesihatan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_kesihatan','Status Kesihatan')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="status_kesihatan" class="form-control" value="" >
        </div>
      </div>

      <!-- Dokumen OKU -->
      <div class="mb-2 row align-items-center">
        <div class="col-sm-4">
          <label class="col-form-label text-nowrap"> <?= h(tr('profile_dokumen_oku','Dokumen OKU')) ?> </label> 
          <i class="ri-information-line ms-1 text-danger extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
             aria-label="<?= h(tr('profile_dokumen_oku_note','Dokumen OKU')) ?>" data-bs-original-title="<?= h(tr('profile_dokumen_oku_note','Sila sertakan Kad OKU / Dokumen OKU / No. Pendaftaran dalam format JPG/JPEG/PDF, maks 5MB')) ?>"></i>
        </div>
        <div class="col-sm-8">
          <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
          <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
        </div>
      </div>

      <!-- Bil. Tanggungan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bil_tanggungan','Bil. Tanggungan')) ?></label>
        <div class="col-sm-8">
          <input type="number" name="bil_tanggungan" class="form-control" value="<?= h($bil_tanggungan ?? '') ?>" >
        </div>                 
      </div>

      <!-- Tahap Pendidikan Tertinggi  -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tahap_pendidikan_tertinggi','Tahap Pendidikan Tertinggi')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="tahap_pendidikan" class="form-control" value="<?= h($tahap_pendidikan ?? '') ?>" >
        </div>
      </div>   
    </div>

    <!-- RIGHT: Alamat Tempat Tinggal -->
    <div class="col-md-6 gx-4">
      <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Alamat Tempat Tinggal')) ?></h5>
      <hr>

      <!-- Kategori Tempat Tinggal -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kategori_tempat_tinggal','Kategori Tempat Tinggal')) ?></label>
        <div class="col-sm-8">
          <select name="kategori_tempat_tinggal" class="form-select">
            <option value="">-- Sila Pilih --</option>
            <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Persendirian</option>
            <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Sewaan</option>
            <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Lain-lain (Sila Nyatakan)</option>
          </select>
        </div>
      </div>  

      <!-- Alamat Baris 1 -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1_permenant ?? '') ?>" >
        </div>                 
      </div>

      <!-- Alamat Baris 2 -->
      <div class="mb-2 row">
        <div class="offset-sm-4 col-sm-8">
          <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2_permenant ?? '') ?>" >
        </div>
      </div>                        

      <!-- Poskod -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="poskod" class="form-control" value="<?= h($poskod_permanent ?? '') ?>" >
        </div>
      </div>

      <!-- Bandar -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="bandar" class="form-control" value="<?= h($bandar_permanent ?? '') ?>" >
        </div>
      </div>

      <!-- Negeri -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="negeri" class="form-control" value="<?= h($negeri_permanent ?? '') ?>" >
        </div>
      </div>

      <!-- Negara -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="negara" class="form-control" value="<?= h($negara_permanent ?? 'Malaysia') ?>" >
        </div>
      </div>
    </div>
  </div>

  <!-- ================= ROW 2 ================= -->
  <div class="row mt-4">
    <!-- LEFT: Maklumat Pekerjaan -->
    <div class="col-md-6 gx-4">
      <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Maklumat Pekerjaan')) ?></h5>
      <hr>

      <!-- Status Pekerjaan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Status Pekerjaan')) ?></label>
        <div class="col-sm-8">
          <select name="status_pekerjaan" class="form-control">
              <option value="">-- Sila pilih --</option>
              <?php foreach ($employmentStatus as $status): ?>
                  <option value="<?= h($status['statusCode']) ?>">
                      <?= h($lang == 'en' ? $status['statusEN'] : $status['statusMY']) ?>
                  </option>
              <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Sektor Pekerjaan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sektor_pekerjaan','Sektor Pekerjaan')) ?></label>
        <div class="col-sm-8">
          <select name="sector_pekerjaan" class="form-control">
              <option value="">-- Sila pilih --</option>
              <?php foreach ($employmentSector as $sector): ?>
                  <option value="<?= h($sector['sectorCode']) ?>">
                      <?= h($lang == 'en' ? $sector['sectorEN'] : $sector['sectorMY']) ?>
                  </option>
              <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Perkhidmatan Beruniform -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_perkhidmatan_beruniform','Perkhidmatan Beruniform')) ?></label>
        <div class="col-sm-8">
          <select name="perkhidmatan_beruniform" class="form-control">
              <option value="">-- Sila pilih --</option>
              <option value="yes" <?= (isset($sector_pekerjaan) && $sector_pekerjaan == 'yes') ? 'selected' : '' ?>>
                  <?= ($lang == 'en' ? 'Yes' : 'Ya') ?>
              </option>
              <option value="no" <?= (isset($sector_pekerjaan) && $sector_pekerjaan == 'no') ? 'selected' : '' ?>>
                  <?= ($lang == 'en' ? 'No' : 'Tidak') ?>
              </option>
          </select>      
        </div>
      </div>

      <!-- Jenis Perkhidmatan Beruniform -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jenis_perkhidmatan_beruniform','Jenis Perkhidmatan Beruniform')) ?></label>
        <div class="col-sm-8">
          <select name="jenis_perkhidmatan_beruniform" class="form-control">
              <option value="">-- Sila pilih --</option>
              <?php foreach ($uniformService as $service): ?>
                  <option value="<?= h($service['serviceCode']) ?>">
                      <?= h($lang == 'en' ? $service['serviceEN'] : $service['serviceMY']) ?>
                  </option>
              <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Lain-lain Perkhidmatan Beruniform -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"> </label>
        <div class="col-sm-8">
          <input type="text" name="perkhidmatan_beruniform_lain" class="form-control" value="<?= h($perkhidmatan_beruniform_lain ?? '') ?>" readonly >
        </div>
      </div>

      <!-- Status Perkhidmatan Beruniform -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('status_perkhidmatan_beruniform','Status Perkhidmatan Beruniform')) ?></label>
        <div class="col-sm-8">
          <select name="status_perkhidmatan_beruniform" class="form-select">
            <option value="">-- Sila Pilih --</option>
            <option value="Dalam Perkhidmatan" <?= ($status_perkhidmatan_beruniform=='Dalam Perkhidmatan')?'selected':'' ?>>Dalam Perkhidmatan</option>
            <option value="Pesara" <?= ($status_perkhidmatan_beruniform=='Pesara')?'selected':'' ?>>Pesara</option>
          </select>
        </div>
      </div>

      <!-- Pendapatan Bulanan Kasar -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('jumlah_pendapatan_bulanan_kasar','Pendapatan Bulanan Kasar')) ?></label>
        <div class="col-sm-8">
          <select name="pendapatan_bulanan" class="form-control">
              <option value="">-- Sila pilih --</option>
              <?php foreach ($salaryrange as $range): ?>
                  <option value="<?= h($range['value']) ?>"
                      <?= (isset($pendapatan_bulanan) && $pendapatan_bulanan == $range['value']) ? 'selected' : '' ?>>
                      <?= h($range['label']) ?>
                  </option>
              <?php endforeach; ?>
          </select>       
        </div>
      </div>

      <!-- Perakuan Pendapatan -->
      <div class="mb-2 row align-items-center">
        <div class="col-sm-4">
          <label class="col-form-label text-nowrap"> <?= h(tr('profile_perakuan_penadapatan','Perakuan Pendapatan')) ?> </label> 
          <i class="ri-information-line ms-1 text-danger extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
             aria-label="<?= h(tr('profile_perakuan_penadapatan','Perakuan Pendapatan')) ?>" data-bs-original-title="<?= h(tr('profile_perakuan_penadapatan','Sila sertakan Penyata Pendapatan atau Surat Pengesahan Pendapatan dalam format JPG/JPEG/PDF, maks 5MB')) ?>"></i>
        </div>
        <div class="col-sm-8">
          <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
          <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
        </div>
      </div>      
    </div>

    <!-- RIGHT: Alamat Majikan -->
    <div class="col-md-6 gx-4">
      <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Alamat Majikan')) ?></h5>
      <hr>

      <!-- Nama Majikan -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_majikan','Nama Majikan')) ?></label>
        <div class="col-sm-8">
          <textarea type="text" name="majikan" class="form-control" value="<?= h($majikan ?? '') ?>" > </textarea>
        </div>
      </div>

      <!-- Alamat Baris 1 -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" >
        </div>                 
      </div>

      <!-- Alamat Baris 2 -->
      <div class="mb-2 row">
        <div class="offset-sm-4 col-sm-8">
          <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" >
        </div>
      </div>                        

      <!-- Poskod -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" >
        </div>
      </div>

      <!-- Bandar -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" >
        </div>
      </div>

      <!-- Negeri -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" >
        </div>
      </div>

      <!-- Negara -->
      <div class="mb-2 row align-items-center">
        <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
        <div class="col-sm-8">
          <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
        </div>
      </div>
    </div>
  </div>

  <!-- BUTTON -->
  <div class="row">
    <div class="col-12 text-center mt-4">
      <button type="submit" class="btn btn-primary px-4">
        <i class="ri-save-3-line me-2"></i>
        <?= h(tr('profile_save_button','Simpan')) ?>
      </button>
    </div>
  </div>

</form>