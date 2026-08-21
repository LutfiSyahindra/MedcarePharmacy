<script>
    $(function() {
        const documentEndpoint = @json(route("dokumen.table"));
        const documentDetailBase = @json(url("/medcare/menu/dokumen"));
        const documentIndexUrl = @json(route("dokumen.index"));
        const detailModalElement = document.getElementById('documentDetailModal');
        const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalElement);
        const typeLabels = {
            reguler: 'Reguler',
            narkotika: 'Narkotika',
            psikotropika: 'Psikotropika',
            prekursor: 'Prekursor',
            oot: 'OOT'
        };
        let activeType = '';
        let searchTimer = null;
        let refreshTimer = null;

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
        }

        function dateObject(value) {
            if (!value) return null;
            const date = new Date(`${value}T00:00:00`);
            return Number.isNaN(date.getTime()) ? null : date;
        }

        function formatDate(value) {
            const date = dateObject(value);
            return date ? new Intl.DateTimeFormat('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric'
            }).format(date) : escapeHtml(value || '-');
        }

        function formatDateCell(value) {
            const date = dateObject(value);
            if (!date) return `<div class="document-date-cell"><strong>${escapeHtml(value || '-')}</strong></div>`;

            return `<div class="document-date-cell">
                <strong>${new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short' }).format(date)}</strong>
                <small>${date.getFullYear()}</small>
            </div>`;
        }

        function statusClass(status, isReady) {
            if (isReady) return 'is-ready';
            if (status === 'waiting_approval' || status === 'draft') return 'is-waiting';
            if (status === 'rejected') return 'is-rejected';
            return '';
        }

        function typeClass(type) {
            return {
                reguler: 'regular',
                narkotika: 'narcotic',
                psikotropika: 'psychotropic',
                prekursor: 'precursor',
                oot: 'oot'
            }[type] || '';
        }

        function typeIcon(type) {
            return {
                reguler: 'mdi-file-document-edit-outline',
                narkotika: 'mdi-alert-octagon-outline',
                psikotropika: 'mdi-brain',
                prekursor: 'mdi-flask-outline',
                oot: 'mdi-account-check-outline'
            }[type] || 'mdi-file-document-outline';
        }

        function updateSummary(summary = {}) {
            const values = {
                documents: summary.documents,
                sets: summary.sets,
                purchaseOrders: summary.purchase_orders,
                reguler: summary.reguler,
                narkotika: summary.narkotika,
                psikotropika: summary.psikotropika,
                prekursor: summary.prekursor,
                oot: summary.oot
            };

            $('#documentTotalCount, #documentAllTabCount').text(formatNumber(values.documents));
            $('#documentSetCount').text(formatNumber(values.sets));
            $('#documentPoCount').text(formatNumber(values.purchaseOrders));
            $('#documentRegularCount, #documentRegularTabCount').text(formatNumber(values.reguler));
            $('#documentNarcoticCount, #documentNarcoticTabCount').text(formatNumber(values.narkotika));
            $('#documentPsychotropicCount, #documentPsychotropicTabCount').text(formatNumber(values.psikotropika));
            $('#documentPrecursorCount, #documentPrecursorTabCount').text(formatNumber(values.prekursor));
            $('#documentOotCount, #documentOotTabCount').text(formatNumber(values.oot));
        }

        function updateLastSync() {
            const time = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(new Date());
            $('#documentLastSync').html(`<i class="mdi mdi-cloud-check-outline"></i>Diperbarui ${escapeHtml(time)}`);
        }

        function selectType(type) {
            activeType = type || '';

            $('.document-type-tabs [data-type]').each(function() {
                const active = String($(this).data('type') || '') === activeType;
                $(this).toggleClass('is-active', active).attr('aria-pressed', active ? 'true' : 'false');
            });

            $('[data-document-type]').each(function() {
                const active = String($(this).data('document-type') || '') === activeType;
                $(this).toggleClass('is-active', active).attr('aria-pressed', active ? 'true' : 'false');
            });

            updateFilterState();
        }

        function activeFilters() {
            const filters = [];
            const search = $('#documentSearch').val().trim();
            const status = $('#documentStatusFilter').val();
            const start = $('#documentDateStart').val();
            const end = $('#documentDateEnd').val();

            if (activeType) filters.push({ key: 'type', label: `Jenis: ${typeLabels[activeType] || activeType}` });
            if (search) filters.push({ key: 'search', label: `Pencarian: “${search}”` });
            if (status) filters.push({ key: 'status', label: `Status: ${$('#documentStatusFilter option:selected').text()}` });
            if (start) filters.push({ key: 'date_start', label: `Mulai: ${formatDate(start)}` });
            if (end) filters.push({ key: 'date_end', label: `Sampai: ${formatDate(end)}` });

            return filters;
        }

        function updateFilterState() {
            const filters = activeFilters();
            const advancedCount = filters.filter(filter => ['status', 'date_start', 'date_end'].includes(filter.key)).length;
            const filterCount = $('#documentFilterCount');

            filterCount.text(advancedCount).toggleClass('d-none', advancedCount === 0);
            $('#toggleAdvancedFilters').toggleClass('is-active', advancedCount > 0);
            $('#documentActiveFilters').toggleClass('d-none', filters.length === 0);
            $('#documentFilterChips').html(filters.map(filter => `
                <button type="button" class="document-filter-chip" data-filter-key="${filter.key}">
                    ${escapeHtml(filter.label)} <i class="mdi mdi-close"></i>
                </button>
            `).join(''));
        }

        function setAdvancedFiltersOpen(open) {
            const panel = document.getElementById('documentAdvancedFilters');
            panel.classList.toggle('is-open', open);
            panel.toggleAttribute('inert', !open);
            panel.setAttribute('aria-hidden', open ? 'false' : 'true');
            $('#toggleAdvancedFilters').attr('aria-expanded', open ? 'true' : 'false');
        }

        function showArchiveAlert(message) {
            $('#documentArchiveAlert')
                .removeClass('d-none')
                .html(`<i class="mdi mdi-alert-circle-outline"></i><span>${escapeHtml(message)}</span>`);
        }

        function hideArchiveAlert() {
            $('#documentArchiveAlert').addClass('d-none').empty();
        }

        const table = $('#documentTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            searchDelay: 350,
            pageLength: 10,
            order: [[1, 'desc']],
            dom: 'rt<"document-table-footer"ip>',
            ajax: {
                url: documentEndpoint,
                data: function(request) {
                    request.type = activeType;
                    request.status = $('#documentStatusFilter').val() || '';
                    request.date_start = $('#documentDateStart').val() || '';
                    request.date_end = $('#documentDateEnd').val() || '';
                },
                dataSrc: function(response) {
                    hideArchiveAlert();
                    updateSummary(response.summary || {});
                    updateLastSync();
                    return response.data || [];
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Arsip dokumen gagal dimuat. Silakan coba kembali.';
                    $('#documentTable_processing').hide();
                    $('#documentLastSync').html('<i class="mdi mdi-cloud-alert-outline"></i>Sinkronisasi gagal');
                    showArchiveAlert(message);
                }
            },
            columns: [
                {
                    data: 'document_number',
                    name: 'document_number',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        const extra = Number(row.document_count) > 1
                            ? `<span class="document-extra-count">+${formatNumber(Number(row.document_count) - 1)} nomor</span>` : '';

                        return `<div class="document-cell">
                            <span class="document-cell-icon is-${typeClass(row.type)}"><i class="mdi ${typeIcon(row.type)}"></i></span>
                            <div class="document-cell-copy">
                                <button type="button" class="document-number-link js-document-detail"
                                    data-type="${escapeHtml(row.type)}" data-purchase-order="${Number(row.purchase_order_id)}"
                                    title="Buka detail ${escapeHtml(value)}">${escapeHtml(value)}</button>
                                <div class="document-cell-meta">
                                    <span class="document-type-badge is-${typeClass(row.type)}">${escapeHtml(row.type_short_label)}</span>
                                    ${extra}
                                </div>
                            </div>
                        </div>`;
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                    render: function(value, type) { return type === 'display' ? formatDateCell(value) : value; }
                },
                {
                    data: 'purchase_order_number',
                    name: 'purchase_order_number',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="document-reference">
                            <strong>${escapeHtml(value)}</strong>
                            <small title="${escapeHtml(row.distributor)}">${escapeHtml(row.distributor)}</small>
                        </div>`;
                    }
                },
                {
                    data: 'branch',
                    name: 'branch',
                    render: function(value, type) {
                        return type === 'display'
                            ? `<span class="document-branch-cell"><i class="mdi mdi-storefront-outline"></i>${escapeHtml(value)}</span>`
                            : value;
                    }
                },
                {
                    data: 'item_count',
                    name: 'item_count',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="document-content-count">
                            <strong>${formatNumber(value)} item obat</strong>
                            <small>${formatNumber(row.document_count)} surat · ${formatNumber(row.page_count)} halaman</small>
                        </div>`;
                    }
                },
                {
                    data: 'status_label',
                    name: 'status',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<span class="document-status-badge ${statusClass(row.status, row.is_ready)}">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: null,
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(value, type, row) {
                        if (type !== 'display') return '';
                        return `<div class="document-action-group">
                            <button type="button" class="document-action-primary js-document-detail"
                                data-type="${escapeHtml(row.type)}" data-purchase-order="${Number(row.purchase_order_id)}"
                                title="Lihat detail"><i class="mdi mdi-eye-outline"></i>Detail</button>
                            <a class="document-action-button" href="${escapeHtml(row.preview_url)}" target="_blank" rel="noopener"
                                title="Pratinjau dokumen" aria-label="Pratinjau dokumen"><i class="mdi mdi-file-eye-outline"></i></a>
                            <a class="document-action-button is-print" href="${escapeHtml(row.print_url)}" target="_blank" rel="noopener"
                                title="Cetak atau simpan PDF" aria-label="Cetak atau simpan PDF"><i class="mdi mdi-printer-outline"></i></a>
                        </div>`;
                    }
                },
                { data: 'search_text', name: 'search_text', visible: false }
            ],
            drawCallback: function() {
                const info = this.api().page.info();
                $('#documentResultCount').text(formatNumber(info.recordsDisplay));
                updateFilterState();
            },
            language: {
                processing: '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan arsip...',
                emptyTable: '<div class="document-empty-state"><i class="mdi mdi-archive-outline"></i><strong>Arsip masih kosong</strong><span>Surat pesanan akan muncul dari purchase order yang memiliki item obat.</span></div>',
                zeroRecords: '<div class="document-empty-state"><i class="mdi mdi-file-search-outline"></i><strong>Dokumen tidak ditemukan</strong><span>Coba ubah kata kunci atau filter yang sedang aktif.</span></div>',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ kelompok dokumen',
                infoEmpty: 'Belum ada dokumen',
                paginate: { previous: '‹', next: '›' }
            }
        });

        $('#documentSearch').on('input', function() {
            const value = this.value;
            $(this).closest('.document-search').toggleClass('has-value', value.length > 0);
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                table.search(value).draw();
                updateFilterState();
            }, 300);
        });

        $('#clearDocumentSearch').on('click', function() {
            $('#documentSearch').val('').trigger('input').trigger('focus');
        });

        $('.document-type-tabs [data-type]').on('click', function() {
            selectType(String($(this).data('type') || ''));
            table.ajax.reload();
        });

        $('[data-document-type]').on('click', function() {
            selectType(String($(this).data('document-type') || ''));
            table.ajax.reload();
            document.getElementById('documentArchiveSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        $('#toggleAdvancedFilters').on('click', function() {
            setAdvancedFiltersOpen($(this).attr('aria-expanded') !== 'true');
        });

        $('#documentStatusFilter, #documentDateStart, #documentDateEnd').on('change', function() {
            const start = $('#documentDateStart').val();
            const end = $('#documentDateEnd').val();

            $('#documentDateEnd').attr('min', start || null);
            if (start && end && end < start) $('#documentDateEnd').val(start);
            updateFilterState();
            table.ajax.reload();
        });

        $('#documentPageLength').on('change', function() {
            table.page.len(Number(this.value) || 10).draw();
        });

        function resetAdvancedFilters() {
            $('#documentStatusFilter, #documentDateStart, #documentDateEnd').val('');
            $('#documentDateEnd').removeAttr('min');
            updateFilterState();
        }

        $('#resetAdvancedFilters').on('click', function() {
            resetAdvancedFilters();
            table.ajax.reload();
        });

        $('#resetDocumentFilters').on('click', function() {
            window.clearTimeout(searchTimer);
            selectType('');
            resetAdvancedFilters();
            $('#documentSearch').val('').closest('.document-search').removeClass('has-value');
            table.search('').ajax.reload();
        });

        $('#documentFilterChips').on('click', '[data-filter-key]', function() {
            const key = String($(this).data('filter-key'));

            if (key === 'type') selectType('');
            if (key === 'search') {
                window.clearTimeout(searchTimer);
                $('#documentSearch').val('').closest('.document-search').removeClass('has-value');
                table.search('');
            }
            if (key === 'status') $('#documentStatusFilter').val('');
            if (key === 'date_start') {
                $('#documentDateStart').val('');
                $('#documentDateEnd').removeAttr('min');
            }
            if (key === 'date_end') $('#documentDateEnd').val('');

            updateFilterState();
            table.ajax.reload();
        });

        function refreshArchive(button) {
            const icon = $(button).find('.mdi-refresh').addClass('mdi-spin');
            $(button).attr('aria-busy', 'true');
            window.clearTimeout(refreshTimer);
            table.ajax.reload(function() {
                icon.removeClass('mdi-spin');
                $(button).removeAttr('aria-busy');
            }, false);
            refreshTimer = window.setTimeout(function() {
                icon.removeClass('mdi-spin');
                $(button).removeAttr('aria-busy');
            }, 10000);
        }

        $('#refreshDocumentTable, #refreshDocumentArchive').on('click', function() {
            refreshArchive(this);
        });

        function setTemplateExpanded(expanded) {
            const library = $('#documentTemplateLibrary');
            const content = document.getElementById('documentTemplateContent');
            library.toggleClass('is-collapsed', !expanded);
            content.toggleAttribute('inert', !expanded);
            content.setAttribute('aria-hidden', expanded ? 'false' : 'true');
            $('#toggleDocumentTemplates')
                .attr('aria-expanded', expanded ? 'true' : 'false')
                .find('span').text(expanded ? 'Tutup template' : 'Buka template');
        }

        $('#toggleDocumentTemplates').on('click', function() {
            setTemplateExpanded($(this).attr('aria-expanded') !== 'true');
        });

        $('#openTemplateLibrary').on('click', function() {
            setTemplateExpanded(true);
            document.getElementById('documentTemplateLibrary')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        if (window.location.hash === '#documentTemplateLibrary') {
            setTemplateExpanded(true);
            window.setTimeout(function() {
                document.getElementById('documentTemplateLibrary')?.scrollIntoView({ behavior: 'auto', block: 'start' });
            }, 350);
        }

        $('#documentTemplateBranch').on('change', function() {
            const branchId = Number(this.value) || 0;
            if (!branchId) return;

            const target = new URL(documentIndexUrl, window.location.origin);
            target.searchParams.set('branch_id', branchId);
            target.hash = 'documentTemplateLibrary';
            window.location.assign(target.toString());
        });

        function openDocumentDetail(type, purchaseOrder) {
            $('#documentDetailLoading')
                .removeClass('d-none')
                .html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Menyiapkan detail dokumen...');
            $('#documentDetailContent').addClass('d-none');
            detailModal.show();

            $.get(`${documentDetailBase}/${encodeURIComponent(type)}/${purchaseOrder}`)
                .done(function(response) { renderDocumentDetail(response.data || {}); })
                .fail(function(xhr) {
                    $('#documentDetailLoading').html(`
                        <i class="mdi mdi-alert-circle-outline text-danger"></i>
                        ${escapeHtml(xhr.responseJSON?.message || 'Detail dokumen tidak dapat dimuat.')}
                    `);
                });
        }

        $('#documentTable').on('click', '.js-document-detail', function() {
            openDocumentDetail(String($(this).data('type')), Number($(this).data('purchase-order')));
        });

        function renderDocumentDetail(documentData) {
            const numbers = Array.isArray(documentData.document_numbers) ? documentData.document_numbers : [];
            const items = Array.isArray(documentData.items) ? documentData.items : [];

            $('#documentDetailIcon')
                .attr('class', `is-${typeClass(documentData.type)}`)
                .html(`<i class="mdi ${typeIcon(documentData.type)}"></i>`);
            $('#documentDetailTitle').text(documentData.type_label || 'Surat Pesanan');
            $('#documentDetailNumber').text(documentData.document_number || '-');
            $('#copyDocumentNumber').removeClass('is-copied').attr('title', 'Salin nomor dokumen').html('<i class="mdi mdi-content-copy"></i>');
            $('#documentDetailSummary').html(`
                <div><span>Tanggal</span><strong>${formatDate(documentData.date)}</strong></div>
                <div><span>Purchase Order</span><strong title="${escapeHtml(documentData.purchase_order_number)}">${escapeHtml(documentData.purchase_order_number || '-')}</strong></div>
                <div><span>Distributor</span><strong title="${escapeHtml(documentData.distributor)}">${escapeHtml(documentData.distributor || '-')}</strong></div>
                <div><span>Cabang</span><strong title="${escapeHtml(documentData.branch)}">${escapeHtml(documentData.branch || '-')}</strong></div>
                <div><span>Status PO</span><strong>${escapeHtml(documentData.status_label || '-')}</strong></div>
            `);
            $('#documentNumberList').html(numbers.map(number => `
                <span><i class="mdi mdi-file-document-check-outline"></i>${escapeHtml(number)}</span>
            `).join('') || '<span>-</span>');
            $('#documentItemTotal').text(`${formatNumber(items.length)} item`);
            $('#documentItemList').html(items.map((item, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${escapeHtml(item.name)}</strong></td>
                    <td>${escapeHtml(item.classification)}</td>
                    <td>${formatNumber(item.quantity)} ${escapeHtml(item.unit)}</td>
                    <td>${escapeHtml(item.document_number)}</td>
                </tr>
            `).join('') || '<tr><td colspan="5" class="text-center text-muted">Tidak ada item.</td></tr>');

            const note = documentData.notes
                ? `<i class="mdi mdi-note-text-outline"></i><div><strong>Catatan PO:</strong> ${escapeHtml(documentData.notes)}</div>`
                : `<i class="mdi mdi-information-outline"></i><div>${escapeHtml(documentData.type_description || '')} Dokumen ini dibuat ${formatNumber(documentData.copy_count)} rangkap.</div>`;

            $('#documentDetailNote').html(note);
            $('#documentPreviewButton').attr('href', documentData.preview_url || '#');
            $('#documentPrintButton').attr('href', documentData.print_url || '#');
            $('#documentDetailLoading').addClass('d-none');
            $('#documentDetailContent').removeClass('d-none');
        }

        $('#copyDocumentNumber').on('click', async function() {
            const value = $('#documentDetailNumber').text().trim();
            if (!value || value === '-') return;

            try {
                await navigator.clipboard.writeText(value);
            } catch (error) {
                const input = $('<textarea>').val(value).css({ position: 'fixed', opacity: 0 }).appendTo('body');
                input[0].select();
                document.execCommand('copy');
                input.remove();
            }

            $(this).addClass('is-copied').attr('title', 'Nomor tersalin').html('<i class="mdi mdi-check"></i>');
        });

        $(document).on('keydown', function(event) {
            const target = event.target;
            const isTyping = target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement;

            if (event.key === '/' && !isTyping && !detailModalElement.classList.contains('show')) {
                event.preventDefault();
                $('#documentSearch').trigger('focus');
            }

            if (event.key === 'Escape' && document.activeElement === $('#documentSearch')[0] && $('#documentSearch').val()) {
                $('#clearDocumentSearch').trigger('click');
            }
        });
    });
</script>
