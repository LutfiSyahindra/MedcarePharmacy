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
        $authActive = "users";
        $authTitle = "Users";
        $authDescription = "Kelola akun pengguna, status aktif, dan penempatan role dari satu layar yang lebih cepat dipindai.";
        $authModalTarget = "#usersModal";
        $authActionIcon = "mdi-account-plus-outline";
        $authActionLabel = "Tambah User";
    @endphp

    <div class="auth-page">
        @include("medcare.settings.auth.users.modalMain")
        @include("medcare.settings.auth.users.modalAssignRoles")
        @include("medcare.settings.auth.partials.header")

        <div class="auth-stats-grid">
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-account-group-outline"></i></span>
                <div>
                    <strong id="usersTotalCount">0</strong>
                    <span>Total Users</span>
                    <small>Semua akun yang terdaftar.</small>
                </div>
            </div>
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-filter-check-outline"></i></span>
                <div>
                    <strong id="usersFilteredCount">0</strong>
                    <span>Hasil Filter</span>
                    <small>Mengikuti pencarian aktif.</small>
                </div>
            </div>
            <div class="auth-stat">
                <span class="auth-stat-icon"><i class="mdi mdi-cursor-default-click-outline"></i></span>
                <div>
                    <strong id="usersSelectedCount">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="auth-table-section">
            <div class="auth-table-toolbar">
                <div class="auth-table-title">
                    <span class="auth-table-title-icon"><i class="mdi mdi-account-multiple-outline"></i></span>
                    <div>
                        <h5>Daftar Users</h5>
                        <p>Atur identitas, email, status login, dan role pengguna.</p>
                    </div>
                </div>
                <div class="auth-table-tools">
                    <label class="auth-search" for="usersSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="usersSearch" placeholder="Cari nama atau email">
                    </label>
                    <button type="button" class="btn btn-outline-primary auth-refresh-table" title="Refresh tabel">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableUsers" class="table auth-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Email</th>
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
    @include("medcare.settings.auth.partials.scripts")
    @include("medcare.settings.auth.users.jsMain")
@endpush
