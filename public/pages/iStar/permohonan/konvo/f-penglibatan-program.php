<div class="col-12 text-end mt-3">
  <button type="button" id="penglibatanBtnAdd" class="btn btn-success sync-groups-btn">
    <i class="ri-add-line"></i><span><?= h(tr('button_add_new', 'Tambah Baru')) ?></span>
  </button>                
</div>
<h5 class="text-h5"><?= h(tr('profile_senarai_penglibatan_program','Senarai Penglibatan Program')) ?></h5>
<hr>
<div class="table-responsive dt-standard">
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
        <th class="small text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th>
      </tr>
    </thead>

    <tbody>
    <?php 
        foreach ($penglibatanData as $i => $row): 
            $wakil     = $row['wakil'] ?? null;
            $peringkat = $row['peringkat'] ?? null;
            $pencapaian = $row['pencapaian'] ?? null;
            $sumber = $row['sumber'] ?? 'Tambahan';

            // IStAD fallback
            if ($sumber === 'IStAD') {
                $wakil = null;
                $peringkat = null;
            }        
    ?>
            <tr data-type="istad" data-index="<?= $i ?>">

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

                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1 flex-nowrap">
                        <button type="button"
                                class="btn btn-sm btn-outline-warning js-view-row"
                                data-row='<?= json_encode($row) ?>'>
                            <i class="ri-eye-line"></i>
                        </button>

                        <button type="button"
                                class="btn btn-sm btn-outline-primary js-edit-row"
                                data-row='<?= json_encode($row) ?>'>
                            <i class="ri-pencil-line"></i>
                        </button>

                        <button type="button"
                                class="btn btn-sm btn-outline-danger js-delete-row"
                                data-row='<?= json_encode($row) ?>'>
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </td>        
            </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>     



                