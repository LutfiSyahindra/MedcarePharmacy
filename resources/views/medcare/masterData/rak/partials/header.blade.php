<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item active" aria-current="page">Rak Penyimpanan</li>
    </ol>
</nav>

<section class="rak-hero">
    <div>
        <span class="rak-kicker">
            <i class="mdi mdi-archive-marker-outline"></i>
            Storage Rack
        </span>
        <h2>Rak Penyimpanan Obat</h2>
        <p>Kelola kode rak, nama rak, dan lokasi fisik penyimpanan agar stok obat lebih mudah ditelusuri dari master data sampai operasional gudang.</p>
        <div class="rak-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#rakModal">
                <i class="mdi mdi-plus-circle-outline"></i>
                Tambah Rak
            </button>
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#rakModalExcell">
                <i class="mdi mdi-file-excel-outline"></i>
                Import Excel
            </button>
            <button type="button" class="btn btn-outline-light rak-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="rak-map-panel" aria-label="Pemetaan rak penyimpanan">
        <div class="rak-map-item">
            <i class="mdi mdi-barcode-scan"></i>
            <div>
                <span>Kode Rak</span>
                <small>Identitas cepat untuk pencarian stok</small>
            </div>
        </div>
        <div class="rak-map-item">
            <i class="mdi mdi-archive-outline"></i>
            <div>
                <span>Nama Rak</span>
                <small>Nama penyimpanan yang mudah dikenali</small>
            </div>
        </div>
        <div class="rak-map-item">
            <i class="mdi mdi-map-marker-radius-outline"></i>
            <div>
                <span>Lokasi</span>
                <small>Gudang, area, lorong, atau detail tempat</small>
            </div>
        </div>
    </div>
</section>
