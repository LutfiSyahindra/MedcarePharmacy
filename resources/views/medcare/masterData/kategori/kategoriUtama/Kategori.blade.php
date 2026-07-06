@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("medcare.masterData.kategori.partials.style")
@endpush

@section("content")
    <div class="category-page">
        @include("medcare.masterData.kategori.kategoriUtama.modalMain")
        @include("medcare.masterData.kategori.partials.header", [
            "categoryActive" => "utama",
            "categoryTitle" => "Kategori",
            "categoryDescription" => "Kelola kategori obat satu level sebelum dipakai pada master obat.",
            "categoryModalTarget" => "#kategoriUtamaModal",
            "categoryActionLabel" => "Tambah Kategori",
            "categoryActionIcon" => "mdi-plus-circle-outline",
        ])

        <div class="category-stats-grid">
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="kategoriUtamaTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh kategori yang tersimpan.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="kategoriUtamaFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="kategoriUtamaSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="category-table-section">
            <div class="category-table-toolbar">
                <div class="category-table-title">
                    <span class="category-table-title-icon"><i class="mdi mdi-tag-multiple-outline"></i></span>
                    <div>
                        <h5>Daftar Kategori</h5>
                        <p>Basis kategori untuk pengelompokan master obat.</p>
                    </div>
                </div>
                <div class="category-table-tools">
                    <label class="category-search" for="kategoriUtamaSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="kategoriUtamaSearch" placeholder="Cari kode atau nama kategori...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableKategoriUtama" class="table category-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.masterData.kategori.partials.scripts")
    @include("medcare.masterData.kategori.kategoriUtama.jsMain")
@endpush
