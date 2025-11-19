<div class="modal fade" id="konversiModal" tabindex="-1" aria-labelledby="konversiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="konversiModalLabel">
                    <i class="mdi mdi-scale-balance me-2"></i> Konversi Satuan Obat
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="konversiForm">
                    @csrf
                    <input id="konversiId" name="konversiId" type="hidden">

                    <div id="input-wrapper">

                        <!-- Group Item -->
                        <div class="p-3 border rounded-3 mb-3 shadow-sm bg-light input-group-item">

                            <div class="row g-3">

                                <!-- Obat -->
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">
                                        <i class="mdi mdi-pill me-1"></i> Obat
                                    </label>
                                    <select class="js-example-basic-single form-select obatSelect" data-width="100%"
                                        name="obat_id[]" required>
                                        <option value="">-- Pilih Obat --</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Satuan -->
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-secondary">
                                        <i class="mdi mdi-package-variant me-1"></i> Satuan Pembelian
                                    </label>
                                    <select class="form-select satuanSelect" name="satuan_id[]" required>
                                        <option value="">Memuat data...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Konversi -->
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-secondary">
                                        <i class="mdi mdi-arrow-collapse-vertical me-1"></i> Konversi ke PCS
                                    </label>
                                    <input class="form-control" type="number" name="konversi[]" min="1"
                                        placeholder="Contoh: 10" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Default -->
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary d-block">
                                        <i class="mdi mdi-check-circle-outline me-1"></i> Default
                                    </label>

                                    <div class="form-check mt-1">
                                        <!-- Hidden field untuk nilai default -->
                                        <input type="hidden" name="is_default[]" value="0" class="defaultHidden">

                                        <!-- Checkbox TANPA name -->
                                        <input class="form-check-input defaultCheck" type="checkbox" value="1">

                                        <label class="form-check-label">Jadikan Default</label>
                                    </div>
                                </div>

                            </div>

                            <!-- Tombol Hapus -->
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-input">
                                    <i class="mdi mdi-trash-can-outline me-1"></i> Hapus Baris
                                </button>
                            </div>

                        </div>

                    </div>

                    <!-- Tambah Row -->
                    <div class="mt-2">
                        <button type="button" id="addInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Konversi
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer mt-4 border-top pt-3">

                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline me-1"></i> Tutup
                        </button>

                        <button type="submit" id="submitForm" class="btn btn-primary px-4">
                            <i class="mdi mdi-content-save-outline me-1"></i> Simpan Konversi
                        </button>

                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
