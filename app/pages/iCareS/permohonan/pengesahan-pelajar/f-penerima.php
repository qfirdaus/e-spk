
                <form method="post" action="<?= base_url('profile/update') ?>">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">

                          <!-- Nama Penerima Surat Pengesahan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label"><?= h(tr('profile_nama_penerima','Nama Penerima Surat Pengesahan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="nama_penerima" class="form-control" value="<?= h($namaPenerima) ?>" >
                            </div>
                          </div>

                          <!-- Alamat Penerima Surat Pengesahan -->

                          <!-- Alamat Baris 1 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat1" class="form-control" value="<?= ucwords(strtolower(h($alamat1 ?? ''))) ?>" >
                            </div>                 
                          </div>

                          <!-- Alamat Baris 2 -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"></label>
                            <div class="col-sm-8">
                              <input type="text" name="alamat2" class="form-control" value="<?= ucwords(strtolower(h($alamat2 ?? ''))) ?>" >
                            </div>
                          </div>                        

                          <!-- Poskod -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_poskod','Poskod')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="poskod" class="form-control" value="<?= h($poskod ?? '') ?>" >
                            </div>
                          </div>

                          <!-- Bandar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bandar','Bandar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bandar" class="form-control" value="<?= ucwords(strtolower(h($bandar ?? ''))) ?>" >
                            </div>
                          </div>

                          <!-- Negeri -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri" class="form-control" value="<?= ucwords(strtolower(h($negeri ?? ''))) ?>" >
                            </div>
                          </div>

                          <!-- Negara -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
                            </div>
                          </div>                                 
 
                        </div>

                        <div class="col-md-6 gx-4">
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
