@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.settings.auth.roleSetting.style")
@endpush

@section("content")
    @php
        $allBranchCount = $roles->where("can_view_all_branches", true)->count();
        $approverCount = $roles->where("is_approver", true)->count();
        $notificationCount = $roles->where("receives_notifications", true)->count();
    @endphp

    <div class="role-setting-page">
        <nav aria-label="breadcrumb" class="role-setting-breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route("dashboard") }}">Dashboard</a></li>
                <li class="breadcrumb-item">Pengaturan</li>
                <li class="breadcrumb-item active" aria-current="page">Role Setting</li>
            </ol>
        </nav>

        <section class="role-setting-hero">
            <div class="role-setting-hero-copy">
                <span class="role-setting-kicker"><i class="mdi mdi-shield-key-outline"></i> Role control center</span>
                <h1>Role Setting</h1>
                <p>Atur akses data lintas branch, kewenangan approval, dan tujuan notifikasi untuk setiap role.</p>
            </div>
            <button type="submit" form="roleSettingForm" class="role-setting-save" id="roleSettingSave">
                <i class="mdi mdi-content-save-outline"></i>
                <span>Simpan Konfigurasi</span>
            </button>
        </section>

        <section class="role-setting-summary" aria-label="Ringkasan konfigurasi">
            <article>
                <span class="is-role"><i class="mdi mdi-account-group-outline"></i></span>
                <div><strong id="roleSettingTotal">{{ $roles->count() }}</strong><small>Total role</small></div>
            </article>
            <article>
                <span class="is-branch"><i class="mdi mdi-source-branch"></i></span>
                <div><strong id="allBranchCount">{{ $allBranchCount }}</strong><small>Akses semua branch</small></div>
            </article>
            <article>
                <span class="is-approval"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div><strong id="approverCount">{{ $approverCount }}</strong><small>Role approval</small></div>
            </article>
            <article>
                <span class="is-notification"><i class="mdi mdi-bell-ring-outline"></i></span>
                <div><strong id="notificationRoleCount">{{ $notificationCount }}</strong><small>Penerima balasan</small></div>
            </article>
        </section>

        <section class="role-setting-guide">
            <span><i class="mdi mdi-information-outline"></i></span>
            <div>
                <strong>Cara kerja scope branch</strong>
                <p>“Branch user” membatasi approval atau notifikasi ke branch tempat user ditugaskan. Jika dokumen berasal dari Branch A, role penerima dengan scope ini hanya menerima notifikasi bila usernya juga berada di Branch A.</p>
            </div>
        </section>

        <section class="role-setting-panel">
            <div class="role-setting-toolbar">
                <label class="role-setting-search" for="roleSettingSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="roleSettingSearch" placeholder="Cari nama role..." autocomplete="off">
                </label>
                <div class="role-setting-filters" role="group" aria-label="Filter role">
                    <button type="button" class="is-active" data-role-filter="all">Semua</button>
                    <button type="button" data-role-filter="approver">Approval</button>
                    <button type="button" data-role-filter="all-branch">Semua branch</button>
                    <button type="button" data-role-filter="notification">Notifikasi</button>
                </div>
            </div>

            <form id="roleSettingForm">
                @csrf
                @method("PUT")

                <div class="role-setting-table" role="table" aria-label="Konfigurasi role">
                    <div class="role-setting-table-head" role="row">
                        <span role="columnheader">Role</span>
                        <span role="columnheader">Akses Data</span>
                        <span role="columnheader">Approval</span>
                        <span role="columnheader">Notifikasi Balasan</span>
                    </div>

                    <div class="role-setting-list" id="roleSettingList">
                        @forelse ($roles as $index => $role)
                            <article class="role-setting-row"
                                data-role-name="{{ str($role["name"])->lower() }}"
                                data-role-index="{{ $index }}"
                                role="row">
                                <input type="hidden" name="settings[{{ $index }}][role_id]" value="{{ $role["role_id"] }}">

                                <div class="role-setting-identity" role="cell">
                                    <span class="role-setting-avatar">{{ $role["initials"] ?: "R" }}</span>
                                    <div>
                                        <strong>{{ $role["name"] }}</strong>
                                        <small>{{ number_format($role["user_count"], 0, ",", ".") }} user terhubung</small>
                                    </div>
                                </div>

                                <div class="role-setting-control" role="cell">
                                    <span class="role-setting-mobile-label">Akses data</span>
                                    <label class="role-setting-switch-row">
                                        <span>
                                            <strong>Semua branch</strong>
                                            <small>Lihat data lintas cabang</small>
                                        </span>
                                        <input type="hidden" name="settings[{{ $index }}][can_view_all_branches]" value="0">
                                        <input type="checkbox" class="role-setting-toggle js-all-branch"
                                            name="settings[{{ $index }}][can_view_all_branches]" value="1"
                                            @checked($role["can_view_all_branches"])>
                                        <i aria-hidden="true"></i>
                                    </label>
                                </div>

                                <div class="role-setting-control" role="cell">
                                    <span class="role-setting-mobile-label">Approval</span>
                                    <label class="role-setting-switch-row">
                                        <span>
                                            <strong>Jadi approval</strong>
                                            <small>Izinkan aksi workflow</small>
                                        </span>
                                        <input type="hidden" name="settings[{{ $index }}][is_approver]" value="0">
                                        <input type="checkbox" class="role-setting-toggle js-approver"
                                            name="settings[{{ $index }}][is_approver]" value="1"
                                            @checked($role["is_approver"])>
                                        <i aria-hidden="true"></i>
                                    </label>
                                    <label class="role-setting-scope">
                                        <span>Cakupan approval</span>
                                        <select name="settings[{{ $index }}][approval_scope]" class="form-select js-approval-scope">
                                            <option value="same_branch" @selected($role["approval_scope"] === "same_branch")>Sesuai branch user</option>
                                            <option value="all_branches" @selected($role["approval_scope"] === "all_branches")>Semua branch</option>
                                        </select>
                                    </label>
                                </div>

                                <div class="role-setting-control" role="cell">
                                    <span class="role-setting-mobile-label">Notifikasi balasan</span>
                                    <label class="role-setting-switch-row">
                                        <span>
                                            <strong>Terima balasan</strong>
                                            <small>Hasil approve, reject, atau posting</small>
                                        </span>
                                        <input type="hidden" name="settings[{{ $index }}][receives_notifications]" value="0">
                                        <input type="checkbox" class="role-setting-toggle js-notification"
                                            name="settings[{{ $index }}][receives_notifications]" value="1"
                                            @checked($role["receives_notifications"])>
                                        <i aria-hidden="true"></i>
                                    </label>
                                    <label class="role-setting-scope">
                                        <span>Cakupan notifikasi</span>
                                        <select name="settings[{{ $index }}][notification_scope]" class="form-select js-notification-scope">
                                            <option value="same_branch" @selected($role["notification_scope"] === "same_branch")>Sesuai branch asal</option>
                                            <option value="all_branches" @selected($role["notification_scope"] === "all_branches")>Semua branch</option>
                                        </select>
                                    </label>
                                </div>
                            </article>
                        @empty
                            <div class="role-setting-empty">
                                <i class="mdi mdi-account-off-outline"></i>
                                <strong>Belum ada role</strong>
                                <p>Buat role terlebih dahulu pada menu Auth → Role.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </form>

            <div class="role-setting-no-result" id="roleSettingNoResult" hidden>
                <i class="mdi mdi-magnify-close"></i>
                <strong>Role tidak ditemukan</strong>
                <span>Coba kata kunci atau filter lain.</span>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.settings.auth.roleSetting.jsMain")
@endpush
