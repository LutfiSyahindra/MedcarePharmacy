@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.menu.notifikasi.partials.style")
@endpush

@section("content")
    @include("medcare.menu.notifikasi.readModal")

    <div class="notification-page">
        <nav class="page-breadcrumb notification-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route("dashboard") }}"><i class="mdi mdi-home-outline"></i> Dashboard</a>
                </li>
                <li class="breadcrumb-item">Operasional</li>
                <li class="breadcrumb-item active" aria-current="page">Notifikasi</li>
            </ol>
        </nav>

        <section class="notification-command" aria-labelledby="notificationPageTitle">
            <div class="notification-command-copy">
                <div class="notification-kicker">
                    <span class="notification-live-dot" aria-hidden="true"></span>
                    Pusat notifikasi operasional
                </div>
                <h1 id="notificationPageTitle">Semua Notifikasi</h1>
                <p>Pantau permintaan transaksi, tindak lanjuti approval, dan lihat hasil proses dalam satu tempat.</p>
                <div class="notification-command-meta" aria-label="Informasi notifikasi">
                    <span><i class="mdi mdi-bell-ring-outline"></i> Pembaruan realtime</span>
                    <span><i class="mdi mdi-shield-check-outline"></i> Sesuai akses role</span>
                </div>
            </div>
            <div class="notification-command-actions">
                <button type="button" class="notification-icon-button" id="refreshNotificationTable"
                    aria-label="Muat ulang notifikasi" title="Muat ulang">
                    <i class="mdi mdi-refresh"></i>
                </button>
                <button type="button" class="notification-mark-read" id="notificationMarkAllRead"
                    onclick="markAllNotifRead()">
                    <i class="mdi mdi-check-all"></i>
                    <span>Tandai semua dibaca</span>
                </button>
            </div>
        </section>

        <section class="notification-metrics" aria-label="Ringkasan notifikasi">
            <article class="notification-metric is-total">
                <span class="metric-icon is-total"><i class="mdi mdi-bell-outline"></i></span>
                <div class="notification-metric-copy">
                    <span class="notification-metric-label">Total notifikasi</span>
                    <strong>{{ number_format($summary["total"] ?? 0, 0, ",", ".") }}</strong>
                    <small>Seluruh aktivitas</small>
                </div>
                <i class="mdi mdi-chart-line metric-decoration" aria-hidden="true"></i>
            </article>
            <article class="notification-metric is-unread">
                <span class="metric-icon is-unread"><i class="mdi mdi-email-alert-outline"></i></span>
                <div class="notification-metric-copy">
                    <span class="notification-metric-label">Belum dibaca</span>
                    <strong id="notificationUnreadSummary">{{ number_format($summary["unread"] ?? 0, 0, ",", ".") }}</strong>
                    <small>Perlu perhatian</small>
                </div>
                <i class="mdi mdi-email-outline metric-decoration" aria-hidden="true"></i>
            </article>
            <article class="notification-metric is-action">
                <span class="metric-icon is-action"><i class="mdi mdi-cursor-default-click-outline"></i></span>
                <div class="notification-metric-copy">
                    <span class="notification-metric-label">Perlu aksi</span>
                    <strong>{{ number_format($summary["pending_action"] ?? 0, 0, ",", ".") }}</strong>
                    <small>Menunggu keputusan</small>
                </div>
                <i class="mdi mdi-cursor-default-click-outline metric-decoration" aria-hidden="true"></i>
            </article>
            <article class="notification-metric is-result">
                <span class="metric-icon is-result"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div class="notification-metric-copy">
                    <span class="notification-metric-label">Hasil aksi</span>
                    <strong>{{ number_format($summary["results"] ?? 0, 0, ",", ".") }}</strong>
                    <small>Proses terselesaikan</small>
                </div>
                <i class="mdi mdi-check-decagram-outline metric-decoration" aria-hidden="true"></i>
            </article>
        </section>

        <section class="notification-inbox" aria-labelledby="notificationInboxTitle">
            <div class="notification-inbox-heading">
                <div>
                    <span class="notification-section-icon"><i class="mdi mdi-inbox-arrow-down-outline"></i></span>
                    <div>
                        <h2 id="notificationInboxTitle">Kotak masuk</h2>
                        <p id="notificationTableMeta" aria-live="polite">Memuat daftar notifikasi...</p>
                    </div>
                </div>
                <span class="notification-result-count" id="notificationResultCount">
                    <i class="mdi mdi-format-list-bulleted"></i>
                    <strong>0</strong> data
                </span>
            </div>

            <div class="notification-toolbar">
                <div class="notification-filters" role="group" aria-label="Filter notifikasi">
                    <button type="button" class="notification-filter is-active" data-filter="" aria-pressed="true">
                        <i class="mdi mdi-view-grid-outline"></i> Semua
                    </button>
                    <button type="button" class="notification-filter" data-filter="Perlu aksi" aria-pressed="false">
                        <i class="mdi mdi-cursor-default-click-outline"></i> Perlu Aksi
                    </button>
                    <button type="button" class="notification-filter" data-filter="Hasil Aksi" aria-pressed="false">
                        <i class="mdi mdi-check-decagram-outline"></i> Hasil Aksi
                    </button>
                    <button type="button" class="notification-filter" data-filter="Belum dibaca" aria-pressed="false">
                        <i class="mdi mdi-email-alert-outline"></i> Belum Dibaca
                    </button>
                </div>
                <label class="notification-search" for="searchNotifikasi">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="searchNotifikasi" placeholder="Cari nomor, modul, supplier, atau pesan">
                    <button type="button" class="notification-search-clear" id="clearNotificationSearch"
                        aria-label="Hapus pencarian" title="Hapus pencarian">
                        <i class="mdi mdi-close-circle"></i>
                    </button>
                </label>
            </div>

            <div class="notification-table-shell">
                <table id="tableNotifikasi" class="table notification-table align-middle">
                    <thead>
                        <tr>
                            <th width="48">No</th>
                            <th>Dokumen</th>
                            <th>Ringkasan</th>
                            <th width="180">Status</th>
                            <th width="130">Waktu</th>
                            <th width="112">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.notifikasi.jsMain")
@endpush
