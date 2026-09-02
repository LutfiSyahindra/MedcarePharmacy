@extends('template.partials.app')

@push('style')
    @include('template.AddOn.mdiicon')
    @include('medcare.menu.analisisOmzet.partials.style')
@endpush

@section('content')
    <main class="ro-page" id="revenueAnalysisApp"
        data-url="{{ route('analisisPenjualan.data') }}"
        data-target-url="{{ route('analisisPenjualan.target.store') }}"
        data-excel-url="{{ route('analisisPenjualan.export.excel') }}"
        data-pdf-url="{{ route('analisisPenjualan.export.pdf') }}"
        data-default-start="{{ $defaultDateStart }}"
        data-default-end="{{ $defaultDateEnd }}">
        <nav class="page-breadcrumb ro-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i> Dashboard</a></li>
                <li class="breadcrumb-item">Analisis</li>
                <li class="breadcrumb-item active" aria-current="page">Penjualan</li>
            </ol>
        </nav>

        <section class="ro-hero">
            <div class="ro-hero-orb ro-orb-one"></div><div class="ro-hero-orb ro-orb-two"></div>
            <div class="ro-hero-copy">
                <div class="ro-hero-brandline">
                    <span class="ro-hero-mark"><i class="mdi mdi-chart-timeline-variant-shimmer"></i></span>
                    <span class="ro-eyebrow"><i class="mdi mdi-chart-box-outline"></i> Sales Intelligence</span>
                    <span class="ro-live-badge"><i></i> Live Analytics</span>
                </div>
                <h1>Analisis Penjualan</h1>
                <p>Pantau tren, produk terlaris, kecepatan jual, jam ramai, dan peluang bundling dari satu dashboard operasional.</p>
                <div class="ro-hero-meta">
                    <span><i class="mdi mdi-check-decagram-outline"></i> Transaksi completed</span>
                    <span><i class="mdi mdi-keyboard-return"></i> Retur posted diperhitungkan</span>
                    <span><i class="mdi mdi-cancel"></i> Void & cancel dikecualikan</span>
                </div>
            </div>
            <div class="ro-hero-side">
                <div class="ro-context-card"><span><i class="mdi mdi-source-branch"></i></span><div><small>Cakupan</small><strong id="roHeroBranch">Menyiapkan...</strong></div></div>
                <div class="ro-context-card"><span><i class="mdi mdi-calendar-range-outline"></i></span><div><small>Periode aktif</small><strong id="roHeroPeriod">Menyiapkan...</strong></div></div>
                <div class="ro-export-group">
                    <button type="button" id="roExportExcel"><i class="mdi mdi-microsoft-excel"></i> Excel</button>
                    <button type="button" id="roExportPdf"><i class="mdi mdi-file-pdf-box"></i> PDF</button>
                    <button type="button" id="roPrint"><i class="mdi mdi-printer-outline"></i> Print</button>
                </div>
            </div>
        </section>

        <nav class="ro-dashboard-nav" aria-label="Navigasi analisis penjualan">
            <a href="#roTrendPanel"><i class="mdi mdi-chart-areaspline"></i><span>Tren Penjualan</span></a>
            <a href="#roFastMovingPanel"><i class="mdi mdi-rocket-launch-outline"></i><span>Fast Moving</span></a>
            <a href="#roProductsPanel"><i class="mdi mdi-trophy-outline"></i><span>Produk Terlaris</span></a>
            <a href="#roHourlyPanel"><i class="mdi mdi-clock-fast"></i><span>Jam Ramai</span></a>
            <a href="#roMarketBasketPanel"><i class="mdi mdi-set-center"></i><span>Market Basket</span></a>
        </nav>

        <section class="ro-filter-panel ro-panel" id="roFilterPanel" aria-labelledby="roFilterTitle">
            <span class="ro-filter-aura ro-filter-aura-one" aria-hidden="true"></span>
            <span class="ro-filter-aura ro-filter-aura-two" aria-hidden="true"></span>
            <div class="ro-filter-progress" aria-hidden="true"><span></span></div>
            <div class="ro-panel-head ro-filter-header">
                <div class="ro-heading ro-filter-heading">
                    <span><i class="mdi mdi-tune-variant"></i></span>
                    <div>
                        <span class="ro-filter-eyebrow">Smart controls</span>
                        <h2 id="roFilterTitle">Filter Analisis</h2>
                        <p>Atur cakupan data untuk seluruh KPI, grafik, dan tabel.</p>
                    </div>
                </div>
                <div class="ro-filter-head-actions">
                    <div class="ro-filter-state is-ready" id="roFilterState" role="status" aria-live="polite">
                        <i class="mdi mdi-check-decagram-outline"></i>
                        <span><small>Status parameter</small><strong id="roFilterStateText">Siap digunakan</strong></span>
                    </div>
                    <button type="button" class="ro-filter-toggle" id="roFilterToggle" aria-expanded="true" aria-controls="roFilterBody" title="Ringkas filter">
                        <span>Ringkas</span><i class="mdi mdi-chevron-up"></i>
                    </button>
                </div>
            </div>
            <div class="ro-filter-body" id="roFilterBody">
                <div class="ro-filter-body-inner">
                    <div class="ro-presets" role="group" aria-label="Periode cepat">
                        <div class="ro-preset-label"><i class="mdi mdi-calendar-clock-outline"></i><span><strong>Periode cepat</strong><small>Pilih rentang instan</small></span></div>
                        <div class="ro-preset-options">
                            <button type="button" data-range="today" aria-pressed="false"><i class="mdi mdi-white-balance-sunny"></i>Hari Ini</button>
                            <button type="button" data-range="7" aria-pressed="false"><i class="mdi mdi-calendar-week-outline"></i>7 Hari</button>
                            <button type="button" data-range="30" class="is-active" aria-pressed="true"><i class="mdi mdi-calendar-month-outline"></i>30 Hari</button>
                            <button type="button" data-range="mtd" aria-pressed="false">Bulan Ini</button>
                            <button type="button" data-range="last-month" aria-pressed="false">Bulan Lalu</button>
                            <button type="button" data-range="ytd" aria-pressed="false">Tahun Ini</button>
                            <button type="button" data-range="custom" aria-pressed="false"><i class="mdi mdi-calendar-edit-outline"></i>Custom</button>
                        </div>
                    </div>
                    <form class="ro-filter-form" id="roFilterForm">
                        <div class="ro-filter-group">
                            <div class="ro-filter-group-head">
                                <span>01</span>
                                <div><strong>Cakupan utama</strong><small>Tentukan cabang dan periode laporan</small></div>
                            </div>
                            <div class="ro-filter-grid ro-filter-grid-primary">
                                <label class="ro-field"><span><i class="mdi mdi-source-branch"></i>Cabang</span><select id="roBranch" name="branch_id"><option value="">Semua cabang</option></select></label>
                                <label class="ro-field"><span><i class="mdi mdi-calendar-start-outline"></i>Tanggal mulai</span><input type="date" id="roDateStart" name="date_start" value="{{ $defaultDateStart }}"></label>
                                <label class="ro-field"><span><i class="mdi mdi-calendar-end-outline"></i>Tanggal akhir</span><input type="date" id="roDateEnd" name="date_end" value="{{ $defaultDateEnd }}"></label>
                                <label class="ro-field"><span><i class="mdi mdi-account-tie-outline"></i>Kasir</span><select id="roCashier" name="cashier_id"><option value="">Semua kasir</option></select></label>
                            </div>
                        </div>
                        <div class="ro-filter-group ro-filter-group-detail">
                            <div class="ro-filter-group-head">
                                <span>02</span>
                                <div><strong>Segmentasi detail</strong><small>Persempit analisis sesuai kebutuhan</small></div>
                                <span class="ro-optional-badge">Opsional</span>
                            </div>
                            <div class="ro-filter-grid ro-filter-grid-detail">
                                <label class="ro-field"><span><i class="mdi mdi-clock-outline"></i>Shift</span><select id="roShift" name="shift_id"><option value="">Semua shift</option></select></label>
                                <label class="ro-field"><span><i class="mdi mdi-pill-multiple"></i>Obat</span><select id="roMedicine" name="medicine_id"><option value="">Semua obat</option></select></label>
                                <label class="ro-field"><span><i class="mdi mdi-shape-outline"></i>Kategori</span><select id="roCategory" name="category_id"><option value="">Semua kategori</option></select></label>
                                <label class="ro-field"><span><i class="mdi mdi-format-list-bulleted-type"></i>Golongan</span><select id="roClassification" name="golongan_id"><option value="">Semua golongan</option></select></label>
                                <label class="ro-field"><span><i class="mdi mdi-cart-arrow-right"></i>Jenis penjualan</span><select id="roSaleType" name="transaction_type"><option value="">Semua jenis</option></select></label>
                                <label class="ro-field"><span><i class="mdi mdi-credit-card-multiple-outline"></i>Metode pembayaran</span><select id="roPaymentMethod" name="payment_method"><option value="">Semua metode</option></select></label>
                                <label class="ro-field ro-field-command-only"><span>Agregasi tren</span><select id="roGranularity" name="granularity"><option value="day">Harian</option><option value="week">Mingguan</option><option value="month">Bulanan</option><option value="year">Tahunan</option></select></label>
                                <label class="ro-field ro-field-command-only"><span>Ranking produk</span><select id="roTop" name="top"><option value="10">Top 10</option><option value="20">Top 20</option><option value="all">Semua</option></select></label>
                                <label class="ro-field ro-field-command-only"><span>Metrik produk</span><select id="roProductMetric" name="product_metric"><option value="revenue">Omzet</option><option value="qty">Qty bersih</option><option value="transactions">Transaksi</option></select></label>
                            </div>
                        </div>
                        <div class="ro-filter-actions">
                            <div class="ro-filter-action-copy" id="roFilterActionCopy"><i class="mdi mdi-information-outline"></i><span><strong>Filter tersinkron</strong><small>Ubah parameter lalu terapkan untuk memperbarui dashboard.</small></span></div>
                            <div>
                                <button type="button" class="ro-btn ro-btn-ghost" id="roReset"><i class="mdi mdi-backup-restore"></i> Reset</button>
                                <button type="submit" class="ro-btn ro-btn-primary" id="roApply"><i class="mdi mdi-filter-check-outline"></i> Terapkan Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="ro-active-filters">
                        <span><i class="mdi mdi-filter-variant"></i> Filter aktif <b id="roFilterCount">0</b></span>
                        <div id="roActiveFilters"><em>Menyiapkan...</em></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="ro-results-heading" id="roExecutive">
            <div><span>Executive overview</span><h2>Kinerja omzet periode aktif</h2></div>
            <p><i class="mdi mdi-clock-check-outline"></i> Diperbarui <strong id="roDataTimestamp">—</strong></p>
        </div>

        <section class="ro-kpi-grid" id="roKpiGrid" aria-live="polite">
            @for ($index = 0; $index < 8; $index++)
                <article class="ro-kpi is-loading"><span class="ro-kpi-icon"></span><div><small>Memuat</small><strong>&nbsp;</strong><p>&nbsp;</p></div></article>
            @endfor
        </section>

        <section class="ro-panel ro-trend-panel" id="roTrendPanel">
            <div class="ro-panel-head">
                <div class="ro-heading"><span class="is-teal"><i class="mdi mdi-chart-areaspline"></i></span><div><h2>Grafik Tren Omzet</h2><p id="roTrendSubtitle">Omzet bersih dibanding periode sebelumnya.</p></div></div>
                <div class="ro-panel-tools">
                    <div class="ro-segment" id="roTrendControls" aria-label="Agregasi grafik tren">
                        <button type="button" data-granularity="day" class="is-active">Harian</button>
                        <button type="button" data-granularity="week">Mingguan</button>
                        <button type="button" data-granularity="month">Bulanan</button>
                        <button type="button" data-granularity="year">Tahunan</button>
                    </div>
                    <div class="ro-chart-legend"><span><i class="current"></i> Aktif</span><span><i class="previous"></i> Sebelumnya</span><span><i class="transactions"></i> Transaksi</span></div>
                </div>
            </div>
            <div class="ro-chart ro-chart-large" id="roTrendChart"><div class="ro-loading"><i class="mdi mdi-loading mdi-spin"></i> Menyusun tren omzet...</div></div>
        </section>

        <section class="ro-panel ro-products-panel ro-analysis-panel" id="roFastMovingPanel">
            <div class="ro-panel-head">
                <div class="ro-heading"><span class="is-green"><i class="mdi mdi-rocket-launch-outline"></i></span><div><h2>Fast Moving</h2><p id="roFastMovingSummary">Mengukur kecepatan jual bersih per hari dan frekuensi transaksi.</p></div></div>
                <span class="ro-soft-badge" id="roFastMovingCount">0 produk</span>
            </div>
            <div class="table-responsive"><table class="table ro-table"><thead><tr><th>Rank</th><th>Nama obat</th><th class="text-end">Qty bersih</th><th class="text-end">Transaksi</th><th class="text-end">Hari aktif</th><th class="text-end">Rata-rata / hari</th><th class="text-end">Penetrasi</th><th class="text-end">Growth qty</th></tr></thead><tbody id="roFastMovingBody"></tbody></table></div>
        </section>

        <section class="ro-panel ro-products-panel" id="roProductsPanel">
            <div class="ro-panel-head">
                <div class="ro-heading"><span class="is-blue"><i class="mdi mdi-trophy-outline"></i></span><div><h2>Produk Terlaris</h2><p id="roProductMetricCopy">Ranking berdasarkan omzet bersih setelah retur.</p></div></div>
                <div class="ro-panel-tools is-inline">
                    <div class="ro-segment" id="roProductMetricControls" aria-label="Metrik ranking produk">
                        <button type="button" data-product-metric="revenue" class="is-active">Omzet</button>
                        <button type="button" data-product-metric="qty">Qty</button>
                        <button type="button" data-product-metric="transactions">Transaksi</button>
                    </div>
                    <div class="ro-segment" id="roTopControls" aria-label="Jumlah ranking produk">
                        <button type="button" data-top="10" class="is-active">Top 10</button>
                        <button type="button" data-top="20">Top 20</button>
                        <button type="button" data-top="all">Semua</button>
                    </div>
                    <span class="ro-soft-badge" id="roProductCount">0 produk</span>
                </div>
            </div>
            <div class="table-responsive"><table class="table ro-table"><thead><tr><th>Rank</th><th>Nama obat</th><th>Kategori</th><th class="text-end">Qty bersih</th><th class="text-end">Transaksi</th><th class="text-end">Omzet</th><th class="text-end">Kontribusi</th><th class="text-end">Growth</th></tr></thead><tbody id="roProductBody"></tbody></table></div>
        </section>

        <section class="ro-grid ro-grid-two" id="roBreakdowns">
            <article class="ro-panel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-violet"><i class="mdi mdi-shape-outline"></i></span><div><h2>Omzet per Kategori</h2><p>Kontribusi kategori terhadap omzet.</p></div></div></div>
                <div class="ro-split-chart"><div class="ro-chart" id="roCategoryChart"></div><div class="ro-mini-list" id="roCategoryList"></div></div>
            </article>
            <article class="ro-panel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-indigo"><i class="mdi mdi-format-list-bulleted-type"></i></span><div><h2>Omzet per Jenis Penjualan</h2><p>OTC, resep, racikan, dan jenis lainnya.</p></div></div></div>
                <div class="ro-chart" id="roTypeChart"></div><div class="ro-summary-list" id="roTypeList"></div>
            </article>
        </section>

        <section class="ro-grid ro-grid-two">
            <article class="ro-panel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-green"><i class="mdi mdi-credit-card-multiple-outline"></i></span><div><h2>Omzet per Metode Pembayaran</h2><p>Pembayaran split dinormalisasi terhadap nilai transaksi.</p></div></div></div>
                <div class="ro-chart" id="roPaymentChart"></div><div class="ro-summary-list" id="roPaymentList"></div>
            </article>
            <article class="ro-panel" id="roHourlyPanel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-orange"><i class="mdi mdi-clock-fast"></i></span><div><h2>Jam Ramai</h2><p id="roPeakHour">Mencari jam dengan transaksi terbanyak...</p><small id="roPeakRevenue"></small></div></div></div>
                <div class="ro-chart" id="roHourlyChart"></div>
            </article>
        </section>

        <section class="ro-panel ro-products-panel ro-analysis-panel" id="roMarketBasketPanel">
            <div class="ro-panel-head">
                <div class="ro-heading"><span class="is-violet"><i class="mdi mdi-set-center"></i></span><div><h2>Market Basket</h2><p id="roMarketBasketSummary">Mencari pasangan produk yang muncul pada struk yang sama.</p></div></div>
                <span class="ro-soft-badge" id="roMarketBasketCount">0 pasangan</span>
            </div>
            <div class="ro-basket-guide">
                <span><strong>Support</strong> porsi seluruh transaksi yang memuat pasangan</span>
                <span><strong>Confidence</strong> peluang produk kedua ikut terbeli</span>
                <span><strong>Lift</strong> nilai &gt; 1 menandakan asosiasi positif</span>
            </div>
            <div class="table-responsive"><table class="table ro-table ro-basket-table"><thead><tr><th>Pasangan produk</th><th class="text-end">Transaksi bersama</th><th class="text-end">Support</th><th class="text-end">Confidence A → B</th><th class="text-end">Confidence B → A</th><th class="text-end">Lift</th><th>Kekuatan</th></tr></thead><tbody id="roMarketBasketBody"></tbody></table></div>
        </section>

        <section class="ro-grid ro-grid-two">
            <article class="ro-panel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-rose"><i class="mdi mdi-calendar-week"></i></span><div><h2>Pola Penjualan per Hari</h2><p id="roPeakDay">Mencari hari terbaik...</p></div></div></div>
                <div class="ro-chart" id="roWeekdayChart"></div>
                <div class="table-responsive"><table class="table ro-table ro-table-compact"><thead><tr><th>Hari</th><th class="text-end">Omzet</th><th class="text-end">Rata-rata</th><th class="text-end">Transaksi</th></tr></thead><tbody id="roWeekdayBody"></tbody></table></div>
            </article>
            <article class="ro-panel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-cyan"><i class="mdi mdi-account-tie-outline"></i></span><div><h2>Kontribusi Omzet per Kasir</h2><p>Produktivitas dan rata-rata nilai transaksi.</p></div></div></div>
                <div class="table-responsive ro-cashier-table"><table class="table ro-table ro-table-compact"><thead><tr><th>Kasir</th><th class="text-end">Trx</th><th class="text-end">Qty</th><th class="text-end">Omzet</th><th class="text-end">Rata-rata</th><th class="text-end">%</th></tr></thead><tbody id="roCashierBody"></tbody></table></div>
            </article>
        </section>

        <section class="ro-grid ro-bottom-grid">
            <article class="ro-panel ro-target-panel" id="roTargetPanel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-teal"><i class="mdi mdi-bullseye-arrow"></i></span><div><h2>Target vs Realisasi Omzet</h2><p>Target mengikuti satu cabang dan rentang periode aktif.</p></div></div><span class="ro-status" id="roTargetStatus">Belum Diatur</span></div>
                <div class="ro-target-values"><div><small>Target omzet</small><strong id="roTargetAmount">Rp 0</strong></div><div><small>Realisasi</small><strong id="roTargetRealization">Rp 0</strong></div><div><small>Sisa target</small><strong id="roTargetRemaining">Rp 0</strong></div></div>
                <div class="ro-progress-meta"><span>Pencapaian</span><strong id="roTargetPercent">0%</strong></div>
                <div class="ro-progress"><span id="roTargetProgress"></span></div>
                <form id="roTargetForm" class="ro-target-form">
                    <label><span>Atur target periode aktif</span><div><span>Rp</span><input type="number" min="0" step="1000" id="roTargetInput" placeholder="0"><button type="submit" class="ro-btn ro-btn-primary"><i class="mdi mdi-content-save-outline"></i> Simpan</button></div></label>
                    <p id="roTargetHint">Pilih satu cabang untuk mengatur target.</p>
                </form>
            </article>
            <article class="ro-panel ro-insight-panel">
                <div class="ro-panel-head"><div class="ro-heading"><span class="is-amber"><i class="mdi mdi-lightbulb-on-outline"></i></span><div><h2>Insight Omzet Otomatis</h2><p>Ringkasan cepat berdasarkan filter aktif.</p></div></div></div>
                <div class="ro-insight-list" id="roInsightList"></div>
            </article>
        </section>

        <div class="ro-toast" id="roToast" role="status" aria-live="polite" hidden><i class="mdi mdi-check-circle-outline"></i><span></span></div>
    </main>
@endsection

@push('scripts')
    @include('medcare.menu.analisisOmzet.partials.script')
@endpush
