<div class="modal fade purchase-modal" id="penerimaanModalDetail" tabindex="-1"
    aria-labelledby="penerimaanModalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-package-variant-closed-check" aria-hidden="true"></i></span>
                    <div>
                        <span class="receipt-detail-eyebrow">Pembelian &amp; Persediaan</span>
                        <h5 class="modal-title" id="penerimaanModalDetailLabel">Detail Penerimaan Barang</h5>
                        <p class="modal-subtitle">Rincian barang, faktur, dan pembayaran dalam satu tampilan.</p>
                    </div>
                </div>
                <div class="receipt-detail-header-actions">
                    <span class="purchase-status" id="detailReceiveStatus">Draft</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup detail penerimaan"></button>
                </div>
            </div>

            <div class="modal-body">
                <div class="receipt-detail-metrics" aria-label="Ringkasan nilai penerimaan">
                    <div class="receipt-detail-metric is-total">
                        <span><i class="mdi mdi-receipt-text-outline" aria-hidden="true"></i> Total Faktur</span>
                        <strong id="detailGrandTotal">Rp 0</strong>
                        <small>Setelah diskon, PPN, dan biaya lain</small>
                    </div>
                    <div class="receipt-detail-metric is-discount">
                        <span><i class="mdi mdi-tag-outline" aria-hidden="true"></i> Total Diskon</span>
                        <strong id="detailTotalDiscount">Rp 0</strong>
                        <small>Akumulasi diskon bertingkat seluruh barang</small>
                    </div>
                    <div class="receipt-detail-metric">
                        <span><i class="mdi mdi-percent-outline" aria-hidden="true"></i> Total PPN</span>
                        <strong id="detailTotalPpn">Rp 0</strong>
                        <small>PPN dihitung setelah diskon barang</small>
                    </div>
                </div>

                <div class="receipt-detail-layout">
                    <div class="receipt-detail-main">
                        <section class="receipt-detail-section" aria-labelledby="receiptTransactionTitle">
                            <div class="receipt-detail-section-heading">
                                <div><span class="receipt-detail-step">01</span><h6 id="receiptTransactionTitle">Informasi Penerimaan</h6></div>
                                <i class="mdi mdi-clipboard-check-outline" aria-hidden="true"></i>
                            </div>
                            <dl class="receipt-detail-metadata">
                                <div><dt>Nomor Penerimaan</dt><dd id="detailNomorPenerimaan">-</dd></div>
                                <div><dt>Nomor PO</dt><dd id="detailNoPo">-</dd></div>
                                <div><dt>Tanggal Terima</dt><dd id="detailTanggalTerima">-</dd></div>
                                <div><dt>Supplier</dt><dd id="detailSupplier">-</dd></div>
                                <div><dt>Nomor Surat Jalan</dt><dd id="detailNomorSuratJalan">-</dd></div>
                                <div><dt>Diskon Untuk</dt><dd id="detailDiskonUntuk">-</dd></div>
                                <div class="receipt-detail-note"><dt><i class="mdi mdi-text-box-outline" aria-hidden="true"></i> Catatan</dt><dd id="detailCatatan">-</dd></div>
                            </dl>
                        </section>

                        <section class="receipt-detail-section receipt-detail-items" aria-labelledby="receiptItemsTitle">
                            <div class="receipt-detail-section-heading">
                                <div><span class="receipt-detail-step">02</span><h6 id="receiptItemsTitle">Barang Diterima</h6></div>
                                <span class="receipt-detail-count"><span id="detailReceiveItemCount">0</span> item</span>
                            </div>
                            <div class="receipt-detail-searchbar">
                                <div class="receipt-detail-search-field" id="detailReceiveMedicineDropdown">
                                    <label for="detailReceiveMedicineSearch"><i class="mdi mdi-magnify" aria-hidden="true"></i> Cari obat dalam penerimaan</label>
                                    <select id="detailReceiveMedicineSearch" aria-describedby="detailReceiveFilterCount" style="width: 100%">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <button type="button" class="btn receipt-detail-reset" id="detailReceiveResetSearch" disabled>
                                    <i class="mdi mdi-filter-remove-outline" aria-hidden="true"></i> Tampilkan semua
                                </button>
                            </div>
                            <p class="receipt-detail-filter-count" id="detailReceiveFilterCount" role="status" aria-live="polite">Menampilkan 0 item</p>
                            <div class="table-responsive receipt-detail-table-wrap">
                                <table class="table purchase-detail-table align-middle" id="detailReceiveTable">
                                    <caption class="visually-hidden">Rincian obat, kuantitas, batch, harga beli, diskon bertingkat, PPN, dan total penerimaan.</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Obat</th>
                                            <th scope="col">Qty Diterima</th>
                                            <th scope="col">Batch / Kedaluwarsa</th>
                                            <th scope="col">Harga Beli</th>
                                            <th scope="col">Diskon 1 / 2 / 3</th>
                                            <th scope="col">PPN</th>
                                            <th scope="col" class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="receipt-detail-empty" id="detailReceiveEmpty" hidden>
                                <i class="mdi mdi-package-variant" aria-hidden="true"></i>
                                <strong>Belum ada barang diterima</strong>
                                <span>Rincian obat akan tampil setelah barang ditambahkan.</span>
                            </div>
                            <p class="receipt-detail-table-note"><i class="mdi mdi-information-outline" aria-hidden="true"></i> Total faktur, diskon, dan PPN tetap mencakup seluruh barang saat pencarian digunakan.</p>
                        </section>
                    </div>

                    <aside class="receipt-detail-section receipt-detail-invoice" aria-labelledby="receiptInvoiceTitle">
                        <div class="receipt-detail-section-heading">
                            <div><i class="mdi mdi-receipt-text-outline" aria-hidden="true"></i><h6 id="receiptInvoiceTitle">Ringkasan Faktur</h6></div>
                        </div>
                        <dl class="receipt-detail-invoice-meta">
                            <div><dt>Nomor Faktur</dt><dd id="detailNomorFaktur">-</dd></div>
                            <div><dt>Tanggal Faktur</dt><dd id="detailTanggalFaktur">-</dd></div>
                            <div><dt>Jatuh Tempo</dt><dd id="detailTanggalJatuhTempo">-</dd></div>
                        </dl>
                        <dl class="receipt-detail-breakdown">
                            <div><dt>Subtotal Barang</dt><dd id="detailInvoiceSubtotal">Rp 0</dd></div>
                            <div class="is-discount"><dt>Total Diskon <small>(−)</small></dt><dd id="detailInvoiceDiskon">Rp 0</dd></div>
                            <div><dt>Total PPN <small>(+)</small></dt><dd id="detailInvoicePajak">Rp 0</dd></div>
                            <div><dt>Biaya Lain <small>(+)</small></dt><dd id="detailInvoiceBiayaLain">Rp 0</dd></div>
                            <div class="receipt-detail-invoice-total"><dt>Total Faktur</dt><dd id="detailTotalFaktur">Rp 0</dd></div>
                            <div><dt>Potongan Ganti Rugi <small>(−)</small></dt><dd id="detailSupplierCompensationDiscount">Rp 0</dd></div>
                            <div class="receipt-detail-payable"><dt>Tagihan Akhir</dt><dd id="detailPayableTotal">Rp 0</dd></div>
                        </dl>
                        <div class="receipt-detail-payment">
                            <div class="receipt-detail-payment-heading"><span>Pembayaran</span><strong id="detailStatusPembayaran">-</strong></div>
                            <dl>
                                <div><dt>Sudah Dibayar</dt><dd id="detailJumlahDibayar">Rp 0</dd></div>
                                <div class="receipt-detail-debt"><dt>Sisa Hutang</dt><dd id="detailSisaHutang">Rp 0</dd></div>
                            </dl>
                        </div>
                    </aside>
                </div>
            </div>

            <div class="modal-footer">
                <span class="receipt-detail-footer-note"><i class="mdi mdi-file-document-check-outline" aria-hidden="true"></i> Rincian sesuai data penerimaan tersimpan</span>
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Tutup</button>
                <a href="#" class="btn btn-primary px-4" id="detailPrintPenerimaan" target="_blank" rel="noopener">
                    <i class="mdi mdi-printer-outline" aria-hidden="true"></i><span>Cetak Penerimaan</span>
                </a>
            </div>
        </div>
    </div>
</div>
