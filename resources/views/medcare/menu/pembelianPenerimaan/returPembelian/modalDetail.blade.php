<div class="modal fade purchase-modal" id="returPembelianModalDetail" tabindex="-1"
    aria-labelledby="returPembelianModalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="returPembelianModalDetailLabel">Detail Retur Pembelian</h5>
                        <p class="modal-subtitle">Ringkasan dokumen retur dan item yang dikembalikan.</p>
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
                                <small>Identitas utama retur pembelian.</small>
                            </div>
                        </div>
                        <span class="purchase-status" id="detailReturnStatus">Draft</span>
                    </div>

                    <div class="purchase-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">Nomor Retur</label>
                                <input type="text" class="form-control form-control-sm" id="detailNomorRetur"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Penerimaan</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnPenerimaan"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Nomor PO</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnPo" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Supplier</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnSupplier"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Tanggal Retur</label>
                                <input type="text" class="form-control form-control-sm" id="detailTanggalRetur"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Ref Supplier</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnRefSupplier"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Total Item</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnItemCount"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Total Qty</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnQtyCount"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Alasan</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnReason"
                                    readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Catatan</label>
                                <input type="text" class="form-control form-control-sm" id="detailReturnNote"
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
                                <small>Qty retur, qty stok keluar, batch, expired date, dan nilai retur.</small>
                            </div>
                        </div>
                    </div>

                    <div class="purchase-form-section-body pb-1">
                        <div class="table-responsive">
                            <table class="table purchase-detail-table align-middle" id="detailReturnTable">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Qty Retur</th>
                                        <th>Batch</th>
                                        <th>Expired</th>
                                        <th>Harga Beli</th>
                                        <th>Diskon</th>
                                        <th>PPN</th>
                                        <th>Total</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div class="purchase-total-panel">
                    <div>
                        <span>Grand Total Retur</span>
                        <small>Subtotal setelah diskon dan PPN.</small>
                    </div>
                    <strong id="detailReturnGrandTotal">Rp 0</strong>
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
