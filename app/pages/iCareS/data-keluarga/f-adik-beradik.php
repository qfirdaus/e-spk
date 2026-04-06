<form method="post" action="<?= base_url('profile/update') ?>">
  <div class="row">
    <!-- LEFT: Maklumat Adik Beradik -->
    <div class="col-md-6 gx-4">
        <h5 class="text-h5"><?= h(tr('maklumat_adik_beradik','Maklumat Adik Beradik')) ?></h5>
        <hr>

        <!-- Bilangan Adik-Beradik -->
        <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_adikberadik','Bilangan Adik-Beradik')) ?></label>
            <div class="col-sm-8">
                <input type="number" name="adik_beradik" class="form-control" value="<?= h($bilAnugerahDekan) ?>" oninput="if(this.value>20)this.value=20;if(this.value<1)this.value=1;">                
            </div>
        </div>                        

        <!-- Anak Ke Berapa dalam Keluarga -->
        <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_anak_ke','Anak Ke Berapa')) ?></label>
            <div class="col-sm-8">
                <input type="number" name="anak_ke" class="form-control" value="<?= h($anak_ke) ?>" oninput="if(this.value>20)this.value=20;if(this.value<1)this.value=1;">
            </div>
        </div>
    </div>
    
    <!-- RIGHT: -->
    <div class="col-md-6 gx-4">
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