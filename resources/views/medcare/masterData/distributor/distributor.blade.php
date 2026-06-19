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
        @include("medcare.masterData.distributor.modalMain")
        @include("medcare.masterData.distributor.modalExcell")
        @include("medcare.masterData.partials.company-header", [
            "companyTitle" => "Distributor Obat",
            "companyBreadcrumb" => "Distributor",
            "companyKicker" => "Supply Partner",
            "companyDescription" => "Kelola data distributor obat lengkap dengan alamat dan kontak agar proses pembelian serta penerimaan lebih mudah ditelusuri.",
            "companyIcon" => "mdi-archive",
            "companyModalTarget" => "#distributorModal",
            "companyImportTarget" => "#distributorModalExcell",
            "companyActionLabel" => "Tambah Distributor",
            "companyImportLabel" => "Import Distributor",
            "companyFlow" => [
                ["icon" => "mdi-barcode-scan", "title" => "Kode", "subtitle" => "Identitas singkat distributor"],
                ["icon" => "mdi-warehouse", "title" => "Distributor", "subtitle" => "Nama dan alamat pemasok"],
                ["icon" => "mdi-truck-delivery-outline", "title" => "Kontak", "subtitle" => "Telepon dan email operasional"],
            ],
        ])

        <div class="company-stats-grid">
            <div class="company-stat">
                <span class="company-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="distributorTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh distributor yang tersimpan.</small>
                </div>
            </div>
            <div class="company-stat">
                <span class="company-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="distributorFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="company-stat">
                <span class="company-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="distributorSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="company-table-section">
            <div class="company-table-toolbar">
                <div class="company-table-title">
                    <span class="company-table-title-icon"><i class="mdi mdi-archive"></i></span>
                    <div>
                        <h5>Daftar Distributor</h5>
                        <p>Kelola profil distributor, kontak, dan status aktif.</p>
                    </div>
                </div>
                <div class="company-table-tools">
                    <label class="company-search" for="distributorSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="distributorSearch" placeholder="Cari kode, nama, alamat, telepon, atau email...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableDistributor" class="table company-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Distributor</th>
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
    @include("medcare.masterData.distributor.jsMain")
@endpush
