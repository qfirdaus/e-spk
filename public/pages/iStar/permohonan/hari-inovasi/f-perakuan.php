<?php
  $istarPerakuanIdPrefix = preg_replace('/[^a-z0-9_-]+/i', '-', (string)($istarPerakuanIdPrefix ?? 'hari-inovasi-perakuan'));
  $istarPerakuanFormSuffix = str_replace('-', '_', $istarPerakuanIdPrefix);
  if (!str_ends_with($istarPerakuanFormSuffix, '_perakuan')) {
    $istarPerakuanFormSuffix .= '_perakuan';
  }
  $istarPerakuanFormKey = 'istar_' . $istarPerakuanFormSuffix;
?>
  <div class="box">
      <form method="POST" action="submit.php" id="formPerakuan">

          <!-- CHECKBOX -->
          <div class="checkbox-group">
              <input type="checkbox" class="chk">
              Saya dengan ini mengakui bahawa saya tidak pernah dikenakan sebarang tindakan tatatertib sepanjang tempoh pengajian saya di <strong>Universiti Pertahanan Nasional Malaysia (UPNM)</strong>.
          </div>

          <div class="checkbox-group">
              <input type="checkbox" class="chk">
              Saya juga mengaku bahawa segala maklumat yang diberikan adalah benar dan tepat. Pihak Universiti berhak menolak permohonan ini sekiranya maklumat tidak benar.
          </div>

          <div class="checkbox-group">
              <input type="checkbox" class="chk">
              Saya bersetuju bahawa keputusan Jawatankuasa Penilaian adalah muktamad dan tidak boleh dipertikaikan.
          </div>

          <!-- PAPARAN SAHAJA -->
          <div class="mt-3">
              <div class="row mb-2">
                  <div class="col-1 fw-semibold">Nama</div>
                  <div class="col-8 text-nowrap">: <?= h($namaPenuh ?? '-') ?></div>
              </div>

              <div class="row mb-2">
                  <div class="col-1 fw-semibold">Kad Pengenalan</div>
                  <div class="col-8 text-nowrap">: <?= h($nokp ?? '-') ?></div>
              </div>

              <div class="row">
                  <div class="col-1 fw-semibold">Tarikh</div>
                  <div class="col-8 text-nowrap">: <?= date('d-m-Y') ?></div>
              </div>

          </div>

          <input type="hidden" name="nama" value="<?= h($namaPenuh ?? '') ?>">
          <input type="hidden" name="no_ic" value="<?= h($nokp ?? '') ?>">
          <input type="hidden" name="tarikh" value="<?= date('d-m-Y') ?>">

          <div class="d-flex justify-content-end mt-3" title="Sila tandakan semua perakuan terlebih dahulu" style="display:inline-block;">
            <button type="submit" class="btn btn-primary px-4" id="btn-submit-<?= h($istarPerakuanIdPrefix) ?>" disabled >
              <i class="ri-save-3-line me-2"></i> <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>
            </button>
          </div>  
      </form>
  </div>
