@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.partials.style")
    @include("medcare.menu.pembelianPenerimaan.returPembelian.partials.style")
@endpush

@section("content")
    <div class="purchase-page">
        @include("medcare.menu.pembelianPenerimaan.returPembelian.modalMain")
        @include("medcare.menu.pembelianPenerimaan.returPembelian.modalDetail")
        @include("medcare.menu.pembelianPenerimaan.returPembelian.modalCompensation")

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Pembelian dan Penerimaan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Retur Pembelian</li>
            </ol>
        </nav>

        <section class="purchase-hero">
            <div class="purchase-hero-copy">
                <span class="purchase-kicker">
                    <i class="mdi mdi-keyboard-return"></i>
                    Purchase Return
                </span>
                <h2>Retur Pembelian</h2>
                <p>Catat retur ke supplier dari penerimaan yang sudah diposting.</p>
                <div class="purchase-hero-actions">
                    <button type="button" class="btn btn-light" id="openReturModal" data-bs-toggle="modal"
                        data-bs-target="#returPembelianModal">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        Buat Retur
                    </button>
                    <button type="button" class="btn btn-outline-light" id="scrollReturTable">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        Lihat Daftar
                    </button>
                </div>
            </div>

            <div class="purchase-flow" aria-label="Alur retur pembelian">
                <div class="purchase-flow-item">
                    <span class="purchase-flow-icon"><i class="mdi mdi-truck-check-outline"></i></span>
                    <div>
                        <strong>1. Pilih Penerimaan</strong>
                        <small>Hanya transaksi posted dan masih punya sisa retur.</small>
                    </div>
                    <i class="mdi mdi-chevron-right purchase-flow-arrow"></i>
                </div>
                <div class="purchase-flow-item">
                    <span class="purchase-flow-icon"><i class="mdi mdi-package-variant-minus"></i></span>
                    <div>
                        <strong>2. Isi Qty Retur</strong>
                        <small>Qty dibatasi sisa retur dan stok batch.</small>
                    </div>
                    <i class="mdi mdi-chevron-right purchase-flow-arrow"></i>
                </div>
                <div class="purchase-flow-item">
                    <span class="purchase-flow-icon"><i class="mdi mdi-database-minus-outline"></i></span>
                    <div>
                        <strong>3. Posting</strong>
                        <small>Stok batch berkurang saat dokumen diposting.</small>
                    </div>
                    <i class="mdi mdi-check-circle-outline purchase-flow-arrow"></i>
                </div>
                <div class="purchase-flow-item">
                    <span class="purchase-flow-icon"><i class="mdi mdi-hand-coin-outline"></i></span>
                    <div>
                        <strong>4. Pantau Ganti Rugi</strong>
                        <small>Retur tetap terbuka sampai penggantian supplier lengkap.</small>
                    </div>
                    <i class="mdi mdi-shield-check-outline purchase-flow-arrow"></i>
                </div>
            </div>
        </section>

        <div class="purchase-stats-grid" aria-label="Ringkasan retur pembelian">
            <article class="purchase-stat is-total">
                <span class="purchase-stat-icon"><i class="mdi mdi-clipboard-list-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="returnTotalCount">0</strong>
                    <span>Total Retur</span>
                    <small>Semua dokumen retur.</small>
                </div>
            </article>
            <article class="purchase-stat is-draft is-clickable" id="openOutstandingCompensations" role="button"
                tabindex="0" title="Tampilkan seluruh retur yang belum diganti, lintas periode">
                <span class="purchase-stat-icon"><i class="mdi mdi-file-clock-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="returnDraftCount">0</strong>
                    <span>Draft</span>
                    <small>Belum mengurangi stok.</small>
                </div>
            </article>
            <article class="purchase-stat is-approved">
                <span class="purchase-stat-icon"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="returnPostedCount">0</strong>
                    <span>Posted</span>
                    <small>Sudah mengurangi stok.</small>
                </div>
            </article>
            <article class="purchase-stat is-value">
                <span class="purchase-stat-icon"><i class="mdi mdi-cash-minus"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="returnTotalValue">Rp 0</strong>
                    <span>Total Nilai</span>
                    <small>Akumulasi nilai retur aktif.</small>
                </div>
            </article>
            <article class="purchase-stat is-draft">
                <span class="purchase-stat-icon"><i class="mdi mdi-clock-alert-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="returnCompensationWaiting">0</strong>
                    <span>Belum Diganti</span>
                    <small>Menunggu atau baru diganti sebagian.</small>
                </div>
            </article>
            <article class="purchase-stat is-value">
                <span class="purchase-stat-icon"><i class="mdi mdi-cash-clock"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="returnCompensationOutstanding">Rp 0</strong>
                    <span>Sisa Ganti Rugi</span>
                    <small><span id="returnCompensationOverdue">0</span> retur melewati batas waktu.</small>
                </div>
            </article>
        </div>

        <section class="purchase-table-section" id="returnTableSection">
            <div class="purchase-table-toolbar">
                <div class="purchase-table-title">
                    <span class="purchase-table-title-icon"><i class="mdi mdi-keyboard-return"></i></span>
                    <div>
                        <h5>Daftar Retur Pembelian</h5>
                        <p>Kelola draft, posting, dan pembatalan retur ke supplier.</p>
                    </div>
                </div>

                <div class="purchase-table-tools">
                    <div class="purchase-search">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="searchReturPembelian"
                            placeholder="Cari nomor retur, penerimaan, PO, supplier..." autocomplete="off"
                            aria-label="Cari retur pembelian">
                        <button type="button" class="purchase-search-clear" id="clearReturnSearch"
                            aria-label="Hapus pencarian" title="Hapus pencarian">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary purchase-icon-btn" id="refreshReturnTable"
                        title="Muat ulang data" aria-label="Muat ulang data">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1"
                        id="openReturModalToolbar" data-bs-toggle="modal" data-bs-target="#returPembelianModal">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        <span>Tambah Retur</span>
                    </button>
                </div>
            </div>

            <div class="purchase-filter-bar">
                <div class="purchase-filter-group" aria-label="Filter status retur pembelian">
                    <span class="purchase-filter-label">
                        <i class="mdi mdi-filter-variant"></i>
                        Status
                    </span>
                    <button type="button" class="purchase-filter-chip is-active" data-status="" aria-pressed="true">
                        Semua <span class="purchase-filter-count" id="returnAllFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="draft" aria-pressed="false">
                        Draft <span class="purchase-filter-count" id="returnDraftFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="posted" aria-pressed="false">
                        Posted <span class="purchase-filter-count" id="returnPostedFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip" data-status="cancelled" aria-pressed="false">
                        Cancelled <span class="purchase-filter-count" id="returnCancelledFilterCount">0</span>
                    </button>
                </div>
                <div class="purchase-filter-group compensation-filter-group" aria-label="Filter ganti rugi supplier">
                    <span class="purchase-filter-label">
                        <i class="mdi mdi-hand-coin-outline"></i>
                        Ganti Rugi
                    </span>
                    <button type="button" class="purchase-filter-chip compensation-filter-chip is-active"
                        data-compensation="" aria-pressed="true">Semua</button>
                    <button type="button" class="purchase-filter-chip compensation-filter-chip"
                        data-compensation="open" aria-pressed="false">Belum Diganti</button>
                    <button type="button" class="purchase-filter-chip compensation-filter-chip"
                        data-compensation="overdue" aria-pressed="false">Jatuh Tempo</button>
                    <button type="button" class="purchase-filter-chip compensation-filter-chip"
                        data-compensation="settled" aria-pressed="false">Sudah Diganti</button>
                    <button type="button" class="purchase-filter-chip compensation-filter-chip"
                        data-compensation="not_required" aria-pressed="false">Tidak Ditagihkan</button>
                </div>
                <div class="purchase-filter-controls">
                    <div class="purchase-date-filter">
                        <label for="returnDateRange">
                            <i class="mdi mdi-calendar-range-outline"></i>
                            Periode Retur
                        </label>
                        <div class="purchase-date-input">
                            <i class="mdi mdi-calendar-month-outline"></i>
                            <input type="text" id="returnDateRange" placeholder="Pilih rentang tanggal"
                                autocomplete="off" aria-label="Pilih rentang tanggal retur">
                            <button type="button" id="clearReturnDateRange" aria-label="Hapus filter tanggal"
                                title="Hapus filter tanggal">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                    </div>

                    <div class="purchase-page-size">
                        <label for="returnPageLength">Tampilkan data</label>
                        <select id="returnPageLength" class="form-select form-select-sm"
                            aria-label="Jumlah baris per halaman">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive purchase-table-wrap">
                <table id="tableReturPembelian" class="table purchase-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Status</th>
                            <th>Nomor Retur</th>
                            <th>Penerimaan</th>
                            <th>Nomor PO</th>
                            <th>Supplier</th>
                            <th>Ref Supplier</th>
                            <th>Tanggal</th>
                            <th>Item / Qty</th>
                            <th>Grand Total</th>
                            <th>Ganti Rugi Supplier</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.pembelianPenerimaan.returPembelian.jsMain")
@endpush
