const saveTimers = {};
let penglibatanLoaded = false;
let jawatanLoaded = false;
let perakuanloaded = false;
let anugerahLoaded = false;

//console.log('KONVO JS LOADED');

function konvoText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

// #### EventListeners ####
document.addEventListener('DOMContentLoaded', async function () {
    await loadDraft();
    
    const checkboxes = document.querySelectorAll('.chk');
    const button = document.getElementById('btn-submit-istar-konvo');

    checkboxes.forEach(chk => {
        chk.addEventListener('change', () => {
            if (!button) return;

            let allChecked = true;
            checkboxes.forEach(c => {
                if (!c.checked) allChecked = false;
            });
            button.disabled = !allChecked;
        });
    });  
    initDatePicker();
});

// tab listener to load content on demand when tab is shown
document.addEventListener('shown.bs.tab', function (event) {

    const target = event.target.getAttribute('href');
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

    if(target === '#anugerah-pengiktirafan-tab') {

        if (!anugerahLoaded) {
            loadAnugerah();
        }
    }   
    
    if (target === '#perakuan-pemohon-tab') {
        if (!perakuanloaded && DRAFT_KONVO.perakuan) {
            initPerakuan();
        }
    }       

});

document.addEventListener('change', function (e) {    
    if (e.target && e.target.classList.contains('kategori-select')) {

        let selected = e.target.options[e.target.selectedIndex];
        let ID_aktiviti = selected.dataset.idaktiviti || '';
        let Text_aktiviti = selected.dataset.aktiviti_text || '';

        // dalam table
        let row = e.target.closest('tr');
        if (row) {
            let aktivitiId = row.querySelector('.id-Aktiviti');
            let aktivitiText = row.querySelector('.aktiviti-Text');

            if (aktivitiId) {
                aktivitiId.value = ID_aktiviti;
            }
            if (aktivitiText) {
                aktivitiText.value = Text_aktiviti;
            }

            jQuery(aktivitiId).trigger('change');
            jQuery(aktivitiText).trigger('change');            
            return;
        }

        // modal
        let modal = e.target.closest('.modal') || document;
        let aktivitiId = modal.querySelector('.id-Aktiviti');
        let aktivitiText = modal.querySelector('.aktiviti-Text');
        if (aktivitiId) {
            aktivitiId.value = ID_aktiviti;
        }
        if (aktivitiText) {
            aktivitiText.value = Text_aktiviti;
        }
    }  

    if (e.target && e.target.classList.contains('jawatan-select')) {

        let selected = e.target.options[e.target.selectedIndex];
        let text = selected.dataset.jawatan_text || '';

        // dalam table
        let row = e.target.closest('tr');
        if (row) {
            let jawatanText = row.querySelector('.jawatan-text');
            if (jawatanText) {
                jawatanText.value = text;
            }

            jQuery(jawatanText).trigger('change');  
            return;
        }

        // dalam modal / form biasa
        let modal = e.target.closest('.modal') || document;
        let jawatanText = modal.querySelector('.jawatan-text');

        if (jawatanText) {
            jawatanText.value = text;
        }
    }   
});

