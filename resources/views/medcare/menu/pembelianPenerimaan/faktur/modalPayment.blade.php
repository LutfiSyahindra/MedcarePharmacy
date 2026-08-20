<div class="modal fade purchase-modal invoice-modal" id="fakturPaymentModal" tabindex="-1"
    aria-labelledby="fakturPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="fakturPaymentForm">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-cash-sync"></i></span>
                    <div>
                        <h5 class="modal-title" id="fakturPaymentModalLabel">Kelola Faktur</h5>
                        <p class="modal-subtitle" id="fakturPaymentSubtitle">-</p>
                    </div>
                </div>
                <div class="purchase-modal-header-meta">
                    <span class="purchase-modal-status" id="invoicePaymentStatusBadge">
                        <i class="mdi mdi-progress-clock"></i>
                        -
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body invoice-payment-body">
                <input type="hidden" id="faktur_id" name="faktur_id">

                <section class="invoice-payment-summary">
                    <div>
                        <small>Total Faktur</small>
                        <strong id="paymentTotalFaktur">Rp 0</strong>
                    </div>
                    <div>
                        <small>Dibayar</small>
                        <strong id="paymentPaidAmount">Rp 0</strong>
                    </div>
                    <div>
                        <small>Sisa Hutang</small>
                        <strong id="paymentRemainingDebt">Rp 0</strong>
                    </div>
                </section>

                <section class="invoice-payment-meter-card">
                    <div class="invoice-payment-meter-head">
                        <span id="paymentProgressLabel">0%</span>
                        <strong id="paymentProgressAmount">Rp 0 / Rp 0</strong>
                    </div>
                    <div class="invoice-payment-meter">
                        <span id="paymentProgressMeter" style="width: 0%"></span>
                    </div>
                    <div class="invoice-payment-shortcuts" aria-label="Aksi cepat pembayaran">
                        <button type="button" class="invoice-shortcut-btn" data-payment-preset="none">
                            <i class="mdi mdi-numeric-0-box-outline"></i>
                            Belum
                        </button>
                        <button type="button" class="invoice-shortcut-btn" data-payment-preset="half">
                            <i class="mdi mdi-circle-half-full"></i>
                            50%
                        </button>
                        <button type="button" class="invoice-shortcut-btn" data-payment-preset="full">
                            <i class="mdi mdi-check-decagram-outline"></i>
                            Lunas
                        </button>
                    </div>
                </section>

                <section class="invoice-form-grid">
                    <div class="invoice-field">
                        <label class="form-label">Nomor Faktur</label>
                        <input type="text" class="form-control" name="nomor_faktur" id="paymentNomorFaktur"
                            autocomplete="off" required>
                    </div>
                    <div class="invoice-field">
                        <label class="form-label">Tanggal Faktur</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="mdi mdi-calendar-blank-outline"></i></span>
                            <input type="text" class="form-control" name="tanggal_faktur" id="paymentTanggalFaktur"
                                autocomplete="off" required>
                        </div>
                    </div>
                    <div class="invoice-field">
                        <label class="form-label">Tanggal Jatuh Tempo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="mdi mdi-calendar-alert"></i></span>
                            <input type="text" class="form-control" name="tanggal_jatuh_tempo"
                                id="paymentTanggalJatuhTempo" autocomplete="off">
                        </div>
                    </div>
                    <div class="invoice-field">
                        <label class="form-label">Biaya Lain</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control invoice-money-field" name="biaya_lain"
                                id="paymentBiayaLain" autocomplete="off">
                        </div>
                    </div>
                    <div class="invoice-field">
                        <label class="form-label">Jumlah Dibayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control invoice-money-field" name="jumlah_dibayar"
                                id="paymentJumlahDibayar" autocomplete="off">
                        </div>
                    </div>
                    <div class="invoice-field">
                        <label class="form-label">Status Otomatis</label>
                        <input type="text" class="form-control" id="paymentStatusReadonly" readonly>
                    </div>
                </section>

                <section class="invoice-calculation-panel">
                    <div>
                        <span>Subtotal</span>
                        <strong id="paymentSubtotal">Rp 0</strong>
                    </div>
                    <div>
                        <span>Diskon</span>
                        <strong id="paymentDiscount">Rp 0</strong>
                    </div>
                    <div>
                        <span>PPN</span>
                        <strong id="paymentTax">Rp 0</strong>
                    </div>
                    <div>
                        <span>Potongan Ganti Rugi</span>
                        <strong id="paymentCompensationDiscount">Rp 0</strong>
                    </div>
                    <div>
                        <span>Total Faktur (Asli)</span>
                        <strong id="paymentComputedTotal">Rp 0</strong>
                    </div>
                    <div class="is-total">
                        <span>Tagihan Setelah Ganti Rugi</span>
                        <strong id="paymentPayableTotal">Rp 0</strong>
                    </div>
                </section>

                <div class="invoice-field">
                    <label class="form-label">Catatan</label>
                    <textarea class="form-control" name="catatan" id="paymentCatatan" rows="3"
                        placeholder="Catatan internal faktur atau pembayaran"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close"></i>
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" id="submitFakturPayment">
                    <i class="mdi mdi-content-save-outline"></i>
                    Simpan Faktur
                </button>
            </div>
        </form>
    </div>
</div>
