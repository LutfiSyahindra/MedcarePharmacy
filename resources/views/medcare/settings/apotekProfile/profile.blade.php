@extends("template.partials.app")

@push("style")
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @include("medcare.settings.apotekProfile.style")
@endpush

@section("content")
    @php
        $logoUrl = $profile?->logo_url;
        $profileValue = fn (string $key, mixed $fallback = null) => old($key, $profile?->{$key} ?? $fallback);
        $mapLatitude = old("latitude", $profile?->latitude);
        $mapLongitude = old("longitude", $profile?->longitude);
    @endphp

    <div class="apotek-profile-page">
        <nav aria-label="breadcrumb" class="apotek-breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route("dashboard") }}">Dashboard</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active" aria-current="page">Profile Apotek</li>
            </ol>
        </nav>

        <section class="apotek-hero">
            <div class="apotek-hero-copy">
                <span class="apotek-kicker"><i data-feather="shield"></i> Identitas resmi cabang</span>
                <h1>Profile Apotek</h1>
                <p>Kelola identitas, lokasi, legalitas, dan informasi operasional setiap cabang dari satu tempat.</p>
            </div>
            @if ($selectedBranch)
                <div class="apotek-branch-badge">
                    <span class="apotek-status-dot {{ $selectedBranch->is_active ? "is-active" : "" }}"></span>
                    <span>
                        <small>Cabang terpilih</small>
                        <strong>{{ $selectedBranch->name }}</strong>
                    </span>
                </div>
            @endif
        </section>

        @if (session("success"))
            <div class="alert apotek-alert apotek-alert-success" role="alert">
                <i data-feather="check-circle"></i>
                <div><strong>Tersimpan</strong><span>{{ session("success") }}</span></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert apotek-alert apotek-alert-danger" role="alert">
                <i data-feather="alert-circle"></i>
                <div>
                    <strong>Data belum dapat disimpan</strong>
                    <span>Periksa kembali field yang ditandai. {{ $errors->first() }}</span>
                </div>
            </div>
        @endif

        @unless ($selectedBranch)
            <section class="apotek-empty-state">
                <span class="apotek-empty-icon"><i data-feather="git-branch"></i></span>
                <h2>Belum ada cabang yang dapat dikelola</h2>
                <p>Hubungi Admin untuk menambahkan atau menugaskan cabang ke akun Anda.</p>
            </section>
        @else
            <form method="POST" action="{{ route("settings.apotek-profile.update") }}" enctype="multipart/form-data"
                id="apotekProfileForm" novalidate>
                @csrf
                @method("PUT")

                <div class="apotek-toolbar">
                    <label for="profileBranchSelector" class="apotek-branch-selector">
                        <span><i data-feather="git-branch"></i> Cabang apotek</span>
                        <select name="branch_id" id="profileBranchSelector" class="form-select @error("branch_id") is-invalid @enderror">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((int) old("branch_id", $selectedBranch->id) === $branch->id)>
                                    {{ $branch->code }} — {{ $branch->name }}{{ $branch->is_active ? "" : " (Nonaktif)" }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <div class="apotek-toolbar-note">
                        <i data-feather="info"></i>
                        <span>Setiap cabang menyimpan profile dan logo masing-masing.</span>
                    </div>
                    <button type="submit" class="btn apotek-save-button">
                        <i data-feather="save"></i>
                        <span>Simpan perubahan</span>
                    </button>
                </div>

                <div class="apotek-layout">
                    <aside class="apotek-side-column">
                        <section class="apotek-card apotek-logo-card">
                            <div class="apotek-card-heading">
                                <span class="apotek-section-icon"><i data-feather="image"></i></span>
                                <div><h2>Logo apotek</h2><p>Identitas visual pada dokumen dan struk.</p></div>
                            </div>

                            <div class="apotek-logo-preview" id="apotekLogoPreview">
                                <img src="{{ $logoUrl ?: "" }}" alt="Preview logo {{ $profileValue("name", $selectedBranch->name) }}"
                                    id="apotekLogoImage" @if (! $logoUrl) hidden @endif>
                                <div class="apotek-logo-placeholder" id="apotekLogoPlaceholder" @if ($logoUrl) hidden @endif>
                                    <span>{{ mb_strtoupper(mb_substr($profileValue("name", $selectedBranch->name), 0, 1)) }}</span>
                                    <small>LOGO</small>
                                </div>
                            </div>

                            <label class="apotek-upload-button" for="logoInput">
                                <i data-feather="upload-cloud"></i>
                                <span>Pilih logo</span>
                            </label>
                            <input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp" hidden>
                            <input type="hidden" name="remove_logo" id="removeLogoInput" value="0">
                            <p class="apotek-file-help">PNG, JPG, atau WEBP · maksimal 2 MB. Rasio persegi disarankan.</p>
                            @error("logo")<div class="apotek-field-error">{{ $message }}</div>@enderror

                            @if ($logoUrl)
                                <button type="button" class="apotek-remove-logo" id="removeLogoButton">
                                    <i data-feather="trash-2"></i> Hapus logo saat disimpan
                                </button>
                            @endif
                        </section>

                        <section class="apotek-card apotek-completeness-card">
                            <div class="apotek-card-heading compact">
                                <span class="apotek-section-icon mint"><i data-feather="activity"></i></span>
                                <div><h2>Kelengkapan profile</h2><p>Field utama yang perlu tersedia.</p></div>
                            </div>
                            @php
                                $requiredChecks = [
                                    "Identitas" => filled($profile?->name),
                                    "Kontak" => filled($profile?->phone) || filled($profile?->whatsapp),
                                    "Alamat & lokasi" => filled($profile?->address) && filled($profile?->latitude),
                                    "Legalitas" => filled($profile?->pharmacy_license_number),
                                    "Apoteker PJ" => filled($profile?->pharmacist_name),
                                ];
                                $completedCount = collect($requiredChecks)->filter()->count();
                                $completion = (int) round(($completedCount / count($requiredChecks)) * 100);
                            @endphp
                            <div class="apotek-completion-score"><strong>{{ $completion }}%</strong><span>{{ $completedCount }}/{{ count($requiredChecks) }} bagian lengkap</span></div>
                            <div class="progress apotek-progress"><div class="progress-bar" style="width: {{ $completion }}%"></div></div>
                            <ul class="apotek-check-list">
                                @foreach ($requiredChecks as $label => $complete)
                                    <li class="{{ $complete ? "is-complete" : "" }}">
                                        <i data-feather="{{ $complete ? "check-circle" : "circle" }}"></i>{{ $label }}
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    </aside>

                    <main class="apotek-main-column">
                        <section class="apotek-card">
                            <div class="apotek-card-heading">
                                <span class="apotek-section-icon"><i data-feather="home"></i></span>
                                <div><h2>Identitas apotek</h2><p>Nama resmi dan pesan yang mewakili cabang.</p></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-lg-7">
                                    <label class="form-label" for="name">Nama apotek <em>*</em></label>
                                    <input type="text" name="name" id="name" value="{{ $profileValue("name", $selectedBranch->name) }}"
                                        class="form-control @error("name") is-invalid @enderror" maxlength="150" placeholder="Contoh: Apotek Sehat Sentosa">
                                    @error("name")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-5">
                                    <label class="form-label" for="slogan">Slogan apotek</label>
                                    <input type="text" name="slogan" id="slogan" value="{{ $profileValue("slogan") }}"
                                        class="form-control @error("slogan") is-invalid @enderror" maxlength="255" placeholder="Sehat lebih dekat">
                                    @error("slogan")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="phone">Telepon</label>
                                    <div class="apotek-input-icon"><i data-feather="phone"></i><input type="text" name="phone" id="phone"
                                        value="{{ $profileValue("phone", $selectedBranch->phone) }}" class="form-control @error("phone") is-invalid @enderror" placeholder="021 555 1234"></div>
                                    @error("phone")<div class="apotek-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="whatsapp">WhatsApp</label>
                                    <div class="apotek-input-icon"><i data-feather="message-circle"></i><input type="text" name="whatsapp" id="whatsapp"
                                        value="{{ $profileValue("whatsapp") }}" class="form-control @error("whatsapp") is-invalid @enderror" placeholder="0812 3456 7890"></div>
                                    @error("whatsapp")<div class="apotek-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="email">Email</label>
                                    <div class="apotek-input-icon"><i data-feather="mail"></i><input type="email" name="email" id="email"
                                        value="{{ $profileValue("email", $selectedBranch->email) }}" class="form-control @error("email") is-invalid @enderror" placeholder="halo@apotek.co.id"></div>
                                    @error("email")<div class="apotek-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="website">Website</label>
                                    <div class="apotek-input-icon"><i data-feather="globe"></i><input type="url" name="website" id="website"
                                        value="{{ $profileValue("website") }}" class="form-control @error("website") is-invalid @enderror" placeholder="https://apotek.co.id"></div>
                                    @error("website")<div class="apotek-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="instagram">Instagram</label>
                                    <div class="apotek-input-icon"><i data-feather="instagram"></i><input type="text" name="instagram" id="instagram"
                                        value="{{ $profileValue("instagram") }}" class="form-control @error("instagram") is-invalid @enderror" placeholder="@apoteksehat"></div>
                                    @error("instagram")<div class="apotek-field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </section>

                        <section class="apotek-card">
                            <div class="apotek-card-heading">
                                <span class="apotek-section-icon coral"><i data-feather="map-pin"></i></span>
                                <div><h2>Alamat & lokasi</h2><p>Klik peta, cari alamat, atau gunakan lokasi perangkat.</p></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="address">Alamat lengkap <em>*</em></label>
                                    <textarea name="address" id="address" rows="3" class="form-control @error("address") is-invalid @enderror"
                                        placeholder="Nama jalan, nomor bangunan, patokan">{{ $profileValue("address", $selectedBranch->address) }}</textarea>
                                    @error("address")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4"><label class="form-label" for="village">Kelurahan / Desa</label><input type="text" name="village" id="village" value="{{ $profileValue("village") }}" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label" for="district">Kecamatan</label><input type="text" name="district" id="district" value="{{ $profileValue("district") }}" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label" for="city">Kota / Kabupaten</label><input type="text" name="city" id="city" value="{{ $profileValue("city") }}" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label" for="province">Provinsi</label><input type="text" name="province" id="province" value="{{ $profileValue("province") }}" class="form-control"></div>
                                <div class="col-md-6"><label class="form-label" for="postal_code">Kode pos</label><input type="text" name="postal_code" id="postal_code" value="{{ $profileValue("postal_code") }}" class="form-control" maxlength="10"></div>
                            </div>

                            <div class="apotek-map-panel">
                                <div class="apotek-map-toolbar">
                                    <div class="apotek-map-search">
                                        <i data-feather="search"></i>
                                        <input type="search" id="mapSearchInput" placeholder="Cari nama jalan, area, atau kota">
                                        <button type="button" id="mapSearchButton">Cari lokasi</button>
                                    </div>
                                    <button type="button" class="apotek-location-button" id="currentLocationButton">
                                        <i data-feather="crosshair"></i><span>Lokasi saya</span>
                                    </button>
                                </div>
                                <div id="apotekLocationMap" data-latitude="{{ $mapLatitude }}" data-longitude="{{ $mapLongitude }}"></div>
                                <div class="apotek-map-footer">
                                    <span id="mapStatus"><i data-feather="mouse-pointer"></i> Klik peta untuk menentukan titik apotek.</span>
                                    <a href="#" id="openMapLink" target="_blank" rel="noopener" @if (! $mapLatitude || ! $mapLongitude) hidden @endif>
                                        Buka peta <i data-feather="external-link"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="row g-3 apotek-coordinate-row">
                                <div class="col-md-6">
                                    <label class="form-label" for="latitude">Latitude <em>*</em></label>
                                    <input type="number" step="any" name="latitude" id="latitude" value="{{ $mapLatitude }}" class="form-control @error("latitude") is-invalid @enderror" placeholder="-6.2000000">
                                    @error("latitude")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="longitude">Longitude <em>*</em></label>
                                    <input type="number" step="any" name="longitude" id="longitude" value="{{ $mapLongitude }}" class="form-control @error("longitude") is-invalid @enderror" placeholder="106.8166667">
                                    @error("longitude")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </section>

                        <section class="apotek-card">
                            <div class="apotek-card-heading">
                                <span class="apotek-section-icon amber"><i data-feather="award"></i></span>
                                <div><h2>Legalitas & penanggung jawab</h2><p>Informasi izin yang penting untuk operasional cabang.</p></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label" for="pharmacist_name">Apoteker penanggung jawab</label><input type="text" name="pharmacist_name" id="pharmacist_name" value="{{ $profileValue("pharmacist_name") }}" class="form-control" placeholder="apt. Nama Lengkap, S.Farm."></div>
                                <div class="col-md-6"><label class="form-label" for="pharmacist_license_number">Nomor SIPA</label><input type="text" name="pharmacist_license_number" id="pharmacist_license_number" value="{{ $profileValue("pharmacist_license_number") }}" class="form-control" placeholder="Nomor izin praktik apoteker"></div>
                                <div class="col-md-6"><label class="form-label" for="pharmacy_license_number">Nomor SIA / izin apotek</label><input type="text" name="pharmacy_license_number" id="pharmacy_license_number" value="{{ $profileValue("pharmacy_license_number") }}" class="form-control" placeholder="Nomor izin resmi apotek"></div>
                                <div class="col-md-3"><label class="form-label" for="license_expired_at">Berlaku sampai</label><input type="date" name="license_expired_at" id="license_expired_at" value="{{ old("license_expired_at", $profile?->license_expired_at?->format("Y-m-d")) }}" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label" for="tax_id">NPWP</label><input type="text" name="tax_id" id="tax_id" value="{{ $profileValue("tax_id") }}" class="form-control" placeholder="00.000.000.0-000.000"></div>
                            </div>
                        </section>

                        <section class="apotek-card">
                            <div class="apotek-card-heading">
                                <span class="apotek-section-icon mint"><i data-feather="clock"></i></span>
                                <div><h2>Jam operasional</h2><p>Atur hari dan jam pelayanan cabang.</p></div>
                            </div>
                            <div class="apotek-hours-list">
                                @foreach ($dayLabels as $day => $label)
                                    @php
                                        $schedule = $operationalHours[$day];
                                        $enabled = (bool) old("operational_hours.$day.enabled", $schedule["enabled"]);
                                    @endphp
                                    <div class="apotek-hours-row {{ $enabled ? "is-open" : "" }}">
                                        <label class="apotek-day-toggle">
                                            <input type="hidden" name="operational_hours[{{ $day }}][enabled]" value="0">
                                            <input type="checkbox" name="operational_hours[{{ $day }}][enabled]" value="1" @checked($enabled)>
                                            <span class="apotek-toggle-track"><span></span></span>
                                            <strong>{{ $label }}</strong>
                                        </label>
                                        <div class="apotek-hours-fields">
                                            <label><span>Buka</span><input type="time" name="operational_hours[{{ $day }}][open]" value="{{ old("operational_hours.$day.open", $schedule["open"]) }}"></label>
                                            <span class="apotek-hours-separator">—</span>
                                            <label><span>Tutup</span><input type="time" name="operational_hours[{{ $day }}][close]" value="{{ old("operational_hours.$day.close", $schedule["close"]) }}"></label>
                                        </div>
                                        <span class="apotek-day-status">{{ $enabled ? "Buka" : "Tutup" }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="apotek-card">
                            <div class="apotek-card-heading">
                                <span class="apotek-section-icon violet"><i data-feather="file-text"></i></span>
                                <div><h2>Catatan struk</h2><p>Pesan singkat yang dapat digunakan pada bagian bawah struk.</p></div>
                            </div>
                            <textarea name="receipt_footer" id="receipt_footer" rows="3" maxlength="500" class="form-control"
                                placeholder="Contoh: Terima kasih. Semoga lekas sehat.">{{ $profileValue("receipt_footer") }}</textarea>
                            <div class="apotek-character-count"><span id="receiptFooterCount">0</span>/500 karakter</div>
                        </section>

                        <div class="apotek-mobile-save">
                            <span><strong>Simpan profile cabang</strong><small>Pastikan lokasi pada peta sudah tepat.</small></span>
                            <button type="submit" class="btn apotek-save-button"><i data-feather="save"></i> Simpan perubahan</button>
                        </div>
                    </main>
                </div>
            </form>
        @endunless
    </div>
@endsection

@push("scripts")
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @include("medcare.settings.apotekProfile.scripts")
@endpush
