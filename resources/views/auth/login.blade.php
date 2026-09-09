<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="theme-color" content="#082f49">

        <title>Masuk | Medcare Pharmacy</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('assets/fonts/feather-font/css/iconfont.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/medcare-login.css') }}">
        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    </head>

    <body>
        <main class="login-page">
            <section class="login-card" aria-labelledby="login-title">
                <aside class="brand-panel" aria-label="Medcare Pharmacy">
                    <div class="brand-panel__glow brand-panel__glow--top" aria-hidden="true"></div>
                    <div class="brand-panel__glow brand-panel__glow--bottom" aria-hidden="true"></div>

                    <div class="brand-panel__header">
                        <div class="brand-logo-wrap">
                            <img src="{{ asset('assets/apotek/LogoResmi.png') }}" alt="Medcare Pharmacy" class="brand-logo">
                        </div>
                        <span class="brand-badge">
                            <span class="brand-badge__dot"></span>
                            Pharmacy Management
                        </span>
                    </div>

                    <div class="brand-panel__content">
                        <span class="eyebrow">SMART PHARMACY SYSTEM</span>
                        <h1>Kelola apotek dengan lebih tenang.</h1>
                        <p>Satu sistem untuk membantu operasional, stok, transaksi, dan laporan apotek Anda tetap rapi setiap hari.</p>

                        <div class="feature-list" aria-label="Keunggulan Medcare Pharmacy">
                            <div class="feature-item">
                                <span class="feature-item__icon"><i data-feather="shield"></i></span>
                                <span>
                                    <strong>Aman &amp; terkontrol</strong>
                                    <small>Akses data sesuai peran pengguna</small>
                                </span>
                            </div>
                            <div class="feature-item">
                                <span class="feature-item__icon"><i data-feather="activity"></i></span>
                                <span>
                                    <strong>Informasi real-time</strong>
                                    <small>Pantau aktivitas apotek lebih cepat</small>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="brand-panel__footer">
                        <span>Medcare Pharmacy</span>
                        <span class="footer-separator"></span>
                        <span>Simplify your pharmacy</span>
                    </div>
                </aside>

                <div class="form-panel">
                    <div class="mobile-brand">
                        <img src="{{ asset('assets/apotek/LogoResmi.png') }}" alt="Medcare Pharmacy">
                    </div>

                    <div class="form-panel__content">
                        <div class="form-heading">
                            <span class="form-heading__icon" aria-hidden="true"><i data-feather="lock"></i></span>
                            <div>
                                <p class="form-kicker">SELAMAT DATANG KEMBALI</p>
                                <h2 id="login-title">Masuk ke akun Anda</h2>
                            </div>
                        </div>
                        <p class="form-description">Silakan masukkan email dan password yang telah terdaftar.</p>

                        @if (session('status'))
                            <div class="status-alert" role="status">
                                <i data-feather="check-circle"></i>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="login-form">
                            @csrf

                            <div class="form-group">
                                <label for="email">Alamat email</label>
                                <div class="input-shell @error('email') input-shell--invalid @enderror">
                                    <i data-feather="mail" class="input-icon"></i>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="nama@apotek.com"
                                        autocomplete="username"
                                        required
                                        autofocus
                                        @error('email') aria-describedby="email-error" aria-invalid="true" @enderror
                                    >
                                </div>
                                @error('email')
                                    <p class="field-error" id="email-error"><i data-feather="alert-circle"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="label-row">
                                    <label for="password">Password</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                                    @endif
                                </div>
                                <div class="input-shell @error('password') input-shell--invalid @enderror">
                                    <i data-feather="key" class="input-icon"></i>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        placeholder="Masukkan password"
                                        autocomplete="current-password"
                                        required
                                        @error('password') aria-describedby="password-error" aria-invalid="true" @enderror
                                    >
                                    <button type="button" class="password-toggle" id="password-toggle" aria-label="Tampilkan password" aria-pressed="false">
                                        <i data-feather="eye" class="eye-open"></i>
                                        <i data-feather="eye-off" class="eye-closed"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="field-error" id="password-error"><i data-feather="alert-circle"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <label class="remember-option" for="remember_me">
                                <input type="checkbox" id="remember_me" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <span class="custom-checkbox"><i data-feather="check"></i></span>
                                <span>Ingat saya di perangkat ini</span>
                            </label>

                            <button type="submit" class="login-button">
                                <span>Masuk ke Medcare</span>
                                <i data-feather="arrow-right"></i>
                            </button>
                        </form>

                        <div class="security-note">
                            <i data-feather="shield"></i>
                            <span>Informasi akun Anda dilindungi dan dienkripsi.</span>
                        </div>
                    </div>

                    <p class="copyright">&copy; {{ date('Y') }} Medcare Pharmacy. All rights reserved.</p>
                </div>
            </section>
        </main>

        <script src="{{ asset('assets/vendors/feather-icons/feather.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.feather) {
                    window.feather.replace({ 'stroke-width': 2 });
                }

                const passwordInput = document.getElementById('password');
                const passwordToggle = document.getElementById('password-toggle');

                if (passwordInput && passwordToggle) {
                    passwordToggle.addEventListener('click', function () {
                        const isPasswordVisible = passwordInput.type === 'text';
                        passwordInput.type = isPasswordVisible ? 'password' : 'text';
                        passwordToggle.classList.toggle('is-visible', !isPasswordVisible);
                        passwordToggle.setAttribute('aria-pressed', String(!isPasswordVisible));
                        passwordToggle.setAttribute('aria-label', isPasswordVisible ? 'Tampilkan password' : 'Sembunyikan password');
                        passwordInput.focus();
                    });
                }
            });
        </script>
    </body>
</html>
