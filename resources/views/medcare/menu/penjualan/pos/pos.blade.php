@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.penjualan.pos.partials.style")
@endpush

@section("content")
    <main class="pos-page pos-page--cashier" aria-label="Kasir point of sale">
        <header class="pos-appbar">
            <div class="pos-brand">
                <span class="pos-brand-mark"><i class="mdi mdi-storefront-outline"></i></span>
                <div class="pos-brand-copy">
                    <div class="pos-eyebrow">MEDCARE · POINT OF SALE</div>
                    <div class="pos-brand-title">
                        <h1>Kasir</h1>
                        <span>Smart pharmacy checkout</span>
                    </div>
                </div>
            </div>

            <div class="pos-appbar-center">
                <span class="pos-session-status" id="posConnection"><i class="mdi mdi-circle"></i> Sistem online</span>
                <span class="pos-clock"><i class="mdi mdi-clock-outline"></i><span id="posClock">Memuat waktu...</span></span>
                <span class="pos-cashier-profile" title="Kasir aktif">
                    <span class="pos-cashier-avatar">{{ strtoupper(substr(auth()->user()->name ?? "K", 0, 1)) }}</span>
                    <span><small>Kasir aktif</small><strong>{{ auth()->user()->name ?? "Kasir" }}</strong></span>
                </span>
            </div>

            <div class="pos-appbar-actions">
                <button type="button" class="btn pos-btn-quiet" id="newTransactionBtn" title="Transaksi baru (F8)">
                    <i class="mdi mdi-plus-circle-outline"></i><span>Transaksi baru</span><kbd>F8</kbd>
                </button>
                <a href="{{ route("penjualan.pos.history") }}" class="btn pos-btn-quiet" title="Riwayat transaksi">
                    <i class="mdi mdi-history"></i><span>Riwayat</span>
                </a>
                <button type="button" class="btn pos-btn-quiet" id="printLastReceiptBtn" disabled title="Cetak struk transaksi terakhir">
                    <i class="mdi mdi-printer-outline"></i><span>Struk terakhir</span>
                </button>
            </div>
        </header>

        <section class="pos-flow" aria-label="Progres transaksi">
            <div class="pos-flow-track" aria-hidden="true"><span id="posFlowProgress"></span></div>
            <div class="pos-flow-step is-active" id="posFlowProduct">
                <span class="pos-flow-icon"><i class="mdi mdi-barcode-scan"></i><b>1</b></span>
                <span><small>Langkah 1</small><strong>Pilih produk</strong></span>
            </div>
            <div class="pos-flow-step" id="posFlowCart">
                <span class="pos-flow-icon"><i class="mdi mdi-cart-outline"></i><b>2</b></span>
                <span><small>Langkah 2</small><strong>Review keranjang</strong></span>
            </div>
            <div class="pos-flow-step" id="posFlowPayment">
                <span class="pos-flow-icon"><i class="mdi mdi-credit-card-check-outline"></i><b>3</b></span>
                <span><small>Langkah 3</small><strong>Terima pembayaran</strong></span>
            </div>
            <div class="pos-flow-summary">
                <span><small>Item</small><strong id="headerCartCount">0</strong></span>
                <span><small>Total berjalan</small><strong id="headerGrandTotal">Rp 0</strong></span>
            </div>
        </section>

        <div class="pos-workspace" id="posWorkspace">
            <span id="posCatalogHomeAnchor" class="d-none" aria-hidden="true"></span>
            <aside class="pos-catalog" aria-label="Pilih produk">
                <section class="pos-surface pos-product-panel">
                    <header class="pos-catalog-hero">
                        <div class="pos-catalog-hero-main">
                            <span class="pos-catalog-step">01</span>
                            <div>
                                <span class="pos-section-kicker">TAMBAH ITEM</span>
                                <h2>Pilih produk penjualan</h2>
                                <p>Cari obat, tentukan satuan, lalu periksa ketersediaan stok.</p>
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
                            <span class="pos-search-eyebrow"><i class="mdi mdi-lightning-bolt"></i> Pencarian cepat</span>
                            <span class="pos-search-shortcut"><kbd>F2</kbd></span>
                        </div>

                        <label class="pos-search-title" for="productSearch">
                            Cari produk yang akan dijual
                            <small>Nama obat, kode produk, kandungan, indikasi, atau barcode</small>
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
                                <span><small>Masukkan produk</small>Tambahkan ke keranjang</span>
                                <i class="mdi mdi-arrow-right"></i>
                            </button>
                        </div>
                    </section>

                    <section class="pos-catalog-block pos-stock-block" aria-labelledby="stockInfoTitle">
                        <div class="pos-catalog-block-head">
                            <div>
                                <span class="pos-catalog-block-icon is-emerald"><i class="mdi mdi-shield-check-outline"></i></span>
                                <span>
                                    <small>INFORMASI STOK</small>
                                    <strong id="stockInfoTitle">Harga & alokasi FEFO</strong>
                                </span>
                            </div>
                            <span class="pos-fefo-badge"><i class="mdi mdi-autorenew"></i> Otomatis</span>
                        </div>

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
                    </section>
                </section>

                <section class="pos-shortcut-card" aria-label="Bantuan keyboard">
                    <span><i class="mdi mdi-lightning-bolt-outline"></i> Alur cepat</span>
                    <div><span><kbd>F2</kbd> Cari</span><span><kbd>Enter</kbd> Tambah</span><span><kbd>F9</kbd> Bayar</span></div>
                </section>
            </aside>

            <section class="pos-checkout" aria-label="Transaksi aktif">
                <section class="pos-surface pos-transaction-panel" id="transactionPanel">
                    <div class="pos-transaction-hero">
                        <span class="pos-transaction-orbit" aria-hidden="true"></span>
                        <div class="pos-transaction-head">
                            <div class="pos-panel-heading pos-transaction-heading">
                                <span class="pos-transaction-number">02</span>
                                <div>
                                    <span class="pos-section-kicker"><i class="mdi mdi-circle"></i> TRANSAKSI AKTIF</span>
                                    <h2>Keranjang penjualan</h2>
                                    <p>Review item, ketersediaan stok, dan nilai transaksi dalam satu tampilan.</p>
                                </div>
                            </div>
                            <span class="pos-draft-pill" id="transactionState" aria-live="polite">
                                <i class="mdi mdi-circle-medium"></i><span>Belum disimpan</span>
                            </span>
                        </div>

                        <div class="pos-transaction-insights" aria-label="Ringkasan keranjang">
                            <div class="pos-insight-card">
                                <span class="pos-insight-icon is-indigo"><i class="mdi mdi-cart-variant"></i></span>
                                <span><small>JENIS ITEM</small><strong id="cartItemCount">0 item</strong></span>
                            </div>
                            <div class="pos-insight-card">
                                <span class="pos-insight-icon is-cyan"><i class="mdi mdi-counter"></i></span>
                                <span><small>TOTAL KUANTITAS</small><strong id="cartQtyCount">0 qty</strong></span>
                            </div>
                            <div class="pos-insight-card">
                                <span class="pos-insight-icon is-amber"><i class="mdi mdi-cash-multiple"></i></span>
                                <span><small>NILAI ITEM</small><strong id="cartRunningTotal">Rp 0</strong></span>
                            </div>
                            <div class="pos-insight-card" id="cartHealthCard">
                                <span class="pos-insight-icon is-emerald"><i class="mdi mdi-shield-check-outline"></i></span>
                                <span><small>KESEHATAN STOK</small><strong id="cartStockHealth">Menunggu item</strong></span>
                            </div>
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

                    <span id="posDetailsHomeAnchor" class="d-none" aria-hidden="true"></span>
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
                                <div class="pos-field">
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
                                    <label for="customerName"><i class="mdi mdi-account-outline"></i> Nama pelanggan</label>
                                    <input type="text" id="customerName" class="form-control" placeholder="Contoh: Budi Santoso">
                                </div>
                                <div class="pos-field">
                                    <label for="customerPhone"><i class="mdi mdi-phone-outline"></i> No. HP</label>
                                    <input type="text" id="customerPhone" class="form-control" placeholder="Opsional">
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

                                <section class="pos-compound-setup d-none" id="compoundSetup" aria-label="Pengaturan racikan">
                                    <div class="pos-compound-steps">
                                        <span><b>1</b> Pilih kelompok aktif</span>
                                        <i class="mdi mdi-chevron-right"></i>
                                        <span><b>2</b> Tambahkan obat</span>
                                        <i class="mdi mdi-chevron-right"></i>
                                        <span><b>3</b> Isi dosis & etiket kelompok</span>
                                    </div>
                                    <div class="pos-compound-controls">
                                        <label class="pos-compound-group-control" for="activeCompoundGroup">
                                            <span>Kelompok tujuan obat berikutnya</span>
                                            <div>
                                                <select id="activeCompoundGroup" class="form-select" aria-label="Kelompok racikan aktif"></select>
                                                <button type="button" class="btn pos-new-compound-group" id="newCompoundGroupBtn">
                                                    <i class="mdi mdi-plus"></i> R/ baru
                                                </button>
                                            </div>
                                            <small>Obat yang ditambahkan dari katalog akan masuk ke kelompok aktif ini.</small>
                                        </label>
                                        <label class="pos-embalase-control" for="embalase">
                                            <span>Biaya embalase racikan</span>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" id="embalase" class="form-control" min="0" step="1" value="0" placeholder="0" inputmode="decimal">
                                            </div>
                                            <small>Ditambahkan satu kali ke total transaksi racikan.</small>
                                        </label>
                                    </div>
                                    <div class="pos-compound-group-summary" id="compoundGroupSummary" aria-live="polite"></div>
                                </section>

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
                                <strong>Daftar item transaksi</strong>
                                <small id="cartEditorCopy">Ubah jumlah atau diskon langsung pada setiap baris.</small>
                            </span>
                        </div>
                        <div class="pos-cart-actions">
                            <span class="pos-live-label"><i class="mdi mdi-access-point"></i> STOK LIVE</span>
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
                                    <th style="width:152px"><i class="mdi mdi-counter"></i> Jumlah</th>
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

                    <div class="pos-cart-footnote">
                        <span><i class="mdi mdi-information-slab-circle-outline"></i> Harga dan batch akan divalidasi kembali saat jumlah item berubah.</span>
                        <span><i class="mdi mdi-lock-check-outline"></i> Perhitungan tersinkron</span>
                    </div>
                </section>

                <section class="pos-checkout-stage" id="posPaymentLayout" aria-labelledby="posPaymentTitle">
                    <header class="pos-checkout-hero">
                        <div class="pos-checkout-title">
                            <span class="pos-checkout-step" aria-hidden="true">03</span>
                            <div>
                                <span class="pos-section-kicker">PEMBAYARAN</span>
                                <h2 id="posPaymentTitle">Terima pembayaran</h2>
                                <p>Pilih metode, masukkan nominal yang diterima, lalu periksa kembali total transaksi.</p>
                            </div>
                        </div>
                        <div class="pos-checkout-hero-side">
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

                            <div class="pos-adjustment-card">
                                <div class="pos-adjustment-card-head">
                                    <span><i class="mdi mdi-tune-variant"></i> Penyesuaian transaksi</span>
                                    <small>Opsional</small>
                                </div>
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
                                <span><small>Transaksi sudah benar</small>Selesaikan pembayaran</span><i class="mdi mdi-arrow-right"></i><kbd>F9</kbd>
                            </button>
                        </div>
                    </footer>
                </section>
            </section>
        </div>
    </main>

    <div class="modal fade pos-prescription-type-modal" id="prescriptionTypeModal" tabindex="-1" aria-labelledby="prescriptionTypeModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <section class="pos-prescription-type-step" id="prescriptionTypeStep">
                    <div class="pos-prescription-type-step-shell">
                        <div class="modal-header">
                            <div class="pos-prescription-modal-heading">
                                <span><i class="mdi mdi-prescription"></i></span>
                                <div>
                                    <small>LANGKAH AWAL LAYANAN RESEP</small>
                                    <h2 class="modal-title" id="prescriptionTypeModalTitle">Pilih jenis resep</h2>
                                    <p>Tentukan cara obat disiapkan. Setelah dipilih, seluruh proses resep dilanjutkan di workspace yang luas.</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" id="closePrescriptionTypeBtn" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="pos-prescription-modal-intro">
                                <span><i class="mdi mdi-information-outline"></i></span>
                                <div>
                                    <strong>Pilih berdasarkan cara penyiapan obat</strong>
                                    <small>Jenis resep dapat diubah kembali selama proses resep berlangsung.</small>
                                </div>
                            </div>

                            <div class="pos-prescription-type-grid" role="group" aria-label="Pilih jenis resep">
                                <button type="button" class="pos-prescription-type-option" data-prescription-type="penjualan_resep" aria-pressed="false">
                                    <span class="pos-prescription-type-icon"><i class="mdi mdi-pill-multiple"></i></span>
                                    <span class="pos-prescription-type-copy">
                                        <small>ETIKET PER OBAT</small>
                                        <strong>Resep Non Racikan</strong>
                                        <span>Setiap obat diberikan secara terpisah dan mempunyai aturan pakainya sendiri.</span>
                                        <span class="pos-prescription-type-points">
                                            <b><i class="mdi mdi-check"></i> Signa per obat</b>
                                            <b><i class="mdi mdi-check"></i> Tanpa kelompok R/</b>
                                        </span>
                                    </span>
                                    <i class="mdi mdi-check-circle pos-prescription-type-check"></i>
                                </button>
                                <button type="button" class="pos-prescription-type-option" data-prescription-type="penjualan_racikan" aria-pressed="false">
                                    <span class="pos-prescription-type-icon"><i class="mdi mdi-mortar-pestle-plus"></i></span>
                                    <span class="pos-prescription-type-copy">
                                        <small>KOMPONEN DIGABUNG</small>
                                        <strong>Resep Racikan</strong>
                                        <span>Beberapa obat disatukan ke dalam satu atau beberapa kelompok racikan.</span>
                                        <span class="pos-prescription-type-points">
                                            <b><i class="mdi mdi-check"></i> Kelompok R/</b>
                                            <b><i class="mdi mdi-check"></i> Dosis tiap komponen</b>
                                        </span>
                                    </span>
                                    <i class="mdi mdi-check-circle pos-prescription-type-check"></i>
                                </button>
                            </div>

                            <div class="pos-prescription-modal-help">
                                <i class="mdi mdi-lightbulb-on-outline"></i>
                                <span><strong>Petunjuk:</strong> pilih <b>Racikan</b> bila apotek perlu mencampur dua atau lebih obat menjadi satu sediaan.</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn pos-prescription-modal-cancel" id="cancelPrescriptionTypeBtn">Batal</button>
                            <small>Pilih salah satu kartu untuk membuka workspace proses resep.</small>
                        </div>
                    </div>
                </section>

                <section class="pos-prescription-cashier-step d-none" id="prescriptionCashierStep" aria-label="Workspace proses resep">
                    <header class="pos-prescription-cashier-header">
                        <div class="pos-prescription-cashier-brand">
                            <span><i class="mdi mdi-prescription"></i></span>
                            <div>
                                <small>WORKSPACE RESEP</small>
                                <strong id="prescriptionFlowTitle">Resep Non Racikan</strong>
                                <p>Lengkapi data resep, obat, etiket, dan stok sebelum kembali ke pembayaran.</p>
                            </div>
                        </div>
                        <div class="pos-prescription-flow-steps" aria-label="Tahapan proses resep">
                            <span class="is-active"><b>1</b> Data resep</span>
                            <i class="mdi mdi-chevron-right"></i>
                            <span><b>2</b> Obat & etiket</span>
                            <i class="mdi mdi-chevron-right"></i>
                            <span><b>3</b> Lanjut bayar</span>
                        </div>
                        <button type="button" class="btn pos-prescription-flow-cancel" id="cancelPrescriptionFlowBtn">
                            <i class="mdi mdi-close"></i> Batalkan proses
                        </button>
                    </header>

                    <div class="pos-prescription-cashier-body">
                        <div class="pos-prescription-cashier-layout">
                            <div class="pos-prescription-modal-catalog" id="prescriptionModalCatalogSlot" aria-label="Katalog obat resep"></div>
                            <section class="pos-prescription-modal-editor" aria-label="Detail dan keranjang resep">
                                <div id="prescriptionModalDetailsSlot"></div>
                                <div id="prescriptionModalCartSlot"></div>
                            </section>
                        </div>
                    </div>

                    <footer class="pos-prescription-cashier-footer">
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
                        <div class="pos-prescription-flow-actions">
                            <button type="button" class="btn pos-prescription-draft-button" id="savePrescriptionDraftBtn" title="Simpan proses resep sebagai draft">
                                <i class="mdi mdi-content-save-outline"></i> Draft
                            </button>
                            <button type="button" class="btn pos-finish-prescription-button" id="finishPrescriptionFlowBtn">
                                <span><small>Simpan proses resep</small>Selesai & lanjut pembayaran</span>
                                <i class="mdi mdi-arrow-right"></i>
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
