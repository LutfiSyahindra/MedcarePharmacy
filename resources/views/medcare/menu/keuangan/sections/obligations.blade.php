<section class="fn-filter-card">
    <div class="fn-section-heading">
        <div><span class="fn-heading-icon"><i class="mdi mdi-filter-variant"></i></span><span><h2>Filter hutang dan piutang aktif</h2><p>Daftar selalu menampilkan saldo terbuka; transaksi yang lunas tersedia di Buku Kas.</p></span></div>
    </div>
    <form id="fnObligationFilter" class="fn-obligation-filter">
        <label><span>Cabang</span><select class="form-select" name="branch_id" id="fnObligationBranch"><option value="">Semua cabang</option></select></label>
        <label><span>Jenis saldo</span><select class="form-select" name="kind"><option value="">Hutang & piutang</option><option value="payable">Hutang supplier</option><option value="receivable">Piutang pelanggan</option></select></label>
        <label><span>Status tempo</span><select class="form-select" name="due_status"><option value="">Semua status</option><option value="overdue">Lewat jatuh tempo</option><option value="due_soon">Tempo ≤ 7 hari</option></select></label>
        <label class="fn-search"><span>Pencarian</span><div><i class="mdi mdi-magnify"></i><input class="form-control" type="search" name="search" placeholder="Faktur, transaksi, supplier, pelanggan"></div></label>
        <div class="fn-filter-actions"><button class="btn fn-btn-secondary" type="button" id="fnObligationReset"><i class="mdi mdi-filter-remove-outline"></i> Reset</button><button class="btn fn-btn-primary" type="submit" id="fnObligationApply"><i class="mdi mdi-check"></i> Terapkan</button></div>
    </form>
</section>

<div class="fn-state fn-loading" id="fnObligationLoading" role="status"><span></span><div><b>Memuat saldo aktif...</b><small>Menyiapkan hutang supplier dan piutang pelanggan.</small></div></div>
<div class="fn-state fn-error" id="fnObligationError" hidden><i class="mdi mdi-alert-circle-outline"></i><div><b>Saldo belum dapat dimuat</b><small id="fnObligationErrorCopy">Silakan coba kembali.</small></div><button type="button" id="fnObligationRetry">Coba lagi</button></div>

<section class="fn-kpi-grid fn-kpi-grid--four" aria-label="Ringkasan hutang dan piutang">
    <article class="fn-kpi is-expense"><span><i class="mdi mdi-truck-delivery-outline"></i></span><div><small>Hutang supplier</small><strong id="fnPayableTotal">Rp 0</strong><p id="fnPayableCount">0 faktur aktif</p></div></article>
    <article class="fn-kpi is-income"><span><i class="mdi mdi-account-cash-outline"></i></span><div><small>Piutang pelanggan</small><strong id="fnReceivableTotal">Rp 0</strong><p id="fnReceivableCount">0 transaksi aktif</p></div></article>
    <article class="fn-kpi is-net"><span><i class="mdi mdi-scale-balance"></i></span><div><small>Posisi bersih</small><strong id="fnObligationNet">Rp 0</strong><p>Piutang dikurangi hutang</p></div></article>
    <article class="fn-kpi is-drawer"><span><i class="mdi mdi-calendar-alert"></i></span><div><small>Hutang lewat tempo</small><strong id="fnOverdueTotal">Rp 0</strong><p id="fnOverdueCount">0 faktur</p></div></article>
</section>

<section class="fn-panel fn-obligation-panel">
    <div class="fn-panel-heading"><div><span class="fn-heading-icon is-orange"><i class="mdi mdi-truck-delivery-outline"></i></span><span><small>ACCOUNTS PAYABLE</small><h2>Hutang Supplier</h2><p>Pembayaran langsung memperbarui faktur dan Buku Kas.</p></span></div><span class="fn-row-count" id="fnPayableRowsCount">0 faktur</span></div>
    <div class="table-responsive"><table class="table fn-table fn-obligation-table"><thead><tr><th>Faktur</th><th>Supplier</th><th>Cabang</th><th>Tanggal / Tempo</th><th class="text-end">Tagihan</th><th class="text-end">Dibayar</th><th class="text-end">Sisa</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="fnPayableRows"></tbody></table></div>
</section>

<section class="fn-panel fn-obligation-panel">
    <div class="fn-panel-heading"><div><span class="fn-heading-icon is-green"><i class="mdi mdi-account-cash-outline"></i></span><span><small>ACCOUNTS RECEIVABLE</small><h2>Piutang Pelanggan & Instansi</h2><p>Penerimaan cicilan tercatat sebagai kas masuk dan pembayaran penjualan.</p></span></div><span class="fn-row-count" id="fnReceivableRowsCount">0 transaksi</span></div>
    <div class="table-responsive"><table class="table fn-table fn-obligation-table"><thead><tr><th>Transaksi</th><th>Pelanggan / Instansi</th><th>Cabang</th><th>Tanggal</th><th class="text-end">Tagihan</th><th class="text-end">Diterima</th><th class="text-end">Sisa</th><th>Umur</th><th>Aksi</th></tr></thead><tbody id="fnReceivableRows"></tbody></table></div>
</section>
