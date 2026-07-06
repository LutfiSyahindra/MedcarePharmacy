<div class="modal fade category-modal" id="subGolonganModal" tabindex="-1" aria-labelledby="subGolonganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-format-list-bulleted-type"></i></span>
                    <div>
                        <h5 class="modal-title" id="subGolonganModalLabel">Tambah Sub Golongan</h5>
                        <p class="modal-subtitle" id="subGolonganModalSubtitle">Pilih main golongan lalu tambahkan detail turunannya.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="subGolonganForm">
                    @csrf

                    <div class="category-form-intro">
                        <i class="mdi mdi-family-tree"></i>
                        <div>
                            <strong>Detail golongan obat</strong>
                            <small>Sub golongan menjadi level paling detail sebelum dipakai pada master obat.</small>
                        </div>
                    </div>

                    <div class="category-batch-toolbar" id="subGolonganBatchToolbar">
                        <span class="category-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="subGolonganRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addSubGolonganInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="subGolonganInputWrapper" class="category-batch-list"></div>
                    <input id="subGolonganId" name="subGolonganId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitSubGolonganForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
