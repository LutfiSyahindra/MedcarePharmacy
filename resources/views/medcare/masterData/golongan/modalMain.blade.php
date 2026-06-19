<div class="modal fade golongan-modal" id="golonganModal" tabindex="-1" aria-labelledby="golonganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-shape-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="golonganModalLabel">Tambah Golongan</h5>
                        <p class="modal-subtitle" id="golonganModalSubtitle">Buat satu atau beberapa golongan obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="golonganForm">
                    @csrf

                    <div class="golongan-form-intro">
                        <i class="mdi mdi-pill-multiple"></i>
                        <div>
                            <strong>Klasifikasi golongan obat</strong>
                            <small>Gunakan kode singkat, nama golongan yang jelas, dan keterangan ringkas bila diperlukan.</small>
                        </div>
                    </div>

                    <div class="golongan-batch-toolbar" id="golonganBatchToolbar">
                        <span class="golongan-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="golonganRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addGolonganInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="golonganInputWrapper" class="golongan-batch-list"></div>
                    <input id="golonganId" name="golonganId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitGolonganForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
