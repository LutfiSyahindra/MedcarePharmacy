@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.menu.keuangan.partials.style")
@endpush

@section("content")
    <div class="finance-page" id="financeApp"
        data-section="{{ $section }}"
        data-url="{{ route('keuangan.data') }}"
        data-store-url="{{ route('keuangan.transactions.store') }}"
        data-void-base="{{ url('/medcare/menu/keuangan/transactions') }}">
        <nav class="page-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('keuangan.index') }}">Keuangan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $page['title'] }}</li>
            </ol>
        </nav>

        @include("medcare.menu.keuangan.partials.navigation")

        <header class="fn-hero">
            <div class="fn-hero-copy">
                <span class="fn-eyebrow"><i class="mdi {{ $page['icon'] }}"></i> {{ $page['eyebrow'] }}</span>
                <h1>{{ $page['heading'] }}</h1>
                <p>{{ $page['description'] }}</p>
                <div class="fn-hero-actions">
                    @if ($section === 'ledger')
                        <button type="button" class="btn fn-btn-light" id="fnAddTransaction" disabled>
                            <i class="mdi mdi-plus-circle-outline"></i> Catat transaksi
                        </button>
                    @else
                        <a href="{{ route('keuangan.ledger') }}" class="btn fn-btn-light">
                            <i class="mdi mdi-book-plus-outline"></i> Buka buku kas
                        </a>
                    @endif
                    @if ($section === 'cashier')
                        <a href="{{ route('penjualan.pos.shifts') }}" class="btn fn-btn-ghost">
                            <i class="mdi mdi-history"></i> Riwayat shift
                        </a>
                    @else
                        <a href="{{ route('keuangan.cashier') }}" class="btn fn-btn-ghost">
                            <i class="mdi mdi-cash-register"></i> Pantau kas kasir
                        </a>
                    @endif
                </div>
            </div>
            <div class="fn-hero-balance">
                <span id="fnHeroLabel">MEMUAT RINGKASAN</span>
                <strong id="fnHeroValue">Rp 0</strong>
                <p id="fnHeroPeriod">Menyiapkan periode...</p>
                <div><i></i><span id="fnHeroBranch">Memuat cabang</span></div>
            </div>
        </header>

        @include("medcare.menu.keuangan.partials.filter")

        <div class="fn-state fn-loading" id="fnLoading" role="status"><span></span><div><b>Memuat {{ strtolower($page['title']) }}...</b><small>Data disiapkan dari sumber transaksi yang terhubung.</small></div></div>
        <div class="fn-state fn-error" id="fnError" hidden><i class="mdi mdi-alert-circle-outline"></i><div><b>Data keuangan belum dapat dimuat</b><small id="fnErrorCopy">Silakan coba kembali.</small></div><button type="button" id="fnRetry">Coba lagi</button></div>

        @include($page['view'])

        @if ($section === 'ledger')
            @include("medcare.menu.keuangan.partials.transaction-modal")
        @endif
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.keuangan.partials.script")
@endpush
