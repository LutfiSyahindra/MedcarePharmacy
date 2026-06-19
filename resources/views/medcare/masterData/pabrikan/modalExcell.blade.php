<div class="modal fade company-modal" id="pabrikanModalExcell" tabindex="-1" aria-labelledby="pabrikanModalExcellLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-file-excel-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="pabrikanModalExcellLabel">Import Pabrikan</h5>
                        <p class="modal-subtitle">Upload file Excel dari template resmi agar data pabrikan tetap konsisten.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="pabrikanExcelForm" enctype="multipart/form-data">
                    @csrf

                    <div class="company-import-panel">
                        <div>
                            <strong>Langkah import data</strong>
                            <ol class="company-import-steps">
                                <li>Download template Excel.</li>
                                <li>Isi kode, nama, alamat, telepon, dan email.</li>
                                <li>Upload kembali file yang sudah lengkap.</li>
                            </ol>
                        </div>
                        <button type="button" id="pabrikanDownloadTemplateBtn" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-download"></i>
                            Download Template
                        </button>
                    </div>

                    <div class="company-field">
                        <label class="form-label fw-bold mb-2">File Excel</label>
                        <input type="file" id="pabrikanExcelInput" name="file" class="dropify"
                            data-allowed-file-extensions="xls xlsx" data-max-file-size="5M">
                        <small class="company-form-hint">Format yang diperbolehkan: .xls atau .xlsx, maksimal 5MB.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="button" id="pabrikanSubmitExcel" class="btn btn-primary">
                            <i class="mdi mdi-upload"></i>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
