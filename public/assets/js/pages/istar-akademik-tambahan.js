function loadAkademikTambahan() {

    if (akademikTambahanLoaded) {
        return;
    }

    const box = document.getElementById('akademik-tambahan-content');

    if (!box) {
        console.log('BOX NOT FOUND');
        return;
    }

    setSectionLoading(box, 'loading');

    fetch(
        base_url +
        'pages/iStar/permohonan/konvo/ajax/load-akademik-tambahan.php'
    )

    .then(res => {

        if (!res.ok) {
            throw new Error('Gagal load form akademik tambahan');
        }

        return res.text();
    })

    .then(html => {

        box.innerHTML = html;

        //sync and save to DRAFT_KONVO
        syncAkademikTambahanFromTable();
        if (DRAFT_KONVO.akademikTambahan.length) {
            fillAkademikTambahanForm();
        }
        initAutoSaveAkademikTambahan();
        initAutoSaveGredPSM();

        hideLoading();

        requestAnimationFrame(() => {
            initStandardDataTable('#dekanDT');
        });

        akademikTambahanLoaded = true;    
    })

    .catch(err => {

        console.log(err);

        box.innerHTML = `
            <div class="alert alert-danger">
                Gagal load form
            </div>
        `;
    });
}

function initAutoSaveAkademikTambahan() {
    jQuery(document).on(
        'change',
        '#dekanDT tbody select, #dekanDT tbody textarea, #dekanDT tbody input:not([type=file])',
        function () {

            let el = jQuery(this);
            let tr = el.closest('tr');

            // handle DataTables child row
            if (tr.hasClass('child')) {
                tr = tr.prev('tr');
            }

            // fallback safety (kalau nested structure weird)
            if (!tr.data('id')) {
                tr = el.closest('tr').prev('tr');
            }

            let rowId = tr.data('id');
            let field = this.name;
            let value = el.val();

            if (!rowId || !field) return;

            const timerKey = rowId + '_' + field;
            clearTimeout(saveTimers[timerKey]);

            tr.removeClass('row-success row-error');
            tr.addClass('row-saving');
            el.addClass('border-warning');

            saveTimers[timerKey] = setTimeout(() => {
                let existing = DRAFT_KONVO.akademikTambahan.find(x => x.id == rowId);

                if (!existing) {
                    existing = { id: rowId };
                    DRAFT_KONVO.akademikTambahan.push(existing);
                }

                existing[field] = value; // update only changed field
                saveDraft();

                jQuery.ajax({
                    url: base_url + 'pages/iStar/permohonan/konvo/ajax/akademik-tambahan.php?action=updateDekanDraft',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id: rowId,
                        field: field,
                        value: value
                    },
                    success: function () {

                        el.removeClass('border-warning');
                        tr.removeClass('row-saving row-error');
                        tr.addClass('row-success');

                        setTimeout(() => tr.removeClass('row-success'), 1500);
                    },

                    error: function () {

                        el.removeClass('border-warning');
                        tr.removeClass('row-saving row-success');
                        tr.addClass('row-error');

                        setTimeout(() => tr.removeClass('row-error'), 2000);
                    }
                });

            }, 600);
        }
    );    
}

// function initAutoSaveGredPSM() {

//     jQuery(document).on('change blur', '[name="gredPSM"]', function () {

//         const value = jQuery(this).val().trim();

//         DRAFT_KONVO.gredPSM = value;

//         saveDraft();

//         jQuery.ajax({
//             url: base_url + 'pages/iStar/permohonan/konvo/ajax/akademik-tambahan.php?action=updateGredPSM',
//             method: 'POST',
//             dataType: 'json',
//             data: {
//                 gredPSM: value
//             }
//         });

//     });

// }
function initAutoSaveGredPSM() {

    jQuery(document).on('change blur', '[name="gredPSM"]', function () {

        const value = jQuery(this).val().trim();

        if (!DRAFT_KONVO) return;

        DRAFT_KONVO.gredPSM = value;

        // 🔥 ensure latest state saved
        clearTimeout(window._gredPSMtimer);

        window._gredPSMtimer = setTimeout(() => {
            saveDraft();
        }, 300);

    });
}

