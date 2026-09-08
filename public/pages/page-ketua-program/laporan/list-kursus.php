<?php
  require_once __DIR__ . '/../../../controllers/LaporanKursusKPController.php'; 
  $controller = new LaporanKursusKPController();
  $controller->handlePostRequest();
  $data = $controller->getHalamanData();

?>

<div class="konvo-tab-card p-4 mb-4 shadow-sm rounded-3">
    <!-- Carian Section -->
    <div class="icares-address-panel-header mb-3">
        <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('carian_laporan', 'Carian Laporan')) ?></h5>
    </div>

    <form action="" method="POST" id="formCarianKursus">
        <div class="row gx-4 gy-3">
            <div class="col-md-8 col-lg-6">
                
                <!-- Kategori Kursus -->
                <div class="row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label fw-semibold">Kategori Kursus</label>
                    <div class="col-sm-8">                    
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectKategori">
                            <option value="" <?= empty($data['kategori']) ? 'selected' : '' ?> disabled>- Sila Pilih -</option>
                            <option value="Program" <?= ($data['kategori'] === 'Program') ? 'selected' : '' ?>>PROGRAM</option>
                            <option value="Universiti" <?= ($data['kategori'] === 'Universiti') ? 'selected' : '' ?>>UNIVERSITI</option>
                        </select>
                    </div>
                </div>

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

                <!-- Sesi Kemasukan -->
                <div class="row align-items-center mb-2">
                    <label class="col-sm-4 col-form-label fw-semibold">Sesi Kemasukan</label>
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

    <!-- SENARAI KURSUS -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="icares-address-panel-header">
            <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('panel_senarai_kursus', 'Senarai Kursus')) ?></h5>
        </div>   
        
        <?php if (!empty($data['courseList'])): ?>
            <a href="generate-excel-list.php" class="btn btn-sm btn-success rounded-3">
                <i class="ri-file-excel-2-line"></i> Eksport Senarai
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive dt-standard">
        <table id="tableLaporanKursus" class="table table-bordered table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th style="width: 10%" class="text-center">Kod Kursus</th>
                    <th style="width: 25%">Nama Kursus</th>
                    <th style="width: 30%">Penyelaras</th>
                    <th style="width: 12%" class="text-center">Kategori</th>
                    <th style="width: 13%" class="text-center">Tarikh Kemaskini</th>
                    <th style="width: 10%" class="text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['courseList'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            Tiada rekod dijumpai
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['courseList'] as $kursus): ?>
                        <tr>
                            <td class="text-center fw-bold text-primary"><?= h($kursus["kod_kursus"]) ?></td>
                            <td class="fw-medium"><?= h($kursus["subjekbm"]) ?></td>
                            <td>
                                <!-- Nama Penyelaras -->
                                <strong class="text-dark"><?= h(ucwords(strtolower(trim($kursus["gelar_nama"])))) ?></strong>

                                <!-- No Staf-->
                                <span class="badge bg-lightcream text-dark mt-1"><?= h(trim($kursus["penyelaras_kursus"] ?? '')) ?></span>                                
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary px-2 py-1">
                                    <?= h($kursus["kategori_kursus"] ?? '-') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?= !empty($kursus["updated_date"]) ? date('d-m-Y', strtotime($kursus["updated_date"])) : '-' ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Lihat Detail -->
                                    <button class="btn btn-sm btn-outline-warning btnLihatPerincian" type="button" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalPerincian"
                                            data-idkursus="<?= h((string)$kursus["id_kursus"]) ?>"
                                            data-bs-toggle="tooltip"
                                            title="Lihat Perincian">
                                        <i class="ri-eye-line fs-5"></i>
                                    </button>

                                    <!-- Download -->
                                    <a href="generate-excel.php?course=<?= h((string)$kursus["id_kursus"]) ?>" class="btn btn-sm btn-outline-success" title="Muat Turun Table 4 (Excel)">
                                        <i class="ri-file-excel-2-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>   
</div>