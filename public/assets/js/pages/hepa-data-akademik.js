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
    $(document).on('submit', '#form-akademik', function (e) {
        e.preventDefault(); 
        
        submitDataSponsor(this);
    });    
});

function submitDataSponsor(formElement) {
    const formData = new FormData(formElement);

    const controllerUrl = base_url + 'pages/rekod-utama/data-akademik/ajax/submit-datasponsor.php';

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