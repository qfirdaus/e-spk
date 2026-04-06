              <div class="card-text">
                <ul style="list-style: none; padding: 0;">
                  <li><input class="form-check-input" type="checkbox" value="" id="consentCheck" required><span style="margin-left: 10px;">Saya mengaku bahawasannya saya tidak pernah dikenakan tindakan tatatertib sepanjang pengajian saya di UPNM.</span></li>
                  <li><input class="form-check-input" type="checkbox" value="" id="consentCheck2" required><span style="margin-left: 10px;">Adalah dengan ini saya mengaku bahawa maklumat yang diberikan di atas adalah benar. </span><br>
                    <span style="margin-left: 22px;">Pihak Universiti berhak menolak permohonan ini dan menarik balik anugerah yang diberikan sekiranya maklumat yang diberikan didapati tidak benar.</span></li>
                </ul>
              <br> 
              <p class="mb-0 fw-semibold" style="margin-left: 10px;">Nama: <?= h($_SESSION['user.name'] ?? '') ?></p>
              <p class="mb-0 fw-semibold" style="margin-left: 10px;">No. Kad Pengenalan: <?= h($_SESSION['user.ic'] ?? '') ?></p>
              <p class="mb-0 fw-semibold" style="margin-left: 10px;">Tarikh: <?= date('d-m-Y') ?></p>
              <!-- Hantar Permohonan -->
              <div class="d-flex justify-content-start mt-3" style="padding: 0 2.5rem;">
                <button type="submit" class="btn btn-primary px-4" id="btn-submit">
                  <i class="ri-save-3-line me-2"></i> <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>
                </button>
              </div>  