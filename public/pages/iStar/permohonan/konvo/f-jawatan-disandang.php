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
          $id_kategori_aktiviti = $row['id_kategori_aktiviti'] ?? null;
          $kod_kategori_aktiviti = $row['kod_kategori_aktiviti'] ?? null;
          $jawatan = $row['jawatan'] ?? null;
          $id_jawatan = $row['id_jawatan'] ?? null;
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

          <!-- Nama Badan Pelajar -->
          <td class="text-start">
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
          <td>
          <?php if ($sumber === 'Tambahan'): ?>
                <select name="kod_kategori_aktiviti" id="kategoriAktiviti" class="form-select kategori-select" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupKategoriPerjawatan as $opt): ?>
                        <option value="<?= h($opt['kod_kategori_aktiviti']) ?>"
                            data-idaktiviti="<?= h($opt['id']) ?>"
                            data-aktiviti_text="<?= h($opt['kategori_aktiviti']) ?>" 
                            <?= $kod_kategori_aktiviti == $opt['kod_kategori_aktiviti'] ? 'selected' : '' ?> >
                            <?= h(strtoupper($opt['kategori_aktiviti'])) ?>
                        </option>                    
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="id_kategori_aktiviti" class="form-control id-Aktiviti">
                <input type="hidden" name="kategori_aktiviti" class="form-control aktiviti-Text">                
          <?php else: ?>
              <?= h($kategori_aktiviti ?? '-') ?>
          <?php endif; ?>
          </td>

          <!-- Tarikh Lantikan -->
          <td>
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
          <td>
          <?php if ($sumber === 'Tambahan'): ?>
                <select name="id_jawatan" class="form-select jawatan-select" required>
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php 
                        foreach ($lookupJawatan as $opt): 
                        if (h(strtoupper($opt['keteranganBP'])) != ''):  $str = ' / '; 
                        else: $str = ''; 
                        endif;                           
                    ?>
                        <option value="<?= h($opt['id_jawatan']) ?>"
                                data-jawatan_text="<?= h(strtoupper($opt['keterangan']))  . $str . h(strtoupper($opt['keteranganBP'])) ?>" 
                            <?= $id_jawatan == $opt['id_jawatan'] ? 'selected' : '' ?> >
                            <?= h(strtoupper($opt['keterangan'])) ?>
                        </option>                    
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="jawatan" id="jawatanText" class="form-control jawatan-text">
          <?php else: ?>
              <?= h($jawatan ?? '-') ?>
          <?php endif; ?>
          </td>          

          <!-- Peringkat -->
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
                  data-id="<?= h($row['id']) ?>" 
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
