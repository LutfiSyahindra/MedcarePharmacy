<script>
    window.AuthUI = window.AuthUI || (function() {
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getInitials(value) {
            const words = String(value || 'NA').trim().split(/\s+/).filter(Boolean);
            return words.slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase() || 'NA';
        }

        function dataTableOptions(options) {
            return $.extend(true, {
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                order: [],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                dom: '<"auth-table-meta"l>rt<"auth-table-footer"ip>',
                language: {
                    processing: '<span class="auth-loading"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Memuat data...</span>',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    zeroRecords: 'Data belum ditemukan',
                    emptyTable: 'Belum ada data untuk ditampilkan',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Belum ada data',
                    infoFiltered: '(difilter dari _MAX_ total)',
                    search: 'Cari:',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Selanjutnya',
                        previous: 'Sebelumnya'
                    }
                }
            }, options);
        }

        function updateTableStats(table, config) {
            if (!table || !table.page) return;

            const info = table.page.info();
            const total = Number(info.recordsTotal || 0).toLocaleString('id-ID');
            const filtered = Number(info.recordsDisplay || 0).toLocaleString('id-ID');

            $(config.totalTarget).text(total);
            $(config.filteredTarget).text(filtered);
            updateSelectedCount(config.tableSelector, config.selectedTarget);
        }

        function updateSelectedCount(tableSelector, selectedTarget) {
            if (!selectedTarget) return;
            const selected = $(`${tableSelector} tbody tr.is-selected`).length;
            $(selectedTarget).text(Number(selected).toLocaleString('id-ID'));
        }

        function initTableTools(config) {
            const table = config.table;
            const $table = $(config.tableSelector);
            const $search = $(config.searchSelector);
            let searchTimer = null;

            $search.on('input', function() {
                const value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    table.search(value).draw();
                }, 220);
            });

            $('.auth-refresh-table').on('click', function() {
                const $button = $(this);
                $button.addClass('disabled');
                table.ajax.reload(function() {
                    $button.removeClass('disabled');
                }, false);
            });

            $table.find('tbody').on('click', 'tr', function(event) {
                if ($(event.target).closest('button, a, input, label, .form-check, .select2-container').length) {
                    return;
                }
                $(this).toggleClass('is-selected');
                updateSelectedCount(config.tableSelector, config.selectedTarget);
            });

            table.on('draw', function() {
                updateTableStats(table, config);
                initTooltips();
            });

            updateTableStats(table, config);
            initTooltips();
        }

        function initTooltips() {
            if (!window.bootstrap || !bootstrap.Tooltip) return;

            document.querySelectorAll('.auth-page [title]').forEach(function(el) {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el);
                }
            });
        }

        function wirePasswordToggles() {
            $('.auth-password-toggle').off('click.authPassword').on('click.authPassword', function() {
                const target = $(this).data('target');
                const $input = $(target);
                const nextType = $input.attr('type') === 'password' ? 'text' : 'password';

                $input.attr('type', nextType);
                $(this).find('i')
                    .toggleClass('mdi-eye-outline', nextType === 'password')
                    .toggleClass('mdi-eye-off-outline', nextType !== 'password');
            });
        }

        function clearValidation(formSelector) {
            const $form = $(formSelector);
            $form.find('.invalid-feedback').text('');
            $form.find('.form-control, .form-select').removeClass('is-invalid');
            $form.find('.auth-field').removeClass('has-error');
        }

        function updateSelectCount(selectSelector, targetSelector) {
            const count = ($(selectSelector).val() || []).length;
            $(targetSelector).text(Number(count).toLocaleString('id-ID'));
        }

        function setButtonLoading(buttonSelector, isLoading, loadingLabel, normalLabel) {
            const $button = $(buttonSelector);
            $button.prop('disabled', isLoading);
            $button.html(isLoading ?
                `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>${loadingLabel}` :
                normalLabel
            );
        }

        return {
            escapeHtml,
            getInitials,
            dataTableOptions,
            initTableTools,
            wirePasswordToggles,
            clearValidation,
            updateSelectCount,
            setButtonLoading,
            initTooltips
        };
    })();
</script>
