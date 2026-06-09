<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('senarai_permohonan','Senarai Permohonan')) ?></h5>
  </div>
  <div class="table-responsive dt-standard">
    <table id="pingatGraduanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th width="40"></th>
        <th class="col-bil text-center">No</th>
        <th class="small w-30"><?= h(tr('nama', 'Nama')) ?></th>
        <th class="small w-15"><?= h(tr('matrik', 'Matrik')) ?></th>
        <th class="small w-12"><?= h(tr('tarikh_permohonan', 'Tarikh Permohonan')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('status', 'Status')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        foreach ($list_pingatGraduan as $i => $row):  
            $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
      ?>
        <tr  data-id="<?= $row['id'] ?>" data-row='<?= $rowJson ?>' >
            <td class="details-control text-center">
                <i class="ri-add-circle-fill me-2 text-info" title="<?= h(tr('detail', 'Maklumat Terperinci')) ?>"></i>
            </td>            

            <td class="col-bil text-center"></td> 

            <!-- Nama -->
            <td> <?= h($row['student_name'] ?? '') ?> </td>

            <!-- Matrik -->
            <td> <?= h($row['matric_no'] ?? '') ?> </td>

            <!-- Tarikh Permohonan -->
            <td> <?= h(date('d-m-Y', strtotime($row['submitted_at'] ?? ''))) ?> </td>
        
            <!-- Status -->
            <td class="text-center">
                <span class="badge bg-info">
                    <?= h($row['status_name'] ?? '') ?>
                </span>    
            </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
