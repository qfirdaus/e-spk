<?php
  $istarPerakuanIdPrefix = preg_replace('/[^a-z0-9_-]+/i', '-', (string)($istarPerakuanIdPrefix ?? 'hari-inovasi-perakuan'));
  $istarPerakuanFormSuffix = str_replace('-', '_', $istarPerakuanIdPrefix);
  if (!str_ends_with($istarPerakuanFormSuffix, '_perakuan')) {
    $istarPerakuanFormSuffix .= '_perakuan';
  }
  $istarPerakuanFormKey = 'istar_' . $istarPerakuanFormSuffix;
  $chkPrefix = h($istarPerakuanIdPrefix);
?>
<div class="konvo-tab-card card shadow-sm mb-4 perakuan-card">
    <div class="card-body">
        <div class="mb-4">
            <h5 class="section-title mb-1">Perakuan Pemohon</h5>
            <p class="text-muted mb-0">Sahkan semua kenyataan di bawah sebelum menghantar permohonan anda.</p>
        </div>

        <form method="POST" action="submit.php" id="formPerakuan">
            <input type="hidden" name="icares_form" value="<?= h($istarPerakuanFormKey) ?>">

            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="perakuan-section p-3">
                        <h6>Pengesahan</h6>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input chk" id="<?= $chkPrefix ?>-chk1">
                            <label class="form-check-label" for="<?= $chkPrefix ?>-chk1">
                                Saya dengan ini mengakui bahawa saya tidak pernah dikenakan sebarang tindakan tatatertib sepanjang tempoh pengajian saya di
                                <strong>Universiti Pertahanan Nasional Malaysia (UPNM)</strong>.
                            </label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input chk" id="<?= $chkPrefix ?>-chk2">
                            <label class="form-check-label" for="<?= $chkPrefix ?>-chk2">
                                Saya juga mengaku bahawa segala maklumat yang diberikan adalah benar dan tepat. Pihak Universiti berhak menolak permohonan ini sekiranya maklumat tidak benar.
                            </label>
                        </div>

                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input chk" id="<?= $chkPrefix ?>-chk3">
                            <label class="form-check-label" for="<?= $chkPrefix ?>-chk3">
                                Saya bersetuju bahawa keputusan Jawatankuasa Penilaian adalah muktamad dan tidak boleh dipertikaikan.
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="info-summary p-3 h-100">
                        <div class="mb-3">
                            <h6 class="mb-1">Maklumat Pemohon</h6>
                            <p class="text-muted small mb-0">Data ini akan dihantar bersama perakuan.</p>
                        </div>

                        <div class="row info-row">
                            <div class="col-md-4 info-label">Nama</div>
                            <div class="col-md-8 info-value">: <?= h($namaPenuh ?? '-') ?></div>
                        </div>

                        <div class="row info-row">
                            <div class="col-md-4 info-label">No. Kad Pengenalan</div>
                            <div class="col-md-8 info-value">: <?= h($nokp ?? '-') ?></div>
                        </div>

                        <div class="row info-row mb-0">
                            <div class="col-md-4 info-label">Tarikh</div>
                            <div class="col-md-8 info-value">: <?= date('d-m-Y') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="nama" value="<?= h($namaPenuh ?? '') ?>">
            <input type="hidden" name="no_ic" value="<?= h($nokp ?? '') ?>">
            <input type="hidden" name="tarikh" value="<?= date('d-m-Y') ?>">

            <div class="d-flex justify-content-end mt-4">
                <button type="submit"
                        class="btn btn-primary rounded-3 px-4 btn-submit"
                        id="btn-submit-<?= h($istarPerakuanIdPrefix) ?>"
                        disabled>
                    <i class="ri-send-plane-line me-2"></i>
                    <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>
                </button>
            </div>
        </form>
    </div>
</div>
