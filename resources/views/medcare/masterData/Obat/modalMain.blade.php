<div class="modal fade obat-modal" id="obatModal" tabindex="-1" aria-labelledby="obatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-pill"></i></span>
                    <div>
                        <h5 class="modal-title mb-0" id="obatModalLabel">Tambah Master Obat</h5>
                        <p class="modal-subtitle" id="obatModalSubtitle">Lengkapi identitas, klasifikasi, pemasok, stok minimum, harga beli, dan status obat.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="obatForm">
                    @csrf
                    <input id="obatId" name="obatId" type="hidden">

                    <div class="obat-form-grid">
                        <section class="obat-form-section">
                            <div class="obat-section-header">
                                <i class="mdi mdi-card-account-details-outline"></i>
                                <div>
                                    <strong>Identitas Obat</strong>
                                    <small>Kode, nama, bentuk sediaan, dan kemasan.</small>
                                </div>
                            </div>
                            <div class="obat-section-body">
                                <div class="obat-fields">
                                    <div class="obat-field">
                                        <label class="form-label">Kode Obat</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-barcode-scan"></i></span>
                                            <input class="form-control" name="kode_obat" type="text" placeholder="OBT0001">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Nama Obat</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-pill"></i></span>
                                            <input class="form-control" name="nama_obat" type="text" placeholder="Paracetamol 500mg">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Sediaan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-bottle-tonic-outline"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih sediaan" name="sediaan_id">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Kemasan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-package-variant-closed"></i></span>
                                            <input class="form-control" name="kemasan" type="text" placeholder="Strip isi 10 tablet">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="obat-form-section">
                            <div class="obat-section-header">
                                <i class="mdi mdi-shape-outline"></i>
                                <div>
                                    <strong>Klasifikasi</strong>
                                    <small>Susun kategori dan hirarki golongan obat.</small>
                                </div>
                            </div>
                            <div class="obat-section-body">
                                <div class="obat-fields">
                                    <div class="obat-field">
                                        <label class="form-label">Kategori</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-folder-outline"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih kategori" name="category_id" id="kategori">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Golongan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-flask-outline"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih golongan" name="golongan_id" id="golongan">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Main Golongan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-shape-plus-outline"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih main golongan" name="main_golongan_id" id="mainGolongan">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Sub Golongan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-source-branch"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih sub golongan" name="sub_golongan_id" id="subGolongan">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field is-wide">
                                        <label class="form-label">Satuan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-scale-balance"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih satuan" name="satuan_id">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="obat-form-section">
                            <div class="obat-section-header">
                                <i class="mdi mdi-factory"></i>
                                <div>
                                    <strong>Produksi & Penyimpanan</strong>
                                    <small>Pabrikan, distributor, dan rak obat.</small>
                                </div>
                            </div>
                            <div class="obat-section-body">
                                <div class="obat-fields">
                                    <div class="obat-field">
                                        <label class="form-label">Pabrikan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-factory"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih pabrikan" name="pabrikan_id">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Distributor</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-truck-delivery-outline"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih distributor" name="distributor_id">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field is-wide">
                                        <label class="form-label">Rak Penyimpanan</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-archive-marker-outline"></i></span>
                                            <select class="form-select master-obat-select" data-width="100%" data-placeholder="Pilih rak penyimpanan" name="rak_id">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="obat-form-section">
                            <div class="obat-section-header">
                                <i class="mdi mdi-clipboard-pulse-outline"></i>
                                <div>
                                    <strong>Informasi Klinis</strong>
                                    <small>Catatan komposisi, indikasi, dan dosis.</small>
                                </div>
                            </div>
                            <div class="obat-section-body">
                                <div class="obat-fields">
                                    <div class="obat-field is-wide">
                                        <label class="form-label">Komposisi</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-test-tube"></i></span>
                                            <input class="form-control" name="komposisi" type="text" placeholder="Paracetamol 500mg">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field is-wide">
                                        <label class="form-label">Indikasi</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-heart-pulse"></i></span>
                                            <input class="form-control" name="indikasi" type="text" placeholder="Penurun demam, pereda nyeri">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field is-wide">
                                        <label class="form-label">Dosis</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-clock-outline"></i></span>
                                            <input class="form-control" name="dosis" type="text" placeholder="3x sehari setelah makan">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="obat-form-section">
                            <div class="obat-section-header">
                                <i class="mdi mdi-chart-box-outline"></i>
                                <div>
                                    <strong>Stok Minimum & Harga Beli</strong>
                                    <small>Ambang stok dan harga beli dasar obat.</small>
                                </div>
                            </div>
                            <div class="obat-section-body">
                                <div class="obat-fields">
                                    <div class="obat-field">
                                        <label class="form-label">Stok Minimum</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-bell-ring-outline"></i></span>
                                            <input class="form-control" name="stok_minimum" type="number" min="0" value="0">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Harga Beli</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-cash-minus"></i></span>
                                            <input class="form-control" name="harga_beli" type="number" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                </div>
                            </div>
                        </section>

                        <section class="obat-form-section">
                            <div class="obat-section-header">
                                <i class="mdi mdi-toggle-switch-outline"></i>
                                <div>
                                    <strong>Status</strong>
                                    <small>Tentukan jenis obat dan status aktifnya.</small>
                                </div>
                            </div>
                            <div class="obat-section-body">
                                <div class="obat-fields">
                                    <div class="obat-field">
                                        <label class="form-label">Jenis Obat</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-certificate-outline"></i></span>
                                            <select class="form-select" name="is_generik">
                                                <option value="1">Generik</option>
                                                <option value="0">Paten</option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="obat-field">
                                        <label class="form-label">Status Data</label>
                                        <div class="obat-input-shell">
                                            <span class="obat-input-icon"><i class="mdi mdi-check-decagram-outline"></i></span>
                                            <select class="form-select" name="is_active">
                                                <option value="1">Aktif</option>
                                                <option value="0">Nonaktif</option>
                                            </select>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline"></i>
                            Tutup
                        </button>
                        <button type="submit" id="submitObatForm" class="btn btn-primary">
                            <i class="mdi mdi-content-save-outline"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
