<script>

    flatpickr("#tarikh_lahir", {
        dateFormat: "d/m/Y",
        allowInput: true
    });

    function checkFileSize(input) {
        const maxFileSize = 5 * 1024 * 1024; // 5MB
        if (input.files && input.files[0]) {
            const fileSize = input.files[0].size;
            if (size > maxFileSize) {
            input.setCustomValidity('<?= h(tr('profile_max_file_size','Max file size 5MB')) ?>');
            input.reportValidity();
            } else {
            input.setCustomValidity('');
            input.reportValidity();
            }
        }
    }
</script>