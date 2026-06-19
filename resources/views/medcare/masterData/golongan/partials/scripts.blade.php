<script>
    window.GolonganUI = window.GolonganUI || (function() {
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getInitials(value) {
            const words = String(value || 'GO').trim().split(/\s+/).filter(Boolean);
            return words.slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase() || 'GO';
        }

        function codeBadge(value) {
            return `
                <span class="golongan-code-badge">
                    <i class="mdi mdi-pound"></i>
                    ${escapeHtml(value || '-')}
                </span>
            `;
        }

        function descriptionBadge(value) {
            const text = value || 'Belum ada keterangan';
            return `
                <span class="golongan-description-badge" title="${escapeHtml(text)}">
                    <i class="mdi mdi-text-box-outline"></i>
                    ${escapeHtml(text)}
                </span>
            `;
        }

        function identity(name, subtitle) {
            const safeName = escapeHtml(name || '-');
            const safeSubtitle = escapeHtml(subtitle || 'Golongan obat');

            return `
                <div class="golongan-identity">
                    <span class="golongan-avatar">${escapeHtml(getInitials(name))}</span>
                    <div>
                        <strong title="${safeName}">${safeName}</strong>
                        <small>${safeSubtitle}</small>
                    </div>
                </div>
            `;
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
                dom: '<"golongan-table-meta"l>rt<"golongan-table-footer"ip>',
                language: {
                    processing: '<span class="golongan-loading"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Memuat data...</span>',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    zeroRecords: 'Data belum ditemukan',
                    emptyTable: 'Belum ada data untuk ditampilkan',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Belum ada data',
                    infoFiltered: '(difilter dari _MAX_ total)',
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
            $(config.totalTarget).text(Number(info.recordsTotal || 0).toLocaleString('id-ID'));
            $(config.filteredTarget).text(Number(info.recordsDisplay || 0).toLocaleString('id-ID'));
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
            const $refresh = $(config.refreshSelector || '.golongan-refresh-table');
            let searchTimer = null;

            $search.off('input.golonganTools').on('input.golonganTools', function() {
                const value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    table.search(value).draw();
                }, 220);
            });

            $refresh.off('click.golonganTools').on('click.golonganTools', function() {
                const $button = $(this);
                $button.addClass('disabled');
                table.ajax.reload(function() {
                    $button.removeClass('disabled');
                }, false);
            });

            $table.find('tbody').off('click.golonganTools').on('click.golonganTools', 'tr', function(event) {
                if ($(event.target).closest('button, a, input, label, .form-check').length) {
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

            document.querySelectorAll('.golongan-page [title]').forEach(function(el) {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el);
                }
            });
        }

        function clearValidation(formSelector) {
            const $form = $(formSelector);
            $form.find('.invalid-feedback').text('');
            $form.find('.form-control').removeClass('is-invalid');
            $form.find('.golongan-field, .golongan-batch-row').removeClass('has-error');
        }

        function markBatchErrors(formSelector, wrapperSelector, errors) {
            const messages = [];
            clearValidation(formSelector);

            Object.keys(errors || {}).forEach(function(key) {
                const message = errors[key][0] || 'Field tidak valid';
                const parts = key.split('.');
                const field = parts[0];
                const index = parts.length > 1 ? Number(parts[1]) : null;
                const $row = index === null ? $(wrapperSelector).find('.golongan-batch-row').first() : $(wrapperSelector).find('.golongan-batch-row').eq(index);
                const inputName = index === null ? field : `${field}[]`;
                let $input = $row.find(`[name="${inputName}"]`);

                if (!$input.length) {
                    $input = $(formSelector).find(`[name="${field}"]`);
                }

                $input.addClass('is-invalid');
                $input.closest('.golongan-field').addClass('has-error');
                $row.addClass('has-error');
                $input.closest('.golongan-field').find('.invalid-feedback').first().text(message);
                messages.push(message);
            });

            return messages;
        }

        function setButtonLoading(button, isLoading, loadingLabel, normalHtml) {
            const $button = $(button);
            const original = normalHtml || $button.data('normal-html') || $button.html();

            if (!$button.data('normal-html')) {
                $button.data('normal-html', original);
            }

            $button.prop('disabled', isLoading);
            $button.html(isLoading ?
                `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>${loadingLabel}` :
                original
            );
        }

        function toast(icon, title, html) {
            Swal.fire({
                icon: icon,
                title: title,
                html: html,
                toast: true,
                position: 'top-end',
                timer: icon === 'error' ? 4200 : 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }

        function updateBatchCount(wrapperSelector, targetSelector) {
            const count = $(wrapperSelector).find('.golongan-batch-row').length;
            $(targetSelector).text(Number(count).toLocaleString('id-ID'));
        }

        return {
            escapeHtml,
            getInitials,
            codeBadge,
            descriptionBadge,
            identity,
            dataTableOptions,
            initTableTools,
            initTooltips,
            clearValidation,
            markBatchErrors,
            setButtonLoading,
            toast,
            updateBatchCount
        };
    })();
</script>
