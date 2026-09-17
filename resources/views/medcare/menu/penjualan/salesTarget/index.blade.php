@extends('template.partials.app')

@push('style')
    @include('template.AddOn.mdiicon')
    @include('template.AddOn.sweetAlert')
    @include('medcare.menu.penjualan.salesTarget.partials.style')
@endpush

@section('content')
    <main class="sales-target-page"
        id="salesTargetApp"
        data-url="{{ route('penjualan.targets.data') }}"
        data-store-url="{{ route('penjualan.targets.store') }}"
        data-destroy-url="{{ route('penjualan.targets.destroy', ['target' => '__TARGET__']) }}"
        data-default-year="{{ $defaultYear }}"
        data-default-branch="{{ $defaultBranchId }}">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penjualan.pos.history') }}">Penjualan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Target &amp; Pencapaian</li>
            </ol>
        </nav>

        <header class="st-hero">
            <div class="st-hero-copy">
                <span class="st-eyebrow"><i class="mdi mdi-bullseye-arrow"></i> Sales performance</span>
                <h1>Target &amp; Pencapaian Penjualan</h1>
                <p>Atur sasaran omzet setiap bulan dan pantau realisasinya langsung dari transaksi penjualan.</p>
                <div class="st-hero-meta">
                    <span><i class="mdi mdi-store-outline"></i><strong id="stBranchLabel">Memuat cabang...</strong></span>
                    <span><i class="mdi mdi-calendar-range"></i>Tahun <strong id="stYearLabel">{{ $defaultYear }}</strong></span>
                    <span><i class="mdi mdi-sync"></i>Diperbarui <strong id="stUpdatedAt">-</strong></span>
                </div>
            </div>
            <div class="st-hero-actions">
                <button type="button" class="btn st-secondary-button" id="stRefreshButton" title="Muat ulang data">
                    <i class="mdi mdi-refresh"></i><span>Muat Ulang</span>
                </button>
                <button type="button" class="btn st-primary-button" id="stOpenTargetButton" @disabled($branches->isEmpty())>
                    <i class="mdi mdi-plus-circle-outline"></i>Atur Target
                </button>
            </div>
        </header>

        <section class="st-filter-bar" aria-label="Filter target penjualan">
            <div class="st-filter-heading">
                <span><i class="mdi mdi-tune-variant"></i></span>
                <div><strong>Periode pemantauan</strong><small>Ringkasan akan mengikuti tahun dan cabang terpilih.</small></div>
            </div>
            <label>
                <span>Tahun</span>
                <select id="stYearFilter" class="form-select">
                    @for ($year = $defaultYear + 2; $year >= $defaultYear - 5; $year--)
                        <option value="{{ $year }}" @selected($year === $defaultYear)>{{ $year }}</option>
                    @endfor
                </select>
            </label>
            <label>
                <span>Cabang</span>
                <select id="stBranchFilter" class="form-select">
                    @if ($branches->count() > 1)
                        <option value="">Semua cabang</option>
                    @endif
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) $branch->id === (int) $defaultBranchId)>
                            {{ $branch->name }}{{ $branch->code ? ' · '.$branch->code : '' }}
                        </option>
                    @endforeach
                </select>
            </label>
        </section>

        @if ($branches->isEmpty())
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="mdi mdi-alert-outline fs-4"></i>
                <span>Akun ini belum memiliki akses cabang. Hubungi administrator sebelum mengatur target penjualan.</span>
            </div>
        @endif

        <section class="st-summary-grid" aria-label="Ringkasan target tahunan">
            <article class="st-summary-card is-target">
                <span class="st-summary-icon"><i class="mdi mdi-bullseye"></i></span>
                <div><small>Target tahunan</small><strong id="stTargetTotal">Rp 0</strong><span id="stTargetCoverage">0 target tersimpan</span></div>
            </article>
            <article class="st-summary-card is-realization">
                <span class="st-summary-icon"><i class="mdi mdi-chart-line"></i></span>
                <div><small>Realisasi bersih</small><strong id="stRealizationTotal">Rp 0</strong><span>Penjualan selesai dikurangi retur</span></div>
            </article>
            <article class="st-summary-card is-achievement">
                <span class="st-summary-icon"><i class="mdi mdi-progress-check"></i></span>
                <div><small>Pencapaian</small><strong id="stAchievementTotal">0%</strong><span id="stAchievedMonths">0 bulan mencapai target</span></div>
            </article>
            <article class="st-summary-card is-remaining">
                <span class="st-summary-icon"><i class="mdi mdi-chart-timeline-variant-shimmer"></i></span>
                <div><small>Sisa target</small><strong id="stRemainingTotal">Rp 0</strong><span id="stConfiguredMonths">0 dari 12 bulan diatur</span></div>
            </article>
        </section>

        <section class="st-overview-grid">
            <article class="st-panel st-progress-panel">
                <div class="st-panel-heading">
                    <div><span>PROGRES TAHUNAN</span><h2>Target vs realisasi</h2><p>Akumulasi sesuai cabang dan tahun aktif.</p></div>
                    <strong id="stProgressPercent">0%</strong>
                </div>
                <div class="st-main-progress"><span id="stProgressBar"></span></div>
                <div class="st-progress-scale"><span>Rp 0</span><span id="stProgressGoal">Target Rp 0</span></div>
                <div class="st-legend">
                    <span><i class="is-target"></i>Target</span>
                    <span><i class="is-realization"></i>Realisasi bersih</span>
                    <span><i class="is-return"></i>Retur posted</span>
                </div>
            </article>

            <article class="st-panel st-current-panel">
                <div class="st-panel-heading">
                    <div><span>BULAN AKTIF</span><h2 id="stCurrentMonth">-</h2><p id="stCurrentMessage">Memuat pencapaian bulan berjalan...</p></div>
                    <span class="st-status is-unset" id="stCurrentStatus">Belum Diatur</span>
                </div>
                <div class="st-current-values">
                    <div><small>Target</small><strong id="stCurrentTarget">Rp 0</strong></div>
                    <div><small>Realisasi</small><strong id="stCurrentRealization">Rp 0</strong></div>
                    <div><small>Sisa</small><strong id="stCurrentRemaining">Rp 0</strong></div>
                </div>
            </article>
        </section>

        <section class="st-panel st-monthly-panel">
            <div class="st-panel-heading st-table-heading">
                <div><span>MONITORING BULANAN</span><h2>Performa 12 bulan</h2><p>Nilai realisasi diperbarui otomatis saat transaksi selesai atau retur diposting.</p></div>
                <div class="st-table-note"><i class="mdi mdi-information-outline"></i>Persentase dapat melebihi 100% ketika realisasi melampaui target.</div>
            </div>
            <div class="table-responsive">
                <table class="table st-table align-middle">
                    <thead><tr>
                        <th>Bulan</th>
                        <th class="text-end">Target</th>
                        <th class="text-end">Realisasi Bersih</th>
                        <th class="text-end">Retur</th>
                        <th>Pencapaian</th>
                        <th class="text-end">Selisih</th>
                        <th>Status</th>
                        <th aria-label="Aksi"></th>
                    </tr></thead>
                    <tbody id="stMonthlyBody">
                        <tr><td colspan="8" class="st-empty"><span class="spinner-border spinner-border-sm"></span> Memuat target penjualan...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="st-panel st-branch-panel" id="stBranchPanel">
            <div class="st-panel-heading st-table-heading">
                <div><span>PERFORMA CABANG</span><h2>Ringkasan per cabang</h2><p>Bandingkan kontribusi target dan pencapaian selama setahun.</p></div>
            </div>
            <div class="table-responsive">
                <table class="table st-table align-middle">
                    <thead><tr><th>Cabang</th><th class="text-end">Target</th><th class="text-end">Realisasi</th><th>Pencapaian</th><th class="text-center">Bulan Diatur</th><th class="text-center">Bulan Tercapai</th></tr></thead>
                    <tbody id="stBranchBody"></tbody>
                </table>
            </div>
        </section>
    </main>

    <div class="modal fade" id="salesTargetModal" tabindex="-1" aria-labelledby="salesTargetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content st-modal">
                <form id="salesTargetForm">
                    <div class="modal-header">
                        <div class="st-modal-title"><span><i class="mdi mdi-bullseye-arrow"></i></span><div><small>Target bulanan</small><h5 class="modal-title" id="salesTargetModalLabel">Atur Target Penjualan</h5></div></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="st-form-grid">
                            <label><span>Cabang <b>*</b></span><select id="stTargetBranch" class="form-select" required>
                                <option value="">Pilih cabang</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}{{ $branch->code ? ' · '.$branch->code : '' }}</option>
                                @endforeach
                            </select></label>
                            <label><span>Bulan target <b>*</b></span><input type="month" id="stTargetPeriod" class="form-control" required></label>
                            <label class="is-wide"><span>Nominal target <b>*</b></span><div class="st-money-input"><span>Rp</span><input type="number" id="stTargetAmount" class="form-control" min="0" step="1000" placeholder="0" required></div><small>Gunakan omzet bersih yang ingin dicapai pada bulan tersebut.</small></label>
                        </div>
                        <div class="st-form-info"><i class="mdi mdi-calculator-variant-outline"></i><span>Realisasi dihitung dari total transaksi berstatus selesai, lalu dikurangi retur berstatus posted pada bulan yang sama.</span></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger me-auto" id="stDeleteTarget" hidden><i class="mdi mdi-trash-can-outline me-1"></i>Hapus</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn st-save-button"><i class="mdi mdi-content-save-outline me-1"></i>Simpan Target</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('medcare.menu.penjualan.salesTarget.partials.script')
@endpush
