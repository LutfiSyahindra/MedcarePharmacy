<div class="modal fade company-modal" id="pabrikanModal" tabindex="-1" aria-labelledby="pabrikanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-factory"></i></span>
                    <div>
                        <h5 class="modal-title" id="pabrikanModalLabel">Tambah Pabrikan</h5>
                        <p class="modal-subtitle" id="pabrikanModalSubtitle">Buat satu atau beberapa data pabrikan obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="pabrikanForm">
                    @csrf

                    <div class="company-form-intro">
                        <i class="mdi mdi-domain"></i>
                        <div>
                            <strong>Profil pabrikan obat</strong>
                            <small>Isi kode, nama pabrikan, alamat, telepon, dan email agar data produksi mudah dilacak.</small>
                        </div>
                    </div>

                    <div class="company-batch-toolbar" id="pabrikanBatchToolbar">
                        <span class="company-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="pabrikanRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addPabrikanInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="pabrikanInputWrapper" class="company-batch-list"></div>
                    <input id="pabrikanId" name="pabrikanId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitPabrikanForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
