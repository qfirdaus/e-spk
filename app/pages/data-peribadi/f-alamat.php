
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
                          <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Alamat Tetap')) ?></h5>
                          <hr>
                          <!-- Alamat Baris 1 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat1" class="form-control" value="<?= ucwords(strtolower(h($alamat1 ?? ''))) ?>" readonly>
                            </div>                 
                          </div>

                          <!-- Alamat Baris 2 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat2" class="form-control" value="<?= ucwords(strtolower(h($alamat2 ?? ''))) ?>" readonly>
                            </div>
                          </div>                        

                          <!-- Poskod -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" readonly>
                            </div>
                          </div>

                          <!-- Bandar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bandar" class="form-control" value="<?= ucwords(strtolower(h($bandar ?? ''))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Negeri -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri" class="form-control" value="<?= ucwords(strtolower(h($negeri ?? ''))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Negara -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" readonly>
                            </div>
                          </div>
                          
                          <br>
                          <h5 class="text-h5"><?= h(tr('profile_alamat_tempat_tinggal','Alamat Tempat Tinggal')) ?></h5>
                          <hr>
                          <!-- Alamat Baris 1 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1x ?? '') ?>" >
                            </div>                 
                          </div>

                          <!-- Alamat Baris 2 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2x ?? '') ?>" >
                            </div>
                          </div>                        

                          <!-- Poskod -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="poskod" class="form-control" value="<?= h($poskodx ?? '') ?>" >
                            </div>
                          </div>

                          <!-- Bandar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bandar" class="form-control" value="<?= h($bandarx ?? '') ?>" >
                            </div>
                          </div>

                          <!-- Negeri -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri" class="form-control" value="<?= h($negerix ?? '') ?>" >
                            </div>
                          </div>

                          <!-- Negara -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negara" class="form-control" value="<?= h($negarax ?? '') ?>" >
                            </div>
                          </div>    
                          
                          
                          <br>
                          <h5 class="text-h5"><?= h(tr('profile_penginapan_semasa_pengajian','Penginapan Semasa Pengajian')) ?></h5>
                          <hr>
                          <!-- Kategori Penginapan : dropdown Dalam / Luar Kampus check asrama -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('kategori_penginapan','Kategori Penginapan')) ?></label>
                            <div class="col-sm-8">
                              <select class="form-select" name="alamat1" aria-label="Default select example">
                                <option value="Dalam Kampus" <?= ($alamat1 ?? 'Dalam Kampus') == 'Dalam Kampus' ? 'selected' : '' ?>>Dalam Kampus</option>
                                <option value="Luwar Kampus" <?= ($alamat1 ?? 'Dalam Kampus') == 'Luwar Kampus' ? 'selected' : '' ?>>Luar Kampus</option>
                              </select>
                            </div>                 
                          </div>                        
                          <!-- Alamat Baris 1 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1x ?? '') ?>" >
                            </div>                 
                          </div>

                          <!-- Alamat Baris 2 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2x ?? '') ?>" >
                            </div>
                          </div>                        

                          <!-- Poskod -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="poskod" class="form-control" value="<?= h($poskodx ?? '') ?>" >
                            </div>
                          </div>

                          <!-- Bandar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bandar" class="form-control" value="<?= h($bandarx ?? '') ?>" >
                            </div>
                          </div>

                          <!-- Negeri -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri" class="form-control" value="<?= h($negerix ?? '') ?>" >
                            </div>
                          </div>

                          <!-- Negara -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negara" class="form-control" value="<?= h($negarax ?? '') ?>" >
                            </div>
                          </div>                         
                        </div>

                        <div class="col-md-6 gx-4">
                          <h5 class="text-h5"><?= h(tr('profile_alamat_surat_menyurat','Alamat Surat Menyurat')) ?></h5>
                          <hr>                        
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