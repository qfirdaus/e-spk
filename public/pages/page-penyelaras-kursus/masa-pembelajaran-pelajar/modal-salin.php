<div class="modal fade" id="salin" tabindex="-1" aria-labelledby="salinLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="ri-file-copy-line me-1"></i> Salin Maklumat SLT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formSalinSLT" action="" method="POST" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" name="txtterm">
                    <input type="hidden" name="txtcourseid">
                    
                    <div class="alert alert-info border-0">
                        Sila pilih <strong>Sesi</strong> dan <strong>Kursus</strong> sumber untuk disalin ke dalam kursus semasa.
                    </div>

                    <!-- Sesi -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sesi</label>
                        <select class="form-select" name="selectSesiModal" required>
                            <option value="" disabled selected>- Sila Pilih Sesi -</option>
                            <?php if (!empty($data['termList'])): ?>
                                <?php foreach ($data['termList'] as $sesi): ?>
                                    <option value="<?= h($sesi["f005term"]) ?>">
                                        <?= h($sesi["f005term"]) ?> - <?= h($sesi["semester"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Kursus  -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kursus</label>
                        <select class="form-select" name="selectKursusModal" required>
                            <option value="" disabled selected>- Sila Pilih Kursus -</option>
                            <?php if (!empty($data['courseList'])): ?>
                                <?php foreach ($data['courseList'] as $kursus): ?>
                                    <option value="<?= h($kursus["id_kursus"]) ?>">
                                        <?= h($kursus["kod_kursus"]) ?> - <?= h($kursus["subjekbm"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-file-copy-line me-1"></i>
                        <?= h(tr('BTN-SALIN', $lang['BTN-SALIN'] ?? 'Salin')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>