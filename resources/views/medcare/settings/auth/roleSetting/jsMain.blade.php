<script>
    $(function() {
        const form = $('#roleSettingForm');
        const saveButton = $('#roleSettingSave');
        const defaultSaveContent = saveButton.html();
        let activeFilter = 'all';

        function syncRow(row) {
            const approver = row.find('.js-approver').is(':checked');
            const notification = row.find('.js-notification').is(':checked');

            row.find('.js-approval-scope').prop('disabled', !approver);
            row.find('.js-notification-scope').prop('disabled', !notification);
            row.toggleClass('is-approver', approver);
            row.toggleClass('is-notification', notification);
            row.toggleClass('is-all-branch', row.find('.js-all-branch').is(':checked'));
        }

        function updateSummary() {
            $('#allBranchCount').text($('.js-all-branch:checked').length);
            $('#approverCount').text($('.js-approver:checked').length);
            $('#notificationRoleCount').text($('.js-notification:checked').length);
        }

        function filterRows() {
            const query = ($('#roleSettingSearch').val() || '').toLocaleLowerCase('id-ID').trim();
            let visible = 0;

            $('.role-setting-row').each(function() {
                const row = $(this);
                const nameMatches = !query || String(row.data('role-name')).includes(query);
                const filterMatches = activeFilter === 'all'
                    || (activeFilter === 'approver' && row.find('.js-approver').is(':checked'))
                    || (activeFilter === 'all-branch' && row.find('.js-all-branch').is(':checked'))
                    || (activeFilter === 'notification' && row.find('.js-notification').is(':checked'));
                const show = nameMatches && filterMatches;

                row.toggleClass('is-hidden', !show);
                if (show) visible++;
            });

            $('#roleSettingNoResult').prop('hidden', visible > 0 || $('.role-setting-row').length === 0);
        }

        $('.role-setting-row').each(function() { syncRow($(this)); });
        updateSummary();

        form.on('change', '.role-setting-toggle', function() {
            syncRow($(this).closest('.role-setting-row'));
            updateSummary();
            filterRows();
        });

        $('#roleSettingSearch').on('input', filterRows);

        $('[data-role-filter]').on('click', function() {
            activeFilter = $(this).data('role-filter');
            $('[data-role-filter]').removeClass('is-active');
            $(this).addClass('is-active');
            filterRows();
        });

        form.on('submit', function(event) {
            event.preventDefault();

            saveButton.prop('disabled', true)
                .html('<i class="mdi mdi-loading mdi-spin"></i><span>Menyimpan...</span>');

            $.ajax({
                url: "{{ route("settings.role-setting.update") }}",
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    const summary = response.summary || {};
                    if (summary.all_branch !== undefined) $('#allBranchCount').text(summary.all_branch);
                    if (summary.approver !== undefined) $('#approverCount').text(summary.approver);
                    if (summary.notification !== undefined) $('#notificationRoleCount').text(summary.notification);

                    Swal.fire({
                        icon: 'success',
                        title: response.message || 'Konfigurasi berhasil disimpan',
                        toast: true,
                        position: 'top-end',
                        timer: 2600,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const firstError = Object.values(errors).flat()[0];

                    Swal.fire({
                        icon: 'error',
                        title: 'Konfigurasi belum tersimpan',
                        text: firstError || xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan konfigurasi.'
                    });
                },
                complete: function() {
                    saveButton.prop('disabled', false).html(defaultSaveContent);
                }
            });
        });
    });
</script>
