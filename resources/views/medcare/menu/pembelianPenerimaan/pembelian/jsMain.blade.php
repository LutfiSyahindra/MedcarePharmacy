<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
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
        const selectedMedicineIds = new Set();
        const medicinePickerPageSize = 100;
        const medicineSelectPageSize = 50;
        let medicinePickerSearchTimer = null;
        let purchaseSaveInProgress = false;
        let currentPoNumberMode = null;
        let currentPoDistributorId = null;
        let poNumberRequestSequence = 0;

        // --- Variabel select2
        let DistributorSelect = $('select[name="distributor_id"]');
        let PembelianSelect2Parent = $('#pembelianModal');

        // --- Inisialisasi flatpickr   
        flatpickr("#flatpickr-date", {
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
                $('#purchaseNumberHint').text('Terisi otomatis untuk distributor ini.');

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
            let selectClass = options.selectClass ? ` ${options.selectClass}` : '';
            let satuanAttr = options.satuanTerpilih ? ` data-satuan-terpilih="${options.satuanTerpilih}"` : '';
            let loadingOption = options.loadingOption ?
                `<option value="${options.obatId || ''}">Loading...</option>` : '';

            return `
                <div class="detail-item purchase-detail-card"${satuanAttr}>
                    <div class="purchase-detail-card-head">
                        <div>
                            <span class="purchase-detail-number">1</span>
                            <strong>Item Obat</strong>
                            <small>Pilih obat, satuan, qty, harga per satuan, Diskon 1–3, dan PPN.</small>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                            <i class="mdi mdi-trash-can-outline"></i> Hapus
                        </button>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4 col-md-6">
                            <label class="form-label">Obat</label>
                            <select class="js-example-basic-single form-select${selectClass}" data-width="100%" name="obat_id[]" required>
                                ${loadingOption}
                            </select>
                            <div class="purchase-current-stock is-empty" aria-live="polite">
                                <i class="mdi mdi-archive-outline"></i>
                                <span>Pilih obat untuk melihat stok saat ini</span>
                            </div>
                        </div>

                        <div class="col-8 col-lg-3 col-md-6 purchase-unit-field">
                            <label class="form-label">Satuan</label>
                            <select class="form-select satuan-select" name="satuan_id[]" disabled required>
                                <option value="">-- Pilih Satuan --</option>
                            </select>
                        </div>

                        <div class="col-4 col-lg-1 col-md-4 purchase-qty-field">
                            <label class="form-label">Qty</label>
                            <input type="number" class="form-control" name="qty[]" min="1" value="${options.qty ?? 1}" required>
                        </div>

                        <div class="col-6 col-lg-2 col-md-4">
                            <label class="form-label">Harga / Satuan</label>
                            <input type="number" class="form-control harga_estimasi_satuan" name="harga_estimasi_satuan[]" min="0" step="any" value="${options.hargaSatuan ?? options.harga ?? 0}">
                            <input type="hidden" class="harga_estimasi" name="harga_estimasi[]" value="${options.harga ?? 0}">
                        </div>

                        <div class="col-6 col-lg-2 col-md-4">
                            <label class="form-label">Subtotal + PPN</label>
                            <input type="number" class="form-control subtotal" name="subtotal[]" value="${options.subtotal ?? ''}" readonly>
                        </div>

                        <div class="col-6 col-lg-3 col-md-6">
                            <label class="form-label">Diskon 1 (%)</label>
                            <input type="number" class="form-control purchase-discount" name="diskon_1[]" min="0" max="100" step="0.01" value="${options.diskon1 ?? 0}">
                        </div>

                        <div class="col-6 col-lg-3 col-md-6">
                            <label class="form-label">Diskon 2 (%)</label>
                            <input type="number" class="form-control purchase-discount" name="diskon_2[]" min="0" max="100" step="0.01" value="${options.diskon2 ?? 0}">
                        </div>

                        <div class="col-6 col-lg-3 col-md-6">
                            <label class="form-label">Diskon 3 (%)</label>
                            <input type="number" class="form-control purchase-discount" name="diskon_3[]" min="0" max="100" step="0.01" value="${options.diskon3 ?? 0}">
                        </div>

                        <div class="col-6 col-lg-3 col-md-6">
                            <label class="form-label">PPN (%)</label>
                            <input type="number" class="form-control purchase-tax" name="ppn[]" min="0" max="100" step="0.01" value="${options.ppn ?? 11}" required>
                        </div>
                    </div>
                </div>
            `;
        }

        function refreshDetailNumbers() {
            $('#detail-wrapper .detail-item').each(function(index) {
                $(this).find('.purchase-detail-number').text(index + 1);
            });
        }

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
            const isLoading = pendingUnitRequests.size > 0;

            $('#submitForm, #saveDraftForm').prop('disabled', isLoading || purchaseSaveInProgress);
            $('#purchaseUnitLoadingNotice').toggleClass('d-none', !isLoading);
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
            return filteredMedicineCatalog().slice(0, medicinePickerPageSize);
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
            const filteredItems = matchingItems.slice(0, medicinePickerPageSize);
            const hasQuery = String($('#medicinePickerSearch').val() || '').trim() !== '';
            const shownCount = filteredItems.length.toLocaleString('id-ID');
            const matchCount = matchingItems.length.toLocaleString('id-ID');
            const resultLabel = matchingItems.length > filteredItems.length
                ? `${shownCount} dari ${matchCount} hasil ditampilkan â€” persempit pencarian`
                : (hasQuery
                    ? `${matchCount} dari ${medicineCatalog.length.toLocaleString('id-ID')} obat`
                    : `${matchCount} obat tersedia`);

            $('#medicinePickerResultCount').text(resultLabel);

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
                            <input type="checkbox" class="form-check-input medicine-picker-checkbox"
                                value="${escapeHtml(id)}"
                                aria-label="Pilih ${escapeHtml(medicineName)}"
                                ${isChecked ? 'checked' : ''}
                                ${isAlreadyAdded ? 'disabled' : ''}>
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

            modalBody.scrollTop(modalBody.scrollTop() + row.position().top - 96);
        });
        // ==== End Inisiasi Variable Global dan Function ====

        // =================== Inisiasi Modal ===================
        // --- Reset modal ketika dibuka
        $('#pembelianModal').on('show.bs.modal', function() {
            let form = $('#pembelianForm');
            purchaseSaveInProgress = false;
            $('#pembelianModalLabel').text('Form Purchase Order');
            form.trigger('reset');

            $('#submitForm').html('<i class="mdi mdi-content-save-outline"></i> Simpan Purchase Order');
            $('#saveDraftForm').html('<i class="mdi mdi-content-save-move-outline"></i> Simpan sebagai Draft');
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
                .attr('title', 'Surat pesanan mengikuti klasifikasi obat; OOT memiliki prioritas untuk seluruh PO')
                .find('span').text('Cetak Surat Pesanan');
        });

        $('#btnPrintPDF').on('click', function() {
            if (!detailPurchaseOrderId || $(this).prop('disabled') || detailPrintDocuments.length < 1) {
                return;
            }

            let blockedDocuments = 0;

            detailPrintDocuments.forEach(function(document) {
                const printWindow = window.open(document.url, '_blank');

                if (printWindow) {
                    printWindow.opener = null;
                } else {
                    blockedDocuments++;
                }
            });

            if (blockedDocuments > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sebagian dokumen diblokir browser',
                    text: 'Izinkan pop-up untuk situs ini agar seluruh surat pesanan dapat dibuka dari satu tombol.'
                });
            }
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

            const emptyRows = $('#detail-wrapper .detail-item').filter(function() {
                return !String($(this).find('select[name="obat_id[]"]').val() || '');
            }).toArray();
            const targetRows = [];

            setMedicinePickerOpen(false);

            selectedItems.forEach(function(item, index) {
                let row;

                if (emptyRows[index]) {
                    row = $(emptyRows[index]);
                } else {
                    $('#detail-wrapper').append(detailItemTemplate());
                    row = $('#detail-wrapper .detail-item').last();
                }

                targetRows.push(row);
            });

            const addedCount = selectedItems.length;
            selectedMedicineIds.clear();
            refreshDetailNumbers();
            hitungTotal();

            preloadMedicineUnits(selectedItems.map(item => item.id))
                .catch(function() {
                    // Baris tetap dapat dipakai; pemuatan satuan akan dicoba kembali per pilihan.
                })
                .then(function() {
                    selectedItems.forEach(function(item, index) {
                        populateMedicineSelect(
                            targetRows[index].find('select[name="obat_id[]"]'),
                            item.id
                        );
                    });

                    refreshDetailNumbers();
                    hitungTotal();

                    Swal.fire({
                        icon: 'success',
                        title: `${addedCount.toLocaleString('id-ID')} obat masuk ke rincian`,
                        toast: true,
                        position: 'top-end',
                        timer: 2200,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    });
                });
        });

        // --- Tambah baris detail obat baru
        $(document).on('click', '#addDetail', function() {
            let newDetail = detailItemTemplate();
            $('#detail-wrapper').append(newDetail);
            refreshDetailNumbers();
            hitungTotal();
            // 3. Ambil select obat di BARIS BARU saja
            let $newSelect = $('#detail-wrapper .detail-item').last().find('select[name="obat_id[]"]');

            // 4. Jalankan load obat + Select2 hanya untuk select BARU
            loadObatInto($newSelect);
        });

        // --- Hapus baris detail obat
        $(document).on('click', '.remove-detail', function() {
            if ($('#detail-wrapper .detail-item').length > 1) {
                $(this).closest('.detail-item').remove();
                refreshDetailNumbers();
                hitungTotal();
                renderMedicinePicker();
            }
        });

        function normalizedDiscount(value) {
            return Math.round(Math.min(100, Math.max(0, Number(value) || 0)) * 100) / 100;
        }

        function roundPurchaseMoney(value) {
            return Math.round((Math.max(0, value) + Number.EPSILON) * 100) / 100;
        }

        function selectedPurchaseConversion(row) {
            const conversion = parseFloat(row.find('.satuan-select option:selected').data('konversi'));

            return Number.isFinite(conversion) && conversion > 0 ? conversion : 1;
        }

        function recalculatePurchaseRow(row) {
            const qty = parseFloat(row.find('[name="qty[]"]').val()) || 0;
            const pricePerUnit = parseFloat(row.find('.harga_estimasi_satuan').val()) || 0;
            const conversion = selectedPurchaseConversion(row);
            const purchaseUnitPrice = pricePerUnit * conversion;
            const discounts = [
                normalizedDiscount(row.find('[name="diskon_1[]"]').val()),
                normalizedDiscount(row.find('[name="diskon_2[]"]').val()),
                normalizedDiscount(row.find('[name="diskon_3[]"]').val())
            ];
            const ppn = normalizedDiscount(row.find('[name="ppn[]"]').val());
            let subtotal = qty * purchaseUnitPrice;

            discounts.forEach(function(discount) {
                subtotal *= 1 - (discount / 100);
            });
            subtotal = roundPurchaseMoney(subtotal);
            subtotal = roundPurchaseMoney(subtotal * (1 + (ppn / 100)));

            // Backend dan modul penerimaan tetap menerima harga per satuan beli.
            row.find('.harga_estimasi').val(purchaseUnitPrice.toFixed(2));
            row.find('.subtotal').val(subtotal.toFixed(2));
        }

        // --- Hitung subtotal berdasarkan qty, harga satuan beli, Diskon 1–3, lalu PPN
        $(document).on('input', '.harga_estimasi_satuan, [name="qty[]"], .purchase-discount, .purchase-tax, .purchase-additional-cost', function() {
            const row = $(this).closest('.detail-item');

            if (row.length) {
                recalculatePurchaseRow(row);
            }

            hitungTotal();
        });

        // --- Fungsi hitung total estimasi keseluruhan
        function hitungTotal() {
            let medicineSubtotal = 0;
            let totalQty = 0;
            $('.subtotal').each(function() {
                medicineSubtotal += parseFloat($(this).val()) || 0;
            });
            $('[name="qty[]"]').each(function() {
                totalQty += parseFloat($(this).val()) || 0;
            });
            let insuranceCost = parseFloat($('[name="biaya_asuransi"]').val()) || 0;
            let shippingCost = parseFloat($('[name="biaya_pengiriman"]').val()) || 0;
            let additionalCost = insuranceCost + shippingCost;
            let total = medicineSubtotal + additionalCost;
            let itemCount = $('.detail-item').length;

            refreshDetailNumbers();
            $('#total_estimasi').text(formatRupiah(total));
            $('#total_estimasi_input').val(total.toFixed(2));
            $('#purchaseModalLineCount, #purchaseModalItemCount').text(itemCount.toLocaleString('id-ID'));
            $('#purchaseModalQtyCount').text(totalQty.toLocaleString('id-ID'));
            $('#purchaseModalMedicineSubtotal').text(formatRupiah(medicineSubtotal));
            $('#purchaseModalAdditionalCost').text(formatRupiah(additionalCost));
        }

        // --- Get data distributor
        DistributorSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
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
                .filter(item => item && item.satuan)
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

            const requestKey = `batch-${++unitRequestSequence}`;
            pendingUnitRequests.add(requestKey);
            updateUnitLoadingState();

            const request = new Promise(function(resolve, reject) {
                $.ajax({
                    url: "{{ route("pembelian.getKonversiSatuan") }}",
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        obat_ids: missingIds
                    },
                    timeout: 20000,
                    success: function(data) {
                        missingIds.forEach(function(id) {
                            cacheMedicineUnits(id, data?.[id] || []);
                        });
                        resolve();
                    },
                    error: reject,
                    complete: function() {
                        missingIds.forEach(id => medicineUnitRequests.delete(id));
                        pendingUnitRequests.delete(requestKey);
                        updateUnitLoadingState();
                    }
                });
            });

            missingIds.forEach(id => medicineUnitRequests.set(id, request));
            return request;
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
            updateCurrentStock(row, medicine);

            if (!$('#medicinePicker').hasClass('d-none')) {
                renderMedicinePicker();
            }

            $satuanSelect
                .html('<option value="">-- Pilih Satuan --</option>')
                .prop('disabled', true);

            if (!preservePurchaseValues) {
                row.find('.harga_estimasi_satuan, .harga_estimasi, .subtotal').val(0);
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

                    if (!list.length) {
                        const unitName = medicine?.satuan?.nama || 'Satuan dasar';

                        $satuanSelect
                            .html(`<option value="0" data-konversi="1">${escapeHtml(unitName)} (dasar)</option>`)
                            .prop('disabled', false)
                            .val('0');
                    } else {
                        let unitOptions = '<option value="">-- Pilih Satuan --</option>';

                        list.forEach(function(item) {
                            unitOptions += `<option value="${escapeHtml(item.id)}" data-konversi="${escapeHtml(item.konversi)}">${escapeHtml(medicineUnitLabel(item, medicine))}</option>`;
                        });

                        $satuanSelect.html(unitOptions).prop('disabled', false);

                        const previousUnit = String(row.data('satuan-terpilih') || '');
                        const hasPreviousUnit = previousUnit &&
                            $satuanSelect.find(`option[value="${previousUnit}"]`).length > 0;
                        const defaultUnit = list.find(item => Number(item.is_default || 0) === 1) || list[0];

                        $satuanSelect.val(hasPreviousUnit ? previousUnit : String(defaultUnit.id));
                    }

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
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(Number(angka) || 0);
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

        function updatePurchaseSummary(summary, recordsTotal) {
            let total = Number(summary.total ?? recordsTotal) || 0;
            let draft = Number(summary.draft) || 0;
            let waiting = Number(summary.waiting_approval) || 0;
            let pending = Number(summary.pending ?? (draft + waiting)) || 0;
            let approved = Number(summary.approved) || 0;
            let completed = Number(summary.selesai) || 0;
            let rejected = Number(summary.rejected) || 0;

            $('#purchaseTotalCount, #purchaseAllFilterCount').text(total.toLocaleString('id-ID'));
            $('#purchasePendingCount').text(pending.toLocaleString('id-ID'));
            $('#purchaseDraftFilterCount').text(draft.toLocaleString('id-ID'));
            $('#purchaseWaitingFilterCount').text(waiting.toLocaleString('id-ID'));
            $('#purchaseApprovedCount, #purchaseApprovedFilterCount').text(approved.toLocaleString('id-ID'));
            $('#purchaseCompletedCount, #purchaseCompletedFilterCount').text(completed.toLocaleString('id-ID'));
            $('#purchaseRejectedFilterCount').text(rejected.toLocaleString('id-ID'));
            $('#purchaseTotalValue').text(formatRupiah(summary.total_estimasi));
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

        let PembelianTable = $('#tablePembelian').DataTable({
            processing: true,
            serverSide: true,
            responsive: window.matchMedia('(min-width: 768px)').matches,
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
                },
                dataSrc: function(response) {
                    updatePurchaseSummary(response.summary || {}, response.recordsTotal);
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
                        let status = String(row.status || '').toLowerCase();

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
                    render: function(data) {
                        return `
                            <span class="purchase-po-number">
                                <i class="mdi mdi-file-document-outline"></i>
                                ${escapeHtml(data)}
                            </span>
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
                    render: function(data) {
                        let distributor = escapeHtml(data);
                        return `
                            <span class="purchase-badge is-distributor" title="${distributor}">
                                <i class="mdi mdi-truck-delivery-outline"></i>
                                ${distributor}
                            </span>
                        `;
                    }
                },
                {
                    data: 'item_count',
                    name: 'item_count',
                    render: function(data, type, row) {
                        if (type !== 'display') {
                            return Number(data) || 0;
                        }

                        return `
                            <span class="purchase-item-qty">
                                <strong>${(Number(data) || 0).toLocaleString('id-ID')} item</strong>
                                <small>${formatStockQuantity(row.total_qty)} qty</small>
                            </span>
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
                    render: function(data) {
                        let status = String(data || '').toLowerCase();
                        let statusClass =
                            (status === 'approved' || status === 'selesai') ? 'is-approved' :
                            (status === 'draft' || status === 'waiting_approval') ? 'is-draft' :
                            status === 'diterima_sebagian' ? 'is-partial' :
                            status === 'rejected' ? 'is-rejected' :
                            'is-other';
                        let statusLabel =
                            status === 'approved' ? 'Disetujui' :
                            status === 'diterima_sebagian' ? 'Diterima Sebagian' :
                            status === 'selesai' ? 'Selesai' :
                            status === 'draft' ? 'Draft' :
                            status === 'waiting_approval' ? 'Menunggu Approval' :
                            status === 'rejected' ? 'Ditolak' :
                            (data || '-');
                        let statusIcon =
                            status === 'approved' ? 'mdi-check-circle-outline' :
                            status === 'diterima_sebagian' ? 'mdi-progress-check' :
                            status === 'selesai' ? 'mdi-package-variant-closed-check' :
                            (status === 'draft' || status === 'waiting_approval') ?
                            'mdi-file-clock-outline' :
                            status === 'rejected' ? 'mdi-close-circle-outline' :
                            'mdi-help-circle-outline';

                        return `
                            <span class="purchase-status ${statusClass}">
                                <i class="mdi ${statusIcon}"></i>
                                ${escapeHtml(statusLabel)}
                            </span>
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
                targets: [0, 11],
                className: 'text-center'
            }],
            drawCallback: function() {
                let table = $('#tablePembelian');
                const mobileLabels = [
                    'No',
                    'Approval',
                    'Nomor PO',
                    'Tanggal PO',
                    'Branch',
                    'Distributor',
                    'Item / Qty',
                    'Total Estimasi',
                    'Status',
                    'Catatan',
                    'User',
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
                emptyTable: 'Belum ada purchase order.',
                zeroRecords: 'Purchase order yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let purchaseSearchTimer;

        $('#searchPembelian').on('input', function() {
            let searchValue = this.value;
            $(this).closest('.purchase-search').toggleClass('has-value', Boolean(searchValue));
            clearTimeout(purchaseSearchTimer);
            purchaseSearchTimer = setTimeout(function() {
                PembelianTable.search(searchValue).draw();
            }, 250);
        });

        $('#clearPurchaseSearch').on('click', function() {
            $('#searchPembelian').val('').trigger('input').focus();
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
            purchaseDateStart = '';
            purchaseDateEnd = '';
            purchaseDatePicker.clear();
            $('#purchaseDatePreset').val('');
            $('#purchaseDateRange').closest('.purchase-date-input').removeClass('has-value');
            PembelianTable.ajax.reload();
        });

        $('#purchaseDatePreset').on('change', function() {
            let preset = this.value;

            if (!preset) {
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
                .attr('title', isOpen ? 'Tutup filter' : 'Buka filter')
                .attr('aria-label', isOpen ? 'Tutup filter' : 'Buka filter')
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
            if (String($('input[name="no_po"]').val() || '').trim() !== '') {
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

            return generateAutomaticPurchaseOrderNumber();
        }

        function waitForPendingUnitRequests() {
            const requests = Array.from(new Set(medicineUnitRequests.values()));

            return requests.length > 0 ? Promise.allSettled(requests) : Promise.resolve();
        }

        function showPurchaseSaveError(xhr) {
            if (xhr?.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
                const errorMessages = [];

                $('#pembelianForm').find('.invalid-feedback').text('');
                $('#pembelianForm').find('.form-control, .form-select').removeClass('is-invalid');

                Object.keys(errors).forEach(function(key) {
                    const messages = errors[key];
                    const parts = key.split('.');
                    const field = parts[0];
                    const index = parts[1];

                    errorMessages.push(messages[0]);

                    if (index !== undefined) {
                        const row = $('#detail-wrapper .detail-item').eq(Number(index));
                        const validationField = field === 'harga_estimasi'
                            ? '.harga_estimasi_satuan'
                            : `[name="${field}[]"]`;

                        row.find(validationField).addClass('is-invalid');
                    } else {
                        $(`[name="${field}"]`).addClass('is-invalid');
                    }
                });

                if (errorMessages.length === 0) {
                    errorMessages.push(xhr.responseJSON?.message || 'Periksa kembali data purchase order.');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errorMessages.join('<br>'),
                    toast: true,
                    position: 'top-end',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Purchase order gagal disimpan',
                text: xhr?.responseJSON?.message || 'Terjadi kesalahan saat menyimpan purchase order.',
            });
        }

        function submitPurchaseOrder(saveAsDraft = false) {
            if (purchaseSaveInProgress) {
                return;
            }

            purchaseSaveInProgress = true;
            updateUnitLoadingState();

            Promise.all([
                    ensurePurchaseOrderNumber(),
                    waitForPendingUnitRequests()
                ])
                .then(function() {
                    const purchaseOrderId = $('#pembelian_id').val();
                    const url = purchaseOrderId ?
                        "{{ route("pembelian.update", ":id") }}".replace(':id', purchaseOrderId) :
                        "{{ route("pembelian.store") }}";
                    const method = purchaseOrderId ? 'PUT' : 'POST';

                    $('#detail-wrapper .detail-item').each(function() {
                        recalculatePurchaseRow($(this));
                    });
                    hitungTotal();

                    const formData = $('#pembelianForm').serializeArray();

                    formData.push({
                        name: 'save_as_draft',
                        value: saveAsDraft ? '1' : '0'
                    });

                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: url,
                            method: method,
                            data: $.param(formData),
                            success: resolve,
                            error: reject
                        });
                    });
                })
                .then(function(response) {
                    if (response.status !== 'success') {
                        throw {
                            status: 422,
                            responseJSON: response
                        };
                    }

                    $('#pembelian_id').val(response.data?.id || $('#pembelian_id').val());
                    $('#pembelianModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    });

                    PembelianTable.ajax.reload(null, false);
                })
                .catch(function(xhr) {
                    showPurchaseSaveError(xhr);
                })
                .finally(function() {
                    purchaseSaveInProgress = false;
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

            editMode = true;

            $.ajax({
                url: "{{ route("pembelian.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {

                    let header = response;
                    let detail = response.details ?? [];

                    $('#pembelianModal').modal('show');
                    $('#pembelianModalLabel').text('Edit Purchase Order');
                    $('#submitForm').html(
                        '<i class="mdi mdi-content-save-edit-outline"></i> Update Purchase Order'
                    );

                    $('#pembelian_id').val(header.id);
                    $('input[name="no_po"]').val(header.no_po);
                    $('#distributor_id').val(header.distributor_id).trigger('change');

                    $('input[name="tanggal"]').val(
                        moment(header.tanggal_po).format('DD-MM-YYYY')
                    );

                    $('textarea[name="catatan"]').val(header.catatan ?? '');
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
                    const ootDocument = {
                        name: 'OOT',
                        count: ootCount,
                        buttonClass: 'btn-outline-success',
                        url: "{{ route("pembelian.suratPesananOot", ":id") }}".replace(':id', po.id)
                    };

                    detailPrintDocuments = ootCount > 0
                        ? [ootDocument]
                        : [
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
                            .attr('title', `Buka surat otomatis untuk: ${documentNames}`)
                            .find('span').text(`Cetak ${detailPrintDocuments.length} Jenis Surat (${totalOrderItems} item)`);
                    }

                    // Header
                    $('#detail_no_po').val(po.no_po);
                    $('#detail_distributor').val(po.distributor_name ?? 'Tidak diketahui');
                    $('#detail_tanggal_po').val(po.tanggal_po ? moment(po.tanggal_po).format('DD-MM-YYYY') : '-');
                    $('#detail_catatan').val(po.catatan ?? '-');

                    // Kosongkan tabel
                    $('#detailObatTable tbody').empty();
                    // Tampilkan jumlah item
                    $('#detail_total_item').text(detail.length);


                    // Detail obat
                    let medicineSubtotal = 0;
                    detail.forEach(item => {
                        const unitName = item.satuan_konversi?.satuan?.nama ?? '-';

                        const regularBadge = item.is_regular
                            ? `<span class="badge bg-info bg-opacity-10 text-info-emphasis ms-2" title="Obat selain Narkotika, Psikotropika, dan Prekursor">Reguler</span>`
                            : '';
                        const narcoticBadge = item.is_narcotic
                            ? `<span class="badge bg-danger bg-opacity-10 text-danger ms-2" title="${escapeHtml(item.narcotic_classification ?? 'Narkotika')}">Narkotika</span>`
                            : '';
                        const psychotropicBadge = item.is_psychotropic
                            ? `<span class="badge bg-primary bg-opacity-10 text-primary ms-2" title="${escapeHtml(item.psychotropic_classification ?? 'Psikotropika')}">Psikotropika</span>`
                            : '';
                        const precursorBadge = item.is_precursor
                            ? `<span class="badge bg-warning bg-opacity-10 text-warning-emphasis ms-2" title="${escapeHtml(item.precursor_classification ?? 'Prekursor')}">Prekursor</span>`
                            : '';
                        const ootBadge = item.is_oot
                            ? `<span class="badge bg-success bg-opacity-10 text-success ms-2" title="${escapeHtml(item.oot_classification ?? 'Klasifikasi OOT dari master obat')}">OOT</span>`
                            : '';

                        $('#detailObatTable tbody').append(`
                    <tr>
                        <td data-label="Nama Obat">${escapeHtml(item.nama_obat ?? '-')}${regularBadge}${narcoticBadge}${psychotropicBadge}${precursorBadge}${ootBadge}</td>
                        <td data-label="Satuan">${escapeHtml(unitName)}</td>
                        <td data-label="Qty">${Number(item.qty ?? 0).toLocaleString('id-ID')}</td>
                        <td data-label="Harga Estimasi">Rp ${Number(item.harga_estimasi ?? 0).toLocaleString('id-ID')}</td>
                        <td data-label="Diskon 1">${Number(item.diskon_1 ?? 0).toLocaleString('id-ID')}%</td>
                        <td data-label="Diskon 2">${Number(item.diskon_2 ?? 0).toLocaleString('id-ID')}%</td>
                        <td data-label="Diskon 3">${Number(item.diskon_3 ?? 0).toLocaleString('id-ID')}%</td>
                        <td data-label="PPN">${Number(item.ppn ?? 0).toLocaleString('id-ID')}%</td>
                        <td data-label="Subtotal + PPN">Rp ${Number(item.subtotal ?? 0).toLocaleString('id-ID')}</td>
                    </tr>
                `);
                        medicineSubtotal += Number(item.subtotal ?? 0);
                    });

                    // Total Estimasi
                    $('#detail_subtotal_obat').text(formatRupiah(medicineSubtotal));
                    $('#detail_biaya_asuransi').text(formatRupiah(po.biaya_asuransi));
                    $('#detail_biaya_pengiriman').text(formatRupiah(po.biaya_pengiriman));
                    $('#detail_total_estimasi').text(formatRupiah(po.total_estimasi));

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

    });
</script>
