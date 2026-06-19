<div class="modal fade auth-modal" id="assignPermissionsModal" tabindex="-1"
    aria-labelledby="assignPermissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-shield-key-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="assignPermissionsModalLabel"></h5>
                        <p class="modal-subtitle">Pilih permission yang melekat pada role ini.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignPermissionsForm">
                    @csrf
                    <div class="auth-select-toolbar">
                        <span class="auth-count-pill">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span id="permissionsSelectionCount">0</span> permission dipilih
                        </span>
                        <div class="auth-select-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPermissions">
                                <i class="mdi mdi-select-all"></i>
                                Pilih Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearPermissionsSelection">
                                <i class="mdi mdi-close-circle-outline"></i>
                                Bersihkan
                            </button>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label class="form-label">Pilih Permissions</label>
                        <select name="permissions_id[]" id="permissionsSelect"
                            class="js-example-basic-multiple form-select" multiple="multiple"
                            data-width="100%"></select>
                        <small class="auth-form-hint">Permission dapat dipilih lebih dari satu untuk setiap role.</small>
                    </div>
                    <input id="rolessId" class="form-control" name="rolessId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="assignPermissions" class="btn btn-primary">
                            <i class="mdi mdi-shield-check-outline"></i>
                            Simpan Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
