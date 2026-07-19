<script>
    $(document).ready(function() {
        document.body.classList.remove('pos-fullscreen-mode');

        const urls = {
            pos: '{{ route("penjualan.pos") }}',
            table: '{{ route("penjualan.pos.table") }}',
            show: '{{ route("penjualan.pos.show", ":id") }}',
            receipt: '{{ route("penjualan.pos.receipt", ":id") }}',
            cancel: '{{ route("penjualan.pos.cancel", ":id") }}'
        };
        const statusLabels = {
            draft: 'Sementara',
            completed: 'Selesai',
            cancelled: 'Batal'
        };
        const paymentLabels = {
            unpaid: 'Belum Bayar',
            paid: 'Lunas',
            credit: 'Tagihan',
            void: 'Void'
        };

        let historySearchTimer = null;
        let detailRequest = null;
        let selectedTransaction = null;
        let drawerTrigger = null;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[character];
            });
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(Number(value) || 0);
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 2
            }).format(Number(value) || 0);
        }

        function formatInputDate(date) {
            const offset = date.getTimezoneOffset();
            return new Date(date.getTime() - (offset * 60000)).toISOString().slice(0, 10);
        }

        function parseTransactionDate(value) {
            if (!value) return { date: '-', time: '-' };

            const [datePart, timePart = ''] = String(value).split(' ');
            const parsed = new Date(`${datePart}T${timePart || '00:00'}:00`);

            if (Number.isNaN(parsed.getTime())) {
                return { date: value, time: '' };
            }

            return {
                date: new Intl.DateTimeFormat('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }).format(parsed),
                time: new Intl.DateTimeFormat('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(parsed).replace('.', ':') + ' WIB'
            };
        }

        function formatRangeDate(value) {
            if (!value) return '';
            const parsed = new Date(`${value}T00:00:00`);
            return new Intl.DateTimeFormat('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            }).format(parsed);
        }

        function updateHistorySummary(summary = {}) {
            $('#historyTotal, #historyResultCount').text(formatNumber(summary.total));
            $('#historyCompleted').text(formatNumber(summary.completed));
            $('#historyDraft').text(formatNumber(summary.draft));
            $('#historyCancelled').text(formatNumber(summary.cancelled));
            $('#historyRevenue').text(formatCurrency(summary.grand_total));
            $('#historyDue').text(formatCurrency(summary.sisa_tagihan));
            $('#historyAverage').text(formatCurrency(summary.average_ticket));
        }

        function updateDateRangeLabel() {
            const start = $('#historyDateStart').val();
            const end = $('#historyDateEnd').val();
            let label = 'Semua waktu';

            if (start && end && start === end) {
                label = formatRangeDate(start);
            } else if (start && end) {
                label = `${formatRangeDate(start)} - ${formatRangeDate(end)}`;
            } else if (start) {
                label = `Mulai ${formatRangeDate(start)}`;
            } else if (end) {
                label = `Sampai ${formatRangeDate(end)}`;
            }

            $('#historyDateRangeLabel').text(label);
        }

        function updateFilterState() {
            let active = 0;
            const searchValue = $('#historySearch').val().trim();

            if (searchValue) active++;
            if ($('#historyDateStart').val() || $('#historyDateEnd').val()) active++;
            if ($('#historyTypeFilter').val()) active++;
            if ($('#historyStatusFilter').val()) active++;
            if ($('#historyPaymentFilter').val()) active++;
            if ($('#historyBranchFilter').attr('type') !== 'hidden' && $('#historyBranchFilter').val()) active++;

            $('#historyFilterCount').text(active);
            $('#clearHistorySearch').prop('hidden', searchValue === '');
            updateDateRangeLabel();
        }

        function tableState(type, label) {
            const icons = {
                ready: 'mdi-check-circle',
                loading: 'mdi-loading mdi-spin',
                error: 'mdi-alert-circle'
            };
            $('#historyTableState').html(
                `<span class="is-${type}"><i class="mdi ${icons[type]}"></i>${escapeHtml(label)}</span>`
            );
        }

        function statusBadge(row) {
            const icon = row.status === 'completed' ? 'mdi-check-circle-outline' :
                (row.status === 'cancelled' ? 'mdi-close-circle-outline' : 'mdi-clock-outline');

            return `
                <div class="history-status-stack">
                    <span class="history-status-badge is-${escapeHtml(row.status)}">
                        <i class="mdi ${icon}"></i>${escapeHtml(row.status_label)}
                    </span>
                    <span class="history-payment-badge is-${escapeHtml(row.payment_status)}">
                        ${escapeHtml(row.payment_label)}
                    </span>
                </div>
            `;
        }

        function rowActions(row) {
            const actions = [`
                <button type="button" class="history-row-action" data-history-action="detail" data-id="${Number(row.id)}"
                    title="Lihat detail" aria-label="Lihat detail ${escapeHtml(row.nomor_transaksi)}">
                    <i class="mdi mdi-eye-outline"></i>
                </button>
            `];

            if (row.can_resume) {
                actions.push(`
                    <button type="button" class="history-row-action is-primary" data-history-action="resume" data-id="${Number(row.id)}"
                        title="Lanjutkan draft" aria-label="Lanjutkan draft ${escapeHtml(row.nomor_transaksi)}">
                        <i class="mdi mdi-playlist-edit"></i>
                    </button>
                `);
            }

            if (row.can_print) {
                actions.push(`
                    <button type="button" class="history-row-action" data-history-action="print" data-id="${Number(row.id)}"
                        title="Cetak struk" aria-label="Cetak struk ${escapeHtml(row.nomor_transaksi)}">
                        <i class="mdi mdi-printer-outline"></i>
                    </button>
                `);
            }

            if (row.can_cancel) {
                actions.push(`
                    <button type="button" class="history-row-action is-danger" data-history-action="cancel" data-id="${Number(row.id)}"
                        title="Batalkan transaksi" aria-label="Batalkan ${escapeHtml(row.nomor_transaksi)}">
                        <i class="mdi mdi-cancel"></i>
                    </button>
                `);
            }

            return `<div class="history-row-actions">${actions.join('')}</div>`;
        }

        $.fn.dataTable.ext.errMode = 'none';

        const HistoryTable = $('#tablePenjualanPos').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            pageLength: 15,
            searching: false,
            order: [],
            ajax: {
                url: urls.table,
                type: 'GET',
                data: function(request) {
                    request.history_search = $('#historySearch').val().trim();
                    request.status = $('#historyStatusFilter').val() || '';
                    request.payment_status = $('#historyPaymentFilter').val() || '';
                    request.jenis_transaksi = $('#historyTypeFilter').val() || '';
                    request.branch_id = $('#historyBranchFilter').val() || '';
                    request.date_start = $('#historyDateStart').val() || '';
                    request.date_end = $('#historyDateEnd').val() || '';
                },
                dataSrc: function(response) {
                    updateHistorySummary(response.summary || {});
                    $('#historyLastSync').text(new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    }).format(new Date()).replaceAll('.', ':'));
                    return response.data || [];
                },
                error: function(xhr) {
                    tableState('error', 'Gagal memuat data');
                    showAjaxError(xhr, 'Riwayat transaksi gagal dimuat.');
                }
            },
            columns: [{
                    data: 'nomor_transaksi',
                    name: 'tanggal_transaksi',
                    render: function(data, type, row) {
                        if (type !== 'display') return row.tanggal_transaksi || data;
                        const date = parseTransactionDate(row.tanggal_transaksi);
                        return `
                            <div class="history-transaction-cell">
                                <strong>${escapeHtml(data)}</strong>
                                <span><i class="mdi mdi-calendar-blank-outline"></i>${escapeHtml(date.date)} &middot; ${escapeHtml(date.time)}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    orderable: false,
                    render: (data, type, row) => type === 'display' ? `
                        <div class="history-customer-cell">
                            <strong>${escapeHtml(data || 'Umum')}</strong>
                            <span><i class="mdi mdi-account-tie-outline"></i>${escapeHtml(row.cashier_name || '-')}</span>
                        </div>
                    ` : data
                },
                {
                    data: 'jenis_label',
                    name: 'jenis_transaksi',
                    orderable: false,
                    render: (data, type, row) => type === 'display' ? `
                        <div class="history-type-cell">
                            <strong>${escapeHtml(data || '-')}</strong>
                            <span><i class="mdi mdi-map-marker-outline"></i>${escapeHtml(row.branch_name || '-')}</span>
                        </div>
                    ` : data
                },
                {
                    data: 'item_count',
                    name: 'details_count',
                    orderable: false,
                    searchable: false,
                    render: data => `<span class="history-item-count"><i class="mdi mdi-pill-multiple"></i>${formatNumber(data)} item</span>`
                },
                {
                    data: 'grand_total',
                    name: 'grand_total',
                    render: (data, type, row) => type === 'display' ? `
                        <div class="history-payment-cell">
                            <strong>${formatCurrency(data)}</strong>
                            ${Number(row.sisa_tagihan) > 0
                                ? `<span class="has-due"><i class="mdi mdi-alert-circle-outline"></i>Sisa ${formatCurrency(row.sisa_tagihan)}</span>`
                                : `<span><i class="mdi mdi-cash-check"></i>Bayar ${formatCurrency(row.total_bayar)}</span>`}
                        </div>
                    ` : data
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: row => statusBadge(row)
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: row => rowActions(row)
                }
            ],
            columnDefs: [{
                targets: [3, 5, 6],
                className: 'text-center'
            }],
            createdRow: function(row, data) {
                $(row).attr({
                    'data-transaction-id': data.id,
                    tabindex: 0,
                    'aria-label': `Buka detail ${data.nomor_transaksi}`
                });
            },
            drawCallback: function() {
                tableState('ready', 'Data siap');
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat riwayat...</span>',
                emptyTable: 'Belum ada transaksi kasir pada rentang ini.',
                zeroRecords: 'Transaksi yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ transaksi',
                infoEmpty: 'Tidak ada transaksi untuk ditampilkan',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        $('#tablePenjualanPos').on('processing.dt', function(event, settings, processing) {
            if (processing) tableState('loading', 'Memuat data...');
        }).on('error.dt', function() {
            tableState('error', 'Gagal memuat data');
        });

        function reloadHistory(resetPaging = true) {
            updateFilterState();
            HistoryTable.ajax.reload(null, resetPaging);
        }

        function validateDateRange() {
            const start = $('#historyDateStart').val();
            const end = $('#historyDateEnd').val();

            if (start && end && start > end) {
                Swal.fire('Rentang tanggal tidak valid', 'Tanggal akhir harus sama atau setelah tanggal awal.', 'warning');
                return false;
            }

            return true;
        }

        $('#historySearch').on('input', function() {
            updateFilterState();
            clearTimeout(historySearchTimer);
            historySearchTimer = setTimeout(() => reloadHistory(true), 350);
        });

        $('#clearHistorySearch').on('click', function() {
            clearTimeout(historySearchTimer);
            $('#historySearch').val('').focus();
            reloadHistory(true);
        });

        $('#historyTypeFilter, #historyStatusFilter, #historyPaymentFilter, #historyBranchFilter').on('change', function() {
            reloadHistory(true);
        });

        $('#historyDateStart, #historyDateEnd').on('change', function() {
            $('.history-range-presets button').removeClass('is-active');
            updateFilterState();
        });

        $('.history-range-presets button').on('click', function() {
            const range = $(this).data('range');
            const today = new Date();
            let start = '';
            let end = '';

            if (range !== 'all') {
                const startDate = new Date(today);
                const numberOfDays = range === 'today' ? 1 : (range === '7days' ? 7 : 30);
                startDate.setDate(startDate.getDate() - (numberOfDays - 1));
                start = formatInputDate(startDate);
                end = formatInputDate(today);
            }

            $('.history-range-presets button').removeClass('is-active');
            $(this).addClass('is-active');
            $('#historyDateStart').val(start);
            $('#historyDateEnd').val(end);
            reloadHistory(true);
        });

        $('#applyHistoryFilter').on('click', function() {
            if (validateDateRange()) reloadHistory(true);
        });

        $('#resetHistoryFilter').on('click', function() {
            clearTimeout(historySearchTimer);
            $('#historySearch, #historyDateStart, #historyDateEnd').val('');
            $('#historyTypeFilter, #historyStatusFilter, #historyPaymentFilter').val('');

            if ($('#historyBranchFilter').attr('type') !== 'hidden') {
                $('#historyBranchFilter').val('');
            }

            $('.history-range-presets button').removeClass('is-active');
            $('.history-range-presets button[data-range="all"]').addClass('is-active');
            reloadHistory(true);
        });

        $('#refreshHistoryBtn').on('click', function() {
            $(this).find('i').addClass('mdi-spin');
            HistoryTable.ajax.reload(() => $(this).find('i').removeClass('mdi-spin'), false);
        });

        $('[data-summary-status]').on('click', function() {
            $('#historyStatusFilter').val($(this).data('summary-status') || '');
            reloadHistory(true);
        });

        $('[data-summary-payment]').on('click', function() {
            $('#historyPaymentFilter').val($(this).data('summary-payment') || '');
            reloadHistory(true);
        });

        $('#tablePenjualanPos tbody').on('click', 'tr[data-transaction-id]', function(event) {
            if ($(event.target).closest('button, a').length) return;
            showTransaction($(this).data('transaction-id'), this);
        }).on('keydown', 'tr[data-transaction-id]', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showTransaction($(this).data('transaction-id'), this);
            }
        });

        $(document).on('click', '[data-history-action]', function(event) {
            event.stopPropagation();
            const id = Number($(this).data('id'));
            const action = $(this).data('history-action');

            if (action === 'detail') showTransaction(id, this);
            if (action === 'resume') window.location.href = `${urls.pos}?draft_id=${encodeURIComponent(id)}`;
            if (action === 'print') window.open(urls.receipt.replace(':id', id), '_blank', 'noopener');
            if (action === 'cancel') cancelTransaction(id);
        });

        function openDrawer(trigger) {
            drawerTrigger = trigger || document.activeElement;
            $('body').addClass('history-drawer-open');
            $('#transactionDrawerBackdrop').prop('hidden', false);
            $('#transactionDrawer').attr('aria-hidden', 'false');

            requestAnimationFrame(function() {
                $('#transactionDrawerBackdrop, #transactionDrawer').addClass('is-open');
                $('#transactionDrawer [data-close-drawer]').trigger('focus');
            });
        }

        function closeDrawer() {
            $('#transactionDrawerBackdrop, #transactionDrawer').removeClass('is-open');
            $('#transactionDrawer').attr('aria-hidden', 'true');
            $('body').removeClass('history-drawer-open');
            setTimeout(() => $('#transactionDrawerBackdrop').prop('hidden', true), 230);

            if (drawerTrigger && typeof drawerTrigger.focus === 'function') {
                drawerTrigger.focus();
            }
        }

        $('#transactionDrawerBackdrop').on('click', closeDrawer);
        $(document).on('click', '[data-close-drawer]', closeDrawer);
        $(document).on('keydown', function(event) {
            if (event.key === 'Escape' && $('#transactionDrawer').hasClass('is-open')) closeDrawer();
        });

        function showTransaction(id, trigger) {
            if (detailRequest) detailRequest.abort();

            selectedTransaction = null;
            openDrawer(trigger);
            $('#transactionDrawerTitle').text('Memuat transaksi...');
            $('#transactionDrawerMeta').empty();
            $('#transactionDrawerLoading').prop('hidden', false);
            $('#transactionDrawerBody, #transactionDrawerFooter').prop('hidden', true).empty();

            const request = $.get(urls.show.replace(':id', id));
            detailRequest = request;

            request.done(function(transaction) {
                    selectedTransaction = transaction;
                    renderTransactionDetail(transaction);
                })
                .fail(function(xhr, status) {
                    if (status === 'abort') return;

                    $('#transactionDrawerLoading').prop('hidden', true);
                    $('#transactionDrawerTitle').text('Detail gagal dimuat');
                    $('#transactionDrawerBody').prop('hidden', false).html(`
                        <div class="history-detail-card text-center py-5">
                            <i class="mdi mdi-alert-circle-outline text-danger fs-2"></i>
                            <p class="mt-2 mb-3 text-muted">${escapeHtml(xhr.responseJSON?.message || 'Terjadi kesalahan saat memuat transaksi.')}</p>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-retry-detail="${Number(id)}">Coba Lagi</button>
                        </div>
                    `);
                })
                .always(function() {
                    if (detailRequest === request) detailRequest = null;
                });
        }

        $(document).on('click', '[data-retry-detail]', function() {
            showTransaction(Number($(this).data('retry-detail')), this);
        });

        function detailInfo(label, value) {
            return `
                <div class="history-detail-info">
                    <small>${escapeHtml(label)}</small>
                    <strong title="${escapeHtml(value || '-')}">${escapeHtml(value || '-')}</strong>
                </div>
            `;
        }

        function renderTransactionDetail(transaction) {
            const transactionDate = parseTransactionDate(transaction.tanggal_transaksi);
            const details = transaction.details || [];
            const payments = transaction.payments || [];
            const isPrescription = ['penjualan_resep', 'penjualan_racikan'].includes(transaction.jenis_transaksi);
            const isCompoundPrescription = transaction.jenis_transaksi === 'penjualan_racikan';
            const consumptionLabels = {
                sebelum_makan: 'Sebelum makan',
                sesudah_makan: 'Sesudah makan',
                bersama_makan: 'Bersama makan',
                tidak_terkait_makan: 'Tidak terkait makan',
                sesuai_instruksi: 'Sesuai instruksi dokter'
            };
            const detailRows = details.map(function(detail) {
                const batches = (detail.batch_summary || []).map(batch => `
                    <span>${escapeHtml(batch.no_batch || 'Tanpa batch')} &middot; ${formatNumber(batch.qty_stok)} stok</span>
                `).join('') || '<span>-</span>';

                return `
                    <tr>
                        <td>
                            <div class="history-detail-product">
                                <strong>${escapeHtml(detail.nama_obat)}</strong>
                                <span>${escapeHtml(detail.kode_obat || '-')} &middot; ${escapeHtml(detail.satuan_jual || '-')}</span>
                                ${isCompoundPrescription ? `<span><b>${escapeHtml(detail.racikan_group || 'R/ -')}</b> &middot; Dosis komponen ${escapeHtml(detail.dosis_komponen || '-')}</span>` : ''}
                                ${isPrescription ? `<span><b>S:</b> ${escapeHtml(detail.aturan_pakai || '-')}</span>` : ''}
                                ${isPrescription && (detail.waktu_konsumsi || detail.durasi_hari) ? `<span>${escapeHtml(consumptionLabels[detail.waktu_konsumsi] || detail.waktu_konsumsi || '')}${detail.waktu_konsumsi && detail.durasi_hari ? ' &middot; ' : ''}${detail.durasi_hari ? `${formatNumber(detail.durasi_hari)} hari` : ''}</span>` : ''}
                                ${detail.keterangan ? `<span>${escapeHtml(detail.keterangan)}</span>` : ''}
                            </div>
                        </td>
                        <td class="text-end">${formatNumber(detail.qty_jual)}</td>
                        <td class="text-end">${formatCurrency(detail.harga_jual)}</td>
                        <td class="text-end">${formatCurrency(detail.diskon_nominal)}</td>
                        <td><div class="history-batch-list">${batches}</div></td>
                        <td class="text-end"><strong>${formatCurrency(detail.total_line)}</strong></td>
                    </tr>
                `;
            }).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">Belum ada item.</td></tr>';

            const paymentRows = payments.map(payment => `
                <div class="history-payment-entry">
                    <span>
                        <b>${escapeHtml(payment.metode_label || payment.metode)}</b>
                        <small>${escapeHtml(payment.reference_no || payment.paid_at || 'Tanpa referensi')}</small>
                    </span>
                    <strong>${formatCurrency(payment.amount)}</strong>
                </div>
            `).join('') || '<span class="text-muted small">Belum ada pembayaran.</span>';

            const noteCard = transaction.catatan ? `
                <section class="history-detail-card">
                    <div class="history-detail-card-heading">
                        <strong><i class="mdi mdi-note-text-outline"></i>Catatan Transaksi</strong>
                    </div>
                    <p class="history-detail-note">${escapeHtml(transaction.catatan)}</p>
                </section>
            ` : '';

            const cancellationCard = transaction.status === 'cancelled' ? `
                <section class="history-detail-card history-cancellation-card">
                    <div class="history-detail-card-heading">
                        <strong><i class="mdi mdi-cancel"></i>Informasi Pembatalan</strong>
                        <span>${escapeHtml(transaction.cancelled_at || '-')}</span>
                    </div>
                    <p class="history-detail-note"><strong>${escapeHtml(transaction.cancelled_by || '-')}</strong><br>${escapeHtml(transaction.cancellation_reason || 'Tidak ada alasan pembatalan.')}</p>
                </section>
            ` : '';

            $('#transactionDrawerTitle').text(transaction.nomor_transaksi);
            $('#transactionDrawerMeta').html(`
                <span><i class="mdi mdi-calendar-blank-outline"></i>${escapeHtml(transactionDate.date)} &middot; ${escapeHtml(transactionDate.time)}</span>
                <span><i class="mdi mdi-map-marker-outline"></i>${escapeHtml(transaction.branch || '-')}</span>
                <span><i class="mdi mdi-circle-slice-8"></i>${escapeHtml(statusLabels[transaction.status] || transaction.status)}</span>
            `);
            $('#transactionDrawerBody').html(`
                <div class="history-detail-stack">
                    <section class="history-detail-card">
                        <div class="history-detail-card-heading">
                            <strong><i class="mdi mdi-account-details-outline"></i>Pelanggan & Transaksi</strong>
                            <span>${escapeHtml(transaction.jenis_label || '-')}</span>
                        </div>
                        <div class="history-detail-grid">
                            ${detailInfo('Pelanggan', transaction.customer_name || 'Umum')}
                            ${detailInfo('Telepon', transaction.customer_phone)}
                            ${detailInfo('Kasir', transaction.created_by)}
                            ${detailInfo('Nomor resep', transaction.nomor_resep)}
                            ${detailInfo('Tanggal resep', transaction.tanggal_resep)}
                            ${detailInfo('Dokter', transaction.dokter_name)}
                            ${detailInfo('Asal resep', transaction.asal_resep)}
                            ${detailInfo('Instansi', transaction.instansi_name)}
                        </div>
                    </section>

                    <section class="history-detail-card history-detail-items">
                        <div class="history-detail-card-heading">
                            <strong><i class="mdi mdi-pill-multiple"></i>Item Transaksi</strong>
                            <span>${formatNumber(details.length)} baris item</span>
                        </div>
                        <div class="history-detail-table-wrap">
                            <table class="table history-detail-table">
                                <thead>
                                    <tr>
                                        <th>Obat</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Diskon</th>
                                        <th>Batch</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>${detailRows}</tbody>
                            </table>
                        </div>
                    </section>

                    <section class="history-detail-card">
                        <div class="history-detail-card-heading">
                            <strong><i class="mdi mdi-cash-multiple"></i>Pembayaran & Total</strong>
                            <span>${escapeHtml(paymentLabels[transaction.payment_status] || transaction.payment_status)}</span>
                        </div>
                        <div class="history-detail-payment-layout">
                            <div class="history-payment-list">${paymentRows}</div>
                            <div class="history-financial-summary">
                                <div class="history-financial-row"><span>Subtotal</span><strong>${formatCurrency(transaction.subtotal_gross)}</strong></div>
                                <div class="history-financial-row"><span>Diskon item</span><strong>- ${formatCurrency(transaction.diskon_item_total)}</strong></div>
                                <div class="history-financial-row"><span>Diskon transaksi</span><strong>- ${formatCurrency(transaction.diskon_transaksi_nominal)}</strong></div>
                                <div class="history-financial-row"><span>Pajak</span><strong>${formatCurrency(transaction.pajak_total)}</strong></div>
                                ${isCompoundPrescription ? `<div class="history-financial-row"><span>Embalase racikan</span><strong>${formatCurrency(transaction.embalase)}</strong></div>` : ''}
                                <div class="history-financial-row is-total"><span>Total</span><strong>${formatCurrency(transaction.grand_total)}</strong></div>
                                <div class="history-financial-row"><span>Dibayar</span><strong>${formatCurrency(transaction.total_bayar)}</strong></div>
                                ${Number(transaction.sisa_tagihan) > 0 ? `<div class="history-financial-row is-due"><span>Sisa tagihan</span><strong>${formatCurrency(transaction.sisa_tagihan)}</strong></div>` : ''}
                                ${Number(transaction.kembalian) > 0 ? `<div class="history-financial-row"><span>Kembalian</span><strong>${formatCurrency(transaction.kembalian)}</strong></div>` : ''}
                            </div>
                        </div>
                    </section>
                    ${noteCard}
                    ${cancellationCard}
                </div>
            `);

            renderDrawerFooter(transaction);
            $('#transactionDrawerLoading').prop('hidden', true);
            $('#transactionDrawerBody, #transactionDrawerFooter').prop('hidden', false);
        }

        function renderDrawerFooter(transaction) {
            const buttons = [`
                <button type="button" class="btn btn-outline-secondary" data-close-drawer>
                    Tutup
                </button>
            `];

            if (transaction.status === 'draft') {
                buttons.push(`
                    <button type="button" class="btn btn-outline-danger" data-history-action="cancel" data-id="${Number(transaction.id)}">
                        <i class="mdi mdi-cancel"></i>Batalkan
                    </button>
                    <button type="button" class="btn btn-primary" data-history-action="resume" data-id="${Number(transaction.id)}">
                        <i class="mdi mdi-playlist-edit"></i>Lanjutkan Draft
                    </button>
                `);
            }

            if (transaction.status === 'completed') {
                buttons.push(`
                    <button type="button" class="btn btn-outline-danger" data-history-action="cancel" data-id="${Number(transaction.id)}">
                        <i class="mdi mdi-cancel"></i>Batalkan
                    </button>
                    <button type="button" class="btn btn-primary" data-history-action="print" data-id="${Number(transaction.id)}">
                        <i class="mdi mdi-printer-outline"></i>Cetak Struk
                    </button>
                `);
            }

            $('#transactionDrawerFooter').html(buttons.join(''));
        }

        function cancelTransaction(id) {
            const transactionNumber = selectedTransaction?.id === id ? selectedTransaction.nomor_transaksi : 'transaksi ini';

            Swal.fire({
                title: 'Batalkan transaksi?',
                html: `<p class="mb-0">Stok dari <strong>${escapeHtml(transactionNumber)}</strong> akan dikembalikan bila transaksi sudah selesai.</p>`,
                input: 'textarea',
                inputLabel: 'Alasan pembatalan',
                inputPlaceholder: 'Contoh: salah input item atau dibatalkan pelanggan',
                inputAttributes: {
                    maxlength: 1000
                },
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Kembali',
                confirmButtonColor: '#d65364',
                reverseButtons: true,
                preConfirm: reason => {
                    if (!reason || !reason.trim()) {
                        Swal.showValidationMessage('Alasan pembatalan wajib diisi.');
                        return false;
                    }
                    return reason.trim();
                }
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urls.cancel.replace(':id', id),
                    type: 'PUT',
                    data: {
                        reason: result.value
                    },
                    success: function(response) {
                        if ($('#transactionDrawer').hasClass('is-open')) closeDrawer();
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 2500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        HistoryTable.ajax.reload(null, false);
                    },
                    error: xhr => showAjaxError(xhr, 'Transaksi gagal dibatalkan.')
                });
            });
        }

        function showAjaxError(xhr, fallback) {
            const errors = xhr.responseJSON?.errors || {};
            const firstError = Object.values(errors)[0]?.[0];
            Swal.fire('Gagal', firstError || xhr.responseJSON?.message || fallback, 'error');
        }

        updateFilterState();
    });
</script>
