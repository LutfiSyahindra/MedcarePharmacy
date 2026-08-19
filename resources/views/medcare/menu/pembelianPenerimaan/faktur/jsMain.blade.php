<script src="{{ asset("assets/vendors/moment/moment.min.js") }}"></script>
<script>
    $(document).ready(function() {
        const routes = {
            table: '{{ route("faktur.table") }}',
            show: '{{ route("faktur.show", ":id") }}',
            update: '{{ route("faktur.update", ":id") }}',
            markPaid: '{{ route("faktur.markPaid", ":id") }}',
            resetPayment: '{{ route("faktur.resetPayment", ":id") }}'
        };

        let invoiceDateStart = moment().startOf('month').format('YYYY-MM-DD');
        let invoiceDateEnd = moment().endOf('month').format('YYYY-MM-DD');
        let invoicePaymentFilter = '';
        let invoiceDueFilter = '';
        let invoiceDatePicker = null;
        let paymentBase = {};
        let currentInvoiceId = null;

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        flatpickr('#paymentTanggalFaktur', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        flatpickr('#paymentTanggalJatuhTempo', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        function routeUrl(route, id) {
            return route.replace(':id', id);
        }

        function formatRupiah(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(Number(value) || 0);
        }

        function formatDecimal(value, minimumFractionDigits = 0, maximumFractionDigits = 2) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits,
                maximumFractionDigits
            });
        }

        function formatMoneyInput(value) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        function moneyValue(value) {
            if (typeof value === 'number') return value;

            let clean = String(value || '').replace(/[^0-9,.-]/g, '');

            if (clean.includes(',') && clean.includes('.')) {
                clean = clean.replace(/\./g, '').replace(',', '.');
            } else if (clean.includes(',')) {
                clean = clean.replace(',', '.');
            } else if (/^\d{1,3}(\.\d{3})+$/.test(clean)) {
                clean = clean.replace(/\./g, '');
            } else if ((clean.match(/\./g) || []).length > 1) {
                clean = clean.replace(/\./g, '');
            }

            return Math.max(0, Number(clean) || 0);
        }

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

        function formatDateDisplay(value) {
            let date = moment(value);
            return date.isValid() ? date.format('DD-MM-YYYY') : (value || '-');
        }

        function formatDueDate(value) {
            return value ? formatDateDisplay(value) : 'Tanpa jatuh tempo';
        }

        function getInitials(name) {
            return String(name || '-')
                .split(/\s+/)
                .filter(Boolean)
                .slice(0, 2)
                .map(part => part.charAt(0))
                .join('')
                .toUpperCase() || '-';
        }

        function paymentMeta(status) {
            status = String(status || '').toLowerCase();

            if (status === 'lunas') {
                return {
                    className: 'is-lunas',
                    icon: 'mdi-check-decagram-outline',
                    label: 'Lunas'
                };
            }

            if (status === 'sebagian') {
                return {
                    className: 'is-sebagian',
                    icon: 'mdi-circle-half-full',
                    label: 'Sebagian'
                };
            }

            return {
                className: 'is-belum-dibayar',
                icon: 'mdi-progress-clock',
                label: 'Belum Dibayar'
            };
        }

        function dueMeta(state, label) {
            state = String(state || '').replaceAll('_', '-');
            const meta = {
                lunas: ['is-lunas', 'mdi-check-circle-outline'],
                overdue: ['is-overdue', 'mdi-alert-circle-outline'],
                'due-soon': ['is-due-soon', 'mdi-calendar-clock'],
                'not-due': ['is-not-due', 'mdi-calendar-check-outline'],
                'no-due': ['is-no-due', 'mdi-calendar-remove-outline'],
                cancelled: ['is-cancelled', 'mdi-cancel']
            } [state] || ['is-no-due', 'mdi-calendar-blank-outline'];

            return {
                className: meta[0],
                icon: meta[1],
                label: label || 'Tanpa Tempo'
            };
        }

        function paymentBadge(status) {
            const meta = paymentMeta(status);
            return `
                <span class="invoice-payment-badge ${meta.className}">
                    <i class="mdi ${meta.icon}"></i>
                    ${meta.label}
                </span>
            `;
        }

        function dueBadge(state, label) {
            const meta = dueMeta(state, label);
            return `
                <span class="invoice-due-status ${meta.className}">
                    <i class="mdi ${meta.icon}"></i>
                    ${escapeHtml(meta.label)}
                </span>
            `;
        }

        function updateInvoiceSummary(summary = {}) {
            const totalValue = Number(summary.total_value || 0);
            const paidValue = Number(summary.paid_value || 0);
            const remainingDebt = Number(summary.remaining_debt || 0);
            const paidRatio = totalValue > 0 ? Math.min(100, Math.round((paidValue / totalValue) * 100)) : 0;
            const openCount = Number(summary.unpaid || 0) + Number(summary.partial || 0);

            $('#invoiceTotalCount, #invoiceAllFilterCount').text(Number(summary.total || 0).toLocaleString('id-ID'));
            $('#invoiceUnpaidFilterCount').text(Number(summary.unpaid || 0).toLocaleString('id-ID'));
            $('#invoicePartialFilterCount').text(Number(summary.partial || 0).toLocaleString('id-ID'));
            $('#invoicePaidCount, #invoicePaidFilterCount').text(Number(summary.paid || 0).toLocaleString('id-ID'));
            $('#invoiceOverdueCount').text(Number(summary.overdue || 0).toLocaleString('id-ID'));
            $('#invoiceDueSoonCount').text(Number(summary.due_soon || 0).toLocaleString('id-ID'));
            $('#invoiceOpenCount').text(openCount.toLocaleString('id-ID'));
            $('#invoiceRemainingDebt, #invoiceHeaderOutstanding').text(formatRupiah(remainingDebt));
            $('#invoicePaidValue').text(formatRupiah(paidValue));
            $('#invoicePaidRatio').text(`${paidRatio}% dari total nilai aktif`);
            $('#invoiceHeaderHealth').text(`${openCount.toLocaleString('id-ID')} faktur perlu tindak lanjut`);
            $('#invoiceHealthMeter').css('width', `${paidRatio}%`);
        }

        const FakturTable = $('#tableFaktur').DataTable({
            processing: true,
            ajax: {
                url: routes.table,
                type: 'GET',
                data: function(request) {
                    request.date_start = invoiceDateStart;
                    request.date_end = invoiceDateEnd;
                    request.payment_status = invoicePaymentFilter;
                    request.due_status = invoiceDueFilter;
                },
                dataSrc: function(response) {
                    updateInvoiceSummary(response.summary || {});
                    return response.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: data => `<span class="purchase-row-number">${escapeHtml(data)}</span>`
                },
                {
                    data: null,
                    render: row => dueBadge(row.due_state, row.due_label)
                },
                {
                    data: null,
                    render: row => `
                        <span class="purchase-po-number">
                            <i class="mdi mdi-receipt-text-check-outline"></i>
                            ${escapeHtml(row.nomor_faktur)}
                        </span>
                    `
                },
                {
                    data: null,
                    render: row => `
                        <span class="invoice-reference-stack">
                            <strong>${escapeHtml(row.nomor_penerimaan)}</strong>
                            <small>${escapeHtml(row.no_po)}</small>
                        </span>
                    `
                },
                {
                    data: 'supplier',
                    render: data => `<span class="purchase-badge is-distributor"><i class="mdi mdi-truck-delivery-outline"></i>${escapeHtml(data)}</span>`
                },
                {
                    data: 'branch',
                    render: data => `<span class="purchase-badge is-branch"><i class="mdi mdi-source-branch"></i>${escapeHtml(data)}</span>`
                },
                {
                    data: 'tanggal_faktur_iso',
                    render: data => `<span class="purchase-date"><i class="mdi mdi-calendar-blank-outline"></i>${formatDateDisplay(data)}</span>`
                },
                {
                    data: 'tanggal_jatuh_tempo_iso',
                    render: data => `<span class="purchase-date"><i class="mdi mdi-calendar-alert"></i>${formatDueDate(data)}</span>`
                },
                {
                    data: 'total_faktur_value',
                    render: data => `<span class="purchase-money"><i class="mdi mdi-cash"></i>${formatRupiah(data)}</span>`
                },
                {
                    data: null,
                    render: row => `
                        <span class="invoice-money-stack">
                            <strong>${formatRupiah(row.jumlah_dibayar_value)}</strong>
                            <small>Sisa ${formatRupiah(row.sisa_hutang_value)}</small>
                        </span>
                    `
                },
                {
                    data: 'status_pembayaran',
                    render: data => paymentBadge(data)
                },
                {
                    data: null,
                    render: row => `
                        <span class="invoice-progress-stack">
                            <strong>${Number(row.payment_progress || 0)}%</strong>
                            <span class="invoice-row-meter"><span style="width: ${Number(row.payment_progress || 0)}%"></span></span>
                        </span>
                    `
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    render: data => data || ''
                }
            ],
            columnDefs: [{
                targets: [0, 1, 10, 11, 12],
                className: 'text-center'
            }],
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat faktur...</span>',
                emptyTable: 'Belum ada faktur pembelian.',
                zeroRecords: 'Faktur yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let invoiceSearchTimer;

        $('#searchFaktur').on('input', function() {
            const searchValue = this.value;
            $(this).closest('.purchase-search').toggleClass('has-value', Boolean(searchValue));
            clearTimeout(invoiceSearchTimer);
            invoiceSearchTimer = setTimeout(function() {
                FakturTable.search(searchValue).draw();
            }, 250);
        });

        $('#clearInvoiceSearch').on('click', function() {
            $('#searchFaktur').val('').trigger('input').focus();
        });

        $('.invoice-filter-chip').on('click', function() {
            $('.invoice-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            invoicePaymentFilter = $(this).data('payment') || '';
            FakturTable.ajax.reload();
        });

        $('.invoice-due-chip').on('click', function() {
            $('.invoice-due-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            invoiceDueFilter = $(this).data('due') || '';
            FakturTable.ajax.reload();
        });

        function formatDateParameter(date) {
            if (!date) return '';
            return moment(date).format('YYYY-MM-DD');
        }

        function applyInvoiceDateRange(startDate, endDate) {
            invoiceDateStart = formatDateParameter(startDate);
            invoiceDateEnd = formatDateParameter(endDate);
            $('#invoiceDateRange').closest('.purchase-date-input').addClass('has-value');
            FakturTable.ajax.reload();
        }

        invoiceDatePicker = flatpickr('#invoiceDateRange', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            defaultDate: [invoiceDateStart, invoiceDateEnd],
            locale: {
                rangeSeparator: ' - '
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    $('#invoiceDatePreset').val('');
                    applyInvoiceDateRange(selectedDates[0], selectedDates[1]);
                }
            },
            onClose: function(selectedDates) {
                if (selectedDates.length === 1) this.clear();
            }
        });

        $('#invoiceDatePreset').val('this_month');
        $('#invoiceDateRange').closest('.purchase-date-input').addClass('has-value');

        $('#clearInvoiceDateRange').on('click', function() {
            invoiceDateStart = '';
            invoiceDateEnd = '';
            invoiceDatePicker.clear();
            $('#invoiceDatePreset').val('');
            $('#invoiceDateRange').closest('.purchase-date-input').removeClass('has-value');
            FakturTable.ajax.reload();
        });

        $('#invoiceDatePreset').on('change', function() {
            const preset = this.value;
            if (!preset) return;

            const today = moment().startOf('day');
            let startDate = today.clone();
            let endDate = today.clone();

            if (preset === '7days') startDate.subtract(6, 'days');
            if (preset === '30days') startDate.subtract(29, 'days');
            if (preset === 'this_month') {
                startDate.startOf('month');
                endDate.endOf('month');
            }

            invoiceDatePicker.setDate([startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD')], false);
            applyInvoiceDateRange(startDate, endDate);
        });

        $('#invoicePageLength').on('change', function() {
            FakturTable.page.len(Number(this.value)).draw();
        });

        $('#refreshInvoiceTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            FakturTable.ajax.reload(null, false);
        });

        $('#tableFaktur').on('xhr.dt', function() {
            $('#refreshInvoiceTable').removeClass('is-loading').prop('disabled', false);
        });

        function fetchFaktur(id) {
            return $.get(routeUrl(routes.show, id));
        }

        function renderDetail(payload) {
            const header = payload.header || {};
            const details = payload.details || [];
            currentInvoiceId = header.id;

            $('#fakturDetailModalLabel').text(`Detail Faktur ${header.nomor_faktur || '-'}`);
            $('#fakturDetailSubtitle').text(`${header.supplier || '-'} - ${header.branch || '-'}`);
            $('#fakturDetailPaymentStatus').html(paymentBadge(header.status_pembayaran));
            $('#detailInvoiceNumber').text(header.nomor_faktur || '-');
            $('#detailInvoiceSupplier').text(header.supplier || '-');
            $('#detailInvoiceTotal').text(formatRupiah(header.total_faktur));
            $('#detailInvoiceDate').text(`Tanggal faktur ${formatDateDisplay(header.tanggal_faktur)}`);
            $('#detailInvoiceDebt').text(formatRupiah(header.sisa_hutang));
            $('#detailInvoiceDueDate').text(`Jatuh tempo ${formatDueDate(header.tanggal_jatuh_tempo)}`);
            $('#detailInvoiceDue').html(dueBadge(header.due_state, header.due_label));
            $('#detailInvoiceProgressText').text(`${Number(header.payment_progress || 0)}%`);
            $('#detailInvoiceProgressMeter').css('width', `${Number(header.payment_progress || 0)}%`);
            $('#detailReceiptNumber').text(header.nomor_penerimaan || '-');
            $('#detailPoNumber').text(header.no_po || '-');
            $('#detailDeliveryNote').text(header.nomor_surat_jalan || '-');
            $('#detailDocumentDueDate').text(formatDueDate(header.tanggal_jatuh_tempo));
            $('#detailInvoiceBranch').text(header.branch || '-');
            $('#detailSubtotal').text(formatRupiah(header.subtotal));
            $('#detailDiscount').text(formatRupiah(header.diskon));
            $('#detailTax').text(formatRupiah(header.pajak));
            $('#detailOtherCost').text(formatRupiah(header.biaya_lain));
            $('#detailPaidAmount').text(formatRupiah(header.jumlah_dibayar));
            $('#detailItemSummary').text(`${Number(header.total_barang || details.length).toLocaleString('id-ID')} item / ${formatDecimal(header.total_qty, 0, 2)} qty`);
            $('#detailInvoiceNote').text(header.catatan || '-');
            $('#editInvoiceFromDetail').toggle(header.status_penerimaan !== 'cancelled').data('id', header.id);

            const rows = details.map(function(item) {
                const conversionText = Number(item.konversi_satuan || 1) > 1 ?
                    `<small>${formatDecimal(item.qty_diterima_stok, 0, 2)} ${escapeHtml(item.satuan_stok || '-')}</small>` :
                    '';

                return `
                    <tr>
                        <td>
                            <strong>${escapeHtml(item.nama_obat)}</strong>
                            <small class="d-block text-muted">${escapeHtml(item.kode_obat)} - ${escapeHtml(item.satuan_beli || '-')}</small>
                        </td>
                        <td>
                            <strong>${formatDecimal(item.qty_diterima, 0, 2)} ${escapeHtml(item.satuan_beli || '')}</strong>
                            ${conversionText}
                        </td>
                        <td>
                            <strong>${escapeHtml(item.no_batch || '-')}</strong>
                            <small class="d-block text-muted">ED ${formatDateDisplay(item.expired_date)}</small>
                        </td>
                        <td>${formatRupiah(item.harga_beli)}</td>
                        <td>
                            <strong>D1 ${formatDecimal(item.diskon_1, 0, 2)}% · D2 ${formatDecimal(item.diskon_2, 0, 2)}% · D3 ${formatDecimal(item.diskon_3, 0, 2)}%</strong>
                            <small class="d-block text-muted">Efektif ${formatDecimal(item.diskon, 0, 2)}% / PPN ${formatDecimal(item.ppn, 0, 2)}%</small>
                            <small class="d-block text-muted">${formatRupiah(item.nilai_diskon)} / ${formatRupiah(item.nilai_ppn)}</small>
                        </td>
                        <td><strong>${formatRupiah(item.total)}</strong></td>
                    </tr>
                `;
            }).join('');

            $('#detailInvoiceItems').html(rows || `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Detail item belum tersedia.</td>
                </tr>
            `);
        }

        function setDateValue(selector, value) {
            const input = $(selector)[0];

            if (input?._flatpickr) {
                input._flatpickr.setDate(value || null, false, 'Y-m-d');
                return;
            }

            $(selector).val(value || '');
        }

        function setMoneyInput(selector, value) {
            $(selector).val(formatMoneyInput(value));
        }

        function renderPayment(payload) {
            const header = payload.header || {};
            currentInvoiceId = header.id;
            paymentBase = {
                subtotal: Number(header.subtotal || 0),
                diskon: Number(header.diskon || 0),
                pajak: Number(header.pajak || 0)
            };

            clearPaymentValidation();
            $('#faktur_id').val(header.id);
            $('#fakturPaymentModalLabel').text(`Kelola Faktur ${header.nomor_faktur || '-'}`);
            $('#fakturPaymentSubtitle').text(`${header.supplier || '-'} - ${header.nomor_penerimaan || '-'}`);
            $('#invoicePaymentStatusBadge').html(paymentBadge(header.status_pembayaran));
            $('#paymentNomorFaktur').val(header.nomor_faktur || '');
            setDateValue('#paymentTanggalFaktur', header.tanggal_faktur);
            setDateValue('#paymentTanggalJatuhTempo', header.tanggal_jatuh_tempo);
            setMoneyInput('#paymentBiayaLain', header.biaya_lain);
            setMoneyInput('#paymentJumlahDibayar', header.jumlah_dibayar);
            $('#paymentCatatan').val(header.catatan || '');
            $('#paymentSubtotal').text(formatRupiah(header.subtotal));
            $('#paymentDiscount').text(formatRupiah(header.diskon));
            $('#paymentTax').text(formatRupiah(header.pajak));
            updatePaymentPreview();
        }

        function updatePaymentPreview() {
            const biayaLain = moneyValue($('#paymentBiayaLain').val());
            const total = Math.max(0, paymentBase.subtotal - paymentBase.diskon + paymentBase.pajak + biayaLain);
            let paid = moneyValue($('#paymentJumlahDibayar').val());
            paid = Math.min(paid, total);
            const debt = Math.max(0, total - paid);
            const progress = total > 0 ? Math.min(100, Math.round((paid / total) * 100)) : 0;
            const status = total <= 0 || paid <= 0 ? 'belum_dibayar' : (paid >= total ? 'lunas' : 'sebagian');

            $('#paymentTotalFaktur, #paymentComputedTotal').text(formatRupiah(total));
            $('#paymentPaidAmount').text(formatRupiah(paid));
            $('#paymentRemainingDebt').text(formatRupiah(debt));
            $('#paymentProgressLabel').text(`${progress}%`);
            $('#paymentProgressAmount').text(`${formatRupiah(paid)} / ${formatRupiah(total)}`);
            $('#paymentProgressMeter').css('width', `${progress}%`);
            $('#paymentStatusReadonly').val(paymentMeta(status).label);
            $('#invoicePaymentStatusBadge').html(paymentBadge(status));

            return {
                total,
                paid,
                debt,
                status
            };
        }

        $('.invoice-money-field').on('input', updatePaymentPreview);

        $('.invoice-money-field').on('blur', function() {
            $(this).val(formatMoneyInput(moneyValue(this.value)));
            updatePaymentPreview();
        });

        $('.invoice-money-field').on('focus', function() {
            this.select();
        });

        $('.invoice-shortcut-btn').on('click', function() {
            const preset = $(this).data('payment-preset');
            const total = Math.max(0, paymentBase.subtotal - paymentBase.diskon + paymentBase.pajak + moneyValue($('#paymentBiayaLain').val()));
            let paid = 0;

            if (preset === 'half') paid = total / 2;
            if (preset === 'full') paid = total;

            setMoneyInput('#paymentJumlahDibayar', paid);
            updatePaymentPreview();
        });

        window.lihatFaktur = function(id) {
            fetchFaktur(id).done(function(payload) {
                renderDetail(payload);
                $('#fakturDetailModal').modal('show');
            });
        };

        window.editFaktur = function(id) {
            fetchFaktur(id).done(function(payload) {
                renderPayment(payload);
                $('#fakturPaymentModal').modal('show');
            });
        };

        $('#editInvoiceFromDetail').on('click', function() {
            const id = $(this).data('id');
            if (!id) return;

            $('#fakturDetailModal').modal('hide');
            window.editFaktur(id);
        });

        window.markFakturPaid = function(id) {
            Swal.fire({
                icon: 'question',
                title: 'Tandai faktur lunas?',
                text: 'Jumlah dibayar akan disamakan dengan total faktur.',
                showCancelButton: true,
                confirmButtonText: 'Ya, tandai lunas',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: routeUrl(routes.markPaid, id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire('Berhasil', response.message || 'Faktur ditandai lunas.', 'success');
                        FakturTable.ajax.reload(null, false);
                    },
                    error: handleAjaxError
                });
            });
        };

        window.resetFakturPayment = function(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Reset pembayaran faktur?',
                text: 'Jumlah dibayar akan kembali menjadi nol.',
                showCancelButton: true,
                confirmButtonText: 'Ya, reset',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: routeUrl(routes.resetPayment, id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire('Berhasil', response.message || 'Pembayaran faktur direset.', 'success');
                        FakturTable.ajax.reload(null, false);
                    },
                    error: handleAjaxError
                });
            });
        };

        $('#fakturPaymentForm').on('submit', function(e) {
            e.preventDefault();

            const id = $('#faktur_id').val();
            const preview = updatePaymentPreview();

            if (moneyValue($('#paymentJumlahDibayar').val()) > preview.total) {
                setMoneyInput('#paymentJumlahDibayar', preview.total);
            }

            const submitButton = $('#submitFakturPayment');
            submitButton.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');
            clearPaymentValidation();

            $.ajax({
                url: routeUrl(routes.update, id),
                type: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    $('#fakturPaymentModal').modal('hide');
                    Swal.fire('Berhasil', response.message || 'Faktur berhasil diperbarui.', 'success');
                    FakturTable.ajax.reload(null, false);
                },
                error: handleValidationError,
                complete: function() {
                    submitButton.prop('disabled', false).html('<i class="mdi mdi-content-save-outline"></i> Simpan Faktur');
                }
            });
        });

        function clearPaymentValidation() {
            const form = $('#fakturPaymentForm');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
        }

        function handleValidationError(xhr) {
            if (xhr.status !== 422 || !xhr.responseJSON?.errors) {
                handleAjaxError(xhr);
                return;
            }

            const errors = xhr.responseJSON.errors;

            Object.keys(errors).forEach(function(field) {
                const input = $(`[name="${field}"]`).first();
                input.addClass('is-invalid');
                input.closest('.invoice-field, .input-group').after(`<div class="invalid-feedback d-block">${escapeHtml(errors[field][0])}</div>`);
            });

            Swal.fire({
                icon: 'warning',
                title: 'Data belum lengkap',
                text: xhr.responseJSON.message || 'Periksa kembali field yang ditandai.'
            });
        }

        function handleAjaxError(xhr) {
            const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses faktur.';

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message
            });
        }
    });
</script>
