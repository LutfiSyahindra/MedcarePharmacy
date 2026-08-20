<script>
    $(function() {
        const labelEndpoint = @json(route("dokumen.etiket.table"));
        const labelIndexUrl = @json(route("dokumen.etiket.index"));
        let activeType = '';
        let searchTimer = null;

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
        }

        function formatDateTime(value) {
            if (!value) return { date: '-', time: '-' };
            const [datePart, timePart = ''] = String(value).split(' ');
            const date = new Date(`${datePart}T${timePart || '00:00'}:00`);
            if (Number.isNaN(date.getTime())) return { date: escapeHtml(value), time: '' };

            return {
                date: new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric'
                }).format(date),
                time: new Intl.DateTimeFormat('id-ID', {
                    hour: '2-digit', minute: '2-digit'
                }).format(date).replace('.', ':')
            };
        }

        function typeClass(type) {
            return type === 'penjualan_racikan' ? 'compound' : 'non-compound';
        }

        function updateSummary(summary = {}) {
            $('#labelTotalCount').text(formatNumber(summary.labels));
            $('#labelSetCount, #labelPrescriptionCount').text(formatNumber(summary.sets));
            $('#labelNonCompoundCount').text(formatNumber(summary.non_compound));
            $('#labelCompoundCount').text(formatNumber(summary.compound));
        }

        function selectType(type) {
            activeType = type || '';
            $('.document-type-tabs [data-type]').each(function() {
                const selected = String($(this).data('type') || '') === activeType;
                $(this).toggleClass('is-active', selected).attr('aria-pressed', selected ? 'true' : 'false');
            });
        }

        const table = $('#labelTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            searching: false,
            pageLength: 10,
            order: [],
            dom: 'rt<"document-table-footer"ip>',
            ajax: {
                url: labelEndpoint,
                data: function(request) {
                    request.etiket_search = $('#labelSearch').val().trim();
                    request.type = activeType;
                    request.branch_id = $('#labelBranchFilter').val() || '';
                    request.date_start = $('#labelDateStart').val() || '';
                    request.date_end = $('#labelDateEnd').val() || '';
                },
                dataSrc: function(response) {
                    updateSummary(response.summary || {});
                    return response.data || [];
                },
                error: function(xhr) {
                    $('#labelTable_processing').hide();
                    console.error(xhr.responseJSON?.message || 'Arsip etiket gagal dimuat.');
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                {
                    data: 'nomor_transaksi',
                    name: 'nomor_transaksi',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="document-number-cell"><strong>${escapeHtml(value)}</strong><small>${escapeHtml(row.nomor_resep || 'Tanpa nomor resep')}</small></div>`;
                    }
                },
                {
                    data: 'type_short_label',
                    name: 'jenis_transaksi',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<span class="document-type-badge is-${typeClass(row.jenis_transaksi)}">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: 'tanggal_transaksi',
                    name: 'tanggal_transaksi',
                    render: function(value, type) {
                        if (type !== 'display') return value;
                        const formatted = formatDateTime(value);
                        return `<div class="document-reference"><strong>${escapeHtml(formatted.date)}</strong><small>${escapeHtml(formatted.time)} WIB</small></div>`;
                    }
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="label-patient-cell"><strong>${escapeHtml(value || 'Umum')}</strong><small title="${escapeHtml(row.dokter_name || '-')}">Dokter: ${escapeHtml(row.dokter_name || '-')}</small></div>`;
                    }
                },
                { data: 'branch_name', name: 'branch.name', orderable: false },
                {
                    data: 'label_count',
                    name: 'label_count',
                    orderable: false,
                    searchable: false,
                    render: function(value, type, row) {
                        if (type !== 'display') return value;
                        return `<div class="label-content-cell"><strong>${formatNumber(value)} lembar etiket</strong><small title="${escapeHtml(row.medicine_names || '-')}">${formatNumber(row.item_count)} item · ${escapeHtml(row.medicine_names || '-')}</small></div>`;
                    }
                },
                { data: 'cashier_name', name: 'createdBy.name', orderable: false },
                {
                    data: null,
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(value, type, row) {
                        return `<div class="document-action-group">
                            <a class="document-action-button" href="${escapeHtml(row.preview_url)}" target="_blank" rel="noopener" title="Pratinjau etiket" aria-label="Pratinjau etiket ${escapeHtml(row.nomor_transaksi)}"><i class="mdi mdi-file-eye-outline"></i></a>
                            <a class="document-action-button is-print" href="${escapeHtml(row.print_url)}" target="_blank" rel="noopener" title="Cetak etiket" aria-label="Cetak etiket ${escapeHtml(row.nomor_transaksi)}"><i class="mdi mdi-printer-outline"></i></a>
                        </div>`;
                    }
                }
            ],
            language: {
                processing: '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan arsip...',
                emptyTable: 'Belum ada etiket resep selesai pada cabang yang dapat Anda akses.',
                zeroRecords: 'Etiket yang sesuai filter tidak ditemukan.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ set etiket',
                infoEmpty: 'Belum ada etiket',
                paginate: { previous: '‹', next: '›' }
            }
        });

        $('#labelSearch').on('input', function() {
            const value = this.value;
            $(this).closest('.document-search').toggleClass('has-value', value.length > 0);
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => table.ajax.reload(), 300);
        });

        $('#clearLabelSearch').on('click', function() {
            $('#labelSearch').val('').trigger('input').focus();
        });

        $('#labelTemplateBranch').on('change', function() {
            const branchId = Number(this.value) || 0;
            if (!branchId) return;
            window.location.href = `${labelIndexUrl}?branch_id=${branchId}#labelTemplateLibrary`;
        });

        $('.document-type-tabs [data-type]').on('click', function() {
            selectType(String($(this).data('type') || ''));
            table.ajax.reload();
        });

        $('[data-label-type]').on('click', function() {
            selectType(String($(this).data('label-type') || ''));
            table.ajax.reload();
            document.getElementById('labelArchiveSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        $('#labelBranchFilter, #labelDateStart, #labelDateEnd').on('change', function() {
            const start = $('#labelDateStart').val();
            const end = $('#labelDateEnd').val();
            if (start && end && end < start) $('#labelDateEnd').val(start);
            table.ajax.reload();
        });

        $('#labelPageLength').on('change', function() {
            table.page.len(Number(this.value) || 10).draw();
        });

        $('#resetLabelFilters').on('click', function() {
            selectType('');
            $('#labelDateStart, #labelDateEnd').val('');
            if ($('#labelBranchFilter').attr('type') !== 'hidden') $('#labelBranchFilter').val('');
            $('#labelSearch').val('').closest('.document-search').removeClass('has-value');
            table.ajax.reload();
        });

        $('#refreshLabelTable, #refreshLabelArchive').on('click', function() {
            const icon = $(this).find('.mdi-refresh').addClass('mdi-spin');
            table.ajax.reload(function() { icon.removeClass('mdi-spin'); }, false);
        });

        $('#scrollLabelArchive').on('click', function() {
            document.getElementById('labelArchiveSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
</script>
