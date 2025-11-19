@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
@endpush

@section("content")
    @include("medcare.settings.auth.permission.modalMain")
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Permissions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Permissions</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#permissionsModal">
                            <i class="mdi mdi-shield-account"></i>
                        </button>
                    </div>
                    <h6 class="card-title">DATA PERMISSIONS</h6>
                    <div class="table-responsive">
                        <table id="tablePermissions" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Permissions</th>
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
    @include("medcare.settings.auth.permission.jsMain")
@endpush
