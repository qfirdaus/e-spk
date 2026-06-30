<div class="konvo-tab-card card shadow-sm mb-4 perakuan-card">
    <div class="card-body">
        <div class="mb-4">
            <h5 class="section-title mb-1"><?= h(tr('tab_perakuan_pemohon', 'Perakuan Pemohon')) ?></h5>
            <p class="text-muted mb-0"><?= h(tr('istar_declaration_intro', 'Sahkan semua kenyataan di bawah sebelum menghantar permohonan anda.')) ?></p>
        </div>

        <form method="POST" id="formPerakuan">
            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="perakuan-section p-3">
                        <h6><?= h(tr('istar_declaration_confirmation', 'Pengesahan')) ?></h6>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input chk" id="chk1">
                            <label class="form-check-label" for="chk1">
                                <?= h(tr('istar_declaration_no_disciplinary_prefix', 'Saya dengan ini mengakui bahawa saya tidak pernah dikenakan sebarang tindakan tatatertib sepanjang tempoh pengajian saya di')) ?>
                                <strong><?= h(tr('istar_declaration_upnm', 'Universiti Pertahanan Nasional Malaysia (UPNM)')) ?></strong>.
                            </label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input chk" id="chk2">
                            <label class="form-check-label" for="chk2">
                                <?= h(tr('istar_declaration_information_true', 'Saya juga mengaku bahawa segala maklumat yang diberikan adalah benar dan tepat. Pihak Universiti berhak menolak permohonan ini sekiranya maklumat tidak benar.')) ?>
                            </label>
                        </div>

                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input chk" id="chk3">
                            <label class="form-check-label" for="chk3">
                                <?= h(tr('istar_declaration_decision_final', 'Saya bersetuju bahawa keputusan Jawatankuasa Penilaian adalah muktamad dan tidak boleh dipertikaikan.')) ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="info-summary p-3 h-100">
                        <div class="mb-3">
                            <h6 class="mb-1"><?= h(tr('istar_applicant_information', 'Maklumat Pemohon')) ?></h6>
                            <p class="text-muted small mb-0"><?= h(tr('istar_declaration_data_notice', 'Data ini akan dihantar bersama perakuan.')) ?></p>
                        </div>

                        <div class="row info-row">
                            <div class="col-md-4 info-label"><?= h(tr('profile_nama', 'Nama')) ?></div>
                            <div class="col-md-8 info-value">: <?= h($namaPenuh ?? '-') ?></div>
                        </div>

                        <div class="row info-row">
                            <div class="col-md-4 info-label"><?= h(tr('profile_no_kad_pengenalan', 'No. Kad Pengenalan')) ?></div>
                            <div class="col-md-8 info-value">: <?= h($nokp ?? '-') ?></div>
                        </div>

                        <div class="row info-row mb-0">
                            <div class="col-md-4 info-label"><?= h(tr('tarikh', 'Tarikh')) ?></div>
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
                        id="btn-submit-istar-konvo"
                        disabled>
                    <i class="ri-send-plane-line me-2"></i>
                    <?= h(tr('profile_btn_submit','Hantar Permohonan')) ?>
                </button>
            </div>
        </form>
    </div>
</div>
