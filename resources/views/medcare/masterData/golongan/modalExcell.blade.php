<!-- Modal Upload Golongan Excel -->
<div class="modal fade" id="golonganModalExcell" tabindex="-1" aria-labelledby="golonganModalExcellLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="golonganModalExcellLabel">Upload Data Golongan (Excel)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="golonganExcellForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info border-start border-3 border-info shadow-sm" role="alert">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="fs-6 mb-0">Note:</strong>
                                    <button type="button" id="downloadTemplateBtn"
                                        class="btn btn-primary btn-sm d-flex align-items-center">
                                        <i data-feather="download" class="me-1"></i>
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

                        <div class="col-md-12">
                            <label class="form-label fw-bold mb-2">Pilih File Excel</label>
                            <input type="file" id="myDropify" name="file" class="dropify"
                                data-allowed-file-extensions="xls xlsx" data-max-file-size="5M" />
                            <small class="text-muted">Format file yang diperbolehkan: .xls, .xlsx (maks. 5MB)</small>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i data-feather="x"></i> Tutup
                        </button>
                        <button type="button" id="submitFormExcell" class="btn btn-primary">
                            <i data-feather="upload"></i> Upload
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
