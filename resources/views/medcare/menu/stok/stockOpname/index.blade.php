@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("medcare.menu.stok.stockOpname.partials.style")
@endpush

@section("content")
    @php($hasControlAccess = $canAuthorize || $canValidate)
    <div class="opname-page">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('stok.stok') }}">Stok</a></li>
                <li class="breadcrumb-item active" aria-current="page">Stock Opname</li>
            </ol>
        </nav>

        <section class="opname-toolbar">
            <div class="opname-title">
                <span class="opname-title-icon"><i class="mdi mdi-clipboard-check-multiple-outline"></i></span>
                <div>
                    <span class="opname-eyebrow">MEDCARE INVENTORY INTELLIGENCE</span>
                    <h4>Stock Opname</h4>
                    <p>Hitung seluruh obat, kendalikan selisih, dan pertahankan jejak audit setiap perubahan stok.</p>
                </div>
            </div>
            <div class="opname-toolbar-actions">
                <span class="opname-access-chip {{ $hasControlAccess ? 'is-control' : 'is-counter' }}">
                    <i class="mdi {{ $hasControlAccess ? 'mdi-shield-crown-outline' : 'mdi-eye-off-outline' }}"></i>
                    <span><small>Akses saat ini</small>{{ $canAuthorize ? 'Control Center' : ($canValidate ? 'Petugas Opname' : 'Blind Count Mode') }}</span>
                </span>
                @if (! $stockMenuLock)
                    <a href="{{ route('kartuStok.kartuStok') }}" class="btn btn-outline-primary"><i class="mdi mdi-card-bulleted-outline"></i> Kartu Stok</a>
                @endif
                @if ($canAuthorize)
                    <button type="button" class="btn btn-primary" id="createOpnameButton"><i class="mdi mdi-plus-circle-outline"></i> Buat Stock Opname</button>
                @endif
            </div>
        </section>

        @if ($stockMenuLock)
            <section class="opname-lock-banner">
                <span class="opname-lock-icon"><i class="mdi mdi-lock-check-outline"></i></span>
                <div>
                    <span class="opname-eyebrow">SESI PENGHITUNGAN TERLINDUNGI</span>
                    <strong>Stok, Kartu Stok, dan Kasir/POS sementara dikunci</strong>
                    <p>{{ $stockMenuLock->nomor }} sedang berlangsung di {{ $stockMenuLock->branch?->name }}. Semua modul otomatis dibuka setelah stok fisik pertama kali disubmit.</p>
                </div>
                <span class="opname-live-pill"><i></i> AKTIF</span>
            </section>
        @endif

        <section class="opname-insight-strip" aria-label="Ringkasan operasional stock opname">
            <div class="opname-insight-main">
                <span class="opname-insight-icon"><i class="mdi mdi-chart-timeline-variant-shimmer"></i></span>
                <div class="opname-insight-copy">
                    <small>Kondisi operasional</small>
                    <strong id="opnameOperationalText" aria-live="polite">Menyiapkan ringkasan opname</strong>
                    <span id="opnameOperationalSubtext">Data dokumen sedang dimuat.</span>
                    <div class="opname-health-track" aria-hidden="true"><span id="opnameOperationalBar" style="width:0%"></span></div>
                </div>
                <span class="opname-health-badge" id="opnameOperationalBadge">Memuat</span>
            </div>
            <div class="opname-insight-metrics">
                <div class="opname-insight-metric is-blue"><small>Sesi aktif</small><strong id="opnameActiveInsight">0</strong><span>Sedang dihitung</span></div>
                <div class="opname-insight-metric is-amber"><small>Antrean review</small><strong id="opnameReviewInsight">0</strong><span>Perlu tindakan</span></div>
                <div class="opname-insight-metric is-green"><small>Dokumen selesai</small><strong id="opnameCompleteInsight">0</strong><span>Stok disesuaikan</span></div>
            </div>
            <div class="opname-policy-card">
                <span><i class="mdi {{ $hasControlAccess ? 'mdi-shield-check-outline' : 'mdi-eye-lock-outline' }}"></i></span>
                <div>
                    <small>{{ $hasControlAccess ? 'Kontrol & kepatuhan' : 'Integritas blind count' }}</small>
                    <strong>{{ $hasControlAccess ? 'Workflow berlapis dan terlacak' : 'Stok sistem dirahasiakan' }}</strong>
                    <p>{{ $hasControlAccess ? 'Setiap otorisasi, alasan selisih, validasi transaksi, persetujuan, dan posting tercatat dalam audit trail.' : 'Masukkan hasil fisik sesuai kondisi rak. Stok sistem baru dibuka setelah submit pertama.' }}</p>
                </div>
            </div>
        </section>

        <section class="opname-flow" aria-label="Alur stock opname">
            <div class="opname-flow-heading">
                <span><i class="mdi mdi-transit-connection-variant"></i></span>
                <div><strong>Alur terkendali</strong><small>Blind count terkunci, lalu transaksi berjalan direkonsiliasi otomatis.</small></div>
            </div>
            <div class="opname-flow-track">
                <div class="opname-flow-step"><span>1</span><div><strong>Mulai & Kunci</strong><small>Stok dan POS berhenti</small></div></div><i class="mdi mdi-chevron-right"></i>
                <div class="opname-flow-step"><span>2</span><div><strong>Blind Count</strong><small>Hitung tanpa stok sistem</small></div></div><i class="mdi mdi-chevron-right"></i>
                <div class="opname-flow-step"><span>3</span><div><strong>Review Selisih</strong><small>Operasional dibuka</small></div></div><i class="mdi mdi-chevron-right"></i>
                <div class="opname-flow-step"><span>4</span><div><strong>Validasi</strong><small>Rekonsiliasi transaksi</small></div></div><i class="mdi mdi-chevron-right"></i>
                <div class="opname-flow-step"><span>5</span><div><strong>Posting</strong><small>Summary & kartu stok</small></div></div>
            </div>
        </section>

        <section class="opname-stat-grid" aria-label="Jumlah dokumen berdasarkan status">
            <article class="opname-stat is-slate"><span class="opname-stat-icon"><i class="mdi mdi-file-document-edit-outline"></i></span><div><small>Persiapan</small><strong id="summaryDraft">0</strong><span>Dokumen draft</span></div></article>
            <article class="opname-stat is-blue"><span class="opname-stat-icon"><i class="mdi mdi-counter"></i></span><div><small>Aktif</small><strong id="summaryCounting">0</strong><span>Sedang dihitung</span></div></article>
            <article class="opname-stat is-amber"><span class="opname-stat-icon"><i class="mdi mdi-shield-search-outline"></i></span><div><small>Kontrol</small><strong id="summaryWaiting">0</strong><span>Menunggu review</span></div></article>
            <article class="opname-stat is-indigo"><span class="opname-stat-icon"><i class="mdi mdi-check-decagram-outline"></i></span><div><small>Disetujui</small><strong id="summaryApproved">0</strong><span>Siap disesuaikan</span></div></article>
            <article class="opname-stat is-green"><span class="opname-stat-icon"><i class="mdi mdi-database-check-outline"></i></span><div><small>Selesai</small><strong id="summaryAdjusted">0</strong><span>Stok telah diposting</span></div></article>
        </section>

        <section class="opname-filter-bar">
            <div class="opname-filter-copy">
                <span><i class="mdi mdi-tune-variant"></i></span>
                <div><strong>Temukan dokumen</strong><small>Gunakan pencarian atau persempit berdasarkan cabang dan status.</small></div>
            </div>
            <div class="opname-filter-controls">
                <div class="opname-search"><i class="mdi mdi-magnify"></i><input type="search" id="opnameSearch" placeholder="Cari nomor, cabang, rak, petugas..." aria-label="Cari dokumen stock opname" autocomplete="off"><button type="button" id="clearOpnameSearch" aria-label="Hapus pencarian"><i class="mdi mdi-close"></i></button></div>
                <select id="opnameBranchFilter" class="form-select" aria-label="Filter cabang">
                    <option value="">Semua cabang</option>
                    @foreach ($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>@endforeach
                </select>
                <select id="opnameStatusFilter" class="form-select" aria-label="Filter status">
                    <option value="">Semua status</option><option value="draft">Draft</option><option value="counting">Proses Penghitungan</option><option value="awaiting_verification">Menunggu Verifikasi</option><option value="awaiting_approval">Menunggu Persetujuan</option><option value="approved">Disetujui</option><option value="adjusted">Penyesuaian Stok</option>
                </select>
                <button type="button" class="btn btn-outline-secondary opname-refresh" id="refreshOpnameTable" title="Muat ulang data" aria-label="Muat ulang data"><i class="mdi mdi-refresh"></i></button>
            </div>
        </section>

        <section class="opname-table-card">
            <div class="opname-card-header">
                <div class="opname-card-title"><span><i class="mdi mdi-file-document-multiple-outline"></i></span><div><h5>Dokumen Stock Opname</h5><p>Seluruh dokumen yang dapat diakses berdasarkan penugasan cabang Anda.</p></div></div>
                <span class="opname-visible-chip"><i class="mdi mdi-eye-check-outline"></i><strong id="opnameTableInfo">Memuat data...</strong></span>
            </div>
            <div class="table-responsive opname-table-wrap">
                <table id="stockOpnameTable" class="table align-middle"><thead><tr><th>No</th><th>Dokumen</th><th>Lokasi</th><th>Operasional</th><th>Progress Hitung</th><th>Status</th><th>Aksi</th></tr></thead><tbody></tbody></table>
            </div>
        </section>
    </div>

    @if ($canAuthorize)
        <div class="modal fade" id="opnameFormModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <form id="opnameForm" class="modal-content opname-modal">
                    <div class="modal-header">
                        <div class="opname-modal-heading"><span><i class="mdi mdi-clipboard-edit-outline"></i></span><div><small>PERENCANAAN SESI</small><h5 class="modal-title" id="opnameFormTitle">Buat Stock Opname</h5><p>Konfigurasi area, tanggal, dan kebijakan transaksi selama penghitungan.</p></div></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                            <input type="hidden" id="opnameFormId">
                            <div class="opname-form-notice"><i class="mdi mdi-information-slab-circle-outline"></i><div><strong>Satu area hanya dapat memiliki satu opname aktif</strong><span>Sistem mengambil snapshot seluruh obat saat sesi diotorisasi, bukan saat draft dibuat.</span></div></div>
                            <section class="opname-form-section">
                                <div class="opname-form-section-title"><span><i class="mdi mdi-map-marker-radius-outline"></i></span><div><strong>Area penghitungan</strong><small>Tentukan kapan dan di mana stok akan dihitung.</small></div></div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Tanggal Opname <span>*</span></label><input type="date" class="form-control" name="tanggal_opname" id="opnameDate" required></div>
                                    <div class="col-md-6"><label class="form-label">Cabang <span>*</span></label><select class="form-select" name="branch_id" id="opnameBranch" required><option value="">Pilih cabang</option>@foreach ($authorizationBranches as $branch)<option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>@endforeach</select></div>
                                    <div class="col-12"><label class="form-label">Lokasi / Rak</label><select class="form-select" name="rak_id" id="opnameRack"><option value="">Semua rak pada cabang</option>@foreach ($racks as $rack)<option value="{{ $rack->id }}">{{ $rack->kode }} - {{ $rack->nama }}{{ $rack->lokasi ? ' · '.$rack->lokasi : '' }}</option>@endforeach</select><small class="field-help"><i class="mdi mdi-lightbulb-on-outline"></i>Pilih satu rak untuk opname parsial atau semua rak untuk penghitungan cabang menyeluruh.</small></div>
                                </div>
                            </section>
                            <section class="opname-form-section">
                                <div class="opname-form-section-title"><span><i class="mdi mdi-lock-check-outline"></i></span><div><strong>Kebijakan transaksi otomatis</strong><small>Tidak perlu memilih mode secara manual.</small></div></div>
                                <div class="opname-form-notice"><i class="mdi mdi-store-lock-outline"></i><div><strong>Stok dan POS dikunci hanya selama blind count</strong><span>Setelah hasil fisik disubmit pertama kali, stok sistem ditampilkan dan operasional dibuka. Semua mutasi berikutnya direkonsiliasi otomatis saat validasi dan posting.</span></div></div>
                            </section>
                            <section class="opname-form-section">
                                <div class="opname-form-section-title"><span><i class="mdi mdi-note-text-outline"></i></span><div><strong>Instruksi petugas</strong><small>Tambahkan konteks agar penghitungan konsisten.</small></div></div>
                                <label class="form-label">Catatan</label><textarea class="form-control" name="catatan" id="opnameNote" rows="3" maxlength="2000" placeholder="Contoh: mulai dari rak paling kiri, pisahkan obat rusak, laporkan batch yang belum tercatat..."></textarea>
                            </section>
                    </div>
                    <div class="modal-footer"><span class="opname-footer-security"><i class="mdi mdi-shield-lock-outline"></i> Tindakan berikutnya memerlukan otorisasi terpisah</span><div><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="saveOpnameButton"><i class="mdi mdi-content-save-outline"></i> Simpan Draft</button></div></div>
                </form>
            </div>
        </div>
    @endif

    <div class="modal fade" id="opnameDetailModal" tabindex="-1" aria-labelledby="detailNumber" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-xl-down modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content opname-modal opname-detail-modal">
                <div class="modal-header opname-control-header">
                    <div class="opname-detail-heading">
                        <span class="opname-detail-icon"><i class="mdi mdi-file-certificate-outline"></i></span>
                        <div>
                            <small>STOCK OPNAME · CONTROL DOCUMENT</small>
                            <h5 class="modal-title" id="detailNumber">Detail Stock Opname</h5>
                            <p id="detailSubtitle">Memuat informasi dokumen...</p>
                        </div>
                    </div>
                    <div class="opname-header-actions">
                        <div class="opname-header-status" id="detailHeaderStatus"><span class="opname-status-dot"></span> Menyiapkan status</div>
                        <button type="button" class="opname-modal-close" data-bs-dismiss="modal" aria-label="Tutup control document"><i class="mdi mdi-close"></i></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="detailLoading" class="opname-loading">
                        <span class="opname-loading-orbit"><i class="mdi mdi-clipboard-text-search-outline"></i></span>
                        <strong>Menyiapkan control document</strong>
                        <small>Menyelaraskan hasil hitung, rekonsiliasi, dan jejak audit.</small>
                        <span class="opname-loading-line"><i></i></span>
                    </div>
                    <div id="detailContent" class="d-none">
                        <section class="opname-control-overview" aria-label="Ringkasan control document">
                            <article class="opname-stage-card">
                                <div class="opname-stage-top">
                                    <span class="opname-stage-icon" id="detailStageIcon"><i class="mdi mdi-file-document-edit-outline"></i></span>
                                    <div class="opname-stage-copy">
                                        <small id="detailStageEyebrow">TAHAP DOKUMEN</small>
                                        <h6 id="detailStageTitle">Menyiapkan status</h6>
                                        <p id="detailStageDescription">Informasi workflow sedang dimuat.</p>
                                    </div>
                                    <div class="opname-progress-ring" id="detailProgressRing" style="--progress:0deg" aria-label="Progress penghitungan 0 persen">
                                        <span id="detailProgressPercent">0%</span><small>terisi</small>
                                    </div>
                                </div>
                                <div class="opname-stage-progress">
                                    <div><span>Progress penghitungan</span><strong id="detailStageCount">0 dari 0 item</strong></div>
                                    <span class="opname-stage-progress-track"><i id="detailStageProgress" style="width:0%"></i></span>
                                </div>
                                <div class="opname-document-facts" id="detailDocumentFacts"></div>
                                <div class="opname-document-note d-none" id="detailDocumentNote"><i class="mdi mdi-note-text-outline"></i><div><small>CATATAN PENGHITUNGAN</small><p></p></div></div>
                            </article>
                            <section class="opname-metric-panel">
                                <div class="opname-panel-heading">
                                    <div><small>EXECUTIVE SNAPSHOT</small><strong>Kondisi dokumen saat ini</strong></div>
                                    <span><i class="mdi mdi-chart-box-outline"></i> Live overview</span>
                                </div>
                                <div class="opname-detail-summary" id="detailSummary"></div>
                            </section>
                        </section>

                        <section class="opname-workflow-card" aria-label="Tahapan workflow stock opname">
                            <div class="opname-panel-heading">
                                <div><small>WORKFLOW CONTROL</small><strong>Jejak proses dan posisi dokumen</strong></div>
                                <span id="detailWorkflowHint"><i class="mdi mdi-shield-check-outline"></i> Proses terkendali</span>
                            </div>
                            <div class="opname-detail-flow" id="detailFlow"></div>
                        </section>

                        <section class="opname-workbench">
                            <div class="opname-workbench-head">
                                <div><small>DOCUMENT WORKSPACE</small><strong>Data operasional & bukti kontrol</strong></div>
                                <ul class="nav nav-tabs opname-tabs" role="tablist" aria-label="Bagian control document">
                                    <li class="nav-item" role="presentation"><button class="nav-link active" id="countTabButton" data-bs-toggle="tab" data-bs-target="#countTab" type="button" role="tab" aria-controls="countTab" aria-selected="true"><i class="mdi mdi-counter"></i><span class="opname-tab-label">Hasil Hitung</span><b id="detailCountBadge">0</b></button></li>
                                    <li class="nav-item d-none" id="summaryTabNav" role="presentation"><button class="nav-link" id="summaryTabButton" data-bs-toggle="tab" data-bs-target="#summaryTab" type="button" role="tab" aria-controls="summaryTab" aria-selected="false"><i class="mdi mdi-chart-donut-variant"></i><span class="opname-tab-label">Ringkasan</span></button></li>
                                    <li class="nav-item" id="movementTabNav" role="presentation"><button class="nav-link" id="movementTabButton" data-bs-toggle="tab" data-bs-target="#movementTab" type="button" role="tab" aria-controls="movementTab" aria-selected="false"><i class="mdi mdi-swap-horizontal"></i><span class="opname-tab-label">Transaksi</span><b id="detailMovementBadge">0</b></button></li>
                                    <li class="nav-item" id="auditTabNav" role="presentation"><button class="nav-link" id="auditTabButton" data-bs-toggle="tab" data-bs-target="#auditTab" type="button" role="tab" aria-controls="auditTab" aria-selected="false"><i class="mdi mdi-history"></i><span class="opname-tab-label">Audit Trail</span><b id="detailAuditBadge">0</b></button></li>
                                </ul>
                            </div>
                            <div class="tab-content opname-tab-content">
                                <div class="tab-pane fade show active" id="countTab" role="tabpanel" aria-labelledby="countTabButton" tabindex="0">
                                    <div class="opname-count-toolbar">
                                        <div class="opname-count-copy"><span><i class="mdi mdi-package-variant-closed-check"></i></span><div><strong>Daftar Obat & Batch</strong><small id="countHelp">Masukkan hasil fisik tanpa melihat referensi stok sistem.</small></div></div>
                                        <div class="opname-count-tools">
                                            <div class="opname-detail-search"><i class="mdi mdi-magnify"></i><input type="search" id="detailMedicineSearch" placeholder="Cari obat, batch, atau rak..." aria-label="Cari obat dalam penghitungan" autocomplete="off"><button type="button" id="clearDetailMedicineSearch" aria-label="Hapus pencarian"><i class="mdi mdi-close"></i></button></div>
                                            <div class="opname-row-filters" role="group" aria-label="Filter hasil hitung">
                                                <button type="button" class="opname-row-filter is-active" data-row-filter="all" aria-pressed="true">Semua <b id="detailAllFilterCount">0</b></button>
                                                <button type="button" class="opname-row-filter" data-row-filter="pending" aria-pressed="false"><i class="mdi mdi-progress-pencil"></i> Belum dihitung <b id="detailPendingFilterCount">0</b></button>
                                                <button type="button" class="opname-row-filter d-none" id="detailDifferenceFilter" data-row-filter="difference" aria-pressed="false"><i class="mdi mdi-alert-circle-outline"></i> Selisih <b id="detailDifferenceFilterCount">0</b></button>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm d-none" id="detailFillZero"><i class="mdi mdi-numeric-0-box-multiple-outline"></i> Isi 0 yang kosong</button>
                                        </div>
                                    </div>
                                    <div class="opname-count-status">
                                        <span><i class="mdi mdi-format-list-checks"></i><strong id="detailVisibleCount">0 item ditampilkan</strong></span>
                                        <span><i class="mdi mdi-progress-check"></i><strong id="detailWorkspaceProgress">0/0 terisi</strong></span>
                                        <span class="blind-count-badge" id="comparisonStateBadge"><i class="mdi mdi-eye-off-outline"></i> Kuantitas sistem terlindungi</span>
                                    </div>
                                    <div class="table-responsive opname-count-table-wrap"><table class="table opname-count-table"><thead><tr id="countTableHead"><th>Obat / Rak</th><th>Batch / ED</th><th>Stok Fisik</th><th>Petugas</th></tr></thead><tbody id="countTableBody"></tbody></table></div>
                                    <div class="opname-no-result d-none" id="detailCountNoResult"><i class="mdi mdi-magnify-close"></i><strong>Data tidak ditemukan</strong><span>Ubah kata pencarian atau pilih filter baris yang lain.</span></div>
                                </div>
                                <div class="tab-pane fade" id="summaryTab" role="tabpanel" aria-labelledby="summaryTabButton" tabindex="0"><div id="summaryContent"></div></div>
                                <div class="tab-pane fade" id="movementTab" role="tabpanel" aria-labelledby="movementTabButton" tabindex="0"><div id="movementContent"></div></div>
                                <div class="tab-pane fade" id="auditTab" role="tabpanel" aria-labelledby="auditTabButton" tabindex="0"><div id="auditContent" class="opname-audit-list"></div></div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="modal-footer opname-detail-footer">
                    <div id="detailFooterNote"><span class="opname-footer-note-icon"><i class="mdi mdi-lightbulb-on-outline"></i></span><div><small>LANGKAH BERIKUTNYA</small><span>Pilih aksi sesuai status dokumen.</span></div></div>
                    <div id="detailActions"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.stok.stockOpname.jsMain")
@endpush
