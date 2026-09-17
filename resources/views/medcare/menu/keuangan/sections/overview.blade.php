<section class="fn-kpi-grid" aria-label="Ringkasan keuangan">
    <article class="fn-kpi is-income"><span><i class="mdi mdi-arrow-down-bold-circle-outline"></i></span><div><small>Total pendapatan</small><strong id="fnIncome">Rp 0</strong><p id="fnIncomeCopy">Termasuk penjualan POS</p></div></article>
    <article class="fn-kpi is-expense"><span><i class="mdi mdi-arrow-up-bold-circle-outline"></i></span><div><small>Total pengeluaran</small><strong id="fnExpense">Rp 0</strong><p>Retur dan biaya operasional</p></div></article>
    <article class="fn-kpi is-net"><span><i class="mdi mdi-scale-balance"></i></span><div><small>Arus kas bersih</small><strong id="fnNet">Rp 0</strong><p>Pendapatan dikurangi pengeluaran</p></div></article>
    <article class="fn-kpi is-cash"><span><i class="mdi mdi-cash-multiple"></i></span><div><small>Arus tunai bersih</small><strong id="fnCash">Rp 0</strong><p>Hanya metode tunai</p></div></article>
    <article class="fn-kpi is-drawer"><span><i class="mdi mdi-cash-register"></i></span><div><small>Saldo laci aktif</small><strong id="fnDrawer">Rp 0</strong><p id="fnDrawerCopy">0 shift sedang aktif</p></div></article>
</section>

<section class="fn-panel fn-feature-panel">
    <div class="fn-panel-heading"><div><span class="fn-heading-icon"><i class="mdi mdi-apps"></i></span><span><small>SUBFITUR KEUANGAN</small><h2>Pilih pekerjaan yang ingin dilakukan</h2><p>Setiap fungsi tersedia pada halaman tersendiri agar informasi lebih mudah dibaca.</p></span></div></div>
    <div class="fn-feature-grid">
        <a href="{{ route('keuangan.monthly') }}"><span class="is-green"><i class="mdi mdi-calendar-month-outline"></i></span><div><h3>Akun Bulanan</h3><p>Omzet & keuntungan setiap bulan, lengkap dengan HPP dan margin.</p></div><i class="mdi mdi-arrow-right"></i></a>
        <a href="{{ route('keuangan.cash-flow') }}"><span class="is-blue"><i class="mdi mdi-chart-timeline-variant"></i></span><div><h3>Arus Kas</h3><p>Bandingkan uang masuk, uang keluar, dan kanal pembayaran.</p></div><i class="mdi mdi-arrow-right"></i></a>
        <a href="{{ route('keuangan.obligations') }}"><span class="is-violet"><i class="mdi mdi-swap-horizontal-bold"></i></span><div><h3>Hutang & Piutang</h3><p>Bayar supplier dan terima pelunasan pelanggan melalui jurnal Keuangan.</p></div><i class="mdi mdi-arrow-right"></i></a>
        <a href="{{ route('keuangan.ledger') }}"><span class="is-orange"><i class="mdi mdi-book-open-page-variant-outline"></i></span><div><h3>Buku Kas Terpadu</h3><p>Cari transaksi dan catat jurnal operasional dengan jejak audit.</p></div><i class="mdi mdi-arrow-right"></i></a>
        <a href="{{ route('keuangan.cashier') }}"><span class="is-violet"><i class="mdi mdi-cash-register"></i></span><div><h3>Kas Kasir</h3><p>Pantau saldo seharusnya pada semua shift yang sedang aktif.</p></div><i class="mdi mdi-arrow-right"></i></a>
    </div>
</section>
