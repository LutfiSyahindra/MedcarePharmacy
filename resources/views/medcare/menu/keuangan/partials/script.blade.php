<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('financeApp');
    if (!app) return;

    const config = {
        categories: @json($categories),
        csrf: document.querySelector('meta[name="csrf-token"]')?.content || ''
    };
    const el = id => document.getElementById(id);
    const state = { finance: null, chart: null, controller: null, branchesLoaded: false };
    const money = value => `${Number(value || 0) < 0 ? '-' : ''}Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.abs(Number(value || 0)))}`;
    const number = value => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const isoDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const localDateTime = () => { const date = new Date(); date.setMinutes(date.getMinutes() - date.getTimezoneOffset()); return date.toISOString().slice(0, 16); };
    const firstError = payload => { const first = Object.values(payload?.errors || {})[0]; return Array.isArray(first) ? first[0] : (first || payload?.message || 'Permintaan belum dapat diproses.'); };
    const iconForMethod = method => ({tunai:'mdi-cash',transfer:'mdi-bank-transfer',qris:'mdi-qrcode-scan',debit:'mdi-credit-card-outline',credit_card:'mdi-credit-card',ewallet:'mdi-wallet-outline',piutang:'mdi-file-clock-outline',instansi:'mdi-domain',potong_piutang:'mdi-file-percent-outline'}[method] || 'mdi-dots-horizontal-circle-outline');
    const iconForSource = source => ({pos:'mdi-point-of-sale',return:'mdi-keyboard-return',cashier:'mdi-cash-register',manual:'mdi-book-edit-outline',settlement:'mdi-swap-horizontal-bold',system:'mdi-cog-transfer-outline'}[source] || 'mdi-circle-outline');
    const setText = (id, value) => { const target = el(id); if (target) target.textContent = value; };

    function loadBranches(meta) {
        if (state.branchesLoaded) return;
        const filter = el('fnBranch');
        const entry = el('fnEntryBranch');
        if (entry) entry.innerHTML = '';
        (meta.branches || []).forEach(branch => {
            const label = `${branch.name}${branch.code ? ` · ${branch.code}` : ''}`;
            filter?.add(new Option(label, branch.id));
            entry?.add(new Option(label, branch.id));
        });
        state.branchesLoaded = true;
        if (el('fnAddTransaction')) el('fnAddTransaction').disabled = !(meta.branches || []).length;
    }

    function renderHero(finance) {
        const period = `${finance.meta.date_start.split('-').reverse().join('/')} – ${finance.meta.date_end.split('-').reverse().join('/')}`;
        let label = 'ARUS KAS BERSIH';
        let value = money(finance.summary.net_cash_flow);
        let detail = period;

        if (app.dataset.section === 'monthly') {
            const netProfit = (finance.monthly_accounts?.accounts || []).find(account => account.key === 'net_profit');
            label = `LABA BERSIH · ${(finance.monthly_accounts?.period_label || '').toUpperCase()}`;
            value = money(netProfit?.amount || 0);
            detail = finance.monthly_accounts?.is_partial ? 'Bulan berjalan' : 'Periode bulanan penuh';
        } else if (app.dataset.section === 'ledger') {
            label = 'TRANSAKSI DITAMPILKAN';
            value = number(finance.meta.displayed_rows);
        } else if (app.dataset.section === 'cashier') {
            label = 'SALDO LACI AKTIF';
            value = money(finance.open_drawers.expected_cash);
            detail = `${finance.open_drawers.count} shift sedang aktif`;
        }

        setText('fnHeroLabel', label);
        setText('fnHeroValue', value);
        setText('fnHeroPeriod', detail);
        setText('fnHeroBranch', finance.meta.branch_label);
        const heroValue = el('fnHeroValue');
        if (heroValue) heroValue.style.color = String(value).startsWith('-') ? '#ffc2ca' : '#fff';
    }

    function renderSummary(finance) {
        const summary = finance.summary;
        setText('fnIncome', money(summary.income));
        setText('fnExpense', money(summary.expense));
        setText('fnNet', money(summary.net_cash_flow));
        setText('fnCash', money(summary.net_cash));
        setText('fnDrawer', money(finance.open_drawers.expected_cash));
        setText('fnDrawerCopy', `${finance.open_drawers.count} shift sedang aktif`);
        setText('fnIncomeCopy', `${money(summary.pos_income)} berasal dari POS`);
        setText('fnRowCount', `${finance.meta.displayed_rows} dari ${finance.meta.total_rows} transaksi`);
        loadBranches(finance.meta);
    }

    function renderChart(trend) {
        const target = el('fnCashFlowChart');
        if (!target) return;
        if (state.chart) state.chart.destroy();
        if (typeof ApexCharts === 'undefined') {
            target.innerHTML = '<div class="fn-empty">Grafik belum tersedia pada halaman ini.</div>';
            return;
        }
        state.chart = new ApexCharts(target, {
            chart:{type:'area',height:290,toolbar:{show:false},fontFamily:'inherit'},
            series:[{name:'Pendapatan',data:trend.income || []},{name:'Pengeluaran',data:trend.expense || []}],
            colors:['#07856c','#d25969'],stroke:{curve:'smooth',width:2.4},fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:.24,opacityTo:.025,stops:[0,95]}},dataLabels:{enabled:false},markers:{size:0,hover:{size:4}},grid:{borderColor:'#edf2f3',strokeDashArray:4},
            xaxis:{categories:trend.labels || [],labels:{style:{colors:'#82949c',fontSize:'10px'},rotate:0,hideOverlappingLabels:true},axisBorder:{show:false},axisTicks:{show:false}},
            yaxis:{labels:{formatter:value=>value >= 1000000 ? `${(value/1000000).toFixed(value >= 10000000 ? 0 : 1)}jt` : value >= 1000 ? `${Math.round(value/1000)}rb` : Math.round(value),style:{colors:'#82949c',fontSize:'10px'}}},
            tooltip:{shared:true,y:{formatter:money}},legend:{position:'top',horizontalAlign:'right',fontSize:'11px',markers:{size:5}}
        });
        state.chart.render();
    }

    function renderMethods(rows) {
        const target = el('fnMethodRows');
        if (!target) return;
        if (!rows.length) { target.innerHTML = '<div class="fn-empty">Belum ada metode pembayaran pada periode ini.</div>'; return; }
        target.innerHTML = rows.map(row => `<div class="fn-method-row"><span><i class="mdi ${iconForMethod(row.key)}"></i></span><div class="fn-method-copy"><b>${escapeHtml(row.label)}</b><small>${row.transactions} transaksi · masuk ${money(row.income)}</small></div><div class="fn-method-value"><strong class="${Number(row.net) < 0 ? 'is-negative' : 'is-positive'}">${money(row.net)}</strong><small>keluar ${money(row.expense)}</small></div></div>`).join('');
    }

    function renderMonthlyAccounts(monthly) {
        const accountTarget = el('fnMonthlyAccounts');
        if (!accountTarget) return;
        const accounts = monthly?.accounts || [];
        const records = monthly?.records || [];
        setText('fnMonthlyPeriod', `${monthly?.period_label || '-'}${monthly?.is_partial ? ' · berjalan' : ''}`);
        setText('fnMonthlyBasis', monthly?.basis || 'Omzet dan keuntungan dihitung dari transaksi sumber.');
        accountTarget.innerHTML = accounts.length ? accounts.map(account => {
            const change = account.change_percent === null ? 'Belum ada pembanding' : `${Number(account.change_percent) >= 0 ? '+' : ''}${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(account.change_percent)}% dari bulan lalu`;
            const icon = account.key === 'revenue' ? 'mdi-chart-box-outline' : (account.key === 'net_profit' ? 'mdi-wallet-plus-outline' : 'mdi-finance');
            return `<article class="fn-account-card is-${escapeHtml(account.key)}"><span class="fn-account-icon"><i class="mdi ${icon}"></i></span><div class="fn-account-copy"><small>${escapeHtml(account.code)}</small><h3>${escapeHtml(account.name)}</h3><strong>${money(account.amount)}</strong><p>${escapeHtml(account.description)}</p></div><span class="fn-account-change is-${escapeHtml(account.trend)}"><i class="mdi ${account.trend === 'up' ? 'mdi-trending-up' : (account.trend === 'down' ? 'mdi-trending-down' : 'mdi-minus')}"></i>${escapeHtml(change)}</span></article>`;
        }).join('') : '<div class="fn-empty">Akun bulanan belum tersedia.</div>';
        el('fnMonthlyRows').innerHTML = records.length ? records.map(row => `<tr class="${row.is_current ? 'is-current' : ''}"><td><span class="fn-month-label"><b>${escapeHtml(row.period_label)}</b>${row.is_partial ? '<small>Bulan berjalan</small>' : '<small>Periode penuh</small>'}</span></td><td class="text-end fn-amount">${money(row.gross_revenue)}</td><td class="text-end fn-amount is-expense">${money(row.returns)}</td><td class="text-end fn-amount is-income">${money(row.net_revenue)}</td><td class="text-end fn-amount">${money(row.net_hpp)}</td><td class="text-end fn-amount ${Number(row.gross_profit) < 0 ? 'is-expense' : 'is-income'}">${money(row.gross_profit)}</td><td class="text-end fn-amount is-expense">${money(row.operating_expenses)}</td><td class="text-end fn-amount ${Number(row.net_profit) < 0 ? 'is-expense' : 'is-income'}">${money(row.net_profit)}</td><td class="text-end fn-margin">${row.net_margin === null ? '—' : `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(row.net_margin)}%`}</td></tr>`).join('') : '<tr><td colspan="9" class="fn-empty">Belum ada catatan bulanan.</td></tr>';
    }

    function renderDrawers(drawers) {
        setText('fnDrawerCount', number(drawers.count));
        setText('fnDrawerSales', money((drawers.rows || []).reduce((total, row) => total + Number(row.cash_sales || 0), 0)));
        const target = el('fnDrawerRows');
        if (!target) return;
        if (!drawers.rows.length) { target.innerHTML = '<div class="fn-empty">Tidak ada shift kasir yang sedang aktif pada cabang terpilih.</div>'; return; }
        target.innerHTML = drawers.rows.map(row => `<article class="fn-drawer-card"><div><span><b>${escapeHtml(row.branch_name)}</b><small>${escapeHtml(row.shift_number)} · ${escapeHtml(row.cashier_name)} · buka ${escapeHtml(row.opened_at)}</small></span><span>AKTIF</span></div><div class="fn-drawer-metrics"><span><small>Penjualan tunai</small><strong>${money(row.cash_sales)}</strong></span><span><small>Kas seharusnya</small><strong>${money(row.expected_cash)}</strong></span></div></article>`).join('');
    }

    function renderLedger(rows) {
        const target = el('fnLedgerRows');
        if (!target) return;
        if (!rows.length) { target.innerHTML = '<tr><td colspan="9" class="fn-empty">Belum ada transaksi yang cocok dengan filter ini.</td></tr>'; return; }
        target.innerHTML = rows.map(row => {
            const voided = row.status === 'voided';
            const sourceClass = row.source === 'manual' ? 'is-manual' : (row.source === 'cashier' ? 'is-cashier' : (row.source === 'return' ? 'is-return' : (['system', 'settlement'].includes(row.source) ? 'is-system' : '')));
            const action = row.can_void ? `<button type="button" class="fn-row-action" data-void-id="${row.record_id}" data-reference="${escapeHtml(row.reference)}" title="Batalkan transaksi"><i class="mdi mdi-cancel"></i></button>` : '';
            return `<tr class="${voided ? 'fn-voided' : ''}"><td><span class="fn-reference"><b>${escapeHtml(row.reference)}</b><small>${escapeHtml(row.occurred_at_label)}${row.external_reference ? ` · ${escapeHtml(row.external_reference)}` : ''}</small></span></td><td>${escapeHtml(row.branch_name)}</td><td><span class="fn-category"><b>${escapeHtml(row.category_label)}</b><small title="${escapeHtml(row.description)}">${escapeHtml(row.description)}</small></span></td><td>${voided ? '<span class="fn-status">Dibatalkan</span>' : `<span class="fn-source ${sourceClass}"><i class="mdi ${iconForSource(row.source)}"></i>${escapeHtml(row.source_label)}</span>`}</td><td><span class="fn-method-badge"><i class="mdi ${iconForMethod(row.payment_method)}"></i>${escapeHtml(row.payment_method_label)}</span></td><td class="text-end fn-amount is-income">${row.type === 'income' ? money(row.amount) : '—'}</td><td class="text-end fn-amount is-expense">${row.type === 'expense' ? money(row.amount) : '—'}</td><td>${escapeHtml(row.created_by)}</td><td>${action}</td></tr>`;
        }).join('');
    }

    function render(finance) {
        state.finance = finance;
        renderHero(finance);
        renderSummary(finance);
        renderMonthlyAccounts(finance.monthly_accounts);
        renderChart(finance.trend);
        renderMethods(finance.payment_methods || []);
        renderDrawers(finance.open_drawers);
        renderLedger(finance.rows || []);
    }

    async function load() {
        state.controller?.abort();
        state.controller = new AbortController();
        el('fnLoading').hidden = false;
        el('fnError').hidden = true;
        el('fnApply').disabled = true;
        app.setAttribute('aria-busy', 'true');
        const params = new URLSearchParams(new FormData(el('fnFilterForm')));
        [...params.entries()].forEach(([key, value]) => { if (!value) params.delete(key); });
        try {
            const response = await fetch(`${app.dataset.url}?${params}`, { headers:{Accept:'application/json'}, signal:state.controller.signal });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(firstError(payload));
            render(payload.finance);
        } catch (error) {
            if (error.name === 'AbortError') return;
            setText('fnErrorCopy', error.message || 'Data keuangan gagal dimuat.');
            el('fnError').hidden = false;
        } finally {
            el('fnLoading').hidden = true;
            el('fnApply').disabled = false;
            app.removeAttribute('aria-busy');
        }
    }

    function setRange(days) {
        const startInput = el('fnDateStart');
        const endInput = el('fnDateEnd');
        if (!startInput || !endInput) return load();
        const end = new Date();
        const start = new Date(end);
        if (days === 'mtd') start.setDate(1); else start.setDate(end.getDate() - Number(days) + 1);
        startInput.value = isoDate(start);
        endInput.value = isoDate(end);
        document.querySelectorAll('.fn-presets button').forEach(button => button.classList.toggle('is-active', button.dataset.days === String(days)));
        load();
    }

    function renderCategories() {
        const type = el('fnEntryType');
        const target = el('fnEntryCategory');
        if (!type || !target) return;
        const categories = config.categories[type.value] || {};
        target.innerHTML = Object.entries(categories).map(([key, label]) => `<option value="${escapeHtml(key)}">${escapeHtml(label)}</option>`).join('');
    }

    function syncCashNotice() {
        const method = el('fnEntryMethod');
        if (!method) return;
        const cash = method.value === 'tunai';
        el('fnCashNotice').hidden = !cash;
        el('fnEntryOccurredAt').disabled = cash;
    }

    el('fnFilterForm').addEventListener('submit', event => { event.preventDefault(); load(); });
    el('fnRetry').addEventListener('click', load);
    el('fnReset').addEventListener('click', () => {
        el('fnFilterForm').reset();
        if (el('fnDateStart')) setRange(30); else load();
    });
    document.querySelectorAll('.fn-presets button').forEach(button => button.addEventListener('click', () => setRange(button.dataset.days)));
    el('fnEntryType')?.addEventListener('change', renderCategories);
    el('fnEntryMethod')?.addEventListener('change', syncCashNotice);
    el('fnAddTransaction')?.addEventListener('click', () => {
        el('fnTransactionForm').reset();
        renderCategories();
        el('fnEntryOccurredAt').value = localDateTime();
        const selectedBranch = el('fnBranch').value;
        if (selectedBranch) el('fnEntryBranch').value = selectedBranch;
        syncCashNotice();
        bootstrap.Modal.getOrCreateInstance(el('fnTransactionModal')).show();
    });

    el('fnTransactionForm')?.addEventListener('submit', async event => {
        event.preventDefault();
        const button = el('fnSaveTransaction');
        const form = new FormData(event.currentTarget);
        if (el('fnEntryOccurredAt').disabled) form.set('occurred_at', localDateTime());
        button.disabled = true;
        button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...';
        try {
            const response = await fetch(app.dataset.storeUrl, { method:'POST', headers:{Accept:'application/json','X-CSRF-TOKEN':config.csrf}, body:form });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(firstError(payload));
            bootstrap.Modal.getInstance(el('fnTransactionModal'))?.hide();
            await Swal.fire({icon:'success',title:'Transaksi tersimpan',text:payload.message,timer:1800,showConfirmButton:false});
            load();
        } catch (error) {
            Swal.fire('Transaksi gagal', error.message, 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="mdi mdi-content-save-check-outline"></i> Simpan transaksi';
        }
    });

    el('fnLedgerRows')?.addEventListener('click', async event => {
        const button = event.target.closest('[data-void-id]');
        if (!button) return;
        const result = await Swal.fire({title:'Batalkan transaksi?',text:`${button.dataset.reference} akan dibalik dan tetap tersimpan dalam jejak audit.`,icon:'warning',input:'textarea',inputLabel:'Alasan pembatalan',inputPlaceholder:'Minimal 5 karakter',inputAttributes:{maxlength:500},showCancelButton:true,confirmButtonText:'Batalkan transaksi',cancelButtonText:'Kembali',confirmButtonColor:'#bd4657',preConfirm:value=>{if(!value || value.trim().length<5){Swal.showValidationMessage('Alasan minimal 5 karakter.');return false;}return value.trim();}});
        if (!result.isConfirmed) return;
        try {
            const response = await fetch(`${app.dataset.voidBase}/${button.dataset.voidId}/void`, {method:'PUT',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':config.csrf},body:JSON.stringify({reason:result.value})});
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(firstError(payload));
            await Swal.fire({icon:'success',title:'Transaksi dibatalkan',text:payload.message,timer:1800,showConfirmButton:false});
            load();
        } catch (error) {
            Swal.fire('Pembatalan gagal', error.message, 'error');
        }
    });

    const initialParams = new URLSearchParams(window.location.search);
    ['source', 'type', 'payment_method', 'search'].forEach(name => {
        const field = el('fnFilterForm')?.elements.namedItem(name);
        if (field && initialParams.has(name)) field.value = initialParams.get(name);
    });
    renderCategories();
    syncCashNotice();
    load();
});
</script>
