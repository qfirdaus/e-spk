const saveTimers = {};
let penglibatanLoaded = false;
let jawatanLoaded = false;

console.log('KONVO JS LOADED');

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
    //showLoading('loading');
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
            hideLoading();

            setTimeout(() => {
                initStandardDataTable('#penglibatanDT');
            }, 0);

            penglibatanLoaded = true;
        })
        .catch(err => {
            console.log(err);
            box.innerHTML = '<div class="text-danger">Gagal load data</div>';
        });
}

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
            
            requestAnimationFrame(() => {
                initStandardDataTable('#jawatanDT');
            });

            jawatanLoaded = true;
        })
        .catch(err => {
            console.log(err);
            box.innerHTML = '<div class="text-danger">Gagal load data</div>';
        });

}

// tab listener to load content on demand when tab is shown
document.addEventListener('shown.bs.tab', function (event) {
    console.log('BOOTSTRAP TAB LISTENER READY');
    const target = event.target.getAttribute('href');

    console.log('TAB SHOWN:', target);

    if (target === '#penglibatan-program-tab') {
        if (!penglibatanLoaded) {
            loadPenglibatan();
        }
    }

    if (target === '#jawatan-disandang-tab') {

        if (!jawatanLoaded) {
            loadJawatan();
        }

    }    

});


function initStandardDataTable(tableId) {

    if (!jQuery(tableId).length) return;

    if ($.fn.DataTable.isDataTable(tableId)) {
        jQuery(tableId).DataTable().destroy();
    }

    jQuery(tableId).DataTable({
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        autoWidth: false,
        scrollX: false,
        responsive: true,
        dom:
        "<'row mb-2 align-items-center dt-header'" +
            "<'col-md-6 d-flex align-items-center dt-left'l>" +
            "<'col-md-6 d-flex justify-content-end align-items-center gap-2 dt-right'f>" +
        ">" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",
        initComplete: function () {

            let toolbar = '';

            if (tableId === '#penglibatanDT') {

                toolbar = `
                    <div class="d-flex gap-2 ms-2">
                        <button type="button"
                                id="syncIstadBtn"
                                class="btn btn-primary rounded-3">
                            <i class="ri-refresh-line me-1"></i>
                            Sync IStAD
                        </button>

                        <button type="button"
                                id="penglibatanBtnAdd"
                                class="btn btn-success rounded-3">
                            <i class="ri-add-line me-1"></i>
                            Tambah Baru
                        </button>
                    </div>
                `;
            }

            if (tableId === '#jawatanDT') {

                toolbar = `
                    <div class="d-flex gap-2 ms-2">
                        <button type="button"
                                id="jawatanBtnAdd"
                                class="btn btn-success rounded-3">
                            <i class="ri-add-line me-1"></i>
                            Tambah Baru
                        </button>
                    </div>
                `;
            }

            jQuery(tableId + '_filter').parent().append(toolbar);
        },
        language: {
            search: "",
            searchPlaceholder: "Search",
            lengthMenu: "Show _MENU_ records",
            info: "Showing _START_ to _END_ of _TOTAL_ records",
            infoEmpty: "Showing 0 to 0 of 0 records",
            emptyTable: "No records found",
            zeroRecords: "No matching records",
            paginate: {
                next: "Next",
                previous: "Previous"
            }

        },

        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                width: 60
            },
            {  // Tindakan 
                targets: -1,
                orderable: false,
                searchable: false,
                width: 150
            }

        ],
        rowCallback: function (row, data, index) {

            const api = this.api();

            const info = api.page.info();

            jQuery('td:eq(0)', row)
                .html(info.start + index + 1);

        },

        destroy: true,
    });

}

