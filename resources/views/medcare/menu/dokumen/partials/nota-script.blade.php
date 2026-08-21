<script>
    $(function() {
        const receiptEndpoint = @json(route("dokumen.nota.table"));
        const receiptIndexUrl = @json(route("dokumen.nota.index"));
        const receiptTypes = @json($receiptTypes);
        const typeIds = {
            penjualan_bebas: 'PenjualanBebas',
            penjualan_resep: 'PenjualanResep',
            penjualan_racikan: 'PenjualanRacikan',
            penjualan_kredit: 'PenjualanKredit',
            penjualan_instansi: 'PenjualanInstansi'
        };
        let activeType = '';
        let searchTimer = null;

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', maximumFractionDigits: 0
            }).format(Number(value) || 0);
        }

        function formatDateTime(value) {
            if (!value) return { date: '-', time: '-' };
            const [datePart, timePart = ''] = String(value).split(' ');
            const date = new Date(`${datePart}T${timePart || '00:00'}:00`);
            if (Number.isNaN(date.getTime())) return { date: value, time: '' };

            return {
                date: new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric'
                }).format(date),
                time: new Intl.DateTimeFormat('id-ID', {
                    hour: '2-digit', minute: '2-digit'
                }).format(date).replace('.', ':')
            };
        }

        function typeMeta(type) {
            return receiptTypes[type] || {
                label: type || '-', short: type || '-', icon: 'mdi-receipt-text-outline', tone: 'regular'
            };
        }

        function updateSummary(summary = {}) {
            const counts = summary.types || {};
            $('#receiptTotalCount, #receiptTabAll').text(formatNumber(summary.total));
            $('#receiptGrandTotal').text(formatCurrency(summary.grand_total));

            Object.keys(typeIds).forEach(type => {
                const count = formatNumber(counts[type] || 0);
                $(`#receiptMetric${typeIds[type]}, #receiptTab${typeIds[type]}`).text(count);
            });
        }

        function selectType(type) {
            activeType = type || '';
            $('.document-type-tabs [data-type]').each(function() {
                const selected = String($(this).data('type') || '') === activeType;
                $(this).toggleClass('is-active', selected).attr('aria-pressed', selected ? 'true' : 'false');
            });
            $('[data-receipt-type]').each(function() {
                const selected = String($(this).data('receipt-type') || '') === activeType;
                $(this).toggleClass('is-active', selected).attr('aria-pressed', selected ? 'true' : 'false');
            });
            updateActiveFilters();
        }

        function filterValues() {
            return {
                search: $('#receiptSearch').val().trim(),
                branch: $('#receiptBranchFilter').attr('type') === 'hidden' ? '' : ($('#receiptBranchFilter').val() || ''),
                start: $('#receiptDateStart').val() || '',
                end: $('#receiptDateEnd').val() || ''
            };
        }

        function updateActiveFilters() {
            const values = filterValues();
            const chips = [];
            if (activeType) chips.push(`Jenis: ${typeMeta(activeType).label}`);
            if (values.search) chips.push(`Pencarian: ${values.search}`);
            if (values.branch) chips.push(`Cabang: ${$('#receiptBranchFilter option:selected').text()}`);
            if (values.start) chips.push(`Mulai: ${values.start}`);
            if (values.end) chips.push(`Sampai: ${values.end}`);

            $('#receiptFilterChips').html(chips.map(label => `<span class="document-filter-chip">${escapeHtml(label)}</span>`).join(''));
            $('#receiptActiveFilters').toggleClass('d-none', chips.length === 0);

            const advancedCount = [values.branch, values.start, values.end].filter(Boolean).length;
            $('#receiptFilterCount').text(advancedCount).toggleClass('d-none', advancedCount === 0);
            $('#toggleReceiptFilters').toggleClass('is-active', advancedCount > 0);
        }

        function setAdvancedFilters(open) {
            const panel = document.getElementById('receiptAdvancedFilters');
            $('#toggleReceiptFilters').attr('aria-expanded', open ? 'true' : 'false');
            $(panel).toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
            if (open) panel.removeAttribute('inert'); else panel.setAttribute('inert', '');
        }

        function setTemplateLibrary(open) {
            const library = document.getElementById('receiptTemplateLibrary');
            const content = document.getElementById('receiptTemplateContent');
            library.classList.toggle('is-collapsed', !open);
            $('#toggleReceiptTemplates').attr('aria-expanded', open ? 'true' : 'false').find('span').text(open ? 'Tutup template' : 'Buka template');
            content.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (open) content.removeAttribute('inert'); else content.setAttribute('inert', '');
        }

        const table = $('#receiptTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            searching: false,
            pageLength: 10,
            order: [],
            dom: 'rt<"document-table-footer"ip>',
            ajax: {
                url: receiptEndpoint,
                data: function(request) {
                    request.nota_search = $('#receiptSearch').val().trim();
                    request.type = activeType;
                    request.branch_id = $('#receiptBranchFilter').val() || '';
                    request.date_start = $('#receiptDateStart').val() || '';
                    request.date_end = $('#receiptDateEnd').val() || '';
                },
                dataSrc: function(response) {
                    updateSummary(response.summary || {});
                    $('#receiptResultCount').text(formatNumber(response.recordsFiltered));
                    $('#receiptArchiveAlert').addClass('d-none').empty();
                    return response.data || [];
                },
                error: function(xhr) {
                    $('#receiptTable_processing').hide();
                    const message = xhr.responseJSON?.message || 'Arsip nota gagal dimuat.';
                    $('#receiptArchiveAlert').removeClass('d-none').html(`<i class="mdi mdi-alert-circle-outline"></i><span>${escapeHtml(message)}</span>`);
                }
            },
            columns: [
                {
                    data: 'nomor_transaksi',
                    name: 'nomor_transaksi',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        const meta = typeMeta(row.jenis_transaksi);
                        return `<div class="document-cell">
                            <span class="document-cell-icon is-${meta.tone}"><i class="mdi ${meta.icon}"></i></span>
                            <span class="document-cell-copy">
                                <strong class="document-number-link">${escapeHtml(value)}</strong>
                                <span class="document-cell-meta"><span class="receipt-payment-badge ${row.payment_status === 'credit' ? 'is-credit' : ''}">${escapeHtml(row.payment_label)}</span></span>
                            </span>
                        </div>`;
                    }
                },
                {
                    data: 'type_label',
                    name: 'jenis_transaksi',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<span class="document-type-badge is-${typeMeta(row.jenis_transaksi).tone}">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: 'tanggal_transaksi',
                    name: 'tanggal_transaksi',
                    render: function(value, type) {
                        if (type !== 'display') return value;
                        const formatted = formatDateTime(value);
                        return `<div class="document-date-cell"><strong>${escapeHtml(formatted.date)}</strong><small>${escapeHtml(formatted.time)} WIB</small></div>`;
                    }
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        let reference = row.nomor_resep ? `Resep ${row.nomor_resep}` : (row.instansi_name || row.customer_phone || 'Pelanggan umum');
                        return `<div class="receipt-customer-cell"><strong>${escapeHtml(value || 'Umum')}</strong><small>${escapeHtml(reference)}</small></div>`;
                    }
                },
                {
                    data: 'item_count',
                    name: 'item_count',
                    orderable: false,
                    searchable: false,
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="receipt-content-cell"><strong>${formatNumber(value)} item</strong><small title="${escapeHtml(row.medicine_names || '-')}">${escapeHtml(row.medicine_names || '-')}</small></div>`;
                    }
                },
                {
                    data: 'grand_total',
                    name: 'grand_total',
                    render: function(value, type, row) {
                        if (type !== 'display') return Number(value) || 0;
                        return `<div class="receipt-total-cell"><strong>${formatCurrency(value)}</strong><small>${escapeHtml(row.payment_label)}</small></div>`;
                    }
                },
                {
                    data: 'branch_name',
                    name: 'branch.name',
                    orderable: false,
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="document-branch-cell"><i class="mdi mdi-storefront-outline"></i><span>${escapeHtml(value)}<br><small>${escapeHtml(row.cashier_name)}</small></span></div>`;
                    }
                },
                {
                    data: null,
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(value, type, row) {
                        return `<div class="document-action-group">
                            <a class="document-action-button" href="${escapeHtml(row.preview_url)}" target="_blank" rel="noopener" title="Pratinjau nota" aria-label="Pratinjau nota ${escapeHtml(row.nomor_transaksi)}"><i class="mdi mdi-file-eye-outline"></i></a>
                            <a class="document-action-button is-print" href="${escapeHtml(row.print_url)}" target="_blank" rel="noopener" title="Cetak nota" aria-label="Cetak nota ${escapeHtml(row.nomor_transaksi)}"><i class="mdi mdi-printer-outline"></i></a>
                        </div>`;
                    }
                }
            ],
            drawCallback: function() {
                const now = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(new Date()).replace('.', ':');
                $('#receiptLastSync').html(`<i class="mdi mdi-cloud-check-outline"></i> Diperbarui ${escapeHtml(now)} WIB`);
            },
            language: {
                processing: '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan arsip...',
                emptyTable: 'Belum ada transaksi selesai pada cabang yang dapat Anda akses.',
                zeroRecords: 'Nota yang sesuai filter tidak ditemukan.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ nota',
                infoEmpty: 'Belum ada nota',
                paginate: { previous: '‹', next: '›' }
            }
        });

        function reloadTable(resetPage = true) {
            updateActiveFilters();
            table.ajax.reload(null, resetPage);
        }

        $('#receiptSearch').on('input', function() {
            $(this).closest('.document-search').toggleClass('has-value', this.value.length > 0);
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => reloadTable(), 300);
        });

        $('#clearReceiptSearch').on('click', function() {
            $('#receiptSearch').val('').trigger('input').focus();
        });

        $('.document-type-tabs [data-type]').on('click', function() {
            selectType(String($(this).data('type') || ''));
            reloadTable();
        });

        $('[data-receipt-type]').on('click', function() {
            selectType(String($(this).data('receipt-type') || ''));
            reloadTable();
            document.getElementById('receiptArchiveSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        $('#receiptBranchFilter, #receiptDateStart, #receiptDateEnd').on('change', function() {
            const start = $('#receiptDateStart').val();
            const end = $('#receiptDateEnd').val();
            if (start && end && end < start) $('#receiptDateEnd').val(start);
            reloadTable();
        });

        $('#receiptPageLength').on('change', function() {
            table.page.len(Number(this.value) || 10).draw();
        });

        $('#toggleReceiptFilters').on('click', function() {
            setAdvancedFilters($(this).attr('aria-expanded') !== 'true');
        });

        $('#resetReceiptAdvancedFilters').on('click', function() {
            $('#receiptDateStart, #receiptDateEnd').val('');
            if ($('#receiptBranchFilter').attr('type') !== 'hidden') $('#receiptBranchFilter').val('');
            reloadTable();
        });

        $('#resetReceiptFilters').on('click', function() {
            selectType('');
            $('#receiptDateStart, #receiptDateEnd').val('');
            if ($('#receiptBranchFilter').attr('type') !== 'hidden') $('#receiptBranchFilter').val('');
            $('#receiptSearch').val('').closest('.document-search').removeClass('has-value');
            reloadTable();
        });

        $('#refreshReceiptTable, #refreshReceiptArchive').on('click', function() {
            const icon = $(this).find('.mdi-refresh').addClass('mdi-spin');
            table.ajax.reload(function() { icon.removeClass('mdi-spin'); }, false);
        });

        $('#toggleReceiptTemplates').on('click', function() {
            setTemplateLibrary($(this).attr('aria-expanded') !== 'true');
        });

        $('#openReceiptTemplates').on('click', function() {
            setTemplateLibrary(true);
            document.getElementById('receiptTemplateLibrary')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        $('#receiptTemplateBranch').on('change', function() {
            const branchId = Number(this.value) || 0;
            if (branchId) window.location.href = `${receiptIndexUrl}?branch_id=${branchId}#receiptTemplateLibrary`;
        });

        $(document).on('keydown', function(event) {
            if (event.key === '/' && !$(event.target).is('input, select, textarea')) {
                event.preventDefault();
                $('#receiptSearch').trigger('focus');
            }
        });

        if (window.location.hash === '#receiptTemplateLibrary') {
            setTemplateLibrary(true);
            window.requestAnimationFrame(() => {
                document.getElementById('receiptTemplateLibrary')?.scrollIntoView({ block: 'start' });
            });
        }
    });
</script>
