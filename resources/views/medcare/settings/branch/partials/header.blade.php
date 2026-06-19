@php
    $branchItems = [
        [
            "key" => "branch",
            "label" => "Branch",
            "icon" => "mdi-source-branch",
            "route" => route("branch.index"),
        ],
        [
            "key" => "assign",
            "label" => "Assign Branch",
            "icon" => "mdi-account-switch-outline",
            "route" => route("assignBranch.assignBranch"),
        ],
    ];
@endphp

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Settings</a></li>
        <li class="breadcrumb-item"><a href="#">Branch</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $branchTitle }}</li>
    </ol>
</nav>

<section class="branch-hero">
    <div>
        <span class="branch-kicker">
            <i class="mdi mdi-map-marker-radius-outline"></i>
            Branch Control
        </span>
        <h2>{{ $branchTitle }}</h2>
        <p>{{ $branchDescription }}</p>
        <div class="branch-hero-actions">
            @if (!empty($branchModalTarget))
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="{{ $branchModalTarget }}">
                    <i class="mdi {{ $branchActionIcon }}"></i>
                    {{ $branchActionLabel }}
                </button>
            @endif
            <button type="button" class="btn btn-outline-light branch-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="branch-flow-panel" aria-label="Alur manajemen branch">
        <div class="branch-flow-item">
            <i class="mdi mdi-storefront-outline"></i>
            <div>
                <span>Branch</span>
                <small>Data cabang dan kontak</small>
            </div>
        </div>
        <div class="branch-flow-item">
            <i class="mdi mdi-toggle-switch-outline"></i>
            <div>
                <span>Status</span>
                <small>Aktifkan cabang yang beroperasi</small>
            </div>
        </div>
        <div class="branch-flow-item">
            <i class="mdi mdi-account-multiple-check-outline"></i>
            <div>
                <span>Assign</span>
                <small>Hubungkan user ke cabang</small>
            </div>
        </div>
    </div>
</section>

<div class="branch-switcher" role="navigation" aria-label="Navigasi branch">
    @foreach ($branchItems as $item)
        <a href="{{ $item["route"] }}" class="{{ $branchActive === $item["key"] ? "is-active" : "" }}">
            <i class="mdi {{ $item["icon"] }}"></i>
            {{ $item["label"] }}
        </a>
    @endforeach
</div>
