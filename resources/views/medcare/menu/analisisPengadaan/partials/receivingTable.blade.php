@php
    $cancelled = $kind === 'cancelled';
    $received = $kind === 'received';
    $qtyLabel = $cancelled ? 'Qty order' : ($received ? 'Qty diterima' : 'Qty belum diterima');
    $valueLabel = $cancelled ? 'Nilai PO' : ($received ? 'Nilai diterima' : 'Nilai outstanding');
@endphp
<details class="pa-panel pa-receiving-section" data-receiving-table="{{ $kind }}" open>
    <summary class="pa-panel-head">
        <div class="pa-heading"><span class="{{ $tone }}"><i class="mdi {{ $icon }}"></i></span><div><h2>{{ $title }}</h2><p>{{ $description }}</p></div></div>
        <span class="pa-section-actions"><span class="pa-soft-badge" id="{{ $prefix }}Count">0 {{ $cancelled ? 'PO' : 'item' }}</span><i class="mdi mdi-chevron-down pa-section-chevron" aria-hidden="true"></i></span>
    </summary>
    <form class="pa-receiving-table-filter" data-table-filter="{{ $kind }}" aria-label="Filter {{ $title }}">
        <p class="pa-table-filter-note">Filter khusus tabel ini dalam cakupan Filter Analisis dan Filter Order vs Penerimaan.</p>
        <label class="pa-table-search"><span><i class="mdi mdi-magnify"></i> {{ $cancelled ? 'Cari nomor PO / supplier' : 'Cari PO / nama / kode barang' }}</span><input type="search" name="search" placeholder="{{ $cancelled ? 'Cari nomor PO atau supplier' : 'Cari barang atau nomor PO' }}" maxlength="150" aria-label="{{ $cancelled ? 'Cari nomor PO atau supplier' : 'Cari barang atau nomor PO' }}" enterkeyhint="search"></label>
        <details class="pa-table-filter-details" data-table-filter-details open>
            <summary><span><i class="mdi mdi-tune-variant" aria-hidden="true"></i> Filter & urutan</span><span class="pa-badge" data-table-active-count hidden></span><i class="mdi mdi-chevron-down pa-section-chevron" aria-hidden="true"></i></summary>
        <div class="pa-presets" role="group" aria-label="Periode cepat {{ $title }}">
            <span><i class="mdi mdi-calendar-clock"></i><b>Periode cepat</b></span>
            <button type="button" class="is-active" data-table-range="scope" aria-pressed="true">Periode Analisis</button>
            <button type="button" data-table-range="today" aria-pressed="false">Hari Ini</button>
            <button type="button" data-table-range="7" aria-pressed="false">7 Hari</button>
            <button type="button" data-table-range="30" aria-pressed="false">30 Hari</button>
            <button type="button" data-table-range="mtd" aria-pressed="false">Bulan Ini</button>
            <button type="button" data-table-range="ytd" aria-pressed="false">Tahun Ini</button>
            <button type="button" data-table-range="custom" aria-pressed="false"><i class="mdi mdi-calendar-edit"></i> Custom</button>
        </div>
        <div class="pa-filter-grid pa-table-filter-grid">
            <label><span>Tanggal PO mulai</span><input type="date" name="date_start"></label>
            <label><span>Tanggal PO akhir</span><input type="date" name="date_end"></label>
            <label><span>Supplier</span><select name="supplier"><option value="">Semua supplier</option></select></label>
            @if (!$cancelled)
                <label><span>Barang</span><select name="medicine"><option value="">Semua barang</option></select></label>
                <label><span>Status penerimaan</span><select name="receipt_status"><option value="">Semua penerimaan</option>@if ($received)<option value="complete">Diterima lengkap</option>@else<option value="none">Belum diterima sama sekali</option>@endif<option value="partial">Diterima sebagian</option></select></label>
                @if (!$received)
                    <label><span>Status PO</span><select name="status"><option value="">Semua status PO</option></select></label>
                @endif
            @endif
            <div class="pa-table-numeric-filters"><div class="pa-filter-grid">
            @if ($cancelled)
                <label><span>Jumlah item minimum</span><input type="number" inputmode="numeric" name="item_min" min="0" step="1" placeholder="Tanpa batas"></label>
                <label><span>Jumlah item maksimum</span><input type="number" inputmode="numeric" name="item_max" min="0" step="1" placeholder="Tanpa batas"></label>
            @endif
            <label><span>{{ $qtyLabel }} minimum</span><input type="number" inputmode="decimal" name="qty_min" min="0" step="any" placeholder="Tanpa batas"></label>
            <label><span>{{ $qtyLabel }} maksimum</span><input type="number" inputmode="decimal" name="qty_max" min="0" step="any" placeholder="Tanpa batas"></label>
            <label><span>{{ $valueLabel }} minimum (Rp)</span><input type="number" inputmode="decimal" name="value_min" min="0" step="any" placeholder="Tanpa batas"></label>
            <label><span>{{ $valueLabel }} maksimum (Rp)</span><input type="number" inputmode="decimal" name="value_max" min="0" step="any" placeholder="Tanpa batas"></label>
            </div></div>
            <label><span>Urutkan</span><select name="sort"><option value="default">Urutan bawaan</option><option value="date:desc">Tanggal terbaru</option><option value="date:asc">Tanggal terlama</option><option value="no_po:asc">Nomor PO A–Z</option><option value="supplier:asc">Supplier A–Z</option>@if (!$cancelled)<option value="medicine:asc">Barang A–Z</option>@else<option value="item_count:desc">Jumlah item terbanyak</option>@endif<option value="qty:desc">{{ $qtyLabel }} terbesar</option><option value="qty:asc">{{ $qtyLabel }} terkecil</option><option value="value:desc">{{ $valueLabel }} terbesar</option><option value="value:asc">{{ $valueLabel }} terkecil</option></select></label>
        </div>
        </details>
        <div class="pa-filter-footer">
            <div class="pa-active-filters"><i class="mdi mdi-filter-variant"></i><span data-table-summary aria-live="polite">Memuat data...</span></div>
            <div><button type="button" class="pa-btn pa-btn-ghost" data-table-reset><i class="mdi mdi-backup-restore"></i> Reset tabel</button><button type="submit" class="pa-btn pa-btn-primary"><i class="mdi mdi-filter-check"></i> Terapkan Filter</button></div>
        </div>
        <p class="pa-table-filter-error" data-table-error role="alert" hidden></p>
    </form>
    <div class="table-responsive pa-receiving-table-wrap" tabindex="0" role="region" aria-label="Tabel {{ $title }}">
        <table class="table pa-table pa-receiving-table {{ $cancelled ? 'pa-table-compact pa-cancelled-table' : '' }}" role="table" aria-label="{{ $title }}">
            <thead role="rowgroup"><tr role="row">
                @if ($cancelled)
                    <th role="columnheader" scope="col">No. PO</th><th role="columnheader" scope="col">Tanggal</th><th role="columnheader" scope="col">Supplier</th><th role="columnheader" scope="col" class="text-end">Item</th><th role="columnheader" scope="col" class="text-end">Qty</th><th role="columnheader" scope="col" class="text-end">Nilai</th><th role="columnheader" scope="col">Status</th>
                @else
                    <th role="columnheader" scope="col">PO / Tanggal</th><th role="columnheader" scope="col">Barang</th><th role="columnheader" scope="col">Supplier</th><th role="columnheader" scope="col" class="text-end">Dipesan</th><th role="columnheader" scope="col" class="text-end">Diterima</th><th role="columnheader" scope="col" class="text-end">Belum diterima</th><th role="columnheader" scope="col" class="text-end">{{ $valueLabel }}</th><th role="columnheader" scope="col">{{ $received ? 'Penerimaan' : 'Status' }}</th>
                @endif
            </tr></thead>
            <tbody role="rowgroup" id="{{ $prefix }}Body"></tbody>
        </table>
    </div>
    <div class="pa-table-pagination">
        <label>Baris per halaman <select data-table-page-size aria-label="Baris per halaman {{ $title }}"><option value="5">5</option><option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option><option value="all">Semua</option></select></label>
        <span data-table-page-info aria-live="polite">0 data</span>
        <div><button type="button" class="pa-icon-btn" data-table-page="-1" aria-label="Halaman sebelumnya {{ $title }}" disabled><i class="mdi mdi-chevron-left" aria-hidden="true"></i></button><button type="button" class="pa-icon-btn" data-table-page="1" aria-label="Halaman berikutnya {{ $title }}" disabled><i class="mdi mdi-chevron-right" aria-hidden="true"></i></button></div>
    </div>
</details>
