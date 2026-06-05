// console.log('pengesahan-pelajar.js loaded');

let penerimaLoaded = false;
let perakuanloaded = false;

document.addEventListener('DOMContentLoaded', function () {
    loadDraft();
});

document.addEventListener('shown.bs.tab', function (event) {

    const target = event.target.getAttribute('href');

    if (target === '#maklumat-penerima-tab') {
        if (!penerimaLoaded) {
            loadPenerima();
        }
    }

    if (target === '#maklumat-perakuan-tab') {
        if (!perakuanloaded) {
            initPerakuan();
        }
    }    
});

function loadDraft() {

    fetch(
        base_url + 'pages/iCares/permohonan/pengesahan-pelajar/ajax/load-draft.php'
    )
    .then(res => res.json())
    .then(data => {

        //console.log('DRAFT LOADED:', data);

        let keepDataStudent = DRAFT.dataStudent;
        DRAFT = data;
        DRAFT.dataStudent = keepDataStudent; // pastikan dataStudent tetap ada 

        // kalau tab penerima dah loaded
        if (penerimaLoaded && data.penerima) {
            fillPenerimaForm(data.penerima);
        }

        if (data.perakuan) {
            // simpan draft perakuan ke global
            DRAFT.perakuan = data.perakuan;

            // kalau UI dah ready
            if (document.getElementById('formPerakuan')) {
                fillPerakuan();
            }
        }
    });
}


function loadPenerima() {

    if (penerimaLoaded) {
        return;
    }

    const box = document.getElementById('penerima-content');

    if (!box) {
        console.log('BOX NOT FOUND');
        return;
    }

    setSectionLoading(box, 'loading');

    fetch(
        base_url +
        'pages/iCares/permohonan/pengesahan-pelajar/ajax/load-penerima.php'
    )

    .then(res => {

        if (!res.ok) {
            throw new Error('Gagal load form penerima');
        }

        return res.text();
    })

    .then(html => {

        box.innerHTML = html;
        if (DRAFT.penerima) {
            fillPenerimaForm(DRAFT.penerima);
        }        
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

function fillPenerimaForm(data) {

    const form = document.getElementById('form-penerima');

    if (!form) return;

    Object.keys(data).forEach(key => {

        const input = form.querySelector(`[name="${key}"]`);

        if (input) {
            input.value = data[key];
        }
    });
}

function initAutoSavePenerima() {

    const form = document.getElementById('form-penerima');

    if (!form) return;

    let timeout;

    form.querySelectorAll('input, select').forEach(field => {

        const eventType =
            field.tagName === 'SELECT'
                ? 'change'
                : 'input';

        field.addEventListener(eventType, () => {

            clearTimeout(timeout);

            timeout = setTimeout(() => {
                autoSavePenerima(form);
            }, 500);
        });
    });
}

function autoSavePenerima(form) {

    const data = Object.fromEntries(
        new FormData(form)
    );

    DRAFT.penerima = data;

    console.log('UPDATED DRAFT:', DRAFT);

    saveDraft();
}

function saveDraft() {

    fetch(
        base_url +
        'pages/iCares/permohonan/pengesahan-pelajar/ajax/save-draft.php',
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(DRAFT)
        }
    )

    .then(res => res.json())

    .then(data => {
        console.log('Draft saved');
    })

    .catch(err => {
        console.error('Save failed', err);
    });
}

function initPerakuan() {

    const form = document.getElementById('formPerakuan');
    const btn = form?.querySelector('button[type="submit"]');

    if (!form || !btn) return;

    const chk1 = document.getElementById('chk1');
    const chk2 = document.getElementById('chk2');

    if (!DRAFT.perakuan) {
        DRAFT.perakuan = {};
    }

    function updateState() {

        DRAFT.perakuan.chk1 = chk1.checked ? 1 : 0;
        DRAFT.perakuan.chk2 = chk2.checked ? 1 : 0;

        const allChecked = chk1.checked && chk2.checked;

        btn.disabled = !allChecked;

        saveDraft();
    }

    chk1.addEventListener('change', updateState);
    chk2.addEventListener('change', updateState);

    fillPerakuan(); 
    updateState();
    perakuanloaded = true;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitPermohonan();
    });    
}

function fillPerakuan() {

    const chk1 = document.getElementById('chk1');
    const chk2 = document.getElementById('chk2');

    if (!chk1 || !chk2) return;

    chk1.checked = DRAFT.perakuan?.chk1 == 1;
    chk2.checked = DRAFT.perakuan?.chk2 == 1;
}

function submitPermohonan() {

    fetch(base_url + 'pages/iCares/permohonan/pengesahan-pelajar/ajax/submit-permohonan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(DRAFT)
    })
    .then(res => res.json())
    .then(res => {

        if (res.status === 'success') {

            Swal.fire({
                icon: 'success',
                title: 'Berjaya',
                text: 'Permohonan berjaya dihantar',
                confirmButtonText: 'OK'
            }).then(() => {

                window.location.href = base_url + 'pages/iCares/semakan/permohonan/index.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message || 'Gagal hantar permohonan'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ralat Server semasa submit'
        });
    });
}