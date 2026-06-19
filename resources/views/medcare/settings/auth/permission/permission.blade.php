@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("medcare.settings.auth.partials.style")
@endpush

@section("content")
    @php
        $authActive = "permission";
        $authTitle = "Permission";
        $authDescription = "Rawat daftar hak akses fitur agar role dapat diberi batas yang jelas dan mudah diaudit.";
        $authModalTarget = "#permissionsModal";
        $authActionIcon = "mdi-shield-plus-outline";
        $authActionLabel = "Tambah Permission";
    @endphp

    <div class="auth-page">
        @include("medcare.settings.auth.permission.modalMain")
        @include("medcare.settings.auth.partials.header")

        <div class="auth-stats-grid">
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-shield-key-outline"></i></span>
                <div>
                    <strong id="permissionsTotalCount">0</strong>
                    <span>Total Permission</span>
                    <small>Semua hak akses fitur.</small>
                </div>
            </div>
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-filter-check-outline"></i></span>
                <div>
                    <strong id="permissionsFilteredCount">0</strong>
                    <span>Hasil Filter</span>
                    <small>Mengikuti pencarian aktif.</small>
                </div>
            </div>
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-cursor-default-click-outline"></i></span>
                <div>
                    <strong id="permissionsSelectedCount">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="auth-table-section">
            <div class="auth-table-toolbar">
                <div class="auth-table-title">
                    <span class="auth-table-title-icon"><i class="mdi mdi-shield-key-outline"></i></span>
                    <div>
                        <h5>Daftar Permission</h5>
                        <p>Kelola permission yang akan ditempelkan ke role.</p>
                    </div>
                </div>
                <div class="auth-table-tools">
                    <label class="auth-search" for="permissionsSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="permissionsSearch" placeholder="Cari nama permission">
                    </label>
                    <button type="button" class="btn btn-outline-primary auth-refresh-table" title="Refresh tabel">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tablePermissions" class="table auth-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Permissions</th>
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
    @include("medcare.settings.auth.partials.scripts")
    @include("medcare.settings.auth.permission.jsMain")
@endpush
