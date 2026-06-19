<div class="modal fade rak-modal" id="rakModalExcell" tabindex="-1" aria-labelledby="rakModalExcellLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-file-excel-outline"></i></span>
                    <div>
                        <h5 class="modal-title mb-0" id="rakModalExcellLabel">Import Rak Penyimpanan</h5>
                        <p class="modal-subtitle">Upload template Excel berisi kode, nama rak, lokasi, dan keterangan.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="rakExcelForm" enctype="multipart/form-data">
                    @csrf

                    <div class="rak-import-panel">
                        <div>
                            <strong>Gunakan template resmi</strong>
                            <ol class="rak-import-steps">
                                <li>Download template Excel Rak Penyimpanan.</li>
                                <li>Isi kolom Kode, Nama, Lokasi, dan Keterangan.</li>
                                <li>Upload kembali file yang sudah diisi.</li>
                            </ol>
                        </div>
                        <button type="button" id="rakDownloadTemplateBtn" class="btn btn-outline-primary">
                            <i class="mdi mdi-download"></i>
                            Download Template
                        </button>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold mb-2" for="rakExcelInput">Pilih File Excel</label>
                        <input type="file" id="rakExcelInput" name="file" class="dropify"
                            data-allowed-file-extensions="xls xlsx" data-max-file-size="5M" />
                        <small class="rak-form-hint">Format file yang diperbolehkan: .xls atau .xlsx dengan ukuran maksimal 5MB.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>
                        <button type="button" id="rakSubmitExcel" class="btn btn-primary">
                            <i class="mdi mdi-upload"></i>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
