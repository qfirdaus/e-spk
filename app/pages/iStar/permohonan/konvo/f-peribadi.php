                <form method="post" action="<?= base_url('profile/update') ?>">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
                          <!-- Nama Pemohon -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama','Nama Pemohon')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="nama_penuh" class="form-control" value="<?= h($namaPenuh) ?>" readonly>
                            </div>                 
                          </div>

                          <!-- No Kad Pengenalan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_kad_pengenalan','No Kad Pengenalan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="no_kad_pengenalan" class="form-control" value="<?= h($nokp) ?>" readonly>
                            </div>
                          </div>         

                          <!-- No Matrik -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_matrik','No. Matrik')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="no_matrik" class="form-control" value="<?= h($nomatrik) ?>" readonly>
                            </div>
                          </div>   

                          <!-- Jantina -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jantina','Jantina')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="jantina" class="form-control" value="<?= ucwords(strtolower(h($jantina))) ?>" readonly>
                            </div>                 
                          </div>                                                         

                          <!-- Telefon -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_telefon','No Telefon')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="no_telefon" class="form-control" value="<?= h($notel_terkini) ?>" readonly>
                            </div>
                          </div>

                          <!-- Emel -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_emel','Emel')) ?></label>
                            <div class="col-sm-8">
                              <input type="email" name="emel" class="form-control" value="<?= h($email) ?>" readonly>
                            </div>
                          </div>  
                        </div>

                        <div class="col-md-6 gx-4">
                          <!-- Fakulti -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_fakulti','Fakulti')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="fakulti" class="form-control" value="<?= h($fakulti) ?>" readonly>
                            </div>                 
                          </div>     

                          <!-- Program Pengajian -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_program','Program Pengajian')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="program" class="form-control" value="<?= h($program) ?>" readonly>
                            </div>                 
                          </div>    

                          <!-- Tempoh Program -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tempoh_program','Tempoh Program')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="tempoh_program" class="form-control" value="<?= h($tempohProgram) ?>" readonly>
                            </div>                 
                          </div>   

                          <!-- PNGK Semasa / Akhir-->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_pngk','PNGK')) ?></label>

                            <div class="col-sm-8 d-flex align-items-center gap-2">
                              <span class="text-nowrap"><?= h(tr('profile_pngk_semasa','Semasa')) ?></span>
                              <input type="text" name="pngk_semasa" class="form-control form-control-sm text-center flex-fill" value="<?= h($pngkSemasa) ?>" readonly>

                              <span class="text-nowrap"><?= h(tr('profile_pngk_akhir','Akhir')) ?></span>
                              <input type="text" name="pngk_akhir" class="form-control form-control-sm text-center flex-fill" value="<?= h($pngkAkhir) ?>" readonly>
                            </div>
                          </div>

                          <!-- Gred Kursus PSM -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_gred_kursus_psm','Gred Kursus PSM')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="gred_kursus_psm" class="form-control" value="<?= h($gredKursusPsm) ?>" readonly>
                            </div>
                          </div>   
                    
                          <!-- Bil. Anugerah Dekan-->
                          <div class="mb-4 row align-items-center">
                            <div class="col-sm-4 d-flex align-items-center">
                              <label class="col-form-label text-nowrap mb-0"><?= h(tr('profile_bil_anugerah_dekan','Bil. Anugerah Dekan')) ?></label> 
                              <i class="ri-information-line ms-1 text-danger extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="<?= h(tr('bil_anugerah_dekan','Bil. Anugerah Dekan')) ?>" data-bs-original-title="<?= h(tr('anugerah_dekan_note','Sila muat naik semua sijil Dekan dalam satu fail PDF, saiz maksimum 5MB.')) ?>"></i>
                            </div>

                            <!-- Input kanan -->
                            <div class="col-sm-8 d-flex align-items-center gap-2">

                              <!-- Input bilangan -->
                              <input type="number" name="bil_anugerah_dekan"
                                    class="form-control form-control-sm text-center"
                                    style="max-width: 80px;"
                                    value="<?= h($bilAnugerahDekan) ?>"
                                    min="1" max="10">

                              <!-- Upload file -->
                              <input type="file" name="dokumen"
                                    class="form-control flex-fill"
                                    accept=".jpg, .jpeg, .pdf"
                                    onchange="checkFileSize(this)" />

                              <div class="invalid-feedback">
                                <?= h(tr('profile_max_file_size','Max file size 5MB')) ?>
                              </div>

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
