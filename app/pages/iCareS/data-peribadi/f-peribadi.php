
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
                          
                          <!-- Tarikh Lahir -->
                          <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-nowrap">Tarikh Lahir</label>
                            <div class="col-sm-8">
                              <input type="text" id="tarikh_lahir" name="tarikh_lahir" class="form-control" 
                              value="<?= !empty($tarikh_lahir) ? date('d/m/Y', strtotime($tarikh_lahir)) : '' ?>" readonly>
                            </div>
                          </div>

                          <!-- Umur -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_umur','Umur')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="umur" class="form-control" value="<?= h($age) ?>" readonly>
                            </div>
                          </div>  

                          <!-- Negeri Kelahiran -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri_kelahiran','Negeri Kelahiran')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri_kelahiran" class="form-control" value="<?= ucwords(strtolower(h($negeri_lahir))) ?>" readonly>
                            </div>
                          </div>    

                          <!-- Kewarganegaraan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kewarganegaraan','Kewarganegaraan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="kewarganegaraan" class="form-control" value="<?= ucwords(strtolower(h($warganegara))) ?>"  readonly>
                            </div>
                          </div>                                            
 
                        </div>

                        <div class="col-md-6 gx-4">
                          <!-- Jantina -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jantina','Jantina')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="jantina" class="form-control" value="<?= ucwords(strtolower(h($jantina))) ?>" readonly>
                            </div>                 
                          </div>

                          <!-- Bangsa -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bangsa','Bangsa')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bangsa" class="form-control" value="<?= ucwords(strtolower(h($bangsa))) ?>" readonly>
                            </div>
                          </div>                        
                          
                          <!-- Agama -->
                          <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_agama','Agama')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="agama" class="form-control" value="<?= ucwords(strtolower(h($agama))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Status Perkahwinan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_perkahwinan','Status Perkahwinan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="status_perkahwinan" class="form-control" value="<?= ucwords(strtolower(h($status_kahwin))) ?>" readonly>
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

                          <!-- Status Pelajar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pelajar','Status Pelajar')) ?></label>
                            <div class="col-sm-8">
                              <input type="status_pelajar" name="status_pelajar" class="form-control" value="<?= ucwords(strtolower(h($status_pelajar))) ?>" readonly>
                            </div>
                          </div> 

                        </div>

                        <?php if (empty($hideButton)) { ?> 
                          <!-- kalau $hideButton tidak diset, paparkan butang simpan hanya di data-peribadi. -->
                          <!-- Submit Button -->
                          <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                            </button>
                          </div>
                        <?php } ?>

                      </div>
                    </div>
                  </div>
                </form>       
