<div class="modal fade category-modal" id="kategoriUtamaModal" tabindex="-1" aria-labelledby="kategoriUtamaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-tag-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="kategoriUtamaModalLabel">Tambah Kategori</h5>
                        <p class="modal-subtitle" id="kategoriUtamaModalSubtitle">Buat satu atau beberapa kategori obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="kategoriUtamaForm">
                    @csrf

                    <div class="category-form-intro">
                        <i class="mdi mdi-sitemap-outline"></i>
                        <div>
                            <strong>Kategori obat</strong>
                            <small>Gunakan kode singkat dan nama yang mudah dikenali oleh tim farmasi.</small>
                        </div>
                    </div>

                    <div class="category-batch-toolbar" id="kategoriUtamaBatchToolbar">
                        <span class="category-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="kategoriUtamaRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addKategoriUtamaInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="kategoriUtamaInputWrapper" class="category-batch-list"></div>
                    <input id="kategoriUtamaId" name="kategoriUtamaId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitKategoriUtamaForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
