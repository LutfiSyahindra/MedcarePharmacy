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
        $authActive = "roles";
        $authTitle = "Role";
        $authDescription = "Susun kelompok akses kerja agar user mendapat permission yang tepat sesuai tanggung jawabnya.";
        $authModalTarget = "#rolesModal";
        $authActionIcon = "mdi-account-key-outline";
        $authActionLabel = "Tambah Role";
    @endphp

    <div class="auth-page">
        @include("medcare.settings.auth.roles.modalMain")
        @include("medcare.settings.auth.roles.modalAssignPermissions")
        @include("medcare.settings.auth.partials.header")

        <div class="auth-stats-grid">
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-account-key-outline"></i></span>
                <div>
                    <strong id="rolesTotalCount">0</strong>
                    <span>Total Role</span>
                    <small>Semua kelompok akses kerja.</small>
                </div>
            </div>
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-filter-check-outline"></i></span>
                <div>
                    <strong id="rolesFilteredCount">0</strong>
                    <span>Hasil Filter</span>
                    <small>Mengikuti pencarian aktif.</small>
                </div>
            </div>
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-cursor-default-click-outline"></i></span>
                <div>
                    <strong id="rolesSelectedCount">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="auth-table-section">
            <div class="auth-table-toolbar">
                <div class="auth-table-title">
                    <span class="auth-table-title-icon"><i class="mdi mdi-account-key-outline"></i></span>
                    <div>
                        <h5>Daftar Role</h5>
                        <p>Kelola role dan hubungkan permission yang diperlukan.</p>
                    </div>
                </div>
                <div class="auth-table-tools">
                    <label class="auth-search" for="rolesSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="rolesSearch" placeholder="Cari nama role">
                    </label>
                    <button type="button" class="btn btn-outline-primary auth-refresh-table" title="Refresh tabel">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableRoles" class="table auth-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Roles</th>
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
    @include("medcare.settings.auth.roles.jsMain")
@endpush
