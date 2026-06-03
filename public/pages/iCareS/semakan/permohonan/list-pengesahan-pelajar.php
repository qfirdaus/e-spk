<div class="semakan-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('senarai_pengesahan_pelajar','Senarai Pengesahan Pelajar')) ?></h5>
  </div>
  <div class="table-responsive dt-standard">
    <table id="pengesahanPelajarDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center"><?= h(tr('bil_no', 'No.')) ?></th>
        <th class="small w-35"><?= h(tr('nama', 'Nama')) ?></th>
        <th class="small w-15"><?= h(tr('matrik', 'Matrik')) ?></th>
        <th class="small w-12"><?= h(tr('program', 'Program')) ?></th>
        <th class="small w-12"><?= h(tr('fakulti', 'Fakulti')) ?></th>
        <th class="small w-16"><?= h(tr('tarikh_permohonan', 'Tarikh Permohonan')) ?></th>
        <th class="small text-center" style="width:130px;"><?= h(tr('status', 'Status')) ?></th>
      </tr>
    </thead>

    <tbody>
    <?php 
        foreach ($list_pengesahanPelajar as $row):
    ?>
            <tr  data-id="<?= $row['id'] ?>" data-matrik="<?= $row['no_matrik'] ?>" >
                <td class="col-bil text-center"></td>
                <td class="text-start">
                    <?= h($row['nama_pemohon']) ?>
                </td>

                <td>
                    <?= h($row['no_matrik']) ?>
                </td>
                <td>
                    <?= h($row['program']) ?>
                </td>
                <td>
                    <?= h($row['fakulti']) ?>
                </td>
                <td>
                    <?= h(date('d-m-Y', strtotime($row['submitted_at']))) ?>
                </td>
                <td>
                    <span class="badge bg-info">
                        Baharu
                    </span>                    
                </td>   
            </tr>
    <?php endforeach; ?>
    </tbody>
  </table>  </div></div>
