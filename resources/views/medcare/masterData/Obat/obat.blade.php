@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.Obat.partials.style")
@endpush

@section("content")
    <div class="obat-page">
        @include("medcare.masterData.Obat.modalMain")
        @include("medcare.masterData.Obat.modalExcell")
        @include("medcare.masterData.Obat.partials.header")

        <div class="obat-stats-grid">
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="obatTotal">0</strong>
                    <span>Total Obat</span>
                    <small>Seluruh master obat yang tersedia.</small>
                </div>
            </div>
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="obatFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah obat sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="obatSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai obat.</small>
                </div>
            </div>
        </div>

        <section class="obat-table-section">
            <div class="obat-table-toolbar">
                <div class="obat-table-title">
                    <span class="obat-table-title-icon"><i class="mdi mdi-pill"></i></span>
                    <div>
                        <h5>Daftar Master Obat</h5>
                        <p>Kolom utama diringkas, detail lengkap bisa dibuka per baris.</p>
                    </div>
                </div>
                <div class="obat-table-tools">
                    <label class="obat-search" for="obatSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="obatSearch" placeholder="Cari kode, nama, kategori, pabrikan...">
                    </label>
                </div>
            </div>

            <div class="obat-filter-panel" id="obatFilterPanel">
                <div class="obat-filter-heading">
                    <div>
                        <span class="obat-filter-kicker"><i class="mdi mdi-filter-variant"></i> Filter klasifikasi</span>
                        <p>Pilih satu atau beberapa klasifikasi. Angka pada pilihan menunjukkan jumlah obat dalam kelompok tersebut.</p>
                    </div>
                    <div class="obat-filter-status">
                        <span id="obatFilterActiveCount">Belum ada filter aktif</span>
                        <button type="button" class="btn btn-outline-secondary" id="obatFilterReset" disabled>
                            <i class="mdi mdi-filter-remove-outline"></i>
                            Reset Filter
                        </button>
                    </div>
                </div>
                <div class="obat-filter-grid">
                    <label class="obat-filter-field" for="obatCategoryFilter">
                        <span><i class="mdi mdi-folder-outline"></i>Kategori</span>
                        <select id="obatCategoryFilter" class="form-select master-obat-filter" data-placeholder="Semua kategori" disabled>
                            <option value="">Memuat kategori...</option>
                        </select>
                    </label>
                    <label class="obat-filter-field" for="obatGolonganFilter">
                        <span><i class="mdi mdi-flask-outline"></i>Golongan</span>
                        <select id="obatGolonganFilter" class="form-select master-obat-filter" data-placeholder="Semua golongan" disabled>
                            <option value="">Memuat golongan...</option>
                        </select>
                    </label>
                    <label class="obat-filter-field" for="obatMainGolonganFilter">
                        <span><i class="mdi mdi-shape-plus-outline"></i>Main Golongan</span>
                        <select id="obatMainGolonganFilter" class="form-select master-obat-filter" data-placeholder="Semua main golongan" disabled>
                            <option value="">Memuat main golongan...</option>
                        </select>
                    </label>
                    <label class="obat-filter-field" for="obatSubGolonganFilter">
                        <span><i class="mdi mdi-source-branch"></i>Sub Golongan</span>
                        <select id="obatSubGolonganFilter" class="form-select master-obat-filter" data-placeholder="Semua sub golongan" disabled>
                            <option value="">Memuat sub golongan...</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tableObat" class="table obat-table align-middle">
                    <thead>
                        <tr>
                            <th>Detail</th>
                            <th>Aksi</th>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Obat</th>
                            <th>Kategori</th>
                            <th>Stok Minimum</th>
                            <th>Harga Beli</th>
                            <th>Jenis</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.masterData.Obat.partials.scripts")
    @include("medcare.masterData.Obat.jsMain")
@endpush
