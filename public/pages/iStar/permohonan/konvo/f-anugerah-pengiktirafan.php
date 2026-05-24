<div class="konvo-tab-card p-3 mb-4">
  <div class="konvo-table-toolbar">
    <h5 class="text-h5">
      <?= h(tr(
          'profile_senarai_anugerah_pengiktirafan',
          'Senarai Anugerah dan Pengiktirafan'
      )) ?>
    </h5>

    <button type="button"
            id="anugerahBtnAdd"
            class="btn btn-success rounded-3">

      <i class="ri-add-line"></i>

      <span>
        <?= h(tr('button_add_new', 'Tambah Baru')) ?>
      </span>

    </button>
  </div>

  <hr>

  <div class="table-responsive dt-standard">

    <table id="anugerahDT"
           class="table table-bordered align-middle w-100">

    <thead>

      <tr>

        <th class="col-bil text-center">
          <?= h(tr('template_senarai_crud_col_no', 'No.')) ?>
        </th>

        <th class="small w-35">
          <?= h(tr(
              'nama_anugerah_pengiktirafan',
              'Nama Anugerah / Pengiktirafan'
          )) ?>
        </th>

        <th class="small w-12">
          <?= h(tr('tahun_pengiktirafan', 'Tahun')) ?>
        </th>

        <th class="small w-18">
          <?= h(tr('kurniaan_pemberian', 'Kurniaan / Pemberian')) ?>
        </th>

        <th class="small w-15">
          <?= h(tr('peringkat', 'Peringkat')) ?>
        </th>

        <th class="small text-center w-12">
          <?= h(tr('istar_col_document', 'Dokumen')) ?>
        </th>

        <th class="small text-center" style="width:140px;">
          <?= h(tr('istar_col_action', 'Tindakan')) ?>
        </th>

      </tr>

    </thead>

    <tbody>

      <?php if ($anugerahData === []): ?>

        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            <?= h(tr('icares_empty_records', 'Tiada rekod anugerah disimpan lagi.')) ?>
          </td>
        </tr>

      <?php endif; ?>

      <?php foreach ($anugerahData as $row): ?>

        <?php
          $rowJson = json_encode(
              $row,
              JSON_HEX_APOS | JSON_HEX_QUOT
          );
        ?>

        <tr>

          <!-- BIL -->
          <td class="col-bil text-center"></td>

          <!-- NAMA -->
          <td class="text-start">

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
          <td class="text-center align-top">

            <?php if (!empty($row['dokumen'])): ?>

              <a href="<?= base_url($row['dokumen']) ?>"
                 target="_blank"
                 class="btn btn-sm btn-outline-info rounded-3">

                <i class="ri-file-pdf-line"></i>

              </a>

            <?php else: ?>

              -

            <?php endif; ?>

          </td>

          <!-- TINDAKAN -->
          <td class="text-center align-top">

            <div class="d-flex justify-content-center gap-1 flex-nowrap">

              <!-- VIEW -->
              <button type="button"
                      class="btn btn-sm btn-outline-warning rounded-3 js-view-row"
                      data-row='<?= $rowJson ?>'>

                <i class="ri-eye-line"></i>

              </button>

              <!-- EDIT -->
              <button type="button"
                      class="btn btn-sm btn-outline-primary rounded-3 js-edit-row"
                      data-row='<?= $rowJson ?>'>

                <i class="ri-pencil-line"></i>

              </button>

              <!-- DELETE -->
              <button type="button"
                      class="btn btn-sm btn-outline-danger rounded-3 btn-delete-anugerah"
                      data-id="<?= h((string)($row['id'] ?? '')) ?>"
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
</div>