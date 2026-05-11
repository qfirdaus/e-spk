const saveTimers = {};
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

            setTimeout(() => {
                initStandardDataTable('#penglibatanDT');
            }, 100);

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
        loadPenglibatan();
    }

});

function initStandardDataTable(tableId) {

    /*
    |--------------------------------------------------------------------------
    | CHECK TABLE EXIST
    |--------------------------------------------------------------------------
    */

    if (!jQuery(tableId).length) return;

    if ($.fn.DataTable.isDataTable(tableId)) {
        jQuery(tableId).DataTable().destroy();
    }

    /*
    |--------------------------------------------------------------------------
    | INIT DATATABLE
    |--------------------------------------------------------------------------
    */

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

            /*
            |--------------------------------------------------------------------------
            | COLUMN BIL
            |--------------------------------------------------------------------------
            */

            {
                targets: 0,
                orderable: false,
                searchable: false,
                width: 60
            },

            /*
            |--------------------------------------------------------------------------
            | LAST COLUMN (TINDAKAN)
            |--------------------------------------------------------------------------
            */

            {
                targets: -1,
                orderable: false,
                searchable: false,
                width: 120
            }

        ],

        /*
        |--------------------------------------------------------------------------
        | AUTO NUMBERING
        |--------------------------------------------------------------------------
        */

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
    function saveRow(rowId, field, value) {
        return jQuery.ajax({
            url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=updateDraft',
            method: 'POST',
            dataType: 'json',
            data: {
            id: rowId,
            field: field,
            value: value
            }
        });
    }

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

});

jQuery('#penglibatanForm').on('submit', function (e) {

    e.preventDefault();

    let form = jQuery(this);

    jQuery.ajax({
        url: base_url + 'pages/iStar/permohonan/konvo/ajax/penglibatan.php?action=addDraft',
        method: 'POST',
        data: form.serialize(),
        dataType: 'json',

        success: function (res) {

            if (res.status === 'ok') {

                showToast(res.message || 'Berjaya tambah rekod');

                form[0].reset();

                bootstrap.Modal.getInstance(
                    document.getElementById('penglibatanAddModal')
                ).hide();

                loadPenglibatan();

            } else {

                // backend return error status
                showToast(res.message || 'Gagal simpan data', 'error');
            }
        },

        error: function (xhr) {

            console.log('AJAX ERROR:', xhr.responseText);

            let msg = 'Ralat sistem. Cuba lagi.';

            // kalau backend return JSON error
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.message) msg = res.message;
            } catch (e) {}

            showToast(msg, 'error');
        }

    });

});

