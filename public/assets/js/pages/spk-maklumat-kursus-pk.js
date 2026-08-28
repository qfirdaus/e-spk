$(document).ready(function() {
    $('#dataKursusDT').DataTable({
        responsive: false, 
        scrollX: false,    
        autoWidth: false,
        columnDefs: [
            { orderable: false, targets: [1, 3, 4, 5, 6, 7, 8] } 
        ]
    });
});

document.addEventListener('DOMContentLoaded', function () {    
    const modalKemaskini = document.getElementById('kemaskini');
    if (modalKemaskini) {
        modalKemaskini.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            const semester = button.getAttribute('data-semester');
            const kursusId = button.getAttribute('data-kursusid');
            const kursusNama = button.getAttribute('data-kursus');
            const term = button.getAttribute('data-term');
            const sinopsis = button.getAttribute('data-sinopsis');
            const bilsem = button.getAttribute('data-bilsem');
            const biltahun = button.getAttribute('data-biltahun');
            const req = button.getAttribute('data-req');
            const other = button.getAttribute('data-other');
            
            $('#txtkursusid').val(kursusId);
            $('#txtterm').val(term);
            $('#txtkursus').val(kursusNama);
            $('#txtsesi').val(semester); 
            $('#txtsinopsis').val(sinopsis);
            $('#txtsem').val(bilsem);
            $('#txttahun').val(biltahun);
            $('#txtrequirement').val(req);
            $('#txtotherinfo').val(other);
            
            $('#div_continuous').empty();
            $('#div_final').empty();
            $('#div_idpenilaian').empty();
            $('#dynamic_field_rujukan').empty();
            $('#div_kemahiran').html('<span class="text-muted small"><i class="ri-loader-4-line ri-spin"></i> Memuatkan data...</span>');

            // FETCH DATA PENILAIAN
            $.ajax({
                url: base_url + 'pages/page-penyelaras-kursus/maklumat-kursus-program/get-penilaian.php',
                type: 'post',
                data: { kursus_id: kursusId, term: term },
                dataType: 'json',
                success: function(response) {
                    $('#div_continuous').empty();
                    $('#div_final').empty();
                    
                    const len = response.length;
                    $('#txt_len').val(len);

                    for (let i = 0; i < len; i++) {
                        let id_penilaian = response[i].id;
                        let penilaian = response[i].penilaian;
                        let jenis = response[i].jenis;
                        let percent = response[i].percentage;
                        let f2f = response[i].f2f;
                        let nf2f = response[i].nf2f;

                        $('#div_idpenilaian').append('<input name="txt_idpenilaian' + i + '" type="hidden" value="' + id_penilaian + '">');

                        let htmlPenilaian = `
                            <div class="row align-items-center mb-3">
                                <label class="col-sm-3 col-form-label fw-semibold">${penilaian}</label>
                                <label class="col-sm-2 col-form-label text-end small">Percentage(%)</label>
                                <div class="col-sm-1">
                                    <input name="c_percent${i}" type="text" class="form-control form-control-sm" value="${percent}">
                                </div>
                                <label class="col-sm-1 col-form-label text-end small">F2F</label>
                                <div class="col-sm-2">
                                    <input name="c_f2f${i}" type="text" class="form-control form-control-sm" value="${f2f}">
                                </div>
                                <label class="col-sm-1 col-form-label text-end small">NF2F</label>
                                <div class="col-sm-2">
                                    <input name="c_nf2f${i}" type="text" class="form-control form-control-sm" value="${nf2f}">
                                </div>
                            </div>
                        `;

                        if (jenis == 1) { // Continuous
                            $('#div_continuous').append(htmlPenilaian);
                        } else { // Final
                            $('#div_final').append(htmlPenilaian);
                        }
                    }
                }
            });

            // FETCH DATA RUJUKAN
            $.ajax({
                url: base_url + 'pages/page-penyelaras-kursus/maklumat-kursus-program/get-rujukan.php',
                type: 'post',
                data: { kursus_id: kursusId, term: term },
                dataType: 'json',
                success: function(response) {
                    $('#dynamic_field_rujukan').empty();
                    const len = response.length;
                    $('#count').val(len);

                    for (let i = 0; i < len; i++) {
                        let reference = response[i].reference;
                        let b = i + 1;
                        
                        let htmlRujukan = `
                            <div class="input-group mb-2" id="rowRef${b}">
                                <input type="text" name="ref${b}" class="form-control" placeholder="Masukkan teks rujukan..." value="${reference}">
                                <button type="button" class="btn btn-outline-danger" onclick="buangInputRujukan(${b})" title="Buang Rujukan">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        `;
                        $('#dynamic_field_rujukan').append(htmlRujukan);
                    }
                }
            });

            // FETCH DATA KEMAHIRAN 
            $.ajax({
                url: base_url + 'pages/page-penyelaras-kursus/maklumat-kursus-program/get-kemahiran.php',
                type: 'post',
                data: { kursus_id: kursusId },
                dataType: 'json',
                success: function(response) {
                    $('#div_kemahiran').empty();
                    
                    if (response.length === 0) {
                        $('#div_kemahiran').html('<span class="text-danger small">Tiada senarai kemahiran djumpai dalam sistem.</span>');
                        return;
                    }

                    // Loop setiap kemahiran yang wujud
                    for (let i = 0; i < response.length; i++) {
                        let idKemahiran = response[i].id_kemahiran;
                        let namaKemahiran = response[i].kemahiran;
                        let isChecked = response[i].is_selected == 1 ? 'checked' : '';

                        let htmlCheckbox = `
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="chkkemahiran[]" value="${idKemahiran}" id="chkKemahiran${idKemahiran}" ${isChecked}>
                                    <label class="form-check-label" for="chkKemahiran${idKemahiran}">
                                        ${namaKemahiran}
                                    </label>
                                </div>
                            </div>
                        `;
                        $('#div_kemahiran').append(htmlCheckbox);
                    }
                },
                error: function() {
                    $('#div_kemahiran').html('<span class="text-danger small">Gagal memuatkan senarai kemahiran.</span>');
                }
            });
        });
    }

    $('#btnSimpanKemaskiniKursus').on('click', function (e) {
        e.preventDefault(); 
        
        var form = document.getElementById('formKemaskiniKursus');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Sahkan Kemaskini?',
            text: "Adakah anda pasti untuk menyimpan maklumat kursus ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
            // DIBUANG: target (Biar ia guna default body)
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: 'Sedang menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                var formData = new FormData(form);
                
                fetch(base_url + 'pages/page-penyelaras-kursus/maklumat-kursus-program/update-kursus.php', { 
                    method: 'POST', 
                    body: formData 
                })
                .then(res => res.json())
                .then(res => {
                    
                    // Tutup Swal loading
                    Swal.close();
                    
                    if (res.status === 'success') {
                        const modalElement = document.getElementById('kemaskini');
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        
                        setTimeout(() => {
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Berjaya', 
                                text: res.message || 'Maklumat berjaya dikemaskini.', 
                                timer: 1500, 
                                showConfirmButton: false 
                            }).then(() => location.reload());
                        }, 400);

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Gagal menyimpan rekod'
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat Pelayan',
                        text: 'Berlaku ralat sistem.' //semak console
                    });
                    console.error(err);
                });
            }
        });
    });

});

window.tambahInputRujukan = function() {
    let countInput = document.getElementById("count");
    let i = parseInt(countInput.value) || 0;
    i++;
    
    let html = `
        <div class="input-group mb-2" id="rowRef${i}">
            <input type="text" name="ref${i}" class="form-control" placeholder="Masukkan teks rujukan...">
            <button type="button" class="btn btn-outline-danger" onclick="buangInputRujukan(${i})" title="Buang Rujukan">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    `;
    
    $('#dynamic_field_rujukan').append(html);
    countInput.value = i;
}

window.buangInputRujukan = function(id) {
    $('#rowRef' + id).remove();
}