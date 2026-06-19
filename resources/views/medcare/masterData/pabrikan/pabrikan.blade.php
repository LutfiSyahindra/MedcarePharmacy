@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.partials.company-style")
@endpush

@section("content")
    <div class="company-page">
        @include("medcare.masterData.pabrikan.modalMain")
        @include("medcare.masterData.pabrikan.modalExcell")
        @include("medcare.masterData.partials.company-header", [
            "companyTitle" => "Pabrikan / Produksi Obat",
            "companyBreadcrumb" => "Pabrikan",
            "companyKicker" => "Manufacturer Master",
            "companyDescription" => "Kelola data pabrikan atau produsen obat lengkap dengan alamat dan kontak agar master obat lebih mudah ditelusuri.",
            "companyIcon" => "mdi-factory",
            "companyModalTarget" => "#pabrikanModal",
            "companyImportTarget" => "#pabrikanModalExcell",
            "companyActionLabel" => "Tambah Pabrikan",
            "companyImportLabel" => "Import Pabrikan",
            "companyFlow" => [
                ["icon" => "mdi-barcode-scan", "title" => "Kode", "subtitle" => "Identitas singkat pabrikan"],
                ["icon" => "mdi-factory", "title" => "Pabrikan", "subtitle" => "Nama dan alamat produksi"],
                ["icon" => "mdi-card-account-phone-outline", "title" => "Kontak", "subtitle" => "Telepon dan email aktif"],
            ],
        ])

        <div class="company-stats-grid">
            <div class="company-stat">
                <span class="company-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="pabrikanTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh pabrikan yang tersimpan.</small>
                </div>
            </div>
            <div class="company-stat">
                <span class="company-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="pabrikanFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="company-stat">
                <span class="company-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="pabrikanSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="company-table-section">
            <div class="company-table-toolbar">
                <div class="company-table-title">
                    <span class="company-table-title-icon"><i class="mdi mdi-factory"></i></span>
                    <div>
                        <h5>Daftar Pabrikan</h5>
                        <p>Kelola profil pabrikan, kontak, dan status aktif.</p>
                    </div>
                </div>
                <div class="company-table-tools">
                    <label class="company-search" for="pabrikanSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="pabrikanSearch" placeholder="Cari kode, nama, alamat, telepon, atau email...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablePabrikan" class="table company-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Pabrikan</th>
                            <th>Alamat</th>
                            <th>Telp</th>
                            <th>Email</th>
                            <th>Status</th>
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
    @include("medcare.masterData.partials.company-scripts")
    @include("medcare.masterData.pabrikan.jsMain")
@endpush
