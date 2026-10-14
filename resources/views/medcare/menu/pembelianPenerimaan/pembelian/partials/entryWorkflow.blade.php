        let purchaseDirty = false;
        let purchaseAllowClose = false;
        let purchaseReviewShown = false;
        let purchaseReviewPending = false;
        let purchaseAddingItems = false;
        let purchaseEntrySession = 0;
        let purchaseSubmittedSignature = '';
        let purchasePasteEntries = [];
        let purchasePasteSequence = 0;
        let purchaseGeneralErrors = [];
        let purchaseIssueCursor = -1;

        function resetPurchaseEntry() {
            purchaseEntrySession++;
            purchaseDirty = false;
            purchaseAllowClose = false;
            purchaseReviewShown = false;
            purchaseReviewPending = false;
            purchaseAddingItems = false;
            purchaseIssueCursor = -1;
            purchaseGeneralErrors = [];
            purchasePasteEntries = [];
            purchasePasteSequence++;
            $('#pembelianForm').prop('inert', false);
            $('.purchase-notes-details').prop('open', false);
            $('#purchaseDraftStatus').text('Belum disimpan');
            $('#purchaseItemSearch, #purchasePasteInput').val('');
            $('#purchaseIssuesOnly, #purchaseSelectVisible').prop('checked', false);
            $('#purchasePastePanel, #purchaseBulkToolbar, #purchaseValidationSummary').addClass('d-none');
            $('#purchasePastePreview').empty();
            $('#applyPurchasePaste').prop('disabled', true);
            $('#togglePurchasePaste').attr('aria-expanded', 'false');
        }

        function markPurchaseDirty() {
            purchaseDirty = true;
            $('#purchaseDraftStatus').text('Perubahan belum disimpan');
        }

        function purchaseEntrySignature() {
            return JSON.stringify($('#pembelianForm').serializeArray().filter(field =>
                !['pembelian_id', '_token', 'total_estimasi', 'subtotal[]'].includes(field.name) &&
                !(field.name === 'no_po' && !selectedDistributorUsesManualPoNumber())
            ));
        }

        function markPurchaseSaved() {
            purchaseDirty = purchaseEntrySignature() !== purchaseSubmittedSignature;
            $('#purchaseDraftStatus').text(purchaseDirty ? 'Ada perubahan setelah penyimpanan' :
                'Tersimpan ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
        }

        function clearPurchaseFilters() {
            $('#purchaseItemSearch').val('');
            $('#purchaseIssuesOnly').prop('checked', false);
            $('#detail-wrapper .detail-item').removeClass('d-none');
        }

        function purchaseRowIssues(row, draft = false) {
            return PurchaseOrderEntry.rowIssues({
                medicineId: row.find('[name="obat_id[]"]').val(),
                unitId: row.find('.satuan-select').val(),
                unitLoading: row.find('.satuan-select').prop('disabled'),
                qty: row.find('.qty').val(),
                price: row.find('.harga_estimasi_satuan').val(),
                adjustments: row.find('.purchase-discount').map(function() { return this.value || '0'; }).get()
                    .concat([row.find('.purchase-tax').val()])
            }, draft).concat(row.data('server-errors') || []);
        }

        function purchaseHeaderIssues() {
            const issues = [];
            $('#pembelianForm').find('[name="distributor_id"], [name="tanggal"], [name="no_po"], .purchase-additional-cost').each(function() {
                if (this.name === 'no_po' && !selectedDistributorUsesManualPoNumber()) return;
                const invalid = !this.validity.valid || (this.required && !String(this.value).trim());
                $(this).toggleClass('is-invalid', purchaseReviewShown && invalid);
                if (invalid) issues.push(($('label[for="' + this.id + '"]').text().replace('*', '').trim() || this.name) + ' belum valid.');
            });
            return issues;
        }

        function refreshPurchaseEntryState() {
            const query = String($('#purchaseItemSearch').val() || '').trim().toLocaleLowerCase('id-ID');
            const issuesOnly = $('#purchaseIssuesOnly').prop('checked');
            const counts = new Map();
            const rows = $('#detail-wrapper .detail-item');
            rows.each(function() {
                const id = String($(this).find('[name="obat_id[]"]').val() || '');
                if (id) counts.set(id, (counts.get(id) || 0) + 1);
            });
            let issueCount = 0;
            let visibleCount = 0;
            rows.each(function() {
                const row = $(this);
                const id = String(row.find('[name="obat_id[]"]').val() || '');
                const medicine = medicineCatalogById.get(id);
                const issues = purchaseRowIssues(row);
                if (counts.get(id) > 1) issues.push('Obat berulang dalam PO.');
                row.data('purchase-issues', issues);
                if (issues.length) issueCount++;
                row.toggleClass('has-issues', purchaseReviewShown && issues.length > 0);
                row.find('.purchase-row-errors').text(purchaseReviewShown ? issues.join(' ') : '');
                const searchText = [medicine?.kode_obat, medicine?.nama_obat, row.find('[name="obat_id[]"] option:selected').text(), row.find('.satuan-select option:selected').text()].join(' ').toLocaleLowerCase('id-ID');
                const visible = (!query || searchText.includes(query)) && (!issuesOnly || issues.length > 0);
                row.toggleClass('d-none', !visible);
                if (visible) visibleCount++;
            });
            $('#purchaseReadyCount').text(rows.length - issueCount);
            $('#purchaseIssueCount').text(issueCount);
            $('#purchaseNoItemResults').toggleClass('d-none', visibleCount > 0);
            $('#purchaseNextIssue').prop('disabled', issueCount === 0);
            const headerIssues = purchaseHeaderIssues();
            const messages = purchaseGeneralErrors.concat(headerIssues);
            if (issueCount) messages.push(issueCount + ' item perlu diperiksa. Gunakan filter atau tombol Ke masalah berikutnya.');
            $('#purchaseValidationSummary').toggleClass('d-none', !purchaseReviewShown || messages.length === 0).text(messages.join(' '));
            updatePurchaseSelection();
        }

        function updatePurchaseSelection() {
            const rows = $('#detail-wrapper .detail-item');
            const selected = rows.find('.purchase-row-check:checked').length;
            const visibleChecks = rows.not('.d-none').find('.purchase-row-check');
            const selectedVisible = visibleChecks.filter(':checked').length;
            rows.each(function() { $(this).toggleClass('is-selected', $(this).find('.purchase-row-check').prop('checked')); });
            $('#purchaseBulkToolbar').toggleClass('d-none', selected === 0);
            $('#purchaseBulkCount').text(selected);
            $('#applyPurchaseBulk').text('Terapkan ke ' + selected + ' item');
            $('#purchaseSelectVisible').prop('checked', visibleChecks.length > 0 && selectedVisible === visibleChecks.length)
                .prop('indeterminate', selectedVisible > 0 && selectedVisible < visibleChecks.length)
                .prop('disabled', visibleChecks.length === 0);
        }

        function focusPurchaseIssue() {
            purchaseReviewShown = true;
            $('#purchaseItemSearch').val('');
            refreshPurchaseEntryState();
            const rows = $('#detail-wrapper .detail-item').filter(function() { return ($(this).data('purchase-issues') || []).length > 0; });
            if (!rows.length) return;
            purchaseIssueCursor = (purchaseIssueCursor + 1) % rows.length;
            const row = rows.eq(purchaseIssueCursor).removeClass('d-none');
            row[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
            row.find('details').prop('open', true);
            const field = row.find('input:not([type="hidden"]):not([type="checkbox"]), select').filter(function() { return !this.disabled && !this.validity.valid; }).first();
            if (field.is('[name="obat_id[]"]') && field.hasClass('select2-hidden-accessible')) field.select2('open');
            else if (field.length) field.trigger('focus');
            else row.find('.satuan-select').trigger('focus');
        }

        function validatePurchaseEntry(draft) {
            purchaseReviewShown = true;
            hitungTotal(true);
            const rows = $('#detail-wrapper .detail-item');
            const ids = rows.find('[name="obat_id[]"]').map(function() { return this.value; }).get();
            const hasDuplicates = new Set(ids.filter(Boolean)).size !== ids.filter(Boolean).length;
            const invalidRows = rows.filter(function() { return purchaseRowIssues($(this), draft).length > 0; });
            if (!rows.length || invalidRows.length || hasDuplicates || purchaseHeaderIssues().length || purchaseGeneralErrors.length) {
                if (invalidRows.length || hasDuplicates) {
                    purchaseIssueCursor = -1;
                    focusPurchaseIssue();
                } else {
                    $('#purchaseValidationSummary')[0].scrollIntoView({ block: 'center' });
                }
                return false;
            }
            return true;
        }

        function confirmPurchaseSubmission() {
            if (purchaseReviewPending) return;
            purchaseReviewPending = true;
            const session = purchaseEntrySession;
            Swal.fire({
                icon: 'question', title: 'Ajukan purchase order?',
                text: $('#detail-wrapper .detail-item').length + ' item · ' + $('#total_estimasi').text() + '. Pesanan akan masuk ke alur approval.',
                showCancelButton: true, confirmButtonText: 'Ajukan PO', cancelButtonText: 'Periksa lagi'
            }).then(result => {
                purchaseReviewPending = false;
                if (result.isConfirmed && session === purchaseEntrySession) submitPurchaseOrder(false, true);
            });
        }

        function showPurchaseValidationErrors(errors, fallback) {
            purchaseReviewShown = true;
            purchaseGeneralErrors = [];
            $('#detail-wrapper .detail-item').removeData('server-errors');
            Object.entries(errors).forEach(([key, values]) => {
                const messages = Array.isArray(values) ? values : [String(values)];
                const parts = key.split('.');
                const row = parts.length > 1 ? $('#detail-wrapper .detail-item').eq(Number(parts[1])) : $();
                if (row.length) row.data('server-errors', (row.data('server-errors') || []).concat(messages));
                else purchaseGeneralErrors.push(...messages);
            });
            if (!Object.keys(errors).length) purchaseGeneralErrors.push(fallback);
            clearPurchaseFilters();
            refreshPurchaseEntryState();
            if ($('#detail-wrapper .has-issues').length) { purchaseIssueCursor = -1; focusPurchaseIssue(); }
            else $('#purchaseValidationSummary')[0].scrollIntoView({ block: 'center' });
        }

        async function appendPurchaseEntries(entries) {
            if (purchaseAddingItems || purchaseSaveInProgress) return;
            const existing = currentDetailMedicineIds();
            entries = entries.filter(entry => !existing.has(String(entry.medicine.id)));
            if (!entries.length) return;
            const session = purchaseEntrySession;
            purchaseAddingItems = true;
            $('#pembelianForm').prop('inert', true);
            markPurchaseDirty();
            updateUnitLoadingState();
            try {
                await preloadMedicineUnits(entries.map(entry => entry.medicine.id)).catch(() => {});
                if (session !== purchaseEntrySession) return;
                clearPurchaseFilters();
                const emptyRows = $('#detail-wrapper .detail-item').filter(function() { return !$(this).find('[name="obat_id[]"]').val(); }).toArray();
                const fragment = document.createDocumentFragment();
                const targets = entries.map((entry, index) => {
                    const row = emptyRows[index] ? $(emptyRows[index]) : $(detailItemTemplate());
                    if (!emptyRows[index]) fragment.appendChild(row[0]);
                    row.find('.qty').val(entry.qty ?? 1);
                    row.find('.harga_estimasi_satuan').val(entry.price ?? entry.medicine.harga_beli ?? 0);
                    if (entry.unitId) row.data('satuan-terpilih', entry.unitId);
                    return row;
                });
                document.getElementById('detail-wrapper').appendChild(fragment);
                entries.forEach((entry, index) => populateMedicineSelect(targets[index].find('[name="obat_id[]"]'), entry.medicine.id, { preservePurchaseValues: true }));
                await waitForPendingUnitRequests();
                if (session !== purchaseEntrySession) return;
                hitungTotal(true);
                $('#pembelianForm').prop('inert', false);
                targets[0][0].scrollIntoView({ block: 'center' });
                targets[0].find('.qty').trigger('focus').trigger('select');
            } finally {
                if (session === purchaseEntrySession) {
                    purchaseAddingItems = false;
                    $('#pembelianForm').prop('inert', false);
                    updateUnitLoadingState();
                }
            }
        }

        function setPurchasePasteOpen(open) {
            $('#purchasePastePanel').toggleClass('d-none', !open);
            $('#togglePurchasePaste').attr('aria-expanded', String(open));
            if (open) { setMedicinePickerOpen(false); $('#purchasePasteInput').trigger('focus'); }
        }

        $('#togglePurchasePaste').on('click', () => setPurchasePasteOpen($('#purchasePastePanel').hasClass('d-none')));
        $('#closePurchasePaste').on('click', () => setPurchasePasteOpen(false));
        $('#purchasePasteInput').on('input', function() {
            purchasePasteSequence++;
            purchasePasteEntries = [];
            $('#applyPurchasePaste').prop('disabled', true);
            $('#purchasePastePreview').empty();
        });
        $('#previewPurchasePaste').on('click', async function() {
            const sequence = ++purchasePasteSequence;
            const session = purchaseEntrySession;
            purchasePasteEntries = [];
            $('#applyPurchasePaste').prop('disabled', true);
            $('#purchasePastePreview').text('Memeriksa kode dan satuan obat...');
            try {
                const catalog = await getMedicineCatalog();
                const rows = PurchaseOrderEntry.parsePaste($('#purchasePasteInput').val(), catalog, Array.from(currentDetailMedicineIds()));
                await preloadMedicineUnits(rows.filter(row => !row.errors.length).map(row => row.medicine.id));
                if (sequence !== purchasePasteSequence || session !== purchaseEntrySession) return;
                if (!rows.length) { $('#purchasePastePreview').text('Tempel sedikitnya satu baris data.'); return; }
                const valid = rows.every(row => !row.errors.length);
                const previewRows = rows.map(row => {
                    const unit = (medicineUnitCache.get(String(row.medicine?.id)) || [])[0];
                    row.unitId = unit?.id;
                    return '<tr><td>' + row.lineNumber + '</td><td>' + escapeHtml(row.code) + '</td><td>' + escapeHtml(row.medicine?.nama_obat || '—') + '</td><td>' + (Number.isFinite(row.qty) ? formatStockQuantity(row.qty) : '—') + '</td><td>' + escapeHtml(unit?.satuan?.nama || 'Konversi belum tersedia') + '</td><td>' + (Number.isFinite(row.price) ? formatRupiah(row.price) : '—') + '</td><td>' + escapeHtml(row.errors.join(' ') || (unit ? 'Siap dimasukkan' : 'Hanya draft sampai konversi lengkap')) + '</td></tr>';
                }).join('');
                $('#purchasePastePreview').html('<p>' + rows.length + ' baris · ' + rows.filter(row => row.errors.length).length + ' bermasalah</p><div class="purchase-paste-preview-wrap"><table class="table table-sm"><thead><tr><th>Baris</th><th>Kode</th><th>Obat</th><th>Qty</th><th>Satuan beli</th><th>Harga dasar</th><th>Hasil pemeriksaan</th></tr></thead><tbody>' + previewRows + '</tbody></table></div>');
                if (valid) purchasePasteEntries = rows;
                $('#applyPurchasePaste').prop('disabled', !valid).text('Masukkan ' + rows.length + ' item ke Rincian');
            } catch {
                if (sequence === purchasePasteSequence && session === purchaseEntrySession) $('#purchasePastePreview').text('Data obat atau satuan gagal dimuat. Klik Periksa Data untuk mencoba lagi.');
            }
        });
        $('#applyPurchasePaste').on('click', async function() {
            if (purchaseAddingItems || purchaseSaveInProgress) return;
            const entries = purchasePasteEntries;
            const existing = currentDetailMedicineIds();
            if (!entries.length || entries.some(entry => existing.has(String(entry.medicine.id)))) {
                $('#purchasePastePreview').text('Rincian PO berubah. Klik Periksa Data lagi agar tidak ada obat berulang.');
                $(this).prop('disabled', true);
                return;
            }
            $(this).prop('disabled', true);
            await appendPurchaseEntries(entries);
            setPurchasePasteOpen(false);
            $('#purchasePasteInput').val('');
            purchasePasteEntries = [];
        });

        $('#medicinePickerPrevious, #medicinePickerNext').on('click', function() {
            medicinePickerPage += this.id === 'medicinePickerNext' ? 1 : -1;
            medicinePickerPage = Math.max(0, medicinePickerPage);
            renderMedicinePicker();
            $('#medicinePicker .purchase-picker-table-wrap').scrollTop(0);
        });
        $('#purchaseItemSearch').on('input', refreshPurchaseEntryState);
        $('#purchaseIssuesOnly').on('change', function() { purchaseReviewShown = true; refreshPurchaseEntryState(); });
        $('#purchaseNextIssue').on('click', focusPurchaseIssue);
        $('#detail-wrapper').on('change', '.purchase-row-check', updatePurchaseSelection);
        $('#purchaseSelectVisible').on('change', function() {
            $('#detail-wrapper .detail-item').not('.d-none').find('.purchase-row-check').prop('checked', this.checked);
            updatePurchaseSelection();
        });
        $('#clearPurchaseSelection').on('click', function() {
            $('#detail-wrapper .purchase-row-check').prop('checked', false);
            updatePurchaseSelection();
        });
        $('#applyPurchaseBulk').on('click', function() {
            const value = document.getElementById('purchaseBulkValue');
            if (value.value === '' || !value.validity.valid) { value.reportValidity(); return; }
            const field = $('#purchaseBulkField').val();
            $('#detail-wrapper .purchase-row-check:checked').each(function() {
                const row = $(this).closest('.detail-item');
                row.find('[name="' + field + '[]"]').val(value.value);
                row.removeData('server-errors');
                recalculatePurchaseRow(row);
            });
            markPurchaseDirty();
            hitungTotal();
        });
        $('#detail-wrapper').on('keydown', '.qty, .harga_estimasi_satuan, .purchase-discount, .purchase-tax', function(event) {
            if (event.key !== 'Enter' || event.isComposing) return;
            event.preventDefault();
            const rows = $('#detail-wrapper .detail-item').not('.d-none');
            const index = rows.index($(this).closest('.detail-item')) + (event.shiftKey ? -1 : 1);
            if (index < 0 || index >= rows.length) return;
            const target = rows.eq(index).find('[name="' + this.name + '"]');
            target.closest('details').prop('open', true);
            target.trigger('focus').trigger('select');
        });
        $('#pembelianForm').on('input change select2:select select2:clear', 'input[name], select[name], textarea[name]', function(event) {
            if (!event.originalEvent && !event.type.startsWith('select2:')) return;
            markPurchaseDirty();
            purchaseGeneralErrors = [];
            $(this).removeClass('is-invalid').closest('.detail-item').removeData('server-errors');
            hitungTotal();
        });
        $('#pembelianModal').on('hide.bs.modal', function(event) {
            if (purchaseAllowClose) return;
            if (purchaseSaveInProgress || purchaseAddingItems) { event.preventDefault(); return; }
            if (!purchaseDirty) return;
            event.preventDefault();
            if (purchaseReviewPending) return;
            purchaseReviewPending = true;
            Swal.fire({
                icon: 'warning', title: 'Tutup tanpa menyimpan?', text: 'Ada perubahan yang belum disimpan. Gunakan Simpan sebagai Draft untuk melanjutkannya nanti.',
                showCancelButton: true, confirmButtonText: 'Tutup tanpa menyimpan', cancelButtonText: 'Tetap mengisi'
            }).then(result => {
                purchaseReviewPending = false;
                if (result.isConfirmed) { purchaseAllowClose = true; $('#pembelianModal').modal('hide'); }
            });
        });
        $('#pembelianModal').on('hidden.bs.modal', function() { purchaseEntrySession++; purchasePasteSequence++; });
        window.addEventListener('beforeunload', function(event) {
            if (purchaseDirty && $('#pembelianModal').hasClass('show')) { event.preventDefault(); event.returnValue = ''; }
        });
