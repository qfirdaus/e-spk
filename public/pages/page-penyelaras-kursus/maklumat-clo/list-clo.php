<?php 
  require_once __DIR__ . '/../../../controllers/MaklumatCLOController.php';
  $cloController = new MaklumatCLOController();
  $data          = $cloController->getHalamanData(); 

  $pengajianTerpilih = $_SESSION["pengajianclo"] ?? '';
  $sesiTerpilih      = $_SESSION["sesiclo"] ?? '';
  $kursusTerpilih    = $_SESSION["kursusclo"] ?? '';

  $semester = '';
  if (!empty($sesiTerpilih)) {
      foreach ($data['list_sesi'] as $row) {
          if ((string)$row['f005term'] === (string)$sesiTerpilih) {
              $semester = $row['semester']; 
              break;
          }
      }
  }

  $course = '';
  if (!empty($kursusTerpilih)) {
      foreach ($data['list_kursus'] as $kursus) {
          if ((string)$kursus['id_kursus'] === (string)$kursusTerpilih) {
              $course = $kursus['kodk'] . " - " . $kursus['subjekbm']; 
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

    <form action="" method="POST" id="formCarianCLO">
        <div class="row gx-4 gy-3">
            <div class="col-md-8 col-lg-6">
                
                <!-- Peringkat Pengajian -->
                <div class="row align-items-center mb-2">
                    <label for="selectPengajian" class="col-sm-4 col-form-label fw-semibold">
                        <?= h(tr('lbl_peringkat_pengajian', 'Peringkat Pengajian')) ?>
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

                <!-- Sesi Kemasukan -->
                <div class="row align-items-center mb-2">
                    <label for="selectSesi" class="col-sm-4 col-form-label fw-semibold">
                        <?= h(tr('lbl_sesi_kemasukan', 'Sesi Kemasukan')) ?>
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

                <!-- Kursus -->
                <div class="row align-items-center">
                    <label for="selectKursus" class="col-sm-4 col-form-label fw-semibold">
                        <?= h(tr('col_kursus', 'Kursus')) ?>
                    </label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectKursus" id="selectKursus">
                            <option value="" <?= empty($kursusTerpilih) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                            <?php if (!empty($data['list_kursus'])): ?>
                                <?php foreach ($data['list_kursus'] as $kursus): ?>
                                    <option value="<?= h((string)$kursus["id_kursus"]) ?>" <?= ($kursusTerpilih == $kursus["id_kursus"]) ? 'selected' : '' ?>>
                                        <?= h($kursus["kodk"]) ?> - <?= h($kursus["subjekbm"]) ?>
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

    <!-- SENARAI CLO -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="icares-address-panel-header">
            <h5 class="text-h5 fw-bold text-primary m-0"><?= h(tr('panel_senarai_clo', 'Senarai CLO')) ?></h5>
        </div>   
        
        <!-- Tambah --->
        <?php if (!empty($sesiTerpilih) && !empty($kursusTerpilih)): ?>
            <button class="btn btn-sm btn-outline-info rounded-3" type="button" id="btnTambah" data-bs-toggle="modal" data-bs-target="#tambah" 
                    data-sesiid="<?= h($sesiTerpilih) ?>"
                    data-sesi="<?= h($semester) ?>"
                    data-kursusid="<?= h($kursusTerpilih) ?>"
                    data-kursus="<?= h($course) ?>"
                    title="<?= h(tr('ttp_tambah_clo', 'Tambah')) ?>">
                <i class="ri-add-line me-1"></i> <?= h(tr('btn_tambah', 'Tambah')) ?>
            </button>
        <?php endif; ?>
    </div>

    <div class="table-responsive dt-standard">
        <table id="order-table" class="table table-bordered table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th style="width: 10%" class="text-center"><?= h(tr('col_kod_clo', 'Kod CLO')) ?></th>
                    <th style="width: 25%"><?= h(tr('col_keterangan_clo', 'Keterangan CLO')) ?></th>
                    <th style="width: 20%"><?= h(tr('col_senarai_plo', 'Senarai PLO')) ?></th>
                    <th style="width: 15%"><?= h(tr('col_kaedah', 'Kaedah Pengajaran')) ?></th>
                    <th style="width: 15%"><?= h(tr('col_penilaian', 'Penilaian')) ?></th>
                    <th style="width: 15%" class="text-center"><?= h(tr('col_tindakan', 'Tindakan')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $list_clo = $data['list_clo'] ?? [];
                if (empty($list_clo)): 
                ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                            <?= h(tr('no_records', 'No records found')) ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($list_clo as $clo): ?>
                        <tr>
                            <td class="text-center fw-bold text-primary"><?= h($clo["kod_clo"]) ?></td>
                            <td><?= h($clo["keterangan_bm"]) ?></td>
                            <td><?= h($clo["senarai_plo_string"] ?? '-') ?></td>
                            <td><?= h($clo["senarai_kaedah_string"] ?? '-') ?></td>
                            <td><?= h($clo["senarai_penilaian_string"] ?? '-') ?></td>
                            
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Kemaskini -->
                                    <button class="btn btn-sm btn-outline-success btnKemaskiniModal" type="button" data-bs-toggle="modal" data-bs-target="#kemaskini" 
                                            data-idclo="<?= h((string)$clo["id_clo"]) ?>"
                                            data-kodclo="<?= h($clo["kod_clo"]) ?>"
                                            data-keteranganbm="<?= h($clo["keterangan_bm"]) ?>"
                                            data-ploid="<?= h($clo["plo_ids"] ?? '') ?>"
                                            data-kaedahid="<?= h($clo["kaedah_ids"] ?? '') ?>"
                                            data-nilaiid="<?= h($clo["penilaian_ids"] ?? '') ?>"
                                            title="<?= h(tr('ttp_kemaskini', 'Kemaskini Maklumat')) ?>">
                                        <i class="ri-edit-2-line"></i>
                                    </button>
                                    
                                    <!-- Hapus -->
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-clo" 
                                            data-idclo="<?= h((string)$clo["id_clo"]) ?>" 
                                            title="<?= h(tr('ttp_hapus', 'Hapus Maklumat')) ?>">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>   
</div>