// Functions related to DRAFT_KONVO
function fillAkademikTambahanForm() {
    if (DRAFT_KONVO.gredPSM !== undefined) {
        jQuery('[name="gredPSM"]').val(DRAFT_KONVO.gredPSM);
    }

    DRAFT_KONVO.akademikTambahan.forEach(item => {

        let tr = jQuery(`#dekanDT tbody tr[data-id="${item.id}"]`);

        if (!tr.length) return;

        Object.keys(item).forEach(key => {

            let field = tr.find(`[name="${key}"]`);

            if (field.length) {
                field.val(item[key]);
            }
        });

    });    
}

function syncAkademikTambahanFromTable() {
    DRAFT_KONVO.akademikTambahan = [];
    const list = [];

    jQuery('#dekanDT tbody tr').each(function () {

        let tr = jQuery(this);

        let row = {
            id: tr.data('id'),
            dokumen_path: ''
        };

        function getValue(field) {
            let input = tr.find(`[name="${field}"]`);
            if (input.length) return input.val();

            let td = tr.find(`td[data-field="${field}"]`);
            if (td.length) return td.text().trim();

            return '';
        }

        row.nama_dokumen = getValue('nama_dokumen');

        // path dokumen
        row.dokumen_path = tr.find('a').data('path') || '';

        list.push(row);

        DRAFT_KONVO.akademikTambahan.push(row);

    });

    //console.log('SYNC ANUGERAH DEKAN:', DRAFT_KONVO.akademikTambahan);
    saveDraft();
}

jQuery(function () {
    //Add Anugerah Dekan
    jQuery(document).on('submit', '#dekanForm', function (e) {

        console.log('Anugerah Dekan Form SUBMIT TRIGGERED');

        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        jQuery.ajax({
            url: base_url + 'pages/iStar/permohonan/konvo/ajax/akademik-tambahan.php?action=addDekanDraft',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

                //console.log('RESPONSE:', res);

                if (res.status === 'ok') {
                    //console.log('RELOAD DEKAN TABLE');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('dekanAddModal')
                    ).hide();

                    Swal.fire({
                        icon: 'success',
                        title: konvoText('swal_success_title', 'Berjaya'),
                        text: res.message || konvoText('record_save_success', 'Rekod berjaya disimpan'),
                        timer: 1500,
                        showConfirmButton: false
                    }); 

                    // reload table shj
                    akademikTambahanLoaded = false;
                    loadAkademikTambahan();              

                } else {
                    //console.log('FAILED TO ADD:', res);
                }
            },

            error: function (xhr) {
                //console.log('AJAX ERROR:', xhr.responseText);

                let msg = konvoText('system_error_try_again', 'Ralat sistem. Cuba lagi.');

                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

                //console.log('AJAX ERROR:', xhr.responseText);
            }

        });
    });
    
    jQuery(document).on('click', '.btn-delete-dekan', function () {

        const btn = jQuery(this);
        const rowId = btn.data('id');

        if (!rowId) {
            console.log('NO ROW ID');
            return;
        }

        Swal.fire({
            title: konvoText('swal_delete_award_title', 'Padam rekod ini?'),
            text: konvoText('swal_delete_warning', 'Tindakan ini tidak boleh dibatalkan!'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: konvoText('swal_confirm_delete', 'Ya, padam'),
            cancelButtonText: konvoText('swal_cancel', 'Batal')
        }).then((result) => {

            if (!result.isConfirmed) return;

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/akademik-tambahan.php?action=deleteDekanDraft',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: rowId
                },

                success: function (res) {

                    if (res.status === 'ok') {

                        Swal.fire({
                            icon: 'success',
                            title: konvoText('swal_success_title', 'Berjaya'),
                            text: res.message || konvoText('record_delete_success', 'Rekod berjaya dipadam'),
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // reload table shj
                        akademikTambahanLoaded = false;
                        loadAkademikTambahan();

                    } else {

                        btn.prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: konvoText('swal_failed_title', 'Gagal'),
                            text: res.message || konvoText('record_delete_failed', 'Gagal padam rekod')
                        });
                    }
                },

                error: function (xhr) {

                    btn.prop('disabled', false);
                    console.log(xhr.responseText);

                    Swal.fire({
                        icon: 'error',
                        title: konvoText('swal_system_error_title', 'Ralat Sistem'),
                        text: konvoText('swal_try_again_later', 'Cuba lagi sebentar lagi')
                    });
                }
            });
        });
    });    
});