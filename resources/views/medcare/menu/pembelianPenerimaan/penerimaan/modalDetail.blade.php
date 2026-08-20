<div class="modal fade purchase-modal" id="penerimaanModalDetail" tabindex="-1"
    aria-labelledby="penerimaanModalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="penerimaanModalDetailLabel">Detail Penerimaan Barang</h5>
                        <p class="modal-subtitle">Ringkasan faktur, status, dan item barang yang diterima.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <section class="purchase-form-section">
                    <div class="purchase-form-section-header">
                        <div class="purchase-form-section-title">
                            <i class="mdi mdi-information-outline"></i>
                            <div>
                                <strong>Informasi Transaksi</strong>
                                <small>Identitas utama penerimaan barang.</small>
                            </div>
                        </div>
                        <span class="purchase-status" id="detailReceiveStatus">Draft</span>
                    </div>

                    <div class="purchase-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">Nomor Penerimaan</label>
                                <input type="text" class="form-control form-control-sm" id="detailNomorPenerimaan"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Nomor PO</label>
                                <input type="text" class="form-control form-control-sm" id="detailNoPo" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Supplier</label>
                                <input type="text" class="form-control form-control-sm" id="detailSupplier" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Tanggal Terima</label>
                                <input type="text" class="form-control form-control-sm" id="detailTanggalTerima"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Nomor Surat Jalan</label>
                                <input type="text" class="form-control form-control-sm" id="detailNomorSuratJalan"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Catatan</label>
                                <input type="text" class="form-control form-control-sm" id="detailCatatan" readonly>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="purchase-form-section">
                    <div class="purchase-form-section-header">
                        <div class="purchase-form-section-title">
                            <i class="mdi mdi-receipt-text-outline"></i>
                            <div>
                                <strong>Informasi Faktur</strong>
                                <small>Nilai faktur, pembayaran, dan sisa hutang.</small>
                            </div>
                        </div>
                    </div>

                    <div class="purchase-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">Nomor Faktur</label>
                                <input type="text" class="form-control form-control-sm" id="detailNomorFaktur"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Tanggal Faktur</label>
                                <input type="text" class="form-control form-control-sm" id="detailTanggalFaktur"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Tanggal Jatuh Tempo</label>
                                <input type="text" class="form-control form-control-sm" id="detailTanggalJatuhTempo"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Status Pembayaran</label>
                                <input type="text" class="form-control form-control-sm" id="detailStatusPembayaran"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Subtotal</label>
                                <input type="text" class="form-control form-control-sm" id="detailInvoiceSubtotal"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Diskon</label>
                                <input type="text" class="form-control form-control-sm" id="detailInvoiceDiskon"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Pajak</label>
                                <input type="text" class="form-control form-control-sm" id="detailInvoicePajak"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Biaya Lain</label>
                                <input type="text" class="form-control form-control-sm" id="detailInvoiceBiayaLain"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Potongan Ganti Rugi</label>
                                <input type="text" class="form-control form-control-sm" id="detailSupplierCompensationDiscount"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Total Faktur (Asli)</label>
                                <input type="text" class="form-control form-control-sm" id="detailTotalFaktur"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Tagihan Setelah Ganti Rugi</label>
                                <input type="text" class="form-control form-control-sm" id="detailPayableTotal"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Jumlah Dibayar</label>
                                <input type="text" class="form-control form-control-sm" id="detailJumlahDibayar"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Sisa Hutang</label>
                                <input type="text" class="form-control form-control-sm" id="detailSisaHutang"
                                    readonly>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="purchase-form-section">
                    <div class="purchase-form-section-header">
                        <div class="purchase-form-section-title">
                            <i class="mdi mdi-pill-multiple"></i>
                            <div>
                                <strong>Detail Barang</strong>
                                <small>Qty diterima, batch, expired date, harga, tiga diskon dari PO, dan PPN.</small>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                            Total Item: <span id="detailReceiveItemCount">0</span>
                        </span>
                    </div>

                    <div class="purchase-form-section-body pb-1">
                        <div class="table-responsive">
                            <table class="table purchase-detail-table align-middle" id="detailReceiveTable">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Qty</th>
                                        <th>Batch</th>
                                        <th>Expired</th>
                                        <th>Harga Beli</th>
                                        <th>Diskon 1</th>
                                        <th>Diskon 2</th>
                                        <th>Diskon 3</th>
                                        <th>PPN</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div class="purchase-total-panel">
                    <div>
                        <span>Total Faktur</span>
                        <small>Subtotal setelah diskon, PPN, dan biaya lain.</small>
                    </div>
                    <strong id="detailGrandTotal">Rp 0</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="mdi mdi-close-circle-outline"></i>
                    <span>Tutup</span>
                </button>
            </div>
        </div>
    </div>
</div>
