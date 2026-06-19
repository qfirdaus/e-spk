const saveTimers = {};
let akademikTambahanLoaded = false;
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

    const target = event.target.getAttribute('href') || event.target.getAttribute('data-bs-target') ;
    //console.log('TAB SHOWN:', target);
 
    if(target === '#akademik-tambahan-tab') {
        if (!akademikTambahanLoaded) {
            loadAkademikTambahan();
        }
    }  

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
        const wait = setInterval(() => {

            const chk1 = document.getElementById('chk1');
            const chk2 = document.getElementById('chk2');
            const chk3 = document.getElementById('chk3');

            if (chk1 && chk2 && chk3 && DRAFT_KONVO?.perakuan) {
                clearInterval(wait);
                initPerakuan();
            }

        }, 50);
    }    

});

document.addEventListener('change', function (e) {    

    if (e.target && e.target.classList.contains('kategori-select')) {

        let row = e.target.closest('tr');
        // dalam table
        if (row) {
            let kategori = e.target.value;
            let selectedOption = e.target.options[e.target.selectedIndex];

            let kategoriText = selectedOption?.dataset?.kategori_aktiviti || '';

            let hiddenKategoriText = row.querySelector('.aktiviti-Text');

            if (hiddenKategoriText) {
                hiddenKategoriText.value = kategoriText || '';

                // trigger autosave
                hiddenKategoriText.dispatchEvent(
                    new Event('change', { bubbles: true })
                );
            }

            let jawatanSelect = row.querySelector('.jawatan-select');
            let jawatanText = row.querySelector('.jawatan-text');

            // update OPTION text
            if (jawatanSelect) {

                Array.from(jawatanSelect.options).forEach(opt => {

                    if (!opt.value) return;

                    let bp = opt.getAttribute('data-bp');
                    let def = opt.getAttribute('data-default');

                    opt.text = (kategori === 'BP') ? bp : def;
                });
            }

            // update input jawatan ikut selected option
            if (jawatanSelect && jawatanText) {

                let selected = jawatanSelect.options[jawatanSelect.selectedIndex];

                let text =
                    kategori === 'BP'
                        ? selected.getAttribute('data-bp')
                        : selected.getAttribute('data-default');

                jawatanText.value = text || '';
            }

            if (jawatanSelect) {
                jQuery(jawatanSelect).trigger('change');
            }

            if (jawatanText) {
                jQuery(jawatanText).trigger('change');
            }  
                                 
            return;
        }

        let modal = e.target.closest('.modal');

        if (modal) {

            let kategori = e.target.value;
            let kategoriText = e.target.selectedOptions?.[0]?.getAttribute('data-kategori_aktiviti') || '';
            let hiddenKategoriText = modal.querySelector('.aktiviti-Text');

            if (hiddenKategoriText) {
                hiddenKategoriText.value = kategoriText || '';

                // trigger autosave
                hiddenKategoriText.dispatchEvent(
                    new Event('change', { bubbles: true })
                );
            }

            let jawatanSelect = modal.querySelector('.jawatan-select');
            let jawatanText = modal.querySelector('.jawatan-text');

            if (!jawatanSelect) return;

            // update option text
            Array.from(jawatanSelect.options).forEach(opt => {

                if (!opt.value) return;

                let bp = opt.getAttribute('data-bp');
                let def = opt.getAttribute('data-default');

                opt.text = (kategori === 'BP') ? bp : def;
            });

            // SAFE selected handling
            let selected = jawatanSelect.options[jawatanSelect.selectedIndex];

            if (selected && jawatanText) {

                let text =
                    kategori === 'BP'
                        ? selected.getAttribute('data-bp')
                        : selected.getAttribute('data-default');

                jawatanText.value = text || '';
            }

            return;
        }            
    }    

    if (e.target && e.target.classList.contains('jawatan-select')) {

        let row = e.target.closest('tr');

        if (row) {

            let kategoriEl = row.querySelector('.kategori-select');
            let kategori = kategoriEl ? kategoriEl.value : '';

            let jawatanSelect = e.target;
            let jawatanText = row.querySelector('.jawatan-text');

            let selected = jawatanSelect.options[jawatanSelect.selectedIndex];

            if (!selected) return;

            let text = '';

            if (kategori === 'BP') {
                text = selected.getAttribute('data-bp') || '';
            } else {
                text = selected.getAttribute('data-default') || '';
            }

            if (jawatanText) {
                jawatanText.value = text;
            }
            
            jQuery(jawatanText).trigger('change');

            return;
        }

        // dalam modal / form biasa
        let modal = e.target.closest('.modal');

        if (modal) {

            let kategoriSelect = modal.querySelector('.kategori-select');
            let kategori = kategoriSelect ? kategoriSelect.value : '';

            let jawatanSelect = modal.querySelector('.jawatan-select');
            let jawatanText = modal.querySelector('.jawatan-text');

            if (!jawatanSelect) return;

            let selected = jawatanSelect.options[jawatanSelect.selectedIndex];

            if (!selected) return;

            let text =
                kategori === 'BP'
                    ? selected.getAttribute('data-bp')
                    : selected.getAttribute('data-default');

            if (jawatanText) {
                jawatanText.value = text || '';
            }

            return;
        }        
    } 
});

