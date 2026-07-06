<!-- // Modal: Add Tetapan Tarikh Permohonan -->
<div class="modal fade modal-gradient" id="dateConfigAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-calendar-event-line"></i>
                    <?= h(tr('tambah_konfigurasi_tarikh','Tambah Tarikh Permohonan')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form-application-date" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="config_type" value="APPLICATION">

                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('kategori_anugerah','Kategori Anugerah')) ?>
                                </label>                            
                                <select name="config_category_award" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <option value="pingat_graduan">Anugerah Pingat Graduan</option>
                                    <option value="kualiti_tnc_hepa">Anugerah Kualiti TNC HEPA</option>
                                    <option value="khas_kecemerlangan">Anugerah Khas Kecemerlangan</option>
                                </select>
                            </div>

                            <!-- Tarikh Mula -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_mula','Tarikh Mula')) ?></label>
                                <input type="text" name="config_tarikh_mula" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div> 

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">

                            <!-- Sesi Permohonan -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('sesi_permohonan','Sesi Permohonan')) ?>
                                </label>
                                <input type="text"
                                       name="config_name_session"
                                       class="form-control"
                                       placeholder="Sesi Permohonan"
                                       required>
                            </div> 

                            <!-- Tarikh Tamat -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_tamat','Tarikh Tamat')) ?></label>
                                <input type="text" name="config_tarikh_tamat" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i>
                        <?= h(tr('profile_save_button','Simpan')) ?>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ########################################## -->

 <!-- Modal: Update Tetapan Tarikh Permohonan -->
<div class="modal fade modal-gradient" id="dateConfigUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-edit-box-line"></i>
                    <?= h(tr('kemaskini_konfigurasi_tarikh','Kemaskini Tarikh Permohonan')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form-upd-application-date" method="post">
                <div class="modal-body">

                    <input type="hidden" name="update_config_id" id="update_id">
                    <input type="hidden" name="update_config_type" value="APPLICATION">

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('kategori_anugerah','Kategori Anugerah')) ?>
                                </label>
                                <select name="update_config_category_award" id="update_category" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <option value="pingat_graduan">Anugerah Pingat Graduan</option>
                                    <option value="kualiti_tnc_hepa">Anugerah Kualiti TNC HEPA</option>
                                    <option value="khas_kecemerlangan">Anugerah Khas Kecemerlangan</option>
                                </select>
                            </div>

                            <!-- Tarikh Mula -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_mula','Tarikh Mula')) ?></label>
                                <input type="text" name="update_config_tarikh_mula" id="update_start_date" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('sesi_permohonan','Sesi Permohonan')) ?>
                                </label>
                                <input type="text"
                                       name="update_config_name_session"
                                       id="update_session"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- Tarikh Tamat -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_tamat','Tarikh Tamat')) ?></label>
                                <input type="text" name="update_config_tarikh_tamat" id="update_end_date" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i>
                        <?= h(tr('profile_save_button','Kemaskini')) ?>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- // Modal: Add Tetapan Tarikh Penilaian Fakulti -->
