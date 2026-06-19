<div class="modal fade branch-modal" id="assignBranchModal" tabindex="-1" aria-labelledby="assignBranchModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-account-switch-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="assignBranchModalLabel"></h5>
                        <p class="modal-subtitle">Pilih user yang bertugas di cabang ini.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignBranchForm">
                    @csrf
                    <div class="branch-select-toolbar">
                        <span class="branch-count-pill">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span id="userSelectionCount">0</span> user dipilih
                        </span>
                        <div class="branch-select-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllUsers">
                                <i class="mdi mdi-select-all"></i>
                                Pilih Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearUsersSelection">
                                <i class="mdi mdi-close-circle-outline"></i>
                                Bersihkan
                            </button>
                        </div>
                    </div>
                    <div class="branch-field">
                        <label class="form-label">Pilih User</label>
                        <select name="user_id[]" id="userSelect" class="js-example-basic-multiple form-select"
                            multiple="multiple" data-width="100%"></select>
                        <small class="branch-form-hint">Gunakan pencarian untuk menemukan user lebih cepat.</small>
                    </div>
                    <input id="branchId" class="form-control" name="branchId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="assignBranchSubmit" class="btn btn-primary">
                            <i class="mdi mdi-account-check-outline"></i>
                            Simpan Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
