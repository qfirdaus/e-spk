<form id="form-penerima">
  <div class="row">
    <div class="col-12">
      <div class="row">
        <div class="col-md-6 gx-4">

          <!-- Nama Penerima Surat Pengesahan -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label"><?= h(tr('profile_nama_penerima','Nama Penerima Surat Pengesahan')) ?></label>
            <div class="col-sm-8">
              <input type="text" name="nama_penerima" class="form-control uppercase" value="<?= h($data['nama_penerima'] ?? '') ?>" >
            </div>
          </div>

          <!-- Alamat Penerima Surat Pengesahan -->

          <!-- Alamat Baris 1 -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
            <div class="col-sm-8">
              <input type="text" name="alamat1" class="form-control uppercase" value="<?= h($data['alamat1'] ?? '') ?>" >
            </div>                 
          </div>

          <!-- Alamat Baris 2 -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat2','Alamat 2')) ?></label>
            <div class="col-sm-8">
              <input type="text" name="alamat2" class="form-control uppercase" value="<?= h($data['alamat2'] ?? '') ?>" >
            </div>
          </div>                        

          <!-- Poskod -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
            <div class="col-sm-8">
              <input type="text" name="poskod" class="form-control" value="<?= h($data['poskod'] ?? '') ?>" >
            </div>
          </div>

          <!-- Bandar -->
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
            <div class="col-sm-8">
              <input type="text" name="bandar" class="form-control uppercase" value="<?= h($data['bandar'] ?? '') ?>" >
            </div>
          </div>

          <!-- Negeri -->
          <?php 
            $lookupNegeri = $lookupAll['negeri'] ?? [];
          ?>
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
            <div class="col-sm-8">
              <select name="negeri" class="form-select form-select-sm">
                  <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                  <?php foreach ($lookupNegeri as $opt): ?>
                  <option value="<?= h($opt['state_code']) ?>"
                      <?= h($data['negeri'] ?? '') == $opt['state_code'] ? 'selected' : '' ?>>
                      <?= h(strtoupper($opt['state'])) ?>
                  </option>
                  <?php endforeach; ?>
              </select>   
            </div>
          </div>

          <!-- Negara -->
          <?php 
            $lookupNegara = $lookupAll['negara'] ?? [];
          ?>
          <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
            <div class="col-sm-8">
              <select name="negara" class="form-select form-select-sm select2">
                  <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                  <?php foreach ($lookupNegara as $opt): ?>
                  <option value="<?= h($opt['country_code']) ?>"
                      <?= h($data['negara'] ?? '') == $opt['country_code'] ? 'selected' : '' ?>>
                      <?= h(strtoupper($opt['country'])) ?>
                  </option>
                  <?php endforeach; ?>
              </select>   
            </div>
          </div>                                 

        </div>

        <div class="col-md-6 gx-4">
        </div>

        <!-- Submit Button -->
        <!-- <div class="col-12 text-end mt-3">
          <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
          </button>
        </div> -->

      </div>
    </div>
  </div>
</form>       