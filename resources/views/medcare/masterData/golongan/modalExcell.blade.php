<div class="modal fade golongan-modal" id="golonganModalExcell" tabindex="-1" aria-labelledby="golonganModalExcellLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-file-excel-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="golonganModalExcellLabel">Import Golongan</h5>
                        <p class="modal-subtitle">Upload file Excel dari template resmi agar data golongan tetap konsisten.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="golonganExcelForm" enctype="multipart/form-data">
                    @csrf

                    <div class="golongan-import-panel">
                        <div>
                            <strong>Langkah import data</strong>
                            <ol class="golongan-import-steps">
                                <li>Download template Excel.</li>
                                <li>Isi kode dan nama golongan sesuai kolom template.</li>
                                <li>Upload kembali file yang sudah lengkap.</li>
                            </ol>
                        </div>
                        <button type="button" id="golonganDownloadTemplateBtn" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-download"></i>
                            Download Template
                        </button>
                    </div>

                    <div class="golongan-field">
                        <label class="form-label fw-bold mb-2">File Excel</label>
                        <input type="file" id="golonganExcelInput" name="file" class="dropify"
                            data-allowed-file-extensions="xls xlsx" data-max-file-size="5M">
                        <small class="golongan-form-hint">Format yang diperbolehkan: .xls atau .xlsx, maksimal 5MB.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="button" id="golonganSubmitExcel" class="btn btn-primary">
                            <i class="mdi mdi-upload"></i>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
