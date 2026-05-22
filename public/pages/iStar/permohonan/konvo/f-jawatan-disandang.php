<div class="icares-address-panel-header">
  <h5 class="text-h5"><?= h(tr('profile_senarai_jawatan_disandang','Senarai Jawatan Disandang')) ?></h5>
</div>
<div class="table-responsive dt-standard p-3">
  <table id="jawatanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil"><?= h(tr('senarai_no', 'No.')) ?></th>
        <th></th>
        <th class="small w-25">Nama Badan Pelajar / Program </th>
        <th class="small"><?= h(tr('kategori_perjawatan', 'Kategori Perjawatan')) ?></th>
        <th class="small">Tarikh Lantikan</th>
        <th class="small">Jawatan</th>
        <th class="small">Peringkat</th>
        <th class="small text-center">Tindakan</th>
      </tr>
    </thead>

    <tbody>
      <?php 
        $lookupPeringkat = $lookupAll['peringkat'] ?? [];

        foreach ($jawatanData as $i => $row): 
          $peringkat = $row['peringkat'] ?? null;
          $sumber = $row['sumber'] ?? 'Tambahan';   
      ?>
        <?php $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>

        <tr  data-id="<?= $row['id'] ?>" data-type="<?= $row['sumber'] ?>" >
          <td class="col-bil text-center"></td>
          <td>
              <span class="badge <?php echo $sumber === 'IStAD' ? 'bg-darkgreen' : 'bg-salmon'; ?>">
                  <?= h($sumber) ?>
              </span>
          </td>          
          <td align="left">
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
                  class="btn btn-sm btn-outline-warning"
                  title="<?= h(tr('lihat_dokumen', 'Lihat Dokumen Sokongan')) ?>">
                      <i class="ri-eye-line"></i>
                  </a>                     
                  <button type="button"
                          class="btn btn-sm btn-outline-info upload-btn"
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
                          class="btn btn-sm btn-outline-danger btn-delete-jawatan"
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
     