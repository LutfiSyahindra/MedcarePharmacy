<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        let stockAlertStatus = '';
        let batchObatId = '';
        let batchExpiryStatus = '';
        let riwayatObatId = '';
        let riwayatBatchId = '';
        let expiredWarningDays = $('#expiredWarningDays').val() || 90;
        let lastStockSummary = {};
        const batchOptionsBaseUrl = '{{ url("medcare/menu/stok/stok/batch-options") }}';
        const updateBatchHargaUrl = '{{ route("stok.batch.updateHargaJual", ":id") }}';

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        flatpickr('#tanggal_mutasi', {
            dateFormat: 'Y-m-d',
            defaultDate: moment().format('YYYY-MM-DD')
        });

        flatpickr('#mutasi_expired_date', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        $('#mutasi_obat_id').select2({
            placeholder: 'Pilih obat',
            dropdownParent: $('#mutasiStokModal'),
            width: '100%',
            ajax: {
                url: '{{ route("stok.obatOptions") }}',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term || ''
                }),
                processResults: data => ({
                    results: data || []
                })
            }
        });

        $('#mutasi_stok_batch_id').select2({
            placeholder: 'Pilih batch',
            dropdownParent: $('#mutasiStokModal'),
            width: '100%'
        });

        function escapeHtml(value) {
            return String(value ?? '-').replace(/[&<>"']/g, function(character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [character];
            });
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 2
            }).format(Number(value) || 0);
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(Number(value) || 0);
        }

        function formatDate(value) {
            if (!value) return '-';
            const date = moment(value);
            return date.isValid() ? date.format('DD-MM-YYYY') : value;
        }

        function dateDistance(value) {
            if (!value) return 'Tanpa ED';
            const date = moment(value);
            if (!date.isValid()) return value;

            const days = date.startOf('day').diff(moment().startOf('day'), 'days');
            if (days < 0) return `Lewat ${Math.abs(days)} hari`;
            if (days === 0) return 'Hari ini';
            return `${days} hari lagi`;
        }

        const alertLabels = {
            '': 'Semua stok',
            aman: 'Stok aman',
            menipis: 'Stok menipis',
            kosong: 'Stok kosong',
            expired: 'Expired',
            akan_expired: 'Akan expired'
        };

        function statusIcon(status) {
            return {
                aman: 'mdi-check-circle-outline',
                menipis: 'mdi-alert-circle-outline',
                kosong: 'mdi-close-circle-outline',
                expired: 'mdi-calendar-remove-outline',
                akan_expired: 'mdi-calendar-alert-outline'
            } [status] || 'mdi-information-outline';
        }

        function statusBadge(status, label) {
            return `
                <span class="stock-status is-${escapeHtml(status)}">
                    <i class="mdi ${statusIcon(status)}"></i>
                    ${escapeHtml(label)}
                </span>
            `;
        }

        function itemCell(row) {
            return `
                <span class="stock-item">
                    <strong>${escapeHtml(row.nama_obat)}</strong>
                    <small><i class="mdi mdi-barcode-scan"></i>${escapeHtml(row.kode_obat)} <span>${escapeHtml(row.satuan)}</span></small>
                </span>
            `;
        }

        function quantityCell(value, satuan, icon = 'mdi-package-variant') {
            return `
                <span class="stock-qty-value">
                    <i class="mdi ${icon}"></i>
                    <strong>${formatNumber(value)}</strong>
                    <small>${escapeHtml(satuan)}</small>
                </span>
            `;
        }

        function totalStockCell(row) {
            const total = Number(row.total_stok) || 0;
            const minimum = Number(row.stok_minimum) || 0;
            const ratio = minimum > 0 ? Math.min(100, Math.round((total / minimum) * 100)) : 100;
            const tone = total <= 0 ? 'is-danger' : (minimum > 0 && total <= minimum ? 'is-warning' : 'is-safe');

            return `
                <span class="stock-qty-stack">
                    ${quantityCell(total, row.satuan)}
                    <span class="stock-progress ${tone}">
                        <span style="width:${ratio}%"></span>
                    </span>
                    <small>Minimum ${formatNumber(minimum)} ${escapeHtml(row.satuan)}</small>
                </span>
            `;
        }

        function expiryCell(value, status) {
            const tone = status === 'expired' ? 'is-danger' : (status === 'akan_expired' ? 'is-warning' : 'is-safe');

            return `
                <span class="stock-date-pill ${tone}">
                    <i class="mdi mdi-calendar-alert-outline"></i>
                    <strong>${escapeHtml(formatDate(value))}</strong>
                    <small>${escapeHtml(dateDistance(value))}</small>
                </span>
            `;
        }

        function updateLastSync(selector) {
            $(selector).text(`Update ${moment().format('HH:mm')}`);
        }

        function activeStockSearch() {
            return ($('#stockSearch').val() || '').trim();
        }

        function stockFilterInfoText(summary = {}) {
            const search = activeStockSearch();
            const statusLabel = summary.active_status_label || alertLabels[stockAlertStatus] || 'Semua stok';
            const total = Number(summary.total_item) || 0;
            const matched = Number(summary.total_search_matched ?? total) || 0;
            const available = Number(summary.total_available ?? matched) || 0;

            if (search && stockAlertStatus) {
                return `${statusLabel}, ${formatNumber(total)} dari ${formatNumber(matched)} hasil pencarian`;
            }

            if (search) {
                return `${formatNumber(matched)} dari ${formatNumber(available)} cocok`;
            }

            if (stockAlertStatus) {
                return `${statusLabel}, ${formatNumber(total)} item`;
            }

            return `Semua data, ${formatNumber(total)} item`;
        }

        function updateStockFilterCounts(counts = {}) {
            ['all', 'aman', 'menipis', 'kosong', 'expired', 'akan_expired'].forEach(function(key) {
                $(`[data-stock-count="${key}"]`).text(formatNumber(counts[key] || 0));
            });
        }

        function updateStockFilterSnapshot(summary = lastStockSummary) {
            const search = activeStockSearch();
            const edDays = summary.expired_warning_days || $('#expiredWarningDays').val() || 90;
            const statusLabel = summary.active_status_label || alertLabels[stockAlertStatus] || 'Semua stok';
            const total = Number(summary.total_item);
            const matched = Number(summary.total_search_matched ?? summary.total_item ?? 0) || 0;
            const available = Number(summary.total_available ?? matched) || 0;
            const searchParts = [];

            $('#stockActiveFilterText').text(Number.isFinite(total) ? `${statusLabel} - ${formatNumber(total)} item` : statusLabel);

            if (search) {
                searchParts.push(`Pencarian: ${search}`);
            }

            searchParts.push(`Warning ED ${edDays} hari`);

            if (search) {
                searchParts.push(`${formatNumber(matched)} dari ${formatNumber(available)} cocok`);
            }

            $('#stockActiveSearchText').text(searchParts.join(' | '));
            $('#stockFilterInfo').text(stockFilterInfoText(summary));
        }

        function updateStockSummary(summary) {
            lastStockSummary = summary || {};
            summary = lastStockSummary;

            const totalItem = Number(summary.total_item) || 0;
            const lowCount = Number(summary.stok_menipis) || 0;
            const emptyCount = Number(summary.stok_kosong) || 0;
            const edRiskCount = (Number(summary.expired) || 0) + (Number(summary.akan_expired) || 0);
            const riskCount = lowCount + emptyCount + edRiskCount;
            const safePercent = totalItem > 0 ? Math.max(0, Math.round(((totalItem - riskCount) / totalItem) * 100)) : 0;
            const safeCount = Math.max(0, totalItem - riskCount);

            $('#stockTotalItem').text(formatNumber(totalItem));
            $('#stockTotalQty').text(formatNumber(summary.total_stok));
            $('#stockLowCount').text(formatNumber(lowCount));
            $('#stockExpiredCount').text(formatNumber(edRiskCount));
            $('#stockValueTotal').text(formatCurrency(summary.nilai_stok));
            $('#stockEmptyInsight').text(formatNumber(emptyCount));
            $('#stockLowInsight').text(formatNumber(lowCount));
            $('#stockEdInsight').text(formatNumber(edRiskCount));
            $('#stockHealthBar').css('width', `${safePercent}%`);
            $('#stockHealthBadge').text(`${safePercent}% aman`);
            $('#stockHealthText').text(totalItem ? `${formatNumber(safeCount)} dari ${formatNumber(totalItem)} item tanpa peringatan` : 'Belum ada item stok');
            $('#stockHealthBadge')
                .removeClass('is-safe is-warning is-danger')
                .addClass(safePercent >= 80 ? 'is-safe' : (safePercent >= 50 ? 'is-warning' : 'is-danger'));
            updateStockFilterCounts(summary.status_counts || {});
            updateStockFilterSnapshot(summary);
            updateLastSync('#stockLastSync');
        }

        function updateBatchSummary(summary) {
            const riskCount = (Number(summary.expired) || 0) + (Number(summary.akan_expired) || 0);
            $('#batchTotalCount').text(formatNumber(summary.total_batch));
            $('#batchTotalQty').text(formatNumber(summary.total_stok));
            $('#batchRiskCount').text(formatNumber(riskCount));
        }

        function updateRiwayatSummary(summary) {
            $('#riwayatTotalCount').text(formatNumber(summary.total_riwayat));
            $('#riwayatUpCount').text(formatNumber(summary.kenaikan));
            $('#riwayatDownCount').text(formatNumber(summary.penurunan));
            $('#riwayatLastChange').text(summary.terakhir || '-');
        }

        function batchCell(row) {
            return `
                <span class="stock-qty-stack">
                    <span class="stock-number"><i class="mdi mdi-barcode"></i>${escapeHtml(row.no_batch)}</span>
                    <small>${row.expired_date ? 'ED ' + escapeHtml(formatDate(row.expired_date)) : 'Tanpa ED'}</small>
                </span>
            `;
        }

        function priceDiffCell(value) {
            const diff = Number(value) || 0;
            const icon = diff > 0 ? 'mdi-trending-up' : (diff < 0 ? 'mdi-trending-down' : 'mdi-minus');
            const sign = diff > 0 ? '+' : '';

            return `<span class="stock-money"><i class="mdi ${icon}"></i>${sign}${formatCurrency(diff)}</span>`;
        }

        let StockTable = $('#tableStock').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            searching: false,
            ajax: {
                url: '{{ route("stok.table") }}',
                type: 'GET',
                data: function(request) {
                    request.alert_status = stockAlertStatus;
                    request.expired_days = expiredWarningDays;
                    request.stock_search = activeStockSearch();
                },
                dataSrc: function(response) {
                    updateStockSummary(response.summary || {});
                    return response.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: data => `<span class="stock-number">${escapeHtml(data)}</span>`
                },
                {
                    data: 'nama_obat',
                    render: (data, type, row) => itemCell(row)
                },
                {
                    data: 'total_stok',
                    render: (data, type, row) => totalStockCell(row)
                },
                {
                    data: 'batch_count',
                    render: data => `<span class="stock-number"><i class="mdi mdi-barcode-scan"></i>${formatNumber(data)}</span>`
                },
                {
                    data: 'nearest_expired_date',
                    render: (data, type, row) => expiryCell(data, row.status)
                },
                {
                    data: 'harga_beli_terakhir',
                    render: data => `<span class="stock-money"><i class="mdi mdi-cash"></i>${formatCurrency(data)}</span>`
                },
                {
                    data: 'stok_minimum',
                    render: (data, type, row) => `<span class="stock-number">${formatNumber(data)} ${escapeHtml(row.satuan)}</span>`
                },
                {
                    data: 'status_label',
                    render: (data, type, row) => statusBadge(row.status, row.status_label)
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    render: data => `<span class="stock-action-group">${data || ''}</span>`
                }
            ],
            columnDefs: [{
                targets: [0, 3, 8],
                className: 'text-center'
            }],
            rowCallback: function(row, data) {
                $(row)
                    .removeClass('is-row-aman is-row-menipis is-row-kosong is-row-expired is-row-akan_expired')
                    .addClass(`is-row-${data.status}`);
            },
            drawCallback: function() {
                $('#tableStock .btn-info').attr('title', 'Lihat batch obat');
                $('#tableStock .btn-primary').attr('title', 'Buka kartu stok');
                const info = this.api().page.info();
                const visible = Number(lastStockSummary.total_item ?? info.recordsDisplay) || 0;
                $('#stockVisibleInfo').text(`${formatNumber(visible)} data`);
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat stok...</span>',
                emptyTable: 'Belum ada data stok.',
                zeroRecords: 'Stok yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let BatchTable = $('#tableBatchStock').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            ajax: {
                url: '{{ route("stok.batchTable") }}',
                type: 'GET',
                data: function(request) {
                    request.obat_id = batchObatId;
                    request.expiry_status = batchExpiryStatus;
                    request.expired_days = expiredWarningDays;
                },
                dataSrc: function(response) {
                    updateBatchSummary(response.summary || {});
                    return response.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    render: row => itemCell(row)
                },
                {
                    data: 'no_batch',
                    render: data => `<span class="stock-number"><i class="mdi mdi-barcode"></i>${escapeHtml(data)}</span>`
                },
                {
                    data: 'expired_date',
                    render: (data, type, row) => expiryCell(data, row.status)
                },
                {
                    data: 'qty',
                    render: (data, type, row) => quantityCell(data, row.satuan, 'mdi-counter')
                },
                {
                    data: 'harga_beli',
                    render: data => `<span class="stock-money"><i class="mdi mdi-cash"></i>${formatCurrency(data)}</span>`
                },
                {
                    data: 'harga_jual',
                    render: data => `<span class="stock-money"><i class="mdi mdi-cash-plus"></i>${formatCurrency(data)}</span>`
                },
                {
                    data: 'nilai_stok',
                    render: data => `<span class="stock-money">${formatCurrency(data)}</span>`
                },
                {
                    data: null,
                    render: row => statusBadge(row.status, row.status_label)
                },
                {
                    data: 'last_movement_at',
                    render: data => `<span class="stock-number"><i class="mdi mdi-clock-outline"></i>${escapeHtml(data || '-')}</span>`
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: row => `
                        <span class="stock-action-group">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="ubahHargaBatch(${Number(row.id)})" title="Ubah harga jual tanpa menambah stok">
                                <i class="mdi mdi-cash-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="filterRiwayatHarga(${Number(row.id)}, ${Number(row.obat_id)})" title="Lihat riwayat harga">
                                <i class="mdi mdi-cash-clock"></i>
                            </button>
                        </span>
                    `
                }
            ],
            columnDefs: [{
                targets: [0],
                className: 'text-center'
            }],
            rowCallback: function(row, data) {
                $(row)
                    .removeClass('is-row-aman is-row-expired is-row-akan_expired')
                    .addClass(`is-row-${data.status}`);
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat batch...</span>',
                emptyTable: 'Belum ada batch stok aktif.',
                zeroRecords: 'Batch yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let RiwayatHargaTable = $('#tableRiwayatHarga').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            ajax: {
                url: '{{ route("stok.riwayatHarga.table") }}',
                type: 'GET',
                data: function(request) {
                    request.obat_id = riwayatObatId;
                    request.stok_batch_id = riwayatBatchId;
                },
                dataSrc: function(response) {
                    updateRiwayatSummary(response.summary || {});
                    return response.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    render: data => `<span class="stock-number"><i class="mdi mdi-clock-outline"></i>${escapeHtml(data || '-')}</span>`
                },
                {
                    data: null,
                    render: row => itemCell(row)
                },
                {
                    data: null,
                    render: row => batchCell(row)
                },
                {
                    data: 'harga_jual_lama',
                    render: data => `<span class="stock-money"><i class="mdi mdi-cash-minus"></i>${formatCurrency(data)}</span>`
                },
                {
                    data: 'harga_jual_baru',
                    render: data => `<span class="stock-money"><i class="mdi mdi-cash-plus"></i>${formatCurrency(data)}</span>`
                },
                {
                    data: 'selisih',
                    render: data => priceDiffCell(data)
                },
                {
                    data: 'alasan',
                    render: data => `<span class="stock-reason">${escapeHtml(data || '-')}</span>`
                },
                {
                    data: 'user',
                    render: data => `<span class="stock-number"><i class="mdi mdi-account-outline"></i>${escapeHtml(data || '-')}</span>`
                }
            ],
            columnDefs: [{
                targets: [0],
                className: 'text-center'
            }],
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat riwayat harga...</span>',
                emptyTable: 'Belum ada riwayat perubahan harga.',
                zeroRecords: 'Riwayat harga yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let searchTimer;
        $('#stockSearch').on('input', function() {
            const value = this.value;
            $(this).closest('.stock-search').toggleClass('has-value', Boolean(value));
            updateStockFilterSnapshot();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => StockTable.ajax.reload(), 250);
        });

        $('#clearStockSearch').on('click', function() {
            $('#stockSearch').val('').trigger('input').focus();
        });

        $('.stock-chip').on('click', function() {
            $('.stock-chip').removeClass('is-active');
            $(this).addClass('is-active');
            stockAlertStatus = $(this).data('alert') || '';
            updateStockFilterSnapshot();
            StockTable.ajax.reload();
        });

        $('#expiredWarningDays').on('change', function() {
            expiredWarningDays = this.value || 90;
            updateStockFilterSnapshot();
            StockTable.ajax.reload();
            BatchTable.ajax.reload();
        });

        $('#batchExpiryFilter').on('change', function() {
            batchExpiryStatus = this.value;
            BatchTable.ajax.reload();
        });

        $('#resetBatchFilter').on('click', function() {
            batchObatId = '';
            batchExpiryStatus = '';
            $('#batchExpiryFilter').val('');
            $('#batchFilterLabel').html('<i class="mdi mdi-filter-variant"></i> Semua batch');
            BatchTable.ajax.reload();
        });

        $('#resetRiwayatHargaFilter').on('click', function() {
            riwayatObatId = '';
            riwayatBatchId = '';
            $('#riwayatHargaFilterLabel').html('<i class="mdi mdi-filter-variant"></i> Semua riwayat harga');
            RiwayatHargaTable.ajax.reload();
        });

        $('#refreshStockTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            StockTable.ajax.reload(null, false);
            BatchTable.ajax.reload(null, false);
            RiwayatHargaTable.ajax.reload(null, false);
        });

        $('#tableStock, #tableBatchStock, #tableRiwayatHarga').on('xhr.dt', function() {
            $('#refreshStockTable').removeClass('is-loading').prop('disabled', false);
        });

        window.filterBatchObat = function(id) {
            batchObatId = id;
            const row = StockTable.rows().data().toArray().find(item => String(item.id) === String(id));
            $('#batchFilterLabel').html(`<i class="mdi mdi-filter-variant"></i> ${escapeHtml(row?.nama_obat || 'Obat #' + id)}`);
            BatchTable.ajax.reload();
            document.getElementById('batchStockSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        };

        window.filterRiwayatHarga = function(batchId, obatId) {
            riwayatBatchId = batchId || '';
            riwayatObatId = obatId || '';
            const row = BatchTable.rows().data().toArray().find(item => String(item.id) === String(batchId));
            const label = row ? `${row.nama_obat || 'Obat'} - Batch ${row.no_batch || '-'}` : `Batch #${batchId}`;
            $('#riwayatHargaFilterLabel').html(`<i class="mdi mdi-filter-variant"></i> ${escapeHtml(label)}`);
            RiwayatHargaTable.ajax.reload();
            document.getElementById('riwayatHargaSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        };

        window.ubahHargaBatch = function(batchId) {
            const row = BatchTable.rows().data().toArray().find(item => String(item.id) === String(batchId));

            if (!row) {
                Swal.fire('Gagal', 'Data batch tidak ditemukan di tabel.', 'error');
                return;
            }

            const hargaSekarang = Number(row.harga_jual) || 0;
            const hargaMargin = Number(row.harga_jual_margin) || 0;
            const faktorMargin = Number(row.margin_faktor_jual) || 1;
            const ppnMargin = Number(row.margin_ppn) || 0;
            const hargaDasarMargin = Number(row.margin_harga_beli_include_ppn) || 0;
            const marginHasMargin = Boolean(row.margin_has_margin);
            const marginReference = row.margin_reference || 'prioritas margin belum tersedia';
            const marginInfoClass = marginHasMargin ? 'text-success' : 'text-warning';
            const marginCalcText = `dasar ${formatCurrency(hargaDasarMargin)} sudah termasuk PPN ${ppnMargin.toLocaleString('id-ID', { maximumFractionDigits: 2 })}%`;
            const marginInfoText = marginHasMargin
                ? `Sesuai margin ${marginReference} (${marginCalcText}, faktor ${faktorMargin.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 })}): ${formatCurrency(hargaMargin)}`
                : `Belum ada margin aktif sesuai prioritas, ${marginCalcText}, faktor 1.000: ${formatCurrency(hargaMargin)}`;

            Swal.fire({
                title: 'Ubah Harga Jual',
                html: `
                    <div class="text-start">
                        <label class="form-label fw-bold">Batch</label>
                        <div class="mb-3 small text-muted">${escapeHtml(row.nama_obat)} - ${escapeHtml(row.no_batch)}</div>
                        <label for="swalMetodeHarga" class="form-label fw-bold">Metode Harga</label>
                        <select id="swalMetodeHarga" class="form-select mb-2">
                            <option value="manual">Input manual</option>
                            <option value="margin">Sesuai dengan margin</option>
                        </select>
                        <div class="form-text ${marginInfoClass} mb-3">${escapeHtml(marginInfoText)}</div>
                        <label for="swalHargaJualBaru" class="form-label fw-bold">Harga Jual Baru</label>
                        <input type="number" id="swalHargaJualBaru" class="form-control" min="0" step="0.01" value="${hargaSekarang}">
                        <div class="form-text mb-3">Harga sekarang: ${formatCurrency(row.harga_jual)}</div>
                        <label for="swalAlasanHarga" class="form-label fw-bold">Alasan</label>
                        <textarea id="swalAlasanHarga" class="form-control" rows="3" maxlength="1000" placeholder="Contoh: penyesuaian margin, perubahan HET, atau koreksi harga"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan Harga',
                cancelButtonText: 'Batal',
                focusConfirm: false,
                didOpen: () => {
                    const methodSelect = document.getElementById('swalMetodeHarga');
                    const priceInput = document.getElementById('swalHargaJualBaru');
                    let manualHargaValue = String(hargaSekarang);

                    const syncHargaMethod = () => {
                        const useMargin = methodSelect?.value === 'margin';

                        if (!priceInput) {
                            return;
                        }

                        if (useMargin) {
                            priceInput.value = hargaMargin;
                            priceInput.readOnly = true;
                            priceInput.classList.add('bg-light');
                            return;
                        }

                        priceInput.value = manualHargaValue;
                        priceInput.readOnly = false;
                        priceInput.classList.remove('bg-light');
                    };

                    priceInput?.addEventListener('input', function() {
                        if (methodSelect?.value !== 'margin') {
                            manualHargaValue = this.value;
                        }
                    });
                    methodSelect?.addEventListener('change', syncHargaMethod);
                    syncHargaMethod();
                    priceInput?.focus();
                },
                preConfirm: () => {
                    const metodeHarga = document.getElementById('swalMetodeHarga')?.value || 'manual';
                    const harga = Number(document.getElementById('swalHargaJualBaru')?.value);
                    const alasan = document.getElementById('swalAlasanHarga')?.value?.trim() || '';

                    if (metodeHarga === 'manual' && (Number.isNaN(harga) || harga < 0)) {
                        Swal.showValidationMessage('Harga jual baru wajib angka minimal 0.');
                        return false;
                    }

                    if (!alasan) {
                        Swal.showValidationMessage('Alasan perubahan harga wajib diisi.');
                        return false;
                    }

                    return metodeHarga === 'margin'
                        ? {
                            metode_harga: 'margin',
                            alasan
                        }
                        : {
                            metode_harga: 'manual',
                            harga_jual_baru: harga,
                            alasan
                        };
                }
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: updateBatchHargaUrl.replace(':id', batchId),
                    type: 'PUT',
                    data: result.value,
                    success: function(response) {
                        Swal.fire({
                            icon: response.changed ? 'success' : 'info',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 2500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        BatchTable.ajax.reload(null, false);
                        RiwayatHargaTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        const firstError = Object.values(errors)[0]?.[0];
                        Swal.fire('Gagal', firstError || xhr.responseJSON?.message || 'Harga jual batch gagal diperbarui.', 'error');
                    }
                });
            });
        };

        const mutationModes = {
            masuk: {
                title: 'Menambah stok',
                subtitle: 'Obat masuk akan menambah saldo batch dalam satuan stok dasar.',
                text: 'Batch baru atau batch yang sama akan bertambah sesuai qty.',
                icon: 'mdi-database-plus-outline',
                footer: 'Siap mencatat obat masuk.',
                batchText: 'Isi batch baru atau batch existing untuk stok masuk.',
                qtyHelp: 'Qty memakai satuan stok dasar obat.',
                submitClass: 'btn-primary',
                submitText: 'Simpan Mutasi'
            },
            keluar: {
                title: 'Mengurangi stok',
                subtitle: 'Obat keluar wajib memilih batch aktif yang masih memiliki saldo.',
                text: 'Saldo batch akan berkurang sesuai qty keluar.',
                icon: 'mdi-database-minus-outline',
                footer: 'Pilih batch aktif sebelum mencatat stok keluar.',
                batchText: 'Pilih batch yang akan dikurangi.',
                qtyHelp: 'Qty keluar tidak boleh melebihi stok batch.',
                submitClass: 'btn-danger',
                submitText: 'Simpan Keluar'
            },
            expired: {
                title: 'Mencatat expired',
                subtitle: 'Mutasi expired mengurangi batch dan meninggalkan jejak audit stok.',
                text: 'Qty expired akan keluar dari saldo batch.',
                icon: 'mdi-calendar-remove-outline',
                footer: 'Pilih batch expired atau batch yang akan ditarik.',
                batchText: 'Pilih batch obat yang expired.',
                qtyHelp: 'Qty expired tidak boleh melebihi stok batch.',
                submitClass: 'btn-danger',
                submitText: 'Simpan Expired'
            },
            penyesuaian_masuk: {
                title: 'Penyesuaian masuk',
                subtitle: 'Gunakan untuk koreksi hasil stok opname yang menambah stok.',
                text: 'Saldo batch akan bertambah sebagai penyesuaian.',
                icon: 'mdi-plus-circle-outline',
                footer: 'Siap mencatat penyesuaian masuk.',
                batchText: 'Isi batch yang disesuaikan atau buat batch baru.',
                qtyHelp: 'Qty penyesuaian memakai satuan stok dasar.',
                submitClass: 'btn-primary',
                submitText: 'Simpan Penyesuaian'
            },
            penyesuaian_keluar: {
                title: 'Penyesuaian keluar',
                subtitle: 'Gunakan untuk koreksi hasil stok opname yang mengurangi stok.',
                text: 'Saldo batch akan berkurang sebagai penyesuaian.',
                icon: 'mdi-minus-circle-outline',
                footer: 'Pilih batch aktif sebelum mencatat penyesuaian keluar.',
                batchText: 'Pilih batch yang akan dikoreksi.',
                qtyHelp: 'Qty penyesuaian keluar tidak boleh melebihi stok batch.',
                submitClass: 'btn-warning',
                submitText: 'Simpan Penyesuaian'
            }
        };

        function isInboundMutation() {
            return ['masuk', 'penyesuaian_masuk'].includes($('#jenis_mutasi').val());
        }

        function activeMutationMeta() {
            return mutationModes[$('#jenis_mutasi').val()] || mutationModes.masuk;
        }

        function selectedBatchMeta() {
            const selected = $('#mutasi_stok_batch_id').find(':selected');

            return {
                id: selected.val() || '',
                label: selected.text() || '',
                qty: Number(selected.data('qty')) || 0,
                harga: Number(selected.data('harga')) || 0,
                hargaJual: Number(selected.data('harga-jual')) || 0,
                ppn: Number(selected.data('ppn')) || 0,
                batch: selected.data('batch') || '',
                expired: selected.data('expired') || ''
            };
        }

        function updateBatchPlaceholder() {
            const emptyOption = $('#mutasi_stok_batch_id').find('option[value=""]').first();
            const label = isInboundMutation() ? 'Batch baru / isi manual' : 'Pilih batch';

            if (emptyOption.length) {
                emptyOption.text(label);
            }
        }

        function syncInboundBatchFields() {
            const inbound = isInboundMutation();
            const batch = selectedBatchMeta();
            const usingExistingBatch = inbound && Boolean(batch.id);
            const identityWasLocked = $('#mutasi_no_batch').prop('disabled');

            $('#mutasi_no_batch, #mutasi_expired_date')
                .prop('required', inbound && !usingExistingBatch)
                .prop('disabled', !inbound || usingExistingBatch);

            if (!inbound) {
                return;
            }

            if (usingExistingBatch) {
                $('#mutasi_no_batch').val(batch.batch);
                $('#mutasi_expired_date').val(batch.expired);
                $('#mutasi_harga_beli').val(batch.harga || '');
                $('#mutasi_harga_jual').val(batch.hargaJual || '');
            } else if (identityWasLocked) {
                $('#mutasi_no_batch, #mutasi_expired_date').val('');
            }
        }

        function updateMutationPreview() {
            const qty = Number($('#mutasi_qty').val()) || 0;
            const inbound = isInboundMutation();
            const meta = activeMutationMeta();
            const batch = selectedBatchMeta();
            const price = inbound ? (Number($('#mutasi_harga_beli').val()) || 0) : batch.harga;
            const value = qty * price;
            const prefix = inbound ? '+' : '-';
            const stockLabel = inbound ? 'stok masuk' : 'stok keluar';

            $('#mutasiPreviewQty').text(`${prefix}${formatNumber(qty)}`);
            $('#mutasiPreviewValue').text(formatCurrency(value));

            if (inbound && batch.id) {
                $('#mutasiBatchHelp').text(`Batch existing dipilih. Stok saat ini ${formatNumber(batch.qty)}. ${batch.expired ? 'ED ' + batch.expired : 'Tanpa ED'}`);
            } else if (!inbound && batch.id) {
                $('#mutasiBatchHelp').text(`Stok batch tersedia ${formatNumber(batch.qty)}. ${batch.expired ? 'ED ' + batch.expired : ''}`);
            }

            if (!qty) {
                $('#mutasiFooterSummary').text(meta.footer);
                return;
            }

            if (inbound && !batch.id && !$('#mutasi_no_batch').val()?.trim()) {
                $('#mutasiFooterSummary').text('Pilih batch existing atau isi nomor batch baru sebelum menyimpan.');
                return;
            }

            if (!inbound && !batch.id) {
                $('#mutasiFooterSummary').text('Pilih batch aktif sebelum menyimpan mutasi keluar.');
                return;
            }

            $('#mutasiFooterSummary').text(`${formatNumber(qty)} ${stockLabel} akan dicatat dalam satuan stok dasar.`);
        }

        function setMutationMode(mode) {
            const meta = mutationModes[mode] || mutationModes.masuk;
            const wasInbound = isInboundMutation();

            $('#jenis_mutasi').val(mode);
            $('.stock-mutation-mode-btn')
                .removeClass('is-active')
                .attr('aria-pressed', 'false');
            $(`.stock-mutation-mode-btn[data-mutasi="${mode}"]`)
                .addClass('is-active')
                .attr('aria-pressed', 'true');

            $('#mutasiModeSubtitle').text(meta.subtitle);
            $('#mutasiImpactTitle').text(meta.title);
            $('#mutasiImpactText').text(meta.text);
            $('#mutasiImpactCard .stock-mutation-impact-icon').html(`<i class="mdi ${meta.icon}"></i>`);
            $('#mutasiBatchModeText').text(meta.batchText);
            $('#mutasiQtyHelp').text(meta.qtyHelp);

            $('#submitMutasiStok')
                .removeClass('btn-primary btn-danger btn-warning')
                .addClass(meta.submitClass)
                .html(`<i class="mdi mdi-content-save-outline"></i> ${meta.submitText}`);

            toggleMutationFields();

            if ($('#mutasi_obat_id').val() && wasInbound !== isInboundMutation()) {
                loadMutationBatches($('#mutasi_obat_id').val());
            }

            updateMutationPreview();
        }

        function toggleMutationFields() {
            const inbound = isInboundMutation();
            const batch = selectedBatchMeta();
            const usingExistingBatch = inbound && Boolean(batch.id);

            $('.stock-in-field').toggleClass('d-none', !inbound);
            $('.stock-batch-select-field').toggleClass('d-none', false);
            updateBatchPlaceholder();
            $('#mutasi_no_batch, #mutasi_expired_date')
                .prop('required', inbound && !usingExistingBatch)
                .prop('disabled', !inbound || usingExistingBatch);
            $('#mutasi_harga_beli, #mutasi_harga_jual, #mutasi_alasan_harga').prop('disabled', !inbound);
            $('#mutasi_stok_batch_id').prop('required', !inbound).prop('disabled', false);

            if (inbound) {
                if (usingExistingBatch) {
                    syncInboundBatchFields();
                } else {
                    $('#mutasi_no_batch, #mutasi_expired_date').prop('disabled', false);
                }

                $('#mutasiBatchHelp').text('Pilih batch existing, atau kosongkan untuk mengisi batch baru.');
            } else if (!$('#mutasi_obat_id').val()) {
                $('#mutasiBatchHelp').text('Pilih obat terlebih dahulu.');
            }
        }

        function loadMutationBatches(obatId) {
            const select = $('#mutasi_stok_batch_id');
            const emptyLabel = isInboundMutation() ? 'Batch baru / isi manual' : 'Pilih batch';
            select.empty().append(`<option value="">${emptyLabel}</option>`).trigger('change');

            if (!obatId) {
                $('#mutasiBatchHelp').text('Pilih obat terlebih dahulu.');
                updateMutationPreview();
                return;
            }

            $.get(`${batchOptionsBaseUrl}/${obatId}`, {
                include_empty: isInboundMutation() ? 1 : 0
            }, function(response) {
                (response || []).forEach(function(batch) {
                    const option = new Option(batch.text, batch.id, false, false);
                    $(option)
                        .attr('data-qty', batch.qty)
                        .attr('data-harga', batch.harga_beli)
                        .attr('data-harga-jual', batch.harga_jual)
                        .attr('data-ppn', batch.ppn)
                        .attr('data-batch', batch.no_batch)
                        .attr('data-expired', batch.expired_date || '');
                    select.append(option);
                });
                $('#mutasiBatchHelp').text((response || []).length
                    ? (isInboundMutation() ? 'Pilih batch existing, atau kosongkan untuk mengisi batch baru.' : 'Batch tersedia untuk mutasi keluar.')
                    : (isInboundMutation() ? 'Belum ada batch. Isi nomor batch baru.' : 'Belum ada batch aktif untuk obat ini.'));
                updateMutationPreview();
            });
        }

        $('.stock-mutation-mode-btn').on('click', function() {
            setMutationMode($(this).data('mutasi'));
        });

        $('#mutasi_obat_id').on('change', function() {
            loadMutationBatches(this.value);
            const selectedText = $(this).find(':selected').text();
            $('#mutasiObatHelp').text(this.value ? selectedText : 'Pilih obat untuk menampilkan batch aktif.');
            updateMutationPreview();
        });

        $('#mutasi_stok_batch_id').on('change', function() {
            syncInboundBatchFields();
            toggleMutationFields();
            updateMutationPreview();
        });
        $('#mutasi_qty, #mutasi_harga_beli, #mutasi_harga_jual, #mutasi_no_batch').on('input', updateMutationPreview);

        $('#mutasiStokModal').on('show.bs.modal', function() {
            $('#mutasiStokForm')[0].reset();
            $('#mutasi_obat_id').val(null).trigger('change');
            $('#mutasi_stok_batch_id').empty().append('<option value="">Pilih batch</option>').trigger('change');
            $('#tanggal_mutasi').val(moment().format('YYYY-MM-DD'));
            $('#mutasiObatHelp').text('Pilih obat untuk menampilkan batch aktif.');
            $('#mutasiBatchHelp').text('Pilih obat terlebih dahulu.');
            setMutationMode('masuk');
            updateMutationPreview();
        });

        $('#mutasiStokForm').on('submit', function(e) {
            e.preventDefault();

            const submit = $('#submitMutasiStok');
            const normalHtml = submit.html();
            submit.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');

            $.ajax({
                url: '{{ route("stok.mutasi.store") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#mutasiStokModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                    StockTable.ajax.reload(null, false);
                    BatchTable.ajax.reload(null, false);
                    RiwayatHargaTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const firstError = Object.values(errors)[0]?.[0];
                    Swal.fire('Gagal', firstError || xhr.responseJSON?.message || 'Mutasi stok gagal disimpan.', 'error');
                },
                complete: function() {
                    submit.prop('disabled', false).html(normalHtml);
                }
            });
        });
    });
</script>
