@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("medcare.menu.analisisProfitabilitas.partials.style")
@endpush

@section("content")
    <div class="profitability-page" id="profitabilityAnalysisApp"
        data-url="{{ route('analisisProfitabilitas.data') }}">
        <nav class="page-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Analisis Profitabilitas</li>
            </ol>
        </nav>

        <section class="pa-hero">
            <div class="pa-hero-copy">
                <span class="pa-eyebrow"><i class="mdi mdi-finance"></i> Profit Intelligence</span>
                <h1>Lihat produk yang benar-benar menghasilkan.</h1>
                <p>Satukan penjualan neto, HPP batch, laba kotor, margin aktual, dan produktivitas modal persediaan dalam satu analisis.</p>
                <div class="pa-hero-chips">
                    <span><i class="mdi mdi-map-marker-outline"></i><b id="paBranchLabel">Memuat cabang...</b></span>
                    <span><i class="mdi mdi-calendar-range"></i><b id="paPeriodLabel">Menyiapkan periode...</b></span>
                </div>
            </div>
            <div class="pa-hero-score">
                <span>GMROI PORTOFOLIO</span>
                <strong id="paHeroGmroi">—</strong>
                <p id="paHeroGmroiCopy">Mengukur laba kotor yang dihasilkan setiap rupiah modal persediaan.</p>
                <small><i></i><span id="paGeneratedAt">Data sedang dihitung</span></small>
            </div>
        </section>

        <nav class="pa-section-nav" aria-label="Bagian analisis profitabilitas">
            <a href="#paProductPanel" class="is-active"><i class="mdi mdi-pill-multiple"></i><span>Profit per Produk</span></a>
            <a href="#paMarginPanel"><i class="mdi mdi-percent-outline"></i><span>Margin</span></a>
            <a href="#paGmroiPanel"><i class="mdi mdi-chart-donut-variant"></i><span>GMROI</span></a>
            <a href="#paTopProfitPanel"><i class="mdi mdi-trophy-outline"></i><span>Top Profit Product</span></a>
        </nav>

        <section class="pa-filter-card">
            <div class="pa-filter-heading">
                <div>
                    <span class="pa-heading-icon"><i class="mdi mdi-tune-variant"></i></span>
                    <div><h2>Parameter analisis</h2><p>Seluruh bagian memakai filter dan sumber data yang sama.</p></div>
                </div>
                <div class="pa-presets" aria-label="Periode cepat">
                    <button type="button" data-days="7">7 hari</button>
                    <button type="button" data-days="30" class="is-active">30 hari</button>
                    <button type="button" data-days="90">90 hari</button>
                    <button type="button" data-days="mtd">Bulan ini</button>
                </div>
            </div>
            <form id="paFilterForm" class="pa-filter-grid">
                <label class="pa-field">
                    <span>Cabang</span>
                    <select class="form-select" name="branch_id" id="paBranch">
                        <option value="">Semua cabang yang dapat diakses</option>
                    </select>
                </label>
                <label class="pa-field">
                    <span>Tanggal mulai</span>
                    <input type="date" class="form-control" name="date_start" id="paDateStart"
                        value="{{ today()->subDays(29)->toDateString() }}">
                </label>
                <label class="pa-field">
                    <span>Tanggal akhir</span>
                    <input type="date" class="form-control" name="date_end" id="paDateEnd"
                        value="{{ today()->toDateString() }}">
                </label>
                <label class="pa-field pa-field-search">
                    <span>Cari produk</span>
                    <div><i class="mdi mdi-magnify"></i><input type="search" class="form-control" name="search" id="paSearch" placeholder="Kode, nama, atau kategori"></div>
                </label>
                <label class="pa-field">
                    <span>Urutkan</span>
                    <select class="form-select" name="sort" id="paSort">
                        <option value="gross_profit">Laba kotor</option>
                        <option value="gross_margin">Margin kotor</option>
                        <option value="gmroi">GMROI</option>
                        <option value="net_sales">Penjualan neto</option>
                        <option value="net_hpp">HPP neto</option>
                        <option value="average_inventory_cost">Modal persediaan</option>
                        <option value="name">Nama produk</option>
                    </select>
                </label>
                <label class="pa-field">
                    <span>Arah</span>
                    <select class="form-select" name="direction" id="paDirection">
                        <option value="desc">Tertinggi</option>
                        <option value="asc">Terendah</option>
                    </select>
                </label>
                <label class="pa-field">
                    <span>Tampilkan</span>
                    <select class="form-select" name="top" id="paTop">
                        <option value="10">Top 10</option>
                        <option value="25" selected>Top 25</option>
                        <option value="50">Top 50</option>
                        <option value="all">Semua produk</option>
                    </select>
                </label>
                <div class="pa-filter-actions">
                    <button type="button" class="btn pa-btn-secondary" id="paReset"><i class="mdi mdi-filter-remove-outline"></i> Reset</button>
                    <button type="submit" class="btn pa-btn-primary" id="paApply"><i class="mdi mdi-chart-box-outline"></i> Terapkan</button>
                </div>
            </form>
        </section>

        <div class="pa-state pa-loading" id="paLoading" role="status">
            <span></span><div><b>Menghitung profitabilitas...</b><small>Menyatukan penjualan, retur, HPP batch, dan nilai persediaan.</small></div>
        </div>
        <div class="pa-state pa-error" id="paError" hidden>
            <i class="mdi mdi-alert-circle-outline"></i><div><b>Analisis belum dapat dimuat</b><small id="paErrorCopy">Silakan coba kembali.</small></div>
            <button type="button" id="paRetry">Coba lagi</button>
        </div>

        <section class="pa-kpi-grid" id="paKpiGrid" aria-label="Ringkasan profitabilitas">
            <article class="pa-kpi is-navy"><span><i class="mdi mdi-cash-check"></i></span><small>Penjualan neto</small><strong id="paNetSales">Rp 0</strong><p>Penjualan completed setelah retur posted.</p></article>
            <article class="pa-kpi is-blue"><span><i class="mdi mdi-package-variant-closed"></i></span><small>HPP neto</small><strong id="paNetHpp">Rp 0</strong><p>Biaya batch terjual setelah HPP retur.</p></article>
            <article class="pa-kpi is-green"><span><i class="mdi mdi-finance"></i></span><small>Laba kotor</small><strong id="paGrossProfit">Rp 0</strong><p id="paProfitabilityCopy">Belum ada produk terukur.</p></article>
            <article class="pa-kpi is-teal"><span><i class="mdi mdi-percent-outline"></i></span><small>Margin kotor</small><strong id="paGrossMargin">—</strong><p>Laba kotor dibanding penjualan neto.</p></article>
            <article class="pa-kpi is-violet"><span><i class="mdi mdi-warehouse"></i></span><small>Rata-rata modal stok</small><strong id="paAverageInventory">Rp 0</strong><p>Rata-rata nilai stok awal dan akhir.</p></article>
            <article class="pa-kpi is-orange"><span><i class="mdi mdi-chart-donut-variant"></i></span><small>GMROI periode</small><strong id="paGmroi">—</strong><p id="paGmroiCopy">Menunggu nilai investasi persediaan.</p></article>
        </section>

        <section class="pa-panel pa-product-panel" id="paProductPanel">
            <div class="pa-panel-heading">
                <div><span class="pa-heading-icon is-blue"><i class="mdi mdi-pill-multiple"></i></span><div><small>DATASET UTAMA</small><h2>Profit per Produk</h2><p>Margin, GMROI, dan Top Profit di bawah berasal dari tabel yang sama.</p></div></div>
                <div class="pa-panel-actions"><span id="paProductCount">0 produk</span><button type="button" id="paExportCsv"><i class="mdi mdi-download-outline"></i> CSV</button></div>
            </div>
            <div class="pa-table-wrap">
                <table class="table pa-table">
                    <thead><tr>
                        <th>Peringkat</th><th>Produk</th><th class="text-end">Qty neto</th><th class="text-end">Penjualan neto</th>
                        <th class="text-end">HPP neto</th><th class="text-end">Laba kotor</th><th class="text-end">Margin</th>
                        <th class="text-end">Rata-rata modal stok</th><th class="text-end">GMROI</th>
                    </tr></thead>
                    <tbody id="paProductRows"><tr><td colspan="9" class="pa-empty">Menunggu hasil analisis.</td></tr></tbody>
                </table>
            </div>
            <div class="pa-table-note"><i class="mdi mdi-information-outline"></i><span id="paTableNote">HPP menggunakan harga beli yang tersimpan pada batch saat transaksi terjadi.</span></div>
        </section>

        <div class="pa-insight-grid">
            <section class="pa-panel" id="paMarginPanel">
                <div class="pa-panel-heading">
                    <div><span class="pa-heading-icon is-teal"><i class="mdi mdi-percent-outline"></i></span><div><small>REALIZED MARGIN</small><h2>Margin Produk</h2><p>Gross margin aktual, berbeda dari markup pengaturan harga.</p></div></div>
                </div>
                <div class="pa-ranking-list" id="paMarginRows"><div class="pa-empty">Menunggu data margin.</div></div>
            </section>

            <section class="pa-panel" id="paGmroiPanel">
                <div class="pa-panel-heading">
                    <div><span class="pa-heading-icon is-violet"><i class="mdi mdi-chart-donut-variant"></i></span><div><small>CAPITAL PRODUCTIVITY</small><h2>GMROI Produk</h2><p>Laba kotor per rupiah rata-rata modal persediaan.</p></div></div>
                </div>
                <div class="pa-ranking-list" id="paGmroiRows"><div class="pa-empty">Menunggu data GMROI.</div></div>
            </section>
        </div>

        <section class="pa-panel pa-top-panel" id="paTopProfitPanel">
            <div class="pa-panel-heading">
                <div><span class="pa-heading-icon is-orange"><i class="mdi mdi-trophy-outline"></i></span><div><small>TOP CONTRIBUTORS</small><h2>Top Profit Product</h2><p>Preset Top-N dari Profit per Produk; tidak menjalankan kalkulasi terpisah.</p></div></div>
                <span class="pa-top-badge"><i class="mdi mdi-star-four-points"></i> Berdasarkan laba kotor</span>
            </div>
            <div class="pa-top-grid" id="paTopProfitRows"><div class="pa-empty">Menunggu produk dengan laba tertinggi.</div></div>
        </section>

        <section class="pa-method">
            <div><i class="mdi mdi-calculator-variant-outline"></i><span><b>Rumus yang digunakan</b><small>Laba kotor = penjualan neto − HPP neto. Margin = laba kotor ÷ penjualan neto. GMROI periode = laba kotor ÷ rata-rata nilai persediaan pada harga beli.</small></span></div>
            <p id="paInventoryBasis">Nilai persediaan dibaca dari saldo historis setiap batch pada kartu stok.</p>
        </section>

        <div class="pa-toast" id="paToast" role="status" aria-live="polite"></div>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.analisisProfitabilitas.partials.script")
@endpush
