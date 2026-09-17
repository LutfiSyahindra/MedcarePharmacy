<nav class="fn-module-nav" aria-label="Subfitur keuangan">
    <a href="{{ route('keuangan.index') }}" class="{{ $section === 'overview' ? 'is-active' : '' }}">
        <i class="mdi mdi-view-dashboard-outline"></i><span><b>Ringkasan</b><small>Indikator utama</small></span>
    </a>
    <a href="{{ route('keuangan.monthly') }}" class="{{ $section === 'monthly' ? 'is-active' : '' }}">
        <i class="mdi mdi-calendar-month-outline"></i><span><b>Akun Bulanan</b><small>Omzet & laba</small></span>
    </a>
    <a href="{{ route('keuangan.cash-flow') }}" class="{{ $section === 'cash-flow' ? 'is-active' : '' }}">
        <i class="mdi mdi-chart-timeline-variant"></i><span><b>Arus Kas</b><small>Tren & kanal</small></span>
    </a>
    <a href="{{ route('keuangan.ledger') }}" class="{{ $section === 'ledger' ? 'is-active' : '' }}">
        <i class="mdi mdi-book-open-page-variant-outline"></i><span><b>Buku Kas</b><small>Jurnal transaksi</small></span>
    </a>
    <a href="{{ route('keuangan.cashier') }}" class="{{ $section === 'cashier' ? 'is-active' : '' }}">
        <i class="mdi mdi-cash-register"></i><span><b>Kas Kasir</b><small>Shift aktif</small></span>
    </a>
</nav>
