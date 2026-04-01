
                <form method="post" action="<?= base_url('profile/update') ?>">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">

                          <!-- Status Kesihatan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_kesihatan','Status Kesihatan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="status_kesihatan" class="form-control" value=" " >
                            </div>
                          </div>                       
                          
                          <!-- Dokumen OKU -->
                          <div class="mb-2 row align-items-center">
                            <div class="col-sm-4">
                                <label class="col-form-label text-nowrap">  <?= h(tr('profile_dokumen_oku','Dokumen OKU')) ?> </label> 
                                <i class="ri-information-line ms-1 text-danger extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                                aria-label="<?= h(tr('profile_dokumen_oku','Dokumen OKU')) ?>" data-bs-original-title="<?= h(tr('profile_dokumen_oku_note','(Sila sertakan Kad OKU / Dokumen OKU / No. Pendaftaran dalam format JPG/JPEG/PDF, maks 5MB)')) ?>"></i>
                            </div>
                            <div class="col-sm-8">
                                <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                                <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                            </div>
                          </div>  
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12 text-center mt-3">
                          <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                          </button>
                        </div>

                      </div>
                    </div>
                  </div>
                </form>    
