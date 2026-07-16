<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        let editMode = false;
        let returnDateStart = moment().startOf('month').format('YYYY-MM-DD');
        let returnDateEnd = moment().endOf('month').format('YYYY-MM-DD');
        let returnDatePicker = null;

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

        function conversionText(qty, conversion, purchaseUnit, stockUnit) {
            let stockQty = (Number(qty) || 0) * (Number(conversion) || 1);
            return `${formatDecimal(qty, 0, 2)} ${purchaseUnit || 'satuan'} = ${formatDecimal(stockQty, 0, 2)} ${stockUnit || 'satuan stok'}`;
        }

        function itemMaxReturnQty(item, existingQty = 0) {
            let conversion = Number(item.konversi || 1) || 1;
            let returnableQty = Number(item.returnable_qty || 0);
            let batchStockAsPurchaseUnit = conversion > 0 ? (Number(item.batch_stock || 0) / conversion) : 0;
            let maxQty = Math.min(returnableQty, batchStockAsPurchaseUnit);

            return Math.max(0, maxQty, Number(existingQty || 0));
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
            let maxQty = itemMaxReturnQty(item, existingQty);
            let qtyValue = existing ? existingQty : 0;
            let conversion = Number(item.konversi || existing?.konversi_satuan || 1) || 1;
            let stockUnit = item.satuan_stok || existing?.satuan_stok || 'satuan stok';
            let purchaseUnit = item.satuan || existing?.satuan_beli || 'satuan';
            let price = Number(item.harga_beli || existing?.harga_beli || 0);
            let diskon = Number(item.diskon || existing?.diskon || 0);
            let ppn = Number(item.ppn || existing?.ppn || 0);
            let alasan = existing?.alasan_item || '';
            let batchStock = Number(item.batch_stock || 0);

            return `
                <tr class="return-detail-row" data-max="${maxQty}" data-price="${price}" data-diskon="${diskon}" data-ppn="${ppn}"
                    data-konversi="${conversion}" data-satuan="${escapeHtml(purchaseUnit)}" data-satuan-stok="${escapeHtml(stockUnit)}">
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
                        <small class="d-block text-muted">Max ${formatDecimal(maxQty, 0, 2)} ${escapeHtml(purchaseUnit)}</small>
                    </td>
                    <td>
                        <div class="receive-qty-control">
                            <input type="number" class="form-control form-control-sm return-qty" name="qty_retur[]"
                                min="0" max="${maxQty}" step="0.01" value="${qtyValue}">
                            <button type="button" class="btn btn-sm btn-light return-fill-max" title="Isi sisa retur">
                                Max
                            </button>
                        </div>
                        <small class="d-block text-muted return-conversion-hint">${conversionText(qtyValue, conversion, purchaseUnit, stockUnit)}</small>
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
                let diskon = Number(row.data('diskon')) || 0;
                let ppn = Number(row.data('ppn')) || 0;
                let conversion = Number(row.data('konversi')) || 1;
                let purchaseUnit = row.data('satuan') || 'satuan';
                let stockUnit = row.data('satuan-stok') || 'satuan stok';
                let subtotal = qty * price;
                let nilaiDiskon = subtotal * (diskon / 100);
                let taxBase = Math.max(0, subtotal - nilaiDiskon);
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

        $('.purchase-filter-chip').on('click', function() {
            $('.purchase-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            ReturPembelianTable.column(1).search($(this).data('status') || '').draw();
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
                            <td>${formatDecimal(item.diskon, 0, 2)}%</td>
                            <td>${formatDecimal(item.ppn, 0, 2)}%</td>
                            <td>${formatRupiah(item.total)}</td>
                            <td>${escapeHtml(item.alasan_item || '-')}</td>
                        </tr>
                    `);
                });

                $('#returPembelianModalDetail').modal('show');
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
