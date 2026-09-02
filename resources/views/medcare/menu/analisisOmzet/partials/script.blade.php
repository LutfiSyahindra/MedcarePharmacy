<script>
(() => {
    'use strict';
    const app = document.getElementById('revenueAnalysisApp');
    if (!app || app.dataset.ready === '1') return;
    app.dataset.ready = '1';

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const state = { charts: {}, controller: null, analysis: null, toastTimer: null, appliedParams: null, filterError: false };
    const form = $('#roFilterForm');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
    const number = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
    const percent = value => `${number(value)}%`;
    const compactMoney = value => {
        const amount = Number(value || 0);
        if (Math.abs(amount) >= 1e12) return `Rp ${(amount / 1e12).toFixed(1)} T`;
        if (Math.abs(amount) >= 1e9) return `Rp ${(amount / 1e9).toFixed(1)} M`;
        if (Math.abs(amount) >= 1e6) return `Rp ${(amount / 1e6).toFixed(1)} jt`;
        if (Math.abs(amount) >= 1e3) return `Rp ${(amount / 1e3).toFixed(0)} rb`;
        return money(amount);
    };
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;' }[char]));
    const dateValue = date => `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;

    function params() {
        const query = new URLSearchParams();
        new FormData(form).forEach((value, key) => { if (String(value).trim() !== '') query.set(key, value); });
        return query;
    }

    function showToast(message, error = false) {
        const toast = $('#roToast');
        toast.hidden = false;
        toast.classList.toggle('is-error', error);
        $('i', toast).className = `mdi ${error ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline'}`;
        $('span', toast).textContent = message;
        clearTimeout(state.toastTimer);
        state.toastTimer = setTimeout(() => { toast.hidden = true; }, 4200);
    }

    function setFilterState(tone, text) {
        const status = $('#roFilterState');
        const icons = {
            ready: 'mdi-check-decagram-outline',
            dirty: 'mdi-alert-decagram-outline',
            loading: 'mdi-loading mdi-spin',
            error: 'mdi-alert-circle-outline',
        };
        status.className = `ro-filter-state is-${tone}`;
        status.setAttribute('title', text);
        $('i', status).className = `mdi ${icons[tone] || icons.ready}`;
        $('#roFilterStateText').textContent = text;
    }

    function syncFieldStates() {
        $$('.ro-field', form).forEach(field => {
            const control = $('input, select', field);
            field.classList.toggle('is-selected', Boolean(control && String(control.value).trim()));
        });
    }

    function syncFilterState() {
        syncFieldStates();
        if (state.appliedParams === null) return;
        const dirty = params().toString() !== state.appliedParams;
        $('#roFilterPanel').classList.toggle('has-pending-changes', dirty);
        if (!state.filterError) setFilterState(dirty ? 'dirty' : 'ready', dirty ? 'Perubahan belum diterapkan' : 'Filter tersinkron');
        const copy = $('#roFilterActionCopy');
        $('strong', copy).textContent = dirty ? 'Ada perubahan parameter' : 'Filter tersinkron';
        $('small', copy).textContent = dirty
            ? 'Klik Terapkan Filter untuk memperbarui seluruh analisis.'
            : 'Ubah parameter lalu terapkan untuk memperbarui dashboard.';
    }

    function setPresetActive(range) {
        $$('.ro-presets button').forEach(button => {
            const active = button.dataset.range === range;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
    }

    function setLoading(active) {
        $('#roFilterPanel').classList.toggle('is-loading', active);
        $('#roFilterPanel').setAttribute('aria-busy', String(active));
        $('#roApply').disabled = active;
        $('#roApply').innerHTML = active
            ? '<i class="mdi mdi-loading mdi-spin"></i> Mengolah Data'
            : '<i class="mdi mdi-filter-check-outline"></i> Terapkan Filter';
        if (active) setFilterState('loading', 'Memperbarui analisis');
    }

    async function load() {
        if (state.controller) state.controller.abort();
        const controller = new AbortController();
        state.controller = controller;
        const requestParams = params().toString();
        setLoading(true);
        try {
            const response = await fetch(`${app.dataset.url}?${requestParams}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(firstError(payload) || 'Data analisis penjualan gagal dimuat.');
            state.analysis = payload.analysis;
            render(payload.analysis);
            state.filterError = false;
            state.appliedParams = requestParams;
            syncFilterState();
        } catch (error) {
            if (error.name !== 'AbortError') {
                state.filterError = true;
                setFilterState('error', 'Gagal memperbarui data');
                showToast(error.message, true);
                renderError(error.message);
            }
        } finally {
            if (state.controller === controller) setLoading(false);
        }
    }

    function firstError(payload) {
        if (payload?.errors) return Object.values(payload.errors).flat()[0];
        return payload?.message || '';
    }

    function render(analysis) {
        $('#roHeroBranch').textContent = analysis.meta.branch_label;
        $('#roHeroPeriod').textContent = analysis.meta.period_label;
        $('#roTrendSubtitle').textContent = `${analysis.meta.period_label} dibanding ${analysis.meta.previous_period_label}`;
        const generatedAt = new Date(String(analysis.meta.generated_at || '').replace(' ', 'T'));
        $('#roDataTimestamp').textContent = Number.isNaN(generatedAt.getTime())
            ? (analysis.meta.generated_at || '—')
            : generatedAt.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        syncSegments(analysis.meta);
        renderOptions(analysis.options || {});
        renderActiveFilters(analysis.meta.active_filters || []);
        renderKpis(analysis.summary);
        renderTrend(analysis.trend);
        renderFastMoving(analysis.fast_moving || { summary: {}, rows: [] });
        renderProducts(analysis.products || []);
        renderCategories(analysis.categories || []);
        renderSaleTypes(analysis.sale_types || []);
        renderPayments(analysis.payments || []);
        renderHourly(analysis.hourly || { rows: [] });
        renderMarketBasket(analysis.market_basket || { summary: {}, rows: [] });
        renderWeekdays(analysis.weekdays || { rows: [] });
        renderCashiers(analysis.cashiers || []);
        renderTarget(analysis.target || {});
        renderInsights(analysis.insights || []);
    }

    function renderOptions(options) {
        const maps = [
            ['#roBranch', options.branches, row => row.id, row => `${row.name}${row.code ? ` · ${row.code}` : ''}`],
            ['#roCashier', options.cashiers, row => row.id, row => row.name],
            ['#roShift', options.shifts, row => row.id, row => row.label],
            ['#roMedicine', options.medicines, row => row.id, row => row.label],
            ['#roCategory', options.categories, row => row.id, row => row.name],
            ['#roClassification', options.classifications, row => row.id, row => row.name],
            ['#roSaleType', options.sale_types, row => row.key, row => row.label],
            ['#roPaymentMethod', options.payment_methods, row => row.key, row => row.label],
        ];
        maps.forEach(([selector, rows, value, label]) => {
            if (!Array.isArray(rows)) return;
            const select = $(selector);
            const selected = select.value;
            const placeholder = select.options[0]?.textContent || 'Semua';
            select.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>` + rows.map(row => `<option value="${escapeHtml(value(row))}">${escapeHtml(label(row))}</option>`).join('');
            if ($$(`option`, select).some(option => option.value === selected)) select.value = selected;
        });
        syncFieldStates();
    }

    function renderActiveFilters(filters) {
        $('#roFilterCount').textContent = number(filters.length);
        $('#roActiveFilters').innerHTML = filters.length
            ? filters.map(label => `<b>${escapeHtml(label)}</b>`).join('')
            : '<em>Tanpa filter tambahan</em>';
    }

    function renderKpis(summary) {
        const growth = Number(summary.growth_percent || 0);
        const cards = [
            ['Total omzet', summary.total_revenue, money, 'mdi-cash-multiple', 'Setelah diskon, sebelum retur', ''],
            ['Omzet bersih', summary.net_revenue, money, 'mdi-wallet-check-outline', `Dikurangi retur ${money(summary.return_value)}`, 'tone-green'],
            ['Total transaksi', summary.transactions, number, 'mdi-receipt-text-check-outline', 'Transaksi selesai unik', 'tone-blue'],
            ['Qty terjual', summary.qty_sold, number, 'mdi-package-variant-closed-check', `Qty retur ${number(summary.return_qty)}`, 'tone-violet'],
            ['Rata-rata transaksi', summary.average_transaction, money, 'mdi-chart-box-outline', 'Nilai rata-rata per struk', 'tone-blue'],
            ['Total diskon', summary.total_discount, money, 'mdi-sale-outline', 'Diskon item + transaksi', 'tone-amber'],
            ['Total retur', summary.return_value, money, 'mdi-cash-refund', `${number(summary.return_documents)} dokumen posted`, 'tone-red'],
            ['Pertumbuhan omzet', growth, percent, growth >= 0 ? 'mdi-trending-up' : 'mdi-trending-down', `Sebelumnya ${money(summary.previous_net_revenue)}`, growth >= 0 ? 'tone-green' : 'tone-red'],
        ];
        $('#roKpiGrid').innerHTML = cards.map((card, index) => `
            <article class="ro-kpi ${card[5]}" style="--ro-card-index:'${String(index + 1).padStart(2, '0')}'">
                <span class="ro-kpi-glow"></span>
                <span class="ro-kpi-icon"><i class="mdi ${card[3]}"></i></span>
                <div><small>${escapeHtml(card[0])}</small><strong>${escapeHtml(card[2](card[1]))}</strong><p>${escapeHtml(card[4])}</p></div>
            </article>`).join('');
    }

    function destroyChart(key) {
        if (state.charts[key]) { state.charts[key].destroy(); delete state.charts[key]; }
    }

    function mountChart(key, selector, options) {
        destroyChart(key);
        const target = $(selector);
        target.innerHTML = '';
        if (typeof ApexCharts === 'undefined') {
            target.innerHTML = '<div class="ro-empty">Library grafik tidak tersedia.</div>';
            return;
        }
        state.charts[key] = new ApexCharts(target, options);
        state.charts[key].render();
    }

    const chartBase = { chart: { fontFamily: 'inherit', toolbar: { show: false }, animations: { speed: 350 }, foreColor: '#6c8090' }, dataLabels: { enabled: false }, grid: { borderColor: '#e8eff1', strokeDashArray: 4 }, legend: { show: false }, tooltip: { shared: false, intersect: false }, noData: { text: 'Belum ada data' } };

    function renderTrend(trend) {
        mountChart('trend', '#roTrendChart', {
            ...chartBase,
            chart: { ...chartBase.chart, type: 'line', height: 335, stacked: false },
            series: [
                { name: 'Omzet Bersih', type: 'area', data: trend.current_revenue || [] },
                { name: 'Periode Sebelumnya', type: 'line', data: trend.previous_revenue || [] },
                { name: 'Transaksi', type: 'column', data: trend.transactions || [] },
            ],
            colors: ['#0f8f83', '#9aabb6', '#3478f6'],
            stroke: { width: [3, 2, 0], curve: 'smooth', dashArray: [0, 6, 0] },
            fill: { type: ['gradient', 'solid', 'solid'], opacity: [.3, 1, .18], gradient: { opacityFrom: .38, opacityTo: .04 } },
            markers: { size: [3, 2, 0] },
            xaxis: { categories: trend.labels || [], axisBorder: { show: false }, axisTicks: { show: false }, labels: { rotate: -35, trim: true } },
            yaxis: [
                { labels: { formatter: compactMoney } },
                { show: false },
                { opposite: true, labels: { formatter: value => number(value) } },
            ],
            tooltip: { shared: true, intersect: false, y: { formatter: (value, ctx) => ctx.seriesIndex === 2 ? `${number(value)} transaksi` : money(value) } },
        });
    }

    function renderFastMoving(data) {
        const rows = data.rows || [];
        const summary = data.summary || {};
        $('#roFastMovingCount').textContent = `${number(rows.length)} produk`;
        $('#roFastMovingSummary').textContent = summary.leader
            ? `${summary.leader} memimpin dengan rata-rata ${number(summary.leader_daily_qty)} unit bersih per hari selama ${number(summary.period_days)} hari.`
            : 'Belum ada produk dengan penjualan bersih pada periode ini.';
        $('#roFastMovingBody').innerHTML = rows.length ? rows.map(row => {
            const growth = Number(row.qty_growth_percent || 0);
            const growthClass = growth > 0 ? 'up' : (growth < 0 ? 'down' : 'flat');
            const growthIcon = growth > 0 ? 'mdi-arrow-up' : (growth < 0 ? 'mdi-arrow-down' : 'mdi-minus');
            return `<tr>
                <td><span class="ro-rank ${Number(row.rank) <= 3 ? 'is-top' : ''}">${number(row.rank)}</span></td>
                <td><span class="ro-product-name"><strong>${escapeHtml(row.name)}</strong><small>${escapeHtml(row.code)} · ${escapeHtml(row.category)}</small></span></td>
                <td class="text-end"><strong>${number(row.net_qty)}</strong></td>
                <td class="text-end">${number(row.transactions)}</td>
                <td class="text-end">${number(row.sales_days)}</td>
                <td class="text-end"><strong>${number(row.average_daily_qty)}</strong></td>
                <td class="text-end">${percent(row.transaction_penetration_percent)}</td>
                <td class="text-end"><span class="ro-growth ${growthClass}"><i class="mdi ${growthIcon}"></i>${percent(Math.abs(growth))}</span></td>
            </tr>`;
        }).join('') : emptyRow(8, 'Belum ada produk fast moving pada periode ini.');
    }

    function renderProducts(rows) {
        $('#roProductCount').textContent = `${number(rows.length)} produk`;
        $('#roProductBody').innerHTML = rows.length ? rows.map((row, index) => {
            const growth = Number(row.growth_percent || 0);
            const growthClass = growth > 0 ? 'up' : (growth < 0 ? 'down' : 'flat');
            const growthIcon = growth > 0 ? 'mdi-arrow-up' : (growth < 0 ? 'mdi-arrow-down' : 'mdi-minus');
            return `<tr>
                <td><span class="ro-rank ${index < 3 ? 'is-top' : ''}">${index + 1}</span></td>
                <td><span class="ro-product-name"><strong>${escapeHtml(row.name)}</strong><small>${escapeHtml(row.code)}</small></span></td>
                <td>${escapeHtml(row.category)}</td><td class="text-end">${number(row.net_qty)}</td><td class="text-end">${number(row.transactions)}</td>
                <td class="text-end"><strong>${money(row.revenue)}</strong></td><td class="text-end">${percent(row.contribution_percent)}</td>
                <td class="text-end"><span class="ro-growth ${growthClass}"><i class="mdi ${growthIcon}"></i>${percent(Math.abs(growth))}</span></td>
            </tr>`;
        }).join('') : emptyRow(8, 'Belum ada produk pada periode ini.');
    }

    function renderCategories(rows) {
        mountChart('category', '#roCategoryChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'donut', height: 275 },
            series: rows.map(row => Math.max(0, Number(row.revenue || 0))), labels: rows.map(row => row.label),
            colors: ['#0f8f83','#3478f6','#7959d9','#e99a3a','#24aa73','#dc5a70','#6f8797','#4bb9cf'],
            stroke: { colors: ['#fff'], width: 3 }, plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', formatter: chart => compactMoney(chart.globals.seriesTotals.reduce((a,b) => a+b,0)) } } } } },
            tooltip: { y: { formatter: money } },
        });
        $('#roCategoryList').innerHTML = rows.length ? rows.slice(0, 7).map(row => `<div class="ro-mini-row"><div><strong>${escapeHtml(row.label)}</strong><small>${number(row.qty)} qty · retur ${number(row.return_qty)}</small></div><span>${money(row.revenue)}<small>${percent(row.contribution_percent)}</small></span></div>`).join('') : '<div class="ro-empty">Belum ada kategori.</div>';
    }

    function renderSaleTypes(rows) {
        horizontalChart('types', '#roTypeChart', rows, '#596fd2');
        renderSummaryList('#roTypeList', rows, 'transaksi');
    }

    function renderPayments(rows) {
        mountChart('payments', '#roPaymentChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'donut', height: 270 },
            series: rows.map(row => Math.max(0, Number(row.revenue || 0))), labels: rows.map(row => row.label),
            colors: ['#14a46e','#3b82f6','#735ad5','#ef9a3c','#21a6b9','#e3586d','#738696','#9b6bc5'],
            plotOptions: { pie: { donut: { size: '70%' } } }, stroke: { width: 3, colors: ['#fff'] },
            tooltip: { y: { formatter: money } },
        });
        renderSummaryList('#roPaymentList', rows, 'transaksi');
    }

    function horizontalChart(key, selector, rows, color) {
        mountChart(key, selector, {
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 270 },
            series: [{ name: 'Omzet', data: rows.map(row => Number(row.revenue || 0)) }], colors: [color],
            plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '48%' } },
            xaxis: { categories: rows.map(row => row.label), labels: { formatter: compactMoney } },
            tooltip: { y: { formatter: money } },
        });
    }

    function renderSummaryList(selector, rows, countLabel) {
        $(selector).innerHTML = rows.length ? rows.map(row => `<div class="ro-summary-row"><div><strong>${escapeHtml(row.label)}</strong><small>${number(row.transactions)} ${countLabel}</small></div><span>${money(row.revenue)}<small>${percent(row.contribution_percent)}</small></span></div>`).join('') : '<div class="ro-empty">Belum ada data.</div>';
    }

    function renderHourly(data) {
        const rows = data.rows || [];
        $('#roPeakHour').textContent = data.busy_hour
            ? `Paling ramai pukul ${data.busy_hour} · ${number(data.busy_transactions)} transaksi`
            : 'Belum ada jam ramai pada periode ini.';
        $('#roPeakRevenue').textContent = data.peak_hour
            ? `Omzet tertinggi pukul ${data.peak_hour} · ${money(data.peak_revenue)}`
            : '';
        mountChart('hourly', '#roHourlyChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 285 },
            series: [{ name: 'Omzet', data: rows.map(row => Number(row.revenue || 0)) }, { name: 'Transaksi', data: rows.map(row => Number(row.transactions || 0)) }],
            colors: ['#e78a33','#3478f6'], plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            xaxis: { categories: rows.map(row => row.label), labels: { rotate: -45, hideOverlappingLabels: true } },
            yaxis: [{ labels: { formatter: compactMoney } }, { opposite: true, labels: { formatter: number } }],
            tooltip: { shared: true, intersect: false, y: { formatter: (value, ctx) => ctx.seriesIndex === 1 ? `${number(value)} transaksi` : money(value) } },
        });
    }

    function renderMarketBasket(data) {
        const rows = data.rows || [];
        const summary = data.summary || {};
        $('#roMarketBasketCount').textContent = `${number(rows.length)} pasangan`;
        $('#roMarketBasketSummary').textContent = summary.leading_pair
            ? `${summary.leading_pair} menjadi pasangan teratas dari ${number(summary.transactions_analyzed)} transaksi yang dianalisis.`
            : `${number(summary.transactions_analyzed)} transaksi dianalisis; belum ditemukan pembelian dua produk berbeda dalam satu struk.`;
        $('#roMarketBasketBody').innerHTML = rows.length ? rows.map(row => {
            const strengthClass = String(row.strength || '').toLowerCase();
            return `<tr>
                <td><span class="ro-basket-pair"><span><b>A</b><strong>${escapeHtml(row.product_a.name)}</strong><small>${escapeHtml(row.product_a.code || '-')}</small></span><i class="mdi mdi-plus"></i><span><b>B</b><strong>${escapeHtml(row.product_b.name)}</strong><small>${escapeHtml(row.product_b.code || '-')}</small></span></span></td>
                <td class="text-end"><strong>${number(row.pair_transactions)}</strong></td>
                <td class="text-end">${percent(row.support_percent)}</td>
                <td class="text-end">${percent(row.confidence_a_to_b_percent)}</td>
                <td class="text-end">${percent(row.confidence_b_to_a_percent)}</td>
                <td class="text-end"><strong>${number(row.lift)}</strong></td>
                <td><span class="ro-association is-${escapeHtml(strengthClass)}">${escapeHtml(row.strength)}</span></td>
            </tr>`;
        }).join('') : emptyRow(7, 'Belum ada pasangan produk untuk filter dan periode ini.');
    }

    function renderWeekdays(data) {
        const rows = data.rows || [];
        $('#roPeakDay').textContent = data.peak_day ? `${data.peak_day} tertinggi · ${money(data.peak_revenue)}` : 'Belum ada hari dengan omzet.';
        mountChart('weekdays', '#roWeekdayChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 245 },
            series: [{ name: 'Omzet', data: rows.map(row => Number(row.revenue || 0)) }], colors: ['#d95468'],
            plotOptions: { bar: { borderRadius: 5, columnWidth: '50%' } }, xaxis: { categories: rows.map(row => row.label) },
            yaxis: { labels: { formatter: compactMoney } }, tooltip: { y: { formatter: money } },
        });
        $('#roWeekdayBody').innerHTML = rows.length ? rows.map(row => `<tr class="${row.label === data.peak_day ? 'table-success' : ''}"><td><strong>${escapeHtml(row.label)}</strong></td><td class="text-end">${money(row.revenue)}</td><td class="text-end">${money(row.average_revenue)}</td><td class="text-end">${number(row.transactions)}</td></tr>`).join('') : emptyRow(4, 'Belum ada data hari.');
    }

    function renderCashiers(rows) {
        $('#roCashierBody').innerHTML = rows.length ? rows.map((row, index) => `<tr><td><strong>${index + 1}. ${escapeHtml(row.name)}</strong></td><td class="text-end">${number(row.transactions)}</td><td class="text-end">${number(row.qty)}</td><td class="text-end"><strong>${money(row.revenue)}</strong></td><td class="text-end">${money(row.average_transaction)}</td><td class="text-end">${percent(row.contribution_percent)}</td></tr>`).join('') : emptyRow(6, 'Belum ada kontribusi kasir.');
    }

    function renderTarget(target) {
        $('#roTargetAmount').textContent = money(target.amount);
        $('#roTargetRealization').textContent = money(target.realization);
        $('#roTargetRemaining').textContent = money(target.remaining);
        $('#roTargetPercent').textContent = percent(target.achievement_percent);
        $('#roTargetProgress').style.width = `${Math.min(100, Math.max(0, Number(target.achievement_percent || 0)))}%`;
        const status = $('#roTargetStatus');
        status.textContent = target.status || 'Belum Diatur';
        status.className = `ro-status ${target.status === 'Target Tercapai' ? 'achieved' : target.status === 'Hampir Tercapai' ? 'near' : target.status === 'Perlu Perhatian' ? 'attention' : ''}`;
        $('#roTargetInput').value = Number(target.amount || 0) || '';
        $('#roTargetInput').disabled = !target.can_edit;
        $('#roTargetForm button').disabled = !target.can_edit;
        $('#roTargetHint').textContent = target.can_edit
            ? 'Target disimpan khusus untuk cabang dan rentang tanggal aktif.'
            : 'Pilih satu cabang untuk mengatur target; tampilan semua cabang menjumlahkan target yang cocok.';
    }

    function renderInsights(rows) {
        $('#roInsightList').innerHTML = rows.map(row => `<article class="ro-insight tone-${escapeHtml(row.tone)}"><span><i class="mdi ${escapeHtml(row.icon)}"></i></span><div><h3>${escapeHtml(row.title)}</h3><p>${escapeHtml(row.text)}</p></div></article>`).join('');
    }

    function emptyRow(columns, message) { return `<tr><td colspan="${columns}"><div class="ro-empty">${escapeHtml(message)}</div></td></tr>`; }

    function syncSegments(meta) {
        $$('#roTrendControls button').forEach(button => button.classList.toggle('is-active', button.dataset.granularity === String(meta.granularity || 'day')));
        $$('#roTopControls button').forEach(button => button.classList.toggle('is-active', button.dataset.top === String(meta.top || '10')));
        $$('#roProductMetricControls button').forEach(button => button.classList.toggle('is-active', button.dataset.productMetric === String(meta.product_metric || 'revenue')));
        const metricCopy = {
            revenue: 'Ranking berdasarkan omzet bersih setelah retur.',
            qty: 'Ranking berdasarkan qty terjual setelah dikurangi retur.',
            transactions: 'Ranking berdasarkan jumlah transaksi unik yang memuat produk.',
        };
        $('#roProductMetricCopy').textContent = metricCopy[meta.product_metric] || metricCopy.revenue;
    }

    function renderError(message) {
        ['#roTrendChart','#roCategoryChart','#roTypeChart','#roPaymentChart','#roHourlyChart','#roWeekdayChart'].forEach(selector => { $(selector).innerHTML = `<div class="ro-empty">${escapeHtml(message)}</div>`; });
        $('#roFastMovingBody').innerHTML = emptyRow(8, message);
        $('#roMarketBasketBody').innerHTML = emptyRow(7, message);
    }

    function applyPreset(range) {
        const now = new Date(); let start = new Date(now); let end = new Date(now); let granularity = 'day';
        if (range === '7' || range === '30') start.setDate(now.getDate() - Number(range) + 1);
        if (range === 'mtd') start = new Date(now.getFullYear(), now.getMonth(), 1);
        if (range === 'last-month') { start = new Date(now.getFullYear(), now.getMonth() - 1, 1); end = new Date(now.getFullYear(), now.getMonth(), 0); }
        if (range === 'ytd') { start = new Date(now.getFullYear(), 0, 1); granularity = 'month'; }
        setPresetActive(range);
        if (range === 'custom') { $('#roDateStart').focus(); return; }
        $('#roDateStart').value = dateValue(start); $('#roDateEnd').value = dateValue(end); $('#roGranularity').value = granularity;
        state.filterError = false;
        syncFilterState();
        load();
    }

    form.addEventListener('submit', event => { event.preventDefault(); load(); });
    form.addEventListener('change', event => {
        state.filterError = false;
        if (event.target.matches('#roDateStart, #roDateEnd')) setPresetActive('custom');
        syncFilterState();
    });
    form.addEventListener('input', event => {
        if (!event.target.matches('input')) return;
        state.filterError = false;
        if (event.target.matches('#roDateStart, #roDateEnd')) setPresetActive('custom');
        syncFilterState();
    });
    $$('.ro-presets button').forEach(button => button.addEventListener('click', () => applyPreset(button.dataset.range)));
    $$('#roTrendControls button').forEach(button => button.addEventListener('click', () => {
        if ($('#roGranularity').value === button.dataset.granularity) return;
        $('#roGranularity').value = button.dataset.granularity;
        $$('#roTrendControls button').forEach(item => item.classList.toggle('is-active', item === button));
        load();
    }));
    $$('#roTopControls button').forEach(button => button.addEventListener('click', () => {
        if ($('#roTop').value === button.dataset.top) return;
        $('#roTop').value = button.dataset.top;
        $$('#roTopControls button').forEach(item => item.classList.toggle('is-active', item === button));
        load();
    }));
    $$('#roProductMetricControls button').forEach(button => button.addEventListener('click', () => {
        if ($('#roProductMetric').value === button.dataset.productMetric) return;
        $('#roProductMetric').value = button.dataset.productMetric;
        $$('#roProductMetricControls button').forEach(item => item.classList.toggle('is-active', item === button));
        load();
    }));
    $('#roReset').addEventListener('click', () => {
        form.reset(); $('#roDateStart').value = app.dataset.defaultStart; $('#roDateEnd').value = app.dataset.defaultEnd;
        setPresetActive('30'); state.filterError = false; syncFilterState(); load();
    });
    $('#roFilterToggle').addEventListener('click', () => {
        const body = $('#roFilterBody');
        const collapsed = !body.classList.contains('is-collapsed');
        body.classList.toggle('is-collapsed', collapsed);
        $('#roFilterPanel').classList.toggle('is-collapsed', collapsed);
        $('#roFilterToggle').setAttribute('aria-expanded', String(!collapsed));
        $('#roFilterToggle').setAttribute('title', collapsed ? 'Buka filter' : 'Ringkas filter');
        $('span', $('#roFilterToggle')).textContent = collapsed ? 'Buka' : 'Ringkas';
        body.setAttribute('aria-hidden', String(collapsed));
        body.inert = collapsed;
    });
    $('#roExportExcel').addEventListener('click', () => { window.location.href = `${app.dataset.excelUrl}?${params()}`; });
    $('#roExportPdf').addEventListener('click', () => { window.location.href = `${app.dataset.pdfUrl}?${params()}`; });
    $('#roPrint').addEventListener('click', () => window.print());
    $('#roTargetForm').addEventListener('submit', async event => {
        event.preventDefault();
        const branchId = $('#roBranch').value;
        if (!branchId) return showToast('Pilih satu cabang sebelum menyimpan target.', true);
        const button = $('#roTargetForm button'); button.disabled = true;
        try {
            const response = await fetch(app.dataset.targetUrl, {
                method: 'POST', headers: { Accept:'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'X-Requested-With':'XMLHttpRequest' },
                body: JSON.stringify({ branch_id: branchId, date_start: $('#roDateStart').value, date_end: $('#roDateEnd').value, target_amount: $('#roTargetInput').value || 0 }),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(firstError(payload) || 'Target omzet gagal disimpan.');
            showToast(payload.message); await load();
        } catch (error) { showToast(error.message, true); }
        finally { button.disabled = false; }
    });

    load();
})();
</script>
