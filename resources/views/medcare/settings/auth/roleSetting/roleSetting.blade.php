@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.settings.auth.roleSetting.style")
@endpush

@section("content")
    @php
        $allBranchCount = $roles->where("can_view_all_branches", true)->count();
        $posAllBranchCount = $roles->where("pos_scope", "all_branches")->count();
        $approverCount = $roles->where("is_approver", true)->count();
        $stockOpnameValidatorCount = $roles->where("is_stock_opname_validator", true)->count();
        $stockDuringOpnameViewerCount = $roles->where("can_view_stock_during_opname", true)->count();
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
                <span class="role-setting-kicker"><i class="mdi mdi-shield-key-outline"></i> Pusat kontrol akses</span>
                <h1>Role Setting</h1>
                <p>Kelola akses data, POS, approval, Stok Opname, dan distribusi notifikasi berdasarkan role dan branch.</p>
            </div>
            <div class="role-setting-hero-action">
                <span class="role-setting-save-state" id="roleSettingSaveState">
                    <i class="mdi mdi-check-circle-outline"></i>
                    <span>Semua perubahan tersimpan</span>
                </span>
                <button type="submit" form="roleSettingForm" class="role-setting-save js-role-setting-save">
                    <i class="mdi mdi-content-save-outline"></i>
                    <span>Simpan Konfigurasi</span>
                </button>
            </div>
        </section>

        <section class="role-setting-overview" aria-label="Ringkasan konfigurasi">
            <div class="role-setting-overview-copy">
                <span>Ringkasan akses</span>
                <strong>Konfigurasi aktif saat ini</strong>
                <p>Angka akan langsung menyesuaikan saat konfigurasi role diubah.</p>
            </div>
            <div class="role-setting-metrics">
                <article>
                    <span class="is-role"><i class="mdi mdi-account-group-outline"></i></span>
                    <div><strong id="roleSettingTotal">{{ $roles->count() }}</strong><small>Total role</small></div>
                </article>
                <article>
                    <span class="is-branch"><i class="mdi mdi-source-branch"></i></span>
                    <div><strong id="allBranchCount">{{ $allBranchCount }}</strong><small>Data semua branch</small></div>
                </article>
                <article>
                    <span class="is-pos"><i class="mdi mdi-point-of-sale"></i></span>
                    <div><strong id="posAllBranchCount">{{ $posAllBranchCount }}</strong><small>POS semua branch</small></div>
                </article>
                <article>
                    <span class="is-approval"><i class="mdi mdi-check-decagram-outline"></i></span>
                    <div><strong id="approverCount">{{ $approverCount }}</strong><small>Role approval</small></div>
                </article>
                <article>
                    <span class="is-opname-validator"><i class="mdi mdi-clipboard-check-outline"></i></span>
                    <div><strong id="stockOpnameValidatorCount">{{ $stockOpnameValidatorCount }}</strong><small>Validator opname</small></div>
                </article>
                <article>
                    <span class="is-opname-stock"><i class="mdi mdi-database-eye-outline"></i></span>
                    <div><strong id="stockDuringOpnameViewerCount">{{ $stockDuringOpnameViewerCount }}</strong><small>Akses stok saat opname</small></div>
                </article>
                <article>
                    <span class="is-notification"><i class="mdi mdi-bell-ring-outline"></i></span>
                    <div><strong id="notificationRoleCount">{{ $notificationCount }}</strong><small>Penerima notifikasi</small></div>
                </article>
            </div>
        </section>

        <section class="role-setting-guide">
            <span><i class="mdi mdi-lightbulb-on-outline"></i></span>
            <div>
                <strong>Memahami cakupan branch</strong>
                <p><b>Branch user</b> membatasi akses ke cabang tempat user ditugaskan. <b>Semua branch</b> memberi role akses lintas cabang untuk fitur tersebut.</p>
            </div>
            <div class="role-setting-guide-tags" aria-label="Jenis pengaturan">
                <span><i class="mdi mdi-database-outline"></i> Data</span>
                <span><i class="mdi mdi-point-of-sale"></i> POS</span>
                <span><i class="mdi mdi-check-decagram-outline"></i> Approval</span>
                <span><i class="mdi mdi-clipboard-check-outline"></i> Stok Opname</span>
                <span><i class="mdi mdi-bell-outline"></i> Notifikasi</span>
            </div>
        </section>

        <section class="role-setting-workspace">
            <div class="role-setting-toolbar">
                <div class="role-setting-toolbar-main">
                    <label class="role-setting-search" for="roleSettingSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="roleSettingSearch" placeholder="Cari nama role..." autocomplete="off">
                    </label>
                    <span class="role-setting-visible-count" id="roleSettingVisibleCount">{{ $roles->count() }} role ditampilkan</span>
                </div>
                <div class="role-setting-filters" role="group" aria-label="Filter role">
                    <button type="button" class="is-active" data-role-filter="all">Semua</button>
                    <button type="button" data-role-filter="approver">Approval</button>
                    <button type="button" data-role-filter="opname-validator">Validator opname</button>
                    <button type="button" data-role-filter="opname-stock">Akses stok saat opname</button>
                    <button type="button" data-role-filter="all-branch">Data lintas branch</button>
                    <button type="button" data-role-filter="pos-all">POS lintas branch</button>
                    <button type="button" data-role-filter="notification">Notifikasi</button>
                </div>
            </div>

            <form id="roleSettingForm">
                @csrf
                @method("PUT")

                <div class="role-setting-list" id="roleSettingList">
                    @forelse ($roles as $index => $role)
                        <article class="role-setting-card"
                            data-role-name="{{ str($role["name"])->lower() }}"
                            data-role-index="{{ $index }}">
                            <input type="hidden" name="settings[{{ $index }}][role_id]" value="{{ $role["role_id"] }}">

                            <header class="role-setting-card-header">
                                <div class="role-setting-identity">
                                    <span class="role-setting-avatar">{{ $role["initials"] ?: "R" }}</span>
                                    <div>
                                        <span class="role-setting-role-label">Role</span>
                                        <h2>{{ $role["name"] }}</h2>
                                        <small><i class="mdi mdi-account-multiple-outline"></i> {{ number_format($role["user_count"], 0, ",", ".") }} user terhubung</small>
                                    </div>
                                </div>
                                <div class="role-setting-statuses" aria-label="Status konfigurasi {{ $role["name"] }}">
                                    <span class="role-setting-status js-status-data"></span>
                                    <span class="role-setting-status js-status-pos"></span>
                                    <span class="role-setting-status js-status-approval"></span>
                                    <span class="role-setting-status js-status-opname-validator"></span>
                                    <span class="role-setting-status js-status-opname-stock"></span>
                                    <span class="role-setting-status js-status-notification"></span>
                                </div>
                            </header>

                            <div class="role-setting-card-body">
                                <fieldset class="role-setting-feature is-data">
                                    <legend class="visually-hidden">Akses data {{ $role["name"] }}</legend>
                                    <div class="role-setting-feature-head">
                                        <span><i class="mdi mdi-database-outline"></i></span>
                                        <div>
                                            <strong>Akses Data</strong>
                                            <small>Data yang dapat dilihat oleh role</small>
                                        </div>
                                    </div>
                                    <div class="role-setting-segment-label">Cakupan akses</div>
                                    <div class="role-setting-segment">
                                        <input type="radio" id="dataSameBranch{{ $index }}"
                                            class="js-data-scope"
                                            name="settings[{{ $index }}][can_view_all_branches]" value="0"
                                            @checked(!$role["can_view_all_branches"])>
                                        <label for="dataSameBranch{{ $index }}"><i class="mdi mdi-map-marker-outline"></i> Branch user</label>
                                        <input type="radio" id="dataAllBranch{{ $index }}"
                                            class="js-data-scope"
                                            name="settings[{{ $index }}][can_view_all_branches]" value="1"
                                            @checked($role["can_view_all_branches"])>
                                        <label for="dataAllBranch{{ $index }}"><i class="mdi mdi-earth"></i> Semua branch</label>
                                    </div>
                                </fieldset>

                                <fieldset class="role-setting-feature is-pos">
                                    <legend class="visually-hidden">Akses POS {{ $role["name"] }}</legend>
                                    <div class="role-setting-feature-head">
                                        <span><i class="mdi mdi-point-of-sale"></i></span>
                                        <div>
                                            <strong>Akses POS</strong>
                                            <small>Cabang transaksi dan riwayat kasir</small>
                                        </div>
                                    </div>
                                    <div class="role-setting-segment-label">Cakupan POS</div>
                                    <div class="role-setting-segment">
                                        <input type="radio" id="posSameBranch{{ $index }}"
                                            class="js-pos-scope"
                                            name="settings[{{ $index }}][pos_scope]" value="same_branch"
                                            @checked($role["pos_scope"] === "same_branch")>
                                        <label for="posSameBranch{{ $index }}"><i class="mdi mdi-map-marker-outline"></i> Branch user</label>
                                        <input type="radio" id="posAllBranch{{ $index }}"
                                            class="js-pos-scope"
                                            name="settings[{{ $index }}][pos_scope]" value="all_branches"
                                            @checked($role["pos_scope"] === "all_branches")>
                                        <label for="posAllBranch{{ $index }}"><i class="mdi mdi-earth"></i> Semua branch</label>
                                    </div>
                                </fieldset>

                                <fieldset class="role-setting-feature is-approval js-approval-panel">
                                    <legend class="visually-hidden">Approval {{ $role["name"] }}</legend>
                                    <div class="role-setting-feature-head">
                                        <span><i class="mdi mdi-check-decagram-outline"></i></span>
                                        <div>
                                            <strong>Approval</strong>
                                            <small>Wewenang proses persetujuan</small>
                                        </div>
                                        <label class="role-setting-switch" title="Aktifkan approval">
                                            <input type="hidden" name="settings[{{ $index }}][is_approver]" value="0">
                                            <input type="checkbox" class="js-approver"
                                                name="settings[{{ $index }}][is_approver]" value="1"
                                                @checked($role["is_approver"])>
                                            <i aria-hidden="true"></i>
                                            <span class="visually-hidden">Aktifkan approval untuk {{ $role["name"] }}</span>
                                        </label>
                                    </div>
                                    <div class="role-setting-segment-label">Cakupan approval</div>
                                    <div class="role-setting-segment">
                                        <input type="radio" id="approvalSameBranch{{ $index }}"
                                            class="js-approval-scope"
                                            name="settings[{{ $index }}][approval_scope]" value="same_branch"
                                            @checked($role["approval_scope"] === "same_branch")>
                                        <label for="approvalSameBranch{{ $index }}"><i class="mdi mdi-map-marker-outline"></i> Branch user</label>
                                        <input type="radio" id="approvalAllBranch{{ $index }}"
                                            class="js-approval-scope"
                                            name="settings[{{ $index }}][approval_scope]" value="all_branches"
                                            @checked($role["approval_scope"] === "all_branches")>
                                        <label for="approvalAllBranch{{ $index }}"><i class="mdi mdi-earth"></i> Semua branch</label>
                                    </div>
                                    <span class="role-setting-feature-off"><i class="mdi mdi-information-outline"></i> Approval nonaktif; scope tetap tersimpan.</span>
                                </fieldset>

                                <fieldset class="role-setting-feature is-opname-validator js-opname-validator-panel">
                                    <legend class="visually-hidden">Validator Stok Opname {{ $role["name"] }}</legend>
                                    <div class="role-setting-feature-head">
                                        <span><i class="mdi mdi-clipboard-check-outline"></i></span>
                                        <div>
                                            <strong>Validator Stok Opname</strong>
                                            <small>Memvalidasi selisih dan rekonsiliasi transaksi pada branch yang dapat diakses</small>
                                        </div>
                                        <label class="role-setting-switch" title="Jadikan validator Stok Opname">
                                            <input type="hidden" name="settings[{{ $index }}][is_stock_opname_validator]" value="0">
                                            <input type="checkbox" class="js-opname-validator"
                                                name="settings[{{ $index }}][is_stock_opname_validator]" value="1"
                                                @checked($role["is_stock_opname_validator"])>
                                            <i aria-hidden="true"></i>
                                            <span class="visually-hidden">Jadikan {{ $role["name"] }} validator Stok Opname</span>
                                        </label>
                                    </div>
                                    <span class="role-setting-feature-off"><i class="mdi mdi-information-outline"></i> Role tidak dapat menjalankan validasi Stok Opname.</span>
                                </fieldset>

                                <fieldset class="role-setting-feature is-opname-stock js-opname-stock-panel">
                                    <legend class="visually-hidden">Akses Stok Saat Opname {{ $role["name"] }}</legend>
                                    <div class="role-setting-feature-head">
                                        <span><i class="mdi mdi-database-eye-outline"></i></span>
                                        <div>
                                            <strong>Lihat Stok Saat Opname</strong>
                                            <small>Tetap dapat membuka Stok Barang dan Kartu Stok dalam mode baca selama blind count</small>
                                        </div>
                                        <label class="role-setting-switch" title="Izinkan melihat stok saat opname">
                                            <input type="hidden" name="settings[{{ $index }}][can_view_stock_during_opname]" value="0">
                                            <input type="checkbox" class="js-opname-stock"
                                                name="settings[{{ $index }}][can_view_stock_during_opname]" value="1"
                                                @checked($role["can_view_stock_during_opname"])>
                                            <i aria-hidden="true"></i>
                                            <span class="visually-hidden">Izinkan {{ $role["name"] }} melihat stok saat opname</span>
                                        </label>
                                    </div>
                                    <span class="role-setting-feature-off"><i class="mdi mdi-information-outline"></i> Stok Barang dan Kartu Stok dikunci selama blind count.</span>
                                </fieldset>

                                <fieldset class="role-setting-feature is-notification js-notification-panel">
                                    <legend class="visually-hidden">Notifikasi {{ $role["name"] }}</legend>
                                    <div class="role-setting-feature-head">
                                        <span><i class="mdi mdi-bell-ring-outline"></i></span>
                                        <div>
                                            <strong>Notifikasi Balasan</strong>
                                            <small>Hasil approve, reject, atau posting</small>
                                        </div>
                                        <label class="role-setting-switch" title="Aktifkan notifikasi balasan">
                                            <input type="hidden" name="settings[{{ $index }}][receives_notifications]" value="0">
                                            <input type="checkbox" class="js-notification"
                                                name="settings[{{ $index }}][receives_notifications]" value="1"
                                                @checked($role["receives_notifications"])>
                                            <i aria-hidden="true"></i>
                                            <span class="visually-hidden">Aktifkan notifikasi untuk {{ $role["name"] }}</span>
                                        </label>
                                    </div>
                                    <div class="role-setting-segment-label">Cakupan notifikasi</div>
                                    <div class="role-setting-segment">
                                        <input type="radio" id="notificationSameBranch{{ $index }}"
                                            class="js-notification-scope"
                                            name="settings[{{ $index }}][notification_scope]" value="same_branch"
                                            @checked($role["notification_scope"] === "same_branch")>
                                        <label for="notificationSameBranch{{ $index }}"><i class="mdi mdi-map-marker-outline"></i> Branch asal</label>
                                        <input type="radio" id="notificationAllBranch{{ $index }}"
                                            class="js-notification-scope"
                                            name="settings[{{ $index }}][notification_scope]" value="all_branches"
                                            @checked($role["notification_scope"] === "all_branches")>
                                        <label for="notificationAllBranch{{ $index }}"><i class="mdi mdi-earth"></i> Semua branch</label>
                                    </div>
                                    <span class="role-setting-feature-off"><i class="mdi mdi-information-outline"></i> Notifikasi nonaktif; scope tetap tersimpan.</span>
                                </fieldset>
                            </div>
                        </article>
                    @empty
                        <div class="role-setting-empty">
                            <i class="mdi mdi-account-off-outline"></i>
                            <strong>Belum ada role</strong>
                            <p>Buat role terlebih dahulu pada menu Auth &gt; Role.</p>
                        </div>
                    @endforelse
                </div>
            </form>

            <div class="role-setting-no-result" id="roleSettingNoResult" hidden>
                <i class="mdi mdi-magnify-close"></i>
                <strong>Role tidak ditemukan</strong>
                <span>Coba kata kunci atau filter lain.</span>
            </div>
        </section>

        <div class="role-setting-save-dock" id="roleSettingSaveDock" hidden>
            <div>
                <span><i class="mdi mdi-circle-medium"></i></span>
                <div><strong>Perubahan belum disimpan</strong><small>Simpan agar konfigurasi baru segera diterapkan.</small></div>
            </div>
            <button type="submit" form="roleSettingForm" class="role-setting-save js-role-setting-save">
                <i class="mdi mdi-content-save-outline"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>
@endsection

@push("scripts")
    @include("medcare.settings.auth.roleSetting.jsMain")
@endpush
