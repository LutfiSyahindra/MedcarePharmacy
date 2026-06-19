<script>
    window.CategoryUI = window.CategoryUI || (function() {
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getInitials(value) {
            const words = String(value || 'KT').trim().split(/\s+/).filter(Boolean);
            return words.slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase() || 'KT';
        }

        function codeBadge(value) {
            return `
                <span class="category-code-badge">
                    <i class="mdi mdi-pound"></i>
                    ${escapeHtml(value || '-')}
                </span>
            `;
        }

        function parentBadge(value, icon) {
            return `
                <span class="category-parent-badge">
                    <i class="mdi ${icon || 'mdi-link-variant'}"></i>
                    ${escapeHtml(value || '-')}
                </span>
            `;
        }

        function identity(name, subtitle) {
            const safeName = escapeHtml(name || '-');
            const safeSubtitle = escapeHtml(subtitle || 'Data kategori');

            return `
                <div class="category-identity">
                    <span class="category-avatar">${escapeHtml(getInitials(name))}</span>
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
                dom: '<"category-table-meta"l>rt<"category-table-footer"ip>',
                language: {
                    processing: '<span class="category-loading"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Memuat data...</span>',
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
            const $refresh = $(config.refreshSelector || '.category-refresh-table');
            let searchTimer = null;

            $search.off('input.categoryTools').on('input.categoryTools', function() {
                const value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    table.search(value).draw();
                }, 220);
            });

            $refresh.off('click.categoryTools').on('click.categoryTools', function() {
                const $button = $(this);
                $button.addClass('disabled');
                table.ajax.reload(function() {
                    $button.removeClass('disabled');
                }, false);
            });

            $table.find('tbody').off('click.categoryTools').on('click.categoryTools', 'tr', function(event) {
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

            document.querySelectorAll('.category-page [title]').forEach(function(el) {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el);
                }
            });
        }

        function clearValidation(formSelector) {
            const $form = $(formSelector);
            $form.find('.invalid-feedback').text('');
            $form.find('.form-control, .form-select').removeClass('is-invalid');
            $form.find('.category-field, .category-batch-row').removeClass('has-error');
        }

        function markBatchErrors(formSelector, wrapperSelector, errors) {
            const messages = [];
            clearValidation(formSelector);

            Object.keys(errors || {}).forEach(function(key) {
                const message = errors[key][0] || 'Field tidak valid';
                const parts = key.split('.');
                const field = parts[0];
                const index = parts.length > 1 ? Number(parts[1]) : null;
                const $row = index === null ? $(wrapperSelector).find('.category-batch-row').first() : $(wrapperSelector).find('.category-batch-row').eq(index);
                const inputName = index === null ? field : `${field}[]`;
                let $input = $row.find(`[name="${inputName}"]`);

                if (!$input.length) {
                    $input = $(formSelector).find(`[name="${field}"]`);
                }

                $input.addClass('is-invalid');
                $input.closest('.category-field').addClass('has-error');
                $row.addClass('has-error');
                $input.closest('.category-field').find('.invalid-feedback').first().text(message);
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
            const count = $(wrapperSelector).find('.category-batch-row').length;
            $(targetSelector).text(Number(count).toLocaleString('id-ID'));
        }

        return {
            escapeHtml,
            getInitials,
            codeBadge,
            parentBadge,
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
