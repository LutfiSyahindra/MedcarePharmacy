@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.datePicker")
    @include("medcare.menu.pembelianPenerimaan.penerimaan.partials.style")
    @include("medcare.menu.pembelianPenerimaan.faktur.partials.style")
@endpush

@section("content")
    <div class="purchase-page invoice-page">
        @include("medcare.menu.pembelianPenerimaan.faktur.modalDetail")
        @include("medcare.menu.pembelianPenerimaan.faktur.modalPayment")

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Pembelian dan Penerimaan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Faktur</li>
            </ol>
        </nav>

        <section class="invoice-command-center">
            <div class="invoice-command-copy">
                <span class="invoice-kicker">
                    <i class="mdi mdi-receipt-text-check-outline"></i>
                    Accounts Payable
                </span>
                <h2>Faktur Pembelian</h2>
                <p>Pusat kendali tagihan supplier dari transaksi penerimaan barang.</p>
            </div>

            <div class="invoice-health-panel">
                <div class="invoice-health-copy">
                    <span>Outstanding</span>
                    <strong id="invoiceHeaderOutstanding">Rp 0</strong>
                    <small id="invoiceHeaderHealth">0 faktur perlu tindak lanjut</small>
                </div>
                <div class="invoice-health-meter" aria-label="Rasio pembayaran faktur">
                    <span id="invoiceHealthMeter" style="width: 0%"></span>
                </div>
            </div>
        </section>

        <div class="purchase-stats-grid invoice-stats-grid" aria-label="Ringkasan faktur">
            <article class="purchase-stat is-total">
                <span class="purchase-stat-icon"><i class="mdi mdi-file-document-multiple-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="invoiceTotalCount">0</strong>
                    <span>Total Faktur</span>
                    <small>Seluruh faktur pembelian.</small>
                </div>
            </article>
            <article class="purchase-stat is-value">
                <span class="purchase-stat-icon"><i class="mdi mdi-cash-clock"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="invoiceRemainingDebt">Rp 0</strong>
                    <span>Sisa Hutang</span>
                    <small>Belum terbayar ke supplier.</small>
                </div>
            </article>
            <article class="purchase-stat is-draft">
                <span class="purchase-stat-icon"><i class="mdi mdi-alert-circle-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="invoiceOverdueCount">0</strong>
                    <span>Jatuh Tempo</span>
                    <small>Perlu diprioritaskan.</small>
                </div>
            </article>
            <article class="purchase-stat is-approved">
                <span class="purchase-stat-icon"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div class="purchase-stat-copy">
                    <strong id="invoicePaidCount">0</strong>
                    <span>Lunas</span>
                    <small>Pembayaran selesai.</small>
                </div>
            </article>
        </div>

        <section class="invoice-insight-strip" aria-label="Insight faktur">
            <article class="invoice-insight-card">
                <span class="invoice-insight-icon is-paid"><i class="mdi mdi-cash-check"></i></span>
                <div>
                    <small>Terbayar</small>
                    <strong id="invoicePaidValue">Rp 0</strong>
                    <span id="invoicePaidRatio">0% dari total nilai aktif</span>
                </div>
            </article>
            <article class="invoice-insight-card">
                <span class="invoice-insight-icon is-soon"><i class="mdi mdi-calendar-clock"></i></span>
                <div>
                    <small>Tempo 7 Hari</small>
                    <strong id="invoiceDueSoonCount">0</strong>
                    <span>Faktur mendekati jatuh tempo.</span>
                </div>
            </article>
            <article class="invoice-insight-card">
                <span class="invoice-insight-icon is-open"><i class="mdi mdi-progress-clock"></i></span>
                <div>
                    <small>Open Invoice</small>
                    <strong id="invoiceOpenCount">0</strong>
                    <span>Belum dibayar atau dibayar sebagian.</span>
                </div>
            </article>
        </section>

        <section class="purchase-table-section invoice-table-section" id="invoiceTableSection">
            <div class="purchase-table-toolbar">
                <div class="purchase-table-title">
                    <span class="purchase-table-title-icon"><i class="mdi mdi-receipt-text-check-outline"></i></span>
                    <div>
                        <h5>Daftar Faktur</h5>
                        <p>Nomor faktur, supplier, tenggat pembayaran, nilai tagihan, dan status pelunasan.</p>
                    </div>
                </div>

                <div class="purchase-table-tools">
                    <div class="purchase-search">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="searchFaktur"
                            placeholder="Cari faktur, penerimaan, PO, supplier, branch..." autocomplete="off"
                            aria-label="Cari faktur">
                        <button type="button" class="purchase-search-clear" id="clearInvoiceSearch"
                            aria-label="Hapus pencarian" title="Hapus pencarian">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary purchase-icon-btn" id="refreshInvoiceTable"
                        title="Muat ulang data" aria-label="Muat ulang data">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                    <a href="{{ route("penerimaan.penerimaan") }}" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="mdi mdi-package-variant-closed-check"></i>
                        <span>Penerimaan</span>
                    </a>
                </div>
            </div>

            <div class="purchase-filter-bar invoice-filter-bar">
                <div class="purchase-filter-group" aria-label="Filter pembayaran faktur">
                    <span class="purchase-filter-label">
                        <i class="mdi mdi-filter-variant"></i>
                        Pembayaran
                    </span>
                    <button type="button" class="purchase-filter-chip invoice-filter-chip is-active"
                        data-payment="" aria-pressed="true">
                        Semua <span class="purchase-filter-count" id="invoiceAllFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip invoice-filter-chip"
                        data-payment="belum_dibayar" aria-pressed="false">
                        Belum <span class="purchase-filter-count" id="invoiceUnpaidFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip invoice-filter-chip"
                        data-payment="sebagian" aria-pressed="false">
                        Sebagian <span class="purchase-filter-count" id="invoicePartialFilterCount">0</span>
                    </button>
                    <button type="button" class="purchase-filter-chip invoice-filter-chip"
                        data-payment="lunas" aria-pressed="false">
                        Lunas <span class="purchase-filter-count" id="invoicePaidFilterCount">0</span>
                    </button>
                </div>

                <div class="purchase-filter-group invoice-due-filter" aria-label="Filter jatuh tempo faktur">
                    <span class="purchase-filter-label">
                        <i class="mdi mdi-calendar-alert"></i>
                        Tempo
                    </span>
                    <button type="button" class="invoice-due-chip is-active" data-due="" aria-pressed="true">Semua</button>
                    <button type="button" class="invoice-due-chip" data-due="overdue" aria-pressed="false">Lewat Tempo</button>
                    <button type="button" class="invoice-due-chip" data-due="due_soon" aria-pressed="false">7 Hari</button>
                    <button type="button" class="invoice-due-chip" data-due="not_due" aria-pressed="false">Aman</button>
                    <button type="button" class="invoice-due-chip" data-due="no_due" aria-pressed="false">Tanpa Tempo</button>
                </div>

                <div class="purchase-filter-controls">
                    <div class="purchase-date-filter">
                        <label for="invoiceDateRange">
                            <i class="mdi mdi-calendar-range-outline"></i>
                            Periode Faktur
                        </label>
                        <div class="purchase-date-input">
                            <i class="mdi mdi-calendar-month-outline"></i>
                            <input type="text" id="invoiceDateRange" placeholder="Pilih rentang tanggal"
                                autocomplete="off" aria-label="Pilih rentang tanggal faktur">
                            <button type="button" id="clearInvoiceDateRange" aria-label="Hapus filter tanggal"
                                title="Hapus filter tanggal">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                        <select id="invoiceDatePreset" class="form-select form-select-sm"
                            aria-label="Pilih periode cepat">
                            <option value="">Periode cepat</option>
                            <option value="today">Hari Ini</option>
                            <option value="7days">7 Hari Terakhir</option>
                            <option value="30days">30 Hari Terakhir</option>
                            <option value="this_month">Bulan Ini</option>
                        </select>
                    </div>

                    <div class="purchase-page-size">
                        <label for="invoicePageLength">Tampilkan data</label>
                        <select id="invoicePageLength" class="form-select form-select-sm"
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
                <table id="tableFaktur" class="table purchase-table invoice-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tempo</th>
                            <th>Faktur</th>
                            <th>Penerimaan / PO</th>
                            <th>Supplier</th>
                            <th>Branch</th>
                            <th>Tanggal Faktur</th>
                            <th>Jatuh Tempo</th>
                            <th>Total</th>
                            <th>Dibayar / Sisa</th>
                            <th>Status</th>
                            <th>Progress</th>
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
    @include("medcare.menu.pembelianPenerimaan.faktur.jsMain")
@endpush
