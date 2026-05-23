

                <div class="icares-address-content">
                  <div class="tab-pane show active">
                    <div class="icares-address-panel-header">
                      <h5><?= h(tr('tab_maklumat_akaun', 'Maklumat Akaun')) ?></h5>
                      <span><?= h(tr('profile_alamat_editable', 'Boleh Dikemaskini')) ?></span>
                    </div>

                <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                  <input type="hidden" name="icares_form" value="data_akaun">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
                          <!-- Akaun Bank -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_akaun_bank','Akaun Bank')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="akaun_bank" class="form-control" value=" " >
                            </div>
                          </div>

                          <!-- Dokumen Akaun -->
                          <div class="mb-2 row align-items-center">
                            <div class="col-sm-4">
                                <label class="col-form-label text-nowrap"> <?= h(tr('profile_dokumen_akaun','Dokumen Akaun')) ?> </label> 
                                <i class="ri-information-line ms-1 text-info extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                                aria-label="<?= h(tr('profile_akaun_bank','Akaun Bank')) ?>" data-bs-original-title="<?= h(tr('profile_dokumen_akaun_note','Sila sertakan Penyata No. Akaun Bank (Aktif) dalam format JPG/JPEG/PDF, maks 5MB')) ?>"></i>
                            </div>
                            <div class="col-sm-8">
                                <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                                <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                            </div>
                          </div>     
                        </div>

                        <div class="col-md-6 gx-4">
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="col-12 text-end mt-3">
                          <button type="submit" class="btn btn-primary rounded-3 px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
                  </div>
                </div>       
