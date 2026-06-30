const saveTimers = {};
let penglibatanLoaded = false;

//console.log('JS LOADED');

function konvoText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

// #### EventListeners ####
document.addEventListener('DOMContentLoaded', async function () {
    await loadDraft();
});

document.addEventListener('shown.bs.tab', function (event) {

    const target = event.target.getAttribute('href') || event.target.getAttribute('data-bs-target') ;
    //console.log('TAB SHOWN:', target);

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

async function loadDraft() {

    try {

        const res = await fetch(
            base_url +
            'pages/rekod-utama/data-kokurikulum/ajax/load-draft.php'
        );

        const data = await res.json();

        console.log('DRAFT LOADED:', data);

        DRAFT_KOKU = {

            draft_initialized: data.draft_initialized || false,

            penglibatan:
                Array.isArray(data.penglibatan)
                    ? data.penglibatan
                    : [],

        };

        // fallback kalau draft kosong
        // if (!DRAFT_KOKU.draft_initialized) {

        //     await Promise.all([
        //         fetch(
        //             base_url +
        //             'pages/rekod-utama/data-kokurikulum/ajax/load-penglibatan-json.php'
        //         )
        //         .then(r => r.json())
        //         .then(r => {
        //             DRAFT_KOKU.penglibatan = r.rows || [];
        //         })

        //     ]);

        //     saveDraft();
        // }

    } catch (err) {

        console.error('loadDraft error:', err);

    }

}

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

    fetch(base_url + 'pages/rekod-utama/data-kokurikulum/ajax/load-penglibatan.php')
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
            if (DRAFT_KOKU.penglibatan.length) {
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

// Function to initialize DataTable
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
        "<'row mb-2'<'col-sm-12 col-md-6 dt-top-left'l><'col-sm-12 col-md-6 d-flex justify-content-md-end dt-top-right'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'dt-bottom-row mt-2 d-flex justify-content-between align-items-center'<'dt-info-left'i><'dt-paging-right d-flex justify-content-end'p>>",
        initComplete: function () {

            let toolbar = '';

            if (tableId === '#penglibatanDT') {

                toolbar = `
                    <div class="d-flex gap-2 ms-2">
                        <button type="button"
                                id="syncIstadBtn"
                                class="btn btn-primary rounded-3">
                            <i class="ri-refresh-line me-1"></i>
                            ${konvoText('sync_istad', 'Sync ISTAD')}
                        </button>

                        <button type="button"
                                id="penglibatanBtnAdd"
                                class="btn btn-success rounded-3">
                            <i class="ri-add-line me-1"></i>
                            ${konvoText('add_new', 'Tambah Baru')}
                        </button>
                    </div>
                `;
            }

            try {
                var $table = jQuery(tableId);
                var $wrapper = $table.closest('.dataTables_wrapper');
                var $topRight = $wrapper.find('.dt-top-right').first();

                if ($topRight && $topRight.length) {
                    $topRight.addClass('align-items-center gap-2 flex-nowrap');
                    $topRight.append(toolbar);
                } else {
                    jQuery(tableId + '_filter').parent().append(toolbar);
                }
            } catch (e) {
                jQuery(tableId + '_filter').parent().append(toolbar);
            }
        },
        language: {
            search: "",
            searchPlaceholder: konvoText('datatable_search_placeholder', 'Search'),
            lengthMenu: konvoText('datatable_length_menu', 'Show _MENU_ records'),
            info: konvoText('datatable_info', 'Showing _START_ to _END_ of _TOTAL_ records'),
            infoEmpty: konvoText('datatable_info_empty', 'Showing 0 to 0 of 0 records'),
            emptyTable: konvoText('datatable_empty_table', 'No records found'),
            zeroRecords: konvoText('datatable_zero_records', 'No matching records'),
            paginate: {
                next: konvoText('datatable_next', 'Next'),
                previous: konvoText('datatable_previous', 'Previous')
            }

        },

        order: [],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                width: '5%',
                className: 'text-center'
            },
            {
                targets: -1,
                orderable: false,
                searchable: false,
                width: '10%',
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
                let existing = DRAFT_KOKU.penglibatan.find(x => x.id == rowId);

                if (!existing) {
                    existing = { id: rowId };
                    DRAFT_KOKU.penglibatan.push(existing);
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

    DRAFT_KOKU.penglibatan = list;
    console.log('SYNC JAWATAN:', DRAFT_KOKU.penglibatan);
    saveDraft();
}

function saveDraft() {

    const payload = JSON.parse(JSON.stringify(DRAFT_KOKU));

    return fetch(base_url + 'pages/rekod-utama/data-kokurikulum/ajax/save-draft.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        console.log('Draft saved');
        return data;
    })
    .catch(err => {
        console.error('Save failed', err);
    });
}

// Functions related to DRAFT_KOKU
function fillPenglibatan() {

    DRAFT_KOKU.penglibatan.forEach(item => {

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