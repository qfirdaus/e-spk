<script>
function checkFileSize(input) {
  const maxFileSize = 5 * 1024 * 1024;
  if (!input || !input.files || !input.files[0]) {
    return;
  }

  const fileSize = input.files[0].size;
  if (fileSize > maxFileSize) {
    input.setCustomValidity(<?= json_encode(tr('profile_max_file_size', 'Max file size 5MB'), JSON_UNESCAPED_UNICODE) ?>);
    input.reportValidity();
    input.value = '';
    return;
  }

  input.setCustomValidity('');
}

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.chk');
    const button = document.getElementById('btn-submit-<?= h($istarPerakuanIdPrefix) ?>');

    checkboxes.forEach(chk => {
        chk.addEventListener('change', () => {
            let allChecked = true;
            checkboxes.forEach(c => {
                if (!c.checked) allChecked = false;
            });
            button.disabled = !allChecked;
        });
    });  
    initDatePicker();
});

function initDatePicker(parent = document) {

    jQuery(parent).find('.datepicker').each(function () {

        // prevent duplicate init
        if (jQuery(this).data('daterangepicker')) {
            return;
        }

        jQuery(this).daterangepicker({
            singleDatePicker: true,
            autoApply: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            }
        });

    });

}

document.addEventListener('shown.bs.modal', function (event) {

    const modal = event.target;

    initDatePicker(modal);

});

document.addEventListener('input', function (e) {

    if (e.target.matches('input.uppercase')) {
        e.target.value = e.target.value.toUpperCase();
    }

});
</script>
