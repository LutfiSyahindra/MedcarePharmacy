@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.rak.partials.style")
@endpush

@section("content")
    <div class="rak-page">
        @include("medcare.masterData.rak.modalMain")
        @include("medcare.masterData.rak.modalExcell")
        @include("medcare.masterData.rak.partials.header")

        <div class="rak-stats-grid">
            <div class="rak-stat">
                <span class="rak-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="rakTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh rak penyimpanan obat yang tersimpan.</small>
                </div>
            </div>
            <div class="rak-stat">
                <span class="rak-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="rakFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="rak-stat">
                <span class="rak-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="rakSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai rak.</small>
                </div>
            </div>
        </div>

        <section class="rak-table-section">
            <div class="rak-table-toolbar">
                <div class="rak-table-title">
                    <span class="rak-table-title-icon"><i class="mdi mdi-archive-marker-outline"></i></span>
                    <div>
                        <h5>Daftar Rak Penyimpanan</h5>
                        <p>Kelola kode, nama rak, lokasi, dan status aktif penyimpanan obat.</p>
                    </div>
                </div>
                <div class="rak-table-tools">
                    <label class="rak-search" for="rakSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="rakSearch" placeholder="Cari kode, rak, atau lokasi...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableRak" class="table rak-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Rak Penyimpanan</th>
                            <th>Lokasi</th>
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
    @include("medcare.masterData.rak.partials.scripts")
    @include("medcare.masterData.rak.jsMain")
@endpush
