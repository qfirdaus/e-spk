<?php
require_once __DIR__ . '/../../../controllers/MaklumatPLOController.php';

$controller = new MaklumatPLOController();
$data = $controller->getHalamanData();

if ($controller->getErrorMessage()) {
    echo '<div class="alert alert-danger shadow-sm border-0 d-flex align-items-center"><i class="ri-error-warning-fill fs-4 me-2"></i> Ralat: ' . h($controller->getErrorMessage()) . '</div>';
}
?>

<div class="card border border-light-subtle shadow-none p-4 mb-4 rounded-3 bg-white">
    <div class="icares-address-panel-header mb-3">
        <h5 class="text-h5 fw-bold text-primary"><?= h(tr('carian', 'Carian')) ?></h5>
    </div>
    
    <form id="form-maklumat-plo" method="POST" action="">
        <div class="row gx-4 gy-2">
            <div class="col-md-8 col-lg-6">
                
                <!-- Pengajian -->
                <div class="row align-items-center mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold text-nowrap">
                        <?= h(tr('peringkat_pengajian','Peringkat Pengajian')) ?>
                    </label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                            <option value="" <?= (($_SESSION["pengajianplo"] ?? '') === '') ? 'selected' : '' ?> disabled>-- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> --</option>
                            <option value="Asasi" <?= (($_SESSION["pengajianplo"] ?? '') === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                            <option value="Diploma" <?= (($_SESSION["pengajianplo"] ?? '') === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                            <option value="Sarjana Muda" <?= (($_SESSION["pengajianplo"] ?? '') === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                        </select>                    
                    </div>                 
                </div>   

                <!-- Sesi -->
                <div class="row align-items-center mb-3">
                    <label class="col-sm-4 col-form-label fw-semibold text-nowrap">
                        <?= h(tr('sesi_kemasukan','Sesi Kemasukan')) ?>
                    </label>
                    <div class="col-sm-8"> 
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                            <option value="" <?= (empty($_SESSION["sesiplo"])) ? 'selected' : '' ?> disabled>-- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> --</option>
                            <?php foreach ($data['list_sesi'] as $sesi): ?>
                            <option value="<?= h($sesi['f005term']) ?>" <?= ($sesi['f005term'] === ($data['selected_term']['f005term'] ?? '')) ? 'selected' : '' ?> >
                                <?= h($sesi['f005term']) ?> - <?= h($sesi['semester']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>                      
                    </div>                 
                </div>                                      
            </div>
        </div>
    </form>

    <hr class="my-4 text-muted">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="icares-address-panel-header">
            <h5 class="text-h5 fw-bold text-primary m-0">
              <?= h(tr('senarai_plo','Senarai PLO')) ?>
            </h5>
        </div>

        <div class="d-flex gap-2">
            <?php 
                $selectedTerm = $data['selected_term'] ?? [];
                $selectedProgram = $data['selected_program'] ?? [];

                $sesiID = $selectedTerm['f005term'] ?? '';
                $semester = $selectedTerm['semester'] ?? '';
                $programID = $selectedProgram['id_program'] ?? '';
                $programNama = $selectedProgram['program'] ?? '';
            ?>

            <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                    data-bs-toggle="modal" data-bs-target="#tambah" 
                    data-bs-container="body"
                    data-sesiid="<?= h($sesiID) ?>"
                    data-sesi="<?= h($semester) ?>"
                    data-programid="<?= h($programID) ?>"
                    data-program="<?= h($programNama) ?>"
                    title="<?= h(tr('tambah_plo', 'Tambah PLO')) ?>">
                <i class="ri-add-line fs-6 me-1"></i> <?= h(tr('tambah', 'Tambah')) ?>
            </button>

            <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnSalin" id="btnSalin" 
                    data-bs-toggle="modal" data-bs-target="#salin" 
                    data-sesi="<?= h($sesiID) ?>"
                    data-programid="<?= h($programID) ?>"
                    title="<?= h(tr('salin_plo', 'Salin PLO')) ?>">
                <i class="ri-file-copy-2-line fs-6 me-1"></i> <?= h(tr('salin', 'Salin')) ?>
            </button>
        </div>    
    </div>

    <!-- JADUAL DATATABLES -->
    <div class="w-100 mt-3">
        <table id="dataPLODT" class="table table-sm table-bordered align-middle table-hover w-100">
            <thead class="table-light">
                <tr>
                    <th width="5%" class="text-center text-secondary fw-bold text-uppercase">NO</th>
                    <th width="10%" class="text-secondary fw-bold text-uppercase"><?= h(tr('kod_plo', 'KOD PLO')) ?></th>
                    <th width="30%" class="text-secondary fw-bold text-uppercase"><?= h(tr('keterangan_plo', 'KETERANGAN PLO')) ?></th>
                    <th width="10%" class="text-center text-secondary fw-bold text-uppercase"><?= h(tr('kod_mqf', 'KOD MQF')) ?></th>
                    <th width="15%" class="text-center text-secondary fw-bold text-uppercase"><?= h(tr('senarai_peo', 'SENARAI PEO')) ?></th>
                    <th width="15%" class="text-center text-secondary fw-bold text-uppercase"><?= h(tr('senarai_clo', 'SENARAI CLO')) ?></th>
                    <th width="15%" class="text-center text-secondary fw-bold text-uppercase"><?= h(tr('tindakan', 'TINDAKAN')) ?></th>
                </tr>
            </thead>

            <tbody>     
                <?php 
                  $list_dataPLO = $data['list_plo'] ?? [];

                  if (empty($list_dataPLO)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'Tiada rekod dijumpai')) ?>
                        </td>
                    </tr>

                <?php else: 
                    foreach ($list_dataPLO as $i => $row):  
                        $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
                        $idPLO = $row['id_plo'] ?? '';                  
                ?>
                    <tr data-id="<?= $idPLO ?>" data-row='<?= $rowJson ?>'>

                        <td class="text-center text-muted"><?= $i + 1 ?></td>         

                        <td class="fw-bold text-primary" ><?= h($row['kod_plo'] ?? '') ?></td>

                        <td class="text-dark">
                            <?= h($row['keterangan_bm'] ?? $row['keterangan'] ?? '') ?>
                        </td>

                        <td class="text-center text-dark">
                            <?= h($row['kod_mqf'] ?? '-') ?>
                        </td>

                        <td class="text-center text-dark"><?= h($row['senarai_peo'] ?? '-') ?></td>

                        <td class="text-center text-dark"><?= h($row['senarai_clo'] ?? '-') ?></td>

                        <td class="text-center">    
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" 
                                        class="btn btn-sm btn-link text-primary p-0" 
                                        id="btnKemaskini" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#kemaskini" 
                                        data-sesiid="<?= h($sesiID) ?>"
                                        data-sesi="<?= h($semester) ?>"
                                        data-programid="<?= h($programID) ?>"
                                        data-program="<?= h($programNama) ?>"
                                        data-idplo="<?= h($idPLO) ?>"
                                        data-kodplo="<?= h($row["kod_plo"]) ?>"
                                        data-keteranganbm="<?= h($row["keterangan_bm"]) ?>"
                                        data-kodmqf="<?= h($row["kod_mqf"]) ?>"
                                        data-peolist='<?= json_encode($list_peo_checked ?? []) ?>'
                                        title="<?= h($lang['TTP-KEMASKINI'] ?? 'Kemaskini') ?>">
                                    <i class="ri-edit-line fs-5"></i>
                                </button>

                                <button type="button" 
                                        class="btn btn-sm btn-link text-danger p-0" 
                                        id="btnHapus" 
                                        onclick="deleteFunc(<?= h($idPLO) ?>)" 
                                        title="<?= h($lang['TTP-HAPUS'] ?? 'Hapus') ?>">
                                    <i class="ri-delete-bin-7-line fs-5"></i>
                                </button>
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