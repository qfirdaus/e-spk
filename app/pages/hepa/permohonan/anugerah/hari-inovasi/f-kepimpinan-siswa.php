                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-6 gx-4">
                                <!-- Filter -->
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Tahap Pengajian')) ?></label>
                                    <div class="col-sm-8">
                                        <select name="status_pekerjaan" class="form-select">
                                        <option value="">-- Sila Pilih --</option>
                                        <option value="Diploma" <?= $tahap=='diploma'?'selected':'' ?>>Diploma</option>
                                        <option value="Sarjana Muda" <?= $tahap=='sarjana_muda'?'selected':'' ?>>Sarjana Muda</option>
                                        </select>
                                    </div>                 
                                </div>

                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-4 col-form-label text-nowrap"><?= h(tr('profile_status_pekerjaan','Program Pengajian')) ?></label>
                                    <div class="col-sm-8">
                                        <select name="status_pekerjaan" class="form-select">
                                        <option value="">Keseluruhan</option>
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
                <div class="d-flex justify-content-start mt-3" >
                    <button type="submit" class="btn btn-success px-4" id="btn-submit">
                    <i class="ri-calculator-line me-2"></i> <?= h(tr('profile_btn_submit','Kira Markah')) ?>
                    </button>
                </div>      
                <br>            
                <div class="table-responsive">
                    <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                    <thead>
                        <tr>
                        <th class="text-center" style="width: 2%;"> </th>
                        <th class="small" style="width: 2%;">Bil. </th>
                        <th class="small text-center" style="width: 8%;">No. Matrik</th> 
                        <th class="small">Nama</th>
                        <th class="small w-25">Program</th>
                        <th class="small">Markah</th>
                        <th class="small text-center">Dokumen</th>
                        <th class="small text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <td>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="selectAll" />
                                <label class="form-check-label" for="selectAll"></label>
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
                                <input class="form-check-input" type="checkbox" id="selectAll" />
                                <label class="form-check-label" for="selectAll"></label>
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