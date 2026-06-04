<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('profile_senarai_anugerah_pengiktirafan','Senarai Anugerah dan Pengiktirafan')) ?></h5>
  </div>
  <div class="table-responsive dt-standard">
    <table id="anugerahDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center"><?= h(tr('senarai_no', 'No.')) ?></th>
        <th class="small w-30"><?= h(tr('nama_anugerah_pengiktirafan', 'Nama Anugerah / Pengiktirafan')) ?></th>
        <th class="small w-10"><?= h(tr('tahun', 'Tahun')) ?></th>
        <th class="small w-18"><?= h(tr('kurniaan_pemberian', 'Kurniaan / Pemberian')) ?></th>
        <th class="small w-12"><?= h(tr('peringkat', 'Peringkat')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>
      <?php 
        $lookupPeringkat = $lookupAll['peringkat'] ?? [];

        foreach ($anugerahData as $i => $row): 
          $peringkat = $row['peringkat'] ?? null;
          $tahun = $row['tahun'] ?? null;

      ?>
        <?php $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>

        <tr  data-id="<?= $row['id'] ?>" >
          <td class="col-bil text-center"></td> 

          <!-- Nama Anugerah -->
          <td>
              <input type="text"
                  name="nama_anugerah"
                  class="form-control form-control-sm"
                  value="<?= h($row['nama_anugerah'] ?? '') ?>">
          </td>

          <!-- Tahun -->
          <td>
              <select name="tahun" class="form-select" required>
                <option value=""><?= h(tr('sila_pilih','Sila Pilih')) ?></option>

                <?php
                  $currentYear = date('Y');
                  $startYear = $currentYear - 15;  
                  $endYear = $currentYear;      

                  for ($y = $endYear; $y >= $startYear; $y--):
                ?>
                    <option value="<?= $y ?>"
                      <?= $tahun == $y ? 'selected' : '' ?> >
                      <?= $y ?>
                    </option>
                <?php endfor; ?>
              </select>                  
          </td>

          <!-- Kurniaan / Pemberian -->
          <td>
              <input type="text"
                  name="kurniaan_pemberian"
                  class="form-control form-control-sm"
                  value="<?= h($row['kurniaan_pemberian'] ?? '') ?>">              
          </td>

          <!-- Peringkat -->
          <td>
              <select name="peringkat" class="form-select form-select-sm">
                  <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                  <?php foreach ($lookupPeringkat as $opt): ?>
                  <option value="<?= h($opt['peringkat_code']) ?>"
                      <?= $peringkat == $opt['peringkat_code'] ? 'selected' : '' ?> >
                      <?= h(strtoupper($opt['peringkat_my'])) ?>
                  </option>
                  <?php endforeach; ?>
              </select>         
          </td>

          <!-- TINDAKAN -->
          <td>
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
                  data-url="pages/iStar/permohonan/konvo/ajax/anugerah.php?action=updateDokumenAnugerah"
                  accept=".pdf,.jpg,.jpeg">

              <button type="button"
                      class="btn btn-sm btn-outline-danger rounded-3 btn-delete-anugerah"
                      title = "<?= h(tr('delete', 'Hapus Rekod')) ?>"
                      data-id="<?= h($row['id']) ?>">
                  <i class="ri-delete-bin-line"></i>
              </button>              
          </td>  
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
