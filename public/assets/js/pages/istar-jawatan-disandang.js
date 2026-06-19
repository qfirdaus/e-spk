function loadJawatan() {

    if (jawatanLoaded) return;

    const box = document.getElementById('jawatan-content');

    if (!box) return;

    setSectionLoading(box, 'loading');

    fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/load-jawatan.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal load jawatan');
            }

            return res.text();
        })
        .then(html => {

            box.innerHTML = html;
        
            //sync and save to DRAFT_KONVO
            requestAnimationFrame(() => {
                syncJawatanFromTable();  
                if (DRAFT_KONVO.jawatan.length) {
                    fillJawatan();
                }
            });    
            
            initAutoSaveJawatan();

            hideLoading();

            requestAnimationFrame(() => {
                initStandardDataTable('#jawatanDT');
            });

            jawatanLoaded = true;
        })
        .catch(err => {
            console.log(err);
            box.innerHTML = `<div class="text-danger">${konvoText('load_data_failed', 'Gagal load data')}</div>`;
        });

}

function initAutoSaveJawatan() {
    jQuery(document).on(
        'change',
        '#jawatanDT tbody select, #jawatanDT tbody textarea, #jawatanDT tbody input:not([type=file])',
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
            let field = el.attr('name') || el.attr('data-field');// let field = this.name;
            let value = el.val();

            if (!rowId || !field) return;

            const timerKey = rowId + '_' + field;
            clearTimeout(saveTimers[timerKey]);

            tr.removeClass('row-success row-error');
            tr.addClass('row-saving');
            el.addClass('border-warning');

            saveTimers[timerKey] = setTimeout(() => {
                let existing = DRAFT_KONVO.jawatan.find(x => x.id == rowId);

                if (!existing) {
                    existing = { id: rowId };
                    DRAFT_KONVO.jawatan.push(existing);
                }

                existing[field] = value; // update only changed field
                saveDraft();

                jQuery.ajax({
                    url: base_url + 'pages/iStar/permohonan/konvo/ajax/jawatan.php?action=updateJawatanDraft',
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

// Functions related to DRAFT_KONVO
function fillJawatan() {

    DRAFT_KONVO.jawatan.forEach(item => {

        let tr = jQuery(`#jawatanDT tbody tr[data-id="${item.id}"]`);

        if (!tr.length) return;

        Object.keys(item).forEach(key => {

            let field = tr.find(`[name="${key}"]`);

            if (field.length) {
                field.val(item[key]);
            }
            
        });

    });
}

// function sync jawatan to DRAFT_KONVO.jawatan
function syncJawatanFromTable() {

    const list = [];

    jQuery('#jawatanDT tbody tr').each(function () {

        let tr = jQuery(this);
        let id = tr.data('id');
        let sumber = tr.data('type');

        if (!id) return;

        let row = {
            id: id,
            sumber: sumber,
            kod_kategori_aktiviti: '',
            kategori_aktiviti: '',
            nama_bp_program: '',
            id_jawatan: '',
            jawatan: '',
            tarikh_lantikan: '',
            peringkat: '',
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

        row.kod_kategori_aktiviti = getValue('kod_kategori_aktiviti');
        row.kategori_aktiviti = getValue('kategori_aktiviti');
        row.nama_bp_program = getValue('nama_bp_program');
        row.id_jawatan = getValue('id_jawatan');
        row.jawatan = getValue('jawatan');
        row.tarikh_lantikan = getValue('tarikh_lantikan');
        row.peringkat = getValue('peringkat');

        // path dokumen
        row.dokumen_path = tr.find('a').data('path') || '';

        list.push(row);
    });

    DRAFT_KONVO.jawatan = list;
    //console.log('SYNC JAWATAN:', DRAFT_KONVO.jawatan);
    saveDraft();    
}

jQuery(function () {

    // Add New Jawatan
    jQuery(document).on('submit', '#jawatanForm', function (e) {

        //console.log('JAWATAN FORM SUBMIT TRIGGERED');

        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        jQuery.ajax({
            url: base_url + 'pages/iStar/permohonan/konvo/ajax/jawatan.php?action=addJawatanDraft',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

                //console.log('RESPONSE:', res);

                if (res.status === 'ok') {
                    //console.log('RELOAD JAWATAN TABLE');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('jawatanAddModal')
                    ).hide();
                   
                    Swal.fire({
                        icon: 'success',
                        title: konvoText('swal_success_title', 'Berjaya'),
                        text: res.message || konvoText('record_save_success', 'Rekod berjaya disimpan'),
                        timer: 1500,
                        showConfirmButton: false
                    }); 

                    // reload table shj
                    jawatanLoaded = false;
                    loadJawatan();                      

                } else {
                    //console.log('FAILED TO ADD:', res);
                }
            },

            error: function (xhr) {
                //sconsole.log('AJAX ERROR:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: konvoText('swal_failed_title', 'Gagal'),
                    text: xhr.message || konvoText('record_save_failed', 'Rekod tidak berjaya disimpan'),
                    timer: 1500,
                    showConfirmButton: false
                }); 

                let msg = konvoText('system_error_try_again', 'Ralat sistem. Cuba lagi.');

                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

                //console.log('AJAX ERROR:', xhr.responseText);
            }

        });

    });  

    // Delete jawatan
    jQuery(document).on('click', '.btn-delete-jawatan', function () {

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
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/jawatan.php?action=deleteJawatanDraft',
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
                        jawatanLoaded = false;
                        loadJawatan();

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
    
    //Sync ISTAD - Jawatan Disandang
    jQuery(document).on('click', '#syncIstadJawatanBtn', function () {

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
            const box = document.getElementById('jawatanDT');

            if (!box) {
                console.log('BOX NOT FOUND');
                return;
            }            

            setButtonBusy(btn, true, 'syncronizing');
            setSectionLoading(box, 'syncronizing');

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/jawatan.php?action=syncIstadJawatan',
                method: 'POST',
                dataType: 'json',

                success: async function (res) {
                    setButtonBusy(btn, false);
                    
                    if (res.status === 'ok') {

                        Swal.fire({
                            icon: 'success',
                            title: konvoText('sync_success_title', 'Penyelarasan Data Berjaya'),
                            // html: `
                            //     <b> ${konvoText('sync_istad_msg', 'Data berjaya dikemaskini' ) || res.message } </b>
                            // `,
                            confirmButtonText: konvoText('swal_ok', 'OK'),
                            allowOutsideClick: false
                        }).then(async () => {
                            DRAFT_KONVO.jawatan = [];

                            jawatanLoaded = false;
                            loadJawatan();       // reload UI table fresh                            
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