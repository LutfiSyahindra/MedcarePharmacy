<div class="modal fade auth-modal" id="rolesModal" tabindex="-1" aria-labelledby="rolesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-account-key-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="rolesModalLabel"></h5>
                        <p class="modal-subtitle">Buat kelompok akses yang mudah dikenali oleh tim.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rolesForm">
                    @csrf
                    <div class="auth-field">
                        <label for="name" class="form-label">Name</label>
                        <div class="auth-input-shell">
                            <span class="auth-input-icon"><i class="mdi mdi-account-key-outline"></i></span>
                            <input id="name" class="form-control" name="name" type="text"
                                placeholder="Contoh: Apoteker, Admin Cabang">
                        </div>
                        <small class="auth-form-hint">Nama role sebaiknya singkat dan menggambarkan tanggung jawab.</small>
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>
                    <input id="rolesId" class="form-control" name="rolesId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