// #### Functions Load ####
// async function loadDraft() {
//     try {
//         const res = await fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/load-draft.php');
//         const data = await res.json();

//         console.log('DRAFT LOADED:', data);

//         // dataStudent (KEKAL)
//         var keepDataStudent = DRAFT_KONVO.dataStudent;

//         // REPLACE OBJECT
//         var newDraft = {};

//         newDraft.draft_initialized = data.draft_initialized;
//         newDraft.gredPSM = data.gredPSM;
//         newDraft.perakuan = data.perakuan;

//         newDraft.akademikTambahan = data.akademikTambahan;
//         newDraft.penglibatan = data.penglibatan;
//         newDraft.jawatan = data.jawatan;
//         newDraft.anugerah = data.anugerah;

//         // assign full object
//         DRAFT_KONVO = newDraft;

//         // dataStudent
//         DRAFT_KONVO.dataStudent = keepDataStudent;

//         // perakuan
//         var p = DRAFT_KONVO.perakuan || {};

//         DRAFT_KONVO.perakuan = {
//             chk1: Number(p.chk1 || 0),
//             chk2: Number(p.chk2 || 0),
//             chk3: Number(p.chk3 || 0)
//         };

//         if (document.getElementById('chk1') &&
//             document.getElementById('chk2') &&
//             document.getElementById('chk3')) {

//             fillPerakuan();
//         }
//         // ARRAY NORMALIZE
//         if (!Array.isArray(DRAFT_KONVO.akademikTambahan)) {
//             DRAFT_KONVO.akademikTambahan = [];
//         }

//         if (!Array.isArray(DRAFT_KONVO.penglibatan)) {
//             DRAFT_KONVO.penglibatan = [];
//         }

//         if (!Array.isArray(DRAFT_KONVO.jawatan)) {
//             DRAFT_KONVO.jawatan = [];
//         }

//         if (!Array.isArray(DRAFT_KONVO.anugerah)) {
//             DRAFT_KONVO.anugerah = [];
//         }

//         // gredPSM fallback
//         if (!DRAFT_KONVO.gredPSM) {
//             DRAFT_KONVO.gredPSM = '';
//         }

