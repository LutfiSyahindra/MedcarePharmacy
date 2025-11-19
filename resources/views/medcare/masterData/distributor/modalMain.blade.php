<div class="modal fade" id="distributorModal" tabindex="-1" aria-labelledby="distributorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="distributorModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="distributorForm">
                    @csrf

                    <div id="input-wrapper">
                        <div class="row g-3 mb-3 input-group-item align-items-end border-bottom pb-3">
                            <div class="col-md-2">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="kode[]" type="text" placeholder="Contoh: SNB">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Distributor</label>
                                <input class="form-control" name="nama[]" type="text" placeholder="Contoh: Sanbe">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Alamat</label>
                                <input class="form-control" name="alamat[]" type="text" placeholder="Alamat lengkap">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Telepon</label>
                                <input class="form-control" name="telepon[]" type="text"
                                    placeholder="Contoh: 08123456789" pattern="[0-9]*" inputmode="numeric"
                                    maxlength="15">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Email</label>
                                <input class="form-control" name="email[]" type="email" placeholder="Alamat Email">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-12 mt-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-input">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="button" id="addInput" class="btn btn-success btn-sm">+ Tambah Input</button>
                    </div>

                    <input id="distributorId" name="distributorId" type="hidden">

                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="submitForm" class="btn btn-primary"></button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>
