jQuery(document).ready(function ($) {

    if ($('#order-table').length > 0 && typeof $.fn.DataTable !== 'undefined') {
        $('#order-table').DataTable({
            responsive: true, 
            pageLength: 10
        });
    }

    // Modal tambah - button + clicked
    const modalTambah = document.getElementById('tambah');
    if (modalTambah) {
        modalTambah.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const sesiId = button.getAttribute('data-sesiid');
            const sesi = button.getAttribute('data-sesi');
            const kursusId = button.getAttribute('data-kursusid');
            const kursus = button.getAttribute('data-kursus');

            const modal = jQuery(this);

            modal.find('#txtsesiid').val(sesiId);
            modal.find('#txtsesi').val(sesi);
            modal.find('#txtkursusid').val(kursusId);
            modal.find('#txtkursus').val(kursus);
        });
    }

    // Modal kemaskini 
    const modalKemaskini = document.getElementById('kemaskini');
    if (modalKemaskini) {
        modalKemaskini.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const idClo = button.getAttribute('data-idclo');
            const kodClo = button.getAttribute('data-kodclo');
            const keteranganBm = button.getAttribute('data-keteranganbm');
            const ploIds = button.getAttribute('data-ploid');
            const kaedahIds = button.getAttribute('data-kaedahid');
            const nilaiIds = button.getAttribute('data-nilaiid');

            $('#kemaskini #txtidclo').val(idClo);
            $('#kemaskini #txtkodclo').val(kodClo);
            $('#kemaskini #txtketeranganclo').val(keteranganBm);
            
            var sesiTeks = $('#selectSesi option:selected').text().trim();
            var kursusTeks = $('#selectKursus option:selected').text().trim();
            $('#kemaskini #edit_txtsesi').val(sesiTeks);
            $('#kemaskini #edit_txtkursus').val(kursusTeks);
            $('#kemaskini .form-check-input').prop('checked', false);

            if (ploIds) {
                ploIds.split(',').forEach(function(id) {
                    $('#kemaskini .edit-chk-plo[value="' + id.trim() + '"]').prop('checked', true);
                });
            }

            if (kaedahIds) {
                kaedahIds.split(',').forEach(function(id) {
                    $('#kemaskini .edit-chk-kaedah[value="' + id.trim() + '"]').prop('checked', true);
                });
            }

            if (nilaiIds) {
                nilaiIds.split(',').forEach(function(id) {
                    $('#kemaskini .edit-chk-penilaian[value="' + id.trim() + '"]').prop('checked', true);
                });
            }
        });
    }

    // Submit Modal Form Tambah CLO
    $('#btnSimpanCLO').on('click', function (e) {
        e.preventDefault(); 
        
        var form = document.getElementById('formTambahCLO');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda mahu menyimpan maklumat CLO baharu ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            target: document.getElementById('tambah') 
        }).then((result) => {
            if (result.isConfirmed) {
                
                var formData = new FormData(form);
                
                fetch(base_url + 'pages/page-penyelaras-kursus/maklumat-clo/submit-clo.php', {
                    method: 'POST', 
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        
                        var modalElement = document.getElementById('tambah');
                        var modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if(modalInstance) modalInstance.hide();
                        
                        Swal.fire({ 
                            icon: 'success', 
                            title: 'Berjaya', 
                            text: res.message || 'Maklumat CLO berjaya disimpan.', 
                            timer: 1500, 
                            showConfirmButton: false 
                        }).then(() => location.reload());
                        
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menyimpan rekod', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Ralat', 'Berlaku ralat pelayan (Server Error).', 'error');
                });
            }
        });
    });    

    // Update CLO
    $('#btnSimpanKemaskini').on('click', function (e) {
        e.preventDefault(); 
        
        var form = document.getElementById('formKemaskiniCLO');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Simpan perubahan maklumat CLO ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            target: document.getElementById('kemaskini') 
        }).then((result) => {
            if (result.isConfirmed) {
                
                var formData = new FormData(form);
                
                fetch(base_url + 'pages/page-penyelaras-kursus/maklumat-clo/update-clo.php', { 
                    method: 'POST', 
                    body: formData 
                })
                .then(res => res.json())
                .then(res => {
                    $('#kemaskini').modal('hide');
                    $('.modal-backdrop').remove(); 
                    $('body').removeClass('modal-open').css('padding-right', '');
                    
                    setTimeout(() => {
                        if (res.status === 'success') {
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Berjaya', 
                                text: res.message || 'Maklumat CLO berjaya dikemaskini.', 
                                timer: 1500, 
                                showConfirmButton: false 
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal menyimpan rekod', 'error');
                        }
                    }, 300);
                })
                .catch(err => {
                    
                    $('#kemaskini').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                    
                    setTimeout(() => {
                        Swal.fire('Ralat', 'Berlaku ralat pelayan (Server Error).', 'error');
                    }, 300);
                });
            }
        });
    });

    // Delete CLO
    $(document).on('click', '.btn-delete-clo', function () {
        var idClo = $(this).data('idclo');
        
        Swal.fire({
            title: 'Hapus Maklumat?',
            text: "Rekod ini akan dihapuskan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#secondary',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var formData = new FormData();
                formData.append('id_clo', idClo);
                formData.append('action', 'delete');

                fetch(base_url + 'pages/page-penyelaras-kursus/maklumat-clo/update-clo.php', { 
                    method: 'POST', 
                    body: formData 
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success', 
                            title: 'Dihapus!', 
                            text: res.message, 
                            timer: 1500, 
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Ralat', 'Berlaku ralat pelayan.', 'error');
                });
            }
        });
    });
});