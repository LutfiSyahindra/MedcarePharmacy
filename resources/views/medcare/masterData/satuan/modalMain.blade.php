<div class="modal fade satuan-modal" id="satuanModal" tabindex="-1" aria-labelledby="satuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-scale-balance"></i></span>
                    <div>
                        <h5 class="modal-title" id="satuanModalLabel">Tambah Satuan</h5>
                        <p class="modal-subtitle" id="satuanModalSubtitle">Buat satu atau beberapa satuan obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="satuanForm">
                    @csrf

                    <div class="satuan-form-intro">
                        <i class="mdi mdi-ruler-square"></i>
                        <div>
                            <strong>Satuan untuk master obat</strong>
                            <small>Gunakan kode singkat dan nama satuan yang konsisten dipakai di transaksi.</small>
                        </div>
                    </div>

                    <div class="satuan-batch-toolbar" id="satuanBatchToolbar">
                        <span class="satuan-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="satuanRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addSatuanInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="satuanInputWrapper" class="satuan-batch-list"></div>
                    <input id="satuanId" name="satuanId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitSatuanForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
