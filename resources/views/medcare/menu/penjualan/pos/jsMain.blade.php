<script>
    $(document).ready(function() {
        document.body.classList.add('pos-fullscreen-mode');

        const transactionTypes = @json($transactionTypes);
        const paymentMethods = @json($paymentMethods);
        const urls = {
            products: '{{ route("penjualan.pos.products") }}',
            quote: '{{ route("penjualan.pos.quote") }}',
            draft: '{{ route("penjualan.pos.draft") }}',
            complete: '{{ route("penjualan.pos.complete") }}',
            show: '{{ route("penjualan.pos.show", ":id") }}',
            receipt: '{{ route("penjualan.pos.receipt", ":id") }}'
        };

        let selectedProduct = null;
        let currentQuote = null;
        let cart = [];
        let quoteTimer = null;
        let lastReceiptId = null;
        let activeCompoundGroup = 'R/ 1';
        let committedTransactionType = 'penjualan_bebas';
        let prescriptionModalReturnType = null;
        let prescriptionTypeApplied = false;
        let prescriptionWorkspaceMounted = false;
        let productSearchInModal = false;
        let prescriptionFlowCloseReason = null;
        let prescriptionPaymentMode = false;
        let lastTotals = {
            grandTotal: 0,
            paidTotal: 0,
            diff: 0
        };

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
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

        const compoundSharedFields = ['aturan_pakai', 'waktu_konsumsi', 'durasi_hari', 'keterangan'];

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

        function synchronizeCompoundGroup(group) {
            const items = compoundGroupItems(group);
            const reference = items[0];

            if (!reference) {
                return;
            }

            items.slice(1).forEach(item => {
                compoundSharedFields.forEach(field => {
                    item[field] = reference[field] || '';
                });
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

            return hasSigna
                && Boolean(String(item.racikan_group || '').trim())
                && Boolean(String(item.dosis_komponen || '').trim());
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
                return `
                    <button type="button" class="pos-compound-summary-chip ${active ? 'is-active' : ''}" data-compound-group="${escapeHtml(group)}">
                        <b>${escapeHtml(group)}</b>
                        <span>${count > 0 ? `${count} komponen` : 'belum ada obat'}</span>
                    </button>
                `;
            }).join('');

            $('#compoundGroupSummary').html(summary || '<span class="pos-compound-summary-empty">Belum ada kelompok racikan.</span>');
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

        function updateFlow() {
            const hasItems = cart.length > 0;
            const canSettle = hasItems && (isCreditTransaction() || lastTotals.diff >= -0.01) && !cart.some(item => !item.is_available);
            const hasSelection = Boolean(selectedProduct) || hasItems;

            $('#posFlowProduct, #posFlowCart, #posFlowPayment').removeClass('is-active is-complete');
            $('#posFlowProduct').toggleClass('is-complete', hasSelection).toggleClass('is-active', !hasSelection);
            $('#posFlowCart').toggleClass('is-complete', hasItems).toggleClass('is-active', hasSelection && !hasItems);
            $('#posFlowPayment').toggleClass('is-active', hasItems);
            $('#posFlowProgress').css('width', `${canSettle ? 96 : (hasItems ? 69 : (hasSelection ? 31 : 8))}%`);

            $('#headerCartCount').text(formatNumber(cart.length));
            $('#headerGrandTotal').text(formatCurrency(lastTotals.grandTotal));
            $('.pos-quick-payment[data-amount="exact"] span').text(
                lastTotals.grandTotal > 0 ? `Bayar pas · ${formatCurrency(lastTotals.grandTotal)}` : 'Bayar pas'
            );

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

            $('.pos-selection-block').toggleClass('has-selection', hasSelection);
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
                        q: params.term || ''
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
                <div class="pos-product-meta">
                    <span><small>Kategori</small>${escapeHtml(selectedProduct.kategori)}</span>
                    <span><small>Golongan</small>${escapeHtml(selectedProduct.golongan)}</span>
                    <span><small>Main/Sub</small>${escapeHtml(selectedProduct.main_golongan)} / ${escapeHtml(selectedProduct.sub_golongan)}</span>
                    <span><small>Sediaan</small>${escapeHtml(selectedProduct.sediaan)}</span>
                    <span><small>Pabrikan</small>${escapeHtml(selectedProduct.pabrikan)}</span>
                    <span><small>Batch FEFO</small>${batch ? escapeHtml(batch.no_batch) + ' | ED ' + escapeHtml(batch.expired_date || '-') : '-'}</span>
                </div>
                <div class="mt-3 small text-muted">
                    <strong>Indikasi:</strong> ${escapeHtml(selectedProduct.indikasi || '-')}<br>
                    <strong>Kandungan:</strong> ${escapeHtml(selectedProduct.komposisi || '-')}<br>
                    <strong>Dosis referensi:</strong> ${escapeHtml(selectedProduct.dosis || '-')}
                </div>
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
            currentQuote = null;
            $('#quoteBox').removeClass('is-loading');
            $('#quotePrice').text(formatCurrency(0));
            $('#quoteStock').text('0');
            $('#quoteBatch').text('-');
            $('#quoteFefoList').html('<span class="pos-fefo-placeholder">Alokasi batch akan tampil setelah produk dan jumlah dipilih.</span>');
            $('#addToCartBtn').prop('disabled', true);
            updateCatalogFlow();
        }

        function requestQuote() {
            if (!selectedProduct || !$('#unitSelect').val()) {
                resetQuote();
                return;
            }

            $('#quoteBox').addClass('is-loading');

            $.get(urls.quote, {
                obat_id: selectedProduct.id,
                satuan_id: $('#unitSelect').val(),
                qty: $('#qtyInput').val() || 1
            }).done(function(response) {
                currentQuote = response;
                renderQuote(response);
            }).fail(function(xhr) {
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

        $('#addToCartBtn').on('click', function() {
            if (!selectedProduct || !currentQuote || !currentQuote.is_available) {
                Swal.fire('Stok tidak cukup', 'Pilih obat dan qty yang tersedia terlebih dahulu.', 'warning');
                return;
            }

            const compound = isCompoundPrescription();
            const compoundTemplate = compoundGroupItems(activeCompoundGroup)[0] || null;
            const cartItem = {
                uid: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                obat_id: selectedProduct.id,
                kode_obat: selectedProduct.kode_obat,
                nama_obat: selectedProduct.nama_obat,
                satuan_id: currentQuote.satuan_id,
                satuan: currentQuote.satuan,
                satuan_stok: currentQuote.satuan_stok,
                konversi: Number(currentQuote.konversi) || 1,
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
                racikan_group: compound ? activeCompoundGroup : '',
                dosis_komponen: '',
                keterangan: compound ? (compoundTemplate?.keterangan || '') : ''
            };
            const matchingItem = isPrescriptionTransaction() ? null : cart.find(item => item.obat_id === cartItem.obat_id && item.satuan_id === cartItem.satuan_id && Number(item.diskon_percent) === 0 && Number(item.diskon_nominal) === 0);

            if (matchingItem) {
                matchingItem.qty += cartItem.qty;
                refreshCartQuote(matchingItem);
            } else {
                cart.push(cartItem);
                renderCart();
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

        function prescriptionItemEditor(item) {
            if (!isPrescriptionTransaction()) {
                return '';
            }

            const compound = isCompoundPrescription();
            const isComplete = isPrescriptionItemComplete(item);

            if (compound) {
                return `
                    <tr class="pos-prescription-detail-row">
                        <td colspan="7">
                            <div class="pos-prescription-item-editor ${isComplete ? 'is-complete' : ''}">
                                <div class="pos-prescription-item-head">
                                    <span><i class="mdi mdi-flask-outline"></i></span>
                                    <span>
                                        <strong>Komponen racikan &middot; ${escapeHtml(item.nama_obat)}</strong>
                                        <small>Tentukan kelompok tujuan dan dosis bahan khusus untuk komponen ini.</small>
                                    </span>
                                    <span class="pos-prescription-item-state"><i class="mdi ${isComplete ? 'mdi-check-circle' : 'mdi-alert-circle-outline'}"></i>${isComplete ? 'Komponen lengkap' : 'Isi dosis komponen'}</span>
                                </div>
                                <div class="pos-prescription-item-grid is-compound-component">
                                    <label>
                                        <span>Masuk kelompok <b>*</b></span>
                                        <select class="form-select form-select-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="racikan_group">
                                            ${compoundGroupOptions(item.racikan_group)}
                                        </select>
                                    </label>
                                    <label>
                                        <span>Dosis bahan / komponen <b>*</b></span>
                                        <input type="text" class="form-control form-control-sm cart-prescription-input" data-id="${escapeHtml(item.uid)}" data-field="dosis_komponen" value="${escapeHtml(item.dosis_komponen || '')}" placeholder="Contoh: 250 mg atau 1/2 tablet">
                                    </label>
                                    <div class="pos-compound-component-help">
                                        <i class="mdi mdi-information-outline"></i> Aturan pakai diisi satu kali pada kartu kelompok ${escapeHtml(normalizeCompoundGroup(item.racikan_group))} di atas.
                                    </div>
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
            const complete = items.length > 0 && items.every(isPrescriptionItemComplete);
            const active = normalizeCompoundGroup(group) === activeCompoundGroup;

            return `
                <tr class="pos-compound-group-row">
                    <td colspan="7">
                        <section class="pos-compound-group-card ${complete ? 'is-complete' : ''}" data-group-card="${escapeHtml(group)}">
                            <div class="pos-compound-group-head">
                                <span class="pos-compound-group-mark">${escapeHtml(group)}</span>
                                <span class="pos-compound-group-copy">
                                    <strong>Etiket bersama untuk ${items.length} komponen</strong>
                                    <small>Aturan pakai berikut berlaku untuk semua obat di kelompok ini.</small>
                                </span>
                                <button type="button" class="pos-use-compound-group ${active ? 'is-active' : ''}" data-compound-group="${escapeHtml(group)}">
                                    <i class="mdi ${active ? 'mdi-check-circle' : 'mdi-plus-circle-outline'}"></i> ${active ? 'Kelompok aktif' : 'Tambah obat ke sini'}
                                </button>
                            </div>
                            <div class="pos-compound-group-fields">
                                <label>
                                    <span>Aturan pakai / signa kelompok <b>*</b></span>
                                    <input type="text" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="aturan_pakai" value="${escapeHtml(reference.aturan_pakai || '')}" placeholder="Contoh: 3 x sehari 1 bungkus">
                                </label>
                                <label>
                                    <span>Waktu konsumsi</span>
                                    <select class="form-select form-select-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="waktu_konsumsi">${consumptionTimeOptions(reference.waktu_konsumsi)}</select>
                                </label>
                                <label>
                                    <span>Durasi (hari)</span>
                                    <input type="number" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="durasi_hari" value="${escapeHtml(reference.durasi_hari || '')}" min="1" max="3650" placeholder="Opsional">
                                </label>
                                <label>
                                    <span>Catatan etiket</span>
                                    <input type="text" class="form-control form-control-sm compound-group-input" data-group="${escapeHtml(group)}" data-field="keterangan" value="${escapeHtml(reference.keterangan || '')}" placeholder="Contoh: Habiskan / bila perlu">
                                </label>
                            </div>
                            <div class="pos-signa-quick" aria-label="Template aturan pakai kelompok ${escapeHtml(group)}">
                                <span>Isi cepat:</span>
                                ${['1 x sehari 1 dosis', '2 x sehari 1 dosis', '3 x sehari 1 dosis', 'Bila perlu'].map(value => `
                                    <button type="button" class="pos-signa-chip" data-group="${escapeHtml(group)}" data-value="${escapeHtml(value)}">${escapeHtml(value)}</button>
                                `).join('')}
                            </div>
                        </section>
                    </td>
                </tr>
            `;
        }

        function renderCart() {
            if (cart.length === 0) {
                $('#cartBody').html(`
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

            const compound = isCompoundPrescription();
            if (compound) {
                compoundGroups().forEach(synchronizeCompoundGroup);
            }
            const displayCart = compound
                ? [...cart].sort((left, right) => {
                    const groupDiff = compoundGroupNumber(left.racikan_group) - compoundGroupNumber(right.racikan_group);
                    return groupDiff || normalizeCompoundGroup(left.racikan_group).localeCompare(normalizeCompoundGroup(right.racikan_group), 'id');
                })
                : cart;
            let renderedGroup = null;

            $('#cartBody').html(displayCart.map(item => {
                const group = compound ? normalizeCompoundGroup(item.racikan_group) : '';
                const groupItems = compound ? compoundGroupItems(group) : [];
                const groupHeader = compound && group !== renderedGroup ? compoundGroupEditor(group, groupItems) : '';
                const componentPosition = compound ? groupItems.findIndex(component => component.uid === item.uid) + 1 : 0;
                renderedGroup = group;

                return `
                ${groupHeader}
                <tr class="${item.is_available ? 'is-available' : 'is-unavailable'}">
                    <td>
                        <div class="pos-item-cell">
                            <span class="pos-item-symbol"><i class="mdi mdi-pill"></i></span>
                            <span class="pos-item-copy">
                                <strong title="${escapeHtml(item.nama_obat)}">${escapeHtml(item.nama_obat)}</strong>
                                <small>${escapeHtml(item.kode_obat)}</small>
                                <span class="pos-item-badges">
                                    <span>${escapeHtml(item.satuan)}</span>
                                    <span>Konversi ${formatNumber(item.konversi)}×</span>
                                    ${compound ? `<span class="pos-item-group-badge">${escapeHtml(group)} &middot; Komponen ${componentPosition}/${groupItems.length}</span>` : ''}
                                </span>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="pos-qty-stepper">
                            <button type="button" class="cart-qty-step" data-id="${escapeHtml(item.uid)}" data-delta="-1" title="Kurangi jumlah" aria-label="Kurangi ${escapeHtml(item.nama_obat)}"><i class="mdi mdi-minus"></i></button>
                            <input type="number" class="form-control form-control-sm cart-qty" data-id="${escapeHtml(item.uid)}" min="0.01" step="0.01" value="${item.qty}" aria-label="Jumlah ${escapeHtml(item.nama_obat)}">
                            <button type="button" class="cart-qty-step" data-id="${escapeHtml(item.uid)}" data-delta="1" title="Tambah jumlah" aria-label="Tambah ${escapeHtml(item.nama_obat)}"><i class="mdi mdi-plus"></i></button>
                        </div>
                        <small class="pos-stock-caption"><i class="mdi mdi-package-variant"></i> ${formatNumber(item.qty_stok)} ${escapeHtml(item.satuan_stok)} stok</small>
                    </td>
                    <td>
                        <span class="pos-money">${formatCurrency(item.harga_jual)}</span>
                        <small class="pos-money-note">per ${escapeHtml(item.satuan)}</small>
                    </td>
                    <td>
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
                    <td>${fefoCell(item)}</td>
                    <td>
                        <span class="pos-money pos-line-total">${formatCurrency(itemNet(item))}</span>
                        <small class="pos-money-note">setelah diskon</small>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm pos-remove-item remove-cart-item" data-id="${escapeHtml(item.uid)}" title="Hapus ${escapeHtml(item.nama_obat)}" aria-label="Hapus ${escapeHtml(item.nama_obat)}">
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

        $(document).on('input change', '.cart-prescription-input', function() {
            const item = findCartItem($(this).data('id'));
            const field = String($(this).data('field') || '');

            if (!item || !['aturan_pakai', 'waktu_konsumsi', 'durasi_hari', 'racikan_group', 'dosis_komponen', 'keterangan'].includes(field)) {
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
                }

                activeCompoundGroup = targetGroup;
                renderCart();
                return;
            }

            item[field] = field === 'durasi_hari'
                ? (this.value === '' ? '' : Math.max(1, Number(this.value) || 1))
                : this.value;
            updatePrescriptionReadiness();

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

            const value = field === 'durasi_hari'
                ? (this.value === '' ? '' : Math.max(1, Number(this.value) || 1))
                : this.value;
            const items = compoundGroupItems(group);
            items.forEach(item => {
                item[field] = value;
            });

            const complete = items.length > 0 && items.every(isPrescriptionItemComplete);
            $(this).closest('.pos-compound-group-card').toggleClass('is-complete', complete);
            updatePrescriptionReadiness();

            if (event.type === 'change') {
                renderCart();
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
            activeCompoundGroup = nextCompoundGroup();
            renderCart();
            $('#productSearch').select2('open');
        });

        $(document).on('click', '.pos-compound-summary-chip, .pos-use-compound-group', function() {
            activeCompoundGroup = normalizeCompoundGroup($(this).data('compound-group'));
            renderCart();
        });

        $(document).on('click', '.remove-cart-item', function() {
            cart = cart.filter(item => item.uid !== String($(this).data('id')));
            renderCart();
        });

        function findCartItem(uid) {
            return cart.find(item => item.uid === String(uid));
        }

        function refreshCartQuote(item) {
            $.get(urls.quote, {
                obat_id: item.obat_id,
                satuan_id: item.satuan_id,
                qty: item.qty
            }).done(function(response) {
                item.qty = Number(response.qty_jual) || item.qty;
                item.qty_stok = Number(response.qty_stok) || item.qty_stok;
                item.harga_jual = Number(response.harga_jual) || item.harga_jual;
                item.subtotal_gross = Number(response.subtotal_gross) || 0;
                item.allocations = response.allocations || [];
                item.is_available = Boolean(response.is_available);
                renderCart();
            }).fail(function(xhr) {
                showAjaxError(xhr, 'Stok item gagal diperiksa ulang.');
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
            const embalase = isCompoundPrescription() ? Math.max(0, Number($('#embalase').val()) || 0) : 0;
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

            checkoutStage.toggleClass('is-short', paymentShort).toggleClass('is-ready', paymentReady);
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

        $('#transactionDiscountPercent, #transactionDiscountNominal, #taxPercent, #embalase').on('input', recalculateTotals);
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
            $('#prescriptionModeSummaryCopy').text(compound
                ? 'Komponen obat disusun dalam kelompok R/ dengan satu etiket bersama.'
                : 'Etiket dan aturan pakai dicatat untuk setiap obat.');

            if (prescription && !$('#tanggalResep').val()) {
                $('#tanggalResep').val(($('#transactionDate').val() || localDateTimeValue()).slice(0, 10));
            }

            if (compound) {
                cart.forEach(item => {
                    if (!String(item.racikan_group || '').trim()) item.racikan_group = activeCompoundGroup;
                });
            }

            $('#prescriptionWorkspaceTitle').text(compound ? 'Resep Racikan' : 'Resep Non Racikan');
            $('#prescriptionWorkspaceCopy').text(compound
                ? 'Pilih kelompok aktif sebelum menambah obat; etiket cukup diisi satu kali per kelompok.'
                : 'Setiap obat berdiri sendiri dan memiliki etiket serta aturan pakainya masing-masing.');
            $('#prescriptionModeNoteTitle').text(compound ? 'Obat dirangkai per kelompok R/' : 'Isi etiket per obat');
            $('#prescriptionModeNoteCopy').text(compound
                ? 'Pilih R/ aktif, tambahkan seluruh komponennya, lalu buat R/ baru bila resep memiliki racikan berikutnya.'
                : 'Tambahkan obat, lalu lengkapi aturan pakai pada kartu obat tersebut. Tidak perlu memilih kelompok R/.');
            $('#prescriptionGuidanceTitle').text(compound ? 'Periksa kelompok racikan sebelum pembayaran' : 'Verifikasi resep sebelum pembayaran');
            $('#prescriptionGuidanceCopy').text(compound
                ? 'Setiap komponen wajib memiliki kelompok dan dosis; aturan pakai wajib diisi sekali pada setiap kelompok.'
                : 'Nama pasien, nomor resep, tanggal, dokter, dan aturan pakai wajib terisi.');
            $('#cartEditorCopy').text(compound
                ? 'Komponen ditampilkan berurutan per kelompok R/ agar racikan mudah diperiksa.'
                : (prescription ? 'Lengkapi etiket pada setiap obat non-racikan.' : 'Ubah jumlah atau diskon langsung pada setiap baris.'));
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
            $('#prescriptionTypeStep').removeClass('d-none');

            const canReturnToWorkspace = isPrescriptionTransaction(committedTransactionType)
                && (prescriptionTypeApplied || isPrescriptionTransaction(prescriptionModalReturnType));
            $('#cancelPrescriptionTypeBtn').text(canReturnToWorkspace ? 'Kembali ke proses resep' : 'Batal');
        }

        function showPrescriptionCashierStep() {
            mountPrescriptionWorkspace();
            $('.pos-transaction-details').prop('open', true);
            $('#prescriptionTypeStep').addClass('d-none');
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

            prescriptionFlowCloseReason = 'finish';
            prescriptionModalInstance().hide();
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
                window.setTimeout(() => {
                    document.getElementById('posPaymentLayout')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    $('.payment-amount').first().trigger('focus');
                }, 180);
            } else if (prescriptionFlowCloseReason === 'cancel') {
                setPrescriptionPaymentMode(false);
                restorePrescriptionReturnType();
            }

            prescriptionModalReturnType = null;
            prescriptionTypeApplied = false;
            prescriptionFlowCloseReason = null;
        });

        $('#setGeneralCustomerBtn').on('click', function() {
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
            const ready = headerComplete
                && cart.length > 0
                && completeItems === cart.length
                && unavailableItems === 0;
            const status = $('#prescriptionReadiness');
            let message = 'Resep lengkap dan siap dilanjutkan ke pembayaran.';

            if (missingHeaderFields.length > 0) {
                message = `Lengkapi ${missingHeaderFields.join(', ')}.`;
            } else if (cart.length === 0) {
                message = 'Tambahkan minimal satu obat ke dalam resep.';
            } else if (completeItems !== cart.length) {
                message = isCompoundPrescription()
                    ? `Lengkapi kelompok dan dosis pada ${cart.length - completeItems} komponen racikan.`
                    : `Lengkapi aturan pakai pada ${cart.length - completeItems} obat.`;
            } else if (unavailableItems > 0) {
                message = `Periksa stok pada ${unavailableItems} item yang belum tersedia.`;
            }

            $('#prescriptionItemCounter').text(`${completeItems}/${cart.length} ${isCompoundPrescription() ? 'komponen' : 'obat'} lengkap`);
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
                .attr('title', ready ? 'Selesai dan lanjut ke pembayaran' : message);
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
                draft_id: $('#draftId').val() || null,
                tanggal_transaksi: $('#transactionDate').val(),
                jenis_transaksi: currentTransactionType(),
                customer_name: $('#customerName').val(),
                customer_phone: $('#customerPhone').val(),
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
                    dosis_komponen: item.dosis_komponen || '',
                    keterangan: item.keterangan || ''
                })),
                payments: includePayments ? paymentRows() : []
            };
        }

        function validateCart(requireCompletePrescription = false) {
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

                if (invalidItem) {
                    Swal.fire(
                        'Detail obat belum lengkap',
                        isCompoundPrescription()
                            ? `Lengkapi kelompok R/, dosis komponen ${invalidItem.nama_obat}, dan etiket bersama pada kartu kelompoknya.`
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
                        $('#printLastReceiptBtn').prop('disabled', false);
                        newTransaction();
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
            $('#transactionDiscountPercent, #transactionDiscountNominal, #taxPercent, #embalase').val(0);
            $('#useTax').prop('checked', false).trigger('change');
            activeCompoundGroup = 'R/ 1';
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
            window.open(urls.receipt.replace(':id', id), '_blank');
        }

        window.printReceipt = printReceipt;

        function loadDraft(transaction) {
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
                dosis_komponen: detail.dosis_komponen || '',
                keterangan: detail.keterangan || ''
            }));

            activeCompoundGroup = cart.find(item => String(item.racikan_group || '').trim())?.racikan_group || 'R/ 1';

            renderCart();
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
            $.get(urls.show.replace(':id', id), function(transaction) {
                loadDraft(transaction);
            }).fail(xhr => showAjaxError(xhr, 'Draft gagal dimuat.'));
        }

        function loadDraftFromQuery() {
            const draftId = new URLSearchParams(window.location.search).get('draft_id');

            if (!draftId) {
                return;
            }

            resumeDraft(draftId);
            window.history.replaceState({}, document.title, window.location.pathname);
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

            if ((event.key === 'F2' || ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k')) && !event.altKey) {
                event.preventDefault();
                if (prescriptionTypeSelectionOpen) {
                    return;
                }
                $('#productSearch').select2('open');
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
                $(prescriptionFlowOpen ? '#finishPrescriptionFlowBtn' : '#completeTransactionBtn').trigger('click');
            }
        });

        newTransaction(false);
        loadDraftFromQuery();
    });
</script>
