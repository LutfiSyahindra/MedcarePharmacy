<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        let editMode = false;
        let receiveDateStart = moment().startOf('month').format('YYYY-MM-DD');
        let receiveDateEnd = moment().endOf('month').format('YYYY-MM-DD');
        let receiveDatePicker = null;

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        flatpickr("#receive-date", {
            dateFormat: "d-m-Y",
            defaultDate: moment().format('DD-MM-YYYY')
        });

        $('#purchase_order_id').select2({
            placeholder: "-- Pilih PO approved --",
            allowClear: true,
            dropdownParent: $('#penerimaanModal'),
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

        function getInitials(value) {
            let words = String(value || '-').trim().split(/\s+/).filter(Boolean);
            return words.slice(0, 2).map(word => word.charAt(0)).join('') || '-';
        }

        function conversionText(qty, conversion, purchaseUnit, stockUnit) {
            let stockQty = (Number(qty) || 0) * (Number(conversion) || 1);
            return `${Number(qty || 0).toLocaleString('id-ID')} ${purchaseUnit || 'satuan'} = ${stockQty.toLocaleString('id-ID')} ${stockUnit || 'satuan stok'}`;
        }

        function batchOptionLabel(batch) {
            if (batch.text) {
                return batch.text;
            }

            return `${batch.no_batch || '-'} | ED ${batch.expired_date || '-'} | Diskon ${formatDecimal(batch.diskon, 0, 2)}% | PPN ${formatDecimal(batch.ppn, 0, 2)}% | Stok ${formatDecimal(batch.qty, 0, 2)}`;
        }

        function normalizedPercent(value) {
            return Math.min(100, Math.max(0, Number(value) || 0));
        }

        function normalizedDiscount(value) {
            return normalizedPercent(value);
        }

        function sameDiscount(left, right) {
            return samePercent(left, right);
        }

        function samePercent(left, right) {
            return Math.abs(normalizedPercent(left) - normalizedPercent(right)) < 0.00001;
        }

        function receiveBatchOptionsHtml(batchOptions, selectedBatchId) {
            let options = ['<option value="">Input manual / batch baru</option>'];

            (batchOptions || []).forEach(function(batch) {
                let selected = String(batch.id) === String(selectedBatchId) ? ' selected' : '';
                options.push(
                    `<option value="${escapeHtml(batch.id)}"${selected}` +
                    ` data-batch="${escapeHtml(batch.no_batch || '')}"` +
                    ` data-expired="${escapeHtml(batch.expired_date || '')}"` +
                    ` data-qty="${Number(batch.qty) || 0}"` +
                    ` data-harga="${Number(batch.harga_beli) || 0}"` +
                    ` data-diskon="${Number(batch.diskon) || 0}"` +
                    ` data-ppn="${Number(batch.ppn) || 0}">` +
                    `${escapeHtml(batchOptionLabel(batch))}</option>`
                );
            });

            return options.join('');
        }

        function selectedReceiveBatchMeta(select) {
            let selected = select.find(':selected');

            return {
                id: selected.val() || '',
                batch: selected.data('batch') || '',
                expired: selected.data('expired') || '',
                qty: Number(selected.data('qty')) || 0,
                diskon: Number(selected.data('diskon')) || 0,
                ppn: Number(selected.data('ppn')) || 0,
                label: selected.text() || ''
            };
        }

        function setReceiveExpiredValue(input, value) {
            let picker = input[0]?._flatpickr;

            if (picker) {
                if (value) {
                    picker.setDate(value, false, 'Y-m-d');
                } else {
                    picker.clear();
                }
                return;
            }

            input.val(value || '');
        }

        function setReceiveBatchMode(select, preserveManualValue = false) {
            let row = select.closest('.receive-detail-row');
            let meta = selectedReceiveBatchMeta(select);
            let batchInput = row.find('input[name="no_batch[]"]');
            let expiredInput = row.find('input[name="expired_date[]"]');
            let hint = row.find('.receive-batch-mode');
            let rowDiscount = normalizedDiscount(row.find('.receive-discount').val());
            let rowTax = normalizedPercent(row.find('.receive-tax').val());

            if (meta.id) {
                batchInput.val(meta.batch).prop('readonly', true);
                setReceiveExpiredValue(expiredInput, meta.expired);
                expiredInput.prop('readonly', true);

                if (!sameDiscount(meta.diskon, rowDiscount) || !samePercent(meta.ppn, rowTax)) {
                    hint
                        .removeClass('is-manual is-existing')
                        .addClass('is-warning')
                        .text(`Diskon ${formatDecimal(rowDiscount, 0, 2)}% dan PPN ${formatDecimal(rowTax, 0, 2)}% akan dibuat batch stok terpisah dari batch existing (${formatDecimal(meta.diskon, 0, 2)}% / ${formatDecimal(meta.ppn, 0, 2)}%).`);
                    row.data('batch-mode', 'separate-price-factor');
                    return;
                }

                hint
                    .removeClass('is-manual is-warning')
                    .addClass('is-existing')
                    .text(`Batch existing diskon ${formatDecimal(meta.diskon, 0, 2)}%, PPN ${formatDecimal(meta.ppn, 0, 2)}%, stok ${formatDecimal(meta.qty, 0, 2)}${meta.expired ? ', ED ' + meta.expired : ''}.`);
                row.data('batch-mode', 'existing');
                return;
            }

            if (!preserveManualValue && row.data('batch-mode') !== 'manual') {
                batchInput.val('');
                setReceiveExpiredValue(expiredInput, '');
            }

            batchInput.prop('readonly', false);
            expiredInput.prop('readonly', false);
            hint
                .removeClass('is-existing is-warning')
                .addClass('is-manual')
                .text('Input manual jika batch belum tersedia.');
            row.data('batch-mode', 'manual');
        }

        function initializeReceiveBatchSelects() {
            $('.receive-batch-select').each(function() {
                let select = $(this);

                if (select.data('select2')) {
                    select.select2('destroy');
                }

                select.select2({
                    dropdownParent: $('#penerimaanModal'),
                    width: '100%',
                    minimumResultsForSearch: 5
                });

                setReceiveBatchMode(select, true);
            });
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

        function setProgressStep(selector, state, text) {
            let step = $(selector);
            step.removeClass('is-active is-complete is-warning');
            step.addClass(state);
            step.find('small').text(text);
        }

        function updateFormProgress() {
            let hasPo = Boolean($('#purchase_order_id').val());
            let hasInvoice = Boolean($('input[name="nomor_faktur"]').val()?.trim()) &&
                Boolean($('input[name="tanggal_penerimaan"]').val()?.trim());
            let itemStats = collectReceiveStats();
            let hasValidItem = itemStats.itemCount > 0 && itemStats.invalidRows === 0;

            setProgressStep('#receiveStepPo', hasPo ? 'is-complete' : 'is-active', hasPo ? 'PO dipilih' :
                'Belum dipilih');
            setProgressStep('#receiveStepInvoice', hasInvoice ? 'is-complete' : (hasPo ? 'is-active' : ''),
                hasInvoice ? 'Faktur siap' : 'Menunggu data');
            setProgressStep('#receiveStepItems', hasValidItem ? 'is-complete' : (itemStats.itemCount > 0 ? 'is-warning' :
                    ''),
                hasValidItem ? `${itemStats.itemCount} item lengkap` : (itemStats.itemCount > 0 ?
                    'Lengkapi batch/expired' : 'Belum ada qty'));

            $('#submitPenerimaanForm')
                .toggleClass('btn-primary', hasValidItem)
                .toggleClass('btn-outline-primary', !hasValidItem);
        }

        function resetPenerimaanForm() {
            let form = $('#penerimaanForm');
            form.trigger('reset');
            $('#penerimaan_id').val('');
            $('#purchase_order_id').val('').trigger('change');
            $('#receive_supplier').val('');
            $('#receivePoSummary').addClass('d-none');
            $('#receiveDetailRows').empty();
            $('#receiveDetailEditor').addClass('d-none');
            $('#receiveDetailEmpty').removeClass('d-none');
            $('#receiveLineCount, #receiveModalItemCount, #receiveModalQtyCount').text('0');
            $('#summaryItemPo, #summaryOutstandingQty, #summaryFilledQty').text('0');
            $('#summaryReceivePercent').text('0%');
            $('#summaryReceiveMeter').css('width', '0%');
            $('#receiveModalSubtotal, #receiveModalDiscount, #receiveModalTax, #receiveGrandTotal').text(formatRupiah(0));
            $('#submitPenerimaanForm').html('<i class="mdi mdi-content-save-outline"></i> Simpan Draft');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
            updateFormProgress();

            $.get('{{ route("penerimaan.generateNoPenerimaan") }}', function(response) {
                $('#nomor_penerimaan').val(response);
            });

            loadApprovedPo();
        }

        $('#penerimaanModal').on('show.bs.modal', function() {
            if (!editMode) {
                resetPenerimaanForm();
            }
        });

        $('#penerimaanModal').on('hidden.bs.modal', function() {
            editMode = false;
        });

        $('#openPenerimaanModal, #openPenerimaanModalToolbar').on('click', function() {
            editMode = false;
        });

        function loadApprovedPo(selectedId, selectedText) {
            return $.ajax({
                url: '{{ route("penerimaan.approvedPo") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let select = $('#purchase_order_id');
                    select.empty().append('<option value="">-- Pilih PO --</option>');

                    (response || []).forEach(function(item) {
                        let option = new Option(item.text, item.id, false, String(item.id) === String(selectedId));
                        $(option).attr('data-supplier', item.supplier);
                        $(option).attr('data-outstanding', item.outstanding_qty);
                        select.append(option);
                    });

                    if (selectedId && !select.find(`option[value="${selectedId}"]`).length) {
                        select.append(new Option(selectedText || `PO #${selectedId}`, selectedId, true, true));
                    }

                    if (selectedId) {
                        select.val(String(selectedId)).trigger('change.select2');
                    }
                }
            });
        }

        function renderPoSummary(po) {
            $('#receivePoSummary').removeClass('d-none');
            $('#summaryNoPo').text(po.no_po || '-');
            $('#summaryTanggalPo').text(formatDateDisplay(po.tanggal_po));
            $('#summaryBranch').text(po.branch || '-');
            $('#summaryTotalEstimasi').text(formatRupiah(po.total_estimasi));
            $('#receive_supplier').val(po.supplier || '-');
            $('#summaryItemPo').text(Number(po.details?.length || 0).toLocaleString('id-ID'));
            let outstanding = (po.details || []).reduce(function(total, item) {
                return total + (Number(item.outstanding_qty) || 0);
            }, 0);
            $('#summaryOutstandingQty').text(outstanding.toLocaleString('id-ID'));
            $('#summaryFilledQty').text('0');
            $('#summaryReceivePercent').text('0%');
            $('#summaryReceiveMeter').css('width', '0%');
            updateFormProgress();
        }

        function detailRowTemplate(item, existing = null) {
            let qtyValue = existing ? Number(existing.qty_diterima || 0) : 0;
            let maxQty = Number(item.outstanding_qty || 0);
            let harga = existing ? Number(existing.harga_beli || 0) : Number(item.harga_estimasi || 0);
            let diskon = existing ? Number(existing.diskon || 0) : 0;
            let ppn = existing ? Number(existing.ppn || 0) : 11;
            let batch = existing ? (existing.no_batch || '') : '';
            let expired = existing && existing.expired_date ? moment(existing.expired_date).format('YYYY-MM-DD') : '';
            let selectedBatchId = existing ? (existing.stok_batch_id || '') : '';
            let batchOptions = Array.isArray(item.batch_options) ? [...item.batch_options] : [];
            let conversion = Number(item.konversi || existing?.konversi_satuan || 1) || 1;
            let stockUnit = item.satuan_stok || existing?.satuan_stok || 'satuan stok';
            let conversionHint = conversion > 1
                ? `<small class="d-block text-muted receive-conversion-hint">${conversionText(qtyValue, conversion, item.satuan, stockUnit)}</small>`
                : '';

            if (selectedBatchId && !batchOptions.some(batchItem => String(batchItem.id) === String(selectedBatchId))) {
                batchOptions.push({
                    id: selectedBatchId,
                    no_batch: batch,
                    expired_date: expired,
                    qty: 0,
                    harga_beli: harga,
                    diskon,
                    ppn,
                    text: `${batch || 'Batch terpilih'} | ED ${expired || '-'} | Diskon ${formatDecimal(diskon, 0, 2)}% | PPN ${formatDecimal(ppn, 0, 2)}%`
                });
            }

            return `
                <tr class="receive-detail-row" data-max="${maxQty}" data-konversi="${conversion}" data-satuan="${escapeHtml(item.satuan)}" data-satuan-stok="${escapeHtml(stockUnit)}">
                    <td>
                        <input type="hidden" name="purchase_order_detail_id[]" value="${item.id}">
                        <input type="hidden" name="obat_id[]" value="${item.obat_id}">
                        <strong>${escapeHtml(item.nama_obat)}</strong>
                        <small class="d-block text-muted">${escapeHtml(item.kode_obat)} - ${escapeHtml(item.satuan)}</small>
                        ${conversion > 1 ? `<small class="d-block text-muted">1 ${escapeHtml(item.satuan)} = ${conversion.toLocaleString('id-ID')} ${escapeHtml(stockUnit)}</small>` : ''}
                    </td>
                    <td>
                        <span class="receive-qty-stack">
                            <strong>${Number(item.qty_po || 0).toLocaleString('id-ID')}</strong>
                            <small>Sisa ${maxQty.toLocaleString('id-ID')}</small>
                        </span>
                    </td>
                    <td>
                        <div class="receive-qty-control">
                            <input type="number" class="form-control form-control-sm receive-qty" name="qty_diterima[]"
                                min="0" max="${maxQty}" step="0.01" value="${qtyValue}">
                            <button type="button" class="btn btn-sm btn-light receive-fill-max" title="Isi sisa PO">
                                Max
                            </button>
                        </div>
                        ${conversionHint}
                    </td>
                    <td>
                        <div class="receive-batch-picker">
                            <select class="form-select form-select-sm receive-batch-select" name="stok_batch_id[]">
                                ${receiveBatchOptionsHtml(batchOptions, selectedBatchId)}
                            </select>
                            <input type="text" class="form-control form-control-sm" name="no_batch[]"
                                value="${escapeHtml(batch)}" placeholder="Batch manual">
                            <small class="receive-batch-mode">Input manual jika batch belum tersedia.</small>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm receive-expired-date"
                            name="expired_date[]" value="${escapeHtml(expired)}" placeholder="YYYY-MM-DD">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm receive-price" name="harga_beli[]"
                            min="0" step="0.01" value="${harga}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm receive-discount" name="diskon[]"
                            min="0" max="100" step="0.01" value="${diskon}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm receive-tax" name="ppn[]"
                            min="0" max="100" step="0.01" value="${ppn}">
                    </td>
                    <td>
                        <strong class="receive-row-total">${formatRupiah(0)}</strong>
                    </td>
                    <td>
                        <span class="receive-row-check is-empty">
                            <i class="mdi mdi-minus-circle-outline"></i>
                            Belum diisi
                        </span>
                    </td>
                </tr>
            `;
        }

        function renderPoDetails(po, existingDetails = []) {
            let existingByDetail = {};
            existingDetails.forEach(function(detail) {
                existingByDetail[detail.purchase_order_detail_id] = detail;
            });

            $('#receiveDetailRows').empty();

            if (!po.details || !po.details.length) {
                $('#receiveDetailEditor').addClass('d-none');
                $('#receiveDetailEmpty').removeClass('d-none');
                return;
            }

            po.details.forEach(function(item) {
                $('#receiveDetailRows').append(detailRowTemplate(item, existingByDetail[item.id]));
            });

            $('#receiveLineCount').text(po.details.length.toLocaleString('id-ID'));
            $('#receiveDetailEditor').removeClass('d-none');
            $('#receiveDetailEmpty').addClass('d-none');

            flatpickr(".receive-expired-date", {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            initializeReceiveBatchSelects();
            recalculateReceiveTotals();
            updateFormProgress();
        }

        function collectReceiveStats() {
            let itemCount = 0;
            let totalQty = 0;
            let totalSubtotal = 0;
            let totalDiscount = 0;
            let totalTax = 0;
            let grandTotal = 0;
            let invalidRows = 0;
            let totalOutstanding = 0;

            $('.receive-detail-row').each(function() {
                let row = $(this);
                let qty = Number(row.find('.receive-qty').val()) || 0;
                let price = Number(row.find('.receive-price').val()) || 0;
                let discount = Number(row.find('.receive-discount').val()) || 0;
                let tax = Number(row.find('.receive-tax').val()) || 0;
                let max = Number(row.data('max')) || 0;
                let conversion = Number(row.data('konversi')) || 1;
                let purchaseUnit = row.data('satuan') || 'satuan';
                let stockUnit = row.data('satuan-stok') || 'satuan stok';
                let selectedBatchId = row.find('.receive-batch-select').val();
                let batch = row.find('input[name="no_batch[]"]').val()?.trim();
                let expired = row.find('input[name="expired_date[]"]').val()?.trim();
                let hasBatchInfo = selectedBatchId ? Boolean(batch) : Boolean(batch && expired);
                let subtotal = qty * price;
                let discountValue = subtotal * discount / 100;
                let taxBase = Math.max(0, subtotal - discountValue);
                let taxValue = taxBase * tax / 100;
                let total = taxBase + taxValue;
                let check = row.find('.receive-row-check');

                totalOutstanding += max;

                row.find('.receive-row-total').text(formatRupiah(total));
                row.find('.receive-conversion-hint').text(conversionText(qty, conversion, purchaseUnit, stockUnit));
                row.removeClass('is-filled is-warning is-empty');
                check.removeClass('is-ok is-warning is-empty');

                if (qty > 0) {
                    itemCount++;
                    totalQty += qty;
                    totalSubtotal += subtotal;
                    totalDiscount += discountValue;
                    totalTax += taxValue;
                    grandTotal += total;

                    if (!hasBatchInfo) {
                        invalidRows++;
                        row.addClass('is-warning');
                        check.addClass('is-warning').html(
                            '<i class="mdi mdi-alert-circle-outline"></i> Perlu batch/expired');
                    } else {
                        row.addClass('is-filled');
                        check.addClass('is-ok').html('<i class="mdi mdi-check-circle-outline"></i> Lengkap');
                    }
                } else {
                    row.addClass('is-empty');
                    check.addClass('is-empty').html('<i class="mdi mdi-minus-circle-outline"></i> Belum diisi');
                }
            });

            return {
                itemCount,
                totalQty,
                totalSubtotal,
                totalDiscount,
                totalTax,
                grandTotal,
                invalidRows,
                totalOutstanding
            };
        }

        function recalculateReceiveTotals() {
            let stats = collectReceiveStats();
            let receivePercent = stats.totalOutstanding > 0 ? Math.min(100, (stats.totalQty / stats.totalOutstanding) *
                100) : 0;

            $('#receiveModalItemCount').text(stats.itemCount.toLocaleString('id-ID'));
            $('#receiveModalQtyCount, #summaryFilledQty').text(stats.totalQty.toLocaleString('id-ID'));
            $('#receiveModalSubtotal').text(formatRupiah(stats.totalSubtotal));
            $('#receiveModalDiscount').text(formatRupiah(stats.totalDiscount));
            $('#receiveModalTax').text(formatRupiah(stats.totalTax));
            $('#receiveGrandTotal').text(formatRupiah(stats.grandTotal));
            $('#summaryReceivePercent').text(`${receivePercent.toFixed(0)}%`);
            $('#summaryReceiveMeter').css('width', `${receivePercent}%`);
            updateFormProgress();
        }

        $(document).on('input', '.receive-qty, .receive-price, .receive-discount, .receive-tax, input[name="no_batch[]"], input[name="expired_date[]"], input[name="nomor_faktur"], input[name="tanggal_penerimaan"]', function() {
            let input = $(this);
            let max = Number(input.closest('.receive-detail-row').data('max')) || 0;

            if (input.hasClass('receive-qty') && Number(input.val()) > max) {
                input.val(max);
            }

            if (input.hasClass('receive-discount') || input.hasClass('receive-tax')) {
                setReceiveBatchMode(input.closest('.receive-detail-row').find('.receive-batch-select'), true);
            }

            recalculateReceiveTotals();
        });

        $(document).on('change', '.receive-batch-select', function() {
            setReceiveBatchMode($(this));
            recalculateReceiveTotals();
        });

        $(document).on('click', '.receive-fill-max', function() {
            let row = $(this).closest('.receive-detail-row');
            row.find('.receive-qty').val(Number(row.data('max')) || 0).trigger('input');
        });

        $('#fillAllOutstanding').on('click', function() {
            $('.receive-detail-row').each(function() {
                $(this).find('.receive-qty').val(Number($(this).data('max')) || 0);
            });
            recalculateReceiveTotals();
        });

        $('#clearAllQty').on('click', function() {
            $('.receive-detail-row .receive-qty').val(0);
            recalculateReceiveTotals();
        });

        $('#purchase_order_id').on('change', function() {
            let poId = $(this).val();

            if (!poId) {
                $('#receive_supplier').val('');
                $('#receivePoSummary').addClass('d-none');
                $('#receiveDetailRows').empty();
                $('#receiveDetailEditor').addClass('d-none');
                $('#receiveDetailEmpty').removeClass('d-none');
                $('#summaryItemPo, #summaryOutstandingQty, #summaryFilledQty').text('0');
                $('#summaryReceivePercent').text('0%');
                $('#summaryReceiveMeter').css('width', '0%');
                recalculateReceiveTotals();
                return;
            }

            $.get('{{ route("penerimaan.purchaseOrderDetail", ":id") }}'.replace(':id', poId), function(po) {
                renderPoSummary(po);
                renderPoDetails(po);
            });
        });

        function updateReceiveSummary(summary, recordsTotal) {
            let total = Number(summary.total ?? recordsTotal) || 0;
            let draft = Number(summary.draft) || 0;
            let posted = Number(summary.posted) || 0;
            let cancelled = Number(summary.cancelled) || 0;

            $('#receiveTotalCount, #receiveAllFilterCount').text(total.toLocaleString('id-ID'));
            $('#receiveDraftCount, #receiveDraftFilterCount').text(draft.toLocaleString('id-ID'));
            $('#receivePostedCount, #receivePostedFilterCount').text(posted.toLocaleString('id-ID'));
            $('#receiveCancelledFilterCount').text(cancelled.toLocaleString('id-ID'));
            $('#receiveTotalValue').text(formatRupiah(summary.grand_total));
            $('#receiveTotalQty').text(Number(summary.total_qty || 0).toLocaleString('id-ID'));
            $('#receiveDraftInsight').text(draft.toLocaleString('id-ID'));
            $('#receiveCancelledInsight').text(cancelled.toLocaleString('id-ID'));
        }

        let PenerimaanTable = $('#tablePenerimaan').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [
                [7, 'desc']
            ],
            ajax: {
                url: '{{ route("penerimaan.table") }}',
                type: 'GET',
                data: function(request) {
                    request.date_start = receiveDateStart;
                    request.date_end = receiveDateEnd;
                },
                dataSrc: function(response) {
                    updateReceiveSummary(response.summary || {}, response.recordsTotal);
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
                    data: 'status',
                    render: data => statusBadge(data)
                },
                {
                    data: 'nomor_penerimaan',
                    render: data => `<span class="purchase-po-number"><i class="mdi mdi-receipt-text-outline"></i>${escapeHtml(data)}</span>`
                },
                {
                    data: 'no_po',
                    render: data => `<span class="purchase-po-number"><i class="mdi mdi-file-document-outline"></i>${escapeHtml(data)}</span>`
                },
                {
                    data: 'supplier',
                    render: data => `<span class="purchase-badge is-distributor"><i class="mdi mdi-truck-delivery-outline"></i>${escapeHtml(data)}</span>`
                },
                {
                    data: 'nomor_faktur',
                    render: data => escapeHtml(data)
                },
                {
                    data: 'nomor_surat_jalan',
                    render: data => escapeHtml(data || '-')
                },
                {
                    data: 'tanggal',
                    render: data => `<span class="purchase-date"><i class="mdi mdi-calendar-blank-outline"></i>${formatDateDisplay(data)}</span>`
                },
                {
                    data: null,
                    render: row => `${Number(row.total_barang || 0).toLocaleString('id-ID')} item / ${Number(row.total_qty || 0).toLocaleString('id-ID')}`
                },
                {
                    data: 'grand_total',
                    render: data => `<span class="purchase-money"><i class="mdi mdi-cash"></i>${formatRupiah(data)}</span>`
                },
                {
                    data: 'user',
                    render: data => `<span class="purchase-user"><span class="purchase-user-avatar">${escapeHtml(getInitials(data))}</span><span>${escapeHtml(data)}</span></span>`
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    render: data => `<div class="purchase-action-group">${data || ''}</div>`
                }
            ],
            columnDefs: [{
                targets: [0, 11],
                className: 'text-center'
            }],
            drawCallback: function() {
                let table = $('#tablePenerimaan');
                table.find('.btn-info').attr('title', 'Lihat detail penerimaan');
                table.find('.btn-success').attr('title', 'Edit draft penerimaan');
                table.find('.btn-primary').attr('title', 'Posting penerimaan ke stok');
                table.find('.btn-warning').attr('title', 'Batalkan penerimaan');
                table.find('.btn-danger').attr('title', 'Hapus draft penerimaan');
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat penerimaan...</span>',
                emptyTable: 'Belum ada penerimaan barang.',
                zeroRecords: 'Penerimaan yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let receiveSearchTimer;

        $('#searchPenerimaan').on('input', function() {
            let searchValue = this.value;
            $(this).closest('.purchase-search').toggleClass('has-value', Boolean(searchValue));
            clearTimeout(receiveSearchTimer);
            receiveSearchTimer = setTimeout(function() {
                PenerimaanTable.search(searchValue).draw();
            }, 250);
        });

        $('#clearReceiveSearch').on('click', function() {
            $('#searchPenerimaan').val('').trigger('input').focus();
        });

        $('.purchase-filter-chip').on('click', function() {
            $('.purchase-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            PenerimaanTable.column(1).search($(this).data('status') || '').draw();
        });

        function formatDateParameter(date) {
            if (!date) return '';
            return moment(date).format('YYYY-MM-DD');
        }

        function applyReceiveDateRange(startDate, endDate) {
            receiveDateStart = formatDateParameter(startDate);
            receiveDateEnd = formatDateParameter(endDate);
            $('#receiveDateRange').closest('.purchase-date-input').addClass('has-value');
            PenerimaanTable.ajax.reload();
        }

        receiveDatePicker = flatpickr('#receiveDateRange', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            defaultDate: [receiveDateStart, receiveDateEnd],
            locale: {
                rangeSeparator: ' - '
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    $('#receiveDatePreset').val('');
                    applyReceiveDateRange(selectedDates[0], selectedDates[1]);
                }
            },
            onClose: function(selectedDates) {
                if (selectedDates.length === 1) this.clear();
            }
        });

        $('#receiveDatePreset').val('this_month');
        $('#receiveDateRange').closest('.purchase-date-input').addClass('has-value');

        $('#clearReceiveDateRange').on('click', function() {
            receiveDateStart = '';
            receiveDateEnd = '';
            receiveDatePicker.clear();
            $('#receiveDatePreset').val('');
            $('#receiveDateRange').closest('.purchase-date-input').removeClass('has-value');
            PenerimaanTable.ajax.reload();
        });

        $('#receiveDatePreset').on('change', function() {
            let preset = this.value;
            if (!preset) return;

            let today = moment().startOf('day');
            let startDate = today.clone();
            let endDate = today.clone();

            if (preset === '7days') startDate.subtract(6, 'days');
            if (preset === '30days') startDate.subtract(29, 'days');
            if (preset === 'this_month') {
                startDate.startOf('month');
                endDate.endOf('month');
            }

            receiveDatePicker.setDate([startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD')], false);
            applyReceiveDateRange(startDate, endDate);
        });

        $('#receivePageLength').on('change', function() {
            PenerimaanTable.page.len(Number(this.value)).draw();
        });

        $('#refreshReceiveTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            PenerimaanTable.ajax.reload(null, false);
        });

        $('#tablePenerimaan').on('xhr.dt', function() {
            $('#refreshReceiveTable').removeClass('is-loading').prop('disabled', false);
        });

        $('#scrollReceiveTable').on('click', function() {
            document.getElementById('receiveTableSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        $('#penerimaanForm').on('submit', function(e) {
            e.preventDefault();

            let id = $('#penerimaan_id').val();
            let url = id ? '{{ route("penerimaan.update", ":id") }}'.replace(':id', id) :
                '{{ route("penerimaan.store") }}';
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                success: function(response) {
                    $('#penerimaanModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                    PenerimaanTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Penerimaan belum bisa disimpan.';
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

        window.editPenerimaan = function(id) {
            $.get('{{ route("penerimaan.edit", ":id") }}'.replace(':id', id), function(response) {
                editMode = true;
                let header = response.header;
                let po = response.po_payload;

                $('#penerimaanModal').one('shown.bs.modal', function() {
                    $('#penerimaanModalLabel').text('Edit Penerimaan Barang');
                    $('#submitPenerimaanForm').html('<i class="mdi mdi-content-save-outline"></i> Perbarui Draft');
                    $('#penerimaan_id').val(header.id);
                    $('#nomor_penerimaan').val(header.nomor_penerimaan);
                    $('input[name="nomor_faktur"]').val(header.nomor_faktur);
                    $('input[name="nomor_surat_jalan"]').val(header.nomor_surat_jalan);
                    $('input[name="tanggal_penerimaan"]').val(formatDateDisplay(header.tanggal_penerimaan));
                    $('textarea[name="catatan"]').val(header.catatan || '');

                    loadApprovedPo(header.purchase_order_id, `${po.no_po} - ${po.supplier}`).then(function() {
                        renderPoSummary(po);
                        renderPoDetails(po, header.details || []);
                    });
                });

                $('#penerimaanModal').modal('show');
            }).fail(function(xhr) {
                Swal.fire('Tidak bisa edit', xhr.responseJSON?.message || 'Data penerimaan tidak bisa dimuat.', 'error');
            });
        };

        window.lihatPenerimaan = function(id) {
            $.get('{{ route("penerimaan.show", ":id") }}'.replace(':id', id), function(response) {
                let header = response.header;
                let meta = statusMeta(header.status);

                $('#detailReceiveStatus')
                    .removeClass('is-draft is-approved is-rejected')
                    .addClass(meta.className)
                    .html(`<i class="mdi ${meta.icon}"></i> ${meta.label}`);
                $('#detailNomorPenerimaan').val(header.nomor_penerimaan);
                $('#detailNoPo').val(header.purchase_order?.no_po || '-');
                $('#detailSupplier').val(header.distributor?.nama || '-');
                $('#detailTanggalTerima').val(formatDateDisplay(header.tanggal_penerimaan));
                $('#detailNomorFaktur').val(header.nomor_faktur || '-');
                $('#detailNomorSuratJalan').val(header.nomor_surat_jalan || '-');
                $('#detailCatatan').val(header.catatan || '-');
                $('#detailReceiveItemCount').text(Number(header.total_barang || 0).toLocaleString('id-ID'));
                $('#detailGrandTotal').text(formatRupiah(header.grand_total));

                $('#detailReceiveTable tbody').empty();
                (header.details || []).forEach(function(item) {
                    let conversion = Number(item.konversi_satuan || item.purchase_order_detail?.satuan_konversi?.konversi || 1) || 1;
                    let purchaseUnit = item.satuan_beli || item.purchase_order_detail?.satuan_konversi?.satuan?.nama || '-';
                    let stockUnit = item.satuan_stok || item.obat?.satuan?.nama || 'satuan stok';
                    let qtyStock = Number(item.qty_diterima_stok || (Number(item.qty_diterima || 0) * conversion)) || 0;

                    $('#detailReceiveTable tbody').append(`
                        <tr>
                            <td>
                                <strong>${escapeHtml(item.obat?.nama_obat || '-')}</strong>
                                <small class="d-block text-muted">${escapeHtml(item.obat?.kode_obat || '-')}</small>
                            </td>
                            <td>
                                <strong>${Number(item.qty_diterima || 0).toLocaleString('id-ID')} ${escapeHtml(purchaseUnit)}</strong>
                                <small class="d-block text-muted">${qtyStock.toLocaleString('id-ID')} ${escapeHtml(stockUnit)}</small>
                            </td>
                            <td>${escapeHtml(item.no_batch || '-')}</td>
                            <td>${formatDateDisplay(item.expired_date)}</td>
                            <td>${formatRupiah(item.harga_beli)}</td>
                            <td>${Number(item.diskon || 0).toLocaleString('id-ID')}%</td>
                            <td>${Number(item.ppn || 0).toLocaleString('id-ID')}%</td>
                            <td>${formatRupiah(item.total)}</td>
                        </tr>
                    `);
                });

                $('#penerimaanModalDetail').modal('show');
            });
        };

        function renderHargaJualPreview(preview) {
            let header = preview.header || {};
            let details = preview.details || [];
            let missingMargin = Number(preview.summary?.missing_margin_count || 0);
            let rows = details.map(function(item) {
                let marginBadge = item.has_margin
                    ? '<span class="badge bg-success bg-opacity-10 text-success">Aktif</span>'
                    : '<span class="badge bg-warning bg-opacity-10 text-warning">Faktor 1</span>';

                return `
                    <tr>
                        <td class="text-start">
                            <strong>${escapeHtml(item.nama_obat)}</strong>
                            <small class="d-block text-muted">${escapeHtml(item.kode_obat)} - ${escapeHtml(item.golongan)}</small>
                        </td>
                        <td class="text-end">
                            <strong>${formatRupiah(item.total_harga_beli_include_ppn || item.total_harga_beli)}</strong>
                            <small class="d-block text-muted">${formatDecimal(item.qty_diterima, 0, 2)} ${escapeHtml(item.satuan_beli)} x ${formatRupiah(item.harga_beli)}</small>
                            <small class="d-block text-muted">Sudah termasuk PPN</small>
                            <small class="d-block text-muted">Dasar ${formatRupiah(item.harga_beli_satuan_terkecil)}/${escapeHtml(item.satuan_terkecil)}</small>
                        </td>
                        <td class="text-end">
                            <strong>${formatDecimal(item.qty_satuan_terkecil, 0, 2)}</strong>
                            <small class="d-block text-muted">${escapeHtml(item.satuan_terkecil)}</small>
                            <small class="d-block text-muted">Konversi ${formatDecimal(item.konversi_satuan, 0, 2)}</small>
                        </td>
                        <td class="text-center">
                            <span class="d-block">${formatDecimal(item.ppn, 0, 2)}%</span>
                            <small class="text-muted">Include total</small>
                        </td>
                        <td class="text-center">
                            <span class="d-block">${formatDecimal(item.faktor_jual, 3, 3)}</span>
                            ${marginBadge}
                        </td>
                        <td class="text-end">
                            <span class="d-block">${formatDecimal(item.diskon, 0, 2)}%</span>
                            <small class="text-muted">${formatRupiah(item.nilai_diskon_beli || item.nilai_diskon_jual)}</small>
                            <small class="d-block text-muted">Sudah masuk total</small>
                        </td>
                        <td class="text-end">
                            <strong>${formatRupiah(item.harga_jual)}</strong>
                            <small class="d-block text-muted">/${escapeHtml(item.satuan_terkecil)}</small>
                            <small class="d-block text-muted">Total ${formatRupiah(item.total_harga_jual)}</small>
                        </td>
                    </tr>
                `;
            }).join('');

            if (!rows) {
                rows = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Tidak ada detail harga jual.</td>
                    </tr>
                `;
            }

            return `
                <div class="receive-selling-preview text-start">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                        <div>
                            <strong>${escapeHtml(header.nomor_penerimaan || '-')}</strong>
                            <small class="d-block text-muted">${escapeHtml(header.no_po || '-')} - ${escapeHtml(header.supplier || '-')}</small>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary align-self-start">${details.length} item</span>
                    </div>
                    ${missingMargin > 0 ? `
                        <div class="alert alert-warning py-2 mb-3">
                            ${missingMargin} item belum memiliki margin golongan aktif, sehingga faktor 1.000 dipakai.
                        </div>
                    ` : ''}
                    <div class="table-responsive" style="max-height: 420px; overflow: auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Obat</th>
                                    <th class="text-end">Total Beli + PPN</th>
                                    <th class="text-end">Qty Terkecil</th>
                                    <th class="text-center">PPN</th>
                                    <th class="text-center">Faktor</th>
                                    <th class="text-end">Diskon</th>
                                    <th class="text-end">Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        function submitPostPenerimaan(id) {
            $.ajax({
                url: '{{ route("penerimaan.post", ":id") }}'.replace(':id', id),
                type: 'PUT',
                success: function(response) {
                    Swal.fire('Berhasil', response.message, 'success');
                    PenerimaanTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Penerimaan gagal diposting.', 'error');
                }
            });
        }

        window.postPenerimaan = function(id) {
            $.get('{{ route("penerimaan.hargaJualPreview", ":id") }}'.replace(':id', id), function(preview) {
                Swal.fire({
                    title: 'Harga jual saat posting',
                    html: renderHargaJualPreview(preview),
                    icon: 'info',
                    width: '72rem',
                    showCancelButton: true,
                    confirmButtonText: 'Posting & Simpan Harga Jual Batch',
                    cancelButtonText: 'Batal',
                    focusConfirm: false
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    submitPostPenerimaan(id);
                });
            }).fail(function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Harga jual penerimaan gagal dimuat.', 'error');
            });
        };

        window.cancelPenerimaan = function(id) {
            Swal.fire({
                title: 'Batalkan penerimaan?',
                text: 'Jika sudah posted, stok akan dikurangi kembali.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Tutup'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route("penerimaan.cancel", ":id") }}'.replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire('Berhasil', response.message, 'success');
                        PenerimaanTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Penerimaan gagal dibatalkan.', 'error');
                    }
                });
            });
        };

        window.deletePenerimaan = function(id) {
            Swal.fire({
                title: 'Hapus draft penerimaan?',
                text: 'Draft akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route("penerimaan.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire('Berhasil', response.message, 'success');
                        PenerimaanTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Draft gagal dihapus.', 'error');
                    }
                });
            });
        };
    });
</script>
