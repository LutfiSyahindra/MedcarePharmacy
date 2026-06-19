<div class="modal fade sediaan-modal" id="sediaanModal" tabindex="-1" aria-labelledby="sediaanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-bottle-tonic-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="sediaanModalLabel">Tambah Sediaan</h5>
                        <p class="modal-subtitle" id="sediaanModalSubtitle">Buat satu atau beberapa bentuk sediaan obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="sediaanForm">
                    @csrf

                    <div class="sediaan-form-intro">
                        <i class="mdi mdi-pill-multiple"></i>
                        <div>
                            <strong>Bentuk sediaan obat</strong>
                            <small>Gunakan kode singkat, nama sediaan yang jelas, dan keterangan ringkas bila diperlukan.</small>
                        </div>
                    </div>

                    <div class="sediaan-batch-toolbar" id="sediaanBatchToolbar">
                        <span class="sediaan-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="sediaanRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addSediaanInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="sediaanInputWrapper" class="sediaan-batch-list"></div>
                    <input id="sediaanId" name="sediaanId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitSediaanForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
