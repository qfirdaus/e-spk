
                <div id="auditEventsLoading" class="skeleton-loader" style="display: none;">
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                </div>

                <form method="post" action="<?= base_url('profile/update') ?>">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
                          <!-- Status Pekerjaan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Status Pekerjaan')) ?></label>
                            <div class="col-sm-8">
                              <select name="status_pekerjaan" class="form-select">
                                <option value="">-- Sila Pilih --</option>
                                <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Bekerja</option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak Bekerja</option>
                              </select>
                            </div>                 
                          </div>

                          <!-- Sektor Pekerjaan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_sektor_pekerjaan','Sektor Pekerjaan')) ?></label>
                            <div class="col-sm-8">
                              <select name="sektor_pekerjaan" class="form-select">
                                <option value="">-- Sila Pilih --</option>
                                <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Kerajaan</option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Swasta</option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Persendirian</option>
                              </select>
                            </div>                 
                          </div>   

                          <!-- Status Pekerjaan Sambilan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan_sambilan','Status Pekerjaan Sambilan')) ?></label>
                            <div class="col-sm-8">
                              <select name="status_pekerjaan_sambilan" class="form-select">
                                <option value="">-- Sila Pilih --</option>
                                <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Ya</option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak</option>
                              </select>
                            </div>                 
                          </div>                  

                          <!-- Jenis Pekerjaan Sambilan -->
                          <div class="mb-2 row align-items-center">
                            <div class="col-sm-4">
                                <label class="col-form-label text-nowrap"> <?= h(tr('profile_jenis_pekerjaan_sambilan','Jenis Pekerjaan Sambilan')) ?> </label> 
                                <i class="ri-information-line ms-1 text-danger extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                                aria-label="<?= h(tr('profile_jenis_pekerjaan','Jenis Pekerjaan Sambilan')) ?>" data-bs-original-title="<?= h(tr('profile_jenis_pekerjaan','Sila nyatakan jenis pekerjaan sambilan, jika ada')) ?>"></i>
                            </div>
                            <div class="col-sm-8">
                              <input type="text" name="poskod" class="form-control" value="<?= h($poskodx ?? '') ?>" >
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6 gx-4">                   
                          <!-- Alamat Baris 1 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1x ?? '') ?>">
                            </div>                 
                          </div>

                          <!-- Alamat Baris 2 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2x ?? '') ?>">
                            </div>
                          </div>                        

                          <!-- Poskod -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="poskod" class="form-control" value="<?= h($poskodx ?? '') ?>">
                            </div>
                          </div>

                          <!-- Bandar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bandar" class="form-control" value="<?= h($bandarx ?? '') ?>">
                            </div>
                          </div>

                          <!-- Negeri -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri" class="form-control" value="<?= h($negerix ?? '') ?>">
                            </div>
                          </div>

                          <!-- Negara -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negara" class="form-control" value="<?= h($negarax ?? '') ?>">
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