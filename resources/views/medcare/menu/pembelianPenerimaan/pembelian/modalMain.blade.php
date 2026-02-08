<div class="modal fade" id="pembelianModal" tabindex="-1" aria-labelledby="pembelianModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="pembelianModalLabel">Form Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="pembelianForm">
                    @csrf

                    <input type="hidden" class="form-control" name="pembelian_id" id="pembelian_id">

                    <!-- ===================== HEADER PURCHASE ORDER ===================== -->
                    <div class="row g-3 border-bottom pb-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">No. PO</label>
                            <input type="text" class="form-control" name="no_po" placeholder="Contoh: PO-00123"
                                readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Distributor</label>
                            <select class="js-example-basic-single form-select" data-width="100%" name="distributor_id"
                                id="distributor_id" required>
                                <option value="">-- Pilih Distributor --</option>
                                <!-- Data distributor di-load lewat AJAX -->
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>

                            <div class="input-group flatpickr" id="flatpickr-date" data-wrap="true"
                                data-click-opens="true">
                                <input type="text" class="form-control" placeholder="Select date" name="tanggal"
                                    data-input>

                                <span class="input-group-text" data-toggle>
                                    <i data-feather="calendar"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="catatan" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>

                    <!-- ===================== DETAIL OBAT ===================== -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Detail Obat</h6>
                        <button type="button" id="addDetail" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle"></i> Tambah Obat
                        </button>
                    </div>

                    <div id="detail-wrapper">
                        <div class="row g-3 mb-3 detail-item align-items-end border-bottom pb-3">

                            <!-- OBAT -->
                            <div class="col-md-4">
                                <label class="form-label">Obat</label>
                                <select class="js-example-basic-single form-select obat-select" data-width="100%"
                                    name="obat_id[]" required>
                                    <!-- via AJAX -->
                                </select>
                            </div>

                            <!-- SATUAN -->
                            <div class="col-md-3">
                                <label class="form-label">Satuan</label>
                                <select class="form-select satuan-select" name="satuan_id[]" required>
                                    <option value="">-- Pilih Satuan --</option>
                                </select>
                            </div>

                            <!-- QTY -->
                            <div class="col-md-2">
                                <label class="form-label">Qty</label>
                                <input type="number" class="form-control qty" name="qty[]" min="1"
                                    value="1" required>
                            </div>

                            <!-- HARGA -->
                            <div class="col-md-3">
                                <label class="form-label">Harga Estimasi</label>
                                <input type="number" class="form-control harga_estimasi" name="harga_estimasi[]"
                                    min="0" step="0.01" value="0">
                            </div>

                            <!-- SUBTOTAL -->
                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>
                                <input type="number" class="form-control subtotal" name="subtotal[]" readonly>
                            </div>

                            <!-- HAPUS -->
                            <div class="col-md-12 mt-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- ===================== TOTAL ===================== -->
                    <div class="text-end mt-4">
                        <h5>Total Estimasi: <span id="total_estimasi" class="fw-bold">0</span></h5>
                        <input type="hidden" name="total_estimasi" id="total_estimasi_input" value="0">
                    </div>

                    <!-- ===================== FOOTER ===================== -->
                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="submitForm" class="btn btn-primary">Simpan Purchase Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
