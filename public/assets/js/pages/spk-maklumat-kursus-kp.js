jQuery(document).ready(function ($) {

    if ($('#dataKursusDT').length > 0) {
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#dataKursusDT').DataTable({
                responsive: false, 
                scrollX: true,
                pageLength: 10
            });
        } else {
            console.error("AMARAN: Library DataTables tidak dijumpai. Pastikan jquery.dataTables.min.js dimuatkan.");
        }
    }

    $('.select2').each(function () {
        var $this = $(this);
        var $parentModal = $this.closest('.modal');

        $this.select2({
            placeholder: "- Sila Pilih -",
            allowClear: false,
            width: '100%',
            dropdownParent: $parentModal.length ? $parentModal : null
        });
    });

    // Modal tambah - button + clicked
    const modalTambah = document.getElementById('tambah');
    if (modalTambah) {
        modalTambah.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const sesiId = button.getAttribute('data-sesiid');
            const sesi = button.getAttribute('data-sesi');
            const programId = button.getAttribute('data-programid');
            const program = button.getAttribute('data-program');

            const modal = jQuery(this);

            modal.find('#txtsesiid').val(sesiId);
            modal.find('#txtsesi').val(sesi);
            modal.find('#txtprogramid').val(programId);
            modal.find('#txtprogram').val(program);
        });
    }

    // Kemaskini Kategori
    $(document).on('change', '.select-kategori', function () {
        var idKursus = $(this).data('idkursus');
        var kategori = $(this).val();
        if (kategori) updateKursusData({ id_kursus: idKursus, kategori_kursus: kategori });
    });

    // Kemaskini Penyelaras
    $(document).on('change', '.select-penyelaras', function () {
        var idKursus = $(this).data('idkursus');
        var penyelaras = $(this).val();
        if (penyelaras) updateKursusData({ id_kursus: idKursus, penyelaras_kursus: penyelaras });
    });

    //  Reset Penyelaras 
    $(document).on('click', '.btn-reset-penyelaras', function () {
        var idKursus = $(this).data('idkursus');
        
        if (!idKursus) return;

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Penyelaras bagi kursus ini akan dikosongkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                updateKursusData({ id_kursus: idKursus, action: 'reset_penyelaras' });
            }
        });
    });

    // Update 
    function updateKursusData(dataObj) {
        var formData = new FormData();
        for (var key in dataObj) formData.append(key, dataObj[key]);

        fetch(base_url + 'pages/page-ketua-program/maklumat-kursus-program/update-kursus.php', {
            method: 'POST', body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berjaya', text: res.message, timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Ralat', 'Berlaku ralat pelayan.', 'error');
        });
    }

    // Submit Modal Form Tambah Kursus
    $('#btnHantarKursus').on('click', function () {
        var form = document.getElementById('formTambahKursus');
        
        // Semak jika form lengkap (Required fields)
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
            target: document.getElementById('tambah') 
        }).then((result) => {
            if (result.isConfirmed) {
                
                var formData = new FormData(form);
                
                fetch(base_url + 'pages/page-ketua-program/maklumat-kursus-program/submit-kursus.php', {
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
                            text: res.message || 'Kursus berjaya disimpan.', 
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
});