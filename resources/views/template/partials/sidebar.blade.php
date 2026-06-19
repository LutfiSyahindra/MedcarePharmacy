<!-- partial:partials/_sidebar.html -->
<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            Medcare<span>Phar</span>
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            {{-- Main --}}
            <li class="nav-item nav-category">Main</li>
            <li class="nav-item">
                <a href="dashboard.html" class="nav-link">
                    <i class="link-icon" data-feather="box"></i>
                    <span class="link-title">Dashboard</span>
                </a>
            </li>

            {{-- Settings --}}
            <li class="nav-item nav-category">Settings</li>
            @can("MEDCARE.SETTINGS.AUTH")
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
            @endcan
            @can("MEDCARE.SETTINGS.BRANCH")
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
            @endcan
            @can("MEDCARE.SETTINGS.MARGIN")
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
            @endcan

            {{-- Master Data --}}
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
                        <li class="nav-item">
                            <a href="{{ route("kategori.mainKategori") }}" class="nav-link">Main Kategori</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route("kategori.subKategori") }}" class="nav-link">Sub Kategori</a>
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
                    <span class="link-title">Pabrikan / Peoduksi</span>
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

            {{-- Menu --}}
            <li class="nav-item nav-category">Menu</li>
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
                            <a href="{{ route("notifikasi.SemuaNotifikasi") }}" class="nav-link">
                                📩 Semua Notifikasi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

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
                    </ul>
                </div>
            </li>

        </ul>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let searchInput = document.getElementById("navbarForm");
        if (!searchInput) return; // kalau elemen ga ada, hentikan

        searchInput.addEventListener("keyup", function() {
            let query = this.value.toLowerCase().trim();
            let menuItems = document.querySelectorAll(".sidebar-body .nav-item");

            menuItems.forEach(function(item) {
                let text = item.innerText.toLowerCase();

                if (item.classList.contains("nav-category")) {
                    if (query === "") {
                        item.style.display = "";
                    } else {
                        let nextMenu = item.nextElementSibling;
                        if (nextMenu && nextMenu.style.display !== "none") {
                            item.style.display = "";
                        } else {
                            item.style.display = "none";
                        }
                    }
                } else {
                    if (query === "") {
                        item.style.display = "";
                    } else if (text.includes(query)) {
                        item.style.display = "";
                        let parentCollapse = item.closest(".collapse");
                        if (parentCollapse) {
                            parentCollapse.classList.add("show");
                        }
                    } else {
                        item.style.display = "none";
                    }
                }
            });
        });
    });
</script>

<!-- partial -->
