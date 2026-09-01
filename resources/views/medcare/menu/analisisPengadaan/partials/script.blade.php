<script>
(() => {
    'use strict';

    const root = document.getElementById('procurementAnalysisApp');
    if (!root) return;

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
    const state = { charts: {}, controller: null, initializedOptions: false, analysis: null, toastTimer: null };
    const colors = ['#3277e6', '#0e9384', '#7656d6', '#d28b17', '#d24f55', '#178aa4', '#70ad63'];
    const money = value => `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value || 0))}`;
    const number = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
    const percent = value => `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(Number(value || 0))}%`;
    const compactMoney = value => {
        const amount = Number(value || 0);
        if (Math.abs(amount) >= 1e9) return `Rp ${(amount / 1e9).toFixed(1)} M`;
        if (Math.abs(amount) >= 1e6) return `Rp ${(amount / 1e6).toFixed(1)} jt`;
        if (Math.abs(amount) >= 1e3) return `Rp ${(amount / 1e3).toFixed(0)} rb`;
        return money(amount);
    };
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char]));
    const formatDate = value => {
        if (!value) return '-';
        const parts = String(value).slice(0, 10).split('-');
        return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : value;
    };
    const dateInput = date => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    const emptyRow = (columns, message = 'Belum ada data pada periode aktif.') => `<tr><td colspan="${columns}"><div class="pa-empty"><i class="mdi mdi-database-off-outline"></i>${escapeHtml(message)}</div></td></tr>`;
    const medicineLink = row => `<button type="button" class="pa-medicine-link" data-medicine="${Number(row.id)}">${escapeHtml(row.name)}</button>`;

    function queryString() {
        const params = new URLSearchParams();
        new FormData($('#paFilterForm')).forEach((value, key) => {
            if (String(value).trim() !== '') params.set(key, value);
        });
        return params.toString();
    }

    async function requestJson(url, signal) {
        const response = await fetch(url, { headers: { Accept: 'application/json' }, signal });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const errors = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
            throw new Error(errors || payload.message || 'Data analisis tidak dapat dimuat.');
        }
        return payload;
    }

    async function loadData() {
        if (state.controller) state.controller.abort();
        state.controller = new AbortController();
        setLoading(true);
        try {
            const separator = root.dataset.url.includes('?') ? '&' : '?';
            const payload = await requestJson(`${root.dataset.url}${separator}${queryString()}`, state.controller.signal);
            state.analysis = payload.analysis || {};
            render(state.analysis);
            setLoading(false);
        } catch (error) {
            if (error.name === 'AbortError') return;
            setLoading(false, error.message);
            toast(error.message, true);
        }
    }

    function setLoading(loading, error = '') {
        const status = $('#paFilterStatus');
        const apply = $('#paApply');
        apply.disabled = loading;
        if (loading) {
            status.className = 'pa-filter-status is-loading';
            status.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mengolah analisis...';
            apply.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Memuat...';
        } else if (error) {
            status.className = 'pa-filter-status is-error';
            status.innerHTML = '<i class="mdi mdi-alert-circle-outline"></i> Gagal memuat';
            apply.innerHTML = '<i class="mdi mdi-filter-check"></i> Terapkan Filter';
        } else {
            status.className = 'pa-filter-status';
            status.innerHTML = '<i class="mdi mdi-check-decagram-outline"></i> Data diperbarui';
            apply.innerHTML = '<i class="mdi mdi-filter-check"></i> Terapkan Filter';
        }
    }

    function render(analysis) {
        renderOptions(analysis.options || {});
        const meta = analysis.meta || {};
        $('#paHeroBranch').textContent = meta.branch_label || '-';
        $('#paHeroPeriod').textContent = meta.period_label || '-';
        $('#paGeneratedAt').textContent = meta.generated_at || '-';
        $('#paActiveFilters').textContent = (meta.active_filters || []).length ? meta.active_filters.join(' · ') : 'Tanpa filter tambahan';
        renderKpis(analysis.summary || {});
        renderCharts(analysis);
        renderRankings(analysis);
        renderMedicines(analysis.medicines || []);
        renderPriceChanges(analysis.price_changes || []);
        renderReorders(analysis.reorder || []);
        renderMoving(analysis.moving_distribution || []);
        renderSuppliers(analysis.suppliers || []);
        renderCategories(analysis.categories || []);
        renderOutstanding(analysis.outstanding || []);
        renderCancelled(analysis.cancelled_orders || []);
        renderNeed(analysis.need_analysis || []);
        renderStatusList(analysis.status_distribution || []);
        renderInsights(analysis.insights || []);
    }

    function renderOptions(options) {
        const definitions = [
            ['#paBranch', options.branches, row => row.id, row => `${row.name}${row.code ? ` · ${row.code}` : ''}`],
            ['#paSupplier', options.suppliers, row => row.id, row => row.name],
            ['#paMedicine', options.medicines, row => row.id, row => `${row.name}${row.code ? ` · ${row.code}` : ''}`],
            ['#paCategory', options.categories, row => row.id, row => row.name],
            ['#paClassification', options.classifications, row => row.id, row => row.name],
            ['#paManufacturer', options.manufacturers, row => row.id, row => row.name],
            ['#paPoStatus', options.po_statuses, row => row.key, row => row.label],
            ['#paReceiptStatus', options.receipt_statuses, row => row.key, row => row.label],
            ['#paCreator', options.creators, row => row.id, row => row.name],
        ];
        definitions.forEach(([selector, rows, value, label]) => {
            if (!Array.isArray(rows)) return;
            const select = $(selector);
            const selected = select.value;
            const placeholder = select.options[0]?.textContent || 'Semua';
            select.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>` + rows.map(row => `<option value="${escapeHtml(value(row))}">${escapeHtml(label(row))}</option>`).join('');
            if ($$('option', select).some(option => option.value === selected)) select.value = selected;
        });
        state.initializedOptions = true;
    }

    function renderKpis(summary) {
        const cards = [
            ['Total Nilai Order', 'order_value', money, 'mdi-cash-multiple', `Dari ${number(summary.total_items)} baris item`, ''],
            ['Total PO', 'total_po', value => `${number(value)} PO`, 'mdi-file-document-multiple-outline', `Sebelumnya ${number(summary.total_po_previous)} PO`, 'tone-green'],
            ['Total Qty Diorder', 'ordered_qty', value => `${number(value)} unit`, 'mdi-package-variant-closed', `Satuan stok terkonversi`, 'tone-violet'],
            ['Belum Diterima', 'outstanding_value', money, 'mdi-package-variant-remove', `Nilai order outstanding`, 'tone-red'],
            ['Rata-rata Lead Time', 'lead_time_days', value => `${number(value)} hari`, 'mdi-clock-fast', `PO sampai penerimaan pertama`, 'tone-amber'],
            ['Supplier Aktif', 'active_suppliers', value => `${number(value)} supplier`, 'mdi-truck-delivery-outline', `Supplier pada periode aktif`, 'tone-teal'],
        ];
        $('#paKpiGrid').innerHTML = cards.map((card, index) => {
            const change = Number(summary[`${card[1]}_change`] || 0);
            const direction = change < 0 ? 'is-down' : '';
            const icon = change < 0 ? 'mdi-trending-down' : 'mdi-trending-up';
            return `<article class="pa-kpi ${card[5]}" style="--pa-index:'${String(index + 1).padStart(2, '0')}'">
                <span><i class="mdi ${card[3]}"></i></span><div><small>${escapeHtml(card[0])}</small><strong>${escapeHtml(card[2](summary[card[1]]))}</strong>
                <p><b class="pa-change ${direction}"><i class="mdi ${icon}"></i>${percent(Math.abs(change))}</b> vs periode lalu</p></div></article>`;
        }).join('');
    }

    function destroyChart(key) {
        if (state.charts[key]) { state.charts[key].destroy(); delete state.charts[key]; }
    }

    function mountChart(key, selector, options) {
        destroyChart(key);
        const target = $(selector);
        if (!target) return;
        target.innerHTML = '';
        if (typeof ApexCharts === 'undefined') {
            target.innerHTML = '<div class="pa-empty"><i class="mdi mdi-chart-box-outline"></i>Library grafik tidak tersedia.</div>';
            return;
        }
        state.charts[key] = new ApexCharts(target, options);
        state.charts[key].render();
    }

    const chartBase = {
        chart: { fontFamily: 'inherit', toolbar: { show: false }, animations: { speed: 350 }, foreColor: '#71848e' },
        dataLabels: { enabled: false }, grid: { borderColor: '#e8eef1', strokeDashArray: 4 }, legend: { show: false },
        noData: { text: 'Belum ada data' }, tooltip: { shared: false, intersect: false },
    };

    function renderCharts(analysis) {
        const trend = analysis.trend || {};
        mountChart('trend', '#paTrendChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'line', height: 340 },
            series: [
                { name: 'Nilai order', type: 'area', data: trend.order_value || [] },
                { name: 'Periode sebelumnya', type: 'line', data: trend.previous_order_value || [] },
            ], colors: ['#3277e6', '#9daab1'], stroke: { width: [3, 2], curve: 'smooth', dashArray: [0, 6] },
            fill: { type: ['gradient', 'solid'], opacity: [.35, 1], gradient: { opacityFrom: .42, opacityTo: .04 } }, markers: { size: [3, 2] },
            xaxis: { categories: trend.labels || [], axisBorder: { show: false }, axisTicks: { show: false }, labels: { rotate: -35 } },
            yaxis: { labels: { formatter: compactMoney } }, tooltip: { shared: true, intersect: false, y: { formatter: money } },
        });

        const top = (analysis.top_quantity || []).slice(0, 10).reverse();
        mountChart('top', '#paTopChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 340 }, series: [{ name: 'Qty order', data: top.map(row => row.ordered_qty) }],
            colors: ['#7656d6'], plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '57%' } },
            xaxis: { categories: top.map(row => row.name), labels: { formatter: number } }, yaxis: { labels: { maxWidth: 135 } },
            tooltip: { y: { formatter: value => `${number(value)} unit` } },
        });

        const suppliers = (analysis.suppliers || []).slice(0, 8);
        mountChart('suppliers', '#paSupplierChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 270 }, series: [{ name: 'Nilai order', data: suppliers.map(row => row.order_value) }],
            colors: ['#0e9384'], plotOptions: { bar: { borderRadius: 5, columnWidth: '48%' } },
            xaxis: { categories: suppliers.map(row => row.name), labels: { rotate: -35, trim: true } }, yaxis: { labels: { formatter: compactMoney } }, tooltip: { y: { formatter: money } },
        });

        const categories = (analysis.categories || []).slice(0, 8);
        mountChart('categories', '#paCategoryChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'donut', height: 270 }, series: categories.map(row => row.order_value), labels: categories.map(row => row.name), colors,
            stroke: { colors: ['#fff'], width: 3 }, plotOptions: { pie: { donut: { size: '66%', labels: { show: true, total: { show: true, label: 'Total', formatter: chart => compactMoney(chart.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
            legend: { show: true, position: 'bottom', fontSize: '9px' }, tooltip: { y: { formatter: money } },
        });

        mountChart('receipts', '#paReceiptChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 270 },
            series: [{ name: 'Diorder', data: trend.ordered_qty || [] }, { name: 'Diterima', data: trend.received_qty || [] }], colors: ['#3277e6', '#26a269'],
            plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } }, xaxis: { categories: trend.labels || [], labels: { rotate: -35 } }, yaxis: { labels: { formatter: number } },
            legend: { show: true, position: 'top', fontSize: '9px' }, tooltip: { shared: true, intersect: false, y: { formatter: value => `${number(value)} unit` } },
        });

        mountChart('sales', '#paSalesChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'line', height: 270 },
            series: [{ name: 'Qty order', data: trend.ordered_qty || [] }, { name: 'Qty penjualan', data: trend.sales_qty || [] }], colors: ['#d28b17', '#178aa4'],
            stroke: { width: [3, 3], curve: 'smooth' }, markers: { size: 2 }, xaxis: { categories: trend.labels || [], labels: { rotate: -35 } }, yaxis: { labels: { formatter: number } },
            legend: { show: true, position: 'top', fontSize: '9px' }, tooltip: { shared: true, intersect: false, y: { formatter: value => `${number(value)} unit` } },
        });

        const leads = (analysis.lead_time_suppliers || []).slice(0, 10).reverse();
        mountChart('lead', '#paLeadChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 290 }, series: [{ name: 'Lead time', data: leads.map(row => row.lead_time_days) }],
            colors: ['#178aa4'], plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '55%', dataLabels: { position: 'top' } } },
            dataLabels: { enabled: true, formatter: value => `${number(value)} hari`, offsetX: 3, style: { colors: ['#55717e'], fontSize: '9px' } },
            xaxis: { categories: leads.map(row => row.name), labels: { formatter: number } }, tooltip: { y: { formatter: value => `${number(value)} hari` } },
        });

        const statuses = analysis.status_distribution || [];
        mountChart('status', '#paStatusChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'donut', height: 290 }, series: statuses.map(row => row.count), labels: statuses.map(row => row.label), colors,
            stroke: { colors: ['#fff'], width: 3 }, plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total PO', formatter: chart => number(chart.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
            tooltip: { y: { formatter: value => `${number(value)} PO` } },
        });
    }

    function renderRankings(analysis) {
        const renderList = (selector, rows, value) => {
            $(selector).innerHTML = rows.length ? rows.slice(0, 10).map((row, index) => `<div class="pa-rank"><span>${index + 1}</span><div>${medicineLink(row)}<small>${escapeHtml(row.code)} · ${escapeHtml(row.category)}</small></div><b>${escapeHtml(value(row))}</b></div>`).join('') : '<div class="pa-empty">Belum ada ranking.</div>';
        };
        renderList('#paFrequencyList', analysis.top_frequency || [], row => `${number(row.order_count)} kali`);
        renderList('#paQuantityList', analysis.top_quantity || [], row => `${number(row.ordered_qty)} ${row.unit}`);
        renderList('#paValueList', analysis.top_value || [], row => money(row.order_value));
    }

    function renderMedicines(rows) {
        $('#paMedicineCount').textContent = `${number(rows.length)} obat`;
        $('#paMedicineBody').innerHTML = rows.length ? rows.map(row => {
            const delta = Number(row.price_change_percent || 0);
            return `<tr><td><span class="pa-cell-main">${medicineLink(row)}</span><span class="pa-cell-sub">${escapeHtml(row.code)} · ${escapeHtml(row.category)}</span></td>
                <td>${escapeHtml(row.main_supplier)}</td><td class="text-end">${number(row.order_count)}x<br><span class="pa-cell-sub">${row.reorder_days === null ? '-' : `${number(row.reorder_days)} hari`}</span></td>
                <td class="text-end">${number(row.ordered_qty)}<span class="pa-cell-sub">${escapeHtml(row.unit)}</span></td><td class="text-end"><b>${money(row.order_value)}</b></td>
                <td class="text-end"><span class="pa-progress"><i style="width:${Math.min(100, Number(row.fulfillment_percent || 0))}%"></i></span>${percent(row.fulfillment_percent)}</td>
                <td class="text-end">${money(row.last_price)}</td><td class="text-end"><span class="pa-delta ${delta > 0 ? 'is-up' : ''}">${delta > 0 ? '+' : ''}${percent(delta)}</span></td>
                <td><span class="pa-badge is-${escapeHtml(row.movement)}">${escapeHtml(row.movement_label)}</span></td></tr>`;
        }).join('') : emptyRow(9);
    }

    function renderPriceChanges(rows) {
        $('#paPriceBody').innerHTML = rows.length ? rows.slice(0, 30).map(row => {
            const delta = Number(row.price_change_percent || 0);
            return `<tr><td>${medicineLink(row)}<span class="pa-cell-sub">${escapeHtml(row.unit)}</span></td><td class="text-end">${money(row.first_price)}</td><td class="text-end">${money(row.last_price)}</td><td class="text-end"><span class="pa-delta ${delta > 0 ? 'is-up' : ''}">${delta > 0 ? '+' : ''}${percent(delta)}</span></td></tr>`;
        }).join('') : emptyRow(4, 'Belum ada obat dengan order berulang.');
    }

    function renderReorders(rows) {
        $('#paReorderBody').innerHTML = rows.length ? rows.slice(0, 30).map(row => `<tr><td>${medicineLink(row)}<span class="pa-cell-sub">${escapeHtml(row.code)}</span></td><td class="text-end">${number(row.order_count)} kali</td><td class="text-end"><b>${number(row.reorder_days)} hari</b></td><td>${formatDate(row.last_order_date)}</td></tr>`).join('') : emptyRow(4, 'Belum ada pemesanan ulang pada periode ini.');
    }

    function renderMoving(rows) {
        $('#paMovingDistribution').innerHTML = rows.length ? rows.map(row => `<article class="pa-moving-card is-${escapeHtml(row.key)}"><small>${escapeHtml(row.label)}</small><strong>${money(row.order_value)}</strong><p>${number(row.medicine_count)} obat · ${number(row.ordered_qty)} unit · ${percent(row.percent)} nilai order</p></article>`).join('') : '<div class="pa-empty">Belum ada distribusi pergerakan.</div>';
    }

    function renderSuppliers(rows) {
        $('#paSupplierBody').innerHTML = rows.length ? rows.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.name)}</span></td><td class="text-end">${number(row.po_count)} PO</td><td class="text-end">${number(row.item_count)}</td><td class="text-end">${number(row.ordered_qty)}</td><td class="text-end"><b>${money(row.order_value)}</b></td><td class="text-end">${money(row.outstanding_value)}</td><td class="text-end"><span class="pa-progress"><i style="width:${Math.min(100, Number(row.fulfillment_percent || 0))}%"></i></span>${percent(row.fulfillment_percent)}</td><td class="text-end">${row.lead_time_days === null ? '-' : `${number(row.lead_time_days)} hari`}</td></tr>`).join('') : emptyRow(8);
    }

    function renderCategories(rows) {
        $('#paCategoryBody').innerHTML = rows.length ? rows.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.name)}</span></td><td class="text-end">${number(row.po_count)} PO</td><td class="text-end">${number(row.item_count)}</td><td class="text-end">${number(row.ordered_qty)}</td><td class="text-end"><b>${money(row.order_value)}</b></td><td class="text-end">${percent(row.contribution_percent)}</td></tr>`).join('') : emptyRow(6);
    }

    function renderOutstanding(rows) {
        $('#paOutstandingCount').textContent = `${number(rows.length)} item`;
        $('#paOutstandingBody').innerHTML = rows.length ? rows.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.no_po)}</span><span class="pa-cell-sub">${formatDate(row.date)}</span></td><td><button type="button" class="pa-medicine-link" data-medicine="${Number(row.medicine_id)}">${escapeHtml(row.medicine)}</button></td><td>${escapeHtml(row.supplier)}</td><td class="text-end">${number(row.ordered_qty)}</td><td class="text-end">${number(row.received_qty)}</td><td class="text-end"><b>${number(row.outstanding_qty)} ${escapeHtml(row.unit)}</b></td><td class="text-end">${money(row.outstanding_value)}</td><td><span class="pa-badge">${escapeHtml(row.status_label)}</span></td></tr>`).join('') : emptyRow(8, 'Semua order pada periode aktif telah diterima lengkap.');
    }

    function renderCancelled(rows) {
        $('#paCancelledCount').textContent = `${number(rows.length)} PO`;
        $('#paCancelledBody').innerHTML = rows.length ? rows.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.no_po)}</span></td><td>${formatDate(row.date)}</td><td>${escapeHtml(row.supplier)}</td><td class="text-end">${number(row.item_count)}</td><td class="text-end">${number(row.ordered_qty)}</td><td class="text-end">${money(row.order_value)}</td><td><span class="pa-badge is-rejected">${escapeHtml(row.status_label)}</span></td></tr>`).join('') : emptyRow(7, 'Tidak ada PO dibatalkan pada periode aktif.');
    }

    function renderNeed(rows) {
        $('#paNeedBody').innerHTML = rows.length ? rows.map(row => `<tr><td><span class="pa-cell-main">${medicineLink(row)}</span><span class="pa-cell-sub">${escapeHtml(row.code)} · order ${formatDate(row.last_order_date)}</span></td><td class="text-end">${number(row.stock_at_order)} ${escapeHtml(row.unit)}</td><td class="text-end">${number(row.sales_30_days)} ${escapeHtml(row.unit)}</td><td class="text-end">${number(row.minimum_stock)}</td><td class="text-end"><b>${number(row.estimated_need)}</b></td><td class="text-end"><b>${number(row.last_order_qty)}</b></td><td class="text-end">${row.need_ratio === null ? '-' : `${number(row.need_ratio)}x`}</td><td><span class="pa-badge is-${escapeHtml(row.need_status)}"><i class="mdi ${row.need_status === 'optimal' ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline'}"></i>${escapeHtml(row.need_status_label)}</span></td></tr>`).join('') : emptyRow(8);
    }

    function renderStatusList(rows) {
        $('#paStatusList').innerHTML = rows.map((row, index) => `<div><i style="background:${colors[index % colors.length]}"></i><span>${escapeHtml(row.label)}</span><b>${number(row.count)}</b></div>`).join('');
    }

    function renderInsights(rows) {
        $('#paInsights').innerHTML = rows.length ? rows.map(row => `<article class="pa-insight tone-${escapeHtml(row.tone)}"><i class="mdi ${escapeHtml(row.icon)}"></i><div><b>${escapeHtml(row.title)}</b><p>${escapeHtml(row.text)}</p></div></article>`).join('') : '<div class="pa-empty">Insight belum tersedia.</div>';
    }

    async function openMedicine(medicineId) {
        const layer = $('#paDrawerLayer');
        layer.hidden = false;
        document.body.classList.add('pa-drawer-open');
        $('#paDrawerTitle').textContent = 'Memuat...';
        $('#paDrawerMeta').textContent = '';
        $('#paDrawerBody').innerHTML = '<div class="pa-loading"><i class="mdi mdi-loading mdi-spin"></i> Menyiapkan detail obat...</div>';
        try {
            const url = root.dataset.medicineUrl.replace('__MEDICINE__', medicineId);
            const payload = await requestJson(`${url}?${queryString()}`);
            renderMedicineDetail(payload.medicine || {});
        } catch (error) {
            $('#paDrawerBody').innerHTML = `<div class="pa-empty"><i class="mdi mdi-alert-circle-outline"></i>${escapeHtml(error.message)}</div>`;
        }
    }

    function renderMedicineDetail(medicine) {
        const summary = medicine.summary || {};
        $('#paDrawerTitle').textContent = medicine.name || 'Detail obat';
        $('#paDrawerMeta').textContent = `${medicine.code || '-'} · ${medicine.category || '-'} · ${medicine.unit || 'unit'}`;
        const kpis = [
            ['Total Diorder', `${number(summary.ordered_qty)} ${medicine.unit || 'unit'}`], ['Total Nilai Order', money(summary.order_value)],
            ['Frekuensi Order', `${number(summary.order_count)} kali`], ['Rata-rata / Order', `${number(summary.average_per_order)} ${medicine.unit || 'unit'}`],
            ['Supplier Utama', summary.main_supplier || '-'], ['Harga Terakhir', money(summary.last_price)],
            ['Harga Terendah', money(summary.lowest_price)], ['Harga Tertinggi', money(summary.highest_price)],
            ['Rata-rata Harga', money(summary.average_price)], ['Rata-rata Lead Time', summary.lead_time_days === null ? '-' : `${number(summary.lead_time_days)} hari`],
            ['Penjualan 30 Hari', `${number(summary.sales_30_days)} ${medicine.unit || 'unit'}`], ['Stok Saat Ini', `${number(summary.current_stock)} ${medicine.unit || 'unit'}`],
        ];
        const history = medicine.history || [];
        $('#paDrawerBody').innerHTML = `<div class="pa-detail-kpis">${kpis.map(kpi => `<article class="pa-detail-kpi"><small>${escapeHtml(kpi[0])}</small><strong>${escapeHtml(kpi[1])}</strong></article>`).join('')}</div>
            <section class="pa-detail-section"><h3>Tren Harga Beli per Satuan Stok</h3><div class="pa-detail-chart" id="paDetailPriceChart"></div></section>
            <section class="pa-detail-section"><h3>Riwayat Order Barang</h3><div class="table-responsive"><table class="table pa-table pa-table-compact"><thead><tr><th>PO / Tanggal</th><th>Supplier</th><th class="text-end">Qty</th><th class="text-end">Diterima</th><th class="text-end">Harga</th><th class="text-end">Nilai</th><th>Status</th></tr></thead><tbody>${history.length ? history.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.no_po)}</span><span class="pa-cell-sub">${formatDate(row.date)}</span></td><td>${escapeHtml(row.supplier)}</td><td class="text-end">${number(row.qty)} ${escapeHtml(row.unit)}</td><td class="text-end">${number(row.received_qty)}</td><td class="text-end">${money(row.price)}</td><td class="text-end">${money(row.value)}</td><td><span class="pa-badge is-${escapeHtml(row.status)}">${escapeHtml(row.status_label)}</span></td></tr>`).join('') : emptyRow(7)}</tbody></table></div></section>`;
        const priceTrend = medicine.price_trend || [];
        mountChart('detailPrice', '#paDetailPriceChart', {
            ...chartBase, chart: { ...chartBase.chart, type: 'area', height: 230 }, series: [{ name: 'Harga beli', data: priceTrend.map(row => row.price) }], colors: ['#0e9384'],
            stroke: { width: 3, curve: 'smooth' }, fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: .04 } }, markers: { size: 3 },
            xaxis: { categories: priceTrend.map(row => row.label), labels: { rotate: -30 } }, yaxis: { labels: { formatter: compactMoney } }, tooltip: { y: { formatter: money } },
        });
    }

    function closeDrawer() {
        $('#paDrawerLayer').hidden = true;
        document.body.classList.remove('pa-drawer-open');
        destroyChart('detailPrice');
    }

    function selectRange(range) {
        const today = new Date();
        const start = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const end = new Date(start);
        if (range === 'today') {
            // same day
        } else if (range === 'mtd') {
            start.setDate(1);
        } else if (range === 'ytd') {
            start.setMonth(0, 1);
        } else if (/^\d+$/.test(range)) {
            start.setDate(start.getDate() - (Number(range) - 1));
        } else if (range === 'custom') {
            $('#paDateStart').focus();
            return;
        }
        $('#paDateStart').value = dateInput(start);
        $('#paDateEnd').value = dateInput(end);
        $$('.pa-presets button').forEach(button => button.classList.toggle('is-active', button.dataset.range === range));
        loadData();
    }

    function resetFilters() {
        $('#paFilterForm').reset();
        $('#paDateStart').value = root.dataset.defaultStart;
        $('#paDateEnd').value = root.dataset.defaultEnd;
        $('#paGranularity').value = 'day';
        $$('.pa-presets button').forEach(button => button.classList.toggle('is-active', button.dataset.range === '30'));
        loadData();
    }

    function toast(message, isError = false) {
        const element = $('#paToast');
        element.hidden = false;
        element.className = `pa-toast${isError ? ' is-error' : ''}`;
        $('span', element).textContent = message;
        $('i', element).className = `mdi ${isError ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline'}`;
        clearTimeout(state.toastTimer);
        state.toastTimer = setTimeout(() => { element.hidden = true; }, 4300);
    }

    $('#paFilterForm').addEventListener('submit', event => { event.preventDefault(); loadData(); });
    $('#paReset').addEventListener('click', resetFilters);
    $('#paRefresh').addEventListener('click', loadData);
    $('#paGranularity').addEventListener('change', loadData);
    $('#paFilterToggle').addEventListener('click', () => {
        const panel = $('#paFilterPanel');
        panel.classList.toggle('is-collapsed');
        const collapsed = panel.classList.contains('is-collapsed');
        $('#paFilterToggle').setAttribute('aria-expanded', String(!collapsed));
        $('i', $('#paFilterToggle')).className = `mdi ${collapsed ? 'mdi-chevron-down' : 'mdi-chevron-up'}`;
    });
    $$('.pa-presets button').forEach(button => button.addEventListener('click', () => selectRange(button.dataset.range)));
    $('#paTabs').addEventListener('click', event => {
        const button = event.target.closest('[data-tab]');
        if (!button) return;
        const tab = button.dataset.tab;
        $$('[data-tab]', $('#paTabs')).forEach(item => item.classList.toggle('is-active', item === button));
        $$('[data-tab-panel]').forEach(panel => panel.classList.toggle('is-active', panel.dataset.tabPanel === tab));
        window.requestAnimationFrame(() => window.dispatchEvent(new Event('resize')));
    });
    root.addEventListener('click', event => {
        const link = event.target.closest('[data-medicine]');
        if (link) openMedicine(link.dataset.medicine);
    });
    $('#paDrawerClose').addEventListener('click', closeDrawer);
    $('#paDrawerBackdrop').addEventListener('click', closeDrawer);
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !$('#paDrawerLayer').hidden) closeDrawer(); });

    loadData();
})();
</script>
