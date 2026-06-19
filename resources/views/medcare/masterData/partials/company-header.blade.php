@php
    $companyTitle = $companyTitle ?? 'Master Perusahaan';
    $companyBreadcrumb = $companyBreadcrumb ?? $companyTitle;
    $companyKicker = $companyKicker ?? 'Company Master';
    $companyDescription = $companyDescription ?? 'Kelola data perusahaan terkait obat dengan cepat dan konsisten.';
    $companyIcon = $companyIcon ?? 'mdi-domain';
    $companyModalTarget = $companyModalTarget ?? '#companyModal';
    $companyImportTarget = $companyImportTarget ?? '#companyModalExcell';
    $companyActionLabel = $companyActionLabel ?? 'Tambah Data';
    $companyImportLabel = $companyImportLabel ?? 'Import Excel';
    $companyFlow = $companyFlow ?? [
        ['icon' => 'mdi-barcode-scan', 'title' => 'Kode', 'subtitle' => 'Identitas singkat data'],
        ['icon' => 'mdi-domain', 'title' => 'Profil', 'subtitle' => 'Nama dan alamat perusahaan'],
        ['icon' => 'mdi-card-account-phone-outline', 'title' => 'Kontak', 'subtitle' => 'Telepon dan email aktif'],
    ];
@endphp

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $companyBreadcrumb }}</li>
    </ol>
</nav>

<section class="company-hero">
    <div>
        <span class="company-kicker">
            <i class="mdi {{ $companyIcon }}"></i>
            {{ $companyKicker }}
        </span>
        <h2>{{ $companyTitle }}</h2>
        <p>{{ $companyDescription }}</p>
        <div class="company-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="{{ $companyModalTarget }}">
                <i class="mdi mdi-plus-circle-outline"></i>
                {{ $companyActionLabel }}
            </button>
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="{{ $companyImportTarget }}">
                <i class="mdi mdi-file-excel-outline"></i>
                {{ $companyImportLabel }}
            </button>
            <button type="button" class="btn btn-outline-light company-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="company-flow-panel" aria-label="Alur data perusahaan">
        @foreach ($companyFlow as $item)
            <div class="company-flow-item">
                <i class="mdi {{ $item['icon'] }}"></i>
                <div>
                    <span>{{ $item['title'] }}</span>
                    <small>{{ $item['subtitle'] }}</small>
                </div>
            </div>
        @endforeach
    </div>
</section>
