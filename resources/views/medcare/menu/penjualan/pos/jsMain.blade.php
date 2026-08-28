<script>
    $(document).ready(function() {
        document.body.classList.add('pos-fullscreen-mode');
        $('#prescriptionTypeModal').appendTo(document.body);
        $('#posCustomerCard').append($('.pos-transaction-details').first());

        const transactionTypes = @json($transactionTypes);
        const paymentMethods = @json($paymentMethods);
        const canSwitchPosBranch = @json((bool) $canSwitchPosBranch);
        const posBranches = @json($posBranches->values());
        const urls = {
            products: '{{ route("penjualan.pos.products") }}',
            patients: '{{ route("penjualan.pos.patients") }}',
            quote: '{{ route("penjualan.pos.quote") }}',
            draft: '{{ route("penjualan.pos.draft") }}',
            complete: '{{ route("penjualan.pos.complete") }}',
            shiftStatus: '{{ route("penjualan.pos.shifts.status") }}',
            shiftOpen: '{{ route("penjualan.pos.shifts.open") }}',
            shiftMovement: '{{ route("penjualan.pos.shifts.movement") }}',
            shiftClose: '{{ route("penjualan.pos.shifts.close") }}',
            show: '{{ route("penjualan.pos.show", ":id") }}',
            receipt: '{{ route("penjualan.pos.receipt", ":id") }}',
            labels: '{{ route("penjualan.pos.labels", ":id") }}'
        };

        let selectedProduct = null;
        let currentQuote = null;
        let cart = [];
        let quoteTimer = null;
        let quoteRequestVersion = 0;
        let lastReceiptId = null;
        let lastReceiptCanPrintLabels = false;
        let activeReceiptDocument = 'receipt';
        let activeBranchId = @json($selectedPosBranchId);
        let activeCompoundGroup = 'R/ 1';
        let savedCompoundGroups = new Set();
        let committedTransactionType = 'penjualan_bebas';
        let prescriptionModalReturnType = null;
        let prescriptionTypeApplied = false;
        let prescriptionWorkspaceMounted = false;
        let productSearchInModal = false;
        let prescriptionFlowCloseReason = null;
        let prescriptionPaymentMode = false;
        let compoundPreviewApproved = false;
        let cashierStage = 'product';
        let cashierMovementFilter = 'all';
        let cashierMovementLoading = false;
        let lastTotals = {
            grandTotal: 0,
            paidTotal: 0,
            diff: 0
        };
        let applyingPatientSelection = false;

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        function activePosBranch() {
            return posBranches.find(branch => Number(branch.id) === Number(activeBranchId)) || null;
        }

        function posBranchModalInstance() {
            const element = document.getElementById('posBranchModal');

            return element && window.bootstrap?.Modal
                ? bootstrap.Modal.getOrCreateInstance(element)
                : null;
        }

        function cashierShiftModalInstance() {
            const element = document.getElementById('cashierShiftModal');

            return element && window.bootstrap?.Modal
                ? bootstrap.Modal.getOrCreateInstance(element)
                : null;
        }

        function renderCashierShiftUi() {
            const branch = activePosBranch();
            const operational = branch?.operational || {};
            const shift = branch?.cashier_shift || null;
            const ready = Boolean(branch && operational.is_open && shift?.status === 'open');
            const cashierName = ready && shift.cashier_name && shift.cashier_name !== '-'
                ? shift.cashier_name
                : null;
            const shiftIdentity = cashierName
                ? `${shift.shift_number || '-'} · ${cashierName}`
                : (shift?.shift_number || '-');

            $('#posWorkspace').toggleClass('is-shift-locked', Boolean(branch) && !ready);
            $('#cashierShiftButton').toggleClass('is-open', ready);
            $('#cashierShiftButton').attr('title', ready
                ? `Kelola shift kasir ${shiftIdentity}`
                : 'Kelola shift kasir');
            $('#cashierShiftCaption').text(operational.is_open === false ? `Tutup · ${operational.label || '-'}` : 'Shift kasir');
            $('#cashierShiftLabel').text(ready ? shiftIdentity : (branch ? 'Belum dibuka' : 'Pilih cabang'));
            $('#cashierShiftClosedPane').toggleClass('d-none', ready);
            $('#cashierShiftOpenPane').toggleClass('d-none', !ready);
            $('#cashierShiftHeaderStatus').toggleClass('d-none', !ready);
            $('#cashierShiftModalTitle').text(ready ? 'Kelola kasir' : 'Buka kasir');
            $('#cashierShiftModalCopy').text(ready
                ? `${branch.name} · ${cashierName || 'User tidak tersedia'} · ${operational.label || 'jam operasional'}`
                : (operational.is_open === false
                    ? `Cabang tutup. Jam operasional ${operational.label || '-'}.`
                    : 'Isi modal awal sebelum menerima transaksi.'));
            $('#openCashierShiftBtn').prop('disabled', !branch || operational.is_open === false);

            if (ready) {
                $('#activeShiftNumber').text(shift.shift_number || '-');
                $('#activeShiftOpenedAt').text(`${cashierName || 'User tidak tersedia'} · Dibuka ${formatShiftTimestamp(shift.opened_at)}`);
                $('#shiftOpeningAmount').text(formatCurrency(shift.opening_amount));
                $('#shiftCashSales').text(formatCurrency(shift.cash_sales));
                $('#shiftCashIn').text(formatCurrency(shift.cash_in_total));
                $('#shiftCashOut').text(formatCurrency(shift.cash_out_total));
                $('#shiftExpectedCash').text(formatCurrency(shift.expected_cash));
                $('#shiftTransactionCount').text(`${shift.transaction_count || 0} transaksi`);
                renderCashierMovements();
            }

            return ready;
        }

        function renderCashierMovements() {
            const movements = Array.isArray(activePosBranch()?.cashier_movements)
                ? activePosBranch().cashier_movements
                : [];
            const cashInCount = movements.filter(movement => movement.type === 'cash_in').length;
            const cashOutCount = movements.filter(movement => movement.type === 'cash_out').length;
            const filteredMovements = cashierMovementFilter === 'all'
                ? movements
                : movements.filter(movement => movement.type === cashierMovementFilter);

            $('#shiftMovementCount').text(`${movements.length} aktivitas`);
            $('#shiftMovementAllCount').text(movements.length);
            $('#shiftMovementInCount').text(cashInCount);
            $('#shiftMovementOutCount').text(cashOutCount);
            $('[data-shift-movement-filter]').removeClass('is-active')
                .filter(`[data-shift-movement-filter="${cashierMovementFilter}"]`).addClass('is-active');

            if (cashierMovementLoading) {
                $('#cashierShiftMovementList').html('<div class="pos-shift-movement-loading"><span><i class="mdi mdi-loading mdi-spin"></i></span><small>Memuat rincian arus kas terbaru...</small></div>');
                return;
            }

            if (!filteredMovements.length) {
                const filteredLabel = cashierMovementFilter === 'cash_in'
                    ? 'kas masuk'
                    : (cashierMovementFilter === 'cash_out' ? 'kas keluar' : 'pergerakan kas');
                $('#cashierShiftMovementList').html(`
                    <div class="pos-shift-movement-empty">
                        <span><i class="mdi mdi-cash-sync"></i></span>
                        <strong>Belum ada ${filteredLabel}</strong>
                        <small>Aktivitas yang dicatat pada shift ini akan tampil lengkap dengan waktu, petugas, dan keterangannya.</small>
                    </div>`);
                return;
            }

            $('#cashierShiftMovementList').html(filteredMovements.map(movement => {
                const isCashIn = movement.type === 'cash_in';
                const label = movement.type_label || (isCashIn ? 'Kas Masuk' : 'Kas Keluar');
                const occurredAt = movement.occurred_at_label || movement.occurred_at || '-';

                return `
                    <article class="pos-shift-movement-item ${isCashIn ? 'is-in' : 'is-out'}">
                        <span class="pos-shift-movement-icon"><i class="mdi ${isCashIn ? 'mdi-arrow-bottom-left' : 'mdi-arrow-top-right'}"></i></span>
                        <div class="pos-shift-movement-copy">
                            <span><strong>${escapeHtml(label)}</strong><time datetime="${escapeHtml(movement.occurred_at || '')}">${escapeHtml(occurredAt)}</time></span>
                            <p title="${escapeHtml(movement.description || '-')}">${escapeHtml(movement.description || '-')}</p>
                            <small><i class="mdi mdi-account-outline"></i> Dicatat oleh ${escapeHtml(movement.created_by || '-')}</small>
                        </div>
                        <strong class="pos-shift-movement-amount">${isCashIn ? '+' : '−'} ${formatCurrency(movement.amount)}</strong>
                    </article>`;
            }).join(''));
        }

        function formatShiftTimestamp(value) {
            const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);

            return match ? `${match[3]}/${match[2]}/${match[1]} · ${match[4]}:${match[5]}` : (value || '-');
        }

        function setCashierMovementLoading(isLoading) {
            cashierMovementLoading = Boolean(isLoading);
            $('#refreshCashierMovements').prop('disabled', cashierMovementLoading)
                .find('i').toggleClass('mdi-spin', cashierMovementLoading);
            renderCashierMovements();
        }

        function updatePosBranchUi() {
            const branch = activePosBranch();
            const ready = Boolean(branch);
            const shiftReady = Boolean(branch?.cashier_shift && branch?.operational?.is_open);

            $('#activePosBranchName').text(branch?.name || 'Pilih cabang');
            $('#posBranchSelector').val(branch?.id || '');
            $('#confirmPosBranchBtn').prop('disabled', !$('#posBranchSelector').val());
            $('#posWorkspace').toggleClass('is-branch-locked', canSwitchPosBranch && !ready);
            $('#productSearch').prop('disabled', !ready || !shiftReady).trigger('change.select2');
            $('#patientSelect').prop('disabled', !ready || !shiftReady).trigger('change.select2');
            renderCashierShiftUi();
        }

        function openPosBranchModal() {
            updatePosBranchUi();
            posBranchModalInstance()?.show();
        }

        function applyPosBranch(branchId, notify = true) {
            const branch = posBranches.find(item => Number(item.id) === Number(branchId));

            if (!branch) {
                Swal.fire('Cabang tidak tersedia', 'Pilih cabang aktif yang tersedia.', 'warning');
                return;
            }

            if (branch.operational?.is_open === false) {
                Swal.fire('Di luar jam operasional', `Kasir ${branch.name} buka pada ${branch.operational.label}.`, 'warning');
                return;
            }

            activeBranchId = Number(branch.id);
            updatePosBranchUi();
            newTransaction(false);
            posBranchModalInstance()?.hide();

            if (!branch.cashier_shift) {
                window.setTimeout(() => cashierShiftModalInstance()?.show(), 180);
            }

            if (notify) {
                Swal.fire({
                    icon: 'success',
                    title: `Cabang ${branch.name} aktif`,
                    toast: true,
                    position: 'top-end',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        }

        $('#posBranchSelector').on('change', function() {
            $('#confirmPosBranchBtn').prop('disabled', !$(this).val());
        });

        $('#posBranchButton').on('click', openPosBranchModal);

        $('#confirmPosBranchBtn').on('click', function() {
            const branchId = Number($('#posBranchSelector').val());

            if (Number(activeBranchId) === branchId) {
                posBranchModalInstance()?.hide();
                if (!activePosBranch()?.cashier_shift) window.setTimeout(() => cashierShiftModalInstance()?.show(), 180);
                return;
            }

            if (Number(activeBranchId) !== branchId && cart.length > 0) {
                Swal.fire({
                    title: 'Ganti cabang POS?',
                    text: 'Keranjang dan data transaksi saat ini akan dikosongkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, ganti cabang',
                    cancelButtonText: 'Batalkan'
                }).then(result => {
                    if (result.isConfirmed) applyPosBranch(branchId);
                });
                return;
            }

            applyPosBranch(branchId);
        });

        function refreshCashierShift(openModal = false) {
            const branch = activePosBranch();

            if (!branch) return $.Deferred().reject().promise();

            if (openModal) {
                cashierMovementFilter = 'all';
                cashierShiftModalInstance()?.show();
                if (branch.cashier_shift) setCashierMovementLoading(true);
            }

            return $.get(urls.shiftStatus, { branch_id: branch.id })
                .done(response => {
                    branch.operational = response.operational || branch.operational;
                    branch.cashier_shift = response.shift || null;
                    branch.cashier_movements = Array.isArray(response.movements) ? response.movements : [];
                    updatePosBranchUi();
                })
                .fail(xhr => showAjaxError(xhr, 'Status shift gagal dimuat.'))
                .always(() => {
                    if (openModal) setCashierMovementLoading(false);
                });
        }

        $('#cashierShiftButton').on('click', function() {
            if (!activePosBranch()) {
                openPosBranchModal();
                return;
            }

            refreshCashierShift(true);
        });

        $(document).on('click', '[data-shift-movement-filter]', function() {
            cashierMovementFilter = $(this).data('shift-movement-filter') || 'all';
            renderCashierMovements();
        });

        $('#refreshCashierMovements').on('click', function() {
            setCashierMovementLoading(true);
            refreshCashierShift().always(() => setCashierMovementLoading(false));
        });

        $('#openCashierShiftForm').on('submit', function(event) {
            event.preventDefault();
            const branch = activePosBranch();

            if (!branch) {
                openPosBranchModal();
                return;
            }

            const button = $('#openCashierShiftBtn');
            const normalHtml = button.html();
            button.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Membuka kasir...');

            $.post(urls.shiftOpen, {
                branch_id: branch.id,
                opening_amount: Number($('#openingCashAmount').val()) || 0,
                opening_notes: $('#openingCashNotes').val()
            }).done(response => {
                branch.cashier_shift = response.shift;
                branch.cashier_movements = [];
                $('#openingCashAmount').val(0);
                $('#openingCashNotes').val('');
                updatePosBranchUi();
                cashierShiftModalInstance()?.hide();
                Swal.fire({ icon: 'success', title: response.message, toast: true, position: 'top-end', timer: 2200, showConfirmButton: false });
            }).fail(xhr => showAjaxError(xhr, 'Kasir gagal dibuka.'))
                .always(() => button.prop('disabled', false).html(normalHtml));
        });

        $('[data-cash-movement]').on('click', function() {
            const type = $(this).data('cash-movement');
            const isCashIn = type === 'cash_in';

            Swal.fire({
                title: isCashIn ? 'Catat kas masuk' : 'Catat kas keluar',
                html: `
                    <div class="text-start">
                        <label class="form-label" for="shiftMovementAmount">Nominal</label>
                        <input type="number" min="1" step="100" id="shiftMovementAmount" class="swal2-input m-0 w-100" placeholder="0">
                        <label class="form-label mt-3" for="shiftMovementDescription">Keterangan</label>
                        <textarea id="shiftMovementDescription" class="swal2-textarea m-0 w-100" placeholder="Jelaskan sumber atau tujuan kas"></textarea>
                    </div>`,
                showCancelButton: true,
                confirmButtonText: isCashIn ? 'Simpan kas masuk' : 'Simpan kas keluar',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const amount = Number($('#shiftMovementAmount').val()) || 0;
                    const description = String($('#shiftMovementDescription').val() || '').trim();
                    if (amount <= 0 || !description) {
                        Swal.showValidationMessage('Nominal dan keterangan wajib diisi.');
                        return false;
                    }
                    return { amount, description };
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                const branch = activePosBranch();
                $.post(urls.shiftMovement, {
                    branch_id: branch.id,
                    type,
                    amount: result.value.amount,
                    description: result.value.description
                }).done(response => {
                    branch.cashier_shift = response.shift;
                    branch.cashier_movements = Array.isArray(response.movements) ? response.movements : [];
                    renderCashierShiftUi();
                    Swal.fire({ icon: 'success', title: response.message, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                }).fail(xhr => showAjaxError(xhr, 'Mutasi kas gagal disimpan.'));
            });
        });

        $('#closeCashierShiftBtn').on('click', function() {
            const branch = activePosBranch();
            const shift = branch?.cashier_shift;
            if (!shift) return;

            Swal.fire({
                title: 'Tutup kasir',
                html: `
                    <div class="text-start">
                        <div class="alert alert-light border">Kas seharusnya <strong>${formatCurrency(shift.expected_cash)}</strong></div>
                        <label class="form-label" for="shiftActualCash">Kas fisik saat ini</label>
                        <input type="number" min="0" step="100" id="shiftActualCash" class="swal2-input m-0 w-100" value="${Number(shift.expected_cash) || 0}">
                        <label class="form-label mt-3" for="shiftClosingNotes">Catatan penutupan</label>
                        <textarea id="shiftClosingNotes" class="swal2-textarea m-0 w-100" placeholder="Opsional"></textarea>
                    </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Hitung & tutup kasir',
                cancelButtonText: 'Batal',
                preConfirm: () => ({
                    actual_cash: Math.max(0, Number($('#shiftActualCash').val()) || 0),
                    closing_notes: $('#shiftClosingNotes').val()
                })
            }).then(result => {
                if (!result.isConfirmed) return;
                $.post(urls.shiftClose, {
                    branch_id: branch.id,
                    ...result.value
                }).done(response => {
                    const closedShift = response.shift;
                    branch.cashier_shift = null;
                    updatePosBranchUi();
                    cashierShiftModalInstance()?.hide();
                    const difference = Number(closedShift.cash_difference) || 0;
                    Swal.fire({
                        icon: Math.abs(difference) < .01 ? 'success' : 'warning',
                        title: 'Kasir ditutup',
                        html: `Kas fisik <b>${formatCurrency(closedShift.actual_cash)}</b><br>Selisih kas <b>${formatCurrency(difference)}</b>`,
                        confirmButtonText: 'Selesai'
                    });
                }).fail(xhr => showAjaxError(xhr, 'Kasir gagal ditutup.'));
            });
        });

        function posReceiptModalInstance() {
            const element = document.getElementById('posReceiptModal');

            return element && window.bootstrap?.Modal
                ? bootstrap.Modal.getOrCreateInstance(element)
                : null;
        }

        function receiptPreviewUrl(id, documentType = 'receipt', autoPrint = false) {
            const documentUrl = (documentType === 'labels' ? urls.labels : urls.receipt).replace(':id', id);
            const separator = documentUrl.includes('?') ? '&' : '?';
            const params = new URLSearchParams({
                embedded: '1',
                autoprint: autoPrint ? '1' : '0'
            });

            return `${documentUrl}${separator}${params.toString()}`;
        }

        function loadReceiptDocument(documentType = 'receipt', autoPrint = false) {
            if (!lastReceiptId || (documentType === 'labels' && !lastReceiptCanPrintLabels)) return;

            activeReceiptDocument = documentType;
            const isLabels = documentType === 'labels';
            $('[data-receipt-document]')
                .removeClass('is-active')
                .attr('aria-selected', 'false')
                .filter(`[data-receipt-document="${documentType}"]`)
                .addClass('is-active')
                .attr('aria-selected', 'true');
            $('#posReceiptModalTitle').text(isLabels ? 'Preview etiket resep' : 'Preview struk');
            $('#posReceiptModalCopy').text(isLabels
                ? 'Etiket siap dicetak dengan konfigurasi kertas thermal 80 mm seperti struk.'
                : 'Struk siap dicetak tanpa membuka tab browser baru.');
            $('.pos-receipt-modal-icon').html(`<i class="mdi ${isLabels ? 'mdi-label-multiple-outline' : 'mdi-receipt-text-check-outline'}"></i>`);
            $('#printReceiptModalBtn').prop('disabled', true).find('span').text(isLabels ? 'Cetak etiket' : 'Cetak struk');
            $('#posReceiptLoading').removeClass('is-hidden').find('span').text(isLabels ? 'Menyiapkan etiket...' : 'Menyiapkan struk...');
            $('#posReceiptFrame').attr('src', receiptPreviewUrl(lastReceiptId, documentType, autoPrint));
        }

        function showReceiptModal(id, autoPrint = false, canPrintLabels = lastReceiptCanPrintLabels) {
            const modalElement = document.getElementById('posReceiptModal');
            const modal = posReceiptModalInstance();

            if (!id || !modalElement || !modal) return;

            lastReceiptId = Number(id);
            lastReceiptCanPrintLabels = Boolean(canPrintLabels);
            activeReceiptDocument = 'receipt';
            $('#printLastReceiptBtn').prop('disabled', false);
            $('#printReceiptModalBtn').prop('disabled', true);
            $('#labelsDocumentTab').toggleClass('d-none', !lastReceiptCanPrintLabels);
            $('#posReceiptLoading').removeClass('is-hidden');

            const loadReceipt = () => {
                loadReceiptDocument('receipt', autoPrint);
            };

            if ($(modalElement).hasClass('show')) {
                loadReceipt();
                return;
            }

            modalElement.addEventListener('shown.bs.modal', loadReceipt, { once: true });
            modal.show();
        }

        $('#posReceiptFrame').on('load', function() {
            if ($(this).attr('src') !== 'about:blank') {
                $('#posReceiptLoading').addClass('is-hidden');
                $('#printReceiptModalBtn').prop('disabled', false);
            }
        });

        $('#posReceiptModal').on('hidden.bs.modal', function() {
            $('#posReceiptFrame').attr('src', 'about:blank');
            $('#posReceiptLoading').removeClass('is-hidden');
            $('#printReceiptModalBtn').prop('disabled', true);
        });

        $('[data-receipt-document]').on('click', function() {
            loadReceiptDocument($(this).data('receipt-document'), false);
        });

        $('#printReceiptModalBtn').on('click', function() {
            const receiptWindow = document.getElementById('posReceiptFrame')?.contentWindow;

            if (!receiptWindow) {
                Swal.fire('Struk belum siap', 'Tunggu sampai preview struk selesai dimuat.', 'warning');
                return;
            }

            receiptWindow.focus();
            receiptWindow.print();
        });

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [character];
            });
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(Number(value) || 0);
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 2
            }).format(Number(value) || 0);
        }

        function toNumber(value) {
            return Number(value) || 0;
        }

        function currentTransactionType() {
            const selectedType = $('#jenisTransaksi').val();

            return selectedType === 'penjualan_resep'
                ? ($('#jenisResep').val() || 'penjualan_resep')
                : selectedType;
        }

        function isCreditTransaction() {
            return ['penjualan_kredit', 'penjualan_instansi'].includes(currentTransactionType());
        }

        function isPrescriptionTransaction(type = currentTransactionType()) {
            return ['penjualan_resep', 'penjualan_racikan'].includes(type);
        }

        function isCompoundPrescription(type = currentTransactionType()) {
            return type === 'penjualan_racikan';
        }

        const compoundSharedFields = [
            'aturan_pakai',
            'waktu_konsumsi',
            'durasi_hari',
            'keterangan',
            'bentuk_racikan',
            'jumlah_racikan',
            'jumlah_ambil_resep',
            'signa_1',
            'signa_2',
            'embalase_racikan'
        ];

        function normalizeCompoundGroup(value) {
            const normalized = String(value || '').trim();
            return normalized || 'R/ 1';
        }

        function compoundGroupNumber(value) {
            const match = normalizeCompoundGroup(value).match(/(\d+)/);
            return match ? Number(match[1]) : Number.MAX_SAFE_INTEGER;
        }

        function compoundGroups() {
            const groups = new Set([activeCompoundGroup]);

            cart.forEach(item => {
                if (String(item.racikan_group || '').trim()) {
                    groups.add(normalizeCompoundGroup(item.racikan_group));
                }
            });

            return Array.from(groups).sort((left, right) => {
                const numberDiff = compoundGroupNumber(left) - compoundGroupNumber(right);
                return numberDiff || left.localeCompare(right, 'id');
            });
        }

        function nextCompoundGroup() {
            const highest = compoundGroups().reduce((max, group) => {
                const number = compoundGroupNumber(group);
                return number !== Number.MAX_SAFE_INTEGER ? Math.max(max, number) : max;
            }, 0);

            return `R/ ${highest + 1}`;
        }

        function compoundGroupItems(group) {
            const normalizedGroup = normalizeCompoundGroup(group);
            return cart.filter(item => normalizeCompoundGroup(item.racikan_group) === normalizedGroup);
        }

        function compoundGroupLabel(group) {
            const number = compoundGroupNumber(group);
            return number === Number.MAX_SAFE_INTEGER ? normalizeCompoundGroup(group) : `Racikan ${number}`;
        }

        function compoundGroupReference(group) {
            return compoundGroupItems(group)[0] || null;
        }

        function composeCompoundSigna(reference = {}) {
            const signa1 = String(reference.signa_1 || '').trim();
            const signa2 = String(reference.signa_2 || '').trim();
            const form = String(reference.bentuk_racikan || 'dosis').trim().toLowerCase();

            if (!signa1 || !signa2) {
                return '';
            }

            return `${signa1} x sehari ${signa2} ${form}`;
        }

        function parseClinicalNumber(value) {
            const normalized = String(value ?? '').trim().replace(',', '.');
            const match = normalized.match(/(\d+(?:\.\d+)?)(?:\s*\/\s*(\d+(?:\.\d+)?))?/);

            if (!match) {
                return null;
            }

            const numerator = Number(match[1]);
            const denominator = match[2] ? Number(match[2]) : 1;
            const result = denominator > 0 ? numerator / denominator : 0;

            return Number.isFinite(result) && result > 0 ? result : null;
        }

        function parseClinicalMeasurement(value) {
            const normalized = String(value ?? '')
                .trim()
                .toLowerCase()
                .replace(',', '.')
                .replace(/[μµ]/g, 'u');
            const concentrationPattern = /\d+(?:\.\d+)?\s*[a-z%]+\s*\/\s*\d*(?:\.\d+)?\s*[a-z%]+/;

            if (concentrationPattern.test(normalized)) {
                return null;
            }

            const match = normalized.match(/(\d+(?:\.\d+)?)(?:\s*\/\s*(\d+(?:\.\d+)?))?\s*([a-z%]+)?/);

            if (!match) {
                return null;
            }

            const numerator = Number(match[1]);
            const denominator = match[2] ? Number(match[2]) : 1;
            const rawValue = denominator > 0 ? numerator / denominator : 0;
            const unit = String(match[3] || '').toLowerCase();
            const units = {
                mcg: ['mass', 0.001, 'mg'],
                ug: ['mass', 0.001, 'mg'],
                mg: ['mass', 1, 'mg'],
                g: ['mass', 1000, 'mg'],
                gram: ['mass', 1000, 'mg'],
                kg: ['mass', 1000000, 'mg'],
                ml: ['volume', 1, 'ml'],
                cc: ['volume', 1, 'ml'],
                l: ['volume', 1000, 'ml'],
                iu: ['activity', 1, 'IU'],
                ui: ['activity', 1, 'IU'],
                '%': ['percent', 1, '%'],
                tablet: ['count', 1, 'unit'],
                tab: ['count', 1, 'unit'],
                kaplet: ['count', 1, 'unit'],
                kapsul: ['count', 1, 'unit'],
                capsule: ['count', 1, 'unit'],
                bungkus: ['count', 1, 'unit'],
                sachet: ['count', 1, 'unit'],
                tetes: ['count', 1, 'unit'],
                '': ['count', 1, 'unit']
            };
            const definition = units[unit];

            if (!definition || !Number.isFinite(rawValue) || rawValue <= 0) {
                return null;
            }

            return {
                family: definition[0],
                value: rawValue * definition[1],
                unit: definition[2]
            };
        }

        function roundCompoundQuantity(value) {
            return Math.round((Number(value) || 0) * 100) / 100;
        }

        function automaticCompoundDays(reference = {}) {
            const prescribed = Number(reference.jumlah_racikan) || 0;
            const signa1 = parseClinicalNumber(reference.signa_1);
            const signa2 = parseClinicalNumber(reference.signa_2);

            if (prescribed <= 0 || !signa1 || !signa2) {
                return null;
            }

            return Math.max(1, Math.ceil(prescribed / (signa1 * signa2)));
        }

        function automaticCompoundTake(reference = {}) {
            const prescribed = Number(reference.jumlah_racikan) || 0;
            const signa1 = parseClinicalNumber(reference.signa_1);
            const signa2 = parseClinicalNumber(reference.signa_2);
            const days = Number(reference.durasi_hari) || 0;

            if (prescribed <= 0 || !signa1 || !signa2 || days <= 0) {
                return null;
            }

            return roundCompoundQuantity(Math.min(prescribed, signa1 * signa2 * days));
        }

        function isAutomaticCompoundDose(item = {}) {
            const strength = String(item.kekuatan_obat || '').trim().toLowerCase().replace(',', '.').replace(/\s+/g, ' ');
            const dose = String(item.dosis_komponen || '').trim().toLowerCase().replace(',', '.').replace(/\s+/g, ' ');

            return Boolean(strength && dose && strength === dose);
        }

        function usesWholeCompoundUnit(item = {}) {
            const unit = `${item.satuan || ''} ${item.satuan_stok || ''}`.toLowerCase();

            return /\b(tablet|tab|kaplet|kapsul|capsule|pil|bungkus|sachet)\b/.test(unit);
        }

        function compoundDispensedQuantity(exactQuantity, item = {}) {
            const exact = roundCompoundQuantity(exactQuantity);

            return usesWholeCompoundUnit(item) ? Math.ceil(exact) : exact;
        }

        function compoundItemCalculation(item, reference = compoundGroupReference(item.racikan_group) || {}) {
            const strength = parseClinicalMeasurement(item.kekuatan_obat);
            const dose = parseClinicalMeasurement(item.dosis_komponen);
            const compoundQty = Number(reference.jumlah_racikan) || 0;
            const compoundTake = Number(reference.jumlah_ambil_resep) || 0;
            const compatible = Boolean(strength && dose && strength.family === dose.family && strength.value > 0);
            const calculatedPrescription = compatible && compoundQty > 0
                ? roundCompoundQuantity((dose.value / strength.value) * compoundQty)
                : null;
            const prescriptionQty = calculatedPrescription || (Number(item.jumlah_resep) || 0);
            const calculatedTake = prescriptionQty > 0 && compoundQty > 0 && compoundTake > 0
                ? roundCompoundQuantity(prescriptionQty * (compoundTake / compoundQty))
                : null;
            const dispensedTake = calculatedTake ? compoundDispensedQuantity(calculatedTake, item) : null;

            return {
                compatible,
                calculatedPrescription,
                calculatedTake,
                dispensedTake,
                strength,
                dose
            };
        }

        function recalculateCompoundGroup(group, options = {}) {
            const normalizedGroup = normalizeCompoundGroup(group);
            const items = compoundGroupItems(normalizedGroup);
            const reference = items[0];

            if (!reference) {
                return [];
            }

            if (options.updateGroupDays && !reference._durasi_hari_manual) {
                const calculatedDays = automaticCompoundDays(reference);
                if (calculatedDays) {
                    items.forEach(item => {
                        item.durasi_hari = calculatedDays;
                    });
                }
            }

            if (options.updateGroupTake) {
                const calculatedTake = automaticCompoundTake(reference);
                if (calculatedTake) {
                    items.forEach(item => {
                        item.jumlah_ambil_resep = calculatedTake;
                    });
                }
            }

            synchronizeCompoundGroup(normalizedGroup);
            const changedQtyItems = [];
            const targets = options.targetItem ? [options.targetItem] : items;

            targets.forEach(item => {
                const calculation = compoundItemCalculation(item, reference);
                if (options.updatePrescriptionQuantity && calculation.calculatedPrescription) {
                    item.jumlah_resep = calculation.calculatedPrescription;
                }

                const finalCalculation = compoundItemCalculation(item, reference);
                if (finalCalculation.dispensedTake && Math.abs((Number(item.qty) || 0) - finalCalculation.dispensedTake) > 0.001) {
                    item.qty = finalCalculation.dispensedTake;
                    changedQtyItems.push(item);
                }
            });

            return changedQtyItems;
        }

        function compoundCalculationMessage(item, reference) {
            const calculation = compoundItemCalculation(item, reference);

            if (!String(item.kekuatan_obat || '').trim() || !String(item.dosis_komponen || '').trim()) {
                return {
                    status: 'waiting',
                    text: 'Isi Dosis Resep; Kekuatan Obat diambil otomatis dari master bila tersedia.'
                };
            }

            if (!calculation.compatible) {
                return {
                    status: 'manual',
                    text: 'Unit tidak cocok atau berbentuk konsentrasi. Verifikasi lalu isi Jumlah Resep secara manual.'
                };
            }

            if (!calculation.calculatedPrescription || !calculation.calculatedTake) {
                return {
                    status: 'waiting',
                    text: 'Lengkapi jumlah dan aturan racikan untuk menghitung jumlah obat.'
                };
            }

            const prescriptionMatches = Math.abs((Number(item.jumlah_resep) || 0) - calculation.calculatedPrescription) < 0.001;
            const takeMatches = Math.abs((Number(item.qty) || 0) - calculation.dispensedTake) < 0.001;
            if (!prescriptionMatches || !takeMatches) {
                return {
                    status: 'manual',
                    text: `Disesuaikan manual. Saran sistem: kebutuhan tepat ${formatNumber(calculation.calculatedTake)} dan stok keluar ${formatNumber(calculation.dispensedTake)} ${item.satuan}.`
                };
            }

            if (Math.abs(calculation.dispensedTake - calculation.calculatedTake) > 0.001) {
                return {
                    status: 'calculated',
                    text: `Kebutuhan tepat ${formatNumber(calculation.calculatedTake)} ${item.satuan}; stok keluar dibulatkan ke atas menjadi ${formatNumber(calculation.dispensedTake)} ${item.satuan}.`
                };
            }

            return {
                status: 'calculated',
                text: `Otomatis: ${formatNumber(calculation.calculatedPrescription)} ${item.satuan} diresepkan, ${formatNumber(calculation.dispensedTake)} ${item.satuan} dikeluarkan.`
            };
        }

        function compoundGroupCheck(group) {
            const items = compoundGroupItems(group);
            const reference = items[0] || {};
            const missing = [
                ['Bentuk racikan', reference.bentuk_racikan],
                ['Jumlah racikan', Number(reference.jumlah_racikan) > 0],
                ['Jumlah ambil resep', Number(reference.jumlah_ambil_resep) > 0],
                ['Signa 1', reference.signa_1],
                ['Signa 2', reference.signa_2],
                ['JHO', Number(reference.durasi_hari) > 0]
            ].filter(([, value]) => !value).map(([label]) => label);
            const invalidComponent = items.find(item => !String(item.dosis_komponen || '').trim() || !(Number(item.jumlah_resep) > 0) || !(Number(item.qty) > 0));

            return {
                valid: items.length > 0 && missing.length === 0 && !invalidComponent,
                hasItems: items.length > 0,
                missing,
                invalidComponent
            };
        }

        function isCompoundGroupSaved(group) {
            return savedCompoundGroups.has(normalizeCompoundGroup(group));
        }

        function compoundEmbalaseTotal() {
            return compoundGroups().reduce((total, group) => {
                return total + Math.max(0, Number(compoundGroupReference(group)?.embalase_racikan) || 0);
            }, 0);
        }

        function markCompoundGroupUnsaved(group) {
            savedCompoundGroups.delete(normalizeCompoundGroup(group));
        }

        function synchronizeCompoundGroup(group) {
            const items = compoundGroupItems(group);
            const reference = items[0];

            if (!reference) {
                return;
            }

            items.slice(1).forEach(item => {
                compoundSharedFields.forEach(field => {
                    item[field] = reference[field] ?? '';
                });
            });

            const signa = composeCompoundSigna(reference);
            items.forEach(item => {
                item.aturan_pakai = signa;
            });
        }

        function compoundGroupOptions(selectedGroup) {
            const selected = normalizeCompoundGroup(selectedGroup);
            const groups = new Set([...compoundGroups(), selected]);

            return Array.from(groups)
                .sort((left, right) => compoundGroupNumber(left) - compoundGroupNumber(right) || left.localeCompare(right, 'id'))
                .map(group => `<option value="${escapeHtml(group)}" ${group === selected ? 'selected' : ''}>${escapeHtml(group)}</option>`)
                .join('');
        }

        function isPrescriptionItemComplete(item) {
            const hasSigna = Boolean(String(item.aturan_pakai || '').trim());

            if (!isCompoundPrescription()) {
                return hasSigna;
            }

            const group = normalizeCompoundGroup(item.racikan_group);

            return hasSigna
                && isCompoundGroupSaved(group)
                && compoundGroupCheck(group).valid
                && Boolean(String(item.racikan_group || '').trim())
                && Boolean(String(item.dosis_komponen || '').trim())
                && Number(item.jumlah_resep) > 0
                && Number(item.qty) > 0;
        }

        function renderCompoundSetup() {
            if (!isCompoundPrescription()) {
                return;
            }

            activeCompoundGroup = normalizeCompoundGroup(activeCompoundGroup);
            const groups = compoundGroups();
            $('#activeCompoundGroup').html(compoundGroupOptions(activeCompoundGroup)).val(activeCompoundGroup);

            const summary = groups.map(group => {
                const count = compoundGroupItems(group).length;
                const active = group === activeCompoundGroup;
                const saved = isCompoundGroupSaved(group);
                return `
                    <button type="button" class="pos-compound-summary-chip ${active ? 'is-active' : ''} ${saved ? 'is-saved' : ''}" data-compound-group="${escapeHtml(group)}">
                        <b>${escapeHtml(compoundGroupLabel(group))}</b>
                        <span>${saved ? '<i class="mdi mdi-check-circle"></i> tersimpan' : (count > 0 ? `${count} komponen` : 'belum ada obat')}</span>
                    </button>
                `;
            }).join('');

            $('#compoundGroupSummary').html(summary || '<span class="pos-compound-summary-empty">Belum ada kelompok racikan.</span>');
            const activeItems = compoundGroupItems(activeCompoundGroup);
            const activeSaved = isCompoundGroupSaved(activeCompoundGroup);
            const hasSavedCompound = groups.some(group => isCompoundGroupSaved(group) && compoundGroupItems(group).length > 0);
            $('#cartEditorTitle').text(compoundGroupLabel(activeCompoundGroup));
            $('#cartEditorCopy').text(activeItems.length > 0
                ? `${activeItems.length} komponen ${activeSaved ? 'tersimpan' : 'sedang disusun'}`
                : 'Belum ada komponen');
            $('#clearCartBtn')
                .toggleClass('d-none', activeItems.length === 0)
                .html('<i class="mdi mdi-trash-can-outline"></i> Hapus semua');
            $('#newCompoundGroupBtn').prop('disabled', !activeSaved);
            $('#compoundSaveRule').toggleClass('is-ready', activeSaved);
            $('#compoundSaveRuleCopy').text(activeSaved
                ? 'Tersimpan. Racikan baru sudah dapat dibuat.'
                : (activeItems.length > 0
                    ? `${activeItems.length} komponen perlu dilengkapi dan disimpan.`
                    : (hasSavedCompound
                        ? 'Opsional. Lanjut bayar bila resep sudah selesai.'
                        : 'Belum ada obat pada racikan ini.')));
        }

        function updateTransactionState() {
            const state = $('#transactionState');
            const draftId = $('#draftId').val();
            const itemText = `${cart.length} item`;

            state.removeClass('is-ready is-draft');

            if (draftId) {
                state.addClass('is-draft').html(`<i class="mdi mdi-content-save-check-outline"></i><span>Draft #${escapeHtml(draftId)} tersimpan</span>`);
                return;
            }

            if (cart.length > 0) {
                state.addClass('is-ready').html(`<i class="mdi mdi-circle-medium"></i><span>${itemText} siap diproses</span>`);
                return;
            }

            state.html('<i class="mdi mdi-circle-medium"></i><span>Belum disimpan</span>');
        }

        function localDateTimeValue(date = new Date()) {
            const pad = value => String(value).padStart(2, '0');

            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function updateClock() {
            $('#posClock').text(new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short'
            }).format(new Date()));
        }

        updateClock();
        setInterval(updateClock, 30000);

        function updateConnectionState() {
            const isOnline = navigator.onLine;
            $('#posConnection')
                .toggleClass('is-offline', !isOnline)
                .html(`<i class="mdi mdi-circle"></i> ${isOnline ? 'Sistem online' : 'Mode offline'}`);
        }

        window.addEventListener('online', updateConnectionState);
        window.addEventListener('offline', updateConnectionState);
        updateConnectionState();

        function compactTenderLabel(amount) {
            const value = Math.max(0, Number(amount) || 0);

            if (value >= 1000000) {
                const millions = value / 1000000;
                return `Rp${Number.isInteger(millions) ? millions : millions.toFixed(1).replace('.', ',')}jt`;
            }

            if (value >= 1000) {
                const thousands = value / 1000;
                return `Rp${Number.isInteger(thousands) ? thousands : thousands.toFixed(1).replace('.', ',')}rb`;
            }

            return formatCurrency(value);
        }

        function updateQuickTenderOptions(grandTotal) {
            const activeRow = $('.pos-payment-row.is-active').first().length
                ? $('.pos-payment-row.is-active').first()
                : $('.pos-payment-row').first();
            const otherPaid = $('.pos-payment-row').not(activeRow).toArray().reduce((sum, row) => {
                return sum + Math.max(0, Number($(row).find('.payment-amount').val()) || 0);
            }, 0);
            const targetAmount = Math.max(0, Number(grandTotal) - otherPaid);
            const fallback = [10000, 20000, 50000, 100000];
            const roundingSteps = [1000, 5000, 10000, 20000, 50000, 100000, 200000, 500000, 1000000];
            const candidates = [];

            if (targetAmount > 0) {
                roundingSteps.forEach(step => {
                    const rounded = Math.ceil(targetAmount / step) * step;
                    if (rounded > targetAmount) candidates.push(rounded);
                });

                let nextLargeAmount = Math.ceil(targetAmount / 100000) * 100000;
                while (candidates.length < 8) {
                    nextLargeAmount += 100000;
                    candidates.push(nextLargeAmount);
                }
            }

            const suggestions = [...new Set(candidates.length ? candidates : fallback)]
                .sort((left, right) => left - right)
                .slice(0, 4);

            $('.pos-quick-payment[data-amount="exact"] span').text(
                targetAmount > 0 ? `Bayar pas · ${formatCurrency(targetAmount)}` : 'Bayar pas'
            );

            $('.pos-quick-payment:not(.is-exact)').each(function(index) {
                const amount = suggestions[index] || fallback[index];
                $(this).attr('data-amount', amount).data('amount', amount).text(compactTenderLabel(amount));
            });
        }

        function updateFlow() {
            const hasItems = cart.length > 0;
            const canSettle = hasItems && (isCreditTransaction() || lastTotals.diff >= -0.01) && !cart.some(item => !item.is_available);

            $('#posFlowProduct, #posFlowCart, #posFlowPayment').removeClass('is-active is-complete');
            if (cashierStage === 'payment') {
                $('#posFlowProduct, #posFlowCart').addClass('is-complete');
                $('#posFlowPayment').addClass('is-active');
            } else if (cashierStage === 'cart') {
                $('#posFlowProduct').addClass('is-complete');
                $('#posFlowCart').addClass('is-active');
            } else {
                $('#posFlowProduct').addClass('is-active');
                $('#posFlowCart').toggleClass('is-complete', hasItems);
            }
            $('#posFlowProduct, #posFlowCart, #posFlowPayment').each(function() {
                $(this).attr('aria-current', $(this).hasClass('is-active') ? 'step' : null);
            });
            $('#posFlowProgress').css('width', `${cashierStage === 'payment' ? (canSettle ? 96 : 69) : (cashierStage === 'cart' ? 48 : 8)}%`);

            $('#headerCartCount').text(formatNumber(cart.length));
            $('#headerGrandTotal').text(formatCurrency(lastTotals.grandTotal));
            $('.pos-quick-payment[data-amount="exact"] span').text(
                lastTotals.grandTotal > 0 ? `Bayar pas · ${formatCurrency(lastTotals.grandTotal)}` : 'Bayar pas'
            );

            updateQuickTenderOptions(lastTotals.grandTotal);
            updateCatalogFlow();
        }

        function updateCatalogFlow() {
            const hasSelection = Boolean(selectedProduct);
            const hasQuote = Boolean(currentQuote);

            $('#catalogPhaseSearch, #catalogPhaseConfigure, #catalogPhaseStock').removeClass('is-active is-complete');
            $('#catalogPhaseSearch').toggleClass('is-active', !hasSelection).toggleClass('is-complete', hasSelection);
            $('#catalogPhaseConfigure').toggleClass('is-active', hasSelection && !hasQuote).toggleClass('is-complete', hasQuote);
            $('#catalogPhaseStock')
                .toggleClass('is-active', hasQuote && !Boolean(currentQuote?.is_available))
                .toggleClass('is-complete', hasQuote && Boolean(currentQuote?.is_available));

            $('.pos-selection-block, .pos-config-block, .pos-stock-block').toggleClass('has-selection', hasSelection);
            $('#selectedProductState')
                .toggleClass('is-ready', hasSelection)
                .html(hasSelection
                    ? '<i class="mdi mdi-check-circle"></i> Produk terpilih'
                    : '<i class="mdi mdi-circle-outline"></i> Menunggu pilihan');
        }

        function setProductSearchFeedback(message, tone = 'default', icon = 'mdi-information-outline') {
            const feedback = $('#productSearchStatus');
            feedback
                .removeClass('is-success is-warning')
                .toggleClass('is-success', tone === 'success')
                .toggleClass('is-warning', tone === 'warning')
                .html(`<i class="mdi ${icon}"></i><span>${escapeHtml(message)}</span>`);
        }

        function productResult(data) {
            if (data.loading) {
                return $(`
                    <div class="pos-search-loading">
                        <i class="mdi mdi-loading mdi-spin"></i>
                        <span>Mencari produk di master obat...</span>
                    </div>
                `);
            }

            const item = data.item || data;
            const stock = Number(item.total_stok) || 0;
            const hasStock = stock > 0;
            const batchText = item.next_batch?.expired_date ? `ED ${item.next_batch.expired_date}` : 'Batch belum tersedia';

            return $(`
                <div class="pos-search-result">
                    <span class="pos-search-result-icon ${hasStock ? '' : 'is-empty'}">
                        <i class="mdi ${hasStock ? 'mdi-pill' : 'mdi-package-variant-remove'}"></i>
                    </span>
                    <span class="pos-search-result-body">
                        <strong class="pos-search-result-name">${escapeHtml(item.nama_obat || data.text)}</strong>
                        <span class="pos-search-result-code">
                            <b>${escapeHtml(item.kode_obat || '-')}</b>
                            <span>${escapeHtml(item.golongan || 'Tanpa golongan')}</span>
                        </span>
                        <small class="pos-search-result-meta">${escapeHtml(item.kategori || '-')} · ${escapeHtml(batchText)}</small>
                    </span>
                    <span class="pos-search-result-side">
                        <strong class="pos-search-result-price">${formatCurrency(item.harga_jual)}</strong>
                        <small class="pos-search-result-stock ${hasStock ? '' : 'is-empty'}">
                            ${hasStock ? `Stok ${formatNumber(stock)} ${escapeHtml(item.satuan_stok || '')}` : 'Stok kosong'}
                        </small>
                    </span>
                </div>
            `);
        }

        function initializeProductSearch(dropdownParent = $(document.body)) {
            const productSearch = $('#productSearch');

            if (productSearch.hasClass('select2-hidden-accessible')) {
                productSearch.select2('destroy');
            }

            productSearch.select2({
                placeholder: 'Ketik atau scan produk...',
                minimumInputLength: 0,
                width: '100%',
                allowClear: true,
                dropdownParent,
                ajax: {
                    url: urls.products,
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: params => ({
                        q: params.term || '',
                        branch_id: activeBranchId || ''
                    }),
                    processResults: data => {
                        const products = data || [];
                        setProductSearchFeedback(
                            products.length > 0
                                ? `${products.length} produk ditemukan. Pilih produk untuk melihat detail stok.`
                                : 'Produk tidak ditemukan. Coba nama, kode, kandungan, atau barcode lain.',
                            products.length > 0 ? 'success' : 'warning',
                            products.length > 0 ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline'
                        );

                        return {
                            results: products.map(item => ({
                                id: item.id,
                                text: item.text,
                                item
                            }))
                        };
                    }
                },
                templateResult: productResult,
                templateSelection: data => data.item ? data.item.nama_obat : (data.text || 'Ketik atau scan produk...'),
                language: {
                    inputTooShort: () => 'Ketik kode, nama, indikasi, kandungan, atau golongan obat.',
                    searching: () => {
                        setProductSearchFeedback('Mencari produk di master obat...', 'default', 'mdi-loading mdi-spin');
                        return 'Mencari produk...';
                    },
                    noResults: () => {
                        setProductSearchFeedback('Produk tidak ditemukan. Periksa kata kunci atau barcode.', 'warning', 'mdi-alert-circle-outline');
                        return 'Produk tidak ditemukan di master data.';
                    }
                }
            });
        }

        initializeProductSearch();

        function patientResult(data) {
            if (data.loading) return data.text;
            const patient = data.patient || data;
            return $(`<div class="pos-patient-result"><span><i class="mdi mdi-account-outline"></i></span><div><strong>${escapeHtml(patient.name || data.text)}</strong><small>${escapeHtml(patient.phone || '')}</small></div></div>`);
        }

        $('#patientSelect').select2({
            placeholder: 'Ketik nama atau nomor telepon...',
            minimumInputLength: 0,
            width: '100%',
            allowClear: true,
            ajax: {
                url: urls.patients,
                dataType: 'json',
                delay: 250,
                cache: true,
                data: params => ({ q: params.term || '', branch_id: activeBranchId || '' }),
                processResults: data => ({
                    results: (data.results || []).map(patient => ({ id: patient.id, text: patient.text, patient }))
                })
            },
            templateResult: patientResult,
            templateSelection: data => data.patient ? `${data.patient.name} · ${data.patient.phone}` : (data.text || 'Ketik nama atau nomor telepon...'),
            language: {
                searching: () => 'Mencari pasien...',
                noResults: () => 'Pasien belum tersimpan. Isi nama dan nomor HP secara manual.'
            }
        });

        function clearPatientSelection() {
            $('#patientId').val('');
            $('#patientSelect').val(null).trigger('change.select2');
        }

        function applyPatient(patient) {
            if (!patient?.id) return;
            applyingPatientSelection = true;
            $('#patientId').val(patient.id);
            $('#customerName').val(patient.name || '');
            $('#customerPhone').val(patient.phone || '');
            updateTransactionDetailSummary();
            applyingPatientSelection = false;
        }

        function showSelectedPatient(patient) {
            if (!patient?.id) {
                clearPatientSelection();
                return;
            }
            const option = new Option(`${patient.name} · ${patient.phone}`, patient.id, true, true);
            $('#patientSelect').empty().append(option).trigger('change.select2');
            $('#patientId').val(patient.id);
        }

        $('#patientSelect').on('select2:select', event => applyPatient(event.params.data.patient));
        $('#patientSelect').on('select2:clear', clearPatientSelection);

        $('#productSearch')
            .on('select2:opening', function() {
                $('#productSearchBox').addClass('is-open');
                setProductSearchFeedback('Ketik minimal satu kata atau scan barcode produk.', 'default', 'mdi-magnify');
            })
            .on('select2:open', function() {
                const openDropdown = $('.select2-container--open .select2-dropdown');
                openDropdown.addClass('pos-product-dropdown');
                const searchField = openDropdown.find('.select2-search__field').attr({
                    placeholder: 'Cari nama, kode, kandungan, atau barcode...',
                    'aria-label': 'Kata kunci pencarian produk'
                });
                window.setTimeout(() => searchField.trigger('focus'), 0);
            })
            .on('select2:close', function() {
                $('#productSearchBox').removeClass('is-open');
            });

        $('#productSearch').on('select2:select', function(e) {
            selectedProduct = e.params.data.item;
            currentQuote = null;
            $('#productSearchBox').addClass('has-selection');
            setProductSearchFeedback(
                `${selectedProduct.nama_obat} dipilih. Atur satuan dan jumlah di bawah.`,
                'success',
                'mdi-check-decagram-outline'
            );
            renderSelectedProduct();
            populateUnits();
            requestQuote();
            updateFlow();
            $('#qtyInput').trigger('focus').select();
        });

        $('#productSearch').on('select2:clear', function() {
            selectedProduct = null;
            currentQuote = null;
            $('#productSearchBox').removeClass('has-selection');
            $('#unitSelect').empty().append('<option value="">Pilih produk dulu</option>').prop('disabled', true);
            $('#qtyInput').val(1);
            renderSelectedProduct();
            resetQuote();
            updateFlow();
        });

        function renderSelectedProduct() {
            if (!selectedProduct) {
                $('#productSearchBox').removeClass('has-selection');
                setProductSearchFeedback(
                    'Mulai ketik atau scan barcode untuk menampilkan produk.',
                    'default',
                    'mdi-information-outline'
                );
                $('#selectedProductCard').removeClass('has-product pos-pulse').html(`
                    <div class="pos-empty-state">
                        <span class="pos-empty-icon"><i class="mdi mdi-pill"></i></span>
                        <strong>Belum ada obat dipilih</strong>
                        <span>Cari atau scan obat untuk melihat harga dan stok tersedia.</span>
                    </div>
                `);
                return;
            }

            const batch = selectedProduct.next_batch;
            $('#selectedProductCard').removeClass('pos-pulse').addClass('has-product').html(`
                <div class="pos-product-name">
                    <div>
                        <strong>${escapeHtml(selectedProduct.nama_obat)}</strong>
                        <small class="text-muted"><i class="mdi mdi-barcode-scan"></i> ${escapeHtml(selectedProduct.kode_obat)}</small>
                    </div>
                    <span class="badge bg-primary">${formatNumber(selectedProduct.total_stok)} ${escapeHtml(selectedProduct.satuan_stok)}</span>
                </div>
                <div class="pos-product-summary">
                    <span>${escapeHtml(selectedProduct.kategori || 'Tanpa kategori')}</span>
                    <span>${escapeHtml(selectedProduct.sediaan || 'Sediaan belum diisi')}</span>
                </div>
                <details class="pos-product-more">
                    <summary><i class="mdi mdi-information-outline"></i> Lihat detail obat <i class="mdi mdi-chevron-down"></i></summary>
                    <div class="pos-product-meta">
                        <span><small>Golongan</small>${escapeHtml(selectedProduct.golongan)}</span>
                        <span><small>Main/Sub</small>${escapeHtml(selectedProduct.main_golongan)} / ${escapeHtml(selectedProduct.sub_golongan)}</span>
                        <span><small>Pabrikan</small>${escapeHtml(selectedProduct.pabrikan)}</span>
                        <span><small>Batch FEFO</small>${batch ? escapeHtml(batch.no_batch) + ' | ED ' + escapeHtml(batch.expired_date || '-') : '-'}</span>
                    </div>
                    <div class="pos-product-clinical">
                        <strong>Indikasi:</strong> ${escapeHtml(selectedProduct.indikasi || '-')}<br>
                        <strong>Kandungan:</strong> ${escapeHtml(selectedProduct.komposisi || '-')}<br>
                        <strong>Dosis referensi:</strong> ${escapeHtml(selectedProduct.dosis || '-')}
                    </div>
                </details>
            `);

            requestAnimationFrame(function() {
                $('#selectedProductCard').addClass('pos-pulse');
            });
        }

        function populateUnits() {
            const select = $('#unitSelect');
            select.empty();

            if (!selectedProduct || !Array.isArray(selectedProduct.units) || selectedProduct.units.length === 0) {
                select.append('<option value="">Satuan tidak tersedia</option>').prop('disabled', true);
                $('#addToCartBtn').prop('disabled', true);
                return;
            }

            selectedProduct.units.forEach(function(unit) {
                const option = new Option(`${unit.nama} (${formatNumber(unit.konversi)} ${selectedProduct.satuan_stok})`, unit.satuan_id, false, Boolean(unit.is_default));
                $(option).attr('data-konversi', unit.konversi).attr('data-name', unit.nama);
                select.append(option);
            });

            if (!select.val()) {
                select.prop('selectedIndex', 0);
            }

            select.prop('disabled', false);
        }

        $('#unitSelect, #qtyInput').on('change input', function() {
            clearTimeout(quoteTimer);
            quoteTimer = setTimeout(requestQuote, 180);
        });

        function resetQuote() {
            quoteRequestVersion += 1;
            currentQuote = null;
            $('#quoteBox').removeClass('is-loading');
            $('#quotePrice').text(formatCurrency(0));
            $('#quoteStock').text('0');
            $('#quoteBatch').text('-');
            $('#quoteFefoList').html('<span class="pos-fefo-placeholder">Alokasi batch akan tampil setelah produk dan jumlah dipilih.</span>');
            $('#addToCartBtn').prop('disabled', true);
            updateCatalogFlow();
        }

        function resetProductWorkspace() {
            clearTimeout(quoteTimer);
            quoteTimer = null;
            selectedProduct = null;
            currentQuote = null;
            $('#productSearch').val(null).trigger('change');
            $('#productSearchBox').removeClass('has-selection');
            $('#unitSelect').empty().append('<option value="">Pilih produk dulu</option>').prop('disabled', true);
            $('#qtyInput').val(1);
            renderSelectedProduct();
            resetQuote();
            updateFlow();
        }

        function requestQuote() {
            if (!selectedProduct || !$('#unitSelect').val()) {
                resetQuote();
                return;
            }

            const requestVersion = ++quoteRequestVersion;
            $('#quoteBox').addClass('is-loading');

            $.get(urls.quote, {
                obat_id: selectedProduct.id,
                satuan_id: $('#unitSelect').val(),
                qty: $('#qtyInput').val() || 1,
                branch_id: activeBranchId || ''
            }).done(function(response) {
                if (requestVersion !== quoteRequestVersion || !selectedProduct) return;
                currentQuote = response;
                renderQuote(response);
            }).fail(function(xhr) {
                if (requestVersion !== quoteRequestVersion) return;
                resetQuote();
                showAjaxError(xhr, 'Quote obat gagal dimuat.');
            });
        }

        function renderQuote(quote) {
            $('#quoteBox').removeClass('is-loading');
            $('#quotePrice').text(formatCurrency(quote.harga_jual));
            $('#quoteStock').text(`${formatNumber(quote.stock_available)} ${escapeHtml(quote.satuan_stok)}`);
            $('#quoteBatch').text((quote.allocations || [])[0]?.no_batch || '-');
            $('#addToCartBtn').prop('disabled', !quote.is_available || Number(quote.harga_jual) <= 0);
            updateCatalogFlow();

            if (!quote.is_available) {
                $('#quoteFefoList').html(`<span class="pos-fefo-chip text-danger"><i class="mdi mdi-alert-circle-outline"></i> Stok tidak cukup untuk qty ini</span>`);
                return;
            }

            $('#quoteFefoList').html((quote.allocations || []).map(allocation => `
                <span class="pos-fefo-chip">
                    <i class="mdi mdi-package-variant-closed"></i>
                    ${escapeHtml(allocation.no_batch || '-')} | ED ${escapeHtml(allocation.expired_date || '-')} | ${formatNumber(allocation.qty_stok)}
                </span>
            `).join('') || `<span class="pos-fefo-chip text-muted"><i class="mdi mdi-package-variant"></i> Tidak ada batch aktif</span>`);
        }

        function productStrength(product) {
            const searchable = `${product?.nama_obat || ''} ${product?.komposisi || ''}`;
            const concentration = searchable.match(/\b\d+(?:[.,]\d+)?\s*(?:mcg|mg|gram|g|ml|iu|ui|%)\s*\/\s*\d*(?:[.,]\d+)?\s*(?:mcg|mg|gram|g|ml|iu|ui|tablet|tab|kaplet|kapsul)\b/i);
            const match = searchable.match(/\b\d+(?:[.,]\d+)?\s*(?:mcg|mg|gram|g|ml|iu|ui|%)\b/i);

            return concentration?.[0] || match?.[0] || product?.dosis || '';
        }

        $('#addToCartBtn').on('click', function() {
            if (!selectedProduct || !currentQuote || !currentQuote.is_available) {
                Swal.fire('Stok tidak cukup', 'Pilih obat dan qty yang tersedia terlebih dahulu.', 'warning');
                return;
            }

            const compound = isCompoundPrescription();
            if (compound && isCompoundGroupSaved(activeCompoundGroup)) {
                Swal.fire(
                    'Racikan sudah disimpan',
                    `Buat racikan berikutnya atau pilih Ubah pada ${compoundGroupLabel(activeCompoundGroup)} sebelum menambah obat.`,
                    'info'
                );
                return;
            }
            const compoundTemplate = compoundGroupItems(activeCompoundGroup)[0] || null;
            const selectedStrength = compound ? productStrength(selectedProduct) : '';
            const cartItem = {
                uid: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                obat_id: selectedProduct.id,
                kode_obat: selectedProduct.kode_obat,
                nama_obat: selectedProduct.nama_obat,
                satuan_id: currentQuote.satuan_id,
                satuan: currentQuote.satuan,
                satuan_stok: currentQuote.satuan_stok,
                konversi: Number(currentQuote.konversi) || 1,
                units: (selectedProduct.units || []).map(unit => ({
                    satuan_id: Number(unit.satuan_id),
                    nama: unit.nama,
                    konversi: Number(unit.konversi) || 1,
                    is_default: Boolean(unit.is_default)
                })),
                qty: Number(currentQuote.qty_jual) || 1,
                qty_stok: Number(currentQuote.qty_stok) || 0,
                harga_jual: Number(currentQuote.harga_jual) || 0,
                subtotal_gross: Number(currentQuote.subtotal_gross) || 0,
                diskon_percent: 0,
                diskon_nominal: 0,
                allocations: currentQuote.allocations || [],
                is_available: Boolean(currentQuote.is_available),
                aturan_pakai: compound ? (compoundTemplate?.aturan_pakai || '') : (selectedProduct.dosis || ''),
                waktu_konsumsi: compound ? (compoundTemplate?.waktu_konsumsi || '') : '',
                durasi_hari: compound ? (compoundTemplate?.durasi_hari || '') : '',
                _durasi_hari_manual: compound ? Boolean(compoundTemplate?._durasi_hari_manual) : false,
                racikan_group: compound ? activeCompoundGroup : '',
                bentuk_racikan: compound ? (compoundTemplate?.bentuk_racikan || '') : '',
                jumlah_racikan: compound ? (compoundTemplate?.jumlah_racikan || '') : '',
                jumlah_ambil_resep: compound ? (compoundTemplate?.jumlah_ambil_resep || '') : '',
                signa_1: compound ? (compoundTemplate?.signa_1 || '') : '',
                signa_2: compound ? (compoundTemplate?.signa_2 || '') : '',
                embalase_racikan: compound ? (compoundTemplate?.embalase_racikan || 0) : 0,
                dosis_komponen: selectedStrength,
                kekuatan_obat: selectedStrength,
                jumlah_resep: compound ? (Number(currentQuote.qty_jual) || 1) : '',
                keterangan: compound ? (compoundTemplate?.keterangan || '') : ''
            };
            const matchingItem = isPrescriptionTransaction() ? null : cart.find(item => item.obat_id === cartItem.obat_id && item.satuan_id === cartItem.satuan_id && Number(item.diskon_percent) === 0 && Number(item.diskon_nominal) === 0);

            if (matchingItem) {
                matchingItem.qty += cartItem.qty;
                refreshCartQuote(matchingItem);
            } else {
                cart.push(cartItem);
                const recalculatedItems = compound
                    ? recalculateCompoundGroup(activeCompoundGroup, {
                        updateGroupTake: false,
                        updatePrescriptionQuantity: true,
                        targetItem: cartItem
                    })
                    : [];
                renderCart();
                recalculatedItems.forEach(refreshCartQuote);
            }

            $('#qtyInput').val(1);
            requestQuote();
        });

        $('#qtyInput').on('keydown', function(event) {
            if (event.key === 'Enter' && !$('#addToCartBtn').prop('disabled')) {
                event.preventDefault();
                $('#addToCartBtn').trigger('click');
            }
        });

        function itemDiscount(item) {
            const gross = Number(item.subtotal_gross) || 0;
            const percent = Math.min(100, Math.max(0, Number(item.diskon_percent) || 0));
            const nominal = Math.max(0, Number(item.diskon_nominal) || 0);

            return Math.min(gross, (gross * percent / 100) + nominal);
        }

        function itemNet(item) {
            return Math.max(0, (Number(item.subtotal_gross) || 0) - itemDiscount(item));
        }

        function itemUnitOptions(item) {
            const units = Array.isArray(item.units) ? [...item.units] : [];
            const currentUnitExists = units.some(unit => Number(unit.satuan_id) === Number(item.satuan_id));

            if (!currentUnitExists && item.satuan_id) {
                units.unshift({
                    satuan_id: Number(item.satuan_id),
                    nama: item.satuan || 'Satuan tersimpan',
                    konversi: Number(item.konversi) || 1,
                    is_default: false
                });
            }

            return units.map(unit => {
                const selected = Number(unit.satuan_id) === Number(item.satuan_id) ? 'selected' : '';
                const conversion = Number(unit.konversi) || 1;
                const label = `${unit.nama} · 1 = ${formatNumber(conversion)} ${item.satuan_stok || 'satuan stok'}`;

                return `<option value="${escapeHtml(unit.satuan_id)}" ${selected}>${escapeHtml(label)}</option>`;
            }).join('');
        }

        function consumptionTimeOptions(selectedValue = '') {
            return [
                ['', 'Pilih waktu konsumsi'],
                ['sebelum_makan', 'Sebelum makan'],
                ['sesudah_makan', 'Sesudah makan'],
                ['bersama_makan', 'Bersama makan'],
                ['tidak_terkait_makan', 'Tidak terkait makan'],
                ['sesuai_instruksi', 'Sesuai instruksi dokter']
            ].map(([value, label]) => `<option value="${value}" ${selectedValue === value ? 'selected' : ''}>${label}</option>`).join('');
        }

        function compoundFormOptions(selectedValue = '') {
            return [
                ['', 'Pilih bentuk racikan'],
                ['Kapsul', 'Kapsul'],
                ['Pulveres', 'Pulveres / puyer'],
                ['Salep', 'Salep'],
                ['Krim', 'Krim'],
                ['Sirup', 'Sirup'],
                ['Larutan', 'Larutan'],
                ['Lainnya', 'Lainnya']
            ].map(([value, label]) => `<option value="${value}" ${selectedValue === value ? 'selected' : ''}>${label}</option>`).join('');
        }

        function prescriptionItemEditor(item) {
            if (!isPrescriptionTransaction()) {
                return '';
            }

            const compound = isCompoundPrescription();
            const isComplete = isPrescriptionItemComplete(item);

            if (compound) {
                const group = normalizeCompoundGroup(item.racikan_group);
                const componentPosition = compoundGroupItems(group).findIndex(component => component.uid === item.uid) + 1;
                const locked = isCompoundGroupSaved(group);
                const disabled = locked ? 'disabled' : '';
                const reference = compoundGroupReference(group) || {};
                const componentCalculation = compoundItemCalculation(item, reference);
                const calculationMessage = compoundCalculationMessage(item, reference);
                const doseIsAutomatic = isAutomaticCompoundDose(item);
                const roundedForStock = componentCalculation.calculatedTake
                    && componentCalculation.dispensedTake
                    && Math.abs(componentCalculation.calculatedTake - componentCalculation.dispensedTake) > 0.001;
                return `
                    <tr class="pos-prescription-detail-row">
                        <td colspan="7">
                            <div class="pos-prescription-item-editor pos-compound-component-editor ${isComplete ? 'is-complete' : ''} ${locked ? 'is-locked' : ''}">
                                <div class="pos-prescription-item-head">
                                    <span><i class="mdi mdi-pill"></i></span>
                                    <span>
                                        <em class="pos-compound-section-label">B. DOSIS KOMPONEN ${componentPosition}</em>
                                        <strong>${escapeHtml(item.nama_obat)}</strong>
                                        <small>Lengkapi dosis bahan ini. Stok keluar dihitung dari etiket bersama di atas.</small>
                                    </span>
                                    <span class="pos-prescription-item-state"><i class="mdi ${locked ? 'mdi-lock-check' : (isComplete ? 'mdi-check-circle' : 'mdi-alert-circle-outline')}"></i>${locked ? 'Tersimpan' : (isComplete ? 'Komponen lengkap' : 'Lengkapi komponen')}</span>
                                </div>
                                <div class="pos-prescription-item-grid is-compound-component">
                                    <label>
                                        <span>Kekuatan obat <em class="pos-auto-field-badge">MASTER</em></span>
                                        <input type="text" class="form-control form-control-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="kekuatan_obat" value="${escapeHtml(item.kekuatan_obat || '')}" placeholder="Contoh: 500 mg" ${disabled}>
                                    </label>
                                    <label>
                                        <span>Dosis resep <b>*</b> <em class="pos-auto-field-badge ${doseIsAutomatic ? 'is-active' : ''}">${doseIsAutomatic ? 'AUTO' : 'MANUAL'}</em></span>
                                        <input type="text" class="form-control form-control-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="dosis_komponen" value="${escapeHtml(item.dosis_komponen || '')}" placeholder="Otomatis dari kekuatan obat" ${disabled}>
                                    </label>
                                    <label>
                                        <span>Jumlah resep <b>*</b> <em class="pos-auto-field-badge">AUTO</em></span>
                                        <input type="number" class="form-control form-control-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="jumlah_resep" value="${escapeHtml(item.jumlah_resep || '')}" min="0.01" step="0.01" placeholder="0" ${disabled}>
                                    </label>
                                    <label class="pos-compound-take-field">
                                        <span>Stok keluar <em class="pos-auto-field-badge">AUTO</em></span>
                                        <strong>${formatNumber(item.qty)} ${escapeHtml(item.satuan)}</strong>
                                        <small>${roundedForStock ? `Kebutuhan tepat ${formatNumber(componentCalculation.calculatedTake)} ${escapeHtml(item.satuan)}; dibulatkan ke atas.` : 'Sesuai kebutuhan tepat racikan.'}</small>
                                    </label>
                                </div>
                                <div class="pos-compound-calculation-note is-${escapeHtml(calculationMessage.status)}">
                                    <i class="mdi ${calculationMessage.status === 'calculated' ? 'mdi-calculator-variant-outline' : (calculationMessage.status === 'manual' ? 'mdi-pencil-outline' : 'mdi-information-outline')}"></i>
                                    ${escapeHtml(calculationMessage.text)}
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            }

            return `
                <tr class="pos-prescription-detail-row">
                    <td colspan="7">
                        <div class="pos-prescription-item-editor ${isComplete ? 'is-complete' : ''}">
                            <div class="pos-prescription-item-head">
                                <span><i class="mdi mdi-clipboard-text-outline"></i></span>
                                <span>
                                    <strong>Etiket obat &middot; ${escapeHtml(item.nama_obat)}</strong>
                                    <small>Aturan pakai ini hanya berlaku untuk obat non-racikan ini.</small>
                                </span>
                                <span class="pos-prescription-item-state"><i class="mdi ${isComplete ? 'mdi-check-circle' : 'mdi-alert-circle-outline'}"></i>${isComplete ? 'Lengkap' : 'Perlu dilengkapi'}</span>
                            </div>
                            <div class="pos-prescription-item-grid">
                                <label class="pos-signa-field">
                                    <span>Aturan pakai / signa <b>*</b></span>
                                    <input type="text" class="form-control form-control-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="aturan_pakai" value="${escapeHtml(item.aturan_pakai || '')}" placeholder="Contoh: 3 x sehari 1 tablet">
                                </label>
                                <label>
                                    <span>Waktu konsumsi</span>
                                    <select class="form-select form-select-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="waktu_konsumsi">${consumptionTimeOptions(item.waktu_konsumsi)}</select>
                                </label>
                                <label>
                                    <span>Durasi (hari)</span>
                                    <input type="number" class="form-control form-control-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="durasi_hari" value="${escapeHtml(item.durasi_hari || '')}" min="1" max="3650" placeholder="Opsional">
                                </label>
                                <label class="pos-prescription-note-field">
                                    <span>Catatan etiket</span>
                                    <input type="text" class="form-control form-control-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="keterangan" value="${escapeHtml(item.keterangan || '')}" placeholder="Contoh: Habiskan / bila perlu">
                                </label>
                            </div>
                            <div class="pos-signa-quick" aria-label="Template aturan pakai">
                                <span>Isi cepat:</span>
                                ${['1 x sehari 1 dosis', '2 x sehari 1 dosis', '3 x sehari 1 dosis', 'Bila perlu'].map(value => `
                                    <button type="button" class="pos-signa-chip" data-id="${escapeHtml(item.uid)}" data-value="${escapeHtml(value)}">${escapeHtml(value)}</button>
                                `).join('')}
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }

        function compoundGroupEditor(group, items) {
            const reference = items[0] || {};
            const saved = isCompoundGroupSaved(group);
            const check = compoundGroupCheck(group);
            const active = normalizeCompoundGroup(group) === activeCompoundGroup;
            const disabled = saved ? 'disabled' : '';
            const calculatedDays = automaticCompoundDays(reference);
            const daysIsAutomatic = calculatedDays && !reference._durasi_hari_manual && Number(reference.durasi_hari) === calculatedDays;
            const calculatedTake = automaticCompoundTake(reference);
            const takeIsAutomatic = calculatedTake && Math.abs((Number(reference.jumlah_ambil_resep) || 0) - calculatedTake) < 0.001;
            const takeFormula = calculatedTake
                ? `JHO ${formatNumber(reference.durasi_hari)} hari &middot; ${formatNumber(parseClinicalNumber(reference.signa_1))} &times; ${formatNumber(parseClinicalNumber(reference.signa_2))} &times; ${formatNumber(reference.durasi_hari)} = <strong>${formatNumber(calculatedTake)} racikan diambil</strong>`
                : 'Isi jumlah racikan, Signa 1, dan Signa 2 agar JHO serta jumlah ambil dihitung otomatis.';

            return `
                <tr class="pos-compound-group-row">
                    <td colspan="7">
                        <section class="pos-compound-group-card ${saved ? 'is-complete is-saved' : ''} ${active ? 'is-active' : ''}" data-group-card="${escapeHtml(group)}">
                            <div class="pos-compound-group-head">
                                <span class="pos-compound-group-mark"><small>${escapeHtml(group)}</small><b>${escapeHtml(compoundGroupLabel(group))}</b></span>
                                <span class="pos-compound-group-copy">
                                    <em class="pos-compound-section-label">A. ETIKET BERSAMA</em>
                                    <strong>${escapeHtml(reference.bentuk_racikan || 'Bentuk racikan belum dipilih')}</strong>
                                    <small>${items.length} komponen &middot; ${Number(reference.jumlah_racikan) > 0 ? `${formatNumber(reference.jumlah_racikan)} dibuat` : 'jumlah belum diisi'}</small>
                                </span>
                                <span class="pos-compound-save-state ${saved ? 'is-saved' : ''}"><i class="mdi ${saved ? 'mdi-check-decagram' : 'mdi-pencil-clock-outline'}"></i>${saved ? 'Tersimpan' : 'Sedang disusun'}</span>
                                <button type="button" class="pos-use-compound-group ${active ? 'is-active' : ''}" data-compound-group="${escapeHtml(group)}">
                                    <i class="mdi ${active ? 'mdi-crosshairs-gps' : 'mdi-eye-outline'}"></i> ${active ? 'Racikan aktif' : 'Lihat racikan'}
                                </button>
                            </div>
                            <div class="pos-compound-group-fields">
                                <label>
                                    <span>Bentuk racikan <b>*</b></span>
                                    <select class="form-select form-select-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="bentuk_racikan" ${disabled}>${compoundFormOptions(reference.bentuk_racikan)}</select>
                                </label>
                                <label>
                                    <span>Jumlah racikan <b>*</b></span>
                                    <input type="number" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="jumlah_racikan" value="${escapeHtml(reference.jumlah_racikan || '')}" min="0.01" step="0.01" placeholder="0" ${disabled}>
                                </label>
                                <label>
                                    <span>Jumlah ambil resep <b>*</b> <em class="pos-auto-field-badge ${takeIsAutomatic ? 'is-active' : ''}">${takeIsAutomatic ? 'AUTO' : 'MANUAL'}</em></span>
                                    <input type="number" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="jumlah_ambil_resep" value="${escapeHtml(reference.jumlah_ambil_resep || '')}" min="0.01" step="0.01" placeholder="0" ${disabled}>
                                </label>
                                <label>
                                    <span>Signa 1 <b>*</b></span>
                                    <input type="text" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="signa_1" value="${escapeHtml(reference.signa_1 || '')}" maxlength="50" placeholder="Contoh: 3" ${disabled}>
                                </label>
                                <label>
                                    <span>Signa 2 <b>*</b></span>
                                    <input type="text" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="signa_2" value="${escapeHtml(reference.signa_2 || '')}" maxlength="50" placeholder="Contoh: 1" ${disabled}>
                                </label>
                                <label>
                                    <span>JHO / jumlah hari <b>*</b> <em class="pos-auto-field-badge ${daysIsAutomatic ? 'is-active' : ''}">${daysIsAutomatic ? 'AUTO' : 'MANUAL'}</em></span>
                                    <input type="number" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="durasi_hari" value="${escapeHtml(reference.durasi_hari || '')}" min="1" max="3650" placeholder="Otomatis" ${disabled}>
                                </label>
                                <label>
                                    <span>Embalase</span>
                                    <span class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control compound-group-input" data-group="${escapeHtml(group)}" data-field="embalase_racikan" value="${escapeHtml(reference.embalase_racikan || 0)}" min="0" step="1" placeholder="0" ${disabled}>
                                    </span>
                                </label>
                            </div>
                            <div class="pos-compound-auto-strip ${calculatedTake ? 'is-calculated' : ''}">
                                <span><i class="mdi ${calculatedTake ? 'mdi-calculator-variant-outline' : 'mdi-lightbulb-on-outline'}"></i></span>
                                <div>
                                    <b>${calculatedTake ? `${daysIsAutomatic ? 'JHO dan jumlah ambil dihitung otomatis' : 'Jumlah ambil dihitung dari JHO manual'}` : 'Hitung otomatis siap digunakan'}</b>
                                    <small>${takeFormula}</small>
                                </div>
                            </div>
                            <div class="pos-compound-group-footer">
                                <span><i class="mdi mdi-information-outline"></i> ${saved ? 'Klik Ubah bila komposisi atau aturan racikan perlu diperbaiki.' : (check.valid ? 'Semua field siap. Simpan untuk mengunci racikan ini.' : 'Lengkapi data racikan dan seluruh dosis komponen.')}</span>
                                <button type="button" class="btn pos-save-compound-group ${saved ? 'is-edit' : ''}" data-compound-group="${escapeHtml(group)}">
                                    <i class="mdi ${saved ? 'mdi-pencil-outline' : 'mdi-content-save-check-outline'}"></i>
                                    ${saved ? `Ubah ${escapeHtml(compoundGroupLabel(group))}` : `Simpan ${escapeHtml(compoundGroupLabel(group))}`}
                                </button>
                            </div>
                        </section>
                    </td>
                </tr>
            `;
        }

        function compoundWorkspaceEmptyRow(group) {
            return `
                <tr class="pos-cart-empty-row">
                    <td colspan="7">
                        <div class="pos-cart-empty pos-compound-workspace-empty">
                            <span class="pos-cart-empty-visual">
                                <i class="mdi mdi-flask-plus-outline"></i>
                            </span>
                            <span class="pos-compound-empty-copy">
                                <strong>Belum ada obat</strong>
                                <small>Cari obat lalu tambahkan ke ${escapeHtml(compoundGroupLabel(group))}.</small>
                            </span>
                            <button type="button" class="btn pos-empty-search-button" id="focusProductSearchBtn">
                                <i class="mdi mdi-plus"></i> Tambah obat
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        function renderCart() {
            const compound = isCompoundPrescription();
            if (cart.length === 0) {
                $('#cartBody').html(compound ? compoundWorkspaceEmptyRow(activeCompoundGroup) : `
                    <tr class="pos-cart-empty-row">
                        <td colspan="7">
                            <div class="pos-cart-empty">
                                <span class="pos-cart-empty-visual">
                                    <i class="mdi mdi-cart-outline"></i>
                                    <b><i class="mdi mdi-plus"></i></b>
                                </span>
                                <strong>Keranjang siap diisi</strong>
                                <small>Cari produk di panel kiri, tentukan satuan dan jumlah, lalu tambahkan ke transaksi.</small>
                                <button type="button" class="btn pos-empty-search-button" id="focusProductSearchBtn">
                                    <i class="mdi mdi-magnify"></i> Cari produk pertama <kbd>F2</kbd>
                                </button>
                                <span class="pos-cart-empty-meta">
                                    <span><i class="mdi mdi-shield-check-outline"></i> Cek stok otomatis</span>
                                    <span><i class="mdi mdi-calendar-sync-outline"></i> Alokasi FEFO</span>
                                    <span><i class="mdi mdi-sale-outline"></i> Diskon fleksibel</span>
                                </span>
                            </div>
                        </td>
                    </tr>
                `);
                recalculateTotals();
                updatePrescriptionReadiness();
                renderCompoundSetup();
                return;
            }

            if (compound) {
                compoundGroups().forEach(synchronizeCompoundGroup);
            }
            const displayCart = compound
                ? cart.filter(item => normalizeCompoundGroup(item.racikan_group) === normalizeCompoundGroup(activeCompoundGroup))
                : cart;
            let renderedGroup = null;

            $('#cartBody').html(compound && displayCart.length === 0
                ? compoundWorkspaceEmptyRow(activeCompoundGroup)
                : displayCart.map(item => {
                const group = compound ? normalizeCompoundGroup(item.racikan_group) : '';
                const groupItems = compound ? compoundGroupItems(group) : [];
                const groupHeader = compound && group !== renderedGroup ? compoundGroupEditor(group, groupItems) : '';
                const componentPosition = compound ? groupItems.findIndex(component => component.uid === item.uid) + 1 : 0;
                const groupLocked = compound && isCompoundGroupSaved(group);
                const lockAttribute = groupLocked || item._quote_loading ? 'disabled' : '';
                renderedGroup = group;

                return `
                ${groupHeader}
                <tr class="pos-product-row ${item.is_available ? 'is-available' : 'is-unavailable'} ${compound ? 'pos-compound-product-row' : ''} ${groupLocked ? 'is-locked' : ''}">
                    <td data-label="Produk">
                        <div class="pos-item-cell">
                            <span class="pos-item-symbol"><i class="mdi mdi-pill"></i></span>
                            <span class="pos-item-copy">
                                <strong title="${escapeHtml(item.nama_obat)}">${escapeHtml(item.nama_obat)}</strong>
                                <small>${escapeHtml(item.kode_obat)}</small>
                                <label class="pos-cart-unit-editor" title="Ubah satuan jual dan konversi ${escapeHtml(item.nama_obat)}">
                                    <i class="mdi mdi-swap-horizontal-bold" aria-hidden="true"></i>
                                    <span class="visually-hidden">Satuan jual ${escapeHtml(item.nama_obat)}</span>
                                    <select class="form-select form-select-sm cart-unit-select" data-id="${escapeHtml(item.uid)}"
                                        aria-label="Satuan jual ${escapeHtml(item.nama_obat)}"
                                        ${lockAttribute}>${itemUnitOptions(item)}</select>
                                </label>
                                <span class="pos-item-badges">
                                    <span>1 ${escapeHtml(item.satuan)} = ${formatNumber(item.konversi)} ${escapeHtml(item.satuan_stok)}</span>
                                    ${compound ? `<span class="pos-item-group-badge">${escapeHtml(group)} &middot; Komponen ${componentPosition}/${groupItems.length}</span>` : ''}
                                </span>
                                <span class="pos-item-quick-meta">
                                    <span>${formatCurrency(item.harga_jual)} / ${escapeHtml(item.satuan)}</span>
                                    <span class="${item.is_available ? '' : 'is-warning'}"><i class="mdi ${item.is_available ? 'mdi-autorenew' : 'mdi-alert-circle-outline'}"></i> ${item.is_available ? 'FEFO otomatis' : 'Stok tidak cukup'}</span>
                                </span>
                            </span>
                        </div>
                    </td>
                    <td data-label="${compound ? 'Stok keluar' : 'Jumlah'}">
                        <div class="pos-qty-stepper">
                            <button type="button" class="cart-qty-step" data-id="${escapeHtml(item.uid)}" data-delta="-1" title="Kurangi jumlah" aria-label="Kurangi ${escapeHtml(item.nama_obat)}" ${lockAttribute}><i class="mdi mdi-minus"></i></button>
                            <input type="number" class="form-control form-control-sm cart-qty" data-id="${escapeHtml(item.uid)}" min="0.01" step="0.01" value="${item.qty}" aria-label="Jumlah ${escapeHtml(item.nama_obat)}" ${lockAttribute}>
                            <button type="button" class="cart-qty-step" data-id="${escapeHtml(item.uid)}" data-delta="1" title="Tambah jumlah" aria-label="Tambah ${escapeHtml(item.nama_obat)}" ${lockAttribute}><i class="mdi mdi-plus"></i></button>
                        </div>
                        <small class="pos-stock-caption"><i class="mdi mdi-package-variant"></i> ${formatNumber(item.qty_stok)} ${escapeHtml(item.satuan_stok)} stok</small>
                    </td>
                    <td data-label="Harga">
                        <span class="pos-money">${formatCurrency(item.harga_jual)}</span>
                        <small class="pos-money-note">per ${escapeHtml(item.satuan)}</small>
                    </td>
                    <td data-label="Diskon item">
                        <div class="pos-discount-editor">
                            <label class="pos-discount-control" title="Diskon persen">
                                <input type="number" class="form-control form-control-sm cart-discount-percent" data-id="${escapeHtml(item.uid)}" min="0" max="100" step="0.01" value="${item.diskon_percent}" aria-label="Diskon persen ${escapeHtml(item.nama_obat)}">
                                <span>%</span>
                            </label>
                            <label class="pos-discount-control is-nominal" title="Diskon nominal">
                                <input type="number" class="form-control form-control-sm cart-discount-nominal" data-id="${escapeHtml(item.uid)}" min="0" step="0.01" value="${item.diskon_nominal}" aria-label="Diskon nominal ${escapeHtml(item.nama_obat)}">
                                <span>Rp</span>
                            </label>
                        </div>
                        <small class="pos-discount-result"><i class="mdi mdi-arrow-down-thin"></i> Hemat ${formatCurrency(itemDiscount(item))}</small>
                    </td>
                    <td data-label="Alokasi FEFO">${fefoCell(item)}</td>
                    <td data-label="Nilai akhir">
                        <span class="pos-money pos-line-total">${formatCurrency(itemNet(item))}</span>
                        <small class="pos-money-note">setelah diskon</small>
                    </td>
                    <td class="text-center" data-label="Aksi">
                        <button type="button" class="btn btn-sm pos-remove-item remove-cart-item" data-id="${escapeHtml(item.uid)}" title="Hapus ${escapeHtml(item.nama_obat)}" aria-label="Hapus ${escapeHtml(item.nama_obat)}" ${lockAttribute}>
                            <i class="mdi mdi-trash-can-outline"></i>
                        </button>
                    </td>
                </tr>
                ${prescriptionItemEditor(item)}
            `;
                }).join(''));

            const cartWrap = $('.pos-cart-wrap');
            cartWrap.removeClass('is-updated');
            requestAnimationFrame(() => cartWrap.addClass('is-updated'));
            recalculateTotals();
            updatePrescriptionReadiness();
            renderCompoundSetup();
        }

        function fefoCell(item) {
            if (!item.is_available) {
                return '<span class="pos-stock-warning"><i class="mdi mdi-alert-circle-outline"></i> Stok tidak cukup</span>';
            }

            const allocations = (item.allocations || []).map(allocation => `
                <span class="pos-fefo-allocation">
                    <i class="mdi mdi-package-variant-closed-check"></i>
                    <span>
                        <b>${escapeHtml(allocation.no_batch || 'Batch otomatis')}</b>
                        <small>${formatNumber(allocation.qty_stok)} ${escapeHtml(item.satuan_stok)} dialokasikan</small>
                    </span>
                </span>
            `).join('');

            return allocations ? `<span class="pos-fefo-stack">${allocations}</span>` : '<span class="pos-fefo-allocation"><i class="mdi mdi-autorenew"></i><span><b>Alokasi otomatis</b><small>Menunggu validasi batch</small></span></span>';
        }

        $(document).on('change', '.cart-qty', function() {
            const item = findCartItem($(this).data('id'));
            if (!item) return;
            item.qty = Math.max(0.01, Number(this.value) || 0.01);
            refreshCartQuote(item);
        });

        $(document).on('change', '.cart-unit-select', function() {
            const item = findCartItem($(this).data('id'));
            const unit = item?.units?.find(option => Number(option.satuan_id) === Number(this.value));

            if (!item || !unit || Number(unit.satuan_id) === Number(item.satuan_id)) {
                return;
            }

            const previousUnit = {
                satuan_id: item.satuan_id,
                satuan: item.satuan,
                konversi: item.konversi
            };

            item.satuan_id = Number(unit.satuan_id);
            item.satuan = unit.nama;
            item.konversi = Number(unit.konversi) || 1;

            if (isCompoundPrescription()) {
                markCompoundGroupUnsaved(item.racikan_group);
            }

            $(this).prop('disabled', true);
            refreshCartQuote(item, previousUnit);
        });

        $(document).on('click', '.cart-qty-step', function() {
            const item = findCartItem($(this).data('id'));
            if (!item) return;

            const delta = Number($(this).data('delta')) || 0;
            item.qty = Math.max(0.01, Math.round((Number(item.qty) + delta) * 100) / 100);
            $(this).closest('td').find('.cart-qty').val(item.qty);
            refreshCartQuote(item);
        });

        $(document).on('click', '#focusProductSearchBtn', function() {
            $('#productSearch').select2('open');
        });

        function setCashierStage(stage, options = {}) {
            const nextStage = ['product', 'cart', 'payment'].includes(stage) ? stage : 'product';
            const shouldFocus = options.focus !== false;

            if (nextStage === 'payment' && cart.length === 0) {
                const searchBox = $('#productSearchBox');
                setProductSearchFeedback('Tambahkan minimal satu produk sebelum membuka pembayaran.', 'warning', 'mdi-cart-plus');
                searchBox.removeClass('is-attention');
                void searchBox[0]?.offsetWidth;
                searchBox.addClass('is-attention');
                cashierStage = 'product';
            } else {
                cashierStage = nextStage;
            }

            $('#posWorkspace')
                .removeClass('is-stage-product is-stage-cart is-stage-payment')
                .addClass(`is-stage-${cashierStage}`)
                .attr('data-cashier-stage', cashierStage);
            $('.pos-premium').attr('data-cashier-stage', cashierStage);
            updateFlow();

            if (!shouldFocus) return cashierStage;

            window.requestAnimationFrame(() => {
                if (cashierStage === 'product') {
                    $('#productSearch').select2('open');
                } else if (cashierStage === 'payment') {
                    $('.payment-amount').first().trigger('focus').select();
                }
            });

            return cashierStage;
        }

        function focusBuyerProfile(targetSelector = '#customerName') {
            setCashierStage('cart', { focus: false });
            $('.pos-transaction-details').first().prop('open', true);

            window.requestAnimationFrame(() => {
                document.getElementById('posCustomerCard')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
                $(targetSelector).trigger('focus');
            });
        }

        function requestPaymentStage() {
            if (cart.length === 0) {
                setCashierStage('payment');
                return;
            }

            const buyerName = String($('#customerName').val() || '').trim();
            const buyerPhone = String($('#customerPhone').val() || '').trim();
            const missingBuyerFields = [];

            if (!buyerName) missingBuyerFields.push({ selector: '#customerName', label: 'nama pembeli' });
            if (!buyerPhone) missingBuyerFields.push({ selector: '#customerPhone', label: 'nomor HP' });

            if (missingBuyerFields.length === 0) {
                setCashierStage('payment');
                return;
            }

            missingBuyerFields.forEach(field => {
                $(field.selector).addClass('is-invalid').attr('aria-invalid', 'true');
            });
            const missingLabel = missingBuyerFields.map(field => field.label).join(' dan ');

            Swal.fire({
                icon: 'warning',
                title: 'Data pembeli wajib diisi',
                html: `
                    <div class="pos-buyer-reminder-copy">
                        <p>Lengkapi <b>${missingLabel}</b> sebelum melanjutkan ke pembayaran.</p>
                        <small>Data ini diperlukan untuk riwayat transaksi dan pelayanan berikutnya.</small>
                    </div>
                `,
                confirmButtonText: '<i class="mdi mdi-account-edit-outline"></i> Isi data pembeli',
                confirmButtonColor: '#1e314f',
                focusConfirm: true,
                allowOutsideClick: false,
                customClass: {
                    popup: 'pos-buyer-reminder-modal',
                    confirmButton: 'pos-buyer-reminder-confirm'
                }
            }).then(result => {
                if (result.isConfirmed) {
                    focusBuyerProfile(missingBuyerFields[0].selector);
                }
            });
        }

        $('#customerName, #customerPhone').on('input', function() {
            if (String(this.value || '').trim()) {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
            }
            if (!applyingPatientSelection && $('#patientId').val()) clearPatientSelection();
        });

        $(document).on('click', '[data-pos-jump]', function() {
            const stage = String($(this).data('pos-jump') || '');

            if (stage === 'product' && prescriptionPaymentMode) {
                $('#editPrescriptionBtn').trigger('click');
                return;
            }

            if (stage === 'payment') {
                requestPaymentStage();
                return;
            }

            setCashierStage(stage);
        });

        $(document).on('click', '#finishCompoundPrescriptionBtn', function() {
            $('#finishPrescriptionFlowBtn').trigger('click');
        });

        $('#goToPaymentBtn').on('click', requestPaymentStage);
        $('#backToCartBtn').on('click', () => setCashierStage('cart', { focus: false }));
        $('#paymentEditPrescriptionBtn').on('click', () => $('#editPrescriptionBtn').trigger('click'));

        $(document).on('input', '.cart-discount-percent, .cart-discount-nominal', function() {
            const item = findCartItem($(this).data('id'));
            if (!item) return;

            if ($(this).hasClass('cart-discount-percent')) {
                item.diskon_percent = Math.min(100, Math.max(0, Number(this.value) || 0));
            } else {
                item.diskon_nominal = Math.max(0, Number(this.value) || 0);
            }

            renderCart();
        });

        $(document).on('input change', '.cart-prescription-input', function(event) {
            const item = findCartItem($(this).data('id'));
            const field = String($(this).data('field') || '');

            if (!item || !['aturan_pakai', 'waktu_konsumsi', 'durasi_hari', 'racikan_group', 'dosis_komponen', 'kekuatan_obat', 'jumlah_resep', 'keterangan'].includes(field)) {
                return;
            }

            if (field === 'racikan_group') {
                const targetGroup = normalizeCompoundGroup(this.value);
                const targetReference = cart.find(component => component.uid !== item.uid && normalizeCompoundGroup(component.racikan_group) === targetGroup);
                item.racikan_group = targetGroup;

                if (targetReference) {
                    compoundSharedFields.forEach(sharedField => {
                        item[sharedField] = targetReference[sharedField] || '';
                    });
                    item._durasi_hari_manual = Boolean(targetReference._durasi_hari_manual);
                }

                activeCompoundGroup = targetGroup;
                renderCart();
                return;
            }

            const doseWasAutomatic = field === 'kekuatan_obat'
                && (!String(item.dosis_komponen || '').trim() || isAutomaticCompoundDose(item));
            item[field] = ['durasi_hari', 'jumlah_resep'].includes(field)
                ? (this.value === '' ? '' : Math.max(field === 'durasi_hari' ? 1 : 0.01, Number(this.value) || 0))
                : this.value;
            if (doseWasAutomatic) {
                item.dosis_komponen = item.kekuatan_obat;
            }
            if (isCompoundPrescription()) {
                markCompoundGroupUnsaved(item.racikan_group);
            }

            const recalculatedItems = isCompoundPrescription() && event.type === 'change'
                ? recalculateCompoundGroup(item.racikan_group, {
                    updateGroupTake: false,
                    updatePrescriptionQuantity: ['kekuatan_obat', 'dosis_komponen'].includes(field),
                    targetItem: item
                })
                : [];
            updatePrescriptionReadiness();

            if (event.type === 'change' && isCompoundPrescription()) {
                renderCart();
                recalculatedItems.forEach(refreshCartQuote);
                return;
            }

            const editor = $(this).closest('.pos-prescription-item-editor');
            const complete = isPrescriptionItemComplete(item);
            editor.toggleClass('is-complete', complete);
            editor.find('.pos-prescription-item-state').html(`<i class="mdi ${complete ? 'mdi-check-circle' : 'mdi-alert-circle-outline'}"></i>${complete ? (isCompoundPrescription() ? 'Komponen lengkap' : 'Lengkap') : (isCompoundPrescription() ? 'Isi dosis komponen' : 'Perlu dilengkapi')}`);
        });

        $(document).on('input change', '.compound-group-input', function(event) {
            const group = normalizeCompoundGroup($(this).data('group'));
            const field = String($(this).data('field') || '');

            if (!compoundSharedFields.includes(field)) {
                return;
            }

            const numericFields = ['durasi_hari', 'jumlah_racikan', 'jumlah_ambil_resep', 'embalase_racikan'];
            const value = numericFields.includes(field)
                ? (this.value === '' ? '' : Math.max(field === 'embalase_racikan' ? 0 : (field === 'durasi_hari' ? 1 : 0.01), Number(this.value) || 0))
                : this.value;
            const items = compoundGroupItems(group);
            items.forEach(item => {
                item[field] = value;
            });

            if (field === 'durasi_hari') {
                const calculatedDays = automaticCompoundDays(items[0] || {});
                const manuallyAdjusted = value !== '' && (!calculatedDays || Number(value) !== calculatedDays);
                items.forEach(item => {
                    item._durasi_hari_manual = manuallyAdjusted;
                });
            }

            synchronizeCompoundGroup(group);
            markCompoundGroupUnsaved(group);

            const quantityDependencies = ['jumlah_racikan', 'jumlah_ambil_resep', 'signa_1', 'signa_2', 'durasi_hari'];
            const recalculatedItems = event.type === 'change' && quantityDependencies.includes(field)
                ? recalculateCompoundGroup(group, {
                    updateGroupDays: ['jumlah_racikan', 'signa_1', 'signa_2'].includes(field) || (field === 'durasi_hari' && value === ''),
                    updateGroupTake: ['jumlah_racikan', 'signa_1', 'signa_2', 'durasi_hari'].includes(field),
                    updatePrescriptionQuantity: field === 'jumlah_racikan'
                })
                : [];

            updatePrescriptionReadiness();
            recalculateTotals();

            if (event.type === 'change') {
                renderCart();
                recalculatedItems.forEach(refreshCartQuote);
            }
        });

        $(document).on('click', '.pos-signa-chip', function() {
            const group = String($(this).data('group') || '');
            const value = String($(this).data('value') || '');

            if (group) {
                compoundGroupItems(group).forEach(item => {
                    item.aturan_pakai = value;
                });
                renderCart();
                return;
            }

            const item = findCartItem($(this).data('id'));
            if (!item) return;

            item.aturan_pakai = value;
            $(this).closest('.pos-prescription-item-editor').find('[data-field="aturan_pakai"]').val(item.aturan_pakai).trigger('input');
        });

        $('#activeCompoundGroup').on('change', function() {
            activeCompoundGroup = normalizeCompoundGroup(this.value);
            renderCart();
        });

        $('#newCompoundGroupBtn').on('click', function() {
            const currentGroup = normalizeCompoundGroup(activeCompoundGroup);

            if (!isCompoundGroupSaved(currentGroup)) {
                Swal.fire(
                    'Simpan racikan aktif terlebih dahulu',
                    `${compoundGroupLabel(currentGroup)} harus lengkap dan tersimpan sebelum membuat racikan berikutnya.`,
                    'warning'
                );
                return;
            }

            activeCompoundGroup = nextCompoundGroup();
            renderCart();
            $('#productSearch').select2('open');
        });

        $(document).on('click', '.pos-save-compound-group', function() {
            const group = normalizeCompoundGroup($(this).data('compound-group'));

            if (isCompoundGroupSaved(group)) {
                savedCompoundGroups.delete(group);
                activeCompoundGroup = group;
                renderCart();
                return;
            }

            const check = compoundGroupCheck(group);
            if (!check.hasItems) {
                Swal.fire('Racikan belum memiliki obat', 'Tambahkan minimal satu obat ke racikan ini.', 'warning');
                return;
            }

            if (check.missing.length > 0) {
                Swal.fire('Data racikan belum lengkap', `Lengkapi: ${check.missing.join(', ')}.`, 'warning');
                return;
            }

            if (check.invalidComponent) {
                Swal.fire(
                    'Komponen belum lengkap',
                    `Lengkapi Dosis Resep dan Jumlah Resep untuk ${check.invalidComponent.nama_obat}.`,
                    'warning'
                );
                return;
            }

            synchronizeCompoundGroup(group);
            savedCompoundGroups.add(group);
            activeCompoundGroup = nextCompoundGroup();
            resetProductWorkspace();
            renderCart();
            Swal.fire({
                icon: 'success',
                title: `${compoundGroupLabel(group)} tersimpan`,
                text: `${compoundGroupLabel(activeCompoundGroup)} sudah disiapkan, tetapi opsional. Bila resep selesai, Anda dapat langsung membayar.`,
                toast: true,
                position: 'top-end',
                timer: 1800,
                showConfirmButton: false
            });
        });

        $(document).on('click', '.pos-compound-summary-chip, .pos-use-compound-group', function() {
            activeCompoundGroup = normalizeCompoundGroup($(this).data('compound-group'));
            renderCart();
        });

        $(document).on('click', '.remove-cart-item', function() {
            const item = findCartItem($(this).data('id'));
            if (item && isCompoundPrescription()) {
                markCompoundGroupUnsaved(item.racikan_group);
            }
            cart = cart.filter(item => item.uid !== String($(this).data('id')));
            renderCart();
        });

        function findCartItem(uid) {
            return cart.find(item => item.uid === String(uid));
        }

        function refreshCartQuote(item, rollbackUnit = null) {
            const requestVersion = (Number(item._quote_request_version) || 0) + 1;
            item._quote_request_version = requestVersion;
            item._quote_loading = true;

            return $.get(urls.quote, {
                obat_id: item.obat_id,
                satuan_id: item.satuan_id,
                qty: item.qty,
                branch_id: activeBranchId || ''
            }).done(function(response) {
                if (item._quote_request_version !== requestVersion) return;

                item.satuan_id = Number(response.satuan_id);
                item.satuan = response.satuan;
                item.konversi = Number(response.konversi) || 1;
                item.qty = Number(response.qty_jual) || item.qty;
                item.qty_stok = Number(response.qty_stok) || item.qty_stok;
                item.harga_jual = Number(response.harga_jual) || item.harga_jual;
                item.subtotal_gross = Number(response.subtotal_gross) || 0;
                item.allocations = response.allocations || [];
                item.is_available = Boolean(response.is_available);
            }).fail(function(xhr) {
                if (item._quote_request_version !== requestVersion) return;

                if (rollbackUnit) {
                    Object.assign(item, rollbackUnit);
                }
                showAjaxError(xhr, 'Stok item gagal diperiksa ulang.');
            }).always(function() {
                if (item._quote_request_version !== requestVersion) return;

                item._quote_loading = false;
                renderCart();
            });
        }

        $('#clearCartBtn').on('click', function() {
            if (cart.length === 0) return;

            Swal.fire({
                title: 'Kosongkan keranjang?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, kosongkan',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    cart = [];
                    activeCompoundGroup = 'R/ 1';
                    savedCompoundGroups = new Set();
                    renderCart();
                }
            });
        });

        function recalculateTotals() {
            const subtotalGross = cart.reduce((sum, item) => sum + (Number(item.subtotal_gross) || 0), 0);
            const itemDiscountTotal = cart.reduce((sum, item) => sum + itemDiscount(item), 0);
            const afterItemDiscount = Math.max(0, subtotalGross - itemDiscountTotal);
            const txPercent = Math.min(100, Math.max(0, Number($('#transactionDiscountPercent').val()) || 0));
            const txNominal = Math.max(0, Number($('#transactionDiscountNominal').val()) || 0);
            const transactionDiscount = Math.min(afterItemDiscount, (afterItemDiscount * txPercent / 100) + txNominal);
            const taxBase = Math.max(0, afterItemDiscount - transactionDiscount);
            const taxPercent = $('#useTax').is(':checked') ? Math.min(100, Math.max(0, Number($('#taxPercent').val()) || 0)) : 0;
            const taxTotal = taxBase * taxPercent / 100;
            const embalase = isCompoundPrescription() ? compoundEmbalaseTotal() : 0;
            $('#embalase').val(embalase);
            const grandTotal = taxBase + taxTotal + embalase;
            const paidTotal = paymentRows().reduce((sum, payment) => sum + payment.amount, 0);
            const diff = paidTotal - grandTotal;
            const totalQty = cart.reduce((sum, item) => sum + (Number(item.qty) || 0), 0);
            const unavailableCount = cart.filter(item => !item.is_available).length;
            const readinessPercent = cart.length === 0 ? 8 : (unavailableCount > 0 ? 48 : 100);

            $('#cartItemCount').text(`${cart.length} item`);
            $('#cartQtyCount').text(`${formatNumber(totalQty)} qty`);
            $('#cartRunningTotal').text(formatCurrency(afterItemDiscount));
            $('#subtotalGrossText').text(formatCurrency(subtotalGross));
            $('#itemDiscountText').text(formatCurrency(itemDiscountTotal));
            $('#transactionDiscountText').text(formatCurrency(transactionDiscount));
            $('#taxTotalText').text(formatCurrency(taxTotal));
            $('#embalaseTotalRow').toggleClass('d-none', !isCompoundPrescription());
            $('#embalaseTotalText').text(formatCurrency(embalase));
            $('#grandTotalText').text(formatCurrency(grandTotal));
            $('#paymentDueText').text(formatCurrency(grandTotal));
            $('#summaryItemCount').text(`${cart.length} item`);
            $('#paidTotalText').text(formatCurrency(paidTotal));
            $('#changeDueLabel').text(paidTotal > 0 && diff >= 0 ? 'Kembalian' : 'Sisa tagihan');
            $('#changeDueText').text(formatCurrency(Math.abs(diff)));

            const paymentProgress = grandTotal > 0 ? Math.min(100, Math.max(0, paidTotal / grandTotal * 100)) : 0;
            const checkoutStage = $('#posPaymentLayout');
            const totalPanel = $('.pos-total-panel');
            const paymentCoverage = $('#paymentCoverage');
            const checkoutStatus = $('#checkoutStatusBadge');
            let coverageTitle = 'Belum ada pembayaran';
            let coverageText = grandTotal > 0
                ? `Masih perlu diterima ${formatCurrency(grandTotal)}.`
                : 'Tambahkan item untuk menghitung total tagihan.';
            let checkoutStatusLabel = cart.length > 0 ? 'Menunggu pembayaran' : 'Menunggu item';
            let checkoutStatusIcon = cart.length > 0 ? 'mdi-cash-clock' : 'mdi-cart-outline';
            const hasUnavailableItems = cart.some(item => !item.is_available);
            const paymentReady = cart.length > 0 && !hasUnavailableItems && (isCreditTransaction() || diff >= -0.01);
            const paymentShort = cart.length > 0 && !isCreditTransaction() && diff < -0.01;

            if (hasUnavailableItems) {
                coverageTitle = 'Stok perlu diperiksa';
                coverageText = 'Selesaikan kendala stok sebelum menutup transaksi.';
                checkoutStatusLabel = 'Periksa stok item';
                checkoutStatusIcon = 'mdi-package-variant-closed-remove';
            } else if (cart.length > 0 && paidTotal > 0 && diff < -0.01) {
                coverageTitle = isCreditTransaction() ? 'Pembayaran dicatat sebagian' : 'Pembayaran belum cukup';
                coverageText = isCreditTransaction()
                    ? `${formatCurrency(Math.abs(diff))} akan menjadi tagihan.`
                    : `Kurang ${formatCurrency(Math.abs(diff))} untuk melunasi transaksi.`;
                checkoutStatusLabel = isCreditTransaction() ? 'Siap dicatat sebagai tagihan' : `Kurang ${formatCurrency(Math.abs(diff))}`;
                checkoutStatusIcon = isCreditTransaction() ? 'mdi-file-document-check-outline' : 'mdi-alert-circle-outline';
            } else if (cart.length > 0 && diff >= -0.01) {
                coverageTitle = diff > 0.01 ? 'Pembayaran cukup, siapkan kembalian' : 'Pembayaran pas';
                coverageText = diff > 0.01
                    ? `Kembalian pelanggan ${formatCurrency(diff)}.`
                    : 'Nominal pembayaran sesuai dengan total tagihan.';
                checkoutStatusLabel = 'Siap diselesaikan';
                checkoutStatusIcon = 'mdi-check-decagram-outline';
            } else if (cart.length > 0 && isCreditTransaction()) {
                coverageTitle = 'Transaksi kredit siap dicatat';
                coverageText = `${formatCurrency(Math.abs(diff))} akan menjadi tagihan pelanggan.`;
                checkoutStatusLabel = 'Siap dicatat sebagai tagihan';
                checkoutStatusIcon = 'mdi-file-document-check-outline';
            }

            checkoutStage
                .toggleClass('has-items', cart.length > 0)
                .toggleClass('is-short', paymentShort)
                .toggleClass('is-ready', paymentReady);
            totalPanel.toggleClass('is-short', paymentShort).toggleClass('is-ready', paymentReady);
            paymentCoverage.toggleClass('is-ready', paymentReady);
            $('#paymentCoverageTitle').text(coverageTitle);
            $('#paymentCoverageText').text(coverageText);
            $('#paymentProgressBar').css('width', `${paymentProgress}%`);
            $('.pos-payment-progress').attr('aria-valuenow', Math.round(paymentProgress));
            checkoutStatus.html(`<i class="mdi ${checkoutStatusIcon}"></i><span>${escapeHtml(checkoutStatusLabel)}</span>`);

            const healthCard = $('#cartHealthCard');
            const healthIcon = healthCard.find('.pos-insight-icon i');
            healthCard.toggleClass('has-warning', unavailableCount > 0);

            if (cart.length === 0) {
                $('#cartStockHealth').text('Menunggu item');
                $('#cartReadinessText').text('Siap menerima produk');
                healthIcon.attr('class', 'mdi mdi-shield-check-outline');
            } else if (unavailableCount > 0) {
                $('#cartStockHealth').text(`${unavailableCount} perlu perhatian`);
                $('#cartReadinessText').text('Periksa ketersediaan stok');
                healthIcon.attr('class', 'mdi mdi-alert-circle-outline');
            } else {
                $('#cartStockHealth').text('Semua tersedia');
                $('#cartReadinessText').text('Keranjang siap diteruskan');
                healthIcon.attr('class', 'mdi mdi-shield-check-outline');
            }

            $('#cartReadinessBar').css('width', `${readinessPercent}%`);
            $('.pos-cart-readiness-track').attr('aria-valuenow', readinessPercent);

            const paymentHint = $('#posPaymentHint');
            const submitBar = $('.pos-submit-bar');
            const submitIcon = $('.pos-submit-status-icon i');
            const creditTransaction = isCreditTransaction();
            const canComplete = cart.length > 0 && (creditTransaction || diff >= -0.01) && !cart.some(item => !item.is_available);

            $('#completeTransactionBtn')
                .prop('disabled', !canComplete)
                .attr('title', canComplete ? 'Selesaikan pembayaran (F9)' : 'Lengkapi keranjang dan pembayaran terlebih dahulu');
            $('#goToPaymentBtn').prop('disabled', cart.length === 0);

            submitBar.removeClass('is-ready is-warning');

            if (cart.length === 0) {
                submitIcon.attr('class', 'mdi mdi-information-outline');
                paymentHint.text('Tambahkan item untuk memproses transaksi.');
            } else if (unavailableCount > 0) {
                submitBar.addClass('is-warning');
                submitIcon.attr('class', 'mdi mdi-package-variant-closed-remove');
                paymentHint.text(`Periksa stok pada ${unavailableCount} item sebelum menyelesaikan transaksi.`);
            } else if (creditTransaction && diff < -0.01) {
                submitBar.addClass('is-ready');
                submitIcon.attr('class', 'mdi mdi-file-document-check-outline');
                paymentHint.text(`Sisa ${formatCurrency(Math.abs(diff))} akan dicatat sebagai tagihan.`);
            } else if (diff < -0.01) {
                submitBar.addClass('is-warning');
                submitIcon.attr('class', 'mdi mdi-alert-circle-outline');
                paymentHint.text(`Pembayaran kurang ${formatCurrency(Math.abs(diff))}.`);
            } else {
                submitBar.addClass('is-ready');
                submitIcon.attr('class', 'mdi mdi-check-decagram-outline');
                paymentHint.text('Pembayaran cukup. Transaksi siap diselesaikan.');
            }

            lastTotals = {
                grandTotal,
                paidTotal,
                diff
            };
            $('#prescriptionModalItemCount').text(`${cart.length} ${isCompoundPrescription() ? 'komponen' : 'obat'}`);
            $('#prescriptionModalGrandTotal, #prescriptionHandoffTotal').text(formatCurrency(grandTotal));
            updatePrescriptionPaymentHandoff();
            updateTransactionState();
            updateFlow();

            return {
                subtotalGross,
                itemDiscountTotal,
                transactionDiscount,
                taxTotal,
                embalase,
                grandTotal,
                paidTotal,
                diff
            };
        }

        $('#transactionDiscountPercent, #transactionDiscountNominal, #taxPercent').on('input', recalculateTotals);
        $('#useTax').on('change', function() {
            $('#taxPercent').prop('disabled', !this.checked);
            recalculateTotals();
        });

        function addPaymentRow(method = 'tunai', amount = '', reference = '') {
            const uid = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            const options = Object.entries(paymentMethods).map(([value, label]) => `
                <option value="${escapeHtml(value)}" ${value === method ? 'selected' : ''}>${escapeHtml(label)}</option>
            `).join('');

            $('#paymentRows').append(`
                <div class="pos-payment-row" data-id="${uid}">
                    <span class="pos-payment-row-number" aria-hidden="true">1</span>
                    <span class="pos-payment-field">
                        <label>Metode pembayaran</label>
                        <select class="form-select payment-method" aria-label="Metode pembayaran">${options}</select>
                    </span>
                    <span class="pos-payment-field">
                        <label>Nominal diterima</label>
                        <span class="pos-payment-amount-wrap">
                            <span>Rp</span>
                            <input type="number" class="form-control payment-amount" min="0" step="0.01" value="${amount}" placeholder="0" aria-label="Nominal pembayaran">
                        </span>
                    </span>
                    <span class="pos-payment-field">
                        <label>Nomor referensi <small>(opsional)</small></label>
                        <span class="pos-payment-reference-wrap">
                            <i class="mdi mdi-pound"></i>
                            <input type="text" class="form-control payment-reference" value="${escapeHtml(reference)}" placeholder="Catatan / nomor referensi" aria-label="Nomor referensi pembayaran">
                        </span>
                    </span>
                    <button type="button" class="btn remove-payment-row" title="Hapus metode pembayaran" aria-label="Hapus metode pembayaran">
                        <i class="mdi mdi-trash-can-outline"></i>
                    </button>
                </div>
            `);
            refreshPaymentRowsUi(uid);
            recalculateTotals();

            return uid;
        }

        function refreshPaymentRowsUi(activeUid = null) {
            const rows = $('.pos-payment-row');

            rows.each(function(index) {
                const row = $(this);
                const method = row.find('.payment-method').val();
                row.find('.pos-payment-row-number').text(index + 1);
                row.attr('data-method', method || 'lainnya');

                if (method === 'tunai') {
                    row.find('.payment-reference').attr('placeholder', 'Catatan (opsional)');
                } else {
                    row.find('.payment-reference').attr('placeholder', 'Nomor referensi / otorisasi');
                }
            });

            if (activeUid) {
                rows.removeClass('is-active').filter(`[data-id="${activeUid}"]`).addClass('is-active');
            } else if (!rows.filter('.is-active').length && rows.length) {
                rows.first().addClass('is-active');
            }

            $('#paymentMethodCount').text(`${rows.length} metode`);
        }

        function paymentRows() {
            const rows = [];

            $('.pos-payment-row').each(function() {
                rows.push({
                    metode: $(this).find('.payment-method').val(),
                    amount: Math.max(0, Number($(this).find('.payment-amount').val()) || 0),
                    reference_no: $(this).find('.payment-reference').val() || ''
                });
            });

            return rows;
        }

        $('#addPaymentBtn').on('click', function() {
            const uid = addPaymentRow('tunai');
            $(`.pos-payment-row[data-id="${uid}"] .payment-method`).trigger('focus');
        });
        $(document).on('focusin click', '.pos-payment-row', function() {
            $('.pos-payment-row').removeClass('is-active');
            $(this).addClass('is-active');
        });
        $(document).on('click', '.pos-quick-payment', function() {
            if ($('.pos-payment-row').length === 0) {
                addPaymentRow('tunai');
            }

            const totals = recalculateTotals();
            const targetPayment = $('.pos-payment-row.is-active').first().length
                ? $('.pos-payment-row.is-active').first()
                : $('.pos-payment-row').first();
            const otherPaid = $('.pos-payment-row').not(targetPayment).toArray().reduce((sum, row) => {
                return sum + Math.max(0, Number($(row).find('.payment-amount').val()) || 0);
            }, 0);
            const amount = $(this).data('amount') === 'exact'
                ? Math.max(0, totals.grandTotal - otherPaid)
                : Math.max(0, Number($(this).data('amount')) || 0);

            targetPayment.find('.payment-method').val('tunai');
            targetPayment.find('.payment-amount').val(amount).trigger('input').trigger('focus');
            refreshPaymentRowsUi(targetPayment.data('id'));
        });
        $(document).on('input change', '.payment-amount, .payment-method, .payment-reference', function() {
            refreshPaymentRowsUi($(this).closest('.pos-payment-row').data('id'));
            recalculateTotals();
        });
        $(document).on('click', '.remove-payment-row', function() {
            $(this).closest('.pos-payment-row').remove();
            if ($('.pos-payment-row').length === 0) {
                addPaymentRow('tunai');
            }
            refreshPaymentRowsUi();
            recalculateTotals();
        });

        function syncTransactionFields() {
            const type = currentTransactionType();
            const prescription = isPrescriptionTransaction(type);
            const compound = isCompoundPrescription(type);

            $('#prescriptionWorkspace').toggleClass('d-none', !prescription);
            $('#compoundSetup').toggleClass('d-none', !compound);
            $('#prescriptionCashierStep').attr('data-prescription-mode', compound ? 'compound' : 'standard');
            $('#prescriptionTypeModal').attr('data-prescription-mode', compound ? 'compound' : 'standard');
            $('.pos-institution-field').toggleClass('d-none', type !== 'penjualan_instansi');
            $('.pos-prescription-type-option')
                .removeClass('is-active')
                .attr('aria-pressed', 'false')
                .filter(`[data-prescription-type="${type}"]`)
                .addClass('is-active')
                .attr('aria-pressed', 'true');
            $('#prescriptionModeSummary').toggleClass('is-compound', compound);
            $('#prescriptionModeIcon').html(`<i class="mdi ${compound ? 'mdi-mortar-pestle-plus' : 'mdi-pill-multiple'}"></i>`);
            $('#prescriptionModeLabel').text(compound ? 'Racikan' : 'Non Racikan');
            $('#cartQtyHeaderLabel').text(compound ? 'Stok Keluar' : 'Jumlah');
            $('#prescriptionModeSummaryCopy').text(compound
                ? 'Komponen obat disusun dalam kelompok R/ dengan satu etiket bersama.'
                : 'Etiket dan aturan pakai dicatat untuk setiap obat.');

            if (prescription && !$('#tanggalResep').val()) {
                $('#tanggalResep').val(($('#transactionDate').val() || localDateTimeValue()).slice(0, 10));
            }

            if (compound) {
                cart.forEach(item => {
                    if (!String(item.racikan_group || '').trim()) item.racikan_group = activeCompoundGroup;
                    if (!(Number(item.jumlah_resep) > 0)) item.jumlah_resep = Number(item.qty) || 1;
                    item.bentuk_racikan ??= '';
                    item.jumlah_racikan ??= '';
                    item.jumlah_ambil_resep ??= '';
                    item.signa_1 ??= '';
                    item.signa_2 ??= '';
                    item.embalase_racikan ??= 0;
                    item.kekuatan_obat ??= '';
                });
            }

            $('#prescriptionWorkspaceTitle').text(compound ? 'Resep Racikan' : 'Resep Non Racikan');
            $('#prescriptionWorkspaceCopy').text(compound
                ? 'Susun satu racikan, lengkapi seluruh komponen, lalu simpan sebelum membuat racikan berikutnya.'
                : 'Setiap obat berdiri sendiri dan memiliki etiket serta aturan pakainya masing-masing.');
            $('#prescriptionPreparationTitle').text(compound ? 'Racikan & etiket bersama' : 'Obat & etiket');
            $('#prescriptionPreparationCopy').text(compound
                ? 'Pilih racikan aktif, isi etiket bersama, lalu lengkapi dosis setiap komponen.'
                : 'Atur jumlah, signa, dan detail klinis setiap obat.');
            $('#prescriptionModeNoteTitle').text(compound ? 'Obat dirangkai per kelompok R/' : 'Isi etiket per obat');
            $('#prescriptionModeNoteCopy').text(compound
                ? 'Tambah obat ke racikan aktif, isi bentuk racikan, signa, JHO, dosis dan jumlah resep, lalu tekan Simpan Racikan.'
                : 'Tambahkan obat, lalu lengkapi aturan pakai pada kartu obat tersebut. Tidak perlu memilih kelompok R/.');
            $('#prescriptionGuidanceTitle').text(compound ? 'Periksa kelompok racikan sebelum pembayaran' : 'Verifikasi resep sebelum pembayaran');
            $('#prescriptionGuidanceCopy').text(compound
                ? 'Setiap racikan wajib disimpan; setiap komponen wajib memiliki dosis resep dan jumlah resep.'
                : 'Nama pasien, nomor resep, tanggal, dokter, dan aturan pakai wajib terisi.');
            $('#finishPrescriptionFlowBtn').html(compound
                ? '<span><small>Verifikasi sebelum bayar</small>Preview resep</span><i class="mdi mdi-clipboard-check-multiple-outline"></i>'
                : '<span><small>Simpan resep & lanjut</small>Bayar</span><i class="mdi mdi-credit-card-check-outline"></i>');
            $('#prescriptionToolbarPayBtn').html(compound
                ? '<i class="mdi mdi-clipboard-check-outline"></i> Preview resep'
                : '<i class="mdi mdi-credit-card-check-outline"></i> Bayar');
            $('.pos-prescription-flow-steps span').eq(2).html(compound ? '<b>3</b> Preview & bayar' : '<b>3</b> Lanjut bayar');
            $('#cartEditorCopy').text(compound
                ? 'Isi etiket bersama terlebih dahulu, lalu lengkapi dosis setiap komponen di bawahnya.'
                : (prescription ? 'Lengkapi etiket pada setiap obat non-racikan.' : 'Ubah jumlah atau diskon langsung pada setiap baris.'));
            $('#cartEditorTitle').text(compound ? 'Isi racikan aktif' : (prescription ? 'Obat resep & etiket' : 'Item yang dijual'));
            if (!compound) {
                $('#clearCartBtn')
                    .removeClass('d-none')
                    .html('<i class="mdi mdi-trash-can-outline"></i> Kosongkan');
            }
            updateTransactionDetailSummary();

            if (type === 'penjualan_kredit' && $('.pos-payment-row').length === 1 && toNumber($('.payment-amount').first().val()) === 0) {
                $('.payment-method').first().val('piutang');
            }

            if (type === 'penjualan_instansi' && $('.pos-payment-row').length === 1 && toNumber($('.payment-amount').first().val()) === 0) {
                $('.payment-method').first().val('instansi');
            }

            renderCart();
        }

        function prescriptionModalInstance() {
            return bootstrap.Modal.getOrCreateInstance(document.getElementById('prescriptionTypeModal'));
        }

        function mountPrescriptionWorkspace() {
            if (prescriptionWorkspaceMounted) {
                return;
            }

            $('#prescriptionModalCatalogSlot').append($('.pos-catalog').first());
            $('#prescriptionModalDetailsSlot').append($('.pos-transaction-details').first().prop('open', true));
            $('#prescriptionModalCompoundSlot').append($('#compoundSetup'));
            $('#prescriptionModalCartSlot').append(
                $('.pos-cart-toolbar').first(),
                $('.pos-cart-wrap').first(),
                $('.pos-cart-footnote').first()
            );
            prescriptionWorkspaceMounted = true;
        }

        function restorePrescriptionWorkspace() {
            if (!prescriptionWorkspaceMounted) {
                return;
            }

            $('#posCatalogHomeAnchor').after($('#prescriptionModalCatalogSlot > .pos-catalog'));
            $('#compoundSetupHomeAnchor').after($('#prescriptionModalCompoundSlot > #compoundSetup'));
            $('#posDetailsHomeAnchor').after($('#prescriptionModalDetailsSlot > .pos-transaction-details'));
            $('#posCartHomeAnchor').after(
                $('#prescriptionModalCartSlot > .pos-cart-toolbar'),
                $('#prescriptionModalCartSlot > .pos-cart-wrap'),
                $('#prescriptionModalCartSlot > .pos-cart-footnote')
            );
            if (productSearchInModal) {
                initializeProductSearch();
                productSearchInModal = false;
            }
            prescriptionWorkspaceMounted = false;
        }

        function setPrescriptionPaymentMode(enabled) {
            prescriptionPaymentMode = Boolean(enabled && isPrescriptionTransaction());
            $('#posWorkspace').toggleClass('is-prescription-payment-mode', prescriptionPaymentMode);
            $('#transactionPanel').toggleClass('is-prescription-handoff', prescriptionPaymentMode);
            $('#prescriptionPaymentHandoff').toggleClass('d-none', !prescriptionPaymentMode);
            $('#paymentEditPrescriptionBtn').toggleClass('d-none', !prescriptionPaymentMode);
            if (prescriptionPaymentMode) {
                setCashierStage('payment', { focus: false });
            }
            updatePrescriptionPaymentHandoff();
        }

        function showPrescriptionTypeStep() {
            $('.pos-prescription-type-option')
                .removeClass('is-active')
                .attr('aria-pressed', 'false')
                .filter(`[data-prescription-type="${$('#jenisResep').val() || 'penjualan_resep'}"]`)
                .addClass('is-active')
                .attr('aria-pressed', 'true');
            $('#prescriptionCashierStep').addClass('d-none');
            $('#compoundPreviewStep').addClass('d-none');
            $('#prescriptionTypeStep').removeClass('d-none');

            const canReturnToWorkspace = isPrescriptionTransaction(committedTransactionType)
                && (prescriptionTypeApplied || isPrescriptionTransaction(prescriptionModalReturnType));
            $('#cancelPrescriptionTypeBtn').text(canReturnToWorkspace ? 'Kembali ke proses resep' : 'Batal');
        }

        function showPrescriptionCashierStep() {
            mountPrescriptionWorkspace();
            compoundPreviewApproved = false;
            $('.pos-transaction-details').prop('open', true);
            $('#prescriptionTypeStep').addClass('d-none');
            $('#compoundPreviewStep').addClass('d-none');
            $('#prescriptionCashierStep').removeClass('d-none');
            if (!productSearchInModal) {
                initializeProductSearch($('#prescriptionCashierStep'));
                productSearchInModal = true;
            }
            $('#prescriptionFlowTitle').text(isCompoundPrescription() ? 'Resep Racikan' : 'Resep Non Racikan');
            $('#cancelPrescriptionFlowBtn').html(prescriptionPaymentMode
                ? '<i class="mdi mdi-arrow-left"></i> Kembali ke pembayaran'
                : '<i class="mdi mdi-close"></i> Batalkan proses');
            updatePrescriptionReadiness();
            recalculateTotals();
            window.setTimeout(() => $('#customerName').trigger('focus'), 180);
        }

        function previewDate(value) {
            if (!value) return '-';

            const parsed = new Date(`${value}T00:00:00`);
            if (Number.isNaN(parsed.getTime())) return value;

            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }).format(parsed);
        }

        function compoundPreviewClinicalField(label, value) {
            return `<span><small>${escapeHtml(label)}</small><strong title="${escapeHtml(value || '-')}">${escapeHtml(value || '-')}</strong></span>`;
        }

        function renderCompoundPreview() {
            const branch = activePosBranch() || {};
            const branchName = branch.display_name || branch.name || 'Medcare Pharmacy';
            const branchLogo = branch.logo_url || '{{ asset("assets/apotek/LogoResmi.png") }}';
            const patient = String($('#customerName').val() || '').trim() || 'Umum';
            const prescriptionNumber = String($('#nomorResep').val() || '').trim() || '-';
            const doctor = String($('#dokterName').val() || '').trim() || '-';
            const groups = compoundGroups().filter(group => compoundGroupItems(group).length > 0);
            const consumptionLabels = {
                sebelum_makan: 'Sebelum makan',
                sesudah_makan: 'Sesudah makan',
                bersama_makan: 'Bersama makan',
                tidak_terkait_makan: 'Tidak terkait makan',
                sesuai_instruksi: 'Sesuai instruksi dokter'
            };
            const totals = recalculateTotals();

            $('#compoundPreviewSummary').html(`
                <div class="pos-compound-preview-identity">
                    <span class="pos-compound-preview-logo"><img src="${escapeHtml(branchLogo)}" alt="Logo ${escapeHtml(branchName)}"></span>
                    <span class="pos-compound-preview-meta"><small>PASIEN</small><strong title="${escapeHtml(patient)}">${escapeHtml(patient)}</strong></span>
                    <span class="pos-compound-preview-meta"><small>NO. RESEP</small><strong title="${escapeHtml(prescriptionNumber)}">${escapeHtml(prescriptionNumber)}</strong></span>
                    <span class="pos-compound-preview-meta"><small>DOKTER</small><strong title="${escapeHtml(doctor)}">${escapeHtml(doctor)}</strong></span>
                    <span class="pos-compound-preview-meta"><small>TANGGAL RESEP</small><strong>${escapeHtml(previewDate($('#tanggalResep').val()))}</strong></span>
                </div>
                <div class="pos-compound-preview-total">
                    <small>${groups.length} RACIKAN · ${cart.length} KOMPONEN</small>
                    <strong>${formatCurrency(totals.grandTotal)}</strong>
                </div>
            `);

            const groupHtml = groups.map(group => {
                const items = compoundGroupItems(group);
                const reference = items[0] || {};
                const groupTotal = items.reduce((sum, item) => sum + itemNet(item), 0)
                    + Math.max(0, Number(reference.embalase_racikan) || 0);
                const groupLabel = compoundGroupLabel(group);
                const form = reference.bentuk_racikan || 'Racikan';
                const directions = reference.aturan_pakai || composeCompoundSigna(reference) || '-';
                const consumption = consumptionLabels[reference.waktu_konsumsi] || reference.waktu_konsumsi || 'Sesuai petunjuk';
                const note = reference.keterangan || 'Gunakan sesuai petunjuk. Jauhkan dari jangkauan anak.';
                const componentRows = items.map(item => `
                    <tr>
                        <td>${escapeHtml(item.nama_obat)}</td>
                        <td>${escapeHtml(item.kekuatan_obat || '-')}</td>
                        <td>${escapeHtml(item.dosis_komponen || '-')}</td>
                        <td>${formatNumber(item.jumlah_resep)} ${escapeHtml(item.satuan || '')}</td>
                        <td>${formatNumber(item.qty)} ${escapeHtml(item.satuan || '')}</td>
                        <td>${formatCurrency(itemNet(item))}</td>
                    </tr>
                `).join('');

                return `
                    <article class="pos-compound-preview-group">
                        <header class="pos-compound-preview-group-head">
                            <span class="pos-compound-preview-number">${escapeHtml(normalizeCompoundGroup(group))}</span>
                            <span class="pos-compound-preview-group-title">
                                <strong>${escapeHtml(groupLabel)} · ${escapeHtml(form)}</strong>
                                <small>${items.length} komponen · Total ${formatCurrency(groupTotal)}</small>
                            </span>
                            <span class="pos-compound-preview-saved"><i class="mdi mdi-check-decagram"></i> TERSIMPAN</span>
                        </header>
                        <div class="pos-compound-preview-content">
                            <div>
                                <div class="pos-compound-preview-clinical">
                                    ${compoundPreviewClinicalField('Dibuat', `${formatNumber(reference.jumlah_racikan)} ${form}`)}
                                    ${compoundPreviewClinicalField('Diambil', formatNumber(reference.jumlah_ambil_resep))}
                                    ${compoundPreviewClinicalField('Signa', `${reference.signa_1 || '-'} × ${reference.signa_2 || '-'}`)}
                                    ${compoundPreviewClinicalField('Aturan pakai', directions)}
                                    ${compoundPreviewClinicalField('JHO', reference.durasi_hari ? `${reference.durasi_hari} hari` : '-')}
                                    ${compoundPreviewClinicalField('Embalase', formatCurrency(reference.embalase_racikan))}
                                </div>
                                <div class="table-responsive">
                                    <table class="pos-compound-preview-table">
                                        <thead><tr><th>Komponen obat</th><th>Kekuatan</th><th>Dosis resep</th><th>Jumlah resep</th><th>Stok keluar</th><th>Nilai</th></tr></thead>
                                        <tbody>${componentRows}</tbody>
                                    </table>
                                </div>
                            </div>
                            <aside class="pos-compound-label-preview" aria-label="Pratinjau etiket ${escapeHtml(groupLabel)}">
                                <div class="pos-compound-label-preview-head">
                                    <span><img src="${escapeHtml(branchLogo)}" alt=""></span>
                                    <div><strong>${escapeHtml(branchName)}</strong><small>ETIKET OBAT RACIKAN</small></div>
                                </div>
                                <div class="pos-compound-label-preview-body">
                                    <small>NAMA PASIEN</small>
                                    <strong>${escapeHtml(patient)}</strong>
                                    <div class="pos-compound-label-directions">${escapeHtml(directions)}</div>
                                    <p>${escapeHtml(consumption)} · ${reference.durasi_hari ? `${reference.durasi_hari} hari` : 'Durasi sesuai petunjuk'}<br>${escapeHtml(note)}</p>
                                </div>
                            </aside>
                        </div>
                    </article>
                `;
            }).join('');

            $('#compoundPreviewGroups').html(groupHtml);
        }

        function showCompoundPreviewStep() {
            renderCompoundPreview();
            compoundPreviewApproved = false;
            $('#prescriptionTypeStep, #prescriptionCashierStep').addClass('d-none');
            $('#compoundPreviewStep').removeClass('d-none');
            document.querySelector('.pos-compound-preview-body')?.scrollTo({ top: 0, behavior: 'auto' });
            window.setTimeout(() => $('#editCompoundPreviewBtn').trigger('focus'), 100);
        }

        function openPrescriptionFlow(options = {}) {
            const chooseType = Boolean(options.chooseType);

            prescriptionModalReturnType = options.returnType || committedTransactionType;
            prescriptionTypeApplied = false;
            prescriptionFlowCloseReason = null;

            if (chooseType) {
                showPrescriptionTypeStep();
            } else {
                showPrescriptionCashierStep();
            }

            prescriptionModalInstance().show();
        }

        function restorePrescriptionReturnType() {
            const returnType = prescriptionModalReturnType || 'penjualan_bebas';

            if (isPrescriptionTransaction(returnType)) {
                $('#jenisTransaksi').val('penjualan_resep');
                $('#jenisResep').val(returnType);
            } else {
                $('#jenisTransaksi').val(returnType);
            }

            committedTransactionType = returnType;
            syncTransactionFields();
        }

        function cancelPrescriptionEntry() {
            prescriptionFlowCloseReason = 'cancel';
            prescriptionModalInstance().hide();
        }

        $('#jenisTransaksi').on('change', function() {
            const selectedType = $(this).val();

            if (selectedType === 'penjualan_resep') {
                setPrescriptionPaymentMode(false);
                openPrescriptionFlow({
                    chooseType: true,
                    returnType: committedTransactionType
                });
                return;
            }

            committedTransactionType = selectedType;
            setPrescriptionPaymentMode(false);
            syncTransactionFields();
        });
        $('#customerName, #customerPhone, #nomorResep, #tanggalResep, #dokterName, #asalResep, #instansiName, #catatanTransaksi').on('input change', function() {
            updateTransactionDetailSummary();
            updatePrescriptionReadiness();
        });

        $('.pos-prescription-type-option').on('click', function() {
            const type = $(this).data('prescription-type');

            prescriptionTypeApplied = true;
            committedTransactionType = type;
            $('#jenisResep').val(type);
            $('#jenisTransaksi').val('penjualan_resep');
            syncTransactionFields();
            showPrescriptionCashierStep();
        });

        $('#changePrescriptionTypeBtn').on('click', function() {
            showPrescriptionTypeStep();
        });

        $('#cancelPrescriptionTypeBtn, #closePrescriptionTypeBtn').on('click', function() {
            const canReturnToWorkspace = isPrescriptionTransaction(committedTransactionType)
                && (prescriptionTypeApplied || isPrescriptionTransaction(prescriptionModalReturnType));

            if (canReturnToWorkspace) {
                showPrescriptionCashierStep();
                return;
            }

            cancelPrescriptionEntry();
        });

        $('#cancelPrescriptionFlowBtn').on('click', function() {
            const readiness = updatePrescriptionReadiness();

            if (prescriptionPaymentMode && readiness.ready) {
                if (isCompoundPrescription()) {
                    showCompoundPreviewStep();
                    return;
                }
                prescriptionFlowCloseReason = 'finish';
                prescriptionModalInstance().hide();
                return;
            }

            Swal.fire({
                title: 'Batalkan proses resep?',
                text: 'Jenis transaksi akan dikembalikan seperti sebelum workspace resep dibuka.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan proses',
                cancelButtonText: 'Lanjutkan resep'
            }).then(result => {
                if (result.isConfirmed) {
                    cancelPrescriptionEntry();
                }
            });
        });

        $('#finishPrescriptionFlowBtn').on('click', function() {
            const readiness = updatePrescriptionReadiness();

            if (!readiness.ready) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Proses resep belum lengkap',
                    text: readiness.message,
                    confirmButtonText: 'Periksa kembali'
                });
                return;
            }

            if (isCompoundPrescription()) {
                showCompoundPreviewStep();
                return;
            }

            prescriptionFlowCloseReason = 'finish';
            prescriptionModalInstance().hide();
        });

        $('#editCompoundPreviewBtn').on('click', function() {
            showPrescriptionCashierStep();
        });

        $('#confirmCompoundPreviewBtn').on('click', function() {
            compoundPreviewApproved = true;
            prescriptionFlowCloseReason = 'finish';
            prescriptionModalInstance().hide();
        });

        $('#prescriptionToolbarPayBtn').on('click', function() {
            $('#finishPrescriptionFlowBtn').trigger('click');
        });

        $('#savePrescriptionDraftBtn').on('click', function() {
            $('#saveDraftBtn').trigger('click');
        });

        $('#editPrescriptionBtn').on('click', function() {
            openPrescriptionFlow({
                chooseType: false,
                returnType: currentTransactionType()
            });
        });

        $('#prescriptionTypeModal').on('hidden.bs.modal', function() {
            restorePrescriptionWorkspace();

            if (prescriptionFlowCloseReason === 'finish') {
                setPrescriptionPaymentMode(true);
                window.requestAnimationFrame(() => {
                    $('.payment-amount').first().trigger('focus').select();
                });
            } else if (prescriptionFlowCloseReason === 'cancel') {
                setPrescriptionPaymentMode(false);
                restorePrescriptionReturnType();
            }

            prescriptionModalReturnType = null;
            prescriptionTypeApplied = false;
            prescriptionFlowCloseReason = null;
        });

        $('#setGeneralCustomerBtn').on('click', function() {
            clearPatientSelection();
            $('#customerName, #customerPhone').val('');
            updateTransactionDetailSummary();
            $('#customerName').trigger('focus');
        });

        function updateTransactionDetailSummary() {
            const customer = String($('#customerName').val() || '').trim();
            const phone = String($('#customerPhone').val() || '').trim();
            const typeLabel = transactionTypes[currentTransactionType()] || $('#jenisTransaksi option:selected').text() || 'Umum';
            const customerLabel = customer || 'Pelanggan umum';
            const customerInitial = customer ? customer.charAt(0).toUpperCase() : 'U';
            const summaryParts = [typeLabel];

            if (phone) summaryParts.push(phone);

            $('#transactionCustomerName').text(customerLabel);
            $('#transactionCustomerAvatar').text(customerInitial);
            $('.pos-detail-summary').text(summaryParts.join(' · '));
        }

        function updatePrescriptionPaymentHandoff() {
            if (!isPrescriptionTransaction()) {
                return;
            }

            const compound = isCompoundPrescription();
            const patient = String($('#customerName').val() || '').trim() || 'Pelanggan umum';
            const prescriptionNumber = String($('#nomorResep').val() || '').trim() || 'Nomor resep belum diisi';
            const doctor = String($('#dokterName').val() || '').trim() || 'Dokter belum diisi';
            const itemLabel = compound ? `${cart.length} komponen` : `${cart.length} obat`;
            const groupLabel = compound
                ? ` · ${compoundGroups().filter(group => compoundGroupItems(group).length > 0).length} kelompok R/`
                : '';

            $('#prescriptionHandoffTitle').text(`${compound ? 'Resep Racikan' : 'Resep Non Racikan'} · ${patient}`);
            $('#prescriptionHandoffMeta').text(`${prescriptionNumber} · ${doctor} · ${itemLabel}${groupLabel}`);
            $('#prescriptionHandoffTotal').text(formatCurrency(lastTotals.grandTotal));
        }

        function updatePrescriptionReadiness() {
            if (!isPrescriptionTransaction()) {
                $('#prescriptionFlowReadiness').removeClass('is-ready');
                $('#prescriptionFlowReadinessText').text('Pilih jenis resep untuk memulai');
                return {
                    ready: false,
                    message: 'Pilih jenis resep terlebih dahulu.'
                };
            }

            const requiredHeaderFields = [
                ['Nama pasien', $('#customerName').val()],
                ['Nomor resep', $('#nomorResep').val()],
                ['Tanggal resep', $('#tanggalResep').val()],
                ['Dokter penulis', $('#dokterName').val()]
            ];
            const missingHeaderFields = requiredHeaderFields
                .filter(([, value]) => !String(value || '').trim())
                .map(([label]) => label);
            const headerComplete = missingHeaderFields.length === 0;
            const completeItems = cart.filter(isPrescriptionItemComplete).length;
            const unavailableItems = cart.filter(item => !item.is_available).length;
            const populatedCompoundGroups = isCompoundPrescription()
                ? compoundGroups().filter(group => compoundGroupItems(group).length > 0)
                : [];
            const unsavedCompoundGroups = isCompoundPrescription()
                ? populatedCompoundGroups.filter(group => !isCompoundGroupSaved(group))
                : [];
            const compoundGroupsReady = !isCompoundPrescription()
                || (populatedCompoundGroups.length > 0 && unsavedCompoundGroups.length === 0);
            const ready = headerComplete
                && cart.length > 0
                && completeItems === cart.length
                && unavailableItems === 0
                && compoundGroupsReady;
            const status = $('#prescriptionReadiness');
            const detailsPanel = $('.pos-prescription-modal-details');
            const editorPanel = $('.pos-prescription-modal-editor');
            let message = isCompoundPrescription()
                ? `${populatedCompoundGroups.length} racikan tersimpan dan siap dibayar. Racikan berikutnya tidak wajib diisi.`
                : 'Resep lengkap dan siap dilanjutkan ke pembayaran.';

            if (missingHeaderFields.length > 0) {
                message = `Lengkapi ${missingHeaderFields.join(', ')}.`;
            } else if (cart.length === 0) {
                message = 'Tambahkan minimal satu obat ke dalam resep.';
            } else if (unsavedCompoundGroups.length > 0) {
                message = `Lengkapi dan simpan ${compoundGroupLabel(unsavedCompoundGroups[0])}.`;
            } else if (completeItems !== cart.length) {
                message = isCompoundPrescription()
                    ? `Lengkapi kelompok dan dosis pada ${cart.length - completeItems} komponen racikan.`
                    : `Lengkapi aturan pakai pada ${cart.length - completeItems} obat.`;
            } else if (unavailableItems > 0) {
                message = `Periksa stok pada ${unavailableItems} item yang belum tersedia.`;
            }

            $('#prescriptionItemCounter').text(`${completeItems}/${cart.length} ${isCompoundPrescription() ? 'komponen' : 'obat'} lengkap`);
            detailsPanel
                .toggleClass('is-complete', headerComplete)
                .find('.pos-rx-panel-state')
                .html(headerComplete
                    ? '<i class="mdi mdi-check-circle-outline"></i> Data lengkap'
                    : '<i class="mdi mdi-progress-alert"></i> Perlu dilengkapi');
            editorPanel.toggleClass('is-complete', cart.length > 0 && completeItems === cart.length && unavailableItems === 0);
            $('#prescriptionWorkspace').toggleClass('is-ready', ready);
            status.toggleClass('is-ready', ready).html(ready
                ? '<i class="mdi mdi-check-decagram"></i> Siap diproses'
                : '<i class="mdi mdi-progress-alert"></i> Belum lengkap');
            $('#prescriptionFlowReadiness')
                .toggleClass('is-ready', ready)
                .find('> span')
                .html(`<i class="mdi ${ready ? 'mdi-check-decagram' : 'mdi-progress-alert'}"></i>`);
            $('#prescriptionFlowReadinessText').text(message);
            $('#finishPrescriptionFlowBtn')
                .toggleClass('is-ready', ready)
                .attr('title', ready ? (isCompoundPrescription() ? 'Preview resep racikan' : 'Bayar transaksi resep') : message);
            $('#prescriptionToolbarPayBtn')
                .toggleClass('is-ready', ready)
                .attr('title', ready ? (isCompoundPrescription() ? 'Preview resep racikan' : 'Bayar transaksi resep') : message);
            $('#prescriptionModalItemCount').text(`${cart.length} ${isCompoundPrescription() ? 'komponen' : 'obat'}`);

            const flowSteps = $('.pos-prescription-flow-steps span');
            flowSteps.eq(0).toggleClass('is-active', headerComplete);
            flowSteps.eq(1).toggleClass('is-active', cart.length > 0 && completeItems === cart.length && unavailableItems === 0);
            flowSteps.eq(2).toggleClass('is-active', ready);
            updatePrescriptionPaymentHandoff();

            return {
                ready,
                message
            };
        }

        function transactionPayload(includePayments = true) {
            return {
                branch_id: activeBranchId || null,
                draft_id: $('#draftId').val() || null,
                tanggal_transaksi: $('#transactionDate').val(),
                jenis_transaksi: currentTransactionType(),
                customer_name: $('#customerName').val(),
                customer_phone: $('#customerPhone').val(),
                patient_id: $('#patientId').val() || null,
                nomor_resep: $('#nomorResep').val(),
                tanggal_resep: $('#tanggalResep').val(),
                dokter_name: $('#dokterName').val(),
                asal_resep: $('#asalResep').val(),
                instansi_name: $('#instansiName').val(),
                catatan: $('#catatanTransaksi').val(),
                diskon_transaksi_percent: toNumber($('#transactionDiscountPercent').val()),
                diskon_transaksi_nominal: toNumber($('#transactionDiscountNominal').val()),
                embalase: isCompoundPrescription() ? toNumber($('#embalase').val()) : 0,
                use_pajak: $('#useTax').is(':checked') ? 1 : 0,
                pajak_percent: toNumber($('#taxPercent').val()),
                details: cart.map(item => ({
                    obat_id: item.obat_id,
                    satuan_id: item.satuan_id,
                    qty: item.qty,
                    diskon_percent: item.diskon_percent,
                    diskon_nominal: item.diskon_nominal,
                    aturan_pakai: item.aturan_pakai || '',
                    waktu_konsumsi: item.waktu_konsumsi || '',
                    durasi_hari: item.durasi_hari || null,
                    racikan_group: item.racikan_group || '',
                    bentuk_racikan: item.bentuk_racikan || '',
                    jumlah_racikan: Number(item.jumlah_racikan) > 0 ? Number(item.jumlah_racikan) : null,
                    jumlah_ambil_resep: Number(item.jumlah_ambil_resep) > 0 ? Number(item.jumlah_ambil_resep) : null,
                    signa_1: item.signa_1 || '',
                    signa_2: item.signa_2 || '',
                    embalase_racikan: Math.max(0, Number(item.embalase_racikan) || 0),
                    dosis_komponen: item.dosis_komponen || '',
                    kekuatan_obat: item.kekuatan_obat || '',
                    jumlah_resep: Number(item.jumlah_resep) > 0 ? Number(item.jumlah_resep) : null,
                    keterangan: item.keterangan || ''
                })),
                payments: includePayments ? paymentRows() : []
            };
        }

        function validateCart(requireCompletePrescription = false) {
            if (!activePosBranch()) {
                openPosBranchModal();
                Swal.fire('Pilih cabang', 'Tentukan cabang aktif sebelum memproses transaksi.', 'warning');
                return false;
            }

            if (cart.length === 0) {
                Swal.fire('Keranjang kosong', 'Tambahkan minimal satu obat ke transaksi.', 'warning');
                return false;
            }

            const unavailable = cart.find(item => !item.is_available);

            if (unavailable) {
                Swal.fire('Stok tidak cukup', `${unavailable.nama_obat} perlu diperiksa ulang.`, 'warning');
                return false;
            }

            if (requireCompletePrescription && isPrescriptionTransaction()) {
                const missingHeader = [
                    ['Nama pasien', $('#customerName').val()],
                    ['Nomor resep', $('#nomorResep').val()],
                    ['Tanggal resep', $('#tanggalResep').val()],
                    ['Dokter penulis', $('#dokterName').val()]
                ].filter(([, value]) => !String(value || '').trim()).map(([label]) => label);

                if (missingHeader.length > 0) {
                    $('.pos-transaction-details').prop('open', true);
                    Swal.fire('Data resep belum lengkap', `Lengkapi: ${missingHeader.join(', ')}.`, 'warning');
                    return false;
                }

                const invalidItem = cart.find(item => !isPrescriptionItemComplete(item));

                if (isCompoundPrescription()) {
                    const unsavedGroup = compoundGroups().find(group => compoundGroupItems(group).length > 0 && !isCompoundGroupSaved(group));
                    if (unsavedGroup) {
                        Swal.fire(
                            'Racikan belum disimpan',
                            `Lengkapi lalu simpan ${compoundGroupLabel(unsavedGroup)} sebelum melanjutkan pembayaran.`,
                            'warning'
                        );
                        return false;
                    }
                }

                if (invalidItem) {
                    Swal.fire(
                        'Detail obat belum lengkap',
                        isCompoundPrescription()
                            ? `Lengkapi Dosis Resep dan Jumlah Resep untuk ${invalidItem.nama_obat}.`
                            : `Lengkapi aturan pakai untuk ${invalidItem.nama_obat}.`,
                        'warning'
                    );
                    return false;
                }
            }

            return true;
        }

        $('#saveDraftBtn').on('click', function() {
            if (!validateCart()) return;
            submitTransaction(urls.draft, transactionPayload(false), '#saveDraftBtn', 'Menyimpan draft...');
        });

        $('#completeTransactionBtn').on('click', function() {
            if (!validateCart(true)) return;

            if (isCompoundPrescription() && !compoundPreviewApproved) {
                setPrescriptionPaymentMode(false);
                openPrescriptionFlow({
                    chooseType: false,
                    returnType: currentTransactionType()
                });
                Swal.fire({
                    icon: 'info',
                    title: 'Preview racikan diperlukan',
                    text: 'Periksa kembali resep racikan dan setujui preview sebelum menyelesaikan pembayaran.',
                    confirmButtonText: 'Review resep'
                });
                return;
            }

            const totals = recalculateTotals();
            if (!isCreditTransaction() && totals.diff < -0.01) {
                Swal.fire('Pembayaran belum cukup', `Tambahkan pembayaran ${formatCurrency(Math.abs(totals.diff))} terlebih dahulu.`, 'warning');
                return;
            }

            submitTransaction(urls.complete, transactionPayload(true), '#completeTransactionBtn', 'Menyimpan transaksi...');
        });

        function submitTransaction(url, payload, buttonSelector, loadingText) {
            const button = $(buttonSelector);
            const normalHtml = button.html();
            button.prop('disabled', true).html(`<i class="mdi mdi-loading mdi-spin"></i> ${loadingText}`);

            $.ajax({
                url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });

                    const transaction = response.transaction || {};
                    $('#draftId').val(transaction.status === 'draft' ? transaction.id : '');
                    recalculateTotals();

                    if (transaction.status === 'completed') {
                        lastReceiptId = transaction.id;
                        lastReceiptCanPrintLabels = Boolean(transaction.can_print_labels);
                        $('#printLastReceiptBtn').prop('disabled', false);
                        newTransaction();
                        showReceiptModal(transaction.id, true, lastReceiptCanPrintLabels);
                        refreshCashierShift();
                    }

                },
                error: function(xhr) {
                    showAjaxError(xhr, 'Transaksi gagal disimpan.');
                },
                complete: function() {
                    button.html(normalHtml);
                    recalculateTotals();
                }
            });
        }

        function newTransaction(resetAlert = false) {
            $('#draftId').val('');
            $('#transactionDate').val(localDateTimeValue());
            $('#jenisTransaksi').val('penjualan_bebas');
            $('#jenisResep').val('penjualan_resep');
            committedTransactionType = 'penjualan_bebas';
            setPrescriptionPaymentMode(false);
            $('#customerName, #customerPhone, #nomorResep, #tanggalResep, #dokterName, #asalResep, #instansiName, #catatanTransaksi').val('');
            clearPatientSelection();
            $('#transactionDiscountPercent, #transactionDiscountNominal, #taxPercent, #embalase').val(0);
            $('#useTax').prop('checked', false).trigger('change');
            activeCompoundGroup = 'R/ 1';
            savedCompoundGroups = new Set();
            compoundPreviewApproved = false;
            selectedProduct = null;
            currentQuote = null;
            $('#productSearch').val(null).trigger('change');
            $('#unitSelect').empty().append('<option value="">Pilih obat dulu</option>').prop('disabled', true);
            $('#qtyInput').val(1);
            renderSelectedProduct();
            resetQuote();
            cart = [];
            renderCart();
            $('#paymentRows').empty();
            addPaymentRow('tunai');
            syncTransactionFields();
            setCashierStage('product', { focus: false });

            if (resetAlert) {
                Swal.fire({
                    icon: 'info',
                    title: 'Transaksi baru siap.',
                    toast: true,
                    position: 'top-end',
                    timer: 1600,
                    showConfirmButton: false
                });
            }
        }

        function requestNewTransaction() {
            if (cart.length === 0) {
                newTransaction(true);
                return;
            }

            Swal.fire({
                title: 'Mulai transaksi baru?',
                text: 'Item di keranjang saat ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, transaksi baru',
                cancelButtonText: 'Lanjutkan transaksi'
            }).then(result => {
                if (result.isConfirmed) {
                    newTransaction(true);
                }
            });
        }

        $('#newTransactionBtn').on('click', requestNewTransaction);
        $('#printLastReceiptBtn').on('click', function() {
            if (lastReceiptId) {
                printReceipt(lastReceiptId);
            }
        });

        function printReceipt(id) {
            showReceiptModal(id, false);
        }

        window.printReceipt = printReceipt;

        function loadDraft(transaction) {
            const draftBranch = posBranches.find(branch => Number(branch.id) === Number(transaction.branch_id));

            if (!draftBranch) {
                Swal.fire('Draft tidak dapat dilanjutkan', 'Cabang pada draft sudah tidak aktif atau tidak dapat diakses.', 'error');
                if (canSwitchPosBranch) openPosBranchModal();
                return;
            }

            activeBranchId = Number(draftBranch.id);
            updatePosBranchUi();
            posBranchModalInstance()?.hide();
            newTransaction(false);
            $('#draftId').val(transaction.id);
            $('#transactionDate').val((transaction.tanggal_transaksi || '').replace(' ', 'T'));
            if (isPrescriptionTransaction(transaction.jenis_transaksi)) {
                $('#jenisTransaksi').val('penjualan_resep');
                $('#jenisResep').val(transaction.jenis_transaksi);
            } else {
                $('#jenisTransaksi').val(transaction.jenis_transaksi);
            }
            committedTransactionType = transaction.jenis_transaksi;
            $('#customerName').val(transaction.customer_name || '');
            $('#customerPhone').val(transaction.customer_phone || '');
            showSelectedPatient(transaction.patient_id ? {
                id: transaction.patient_id,
                name: transaction.customer_name || '',
                phone: transaction.customer_phone || ''
            } : null);
            $('#nomorResep').val(transaction.nomor_resep || '');
            $('#tanggalResep').val(transaction.tanggal_resep || '');
            $('#dokterName').val(transaction.dokter_name || '');
            $('#asalResep').val(transaction.asal_resep || '');
            $('#instansiName').val(transaction.instansi_name || '');
            $('#catatanTransaksi').val(transaction.catatan || '');
            $('#transactionDiscountPercent').val(transaction.diskon_transaksi_percent || 0);
            $('#transactionDiscountNominal').val(transaction.diskon_transaksi_nominal || 0);
            $('#embalase').val(transaction.embalase || 0);
            $('#useTax').prop('checked', Number(transaction.pajak_percent) > 0);
            $('#taxPercent').prop('disabled', Number(transaction.pajak_percent) <= 0).val(transaction.pajak_percent || 0);
            syncTransactionFields();

            cart = (transaction.details || []).map(detail => ({
                uid: `draft-${detail.id}-${Math.random().toString(16).slice(2)}`,
                obat_id: detail.obat_id,
                kode_obat: detail.kode_obat,
                nama_obat: detail.nama_obat,
                satuan_id: detail.satuan_id,
                satuan: detail.satuan_jual,
                satuan_stok: detail.satuan_stok,
                konversi: Number(detail.konversi) || 1,
                units: (detail.units || []).map(unit => ({
                    satuan_id: Number(unit.satuan_id),
                    nama: unit.nama,
                    konversi: Number(unit.konversi) || 1,
                    is_default: Boolean(unit.is_default)
                })),
                qty: Number(detail.qty_jual) || 1,
                qty_stok: Number(detail.qty_stok) || 0,
                harga_jual: Number(detail.harga_jual) || 0,
                subtotal_gross: Number(detail.subtotal_gross) || 0,
                diskon_percent: Number(detail.diskon_percent) || 0,
                diskon_nominal: Number(detail.diskon_nominal) || 0,
                allocations: detail.batch_summary || [],
                is_available: true,
                aturan_pakai: detail.aturan_pakai || '',
                waktu_konsumsi: detail.waktu_konsumsi || '',
                durasi_hari: detail.durasi_hari || '',
                racikan_group: detail.racikan_group || '',
                bentuk_racikan: detail.bentuk_racikan || '',
                jumlah_racikan: detail.jumlah_racikan || '',
                jumlah_ambil_resep: detail.jumlah_ambil_resep || '',
                signa_1: detail.signa_1 || '',
                signa_2: detail.signa_2 || '',
                embalase_racikan: Number(detail.embalase_racikan) || 0,
                dosis_komponen: detail.dosis_komponen || '',
                kekuatan_obat: detail.kekuatan_obat || '',
                jumlah_resep: detail.jumlah_resep || detail.qty_jual || '',
                keterangan: detail.keterangan || ''
            }));

            const loadedGroups = [...new Set(cart
                .filter(item => String(item.racikan_group || '').trim())
                .map(item => normalizeCompoundGroup(item.racikan_group)))];
            if (isCompoundPrescription(transaction.jenis_transaksi) && loadedGroups.length > 0) {
                const firstGroup = loadedGroups[0];
                const firstReference = compoundGroupReference(firstGroup);
                if (firstReference && !(Number(firstReference.embalase_racikan) > 0) && Number(transaction.embalase) > 0) {
                    compoundGroupItems(firstGroup).forEach(item => {
                        item.embalase_racikan = Number(transaction.embalase);
                    });
                }
                loadedGroups.forEach(group => {
                    const reference = compoundGroupReference(group) || {};
                    const calculatedDays = automaticCompoundDays(reference);
                    const manuallyAdjustedDays = Number(reference.durasi_hari) > 0
                        && (!calculatedDays || Number(reference.durasi_hari) !== calculatedDays);
                    compoundGroupItems(group).forEach(item => {
                        item._durasi_hari_manual = manuallyAdjustedDays;
                    });
                    synchronizeCompoundGroup(group);
                    if (compoundGroupCheck(group).valid) savedCompoundGroups.add(group);
                });
            }

            activeCompoundGroup = loadedGroups[loadedGroups.length - 1] || 'R/ 1';

            renderCart();
            setCashierStage('cart', { focus: false });
            if (isPrescriptionTransaction(transaction.jenis_transaksi)) {
                window.setTimeout(() => openPrescriptionFlow({
                    chooseType: false,
                    returnType: transaction.jenis_transaksi
                }), 0);
            }
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function resumeDraft(id) {
            return $.get(urls.show.replace(':id', id), function(transaction) {
                loadDraft(transaction);
            }).fail(xhr => {
                showAjaxError(xhr, 'Draft gagal dimuat.');
                if (canSwitchPosBranch && !activePosBranch()) openPosBranchModal();
            });
        }

        function loadDraftFromQuery() {
            const draftId = new URLSearchParams(window.location.search).get('draft_id');

            if (!draftId) {
                return false;
            }

            resumeDraft(draftId);
            window.history.replaceState({}, document.title, window.location.pathname);

            return true;
        }

        window.resumeDraft = resumeDraft;

        function showAjaxError(xhr, fallback) {
            const errors = xhr.responseJSON?.errors || {};
            const firstError = Object.values(errors)[0]?.[0];
            Swal.fire('Gagal', firstError || xhr.responseJSON?.message || fallback, 'error');
        }

        $(document).on('keydown', function(event) {
            const target = event.target;
            const isTyping = $(target).is('input, textarea, select') || $(target).closest('.select2-container').length > 0;
            const prescriptionFlowOpen = $('#prescriptionTypeModal').hasClass('show');
            const prescriptionTypeSelectionOpen = prescriptionFlowOpen && !$('#prescriptionTypeStep').hasClass('d-none');
            const compoundPreviewOpen = prescriptionFlowOpen && !$('#compoundPreviewStep').hasClass('d-none');

            if ((event.key === 'F2' || ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k')) && !event.altKey) {
                event.preventDefault();
                if (prescriptionTypeSelectionOpen) {
                    return;
                }
                if (prescriptionPaymentMode && !prescriptionFlowOpen) {
                    $('#editPrescriptionBtn').trigger('click');
                    return;
                }
                setCashierStage('product');
                return;
            }

            if (isTyping) return;

            if (event.key === 'F8') {
                event.preventDefault();
                if (prescriptionFlowOpen) {
                    Swal.fire('Proses resep masih aktif', 'Selesaikan atau batalkan proses resep sebelum memulai transaksi baru.', 'info');
                    return;
                }
                requestNewTransaction();
            }

            if (event.key === 'F9') {
                event.preventDefault();
                if (!prescriptionFlowOpen && cashierStage !== 'payment') {
                    requestPaymentStage();
                    return;
                }
                $(compoundPreviewOpen
                    ? '#confirmCompoundPreviewBtn'
                    : (prescriptionFlowOpen ? '#finishPrescriptionFlowBtn' : '#completeTransactionBtn')).trigger('click');
            }
        });

        newTransaction(false);
        updatePosBranchUi();
        const loadingDraft = activePosBranch()?.cashier_shift ? loadDraftFromQuery() : false;

        if (canSwitchPosBranch && !loadingDraft) {
            openPosBranchModal();
        } else if (activePosBranch() && !activePosBranch().cashier_shift) {
            cashierShiftModalInstance()?.show();
        }
    });
</script>
