<?php
require_once __DIR__ . '/../../../controllers/MaklumatPLOKPController.php';

$controller = new MaklumatPLOKPController();
$data       = $controller->getHalamanData();

if ($controller->getErrorMessage()): ?>
    <div class="alert alert-danger mb-3" role="alert">
        <strong>Ralat:</strong> <?= h($controller->getErrorMessage()) ?>
    </div>
<?php endif; ?>

<div class="konvo-tab-card p-4 mb-4 shadow-sm rounded-3">
    <!-- Carian Section -->
    <div class="icares-address-panel-header mb-3">
        <h5 class="text-h5 fw-bold text-primary"><?= h(tr('carian', 'Carian')) ?></h5>
    </div>

    <form id="form-maklumat-plo" method="POST" action="">
        <div class="row gx-4 gy-2">
            <div class="col-md-8 col-lg-6">

                <!-- Peringkat Pengajian -->
                <div class="mb-3 row align-items-center">
                    <label for="selectPengajianPLO" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('peringkat_pengajian', 'Peringkat Pengajian')) ?>
                    </label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajianPLO" id="selectPengajianPLO">
                            <option value="" <?= (($_SESSION["pengajianplo"] ?? '') === '') ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <option value="Asasi" <?= (($_SESSION["pengajianplo"] ?? '') === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                            <option value="Diploma" <?= (($_SESSION["pengajianplo"] ?? '') === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                            <option value="Sarjana Muda" <?= (($_SESSION["pengajianplo"] ?? '') === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                        </select>                    
                    </div>         
                </div>   

                <!-- Sesi Kemasukan -->
                <div class="mb-3 row align-items-center">
                    <label for="selectSesiPLO" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('sesi_kemasukan', 'Sesi Kemasukan')) ?>
                    </label>
                    <div class="col-sm-8"> 
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesiPLO" id="selectSesiPLO">
                            <option value="" <?= empty($_SESSION["sesiplo"]) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
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
                    <label for="selectProgramPLO" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('program', 'Program')) ?>
                    </label>
                    <div class="col-sm-8"> 
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectProgramPLO" id="selectProgramPLO">
                            <option value="" <?= empty($_SESSION["programplo"]) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
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

    <!-- Header & Action Buttons -->
    <?php 
        $selectedTerm    = $data['selected_term'] ?? [];
        $selectedProgram = $data['selected_program'] ?? [];

        $sesiID      = $selectedTerm['f005term'] ?? '';
        $semester    = $selectedTerm['semester'] ?? '';
        $programID   = $selectedProgram['id_program'] ?? '';
        $programNama = $selectedProgram['program'] ?? '';
    ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="icares-address-panel-header">
            <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('senarai_plo', 'Senarai PLO')) ?></h5>
        </div>      
        
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                    data-bs-toggle="modal" data-bs-target="#tambah" 
                    data-bs-container="body"
                    data-sesiid="<?= h($sesiID) ?>"
                    data-sesi="<?= h($semester) ?>"
                    data-programid="<?= h($programID) ?>"
                    data-program="<?= h($programNama) ?>"
                    title="<?= h(tr('tambah_plo', 'Tambah PLO')) ?>">
                <i class="ri-add-line me-1"></i> <?= h(tr('tambah', 'Tambah')) ?>
            </button>

            <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnSalin" id="btnSalin" 
                    data-bs-toggle="modal" data-bs-target="#salin" 
                    data-sesiid="<?= h($sesiID) ?>"
                    data-sesi="<?= h($semester) ?>"
                    data-programid="<?= h($programID) ?>"
                    data-program="<?= h($programNama) ?>"
                    title="<?= h(tr('salin_plo', 'Salin PLO')) ?>">
                <i class="ri-file-copy-2-line me-1"></i> <?= h(tr('salin', 'Salin')) ?>
            </button>
        </div>     
    </div>

    <!-- Table Section -->
    <div class="table-responsive dt-standard">
        <table id="dataPLODT" class="table table-bordered align-middle w-100 table-hover">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th class="small" style="width: 15%;"><?= h(tr('kod_plo', 'Kod PLO')) ?></th>
                    <th class="small" style="width: 30%;"><?= h(tr('keterangan_plo', 'Keterangan PLO')) ?></th>
                    <th class="small" style="width: 12%;"><?= h(tr('kod_mqf', 'Kod MQF')) ?></th>
                    <th class="small" style="width: 13%;"><?= h(tr('senarai_peo', 'Senarai PEO')) ?></th>
                    <th class="small" style="width: 10%;"><?= h(tr('senarai_clo', 'Senarai CLO')) ?></th>
                    <th class="small text-center" style="width: 15%;"><?= h(tr('tindakan', 'Tindakan')) ?></th>
                </tr>
            </thead>
            <tbody>     
                <?php 
                $list_dataPLO = $data['list_plo'] ?? [];

                if (empty($list_dataPLO)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'No records found')) ?>
                        </td>
                    </tr>
                <?php else: 
                    foreach ($list_dataPLO as $i => $row):  
                        $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
                        $idPLO   = $row['id_plo'] ?? '';
                        
                        $peoListJson = json_encode(!empty($row['senarai_id_peo']) ? array_map('trim', explode(',', $row['senarai_id_peo'])) : []);
                ?>
                    <tr data-id="<?= h($idPLO) ?>" data-row='<?= $rowJson ?>'>
                        <td class="text-center"><?= $i + 1 ?></td>        
                        <td class="fw-semibold"><?= h($row['kod_plo'] ?? '') ?></td>
                        <td><?= h($row['keterangan_bm'] ?? $row['keterangan'] ?? '') ?></td>
                        <td><?= h($row['kod_mqf'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($row['senarai_kod_peo'])): ?>
                                <span class="badge bg-primary cursor-pointer" 
                                      data-bs-toggle="popover" 
                                      data-bs-trigger="hover focus" 
                                      data-bs-placement="top"
                                      title="Keterangan PEO"
                                      data-bs-content="<?= h($row['senarai_keterangan_peo'] ?? '') ?>">
                                    <?= h($row['senarai_kod_peo']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= h($row['senarai_clo'] ?? '-') ?></td>
                        <td class="text-center">    
                            <button type="button" 
                                    class="btn btn-sm btn-icon btn-outline-success me-1" 
                                    id="btnKemaskini" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#kemaskini" 
                                    data-sesiid="<?= h($sesiID) ?>"
                                    data-sesi="<?= h($semester) ?>"
                                    data-programid="<?= h($programID) ?>"
                                    data-program="<?= h($programNama) ?>"
                                    data-idplo="<?= h($idPLO) ?>"
                                    data-kodplo="<?= h($row["kod_plo"] ?? '') ?>"
                                    data-keteranganbm="<?= h($row["keterangan_bm"] ?? '') ?>"
                                    data-kodmqf="<?= h($row["kod_mqf"] ?? '') ?>"
                                    data-peolist="<?= htmlspecialchars($peoListJson, ENT_QUOTES, 'UTF-8') ?>"
                                    title="<?= h($lang['TTP-KEMASKINI'] ?? 'Kemaskini') ?>">
                                <i class="ri-edit-line"></i>
                            </button>

                            <button type="button" 
                                    class="btn btn-sm btn-icon btn-outline-danger" 
                                    id="btnHapus" 
                                    onclick="deleteFunc(<?= h($idPLO) ?>)" 
                                    title="<?= h($lang['TTP-HAPUS'] ?? 'Hapus') ?>">
                                <i class="ri-delete-bin-7-line"></i>
                            </button>       
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