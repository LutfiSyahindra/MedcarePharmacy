@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.penjualan.pos.partials.style")
    @include("medcare.menu.penjualan.pos.partials.simpleStyle")
    @include("medcare.menu.penjualan.pos.partials.premiumCashierStyle")
    @include("medcare.menu.penjualan.pos.partials.prescriptionCashierStyle")
    @include("medcare.menu.penjualan.pos.partials.prescriptionComfortStyle")
@endpush

@section("content")
    <main class="pos-page pos-page--cashier pos-simple pos-premium" aria-label="Kasir point of sale">
        <header class="pos-appbar">
            <div class="pos-brand">
                <span class="pos-brand-mark"><i class="mdi mdi-point-of-sale"></i></span>
                <div class="pos-brand-copy">
                    <div class="pos-brand-title">
                        <h1>Medcare Kasir</h1>
                        <span>Point of sale apotek</span>
                    </div>
                    <div class="pos-brand-meta">
                        <span class="pos-session-status" id="posConnection"><i class="mdi mdi-circle"></i> Online</span>
                        <span class="pos-clock"><i class="mdi mdi-clock-outline"></i><span id="posClock">Memuat waktu...</span></span>
                    </div>
                </div>
            </div>

            <div class="pos-appbar-center">
                <nav class="pos-flow" aria-label="Tahapan transaksi">
                    <button type="button" class="pos-flow-step is-active" id="posFlowProduct" data-pos-jump="product"
                        aria-label="Buka langkah pilih produk">
                        <span class="pos-flow-icon"><i class="mdi mdi-barcode-scan"></i><b>1</b></span>
                        <span><small>Langkah 1</small><strong>Produk</strong></span>
                    </button>
                    <span class="pos-flow-divider" aria-hidden="true"><i class="mdi mdi-chevron-right"></i></span>
                    <button type="button" class="pos-flow-step" id="posFlowCart" data-pos-jump="cart"
                        aria-label="Buka langkah review keranjang">
                        <span class="pos-flow-icon"><i class="mdi mdi-cart-outline"></i><b>2</b></span>
                        <span><small>Langkah 2</small><strong>Keranjang</strong></span>
                    </button>
                    <span class="pos-flow-divider" aria-hidden="true"><i class="mdi mdi-chevron-right"></i></span>
                    <button type="button" class="pos-flow-step" id="posFlowPayment" data-pos-jump="payment"
                        aria-label="Buka langkah pembayaran">
                        <span class="pos-flow-icon"><i class="mdi mdi-credit-card-check-outline"></i><b>3</b></span>
                        <span><small>Langkah 3</small><strong>Pembayaran</strong></span>
                    </button>
                    <div class="pos-flow-summary">
                        <span><small>Item</small><strong id="headerCartCount">0</strong></span>
                        <span><small>Total</small><strong id="headerGrandTotal">Rp 0</strong></span>
                    </div>
                </nav>
            </div>

            <div class="pos-appbar-actions">
                @if ($canSwitchPosBranch)
                    <button type="button" class="btn pos-btn-quiet pos-branch-button" id="posBranchButton"
                        title="Pilih cabang transaksi">
                        <i class="mdi mdi-source-branch"></i>
                        <span><small>Cabang aktif</small><strong id="activePosBranchName">Pilih cabang</strong></span>
                        <i class="mdi mdi-chevron-down"></i>
                    </button>
                @endif
                <button type="button" class="btn pos-btn-quiet" id="newTransactionBtn" title="Transaksi baru (F8)">
                    <i class="mdi mdi-plus"></i><span>Baru</span><kbd>F8</kbd>
                </button>
                <a href="{{ route("penjualan.pos.history") }}" class="btn pos-btn-quiet" title="Riwayat transaksi">
                    <i class="mdi mdi-history"></i><span>Riwayat</span>
                </a>
                <button type="button" class="btn pos-btn-quiet" id="printLastReceiptBtn" disabled title="Cetak struk transaksi terakhir">
                    <i class="mdi mdi-printer-outline"></i><span>Struk terakhir</span>
                </button>
                <span class="pos-cashier-profile" title="Kasir aktif">
                    <span class="pos-cashier-avatar">{{ strtoupper(substr(auth()->user()->name ?? "K", 0, 1)) }}</span>
                    <span><small>Kasir</small><strong>{{ auth()->user()->name ?? "Kasir" }}</strong></span>
                </span>
            </div>
        </header>

        <div class="pos-workspace is-stage-product" id="posWorkspace" data-cashier-stage="product">
            <span id="posCatalogHomeAnchor" class="d-none" aria-hidden="true"></span>
            <aside class="pos-catalog" aria-label="Pilih produk">
                <section class="pos-surface pos-product-panel">
                    <header class="pos-catalog-hero">
                        <div class="pos-catalog-hero-main">
                            <span class="pos-catalog-step">1</span>
                            <div>
                                <span class="pos-section-kicker">PRODUK</span>
                                <h2>Tambah produk</h2>
                                <p>Cari nama, kode, atau scan barcode.</p>
                            </div>
                        </div>
                        <span class="pos-catalog-ready"><i class="mdi mdi-access-point"></i> Scanner aktif</span>
                    </header>

                    <div class="pos-catalog-rail" aria-label="Alur tambah item">
                        <div class="pos-catalog-phase is-active" id="catalogPhaseSearch">
                            <span><i class="mdi mdi-magnify-scan"></i></span>
                            <small>1. Cari</small>
                        </div>
                        <i class="mdi mdi-chevron-right"></i>
                        <div class="pos-catalog-phase" id="catalogPhaseConfigure">
                            <span><i class="mdi mdi-tune-variant"></i></span>
                            <small>2. Atur</small>
                        </div>
                        <i class="mdi mdi-chevron-right"></i>
                        <div class="pos-catalog-phase" id="catalogPhaseStock">
                            <span><i class="mdi mdi-package-variant-closed-check"></i></span>
                            <small>3. Cek stok</small>
                        </div>
                    </div>

                    <div class="pos-search-box" id="productSearchBox">
                        <div class="pos-search-topline">
                            <span class="pos-search-eyebrow"><i class="mdi mdi-lightning-bolt"></i> Input produk</span>
                            <span class="pos-search-shortcut"><kbd>F2</kbd></span>
                        </div>

                        <label class="pos-search-title" for="productSearch">
                            Cari produk
                            <small>Nama obat, kode produk, atau barcode</small>
                        </label>

                        <div class="pos-search-control">
                            <span class="pos-search-leading"><i class="mdi mdi-barcode-scan"></i></span>
                            <select id="productSearch" class="form-select" style="width:100%" aria-describedby="productSearchStatus"></select>
                            <span class="pos-search-ready" aria-hidden="true"><i class="mdi mdi-circle"></i> LIVE</span>
                        </div>

                        <div class="pos-search-feedback">
                            <p id="productSearchStatus" aria-live="polite">
                                <i class="mdi mdi-information-outline"></i>
                                <span>Mulai ketik atau scan barcode untuk menampilkan produk.</span>
                            </p>
                            <div class="pos-search-scope" aria-label="Data yang dapat dicari">
                                <span><i class="mdi mdi-barcode"></i> Kode</span>
                                <span><i class="mdi mdi-pill"></i> Obat</span>
                                <span><i class="mdi mdi-flask-outline"></i> Kandungan</span>
                            </div>
                        </div>
                    </div>

                    <section class="pos-catalog-block pos-selection-block" aria-labelledby="selectedProductTitle">
                        <div class="pos-catalog-block-head">
                            <div>
                                <span class="pos-catalog-block-icon"><i class="mdi mdi-pill-multiple"></i></span>
                                <span>
                                    <small>PRODUK TERPILIH</small>
                                    <strong id="selectedProductTitle">Detail produk</strong>
                                </span>
                            </div>
                            <span class="pos-block-state" id="selectedProductState"><i class="mdi mdi-circle-outline"></i> Menunggu pilihan</span>
                        </div>

                        <div class="pos-product-card" id="selectedProductCard">
                            <div class="pos-empty-state">
                                <span class="pos-empty-icon"><i class="mdi mdi-package-variant"></i></span>
                                <strong>Belum ada produk dipilih</strong>
                                <span>Gunakan pencarian di atas untuk menampilkan detail obat.</span>
                            </div>
                        </div>
                    </section>

                    <section class="pos-catalog-block pos-config-block" aria-labelledby="saleConfigTitle">
                        <div class="pos-catalog-block-head">
                            <div>
                                <span class="pos-catalog-block-icon is-violet"><i class="mdi mdi-tune-vertical-variant"></i></span>
                                <span>
                                    <small>PENGATURAN JUAL</small>
                                    <strong id="saleConfigTitle">Satuan & jumlah</strong>
                                </span>
                            </div>
                            <span class="pos-block-number">02</span>
                        </div>

                        <div class="pos-add-card">
                            <div class="pos-add-fields">
                                <div class="pos-field">
                                    <label for="unitSelect"><i class="mdi mdi-scale-balance"></i> Satuan jual</label>
                                    <select id="unitSelect" class="form-select" disabled>
                                        <option value="">Pilih produk dulu</option>
                                    </select>
                                </div>
                                <div class="pos-field pos-qty-field">
                                    <label for="qtyInput"><i class="mdi mdi-counter"></i> Jumlah</label>
                                    <input type="number" id="qtyInput" class="form-control" min="0.01" step="0.01" value="1">
                                </div>
                            </div>
                            <button type="button" class="btn pos-add-button" id="addToCartBtn" disabled>
                                <span class="pos-add-button-icon"><i class="mdi mdi-cart-plus"></i></span>
                                <span>Tambah ke keranjang</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </button>
                        </div>
                    </section>

                    <details class="pos-catalog-block pos-stock-block" aria-labelledby="stockInfoTitle">
                        <summary class="pos-catalog-block-head">
                            <div>
                                <span class="pos-catalog-block-icon is-emerald"><i class="mdi mdi-shield-check-outline"></i></span>
                                <span>
                                    <small>INFORMASI STOK</small>
                                    <strong id="stockInfoTitle">Harga & alokasi FEFO</strong>
                                </span>
                            </div>
                            <span class="pos-fefo-badge"><i class="mdi mdi-autorenew"></i> Otomatis</span>
                        </summary>

                        <div class="pos-quote-box" id="quoteBox">
                            <div>
                                <small><i class="mdi mdi-tag-outline"></i> Harga satuan</small>
                                <strong id="quotePrice">Rp 0</strong>
                            </div>
                            <div>
                                <small><i class="mdi mdi-package-variant"></i> Stok tersedia</small>
                                <strong id="quoteStock">0</strong>
                            </div>
                            <div>
                                <small><i class="mdi mdi-calendar-clock"></i> Batch terdekat</small>
                                <strong id="quoteBatch">-</strong>
                            </div>
                        </div>
                        <div class="pos-fefo-zone">
                            <span class="pos-fefo-label"><i class="mdi mdi-timeline-clock-outline"></i> Rencana alokasi</span>
                            <div class="pos-fefo-list" id="quoteFefoList">
                                <span class="pos-fefo-placeholder">Alokasi batch akan tampil setelah produk dan jumlah dipilih.</span>
                            </div>
                        </div>
                    </details>
                </section>

                <section class="pos-shortcut-card" aria-label="Bantuan keyboard">
                    <span><i class="mdi mdi-lightning-bolt-outline"></i> Alur cepat</span>
                    <div><span><kbd>F2</kbd> Cari</span><span><kbd>Enter</kbd> Tambah</span><span><kbd>F9</kbd> Bayar</span></div>
                </section>
            </aside>

            <section class="pos-checkout" aria-label="Transaksi aktif">
                <section class="pos-customer-card" id="posCustomerCard" aria-label="Profil pembeli">
                    <span id="posDetailsHomeAnchor" class="d-none" aria-hidden="true"></span>
                </section>

                <section class="pos-surface pos-transaction-panel" id="transactionPanel">
                    <div class="pos-transaction-hero">
                        <span class="pos-transaction-orbit" aria-hidden="true"></span>
                        <div class="pos-transaction-head">
                            <div class="pos-panel-heading pos-transaction-heading">
                                <span class="pos-transaction-number">2</span>
                                <div>
                                    <span class="pos-section-kicker"><i class="mdi mdi-circle"></i> TRANSAKSI</span>
                                    <h2>Keranjang</h2>
                                    <p>Periksa item dan total sebelum melanjutkan pembayaran.</p>
                                </div>
                            </div>
                            <span class="pos-draft-pill" id="transactionState" aria-live="polite">
                                <i class="mdi mdi-circle-medium"></i><span>Belum disimpan</span>
                            </span>
                        </div>

                        <div class="pos-cart-readiness">
                            <span><i class="mdi mdi-transit-connection-variant"></i> <b id="cartReadinessText">Siap menerima produk</b></span>
                            <div class="pos-cart-readiness-track" role="progressbar" aria-label="Kesiapan keranjang" aria-valuemin="0" aria-valuemax="100" aria-valuenow="8">
                                <span id="cartReadinessBar"></span>
                            </div>
                            <small><i class="mdi mdi-autorenew"></i> Validasi FEFO berjalan otomatis</small>
                        </div>
                    </div>

                    <input type="hidden" id="draftId">

                    <section class="pos-prescription-payment-handoff d-none" id="prescriptionPaymentHandoff" aria-label="Ringkasan resep siap dibayar">
                        <span class="pos-prescription-handoff-icon"><i class="mdi mdi-clipboard-check-multiple-outline"></i></span>
                        <div class="pos-prescription-handoff-copy">
                            <small>PROSES RESEP SELESAI</small>
                            <strong id="prescriptionHandoffTitle">Resep siap dilanjutkan ke pembayaran</strong>
                            <span id="prescriptionHandoffMeta">0 item resep</span>
                        </div>
                        <div class="pos-prescription-handoff-total">
                            <small>TOTAL TAGIHAN</small>
                            <strong id="prescriptionHandoffTotal">Rp 0</strong>
                        </div>
                        <button type="button" class="btn pos-edit-prescription-button" id="editPrescriptionBtn">
                            <i class="mdi mdi-pencil-outline"></i> Edit proses resep
                        </button>
                    </section>

                    <details class="pos-transaction-details">
                        <summary>
                            <span class="pos-detail-customer">
                                <span class="pos-customer-avatar" id="transactionCustomerAvatar">U</span>
                                <span>
                                    <small>PROFIL PEMBELI</small>
                                    <strong id="transactionCustomerName">Pelanggan umum</strong>
                                </span>
                            </span>
                            <span class="pos-detail-toggle">
                                <span class="pos-detail-summary">Umum</span>
                                <span class="pos-detail-chevron"><i class="mdi mdi-chevron-down"></i></span>
                            </span>
                        </summary>
                        <div class="pos-transaction-form-shell">
                            <div class="pos-transaction-form-intro">
                                <span><i class="mdi mdi-account-edit-outline"></i></span>
                                <div>
                                    <strong>Informasi transaksi</strong>
                                    <small>Lengkapi data pembeli bila transaksi memerlukan resep, kredit, atau penagihan instansi.</small>
                                </div>
                                <button type="button" class="btn pos-general-customer-button" id="setGeneralCustomerBtn">
                                    <i class="mdi mdi-account-refresh-outline"></i> Pelanggan umum
                                </button>
                            </div>
                            <div class="pos-form-grid">
                                <div class="pos-field pos-transaction-type-field">
                                    <label for="transactionDate"><i class="mdi mdi-calendar-clock-outline"></i> Tanggal transaksi</label>
                                    <input type="datetime-local" id="transactionDate" class="form-control">
                                </div>
                                <div class="pos-field pos-transaction-type-selector-field">
                                    <label for="jenisTransaksi"><i class="mdi mdi-swap-horizontal-bold"></i> Jenis transaksi</label>
                                    <select id="jenisTransaksi" class="form-select">
                                        @foreach ($transactionTypes as $value => $label)
                                            @continue($value === "penjualan_racikan")
                                            <option value="{{ $value }}">{{ $value === "penjualan_resep" ? "Penjualan Resep" : $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="jenisResep" value="penjualan_resep">
                                </div>
                                <div class="pos-field">
                                    <label for="customerName"><i class="mdi mdi-account-outline"></i> Nama pelanggan <b>*</b></label>
                                    <input type="text" id="customerName" class="form-control" placeholder="Wajib diisi" required aria-required="true" autocomplete="name">
                                </div>
                                <div class="pos-field">
                                    <label for="customerPhone"><i class="mdi mdi-phone-outline"></i> No. HP <b>*</b></label>
                                    <input type="tel" id="customerPhone" class="form-control" placeholder="Wajib diisi" required aria-required="true" autocomplete="tel" inputmode="tel">
                                </div>
                                <div class="pos-field pos-institution-field">
                                    <label for="instansiName"><i class="mdi mdi-domain"></i> Instansi</label>
                                    <input type="text" id="instansiName" class="form-control" placeholder="Nama instansi">
                                </div>
                                <div class="pos-field">
                                    <label for="catatanTransaksi"><i class="mdi mdi-note-text-outline"></i> Catatan</label>
                                    <input type="text" id="catatanTransaksi" class="form-control" placeholder="Catatan internal (opsional)">
                                </div>
                            </div>

                            <section class="pos-prescription-workspace d-none" id="prescriptionWorkspace" aria-label="Detail resep">
                                <div class="pos-prescription-head">
                                    <span class="pos-prescription-head-icon"><i class="mdi mdi-prescription"></i></span>
                                    <div>
                                        <small>LAYANAN RESEP</small>
                                        <strong id="prescriptionWorkspaceTitle">Resep Non Racikan</strong>
                                        <p id="prescriptionWorkspaceCopy">Catat identitas resep dan aturan pakai setiap obat secara lengkap.</p>
                                    </div>
                                    <span class="pos-prescription-status" id="prescriptionReadiness">
                                        <i class="mdi mdi-progress-alert"></i> Belum lengkap
                                    </span>
                                </div>

                                <div class="pos-prescription-mode-summary" id="prescriptionModeSummary">
                                    <span class="pos-prescription-mode-icon" id="prescriptionModeIcon"><i class="mdi mdi-pill-multiple"></i></span>
                                    <span class="pos-prescription-mode-copy">
                                        <small>JENIS RESEP AKTIF</small>
                                        <strong id="prescriptionModeLabel">Non Racikan</strong>
                                        <span id="prescriptionModeSummaryCopy">Etiket dan aturan pakai dicatat untuk setiap obat.</span>
                                    </span>
                                    <button type="button" class="btn pos-change-prescription-type" id="changePrescriptionTypeBtn">
                                        <i class="mdi mdi-swap-horizontal"></i> Ubah jenis
                                    </button>
                                </div>

                                <div class="pos-prescription-mode-note" id="prescriptionModeNote">
                                    <span><i class="mdi mdi-information-outline"></i></span>
                                    <div>
                                        <strong id="prescriptionModeNoteTitle">Isi etiket per obat</strong>
                                        <small id="prescriptionModeNoteCopy">Tambahkan obat, lalu lengkapi aturan pakai pada kartu obat tersebut.</small>
                                    </div>
                                </div>

                                <div class="pos-prescription-form-grid">
                                    <div class="pos-field pos-prescription-field">
                                        <label for="nomorResep"><i class="mdi mdi-identifier"></i> Nomor resep <b>*</b></label>
                                        <input type="text" id="nomorResep" class="form-control" placeholder="Contoh: RSP-2026-0001" autocomplete="off">
                                    </div>
                                    <div class="pos-field pos-prescription-field">
                                        <label for="tanggalResep"><i class="mdi mdi-calendar-check-outline"></i> Tanggal resep <b>*</b></label>
                                        <input type="date" id="tanggalResep" class="form-control">
                                    </div>
                                    <div class="pos-field pos-prescription-field">
                                        <label for="dokterName"><i class="mdi mdi-doctor"></i> Dokter penulis <b>*</b></label>
                                        <input type="text" id="dokterName" class="form-control" placeholder="Nama dokter" autocomplete="off">
                                    </div>
                                    <div class="pos-field pos-prescription-field">
                                        <label for="asalResep"><i class="mdi mdi-hospital-building"></i> Asal resep</label>
                                        <input type="text" id="asalResep" class="form-control" placeholder="Klinik / rumah sakit (opsional)" autocomplete="off">
                                    </div>
                                </div>

                                <span id="compoundSetupHomeAnchor" class="d-none" aria-hidden="true"></span>
                                <section class="pos-compound-setup d-none" id="compoundSetup" aria-label="Pengaturan racikan">
                                    <div class="pos-compound-steps">
                                        <span><b>1</b> Susun racikan aktif</span>
                                        <i class="mdi mdi-chevron-right"></i>
                                        <span><b>2</b> Simpan racikan</span>
                                        <i class="mdi mdi-chevron-right"></i>
                                        <span><b>3</b> Tambah racikan berikutnya</span>
                                    </div>
                                    <div class="pos-compound-controls">
                                        <label class="pos-compound-group-control" for="activeCompoundGroup">
                                            <span>Racikan aktif</span>
                                            <div>
                                                <select id="activeCompoundGroup" class="form-select" aria-label="Kelompok racikan aktif"></select>
                                                <button type="button" class="btn pos-new-compound-group" id="newCompoundGroupBtn">
                                                    <i class="mdi mdi-plus"></i> Racikan berikutnya
                                                </button>
                                            </div>
                                            <small>Obat baru otomatis masuk ke racikan aktif.</small>
                                        </label>
                                        <div class="pos-compound-save-rule" id="compoundSaveRule">
                                            <span><i class="mdi mdi-content-save-check-outline"></i></span>
                                            <div>
                                                <strong>Selesaikan satu per satu</strong>
                                                <small id="compoundSaveRuleCopy">Racikan 1 belum disimpan.</small>
                                            </div>
                                        </div>
                                        <input type="hidden" id="embalase" value="0">
                                    </div>
                                    <div class="pos-compound-group-summary" id="compoundGroupSummary" aria-live="polite"></div>
                                </section>

                                <div class="pos-prescription-guidance">
                                    <span><i class="mdi mdi-shield-check-outline"></i></span>
                                    <div>
                                        <strong id="prescriptionGuidanceTitle">Verifikasi resep sebelum pembayaran</strong>
                                        <small id="prescriptionGuidanceCopy">Nama pasien, nomor resep, tanggal, dokter, dan aturan pakai wajib terisi.</small>
                                    </div>
                                    <span class="pos-prescription-counter" id="prescriptionItemCounter">0 item resep</span>
                                </div>
                            </section>
                        </div>
                    </details>

                    <span id="posCartHomeAnchor" class="d-none" aria-hidden="true"></span>
                    <div class="pos-cart-toolbar">
                        <div class="pos-cart-title">
                            <span class="pos-cart-title-icon"><i class="mdi mdi-format-list-bulleted-square"></i></span>
                            <span>
                                <strong id="cartEditorTitle">Item yang dijual</strong>
                                <small id="cartEditorCopy">Jumlah dan diskon dapat diubah langsung.</small>
                            </span>
                        </div>
                        <div class="pos-cart-actions">
                            <span class="pos-live-label"><i class="mdi mdi-access-point"></i> STOK LIVE</span>
                            <button type="button" class="btn pos-prescription-toolbar-pay" id="prescriptionToolbarPayBtn" title="Lengkapi resep untuk membayar">
                                <i class="mdi mdi-credit-card-check-outline"></i> Bayar
                            </button>
                            <button type="button" class="btn pos-clear-button" id="clearCartBtn">
                                <i class="mdi mdi-trash-can-outline"></i> Kosongkan
                            </button>
                        </div>
                    </div>

                    <div class="pos-cart-wrap">
                        <table class="table pos-cart-table align-middle">
                            <thead>
                                <tr>
                                    <th><i class="mdi mdi-pill-multiple"></i> Produk</th>
                                    <th style="width:152px"><i class="mdi mdi-counter"></i> <span id="cartQtyHeaderLabel">Jumlah</span></th>
                                    <th style="width:125px"><i class="mdi mdi-tag-outline"></i> Harga</th>
                                    <th style="width:205px"><i class="mdi mdi-sale-outline"></i> Diskon item</th>
                                    <th style="width:178px"><i class="mdi mdi-package-variant-closed-check"></i> Alokasi FEFO</th>
                                    <th style="width:145px"><i class="mdi mdi-calculator-variant-outline"></i> Nilai akhir</th>
                                    <th style="width:54px"><span class="visually-hidden">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
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
                            </tbody>
                        </table>
                    </div>

                    <div class="pos-cart-dock" role="region" aria-label="Ringkasan dan aksi keranjang">
                        <div class="pos-transaction-insights" aria-label="Ringkasan keranjang">
                            <div class="pos-insight-card">
                                <span class="pos-insight-icon is-indigo"><i class="mdi mdi-cart-variant"></i></span>
                                <span><small>JENIS ITEM</small><strong id="cartItemCount">0 item</strong></span>
                            </div>
                            <div class="pos-insight-card">
                                <span class="pos-insight-icon is-cyan"><i class="mdi mdi-counter"></i></span>
                                <span><small>TOTAL KUANTITAS</small><strong id="cartQtyCount">0 qty</strong></span>
                            </div>
                            <div class="pos-insight-card" id="cartHealthCard">
                                <span class="pos-insight-icon is-emerald"><i class="mdi mdi-shield-check-outline"></i></span>
                                <span><small>STATUS STOK</small><strong id="cartStockHealth">Menunggu item</strong></span>
                            </div>
                        </div>
                        <div class="pos-cart-dock-total">
                            <span><small>Total sementara</small><strong id="cartRunningTotal">Rp 0</strong></span>
                            <button type="button" class="btn pos-go-payment-button" id="goToPaymentBtn" disabled>
                                <span><small>Review selesai</small>Lanjut bayar</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pos-cart-footnote">
                        <span><i class="mdi mdi-information-slab-circle-outline"></i> Harga dan batch akan divalidasi kembali saat jumlah item berubah.</span>
                        <span><i class="mdi mdi-lock-check-outline"></i> Perhitungan tersinkron</span>
                    </div>
                </section>

                <section class="pos-checkout-stage" id="posPaymentLayout" aria-labelledby="posPaymentTitle">
                    <header class="pos-checkout-hero">
                        <button type="button" class="btn pos-payment-back" id="backToCartBtn">
                            <i class="mdi mdi-arrow-left"></i><span>Kembali</span>
                        </button>
                        <div class="pos-checkout-title">
                            <span class="pos-checkout-step" aria-hidden="true">3</span>
                            <div>
                                <span class="pos-section-kicker">PEMBAYARAN</span>
                                <h2 id="posPaymentTitle">Pembayaran</h2>
                                <p>Pilih metode dan masukkan nominal yang diterima.</p>
                            </div>
                        </div>
                        <div class="pos-checkout-hero-side">
                            <button type="button" class="btn pos-payment-edit-prescription d-none" id="paymentEditPrescriptionBtn">
                                <i class="mdi mdi-prescription"></i><span>Edit resep</span>
                            </button>
                            <span class="pos-checkout-state" id="checkoutStatusBadge">
                                <i class="mdi mdi-progress-clock"></i><span>Menunggu pembayaran</span>
                            </span>
                            <div class="pos-checkout-due">
                                <small>Total yang perlu diterima</small>
                                <strong id="paymentDueText">Rp 0</strong>
                            </div>
                        </div>
                    </header>

                    <div class="pos-payment-layout">
                        <div class="pos-payment-panel">
                            <div class="pos-payment-head">
                                <div>
                                    <span class="pos-payment-title-icon"><i class="mdi mdi-wallet-plus-outline"></i></span>
                                    <span class="pos-payment-title-copy">
                                        <strong>Metode pembayaran</strong>
                                        <small>Tunai, transfer, atau gabungkan beberapa metode.</small>
                                    </span>
                                </div>
                                <div class="pos-payment-head-actions">
                                    <span class="pos-payment-count" id="paymentMethodCount">1 metode</span>
                                    <button type="button" class="btn pos-add-payment-button" id="addPaymentBtn" title="Tambah metode pembayaran">
                                        <i class="mdi mdi-plus"></i><span>Tambah metode</span>
                                    </button>
                                </div>
                            </div>

                            <div class="pos-payment-rows-head" aria-hidden="true">
                                <span>Metode</span><span>Nominal diterima</span><span>Referensi</span><span></span>
                            </div>
                            <div id="paymentRows" class="pos-payment-rows" aria-live="polite"></div>

                            <div class="pos-payment-quick" aria-label="Nominal tunai cepat">
                                <div class="pos-payment-quick-copy">
                                    <span><i class="mdi mdi-lightning-bolt"></i> Nominal tunai cepat</span>
                                    <small>Metode aktif otomatis diatur sebagai Tunai.</small>
                                </div>
                                <div class="pos-payment-quick-options">
                                    <button type="button" class="pos-quick-payment is-exact" data-amount="exact"><i class="mdi mdi-check-decagram-outline"></i> <span>Bayar pas</span></button>
                                    <button type="button" class="pos-quick-payment" data-amount="10000">Rp10rb</button>
                                    <button type="button" class="pos-quick-payment" data-amount="20000">Rp20rb</button>
                                    <button type="button" class="pos-quick-payment" data-amount="50000">Rp50rb</button>
                                    <button type="button" class="pos-quick-payment" data-amount="100000">Rp100rb</button>
                                </div>
                            </div>

                            <div class="pos-payment-coverage" id="paymentCoverage">
                                <span class="pos-payment-coverage-icon"><i class="mdi mdi-cash-clock"></i></span>
                                <div>
                                    <span class="pos-payment-coverage-copy">
                                        <strong id="paymentCoverageTitle">Belum ada pembayaran</strong>
                                        <small id="paymentCoverageText">Masukkan nominal untuk melihat kecukupan pembayaran.</small>
                                    </span>
                                    <span class="pos-payment-progress" role="progressbar" aria-label="Kecukupan pembayaran" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <span id="paymentProgressBar"></span>
                                    </span>
                                </div>
                            </div>

                        </div>

                        <aside class="pos-total-panel" aria-label="Ringkasan tagihan">
                            <div class="pos-total-heading">
                                <div>
                                    <span class="pos-total-heading-icon"><i class="mdi mdi-receipt-text-check-outline"></i></span>
                                    <span><small>RINGKASAN</small><strong>Detail tagihan</strong></span>
                                </div>
                                <span class="pos-summary-item-count" id="summaryItemCount">0 item</span>
                            </div>

                            <div class="pos-total-breakdown">
                                <div class="pos-total-row">
                                    <span>Subtotal produk</span><strong id="subtotalGrossText">Rp 0</strong>
                                </div>
                                <div class="pos-total-row is-saving">
                                    <span><i class="mdi mdi-tag-heart-outline"></i> Diskon item</span><strong id="itemDiscountText">Rp 0</strong>
                                </div>
                            </div>

                            <details class="pos-adjustment-card">
                                <summary class="pos-adjustment-card-head">
                                    <span><i class="mdi mdi-tune-variant"></i> Penyesuaian transaksi</span>
                                    <small>Diskon & pajak <i class="mdi mdi-chevron-down"></i></small>
                                </summary>
                                <div class="pos-adjustment-card-body">
                                    <div class="pos-total-row pos-adjustment-row">
                                        <div>
                                            <label for="transactionDiscountPercent">Diskon tambahan</label>
                                            <div class="pos-adjustment-fields">
                                                <label><span>%</span><input type="number" id="transactionDiscountPercent" class="form-control" min="0" max="100" step="0.01" value="0" aria-label="Diskon transaksi persen" placeholder="0"></label>
                                                <label class="is-rupiah"><span>Rp</span><input type="number" id="transactionDiscountNominal" class="form-control" min="0" step="0.01" value="0" aria-label="Diskon transaksi nominal" placeholder="0"></label>
                                            </div>
                                        </div>
                                        <strong id="transactionDiscountText">Rp 0</strong>
                                    </div>
                                    <div class="pos-total-row pos-tax-row">
                                        <label class="pos-tax-switch" for="useTax">
                                            <input class="form-check-input" type="checkbox" role="switch" id="useTax">
                                            <span><b>Tambahkan pajak</b><small>Aktifkan bila transaksi dikenakan pajak.</small></span>
                                        </label>
                                        <div class="pos-tax-value">
                                            <label><input type="number" id="taxPercent" class="form-control" min="0" max="100" step="0.01" value="0" disabled aria-label="Persen pajak" placeholder="0"><span>%</span></label>
                                            <strong id="taxTotalText">Rp 0</strong>
                                        </div>
                                    </div>
                                    <div class="pos-total-row pos-embalase-total d-none" id="embalaseTotalRow">
                                        <span><i class="mdi mdi-package-variant-closed"></i> Embalase racikan</span>
                                        <strong id="embalaseTotalText">Rp 0</strong>
                                    </div>
                                </div>
                            </details>

                            <div class="pos-total-row is-grand">
                                <span><small>TOTAL AKHIR</small>Total tagihan</span><strong id="grandTotalText">Rp 0</strong>
                            </div>
                            <div class="pos-balance-row">
                                <div><span><i class="mdi mdi-cash-check"></i> Sudah dibayar</span><strong id="paidTotalText">Rp 0</strong></div>
                                <div><span id="changeDueLabel">Sisa tagihan</span><strong id="changeDueText">Rp 0</strong></div>
                            </div>
                        </aside>
                    </div>

                    <footer class="pos-submit-bar">
                        <div class="pos-submit-status">
                            <span class="pos-submit-status-icon"><i class="mdi mdi-information-outline"></i></span>
                            <span>
                                <small>STATUS TRANSAKSI</small>
                                <p id="posPaymentHint" aria-live="polite">Tambahkan item untuk memproses transaksi.</p>
                            </span>
                        </div>
                        <div>
                            <button type="button" class="btn pos-save-draft-button" id="saveDraftBtn">
                                <i class="mdi mdi-content-save-outline"></i><span>Simpan draft</span>
                            </button>
                            <button type="button" class="btn pos-complete-button" id="completeTransactionBtn">
                                <span><small>Transaksi sudah benar</small>Selesaikan transaksi</span><i class="mdi mdi-arrow-right"></i><kbd>F9</kbd>
                            </button>
                        </div>
                    </footer>
                </section>
            </section>
        </div>
    </main>

    @if ($canSwitchPosBranch)
        <div class="modal fade pos-branch-modal" id="posBranchModal" tabindex="-1"
            aria-labelledby="posBranchModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="pos-branch-modal-icon"><i class="mdi mdi-store-marker-outline"></i></span>
                        <div>
                            <small>SESI TRANSAKSI MULTI BRANCH</small>
                            <h2 class="modal-title" id="posBranchModalTitle">Pilih cabang POS</h2>
                            <p>Stok, harga, nomor transaksi, dan struk akan mengikuti cabang ini.</p>
                        </div>
                    </div>
                    <div class="modal-body">
                        @if ($posBranches->isNotEmpty())
                            <label for="posBranchSelector" class="form-label">Cabang aktif</label>
                            <select id="posBranchSelector" class="form-select">
                                <option value="">Pilih cabang untuk memulai transaksi</option>
                                @foreach ($posBranches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} — {{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <p class="pos-branch-modal-help"><i class="mdi mdi-shield-check-outline"></i> Hanya cabang berstatus aktif yang dapat digunakan.</p>
                        @else
                            <div class="pos-branch-empty">
                                <i class="mdi mdi-store-alert-outline"></i>
                                <strong>Belum ada cabang aktif</strong>
                                <span>Aktifkan minimal satu cabang sebelum menjalankan transaksi POS.</span>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if ($posBranches->isNotEmpty())
                            <button type="button" class="btn pos-confirm-branch" id="confirmPosBranchBtn" disabled>
                                Gunakan cabang ini <i class="mdi mdi-arrow-right"></i>
                            </button>
                        @else
                            <a href="{{ route("branch.index") }}" class="btn pos-confirm-branch">
                                Kelola cabang <i class="mdi mdi-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade pos-receipt-modal" id="posReceiptModal" tabindex="-1"
        aria-labelledby="posReceiptModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="pos-receipt-modal-icon"><i class="mdi mdi-receipt-text-check-outline"></i></span>
                    <div>
                        <small>BUKTI PEMBAYARAN</small>
                        <h2 class="modal-title" id="posReceiptModalTitle">Preview struk</h2>
                        <p id="posReceiptModalCopy">Struk siap dicetak tanpa membuka tab browser baru.</p>
                    </div>
                    <div class="pos-receipt-document-tabs" id="posReceiptDocumentTabs" role="tablist" aria-label="Pilih dokumen cetak">
                        <button type="button" class="is-active" id="receiptDocumentTab" data-receipt-document="receipt" role="tab" aria-selected="true">
                            <i class="mdi mdi-receipt-text-outline"></i> Struk
                        </button>
                        <button type="button" class="d-none" id="labelsDocumentTab" data-receipt-document="labels" role="tab" aria-selected="false">
                            <i class="mdi mdi-label-multiple-outline"></i> Etiket resep
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="pos-receipt-frame-shell">
                        <div class="pos-receipt-loading" id="posReceiptLoading">
                            <i class="mdi mdi-loading mdi-spin"></i>
                            <span>Menyiapkan struk...</span>
                        </div>
                        <iframe id="posReceiptFrame" title="Preview struk transaksi" src="about:blank"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn pos-receipt-close" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn pos-receipt-print" id="printReceiptModalBtn" disabled>
                        <i class="mdi mdi-printer-outline"></i> <span>Cetak struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade pos-prescription-type-modal rx-simple" id="prescriptionTypeModal" tabindex="-1" aria-labelledby="prescriptionTypeModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <section class="pos-prescription-type-step" id="prescriptionTypeStep">
                    <div class="pos-prescription-type-step-shell">
                        <div class="modal-header">
                            <div class="pos-prescription-modal-heading">
                                <span class="pos-rx-brand-mark"><b>R<em>x</em></b></span>
                                <div>
                                    <small>MEDCARE · PRESCRIPTION DESK</small>
                                    <h2 class="modal-title" id="prescriptionTypeModalTitle">Mulai transaksi resep</h2>
                                    <p>Satu workspace untuk penyiapan, etiket, dan pembayaran resep.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" id="closePrescriptionTypeBtn" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="pos-prescription-modal-intro">
                                <span><i class="mdi mdi-shape-outline"></i></span>
                                <div>
                                    <small>LANGKAH AWAL</small>
                                    <strong>Bagaimana obat pada resep ini disiapkan?</strong>
                                    <p>Pilih alur kerja yang sesuai. Anda tetap dapat mengganti jenis resep sebelum pembayaran.</p>
                                </div>
                                <span class="pos-rx-intro-badge"><i class="mdi mdi-shield-check-outline"></i> Validasi klinis aktif</span>
                            </div>

                            <div class="pos-prescription-type-grid" role="group" aria-label="Pilih jenis resep">
                                <button type="button" class="pos-prescription-type-option" data-prescription-type="penjualan_resep" aria-pressed="false">
                                    <span class="pos-rx-option-topline">
                                        <span class="pos-prescription-type-icon"><i class="mdi mdi-pill-multiple"></i></span>
                                        <span class="pos-rx-option-number">01</span>
                                    </span>
                                    <span class="pos-prescription-type-copy">
                                        <small>OBAT DISIAPKAN TERPISAH</small>
                                        <strong>Non Racikan</strong>
                                        <span>Untuk resep dengan etiket dan aturan pakai berbeda pada setiap obat.</span>
                                        <span class="pos-prescription-type-points" aria-label="Fitur resep non racikan">
                                            <b><i class="mdi mdi-check-circle-outline"></i> Signa per obat</b>
                                            <b><i class="mdi mdi-check-circle-outline"></i> Etiket individual</b>
                                            <b><i class="mdi mdi-check-circle-outline"></i> Proses lebih singkat</b>
                                        </span>
                                    </span>
                                    <span class="pos-rx-option-action">Buka kasir <i class="mdi mdi-arrow-right"></i></span>
                                    <i class="mdi mdi-check-circle pos-prescription-type-check"></i>
                                </button>
                                <button type="button" class="pos-prescription-type-option" data-prescription-type="penjualan_racikan" aria-pressed="false">
                                    <span class="pos-rx-option-topline">
                                        <span class="pos-prescription-type-icon"><i class="mdi mdi-mortar-pestle-plus"></i></span>
                                        <span class="pos-rx-option-number">02</span>
                                    </span>
                                    <span class="pos-prescription-type-copy">
                                        <small>OBAT DIRACIK MENJADI SATU</small>
                                        <strong>Racikan</strong>
                                        <span>Untuk resep dengan beberapa komponen dalam satu sediaan dan etiket bersama.</span>
                                        <span class="pos-prescription-type-points" aria-label="Fitur resep racikan">
                                            <b><i class="mdi mdi-check-circle-outline"></i> Kelompok R/</b>
                                            <b><i class="mdi mdi-check-circle-outline"></i> Dosis komponen</b>
                                            <b><i class="mdi mdi-check-circle-outline"></i> Preview racikan</b>
                                        </span>
                                    </span>
                                    <span class="pos-rx-option-action">Buka kasir <i class="mdi mdi-arrow-right"></i></span>
                                    <i class="mdi mdi-check-circle pos-prescription-type-check"></i>
                                </button>
                            </div>

                            <div class="pos-prescription-modal-help">
                                <i class="mdi mdi-lightbulb-on-outline"></i>
                                <span><strong>Masih ragu?</strong> Pilih Racikan bila dua atau lebih obat harus dicampur menjadi satu sediaan. Selain itu gunakan Non Racikan.</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <span class="pos-rx-chooser-assurance"><i class="mdi mdi-lock-check-outline"></i> Data tersimpan di transaksi kasir aktif</span>
                            <button type="button" class="btn pos-prescription-modal-cancel" id="cancelPrescriptionTypeBtn"><i class="mdi mdi-arrow-left"></i> Batal</button>
                        </div>
                    </div>
                </section>

                <section class="pos-prescription-cashier-step d-none" id="prescriptionCashierStep" aria-label="Workspace proses resep">
                    <header class="pos-prescription-cashier-header">
                        <div class="pos-prescription-cashier-brand">
                            <span class="pos-rx-brand-mark"><b>R<em>x</em></b></span>
                            <div>
                                <small>MEDCARE · PRESCRIPTION DESK</small>
                                <strong id="prescriptionFlowTitle">Resep Non Racikan</strong>
                                <p>Penyiapan resep terarah dari data pasien hingga pembayaran.</p>
                            </div>
                        </div>
                        <div class="pos-prescription-flow-steps" aria-label="Tahapan proses resep">
                            <span class="is-active"><b>1</b><em>Data resep</em></span>
                            <i class="mdi mdi-minus"></i>
                            <span><b>2</b><em>Obat & etiket</em></span>
                            <i class="mdi mdi-minus"></i>
                            <span><b>3</b><em>Lanjut bayar</em></span>
                        </div>
                        <div class="pos-rx-header-actions">
                            <span class="pos-rx-validation-state"><i class="mdi mdi-shield-check-outline"></i> Validasi aktif</span>
                            <button type="button" class="btn pos-prescription-flow-cancel" id="cancelPrescriptionFlowBtn">
                                <i class="mdi mdi-close"></i> Batalkan proses
                            </button>
                        </div>
                    </header>

                    <div class="pos-prescription-cashier-body">
                        <div class="pos-prescription-cashier-layout">
                            <div class="pos-prescription-modal-catalog" id="prescriptionModalCatalogSlot" aria-label="Katalog obat resep"></div>
                            <section class="pos-prescription-modal-details pos-rx-work-panel" aria-label="Data pasien dan resep">
                                <header class="pos-rx-panel-header">
                                    <span class="pos-rx-panel-index">01</span>
                                    <span class="pos-rx-panel-copy">
                                        <small>IDENTITAS</small>
                                        <strong>Pasien & resep</strong>
                                        <p>Lengkapi data wajib sebelum memproses obat.</p>
                                    </span>
                                    <span class="pos-rx-panel-state"><i class="mdi mdi-progress-alert"></i> Perlu dilengkapi</span>
                                </header>
                                <div id="prescriptionModalDetailsSlot"></div>
                            </section>
                            <section class="pos-prescription-modal-editor pos-rx-work-panel" aria-label="Obat dan etiket resep">
                                <header class="pos-rx-panel-header">
                                    <span class="pos-rx-panel-index">02</span>
                                    <span class="pos-rx-panel-copy">
                                        <small>PENYIAPAN RESEP</small>
                                        <strong id="prescriptionPreparationTitle">Obat & etiket</strong>
                                        <p id="prescriptionPreparationCopy">Atur jumlah, signa, dan detail klinis setiap obat.</p>
                                    </span>
                                    <span class="pos-rx-panel-live"><i class="mdi mdi-access-point"></i> Stok live</span>
                                </header>
                                <div class="pos-rx-compound-slot" id="prescriptionModalCompoundSlot"></div>
                                <div id="prescriptionModalCartSlot"></div>
                            </section>
                        </div>
                    </div>

                    <footer class="pos-prescription-cashier-footer">
                        <div class="pos-rx-footer-status">
                            <div class="pos-prescription-flow-readiness" id="prescriptionFlowReadiness">
                                <span><i class="mdi mdi-progress-alert"></i></span>
                                <div>
                                    <small>KESIAPAN RESEP</small>
                                    <strong id="prescriptionFlowReadinessText">Lengkapi data resep dan obat</strong>
                                </div>
                            </div>
                            <div class="pos-prescription-flow-metrics">
                                <span><small>ITEM RESEP</small><strong id="prescriptionModalItemCount">0 item</strong></span>
                                <span><small>TOTAL SEMENTARA</small><strong id="prescriptionModalGrandTotal">Rp 0</strong></span>
                            </div>
                        </div>
                        <div class="pos-prescription-flow-actions">
                            <button type="button" class="btn pos-prescription-draft-button" id="savePrescriptionDraftBtn" title="Simpan proses resep sebagai draft">
                                <i class="mdi mdi-content-save-outline"></i> Draft
                            </button>
                            <button type="button" class="btn pos-finish-prescription-button" id="finishPrescriptionFlowBtn" aria-label="Bayar transaksi resep">
                                <span><small>Simpan resep & lanjut</small>Bayar</span>
                                <i class="mdi mdi-credit-card-check-outline"></i>
                            </button>
                        </div>
                    </footer>
                </section>

                <section class="pos-compound-preview-step d-none" id="compoundPreviewStep" aria-label="Preview resep racikan sebelum pembayaran">
                    <header class="pos-compound-preview-header">
                        <div class="pos-compound-preview-brand">
                            <span><i class="mdi mdi-clipboard-check-multiple-outline"></i></span>
                            <div>
                                <small>VERIFIKASI SEBELUM PEMBAYARAN</small>
                                <strong>Preview resep racikan</strong>
                                <p>Pastikan pasien, komposisi, jumlah, dan etiket sudah benar.</p>
                            </div>
                        </div>
                        <div class="pos-compound-preview-header-actions">
                            <div class="pos-compound-preview-status">
                                <i class="mdi mdi-shield-check-outline"></i>
                                <span><small>STATUS</small><strong>Siap dikoreksi</strong></span>
                            </div>
                            <button type="button" class="btn pos-compound-preview-confirm pos-compound-preview-pay"
                                id="confirmCompoundPreviewBtn" aria-label="Bayar resep racikan">
                                <span><small>Semua data sudah sesuai</small>Bayar</span>
                                <i class="mdi mdi-credit-card-check-outline"></i>
                            </button>
                        </div>
                    </header>

                    <div class="pos-compound-preview-body">
                        <div class="pos-compound-preview-shell">
                            <section class="pos-compound-preview-summary" id="compoundPreviewSummary"></section>
                            <section class="pos-compound-preview-groups" id="compoundPreviewGroups" aria-label="Rincian kelompok racikan"></section>
                        </div>
                    </div>

                    <footer class="pos-compound-preview-footer">
                        <div class="pos-compound-preview-assurance">
                            <i class="mdi mdi-information-slab-circle-outline"></i>
                            <span><strong>Checkpoint farmasi</strong><small>Pembayaran hanya dibuka setelah preview ini disetujui.</small></span>
                        </div>
                        <div class="pos-compound-preview-actions">
                            <button type="button" class="btn pos-compound-preview-edit" id="editCompoundPreviewBtn">
                                <i class="mdi mdi-pencil-outline"></i> Koreksi resep
                            </button>
                        </div>
                    </footer>
                </section>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.penjualan.pos.jsMain")
@endpush
