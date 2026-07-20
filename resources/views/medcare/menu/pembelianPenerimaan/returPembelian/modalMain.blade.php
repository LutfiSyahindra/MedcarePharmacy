<div class="modal fade purchase-modal" id="returPembelianModal" tabindex="-1"
    aria-labelledby="returPembelianModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-keyboard-return"></i></span>
                    <div>
                        <h5 class="modal-title" id="returPembelianModalLabel">Form Retur Pembelian</h5>
                        <p class="modal-subtitle">Pilih penerimaan posted dan isi qty barang yang dikembalikan.</p>
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
                <form id="returPembelianForm">
                    @csrf
                    <input type="hidden" name="retur_pembelian_id" id="retur_pembelian_id">

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-document-outline"></i>
                                <div>
                                    <strong>Informasi Retur</strong>
                                    <small>Nomor retur, penerimaan asal, supplier, dan tanggal retur.</small>
                                </div>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Nomor Retur</label>
                                    <input type="text" class="form-control" name="nomor_retur" id="nomor_retur"
                                        readonly>
                                </div>
                                <div class="col-lg-5 col-md-6">
                                    <label class="form-label">Penerimaan Posted</label>
                                    <select class="form-select" name="penerimaan_barang_id" id="penerimaan_barang_id"
                                        data-width="100%" required>
                                        <option value="">-- Pilih Penerimaan --</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label">Supplier</label>
                                    <input type="text" class="form-control" id="return_supplier" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Nomor PO</label>
                                    <input type="text" class="form-control" id="return_no_po" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Nomor Faktur</label>
                                    <input type="text" class="form-control" id="return_nomor_faktur" readonly>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Ref Supplier</label>
                                    <input type="text" class="form-control" name="nomor_referensi_supplier"
                                        placeholder="Opsional">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Tanggal Retur</label>
                                    <div class="input-group flatpickr" id="return-date" data-wrap="true"
                                        data-click-opens="true">
                                        <input type="text" class="form-control" placeholder="Pilih tanggal"
                                            name="tanggal_retur" data-input required>
                                        <span class="input-group-text" data-toggle>
                                            <i class="mdi mdi-calendar-month-outline"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alasan Retur</label>
                                    <textarea class="form-control" name="alasan" rows="2"
                                        placeholder="Contoh: barang rusak, batch salah, atau retur ke supplier"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="catatan" rows="2"
                                        placeholder="Catatan tambahan"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-hand-coin-outline"></i>
                                <div>
                                    <strong>Rencana Ganti Rugi Supplier</strong>
                                    <small>Tentukan sejak awal apakah nilai retur ini harus ditagihkan kembali ke supplier.</small>
                                </div>
                            </div>
                        </div>
                        <div class="purchase-form-section-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-4">
                                    <label class="form-label">Apakah Akan Diganti Rugi?</label>
                                    <select class="form-select" name="expects_compensation" id="expects_compensation">
                                        <option value="1" selected>Ya, tagihkan senilai total retur</option>
                                        <option value="0">Tidak ada ganti rugi</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 compensation-plan-field">
                                    <label class="form-label">Batas Waktu</label>
                                    <input type="date" class="form-control" name="compensation_due_date"
                                        id="compensation_due_date">
                                </div>
                                <div class="col-lg-5">
                                    <label class="form-label">Catatan Kesepakatan / Alasan</label>
                                    <input type="text" class="form-control" name="compensation_notes"
                                        placeholder="Wajib diisi bila retur tidak ditagihkan">
                                </div>
                            </div>
                            <div class="compensation-plan-alert mt-3" id="compensationPlanHint">
                                <i class="mdi mdi-shield-alert-outline"></i>
                                <span>Setelah retur diposting, status akan tetap <strong>Menunggu</strong> sampai realisasi supplier dicatat penuh.</span>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section receive-po-summary d-none" id="returnReceiptSummary">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-search-outline"></i>
                                <div>
                                    <strong>Ringkasan Penerimaan</strong>
                                    <small>Sisa retur dihitung dari qty penerimaan dikurangi retur aktif.</small>
                                </div>
                            </div>
                        </div>
                        <div class="purchase-form-section-body">
                            <div class="receive-summary-grid">
                                <div><span>Penerimaan</span><strong id="summaryNomorPenerimaan">-</strong></div>
                                <div><span>Tanggal Terima</span><strong id="summaryTanggalTerima">-</strong></div>
                                <div><span>Branch</span><strong id="summaryReturnBranch">-</strong></div>
                                <div><span>Grand Total</span><strong id="summaryReturnGrandTotal">Rp 0</strong></div>
                                <div><span>Total Item</span><strong id="summaryReturnItemCount">0</strong></div>
                                <div><span>Sisa Qty Retur</span><strong id="summaryReturnableQty">0</strong></div>
                                <div><span>Qty Diisi</span><strong id="summaryReturnFilledQty">0</strong></div>
                                <div><span>Nilai Retur</span><strong id="summaryReturnValue">Rp 0</strong></div>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-pill-multiple"></i>
                                <div>
                                    <strong>Detail Barang Retur</strong>
                                    <small>Pilih satuan retur per item; stok keluar otomatis mengikuti konversi satuan stok.</small>
                                </div>
                            </div>
                            <div class="receive-detail-actions">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                                    <span id="returnLineCount">0</span> item
                                </span>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="fillAllReturnable">
                                    <i class="mdi mdi-format-list-checks"></i>
                                    Isi Semua Sisa
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllReturnQty">
                                    <i class="mdi mdi-broom"></i>
                                    Kosongkan Qty
                                </button>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div id="returnDetailEmpty" class="receive-empty-state">
                                <i class="mdi mdi-file-search-outline"></i>
                                <strong>Pilih penerimaan terlebih dahulu</strong>
                                <span>Item yang masih bisa diretur akan muncul di sini.</span>
                            </div>
                            <div class="table-responsive receive-detail-editor d-none" id="returnDetailEditor">
                                <table class="table purchase-detail-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th>Diterima / Sisa</th>
                                            <th>Batch</th>
                                            <th>Stok Batch</th>
                                            <th>Qty Retur</th>
                                            <th>Alasan Item</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="returnDetailRows"></tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <div class="purchase-total-panel">
                        <div class="purchase-total-copy">
                            <span>Total Retur</span>
                            <small>Nilai mengikuti harga, diskon, dan PPN penerimaan.</small>
                        </div>
                        <div class="purchase-total-stats">
                            <span><strong id="returnModalItemCount">0</strong> item</span>
                            <span>Qty <strong id="returnModalQtyCount">0</strong></span>
                            <span>Subtotal <strong id="returnModalSubtotal">Rp 0</strong></span>
                            <span>Diskon <strong id="returnModalDiscount">Rp 0</strong></span>
                            <span>PPN <strong id="returnModalTax">Rp 0</strong></span>
                        </div>
                        <strong id="returnGrandTotal">Rp 0</strong>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Batal
                        </button>
                        <button type="submit" id="submitReturPembelianForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
