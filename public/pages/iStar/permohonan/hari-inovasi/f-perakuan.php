<?php
  $istarPerakuanIdPrefix = preg_replace('/[^a-z0-9_-]+/i', '-', (string)($istarPerakuanIdPrefix ?? 'hari-inovasi-perakuan'));
  $istarPerakuanFormSuffix = str_replace('-', '_', $istarPerakuanIdPrefix);
  if (!str_ends_with($istarPerakuanFormSuffix, '_perakuan')) {
    $istarPerakuanFormSuffix .= '_perakuan';
  }
  $istarPerakuanFormKey = 'istar_' . $istarPerakuanFormSuffix;
?>
              <form method="post" action="<?= base_url('actions/profile-update.php') ?>">
                <input type="hidden" name="icares_form" value="<?= h($istarPerakuanFormKey) ?>">
              <div class="card-text">
                <ul style="list-style: none; padding: 0;">
                  <li><input class="form-check-input" type="checkbox" value="" id="consentCheck-<?= h($istarPerakuanIdPrefix) ?>" required><span style="margin-left: 10px;"><?= h(tr('icares_declaration_no_disciplinary','Saya mengaku bahawasannya saya tidak pernah dikenakan tindakan tatatertib sepanjang pengajian saya di UPNM.')) ?></span></li>
                  <li><input class="form-check-input" type="checkbox" value="" id="consentCheck2-<?= h($istarPerakuanIdPrefix) ?>" required><span style="margin-left: 10px;"><?= h(tr('icares_declaration_information_true','Adalah dengan ini saya mengaku bahawa maklumat yang diberikan di atas adalah benar.')) ?></span><br>
                    <span style="margin-left: 22px;"><?= h(tr('icares_declaration_false_info_notice','Pihak Universiti berhak menolak permohonan ini dan menarik balik anugerah yang diberikan sekiranya maklumat yang diberikan didapati tidak benar.')) ?></span></li>
                </ul>
              <br> 
              <p class="mb-0 fw-semibold" style="margin-left: 10px;"><?= h(tr('icares_declaration_name','Nama')) ?>: <?= h($_SESSION['user.name'] ?? '') ?></p>
              <p class="mb-0 fw-semibold" style="margin-left: 10px;"><?= h(tr('icares_declaration_ic','No. Kad Pengenalan')) ?>: <?= h($_SESSION['user.ic'] ?? '') ?></p>
              <p class="mb-0 fw-semibold" style="margin-left: 10px;"><?= h(tr('icares_declaration_date','Tarikh')) ?>: <?= date('d-m-Y') ?></p>
              <!-- Hantar Permohonan -->
              <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary px-4" id="btn-submit-<?= h($istarPerakuanIdPrefix) ?>">
                  <i class="ri-save-3-line me-2"></i> <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>
                </button>
              </div>  
              </form>
