<details id="purchaseEntryTools" class="purchase-entry-tools" open>
    <summary>
        <span><i class="mdi mdi-tune" aria-hidden="true"></i> Filter &amp; PPN</span>
        <span id="purchaseFilterStatus" class="purchase-filter-status d-none">Filter aktif</span>
        <i class="mdi mdi-chevron-down purchase-tools-chevron" aria-hidden="true"></i>
    </summary>
    <div class="purchase-entry-tools-body">
        <div class="purchase-items-toolbar">
            <input type="search" id="purchaseItemSearch" class="form-control" placeholder="Cari item dalam PO..." aria-label="Cari item dalam PO">
            <label class="purchase-filter-check"><input type="checkbox" id="purchaseIssuesOnly" class="form-check-input"> Hanya yang bermasalah</label>
            <button type="button" id="purchaseNextIssue" class="btn btn-outline-warning btn-sm"><span class="purchase-desktop-text">Ke masalah berikutnya</span><span class="purchase-mobile-text">Lihat Masalah</span></button>
            <label class="purchase-select-all-mobile" for="purchaseSelectVisibleMobile"><input type="checkbox" id="purchaseSelectVisibleMobile" class="form-check-input"> Pilih semua item yang tampil</label>
        </div>
        <div class="purchase-tax-toolbar">
            <label class="form-label" for="purchaseAllTax">PPN semua obat (%)</label>
            <input type="number" id="purchaseAllTax" class="form-control" min="0" max="100" step="0.01" value="11" inputmode="decimal" aria-describedby="purchaseAllTaxHint" required>
            <button type="button" id="applyPurchaseAllTax" class="btn btn-outline-primary btn-sm">Terapkan PPN ke Semua Obat</button>
            <small id="purchaseAllTaxHint">Berlaku untuk seluruh baris dan obat yang ditambahkan berikutnya.</small>
            <small id="purchaseAllTaxStatus" role="status" aria-live="polite"></small>
        </div>
    </div>
</details>
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
