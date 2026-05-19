<div class="icares-address-panel-header">
  <h5 class="text-h5"><?= h(tr('profile_senarai_jawatan_disandang','Senarai Jawatan Disandang')) ?></h5>
</div>
<div class="table-responsive dt-standard">
  <table id="jawatanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil"><?= h(tr('senarai_no', 'No.')) ?></th>
        <th class="small w-25">Nama Badan Pelajar / Program </th>
        <th class="small">Tarikh Lantikan</th>
        <th class="small">Jawatan</th>
        <th class="small">Peringkat</th>
        <th class="small text-center">Tindakan</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($jawatanData as $row): ?>
        <?php $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>

        <tr>
          <td class="col-bil"></td>
          <td align="left">
            <?= h($row['nama_bp_program'] ?? '-') ?>
            <!-- <span class="access-chip is-allowed truncate-1line" data-bs-toggle="tooltip" data-bs-custom-class="template-tooltip" data-bs-original-title="IStAD">IStAD</span> -->
          </td>
          <td>
            <?= !empty($row['tarikh_mula'])
              ? h(date('d/m/Y', strtotime($row['tarikh_mula'])))
              : '-' ?>
          </td>
          <td><?= h($row['jawatan'] ?? '-') ?></td>
          <td><?= h($row['peringkat'] ?? '-') ?></td>

          <td class="text-center">
            <div class="d-flex justify-content-center gap-1 flex-nowrap">
              <button class="btn btn-sm btn-outline-warning js-view-row" data-row='<?= $rowJson ?>'>
                <i class="ri-eye-line"></i>
              </button>

              <button class="btn btn-sm btn-outline-primary js-edit-row" data-row='<?= $rowJson ?>'>
                <i class="ri-pencil-line"></i>
              </button>

              <button class="btn btn-sm btn-outline-danger js-delete-row" data-row='<?= $rowJson ?>'>
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>     


                