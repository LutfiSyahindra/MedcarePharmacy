<div class="modal fade" id="obatModal" tabindex="-1" aria-labelledby="obatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="obatModalLabel">Tambah Obat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="obatForm">
                    @csrf
                    <div class="row g-3">

                        <!-- ======================= BAGIAN KIRI ======================= -->
                        <div class="col-md-6">
                            <input id="obatId" name="obatId" type="hidden">
                            <!-- Informasi Utama -->
                            <div class="card shadow-sm border-25px mb-3">
                                <div class="card-header bg-light fw-semibold">
                                    <i class="bi bi-capsule text-primary"></i> Informasi Obat
                                </div>
                                <div class="card-body">
                                    <!-- Kode Obat -->
                                    <div class="mb-3">
                                        <label class="form-label">Kode Obat</label>
                                        <input class="form-control" name="kode_obat" type="text"
                                            placeholder="Contoh: OBT0001">
                                    </div>

                                    <!-- Nama Obat -->
                                    <div class="mb-3">
                                        <label class="form-label">Nama Obat</label>
                                        <input class="form-control" name="nama_obat" type="text"
                                            placeholder="Contoh: Paracetamol 500mg">
                                    </div>

                                    <!-- Sediaan -->
                                    <div class="mb-3">
                                        <label class="form-label">Sediaan</label>
                                        <select class="js-example-basic-single form-select" data-width="100%"
                                            name="sediaan_id">
                                            <option value="">-- Pilih Sediaan --</option>
                                        </select>
                                    </div>

                                    <!-- Kategori Utama -->
                                    <div class="mb-3">
                                        <label class="form-label">Kategori Utama</label>
                                        <select class="js-example-basic-single form-select" data-width="100%"
                                            name="category_id" id="kategoriUtama">
                                            <option value="">-- Pilih Kategori Utama --</option>
                                        </select>
                                    </div>

                                    <div class="row g-2">
                                        <!-- Kategori -->
                                        <div class="col-md-6">
                                            <label class="form-label">Kategori</label>
                                            <select class="js-example-basic-single form-select" data-width="100%"
                                                name="main_category_id" id="kategori">
                                                <option value="">-- Pilih Kategori --</option>
                                            </select>
                                        </div>

                                        <!-- Sub Kategori -->
                                        <div class="col-md-6">
                                            <label class="form-label">Sub Kategori</label>
                                            <select class="js-example-basic-single form-select" data-width="100%"
                                                name="sub_kategori_id" id="subKategori">
                                                <option value="">-- Pilih Sub Kategori --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-2 mt-2">
                                        <!-- Golongan -->
                                        <div class="col-md-6">
                                            <label class="form-label">Golongan</label>
                                            <select class="js-example-basic-single form-select" data-width="100%"
                                                name="golongan_id">
                                                <option value="">-- Pilih Golongan --</option>
                                            </select>
                                        </div>

                                        <!-- Satuan -->
                                        <div class="col-md-6">
                                            <label class="form-label">Satuan</label>
                                            <select class="js-example-basic-single form-select" data-width="100%"
                                                name="satuan_id">
                                                <option value="">-- Pilih Satuan --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Komposisi dan Indikasi -->
                            <div class="card shadow-sm border-25px mb-3">
                                <div class="card-header bg-light fw-semibold">
                                    <i class="bi bi-info-circle text-primary"></i> Informasi Tambahan
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Komposisi</label>
                                        <input class="form-control" name="komposisi"
                                            placeholder="Contoh: Paracetamol 500mg">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Indikasi</label>
                                        <input class="form-control" name="indikasi"
                                            placeholder="Contoh: Penurun demam, pereda nyeri">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dosis</label>
                                        <input class="form-control" name="dosis"
                                            placeholder="Contoh: 3x sehari setelah makan">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Kemasan</label>
                                        <input class="form-control" name="kemasan"
                                            placeholder="Contoh: Strip isi 10 tablet">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ======================= BAGIAN KANAN ======================= -->
                        <div class="col-md-6">

                            <!-- Produksi & Penyimpanan -->
                            <div class="card shadow-sm border-25px mb-3">
                                <div class="card-header bg-light fw-semibold">
                                    <i class="bi bi-building text-primary"></i> Produksi & Penyimpanan
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Pabrikan</label>
                                        <select class="js-example-basic-single form-select" data-width="100%"
                                            name="pabrikan_id">
                                            <option value="">-- Pilih Pabrikan --</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Distributor</label>
                                        <select class="js-example-basic-single form-select" data-width="100%"
                                            name="distributor_id">
                                            <option value="">-- Pilih Distributor --</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Rak Penyimpanan</label>
                                        <select class="js-example-basic-single form-select" data-width="100%"
                                            name="rak_id">
                                            <option value="">-- Pilih Rak --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Stok & Harga -->
                            <div class="card shadow-sm border-25px mb-3">
                                <div class="card-header bg-light fw-semibold">
                                    <i class="bi bi-box-seam text-primary"></i> Stok & Harga
                                </div>
                                <div class="card-body">
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-12">
                                            <label class="form-label">Stok Minimum</label>
                                            <input class="form-control" name="stok_minimum" type="number"
                                                min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Harga Beli</label>
                                            <input class="form-control" name="harga_beli" type="number"
                                                step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Harga Jual</label>
                                            <input class="form-control" name="harga_jual" type="number"
                                                step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kadaluarsa & Status -->
                            <div class="card shadow-sm border-25px">
                                <div class="card-header bg-light fw-semibold">
                                    <i class="bi bi-calendar-event text-primary"></i>Status
                                </div>
                                <div class="card-body">
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Generik?</label>
                                            <select class="form-select" name="is_generik">
                                                <option value="1">Ya</option>
                                                <option value="0">Tidak</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Aktif?</label>
                                            <select class="form-select" name="is_active">
                                                <option value="1">Aktif</option>
                                                <option value="0">Nonaktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Tutup
                        </button>
                        <button type="submit" id="submitForm" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>
