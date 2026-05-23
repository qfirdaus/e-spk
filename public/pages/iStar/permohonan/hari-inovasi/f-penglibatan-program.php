                <div class="konvo-tab-card p-3 mb-4">
                  <div class="icares-address-panel-header">
                    <h5 class="text-h5"><?= h(tr('profile_senarai_penglibatan_program','Senarai Penglibatan Program')) ?></h5>
                  </div>

                  <div class="konvo-form-box mb-3">
                    <form method="post" enctype="multipart/form-data" action="<?= base_url('actions/profile-update.php') ?>">
                      <input type="hidden" name="icares_form" value="istar_hari_inovasi_penglibatan_program">
                      <div class="row gx-3">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label"><?= h(tr('nama_program_pertandingan','Nama Program / Nama Pertandingan')) ?></label>
                            <input type="text" name="nama_penuh" class="form-control" value="" >
                          </div>

                          <div class="mb-3">
                            <label class="form-label"><?= h(tr('profile_tarikh','Tarikh')) ?></label>
                            <input type="date" name="tarikh" class="form-control" value="" >
                          </div>

                          <div class="mb-3">
                            <label class="form-label"><?= h(tr('wakil','Wakil')) ?></label>
                            <select name="wakil" class="form-select">
                              <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                            </select>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label"><?= h(tr('peringkat','Peringkat')) ?></label>
                            <select name="peringkat" class="form-select">
                              <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label"><?= h(tr('pencapaian','Pencapaian')) ?></label>
                            <select name="pencapaian" class="form-select">
                              <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label"><?= h(tr('profile_dokumen_sokongan','Dokumen Sokongan')) ?></label>
                            <input type="file" name="dokumen" class="form-control" accept=".jpg, .jpeg, .pdf" onchange="checkFileSize(this)" />
                          </div>
                        </div>

                        <div class="col-12 text-end">
                          <button type="submit" class="btn btn-primary rounded-3 px-4"><i class="ri-save-3-line me-2"></i> <?= h(tr('profile_save_button','Simpan')) ?></button>
                        </div>
                      </div>
                    </form>
                  </div>

                  <div class="table-responsive dt-standard">
                    <table id="hariInovasiPenglibatanTable" class="table table-bordered align-middle w-100 hari-inovasi-table">
                      <thead>
                        <tr>
                          <th class="col-bil text-center"><?= h(tr('bil_no','No.')) ?></th>
                          <th class="small w-40"><?= h(tr('nama_program_pertandingan','Nama Program / Nama Pertandingan')) ?></th>
                          <th class="small w-15 text-center"><?= h(tr('profile_tarikh','Tarikh')) ?></th>
                          <th class="small w-12 text-center"><?= h(tr('wakil','Wakil')) ?></th>
                          <th class="small w-12 text-center"><?= h(tr('peringkat','Peringkat')) ?></th>
                          <th class="small w-12 text-center"><?= h(tr('pencapaian','Pencapaian')) ?></th>
                          <th class="small text-center" style="width:120px;"><?= h(tr('istar_col_action','Tindakan')) ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td></td>
                          <td class="text-start js-tooltip-cell">PERSEMBAHAN TAEKWONDO SEMPENA SAMBUTAN HARI WANITA SEDUNIA PERINGKAT UPNM</td>
                          <td class="text-center">14-03-2025</td>
                          <td class="text-center"></td>
                          <td class="text-center"><?= h(tr('istar_option_university','Universiti')) ?></td>
                          <td class="text-center"><?= h(tr('istar_result_third_place','Tempat Ketiga')) ?></td>
                          <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                              <button class="btn btn-sm btn-outline-warning rounded-3" type="button"><i class="ri-eye-line"></i></button>
                              <button class="btn btn-sm btn-outline-primary rounded-3" type="button"><i class="ri-pencil-line"></i></button>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td></td>
                          <td class="text-start js-tooltip-cell">PENGANJURAN HAWK INTENSE CHALLENGE 2025</td>
                          <td class="text-center">31-05-2025</td>
                          <td class="text-center"></td>
                          <td class="text-center"><?= h(tr('istar_option_university','Universiti')) ?></td>
                          <td class="text-center"><?= h(tr('istar_result_participant','Peserta')) ?></td>
                          <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                              <button class="btn btn-sm btn-outline-warning rounded-3" type="button"><i class="ri-eye-line"></i></button>
                              <button class="btn btn-sm btn-outline-primary rounded-3" type="button"><i class="ri-pencil-line"></i></button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
