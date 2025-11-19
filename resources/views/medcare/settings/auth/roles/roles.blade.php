@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
@endpush

@section("content")
    @include("medcare.settings.auth.roles.modalMain")
    @include("medcare.settings.auth.roles.modalAssignPermissions")
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Roles</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rolesModal">
                            <i class="mdi mdi-cog"></i>
                        </button>
                    </div>
                    <h6 class="card-title">DATA ROLES</h6>
                    <div class="table-responsive">
                        <table id="tableRoles" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Roles</th>
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
    @include("medcare.settings.auth.roles.jsMain")
@endpush
