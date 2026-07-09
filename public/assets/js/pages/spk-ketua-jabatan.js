$(document).ready(function () {
    $('.search-box input[type="text"]').on("keyup input", function () {
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".search_result");
        if (inputVal.length) {
            $.get("search-staff.php?dir=<?php echo basename(dirname($_SERVER['SCRIPT_NAME'])) ?>", {term: inputVal}).done(function (data) {
                // Display the returned data in browser
                resultDropdown.html(data);
            });
        } else {
            resultDropdown.empty();
        }
    });

    // Set search input value on click of result item
    $(document).on("click", ".search_result p", function () {
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
        $(this).parent(".search_result").empty();
    });
});

$(document).on("click", "#btnTambah", function () { 
    
    var nostaf = $(this).data('nostaf');
    $("#tambah .modal-body #txtnostaf").val(nostaf);
    
    var nokp = $(this).data('nokp');
    $("#tambah .modal-body #txtnokp").val(nokp);
    
    var nama = $(this).data('nama');
    $("#tambah .modal-body #txtnama").val(nama);
    
    var jabatan = $(this).data('jabatan');
    $("#tambah .modal-body #txtjabatan").val(jabatan);

    var kodjabatan = $(this).data('kdjbtnhakiki');
    $("#tambah .modal-body #txtkodjabatan").val(kodjabatan);
    
    var notel = $(this).data('notel');
    $("#tambah .modal-body #txtnotel").val(notel);
    
    var emel = $(this).data('emel');
    $("#tambah .modal-body #txtemel").val(emel);

});

// submit Ketua Jabatan baharu
const btnTambahKetuaJabatan = document.getElementById('btnTambahKetuaJabatan');
if (btnTambahKetuaJabatan) {
    btnTambahKetuaJabatan.addEventListener('click', function(e) {

        const modalElement = document.getElementById('tambah');
        const form = modalElement.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat ketua jabatan baharu ini?",
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
                hiddenInput.name = 'btnTambahKetuaJabatan';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                submitHeadDept(form);
            }
        });
    });
}

function submitHeadDept(formElement) {
    const formData = new FormData(formElement);
    const controllerUrl = base_url + 'pages/page-admin/ketua-jabatan/submit-head-dept.php';

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

// delete Ketua Program
function deleteFunc(stafID) {
    if (!stafID) return;

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
            
            const controllerUrl = base_url + 'pages/page-admin/ketua-program/delete-head-programme.php';
            
            const formData = new FormData();
            formData.append('btnHapus', '1');
            formData.append('stafID', stafID);

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