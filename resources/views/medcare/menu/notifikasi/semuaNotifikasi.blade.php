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
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Notifikasi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Semua Notifikasi</li>
            </ol>
        </nav>

        <section class="notification-command">
            <div>
                <span class="notification-kicker">Notification command center</span>
                <h4>Semua Notifikasi</h4>
                <p>Pantau request pembelian, penerimaan, retur pembelian, dan hasil aksi role approval.</p>
            </div>
            <div class="notification-command-actions">
                <button type="button" class="notification-icon-button" id="refreshNotificationTable" title="Refresh">
                    <i class="mdi mdi-refresh"></i>
                </button>
                <button type="button" class="notification-mark-read" onclick="markAllNotifRead()">
                    <i class="mdi mdi-check-all"></i>
                    <span>Tandai Dibaca</span>
                </button>
            </div>
        </section>

        <div class="notification-metrics">
            <div class="notification-metric">
                <span class="metric-icon is-total"><i class="mdi mdi-bell-outline"></i></span>
                <div>
                    <strong>{{ number_format($summary["total"] ?? 0, 0, ",", ".") }}</strong>
                    <span>Total</span>
                </div>
            </div>
            <div class="notification-metric">
                <span class="metric-icon is-unread"><i class="mdi mdi-email-alert-outline"></i></span>
                <div>
                    <strong id="notificationUnreadSummary">{{ number_format($summary["unread"] ?? 0, 0, ",", ".") }}</strong>
                    <span>Belum Dibaca</span>
                </div>
            </div>
            <div class="notification-metric">
                <span class="metric-icon is-action"><i class="mdi mdi-cursor-default-click-outline"></i></span>
                <div>
                    <strong>{{ number_format($summary["pending_action"] ?? 0, 0, ",", ".") }}</strong>
                    <span>Perlu Aksi</span>
                </div>
            </div>
            <div class="notification-metric">
                <span class="metric-icon is-result"><i class="mdi mdi-check-decagram-outline"></i></span>
                <div>
                    <strong>{{ number_format($summary["results"] ?? 0, 0, ",", ".") }}</strong>
                    <span>Hasil Aksi</span>
                </div>
            </div>
        </div>

        <section class="notification-inbox">
            <div class="notification-toolbar">
                <div class="notification-filters" role="tablist" aria-label="Filter notifikasi">
                    <button type="button" class="notification-filter is-active" data-filter="">Semua</button>
                    <button type="button" class="notification-filter" data-filter="Perlu aksi">Perlu Aksi</button>
                    <button type="button" class="notification-filter" data-filter="Hasil Aksi">Hasil Aksi</button>
                    <button type="button" class="notification-filter" data-filter="Belum dibaca">Belum Dibaca</button>
                </div>
                <label class="notification-search" for="searchNotifikasi">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="searchNotifikasi" placeholder="Cari nomor, modul, supplier, atau pesan">
                </label>
            </div>

            <div class="table-responsive">
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
