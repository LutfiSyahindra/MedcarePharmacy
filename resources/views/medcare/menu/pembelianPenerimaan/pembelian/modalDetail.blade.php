<div class="modal fade purchase-modal purchase-order-detail" id="pembelianModalDetail" tabindex="-1"
    aria-labelledby="pembelianModalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-file-document-check-outline" aria-hidden="true"></i></span>
                    <div>
                        <h5 class="modal-title" id="pembelianModalDetailLabel">Detail Purchase Order</h5>
                        <p class="modal-subtitle">Informasi pesanan, rincian obat, dan estimasi biaya.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <section class="purchase-detail-overview" aria-label="Informasi purchase order">
                    <div class="purchase-detail-reference">
                        <div>
                            <span class="purchase-detail-eyebrow">Nomor Purchase Order</span>
                            <h3 id="detail_no_po">—</h3>
                        </div>
                        <span id="detail_status" class="purchase-status is-other"></span>
                    </div>
                    <dl class="purchase-detail-metadata">
                        <div>
                            <span class="purchase-detail-meta-icon"><i class="mdi mdi-truck-outline" aria-hidden="true"></i></span>
                            <div><dt>Distributor</dt><dd id="detail_distributor">—</dd></div>
                        </div>
                        <div>
                            <span class="purchase-detail-meta-icon"><i class="mdi mdi-calendar-month-outline" aria-hidden="true"></i></span>
                            <div><dt>Tanggal PO</dt><dd id="detail_tanggal_po">—</dd></div>
                        </div>
                        <div>
                            <span class="purchase-detail-meta-icon"><i class="mdi mdi-store-outline" aria-hidden="true"></i></span>
                            <div><dt>Cabang</dt><dd id="detail_branch">—</dd></div>
                        </div>
                    </dl>
                </section>
                <div class="purchase-detail-layout">
                    <div class="purchase-detail-main">
                        <section class="purchase-detail-surface" aria-labelledby="purchaseDetailItemsTitle">
                            <div class="purchase-detail-surface-heading">
                                <div>
                                    <span class="purchase-detail-eyebrow">Rincian Pesanan</span>
                                    <h6 id="purchaseDetailItemsTitle">Daftar obat</h6>
                                </div>
                                <span class="purchase-detail-item-count"><i class="mdi mdi-pill" aria-hidden="true"></i>
                                    <span id="detail_total_item">0</span> item
                                </span>
                            </div>
                            <div class="purchase-detail-toolbar">
                                <div class="purchase-detail-search">
                                    <label for="detailMedicineSearch">Cari obat dalam pesanan</label>
                                    <select id="detailMedicineSearch" class="form-select" style="width: 100%"
                                        aria-describedby="detailMedicineResultCount"><option value=""></option></select>
                                </div>
                                <button type="button" id="resetDetailMedicineSearch" class="btn btn-light" disabled>
                                    <i class="mdi mdi-filter-remove-outline" aria-hidden="true"></i> Tampilkan semua
                                </button>
                            </div>
                            <div class="purchase-detail-results" id="detailMedicineResultCount" role="status" aria-live="polite"></div>
                            <div class="table-responsive purchase-detail-table-wrap" role="region" aria-label="Rincian obat" tabindex="0">
                                <table class="table purchase-detail-table align-middle" id="detailObatTable">
                                    <caption class="visually-hidden">Rincian obat, diskon bertingkat, PPN, dan subtotal purchase order.</caption>
                                    <thead><tr>
                                        <th scope="col">Nama Obat</th>
                                        <th scope="col">Satuan</th>
                                        <th scope="col" class="text-end">Qty</th>
                                        <th scope="col" class="text-end">Harga Estimasi</th>
                                        <th scope="col">Diskon 1 / 2 / 3</th>
                                        <th scope="col" class="text-end">PPN</th>
                                        <th scope="col" class="text-end">Subtotal + PPN</th>
                                    </tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div id="detailMedicineEmpty" class="purchase-detail-empty d-none" role="status">
                                <i class="mdi mdi-pill" aria-hidden="true"></i>
                                <strong>Belum ada obat dalam pesanan ini</strong>
                                <span>Rincian obat akan tampil setelah ditambahkan ke purchase order.</span>
                            </div>
                        </section>
                        <section class="purchase-detail-note" aria-labelledby="purchaseDetailNotesTitle">
                            <i class="mdi mdi-text-box-outline" aria-hidden="true"></i>
                            <div>
                                <h6 id="purchaseDetailNotesTitle">Catatan pesanan</h6>
                                <p id="detail_catatan">Belum ada catatan untuk pesanan ini.</p>
                            </div>
                        </section>
                    </div>
                    <aside class="purchase-detail-cost-summary purchase-detail-surface" aria-labelledby="purchaseDetailSummaryTitle">
                        <div class="purchase-detail-cost-summary-heading">
                            <span class="purchase-detail-cost-summary-icon"><i class="mdi mdi-receipt-text-outline" aria-hidden="true"></i></span>
                            <h6 id="purchaseDetailSummaryTitle">Ringkasan biaya</h6>
                            <p>Estimasi seluruh pesanan</p>
                        </div>
                        <dl class="purchase-detail-costs">
                            <div><dt>Subtotal awal <small>Sebelum diskon &amp; PPN</small></dt><dd id="detail_subtotal_awal">Rp 0</dd></div>
                            <div class="purchase-detail-discount-total"><dt>Total diskon <small>Diskon 1, 2, dan 3 bertingkat</small></dt><dd id="detail_total_diskon">Rp 0</dd></div>
                            <div><dt>Subtotal setelah diskon</dt><dd id="detail_subtotal_netto">Rp 0</dd></div>
                            <div><dt>Total PPN <small>Mengikuti PPN tiap obat</small></dt><dd id="detail_total_ppn">Rp 0</dd></div>
                            <div class="purchase-detail-cost-subtotal"><dt>Subtotal obat <small>Setelah diskon &amp; PPN</small></dt><dd id="detail_subtotal_obat">Rp 0</dd></div>
                            <div><dt>Asuransi</dt><dd id="detail_biaya_asuransi">Rp 0</dd></div>
                            <div><dt>Pengiriman</dt><dd id="detail_biaya_pengiriman">Rp 0</dd></div>
                        </dl>
                        <div class="purchase-detail-grand-total">
                            <span>Total estimasi</span>
                            <strong id="detail_total_estimasi">Rp 0</strong>
                        </div>
                        <p class="purchase-detail-cost-summary-hint"><i class="mdi mdi-information-outline" aria-hidden="true"></i>
                            Nilai akhir mengikuti faktur dan penerimaan barang.
                        </p>
                    </aside>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="btnPrintPDF" disabled
                    title="Cetak surat pesanan sesuai jenis SP dalam PO">
                    <i class="mdi mdi-printer-outline fs-5" aria-hidden="true"></i>
                    <span>Cetak Surat Pesanan</span>
                </button>
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
