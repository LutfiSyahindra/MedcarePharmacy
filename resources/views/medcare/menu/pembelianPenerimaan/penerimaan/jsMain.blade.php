<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        const selectedPurchaseOrderId = @json($selectedPurchaseOrder?->id);
        const initialReceiptId = @json($initialReceiptId);
        let editMode = false;
        let receiveDateStart = selectedPurchaseOrderId ? '' : moment().startOf('month').format('YYYY-MM-DD');
        let receiveDateEnd = selectedPurchaseOrderId ? '' : moment().endOf('month').format('YYYY-MM-DD');
        let receiveDatePicker = null;
        let receiveMedicineId = '';
        let paymentPreset = 'none';
        let supplierCompensationAvailable = 0;
        let currentPoAdditionalCost = 0;
        let currentPoTotalQty = 0;
        let pendingReceiptPo = null;
        let pendingInvoiceMode = '';
        let receiptInvoiceMode = '';
        let receiptSaving = false;
        let pickerSelection = null;
        let pickerInvoiceMode = '';
        let pickerDetail = null;
        let pickerBusy = false;
        let pickerListRequest = null;
        let pickerDetailRequest = null;
        let pickerCreateRequest = null;
        let pickerQuery = '';
        let pickerOrderQuery = '';
        let pickerMedicineId = '';
        let pickerMedicines = new Map();

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        flatpickr("#receive-date", {
            dateFormat: "d-m-Y",
            defaultDate: moment().format('DD-MM-YYYY')
        });

        flatpickr("#invoice-date", {
            dateFormat: "d-m-Y",
            defaultDate: moment().format('DD-MM-YYYY')
        });

        flatpickr("#invoice-due-date", {
            dateFormat: "d-m-Y"
        });

        function formatRupiah(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
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
            if (!value) {
                return '-';
            }

            if (/^\d{2}-\d{2}-\d{4}$/.test(value)) {
                return value;
            }

            let date = moment(value);
            return date.isValid() ? date.format('DD-MM-YYYY') : (value || '-');
        }

        function formatDateInput(value) {
            if (!value) {
                return '';
            }

            if (/^\d{2}-\d{2}-\d{4}$/.test(value)) {
                return value;
            }

            let date = moment(value);
            return date.isValid() ? date.format('DD-MM-YYYY') : value;
        }

        function roundMoney(value) {
            let numeric = Number(value) || 0;
            let [coefficient, exponent = '0'] = String(numeric).split('e');
            let shifted = Number(`${coefficient}e${Number(exponent) + 2}`);
            // Preserve half-cent rounding when floating-point arithmetic lands just below the midpoint.
            return Math.round(shifted + Number.EPSILON * Math.abs(shifted)) / 100;
        }

        function formatMoneyInputValue(value) {
            let numeric = typeof value === 'string' ? parseCurrencyValue(value) : (Number(value) || 0);
            return roundMoney(numeric).toFixed(2);
        }

        function parseCurrencyValue(value) {
            if (typeof value === 'number') {
                return Math.max(0, Number.isFinite(value) ? value : 0);
            }

            let normalized = String(value || '')
                .replace(/[^\d,.-]/g, '')
                .replace(/(?!^)-/g, '')
                .trim();

            if (!normalized || normalized === '-' || normalized === ',' || normalized === '.') {
                return 0;
            }

            let negative = normalized.startsWith('-');
            normalized = normalized.replace(/-/g, '');

            if (normalized.includes(',')) {
                normalized = normalized.replace(/\./g, '').replace(',', '.');
            } else {
                let parts = normalized.split('.');
                if (parts.length > 2 || (parts.length === 2 && parts[1].length === 3)) {
                    normalized = parts.join('');
                }
            }

            let parsed = Number(`${negative ? '-' : ''}${normalized}`);
            return Math.max(0, Number.isFinite(parsed) ? parsed : 0);
        }

        function moneyEditValue(value) {
            let numeric = parseCurrencyValue(value);
            let fixed = formatMoneyInputValue(numeric);
            return fixed.endsWith('.00') ? fixed.slice(0, -3) : fixed;
        }

        function invoiceMoneyInput(name) {
            return $(`input[name="${name}"].invoice-money, input[data-invoice-field="${name}"].invoice-money`);
        }

        function setMoneyInput(name, value) {
            setMoneyElement(invoiceMoneyInput(name), value);
        }

        function getMoneyInput(name) {
            return parseCurrencyValue(invoiceMoneyInput(name).first().val());
        }

        function setMoneyElement(input, value) {
            input.val(formatRupiah(value)).data('raw-value', formatMoneyInputValue(value));
        }

        function updateInvoicePaymentUi(payableTotal, paid, debt, status) {
            let paidPercent = payableTotal > 0 ? Math.min(100, (paid / payableTotal) * 100) : 0;

            $('#invoiceBoardTotal').text(formatRupiah(payableTotal));
            $('#invoiceBoardHint').text(payableTotal > 0
                ? `${paymentStatusLabel(status)} · Dibayar ${formatRupiah(paid)} · Sisa ${formatRupiah(debt)} (${paidPercent.toFixed(0)}%)`
                : 'Nilai dihitung otomatis dari item yang diterima.');
            $('#invoicePaymentStatusText').text(`Status: ${paymentStatusLabel(status)}`);

            $('.receive-payment-action').removeClass('is-active');
            if (status === 'belum_dibayar') {
                $('.receive-payment-action[data-payment-action="none"]').addClass('is-active');
            } else if (status === 'sebagian' && payableTotal > 0 && Math.abs(paid - (payableTotal / 2)) < 0.01) {
                $('.receive-payment-action[data-payment-action="half"]').addClass('is-active');
            } else if (status === 'lunas') {
                $('.receive-payment-action[data-payment-action="full"]').addClass('is-active');
            }
        }

        function serializePenerimaanForm() {
            let form = $('#penerimaanForm');
            let originals = [];

            form.find('.invoice-money, .receive-price').each(function() {
                let input = $(this);
                originals.push([input, input.val()]);
                input.val(formatMoneyInputValue(parseCurrencyValue(input.val())));
            });

            let payload = form.serialize();

            originals.forEach(function(item) {
                item[0].val(item[1]);
            });

            return payload;
        }

        function paymentStatusLabel(status) {
            const labels = {
                belum_dibayar: 'Belum Dibayar',
                sebagian: 'Sebagian',
                lunas: 'Lunas'
            };

            return labels[status] || '-';
        }

        function paymentStatusFromAmounts(total, paid) {
            total = Number(total) || 0;
            paid = Number(paid) || 0;

            if (total <= 0 || paid <= 0) {
                return 'belum_dibayar';
            }

            return paid >= total ? 'lunas' : 'sebagian';
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

            return `${batch.no_batch || '-'} | ED ${formatDateDisplay(batch.expired_date)} | Diskon ${formatDecimal(batch.diskon, 0, 2)}% | PPN ${formatDecimal(batch.ppn, 0, 2)}% | Stok ${formatDecimal(batch.qty, 0, 2)}`;
        }

        function normalizedPercent(value) {
            return roundMoney(Math.min(100, Math.max(0, Number(value) || 0)));
        }

        function normalizedDiscount(value) {
            return normalizedPercent(value);
        }

        function tieredDiscountNet(grossAmount, discount1, discount2, discount3) {
            let netAmount = Math.max(0, Number(grossAmount) || 0);

            [discount1, discount2, discount3].forEach(function(discount) {
                netAmount *= 1 - (normalizedDiscount(discount) / 100);
            });

            return roundMoney(netAmount);
        }

        function effectiveTieredDiscount(discount1, discount2, discount3) {
            let remaining = tieredDiscountNet(100, discount1, discount2, discount3);

            return Number((100 - remaining).toFixed(2));
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
                    let inputFormat = /^\d{2}-\d{2}-\d{4}$/.test(value) ? 'd-m-Y' : 'Y-m-d';
                    picker.setDate(value, false, inputFormat);
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
            let rowDiscount = normalizedDiscount(row.data('diskon-efektif'));
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
                    .text(`Batch existing diskon ${formatDecimal(meta.diskon, 0, 2)}%, PPN ${formatDecimal(meta.ppn, 0, 2)}%, stok ${formatDecimal(meta.qty, 0, 2)}${meta.expired ? ', ED ' + formatDateDisplay(meta.expired) : ''}.`);
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

        function updateReceiveGuidance(hasPo, itemStats, hasInvoice) {
            let headerIcon = 'mdi-file-clock-outline';
            let headerLabel = 'Draft sebelum posting stok';

            if (!hasPo) {
                $('#receiveHeaderStatus').html(`<i class="mdi ${headerIcon}"></i> ${headerLabel}`);
                return;
            }

            if (itemStats.itemCount <= 0) {
                headerIcon = 'mdi-package-variant-closed';
                headerLabel = 'Menunggu detail barang';
            } else if (itemStats.invalidRows > 0) {
                headerIcon = 'mdi-alert-circle-outline';
                headerLabel = 'Perlu cek detail barang';
            } else if (!hasInvoice) {
                headerIcon = 'mdi-receipt-text-outline';
                headerLabel = 'Menunggu faktur';
            } else {
                headerIcon = 'mdi-check-decagram-outline';
                headerLabel = 'Siap simpan draft';
            }

            $('#receiveHeaderStatus').html(`<i class="mdi ${headerIcon}"></i> ${headerLabel}`);
        }

        function invoiceLockMessage(hasPo, itemStats) {
            if (!hasPo) {
                return 'Pilih PO approved terlebih dahulu.';
            }

            if (itemStats.itemCount <= 0) {
                return 'Isi minimal satu qty barang yang diterima.';
            }

            return 'Lengkapi batch atau expired date pada item yang diterima.';
        }

        function setInvoiceLockState(locked, message) {
            let section = $('#receiveInvoiceSection');
            section.toggleClass('is-locked', locked);
            $('#receiveInvoiceLockNotice')
                .toggleClass('d-none', !locked)
                .find('span')
                .text(message || 'Isi detail barang terlebih dahulu.');

            section
                .find('input:not([readonly]), select, button')
                .prop('disabled', locked);
        }

        function updateFormProgress() {
            let hasPo = Boolean($('#purchase_order_id').val());
            let hasInvoice = Boolean($('input[name="nomor_faktur"]').val()?.trim()) &&
                Boolean($('input[name="tanggal_faktur"]').val()?.trim());
            let itemStats = collectReceiveStats();
            let hasValidItem = itemStats.itemCount > 0 && itemStats.invalidRows === 0;
            let itemState = '';
            let invoiceState = '';

            if (hasValidItem) {
                itemState = 'is-complete';
            } else if (itemStats.itemCount > 0) {
                itemState = 'is-warning';
            } else if (hasPo) {
                itemState = 'is-active';
            }

            if (hasValidItem && hasInvoice) {
                invoiceState = 'is-complete';
            } else if (hasValidItem) {
                invoiceState = 'is-active';
            }

            setProgressStep('#receiveStepPo', hasPo ? 'is-complete' : 'is-active', hasPo ? 'PO dipilih' :
                'Belum dipilih');
            setProgressStep('#receiveStepItems', itemState,
                hasValidItem ? `${itemStats.itemCount} item lengkap` : (itemStats.itemCount > 0 ?
                    'Lengkapi batch/expired' : (hasPo ? 'Isi qty diterima' : 'Menunggu PO')));
            setProgressStep('#receiveStepInvoice', invoiceState,
                hasInvoice && hasValidItem ? 'Faktur siap' : (hasValidItem ? 'Siap diisi' : 'Menunggu detail'));

            setInvoiceLockState(!hasValidItem, invoiceLockMessage(hasPo, itemStats));
            let canUseCompensation = hasValidItem && supplierCompensationAvailable > 0;
            $('#applySupplierCompensation').prop('disabled', !canUseCompensation);
            $('#supplierCompensationDiscount').prop(
                'disabled',
                !canUseCompensation || !$('#applySupplierCompensation').is(':checked')
            );
            updateReceiveGuidance(hasPo, itemStats, hasInvoice);

            $('#submitPenerimaanForm')
                .toggleClass('btn-primary', hasValidItem && hasInvoice)
                .toggleClass('btn-outline-primary', !(hasValidItem && hasInvoice));
        }

        function resetPenerimaanForm(generateNumber = true) {
            let form = $('#penerimaanForm');
            form.trigger('reset');
            paymentPreset = 'none';
            currentPoAdditionalCost = 0;
            currentPoTotalQty = 0;
            receiptInvoiceMode = '';
            $('#receiveInvoiceModeNotice, #submitPenerimaanNextInvoice').addClass('d-none');
            $('#penerimaan_id').val('');
            $('#purchase_order_id, #receive_selected_po').val('');
            $('#receive_supplier').val('');
            hideSupplierCompensationAlert();
            $('#receivePoSummary').addClass('d-none');
            $('#receiveInvoiceHistory').addClass('d-none');
            $('#receiveInvoiceHistoryList').empty();
            $('#receiveInvoiceHistoryCount').text('0 faktur');
            $('#receiveInvoiceHistoryEmpty').removeClass('d-none');
            $('#receiveDetailRows').empty();
            updateReceiveItemSearch();
            $('#receiveDetailEditor').addClass('d-none');
            $('#receiveDetailEmpty').removeClass('d-none');
            $('#receiveDetailLiveSummary').addClass('d-none');
            $('#receiveLineCount, #receiveModalItemCount, #receiveModalQtyCount').text('0');
            $('#receiveReadyRows, #receiveWarningRows').text('0 item');
            $('#receiveLiveQty').text('0');
            $('#receiveLiveValue').text(formatRupiah(0));
            $('#summaryItemPo, #summaryOutstandingQty, #summaryFilledQty').text('0');
            $('#summaryReceivePercent').text('0%');
            $('#summaryReceiveMeter').css('width', '0%');
            $('#receiveGrandTotal').text(formatRupiah(0));
            $('#receivePoSummary, #receiveInvoiceSection').removeClass('is-mobile-details-open');
            $('.receive-mobile-section-toggle')
                .attr('aria-expanded', 'false')
                .find('i')
                .removeClass('mdi-chevron-up')
                .addClass('mdi-chevron-down');
            ['subtotal', 'diskon', 'pajak', 'biaya_lain', 'supplier_compensation_discount', 'total_faktur', 'jumlah_dibayar', 'sisa_hutang'].forEach(function(field) {
                setMoneyInput(field, 0);
            });
            $('input[name="tanggal_faktur"]').val(moment().format('DD-MM-YYYY'));
            $('input[name="tanggal_jatuh_tempo"]').val('');
            $('input[name="status_pembayaran"]').val('belum_dibayar');
            updateInvoicePaymentUi(0, 0, 0, 'belum_dibayar');
            $('#penerimaanModalLabel').text('Form Penerimaan Barang');
            $('#submitPenerimaanForm').html('<i class="mdi mdi-content-save-outline"></i> Simpan Draft');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();
            updateFormProgress();

            if (generateNumber) {
                $.get('{{ route("penerimaan.generateNoPenerimaan") }}', function(response) {
                    $('#nomor_penerimaan').val(response);
                });
            }
        }

        $('#penerimaanModal').on('show.bs.modal', function() {
            if (!editMode) {
                resetPenerimaanForm();
                if (pendingReceiptPo) {
                    receiptInvoiceMode = pendingInvoiceMode;
                    pendingInvoiceMode = '';
                    applyPurchaseOrder(pendingReceiptPo);
                    renderPoDetails(pendingReceiptPo);
                    $('#receiveInvoiceModeNotice').removeClass('d-none').text(receiptInvoiceMode === 'split'
                        ? 'Pecah Faktur: isi hanya barang dan qty pada faktur ini. Qty 0 tidak disimpan. Sisa PO tersedia untuk faktur berikutnya; setiap faktur memiliki batch, pembayaran, dan posting sendiri.'
                        : 'Satu faktur: seluruh sisa qty PO sudah terisi. Periksa qty, batch, dan rincian faktur sebelum menyimpan.');
                    $('#submitPenerimaanNextInvoice').toggleClass('d-none', receiptInvoiceMode !== 'split');
                    pendingReceiptPo = null;
                }
            }
        });

        $('#penerimaanModal').on('hidden.bs.modal', function() {
            editMode = false;
            $('#receivePoSummary, #receiveInvoiceSection').removeClass('is-mobile-details-open');
            $('.receive-mobile-section-toggle')
                .attr('aria-expanded', 'false')
                .find('i')
                .removeClass('mdi-chevron-up')
                .addClass('mdi-chevron-down');
        });

        function applyPurchaseOrder(po) {
            $('#purchase_order_id').val(po.id);
            $('#receive_selected_po').val(po.no_po || '-');
            renderPoSummary(po);
            renderReceivedInvoices(po.received_invoices || []);
        }

        function setReceivedInvoiceHistoryOpen(isOpen) {
            $('#receiveInvoiceHistoryBody').toggleClass('d-none', !isOpen);
            let button = $('#receiveInvoiceHistoryToggle');
            button.attr('aria-expanded', isOpen ? 'true' : 'false')
                .attr('aria-label', `${isOpen ? 'Tutup' : 'Buka'} riwayat barang diterima di faktur terpisah`);
            button.find('span').text(isOpen ? 'Tutup' : 'Buka');
            button.find('i')
                .toggleClass('mdi-chevron-up', isOpen)
                .toggleClass('mdi-chevron-down', !isOpen);
        }

        $('#receiveInvoiceHistoryToggle').on('click', function() {
            setReceivedInvoiceHistoryOpen($(this).attr('aria-expanded') !== 'true');
        });

        function renderReceivedInvoices(invoices) {
            setReceivedInvoiceHistoryOpen(!window.matchMedia('(max-width: 767.98px)').matches);
            $('#receiveInvoiceHistory').removeClass('d-none');
            $('#receiveInvoiceHistoryCount').text(`${invoices.length.toLocaleString('id-ID')} faktur`);
            $('#receiveInvoiceHistoryEmpty').toggleClass('d-none', invoices.length > 0);
            $('#receiveInvoiceHistoryList').html(invoices.map(invoice => `
                <article class="receive-history-card">
                    <div class="receive-history-card-header">
                        <div class="receive-history-reference">
                            <strong>Faktur ${escapeHtml(invoice.nomor_faktur || '-')}</strong>
                            <small>${escapeHtml(invoice.nomor_penerimaan || '-')} · Tanggal faktur ${escapeHtml(formatDateDisplay(invoice.tanggal_faktur))} · Diterima ${escapeHtml(formatDateDisplay(invoice.tanggal_penerimaan))}</small>
                        </div>
                        <div class="receive-history-status">
                            ${statusBadge(invoice.status)}
                            <small>${invoice.status === 'posted' ? 'Sudah masuk stok' : 'Belum masuk stok'}</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table receive-history-table receive-mobile-card-table align-middle mb-0">
                            <thead>
                                <tr><th scope="col">Barang</th><th scope="col">Qty Diterima</th><th scope="col">No Batch</th><th scope="col">Expired Date</th></tr>
                            </thead>
                            <tbody>${(invoice.details || []).map(item => `
                                <tr>
                                    <td data-mobile-label="Barang"><strong>${escapeHtml(item.nama_obat || '-')}</strong><small>${escapeHtml(item.kode_obat || '-')}</small></td>
                                    <td data-mobile-label="Qty Diterima">${formatDecimal(item.qty_diterima)} ${escapeHtml(item.satuan || '-')}</td>
                                    <td data-mobile-label="No Batch">${escapeHtml(item.no_batch || '-')}</td>
                                    <td data-mobile-label="Expired Date">${escapeHtml(formatDateDisplay(item.expired_date))}</td>
                                </tr>
                            `).join('')}</tbody>
                        </table>
                    </div>
                </article>
            `).join(''));
        }

        function pickerSearchTokens(query) {
            return String(query || '').toLocaleLowerCase('id-ID').replace(/"/g, '').split(/\s+/).filter(Boolean);
        }

        function pickerMedicineSearchText(po) {
            return (po.medicines || []).map(medicine => `${medicine.nama_obat} ${medicine.kode_obat}`).join(' ');
        }

        function pickerMedicineMatches(po) {
            return pickerMedicineId ? (po.medicines || []).filter(medicine =>
                String(medicine.id) === pickerMedicineId) : [];
        }

        function updatePickerMedicineOptions(orders) {
            pickerMedicines = new Map();
            orders.forEach(po => (po.medicines || []).forEach(medicine => {
                pickerMedicines.set(String(medicine.id), medicine);
            }));
            let select = $('#receivePoPickerSearch').empty().append(new Option('', ''));
            [...pickerMedicines.values()]
                .sort((left, right) => `${left.nama_obat} ${left.kode_obat}`.localeCompare(`${right.nama_obat} ${right.kode_obat}`, 'id-ID'))
                .forEach(medicine => select.append(new Option(`${medicine.nama_obat} (${medicine.kode_obat})`, String(medicine.id))));
            select.val(pickerMedicines.has(pickerMedicineId) ? pickerMedicineId : null).trigger('change');
        }

        function highlightPickerText(value, query) {
            let tokens = pickerSearchTokens(query).map(token => token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
            if (!tokens.length) return escapeHtml(value);
            return String(value ?? '-').split(new RegExp(`(${tokens.join('|')})`, 'gi'))
                .map((part, index) => index % 2 ? `<mark>${escapeHtml(part)}</mark>` : escapeHtml(part)).join('');
        }

        function renderPickerMedicines(po, query) {
            let medicines = po.medicines || [];
            let matches = pickerMedicineMatches(po);
            let source = matches.length ? matches : medicines;
            let visible = source.slice(0, matches.length ? 3 : 2);
            let preview = visible.map(medicine => `
                <span class="receive-po-picker-medicine ${matches.length ? 'is-match' : ''}">
                    <strong>${highlightPickerText(medicine.nama_obat, query)}</strong>
                    <small>${highlightPickerText(medicine.kode_obat, query)} &middot; Sisa ${formatDecimal(medicine.outstanding_qty)} ${escapeHtml(medicine.satuan)}</small>
                </span>`).join('');
            return `
                <div class="receive-po-picker-item-meta"><strong>${formatDecimal(po.item_count)} item</strong>
                    <span>Sisa ${formatDecimal(po.outstanding_qty)} qty</span>
                    ${matches.length ? '<span class="receive-po-picker-match-label"><i class="mdi mdi-pill"></i> Obat ditemukan</span>' : ''}
                </div>
                <div class="receive-po-picker-medicines">${preview}</div>
                ${source.length > visible.length ? `<small class="receive-po-picker-more">+${source.length - visible.length} obat ${matches.length ? 'cocok lainnya' : 'lainnya'} &middot; lihat detail</small>` : ''}`;
        }

        function updatePickerResults(table) {
            let rows = table.rows({ search: 'applied' }).data().toArray();
            let filters = [];
            if (pickerOrderQuery) filters.push(`Pencarian "${pickerOrderQuery}"`);
            if (pickerMedicineId) filters.push(`Memuat obat "${pickerQuery}"`);
            $('#receivePoPickerResultCount').text(filters.length
                ? `${formatDecimal(rows.length)} PO sesuai pencarian`
                : `${formatDecimal(rows.length)} pembelian tersedia`);
            $('#receivePoPickerResultHint').text(filters.length
                ? filters.join(' · ')
                : 'Pilih satu PO untuk melanjutkan.');
        }

        function updatePickerOverview(orders) {
            $('#receivePickerOrderCount').text(formatDecimal(orders.length));
            $('#receivePickerOutstandingCount').text(formatDecimal(orders.reduce((total, po) => total + Number(po.outstanding_qty || 0), 0)));
            $('#receivePickerEstimatedValue').text(formatRupiah(orders.reduce((total, po) => total + Number(po.total_estimasi || 0), 0)));
        }

        $('#receivePoPickerSearch').select2({
            dropdownParent: $('#receivePoPickerModal'),
            width: '100%',
            placeholder: 'Cari / pilih nama atau kode obat...',
            allowClear: true,
            language: {
                noResults: () => 'Obat tidak ditemukan di PO tersedia.',
                searching: () => 'Mencari obat...'
            }
        });

        $.fn.dataTable.ext.search.push(function(settings, searchData, dataIndex, row) {
            if (settings.nTable.id !== 'receivePoPickerTable' || !pickerMedicineId) return true;
            return pickerMedicineMatches(row).length > 0;
        });

        let pickerTable = null;
        pickerTable = $('#receivePoPickerTable').DataTable({
            data: [],
            autoWidth: false,
            pageLength: 10,
            dom: 'rtip',
            order: [[2, 'desc']],
            columns: [
                { data: 'no_po', render: (value, type, row) => type === 'display' ? `
                    <div class="receive-po-picker-reference"><span><i class="mdi mdi-file-document-outline"></i></span>
                        <strong>${highlightPickerText(value, pickerOrderQuery)}</strong></div>
                    <span class="receive-po-picker-approved"><i class="mdi mdi-check-circle-outline"></i> ${row.status === 'diterima_sebagian' ? 'Diterima sebagian' : 'Disetujui'}</span>` : value },
                { data: 'supplier', render: (value, type, row) => type === 'display'
                    ? `<strong class="receive-po-picker-supplier">${highlightPickerText(value, pickerOrderQuery)}</strong>
                        <small class="receive-po-picker-branch"><i class="mdi mdi-map-marker-outline"></i> ${highlightPickerText(row.branch, pickerOrderQuery)}</small>`
                    : `${value} ${row.branch}` },
                { data: 'tanggal_po', render: (value, type) => type === 'display'
                    ? highlightPickerText(formatDateDisplay(value), pickerOrderQuery)
                    : type === 'filter' ? `${value} ${formatDateDisplay(value)}` : value },
                { data: 'outstanding_qty', render: (value, type, row) => type === 'display'
                    ? renderPickerMedicines(row, `${pickerOrderQuery} ${pickerQuery}`) : type === 'filter' ? pickerMedicineSearchText(row) : value },
                { data: 'total_estimasi', render: (value, type) => type === 'display'
                    ? `<strong class="receive-po-picker-value">${formatRupiah(value)}</strong>` : value },
                { data: 'id', orderable: false, searchable: false, render: () => `
                    <div class="receive-po-picker-row-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary receive-po-picker-action" data-action="detail">
                            <i class="mdi mdi-eye-outline"></i> Detail
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary receive-po-picker-action" data-action="select" aria-pressed="false">
                            <i class="mdi mdi-check-circle-outline"></i> Pilih
                        </button>
                    </div>` }
            ],
            columnDefs: [{
                targets: '_all',
                createdCell: function(cell, value, row, rowIndex, columnIndex) {
                    $(cell).attr('data-label', ['Pembelian', 'Supplier / Cabang', 'Tanggal PO', 'Obat & Sisa Qty', 'Total Estimasi', 'Aksi'][columnIndex]);
                }
            }],
            language: {
                emptyTable: '<div class="receive-po-picker-empty"><i class="mdi mdi-file-check-outline"></i><strong>Belum ada PO siap diterima</strong><span>Pembelian disetujui yang masih bersisa akan tampil di sini.</span></div>',
                zeroRecords: '<div class="receive-po-picker-empty"><i class="mdi mdi-magnify"></i><strong>PO belum ditemukan</strong><span>Ubah kata pencarian atau hapus filter obat untuk melihat PO lainnya.</span></div>',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ pembelian',
                infoEmpty: 'Tidak ada pembelian',
                infoFiltered: '(dari _MAX_ pembelian)',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            },
            drawCallback: function() {
                updatePickerSelection();
                updatePickerResults(this.api());
            }
        });

        function showPickerMessage(message, isError = false) {
            $('#receivePoPickerMessage').text(message)
                .toggleClass('d-none', !message)
                .toggleClass('alert-danger', isError)
                .toggleClass('alert-info', !isError);
        }

        function updatePickerSelection() {
            $('#receivePoPickerSelection').text(pickerSelection
                ? `${pickerSelection.no_po} · ${pickerSelection.supplier}`
                : 'Belum ada pembelian dipilih');
            $('#receivePoPickerSelectionPanel').toggleClass('has-selection', Boolean(pickerSelection));
            $('#receivePoPickerSelectionPanel .receive-po-picker-selection-icon i')
                .toggleClass('mdi-file-document-outline', !pickerSelection)
                .toggleClass('mdi-check-circle-outline', Boolean(pickerSelection));
            $('#receivePoPickerInvoiceOptions').toggleClass('d-none', !pickerSelection);
            $('#receivePoPickerInvoiceHint').text(pickerSelection
                ? `Pilih cara pencatatan faktur untuk ${pickerSelection.no_po}.`
                : 'Pilih cara pencatatan faktur untuk PO terpilih.');
            $('input[name="receive_invoice_mode"]').prop('disabled', pickerBusy || !pickerSelection)
                .each(function() {
                    $(this).prop('checked', this.value === pickerInvoiceMode);
                });
            $('.receive-invoice-option').each(function() {
                $(this).toggleClass('is-selected', $(this).find('input').is(':checked'));
            });
            $('#receivePoPickerCreate').prop('disabled', pickerBusy || !pickerSelection || !pickerInvoiceMode);
            $('#receivePoPickerOrderSearch, #receivePoPickerClearSearch').prop('disabled', pickerBusy);
            $('#receivePoPickerSearch').prop('disabled', pickerBusy || !pickerMedicines.size);
            $('#receivePoPickerRefresh, .receive-po-picker-action').prop('disabled', pickerBusy);
            $('#receivePoPickerSelectDetail').prop('disabled', pickerBusy || !pickerDetail?.can_create_receipt);
            $('#receivePoPickerTable tbody tr').each(function() {
                let row = pickerTable?.row(this).data();
                let selected = Boolean(row && pickerSelection && Number(row.id) === Number(pickerSelection.id));
                $(this).toggleClass('is-selected', selected)
                    .find('[data-action="select"]').attr('aria-pressed', String(selected))
                    .toggleClass('btn-primary', selected).toggleClass('btn-outline-primary', !selected)
                    .html(`<i class="mdi mdi-check-circle-outline"></i> ${selected ? 'Dipilih' : 'Pilih'}`);
            });
        }

        function selectPickerPurchaseOrder(po) {
            if (Number(pickerSelection?.id) !== Number(po.id)) {
                pickerInvoiceMode = '';
            }
            pickerSelection = po;
            updatePickerSelection();
            document.getElementById('receivePoPickerInvoiceOptions')?.scrollIntoView({
                behavior: 'smooth', block: 'nearest'
            });
        }

        $('input[name="receive_invoice_mode"]').on('change', function() {
            pickerInvoiceMode = this.value;
            updatePickerSelection();
        });

        function showPickerList() {
            $('#receivePoPickerDetail').addClass('d-none');
            $('#receivePoPickerList').removeClass('d-none');
            $('#receivePickerStepList').addClass('is-active');
            $('#receivePickerStepDetail').removeClass('is-active');
            pickerTable.columns.adjust();
        }

        function loadPickerOrders() {
            pickerListRequest?.abort();
            pickerDetailRequest?.abort();
            pickerSelection = null;
            pickerInvoiceMode = '';
            pickerDetail = null;
            pickerBusy = true;
            $('#receivePoPickerSearch').select2('close');
            showPickerList();
            pickerTable.clear().draw();
            updatePickerOverview([]);
            $('.receive-po-picker-overview').addClass('is-loading');
            showPickerMessage('Memuat pembelian disetujui...');
            updatePickerSelection();
            pickerListRequest = $.get('{{ route("penerimaan.approvedPo") }}').done(function(orders) {
                pickerBusy = false;
                updatePickerOverview(orders || []);
                $('.receive-po-picker-overview').removeClass('is-loading');
                pickerTable.rows.add(orders || []);
                updatePickerMedicineOptions(orders || []);
                showPickerMessage('');
                updatePickerSelection();
            }).fail(function(xhr) {
                if (xhr.statusText === 'abort') return;
                pickerBusy = false;
                updatePickerMedicineOptions([]);
                $('.receive-po-picker-overview').removeClass('is-loading');
                showPickerMessage('Pembelian gagal dimuat. Klik muat ulang untuk mencoba kembali.', true);
                updatePickerSelection();
            });
        }

        $('#receivePoPickerModal').on('show.bs.modal', function() {
            $('#receivePoPickerOrderSearch').val('').trigger('input');
            $('#receivePoPickerSearch').val(null).trigger('change');
            loadPickerOrders();
        }).on('shown.bs.modal', function() {
            pickerTable.columns.adjust();
            if (!window.matchMedia('(max-width: 767.98px)').matches) {
                $('#receivePoPickerOrderSearch').trigger('focus');
            }
        }).on('hidden.bs.modal', function() {
            $('#receivePoPickerSearch').select2('close');
            pickerListRequest?.abort();
            pickerDetailRequest?.abort();
            pickerCreateRequest?.abort();
        });

        $('#receivePoPickerOrderSearch').on('input', function() {
            pickerOrderQuery = this.value.trim();
            $('#receivePoPickerClearSearch').toggleClass('d-none', !this.value);
            pickerTable.search(pickerOrderQuery, false, true, true).rows().invalidate('data').draw();
        });
        $('#receivePoPickerClearSearch').on('click', function() {
            $('#receivePoPickerOrderSearch').val('').trigger('input').trigger('focus');
        });
        $('#receivePoPickerSearch').on('change', function() {
            pickerMedicineId = this.value || '';
            pickerQuery = pickerMedicines.get(pickerMedicineId)?.nama_obat || '';
            pickerTable.search(pickerOrderQuery, false, true, true).rows().invalidate('data').draw();
        });
        $('#receivePoPickerRefresh').on('click', loadPickerOrders);
        $('#receivePoPickerBack').on('click', showPickerList);
        $('#receivePoPickerSelectDetail').on('click', function() {
            if (!pickerDetail?.can_create_receipt || pickerBusy) return;
            selectPickerPurchaseOrder(pickerDetail);
        });

        $('#receivePoPickerTable').on('click', '.receive-po-picker-action', function() {
            if (pickerBusy) return;
            let po = pickerTable.row($(this).closest('tr')).data();
            if ($(this).data('action') === 'select') {
                selectPickerPurchaseOrder(po);
                return;
            }
            pickerDetailRequest?.abort();
            pickerBusy = true;
            updatePickerSelection();
            showPickerMessage('Memuat detail pembelian...');
            pickerDetailRequest = $.get('{{ route("penerimaan.purchaseOrderDetail", ":id") }}'.replace(':id', po.id))
                .done(function(detail) {
                    pickerDetail = detail;
                    $('#receivePickerDetailNumber').text(detail.no_po || '-');
                    $('#receivePickerDetailSupplier').text(detail.supplier || '-');
                    $('#receivePickerDetailBranch').text(detail.branch || '-');
                    $('#receivePickerDetailDate').text(formatDateDisplay(detail.tanggal_po));
                    $('#receivePickerDetailInsurance').text(formatRupiah(detail.biaya_asuransi));
                    $('#receivePickerDetailShipping').text(formatRupiah(detail.biaya_pengiriman));
                    $('#receivePickerDetailTotal').text(formatRupiah(detail.total_estimasi));
                    $('#receivePickerDetailNotes').text(detail.catatan || '-');
                    $('#receivePickerDetailRows').html((detail.details || []).map(item => `
                        <tr>
                            <td data-mobile-label="Obat"><strong>${highlightPickerText(item.nama_obat, `${pickerOrderQuery} ${pickerQuery}`)}</strong><small class="d-block text-muted">${highlightPickerText(item.kode_obat, `${pickerOrderQuery} ${pickerQuery}`)}</small></td>
                            <td data-mobile-label="Satuan">${escapeHtml(item.satuan)}</td>
                            <td data-mobile-label="Qty Pembelian">${formatDecimal(item.qty_po)}</td>
                            <td data-mobile-label="Qty dalam Penerimaan">${formatDecimal(item.received_qty)}</td>
                            <td data-mobile-label="Sisa Qty">${formatDecimal(item.outstanding_qty)}</td>
                            <td data-mobile-label="Harga Estimasi">${formatRupiah(item.harga_estimasi)}</td>
                            <td data-mobile-label="Diskon 1 / 2 / 3">${formatDecimal(item.diskon_1)}% / ${formatDecimal(item.diskon_2)}% / ${formatDecimal(item.diskon_3)}%</td>
                            <td data-mobile-label="PPN">${formatDecimal(item.ppn)}%</td>
                        </tr>`).join(''));
                    $('#receivePoPickerList').addClass('d-none');
                    $('#receivePoPickerDetail').removeClass('d-none');
                    $('#receivePickerStepList').removeClass('is-active');
                    $('#receivePickerStepDetail').addClass('is-active');
                    showPickerMessage(detail.can_create_receipt ? ''
                        : 'Pembelian ini sudah berubah atau tidak lagi tersedia. Muat ulang daftar pembelian.', !detail.can_create_receipt);
                }).fail(function(xhr) {
                    if (xhr.statusText === 'abort') return;
                    showPickerMessage(xhr.responseJSON?.message || 'Detail pembelian gagal dimuat. Silakan coba kembali.', true);
                }).always(function() {
                    pickerBusy = false;
                    updatePickerSelection();
                });
        });

        $('#receivePoPickerCreate').on('click', function() {
            if (!pickerSelection || !pickerInvoiceMode || pickerBusy) return;
            pickerDetailRequest?.abort();
            pickerBusy = true;
            updatePickerSelection();
            showPickerMessage('Menyiapkan form penerimaan...');
            pickerCreateRequest = $.get('{{ route("penerimaan.purchaseOrderDetail", ":id") }}'.replace(':id', pickerSelection.id))
                .done(function(po) {
                    if (!po.can_create_receipt) {
                        pickerSelection = null;
                        showPickerMessage('Pembelian ini sudah berubah atau tidak lagi tersedia. Muat ulang daftar pembelian.', true);
                        return;
                    }
                    pendingReceiptPo = po;
                    pendingInvoiceMode = pickerInvoiceMode;
                    editMode = false;
                    $('#receivePoPickerModal').one('hidden.bs.modal', function() {
                        $('#penerimaanModal').modal('show');
                    }).modal('hide');
                }).fail(function(xhr) {
                    if (xhr.statusText === 'abort') return;
                    showPickerMessage(xhr.responseJSON?.message || 'Form penerimaan gagal disiapkan. Silakan coba kembali.', true);
                }).always(function() {
                    pickerBusy = false;
                    updatePickerSelection();
                });
        });

        function renderPoSummary(po) {
            currentPoAdditionalCost = Number(po.total_biaya_tambahan || 0);
            currentPoTotalQty = Number(po.total_qty_po || 0);
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
            renderSupplierCompensationAlert(po.supplier_compensation_alert || {});
            updateFormProgress();
        }

        function hideSupplierCompensationAlert() {
            supplierCompensationAvailable = 0;
            $('#supplierCompensationAlert').addClass('d-none').removeClass('is-overdue');
            $('#supplierCompensationRows').empty();
            $('#supplierCompensationReturnCount, #supplierCompensationOverdue').text('0');
            $('#supplierCompensationOutstanding').text(formatRupiah(0));
            $('#supplierCompensationMore').addClass('d-none').text('');
            $('#supplierCompensationApplyPanel').addClass('d-none');
            $('#applySupplierCompensation').prop('checked', false);
            $('#supplierCompensationDiscount').prop('disabled', true);
            setMoneyInput('supplier_compensation_discount', 0);
            $('#supplierCompensationAvailable, #supplierCompensationMax').text(formatRupiah(0));
        }

        function supplierCompensationStatus(status) {
            const statuses = {
                waiting: {
                    label: 'Menunggu',
                    className: 'is-waiting',
                    icon: 'mdi-clock-alert-outline'
                },
                partial: {
                    label: 'Diganti sebagian',
                    className: 'is-partial',
                    icon: 'mdi-progress-clock'
                },
                overdue: {
                    label: 'Jatuh tempo',
                    className: 'is-overdue',
                    icon: 'mdi-alert-circle-outline'
                }
            };

            return statuses[String(status || '').toLowerCase()] || statuses.waiting;
        }

        function renderSupplierCompensationAlert(alert) {
            hideSupplierCompensationAlert();

            if (!alert.has_outstanding || !Array.isArray(alert.returns) || !alert.returns.length) {
                return;
            }

            let returnCount = Number(alert.return_count || alert.returns.length);
            let overdueCount = Number(alert.overdue_count || 0);
            supplierCompensationAvailable = Number(alert.outstanding_value || 0);
            let rows = alert.returns.map(function(item) {
                let status = supplierCompensationStatus(item.status);
                let dueDate = item.due_date ? formatDateDisplay(item.due_date) : 'Belum ditentukan';
                let notes = item.notes
                    ? `<small class="d-block text-muted">${escapeHtml(item.notes)}</small>`
                    : '';

                return `
                    <tr>
                        <td data-mobile-label="Nomor Retur">
                            <strong>${escapeHtml(item.nomor_retur || '-')}</strong>
                            ${notes}
                        </td>
                        <td data-mobile-label="Branch">${escapeHtml(item.branch || '-')}</td>
                        <td data-mobile-label="Tanggal / Batas">
                            <strong>${formatDateDisplay(item.tanggal_retur)}</strong>
                            <small class="d-block ${item.status === 'overdue' ? 'text-danger' : 'text-muted'}">Batas ${escapeHtml(dueDate)}</small>
                        </td>
                        <td data-mobile-label="Status">
                            <span class="supplier-compensation-status ${status.className}">
                                <i class="mdi ${status.icon}"></i> ${status.label}
                            </span>
                        </td>
                        <td data-mobile-label="Sudah Diganti">
                            <strong>${formatRupiah(item.received_value)}</strong>
                            <small class="d-block text-muted">dari ${formatRupiah(item.expected_value)}</small>
                        </td>
                        <td data-mobile-label="Sisa"><strong class="text-danger">${formatRupiah(item.outstanding_value)}</strong></td>
                    </tr>
                `;
            }).join('');

            $('#supplierCompensationRows').html(rows);
            $('#supplierCompensationReturnCount').text(returnCount.toLocaleString('id-ID'));
            $('#supplierCompensationOutstanding').text(formatRupiah(alert.outstanding_value));
            $('#supplierCompensationOverdue').text(overdueCount.toLocaleString('id-ID'));
            $('#supplierCompensationAlert')
                .removeClass('d-none')
                .toggleClass('is-overdue', overdueCount > 0);
            $('#supplierCompensationApplyPanel').removeClass('d-none');
            $('#supplierCompensationAvailable').text(formatRupiah(supplierCompensationAvailable));

            let hiddenCount = Math.max(0, returnCount - alert.returns.length);
            $('#supplierCompensationMore')
                .toggleClass('d-none', hiddenCount === 0)
                .text(hiddenCount > 0 ? `Masih ada ${hiddenCount.toLocaleString('id-ID')} retur lain. Buka halaman Retur Pembelian untuk melihat semuanya.` : '');
        }

        function detailRowTemplate(item, existing = null) {
            let maxQty = Number(item.outstanding_qty || 0);
            let qtyValue = existing ? Number(existing.qty_diterima || 0) : receiptInvoiceMode === 'single' ? maxQty : 0;
            let harga = existing ? Number(existing.harga_beli || 0) : Number(item.harga_estimasi || 0);
            let diskon1 = Number(item.diskon_1 ?? existing?.diskon_1 ?? existing?.diskon ?? 0);
            let diskon2 = Number(item.diskon_2 ?? existing?.diskon_2 ?? 0);
            let diskon3 = Number(item.diskon_3 ?? existing?.diskon_3 ?? 0);
            let diskon = Number(item.diskon_efektif ?? effectiveTieredDiscount(diskon1, diskon2, diskon3));
            let ppn = existing ? Number(existing.ppn || 0) : Number(item.ppn ?? 11);
            let batch = existing ? (existing.no_batch || '') : '';
            let expired = existing && existing.expired_date ? formatDateInput(existing.expired_date) : '';
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
                <tr class="receive-detail-row" data-po-detail-id="${escapeHtml(item.id)}" data-max="${maxQty}" data-konversi="${conversion}" data-satuan="${escapeHtml(item.satuan)}" data-satuan-stok="${escapeHtml(stockUnit)}"
                    data-diskon-1="${diskon1}" data-diskon-2="${diskon2}" data-diskon-3="${diskon3}" data-diskon-efektif="${diskon}">
                    <td data-mobile-label="Barang">
                        <div class="receive-item-cell">
                            <span class="receive-item-avatar"><i class="mdi mdi-pill"></i></span>
                            <div class="receive-item-copy">
                                <input type="hidden" name="purchase_order_detail_id[]" value="${item.id}">
                                <input type="hidden" name="obat_id[]" value="${item.obat_id}">
                                <strong>${escapeHtml(item.nama_obat)}</strong>
                                <small class="text-muted">${escapeHtml(item.kode_obat)} - ${escapeHtml(item.satuan)}</small>
                                ${conversion > 1 ? `<small class="text-muted">1 ${escapeHtml(item.satuan)} = ${conversion.toLocaleString('id-ID')} ${escapeHtml(stockUnit)}</small>` : ''}
                            </div>
                        </div>
                    </td>
                    <td data-mobile-label="PO / Sisa">
                        <span class="receive-qty-stack">
                            <strong>${Number(item.qty_po || 0).toLocaleString('id-ID')}</strong>
                            <small>Sisa ${maxQty.toLocaleString('id-ID')}</small>
                        </span>
                    </td>
                    <td data-mobile-label="Qty Diterima">
                        <div class="receive-qty-control">
                            <input type="number" class="form-control form-control-sm receive-qty" name="qty_diterima[]"
                                min="0" max="${maxQty}" step="0.01" value="${qtyValue}" inputmode="decimal"
                                aria-label="Qty diterima ${escapeHtml(item.nama_obat)}">
                            <button type="button" class="btn btn-sm btn-light receive-fill-max" title="Isi sisa PO"
                                aria-label="Isi seluruh sisa ${escapeHtml(item.nama_obat)}">
                                Max
                            </button>
                        </div>
                        <small class="receive-row-hint">Maks ${maxQty.toLocaleString('id-ID')} ${escapeHtml(item.satuan || '')}</small>
                        ${conversionHint}
                    </td>
                    <td data-mobile-label="No Batch">
                        <div class="receive-batch-picker">
                            <select class="form-select form-select-sm receive-batch-select" name="stok_batch_id[]"
                                aria-label="Pilih batch ${escapeHtml(item.nama_obat)}">
                                ${receiveBatchOptionsHtml(batchOptions, selectedBatchId)}
                            </select>
                            <input type="text" class="form-control form-control-sm" name="no_batch[]"
                                value="${escapeHtml(batch)}" placeholder="Batch manual"
                                aria-label="Nomor batch ${escapeHtml(item.nama_obat)}">
                            <small class="receive-batch-mode">Input manual jika batch belum tersedia.</small>
                        </div>
                    </td>
                    <td data-mobile-label="Expired Date">
                        <input type="text" class="form-control form-control-sm receive-expired-date"
                            name="expired_date[]" value="${escapeHtml(expired)}" placeholder="DD-MM-YYYY"
                            aria-label="Tanggal kedaluwarsa ${escapeHtml(item.nama_obat)}">
                        <small class="receive-field-note">Wajib untuk batch baru.</small>
                    </td>
                    <td data-mobile-label="Harga Beli">
                        <input type="text" class="form-control form-control-sm receive-price" name="harga_beli[]"
                            value="${formatRupiah(harga)}" inputmode="decimal" autocomplete="off"
                            aria-label="Harga beli ${escapeHtml(item.nama_obat)}">
                        <small class="receive-field-note">Harga per ${escapeHtml(item.satuan || 'satuan')}.</small>
                    </td>
                    <td data-mobile-label="Diskon PO">
                        <div class="receive-qty-stack">
                            <strong>D1 ${formatDecimal(diskon1, 0, 2)}%</strong>
                            <small>D2 ${formatDecimal(diskon2, 0, 2)}%</small>
                            <small>D3 ${formatDecimal(diskon3, 0, 2)}%</small>
                        </div>
                        <small class="receive-field-note">Dari PO · efektif ${formatDecimal(diskon, 0, 2)}%</small>
                    </td>
                    <td data-mobile-label="PPN %">
                        <input type="number" class="form-control form-control-sm receive-tax" name="ppn[]"
                            min="0" max="100" step="0.01" value="${ppn}" inputmode="decimal"
                            aria-label="PPN persen ${escapeHtml(item.nama_obat)}">
                        <small class="receive-field-note">Dari PO · default 11%</small>
                    </td>
                    <td data-mobile-label="Subtotal">
                        <strong class="receive-row-total">${formatRupiah(0)}</strong>
                    </td>
                    <td data-mobile-label="Status">
                        <span class="receive-row-check is-empty">
                            <i class="mdi mdi-minus-circle-outline"></i>
                            Belum diisi
                        </span>
                    </td>
                </tr>
            `;
        }

        $('#receiveItemSearch').select2({
            dropdownParent: $('#penerimaanModal'),
            width: '100%',
            placeholder: 'Cari / pilih nama atau kode barang dari PO...',
            allowClear: true,
            minimumResultsForSearch: 0,
            language: {
                noResults: () => 'Barang tidak ditemukan pada PO terpilih.',
                searching: () => 'Mencari barang...'
            }
        }).on('change', function() {
            filterReceiveItemRows();
        }).on('select2:select', function() {
            $('#receiveDetailRows .receive-detail-row:not(.d-none)').first()
                .find('.receive-qty').trigger('focus');
        }).on('select2:open', function() {
            document.querySelector('#penerimaanModal .select2-search__field')?.focus();
        });

        function filterReceiveItemRows() {
            let selectedId = String($('#receiveItemSearch').val() || '');
            $('#receiveDetailRows .receive-detail-row').each(function() {
                let row = $(this);
                row.toggleClass('d-none', selectedId !== '' && row.attr('data-po-detail-id') !== selectedId);
            });
        }

        function updateReceiveItemSearch(items = []) {
            let options = '<option value=""></option>';
            items.forEach(function(item) {
                let label = `${item.nama_obat || '-'} (${item.kode_obat || '-'}) - Sisa ${formatDecimal(item.outstanding_qty)} ${item.satuan || ''}`;
                options += `<option value="${escapeHtml(item.id)}">${escapeHtml(label)}</option>`;
            });

            $('#receiveItemSearch').html(options).val('').prop('disabled', !items.length).trigger('change');
        }

        function renderPoDetails(po, existingDetails = []) {
            let existingByDetail = {};
            existingDetails.forEach(function(detail) {
                existingByDetail[detail.purchase_order_detail_id] = detail;
            });

            $('#receiveDetailRows').empty();

            let items = (po.details || []).filter(item => Number(item.outstanding_qty) > 0 || existingByDetail[item.id]);
            updateReceiveItemSearch(items);

            if (!items.length) {
                $('#receiveDetailEditor').addClass('d-none');
                $('#receiveDetailEmpty').removeClass('d-none');
                $('#receiveDetailLiveSummary').addClass('d-none');
                $('#receiveLineCount').text('0');
                recalculateReceiveTotals();
                return;
            }

            items.forEach(function(item) {
                $('#receiveDetailRows').append(detailRowTemplate(item, existingByDetail[item.id]));
            });

            $('#receiveLineCount').text(items.length.toLocaleString('id-ID'));
            $('#receiveDetailEditor').removeClass('d-none');
            $('#receiveDetailEmpty').addClass('d-none');

            flatpickr(".receive-expired-date", {
                dateFormat: "d-m-Y",
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
            let rowCount = 0;
            let readyRows = 0;
            let emptyRows = 0;

            $('.receive-detail-row').each(function() {
                let row = $(this);
                rowCount++;
                let qty = Number(row.find('.receive-qty').val()) || 0;
                let price = parseCurrencyValue(row.find('.receive-price').val());
                let discount1 = Number(row.data('diskon-1')) || 0;
                let discount2 = Number(row.data('diskon-2')) || 0;
                let discount3 = Number(row.data('diskon-3')) || 0;
                let tax = normalizedPercent(row.find('.receive-tax').val());
                let max = Number(row.data('max')) || 0;
                let conversion = Number(row.data('konversi')) || 1;
                let purchaseUnit = row.data('satuan') || 'satuan';
                let stockUnit = row.data('satuan-stok') || 'satuan stok';
                let selectedBatchId = row.find('.receive-batch-select').val();
                let batch = row.find('input[name="no_batch[]"]').val()?.trim();
                let expired = row.find('input[name="expired_date[]"]').val()?.trim();
                let hasBatchInfo = selectedBatchId ? Boolean(batch) : Boolean(batch && expired);
                // Match the server's rounding for each receipt line before summing the invoice.
                let subtotal = roundMoney(qty * price);
                let taxBase = tieredDiscountNet(subtotal, discount1, discount2, discount3);
                let discountValue = roundMoney(Math.max(0, subtotal - taxBase));
                let taxValue = roundMoney(taxBase * (tax / 100));
                let total = roundMoney(taxBase + taxValue);
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
                        readyRows++;
                        row.addClass('is-filled');
                        check.addClass('is-ok').html('<i class="mdi mdi-check-circle-outline"></i> Lengkap');
                    }
                } else {
                    emptyRows++;
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
                totalOutstanding,
                rowCount,
                readyRows,
                emptyRows
            };
        }

        function updateReceiveDetailSummary(stats) {
            $('#receiveDetailLiveSummary').toggleClass('d-none', stats.rowCount <= 0);
            $('#receiveReadyRows').text(`${Number(stats.readyRows || 0).toLocaleString('id-ID')} item`);
            $('#receiveWarningRows').text(`${Number(stats.invalidRows || 0).toLocaleString('id-ID')} item`);
            $('#receiveLiveQty').text(Number(stats.totalQty || 0).toLocaleString('id-ID'));
            $('#receiveLiveValue').text(formatRupiah(stats.grandTotal || 0));
        }

        function syncInvoiceTotals(stats) {
            let subtotal = roundMoney(stats.totalSubtotal);
            let discount = roundMoney(stats.totalDiscount);
            let tax = roundMoney(stats.totalTax);
            let otherCostInput = $('input[name="biaya_lain"]');
            let paidInput = $('input[name="jumlah_dibayar"]');
            let receivedQty = Number(stats.totalQty) || 0;
            let otherCost = currentPoTotalQty > 0
                ? roundMoney(currentPoAdditionalCost * Math.min(receivedQty, currentPoTotalQty) / currentPoTotalQty)
                : 0;
            setMoneyInput('biaya_lain', otherCost);
            let grossTotal = roundMoney(Math.max(0, subtotal - discount + tax + otherCost));
            let compensationEnabled = $('#applySupplierCompensation').is(':checked') && supplierCompensationAvailable > 0;
            let compensationInput = $('#supplierCompensationDiscount');
            let maxCompensationDiscount = Math.min(grossTotal, supplierCompensationAvailable);
            let compensationDiscount = compensationEnabled ? parseCurrencyValue(compensationInput.val()) : 0;
            compensationDiscount = roundMoney(Math.min(maxCompensationDiscount, compensationDiscount));
            let payableTotal = roundMoney(Math.max(0, grossTotal - compensationDiscount));
            let paid = getMoneyInput('jumlah_dibayar');
            let isEditingPaid = paidInput.is(':focus');

            if (!isEditingPaid) {
                if (paymentPreset === 'full') {
                    paid = payableTotal;
                } else if (paymentPreset === 'half') {
                    paid = payableTotal / 2;
                } else if (paymentPreset === 'none') {
                    paid = 0;
                }
            }

            paid = Math.min(roundMoney(paid), payableTotal);

            let debt = roundMoney(Math.max(0, payableTotal - paid));
            let paymentStatus = paymentStatusFromAmounts(payableTotal, paid);

            if (grossTotal > 0 && payableTotal <= 0) {
                paymentStatus = 'lunas';
            }

            setMoneyInput('subtotal', subtotal);
            setMoneyInput('diskon', discount);
            setMoneyInput('pajak', tax);
            if (!compensationInput.is(':focus')) {
                setMoneyInput('supplier_compensation_discount', compensationDiscount);
            } else {
                compensationInput.data('raw-value', formatMoneyInputValue(compensationDiscount));
            }
            setMoneyInput('total_faktur', grossTotal);
            setMoneyInput('sisa_hutang', debt);
            $('input[name="status_pembayaran"]').val(paymentStatus);

            if (!otherCostInput.is(':focus')) {
                setMoneyElement(otherCostInput, otherCost);
            } else {
                otherCostInput.data('raw-value', formatMoneyInputValue(otherCost));
            }

            if (!isEditingPaid || paid !== parseCurrencyValue(paidInput.val())) {
                paidInput.val(isEditingPaid ? moneyEditValue(paid) : formatRupiah(paid));
                paidInput.data('raw-value', formatMoneyInputValue(paid));
            } else {
                paidInput.data('raw-value', formatMoneyInputValue(paid));
            }

            updateInvoicePaymentUi(payableTotal, paid, debt, paymentStatus);
            $('#supplierCompensationMax').text(formatRupiah(maxCompensationDiscount));
            $('#invoiceBoardFormula').text(compensationDiscount > 0
                ? `${formatRupiah(grossTotal)} - ganti rugi ${formatRupiah(compensationDiscount)}`
                : 'Sama dengan total asli faktur');

            return {
                total: grossTotal,
                payableTotal,
                paid,
                debt,
                status: paymentStatus,
                compensationDiscount
            };
        }

        function recalculateReceiveTotals() {
            let stats = collectReceiveStats();
            let invoice = syncInvoiceTotals(stats);
            let receivePercent = stats.totalOutstanding > 0 ? Math.min(100, (stats.totalQty / stats.totalOutstanding) *
                100) : 0;

            updateReceiveDetailSummary(stats);
            $('#receiveModalItemCount').text(stats.itemCount.toLocaleString('id-ID'));
            $('#receiveModalQtyCount, #summaryFilledQty').text(stats.totalQty.toLocaleString('id-ID'));
            $('#receiveGrandTotal').text(formatRupiah(invoice.total));
            $('#summaryReceivePercent').text(`${receivePercent.toFixed(0)}%`);
            $('#summaryReceiveMeter').css('width', `${receivePercent}%`);
            updateFormProgress();
        }

        $(document).on('input change', '.receive-qty, .receive-price, .receive-tax, input[name="no_batch[]"], input[name="expired_date[]"], input[name="nomor_faktur"], input[name="tanggal_penerimaan"], input[name="tanggal_faktur"], input[name="tanggal_jatuh_tempo"], input[name="biaya_lain"], input[name="supplier_compensation_discount"], input[name="jumlah_dibayar"]', function() {
            let input = $(this);
            let max = Number(input.closest('.receive-detail-row').data('max')) || 0;

            if (input.attr('name') === 'jumlah_dibayar') {
                paymentPreset = 'manual';
            }

            if (input.hasClass('receive-qty') && Number(input.val()) > max) {
                input.val(max);
            }

            if (input.hasClass('receive-tax')) {
                setReceiveBatchMode(input.closest('.receive-detail-row').find('.receive-batch-select'), true);
            }

            recalculateReceiveTotals();
        });

        $('#applySupplierCompensation').on('change', function() {
            let enabled = $(this).is(':checked');
            let input = $('#supplierCompensationDiscount');
            input.prop('disabled', !enabled);

            if (enabled) {
                let grossTotal = Math.max(
                    0,
                    getMoneyInput('subtotal')
                        - getMoneyInput('diskon')
                        + getMoneyInput('pajak')
                        + getMoneyInput('biaya_lain')
                );
                setMoneyInput(
                    'supplier_compensation_discount',
                    Math.min(grossTotal, supplierCompensationAvailable)
                );
            } else {
                setMoneyInput('supplier_compensation_discount', 0);
            }

            recalculateReceiveTotals();
        });

        $(document).on('focus', '.invoice-money:not([readonly]), .receive-price', function() {
            let input = $(this);
            input.val(moneyEditValue(input.val()));
            this.select();
        });

        $(document).on('blur', '.invoice-money:not([readonly]), .receive-price', function() {
            let input = $(this);
            setMoneyElement(input, parseCurrencyValue(input.val()));
            recalculateReceiveTotals();
        });

        $('.receive-payment-action').on('click', function() {
            let action = $(this).data('payment-action');
            let total = Math.max(0, getMoneyInput('total_faktur') - getMoneyInput('supplier_compensation_discount'));
            let paid = 0;
            paymentPreset = action;

            if (action === 'half') {
                paid = total / 2;
            }

            if (action === 'full') {
                paid = total;
            }

            setMoneyInput('jumlah_dibayar', paid);
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
            $('#receiveItemSearch').val('').trigger('change');
            $('.receive-detail-row').each(function() {
                $(this).find('.receive-qty').val(Number($(this).data('max')) || 0);
            });
            recalculateReceiveTotals();
        });

        $('#clearAllQty').on('click', function() {
            $('#receiveItemSearch').val('').trigger('change');
            $('.receive-detail-row .receive-qty').val(0);
            recalculateReceiveTotals();
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

        function renderReceiveItems(row, type) {
            const total = `${formatDecimal(row.total_barang)} item / ${formatDecimal(row.total_qty)}`;
            if (type !== 'display' || !row.obat_dipilih_details?.length) return total;

            // DataTables already escapes the nested medicine strings in the response.
            const medicine = row.obat_dipilih_details[0];
            return `<span class="receive-medicine-receipt-total">Total penerimaan: ${total}</span><div class="receive-medicine-matches">
                <div class="receive-medicine-match-title">
                    <strong>${medicine.nama_obat}</strong>
                    <span>${medicine.kode_obat || '-'}</span>
                </div>
                ${row.obat_dipilih_details.map(detail => `
                <div class="receive-medicine-match">
                    <dl class="receive-medicine-batch-meta">
                        <div><dt>Qty diterima</dt><dd>${formatDecimal(detail.qty_diterima)} ${detail.satuan}</dd></div>
                        <div><dt>Batch</dt><dd>${detail.no_batch || '-'}</dd></div>
                        <div><dt>Kedaluwarsa</dt><dd>ED: ${escapeHtml(formatDateDisplay(detail.expired_date))}</dd></div>
                    </dl>
                </div>
            `).join('')}</div>`;
        }

        function renderReceiveMedicineOption(medicine) {
            if (!medicine.id || !medicine.nama_obat) return medicine.text;
            return $(`<span class="receive-medicine-option">
                <strong>${escapeHtml(medicine.nama_obat)}</strong>
                <small>Kode: ${escapeHtml(medicine.kode_obat || '-')}</small>
            </span>`);
        }

        function renderReceiveMedicineCard(row) {
            // Receipt text is escaped by the DataTables endpoint; actions are its trusted HTML.
            return `<article class="receive-medicine-result-card">
                <header class="receive-medicine-result-card-header">
                    <div><small>Nomor penerimaan</small><h6>${row.nomor_penerimaan || '-'}</h6></div>
                    ${statusBadge(row.status)}
                </header>
                <dl class="receive-medicine-receipt-meta">
                    <div class="is-wide"><dt>Supplier</dt><dd>${row.supplier || '-'}</dd></div>
                    <div><dt>Tanggal terima</dt><dd>${escapeHtml(formatDateDisplay(row.tanggal))}</dd></div>
                    <div><dt>Nomor PO</dt><dd>${row.no_po || '-'}</dd></div>
                    <div><dt>Nomor faktur</dt><dd>${row.nomor_faktur || '-'}</dd></div>
                    <div><dt>Total faktur</dt><dd>${formatRupiah(row.grand_total)}</dd></div>
                </dl>
                <div class="receive-medicine-result-card-body">${renderReceiveItems(row, 'display')}</div>
                <footer class="receive-medicine-result-card-footer">
                    <span>Aksi penerimaan</span>
                    <div class="purchase-action-group">${row.actions || ''}</div>
                </footer>
            </article>`;
        }

        function updateReceiveMedicineResults(table) {
            const filtered = Boolean(receiveMedicineId);
            $('#receiveTableSection').toggleClass('is-medicine-filtered', filtered);
            $('#receiveMedicineResults').prop('hidden', !filtered).attr('aria-busy', 'false');
            if (!filtered) {
                $('#receiveMedicineResultList').empty();
                $('#receiveMedicineResultCount').text('');
                return;
            }

            const rows = table.rows({ page: 'current' }).data().toArray();
            const page = table.page.info();
            $('#receiveMedicineResultCount').text(rows.length
                ? `${formatDecimal(page.start + 1)}–${formatDecimal(page.end)} dari ${formatDecimal(page.recordsDisplay)} penerimaan`
                : '0 penerimaan ditemukan');
            $('#receiveMedicineResultList').html(rows.length
                ? rows.map(renderReceiveMedicineCard).join('')
                : `<div class="receive-medicine-results-empty">
                    <i class="mdi mdi-package-variant" aria-hidden="true"></i>
                    <strong>Belum ada penerimaan yang sesuai</strong>
                    <p>Coba ubah tanggal, status, atau pencarian nomor penerimaan. Pilih obat lain untuk mencari kembali.</p>
                </div>`);
        }

        $('#tablePenerimaan').on('preXhr.dt', function() {
            if (!receiveMedicineId) return;
            $('#receiveMedicineResults').attr('aria-busy', 'true');
            $('#receiveMedicineResultCount').text('Memuat penerimaan...');
            $('#receiveMedicineResultList').html('<div class="receive-medicine-results-empty"><i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i><span>Memuat riwayat penerimaan obat...</span></div>');
        });

        let PenerimaanTable = $('#tablePenerimaan').DataTable({
            processing: true,
            serverSide: true,
            responsive: window.matchMedia('(min-width: 768px)').matches,
            autoWidth: false,
            dom: 'rt<"receive-table-footer"ip>',
            pagingType: 'simple_numbers',
            pageLength: 10,
            order: [
                [7, 'desc']
            ],
            ajax: {
                url: '{{ route("penerimaan.table") }}',
                type: 'GET',
                data: function(request) {
                    request.purchase_order_id = selectedPurchaseOrderId || '';
                    request.obat_id = receiveMedicineId;
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
                    render: renderReceiveItems
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
            createdRow: function(row) {
                const mobileLabels = [
                    'No', 'Status', 'Nomor Penerimaan', 'Nomor PO', 'Supplier', 'Faktur',
                    'Surat Jalan', 'Tanggal', 'Item / Qty', 'Total Faktur', 'User', 'Aksi'
                ];

                $(row).children('td').each(function(index) {
                    $(this).attr('data-mobile-label', mobileLabels[index] || '');
                });

                let actionGroup = $(row).children('td').eq(11).find('.purchase-action-group');
                if (actionGroup.length) {
                    actionGroup.prepend(`
                        <button type="button" class="btn btn-sm btn-light receive-mobile-more"
                            aria-expanded="false" title="Tampilkan informasi tambahan">
                            <i class="mdi mdi-information-outline"></i>
                            <span>Info</span>
                        </button>
                    `);
                }
            },
            drawCallback: function() {
                updateReceiveMedicineResults(this.api());
                let table = $('#tablePenerimaan, #receiveMedicineResultList');
                table.find('.btn-info').attr('title', 'Lihat detail penerimaan');
                table.find('.btn-print-receipt').attr('title', 'Cetak penerimaan');
                table.find('.btn-success').attr('title', 'Edit draft penerimaan');
                table.find('.btn-primary').attr('title', 'Posting penerimaan ke stok');
                table.find('.btn-warning').attr('title', 'Batalkan penerimaan');
                table.find('.btn-danger').attr('title', 'Hapus draft penerimaan');
                table.find('.purchase-action-group .btn[title]').each(function() {
                    $(this).attr('aria-label', $(this).attr('title'));
                });
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat penerimaan...</span>',
                emptyTable: selectedPurchaseOrderId ? 'Belum ada penerimaan untuk PO ini.' : 'Belum ada penerimaan barang.',
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
            defaultDate: selectedPurchaseOrderId ? [] : [receiveDateStart, receiveDateEnd],
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

        $('#receiveDatePreset').val(selectedPurchaseOrderId ? '' : 'this_month');
        $('#receiveDateRange').closest('.purchase-date-input').toggleClass('has-value', !selectedPurchaseOrderId);

        $('#receiveMedicineFilter').select2({
            width: '100%',
            placeholder: 'Ketik nama atau kode obat...',
            dropdownParent: $('.receive-medicine-select'),
            templateResult: renderReceiveMedicineOption,
            templateSelection: renderReceiveMedicineOption,
            minimumResultsForSearch: 0,
            allowClear: false,
            ajax: {
                url: '{{ route("penerimaan.receiptMedicines") }}',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term || '',
                    page: params.page || 1,
                    purchase_order_id: selectedPurchaseOrderId || ''
                })
            },
            language: {
                noResults: () => 'Obat tidak ditemukan dalam penerimaan.',
                searching: () => 'Mencari obat...',
                loadingMore: () => 'Memuat obat lainnya...',
                errorLoading: () => 'Daftar obat gagal dimuat. Silakan coba lagi.'
            }
        }).on('change', function() {
            receiveMedicineId = this.value || '';
            $('#clearReceiveMedicineFilter').prop('disabled', !receiveMedicineId);
            $('#receiveTableSection').toggleClass('is-medicine-filtered', Boolean(receiveMedicineId));
            $('#receiveMedicineResults').prop('hidden', !receiveMedicineId);

            if (receiveMedicineId) {
                receiveDateStart = '';
                receiveDateEnd = '';
                receiveDatePicker.clear(false);
                $('#receiveDatePreset').val('');
                $('#receiveDateRange').closest('.purchase-date-input').removeClass('has-value');
            }

            $('#receiveMedicineFilterHint').text(receiveMedicineId
                ? 'Periode diatur ke semua tanggal saat memilih obat. Gunakan filter tanggal dan status untuk mempersempit hasil.'
                : 'Ketik nama atau kode obat, lalu pilih obat untuk melihat riwayat penerimaan dan batch-nya.');
            PenerimaanTable.ajax.reload();
        });

        $('#receiveMedicineFilter').on('select2:open', function() {
            $('.receive-medicine-select .select2-dropdown').addClass('receive-medicine-dropdown');
            $('.receive-medicine-select .select2-search__field')
                .attr('placeholder', 'Cari nama / kode obat')
                .attr('aria-label', 'Cari nama atau kode obat dalam penerimaan');
        });

        $('#clearReceiveMedicineFilter').on('click', function() {
            $('#receiveMedicineFilter').val(null).trigger('change');
            $('.receive-medicine-select .select2-selection').trigger('focus');
        });

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

        $('#receiveMobileFilterToggle').on('click', function() {
            let filterBar = $('#receiveFilterBar');
            let isOpen = !filterBar.hasClass('is-mobile-open');

            filterBar.toggleClass('is-mobile-open', isOpen);
            $(this)
                .toggleClass('is-active', isOpen)
                .attr('aria-expanded', isOpen ? 'true' : 'false')
                .attr('title', isOpen ? 'Tutup filter' : 'Buka filter')
                .attr('aria-label', isOpen ? 'Tutup filter' : 'Buka filter')
                .find('i')
                .toggleClass('mdi-tune-variant', !isOpen)
                .toggleClass('mdi-chevron-up', isOpen);
        });

        $('#penerimaanModal').on('click', '[data-receive-section]', function() {
            document.getElementById($(this).data('receive-section'))?.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'start'
            });
        });

        $('#penerimaanModal').on('click', '.receive-mobile-section-toggle', function() {
            let button = $(this);
            let panel = $(button.data('mobile-panel'));
            let isOpen = !panel.hasClass('is-mobile-details-open');

            panel.toggleClass('is-mobile-details-open', isOpen);
            button.attr('aria-expanded', isOpen ? 'true' : 'false');
            button.find('i')
                .toggleClass('mdi-chevron-down', !isOpen)
                .toggleClass('mdi-chevron-up', isOpen);
        });

        $('#tablePenerimaan').on('click', '.receive-mobile-more', function() {
            let button = $(this);
            let row = button.closest('tr');
            let isExpanded = !row.hasClass('is-mobile-expanded');

            row.toggleClass('is-mobile-expanded', isExpanded);
            button
                .attr('aria-expanded', isExpanded ? 'true' : 'false')
                .attr('title', isExpanded ? 'Sembunyikan informasi tambahan' : 'Tampilkan informasi tambahan')
                .attr('aria-label', isExpanded ? 'Sembunyikan informasi tambahan' : 'Tampilkan informasi tambahan')
                .find('span')
                .text(isExpanded ? 'Ringkas' : 'Info');
            button.find('i')
                .toggleClass('mdi-information-outline', !isExpanded)
                .toggleClass('mdi-chevron-up', isExpanded);
        });

        $('#tablePenerimaan').on('xhr.dt', function(event, settings, response) {
            $('#refreshReceiveTable').removeClass('is-loading').prop('disabled', false);
            if (receiveMedicineId && !response) {
                $('#receiveMedicineResults').attr('aria-busy', 'false');
                $('#receiveMedicineResultCount').text('Data gagal dimuat');
                $('#receiveMedicineResultList').html(`<div class="receive-medicine-results-empty">
                    <i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>
                    <strong>Riwayat penerimaan gagal dimuat</strong>
                    <p>Tekan tombol muat ulang data untuk mencoba kembali.</p>
                </div>`);
                return true;
            }
        });

        $('#scrollReceiveTable').on('click', function() {
            document.getElementById('receiveTableSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        function openNextInvoice(poId) {
            $.get('{{ route("penerimaan.purchaseOrderDetail", ":id") }}'.replace(':id', poId))
                .done(function(po) {
                    if (!po.can_create_receipt) {
                        Swal.fire('Faktur sudah disimpan', 'Seluruh qty PO sudah masuk ke penerimaan. Tidak ada sisa untuk faktur berikutnya.', 'success');
                        return;
                    }
                    pendingReceiptPo = po;
                    pendingInvoiceMode = 'split';
                    editMode = false;
                    $('#penerimaanModal').modal('show');
                }).fail(function(xhr) {
                    Swal.fire('Faktur sudah disimpan', xhr.responseJSON?.message ||
                        'Form faktur berikutnya belum dapat dimuat. Buka daftar PO untuk melanjutkan pencatatan.', 'warning');
                });
        }

        $('#penerimaanForm').on('submit', function(e) {
            e.preventDefault();
            if (receiptSaving) return;
            $(document.activeElement).filter('.invoice-money, .receive-price').trigger('blur');
            recalculateReceiveTotals();

            let itemStats = collectReceiveStats();
            let hasValidItem = itemStats.itemCount > 0 && itemStats.invalidRows === 0;

            if (!hasValidItem) {
                $('#receiveItemSearch').val('').trigger('change');
                updateFormProgress();
                document.getElementById('receiveDetailSection')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                Swal.fire({
                    icon: 'warning',
                    title: 'Detail barang belum lengkap',
                    text: itemStats.itemCount > 0 ?
                        'Lengkapi batch atau expired date pada item yang diterima.' :
                        'Isi minimal satu qty barang yang diterima.'
                });
                return;
            }

            if (!$('input[name="nomor_faktur"]').val()?.trim() || !$('input[name="tanggal_faktur"]').val()?.trim()) {
                updateFormProgress();
                document.getElementById('receiveInvoiceSection')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                Swal.fire({
                    icon: 'warning',
                    title: 'Faktur belum lengkap',
                    text: 'Isi nomor dan tanggal faktur sebelum menyimpan draft.'
                });
                return;
            }

            let id = $('#penerimaan_id').val();
            let url = id ? '{{ route("penerimaan.update", ":id") }}'.replace(':id', id) :
                '{{ route("penerimaan.store") }}';
            let method = id ? 'PUT' : 'POST';
            let createNextInvoice = !id && receiptInvoiceMode === 'split'
                && e.originalEvent?.submitter?.id === 'submitPenerimaanNextInvoice';
            let poId = $('#purchase_order_id').val();
            receiptSaving = true;
            $('#submitPenerimaanForm, #submitPenerimaanNextInvoice').prop('disabled', true);

            $.ajax({
                url: url,
                type: method,
                data: serializePenerimaanForm(),
                success: function(response) {
                    if (createNextInvoice) {
                        $('#penerimaanModal').one('hidden.bs.modal', function() {
                            openNextInvoice(poId);
                        });
                    }
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
                },
                complete: function() {
                    receiptSaving = false;
                    $('#submitPenerimaanForm, #submitPenerimaanNextInvoice').prop('disabled', false);
                }
            });
        });

        window.editPenerimaan = function(id) {
            $.get('{{ route("penerimaan.edit", ":id") }}'.replace(':id', id), function(response) {
                resetPenerimaanForm(false);
                editMode = true;
                let header = response.header;
                let po = response.po_payload;

                $('#penerimaanModal').one('shown.bs.modal', function() {
                    $('#penerimaanModalLabel').text('Edit Penerimaan Barang');
                    $('#submitPenerimaanForm').html('<i class="mdi mdi-content-save-outline"></i> Perbarui Draft');
                    paymentPreset = 'manual';
                    $('#penerimaan_id').val(header.id);
                    $('#nomor_penerimaan').val(header.nomor_penerimaan);
                    $('input[name="nomor_faktur"]').val(header.nomor_faktur);
                    $('input[name="nomor_surat_jalan"]').val(header.nomor_surat_jalan);
                    $('input[name="tanggal_penerimaan"]').val(formatDateInput(header.tanggal_penerimaan));
                    $('input[name="tanggal_faktur"]').val(formatDateInput(header.tanggal_faktur));
                    $('input[name="tanggal_jatuh_tempo"]').val(formatDateInput(header.tanggal_jatuh_tempo));
                    $('input[name="status_pembayaran"]').val(header.status_pembayaran || 'belum_dibayar');
                    setMoneyInput('subtotal', header.subtotal);
                    setMoneyInput('diskon', header.diskon ?? header.total_diskon);
                    setMoneyInput('pajak', header.pajak ?? header.total_ppn);
                    setMoneyInput('biaya_lain', header.biaya_lain);
                    setMoneyInput('total_faktur', header.total_faktur ?? header.grand_total);
                    setMoneyInput('jumlah_dibayar', header.jumlah_dibayar);
                    setMoneyInput('sisa_hutang', Math.max(0,
                        Number(header.total_faktur ?? header.grand_total ?? 0) -
                        Number(header.supplier_compensation_discount || 0) -
                        Number(header.jumlah_dibayar || 0)));
                    $('textarea[name="catatan"]').val(header.catatan || '');

                    applyPurchaseOrder(po);
                    let compensationDiscount = Number(header.supplier_compensation_discount || 0);
                    $('#applySupplierCompensation').prop('checked', compensationDiscount > 0);
                    $('#supplierCompensationDiscount').prop('disabled', compensationDiscount <= 0);
                    setMoneyInput('supplier_compensation_discount', compensationDiscount);
                    renderPoDetails(po, header.details || []);
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
                $('#detailTanggalFaktur').val(formatDateDisplay(header.tanggal_faktur));
                $('#detailTanggalJatuhTempo').val(formatDateDisplay(header.tanggal_jatuh_tempo));
                $('#detailStatusPembayaran').val(paymentStatusLabel(header.status_pembayaran));
                $('#detailInvoiceSubtotal').val(formatRupiah(header.subtotal));
                $('#detailInvoiceDiskon').val(formatRupiah(header.diskon ?? header.total_diskon));
                $('#detailInvoicePajak').val(formatRupiah(header.pajak ?? header.total_ppn));
                $('#detailInvoiceBiayaLain').val(formatRupiah(header.biaya_lain));
                $('#detailSupplierCompensationDiscount').val(formatRupiah(header.supplier_compensation_discount));
                $('#detailTotalFaktur').val(formatRupiah(header.total_faktur ?? header.grand_total));
                $('#detailPayableTotal').val(formatRupiah(Math.max(
                    0,
                    Number(header.total_faktur ?? header.grand_total ?? 0) - Number(header.supplier_compensation_discount || 0)
                )));
                $('#detailJumlahDibayar').val(formatRupiah(header.jumlah_dibayar));
                $('#detailSisaHutang').val(formatRupiah(header.sisa_hutang));
                $('#detailNomorSuratJalan').val(header.nomor_surat_jalan || '-');
                $('#detailDiskonUntuk').val(
                    header.diskon_untuk === 'pasien'
                        ? 'Diberikan ke Pasien'
                        : (header.diskon_untuk === 'apotek' ? 'Diambil Apotek' : '-')
                );
                $('#detailCatatan').val(header.catatan || '-');
                $('#detailReceiveItemCount').text(Number(header.total_barang || 0).toLocaleString('id-ID'));
                $('#detailGrandTotal').text(formatRupiah(header.total_faktur ?? header.grand_total));
                $('#detailPrintPenerimaan').attr(
                    'href',
                    '{{ route("penerimaan.print", ":id") }}'.replace(':id', header.id)
                );

                $('#detailReceiveTable tbody').empty();
                (header.details || []).forEach(function(item) {
                    let conversion = Number(item.konversi_satuan || item.purchase_order_detail?.satuan_konversi?.konversi || 1) || 1;
                    let purchaseUnit = item.satuan_beli || item.purchase_order_detail?.satuan_konversi?.satuan?.nama || '-';
                    let stockUnit = item.satuan_stok || item.obat?.satuan?.nama || 'satuan stok';
                    let qtyStock = Number(item.qty_diterima_stok || (Number(item.qty_diterima || 0) * conversion)) || 0;

                    $('#detailReceiveTable tbody').append(`
                        <tr>
                            <td data-mobile-label="Barang">
                                <strong>${escapeHtml(item.obat?.nama_obat || '-')}</strong>
                                <small class="d-block text-muted">${escapeHtml(item.obat?.kode_obat || '-')}</small>
                            </td>
                            <td data-mobile-label="Qty">
                                <strong>${Number(item.qty_diterima || 0).toLocaleString('id-ID')} ${escapeHtml(purchaseUnit)}</strong>
                                <small class="d-block text-muted">${qtyStock.toLocaleString('id-ID')} ${escapeHtml(stockUnit)}</small>
                            </td>
                            <td data-mobile-label="Batch">${escapeHtml(item.no_batch || '-')}</td>
                            <td data-mobile-label="Expired">${formatDateDisplay(item.expired_date)}</td>
                            <td data-mobile-label="Harga Beli">
                                ${formatRupiah(item.harga_beli)}
                                <small class="d-block text-muted">HPP stok ${formatRupiah(item.harga_beli_stok)}/${escapeHtml(stockUnit)} (biaya lain tidak masuk HPP)</small>
                            </td>
                            <td data-mobile-label="Diskon 1">${formatDecimal(item.diskon_1, 0, 2)}%</td>
                            <td data-mobile-label="Diskon 2">${formatDecimal(item.diskon_2, 0, 2)}%</td>
                            <td data-mobile-label="Diskon 3">${formatDecimal(item.diskon_3, 0, 2)}%</td>
                            <td data-mobile-label="PPN">${Number(item.ppn || 0).toLocaleString('id-ID')}%</td>
                            <td data-mobile-label="Total">${formatRupiah(item.total)}</td>
                        </tr>
                    `);
                });

                $('#penerimaanModalDetail').modal('show');
            }).fail(function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Detail penerimaan tidak bisa dimuat.', 'error');
            });
        };

        function renderHargaJualPreview(preview) {
            let header = preview.header || {};
            let details = preview.details || [];
            let missingMargin = Number(preview.summary?.missing_margin_count || 0);
            let discountForPatient = header.diskon_untuk === 'pasien';
            let totalPurchase = details.reduce((total, item) => total + Number(item.total_harga_beli_include_ppn || item.total_harga_beli || 0), 0);
            let totalSelling = details.reduce((total, item) => total + Number(item.total_harga_jual || 0), 0);
            let totalStockQty = details.reduce((total, item) => total + Number(item.qty_satuan_terkecil || 0), 0);
            const detailsOpenAttribute = window.matchMedia('(min-width: 768px)').matches ? ' open' : '';
            let discountExplanation = discountForPatient
                ? 'Diskon diberikan ke pasien: harga beli stok dan dasar harga jual sudah menggunakan harga setelah diskon.'
                : 'Diskon diambil apotek: harga beli stok dan dasar harga jual tetap menggunakan harga sebelum diskon.';
            const marginLevelLabels = {
                sub_golongan: 'Sub Golongan',
                main_golongan: 'Main Golongan',
                golongan: 'Golongan'
            };
            let rows = details.map(function(item) {
                const marginLevel = marginLevelLabels[item.margin_tingkat] || 'Margin';
                const marginReference = item.margin_reference || '';
                let marginBadge = item.has_margin
                    ? `<span class="badge bg-success bg-opacity-10 text-success">${escapeHtml(marginLevel)}</span>`
                    : '<span class="badge bg-warning bg-opacity-10 text-warning">Faktor 1</span>';

                return `
                    <tr>
                        <td class="text-start" data-mobile-label="Obat">
                            <strong>${escapeHtml(item.nama_obat)}</strong>
                            <small class="d-block text-muted">${escapeHtml(item.kode_obat)} - ${escapeHtml(item.golongan)}</small>
                        </td>
                        <td class="text-end" data-mobile-label="Total beli + PPN">
                            <strong>${formatRupiah(item.total_harga_beli_include_ppn || item.total_harga_beli)}</strong>
                            <small class="d-block text-muted">${formatDecimal(item.qty_diterima, 0, 2)} ${escapeHtml(item.satuan_beli)} x ${formatRupiah(item.harga_beli)}</small>
                            <small class="d-block text-muted">Sudah termasuk PPN</small>
                            <small class="d-block text-muted">HPP stok ${formatRupiah(item.harga_beli_stok)}/${escapeHtml(item.satuan_terkecil)} (tanpa biaya lain)</small>
                            <small class="d-block text-muted">Dasar margin incl. PPN ${formatRupiah(item.harga_beli_satuan_terkecil)}/${escapeHtml(item.satuan_terkecil)}</small>
                        </td>
                        <td class="text-end" data-mobile-label="Biaya lain">
                            <strong>${formatRupiah(item.alokasi_biaya_lain)}</strong>
                            <small class="d-block text-muted">${formatRupiah(item.biaya_lain_satuan_beli)}/${escapeHtml(item.satuan_beli)}</small>
                            <small class="d-block text-muted">${formatRupiah(item.biaya_lain_satuan_stok)}/${escapeHtml(item.satuan_terkecil)}</small>
                        </td>
                        <td class="text-end" data-mobile-label="Qty terkecil">
                            <strong>${formatDecimal(item.qty_satuan_terkecil, 0, 2)}</strong>
                            <small class="d-block text-muted">${escapeHtml(item.satuan_terkecil)}</small>
                            <small class="d-block text-muted">Konversi ${formatDecimal(item.konversi_satuan, 0, 2)}</small>
                        </td>
                        <td class="text-center" data-mobile-label="PPN">
                            <span class="d-block">${formatDecimal(item.ppn, 0, 2)}%</span>
                            <small class="text-muted">Include total</small>
                        </td>
                        <td class="text-center" data-mobile-label="Faktor">
                            <span class="d-block">${formatDecimal(item.faktor_jual, 3, 3)}</span>
                            ${marginBadge}
                            ${marginReference ? `<small class="d-block text-muted">${escapeHtml(marginReference)}</small>` : ''}
                        </td>
                        <td class="text-end" data-mobile-label="Diskon">
                            <span class="d-block">D1 ${formatDecimal(item.diskon_1, 0, 2)}% · D2 ${formatDecimal(item.diskon_2, 0, 2)}% · D3 ${formatDecimal(item.diskon_3, 0, 2)}%</span>
                            <small class="d-block text-muted">Efektif ${formatDecimal(item.diskon, 0, 2)}%</small>
                            <small class="text-muted">${formatRupiah(item.nilai_diskon_beli || item.nilai_diskon_jual)}</small>
                            <small class="d-block text-muted">${discountForPatient ? 'Masuk HPP stok' : 'Menjadi margin apotek'}</small>
                        </td>
                        <td class="text-end" data-mobile-label="Harga jual">
                            <strong>${formatRupiah(item.harga_jual)}</strong>
                            <small class="d-block text-muted">/${escapeHtml(item.satuan_terkecil)}</small>
                            <small class="d-block text-muted">Total ${formatRupiah(item.total_harga_jual)}</small>
                            <small class="d-block text-muted">Termasuk alokasi biaya lain</small>
                        </td>
                    </tr>
                `;
            }).join('');

            if (!rows) {
                rows = `
                    <tr>
                            <td colspan="8" class="receive-posting-empty text-center text-muted py-4">Tidak ada detail harga jual.</td>
                    </tr>
                `;
            }

            return `
                <div class="receive-selling-preview">
                    <div class="receive-posting-document-head">
                        <div>
                            <span class="receive-posting-eyebrow">Preview harga jual final</span>
                            <strong>${escapeHtml(header.nomor_penerimaan || '-')}</strong>
                            <small><i class="mdi mdi-file-document-outline"></i>${escapeHtml(header.no_po || '-')}<i class="mdi mdi-circle-small"></i>${escapeHtml(header.supplier || '-')}</small>
                        </div>
                        <span class="receive-posting-ready"><i class="mdi mdi-check-decagram"></i> Siap diposting</span>
                    </div>

                    <div class="receive-posting-summary" aria-label="Ringkasan posting penerimaan">
                        <article>
                            <span><i class="mdi mdi-package-variant-closed"></i> Item</span>
                            <strong>${details.length.toLocaleString('id-ID')}</strong>
                            <small>baris obat</small>
                        </article>
                        <article>
                            <span><i class="mdi mdi-counter"></i> Qty stok</span>
                            <strong>${formatDecimal(totalStockQty, 0, 2)}</strong>
                            <small>satuan terkecil</small>
                        </article>
                        <article>
                            <span><i class="mdi mdi-cart-arrow-down"></i> Nilai beli</span>
                            <strong>${formatRupiah(totalPurchase)}</strong>
                            <small>sudah termasuk PPN</small>
                        </article>
                        <article class="is-highlight">
                            <span><i class="mdi mdi-tag-text-outline"></i> Estimasi jual</span>
                            <strong>${formatRupiah(totalSelling)}</strong>
                            <small>termasuk biaya lain</small>
                        </article>
                    </div>

                    <div class="receive-posting-policy ${discountForPatient ? 'is-patient' : 'is-pharmacy'}">
                        <span class="receive-posting-policy-icon"><i class="mdi ${discountForPatient ? 'mdi-account-heart-outline' : 'mdi-storefront-outline'}"></i></span>
                        <div>
                            <small>Kebijakan diskon aktif</small>
                            <strong>${escapeHtml(header.diskon_untuk_label || '-')}</strong>
                            <p>${escapeHtml(discountExplanation)} Biaya lain ${formatRupiah(header.biaya_lain)} dialokasikan proporsional dan ditambahkan ke harga jual.</p>
                        </div>
                    </div>

                    ${missingMargin > 0 ? `
                        <div class="receive-posting-warning" role="alert">
                            <i class="mdi mdi-alert-outline"></i>
                            <div><strong>${missingMargin} item belum memiliki margin aktif</strong><span>Sistem akan memakai faktor 1.000. Periksa rincian sebelum melanjutkan.</span></div>
                        </div>
                    ` : ''}

                    <details class="receive-posting-details"${detailsOpenAttribute}>
                        <summary>
                            <span><i class="mdi mdi-format-list-bulleted-square"></i><span><strong>Rincian pembentukan harga</strong><small>Audit HPP, diskon, margin, dan harga jual per item</small></span></span>
                            <span class="receive-posting-details-meta">${details.length} baris <i class="mdi mdi-chevron-down"></i></span>
                        </summary>
                        <div class="receive-posting-table-shell" role="region" aria-label="Rincian harga jual, geser ke kiri atau kanan untuk melihat seluruh kolom" tabindex="0">
                            <table class="receive-posting-table">
                                <thead>
                                    <tr>
                                        <th>Obat</th>
                                        <th class="text-end">Total beli + PPN</th>
                                        <th class="text-end">Biaya lain</th>
                                        <th class="text-end">Qty terkecil</th>
                                        <th class="text-center">PPN</th>
                                        <th class="text-center">Faktor</th>
                                        <th class="text-end">Diskon</th>
                                        <th class="text-end">Harga jual</th>
                                    </tr>
                                </thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                    </details>
                </div>
            `;
        }

        function renderDiscountRecipientChoice(preview) {
            const paid = Number(preview.header?.jumlah_dibayar || 0);
            const paymentOptions = Object.entries(preview.payment_methods || {})
                .map(([value, label]) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`)
                .join('');
            const paymentSection = paid > 0 ? `
                <section class="receive-posting-payment" id="receiveInitialPaymentFields">
                    <div class="receive-posting-section-head">
                        <span class="receive-posting-section-icon is-money"><i class="mdi mdi-wallet-outline"></i></span>
                        <div>
                            <strong>Catat pembayaran awal</strong>
                            <small>Jurnal pengeluaran dibuat otomatis saat penerimaan diposting.</small>
                        </div>
                        <span class="receive-posting-payment-amount">${formatRupiah(paid)}</span>
                    </div>
                    <div class="receive-posting-payment-grid">
                        <div class="receive-posting-field">
                            <label for="receivePaymentMethod">Metode pembayaran <span>*</span></label>
                            <div class="receive-posting-control"><i class="mdi mdi-credit-card-outline"></i><select class="form-select" id="receivePaymentMethod" required><option value="">Pilih metode</option>${paymentOptions}</select></div>
                        </div>
                        <div class="receive-posting-field">
                            <label for="receivePaymentOccurredAt">Waktu pembayaran <span>*</span></label>
                            <div class="receive-posting-control"><i class="mdi mdi-calendar-clock-outline"></i><input type="datetime-local" class="form-control" id="receivePaymentOccurredAt" value="${moment().format('YYYY-MM-DDTHH:mm')}" required></div>
                        </div>
                        <div class="receive-posting-field">
                            <label for="receivePaymentReference">Nomor referensi</label>
                            <div class="receive-posting-control"><i class="mdi mdi-receipt-text-outline"></i><input type="text" class="form-control" id="receivePaymentReference" maxlength="120" placeholder="Nomor transfer / bukti bayar"></div>
                        </div>
                    </div>
                    <div class="receive-posting-payment-note"><i class="mdi mdi-information-outline"></i> Faktur ${escapeHtml(preview.header?.nomor_faktur || '-')} akan terhubung ke modul Keuangan.</div>
                </section>
            ` : '';

            return `
                <div class="receive-posting-shell">
                    <div class="receive-posting-intro">
                        <span class="receive-posting-intro-icon"><i class="mdi mdi-shield-check-outline"></i></span>
                        <div>
                            <strong>Langkah terakhir sebelum stok diperbarui</strong>
                            <p>Tentukan penerima manfaat diskon, audit harga jual batch, lalu konfirmasi posting.</p>
                        </div>
                        <span class="receive-posting-draft-pill"><i class="mdi mdi-file-clock-outline"></i> Draft</span>
                    </div>

                    <div class="receive-posting-steps" aria-label="Tahapan posting penerimaan">
                        <span class="is-done"><i class="mdi mdi-check"></i><b>Data tervalidasi</b></span>
                        <i class="mdi mdi-chevron-right"></i>
                        <span class="is-active"><b>Atur diskon</b></span>
                        <i class="mdi mdi-chevron-right"></i>
                        <span><b>Posting stok</b></span>
                    </div>

                    <section class="receive-posting-choice-section">
                        <div class="receive-posting-section-head">
                            <span class="receive-posting-section-icon"><i class="mdi mdi-sale-outline"></i></span>
                            <div>
                                <strong>Siapa yang menerima manfaat diskon?</strong>
                                <small>Pilihan ini langsung memperbarui HPP dan preview harga jual.</small>
                            </div>
                            <span class="receive-posting-required">Wajib dipilih</span>
                        </div>
                        <div class="receive-posting-choice-grid" role="radiogroup" aria-label="Penerima manfaat diskon">
                            <label class="receive-posting-choice is-selected" for="discountRecipientPatient">
                                <input class="visually-hidden" type="radio" name="receive_discount_recipient" id="discountRecipientPatient" value="pasien" checked>
                                <span class="receive-posting-choice-icon is-patient"><i class="mdi mdi-account-heart-outline"></i></span>
                                <span class="receive-posting-choice-copy"><small>Opsi pelayanan</small><strong>Untuk Pasien</strong><span>HPP stok memakai harga beli setelah diskon, sehingga manfaat diteruskan ke pasien.</span></span>
                                <span class="receive-posting-choice-check"><i class="mdi mdi-check"></i></span>
                            </label>
                            <label class="receive-posting-choice" for="discountRecipientPharmacy">
                                <input class="visually-hidden" type="radio" name="receive_discount_recipient" id="discountRecipientPharmacy" value="apotek">
                                <span class="receive-posting-choice-icon is-pharmacy"><i class="mdi mdi-storefront-outline"></i></span>
                                <span class="receive-posting-choice-copy"><small>Opsi bisnis</small><strong>Untuk Apotek</strong><span>HPP tetap memakai harga sebelum diskon; selisihnya menjadi margin apotek.</span></span>
                                <span class="receive-posting-choice-check"><i class="mdi mdi-check"></i></span>
                            </label>
                        </div>
                    </section>

                    ${paymentSection}

                    <div id="receiveSellingPreviewContent" class="receive-posting-preview" aria-live="polite">${renderHargaJualPreview(preview)}</div>

                    <label class="receive-posting-agreement" for="receivePostingAgreement">
                        <input type="checkbox" id="receivePostingAgreement">
                        <span class="receive-posting-agreement-box"><i class="mdi mdi-check"></i></span>
                        <span>
                            <strong>Saya sudah memeriksa data penerimaan dan harga jual.</strong>
                            <small>Posting akan menambah stok batch, menyimpan harga jual, dan mencatat pembayaran awal bila ada.</small>
                        </span>
                    </label>
                </div>
            `;
        }

        function submitPostPenerimaan(id, payload) {
            Swal.fire({
                title: 'Sedang memposting penerimaan',
                html: '<div class="receive-posting-progress"><span><i class="mdi mdi-database-sync-outline"></i></span><strong>Memperbarui stok dan harga jual batch...</strong><small>Mohon tunggu, jangan tutup halaman ini.</small></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'receive-posting-status-popup',
                    title: 'receive-posting-status-title',
                    htmlContainer: 'receive-posting-status-html'
                },
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("penerimaan.post", ":id") }}'.replace(':id', id),
                type: 'PUT',
                data: payload,
                success: function(response) {
                    PenerimaanTable.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Penerimaan berhasil diposting',
                        text: response.message,
                        confirmButtonText: 'Selesai',
                        confirmButtonColor: '#0f766e'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Posting belum berhasil',
                        text: xhr.responseJSON?.message || 'Penerimaan gagal diposting.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        }

        window.postPenerimaan = function(id) {
            const previewUrl = '{{ route("penerimaan.hargaJualPreview", ":id") }}'.replace(':id', id);

            Swal.fire({
                title: 'Menyiapkan preview posting',
                html: '<div class="receive-posting-progress"><span><i class="mdi mdi-calculator-variant-outline"></i></span><strong>Menghitung HPP, margin, dan harga jual...</strong><small>Data terbaru sedang disiapkan.</small></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'receive-posting-status-popup',
                    title: 'receive-posting-status-title',
                    htmlContainer: 'receive-posting-status-html'
                },
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.get(previewUrl, { diskon_untuk: 'pasien' }, function(preview) {
                Swal.fire({
                    title: 'Finalisasi & Posting Penerimaan',
                    html: renderDiscountRecipientChoice(preview),
                    width: '76rem',
                    showCancelButton: true,
                    showCloseButton: true,
                    buttonsStyling: false,
                    reverseButtons: true,
                    confirmButtonText: '<i class="mdi mdi-database-check-outline"></i><span>Posting Penerimaan</span>',
                    cancelButtonText: 'Periksa Lagi',
                    focusConfirm: false,
                    allowOutsideClick: false,
                    customClass: {
                        container: 'receive-posting-container',
                        popup: 'receive-posting-popup',
                        title: 'receive-posting-title',
                        htmlContainer: 'receive-posting-html',
                        actions: 'receive-posting-actions',
                        confirmButton: 'receive-posting-confirm',
                        cancelButton: 'receive-posting-cancel',
                        closeButton: 'receive-posting-close',
                        validationMessage: 'receive-posting-validation'
                    },
                    didOpen: function() {
                        const container = $(Swal.getHtmlContainer());
                        const confirmButton = Swal.getConfirmButton();
                        let previewRequest = null;
                        let previewIsLoading = false;
                        let activeDiscount = 'pasien';

                        const updateConfirmAvailability = function() {
                            confirmButton.disabled = previewIsLoading || !container.find('#receivePostingAgreement').is(':checked');
                        };

                        const updateChoiceState = function() {
                            container.find('.receive-posting-choice').removeClass('is-selected').attr('aria-checked', 'false');
                            container.find('input[name="receive_discount_recipient"]:checked').closest('.receive-posting-choice').addClass('is-selected').attr('aria-checked', 'true');
                        };

                        updateChoiceState();
                        updateConfirmAvailability();

                        container.on('change', '#receivePostingAgreement', function() {
                            container.find('.receive-posting-agreement').toggleClass('is-checked', this.checked);
                            updateConfirmAvailability();
                        });

                        container.on('change', 'input[name="receive_discount_recipient"]', function() {
                            const diskonUntuk = this.value;
                            const previewContainer = container.find('#receiveSellingPreviewContent');

                            if (previewRequest) previewRequest.abort();

                            updateChoiceState();
                            previewIsLoading = true;
                            previewContainer.addClass('is-loading').attr('aria-busy', 'true');
                            updateConfirmAvailability();
                            Swal.resetValidationMessage();

                            previewRequest = $.get(previewUrl, { diskon_untuk: diskonUntuk })
                                .done(function(updatedPreview) {
                                    activeDiscount = diskonUntuk;
                                    previewContainer.html(renderHargaJualPreview(updatedPreview));
                                })
                                .fail(function(xhr) {
                                    if (xhr.statusText === 'abort') return;
                                    container.find(`input[name="receive_discount_recipient"][value="${activeDiscount}"]`).prop('checked', true);
                                    updateChoiceState();
                                    Swal.showValidationMessage(xhr.responseJSON?.message || 'Preview harga jual gagal diperbarui.');
                                })
                                .always(function(_response, status) {
                                    if (status === 'abort') return;
                                    previewIsLoading = false;
                                    previewContainer.removeClass('is-loading').attr('aria-busy', 'false');
                                    updateConfirmAvailability();
                                });
                        });
                    },
                    preConfirm: function() {
                        const container = $(Swal.getHtmlContainer());
                        const diskonUntuk = container.find('input[name="receive_discount_recipient"]:checked').val();

                        if (!diskonUntuk) {
                            Swal.showValidationMessage('Pilih diskon diberikan ke pasien atau diambil apotek.');
                            return false;
                        }

                        if (!container.find('#receivePostingAgreement').is(':checked')) {
                            Swal.showValidationMessage('Centang konfirmasi setelah Anda selesai memeriksa data.');
                            return false;
                        }

                        const paid = Number(preview.header?.jumlah_dibayar || 0);
                        const paymentMethod = container.find('#receivePaymentMethod').val();
                        const paymentOccurredAt = container.find('#receivePaymentOccurredAt').val();

                        if (paid > 0 && !paymentMethod) {
                            Swal.showValidationMessage('Pilih metode pembayaran faktur.');
                            return false;
                        }

                        if (paid > 0 && !paymentOccurredAt) {
                            Swal.showValidationMessage('Isi waktu pembayaran faktur.');
                            return false;
                        }

                        return {
                            diskon_untuk: diskonUntuk,
                            payment_method: paymentMethod || '',
                            payment_occurred_at: paymentOccurredAt || '',
                            payment_reference_no: container.find('#receivePaymentReference').val() || ''
                        };
                    }
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    submitPostPenerimaan(id, result.value);
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

        if (initialReceiptId) {
            window.lihatPenerimaan(initialReceiptId);
        }
    });
</script>
