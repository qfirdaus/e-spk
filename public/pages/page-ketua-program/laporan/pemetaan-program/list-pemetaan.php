<?php
  require_once __DIR__ . '/../../../../controllers/PemetaanProgramController.php'; 

  $controller = new PemetaanProgramController();
  $controller->handlePostRequest();
  $data = $controller->getHalamanData();
?>

<!-- PASS DATA KE JAVASCRIPT-->
<script>
    const PEO_DATA = <?= json_encode($data['peoStats'] ?? []) ?>;
    const PLO_DATA = <?= json_encode($data['ploStats'] ?? []) ?>;
</script>

<div class="konvo-tab-card p-4 mb-4 shadow-sm rounded-3">
    <div class="icares-address-panel-header mb-3">
        <h5 class="text-h5 fw-bold text-primary m-0">Carian </h5>
    </div>

    <form action="" method="POST" id="formCarianPemetaan">
        <div class="row gx-4 gy-3">
            <div class="col-md-8 col-lg-6">
                <!-- Pengajian -->
                <div class="row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label fw-semibold">Peringkat Pengajian</label>
                    <div class="col-sm-8">                    
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian">
                            <option value="" <?= empty($data['pengajian']) ? 'selected' : '' ?> disabled>- Pilih -</option>
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
                            <option value="" <?= empty($data['sesi']) ? 'selected' : '' ?> disabled>- Pilih -</option>
                            <?php foreach ($data['termList'] as $sesi): ?>
                                <option value="<?= h($sesi["f005term"]) ?>" <?= ($data['sesi'] === $sesi["f005term"]) ? 'selected' : '' ?>><?= h($sesi["f005term"]) ?> - <?= h($sesi["semester"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Program -->
                <div class="row align-items-center">
                    <label class="col-sm-4 col-form-label fw-semibold">Program</label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectProgram">
                            <option value="" <?= empty($data['program']) ? 'selected' : '' ?> disabled>- Pilih -</option>
                            <?php foreach ($data['programList'] as $prog): ?>
                                <option value="<?= h((string)$prog["id_program"]) ?>" <?= ($data['program'] == $prog["id_program"]) ? 'selected' : '' ?>><?= h($prog["program"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <hr class="my-4 text-muted">

    <?php if (empty($data['peoStats']) && empty($data['ploStats'])): ?>
        <div class="text-center text-muted py-5 bg-light-subtle rounded-3 border border-light-subtle shadow-sm mb-2">
            <i class="ri-inbox-2-line d-block text-secondary mb-2" style="font-size: 3rem;"></i>
            <span class="fw-bold fs-5 text-dark">Tiada rekod statistik dijumpai.</span>
            <p class="mb-0 mt-1">Sila buat carian Peringkat Pengajian, Sesi, dan Program di atas untuk melihat carta.</p>
        </div>
    <?php endif; ?>

    <!-- SEKSYEN 1: PEO -->
    <?php if (!empty($data['peoStats'])): ?>
    <div class="bg-primary bg-gradient text-white p-3 rounded-3 shadow-sm mb-4 mt-2 d-flex align-items-center">
        <i class="ri-bar-chart-grouped-line fs-2 me-3 opacity-75"></i>
        <div>
            <h4 class="fw-bold mb-0 text-white">Statistik Pemetaan PEO</h4>
            <small class="text-white-50">Rumusan taburan Objektif Pendidikan Program</small>
        </div>
    </div>
    <div class="table-responsive border rounded-3 shadow-sm mb-4">
        <table class="table table-bordered table-hover text-center mb-0">
            <thead class="bg-light">
                <tr><th colspan="<?= count($data['peoStats']) ?>" class="text-primary fw-bold"><?= h($data['programName']) ?></th></tr>
                <tr>
                    <?php foreach ($data['peoStats'] as $peo): ?>
                        <th class="bg-primary-subtle"><?= h($peo['kod_peo']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach ($data['peoStats'] as $peo): ?>
                        <td class="fw-bold"><?= $peo['total'] ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($data['peoStats'] as $peo): ?>
                        <td class="text-success fw-bold"><?= $peo['percentage'] ?>%</td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Carta PEO -->
    <div class="row mb-5">
        <div class="col-md-6"><div class="card shadow-sm border-0"><div class="card-body"><div id="pie_peo" style="height:400px"></div></div></div></div>
        <div class="col-md-6"><div class="card shadow-sm border-0"><div class="card-body"><div id="bar_peo" style="height:400px"></div></div></div></div>
    </div>
    <?php endif; ?>

    
    <!-- SEKSYEN 2: PLO  -->
    <?php if (!empty($data['ploStats'])): ?>
    <hr class="my-5 text-muted"> 

    <div class="bg-info bg-gradient text-white p-3 rounded-3 shadow-sm mb-4 d-flex align-items-center">
        <i class="ri-pie-chart-2-line fs-2 me-3 opacity-75"></i>
        <div>
            <h4 class="fw-bold mb-0 text-white">Statistik Pemetaan CLO - PLO</h4>
            <small class="text-white-50">Rumusan taburan Hasil Pembelajaran Program</small>
        </div>
    </div>
    <div class="table-responsive border rounded-3 shadow-sm mb-4">
        <table class="table table-bordered table-hover text-center mb-0">
            <thead class="bg-light">
                <tr><th colspan="<?= count($data['ploStats']) ?>" class="text-primary fw-bold"><?= h($data['programName']) ?></th></tr>
                <tr>
                    <?php foreach ($data['ploStats'] as $plo): ?>
                        <th class="bg-primary-subtle"><?= h($plo['kod_plo']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach ($data['ploStats'] as $plo): ?>
                        <td class="fw-bold"><?= $plo['total'] ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($data['ploStats'] as $plo): ?>
                        <td class="text-success fw-bold"><?= $plo['percentage'] ?>%</td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Carta PLO -->
    <div class="row">
        <div class="col-md-6"><div class="card shadow-sm border-0"><div class="card-body"><div id="pie_plo" style="height:400px"></div></div></div></div>
        <div class="col-md-6"><div class="card shadow-sm border-0"><div class="card-body"><div id="bar_plo" style="height:400px"></div></div></div></div>
    </div>
    <?php endif; ?>

</div>