<div class="modal fade purchase-modal" id="pembelianModalDetail" tabindex="-1"
    aria-labelledby="pembelianModalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-file-document-check-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="pembelianModalDetailLabel">Detail Purchase Order</h5>
                        <p class="modal-subtitle">Tinjau informasi transaksi dan seluruh rincian obat.</p>
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
                                <strong>Informasi Purchase Order</strong>
                                <small>Identitas utama transaksi pembelian.</small>
                            </div>
                        </div>
                    </div>

                    <div class="purchase-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Nomor PO</label>
                                <input type="text" class="form-control form-control-sm" id="detail_no_po" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Distributor</label>
                                <input type="text" class="form-control form-control-sm" id="detail_distributor"
                                    readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Tanggal PO</label>
                                <input type="text" class="form-control form-control-sm" id="detail_tanggal_po"
                                    readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small">Catatan</label>
                                <textarea class="form-control form-control-sm" id="detail_catatan" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="purchase-form-section">
                    <div class="purchase-form-section-header">
                        <div class="purchase-form-section-title">
                            <i class="mdi mdi-pill-multiple"></i>
                            <div>
                                <strong>Detail Obat</strong>
                                <small>Item dan nilai estimasi yang diajukan.</small>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                            Total Item: <span id="detail_total_item">0</span>
                        </span>
                    </div>

                    <div class="purchase-form-section-body pb-1">
                        <div class="table-responsive">
                            <table class="table purchase-detail-table align-middle" id="detailObatTable">
                                <thead>
                                    <tr>
                                        <th>Nama Obat</th>
                                        <th>Satuan</th>
                                        <th>Qty</th>
                                        <th>Harga Estimasi</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <div class="purchase-total-panel">
                    <div>
                        <span>Total Estimasi Purchase Order</span>
                        <small>Akumulasi seluruh item obat pada transaksi ini.</small>
                    </div>
                    <strong id="detail_total_estimasi">Rp 0</strong>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-danger" id="btnPrintPDF">
                    <i class="mdi mdi-file-pdf-box fs-5"></i>
                    <span>Cetak PDF</span>
                </button>

                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="mdi mdi-close-circle-outline fs-5"></i>
                    <span>Tutup</span>
                </button>
            </div>
        </div>
    </div>
</div>
