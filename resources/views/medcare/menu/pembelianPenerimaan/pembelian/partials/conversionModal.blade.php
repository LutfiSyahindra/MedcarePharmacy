<dialog id="purchaseConversionModal" class="purchase-conversion-modal" aria-labelledby="purchaseConversionModalTitle" aria-describedby="purchaseConversionDescription">
    <div class="purchase-conversion-header">
        <div class="purchase-conversion-heading">
            <span class="purchase-conversion-icon"><i class="mdi mdi-swap-horizontal" aria-hidden="true"></i></span>
            <div>
                <span class="purchase-conversion-eyebrow">Pengaturan obat</span>
                <h5 id="purchaseConversionModalTitle">Satuan Konversi</h5>
                <p id="purchaseConversionDescription">Atur satuan pembelian dan isi di dalamnya.</p>
            </div>
        </div>
        <button type="button" class="btn-close purchase-close-conversion" aria-label="Tutup pengaturan konversi"></button>
    </div>
    <form id="purchaseConversionForm">
        <div class="purchase-conversion-body">
            <div class="purchase-conversion-medicine-card">
                <div class="purchase-conversion-medicine-copy">
                    <span id="purchaseConversionMedicineCode" class="purchase-conversion-code"></span>
                    <strong id="purchaseConversionMedicine"></strong>
                </div>
                <div class="purchase-conversion-stock">
                    <i class="mdi mdi-package-variant-closed" aria-hidden="true"></i>
                    <div><span>Satuan stok</span><strong id="purchaseConversionStockUnit"></strong></div>
                </div>
            </div>
            <div class="purchase-conversion-section-heading">
                <div><h6>Satuan beli &amp; isi</h6><p>Edit konversi yang tersedia atau tambahkan satuan baru. Pilih satu untuk dipakai di PO.</p></div>
                <span id="purchaseConversionCount" class="purchase-conversion-count">0 satuan</span>
            </div>
            <div id="purchaseConversionLoading" class="purchase-conversion-loading" role="status"><i class="mdi mdi-loading mdi-spin" aria-hidden="true"></i> Memuat satuan konversi...</div>
            <div id="purchaseConversionRows"></div>
            <button type="button" id="purchaseAddConversion" class="btn purchase-conversion-add" disabled>
                <i class="mdi mdi-plus-circle-outline" aria-hidden="true"></i> Tambah Satuan Konversi
            </button>
            <div id="purchaseConversionError" class="alert alert-danger mt-3 mb-0 d-none" role="alert"></div>
        </div>
        <div class="purchase-conversion-footer">
            <p><i class="mdi mdi-shield-check-outline" aria-hidden="true"></i> Tersimpan untuk transaksi berikutnya.</p>
            <div class="purchase-conversion-save-actions">
                <button type="button" class="btn btn-light purchase-close-conversion">Batal</button>
                <button type="submit" id="purchaseSaveConversions" class="btn btn-primary" disabled><i class="mdi mdi-check" aria-hidden="true"></i> <span>Simpan Konversi</span></button>
            </div>
        </div>
    </form>
</dialog>

<template id="purchaseConversionRowTemplate">
    <div class="purchase-conversion-row">
        <div class="purchase-conversion-row-heading">
            <span class="purchase-conversion-row-number"></span>
            <div class="purchase-conversion-row-meta">
                <span class="purchase-conversion-row-status"></span>
                <button type="button" class="btn btn-sm btn-outline-danger purchase-remove-conversion" aria-label="Hapus baris satuan konversi"><i class="mdi mdi-trash-can-outline" aria-hidden="true"></i> Hapus</button>
            </div>
        </div>
        <div class="purchase-conversion-unit-field">
            <label class="form-label">Satuan beli</label>
            <select class="form-select purchase-conversion-unit" data-placeholder="Cari satuan beli..." required><option value=""></option></select>
        </div>
        <div class="purchase-conversion-factor-field">
            <label class="form-label">Isi per satuan beli</label>
            <div class="purchase-conversion-factor-input">
                <input type="number" class="form-control purchase-conversion-factor" min="1" max="2147483647" step="1" value="1" inputmode="numeric" required>
                <span class="purchase-conversion-stock-label"></span>
            </div>
        </div>
        <div class="purchase-conversion-row-actions">
            <label class="purchase-conversion-choice"><input type="radio" class="form-check-input purchase-conversion-use" name="purchase_conversion_selected" required> Pakai di PO</label>
        </div>
        <div class="purchase-conversion-equation"><i class="mdi mdi-swap-horizontal" aria-hidden="true"></i><span></span></div>
        <small class="purchase-conversion-lock-note d-none"><i class="mdi mdi-lock-outline" aria-hidden="true"></i> Satuan dan isi dikunci karena sudah digunakan dalam PO.</small>
    </div>
</template>
