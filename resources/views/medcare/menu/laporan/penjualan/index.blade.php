@extends("template.partials.app")

@php
    $reportModule = array_merge([
        'key' => 'penjualan',
        'data_route' => 'laporan.penjualan.data',
        'index_route' => 'laporan.penjualan.index',
        'eyebrow' => 'Sales Intelligence',
        'switcher_title' => 'Jelajahi laporan penjualan',
        'nav_label' => 'Jenis laporan penjualan',
        'summary_loading' => 'Menghitung data penjualan...',
        'chart_title' => 'Tren penjualan',
        'chart_aria' => 'Grafik tren laporan penjualan',
        'chart_loading' => 'Menyusun visual penjualan...',
        'health_eyebrow' => 'Kesehatan penjualan',
        'health_title' => 'Setelah retur',
        'health_copy' => 'rasio retur terhadap omzet periode aktif.',
        'analysis_url' => route('analisisOmzet.index'),
        'basis_label' => 'Omzet laporan: completed sebelum retur',
    ], $reportModule ?? []);
    $showSupplierFilter = $showSupplierFilter ?? false;
    $defaultDateStart = $defaultDateStart ?? today()->subDays(29)->toDateString();
    $defaultDateEnd = $defaultDateEnd ?? today()->toDateString();
@endphp

@push("style")
    @include("template.AddOn.mdiicon")
    @include("medcare.menu.laporan.penjualan.partials.style")
@endpush

