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
        @include("medcare.masterData.kategori.mainKategori.modalMain")
        @include("medcare.masterData.kategori.mainKategori.modalExcell")
        @include("medcare.masterData.kategori.partials.header", [
            "categoryActive" => "main",
            "categoryTitle" => "Main Kategori",
            "categoryDescription" => "Hubungkan kategori utama dengan kelompok obat yang lebih spesifik dan siap dipakai di master obat.",
            "categoryModalTarget" => "#mainCategoryModal",
            "categoryActionLabel" => "Tambah Main Kategori",
            "categoryActionIcon" => "mdi-plus-circle-outline",
            "categoryImportTarget" => "#mainCategoryModalExcell",
            "categoryImportLabel" => "Import Main Kategori",
        ])

        <div class="category-stats-grid">
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="mainKategoriTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh main kategori yang tersimpan.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="mainKategoriFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="mainKategoriSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="category-table-section">
            <div class="category-table-toolbar">
                <div class="category-table-title">
                    <span class="category-table-title-icon"><i class="mdi mdi-shape-outline"></i></span>
                    <div>
                        <h5>Daftar Main Kategori</h5>
                        <p>Turunan kategori utama untuk memperjelas kelompok obat.</p>
                    </div>
                </div>
                <div class="category-table-tools">
                    <label class="category-search" for="mainKategoriSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="mainKategoriSearch" placeholder="Cari kategori utama, kode, atau nama...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableMainKategori" class="table category-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori Utama</th>
                            <th>Kode</th>
                            <th>Main Kategori</th>
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
    @include("medcare.masterData.kategori.mainKategori.jsMain")
@endpush
