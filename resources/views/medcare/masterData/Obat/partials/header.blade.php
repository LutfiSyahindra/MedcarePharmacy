<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item active" aria-current="page">Master Obat</li>
    </ol>
</nav>

<section class="obat-hero">
    <div>
        <span class="obat-kicker">
            <i class="mdi mdi-pill"></i>
            Medicine Catalog
        </span>
        <h2>Master Obat</h2>
        <p>Kelola katalog obat dari kode, kategori, golongan, satuan, pemasok, rak penyimpanan, stok minimum, harga beli, sampai status aktif dalam satu layar yang lebih mudah dipindai.</p>
        <div class="obat-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#obatModal">
                <i class="mdi mdi-plus-circle-outline"></i>
                Tambah Obat
            </button>
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#obatModalExcell">
                <i class="mdi mdi-file-excel-outline"></i>
                Import Excel
            </button>
            <button type="button" class="btn btn-outline-light obat-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="obat-flow-panel" aria-label="Alur master obat">
        <div class="obat-flow-item">
            <i class="mdi mdi-barcode-scan"></i>
            <div>
                <span>Identitas</span>
                <small>Kode, nama, sediaan, dan kemasan obat</small>
            </div>
        </div>
        <div class="obat-flow-item">
            <i class="mdi mdi-shape-outline"></i>
            <div>
                <span>Klasifikasi</span>
                <small>Kategori utama, kategori, sub kategori, dan golongan</small>
            </div>
        </div>
        <div class="obat-flow-item">
            <i class="mdi mdi-warehouse"></i>
            <div>
                <span>Operasional</span>
                <small>Pabrikan, distributor, rak, stok minimum, dan harga beli</small>
            </div>
        </div>
    </div>
</section>
