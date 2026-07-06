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
        @include("medcare.masterData.golongan.mainGolongan.modalMain")
        @include("medcare.masterData.golongan.mainGolongan.modalExcell")
        @include("medcare.masterData.golongan.partials.hierarchy-header", [
            "golonganActive" => "main",
            "golonganTitle" => "Main Golongan",
            "golonganDescription" => "Hubungkan golongan obat dengan kelompok turunannya agar master obat bisa dipetakan lebih presisi.",
            "golonganModalTarget" => "#mainGolonganModal",
            "golonganActionLabel" => "Tambah Main Golongan",
            "golonganImportTarget" => "#mainGolonganModalExcell",
            "golonganImportLabel" => "Import Main Golongan",
        ])

        <div class="category-stats-grid">
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="mainGolonganTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh main golongan yang tersimpan.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="mainGolonganFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="category-stat">
                <span class="category-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="mainGolonganSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="category-table-section">
            <div class="category-table-toolbar">
                <div class="category-table-title">
                    <span class="category-table-title-icon"><i class="mdi mdi-shape-plus-outline"></i></span>
                    <div>
                        <h5>Daftar Main Golongan</h5>
                        <p>Turunan golongan obat yang dipakai pada master obat.</p>
                    </div>
                </div>
                <div class="category-table-tools">
                    <label class="category-search" for="mainGolonganSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="mainGolonganSearch" placeholder="Cari golongan, kode, atau nama...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableMainGolongan" class="table category-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Golongan</th>
                            <th>Kode</th>
                            <th>Main Golongan</th>
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
    @include("medcare.masterData.golongan.mainGolongan.jsMain")
@endpush
