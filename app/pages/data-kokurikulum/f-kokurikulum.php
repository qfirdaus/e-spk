
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
                          <!-- Status Pelajar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pelajar','Status Pelajar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="status_pelajar" class="form-control" value="<?= ucwords(strtolower(h($status_pelajar ?? ''))) ?>" readonly>
                            </div>                 
                          </div>

                          <!-- Kegiatan Badan Pelajar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kegiatan_badan_pelajar','Kegiatan Badan Pelajar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="kegiatan_badan_pelajar" class="form-control" value="<?= ucwords(strtolower(h($kegiatan_badan_pelajar ?? ''))) ?>" readonly>
                            </div>
                          </div>                        

                          <!-- Tahap Penglibatan  -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tahap_penglibatan','Tahap Penglibatan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="tahap_penglibatan" class="form-control" value="<?= ucwords(strtolower(h($tahap_penglibatan ?? ''))) ?>" readonly>
                            </div>
                          </div>

                        </div>

                        <div class="col-md-6 gx-4">                                      
                          <!-- Kegiatan Sukan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kegiatan_sukan','Kegiatan Sukan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="kegiatan_sukan" class="form-control" value="<?= ucwords(strtolower(h($kegiatan_sukan ?? ''))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Tahap Penglibatan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tahap_penglibatan','Tahap Penglibatan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="tahap_penglibatan" class="form-control" value="<?= ucwords(strtolower(h($tahap_penglibatan ?? ''))) ?>" readonly>
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