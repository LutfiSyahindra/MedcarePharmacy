<div class="modal fade purchase-modal" id="penerimaanModal" tabindex="-1" aria-labelledby="penerimaanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-truck-check-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="penerimaanModalLabel">Form Penerimaan Barang</h5>
                        <p class="modal-subtitle">Pilih PO approved lalu isi detail penerimaan fisik barang.</p>
                    </div>
                </div>
                <div class="purchase-modal-header-meta">
                    <span class="purchase-modal-status">
                        <i class="mdi mdi-file-clock-outline"></i>
                        Draft sebelum posting stok
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body">
                <form id="penerimaanForm">
                    @csrf
                    <input type="hidden" name="penerimaan_id" id="penerimaan_id">

                    <div class="purchase-modal-overview" aria-label="Ringkasan proses penerimaan">
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-shield-check-outline"></i></span>
                            <div>
                                <strong>PO Approved</strong>
                                <small>Dropdown hanya memuat PO yang sudah disetujui.</small>
                            </div>
                        </div>
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-barcode"></i></span>
                            <div>
                                <strong>Batch & Expired</strong>
                                <small>Wajib diisi untuk setiap item yang diterima.</small>
                            </div>
                        </div>
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-warehouse"></i></span>
                            <div>
                                <strong>Posting Stok</strong>
                                <small>Stok bertambah saat transaksi diposting.</small>
                            </div>
                        </div>
                    </div>

                    <div class="receive-form-progress" aria-label="Progress kelengkapan form">
                        <div class="receive-progress-step is-active" id="receiveStepPo">
                            <span>1</span>
                            <div>
                                <strong>Pilih PO</strong>
                                <small>Belum dipilih</small>
                            </div>
                        </div>
                        <div class="receive-progress-line"></div>
                        <div class="receive-progress-step" id="receiveStepInvoice">
                            <span>2</span>
                            <div>
                                <strong>Faktur</strong>
                                <small>Menunggu data</small>
                            </div>
                        </div>
                        <div class="receive-progress-line"></div>
                        <div class="receive-progress-step" id="receiveStepItems">
                            <span>3</span>
                            <div>
                                <strong>Item</strong>
                                <small>Belum ada qty</small>
                            </div>
                        </div>
                    </div>

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-document-outline"></i>
                                <div>
                                    <strong>Informasi Penerimaan</strong>
                                    <small>Nomor penerimaan, PO, faktur, dan tanggal transaksi.</small>
                                </div>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Nomor Penerimaan</label>
                                    <input type="text" class="form-control" name="nomor_penerimaan"
                                        id="nomor_penerimaan" readonly>
                                    <small class="purchase-field-hint">Dibuat otomatis per bulan.</small>
                                </div>
                                <div class="col-lg-5 col-md-6">
                                    <label class="form-label">Nomor PO Approved</label>
                                    <select class="form-select" name="purchase_order_id" id="purchase_order_id"
                                        data-width="100%" required>
                                        <option value="">-- Pilih PO --</option>
                                    </select>
                                    <small class="purchase-field-hint">PO yang sudah habis diterima tidak ditampilkan.</small>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Supplier</label>
                                    <input type="text" class="form-control" id="receive_supplier" readonly>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Nomor Faktur</label>
                                    <input type="text" class="form-control" name="nomor_faktur"
                                        placeholder="Contoh: INV/2026/001" required>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Nomor Surat Jalan</label>
                                    <input type="text" class="form-control" name="nomor_surat_jalan"
                                        placeholder="Opsional">
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Tanggal Penerimaan</label>
                                    <div class="input-group flatpickr" id="receive-date" data-wrap="true"
                                        data-click-opens="true">
                                        <input type="text" class="form-control" placeholder="Pilih tanggal"
                                            name="tanggal_penerimaan" data-input required>
                                        <span class="input-group-text" data-toggle>
                                            <i class="mdi mdi-calendar-month-outline"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="catatan" rows="2"
                                        placeholder="Catatan penerimaan, kondisi barang, atau instruksi khusus..."></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section receive-po-summary d-none" id="receivePoSummary">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-search-outline"></i>
                                <div>
                                    <strong>Ringkasan PO Terpilih</strong>
                                    <small>Gunakan sisa qty sebagai batas penerimaan.</small>
                                </div>
                            </div>
                        </div>
                        <div class="purchase-form-section-body">
                            <div class="receive-summary-grid">
                                <div><span>Nomor PO</span><strong id="summaryNoPo">-</strong></div>
                                <div><span>Tanggal PO</span><strong id="summaryTanggalPo">-</strong></div>
                                <div><span>Branch</span><strong id="summaryBranch">-</strong></div>
                                <div><span>Total Estimasi</span><strong id="summaryTotalEstimasi">Rp 0</strong></div>
                                <div><span>Total Item PO</span><strong id="summaryItemPo">0</strong></div>
                                <div><span>Sisa Qty PO</span><strong id="summaryOutstandingQty">0</strong></div>
                                <div><span>Qty Diisi</span><strong id="summaryFilledQty">0</strong></div>
                                <div><span>Progress Terima</span><strong id="summaryReceivePercent">0%</strong></div>
                            </div>
                            <div class="receive-progress-meter" aria-label="Progress qty diterima">
                                <span id="summaryReceiveMeter"></span>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-pill-multiple"></i>
                                <div>
                                    <strong>Detail Barang Diterima</strong>
                                    <small>Isi hanya qty yang benar-benar diterima. Baris qty 0 tidak disimpan.</small>
                                </div>
                            </div>
                            <div class="receive-detail-actions">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                    <span id="receiveLineCount">0</span> item PO
                                </span>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="fillAllOutstanding">
                                    <i class="mdi mdi-format-list-checks"></i>
                                    Terima Semua Sisa
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllQty">
                                    <i class="mdi mdi-broom"></i>
                                    Kosongkan Qty
                                </button>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div id="receiveDetailEmpty" class="receive-empty-state">
                                <i class="mdi mdi-file-search-outline"></i>
                                <strong>Pilih PO terlebih dahulu</strong>
                                <span>Detail obat akan muncul otomatis dari PO approved yang dipilih.</span>
                            </div>
                            <div class="table-responsive receive-detail-editor d-none" id="receiveDetailEditor">
                                <table class="table purchase-detail-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th>PO / Sisa</th>
                                            <th>Qty Diterima</th>
                                            <th>No Batch</th>
                                            <th>Expired Date</th>
                                            <th>Harga Beli</th>
                                            <th>Diskon %</th>
                                            <th>PPN %</th>
                                            <th>Subtotal</th>
                                            <th>Cek</th>
                                        </tr>
                                    </thead>
                                    <tbody id="receiveDetailRows"></tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <div class="purchase-total-panel">
                        <div class="purchase-total-copy">
                            <span>Total Penerimaan</span>
                            <small>Subtotal dihitung dari harga beli, diskon, dan PPN.</small>
                        </div>
                        <div class="purchase-total-stats">
                            <span><strong id="receiveModalItemCount">0</strong> item</span>
                            <span>Qty <strong id="receiveModalQtyCount">0</strong></span>
                            <span>Subtotal <strong id="receiveModalSubtotal">Rp 0</strong></span>
                            <span>Diskon <strong id="receiveModalDiscount">Rp 0</strong></span>
                            <span>PPN <strong id="receiveModalTax">Rp 0</strong></span>
                        </div>
                        <strong id="receiveGrandTotal">Rp 0</strong>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Batal
                        </button>
                        <button type="submit" id="submitPenerimaanForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
