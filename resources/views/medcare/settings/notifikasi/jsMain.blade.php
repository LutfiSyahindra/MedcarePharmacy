<script>
    $(document).ready(function() {
        const saveButton = $('#notificationSettingsSave');
        const defaultSaveLabel = saveButton.html();

        $('#navbarLimit').on('input', function() {
            $('#navbarLimitValue').text(this.value);
        });

        $('#notificationSettingsForm').on('submit', function(event) {
            event.preventDefault();

            saveButton
                .prop('disabled', true)
                .html('<i class="mdi mdi-loading mdi-spin"></i><span>Menyimpan...</span>');

            $.ajax({
                url: "{{ route("settings.notifikasi.update") }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.message || 'Konfigurasi disimpan',
                        toast: true,
                        position: 'top-end',
                        timer: 2600,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan konfigurasi.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: message
                    });
                },
                complete: function() {
                    saveButton.prop('disabled', false).html(defaultSaveLabel);
                }
            });
        });
    });
</script>
