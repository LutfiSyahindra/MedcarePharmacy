<div class="modal fade category-modal" id="mainGolonganModal" tabindex="-1" aria-labelledby="mainGolonganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-shape-plus-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="mainGolonganModalLabel">Tambah Main Golongan</h5>
                        <p class="modal-subtitle" id="mainGolonganModalSubtitle">Pilih golongan lalu tambahkan kelompok turunannya.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="mainGolonganForm">
                    @csrf

                    <div class="category-form-intro">
                        <i class="mdi mdi-source-branch"></i>
                        <div>
                            <strong>Turunan dari golongan obat</strong>
                            <small>Main golongan membantu pemetaan obat dengan hirarki golongan yang baru.</small>
                        </div>
                    </div>

                    <div class="category-batch-toolbar" id="mainGolonganBatchToolbar">
                        <span class="category-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="mainGolonganRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addMainGolonganInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="mainGolonganInputWrapper" class="category-batch-list"></div>
                    <input id="mainGolonganId" name="mainGolonganId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitMainGolonganForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
