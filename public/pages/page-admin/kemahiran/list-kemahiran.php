<?php
require_once __DIR__ . '/../../../controllers/KemahiranController.php';

$controller = new KemahiranController();
$data = $controller->getHalamanData();

if ($controller->getErrorMessage()) {
    echo "Ralat: " . $controller->getErrorMessage();
}

?>
<div class="konvo-tab-card p-3 mb-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="icares-address-panel-header">
      <h5 class="text-h5 fw-bold text-primary m-0">
        <?= h(tr('PANEL-KEMAHIRAN','Senarai Kemahiran')) ?>
      </h5>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-info rounded-3" type="button" name="btnTambah" id="btnTambah" 
                data-bs-toggle="modal" data-bs-target="#tambah" 
                data-bs-container="body"
                title="<?= h(tr('TTP-TAMBAH-KEMAHIRAN', 'Tambah Kemahiran')) ?>">
            <i class="ri-add-line"></i> <?= h(tr('tambah', 'Tambah')) ?>
        </button>     
    </div>    
  </div>

  <div class="w-100 mt-3">
    <table id="dataSkillDT" class="table table-sm table-bordered align-middle table-hover w-100">
    <thead>
      <tr>
        <th width="5%" class="col-bil text-center"><?= h(tr('COL-BIL', 'No')) ?></th>
        <th class="small"><?= h(tr('COL-KEMAHIRAN', 'Kemahiran')) ?></th>
        <th width="10%" class="small text-center"><?= h(tr('COL-TARIKH-KEMASKINI', 'Tarikh Kemaskini')) ?></th>
        <th width="10%" class="small text-center"><?= h(tr('COL-ACTION', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        $list_dataSkill = $data['list_skill'] ?? [];

        if (empty($list_dataSkill)): 
      ?>
        <tr>
          <td colspan="4" class="text-center text-muted py-4">
            <?= h(tr('no_records', 'No records found')) ?>
          </td>
        </tr>
      <?php 
        else: 
            foreach ($list_dataSkill as $i => $row):  
                $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 

                $idKemahiran = $row['id_kemahiran'] ?? '';
                $tarikhkemaskini = date("d-M-Y", strtotime($row["created_date"]));
                if ($row["updated_date"] != NULL)
                    $tarikhkemaskini = date("d-M-Y", strtotime($row["updated_date"]));                
      ?>
        <tr>
            <td class="col-bil text-center"><?= $i + 1 ?></td>         
            <td class="fw-bold text-primary"><?= h($row['kemahiran'] ?? '') ?></td>
            <td class="text-center"><?= h($tarikhkemaskini ?? '') ?></td>   
            <td align="center">    
              <button type="button" 
                      class="btn btn-sm btn-link text-primary p-0"   
                      id="btnKemaskini" 
                      data-bs-toggle="modal" 
                      data-bs-target="#kemaskini" 
                      data-idSkill="<?= $idKemahiran ?>"   
                      data-skill="<?= $row['kemahiran'] ?>"  
                      title="<?= h($lang['TTP-KEMASKINI'] ?? 'Kemaskini') ?>">
                  <i class="ri-edit-line"></i>
              </button>

              <button type="button" 
                      class="btn btn-sm btn-link text-danger p-0"
                      id="btnHapus" 
                      onclick="deleteFunc(<?= h($idKemahiran) ?>)"
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