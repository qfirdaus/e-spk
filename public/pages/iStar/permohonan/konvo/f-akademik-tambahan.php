
                <div class="skeleton-loader" style="display: none;">
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                </div>

                <div class="icares-address-content">
                  <div class="tab-pane show active">
                    <div class="icares-address-panel-header">
                      <h5><?= h(tr('tab_maklumat_tambahan','Maklumat Tambahan')) ?></h5>
                      <span><?= h(tr('profile_alamat_editable', 'Boleh Kemaskini')) ?></span>
                    </div>

                    <form id="form-akademik-tambahan" method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                        <div class="row">
                            <div class="col-12">

                                <!-- Gred PSM -->
                                <div class="mb-4 row align-items-center">
                                    <label class="col-md-2 col-form-label text-nowrap">
                                        <?= h(tr('gred_kursus_psm','Gred Kursus PSM')) ?>
                                    </label>

                                    <div class="col-md-4">
                                        <input type="text"
                                            name="gredPSM"
                                            class="form-control uppercase"
                                            value="">
                                    </div>
                                </div>

                                <!-- Anugerah Dekan -->
                                <div class="mb-2 row align-items-start">
                                    <label class="col-sm-2 col-form-label">
                                        <?= h(tr('anugerah_dekan','Anugerah Dekan')) ?>
                                    </label>

                                    <div class="col-sm-10">

                                        <div class="d-flex justify-content-start align-items-center mb-2">
                                            <button type="button"
                                                    id="dekanBtnAdd"
                                                    class="btn btn-success rounded-3">
                                                <i class="ri-add-line me-1"></i>
                                                <?= h(tr('add_new_anugerah_dekan', 'Tambah Anugerah Dekan')) ?>
                                            </button>                                         
                                        </div>

                                        <div class="table-responsive dt-standard">
                                            <table id="dekanDT" class="table table-bordered align-middle w-100">
                                                <thead>
                                                <tr>
                                                    <th class="col-bil text-center"><?= h(tr('senarai_no', 'No.')) ?></th>
                                                    <th class="small w-30"><?= h(tr('dokumen_dekan', 'Dokumen')) ?></th>
                                                    <th class="small w-20 text-center"><?= h(tr('tindakan', 'Tindakan')) ?></th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                <?php 
                                                    foreach ($akademikTambahanData as $i => $row): 
                                                ?>
                                                    <?php $rowJson = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>

                                                    <tr  data-id="<?= $row['id'] ?>" >
                                                    <td class="col-bil text-center"></td> 

                                                    <!-- Dokumen Dekan -->
                                                    <td>
                                                        <input type="text"
                                                            name="nama_dokumen"
                                                            class="form-control form-control-sm"
                                                            value="<?= h($row['nama_dokumen'] ?? '') ?>">
                                                    </td>

                                                    <!-- TINDAKAN -->
                                                    <td>
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
                                                            data-namadokumen="<?= h($row['nama_dokumen'] ?? '') ?>"
                                                            data-url="pages/iStar/permohonan/konvo/ajax/akademik-tambahan.php?action=updateDokumenDekan"
                                                            accept=".pdf,.jpg,.jpeg">

                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger rounded-3 btn-delete-dekan"
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
                                </div>

                            </div>
                        </div>
                     </form>
                  </div>
                </div> 
