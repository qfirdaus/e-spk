                <!-- <div class="table-responsive">
                    <table id="groupTable" class="table table-bordered align-middle dataTable no-footer">
                    <thead>
                        <tr>
                        <th class="small w-25"><?= h(tr('istar_award_name','Nama Anugerah')) ?></th>
                        <th class="small text-center" style="width: 8%;"><?= h(tr('profile_no_matrik','No. Matrik')) ?></th> 
                        <th class="small"><?= h(tr('profile_nama','Nama')) ?></th>
                        <th class="small w-25"><?= h(tr('profile_program','Program')) ?></th>
                        <th class="small"><?= h(tr('istar_col_mark','Markah')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <td><?= h(tr('tab_pelajar_diraja','Pelajar Diraja')) ?></td>
                        <td class=" text-center">2250005</td>
                        <td>Ahmad bin Ali</td>
                        <td class="w-25">SARJANA MUDA PENGURUSAN (PERTAHANAN DAN KESELAMATAN) DENGAN KEPUJIAN</td>
                        <td>0</td>
                        </tr>
                        <tr>
                        <td><?= h(tr('tab_pingat_emas_canselor','Pingat Emas Canselor')) ?></td>
                        <td class=" text-center">7240341</td>
                        <td>Nur Balqis Hananie binti Rosidi</td>
                        <td class="w-25">DIPLOMA PENTADBIRAN PERNIAGAAN</td>
                        <td>0</td>
                        </tr>
                        <tr>
                        <td><?= h(tr('tab_pingat_emas_canselor','Pingat Emas Canselor')) ?></td>
                        <td class=" text-center">7240200</td>
                        <td>NURUL AIN BINTI MOHD ZAMRI</td>
                        <td class="w-25">DIPLOMA PENTADBIRAN PERNIAGAAN</td>
                        <td>0</td>
                        </tr>
                        <tr>
                        <td><?= h(tr('tab_pingat_emas_lpu','Pingat Emas LPU (Kepimpinan)')) ?></td>
                        <td class=" text-center">2250015</td>
                        <td>Jiva Santhini a/p Sivanathan</td>
                        <td class="w-25">SARJANA MUDA DOKTOR PERUBATAN</td>
                        <td>0</td>
                        </tr>
                    </tbody>
                    </table>               
                </div>     
                <div class="d-flex justify-content-end mt-3" >
                    <button type="button" class="btn btn-primary px-4" id="btn-submit">
                    <i class="ri-telegram-line me-2"></i> <?= h(tr('istar_btn_submit_nomination','Hantar Pencalonan')) ?>
                    </button>
                </div>                            -->

                <div class="table-responsive">
    <table id="permohonanTable" class="table table-bordered align-middle dataTable no-footer">
        <thead class="table-light">
            <tr>
                <th style="width:5%;">#</th>
                <th><?= h(tr('profile_nama','Nama')) ?></th>
                <th class="text-center"><?= h(tr('profile_no_matrik','No. Matrik')) ?></th>
                <th style="width:25%;"><?= h(tr('profile_program','Program')) ?></th>
                <th><?= h(tr('wakil','Wakil')) ?></th>
                <th><?= h(tr('peringkat','Peringkat')) ?></th>
                <th><?= h(tr('pencapaian','Pencapaian')) ?></th>
                <th class="text-center"><?= h(tr('istar_col_mark','Markah')) ?></th>
            </tr>
        </thead>

        <tbody>

            <!-- ROW 1 -->
            <tr>
                <td>1</td>
                <td>Ahmad bin Ali</td>
                <td class="text-center">2250005</td>
                <td>SARJANA MUDA PENGURUSAN (PERTAHANAN DAN KESELAMATAN) DENGAN KEPUJIAN</td>

                <td>
                    <select class="form-select form-select-sm">
                        <option selected>Individu</option>
                        <option>Kolej Kediaman</option>
                        <option>Fakulti</option>
                        <option>Universiti</option>
                        <option>Negeri</option>
                    </select>
                </td>

                <td>
                    <select class="form-select form-select-sm">
                        <option>Kolej Kediaman</option>
                        <option>Fakulti</option>
                        <option selected>Universiti</option>
                        <option>Negeri</option>
                        <option>Kebangsaan</option>
                    </select>
                </td>

                <td>Johan / Emas</td>
                <td class="text-center fw-bold">92</td>
            </tr>

            <!-- ROW 2 -->
            <tr>
                <td>2</td>
                <td>Nur Balqis Hananie binti Rosidi</td>
                <td class="text-center">7240341</td>
                <td>DIPLOMA PENTADBIRAN PERNIAGAAN</td>

                <td>
                    <select class="form-select form-select-sm">
                        <option>Individu</option>
                        <option selected>Badan Pelajar</option>
                        <option>Fakulti</option>
                        <option>Universiti</option>
                        <option>Negeri</option>
                    </select>
                </td>

                <td>
                    <select class="form-select form-select-sm">
                        <option>Fakulti</option>
                        <option selected>Universiti</option>
                        <option>Negeri</option>
                        <option>Kebangsaan</option>
                    </select>
                </td>

                <td>Naib Johan / Perak</td>
                <td class="text-center fw-bold">85</td>
            </tr>

            <!-- ROW 3 -->
            <tr>
                <td>3</td>
                <td>Nurul Ain binti Mohd Zamri</td>
                <td class="text-center">7240200</td>
                <td>DIPLOMA PENTADBIRAN PERNIAGAAN</td>

                <td>
                    <select class="form-select form-select-sm">
                        <option selected>Fakulti</option>
                        <option>Badan Pelajar</option>
                        <option>Universiti</option>
                        <option>Negeri</option>
                    </select>
                </td>

                <td>
                    <select class="form-select form-select-sm">
                        <option>Universiti</option>
                        <option selected>Negeri</option>
                        <option>Kebangsaan</option>
                    </select>
                </td>

                <td>Peserta</td>
                <td class="text-center fw-bold">70</td>
            </tr>

            <!-- ROW 4 -->
            <tr>
                <td>4</td>
                <td>Jiva Santhini a/p Sivanathan</td>
                <td class="text-center">2250015</td>
                <td>SARJANA MUDA DOKTOR PERUBATAN</td>

                <td>
                    <select class="form-select form-select-sm">
                        <option>Individu</option>
                        <option>Fakulti</option>
                        <option selected>Universiti</option>
                        <option>Negeri</option>
                    </select>
                </td>

                <td>
                    <select class="form-select form-select-sm">
                        <option>Universiti</option>
                        <option>Negeri</option>
                        <option selected>Kebangsaan</option>
                    </select>
                </td>

                <td>Tempat Ketiga / Gangsa</td>
                <td class="text-center fw-bold">78</td>
            </tr>

            <!-- ROW 5 -->
            <tr>
                <td>5</td>
                <td>Muhammad Danish Hakim</td>
                <td class="text-center">2250099</td>
                <td>SARJANA MUDA SAINS KOMPUTER</td>

                <td>
                    <select class="form-select form-select-sm">
                        <option selected>Individu</option>
                        <option>Badan Pelajar</option>
                        <option>Fakulti</option>
                        <option>Universiti</option>
                    </select>
                </td>

                <td>
                    <select class="form-select form-select-sm">
                        <option>Fakulti</option>
                        <option selected>Universiti</option>
                        <option>Negeri</option>
                    </select>
                </td>

                <td>Johan / Emas</td>
                <td class="text-center fw-bold">95</td>
            </tr>

        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-3">
    <button type="button" class="btn btn-primary px-4" id="btn-submit">
        <i class="ri-telegram-line me-2"></i>
        <?= h(tr('istar_btn_submit_nomination','Hantar Pencalonan')) ?>
    </button>
</div>