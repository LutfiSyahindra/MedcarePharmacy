@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.sediaan.partials.style")
@endpush

@section("content")
    <div class="sediaan-page">
        @include("medcare.masterData.sediaan.modalMain")
        @include("medcare.masterData.sediaan.modalExcell")
        @include("medcare.masterData.sediaan.partials.header")

        <div class="sediaan-stats-grid">
            <div class="sediaan-stat">
                <span class="sediaan-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="sediaanTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh sediaan obat yang tersimpan.</small>
                </div>
            </div>
            <div class="sediaan-stat">
                <span class="sediaan-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="sediaanFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="sediaan-stat">
                <span class="sediaan-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="sediaanSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="sediaan-table-section">
            <div class="sediaan-table-toolbar">
                <div class="sediaan-table-title">
                    <span class="sediaan-table-title-icon"><i class="mdi mdi-bottle-tonic-outline"></i></span>
                    <div>
                        <h5>Daftar Sediaan Obat</h5>
                        <p>Kelola kode, nama, keterangan, dan status aktif sediaan.</p>
                    </div>
                </div>
                <div class="sediaan-table-tools">
                    <label class="sediaan-search" for="sediaanSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="sediaanSearch" placeholder="Cari kode, nama, atau keterangan...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableSediaan" class="table sediaan-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Sediaan</th>
                            <th>Keterangan</th>
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
    @include("medcare.masterData.sediaan.partials.scripts")
    @include("medcare.masterData.sediaan.jsMain")
@endpush