// #### Functions Load ####
async function loadDraft() {

    try {

        const res = await fetch(
            base_url + 'pages/iStar/permohonan/konvo/ajax/load-draft.php'
        );

        const data = await res.json();

        console.log('DRAFT LOADED:', data);

        let keepDataStudent = DRAFT_KONVO.dataStudent;

        DRAFT_KONVO = data || {};

        DRAFT_KONVO.dataStudent = keepDataStudent;

        const existingPerakuan = DRAFT_KONVO.perakuan || {};
        const incomingPerakuan = data.perakuan || {};

        DRAFT_KONVO.perakuan = {
            chk1: Number(incomingPerakuan.chk1 ?? existingPerakuan.chk1 ?? 0),
            chk2: Number(incomingPerakuan.chk2 ?? existingPerakuan.chk2 ?? 0),
            chk3: Number(incomingPerakuan.chk3 ?? existingPerakuan.chk3 ?? 0)
        };

        if (!Array.isArray(DRAFT_KONVO.penglibatan)) {
            DRAFT_KONVO.penglibatan = data.penglibatan || [];
        }
        if (DRAFT_KONVO.penglibatan.length === 0 && Array.isArray(data.penglibatan)) {
            DRAFT_KONVO.penglibatan = data.penglibatan;
        }
        
        if (!Array.isArray(DRAFT_KONVO.jawatan)) {
            DRAFT_KONVO.jawatan = data.jawatan || [];
        }

        if (DRAFT_KONVO.jawatan.length === 0 && Array.isArray(data.jawatan)) {
            DRAFT_KONVO.jawatan = data.jawatan;
        }
        
        if (!Array.isArray(DRAFT_KONVO.anugerah)) {
            DRAFT_KONVO.anugerah = data.anugerah || [];
        }

        if (DRAFT_KONVO.anugerah.length === 0 && Array.isArray(data.anugerah)) {
            DRAFT_KONVO.anugerah = data.anugerah;
        }

    } catch (err) {

        console.error(err);

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
            syncJawatanFromTable();
            if (DRAFT_KONVO.jawatan.length) {
                fillJawatan();
            }
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

// #### Functions Initiate ####
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
                            ${konvoText('sync_istad', 'Sync IStAD')}
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

            if (tableId === '#jawatanDT') {

                toolbar = `
                    <div class="d-flex gap-2 ms-2">
                        <button type="button"
                                id="syncIstadJawatanBtn"
                                class="btn btn-primary rounded-3">
                            <i class="ri-refresh-line me-1"></i>
                            ${konvoText('sync_istad', 'Sync IStAD')}
                        </button>

                        <button type="button"
                                id="jawatanBtnAdd"
                                class="btn btn-success rounded-3">
                            <i class="ri-add-line me-1"></i>
                            ${konvoText('add_new', 'Tambah Baru')}
                        </button>
                    </div>
                `;
            }

            if (tableId === '#anugerahDT') {

                toolbar = `
                    <div class="d-flex gap-2 ms-2">
                        <button type="button"
                                id="anugerahBtnAdd"
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
            let field = this.name;
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

function initPerakuan() {

    const form = document.getElementById('formPerakuan');
    const btn = form?.querySelector('button[type="submit"]');

    if (!form || !btn) return;

    const chk1 = document.getElementById('chk1');
    const chk2 = document.getElementById('chk2');
    const chk3 = document.getElementById('chk3');

    if (!DRAFT_KONVO.perakuan) {
        DRAFT_KONVO.perakuan = {};
    }

    function updateState() {

        DRAFT_KONVO.perakuan.chk1 = chk1.checked ? 1 : 0;
        DRAFT_KONVO.perakuan.chk2 = chk2.checked ? 1 : 0;
        DRAFT_KONVO.perakuan.chk3 = chk3.checked ? 1 : 0;

        const allChecked = chk1.checked && chk2.checked && chk3.checked;

        btn.disabled = !allChecked;

        saveDraft();
    }

    chk1.addEventListener('change', updateState);
    chk2.addEventListener('change', updateState);
    chk3.addEventListener('change', updateState);

    fillPerakuan(); 
    updateState();
    perakuanloaded = true;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitPermohonan();
    });    
}

// #### Functions Modal ####
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

function fillPerakuan() {

    const chk1 = document.getElementById('chk1');
    const chk2 = document.getElementById('chk2');
    const chk3 = document.getElementById('chk3');

    if (!chk1 || !chk2 || !chk3) return;

    chk1.checked = DRAFT_KONVO.perakuan?.chk1 == 1;
    chk2.checked = DRAFT_KONVO.perakuan?.chk2 == 1;
    chk3.checked = DRAFT_KONVO.perakuan?.chk3 == 1;
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

        // semua input dalam row
        // tr.find('input, select, textarea').each(function () {
        //     const name = this.name;
        //     if (name) {
        //         row[name] = jQuery(this).val();
        //     }
        // });    

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

    saveDraft();
}

function syncJawatanFromTable() {

    // kalau draft dah ada data, jangan overwrite
    if (DRAFT_KONVO.jawatan.length) {
        return;
    }

    DRAFT_KONVO.jawatan = [];

    jQuery('#jawatanDT tbody tr').each(function () {

        let tr = jQuery(this);

        let row = {
            id: tr.data('id')
        };

        tr.find('[name]').each(function () {

            row[this.name] = jQuery(this).val();

        });

        DRAFT_KONVO.jawatan.push(row);

    });

    //console.log('SYNC JAWATAN:', DRAFT_KONVO.jawatan);
    saveDraft();
}

function syncAnugerahFromTable() {

    // kalau draft dah ada data, jangan overwrite
    if (DRAFT_KONVO.anugerah.length) {
        return;
    }

    DRAFT_KONVO.anugerah = [];

    jQuery('#anugerahDT tbody tr').each(function () {

        let tr = jQuery(this);

        let row = {
            id: tr.data('id')
        };

        tr.find('[name]').each(function () {

            row[this.name] = jQuery(this).val();

        });

        DRAFT_KONVO.anugerah.push(row);

    });

    console.log('SYNC ANUGERAH:', DRAFT_KONVO.anugerah);
    saveDraft();
}

function saveDraft() {

    fetch(
        base_url +
        'pages/iStar/permohonan/konvo/ajax/save-draft.php',
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(DRAFT_KONVO)
        }
    )

    .then(res => res.json())

    .then(data => {
        console.log('Draft saved');
    })

    .catch(err => {
        console.error('Save failed', err);
    });
}

// Function to submit the final permohonan (DRAFT_KONVO) to server
function submitPermohonan() {

    fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/submit-permohonan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(DRAFT_KONVO)
    })
    .then(res => res.json())
    .then(res => {

        if (res.status === 'success') {

            Swal.fire({
                icon: 'success',
                title: 'Berjaya',
                text: 'Permohonan berjaya dihantar',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = base_url + 'pages/iStar/semakan/permohonan/index.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message || 'Gagal hantar permohonan'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ralat Server semasa submit'
        });
    });
}

// #### JQuery Functions ####
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

                let msg = konvoText('system_error_try_again', 'Ralat sistem. Cuba lagi.');

                try {
                    let res = JSON.parse(xhr.responseText);
                    if (res.message) msg = res.message;
                } catch (e) {}

                //console.log('AJAX ERROR:', xhr.responseText);
            }

        });

    });    

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
                    //jawatanLoaded = true;
                    //console.log('RELOAD JAWATAN TABLE');

                    form.reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById('jawatanAddModal')
                    ).hide();

                    //reload page to reflect new data
                    // setTimeout(() => {
                    //     location.reload();
                    // }, 150);  
                    
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

    // Update Document - kemaskini dokumen secara inline bila file dipilih
    jQuery(document).on('change', '.dokumen-inline', function () {

        const input = this;

        if (!input.files.length) return;

        const file = input.files[0];
        const rowId = jQuery(this).data('id');
        let el = jQuery(this);
        let tr = el.closest('tr');

        // FIX DataTables child row
        if (tr.hasClass('child')) {
            tr = tr.prev('tr');
        }

        const formData = new FormData();
        const ajaxUrl = jQuery(this).data('url');

        formData.append('id', rowId);
        formData.append('dokumen', file);

        tr.removeClass('row-success row-error');
        tr.addClass('row-saving');

        jQuery.ajax({

            url: base_url + ajaxUrl,
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
                    const id = rowId;

                    jQuery('a.btn-outline-warning[data-id="' + id + '"]')
                        .attr('href', base_url + filePath)
                        .attr('data-path', filePath);

                    setTimeout(() => {
                        tr.removeClass('row-success');
                    }, 1500);

                } else {

                    tr.addClass('row-error');
                    //console.log('FILE UPLOAD FAILED:', res);
                    Swal.fire({
                        icon: 'error',
                        title: konvoText('swal_failed_title', 'Gagal'),
                        text: res.message || konvoText('record_update_failed', 'Gagal kemaskini rekod')
                    });                    

                }

            },

            error: function (xhr) {

                tr.removeClass('row-saving');
                tr.addClass('row-error');

                console.log('FILE UPLOAD ERROR:', xhr.responseText);
            }

        });

    });    

    // button trigger for upload document 
    jQuery(document).on('click', '.upload-btn', function () {
        let el = jQuery(this);
        let tr = el.closest('tr');

        // FIX DataTables child row
        if (tr.hasClass('child')) {
            tr = tr.prev('tr');
        }        
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

    //Sync IStAD
    jQuery(document).on('click', '#syncIstadBtn', function () {

        Swal.fire({
            title: konvoText('sync_istad_title', 'Sync data IStAD?'),
            text: konvoText('sync_istad_text', 'Data IStAD akan dikemaskini semula. Data Tambahan tidak akan berubah.'),
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

    //Sync IStAD - Jawatan Disandang
    jQuery(document).on('click', '#syncIstadJawatanBtn', function () {

        Swal.fire({
            title: konvoText('sync_istad_title', 'Sync data IStAD?'),
            text: konvoText('sync_istad_text', 'Data IStAD akan dikemaskini semula. Data Tambahan tidak akan berubah.'),
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

    // if (jQuery('#anugerahDT').length) {
    //     initStandardDataTable('#anugerahDT');
    //     initDatePicker(document.getElementById('anugerahDT'));
    // }
});
