@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.menu.pasien.partials.style")
@endpush

@section("content")
    <div class="patient-page">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route("dashboard") }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Data Pasien</li>
            </ol>
        </nav>

        <header class="patient-hero">
            <div class="patient-hero-copy">
                <span class="patient-kicker"><i class="mdi mdi-account-heart-outline"></i> Data Kasir</span>
                <h1>Data Pasien</h1>
                <p>Simpan nama dan nomor telepon pasien agar dapat dicari dan dipilih langsung saat transaksi kasir.</p>
                <div class="patient-hero-actions">
                    <button type="button" class="btn btn-light" id="addPatientButton" @disabled($branches->isEmpty())>
                        <i class="mdi mdi-account-plus-outline me-1"></i>Tambah Pasien
                    </button>
                    <a href="{{ route("penjualan.pos") }}" class="btn btn-outline-light" target="_blank" rel="noopener noreferrer">
                        <i class="mdi mdi-point-of-sale me-1"></i>Buka Kasir
                    </a>
                </div>
            </div>
            <div class="patient-hero-visual" aria-hidden="true">
                <span class="patient-hero-icon"><i class="mdi mdi-account-sync-outline"></i></span>
                <div><strong>Terhubung ke kasir</strong><small>Pilih pasien untuk mengisi nama dan nomor HP otomatis</small></div>
                <div><strong>Tersimpan otomatis</strong><small>Pelanggan baru dari transaksi masuk ke daftar pasien</small></div>
            </div>
        </header>

        @if ($branches->isEmpty())
            <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                <i class="mdi mdi-alert-circle-outline fs-4"></i>
                <div><strong>Cabang belum tersedia.</strong> Hubungi administrator untuk menugaskan cabang aktif ke akun Anda.</div>
            </div>
        @endif

        <section class="patient-stats" aria-label="Ringkasan data pasien">
            <article>
                <span><i class="mdi mdi-account-group-outline"></i></span>
                <div><small>Total pasien</small><strong id="patientTotal">0</strong><p>di cabang yang dapat diakses</p></div>
            </article>
            <article class="is-filtered">
                <span><i class="mdi mdi-filter-check-outline"></i></span>
                <div><small>Hasil pencarian</small><strong id="patientFiltered">0</strong><p>sesuai filter aktif</p></div>
            </article>
            <article class="is-branch">
                <span><i class="mdi mdi-source-branch"></i></span>
                <div><small>Cabang tersedia</small><strong>{{ $branches->count() }}</strong><p>cakupan akses akun</p></div>
            </article>
        </section>

        <section class="patient-visit-panel" id="patientVisitAnalytics" aria-labelledby="patientVisitTitle">
            <div class="patient-visit-heading">
                <div>
                    <span class="patient-visit-kicker"><i class="mdi mdi-chart-timeline-variant"></i> Analitik pasien</span>
                    <h2 id="patientVisitTitle">Kunjungan Pasien</h2>
                    <p id="patientVisitRange">Memuat data kunjungan...</p>
                </div>
                <div class="patient-period-tabs" id="patientVisitPeriods" aria-label="Pilih periode kunjungan">
                    <button type="button" class="is-active" data-visit-period="week">Minggu</button>
                    <button type="button" data-visit-period="month">Bulan</button>
                    <button type="button" data-visit-period="year">Tahun</button>
                </div>
            </div>

            <div class="patient-visit-summary">
                <article>
                    <span><i class="mdi mdi-calendar-check-outline"></i></span>
                    <div><small>Total kunjungan</small><strong id="patientVisitTotal">0</strong><p>transaksi pasien selesai</p></div>
                </article>
                <article class="is-unique">
                    <span><i class="mdi mdi-account-multiple-check-outline"></i></span>
                    <div><small>Pasien berkunjung</small><strong id="patientVisitUnique">0</strong><p>pasien unik pada periode ini</p></div>
                </article>
                <article class="is-repeat">
                    <span><i class="mdi mdi-account-sync-outline"></i></span>
                    <div><small>Kunjungan ulang</small><strong id="patientVisitRepeat">0</strong><p>kunjungan setelah kedatangan pertama</p></div>
                </article>
            </div>

            <div class="patient-visit-content">
                <div class="patient-visit-chart-shell">
                    <div class="patient-subheading"><strong>Tren kunjungan</strong><small>Jumlah transaksi pasien selesai</small></div>
                    <div id="patientVisitChart" class="patient-visit-chart" aria-label="Grafik tren kunjungan pasien"></div>
                </div>
                <aside class="patient-top-visitors">
                    <div class="patient-subheading"><strong>Pasien teratas</strong><small>Paling sering berkunjung</small></div>
                    <div id="patientTopVisitors"></div>
                </aside>
            </div>
            <div class="patient-visit-error" id="patientVisitError" hidden></div>
        </section>

        <section class="patient-panel">
            <div class="patient-panel-heading">
                <div>
                    <h2>Daftar Pasien</h2>
                    <p>Cari pasien berdasarkan nama atau nomor telepon.</p>
                </div>
                <button type="button" class="btn btn-primary" id="addPatientToolbar" @disabled($branches->isEmpty())>
                    <i class="mdi mdi-plus-circle-outline me-1"></i>Pasien Baru
                </button>
            </div>

            <div class="patient-toolbar">
                <label class="patient-search" for="patientSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="patientSearch" placeholder="Cari nama atau nomor telepon..." autocomplete="off">
                </label>
                <select id="patientBranchFilter" class="form-select" aria-label="Filter cabang">
                    <option value="">Semua cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($selectedBranchId === (int) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-secondary" id="resetPatientFilter">Reset</button>
            </div>

            <div class="table-responsive">
                <table id="patientTable" class="table patient-table align-middle">
                    <thead><tr>
                        <th>Nama Pasien</th>
                        <th>Nomor Telepon</th>
                        <th>Cabang</th>
                        <th>Terakhir Diperbarui</th>
                        <th aria-label="Aksi"></th>
                    </tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="modal fade patient-modal" id="patientModal" tabindex="-1" aria-labelledby="patientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="patientForm" novalidate>
                    @csrf
                    <div class="modal-header">
                        <div class="patient-modal-title">
                            <span><i class="mdi mdi-account-plus-outline"></i></span>
                            <div>
                                <small>Data pasien kasir</small>
                                <h5 class="modal-title" id="patientModalLabel">Tambah Pasien</h5>
                                <p id="patientModalSubtitle">Isi nama dan nomor telepon pasien.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="patientId">
                        <div class="patient-form-section mb-0">
                            <div class="patient-section-heading">
                                <span>01</span><div><h6>Identitas Pasien</h6><p>Kedua data ini akan tersedia pada pencarian pasien di kasir.</p></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="patientBranchId" class="form-label">Cabang <b>*</b></label>
                                    <select class="form-select" id="patientBranchId" name="branch_id" required>
                                        <option value="">Pilih cabang</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="patientName" class="form-label">Nama Pasien <b>*</b></label>
                                    <input type="text" maxlength="150" class="form-control" id="patientName" name="name" placeholder="Nama lengkap pasien" required autocomplete="name">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="patientPhone" class="form-label">Nomor Telepon <b>*</b></label>
                                    <input type="tel" maxlength="20" class="form-control" id="patientPhone" name="phone" placeholder="Contoh: 081234567890" required autocomplete="tel" inputmode="tel">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span class="patient-required-note"><b>*</b> Wajib diisi</span>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="savePatientButton"><i class="mdi mdi-content-save-outline me-1"></i>Simpan Pasien</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.pasien.partials.script")
@endpush
