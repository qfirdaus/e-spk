<div class="modal fade" id="kemaskini" tabindex="-1" aria-labelledby="kemaskiniLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        
        <form id="formKemaskiniKursus" method="POST" class="modal-content">
            
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="ri-edit-2-line me-1"></i>Kemaskini Maklumat Kursus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                
                <input type="hidden" name="txtkursusid" id="txtkursusid">
                <input type="hidden" name="txtterm" id="txtterm">
                <input type="hidden" name="txt_len" id="txt_len">
                <div id="div_idpenilaian"></div>
                <input type="hidden" name="count" id="count" value="0">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Sesi </label>
                        <input type="text" id="txtsesi" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Kursus</label>
                        <input type="text" id="txtkursus" class="form-control bg-light" readonly>
                    </div>
                </div>

                <!-- Sinopsis -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Sinopsis</label>
                        <textarea name="txtsinopsis" id="txtsinopsis" class="form-control" rows="3" placeholder="Masukkan sinopsis kursus..."></textarea>
                    </div>
                </div>

                <!-- Semester & Tahun -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Semester Pengajian</label>
                        <input type="number" name="txtsem" id="txtsem" class="form-control" min="1" max="3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tahun Pengajian</label>
                        <input type="number" name="txttahun" id="txttahun" class="form-control" min="1" max="6" required>
                    </div>
                </div>

                <hr class="my-4 text-muted" style="border-top: 1px dashed;">

                <!-- Kemahiran (Checkboxes) -->
                <div class="mb-4">
                    <label class="form-label fw-bold border-bottom pb-2 d-block text-primary">Senarai Kemahiran (Transferable Skills)</label>
                    <div class="row px-2 mt-2" id="div_kemahiran">
                        <span class="text-muted small"><i class="ri-loader-4-line ri-spin"></i> Memuatkan senarai kemahiran...</span>
                    </div>
                </div>

                <!-- Penilaian -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold border-bottom pb-2 d-block text-primary">Penilaian Berterusan (Continuous)</label>
                        <div id="div_continuous" class="gy-2 mt-2"></div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold border-bottom pb-2 d-block text-primary">Penilaian Akhir (Final)</label>
                        <div id="div_final" class="gy-2 mt-2"></div>
                    </div>
                </div>

                <!-- Tabs Maklumat Tambahan -->
                <ul class="nav nav-tabs" id="tabMaklumatTambahan" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-req" type="button" role="tab">Keperluan Khas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-other" type="button" role="tab">Lain-lain Maklumat</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-ref" type="button" role="tab">Rujukan (References)</button>
                    </li>
                </ul>
                
                <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white mb-3" id="tabContentMaklumatTambahan">
                    <!-- Tab 1: Keperluan Khas -->
                    <div class="tab-pane fade show active" id="tab-req" role="tabpanel">
                        <small>Identify special requirement to deliver the course (e.g:software, nursery, computer lab, simulation room etc)</small>
                        <br><br>
                        <textarea name="txtrequirement" id="txtrequirement" class="form-control" rows="3" placeholder="Contoh: Makmal Komputer, Perisian khusus..."></textarea>
                    </div>
                    
                    <!-- Tab 2: Lain-lain -->
                    <div class="tab-pane fade" id="tab-other" role="tabpanel">
                        <textarea name="txtotherinfo" id="txtotherinfo" class="form-control" rows="3" placeholder="Maklumat tambahan jika ada..."></textarea>
                    </div>
                    
                    <!-- Tab 3: Rujukan -->
                    <div class="tab-pane fade" id="tab-ref" role="tabpanel">
                        <small>References (include required and further readings, and should be the most current)</small>
                        <br><br>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small fw-semibold">Senarai Rujukan Semasa</span>
                            <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="tambahInputRujukan()">+ Tambah Rujukan</button>
                        </div>
                        <div id="dynamic_field_rujukan" class="mt-2">
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" id="btnSimpanKemaskiniKursus" class="btn btn-primary">Simpan</button>
            </div>
            
        </form> 
        
    </div>
</div>