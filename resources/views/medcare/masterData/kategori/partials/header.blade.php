@php
    $categoryActive = $categoryActive ?? 'kategori';
    $categoryTitle = $categoryTitle ?? 'Kategori';
    $categoryDescription = $categoryDescription ?? 'Kelola kategori obat dengan cepat dan rapi.';
    $categoryModalTarget = $categoryModalTarget ?? '#kategoriUtamaModal';
    $categoryActionLabel = $categoryActionLabel ?? 'Tambah Data';
    $categoryActionIcon = $categoryActionIcon ?? 'mdi-plus-circle-outline';
    $categoryImportTarget = $categoryImportTarget ?? null;
    $categoryImportLabel = $categoryImportLabel ?? 'Import Excel';

    $categoryMenus = [
        [
            'key' => 'utama',
            'label' => 'Kategori',
            'icon' => 'mdi-tag-outline',
            'route' => route('kategori.kategoriUtama'),
        ],
    ];
@endphp

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item"><a href="#">Kategori</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $categoryTitle }}</li>
    </ol>
</nav>

<section class="category-hero">
    <div>
        <span class="category-kicker">
            <i class="mdi mdi-layers-triple-outline"></i>
            Category
        </span>
        <h2>{{ $categoryTitle }}</h2>
        <p>{{ $categoryDescription }}</p>
        <div class="category-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="{{ $categoryModalTarget }}">
                <i class="mdi {{ $categoryActionIcon }}"></i>
                {{ $categoryActionLabel }}
            </button>
            @if ($categoryImportTarget)
                <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="{{ $categoryImportTarget }}">
                    <i class="mdi mdi-file-excel-outline"></i>
                    {{ $categoryImportLabel }}
                </button>
            @endif
            <button type="button" class="btn btn-outline-light category-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="category-flow-panel" aria-label="Alur master kategori">
        <div class="category-flow-item {{ $categoryActive === 'utama' ? 'is-active' : '' }}">
            <i class="mdi mdi-tag-outline"></i>
            <div>
                <span>Kategori</span>
                <small>Pengelompokan utama obat</small>
            </div>
        </div>
    </div>
</section>

<div class="category-switcher" aria-label="Navigasi kategori">
    @foreach ($categoryMenus as $menu)
        <a href="{{ $menu['route'] }}" class="{{ $categoryActive === $menu['key'] ? 'is-active' : '' }}">
            <i class="mdi {{ $menu['icon'] }}"></i>
            {{ $menu['label'] }}
        </a>
    @endforeach
</div>
