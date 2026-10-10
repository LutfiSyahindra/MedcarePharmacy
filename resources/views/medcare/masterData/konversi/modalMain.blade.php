<div class="modal fade obat-modal konversi-modal" id="konversiModal" tabindex="-1" aria-labelledby="konversiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-swap-horizontal-bold" aria-hidden="true"></i></span>
                    <div>
                        <span class="konversi-modal-kicker">Konversi per obat</span>
                        <h2 class="modal-title mb-0" id="konversiModalLabel">Kelola Konversi Satuan</h2>
                        <p class="modal-subtitle" id="konversiModalSubtitle">Atur satuan pembelian untuk obat terpilih.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="konversiForm">
                    @csrf
                    <input id="activeObatId" name="active_obat_id" type="hidden">
                    <div class="konversi-obat-context">
                        <div>
                            <span class="konversi-context-label">Obat terpilih</span>
                            <strong id="konversiObatName">Pilih obat dari tabel</strong>
                            <span id="konversiObatMeta">Kode obat dan satuan stok akan muncul di sini.</span>
                        </div>
                        <small id="konversiObatStatus" class="konversi-empty-badge"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>Belum dipilih</small>
                    </div>
                    <div class="konversi-section-heading">
                        <div><h3>Satuan pembelian</h3><p id="konversiModalNote">Contoh: 1 Box = 100 satuan stok, 1 Strip = 10 satuan stok.</p></div>
                    </div>
                    <div id="input-wrapper"></div>
                    <button type="button" id="addInput" class="btn btn-outline-primary konversi-add-row"><i class="mdi mdi-plus" aria-hidden="true"></i>Tambah Satuan Pembelian</button>
                    <div class="konversi-preview-panel">
                        <span class="konversi-preview-label"><i class="mdi mdi-eye-outline" aria-hidden="true"></i>Pratinjau konversi</span>
                        <div class="konversi-live-preview" id="konversiLivePreview" aria-live="polite">Belum ada baris konversi yang siap disimpan.</div>
                    </div>
                    <details class="konversi-policy">
                        <summary>Bagaimana konversi Utama digunakan pada PO?</summary>
                        <p>Satuan kosong pada PO tanpa penerimaan ikut memakai konversi Utama, atau satu-satunya konversi yang tersedia. Jumlah dan nilai PO tetap. PO yang sudah mempunyai penerimaan dilindungi.</p>
                    </details>
                </form>
            </div>
            <div class="modal-footer">
                <span class="konversi-footer-hint"><i class="mdi mdi-information-outline" aria-hidden="true"></i>Pilih satu konversi Utama untuk PO.</span>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="konversiForm" id="submitForm" class="btn btn-primary"><i class="mdi mdi-check" aria-hidden="true"></i>Simpan Konversi</button>
            </div>
        </div>
    </div>
</div>
