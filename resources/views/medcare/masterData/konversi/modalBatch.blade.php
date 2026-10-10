<div class="modal fade obat-modal konversi-modal" id="konversiBatchModal" tabindex="-1" aria-labelledby="konversiBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-playlist-plus" aria-hidden="true"></i></span>
                    <div>
                        <span class="konversi-modal-kicker">Pengaturan sekaligus</span>
                        <h2 class="modal-title mb-0" id="konversiBatchModalLabel">Atur Konversi Batch</h2>
                        <p class="modal-subtitle">Satu aturan konversi untuk beberapa obat dengan isi kemasan yang sama.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="konversiBatchForm">
                    @csrf
                    <div class="alert alert-danger d-none" id="konversiBatchError" role="alert" tabindex="-1"></div>
                    <div class="konversi-batch-layout">
                        <section class="konversi-batch-step" aria-labelledby="batchTargetHeading">
                            <div class="konversi-step-heading"><span class="konversi-step-number">01</span><div><h3 id="batchTargetHeading">Pilih target obat</h3><p>Tentukan obat yang akan menerima konversi.</p></div></div>
                            <div class="mb-3">
                                <label class="form-label" for="batchStockUnits">Satuan stok</label>
                                <select id="batchStockUnits" class="form-select" multiple aria-describedby="batchStockHelp"></select>
                                <small class="konversi-field-help" id="batchStockHelp">Kosongkan jika memilih obat tanpa batasan satuan stok.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="batchMedicines">Obat</label>
                                <select id="batchMedicines" class="form-select" multiple aria-describedby="batchMedicineHelp"></select>
                                <small class="konversi-field-help" id="batchMedicineHelp">Kosongkan untuk semua obat sesuai satuan stok atau daftar PO.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="batchScope">Status target</label>
                                <select id="batchScope" class="form-select" aria-describedby="batchScopeHelp">
                                    <option value="all">Semua status konversi</option>
                                    <option value="without">Hanya obat belum punya konversi</option>
                                    <option value="po_without">Obat PO: konversi atau satuan belum lengkap</option>
                                </select>
                                <small class="konversi-field-help" id="batchScopeHelp">Daftar PO mengikuti akses cabang Anda.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary w-100" id="batchPreviewButton"><i class="mdi mdi-magnify" aria-hidden="true"></i>Lihat Target Obat</button>
                            <div class="konversi-preview-panel">
                                <span class="konversi-preview-label"><i class="mdi mdi-format-list-bulleted" aria-hidden="true"></i>Pratinjau target</span>
                                <div class="konversi-live-preview" aria-live="polite"><strong id="batchTargetSummary">Pilih target, lalu klik Lihat Target Obat.</strong></div>
                                <div class="konversi-batch-targets d-none" id="batchTargetsContainer" tabindex="0" role="region" aria-label="Daftar target obat, geser untuk melihat semua kolom">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead><tr><th scope="col">Obat</th><th scope="col">Satuan stok</th><th scope="col">Konversi saat ini</th><th scope="col">Nomor PO</th></tr></thead>
                                        <tbody id="batchTargetsBody"></tbody>
                                    </table>
                                    <small class="konversi-field-help konversi-target-scroll-hint px-2 pb-2">Geser tabel untuk melihat seluruh kolom.</small>
                                </div>
                            </div>
                        </section>
                        <section class="konversi-batch-step" aria-labelledby="batchConversionHeading">
                            <div class="konversi-step-heading"><span class="konversi-step-number">02</span><div><h3 id="batchConversionHeading">Atur satuan pembelian</h3><p>Pastikan isi kemasan sama untuk semua target.</p></div></div>
                            <div id="batchConversionRows"></div>
                            <button type="button" class="btn btn-outline-primary konversi-add-row" id="batchAddConversion"><i class="mdi mdi-plus" aria-hidden="true"></i>Tambah Satuan Pembelian</button>
                            <div class="konversi-preview-panel">
                                <span class="konversi-preview-label"><i class="mdi mdi-eye-outline" aria-hidden="true"></i>Pratinjau konversi</span>
                                <div class="konversi-live-preview" id="batchConversionPreview" aria-live="polite">Isi satuan pembelian dan jumlah konversinya.</div>
                            </div>
                        </section>
                    </div>
                    <details class="konversi-policy">
                        <summary>Ketentuan penerapan batch dan pembaruan PO</summary>
                        <p>Jika satuan stok dan obat diisi, hanya obat pilihan dengan satuan stok yang cocok yang menjadi target. Nilai konversi yang sudah ada akan dilewati; pilihan Utama tetap diterapkan. Satuan kosong pada PO tanpa penerimaan diisi dari konversi Utama, atau satu-satunya konversi yang tersedia. Jumlah, harga, diskon, pajak, dan total PO tetap. PO yang sudah mempunyai penerimaan dilindungi.</p>
                    </details>
                </form>
            </div>
            <div class="modal-footer">
                <span class="konversi-footer-hint"><i class="mdi mdi-information-outline" aria-hidden="true"></i>Periksa target sebelum menyimpan.</span>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="konversiBatchForm" class="btn btn-primary" id="batchSaveButton" disabled><i class="mdi mdi-check" aria-hidden="true"></i>Simpan Batch</button>
            </div>
        </div>
    </div>
</div>
