<?php
require_once __DIR__ . '/../../../controllers/MaklumatKursusPKController.php';

$controller = new MaklumatKursusPKController();
$data       = $controller->getHalamanData();

if (!empty($controller->getErrorMessage())) {
    echo "<div class='alert alert-danger'>RALAT SISTEM: " . $controller->getErrorMessage() . "</div>";
}

$pengajianTerpilih = $_SESSION["pengajiankursus"] ?? '';
$sesiTerpilih      = $_SESSION["sesikursus"] ?? '';

$semester = '';
if (!empty($sesiTerpilih)) {
    foreach ($data['list_sesi'] as $row) {
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

    <form id="form-carian-kursus" method="POST" action="">
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
                            <?php if (!empty($data['list_sesi'])): ?>
                                <?php foreach ($data['list_sesi'] as $sesi): ?>
                                    <option value="<?= h($sesi["f005term"]) ?>" <?= ($sesiTerpilih === $sesi["f005term"]) ? 'selected' : '' ?>>
                                        <?= h($sesi["f005term"]) ?> - <?= h($sesi["semester"]) ?>
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

    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="icares-address-panel-header mb-3">
            <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('senarai_kursus', 'Senarai Kursus Program')) ?></h5>
        </div>
    </div>   

    <!-- Table Section -->
    <div class="w-100 mt-3">
        <table id="dataKursusDT" class="table table-sm table-bordered align-middle table-hover w-100" style="table-layout: auto; font-size: 13px;">
            <thead class="table-light text-center align-middle" style="font-size: 11px; letter-spacing: 0.3px;">
                <tr>
                    <th rowspan="2" width="4%">No</th>
                    <th rowspan="2" width="20%" class="text-start">Nama Kursus</th>
                    <th rowspan="2" width="7%">Sinopsis</th>
                    <th rowspan="2" width="7%">Kategori<br>Kursus</th>
                    <th rowspan="2" width="7%">CLO</th>
                    <th rowspan="2" width="10%">Kemahiran</th>          
                    <th colspan="2" width="20%">Penilaian</th>     
                    <th rowspan="2" width="15%">Info &<br>Rujukan</th>
                    <th rowspan="2" width="10%">Tindakan</th> 
                </tr>                                                                    
                <tr>     
                    <th>Continuous</th>           
                    <th>Final</th> 
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['list_kursus'])): $i = 1; ?>
                    <?php foreach ($data['list_kursus'] as $kursus): ?>
                        <?php 
                            $adaSinopsis = !empty(trim($kursus["sinopsis_bm"] ?? ''));
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td class="text-nowrap fw-bold">
                                <!-- Kod Kursus-->
                                <span class="badge bg-lightcream text-dark mt-1"><?= h(trim($kursus["kod_kursus"] ?? '')) ?></span><br>
                                
                                <!-- Nama Kursus -->
                                <strong class="text-dark"><?= h(ucwords(strtolower(trim($kursus["subjekbm"])))) ?></strong>
                            </td>
                            
                            <td class="text-center">
                                <span title="<?= h($kursus["sinopsis_bm"]) ?>" data-bs-toggle="tooltip">
                                    <?php if ($adaSinopsis): ?>
                                        <i class="ri-checkbox-circle-line text-success fs-5"></i>
                                    <?php else: ?>
                                        <i class="ri-close-circle-line text-danger fs-5"></i>
                                    <?php endif; ?>
                                </span>
                            </td>
                            
                            <td class="text-center"><strong class="text-dark"><?= h($kursus["kategori_kursus"]) ?></strong></td>
                            
                            <td><?= h($kursus["senarai_clo_string"] ?? '') ?></td>
                            <td><?= nl2br(h($kursus["senarai_kemahiran_string"] ?? '')) ?></td>

                            <!-- Continuous -->
                            <td>
                                <?php 
                                    $cont_str = trim($kursus["senarai_continuous_string"] ?? '');
                                    if (!empty($cont_str)) {
                                 
                                        $cont_items = explode("\n\n", $cont_str);
                                        foreach ($cont_items as $item) {
                                            if (empty(trim($item))) continue;
                                            
                                  
                                            $parts = explode(" (", $item, 2);
                                            $title = $parts[0];
                                            $details = isset($parts[1]) ? rtrim($parts[1], ")") : ''; 
                                            
                                            echo '<strong class="text-primary">' . h($title) . '</strong><br>';
                                            if ($details) {
                                                echo h($details) . '<br><br>';
                                            }
                                        }
                                    }
                                ?>
                            </td>
                            
                            <!-- Final -->
                            <td>
                                <?php 
                                    $final_str = trim($kursus["senarai_final_string"] ?? '');
                                    if (!empty($final_str)) {
                                        $final_items = explode("\n\n", $final_str);
                                        foreach ($final_items as $item) {
                                            if (empty(trim($item))) continue;
                                            
                                            $parts = explode(" (", $item, 2);
                                            $title = $parts[0];
                                            $details = isset($parts[1]) ? rtrim($parts[1], ")") : ''; 
                                            
                                            echo '<strong class="text-primary">' . h($title) . '</strong><br>';
                                            if ($details) {
                                                echo h($details) . '<br><br>';
                                            }
                                        }
                                    }
                                ?>
                            </td>
                            
                            <td>
                                <?php if (!empty(trim($kursus["special_requirement"] ?? ''))): ?>
                                    <strong class="text-primary">Keperluan Khas:</strong><br>
                                    <?= nl2br(h($kursus["special_requirement"])) ?><br><br>
                                <?php endif; ?>

                                <?php if (!empty(trim($kursus["other_information"] ?? ''))): ?>
                                    <strong class="text-primary">Makluman:</strong><br>
                                    <?= nl2br(h($kursus["other_information"])) ?><br><br>
                                <?php endif; ?>

                                <?php if (!empty(trim($kursus["senarai_rujukan_string"] ?? ''))): ?>
                                    <strong class="text-primary">Rujukan:</strong><br>
                                    <?= nl2br(h($kursus["senarai_rujukan_string"])) ?>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Tindakan -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-outline-success btnKemaskiniModal" type="button" data-bs-toggle="modal" data-bs-target="#kemaskini" 
                                            data-semester="<?= h($semester) ?>"
                                            data-kursusid="<?= h($kursus["id_kursus"]) ?>"                                     
                                            data-kursus="<?= h($kursus["subjekbm"]) ?>"
                                            data-term="<?= h($kursus["term_pengajian"]) ?>"
                                            data-sinopsis="<?= h($kursus["sinopsis_bm"]) ?>"
                                            data-bilsem="<?= h($kursus["sem_pengajian"]) ?>"
                                            data-biltahun="<?= h($kursus["tahun_pengajian"]) ?>"
                                            data-req="<?= h($kursus["special_requirement"]) ?>"
                                            data-other="<?= h($kursus["other_information"]) ?>"
                                            title="Kemaskini Maklumat">
                                        <i class="ri-edit-box-line fs-5"></i>
                                    </button>
                                    
                                    <a href="generate-excel.php?course=<?= h($kursus["id_kursus"]) ?>" class="btn btn-sm btn-outline-primary" title="Muat Turun Excel">
                                        <i class="ri-download-2-line fs-5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">                            
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'Tiada rekod dijumpai')) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>           
</div>
