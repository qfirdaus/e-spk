function loadPenglibatan() {

    if (penglibatanLoaded){
        //console.log('ALREADY LOADED - SKIP');
        return; // stop if already loaded
    } 

    //console.log('TAB EVENT FIRED');
    //console.log('base_url:', base_url);
    const box = document.getElementById('penglibatan-content');

    if (!box) {
        console.log('BOX NOT FOUND');
        return;
    }

    //console.log('LOADING PENGLIBATAN...');
    setSectionLoading(box, 'loading');

    fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/load-penglibatan.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal load penglibatan');
            }

            return res.text();
        })
        .then(html => {

            box.innerHTML = html;
            //console.log('PENGLIBATAN LOADED');

            //sync and save to DRAFT_KONVO
            syncPenglibatanFromTable();
            if (DRAFT_KONVO.penglibatan.length) {
                fillPenglibatan();
            }
            initAutoSavePenglibatan();

            hideLoading();

            setTimeout(() => {
                initStandardDataTable('#penglibatanDT');
            }, 0);

            penglibatanLoaded = true;
        })
        .catch(err => {
            console.log(err);
            box.innerHTML = `<div class="text-danger">${konvoText('load_data_failed', 'Gagal load data')}</div>`;
        });
}

function initAutoSavePenglibatan() {
    jQuery(document).on(
        'change',
        '#penglibatanDT tbody select, #penglibatanDT tbody textarea, #penglibatanDT tbody input:not([type=file])',
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
                let existing = DRAFT_KONVO.penglibatan.find(x => x.id == rowId);

                if (!existing) {
                    existing = { id: rowId };
                    DRAFT_KONVO.penglibatan.push(existing);
                }

                existing[field] = value; // update only changed field
                saveDraft();

                jQuery.ajax({
                    url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=updateDraft',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id: rowId,
                        field: field,
                        value: value
                    },
                    success: function (res) {

                        //console.log('AUTO SAVE SUCCESS:', res);

                        el.removeClass('border-warning');
                        tr.removeClass('row-saving row-error');
                        tr.addClass('row-success');

                        setTimeout(() => {

                            tr.removeClass('row-success');

                        }, 1500);

                    },
                    error: function (xhr) {

                        console.log('SAVE ERROR:', xhr.responseText);

                        el.removeClass('border-warning');
                        tr.removeClass('row-saving row-success');
                        tr.addClass('row-error');

                        setTimeout(() => {

                            tr.removeClass('row-error');

                        }, 2000);

                    }
                });

            }, 600);

        }
    );    
}

// Functions related to DRAFT_KONVO
function fillPenglibatan() {

    DRAFT_KONVO.penglibatan.forEach(item => {

        let tr = jQuery(`#penglibatanDT tbody tr[data-id="${item.id}"]`);

        if (!tr.length) return;

        Object.keys(item).forEach(key => {

            let field = tr.find(`[name="${key}"]`);

            if (field.length) {
                field.val(item[key]);
            }

        });

    });

}

function syncPenglibatanFromTable() {

    const list = [];

    jQuery('#penglibatanDT tbody tr').each(function () {

        let tr = jQuery(this);
        let id = tr.data('id');
        let sumber = tr.data('type');

        if (!id) return;

        let row = {
            id: id,
            sumber: sumber,
            nama: '',
            tarikh: '',
            wakil: '',
            peringkat: '',
            pencapaian: '',
            dokumen_path: ''
        };

        // helper ambil value (input > text fallback)
        function getValue(field) {
            let input = tr.find(`[name="${field}"]`);
            if (input.length) return input.val();

            let td = tr.find(`td[data-field="${field}"]`);
            if (td.length) return td.text().trim();

            return '';
        }

        row.nama = getValue('nama');
        row.tarikh = getValue('tarikh');
        row.wakil = getValue('wakil');
        row.peringkat = getValue('peringkat');
        row.pencapaian = getValue('pencapaian');

        // path dokumen
        row.dokumen_path = tr.find('a').data('path') || '';

        list.push(row);
    });

    DRAFT_KONVO.penglibatan = list;
    console.log('SYNC JAWATAN:', DRAFT_KONVO.jawatan);
    saveDraft();
}

