<div class="modal fade purchase-modal" id="penerimaanModal" tabindex="-1" aria-labelledby="penerimaanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable receive-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-truck-check-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="penerimaanModalLabel">Form Penerimaan Barang</h5>
                        <p class="modal-subtitle">Catat barang yang diterima dari purchase order.</p>
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

                    <section class="purchase-form-section receive-info-section" id="receiveInfoSection">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-document-outline"></i>
                                <div>
                                    <strong>Informasi Penerimaan</strong>
                                    <small>Nomor penerimaan, PO, supplier, surat jalan, dan tanggal transaksi.</small>
                                </div>
                            </div>
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
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Nomor Surat Jalan</label>
                                    <input type="text" class="form-control" name="nomor_surat_jalan"
                                        placeholder="Opsional">
                                </div>
                                <div class="col-lg-3 col-md-6">
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
                                <div class="col-lg-6">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="catatan" rows="1"
                                        placeholder="Kondisi barang atau catatan tambahan (opsional)"></textarea>
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
                                <i class="mdi mdi-file-check-outline"></i>
                                <div>
                                    <strong>Ringkasan PO Terpilih</strong>
                                    <small class="receive-summary-reference">
                                        <span id="summaryNoPo">-</span>
                                        <i aria-hidden="true"></i>
                                        <span id="summaryTanggalPo">-</span>
                                    </small>
                                </div>
                            </div>
                            <div class="receive-summary-progress" aria-label="Progress penerimaan">
                                <span>Progress</span>
                                <strong id="summaryReceivePercent">0%</strong>
                            </div>
                            <button type="button" class="receive-mobile-section-toggle"
                                data-mobile-panel="#receivePoSummary" aria-expanded="false">
                                <i class="mdi mdi-chevron-down"></i>
                                <span>Rincian</span>
                            </button>
                        </div>
                        <div class="purchase-form-section-body">
                            <div class="receive-summary-grid">
                                <div class="receive-summary-metric receive-summary-metric--branch">
                                    <span class="receive-summary-label">Branch tujuan</span>
                                    <strong id="summaryBranch">-</strong>
                                </div>
                                <div class="receive-summary-metric">
                                    <span class="receive-summary-label">Total estimasi</span>
                                    <strong id="summaryTotalEstimasi">Rp 0</strong>
                                </div>
                                <div class="receive-summary-metric">
                                    <span class="receive-summary-label">Item PO</span>
                                    <strong id="summaryItemPo">0</strong>
                                </div>
                                <div class="receive-summary-metric">
                                    <span class="receive-summary-label">Diterima / sisa</span>
                                    <strong class="receive-summary-quantity">
                                        <span id="summaryFilledQty">0</span>
                                        <span class="receive-summary-quantity-divider">/</span>
                                        <span id="summaryOutstandingQty">0</span>
                                    </strong>
                                </div>
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
                                <span class="receive-item-count">
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
                            <button type="button" class="receive-mobile-section-toggle"
                                data-mobile-panel="#receiveInvoiceSection" aria-expanded="false">
                                <i class="mdi mdi-chevron-down"></i>
                                <span>Rincian nilai</span>
                            </button>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="receive-invoice-lock" id="receiveInvoiceLockNotice">
                                <i class="mdi mdi-lock-clock-outline"></i>
                                <span>Isi detail barang terlebih dahulu.</span>
                            </div>

                            <div class="receive-invoice-board">
                                <div class="receive-invoice-main">
                                    <span>Tagihan Bersih</span>
                                    <strong id="invoiceBoardTotal">Rp 0</strong>
                                    <small id="invoiceBoardFormula">Subtotal - diskon + PPN + biaya lain</small>
                                </div>
                                <small class="receive-payment-hint" id="invoiceBoardHint">
                                    Masukkan nominal yang sudah dibayar untuk faktur ini.
                                </small>
                            </div>

                            <div class="supplier-compensation-apply d-none" id="supplierCompensationApplyPanel">
                                <div class="supplier-compensation-apply-copy">
                                    <span class="supplier-compensation-apply-icon">
                                        <i class="mdi mdi-cash-minus"></i>
                                    </span>
                                    <div>
                                        <strong>Gunakan Ganti Rugi untuk Mengurangi Tagihan</strong>
                                        <small>
                                            Saldo tersedia <b id="supplierCompensationAvailable">Rp 0</b>.
                                            Total asli faktur tetap utuh; potongan hanya mengurangi tagihan yang dibayar.
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
                                            placeholder="Rp 0" readonly>
                                    </div>
                                    <small class="purchase-field-hint">Terisi otomatis dari biaya asuransi dan pengiriman PO, sesuai proporsi qty yang diterima.</small>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Total Faktur</label>
                                    <input type="text" class="form-control invoice-money fw-bold" name="total_faktur"
                                        value="Rp 0" inputmode="numeric" readonly>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label" for="invoicePaidAmount">Nominal Pembayaran Faktur</label>
                                    <div class="receive-money-field is-primary" id="paidAmountField">
                                        <i class="mdi mdi-cash-fast"></i>
                                        <input type="text" class="form-control invoice-money" name="jumlah_dibayar"
                                            id="invoicePaidAmount" value="Rp 0" inputmode="numeric"
                                            autocomplete="off" placeholder="Rp 0">
                                    </div>
                                    <small class="purchase-field-hint">Isi nominal yang sudah dibayar. Jurnal Keuangan dibuat saat penerimaan diposting.</small>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Sisa Hutang</label>
                                    <input type="text" class="form-control invoice-money fw-bold" name="sisa_hutang"
                                        value="Rp 0" inputmode="numeric" readonly>
                                    <small class="purchase-field-hint" id="invoicePaymentStatusText">Status: Belum Dibayar</small>
                                </div>
                                <div class="col-12">
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
                                </div>
                                <input type="hidden" name="status_pembayaran" value="belum_dibayar">
                            </div>
                        </div>
                    </section>

                    <div class="receive-form-actions">
                        <div class="receive-form-total">
                            <span>Total Penerimaan</span>
                            <strong id="receiveGrandTotal">Rp 0</strong>
                            <small><span id="receiveModalItemCount">0</span> item · Qty <span id="receiveModalQtyCount">0</span></small>
                        </div>
                        <div class="receive-form-buttons">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" id="submitPenerimaanForm" class="btn btn-primary">
                                <i class="mdi mdi-content-save-outline"></i>
                                Simpan Draft
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
