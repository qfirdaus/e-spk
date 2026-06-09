<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('senarai_tarikh_permohonan','Senarai Tarikh Permohonan')) ?></h5>
  </div>
  <div class="table-responsive dt-standard">
    <table id="dateConfigDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center">No</th>
        <th class="small w-30"><?= h(tr('nama', 'Kategori Anugerah')) ?></th>
        <th class="small w-15"><?= h(tr('sesi_permohonan', 'Sesi Permohonan')) ?></th>
        <th class="small w-12"><?= h(tr('tarikh_mula', 'Tarikh Mula')) ?></th>
        <th class="small w-12"><?= h(tr('tarikh_tamat', 'Tarikh Tamat')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('status', 'Status')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        foreach ($list_dateConfig as $i => $row):  
            $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
      ?>
        <tr  data-id="<?= $row['id'] ?>" data-row='<?= $rowJson ?>' >

            <td class="col-bil text-center"></td>         

            <!-- Kategori Award -->
            <td> <?= h($row['award_desc'] ?? '') ?> </td>

            <!-- Nama Sesi -->
            <td> <?= h($row['config_name'] ?? '') ?> </td>

            <!-- Tarikh Mula -->
            <td> <?= h(date('d-m-Y', strtotime($row['start_date'] ?? ''))) ?> </td>

            <!-- Tarikh Tamat -->
            <td> <?= h(date('d-m-Y', strtotime($row['end_date'] ?? ''))) ?> </td>

            <!-- Status -->
            <td class="text-center">
                
                <span class="badge <?php if ($row['is_active'] == 1) { echo "bg-info"; } else { echo "bg-danger"; } ?> ">
                    <?= h($row['is_active_status'] ?? '') ?>
                </span>    
            </td>

            <!-- Tindakan -->
            <td>    
                <button type="button" name="dateConfigBtnUpdate"
                        class="btn btn-sm btn-outline-secondary rounded-3 btn-update-dateConfig" 
                        title = "<?= h(tr('update', 'Kemaskini Rekod')) ?>"
                        data-id="<?= $row['id'] ?>">
                    <i class="ri-pencil-line"></i>
                </button>   

                <button type="button"
                        class="btn btn-sm btn-outline-danger rounded-3 btn-delete-dateConfig"
                        title = "<?= h(tr('delete', 'Hapus Rekod')) ?>"
                        data-id="<?= $row['id'] ?>">
                    <i class="ri-delete-bin-line"></i>
                </button>              
            </td>           
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
