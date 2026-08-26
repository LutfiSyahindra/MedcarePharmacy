@extends('template.partials.app')

@push('style')
    @include('template.AddOn.mdiicon')
    @include('medcare.dashboard.style')
@endpush

@section('content')
    <main class="command-center" id="commandCenter" data-endpoint="{{ route('dashboard.data') }}">
        <section class="cc-hero">
            <div class="cc-hero-copy">
                <span class="cc-eyebrow"><i class="mdi mdi-view-dashboard-variant-outline"></i> Pharmacy intelligence</span>
                <h1>Dashboard Command Center</h1>
                <p>Pantau penjualan, profitabilitas, persediaan, tagihan, dan pekerjaan prioritas dari satu layar.</p>
                <div class="cc-live-line">
                    <span class="cc-live-dot"></span>
                    <strong>Data operasional aktif</strong>
                    <span id="ccGeneratedLabel">Diperbarui {{ $dashboard['meta']['generated_label'] }}</span>
                </div>
            </div>

            <div class="cc-filter-shell" aria-label="Filter dashboard">
                <div class="cc-filter-topline">
                    <div>
                        <small>Ruang pantau</small>
                        <strong id="ccScopeLabel">{{ $dashboard['meta']['branch_label'] }}</strong>
                    </div>
                    <button type="button" class="cc-refresh-button" id="ccRefreshButton" title="Perbarui data">
                        <i class="mdi mdi-refresh"></i><span>Refresh</span>
                    </button>
                </div>
                <div class="cc-filter-grid">
                    <label class="cc-field">
                        <span>Cabang</span>
                        <select id="ccBranchFilter" class="form-select">
                            @if (count($dashboard['meta']['branches']) > 1)
                                <option value="">Semua cabang</option>
                            @endif
                            @foreach ($dashboard['meta']['branches'] as $branch)
                                <option value="{{ $branch['id'] }}" @selected((string) $dashboard['meta']['branch_id'] === (string) $branch['id'])>
                                    {{ $branch['name'] }}{{ $branch['active'] ? '' : ' · Nonaktif' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <div class="cc-field">
                        <span>Periode</span>
                        <div class="cc-period-tabs" id="ccPeriodTabs">
                            @foreach (['today' => 'Hari ini', '7' => '7H', '30' => '30H', '90' => '90H', 'mtd' => 'Bulan ini'] as $period => $label)
                                <button type="button" data-period="{{ $period }}" class="{{ $dashboard['meta']['period'] === $period ? 'is-active' : '' }}">{{ $label }}</button>
                            @endforeach
                            <button type="button" data-period="custom" class="{{ $dashboard['meta']['period'] === 'custom' ? 'is-active' : '' }}" title="Rentang tanggal khusus"><i class="mdi mdi-calendar-range"></i></button>
                        </div>
                    </div>
                </div>
                <div class="cc-custom-range {{ $dashboard['meta']['period'] === 'custom' ? 'is-open' : '' }}" id="ccCustomRange">
                    <label><span>Dari</span><input type="date" id="ccDateStart" value="{{ $dashboard['meta']['start_date'] }}"></label>
                    <i class="mdi mdi-arrow-right"></i>
                    <label><span>Sampai</span><input type="date" id="ccDateEnd" value="{{ $dashboard['meta']['end_date'] }}"></label>
                    <button type="button" id="ccApplyCustomRange">Terapkan</button>
                </div>
                <div class="cc-filter-caption">
                    <i class="mdi mdi-calendar-check-outline"></i>
                    <span id="ccRangeLabel">{{ $dashboard['meta']['range_label'] }}</span>
                </div>
            </div>
        </section>

        <div class="cc-system-message" id="ccSystemMessage" hidden></div>

        @if ($dashboard['meta']['empty_scope'])
            <section class="cc-empty-scope">
                <i class="mdi mdi-store-alert-outline"></i>
                <div><strong>Akun belum memiliki akses cabang</strong><span>Hubungkan akun ke cabang agar Command Center dapat menampilkan data.</span></div>
            </section>
        @endif

        <section class="cc-section-heading">
            <div>
                <span>Ringkasan eksekutif</span>
                <h2>Kesehatan bisnis dalam satu pandangan</h2>
            </div>
            <p>Perbandingan KPI menggunakan periode sebelumnya dengan durasi yang sama.</p>
        </section>
        <section class="cc-kpi-grid" id="ccKpiGrid" aria-live="polite"></section>

        <section class="cc-section-heading cc-section-heading--compact">
            <div>
                <span>Analisis cepat</span>
                <h2>Sinyal utama untuk pengambilan keputusan</h2>
            </div>
            <p>Interpretasi otomatis dari performa periode aktif, persediaan, dan posisi tagihan.</p>
        </section>
        <section class="cc-analysis-grid" id="ccAnalysisGrid" aria-live="polite"></section>

        <section class="cc-section-heading cc-section-heading--compact">
            <div>
                <span>Pusat tindakan</span>
                <h2>Apa yang perlu ditangani sekarang?</h2>
            </div>
            <div class="cc-alert-total"><b id="ccAlertTotal">{{ $dashboard['alerts']['total'] }}</b> alert terdeteksi</div>
        </section>
        <section class="cc-action-grid" id="ccActionCenter"></section>

        <section class="cc-main-grid">
            <article class="cc-panel cc-panel--wide">
                <header class="cc-panel-header">
                    <div class="cc-panel-title">
                        <span class="cc-panel-icon is-primary"><i class="mdi mdi-chart-timeline-variant-shimmer"></i></span>
                        <div><small>Revenue pulse</small><h3>Tren omzet bersih</h3></div>
                    </div>
                    <div class="cc-chart-legend"><span><i class="is-sales"></i> Omzet</span><span><i class="is-transaction"></i> Transaksi</span></div>
                </header>
                <div class="cc-chart" id="ccRevenueChart" aria-label="Grafik tren omzet dan transaksi"></div>
            </article>

            <article class="cc-panel cc-alert-panel">
                <header class="cc-panel-header">
                    <div class="cc-panel-title">
                        <span class="cc-panel-icon is-danger"><i class="mdi mdi-bell-badge-outline"></i></span>
                        <div><small>Attention center</small><h3>Pusat alert</h3></div>
                    </div>
                    <a href="{{ route('notifikasi.SemuaNotifikasi') }}" class="cc-panel-link">Notifikasi <i class="mdi mdi-arrow-right"></i></a>
                </header>
                <div class="cc-alert-tabs" id="ccAlertTabs">
                    <button type="button" class="is-active" data-severity="">Semua <b id="ccAlertAllCount">0</b></button>
                    <button type="button" data-severity="critical"><i class="is-critical"></i>Kritis <b id="ccAlertCriticalCount">0</b></button>
                    <button type="button" data-severity="warning"><i class="is-warning"></i>Waspada <b id="ccAlertWarningCount">0</b></button>
                    <button type="button" data-severity="info"><i class="is-info"></i>Info <b id="ccAlertInfoCount">0</b></button>
                </div>
                <div class="cc-alert-list" id="ccAlertList"></div>
            </article>
        </section>

        <section class="cc-insight-grid">
            <article class="cc-panel">
                <header class="cc-panel-header">
                    <div class="cc-panel-title">
                        <span class="cc-panel-icon is-success"><i class="mdi mdi-package-variant-closed-check"></i></span>
                        <div><small>Inventory health</small><h3>Kesehatan persediaan</h3></div>
                    </div>
                    <a href="{{ route('stok.stok') }}" class="cc-panel-link">Kelola stok <i class="mdi mdi-arrow-right"></i></a>
                </header>
                <div class="cc-inventory-layout">
                    <div class="cc-donut-chart" id="ccInventoryChart"></div>
                    <div class="cc-inventory-legend" id="ccInventoryLegend"></div>
                </div>
            </article>

            <article class="cc-panel">
                <header class="cc-panel-header">
                    <div class="cc-panel-title">
                        <span class="cc-panel-icon is-violet"><i class="mdi mdi-wallet-outline"></i></span>
                        <div><small>Payment mix</small><h3>Komposisi pembayaran</h3></div>
                    </div>
                    <span class="cc-panel-badge">Transaksi selesai</span>
                </header>
                <div class="cc-payment-layout">
                    <div class="cc-donut-chart" id="ccPaymentChart"></div>
                    <div class="cc-payment-list" id="ccPaymentList"></div>
                </div>
            </article>

            <article class="cc-panel">
                <header class="cc-panel-header">
                    <div class="cc-panel-title">
                        <span class="cc-panel-icon is-orange"><i class="mdi mdi-trophy-outline"></i></span>
                        <div><small>Product performance</small><h3>Produk terlaris</h3></div>
                    </div>
                    <a href="{{ route('penjualan.pos.history') }}" class="cc-panel-link">Riwayat POS <i class="mdi mdi-arrow-right"></i></a>
                </header>
                <div class="cc-ranking-list" id="ccTopProducts"></div>
            </article>

            <article class="cc-panel">
                <header class="cc-panel-header">
                    <div class="cc-panel-title">
                        <span class="cc-panel-icon is-info"><i class="mdi mdi-store-marker-outline"></i></span>
                        <div><small>Branch performance</small><h3>Performa cabang</h3></div>
                    </div>
                    <span class="cc-panel-badge">Omzet bersih</span>
                </header>
                <div class="cc-branch-list" id="ccBranchPerformance"></div>
            </article>
        </section>

        <section class="cc-panel cc-activity-panel">
            <header class="cc-panel-header">
                <div class="cc-panel-title">
                    <span class="cc-panel-icon is-teal"><i class="mdi mdi-pulse"></i></span>
                    <div><small>Live operations</small><h3>Aktivitas operasional terbaru</h3></div>
                </div>
                <span class="cc-live-badge"><i></i> Terhubung ke transaksi</span>
            </header>
            <div class="cc-activity-list" id="ccRecentActivity"></div>
        </section>
    </main>
@endsection

@push('scripts')
    @include('medcare.dashboard.scripts')
@endpush
