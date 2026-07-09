jQuery(function () {
    // modal tambah - button + clicked
    const modalTambah = document.getElementById('tambah');
    if (modalTambah) {
        modalTambah.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const modal = jQuery(this);
        });
    }

    // modal kemaskini - button kemaskini clicked
    const modalKemaskini = document.getElementById('kemaskini');
    if (modalKemaskini) {
        modalKemaskini.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            const idMQF = button.getAttribute('data-idMQF');
            const kodMQF = button.getAttribute('data-kodMQF');

            const modal = jQuery(this);

            modal.find('#txtidmqf_edit').val(idMQF);
            modal.find('#txtmqf_edit').val(kodMQF);
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

// submit MQF baharu
const btnTambahMQF = document.getElementById('btnTambahMQF');
if (btnTambahMQF) {
    btnTambahMQF.addEventListener('click', function(e) {

        const modalElement = document.getElementById('tambah');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat kod MQF baharu ini?",
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

                submitMQF(form);
            }
        });
    });
}

function submitMQF(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-admin/kod-mqf/submit-mqf.php';

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

// update MQF
const btnKemaskiniMQF = document.getElementById('btnKemaskiniMQF');
if (btnKemaskiniMQF) {
    btnKemaskiniMQF.addEventListener('click', function(e) {
        const modalElement = document.getElementById('kemaskini');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat kod MQF yang telah dikemaskini ini?",
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
                hiddenInput.name = 'btnKemaskiniMQF';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                updateMQF(form);
            }
        });
    });
}

function updateMQF(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-admin/kod-mqf/update-mqf.php';

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            const modalElement = document.getElementById('kemaskini');
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            modalInstance.hide();

            //remove modal backdrop if it exists
            document.querySelector('.modal-backdrop')?.remove();

            Swal.fire({
                icon: 'success',
                title: 'Berjaya',
                text: res.message || 'Rekod berjaya dikemaskini',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message || 'Gagal mengemaskini rekod'
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

// delete MQF
function deleteFunc(idMQF) {
    if (!idMQF) return;

    Swal.fire({
        title: 'Adakah anda pasti?',
        text: "Anda tidak akan dapat mengembalikan rekod ini semula!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            
            const controllerUrl = base_url + 'pages/page-admin/kod-mqf/delete-mqf.php';
            
            const formData = new FormData();
            formData.append('btnHapus', '1');
            formData.append('idMQF', idMQF);

            fetch(controllerUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapuskan!',
                        text: res.message || 'Rekod telah berjaya dihapuskan.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Gagal menghapus rekod'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat',
                    text: 'Ralat pelayan semasa memproses penghapusan data'
                });
            });
        }
    });
}