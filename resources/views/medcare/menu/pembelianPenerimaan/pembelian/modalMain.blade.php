<div class="modal fade purchase-modal" id="pembelianModal" tabindex="-1" aria-labelledby="pembelianModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-cart-plus"></i></span>
                    <div>
                        <h5 class="modal-title" id="pembelianModalLabel">Form Purchase Order</h5>
                        <p class="modal-subtitle">Lengkapi informasi PO dan rincian obat yang akan dibeli.</p>
                    </div>
                </div>
                <div class="purchase-modal-header-meta">
                    <span class="purchase-modal-status">
                        <i class="mdi mdi-shield-check-outline"></i>
                        Approval Admin
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body">
                <form id="pembelianForm">
                    @csrf

                    <input type="hidden" class="form-control" name="pembelian_id" id="pembelian_id">

                    <div class="purchase-modal-overview" aria-label="Ringkasan proses purchase order">
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-identifier"></i></span>
                            <div>
                                <strong>Nomor Otomatis</strong>
                                <small>PO dibuat sesuai urutan bulan berjalan.</small>
                            </div>
                        </div>
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-account-check-outline"></i></span>
                            <div>
                                <strong>Menunggu Admin</strong>
                                <small>User non-admin masuk ke antrean approval.</small>
                            </div>
                        </div>
                        <div class="purchase-overview-item">
                            <span><i class="mdi mdi-calculator-variant-outline"></i></span>
                            <div>
                                <strong>Total Realtime</strong>
                                <small>Subtotal dan estimasi dihitung otomatis.</small>
                            </div>
                        </div>
                    </div>

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-file-document-outline"></i>
                                <div>
                                    <strong>Informasi Purchase Order</strong>
                                    <small>Tentukan distributor, tanggal, dan catatan transaksi.</small>
                                </div>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nomor PO</label>
                                    <div class="purchase-input-icon">
                                        <i class="mdi mdi-file-document-edit-outline"></i>
                                        <input type="text" class="form-control" name="no_po"
                                            placeholder="Nomor dibuat otomatis" readonly>
                                    </div>
                                    <small class="purchase-field-hint">Terisi otomatis saat modal tambah dibuka.</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Distributor</label>
                                    <select class="js-example-basic-single form-select" data-width="100%"
                                        name="distributor_id" id="distributor_id" required>
                                        <option value="">-- Pilih Distributor --</option>
                                    </select>
                                    <small class="purchase-field-hint">Pilih pemasok utama untuk PO ini.</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Tanggal PO</label>
                                    <div class="input-group flatpickr" id="flatpickr-date" data-wrap="true"
                                        data-click-opens="true">
                                        <input type="text" class="form-control" placeholder="Pilih tanggal"
                                            name="tanggal" data-input>
                                        <span class="input-group-text" data-toggle>
                                            <i class="mdi mdi-calendar-month-outline"></i>
                                        </span>
                                    </div>
                                    <small class="purchase-field-hint">Gunakan tanggal rencana pembelian.</small>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" name="catatan" rows="2"
                                        placeholder="Tambahkan instruksi atau catatan untuk purchase order ini..."></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-pill-multiple"></i>
                                <div>
                                    <strong>Rincian Obat</strong>
                                    <small>Tambahkan obat, satuan, jumlah, dan harga estimasinya.</small>
                                </div>
                            </div>
                            <button type="button" id="addDetail" class="btn btn-success btn-sm purchase-add-detail">
                                <i class="mdi mdi-plus-circle-outline"></i>
                                Tambah Obat
                            </button>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="purchase-detail-summary">
                                <span><i class="mdi mdi-format-list-numbered"></i> <strong
                                        id="purchaseModalLineCount">1</strong> baris obat</span>
                                <span><i class="mdi mdi-package-variant-closed"></i> <strong
                                        id="purchaseModalQtyCount">1</strong> total qty</span>
                            </div>
                            <div id="detail-wrapper">
                                <div class="detail-item purchase-detail-card">
                                    <div class="purchase-detail-card-head">
                                        <div>
                                            <span class="purchase-detail-number">1</span>
                                            <strong>Item Obat</strong>
                                            <small>Pilih obat, satuan, qty, dan harga estimasi.</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="row g-3 align-items-end">
                                        <div class="col-lg-4 col-md-6">
                                            <label class="form-label">Obat</label>
                                            <select class="js-example-basic-single form-select obat-select"
                                                data-width="100%" name="obat_id[]" required>
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label">Satuan</label>
                                            <select class="form-select satuan-select" name="satuan_id[]" disabled
                                                required>
                                                <option value="">-- Pilih Satuan --</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label">Qty</label>
                                            <input type="number" class="form-control qty" name="qty[]" min="1"
                                                value="1" required>
                                        </div>

                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label">Harga Estimasi</label>
                                            <input type="number" class="form-control harga_estimasi"
                                                name="harga_estimasi[]" min="0" step="0.01" value="0">
                                        </div>

                                        <div class="col-lg-2 col-md-4">
                                            <label class="form-label">Subtotal</label>
                                            <input type="number" class="form-control subtotal" name="subtotal[]"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="purchase-total-panel">
                        <div class="purchase-total-copy">
                            <span>Total Estimasi Purchase Order</span>
                            <small>Nilai diperbarui otomatis dari seluruh rincian obat.</small>
                        </div>
                        <div class="purchase-total-stats">
                            <span><strong id="purchaseModalItemCount">1</strong> item</span>
                            <span>Rata-rata <strong id="purchaseModalAverage">Rp 0</strong></span>
                        </div>
                        <strong id="total_estimasi">Rp 0</strong>
                        <input type="hidden" name="total_estimasi" id="total_estimasi_input" value="0">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Batal
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan Purchase Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
