<div class="modal fade obat-modal" id="konversiModal" tabindex="-1" aria-labelledby="konversiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-scale-balance"></i></span>
                    <div>
                        <h5 class="modal-title mb-0" id="konversiModalLabel">Tambah Konversi Satuan Obat</h5>
                        <p class="modal-subtitle" id="konversiModalSubtitle">Pilih obat, satuan pembelian, dan jumlah konversi ke PCS.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="konversiForm">
                    @csrf
                    <input id="konversiId" name="konversiId" type="hidden">

                    <div class="konversi-modal-note mb-3">
                        <i class="mdi mdi-information-outline"></i>
                        <div>
                            <strong class="d-block text-dark">Gunakan satuan pembelian yang biasa muncul di transaksi.</strong>
                            <span>Contoh: 1 Box = 100 PCS, 1 Strip = 10 PCS. Centang default untuk satuan utama.</span>
                        </div>
                    </div>

                    <div id="input-wrapper">
                        <div class="konversi-input-card input-group-item">
                            <div class="konversi-input-card-header">
                                <strong><i class="mdi mdi-swap-horizontal-bold"></i> Baris Konversi</strong>
                                <button type="button" class="btn btn-sm btn-light remove-input">
                                    <i class="mdi mdi-trash-can-outline me-1"></i>Hapus
                                </button>
                            </div>
                            <div class="konversi-input-card-body">
                                <div class="obat-fields">
                                    <div class="obat-field is-wide">
                                        <label class="form-label">Obat</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-pill"></i></span>
                                            <select class="js-example-basic-single form-select obatSelect" data-width="100%" name="obat_id[]" required>
                                                <option value="">-- Pilih Obat --</option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Satuan Pembelian</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-package-variant"></i></span>
                                            <select class="form-select satuanSelect" name="satuan_id[]" required>
                                                <option value="">Memuat data...</option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Konversi ke PCS</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-calculator-variant-outline"></i></span>
                                            <input class="form-control" type="number" name="konversi[]" min="1" placeholder="Contoh: 10" required>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Default</label>
                                        <div class="konversi-default-box">
                                            <input type="hidden" name="is_default[]" value="0" class="defaultHidden">
                                            <input class="form-check-input defaultCheck" type="checkbox" value="1">
                                            <label class="form-check-label">Jadikan default</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" id="addInput" class="btn btn-outline-primary">
                            <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Konversi
                        </button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>

                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Konversi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
