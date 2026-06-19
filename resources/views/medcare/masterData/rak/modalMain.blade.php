<div class="modal fade rak-modal" id="rakModal" tabindex="-1" aria-labelledby="rakModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-archive-marker-outline"></i></span>
                    <div>
                        <h5 class="modal-title mb-0" id="rakModalLabel">Tambah Rak Penyimpanan</h5>
                        <p class="modal-subtitle" id="rakModalSubtitle">Buat satu atau beberapa rak penyimpanan obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="rakForm">
                    @csrf

                    <div class="rak-form-intro">
                        <i class="mdi mdi-map-marker-path"></i>
                        <div>
                            <strong>Lengkapi identitas rak</strong>
                            <small>Kode dipakai sebagai referensi cepat, sedangkan lokasi membantu pencarian fisik stok.</small>
                        </div>
                    </div>

                    <div class="rak-batch-toolbar" id="rakBatchToolbar">
                        <span class="rak-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="rakRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addRakInput" class="btn btn-outline-primary">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="rakInputWrapper" class="rak-batch-list"></div>

                    <input type="hidden" id="rakId" name="rakId">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitRakForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
