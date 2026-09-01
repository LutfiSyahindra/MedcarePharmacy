@extends('template.partials.app')

@push('style')
    @include('template.AddOn.mdiicon')
    @include('medcare.menu.analisisPengadaan.partials.style')
@endpush

@section('content')
    <main class="pa-page" id="procurementAnalysisApp"
        data-url="{{ route('analisisPengadaan.data') }}"
        data-medicine-url="{{ route('analisisPengadaan.medicine', ['medicine' => '__MEDICINE__']) }}"
        data-default-start="{{ $defaultDateStart }}"
        data-default-end="{{ $defaultDateEnd }}">
        <nav class="page-breadcrumb pa-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i> Dashboard</a></li>
                <li class="breadcrumb-item">Analisis</li>
                <li class="breadcrumb-item active" aria-current="page">Pengadaan</li>
            </ol>
        </nav>

        <section class="pa-hero">
            <span class="pa-hero-orb pa-orb-one"></span><span class="pa-hero-orb pa-orb-two"></span>
            <div class="pa-hero-copy">
                <div class="pa-brandline">
                    <span class="pa-brandmark"><i class="mdi mdi-cart-heart"></i></span>
                    <span class="pa-eyebrow">Procurement Intelligence</span>
                    <span class="pa-live"><i></i> Data operasional</span>
                </div>
                <h1>Analisis Pengadaan</h1>
                <p>Ukur efektivitas pembelian, ketepatan kebutuhan, performa supplier, dan penyelesaian penerimaan dalam satu workspace.</p>
                <div class="pa-hero-points">
                    <span><i class="mdi mdi-scale-balance"></i> Order vs kebutuhan</span>
                    <span><i class="mdi mdi-truck-check-outline"></i> Fulfillment supplier</span>
                    <span><i class="mdi mdi-chart-timeline-variant"></i> Tren harga & lead time</span>
                </div>
            </div>
            <div class="pa-hero-context">
                <div><span><i class="mdi mdi-source-branch"></i></span><small>Cakupan</small><strong id="paHeroBranch">Menyiapkan...</strong></div>
                <div><span><i class="mdi mdi-calendar-range"></i></span><small>Periode aktif</small><strong id="paHeroPeriod">Menyiapkan...</strong></div>
                <button type="button" id="paRefresh"><i class="mdi mdi-refresh"></i> Perbarui data</button>
            </div>
        </section>

        <nav class="pa-tabs" id="paTabs" aria-label="Bagian analisis pengadaan">
            <button type="button" class="is-active" data-tab="summary"><i class="mdi mdi-view-dashboard-outline"></i><span>Ringkasan</span></button>
            <button type="button" data-tab="medicines"><i class="mdi mdi-pill-multiple"></i><span>Analisis Barang</span></button>
            <button type="button" data-tab="suppliers"><i class="mdi mdi-truck-outline"></i><span>Analisis Supplier</span></button>
            <button type="button" data-tab="receipts"><i class="mdi mdi-package-variant-closed-check"></i><span>Order vs Penerimaan</span></button>
            <button type="button" data-tab="sales"><i class="mdi mdi-swap-horizontal-bold"></i><span>Order vs Penjualan</span></button>
        </nav>

        <section class="pa-panel pa-filter-panel" id="paFilterPanel">
            <div class="pa-panel-head pa-filter-head">
                <div class="pa-heading"><span class="is-navy"><i class="mdi mdi-tune-variant"></i></span><div><small>Smart controls</small><h2>Filter Analisis</h2><p>Semua KPI, grafik, ranking, dan tabel mengikuti parameter ini.</p></div></div>
                <div class="pa-filter-head-actions">
                    <span class="pa-filter-status" id="paFilterStatus"><i class="mdi mdi-check-decagram-outline"></i> Siap digunakan</span>
                    <button type="button" class="pa-icon-btn" id="paFilterToggle" aria-expanded="true" title="Ringkas filter"><i class="mdi mdi-chevron-up"></i></button>
                </div>
            </div>
            <div class="pa-filter-body" id="paFilterBody">
                <div class="pa-presets" role="group" aria-label="Periode cepat">
                    <span><i class="mdi mdi-calendar-clock"></i><b>Periode cepat</b></span>
                    <button type="button" data-range="today">Hari Ini</button>
                    <button type="button" data-range="7">7 Hari</button>
                    <button type="button" class="is-active" data-range="30">30 Hari</button>
                    <button type="button" data-range="mtd">Bulan Ini</button>
                    <button type="button" data-range="ytd">Tahun Ini</button>
                    <button type="button" data-range="custom"><i class="mdi mdi-calendar-edit"></i> Custom</button>
                </div>
                <form id="paFilterForm">
                    <div class="pa-filter-grid">
                        <label><span><i class="mdi mdi-source-branch"></i> Cabang</span><select name="branch_id" id="paBranch"><option value="">Semua cabang</option></select></label>
                        <label><span><i class="mdi mdi-calendar-start"></i> Tanggal mulai</span><input type="date" name="date_start" id="paDateStart" value="{{ $defaultDateStart }}"></label>
                        <label><span><i class="mdi mdi-calendar-end"></i> Tanggal akhir</span><input type="date" name="date_end" id="paDateEnd" value="{{ $defaultDateEnd }}"></label>
                        <label><span><i class="mdi mdi-truck-outline"></i> Supplier</span><select name="supplier_id" id="paSupplier"><option value="">Semua supplier</option></select></label>
                        <label><span><i class="mdi mdi-pill"></i> Obat</span><select name="medicine_id" id="paMedicine"><option value="">Semua obat</option></select></label>
                        <label><span><i class="mdi mdi-shape-outline"></i> Kategori</span><select name="category_id" id="paCategory"><option value="">Semua kategori</option></select></label>
                        <label><span><i class="mdi mdi-format-list-bulleted-type"></i> Golongan</span><select name="golongan_id" id="paClassification"><option value="">Semua golongan</option></select></label>
                        <label><span><i class="mdi mdi-factory"></i> Pabrikan</span><select name="manufacturer_id" id="paManufacturer"><option value="">Semua pabrikan</option></select></label>
                        <label><span><i class="mdi mdi-list-status"></i> Status PO</span><select name="po_status" id="paPoStatus"><option value="">Semua status PO</option></select></label>
                        <label><span><i class="mdi mdi-package-check"></i> Status penerimaan</span><select name="receipt_status" id="paReceiptStatus"><option value="">Semua penerimaan</option></select></label>
                        <label><span><i class="mdi mdi-account-edit-outline"></i> User pembuat</span><select name="created_by" id="paCreator"><option value="">Semua pembuat</option></select></label>
                        <label><span><i class="mdi mdi-chart-timeline-variant"></i> Agregasi tren</span><select name="granularity" id="paGranularity"><option value="day">Harian</option><option value="week">Mingguan</option><option value="month">Bulanan</option><option value="year">Tahunan</option></select></label>
                    </div>
                    <div class="pa-filter-footer">
                        <div class="pa-active-filters"><i class="mdi mdi-filter-variant"></i><span id="paActiveFilters">Tanpa filter tambahan</span></div>
                        <div><button type="button" class="pa-btn pa-btn-ghost" id="paReset"><i class="mdi mdi-backup-restore"></i> Reset</button><button type="submit" class="pa-btn pa-btn-primary" id="paApply"><i class="mdi mdi-filter-check"></i> Terapkan Filter</button></div>
                    </div>
                </form>
            </div>
        </section>

        <section class="pa-tab-panel is-active" data-tab-panel="summary">
            <div class="pa-section-label"><div><span>Executive overview</span><h2>Kinerja pengadaan periode aktif</h2></div><p><i class="mdi mdi-clock-check-outline"></i> Diperbarui <b id="paGeneratedAt">&mdash;</b></p></div>
            <div class="pa-kpi-grid" id="paKpiGrid" aria-live="polite">
                @for ($i = 0; $i < 6; $i++)<article class="pa-kpi is-loading"><span></span><div><small>Memuat data</small><strong>&nbsp;</strong><p>&nbsp;</p></div></article>@endfor
            </div>

            <div class="pa-grid pa-grid-wide">
                <article class="pa-panel pa-chart-panel">
                    <div class="pa-panel-head"><div class="pa-heading"><span class="is-blue"><i class="mdi mdi-chart-areaspline"></i></span><div><h2>Trend Nilai Order</h2><p>Nilai PO aktif dibanding periode sebelumnya.</p></div></div><div class="pa-legend"><span><i class="current"></i>Aktif</span><span><i class="previous"></i>Sebelumnya</span></div></div>
                    <div class="pa-chart pa-chart-lg" id="paTrendChart"><div class="pa-loading"><i class="mdi mdi-loading mdi-spin"></i> Menyusun tren...</div></div>
                </article>
                <article class="pa-panel pa-chart-panel">
                    <div class="pa-panel-head"><div class="pa-heading"><span class="is-violet"><i class="mdi mdi-format-list-numbered"></i></span><div><h2>Top 10 Barang Diorder</h2><p>Ranking menurut total qty satuan stok.</p></div></div></div>
                    <div class="pa-chart pa-chart-lg" id="paTopChart"></div>
                </article>
            </div>

            <div class="pa-grid pa-grid-four">
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-teal"><i class="mdi mdi-truck-outline"></i></span><div><h2>Order per Supplier</h2><p>Kontribusi nilai PO.</p></div></div></div><div class="pa-chart" id="paSupplierChart"></div></article>
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-violet"><i class="mdi mdi-chart-donut"></i></span><div><h2>Order per Kategori</h2><p>Distribusi nilai pembelian.</p></div></div></div><div class="pa-chart" id="paCategoryChart"></div></article>
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-green"><i class="mdi mdi-package-variant-closed-check"></i></span><div><h2>Order vs Penerimaan</h2><p>Qty dipesan dan diterima.</p></div></div></div><div class="pa-chart" id="paReceiptChart"></div></article>
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-amber"><i class="mdi mdi-swap-horizontal"></i></span><div><h2>Order vs Penjualan</h2><p>Tren qty beli dan jual.</p></div></div></div><div class="pa-chart" id="paSalesChart"></div></article>
            </div>

            <div class="pa-grid pa-grid-two">
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-cyan"><i class="mdi mdi-clock-fast"></i></span><div><h2>Lead Time Supplier</h2><p>Supplier tercepat berdasarkan penerimaan pertama.</p></div></div></div><div class="pa-chart" id="paLeadChart"></div></article>
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-rose"><i class="mdi mdi-list-status"></i></span><div><h2>Distribusi Status PO</h2><p>Draft hingga selesai atau dibatalkan.</p></div></div></div><div class="pa-split"><div class="pa-chart" id="paStatusChart"></div><div class="pa-mini-list" id="paStatusList"></div></div></article>
            </div>

            <article class="pa-panel pa-insights"><div class="pa-panel-head"><div class="pa-heading"><span class="is-amber"><i class="mdi mdi-lightbulb-on-outline"></i></span><div><h2>Insight Pengadaan</h2><p>Prioritas otomatis dari data periode aktif.</p></div></div></div><div class="pa-insight-grid" id="paInsights"></div></article>
        </section>

        <section class="pa-tab-panel" data-tab-panel="medicines">
            <div class="pa-section-label"><div><span>Item intelligence</span><h2>Analisis barang yang diorder</h2></div><p>Klik nama obat untuk melihat riwayat lengkap.</p></div>
            <div class="pa-grid pa-grid-three pa-ranking-grid">
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-blue"><i class="mdi mdi-repeat"></i></span><div><h2>Paling Sering Diorder</h2><p>Frekuensi PO tertinggi.</p></div></div></div><div class="pa-rank-list" id="paFrequencyList"></div></article>
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-violet"><i class="mdi mdi-package-variant"></i></span><div><h2>Paling Banyak Diorder</h2><p>Total qty tertinggi.</p></div></div></div><div class="pa-rank-list" id="paQuantityList"></div></article>
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-green"><i class="mdi mdi-cash-multiple"></i></span><div><h2>Nilai Order Terbesar</h2><p>Nilai pembelian tertinggi.</p></div></div></div><div class="pa-rank-list" id="paValueList"></div></article>
            </div>
            <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-navy"><i class="mdi mdi-table-large"></i></span><div><h2>Ringkasan Analisis Barang</h2><p>Frekuensi, qty, nilai, fulfillment, harga, dan pergerakan.</p></div></div><span class="pa-soft-badge" id="paMedicineCount">0 obat</span></div><div class="table-responsive"><table class="table pa-table"><thead><tr><th>Obat</th><th>Supplier utama</th><th class="text-end">Frekuensi</th><th class="text-end">Qty order</th><th class="text-end">Nilai</th><th class="text-end">Diterima</th><th class="text-end">Harga terakhir</th><th class="text-end">Perubahan</th><th>Pergerakan</th></tr></thead><tbody id="paMedicineBody"></tbody></table></div></article>
            <div class="pa-grid pa-grid-two">
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-orange"><i class="mdi mdi-chart-line"></i></span><div><h2>Perubahan Harga Beli</h2><p>Perubahan harga satuan stok dari order pertama ke terakhir.</p></div></div></div><div class="table-responsive"><table class="table pa-table pa-table-compact"><thead><tr><th>Obat</th><th class="text-end">Harga awal</th><th class="text-end">Harga terakhir</th><th class="text-end">Perubahan</th></tr></thead><tbody id="paPriceBody"></tbody></table></div></article>
                <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-cyan"><i class="mdi mdi-repeat-variant"></i></span><div><h2>Frekuensi Reorder</h2><p>Jarak rata-rata pemesanan ulang obat yang sama.</p></div></div></div><div class="table-responsive"><table class="table pa-table pa-table-compact"><thead><tr><th>Obat</th><th class="text-end">Frekuensi</th><th class="text-end">Rata-rata jeda</th><th>Tanggal terakhir</th></tr></thead><tbody id="paReorderBody"></tbody></table></div></article>
            </div>
            <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-violet"><i class="mdi mdi-speedometer"></i></span><div><h2>Slow / Fast Moving Order</h2><p>Apakah nilai pembelian didominasi produk cepat atau lambat bergerak.</p></div></div></div><div class="pa-moving-grid" id="paMovingDistribution"></div></article>
        </section>

        <section class="pa-tab-panel" data-tab-panel="suppliers">
            <div class="pa-section-label"><div><span>Supplier performance</span><h2>Analisis supplier</h2></div><p>Nilai order, fulfillment, outstanding, dan lead time.</p></div>
            <article class="pa-panel"><div class="table-responsive"><table class="table pa-table"><thead><tr><th>Supplier</th><th class="text-end">Jumlah PO</th><th class="text-end">Item</th><th class="text-end">Qty order</th><th class="text-end">Nilai order</th><th class="text-end">Outstanding</th><th class="text-end">Fulfillment</th><th class="text-end">Lead time</th></tr></thead><tbody id="paSupplierBody"></tbody></table></div></article>
            <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-violet"><i class="mdi mdi-shape-outline"></i></span><div><h2>Order per Kategori Obat</h2><p>Distribusi jumlah item, qty, dan nilai berdasarkan kategori.</p></div></div></div><div class="table-responsive"><table class="table pa-table pa-table-compact"><thead><tr><th>Kategori</th><th class="text-end">Jumlah PO</th><th class="text-end">Item</th><th class="text-end">Qty</th><th class="text-end">Nilai</th><th class="text-end">Kontribusi</th></tr></thead><tbody id="paCategoryBody"></tbody></table></div></article>
        </section>

        <section class="pa-tab-panel" data-tab-panel="receipts">
            <div class="pa-section-label"><div><span>Receiving control</span><h2>Order vs penerimaan</h2></div><p>Temukan item yang belum diterima atau baru dipenuhi sebagian.</p></div>
            <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-red"><i class="mdi mdi-alert-circle-outline"></i></span><div><h2>Outstanding Order</h2><p>Qty dan nilai yang masih menunggu penerimaan posted.</p></div></div><span class="pa-soft-badge" id="paOutstandingCount">0 item</span></div><div class="table-responsive"><table class="table pa-table"><thead><tr><th>PO / Tanggal</th><th>Barang</th><th>Supplier</th><th class="text-end">Dipesan</th><th class="text-end">Diterima</th><th class="text-end">Belum diterima</th><th class="text-end">Nilai outstanding</th><th>Status</th></tr></thead><tbody id="paOutstandingBody"></tbody></table></div></article>
            <article class="pa-panel"><div class="pa-panel-head"><div class="pa-heading"><span class="is-rose"><i class="mdi mdi-cancel"></i></span><div><h2>Order Dibatalkan</h2><p>PO berstatus rejected pada periode aktif.</p></div></div><span class="pa-soft-badge" id="paCancelledCount">0 PO</span></div><div class="table-responsive"><table class="table pa-table pa-table-compact"><thead><tr><th>No. PO</th><th>Tanggal</th><th>Supplier</th><th class="text-end">Item</th><th class="text-end">Qty</th><th class="text-end">Nilai</th><th>Status</th></tr></thead><tbody id="paCancelledBody"></tbody></table></div></article>
        </section>

        <section class="pa-tab-panel" data-tab-panel="sales">
            <div class="pa-section-label"><div><span>Demand alignment</span><h2>Order vs kebutuhan</h2></div><p>Indikator pembelian berdasarkan stok saat order terakhir, penjualan 30 hari, dan stok minimum.</p></div>
            <div class="pa-need-legend"><span class="is-optimal"><i></i> Optimal</span><span class="is-evaluation"><i></i> Perlu Evaluasi</span><span class="is-over"><i></i> Over Order</span><span class="is-under"><i></i> Under Order</span></div>
            <article class="pa-panel"><div class="table-responsive"><table class="table pa-table pa-need-table"><thead><tr><th>Obat</th><th class="text-end">Stok saat order</th><th class="text-end">Penjualan 30 hari</th><th class="text-end">Stok minimum</th><th class="text-end">Estimasi kebutuhan</th><th class="text-end">Qty order terakhir</th><th class="text-end">Rasio</th><th>Status</th></tr></thead><tbody id="paNeedBody"></tbody></table></div></article>
        </section>

        <div class="pa-toast" id="paToast" role="status" aria-live="polite" hidden><i class="mdi mdi-check-circle-outline"></i><span></span></div>
    </main>

    <div class="pa-drawer-layer" id="paDrawerLayer" hidden>
        <button type="button" class="pa-drawer-backdrop" id="paDrawerBackdrop" aria-label="Tutup detail"></button>
        <aside class="pa-drawer" id="paMedicineDrawer" aria-labelledby="paDrawerTitle" aria-modal="true" role="dialog">
            <header><div><span>Detail analisis barang</span><h2 id="paDrawerTitle">Memuat...</h2><p id="paDrawerMeta"></p></div><button type="button" id="paDrawerClose" aria-label="Tutup"><i class="mdi mdi-close"></i></button></header>
            <div class="pa-drawer-body" id="paDrawerBody"><div class="pa-loading"><i class="mdi mdi-loading mdi-spin"></i> Menyiapkan detail obat...</div></div>
        </aside>
    </div>
@endsection

@push('scripts')
    @include('medcare.menu.analisisPengadaan.partials.script')
@endpush
