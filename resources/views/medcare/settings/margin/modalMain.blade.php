<div class="modal fade" id="marginsModal" tabindex="-1" aria-labelledby="marginsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="marginsModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form id="marginsForm">
                    @csrf
                    <div class="mb-3">
                        <label for="tingkat" class="form-label">Tingkat</label>
                        <select id="tingkat" class="form-select" name="tingkat">
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="kategoriUtama">Kategori Utama</option>
                            <option value="kategori">Kategori</option>
                            <option value="sub_kategori">Sub Kategori</option>
                            <option value="obat">Obat</option>
                        </select>
                        <div class="invalid-feedback" id="error-tingkat"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference</label>
                        <select name="reference_id[]" id="reference_idSelect"
                            class="js-example-basic-multiple form-select" multiple data-width="100%"></select>
                        <div class="invalid-feedback" id="error-reference_id"></div>
                    </div>
                    <div class="mb-3">
                        <label for="faktor_jual" class="form-label">Faktor Jual</label>
                        <input id="faktor_jual" class="form-control" name="faktor_jual" type="number" step="0.01"
                            min="0">
                        <div class="invalid-feedback" id="error-faktor_jual"></div>
                    </div>
                    <input id="marginsId" class="form-control" name="marginsId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="submitForm" class="btn btn-primary"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
