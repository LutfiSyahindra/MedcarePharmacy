<div class="modal fade margin-modal" id="marginsModal" tabindex="-1" aria-labelledby="marginsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-percent-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="marginsModalLabel"></h5>
                        <p class="modal-subtitle">Pilih tingkat, reference, dan faktor jual untuk aturan margin.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="marginsForm">
                    @csrf
                    <div class="margin-form-grid">
                        <div class="margin-field">
                            <label for="tingkat" class="form-label">Tingkat</label>
                            <div class="margin-input-shell">
                                <span class="margin-input-icon"><i class="mdi mdi-layers-outline"></i></span>
                                <select id="tingkat" class="form-select" name="tingkat">
                                    <option value="">Pilih tingkat</option>
                                    <option value="kategoriUtama">Kategori Utama</option>
                                    <option value="kategori">Kategori</option>
                                    <option value="sub_kategori">Sub Kategori</option>
                                    <option value="obat">Obat</option>
                                </select>
                            </div>
                            <small class="margin-form-hint">Tingkat menentukan daftar reference yang dimuat.</small>
                            <div class="invalid-feedback" id="error-tingkat"></div>
                        </div>
                        <div class="margin-field">
                            <label for="faktor_jual" class="form-label">Faktor Jual</label>
                            <div class="margin-input-shell">
                                <span class="margin-input-icon"><i class="mdi mdi-chart-line"></i></span>
                                <input id="faktor_jual" class="form-control" name="faktor_jual" type="number"
                                    step="0.01" min="0" placeholder="Contoh: 1.20">
                            </div>
                            <small class="margin-form-hint">
                                Estimasi margin: <strong id="marginPreview">0%</strong>
                            </small>
                            <div class="invalid-feedback" id="error-faktor_jual"></div>
                        </div>
                        <div class="margin-field is-wide">
                            <div class="margin-reference-tools">
                                <span class="margin-count-pill">
                                    <i class="mdi mdi-check-circle-outline"></i>
                                    <span id="referenceSelectionCount">0</span> reference dipilih
                                </span>
                                <div class="margin-reference-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllReferences">
                                        <i class="mdi mdi-select-all"></i>
                                        Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearReferences">
                                        <i class="mdi mdi-close-circle-outline"></i>
                                        Bersihkan
                                    </button>
                                </div>
                            </div>
                            <label class="form-label">Reference</label>
                            <select name="reference_id[]" id="reference_idSelect"
                                class="js-example-basic-multiple form-select" multiple data-width="100%"></select>
                            <small class="margin-form-hint" id="referenceHint">Pilih tingkat terlebih dahulu.</small>
                            <div class="invalid-feedback" id="error-reference_id"></div>
                        </div>
                    </div>
                    <input id="marginsId" class="form-control" name="marginsId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Margin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
