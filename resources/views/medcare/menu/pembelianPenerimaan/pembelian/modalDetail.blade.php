<div class="modal fade" id="pembelianModalDetail" tabindex="-1" aria-labelledby="pembelianModalDetailLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">

            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="pembelianModalDetailLabel">
                    <i class="bi bi-file-earmark-text me-2"></i> Detail Purchase Order
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <!-- Header PO -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">

                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="bi bi-info-circle me-2"></i>Informasi Purchase Order
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Nomor PO</label>
                                <input type="text" class="form-control form-control-sm" id="detail_no_po" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Distributor</label>
                                <input type="text" class="form-control form-control-sm" id="detail_distributor"
                                    readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Tanggal PO</label>
                                <input type="text" class="form-control form-control-sm" id="detail_tanggal_po"
                                    readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-secondary">Catatan</label>
                                <textarea class="form-control form-control-sm" id="detail_catatan" rows="2" readonly></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Detail Obat -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body pb-1">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-primary mb-0">
                                <i class="bi bi-capsule-pill me-2"></i>Detail Obat
                            </h6>

                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                Total Item: <span id="detail_total_item">0</span>
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="detailObatTable">
                                <thead class="table-light border rounded-3">
                                    <tr>
                                        <th>Nama Obat</th>
                                        <th>Satuan</th>
                                        <th>Qty</th>
                                        <th>Harga Estimasi</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Diisi via JS -->
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- Total -->
                <div class="text-end mt-3">
                    <div
                        class="d-inline-block bg-success bg-opacity-10 border border-success rounded-3 px-4 py-3 shadow-sm">
                        <span class="fw-bold text-success me-2">
                            <i class="bi bi-calculator me-1"></i> Total Estimasi:
                        </span>
                        <span id="detail_total_estimasi" class="fw-bold fs-5 text-success">Rp 0</span>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-top bg-light py-3 d-flex justify-content-between align-items-center">

                <!-- Tombol PDF -->
                <button type="button" class="btn btn-outline-danger d-flex align-items-center gap-2" id="btnPrintPDF">
                    <i class="mdi mdi-file-pdf-box fs-5"></i>
                    <span>Cetak PDF</span>
                </button>

                <!-- Tombol Tutup -->
                <button type="button" class="btn btn-secondary d-flex align-items-center gap-2 px-4"
                    data-bs-dismiss="modal">
                    <i class="mdi mdi-close-circle-outline fs-5"></i>
                    <span>Tutup</span>
                </button>

            </div>

        </div>
    </div>
</div>
