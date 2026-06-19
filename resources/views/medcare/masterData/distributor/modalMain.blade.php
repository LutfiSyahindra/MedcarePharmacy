<div class="modal fade company-modal" id="distributorModal" tabindex="-1" aria-labelledby="distributorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-archive"></i></span>
                    <div>
                        <h5 class="modal-title" id="distributorModalLabel">Tambah Distributor</h5>
                        <p class="modal-subtitle" id="distributorModalSubtitle">Buat satu atau beberapa data distributor obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="distributorForm">
                    @csrf

                    <div class="company-form-intro">
                        <i class="mdi mdi-truck-delivery-outline"></i>
                        <div>
                            <strong>Profil distributor obat</strong>
                            <small>Isi kode, nama distributor, alamat, telepon, dan email untuk kebutuhan pembelian dan penerimaan.</small>
                        </div>
                    </div>

                    <div class="company-batch-toolbar" id="distributorBatchToolbar">
                        <span class="company-count-pill">
                            <i class="mdi mdi-format-list-numbered"></i>
                            <span id="distributorRowCount">1</span> baris input
                        </span>
                        <button type="button" id="addDistributorInput" class="btn btn-success btn-sm">
                            <i class="mdi mdi-plus-circle-outline"></i>
                            Tambah Baris
                        </button>
                    </div>

                    <div id="distributorInputWrapper" class="company-batch-list"></div>
                    <input id="distributorId" name="distributorId" type="hidden">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitDistributorForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
