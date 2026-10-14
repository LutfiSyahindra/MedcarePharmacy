<div id="purchasePastePanel" class="purchase-paste-panel d-none">
    <label class="form-label" for="purchasePasteInput">Tempel kolom Excel: Kode obat → Qty → Harga per satuan dasar (opsional)</label>
    <textarea id="purchasePasteInput" class="form-control" rows="4" placeholder="OBT001&#9;10&#9;1500" aria-describedby="purchasePasteHint"></textarea>
    <small id="purchasePasteHint">Tanpa baris judul. Pisahkan kolom dengan Tab. Qty mengikuti satuan beli default. Harga memakai angka tanpa pemisah ribuan; koma boleh untuk desimal.</small>
    <div class="purchase-paste-actions">
        <button type="button" id="previewPurchasePaste" class="btn btn-outline-primary btn-sm">Periksa Data</button>
        <button type="button" id="applyPurchasePaste" class="btn btn-primary btn-sm" disabled>Masukkan ke Rincian</button>
        <button type="button" id="closePurchasePaste" class="btn btn-light btn-sm">Tutup</button>
    </div>
    <div id="purchasePastePreview" aria-live="polite"></div>
</div>
<div class="purchase-items-toolbar">
    <input type="search" id="purchaseItemSearch" class="form-control" placeholder="Cari item dalam PO..." aria-label="Cari item dalam PO">
    <label class="purchase-filter-check"><input type="checkbox" id="purchaseIssuesOnly" class="form-check-input"> Hanya yang bermasalah</label>
    <button type="button" id="purchaseNextIssue" class="btn btn-outline-warning btn-sm">Ke masalah berikutnya</button>
</div>
<div id="purchaseBulkToolbar" class="purchase-bulk-toolbar d-none">
    <strong><span id="purchaseBulkCount">0</span> item dipilih</strong>
    <label for="purchaseBulkField" class="visually-hidden">Kolom yang diubah massal</label>
    <select id="purchaseBulkField" class="form-select"><option value="diskon_1">Diskon 1 (%)</option><option value="diskon_2">Diskon 2 (%)</option><option value="diskon_3">Diskon 3 (%)</option><option value="ppn">PPN (%)</option></select>
    <label for="purchaseBulkValue" class="visually-hidden">Nilai persentase</label>
    <input type="number" id="purchaseBulkValue" class="form-control" min="0" max="100" step="0.01" value="0" inputmode="decimal">
    <button type="button" id="applyPurchaseBulk" class="btn btn-primary btn-sm">Terapkan ke 0 item</button>
    <button type="button" id="clearPurchaseSelection" class="btn btn-light btn-sm">Batal pilih</button>
</div>
<div id="purchaseValidationSummary" class="alert alert-danger d-none" role="alert"></div>
