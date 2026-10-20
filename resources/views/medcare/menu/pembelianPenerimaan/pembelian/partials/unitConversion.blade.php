        function renderPurchaseMedicineUnits(row, list, selectedId) {
            const medicine = medicineCatalogById.get(String(row.find('[name="obat_id[]"]').val() || ''));
            const select = row.find('.satuan-select');
            if (!list.length) {
                const unitName = medicine?.satuan?.nama || 'Satuan dasar';
                select.html(`<option value="0" data-konversi="1">${escapeHtml(unitName)} (konversi belum tersedia)</option>`)
                    .prop('disabled', false).val('0');
                return;
            }
            select.empty().append(new Option('-- Pilih Satuan --', ''));
            list.forEach(function(item) {
                $(new Option(medicineUnitLabel(item, medicine), String(item.id)))
                    .attr('data-konversi', item.konversi).appendTo(select);
            });
            const selected = list.find(item => String(item.id) === String(selectedId)) ||
                list.find(item => Number(item.is_default) === 1) || list[0];
            select.prop('disabled', false).val(String(selected.id));
        }

        function closePurchaseConversionModal() {
            const dialog = document.getElementById('purchaseConversionModal');
            purchaseConversionContext = null;
            if (dialog.open) dialog.close();
            updateUnitLoadingState();
        }

        function destroyPurchaseConversionSelects() {
            $('#purchaseConversionRows .purchase-conversion-unit.select2-hidden-accessible').each(function() {
                $(this).select2('destroy');
            });
        }

        function initializePurchaseConversionSelect(select) {
            select.select2({
                width: '100%',
                placeholder: 'Cari satuan beli...',
                minimumResultsForSearch: 0,
                dropdownParent: $('#purchaseConversionModal'),
                language: {
                    noResults: function() { return 'Satuan tidak ditemukan'; },
                    searching: function() { return 'Mencari satuan...'; }
                }
            }).on('select2:open', function() {
                $('#purchaseConversionModal .select2-search__field').attr('placeholder', 'Ketik nama satuan...').trigger('focus');
            });
        }

        function showPurchaseConversionError(message) {
            $('#purchaseConversionError').toggleClass('d-none', !message).text(message || '');
        }

        function updatePurchaseConversionRows() {
            const context = purchaseConversionContext;
            if (!context) return;
            const rows = $('#purchaseConversionRows .purchase-conversion-row');
            const selectedUnits = rows.find('.purchase-conversion-unit').map(function() { return String(this.value); }).get();
            rows.each(function(index) {
                const row = $(this);
                const select = row.find('.purchase-conversion-unit');
                const factor = row.find('.purchase-conversion-factor');
                const stockUnit = Boolean(select.val()) && String(select.val()) === String(context.medicine.satuan_id);
                const isUsed = Boolean(row.data('is-used'));
                select.prop('disabled', isUsed || purchaseConversionSaves.size > 0);
                factor.prop('readonly', stockUnit || isUsed);
                if (stockUnit) factor.val(1);
                row.find('.purchase-conversion-equation span').text(select.val()
                    ? `1 ${select.find('option:selected').text()} = ${formatStockQuantity(factor.val()) || '...'} ${context.medicine.satuan.nama}`
                    : 'Pilih satuan beli dan isi jumlah satuan stok di dalamnya.');
                row.find('.purchase-conversion-row-number').text(`Satuan ${String(index + 1).padStart(2, '0')}`);
                row.toggleClass('is-selected', row.find('.purchase-conversion-use').prop('checked'));
                const existing = row.data('conversion-id');
                const changed = existing && (String(select.val()) !== String(row.data('original-unit')) || Number(factor.val()) !== Number(row.data('original-factor')));
                row.find('.purchase-conversion-row-status').toggleClass('is-existing', Boolean(existing))
                    .text(existing ? (isUsed ? 'Dipakai PO' : changed ? 'Diubah' : 'Tersimpan') + (row.data('is-default') ? ' · Utama' : '') : 'Baru');
                row.find('.purchase-conversion-lock-note').toggleClass('d-none', !isUsed);
                select.find('option').each(function() {
                    $(this).prop('disabled', Boolean(this.value) && this.value !== String(select.val()) && selectedUnits.includes(this.value));
                });
                select.trigger('change.select2');
                row.find('.purchase-remove-conversion').toggleClass('d-none', Boolean(existing)).prop('disabled', rows.length === 1);
            });
            $('#purchaseConversionCount').text(`${rows.length} satuan`);
            $('#purchaseAddConversion').prop('disabled', rows.length >= 20 || rows.length >= context.units.length);
        }

        function addPurchaseConversionRow(conversion = null, focus = true) {
            const context = purchaseConversionContext;
            if (!context || !context.units?.length || $('#purchaseConversionRows .purchase-conversion-row').length >= Math.min(20, context.units.length)) return;
            const row = $(document.getElementById('purchaseConversionRowTemplate').content.firstElementChild.cloneNode(true));
            const rowId = ++purchaseConversionRowSequence;
            row.find('.purchase-conversion-unit').attr('id', `purchaseConversionUnit${rowId}`);
            row.find('.purchase-conversion-unit-field label').attr('for', `purchaseConversionUnit${rowId}`);
            row.find('.purchase-conversion-factor').attr('id', `purchaseConversionFactor${rowId}`);
            row.find('.purchase-conversion-factor-field label').attr('for', `purchaseConversionFactor${rowId}`);
            row.find('.purchase-conversion-use').val(rowId);
            row.find('.purchase-conversion-stock-label').text(context.medicine.satuan.nama);
            context.units.forEach(unit => row.find('.purchase-conversion-unit').append(new Option(unit.nama, String(unit.id))));
            if (conversion) {
                row.data('conversion-id', conversion.id).data('original-unit', conversion.satuan_id)
                    .data('original-factor', conversion.konversi).data('is-default', Number(conversion.is_default) === 1)
                    .data('is-used', Number(conversion.is_used) === 1);
                row.find('.purchase-conversion-unit').val(String(conversion.satuan_id));
                row.find('.purchase-conversion-factor').val(conversion.konversi);
                row.find('.purchase-conversion-use').prop('checked', String(conversion.id) === context.selectedConversionId);
            } else if (!$('#purchaseConversionRows .purchase-conversion-row').length) {
                row.find('.purchase-conversion-use').prop('checked', true);
                if (context.units.some(unit => String(unit.id) === String(context.medicine.satuan_id))) {
                    row.find('.purchase-conversion-unit').val(String(context.medicine.satuan_id));
                }
            }
            $('#purchaseConversionRows').append(row);
            initializePurchaseConversionSelect(row.find('.purchase-conversion-unit'));
            updatePurchaseConversionRows();
            if (focus) row.find('.purchase-conversion-unit').select2('open');
        }

        function getPurchaseConversionUnits() {
            if (!purchaseConversionUnitsRequest) {
                purchaseConversionUnitsRequest = new Promise(function(resolve, reject) {
                    $.ajax({
                        url: "{{ route('pembelian.getSatuan') }}", type: 'GET', dataType: 'json', timeout: 20000,
                        success: resolve,
                        error: function(xhr) { purchaseConversionUnitsRequest = null; reject(xhr); }
                    });
                });
            }
            return purchaseConversionUnitsRequest;
        }

        $(document).on('click', '.purchase-set-conversion', function() {
            if (purchaseSaveInProgress || purchaseAddingItems || purchaseConversionContext || purchaseConversionSaves.size > 0) return;
            const row = $(this).closest('.detail-item');
            const medicineId = String(row.find('[name="obat_id[]"]').val() || '');
            const medicine = medicineCatalogById.get(medicineId);
            const context = { row, medicineId, medicine, session: purchaseEntrySession, units: [], selectedConversionId: String(row.find('.satuan-select').val() || '') };
            purchaseConversionContext = context;
            $('#purchaseConversionMedicineCode').text(medicine?.kode_obat || 'Obat');
            $('#purchaseConversionMedicine').text(medicine?.nama_obat || '');
            $('#purchaseConversionStockUnit').text(medicine?.satuan?.nama || 'Belum tersedia');
            destroyPurchaseConversionSelects();
            $('#purchaseConversionRows').empty();
            $('#purchaseConversionCount').text('0 satuan');
            $('#purchaseConversionLoading').removeClass('d-none');
            $('#purchaseAddConversion, #purchaseSaveConversions').prop('disabled', true);
            showPurchaseConversionError('');
            document.getElementById('purchaseConversionModal').showModal();
            updateUnitLoadingState();

            if (!medicine?.satuan_id || !medicine?.satuan) {
                $('#purchaseConversionLoading').addClass('d-none');
                showPurchaseConversionError('Obat belum memiliki satuan stok. Lengkapi satuan stok obat terlebih dahulu.');
                return;
            }
            medicineUnitCache.delete(medicineId);
            Promise.all([getPurchaseConversionUnits(), getMedicineUnits(medicineId)]).then(function([units, conversions]) {
                if (purchaseConversionContext !== context || context.session !== purchaseEntrySession) return;
                const unitMap = new Map(units.map(unit => [String(unit.id), unit]));
                conversions.forEach(conversion => {
                    if (!unitMap.has(String(conversion.satuan_id))) {
                        unitMap.set(String(conversion.satuan_id), { id: conversion.satuan_id, nama: conversion.satuan?.nama || 'Satuan tidak aktif' });
                    }
                });
                context.units = Array.from(unitMap.values());
                if (!conversions.some(item => String(item.id) === context.selectedConversionId)) {
                    context.selectedConversionId = String((conversions.find(item => Number(item.is_default) === 1) || conversions[0])?.id || '');
                }
                $('#purchaseConversionLoading').addClass('d-none');
                if (!context.units.length) {
                    showPurchaseConversionError('Belum ada satuan aktif. Tambahkan satuan di Master Data Satuan terlebih dahulu.');
                    return;
                }
                conversions.forEach(conversion => addPurchaseConversionRow(conversion, false));
                if (!conversions.length) addPurchaseConversionRow(null, false);
                $('#purchaseSaveConversions').prop('disabled', false);
            }).catch(function() {
                if (purchaseConversionContext !== context) return;
                $('#purchaseConversionLoading').addClass('d-none');
                showPurchaseConversionError('Satuan gagal dimuat. Tutup modal lalu buka kembali untuk mencoba lagi.');
            });
        });

        $('.purchase-close-conversion').on('click', function() {
            if (purchaseConversionSaves.size === 0) closePurchaseConversionModal();
        });
        $('#purchaseConversionModal').on('cancel', function(event) {
            if (purchaseConversionSaves.size > 0) event.preventDefault();
        }).on('close', function() {
            if (!this.open) {
                purchaseConversionContext = null;
                destroyPurchaseConversionSelects();
            }
            updateUnitLoadingState();
        });
        $('#purchaseAddConversion').on('click', function() { addPurchaseConversionRow(); });
        $('#purchaseConversionRows').on('click', '.purchase-remove-conversion', function() {
            const row = $(this).closest('.purchase-conversion-row');
            if (row.data('conversion-id') || $('#purchaseConversionRows .purchase-conversion-row').length <= 1) return;
            const selected = row.find('.purchase-conversion-use').prop('checked');
            row.find('.purchase-conversion-unit').select2('destroy');
            row.remove();
            if (selected) $('#purchaseConversionRows .purchase-conversion-use').first().prop('checked', true);
            updatePurchaseConversionRows();
        }).on('input change', 'input, select', function() {
            $(this).removeClass('is-invalid');
            showPurchaseConversionError('');
            updatePurchaseConversionRows();
        });

        document.getElementById('purchaseConversionForm').addEventListener('invalid', function(event) {
            if (event.target.matches('.purchase-conversion-unit.select2-hidden-accessible')) {
                event.preventDefault();
                $(event.target).addClass('is-invalid');
                if (event.target === this.querySelector('select:invalid, input:invalid')) $(event.target).select2('open');
            }
        }, true);

        $('#purchaseConversionForm').on('submit', function(event) {
            event.preventDefault();
            const context = purchaseConversionContext;
            if (!context || purchaseSaveInProgress || purchaseConversionSaves.size > 0) return;
            if (!this.reportValidity()) return;
            const rows = $('#purchaseConversionRows .purchase-conversion-row');
            const conversions = rows.map(function() {
                const row = $(this);
                return {
                    ...(row.data('conversion-id') ? { id: row.data('conversion-id') } : {}),
                    satuan_id: row.find('.purchase-conversion-unit').val(),
                    konversi: row.find('.purchase-conversion-factor').val()
                };
            }).get();
            if (!conversions.length || new Set(conversions.map(item => item.satuan_id)).size !== conversions.length) {
                showPurchaseConversionError('Pilih satuan yang berbeda pada setiap baris konversi.');
                return;
            }
            const selectedUnitId = rows.find('.purchase-conversion-use:checked').closest('.purchase-conversion-row').find('.purchase-conversion-unit').val();
            const payload = { obat_id: context.medicineId, conversions, selected_satuan_id: selectedUnitId };
            purchaseConversionSaves.add(context.medicineId);
            showPurchaseConversionError('');
            $('#purchaseConversionModal input, #purchaseConversionModal select, #purchaseConversionModal button').prop('disabled', true);
            $('#purchaseConversionModal .purchase-conversion-unit').trigger('change.select2');
            $('#purchaseSaveConversions span').text('Menyimpan...');
            updateUnitLoadingState();

            $.ajax({
                url: "{{ route('pembelian.storeKonversiSatuan') }}", type: 'POST', dataType: 'json',
                contentType: 'application/json', data: JSON.stringify(payload), timeout: 20000,
                success: function(response) {
                    if (context.session !== purchaseEntrySession || purchaseConversionContext !== context) return;
                    const list = cacheMedicineUnits(context.medicineId, response.data);
                    $('#detail-wrapper .detail-item').each(function() {
                        const row = $(this);
                        if (String(row.find('[name="obat_id[]"]').val() || '') !== context.medicineId) return;
                        row.data('unit-request-id', ++unitRequestSequence);
                        row.data('satuan-terpilih', response.selected_id).removeData('server-errors');
                        renderPurchaseMedicineUnits(row, list, response.selected_id);
                        recalculatePurchaseRow(row);
                        row.find('.purchase-conversion-success').removeClass('d-none').text('Konversi tersimpan. Satuan pilihan sudah dipakai di PO.');
                    });
                    closePurchaseConversionModal();
                    markPurchaseDirty();
                    hitungTotal();
                },
                error: function(xhr) {
                    if (context.session !== purchaseEntrySession || purchaseConversionContext !== context) return;
                    const errors = xhr.responseJSON?.errors || {};
                    Object.keys(errors).forEach(function(key) {
                        const match = key.match(/^conversions\.(\d+)\.(id|satuan_id|konversi)$/);
                        if (match) rows.eq(Number(match[1])).find(match[2] === 'konversi' ? '.purchase-conversion-factor' : '.purchase-conversion-unit').addClass('is-invalid');
                    });
                    showPurchaseConversionError(Object.values(errors).flat().join(' ') || xhr.responseJSON?.message || 'Konversi gagal disimpan. Silakan coba kembali.');
                },
                complete: function() {
                    purchaseConversionSaves.delete(context.medicineId);
                    $('#purchaseSaveConversions span').text('Simpan Konversi');
                    $('#purchaseConversionModal input, #purchaseConversionModal select, #purchaseConversionModal button').prop('disabled', false);
                    $('#purchaseConversionModal .purchase-conversion-unit').trigger('change.select2');
                    if (purchaseConversionContext === context) updatePurchaseConversionRows();
                    updateUnitLoadingState();
                }
            });
        });
