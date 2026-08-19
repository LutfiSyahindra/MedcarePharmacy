<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {
        let editMode = false;
        let receiveDateStart = moment().startOf('month').format('YYYY-MM-DD');
        let receiveDateEnd = moment().endOf('month').format('YYYY-MM-DD');
        let receiveDatePicker = null;
        let paymentPreset = 'none';
        let supplierCompensationAvailable = 0;

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
            if (!value) {
                return '-';
            }

            let date = moment(value);
            return date.isValid() ? date.format('DD-MM-YYYY') : (value || '-');
        }

        function formatDateInput(value) {
            if (!value) {
                return '';
            }

            let date = moment(value);
            return date.isValid() ? date.format('DD-MM-YYYY') : value;
        }

        function formatMoneyInputValue(value) {
            let numeric = typeof value === 'string' ? parseCurrencyValue(value) : (Number(value) || 0);
            return (Math.round(numeric * 100) / 100).toFixed(2);
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

        function paymentStatusMeta(status) {
            const meta = {
                belum_dibayar: {
                    className: 'is-unpaid',
                    icon: 'mdi-clock-alert-outline',
                    label: 'Belum Dibayar',
                    hint: 'Belum ada pembayaran tercatat untuk faktur ini.'
                },
                sebagian: {
                    className: 'is-partial',
                    icon: 'mdi-progress-check',
                    label: 'Sebagian',
                    hint: 'Pembayaran sebagian tersimpan, sisa hutang masih aktif.'
                },
                lunas: {
                    className: 'is-paid',
                    icon: 'mdi-check-decagram-outline',
                    label: 'Lunas',
                    hint: 'Faktur sudah lunas dan tidak menyisakan hutang.'
                }
            };

            return meta[status] || meta.belum_dibayar;
        }

        function updateInvoicePaymentUi(total, paid, debt, status) {
            let paidPercent = total > 0 ? Math.min(100, (paid / total) * 100) : (status === 'lunas' ? 100 : 0);
            let meta = paymentStatusMeta(status);

            $('#invoicePaymentStatusBadge')
                .removeClass('is-unpaid is-partial is-paid')
                .addClass(meta.className)
                .html(`<i class="mdi ${meta.icon}"></i> ${meta.label}`);
            $('#invoiceBoardTotal').text(formatRupiah(total));
            $('#invoiceBoardPaid').text(formatRupiah(paid));
            $('#invoiceBoardDebt').text(formatRupiah(debt));
            $('#invoicePaidPercent').text(`${paidPercent.toFixed(0)}%`);
            $('#invoicePaidMeter').css('width', `${paidPercent}%`);
            $('#invoiceBoardHint').text(total > 0 || status === 'lunas' ? meta.hint : 'Isi item penerimaan untuk menghitung tagihan.');

            $('.receive-payment-action').removeClass('is-active');
            if (status === 'belum_dibayar') {
                $('.receive-payment-action[data-payment-action="none"]').addClass('is-active');
            } else if (status === 'sebagian' && total > 0 && Math.abs(paid - (total / 2)) < 0.01) {
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

            return `${batch.no_batch || '-'} | ED ${batch.expired_date || '-'} | Diskon ${formatDecimal(batch.diskon, 0, 2)}% | PPN ${formatDecimal(batch.ppn, 0, 2)}% | Stok ${formatDecimal(batch.qty, 0, 2)}`;
        }

        function normalizedPercent(value) {
            return Math.min(100, Math.max(0, Number(value) || 0));
        }

        function normalizedDiscount(value) {
            return normalizedPercent(value);
        }

        function tieredDiscountNet(grossAmount, discount1, discount2, discount3) {
            let netAmount = Math.max(0, Number(grossAmount) || 0);

            [discount1, discount2, discount3].forEach(function(discount) {
                netAmount *= 1 - (normalizedDiscount(discount) / 100);
            });

            return netAmount;
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

        function setStateClass(element, state) {
            element.removeClass('is-active is-complete is-warning');
            if (state) {
                element.addClass(state);
            }
        }

        function setStatusPill(selector, state, icon, label) {
            let element = $(selector);
            setStateClass(element, state);
            element.html(`<i class="mdi ${icon}"></i> ${label}`);
        }

        function setRequirementState(selector, state, icon) {
            let element = $(selector);
            setStateClass(element, state);
            element.find('i').attr('class', `mdi ${icon}`);
        }

        function updateReceiveGuidance(hasPo, itemStats, hasInvoice, hasValidItem) {
            let title = 'Mulai dari PO approved';
            let text = 'Pilih nomor PO untuk memuat supplier, sisa qty, dan detail obat.';
            let icon = 'mdi-cursor-default-click-outline';
            let headerIcon = 'mdi-file-clock-outline';
            let headerLabel = 'Draft sebelum posting stok';
            let issueCount = 0;

            if (!hasPo) {
                issueCount = 1;
            } else if (itemStats.itemCount <= 0) {
                title = 'Isi qty barang yang diterima';
                text = 'Gunakan tombol Max per baris atau Terima Semua Sisa jika barang datang lengkap.';
                icon = 'mdi-format-list-checks';
                headerIcon = 'mdi-package-variant-closed';
                headerLabel = 'Menunggu detail barang';
                issueCount = 1;
            } else if (itemStats.invalidRows > 0) {
                title = 'Lengkapi batch atau expired date';
                text = `${itemStats.invalidRows} item sudah punya qty, tetapi belum lengkap untuk disimpan.`;
                icon = 'mdi-alert-circle-outline';
                headerIcon = 'mdi-alert-circle-outline';
                headerLabel = 'Perlu cek detail barang';
                issueCount = itemStats.invalidRows;
            } else if (!hasInvoice) {
                title = 'Detail barang siap, lanjut faktur';
                text = 'Isi nomor faktur dan tanggal faktur untuk membuka tombol simpan draft.';
                icon = 'mdi-receipt-text-plus-outline';
                headerIcon = 'mdi-receipt-text-outline';
                headerLabel = 'Menunggu faktur';
                issueCount = 1;
            } else {
                title = 'Penerimaan siap disimpan';
                text = 'PO, detail barang, batch, expired date, dan faktur sudah lengkap.';
                icon = 'mdi-check-decagram-outline';
                headerIcon = 'mdi-check-decagram-outline';
                headerLabel = 'Siap simpan draft';
            }

            $('#receiveGuidanceTitle').text(title);
            $('#receiveGuidanceText').text(text);
            $('#receiveGuidanceIcon').html(`<i class="mdi ${icon}"></i>`);
            $('#receiveHeaderStatus').html(`<i class="mdi ${headerIcon}"></i> ${headerLabel}`);

            setStateClass($('#receiveProblemCount'), issueCount > 0 ? (itemStats.invalidRows > 0 ? 'is-warning' : 'is-active') : 'is-complete');
            $('#receiveProblemCount').html(
                `<i class="mdi ${issueCount > 0 ? 'mdi-information-outline' : 'mdi-check-circle-outline'}"></i> ${issueCount} catatan`
            );

            setRequirementState('#receiveRequirementPo', hasPo ? 'is-complete' : 'is-active',
                hasPo ? 'mdi-check-circle-outline' : 'mdi-file-check-outline');
            setRequirementState('#receiveRequirementItems',
                hasValidItem ? 'is-complete' : (itemStats.invalidRows > 0 ? 'is-warning' : (hasPo ? 'is-active' : '')),
                hasValidItem ? 'mdi-check-circle-outline' : (itemStats.invalidRows > 0 ? 'mdi-alert-circle-outline' : 'mdi-barcode-scan'));
            setRequirementState('#receiveRequirementInvoice',
                hasInvoice && hasValidItem ? 'is-complete' : (hasValidItem ? 'is-active' : ''),
                hasInvoice && hasValidItem ? 'mdi-check-circle-outline' : 'mdi-receipt-text-outline');

            setStatusPill('#receiveInfoSectionStatus', hasPo ? 'is-complete' : 'is-active',
                hasPo ? 'mdi-check-circle-outline' : 'mdi-cursor-default-click-outline',
                hasPo ? 'PO siap' : 'Pilih PO');
            setStatusPill('#receiveDetailSectionStatus',
                hasValidItem ? 'is-complete' : (itemStats.invalidRows > 0 ? 'is-warning' : (hasPo ? 'is-active' : '')),
                hasValidItem ? 'mdi-check-circle-outline' : (itemStats.invalidRows > 0 ? 'mdi-alert-circle-outline' : 'mdi-timer-sand'),
                hasValidItem ? `${itemStats.itemCount} item siap` : (itemStats.invalidRows > 0 ? `${itemStats.invalidRows} perlu cek` : (hasPo ? 'Isi qty' : 'Menunggu PO')));
            setStatusPill('#receiveInvoiceSectionStatus',
                hasInvoice && hasValidItem ? 'is-complete' : (hasValidItem ? 'is-active' : ''),
                hasInvoice && hasValidItem ? 'mdi-check-circle-outline' : (hasValidItem ? 'mdi-receipt-text-plus-outline' : 'mdi-lock-clock-outline'),
                hasInvoice && hasValidItem ? 'Faktur siap' : (hasValidItem ? 'Lengkapi faktur' : 'Terkunci'));
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
            $('#receiveInvoicePrompt').toggleClass('d-none', !hasValidItem || hasInvoice);
            updateReceiveGuidance(hasPo, itemStats, hasInvoice, hasValidItem);

            $('#submitPenerimaanForm')
                .toggleClass('btn-primary', hasValidItem && hasInvoice)
                .toggleClass('btn-outline-primary', !(hasValidItem && hasInvoice));
        }

        function resetPenerimaanForm() {
            let form = $('#penerimaanForm');
            form.trigger('reset');
            paymentPreset = 'none';
            $('#penerimaan_id').val('');
            $('#purchase_order_id').val('').trigger('change');
            $('#receive_supplier').val('');
            hideSupplierCompensationAlert();
            $('#receivePoSummary').addClass('d-none');
            $('#receiveDetailRows').empty();
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
            $('#receiveModalSubtotal, #receiveModalDiscount, #receiveModalTax, #receiveGrandTotal').text(formatRupiah(0));
            ['subtotal', 'diskon', 'pajak', 'biaya_lain', 'supplier_compensation_discount', 'total_faktur', 'jumlah_dibayar', 'sisa_hutang'].forEach(function(field) {
                setMoneyInput(field, 0);
            });
            $('input[name="tanggal_faktur"]').val(moment().format('DD-MM-YYYY'));
            $('input[name="tanggal_jatuh_tempo"]').val('');
            $('select[name="status_pembayaran"]').val('belum_dibayar');
            updateInvoicePaymentUi(0, 0, 0, 'belum_dibayar');
            $('#penerimaanModalLabel').text('Form Penerimaan Barang');
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
            $('#receiveModalCompensationDiscount').text(formatRupiah(0));
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
                        <td>
                            <strong>${escapeHtml(item.nomor_retur || '-')}</strong>
                            ${notes}
                        </td>
                        <td>${escapeHtml(item.branch || '-')}</td>
                        <td>
                            <strong>${formatDateDisplay(item.tanggal_retur)}</strong>
                            <small class="d-block ${item.status === 'overdue' ? 'text-danger' : 'text-muted'}">Batas ${escapeHtml(dueDate)}</small>
                        </td>
                        <td>
                            <span class="supplier-compensation-status ${status.className}">
                                <i class="mdi ${status.icon}"></i> ${status.label}
                            </span>
                        </td>
                        <td>
                            <strong>${formatRupiah(item.received_value)}</strong>
                            <small class="d-block text-muted">dari ${formatRupiah(item.expected_value)}</small>
                        </td>
                        <td><strong class="text-danger">${formatRupiah(item.outstanding_value)}</strong></td>
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
            let qtyValue = existing ? Number(existing.qty_diterima || 0) : 0;
            let maxQty = Number(item.outstanding_qty || 0);
            let harga = existing ? Number(existing.harga_beli || 0) : Number(item.harga_estimasi || 0);
            let diskon1 = Number(item.diskon_1 ?? existing?.diskon_1 ?? existing?.diskon ?? 0);
            let diskon2 = Number(item.diskon_2 ?? existing?.diskon_2 ?? 0);
            let diskon3 = Number(item.diskon_3 ?? existing?.diskon_3 ?? 0);
            let diskon = Number(item.diskon_efektif ?? effectiveTieredDiscount(diskon1, diskon2, diskon3));
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
                <tr class="receive-detail-row" data-max="${maxQty}" data-konversi="${conversion}" data-satuan="${escapeHtml(item.satuan)}" data-satuan-stok="${escapeHtml(stockUnit)}"
                    data-diskon-1="${diskon1}" data-diskon-2="${diskon2}" data-diskon-3="${diskon3}" data-diskon-efektif="${diskon}">
                    <td>
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
                        <small class="receive-row-hint">Maks ${maxQty.toLocaleString('id-ID')} ${escapeHtml(item.satuan || '')}</small>
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
                        <small class="receive-field-note">Wajib untuk batch baru.</small>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm receive-price" name="harga_beli[]"
                            value="${formatRupiah(harga)}" inputmode="numeric" autocomplete="off">
                        <small class="receive-field-note">Harga per ${escapeHtml(item.satuan || 'satuan')}.</small>
                    </td>
                    <td>
                        <div class="receive-qty-stack">
                            <strong>D1 ${formatDecimal(diskon1, 0, 2)}%</strong>
                            <small>D2 ${formatDecimal(diskon2, 0, 2)}%</small>
                            <small>D3 ${formatDecimal(diskon3, 0, 2)}%</small>
                        </div>
                        <small class="receive-field-note">Dari PO · efektif ${formatDecimal(diskon, 0, 2)}%</small>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm receive-tax" name="ppn[]"
                            min="0" max="100" step="0.01" value="${ppn}">
                        <small class="receive-field-note">Default 11%</small>
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
                $('#receiveDetailLiveSummary').addClass('d-none');
                $('#receiveLineCount').text('0');
                recalculateReceiveTotals();
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
                let taxBase = tieredDiscountNet(subtotal, discount1, discount2, discount3);
                let discountValue = Math.max(0, subtotal - taxBase);
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

        function updateReceiveCockpit(stats, invoice, receivePercent) {
            let hasPo = Boolean($('#purchase_order_id').val());
            let poNumber = hasPo ? ($('#summaryNoPo').text() || '-') : '-';
            let supplier = hasPo ? ($('#receive_supplier').val() || '-') : 'Pilih PO approved';
            let rowCount = Number(stats.rowCount || 0);
            let readyRows = Number(stats.readyRows || 0);
            let invalidRows = Number(stats.invalidRows || 0);
            let filledRows = Number(stats.itemCount || 0);
            let qtyText = Number(stats.totalQty || 0).toLocaleString('id-ID');
            let invoiceStatus = paymentStatusLabel(invoice.status);
            let itemHint = 'Belum ada detail barang.';

            if (rowCount > 0 && filledRows <= 0) {
                itemHint = 'Isi qty pada barang yang diterima.';
            } else if (invalidRows > 0) {
                itemHint = `${invalidRows.toLocaleString('id-ID')} item perlu batch atau expired date.`;
            } else if (filledRows > 0) {
                itemHint = 'Detail barang sudah siap untuk faktur.';
            }

            $('#cockpitGrandTotal').text(formatRupiah(invoice.total || 0));
            $('#cockpitSubtitle').text(hasPo
                ? `${filledRows.toLocaleString('id-ID')} item diterima, total qty ${qtyText}.`
                : 'Pilih PO approved untuk memulai penerimaan barang.');
            $('#cockpitReceiveMeter').css('width', `${Math.max(0, Math.min(100, receivePercent || 0))}%`);
            $('#cockpitPo').text(poNumber);
            $('#cockpitSupplier').text(supplier);
            $('#cockpitReadyItems').text(`${readyRows.toLocaleString('id-ID')}/${rowCount.toLocaleString('id-ID')}`);
            $('#cockpitItemHint').text(itemHint);
            $('#cockpitInvoiceStatus').text(invoiceStatus);
            $('#cockpitDebt').text(`Sisa hutang ${formatRupiah(invoice.debt || 0)}`);
        }

        function syncInvoiceTotals(stats) {
            let subtotal = Number(stats.totalSubtotal) || 0;
            let discount = Number(stats.totalDiscount) || 0;
            let tax = Number(stats.totalTax) || 0;
            let otherCostInput = $('input[name="biaya_lain"]');
            let paidInput = $('input[name="jumlah_dibayar"]');
            let otherCost = getMoneyInput('biaya_lain');
            let grossTotal = Math.max(0, subtotal - discount + tax + otherCost);
            let compensationEnabled = $('#applySupplierCompensation').is(':checked') && supplierCompensationAvailable > 0;
            let compensationInput = $('#supplierCompensationDiscount');
            let maxCompensationDiscount = Math.min(grossTotal, supplierCompensationAvailable);
            let compensationDiscount = compensationEnabled ? parseCurrencyValue(compensationInput.val()) : 0;
            compensationDiscount = Math.min(maxCompensationDiscount, compensationDiscount);
            let total = Math.max(0, grossTotal - compensationDiscount);
            let paid = getMoneyInput('jumlah_dibayar');
            let isEditingPaid = paidInput.is(':focus');

            if (!isEditingPaid) {
                if (paymentPreset === 'full') {
                    paid = total;
                } else if (paymentPreset === 'half') {
                    paid = total / 2;
                } else if (paymentPreset === 'none') {
                    paid = 0;
                }
            }

            if (paid > total) {
                paid = total;
            }

            let debt = Math.max(0, total - paid);
            let paymentStatus = paymentStatusFromAmounts(total, paid);

            if (grossTotal > 0 && total <= 0) {
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
            setMoneyInput('total_faktur', total);
            setMoneyInput('sisa_hutang', debt);
            $('select[name="status_pembayaran"]').val(paymentStatus);

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

            updateInvoicePaymentUi(total, paid, debt, paymentStatus);
            $('#supplierCompensationMax').text(formatRupiah(maxCompensationDiscount));
            $('#receiveModalCompensationDiscount').text(formatRupiah(compensationDiscount));
            $('#invoiceBoardFormula').text(compensationDiscount > 0
                ? 'Subtotal - diskon + PPN + biaya lain - ganti rugi supplier'
                : 'Subtotal - diskon + PPN + biaya lain');

            return {
                total,
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
            updateReceiveCockpit(stats, invoice, receivePercent);
            $('#receiveModalItemCount').text(stats.itemCount.toLocaleString('id-ID'));
            $('#receiveModalQtyCount, #summaryFilledQty').text(stats.totalQty.toLocaleString('id-ID'));
            $('#receiveModalSubtotal').text(formatRupiah(stats.totalSubtotal));
            $('#receiveModalDiscount').text(formatRupiah(stats.totalDiscount));
            $('#receiveModalTax').text(formatRupiah(stats.totalTax));
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

        $('select[name="status_pembayaran"]').on('change', function() {
            let selectedStatus = $(this).val();
            let total = getMoneyInput('total_faktur');
            let currentPaid = getMoneyInput('jumlah_dibayar');

            if (selectedStatus === 'lunas') {
                paymentPreset = 'full';
                setMoneyInput('jumlah_dibayar', total);
            }

            if (selectedStatus === 'belum_dibayar') {
                paymentPreset = 'none';
                setMoneyInput('jumlah_dibayar', 0);
            }

            if (selectedStatus === 'sebagian') {
                if (total > 0 && (currentPaid <= 0 || currentPaid >= total)) {
                    paymentPreset = 'half';
                    setMoneyInput('jumlah_dibayar', total / 2);
                } else {
                    paymentPreset = 'manual';
                }
            }

            recalculateReceiveTotals();
        });

        $('.receive-payment-action').on('click', function() {
            let action = $(this).data('payment-action');
            let total = getMoneyInput('total_faktur');
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
            $('.receive-detail-row').each(function() {
                $(this).find('.receive-qty').val(Number($(this).data('max')) || 0);
            });
            recalculateReceiveTotals();
        });

        $('#clearAllQty').on('click', function() {
            $('.receive-detail-row .receive-qty').val(0);
            recalculateReceiveTotals();
        });

        $('#goToInvoiceSection').on('click', function() {
            document.getElementById('receiveInvoiceSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            setTimeout(function() {
                $('input[name="nomor_faktur"]').trigger('focus');
            }, 250);
        });

        $('.receive-jump-link').on('click', function() {
            let targetId = $(this).data('target');
            if (!targetId) return;

            document.getElementById(targetId)?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        $('#purchase_order_id').on('change', function() {
            let poId = $(this).val();

            if (!poId) {
                $('#receive_supplier').val('');
                hideSupplierCompensationAlert();
                $('#receivePoSummary').addClass('d-none');
                $('#receiveDetailRows').empty();
                $('#receiveDetailEditor').addClass('d-none');
                $('#receiveDetailEmpty').removeClass('d-none');
                $('#receiveDetailLiveSummary').addClass('d-none');
                $('#receiveLineCount').text('0');
                $('#receiveReadyRows, #receiveWarningRows').text('0 item');
                $('#receiveLiveQty').text('0');
                $('#receiveLiveValue').text(formatRupiah(0));
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
            $(document.activeElement).filter('.invoice-money').trigger('blur');

            let itemStats = collectReceiveStats();
            let hasValidItem = itemStats.itemCount > 0 && itemStats.invalidRows === 0;

            if (!hasValidItem) {
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

            $.ajax({
                url: url,
                type: method,
                data: serializePenerimaanForm(),
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
                    paymentPreset = header.status_pembayaran === 'lunas' ? 'full' : (header.status_pembayaran ===
                        'belum_dibayar' ? 'none' : 'manual');
                    $('#penerimaan_id').val(header.id);
                    $('#nomor_penerimaan').val(header.nomor_penerimaan);
                    $('input[name="nomor_faktur"]').val(header.nomor_faktur);
                    $('input[name="nomor_surat_jalan"]').val(header.nomor_surat_jalan);
                    $('input[name="tanggal_penerimaan"]').val(formatDateInput(header.tanggal_penerimaan));
                    $('input[name="tanggal_faktur"]').val(formatDateInput(header.tanggal_faktur));
                    $('input[name="tanggal_jatuh_tempo"]').val(formatDateInput(header.tanggal_jatuh_tempo));
                    $('select[name="status_pembayaran"]').val(header.status_pembayaran || 'belum_dibayar');
                    setMoneyInput('subtotal', header.subtotal);
                    setMoneyInput('diskon', header.diskon ?? header.total_diskon);
                    setMoneyInput('pajak', header.pajak ?? header.total_ppn);
                    setMoneyInput('biaya_lain', header.biaya_lain);
                    setMoneyInput('total_faktur', header.total_faktur ?? header.grand_total);
                    setMoneyInput('jumlah_dibayar', header.jumlah_dibayar);
                    setMoneyInput('sisa_hutang', header.sisa_hutang);
                    $('textarea[name="catatan"]').val(header.catatan || '');

                    loadApprovedPo(header.purchase_order_id, `${po.no_po} - ${po.supplier}`).then(function() {
                        renderPoSummary(po);
                        let compensationDiscount = Number(header.supplier_compensation_discount || 0);
                        $('#applySupplierCompensation').prop('checked', compensationDiscount > 0);
                        $('#supplierCompensationDiscount').prop('disabled', compensationDiscount <= 0);
                        setMoneyInput('supplier_compensation_discount', compensationDiscount);
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
                $('#detailTanggalFaktur').val(formatDateDisplay(header.tanggal_faktur));
                $('#detailTanggalJatuhTempo').val(formatDateDisplay(header.tanggal_jatuh_tempo));
                $('#detailStatusPembayaran').val(paymentStatusLabel(header.status_pembayaran));
                $('#detailInvoiceSubtotal').val(formatRupiah(header.subtotal));
                $('#detailInvoiceDiskon').val(formatRupiah(header.diskon ?? header.total_diskon));
                $('#detailInvoicePajak').val(formatRupiah(header.pajak ?? header.total_ppn));
                $('#detailInvoiceBiayaLain').val(formatRupiah(header.biaya_lain));
                $('#detailSupplierCompensationDiscount').val(formatRupiah(header.supplier_compensation_discount));
                $('#detailTotalFaktur').val(formatRupiah(header.total_faktur ?? header.grand_total));
                $('#detailJumlahDibayar').val(formatRupiah(header.jumlah_dibayar));
                $('#detailSisaHutang').val(formatRupiah(header.sisa_hutang));
                $('#detailNomorSuratJalan').val(header.nomor_surat_jalan || '-');
                $('#detailCatatan').val(header.catatan || '-');
                $('#detailReceiveItemCount').text(Number(header.total_barang || 0).toLocaleString('id-ID'));
                $('#detailGrandTotal').text(formatRupiah(header.total_faktur ?? header.grand_total));

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
                            <td>${formatDecimal(item.diskon_1, 0, 2)}%</td>
                            <td>${formatDecimal(item.diskon_2, 0, 2)}%</td>
                            <td>${formatDecimal(item.diskon_3, 0, 2)}%</td>
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
                            ${marginReference ? `<small class="d-block text-muted">${escapeHtml(marginReference)}</small>` : ''}
                        </td>
                        <td class="text-end">
                            <span class="d-block">D1 ${formatDecimal(item.diskon_1, 0, 2)}% · D2 ${formatDecimal(item.diskon_2, 0, 2)}% · D3 ${formatDecimal(item.diskon_3, 0, 2)}%</span>
                            <small class="d-block text-muted">Efektif ${formatDecimal(item.diskon, 0, 2)}%</small>
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
                            ${missingMargin} item belum memiliki margin aktif sesuai prioritas, sehingga faktor 1.000 dipakai.
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
