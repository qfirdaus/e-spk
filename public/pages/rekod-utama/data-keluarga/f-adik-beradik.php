<form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
  <input type="hidden" name="icares_form" value="keluarga_adik_beradik">

  <div class="icares-address-layout">
    <div class="icares-address-nav" role="tablist" aria-label="<?= h(tr('tab_maklumat_adik_beradik', 'Maklumat Adik-Beradik')) ?>">
      <button class="icares-address-nav__item active" id="adik-beradik-info-tab" data-bs-toggle="pill" data-bs-target="#adik-beradik-info-panel" type="button" role="tab" aria-controls="adik-beradik-info-panel" aria-selected="true">
        <i class="ri-team-line"></i>
        <span><?= h(tr('maklumat_adik_beradik','Maklumat Adik Beradik')) ?></span>
      </button>
    </div>

    <div class="icares-address-content tab-content">
      <div class="tab-pane fade show active" id="adik-beradik-info-panel" role="tabpanel" aria-labelledby="adik-beradik-info-tab" tabindex="0">
        <div class="icares-address-panel-header">
          <h5><?= h(tr('maklumat_adik_beradik','Maklumat Adik Beradik')) ?></h5>
          <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
        </div>
  <div class="row">
    <!-- LEFT: Maklumat Adik Beradik -->
    <div class="col-12 gx-4">
        <h5 class="text-h5"><?= h(tr('maklumat_adik_beradik','Maklumat Adik Beradik')) ?></h5>
        <hr>

        <!-- Bilangan Adik-Beradik -->
        <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_adikberadik','Bilangan Adik-Beradik')) ?></label>
            <div class="col-sm-8">
                <input type="number" name="adik_beradik" class="form-control" value="<?= h($bilAnugerahDekan ?? '') ?>" oninput="if(this.value>20)this.value=20;if(this.value<1)this.value=1;">                
            </div>
        </div>                        

        <!-- Anak Ke Berapa dalam Keluarga -->
        <div class="mb-2 row align-items-center">
            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_anak_ke','Anak Ke Berapa')) ?></label>
            <div class="col-sm-8">
                <input type="number" name="anak_ke" class="form-control" value="<?= h($anak_ke ?? '') ?>" oninput="if(this.value>20)this.value=20;if(this.value<1)this.value=1;">
            </div>
        </div>
    </div>
    
    <!-- RIGHT: -->
    <div class="col-12 gx-4">
    </div>

  </div>
      </div>
    </div>
  </div>

  <!-- BUTTON -->
  <div class="row">
    <div class="col-12 text-end mt-4">
      <button type="submit" class="btn btn-primary rounded-3 px-4">
        <i class="ri-save-3-line me-2"></i>
        <?= h(tr('profile_save_button','Simpan')) ?>
      </button>
    </div>
  </div>

</form>
