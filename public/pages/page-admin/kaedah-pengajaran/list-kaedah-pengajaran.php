<?php
require_once __DIR__ . '/../../../controllers/KaedahPengajaranController.php';

$controller = new KaedahPengajaranController();
$data = $controller->getHalamanData();

if ($controller->getErrorMessage()) {
    echo "Ralat: " . $controller->getErrorMessage();
}

?>
<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('PANEL-KAEDAH-PENGAJARAN','Senarai Kaedah Pengajaran')) ?></h5>

    <div class="list-actions" style="float: right; margin-bottom:10px;">
        <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                data-bs-toggle="modal" data-bs-target="#tambah" 
                data-bs-container="body"
                title="<?= h(tr('TTP-TAMBAH-KAEDAH-PENGAJARAN', 'Tambah Kaedah Pengajaran')) ?>">
            <i class="ri-add-line"></i>
        </button>     
    </div>    
  </div>
  <div class="table-responsive dt-standard">
    <table id="dataLocDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center"><?= h(tr('COL-BIL', 'No')) ?></th>
        <th class="small w-30"><?= h(tr('COL-KAEDAH-PENGAJARAN', 'Kaedah Pengajaran')) ?></th>
        <th class="small w-15"><?= h(tr('COL-TARIKH-KEMASKINI', 'Tarikh Kemaskini')) ?></th>
        <th class="small w-20 text-center"></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        $list_dataTeachingMethod= $data['list_teaching_method'] ?? [];

        if (empty($list_dataTeachingMethod)): 
      ?>
        <tr>
          <td colspan="4" class="text-center text-muted py-4">
            <?= h(tr('no_records', 'No records found')) ?>
          </td>
        </tr>
      <?php 
        else: 
            foreach ($list_dataTeachingMethod as $i => $row):  
                $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 

                $idKaedahPengajaran = $row['id_kaedah_pengajaran'] ?? '';
                $tarikhkemaskini = date("d-M-Y", strtotime($row["created_date"]));
                if ($row["updated_date"] != NULL)
                    $tarikhkemaskini = date("d-M-Y", strtotime($row["updated_date"]));                
      ?>
        <tr>
            <td class="col-bil text-center"><?= $i + 1 ?></td>         
            <td><?= h($row['kaedah_pengajaran'] ?? '') ?></td>
            <td><?= h($tarikhkemaskini ?? '') ?></td>   
            <td align="center">    
              <button type="button" 
                      class="btn btn-sm btn-icon btn-outline-success me-1" 
                      id="btnKemaskini" 
                      data-bs-toggle="modal" 
                      data-bs-target="#kemaskini" 
                      data-idTeachMethod="<?= $idKaedahPengajaran ?>"   
                      data-teachMethod="<?= $row['kaedah_pengajaran'] ?>"  
                      title="<?= h($lang['TTP-KEMASKINI'] ?? 'Kemaskini') ?>">
                  <i class="ri-edit-line"></i>
              </button>

              <button type="button" 
                      class="btn btn-sm btn-icon btn-outline-danger" 
                      id="btnHapus" 
                      onclick="deleteFunc(<?= h($idKaedahPengajaran) ?>)"
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