$(document).ready(function() {
    
    $('#dataSltDT').DataTable({
        responsive: false, 
        scrollX: false,     
        autoWidth: false,
        columnDefs: [
            { orderable: false, targets: [8, 9] } 
        ]
    });

    // Add 
    $('button[data-bs-target="#tambah"]').on('click', function() {
        $('#add_txtsesiid').val($(this).data('sesiid'));
        $('#add_txtsesi').val($(this).data('sesi'));
        $('#add_txtkursusid').val($(this).data('kursusid'));
        $('#add_txtkursus').val($(this).data('kursus'));
    });

    // Update
    $('.btnKemaskiniModal').on('click', function() {
        var modal = $('#kemaskini');
        
        modal.find('input[name="txtidslt"]').val($(this).data('idslt'));
        modal.find('input[name="txtkursusid"]').val($(this).data('kursusid'));
        modal.find('input[name="txtsesi"]').val($(this).data('sesi'));
        modal.find('input[name="txtkursus"]').val($(this).data('kursus'));
        
        modal.find('textarea[name="txtcontent"]').val($(this).data('content'));
        modal.find('select[name="selectCLO"]').val($(this).data('idclo'));
        
        modal.find('input[name="txtlecture"]').val($(this).data('lecture'));
        modal.find('input[name="txttutorial"]').val($(this).data('tutorial'));
        modal.find('input[name="txtpractical"]').val($(this).data('practical'));
        modal.find('input[name="txtothers"]').val($(this).data('others'));
        modal.find('input[name="txtnf2f"]').val($(this).data('nf2f'));
        modal.find('input[name="txtindependent"]').val($(this).data('independent'));
    });


    // Copy
    $('button[data-bs-target="#salin"]').on('click', function() {
        var modal = $('#salin');
        modal.find('input[name="txtterm"]').val($(this).data('term'));
        modal.find('input[name="txtcourseid"]').val($(this).data('kursusid'));
    });

    // Submit
    $('#formTambahSLT').on('submit', function(e) {
        e.preventDefault(); 
        
        var formElement = this; 
        
        Swal.fire({
            title: 'Simpan Maklumat?',
            text: "Adakah anda pasti untuk menyimpan maklumat SLT ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitSLT(formElement);
            }
        });
    });

    function submitSLT(formElement) {
        const formData = new FormData(formElement);
        
        const controllerUrl = base_url + 'pages/page-penyelaras-kursus/masa-pembelajaran-pelajar/submit-slt.php';

        // Tunjuk loading semasa memproses
        Swal.fire({
            title: 'Menyimpan...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

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
                text: 'Berlaku ralat pelayan (Server Error) semasa menyimpan data.'
            });
        });
    }   
});