<?php 
  require_once __DIR__ . '/../../../controllers/LaporanPLOPKController.php';
  
  $controller = new LaporanPLOPKController();
  $controller->handlePostRequest();
  $data = $controller->getHalamanData();
?>

<div class="konvo-tab-card p-4 mb-4 shadow-sm rounded-3">
    <!-- Carian Section -->
    <div class="icares-address-panel-header mb-3">
        <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('carian_laporan', 'Carian Laporan')) ?></h5>
    </div>

    <form action="" method="POST" id="formCarianLaporan">
        <div class="row gx-4 gy-3">
            <div class="col-md-8 col-lg-6">
                
                <!-- Peringkat Pengajian -->
                <div class="row align-items-center mb-2">
                    <label for="selectPengajian" class="col-sm-4 col-form-label fw-semibold">
                        <?= h(tr('lbl_peringkat_pengajian', 'Peringkat Pengajian')) ?>
                    </label>
                    <div class="col-sm-8">                    
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                            <option value="" <?= empty($data['pengajian']) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <option value="Asasi" <?= ($data['pengajian'] === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                            <option value="Diploma" <?= ($data['pengajian'] === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                            <option value="Sarjana Muda" <?= ($data['pengajian'] === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                        </select>
                    </div>
                </div>

                <!-- Sesi Kemasukan -->
                <div class="row align-items-center mb-2">
                    <label for="selectSesi" class="col-sm-4 col-form-label fw-semibold">
                        <?= h(tr('lbl_sesi_kemasukan', 'Sesi Kemasukan')) ?>
                    </label>
                    <div class="col-sm-8">                    
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                            <option value="" <?= empty($data['sesi']) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
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

                <!-- Program -->
                <div class="row align-items-center">
                    <label for="selectProgram" class="col-sm-4 col-form-label fw-semibold">
                        <?= h(tr('lbl_program', 'Program')) ?>
                    </label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectProgram" id="selectProgram">
                            <option value="" <?= empty($data['program']) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <?php if (!empty($data['programList'])): ?>
                                <?php foreach ($data['programList'] as $prog): ?>
                                    <option value="<?= h((string)$prog["id_program"]) ?>" <?= ($data['program'] == $prog["id_program"]) ? 'selected' : '' ?>>
                                        <?= h($prog["program"]) ?>
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

    <!-- SENARAI PLO -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="icares-address-panel-header">
            <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('panel_senarai_plo', 'Senarai PLO')) ?></h5>
        </div>   
    </div>

    <div class="w-100 mt-3">
        <table id="tableLaporanPLO" class="table table-sm table-bordered align-middle table-hover w-100">
            <thead class="table-light">
                <tr>
                    <th style="width: 15%" class="text-center"><?= h(tr('col_kod_plo', 'Kod PLO')) ?></th>
                    <th style="width: 45%"><?= h(tr('col_keterangan_plo', 'Keterangan PLO')) ?></th>
                    <th style="width: 20%"><?= h(tr('col_senarai_peo', 'Senarai PEO')) ?></th>
                    <th style="width: 20%"><?= h(tr('col_senarai_clo', 'Senarai CLO')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $list_plo = $data['ploList'] ?? [];
                if (empty($list_plo)): 
                ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'Tiada rekod dijumpai')) ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($list_plo as $plo): ?>
                        <tr>
                            <td class="text-center fw-bold text-primary"><?= h($plo["kod_plo"]) ?></td>
                            <td><?= h($plo["keterangan_bm"]) ?></td>

                            <td>
                                <?php if (!empty($plo['senarai_peo'])): ?>
                                    <?php 
                                        $arrKodPeo = [];
                                        $arrKetPeo = [];
                                        foreach ($plo['senarai_peo'] as $peo) {
                                            $arrKodPeo[] = $peo['kod_peo'];
                                            $arrKetPeo[] = $peo['kod_peo'] . ': ' . $peo['keterangan_bm'];
                                        }
                                    ?>
                                    <span class="badge bg-primary cursor-pointer" 
                                        data-bs-toggle="popover" 
                                        data-bs-trigger="hover focus" 
                                        data-bs-placement="top"
                                        title="Keterangan PEO"
                                        data-bs-content="<?= h(implode(' | ', $arrKetPeo)) ?>">
                                        <?= h(implode(', ', $arrKodPeo)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($plo['senarai_clo'])): ?>
                                    <?php 
                                        $arrKodClo = [];
                                        $arrKetClo = [];
                                        foreach ($plo['senarai_clo'] as $clo) {
                                            $arrKodClo[] = $clo['kod_clo'];
                                            $arrKetClo[] = $clo['kod_clo'] . ': ' . $clo['keterangan_bm'];
                                        }
                                    ?>
                                    <span class="badge bg-success cursor-pointer" 
                                        data-bs-toggle="popover" 
                                        data-bs-trigger="hover focus" 
                                        data-bs-placement="top"
                                        title="Keterangan CLO"
                                        data-bs-content="<?= h(implode(' | ', $arrKetClo)) ?>">
                                        <?= h(implode(', ', $arrKodClo)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>   
</div>