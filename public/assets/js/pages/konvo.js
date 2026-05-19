const saveTimers = {};
let penglibatanLoaded = false;
let jawatanLoaded = false;

function showToast(message, type = 'success') {

    let bgClass = 'bg-success';

    if (type === 'error') {
        bgClass = 'bg-danger';
    }

    const toast = `

        <div class="toast align-items-center text-white ${bgClass} border-0 show mb-2">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast">
                </button>
            </div>
        </div>

    `;

    jQuery('.toast-lite').append(toast);

    setTimeout(() => {
        jQuery('.toast-lite .toast').first().remove();
    }, 2500);

}

console.log('KONVO JS LOADED');

function loadPenglibatan() {
    if (penglibatanLoaded){
        console.log('ALREADY LOADED - SKIP');
        return; // stop if already loaded
    } 

    console.log('TAB EVENT FIRED');
    console.log('base_url:', base_url);
    const box = document.getElementById('penglibatan-content');

    if (!box) {
        console.log('BOX NOT FOUND');
        return;
    }

    console.log('LOADING PENGLIBATAN...');

    box.innerHTML = '<div class="text-center py-3">Memuatkan Data...</div>';
    showLoading('loading');

    fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/load-penglibatan.php')
        .then(res => res.text())
        .then(html => {

            box.innerHTML = html;

            console.log('PENGLIBATAN LOADED');
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

    box.innerHTML = 'Loading...';
    showLoading('loading');

    fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/load-jawatan.php')

        .then(res => res.text())
        .then(html => {

            box.innerHTML = html;
            hideLoading();
            
            requestAnimationFrame(() => {
                initStandardDataTable('#jawatanDT');
            });

            jawatanLoaded = true;
        })
        .catch(err => {
            console.log(err);
            box.innerHTML = '<div class="text-danger">Gagal load data</div>';
            hideLoading();
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

                        console.log('AUTO SAVE SUCCESS:', res);

                        el.removeClass('border-warning');
                        tr.removeClass('row-saving row-error');
                        tr.addClass('row-success');

                        showToast('Wakil berjaya dikemaskini');

                        setTimeout(() => {

                            tr.removeClass('row-success');

                        }, 1500);

                    },
                    error: function (xhr) {

                        console.log('SAVE ERROR:', xhr.responseText);

                        el.removeClass('border-warning');
                        tr.removeClass('row-saving row-success');
                        tr.addClass('row-error');

                        showToast('Gagal simpan data', 'error');

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

                    showToast(res.message || 'Dokumen berjaya dikemaskini');

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

                    showToast(res.message || 'Gagal update dokumen', 'error');

                }

            },

            error: function (xhr) {

                console.log(xhr.responseText);

                tr.removeClass('row-saving');
                tr.addClass('row-error');

                showToast('Ralat sistem', 'error');

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

                console.log('RESPONSE:', res);

                if (res.status === 'ok') {
                    penglibatanLoaded = true;

                    showToast(res.message || 'Berjaya tambah rekod');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('penglibatanAddModal')
                    ).hide();

                    //reload page to reflect new data
                    setTimeout(() => {
                        location.reload();
                    }, 150);                

                } else {
                    showToast(res.message || 'Gagal simpan data', 'error');
                }
            },

            error: function (xhr) {
                console.log('AJAX ERROR:', xhr.responseText);

                let msg = 'Ralat sistem. Cuba lagi.';

                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

                showToast(msg, 'error');
            }

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
            btn.prop('disabled', true);
          
            showLoading('syncronizing');

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=syncIstad',
                method: 'POST',
                dataType: 'json',

                success: function (res) {
                    hideLoading(); 
                    btn.prop('disabled', false);

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

                    btn.prop('disabled', false);

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
        '#jawatanDT tbody input, #jawatanDT tbody select, #jawatanDT tbody textarea',
        function () {
            let el = jQuery(this);
            let tr = el.closest('tr');
            let rowId = tr.data('id');
            let field = this.name;
            let value = el.val();

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/jawatan.php?action=updateDraft',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: rowId,
                    field: field,
                    value: value
                }
            });

        }
    );        
});