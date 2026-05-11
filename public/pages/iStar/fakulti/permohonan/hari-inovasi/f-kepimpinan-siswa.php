                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-6 gx-4">
                                <!-- Filter -->
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_peringkat_pengajian','Tahap Pengajian')) ?></label>
                                    <div class="col-sm-8">
                                        <select name="status_pekerjaan" class="form-select">
                                        <option value=""><?= h(tr('istar_common_select','-- Sila Pilih --')) ?></option>
                                        <option value="Diploma" <?= $tahap=='diploma'?'selected':'' ?>><?= h(tr('istar_degree_diploma','Diploma')) ?></option>
                                        <option value="Sarjana Muda" <?= $tahap=='sarjana_muda'?'selected':'' ?>><?= h(tr('istar_degree_bachelor','Sarjana Muda')) ?></option>
                                        </select>
                                    </div>                 
                                </div>

                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_program_pengajian','Program Pengajian')) ?></label>
                                    <div class="col-sm-8">
                                        <select name="status_pekerjaan" class="form-select">
                                        <option value=""><?= h(tr('istar_common_all','Keseluruhan')) ?></option>
                                        <option value="Diploma" <?= $tahap=='diploma'?'selected':'' ?>> Diploma Pengurusan Logistik</option>
                                        <option value="Sarjana Muda" <?= $tahap=='sarjana_muda'?'selected':'' ?>>Sarjana Muda Kejuruteraan Mekanikal</option>
                                        </select>
                                    </div>                 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-end mt-3" >
                    <button type="button" class="btn btn-primary px-4" id="btn-submit-kepimpinan-siswa">
                    <i class="ri-calculator-line me-2"></i> <?= h(tr('istar_btn_calculate_marks','Kira Markah')) ?>
                    </button>
                </div>      
                <br>            
                <div class="table-responsive">
                    <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                    <thead>
                        <tr>
                        <th class="text-center" style="width: 2%;"> </th>
                        <th class="small" style="width: 2%;"><?= h(tr('profile_bil','Bil.')) ?></th>
                        <th class="small text-center" style="width: 8%;"><?= h(tr('profile_no_matrik','No. Matrik')) ?></th> 
                        <th class="small"><?= h(tr('profile_nama','Nama')) ?></th>
                        <th class="small w-25"><?= h(tr('profile_program','Program')) ?></th>
                        <th class="small"><?= h(tr('istar_col_mark','Markah')) ?></th>
                        <th class="small text-center"><?= h(tr('istar_col_document','Dokumen')) ?></th>
                        <th class="small text-center"><?= h(tr('istar_col_action','Tindakan')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <td>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="selectAll-kepimpinan-program" />
                                <label class="form-check-label" for="selectAll-kepimpinan-program"></label>
                            </div>                            
                        </td>
                        <td>1</td>
                        <td class=" text-center">2250005</td>
                        <td>Ahmad bin Ali</td>
                        <td class="w-25">SARJANA MUDA PENGURUSAN (PERTAHANAN DAN KESELAMATAN) DENGAN KEPUJIAN</td>
                        <td>0</td>
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
                        <td>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="selectAll-kepimpinan-jawatan" />
                                <label class="form-check-label" for="selectAll-kepimpinan-jawatan"></label>
                            </div>                            
                        </td>
                        <td>2</td>
                        <td class=" text-center">7240341</td>
                        <td>Nur Balqis Hananie binti Rosidi</td>
                        <td class="w-25">DIPLOMA PENTADBIRAN PERNIAGAAN</td>
                        <td>0</td>
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
