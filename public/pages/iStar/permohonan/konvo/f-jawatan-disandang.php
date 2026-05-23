<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('profile_senarai_jawatan_disandang','Senarai Jawatan Disandang')) ?></h5>
  </div>
  <div class="table-responsive dt-standard">
    <table id="jawatanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center"><?= h(tr('senarai_no', 'No.')) ?></th>
        <th class="w-10 text-center"></th>
        <th class="small w-30"><?= h(tr('nama_badan_pelajar_program', 'Nama Badan Pelajar / Program')) ?></th>
        <th class="small w-18"><?= h(tr('kategori_perjawatan', 'Kategori Perjawatan')) ?></th>
        <th class="small w-14"><?= h(tr('profile_tarikh_lantikan', 'Tarikh Lantikan')) ?></th>
        <th class="small w-12"><?= h(tr('jawatan', 'Jawatan')) ?></th>
        <th class="small w-12"><?= h(tr('peringkat', 'Peringkat')) ?></th>
        <th class="small text-center" style="width:130px;"><?= h(tr('tindakan', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>
      <?php 
        $lookupPeringkat = $lookupAll['peringkat'] ?? [];

        foreach ($jawatanData as $i => $row): 
          $peringkat = $row['peringkat'] ?? null;
          $sumber = $row['sumber'] ?? 'Tambahan';   
          $sumberLabel = $sumber === 'IStAD'
              ? tr('istar_source_istad', 'IStAD')
              : tr('istar_source_additional', 'Tambahan');
      ?>
        <?php $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>

        <tr  data-id="<?= $row['id'] ?>" data-type="<?= $row['sumber'] ?>" >
          <td class="col-bil text-center"></td>
          <td class="text-center">
              <span class="badge <?php echo $sumber === 'IStAD' ? 'bg-darkgreen' : 'bg-salmon'; ?>">
                  <?= h($sumberLabel) ?>
              </span>
          </td>          
          <td class="text-start">
            <?= h($row['nama_bp_program'] ?? '-') ?>
            <!-- <span class="access-chip is-allowed truncate-1line" data-bs-toggle="tooltip" data-bs-custom-class="template-tooltip" data-bs-original-title="IStAD">IStAD</span> -->
          </td>
          <td align="left"><?= h($row['kategori_aktiviti'] ?? '-') ?></td>
          <td>
            <?= !empty($row['tarikh_lantikan'])
              ? h(date('d/m/Y', strtotime($row['tarikh_lantikan'])))
              : '-' ?>
          </td>
          <td><?= h($row['jawatan'] ?? '-') ?></td>
          <td>
              <select name="peringkat" class="form-select form-select-sm">
                  <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                  <?php foreach ($lookupPeringkat as $opt): ?>
                  <option value="<?= h($opt['peringkat_code']) ?>"
                      <?= $peringkat == $opt['peringkat_code'] ? 'selected' : '' ?>>
                      <?= h(strtoupper($opt['peringkat_my'])) ?>
                  </option>
                  <?php endforeach; ?>
              </select>         
          </td>

          <!-- TINDAKAN -->
          <td>
              <?php 
              if (
                  ($row['sumber'] ?? '') === 'Tambahan'
                  && !empty($row['dokumen']['path'])
              ):  ?>

                  <a href="<?= base_url($row['dokumen']['path']) ?>"
                  target="_blank"
                  class="btn btn-sm btn-outline-warning rounded-3"
                  title="<?= h(tr('lihat_dokumen', 'Lihat Dokumen Sokongan')) ?>">
                      <i class="ri-eye-line"></i>
                  </a>                     
                  <button type="button"
                          class="btn btn-sm btn-outline-info rounded-3 upload-btn"
                          title="<?= h(tr('kemaskini_dokumen', 'Kemaskini Dokumen Sokongan')) ?>"
                          data-id="<?= h($row['id']) ?>">
                      <i class="bi bi-upload"></i>
                  </button>

                  <input type="file"
                      class="dokumen-inline d-none"
                      data-id="<?= h($row['id']) ?>"
                      data-url="pages/iStar/permohonan/konvo/ajax/jawatan.php?action=updateDokumenJawatan"
                      accept=".pdf,.jpg,.jpeg">
              <?php else: ?>
                  -
              <?php endif; ?>

              <?php if (($row['sumber'] ?? '') === 'Tambahan'): ?>
                  <button type="button"
                          class="btn btn-sm btn-outline-danger rounded-3 btn-delete-jawatan"
                          title = "<?= h(tr('delete', 'Hapus Rekod')) ?>"
                          data-id="<?= h($row['id']) ?>">
                      <i class="ri-delete-bin-line"></i>
                  </button>
              <?php endif; ?>                 
          </td>  
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
