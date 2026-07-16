<script>
    $(document).ready(function() {
        const saveButton = $('#notificationSettingsSave');
        const defaultSaveLabel = saveButton.html();

        $('#navbarLimit').on('input', function() {
            $('#navbarLimitValue').text(this.value);
        });

        function activeRoleCount() {
            return $('input[name="roles[admin]"]:checked, input[name="roles[apoteker]"]:checked').length;
        }

        $('#notificationSettingsForm').on('submit', function(event) {
            event.preventDefault();

            if (activeRoleCount() === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Role approver belum dipilih',
                    text: 'Aktifkan minimal Admin atau Apoteker.',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

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
