<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        let dateStart = '';
        let dateEnd = '';
        let kartuDatePicker = null;
        const queryParams = new URLSearchParams(window.location.search);
        const initialObatId = queryParams.get('obat_id') || '';
        const batchOptionsBaseUrl = '{{ url("medcare/menu/stok/stok/batch-options") }}';

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

        function formatDateTime(value) {
            if (!value) return '-';
            const date = moment(value);
            return date.isValid() ? date.format('DD-MM-YYYY HH:mm') : value;
        }

        const mutationLabels = {
            '': 'Semua mutasi',
            masuk: 'Obat Masuk',
            keluar: 'Obat Keluar',
            expired: 'Obat Expired',
            penyesuaian_masuk: 'Penyesuaian Masuk',
            penyesuaian_keluar: 'Penyesuaian Keluar',
            pembatalan_penerimaan: 'Pembatalan Penerimaan',
            saldo_awal: 'Saldo Awal'
        };

        function mutationIcon(type) {
            return {
                masuk: 'mdi-arrow-down-bold-circle-outline',
                keluar: 'mdi-arrow-up-bold-circle-outline',
                expired: 'mdi-calendar-remove-outline',
                penyesuaian_masuk: 'mdi-plus-circle-outline',
                penyesuaian_keluar: 'mdi-minus-circle-outline',
                pembatalan_penerimaan: 'mdi-cancel',
                saldo_awal: 'mdi-database-plus-outline'
            } [type] || 'mdi-swap-horizontal';
        }

        function mutationBadge(type, label) {
            return `
                <span class="stock-status is-${escapeHtml(type)}">
                    <i class="mdi ${mutationIcon(type)}"></i>
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

        function flowNumberCell(value, satuan, direction) {
            const numeric = Number(value) || 0;
            const icon = direction === 'in' ? 'mdi-arrow-down-bold' : 'mdi-arrow-up-bold';

            return `
                <span class="stock-flow-number is-${direction} ${numeric ? '' : 'is-empty'}">
                    <i class="mdi ${icon}"></i>
                    <strong>${numeric ? formatNumber(numeric) : '-'}</strong>
                    ${numeric ? `<small>${escapeHtml(satuan)}</small>` : ''}
                </span>
            `;
        }

        function referenceCell(value) {
            return `
                <span class="stock-reference-pill">
                    <i class="mdi mdi-file-document-outline"></i>
                    ${escapeHtml(value || '-')}
                </span>
            `;
        }

        function noteCell(value) {
            return `<span class="stock-note-cell">${escapeHtml(value || '-')}</span>`;
        }

        function updateLastSync(selector) {
            $(selector).text(`Update ${moment().format('HH:mm')}`);
        }

        function updateKartuFilterSummary() {
            const jenis = $('#kartuJenisFilter').val() || '';
            const obatText = $('#kartuObatFilter').find(':selected').text();
            const batchText = $('#kartuBatchFilter').find(':selected').text();
            const search = $('#kartuSearch').val();
            const filterPieces = [mutationLabels[jenis] || 'Mutasi terfilter'];

            if ($('#kartuObatFilter').val()) filterPieces.push(obatText);
            if ($('#kartuBatchFilter').val()) filterPieces.push(batchText);
            if (search) filterPieces.push(`Cari: ${search}`);

            $('#kartuFilterSummary').text(filterPieces.join(' | '));
            $('#kartuDateSummary').text(dateStart && dateEnd ? `${moment(dateStart).format('DD MMM YYYY')} - ${moment(dateEnd).format('DD MMM YYYY')}` : 'Semua tanggal');
        }

        function syncKartuJenisChips() {
            const value = $('#kartuJenisFilter').val() || '';
            $('.stock-ledger-chip-group .stock-chip')
                .removeClass('is-active')
                .filter(`[data-kartu-jenis="${value}"]`)
                .addClass('is-active');
        }

        function updateSummary(summary) {
            const masuk = Number(summary.total_masuk) || 0;
            const keluar = Number(summary.total_keluar) || 0;
            const expired = Number(summary.total_expired) || 0;
            const totalFlow = Math.max(1, masuk + keluar);
            const net = masuk - keluar;

            $('#kartuMutasiCount').text(formatNumber(summary.jumlah_mutasi));
            $('#kartuTotalMasuk').text(formatNumber(masuk));
            $('#kartuTotalKeluar').text(formatNumber(keluar));
            $('#kartuTotalExpired').text(formatNumber(expired));
            $('#kartuSaldoTercatat').text(formatNumber(summary.saldo_tercatat));
            $('#kartuMasukInsight').text(formatNumber(masuk));
            $('#kartuKeluarInsight').text(formatNumber(keluar));
            $('#kartuExpiredInsight').text(formatNumber(expired));
            $('#kartuNetMutasi').text(`${net >= 0 ? '+' : ''}${formatNumber(net)} net`);
            $('#kartuNetMutasi')
                .removeClass('is-safe is-warning is-danger')
                .addClass(net >= 0 ? 'is-safe' : 'is-danger');
            $('#kartuFlowText').text(`${formatNumber(masuk)} masuk dibanding ${formatNumber(keluar)} keluar`);
            $('#kartuFlowIn').css('width', `${Math.round((masuk / totalFlow) * 100)}%`);
            $('#kartuFlowOut').css('width', `${Math.round((keluar / totalFlow) * 100)}%`);
            updateKartuFilterSummary();
            updateLastSync('#kartuLastSync');
        }

        $('#kartuObatFilter').select2({
            placeholder: 'Semua obat',
            allowClear: true,
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

        $('#kartuBatchFilter').select2({
            placeholder: 'Semua batch',
            allowClear: true,
            width: '100%'
        });

        function loadBatchOptions(obatId, selectedBatchId = '') {
            const batchSelect = $('#kartuBatchFilter');
            batchSelect.empty().append('<option value="">Semua batch</option>').prop('disabled', !obatId).trigger('change');

            if (!obatId) {
                return;
            }

            $.get(`${batchOptionsBaseUrl}/${obatId}`, function(response) {
                (response || []).forEach(function(batch) {
                    batchSelect.append(new Option(batch.text, batch.id, false, String(batch.id) === String(selectedBatchId)));
                });
                batchSelect.prop('disabled', false).trigger('change.select2');
            });
        }

        if (initialObatId) {
            $.get('{{ route("stok.obatOptions") }}', {
                id: initialObatId
            }, function(response) {
                const obat = (response || [])[0];
                if (!obat) return;

                $('#kartuObatFilter')
                    .append(new Option(obat.text, obat.id, true, true))
                    .trigger('change.select2');
                loadBatchOptions(obat.id);
                KartuTable.ajax.reload();
            });
        }

        kartuDatePicker = flatpickr('#kartuDateRange', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            locale: {
                rangeSeparator: ' - '
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    dateStart = moment(selectedDates[0]).format('YYYY-MM-DD');
                    dateEnd = moment(selectedDates[1]).format('YYYY-MM-DD');
                    updateKartuFilterSummary();
                    KartuTable.ajax.reload();
                }
            },
            onClose: function(selectedDates) {
                if (selectedDates.length === 1) {
                    this.clear();
                    dateStart = '';
                    dateEnd = '';
                    updateKartuFilterSummary();
                }
            }
        });

        let KartuTable = $('#tableKartuStok').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [
                [1, 'desc']
            ],
            ajax: {
                url: '{{ route("kartuStok.table") }}',
                type: 'GET',
                data: function(request) {
                    request.obat_id = $('#kartuObatFilter').val() || '';
                    request.stok_batch_id = $('#kartuBatchFilter').val() || '';
                    request.jenis_mutasi = $('#kartuJenisFilter').val() || '';
                    request.date_start = dateStart;
                    request.date_end = dateEnd;
                },
                dataSrc: function(response) {
                    updateSummary(response.summary || {});
                    return response.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'tanggal_mutasi',
                    render: data => `<span class="stock-date-time"><i class="mdi mdi-calendar-clock-outline"></i><strong>${formatDateTime(data)}</strong></span>`
                },
                {
                    data: null,
                    render: row => itemCell(row)
                },
                {
                    data: null,
                    render: row => `
                        <span class="stock-item">
                            <strong>${escapeHtml(row.no_batch)}</strong>
                            <small>ED ${escapeHtml(row.expired_date || '-')}</small>
                        </span>
                    `
                },
                {
                    data: null,
                    render: row => mutationBadge(row.jenis_mutasi, row.jenis_label)
                },
                {
                    data: 'qty_masuk',
                    render: (data, type, row) => flowNumberCell(data, row.satuan, 'in')
                },
                {
                    data: 'qty_keluar',
                    render: (data, type, row) => flowNumberCell(data, row.satuan, 'out')
                },
                {
                    data: 'saldo_batch',
                    render: (data, type, row) => `<span class="stock-number">${formatNumber(data)} ${escapeHtml(row.satuan)}</span>`
                },
                {
                    data: 'saldo_total',
                    render: (data, type, row) => `<span class="stock-number">${formatNumber(data)} ${escapeHtml(row.satuan)}</span>`
                },
                {
                    data: 'harga_beli',
                    render: data => `<span class="stock-money"><i class="mdi mdi-cash"></i>${formatCurrency(data)}</span>`
                },
                {
                    data: 'nomor_referensi',
                    render: data => referenceCell(data)
                },
                {
                    data: 'user',
                    render: data => `<span class="stock-user-pill"><i class="mdi mdi-account-outline"></i>${escapeHtml(data || '-')}</span>`
                },
                {
                    data: 'keterangan',
                    render: data => noteCell(data)
                }
            ],
            columnDefs: [{
                targets: [0],
                className: 'text-center'
            }],
            rowCallback: function(row, data) {
                $(row)
                    .removeClass('is-row-masuk is-row-keluar is-row-expired is-row-penyesuaian_masuk is-row-penyesuaian_keluar is-row-pembatalan_penerimaan is-row-saldo_awal')
                    .addClass(`is-row-${data.jenis_mutasi}`);
            },
            drawCallback: function() {
                const info = this.api().page.info();
                $('#kartuVisibleInfo').text(`${formatNumber(info.recordsDisplay)} data`);
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat kartu stok...</span>',
                emptyTable: 'Belum ada mutasi stok.',
                zeroRecords: 'Mutasi yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let searchTimer;
        $('#kartuSearch').on('input', function() {
            const value = this.value;
            $(this).closest('.stock-search').toggleClass('has-value', Boolean(value));
            updateKartuFilterSummary();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => KartuTable.search(value).draw(), 250);
        });

        $('#clearKartuSearch').on('click', function() {
            $('#kartuSearch').val('').trigger('input').focus();
        });

        $('#kartuObatFilter').on('change', function() {
            loadBatchOptions(this.value || '');
            updateKartuFilterSummary();
            KartuTable.ajax.reload();
        });

        $('#kartuBatchFilter, #kartuJenisFilter').on('change', function() {
            syncKartuJenisChips();
            updateKartuFilterSummary();
            KartuTable.ajax.reload();
        });

        $('.stock-ledger-chip-group .stock-chip').on('click', function() {
            $('#kartuJenisFilter').val($(this).data('kartu-jenis') || '').trigger('change');
        });

        $('#clearKartuFilter').on('click', function() {
            $('#kartuObatFilter').val(null).trigger('change');
            $('#kartuBatchFilter').empty().append('<option value="">Semua batch</option>').prop('disabled', true).trigger('change');
            $('#kartuJenisFilter').val('');
            $('#kartuSearch').val('').trigger('input');
            dateStart = '';
            dateEnd = '';
            kartuDatePicker.clear();
            syncKartuJenisChips();
            updateKartuFilterSummary();
            KartuTable.ajax.reload();
        });

        $('#refreshKartuTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            KartuTable.ajax.reload(null, false);
        });

        $('#tableKartuStok').on('xhr.dt', function() {
            $('#refreshKartuTable').removeClass('is-loading').prop('disabled', false);
        });

        syncKartuJenisChips();
        updateKartuFilterSummary();
    });
</script>
