@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.pembelianPenerimaan.pembelian.partials.style")
    @include("medcare.menu.pembelianPenerimaan.pembelian.partials.modalFormStyle")
    @include("medcare.menu.pembelianPenerimaan.pembelian.partials.mobileFormStyle")
@endpush

@section("content")
    <div class="purchase-page">
        <div id="purchaseRecoveryNotice" class="alert alert-warning d-none" role="status">
            <p class="mb-2">Ada isian PO yang belum selesai disimpan di tab ini. Pulihkan untuk melanjutkan.</p>
            <button type="button" class="btn btn-sm btn-primary" id="restorePurchaseRecovery">Pulihkan isian PO</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="discardPurchaseRecovery">Hapus cadangan</button>
        </div>
        @include("medcare.menu.pembelianPenerimaan.pembelian.modalMain")
        @include("medcare.menu.pembelianPenerimaan.pembelian.modalDetail")

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Pembelian dan Penerimaan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pembelian Obat</li>
            </ol>
        </nav>

        <section class="purchase-hero">
            <div class="purchase-hero-copy">
                <span class="purchase-kicker">
                    <i class="mdi mdi-cart-arrow-down"></i>
                    Procurement Workspace
                </span>
                <h2>Kelola purchase order obat dengan alur yang lebih jelas.</h2>
                <p>
                    Buat PO, pantau proses persetujuan, dan temukan transaksi pembelian dengan cepat dalam satu
                    tampilan yang ringkas.
                </p>
                <div class="purchase-hero-actions">
                    <button type="button" class="btn btn-light" data-bs-toggle="modal"
                        data-bs-target="#pembelianModal">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        Buat Purchase Order
                    </button>
                    <button type="button" class="btn btn-outline-light" id="scrollPurchaseTable">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        Lihat Daftar PO
                    </button>
                </div>
            </div>

            <aside class="purchase-estimate-panel" aria-labelledby="purchaseEstimateLabel">
                <div class="purchase-estimate-heading">
                    <span id="purchaseEstimateLabel">Total Nilai Estimasi PO</span>
                    <span class="purchase-estimate-icon"><i class="mdi mdi-cash-multiple" aria-hidden="true"></i></span>
                </div>
                <div class="purchase-estimate-copy">
                    <strong id="purchaseTotalValue">Rp 0</strong>
                </div>
                <div class="purchase-estimate-footer">
                    <div class="purchase-estimate-period">
                        <i class="mdi mdi-calendar-range-outline" aria-hidden="true"></i>
                        <span id="purchaseEstimatePeriod">Memuat periode...</span>
                    </div>
                    <small>Akumulasi nilai PO sesuai status, periode, dan obat terpilih.</small>
                </div>
            </aside>
        </section>

        <div class="purchase-stats-grid" aria-label="Ringkasan purchase order">
            <article class="purchase-stat is-total">
                <span class="purchase-stat-icon"><i class="mdi mdi-file-document-multiple-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="purchaseTotalCount">0</strong>
                    <span>Total Purchase Order</span>
                    <small>Sesuai periode dan obat terpilih.</small>
                    <div class="purchase-stat-metrics" aria-label="Ringkasan item dan kuantitas">
                        <span><b id="purchaseTotalItem">0</b> item</span>
                        <span><b id="purchaseTotalQty">0</b> qty</span>
                    </div>
                </div>
            </article>
            <article class="purchase-stat is-draft">
                <span class="purchase-stat-icon"><i class="mdi mdi-file-clock-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="purchasePendingCount">0</strong>
                    <span>Menunggu Proses</span>
                    <small>Draft dan menunggu approval.</small>
                </div>
            </article>
            <article class="purchase-stat is-approved">
                <span class="purchase-stat-icon"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="purchaseApprovedCount">0</strong>
                    <span>Telah Disetujui</span>
                    <small>PO siap ditindaklanjuti.</small>
                </div>
            </article>
            <article class="purchase-stat is-completed">
                <span class="purchase-stat-icon"><i class="mdi mdi-package-variant-closed-check"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="purchaseCompletedCount">0</strong>
                    <span>Selesai</span>
                    <small>PO telah diterima lengkap.</small>
                </div>
            </article>
        </div>

        <section class="purchase-table-section" id="purchaseTableSection">
            <div class="purchase-table-toolbar">
                <div class="purchase-table-title">
                    <span class="purchase-table-title-icon"><i class="mdi mdi-clipboard-text-outline"></i></span>
                    <div>
                        <h5>Daftar Purchase Order</h5>
                        <p>Pantau pesanan dan kelola pembelian obat Anda.</p>
                    </div>
                </div>

                <div class="purchase-table-tools">
                    <button type="button" class="btn btn-outline-secondary purchase-icon-btn" id="refreshPurchaseTable"
                        title="Muat ulang data" aria-label="Muat ulang data">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                    <button type="button"
                        class="btn btn-outline-secondary purchase-icon-btn purchase-mobile-filter-toggle"
                        id="purchaseMobileFilterToggle" aria-controls="purchasePeriodControls" aria-expanded="false"
                        title="Buka filter periode" aria-label="Buka filter periode">
                        <i class="mdi mdi-tune-variant"></i>
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#pembelianModal">
                        <i class="mdi mdi-plus"></i>
                        <span>Buat PO Baru</span>
                    </button>
                </div>
            </div>

            <div class="purchase-search-workspace">
                <div class="purchase-search-field">
                    <label for="searchPembelian">Cari purchase order</label>
                    <div class="purchase-search">
                        <i class="mdi mdi-magnify" aria-hidden="true"></i>
                        <input type="search" id="searchPembelian"
                            placeholder="Nomor PO, distributor, cabang, atau pembuat..." autocomplete="off"
                            aria-describedby="purchaseSearchHint">
                        <button type="button" class="purchase-search-clear" id="clearPurchaseSearch"
                            aria-label="Hapus pencarian" title="Hapus pencarian" tabindex="-1">
                            <i class="mdi mdi-close" aria-hidden="true"></i>
                        </button>
                    </div>
                    <small id="purchaseSearchHint">Cari transaksi sesuai status dan periode yang dipilih.</small>
                </div>

                <form class="purchase-medicine-search" id="purchaseMedicineSearchForm">
                    <label for="searchPurchaseMedicine">
                        <i class="mdi mdi-pill" aria-hidden="true"></i>
                        Telusuri berdasarkan obat
                    </label>
                    <div class="purchase-medicine-search-controls">
                        <div class="purchase-medicine-search-select">
                            <select id="searchPurchaseMedicine" class="form-select"
                                aria-describedby="purchaseMedicineSearchHint">
                                <option value=""></option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-primary" title="Cari obat di semua PO"
                            aria-label="Cari obat di semua PO">
                            <i class="mdi mdi-magnify" aria-hidden="true"></i>
                            <span>Cari obat</span>
                        </button>
                    </div>
                    <small id="purchaseMedicineSearchHint">
                        Pilih obat untuk mencari di semua tanggal dan status.
                    </small>
                    <small id="purchaseMedicineSearchResult" class="d-none" role="status" aria-live="polite"></small>
                </form>
            </div>

            <div class="purchase-filter-bar" id="purchaseFilterBar">
                <div class="purchase-filter-group" role="group" aria-label="Filter status purchase order">
                    <span class="purchase-filter-label">
                        <i class="mdi mdi-filter-variant"></i>
                        Status
                    </span>
                    <button type="button" class="purchase-filter-chip is-active" data-status=""
                        aria-pressed="true">
                        Semua
                        <span class="purchase-filter-count" id="purchaseAllFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="draft" aria-pressed="false">
                        Draft
                        <span class="purchase-filter-count" id="purchaseDraftFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="waiting_approval"
                        aria-pressed="false">
                        Menunggu Approval
                        <span class="purchase-filter-count" id="purchaseWaitingFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="approved" aria-pressed="false">
                        Disetujui
                        <span class="purchase-filter-count" id="purchaseApprovedFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="dalam_penerimaan" aria-pressed="false">
                        Dalam Penerimaan
                        <span class="purchase-filter-count" id="purchaseReceivingFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="selesai" aria-pressed="false">
                        Selesai
                        <span class="purchase-filter-count" id="purchaseCompletedFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="rejected" aria-pressed="false">
                        Ditolak
                        <span class="purchase-filter-count" id="purchaseRejectedFilterCount">0</span>
                    </button>
                </div>

                <div class="purchase-filter-controls" id="purchasePeriodControls">
                    <div class="purchase-date-filter">
                        <label for="purchaseDateRange">
                            <i class="mdi mdi-calendar-range-outline"></i>
                            Periode PO
                        </label>
                        <div class="purchase-date-input">
                            <i class="mdi mdi-calendar-month-outline"></i>
                            <input type="text" id="purchaseDateRange" placeholder="Pilih rentang tanggal"
                                autocomplete="off" aria-label="Pilih rentang tanggal purchase order">
                            <button type="button" id="clearPurchaseDateRange" aria-label="Hapus filter tanggal"
                                title="Hapus filter tanggal">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                        <select id="purchaseDatePreset" class="form-select form-select-sm"
                            aria-label="Pilih periode cepat">
                            <option value="">Periode cepat</option>
                            <option value="all">Semua Tanggal</option>
                            <option value="today">Hari Ini</option>
                            <option value="7days">7 Hari Terakhir</option>
                            <option value="30days">30 Hari Terakhir</option>
                            <option value="this_month">Bulan Ini</option>
                        </select>
                    </div>

                    <div class="purchase-page-size">
                        <label for="purchasePageLength">Baris per halaman</label>
                        <select id="purchasePageLength" class="form-select form-select-sm"
                            aria-label="Jumlah baris per halaman">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="purchase-results-bar">
                <div class="purchase-results-context" role="status" aria-live="polite" aria-atomic="true">
                    <span class="purchase-results-dot" aria-hidden="true"></span>
                    <span id="purchaseListContext">Memuat daftar purchase order...</span>
                </div>
                <button type="button" class="purchase-reset-filters" id="resetPurchaseFilters">
                    <i class="mdi mdi-filter-remove-outline" aria-hidden="true"></i>
                    Reset filter
                </button>
            </div>

            <div class="table-responsive purchase-table-wrap">
                <table id="tablePembelian" class="table purchase-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Approval</th>
                            <th>Purchase Order</th>
                            <th>Tanggal PO</th>
                            <th>Cabang</th>
                            <th>Distributor</th>
                            <th>Item / Qty</th>
                            <th>Total Estimasi</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Dibuat oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="purchase-table-hint">
                <i class="mdi mdi-information-outline"></i>
                Klik nomor PO atau ikon mata untuk melihat rincian pesanan. Status penerimaan dapat dibuka jika sudah tersedia.
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.partials.surat-pesanan-print-options")
    @include("medcare.menu.pembelianPenerimaan.pembelian.jsMain")
@endpush
