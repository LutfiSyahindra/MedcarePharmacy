@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("medcare.menu.penjualan.returPenjualan.partials.style")
@endpush

@section("content")
    <div class="sales-return-page">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route("penjualan.pos.history") }}">Penjualan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Retur Penjualan</li>
            </ol>
        </nav>

        <header class="sales-return-hero">
            <div>
                <span class="sales-return-kicker"><i class="mdi mdi-keyboard-return"></i> Sales Return</span>
                <h1>Retur Penjualan</h1>
                <p>Kembalikan item ke batch asal, hitung refund secara proporsional, dan jaga jejak kartu stok tetap utuh.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-light" id="openSalesReturnForm">
                        <i class="mdi mdi-plus-circle-outline me-1"></i>Buat Retur
                    </button>
                    <a href="{{ route("penjualan.pos.history") }}" class="btn btn-outline-light">
                        <i class="mdi mdi-receipt-text-clock-outline me-1"></i>Riwayat Kasir
                    </a>
                </div>
            </div>
            <div class="sales-return-flow" aria-label="Alur retur penjualan">
                <div><span>1</span><strong>Pilih transaksi selesai</strong><small>Hanya item yang masih memiliki sisa retur.</small></div>
                <i class="mdi mdi-chevron-right"></i>
                <div><span>2</span><strong>Tentukan item & qty</strong><small>Nilai refund mengikuti diskon dan pajak transaksi.</small></div>
                <i class="mdi mdi-chevron-right"></i>
                <div><span>3</span><strong>Posting retur</strong><small>Stok otomatis kembali ke batch penjualan asal.</small></div>
            </div>
        </header>

        <section class="sales-return-stats" aria-label="Ringkasan retur penjualan">
            <article><i class="mdi mdi-file-document-multiple-outline"></i><div><small>Total dokumen</small><strong id="returnStatTotal">0</strong><span>sesuai filter</span></div></article>
            <article class="is-warning"><i class="mdi mdi-file-clock-outline"></i><div><small>Menunggu posting</small><strong id="returnStatDraft">0</strong><span>draft aktif</span></div></article>
            <article class="is-success"><i class="mdi mdi-package-variant-plus"></i><div><small>Sudah posted</small><strong id="returnStatPosted">0</strong><span id="returnStatQty">0 item dikembalikan</span></div></article>
            <article class="is-value"><i class="mdi mdi-cash-refund"></i><div><small>Nilai refund posted</small><strong id="returnStatValue">Rp 0</strong><span>di luar embalase</span></div></article>
        </section>

        <section class="sales-return-panel">
            <div class="sales-return-toolbar">
                <div>
                    <h2>Daftar Retur</h2>
                    <p>Kelola draft, posting, serta pembatalan retur.</p>
                </div>
                <div class="sales-return-toolbar-actions">
                    <div class="sales-return-search"><i class="mdi mdi-magnify"></i><input type="search" id="returnSearch" placeholder="Nomor retur, transaksi, pelanggan..."></div>
                    <button type="button" class="btn btn-outline-secondary" id="refreshSalesReturns" title="Muat ulang"><i class="mdi mdi-refresh"></i></button>
                    <button type="button" class="btn btn-primary" id="openSalesReturnFormToolbar"><i class="mdi mdi-plus-circle-outline me-1"></i>Tambah Retur</button>
                </div>
            </div>

            <div class="sales-return-filters">
                <div class="sales-return-chips" role="group" aria-label="Filter status">
                    <button type="button" class="is-active" data-return-status="">Semua</button>
                    <button type="button" data-return-status="draft">Draft</button>
                    <button type="button" data-return-status="posted">Posted</button>
                    <button type="button" data-return-status="cancelled">Dibatalkan</button>
                </div>
                <div class="sales-return-filter-fields">
                    <input type="date" id="returnDateStart" class="form-control" aria-label="Tanggal awal">
                    <span>s.d.</span>
                    <input type="date" id="returnDateEnd" class="form-control" aria-label="Tanggal akhir">
                    @if ($branches->count() > 1)
                        <select id="returnBranch" class="form-select" aria-label="Cabang">
                            <option value="">Semua cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" id="returnBranch" value="{{ $branches->first()?->id }}">
                    @endif
                    <button type="button" class="btn btn-outline-secondary" id="resetReturnFilters">Reset</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="salesReturnTable" class="table sales-return-table align-middle">
                    <thead><tr>
                        <th>Retur</th>
                        <th>Transaksi & Pelanggan</th>
                        <th>Tanggal & Cabang</th>
                        <th>Item / Qty</th>
                        <th>Refund</th>
                        <th>Nilai Retur</th>
                        <th>Dibuat Oleh</th>
                        <th aria-label="Aksi"></th>
                    </tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="modal fade sales-return-modal-root" id="salesReturnModal" tabindex="-1" aria-labelledby="salesReturnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable sales-return-dialog">
            <div class="modal-content sales-return-modal">
                <form id="salesReturnForm" class="sales-return-form-shell">
                    <div class="modal-header">
                        <div class="sales-return-modal-brand">
                            <span class="sales-return-modal-icon"><i class="mdi mdi-keyboard-return"></i></span>
                            <div>
                                <span class="sales-return-modal-kicker">Sales return workspace</span>
                                <h5 class="modal-title" id="salesReturnModalLabel">Buat Retur Penjualan</h5>
                                <p>Pilih transaksi, tentukan item, lalu simpan sebagai draft sebelum stok dikembalikan.</p>
                            </div>
                        </div>
                        <div class="sales-return-modal-progress" aria-label="Tahapan retur">
                            <span class="is-active"><b>1</b>Transaksi</span>
                            <i class="mdi mdi-chevron-right"></i>
                            <span><b>2</b>Item</span>
                            <i class="mdi mdi-chevron-right"></i>
                            <span><b>3</b>Posting</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="salesReturnId">
                        <section class="sales-return-form-card">
                            <div class="sales-return-card-heading">
                                <span><i class="mdi mdi-file-document-edit-outline"></i></span>
                                <div>
                                    <h6>Informasi Dokumen</h6>
                                    <p>Data transaksi sumber dan metode pengembalian dana.</p>
                                </div>
                            </div>
                            <div class="sales-return-form-grid">
                                <div class="sales-return-field is-wide">
                                    <label for="returnTransaction">Transaksi penjualan <span>*</span></label>
                                    <select id="returnTransaction" class="form-select" required></select>
                                    <small>Pilih transaksi selesai yang masih memiliki item untuk diretur.</small>
                                </div>
                                <div class="sales-return-field">
                                    <label for="returnNumber">Nomor retur</label>
                                    <div class="sales-return-input-icon"><i class="mdi mdi-barcode"></i><input type="text" id="returnNumber" class="form-control" readonly placeholder="Otomatis"></div>
                                </div>
                                <div class="sales-return-field">
                                    <label for="returnDate">Tanggal retur <span>*</span></label>
                                    <div class="sales-return-input-icon"><i class="mdi mdi-calendar-blank-outline"></i><input type="date" id="returnDate" class="form-control" value="{{ now()->format("Y-m-d") }}" max="{{ now()->format("Y-m-d") }}" required></div>
                                </div>
                                <div class="sales-return-field">
                                    <label for="returnRefundMethod">Metode refund <span>*</span></label>
                                    <select id="returnRefundMethod" class="form-select" required>
                                        <option value="">Pilih metode</option>
                                        @foreach ($refundMethods as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sales-return-field">
                                    <label for="returnRefundReference">Referensi refund</label>
                                    <div class="sales-return-input-icon"><i class="mdi mdi-link-variant"></i><input type="text" id="returnRefundReference" class="form-control" maxlength="120" placeholder="Nomor transfer / approval"></div>
                                </div>
                                <div class="sales-return-field is-wide">
                                    <label for="returnReason">Alasan retur <span>*</span></label>
                                    <textarea id="returnReason" class="form-control" rows="2" maxlength="1000" placeholder="Contoh: obat tidak sesuai atau pembelian dibatalkan" required></textarea>
                                </div>
                                <div class="sales-return-field is-wide">
                                    <label for="returnNotes">Catatan internal</label>
                                    <textarea id="returnNotes" class="form-control" rows="2" maxlength="2000" placeholder="Opsional untuk kebutuhan audit internal"></textarea>
                                </div>
                            </div>
                        </section>

                        <div class="sales-return-transaction-card" id="returnTransactionInfo" hidden></div>

                        <section class="sales-return-items-section">
                            <div class="sales-return-section-title">
                                <div class="sales-return-card-heading is-compact">
                                    <span><i class="mdi mdi-package-variant-closed-minus"></i></span>
                                    <div><h6>Item yang Diretur</h6><p>Centang item lalu isi qty dalam satuan penjualan.</p></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllReturnable"><i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i>Pilih Semua</button>
                            </div>
                            <div class="table-responsive sales-return-items-scroll">
                                <table class="table sales-return-items-table">
                                    <thead><tr><th></th><th>Obat</th><th>Terjual</th><th>Sudah Retur</th><th>Sisa</th><th>Qty Retur</th><th>Estimasi Refund</th><th>Alasan Item</th></tr></thead>
                                    <tbody id="salesReturnItems"><tr><td colspan="8" class="text-center text-muted py-4">Pilih transaksi terlebih dahulu.</td></tr></tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                    <div class="modal-footer sales-return-modal-footer">
                        <div class="sales-return-footer-note"><i class="mdi mdi-shield-check-outline"></i><span><strong>Aman untuk stok</strong><small>Stok belum berubah sampai retur diposting.</small></span></div>
                        <div class="sales-return-form-total"><small>Estimasi nilai retur</small><strong id="returnFormTotal">Rp 0</strong><span>Nilai final divalidasi sistem.</span></div>
                        <div class="sales-return-footer-actions"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="saveSalesReturn"><i class="mdi mdi-content-save-outline me-1"></i>Simpan Draft</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade sales-return-modal-root" id="salesReturnDetailModal" tabindex="-1" aria-labelledby="salesReturnDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable sales-return-dialog sales-return-detail-dialog">
            <div class="modal-content sales-return-modal">
                <div class="modal-header"><div class="sales-return-modal-brand"><span class="sales-return-modal-icon"><i class="mdi mdi-file-search-outline"></i></span><div><span class="sales-return-modal-kicker">Detail retur</span><h5 class="modal-title" id="salesReturnDetailTitle">Memuat...</h5><p>Ringkasan dokumen, refund, dan batch stok yang terlibat.</p></div></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body" id="salesReturnDetailBody"></div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="mdi mdi-close me-1"></i>Tutup</button></div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.penjualan.returPenjualan.jsMain")
@endpush
