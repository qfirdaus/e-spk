//console.log('semak_pengesahan_pelajar.js loaded');

let pingatGraduanLoaded = false;

function jsSwalText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

document.addEventListener('DOMContentLoaded', function () {

    const activeTab = document.querySelector(
        'a[href="#semak-pingat-graduan-tab"]'
    );

    if (activeTab && activeTab.classList.contains('active')) {
        loadPingatGraduan();
    }

});

document.addEventListener('shown.bs.tab', function (event) {

    const target = event.target.getAttribute('href');

    if (target === '#semak-pingat-graduan-tab') {
        if (!pingatGraduanLoaded) {
            loadPingatGraduan();
        }
    }
});

function loadPingatGraduan() {
    //console.log('loadPingatGraduan()');
    const container = document.getElementById('semak-pingat-graduan-container');
    container.innerHTML = msg_load.loading;

    fetch(base_url + 'pages/iStar/semakan/permohonan/ajax/load-pingat-graduan.php')
        .then(res => res.text())
        .then(html => {

            container.innerHTML = html;

            // requestAnimationFrame(() => {

            //     if ($.fn.DataTable.isDataTable('#pingatGraduanDT')) {
            //         $('#pingatGraduanDT').DataTable().destroy();
            //     }

            //     pengesahanTable = $('#pingatGraduanDT').DataTable({
            //         responsive: true,
            //         pageLength: 10,
            //         order: [[0, 'desc']],

            //         columnDefs: [
            //             {
            //                 targets: 0,
            //                 searchable: false,
            //                 orderable: false,
            //                 render: function (data, type, row, meta) {
            //                     return meta.row + meta.settings._iDisplayStart + 1;
            //                 }
            //             }
            //         ]
            //     });

            // });
            requestAnimationFrame(() => {
                const table =
                    initStandardDataTable('#pingatGraduanDT');

                initExpandRow(table);
            });            
            pingatGraduanLoaded = true;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = "Error load data";
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

// expand row 
function initExpandRow(table) {

    $('#pingatGraduanDT tbody').on(
        'click',
        '.details-control',
        function () {

            const tr = $(this).closest('tr');
            const row = table.row(tr);

            if (row.child.isShown()) {

                row.child.hide();

                tr.removeClass('shown');

                $(this)
                    .find('i')
                    .removeClass('ri-subtract-fill text-danger')
                    .addClass('ri-add-circle-fill text-info');

            } else {

                const raw = tr.attr('data-row');
                const rowData = JSON.parse(raw || '{}');

                row.child(
                    renderChildTable(rowData)
                ).show();

                tr.addClass('shown');

                initChildTable(`#child_${rowData.id}`);

                $(this)
                    .find('i')
                    .removeClass('ri-add-circle-fill text-info')
                    .addClass('ri-subtract-fill text-danger');
            }
        }
    );
}

function initChildTable(tableId) {
    setTimeout(() => {

        if (!document.querySelector(tableId)) return;

        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
        }

        $(tableId).DataTable({
            pageLength: 5,        
            lengthChange: false,  
            searching: false,     
            ordering: false,
            info: false,          
            paging: true,
            autoWidth: false
        });

    }, 50);
}

// render child table for each row (pingat graduan) with data from row.penglibatan
function renderChildTable(row)
{
    const penglibatan = row.penglibatan || [];

    if (!penglibatan.length) {
        return `
            <div class="p-3 text-muted">
                Tiada penglibatan
            </div>
        `;
    }

    const tableId = `child_${row.id}`;

    let rows = '';

    penglibatan.forEach((p, i) => {
        const formatTarikhMY = (dateStr) => {
            if (!dateStr) return '-';
            const bulan = [
                'Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun',
                'Jul', 'Ogo', 'Sep', 'Okt', 'Nov', 'Dis'
            ];

            const d = new Date(dateStr);
            return `${String(d.getDate()).padStart(2, '0')} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
        };        

        const badgeClass =  p.source === 'IStAD'  ? 'bg-darkgreen' : 'bg-salmon';

        rows += `
            <tr>
                <td class="text-center">${i + 1}</td>
                <td>
                    <span class="badge ${badgeClass}">
                        ${p.source ?? '-'}
                    </span>                
                </td>
                <td>${p.name_programme ?? '-'}</td>
                <td>${formatTarikhMY(p.programme_date)}</td>
                <td>${p.representative_desc ?? '-'}</td>
                <td>${p.level_desc ?? '-'}</td>
                <td>${p.achievement ?? '-'}</td>
            </tr>
        `;
    });

    return `
        <div class="archive-child-content p-3">
            <div class="icares-address-panel-header">
                <h5 class="text-h5">Penglibatan Program</h5>
            </div>
            <table id="${tableId}" class="table table-sm table-bordered child-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th></th>
                        <th>Nama Program</th>
                        <th>Tarikh</th>
                        <th>Wakil</th>
                        <th>Peringkat</th>
                        <th>Pencapaian</th>
                    </tr>
                </thead>

                <tbody>
                    ${rows}
                </tbody>
            </table>

        </div>
    `;
}