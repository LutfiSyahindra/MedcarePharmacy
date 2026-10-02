@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.pembelianPenerimaan.pembelian.partials.style")
@endpush

@section("content")
    <div class="purchase-page">
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

            <aside class="purchase-estimate-panel" aria-label="Total nilai estimasi purchase order">
                <span class="purchase-estimate-icon"><i class="mdi mdi-cash-multiple"></i></span>
                <div class="purchase-estimate-copy">
                    <span>Total Nilai Estimasi PO</span>
                    <strong id="purchaseTotalValue">Rp 0</strong>
                    <small>Akumulasi nilai seluruh PO yang tersimpan.</small>
                </div>
            </aside>
        </section>

        <div class="purchase-stats-grid" aria-label="Ringkasan purchase order">
            <article class="purchase-stat is-total">
                <span class="purchase-stat-icon"><i class="mdi mdi-file-document-multiple-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="purchaseTotalCount">0</strong>
                    <span>Total Purchase Order</span>
                    <small>Seluruh PO yang tersimpan.</small>
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
                        <p>Cari, filter, lalu buka detail transaksi yang dibutuhkan.</p>
                    </div>
                </div>

                <div class="purchase-table-tools">
                    <div class="purchase-search">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="searchPembelian"
                            placeholder="Cari nomor PO, distributor, branch, atau user..." autocomplete="off"
                            aria-label="Cari purchase order">
                        <button type="button" class="purchase-search-clear" id="clearPurchaseSearch"
                            aria-label="Hapus pencarian" title="Hapus pencarian">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary purchase-icon-btn" id="refreshPurchaseTable"
                        title="Muat ulang data" aria-label="Muat ulang data">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                    <button type="button"
                        class="btn btn-outline-secondary purchase-icon-btn purchase-mobile-filter-toggle"
                        id="purchaseMobileFilterToggle" aria-controls="purchaseFilterBar" aria-expanded="false"
                        title="Buka filter" aria-label="Buka filter">
                        <i class="mdi mdi-tune-variant"></i>
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#pembelianModal">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        <span>Tambah PO</span>
                    </button>
                </div>
            </div>

            <form class="purchase-medicine-search" id="purchaseMedicineSearchForm">
                <label for="searchPurchaseMedicine">
                    <i class="mdi mdi-pill"></i>
                    Cari obat di PO
                </label>
                <div class="purchase-medicine-search-controls">
                    <div class="purchase-medicine-search-select">
                        <select id="searchPurchaseMedicine" class="form-select"
                            aria-describedby="purchaseMedicineSearchHint">
                            <option value=""></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Cari di Semua PO</button>
                </div>
                <small id="purchaseMedicineSearchHint">
                    Ketik nama atau kode, lalu pilih obat. Pencarian dimulai dari semua tanggal dan status.
                </small>
                <small id="purchaseMedicineSearchResult" class="d-none" role="status" aria-live="polite"></small>
            </form>

            <div class="purchase-filter-bar" id="purchaseFilterBar">
                <div class="purchase-filter-group" aria-label="Filter status purchase order">
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

                <div class="purchase-filter-controls">
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
                        <label for="purchasePageLength">Tampilkan data</label>
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

            <div class="table-responsive purchase-table-wrap">
                <table id="tablePembelian" class="table purchase-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Approval</th>
                            <th>Nomor PO</th>
                            <th>Tanggal PO</th>
                            <th>Branch</th>
                            <th>Distributor</th>
                            <th>Item / Qty</th>
                            <th>Total Estimasi</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>User</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="purchase-table-hint">
                <i class="mdi mdi-information-outline"></i>
                Gunakan filter status untuk mempersempit daftar, lalu klik ikon mata untuk melihat rincian obat.
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.pembelianPenerimaan.pembelian.jsMain")
@endpush
