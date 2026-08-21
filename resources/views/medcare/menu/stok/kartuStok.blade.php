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
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Stok</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kartu Stok</li>
            </ol>
        </nav>

        <section class="stock-toolbar">
            <div class="stock-toolbar-title">
                <span class="stock-toolbar-title-icon"><i class="mdi mdi-card-bulleted-outline"></i></span>
                <div>
                    <h4>Kartu Stok</h4>
                    <p>Riwayat mutasi masuk, keluar, expired, penyesuaian, dan saldo per batch.</p>
                </div>
            </div>
            <div class="stock-toolbar-actions">
                <span class="stock-live-chip">
                    <i class="mdi mdi-sync-circle"></i>
                    <span id="kartuLastSync">Memuat data...</span>
                </span>
                <a href="{{ route("stok.stok") }}" class="btn btn-outline-primary">
                    <i class="mdi mdi-warehouse"></i>
                    Stok Barang
                </a>
            </div>
        </section>

        <div class="stock-stat-grid">
            <article class="stock-stat">
                <span class="stock-stat-icon is-blue"><i class="mdi mdi-swap-horizontal"></i></span>
                <div class="stock-stat-copy">
                    <strong id="kartuMutasiCount">0</strong>
                    <span>Jumlah mutasi</span>
                </div>
            </article>
            <article class="stock-stat">
                <span class="stock-stat-icon is-green"><i class="mdi mdi-arrow-down-bold-circle-outline"></i></span>
                <div class="stock-stat-copy">
                    <strong id="kartuTotalMasuk">0</strong>
                    <span>Total masuk</span>
                </div>
            </article>
            <article class="stock-stat">
                <span class="stock-stat-icon is-amber"><i class="mdi mdi-arrow-up-bold-circle-outline"></i></span>
                <div class="stock-stat-copy">
                    <strong id="kartuTotalKeluar">0</strong>
                    <span>Total keluar</span>
                </div>
            </article>
            <article class="stock-stat">
                <span class="stock-stat-icon is-red"><i class="mdi mdi-calendar-alert"></i></span>
                <div class="stock-stat-copy">
                    <strong id="kartuTotalExpired">0</strong>
                    <span>Total expired</span>
                </div>
            </article>
        </div>

        <section class="stock-insight-strip stock-ledger-strip" aria-label="Ringkasan kartu stok">
            <div class="stock-insight-main">
                <span class="stock-insight-icon is-indigo"><i class="mdi mdi-chart-timeline-variant"></i></span>
                <div class="stock-insight-copy">
                    <small>Arus mutasi</small>
                    <strong id="kartuFlowText">Menunggu data mutasi</strong>
                    <div class="stock-flow-track" aria-hidden="true">
                        <span class="is-in" id="kartuFlowIn" style="width:0%"></span>
                        <span class="is-out" id="kartuFlowOut" style="width:0%"></span>
                    </div>
                </div>
                <span class="stock-health-badge" id="kartuNetMutasi">0 net</span>
            </div>
            <div class="stock-insight-metrics">
                <div class="stock-insight-metric">
                    <small>Masuk</small>
                    <strong id="kartuMasukInsight">0</strong>
                </div>
                <div class="stock-insight-metric">
                    <small>Keluar</small>
                    <strong id="kartuKeluarInsight">0</strong>
                </div>
                <div class="stock-insight-metric">
                    <small>Expired</small>
                    <strong id="kartuExpiredInsight">0</strong>
                </div>
            </div>
            <div class="stock-filter-snapshot">
                <small>Filter aktif</small>
                <strong id="kartuFilterSummary">Semua mutasi</strong>
                <span id="kartuDateSummary">Semua tanggal</span>
            </div>
        </section>

        <section class="stock-filter-bar">
            <div class="stock-chip-group stock-ledger-chip-group" aria-label="Filter jenis mutasi">
                <button type="button" class="stock-chip is-active" data-kartu-jenis="">Semua</button>
                <button type="button" class="stock-chip" data-kartu-jenis="masuk">Masuk</button>
                <button type="button" class="stock-chip" data-kartu-jenis="keluar">Keluar</button>
                <button type="button" class="stock-chip" data-kartu-jenis="expired">Expired</button>
                <button type="button" class="stock-chip" data-kartu-jenis="penyesuaian_masuk">Adj. Masuk</button>
                <button type="button" class="stock-chip" data-kartu-jenis="penyesuaian_keluar">Adj. Keluar</button>
            </div>
            <div class="stock-filter-controls">
                <select id="kartuObatFilter" class="form-select" style="min-width:260px"></select>
                <select id="kartuBatchFilter" class="form-select" style="min-width:260px" disabled>
                    <option value="">Semua batch</option>
                </select>
                <select id="kartuJenisFilter" class="form-select" style="width:auto">
                    <option value="">Semua mutasi</option>
                    <option value="masuk">Obat Masuk</option>
                    <option value="keluar">Obat Keluar</option>
                    <option value="expired">Obat Expired</option>
                    <option value="penyesuaian_masuk">Penyesuaian Masuk</option>
                    <option value="penyesuaian_keluar">Penyesuaian Keluar</option>
                    <option value="pembatalan_penerimaan">Pembatalan Penerimaan</option>
                    <option value="penjualan">Penjualan POS</option>
                    <option value="pembatalan_penjualan">Pembatalan Penjualan</option>
                    <option value="retur_pembelian">Retur Pembelian</option>
                    <option value="pembatalan_retur_pembelian">Pembatalan Retur Pembelian</option>
                    <option value="retur_penjualan">Retur Penjualan</option>
                    <option value="pembatalan_retur_penjualan">Pembatalan Retur Penjualan</option>
                    <option value="saldo_awal">Saldo Awal</option>
                </select>
            </div>
            <div class="stock-filter-controls">
                <div class="stock-search">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="kartuSearch" placeholder="Cari mutasi, obat, batch, referensi..." autocomplete="off">
                    <button type="button" id="clearKartuSearch" aria-label="Hapus pencarian">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>
                <input type="text" id="kartuDateRange" class="form-control form-control-sm" style="width:210px"
                    placeholder="Filter tanggal">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearKartuFilter">
                    <i class="mdi mdi-filter-remove-outline"></i>
                    Reset
                </button>
                <button type="button" class="btn btn-outline-secondary stock-refresh" id="refreshKartuTable"
                    title="Muat ulang kartu stok" aria-label="Muat ulang kartu stok">
                    <i class="mdi mdi-refresh"></i>
                </button>
            </div>
        </section>

        <section class="stock-table-section">
            <div class="stock-section-header">
                <div class="stock-section-title">
                    <span class="stock-section-icon"><i class="mdi mdi-history"></i></span>
                    <div>
                        <h5>Riwayat Mutasi Stok</h5>
                        <p>Saldo batch dan saldo total tercatat setelah setiap mutasi.</p>
                    </div>
                </div>
                <div class="stock-section-tools">
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-database-check-outline"></i>
                        Saldo tercatat: <strong id="kartuSaldoTercatat">0</strong>
                    </span>
                    <span class="stock-batch-filter-note">
                        <i class="mdi mdi-eye-check-outline"></i>
                        Ditampilkan: <strong id="kartuVisibleInfo">0 data</strong>
                    </span>
                </div>
            </div>
            <div class="table-responsive stock-table-wrap">
                <table id="tableKartuStok" class="table stock-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Obat</th>
                            <th>Batch</th>
                            <th>Jenis</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Saldo Batch</th>
                            <th>Saldo Total</th>
                            <th>Harga Beli</th>
                            <th>Referensi</th>
                            <th>User</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.stok.jsKartu")
@endpush
