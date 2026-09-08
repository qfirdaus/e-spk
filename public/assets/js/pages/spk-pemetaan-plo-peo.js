$(document).ready(function() {
    
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#tablePemetaanPLOPeo').DataTable({
            responsive: false, 
            scrollX: false,    
            autoWidth: false,
            ordering: false,   
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ms.json"
            }
        });
    }

    if ($('.select2').length && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%'
        });
    }

});