@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("medcare.settings.branch.partials.style")
@endpush

@section("content")
    @php
        $branchActive = "assign";
        $branchTitle = "Assign Branch";
        $branchDescription = "Hubungkan user dengan cabang operasional agar akses kerja mengikuti lokasi yang tepat.";
        $branchModalTarget = "";
        $branchActionIcon = "";
        $branchActionLabel = "";
    @endphp

    <div class="branch-page">
        @include("medcare.settings.assignBranch.modalMain")
        @include("medcare.settings.branch.partials.header")

        <div class="branch-stats-grid">
            <div class="branch-stat">
                <span class="branch-stat-icon"><i class="mdi mdi-storefront-outline"></i></span>
                <div>
                    <strong id="assignBranchTotalCount">0</strong>
                    <span>Total Branch</span>
                    <small>Cabang yang bisa diberi user.</small>
                </div>
            </div>
            <div class="branch-stat">
                <span class="branch-stat-icon"><i class="mdi mdi-filter-check-outline"></i></span>
                <div>
                    <strong id="assignBranchFilteredCount">0</strong>
                    <span>Hasil Filter</span>
                    <small>Mengikuti pencarian aktif.</small>
                </div>
            </div>
            <div class="branch-stat">
                <span class="branch-stat-icon"><i class="mdi mdi-account-check-outline"></i></span>
                <div>
                    <strong id="assignBranchSelectedCount">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris untuk menandai cabang.</small>
                </div>
            </div>
        </div>

        <section class="branch-table-section">
            <div class="branch-table-toolbar">
                <div class="branch-table-title">
                    <span class="branch-table-title-icon"><i class="mdi mdi-account-switch-outline"></i></span>
                    <div>
                        <h5>Assign User ke Branch</h5>
                        <p>Pilih cabang lalu kelola user yang ditugaskan.</p>
                    </div>
                </div>
                <div class="branch-table-tools">
                    <label class="branch-search" for="assignBranchSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="assignBranchSearch" placeholder="Cari kode atau nama branch">
                    </label>
                    <button type="button" class="btn btn-outline-primary branch-refresh-table" title="Refresh tabel">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableAssignBranch" name="tableAssignBranch" class="table branch-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Branch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.settings.branch.partials.scripts")
    @include("medcare.settings.assignBranch.jsMain")
@endpush
