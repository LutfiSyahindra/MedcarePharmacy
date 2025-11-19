<div class="modal fade" id="sediaanModal" tabindex="-1" aria-labelledby="sediaanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="sediaanModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="sediaanForm">
                    @csrf

                    <div id="input-wrapper">
                        <div class="row g-3 mb-2 input-group-item">
                            <div class="col-md-3">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="kode[]" type="text" placeholder="Contoh: TBL">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Sediaan</label>
                                <input class="form-control" name="nama[]" type="text"
                                    placeholder="Contoh: Tablet">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Keterangan</label>
                                <input class="form-control" name="keterangan[]" type="text"
                                    placeholder="Deskripsi tambahan (opsional)">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-input">Hapus</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="button" id="addInput" class="btn btn-success btn-sm">+ Tambah Input</button>
                    </div>

                    <input id="sediaanId" name="sediaanId" type="hidden">

                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="submitForm" class="btn btn-primary"></button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>
