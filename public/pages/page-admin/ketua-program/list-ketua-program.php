<?php
require_once __DIR__ . '/../../../controllers/KetuaProgramController.php';

$controller = new KetuaProgramController();
$data = $controller->getHalamanData();

if ($controller->getErrorMessage()) {
    echo "Ralat: " . $controller->getErrorMessage();
}

?>
<div class="card border border-light-subtle shadow-none p-4 mb-4 rounded-3 bg-white">
  <div class="icares-address-panel-header mb-3">
      <h5 class="text-h5 fw-bold text-primary"><?= h(tr('carian', 'Carian')) ?></h5>
  </div>

  <form id="form-maklumat-plo" method="POST" action="">
    <div class="row gx-4 gy-2">
        <div class="col-md-8 col-lg-8">
        
          <div class="row align-items-center mb-3">
            <label class="col-sm-2 col-form-label text-nowrap">
              <?= h(tr('staf','Staf')) ?>
            </label>
            <div class="col-sm-8 search-box">
              <input type="text" class="form-control" id="txtstaf" placeholder="Sila masukkan No.Staf / Nama">
              <div class="search_result" style="background-color: white"></div>                  
            </div>                 
          </div>                                      

      </div>
    </div>
  </form>

  <hr class="my-4 text-muted">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="icares-address-panel-header">
        <h5 class="text-h5 fw-bold text-primary m-0">
          <?= h(tr('PANEL-SENARAI-KETUA-PROGRAM','Senarai Ketua Program')) ?>
        </h5>
    </div>
  </div>

  <div class="w-100 mt-3">
    <table id="dataKetuaProgramDT" class="table table-sm table-bordered align-middle table-hover w-100">
    <thead>
      <tr>
        <th width="5%" class="col-bil text-center"><?= h(tr('COL-BIL', 'No')) ?></th>
        <th width="10%" class="small text-center"><?= h(tr('COL-NO-STAF', 'No Staf')) ?></th>
        <th width="25%" class="small"><?= h(tr('COL-NAMA-STAF', 'Nama')) ?></th>
        <th width="20%" class="small"><?= h(tr('COL-JABATAN', 'Jabatan')) ?></th>
        <!-- <th width="15%" class="small"><?= h(tr('COL-TELEFON-PEJ', 'Telefon Pejabat')) ?></th> -->
        <th width="10%" class="small text-center"><?= h(tr('COL-NO-TELEFON', 'No. Telefon')) ?></th>
        <th width="20%" class="small"><?= h(tr('COL-EMEL', 'EMEL')) ?></th>
        <th width="10%" class="small text-center"><?= h(tr('COL-TINDAKAN', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        $list_dataKetuaProgram = $data['list_ketua_program'] ?? [];

        // Semak jika array kosong
        if (empty($list_dataKetuaProgram)): 
      ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">
            <?= h(tr('no_records', 'No records found')) ?>
          </td>
        </tr>
      <?php 
        else: 
            foreach ($list_dataKetuaProgram as $i => $row):  
                $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
      ?>
        <tr>
            <td class="col-bil text-center"><?= $i + 1 ?></td>         
            <td class="fw-bold text-primary"><?= h($row['f_stafID'] ?? '') ?></td>
            <td><?= h($row['f_nama'] ?? '') ?></td>
            <td><?= h($row['f_namajabatan'] ?? '') ?></td>
            <!-- <td><?= h($row['f_telefon_pej'] ?? '') ?></td> -->
            <td class="text-center"><?= h($row['f_handphone'] ?? '') ?></td>
            <td><?= h($row['f_email'] ?? '') ?></td>
            <td class="text-center">
              <button type="button" 
                      class="btn btn-sm btn-link text-danger p-0"
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
    unset($_SESSION['flash_alert']); 
endif; 
?>