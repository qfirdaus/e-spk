              <form method="post" action="<?= base_url('profile/update') ?>">
                <div class="row">
                  <div class="col-12">
                    <div class="row">
                      <div class="col-md-6 gx-4">
                        <!-- Nama bapa -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_nama','Nama Bapa')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="nama_penuh" class="form-control" value="<?= ucwords(strtolower(h($nama_bapa))) ?>" readonly>
                          </div>                 
                        </div>

                        <!-- No Kad Pengenalan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_no_kad_pengenalan','No Kad Pengenalan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="no_kad_pengenalan" class="form-control" value="<?= h($nokpbapa) ?>" readonly>
                          </div>
                        </div>                        
                        
                        <!-- No Passport -->
                        <div class="mb-2 row">
                          <label class="col-sm-4 col-form-label text-nowrap">No Passport</label>
                          <div class="col-sm-8">
                            <input type="text" name="no_passport" class="form-control" value=" " >
                          </div>
                        </div>         
                        
                        <!-- Telefon -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_telefon','No Telefon')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="no_telefon" class="form-control" value="<?= h($nohp_bapa) ?>" readonly>
                          </div>
                        </div>

                        <!-- Emel -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_emel','Emel')) ?></label>
                          <div class="col-sm-8">
                            <input type="email" name="emel" class="form-control" value=" " >
                          </div>
                        </div>

                        <!-- Status Kesihatan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_kesihatan','Status Kesihatan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="status_kesihatan" class="form-control" value="" >
                          </div>
                        </div>                       
                        
                        <!-- Dokumen OKU -->
                        <div class="mb-2 row align-items-center">
                          <div class="col-sm-4">
                              <label class="col-form-label text-nowrap">
                              <?= h(tr('profile_dokumen_oku','Dokumen OKU')) ?>
                              </label>
                              <br>
                              <small class="text-danger">
                              <?= h(tr('profile_dokumen_oku_note','(Sila sertakan Kad OKU / Dokumen OKU / No. Pendaftaran dalam format JPG/JPEG/PDF, maks 5MB)')) ?>
                              </small>
                          </div>
                          <div class="col-sm-8">
                              <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                              <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                          </div>
                        </div>                          
                        
                        <!-- Bil. Anak -->
                        <!-- <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bil_anak','Bil. Anak')) ?></label>
                          <div class="col-sm-8">
                            <input type="number" name="bil_anak" class="form-control" value="<?= h($bil_anak ?? '') ?>" >
                          </div>                 
                        </div>               -->
                        
                        <!-- Bil. Tanggungan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_bil_tanggungan','Bil. Tanggungan')) ?></label>
                          <div class="col-sm-8">
                            <input type="number" name="bil_tanggungan" class="form-control" value="<?= h($bil_tanggungan ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Tahap Pendidikan  -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tahap_pendidikan','Tahap Pendidikan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="tahap_pendidikan" class="form-control" value="<?= h($tahap_pendidikan ?? '') ?>" >
                          </div>
                        </div>   

                        <br>
                        <h5 class="text-h5"><?= h(tr('profile_alamat_permanent','Maklumat Pekerjaan')) ?></h5>
                        <hr>
                        <!-- Status Pekerjaan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Status Pekerjaan')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_pekerjaan" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Bekerja</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak Bekerja</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Pesara</option>
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

                        <!-- Majikan -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_majikan','Nama Majikan')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="majikan" class="form-control" value="<?= h($majikan ?? '') ?>" >
                          </div>                 
                        </div>      

                        <!-- Perkhidmatan Beruniform -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_perkhidmatan_beruniform','Perkhidmatan Beruniform')) ?></label>
                          <div class="col-sm-8">
                            <select name="perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Ya</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tidak</option>
                            </select>
                          </div>                 
                        </div>      

                        <!-- Jenis Perkhidmatan Beruniform -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_jenis_perkhidmatan_beruniform','Jenis Perkhidmatan Beruniform')) ?></label>
                          <div class="col-sm-8">
                            <select name="jenis_perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Polis</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Tentera</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Bomba</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Lain-lain (Sila Nyatakan)</option>
                            </select>
                          </div>                 
                        </div>      

                        <!-- Status Perkhidmatan Beruniform -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('status_perkhidmatan_beruniform','Status Perkhidmatan Beruniform')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Dalam Perkhidmatan</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Pesara</option>
                            </select>
                          </div>                 
                        </div>   

                        <!-- Pendapatan Bulanan Kasar (RM) -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('jumlah_pendapatan_bulanan_kasar','Pendapatan Bulanan Kasar (RM)')) ?></label>
                          <div class="col-sm-8">
                            <select name="status_perkhidmatan_beruniform" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>><1,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>1,001-2,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>2,001-3,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>3,001-4,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>4,001-5,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>5,001-6,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>6,001-7,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>7,001-8,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>8,001-9,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>9,001-10,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>10,001-11,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>11,001-12,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>12,001-13,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>13,001-14,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>14,001-15,000</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><15,001</option>
                            </select>
                          </div>                 
                        </div>  

                        <!-- Perakuan Pendapatan -->
                        <div class="mb-2 row align-items-center">
                          <div class="col-sm-4">
                              <label class="col-form-label text-nowrap">
                              <?= h(tr('profile_dokumen_akaun','Perakuan Pendapatan')) ?>
                              </label>
                              <br>
                              <small class="text-danger">
                              <?= h(tr('profile_dokumen_akaun_note','(Sila sertakan Penyata Pendapatan atau Surat Pengesahan Pendapatan dalam format JPG/JPEG/PDF, maks 5MB)')) ?>
                              </small>
                          </div>
                          <div class="col-sm-8">
                              <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                              <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                          </div>
                        </div>                                      
                      </div>

                      <div class="col-md-6 gx-4">
                        <!-- Kategori Tempat Tinggal -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_kategori_tempat_tinggal','Kategori Tempat Tinggal')) ?></label>
                          <div class="col-sm-8">
                            <select name="kategori_tempat_tinggal" class="form-select">
                              <option value="">-- Sila Pilih --</option>
                              <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>>Persendirian</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Sewaan</option>
                              <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>>Lain-lain (Sila Nyatakan)</option>
                            </select>
                          </div>
                        </div>  

                        <!-- Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" >
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
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
                          </div>
                        </div>   

                        <br><br><br><br><br><br><br><br><br><br><br><br><br>
                        <!-- Pekerjaan Alamat Baris 1 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_alamat1','Alamat')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat1" class="form-control" value="<?= h($alamat1 ?? '') ?>" >
                          </div>                 
                        </div>

                        <!-- Alamat Baris 2 -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"></label>
                          <div class="col-sm-8">
                            <input type="text" name="alamat2" class="form-control" value="<?= h($alamat2 ?? '') ?>" >
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
                            <input type="text" name="bandar" class="form-control" value="<?= h($bandar ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negeri -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negeri','Negeri')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negeri" class="form-control" value="<?= h($negeri ?? '') ?>" >
                          </div>
                        </div>

                        <!-- Negara -->
                        <div class="mb-2 row align-items-center">
                          <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_negara','Negara')) ?></label>
                          <div class="col-sm-8">
                            <input type="text" name="negara" class="form-control" value="<?= h($negara ?? 'Malaysia') ?>" >
                          </div>
                        </div>                         
                      <br><br><br><br><br><br><br>
                      <!-- Submit Button -->
                      <div class="col-4 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                        </button>
                      </div>

                    </div>
                  </div>
                </div>
              </form>   