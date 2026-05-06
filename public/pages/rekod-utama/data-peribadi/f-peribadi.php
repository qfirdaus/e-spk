
                <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                  <input type="hidden" name="icares_form" value="data_peribadi">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
                          <!-- Nama Pemohon -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama','Nama Pemohon')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="nama_penuh" class="form-control" value="<?= h($peribadi['nama_penuh'] ?? '') ?>" readonly>
                            </div>                 
                          </div>

                          <!-- No Kad Pengenalan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_kad_pengenalan','No Kad Pengenalan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="no_kad_pengenalan" class="form-control" value="<?= h($peribadi['nokp'] ?? '') ?>" readonly>
                            </div>
                          </div>         

                          <!-- No Matrik -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_matrik','No. Matrik')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="no_matrik" class="form-control" value="<?= h($peribadi['matrik'] ?? '') ?>" readonly>
                            </div>
                          </div>                        
                          
                          <!-- Tarikh Lahir -->
                          <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-nowrap">Tarikh Lahir</label>
                            <div class="col-sm-8">
                              <input type="text" id="tarikh_lahir" name="tarikh_lahir" class="form-control" 
                              value="<?= !empty($peribadi['tarikh_lahir'] ?? '') ? date('d/m/Y', strtotime($peribadi['tarikh_lahir'] ?? '')) : '' ?>" readonly>
                            </div>
                          </div>

                          <!-- Umur -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_umur','Umur')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="umur" class="form-control" value="<?= h($peribadi['age'] ?? '') ?>" readonly>
                            </div>
                          </div>  

                          <!-- Negeri Kelahiran -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri_kelahiran','Negeri Kelahiran')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="negeri_kelahiran" class="form-control" value="<?= ucwords(strtolower(h($peribadi['negeri_lahir'] ?? ''))) ?>" readonly>
                            </div>
                          </div>    

                          <!-- Kewarganegaraan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_warganegara','Kewarganegaraan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="warganegara" class="form-control" value="<?= ucwords(strtolower(h($peribadi['warganegara'] ?? ''))) ?>"  readonly>
                            </div>
                          </div>                                            
 
                        </div>

                        <div class="col-md-6 gx-4">
                          <!-- Jantina -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jantina','Jantina')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="jantina" class="form-control" value="<?= ucwords(strtolower(h($peribadi['jantina'] ?? ''))) ?>" readonly>
                            </div>                 
                          </div>

                          <!-- Bangsa -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bangsa','Bangsa')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="bangsa" class="form-control" value="<?= ucwords(strtolower(h($peribadi['bangsa'] ?? ''))) ?>" readonly>
                            </div>
                          </div>                        
                          
                          <!-- Agama -->
                          <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_agama','Agama')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="agama" class="form-control" value="<?= ucwords(strtolower(h($peribadi['agama'] ?? ''))) ?>" readonly>
                            </div>
                          </div>

                          <!-- Status Perkahwinan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_perkahwinan','Status Perkahwinan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="status_perkahwinan" class="form-control" value="<?= ucwords(strtolower(h($peribadi['status_kahwin'] ?? ''))) ?>" readonly>
                            </div>
                          </div>  

                          <!-- Telefon Terkini-->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_telefon_terkini','No Telefon Terkini')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="no_telefon_terkini" class="form-control" value="<?= h($peribadi['telno_terkini'] ?? '') ?>" readonly>
                            </div>
                          </div>

                          <!-- Emel -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_emel','Emel')) ?></label>
                            <div class="col-sm-8">
                              <input type="email" name="emel" class="form-control" value="<?= h($peribadi['email'] ?? '') ?>" readonly>
                            </div>
                          </div> 

                          <!-- Status Pelajar -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pelajar','Status Pelajar')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="status_pelajar" class="form-control" value="<?= ucwords(strtolower(h($peribadi['status_pelajar'] ?? ''))) ?>" readonly>
                            </div>
                          </div> 

                        </div>
                      </div>
                    </div>
                  </div>
                </form>       
