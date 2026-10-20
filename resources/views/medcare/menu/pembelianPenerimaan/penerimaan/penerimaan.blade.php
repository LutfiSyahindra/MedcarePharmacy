@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.partials.style")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.partials.workspace-style")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.partials.detail-style")
@endpush

@section("content")
    <div class="purchase-page receive-workspace">
        @include("medcare.menu.pembelianPenerimaan.penerimaan.modalMain")
        @include("medcare.menu.pembelianPenerimaan.penerimaan.modalDetail")
        @include("medcare.menu.pembelianPenerimaan.penerimaan.modalPurchaseOrder")

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Pembelian dan Penerimaan</li>
                <li class="breadcrumb-item active" aria-current="page">Penerimaan Barang</li>
            </ol>
        </nav>

        <header class="receive-page-hero" aria-labelledby="receivePageTitle">
            <div class="receive-hero-copy">
                <span class="receive-eyebrow">
                    <i class="mdi mdi-package-variant-closed-check" aria-hidden="true"></i>
                    Pembelian &amp; Persediaan
                </span>
                <h1 id="receivePageTitle">Penerimaan Barang</h1>
                <p>Catat barang masuk, kelola faktur dan batch, lalu posting ke stok.</p>
                <div class="receive-hero-actions">
                    <button type="button" class="btn btn-light" id="openPenerimaanModal" data-bs-toggle="modal"
                        data-bs-target="#receivePoPickerModal">
                        <i class="mdi mdi-plus" aria-hidden="true"></i>
                        Buat Penerimaan
                    </button>
                    <button type="button" class="btn receive-hero-link" id="scrollReceiveTable">
                        Lihat daftar <i class="mdi mdi-arrow-down" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <ol class="receive-steps" aria-label="Alur penerimaan barang">
                <li>
                    <span class="receive-step-number">01</span>
                    <div>
                        <strong>Pilih pembelian</strong>
                        <small>PO disetujui dengan sisa barang.</small>
                    </div>
                    <i class="mdi mdi-file-check-outline" aria-hidden="true"></i>
                </li>
                <li>
                    <span class="receive-step-number">02</span>
                    <div>
                        <strong>Periksa penerimaan</strong>
                        <small>Lengkapi faktur, qty, batch, dan kedaluwarsa.</small>
                    </div>
                    <i class="mdi mdi-barcode-scan" aria-hidden="true"></i>
                </li>
                <li>
                    <span class="receive-step-number">03</span>
                    <div>
                        <strong>Posting ke stok</strong>
                        <small>Simpan draft atau posting setelah sesuai.</small>
                    </div>
                    <i class="mdi mdi-warehouse" aria-hidden="true"></i>
                </li>
            </ol>
        </header>

        <section class="receive-overview" aria-labelledby="receiveOverviewTitle">
            <div class="receive-section-heading">
                <h2 id="receiveOverviewTitle">Ringkasan penerimaan</h2>
                <span>Sesuai pilihan PO, obat, dan periode</span>
            </div>
            <div class="receive-metrics">
                <article class="receive-metric">
                    <div class="receive-metric-label"><span>Total penerimaan</span><i class="mdi mdi-clipboard-list-outline" aria-hidden="true"></i></div>
                    <strong id="receiveTotalCount">0</strong>
                    <small>Seluruh transaksi</small>
                </article>
                <article class="receive-metric is-draft">
                    <div class="receive-metric-label"><span>Draft</span><i class="mdi mdi-file-clock-outline" aria-hidden="true"></i></div>
                    <strong id="receiveDraftCount">0</strong>
                    <small>Menunggu posting</small>
                </article>
                <article class="receive-metric is-posted">
                    <div class="receive-metric-label"><span>Posted</span><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i></div>
                    <strong id="receivePostedCount">0</strong>
                    <small>Sudah masuk stok</small>
                </article>
                <article class="receive-metric is-value">
                    <div class="receive-metric-label"><span>Total nilai faktur</span><i class="mdi mdi-wallet-outline" aria-hidden="true"></i></div>
                    <strong id="receiveTotalValue">Rp 0</strong>
                    <small>Draft &amp; posted</small>
                </article>
            </div>
            <div class="receive-overview-footer">
                <span><i class="mdi mdi-package-variant" aria-hidden="true"></i> Total qty <strong id="receiveTotalQty">0</strong><small> (draft &amp; posted)</small></span>
                <span><i class="mdi mdi-cancel" aria-hidden="true"></i> Dibatalkan <strong id="receiveCancelledInsight">0</strong></span>
            </div>
        </section>

        <section class="purchase-table-section" id="receiveTableSection" aria-labelledby="receiveListTitle">
            @if ($selectedPurchaseOrder)
                <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2 m-3"
                    role="status">
                    <span>
                        Penerimaan untuk PO <strong>{{ $selectedPurchaseOrder->no_po }}</strong>,
                        termasuk draft yang belum diposting.
                    </span>
                    <a href="{{ route('penerimaan.penerimaan') }}" class="btn btn-sm btn-outline-primary">
                        Tampilkan Semua PO
                    </a>
                </div>
            @endif
            <div class="receive-list-heading">
                <div class="purchase-table-title">
                    <span class="purchase-table-title-icon"><i class="mdi mdi-truck-check-outline"></i></span>
                    <div>
                        <h2 id="receiveListTitle">Daftar Penerimaan Barang</h2>
                        <p>Temukan transaksi dan pantau status barang masuk.</p>
                    </div>
                </div>

                <div class="receive-list-actions">
                    <button type="button" class="btn btn-outline-secondary purchase-icon-btn" id="refreshReceiveTable"
                        title="Muat ulang data" aria-label="Muat ulang data">
                        <i class="mdi mdi-refresh" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary receive-filter-toggle"
                        id="receiveMobileFilterToggle" aria-controls="receiveFilterBar" aria-expanded="false"
                        title="Buka filter" aria-label="Buka filter">
                        <i class="mdi mdi-tune-variant" aria-hidden="true"></i><span>Periode</span>
                    </button>
                    <button type="button" class="btn btn-primary receive-create-button"
                        id="openPenerimaanModalToolbar" data-bs-toggle="modal" data-bs-target="#receivePoPickerModal">
                        <i class="mdi mdi-plus" aria-hidden="true"></i>
                        <span>Buat Penerimaan</span>
                    </button>
                </div>
            </div>

            <div class="receive-search-panel">
                <div class="receive-document-search">
                    <label for="searchPenerimaan">Cari transaksi</label>
                    <div class="purchase-search">
                        <i class="mdi mdi-magnify" aria-hidden="true"></i>
                        <input type="search" id="searchPenerimaan"
                            placeholder="No. penerimaan, PO, supplier, atau faktur..." autocomplete="off"
                            aria-label="Cari penerimaan barang" aria-describedby="receiveSearchHint">
                        <button type="button" class="purchase-search-clear" id="clearReceiveSearch"
                            aria-label="Hapus pencarian" title="Hapus pencarian">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <p id="receiveSearchHint">Cari berdasarkan dokumen atau nama supplier.</p>
                </div>

                <div class="receive-medicine-filter" role="group" aria-labelledby="receiveMedicineFilterLabel">
                    <div class="receive-medicine-filter-heading">
                        <label for="receiveMedicineFilter" id="receiveMedicineFilterLabel">
                            <i class="mdi mdi-pill" aria-hidden="true"></i> Cari Obat dalam Penerimaan
                        </label>
                    </div>
                    <div class="receive-medicine-filter-input">
                        <div class="receive-medicine-select">
                            <select id="receiveMedicineFilter" aria-describedby="receiveMedicineFilterHint">
                                <option value=""></option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-outline-secondary" id="clearReceiveMedicineFilter" disabled
                            aria-label="Hapus filter obat" title="Hapus filter obat">
                            <i class="mdi mdi-close" aria-hidden="true"></i><span>Reset obat</span>
                        </button>
                    </div>
                    <p id="receiveMedicineFilterHint" role="status" aria-live="polite">
                        Pilih obat untuk menelusuri penerimaan dan batch dari semua tanggal.
                    </p>
                </div>
            </div>

            <div class="receive-filter-panel">
                <div class="receive-status-row">
                    <div class="receive-status-tabs" role="group" aria-label="Filter status penerimaan">
                        <button type="button" class="purchase-filter-chip is-active" data-status="" aria-pressed="true">
                            Semua <span class="purchase-filter-count" id="receiveAllFilterCount">0</span>
                        </button>
                        <button type="button" class="purchase-filter-chip" data-status="draft" aria-pressed="false">
                            <i class="receive-status-dot is-draft" aria-hidden="true"></i>
                            Draft <span class="purchase-filter-count" id="receiveDraftFilterCount">0</span>
                        </button>
                        <button type="button" class="purchase-filter-chip" data-status="posted" aria-pressed="false">
                            <i class="receive-status-dot is-posted" aria-hidden="true"></i>
                            Posted <span class="purchase-filter-count" id="receivePostedFilterCount">0</span>
                        </button>
                        <button type="button" class="purchase-filter-chip" data-status="cancelled" aria-pressed="false">
                            Batal <span class="purchase-filter-count" id="receiveCancelledFilterCount">0</span>
                        </button>
                    </div>
                    <span class="receive-period-caption">
                        <i class="mdi mdi-calendar-blank-outline" aria-hidden="true"></i>
                        <span id="receivePeriodLabel">{{ $selectedPurchaseOrder ? 'Semua tanggal' : 'Bulan ini' }}</span>
                    </span>
                </div>
                <div class="receive-list-filters" id="receiveFilterBar">
                    <div class="receive-date-field">
                        <label for="receiveDateRange">
                            <i class="mdi mdi-calendar-range-outline"></i>
                            Tanggal penerimaan
                        </label>
                        <div class="purchase-date-input">
                            <i class="mdi mdi-calendar-month-outline"></i>
                            <input type="text" id="receiveDateRange" placeholder="Pilih rentang tanggal"
                                autocomplete="off" aria-label="Pilih rentang tanggal penerimaan">
                            <button type="button" id="clearReceiveDateRange" aria-label="Hapus filter tanggal"
                                title="Hapus filter tanggal">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                    </div>
                    <div class="receive-preset-field">
                        <label for="receiveDatePreset">Periode cepat</label>
                        <select id="receiveDatePreset" class="form-select form-select-sm"
                            aria-label="Pilih periode cepat">
                            <option value="">Periode cepat</option>
                            <option value="today">Hari Ini</option>
                            <option value="7days">7 Hari Terakhir</option>
                            <option value="30days">30 Hari Terakhir</option>
                            <option value="this_month">Bulan Ini</option>
                        </select>
                    </div>

                    <div class="receive-page-size">
                        <label for="receivePageLength">Baris per halaman</label>
                        <select id="receivePageLength" class="form-select form-select-sm"
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
                <div id="receiveMedicineResults" class="receive-medicine-results" hidden aria-busy="false">
                    <div class="receive-medicine-results-heading">
                        <h6>Hasil Pencarian Obat</h6>
                        <p id="receiveMedicineResultCount" role="status" aria-live="polite"></p>
                    </div>
                    <div id="receiveMedicineResultList" class="receive-medicine-result-list"></div>
                </div>
                <table id="tablePenerimaan" class="table purchase-table align-middle">
                    <caption class="visually-hidden">Daftar transaksi penerimaan barang beserta status, supplier, faktur, dan totalnya.</caption>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Status</th>
                            <th>Nomor Penerimaan</th>
                            <th>Nomor PO</th>
                            <th>Supplier</th>
                            <th>Faktur</th>
                            <th>Surat Jalan</th>
                            <th>Tanggal</th>
                            <th>Item / Qty</th>
                            <th>Total Faktur</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="purchase-table-hint">
                <i class="mdi mdi-information-outline" aria-hidden="true"></i>
                <span>Draft belum menambah stok. Posting setelah faktur, batch, kedaluwarsa, dan qty sudah sesuai.</span>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.jsMain")
@endpush
