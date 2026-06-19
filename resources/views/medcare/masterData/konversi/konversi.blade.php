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
                <p>Kelola relasi satuan pembelian obat ke satuan terkecil agar proses pembelian, stok, dan transaksi farmasi tetap konsisten.</p>
                <div class="obat-hero-actions">
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#konversiModal">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        Tambah Konversi
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
                    <i class="mdi mdi-pill"></i>
                    <div>
                        <span>Pilih Obat</span>
                        <small>Tentukan master obat yang akan diberi konversi.</small>
                    </div>
                </div>
                <div class="obat-flow-item">
                    <i class="mdi mdi-package-variant-closed"></i>
                    <div>
                        <span>Satuan Pembelian</span>
                        <small>Box, strip, botol, dus, atau satuan lain.</small>
                    </div>
                </div>
                <div class="obat-flow-item">
                    <i class="mdi mdi-calculator-variant-outline"></i>
                    <div>
                        <span>Jumlah PCS</span>
                        <small>Isi konversi ke satuan terkecil untuk stok.</small>
                    </div>
                </div>
            </div>
        </section>

        <div class="obat-stats-grid">
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-database-outline"></i></span>
                <div>
                    <strong id="konversiTotal">0</strong>
                    <span>Total Konversi</span>
                    <small>Seluruh data konversi satuan obat.</small>
                </div>
            </div>
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-filter-outline"></i></span>
                <div>
                    <strong id="konversiFiltered">0</strong>
                    <span>Hasil Filter</span>
                    <small>Jumlah data sesuai pencarian aktif.</small>
                </div>
            </div>
            <div class="obat-stat">
                <span class="obat-stat-icon"><i class="mdi mdi-cursor-pointer"></i></span>
                <div>
                    <strong id="konversiSelected">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris tabel untuk menandai konversi.</small>
                </div>
            </div>
        </div>

        <section class="obat-table-section">
            <div class="obat-table-toolbar">
                <div class="obat-table-title">
                    <span class="obat-table-title-icon"><i class="mdi mdi-scale-balance"></i></span>
                    <div>
                        <h5>Daftar Konversi</h5>
                        <p>Pantau relasi satuan pembelian ke PCS dalam daftar yang mudah dipindai.</p>
                    </div>
                </div>
                <div class="obat-table-tools">
                    <label class="obat-search" for="searchKonversi">
                        <i class="mdi mdi-magnify"></i>
                        <input type="text" id="searchKonversi" placeholder="Cari obat, satuan, atau jumlah...">
                    </label>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tableKonversi" class="table obat-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Obat</th>
                            <th>Satuan Pembelian</th>
                            <th>Konversi PCS</th>
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
