<div class="modal fade obat-modal" id="obatModalExcell" tabindex="-1" aria-labelledby="obatModalExcellLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-file-excel-outline"></i></span>
                    <div>
                        <h5 class="modal-title mb-0" id="obatModalExcellLabel">Import Master Obat</h5>
                        <p class="modal-subtitle">Upload template Excel berisi identitas, relasi master, stok minimum, harga beli, dan status obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="obatExcelForm" enctype="multipart/form-data">
                    @csrf

                    <div class="obat-import-panel">
                        <div>
                            <strong>Gunakan kode master data</strong>
                            <ol class="obat-import-steps">
                                <li>Download template Master Obat.</li>
                                <li>Isi kolom relasi menggunakan kode kategori, golongan, satuan, sediaan, pabrikan, distributor, dan rak.</li>
                                <li>Gunakan nilai 1 atau 0 untuk is_generik dan is_active.</li>
                            </ol>
                        </div>
                        <button type="button" id="obatDownloadTemplateBtn" class="btn btn-outline-primary">
                            <i class="mdi mdi-download"></i>
                            Download Template
                        </button>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold mb-2" for="obatExcelInput">Pilih File Excel</label>
                        <input type="file" id="obatExcelInput" name="file" class="dropify"
                            data-allowed-file-extensions="xls xlsx" data-max-file-size="5M" />
                        <small class="obat-form-hint">Format file yang diperbolehkan: .xls atau .xlsx dengan ukuran maksimal 5MB.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>
                        <button type="button" id="obatSubmitExcel" class="btn btn-primary">
                            <i class="mdi mdi-upload"></i>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
