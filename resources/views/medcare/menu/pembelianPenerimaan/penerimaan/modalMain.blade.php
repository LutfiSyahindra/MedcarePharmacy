<div class="modal fade purchase-modal" id="penerimaanModal" tabindex="-1" aria-labelledby="penerimaanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable receive-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-truck-check-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="penerimaanModalLabel">Form Penerimaan Barang</h5>
                        <p class="modal-subtitle">Pilih PO approved, isi detail barang, lalu lengkapi faktur.</p>
                    </div>
                </div>
                <div class="purchase-modal-header-meta">
                    <span class="purchase-modal-status" id="receiveHeaderStatus">
                        <i class="mdi mdi-file-clock-outline"></i>
                        Draft sebelum posting stok
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body">
                <form id="penerimaanForm">
                    @csrf
                    <input type="hidden" name="penerimaan_id" id="penerimaan_id">

                    <div class="purchase-modal-overview" aria-label="Ringkasan proses penerimaan">
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-shield-check-outline"></i></span>
                            <div>
                                <strong>PO Approved</strong>
                                <small>Dropdown hanya memuat PO yang sudah disetujui.</small>
                            </div>
                        </div>
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-barcode"></i></span>
                            <div>
                                <strong>Detail Barang</strong>
                                <small>Qty, batch, expired, harga, dan PPN diisi; diskon mengikuti PO.</small>
                            </div>
                        </div>
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-warehouse"></i></span>
                            <div>
                                <strong>Faktur</strong>
                                <small>Nilai tagihan dihitung dari detail barang yang diterima.</small>
                            </div>
                        </div>
                    </div>

                    <div class="receive-form-progress" aria-label="Progress kelengkapan form">
                        <div class="receive-progress-step is-active" id="receiveStepPo">
                            <span>1</span>
                            <div>
                                <strong>Pilih PO</strong>
                                <small>Belum dipilih</small>
                            </div>
                        </div>
                        <div class="receive-progress-line"></div>
                        <div class="receive-progress-step" id="receiveStepItems">
                            <span>2</span>
                            <div>
                                <strong>Detail Barang</strong>
                                <small>Belum ada qty</small>
                            </div>
                        </div>
                        <div class="receive-progress-line"></div>
                        <div class="receive-progress-step" id="receiveStepInvoice">
                            <span>3</span>
                            <div>
                                <strong>Faktur</strong>
                                <small>Menunggu detail</small>
                            </div>
                        </div>
                    </div>

                    <div class="receive-command-center" aria-live="polite">
                        <div class="receive-command-status">
                            <span class="receive-command-icon" id="receiveGuidanceIcon">
                                <i class="mdi mdi-cursor-default-click-outline"></i>
                            </span>
                            <div>
                                <strong id="receiveGuidanceTitle">Mulai dari PO approved</strong>
                                <small id="receiveGuidanceText">Pilih nomor PO untuk memuat supplier, sisa qty, dan detail obat.</small>
                            </div>
                        </div>
                        <div class="receive-requirements" aria-label="Checklist kelengkapan penerimaan">
                            <button type="button" class="receive-requirement is-active receive-jump-link"
                                id="receiveRequirementPo" data-target="receiveInfoSection">
                                <i class="mdi mdi-file-check-outline"></i>
                                <span>PO</span>
                            </button>
                            <button type="button" class="receive-requirement receive-jump-link"
                                id="receiveRequirementItems" data-target="receiveDetailSection">
                                <i class="mdi mdi-barcode-scan"></i>
                                <span>Item</span>
                            </button>
                            <button type="button" class="receive-requirement receive-jump-link"
                                id="receiveRequirementInvoice" data-target="receiveInvoiceSection">
                                <i class="mdi mdi-receipt-text-outline"></i>
                                <span>Faktur</span>
                            </button>
                        </div>
                        <div class="receive-command-actions">
                            <span class="receive-problem-pill" id="receiveProblemCount">
                                <i class="mdi mdi-information-outline"></i>
                                0 catatan
                            </span>
                            <button type="button" class="btn btn-light btn-sm receive-jump-link"
                                data-target="receiveDetailSection">
                                <i class="mdi mdi-format-list-checks"></i>
                                Detail
                            </button>
                            <button type="button" class="btn btn-primary btn-sm receive-jump-link"
                                data-target="receiveInvoiceSection">
                                <i class="mdi mdi-receipt-text-plus-outline"></i>
                                Faktur
                            </button>
                        </div>
                    </div>

                    <div class="receive-cockpit" aria-live="polite">
                        <div class="receive-cockpit-main">
                            <span class="receive-cockpit-eyebrow">
                                <i class="mdi mdi-radar"></i>
                                Live Receipt
                            </span>
                            <strong id="cockpitGrandTotal">Rp 0</strong>
                            <small id="cockpitSubtitle">Pilih PO approved untuk memulai penerimaan barang.</small>
                            <div class="receive-cockpit-meter" aria-label="Progress qty diterima">
                                <span id="cockpitReceiveMeter"></span>
                            </div>
                        </div>
                        <div class="receive-cockpit-grid">
                            <div class="receive-cockpit-card">
                                <span><i class="mdi mdi-file-document-check-outline"></i> PO</span>
                                <strong id="cockpitPo">-</strong>
                                <small id="cockpitSupplier">Pilih PO approved</small>
                            </div>
                            <div class="receive-cockpit-card">
                                <span><i class="mdi mdi-package-variant-closed-check"></i> Item Siap</span>
                                <strong id="cockpitReadyItems">0/0</strong>
                                <small id="cockpitItemHint">Belum ada detail barang.</small>
                            </div>
                            <div class="receive-cockpit-card">
                                <span><i class="mdi mdi-receipt-text-check-outline"></i> Faktur</span>
                                <strong id="cockpitInvoiceStatus">Belum diisi</strong>
                                <small id="cockpitDebt">Sisa hutang Rp 0</small>
                            </div>
                        </div>
                    </div>

                    <section class="purchase-form-section receive-info-section" id="receiveInfoSection">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-document-outline"></i>
                                <div>
                                    <strong>Informasi Penerimaan</strong>
                                    <small>Nomor penerimaan, PO, supplier, surat jalan, dan tanggal transaksi.</small>
                                </div>
                            </div>
                            <span class="receive-section-status is-active" id="receiveInfoSectionStatus">
                                <i class="mdi mdi-cursor-default-click-outline"></i>
                                Pilih PO
                            </span>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Nomor Penerimaan</label>
                                    <input type="text" class="form-control" name="nomor_penerimaan"
                                        id="nomor_penerimaan" readonly>
                                    <small class="purchase-field-hint">Dibuat otomatis per bulan.</small>
                                </div>
                                <div class="col-lg-5 col-md-6">
                                    <label class="form-label">Nomor PO Approved</label>
                                    <select class="form-select" name="purchase_order_id" id="purchase_order_id"
                                        data-width="100%" required>
                                        <option value="">-- Pilih PO --</option>
                                    </select>
                                    <small class="purchase-field-hint">PO yang sudah habis diterima tidak ditampilkan.</small>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Supplier</label>
                                    <input type="text" class="form-control" id="receive_supplier" readonly>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Nomor Surat Jalan</label>
                                    <input type="text" class="form-control" name="nomor_surat_jalan"
                                        placeholder="Opsional">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Tanggal Penerimaan</label>
                                    <div class="input-group flatpickr" id="receive-date" data-wrap="true"
                                        data-click-opens="true">
                                        <input type="text" class="form-control" placeholder="Pilih tanggal"
                                            name="tanggal_penerimaan" data-input required>
                                        <span class="input-group-text" data-toggle>
                                            <i class="mdi mdi-calendar-month-outline"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="catatan" rows="2"
                                        placeholder="Catatan penerimaan, kondisi barang, atau instruksi khusus..."></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="supplier-compensation-alert d-none" id="supplierCompensationAlert"
                        aria-live="polite">
                        <div class="supplier-compensation-alert-header">
                            <span class="supplier-compensation-alert-icon">
                                <i class="mdi mdi-hand-coin-outline"></i>
                            </span>
                            <div class="supplier-compensation-alert-copy">
                                <strong>Supplier Masih Memiliki Ganti Rugi yang Belum Direalisasikan</strong>
                                <p class="mb-0">
                                    Konfirmasikan dengan supplier saat barang datang agar retur sebelumnya tidak terlewat.
                                </p>
                            </div>
                            <a href="{{ route('returPembelian.returPembelian') }}" target="_blank"
                                class="btn btn-sm btn-outline-danger">
                                <i class="mdi mdi-open-in-new"></i> Buka Retur
                            </a>
                        </div>

                        <div class="supplier-compensation-metrics">
                            <div>
                                <span>Retur Belum Selesai</span>
                                <strong id="supplierCompensationReturnCount">0</strong>
                            </div>
                            <div>
                                <span>Total Belum Diganti</span>
                                <strong id="supplierCompensationOutstanding">Rp 0</strong>
                            </div>
                            <div>
                                <span>Jatuh Tempo</span>
                                <strong id="supplierCompensationOverdue">0</strong>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table supplier-compensation-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nomor Retur</th>
                                        <th>Branch</th>
                                        <th>Tanggal / Batas Waktu</th>
                                        <th>Status</th>
                                        <th>Sudah Diganti</th>
                                        <th>Sisa</th>
                                    </tr>
                                </thead>
                                <tbody id="supplierCompensationRows"></tbody>
                            </table>
                        </div>
                        <small class="supplier-compensation-more d-none" id="supplierCompensationMore"></small>
                    </section>

                    <section class="purchase-form-section receive-po-summary d-none" id="receivePoSummary">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-search-outline"></i>
                                <div>
                                    <strong>Ringkasan PO Terpilih</strong>
                                    <small>Gunakan sisa qty sebagai batas penerimaan.</small>
                                </div>
                            </div>
                        </div>
                        <div class="purchase-form-section-body">
                            <div class="receive-summary-grid">
                                <div><span>Nomor PO</span><strong id="summaryNoPo">-</strong></div>
                                <div><span>Tanggal PO</span><strong id="summaryTanggalPo">-</strong></div>
                                <div><span>Branch</span><strong id="summaryBranch">-</strong></div>
                                <div><span>Total Estimasi</span><strong id="summaryTotalEstimasi">Rp 0</strong></div>
                                <div><span>Total Item PO</span><strong id="summaryItemPo">0</strong></div>
                                <div><span>Sisa Qty PO</span><strong id="summaryOutstandingQty">0</strong></div>
                                <div><span>Qty Diisi</span><strong id="summaryFilledQty">0</strong></div>
                                <div><span>Progress Terima</span><strong id="summaryReceivePercent">0%</strong></div>
                            </div>
                            <div class="receive-progress-meter" aria-label="Progress qty diterima">
                                <span id="summaryReceiveMeter"></span>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section receive-detail-section" id="receiveDetailSection">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-pill-multiple"></i>
                                <div>
                                    <strong>Detail Barang Diterima</strong>
                                    <small>Isi hanya qty yang benar-benar diterima. Baris qty 0 tidak disimpan.</small>
                                </div>
                            </div>
                            <div class="receive-detail-actions">
                                <span class="receive-section-status" id="receiveDetailSectionStatus">
                                    <i class="mdi mdi-timer-sand"></i>
                                    Menunggu PO
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                    <span id="receiveLineCount">0</span> item PO
                                </span>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="fillAllOutstanding">
                                    <i class="mdi mdi-format-list-checks"></i>
                                    Terima Semua Sisa
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllQty">
                                    <i class="mdi mdi-broom"></i>
                                    Kosongkan Qty
                                </button>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="receive-detail-live-summary d-none" id="receiveDetailLiveSummary">
                                <div>
                                    <span>Siap disimpan</span>
                                    <strong id="receiveReadyRows">0 item</strong>
                                </div>
                                <div>
                                    <span>Perlu dicek</span>
                                    <strong id="receiveWarningRows">0 item</strong>
                                </div>
                                <div>
                                    <span>Qty diterima</span>
                                    <strong id="receiveLiveQty">0</strong>
                                </div>
                                <div>
                                    <span>Nilai barang</span>
                                    <strong id="receiveLiveValue">Rp 0</strong>
                                </div>
                            </div>
                            <div id="receiveDetailEmpty" class="receive-empty-state">
                                <i class="mdi mdi-file-search-outline"></i>
                                <strong>Pilih PO terlebih dahulu</strong>
                                <span>Detail obat akan muncul otomatis dari PO approved yang dipilih.</span>
                            </div>
                            <div class="table-responsive receive-detail-editor d-none" id="receiveDetailEditor">
                                <table class="table purchase-detail-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th>PO / Sisa</th>
                                            <th>Qty Diterima</th>
                                            <th>No Batch</th>
                                            <th>Expired Date</th>
                                            <th>Harga Beli</th>
                                            <th>Diskon PO</th>
                                            <th>PPN %</th>
                                            <th>Subtotal</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="receiveDetailRows"></tbody>
                                </table>
                            </div>
                            <div class="receive-next-step d-none" id="receiveInvoicePrompt">
                                <div>
                                    <strong>Detail barang siap</strong>
                                    <small>Total tagihan sudah terbentuk dari item yang diterima.</small>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" id="goToInvoiceSection">
                                    <i class="mdi mdi-receipt-text-plus-outline"></i>
                                    Isi Faktur
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section receive-invoice-section is-locked" id="receiveInvoiceSection">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-receipt-text-outline"></i>
                                <div>
                                    <strong>Informasi Faktur</strong>
                                    <small>Tanggal faktur, nilai tagihan, status bayar, dan sisa hutang.</small>
                                </div>
                            </div>
                            <div class="receive-invoice-header-actions">
                                <span class="receive-section-status" id="receiveInvoiceSectionStatus">
                                    <i class="mdi mdi-lock-clock-outline"></i>
                                    Terkunci
                                </span>
                                <span class="receive-invoice-status-pill is-unpaid" id="invoicePaymentStatusBadge">
                                    <i class="mdi mdi-clock-alert-outline"></i>
                                    Belum Dibayar
                                </span>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="receive-invoice-lock" id="receiveInvoiceLockNotice">
                                <i class="mdi mdi-lock-clock-outline"></i>
                                <span>Isi detail barang terlebih dahulu.</span>
                            </div>

                            <div class="receive-invoice-board">
                                <div class="receive-invoice-main">
                                    <span>Total Tagihan</span>
                                    <strong id="invoiceBoardTotal">Rp 0</strong>
                                    <small id="invoiceBoardFormula">Subtotal - diskon + PPN + biaya lain</small>
                                </div>
                                <div class="receive-payment-summary">
                                    <div>
                                        <span>Dibayar</span>
                                        <strong id="invoiceBoardPaid">Rp 0</strong>
                                    </div>
                                    <div>
                                        <span>Sisa Hutang</span>
                                        <strong id="invoiceBoardDebt">Rp 0</strong>
                                    </div>
                                    <div>
                                        <span>Progress</span>
                                        <strong id="invoicePaidPercent">0%</strong>
                                    </div>
                                </div>
                                <div class="receive-payment-meter" aria-label="Progress pembayaran faktur">
                                    <span id="invoicePaidMeter"></span>
                                </div>
                                <div class="receive-payment-actions" aria-label="Aksi cepat pembayaran faktur">
                                    <button type="button" class="btn btn-light btn-sm receive-payment-action"
                                        data-payment-action="none">
                                        <i class="mdi mdi-cash-remove"></i>
                                        Belum Bayar
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm receive-payment-action"
                                        data-payment-action="half">
                                        <i class="mdi mdi-chart-donut"></i>
                                        Bayar 50%
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm receive-payment-action"
                                        data-payment-action="full">
                                        <i class="mdi mdi-cash-check"></i>
                                        Lunas
                                    </button>
                                </div>
                                <small class="receive-payment-hint" id="invoiceBoardHint">
                                    Isi item penerimaan untuk menghitung tagihan.
                                </small>
                            </div>

                            <div class="supplier-compensation-apply d-none" id="supplierCompensationApplyPanel">
                                <div class="supplier-compensation-apply-copy">
                                    <span class="supplier-compensation-apply-icon">
                                        <i class="mdi mdi-cash-minus"></i>
                                    </span>
                                    <div>
                                        <strong>Potong Ganti Rugi dari Faktur Ini</strong>
                                        <small>
                                            Saldo tersedia <b id="supplierCompensationAvailable">Rp 0</b>.
                                            Potongan dialokasikan ke retur jatuh tempo atau terlama terlebih dahulu.
                                        </small>
                                    </div>
                                </div>
                                <div class="form-check form-switch supplier-compensation-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="applySupplierCompensation">
                                    <label class="form-check-label" for="applySupplierCompensation">Gunakan potongan</label>
                                </div>
                                <div class="supplier-compensation-amount">
                                    <label for="supplierCompensationDiscount">Nominal Potongan</label>
                                    <div class="receive-money-field">
                                        <i class="mdi mdi-hand-coin-outline"></i>
                                        <input type="text" class="form-control invoice-money"
                                            name="supplier_compensation_discount" id="supplierCompensationDiscount"
                                            value="Rp 0" inputmode="numeric" autocomplete="off" disabled>
                                    </div>
                                    <small>Maksimal <span id="supplierCompensationMax">Rp 0</span></small>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Nomor Faktur</label>
                                    <input type="text" class="form-control" name="nomor_faktur"
                                        placeholder="Contoh: INV/2026/001" required>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Tanggal Faktur</label>
                                    <div class="input-group flatpickr" id="invoice-date" data-wrap="true"
                                        data-click-opens="true">
                                        <input type="text" class="form-control" placeholder="Pilih tanggal"
                                            name="tanggal_faktur" data-input required>
                                        <span class="input-group-text" data-toggle>
                                            <i class="mdi mdi-calendar-month-outline"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Tanggal Jatuh Tempo</label>
                                    <div class="input-group flatpickr" id="invoice-due-date" data-wrap="true"
                                        data-click-opens="true">
                                        <input type="text" class="form-control" placeholder="Pilih tanggal"
                                            name="tanggal_jatuh_tempo" data-input>
                                        <span class="input-group-text" data-toggle>
                                            <i class="mdi mdi-calendar-alert-outline"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Subtotal</label>
                                    <input type="text" class="form-control invoice-money" name="subtotal"
                                        value="Rp 0" inputmode="numeric" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Diskon</label>
                                    <input type="text" class="form-control invoice-money" data-invoice-field="diskon"
                                        value="Rp 0" inputmode="numeric" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Pajak</label>
                                    <input type="text" class="form-control invoice-money" name="pajak"
                                        value="Rp 0" inputmode="numeric" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Biaya Lain</label>
                                    <div class="receive-money-field">
                                        <i class="mdi mdi-cash-plus"></i>
                                        <input type="text" class="form-control invoice-money" name="biaya_lain"
                                            value="Rp 0" inputmode="numeric" autocomplete="off"
                                            placeholder="Rp 0">
                                    </div>
                                    <small class="purchase-field-hint">Tambahkan ongkir, admin, atau biaya supplier.</small>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Total Faktur</label>
                                    <input type="text" class="form-control invoice-money fw-bold" name="total_faktur"
                                        value="Rp 0" inputmode="numeric" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Status Pembayaran</label>
                                    <select class="form-select" name="status_pembayaran">
                                        <option value="belum_dibayar">Belum Dibayar</option>
                                        <option value="sebagian">Sebagian</option>
                                        <option value="lunas">Lunas</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Jumlah Dibayar</label>
                                    <div class="receive-money-field is-primary" id="paidAmountField">
                                        <i class="mdi mdi-cash-fast"></i>
                                        <input type="text" class="form-control invoice-money" name="jumlah_dibayar"
                                            value="Rp 0" inputmode="numeric" autocomplete="off"
                                            placeholder="Rp 0">
                                    </div>
                                    <small class="purchase-field-hint">Tidak bisa melebihi total faktur.</small>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Sisa Hutang</label>
                                    <input type="text" class="form-control invoice-money fw-bold" name="sisa_hutang"
                                        value="Rp 0" inputmode="numeric" readonly>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="purchase-total-panel">
                        <div class="purchase-total-copy">
                            <span>Total Faktur</span>
                            <small>Subtotal dihitung dari detail barang, diskon, PPN, dan biaya lain.</small>
                        </div>
                        <div class="purchase-total-stats">
                            <span><strong id="receiveModalItemCount">0</strong> item</span>
                            <span>Qty <strong id="receiveModalQtyCount">0</strong></span>
                            <span>Subtotal <strong id="receiveModalSubtotal">Rp 0</strong></span>
                            <span>Diskon <strong id="receiveModalDiscount">Rp 0</strong></span>
                            <span>PPN <strong id="receiveModalTax">Rp 0</strong></span>
                            <span>Ganti Rugi <strong id="receiveModalCompensationDiscount">Rp 0</strong></span>
                        </div>
                        <strong id="receiveGrandTotal">Rp 0</strong>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Batal
                        </button>
                        <button type="submit" id="submitPenerimaanForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
