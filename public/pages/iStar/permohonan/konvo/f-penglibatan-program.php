<div class="col-12 text-end mt-3">
  <button type="button" id="penglibatanBtnAdd" class="btn btn-success sync-groups-btn">
    <i class="ri-add-line"></i><span><?= h(tr('button_add_new', 'Tambah Baru')) ?></span>
  </button>                
</div>
<div class="icares-address-panel-header">
  <h5 class="text-h5"><?= h(tr('profile_senarai_penglibatan_program','Senarai Penglibatan Program')) ?></h5>
</div>
<hr>
<div class="table-responsive dt-standard p-3">
  <table id="penglibatanDT" class="table table-bordered align-middle w-100">
    <thead>
      <tr>
        <th class="col-bil"><?= h(tr('bil_no', 'No.')) ?></th>
        <th></th>
        <th class="small w-25"><?= h(tr('nama_program_pertandingan', 'Nama Program / Pertandingan')) ?></th>
        <th class="small"><?= h(tr('tarikh', 'Tarikh')) ?></th>
        <th class="small"><?= h(tr('wakil', 'Wakil')) ?></th>
        <th class="small"><?= h(tr('peringkat', 'Peringkat')) ?></th>
        <th class="small"><?= h(tr('pencapaian', 'Pencapaian')) ?></th>
        <!-- <th class="small text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th> -->
      </tr>
    </thead>

    <tbody>
    <?php 
        foreach ($penglibatanData as $i => $row): 
            $wakil     = $row['wakil'] ?? null;
            $peringkat = $row['peringkat'] ?? null;
            $pencapaian = $row['pencapaian'] ?? null;
            $sumber = $row['sumber'] ?? 'Tambahan';   
    ?>
            <tr  data-id="<?= $row['id'] ?>" data-type="<?= $row['sumber'] ?>" >

                <td class="col-bil text-center"></td>
                <td>
                    <span class="badge <?php echo $sumber === 'IStAD' ? 'bg-success' : 'bg-warning'; ?>">
                        <?= h($sumber) ?>
                    </span>
                </td>

                <td>
                    <?= h($row['nama'] ?? '-') ?>
                </td>

                <td>
                    <?= !empty($row['tarikh'])
                        ? h(date('d/m/Y', strtotime($row['tarikh'])))
                        : '-' ?>
                </td>

                <!-- WAKIL (editable text OR dropdown kalau lookup) -->
                <td>
                <select name="wakil" class="form-select form-select-sm">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupWakil as $opt): ?>
                        <option value="<?= h($opt['wakil_code']) ?>"
                            <?= $wakil == $opt['wakil_code'] ? 'selected' : '' ?>>
                            <?= h($opt['wakil_my']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </td>

                <!-- PERINGKAT -->
                <td>
                <select name="peringkat" class="form-select form-select-sm">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupPeringkat as $opt): ?>
                    <option value="<?= h($opt['peringkat_code']) ?>"
                        <?= $peringkat == $opt['peringkat_code'] ? 'selected' : '' ?>>
                        <?= h($opt['peringkat_my']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                </td>

                <!-- PENCAPAIAN -->
                <td>
                <select name="pencapaian" class="form-select form-select-sm">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupPencapaian as $opt): ?>
                        <option value="<?= h($opt['pencapaian_code']) ?>"
                            <?= $pencapaian == $opt['pencapaian_code'] ? 'selected' : '' ?>>
                            <?= h($opt['pencapaian_my']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </td>
            </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>     



                