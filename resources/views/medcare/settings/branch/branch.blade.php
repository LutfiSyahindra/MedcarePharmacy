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
        $branchActive = "branch";
        $branchTitle = "Branch";
        $branchDescription = "Kelola data cabang, kontak, dan status operasional agar jaringan Medcare mudah dipantau.";
        $branchModalTarget = "#branchModal";
        $branchActionIcon = "mdi-store-plus-outline";
        $branchActionLabel = "Tambah Branch";
    @endphp

    <div class="branch-page">
        @include("medcare.settings.branch.modalMain")
        @include("medcare.settings.branch.partials.header")

        <div class="branch-stats-grid">
            <div class="branch-stat">
                <span class="branch-stat-icon"><i class="mdi mdi-storefront-outline"></i></span>
                <div>
                    <strong id="branchTotalCount">0</strong>
                    <span>Total Branch</span>
                    <small>Semua cabang yang terdaftar.</small>
                </div>
            </div>
            <div class="branch-stat">
                <span class="branch-stat-icon"><i class="mdi mdi-filter-check-outline"></i></span>
                <div>
                    <strong id="branchFilteredCount">0</strong>
                    <span>Hasil Filter</span>
                    <small>Mengikuti pencarian aktif.</small>
                </div>
            </div>
            <div class="branch-stat">
                <span class="branch-stat-icon"><i class="mdi mdi-cursor-default-click-outline"></i></span>
                <div>
                    <strong id="branchSelectedCount">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="branch-table-section">
            <div class="branch-table-toolbar">
                <div class="branch-table-title">
                    <span class="branch-table-title-icon"><i class="mdi mdi-map-marker-radius-outline"></i></span>
                    <div>
                        <h5>Daftar Branch</h5>
                        <p>Atur kode, nama cabang, kontak, dan status aktif.</p>
                    </div>
                </div>
                <div class="branch-table-tools">
                    <label class="branch-search" for="branchSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="branchSearch" placeholder="Cari kode atau nama branch">
                    </label>
                    <button type="button" class="btn btn-outline-primary branch-refresh-table" title="Refresh tabel">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableBranch" class="table branch-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Branch</th>
                            <th>Status</th>
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
    @include("medcare.settings.branch.jsMain")
@endpush
