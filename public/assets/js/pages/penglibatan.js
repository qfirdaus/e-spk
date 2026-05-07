jQuery(document).ready(function () {

  const dt = jQuery('#penglibatanDT').DataTable({
    pageLength: 10,
    lengthChange: true,
    lengthMenu: [10, 25, 50, 100],
    ordering: true,
    autoWidth: false,
    scrollX: false,

    dom:
      '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-end"f>>' +
      't' +
      '<"row mt-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',

    language: {
      search: "",
      lengthMenu: "Show _MENU_ records",
      info: "Showing _START_ to _END_ of _TOTAL_ records",
      emptyTable: "No records found",
      zeroRecords: "No matching records",
      paginate: {
        next: "Next",
        previous: "Previous"
      }
    },

    columnDefs: [
      { targets: 0, orderable: false },
      { targets: 5, orderable: false, searchable: false, width: 110 }
    ],

    rowCallback: function(row, data, index) {
      const api = this.api();
      const info = api.page.info();
      jQuery('td:eq(0)', row).html(info.start + index + 1);
    }
  });

});

jQuery(function () {

  const addModal = new bootstrap.Modal(document.getElementById('penglibatanAddModal'));

  jQuery('#penglibatanBtnAdd').on('click', function () {
    addModal.show();
  });

});