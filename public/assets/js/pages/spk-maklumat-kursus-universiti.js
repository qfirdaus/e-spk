jQuery(function () {
    // modal tambah - button + clicked
    const modalTambah = document.getElementById('tambah');
    if (modalTambah) {
        modalTambah.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const sesiId = button.getAttribute('data-sesiid');
            const sesi = button.getAttribute('data-sesi');

            const modal = jQuery(this);

            modal.find('#txtsesiid').val(sesiId);
            modal.find('#txtsesi').val(sesi);
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    
    const alertBox = document.getElementById('flash-alert-data');

    if (alertBox) {
        const alertIcon = alertBox.getAttribute('data-icon');
        const alertTitle = alertBox.getAttribute('data-title');
        const alertMessage = alertBox.getAttribute('data-message');

        Swal.fire({
            icon: alertIcon,
            title: alertTitle,
            text: alertMessage,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }
});

function hantarBorangPenyelaras(elemenSelect) {
    // Ambil form terdekat bagi select yang berubah ini
    var borang = elemenSelect.closest('form');
    if (borang) {
        borang.submit();
    }
}

// Fungsi 2: Dipicu apabila butang reset diklik
function resetPenyelaras(idKursus) {
    // 1. Cari elemen select berdasarkan ID dinamik yang dihantar
    var $selectElement = $('#select_' + idKursus);
    
    if ($selectElement.length > 0) {
        // 2. Set nilai kembali ke "0" dan paksa Select2 kemaskini paparan visual
        $selectElement.val('0').trigger('change');
        
        // 3. Cari form dan hantar ke controller
        var borang = document.getElementById('form_' + idKursus);
        if (borang) {
            borang.submit();
        }
    } else {
        console.log('Ralat: Elemen select_' + idKursus + ' tidak dijumpai.');
    }
}

// submit Kursus baharu
const btnHantarKursus = document.getElementById('btnHantarKursus');
if (btnHantarKursus) {
    btnHantarKursus.addEventListener('click', function(e) {
        const modalElement = document.getElementById('tambah');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat kursus baharu ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            target: modalElement,
        }).then((result) => {
            if (result.isConfirmed) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'btnTambah';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                submitKursus(form);
            }
        });
    });
}

function submitKursus(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-admin/maklumat-kursus-universiti/submit-kursus.php';

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            const modalElement = document.getElementById('tambah');
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            modalInstance.hide();

            //remove modal backdrop if it exists
            document.querySelector('.modal-backdrop')?.remove();

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
        Swal.fire({
            icon: 'error',
            title: 'Ralat',
            text: 'Ralat pelayan (Server Error) semasa menyimpan data'
        });
    });
} 