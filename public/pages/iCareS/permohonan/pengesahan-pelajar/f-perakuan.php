<div class="card border shadow-sm">

    <div class="card-body">

        <form method="POST" action="submit.php" id="formPerakuan">
            <!-- PERAKUAN SECTION -->
            <div class="border rounded p-3 mb-2 bg-white">

                <div class="fw-semibold mb-3">
                    Perakuan Pemohon
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input chk" id="chk1">
                    <label class="form-check-label" for="chk1">
                        Saya mengaku bahawasannya saya tidak pernah dikenakan tindakan tatatertib sepanjang pengajian saya di 
                        <strong>Universiti Pertahanan Nasional Malaysia (UPNM)</strong>.
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input chk" id="chk2">
                    <label class="form-check-label" for="chk2">
                        Adalah dengan ini saya mengaku bahawa maklumat yang diberikan di atas adalah benar. Pihak Universiti berhak menolak permohonan ini dan menarik balik anugerah yang diberikan sekiranya maklumat yang diberikan didapati tidak benar.
                    </label>
                </div>
            </div>

            <!-- INFO SECTION -->
            <div class="border rounded p-3 mb-3 bg-light">
                <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">Nama</div>
                    <div class="col-md-8">: <?= h($namaPenuh ?? '-') ?></div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4 fw-semibold">No. Kad Pengenalan</div>
                    <div class="col-md-8">: <?= h($nokp ?? '-') ?></div>
                </div>

                <div class="row">
                    <div class="col-md-4 fw-semibold">Tarikh</div>
                    <div class="col-md-8">: <?= date('d-m-Y') ?></div>
                </div>
            </div>

            <!-- HIDDEN -->
            <input type="hidden" name="nama" value="<?= h($namaPenuh ?? '') ?>">
            <input type="hidden" name="no_ic" value="<?= h($nokp ?? '') ?>">
            <input type="hidden" name="tarikh" value="<?= date('d-m-Y') ?>">

            <!-- BUTTON -->
            <div class="d-flex justify-content-end">

                <button type="submit"
                        class="btn btn-primary px-4"
                        id="btn-submit-application"
                        disabled>

                    <i class="ri-send-plane-line me-2"></i>

                    <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>

                </button>

            </div>

        </form>

    </div>

</div>              