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

            const idSkill = button.getAttribute('data-idSkill');
            const skill = button.getAttribute('data-skill');

            const modal = jQuery(this);

            modal.find('#txtidkemahiran_edit').val(idSkill);
            modal.find('#txtkemahiran_edit').val(skill);
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

// submit Skill baharu
const btnTambahSkill = document.getElementById('btnTambahSkill');
if (btnTambahSkill) {
    btnTambahSkill.addEventListener('click', function(e) {

        const modalElement = document.getElementById('tambah');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat kemahiran baharu ini?",
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

                submitSkill(form);
            }
        });
    });
}

function submitSkill(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-admin/kemahiran/submit-skill.php';

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

// update Skill
const btnKemaskiniSkill = document.getElementById('btnKemaskiniSkill');
if (btnKemaskiniSkill) {
    btnKemaskiniSkill.addEventListener('click', function(e) {
        const modalElement = document.getElementById('kemaskini');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat kemahiran yang telah dikemaskini ini?",
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
                hiddenInput.name = 'btnKemaskiniSkill';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                updateSkill(form);
            }
        });
    });
}

function updateSkill(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-admin/kemahiran/update-skill.php';

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

// delete Skill
function deleteFunc(idKemahiran) {
    if (!idKemahiran) return;

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
            
            const controllerUrl = base_url + 'pages/page-admin/kemahiran/delete-skill.php';
            
            const formData = new FormData();
            formData.append('btnHapus', '1');
            formData.append('idKemahiran', idKemahiran);

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