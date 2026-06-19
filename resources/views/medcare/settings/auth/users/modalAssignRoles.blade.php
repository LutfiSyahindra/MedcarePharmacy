<div class="modal fade auth-modal" id="assignRolesModal" tabindex="-1" aria-labelledby="assignRolesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-account-key-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="assignRolesModalLabel"></h5>
                        <p class="modal-subtitle">Tempelkan role yang sesuai untuk user ini.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignRolesForm">
                    @csrf
                    <div class="auth-select-toolbar">
                        <span class="auth-count-pill">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span id="rolesSelectionCount">0</span> role dipilih
                        </span>
                        <div class="auth-select-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllRoles">
                                <i class="mdi mdi-select-all"></i>
                                Pilih Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearRolesSelection">
                                <i class="mdi mdi-close-circle-outline"></i>
                                Bersihkan
                            </button>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label class="form-label">Pilih Roles</label>
                        <select name="roles_id[]" id="rolesSelect" class="js-example-basic-multiple form-select"
                            multiple="multiple" data-width="100%"></select>
                        <small class="auth-form-hint">Gunakan pencarian untuk menemukan role lebih cepat.</small>
                    </div>
                    <input id="userssId" class="form-control" name="userssId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="assignRoles" class="btn btn-primary">
                            <i class="mdi mdi-account-check-outline"></i>
                            Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