function openModal(modalId) {

    const modalEl = document.getElementById(modalId);

    if (!modalEl) {
        console.log('MODAL NOT FOUND:', modalId);
        return;
    }

    if (!window.bootstrap) {
        console.error('BOOTSTRAP NOT READY');
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

jQuery(document).on('click', '#penglibatanBtnAdd', function () {
    openModal('penglibatanAddModal');
});

jQuery(document).on('click', '#jawatanBtnAdd', function () {
    openModal('jawatanAddModal');
});

jQuery(document).on('click', '#anugerahBtnAdd', function () {
    openModal('anugerahAddModal');
});

jQuery(function () {
    
    // Update Penglibatan
    jQuery(document).on(
        'change',
        '#penglibatanDT tbody select, #penglibatanDT tbody textarea, #penglibatanDT tbody input:not([type=file])',
        function () {

            let el = jQuery(this);
            let tr = el.closest('tr');
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

    // Update Document Penglibatan - kemaskini dokumen secara inline bila file dipilih
    jQuery(document).on('change', '.dokumen-inline', function () {

        const input = this;

        if (!input.files.length) return;

        const file = input.files[0];
        const rowId = jQuery(this).data('id');
        const tr = jQuery(this).closest('tr');
        const formData = new FormData();

        formData.append('id', rowId);
        formData.append('dokumen', file);

        tr.removeClass('row-success row-error');
        tr.addClass('row-saving');

        jQuery.ajax({

            url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=updateDokumen',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

                tr.removeClass('row-saving');

                if (res.status === 'ok') {

                    tr.addClass('row-success');
                    //console.log('FILE UPLOAD SUCCESS:', res);

                    // UPDATE LINK VIEW BUTTON
                    const filePath = res.path; // <-- kena return dari backend

                    if (filePath) {
                        tr.find('a.btn-outline-warning').attr('href', base_url + filePath);
                    }

                    setTimeout(() => {
                        tr.removeClass('row-success');
                    }, 1500);

                } else {

                    tr.addClass('row-error');
                    //console.log('FILE UPLOAD FAILED:', res);

                }

            },

            error: function (xhr) {

                tr.removeClass('row-saving');
                tr.addClass('row-error');

                console.log('FILE UPLOAD ERROR:', xhr.responseText);
            }

        });

    });    

    // button trigger for upload document penglibatan
    jQuery(document).on('click', '.upload-btn', function () {
        const tr = jQuery(this).closest('tr');
        const input = tr.find('.dokumen-inline')[0];

        if (input) {
            input.click();
        } else {
            console.log('FILE INPUT NOT FOUND');
        }
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
            title: 'Padam rekod ini?',
            text: "Tindakan ini tidak boleh dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, padam',
            cancelButtonText: 'Batal'
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
                            title: 'Berjaya',
                            text: res.message || 'Rekod berjaya dipadam',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        const table = jQuery('#penglibatanDT').DataTable();

                        table
                            .row(btn.closest('tr'))
                            .remove()
                            .draw(false);

                    } else {

                        btn.prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Gagal padam rekod'
                        });
                    }
                },

                error: function (xhr) {

                    btn.prop('disabled', false);

                    console.log(xhr.responseText);

                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat Sistem',
                        text: 'Cuba lagi sebentar lagi'
                    });
                }

            });

        });

    });   

    // Add New Penglibatan
    jQuery(document).on('submit', '#penglibatanForm', function (e) {

        console.log('PENGLIBATAN FORM SUBMIT TRIGGERED');

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
                    penglibatanLoaded = true;
                    //console.log('RELOAD PENGLIBATAN TABLE');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('penglibatanAddModal')
                    ).hide();

                    //reload page to reflect new data
                    setTimeout(() => {
                        location.reload();
                    }, 150);                

                } else {
                    //console.log('FAILED TO ADD:', res);
                }
            },

            error: function (xhr) {
                //console.log('AJAX ERROR:', xhr.responseText);

                let msg = 'Ralat sistem. Cuba lagi.';

                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

                //console.log('AJAX ERROR:', xhr.responseText);
            }

        });

    });    

    jQuery(document).on('submit', '#anugerahForm', function (e) {

        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        jQuery.ajax({
            url: base_url + 'pages/iStar/permohonan/konvo/ajax/anugerah.php?action=addDraft',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

                if (res.status === 'ok') {

                    showToast(res.message || 'Rekod anugerah berjaya ditambah');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('anugerahAddModal')
                    ).hide();

                    setTimeout(() => {
                        location.reload();
                    }, 150);

                    return;
                }

                showToast(res.message || 'Gagal simpan rekod anugerah', 'error');
            },

            error: function (xhr) {

                console.log('ANUGERAH AJAX ERROR:', xhr.responseText);

                let msg = 'Ralat sistem. Cuba lagi.';

                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

                showToast(msg, 'error');
            }
        });
    });

    jQuery(document).on('click', '.btn-delete-anugerah', function () {

        const btn = jQuery(this);
        const rowId = btn.data('id');

        if (!rowId) {
            showToast('ID rekod anugerah tidak sah', 'error');
            return;
        }

        Swal.fire({
            title: 'Padam rekod anugerah ini?',
            text: 'Tindakan ini tidak boleh dibatalkan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, padam',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (!result.isConfirmed) return;

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/anugerah.php?action=deleteDraft',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: rowId
                },

                success: function (res) {

                    if (res.status === 'ok') {
                        showToast(res.message || 'Rekod anugerah berjaya dipadam');
                        setTimeout(() => {
                            location.reload();
                        }, 150);
                        return;
                    }

                    showToast(res.message || 'Gagal padam rekod anugerah', 'error');
                },

                error: function (xhr) {
                    console.log('ANUGERAH DELETE ERROR:', xhr.responseText);
                    showToast('Ralat sistem', 'error');
                }
            });
        });
    });

    //Sync IStAD
    jQuery(document).on('click', '#syncIstadBtn', function () {

        Swal.fire({
            title: 'Sync data IStAD?',
            text: 'Data IStAD akan dikemaskini semula. Data Tambahan tidak akan berubah.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, sync',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (!result.isConfirmed) return;

            const btn = jQuery(this);
            setButtonBusy(btn, true, 'syncronizing');

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=syncIstad',
                method: 'POST',
                dataType: 'json',

                success: function (res) {
                    setButtonBusy(btn, false);

                    if (res.status === 'ok') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Penyelarasan Data Berjaya',
                            html: `
                                <b> ${res.message || ''} </b>
                            `,
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then(() => {
                            // reload table shj
                            penglibatanLoaded = false;
                            loadPenglibatan();
                        });

                    } else {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Penyelarasan data gagal'
                        });
                    }
                },

                error: function (xhr) {
                    console.log('AJAX ERROR:', xhr.responseText);

                    setButtonBusy(btn, false);

                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat sistem',
                        text: 'Cuba lagi'
                    });
                }
            });

        });

    });    

    //Update Jawatan Disandang
    jQuery(document).on(
        'change',
        '#jawatanDT tbody select, #jawatanDT tbody textarea, #jawatanDT tbody input:not([type=file])',
        function () {           
            //console.log('SELECT CHANGED');
            let el = jQuery(this);
            let tr = el.closest('tr');
            let rowId = tr.data('id');
            let field = this.name;
            let value = el.val();

            if (!rowId || !field) return;

            const timerKey = rowId + '_' + field;
            clearTimeout(saveTimers[timerKey]);
            //console.log('FIELD CHANGED:', field, 'VALUE:', value);

            tr.removeClass('row-success row-error');
            tr.addClass('row-saving');
            el.addClass('border-warning');

            saveTimers[timerKey] = setTimeout(() => {

                jQuery.ajax({
                    url: base_url + 'pages/iStar/permohonan/konvo/ajax/jawatan.php?action=updateJawatanDraft',
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
          
});