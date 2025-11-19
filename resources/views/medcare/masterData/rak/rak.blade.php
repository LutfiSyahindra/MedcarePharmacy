@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
@endpush

@section("content")
    @include("medcare.masterData.rak.modalMain")
    @include("medcare.masterData.rak.modalExcell")
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">RAK PENYIMPANAN</a></li>
            <li class="breadcrumb-item active" aria-current="page">RAK PENYIMPANAN rak</li>
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
                                <i class="mdi mdi-archive mdi-24px"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-primary mb-1">Data Master Rak</h5>
                                <small class="text-muted">Kelola dan cari data Rak dengan cepat</small>
                            </div>
                        </div>

                        <!-- Bagian Kanan: Search dan Tombol Aksi -->
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <!-- Search Bar -->
                            <div class="input-group input-group-sm" style="width: 220px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="mdi mdi-magnify text-muted"></i>
                                </span>
                                <input type="text" id="searchRak" class="form-control border-start-0"
                                    placeholder="Cari Rak...">
                            </div>

                            <!-- Tombol Import Excel -->
                            <button type="button" class="btn btn-success btn-sm d-flex align-items-center"
                                data-bs-toggle="modal" data-bs-target="#rakModalExcell">
                                <i class="mdi mdi-file-excel me-1"></i>
                                <span>Import</span>
                            </button>

                            <!-- Tombol Tambah Data -->
                            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center"
                                data-bs-toggle="modal" data-bs-target="#rakModal">
                                <i class="mdi mdi-plus-circle me-1"></i>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tableRak" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Rak Penyimpanan</th>
                                    <th>Status</th>
                                    <th>Actions</th>
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
    @include("medcare.masterData.rak.jsMain")
@endpush
