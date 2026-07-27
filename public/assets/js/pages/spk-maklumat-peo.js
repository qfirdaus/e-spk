// console.log('js loaded');

function jsSwalText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

jQuery(function () {
    // modal tambah - button + clicked
    const modalTambah = document.getElementById('tambah');
    if (modalTambah) {
        modalTambah.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const sesiId = button.getAttribute('data-sesiid');
            const sesi = button.getAttribute('data-sesi');
            const programId = button.getAttribute('data-programid');
            const program = button.getAttribute('data-program');
            const ptj = button.getAttribute('data-ptj');

            const modal = jQuery(this);

            modal.find('#txtsesiid').val(sesiId);
            modal.find('#txtsesi').val(sesi);
            modal.find('#txtprogramid').val(programId);
            modal.find('#txtprogram').val(program);
            modal.find('#txtptj').val(ptj);
        });
    }

    // modal salin - button salin clicked
    const modalSalin = document.getElementById('salin');
    if (modalSalin) {
        modalSalin.addEventListener('show.bs.modal', function (event) {
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

    // modal kemaskini
    const modalKemaskini = document.getElementById('kemaskini');
    if (modalKemaskini) {
        modalKemaskini.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            const idPeo = button.getAttribute('data-idpeo');
            const sesiId = button.getAttribute('data-sesiid');
            const sesi = button.getAttribute('data-sesi');
            const programId = button.getAttribute('data-programid');
            const program = button.getAttribute('data-program');
            const kodPeo = button.getAttribute('data-kodpeo');
            const keteranganBM = button.getAttribute('data-keteranganbm');
            const tarikh_senat = button.getAttribute('data-tarikhsenat');

            const modal = jQuery(this);
            
            modal.find('#txtidpeo_edit').val(idPeo); 
            modal.find('#txtsesiid_edit').val(sesiId);
            modal.find('#txtsesi_edit').val(sesi);
            modal.find('#txtprogram_id_edit').val(programId);
            modal.find('#txtprogram_edit').val(program);
            modal.find('#txtkodpeo_edit').val(kodPeo);
            modal.find('#txtketeranganpeo_edit').val(keteranganBM);
            modal.find('#txttarikhsenat_edit').val(tarikh_senat);

            // Set nilai dropdown (Jika pakai Select2, tambah .trigger('change'))
            // if (kodMqf) {
            //     var cleanedValue = kodMqf.toString().trim();
            //     var selectElement = document.getElementById('selectkodmqf_edit'); 
                
            //     if (selectElement) {
            //         selectElement.value = cleanedValue;                 
            //         var textToDisplay = $(selectElement).find('option[value="' + cleanedValue + '"]').text().trim();
                    
            //         if (textToDisplay) {
            //             $(selectElement)
            //                 .next('.select2-container')
            //                 .find('.select2-selection__rendered')
            //                 .text(textToDisplay);
                            
            //             $(selectElement)
            //                 .next('.select2-container')
            //                 .find('.select2-selection__rendered')
            //                 .attr('title', textToDisplay);
            //         }
            //         $(selectElement).trigger('change.select2'); 
            //     }
            // }
        });
    }
});

function deleteFunc(idPEO) {
    if (!idPEO) return;

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
            
            const controllerUrl = base_url + 'pages/page-ketua-program/maklumat-peo/delete-peo.php';
            
            const formData = new FormData();
            formData.append('btnHapus', '1');
            formData.append('id_peo', idPEO);

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

// submit PEO baharu
const btnHantarPeo = document.getElementById('btnHantarPeo');
if (btnHantarPeo) {
    btnHantarPeo.addEventListener('click', function(e) {
        const modalElement = document.getElementById('tambah');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat PLO baharu ini?",
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

                submitPEO(form);
            }
        });
    });
}

function submitPEO(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-ketua-program/maklumat-peo/submit-peo.php';

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

// update PEO
const btnKemaskiniPeo = document.getElementById('btnKemaskiniPeo');
if (btnKemaskiniPeo) {
    btnKemaskiniPeo.addEventListener('click', function(e) {
        const modalElement = document.getElementById('kemaskini');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat PLO yang telah dikemaskini ini?",
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
                hiddenInput.name = 'btnKemaskiniPeo';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                updatePEO(form);
            }
        });
    });
}

function updatePEO(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-ketua-program/maklumat-peo/update-peo.php';
    const modalElement = document.getElementById('kemaskini');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);    

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
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
            modalInstance.hide();

            //remove modal backdrop if it exists
            document.querySelector('.modal-backdrop')?.remove();

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message || 'Gagal mengemaskini rekod'
            });
        }
    })
    .catch(err => {
        modalInstance.hide();

        //remove modal backdrop if it exists
        document.querySelector('.modal-backdrop')?.remove();

        Swal.fire({
            icon: 'error',
            title: 'Ralat',
            text: 'Ralat pelayan (Server Error) semasa menyimpan data'
        });
    });
} 

// copy PeO
const btnSalinPeoSubmit = document.getElementById('btnSalinPeoSubmit');
if (btnSalinPeoSubmit) {
    btnSalinPeoSubmit.addEventListener('click', function(e) {
        const modalElement = document.getElementById('salin');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyalin maklumat PEO sesi ini?",
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
                hiddenInput.name = 'btnSalin';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                copyPEO(form);
            }
        });
    });
}

function copyPEO(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-ketua-program/maklumat-peo/copy-peo.php';

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            const modalElement = document.getElementById('salin');
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            modalInstance.hide();

            //remove modal backdrop if it exists
            document.querySelector('.modal-backdrop')?.remove();

            Swal.fire({
                icon: 'success',
                title: 'Berjaya',
                text: res.message || 'Rekod berjaya disalin',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message || 'Gagal menyalin rekod'
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
