<script>
(() => {
    'use strict';

    const root = document.getElementById('procurementAnalysisApp');
    if (!root) return;

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));
    const state = { charts: {}, controller: null, initializedOptions: false, analysis: null, toastTimer: null, receivingTables: {} };
    const receivingTableDefinitions = {
        received: { key: 'received_items', prefix: 'paReceived', qty: 'received_qty', value: 'received_value', unit: 'item', render: renderReceived },
        outstanding: { key: 'outstanding', prefix: 'paOutstanding', qty: 'outstanding_qty', value: 'outstanding_value', unit: 'item', render: renderOutstanding },
        cancelled: { key: 'cancelled_orders', prefix: 'paCancelled', qty: 'ordered_qty', value: 'order_value', unit: 'PO', render: renderCancelled },
    };
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
        ['#paFilterForm', '#paReceivingFilterForm'].forEach(selector => {
            new FormData($(selector)).forEach((value, key) => {
                if (String(value).trim() !== '') params.set(key, value);
            });
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
        $('#paReceivingApply').disabled = loading;
        $('#paReceivingFilterForm').setAttribute('aria-busy', String(loading));
        $('#paReceivingKpis').setAttribute('aria-busy', String(loading));
        $('#paReceivingApply').innerHTML = loading
            ? '<i class="mdi mdi-loading mdi-spin"></i> Memuat...'
            : '<i class="mdi mdi-filter-check"></i> Terapkan Filter';
        if (loading) {
            $('#paReceivingScope').textContent = 'Memuat hasil filter penerimaan...';
            status.className = 'pa-filter-status is-loading';
            status.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Mengolah analisis...';
            apply.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Memuat...';
        } else if (error) {
            $('#paReceivingScope').textContent = 'Filter gagal diterapkan. Ringkasan masih memakai filter sebelumnya.';
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
        $('#paOrderMethodology').textContent = meta.methodology?.orders || '';
        $('#paActiveFilters').textContent = (meta.active_filters || []).length ? meta.active_filters.join(' · ') : 'Tanpa filter tambahan';
        $('#paReceiptMethodology').textContent = meta.methodology?.receipts || '';
        renderKpis(analysis.summary || {});
        renderCharts(analysis);
        renderRankings(analysis);
        renderMedicines(analysis.medicines || []);
        renderPriceChanges(analysis.price_changes || []);
        renderReorders(analysis.reorder || []);
        renderMoving(analysis.moving_distribution || []);
        renderSuppliers(analysis.suppliers || []);
        renderCategories(analysis.categories || []);
        renderReceiving(analysis.receiving || {}, analysis.options || {});
        renderNeed(analysis.need_analysis || []);
        renderStatusList(analysis.status_distribution || []);
        renderInsights(analysis.insights || []);
    }

    function renderOptions(options) {
        const definitions = [
            ['#paBranch', options.branches, row => row.id, row => `${row.name}${row.code ? ` · ${row.code}` : ''}`],
            ['#paSupplier', options.suppliers, row => row.id, row => row.name],
            ['#paReceivingSupplier', options.suppliers, row => row.id, row => row.name],
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
            const selectedLabel = select.options[select.selectedIndex]?.textContent || selected;
            const placeholder = select.options[0]?.textContent || 'Semua';
            select.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>` + rows.map(row => `<option value="${escapeHtml(value(row))}">${escapeHtml(label(row))}</option>`).join('');
            if (selector === '#paReceivingSupplier' && selected && !rows.some(row => String(value(row)) === selected)) {
                select.innerHTML += `<option value="${escapeHtml(selected)}">${escapeHtml(selectedLabel)}</option>`;
            }
            if ($$('option', select).some(option => option.value === selected)) select.value = selected;
        });
        state.initializedOptions = true;
    }

    function renderKpis(summary) {
        const cards = [
            ['Nilai Estimasi PO', 'order_value', money, 'mdi-cash-multiple', `Item ${money(summary.item_value)} + biaya ${money(summary.additional_cost_value)}`, ''],
            ['Nilai Sudah Diterima', 'received_value', money, 'mdi-cash-check', 'Aktual penerimaan posted, termasuk PPN dan alokasi biaya', 'tone-green'],
            ['Nilai Belum Diterima', 'outstanding_value', money, 'mdi-package-variant-remove', 'Estimasi nilai PO outstanding', 'tone-red'],
            ['Total PO', 'total_po', value => `${number(value)} PO`, 'mdi-file-document-multiple-outline', `Sebelumnya ${number(summary.total_po_previous)} PO`, 'tone-green'],
            ['Total Item PO', 'total_items', value => `${number(value)} item`, 'mdi-format-list-bulleted', 'Jumlah baris item PO pada filter aktif', 'tone-teal'],
            ['Total Qty Diorder', 'ordered_qty', value => `${number(value)} unit`, 'mdi-package-variant-closed', `Satuan stok terkonversi`, 'tone-violet'],
            ['Qty Sudah Diterima', 'received_qty', value => `${number(value)} unit`, 'mdi-package-variant-closed-check', 'Satuan stok dari penerimaan posted', 'tone-green'],
            ['Qty Belum Diterima', 'outstanding_qty', value => `${number(value)} unit`, 'mdi-package-variant-remove', 'Sisa qty per baris PO dalam satuan stok', 'tone-red'],
            ['Item Sudah Diterima', 'received_items', value => `${number(value)} item`, 'mdi-package-check', `Termasuk ${number(summary.partial_received_items)} item diterima sebagian`, 'tone-green'],
            ['Item Belum Diterima', 'outstanding_items', value => `${number(value)} item`, 'mdi-package-variant-remove', 'Termasuk item dengan sisa penerimaan sebagian', 'tone-red'],
            ['Rata-rata Lead Time', 'lead_time_days', value => `${number(value)} hari`, 'mdi-clock-fast', `PO sampai penerimaan pertama`, 'tone-amber'],
            ['Supplier Aktif', 'active_suppliers', value => `${number(value)} supplier`, 'mdi-truck-delivery-outline', `Supplier pada periode aktif`, 'tone-teal'],
        ];
        $('#paKpiGrid').innerHTML = cards.map((card, index) => {
            const change = Number(summary[`${card[1]}_change`] || 0);
            const direction = change < 0 ? 'is-down' : '';
            const icon = change < 0 ? 'mdi-trending-down' : 'mdi-trending-up';
            return `<article class="pa-kpi ${card[5]}" style="--pa-index:'${String(index + 1).padStart(2, '0')}'">
                <span><i class="mdi ${card[3]}"></i></span><div><small>${escapeHtml(card[0])}</small><strong>${escapeHtml(card[2](summary[card[1]]))}</strong>
                <p>${escapeHtml(card[4])}</p><p><b class="pa-change ${direction}"><i class="mdi ${icon}"></i>${percent(Math.abs(change))}</b> vs periode lalu</p></div></article>`;
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
                { name: 'Nilai estimasi PO', type: 'area', data: trend.order_value || [] },
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
            ...chartBase, chart: { ...chartBase.chart, type: 'bar', height: 270 }, series: [{ name: 'Nilai estimasi PO', data: suppliers.map(row => row.order_value) }],
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
                <td class="text-end">${number(row.received_qty)}<span class="pa-cell-sub">${escapeHtml(row.unit)} · ${percent(row.fulfillment_percent)}</span></td>
                <td class="text-end">${number(row.outstanding_qty)}<span class="pa-cell-sub">${escapeHtml(row.unit)}</span></td><td class="text-end">${money(row.received_value)}</td>
                <td class="text-end">${money(row.last_price)}</td><td class="text-end"><span class="pa-delta ${delta > 0 ? 'is-up' : ''}">${delta > 0 ? '+' : ''}${percent(delta)}</span></td>
                <td><span class="pa-badge is-${escapeHtml(row.movement)}">${escapeHtml(row.movement_label)}</span></td></tr>`;
        }).join('') : emptyRow(11);
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
        $('#paMovingDistribution').innerHTML = rows.length ? rows.map(row => `<article class="pa-moving-card is-${escapeHtml(row.key)}"><small>${escapeHtml(row.label)}</small><strong>${money(row.order_value)}</strong><p>${number(row.medicine_count)} obat · ${number(row.ordered_qty)} unit · ${percent(row.percent)} nilai item PO</p></article>`).join('') : '<div class="pa-empty">Belum ada distribusi pergerakan.</div>';
    }

    function renderSuppliers(rows) {
        $('#paSupplierBody').innerHTML = rows.length ? rows.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.name)}</span></td><td class="text-end">${number(row.po_count)} PO</td><td class="text-end">${number(row.item_count)}</td><td class="text-end">${number(row.ordered_qty)}</td><td class="text-end">${number(row.received_qty)}</td><td class="text-end">${number(row.outstanding_qty)}</td><td class="text-end"><b>${money(row.order_value)}</b></td><td class="text-end">${money(row.received_value)}</td><td class="text-end">${money(row.outstanding_value)}</td><td class="text-end"><span class="pa-progress"><i style="width:${Math.min(100, Number(row.fulfillment_percent || 0))}%"></i></span>${percent(row.fulfillment_percent)}</td><td class="text-end">${row.lead_time_days === null ? '-' : `${number(row.lead_time_days)} hari`}</td></tr>`).join('') : emptyRow(11);
    }

    function renderCategories(rows) {
        $('#paCategoryBody').innerHTML = rows.length ? rows.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.name)}</span></td><td class="text-end">${number(row.po_count)} PO</td><td class="text-end">${number(row.item_count)}</td><td class="text-end">${number(row.ordered_qty)}</td><td class="text-end"><b>${money(row.order_value)}</b></td><td class="text-end">${percent(row.contribution_percent)}</td></tr>`).join('') : emptyRow(6);
    }

    function renderReceiving(receiving, options) {
        const summary = receiving.summary || {};
        const filters = receiving.filters || {};
        const supplier = (options.suppliers || []).find(row => String(row.id) === String(filters.supplier_id));
        const statusLabel = $$('option', $('#paReceivingStatus')).find(option => option.value === (filters.status || ''))?.textContent || 'Semua item';
        $('#paReceivingScope').textContent = [
            supplier?.name || (filters.supplier_id ? `Supplier #${filters.supplier_id}` : 'Semua supplier'),
            statusLabel,
            filters.search ? `Pencarian: ${filters.search}` : '',
            `${number(summary.total_po)} PO / ${number(summary.unique_items)} jenis barang`,
        ].filter(Boolean).join(' · ');
        const cards = [
            ['Item Sudah Diterima', summary.received_items, number, 'mdi-package-check', `Baris PO; ${number(summary.partial_received_items)} diterima sebagian`, 'tone-green'],
            ['Qty Sudah Diterima', summary.received_qty, number, 'mdi-package-variant-closed-check', 'Qty satuan stok dari penerimaan Posted', 'tone-green'],
            ['Nilai Sudah Diterima', summary.received_value, money, 'mdi-cash-check', 'Nilai aktual Posted, termasuk PPN dan alokasi biaya', 'tone-green'],
            ['Item Belum Diterima', summary.outstanding_items, number, 'mdi-package-variant-remove', 'Baris PO yang masih memiliki sisa penerimaan', 'tone-red'],
            ['Qty Belum Diterima', summary.outstanding_qty, number, 'mdi-package-variant-remove', 'Sisa qty per baris PO dalam satuan stok', 'tone-red'],
            ['Nilai Belum Diterima', summary.outstanding_value, money, 'mdi-cash-clock', 'Estimasi sisa nilai PO, termasuk alokasi biaya', 'tone-red'],
        ];
        $('#paReceivingKpis').innerHTML = cards.map(card => `<article class="pa-kpi ${card[5]}"><span><i class="mdi ${card[3]}"></i></span><div><small>${escapeHtml(card[0])}</small><strong>${escapeHtml(card[2](card[1]))}</strong><p>${escapeHtml(card[4])}</p></div></article>`).join('');
        renderReceivingTables(receiving);
    }

    function receivingFilterError(filters) {
        if (filters.date_start && filters.date_end && filters.date_start > filters.date_end) {
            return 'Tanggal PO akhir harus sama dengan atau setelah tanggal mulai.';
        }
        for (const [key, label] of [['qty', 'Qty'], ['value', 'Nilai'], ['item', 'Jumlah item']]) {
            const min = filters[`${key}_min`];
            const max = filters[`${key}_max`];
            if ((min && (!Number.isFinite(Number(min)) || Number(min) < 0)) || (max && (!Number.isFinite(Number(max)) || Number(max) < 0))) {
                return `${label} harus berupa angka nol atau lebih.`;
            }
            if (min !== undefined && min !== '' && max !== undefined && max !== '' && Number(min) > Number(max)) {
                return `${label} maksimum harus sama dengan atau lebih besar dari minimum.`;
            }
        }
        return '';
    }

    function filterReceivingRows(rows, filters, definition) {
        const search = String(filters.search || '').trim().toLocaleLowerCase('id-ID');
        const inRange = (value, min, max) => (min === undefined || min === '' || Number(value || 0) >= Number(min))
            && (max === undefined || max === '' || Number(value || 0) <= Number(max));
        const filtered = rows.filter(row => {
            const date = String(row.date || '').slice(0, 10);
            const receiptStatus = Number(row.received_qty || 0) <= 0 ? 'none' : (Number(row.outstanding_qty || 0) > 0 ? 'partial' : 'complete');
            return (!search || [row.no_po, row.medicine, row.medicine_code, row.supplier].join(' ').toLocaleLowerCase('id-ID').includes(search))
                && (!filters.date_start || date >= filters.date_start)
                && (!filters.date_end || date <= filters.date_end)
                && (!filters.supplier || String(row.supplier_id) === filters.supplier)
                && (!filters.medicine || String(row.medicine_id) === filters.medicine)
                && (!filters.status || row.status === filters.status)
                && (!filters.receipt_status || receiptStatus === filters.receipt_status)
                && inRange(row[definition.qty], filters.qty_min, filters.qty_max)
                && inRange(row[definition.value], filters.value_min, filters.value_max)
                && inRange(row.item_count, filters.item_min, filters.item_max);
        });
        const [sort, direction] = String(filters.sort || 'default').split(':');
        if (['date', 'no_po', 'supplier', 'medicine', 'item_count', 'qty', 'value'].includes(sort)) {
            const field = sort === 'qty' ? definition.qty : (sort === 'value' ? definition.value : sort);
            const numeric = ['item_count', 'qty', 'value'].includes(sort);
            filtered.sort((a, b) => {
                const comparison = numeric ? Number(a[field] || 0) - Number(b[field] || 0)
                    : String(a[field] || '').localeCompare(String(b[field] || ''), 'id-ID', { numeric: true, sensitivity: 'base' });
                return comparison * (direction === 'desc' ? -1 : 1);
            });
        }
        return filtered;
    }

    function renderReceivingTables(receiving) {
        Object.entries(receivingTableDefinitions).forEach(([kind, definition]) => {
            const table = state.receivingTables[kind];
            table.rows = receiving[definition.key] || [];
            table.page = 1;
            const setOptions = (name, valueKey, labelKey) => {
                const select = $(`[name="${name}"]`, table.form);
                if (!select) return;
                const selected = select.value;
                const selectedLabel = select.options[select.selectedIndex]?.textContent || selected;
                const placeholder = select.options[0].textContent;
                const options = new Map(table.rows.map(row => [String(row[valueKey]), row[labelKey]]));
                if (selected && !options.has(selected)) options.set(selected, selectedLabel);
                select.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>` + [...options]
                    .sort((a, b) => String(a[1]).localeCompare(String(b[1]), 'id-ID'))
                    .map(([value, label]) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`).join('');
                select.value = selected;
            };
            setOptions('supplier', 'supplier_id', 'supplier');
            setOptions('medicine', 'medicine_id', 'medicine');
            setOptions('status', 'status', 'status_label');
            renderReceivingTable(kind);
        });
    }

    function renderReceivingTable(kind) {
        const table = state.receivingTables[kind];
        const definition = receivingTableDefinitions[kind];
        const error = $('[data-table-error]', table.form);
        error.textContent = receivingFilterError(table.filters);
        error.hidden = !error.textContent;
        if (error.textContent) return;
        const filtered = filterReceivingRows(table.rows, table.filters, definition);
        const size = table.pageSize === 'all' ? Math.max(1, filtered.length) : Number(table.pageSize);
        const pages = Math.max(1, Math.ceil(filtered.length / size));
        table.page = Math.max(1, Math.min(table.page, pages));
        const start = (table.page - 1) * size;
        table.filteredCount = filtered.length;
        table.pages = pages;
        table.start = filtered.length ? start + 1 : 0;
        table.end = Math.min(start + size, filtered.length);
        definition.render(filtered.slice(start, start + size));
        $(`#${definition.prefix}Count`).textContent = `${number(filtered.length)}${filtered.length === table.rows.length ? '' : ` / ${number(table.rows.length)}`} ${definition.unit}`;
        $('[data-table-summary]', table.form).textContent = `${number(filtered.length)} dari ${number(table.rows.length)} ${definition.unit} · Qty ${number(filtered.reduce((total, row) => total + Number(row[definition.qty] || 0), 0))} · ${money(filtered.reduce((total, row) => total + Number(row[definition.value] || 0), 0))}`;
        const activeCount = Object.entries(table.filters).filter(([key, value]) => key !== 'search' && value !== '' && !(key === 'sort' && value === 'default')).length;
        const filterBadge = $('[data-table-active-count]', table.form);
        filterBadge.textContent = `${number(activeCount)} aktif`;
        filterBadge.hidden = activeCount === 0;
        $('[data-table-page-info]', table.section).textContent = `${filtered.length ? number(start + 1) : 0}–${number(Math.min(start + size, filtered.length))} dari ${number(filtered.length)} · Halaman ${number(table.page)} / ${number(pages)}`;
        $('[data-table-page="-1"]', table.section).disabled = table.page <= 1;
        $('[data-table-page="1"]', table.section).disabled = table.page >= pages;
    }

    function quickPeriodDates(range, today = new Date()) {
        if (range === 'scope') return { start: '', end: '' };
        const start = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const end = new Date(start);
        if (range === 'mtd') start.setDate(1);
        else if (range === 'ytd') start.setMonth(0, 1);
        else if (/^\d+$/.test(range) && Number(range) > 0) start.setDate(start.getDate() - (Number(range) - 1));
        else if (range !== 'today') return null;
        return { start: dateInput(start), end: dateInput(end) };
    }

    function updateReceivingTablePeriod(table, range) {
        table.range = range;
        $$('[data-table-range]', table.form).forEach(button => {
            const active = button.dataset.tableRange === range;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
    }

    function resetReceivingTableFilters() {
        Object.values(state.receivingTables).forEach(table => {
            table.form.reset();
            table.filters = {};
            table.page = 1;
            updateReceivingTablePeriod(table, 'scope');
        });
    }

    function initializeReceivingTables() {
        $$('[data-receiving-table]').forEach(section => {
            const kind = section.dataset.receivingTable;
            const form = $('[data-table-filter]', section);
            const mobile = window.matchMedia('(max-width: 767px)').matches;
            const pageSize = mobile ? '5' : '25';
            const table = state.receivingTables[kind] = { section, form, rows: [], filters: {}, page: 1, pageSize, range: 'scope' };
            $('[data-table-page-size]', section).value = pageSize;
            if (mobile) {
                $('[data-table-filter-details]', form).open = false;
                section.open = kind === 'received';
            }
            const apply = event => {
                if (['date_start', 'date_end'].includes(event?.target?.name)) updateReceivingTablePeriod(table, 'custom');
                const filters = Object.fromEntries(new FormData(form));
                const error = $('[data-table-error]', form);
                error.textContent = receivingFilterError(filters);
                error.hidden = !error.textContent;
                if (error.textContent || !form.checkValidity()) return;
                table.filters = filters;
                table.page = 1;
                renderReceivingTable(kind);
            };
            form.addEventListener('submit', event => { event.preventDefault(); apply(event); });
            form.addEventListener('invalid', () => { $('[data-table-filter-details]', form).open = true; }, true);
            form.addEventListener('input', apply);
            form.addEventListener('change', apply);
            $$('[data-table-range]', form).forEach(button => button.addEventListener('click', () => {
                const range = button.dataset.tableRange;
                if (range === 'custom') {
                    updateReceivingTablePeriod(table, range);
                    $('[name="date_start"]', form).focus();
                    return;
                }
                const dates = quickPeriodDates(range);
                if (!dates) return;
                $('[name="date_start"]', form).value = dates.start;
                $('[name="date_end"]', form).value = dates.end;
                updateReceivingTablePeriod(table, range);
                apply();
            }));
            $('[data-table-reset]', form).addEventListener('click', () => { form.reset(); updateReceivingTablePeriod(table, 'scope'); apply(); });
            $('[data-table-page-size]', section).addEventListener('change', event => {
                table.pageSize = event.target.value;
                table.page = 1;
                renderReceivingTable(kind);
            });
            $$('[data-table-page]', section).forEach(button => button.addEventListener('click', () => {
                table.page += Number(button.dataset.tablePage);
                renderReceivingTable(kind);
                if (window.matchMedia('(max-width: 767px)').matches) {
                    $('.pa-receiving-table-wrap', section).scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }));
        });
    }

    function renderReceived(rows) {
        $('#paReceivedCount').textContent = `${number(rows.length)} item`;
        $('#paReceivedBody').innerHTML = rows.length ? rows.map(row => `<tr role="row"><td role="cell" data-label="PO / Tanggal"><span class="pa-cell-main">${escapeHtml(row.no_po)}</span><span class="pa-cell-sub">${formatDate(row.date)}</span></td><td role="cell" data-label="Barang"><button type="button" class="pa-medicine-link" data-medicine="${Number(row.medicine_id)}">${escapeHtml(row.medicine)}</button></td><td role="cell" data-label="Supplier">${escapeHtml(row.supplier)}</td><td role="cell" data-label="Dipesan" class="text-end pa-receipt-qty">${number(row.ordered_qty)}</td><td role="cell" data-label="Diterima" class="text-end pa-receipt-qty"><b>${number(row.received_qty)} ${escapeHtml(row.unit)}</b></td><td role="cell" data-label="Belum diterima" class="text-end pa-receipt-qty">${number(row.outstanding_qty)}</td><td role="cell" data-label="Nilai diterima" class="text-end pa-receipt-value">${money(row.received_value)}</td><td role="cell" data-label="Penerimaan"><span class="pa-badge ${row.receipt_status === 'complete' ? 'is-optimal' : 'is-evaluation'}">${row.receipt_status === 'complete' ? 'Lengkap' : 'Sebagian'}</span></td></tr>`).join('') : emptyRow(8, 'Belum ada item dari penerimaan posted pada PO yang dipilih.');
    }

    function renderOutstanding(rows) {
        $('#paOutstandingCount').textContent = `${number(rows.length)} item`;
        $('#paOutstandingBody').innerHTML = rows.length ? rows.map(row => `<tr role="row"><td role="cell" data-label="PO / Tanggal"><span class="pa-cell-main">${escapeHtml(row.no_po)}</span><span class="pa-cell-sub">${formatDate(row.date)}</span></td><td role="cell" data-label="Barang"><button type="button" class="pa-medicine-link" data-medicine="${Number(row.medicine_id)}">${escapeHtml(row.medicine)}</button></td><td role="cell" data-label="Supplier">${escapeHtml(row.supplier)}</td><td role="cell" data-label="Dipesan" class="text-end pa-receipt-qty">${number(row.ordered_qty)}</td><td role="cell" data-label="Diterima" class="text-end pa-receipt-qty">${number(row.received_qty)}</td><td role="cell" data-label="Belum diterima" class="text-end pa-receipt-qty"><b>${number(row.outstanding_qty)} ${escapeHtml(row.unit)}</b></td><td role="cell" data-label="Nilai outstanding" class="text-end pa-receipt-value">${money(row.outstanding_value)}</td><td role="cell" data-label="Status PO"><span class="pa-badge">${escapeHtml(row.status_label)}</span></td></tr>`).join('') : emptyRow(8, 'Tidak ada item dengan sisa penerimaan pada filter aktif.');
    }

    function renderCancelled(rows) {
        $('#paCancelledCount').textContent = `${number(rows.length)} PO`;
        $('#paCancelledBody').innerHTML = rows.length ? rows.map(row => `<tr role="row"><td role="cell" data-label="No. PO"><span class="pa-cell-main">${escapeHtml(row.no_po)}</span></td><td role="cell" data-label="Tanggal">${formatDate(row.date)}</td><td role="cell" data-label="Supplier">${escapeHtml(row.supplier)}</td><td role="cell" data-label="Item" class="text-end pa-receipt-qty">${number(row.item_count)}</td><td role="cell" data-label="Qty" class="text-end pa-receipt-qty">${number(row.ordered_qty)}</td><td role="cell" data-label="Nilai PO" class="text-end pa-receipt-value">${money(row.order_value)}</td><td role="cell" data-label="Status PO"><span class="pa-badge is-rejected">${escapeHtml(row.status_label)}</span></td></tr>`).join('') : emptyRow(7, 'Tidak ada PO dibatalkan pada periode aktif.');
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
            ['Total Diorder', `${number(summary.ordered_qty)} ${medicine.unit || 'unit'}`], ['Nilai Item PO', money(summary.order_value)],
            ['Qty Sudah Diterima', `${number(summary.received_qty)} ${medicine.unit || 'unit'}`],
            ['Qty Belum Diterima', `${number(summary.outstanding_qty)} ${medicine.unit || 'unit'}`], ['Nilai Sudah Diterima', money(summary.received_value)],
            ['Frekuensi Order', `${number(summary.order_count)} kali`], ['Rata-rata / Order', `${number(summary.average_per_order)} ${medicine.unit || 'unit'}`],
            ['Supplier Utama', summary.main_supplier || '-'], ['Harga Terakhir', money(summary.last_price)],
            ['Harga Terendah', money(summary.lowest_price)], ['Harga Tertinggi', money(summary.highest_price)],
            ['Rata-rata Harga', money(summary.average_price)], ['Rata-rata Lead Time', summary.lead_time_days === null ? '-' : `${number(summary.lead_time_days)} hari`],
            ['Penjualan 30 Hari', `${number(summary.sales_30_days)} ${medicine.unit || 'unit'}`], ['Stok Saat Ini', `${number(summary.current_stock)} ${medicine.unit || 'unit'}`],
        ];
        const history = medicine.history || [];
        $('#paDrawerBody').innerHTML = `<div class="pa-detail-kpis">${kpis.map(kpi => `<article class="pa-detail-kpi"><small>${escapeHtml(kpi[0])}</small><strong>${escapeHtml(kpi[1])}</strong></article>`).join('')}</div>
            <section class="pa-detail-section"><h3>Tren Harga Estimasi PO per Satuan Stok</h3><div class="pa-detail-chart" id="paDetailPriceChart"></div></section>
            <section class="pa-detail-section"><h3>Riwayat Order Barang</h3><div class="table-responsive"><table class="table pa-table pa-table-compact"><thead><tr><th>PO / Tanggal</th><th>Supplier</th><th class="text-end">Qty order</th><th class="text-end">Qty diterima</th><th class="text-end">Qty belum diterima</th><th class="text-end">Nilai diterima</th><th class="text-end">Harga estimasi</th><th class="text-end">Nilai item PO</th><th>Status</th></tr></thead><tbody>${history.length ? history.map(row => `<tr><td><span class="pa-cell-main">${escapeHtml(row.no_po)}</span><span class="pa-cell-sub">${formatDate(row.date)}</span></td><td>${escapeHtml(row.supplier)}</td><td class="text-end">${number(row.qty)} ${escapeHtml(row.unit)}</td><td class="text-end">${number(row.received_qty)}</td><td class="text-end">${number(row.outstanding_qty)}</td><td class="text-end">${money(row.received_value)}</td><td class="text-end">${money(row.price)}</td><td class="text-end">${money(row.value)}</td><td><span class="pa-badge is-${escapeHtml(row.status)}">${escapeHtml(row.status_label)}</span></td></tr>`).join('') : emptyRow(9)}</tbody></table></div></section>`;
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
        if (range === 'custom') {
            $('#paDateStart').focus();
            return;
        }
        const dates = quickPeriodDates(range);
        if (!dates) return;
        $('#paDateStart').value = dates.start;
        $('#paDateEnd').value = dates.end;
        $$('.pa-presets button[data-range]').forEach(button => button.classList.toggle('is-active', button.dataset.range === range));
        loadData();
    }

    function resetFilters() {
        $('#paFilterForm').reset();
        $('#paReceivingFilterForm').reset();
        resetReceivingTableFilters();
        $('#paDateStart').value = root.dataset.defaultStart;
        $('#paDateEnd').value = root.dataset.defaultEnd;
        $('#paGranularity').value = 'day';
        $$('.pa-presets button[data-range]').forEach(button => button.classList.toggle('is-active', button.dataset.range === '30'));
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
    $('#paReceivingFilterForm').addEventListener('submit', event => { event.preventDefault(); loadData(); });
    $('#paReceivingSupplier').addEventListener('change', loadData);
    $('#paReceivingStatus').addEventListener('change', loadData);
    $('#paReceivingReset').addEventListener('click', () => { $('#paReceivingFilterForm').reset(); resetReceivingTableFilters(); loadData(); });
    $('#paReset').addEventListener('click', resetFilters);
    $('#paRefresh').addEventListener('click', loadData);
    $('#paGranularity').addEventListener('change', loadData);
    function setFilterCollapsed(collapsed) {
        const panel = $('#paFilterPanel');
        panel.classList.toggle('is-collapsed', collapsed);
        $('#paFilterToggle').setAttribute('aria-expanded', String(!collapsed));
        $('span', $('#paFilterToggle')).textContent = collapsed ? 'Tampilkan filter' : 'Ringkas filter';
        $('i', $('#paFilterToggle')).className = `mdi ${collapsed ? 'mdi-chevron-down' : 'mdi-chevron-up'}`;
    }
    $('#paFilterToggle').addEventListener('click', () => setFilterCollapsed(!$('#paFilterPanel').classList.contains('is-collapsed')));
    $$('.pa-presets button[data-range]').forEach(button => button.addEventListener('click', () => selectRange(button.dataset.range)));
    function activateTab(tab) {
        root.dataset.activeTab = tab;
        if (tab === 'receipts' && window.matchMedia('(max-width: 767px)').matches) setFilterCollapsed(true);
        $$('[data-tab]', $('#paTabs')).forEach(item => {
            item.classList.toggle('is-active', item.dataset.tab === tab);
            if (item.dataset.tab === tab) item.setAttribute('aria-current', 'page');
            else item.removeAttribute('aria-current');
        });
        $$('[data-tab-panel]').forEach(panel => panel.classList.toggle('is-active', panel.dataset.tabPanel === tab));
        if (window.matchMedia('(max-width: 820px)').matches) {
            $(`[data-tab="${tab}"]`, $('#paTabs')).scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        }
        window.requestAnimationFrame(() => window.dispatchEvent(new Event('resize')));
    }
    $('#paTabs').addEventListener('click', event => {
        const button = event.target.closest('[data-tab]');
        if (button) activateTab(button.dataset.tab);
    });
    root.addEventListener('click', event => {
        const link = event.target.closest('[data-medicine]');
        if (link) openMedicine(link.dataset.medicine);
    });
    $('#paDrawerClose').addEventListener('click', closeDrawer);
    $('#paDrawerBackdrop').addEventListener('click', closeDrawer);
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !$('#paDrawerLayer').hidden) closeDrawer(); });

    initializeReceivingTables();
    if (window.matchMedia('(max-width: 767px)').matches) {
        $('#paReceivingFilterPanel').open = false;
        $('#paReceivingSummary').open = false;
    }
    loadData();
})();
</script>
