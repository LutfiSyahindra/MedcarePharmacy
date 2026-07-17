@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.stok.partials.style")
@endpush

@section("content")
    <div class="stock-page">
        @include("medcare.menu.stok.modalMutasi")

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Stok</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stok Barang</li>
            </ol>
        </nav>

        <section class="stock-toolbar">
            <div class="stock-toolbar-title">
                <span class="stock-toolbar-title-icon"><i class="mdi mdi-warehouse"></i></span>
                <div>
                    <h4>Stok Barang</h4>
                    <p>Kontrol stok total, batch, expired date, harga beli terakhir, dan peringatan stok.</p>
                </div>
            </div>
            <div class="stock-toolbar-actions">
                <span class="stock-live-chip">
                    <i class="mdi mdi-sync-circle"></i>
                    <span id="stockLastSync">Memuat data...</span>
                </span>
                <a href="{{ route("kartuStok.kartuStok") }}" class="btn btn-outline-primary">
                    <i class="mdi mdi-card-bulleted-outline"></i>
                    Kartu Stok
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mutasiStokModal">
                    <i class="mdi mdi-plus-circle-outline"></i>
                    Catat Mutasi
                </button>
            </div>
        </section>

        <div class="stock-stat-grid">
            <article class="stock-stat">
                <span class="stock-stat-icon is-blue"><i class="mdi mdi-pill"></i></span>
                <div class="stock-stat-copy">
                    <strong id="stockTotalItem">0</strong>
                    <span>Total item obat</span>
                </div>
            </article>
            <article class="stock-stat">
                <span class="stock-stat-icon is-green"><i class="mdi mdi-package-variant"></i></span>
                <div class="stock-stat-copy">
                    <strong id="stockTotalQty">0</strong>
                    <span>Total stok tercatat</span>
                </div>
            </article>
            <article class="stock-stat">
                <span class="stock-stat-icon is-amber"><i class="mdi mdi-alert-circle-outline"></i></span>
                <div class="stock-stat-copy">
                    <strong id="stockLowCount">0</strong>
                    <span>Stok minimum</span>
                </div>
            </article>
            <article class="stock-stat">
                <span class="stock-stat-icon is-red"><i class="mdi mdi-calendar-alert"></i></span>
                <div class="stock-stat-copy">
                    <strong id="stockExpiredCount">0</strong>
                    <span>Expired dan akan expired</span>
                </div>
            </article>
        </div>

        <section class="stock-insight-strip" aria-label="Ringkasan kesehatan stok">
            <div class="stock-insight-main">
                <span class="stock-insight-icon"><i class="mdi mdi-shield-check-outline"></i></span>
                <div class="stock-insight-copy">
                    <small>Kesehatan stok</small>
                    <strong id="stockHealthText">Menunggu data stok</strong>
                    <div class="stock-health-track" aria-hidden="true">
                        <span id="stockHealthBar" style="width:0%"></span>
                    </div>
                </div>
                <span class="stock-health-badge" id="stockHealthBadge">-</span>
            </div>
            <div class="stock-insight-metrics">
                <div class="stock-insight-metric">
                    <small>Kosong</small>
                    <strong id="stockEmptyInsight">0</strong>
                </div>
                <div class="stock-insight-metric">
                    <small>Menipis</small>
                    <strong id="stockLowInsight">0</strong>
                </div>
                <div class="stock-insight-metric">
                    <small>ED Risk</small>
                    <strong id="stockEdInsight">0</strong>
                </div>
            </div>
            <div class="stock-filter-snapshot">
                <small>Filter aktif</small>
                <strong id="stockActiveFilterText">Semua stok</strong>
                <span id="stockActiveSearchText">Tanpa pencarian khusus</span>
            </div>
        </section>

        <section class="stock-filter-bar">
            <div class="stock-chip-group" aria-label="Filter status stok">
                <button type="button" class="stock-chip is-active" data-alert="">
                    <span>Semua</span>
                    <strong data-stock-count="all">0</strong>
                </button>
                <button type="button" class="stock-chip" data-alert="aman">
                    <span>Aman</span>
                    <strong data-stock-count="aman">0</strong>
                </button>
                <button type="button" class="stock-chip" data-alert="menipis">
                    <span>Menipis</span>
                    <strong data-stock-count="menipis">0</strong>
                </button>
                <button type="button" class="stock-chip" data-alert="kosong">
                    <span>Kosong</span>
                    <strong data-stock-count="kosong">0</strong>
                </button>
                <button type="button" class="stock-chip" data-alert="expired">
                    <span>Expired</span>
                    <strong data-stock-count="expired">0</strong>
                </button>
                <button type="button" class="stock-chip" data-alert="akan_expired">
                    <span>Akan Expired</span>
                    <strong data-stock-count="akan_expired">0</strong>
                </button>
            </div>
            <div class="stock-filter-controls">
                <div class="stock-search">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="stockSearch" placeholder="Cari kode atau nama obat..." autocomplete="off">
                    <button type="button" id="clearStockSearch" aria-label="Hapus pencarian">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>
                <select id="expiredWarningDays" class="form-select form-select-sm" style="width:auto">
                    <option value="30">Warning ED 30 hari</option>
                    <option value="60">Warning ED 60 hari</option>
                    <option value="90" selected>Warning ED 90 hari</option>
                    <option value="180">Warning ED 180 hari</option>
                </select>
                <button type="button" class="btn btn-outline-secondary stock-refresh" id="refreshStockTable"
                    title="Muat ulang stok" aria-label="Muat ulang stok">
                    <i class="mdi mdi-refresh"></i>
                </button>
            </div>
        </section>

        <section class="stock-table-section">
            <div class="stock-section-header">
                <div class="stock-section-title">
                    <span class="stock-section-icon"><i class="mdi mdi-format-list-bulleted-square"></i></span>
                    <div>
                        <h5>Stok Total Per Barang</h5>
                        <p>Angka stok diambil dari saldo batch aktif dan kartu stok.</p>
                    </div>
                </div>
                <div class="stock-section-tools">
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-eye-check-outline"></i>
                        Ditampilkan: <strong id="stockVisibleInfo">0 data</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-filter-check-outline"></i>
                        Filter: <strong id="stockFilterInfo">Semua data</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-cash-multiple"></i>
                        Nilai stok: <strong id="stockValueTotal">Rp 0</strong>
                    </span>
                </div>
            </div>
            <div class="table-responsive stock-table-wrap">
                <table id="tableStock" class="table stock-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Obat</th>
                            <th>Total Stok</th>
                            <th>Batch</th>
                            <th>ED Terdekat</th>
                            <th>Harga Beli Terakhir</th>
                            <th>Stok Minimum</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <section class="stock-table-section" id="batchStockSection">
            <div class="stock-section-header">
                <div class="stock-section-title">
                    <span class="stock-section-icon"><i class="mdi mdi-package-variant-closed"></i></span>
                    <div>
                        <h5>Stok Per Batch</h5>
                        <p>Daftar batch aktif, expired date, qty, dan nilai stok per batch.</p>
                    </div>
                </div>
                <div class="stock-section-tools">
                    <span class="stock-batch-filter-note" id="batchFilterLabel">
                        <i class="mdi mdi-filter-variant"></i>
                        Semua batch
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-package-variant-closed-check"></i>
                        Total batch: <strong id="batchTotalCount">0</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-counter"></i>
                        Qty: <strong id="batchTotalQty">0</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-calendar-alert"></i>
                        ED Risk: <strong id="batchRiskCount">0</strong>
                    </span>
                    <select id="batchExpiryFilter" class="form-select form-select-sm" style="width:auto">
                        <option value="">Semua status ED</option>
                        <option value="expired">Expired</option>
                        <option value="akan_expired">Akan Expired</option>
                        <option value="aman">Aman</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="resetBatchFilter">
                        <i class="mdi mdi-filter-remove-outline"></i>
                        Reset
                    </button>
                </div>
            </div>
            <div class="table-responsive stock-table-wrap">
                <table id="tableBatchStock" class="table stock-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Obat</th>
                            <th>No Batch</th>
                            <th>Expired Date</th>
                            <th>Qty</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Nilai Stok</th>
                            <th>Status</th>
                            <th>Mutasi Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <section class="stock-table-section" id="riwayatHargaSection">
            <div class="stock-section-header">
                <div class="stock-section-title">
                    <span class="stock-section-icon"><i class="mdi mdi-cash-clock"></i></span>
                    <div>
                        <h5>Riwayat Harga Jual</h5>
                        <p>Jejak perubahan harga jual per batch, termasuk alasan dan user pengubah.</p>
                    </div>
                </div>
                <div class="stock-section-tools">
                    <span class="stock-batch-filter-note" id="riwayatHargaFilterLabel">
                        <i class="mdi mdi-filter-variant"></i>
                        Semua riwayat harga
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-history"></i>
                        Total: <strong id="riwayatTotalCount">0</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-trending-up"></i>
                        Naik: <strong id="riwayatUpCount">0</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-trending-down"></i>
                        Turun: <strong id="riwayatDownCount">0</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-clock-outline"></i>
                        Terakhir: <strong id="riwayatLastChange">-</strong>
                    </span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="resetRiwayatHargaFilter">
                        <i class="mdi mdi-filter-remove-outline"></i>
                        Reset
                    </button>
                </div>
            </div>
            <div class="table-responsive stock-table-wrap">
                <table id="tableRiwayatHarga" class="table stock-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Obat</th>
                            <th>No Batch</th>
                            <th>Harga Lama</th>
                            <th>Harga Baru</th>
                            <th>Selisih</th>
                            <th>Alasan</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.stok.jsMain")
@endpush
