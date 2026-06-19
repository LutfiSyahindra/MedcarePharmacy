<div class="modal fade branch-modal" id="branchModal" tabindex="-1" aria-labelledby="branchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-store-plus-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="branchModalLabel"></h5>
                        <p class="modal-subtitle">Lengkapi identitas cabang dan kontak operasional.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="branchForm">
                    @csrf
                    <div class="branch-form-grid">
                        <div class="branch-field">
                            <label for="code" class="form-label">Kode</label>
                            <div class="branch-input-shell">
                                <span class="branch-input-icon"><i class="mdi mdi-barcode"></i></span>
                                <input id="code" class="form-control" name="code" type="text"
                                    placeholder="Contoh: BRC-001" autocomplete="off">
                            </div>
                            <small class="branch-form-hint">Kode unik cabang untuk referensi internal.</small>
                            <div class="invalid-feedback" id="error-code"></div>
                        </div>
                        <div class="branch-field">
                            <label for="name" class="form-label">Branch</label>
                            <div class="branch-input-shell">
                                <span class="branch-input-icon"><i class="mdi mdi-storefront-outline"></i></span>
                                <input id="name" class="form-control" name="name" type="text"
                                    placeholder="Nama cabang">
                            </div>
                            <small class="branch-form-hint">Gunakan nama yang mudah dikenali tim.</small>
                            <div class="invalid-feedback" id="error-name"></div>
                        </div>
                        <div class="branch-field is-wide">
                            <label for="address" class="form-label">Alamat</label>
                            <div class="branch-input-shell">
                                <span class="branch-input-icon"><i class="mdi mdi-map-marker-outline"></i></span>
                                <input id="address" class="form-control" name="address" type="text"
                                    placeholder="Alamat lengkap cabang">
                            </div>
                            <div class="invalid-feedback" id="error-address"></div>
                        </div>
                        <div class="branch-field">
                            <label for="phone" class="form-label">No Hp</label>
                            <div class="branch-input-shell">
                                <span class="branch-input-icon"><i class="mdi mdi-phone-outline"></i></span>
                                <input id="phone" class="form-control" name="phone" type="tel"
                                    placeholder="Nomor kontak cabang">
                            </div>
                            <div class="invalid-feedback" id="error-phone"></div>
                        </div>
                        <div class="branch-field">
                            <label for="email" class="form-label">Email</label>
                            <div class="branch-input-shell">
                                <span class="branch-input-icon"><i class="mdi mdi-email-outline"></i></span>
                                <input id="email" class="form-control" name="email" type="email"
                                    placeholder="branch@domain.com">
                            </div>
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>
                    </div>
                    <input id="branchId" class="form-control" name="branchId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Branch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
