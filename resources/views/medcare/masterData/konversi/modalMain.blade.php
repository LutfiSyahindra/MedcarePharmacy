<div class="modal fade obat-modal" id="konversiModal" tabindex="-1" aria-labelledby="konversiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-scale-balance"></i></span>
                    <div>
                        <h5 class="modal-title mb-0" id="konversiModalLabel">Kelola Konversi Satuan Obat</h5>
                        <p class="modal-subtitle" id="konversiModalSubtitle">Isi satuan pembelian untuk obat yang dipilih dari tabel.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="konversiForm">
                    @csrf
                    <input id="activeObatId" name="active_obat_id" type="hidden">

                    <div class="konversi-obat-context">
                        <div>
                            <strong id="konversiObatName">Pilih obat dari tabel</strong>
                            <span id="konversiObatMeta">Kode obat dan satuan stok akan muncul di sini.</span>
                        </div>
                        <small id="konversiObatStatus" class="konversi-empty-badge">
                            <i class="mdi mdi-alert-circle-outline"></i>
                            Belum dipilih
                        </small>
                    </div>

                    <div class="konversi-modal-note mb-3">
                        <i class="mdi mdi-information-outline"></i>
                        <div>
                            <strong class="d-block text-dark">Masukkan satuan pembelian yang dipakai di transaksi.</strong>
                            <span id="konversiModalNote">Contoh: 1 Box = 100 satuan stok, 1 Strip = 10 satuan stok.</span>
                        </div>
                    </div>

                    <div id="input-wrapper"></div>

                    <div class="konversi-live-preview" id="konversiLivePreview">
                        Belum ada baris konversi yang siap disimpan.
                    </div>

                    <div class="mt-3">
                        <button type="button" id="addInput" class="btn btn-outline-primary">
                            <i class="mdi mdi-plus-circle-outline me-1"></i>
                            Tambah Satuan
                        </button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>

                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Konversi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