jQuery(function () {
    // Add New Penglibatan
    jQuery(document).on('submit', '#penglibatanForm', function (e) {

        //console.log('PENGLIBATAN FORM SUBMIT TRIGGERED');

        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        jQuery.ajax({
            url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=addDraft',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

                //console.log('RESPONSE:', res);

                if (res.status === 'ok') {
                    //console.log('RELOAD PENGLIBATAN TABLE');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('penglibatanAddModal')
                    ).hide();

                    //reload page to reflect new data
                    setTimeout(() => {
                        location.reload();
                    }, 150);                

                    // reload table shj
                    penglibatanLoaded = false;
                    loadPenglibatan();   

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

    // Delete penglibatan
    jQuery(document).on('click', '.btn-delete-penglibatan', function () {

        const btn = jQuery(this);
        const rowId = btn.data('id');

        if (!rowId) {
            console.log('NO ROW ID');
            return;
        }

        Swal.fire({
            title: konvoText('swal_delete_record_title', 'Padam rekod ini?'),
            text: konvoText('swal_delete_warning', 'Tindakan ini tidak boleh dibatalkan!'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: konvoText('swal_confirm_delete', 'Ya, padam'),
            cancelButtonText: konvoText('swal_cancel', 'Batal')
        }).then((result) => {

            if (!result.isConfirmed) return;

            btn.prop('disabled', true);

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=deleteDraft',
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
                        penglibatanLoaded = false;
                        loadPenglibatan();

                        //console.log('RECORD DELETED, RELOAD TABLE');
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
    
    //Sync ISTAD
    jQuery(document).on('click', '#syncIstadBtn', function () {

        Swal.fire({
            title: konvoText('sync_istad_title', 'Sync data ISTAD?'),
            text: konvoText('sync_istad_text', 'Data ISTAD akan dikemaskini semula. Data Tambahan tidak akan berubah.'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: konvoText('sync_istad_confirm', 'Ya, sync'),
            cancelButtonText: konvoText('swal_cancel', 'Batal')
        }).then((result) => {

            if (!result.isConfirmed) return;

            const btn = jQuery(this);
            const box = document.getElementById('penglibatanDT');

            if (!box) {
                console.log('BOX NOT FOUND');
                return;
            }            

            setButtonBusy(btn, true, 'syncronizing');
            setSectionLoading(box, 'syncronizing');
            
            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=syncIstad',
                method: 'POST',
                dataType: 'json',

                success: async function (res) {
                    setButtonBusy(btn, false);

                    if (res.status === 'ok') {

                        Swal.fire({
                            icon: 'success',
                            title: konvoText('sync_success_title', 'Penyelarasan Data Berjaya'),
                            // html: `
                            //     <b> ${konvoText('sync_istad_msg', res.message) } || '' } </b>
                            // `,
                            confirmButtonText: konvoText('swal_ok', 'OK'),
                            allowOutsideClick: false
                        }).then(async () => {
                            DRAFT_KONVO.penglibatan = [];

                            penglibatanLoaded = false;
                            loadPenglibatan();       // reload UI table fresh  
                        });

                    } else {

                        Swal.fire({
                            icon: 'error',
                            title: konvoText('swal_failed_title', 'Gagal'),
                            text: res.message || konvoText('sync_failed', 'Penyelarasan data gagal')
                        });
                    }
                },

                error: function (xhr) {
                    console.log('AJAX ERROR:', xhr.responseText);

                    setButtonBusy(btn, false);

                    Swal.fire({
                        icon: 'error',
                        title: konvoText('swal_system_error_title', 'Ralat sistem'),
                        text: konvoText('swal_try_again', 'Cuba lagi')
                    });
                }
            });

        });

    });   

});