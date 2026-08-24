<?php
require_once __DIR__ . '/../../../controllers/MaklumatKursusKPController.php';

$controller = new MaklumatKursusKPController();
$data       = $controller->getHalamanData();

$selectedTerm    = $data['selected_term'] ?? [];
$selectedProgram = $data['selected_program'] ?? [];

$sesiID      = $selectedTerm['f005term'] ?? '';
$semester    = $selectedTerm['semester'] ?? '';
$programID   = $selectedProgram['id_program'] ?? '';
$programNama = $selectedProgram['program'] ?? '';
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
                            <option value="" <?= (($_SESSION["pengajiankursus"] ?? '') === '') ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <option value="Asasi" <?= (($_SESSION["pengajiankursus"] ?? '') === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                            <option value="Diploma" <?= (($_SESSION["pengajiankursus"] ?? '') === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                            <option value="Sarjana Muda" <?= (($_SESSION["pengajiankursus"] ?? '') === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
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
                            <option value="" <?= empty($_SESSION["sesikursus"]) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <?php foreach ($data['list_sesi'] as $sesi): ?>
                                <option value="<?= h($sesi['f005term']) ?>" <?= ($sesi['f005term'] === ($data['selected_term']['f005term'] ?? '')) ? 'selected' : '' ?>>
                                    <?= h($sesi['f005term']) ?> - <?= h($sesi['semester']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>                      
                    </div>         
                </div>      

                <!-- Program -->
                <div class="mb-3 row align-items-center">
                    <label for="selectProgram" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('program', 'Program')) ?>
                    </label>
                    <div class="col-sm-8"> 
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectProgram" id="selectProgram">
                            <option value="" <?= empty($_SESSION["programkursus"]) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <?php foreach ($data['list_program'] as $program): ?>
                                <option value="<?= h($program['id_program']) ?>" <?= ($program['id_program'] === ($data['selected_program']['id_program'] ?? '')) ? 'selected' : '' ?>>
                                    <?= h($program['program']) ?>
                                </option>
                            <?php endforeach; ?>
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
        
        <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                data-bs-toggle="modal" data-bs-target="#tambah" 
                data-sesiid="<?= h($sesiID) ?>"
                data-sesi="<?= h(trim($semester)) ?>"
                data-programid="<?= h($programID) ?>"
                data-program="<?= h($programNama) ?>"
                title="<?= h(tr('tambah_kursus', 'Tambah Kursus')) ?>">
            <i class="ri-add-line me-1"></i> <?= h(tr('tambah', 'Tambah')) ?>
        </button>
    </div>

    <!-- Table Section -->
    <div class="table-responsive dt-standard">
        <table id="dataKursusDT" class="table table-bordered align-middle w-100 table-hover">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">Status</th>
                    <th style="width: 15%;"><?= h(tr('kod_kursus', 'Kod Kursus')) ?></th>
                    <th style="width: 30%;"><?= h(tr('nama_kursus', 'Nama Kursus')) ?></th>
                    <th style="width: 20%;"><?= h(tr('kategori_kursus', 'Kategori Kursus')) ?></th>
                    <th style="width: 30%;"><?= h(tr('penyelaras', 'Penyelaras Kursus')) ?></th>
                </tr>
            </thead>
            <tbody>     
                <?php 
                $list_kursus = $data['list_kursus'] ?? [];

                if (empty($list_kursus)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'Tiada rekod dijumpai')) ?>
                        </td>
                    </tr>
                <?php else: 
                    foreach ($list_kursus as $row):  
                        $isComplete = (!empty($row["kategori_kursus"]) && !empty($row["penyelaras_kursus"]));
                ?>
                    <tr>
                        <td class="text-center">
                            <?php if ($isComplete): ?>
                                <i class="ri-checkbox-circle-fill text-success fs-4" title="Lengkap"></i>
                            <?php else: ?>
                                <i class="ri-close-circle-fill text-danger fs-4" title="Belum Lengkap"></i>
                            <?php endif; ?>
                        </td>        
                        <td class="fw-semibold"><?= h($row['kod_kursus']) ?></td>
                        <td><?= h($row['subjekbm']) ?></td>
                        
                        <!-- Dropdown Kategori -->
                        <td>
                            <select class="form-select form-select-sm select-kategori select2" data-idkursus="<?= h($row['id_kursus']) ?>">
                                <option value="" <?= empty($row["kategori_kursus"]) ? 'selected' : '' ?> disabled>- Sila Pilih -</option>
                                <option value="Teras" <?= ($row["kategori_kursus"] === 'Teras') ? 'selected' : '' ?>>Teras</option>
                                <option value="Elektif" <?= ($row["kategori_kursus"] === 'Elektif') ? 'selected' : '' ?>>Elektif</option>
                            </select>
                        </td>

                        <!-- Dropdown Penyelaras -->
                        <td> 
                            <div class="d-flex align-items-center gap-1">
                                <select class="form-select form-select-sm select2 select-penyelaras" data-idkursus="<?= h($row['id_kursus']) ?>">
                                    <?php 
                                    $pensyarahList = $controller->getDynamicPensyarahList($row['kod_kursus']);
                                    
                                    if (empty($pensyarahList)): 
                                    ?>
                                        <option value="" selected disabled>- Tiada Rekod Pensyarah -</option>
                                    
                                    <?php else: ?>

                                        <option value="" disabled <?= empty($row["penyelaras_kursus"]) ? 'selected' : '' ?> >- Sila Pilih -</option>
                                        <?php foreach ($pensyarahList as $p): ?>
                                            <option value="<?= h($p['nopekerja']) ?>" <?= ($row['penyelaras_kursus'] === $p['nopekerja']) ? 'selected' : '' ?>>
                                                <?= h($p['gelar_nama']) ?> - <?= h($p['nopekerja']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                                <?php if (!empty($row["penyelaras_kursus"])): ?>
                                    <button class="btn btn-sm btn-outline-danger btn-reset-penyelaras flex-shrink-0" type="button" data-idkursus="<?= h($row['id_kursus']) ?>" title="Reset">
                                        <i class="ri-repeat-line"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>                                             
                    </tr>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </tbody>
        </table>
    </div>      
</div>