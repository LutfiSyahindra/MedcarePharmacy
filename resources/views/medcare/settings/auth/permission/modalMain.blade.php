<div class="modal fade auth-modal" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-shield-plus-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="permissionsModalLabel"></h5>
                        <p class="modal-subtitle">Tambahkan hak akses fitur untuk digunakan oleh role.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permissionsForm">
                    @csrf
                    <div class="auth-field">
                        <label for="name" class="form-label">Name</label>
                        <div class="auth-input-shell">
                            <span class="auth-input-icon"><i class="mdi mdi-shield-key-outline"></i></span>
                            <input id="name" class="form-control" name="name" type="text"
                                placeholder="Contoh: MEDCARE.SETTINGS.AUTH">
                        </div>
                        <small class="auth-form-hint">Gunakan pola nama yang konsisten agar mudah diaudit.</small>
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>
                    <input id="permissionsId" class="form-control" name="permissionsId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
