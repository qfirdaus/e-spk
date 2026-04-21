                <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                  <input type="hidden" name="icares_form" value="istar_konvo_anugerah_pengiktirafan">
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
                          <!-- Nama Anugerah / Pengiktirafan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('nama_anugerah_pengiktirafan','Nama Anugerah / Pengiktirafan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="nama_penuh" class="form-control" value=" " >
                            </div>                 
                          </div>

                          <!-- Tahun -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('tahun_pengiktirafan','Tahun')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="tahun" class="form-control" value=" "  >
                            </div>
                          </div>  

                          <!-- Kurniaan / Pemberian -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('kurniaan_pemberian','Kurniaan / Pemberian')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="kurniaan_pemberian" class="form-control" value=" "  >
                            </div>
                          </div>  

                        </div>

                        <div class="col-md-6 gx-4">  
                          <!-- Peringkat -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('peringkat','Peringkat')) ?></label>
                            <div class="col-sm-8">
                                <select name="peringkat" class="form-select">
                                <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                                <option value="Lelaki" <?= $peringkat=='Lelaki'?'selected':'' ?>><?= h(tr('istar_option_residential_college','Kolej Kediaman')) ?></option>
                                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_faculty','Fakulti')) ?></option> 
                                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_university','Universiti')) ?></option>
                                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_state','Negeri')) ?></option>
                                <option value="Perempuan" <?= $peringkat=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_national','Kebangsaan')) ?></option>
                                </select>
                            </div>                 
                          </div>                                                                                   
                          
                          <!-- Dokumen Sokongan -->
                          <div class="mb-2 row align-items-center">
                            <div class="col-sm-4">
                                <label class="col-form-label text-nowrap"> <?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?> </label> 
                                <i class="ri-information-line ms-1 text-danger extra-roles-info" data-bs-toggle="tooltip" data-bs-placement="top" 
                                aria-label="<?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?>" data-bs-original-title="<?= h(tr('profile_dokumen_sokongan_note','Sila sertakan Dokumen Sokongan dalam format JPG/JPEG/PDF, maks 5MB')) ?>"></i>
                            </div>
                            <div class="col-sm-8">
                                <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                                <div class="invalid-feedback"><?= h(tr('profile_max_file_size','Max file size 5MB')) ?></div>
                            </div>
                          </div>                        
                        </div>   
                                                
                        <!-- Submit Button -->
                        <div class="col-12 text-end mt-3">
                          <button type="submit" class="btn btn-primary px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>       
                <hr>
                <div class="table-responsive">
                    <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                    <thead>
                        <tr>
                        <th class="small w-25"><?= h(tr('nama_anugerah_pengiktirafan','Nama Anugerah / Pengiktirafan')) ?></th>
                        <th class="small"><?= h(tr('tahun_pengiktirafan','Tahun')) ?></th> 
                        <th class="small"><?= h(tr('wakil','Wakil')) ?></th>
                        <th class="small"><?= h(tr('peringkat','Peringkat')) ?></th>
                        <th class="small"><?= h(tr('pencapaian','Pencapaian')) ?></th>
                        <th class="small text-center"><?= h(tr('istar_col_document','Dokumen')) ?></th>
                        <th class="small text-center"><?= h(tr('istar_col_action','Tindakan')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <td>PERSEMBAHAN TAEKWONDO SEMPENA SAMBUTAN HARI WANITA SEDUNIA PERINGKAT UPNM</td>
                        <td>14-03-2025 - 14-03-2025</td>
                        <td></td>
                        <td><?= h(tr('istar_option_university','Universiti')) ?></td>
                        <td><?= h(tr('istar_result_third_place','Tempat Ketiga')) ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" type="button">
                            <i class="ri-eye-line me-1"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger" type="button">
                            <i class="ri-pencil-line me-1"></i>
                            </button>
                        </td>
                        </tr>
                        <tr>
                        <td>PENGANJURAN HAWK INTENSE CHALLENGE 2025</td>
                        <td>31-05-2025 - 31-05-2025</td>
                        <td></td>
                        <td><?= h(tr('istar_option_university','Universiti')) ?></td>
                        <td><?= h(tr('istar_result_participant','Peserta')) ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" type="button">
                            <i class="ri-eye-line me-1"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger" type="button">
                            <i class="ri-pencil-line me-1"></i>
                            </button>
                        </td>
                        </tr>
                    </tbody>
                    </table>               
                </div>              
