@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("medcare.menu.analisisPersediaan.partials.style")
@endpush

@section("content")
    <div class="inventory-analysis-page" id="inventoryAnalysisApp"
        data-analysis="{{ $analysisType }}"
        data-url="{{ route('analisisPersediaan.data', ['analysis' => $analysisType]) }}"
        @if ($analysisType === 'saran-pembelian')
            data-create-po-url="{{ route('analisisPersediaan.purchaseOrders.store') }}"
        @endif>
        <nav class="page-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Analisis Persediaan</li>
                <li class="breadcrumb-item active" aria-current="page">{{ $analysisDefinition['title'] }}</li>
            </ol>
        </nav>

        <section class="ia-hero ia-tone-{{ $analysisDefinition['tone'] }}">
            <div class="ia-hero-copy">
                <span class="ia-hero-icon"><i class="mdi {{ $analysisDefinition['icon'] }}"></i></span>
                <div>
                    <span class="ia-eyebrow"><i class="mdi mdi-chart-box-outline"></i> Pusat Analisis Persediaan</span>
                    <h1>{{ $analysisDefinition['title'] }}</h1>
                    <p>{{ $analysisDefinition['description'] }}</p>
                    <div class="ia-hero-live">
                        <span><i></i> Data operasional</span>
                        <b id="iaHeroPeriod">Menyiapkan periode analisis...</b>
                    </div>
                </div>
            </div>
            <div class="ia-hero-status">
                <span class="ia-hero-status-label">Konteks analisis aktif</span>
                <div class="ia-hero-meta">
                    <i class="mdi mdi-map-marker-outline"></i>
                    <span><small>Cakupan cabang</small><b id="iaBranchLabel">Memuat cabang...</b></span>
                </div>
                <div class="ia-hero-meta">
                    <i class="mdi mdi-clock-check-outline"></i>
                    <span><small>Status data</small><b id="iaGeneratedAt">Menyiapkan analisis...</b></span>
                </div>
            </div>
        </section>

        <nav class="ia-analysis-nav" aria-label="Jenis analisis persediaan">
            @foreach ($analysisTypes as $analysisSlug => $analysisMenu)
                <a href="{{ route('analisisPersediaan.index', ['analysis' => $analysisSlug]) }}"
                    class="ia-analysis-link {{ $analysisSlug === $analysisType ? 'is-active' : '' }}"
                    @if ($analysisSlug === $analysisType) aria-current="page" @endif>
                    <span class="ia-analysis-icon"><i class="mdi {{ $analysisMenu['icon'] }}"></i></span>
                    <span><b>{{ $analysisMenu['short_title'] }}</b><small>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</small></span>
                </a>
            @endforeach
            <a href="{{ route('laporan.persediaan.index', ['report' => 'stok-minimum']) }}"
                class="ia-analysis-link">
                <span class="ia-analysis-icon"><i class="mdi mdi-file-table-outline"></i></span>
                <span><b>Laporan Stok Minimum</b><small>DETAIL</small></span>
            </a>
        </nav>

        <section class="ia-filter-card">
            <div class="ia-section-heading">
                <div>
                    <span class="ia-section-icon"><i class="mdi mdi-tune-variant"></i></span>
                    <div><h2>Parameter analisis</h2><p>Sesuaikan cabang, periode aktivitas, dan batas klasifikasi.</p></div>
                </div>
                <div class="ia-filter-heading-actions">
                    <button type="button" class="btn btn-light btn-sm" id="iaResetFilter">
                        <i class="mdi mdi-filter-remove-outline"></i> Reset
                    </button>
                    <button type="button" class="btn btn-light btn-sm ia-filter-toggle" id="iaFilterToggle"
                        aria-controls="iaFilterBody" aria-expanded="true">
                        <i class="mdi mdi-chevron-up"></i><span>Sembunyikan</span>
                    </button>
                </div>
            </div>
            <div class="ia-filter-body" id="iaFilterBody">
                <div class="ia-period-presets" aria-label="Pilihan periode cepat">
                    <span>Periode cepat</span>
                    <div>
                        <button type="button" data-range="7">7 hari</button>
                        <button type="button" data-range="30">30 hari</button>
                        <button type="button" data-range="90" class="is-active">90 hari</button>
                        <button type="button" data-range="mtd">Bulan ini</button>
                    </div>
                </div>
                <form id="iaFilterForm" class="ia-filter-grid">
                    <label class="ia-field">
                        <span>Cabang</span>
                        <select class="form-select" id="iaBranch" name="branch_id">
                            <option value="">Semua cabang yang dapat diakses</option>
                        </select>
                    </label>
                    <label class="ia-field">
                        <span>Tanggal mulai</span>
                        <input type="date" class="form-control" id="iaDateStart" name="date_start"
                            value="{{ today()->subDays(89)->toDateString() }}">
                    </label>
                    <label class="ia-field">
                        <span>Tanggal akhir</span>
                        <input type="date" class="form-control" id="iaDateEnd" name="date_end"
                            value="{{ today()->toDateString() }}">
                    </label>
                    @if (in_array($analysisType, ['slow-moving', 'dead-stock'], true))
                        <label class="ia-field">
                            <span>Batas slow moving</span>
                            <select class="form-select" id="iaSlowDays" name="slow_days">
                                <option value="14">14 hari</option>
                                <option value="30" selected>30 hari</option>
                                <option value="45">45 hari</option>
                                <option value="60">60 hari</option>
                            </select>
                        </label>
                        <label class="ia-field">
                            <span>Batas dead stock</span>
                            <select class="form-select" id="iaDeadDays" name="dead_days">
                                <option value="60">60 hari</option>
                                <option value="90" selected>90 hari</option>
                                <option value="120">120 hari</option>
                                <option value="180">180 hari</option>
                                <option value="365">365 hari</option>
                            </select>
                        </label>
                    @endif
                    @if ($analysisType === 'saran-pembelian')
                        <label class="ia-field">
                            <span>Target ketersediaan</span>
                            <select class="form-select" id="iaCoverDays" name="cover_days">
                                <option value="7">7 hari</option>
                                <option value="14">14 hari</option>
                                <option value="30" selected>30 hari</option>
                                <option value="45">45 hari</option>
                                <option value="60">60 hari</option>
                                <option value="90">90 hari</option>
                            </select>
                        </label>
                        <label class="ia-field">
                            <span>Lead time pemasok</span>
                            <select class="form-select" id="iaLeadDays" name="lead_days">
                                <option value="3">3 hari</option>
                                <option value="7" selected>7 hari</option>
                                <option value="14">14 hari</option>
                                <option value="21">21 hari</option>
                                <option value="30">30 hari</option>
                            </select>
                        </label>
                    @endif
                    <label class="ia-field ia-search-field">
                        <span>Cari produk</span>
                        <span class="ia-input-icon"><i class="mdi mdi-magnify"></i><input type="search" class="form-control"
                            id="iaSearch" name="search" placeholder="{{ $analysisType === 'saran-pembelian' ? 'Kode atau nama obat...' : 'Kode, nama obat, atau distributor...' }}">
                            <button type="button" id="iaClearSearch" aria-label="Hapus pencarian" hidden><i class="mdi mdi-close"></i></button>
                        </span>
                    </label>
                    <button type="submit" class="btn btn-primary ia-apply-filter" id="iaApplyFilter">
                        <i class="mdi mdi-chart-box-plus-outline"></i> Terapkan Analisis
                    </button>
                </form>
            </div>
            <div class="ia-active-filters">
                <span><i class="mdi mdi-filter-check-outline"></i> Parameter aktif</span>
                <div id="iaActiveFilterSummary" aria-live="polite"><em>Menyiapkan parameter...</em></div>
            </div>
        </section>

        <div class="ia-content-heading">
            <div><span>Ringkasan eksekutif</span><h2>Kondisi utama dalam satu pandangan</h2></div>
            <p id="iaSummaryContext">Membaca data periode aktif...</p>
        </div>
        <section class="ia-summary-grid" id="iaSummaryGrid" aria-label="Ringkasan analisis" aria-live="polite">
            @for ($i = 0; $i < 4; $i++)
                <article class="ia-summary-card is-loading"><span></span><div><small>Memuat data</small><strong>&nbsp;</strong></div></article>
            @endfor
        </section>

        <div class="ia-insight-grid">
            <section class="ia-panel ia-distribution-panel">
                <div class="ia-panel-heading">
                    <div><span class="ia-section-icon"><i class="mdi mdi-chart-bar-stacked"></i></span><div><h2 id="iaDistributionTitle">Sorotan data</h2><p id="iaDistributionSubtitle">Distribusi hasil analisis aktif.</p></div></div>
                </div>
                <div class="ia-distribution-layout">
                    <div class="ia-donut-shell">
                        <div class="ia-donut" id="iaDistributionDonut" role="img" aria-label="Total distribusi analisis">
                            <span><small>Total</small><strong id="iaDistributionTotal">—</strong></span>
                        </div>
                    </div>
                    <div class="ia-distribution" id="iaDistribution">
                        <div class="ia-loading-state"><i class="mdi mdi-loading mdi-spin"></i> Mengolah distribusi...</div>
                    </div>
                </div>
            </section>
            <aside class="ia-insight-stack">
                <article class="ia-priority-card">
                    <div class="ia-priority-top"><span><i class="mdi mdi-auto-fix"></i></span><small>Insight prioritas</small></div>
                    <h3 id="iaPriorityTitle">Menyiapkan rekomendasi...</h3>
                    <p id="iaPriorityCopy">Insight akan muncul setelah data selesai dianalisis.</p>
                    <button type="button" id="iaViewPriority" disabled>Lihat detail prioritas <i class="mdi mdi-arrow-down"></i></button>
                </article>
                <article class="ia-method-card">
                    <span class="ia-method-icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
                    <div><small>Cara membaca</small><h3 id="iaMethodTitle">{{ $analysisDefinition['title'] }}</h3><p id="iaMethodCopy"></p></div>
                </article>
            </aside>
        </div>

        <section class="ia-panel ia-table-panel">
            <div class="ia-table-toolbar">
                <div class="ia-panel-heading">
                    <div><span class="ia-section-icon"><i class="mdi mdi-table-large"></i></span><div><h2>Detail {{ $analysisDefinition['title'] }}</h2><p id="iaResultInfo" aria-live="polite">Menyiapkan data...</p></div></div>
                </div>
                <div class="ia-table-actions">
                    @if ($analysisType === 'saran-pembelian')
                        <button type="button" class="btn btn-success btn-sm" id="iaCreatePo" disabled>
                            <i class="mdi mdi-cart-check"></i> Buat PO <span id="iaCreatePoCount">(0)</span>
                        </button>
                    @endif
                    <button type="button" class="btn btn-light btn-sm ia-clear-sort" id="iaClearSort" hidden>
                        <i class="mdi mdi-sort-variant-remove"></i> Reset urutan
                    </button>
                    <label>Baris
                        <select id="iaPageSize" class="form-select form-select-sm">
                            <option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option>
                        </select>
                    </label>
                    <button type="button" class="btn btn-outline-success btn-sm" id="iaExportCsv" disabled>
                        <i class="mdi mdi-file-delimited-outline"></i> Ekspor CSV
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="iaRefresh" title="Muat ulang data" aria-label="Muat ulang data">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive ia-table-wrap" tabindex="0" aria-label="Tabel detail hasil analisis">
                <table class="table ia-table align-middle" id="iaTable">
                    <thead><tr id="iaTableHead"></tr></thead>
                    <tbody id="iaTableBody"><tr><td><div class="ia-loading-state"><i class="mdi mdi-loading mdi-spin"></i> Mengambil data persediaan...</div></td></tr></tbody>
                </table>
            </div>
            <div class="ia-pagination">
                <span id="iaPaginationInfo">0 data</span>
                <div><button type="button" id="iaPreviousPage" class="btn btn-light btn-sm" aria-label="Halaman sebelumnya" disabled><i class="mdi mdi-chevron-left"></i></button><span id="iaPageLabel">Halaman 1</span><button type="button" id="iaNextPage" class="btn btn-light btn-sm" aria-label="Halaman berikutnya" disabled><i class="mdi mdi-chevron-right"></i></button></div>
            </div>
        </section>
        @if ($analysisType === 'saran-pembelian')
            <div class="modal fade ia-po-modal" id="iaCreatePoModal" tabindex="-1"
                aria-labelledby="iaCreatePoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                    <div class="modal-content">
                        <form id="iaCreatePoForm" novalidate>
                            <div class="modal-header">
                                <div class="ia-po-modal-title">
                                    <span><i class="mdi mdi-cart-check"></i></span>
                                    <div>
                                        <small>Saran Pembelian</small>
                                        <h5 class="modal-title" id="iaCreatePoModalLabel">Buat Purchase Order</h5>
                                        <p>Pilih distributor, sesuaikan harga estimasi, dan isi diskon setiap produk sebelum PO dibuat.</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <div class="ia-po-modal-overview">
                                    <div class="ia-po-selection-card">
                                        <span><i class="mdi mdi-pill-multiple"></i></span>
                                        <div><small>Produk terpilih</small><strong id="iaPoSelectedCount">0 produk</strong></div>
                                    </div>
                                    <div class="ia-po-selection-card">
                                        <span><i class="mdi mdi-map-marker-outline"></i></span>
                                        <div><small>Cabang tujuan</small><strong id="iaPoBranchName">-</strong></div>
                                    </div>
                                    <div class="ia-po-selection-card">
                                        <span><i class="mdi mdi-file-document-multiple-outline"></i></span>
                                        <div><small>PO akan dibuat</small><strong id="iaPoOrderCount">Pilih distributor</strong></div>
                                    </div>
                                </div>

                                @if ($distributors->isEmpty())
                                    <div class="alert alert-warning mb-3">
                                        <i class="mdi mdi-alert-outline"></i>
                                        Belum ada distributor aktif. Aktifkan distributor terlebih dahulu sebelum membuat PO.
                                    </div>
                                @endif

                                <div class="ia-po-discount-note">
                                    <i class="mdi mdi-information-outline"></i>
                                    Harga estimasi dapat diubah sesuai penawaran distributor. Produk dengan distributor yang sama digabung dalam satu PO; diskon dihitung bertingkat per produk.
                                </div>
                                <div class="table-responsive ia-po-item-wrap">
                                    <table class="table align-middle mb-0 ia-po-item-table">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Distributor</th>
                                                <th>Qty beli</th>
                                                <th>Harga estimasi</th>
                                                <th>Diskon 1 (%)</th>
                                                <th>Diskon 2 (%)</th>
                                                <th>Diskon 3 (%)</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="iaPoItemsBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer ia-po-modal-footer">
                                <div class="ia-po-total-summary">
                                    <span>Estimasi bruto <b id="iaPoGrossTotal">Rp 0</b></span>
                                    <span>Total setelah diskon <strong id="iaPoNetTotal">Rp 0</strong></span>
                                </div>
                                <div class="ia-po-footer-actions">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success" id="iaSubmitPo"
                                        @disabled($distributors->isEmpty())>
                                        <i class="mdi mdi-cart-check"></i> Buat PO
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
        <div class="ia-toast" id="iaToast" role="status" aria-live="polite" hidden>
            <span><i class="mdi mdi-check-circle-outline"></i></span><p></p>
        </div>
        <div class="ia-load-progress" id="iaLoadProgress" aria-hidden="true"><span></span></div>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.analisisPersediaan.partials.script")
@endpush
