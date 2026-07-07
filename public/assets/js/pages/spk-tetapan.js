// console.log('js loaded');

function jsSwalText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

jQuery(function () {
    // modal tambah lepas click button +
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

    // modal salin lepas click button copy
    // const modalSalin = document.getElementById('salin');
    // if (modalSalin) {
    //     modalSalin.addEventListener('show.bs.modal', function (event) {
    //         const button = event.relatedTarget;
    //         const sesiId = button.getAttribute('data-sesiId');
    //         const sesi = button.getAttribute('data-sesi');
    //         const programId = button.getAttribute('data-programId');

    //         const modal = jQuery(this);
    //         modal.find('#txtsesiid').val(sesiId);
    //         modal.find('#txtsesi').val(sesi);
    //         modal.find('#txtprogramid').val(programId);             
    //     });
    // }    

    // modal kemaskini
    const modalKemaskini = document.getElementById('kemaskini');
    if (modalKemaskini) {
        modalKemaskini.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            const idPlo = button.getAttribute('data-idplo');
            const sesiId = button.getAttribute('data-sesiid');
            const sesi = button.getAttribute('data-sesi');
            const programId = button.getAttribute('data-programid');
            const program = button.getAttribute('data-program');
            const kodPlo = button.getAttribute('data-kodplo');
            const keteranganBM = button.getAttribute('data-keteranganbm');
            const kodMqf = button.getAttribute('data-kodmqf');
            const peoListStr = button.getAttribute('data-peolist'); 

            const modal = jQuery(this);
            
            modal.find('#txtidplo').val(idPlo); 
            modal.find('#txtsesiid').val(sesiId);
            modal.find('#txtsesi').val(sesi);
            modal.find('#txtprogram_id').val(programId);
            modal.find('#txtprogram').val(program);
            modal.find('#txtkodplo').val(kodPlo);
            modal.find('#txtketeranganplo').val(keteranganBM);

            // Set nilai dropdown (Jika pakai Select2, tambah .trigger('change'))
            if (kodMqf) {
                var cleanedValue = kodMqf.toString().trim();
                var selectElement = document.getElementById('selectkodmqf_edit'); 
                
                if (selectElement) {
                    selectElement.value = cleanedValue;                 
                    var textToDisplay = $(selectElement).find('option[value="' + cleanedValue + '"]').text().trim();
                    
                    if (textToDisplay) {
                        $(selectElement)
                            .next('.select2-container')
                            .find('.select2-selection__rendered')
                            .text(textToDisplay);
                            
                        $(selectElement)
                            .next('.select2-container')
                            .find('.select2-selection__rendered')
                            .attr('title', textToDisplay);
                    }
                    $(selectElement).trigger('change.select2'); 
                }
            }
        });
    }    
});

function deleteFunc(idPlo) {
    if (!idPlo) return;

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
            
            const controllerUrl = base_url + 'pages/page-admin/maklumat-plo-ku/delete-plo.php';
            
            const formData = new FormData();
            formData.append('btnHapus', '1');
            formData.append('id_plo', idPlo);

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

// submit PLO baharu
const btnHantarPlo = document.getElementById('btnHantarPlo');
if (btnHantarPlo) {
    btnHantarPlo.addEventListener('click', function(e) {
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

                submitPLO(form);
            }
        });
    });
}

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
            // if (document.activeElement) {
            //     document.activeElement.blur(); 
            // }    

            // const modalElement = document.getElementById('tambah');
            // const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            // modalInstance.hide();

            //remove modal backdrop if it exists
            //document.querySelector('.modal-backdrop')?.remove();

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

// update PLO
const btnKemaskiniPlo = document.getElementById('btnKemaskiniPlo');
if (btnKemaskiniPlo) {
    btnKemaskiniPlo.addEventListener('click', function(e) {
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
                hiddenInput.name = 'btnKemaskiniPlo';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                updatePLO(form);
            }
        });
    });
}

function updatePLO(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-admin/maklumat-plo-ku/update-plo.php';

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            // const modalElement = document.getElementById('kemaskini');
            // const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
            // modalInstance.hide();

            //remove modal backdrop if it exists
            //document.querySelector('.modal-backdrop')?.remove();

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
