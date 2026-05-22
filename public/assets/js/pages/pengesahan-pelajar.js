console.log('pengesahan-pelajar.js loaded');

let penerimaLoaded = false;
let lastData = {};

function loadPenerima() {

    if (penerimaLoaded) {
        return;
    }
    //console.log('Loading penerima form...');
    const box = document.getElementById('penerima-content');

    if (!box) {
        console.log('BOX NOT FOUND');
        return;
    }

    setSectionLoading(box, 'loading');

    fetch(base_url + 'pages/iCares/permohonan/pengesahan-pelajar/ajax/load-penerima.php')

        .then(res => {

            if (!res.ok) {
                throw new Error('Gagal load form penerima');
            }

            return res.text();
        })

        .then(html => {

            box.innerHTML = html;
            initAutoSavePenerima(); 
            penerimaLoaded = true;
        })

        .catch(err => {

            console.log(err);

            box.innerHTML = `
                <div class="alert alert-danger">
                    Gagal load form
                </div>
            `;
        });
}

function initAutoSavePenerima() {

    const form = document.getElementById('form-penerima');

    if (!form) return;

    let timeout;

    form.querySelectorAll('input').forEach(input => {

        input.addEventListener('input', () => {

            clearTimeout(timeout);

            timeout = setTimeout(() => {
                autoSavePenerima();
            }, 800); // debounce 0.8s
        });

    });
}

function autoSavePenerima() {

    const form = document.getElementById('form-penerima');

    if (!form) return;

    const formData = new FormData(form);
    const obj = Object.fromEntries(formData);

    if (JSON.stringify(obj) === JSON.stringify(lastData)) {
        return;
    }

    lastData = obj;

    fetch(base_url + 'pages/iCares/permohonan/pengesahan-pelajar/ajax/save-penerima.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        //console.log('AUTO SAVED:', data);
    })
    .catch(err => {
        //console.log('SAVE ERROR', err);
    });
}

// tab listener to load content on demand when tab is shown
document.addEventListener('shown.bs.tab', function (event) {
    console.log('BOOTSTRAP TAB LISTENER READY');
    const target = event.target.getAttribute('href');

    console.log('TAB SHOWN:', target);

    if (target === '#maklumat-penerima-tab') {
        if (!penerimaLoaded) {
            loadPenerima();
        }
    } 

});