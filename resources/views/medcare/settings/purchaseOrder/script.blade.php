<script>
    $(function() {
        const rows = $('.po-setting-row');
        const saveButton = $('#poSettingSave');
        const resetButton = $('#poSettingReset');
        const saveButtonDefault = saveButton.html();
        let activeFilter = 'all';

        function isManual($row) {
            return $row.find('.po-setting-toggle').prop('checked');
        }

        function refreshRowModes() {
            rows.each(function() {
                const $row = $(this);
                const manual = isManual($row);
                $row.attr('data-mode', manual ? 'manual' : 'automatic').data('mode', manual ? 'manual' : 'automatic');
                $row.find('[data-mode-badge]')
                    .attr('class', `po-setting-mode-badge is-${manual ? 'manual' : 'automatic'}`)
                    .html(manual
                        ? '<i class="mdi mdi-form-textbox"></i><span>Manual</span>'
                        : '<i class="mdi mdi-auto-fix"></i><span>Otomatis</span>');
            });

            const manualCount = rows.filter(function() { return isManual($(this)); }).length;
            $('#poSettingManualCount').text(manualCount.toLocaleString('id-ID'));
            $('#poSettingAutomaticCount').text((rows.length - manualCount).toLocaleString('id-ID'));
        }

        function refreshDirtyState() {
            let dirtyCount = 0;

            rows.each(function() {
                const $row = $(this);
                const $toggle = $row.find('.po-setting-toggle');
                const dirty = ($toggle.prop('checked') ? '1' : '0') !== String($toggle.data('initial'));
                $row.toggleClass('is-dirty', dirty);
                if (dirty) dirtyCount++;
            });

            saveButton.prop('disabled', dirtyCount === 0);
            resetButton.prop('disabled', dirtyCount === 0);
            $('#poSettingDirtyCount').text(dirtyCount).toggleClass('d-none', dirtyCount === 0);
        }

        function applyFilters() {
            const search = String($('#poSettingSearch').val() || '').toLocaleLowerCase('id-ID').trim();
            let visibleCount = 0;

            rows.each(function() {
                const $row = $(this);
                const matchesSearch = !search || String($row.data('search') || '').includes(search);
                const matchesFilter = activeFilter === 'all'
                    || $row.data('mode') === activeFilter
                    || (activeFilter === 'active' && String($row.data('active')) === '1');
                const visible = matchesSearch && matchesFilter;

                $row.toggleClass('is-hidden', !visible);
                if (visible) visibleCount++;
            });

            $('#poSettingVisibleCount').text(visibleCount.toLocaleString('id-ID'));
            $('#poSettingNoResult').toggleClass('d-none', visibleCount !== 0 || rows.length === 0);
            $('#poSettingForm').toggleClass('d-none', visibleCount === 0 && rows.length > 0);
            $('#poSettingClearSearch').toggle(Boolean(search));
        }

        function setVisibleRows(manual) {
            rows.not('.is-hidden').find('.po-setting-toggle').prop('checked', manual);
            refreshRowModes();
            refreshDirtyState();
            applyFilters();
        }

        $('.po-setting-toggle').on('change', function() {
            refreshRowModes();
            refreshDirtyState();
            applyFilters();
        });

        $('#poSettingSearch').on('input', applyFilters);
        $('#poSettingClearSearch').on('click', function() {
            $('#poSettingSearch').val('').trigger('input').focus();
        });

        $('.po-setting-summary-card').on('click', function() {
            activeFilter = String($(this).data('filter'));
            $('.po-setting-summary-card').removeClass('is-active');
            $(this).addClass('is-active');
            applyFilters();
        });

        $('#poSettingVisibleAutomatic').on('click', function() { setVisibleRows(false); });
        $('#poSettingVisibleManual').on('click', function() { setVisibleRows(true); });

        resetButton.on('click', function() {
            rows.find('.po-setting-toggle').each(function() {
                $(this).prop('checked', String($(this).data('initial')) === '1');
            });
            refreshRowModes();
            refreshDirtyState();
            applyFilters();
        });

        $('#poSettingForm').on('submit', function(event) {
            event.preventDefault();

            const manualDistributorIds = rows.find('.po-setting-toggle:checked').map(function() {
                return Number($(this).val());
            }).get();

            saveButton.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i><span>Menyimpan...</span>');
            resetButton.prop('disabled', true);

            $.ajax({
                url: @json(route('settings.purchase-order.update')),
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({ manual_distributor_ids: manualDistributorIds }),
                headers: { 'X-CSRF-TOKEN': @json(csrf_token()) },
                success: function(response) {
                    rows.find('.po-setting-toggle').each(function() {
                        $(this).data('initial', $(this).prop('checked') ? '1' : '0');
                    });
                    refreshDirtyState();
                    Swal.fire({
                        icon: 'success',
                        title: response.message || 'Setting PO berhasil disimpan.',
                        toast: true,
                        position: 'top-end',
                        timer: 2600,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const message = Object.values(errors).flat()[0] || xhr.responseJSON?.message || 'Setting PO gagal disimpan.';
                    Swal.fire({ icon: 'error', title: 'Gagal', text: message });
                },
                complete: function() {
                    saveButton.html(saveButtonDefault);
                    refreshDirtyState();
                }
            });
        });

        refreshRowModes();
        refreshDirtyState();
        applyFilters();
    });
</script>
