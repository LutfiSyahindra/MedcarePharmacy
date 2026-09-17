<section class="fn-kpi-grid fn-kpi-grid--three" aria-label="Ringkasan kas kasir">
    <article class="fn-kpi is-drawer"><span><i class="mdi mdi-cash-register"></i></span><div><small>Saldo laci aktif</small><strong id="fnDrawer">Rp 0</strong><p id="fnDrawerCopy">0 shift sedang aktif</p></div></article>
    <article class="fn-kpi is-net"><span><i class="mdi mdi-account-clock-outline"></i></span><div><small>Shift aktif</small><strong id="fnDrawerCount">0</strong><p>Kasir yang belum tutup shift</p></div></article>
    <article class="fn-kpi is-cash"><span><i class="mdi mdi-cash-plus"></i></span><div><small>Penjualan tunai shift aktif</small><strong id="fnDrawerSales">Rp 0</strong><p>Akumulasi seluruh laci aktif</p></div></article>
</section>

<section class="fn-panel fn-drawer-panel">
    <div class="fn-panel-heading"><div><span class="fn-heading-icon is-green"><i class="mdi mdi-cash-register"></i></span><span><small>LIVE CASHIER</small><h2>Kasir yang sedang aktif</h2><p>Saldo seharusnya dihitung langsung dari modal, penjualan tunai, dan mutasi shift.</p></span></div><a href="{{ route('penjualan.pos.shifts') }}">Lihat riwayat shift <i class="mdi mdi-arrow-right"></i></a></div>
    <div class="fn-drawer-grid" id="fnDrawerRows"><div class="fn-empty">Menunggu status kasir.</div></div>
</section>
