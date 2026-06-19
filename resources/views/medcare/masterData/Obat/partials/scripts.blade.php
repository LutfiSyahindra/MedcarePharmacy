<script>
    window.MasterObatUI = window.MasterObatUI || (function() {
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function numberValue(value) {
            const numeric = Number(String(value ?? 0).replace(/[^\d.-]/g, ''));
            return Number.isFinite(numeric) ? numeric : 0;
        }

        function formatNumber(value) {
            return numberValue(value).toLocaleString('id-ID');
        }

        function formatCurrency(value) {
            return `Rp ${numberValue(value).toLocaleString('id-ID')}`;
        }

        function getInitials(value) {
            const words = String(value || 'OB').trim().split(/\s+/).filter(Boolean);
            return words.slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase() || 'OB';
        }

        function codeBadge(value) {
            return `
                <span class="obat-code-badge">
                    <i class="mdi mdi-pound"></i>
                    ${escapeHtml(value || '-')}
                </span>
            `;
        }

        function tagBadge(value, icon = 'mdi-tag-outline') {
            return `
                <span class="obat-tag-badge" title="${escapeHtml(value || '-')}">
                    <i class="mdi ${icon}"></i>
                    ${escapeHtml(value || '-')}
                </span>
            `;
        }

        function identity(name, subtitle) {
            const safeName = escapeHtml(name || '-');
            const safeSubtitle = escapeHtml(subtitle || 'Master obat');

            return `
                <div class="obat-identity">
                    <span class="obat-avatar">${escapeHtml(getInitials(name))}</span>
                    <div>
                        <strong title="${safeName}">${safeName}</strong>
                        <small>${safeSubtitle}</small>
                    </div>
                </div>
            `;
        }

        function minimumBadge(value) {
            return `
                <span class="obat-stock-badge is-low">
                    <i class="mdi mdi-bell-ring-outline"></i>
                    ${formatNumber(value)}
                </span>
            `;
        }

        function moneyBadge(value) {
            return `
                <span class="obat-money-badge">
                    <i class="mdi mdi-cash"></i>
                    ${formatCurrency(value)}
                </span>
            `;
        }

        function typeBadge(value) {
            const text = value || '-';
            const isGeneric = String(text).toLowerCase() === 'generik';

            return `
                <span class="obat-type-badge ${isGeneric ? 'is-generic' : 'is-paten'}">
                    <i class="mdi ${isGeneric ? 'mdi-pill' : 'mdi-certificate-outline'}"></i>
                    ${escapeHtml(text)}
                </span>
            `;
        }

        function miniStack(primary, secondary) {
            return `
                <div class="obat-mini-stack">
                    <strong>${escapeHtml(primary || '-')}</strong>
                    <small>${escapeHtml(secondary || '-')}</small>
                </div>
            `;
        }

        function detailItem(label, value, icon = 'mdi-information-outline') {
            return `
                <div class="obat-detail-item">
                    <span><i class="mdi ${icon}"></i>${escapeHtml(label)}</span>
                    <strong>${escapeHtml(value || '-')}</strong>
                </div>
            `;
        }

        function detailPanel(row) {
            return `
                <div class="obat-detail-panel">
                    <section class="obat-detail-group">
                        <h6><i class="mdi mdi-shape-outline"></i>Klasifikasi</h6>
                        <div class="obat-detail-grid">
                            ${detailItem('Kategori Utama', row.category_id, 'mdi-folder-outline')}
                            ${detailItem('Kategori', row.main_category_id, 'mdi-folder-multiple-outline')}
                            ${detailItem('Sub Kategori', row.sub_kategori_id, 'mdi-source-branch')}
                            ${detailItem('Golongan', row.golongan_id, 'mdi-flask-outline')}
                            ${detailItem('Satuan', row.satuan_id, 'mdi-scale-balance')}
                            ${detailItem('Sediaan', row.sediaan_id, 'mdi-bottle-tonic-outline')}
                        </div>
                    </section>
                    <section class="obat-detail-group">
                        <h6><i class="mdi mdi-warehouse"></i>Operasional</h6>
                        <div class="obat-detail-grid">
                            ${detailItem('Pabrikan', row.pabrikan_id, 'mdi-factory')}
                            ${detailItem('Distributor', row.distributor_id, 'mdi-truck-delivery-outline')}
                            ${detailItem('Rak Penyimpanan', row.rak_id, 'mdi-archive-marker-outline')}
                            ${detailItem('Kemasan', row.kemasan, 'mdi-package-variant-closed')}
                            ${detailItem('Stok Minimum', formatNumber(row.stok_minimum), 'mdi-bell-ring-outline')}
                            ${detailItem('Harga Beli', formatCurrency(row.harga_beli), 'mdi-cash-minus')}
                        </div>
                    </section>
                    <section class="obat-detail-group is-wide">
                        <h6><i class="mdi mdi-clipboard-pulse-outline"></i>Informasi Klinis</h6>
                        <div class="obat-detail-grid">
                            ${detailItem('Komposisi', row.komposisi, 'mdi-test-tube')}
                            ${detailItem('Indikasi', row.indikasi, 'mdi-heart-pulse')}
                            ${detailItem('Dosis', row.dosis, 'mdi-clock-outline')}
                            ${detailItem('Jenis', row.jenis, 'mdi-certificate-outline')}
                            ${detailItem('No Batch', row.no_batch, 'mdi-identifier')}
                            ${detailItem('Kadaluarsa', row.tgl_kadaluarsa, 'mdi-calendar-alert-outline')}
                        </div>
                    </section>
                </div>
            `;
        }

        function dataTableOptions(options) {
            return $.extend(true, {
                processing: true,
                serverSide: true,
                responsive: false,
                autoWidth: false,
                scrollX: true,
                order: [],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                dom: '<"obat-table-meta"l>rt<"obat-table-footer"ip>',
                language: {
                    processing: '<span class="obat-loading"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Memuat data...</span>',
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

        function syncStickyNo(tableSelector) {
            const $table = $(tableSelector);
            const detailWidth = $table.find('th.obat-sticky-detail').outerWidth() || 62;
            const actionWidth = $table.find('th.obat-sticky-action').outerWidth() || 76;
            const styleId = 'masterObatStickyStyle';
            let styleTag = document.getElementById(styleId);

            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = styleId;
                document.head.appendChild(styleTag);
            }

            styleTag.textContent = `
                .dataTables_wrapper .dataTable th.obat-sticky-detail,
                .dataTables_wrapper .dataTable td.obat-sticky-detail {
                    left: 0;
                }
                .dataTables_wrapper .dataTable th.obat-sticky-action,
                .dataTables_wrapper .dataTable td.obat-sticky-action {
                    left: ${detailWidth}px;
                }
                .dataTables_wrapper .dataTable th.obat-sticky-no,
                .dataTables_wrapper .dataTable td.obat-sticky-no {
                    left: ${detailWidth + actionWidth}px;
                    box-shadow: 4px 0 16px rgba(15, 23, 42, .035);
                }
            `;
        }

        function initTableTools(config) {
            const table = config.table;
            const $table = $(config.tableSelector);
            const $search = $(config.searchSelector);
            const $refresh = $(config.refreshSelector || '.obat-refresh-table');
            let searchTimer = null;

            $search.off('input.obatTools').on('input.obatTools', function() {
                const value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    table.search(value).draw();
                }, 220);
            });

            $refresh.off('click.obatTools').on('click.obatTools', function() {
                const $button = $(this);
                $button.addClass('disabled');
                table.ajax.reload(function() {
                    $button.removeClass('disabled');
                }, false);
            });

            $table.find('tbody').off('click.obatTools').on('click.obatTools', 'tr', function(event) {
                if ($(this).hasClass('obat-detail-row')) {
                    return;
                }

                if ($(event.target).closest('button, a, input, label, .form-check').length) {
                    return;
                }

                $(this).toggleClass('is-selected');
                updateSelectedCount(config.tableSelector, config.selectedTarget);
            });

            table.on('draw', function() {
                updateTableStats(table, config);
                syncStickyNo(config.tableSelector);
                initTooltips();
            });

            updateTableStats(table, config);
            syncStickyNo(config.tableSelector);
            initTooltips();
        }

        function initTooltips() {
            if (!window.bootstrap || !bootstrap.Tooltip) return;

            document.querySelectorAll('.obat-page [title]').forEach(function(el) {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el);
                }
            });
        }

        function clearValidation(formSelector) {
            const $form = $(formSelector);
            $form.find('.invalid-feedback').text('');
            $form.find('.form-control, .form-select').removeClass('is-invalid');
            $form.find('.obat-field').removeClass('has-error');
        }

        function markFieldErrors(formSelector, errors) {
            const messages = [];
            clearValidation(formSelector);

            Object.keys(errors || {}).forEach(function(field) {
                const message = errors[field][0] || 'Field tidak valid';
                let $input = $(formSelector).find(`[name="${field}"]`);

                if (!$input.length) {
                    $input = $(formSelector).find(`[name="${field}[]"]`).first();
                }

                $input.addClass('is-invalid');
                $input.closest('.obat-field').addClass('has-error');
                $input.closest('.obat-field').find('.invalid-feedback').first().text(message);
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
                timer: icon === 'error' ? 4600 : 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }

        return {
            escapeHtml,
            formatNumber,
            formatCurrency,
            codeBadge,
            tagBadge,
            identity,
            minimumBadge,
            moneyBadge,
            typeBadge,
            miniStack,
            detailPanel,
            dataTableOptions,
            initTableTools,
            initTooltips,
            clearValidation,
            markFieldErrors,
            setButtonLoading,
            toast
        };
    })();
</script>
