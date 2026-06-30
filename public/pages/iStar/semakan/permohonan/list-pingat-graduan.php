<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('senarai_permohonan','Senarai Permohonan')) ?></h5>
  </div>
  <!-- <div class="table-responsive dt-standard">
    <table id="pingatGraduanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th width="40"></th>
        <th class="col-bil text-center">No</th>
        <th class="small w-30"><?= h(tr('nama', 'Nama')) ?></th>
        <th class="small w-15"><?= h(tr('matrik', 'Matrik')) ?></th>
        <th class="small w-12"><?= h(tr('tarikh_permohonan', 'Tarikh Permohonan')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('status', 'Status')) ?></th>
        <th class="small w-10 text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th> </tr>
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
            <td> <?= h($row['student_name'] ?? '') ?> </td>
            <td> <?= h($row['matric_no'] ?? '') ?> </td>
            <td> <?= h(date('d-m-Y', strtotime($row['submitted_at'] ?? ''))) ?> </td>
            <td class="text-center">
                <span class="badge bg-info">
                    <?= h($row['status_name'] ?? '') ?>
                </span>    
            </td>
            <td class="text-center">
                <a href="cetak_permohonan.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-danger" title="Lihat PDF">
                    <i class="ri-file-pdf-line"></i> PDF
                </a>
            </td>            
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div> -->

    <table id="pingatGraduanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center">No</th>
        <th class="small w-30"><?= h(tr('nama', 'Nama')) ?></th>
        <th class="small w-15"><?= h(tr('matrik', 'Matrik')) ?></th>
        <th class="small w-12"><?= h(tr('tarikh_permohonan', 'Tarikh Permohonan')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('status', 'Status')) ?></th>
        <th class="small w-10 text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th> </tr>
      </tr>
    </thead>

    <tbody>     
      <?php 
        foreach ($list_pingatGraduan as $i => $row):  
            $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
      ?>
        <tr>
            <td class="col-bil text-center"></td> 
            <td> <?= h($row['student_name'] ?? '') ?> </td>
            <td> <?= h($row['matric_no'] ?? '') ?> </td>
            <td> <?= h(date('d-m-Y', strtotime($row['submitted_at'] ?? ''))) ?> </td>
            <td class="text-center">
                <span class="badge bg-info">
                    <?= h($row['status_name'] ?? '') ?>
                </span>    
            </td>
            <td class="text-center">
                <a href="cetak_permohonan.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-warning rounded-3" title="<?= h(tr('lihat_permohonan', 'Lihat Permohonan')) ?>">
                    <i class="ri-eye-line"></i>
                </a>                   
            </td>            
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>  
</div>
