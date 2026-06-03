//console.log('semak_pengesahan_pelajar.js loaded');

let pengesahanLoaded = false;
let pengesahanTable = null;

document.addEventListener('DOMContentLoaded', function () {

    const activeTab = document.querySelector(
        'a[href="#semak_pengesahan_pelajar-tab"]'
    );

    if (activeTab && activeTab.classList.contains('active')) {
        loadPengesahan();
    }

});

document.addEventListener('shown.bs.tab', function (event) {

    const target = event.target.getAttribute('href');

    if (target === '#semak_pengesahan_pelajar-tab') {
        if (!pengesahanLoaded) {
            loadPengesahan();
        }
    }
});

function loadPengesahan() {
    console.log('loadPengesahan()');
    const container = document.getElementById('pengesahan-pelajar-container');
    container.innerHTML = msg_load.loading;

    fetch(base_url + 'pages/iCares/semakan/permohonan/ajax/load-pengesahan-pelajar.php')
        .then(res => res.text())
        .then(html => {

            container.innerHTML = html;

            requestAnimationFrame(() => {

                if ($.fn.DataTable.isDataTable('#pengesahanPelajarDT')) {
                    $('#pengesahanPelajarDT').DataTable().destroy();
                }

                pengesahanTable = $('#pengesahanPelajarDT').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'desc']],

                    columnDefs: [
                        {
                            targets: 0,
                            searchable: false,
                            orderable: false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        }
                    ]
                });

            });
            pengesahanLoaded = true;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = "Error load data";
        });
}