<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        let editMode = false;
        let returnDateStart = moment().startOf('month').format('YYYY-MM-DD');
        let returnDateEnd = moment().endOf('month').format('YYYY-MM-DD');
        let returnDatePicker = null;
        let compensationFilter = '';

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        flatpickr("#return-date", {
            dateFormat: "d-m-Y",
            defaultDate: moment().format('DD-MM-YYYY')
        });

        $('#penerimaan_barang_id').select2({
            placeholder: "-- Pilih penerimaan posted --",
            allowClear: true,
            dropdownParent: $('#returPembelianModal'),
            width: '100%'
        });

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

        function tieredDiscountNet(grossAmount, discount1, discount2, discount3) {
            let netAmount = Math.max(0, Number(grossAmount) || 0);

            [discount1, discount2, discount3].forEach(function(discount) {
                let percentage = Math.min(100, Math.max(0, Number(discount) || 0));
                netAmount *= 1 - (percentage / 100);
            });

            return netAmount;
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

        function statusMeta(status) {
            status = String(status || '').toLowerCase();

            if (status === 'posted') {
                return {
                    className: 'is-approved',
                    label: 'Posted',
                    icon: 'mdi-check-circle-outline'
                };
            }

            if (status === 'cancelled') {
                return {
                    className: 'is-rejected',
                    label: 'Cancelled',
                    icon: 'mdi-cancel'
                };
            }

            return {
                className: 'is-draft',
                label: 'Draft',
                icon: 'mdi-file-clock-outline'
            };
        }

        function statusBadge(status) {
            let meta = statusMeta(status);
            return `
                <span class="purchase-status ${meta.className}">
                    <i class="mdi ${meta.icon}"></i>
                    ${meta.label}
                </span>
            `;
        }

        function compensationStatusMeta(status) {
            const statuses = {
                not_required: { className: 'is-neutral', label: 'Tidak ditagihkan', icon: 'mdi-minus-circle-outline' },
                not_started: { className: 'is-neutral', label: 'Belum diposting', icon: 'mdi-file-clock-outline' },
                waiting: { className: 'is-waiting', label: 'Menunggu', icon: 'mdi-clock-alert-outline' },
                partial: { className: 'is-partial', label: 'Diganti sebagian', icon: 'mdi-progress-clock' },
                settled: { className: 'is-settled', label: 'Sudah diganti', icon: 'mdi-shield-check-outline' },
                overdue: { className: 'is-overdue', label: 'Jatuh tempo', icon: 'mdi-alert-circle-outline' },
                cancelled: { className: 'is-neutral', label: 'Retur batal', icon: 'mdi-cancel' }
            };

            return statuses[String(status || '').toLowerCase()] || statuses.waiting;
        }

        function compensationStatusBadge(status, outstandingValue = 0) {
            let meta = compensationStatusMeta(status);
            let outstanding = Number(outstandingValue || 0);
            let value = outstanding > 0
                ? `<small>${formatRupiah(outstanding)} tersisa</small>`
                : '';

            return `
                <span class="compensation-status ${meta.className}">
                    <span><i class="mdi ${meta.icon}"></i> ${meta.label}</span>
                    ${value}
                </span>
            `;
        }

        function conversionText(qty, conversion, purchaseUnit, stockUnit) {
            let stockQty = (Number(qty) || 0) * (Number(conversion) || 1);
            return `${formatDecimal(qty, 0, 2)} ${purchaseUnit || 'satuan'} = ${formatDecimal(stockQty, 0, 2)} ${stockUnit || 'satuan stok'}`;
        }

        function itemMaxReturnQty(item, conversion, existingQty = 0) {
            conversion = Number(conversion || 1) || 1;
            let returnableStock = Number(item.returnable_qty_stok || 0);
            let availableStock = Math.min(returnableStock, Number(item.batch_stock || 0));
            let maxQty = conversion > 0 ? Math.floor((availableStock / conversion) * 100) / 100 : 0;

            return Math.max(0, maxQty, Number(existingQty || 0));
        }

        function itemReturnUnits(item) {
            if (Array.isArray(item.units) && item.units.length) {
                return item.units;
            }

            return [{
                satuan_id: item.purchase_satuan_id,
                nama: item.satuan || 'satuan',
                konversi: Number(item.konversi || 1) || 1,
                is_purchase: true
            }];
        }

        function setReturnDate(value) {
            let input = $('input[name="tanggal_retur"]');
            let picker = input[0]?._flatpickr;

            if (picker) {
                picker.setDate(value || moment().format('DD-MM-YYYY'), false, 'd-m-Y');
                return;
            }

            input.val(value || moment().format('DD-MM-YYYY'));
        }

        function resetReturForm() {
            let form = $('#returPembelianForm');
            form.trigger('reset');
            $('#retur_pembelian_id').val('');
            $('#penerimaan_barang_id').val('').trigger('change.select2');
            $('#return_supplier, #return_no_po, #return_nomor_faktur').val('');
            $('#expects_compensation').val('1').trigger('change');
            $('#compensation_due_date').val('');
            $('input[name="compensation_notes"]').val('');
            $('#returnReceiptSummary').addClass('d-none');
            $('#returnDetailRows').empty();
            $('#returnDetailEditor').addClass('d-none');
            $('#returnDetailEmpty').removeClass('d-none');
            $('#returnLineCount, #returnModalItemCount, #returnModalQtyCount').text('0');
            $('#summaryReturnItemCount, #summaryReturnableQty, #summaryReturnFilledQty').text('0');
            $('#summaryReturnValue, #summaryReturnGrandTotal').text(formatRupiah(0));
            $('#returnModalSubtotal, #returnModalDiscount, #returnModalTax, #returnGrandTotal').text(formatRupiah(0));
            $('#returPembelianModalLabel').text('Form Retur Pembelian');
            $('#submitReturPembelianForm').html('<i class="mdi mdi-content-save-outline"></i> Simpan Draft');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
            setReturnDate(moment().format('DD-MM-YYYY'));

            $.get('{{ route("returPembelian.generateNoRetur") }}', function(response) {
                $('#nomor_retur').val(response);
            });

            loadPostedReceipts();
        }

        $('#returPembelianModal').on('show.bs.modal', function() {
            if (!editMode) {
                resetReturForm();
            }
        });

        $('#returPembelianModal').on('hidden.bs.modal', function() {
            editMode = false;
        });

        $('#openReturModal, #openReturModalToolbar').on('click', function() {
            editMode = false;
        });

        function syncCompensationPlanFields(select) {
            let expects = String($(select).val()) === '1';
            let scope = $(select).closest('form, .modal-body');
            scope.find('.compensation-plan-field input').prop('disabled', !expects);

            if ($(select).attr('id') === 'expects_compensation') {
                $('#compensationPlanHint')
                    .toggleClass('is-neutral', !expects)
                    .html(expects
                        ? '<i class="mdi mdi-shield-alert-outline"></i><span>Setelah retur diposting, status akan tetap <strong>Menunggu</strong> sampai realisasi supplier dicatat penuh.</span>'
                        : '<i class="mdi mdi-information-outline"></i><span>Retur akan ditandai <strong>Tidak ditagihkan</strong> dan tidak masuk daftar kewajiban supplier.</span>');
            }
        }

        $('#expects_compensation, #planExpectsCompensation').on('change', function() {
            syncCompensationPlanFields(this);
        });

        function loadPostedReceipts(selectedId, selectedText) {
            return $.ajax({
                url: '{{ route("returPembelian.postedReceipts") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let select = $('#penerimaan_barang_id');
                    select.empty().append('<option value="">-- Pilih Penerimaan --</option>');

                    (response || []).forEach(function(item) {
                        let option = new Option(item.text, item.id, false, String(item.id) === String(selectedId));
                        $(option).attr('data-supplier', item.supplier);
                        $(option).attr('data-returnable', item.returnable_qty);
                        select.append(option);
                    });

                    if (selectedId && !select.find(`option[value="${selectedId}"]`).length) {
                        select.append(new Option(selectedText || `Penerimaan #${selectedId}`, selectedId, true, true));
                    }

                    if (selectedId) {
                        select.val(String(selectedId)).trigger('change.select2');
                    }
                }
            });
        }

        function renderReceiptSummary(penerimaan) {
            $('#returnReceiptSummary').removeClass('d-none');
            $('#summaryNomorPenerimaan').text(penerimaan.nomor_penerimaan || '-');
            $('#summaryTanggalTerima').text(formatDateDisplay(penerimaan.tanggal_penerimaan));
            $('#summaryReturnBranch').text(penerimaan.branch || '-');
            $('#summaryReturnGrandTotal').text(formatRupiah(penerimaan.grand_total));
            $('#return_supplier').val(penerimaan.supplier || '-');
            $('#return_no_po').val(penerimaan.no_po || '-');
            $('#return_nomor_faktur').val(penerimaan.nomor_faktur || '-');
            $('#summaryReturnItemCount').text(Number(penerimaan.details?.length || 0).toLocaleString('id-ID'));

            let returnable = (penerimaan.details || []).reduce(function(total, item) {
                return total + (Number(item.returnable_qty) || 0);
            }, 0);
            $('#summaryReturnableQty').text(formatDecimal(returnable, 0, 2));
        }

        function detailRowTemplate(item, existing = null) {
            let existingQty = Number(existing?.qty_retur || 0);
            let qtyValue = existing ? existingQty : 0;
            let units = itemReturnUnits(item);
            let selectedUnit = units.find(unit => String(unit.satuan_id) === String(existing?.satuan_retur_id));

            if (!selectedUnit && existing) {
                selectedUnit = units.find(unit =>
                    String(unit.nama || '').toLowerCase() === String(existing.satuan_beli || '').toLowerCase() &&
                    Number(unit.konversi || 1) === Number(existing.konversi_satuan || 1)
                );
            }

            selectedUnit = selectedUnit || units.find(unit => unit.is_purchase) || units[0];

            let conversion = Number(selectedUnit?.konversi || item.konversi || 1) || 1;
            let stockUnit = item.satuan_stok || existing?.satuan_stok || 'satuan stok';
            let purchaseUnit = item.satuan || 'satuan';
            let returnUnit = selectedUnit?.nama || existing?.satuan_beli || purchaseUnit;
            let purchaseConversion = Number(item.konversi || 1) || 1;
            let priceStock = Number(item.harga_beli_stok || 0) || (Number(item.harga_beli || 0) / purchaseConversion);
            let price = priceStock * conversion;
            let diskon1 = Number(item.diskon_1 ?? existing?.diskon_1 ?? existing?.diskon ?? 0);
            let diskon2 = Number(item.diskon_2 ?? existing?.diskon_2 ?? 0);
            let diskon3 = Number(item.diskon_3 ?? existing?.diskon_3 ?? 0);
            let diskon = Number(item.diskon || existing?.diskon || 0);
            let ppn = Number(item.ppn || existing?.ppn || 0);
            let alasan = existing?.alasan_item || '';
            let batchStock = Number(item.batch_stock || 0);
            let maxQty = itemMaxReturnQty(item, conversion, existingQty);
            let unitOptions = units.map(function(unit) {
                let unitConversion = Number(unit.konversi || 1) || 1;
                let conversionLabel = unitConversion === 1 ? '' : ` (${formatDecimal(unitConversion, 0, 2)} ${escapeHtml(stockUnit)})`;
                let selected = String(unit.satuan_id) === String(selectedUnit?.satuan_id) ? ' selected' : '';

                return `<option value="${unit.satuan_id}" data-konversi="${unitConversion}" data-satuan="${escapeHtml(unit.nama)}"${selected}>${escapeHtml(unit.nama)}${conversionLabel}</option>`;
            }).join('');

            return `
                <tr class="return-detail-row" data-max="${maxQty}" data-price="${price}" data-diskon="${diskon}" data-diskon-1="${diskon1}" data-diskon-2="${diskon2}" data-diskon-3="${diskon3}" data-ppn="${ppn}"
                    data-price-stock="${priceStock}" data-returnable-stock="${Number(item.returnable_qty_stok || 0)}" data-batch-stock="${batchStock}"
                    data-konversi="${conversion}" data-satuan="${escapeHtml(returnUnit)}" data-satuan-stok="${escapeHtml(stockUnit)}">
                    <td>
                        <input type="hidden" name="penerimaan_barang_detail_id[]" value="${item.id}">
                        <strong>${escapeHtml(item.nama_obat)}</strong>
                        <small class="d-block text-muted">${escapeHtml(item.kode_obat)} - ${escapeHtml(purchaseUnit)}</small>
                        ${conversion > 1 ? `<small class="d-block text-muted">1 ${escapeHtml(purchaseUnit)} = ${formatDecimal(conversion, 0, 2)} ${escapeHtml(stockUnit)}</small>` : ''}
                    </td>
                    <td>
                        <span class="receive-qty-stack">
                            <strong>${formatDecimal(item.qty_diterima, 0, 2)}</strong>
                            <small>Sisa ${formatDecimal(item.returnable_qty, 0, 2)}</small>
                            <small>Sudah retur ${formatDecimal(item.returned_qty, 0, 2)}</small>
                        </span>
                    </td>
                    <td>
                        <strong>${escapeHtml(item.no_batch || '-')}</strong>
                        <small class="d-block text-muted">ED ${escapeHtml(item.expired_date || '-')}</small>
                    </td>
                    <td>
                        <strong>${formatDecimal(batchStock, 0, 2)} ${escapeHtml(stockUnit)}</strong>
                        <small class="d-block text-muted return-max-hint">Max ${formatDecimal(maxQty, 0, 2)} ${escapeHtml(returnUnit)}</small>
                    </td>
                    <td>
                        <div class="return-qty-control">
                            <input type="number" class="form-control form-control-sm return-qty" name="qty_retur[]"
                                min="0" max="${maxQty}" step="0.01" value="${qtyValue}">
                            <select class="form-select form-select-sm return-unit" name="satuan_retur_id[]" aria-label="Satuan retur ${escapeHtml(item.nama_obat)}">
                                ${unitOptions}
                            </select>
                            <button type="button" class="btn btn-sm btn-light return-fill-max" title="Isi sisa retur">
                                Max
                            </button>
                        </div>
                        <small class="d-block text-muted return-conversion-hint">${conversionText(qtyValue, conversion, returnUnit, stockUnit)}</small>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="alasan_item[]"
                            value="${escapeHtml(alasan)}" placeholder="Opsional">
                    </td>
                    <td>
                        <strong class="return-line-total">${formatRupiah(0)}</strong>
                        <small class="d-block return-row-check text-muted">Belum diisi</small>
                    </td>
                </tr>
            `;
        }

        function renderReceiptDetails(penerimaan, existingDetails = []) {
            let existingByReceiptDetailId = {};
            (existingDetails || []).forEach(function(detail) {
                existingByReceiptDetailId[String(detail.penerimaan_barang_detail_id)] = detail;
            });

            let rows = (penerimaan.details || []).map(function(item) {
                return detailRowTemplate(item, existingByReceiptDetailId[String(item.id)] || null);
            }).join('');

            $('#returnDetailRows').html(rows);
            $('#returnLineCount').text(Number(penerimaan.details?.length || 0).toLocaleString('id-ID'));

            if (rows) {
                $('#returnDetailEmpty').addClass('d-none');
                $('#returnDetailEditor').removeClass('d-none');
            } else {
                $('#returnDetailEditor').addClass('d-none');
                $('#returnDetailEmpty').removeClass('d-none');
            }

            updateReturnTotals();
        }

        function updateReturnTotals() {
            let itemCount = 0;
            let qtyTotal = 0;
            let subtotalTotal = 0;
            let discountTotal = 0;
            let taxTotal = 0;
            let grandTotal = 0;

            $('.return-detail-row').each(function() {
                let row = $(this);
                let qty = Number(row.find('.return-qty').val()) || 0;
                let maxQty = Number(row.data('max')) || 0;
                let price = Number(row.data('price')) || 0;
                let diskon1 = Number(row.data('diskon-1')) || 0;
                let diskon2 = Number(row.data('diskon-2')) || 0;
                let diskon3 = Number(row.data('diskon-3')) || 0;
                let ppn = Number(row.data('ppn')) || 0;
                let conversion = Number(row.data('konversi')) || 1;
                let purchaseUnit = row.data('satuan') || 'satuan';
                let stockUnit = row.data('satuan-stok') || 'satuan stok';
                let subtotal = qty * price;
                let taxBase = tieredDiscountNet(subtotal, diskon1, diskon2, diskon3);
                let nilaiDiskon = Math.max(0, subtotal - taxBase);
                let nilaiPpn = taxBase * (ppn / 100);
                let total = taxBase + nilaiPpn;
                let check = row.find('.return-row-check');

                row.find('.return-line-total').text(formatRupiah(total));
                row.find('.return-conversion-hint').text(conversionText(qty, conversion, purchaseUnit, stockUnit));

                if (qty > 0) {
                    itemCount++;
                    qtyTotal += qty;
                    subtotalTotal += subtotal;
                    discountTotal += nilaiDiskon;
                    taxTotal += nilaiPpn;
                    grandTotal += total;
                }

                if (qty <= 0) {
                    check.removeClass('text-success text-danger').addClass('text-muted').text('Belum diisi');
                } else if (qty > maxQty) {
                    check.removeClass('text-success text-muted').addClass('text-danger').text(`Melebihi max ${formatDecimal(maxQty, 0, 2)}`);
                } else {
                    check.removeClass('text-danger text-muted').addClass('text-success').text('Siap retur');
                }
            });

            $('#returnModalItemCount').text(itemCount.toLocaleString('id-ID'));
            $('#returnModalQtyCount, #summaryReturnFilledQty').text(formatDecimal(qtyTotal, 0, 2));
            $('#returnModalSubtotal').text(formatRupiah(subtotalTotal));
            $('#returnModalDiscount').text(formatRupiah(discountTotal));
            $('#returnModalTax').text(formatRupiah(taxTotal));
            $('#returnGrandTotal, #summaryReturnValue').text(formatRupiah(grandTotal));
        }

        $('#penerimaan_barang_id').on('change', function() {
            let id = $(this).val();

            if (!id) {
                $('#return_supplier, #return_no_po, #return_nomor_faktur').val('');
                $('#returnReceiptSummary').addClass('d-none');
                $('#returnDetailRows').empty();
                $('#returnDetailEditor').addClass('d-none');
                $('#returnDetailEmpty').removeClass('d-none');
                updateReturnTotals();
                return;
            }

            $.get('{{ route("returPembelian.receiptDetail", ":id") }}'.replace(':id', id), function(response) {
                renderReceiptSummary(response);
                renderReceiptDetails(response);
            }).fail(function(xhr) {
                Swal.fire('Gagal memuat penerimaan', xhr.responseJSON?.message || 'Data penerimaan tidak bisa dimuat.', 'error');
            });
        });

        $('#returnDetailRows').on('input', '.return-qty', updateReturnTotals);

        $('#returnDetailRows').on('change', '.return-unit', function() {
            let select = $(this);
            let row = select.closest('.return-detail-row');
            let option = select.find('option:selected');
            let conversion = Number(option.data('konversi')) || 1;
            let returnUnit = option.data('satuan') || 'satuan';
            let availableStock = Math.min(Number(row.data('returnable-stock')) || 0, Number(row.data('batch-stock')) || 0);
            let maxQty = conversion > 0 ? Math.floor((availableStock / conversion) * 100) / 100 : 0;
            let price = (Number(row.data('price-stock')) || 0) * conversion;

            row.data('konversi', conversion);
            row.data('satuan', returnUnit);
            row.data('max', maxQty);
            row.data('price', price);
            row.find('.return-qty').attr('max', maxQty);
            row.find('.return-max-hint').text(`Max ${formatDecimal(maxQty, 0, 2)} ${returnUnit}`);
            updateReturnTotals();
        });

        $('#returnDetailRows').on('click', '.return-fill-max', function() {
            let row = $(this).closest('.return-detail-row');
            row.find('.return-qty').val(Number(row.data('max')) || 0).trigger('input');
        });

        $('#fillAllReturnable').on('click', function() {
            $('.return-detail-row').each(function() {
                $(this).find('.return-qty').val(Number($(this).data('max')) || 0);
            });
            updateReturnTotals();
        });

        $('#clearAllReturnQty').on('click', function() {
            $('.return-qty').val(0);
            updateReturnTotals();
        });

        function updateReturnSummary(summary = {}) {
            $('#returnTotalCount, #returnAllFilterCount').text(Number(summary.total || 0).toLocaleString('id-ID'));
            $('#returnDraftCount, #returnDraftFilterCount').text(Number(summary.draft || 0).toLocaleString('id-ID'));
            $('#returnPostedCount, #returnPostedFilterCount').text(Number(summary.posted || 0).toLocaleString('id-ID'));
            $('#returnCancelledFilterCount').text(Number(summary.cancelled || 0).toLocaleString('id-ID'));
            $('#returnTotalValue').text(formatRupiah(summary.grand_total || 0));
            $('#returnCompensationWaiting').text(Number(summary.compensation_waiting || 0).toLocaleString('id-ID'));
            $('#returnCompensationOverdue').text(Number(summary.compensation_overdue || 0).toLocaleString('id-ID'));
            $('#returnCompensationOutstanding').text(formatRupiah(summary.compensation_outstanding || 0));
        }

        function initReturnActionTooltips() {
            if (!window.bootstrap || !bootstrap.Tooltip) return;

            document.querySelectorAll('#tableReturPembelian [data-bs-toggle="tooltip"]').forEach(function(element) {
                bootstrap.Tooltip.getInstance(element)?.dispose();
                new bootstrap.Tooltip(element, {
                    container: 'body',
                    trigger: 'hover focus'
                });
            });
        }

        let ReturPembelianTable = $('#tableReturPembelian').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("returPembelian.table") }}',
                data: function(data) {
                    data.date_start = returnDateStart;
                    data.date_end = returnDateEnd;
                    data.compensation_filter = compensationFilter;
                },
                dataSrc: function(json) {
                    updateReturnSummary(json.summary || {});
                    return json.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        return statusBadge(data);
                    }
                },
                {
                    data: 'nomor_retur',
                    name: 'nomor_retur'
                },
                {
                    data: 'nomor_penerimaan',
                    name: 'nomor_penerimaan'
                },
                {
                    data: 'no_po',
                    name: 'no_po'
                },
                {
                    data: 'supplier',
                    name: 'supplier'
                },
                {
                    data: 'nomor_referensi_supplier',
                    name: 'nomor_referensi_supplier',
                    render: data => data || '-'
                },
                {
                    data: 'tanggal',
                    name: 'tanggal'
                },
                {
                    data: null,
                    name: 'total_qty',
                    render: function(data, type, row) {
                        return `
                            <strong>${Number(row.total_barang || 0).toLocaleString('id-ID')} item</strong>
                            <small class="d-block text-muted">${formatDecimal(row.total_qty, 0, 2)} qty</small>
                        `;
                    }
                },
                {
                    data: 'grand_total',
                    name: 'grand_total',
                    render: data => formatRupiah(data)
                },
                {
                    data: 'compensation_status',
                    name: 'compensation_status',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;
                        return compensationStatusBadge(data, row.compensation_outstanding_value);
                    }
                },
                {
                    data: 'user',
                    name: 'user'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [7, 'desc']
            ],
            pageLength: 10,
            responsive: true,
            drawCallback: function() {
                initReturnActionTooltips();
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat retur...</span>',
                emptyTable: 'Belum ada retur pembelian.',
                zeroRecords: 'Retur pembelian yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let returnSearchTimer;

        $('#searchReturPembelian').on('input', function() {
            let searchValue = this.value;
            $(this).closest('.purchase-search').toggleClass('has-value', Boolean(searchValue));
            clearTimeout(returnSearchTimer);
            returnSearchTimer = setTimeout(function() {
                ReturPembelianTable.search(searchValue).draw();
            }, 250);
        });

        $('#clearReturnSearch').on('click', function() {
            $('#searchReturPembelian').val('').trigger('input').focus();
        });

        $('.purchase-filter-group:not(.compensation-filter-group) .purchase-filter-chip').on('click', function() {
            $('.purchase-filter-group:not(.compensation-filter-group) .purchase-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            ReturPembelianTable.column(1).search($(this).data('status') || '').draw();
        });

        function selectCompensationFilter(value) {
            compensationFilter = value || '';
            $('.compensation-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(`.compensation-filter-chip[data-compensation="${compensationFilter}"]`)
                .addClass('is-active')
                .attr('aria-pressed', 'true');

            if (compensationFilter === 'open' || compensationFilter === 'overdue') {
                returnDateStart = '';
                returnDateEnd = '';
                returnDatePicker?.clear();
                $('#returnDateRange').closest('.purchase-date-input').removeClass('has-value');
            }

            ReturPembelianTable.ajax.reload();
        }

        $('.compensation-filter-chip').on('click', function() {
            selectCompensationFilter($(this).data('compensation'));
        });

        $('#openOutstandingCompensations').on('click keydown', function(event) {
            if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            selectCompensationFilter('open');
            document.getElementById('returnTableSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        function formatDateParameter(date) {
            if (!date) return '';
            return moment(date).format('YYYY-MM-DD');
        }

        returnDatePicker = flatpickr('#returnDateRange', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            defaultDate: [returnDateStart, returnDateEnd],
            locale: {
                rangeSeparator: ' - '
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    returnDateStart = formatDateParameter(selectedDates[0]);
                    returnDateEnd = formatDateParameter(selectedDates[1]);
                    $('#returnDateRange').closest('.purchase-date-input').addClass('has-value');
                    ReturPembelianTable.ajax.reload();
                }
            },
            onClose: function(selectedDates) {
                if (selectedDates.length === 1) this.clear();
            }
        });

        $('#returnDateRange').closest('.purchase-date-input').addClass('has-value');

        $('#clearReturnDateRange').on('click', function() {
            returnDateStart = '';
            returnDateEnd = '';
            returnDatePicker.clear();
            $('#returnDateRange').closest('.purchase-date-input').removeClass('has-value');
            ReturPembelianTable.ajax.reload();
        });

        $('#returnPageLength').on('change', function() {
            ReturPembelianTable.page.len(Number(this.value)).draw();
        });

        $('#refreshReturnTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            ReturPembelianTable.ajax.reload(null, false);
        });

        $('#tableReturPembelian').on('xhr.dt', function() {
            $('#refreshReturnTable').removeClass('is-loading').prop('disabled', false);
        });

        $('#scrollReturTable').on('click', function() {
            document.getElementById('returnTableSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        $('#returPembelianForm').on('submit', function(e) {
            e.preventDefault();

            let id = $('#retur_pembelian_id').val();
            let url = id ? '{{ route("returPembelian.update", ":id") }}'.replace(':id', id) :
                '{{ route("returPembelian.store") }}';
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                success: function(response) {
                    $('#returPembelianModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                    ReturPembelianTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Retur pembelian belum bisa disimpan.';
                    let errors = xhr.responseJSON?.errors || {};
                    let firstError = Object.values(errors)[0]?.[0];
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: firstError || message
                    });
                }
            });
        });

        window.editReturPembelian = function(id) {
            $.get('{{ route("returPembelian.edit", ":id") }}'.replace(':id', id), function(response) {
                editMode = true;
                let header = response.header;
                let penerimaan = response.penerimaan_payload;

                $('#returPembelianModal').one('shown.bs.modal', function() {
                    $('#returPembelianModalLabel').text('Edit Retur Pembelian');
                    $('#submitReturPembelianForm').html('<i class="mdi mdi-content-save-outline"></i> Perbarui Draft');
                    $('#retur_pembelian_id').val(header.id);
                    $('#nomor_retur').val(header.nomor_retur);
                    $('input[name="nomor_referensi_supplier"]').val(header.nomor_referensi_supplier || '');
                    setReturnDate(formatDateDisplay(header.tanggal_retur));
                    $('textarea[name="alasan"]').val(header.alasan || '');
                    $('textarea[name="catatan"]').val(header.catatan || '');
                    $('#expects_compensation').val(header.expects_compensation ? '1' : '0').trigger('change');
                    $('#compensation_due_date').val(header.compensation_due_date ? moment(header.compensation_due_date).format('YYYY-MM-DD') : '');
                    $('input[name="compensation_notes"]').val(header.compensation_notes || '');

                    loadPostedReceipts(header.penerimaan_barang_id, `${penerimaan.nomor_penerimaan} - ${penerimaan.no_po}`).then(function() {
                        renderReceiptSummary(penerimaan);
                        renderReceiptDetails(penerimaan, header.details || []);
                    });
                });

                $('#returPembelianModal').modal('show');
            }).fail(function(xhr) {
                Swal.fire('Tidak bisa edit', xhr.responseJSON?.message || 'Data retur pembelian tidak bisa dimuat.', 'error');
            });
        };

        window.lihatReturPembelian = function(id) {
            $.get('{{ route("returPembelian.show", ":id") }}'.replace(':id', id), function(response) {
                let header = response.header;
                let meta = statusMeta(header.status);

                $('#detailReturnStatus')
                    .removeClass('is-draft is-approved is-rejected')
                    .addClass(meta.className)
                    .html(`<i class="mdi ${meta.icon}"></i> ${meta.label}`);
                $('#detailNomorRetur').val(header.nomor_retur);
                $('#detailReturnPenerimaan').val(header.penerimaan_barang?.nomor_penerimaan || '-');
                $('#detailReturnPo').val(header.purchase_order?.no_po || '-');
                $('#detailReturnSupplier').val(header.distributor?.nama || '-');
                $('#detailTanggalRetur').val(formatDateDisplay(header.tanggal_retur));
                $('#detailReturnRefSupplier').val(header.nomor_referensi_supplier || '-');
                $('#detailReturnItemCount').val(Number(header.total_barang || 0).toLocaleString('id-ID'));
                $('#detailReturnQtyCount').val(formatDecimal(header.total_qty, 0, 2));
                $('#detailReturnReason').val(header.alasan || '-');
                $('#detailReturnNote').val(header.catatan || '-');
                $('#detailReturnGrandTotal').text(formatRupiah(header.grand_total));

                renderDetailCompensation(response.compensation || {});

                $('#detailReturnTable tbody').empty();
                (header.details || []).forEach(function(item) {
                    let conversion = Number(item.konversi_satuan || item.purchase_order_detail?.satuan_konversi?.konversi || 1) || 1;
                    let purchaseUnit = item.satuan_beli || item.purchase_order_detail?.satuan_konversi?.satuan?.nama || '-';
                    let stockUnit = item.satuan_stok || item.obat?.satuan?.nama || 'satuan stok';
                    let qtyStock = Number(item.qty_retur_stok || (Number(item.qty_retur || 0) * conversion)) || 0;

                    $('#detailReturnTable tbody').append(`
                        <tr>
                            <td>
                                <strong>${escapeHtml(item.obat?.nama_obat || '-')}</strong>
                                <small class="d-block text-muted">${escapeHtml(item.obat?.kode_obat || '-')}</small>
                            </td>
                            <td>
                                <strong>${formatDecimal(item.qty_retur, 0, 2)} ${escapeHtml(purchaseUnit)}</strong>
                                <small class="d-block text-muted">${formatDecimal(qtyStock, 0, 2)} ${escapeHtml(stockUnit)}</small>
                            </td>
                            <td>${escapeHtml(item.no_batch || '-')}</td>
                            <td>${formatDateDisplay(item.expired_date)}</td>
                            <td>${formatRupiah(item.harga_beli)}</td>
                            <td>D1 ${formatDecimal(item.diskon_1, 0, 2)}% · D2 ${formatDecimal(item.diskon_2, 0, 2)}% · D3 ${formatDecimal(item.diskon_3, 0, 2)}%</td>
                            <td>${formatDecimal(item.ppn, 0, 2)}%</td>
                            <td>${formatRupiah(item.total)}</td>
                            <td>${escapeHtml(item.alasan_item || '-')}</td>
                        </tr>
                    `);
                });

                $('#returPembelianModalDetail').modal('show');
            });
        };

        function renderDetailCompensation(compensation) {
            let meta = compensationStatusMeta(compensation.status);
            $('#detailCompensationStatus')
                .attr('class', `compensation-status ${meta.className}`)
                .html(`<span><i class="mdi ${meta.icon}"></i> ${meta.label}</span>`);
            $('#detailCompensationExpected').text(formatRupiah(compensation.expected_value));
            $('#detailCompensationReceived').text(formatRupiah(compensation.received_value));
            $('#detailCompensationOutstanding').text(formatRupiah(compensation.outstanding_value));
            $('#detailCompensationDueDate').text(compensation.due_date ? formatDateDisplay(compensation.due_date) : '-');
            $('#detailCompensationNotes').text(compensation.notes || 'Tidak ada catatan kesepakatan.');
        }

        function compensationTypeLabel(type) {
            return {
                barang_pengganti: 'Barang pengganti',
                potongan_faktur: 'Potongan faktur',
                refund_tunai: 'Refund tunai',
                transfer_bank: 'Transfer bank',
                nota_kredit: 'Nota kredit',
                lainnya: 'Lainnya'
            }[type] || type || '-';
        }

        function renderCompensationModal(header, compensation) {
            let meta = compensationStatusMeta(compensation.status);
            $('#compensationReturnId').val(header.id);
            $('#compensationDocumentLabel').text(`${header.nomor_retur} · ${header.distributor?.nama || '-'}`);
            $('#compensationModalStatus')
                .attr('class', `compensation-status ${meta.className}`)
                .html(`<span><i class="mdi ${meta.icon}"></i> ${meta.label}</span>`);
            $('#compensationExpectedValue').text(formatRupiah(compensation.expected_value));
            $('#compensationReceivedValue').text(formatRupiah(compensation.received_value));
            $('#compensationOutstandingValue').text(formatRupiah(compensation.outstanding_value));
            $('#compensationDueDateLabel').text(compensation.due_date ? formatDateDisplay(compensation.due_date) : '-');
            $('#planExpectsCompensation').val(compensation.expects_compensation ? '1' : '0').trigger('change');
            $('#planCompensationDueDate').val(compensation.due_date || '');
            $('#planCompensationNotes').val(compensation.notes || '');
            $('#compensationNominal').attr('max', Number(compensation.outstanding_value || 0)).val('');
            $('#compensationNominalMax').text(formatRupiah(compensation.outstanding_value));
            $('#compensationRealizationDate').val(moment().format('YYYY-MM-DD'));

            let canRecord = compensation.expects_compensation && Number(compensation.outstanding_value || 0) > 0;
            $('#compensationEntryForm :input').prop('disabled', !canRecord);
            $('#compensationEntrySection').toggleClass('is-disabled-section', !canRecord);

            let entries = compensation.entries || [];
            $('#compensationHistoryRows').empty();

            entries.forEach(function(entry) {
                let isCancelled = Boolean(entry.cancelled_at);
                let isReceiptDiscount = Boolean(entry.penerimaan_barang?.id);
                let reference = [entry.nomor_referensi, entry.nomor_faktur].filter(Boolean).join(' · ') || '-';
                let actor = entry.created_by?.name || '-';
                let cancelCopy = isCancelled
                    ? `<small class="d-block text-danger">${escapeHtml(entry.cancellation_reason || 'Dibatalkan')}</small>`
                    : '';

                $('#compensationHistoryRows').append(`
                    <tr class="${isCancelled ? 'is-cancelled-entry' : ''}">
                        <td>${formatDateDisplay(entry.tanggal_realisasi)}</td>
                        <td><strong>${escapeHtml(compensationTypeLabel(entry.jenis))}</strong>${entry.keterangan ? `<small class="d-block text-muted">${escapeHtml(entry.keterangan)}</small>` : ''}</td>
                        <td>${escapeHtml(reference)}</td>
                        <td><strong>${formatRupiah(entry.nominal)}</strong></td>
                        <td>${escapeHtml(actor)}</td>
                        <td>${isCancelled ? '<span class="badge bg-danger bg-opacity-10 text-danger">Dibatalkan</span>' : (isReceiptDiscount ? '<span class="badge bg-primary bg-opacity-10 text-primary">Potongan penerimaan</span>' : '<span class="badge bg-success bg-opacity-10 text-success">Aktif</span>')}${cancelCopy}</td>
                        <td>${isCancelled || isReceiptDiscount ? '' : `<button type="button" class="btn btn-sm btn-outline-danger" onclick="batalkanRealisasiGantiRugi(${header.id}, ${entry.id})" title="Batalkan realisasi"><i class="mdi mdi-close-circle-outline"></i></button>`}</td>
                    </tr>
                `);
            });

            $('#compensationHistoryEmpty').toggleClass('d-none', entries.length > 0);
            $('#compensationHistoryWrap').toggleClass('d-none', entries.length === 0);
        }

        function loadCompensation(id, openModal = false) {
            return $.get('{{ route("returPembelian.show", ":id") }}'.replace(':id', id), function(response) {
                renderCompensationModal(response.header, response.compensation || {});
                if (openModal) $('#returCompensationModal').modal('show');
            }).fail(function(xhr) {
                Swal.fire('Gagal memuat ganti rugi', xhr.responseJSON?.message || 'Data ganti rugi supplier tidak bisa dimuat.', 'error');
            });
        }

        window.kelolaGantiRugi = function(id) {
            loadCompensation(id, true);
        };

        $('#compensationPlanForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#compensationReturnId').val();

            $.ajax({
                url: '{{ route("returPembelian.updateCompensationPlan", ":id") }}'.replace(':id', id),
                type: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({ icon: 'success', title: response.message, toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
                    loadCompensation(id);
                    ReturPembelianTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    Swal.fire('Gagal menyimpan rencana', Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || xhr.responseJSON?.message || 'Rencana belum bisa disimpan.', 'error');
                }
            });
        });

        $('#compensationEntryForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#compensationReturnId').val();

            $.ajax({
                url: '{{ route("returPembelian.storeCompensation", ":id") }}'.replace(':id', id),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({ icon: 'success', title: response.message, toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
                    $('#compensationEntryForm')[0].reset();
                    loadCompensation(id);
                    ReturPembelianTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    Swal.fire('Gagal mencatat realisasi', Object.values(xhr.responseJSON?.errors || {})[0]?.[0] || xhr.responseJSON?.message || 'Realisasi belum bisa dicatat.', 'error');
                }
            });
        });

        function toggleCompensationModalFocusTrap(activate) {
            if (!window.bootstrap || !bootstrap.Modal) return;

            let modalElement = document.getElementById('returCompensationModal');
            let modalInstance = modalElement ? bootstrap.Modal.getInstance(modalElement) : null;
            let focusTrap = modalInstance?._focustrap;

            if (!focusTrap) return;

            if (activate && modalElement.classList.contains('show')) {
                focusTrap.activate();
                return;
            }

            focusTrap.deactivate();
        }

        window.batalkanRealisasiGantiRugi = function(returnId, compensationId) {
            toggleCompensationModalFocusTrap(false);

            Swal.fire({
                title: 'Batalkan realisasi ganti rugi?',
                input: 'textarea',
                inputLabel: 'Alasan pembatalan',
                inputPlaceholder: 'Jelaskan alasan koreksi data...',
                inputValidator: value => !String(value || '').trim() ? 'Alasan pembatalan wajib diisi.' : undefined,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Batalkan realisasi',
                cancelButtonText: 'Tutup',
                didOpen: function() {
                    let input = Swal.getInput();
                    if (input) {
                        input.removeAttribute('readonly');
                        input.removeAttribute('disabled');
                        input.focus();
                    }
                }
            }).then(function(result) {
                if (!result.isConfirmed) {
                    toggleCompensationModalFocusTrap(true);
                    return;
                }

                $.ajax({
                    url: '{{ route("returPembelian.cancelCompensation", [":id", ":compensationId"]) }}'
                        .replace(':id', returnId)
                        .replace(':compensationId', compensationId),
                    type: 'DELETE',
                    data: { cancellation_reason: String(result.value || '').trim() },
                    success: function(response) {
                        loadCompensation(returnId);
                        ReturPembelianTable.ajax.reload(null, false);
                        Swal.fire('Berhasil', response.message, 'success').then(function() {
                            toggleCompensationModalFocusTrap(true);
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Realisasi belum bisa dibatalkan.', 'error').then(function() {
                            toggleCompensationModalFocusTrap(true);
                        });
                    }
                });
            });
        };

        window.postReturPembelian = function(id) {
            Swal.fire({
                title: 'Posting retur pembelian?',
                text: 'Stok batch akan dikurangi sesuai qty retur.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, posting',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route("returPembelian.post", ":id") }}'.replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire('Berhasil', response.message, 'success');
                        ReturPembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Retur pembelian gagal diposting.', 'error');
                    }
                });
            });
        };

        window.cancelReturPembelian = function(id) {
            Swal.fire({
                title: 'Batalkan retur pembelian?',
                text: 'Jika sudah posted, stok akan dikembalikan ke batch asal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Tutup'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route("returPembelian.cancel", ":id") }}'.replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire('Berhasil', response.message, 'success');
                        ReturPembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Retur pembelian gagal dibatalkan.', 'error');
                    }
                });
            });
        };

        window.deleteReturPembelian = function(id) {
            Swal.fire({
                title: 'Hapus draft retur pembelian?',
                text: 'Draft akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route("returPembelian.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire('Berhasil', response.message, 'success');
                        ReturPembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Draft retur pembelian gagal dihapus.', 'error');
                    }
                });
            });
        };
    });
</script>
