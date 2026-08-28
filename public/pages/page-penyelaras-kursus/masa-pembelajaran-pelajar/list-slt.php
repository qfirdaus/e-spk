<?php
require_once __DIR__ . '/../../../controllers/MaklumatSLTController.php';

$controller = new MaklumatSLTController();
$data       = $controller->getHalamanData();

$pengajianTerpilih = $_SESSION['pengajiankursus'] ?? '';
$sesiTerpilih      = $_SESSION['sesikursus'] ?? '';
$kursusTerpilih    = $_SESSION['kodKursus'] ?? '';

$semester = '';
if (!empty($sesiTerpilih)) {
    foreach ($data['termList'] as $row) {
        if ((string)$row['f005term'] === (string)$sesiTerpilih) {
            $semester = $row['semester']; 
            break;
        }
    }
}
?>

<div class="konvo-tab-card p-4 mb-4 shadow-sm rounded-3">
    <!-- Carian Section -->
    <div class="icares-address-panel-header mb-3">
        <h5 class="text-h5 fw-bold text-primary"><?= h(tr('carian', 'Carian')) ?></h5>
    </div>

    <form id="form-carian-slt" method="POST" action="">
        <div class="row gx-4 gy-2">
            <div class="col-md-8 col-lg-6">
                <!-- Peringkat Pengajian -->
                <div class="mb-3 row align-items-center">
                    <label for="selectPengajian" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('peringkat_pengajian', 'Peringkat Pengajian')) ?>
                    </label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                            <option value="" <?= empty($pengajianTerpilih) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <option value="Asasi" <?= ($pengajianTerpilih === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                            <option value="Diploma" <?= ($pengajianTerpilih === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                            <option value="Sarjana Muda" <?= ($pengajianTerpilih === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                        </select>               
                    </div>         
                </div>   

                <!-- Sesi -->
                <div class="mb-3 row align-items-center">
                    <label for="selectSesi" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('sesi', 'Sesi')) ?>
                    </label>
                    <div class="col-sm-8"> 
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                            <option value="" <?= empty($sesiTerpilih) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <?php if (!empty($data['termList'])): ?>
                                <?php foreach ($data['termList'] as $sesi): ?>
                                    <option value="<?= h($sesi["f005term"]) ?>" <?= ($sesiTerpilih === $sesi["f005term"]) ? 'selected' : '' ?>>
                                        <?= h($sesi["f005term"]) ?> - <?= h($sesi["semester"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>                
                    </div>         
                </div>

                <!-- Kursus -->
                <div class="mb-3 row align-items-center">
                    <label for="selectKursus" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('kursus', 'Kursus')) ?>
                    </label>
                    <div class="col-sm-8"> 
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectKursus" id="selectKursus">
                            <option value="" <?= empty($kursusTerpilih) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <?php if (!empty($data['courseList'])): ?>
                                <?php foreach ($data['courseList'] as $kursus): ?>
                                    <option value="<?= h($kursus["id_kursus"]) ?>" <?= ($kursusTerpilih == $kursus["id_kursus"]) ? 'selected' : '' ?>>
                                        <?= h($kursus["kod_kursus"]) ?> - <?= h($kursus["subjekbm"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>                
                    </div>         
                </div>
            </div>
        </div>
    </form>

    <hr class="my-4 text-muted">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('senarai_slt', 'Senarai SLT')) ?></h5>
        
        <?php if (!empty($kursusTerpilih)): ?>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                        data-bs-toggle="modal" data-bs-target="#tambah" 
                        data-bs-container="body"
                        data-sesiid="<?= h($sesiTerpilih) ?>"
                        data-sesi="<?= h($semester) ?>"
                        data-kursusid="<?= h($kursusTerpilih) ?>"
                        data-kursus="<?= h($data['selectedCourse']['subjekbm'] ?? '') ?>"
                        title="<?= h(tr('tambah_slt', 'Tambah SLT')) ?>">
                    <i class="ri-add-line me-1"></i> <?= h(tr('tambah', 'Tambah')) ?>
                </button>   

                <button class="btn btn-sm btn-outline-info rounded-3" type="button" data-bs-toggle="modal" data-bs-target="#salin" 
                        data-term="<?= h($sesiTerpilih) ?>"
                        data-kursusid="<?= h($kursusTerpilih) ?>"
                        title="<?= h(tr('salin_slt', 'Salin SLT')) ?>"> 
                    <i class="ri-file-copy-line me-1"></i> <?= h(tr('salin', 'Salin')) ?>
                </button>
            </div>
        <?php endif; ?>
    </div>   

    <!-- Table Section -->
    <style>
        #dataSltDT thead th {
            font-size: 11px !important;
            letter-spacing: 0.3px !important;
            text-transform: uppercase;
            vertical-align: middle !important;
        }
        #dataSltDT tbody td { font-size: 13px; }
    </style>

    <div class="w-100 mt-3">
        <table id="dataSltDT" class="table table-sm table-bordered align-middle table-hover w-100" style="table-layout: auto;"> 
            <thead class="table-light text-center"> 
                <tr>
                    <th rowspan="2" width="5%">No</th>
                    <th rowspan="2">Course Content Outline (CCO)</th>
                    <th rowspan="2" width="7%">Kod<br>CLO</th>
                    <th colspan="4">Guided Learning (F2F)</th>          
                    <th rowspan="2" width="8%">NF2F<br>Guided</th>     
                    <th rowspan="2" width="8%">NF2F<br>Independent</th> 
                    <th rowspan="2" width="7%">Jum.<br>SLT</th> 
                    <th rowspan="2" width="8%">Tindakan</th>
                </tr>                                                                    
                <tr>     
                    <th width="7%">Lecture</th>           
                    <th width="7%">Tutorial</th> 
                    <th width="7%">Practical</th> 
                    <th width="7%">Others</th> 
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['sltList'])): $i =1; ?>
                    <?php foreach ($data['sltList'] as $slt):  ?>
                        <tr>
                            <td class="text-center fw-bold text-primary"><?=  $i++ ?></td>
                            <td><?= nl2br(h($slt["content_outline"] ?? '')) ?></td>
                            <td class="text-center fw-bold text-dark"><?= h($slt["kod_clo"] ?? '') ?></td>
                            <td class="text-center"><?= (float)($slt["f2f_lecture"] ?? 0) ?></td>
                            <td class="text-center"><?= (float)($slt["f2f_tutorial"] ?? 0) ?></td>
                            <td class="text-center"><?= (float)($slt["f2f_practical"] ?? 0) ?></td>
                            <td class="text-center"><?= (float)($slt["f2f_others"] ?? 0) ?></td>
                            <td class="text-center"><?= (float)($slt["nf2f_guided"] ?? 0) ?></td>
                            <td class="text-center"><?= (float)($slt["nf2f_independent"] ?? 0) ?></td>
                            <td class="text-center fw-bold text-primary"><?= (float)($slt["slt"] ?? 0) ?></td>
                            
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    
                                    <!-- Kemaskini -->
                                    <button class="btn btn-sm btn-icon btn-outline-success me-1 btnKemaskiniModal"  type="button" data-bs-toggle="modal" data-bs-target="#kemaskini" 
                                            data-idslt="<?= h($slt["id_slt"]) ?>"
                                            data-kursusid="<?= h($kursusTerpilih) ?>"
                                            data-sesi="<?= h($semester) ?>"
                                            data-kursus="<?= h($data['selectedCourse']['subjekbm'] ?? '') ?>"
                                            data-content="<?= h($slt["content_outline"]) ?>"
                                            data-idclo="<?= h($slt["id_clo"]) ?>"
                                            data-lecture="<?= h($slt["f2f_lecture"]) ?>"
                                            data-tutorial="<?= h($slt["f2f_tutorial"]) ?>"
                                            data-practical="<?= h($slt["f2f_practical"]) ?>"
                                            data-others="<?= h($slt["f2f_others"]) ?>"
                                            data-nf2f="<?= h($slt["nf2f_guided"]) ?>"
                                            data-independent="<?= h($slt["nf2f_independent"]) ?>"
                                            title="Kemaskini">
                                        <i class="ri-edit-box-line fs-5"></i>
                                    </button>
                                    
                                    <!-- Hapus (Hantar POST terus ke page controller) -->
                                    <form action="" method="POST" onsubmit="return confirm('Adakah anda pasti untuk menghapus maklumat ini?');" style="display:inline;">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="sltid" value="<?= h($slt["id_slt"]) ?>">
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                            <i class="ri-delete-bin-line fs-5"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">                            
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'Tiada rekod dijumpai')) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>              
</div>