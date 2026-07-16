@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.partials.style")
@endpush

@section("content")
    <div class="purchase-page">
        @include("medcare.menu.pembelianPenerimaan.penerimaan.modalMain")
        @include("medcare.menu.pembelianPenerimaan.penerimaan.modalDetail")

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Pembelian dan Penerimaan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Penerimaan Barang</li>
            </ol>
        </nav>

        <section class="purchase-hero">
            <div class="purchase-hero-copy">
                <span class="purchase-kicker">
                    <i class="mdi mdi-package-variant-closed-check"></i>
                    Receiving Workspace
                </span>
                <h2>Terima barang dari PO yang sudah disetujui dengan kontrol batch dan sisa qty.</h2>
                <p>
                    Pilih PO approved, isi faktur, surat jalan, qty diterima, batch, expired date,
                    harga beli, diskon, dan PPN dalam satu form yang mudah diaudit.
                </p>
                <div class="purchase-hero-actions">
                    <button type="button" class="btn btn-light" id="openPenerimaanModal" data-bs-toggle="modal"
                        data-bs-target="#penerimaanModal">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        Buat Penerimaan
                    </button>
                    <button type="button" class="btn btn-outline-light" id="scrollReceiveTable">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        Lihat Daftar
                    </button>
                </div>
            </div>

            <div class="purchase-flow" aria-label="Alur penerimaan barang">
                <div class="purchase-flow-item">
                    <span class="purchase-flow-icon"><i class="mdi mdi-file-check-outline"></i></span>
                    <div>
                        <strong>1. Pilih PO Approved</strong>
                        <small>Hanya PO yang sudah disetujui dan masih bersisa.</small>
                    </div>
                    <i class="mdi mdi-chevron-right purchase-flow-arrow"></i>
                </div>
                <div class="purchase-flow-item">
                    <span class="purchase-flow-icon"><i class="mdi mdi-barcode-scan"></i></span>
                    <div>
                        <strong>2. Catat Batch</strong>
                        <small>Isi qty, batch, expired date, harga, diskon, dan PPN.</small>
                    </div>
                    <i class="mdi mdi-chevron-right purchase-flow-arrow"></i>
                </div>
                <div class="purchase-flow-item">
                    <span class="purchase-flow-icon"><i class="mdi mdi-warehouse"></i></span>
                    <div>
                        <strong>3. Posting Stok</strong>
                        <small>Draft bisa dicek dulu, posted akan memperbarui stok.</small>
                    </div>
                    <i class="mdi mdi-check-circle-outline purchase-flow-arrow"></i>
                </div>
            </div>
        </section>

        <div class="purchase-stats-grid" aria-label="Ringkasan penerimaan barang">
            <article class="purchase-stat is-total">
                <span class="purchase-stat-icon"><i class="mdi mdi-clipboard-list-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="receiveTotalCount">0</strong>
                    <span>Total Penerimaan</span>
                    <small>Seluruh transaksi penerimaan.</small>
                </div>
            </article>
            <article class="purchase-stat is-draft">
                <span class="purchase-stat-icon"><i class="mdi mdi-file-clock-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="receiveDraftCount">0</strong>
                    <span>Draft</span>
                    <small>Belum menambah stok.</small>
                </div>
            </article>
            <article class="purchase-stat is-approved">
                <span class="purchase-stat-icon"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="receivePostedCount">0</strong>
                    <span>Posted</span>
                    <small>Sudah masuk stok.</small>
                </div>
            </article>
            <article class="purchase-stat is-value">
                <span class="purchase-stat-icon"><i class="mdi mdi-cash-multiple"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="receiveTotalValue">Rp 0</strong>
                    <span>Total Nilai</span>
                    <small>Akumulasi grand total.</small>
                </div>
            </article>
        </div>

        <section class="receive-insight-strip" aria-label="Insight penerimaan barang">
            <article class="receive-insight-card">
                <span class="receive-insight-icon is-primary"><i class="mdi mdi-package-variant"></i></span>
                <div>
                    <small>Qty diterima periode ini</small>
                    <strong id="receiveTotalQty">0</strong>
                    <span>Akumulasi qty dari filter tanggal aktif.</span>
                </div>
            </article>
            <article class="receive-insight-card">
                <span class="receive-insight-icon is-warning"><i class="mdi mdi-progress-clock"></i></span>
                <div>
                    <small>Draft perlu diposting</small>
                    <strong id="receiveDraftInsight">0</strong>
                    <span>Draft belum menambah saldo batch dan kartu stok.</span>
                </div>
            </article>
            <article class="receive-insight-card">
                <span class="receive-insight-icon is-danger"><i class="mdi mdi-cancel"></i></span>
                <div>
                    <small>Dibatalkan</small>
                    <strong id="receiveCancelledInsight">0</strong>
                    <span>Transaksi yang sudah tidak aktif.</span>
                </div>
            </article>
        </section>

        <section class="purchase-table-section" id="receiveTableSection">
            <div class="purchase-table-toolbar">
                <div class="purchase-table-title">
                    <span class="purchase-table-title-icon"><i class="mdi mdi-truck-check-outline"></i></span>
                    <div>
                        <h5>Daftar Penerimaan Barang</h5>
                        <p>Pantau draft, transaksi posted, dan penerimaan yang dibatalkan.</p>
                    </div>
                </div>

                <div class="purchase-table-tools">
                    <div class="purchase-search">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="searchPenerimaan"
                            placeholder="Cari nomor penerimaan, PO, supplier, faktur..." autocomplete="off"
                            aria-label="Cari penerimaan barang">
                        <button type="button" class="purchase-search-clear" id="clearReceiveSearch"
                            aria-label="Hapus pencarian" title="Hapus pencarian">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary purchase-icon-btn" id="refreshReceiveTable"
                        title="Muat ulang data" aria-label="Muat ulang data">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1"
                        id="openPenerimaanModalToolbar" data-bs-toggle="modal" data-bs-target="#penerimaanModal">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        <span>Tambah Penerimaan</span>
                    </button>
                </div>
            </div>

            <div class="purchase-filter-bar">
                <div class="purchase-filter-group" aria-label="Filter status penerimaan">
                    <span class="purchase-filter-label">
                        <i class="mdi mdi-filter-variant"></i>
                        Status
                    </span>
                    <button type="button" class="purchase-filter-chip is-active" data-status="" aria-pressed="true">
                        Semua <span class="purchase-filter-count" id="receiveAllFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="draft" aria-pressed="false">
                        Draft <span class="purchase-filter-count" id="receiveDraftFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="posted" aria-pressed="false">
                        Posted <span class="purchase-filter-count" id="receivePostedFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="cancelled" aria-pressed="false">
                        Cancelled <span class="purchase-filter-count" id="receiveCancelledFilterCount">0</span>
                    </button>
                </div>
                <div class="purchase-filter-controls">
                    <div class="purchase-date-filter">
                        <label for="receiveDateRange">
                            <i class="mdi mdi-calendar-range-outline"></i>
                            Periode Terima
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
                        <select id="receiveDatePreset" class="form-select form-select-sm"
                            aria-label="Pilih periode cepat">
                            <option value="">Periode cepat</option>
                            <option value="today">Hari Ini</option>
                            <option value="7days">7 Hari Terakhir</option>
                            <option value="30days">30 Hari Terakhir</option>
                            <option value="this_month">Bulan Ini</option>
                        </select>
                    </div>

                    <div class="purchase-page-size">
                        <label for="receivePageLength">Tampilkan data</label>
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
                <table id="tablePenerimaan" class="table purchase-table align-middle">
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
                            <th>Grand Total</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="purchase-table-hint">
                <i class="mdi mdi-information-outline"></i>
                Draft belum menambah stok. Gunakan posting setelah faktur, batch, expired date, dan qty sudah benar.    
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.jsMain")
@endpush
