<?php
  $lookupSesi = $lookupAll['list_sesikemasukan'] ?? [];
?>
<div class="icares-address-content">
    <div class="tab-pane show active">
      <div class="icares-address-panel-header">
        <h5><?= h(tr('carian', 'Carian')) ?></h5>
      </div>

      <form id="form-akaun" method="post" enctype="multipart/form-data">
        <div class="row">
          <div class="col-12">
            <div class="row">
              <div class="col-md-6 gx-4">

                <!-- Peringkat Pengajian -->
                <div class="mb-2 row align-items-center">
                  <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('peringkat_pengajian','Peringkat Pengajian')) ?></label>
                  <div class="col-sm-8">
                    <select name="selectPengajian" class="form-select form-select-sm select2">
                        <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                        <?php foreach ($lookupPeringkat as $opt): ?>
                        <option value="<?= h($opt['peringkat_code']) ?>"
                            <?= h($dataAkaun['peringkat_code'] ?? '') == $opt['peringkat_code'] ? 'selected' : '' ?>>
                            <?= h(strtoupper($opt['peringkat_name'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>  
                  </div>                 
                </div>   

                <!-- Sesi Kemasukan -->
                <div class="mb-2 row align-items-center">
                  <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('sesi_kemasukan','Sesi Kemasukan')) ?></label>
                  <div class="col-sm-8">
                    <select name="sesi_kemasukan" class="form-select form-select-sm select2">
                        <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                        <?php foreach ($lookupSesi as $opt): ?>
                        <option value="<?= h($opt['f005term']) ?>" >
                            <?= h($opt['f005term']) ?> - <?= h(strtoupper($opt['semester'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>  
                  </div>                 
                </div>                                  

              </div>

            </div>
          </div>
        </div>
      </form>
    </div>
</div> 
<br><br>
<div class="konvo-tab-card p-3 mb-4">
  <div class="icares-address-panel-header">
    <h5 class="text-h5"><?= h(tr('senarai_plo','Senarai PLO')) ?></h5>
  </div>
  <div class="table-responsive dt-standard">
    <table id="dataPLODT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil text-center">No</th>
        <th class="small w-30"><?= h(tr('kod_plo', 'Kod PLO')) ?></th>
        <th class="small w-15"><?= h(tr('keterangan_plo', 'Keterangan PLO')) ?></th>
        <th class="small w-12"><?= h(tr('kod_mqf', 'Kod MQF')) ?></th>
        <th class="small w-12"><?= h(tr('senarai_peo', 'Senarai PEO')) ?></th>
        <th class="small w-12"><?= h(tr('senarai_clo', 'Senarai CLO')) ?></th>
        <th class="small w-20 text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>     
      <?php 
        foreach ($list_dataPLO as $i => $row):  
            $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); 
      ?>
        <tr  data-id="<?= $row['id_plo'] ?>" data-row='<?= $rowJson ?>' >

            <td class="col-bil text-center"></td>         

            <!-- Kod PLO -->
            <td> <?= h($row['kod_plo'] ?? '') ?> </td>

            <!-- Keterangan PLO -->
            <td> <?= h($row['keterangan_bm'] ?? '') ?> </td>

            <!-- Kod MQF -->
            <td> <?= h($row['kod_mqf'] ?? '') ?>  </td>

            <!-- Senarai PEO -->
            <td> <?= h($row['senarai_peo'] ?? '') ?>  </td>

            <!-- Senarai CLO -->
            <td> <?= h($row['senarai_clo'] ?? '') ?>  </td>

            <!-- Tindakan -->
            <td>    
                <?php 
                    $today = date('Y-m-d');
                    $endDate = !empty($row['end_date']) ? date('Y-m-d', strtotime($row['end_date'])) : '';
                    
                    // check jika hari ini dah LEPAS tarikh tamat
                    $isExpired = ($endDate && $today > $endDate) ? 'disabled' : '';
                ?>

                <!-- <button type="button" name="dateConfigBtnUpdate"
                        class="btn btn-sm btn-outline-secondary rounded-3 btn-update-dateConfig" 
                        title = "<?= h(tr('update', 'Kemaskini Rekod')) ?>"
                        data-id="<?= $row['id'] ?>">
                    <i class="ri-pencil-line"></i>
                </button>   

                <button type="button"
                        class="btn btn-sm btn-outline-warning rounded-3 btn-override-dateConfig"
                        title = "<?= h(tr('override', 'Override')) ?>"
                        data-id="<?= $row['id'] ?>" > 
                        <i class="ri-toggle-line"></i>
                </button>  

                <button type="button"
                        class="btn btn-sm btn-outline-danger rounded-3 btn-delete-dateConfig"
                        title = "<?= h(tr('delete', 'Hapus Rekod')) ?>"
                        data-id="<?= $row['id'] ?>"
                        <?= $isExpired ?> > 
                        <i class="ri-delete-bin-line"></i>
                </button>           -->
            </td>           
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
