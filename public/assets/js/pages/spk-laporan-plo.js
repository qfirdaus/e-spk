$(document).ready(function() {
    $('#tableLaporanPLO').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ms.json"
        }
    });

    if ($('.select2').length) {
        $('.select2').select2({
            width: '100%'
        });
    }
});