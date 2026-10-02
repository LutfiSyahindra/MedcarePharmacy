<div class="modal fade purchase-modal" id="pembelianModal" tabindex="-1" aria-labelledby="pembelianModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
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
                                <strong id="purchaseNumberModeTitle">Nomor Otomatis</strong>
                                <small id="purchaseNumberModeDescription">PO dibuat sesuai urutan bulan berjalan.</small>
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
                                            placeholder="Pilih distributor terlebih dahulu" maxlength="50" readonly required>
                                    </div>
                                    <small class="purchase-field-hint" id="purchaseNumberHint">Nomor PO mengikuti pengaturan distributor.</small>
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
                                    <small>Tambahkan obat, satuan, jumlah, harga, dan tiga diskon bertingkatnya.</small>
                                </div>
                            </div>
                            <div class="purchase-detail-actions">
                                <button type="button" id="toggleMedicinePicker"
                                    class="btn btn-primary btn-sm purchase-add-detail" aria-expanded="false"
                                    aria-controls="medicinePicker">
                                    <i class="mdi mdi-checkbox-multiple-marked-outline"></i>
                                    Pilih Banyak Obat
                                </button>
                                <button type="button" id="addDetail"
                                    class="btn btn-outline-success btn-sm purchase-add-detail">
                                    <i class="mdi mdi-plus-circle-outline"></i>
                                    Tambah Baris Manual
                                </button>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div id="medicinePicker" class="purchase-medicine-picker d-none" aria-hidden="true">
                                <div class="purchase-medicine-picker-head">
                                    <div>
                                        <strong><i class="mdi mdi-format-list-checks"></i> Pilih Item Obat</strong>
                                        <small>Cari lalu centang beberapa obat untuk dimasukkan sekaligus ke rincian PO. Stok mengikuti branch PO.</small>
                                    </div>
                                    <button type="button" id="closeMedicinePicker"
                                        class="btn btn-sm btn-light purchase-picker-close" aria-label="Tutup daftar obat"
                                        title="Tutup daftar obat">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </div>

                                <div class="purchase-medicine-picker-toolbar">
                                    <div class="purchase-picker-search">
                                        <i class="mdi mdi-magnify"></i>
                                        <input type="search" id="medicinePickerSearch"
                                            placeholder="Cari kode, nama, atau satuan obat..." autocomplete="off"
                                            aria-label="Cari item obat">
                                        <button type="button" id="clearMedicinePickerSearch"
                                            aria-label="Hapus pencarian obat" title="Hapus pencarian">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                    </div>
                                    <div class="purchase-picker-selection">
                                        <label class="purchase-picker-select-all" for="selectAllVisibleMedicines">
                                            <input type="checkbox" class="form-check-input"
                                                id="selectAllVisibleMedicines"
                                                aria-label="Pilih semua hasil pencarian">
                                            <span>Pilih semua hasil</span>
                                        </label>
                                        <span id="medicinePickerResultCount" class="purchase-picker-result-count">
                                            Memuat daftar obat...
                                        </span>
                                    </div>
                                </div>

                                <div class="purchase-picker-table-wrap">
                                    <table class="table table-hover align-middle purchase-picker-table">
                                        <thead>
                                            <tr>
                                                <th class="purchase-picker-check-cell">Pilih</th>
                                                <th>Kode</th>
                                                <th>Nama Obat</th>
                                                <th>Satuan Dasar</th>
                                                <th class="text-end">Stok Saat Ini</th>
                                                <th class="text-end">Harga Beli</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="medicinePickerList">
                                            <tr>
                                                <td colspan="7" class="purchase-picker-state">
                                                    <i class="mdi mdi-loading mdi-spin"></i> Memuat daftar obat...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="purchase-medicine-picker-footer">
                                    <span>
                                        <strong id="medicinePickerSelectedCount">0</strong> obat dipilih
                                    </span>
                                    <button type="button" id="addSelectedMedicines" class="btn btn-primary btn-sm"
                                        disabled>
                                        <i class="mdi mdi-playlist-plus"></i>
                                        Masukkan ke Rincian
                                    </button>
                                </div>
                            </div>

                            <div class="purchase-detail-summary">
                                <span><i class="mdi mdi-format-list-numbered"></i> <strong
                                        id="purchaseModalLineCount">1</strong> baris obat</span>
                                <span><i class="mdi mdi-package-variant-closed"></i> <strong
                                        id="purchaseModalQtyCount">1</strong> total qty</span>
                                <span id="purchaseUnitLoadingNotice" class="d-none">
                                    <i class="mdi mdi-loading mdi-spin"></i> Memuat satuan obat...
                                </span>
                            </div>
                            <div id="detail-wrapper">
                                <div class="detail-item purchase-detail-card">
                                    <div class="purchase-detail-card-head">
                                        <div>
                                            <span class="purchase-detail-number">1</span>
                                            <strong>Item Obat</strong>
                                            <small>Pilih obat, satuan, qty, harga per satuan, Diskon 1–3, dan PPN.</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-lg-4 col-md-6">
                                            <label class="form-label">Obat</label>
                                            <select class="js-example-basic-single form-select obat-select"
                                                data-width="100%" name="obat_id[]" required>
                                            </select>
                                            <div class="purchase-current-stock is-empty" aria-live="polite">
                                                <i class="mdi mdi-archive-outline"></i>
                                                <span>Pilih obat untuk melihat stok saat ini</span>
                                            </div>
                                        </div>

                                        <div class="col-8 col-lg-3 col-md-6 purchase-unit-field">
                                            <label class="form-label">Satuan</label>
                                            <select class="form-select satuan-select" name="satuan_id[]" disabled
                                                required>
                                                <option value="">-- Pilih Satuan --</option>
                                            </select>
                                        </div>

                                        <div class="col-4 col-lg-1 col-md-4 purchase-qty-field">
                                            <label class="form-label">Qty</label>
                                            <input type="number" class="form-control qty" name="qty[]" min="1"
                                                value="1" required>
                                        </div>

                                        <div class="col-6 col-lg-2 col-md-4 purchase-price-field">
                                            <label class="form-label">Harga / Satuan</label>
                                            <input type="number" class="form-control harga_estimasi_satuan"
                                                name="harga_estimasi_satuan[]" min="0" step="any" value="0">
                                            <input type="hidden" class="harga_estimasi" name="harga_estimasi[]"
                                                value="0">
                                        </div>

                                        <div class="col-6 col-lg-2 col-md-4 purchase-subtotal-field">
                                            <label class="form-label">Subtotal + PPN</label>
                                            <input type="number" class="form-control subtotal" name="subtotal[]"
                                                readonly>
                                        </div>

                                        <div class="col-6 col-lg-3 col-md-6">
                                            <label class="form-label">Diskon 1 (%)</label>
                                            <input type="number" class="form-control purchase-discount"
                                                name="diskon_1[]" min="0" max="100" step="0.01" value="0">
                                        </div>

                                        <div class="col-6 col-lg-3 col-md-6">
                                            <label class="form-label">Diskon 2 (%)</label>
                                            <input type="number" class="form-control purchase-discount"
                                                name="diskon_2[]" min="0" max="100" step="0.01" value="0">
                                        </div>

                                        <div class="col-6 col-lg-3 col-md-6">
                                            <label class="form-label">Diskon 3 (%)</label>
                                            <input type="number" class="form-control purchase-discount"
                                                name="diskon_3[]" min="0" max="100" step="0.01" value="0">
                                        </div>

                                        <div class="col-6 col-lg-3 col-md-6">
                                            <label class="form-label">PPN (%)</label>
                                            <input type="number" class="form-control purchase-tax"
                                                name="ppn[]" min="0" max="100" step="0.01" value="11" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="purchase-form-section">
                        <div class="purchase-form-section-header">
                            <div class="purchase-form-section-title">
                                <i class="mdi mdi-truck-delivery-outline"></i>
                                <div>
                                    <strong>Biaya Tambahan</strong>
                                    <small>Isi biaya asuransi dan pengiriman bila dibebankan pada PO.</small>
                                </div>
                            </div>
                        </div>

                        <div class="purchase-form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Biaya Asuransi <span class="text-muted">(Opsional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control purchase-additional-cost"
                                            name="biaya_asuransi" min="0" max="9999999999999.99" step="0.01"
                                            value="0">
                                    </div>
                                    <small class="purchase-field-hint">Biaya perlindungan barang selama proses pengiriman.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Biaya Pengiriman <span class="text-muted">(Opsional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control purchase-additional-cost"
                                            name="biaya_pengiriman" min="0" max="9999999999999.99" step="0.01"
                                            value="0">
                                    </div>
                                    <small class="purchase-field-hint">Ongkos kirim atau biaya logistik dari distributor.</small>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="purchase-total-panel">
                        <div class="purchase-total-copy">
                            <span>Total Estimasi Purchase Order</span>
                            <small>Subtotal obat setelah diskon dan PPN, ditambah biaya asuransi dan pengiriman.</small>
                        </div>
                        <div class="purchase-total-stats">
                            <span><strong id="purchaseModalItemCount">1</strong> item</span>
                            <span>Subtotal obat <strong id="purchaseModalMedicineSubtotal">Rp 0</strong></span>
                            <span>Biaya tambahan <strong id="purchaseModalAdditionalCost">Rp 0</strong></span>
                        </div>
                        <strong id="total_estimasi">Rp 0</strong>
                        <input type="hidden" name="total_estimasi" id="total_estimasi_input" value="0">
                    </div>
                </form>
            </div>

            <div class="modal-footer purchase-form-footer">
                <div class="purchase-mobile-total" role="status" aria-live="polite" aria-atomic="true">
                    <span>Total Estimasi</span>
                    <strong id="purchaseMobileTotal">Rp 0</strong>
                </div>
                <div class="purchase-save-actions">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="mdi mdi-close-circle-outline"></i>
                        Tutup
                    </button>
                    <button type="button" id="saveDraftForm" class="btn btn-outline-primary">
                        <i class="mdi mdi-content-save-move-outline"></i>
                        Simpan sebagai Draft
                    </button>
                    <button type="submit" id="submitForm" class="btn btn-primary" form="pembelianForm">
                        <i class="mdi mdi-content-save-outline"></i>
                        Simpan Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
