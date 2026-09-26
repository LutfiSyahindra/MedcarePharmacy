<script>
    $(function() {
        const typeLabels = @json($typeLabels);
        const typeIcons = {
            regular: 'mdi-file-document-outline',
            narkotika: 'mdi-alert-octagon-outline',
            psikotropika: 'mdi-brain',
            oot: 'mdi-account-check-outline',
            prekursor: 'mdi-flask-outline'
        };
        const rows = $('.sp-setting-row');
        const saveButton = $('#spSaveButton');
        const resetButton = $('#spResetChanges');
        const saveButtonDefault = saveButton.html();

        function normalize(value) {
            return String(value || '').toLocaleLowerCase('id-ID').trim();
        }

        function selectedType($row) {
            return $row.find('.sp-type-select').val() || '';
        }

        function resolveRowType($row) {
            const directType = selectedType($row);
            if (directType) return { type: directType, source: 'Diatur langsung' };

            let parentKey = $row.data('parent-key');
            while (parentKey) {
                const $parent = rows.filter(`[data-key="${parentKey}"]`);
                const parentType = selectedType($parent);
                if (parentType) return { type: parentType, source: 'Mengikuti induk' };
                parentKey = $parent.data('parent-key');
            }

            return { type: $row.data('automatic-type') || 'regular', source: 'Deteksi otomatis' };
        }

        function refreshEffectiveTypes() {
            rows.each(function() {
                const $row = $(this);
                const result = resolveRowType($row);
                const $badge = $row.find('[data-effective-badge]');

                $row.attr('data-effective-type', result.type).data('effective-type', result.type);
                $badge
                    .attr('class', `sp-type-badge is-${result.type}`)
                    .html(`<i class="mdi ${typeIcons[result.type]}"></i><span>${typeLabels[result.type]}</span>`);
                $row.find('[data-source-label]').text(result.source);
            });
        }

        function refreshDirtyState() {
            let dirtyCount = 0;
            rows.each(function() {
                const $row = $(this);
                const $select = $row.find('.sp-type-select');
                const dirty = String($select.val() || '') !== String($select.data('initial') || '');
                $row.toggleClass('is-dirty', dirty);
                if (dirty) dirtyCount++;
            });

            saveButton.prop('disabled', dirtyCount === 0);
            resetButton.prop('disabled', dirtyCount === 0);
            $('#spDirtyCount').text(dirtyCount).toggleClass('d-none', dirtyCount === 0);
        }

        function applyFilters() {
            const search = normalize($('#spSearch').val());
            const level = $('#spLevelFilter').val();
            const type = $('#spTypeFilter').val();
            let visibleCount = 0;

            rows.each(function() {
                const $row = $(this);
                const matchesSearch = !search || normalize($row.data('search')).includes(search);
                const matchesLevel = level === 'all' || $row.data('level') === level;
                const matchesType = type === 'all' || $row.data('effective-type') === type;
                const visible = matchesSearch && matchesLevel && matchesType;
                $row.toggle(visible);
                if (visible) visibleCount++;
            });

            $('#spVisibleCount').text(visibleCount.toLocaleString('id-ID'));
            $('#spNoResults').toggleClass('d-none', visibleCount !== 0 || rows.length === 0);
            $('#spSettingsForm .sp-table-wrap').toggleClass('d-none', visibleCount === 0 && rows.length > 0);
            $('#spClearSearch').toggle(Boolean(search));
        }

        $('.sp-type-select').on('change', function() {
            refreshEffectiveTypes();
            refreshDirtyState();
            applyFilters();
        });

        $('#spSearch').on('input', applyFilters);
        $('#spLevelFilter, #spTypeFilter').on('change', function() {
            $('.sp-summary-card').removeClass('is-active');
            applyFilters();
        });
        $('#spClearSearch').on('click', function() {
            $('#spSearch').val('').trigger('input').focus();
        });

        $('.sp-summary-card').on('click', function() {
            const type = $(this).data('filter-type');
            $('.sp-summary-card').removeClass('is-active');
            $(this).addClass('is-active');
            $('#spTypeFilter').val(type).trigger('change');
            $(this).addClass('is-active');
        });

        $('#spApplyBulk').on('click', function() {
            const visibleRows = rows.filter(':visible');
            if (!visibleRows.length) return;

            const label = $('#spBulkType option:selected').text();
            Swal.fire({
                icon: 'question',
                title: `Terapkan ${label}?`,
                text: `${visibleRows.length.toLocaleString('id-ID')} klasifikasi yang tampil akan diubah.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, terapkan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(result => {
                if (!result.isConfirmed) return;
                visibleRows.find('.sp-type-select').val($('#spBulkType').val());
                refreshEffectiveTypes();
                refreshDirtyState();
                applyFilters();
            });
        });

        resetButton.on('click', function() {
            rows.find('.sp-type-select').each(function() {
                $(this).val($(this).data('initial') || '');
            });
            refreshEffectiveTypes();
            refreshDirtyState();
            applyFilters();
        });

        $('#spSettingsForm').on('submit', function(event) {
            event.preventDefault();

            const assignments = rows.filter(function() {
                const $select = $(this).find('.sp-type-select');
                return String($select.val() || '') !== String($select.data('initial') || '');
            }).map(function() {
                const $select = $(this).find('.sp-type-select');
                return {
                    level: String($select.data('level')),
                    id: Number($select.data('id')),
                    sp_type: $select.val() || null
                };
            }).get();

            saveButton.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i><span>Menyimpan...</span>');
            resetButton.prop('disabled', true);

            $.ajax({
                url: @json(route('settings.surat-pesanan.update')),
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({ assignments }),
                headers: { 'X-CSRF-TOKEN': @json(csrf_token()) },
                success: function(response) {
                    rows.find('.sp-type-select').each(function() {
                        $(this).data('initial', $(this).val() || '');
                    });
                    Object.entries(response.summary?.types || {}).forEach(([type, count]) => {
                        $(`[data-summary-count="${type}"]`).text(Number(count).toLocaleString('id-ID'));
                    });
                    $('#spConfiguredCount').text(Number(response.summary?.configured || 0).toLocaleString('id-ID'));
                    refreshDirtyState();
                    Swal.fire({
                        icon: 'success',
                        title: response.message || 'Setting SP berhasil disimpan.',
                        toast: true,
                        position: 'top-end',
                        timer: 2600,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const message = Object.values(errors).flat()[0] || xhr.responseJSON?.message || 'Setting SP gagal disimpan.';
                    Swal.fire({ icon: 'error', title: 'Gagal', text: message });
                },
                complete: function() {
                    saveButton.html(saveButtonDefault);
                    refreshDirtyState();
                }
            });
        });

        refreshEffectiveTypes();
        refreshDirtyState();
        applyFilters();
    });
</script>
