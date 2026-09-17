<div class="modal fade" id="fnTransactionModal" tabindex="-1" aria-labelledby="fnTransactionTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content fn-modal">
        <div class="modal-header"><div><span class="fn-modal-kicker">JURNAL OPERASIONAL</span><h5 class="modal-title" id="fnTransactionTitle">Catat transaksi keuangan</h5><p>Transaksi tunai otomatis masuk ke shift kasir Anda yang aktif.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form id="fnTransactionForm">
            <div class="modal-body"><div class="fn-modal-grid">
                <label><span>Cabang <b>*</b></span><select class="form-select" name="branch_id" id="fnEntryBranch" required></select></label>
                <label><span>Jenis transaksi <b>*</b></span><select class="form-select" name="type" id="fnEntryType" required><option value="income">Pendapatan</option><option value="expense">Pengeluaran</option></select></label>
                <label><span>Kategori <b>*</b></span><select class="form-select" name="category" id="fnEntryCategory" required></select></label>
                <label><span>Metode pembayaran <b>*</b></span><select class="form-select" name="payment_method" id="fnEntryMethod" required>@foreach($paymentMethods as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                <label><span>Nominal <b>*</b></span><div class="fn-money-input"><b>Rp</b><input class="form-control" type="number" name="amount" min="1" max="999999999999.99" step="0.01" placeholder="0" required></div></label>
                <label><span>Tanggal & waktu <b>*</b></span><input class="form-control" type="datetime-local" name="occurred_at" id="fnEntryOccurredAt" required></label>
                <label><span>Referensi eksternal</span><input class="form-control" type="text" name="reference_no" maxlength="120" placeholder="Nomor bukti / invoice"></label>
                <label class="fn-modal-wide"><span>Keterangan <b>*</b></span><textarea class="form-control" name="description" rows="3" maxlength="255" placeholder="Jelaskan tujuan transaksi agar mudah diaudit" required></textarea></label>
            </div><div class="fn-cash-notice" id="fnCashNotice"><i class="mdi mdi-swap-horizontal-bold"></i><span><b>Terhubung ke kasir.</b> Metode tunai memerlukan shift Anda aktif. Waktu transaksi akan mengikuti waktu pencatatan agar rekonsiliasi laci akurat.</span></div></div>
            <div class="modal-footer"><button type="button" class="btn fn-btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn fn-btn-primary" id="fnSaveTransaction"><i class="mdi mdi-content-save-check-outline"></i> Simpan transaksi</button></div>
        </form>
    </div></div>
</div>
