function loadAnugerah() {

    if (anugerahLoaded) return;

    const box = document.getElementById('anugerah-content');

    if (!box) return;

    setSectionLoading(box, 'loading');

    fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/load-anugerah.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal load anugerah');
            }

            return res.text();
        })
        .then(html => {

            box.innerHTML = html;
            
            //sync and save to DRAFT_KONVO
            syncAnugerahFromTable();
            if (DRAFT_KONVO.anugerah.length) {
                fillAnugerah();
            }
            initAutoSaveAnugerah();

            hideLoading();

            requestAnimationFrame(() => {
                initStandardDataTable('#anugerahDT');
            });

            anugerahLoaded = true;

        })
        .catch(err => {
            console.log(err);
            box.innerHTML = `<div class="text-danger">${konvoText('load_data_failed', 'Gagal load data')}</div>`;
        });

}

function initAutoSaveAnugerah() {
    jQuery(document).on(
        'change',
        '#anugerahDT tbody select, #anugerahDT tbody textarea, #anugerahDT tbody input:not([type=file])',
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
                let existing = DRAFT_KONVO.anugerah.find(x => x.id == rowId);

                if (!existing) {
                    existing = { id: rowId };
                    DRAFT_KONVO.anugerah.push(existing);
                }

                existing[field] = value; // update only changed field
                saveDraft();

                jQuery.ajax({
                    url: base_url + 'pages/iStar/permohonan/konvo/ajax/anugerah.php?action=updateAnugerahDraft',
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
function fillAnugerah() {

    DRAFT_KONVO.anugerah.forEach(item => {

        let tr = jQuery(`#anugerahDT tbody tr[data-id="${item.id}"]`);

        if (!tr.length) return;

        Object.keys(item).forEach(key => {

            let field = tr.find(`[name="${key}"]`);

            if (field.length) {
                field.val(item[key]);
            }
        });

    });
}

function syncAnugerahFromTable() {
    DRAFT_KONVO.anugerah = [];
    const list = [];

    jQuery('#anugerahDT tbody tr').each(function () {

        let tr = jQuery(this);

        let row = {
            id: tr.data('id'),
            nama_anugerah: '',
            tahun: '',
            kurniaan_pemberian: '',
            peringkat: '',
            dokumen_path: ''
        };

        function getValue(field) {
            let input = tr.find(`[name="${field}"]`);
            if (input.length) return input.val();

            let td = tr.find(`td[data-field="${field}"]`);
            if (td.length) return td.text().trim();

            return '';
        }

        row.nama_anugerah = getValue('nama_anugerah');
        row.tahun = getValue('tahun');
        row.kurniaan_pemberian = getValue('kurniaan_pemberian');
        row.peringkat = getValue('peringkat');

        // path dokumen
        row.dokumen_path = tr.find('a').data('path') || '';

        list.push(row);
    });

    DRAFT_KONVO.anugerah = list;
    //console.log('SYNC ANUGERAH:', DRAFT_KONVO.anugerah);
    saveDraft();
}

// #### JQuery Functions ####
jQuery(function () {

    //Add New Anugerah
    jQuery(document).on('submit', '#anugerahForm', function (e) {

        //console.log('ANUGERAH FORM SUBMIT TRIGGERED');

        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        jQuery.ajax({
            url: base_url + 'pages/iStar/permohonan/konvo/ajax/anugerah.php?action=addAnugerahDraft',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

                //console.log('RESPONSE:', res);

                if (res.status === 'ok') {
                    //console.log('RELOAD ANUGERAH TABLE');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('anugerahAddModal')
                    ).hide();

                    Swal.fire({
                        icon: 'success',
                        title: konvoText('swal_success_title', 'Berjaya'),
                        text: res.message || konvoText('record_save_success', 'Rekod berjaya disimpan'),
                        timer: 1500,
                        showConfirmButton: false
                    }); 

                    // reload table shj
                    anugerahLoaded = false;
                    loadAnugerah();              

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

    jQuery(document).on('click', '.btn-delete-anugerah', function () {

        const btn = jQuery(this);
        const rowId = btn.data('id');

        if (!rowId) {
            console.log('NO ROW ID');
            return;
        }

        Swal.fire({
            title: konvoText('swal_delete_award_title', 'Padam rekod anugerah ini?'),
            text: konvoText('swal_delete_warning', 'Tindakan ini tidak boleh dibatalkan!'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: konvoText('swal_confirm_delete', 'Ya, padam'),
            cancelButtonText: konvoText('swal_cancel', 'Batal')
        }).then((result) => {

            if (!result.isConfirmed) return;

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/anugerah.php?action=deleteAnugerahDraft',
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
                        anugerahLoaded = false;
                        loadAnugerah();

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