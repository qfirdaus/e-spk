<?php
require_once __DIR__ . '/../../../controllers/KetuaJabatanController.php';

$controller = new KetuaJabatanController();
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
              <div class="col-md-12 gx-4">
                
                  <div class="mb-2 row align-items-center">
                    <label class="col-sm-2 col-form-label text-nowrap"><?= h(tr('staf','Staf')) ?></label>
                    <div class="col-sm-8 search-box">
                      <input type="text" class="form-control" id="txtstaf" placeholder="No.Staf / Nama">
                      <div class="search_result" style="background-color: white"></div>                  
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
    <h5 class="text-h5"><?= h(tr('PANEL-SENARAI-KETUA-Jabatan','Senarai Ketua Jabatan')) ?></h5>   
  </div>
  <div class="table-responsive dt-standard">
    <table id="dataKetuaJabatanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center"><?= h(tr('COL-BIL', 'No')) ?></th>
        <th class="small w-30"><?= h(tr('COL-NO-STAF', 'No Staf')) ?></th>
        <th class="small w-15"><?= h(tr('COL-NAMA-STAF', 'Nama')) ?></th>
        <th class="small w-12"><?= h(tr('COL-JABATAN', 'Jabatan')) ?></th>
        <!-- <th class="small w-12"><?= h(tr('COL-TELEFON-PEJ', 'Telefon Pejabat')) ?></th> -->
        <th class="small w-12"><?= h(tr('COL-NO-TELEFON', 'No. Telefon')) ?></th>
        <th class="small w-12"><?= h(tr('COL-EMEL', 'EMEL')) ?></th>
        <th class="small w-12"><?= h(tr('COL-TINDAKAN', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        $list_dataKetuaJabatan = $data['list_ketua_jabatan'] ?? [];

        // Semak jika array kosong
        if (empty($list_dataKetuaJabatan)): 
      ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">
            <?= h(tr('no_records', 'No records found')) ?>
          </td>
        </tr>
      <?php 
        else: 
            foreach ($list_dataKetuaJabatan as $i => $row):  
                $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
      ?>
        <tr>
            <td class="col-bil text-center"><?= $i + 1 ?></td>         
            <td><?= h($row['f_stafID'] ?? '') ?></td>
            <td><?= h($row['f_nama'] ?? '') ?></td>
            <td><?= h($row['f_namajabatan'] ?? '') ?></td>
            <!-- <td><?= h($row['f_telefon_pej'] ?? '') ?></td> -->
            <td><?= h($row['f_handphone'] ?? '') ?></td>
            <td><?= h($row['f_email'] ?? '') ?></td>
            <td>
              <button type="button" 
                      class="btn btn-sm btn-icon btn-outline-danger" 
                      id="btnHapus" 
                      onclick="deleteFunc('<?= h($row['f_stafID']) ?>')"
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

<?php if (isset($_SESSION['flash_alert'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: '<?= $_SESSION['flash_alert']['icon'] ?>',
            title: '<?= $_SESSION['flash_alert']['title'] ?>',
            text: '<?= $_SESSION['flash_alert']['message'] ?>',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });
</script>
<?php 
    // PENTING: Hapuskan session selepas digunakan supaya alert tak keluar lagi bila page di-refresh
    unset($_SESSION['flash_alert']); 
endif; 
?>