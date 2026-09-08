<div class="modal fade" id="modalPerincian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="ri-information-line me-1"></i> Perincian Kursus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>       
            
            <div class="modal-body bg-light-subtle" id="paparanPerincian">
                <!-- Loading spinner -->
                <div class="text-center py-5" id="loadingPerincian">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted fw-medium">Sedang memuatkan data dari pelayan...</p>
                </div>
                
                <div id="kandunganPerincian" class="d-none">
                    
                    <!-- SEKSYEN 1: MAKLUMAT ASAS -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                            <h6 class="fw-bold text-primary mb-0">Maklumat Asas</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless fs-6 mb-0">
                                <tr><th width="20%" class="text-muted">Kod & Nama Kursus</th><td width="3%">:</td><td class="fw-bold text-dark"><span id="detailKod" class="text-primary"></span> - <span id="detailNama"></span></td></tr>
                                <tr><th class="text-muted">Sesi Penawaran</th><td>:</td><td><span id="detailSesi"></span></td></tr>
                                <tr><th class="text-muted">Nilai Kredit</th><td>:</td><td id="detailKredit"></td></tr>
                                <tr><th class="text-muted">Penyelaras</th><td>:</td><td id="detailPenyelaras"></td></tr>
                                <tr><th class="text-muted align-top">Sinopsis</th><td class="align-top">:</td><td id="detailSinopsis" class="text-wrap text-justify"></td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- SEKSYEN 2: KEPERLUAN & RUJUKAN -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                            <h6 class="fw-bold text-primary mb-0">Keperluan & Rujukan</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless fs-6 mb-0">
                                <tr><th width="20%" class="text-muted">Prasyarat</th><td width="3%">:</td><td id="detailPrasyarat"></td></tr>
                                <tr><th class="text-muted">Keperluan Khas</th><td>:</td><td id="detailKeperluan"></td></tr>
                                <tr><th class="text-muted">Maklumat Tambahan</th><td>:</td><td id="detailLainLain"></td></tr>
                                <tr>
                                    <th class="text-muted align-top">Rujukan Utama</th>
                                    <td class="align-top">:</td>
                                    <td>
                                        <ol id="detailRujukan" class="ps-3 mb-0"></ol>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- SEKSYEN 3: KEMAHIRAN & CLO -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                            <h6 class="fw-bold text-primary mb-0">Kemahiran Boleh Pindah (Transferable Skills)</h6>
                        </div>
                        <div class="card-body pb-1">
                            <ul id="detailKemahiran" class="ps-3 mb-2"></ul>
                        </div>
                        
                        <div class="card-header bg-white border-bottom-0 pt-2 pb-0">
                            <h6 class="fw-bold text-primary mb-0">Hasil Pembelajaran Kursus (CLO)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th width="10%">Kod CLO</th>
                                            <th width="40%">Keterangan</th>
                                            <th width="15%">PLO Berkaitan</th>
                                            <th width="15%">Kaedah Ajar</th>
                                            <th width="20%">Kaedah Penilaian</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailSenaraiCLO">
                                        <!-- CLO from JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>