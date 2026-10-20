        const purchaseEntryUserId = @json(auth()->id());
        const purchaseRecoveryStore = PurchaseOrderEntry.recoveryStore(
            () => window.sessionStorage,
            'medcare:po-recovery:' + purchaseEntryUserId + ':' + @json(\App\Support\BranchAccess::userBranchId())
        );
        let purchasePendingSave = null;
        let purchaseRecoveryTimer = null;
        let purchaseRecovering = false;
        let purchaseRecoveryOpening = false;
        let purchaseBackupAvailable = false;

        function clearPurchaseRecovery() {
            clearTimeout(purchaseRecoveryTimer);
            purchaseRecoveryStore.clear();
            purchaseBackupAvailable = false;
            $('#purchaseRecoveryNotice').addClass('d-none');
        }

        function schedulePurchaseRecovery() {
            if (purchaseRecovering) return;
            clearTimeout(purchaseRecoveryTimer);
            purchaseRecoveryTimer = setTimeout(persistPurchaseRecovery, 300);
        }

        function persistPurchaseRecovery() {
            clearTimeout(purchaseRecoveryTimer);
            if (purchaseRecovering || !$('#pembelianModal').hasClass('show')) return;
            const form = {};
            $('#pembelianForm [name]').each(function() {
                if (this.name !== '_token' && !this.name.endsWith('[]')) form[this.name] = $(this).val();
            });
            const rows = $('#detail-wrapper .detail-item').map(function() {
                const row = $(this);
                const fields = {};
                // Include disabled selections and incomplete/invalid values as entered.
                row.find('[name]').each(function() { fields[this.name.slice(0, -2)] = $(this).val(); });
                return { fields, priceInput: row.find('.purchase-price-input').val(),
                    medicineText: row.find('[name="obat_id[]"] option:selected').text() };
            }).get();
            purchaseBackupAvailable = purchaseRecoveryStore.write({ form, rows,
                pending: purchasePendingSave, defaultTax: purchaseDefaultTax, updatedAt: Date.now() });
        }

        function showPurchaseRecoveryNotice() {
            $('#purchaseRecoveryNotice').toggleClass('d-none', !purchaseRecoveryStore.read());
        }

        async function restorePurchaseRecovery() {
            const backup = purchaseRecoveryStore.read();
            if (!backup || purchaseRecovering || $('#pembelianModal').hasClass('show')) return;
            purchaseRecovering = true;
            $('#restorePurchaseRecovery').prop('disabled', true);
            try {
                await Promise.all([getMedicineCatalog(), Promise.resolve(purchaseDistributorsRequest)]);
                await preloadMedicineUnits(backup.rows.map(row => row.fields.obat_id).filter(Boolean));
                editMode = true; // Preserve the entered PO number while opening the form.
                purchaseRecoveryOpening = true;
                $('#pembelianModal').modal('show');
                purchaseRecoveryOpening = false;
                Object.entries(backup.form).forEach(([name, value]) => {
                    $('#pembelianForm [name]').filter(function() { return this.name === name; }).val(value);
                });
                DistributorSelect.trigger('change');
                editMode = Boolean(backup.form.pembelian_id);
                $('#pembelianModalLabel').text(editMode ? 'Edit Purchase Order' : 'Form Purchase Order');
                purchaseDefaultTax = backup.defaultTax ?? 11;
                $('#purchaseAllTax').val(purchaseDefaultTax);
                $('#detail-wrapper').empty();
                backup.rows.forEach(function(saved) {
                    const row = $(detailItemTemplate());
                    $('#detail-wrapper').append(row);
                    const medicineId = String(saved.fields.obat_id || '');
                    const medicine = medicineCatalogById.get(medicineId);
                    const select = row.find('[name="obat_id[]"]');
                    select.append(new Option('-- Pilih Obat --', ''));
                    if (medicineId) select.append(new Option(medicine ? medicineOptionText(medicine) : saved.medicineText, medicineId));
                    initializeMedicineSelect(select);
                    if (medicineId) renderPurchaseMedicineUnits(row, medicineUnitCache.get(medicineId) || [], saved.fields.satuan_id);
                    Object.entries(saved.fields).forEach(([name, value]) => {
                        row.find('[name]').filter(function() { return this.name === name + '[]'; }).val(value);
                    });
                    select.trigger('change.select2');
                    row.find('.purchase-item-adjustments').prop('open', true);
                    updateCurrentStock(row, medicine);
                    recalculatePurchaseRow(row);
                    row.find('.purchase-price-input').val(saved.priceInput);
                });
                purchasePendingSave = backup.pending || null;
                purchaseDirty = true;
                purchaseBackupAvailable = true;
                $('.purchase-notes-details').prop('open', Boolean(backup.form.catatan));
                $('#purchaseDraftStatus').text('Isian dipulihkan · belum disimpan');
                $('#purchaseRecoveryNotice').addClass('d-none');
                hitungTotal(true);
                updateUnitLoadingState();
            } catch (_) {
                editMode = false;
                Swal.fire({ icon: 'error', title: 'Isian belum dapat dipulihkan',
                    text: 'Data cadangan tetap tersedia. Periksa koneksi dan sesi login, lalu coba pulihkan kembali.' });
            } finally {
                purchaseRecovering = false;
                purchaseRecoveryOpening = false;
                $('#restorePurchaseRecovery').prop('disabled', false);
            }
        }

        function purchaseAjax(options) {
            return new Promise((resolve, reject) => $.ajax({ ...options, success: resolve, error: reject }));
        }

        function sendPendingPurchaseSave() {
            const attempt = purchasePendingSave;
            const send = () => purchaseAjax({
                url: attempt.url, method: attempt.method, dataType: 'json', timeout: 60000,
                contentType: 'application/json', data: JSON.stringify(attempt.payload), processData: false,
                headers: { Accept: 'application/json', 'Idempotency-Key': attempt.key,
                    'X-PO-User': String(purchaseEntryUserId),
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            return PurchaseOrderEntry.requestWithCsrfRecovery(send,
                () => purchaseAjax({ url: @json(route('session.csrf-token')), method: 'GET',
                    dataType: 'json', cache: false, timeout: 15000, headers: { Accept: 'application/json' } }),
                function(session) {
                    if (String(session.user_id) !== String(purchaseEntryUserId)) {
                        throw { status: 403, responseJSON: { message: 'Akun telah berubah. Masuk kembali dengan akun pembuat isian PO.' } };
                    }
                    if (!session.token) throw { status: 419 };
                    $('meta[name="csrf-token"]').attr('content', session.token);
                    $('input[name="_token"]').val(session.token);
                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': session.token } });
                });
        }

        $('#restorePurchaseRecovery').on('click', restorePurchaseRecovery);
        $('#discardPurchaseRecovery').on('click', function() {
            Swal.fire({ icon: 'warning', title: 'Hapus cadangan isian PO?',
                text: 'Isian yang belum tersimpan akan dihapus dari tab ini.', showCancelButton: true,
                confirmButtonText: 'Hapus cadangan', cancelButtonText: 'Batal'
            }).then(result => { if (result.isConfirmed) clearPurchaseRecovery(); });
        });
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden' && (purchaseDirty || purchasePendingSave)) persistPurchaseRecovery();
        });
