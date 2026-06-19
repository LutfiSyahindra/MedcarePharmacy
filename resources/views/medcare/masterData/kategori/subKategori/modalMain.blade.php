<div class="modal fade category-modal" id="subCategoryModal" tabindex="-1" aria-labelledby="subCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-format-list-bulleted-type"></i></span>
                    <div>
                        <h5 class="modal-title" id="subCategoryModalLabel">Tambah Sub Kategori</h5>
                        <p class="modal-subtitle" id="subCategoryModalSubtitle">Pilih main kategori lalu tambahkan detail klasifikasinya.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="subCategoryForm">
                    @csrf

                    <div class="category-form-intro">
                        <i class="mdi mdi-family-tree"></i>
                        <div>
                            <strong>Detail klasifikasi obat</strong>
                            <small>Sub kategori menjadi level paling detail sebelum dipakai pada master obat.</small>
                        </div>
                    </div>

                    <div class="category-batch-toolbar" id="subCategoryBatchToolbar">
                        <span class="category-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="subCategoryRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addSubCategoryInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="subCategoryInputWrapper" class="category-batch-list"></div>
                    <input id="subCategoryId" name="subCategoryId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitSubCategoryForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
