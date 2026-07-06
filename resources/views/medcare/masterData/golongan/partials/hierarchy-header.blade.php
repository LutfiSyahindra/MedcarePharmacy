@php
    $golonganActive = $golonganActive ?? 'root';
    $golonganTitle = $golonganTitle ?? 'Golongan';
    $golonganDescription = $golonganDescription ?? 'Kelola struktur golongan obat.';
    $golonganModalTarget = $golonganModalTarget ?? '#golonganModal';
    $golonganActionLabel = $golonganActionLabel ?? 'Tambah Data';
    $golonganImportTarget = $golonganImportTarget ?? null;
    $golonganImportLabel = $golonganImportLabel ?? 'Import Excel';

    $golonganMenus = [
        [
            'key' => 'root',
            'label' => 'Golongan',
            'icon' => 'mdi-shape-outline',
            'route' => route('golongan.golongan'),
        ],
        [
            'key' => 'main',
            'label' => 'Main Golongan',
            'icon' => 'mdi-shape-plus-outline',
            'route' => route('golongan.mainGolongan'),
        ],
        [
            'key' => 'sub',
            'label' => 'Sub Golongan',
            'icon' => 'mdi-format-list-bulleted-type',
            'route' => route('golongan.subGolongan'),
        ],
    ];
@endphp

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item"><a href="#">Golongan</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $golonganTitle }}</li>
    </ol>
</nav>

<section class="category-hero">
    <div>
        <span class="category-kicker">
            <i class="mdi mdi-layers-triple-outline"></i>
            Drug Class Tree
        </span>
        <h2>{{ $golonganTitle }}</h2>
        <p>{{ $golonganDescription }}</p>
        <div class="category-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="{{ $golonganModalTarget }}">
                <i class="mdi mdi-plus-circle-outline"></i>
                {{ $golonganActionLabel }}
            </button>
            @if ($golonganImportTarget)
                <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="{{ $golonganImportTarget }}">
                    <i class="mdi mdi-file-excel-outline"></i>
                    {{ $golonganImportLabel }}
                </button>
            @endif
            <button type="button" class="btn btn-outline-light category-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="category-flow-panel" aria-label="Alur master golongan">
        <div class="category-flow-item {{ $golonganActive === 'root' ? 'is-active' : '' }}">
            <i class="mdi mdi-shape-outline"></i>
            <div>
                <span>Golongan</span>
                <small>Level dasar klasifikasi obat</small>
            </div>
        </div>
        <div class="category-flow-item {{ $golonganActive === 'main' ? 'is-active' : '' }}">
            <i class="mdi mdi-shape-plus-outline"></i>
            <div>
                <span>Main Golongan</span>
                <small>Turunan dari golongan</small>
            </div>
        </div>
        <div class="category-flow-item {{ $golonganActive === 'sub' ? 'is-active' : '' }}">
            <i class="mdi mdi-format-list-bulleted-type"></i>
            <div>
                <span>Sub Golongan</span>
                <small>Detail untuk master obat</small>
            </div>
        </div>
    </div>
</section>

<div class="category-switcher" aria-label="Navigasi golongan">
    @foreach ($golonganMenus as $menu)
        <a href="{{ $menu['route'] }}" class="{{ $golonganActive === $menu['key'] ? 'is-active' : '' }}">
            <i class="mdi {{ $menu['icon'] }}"></i>
            {{ $menu['label'] }}
        </a>
    @endforeach
</div>
