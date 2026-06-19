@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.satuan.partials.style")
@endpush

@section("content")
    <div class="satuan-page">
        @include("medcare.masterData.satuan.modalMain")
        @include("medcare.masterData.satuan.modalExcell")
        @include("medcare.masterData.satuan.partials.header")

        <div class="satuan-stats-grid">
            <div class="satuan-stat">
                <span class="satuan-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="satuanTotal">0</strong>
                    <span>Total Data</span>
                    <small>Seluruh satuan obat yang tersimpan.</small>
                </div>
            </div>
            <div class="satuan-stat">
                <span class="satuan-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="satuanFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="satuan-stat">
                <span class="satuan-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="satuanSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="satuan-table-section">
            <div class="satuan-table-toolbar">
                <div class="satuan-table-title">
                    <span class="satuan-table-title-icon"><i class="mdi mdi-scale-balance"></i></span>
                    <div>
                        <h5>Daftar Satuan Obat</h5>
                        <p>Kelola kode, nama satuan, dan status aktif satuan.</p>
                    </div>
                </div>
                <div class="satuan-table-tools">
                    <label class="satuan-search" for="satuanSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="satuanSearch" placeholder="Cari kode atau nama satuan...">
                    </label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableSatuan" class="table satuan-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Satuan</th>
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
    @include("medcare.masterData.satuan.partials.scripts")
    @include("medcare.masterData.satuan.jsMain")
@endpush
