                <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                  <input type="hidden" name="icares_form" value="istar_konvo_penglibatan_program">
                  <h5 class="text-h5"><?= h(tr('profile_penglibatan_program','Tambah Penglibatan Program')) ?></h5>
                  <hr>
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                        <div class="col-md-6 gx-4">
                          <!-- Nama Program / Nama Pertandingan -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label"><?= h(tr('nama_program_pertandingan','Nama Program / Nama Pertandingan')) ?></label>
                            <div class="col-sm-8">
                              <input type="text" name="nama_penuh" class="form-control" value=" " >
                            </div>                 
                          </div>

                          <!-- Tarikh -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_tarikh','Tarikh')) ?></label>
                            <div class="col-sm-8">
                              <input type="date" name="tarikh" class="form-control" value=" "  >
                            </div>
                          </div>  

                          <!-- Wakil -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('wakil','Wakil')) ?></label>
                            <div class="col-sm-8">
                                <select name="jantina" class="form-select">
                                <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                                <option value="Lelaki" <?= $jantina=='Lelaki'?'selected':'' ?>><?= h(tr('istar_option_individual_residential_college','Individu / Kolej Kediaman')) ?></option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_faculty','Fakulti')) ?></option> 
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_university','Universiti')) ?></option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_state','Negeri')) ?></option>
                                <option value="Perempuan" <?= $jantina=='Perempuan'?'selected':'' ?>><?= h(tr('profile_negara','Negara')) ?></option>
                                </select>
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

                          <!-- Pencapaian -->
                          <div class="mb-2 row align-items-center">
                            <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('pencapaian','Pencapaian')) ?></label>
                            <div class="col-sm-8">
                                <select name="pencapaian" class="form-select">
                                <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                                <option value="Lelaki" <?= $pencapaian=='Lelaki'?'selected':'' ?>><?= h(tr('istar_result_gold','Johan / Emas')) ?></option>
                                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_option_student_body','Badan Pelajar')) ?></option>
                                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_result_silver','Naib Johan / Perak')) ?></option> 
                                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_result_bronze','Tempat Ketiga / Gangsa')) ?></option>
                                <option value="Perempuan" <?= $pencapaian=='Perempuan'?'selected':'' ?>><?= h(tr('istar_result_participant','Peserta')) ?></option>
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
                <h5 class="text-h5"><?= h(tr('profile_senarai_penglibatan_program','Senarai Penglibatan Program')) ?></h5>
                <hr>
                <div class="table-responsive">
                    <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                    <thead>
                        <tr>
                        <th class="small w-25"><?= h(tr('nama_program_pertandingan','Nama Program / Nama Pertandingan')) ?></th>
                        <th class="small"><?= h(tr('profile_tarikh','Tarikh')) ?></th> 
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
