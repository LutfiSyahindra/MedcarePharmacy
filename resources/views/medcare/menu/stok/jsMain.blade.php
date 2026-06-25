<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        let stockAlertStatus = '';
        let batchObatId = '';
        let batchExpiryStatus = '';
        let expiredWarningDays = $('#expiredWarningDays').val() || 90;
        const batchOptionsBaseUrl = '{{ url("medcare/menu/stok/stok/batch-options") }}';

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

        function updateStockFilterSnapshot() {
            const search = $('#stockSearch').val();
            const edDays = $('#expiredWarningDays').val() || 90;
            $('#stockActiveFilterText').text(alertLabels[stockAlertStatus] || 'Semua stok');
            $('#stockActiveSearchText').text(search ? `Pencarian: ${search}` : `Warning ED ${edDays} hari`);
        }

        function updateStockSummary(summary) {
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
            updateStockFilterSnapshot();
            updateLastSync('#stockLastSync');
        }

        function updateBatchSummary(summary) {
            const riskCount = (Number(summary.expired) || 0) + (Number(summary.akan_expired) || 0);
            $('#batchTotalCount').text(formatNumber(summary.total_batch));
            $('#batchTotalQty').text(formatNumber(summary.total_stok));
            $('#batchRiskCount').text(formatNumber(riskCount));
        }

        let StockTable = $('#tableStock').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            ajax: {
                url: '{{ route("stok.table") }}',
                type: 'GET',
                data: function(request) {
                    request.alert_status = stockAlertStatus;
                    request.expired_days = expiredWarningDays;
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
                    data: null,
                    render: row => itemCell(row)
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
                    data: null,
                    render: row => statusBadge(row.status, row.status_label)
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
                $('#stockVisibleInfo').text(`${formatNumber(info.recordsDisplay)} data`);
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

        let searchTimer;
        $('#stockSearch').on('input', function() {
            const value = this.value;
            $(this).closest('.stock-search').toggleClass('has-value', Boolean(value));
            updateStockFilterSnapshot();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => StockTable.search(value).draw(), 250);
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

        $('#refreshStockTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            StockTable.ajax.reload(null, false);
            BatchTable.ajax.reload(null, false);
        });

        $('#tableStock, #tableBatchStock').on('xhr.dt', function() {
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
                batch: selected.data('batch') || '',
                expired: selected.data('expired') || ''
            };
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

            if (!inbound && batch.id) {
                $('#mutasiBatchHelp').text(`Stok batch tersedia ${formatNumber(batch.qty)}. ${batch.expired ? 'ED ' + batch.expired : ''}`);
            }

            if (!qty) {
                $('#mutasiFooterSummary').text(meta.footer);
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
            updateMutationPreview();
        }

        function toggleMutationFields() {
            const inbound = isInboundMutation();
            $('.stock-in-field').toggleClass('d-none', !inbound);
            $('.stock-out-field').toggleClass('d-none', inbound);
            $('#mutasi_no_batch, #mutasi_expired_date').prop('required', inbound).prop('disabled', !inbound);
            $('#mutasi_harga_beli').prop('disabled', !inbound);
            $('#mutasi_stok_batch_id').prop('required', !inbound).prop('disabled', inbound);

            if (inbound) {
                $('#mutasi_stok_batch_id').val('').trigger('change.select2');
                $('#mutasiBatchHelp').text('Batch aktif tidak wajib untuk mutasi masuk.');
            } else if (!$('#mutasi_obat_id').val()) {
                $('#mutasiBatchHelp').text('Pilih obat terlebih dahulu.');
            }
        }

        function loadMutationBatches(obatId) {
            const select = $('#mutasi_stok_batch_id');
            select.empty().append('<option value="">Pilih batch</option>').trigger('change');

            if (!obatId) {
                $('#mutasiBatchHelp').text('Pilih obat terlebih dahulu.');
                updateMutationPreview();
                return;
            }

            $.get(`${batchOptionsBaseUrl}/${obatId}`, function(response) {
                (response || []).forEach(function(batch) {
                    const option = new Option(batch.text, batch.id, false, false);
                    $(option)
                        .attr('data-qty', batch.qty)
                        .attr('data-harga', batch.harga_beli)
                        .attr('data-batch', batch.no_batch)
                        .attr('data-expired', batch.expired_date || '');
                    select.append(option);
                });
                $('#mutasiBatchHelp').text((response || []).length ? 'Batch tersedia untuk mutasi keluar.' : 'Belum ada batch aktif untuk obat ini.');
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

        $('#mutasi_stok_batch_id').on('change', updateMutationPreview);
        $('#mutasi_qty, #mutasi_harga_beli').on('input', updateMutationPreview);

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