<div class="modal fade modal-gradient" id="evaluateDateFacAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-calendar-event-line"></i>
                    <?= h(tr('tambah_tarikh_evaluate_fac','Tambah Tarikh Penilaian Fakulti')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form-evaluate-date-fac" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="config_type" value="EVALUATE_FACULTY">

                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('kategori_anugerah','Kategori Anugerah')) ?>
                                </label>                            
                                <select name="config_category_award" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <option value="pingat_graduan">Anugerah Pingat Graduan</option>
                                    <option value="kualiti_tnc_hepa">Anugerah Kualiti TNC HEPA</option>
                                    <option value="khas_kecemerlangan">Anugerah Khas Kecemerlangan</option>
                                </select>
                            </div>

                            <!-- Tarikh Mula -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_mula','Tarikh Mula')) ?></label>
                                <input type="text" name="config_tarikh_mula" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div> 

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">

                            <!-- Sesi Permohonan -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('sesi_permohonan','Sesi Permohonan')) ?>
                                </label>
                <select name="config_name" class="form-select form-select-sm select2">
                    <option value=""><?= h(tr('sila_pilih', 'Sila Pilih')) ?></option>
                    <?php foreach ($lookupAll['list_sesi_permohonan'] as $opt): ?>
                    <option value="<?= h($opt['config_name']) ?>"
                        <?= h($row['config_name'] ?? '') == $opt['config_name'] ? 'selected' : '' ?>>
                        <?= h($opt['config_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>   
                            </div> 

                            <!-- Tarikh Tamat -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_tamat','Tarikh Tamat')) ?></label>
                                <input type="text" name="config_tarikh_tamat" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i>
                        <?= h(tr('profile_save_button','Simpan')) ?>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ########################################## -->

 <!-- Modal: Update Tetapan Tarikh Evaluate Fakulti -->
<div class="modal fade modal-gradient" id="evaluateDateFacUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-edit-box-line"></i>
                    <?= h(tr('kemaskini_konfigurasi_tarikh','Kemaskini Tarikh Permohonan')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form-upd-evaluate-date-fac" method="post">
                <div class="modal-body">

                    <input type="hidden" name="update_config_id" id="update_id">
                    <input type="hidden" name="update_config_type" value="EVALUATE_FACULTY">

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('kategori_anugerah','Kategori Anugerah')) ?>
                                </label>
                                <select name="update_config_category_award" id="update_category" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <option value="pingat_graduan">Anugerah Pingat Graduan</option>
                                    <option value="kualiti_tnc_hepa">Anugerah Kualiti TNC HEPA</option>
                                    <option value="khas_kecemerlangan">Anugerah Khas Kecemerlangan</option>
                                </select>
                            </div>

                            <!-- Tarikh Mula -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_mula','Tarikh Mula')) ?></label>
                                <input type="text" name="update_config_tarikh_mula" id="update_start_date" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('sesi_permohonan','Sesi Permohonan')) ?>
                                </label>
                                <input type="text"
                                       name="update_config_name_session"
                                       id="update_session"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- Tarikh Tamat -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_tamat','Tarikh Tamat')) ?></label>
                                <input type="text" name="update_config_tarikh_tamat" id="update_end_date" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i>
                        <?= h(tr('profile_save_button','Kemaskini')) ?>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- // Modal: Add Tetapan Tarikh Penilaian Hepa -->
<div class="modal fade modal-gradient" id="evaluateDateHepaAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-calendar-event-line"></i>
                    <?= h(tr('tambah_tarikh_evaluate_hepa','Tambah Tarikh Penilaian Hepa')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form-evaluate-date-hepa" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="config_type" value="EVALUATE_HEPA">

                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('kategori_anugerah','Kategori Anugerah')) ?>
                                </label>                            
                                <select name="config_category_award" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <option value="pingat_graduan">Anugerah Pingat Graduan</option>
                                    <option value="kualiti_tnc_hepa">Anugerah Kualiti TNC HEPA</option>
                                    <option value="khas_kecemerlangan">Anugerah Khas Kecemerlangan</option>
                                </select>
                            </div>

                            <!-- Tarikh Mula -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_mula','Tarikh Mula')) ?></label>
                                <input type="text" name="config_tarikh_mula" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div> 

                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">

                            <!-- Sesi Permohonan -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('sesi_permohonan','Sesi Permohonan')) ?>
                                </label>
                                <input type="text"
                                       name="config_name_session"
                                       class="form-control"
                                       placeholder="Sesi Permohonan"
                                       required>
                            </div> 

                            <!-- Tarikh Tamat -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_tamat','Tarikh Tamat')) ?></label>
                                <input type="text" name="config_tarikh_tamat" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i>
                        <?= h(tr('profile_save_button','Simpan')) ?>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ########################################## -->

 <!-- Modal: Update Tetapan Tarikh Evaluate Hepa -->
<div class="modal fade modal-gradient" id="evaluateDateHepaUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-edit-box-line"></i>
                    <?= h(tr('kemaskini_penilaian_hepa','Kemaskini Tarikh Penilaian Hepa')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form-upd-evaluate-date-hepa" method="post">
                <div class="modal-body">

                    <input type="hidden" name="update_config_id" id="update_id">
                    <input type="hidden" name="update_config_type" value="EVALUATE_HEPA">

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('kategori_anugerah','Kategori Anugerah')) ?>
                                </label>
                                <select name="update_config_category_award" id="update_category" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <option value="pingat_graduan">Anugerah Pingat Graduan</option>
                                    <option value="kualiti_tnc_hepa">Anugerah Kualiti TNC HEPA</option>
                                    <option value="khas_kecemerlangan">Anugerah Khas Kecemerlangan</option>
                                </select>
                            </div>

                            <!-- Tarikh Mula -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_mula','Tarikh Mula')) ?></label>
                                <input type="text" name="update_config_tarikh_mula" id="update_start_date" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('sesi_permohonan','Sesi Permohonan')) ?>
                                </label>
                                <input type="text"
                                       name="update_config_name_session"
                                       id="update_session"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- Tarikh Tamat -->
                            <div class="mb-3">
                                <label class="form-label"><?= h(tr('tarikh_tamat','Tarikh Tamat')) ?></label>
                                <input type="text" name="update_config_tarikh_tamat" id="update_end_date" class="form-control datepicker" placeholder="dd/mm/yyyy" required>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        <?= h(tr('template_senarai_crud_btn_cancel','Cancel')) ?>
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i>
                        <?= h(tr('profile_save_button','Kemaskini')) ?>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>