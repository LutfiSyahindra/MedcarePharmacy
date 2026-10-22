<script src="{{ asset('assets/vendors/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/purchase-order-entry.js') }}?v={{ filemtime(public_path('assets/js/purchase-order-entry.js')) }}"></script>
<script>
    $(document).ready(function() {

        // ==== Inisiasi Variable Global dan Function ====
        // --- Variabel global
        let editMode = false;
        let detailPurchaseOrderId = null;
        let detailPrintDocuments = [];
        let unitRequestSequence = 0;
        const pendingUnitRequests = new Set();
        let medicineCatalog = null;
        let medicineCatalogRequest = null;
        let medicineCatalogById = new Map();
        const medicineUnitCache = new Map();
        const medicineUnitRequests = new Map();
        const purchaseConversionSaves = new Set();
        let purchaseConversionUnitsRequest = null;
        let purchaseConversionContext = null;
        let purchaseConversionRowSequence = 0;
        const selectedMedicineIds = new Set();
        const medicinePickerPageSize = 100;
        const medicineSelectPageSize = 50;
        let medicinePickerSearchTimer = null;
        let purchaseSaveInProgress = false;
        let currentPoNumberMode = null;
        let currentPoDistributorId = null;
        let poNumberRequestSequence = 0;
        let purchaseFieldSequence = 0;
        let medicinePickerPage = 0;
        let purchaseTotalFrame = null;
        let purchaseDefaultTax = 11;

        @include('medcare.menu.pembelianPenerimaan.pembelian.partials.entryWorkflow')
        @include('medcare.menu.pembelianPenerimaan.pembelian.partials.unitConversion')
        @include('medcare.menu.pembelianPenerimaan.pembelian.partials.entryRecovery')


        // --- Variabel select2
        let DistributorSelect = $('select[name="distributor_id"]');
        let PembelianSelect2Parent = $('#pembelianModal');

        // --- Inisialisasi flatpickr   
        flatpickr("#flatpickr-date", {
            wrap: true,
            dateFormat: "d-m-Y"
        });

        // --- Inisialisasi Select2
        $('.js-example-basic-single').select2({
            placeholder: "-- Pilih --",
            allowClear: false,
            width: 'resolve',
            dropdownParent: PembelianSelect2Parent
        });

        function selectedDistributorUsesManualPoNumber() {
            return String(DistributorSelect.find('option:selected').data('manual-po-number') || '0') === '1';
        }

        function generateAutomaticPurchaseOrderNumber() {
            const requestSequence = ++poNumberRequestSequence;
            const field = $('#pembelianForm input[name="no_po"]');

            field.val('').attr('placeholder', 'Sedang membuat nomor PO...');

            return Promise.resolve($.get('{{ route("pembelian.generateNoPO") }}'))
                .then(function(number) {
                    if (requestSequence === poNumberRequestSequence && !selectedDistributorUsesManualPoNumber()) {
                        field.val(number).attr('placeholder', 'Nomor dibuat otomatis');
                    }
                });
        }

        function updatePurchaseOrderNumberMode() {
            const field = $('#pembelianForm input[name="no_po"]');
            const hasDistributor = Boolean(DistributorSelect.val());
            const manual = hasDistributor && selectedDistributorUsesManualPoNumber();
            const nextMode = !hasDistributor ? null : (manual ? 'manual' : 'automatic');
            const nextDistributorId = hasDistributor ? String(DistributorSelect.val()) : null;
            const distributorChanged = currentPoDistributorId !== nextDistributorId;

            poNumberRequestSequence++;

            if (!hasDistributor) {
                field.prop('readonly', true).val('').attr('placeholder', 'Pilih distributor terlebih dahulu');
                $('#purchaseNumberModeTitle').text('Pilih Distributor');
                $('#purchaseNumberModeDescription').text('Mode nomor PO ditentukan oleh distributor.');
                $('#purchaseNumberHint').text('Nomor PO mengikuti pengaturan distributor.');
            } else if (manual) {
                field.prop('readonly', false).attr('placeholder', 'Ketik nomor PO dari distributor');
                $('#purchaseNumberModeTitle').text('Nomor PO Manual');
                $('#purchaseNumberModeDescription').text('Nomor PO diketik sesuai dokumen distributor.');
                $('#purchaseNumberHint').text('Wajib diisi manual dan harus berbeda dari nomor PO lain.');

                if (!editMode && (currentPoNumberMode !== 'manual' || distributorChanged)) {
                    field.val('');
                }
            } else {
                field.prop('readonly', true).attr('placeholder', 'Nomor dibuat otomatis');
                $('#purchaseNumberModeTitle').text('Nomor Otomatis');
                $('#purchaseNumberModeDescription').text('PO dibuat sesuai urutan bulan berjalan.');
                $('#purchaseNumberHint').text(editMode ? 'Nomor PO sudah tersimpan.' :
                    'Pratinjau nomor. Nomor akhir ditetapkan saat disimpan.');

                if (!editMode && (currentPoNumberMode !== 'automatic' || !String(field.val() || '').trim())) {
                    generateAutomaticPurchaseOrderNumber().catch(function() {
                        field.attr('placeholder', 'Nomor otomatis gagal dibuat');
                    });
                }
            }

            currentPoNumberMode = nextMode;
            currentPoDistributorId = nextDistributorId;
        }

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        function detailItemTemplate(options = {}) {
            const row = $(document.getElementById('purchaseDetailRowTemplate').content.firstElementChild.cloneNode(true));
            if (options.satuanTerpilih) row.attr('data-satuan-terpilih', options.satuanTerpilih);
            if (options.selectClass) row.find('[name="obat_id[]"]').addClass(options.selectClass);
            if (options.loadingOption) row.find('[name="obat_id[]"]').append(new Option('Memuat obat...', options.obatId || ''));
            const values = {
                qty: options.qty ?? 1, harga_estimasi_satuan: options.hargaSatuan ?? options.harga ?? 0,
                harga_estimasi: options.harga ?? 0, subtotal: options.subtotal ?? 0,
                diskon_1: options.diskon1 ?? 0, diskon_2: options.diskon2 ?? 0,
                diskon_3: options.diskon3 ?? 0, ppn: options.ppn ?? purchaseDefaultTax
            };
            Object.entries(values).forEach(([name, value]) => row.find('[name="' + name + '[]"]').attr('value', value));
            row.find('.purchase-price-input').attr('value', PurchaseOrderEntry.formatMoney(values.harga_estimasi_satuan));
            row.find('.purchase-subtotal-display').attr('value', PurchaseOrderEntry.formatMoney(values.subtotal));
            row.find('details').prop('open', [options.diskon1, options.diskon2, options.diskon3].some(value => Number(value) > 0) || Number(options.ppn ?? 11) !== 11);
            return row[0].outerHTML;
        }

        function refreshDetailNumbers() {
            const itemCount = $('#detail-wrapper .detail-item').length;
            $('#detail-wrapper .detail-item').each(function(index) {
                const row = $(this);
                row.find('.purchase-detail-number').text(index + 1);
                row.find('.purchase-row-check').attr('aria-label', 'Pilih baris obat ' + (index + 1));
                row.find('.remove-detail')
                    .prop('disabled', itemCount === 1)
                    .attr('aria-label', `Hapus obat baris ${index + 1}`);
                row.find('.form-label').each(function() {
                    const label = $(this);
                    const field = label.siblings('input:not([type="hidden"]), select').first();
                    // Select2 menyimpan ID select saat inisialisasi; pertahankan ID yang sudah ada.
                    if (!field.attr('id')) {
                        field.attr('id', `purchaseItemField${++purchaseFieldSequence}`);
                    }
                    label.attr('for', field.attr('id'));
                });
                updatePurchaseAdjustmentSummary(row);
            });
        }

        function updatePurchaseAdjustmentSummary(row) {
            const discounts = row.find('.purchase-discount').map(function() {
                return normalizedDiscount(this.value);
            }).get();
            const discountLabel = discounts.some(value => value > 0)
                ? `Diskon ${discounts.map(value => formatStockQuantity(value) + '%').join(' / ')}`
                : 'Tanpa diskon';
            const ppn = formatStockQuantity(normalizedDiscount(row.find('.purchase-tax').val()));

            row.find('.purchase-adjustments-value').text(`${discountLabel} · PPN ${ppn}%`);
        }

        // Buka panel sebelum browser memfokuskan kolom yang gagal validasi.
        document.getElementById('pembelianForm').addEventListener('invalid', function(event) {
            const adjustments = event.target.closest('.purchase-item-adjustments');
            if (adjustments) {
                adjustments.open = true;
            }
        }, true);

        function formatStockQuantity(value) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(Number(value) || 0);
        }

        function updateCurrentStock(row, medicine) {
            const stockElement = row.find('.purchase-current-stock');

            if (!medicine) {
                stockElement
                    .addClass('is-empty')
                    .removeClass('is-available is-empty-stock')
                    .html('<i class="mdi mdi-archive-outline"></i><span>Pilih obat untuk melihat stok saat ini</span>');
                return;
            }

            const stock = Number(medicine.stok_saat_ini) || 0;
            const unitName = medicine.satuan?.nama || 'satuan dasar';

            stockElement
                .removeClass('is-empty is-available is-empty-stock')
                .addClass(stock > 0 ? 'is-available' : 'is-empty-stock')
                .html(
                    `<i class="mdi ${stock > 0 ? 'mdi-package-variant-closed-check' : 'mdi-package-variant-closed-remove'}"></i>` +
                    `<span>Stok saat ini: <strong>${formatStockQuantity(stock)} ${escapeHtml(unitName)}</strong></span>`
                );
        }

        function updateUnitLoadingState() {
            const isLoading = pendingUnitRequests.size > 0 || purchaseConversionSaves.size > 0;
            let hasMissingConversion = false;
            const hasOpenConversion = purchaseConversionContext !== null;

            $('#detail-wrapper .detail-item').each(function() {
                const row = $(this);
                const unitSelect = row.find('.satuan-select');
                const missingConversion = Boolean(row.find('[name="obat_id[]"]').val()) &&
                    !unitSelect.prop('disabled') && !(Number(unitSelect.val()) > 0);

                row.find('.purchase-unit-warning').toggleClass('d-none', !missingConversion);
                row.find('.purchase-set-conversion')
                    .toggleClass('d-none', !row.find('[name="obat_id[]"]').val())
                    .prop('disabled', unitSelect.prop('disabled') || purchaseSaveInProgress || purchaseAddingItems || hasOpenConversion || purchaseConversionSaves.has(String(row.find('[name="obat_id[]"]').val())));
                hasMissingConversion = hasMissingConversion || missingConversion;
            });

            $('#submitForm').prop('disabled', isLoading || purchaseSaveInProgress || purchaseAddingItems || hasMissingConversion || hasOpenConversion);
            $('#saveDraftForm').prop('disabled', isLoading || purchaseSaveInProgress || purchaseAddingItems || hasOpenConversion);
            $('#addDetail, #addAnotherDetail, #toggleMedicinePicker, #addSelectedMedicines, #applyPurchaseBulk')
                .prop('disabled', purchaseSaveInProgress || purchaseAddingItems);
            $('#addSelectedMedicines').prop('disabled', purchaseSaveInProgress || purchaseAddingItems || selectedMedicineIds.size === 0);
            $('#purchaseUnitLoadingNotice').toggleClass('d-none', !isLoading);
            $('#purchaseConversionWarning').toggleClass('d-none', !hasMissingConversion);
        }

        function getMedicineCatalog() {
            if (Array.isArray(medicineCatalog)) {
                return Promise.resolve(medicineCatalog);
            }

            if (medicineCatalogRequest) {
                return medicineCatalogRequest;
            }

            medicineCatalogRequest = new Promise(function(resolve, reject) {
                $.ajax({
                    url: "{{ route("pembelian.getObat") }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const data = Array.isArray(response) ? response : (response.data || []);

                        medicineCatalog = data.filter(item => item && item.id);
                        medicineCatalogById = new Map(
                            medicineCatalog.map(item => [String(item.id), item])
                        );
                        resolve(medicineCatalog);
                    },
                    error: function(xhr) {
                        medicineCatalogRequest = null;
                        reject(xhr);
                    }
                });
            });

            return medicineCatalogRequest;
        }

        function currentDetailMedicineIds() {
            const ids = new Set();

            $('#detail-wrapper select[name="obat_id[]"]').each(function() {
                const value = String($(this).val() || '');

                if (value) {
                    ids.add(value);
                }
            });

            return ids;
        }

        function filteredMedicineCatalog() {
            const query = String($('#medicinePickerSearch').val() || '').trim().toLocaleLowerCase('id-ID');

            if (!query) {
                return medicineCatalog || [];
            }

            return (medicineCatalog || []).filter(function(item) {
                const searchableText = [
                    item.kode_obat,
                    item.nama_obat,
                    item.satuan?.nama
                ].filter(Boolean).join(' ').toLocaleLowerCase('id-ID');

                return searchableText.includes(query);
            });
        }

        function visibleMedicineCatalog() {
            const offset = medicinePickerPage * medicinePickerPageSize;
            return filteredMedicineCatalog().slice(offset, offset + medicinePickerPageSize);
        }

        function updateMedicinePickerSelectionState(filteredItems, existingIds) {
            const selectableIds = filteredItems
                .map(item => String(item.id))
                .filter(id => !existingIds.has(id));
            const selectedVisibleCount = selectableIds.filter(id => selectedMedicineIds.has(id)).length;
            const selectAll = $('#selectAllVisibleMedicines').get(0);

            $('#selectAllVisibleMedicines')
                .prop('disabled', selectableIds.length === 0)
                .prop('checked', selectableIds.length > 0 && selectedVisibleCount === selectableIds.length);

            if (selectAll) {
                selectAll.indeterminate = selectedVisibleCount > 0 && selectedVisibleCount < selectableIds.length;
            }

            $('#medicinePickerSelectedCount').text(selectedMedicineIds.size.toLocaleString('id-ID'));
            $('#addSelectedMedicines').prop('disabled', selectedMedicineIds.size === 0);
        }

        function renderMedicinePicker() {
            if (!Array.isArray(medicineCatalog)) {
                return;
            }

            const existingIds = currentDetailMedicineIds();
            const catalogIds = new Set(medicineCatalog.map(item => String(item.id)));

            Array.from(selectedMedicineIds).forEach(function(id) {
                if (existingIds.has(id) || !catalogIds.has(id)) {
                    selectedMedicineIds.delete(id);
                }
            });

            const matchingItems = filteredMedicineCatalog();
            medicinePickerPage = Math.min(medicinePickerPage, Math.max(0, Math.ceil(matchingItems.length / medicinePickerPageSize) - 1));
            const filteredItems = visibleMedicineCatalog();
            const startIndex = matchingItems.length ? medicinePickerPage * medicinePickerPageSize + 1 : 0;
            $('#medicinePickerResultCount').text(startIndex + '–' + (startIndex ? startIndex + filteredItems.length - 1 : 0) + ' dari ' + matchingItems.length + ' hasil');
            $('#medicinePickerPrevious').prop('disabled', medicinePickerPage === 0);
            $('#medicinePickerNext').prop('disabled', (medicinePickerPage + 1) * medicinePickerPageSize >= matchingItems.length);

            if (filteredItems.length === 0) {
                $('#medicinePickerList').html(`
                    <tr>
                        <td colspan="7" class="purchase-picker-state">
                            <i class="mdi mdi-magnify-close"></i>
                            Tidak ada obat yang sesuai dengan pencarian.
                        </td>
                    </tr>
                `);
                updateMedicinePickerSelectionState(filteredItems, existingIds);
                return;
            }

            const rows = filteredItems.map(function(item) {
                const id = String(item.id);
                const isAlreadyAdded = existingIds.has(id);
                const isChecked = selectedMedicineIds.has(id);
                const medicineName = item.nama_obat || 'Tanpa Nama';
                const medicineCode = item.kode_obat || '-';
                const unitName = item.satuan?.nama || 'Satuan dasar';

                return `
                    <tr class="${isAlreadyAdded ? 'is-added' : ''}">
                        <td class="purchase-picker-check-cell" data-label="Pilih">
                            <label class="purchase-picker-choice">
                                <input type="checkbox" class="form-check-input medicine-picker-checkbox"
                                    value="${escapeHtml(id)}"
                                    aria-label="Pilih ${escapeHtml(medicineName)}"
                                    ${isChecked ? 'checked' : ''}
                                    ${isAlreadyAdded ? 'disabled' : ''}>
                            </label>
                        </td>
                        <td data-label="Kode"><span class="purchase-picker-code">${escapeHtml(medicineCode)}</span></td>
                        <td data-label="Nama Obat">
                            <strong class="purchase-picker-name">${escapeHtml(medicineName)}</strong>
                        </td>
                        <td data-label="Satuan Dasar">${escapeHtml(unitName)}</td>
                        <td class="text-end purchase-picker-stock" data-label="Stok Saat Ini">${formatStockQuantity(item.stok_saat_ini)} ${escapeHtml(unitName)}</td>
                        <td class="text-end purchase-picker-price" data-label="Harga Beli">${formatRupiah(item.harga_beli || 0)}</td>
                        <td data-label="Status">
                            ${isAlreadyAdded
                                ? '<span class="purchase-picker-status is-added"><i class="mdi mdi-check"></i> Sudah masuk</span>'
                                : '<span class="purchase-picker-status"><i class="mdi mdi-plus"></i> Bisa dipilih</span>'}
                        </td>
                    </tr>
                `;
            }).join('');

            $('#medicinePickerList').html(rows);
            updateMedicinePickerSelectionState(filteredItems, existingIds);
        }

        function setMedicinePickerOpen(isOpen) {
            $('#medicinePicker')
                .toggleClass('d-none', !isOpen)
                .attr('aria-hidden', isOpen ? 'false' : 'true');
            $('#toggleMedicinePicker')
                .attr('aria-expanded', isOpen ? 'true' : 'false')
                .toggleClass('is-active', isOpen);

            if (!isOpen) {
                return;
            }

            $('#medicinePickerList').html(`
                <tr>
                    <td colspan="7" class="purchase-picker-state">
                        <i class="mdi mdi-loading mdi-spin"></i> Memuat daftar obat...
                    </td>
                </tr>
            `);

            getMedicineCatalog()
                .then(function() {
                    renderMedicinePicker();
                    $('#medicinePickerSearch').trigger('focus');
                })
                .catch(function() {
                    $('#medicinePickerResultCount').text('Daftar obat gagal dimuat');
                    $('#medicinePickerList').html(`
                        <tr>
                            <td colspan="7" class="purchase-picker-state is-error">
                                <i class="mdi mdi-alert-circle-outline"></i>
                                Gagal memuat daftar obat. Tutup lalu buka kembali untuk mencoba lagi.
                            </td>
                        </tr>
                    `);
                });
        }

        function resetMedicinePicker() {
            selectedMedicineIds.clear();
            medicinePickerPage = 0;
            $('#medicinePickerSearch').val('');
            setMedicinePickerOpen(false);

            if (Array.isArray(medicineCatalog)) {
                renderMedicinePicker();
            }
        }

        $(document).on('select2:opening', '#pembelianModal select[name="obat_id[]"]', function() {
            let modalBody = $('#pembelianModal .modal-body');
            let row = $(this).closest('.detail-item');

            if (!modalBody.length || !row.length) {
                return;
            }

            row.get(0).scrollIntoView({ block: 'nearest' });
        });
        // ==== End Inisiasi Variable Global dan Function ====

        // =================== Inisiasi Modal ===================
        // --- Reset modal ketika dibuka
        $('#pembelianModal').on('show.bs.modal', function(event) {
            if (purchaseRecovering && !purchaseRecoveryOpening) {
                event.preventDefault();
                return;
            }
            if (!purchaseRecovering && !editMode && purchaseRecoveryStore.read()) {
                event.preventDefault();
                restorePurchaseRecovery();
                return;
            }
            let form = $('#pembelianForm');
            purchaseSaveInProgress = false;
            resetPurchaseEntry();
            medicineUnitCache.clear();
            $('#pembelianModalLabel').text('Form Purchase Order');
            form.trigger('reset');

            $('#submitForm').html('<i class="mdi mdi-check-all" aria-hidden="true"></i><span class="purchase-desktop-text">Periksa &amp; Ajukan</span><span class="purchase-mobile-text">Ajukan PO</span>');
            $('#saveDraftForm').html('<i class="mdi mdi-content-save-move-outline" aria-hidden="true"></i><span class="purchase-desktop-text">Simpan sebagai Draft</span><span class="purchase-mobile-text">Simpan Draft</span>');
            $('#total_estimasi').text(formatRupiah(0));
            $('#total_estimasi_input').val(0);
            $('#pembelian_id').val('');
            currentPoNumberMode = null;
            currentPoDistributorId = null;
            DistributorSelect.val('').trigger('change');
            updateUnitLoadingState();
            resetMedicinePicker();

            // Reset error state
            form.find('.invalid-feedback').text('');
            form.find('.form-control, .form-select').removeClass('is-invalid');

            // Reset detail-wrapper jadi hanya 1 baris kosong
            $('#detail-wrapper').html(detailItemTemplate());
            hitungTotal();

            $(this).one('shown.bs.modal', function() {
                $('#pembelianModal .modal-body').scrollTop(0);
                if (!editMode) {
                    updatePurchaseOrderNumberMode();
                }
            });

            let $obatSelects = $('#detail-wrapper').find('select[name="obat_id[]"]');
            loadObatInto($obatSelects);
        });

        // --- Reset state modal setelah benar-benar ditutup
        $('#pembelianModal').on('hidden.bs.modal', function() {
            editMode = false;
            $('#pembelian_id').val('');
            resetMedicinePicker();
            purchaseSaveInProgress = false;
            updateUnitLoadingState();
        });
        // =================== End Inisiasi Modal ===============

        $('#pembelianModalDetail').on('hidden.bs.modal', function() {
            detailPurchaseOrderId = null;
            detailPrintDocuments = [];
            $('#btnPrintPDF')
                .prop('disabled', true)
                .removeClass('btn-outline-info btn-outline-primary btn-outline-warning btn-outline-success btn-outline-dark')
                .addClass('btn-outline-danger')
                .attr('title', 'Cetak surat pesanan sesuai jenis SP dalam PO')
                .find('span').text('Cetak Surat Pesanan');
        });

        $('#btnPrintPDF').on('click', function() {
            if (!detailPurchaseOrderId || $(this).prop('disabled') || detailPrintDocuments.length < 1) {
                return;
            }

            showSuratPesananPrintOptions({
                target: '#pembelianModalDetail',
                documents: detailPrintDocuments.map(document => ({
                    url: document.url,
                    name: `${document.name} (${document.count} item)`
                }))
            });
        });

        // =================== Inisiasi Event Handler ===================
        $('#toggleMedicinePicker').on('click', function() {
            setMedicinePickerOpen($('#medicinePicker').hasClass('d-none'));
        });

        $('#closeMedicinePicker').on('click', function() {
            setMedicinePickerOpen(false);
        });

        $('#medicinePickerSearch').on('input', function() {
            $('#clearMedicinePickerSearch').toggleClass('is-visible', $(this).val().length > 0);
            medicinePickerPage = 0;
            window.clearTimeout(medicinePickerSearchTimer);
            medicinePickerSearchTimer = window.setTimeout(renderMedicinePicker, 120);
        });

        $('#clearMedicinePickerSearch').on('click', function() {
            $('#medicinePickerSearch').val('').trigger('input').trigger('focus');
        });

        $(document).on('change', '.medicine-picker-checkbox', function() {
            const medicineId = String($(this).val());

            if ($(this).prop('checked')) {
                selectedMedicineIds.add(medicineId);
            } else {
                selectedMedicineIds.delete(medicineId);
            }

            updateMedicinePickerSelectionState(visibleMedicineCatalog(), currentDetailMedicineIds());
        });

        $('#selectAllVisibleMedicines').on('change', function() {
            const shouldSelect = $(this).prop('checked');
            const existingIds = currentDetailMedicineIds();

            visibleMedicineCatalog().forEach(function(item) {
                const medicineId = String(item.id);

                if (existingIds.has(medicineId)) {
                    return;
                }

                if (shouldSelect) {
                    selectedMedicineIds.add(medicineId);
                } else {
                    selectedMedicineIds.delete(medicineId);
                }
            });

            renderMedicinePicker();
        });

        $('#addSelectedMedicines').on('click', function() {
            if (!Array.isArray(medicineCatalog) || selectedMedicineIds.size === 0) {
                return;
            }

            const existingIds = currentDetailMedicineIds();
            const selectedItems = Array.from(selectedMedicineIds)
                .filter(id => !existingIds.has(id))
                .map(id => medicineCatalogById.get(id))
                .filter(Boolean);

            if (selectedItems.length === 0) {
                selectedMedicineIds.clear();
                renderMedicinePicker();
                return;
            }

            setMedicinePickerOpen(false);
            selectedMedicineIds.clear();
            appendPurchaseEntries(selectedItems.map(medicine => ({ medicine })));
        });

        // --- Tambah baris detail obat baru
        $(document).on('click', '#addDetail', function() {
            closePurchaseAdjustments();
            clearPurchaseFilters();
            markPurchaseDirty();
            let newDetail = detailItemTemplate();
            $('#detail-wrapper').append(newDetail);
            refreshDetailNumbers();
            hitungTotal();
            // 3. Ambil select obat di BARIS BARU saja
            let $newSelect = $('#detail-wrapper .detail-item').last().find('select[name="obat_id[]"]');

            // 4. Jalankan load obat + Select2 hanya untuk select BARU
            loadObatInto($newSelect);
            $newSelect.closest('.detail-item').get(0).scrollIntoView({ block: 'nearest' });
        });

        $('#addAnotherDetail').on('click', function() {
            $('#addDetail').trigger('click');
        });

        // --- Hapus baris detail obat
        $(document).on('click', '.remove-detail', function() {
            if ($('#detail-wrapper .detail-item').length > 1) {
                $(this).closest('.detail-item').remove();
                markPurchaseDirty();
                refreshDetailNumbers();
                hitungTotal();
                renderMedicinePicker();
            }
        });

        function normalizedDiscount(value) {
            return Math.round(Math.min(100, Math.max(0, Number(value) || 0)) * 100) / 100;
        }

        function selectedPurchaseConversion(row) {
            const conversion = parseFloat(row.find('.satuan-select option:selected').data('konversi'));

            return Number.isFinite(conversion) && conversion > 0 ? conversion : 1;
        }

        function recalculatePurchaseRow(row) {
            const qty = parseFloat(row.find('[name="qty[]"]').val()) || 0;
            const pricePerUnit = parseFloat(row.find('.harga_estimasi_satuan').val()) || 0;
            const conversion = selectedPurchaseConversion(row);
            const discounts = [
                normalizedDiscount(row.find('[name="diskon_1[]"]').val()),
                normalizedDiscount(row.find('[name="diskon_2[]"]').val()),
                normalizedDiscount(row.find('[name="diskon_3[]"]').val())
            ];
            const ppn = normalizedDiscount(row.find('[name="ppn[]"]').val());
            const amounts = PurchaseOrderEntry.calculateAmounts({ qty, price: pricePerUnit, conversion, discounts, tax: ppn });
            const purchaseUnitPrice = amounts.unitPrice;

            // Backend dan modul penerimaan tetap menerima harga per satuan beli.
            row.find('.harga_estimasi').val(purchaseUnitPrice.toFixed(2));
            row.find('.subtotal').val(amounts.subtotal.toFixed(2));
            row.find('.purchase-subtotal-display').val(PurchaseOrderEntry.formatMoney(amounts.subtotal));
            const priceInput = row.find('.purchase-price-input')[0];
            if (document.activeElement !== priceInput) {
                priceInput.value = row.find('.harga_estimasi_satuan').val() === '' ? '' : PurchaseOrderEntry.formatMoney(pricePerUnit);
            }
            priceInput.setCustomValidity(row.find('.harga_estimasi_satuan').val() === '' ? 'Masukkan harga Rupiah yang valid, misalnya Rp 1.500,50.' : '');
            const medicine = medicineCatalogById.get(String(row.find('[name="obat_id[]"]').val() || ''));
            const unitId = String(row.find('.satuan-select').val() || '');
            const unitText = row.find('.satuan-select option:selected').text().split(' (')[0];
            row.find('.purchase-unit-price').text(medicine && Number(unitId) > 0
                ? formatRupiah(purchaseUnitPrice) + ' / ' + unitText + ' · dasar: ' + (medicine.satuan?.nama || 'satuan dasar')
                : medicine && unitId === '0' ? 'Harga memakai satuan dasar.' : 'Pilih satuan beli');
            updatePurchaseAdjustmentSummary(row);
        }

        $('#detail-wrapper').on('input', '.purchase-price-input', function() {
            const value = PurchaseOrderEntry.moneyInput(this.value, this.selectionStart);
            this.value = value.display;
            this.setSelectionRange(value.caret, value.caret);
            const row = $(this).closest('.detail-item');
            row.find('.harga_estimasi_satuan').val(value.amount);
            row.removeData('server-errors');
            $(this).removeClass('is-invalid');
            purchaseGeneralErrors = [];
            markPurchaseDirty();
            recalculatePurchaseRow(row);
            hitungTotal();
        }).on('blur', '.purchase-price-input', function() {
            const amount = $(this).closest('.detail-item').find('.harga_estimasi_satuan').val();
            if (amount !== '') this.value = PurchaseOrderEntry.formatMoney(amount);
        });

        // --- Hitung subtotal berdasarkan qty, harga satuan beli, Diskon 1–3, lalu PPN
        $(document).on('input', '.harga_estimasi_satuan, [name="qty[]"], .purchase-discount, .purchase-tax, .purchase-additional-cost', function() {
            const row = $(this).closest('.detail-item');

            if (row.length) {
                recalculatePurchaseRow(row);
            }

            hitungTotal();
        });

        // --- Fungsi hitung total estimasi keseluruhan
        function hitungTotal(immediate = false) {
            if (immediate) {
                if (purchaseTotalFrame !== null) cancelAnimationFrame(purchaseTotalFrame);
                purchaseTotalFrame = null;
                renderPurchaseTotals();
                return;
            }
            if (purchaseTotalFrame !== null) return;
            purchaseTotalFrame = requestAnimationFrame(function() {
                purchaseTotalFrame = null;
                renderPurchaseTotals();
            });
        }

        function renderPurchaseTotals() {
            const amounts = $('#detail-wrapper .detail-item').map(function() {
                const row = $(this);
                return PurchaseOrderEntry.calculateAmounts({
                    qty: row.find('.qty').val(), price: row.find('.harga_estimasi').val(),
                    discounts: row.find('.purchase-discount').map(function() { return this.value; }).get(),
                    tax: row.find('.purchase-tax').val()
                });
            }).get();
            let insuranceCost = parseFloat($('[name="biaya_asuransi"]').val()) || 0;
            let shippingCost = parseFloat($('[name="biaya_pengiriman"]').val()) || 0;
            let additionalCost = insuranceCost + shippingCost;
            const summary = PurchaseOrderEntry.summarizeAmounts(amounts, additionalCost);
            let total = summary.total;
            let itemCount = $('#detail-wrapper .detail-item').length;

            refreshDetailNumbers();
            $('#total_estimasi, #purchaseMobileTotal').text(formatRupiah(total));
            $('#total_estimasi_input').val(total.toFixed(2));
            $('#purchaseModalLineCount, #purchaseModalItemCount').text(itemCount.toLocaleString('id-ID'));
            $('#purchaseModalGrossSubtotal').text(formatRupiah(summary.gross));
            $('#purchaseModalDiscountSubtotal').text((summary.discount > 0 ? '− ' : '') + formatRupiah(summary.discount));
            $('#purchaseModalTaxSubtotal').text(formatRupiah(summary.tax));
            $('#purchaseModalMedicineSubtotal').text(formatRupiah(summary.subtotal));
            $('#purchaseModalAdditionalCost').text(formatRupiah(additionalCost));
            $('#purchaseAdditionalCostsValue').text(additionalCost > 0 ? formatRupiah(additionalCost) : 'Opsional');
            $('#purchaseFooterItemCount').text($('#detail-wrapper [name="obat_id[]"]').filter(function() { return Boolean(this.value); }).length.toLocaleString('id-ID'));
            updateUnitLoadingState();
            refreshPurchaseEntryState();
        }

        // --- Get data distributor
        DistributorSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        const purchaseDistributorsRequest = $.ajax({
            url: "{{ route("pembelian.getDistributor") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                DistributorSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Distributor --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    DistributorSelect.append(
                        '<option value="">Tidak ada Distributor tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    $(option).attr('data-manual-po-number', item.uses_manual_po_number ? '1' : '0');
                    DistributorSelect.append(option);
                });

                DistributorSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Distributor:', xhr);
                DistributorSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        DistributorSelect.on('change', updatePurchaseOrderNumberMode);

        // --- Get data obat. Setiap baris hanya menyimpan option yang terpilih;
        // hasil pencarian Select2 dibaca dari satu katalog bersama agar DOM tetap ringan.
        function medicineOptionText(item) {
            return (item?.nama_obat || 'Tanpa Nama') +
                (item?.kode_obat ? ` (${item.kode_obat})` : '');
        }

        function initializeMedicineSelect($select) {
            $select.select2({
                placeholder: "-- Pilih Obat --",
                width: 'resolve',
                dropdownParent: PembelianSelect2Parent,
                ajax: {
                    delay: 120,
                    transport: function(params, success) {
                        const query = String(params.data?.term || '')
                            .trim()
                            .toLocaleLowerCase('id-ID');
                        const page = Math.max(1, Number(params.data?.page) || 1);
                        const matches = (medicineCatalog || []).filter(function(item) {
                            if (!query) {
                                return true;
                            }

                            return [item.kode_obat, item.nama_obat, item.satuan?.nama]
                                .filter(Boolean)
                                .join(' ')
                                .toLocaleLowerCase('id-ID')
                                .includes(query);
                        });
                        const offset = (page - 1) * medicineSelectPageSize;
                        const pageItems = matches.slice(offset, offset + medicineSelectPageSize);

                        success({
                            results: pageItems.map(item => ({
                                id: String(item.id),
                                text: medicineOptionText(item)
                            })),
                            pagination: {
                                more: offset + medicineSelectPageSize < matches.length
                            }
                        });

                        return {
                            abort: function() {}
                        };
                    },
                    processResults: function(data) {
                        return data;
                    }
                }
            });
        }

        function populateMedicineSelect($select, selectedValue = null, options = {}) {
            if (!$select || $select.length === 0 || !Array.isArray(medicineCatalog)) {
                return;
            }

            $select.each(function() {
                const s = $(this);

                if (s.hasClass('select2-hidden-accessible')) {
                    s.select2('destroy');
                }

                s.prop('disabled', false)
                    .empty()
                    .append('<option value="">-- Pilih Obat --</option>');

                const selectedMedicine = medicineCatalogById.get(String(selectedValue || ''));

                if (medicineCatalog.length === 0) {
                    s.append('<option value="">Tidak ada Obat tersedia</option>');
                } else if (selectedMedicine) {
                    s.append(new Option(
                        medicineOptionText(selectedMedicine),
                        selectedMedicine.id,
                        true,
                        true
                    ));
                }

                initializeMedicineSelect(s);

                if (selectedMedicine) {
                    s.closest('.detail-item').data(
                        'preserve-purchase-values',
                        options.preservePurchaseValues === true
                    );
                    s.val(String(selectedMedicine.id)).trigger('change');
                }
            });
        }

        function loadObatInto($select, selectedValue = null, options = {}) {
            if (!$select || $select.length === 0) return Promise.resolve();

            $select.each(function() {
                $(this).prop('disabled', true).html('<option>Memuat data...</option>');
            });

            return getMedicineCatalog()
                .then(function() {
                    populateMedicineSelect($select, selectedValue, options);
                })
                .catch(function(xhr) {
                    console.error('Error Obat:', xhr);

                    $select.each(function() {
                        let s = $(this);

                        s.prop('disabled', false)
                            .empty()
                            .append('<option value="">Gagal memuat data obat</option>');

                        if (s.hasClass('select2-hidden-accessible')) {
                            s.select2('destroy');
                        }

                        initializeMedicineSelect(s);
                    });
                });
        }

        function cacheMedicineUnits(medicineId, data) {
            const list = (Array.isArray(data) ? data : [])
                .filter(item => item && item.satuan && Number(item.konversi) > 0)
                .sort(function(a, b) {
                    const defaultDifference = Number(b.is_default || 0) - Number(a.is_default || 0);

                    return defaultDifference ||
                        (Number(a.konversi) - Number(b.konversi)) ||
                        (Number(a.id) - Number(b.id));
                });

            medicineUnitCache.set(String(medicineId), list);
            return list;
        }

        function medicineUnitLabel(item, medicine) {
            const unitName = item?.satuan?.nama || 'Satuan';
            const stockUnitName = medicine?.satuan?.nama || 'satuan stok';
            const conversion = Number(item?.konversi) || 1;

            if (conversion === 1 && unitName.toLocaleLowerCase('id-ID') === stockUnitName.toLocaleLowerCase('id-ID')) {
                return `${unitName} (dasar)`;
            }

            return `${unitName} (1 ${unitName} = ${conversion.toLocaleString('id-ID')} ${stockUnitName})`;
        }

        function preloadMedicineUnits(medicineIds) {
            const missingIds = Array.from(new Set(medicineIds.map(String)))
                .filter(id => id && !medicineUnitCache.has(id) && !medicineUnitRequests.has(id));

            if (missingIds.length === 0) {
                return Promise.resolve();
            }

            const requests = PurchaseOrderEntry.splitBatches(missingIds).map(function(batch) {
                const requestKey = 'batch-' + (++unitRequestSequence);
                pendingUnitRequests.add(requestKey);
                const request = new Promise(function(resolve, reject) {
                    $.ajax({
                        url: "{{ route("pembelian.getKonversiSatuan") }}",
                        type: 'GET', dataType: 'json', data: { obat_ids: batch }, timeout: 20000,
                        success: function(data) {
                            batch.forEach(id => cacheMedicineUnits(id, data?.[id] || []));
                            resolve();
                        },
                        error: reject,
                        complete: function() {
                            batch.forEach(id => medicineUnitRequests.delete(id));
                            pendingUnitRequests.delete(requestKey);
                            updateUnitLoadingState();
                        }
                    });
                });
                batch.forEach(id => medicineUnitRequests.set(id, request));
                return request;
            });
            updateUnitLoadingState();
            return Promise.all(requests);
        }

        function getMedicineUnits(medicineId) {
            const id = String(medicineId || '');

            if (medicineUnitCache.has(id)) {
                return Promise.resolve(medicineUnitCache.get(id));
            }

            if (medicineUnitRequests.has(id)) {
                return medicineUnitRequests.get(id).then(() => medicineUnitCache.get(id) || []);
            }

            return preloadMedicineUnits([id]).then(() => medicineUnitCache.get(id) || []);
        }

        // Ketika pengguna memilih obat
        $(document).on('change', 'select[name="obat_id[]"]', function() {
            const row = $(this).closest('.detail-item');
            const obatId = String($(this).val() || '');
            const medicine = medicineCatalogById.get(obatId);
            const preservePurchaseValues = row.data('preserve-purchase-values') === true;
            const $satuanSelect = row.find('.satuan-select');
            const requestId = ++unitRequestSequence;

            row.data('unit-request-id', requestId);
            if (purchaseConversionContext?.row[0] === row[0]) closePurchaseConversionModal();
            row.find('.purchase-conversion-success').addClass('d-none').text('');
            updateCurrentStock(row, medicine);

            if (!$('#medicinePicker').hasClass('d-none')) {
                renderMedicinePicker();
            }

            $satuanSelect
                .html('<option value="">-- Pilih Satuan --</option>')
                .prop('disabled', true);

            if (!preservePurchaseValues) {
                row.find('.harga_estimasi_satuan, .harga_estimasi, .subtotal').val(0);
                recalculatePurchaseRow(row);
            }

            if (!obatId) {
                row.removeData('preserve-purchase-values');
                hitungTotal();
                return;
            }

            getMedicineUnits(obatId)
                .then(function(list) {
                    if (row.data('unit-request-id') !== requestId) {
                        return;
                    }

                    renderPurchaseMedicineUnits(row, list, row.data('satuan-terpilih'));

                    if (preservePurchaseValues) {
                        row.removeData('preserve-purchase-values');
                        recalculatePurchaseRow(row);
                        hitungTotal();
                    } else {
                        const catalogPrice = parseFloat(medicine?.harga_beli) || 0;

                        row.find('.harga_estimasi_satuan').val(catalogPrice);
                        $satuanSelect.trigger('change');
                    }
                })
                .catch(function() {
                    if (row.data('unit-request-id') !== requestId) {
                        return;
                    }

                    row.removeData('preserve-purchase-values');
                    $satuanSelect
                        .html('<option value="">-- Gagal memuat satuan --</option>')
                        .prop('disabled', false);
                    recalculatePurchaseRow(row);
                    hitungTotal();
                });
        });

        // --- Format Rupiah 
        function formatRupiah(angka) {
            return PurchaseOrderEntry.formatMoney(angka);
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

        function getInitials(value) {
            let words = String(value || '-').trim().split(/\s+/).filter(Boolean);
            return words.slice(0, 2).map(word => word.charAt(0)).join('') || '-';
        }

        function escapeDataTableText(value) {
            // DataTables sudah mengirim teks yang di-escape; dekode sekali sebelum render.
            const entities = {
                '&amp;': '&',
                '&lt;': '<',
                '&gt;': '>',
                '&quot;': '"',
                '&#039;': "'"
            };
            const text = String(value ?? '-').replace(/&(amp|lt|gt|quot|#039);/g, entity => entities[entity]);
            return escapeHtml(text);
        }

        function updatePurchaseSummary(summary, recordsTotal) {
            let total = Number(summary.total ?? recordsTotal) || 0;
            let draft = Number(summary.draft) || 0;
            let waiting = Number(summary.waiting_approval) || 0;
            let pending = Number(summary.pending ?? (draft + waiting)) || 0;
            let approved = Number(summary.approved) || 0;
            let receiving = Number(summary.dalam_penerimaan) || 0;
            let completed = Number(summary.selesai) || 0;
            let rejected = Number(summary.rejected) || 0;

            $('#purchaseTotalCount, #purchaseAllFilterCount').text(total.toLocaleString('id-ID'));
            $('#purchasePendingCount').text(pending.toLocaleString('id-ID'));
            $('#purchaseDraftFilterCount').text(draft.toLocaleString('id-ID'));
            $('#purchaseWaitingFilterCount').text(waiting.toLocaleString('id-ID'));
            $('#purchaseApprovedCount, #purchaseApprovedFilterCount').text(approved.toLocaleString('id-ID'));
            $('#purchaseReceivingFilterCount').text(receiving.toLocaleString('id-ID'));
            $('#purchaseCompletedCount, #purchaseCompletedFilterCount').text(completed.toLocaleString('id-ID'));
            $('#purchaseRejectedFilterCount').text(rejected.toLocaleString('id-ID'));
            const totalValue = formatRupiah(summary.total_estimasi);
            $('#purchaseTotalValue').text(totalValue).css('--purchase-value-length', totalValue.length);
            $('#purchaseTotalItem').text((Number(summary.total_item) || 0).toLocaleString('id-ID'));
            $('#purchaseTotalQty').text(formatStockQuantity(summary.total_qty));
        }

        // --- Harga per satuan tetap; konversi hanya mengubah subtotal dan nilai harga satuan beli
        $(document).on('change', '.satuan-select', function() {
            const row = $(this).closest('.detail-item');

            recalculatePurchaseRow(row);

            hitungTotal();
        });

        // =================== End Inisiasi Event Handler ===============

        // =================== Inisiasi DataTable ======================
        // --- DataTable
        let currentMonthStart = moment().startOf('month');
        let currentMonthEnd = moment().endOf('month');
        let purchaseDateStart = currentMonthStart.format('YYYY-MM-DD');
        let purchaseDateEnd = currentMonthEnd.format('YYYY-MM-DD');
        let purchaseMedicineSearch = '';
        let purchaseMedicineId = '';

        let PembelianTable = $('#tablePembelian').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            order: [
                [3, 'desc']
            ],
            ajax: {
                url: "{{ route("pembelian.table") }}",
                type: "GET",
                data: function(request) {
                    request.date_start = purchaseDateStart;
                    request.date_end = purchaseDateEnd;
                    request.medicine_id = purchaseMedicineId;
                },
                dataSrc: function(response) {
                    updatePurchaseSummary(response.summary || {}, response.recordsTotal);
                    $('#purchaseMedicineSearchResult')
                        .toggleClass('d-none', !purchaseMedicineSearch)
                        .text(purchaseMedicineSearch
                            ? `${Number(response.recordsFiltered || 0).toLocaleString('id-ID')} PO memuat obat "${purchaseMedicineSearch}" sesuai filter aktif.`
                            : '');
                    return response.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `<span class="purchase-row-number">${escapeHtml(data)}</span>`;
                    }
                },
                {
                    data: 'approved_by',
                    name: 'approved_by',
                    render: function(data, type, row) {
                        let status = String(row.purchase_order_status || row.status || '').toLowerCase();

                        if (status === 'rejected') {
                            return `
                                <span class="purchase-approval is-rejected">
                                    <i class="mdi mdi-close-circle-outline"></i>
                                    Ditolak
                                </span>
                            `;
                        }

                        if (!data && status !== 'approved') {
                            return `
                                <span class="purchase-approval is-pending">
                                    <i class="mdi mdi-clock-outline"></i>
                                    Menunggu
                                </span>
                            `;
                        }

                        return `
                            <span class="purchase-approval is-approved" title="Approval tercatat">
                                <i class="mdi mdi-check-circle-outline"></i>
                                Disetujui
                                <br>
                                <small class="text-muted">
                                    ${data}
                                </small>
                            </span>
                        `;
                    }
                },
                {
                    data: 'no_po',
                    name: 'no_po',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;
                        const date = moment(row.tanggal_po);
                        return `
                            <button type="button" class="purchase-po-number purchase-open-detail" data-id="${escapeHtml(row.id)}"
                                aria-label="Lihat detail PO ${escapeHtml(data)}">
                                ${escapeHtml(data)}
                                <i class="mdi mdi-arrow-top-right" aria-hidden="true"></i>
                            </button>
                            <span class="purchase-cell-meta purchase-desktop-meta">${date.isValid() ? date.format('DD MMM YYYY') : '-'}</span>
                            ${row.catatan ? `<span class="purchase-note purchase-desktop-meta" title="${escapeHtml(row.catatan)}"><i class="mdi mdi-note-text-outline" aria-hidden="true"></i> ${escapeHtml(row.catatan)}</span>` : ''}
                        `;
                    }
                },
                {
                    data: 'tanggal_po',
                    name: 'tanggal_po',
                    render: function(data) {
                        let date = moment(data);
                        let formattedDate = date.isValid() ? date.format('DD-MM-YYYY') :
                            escapeHtml(data);
                        return `
                            <span class="purchase-date">
                                <i class="mdi mdi-calendar-blank-outline"></i>
                                ${formattedDate}
                            </span>
                        `;
                    }
                },
                {
                    data: 'branch_id',
                    name: 'branch_id',
                    render: function(data) {
                        return `
                            <span class="purchase-badge is-branch">
                                <i class="mdi mdi-hospital-building"></i>
                                ${escapeHtml(data)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'distributor_id',
                    name: 'distributor_id',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;
                        let distributor = escapeHtml(data);
                        return `
                            <span class="purchase-badge is-distributor" title="${distributor}">
                                <i class="mdi mdi-truck-delivery-outline"></i>
                                ${distributor}
                            </span>
                            <span class="purchase-cell-meta purchase-desktop-meta"><i class="mdi mdi-hospital-building" aria-hidden="true"></i> ${escapeHtml(row.branch_id)}</span>
                        `;
                    }
                },
                {
                    data: 'item_count',
                    name: 'item_count',
                    responsivePriority: 2,
                    render: function(data, type, row) {
                        if (type !== 'display') {
                            return Number(data) || 0;
                        }

                        const matches = (row.matched_medicines || []).map(function(medicine) {
                            return `
                                <span class="purchase-medicine-match">
                                    <strong>${escapeDataTableText(medicine.nama_obat)}</strong>
                                    <small>${escapeDataTableText(medicine.kode_obat)} &middot; ${formatStockQuantity(medicine.qty)} ${escapeDataTableText(medicine.satuan)}</small>
                                </span>
                            `;
                        }).join('');

                        return `
                            <span class="purchase-item-qty">
                                <strong>${(Number(data) || 0).toLocaleString('id-ID')} item</strong>
                                <small>${formatStockQuantity(row.total_qty)} qty</small>
                            </span>
                            ${matches ? `<span class="purchase-medicine-matches">${matches}</span>` : ''}
                        `;
                    }
                },
                {
                    data: 'total_estimasi',
                    name: 'total_estimasi',
                    render: function(data) {
                        return `
                            <span class="purchase-money">
                                <i class="mdi mdi-cash"></i>
                                ${formatRupiah(data)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;

                        let status = String(data || '').toLowerCase();
                        let statusClass =
                            (status === 'approved' || status === 'selesai') ? 'is-approved' :
                            (status === 'draft' || status === 'waiting_approval') ? 'is-draft' :
                            (status === 'dalam_penerimaan' || status === 'diterima_sebagian') ? 'is-partial' :
                            status === 'rejected' ? 'is-rejected' :
                            'is-other';
                        let statusLabel =
                            status === 'approved' ? 'Disetujui' :
                            status === 'dalam_penerimaan' ? 'Dalam Penerimaan' :
                            status === 'diterima_sebagian' ? 'Diterima Sebagian' :
                            status === 'selesai' ? 'Selesai' :
                            status === 'draft' ? 'Draft' :
                            status === 'waiting_approval' ? 'Menunggu Approval' :
                            status === 'rejected' ? 'Ditolak' :
                            (data || '-');
                        let statusIcon =
                            status === 'approved' ? 'mdi-check-circle-outline' :
                            status === 'dalam_penerimaan' ? 'mdi-package-variant' :
                            status === 'diterima_sebagian' ? 'mdi-progress-check' :
                            status === 'selesai' ? 'mdi-package-variant-closed-check' :
                            (status === 'draft' || status === 'waiting_approval') ?
                            'mdi-file-clock-outline' :
                            status === 'rejected' ? 'mdi-close-circle-outline' :
                            'mdi-help-circle-outline';
                        let statusContent = `
                            <i class="mdi ${statusIcon}"></i>
                            ${escapeHtml(statusLabel)}
                        `;
                        const approval = row.approved_by
                            ? `Disetujui oleh ${escapeHtml(row.approved_by)}`
                            : status === 'rejected' ? 'Pengajuan ditolak'
                            : status === 'draft' ? 'Belum diajukan'
                            : status === 'waiting_approval' ? 'Menunggu persetujuan' : '';
                        const approvalContent = approval
                            ? `<span class="purchase-cell-meta purchase-desktop-meta purchase-approval-caption">${approval}</span>` : '';

                        if (!row.has_receipt) {
                            return `
                                <span class="purchase-status is-disabled ${statusClass}" role="link" aria-disabled="true"
                                    title="Belum ada data penerimaan untuk PO ${escapeHtml(row.no_po)}">
                                    ${statusContent}
                                </span>
                                ${approvalContent}
                            `;
                        }

                        let receivingUrl = '{{ route("penerimaan.penerimaan") }}' +
                            '?purchase_order_id=' + encodeURIComponent(row.id);

                        return `
                            <a class="purchase-status purchase-status-link ${statusClass}" href="${receivingUrl}"
                                title="Lihat penerimaan PO ${escapeHtml(row.no_po)}"
                                aria-label="Lihat penerimaan PO ${escapeHtml(row.no_po)}: ${escapeHtml(statusLabel)}">
                                ${statusContent}
                            </a>
                            ${approvalContent}
                        `;
                    }
                },
                {
                    data: 'catatan',
                    name: 'catatan',
                    render: function(data) {
                        let note = escapeHtml(data || '-');
                        return `<span class="purchase-note" title="${note}">${note}</span>`;
                    }
                },
                {
                    data: 'created_by',
                    name: 'created_by',
                    render: function(data) {
                        let user = escapeHtml(data || '-');
                        return `
                            <span class="purchase-user">
                                <span class="purchase-user-avatar">${escapeHtml(getInitials(data))}</span>
                                <span>${user}</span>
                            </span>
                        `;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `<div class="purchase-action-group">${data || ''}</div>`;
                    }
                }
            ],
            columnDefs: [{
                targets: [11],
                className: 'text-center'
            }, {
                targets: [0],
                className: 'purchase-secondary-column text-center'
            }, {
                targets: [1, 3, 4, 9],
                className: 'purchase-secondary-column'
            }, {
                targets: [7],
                className: 'text-end'
            }],
            drawCallback: function() {
                updatePurchaseListContext(this.api());
                let table = $('#tablePembelian');
                table.toggleClass('is-medicine-search', Boolean(purchaseMedicineSearch));
                const mobileLabels = [
                    'No',
                    'Approval',
                    'Nomor PO',
                    'Tanggal PO',
                    'Cabang',
                    'Distributor',
                    'Item / Qty',
                    'Total Estimasi',
                    'Status',
                    'Catatan',
                    'Dibuat oleh',
                    'Aksi'
                ];

                table.find('tbody tr').each(function() {
                    let row = $(this);

                    row.children('td').not('[colspan]').each(function(index) {
                        $(this).attr('data-label', mobileLabels[index] || 'Informasi');
                    });

                    let actionGroup = row.children('td').eq(11).find('.purchase-action-group');
                    if (actionGroup.length && !actionGroup.find('.purchase-mobile-more').length) {
                        actionGroup.prepend(`
                            <button type="button" class="btn btn-sm purchase-mobile-more"
                                aria-expanded="false" aria-label="Tampilkan informasi tambahan"
                                title="Tampilkan informasi tambahan">
                                <i class="mdi mdi-information-outline"></i>
                                <span>Info</span>
                            </button>
                        `);
                    }
                });

                table.find('.purchase-action-group .btn-approve-pembelian')
                    .attr({
                        title: 'Setujui purchase order',
                        'aria-label': 'Setujui purchase order'
                    });
                table.find('.purchase-action-group .btn-reject-pembelian')
                    .attr({
                        title: 'Tolak purchase order',
                        'aria-label': 'Tolak purchase order'
                    });
                table.find('.purchase-action-group .btn-reopen-pembelian')
                    .attr({
                        title: 'Buka approval purchase order',
                        'aria-label': 'Buka approval purchase order'
                    });
                table.find('.purchase-action-group .btn-edit-pembelian')
                    .attr({
                        title: 'Edit purchase order',
                        'aria-label': 'Edit purchase order'
                    });
                table.find('.purchase-action-group .btn-info')
                    .attr({
                        title: 'Lihat detail purchase order',
                        'aria-label': 'Lihat detail purchase order'
                    });
                table.find('.purchase-action-group .btn-danger')
                    .attr({
                        title: 'Hapus purchase order',
                        'aria-label': 'Hapus purchase order'
                    });
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat purchase order...</span>',
                emptyTable: '<div class="purchase-empty-state"><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i><strong>Belum ada purchase order di periode ini</strong><span>Ubah periode atau buat PO baru untuk mulai mencatat pembelian.</span></div>',
                zeroRecords: '<div class="purchase-empty-state"><i class="mdi mdi-magnify" aria-hidden="true"></i><strong>Purchase order tidak ditemukan</strong><span>Coba kata kunci lain atau reset filter untuk melihat semua pesanan.</span></div>',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let purchaseSearchTimer;

        function updatePurchaseListContext(table) {
            const status = String(table.column(8).search() || '');
            const labels = {
                draft: 'Draft', waiting_approval: 'Menunggu persetujuan', approved: 'Disetujui',
                dalam_penerimaan: 'Dalam penerimaan', selesai: 'Selesai', rejected: 'Ditolak'
            };
            const period = purchaseDateStart && purchaseDateEnd
                ? `${moment(purchaseDateStart).format('DD MMM YYYY')} – ${moment(purchaseDateEnd).format('DD MMM YYYY')}`
                : 'Semua tanggal';
            const count = Number(table.page.info().recordsDisplay || 0).toLocaleString('id-ID');
            $('#purchaseEstimatePeriod').text(period);
            $('#purchaseListContext').text(`${count} PO · ${labels[status] || 'Semua status'} · ${period}`);
            $('#resetPurchaseFilters').prop('disabled', !(status || purchaseDateStart || purchaseDateEnd || purchaseMedicineId || table.search()));
        }

        $('#searchPembelian').on('input', function() {
            let searchValue = this.value;
            $(this).closest('.purchase-search').toggleClass('has-value', Boolean(searchValue));
            $('#clearPurchaseSearch').attr('tabindex', searchValue ? '0' : '-1');
            clearTimeout(purchaseSearchTimer);
            purchaseSearchTimer = setTimeout(function() {
                PembelianTable.search(searchValue).draw();
            }, 250);
        });

        $('#clearPurchaseSearch').on('click', function() {
            $('#searchPembelian').val('').trigger('input').focus();
        });

        $('#resetPurchaseFilters').on('click', function() {
            clearTimeout(purchaseSearchTimer);
            purchaseMedicineId = '';
            purchaseMedicineSearch = '';
            $('#searchPurchaseMedicine').val(null).trigger('change.select2');
            $('#searchPembelian').val('').closest('.purchase-search').removeClass('has-value');
            $('#clearPurchaseSearch').attr('tabindex', '-1');
            $('.purchase-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $('.purchase-filter-chip[data-status=""]').addClass('is-active').attr('aria-pressed', 'true');
            clearPurchaseDateFilter();
            PembelianTable.search('').column(8).search('').draw();
        });

        $('#tablePembelian').on('click', '.purchase-open-detail', function() {
            window.lihatPembelian($(this).attr('data-id'));
        });

        $('.purchase-filter-chip').on('click', function() {
            $('.purchase-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            PembelianTable.column(8).search($(this).data('status') || '').draw();
        });

        function formatDateParameter(date) {
            return moment(date).format('YYYY-MM-DD');
        }

        function applyPurchaseDateRange(startDate, endDate) {
            purchaseDateStart = formatDateParameter(startDate);
            purchaseDateEnd = formatDateParameter(endDate);
            $('#purchaseDateRange').closest('.purchase-date-input').addClass('has-value');
            PembelianTable.ajax.reload();
        }

        let purchaseDatePicker = flatpickr('#purchaseDateRange', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            defaultDate: [
                purchaseDateStart,
                purchaseDateEnd
            ],
            locale: {
                rangeSeparator: ' - '
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    $('#purchaseDatePreset').val('');
                    applyPurchaseDateRange(selectedDates[0], selectedDates[1]);
                }
            },
            onClose: function(selectedDates) {
                if (selectedDates.length === 1) {
                    this.clear();
                }
            }
        });

        $('#purchaseDatePreset').val('this_month');
        $('#purchaseDateRange').closest('.purchase-date-input').addClass('has-value');

        $('#clearPurchaseDateRange').on('click', function() {
            clearPurchaseDateFilter();
            PembelianTable.ajax.reload();
        });

        function clearPurchaseDateFilter() {
            purchaseDateStart = '';
            purchaseDateEnd = '';
            purchaseDatePicker.clear();
            $('#purchaseDatePreset').val('all');
            $('#purchaseDateRange').closest('.purchase-date-input').removeClass('has-value');
        }

        $('#searchPurchaseMedicine').select2({
            placeholder: 'Ketik nama atau kode, lalu pilih obat',
            width: '100%',
            allowClear: true,
            dropdownParent: $('#purchaseMedicineSearchForm'),
            language: {
                searching: () => 'Memuat obat...',
                noResults: () => 'Obat tidak ditemukan.',
                errorLoading: () => 'Gagal memuat obat. Tutup dropdown lalu coba lagi.'
            },
            ajax: {
                delay: 150,
                transport: function(params, success, failure) {
                    let cancelled = false;
                    const query = String(params.data?.term || '').trim().toLocaleLowerCase('id-ID');
                    const page = Math.max(1, Number(params.data?.page) || 1);

                    getMedicineCatalog().then(function(catalog) {
                        if (cancelled) return;

                        const matches = catalog.filter(item =>
                            medicineOptionText(item).toLocaleLowerCase('id-ID').includes(query)
                        );
                        const offset = (page - 1) * medicineSelectPageSize;

                        success({
                            results: matches.slice(offset, offset + medicineSelectPageSize).map(item => ({
                                id: String(item.id),
                                text: medicineOptionText(item)
                            })),
                            pagination: { more: offset + medicineSelectPageSize < matches.length }
                        });
                    }).catch(function() {
                        if (!cancelled) failure();
                    });

                    return { abort: function() { cancelled = true; } };
                },
                processResults: data => data
            }
        });

        $('#purchaseMedicineSearchForm').on('submit', function(event) {
            event.preventDefault();
            purchaseMedicineId = String($('#searchPurchaseMedicine').val() || '');
            const medicine = medicineCatalogById.get(purchaseMedicineId);
            purchaseMedicineSearch = medicine ? medicineOptionText(medicine) : '';
            clearTimeout(purchaseSearchTimer);
            $('#searchPembelian').val('').closest('.purchase-search').removeClass('has-value');
            $('#clearPurchaseSearch').attr('tabindex', '-1');
            $('.purchase-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $('.purchase-filter-chip[data-status=""]').addClass('is-active').attr('aria-pressed', 'true');
            clearPurchaseDateFilter();
            PembelianTable.search('').column(8).search('').draw();
        });

        $('#searchPurchaseMedicine').on('change', function() {
            if ($(this).val()) {
                $('#purchaseMedicineSearchForm').trigger('submit');
            } else if (purchaseMedicineId) {
                purchaseMedicineId = '';
                purchaseMedicineSearch = '';
                PembelianTable.ajax.reload();
            }
        });

        $('#purchaseDatePreset').on('change', function() {
            let preset = this.value;

            if (!preset) {
                return;
            }

            if (preset === 'all') {
                clearPurchaseDateFilter();
                PembelianTable.ajax.reload();
                return;
            }

            let today = moment().startOf('day');
            let startDate = today.clone();
            let endDate = today.clone();

            if (preset === '7days') {
                startDate.subtract(6, 'days');
            } else if (preset === '30days') {
                startDate.subtract(29, 'days');
            } else if (preset === 'this_month') {
                startDate.startOf('month');
                endDate.endOf('month');
            }

            purchaseDatePicker.setDate([
                startDate.format('YYYY-MM-DD'),
                endDate.format('YYYY-MM-DD')
            ], false);
            applyPurchaseDateRange(startDate, endDate);
        });

        $('#purchasePageLength').on('change', function() {
            PembelianTable.page.len(Number(this.value)).draw();
        });

        $('#refreshPurchaseTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            PembelianTable.ajax.reload(null, false);
        });

        $('#purchaseMobileFilterToggle').on('click', function() {
            let button = $(this);
            let filterBar = $('#purchaseFilterBar');
            let isOpen = !filterBar.hasClass('is-mobile-open');

            filterBar.toggleClass('is-mobile-open', isOpen);
            button
                .toggleClass('is-active', isOpen)
                .attr('aria-expanded', isOpen ? 'true' : 'false')
                .attr('title', isOpen ? 'Tutup filter periode' : 'Buka filter periode')
                .attr('aria-label', isOpen ? 'Tutup filter periode' : 'Buka filter periode')
                .find('i')
                .toggleClass('mdi-tune-variant', !isOpen)
                .toggleClass('mdi-chevron-up', isOpen);
        });

        $('#tablePembelian').on('click', '.purchase-mobile-more', function() {
            let button = $(this);
            let row = button.closest('tr');
            let isExpanded = !row.hasClass('is-mobile-expanded');

            row.toggleClass('is-mobile-expanded', isExpanded);
            button
                .attr('aria-expanded', isExpanded ? 'true' : 'false')
                .attr('aria-label', isExpanded ? 'Sembunyikan informasi tambahan' : 'Tampilkan informasi tambahan')
                .attr('title', isExpanded ? 'Sembunyikan informasi tambahan' : 'Tampilkan informasi tambahan')
                .find('span')
                .text(isExpanded ? 'Ringkas' : 'Info');
            button.find('i')
                .toggleClass('mdi-information-outline', !isExpanded)
                .toggleClass('mdi-chevron-up', isExpanded);
        });

        $('#tablePembelian').on('xhr.dt', function() {
            $('#refreshPurchaseTable').removeClass('is-loading').prop('disabled', false);
        });

        $('#scrollPurchaseTable').on('click', function() {
            document.getElementById('purchaseTableSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
        // =================== End Inisiasi DataTable ==================

        // =================== Inisiasi Action =========================
        function ensurePurchaseOrderNumber() {
            if (!selectedDistributorUsesManualPoNumber() || String($('input[name="no_po"]').val() || '').trim() !== '') {
                return Promise.resolve();
            }

            if (selectedDistributorUsesManualPoNumber()) {
                return Promise.reject({
                    status: 422,
                    responseJSON: {
                        errors: {
                            no_po: ['Nomor PO wajib diketik manual untuk distributor yang dipilih.']
                        }
                    }
                });
            }

        }

        function waitForPendingUnitRequests() {
            const requests = Array.from(new Set(medicineUnitRequests.values()));

            return requests.length > 0 ? Promise.allSettled(requests) : Promise.resolve();
        }

        function showPurchaseSaveError(xhr) {
            if (xhr?.status === 422) {
                showPurchaseValidationErrors(xhr.responseJSON?.errors || {}, xhr.responseJSON?.message || 'Periksa kembali data purchase order.');
                return;
            }
            const status = xhr?.status;
            let message = 'Isian PO tetap tersedia. Silakan coba simpan kembali.';
            if (status === 401 || status === 419) {
                message = 'Sesi login telah berakhir atau berubah. Masuk kembali melalui tab baru, lalu coba simpan lagi di form ini.';
            } else if (status === 0 || status >= 500 || xhr?.statusText === 'parsererror') {
                message = 'Hasil penyimpanan belum dapat dipastikan. Periksa koneksi lalu klik simpan kembali untuk memeriksa percobaan yang sama tanpa membuat PO ganda.';
            } else if (status === 403 || status === 409) {
                message = xhr.responseJSON?.message || 'Akses atau status PO telah berubah. Periksa akun dan daftar PO, lalu lanjutkan dari isian ini.';
            }
            Swal.fire({
                icon: 'error', title: 'Purchase order gagal disimpan',
                text: message + (purchaseBackupAvailable ? ' Cadangan isian tersedia di tab browser ini.' : ' Jangan tutup atau muat ulang tab ini karena cadangan browser tidak tersedia.'),
                showCancelButton: status === 401 || status === 419,
                confirmButtonText: status === 401 || status === 419 ? 'Buka login di tab baru' : 'Mengerti',
                cancelButtonText: 'Tetap di form'
            }).then(function(result) {
                if (result.isConfirmed && (status === 401 || status === 419)) window.open(@json(route('login')), '_blank', 'noopener');
            });
        }

        function submitPurchaseOrder(saveAsDraft = false, confirmed = false) {
            if (purchaseSaveInProgress || purchaseConversionSaves.size > 0 || purchaseConversionContext) {
                return;
            }

            if (!purchasePendingSave && !validatePurchaseEntry(saveAsDraft)) return;
            if (!purchasePendingSave && !saveAsDraft && !confirmed) {
                confirmPurchaseSubmission();
                return;
            }
            purchaseSaveInProgress = true;
            $('#pembelianForm').prop('inert', true);
            $('#purchaseDraftStatus').text('Menyimpan...');
            updateUnitLoadingState();
            persistPurchaseRecovery();

            (purchasePendingSave ? Promise.resolve() : Promise.all([
                    ensurePurchaseOrderNumber(),
                    waitForPendingUnitRequests()
                ]))
                .then(function() {
                    if (purchasePendingSave) return sendPendingPurchaseSave();
                    if (!saveAsDraft && $('#detail-wrapper .detail-item').toArray().some(function(element) {
                        const row = $(element);

                        return Boolean(row.find('[name="obat_id[]"]').val()) &&
                            !(Number(row.find('.satuan-select').val()) > 0);
                    })) {
                        throw {
                            status: 422,
                            responseJSON: {
                                message: 'Lengkapi satuan konversi terlebih dahulu. PO hanya dapat disimpan sebagai draft.'
                            }
                        };
                    }

                    const purchaseOrderId = $('#pembelian_id').val();
                    const url = purchaseOrderId ?
                        "{{ route("pembelian.update", ":id") }}".replace(':id', purchaseOrderId) :
                        "{{ route("pembelian.store") }}";
                    const method = purchaseOrderId ? 'PUT' : 'POST';

                    $('#detail-wrapper .detail-item').each(function() {
                        recalculatePurchaseRow($(this));
                    });
                    hitungTotal(true);

                    const formData = $('#pembelianForm').serializeArray();

                    formData.push({
                        name: 'save_as_draft',
                        value: saveAsDraft ? '1' : '0'
                    });

                    const payload = PurchaseOrderEntry.formPayload(formData, saveAsDraft);
                    delete payload._token;
                    purchaseSubmittedSignature = purchaseEntrySignature();
                    purchasePendingSave = {
                        key: PurchaseOrderEntry.newRequestKey(), url, method, payload, saveAsDraft,
                        signature: purchaseSubmittedSignature
                    };
                    persistPurchaseRecovery();
                    return sendPendingPurchaseSave();
                })
                .then(function(response) {
                    if (response?.status !== 'success' || !response.data?.id) {
                        throw {
                            status: response?.errors ? 422 : 0,
                            responseJSON: response
                        };
                    }

                    const savedAttempt = purchasePendingSave;
                    purchaseSubmittedSignature = savedAttempt.signature;
                    saveAsDraft = savedAttempt.saveAsDraft;
                    // Compare before the server's final automatic number changes the form.
                    const changedSinceAttempt = purchaseEntrySignature() !== savedAttempt.signature;
                    purchasePendingSave = null;
                    $('#pembelian_id').val(response.data?.id || $('#pembelian_id').val());
                    if (response.data?.no_po) {
                        $('input[name="no_po"]').val(response.data.no_po);
                    }
                    editMode = true;
                    updatePurchaseOrderNumberMode();
                    if (saveAsDraft) $('#pembelianModalLabel').text('Edit Purchase Order');
                    markPurchaseSaved();
                    if (!changedSinceAttempt) {
                        purchaseAllowClose = true;
                        $('#pembelianModal').modal('hide');
                    }

                    if (changedSinceAttempt) {
                        Swal.fire({ icon: 'info', title: 'Percobaan simpan sebelumnya berhasil',
                            text: 'PO sebelumnya sudah tersimpan. Perubahan setelah percobaan tersebut masih ada di form dan belum disimpan. Periksa kembali sebelum melanjutkan.' });
                    } else if (response.warning) {
                        Swal.fire({
                            icon: 'warning',
                            title: response.message,
                            text: response.warning,
                            confirmButtonText: 'Mengerti'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    }

                    PembelianTable.ajax.reload(null, false);
                })
                .catch(function(xhr) {
                    if (xhr?.status === 422) purchasePendingSave = null;
                    persistPurchaseRecovery();
                    $('#purchaseDraftStatus').text('Gagal disimpan · periksa data dan coba lagi');
                    showPurchaseSaveError(xhr);
                })
                .finally(function() {
                    purchaseSaveInProgress = false;
                    $('#pembelianForm').prop('inert', false);
                    updateUnitLoadingState();
                });
        }

        // --- Submit PO ke alur approval
        $('#pembelianForm').on('submit', function(event) {
            event.preventDefault();
            submitPurchaseOrder(false);
        });

        // --- Simpan eksplisit sebagai draft
        $('#saveDraftForm').on('click', function() {
            submitPurchaseOrder(true);
        });

        // --- Edit Pembelian
        window.approvePembelian = function(id) {
            Swal.fire({
                title: 'Setujui purchase order?',
                text: 'PO ini akan ditandai sudah disetujui oleh admin.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route("pembelian.approve", ":id") }}".replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: response.status === 'info' ? 'info' : 'success',
                            title: response.message || 'Pembelian berhasil disetujui.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        PembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyetujui PO.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Approval gagal',
                            text: message,
                        });
                    }
                });
            });
        };

        window.rejectPembelian = function(id) {
            Swal.fire({
                title: 'Tolak purchase order?',
                text: 'PO ini akan ditandai ditolak dan tidak masuk approval.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route("pembelian.reject", ":id") }}".replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: response.status === 'info' ? 'info' : 'success',
                            title: response.message || 'Pembelian berhasil ditolak.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        PembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menolak PO.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Reject gagal',
                            text: message,
                        });
                    }
                });
            });
        };

        window.reopenPembelian = function(id) {
            Swal.fire({
                title: 'Buka approval purchase order?',
                text: 'Status PO akan kembali menunggu approval sehingga bisa diedit ulang.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, buka approval',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route("pembelian.reopenApproval", ":id") }}".replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: response.status === 'info' ? 'info' : 'success',
                            title: response.message || 'Approval pembelian berhasil dibuka.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        PembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat membuka approval PO.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Buka approval gagal',
                            text: message,
                        });
                    }
                });
            });
        };

        window.editPembelian = function(id) {
            if (purchaseRecoveryStore.read()) {
                restorePurchaseRecovery();
                return;
            }

            editMode = true;

            $.ajax({
                url: "{{ route('pembelian.edit', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(response) {

                    let header = response;
                    let detail = response.details ?? [];

                    $('#pembelianModal').modal('show');
                    $('#pembelianModalLabel').text('Edit Purchase Order');
                    $('#submitForm').html(
                        '<i class="mdi mdi-check-all" aria-hidden="true"></i><span class="purchase-desktop-text">Periksa &amp; Ajukan</span><span class="purchase-mobile-text">Ajukan PO</span>'
                    );

                    $('#pembelian_id').val(header.id);
                    $('input[name="no_po"]').val(header.no_po);
                    $('#distributor_id').val(header.distributor_id).trigger('change');

                    $('input[name="tanggal"]').val(
                        moment(header.tanggal_po).format('DD-MM-YYYY')
                    );

                    $('textarea[name="catatan"]').val(header.catatan ?? '');
                    $('.purchase-notes-details').prop('open', Boolean(header.catatan));
                    $('#purchaseDraftStatus').text('PO tersimpan');
                    $('input[name="biaya_asuransi"]').val(header.biaya_asuransi ?? 0);
                    $('input[name="biaya_pengiriman"]').val(header.biaya_pengiriman ?? 0);

                    $('#detail-wrapper').empty();

                    detail.forEach(item => {

                        let row = detailItemTemplate({
                            obatId: item.obat_id,
                            satuanTerpilih: item.satuan_konversi?.id,
                            qty: item.qty,
                            hargaSatuan: Number(item.harga_estimasi || 0) /
                                Math.max(1, Number(item.satuan_konversi?.konversi || 1)),
                            harga: item.harga_estimasi,
                            diskon1: item.diskon_1,
                            diskon2: item.diskon_2,
                            diskon3: item.diskon_3,
                            ppn: item.ppn ?? 11,
                            subtotal: item.subtotal,
                            selectClass: 'obatSelect',
                            loadingOption: true
                        });

                        $('#detail-wrapper').append(row);
                    });


                    hitungTotal();

                    getMedicineCatalog()
                        .then(function() {
                            return preloadMedicineUnits(detail.map(item => item.obat_id));
                        })
                        .catch(function() {
                            // Select tetap dirender; satuan yang gagal akan menampilkan status error per baris.
                        })
                        .then(function() {
                            const rowLoads = [];

                            $('.obatSelect').each(function(i) {
                                rowLoads.push(loadObatInto($(this), detail[i].obat_id, {
                                    preservePurchaseValues: true
                                }));
                            });
                            return Promise.allSettled(rowLoads);
                        });
                },
                error: function(xhr) {
                    editMode = false;
                    let message = xhr.responseJSON?.message ||
                        'Terjadi kesalahan saat memuat data pembelian.';

                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak bisa edit',
                        text: message,
                    });
                }
            });
        };

        // --- Detail
        @include('medcare.menu.pembelianPenerimaan.pembelian.partials.detailScript')

        window.lihatPembelian = function(id) {
            $.ajax({
                url: "{{ route("pembelian.show", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(res) {
                    const po = res?.data ?? res?.original ?? res;

                    if (!po || typeof po !== 'object' || !Array.isArray(po.details)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Detail PO tidak dapat ditampilkan',
                            text: 'Respons detail purchase order tidak valid. Silakan muat ulang halaman.'
                        });
                        return;
                    }

                    const detail = po.details;
                    detailPurchaseOrderId = po.id;

                    const regularCount = Number(po.regular_item_count ?? 0);
                    const narcoticCount = Number(po.narcotic_item_count ?? 0);
                    const psychotropicCount = Number(po.psychotropic_item_count ?? 0);
                    const precursorCount = Number(po.precursor_item_count ?? 0);
                    const ootCount = Number(po.oot_item_count ?? 0);
                    detailPrintDocuments = [
                        {
                            name: 'Reguler',
                            count: regularCount,
                            buttonClass: 'btn-outline-info',
                            url: "{{ route("pembelian.suratPesananReguler", ":id") }}".replace(':id', po.id)
                        },
                        {
                            name: 'Narkotika',
                            count: narcoticCount,
                            buttonClass: 'btn-outline-danger',
                            url: "{{ route("pembelian.suratPesananNarkotika", ":id") }}".replace(':id', po.id)
                        },
                        {
                            name: 'Psikotropika',
                            count: psychotropicCount,
                            buttonClass: 'btn-outline-primary',
                            url: "{{ route("pembelian.suratPesananPsikotropika", ":id") }}".replace(':id', po.id)
                        },
                        {
                            name: 'Prekursor',
                            count: precursorCount,
                            buttonClass: 'btn-outline-warning',
                            url: "{{ route("pembelian.suratPesananPrekursor", ":id") }}".replace(':id', po.id)
                        },
                        {
                            name: 'OOT',
                            count: ootCount,
                            buttonClass: 'btn-outline-success',
                            url: "{{ route("pembelian.suratPesananOot", ":id") }}".replace(':id', po.id)
                        }
                    ].filter(document => document.count > 0);

                    const $printButton = $('#btnPrintPDF');
                    const totalOrderItems = detailPrintDocuments.reduce(
                        (total, document) => total + document.count,
                        0
                    );

                    $printButton
                        .removeClass('btn-outline-danger btn-outline-info btn-outline-primary btn-outline-warning btn-outline-success btn-outline-dark')
                        .prop('disabled', detailPrintDocuments.length < 1);

                    if (detailPrintDocuments.length === 0) {
                        $printButton
                            .addClass('btn-outline-danger')
                            .attr('title', 'PO ini tidak memiliki item yang dapat dibuatkan surat pesanan')
                            .find('span').text('Tidak Ada Surat Pesanan');
                    } else if (detailPrintDocuments.length === 1) {
                        const document = detailPrintDocuments[0];
                        $printButton
                            .addClass(document.buttonClass)
                            .attr('title', `${document.count} item akan dicetak pada Surat Pesanan ${document.name}`)
                            .find('span').text(`Cetak Surat ${document.name} (${document.count} item)`);
                    } else {
                        const documentNames = detailPrintDocuments.map(document => document.name).join(', ');
                        $printButton
                            .addClass('btn-outline-dark')
                            .attr('title', `Pilih surat yang ingin dicetak: ${documentNames}`)
                            .find('span').text(`Cetak Surat Pesanan (${detailPrintDocuments.length} jenis, ${totalOrderItems} item)`);
                    }

                    renderPurchaseOrderDetail(po);

                    // Tampilkan modal
                    $('#pembelianModalDetail').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Detail PO tidak dapat ditampilkan',
                        text: xhr.responseJSON?.message ||
                            (xhr.status === 404
                                ? 'Purchase order tidak ditemukan atau tidak dapat diakses dari branch Anda.'
                                : 'Terjadi kesalahan saat memuat detail purchase order.')
                    });
                }
            });
        };

        // --- Hapus
        window.deletePembelian = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'PO ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("pembelian.destroy", ":id") }}".replace(
                            ':id',
                            id),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Dihapus!',
                                    response.message,
                                    'success'
                                );
                                PembelianTable.ajax.reload(); // Reload DataTables
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menghapus PO.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
        // ==================== End Inisiasi Action ====================
        showPurchaseRecoveryNotice();

    });
</script>
