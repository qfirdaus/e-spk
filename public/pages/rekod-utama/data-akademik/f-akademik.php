
                <div class="skeleton-loader" style="display: none;">
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                </div>

                <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                  <input type="hidden" name="icares_form" value="data_akademik">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
                          <!-- Fakulti -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_fakulti','Fakulti')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="fakulti" class="form-control" value="<?= ucwords(strtolower(h($fakulti ?? ''))) ?>" readonly>
                            </div>                 
                          </div>

                          <!-- Peringkat Pengajian -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_peringkat_pengajian','Peringkat Pengajian')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="peringkat_pengajian" class="form-control" value="<?= ucwords(strtolower(h($peringkat_pengajian ?? ''))) ?>" readonly>
                            </div>
                          </div>                        

                          <!-- Program Pengajian -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_program_pengajian','Program Pengajian')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="program_pengajian" class="form-control" value="<?= str_replace('( ', '(', ucwords(strtolower(str_replace('(', '( ', h($program_pengajian ?? ''))))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Tempoh Program -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tempoh_program','Tempoh Program')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="tempoh_program" class="form-control" value="<?= ucwords(strtolower(h($tempoh_program ?? ''))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Sesi Akademik Masuk -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sesi_akademik_masuk','Sesi Akademik Masuk')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="semester_masuk" class="form-control" value="<?= h($sesi_akademik_masuk) ?>" readonly>
                            </div>
                          </div>

                          <!-- Sesi Akademik Tamat -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sesi_akademik_tamat','Sesi Akademik Tamat')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="semester_tamat" class="form-control" value="<?= h($sesi_akademik_tamat) ?>" readonly>
                            </div>
                          </div>

                        </div>

                        <div class="col-md-6 gx-4">                    

                          <!-- Status Pengajian -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pengajian','Status Pengajian')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="status_pengajian" class="form-control" value="<?= ucwords(strtolower(h($status_pengajian ?? ''))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Semester Pengajian Terkini -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_semester_terkini','Semester Pengajian Terkini')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="semester_terkini" class="form-control" value="<?= h($semester_terkini ?? '') ?>" readonly>
                            </div>
                          </div>   

                          <!-- PNGS -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_pngs','PNGS')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="pngs" class="form-control" value="<?= h($pngs ?? '') ?>" <?php if (!empty($hideButton)) { ?> readonly<?php } ?> >
                            </div>                 
                          </div>

                          <!-- PNGK -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_pngk','PNGK')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="pngk" class="form-control" value="<?= h($pngk ?? '') ?>" <?php if (!empty($hideButton)) { ?> readonly<?php } ?> >
                            </div>
                          </div>  

                          <!-- Pembiayaan Pengajian -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_pembiayaan_pengajian','Pembiayaan Pengajian')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="pembiayaan_pengajian" class="form-control" value="<?= h($pembiayaan_pengajian ?? '') ?>" <?php if (!empty($hideButton)) { ?> readonly<?php } ?> >
                            </div>
                          </div>                     
                        </div>

                        <?php if (empty($hideButton)) { ?> 
                        <!-- Submit Button -->
                        <div class="col-12 text-end mt-3">
                          <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                          </button>
                        </div>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                </form> 
