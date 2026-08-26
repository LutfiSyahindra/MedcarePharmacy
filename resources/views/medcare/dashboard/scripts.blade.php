<script>
    (() => {
        'use strict';

        const initialDashboard = @json($dashboard);
        const root = document.getElementById('commandCenter');
        if (!root) return;

        const state = {
            data: initialDashboard,
            period: initialDashboard.meta.period || '30',
            severity: '',
            charts: { revenue: null, inventory: null, payment: null },
            loading: false
        };
        const paymentColors = ['#315ca7', '#38a189', '#7559bb', '#dd7441', '#3186bd', '#d58a27', '#d64c60', '#8b96a8'];
        const inventoryMeta = [
            { key: 'healthy', label: 'Aman', color: '#2a9a7b' },
            { key: 'low', label: 'Menipis', color: '#d99a36' },
            { key: 'empty', label: 'Kosong', color: '#d64c60' },
            { key: 'near_expiry', label: 'Akan ED', color: '#dd7441' },
            { key: 'expired', label: 'Kedaluwarsa', color: '#8c4e73' }
        ];

        const element = id => document.getElementById(id);
        const escapeHtml = value => String(value ?? '')
            .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        const safeUrl = value => {
            try {
                const url = new URL(String(value || '#'), window.location.origin);
                return url.origin === window.location.origin ? escapeHtml(url.pathname + url.search + url.hash) : '#';
            } catch (error) { return '#'; }
        };
        const number = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
        const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
        const compactMoney = value => {
            const amount = Number(value || 0);
            const absolute = Math.abs(amount);
            if (absolute >= 1e12) return `Rp ${number(amount / 1e12)} triliun`;
            if (absolute >= 1e9) return `Rp ${number(amount / 1e9)} miliar`;
            if (absolute >= 1e6) return `Rp ${number(amount / 1e6)} juta`;
            if (absolute >= 1e3) return `Rp ${number(amount / 1e3)} ribu`;
            return money(amount);
        };
        const emptyBlock = (title, description, icon = 'mdi-database-off-outline') => `
            <div class="cc-empty-block"><i class="mdi ${icon}"></i><strong>${escapeHtml(title)}</strong><span>${escapeHtml(description)}</span></div>`;

        function renderDashboard(data) {
            state.data = data;
            element('ccScopeLabel').textContent = data.meta.branch_label;
            element('ccRangeLabel').textContent = data.meta.range_label;
            element('ccGeneratedLabel').textContent = `Diperbarui ${data.meta.generated_label}`;
            element('ccDateStart').value = data.meta.start_date;
            element('ccDateEnd').value = data.meta.end_date;
            element('ccAlertTotal').textContent = number(data.alerts.total);

            renderKpis(data.kpis);
            renderAnalysis(data);
            renderActionCenter(data.action_center);
            renderRevenueChart(data.trend);
            renderAlerts(data.alerts);
            renderInventory(data.inventory);
            renderPaymentMix(data.payment_mix);
            renderTopProducts(data.top_products);
            renderBranches(data.branches);
            renderActivity(data.recent_activity);
        }

        function renderKpis(rows) {
            element('ccKpiGrid').innerHTML = rows.length ? rows.map(kpi => {
                const formatted = kpi.format === 'currency' ? compactMoney(kpi.value) : number(kpi.value);
                const exact = kpi.format === 'currency' ? money(kpi.value) : number(kpi.value);
                const change = kpi.change ? `
                    <span class="cc-kpi-change is-${escapeHtml(kpi.change.direction)}">
                        <i class="mdi ${kpi.change.direction === 'up' ? 'mdi-trending-up' : (kpi.change.direction === 'down' ? 'mdi-trending-down' : 'mdi-minus')}"></i>
                        ${number(kpi.change.value)}%
                    </span>` : '<span class="cc-kpi-change"><i class="mdi mdi-circle-small"></i> Saat ini</span>';

                return `<article class="cc-kpi-card is-${escapeHtml(kpi.tone)}" title="${escapeHtml(exact)}">
                    <div class="cc-kpi-top"><span class="cc-kpi-icon"><i class="mdi ${escapeHtml(kpi.icon)}"></i></span>${change}</div>
                    <small>${escapeHtml(kpi.label)}</small>
                    <div class="cc-kpi-value">${escapeHtml(formatted)}</div>
                    <div class="cc-kpi-meta"><i class="mdi mdi-information-outline"></i>${escapeHtml(kpi.meta)}</div>
                </article>`;
            }).join('') : emptyBlock('Belum ada KPI', 'Akun belum memiliki data cabang untuk ditampilkan.');
        }

        function renderAnalysis(data) {
            if (!(data.kpis || []).length) {
                element('ccAnalysisGrid').innerHTML = emptyBlock('Analisis belum tersedia', 'Hubungkan akun ke cabang untuk melihat sinyal bisnis utama.');
                return;
            }

            const kpis = Object.fromEntries((data.kpis || []).map(kpi => [kpi.key, kpi]));
            const netSales = Number(kpis.net_sales?.value || 0);
            const grossProfit = Number(kpis.gross_profit?.value || 0);
            const receivables = Number(kpis.receivables?.value || 0);
            const payables = Number(kpis.payables?.value || 0);
            const salesChange = kpis.net_sales?.change;
            const margin = netSales !== 0 ? (grossProfit / netSales) * 100 : 0;
            const inventory = data.inventory || {};
            const inventoryTotal = ['healthy', 'low', 'empty', 'near_expiry', 'expired']
                .reduce((sum, key) => sum + Number(inventory[key] || 0), 0);
            const inventoryRisk = ['low', 'empty', 'near_expiry', 'expired']
                .reduce((sum, key) => sum + Number(inventory[key] || 0), 0);
            const riskShare = inventoryTotal > 0 ? (inventoryRisk / inventoryTotal) * 100 : 0;
            const netBilling = receivables - payables;
            const salesDirection = salesChange?.direction || 'flat';
            const salesPrefix = salesDirection === 'up' ? '+' : (salesDirection === 'down' ? '-' : '');
            const salesDescription = salesDirection === 'up'
                ? 'Omzet tumbuh dibanding periode sebelumnya dengan durasi sama.'
                : (salesDirection === 'down'
                    ? 'Omzet menurun dibanding periode sebelumnya; cek tren harian dan produk utama.'
                    : 'Omzet relatif stabil dibanding periode sebelumnya dengan durasi sama.');
            const billingLabel = netBilling > 0 ? 'Net piutang' : (netBilling < 0 ? 'Net hutang' : 'Posisi seimbang');

            const rows = [
                {
                    label: 'Momentum omzet',
                    value: salesChange ? `${salesPrefix}${number(salesChange.value)}%` : 'Belum tersedia',
                    description: salesDescription,
                    icon: 'mdi-chart-line',
                    tone: salesDirection === 'up' ? 'success' : (salesDirection === 'down' ? 'danger' : 'info')
                },
                {
                    label: 'Margin kotor',
                    value: `${number(margin)}%`,
                    description: `${compactMoney(grossProfit)} laba kotor dari ${compactMoney(netSales)} omzet bersih.`,
                    icon: 'mdi-finance',
                    tone: 'primary'
                },
                {
                    label: 'Produk berisiko',
                    value: `${number(inventoryRisk)} / ${number(inventoryTotal)}`,
                    description: `${number(riskShare)}% produk-cabang menipis, kosong, mendekati ED, atau kedaluwarsa.`,
                    icon: 'mdi-package-variant-closed-minus',
                    tone: inventoryRisk > 0 ? 'warning' : 'success'
                },
                {
                    label: billingLabel,
                    value: compactMoney(Math.abs(netBilling)),
                    description: `${compactMoney(receivables)} piutang dibanding ${compactMoney(payables)} hutang supplier.`,
                    icon: 'mdi-cash-multiple',
                    tone: netBilling < 0 ? 'danger' : (netBilling > 0 ? 'violet' : 'success')
                }
            ];

            element('ccAnalysisGrid').innerHTML = rows.map(row => `
                <article class="cc-analysis-card is-${escapeHtml(row.tone)}">
                    <span class="cc-analysis-icon"><i class="mdi ${escapeHtml(row.icon)}"></i></span>
                    <span class="cc-analysis-copy">
                        <small>${escapeHtml(row.label)}</small>
                        <strong>${escapeHtml(row.value)}</strong>
                        <span>${escapeHtml(row.description)}</span>
                    </span>
                </article>`).join('');
        }

        function renderActionCenter(rows) {
            element('ccActionCenter').innerHTML = rows.length ? rows.map(row => `
                <a href="${safeUrl(row.url)}" class="cc-action-card is-${escapeHtml(row.tone)}">
                    <span class="cc-action-icon"><i class="mdi ${escapeHtml(row.icon)}"></i></span>
                    <span class="cc-action-copy"><strong>${escapeHtml(row.label)}</strong><span>${escapeHtml(row.description)}</span></span>
                    <b class="cc-action-count">${number(row.count)}</b>
                </a>`).join('') : emptyBlock('Tidak ada tindakan', 'Belum ada pekerjaan prioritas pada ruang pantau ini.', 'mdi-check-decagram-outline');
        }

        function renderRevenueChart(trend) {
            if (state.charts.revenue) state.charts.revenue.destroy();
            const target = element('ccRevenueChart');
            target.innerHTML = '';
            if (typeof ApexCharts === 'undefined') {
                target.innerHTML = emptyBlock('Grafik tidak tersedia', 'Komponen grafik gagal dimuat.');
                return;
            }

            state.charts.revenue = new ApexCharts(target, {
                chart: { type: 'line', height: 315, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif', animations: { speed: 450 } },
                series: [
                    { name: 'Omzet bersih', type: 'area', data: trend.values || [] },
                    { name: 'Transaksi', type: 'line', data: trend.counts || [] }
                ],
                colors: ['#315ca7', '#48aa98'],
                stroke: { curve: 'smooth', width: [2.6, 2.1] },
                fill: { type: ['gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: .3, opacityTo: .025, stops: [0, 90, 100] } },
                dataLabels: { enabled: false },
                markers: { size: 0, hover: { size: 4 } },
                grid: { borderColor: '#edf0f5', strokeDashArray: 4, padding: { left: 8, right: 10 } },
                xaxis: {
                    categories: trend.labels || [], tickAmount: Math.min(7, (trend.labels || []).length),
                    labels: { style: { colors: '#7d899b', fontSize: '10px', fontWeight: 600 }, formatter: shortDate }
                },
                yaxis: [
                    { labels: { style: { colors: '#7d899b', fontSize: '10px', fontWeight: 600 }, formatter: value => compactAxis(value) } },
                    { opposite: true, min: 0, forceNiceScale: true, labels: { style: { colors: '#7d899b', fontSize: '10px', fontWeight: 600 }, formatter: value => Math.round(value) } }
                ],
                tooltip: {
                    shared: true, intersect: false,
                    x: { formatter: (_, context) => longDate((trend.labels || [])[context.dataPointIndex]) },
                    y: [{ formatter: value => money(value) }, { formatter: value => `${number(value)} transaksi` }]
                },
                legend: { show: false }
            });
            state.charts.revenue.render();
        }

        function renderAlerts(alerts) {
            element('ccAlertAllCount').textContent = number(alerts.total);
            element('ccAlertCriticalCount').textContent = number(alerts.critical);
            element('ccAlertWarningCount').textContent = number(alerts.warning);
            element('ccAlertInfoCount').textContent = number(alerts.info);
            const rows = (alerts.items || []).filter(row => !state.severity || row.severity === state.severity);

            element('ccAlertList').innerHTML = rows.length ? rows.map(row => `
                <a class="cc-alert-item is-${escapeHtml(row.severity)}" href="${safeUrl(row.action_url)}" title="${escapeHtml(row.action_label)}">
                    <span class="cc-alert-item-icon"><i class="mdi ${escapeHtml(row.icon)}"></i></span>
                    <span class="cc-alert-copy"><strong>${escapeHtml(row.title)}</strong><span>${escapeHtml(row.description)}</span><small>${escapeHtml(row.metric)}</small></span>
                    <i class="mdi mdi-chevron-right cc-alert-arrow"></i>
                </a>`).join('') : emptyBlock('Tidak ada alert', 'Semua kondisi pada kategori ini terlihat aman.', 'mdi-shield-check-outline');
        }

        function renderInventory(inventory) {
            const series = inventoryMeta.map(item => Number(inventory[item.key] || 0));
            element('ccInventoryLegend').innerHTML = inventoryMeta.map((item, index) => `
                <div class="cc-inventory-row"><i class="cc-legend-dot" style="background:${item.color}"></i><span>${item.label}</span><b>${number(series[index])}</b></div>`).join('');
            renderDonut('inventory', 'ccInventoryChart', series, inventoryMeta.map(item => item.label), inventoryMeta.map(item => item.color), 'Produk', number(series.reduce((sum, value) => sum + value, 0)));
        }

        function renderPaymentMix(rows) {
            const total = rows.reduce((sum, row) => sum + Number(row.value || 0), 0);
            element('ccPaymentList').innerHTML = rows.length ? rows.slice(0, 6).map((row, index) => `
                <div class="cc-payment-row">
                    <i class="cc-payment-color" style="background:${paymentColors[index % paymentColors.length]}"></i>
                    <span>${escapeHtml(row.label)}<small>${total > 0 ? number((Number(row.value) / total) * 100) : 0}% dari pembayaran</small></span>
                    <b>${escapeHtml(compactMoney(row.value))}</b>
                </div>`).join('') : emptyBlock('Belum ada pembayaran', 'Metode pembayaran muncul setelah transaksi selesai.', 'mdi-wallet-outline');
            renderDonut('payment', 'ccPaymentChart', rows.map(row => Number(row.value || 0)), rows.map(row => row.label), paymentColors, 'Total', compactMoney(total));
        }

        function renderDonut(key, targetId, series, labels, colors, centerLabel, centerValue) {
            if (state.charts[key]) state.charts[key].destroy();
            const target = element(targetId);
            target.innerHTML = '';
            if (typeof ApexCharts === 'undefined') return;
            const safeSeries = series.some(value => value > 0) ? series : [1];
            const safeLabels = series.some(value => value > 0) ? labels : ['Belum ada data'];
            const safeColors = series.some(value => value > 0) ? colors : ['#e8ebf0'];
            state.charts[key] = new ApexCharts(target, {
                chart: { type: 'donut', height: 210, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                series: safeSeries, labels: safeLabels, colors: safeColors,
                stroke: { width: 3, colors: ['#fff'] },
                dataLabels: { enabled: false }, legend: { show: false },
                plotOptions: { pie: { donut: { size: '72%', labels: { show: true, name: { show: true, offsetY: 17, color: '#7d899b', fontSize: '10px', fontWeight: 600 }, value: { show: true, offsetY: -8, color: '#30415b', fontSize: '16px', fontWeight: 800, formatter: () => centerValue }, total: { show: true, label: centerLabel, color: '#7d899b', fontSize: '10px', fontWeight: 600, formatter: () => centerValue } } } } },
                tooltip: { y: { formatter: value => key === 'payment' ? money(value) : `${number(value)} produk` } }
            });
            state.charts[key].render();
        }

        function renderTopProducts(rows) {
            element('ccTopProducts').innerHTML = rows.length ? rows.map(row => `
                <div class="cc-ranking-item">
                    <span class="cc-rank ${row.rank === 1 ? 'is-top' : ''}">${number(row.rank)}</span>
                    <span class="cc-ranking-copy"><strong>${escapeHtml(row.name)}</strong><small>${escapeHtml(row.code)} · ${number(row.qty)} ${escapeHtml(row.unit)}</small><span class="cc-progress"><i style="width:${Math.max(0, Number(row.progress || 0))}%"></i></span></span>
                    <span class="cc-ranking-value"><strong>${escapeHtml(compactMoney(row.revenue))}</strong><small>omzet bersih</small></span>
                </div>`).join('') : emptyBlock('Belum ada produk terjual', 'Ranking akan muncul dari transaksi pada periode ini.', 'mdi-trophy-outline');
        }

        function renderBranches(rows) {
            element('ccBranchPerformance').innerHTML = rows.length ? rows.map(row => `
                <div class="cc-branch-item">
                    <span class="cc-branch-avatar ${row.active ? '' : 'is-inactive'}">${escapeHtml(initials(row.code || row.name))}</span>
                    <span class="cc-branch-copy"><strong>${escapeHtml(row.name)}</strong><small>${number(row.transactions)} transaksi · ${number(row.share)}% kontribusi</small><span class="cc-progress"><i style="width:${Math.max(0, Number(row.share || 0))}%"></i></span></span>
                    <span class="cc-branch-value"><strong>${escapeHtml(compactMoney(row.net_sales))}</strong><small>#${number(row.rank)} cabang</small></span>
                </div>`).join('') : emptyBlock('Belum ada performa cabang', 'Tidak ada transaksi selesai pada periode ini.', 'mdi-store-outline');
        }

        function renderActivity(rows) {
            element('ccRecentActivity').innerHTML = rows.length ? rows.map(row => `
                <a class="cc-activity-item is-${escapeHtml(row.tone)}" href="${safeUrl(row.url)}">
                    <span class="cc-activity-icon"><i class="mdi ${escapeHtml(row.icon)}"></i></span>
                    <span class="cc-activity-copy"><strong>${escapeHtml(row.title)}</strong><span>${escapeHtml(row.description)}</span><small>${escapeHtml(row.time)} · <b>${escapeHtml(compactMoney(row.amount))}</b></small></span>
                </a>`).join('') : emptyBlock('Belum ada aktivitas', 'Transaksi terbaru pada periode ini akan muncul di sini.', 'mdi-pulse');
        }

        async function loadDashboard() {
            if (state.loading) return;
            const branchId = element('ccBranchFilter').value;
            const params = new URLSearchParams({ period: state.period });
            if (branchId) params.set('branch_id', branchId);
            if (state.period === 'custom') {
                params.set('date_start', element('ccDateStart').value);
                params.set('date_end', element('ccDateEnd').value);
            }

            setLoading(true);
            element('ccSystemMessage').hidden = true;
            try {
                const response = await fetch(`${root.dataset.endpoint}?${params.toString()}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const payload = await response.json();
                if (!response.ok) throw new Error(firstError(payload) || 'Data dashboard gagal diperbarui.');
                renderDashboard(payload.dashboard);
                const browserUrl = new URL(window.location.href);
                browserUrl.search = params.toString();
                window.history.replaceState({}, '', browserUrl);
            } catch (error) {
                element('ccSystemMessage').textContent = error.message || 'Data dashboard gagal diperbarui.';
                element('ccSystemMessage').hidden = false;
            } finally {
                setLoading(false);
            }
        }

        function setLoading(loading) {
            state.loading = loading;
            element('ccRefreshButton').classList.toggle('is-loading', loading);
            element('ccRefreshButton').disabled = loading;
            root.classList.toggle('cc-loading-overlay', loading);
        }

        function firstError(payload) {
            if (payload?.message) return payload.message;
            const errors = payload?.errors || {};
            return Object.values(errors).flat()[0] || '';
        }

        function shortDate(value) {
            if (!value) return '';
            return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short' }).format(new Date(`${value}T00:00:00`));
        }
        function longDate(value) {
            if (!value) return '';
            return new Intl.DateTimeFormat('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${value}T00:00:00`));
        }
        function compactAxis(value) {
            const amount = Number(value || 0);
            if (Math.abs(amount) >= 1e9) return `${number(amount / 1e9)}M`;
            if (Math.abs(amount) >= 1e6) return `${number(amount / 1e6)}jt`;
            if (Math.abs(amount) >= 1e3) return `${number(amount / 1e3)}rb`;
            return number(amount);
        }
        function initials(value) {
            return String(value || 'CB').split(/[\s-]+/).filter(Boolean).slice(0, 2).map(word => word[0]).join('').toUpperCase();
        }

        element('ccPeriodTabs').addEventListener('click', event => {
            const button = event.target.closest('[data-period]');
            if (!button) return;
            state.period = button.dataset.period;
            element('ccPeriodTabs').querySelectorAll('[data-period]').forEach(item => item.classList.toggle('is-active', item === button));
            element('ccCustomRange').classList.toggle('is-open', state.period === 'custom');
            if (state.period !== 'custom') loadDashboard();
        });
        element('ccBranchFilter').addEventListener('change', loadDashboard);
        element('ccRefreshButton').addEventListener('click', loadDashboard);
        element('ccApplyCustomRange').addEventListener('click', () => {
            const start = element('ccDateStart').value;
            const end = element('ccDateEnd').value;
            if (!start || !end || start > end) {
                element('ccSystemMessage').textContent = 'Pilih rentang tanggal yang valid.';
                element('ccSystemMessage').hidden = false;
                return;
            }
            loadDashboard();
        });
        element('ccAlertTabs').addEventListener('click', event => {
            const button = event.target.closest('[data-severity]');
            if (!button) return;
            state.severity = button.dataset.severity;
            element('ccAlertTabs').querySelectorAll('[data-severity]').forEach(item => item.classList.toggle('is-active', item === button));
            renderAlerts(state.data.alerts);
        });

        renderDashboard(initialDashboard);
        window.setInterval(() => document.visibilityState === 'visible' && loadDashboard(), 300000);
    })();
</script>
