<?php
require_once __DIR__ . '/../../../controllers/MaklumatPLOController.php';

$controller = new MaklumatPLOController();
$data = $controller->getHalamanData();

if ($controller->getErrorMessage()) {
    echo "Ralat: " . $controller->getErrorMessage();
}

?>
<div class="icares-address-content">
    <div class="tab-pane show active">
      <div class="icares-address-panel-header">
        <h5><?= h(tr('carian', 'Carian')) ?></h5>
      </div>

      <form id="form-maklumat-plo" method="POST" action="">
        <div class="row">
          <div class="col-12">
            <div class="row">
              <div class="col-md-6 gx-4">
                
                  <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('peringkat_pengajian','Peringkat Pengajian')) ?></label>
                    <div class="col-sm-8">
                      <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectPengajian" id="selectPengajian">
                          <option value="" <?= (($_SESSION["pengajianplo"] ?? '') === '') ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
                          <option value="Asasi" <?= (($_SESSION["pengajianplo"] ?? '') === 'Asasi') ? 'selected' : '' ?>>ASASI</option>
                          <option value="Diploma" <?= (($_SESSION["pengajianplo"] ?? '') === 'Diploma') ? 'selected' : '' ?>>DIPLOMA</option>
                          <option value="Sarjana Muda" <?= (($_SESSION["pengajianplo"] ?? '') === 'Sarjana Muda') ? 'selected' : '' ?>>SARJANA MUDA</option>
                      </select>                    
                    </div>                 
                  </div>   

                  <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('sesi_kemasukan','Sesi Kemasukan')) ?></label>
                    <div class="col-sm-8"> 
                      <select class="form-select form-select-sm select2" onchange="this.form.submit()" name="selectSesi" id="selectSesi">
                          <option value="" <?= (empty($_SESSION["sesiplo"])) ? 'selected' : '' ?> disabled>- <?= h(tr('sila_pilih', 'Sila Pilih')) ?> -</option>
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
          </div>
        </div>
      </form>
    </div>
</div> 
<br><br>
<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('senarai_plo','Senarai PLO')) ?></h5>

    <div class="list-actions" style="float: right; margin-bottom:10px;">
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
            <i class="ri-add-line"></i>
        </button>

        <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnSalin" id="btnSalin" 
                data-toggle="modal" data-target="#salin" 
                data-sesi="<?= h($sesiID) ?>"
                data-programid="<?= h($programID) ?>"
                title="<?= h(tr('salin_plo', 'Salin PLO')) ?>">
            <i class="ri-file-copy-2-line"></i>
        </button>
    </div>    
  </div>
  <div class="table-responsive dt-standard">
    <table id="dataPLODT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center">No</th>
        <th class="small w-30"><?= h(tr('kod_plo', 'Kod PLO')) ?></th>
        <th class="small w-15"><?= h(tr('keterangan_plo', 'Keterangan PLO')) ?></th>
        <th class="small w-12"><?= h(tr('kod_mqf', 'Kod MQF')) ?></th>
        <th class="small w-12"><?= h(tr('senarai_peo', 'Senarai PEO')) ?></th>
        <th class="small w-12"><?= h(tr('senarai_clo', 'Senarai CLO')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        $list_dataPLO = $data['list_plo'] ?? [];

        // Semak jika array kosong
        if (empty($list_dataPLO)): 
      ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            <?= h(tr('no_records', 'No records found')) ?>
          </td>
        </tr>
      <?php 
        else: 
            foreach ($list_dataPLO as $i => $row):  
                $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
                $idPLO = $row['id_plo'] ?? '';
                
                // Definisikan pembolehubah $isExpired yang tercicir sebelum ini
                $today = date('Y-m-d');
                $endDate = !empty($row['end_date']) ? date('Y-m-d', strtotime($row['end_date'])) : '';
                $isExpired = ($endDate && $today > $endDate) ? 'disabled' : '';
      ?>
        <tr data-id="<?= $idPLO ?>" data-row='<?= $rowJson ?>'>

            <td class="col-bil text-center"><?= $i + 1 ?></td>         

            <td><?= h($row['kod_plo'] ?? '') ?></td>

            <td><?= h($row['keterangan_bm'] ?? $row['keterangan'] ?? '') ?></td>

            <td><?= h($row['kod_mqf'] ?? '') ?></td>

            <td><?= h($row['senarai_peo'] ?? '') ?></td>

            <td><?= h($row['senarai_clo'] ?? '') ?></td>

            <td align="center">    
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
                      data-kodplo="<?= h($row["kod_plo"]) ?>"
                      data-keteranganbm="<?= h($row["keterangan_bm"]) ?>"
                      data-kodmqf="<?= h($row["kod_mqf"]) ?>"
                      data-peolist='<?= json_encode($list_peo_checked ?? []) ?>'
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
