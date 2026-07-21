@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.settings.notifikasi.style")
@endpush

@section("content")
    @php
        $moduleSettings = $settings["modules"] ?? [];
        $pending = $stats["pending"] ?? [];
    @endphp

    <div class="notif-settings-page">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Konfigurasi Notifikasi</li>
            </ol>
        </nav>

        <section class="notif-settings-header">
            <div>
                <span class="notif-settings-kicker">Transaction notification center</span>
                <h4>Konfigurasi Notifikasi</h4>
                <p>Atur notifikasi pembelian, penerimaan, retur pembelian, target role, dan channel realtime.</p>
            </div>
            <button type="submit" form="notificationSettingsForm" class="notif-save-button" id="notificationSettingsSave">
                <i class="mdi mdi-content-save-outline"></i>
                <span>Simpan Konfigurasi</span>
            </button>
        </section>

        <div class="notif-settings-grid">
            @foreach ($modules as $key => $label)
                <div class="notif-stat-tile">
                    <span class="notif-stat-icon is-{{ $key }}"><i class="mdi mdi-bell-check-outline"></i></span>
                    <div>
                        <strong>{{ number_format($pending[$key] ?? 0, 0, ",", ".") }}</strong>
                        <span>{{ $label }}</span>
                        <small>Menunggu aksi</small>
                    </div>
                </div>
            @endforeach
        </div>

        <form id="notificationSettingsForm" class="notif-settings-form">
            @csrf
            @method("PUT")

            <section class="notif-config-panel">
                <div class="notif-panel-title">
                    <span><i class="mdi mdi-tune-variant"></i></span>
                    <div>
                        <h5>Modul Transaksi</h5>
                        <p>Aktifkan alur notifikasi untuk dokumen yang perlu diawasi.</p>
                    </div>
                </div>

                <div class="notif-module-grid">
                    @foreach ($modules as $key => $label)
                        <label class="notif-module-option">
                            <input type="checkbox" class="notif-toggle-source" name="modules[{{ $key }}][enabled]" value="1"
                                @checked($moduleSettings[$key]["enabled"] ?? false)>
                            <span class="notif-module-switch"></span>
                            <span class="notif-module-copy">
                                <strong>{{ $label }}</strong>
                                <small>{{ $key === "pembelian" ? "Approve atau tolak PO." : ($key === "penerimaan" ? "Posting atau batalkan penerimaan." : "Posting atau batalkan retur.") }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="notif-config-panel">
                <div class="notif-panel-title">
                    <span><i class="mdi mdi-account-key-outline"></i></span>
                    <div>
                        <h5>Role dan Scope Branch</h5>
                        <p>Target approver dan penerima notifikasi kini mengikuti konfigurasi setiap role.</p>
                    </div>
                </div>

                <div class="notif-role-setting-callout">
                    <span><i class="mdi mdi-shield-key-outline"></i></span>
                    <div>
                        <strong>Kelola melalui Role Setting</strong>
                        <small>Tentukan role approval, scope semua branch atau branch user, serta penerima notifikasi balasan.</small>
                    </div>
                    <a href="{{ route("settings.role-setting.index") }}">Buka Role Setting <i class="mdi mdi-arrow-right"></i></a>
                </div>
            </section>

            <section class="notif-config-panel">
                <div class="notif-panel-title">
                    <span><i class="mdi mdi-access-point-network"></i></span>
                    <div>
                        <h5>Channel dan Perilaku</h5>
                        <p>Kontrol balasan ke pembuat, realtime, suara, dan jumlah item dropdown.</p>
                    </div>
                </div>

                <div class="notif-behavior-grid">
                    <label class="notif-behavior-row">
                        <span>
                            <strong>Balasan ke pembuat</strong>
                            <small>Kirim hasil aksi ke user pembuat dokumen.</small>
                        </span>
                        <input type="checkbox" class="notif-toggle-source" name="notify_creator" value="1"
                            @checked($settings["notify_creator"] ?? true)>
                        <i></i>
                    </label>

                    <label class="notif-behavior-row">
                        <span>
                            <strong>Realtime broadcast</strong>
                            <small>Push notifikasi baru ke navbar dan toast.</small>
                        </span>
                        <input type="checkbox" class="notif-toggle-source" name="broadcast" value="1"
                            @checked($settings["broadcast"] ?? true)>
                        <i></i>
                    </label>

                    <label class="notif-behavior-row">
                        <span>
                            <strong>Suara realtime</strong>
                            <small>Aktifkan bunyi notifikasi saat broadcast masuk.</small>
                        </span>
                        <input type="checkbox" class="notif-toggle-source" name="sound" value="1"
                            @checked($settings["sound"] ?? true)>
                        <i></i>
                    </label>

                </div>

                <div class="notif-limit-control">
                    <label for="navbarLimit">
                        <span>Limit dropdown navbar</span>
                        <strong id="navbarLimitValue">{{ (int) ($settings["navbar_limit"] ?? 8) }}</strong>
                    </label>
                    <input type="range" min="3" max="20" step="1" id="navbarLimit" name="navbar_limit"
                        value="{{ (int) ($settings["navbar_limit"] ?? 8) }}">
                </div>
            </section>
        </form>
    </div>
@endsection

@push("scripts")
    @include("medcare.settings.notifikasi.jsMain")
@endpush
