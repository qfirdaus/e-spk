//console.log('istar-konfigurasi.js loaded');

let configDateLoaded = false;

function jsSwalText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

document.addEventListener('DOMContentLoaded', function () {

    const activeTab = document.querySelector(
        'a[href="#tetapan-tarikh-tab"]'
    );

    if (activeTab && activeTab.classList.contains('active')) {
        loadDateConfig();
    }

});

document.addEventListener('shown.bs.tab', function (event) {

    const target = event.target.getAttribute('href');

    if (target === '#tetapan-tarikh-tab') {
        if (!configDateLoaded) {
            loadDateConfig();
        }
    }
});

function loadDateConfig() {
    //console.log('loadDateConfig()');
    if (configDateLoaded) return;

    const box = document.getElementById('tetapan-tarikh-container');

    if (!box) return;

    setSectionLoading(box, 'loading');

    fetch(base_url + 'pages/iStar/konfigurasi/tetapan-tarikh/ajax/load-tetapan-tarikh.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal load tetapan tarikh');
            }
            
            return res.text();
        })
        .then(html => {

            box.innerHTML = html;
            hideLoading();

            requestAnimationFrame(() => {
                initStandardDataTable('#dateConfigDT');
            });

            configDateLoaded = true;
        })
        .catch(err => {
            console.log(err);
            box.innerHTML = `<div class="text-danger">${jsSwalText('load_data_failed', 'Gagal load data')}</div>`;
        });
}

function initStandardDataTable(tableId) {

    if (!jQuery(tableId).length) return;

    if ($.fn.DataTable.isDataTable(tableId)) {
        jQuery(tableId).DataTable().destroy();
    }

    const table = jQuery(tableId).DataTable({
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        autoWidth: false,
        scrollX: false,
        responsive: false,
        dom:
        "<'row mb-2'<'col-sm-12 col-md-6 dt-top-left'l><'col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'dt-bottom-row mt-2 d-flex justify-content-between align-items-center'<'dt-info-left'i><'dt-paging-right d-flex justify-content-end'p>>",
        initComplete: function () {

            let toolbar = `
                <div class="d-flex gap-2 ms-2">
                    <button type="button"
                            id="dateConfigBtnAdd"
                            class="btn btn-success btn-sm rounded-3">
                        <i class="ri-add-line me-1"></i>
                        Tambah Baru
                    </button>
                </div>
            `;
            
            const api = this.api();
            const $container = jQuery(api.table().container());

            const $topRight = $container.find('.dt-top-right');

            if ($topRight.length) {
                $topRight
                    .addClass('d-flex align-items-center gap-2 flex-nowrap')
                    .append(toolbar);
            }
        },        
        language: {
            search: "",
            searchPlaceholder: jsSwalText('datatable_search_placeholder', 'Search'),
            lengthMenu: jsSwalText('datatable_length_menu', 'Show _MENU_ records'),
            info: jsSwalText('datatable_info', 'Showing _START_ to _END_ of _TOTAL_ records'),
            infoEmpty: jsSwalText('datatable_info_empty', 'Showing 0 to 0 of 0 records'),
            emptyTable: jsSwalText('datatable_empty_table', 'No records found'),
            zeroRecords: jsSwalText('datatable_zero_records', 'No matching records'),
            paginate: {
                next: jsSwalText('datatable_next', 'Next'),
                previous: jsSwalText('datatable_previous', 'Previous')
            }

        },

        order: [],
        columnDefs: [
        {
            targets: 0,
            orderable: false,
            searchable: false,
            className: 'details-control text-center',
            width: '40px'
        },
        {
            targets: 1,
            orderable: false,
            searchable: false,
            width: '5%',
            className: 'text-center'
        },
        {
            targets: -1,
            orderable: false,
            searchable: false,
            className: 'text-center'
        }
        ],
        createdRow: function (row, data, dataIndex) {
            var $cell = jQuery('td', row).eq(2);
            var cellText = $cell.text().trim();

            if (cellText.length) {
                $cell.attr('title', cellText);
                $cell.attr('data-bs-toggle', 'tooltip');
                $cell.attr('data-bs-placement', 'top');
            }

            if (typeof bootstrap !== 'undefined') {
                jQuery('[data-bs-toggle="tooltip"]', row).each(function () {
                    if (!this._bsTooltip) {
                        this._bsTooltip = new bootstrap.Tooltip(this, {
                            boundary: 'window',
                            customClass: 'konvo-tooltip'
                        });
                    }
                });
            }
        },
        rowCallback: function (row, data, index) {

            const api = this.api();
            const info = api.page.info();

            //jQuery('td:eq(0)', row)
            jQuery(row)
                .find('td.col-bil')            
                .html(info.start + index + 1);

        },

        drawCallback: function () {
            const api = this.api();

            setTimeout(() => {
                const tableNode = api.table().node(); 
                if (!tableNode) return;

                // child rows (responsive)
                jQuery(tableNode)
                    .find('tbody tr')
                    .each(function () {
                        initDatePicker(this);
                    });

            }, 50);
        },     

        destroy: true,
    });

    return table;
}

