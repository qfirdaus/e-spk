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
        $lookupJawatan = $lookupAll['jawatan'] ?? [];
        $lookupKategoriPerjawatan = $lookupAll['kategori_perjawatan'] ?? [];

        foreach ($jawatanData as $i => $row): 
          $peringkat = $row['peringkat'] ?? null;
          $kategori_aktiviti = $row['kategori_aktiviti'] ?? null;
          $kod_kategori_aktiviti = $row['kod_kategori_aktiviti'] ?? null;
          $jawatan = $row['jawatan'] ?? null;
          $id_jawatan = $row['id_jawatan'] ?? null;
          $sumber = $row['sumber'] ?? 'Tambahan';   
          $sumberLabel = $sumber === 'ISTAD'
              ? tr('istar_source_istad', 'ISTAD')
              : tr('istar_source_additional', 'Tambahan');

      ?>
        <?php $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>

        <tr  data-id="<?= $row['id'] ?>" data-type="<?= $row['sumber'] ?>" >
          <td class="col-bil text-center"></td>
          <td class="text-center">
              <span class="badge <?php echo $sumber === 'ISTAD' ? 'bg-darkgreen' : 'bg-salmon'; ?>">
                  <?= h($sumberLabel) ?>
              </span>
          </td>     

          <!-- Nama Badan Pelajar -->
          <td class="text-start" data-field="nama_bp_program">
          <?php if ($sumber === 'Tambahan'): ?>
              <input type="text"
                    name="nama_bp_program"
                    class="form-control form-control-sm"
                    value="<?= h($row['nama_bp_program'] ?? '') ?>">
          <?php else: ?>
              <?= h($row['nama_bp_program'] ?? '-') ?>
          <?php endif; ?>
          </td>

          <!-- Kategori Perjawatan -->
          <td data-field="kategori_aktiviti">
          <?php if ($sumber === 'Tambahan'): ?>
                <select name="kod_kategori_aktiviti" class="form-select kategori-select" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupKategoriPerjawatan as $opt): ?>
                        <option value="<?= h($opt['kod_kategori_aktiviti']) ?>"
                            data-idaktiviti="<?= h($opt['id']) ?>"
                            data-kategori_aktiviti="<?= h($opt['kategori_aktiviti']) ?>" 
                            <?= $kod_kategori_aktiviti == $opt['kod_kategori_aktiviti'] ? 'selected' : '' ?> >
                            <?= h(strtoupper($opt['kategori_aktiviti'])) ?>
                        </option>                    
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="kategori_aktiviti" class="form-control aktiviti-Text" value="<?= h($kategori_aktiviti ?? '') ?>" >

          <?php else: ?>
              <input type="hidden" name="kod_kategori_aktiviti" class="form-control" value="<?= h($kod_kategori_aktiviti ?? '') ?>" >   
              <?= h($kategori_aktiviti ?? '-') ?>
          <?php endif; ?>
          </td>

          <!-- Tarikh Lantikan -->
          <td data-field="tarikh_lantikan">
          <?php if ($sumber === 'Tambahan'): ?>
              <input type="text"
                    name="tarikh_lantikan"
                    class="form-control form-control-sm datepicker"
                    placeholder="dd/mm/yyyy"
                    value="<?= !empty($row['tarikh_lantikan'])
                          ? h(date('d/m/Y', strtotime($row['tarikh_lantikan'])))
                          : '' ?>">
          <?php else: ?>
              <?= !empty($row['tarikh_lantikan'])
                  ? h(date('d/m/Y', strtotime($row['tarikh_lantikan'])))
                  : '-' ?>
          <?php endif; ?>
          </td>

          <!-- Jawatan -->
          <td data-field="jawatan">
          <?php if ($sumber === 'Tambahan'): ?>
                <select name="id_jawatan" class="form-select jawatan-select" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php 
                        foreach ($lookupJawatan as $opt):                        
                    ?>     
                            <option value="<?= h($opt['id_jawatan']) ?>"
                                data-bp="<?= h(strtoupper($opt['keteranganBP'])) ?>"
                                data-default="<?= h(strtoupper($opt['keterangan'])) ?>"
                                <?= $id_jawatan == $opt['id_jawatan'] ? 'selected' : '' ?> >

                            <?= ($kod_kategori_aktiviti == 'BP')
                                ? h(strtoupper($opt['keteranganBP']))
                                : h(strtoupper($opt['keterangan'])) ?>

                            </option>                                      
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="jawatan" class="form-control jawatan-text" value="<?= h($jawatan ?? '') ?>" >

          <?php else: ?>
              <input type="hidden" name="id_jawatan" class="form-control" value="<?= h($id_jawatan ?? '') ?>" >            
              <?= h($jawatan ?? '-') ?>
          <?php endif; ?>
          </td>          

          <!-- Peringkat -->
          <td data-field="peringkat">
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
                  data-id="<?= h($row['id']) ?>" 
                  data-path="<?= $row['dokumen']['path'] ?>"
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
                      data-tab="jawatanDisandang"
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
