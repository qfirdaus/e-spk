<?php
  require_once __DIR__ . '/../../../../controllers/PemetaanKursusUniversitiPLOController.php'; 

  $controller = new PemetaanKursusUniversitiPLOController();
  $controller->handlePostRequest();
  $data = $controller->getHalamanData();
?>

<div class="konvo-tab-card p-4 mb-4 shadow-sm rounded-3">
    <div class="icares-address-panel-header mb-3">
        <h5 class="text-h5 fw-bold text-primary m-0">Carian</h5>
    </div>

    <form action="" method="POST" id="formCarianPemetaan">
        <div class="row gx-4 gy-3">
            <div class="col-md-8 col-lg-6">
                <!-- Peringkat Pengajian -->
                <div class="row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label fw-semibold">Peringkat Pengajian</label>
                    <div class="col-sm-8">                    
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian">
                            <option value="" <?= empty($data['pengajian']) ? 'selected' : '' ?> disabled>- Sila Pilih -</option>
                            <option value="Asasi" <?= ($data['pengajian'] === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                            <option value="Diploma" <?= ($data['pengajian'] === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                            <option value="Sarjana Muda" <?= ($data['pengajian'] === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                        </select>
                    </div>
                </div>

                <!-- Sesi -->
                <div class="row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label fw-semibold">Sesi</label>
                    <div class="col-sm-8">                    
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesi">
                            <option value="" <?= empty($data['sesi']) ? 'selected' : '' ?> disabled>- Sila Pilih -</option>
                            <?php if (!empty($data['termList'])): ?>
                                <?php foreach ($data['termList'] as $sesi): ?>
                                    <option value="<?= h($sesi["f005term"]) ?>" <?= ($data['sesi'] === $sesi["f005term"]) ? 'selected' : '' ?>>
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

    <!-- MATRIKS PEMETAAN -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
        <div class="icares-address-panel-header">
            <h5 class="text-h5 fw-bold text-primary m-0">Pemetaan Kursus Universiti dan PLO</h5>
        </div>   
    </div>

    <div class="w-100 mt-3">
        <table id="tablePemetaanUniv" class="table table-sm table-bordered align-middle table-hover w-100">
            <thead class="align-middle text-center">
                <tr>
                    <th rowspan="2" width="30%" class="text-center bg-light fw-bold text-uppercase border-bottom-0 align-middle">
                        KURSUS
                    </th>
                    <?php if (!empty($data['ploHeaders'])): ?>
                        <th colspan="<?= count($data['ploHeaders']) ?>" class="bg-primary-subtle fw-bold border-bottom-0">
                            Hasil Pembelajaran Program (PLO)
                        </th>
                    <?php else: ?>
                        <th class="bg-primary-subtle cfw-bold border-bottom-0">PLO</th>
                    <?php endif; ?>
                </tr>
                <tr>
                    <?php if (!empty($data['ploHeaders'])): ?>
                        <?php foreach ($data['ploHeaders'] as $plo): ?>
                            <th class="fw-bold bg-light text-dark text-center" style="min-width: 65px;">
                                <?= h($plo['kod_plo']) ?>
                            </th>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <th class="fw-bold bg-light text-dark text-center">-</th>
                    <?php endif; ?>
                </tr>
            </thead>
            
            <tbody class="border-top-0">
                <?php if (empty($data['courseData'])): ?>
                    <tr>
                        <td colspan="<?= count($data['ploHeaders'] ?? []) + 2 ?>" class="text-center text-muted py-5 bg-light-subtle">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'Tiada rekod dijumpai')) ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['courseData'] as $kursus): ?>
                        <tr>
                            <td class="text-start px-3">
                                <div class="d-flex flex-column">
                                    <span class="text-primary fw-bold" style="font-size: 0.95rem;"><?= h($kursus["kod_kursus"]) ?></span>
                                    <small class="text-muted fw-medium mt-1"><?= h($kursus["subjekbm"]) ?></small>
                                </div>
                            </td>
                            
                            <?php if (!empty($data['ploHeaders'])): ?>
                                <?php foreach ($data['ploHeaders'] as $plo): ?>
                                    <td class="text-center">
                                        <?php if (in_array($plo['id_plo'], $kursus['plos'])): ?>
                                            <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle rounded-circle" style="width: 26px; height: 26px;">
                                                <i class="ri-check-line fs-5 text-success fw-bold"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <td class="text-center">-</td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div> 
</div>