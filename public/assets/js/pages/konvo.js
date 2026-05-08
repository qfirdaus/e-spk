function initStandardDataTable(tableId) {

    /*
    |--------------------------------------------------------------------------
    | CHECK TABLE EXIST
    |--------------------------------------------------------------------------
    */

    if (!jQuery(tableId).length) {
        return;
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

        }

    });

}



/*
|--------------------------------------------------------------------------
| DOCUMENT READY
|--------------------------------------------------------------------------
*/

jQuery(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | INIT ALL TABLES
    |--------------------------------------------------------------------------
    */

    [
        '#penglibatanDT',
        '#jawatanDT',
        '#anugerahDT'
    ].forEach(function (tableId) {

        initStandardDataTable(tableId);

    });


    /*
    |--------------------------------------------------------------------------
    | MODAL ADD - PENGLIBATAN
    |--------------------------------------------------------------------------
    */

    const penglibatanModalEl =
        document.getElementById('penglibatanAddModal');

    if (penglibatanModalEl) {

        const penglibatanModal =
            new bootstrap.Modal(penglibatanModalEl);

        jQuery('#penglibatanBtnAdd').on('click', function () {

            penglibatanModal.show();

        });

    }


    /*
    |--------------------------------------------------------------------------
    | MODAL ADD - JAWATAN
    |--------------------------------------------------------------------------
    */

    const jawatanModalEl =
        document.getElementById('jawatanAddModal');

    if (jawatanModalEl) {

        const jawatanModal =
            new bootstrap.Modal(jawatanModalEl);

        jQuery('#jawatanBtnAdd').on('click', function () {

            jawatanModal.show();

        });

    }


    /*
    |--------------------------------------------------------------------------
    | MODAL ADD - ANUGERAH
    |--------------------------------------------------------------------------
    */

    const anugerahModalEl =
        document.getElementById('anugerahAddModal');

    if (anugerahModalEl) {

        const anugerahModal =
            new bootstrap.Modal(anugerahModalEl);

        jQuery('#anugerahBtnAdd').on('click', function () {

            anugerahModal.show();

        });

    }

});