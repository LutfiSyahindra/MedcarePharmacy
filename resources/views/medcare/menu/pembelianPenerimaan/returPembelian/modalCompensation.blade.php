<div class="modal fade purchase-modal" id="returCompensationModal" tabindex="-1"
    aria-labelledby="returCompensationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-hand-coin-outline"></i></span>
                    <div>
                        <h5 class="modal-title" id="returCompensationModalLabel">Ganti Rugi Supplier</h5>
                        <p class="modal-subtitle" id="compensationDocumentLabel">Pantau kewajiban supplier sampai lunas.</p>
                    </div>
                </div>
                <span class="compensation-status is-waiting" id="compensationModalStatus">Menunggu</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="compensation-summary-grid mb-4">
                    <div><span>Nilai Retur</span><strong id="compensationExpectedValue">Rp 0</strong></div>
                    <div><span>Sudah Diganti</span><strong id="compensationReceivedValue">Rp 0</strong></div>
                    <div class="is-outstanding"><span>Sisa Belum Diganti</span><strong id="compensationOutstandingValue">Rp 0</strong></div>
                    <div><span>Batas Waktu</span><strong id="compensationDueDateLabel">-</strong></div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <section class="purchase-form-section h-100">
                            <div class="purchase-form-section-header">
                                <div class="purchase-form-section-title">
                                    <i class="mdi mdi-clipboard-edit-outline"></i>
                                    <div>
                                        <strong>Rencana Penagihan</strong>
                                        <small>Bisa diperbarui jika kesepakatan supplier berubah.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="purchase-form-section-body">
                                <form id="compensationPlanForm">
                                    <input type="hidden" id="compensationReturnId">
                                    <div class="mb-3">
                                        <label class="form-label">Status Penagihan</label>
                                        <select class="form-select" name="expects_compensation" id="planExpectsCompensation">
                                            <option value="1">Akan diganti rugi supplier</option>
                                            <option value="0">Tidak ada ganti rugi</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 compensation-plan-field">
                                        <label class="form-label">Batas Waktu</label>
                                        <input type="date" class="form-control" name="compensation_due_date"
                                            id="planCompensationDueDate">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Catatan Kesepakatan / Alasan</label>
                                        <textarea class="form-control" name="compensation_notes" id="planCompensationNotes" rows="3"
                                            placeholder="Wajib diisi bila retur tidak ditagihkan"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="mdi mdi-content-save-outline"></i> Simpan Rencana
                                    </button>
                                </form>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-7">
                        <section class="purchase-form-section h-100" id="compensationEntrySection">
                            <div class="purchase-form-section-header">
                                <div class="purchase-form-section-title">
                                    <i class="mdi mdi-cash-check"></i>
                                    <div>
                                        <strong>Catat Realisasi</strong>
                                        <small>Nilai barang pengganti juga dicatat dalam rupiah agar dapat direkonsiliasi.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="purchase-form-section-body">
                                <form id="compensationEntryForm">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label">Bentuk Ganti Rugi</label>
                                            <select class="form-select" name="jenis" required>
                                                <option value="barang_pengganti">Barang pengganti</option>
                                                <option value="potongan_faktur">Potongan faktur berikutnya</option>
                                                <option value="nota_kredit">Nota kredit</option>
                                                <option value="transfer_bank">Transfer bank</option>
                                                <option value="refund_tunai">Refund tunai</option>
                                                <option value="lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Tanggal Realisasi</label>
                                            <input type="date" class="form-control" name="tanggal_realisasi"
                                                id="compensationRealizationDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Nilai Realisasi</label>
                                            <input type="number" class="form-control" name="nominal" id="compensationNominal"
                                                min="0.01" step="0.01" required>
                                            <small class="text-muted">Maks. <span id="compensationNominalMax">Rp 0</span></small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nomor Referensi</label>
                                            <input type="text" class="form-control" name="nomor_referensi"
                                                placeholder="Nota kredit / bukti transfer / surat jalan">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nomor Faktur Terkait</label>
                                            <input type="text" class="form-control" name="nomor_faktur"
                                                placeholder="Jika berupa potongan faktur">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Keterangan</label>
                                            <textarea class="form-control" name="keterangan" rows="2"
                                                placeholder="Rincian barang pengganti atau informasi lain"></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mt-3" id="submitCompensationEntry">
                                        <i class="mdi mdi-check-circle-outline"></i> Catat Realisasi Supplier
                                    </button>
                                    <small class="d-block text-muted mt-2">
                                        <i class="mdi mdi-information-outline"></i>
                                        Pencatatan ini tidak menambah stok. Barang pengganti tetap harus diterima melalui proses stok yang berlaku.
                                    </small>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>

                <section class="purchase-form-section mt-4">
                    <div class="purchase-form-section-header">
                        <div class="purchase-form-section-title">
                            <i class="mdi mdi-history"></i>
                            <div>
                                <strong>Riwayat Realisasi</strong>
                                <small>Catatan aktif dan pembatalan tetap terlihat sebagai jejak audit.</small>
                            </div>
                        </div>
                    </div>
                    <div class="purchase-form-section-body">
                        <div id="compensationHistoryEmpty" class="compensation-empty">
                            <i class="mdi mdi-clock-alert-outline"></i>
                            <span>Belum ada realisasi dari supplier.</span>
                        </div>
                        <div class="table-responsive d-none" id="compensationHistoryWrap">
                            <table class="table purchase-detail-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Bentuk</th>
                                        <th>Referensi</th>
                                        <th>Nilai</th>
                                        <th>Dicatat Oleh</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="compensationHistoryRows"></tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
