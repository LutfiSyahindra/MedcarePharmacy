@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
@endpush

@section("content")
    @include("medcare.settings.margin.modalMain")
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Margin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Margin</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#marginsModal">
                            <i class="mdi mdi-view-list"></i></button>
                    </div>
                    <h6 class="card-title">DATA MARGIN</h6>
                    <div class="table-responsive">
                        <table id="tableMargin" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Reference</th>
                                    <th>Faktor Jual</th>
                                    <th>Persentase</th>
                                    <th>Tingkat</th>
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
    @include("medcare.settings.margin.jsMain")
@endpush
