@php
    $sidebarUser = auth()->user();
    $sidebarUserName = $sidebarUser?->name ?: "Pengguna Medcare";
    $sidebarUserRole = $sidebarUser?->getRoleNames()->first() ?: "Team Member";
    $sidebarUserInitials = collect(preg_split('/\s+/', trim($sidebarUserName)))
        ->filter()
        ->take(2)
        ->map(fn($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode("");

    $sidebarBranch = null;
    if ($sidebarUser) {
        $sidebarBranchIds = \App\Support\BranchAccess::userBranchIds($sidebarUser);
        $sessionBranchId = (int) session("active_branch_id", 0);
        $isSidebarAdmin = $sidebarUser->hasAnyRole(["Admin", "admin"]);

        if ($sessionBranchId && ($isSidebarAdmin || in_array($sessionBranchId, $sidebarBranchIds, true))) {
            $sidebarBranch = \App\Models\BranchModel::with("apotekProfile")->find($sessionBranchId);
        }

        if (!$sidebarBranch && !empty($sidebarBranchIds)) {
            $sidebarBranch = \App\Models\BranchModel::with("apotekProfile")->find($sidebarBranchIds[0]);
        }

        if (!$sidebarBranch && $isSidebarAdmin) {
            $sidebarBranch = \App\Models\BranchModel::with("apotekProfile")
                ->where("is_active", true)
                ->orderBy("name")
                ->first();
        }
    }

    $sidebarApotekProfile = $sidebarBranch?->apotekProfile;
    $sidebarLogoUrl = $sidebarApotekProfile?->logo_url;
    $sidebarBrandName = $sidebarApotekProfile?->name ?: ($sidebarBranch?->name ?: "Medcare Phar");
    $sidebarBrandTagline = $sidebarBranch?->code
        ? "Cabang {$sidebarBranch->code}"
        : "Pharmacy Management";
    $sidebarProfileMeta = trim($sidebarUserRole . ($sidebarBranch?->code ? " - {$sidebarBranch->code}" : ""));
    $documentMenuActive = request()->routeIs("dokumen.*");
    $orderDocumentMenuActive = request()->routeIs("dokumen.index", "dokumen.table", "dokumen.template", "dokumen.show");
    $labelDocumentMenuActive = request()->routeIs("dokumen.etiket.*");
    $receiptDocumentMenuActive = request()->routeIs("dokumen.nota.*");
    $stockMenuActive = request()->routeIs("stok.*", "kartuStok.*", "stockOpname.*");
    $inventoryAnalysisMenuActive = request()->routeIs("analisisPersediaan.*");
    $procurementAnalysisMenuActive = request()->routeIs("analisisPengadaan.*");
    $revenueAnalysisMenuActive = request()->routeIs("analisisOmzet.*") || request()->routeIs("analisisPenjualan.*");
    $salesAnalysisSection = request()->query('section', 'tren');
    $profitabilityAnalysisMenuActive = request()->routeIs("analisisProfitabilitas.*");
    $profitabilityAnalysisSection = request()->query('section', 'profit-product');
    $reportMenuActive = request()->routeIs("laporan.*");
    $stockOpnameAccess = app(\App\Support\StockOpnameAccess::class);
    $activeStockOpnameLock = $stockOpnameAccess->activeLock();
    $stockMenuLock = $stockOpnameAccess->stockMenusAreLocked() ? $activeStockOpnameLock : null;
    $cashierOpnameLock = $activeStockOpnameLock;
    $patientMenuActive = request()->routeIs("pasien.*");
    $sidebarCan = static fn(string $permission): bool => (bool) $sidebarUser?->can($permission);
    $sidebarCanAny = static fn(array $permissions): bool => collect($permissions)->contains($sidebarCan);
    $sidebarHasSettings = $sidebarCanAny(\App\Support\SidebarPermissions::settings());
    $sidebarReportsAndAnalysisPermissions = [
        \App\Support\SidebarPermissions::ANALISIS_PERSEDIAAN,
        \App\Support\SidebarPermissions::ANALISIS_PENJUALAN,
        \App\Support\SidebarPermissions::ANALISIS_PROFITABILITAS,
        \App\Support\SidebarPermissions::ANALISIS_PENGADAAN,
        \App\Support\SidebarPermissions::LAPORAN,
    ];
    $sidebarHasOperations = $sidebarCanAny(array_values(array_diff(
        \App\Support\SidebarPermissions::operations(),
        $sidebarReportsAndAnalysisPermissions,
    )));
    $sidebarHasReportsAndAnalysis = $sidebarCanAny($sidebarReportsAndAnalysisPermissions);
@endphp

<!-- partial:partials/_sidebar.html -->
<nav class="sidebar medcare-sidebar" aria-label="Navigasi utama">
    <div class="sidebar-header">
        <a href="{{ route("dashboard") }}" class="sidebar-brand" aria-label="{{ $sidebarBrandName }} - Dashboard"
            title="{{ $sidebarBrandName }}">
            <span class="sidebar-brand-mark {{ $sidebarLogoUrl ? "has-official-logo" : "" }}" aria-hidden="true">
                @if ($sidebarLogoUrl)
                    <img src="{{ $sidebarLogoUrl }}" alt="" class="sidebar-brand-logo">
                @endif
                <span class="sidebar-brand-cross"></span>
            </span>
            <span class="sidebar-brand-copy">
                <span class="sidebar-brand-name">{{ $sidebarBrandName }}</span>
                <span class="sidebar-brand-tagline">{{ $sidebarBrandTagline }}</span>
            </span>
        </a>
        <button type="button" class="sidebar-toggler not-active" aria-label="Ciutkan sidebar"
            title="Ciutkan sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
    <div class="sidebar-body">
        <div class="sidebar-profile" aria-label="Pengguna aktif">
            <span class="sidebar-profile-avatar" aria-hidden="true">{{ $sidebarUserInitials ?: "MP" }}</span>
            <span class="sidebar-profile-copy">
                <span class="sidebar-profile-label">Akun aktif</span>
                <strong class="sidebar-profile-name">{{ $sidebarUserName }}</strong>
                <span class="sidebar-profile-role">{{ $sidebarProfileMeta }}</span>
            </span>
            <span class="sidebar-profile-status" title="Sistem aktif" aria-label="Sistem aktif"></span>
        </div>

        <ul class="nav sidebar-menu">
            {{-- Main --}}
            @if ($sidebarCan(\App\Support\SidebarPermissions::DASHBOARD))
                <li class="nav-item nav-category">Overview</li>
                <li class="nav-item">
                    <a href="{{ route("dashboard") }}"
                        class="nav-link {{ request()->routeIs("dashboard") ? "active" : "" }}">
                        <i class="link-icon" data-feather="grid"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>
            @endif

            {{-- Settings --}}
            @if ($sidebarHasSettings)
                <li class="nav-item nav-category">Pengaturan</li>
            @endif
            @if ($sidebarCan(\App\Support\SidebarPermissions::SETTINGS_PROFILE_APOTEK))
                <li class="nav-item">
                    <a href="{{ route("settings.apotek-profile.index") }}"
                        class="nav-link {{ request()->routeIs("settings.apotek-profile.*") ? "active" : "" }}">
                        <i class="link-icon" data-feather="home"></i>
                        <span class="link-title">Profile Apotek</span>
                    </a>
                </li>
            @endif
            @if ($sidebarCan(\App\Support\SidebarPermissions::SETTINGS_AUTH))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#users" role="button" aria-expanded="false"
                        aria-controls="users">
                        <i class="link-icon" data-feather="users"></i>
                        <span class="link-title">Auth</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="users">
                        <ul class="nav sub-menu">
                            <li class="nav-item">
                                <a href="{{ route("users.index") }}" class="nav-link">Users</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route("roles.role") }}" class="nav-link">Role</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route("permissions.permissions") }}" class="nav-link">Permission</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
            @if ($sidebarCan(\App\Support\SidebarPermissions::SETTINGS_ROLE_SETTING))
                <li class="nav-item">
                    <a href="{{ route("settings.role-setting.index") }}"
                        class="nav-link {{ request()->routeIs("settings.role-setting.*") ? "active" : "" }}">
                        <i class="link-icon" data-feather="shield"></i>
                        <span class="link-title">Role Setting</span>
                    </a>
                </li>
            @endif
            @if ($sidebarCan(\App\Support\SidebarPermissions::SETTINGS_BRANCH))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#branch" role="button" aria-expanded="false"
                        aria-controls="branch">
                        <i class="link-icon" data-feather="git-branch"></i>
                        <span class="link-title">Branch</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="branch">
                        <ul class="nav sub-menu">
                            <li class="nav-item">
                                <a href="{{ route("branch.index") }}" class="nav-link">Branch</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route("assignBranch.assignBranch") }}" class="nav-link">Assign Branch</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
            @if ($sidebarCan(\App\Support\SidebarPermissions::SETTINGS_MARGIN))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#margin" role="button" aria-expanded="false"
                        aria-controls="branch">
                        <i class="link-icon" data-feather="percent"></i>
                        <span class="link-title">Margin</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>

                    <div class="collapse" id="margin">
                        <ul class="nav sub-menu">
                            <li class="nav-item">
                                <a href="{{ route("margin.margin") }}" class="nav-link">Margin</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
            @if ($sidebarCan(\App\Support\SidebarPermissions::SETTINGS_NOTIFIKASI))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#settings-notifikasi" role="button"
                        aria-expanded="false" aria-controls="settings-notifikasi">
                        <i class="link-icon" data-feather="bell"></i>
                        <span class="link-title">Notifikasi</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>

                    <div class="collapse" id="settings-notifikasi">
                        <ul class="nav sub-menu">
                            <li class="nav-item">
                                <a href="{{ route("settings.notifikasi.index") }}" class="nav-link">Konfigurasi</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            {{-- Master Data --}}
            @if ($sidebarCan(\App\Support\SidebarPermissions::MASTER_DATA))
                <li class="nav-item nav-category">Master Data</li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#kategori_obat" role="button" aria-expanded="false"
                    aria-controls="kategori_obat">
                    <i class="link-icon" data-feather="tag"></i>
                    <span class="link-title">Kategori</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="kategori_obat">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("kategori.kategoriUtama") }}" class="nav-link">Kategori</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#satuan" role="button" aria-expanded="false"
                    aria-controls="satuan">
                    <i class="link-icon" data-feather="hash"></i>
                    <span class="link-title">Satuan</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="satuan">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("satuan.satuan") }}" class="nav-link">Main satuan</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#golongan" role="button" aria-expanded="false"
                    aria-controls="golongan">
                    <i class="link-icon" data-feather="grid"></i>
                    <span class="link-title">Golongan</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="golongan">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("golongan.golongan") }}" class="nav-link">Golongan Obat</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("golongan.mainGolongan") }}" class="nav-link">Main Golongan</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("golongan.subGolongan") }}" class="nav-link">Sub Golongan</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#sediaan" role="button" aria-expanded="false"
                    aria-controls="sediaan">
                    <i class="link-icon" data-feather="droplet"></i>
                    <span class="link-title">Sediaan</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="sediaan">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("sediaan.sediaan") }}" class="nav-link">Sediaan Obat</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#pabrikan" role="button" aria-expanded="false"
                    aria-controls="pabrikan">
                    <i class="link-icon" data-feather="settings"></i>
                    <span class="link-title">Pabrikan / Produksi</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="pabrikan">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("pabrikan.pabrikan") }}" class="nav-link">Pabrikan / Produksi Obat</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#distributor" role="button"
                    aria-expanded="false" aria-controls="distributor">
                    <i class="link-icon" data-feather="truck"></i>
                    <span class="link-title">Distributor</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="distributor">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("distributor.distributor") }}" class="nav-link">Distributor Obat</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#rak" role="button" aria-expanded="false"
                    aria-controls="rak">
                    <i class="link-icon" data-feather="archive"></i>
                    <span class="link-title">Rak Penyimpanan</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="rak">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("rak.rak") }}" class="nav-link">Rak Penyimpanan Obat</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#masterObat" role="button"
                    aria-expanded="false" aria-controls="masterObat">
                    <i class="link-icon" data-feather="layers"></i>
                    <span class="link-title">Master Obat</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="masterObat">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("masterObat.MasterObat") }}" class="nav-link">Master Obat</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#konversiObat" role="button"
                    aria-expanded="false" aria-controls="konversiObat">

                    <i class="link-icon" data-feather="repeat"></i>

                    <span class="link-title">Konversi Satuan Obat</span>

                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>

                <div class="collapse" id="konversiObat">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("konversiSatuanObat.konversiSatuanObat") }}" class="nav-link">Konversi
                                Satuan</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- Menu --}}
            @if ($sidebarHasOperations)
                <li class="nav-item nav-category">Operasional</li>
            @endif
            @if ($sidebarCan(\App\Support\SidebarPermissions::NOTIFIKASI))
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#notifikasi" role="button"
                    aria-expanded="false" aria-controls="notifikasi">
                    <i class="link-icon" data-feather="bell"></i>

                    <span class="link-title">
                        Notifikasi
                        {{-- 🔔 CONTAINER KHUSUS BADGE --}}
                        <span id="notif-badge-container">
                            @if (auth()->user()->unreadNotifications->count())
                                <span class="badge bg-danger ms-1" id="notif-count">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </span>
                    </span>

                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>

                <div class="collapse" id="notifikasi">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("notifikasi.SemuaNotifikasi") }}" class="nav-link">Semua Notifikasi</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::PENJUALAN))
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#penjualan" role="button"
                    aria-expanded="false" aria-controls="penjualan">
                    <i class="link-icon" data-feather="shopping-cart"></i>
                    <span class="link-title">Penjualan</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="penjualan">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            @if ($cashierOpnameLock)
                                <span class="nav-link text-warning" title="Dikunci oleh {{ $cashierOpnameLock->nomor }}">
                                    <i class="mdi mdi-lock-outline me-1"></i> Kasir / POS dikunci
                                </span>
                            @else
                                <a href="{{ route("penjualan.pos") }}" class="nav-link" target="_blank"
                                    rel="noopener noreferrer">Kasir / POS</a>
                            @endif
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("penjualan.pos.history") }}" class="nav-link">Riwayat Transaksi Kasir</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("penjualan.pos.shifts") }}" class="nav-link">Riwayat Shift Kasir</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("returPenjualan.index") }}" class="nav-link">Retur Penjualan</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::PASIEN))
            <li class="nav-item">
                <a href="{{ route("pasien.index") }}"
                    class="nav-link {{ $patientMenuActive ? "active" : "" }}">
                    <i class="link-icon" data-feather="user-plus"></i>
                    <span class="link-title">Data Pasien</span>
                </a>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::PEMBELIAN))
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#pembelian" role="button"
                    aria-expanded="false" aria-controls="pembelian">
                    <i class="link-icon" data-feather="shopping-bag"></i>
                    <span class="link-title">Pembelian</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="pembelian">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("pembelian.pembelian") }}" class="nav-link">Pembelian</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("penerimaan.penerimaan") }}" class="nav-link">Penerimaan</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("faktur.faktur") }}" class="nav-link">Faktur</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("returPembelian.returPembelian") }}" class="nav-link">Retur Pembelian</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::DOKUMEN))
            <li class="nav-item">
                <a class="nav-link {{ $documentMenuActive ? "active" : "" }}" data-bs-toggle="collapse"
                    href="#dokumen" role="button" aria-expanded="{{ $documentMenuActive ? "true" : "false" }}"
                    aria-controls="dokumen">
                    <i class="link-icon" data-feather="file-text"></i>
                    <span class="link-title">Dokumen</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse {{ $documentMenuActive ? "show" : "" }}" id="dokumen">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route("dokumen.index") }}"
                                class="nav-link {{ $orderDocumentMenuActive ? "active" : "" }}">Surat Pesanan</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("dokumen.etiket.index") }}"
                                class="nav-link {{ $labelDocumentMenuActive ? "active" : "" }}">Etiket</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("dokumen.nota.index") }}"
                                class="nav-link {{ $receiptDocumentMenuActive ? "active" : "" }}">Nota</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::STOK))
            <li class="nav-item {{ $stockMenuActive ? 'active' : '' }}">
                <a class="nav-link {{ $stockMenuActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#stok" role="button"
                    aria-expanded="{{ $stockMenuActive ? 'true' : 'false' }}" aria-controls="stok">
                    <i class="link-icon" data-feather="database"></i>
                    <span class="link-title">Stok</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse {{ $stockMenuActive ? 'show' : '' }}" id="stok">
                    <ul class="nav sub-menu">
                        @if (! $stockMenuLock)
                            <li class="nav-item">
                                <a href="{{ route("stok.stok") }}" class="nav-link {{ request()->routeIs('stok.*') ? 'active' : '' }}">Stok Barang</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route("kartuStok.kartuStok") }}" class="nav-link {{ request()->routeIs('kartuStok.*') ? 'active' : '' }}">Kartu Stok</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <span class="nav-link text-warning" title="Dikunci oleh {{ $stockMenuLock->nomor }}">
                                    <i class="mdi mdi-lock-outline me-1"></i> Stok dikunci
                                </span>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('stockOpname.index') }}" class="nav-link {{ request()->routeIs('stockOpname.*') ? 'active' : '' }}">Stock Opname</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarHasReportsAndAnalysis)
                <li class="nav-item nav-category">Laporan &amp; Analisis</li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::ANALISIS_PERSEDIAAN))
            <li class="nav-item {{ $inventoryAnalysisMenuActive ? 'active' : '' }}">
                <a class="nav-link {{ $inventoryAnalysisMenuActive ? 'active' : '' }}" data-bs-toggle="collapse"
                    href="#analisis-persediaan" role="button"
                    aria-expanded="{{ $inventoryAnalysisMenuActive ? 'true' : 'false' }}"
                    aria-controls="analisis-persediaan">
                    <i class="link-icon" data-feather="bar-chart-2"></i>
                    <span class="link-title">Analisis Persediaan</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse {{ $inventoryAnalysisMenuActive ? 'show' : '' }}" id="analisis-persediaan">
                    <ul class="nav sub-menu">
                        @foreach (\App\Services\Menu\AnalisisPersediaan\InventoryAnalysisService::menuTypes() as $analysisSlug => $analysisMenu)
                            <li class="nav-item">
                                <a href="{{ route('analisisPersediaan.index', ['analysis' => $analysisSlug]) }}"
                                    class="nav-link {{ request()->routeIs('analisisPersediaan.*') && request()->route('analysis') === $analysisSlug ? 'active' : '' }}">
                                    {{ $analysisMenu['short_title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::ANALISIS_PENJUALAN))
            <li class="nav-item {{ $revenueAnalysisMenuActive ? 'active' : '' }}">
                <a class="nav-link {{ $revenueAnalysisMenuActive ? 'active' : '' }}" data-bs-toggle="collapse"
                    href="#analisis-penjualan" role="button"
                    aria-expanded="{{ $revenueAnalysisMenuActive ? 'true' : 'false' }}"
                    aria-controls="analisis-penjualan">
                    <i class="link-icon" data-feather="trending-up"></i>
                    <span class="link-title">Analisis Penjualan</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse {{ $revenueAnalysisMenuActive ? 'show' : '' }}" id="analisis-penjualan">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('analisisPenjualan.index', ['section' => 'tren']) }}#roTrendPanel" class="nav-link {{ $revenueAnalysisMenuActive && $salesAnalysisSection === 'tren' ? 'active' : '' }}">Tren Penjualan</a></li>
                        <li class="nav-item"><a href="{{ route('analisisPenjualan.index', ['section' => 'fast-moving']) }}#roFastMovingPanel" class="nav-link {{ $revenueAnalysisMenuActive && $salesAnalysisSection === 'fast-moving' ? 'active' : '' }}">Fast Moving</a></li>
                        <li class="nav-item"><a href="{{ route('analisisPenjualan.index', ['section' => 'produk-terlaris']) }}#roProductsPanel" class="nav-link {{ $revenueAnalysisMenuActive && $salesAnalysisSection === 'produk-terlaris' ? 'active' : '' }}">Produk Terlaris</a></li>
                        <li class="nav-item"><a href="{{ route('analisisPenjualan.index', ['section' => 'jam-ramai']) }}#roHourlyPanel" class="nav-link {{ $revenueAnalysisMenuActive && $salesAnalysisSection === 'jam-ramai' ? 'active' : '' }}">Jam Ramai</a></li>
                        <li class="nav-item"><a href="{{ route('analisisPenjualan.index', ['section' => 'market-basket']) }}#roMarketBasketPanel" class="nav-link {{ $revenueAnalysisMenuActive && $salesAnalysisSection === 'market-basket' ? 'active' : '' }}">Market Basket</a></li>
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::ANALISIS_PROFITABILITAS))
            <li class="nav-item {{ $profitabilityAnalysisMenuActive ? 'active' : '' }}">
                <a class="nav-link {{ $profitabilityAnalysisMenuActive ? 'active' : '' }}" data-bs-toggle="collapse"
                    href="#analisis-profitabilitas" role="button"
                    aria-expanded="{{ $profitabilityAnalysisMenuActive ? 'true' : 'false' }}"
                    aria-controls="analisis-profitabilitas">
                    <i class="link-icon" data-feather="dollar-sign"></i>
                    <span class="link-title">Analisis Profitabilitas</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse {{ $profitabilityAnalysisMenuActive ? 'show' : '' }}" id="analisis-profitabilitas">
                    <ul class="nav sub-menu">
                        <li class="nav-item"><a href="{{ route('analisisProfitabilitas.index', ['section' => 'profit-product']) }}#paProductPanel" class="nav-link {{ $profitabilityAnalysisMenuActive && $profitabilityAnalysisSection === 'profit-product' ? 'active' : '' }}">Profit per Produk</a></li>
                        <li class="nav-item"><a href="{{ route('analisisProfitabilitas.index', ['section' => 'margin']) }}#paMarginPanel" class="nav-link {{ $profitabilityAnalysisMenuActive && $profitabilityAnalysisSection === 'margin' ? 'active' : '' }}">Margin</a></li>
                        <li class="nav-item"><a href="{{ route('analisisProfitabilitas.index', ['section' => 'gmroi']) }}#paGmroiPanel" class="nav-link {{ $profitabilityAnalysisMenuActive && $profitabilityAnalysisSection === 'gmroi' ? 'active' : '' }}">GMROI</a></li>
                        <li class="nav-item"><a href="{{ route('analisisProfitabilitas.index', ['section' => 'top-profit-product']) }}#paTopProfitPanel" class="nav-link {{ $profitabilityAnalysisMenuActive && $profitabilityAnalysisSection === 'top-profit-product' ? 'active' : '' }}">Top Profit Product</a></li>
                    </ul>
                </div>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::ANALISIS_PENGADAAN))
            <li class="nav-item {{ $procurementAnalysisMenuActive ? 'active' : '' }}">
                <a href="{{ route('analisisPengadaan.index') }}"
                    class="nav-link {{ $procurementAnalysisMenuActive ? 'active' : '' }}">
                    <i class="link-icon" data-feather="activity"></i>
                    <span class="link-title">Analisis PO & Kebutuhan</span>
                </a>
            </li>
            @endif

            @if ($sidebarCan(\App\Support\SidebarPermissions::LAPORAN))
            <li class="nav-item {{ $reportMenuActive ? 'active' : '' }}">
                <a class="nav-link {{ $reportMenuActive ? 'active' : '' }}" data-bs-toggle="collapse"
                    href="#laporan" role="button" aria-expanded="{{ $reportMenuActive ? 'true' : 'false' }}"
                    aria-controls="laporan">
                    <i class="link-icon" data-feather="pie-chart"></i>
                    <span class="link-title">Laporan Operasional</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse {{ $reportMenuActive ? 'show' : '' }}" id="laporan">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('laporan.penjualan.index', ['report' => 'ringkasan']) }}"
                                class="nav-link {{ request()->routeIs('laporan.penjualan.*') ? 'active' : '' }}">
                                Detail Penjualan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('laporan.pembelian.index', ['report' => 'pembelian']) }}"
                                class="nav-link {{ request()->routeIs('laporan.pembelian.*') ? 'active' : '' }}">
                                Realisasi Pembelian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('laporan.persediaan.index', ['report' => 'posisi-stok']) }}"
                                class="nav-link {{ request()->routeIs('laporan.persediaan.*') ? 'active' : '' }}">
                                Stok & Audit
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

        </ul>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.querySelector(".medcare-sidebar");
        const searchInput = document.getElementById("navbarForm");

        if (!sidebar) return;

        const officialLogo = sidebar.querySelector(".sidebar-brand-logo");
        if (officialLogo) {
            const useFallbackLogo = function() {
                officialLogo.hidden = true;
                officialLogo.closest(".sidebar-brand-mark")?.classList.remove("has-official-logo");
            };

            officialLogo.addEventListener("error", useFallbackLogo, { once: true });
            if (officialLogo.complete && !officialLogo.naturalWidth) useFallbackLogo();
        }

        const normalizePath = function(value) {
            try {
                const path = new URL(value, window.location.origin).pathname.replace(/\/+$/, "");
                return path || "/";
            } catch (error) {
                return value;
            }
        };

        const currentPath = normalizePath(window.location.href);
        const navigableLinks = Array.from(sidebar.querySelectorAll("a.nav-link[href]"))
            .filter(link => !link.hasAttribute("data-bs-toggle"));

        navigableLinks.forEach(function(link) {
            if (normalizePath(link.href) !== currentPath) return;

            link.classList.add("active");
            const item = link.closest(".nav-item");
            item?.classList.add("active");

            const collapse = link.closest(".collapse");
            if (collapse) {
                collapse.classList.add("show");
                const parentToggle = collapse.previousElementSibling;
                parentToggle?.setAttribute("aria-expanded", "true");
                parentToggle?.closest(".nav-item")?.classList.add("active");
            }
        });

        const syncTogglerLabel = function() {
            const folded = document.body.classList.contains("sidebar-folded");
            sidebar.querySelectorAll(".sidebar-toggler").forEach(function(toggler) {
                toggler.setAttribute("aria-label", folded ? "Perluas sidebar" : "Ciutkan sidebar");
                toggler.setAttribute("title", folded ? "Perluas sidebar" : "Ciutkan sidebar");
            });
        };

        sidebar.querySelector(".sidebar-toggler")?.addEventListener("click", function() {
            window.setTimeout(syncTogglerLabel, 0);
        });
        syncTogglerLabel();

        if (!searchInput) return;

        const rootItems = Array.from(sidebar.querySelectorAll(".sidebar-menu > .nav-item"));
        const categories = rootItems.filter(item => item.classList.contains("nav-category"));

        searchInput.addEventListener("input", function() {
            const query = this.value.toLocaleLowerCase("id-ID").trim();

            rootItems.forEach(function(item) {
                if (item.classList.contains("nav-category")) return;

                const ownTitle = item.querySelector(":scope > .nav-link > .link-title")?.textContent || "";
                const ownMatch = ownTitle.toLocaleLowerCase("id-ID").includes(query);
                const subItems = Array.from(item.querySelectorAll(".sub-menu > .nav-item"));
                let childMatch = false;

                subItems.forEach(function(subItem) {
                    const matches = !query || ownMatch || subItem.textContent.toLocaleLowerCase("id-ID").includes(query);
                    subItem.classList.toggle("is-search-hidden", !matches);
                    childMatch = childMatch || matches;
                });

                const matches = !query || ownMatch || childMatch;
                item.classList.toggle("is-search-hidden", !matches);

                const collapse = item.querySelector(":scope > .collapse");
                if (collapse && query && matches) {
                    collapse.classList.add("show", "is-search-open");
                    item.querySelector(":scope > .nav-link")?.setAttribute("aria-expanded", "true");
                } else if (collapse && !query && collapse.classList.contains("is-search-open")) {
                    collapse.classList.remove("is-search-open");
                    if (!collapse.querySelector(".nav-link.active")) {
                        collapse.classList.remove("show");
                        item.querySelector(":scope > .nav-link")?.setAttribute("aria-expanded", "false");
                    }
                }
            });

            categories.forEach(function(category) {
                let sibling = category.nextElementSibling;
                let hasVisibleItem = false;

                while (sibling && !sibling.classList.contains("nav-category")) {
                    hasVisibleItem = hasVisibleItem || !sibling.classList.contains("is-search-hidden");
                    sibling = sibling.nextElementSibling;
                }

                category.classList.toggle("is-search-hidden", Boolean(query) && !hasVisibleItem);
            });
        });
    });
</script>

<!-- partial -->
