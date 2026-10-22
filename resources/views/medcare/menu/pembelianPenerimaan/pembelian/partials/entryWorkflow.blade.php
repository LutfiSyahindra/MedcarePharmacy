        let purchaseDirty = false;
        let purchaseAllowClose = false;
        let purchaseReviewShown = false;
        let purchaseReviewPending = false;
        let purchaseAddingItems = false;
        let purchaseEntrySession = 0;
        let purchaseSubmittedSignature = '';
        let purchaseGeneralErrors = [];
        let purchaseIssueCursor = -1;
        const purchaseMobileLayout = window.matchMedia('(max-width: 767.98px), (max-width: 991.98px) and (pointer: coarse)');

        function resetPurchaseToolsLayout() {
            $('#purchaseEntryTools').prop('open', !purchaseMobileLayout.matches);
            $('#purchaseAdditionalCosts').prop('open', !purchaseMobileLayout.matches);
        }

        resetPurchaseToolsLayout();
        purchaseMobileLayout.addEventListener('change', resetPurchaseToolsLayout);

        function closePurchaseAdjustments(activeRow = null) {
            if (!purchaseMobileLayout.matches) return;
            $('#detail-wrapper .detail-item').not(activeRow).find('.purchase-item-adjustments[open]').prop('open', false);
        }

        $('#detail-wrapper').on('focusin click select2:opening', '.detail-item', function() {
            closePurchaseAdjustments(this);
        });

        const purchaseScrollBody = document.querySelector('#pembelianModal > .modal-dialog > .modal-content > .modal-body');
        const purchaseSections = ['purchaseInformationSection', 'purchaseItemsSection', 'purchaseSummarySection'];
        const purchaseReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let purchaseNavigationFrame = null;

        function updatePurchaseNavigation() {
            if (!purchaseMobileLayout.matches || !$('#pembelianModal').hasClass('show')) return;
            const top = purchaseScrollBody.getBoundingClientRect().top + 48;
            let current = purchaseSections[0];
            purchaseSections.forEach(id => {
                if (document.getElementById(id).getBoundingClientRect().top <= top) current = id;
            });
            if (purchaseScrollBody.scrollTop > 0 && purchaseScrollBody.scrollTop + purchaseScrollBody.clientHeight >= purchaseScrollBody.scrollHeight - 4) {
                current = purchaseSections[2];
            }
            $('.purchase-section-link').each(function() {
                const active = this.dataset.purchaseSection === current;
                $(this).toggleClass('is-current', active).attr('aria-current', active ? 'step' : null);
            });
        }

        function navigatePurchaseSection(id) {
            if (purchaseSaveInProgress) return;
            const section = document.getElementById(id);
            if (!section || !purchaseSections.includes(id)) return;
            section.focus({ preventScroll: true });
            purchaseScrollBody.scrollTo({
                top: section.getBoundingClientRect().top - purchaseScrollBody.getBoundingClientRect().top + purchaseScrollBody.scrollTop - 12,
                behavior: purchaseReducedMotion.matches ? 'auto' : 'smooth'
            });
        }

        $('.purchase-section-link').on('click', function() { navigatePurchaseSection(this.dataset.purchaseSection); });
        $('#purchaseReviewTotal').on('click', function() { navigatePurchaseSection('purchaseSummarySection'); });
        purchaseScrollBody.addEventListener('scroll', function() {
            if (purchaseNavigationFrame !== null) return;
            purchaseNavigationFrame = requestAnimationFrame(function() {
                purchaseNavigationFrame = null;
                updatePurchaseNavigation();
            });
        }, { passive: true });
        $('#pembelianModal').on('shown.bs.modal', updatePurchaseNavigation);
        purchaseMobileLayout.addEventListener('change', updatePurchaseNavigation);

        function resetPurchaseEntry() {
            purchasePendingSave = null;
            purchaseLoadingDetails = false;
            purchaseDefaultTax = 11;
            $('#purchaseAllTax').val(11);
            $('#purchaseAllTaxStatus').text('');
            purchaseEntrySession++;
            purchaseDirty = false;
            purchaseAllowClose = false;
            purchaseReviewShown = false;
            purchaseReviewPending = false;
            purchaseAddingItems = false;
            purchaseIssueCursor = -1;
            purchaseGeneralErrors = [];
            $('#pembelianForm').prop('inert', false);
            $('.purchase-notes-details').prop('open', false);
            resetPurchaseToolsLayout();
            $('#purchaseFilterStatus').addClass('d-none');
            $('#purchaseDraftStatus').text('Belum disimpan');
            $('#purchaseItemSearch').val('');
            $('#purchaseIssuesOnly, #purchaseSelectVisible, #purchaseSelectVisibleMobile').prop('checked', false);
            $('#purchaseBulkToolbar, #purchaseValidationSummary').addClass('d-none');
        }

        function markPurchaseDirty() {
            purchaseDirty = true;
            $('#purchaseDraftStatus').text('Perubahan belum disimpan');
            schedulePurchaseRecovery();
        }

        function purchaseEntrySignature() {
            return JSON.stringify($('#pembelianForm').serializeArray().filter(field =>
                !['pembelian_id', '_token', 'total_estimasi', 'subtotal[]'].includes(field.name) &&
                !(field.name === 'no_po' && !selectedDistributorUsesManualPoNumber())
            ));
        }

        function markPurchaseSaved() {
            purchaseDirty = purchaseEntrySignature() !== purchaseSubmittedSignature;
            if (purchaseDirty) persistPurchaseRecovery();
            else clearPurchaseRecovery();
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
                if (purchaseReviewShown && invalid) $(this).closest('details').prop('open', true);
                if (invalid) issues.push(($('label[for="' + this.id + '"]').text().replace('*', '').trim() || this.name) + ' belum valid.');
            });
            return issues;
        }

        function refreshPurchaseEntryState() {
            const query = String($('#purchaseItemSearch').val() || '').trim().toLocaleLowerCase('id-ID');
            const issuesOnly = $('#purchaseIssuesOnly').prop('checked');
            $('#purchaseFilterStatus').toggleClass('d-none', !query && !issuesOnly);
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
            $('#purchaseSelectVisible, #purchaseSelectVisibleMobile').prop('checked', visibleChecks.length > 0 && selectedVisible === visibleChecks.length)
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
            closePurchaseAdjustments(row[0]);
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
                    const field = $('#pembelianForm .is-invalid').first();
                    if (field.length) {
                        field.closest('details').prop('open', true);
                        field[0].scrollIntoView({ block: 'center' });
                        if (field.hasClass('select2-hidden-accessible')) field.select2('open');
                        else field.trigger('focus');
                    } else {
                        $('#purchaseValidationSummary')[0].scrollIntoView({ block: 'center' });
                    }
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
                closePurchaseAdjustments(targets[0][0]);
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
        $('#purchaseSelectVisible, #purchaseSelectVisibleMobile').on('change', function() {
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
        $('#applyPurchaseAllTax').on('click', function() {
            const field = document.getElementById('purchaseAllTax');
            if (field.value === '' || !field.validity.valid) { field.reportValidity(); return; }
            purchaseDefaultTax = normalizedDiscount(field.value);
            const rows = $('#detail-wrapper .detail-item');
            rows.each(function() {
                const row = $(this);
                row.find('.purchase-tax').val(purchaseDefaultTax);
                row.removeData('server-errors');
                recalculatePurchaseRow(row);
            });
            markPurchaseDirty();
            hitungTotal(true);
            $('#purchaseAllTaxStatus').text('PPN ' + formatStockQuantity(purchaseDefaultTax) + '% diterapkan ke ' + rows.length + ' baris obat.');
        });
        $('#detail-wrapper').on('keydown', '.qty, .purchase-price-input, .purchase-discount, .purchase-tax', function(event) {
            if (event.key !== 'Enter' || event.isComposing) return;
            event.preventDefault();
            const rows = $('#detail-wrapper .detail-item').not('.d-none');
            const index = rows.index($(this).closest('.detail-item')) + (event.shiftKey ? -1 : 1);
            if (index < 0 || index >= rows.length) return;
            const target = $(this).hasClass('purchase-price-input') ? rows.eq(index).find('.purchase-price-input') : rows.eq(index).find('[name="' + this.name + '"]');
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
            if (purchaseSaveInProgress || purchaseAddingItems || purchaseConversionSaves.size > 0 || purchaseConversionContext) { event.preventDefault(); return; }
            if (!purchaseDirty && !purchasePendingSave) return;
            event.preventDefault();
            if (purchaseReviewPending) return;
            purchaseReviewPending = true;
            Swal.fire({
                icon: 'warning', title: 'Tutup tanpa menyimpan?', text: 'Ada perubahan yang belum disimpan. Gunakan Simpan sebagai Draft untuk melanjutkannya nanti.',
                showCancelButton: true, confirmButtonText: 'Tutup tanpa menyimpan', cancelButtonText: 'Tetap mengisi'
            }).then(result => {
                purchaseReviewPending = false;
                if (result.isConfirmed) { clearPurchaseRecovery(); purchaseAllowClose = true; $('#pembelianModal').modal('hide'); }
            });
        });
        $('#pembelianModal').on('hidden.bs.modal', function() { purchaseEntrySession++; });
        window.addEventListener('beforeunload', function(event) {
            if (purchaseDirty || purchasePendingSave) persistPurchaseRecovery();
            if ((purchaseDirty || purchasePendingSave) && $('#pembelianModal').hasClass('show')) { event.preventDefault(); event.returnValue = ''; }
        });
