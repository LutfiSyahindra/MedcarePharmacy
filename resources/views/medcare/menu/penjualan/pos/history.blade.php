@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.menu.penjualan.pos.partials.style")
    @include("medcare.menu.penjualan.pos.partials.historyStyle")
@endpush

@section("content")
    <div class="pos-page pos-history-page">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route("penjualan.pos") }}">Penjualan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat Transaksi Kasir</li>
            </ol>
        </nav>

        <header class="history-hero">
            <div class="history-hero-main">
                <span class="history-hero-icon"><i class="mdi mdi-receipt-text-clock-outline"></i></span>
                <div>
                    <span class="history-eyebrow">Pusat aktivitas kasir</span>
                    <h1>Riwayat Transaksi</h1>
                    <p>Telusuri transaksi, pantau pembayaran, lanjutkan draft, dan buka rincian tanpa meninggalkan halaman.</p>
                </div>
            </div>
            <div class="history-hero-actions">
                <button type="button" class="history-icon-button" id="refreshHistoryBtn" title="Muat ulang data"
                    aria-label="Muat ulang data">
                    <i class="mdi mdi-refresh"></i>
                </button>
                <a href="{{ route("penjualan.pos") }}" class="btn history-primary-button">
                    <i class="mdi mdi-point-of-sale"></i>
                    Transaksi Baru
                </a>
            </div>
            <div class="history-hero-context">
                <span><i class="mdi mdi-calendar-range"></i><strong id="historyDateRangeLabel">Semua waktu</strong></span>
                <span><i class="mdi mdi-database-check-outline"></i>Terakhir diperbarui <strong id="historyLastSync">-</strong></span>
            </div>
        </header>

        <section class="history-summary-grid" aria-label="Ringkasan transaksi sesuai filter">
            <button type="button" class="history-summary-card is-primary" data-summary-status="">
                <span class="history-summary-icon"><i class="mdi mdi-receipt-text-outline"></i></span>
                <span class="history-summary-copy">
                    <small>Total transaksi</small>
                    <strong id="historyTotal">0</strong>
                    <span><b id="historyCompleted">0</b> transaksi selesai</span>
                </span>
                <i class="mdi mdi-chevron-right history-summary-arrow"></i>
            </button>
            <article class="history-summary-card is-success">
                <span class="history-summary-icon"><i class="mdi mdi-chart-line"></i></span>
                <span class="history-summary-copy">
                    <small>Omzet selesai</small>
                    <strong id="historyRevenue">Rp 0</strong>
                    <span>Rata-rata <b id="historyAverage">Rp 0</b></span>
                </span>
            </article>
            <button type="button" class="history-summary-card is-warning" data-summary-status="draft">
                <span class="history-summary-icon"><i class="mdi mdi-progress-clock"></i></span>
                <span class="history-summary-copy">
                    <small>Perlu dilanjutkan</small>
                    <strong id="historyDraft">0</strong>
                    <span>Draft transaksi kasir</span>
                </span>
                <i class="mdi mdi-chevron-right history-summary-arrow"></i>
            </button>
            <button type="button" class="history-summary-card is-danger" data-summary-payment="credit">
                <span class="history-summary-icon"><i class="mdi mdi-credit-card-clock-outline"></i></span>
                <span class="history-summary-copy">
                    <small>Sisa tagihan</small>
                    <strong id="historyDue">Rp 0</strong>
                    <span><b id="historyCancelled">0</b> transaksi dibatalkan</span>
                </span>
                <i class="mdi mdi-chevron-right history-summary-arrow"></i>
            </button>
        </section>

        <section class="history-workspace">
            <aside class="history-filter-panel" aria-labelledby="historyFilterTitle">
                <div class="history-section-heading">
                    <span><i class="mdi mdi-tune-variant"></i></span>
                    <div>
                        <h2 id="historyFilterTitle">Filter Transaksi</h2>
                        <p>Persempit data yang ingin diperiksa.</p>
                    </div>
                    <span class="history-filter-count" id="historyFilterCount" aria-label="Jumlah filter aktif">0</span>
                </div>

                <div class="history-filter-field">
                    <label for="historySearch">Pencarian</label>
                    <div class="history-search-box">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="historySearch"
                            placeholder="Nomor, pelanggan, resep, dokter..." autocomplete="off">
                        <button type="button" id="clearHistorySearch" aria-label="Hapus pencarian" hidden>
                            <i class="mdi mdi-close-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="history-filter-field">
                    <label>Rentang waktu</label>
                    <div class="history-range-presets" role="group" aria-label="Pilihan cepat rentang waktu">
                        <button type="button" data-range="today">Hari ini</button>
                        <button type="button" data-range="7days">7 hari</button>
                        <button type="button" data-range="30days">30 hari</button>
                        <button type="button" class="is-active" data-range="all">Semua</button>
                    </div>
                    <div class="history-date-inputs">
                        <div>
                            <span>Dari</span>
                            <input type="date" id="historyDateStart" aria-label="Tanggal awal">
                        </div>
                        <i class="mdi mdi-arrow-right"></i>
                        <div>
                            <span>Sampai</span>
                            <input type="date" id="historyDateEnd" aria-label="Tanggal akhir">
                        </div>
                    </div>
                </div>

                <div class="history-filter-field">
                    <label for="historyTypeFilter">Jenis transaksi</label>
                    <select id="historyTypeFilter" class="form-select">
                        <option value="">Semua jenis transaksi</option>
                        @foreach ($transactionTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="history-filter-field">
                    <label for="historyStatusFilter">Status transaksi</label>
                    <select id="historyStatusFilter" class="form-select">
                        <option value="">Semua status</option>
                        <option value="draft">Sementara</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Batal</option>
                    </select>
                </div>

                <div class="history-filter-field">
                    <label for="historyPaymentFilter">Status pembayaran</label>
                    <select id="historyPaymentFilter" class="form-select">
                        <option value="">Semua pembayaran</option>
                        <option value="unpaid">Belum bayar</option>
                        <option value="paid">Lunas</option>
                        <option value="credit">Tagihan</option>
                        <option value="void">Void</option>
                    </select>
                </div>

                @if ($branches->count() > 1)
                    <div class="history-filter-field">
                        <label for="historyBranchFilter">Cabang</label>
                        <select id="historyBranchFilter" class="form-select">
                            <option value="">Semua cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" id="historyBranchFilter" value="{{ $branches->first()?->id }}">
                @endif

                <div class="history-filter-actions">
                    <button type="button" class="btn history-apply-button" id="applyHistoryFilter">
                        <i class="mdi mdi-filter-check-outline"></i>Terapkan
                    </button>
                    <button type="button" class="btn history-reset-button" id="resetHistoryFilter">
                        Reset
                    </button>
                </div>
                <p class="history-filter-note"><i class="mdi mdi-information-outline"></i>Ringkasan dan tabel selalu mengikuti filter aktif.</p>
            </aside>

            <div class="history-results-panel">
                <div class="history-results-header">
                    <div class="history-section-heading">
                        <span><i class="mdi mdi-format-list-bulleted-square"></i></span>
                        <div>
                            <h2>Daftar Transaksi</h2>
                            <p><strong id="historyResultCount">0</strong> transaksi ditemukan</p>
                        </div>
                    </div>
                    <div class="history-table-state" id="historyTableState">
                        <span class="is-ready"><i class="mdi mdi-check-circle"></i>Data siap</span>
                    </div>
                </div>

                <div class="table-responsive history-table-wrap">
                    <table id="tablePenjualanPos" class="table history-table align-middle">
                        <thead>
                            <tr>
                                <th>Transaksi & Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Jenis & Cabang</th>
                                <th>Item</th>
                                <th>Total & Pembayaran</th>
                                <th>Status</th>
                                <th aria-label="Aksi"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="history-drawer-backdrop" id="transactionDrawerBackdrop" hidden></div>
    <aside class="history-drawer" id="transactionDrawer" role="dialog" aria-modal="true"
        aria-labelledby="transactionDrawerTitle" aria-hidden="true">
        <header class="history-drawer-header">
            <div>
                <span class="history-eyebrow">Detail transaksi</span>
                <h2 id="transactionDrawerTitle">Memuat transaksi...</h2>
                <div id="transactionDrawerMeta"></div>
            </div>
            <button type="button" class="history-drawer-close" data-close-drawer aria-label="Tutup detail transaksi">
                <i class="mdi mdi-close"></i>
            </button>
        </header>
        <div class="history-drawer-loading" id="transactionDrawerLoading">
            <i class="mdi mdi-loading mdi-spin"></i>
            <strong>Menyiapkan detail transaksi</strong>
            <span>Item, pembayaran, dan informasi pelanggan sedang dimuat.</span>
        </div>
        <div class="history-drawer-body" id="transactionDrawerBody" hidden></div>
        <footer class="history-drawer-footer" id="transactionDrawerFooter" hidden></footer>
    </aside>
@endsection

@push("scripts")
    @include("medcare.menu.penjualan.pos.historyJs")
@endpush
