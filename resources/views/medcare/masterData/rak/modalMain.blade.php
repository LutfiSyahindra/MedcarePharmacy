<div class="modal fade" id="rakModal" tabindex="-1" aria-labelledby="rakModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="rakModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="rakForm">
                    @csrf

                    <!-- Wrapper Input -->
                    <div id="input-wrapper">
                        <div class="row g-3 mb-2 input-group-item align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Kode</label>
                                <input type="text" name="kode[]" class="form-control" placeholder="Kode rak">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Rak Penyimpanan Obat</label>
                                <input type="text" name="nama[]" class="form-control" placeholder="Nama rak">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="lokasi[]" class="form-control" placeholder="Lokasi rak">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-danger btn-sm remove-input">
                                    <i data-feather="trash-2"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Tambah Input -->
                    <div class="mt-3">
                        <button type="button" id="addInput" class="btn btn-success btn-sm">
                            <i data-feather="plus-circle"></i> Tambah Input
                        </button>
                    </div>

                    <!-- Hidden ID -->
                    <input type="hidden" id="rakId" name="rakId">

                    <!-- Footer -->
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i data-feather="x-circle"></i> Tutup
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i data-feather="save"></i> Simpan
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>
