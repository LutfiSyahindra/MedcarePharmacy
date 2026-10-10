@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.Obat.partials.style")
    @include("medcare.masterData.konversi.style")
@endpush

@section("content")
    <div class="obat-page konversi-page">
        @include("medcare.masterData.konversi.modalMain")
        @include("medcare.masterData.konversi.modalBatch")
        @include("medcare.masterData.konversi.modalExcell")

        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Master Data</li>
                <li class="breadcrumb-item active" aria-current="page">Konversi Satuan</li>
            </ol>
        </nav>

        <section class="konversi-hero" aria-labelledby="konversiPageTitle">
            <div class="konversi-hero-copy">
                <span class="konversi-eyebrow"><span></span> MANAJEMEN SATUAN OBAT</span>
                <h1 id="konversiPageTitle">Satuan tepat.<br><span>Transaksi lebih mudah.</span></h1>
                <p>Atur hubungan satuan pembelian dan stok obat dalam satu tempat. Kelola per obat atau terapkan sekaligus melalui batch.</p>
                <div class="konversi-hero-actions">
                    <button type="button" class="btn konversi-btn-white konversi-open-batch">
                        <i class="mdi mdi-playlist-plus" aria-hidden="true"></i>Atur Konversi Batch
                    </button>
                    <button type="button" class="btn konversi-btn-glass" data-bs-toggle="modal" data-bs-target="#konversiModalExcell">
                        <i class="mdi mdi-file-excel-outline" aria-hidden="true"></i>Import Excel
                    </button>
                </div>
            </div>
            <div class="konversi-equation" aria-label="Contoh konversi: 1 Box sama dengan 10 Strip, sama dengan 100 Tablet">
                <div class="konversi-equation-heading"><i class="mdi mdi-swap-horizontal-bold" aria-hidden="true"></i><span>Satu kemasan, banyak satuan</span><small>CONTOH</small></div>
                <div class="konversi-equation-units">
                    <div><i class="mdi mdi-package-variant-closed" aria-hidden="true"></i><strong>1 <span>Box</span></strong><small>Satuan pembelian</small></div>
                    <span class="konversi-equation-equals" aria-hidden="true">=</span>
                    <div><i class="mdi mdi-view-week-outline" aria-hidden="true"></i><strong>10 <span>Strip</span></strong><small>Kemasan kecil</small></div>
                    <span class="konversi-equation-equals" aria-hidden="true">=</span>
                    <div><i class="mdi mdi-pill" aria-hidden="true"></i><strong>100 <span>Tablet</span></strong><small>Satuan stok</small></div>
                </div>
                <div class="konversi-equation-foot"><i class="mdi mdi-information-outline" aria-hidden="true"></i>Isi konversi mengikuti kemasan masing-masing obat.</div>
            </div>
        </section>

        <div class="obat-stats-grid konversi-stats" aria-label="Ringkasan konversi satuan">
            <div class="obat-stat konversi-stat-total">
                <span class="obat-stat-icon"><i class="mdi mdi-pill" aria-hidden="true"></i></span>
                <div><span>Total obat</span><strong id="konversiTotalObat">0</strong><small>Dalam master data</small></div>
            </div>
            <div class="obat-stat konversi-stat-ready">
                <span class="obat-stat-icon"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i></span>
                <div><span>Sudah diatur</span><strong id="konversiWith">0</strong><small>Memiliki konversi</small></div>
            </div>
            <div class="obat-stat konversi-stat-pending">
                <span class="obat-stat-icon"><i class="mdi mdi-clock-outline" aria-hidden="true"></i></span>
                <div><span>Belum diatur</span><strong id="konversiWithout">0</strong><small>Perlu konversi satuan</small></div>
            </div>
            <div class="obat-stat konversi-stat-po">
                <span class="obat-stat-icon"><i class="mdi mdi-cart-outline" aria-hidden="true"></i></span>
                <div><span>PO belum lengkap</span><strong id="konversiPoWithout">0</strong><small>Satuan perlu dilengkapi</small></div>
            </div>
            <div class="obat-stat konversi-stat-filtered">
                <span class="obat-stat-icon"><i class="mdi mdi-filter-outline" aria-hidden="true"></i></span>
                <div><span>Hasil filter</span><strong id="konversiFiltered">0</strong><small>Sesuai pencarian aktif</small></div>
            </div>
        </div>

        <section class="obat-table-section konversi-table-section" aria-labelledby="konversiTableTitle">
            <div class="konversi-table-heading">
                <div class="obat-table-title">
                    <span class="obat-table-title-icon"><i class="mdi mdi-swap-horizontal-bold" aria-hidden="true"></i></span>
                    <div><h2 id="konversiTableTitle">Daftar konversi satuan</h2><p>Kelola satuan pembelian untuk setiap obat.</p></div>
                </div>
                <button type="button" class="btn konversi-refresh obat-refresh-table" aria-label="Muat ulang data konversi" title="Muat ulang data">
                    <i class="mdi mdi-refresh" aria-hidden="true"></i><span>Muat ulang</span>
                </button>
            </div>
            <div class="konversi-table-toolbar">
                <div class="konversi-status-filter" role="group" aria-label="Filter status konversi">
                    <button type="button" class="btn is-active" data-status="all" aria-pressed="true">Semua</button>
                    <button type="button" class="btn" data-status="with" aria-pressed="false"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i>Sudah diatur</button>
                    <button type="button" class="btn" data-status="without" aria-pressed="false"><i class="mdi mdi-clock-outline" aria-hidden="true"></i>Belum diatur</button>
                    <button type="button" class="btn" data-status="po_without" aria-pressed="false"><i class="mdi mdi-cart-outline" aria-hidden="true"></i>PO belum lengkap</button>
                </div>
                <label class="obat-search" for="searchKonversi">
                    <i class="mdi mdi-magnify" aria-hidden="true"></i>
                    <span class="visually-hidden">Cari obat, kode, satuan, atau nomor PO</span>
                    <input type="search" id="searchKonversi" placeholder="Cari obat, kode, satuan, atau PO…" autocomplete="off">
                </label>
            </div>
            <div class="table-responsive">
                <table id="tableKonversi" class="table obat-table align-middle">
                    <thead><tr><th scope="col">No</th><th scope="col">Obat</th><th scope="col">Satuan stok</th><th scope="col">Status</th><th scope="col">Konversi tersedia</th><th scope="col">Aksi</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.masterData.Obat.partials.scripts")
    @include("medcare.masterData.konversi.jsMain")
@endpush
