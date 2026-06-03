                <div class="skeleton-loader" style="display: none;">
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                  <div class="skeleton-row"></div>
                </div>

                <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                  <input type="hidden" name="icares_form" value="data_alamat">

                  <div class="icares-address-layout">
                    <div class="icares-address-nav" role="tablist" aria-label="<?= h(tr('tab_maklumat_alamat', 'Maklumat Alamat')) ?>">
                      <button class="icares-address-nav__item active" id="alamat-tetap-tab" data-bs-toggle="pill" data-bs-target="#alamat-tetap-panel" type="button" role="tab" aria-controls="alamat-tetap-panel" aria-selected="true">
                        <i class="ri-home-4-line"></i>
                        <span><?= h(tr('alamat_permanent', 'Alamat Tetap')) ?></span>
                      </button>
                      <button class="icares-address-nav__item" id="alamat-tinggal-tab" data-bs-toggle="pill" data-bs-target="#alamat-tinggal-panel" type="button" role="tab" aria-controls="alamat-tinggal-panel" aria-selected="false">
                        <i class="ri-map-pin-user-line"></i>
                        <span><?= h(tr('profile_alamat_tempat_tinggal', 'Alamat Tempat Tinggal')) ?></span>
                      </button>
                      <button class="icares-address-nav__item" id="alamat-penginapan-tab" data-bs-toggle="pill" data-bs-target="#alamat-penginapan-panel" type="button" role="tab" aria-controls="alamat-penginapan-panel" aria-selected="false">
                        <i class="ri-building-4-line"></i>
                        <span><?= h(tr('profile_penginapan_semasa_pengajian', 'Penginapan Semasa Pengajian')) ?></span>
                      </button>
                      <button class="icares-address-nav__item" id="alamat-surat-tab" data-bs-toggle="pill" data-bs-target="#alamat-surat-panel" type="button" role="tab" aria-controls="alamat-surat-panel" aria-selected="false">
                        <i class="ri-mail-send-line"></i>
                        <span><?= h(tr('profile_alamat_surat_menyurat', 'Alamat Surat Menyurat')) ?></span>
                      </button>
                    </div>

                    <div class="icares-address-content tab-content">
                      <div class="tab-pane fade show active" id="alamat-tetap-panel" role="tabpanel" aria-labelledby="alamat-tetap-tab" tabindex="0">
                        <div class="icares-address-panel-header">
                          <h5><?= h(tr('alamat_permanent', 'Alamat Tetap')) ?></h5>
                          <span><?= h(tr('profile_alamat_source', 'Data Sumber')) ?></span>
                        </div>
                        <div class="row g-3">
                          <div class="col-12">
                            <label class="form-label"><?= h(tr('profile_alamat1', 'Alamat')) ?></label>
                            <input type="text" name="alamat_tetap_1" class="form-control" value="<?= ucwords(strtolower(h($peribadi['alamat1'] ?? ''))) ?>" readonly>
                          </div>
                          <div class="col-12">
                            <input type="text" name="alamat_tetap_2" class="form-control" value="<?= ucwords(strtolower(h($peribadi['alamat2'] ?? ''))) ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_poskod', 'Poskod')) ?></label>
                            <input type="text" name="alamat_tetap_poskod" class="form-control" value="<?= h($peribadi['alamat3'] ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_bandar', 'Bandar')) ?></label>
                            <input type="text" name="alamat_tetap_bandar" class="form-control" value="<?= ucwords(strtolower(h($peribadi['alamat4'] ?? ''))) ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_negeri', 'Negeri')) ?></label>
                            <input type="text" name="alamat_tetap_negeri" class="form-control" value="<?= ucwords(strtolower(h($peribadi['negeri'] ?? ''))) ?>" readonly>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label"><?= h(tr('profile_negara', 'Negara')) ?></label>
                            <input type="text" name="alamat_tetap_negara" class="form-control" value="<?= h($peribadi['negara'] ?? 'Malaysia') ?>" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="tab-pane fade" id="alamat-tinggal-panel" role="tabpanel" aria-labelledby="alamat-tinggal-tab" tabindex="0">
                        <div class="icares-address-panel-header">
                          <h5><?= h(tr('profile_alamat_tempat_tinggal', 'Alamat Tempat Tinggal')) ?></h5>
                          <span><?= h(tr('profile_alamat_editable', 'Data Sumber')) ?></span>
                        </div>
                        <div class="row g-3">
                          <div class="col-12">
                            <label class="form-label"><?= h(tr('profile_alamat1', 'Alamat')) ?></label>
                            <input type="text" name="alamat_tinggal_1" class="form-control" value="<?= h($alamat1x ?? '') ?>" readonly>
                          </div>
                          <div class="col-12">
                            <input type="text" name="alamat_tinggal_2" class="form-control" value="<?= h($alamat2x ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_poskod', 'Poskod')) ?></label>
                            <input type="text" name="alamat_tinggal_poskod" class="form-control" value="<?= h($poskodx ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_bandar', 'Bandar')) ?></label>
                            <input type="text" name="alamat_tinggal_bandar" class="form-control" value="<?= h($bandarx ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_negeri', 'Negeri')) ?></label>
                            <input type="text" name="alamat_tinggal_negeri" class="form-control" value="<?= h($negerix ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label"><?= h(tr('profile_negara', 'Negara')) ?></label>
                            <input type="text" name="alamat_tinggal_negara" class="form-control" value="<?= h($negarax ?? '') ?>" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="tab-pane fade" id="alamat-penginapan-panel" role="tabpanel" aria-labelledby="alamat-penginapan-tab" tabindex="0">
                        <div class="icares-address-panel-header">
                          <h5><?= h(tr('profile_penginapan_semasa_pengajian', 'Penginapan Semasa Pengajian')) ?></h5>
                          <span><?= h(tr('profile_alamat_editable', 'Data Sumber')) ?></span>
                        </div>
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label"><?= h(tr('kategori_penginapan', 'Kategori Penginapan')) ?></label>
                            <input type="text" name="alamat_penginapan_kategori" class="form-control" value="Dalam Kampus" readonly>
                          </div>
                          <div class="col-12">
                            <label class="form-label"><?= h(tr('profile_alamat1', 'Alamat')) ?></label>
                            <input type="text" name="alamat_penginapan_1" class="form-control" value="<?= h($alamat1x ?? '') ?>" readonly>
                          </div>
                          <div class="col-12">
                            <input type="text" name="alamat_penginapan_2" class="form-control" value="<?= h($alamat2x ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_poskod', 'Poskod')) ?></label>
                            <input type="text" name="alamat_penginapan_poskod" class="form-control" value="<?= h($poskodx ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_bandar', 'Bandar')) ?></label>
                            <input type="text" name="alamat_penginapan_bandar" class="form-control" value="<?= h($bandarx ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_negeri', 'Negeri')) ?></label>
                            <input type="text" name="alamat_penginapan_negeri" class="form-control" value="<?= h($negerix ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label"><?= h(tr('profile_negara', 'Negara')) ?></label>
                            <input type="text" name="alamat_penginapan_negara" class="form-control" value="<?= h($negarax ?? '') ?>" readonly>
                          </div>
                        </div>
                      </div>

                      <div class="tab-pane fade" id="alamat-surat-panel" role="tabpanel" aria-labelledby="alamat-surat-tab" tabindex="0">
                        <div class="icares-address-panel-header">
                          <h5><?= h(tr('profile_alamat_surat_menyurat', 'Alamat Surat Menyurat')) ?></h5>
                          <span><?= h(tr('profile_alamat_editable', 'Data Sumber')) ?></span>
                        </div>
                        <div class="row g-3">
                          <div class="col-12">
                            <label class="form-label"><?= h(tr('profile_alamat1', 'Alamat')) ?></label>
                            <input type="text" name="alamat_surat_1" class="form-control" value="<?= h($alamat1x ?? '') ?>" readonly>
                          </div>
                          <div class="col-12">
                            <input type="text" name="alamat_surat_2" class="form-control" value="<?= h($alamat2x ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_poskod', 'Poskod')) ?></label>
                            <input type="text" name="alamat_surat_poskod" class="form-control" value="<?= h($poskodx ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_bandar', 'Bandar')) ?></label>
                            <input type="text" name="alamat_surat_bandar" class="form-control" value="<?= h($bandarx ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label"><?= h(tr('profile_negeri', 'Negeri')) ?></label>
                            <input type="text" name="alamat_surat_negeri" class="form-control" value="<?= h($negerix ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label"><?= h(tr('profile_negara', 'Negara')) ?></label>
                            <input type="text" name="alamat_surat_negara" class="form-control" value="<?= h($negarax ?? '') ?>" readonly>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                      <i class="ri-save-3-line me-2"></i><?= h(tr('profile_save_button', 'Simpan')) ?>
                    </button>
                  </div> -->
                </form>
