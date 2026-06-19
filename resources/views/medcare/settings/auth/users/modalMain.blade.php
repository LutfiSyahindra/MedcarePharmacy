<div class="modal fade auth-modal" id="usersModal" tabindex="-1" aria-labelledby="usersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-account-plus-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="usersModalLabel"></h5>
                        <p class="modal-subtitle">Lengkapi identitas user dan password login.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="signupForm">
                    @csrf
                    <div class="auth-field">
                        <label for="name" class="form-label">Name</label>
                        <div class="auth-input-shell">
                            <span class="auth-input-icon"><i class="mdi mdi-account-outline"></i></span>
                            <input id="name" class="form-control" name="name" type="text" placeholder="Nama lengkap user"
                                autocomplete="name">
                        </div>
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>
                    <div class="auth-field">
                        <label for="email" class="form-label">Email</label>
                        <div class="auth-input-shell">
                            <span class="auth-input-icon"><i class="mdi mdi-email-outline"></i></span>
                            <input id="email" class="form-control" name="email" type="email"
                                placeholder="nama@domain.com" autocomplete="email">
                        </div>
                        <div class="invalid-feedback" id="error-email"></div>
                    </div>
                    <div class="auth-field">
                        <label for="password" class="form-label">Password</label>
                        <div class="auth-input-shell">
                            <span class="auth-input-icon"><i class="mdi mdi-lock-outline"></i></span>
                            <input id="password" class="form-control" name="password" type="password"
                                placeholder="Minimal 6 karakter" autocomplete="new-password">
                            <button type="button" class="auth-password-toggle" data-target="#password"
                                aria-label="Tampilkan password">
                                <i class="mdi mdi-eye-outline"></i>
                            </button>
                        </div>
                        <small class="auth-form-hint">Kosongkan saat edit jika password tidak ingin diubah.</small>
                        <div class="invalid-feedback" id="error-password"></div>
                    </div>
                    <div class="auth-field">
                        <label for="confirm_password" class="form-label">Confirm password</label>
                        <div class="auth-input-shell">
                            <span class="auth-input-icon"><i class="mdi mdi-lock-check-outline"></i></span>
                            <input id="confirm_password" class="form-control" name="password_confirmation"
                                type="password" placeholder="Ulangi password" autocomplete="new-password">
                            <button type="button" class="auth-password-toggle" data-target="#confirm_password"
                                aria-label="Tampilkan konfirmasi password">
                                <i class="mdi mdi-eye-outline"></i>
                            </button>
                        </div>
                    </div>
                    <input id="userId" class="form-control" name="userId" type="hidden">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i>
                            Close
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
