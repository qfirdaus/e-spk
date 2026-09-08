$(document).ready(function() {
    
    // Show Detail
    $(document).on('click', '.btnLihatPerincian', function() {
        var idKursus = $(this).data('idkursus');
        
        $('#loadingPerincian').removeClass('d-none');
        $('#kandunganPerincian').addClass('d-none');
        
        $('#detailRujukan').empty();
        $('#detailKemahiran').empty();
        $('#detailSenaraiCLO').empty();
        
        const formData = new FormData();
        formData.append('id_kursus', idKursus);

        fetch(base_url + 'pages/page-ketua-program/laporan/senarai-kursus/get-detail.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            $('#loadingPerincian').addClass('d-none');
            
            if (res.status === 'success') {
                $('#kandunganPerincian').removeClass('d-none');
                
                let data = res.data;
                let course = data.course || {};
                let prereq = data.prereq || {};
                
                $('#detailKod').text(course.kod_kursus || '-');
                $('#detailNama').text(course.subjekbm || '-');
                $('#detailKredit').text(course.kredit || '-');
                
                let sesiTahun = (course.sem_pengajian || '') + ' - ' + (course.tahun_pengajian || '');
                $('#detailSesi').text(sesiTahun.trim() === '-' ? 'Tidak Ditetapkan' : sesiTahun);
                
                let namaPenyelaras = (course.gelar_nama || '') + ' - ' + (course.penyelaras_kursus || '');
                $('#detailPenyelaras').text(namaPenyelaras.trim() === '-' ? 'Tiada Penyelaras' : namaPenyelaras);
                
                $('#detailSinopsis').text(course.sinopsis_bm || 'Tiada maklumat.');
                
                // Keperluan & Tambahan
                $('#detailKeperluan').text(course.special_requirement || 'Tiada keperluan khas.');
                $('#detailLainLain').text(course.other_information || 'Tiada maklumat tambahan.');
                
                let syarat = [prereq.f240psyarat1, prereq.f240psyarat2, prereq.f240psyarat3, prereq.f240psyarat4, prereq.f240psyarat5]
                             .filter(val => val && val.trim() !== '').join(' ');
                $('#detailPrasyarat').text(syarat || 'Tiada prasyarat khusus.');
                
                // Rujukan 
                if (data.references && data.references.length > 0) {
                    data.references.forEach(function(ref) {
                        $('#detailRujukan').append(`<li>${ref.reference_desc}</li>`);
                    });
                } else {
                    $('#detailRujukan').append(`<li class="text-muted text-decoration-none" style="list-style-type: none; margin-left: -1rem;">Tiada senarai rujukan.</li>`);
                }

                // Kemahiran 
                if (data.skills && data.skills.length > 0) {
                    data.skills.forEach(function(skill) {
                        $('#detailKemahiran').append(`<li>${skill.kemahiran}</li>`);
                    });
                } else {
                    $('#detailKemahiran').append(`<li class="text-muted text-decoration-none" style="list-style-type: none; margin-left: -1rem;">Tiada rekod kemahiran.</li>`);
                }

                // Senarai CLO 
                if (data.clos && data.clos.length > 0) {
                    data.clos.forEach(function(clo) {
                        let plos = clo.plos && clo.plos.length > 0 ? clo.plos.join(', ') : '-';
                        let methods = clo.methods && clo.methods.length > 0 ? clo.methods.join('<br>') : '-';
                        let assessments = clo.assessments && clo.assessments.length > 0 ? clo.assessments.join('<br>') : '-';
                        
                        let rowHTML = `
                            <tr>
                                <td class="text-center fw-bold text-primary">${clo.kod_clo}</td>
                                <td>${clo.keterangan_bm}</td>
                                <td class="text-center">${plos}</td>
                                <td>${methods}</td>
                                <td>${assessments}</td>
                            </tr>
                        `;
                        $('#detailSenaraiCLO').append(rowHTML);
                    });
                } else {
                    $('#detailSenaraiCLO').append(`<tr><td colspan="5" class="text-center text-muted py-3">Tiada rekod Hasil Pembelajaran (CLO).</td></tr>`);
                }

            } else {
                Swal.fire('Ralat', res.message, 'error');
            }
        })
        .catch(err => {
            $('#loadingPerincian').addClass('d-none');
            console.error("Ralat Fetch:", err);
            Swal.fire('Ralat', 'Berlaku ralat semasa menyambung ke pelayan.', 'error');
        });
    });

    // DataTables 
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#tableLaporanKursus').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ms.json"
            }
        });
    }

    if ($('.select2').length && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%'
        });
    }

    if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

});