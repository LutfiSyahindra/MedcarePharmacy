<script>
    $(function() {
        const documentEndpoint = @json(route("dokumen.table"));
        const documentDetailBase = @json(url("/medcare/menu/dokumen"));
        const documentIndexUrl = @json(route("dokumen.index"));
        const detailModalElement = document.getElementById('documentDetailModal');
        const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalElement);
        let activeType = '';
        let searchTimer = null;

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
        }

        function formatDate(value) {
            if (!value) return '-';
            const date = new Date(`${value}T00:00:00`);
            return Number.isNaN(date.getTime()) ? escapeHtml(value) : new Intl.DateTimeFormat('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric'
            }).format(date);
        }

        function statusClass(status, isReady) {
            if (isReady) return 'is-ready';
            if (status === 'waiting_approval' || status === 'draft') return 'is-waiting';
            if (status === 'rejected') return 'is-rejected';
            return '';
        }

        function typeClass(type) {
            return {
                narkotika: 'narcotic',
                psikotropika: 'psychotropic',
                prekursor: 'precursor'
            }[type] || '';
        }

        function updateSummary(summary = {}) {
            $('#documentTotalCount').text(formatNumber(summary.documents));
            $('#documentSetCount').text(formatNumber(summary.sets));
            $('#documentPoCount').text(formatNumber(summary.purchase_orders));
            $('#documentNarcoticCount').text(formatNumber(summary.narkotika));
            $('#documentPsychotropicCount').text(formatNumber(summary.psikotropika));
            $('#documentPrecursorCount').text(formatNumber(summary.prekursor));
        }

        function selectType(type) {
            activeType = type || '';
            $('.document-type-tabs [data-type]').each(function() {
                const active = String($(this).data('type') || '') === activeType;
                $(this).toggleClass('is-active', active).attr('aria-pressed', active ? 'true' : 'false');
            });
        }

        const table = $('#documentTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            searchDelay: 350,
            pageLength: 10,
            order: [[3, 'desc']],
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
                    updateSummary(response.summary || {});
                    return response.data || [];
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Arsip dokumen gagal dimuat.';
                    $('#documentTable_processing').hide();
                    console.error(message);
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                {
                    data: 'document_number',
                    name: 'document_number',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        const extra = Number(row.document_count) > 1
                            ? `<span class="document-extra-count">+${formatNumber(Number(row.document_count) - 1)} nomor lain</span>` : '';
                        return `<div class="document-number-cell"><strong>${escapeHtml(value)}</strong>${extra}<small>${formatNumber(row.page_count)} halaman cetak</small></div>`;
                    }
                },
                {
                    data: 'type_short_label',
                    name: 'type',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<span class="document-type-badge is-${typeClass(row.type)}">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                    render: function(value, type) { return type === 'display' ? formatDate(value) : value; }
                },
                {
                    data: 'purchase_order_number',
                    name: 'purchase_order_number',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="document-reference"><strong>${escapeHtml(value)}</strong><small title="${escapeHtml(row.distributor)}">${escapeHtml(row.distributor)}</small></div>`;
                    }
                },
                { data: 'branch', name: 'branch' },
                {
                    data: 'item_count',
                    name: 'item_count',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="document-content-count"><strong>${formatNumber(value)} item obat</strong><small>${formatNumber(row.document_count)} surat · ${formatNumber(row.copy_count)} rangkap</small></div>`;
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
                        return `<div class="document-action-group">
                            <button type="button" class="document-action-button js-document-detail" data-type="${escapeHtml(row.type)}" data-purchase-order="${Number(row.purchase_order_id)}" title="Lihat detail" aria-label="Lihat detail"><i class="mdi mdi-eye-outline"></i></button>
                            <a class="document-action-button" href="${escapeHtml(row.preview_url)}" target="_blank" rel="noopener" title="Pratinjau dokumen" aria-label="Pratinjau dokumen"><i class="mdi mdi-file-eye-outline"></i></a>
                            <a class="document-action-button is-print" href="${escapeHtml(row.print_url)}" target="_blank" rel="noopener" title="Cetak atau simpan PDF" aria-label="Cetak atau simpan PDF"><i class="mdi mdi-printer-outline"></i></a>
                        </div>`;
                    }
                },
                { data: 'search_text', name: 'search_text', visible: false }
            ],
            language: {
                processing: '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan arsip...',
                emptyTable: 'Belum ada surat pesanan khusus pada cabang yang dapat Anda akses.',
                zeroRecords: 'Dokumen yang sesuai filter tidak ditemukan.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ kelompok dokumen',
                infoEmpty: 'Belum ada dokumen',
                paginate: { previous: '‹', next: '›' }
            }
        });

        $('#documentSearch').on('input', function() {
            const value = this.value;
            $(this).closest('.document-search').toggleClass('has-value', value.length > 0);
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => table.search(value).draw(), 300);
        });

        $('#clearDocumentSearch').on('click', function() {
            $('#documentSearch').val('').trigger('input').focus();
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

        $('#documentStatusFilter, #documentDateStart, #documentDateEnd').on('change', function() {
            const start = $('#documentDateStart').val();
            const end = $('#documentDateEnd').val();
            if (start && end && end < start) $('#documentDateEnd').val(start);
            table.ajax.reload();
        });

        $('#documentPageLength').on('change', function() {
            table.page.len(Number(this.value) || 10).draw();
        });

        $('#resetDocumentFilters').on('click', function() {
            selectType('');
            $('#documentStatusFilter, #documentDateStart, #documentDateEnd').val('');
            $('#documentSearch').val('').closest('.document-search').removeClass('has-value');
            table.search('').ajax.reload();
        });

        $('#refreshDocumentTable, #refreshDocumentArchive').on('click', function() {
            const icon = $(this).find('.mdi-refresh').addClass('mdi-spin');
            table.ajax.reload(function() { icon.removeClass('mdi-spin'); }, false);
        });

        $('#scrollDocumentArchive').on('click', function() {
            document.getElementById('documentArchiveSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        $('#documentTemplateBranch').on('change', function() {
            const branchId = Number(this.value) || 0;
            if (!branchId) return;
            window.location.href = `${documentIndexUrl}?branch_id=${branchId}#documentTemplateLibrary`;
        });

        $('#documentTable').on('click', '.js-document-detail', function() {
            const type = String($(this).data('type'));
            const purchaseOrder = Number($(this).data('purchase-order'));
            $('#documentDetailLoading').removeClass('d-none').html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Menyiapkan detail dokumen...');
            $('#documentDetailContent').addClass('d-none');
            detailModal.show();

            $.get(`${documentDetailBase}/${encodeURIComponent(type)}/${purchaseOrder}`)
                .done(function(response) { renderDocumentDetail(response.data || {}); })
                .fail(function(xhr) {
                    $('#documentDetailLoading').html(`<i class="mdi mdi-alert-circle-outline text-danger"></i> ${escapeHtml(xhr.responseJSON?.message || 'Detail dokumen tidak dapat dimuat.')}`);
                });
        });

        function renderDocumentDetail(documentData) {
            const typeIcon = {
                narkotika: 'mdi-alert-octagon-outline',
                psikotropika: 'mdi-brain',
                prekursor: 'mdi-flask-outline'
            }[documentData.type] || 'mdi-file-document-outline';
            const numbers = Array.isArray(documentData.document_numbers) ? documentData.document_numbers : [];
            const items = Array.isArray(documentData.items) ? documentData.items : [];

            $('#documentDetailIcon').attr('class', `is-${typeClass(documentData.type)}`).html(`<i class="mdi ${typeIcon}"></i>`);
            $('#documentDetailTitle').text(documentData.type_label || 'Surat Pesanan');
            $('#documentDetailNumber').text(documentData.document_number || '-');
            $('#documentDetailSummary').html(`
                <div><span>Tanggal</span><strong>${formatDate(documentData.date)}</strong></div>
                <div><span>Purchase Order</span><strong title="${escapeHtml(documentData.purchase_order_number)}">${escapeHtml(documentData.purchase_order_number || '-')}</strong></div>
                <div><span>Distributor</span><strong title="${escapeHtml(documentData.distributor)}">${escapeHtml(documentData.distributor || '-')}</strong></div>
                <div><span>Cabang</span><strong title="${escapeHtml(documentData.branch)}">${escapeHtml(documentData.branch || '-')}</strong></div>
                <div><span>Status PO</span><strong>${escapeHtml(documentData.status_label || '-')}</strong></div>
            `);
            $('#documentNumberList').html(numbers.map(number => `<span><i class="mdi mdi-file-document-check-outline"></i>${escapeHtml(number)}</span>`).join('') || '<span>-</span>');
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
    });
</script>
