<div class="modal fade purchase-modal invoice-modal" id="fakturDetailModal" tabindex="-1"
    aria-labelledby="fakturDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-receipt-text-check-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="fakturDetailModalLabel">Detail Faktur</h5>
                        <p class="modal-subtitle" id="fakturDetailSubtitle">-</p>
                    </div>
                </div>
                <div class="purchase-modal-header-meta">
                    <span class="purchase-modal-status" id="fakturDetailPaymentStatus">
                        <i class="mdi mdi-progress-clock"></i>
                        -
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body invoice-detail-body">
                <section class="invoice-detail-overview">
                    <article class="invoice-detail-card is-main">
                        <small>Nomor Faktur</small>
                        <strong id="detailInvoiceNumber">-</strong>
                        <span id="detailInvoiceSupplier">-</span>
                    </article>
                    <article class="invoice-detail-card">
                        <small>Total Faktur</small>
                        <strong id="detailInvoiceTotal">Rp 0</strong>
                        <span id="detailInvoiceDate">-</span>
                    </article>
                    <article class="invoice-detail-card">
                        <small>Sisa Hutang</small>
                        <strong id="detailInvoiceDebt">Rp 0</strong>
                        <span id="detailInvoiceDueDate">Jatuh tempo -</span>
                        <span id="detailInvoiceDue">-</span>
                    </article>
                    <article class="invoice-detail-card">
                        <small>Progress Bayar</small>
                        <strong id="detailInvoiceProgressText">0%</strong>
                        <div class="invoice-inline-meter">
                            <span id="detailInvoiceProgressMeter" style="width: 0%"></span>
                        </div>
                    </article>
                </section>

                <section class="invoice-detail-grid">
                    <div class="invoice-info-panel">
                        <div class="invoice-section-heading">
                            <span><i class="mdi mdi-file-document-outline"></i></span>
                            <div>
                                <strong>Dokumen</strong>
                                <small>Referensi transaksi pembelian.</small>
                            </div>
                        </div>
                        <div class="invoice-info-list">
                            <div>
                                <span>Penerimaan</span>
                                <strong id="detailReceiptNumber">-</strong>
                            </div>
                            <div>
                                <span>Purchase Order</span>
                                <strong id="detailPoNumber">-</strong>
                            </div>
                            <div>
                                <span>Surat Jalan</span>
                                <strong id="detailDeliveryNote">-</strong>
                            </div>
                            <div>
                                <span>Jatuh Tempo</span>
                                <strong id="detailDocumentDueDate">-</strong>
                            </div>
                            <div>
                                <span>Branch</span>
                                <strong id="detailInvoiceBranch">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-info-panel">
                        <div class="invoice-section-heading">
                            <span><i class="mdi mdi-cash-multiple"></i></span>
                            <div>
                                <strong>Nilai Faktur</strong>
                                <small>Nilai asli faktur, ganti rugi, dan pembayaran.</small>
                            </div>
                        </div>
                        <div class="invoice-money-list">
                            <div>
                                <span>Subtotal</span>
                                <strong id="detailSubtotal">Rp 0</strong>
                            </div>
                            <div>
                                <span>Diskon</span>
                                <strong id="detailDiscount">Rp 0</strong>
                            </div>
                            <div>
                                <span>PPN</span>
                                <strong id="detailTax">Rp 0</strong>
                            </div>
                            <div>
                                <span>Biaya Lain</span>
                                <strong id="detailOtherCost">Rp 0</strong>
                            </div>
                            <div>
                                <span>Potongan Ganti Rugi</span>
                                <strong id="detailCompensationDiscount">Rp 0</strong>
                            </div>
                            <div class="is-emphasis">
                                <span>Tagihan Setelah Ganti Rugi</span>
                                <strong id="detailPayableTotal">Rp 0</strong>
                            </div>
                            <div class="is-emphasis">
                                <span>Dibayar</span>
                                <strong id="detailPaidAmount">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="invoice-items-panel">
                    <div class="invoice-section-heading">
                        <span><i class="mdi mdi-pill-multiple"></i></span>
                        <div>
                            <strong>Item Faktur</strong>
                            <small id="detailItemSummary">0 item</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table invoice-item-table align-middle">
                            <thead>
                                <tr>
                                    <th>Obat</th>
                                    <th>Qty</th>
                                    <th>Batch</th>
                                    <th>Harga</th>
                                    <th>Diskon / PPN</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="detailInvoiceItems"></tbody>
                        </table>
                    </div>
                </section>

                <section class="invoice-note-panel">
                    <span><i class="mdi mdi-note-text-outline"></i></span>
                    <p id="detailInvoiceNote">-</p>
                </section>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close"></i>
                    Tutup
                </button>
                <button type="button" class="btn btn-primary" id="editInvoiceFromDetail">
                    <i class="mdi mdi-pencil-outline"></i>
                    Edit Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>
