const saveTimers = {};
//console.log('JS LOADED');

function konvoText(key, fallback) {
    if (window.konvoI18n && Object.prototype.hasOwnProperty.call(window.konvoI18n, key)) {
        return window.konvoI18n[key];
    }
    return fallback;
}

// Function to submit to server
// #### JQuery Functions ####

jQuery(document).ready(function ($){   
    //file upload
    $(document).on('change', '.input-upload-fail', function() {
        let fileInput = this;
        
        if (fileInput.files.length > 0) {
            let file = fileInput.files[0];
            let fileSize = file.size / 1024 / 1024; // Tukar saiz kepada MB
            let fileName = file.name;
            let fileExtension = fileName.split('.').pop().toLowerCase();
            
            let allowedExtensions = ['jpg', 'jpeg', 'pdf'];
            if ($.inArray(fileExtension, allowedExtensions) == -1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Fail Tidak Sah',
                    text: 'Sila pilih fail dalam format JPG, JPEG atau PDF sahaja.',
                    confirmButtonColor: '#3085d6'
                });
                
                $(this).val(''); // Reset fail
                return false;
            }
            
            // Maksimum 5MB
            if (fileSize > 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'Saiz Fail Terlalu Besar',
                    text: 'Saiz fail yang dipilih adalah ' + fileSize.toFixed(2) + 'MB. Maksimum saiz yang dibenarkan adalah 5MB.',
                    confirmButtonColor: '#3085d6'
                });
                
                $(this).val(''); // Reset fail
                return false;
            }
        }
    });

    $(document).on('click', '.btn-tukar-fail', function() {
        let parentContainer = $(this).closest('.upload-wrapper');
        parentContainer.find('.existing-file-section').addClass('d-none'); 
        parentContainer.find('.new-file-section').removeClass('d-none');   
    });

    $(document).on('click', '.btn-batal-tukar', function() {
        let parentContainer = $(this).closest('.upload-wrapper');
        parentContainer.find('.input-upload-fail').val('');                    
        parentContainer.find('.new-file-section').addClass('d-none');      
        parentContainer.find('.existing-file-section').removeClass('d-none');
    });
    
    // Lain-lain Perkhidmatan Beruniform
    function toggleUniformLain() {
        let selectedValue = $('#select-jenis-uniform').val();
        
        if (selectedValue === 'OTH') {
            $('#section-uniform-lain').slideDown(200); 
        } else {
            $('#section-uniform-lain').slideUp(200);   
            $('#perkhidmatan_beruniform_lain').val(''); 
        }
    }

    $(document).on('change', '#select-jenis-uniform', function() {
        toggleUniformLain();
    });    

    //submit form
    $(document).on('submit', '#form-bapa', function (e) {
        e.preventDefault(); 
        
        submitDataBapa(this);
    });     

    $(document).on('submit', '#form-ibu', function (e) {
        e.preventDefault(); 
        
        submitDataIbu(this);
    });    
});

// Function to submit to server

function submitDataBapa(formElement) {
    const formData = new FormData(formElement);

    const controllerUrl = base_url + 'pages/rekod-utama/data-keluarga/ajax/submit-databapa.php';

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berjaya',
                text: res.message || 'Rekod berjaya disimpan',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = window.location.href;
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
        console.log(err);
        Swal.fire({
            icon: 'error',
            title: 'Ralat',
            text: 'Ralat pelayan (Server Error) semasa menyimpan data'
        });
    });
}

function submitDataIbu(formElement) {
    const formData = new FormData(formElement);

    const controllerUrl = base_url + 'pages/rekod-utama/data-keluarga/ajax/submit-dataibu.php';

    fetch(controllerUrl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berjaya',
                text: res.message || 'Rekod berjaya disimpan',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = window.location.href;
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
        console.log(err);
        Swal.fire({
            icon: 'error',
            title: 'Ralat',
            text: 'Ralat pelayan (Server Error) semasa menyimpan data'
        });
    });
}