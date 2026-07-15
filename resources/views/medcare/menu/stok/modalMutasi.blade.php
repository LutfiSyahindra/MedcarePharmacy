<div class="modal fade stock-mutation-modal" id="mutasiStokModal" tabindex="-1"
    aria-labelledby="mutasiStokModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="mutasiStokForm" class="stock-mutation-form">
                <div class="modal-header stock-mutation-header">
                    <div class="stock-mutation-title">
                        <span class="stock-mutation-title-icon"><i class="mdi mdi-swap-horizontal-bold"></i></span>
                        <div>
                            <h5 class="modal-title" id="mutasiStokModalLabel">Catat Mutasi Stok</h5>
                            <p id="mutasiModeSubtitle">Obat masuk akan menambah saldo batch dalam satuan stok dasar.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body stock-mutation-body">
                    <input type="hidden" name="jenis_mutasi" id="jenis_mutasi" value="masuk">

                    <aside class="stock-mutation-side">
                        <div class="stock-mutation-mode" role="group" aria-label="Jenis mutasi stok">
                            <button type="button" class="stock-mutation-mode-btn is-active" data-mutasi="masuk">
                                <i class="mdi mdi-arrow-down-bold-circle-outline"></i>
                                <span>Obat Masuk</span>
                            </button>
                            <button type="button" class="stock-mutation-mode-btn" data-mutasi="keluar">
                                <i class="mdi mdi-arrow-up-bold-circle-outline"></i>
                                <span>Obat Keluar</span>
                            </button>
                            <button type="button" class="stock-mutation-mode-btn" data-mutasi="expired">
                                <i class="mdi mdi-calendar-remove-outline"></i>
                                <span>Expired</span>
                            </button>
                            <button type="button" class="stock-mutation-mode-btn" data-mutasi="penyesuaian_masuk">
                                <i class="mdi mdi-plus-circle-outline"></i>
                                <span>Adj. Masuk</span>
                            </button>
                            <button type="button" class="stock-mutation-mode-btn" data-mutasi="penyesuaian_keluar">
                                <i class="mdi mdi-minus-circle-outline"></i>
                                <span>Adj. Keluar</span>
                            </button>
                        </div>

                        <div class="stock-mutation-impact" id="mutasiImpactCard">
                            <span class="stock-mutation-impact-icon"><i class="mdi mdi-database-plus-outline"></i></span>
                            <div>
                                <small>Dampak stok</small>
                                <strong id="mutasiImpactTitle">Menambah stok</strong>
                                <p id="mutasiImpactText">Batch baru atau batch yang sama akan bertambah sesuai qty.</p>
                            </div>
                        </div>

                        <div class="stock-mutation-mini-grid">
                            <div class="stock-mutation-mini">
                                <small>Qty</small>
                                <strong id="mutasiPreviewQty">0</strong>
                            </div>
                            <div class="stock-mutation-mini">
                                <small>Nilai</small>
                                <strong id="mutasiPreviewValue">Rp 0</strong>
                            </div>
                        </div>
                    </aside>

                    <section class="stock-mutation-main">
                        <div class="stock-mutation-section">
                            <div class="stock-mutation-section-title">
                                <i class="mdi mdi-clipboard-text-outline"></i>
                                <div>
                                    <strong>Informasi Mutasi</strong>
                                    <span>Identitas transaksi dan obat.</span>
                                </div>
                            </div>

                            <div class="stock-field-grid">
                                <div class="stock-field">
                                    <label for="tanggal_mutasi"><i class="mdi mdi-calendar-clock"></i> Tanggal Mutasi</label>
                                    <input type="text" class="form-control" name="tanggal_mutasi" id="tanggal_mutasi"
                                        required>
                                </div>
                                <div class="stock-field">
                                    <label for="mutasi_nomor_referensi"><i class="mdi mdi-file-document-outline"></i> Nomor Referensi</label>
                                    <input type="text" class="form-control" name="nomor_referensi"
                                        id="mutasi_nomor_referensi" maxlength="100" placeholder="Opsional">
                                </div>
                                <div class="stock-field is-full">
                                    <label for="mutasi_obat_id"><i class="mdi mdi-pill"></i> Obat</label>
                                    <select class="form-select" name="obat_id" id="mutasi_obat_id" required></select>
                                    <div class="stock-help" id="mutasiObatHelp">Pilih obat untuk menampilkan batch aktif.</div>
                                </div>
                            </div>
                        </div>

                        <div class="stock-mutation-section">
                            <div class="stock-mutation-section-title">
                                <i class="mdi mdi-package-variant-closed"></i>
                                <div>
                                    <strong>Batch dan Jumlah</strong>
                                    <span id="mutasiBatchModeText">Isi batch baru atau batch existing untuk stok masuk.</span>
                                </div>
                            </div>

                            <div class="stock-field-grid">
                                <div class="stock-field stock-batch-select-field is-full">
                                    <label for="mutasi_stok_batch_id"><i class="mdi mdi-package-variant-closed-check"></i> Batch Stok</label>
                                    <select class="form-select" name="stok_batch_id" id="mutasi_stok_batch_id"></select>
                                    <div class="stock-help" id="mutasiBatchHelp">Pilih obat terlebih dahulu.</div>
                                </div>
                                <div class="stock-field stock-in-field">
                                    <label for="mutasi_no_batch"><i class="mdi mdi-barcode"></i> Nomor Batch</label>
                                    <input type="text" class="form-control" name="no_batch" id="mutasi_no_batch"
                                        maxlength="80" placeholder="Contoh: BATCH-2026-A">
                                </div>
                                <div class="stock-field stock-in-field">
                                    <label for="mutasi_expired_date"><i class="mdi mdi-calendar-alert"></i> Expired Date</label>
                                    <input type="text" class="form-control" name="expired_date" id="mutasi_expired_date"
                                        placeholder="YYYY-MM-DD">
                                </div>
                                <div class="stock-field">
                                    <label for="mutasi_qty"><i class="mdi mdi-counter"></i> Qty</label>
                                    <input type="number" class="form-control" name="qty" id="mutasi_qty" min="0.01"
                                        step="0.01" required placeholder="0">
                                    <div class="stock-help" id="mutasiQtyHelp">Qty memakai satuan stok dasar obat.</div>
                                </div>
                                <div class="stock-field stock-in-field">
                                    <label for="mutasi_harga_beli"><i class="mdi mdi-cash"></i> Harga Beli Satuan Stok</label>
                                    <input type="number" class="form-control" name="harga_beli" id="mutasi_harga_beli"
                                        min="0" step="0.01" placeholder="0">
                                </div>
                                <div class="stock-field stock-in-field">
                                    <label for="mutasi_harga_jual"><i class="mdi mdi-cash-plus"></i> Harga Jual Satuan Stok</label>
                                    <input type="number" class="form-control" name="harga_jual" id="mutasi_harga_jual"
                                        min="0" step="0.01" placeholder="0">
                                </div>
                                <div class="stock-field stock-in-field is-full">
                                    <label for="mutasi_alasan_harga"><i class="mdi mdi-text-box-edit-outline"></i> Alasan Perubahan Harga</label>
                                    <textarea class="form-control" name="alasan_harga" id="mutasi_alasan_harga" rows="2"
                                        maxlength="1000" placeholder="Contoh: update margin jual, penyesuaian harga supplier, atau promo selesai"></textarea>
                                    <div class="stock-help">Dicatat ke riwayat jika harga jual batch berubah.</div>
                                </div>
                            </div>
                        </div>

                        <div class="stock-mutation-section">
                            <div class="stock-mutation-section-title">
                                <i class="mdi mdi-note-text-outline"></i>
                                <div>
                                    <strong>Catatan</strong>
                                    <span>Tambahkan konteks singkat untuk audit stok.</span>
                                </div>
                            </div>
                            <div class="stock-field-grid">
                                <div class="stock-field is-full">
                                    <label for="mutasi_keterangan"><i class="mdi mdi-text-box-outline"></i> Keterangan</label>
                                    <textarea class="form-control" name="keterangan" id="mutasi_keterangan" rows="3"
                                        placeholder="Contoh: koreksi stok opname, obat rusak, atau penyesuaian gudang"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="modal-footer stock-mutation-footer">
                    <div class="stock-mutation-footer-summary">
                        <i class="mdi mdi-information-outline"></i>
                        <span id="mutasiFooterSummary">Siap mencatat obat masuk.</span>
                    </div>
                    <div class="stock-mutation-footer-actions">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitMutasiStok">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Mutasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