//     } catch (err) {
//         console.error('loadDraft error:', err);
//     }
// }
async function loadDraft() {

    try {

        const res = await fetch(
            base_url +
            'pages/iStar/permohonan/konvo/ajax/load-draft.php'
        );

        const data = await res.json();

        console.log('DRAFT LOADED:', data);

        const keepDataStudent = DRAFT_KONVO.dataStudent;

        DRAFT_KONVO = {

            draft_initialized: data.draft_initialized || false,

            dataStudent: keepDataStudent,

            gredPSM: data.gredPSM || '',

            akademikTambahan:
                Array.isArray(data.akademikTambahan)
                    ? data.akademikTambahan
                    : [],

            penglibatan:
                Array.isArray(data.penglibatan)
                    ? data.penglibatan
                    : [],

            jawatan:
                Array.isArray(data.jawatan)
                    ? data.jawatan
                    : [],

            anugerah:
                Array.isArray(data.anugerah)
                    ? data.anugerah
                    : [],

            perakuan: {
                chk1: Number(data?.perakuan?.chk1 || 0),
                chk2: Number(data?.perakuan?.chk2 || 0),
                chk3: Number(data?.perakuan?.chk3 || 0)
            }

        };

        // fallback kalau draft kosong
        if (!DRAFT_KONVO.draft_initialized) {

            await Promise.all([

                fetch(
                    base_url +
                    'pages/iStar/permohonan/konvo/ajax/load-akademik-tambahan-json.php'
                )
                .then(r => r.json())
                .then(r => {
                    DRAFT_KONVO.akademikTambahan = r.rows || [];
                }),

                fetch(
                    base_url +
                    'pages/iStar/permohonan/konvo/ajax/load-penglibatan-json.php'
                )
                .then(r => r.json())
                .then(r => {
                    DRAFT_KONVO.penglibatan = r.rows || [];
                }),

                fetch(
                    base_url +
                    'pages/iStar/permohonan/konvo/ajax/load-jawatan-json.php'
                )
                .then(r => r.json())
                .then(r => {
                    DRAFT_KONVO.jawatan = r.rows || [];
                }),

                fetch(
                    base_url +
                    'pages/iStar/permohonan/konvo/ajax/load-anugerah-json.php'
                )
                .then(r => r.json())
                .then(r => {
                    DRAFT_KONVO.anugerah = r.rows || [];
                })

            ]);

            saveDraft();
        }

        fillPerakuan();

    } catch (err) {

        console.error('loadDraft error:', err);

    }

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

            if (tableId === '#jawatanDT') {

                toolbar = `
                    <div class="d-flex gap-2 ms-2">
                        <button type="button"
                                id="syncIstadJawatanBtn"
                                class="btn btn-primary rounded-3">
                            <i class="ri-refresh-line me-1"></i>
                            ${konvoText('sync_istad', 'Sync ISTAD')}
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

// function initPerakuan() {
//     if (perakuanloaded) return;

//     const form = document.getElementById('formPerakuan');
//     const btn = form?.querySelector('button[type="submit"]');

//     if (!form || !btn) return;

//     const chk1 = document.getElementById('chk1');
//     const chk2 = document.getElementById('chk2');
//     const chk3 = document.getElementById('chk3');

//     if (!DRAFT_KONVO.perakuan) {
//         DRAFT_KONVO.perakuan = {};
//     }

//     function updateState() {

//         DRAFT_KONVO.perakuan.chk1 = chk1.checked ? 1 : 0;
//         DRAFT_KONVO.perakuan.chk2 = chk2.checked ? 1 : 0;
//         DRAFT_KONVO.perakuan.chk3 = chk3.checked ? 1 : 0;

//         const allChecked = chk1.checked && chk2.checked && chk3.checked;

//         btn.disabled = !allChecked;

//         saveDraft();
//     }

//     chk1.addEventListener('change', updateState);
//     chk2.addEventListener('change', updateState);
//     chk3.addEventListener('change', updateState);

//     fillPerakuan(); 
//     //updateState();
//     perakuanloaded = true;

//     form.addEventListener('submit', function (e) {
//         e.preventDefault();

//         submitPermohonan();
//     });    
// }

function initPerakuan() {

    if (perakuanloaded) return;

    const form = document.getElementById('formPerakuan');
    const btn = form?.querySelector('button[type="submit"]');

    const chk1 = document.getElementById('chk1');
    const chk2 = document.getElementById('chk2');
    const chk3 = document.getElementById('chk3');

    if (!chk1 || !chk2 || !chk3) return;

    setTimeout(fillPerakuan, 0);

    function updateState() {

        if (!DRAFT_KONVO.perakuan) {
            DRAFT_KONVO.perakuan = {};
        }

        DRAFT_KONVO.perakuan.chk1 = chk1.checked ? 1 : 0;
        DRAFT_KONVO.perakuan.chk2 = chk2.checked ? 1 : 0;
        DRAFT_KONVO.perakuan.chk3 = chk3.checked ? 1 : 0;

        btn.disabled = !(chk1.checked && chk2.checked && chk3.checked);

        saveDraft();
    }

    chk1.addEventListener('change', updateState);
    chk2.addEventListener('change', updateState);
    chk3.addEventListener('change', updateState);

    // ❌ JANGAN CALL updateState HERE

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

jQuery(document).on('click', '#dekanBtnAdd', function () {
    openModal('dekanAddModal');
});

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
function fillPerakuan() {

    const p = DRAFT_KONVO?.perakuan || {};

    const chk1 = document.getElementById('chk1');
    const chk2 = document.getElementById('chk2');
    const chk3 = document.getElementById('chk3');

    if (!chk1 || !chk2 || !chk3) return;

    chk1.checked = Number(p.chk1) === 1;
    chk2.checked = Number(p.chk2) === 1;
    chk3.checked = Number(p.chk3) === 1;
}

// function fillPerakuan() {

//     const chk1 = document.getElementById('chk1');
//     const chk2 = document.getElementById('chk2');
//     const chk3 = document.getElementById('chk3');

//     if (!chk1 || !chk2 || !chk3) return;

//     chk1.checked = DRAFT_KONVO.perakuan?.chk1 == 1;
//     chk2.checked = DRAFT_KONVO.perakuan?.chk2 == 1;
//     chk3.checked = DRAFT_KONVO.perakuan?.chk3 == 1;
// }

// function saveDraft() {

//     fetch(
//         base_url +
//         'pages/iStar/permohonan/konvo/ajax/save-draft.php',
//         {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json'
//             },
//             body: JSON.stringify(DRAFT_KONVO)
//         }
//     )

//     .then(res => res.json())

//     .then(data => {
//         console.log('Draft saved');
//     })

//     .catch(err => {
//         console.error('Save failed', err);
//     });
// }
function saveDraft() {

    const payload = JSON.parse(JSON.stringify(DRAFT_KONVO));

    return fetch(base_url + 'pages/iStar/permohonan/konvo/ajax/save-draft.php', {
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

    // Update Document - kemaskini dokumen secara inline bila file dipilih
    jQuery(document).on('change', '.dokumen-inline', function () {

        const input = this;

        if (!input.files.length) return;

        const file = input.files[0];
        const rowId = jQuery(this).data('id');
        const tabKey = jQuery(this).data('tab');

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
                    
                        if(tabKey=='penglibatan'){
                            penglibatanLoaded = false;
                            loadPenglibatan();
                        }else if(tabKey=='akademikTambahan') {
                            akademikTambahanLoaded = false;
                            loadAkademikTambahan();
                        }else if(tabKey=='jawatanDisandang') {
                            jawatanLoaded = false;
                            loadJawatan();
                        }else if(tabKey=='anugerah') {
                            anugerahLoaded = false;
                            loadAnugerah();
                        }

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

});

