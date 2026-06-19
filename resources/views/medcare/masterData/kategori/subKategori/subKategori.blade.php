@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.kategori.partials.style")
@endpush

@section("content")
    <div class="category-page">
        @include("medcare.masterData.kategori.subKategori.modalMain")
        @include("medcare.masterData.kategori.subKategori.modalExcell")
        @include("medcare.masterData.kategori.partials.header", [
            "categoryActive" => "sub",
            "categoryTitle" => "Sub Kategori",
            "categoryDescription" => "Kelola detail klasifikasi obat agar pemetaan master obat lebih presisi dan mudah dicari.",
            "categoryModalTarget" => "#subCategoryModal",
            "categoryActionLabel" => "Tambah Sub Kategori",
            "categoryActionIcon" => "mdi-plus-circle-outline",
            "categoryImportTarget" => "#subCategoryModalExcell",
            "categoryImportLabel" => "Import Sub Kategori",
        ])

        <div class="category-stats-grid">
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="subKategoriTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh sub kategori yang tersimpan.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="subKategoriFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="subKategoriSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="category-table-section">
            <div class="category-table-toolbar">
                <div class="category-table-title">
                    <span class="category-table-title-icon"><i class="mdi mdi-format-list-bulleted-type"></i></span>
                    <div>
                        <h5>Daftar Sub Kategori</h5>
                        <p>Detail klasifikasi yang dipakai pada master obat.</p>
                    </div>
                </div>
                <div class="category-table-tools">
                    <label class="category-search" for="subKategoriSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="subKategoriSearch" placeholder="Cari main kategori, kode, atau nama...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableSubKategori" class="table category-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Main Kategori</th>
                            <th>Kode</th>
                            <th>Sub Kategori</th>
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
    @include("medcare.masterData.kategori.subKategori.jsMain")
@endpush
