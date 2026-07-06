//console.log('js loaded');

let ploDataLoaded = false;

function jsSwalText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

document.addEventListener('DOMContentLoaded', function () {

    // tab class 'active' 
    const initialActiveTab = document.querySelector('.nav-link.active, .nav-item .active');
    if (initialActiveTab) {
        handleTabLoad(initialActiveTab.getAttribute('href'));
    }

    // tab clicked
    jQuery('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const targetTabHref = jQuery(e.target).attr('href'); 
        handleTabLoad(targetTabHref);
    });

    // function tab
    function handleTabLoad(tabHref) {
        switch (tabHref) {
            case '#maklumat-plo-tab':
                loadDataPLO();
                break;

            default:
                break;
        }
    }

    $(document).on('submit', '#form-application-date', function (e) {
        e.preventDefault(); 
        
        submitApplicationDate(this);
    });      


});


function loadDataPLO() {
    //console.log('loadDataPLO()');
    if (ploDataLoaded) return;

    const box = document.getElementById('maklumat-plo-container');

    if (!box) return;

    setSectionLoading(box, 'loading');

    fetch(base_url + 'pages/admin-spk/tetapan/maklumat-plo/ajax/load-maklumat-plo.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal load senarai PLO');
            }
            
            return res.text();
        })
        .then(html => {

            box.innerHTML = html;
            hideLoading();

            requestAnimationFrame(() => {
                initStandardDataTable('#dataPLODT');
            });

            ploDataLoaded = true;
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
        
        // --- TUKAR DI SINI ---
        autoWidth: false,      // Biar CSS yang tentukan lebar kolum
        scrollX: false,        // Tutup skrol melintang supaya jadual terpaksa fit kontainer
        responsive: false,     // Tutup fungsi child rows / lipatan otomatis
        // ---------------------
        
        dom:
        "<'row mb-2'<'col-sm-12 col-md-6 dt-top-left'l><'col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'dt-bottom-row mt-2 d-flex justify-content-between align-items-center'<'dt-info-left'i><'dt-paging-right d-flex justify-content-end'p>>",
        
        initComplete: function () {
            let btnAdd = ''; 

            if (tableId === '#dataPLODT') {
                btnAdd = 'dataPLOBtnAdd'; 
            }   

            let toolbar = `
                <div class="d-flex gap-2 ms-2">
                    <button type="button"
                            id="${btnAdd}"
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
            // Paksa kolum keterangan (contoh index ke-2) atau semua kolum teks menerima style bungkus teks
            {
                targets: '_all',
                className: 'text-wrap'
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
            jQuery(row).find('td.col-bil').html(info.start + index + 1);
        },
        drawCallback: function () {
            const api = this.api();
            setTimeout(() => {
                const tableNode = api.table().node(); 
                if (!tableNode) return;
                jQuery(tableNode).find('tbody tr').each(function () {
                    if (typeof initDatePicker === 'function') {
                        initDatePicker(this);
                    }
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
       // jQuery('#update_status').val(row.is_active);

        // buka modal
        openModal('dateConfigUpdateModal');
    });

    // button toggle override status
    jQuery(document).on('click', '.btn-override-dateConfig', function () {
        var icon = jQuery(this).find('i');

        icon.toggleClass('ri-toggle-line ri-toggle-fill');        
    });

    // update tetapan tarikh permohonan
    jQuery(document).on('submit', '#form-upd-application-date', function (e) {

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

function submitApplicationDate(formElement) {
    const formData = new FormData(formElement);

    const controllerUrl = base_url + 'pages/iStar/konfigurasi/tetapan-tarikh/ajax/submit-tarikh-permohonan.php';

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        bootstrap.Modal.getInstance(
            document.getElementById('dateConfigAddModal')
        ).hide();
                                
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berjaya',
                text: res.message || 'Rekod berjaya disimpan',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message || 'Gagal menyimpan rekod'
            });
        }
    })
    .catch(err => {
        console.log(err);
        Swal.fire({
            icon: 'error',
            title: 'Ralat',
            text: 'Ralat pelayan (Server Error) semasa menyimpan data'
        });
    });
}

// function updateApplicationDate(formElement) {
//     const formData = new FormData(formElement);

//     const controllerUrl = base_url + 'pages/iStar/konfigurasi/tetapan-tarikh/ajax/update-tarikh-permohonan.php';

//     fetch(controllerUrl, {
//         method: 'POST',
//         body: formData
//     })
//     .then(res => res.json())
//     .then(res => {
//         bootstrap.Modal.getInstance(
//             document.getElementById('dateConfigUpdateModal')
//         ).hide();
                                
//         if (res.status === 'success') {
//             Swal.fire({
//                 icon: 'success',
//                 title: 'Berjaya',
//                 text: res.message || 'Rekod berjaya disimpan',
//                 confirmButtonText: 'OK'
//             }).then(() => {
//                 window.location.reload();
//             });

//         } else {
//             Swal.fire({
//                 icon: 'error',
//                 title: 'Gagal',
//                 text: res.message || 'Gagal menyimpan rekod'
//             });
//         }
//     })
//     .catch(err => {
//         console.log(err);
//         Swal.fire({
//             icon: 'error',
//             title: 'Ralat',
//             text: 'Ralat pelayan (Server Error) semasa menyimpan data'
//         });
//     });    
// }