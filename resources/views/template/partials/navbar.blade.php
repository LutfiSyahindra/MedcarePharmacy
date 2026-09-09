@php
    $navbarUser = auth()->user();
    $navbarUserName = $navbarUser?->name ?: "Pengguna Medcare";
    $navbarUserRole = $navbarUser?->getRoleNames()->first() ?: "Team Member";
    $navbarUserInitials = collect(preg_split('/\s+/', trim($navbarUserName)))
        ->filter()
        ->take(2)
        ->map(fn($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode("");
    $navbarUnreadCount = $navbarUser?->unreadNotifications()->count() ?? 0;
@endphp

<nav class="navbar medcare-navbar" aria-label="Navigasi atas">
    <button type="button" class="sidebar-toggler medcare-navbar__menu" aria-label="Buka menu utama"
        title="Buka menu utama">
        <i data-feather="menu"></i>
    </button>

    <div class="navbar-content">
        <a href="{{ route("dashboard") }}" class="medcare-navbar__mobile-brand" aria-label="Medcare Pharmacy - Dashboard">
            <span class="medcare-navbar__mobile-brand-icon" aria-hidden="true">
                <i data-feather="activity"></i>
            </span>
            <span>
                <strong>Medcare</strong>
                <small>Pharmacy workspace</small>
            </span>
        </a>

        <form class="search-form medcare-navbar__search" role="search" onsubmit="return false;">
            <label class="visually-hidden" for="navbarForm">Cari menu</label>
            <div class="medcare-navbar__search-shell">
                <span class="medcare-navbar__search-icon" aria-hidden="true">
                    <i data-feather="search"></i>
                </span>
                <input type="search" class="form-control" id="navbarForm" placeholder="Cari menu atau halaman..."
                    autocomplete="off" spellcheck="false" aria-describedby="navbarSearchHint">
                <span class="medcare-navbar__search-hint" id="navbarSearchHint">Menu cepat</span>
                <kbd class="medcare-navbar__shortcut" aria-label="Control K">Ctrl K</kbd>
            </div>
        </form>

        <ul class="navbar-nav medcare-navbar__actions">
            <li class="nav-item medcare-navbar__status-item">
                <span class="medcare-navbar__status" title="Sistem siap digunakan">
                    <span class="medcare-navbar__status-dot" aria-hidden="true"></span>
                    <span>
                        <small>Status sistem</small>
                        <strong>Operasional</strong>
                    </span>
                </span>
            </li>

            <li class="nav-item dropdown medcare-navbar__action-item">
                <button type="button" class="nav-link dropdown-toggle medcare-navbar__icon-button"
                    id="notificationDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    aria-label="Buka notifikasi" title="Notifikasi">
                    <i data-feather="bell"></i>
                    <span class="medcare-navbar__icon-label">Notifikasi</span>
                    <span id="navbar-notif-badge" class="medcare-navbar__badge-wrap">
                        @if ($navbarUnreadCount)
                            <span class="badge rounded-pill">{{ $navbarUnreadCount }}</span>
                        @endif
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-end medcare-navbar__dropdown medcare-navbar__notification-menu p-0"
                    aria-labelledby="notificationDropdown">
                    <div class="medcare-navbar__dropdown-heading">
                        <span class="medcare-navbar__dropdown-heading-icon" aria-hidden="true">
                            <i data-feather="bell"></i>
                        </span>
                        <span class="medcare-navbar__dropdown-heading-copy">
                            <strong>Notifikasi</strong>
                            <small>Aktivitas terbaru apotek</small>
                        </span>
                        <button type="button" class="medcare-navbar__mark-read" onclick="markAllNotifRead()">
                            Tandai dibaca
                        </button>
                    </div>

                    <div id="navbar-notif-list" class="medcare-navbar__notification-list" aria-live="polite">
                        <div class="medcare-navbar__empty-state">
                            <span><i data-feather="loader"></i></span>
                            <p>Memuat notifikasi...</p>
                        </div>
                    </div>

                    <div class="medcare-navbar__dropdown-footer">
                        <a href="{{ route("notifikasi.SemuaNotifikasi") }}">
                            <span>Lihat semua notifikasi</span>
                            <i data-feather="arrow-right"></i>
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item dropdown medcare-navbar__profile-item">
                <button type="button" class="nav-link dropdown-toggle medcare-navbar__profile-trigger"
                    id="profileDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    aria-label="Buka menu akun {{ $navbarUserName }}">
                    <span class="medcare-navbar__avatar" aria-hidden="true">
                        <span>{{ $navbarUserInitials ?: "MP" }}</span>
                        @if ($navbarUser?->avatar)
                            <img src="{{ $navbarUser->avatar }}" alt="" onerror="this.remove()">
                        @endif
                    </span>
                    <span class="medcare-navbar__profile-copy">
                        <strong>{{ $navbarUserName }}</strong>
                        <small>{{ $navbarUserRole }}</small>
                    </span>
                    <span class="medcare-navbar__profile-chevron" aria-hidden="true">
                        <i data-feather="chevron-down"></i>
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-end medcare-navbar__dropdown medcare-navbar__profile-menu p-0"
                    aria-labelledby="profileDropdown">
                    <div class="medcare-navbar__profile-hero">
                        <span class="medcare-navbar__profile-orb medcare-navbar__profile-orb--one"></span>
                        <span class="medcare-navbar__profile-orb medcare-navbar__profile-orb--two"></span>
                        <div class="medcare-navbar__avatar medcare-navbar__avatar--large" aria-hidden="true">
                            <span>{{ $navbarUserInitials ?: "MP" }}</span>
                            @if ($navbarUser?->avatar)
                                <img src="{{ $navbarUser->avatar }}" alt="" onerror="this.remove()">
                            @endif
                        </div>
                        <div class="medcare-navbar__profile-hero-copy">
                            <small>Akun aktif</small>
                            <strong>{{ $navbarUserName }}</strong>
                            <span>{{ $navbarUser?->email }}</span>
                        </div>
                    </div>

                    <div class="medcare-navbar__account-meta">
                        <span class="medcare-navbar__account-icon"><i data-feather="shield"></i></span>
                        <span>
                            <small>Hak akses</small>
                            <strong>{{ $navbarUserRole }}</strong>
                        </span>
                        <span class="medcare-navbar__verified"><i data-feather="check"></i></span>
                    </div>

                    <div class="medcare-navbar__profile-links">
                        <a href="{{ route("dashboard") }}" class="medcare-navbar__profile-link">
                            <span><i data-feather="home"></i></span>
                            <span>
                                <strong>Dashboard</strong>
                                <small>Kembali ke ringkasan utama</small>
                            </span>
                            <i data-feather="chevron-right"></i>
                        </a>

                        <form method="POST" action="{{ route("logout") }}">
                            @csrf
                            <button type="submit" class="medcare-navbar__profile-link medcare-navbar__profile-link--logout">
                                <span><i data-feather="log-out"></i></span>
                                <span>
                                    <strong>Keluar</strong>
                                    <small>Akhiri sesi dengan aman</small>
                                </span>
                                <i data-feather="chevron-right"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('navbarForm');
        const searchShell = searchInput?.closest('.medcare-navbar__search-shell');

        searchInput?.addEventListener('focus', function() {
            searchShell?.classList.add('is-focused');
        });

        searchInput?.addEventListener('blur', function() {
            searchShell?.classList.remove('is-focused');
        });

        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                searchInput?.focus();
                searchInput?.select();
            }

            if (event.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.blur();
            }
        });
    });
</script>
