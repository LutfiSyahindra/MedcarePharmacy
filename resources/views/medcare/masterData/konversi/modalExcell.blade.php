<div class="modal fade obat-modal" id="konversiModalExcell" tabindex="-1" aria-labelledby="konversiModalExcellLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-file-excel-outline"></i></span>
                    <div>
                        <h5 class="modal-title mb-0" id="konversiModalExcellLabel">Import Konversi Satuan</h5>
                        <p class="modal-subtitle">Upload file Excel dari template yang sudah disediakan.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="konversiExcellForm" enctype="multipart/form-data">
                    @csrf
                    <section class="obat-form-section">
                        <div class="obat-section-header">
                            <i class="mdi mdi-file-download-outline"></i>
                            <div>
                                <strong>Template Excel</strong>
                                <small>Unduh template, isi data, lalu upload kembali.</small>
                            </div>
                        </div>
                        <div class="obat-section-body">
                            <div class="konversi-modal-note mb-3">
                                <i class="mdi mdi-lightbulb-outline"></i>
                                <div>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <strong class="text-dark">Pastikan format kolom sesuai template.</strong>
                                        <button type="button" id="downloadTemplateBtn" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-download me-1"></i>
                                            Download Template
                                        </button>
                                    </div>
                                    <ol class="mb-0 ps-3">
                                        <li>Unduh template dengan tombol di atas.</li>
                                        <li>Isi data pada template sesuai kolom yang tersedia.</li>
                                        <li>Upload kembali template yang telah diisi.</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="obat-field is-wide">
                                <label class="form-label">Pilih File Excel</label>
                                <input type="file" id="myDropify" name="file" class="dropify"
                                    data-allowed-file-extensions="xls xlsx" data-max-file-size="5M" />
                                <small class="text-muted">Format file yang diperbolehkan: .xls, .xlsx (maks. 5MB)</small>
                            </div>
                        </div>
                    </section>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>
                        <button type="button" id="submitFormExcell" class="btn btn-primary">
                            <i class="mdi mdi-upload"></i>
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
