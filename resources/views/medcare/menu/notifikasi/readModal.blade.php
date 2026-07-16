<div class="modal fade notification-modal" id="modalReadNotif" tabindex="-1" aria-labelledby="notif-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content notification-modal-content">
            <div class="notification-modal-header">
                <div class="notification-modal-title">
                    <span class="notification-modal-icon" id="notif-modal-icon">
                        <i class="mdi mdi-bell-outline"></i>
                    </span>
                    <div>
                        <h5 id="notif-modal-title">Notifikasi</h5>
                        <small id="notif-modal-subtitle">Detail notifikasi transaksi</small>
                    </div>
                </div>
                <div class="notification-modal-header-actions">
                    <span class="notification-modal-status is-muted" id="notif-modal-status">Menunggu data</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
            </div>

            <div class="modal-body notification-modal-body" id="notif-modal-body">
                <div class="notification-modal-empty">
                    <span><i class="mdi mdi-bell-ring-outline"></i></span>
                    <strong>Memuat detail notifikasi</strong>
                    <small>Mohon tunggu sebentar.</small>
                </div>
            </div>

            <div class="notification-modal-footer" id="notif-modal-footer">
                <span class="notification-modal-action is-static">
                    <i class="mdi mdi-information-outline"></i>
                    <span>Pilih notifikasi untuk melihat aksi.</span>
                </span>
            </div>
        </div>
    </div>
</div>
