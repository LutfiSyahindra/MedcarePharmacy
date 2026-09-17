<div class="modal fade" id="fnSettlementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content fn-modal" id="fnSettlementForm">
            <div class="modal-header"><div><span class="fn-modal-kicker" id="fnSettlementKicker">PEMBAYARAN</span><h5 class="modal-title" id="fnSettlementTitle">Catat pembayaran</h5><p id="fnSettlementSubtitle">Saldo sumber dan Buku Kas akan diperbarui bersamaan.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="fnSettlementKind"><input type="hidden" id="fnSettlementId">
                <div class="fn-settlement-summary"><span><small>Referensi</small><strong id="fnSettlementReference">-</strong></span><span><small>Pihak terkait</small><strong id="fnSettlementCounterparty">-</strong></span><span><small>Sisa saldo</small><strong id="fnSettlementRemaining">Rp 0</strong></span></div>
                <div class="fn-modal-grid">
                    <label><span>Metode pembayaran <b>*</b></span><select class="form-select" name="payment_method" id="fnSettlementMethod" required>@foreach($settlementPaymentMethods as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                    <label><span>Tanggal pembayaran <b>*</b></span><input class="form-control" type="datetime-local" name="occurred_at" id="fnSettlementDate" required></label>
                    <label><span>Nominal <b>*</b></span><div class="fn-money-input"><b>Rp</b><input class="form-control" type="number" name="amount" id="fnSettlementAmount" min="0.01" step="0.01" required></div><small id="fnSettlementLimit">Maksimal Rp 0</small></label>
                    <label><span>Nomor referensi</span><input class="form-control" name="reference_no" maxlength="120" placeholder="Nomor transfer / bukti bayar"></label>
                    <label class="fn-modal-wide"><span>Catatan</span><textarea class="form-control" name="notes" maxlength="500" rows="3" placeholder="Keterangan pembayaran (opsional)"></textarea></label>
                </div>
                <div class="fn-cash-notice" id="fnSettlementCashNotice"><i class="mdi mdi-cash-register"></i><span>Pembayaran tunai memakai waktu saat ini dan memerlukan shift kasir aktif milik Anda pada cabang sumber.</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn fn-btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn fn-btn-primary" id="fnSaveSettlement"><i class="mdi mdi-content-save-check-outline"></i> Simpan pembayaran</button></div>
        </form>
    </div>
</div>
