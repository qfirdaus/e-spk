<!-- // Modal: Add Tetapan Tarikh Permohonan -->
<div class="modal fade" id="dateConfigAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-calendar-event-line"></i>
                    <?= h(tr('tambah_konfigurasi_tarikh','Tambah Tarikh Permohonan')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="dateConfigForm" method="post">
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

                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('status','Status')) ?>
                                </label>
                                <select name="config_is_active" class="form-select" required>
                                    <option value="1">
                                        <?= h(tr('aktif','Aktif')) ?>
                                    </option>
                                    <option value="0">
                                        <?= h(tr('tidak_aktif','Tidak Aktif')) ?>
                                    </option>
                                </select>
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
<div class="modal fade" id="dateConfigUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-edit-box-line"></i>
                    <?= h(tr('kemaskini_konfigurasi_tarikh','Kemaskini Tarikh Permohonan')) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="dateConfigUpdateForm" method="post">
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

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= h(tr('status','Status')) ?>
                                </label>
                                <select name="update_config_is_active" id="update_status" class="form-select" required>
                                    <option value="1"><?= h(tr('aktif','Aktif')) ?></option>
                                    <option value="0"><?= h(tr('tidak_aktif','Tidak Aktif')) ?></option>
                                </select>
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