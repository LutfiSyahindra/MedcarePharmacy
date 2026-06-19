@php
    $authItems = [
        [
            "key" => "users",
            "label" => "Users",
            "icon" => "mdi-account-group-outline",
            "route" => route("users.index"),
        ],
        [
            "key" => "roles",
            "label" => "Role",
            "icon" => "mdi-account-key-outline",
            "route" => route("roles.role"),
        ],
        [
            "key" => "permission",
            "label" => "Permission",
            "icon" => "mdi-shield-key-outline",
            "route" => route("permissions.permissions"),
        ],
    ];
@endphp

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Settings</a></li>
        <li class="breadcrumb-item"><a href="#">Auth</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $authTitle }}</li>
    </ol>
</nav>

<section class="auth-hero">
    <div>
        <span class="auth-kicker">
            <i class="mdi mdi-shield-check-outline"></i>
            Access Control
        </span>
        <h2>{{ $authTitle }}</h2>
        <p>{{ $authDescription }}</p>
        <div class="auth-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="{{ $authModalTarget }}">
                <i class="mdi {{ $authActionIcon }}"></i>
                {{ $authActionLabel }}
            </button>
            <button type="button" class="btn btn-outline-light auth-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="auth-flow-panel" aria-label="Alur manajemen akses">
        <div class="auth-flow-item">
            <i class="mdi mdi-account-outline"></i>
            <div>
                <span>Users</span>
                <small>Identitas dan status akun</small>
            </div>
        </div>
        <div class="auth-flow-item">
            <i class="mdi mdi-account-key-outline"></i>
            <div>
                <span>Role</span>
                <small>Kelompok akses kerja</small>
            </div>
        </div>
        <div class="auth-flow-item">
            <i class="mdi mdi-shield-key-outline"></i>
            <div>
                <span>Permission</span>
                <small>Hak akses per fitur</small>
            </div>
        </div>
    </div>
</section>

<div class="auth-switcher" role="navigation" aria-label="Navigasi auth">
    @foreach ($authItems as $item)
        <a href="{{ $item["route"] }}" class="{{ $authActive === $item["key"] ? "is-active" : "" }}">
            <i class="mdi {{ $item["icon"] }}"></i>
            {{ $item["label"] }}
        </a>
    @endforeach
</div>
