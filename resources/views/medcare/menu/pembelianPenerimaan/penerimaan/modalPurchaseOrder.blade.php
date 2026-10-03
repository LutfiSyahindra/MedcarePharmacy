<div class="modal fade purchase-modal receive-po-picker-modal" id="receivePoPickerModal" tabindex="-1"
    aria-labelledby="receivePoPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header receive-po-picker-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-file-check-outline"></i></span>
                    <div>
                        <span class="receive-po-picker-eyebrow">PENERIMAAN BARANG</span>
                        <h5 class="modal-title" id="receivePoPickerModalLabel">Temukan pembelian yang akan diterima</h5>
                        <p class="modal-subtitle">Cari PO, periksa obatnya, lalu pilih satu faktur atau pecah faktur.</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="receive-po-picker-steps" aria-label="Langkah penerimaan">
                    <span class="is-active" id="receivePickerStepList"><b>01</b> Pilih pembelian</span>
                    <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                    <span id="receivePickerStepDetail"><b>02</b> Periksa detail</span>
                    <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                    <span><b>03</b> Pilih faktur</span>
                </div>
                <div id="receivePoPickerMessage" class="alert d-none" role="status" aria-live="polite"></div>
                <div id="receivePoPickerList">
                    <div class="receive-po-picker-overview" aria-label="Ringkasan pembelian tersedia">
                        <div class="receive-po-picker-stat">
                            <span class="receive-po-picker-stat-icon"><i class="mdi mdi-file-check-outline"></i></span>
                            <div><small>PO siap diterima</small><strong id="receivePickerOrderCount">0</strong></div>
                            <span class="receive-po-picker-approved"><i class="mdi mdi-check-decagram"></i> Masih bersisa</span>
                        </div>
                        <div class="receive-po-picker-stat">
                            <span class="receive-po-picker-stat-icon is-blue"><i class="mdi mdi-package-variant-closed"></i></span>
                            <div><small>Total sisa qty</small><strong id="receivePickerOutstandingCount">0</strong></div>
                        </div>
                        <div class="receive-po-picker-stat">
                            <span class="receive-po-picker-stat-icon is-amber"><i class="mdi mdi-wallet-outline"></i></span>
                            <div><small>Nilai estimasi pembelian</small><strong id="receivePickerEstimatedValue">Rp 0</strong></div>
                        </div>
                    </div>
                    <div class="receive-po-picker-search-panel">
                        <label for="receivePoPickerOrderSearch">Cari PO</label>
                        <div class="receive-po-picker-toolbar">
                            <div class="receive-po-picker-search">
                                <i class="mdi mdi-magnify" aria-hidden="true"></i>
                                <input type="text" id="receivePoPickerOrderSearch" class="form-control"
                                    placeholder="Nomor PO, supplier, cabang, tanggal, atau obat..."
                                    autocomplete="off" aria-describedby="receivePoPickerOrderSearchHint">
                                <button type="button" class="btn receive-po-picker-clear d-none" id="receivePoPickerClearSearch"
                                    aria-label="Hapus pencarian PO" title="Hapus pencarian PO">
                                    <i class="mdi mdi-close" aria-hidden="true"></i>
                                </button>
                            </div>
                            <button type="button" class="btn receive-po-picker-refresh" id="receivePoPickerRefresh"
                                aria-label="Muat ulang pembelian" title="Muat ulang pembelian">
                                <i class="mdi mdi-refresh"></i> <span>Muat ulang</span>
                            </button>
                        </div>
                        <small id="receivePoPickerOrderSearchHint">Cari berdasarkan nomor PO, nama supplier, cabang, tanggal PO, atau nama / kode obat.</small>
                        <div class="receive-po-picker-medicine-filter">
                            <label for="receivePoPickerSearch">Filter obat di dalam PO (opsional)</label>
                            <div class="receive-po-picker-search">
                                <i class="mdi mdi-pill" aria-hidden="true"></i>
                                <select id="receivePoPickerSearch" class="form-select"
                                    aria-describedby="receivePoPickerSearchHint">
                                    <option value=""></option>
                                </select>
                            </div>
                            <small id="receivePoPickerSearchHint">Pilih obat untuk mempersempit hasil pencarian PO.</small>
                        </div>
                    </div>
                    <div class="receive-po-picker-results" aria-live="polite">
                        <strong id="receivePoPickerResultCount">0 pembelian tersedia</strong>
                        <span id="receivePoPickerResultHint">Pilih satu PO untuk melanjutkan.</span>
                    </div>
                    <div class="table-responsive purchase-table-wrap receive-po-picker-table-wrap">
                        <table id="receivePoPickerTable" class="table purchase-table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Pembelian</th>
                                    <th>Supplier / Cabang</th>
                                    <th>Tanggal PO</th>
                                    <th>Obat &amp; Sisa Qty</th>
                                    <th>Total Estimasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="receive-po-picker-note">
                        <i class="mdi mdi-information-outline"></i>
                        PO disetujui atau diterima sebagian yang masih bersisa. Qty dalam draft dan faktur sebelumnya sudah mengurangi sisa PO.
                    </div>
                </div>
                <section id="receivePoPickerDetail" class="d-none" aria-label="Detail pembelian">
                    <div class="receive-po-picker-toolbar receive-po-picker-detail-toolbar">
                        <button type="button" class="btn btn-outline-secondary" id="receivePoPickerBack">
                            <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="receivePoPickerSelectDetail">
                            <i class="mdi mdi-check-circle-outline"></i> Pilih Pembelian Ini
                        </button>
                    </div>
                    <div class="receive-po-picker-summary">
                        <div><small>Nomor PO</small><strong id="receivePickerDetailNumber">-</strong></div>
                        <div><small>Supplier</small><strong id="receivePickerDetailSupplier">-</strong></div>
                        <div><small>Cabang</small><strong id="receivePickerDetailBranch">-</strong></div>
                        <div><small>Tanggal PO</small><strong id="receivePickerDetailDate">-</strong></div>
                        <div><small>Biaya Asuransi</small><strong id="receivePickerDetailInsurance">-</strong></div>
                        <div><small>Biaya Pengiriman</small><strong id="receivePickerDetailShipping">-</strong></div>
                        <div class="receive-po-picker-summary-total"><small>Total Estimasi</small><strong id="receivePickerDetailTotal">-</strong></div>
                    </div>
                    <div class="receive-po-picker-detail-note"><i class="mdi mdi-text-box-outline"></i>
                        <div><small>Catatan pembelian</small><p id="receivePickerDetailNotes">-</p></div>
                    </div>
                    <div class="receive-po-picker-results"><strong>Rincian obat dalam pembelian</strong><span>Periksa jumlah dan harga sebelum menerima barang.</span></div>
                    <div class="table-responsive purchase-table-wrap receive-po-picker-table-wrap">
                        <table class="table purchase-table align-middle receive-mobile-card-table">
                            <thead>
                                <tr>
                                    <th>Obat</th>
                                    <th>Satuan</th>
                                    <th>Qty Pembelian</th>
                                    <th>Qty dalam Penerimaan</th>
                                    <th>Sisa Qty</th>
                                    <th>Harga Estimasi</th>
                                    <th>Diskon 1 / 2 / 3</th>
                                    <th>PPN</th>
                                </tr>
                            </thead>
                            <tbody id="receivePickerDetailRows"></tbody>
                        </table>
                    </div>
                </section>
                <fieldset id="receivePoPickerInvoiceOptions" class="receive-invoice-options d-none"
                    aria-describedby="receivePoPickerInvoiceHint">
                    <legend>Bagaimana PO ini akan difakturkan?</legend>
                    <p id="receivePoPickerInvoiceHint">Pilih cara pencatatan faktur untuk PO terpilih.</p>
                    <div class="receive-invoice-option-grid">
                        <label class="receive-invoice-option" for="receiveInvoiceSingle">
                            <input type="radio" class="form-check-input" name="receive_invoice_mode"
                                id="receiveInvoiceSingle" value="single">
                            <span><strong><i class="mdi mdi-file-document-outline"></i> Satu faktur</strong>
                                <small>Catat seluruh sisa PO dalam satu faktur. Qty sisa akan terisi otomatis.</small></span>
                        </label>
                        <label class="receive-invoice-option" for="receiveInvoiceSplit">
                            <input type="radio" class="form-check-input" name="receive_invoice_mode"
                                id="receiveInvoiceSplit" value="split">
                            <span><strong><i class="mdi mdi-file-document-multiple-outline"></i> Pecah Faktur</strong>
                                <small>Isi barang dan qty untuk faktur ini. Sisa PO dapat dicatat pada faktur berikutnya, termasuk saat faktur sebelumnya masih draft.</small></span>
                        </label>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer receive-po-picker-footer">
                <div class="receive-po-picker-selection" id="receivePoPickerSelectionPanel">
                    <span class="receive-po-picker-selection-icon"><i class="mdi mdi-file-document-outline"></i></span>
                    <div><small>Pilihan pembelian</small><strong id="receivePoPickerSelection" aria-live="polite">Belum ada pembelian dipilih</strong></div>
                </div>
                <div class="receive-po-picker-footer-actions">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="receivePoPickerCreate" disabled>
                        <i class="mdi mdi-truck-check-outline"></i> Buat Penerimaan <i class="mdi mdi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
