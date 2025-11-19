@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
@endpush

@section("content")
    @include("medcare.settings.assignBranch.modalMain")
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">AssignBranch</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Assign Branch</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                    </div>
                    <h6 class="card-title">DATA ASSIGN BRANCH</h6>
                    <div class="table-responsive">
                        <table id="tableAssignBranch" name="tableAssignBranch" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Branch</th>
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
    @include("medcare.settings.assignBranch.jsMain")
@endpush
