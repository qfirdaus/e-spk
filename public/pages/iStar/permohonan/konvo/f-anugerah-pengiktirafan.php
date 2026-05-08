<div class="col-12 text-end mt-3">

  <button type="button"
          id="anugerahBtnAdd"
          class="btn btn-success">

    <i class="ri-add-line"></i>

    <span>
      <?= h(tr('button_add_new', 'Tambah Baru')) ?>
    </span>

  </button>

</div>


<h5 class="text-h5">

  <?= h(tr(
      'profile_senarai_anugerah_pengiktirafan',
      'Senarai Anugerah dan Pengiktirafan'
  )) ?>

</h5>

<hr>


<div class="table-responsive dt-standard">

  <table id="anugerahDT"
         class="table table-bordered align-middle w-100">

    <thead>

      <tr>

        <th class="col-bil">
          <?= h(tr('template_senarai_crud_col_no', 'No.')) ?>
        </th>

        <th class="small w-25">
          <?= h(tr(
              'nama_anugerah_pengiktirafan',
              'Nama Anugerah / Pengiktirafan'
          )) ?>
        </th>

        <th class="small">
          <?= h(tr('tahun_pengiktirafan', 'Tahun')) ?>
        </th>

        <th class="small">
          <?= h(tr('kurniaan_pemberian', 'Kurniaan / Pemberian')) ?>
        </th>

        <th class="small">
          <?= h(tr('peringkat', 'Peringkat')) ?>
        </th>

        <th class="small text-center">
          <?= h(tr('istar_col_document', 'Dokumen')) ?>
        </th>

        <th class="small text-center">
          <?= h(tr('istar_col_action', 'Tindakan')) ?>
        </th>

      </tr>

    </thead>

    <tbody>

      <?php foreach ($anugerahData as $row): ?>

        <?php
          $rowJson = json_encode(
              $row,
              JSON_HEX_APOS | JSON_HEX_QUOT
          );
        ?>

        <tr>

          <!-- BIL -->
          <td class="col-bil"></td>

          <!-- NAMA -->
          <td>

            <?= h($row['nama_anugerah'] ?? '-') ?>

            <!-- OPTIONAL BADGE -->
            <!--
            <span class="access-chip is-allowed truncate-1line"
                  data-bs-toggle="tooltip"
                  data-bs-custom-class="template-tooltip"
                  data-bs-original-title="IStAD">

                IStAD

            </span>
            -->

          </td>

          <!-- TAHUN -->
          <td>

            <?= h($row['tahun'] ?? '-') ?>

          </td>

          <!-- KURNIAAN -->
          <td>

            <?= h($row['kurniaan_pemberian'] ?? '-') ?>

          </td>

          <!-- PERINGKAT -->
          <td>

            <?= h($row['peringkat'] ?? '-') ?>

          </td>

          <!-- DOKUMEN -->
          <td class="text-center">

            <?php if (!empty($row['dokumen'])): ?>

              <a href="<?= base_url($row['dokumen']) ?>"
                 target="_blank"
                 class="btn btn-sm btn-outline-info">

                <i class="ri-file-pdf-line"></i>

              </a>

            <?php else: ?>

              -

            <?php endif; ?>

          </td>

          <!-- TINDAKAN -->
          <td class="text-center">

            <div class="d-flex justify-content-center gap-1 flex-nowrap">

              <!-- VIEW -->
              <button type="button"
                      class="btn btn-sm btn-outline-warning js-view-row"
                      data-row='<?= $rowJson ?>'>

                <i class="ri-eye-line"></i>

              </button>

              <!-- EDIT -->
              <button type="button"
                      class="btn btn-sm btn-outline-primary js-edit-row"
                      data-row='<?= $rowJson ?>'>

                <i class="ri-pencil-line"></i>

              </button>

              <!-- DELETE -->
              <button type="button"
                      class="btn btn-sm btn-outline-danger js-delete-row"
                      data-row='<?= $rowJson ?>'>

                <i class="ri-delete-bin-line"></i>

              </button>

            </div>

          </td>

        </tr>

      <?php endforeach; ?>

    </tbody>

  </table>

</div>