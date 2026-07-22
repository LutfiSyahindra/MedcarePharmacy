<script>
    $(function() {
        const page = $('.role-setting-page');
        const form = $('#roleSettingForm');
        const saveButtons = $('.js-role-setting-save');
        const saveDock = $('#roleSettingSaveDock');
        const saveState = $('#roleSettingSaveState');
        let activeFilter = 'all';
        let savedFormState = form.serialize();
        let hasUnsavedChanges = false;

        saveButtons.each(function() {
            $(this).data('default-content', $(this).html());
        });

        function selectedValue(card, selector) {
            return String(card.find(selector + ':checked').val() || '');
        }

        function setStatus(element, icon, text, stateClass) {
            element
                .removeClass('is-active is-all is-off')
                .addClass(stateClass)
                .html('<i class="mdi ' + icon + '"></i>' + text);
        }

        function syncCard(card) {
            const dataAllBranch = selectedValue(card, '.js-data-scope') === '1';
            const posAllBranch = selectedValue(card, '.js-pos-scope') === 'all_branches';
            const approver = card.find('.js-approver').is(':checked');
            const approvalAllBranch = selectedValue(card, '.js-approval-scope') === 'all_branches';
            const notification = card.find('.js-notification').is(':checked');
            const notificationAllBranch = selectedValue(card, '.js-notification-scope') === 'all_branches';

            card.toggleClass('is-all-branch', dataAllBranch);
            card.toggleClass('is-pos-all', posAllBranch);
            card.toggleClass('is-approver', approver);
            card.toggleClass('is-notification', notification);
            card.find('.js-approval-panel').toggleClass('is-off', !approver);
            card.find('.js-notification-panel').toggleClass('is-off', !notification);

            setStatus(
                card.find('.js-status-data'),
                dataAllBranch ? 'mdi-earth' : 'mdi-map-marker-outline',
                dataAllBranch ? 'Data: semua branch' : 'Data: branch user',
                dataAllBranch ? 'is-all' : ''
            );
            setStatus(
                card.find('.js-status-pos'),
                posAllBranch ? 'mdi-earth' : 'mdi-map-marker-outline',
                posAllBranch ? 'POS: semua branch' : 'POS: branch user',
                posAllBranch ? 'is-all' : ''
            );
            setStatus(
                card.find('.js-status-approval'),
                approver ? 'mdi-check-decagram-outline' : 'mdi-minus-circle-outline',
                approver ? (approvalAllBranch ? 'Approval: semua branch' : 'Approval: branch user') : 'Approval: nonaktif',
                approver ? 'is-active' : 'is-off'
            );
            setStatus(
                card.find('.js-status-notification'),
                notification ? 'mdi-bell-ring-outline' : 'mdi-bell-off-outline',
                notification ? (notificationAllBranch ? 'Notifikasi: semua branch' : 'Notifikasi: branch asal') : 'Notifikasi: nonaktif',
                notification ? 'is-active' : 'is-off'
            );
        }

        function updateSummary() {
            $('#allBranchCount').text($('.js-data-scope[value="1"]:checked').length);
            $('#posAllBranchCount').text($('.js-pos-scope[value="all_branches"]:checked').length);
            $('#approverCount').text($('.js-approver:checked').length);
            $('#notificationRoleCount').text($('.js-notification:checked').length);
        }

        function filterCards() {
            const query = ($('#roleSettingSearch').val() || '').toLocaleLowerCase('id-ID').trim();
            const cards = $('.role-setting-card');
            let visible = 0;

            cards.each(function() {
                const card = $(this);
                const roleName = String(card.data('role-name') || '');
                const nameMatches = !query || roleName.includes(query);
                const filterMatches = activeFilter === 'all'
                    || (activeFilter === 'approver' && card.find('.js-approver').is(':checked'))
                    || (activeFilter === 'all-branch' && selectedValue(card, '.js-data-scope') === '1')
                    || (activeFilter === 'pos-all' && selectedValue(card, '.js-pos-scope') === 'all_branches')
                    || (activeFilter === 'notification' && card.find('.js-notification').is(':checked'));
                const show = nameMatches && filterMatches;

                card.toggleClass('is-hidden', !show);
                if (show) visible++;
            });

            $('#roleSettingVisibleCount').text(visible + ' role ditampilkan');
            $('#roleSettingNoResult').prop('hidden', visible > 0 || cards.length === 0);
        }

        function setDirtyState(isDirty) {
            hasUnsavedChanges = isDirty;
            page.toggleClass('has-unsaved', isDirty);
            saveDock.prop('hidden', !isDirty);

            if (isDirty) {
                saveState.html('<i class="mdi mdi-alert-circle-outline"></i><span>Ada perubahan belum disimpan</span>');
            } else {
                saveState.html('<i class="mdi mdi-check-circle-outline"></i><span>Semua perubahan tersimpan</span>');
            }
        }

        function detectChanges() {
            setDirtyState(form.serialize() !== savedFormState);
        }

        $('.role-setting-card').each(function() {
            syncCard($(this));
        });
        updateSummary();
        filterCards();

        form.on('change', 'input', function() {
            syncCard($(this).closest('.role-setting-card'));
            updateSummary();
            filterCards();
            detectChanges();
        });

        $('#roleSettingSearch').on('input', filterCards);

        $('[data-role-filter]').on('click', function() {
            activeFilter = $(this).data('role-filter');
            $('[data-role-filter]').removeClass('is-active');
            $(this).addClass('is-active');
            filterCards();
        });

        $(window).on('beforeunload.roleSetting', function(event) {
            if (!hasUnsavedChanges) return;
            event.preventDefault();
            event.returnValue = '';
        });

        form.on('submit', function(event) {
            event.preventDefault();

            saveButtons.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i><span>Menyimpan...</span>');

            $.ajax({
                url: "{{ route("settings.role-setting.update") }}",
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    const summary = response.summary || {};
                    if (summary.all_branch !== undefined) $('#allBranchCount').text(summary.all_branch);
                    if (summary.pos_all_branch !== undefined) $('#posAllBranchCount').text(summary.pos_all_branch);
                    if (summary.approver !== undefined) $('#approverCount').text(summary.approver);
                    if (summary.notification !== undefined) $('#notificationRoleCount').text(summary.notification);

                    savedFormState = form.serialize();
                    setDirtyState(false);

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
                    const response = xhr.responseJSON || {};
                    const errors = response.errors || {};
                    const firstError = Object.values(errors).reduce(function(result, messages) {
                        if (result) return result;
                        return Array.isArray(messages) ? messages[0] : messages;
                    }, '');

                    Swal.fire({
                        icon: 'error',
                        title: 'Konfigurasi belum tersimpan',
                        text: firstError || response.message || 'Terjadi kesalahan saat menyimpan konfigurasi.'
                    });
                },
                complete: function() {
                    saveButtons.each(function() {
                        $(this).prop('disabled', false).html($(this).data('default-content'));
                    });
                }
            });
        });
    });
</script>
