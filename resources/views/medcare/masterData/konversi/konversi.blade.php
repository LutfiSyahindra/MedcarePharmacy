@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.dropify")
    @include("medcare.masterData.Obat.partials.style")

    <style>
        .konversi-page {
            --konversi-primary: #0f766e;
            --konversi-blue: #2563eb;
            --konversi-warning: #d97706;
            --konversi-success: #16a34a;
            --konversi-danger: #dc2626;
        }

        .konversi-page .obat-hero {
            background:
                linear-gradient(135deg, rgba(15, 118, 110, .97), rgba(37, 99, 235, .94)),
                linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
            background-size: auto, 34px 34px;
        }

        .konversi-rule {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .65rem;
            border-radius: 999px;
            color: var(--konversi-blue);
            background: #eef6ff;
            font-weight: 800;
            white-space: nowrap;
        }

        .konversi-rule i {
            font-size: 1rem;
        }

        .konversi-pcs-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .65rem;
            border-radius: 999px;
            color: var(--konversi-warning);
            background: #fff7ed;
            font-weight: 800;
        }

        .konversi-input-card {
            border: 1px solid var(--obat-border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .konversi-input-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--obat-border);
            background: linear-gradient(180deg, #fff, #f8fbff);
        }

        .konversi-input-card-header strong {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: var(--obat-text);
            font-weight: 800;
        }

        .konversi-input-card-body {
            padding: 1rem;
        }

        .konversi-default-box {
            display: flex;
            align-items: center;
            gap: .55rem;
            min-height: 42px;
            padding: .65rem .75rem;
            border: 1px solid var(--obat-border);
            border-radius: 8px;
            background: #f8fbff;
        }

        .konversi-default-box .form-check-input {
            margin-top: 0;
        }

        .konversi-modal-note {
            display: flex;
            gap: .75rem;
            padding: .9rem 1rem;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            background: #f8fbff;
            color: var(--obat-muted);
        }

        .konversi-modal-note i {
            color: var(--konversi-blue);
            font-size: 1.25rem;
        }

        .konversi-page .obat-stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .konversi-status-filter {
            display: inline-flex;
            gap: .35rem;
            padding: .25rem;
            border: 1px solid var(--obat-border);
            border-radius: 8px;
            background: #fff;
        }

        .konversi-status-filter .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 0;
            border-radius: 8px !important;
            color: var(--obat-muted);
            font-weight: 800;
            white-space: nowrap;
        }

        .konversi-status-filter .btn.is-active {
            color: #fff;
            background: linear-gradient(135deg, var(--konversi-primary), var(--konversi-blue));
            box-shadow: 0 8px 18px rgba(15, 118, 110, .16);
        }

        .konversi-status-badge,
        .konversi-empty-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .65rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .konversi-status-badge.is-ready {
            color: var(--konversi-success);
            background: #ecfdf3;
        }

        .konversi-status-badge.is-empty,
        .konversi-empty-badge {
            color: var(--konversi-warning);
            background: #fff7ed;
        }

        .konversi-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            min-width: 280px;
            max-width: 560px;
            white-space: normal;
        }

        .konversi-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .42rem .58rem;
            border-radius: 999px;
            color: var(--konversi-blue);
            background: #eef6ff;
            font-size: .78rem;
            font-weight: 800;
        }

        .konversi-chip.is-default {
            color: var(--konversi-success);
            background: #ecfdf3;
        }

        .konversi-inline-empty {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border: 0;
            border-radius: 999px;
            padding: .45rem .65rem;
            color: var(--konversi-warning);
            background: #fff7ed;
            font-weight: 800;
        }

        .konversi-obat-context {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .8rem;
            align-items: center;
            margin-bottom: 1rem;
            padding: .9rem 1rem;
            border: 1px solid var(--obat-border);
            border-radius: 8px;
            background: #fff;
        }

        .konversi-obat-context strong {
            display: block;
            color: var(--obat-text);
            font-weight: 800;
        }

        .konversi-obat-context span,
        .konversi-obat-context small {
            color: var(--obat-muted);
        }

        .konversi-live-preview {
            margin-top: .8rem;
            padding: .8rem;
            border: 1px dashed rgba(37, 99, 235, .28);
            border-radius: 8px;
            color: var(--obat-muted);
            background: #f8fbff;
        }

        .konversi-row-actions {
            display: flex;
            align-items: end;
            justify-content: flex-end;
        }

        .konversi-row-actions .btn {
            border-radius: 8px;
            font-weight: 800;
        }

        .konversi-input-card .obat-field.is-satuan {
            grid-column: span 5;
        }

        .konversi-input-card .obat-field.is-konversi {
            grid-column: span 4;
        }

        .konversi-input-card .obat-field.is-default {
            grid-column: span 3;
        }

        .konversi-status-badge small {
            font-size: .72rem;
            font-weight: 800;
            opacity: .78;
        }

        @media (max-width: 1199.98px) {
            .konversi-page .obat-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .konversi-page .obat-stats-grid {
                grid-template-columns: 1fr;
            }

            .konversi-status-filter {
                width: 100%;
                overflow-x: auto;
            }

            .konversi-obat-context {
                grid-template-columns: 1fr;
            }

            .konversi-input-card .obat-field.is-satuan,
            .konversi-input-card .obat-field.is-konversi,
            .konversi-input-card .obat-field.is-default {
                grid-column: 1 / -1;
            }
        }
    </style>
