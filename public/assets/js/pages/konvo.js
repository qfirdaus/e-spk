const saveTimers = {};
let penglibatanLoaded = false;

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

    fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/load-penglibatan.php')
        .then(res => res.text())
        .then(html => {

            box.innerHTML = html;

            console.log('PENGLIBATAN LOADED');

            requestAnimationFrame(() => {
                initStandardDataTable('#penglibatanDT');
            });

            penglibatanLoaded = true;
        })
        .catch(err => {
            console.log(err);
            box.innerHTML = '<div class="text-danger">Gagal load data</div>';
        });
}


document.addEventListener('shown.bs.tab', function (event) {
    console.log('BOOTSTRAP TAB LISTENER READY');
    const target = event.target.getAttribute('href');

    console.log('TAB SHOWN:', target);

    if (target === '#penglibatan-program-tab') {
        if (!penglibatanLoaded) {
            loadPenglibatan();
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
            '<"row mb-2"' +
                '<"col-sm-12 col-md-6"l>' +
                '<"col-sm-12 col-md-6 d-flex justify-content-end"f>' +
            '>' +

            't' +

            '<"row mt-2"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6 d-flex justify-content-end"p>' +
            '>',

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

            {
                targets: -1,
                orderable: false,
                searchable: false,
                width: 120
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
    // Update
    jQuery(document).on(
        'change',
        '#penglibatanDT tbody select, #penglibatanDT tbody input, #penglibatanDT tbody textarea',
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

    // Delete
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

    // Add New
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

            jQuery.ajax({
                url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=syncIstad',
                method: 'POST',
                dataType: 'json',

                success: function (res) {

                    btn.prop('disabled', false);

                    if (res.status === 'ok') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Sync Berjaya',
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
                            text: res.message || 'Sync gagal'
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
});