jQuery(function () {
    // click btn-add tarikh permohonan
    jQuery(document).on('click', '#dateConfigBtnAdd', function () {
        openModal('dateConfigAddModal');
    });


    //Add New Tarikh Permohonan
    jQuery(document).on('submit', '#dateConfigForm', function (e) {

        //console.log('DATE CONFIG FORM SUBMIT TRIGGERED');

        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        jQuery.ajax({
            url: base_url + 'pages/iStar/konfigurasi/tetapan-tarikh/ajax/tarikh-permohonan.php?action=addDateAppDraft',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

               // console.log('RESPONSE:', res);

                if (res.status === 'ok') {
                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('dateConfigAddModal')
                    ).hide();
                    // console.log(typeof Swal);
                    // console.log(Swal);
                    Swal.fire({
                        icon: 'success',
                        title: jsSwalText('swal_success_title', 'Berjaya'),
                        text: res.message || jsSwalText('record_save_success', 'Rekod berjaya disimpan'),
                        timer: 1500,
                        showConfirmButton: false
                    }); 

                    // reload table shj
                    configDateLoaded = false;
                    loadDateConfig();              

                } else {
                    console.log('FAILED TO ADD:', res);
                }
            },

            error: function  (xhr, status, error) {
                // console.log('XHR:', xhr);
                let msg = jsSwalText('system_error_try_again', 'Ralat sistem. Cuba lagi.');

                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

            }

        });
    });

    // click btn-update tetapan tarikh permohonan
    jQuery(document).on('click', '.btn-update-dateConfig', function () {

        let row = jQuery(this).closest('tr').data('row');

        // kalau string, parse JSON
        if (typeof row === 'string') {
            row = JSON.parse(row);
        }

        // isi modal fields
        jQuery('#update_id').val(row.id);
        jQuery('#update_category').val(row.config_category_award);
        jQuery('#update_session').val(row.config_name);
        jQuery('#update_start_date').val(formatDate(row.start_date));
        jQuery('#update_end_date').val(formatDate(row.end_date));
        jQuery('#update_status').val(row.is_active);

        // buka modal
        openModal('dateConfigUpdateModal');
    });

    // update tetapan tarikh permohonan
    jQuery(document).on('submit', '#dateConfigUpdateForm', function (e) {

        //console.log('UPDATE DATE CONFIG FORM SUBMIT TRIGGERED');

        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        jQuery.ajax({
            url: base_url + 'pages/iStar/konfigurasi/tetapan-tarikh/ajax/tarikh-permohonan.php?action=updateDateAppDraft',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function (res) {

               // console.log('RESPONSE:', res);

                if (res.status === 'ok') {
                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('dateConfigUpdateModal')
                    ).hide();
                    console.log(res.data);
                    Swal.fire({
                        icon: 'success',
                        title: jsSwalText('swal_success_title', 'Berjaya'),
                        text: res.message || jsSwalText('record_save_success', 'Rekod berjaya dikemaskini'),
                        timer: 1500,
                        showConfirmButton: false
                    }); 

                    // reload table shj
                    configDateLoaded = false;
                    loadDateConfig();              

                } else {
                    console.log('FAILED TO UPDATE:', res);

                    bootstrap.Modal.getInstance(
                        document.getElementById('dateConfigUpdateModal')
                    ).hide();

                    Swal.fire({
                        icon: 'error',
                        title: jsSwalText('swal_failed_title', 'Tidak Berjaya'),
                        text: res.message || jsSwalText('record_save_failed', 'Rekod tidak berjaya dikemaskini'),
                        timer: 1500,
                        showConfirmButton: false
                    });                     
                }
            },

            error: function  (xhr, status, error) {
                // console.log('XHR:', xhr);
                let msg = jsSwalText('system_error_try_again', 'Ralat sistem. Cuba lagi.');

                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

            }

        });
    });

    // click btn-delete tetapan tarikh permohonan
    jQuery(document).on('click', '.btn-delete-dateConfig', function () {
        
        const btn = jQuery(this);
        const rowId = btn.data('id');

        if (!rowId) {
            console.log('NO ROW ID');
            return;
        }

        Swal.fire({
            title: jsSwalText('swal_delete_award_title', 'Padam rekod ini?'),
            text: jsSwalText('swal_delete_warning', 'Tindakan ini tidak boleh dibatalkan!'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: jsSwalText('swal_confirm_delete', 'Ya, padam'),
            cancelButtonText: jsSwalText('swal_cancel', 'Batal')
        }).then((result) => {

            if (!result.isConfirmed) return;

            jQuery.ajax({
                url: base_url + 'pages/iStar/konfigurasi/tetapan-tarikh/ajax/tarikh-permohonan.php?action=deleteDateAppDraft',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: rowId
                },

                success: function (res) {

                    if (res.status === 'ok') {

                        Swal.fire({
                            icon: 'success',
                            title: jsSwalText('swal_success_title', 'Berjaya'),
                            text: res.message || jsSwalText('record_delete_success', 'Rekod berjaya dipadam'),
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // reload table shj
                        configDateLoaded = false;
                        loadDateConfig();

                    } else {

                        btn.prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: jsSwalText('swal_failed_title', 'Gagal'),
                            text: res.message || jsSwalText('record_delete_failed', 'Gagal padam rekod')
                        });
                    }
                },

                error: function (xhr) {

                    btn.prop('disabled', false);
                    console.log(xhr.responseText);

                    Swal.fire({
                        icon: 'error',
                        title: jsSwalText('swal_system_error_title', 'Ralat Sistem'),
                        text: jsSwalText('swal_try_again_later', 'Cuba lagi sebentar lagi')
                    });
                }
            });
        });
    });    
});    

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

function formatDate(dateStr) {
    if (!dateStr) return '';

    let d = new Date(dateStr);
    let day = String(d.getDate()).padStart(2,'0');
    let month = String(d.getMonth()+1).padStart(2,'0');
    let year = d.getFullYear();

    return `${day}-${month}-${year}`;
}