@endpush

@section("content")
    <div class="obat-page konversi-page">
        @include("medcare.masterData.konversi.modalMain")
        @include("medcare.masterData.konversi.modalExcell")

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                <li class="breadcrumb-item active" aria-current="page">Konversi Satuan Obat</li>
            </ol>
        </nav>

        <section class="obat-hero">
            <div>
                <span class="obat-kicker">
                    <i class="mdi mdi-swap-horizontal-bold"></i>
                    Unit Conversion
                </span>
                <h2>Konversi Satuan Obat</h2>
                <p>Semua master obat ditampilkan langsung. User tinggal pilih obat yang belum lengkap, lalu isi satuan pembelian dan jumlah konversinya.</p>
                <div class="obat-hero-actions">
                    <button type="button" class="btn btn-light konversi-hero-filter" data-status="without">
                        <i class="mdi mdi-alert-circle-outline"></i>
                        Lihat Belum Ada
                    </button>
                    <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#konversiModalExcell">
                        <i class="mdi mdi-file-excel-outline"></i>
                        Import Excel
                    </button>
                    <button type="button" class="btn btn-outline-light obat-refresh-table">
                        <i class="mdi mdi-refresh"></i>
                        Refresh Data
                    </button>
                </div>
            </div>
            <div class="obat-flow-panel" aria-label="Alur konversi satuan obat">
                <div class="obat-flow-item">
                    <i class="mdi mdi-format-list-bulleted"></i>
                    <div>
                        <span>Semua Obat</span>
                        <small>Data master obat tampil sebagai daftar kerja.</small>
                    </div>
                </div>
                <div class="obat-flow-item">
                    <i class="mdi mdi-filter-variant"></i>
                    <div>
                        <span>Filter Status</span>
                        <small>Pisahkan obat yang sudah atau belum punya konversi.</small>
                    </div>
                </div>
                <div class="obat-flow-item">
                    <i class="mdi mdi-tune-variant"></i>
                    <div>
                        <span>Kelola Satuan</span>
                        <small>Isi box, strip, botol, dus, atau satuan pembelian lain.</small>
                    </div>
                </div>
            </div>
        </section>

        <div class="obat-stats-grid">
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="konversiTotalObat">0</strong>
                    <span>Total Obat</span>
                    <small>Seluruh master obat yang perlu dipantau.</small>
                </div>
            </div>
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div>
                    <strong id="konversiWith">0</strong>
                    <span>Sudah Ada</span>
                    <small>Obat yang memiliki satuan konversi.</small>
                </div>
            </div>
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-alert-circle-outline"></i></span>
                <div>
                    <strong id="konversiWithout">0</strong>
                    <span>Belum Ada</span>
                    <small>Obat yang perlu segera dilengkapi.</small>
                </div>
            </div>
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="konversiFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah baris sesuai pencarian aktif.</small>
                </div>
            </div>
        </div>

        <section class="obat-table-section">
            <div class="obat-table-toolbar">
                <div class="obat-table-title">
                    <span class="obat-table-title-icon"><i class="mdi mdi-scale-balance"></i></span>
                    <div>
                        <h5>Daftar Obat dan Status Konversi</h5>
                        <p>Klik tombol kelola pada obat untuk menambah atau memperbarui satuan konversinya.</p>
                    </div>
                </div>
                <div class="obat-table-tools">
                    <div class="konversi-status-filter" aria-label="Filter status konversi">
                        <button type="button" class="btn btn-sm is-active" data-status="all">
                            <i class="mdi mdi-format-list-bulleted"></i>
                            Semua
                        </button>
                        <button type="button" class="btn btn-sm" data-status="with">
                            <i class="mdi mdi-check-circle-outline"></i>
                            Sudah
                        </button>
                        <button type="button" class="btn btn-sm" data-status="without">
                            <i class="mdi mdi-alert-circle-outline"></i>
                            Belum
                        </button>
                    </div>
                    <label class="obat-search" for="searchKonversi">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="searchKonversi" placeholder="Cari obat, kode, satuan, atau status...">
                    </label>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tableKonversi" class="table obat-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Obat</th>
                            <th>Satuan Stok</th>
                            <th>Status</th>
                            <th>Konversi Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.masterData.Obat.partials.scripts")
    @include("medcare.masterData.konversi.jsMain")
@endpush
