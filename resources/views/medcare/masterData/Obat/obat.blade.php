@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
@endpush

<style>
    /* Kolom Action (paling kiri) */
    .dataTables_wrapper .dataTable th.sticky-action,
    .dataTables_wrapper .dataTable td.sticky-action {
        position: sticky;
        left: 0;
        background: #fff;
        z-index: 5;
        /* border-right: 1px solid #dee2e6; */
        /* box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05); */
    }

    /* Agar tabel rapi */
    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        white-space: nowrap;
        vertical-align: middle;
    }
</style>

@section("content")
    @include("medcare.masterData.obat.modalMain")
    @include("medcare.masterData.obat.modalExcell")
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Obat</a></li>
            <li class="breadcrumb-item active" aria-current="page">Master Data Obat</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-3 bg-light rounded-3 shadow-sm">
                        <!-- Bagian Kiri: Ikon dan Judul -->
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="icon bg-primary bg-opacity-10 text-primary rounded-circle me-3 d-flex align-items-center justify-content-center"
                                style="width: 44px; height: 44px;">
                                <i class="mdi mdi-pill mdi-24px"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-primary mb-1">Data Master Obat</h5>
                                <small class="text-muted">Kelola dan cari data obat dengan cepat</small>
                            </div>
                        </div>

                        <!-- Bagian Kanan: Search dan Tombol Aksi -->
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <!-- Search Bar -->
                            <div class="input-group input-group-sm" style="width: 220px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="mdi mdi-magnify text-muted"></i>
                                </span>
                                <input type="text" id="searchObat" class="form-control border-start-0"
                                    placeholder="Cari obat...">
                            </div>

                            <!-- Tombol Import Excel -->
                            <button type="button" class="btn btn-success btn-sm d-flex align-items-center"
                                data-bs-toggle="modal" data-bs-target="#obatModalExcell">
                                <i class="mdi mdi-file-excel me-1"></i>
                                <span>Import</span>
                            </button>

                            <!-- Tombol Tambah Data -->
                            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center"
                                data-bs-toggle="modal" data-bs-target="#obatModal">
                                <i class="mdi mdi-plus-circle me-1"></i>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>

                    <!-- Komponen Livewire -->
                    <!-- Tabel Data -->
                    <div class="table-responsive">
                        <table id="tableObat" class="table">
                            <thead>
                                <tr>
                                    <th>Actions</th>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Obat</th>
                                    <th>Kategori Utama</th>
                                    <th>Kategori</th>
                                    <th>Sub Kategori</th>
                                    <th>Golongan</th>
                                    <th>Satuan</th>
                                    <th>Sediaan</th>
                                    <th>Pabrikan</th>
                                    <th>Distributor</th>
                                    <th>Penyimpanan</th>
                                    <th>Kemasan</th>
                                    <th>Stok Minimum</th>
                                    <th>Stok</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    @include("medcare.masterData.Obat.jsMain")
@endpush
