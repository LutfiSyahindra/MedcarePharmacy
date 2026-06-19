@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.golongan.partials.style")
@endpush

@section("content")
    <div class="golongan-page">
        @include("medcare.masterData.golongan.modalMain")
        @include("medcare.masterData.golongan.modalExcell")
        @include("medcare.masterData.golongan.partials.header")

        <div class="golongan-stats-grid">
            <div class="golongan-stat">
                <span class="golongan-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="golonganTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh golongan obat yang tersimpan.</small>
                </div>
            </div>
            <div class="golongan-stat">
                <span class="golongan-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="golonganFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="golongan-stat">
                <span class="golongan-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="golonganSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="golongan-table-section">
            <div class="golongan-table-toolbar">
                <div class="golongan-table-title">
                    <span class="golongan-table-title-icon"><i class="mdi mdi-shape-outline"></i></span>
                    <div>
                        <h5>Daftar Golongan Obat</h5>
                        <p>Kelola kode, nama, keterangan, dan status aktif golongan.</p>
                    </div>
                </div>
                <div class="golongan-table-tools">
                    <label class="golongan-search" for="golonganSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="golonganSearch" placeholder="Cari kode, nama, atau keterangan...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableGolongan" class="table golongan-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Golongan</th>
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
    @include("medcare.masterData.golongan.partials.scripts")
    @include("medcare.masterData.golongan.jsMain")
@endpush
