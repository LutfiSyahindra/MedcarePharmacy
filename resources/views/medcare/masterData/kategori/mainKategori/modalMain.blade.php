<div class="modal fade category-modal" id="mainCategoryModal" tabindex="-1" aria-labelledby="mainCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-shape-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="mainCategoryModalLabel">Tambah Main Kategori</h5>
                        <p class="modal-subtitle" id="mainCategoryModalSubtitle">Pilih kategori utama lalu tambahkan kelompok obat turunannya.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="mainCategoryForm">
                    @csrf

                    <div class="category-form-intro">
                        <i class="mdi mdi-source-branch"></i>
                        <div>
                            <strong>Turunan dari kategori utama</strong>
                            <small>Main kategori membantu tim mencari kelompok obat dengan konteks induknya.</small>
                        </div>
                    </div>

                    <div class="category-batch-toolbar" id="mainCategoryBatchToolbar">
                        <span class="category-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="mainCategoryRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addMainCategoryInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="mainCategoryInputWrapper" class="category-batch-list"></div>
                    <input id="mainCategoryId" name="mainCategoryId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitMainCategoryForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
