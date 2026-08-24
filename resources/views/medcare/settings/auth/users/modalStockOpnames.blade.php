<div class="modal fade auth-modal" id="userStockOpnamesModal" tabindex="-1"
    aria-labelledby="userStockOpnamesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-clipboard-text-clock-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="userStockOpnamesModalLabel">Transaksi Stock Opname</h5>
                        <p class="modal-subtitle" id="userStockOpnamesSubtitle">Riwayat keterlibatan user dalam Stock Opname.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="user-opname-summary" id="userStockOpnamesSummary" hidden>
                    <article>
                        <span><i class="mdi mdi-clipboard-list-outline"></i></span>
                        <div><strong data-summary="total">0</strong><small>Total transaksi</small></div>
                    </article>
                    <article>
                        <span><i class="mdi mdi-progress-clock"></i></span>
                        <div><strong data-summary="counting">0</strong><small>Sedang dihitung</small></div>
                    </article>
                    <article>
                        <span><i class="mdi mdi-timer-sand"></i></span>
                        <div><strong data-summary="waiting">0</strong><small>Menunggu proses</small></div>
                    </article>
                    <article>
                        <span><i class="mdi mdi-check-decagram-outline"></i></span>
                        <div><strong data-summary="completed">0</strong><small>Disetujui / selesai</small></div>
                    </article>
                </div>

                <div class="user-opname-state" id="userStockOpnamesLoading">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <strong>Memuat transaksi opname...</strong>
                </div>

                <div class="user-opname-state is-empty" id="userStockOpnamesEmpty" hidden>
                    <i class="mdi mdi-clipboard-text-off-outline"></i>
                    <strong>Belum ada transaksi Stock Opname</strong>
                    <span>User ini belum tercatat sebagai pelaksana transaksi opname.</span>
                </div>

                <div class="table-responsive user-opname-table-wrap" id="userStockOpnamesTableWrap" hidden>
                    <table class="table auth-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal / Nomor</th>
                                <th>Branch / Lokasi</th>
                                <th>Status</th>
                                <th>Peran User</th>
                                <th>Aktivitas Terakhir</th>
                            </tr>
                        </thead>
                        <tbody id="userStockOpnamesRows"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('stockOpname.index') }}" class="btn btn-outline-primary">
                    <i class="mdi mdi-open-in-new"></i>
                    Buka Modul Stock Opname
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close"></i>
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
