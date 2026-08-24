<?php
require_once __DIR__ . '/../../../controllers/MaklumatPEOController.php';

$controller = new MaklumatPEOController();
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
                    <label for="selectPengajian" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('peringkat_pengajian', 'Peringkat Pengajian')) ?>
                    </label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                            <option value="" <?= (($_SESSION["pengajian"] ?? '') === '') ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <option value="Asasi" <?= (($_SESSION["pengajian"] ?? '') === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                            <option value="Diploma" <?= (($_SESSION["pengajian"] ?? '') === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                            <option value="Sarjana Muda" <?= (($_SESSION["pengajian"] ?? '') === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                        </select>                    
                    </div>         
                </div>   

                <!-- Sesi Kemasukan -->
                <div class="mb-3 row align-items-center">
                    <label for="selectSesi" class="col-sm-4 col-form-label text-nowrap fw-semibold">
                        <?= h(tr('sesi_kemasukan', 'Sesi Kemasukan')) ?>
                    </label>
                    <div class="col-sm-8"> 
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                            <option value="" <?= empty($_SESSION["sesi"]) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <?php foreach ($data['list_sesi'] as $sesi): ?>
                                <option value="<?= h($sesi['sesi2']) ?>" <?= ($sesi['sesi2'] === ($data['selected_term']['sesi2'] ?? '')) ? 'selected' : '' ?>> 
                                    <?= h($sesi['term']) ?> - <?= h($sesi['sesi2']) ?>
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
                            <option value="" <?= empty($_SESSION["program"]) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
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

        $sesiID      = $selectedTerm['term'] ?? '';
        $sesi2       = $selectedTerm['sesi2'] ?? '';
        $programID   = $selectedProgram['id_program'] ?? '';
        $programNama = $selectedProgram['program'] ?? '';
        $ptj         = $data['kodJabatan_staf'] ?? ''; 
    ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="icares-address-panel-header">
            <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('senarai_peo', 'Senarai PEO')) ?></h5>
        </div>      
        
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                    data-bs-toggle="modal" data-bs-target="#tambah" 
                    data-bs-container="body"
                    data-sesiid="<?= h($sesiID) ?>"
                    data-sesi="<?= h($sesi2) ?>"
                    data-programid="<?= h($programID) ?>"
                    data-program="<?= h($programNama) ?>"
                    data-ptj="<?= h($ptj) ?>"
                    title="<?= h(tr('tambah_peo', 'Tambah PEO')) ?>">
                <i class="ri-add-line me-1"></i> <?= h(tr('tambah', 'Tambah')) ?>
            </button>

            <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnSalin" id="btnSalin" 
                    data-bs-toggle="modal" data-bs-target="#salin" 
                    data-sesiid="<?= h($sesiID) ?>"
                    data-sesi="<?= h($sesi2) ?>"
                    data-programid="<?= h($programID) ?>"
                    title="<?= h(tr('salin_peo', 'Salin PEO')) ?>">
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
                    <th class="small" style="width: 15%;"><?= h(tr('kod_peo', 'Kod PEO')) ?></th>
                    <th class="small" style="width: 35%;"><?= h(tr('keterangan_peo', 'Keterangan PEO')) ?></th>
                    <th class="small" style="width: 15%;"><?= h(tr('tarikh_senat', 'Tarikh Senat')) ?></th>
                    <th class="small" style="width: 15%;"><?= h(tr('senarai_plo', 'Senarai PLO')) ?></th>
                    <th class="small text-center" style="width: 15%;"><?= h(tr('tindakan', 'Tindakan')) ?></th>
                </tr>
            </thead>
            <tbody>     
                <?php 
                $list_dataPEO = $data['list_peo'] ?? [];

                if (empty($list_dataPEO)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'No records found')) ?>
                        </td>
                    </tr>
                <?php else: 
                    foreach ($list_dataPEO as $i => $row):  
                        $rowJson     = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
                        $idPEO       = $row['id_peo'] ?? '';
                        $tarikhSenat = !empty($row['tarikh_senat']) ? date('d-m-Y', strtotime($row['tarikh_senat'])) : '-';
                ?>
                    <tr data-id="<?= h($idPEO) ?>" data-row='<?= $rowJson ?>'>
                        <td class="text-center"><?= $i + 1 ?></td>        
                        <td class="fw-semibold"><?= h($row['kod_peo'] ?? '') ?></td>
                        <td><?= h($row['keterangan_bm'] ?? '') ?></td>
                        <td><?= h($tarikhSenat) ?></td>
                        <td>
                            <?php if (!empty($row['senarai_kod_plo'])): ?>
                                <span class="badge bg-primary cursor-pointer" 
                                      data-bs-toggle="popover" 
                                      data-bs-trigger="hover focus" 
                                      data-bs-placement="top"
                                      title="Keterangan PLO"
                                      data-bs-content="<?= h($row['senarai_keterangan_plo'] ?? '') ?>">
                                    <?= h($row['senarai_kod_plo']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">    
                            <button type="button" 
                                    class="btn btn-sm btn-icon btn-outline-success me-1" 
                                    id="btnKemaskini" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#kemaskini" 
                                    data-sesiid="<?= h($sesiID) ?>"
                                    data-sesi="<?= h($sesi2) ?>"
                                    data-programid="<?= h($programID) ?>"
                                    data-program="<?= h($programNama) ?>"
                                    data-idpeo="<?= h($idPEO) ?>"
                                    data-kodpeo="<?= h($row["kod_peo"] ?? '') ?>"
                                    data-keteranganbm="<?= h($row["keterangan_bm"] ?? '') ?>"
                                    data-tarikhsenat="<?= h($tarikhSenat) ?>"
                                    title="<?= h($lang['TTP-KEMASKINI'] ?? 'Kemaskini') ?>">
                                <i class="ri-edit-line"></i> 
                            </button>

                            <button type="button" 
                                    class="btn btn-sm btn-icon btn-outline-danger" 
                                    id="btnHapus" 
                                    onclick="deleteFunc(<?= h($idPEO) ?>)" 
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