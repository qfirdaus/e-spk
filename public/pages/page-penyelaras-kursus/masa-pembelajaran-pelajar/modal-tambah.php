<div class="modal fade" id="tambah" tabindex="-1" aria-labelledby="tambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                <i class="ri-add-circle-line me-1"></i>
                <?= h(tr('TTL-TAMBAH-SLT', 'Tambah SLT Baharu')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formTambahSLT" action="" method="POST" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" name="txtsesiid" id="add_txtsesiid">
                    <input type="hidden" name="txtkursusid" id="add_txtkursusid">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-6">Sesi</label>
                            <input type="text" name="txtsesi" id="add_txtsesi" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold fs-6">Kursus</label>
                            <input type="text" name="txtkursus" id="add_txtkursus" class="form-control bg-light" readonly>
                        </div>
                    </div>

                    <!-- Keterangan CCO & CLO -->
                    <div class="mb-3">
                        <label class="form-label fw-bold fs-6 text-dark">Keterangan CCO</label>
                        <textarea name="txtCCO" id="add_txtCCO" class="form-control" rows="3" placeholder="Masukkan Keterangan Course Content Outline (CCO)..." required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold fs-6 text-dark">Kod CLO</label>
                        <select class="form-select" name="selectCLO" id="add_selectCLO" required>
                            <option value="" disabled selected>- Sila Pilih CLO -</option>
                            <?php if (!empty($data['cloList'])): ?>
                                <?php foreach ($data['cloList'] as $clo): ?>
                                    <option value="<?= h($clo['id_clo']) ?>"><?= h($clo['kod_clo']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold text-primary mb-3">Teaching and Learning Activities</h5>
                    
                    <!-- F2F (Face-to-Face) -->
                    <div class="row mb-3 bg-light p-2 rounded mx-0">
                        <div class="col-12 mb-2"><label class="form-label fs-6 text-dark fw-bold mb-0">Guided Learning (F2F)</label></div>
                        
                        <div class="col-md-3 mb-2">
                            <label class="form-label fs-6 text-dark">Lecture</label>
                            <input type="number" step="0.01" name="txtlecture" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label fs-6 text-dark">Tutorial</label>
                            <input type="number" step="0.01" name="txttutorial" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label fs-6 text-dark">Practical</label>
                            <input type="number" step="0.01" name="txtpractical" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label fs-6 text-dark">Others</label>
                            <input type="number" step="0.01" name="txtothers" class="form-control form-control-sm" placeholder="0">
                        </div>
                    </div>

                    <!-- NF2F (Non Face-to-Face) -->
                    <div class="row mb-1 p-2 mx-0">
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold fs-6 text-dark">Guided Learning (NF2F)</label>
                            <input type="number" step="0.01" name="txtnf2f" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold fs-6 text-dark">Independent Learning (NF2F)</label>
                            <input type="number" step="0.01" name="txtindependent" class="form-control form-control-sm" placeholder="0">
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" >
                        <i class="ri-save-3-line me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>