@section("content")
    <div class="sales-report-page" id="salesReportApp"
        data-report="{{ $reportType }}"
        data-module="{{ $reportModule['key'] }}"
        data-default-start="{{ $defaultDateStart }}"
        data-default-end="{{ $defaultDateEnd }}"
        data-chart-loading="{{ $reportModule['chart_loading'] }}"
        data-url="{{ route($reportModule['data_route'], ['report' => $reportType]) }}">
        <nav class="page-breadcrumb sr-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i> Dashboard</a></li>
                <li class="breadcrumb-item">Laporan</li>
                <li class="breadcrumb-item active" aria-current="page">{{ $reportDefinition['title'] }}</li>
            </ol>
        </nav>

        <section class="sr-hero sr-tone-{{ $reportDefinition['tone'] }}">
            <div class="sr-hero-glow sr-hero-glow-one"></div>
            <div class="sr-hero-glow sr-hero-glow-two"></div>
            <div class="sr-hero-main">
                <span class="sr-hero-mark"><i class="mdi {{ $reportDefinition['icon'] }}"></i></span>
                <div class="sr-hero-copy">
                    <span class="sr-eyebrow"><i class="mdi mdi-chart-box-plus-outline"></i> {{ $reportModule['eyebrow'] }}</span>
                    <h1>{{ $reportDefinition['title'] }}</h1>
                    <p>{{ $reportDefinition['description'] }}</p>
                    <nav class="sr-hero-actions" aria-label="Navigasi isi laporan">
                        <a href="#srSummarySection"><i class="mdi mdi-view-dashboard-outline"></i> Ringkasan</a>
                        <a href="#srInsightsSection"><i class="mdi mdi-chart-areaspline"></i> Analisis</a>
                        <a href="#srTablePanel"><i class="mdi mdi-table-large"></i> Detail data</a>
                        @if (! empty($reportModule['analysis_url']))
                            <a href="{{ $reportModule['analysis_url'] }}"><i class="mdi mdi-open-in-new"></i> Dashboard analisis</a>
                        @endif
                    </nav>
                    <div class="sr-hero-pills">
                        <span><i></i> Data operasional langsung</span>
                        <span><i class="mdi mdi-shield-check-outline"></i> Sesuai akses cabang</span>
                        <span><i class="mdi mdi-information-outline"></i> {{ $reportModule['basis_label'] }}</span>
                    </div>
                </div>
            </div>
            <div class="sr-hero-context">
                <div>
                    <span class="sr-context-icon"><i class="mdi mdi-map-marker-radius-outline"></i></span>
                    <span><small>Cakupan cabang</small><strong id="srBranchContext">Menyiapkan...</strong></span>
                </div>
                <div>
                    <span class="sr-context-icon"><i class="mdi mdi-calendar-range-outline"></i></span>
                    <span><small>Periode aktif</small><strong id="srPeriodContext">Menyiapkan...</strong></span>
                </div>
                <div>
                    <span class="sr-context-icon"><i class="mdi mdi-clock-check-outline"></i></span>
                    <span><small>Pembaruan terakhir</small><strong id="srGeneratedContext">Menyiapkan...</strong></span>
                </div>
            </div>
        </section>

        <section class="sr-report-switcher" aria-labelledby="srReportSwitcherTitle">
            <div class="sr-section-intro sr-switcher-intro">
                <div>
                    <span class="sr-kicker">Pusat analisis · {{ count($reportTypes) }} perspektif laporan</span>
                    <h2 id="srReportSwitcherTitle">{{ $reportModule['switcher_title'] }}</h2>
                </div>
                <div class="sr-switcher-tools">
                    <p>Filter periode dan cabang tetap terbawa saat Anda berpindah laporan.</p>
                    <span class="sr-rail-controls" aria-label="Geser daftar laporan">
                        <button type="button" id="srReportPrevious" aria-label="Lihat laporan sebelumnya"><i class="mdi mdi-chevron-left"></i></button>
                        <button type="button" id="srReportNext" aria-label="Lihat laporan berikutnya"><i class="mdi mdi-chevron-right"></i></button>
                    </span>
                </div>
            </div>
            <nav class="sr-report-nav" id="srReportNav" aria-label="{{ $reportModule['nav_label'] }}" tabindex="0">
                @foreach ($reportTypes as $reportSlug => $reportMenu)
                    <a href="{{ route($reportModule['index_route'], ['report' => $reportSlug]) }}"
                        class="sr-report-link sr-link-tone-{{ $reportMenu['tone'] }} {{ $reportSlug === $reportType ? 'is-active' : '' }}"
                        @if ($reportSlug === $reportType) aria-current="page" @endif>
                        <span class="sr-report-link-icon"><i class="mdi {{ $reportMenu['icon'] }}"></i></span>
                        <span class="sr-report-link-copy">
                            <small>{{ $reportSlug === $reportType ? 'Sedang dilihat' : 'Laporan '.str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</small>
                            <b>{{ $reportMenu['short_title'] }}</b>
                        </span>
                        <i class="mdi {{ $reportSlug === $reportType ? 'mdi-check-circle' : 'mdi-arrow-right' }} sr-report-arrow"></i>
                    </a>
                @endforeach
            </nav>
        </section>

        <section class="sr-filter-card">
            <div class="sr-filter-head">
                <div class="sr-panel-heading">
                    <span class="sr-heading-icon"><i class="mdi mdi-tune-variant"></i></span>
                    <div><h2>Filter laporan</h2><p>Atur cakupan data yang ingin dianalisis.</p></div>
                </div>
                <div class="sr-filter-head-actions">
                    <span class="sr-filter-status" id="srFilterStatus"><i></i> Filter aktif</span>
                    <button type="button" class="sr-icon-button" id="srFilterToggle" aria-expanded="true"
                        aria-controls="srFilterBody" title="Sembunyikan filter">
                        <i class="mdi mdi-chevron-up"></i>
                    </button>
                </div>
            </div>
            <div class="sr-filter-body" id="srFilterBody">
                <div class="sr-period-presets" role="group" aria-label="Periode cepat">
                    <span>Periode cepat</span>
                    <button type="button" data-range="today">Hari ini</button>
                    <button type="button" data-range="7">7 hari</button>
                    <button type="button" data-range="30" class="is-active">30 hari</button>
                    <button type="button" data-range="mtd">Bulan ini</button>
                    <button type="button" data-range="ytd">Tahun ini</button>
                </div>
                <form id="srFilterForm" class="sr-filter-grid {{ $showSupplierFilter ? 'has-supplier' : '' }}">
                    <label class="sr-field sr-field-branch">
                        <span>Cabang</span>
                        <span class="sr-field-control"><i class="mdi mdi-source-branch"></i>
                            <select class="form-select" id="srBranch" name="branch_id">
                                <option value="">Semua cabang yang dapat diakses</option>
                            </select>
                        </span>
                    </label>
                    @if ($showSupplierFilter)
                        <label class="sr-field sr-field-supplier">
                            <span>Supplier</span>
                            <span class="sr-field-control"><i class="mdi mdi-truck-outline"></i>
                                <select class="form-select" id="srSupplier" name="supplier_id">
                                    <option value="">Semua supplier</option>
                                </select>
                            </span>
                        </label>
                    @endif
                    <label class="sr-field">
                        <span>Tanggal mulai</span>
                        <span class="sr-field-control"><i class="mdi mdi-calendar-start-outline"></i>
                            <input type="date" class="form-control" id="srDateStart" name="date_start"
                                value="{{ $defaultDateStart }}">
                        </span>
                    </label>
                    <label class="sr-field">
                        <span>Tanggal akhir</span>
                        <span class="sr-field-control"><i class="mdi mdi-calendar-end-outline"></i>
                            <input type="date" class="form-control" id="srDateEnd" name="date_end"
                                value="{{ $defaultDateEnd }}">
                        </span>
                    </label>
                    <button type="submit" class="btn sr-apply-button" id="srApplyFilter">
                        <i class="mdi mdi-filter-check-outline"></i>
                        <span>Tampilkan data</span>
                    </button>
                </form>
                <div class="sr-active-filter" aria-live="polite">
                    <span><i class="mdi mdi-filter-check-outline"></i> Filter aktif</span>
                    <div id="srActiveFilterSummary"><em>Menyiapkan parameter...</em></div>
                    <button type="button" id="srResetFilter"><i class="mdi mdi-filter-remove-outline"></i> Reset</button>
                </div>
            </div>
        </section>

        <div class="sr-section-intro sr-results-intro" id="srSummarySection">
            <div><span class="sr-kicker">Ringkasan eksekutif</span><h2>Kinerja utama periode aktif</h2></div>
            <p id="srSummaryContext">{{ $reportModule['summary_loading'] }}</p>
        </div>

        <section class="sr-metric-grid" id="srMetricGrid" aria-label="Ringkasan laporan" aria-live="polite">
            @for ($i = 0; $i < 4; $i++)
                <article class="sr-metric-card is-loading">
                    <span class="sr-metric-icon"></span><div><small>Memuat data</small><strong>&nbsp;</strong><p>&nbsp;</p></div>
                </article>
            @endfor
        </section>

        <section class="sr-insight-layout" id="srInsightsSection">
            <article class="sr-panel sr-chart-panel">
                <div class="sr-panel-head">
                    <div class="sr-panel-heading">
                        <span class="sr-heading-icon is-chart"><i class="mdi mdi-chart-areaspline"></i></span>
                        <div><h2 id="srChartTitle">{{ $reportModule['chart_title'] }}</h2><p id="srChartSubtitle">Mengolah data periode aktif...</p></div>
                    </div>
                    <div class="sr-chart-legend" id="srChartLegend"></div>
                </div>
                <div class="sr-chart-shell" id="srChart" role="img" aria-label="{{ $reportModule['chart_aria'] }}">
                    <div class="sr-loading-state"><i class="mdi mdi-loading mdi-spin"></i><span>{{ $reportModule['chart_loading'] }}</span></div>
                </div>
            </article>

            <aside class="sr-insight-stack">
                <article class="sr-insight-card">
                    <div class="sr-insight-top"><span><i class="mdi mdi-lightning-bolt-outline"></i></span><small>Insight periode</small></div>
                    <h3 id="srInsightTitle">Menyiapkan insight...</h3>
                    <p id="srInsightCopy">Insight otomatis akan muncul setelah laporan selesai dihitung.</p>
                    <div class="sr-insight-peak">
                        <small id="srPeakLabel">Nilai puncak</small>
                        <strong id="srPeakValue">—</strong>
                    </div>
                </article>
                <article class="sr-health-card">
                    <div class="sr-health-head"><span><i class="mdi mdi-heart-pulse"></i></span><div><small>{{ $reportModule['health_eyebrow'] }}</small><b>{{ $reportModule['health_title'] }}</b></div></div>
                    <strong id="srNetSales">—</strong>
                    <div class="sr-health-track"><span id="srHealthTrack"></span></div>
                    <p><span id="srReturnRatio">0%</span> {{ $reportModule['health_copy'] }}</p>
                </article>
            </aside>
        </section>

        <section class="sr-panel sr-table-panel" id="srTablePanel">
            <div class="sr-table-toolbar">
                <div class="sr-panel-heading">
                    <span class="sr-heading-icon is-table"><i class="mdi mdi-table-large"></i></span>
                    <div><h2 id="srTableTitle">Detail laporan</h2><p id="srTableInfo" aria-live="polite">Menyiapkan data...</p></div>
                </div>
                <div class="sr-table-actions">
                    <label class="sr-table-search">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="srSearch" placeholder="Cari di laporan..." aria-label="Cari di laporan">
                        <button type="button" id="srClearSearch" aria-label="Hapus pencarian" hidden><i class="mdi mdi-close"></i></button>
                    </label>
                    <label class="sr-page-size">Baris
                        <select id="srPageSize" class="form-select form-select-sm">
                            <option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option>
                        </select>
                    </label>
                    <div class="sr-export-actions" aria-label="Aksi laporan">
                        <button type="button" class="btn btn-outline-success btn-sm" id="srExportCsv" disabled>
                            <i class="mdi mdi-file-delimited-outline"></i> Ekspor CSV
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="srPrint">
                            <i class="mdi mdi-printer-outline"></i> Cetak
                        </button>
                    </div>
                    <div class="sr-view-actions" aria-label="Pengaturan tampilan tabel">
                        <div class="sr-column-control">
                            <button type="button" class="sr-icon-button" id="srColumnToggle" title="Atur kolom"
                                aria-label="Atur kolom yang ditampilkan" aria-expanded="false" aria-controls="srColumnMenu">
                                <i class="mdi mdi-table-column-plus-after"></i>
                            </button>
                            <div class="sr-column-menu" id="srColumnMenu" hidden>
                                <div class="sr-column-menu-head">
                                    <span><small>Tampilan data</small><strong>Pilih kolom</strong></span>
                                    <button type="button" id="srResetColumns">Tampilkan semua</button>
                                </div>
                                <div class="sr-column-menu-list" id="srColumnMenuList"></div>
                            </div>
                        </div>
                        <button type="button" class="sr-icon-button" id="srDensityToggle" title="Gunakan tampilan ringkas"
                            aria-label="Gunakan tampilan tabel ringkas" aria-pressed="false">
                            <i class="mdi mdi-format-line-spacing"></i>
                        </button>
                        <button type="button" class="sr-icon-button" id="srFocusTable" title="Buka mode fokus"
                            aria-label="Buka tabel dalam mode fokus" aria-pressed="false">
                            <i class="mdi mdi-arrow-expand-all"></i>
                        </button>
                        <button type="button" class="sr-icon-button" id="srRefresh" title="Muat ulang data" aria-label="Muat ulang data">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="sr-table-guide">
                <span><i class="mdi mdi-gesture-tap"></i>Klik judul untuk mengurutkan dan klik baris untuk menyorot data.</span>
                <strong id="srTableViewMeta">Menyiapkan kolom...</strong>
            </div>
            <div class="sr-table-viewport" id="srTableViewport">
                <div class="table-responsive sr-table-wrap" id="srTableWrap" tabindex="0" aria-label="Tabel laporan {{ $reportModule['key'] }}">
                    <table class="table sr-table align-middle" id="srTable">
                        <thead><tr id="srTableHead"></tr></thead>
                        <tbody id="srTableBody">
                            <tr><td><div class="sr-loading-state"><i class="mdi mdi-loading mdi-spin"></i><span>Mengambil data laporan...</span></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="sr-pagination">
                <span id="srPaginationInfo">0 data</span>
                <div class="sr-pagination-navigation" aria-label="Navigasi halaman tabel">
                    <button type="button" id="srPreviousPage" aria-label="Halaman sebelumnya" disabled><i class="mdi mdi-chevron-left"></i></button>
                    <div class="sr-page-numbers" id="srPageNumbers"></div>
                    <span id="srPageLabel" class="sr-page-label" aria-live="polite">Halaman 1</span>
                    <button type="button" id="srNextPage" aria-label="Halaman berikutnya" disabled><i class="mdi mdi-chevron-right"></i></button>
                </div>
            </div>
        </section>

        <div class="sr-toast" id="srToast" role="status" aria-live="polite" hidden>
            <i class="mdi mdi-check-circle-outline"></i><span></span>
        </div>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.laporan.penjualan.partials.script")
@endpush
