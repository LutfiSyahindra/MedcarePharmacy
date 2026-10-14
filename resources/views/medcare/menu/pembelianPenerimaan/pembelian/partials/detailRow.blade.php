<tr class="detail-item purchase-detail-row">
    <td class="purchase-row-selection">
        <span class="purchase-detail-number">1</span>
        <input type="checkbox" class="form-check-input purchase-row-check" aria-label="Pilih baris obat 1">
    </td>
    <td class="purchase-medicine-field">
        <label class="form-label purchase-cell-label">Obat</label>
        <select class="form-select obat-select" data-width="100%" name="obat_id[]" required></select>
        <div class="purchase-current-stock is-empty"><span>Pilih obat untuk melihat stok saat ini</span></div>
        <div class="purchase-row-errors text-danger" aria-live="polite"></div>
    </td>
    <td class="purchase-unit-field">
        <label class="form-label purchase-cell-label">Satuan beli</label>
        <select class="form-select satuan-select" name="satuan_id[]" disabled required><option value="">-- Pilih Satuan --</option></select>
        <small class="purchase-unit-warning text-warning d-none">Lengkapi satuan konversi. Sementara, simpan sebagai draft.</small>
    </td>
    <td class="purchase-qty-field">
        <label class="form-label purchase-cell-label">Qty</label>
        <input type="number" class="form-control qty" name="qty[]" min="1" step="any" value="1" inputmode="decimal" required>
    </td>
    <td class="purchase-price-field">
        <label class="form-label purchase-cell-label">Harga / Satuan Dasar (Rp)</label>
        <input type="number" class="form-control harga_estimasi_satuan" name="harga_estimasi_satuan[]" min="0" step="any" value="0" inputmode="decimal" required>
        <input type="hidden" class="harga_estimasi" name="harga_estimasi[]" value="0">
        <small class="purchase-unit-price">Pilih satuan beli</small>
    </td>
    <td class="purchase-subtotal-field">
        <label class="form-label purchase-cell-label">Subtotal + PPN (Rp)</label>
        <input type="number" class="form-control subtotal" name="subtotal[]" value="0" readonly>
    </td>
    <td class="purchase-row-actions">
        <details class="purchase-item-adjustments">
            <summary><span>Diskon &amp; PPN <i class="mdi mdi-chevron-down" aria-hidden="true"></i></span><span class="purchase-adjustments-value">Tanpa diskon · PPN 11%</span></summary>
            <div class="purchase-adjustment-fields">
                <div><label class="form-label">Diskon 1 (%)</label><input type="number" class="form-control purchase-discount" name="diskon_1[]" min="0" max="100" step="0.01" value="0" inputmode="decimal"></div>
                <div><label class="form-label">Diskon 2 (%)</label><input type="number" class="form-control purchase-discount" name="diskon_2[]" min="0" max="100" step="0.01" value="0" inputmode="decimal"></div>
                <div><label class="form-label">Diskon 3 (%)</label><input type="number" class="form-control purchase-discount" name="diskon_3[]" min="0" max="100" step="0.01" value="0" inputmode="decimal"></div>
                <div><label class="form-label">PPN (%)</label><input type="number" class="form-control purchase-tax" name="ppn[]" min="0" max="100" step="0.01" value="11" inputmode="decimal" required></div>
            </div>
            <small class="purchase-adjustment-hint">Diskon dihitung berurutan; PPN setelah diskon.</small>
        </details>
        <button type="button" class="btn btn-sm btn-outline-danger remove-detail"><i class="mdi mdi-trash-can-outline" aria-hidden="true"></i> Hapus</button>
    </td>
</tr>
