//console.log('js loaded');

function jsSwalText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

document.addEventListener('DOMContentLoaded', function () {

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

jQuery(function () {
    // modal dibuka
    const modalTambah = document.getElementById('tambah');
    if (modalTambah) {
        modalTambah.addEventListener('show.bs.modal', function (event) {

            const button = event.relatedTarget;
            const sesiId = button.getAttribute('data-sesiid');
            const sesi = button.getAttribute('data-sesi');
            const programId = button.getAttribute('data-programid');

            const modal = jQuery(this);
            modal.find('#txtsesiid').val(sesiId);
            modal.find('#txtsesi').val(sesi);
            modal.find('#txtprogramid').val(programId);
        });
    }
});

document.getElementById('btnHantarPlo').addEventListener('click', function(e) {
    // Ambil elemen modal Bootstrap
    const modalElement = document.getElementById('tambah');
    const form = modalElement.querySelector('form');
    
    // Semak validasi input-input required
    if (!form.checkValidity()) {
        form.reportValidity(); 
        return;
    }

    // Paparkan SweetAlert2
    Swal.fire({
        title: 'Adakah anda pasti?',
        text: "Anda mahu menyimpan maklumat PLO baharu ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal',
        // --- PENYELASAIAN UTK ISU MODAL BEAKANG ---
        target: modalElement, // Ini akan memaksa Swal masuk ke dalam skop modal dan duduk di lapisan paling atas
    }).then((result) => {
        if (result.isConfirmed) {
            // Cipta input hidden 'btnTambah' secara dinamik
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'btnTambah';
            hiddenInput.value = '1';
            form.appendChild(hiddenInput);

            // Hantar form
           submitPLO(form);
        }
    });

    function submitPLO(formElement) {
        const formData = new FormData(formElement);

        const controllerUrl = base_url + 'pages/page-admin/maklumat-plo-ku/submit-plo.php';

        fetch(controllerUrl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
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
            //console.log(err);
            Swal.fire({
                icon: 'error',
                title: 'Ralat',
                text: 'Ralat pelayan (Server Error) semasa menyimpan data'
            });
        });
    }    
});