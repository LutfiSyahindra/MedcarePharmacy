<script>
    (() => {
        'use strict';

        const app = document.getElementById('salesReportApp');
        if (!app) return;

        const elements = {
            form: document.getElementById('srFilterForm'),
            branch: document.getElementById('srBranch'),
            supplier: document.getElementById('srSupplier'),
            dateStart: document.getElementById('srDateStart'),
            dateEnd: document.getElementById('srDateEnd'),
            apply: document.getElementById('srApplyFilter'),
            filterToggle: document.getElementById('srFilterToggle'),
            filterBody: document.getElementById('srFilterBody'),
            filterStatus: document.getElementById('srFilterStatus'),
            activeFilters: document.getElementById('srActiveFilterSummary'),
            resetFilter: document.getElementById('srResetFilter'),
            reportNav: document.getElementById('srReportNav'),
            reportPrevious: document.getElementById('srReportPrevious'),
            reportNext: document.getElementById('srReportNext'),
            metricGrid: document.getElementById('srMetricGrid'),
            chart: document.getElementById('srChart'),
            chartTitle: document.getElementById('srChartTitle'),
            chartSubtitle: document.getElementById('srChartSubtitle'),
            chartLegend: document.getElementById('srChartLegend'),
            insightTitle: document.getElementById('srInsightTitle'),
            insightCopy: document.getElementById('srInsightCopy'),
            peakLabel: document.getElementById('srPeakLabel'),
            peakValue: document.getElementById('srPeakValue'),
            netSales: document.getElementById('srNetSales'),
            returnRatio: document.getElementById('srReturnRatio'),
            healthTrack: document.getElementById('srHealthTrack'),
            branchContext: document.getElementById('srBranchContext'),
            periodContext: document.getElementById('srPeriodContext'),
            generatedContext: document.getElementById('srGeneratedContext'),
            summaryContext: document.getElementById('srSummaryContext'),
            tableTitle: document.getElementById('srTableTitle'),
            tableInfo: document.getElementById('srTableInfo'),
            tablePanel: document.getElementById('srTablePanel'),
            tableViewport: document.getElementById('srTableViewport'),
            tableWrap: document.getElementById('srTableWrap'),
            table: document.getElementById('srTable'),
            tableHead: document.getElementById('srTableHead'),
            tableBody: document.getElementById('srTableBody'),
            tableViewMeta: document.getElementById('srTableViewMeta'),
            search: document.getElementById('srSearch'),
            clearSearch: document.getElementById('srClearSearch'),
            pageSize: document.getElementById('srPageSize'),
            exportCsv: document.getElementById('srExportCsv'),
            print: document.getElementById('srPrint'),
            refresh: document.getElementById('srRefresh'),
            columnToggle: document.getElementById('srColumnToggle'),
            columnMenu: document.getElementById('srColumnMenu'),
            columnMenuList: document.getElementById('srColumnMenuList'),
            resetColumns: document.getElementById('srResetColumns'),
            densityToggle: document.getElementById('srDensityToggle'),
            focusTable: document.getElementById('srFocusTable'),
            paginationInfo: document.getElementById('srPaginationInfo'),
            pageNumbers: document.getElementById('srPageNumbers'),
            pageLabel: document.getElementById('srPageLabel'),
            previousPage: document.getElementById('srPreviousPage'),
            nextPage: document.getElementById('srNextPage'),
            toast: document.getElementById('srToast'),
        };

        const today = localDate(new Date());
        const initialStart = app.dataset.defaultStart || shiftDate(today, -29);
        const initialEnd = app.dataset.defaultEnd || today;
        const urlState = new URLSearchParams(window.location.search);
        const state = {
            report: app.dataset.report,
            endpoint: app.dataset.url,
            branchId: urlState.get('branch_id') || '',
            supplierId: urlState.get('supplier_id') || '',
            dateStart: urlState.get('date_start') || elements.dateStart.value || initialStart,
            dateEnd: urlState.get('date_end') || elements.dateEnd.value || initialEnd,
            search: urlState.get('search') || '',
            sort: urlState.get('sort') || '',
            direction: urlState.get('direction') || '',
            page: Math.max(1, Number(urlState.get('page')) || 1),
            perPage: [10, 25, 50, 100].includes(Number(urlState.get('per_page'))) ? Number(urlState.get('per_page')) : 25,
            payload: null,
            controller: null,
            searchTimer: null,
            toastTimer: null,
            filterDirty: false,
            tableColumns: [],
            hiddenColumns: new Set(readHiddenColumnPreference()),
            density: readTablePreference('density', 'comfortable') === 'compact' ? 'compact' : 'comfortable',
        };

        elements.dateStart.value = state.dateStart;
        elements.dateEnd.value = state.dateEnd;
        elements.search.value = state.search;
        elements.pageSize.value = String(state.perPage);
        applyTableDensity();
        syncPresetSelection();

        const numberFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
        const integerFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
        const currencyFormatter = new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0,
        });
        const compactCurrencyFormatter = new Intl.NumberFormat('id-ID', {
            notation: 'compact', compactDisplay: 'short', maximumFractionDigits: 1,
        });
        const dateFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const dateTimeFormatter = new Intl.DateTimeFormat('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false,
        });

        const transactionTypes = {
            penjualan_bebas: 'OTC / Penjualan Bebas',
            penjualan_resep: 'Resep Non Racikan',
            penjualan_racikan: 'Resep Racikan',
            penjualan_kredit: 'Penjualan Kredit',
            penjualan_instansi: 'Penjualan Instansi',
        };
        const paymentMethods = {
            tunai: 'Tunai', debit: 'Kartu Debit', credit_card: 'Kartu Kredit', transfer: 'Transfer',
            qris: 'QRIS', ewallet: 'E-Wallet', piutang: 'Piutang', instansi: 'Instansi',
        };
        const refundMethods = {
            tunai: 'Tunai', debit: 'Kartu Debit', credit_card: 'Kartu Kredit', transfer: 'Transfer Bank',
            qris: 'QRIS', ewallet: 'E-Wallet', potong_piutang: 'Potong Piutang', lainnya: 'Lainnya',
        };
        const statuses = {
            draft: 'Draft', waiting_approval: 'Menunggu Persetujuan', approved: 'Disetujui', rejected: 'Ditolak',
            diterima_sebagian: 'Diterima Sebagian', selesai: 'Selesai', posted: 'Posted', cancelled: 'Dibatalkan',
            belum_dibayar: 'Belum Dibayar', sebagian: 'Dibayar Sebagian', lunas: 'Lunas',
            terlambat: 'Terlambat', segera_jatuh_tempo: 'Segera Jatuh Tempo', terjadwal: 'Terjadwal', tanpa_tempo: 'Tanpa Jatuh Tempo',
            counting: 'Proses Penghitungan', awaiting_verification: 'Review Selisih',
            awaiting_approval: 'Menunggu Persetujuan', adjusted: 'Penyesuaian Stok',
        };

        async function loadReport(options = {}) {
            const tableOnly = Boolean(options.tableOnly);
            if (state.controller) state.controller.abort();
            const controller = new AbortController();
            state.controller = controller;
            setLoading(true, tableOnly);

            try {
                const response = await fetch(buildUrl(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                const contentType = response.headers.get('content-type') || '';
                const result = contentType.includes('application/json') ? await response.json() : null;
                if (!response.ok) throw new Error(responseMessage(result, response.status));

                state.payload = result.report;
                renderReport(result.report);
                setFilterStatus(state.filterDirty ? 'dirty' : 'active');
                syncUrl();
            } catch (error) {
                if (error.name === 'AbortError') return;
                renderError(error.message || 'Laporan belum dapat dimuat.');
                setFilterStatus('error');
                toast(error.message || 'Laporan belum dapat dimuat.', true);
            } finally {
                if (state.controller === controller) setLoading(false, tableOnly);
            }
        }

        function buildUrl(overrides = {}) {
            const url = new URL(state.endpoint, window.location.origin);
            const values = {
                branch_id: state.branchId,
                supplier_id: state.supplierId,
                date_start: state.dateStart,
                date_end: state.dateEnd,
                search: state.search,
                sort: state.sort,
                direction: state.direction,
                page: state.page,
                per_page: state.perPage,
                ...overrides,
            };
            Object.entries(values).forEach(([key, value]) => {
                if (value !== '' && value !== null && value !== undefined) url.searchParams.set(key, value);
            });
            return url.toString();
        }

        function renderReport(report) {
            renderMeta(report.meta);
            renderMetrics(report.metrics);
            renderChart(report.chart);
            renderInsight(report.insight, report.chart);
            renderTable(report.table);
            renderActiveFilters(report.meta);
        }

        function renderMeta(meta) {
            elements.branchContext.textContent = meta.branch_label;
            elements.periodContext.textContent = `${formatDate(meta.date_start)} — ${formatDate(meta.date_end)}`;
            elements.generatedContext.textContent = formatDateTime(meta.generated_at);
            elements.summaryContext.textContent = `${integerFormatter.format(meta.period_days)} hari · ${meta.branch_label}`;

            const current = String(state.branchId || '');
            const selected = state.filterDirty ? String(elements.branch.value || '') : current;
            elements.branch.innerHTML = '<option value="">Semua cabang yang dapat diakses</option>' + meta.branches.map(branch =>
                `<option value="${escapeHtml(branch.id)}">${escapeHtml(branch.name)}${branch.code ? ` · ${escapeHtml(branch.code)}` : ''}</option>`
            ).join('');
            elements.branch.value = selected;

            if (elements.supplier) {
                const currentSupplier = String(state.supplierId || '');
                const selectedSupplier = state.filterDirty ? String(elements.supplier.value || '') : currentSupplier;
                elements.supplier.innerHTML = '<option value="">Semua supplier</option>' + (meta.suppliers || []).map(supplier =>
                    `<option value="${escapeHtml(supplier.id)}">${escapeHtml(supplier.name)}${supplier.code ? ` · ${escapeHtml(supplier.code)}` : ''}</option>`
                ).join('');
                elements.supplier.value = selectedSupplier;
            }
        }

        function renderMetrics(metrics) {
            elements.metricGrid.innerHTML = metrics.map(metric => `
                <article class="sr-metric-card sr-metric-tone-${escapeHtml(metric.tone || 'navy')}">
                    <span class="sr-metric-icon"><i class="mdi ${escapeHtml(metric.icon)}"></i></span>
                    <div>
                        <small>${escapeHtml(metric.label)}</small>
                        <strong title="${escapeHtml(formatValue(metric.value, metric.format))}">${escapeHtml(formatValue(metric.value, metric.format))}</strong>
                        <p>${escapeHtml(metric.note || '')}</p>
                    </div>
                </article>
            `).join('');
        }

        function renderChart(chart) {
            elements.chartTitle.textContent = chart.title;
            elements.chartSubtitle.textContent = chart.subtitle;
            elements.chartLegend.innerHTML = chart.series.map(series => `<span><i></i>${escapeHtml(series.name)}</span>`).join('');

            const labels = chart.labels || [];
            const amountSeries = chart.series[0]?.data || [];
            const countSeries = chart.series[1]?.data || [];
            const numericAmounts = amountSeries.map(value => Number(value) || 0);
            const maxAmount = Math.max(0, ...numericAmounts);
            const minAmount = Math.min(0, ...numericAmounts);
            const maxCount = Math.max(0, ...countSeries.map(Number));
            const numericCounts = countSeries.map(value => Number(value) || 0);
            if (!labels.length || (numericAmounts.every(value => value === 0) && numericCounts.every(value => value === 0))) {
                elements.chart.innerHTML = `<div class="sr-chart-empty"><div><i class="mdi mdi-chart-line-variant"></i><strong>Belum ada data untuk divisualkan</strong><p>Sesuaikan cabang atau periode laporan.</p></div></div>`;
                return;
            }

            const width = 820, height = 300;
            const margin = { top: 17, right: 18, bottom: 37, left: 62 };
            const plotWidth = width - margin.left - margin.right;
            const plotHeight = height - margin.top - margin.bottom;
            const x = index => margin.left + (labels.length === 1 ? plotWidth / 2 : (index / (labels.length - 1)) * plotWidth);
            const amountSpan = maxAmount - minAmount || 1;
            const flatAmount = maxAmount === minAmount;
            const y = value => flatAmount ? margin.top + plotHeight : margin.top + ((maxAmount - (Number(value) || 0)) / amountSpan) * plotHeight;
            const zeroY = y(0);
            const countY = value => margin.top + plotHeight - ((Number(value) || 0) / (maxCount || 1)) * (plotHeight * .42);
            const points = amountSeries.map((value, index) => [x(index), y(value)]);
            const linePath = points.map((point, index) => `${index ? 'L' : 'M'} ${point[0].toFixed(2)} ${point[1].toFixed(2)}`).join(' ');
            const areaPath = `${linePath} L ${x(labels.length - 1).toFixed(2)} ${zeroY.toFixed(2)} L ${x(0).toFixed(2)} ${zeroY.toFixed(2)} Z`;
            const barWidth = Math.max(2, Math.min(12, plotWidth / Math.max(labels.length, 1) * .42));

            let grids = '';
            for (let tick = 0; tick <= 4; tick++) {
                const tickValue = maxAmount - (amountSpan * tick / 4);
                const tickY = margin.top + (plotHeight / 4) * tick;
                grids += `<line class="sr-chart-grid" x1="${margin.left}" y1="${tickY}" x2="${width - margin.right}" y2="${tickY}"></line>`;
                grids += `<text class="sr-chart-axis-label" x="${margin.left - 10}" y="${tickY + 3}" text-anchor="end">${escapeHtml(compactCurrency(tickValue))}</text>`;
            }

            const labelIndexes = unique([0, Math.round((labels.length - 1) * .25), Math.round((labels.length - 1) * .5), Math.round((labels.length - 1) * .75), labels.length - 1]);
            const xLabels = labelIndexes.map(index => `<text class="sr-chart-axis-label" x="${x(index)}" y="${height - 10}" text-anchor="middle">${escapeHtml(shortChartLabel(labels[index]))}</text>`).join('');
            const bars = countSeries.map((value, index) => {
                const barY = countY(value);
                return `<rect class="sr-chart-bar" x="${x(index) - barWidth / 2}" y="${barY}" width="${barWidth}" height="${margin.top + plotHeight - barY}"></rect>`;
            }).join('');
            const pointStep = Math.max(1, Math.ceil(labels.length / 45));
            const circles = points.map((point, index) => index % pointStep === 0 || index === points.length - 1
                ? `<circle class="sr-chart-point" cx="${point[0]}" cy="${point[1]}" r="3"></circle>` : '').join('');
            const hitWidth = Math.max(5, plotWidth / Math.max(labels.length, 1));
            const hits = labels.map((label, index) => `<rect class="sr-chart-hit" x="${Math.max(margin.left, x(index) - hitWidth / 2)}" y="${margin.top}" width="${hitWidth}" height="${plotHeight}" data-index="${index}"><title>${escapeHtml(label)} · ${escapeHtml(chart.series[0].name)} ${escapeHtml(formatValue(amountSeries[index], chart.series[0].format))} · ${escapeHtml(chart.series[1].name)} ${escapeHtml(formatValue(countSeries[index], chart.series[1].format))}</title></rect>`).join('');

            elements.chart.innerHTML = `
                <svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none" aria-hidden="true">
                    <defs><linearGradient id="srAreaGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#3d83ce" stop-opacity=".24"></stop><stop offset="100%" stop-color="#3d83ce" stop-opacity=".015"></stop></linearGradient></defs>
                    ${grids}${bars}<path class="sr-chart-area" d="${areaPath}"></path><path class="sr-chart-line" d="${linePath}"></path>${circles}${xLabels}${hits}
                </svg><div class="sr-chart-tooltip" hidden></div>`;

            const tooltip = elements.chart.querySelector('.sr-chart-tooltip');
            elements.chart.querySelectorAll('.sr-chart-hit').forEach(hit => {
                hit.addEventListener('pointerenter', event => showChartTooltip(event, tooltip, chart));
                hit.addEventListener('pointermove', event => positionChartTooltip(event, tooltip));
                hit.addEventListener('pointerleave', () => { tooltip.hidden = true; });
            });
        }

        function showChartTooltip(event, tooltip, chart) {
            const index = Number(event.currentTarget.dataset.index);
            tooltip.innerHTML = `<small>${escapeHtml(formatChartTooltipLabel(chart.labels[index]))}</small><strong>${escapeHtml(chart.series[0].name)} · ${escapeHtml(formatValue(chart.series[0].data[index], chart.series[0].format))}</strong><span>${escapeHtml(chart.series[1].name)} · ${escapeHtml(formatValue(chart.series[1].data[index], chart.series[1].format))}</span>`;
            tooltip.hidden = false;
            positionChartTooltip(event, tooltip);
        }

        function positionChartTooltip(event, tooltip) {
            const bounds = elements.chart.getBoundingClientRect();
            const left = Math.max(85, Math.min(bounds.width - 85, event.clientX - bounds.left));
            const top = Math.max(82, event.clientY - bounds.top);
            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${top}px`;
        }

        function renderInsight(insight, chart) {
            elements.insightTitle.textContent = insight.title;
            elements.insightCopy.textContent = insight.copy;
            elements.peakLabel.textContent = insight.peak_label === '-' ? 'Nilai puncak' : `Puncak · ${formatChartTooltipLabel(insight.peak_label)}`;
            elements.peakValue.textContent = formatValue(insight.peak_value, chart.series[0]?.format || 'currency');
            elements.netSales.textContent = currencyFormatter.format(Number(insight.net_after_returns) || 0);
            elements.returnRatio.textContent = `${numberFormatter.format(Number(insight.return_ratio) || 0)}%`;
            elements.healthTrack.style.width = `${Math.max(0, Math.min(100, 100 - (Number(insight.return_ratio) || 0)))}%`;
        }

        function renderTable(table) {
            const columns = table.columns || [];
            state.tableColumns = columns;
            if (!table.searchable && state.search) {
                state.search = '';
                elements.search.value = '';
            }
            elements.search.disabled = !table.searchable;
            elements.search.placeholder = table.searchable ? 'Cari di laporan...' : 'Pencarian tidak diperlukan';
            elements.clearSearch.hidden = !table.searchable || !state.search;
            elements.tableTitle.textContent = table.title;
            elements.tableInfo.textContent = table.pagination.total
                ? `${integerFormatter.format(table.pagination.total)} baris sesuai filter`
                : 'Tidak ada data sesuai filter';
            elements.tableHead.innerHTML = columns.map(column => {
                const sorted = state.sort ? state.sort === column.key : table.default_sort === column.key;
                const direction = state.sort ? state.direction : table.default_direction;
                const icon = sorted ? (direction === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down') : 'mdi-unfold-more-horizontal';
                const ariaSort = sorted ? (direction === 'asc' ? 'ascending' : 'descending') : 'none';
                return `<th class="${numericType(column.type) ? 'is-number ' : ''}${sorted ? 'is-sorted ' : ''}${state.hiddenColumns.has(column.key) ? 'is-column-hidden' : ''}"
                    scope="col" data-column-key="${escapeHtml(column.key)}" aria-sort="${ariaSort}">
                    <button type="button" data-sort="${escapeHtml(column.key)}" aria-label="Urutkan berdasarkan ${escapeHtml(column.label)}${sorted ? `, saat ini ${direction === 'asc' ? 'menaik' : 'menurun'}` : ''}">
                        ${escapeHtml(column.label)} <i class="mdi ${icon}"></i>
                    </button>
                </th>`;
            }).join('');

            if (!table.rows.length) {
                elements.tableBody.innerHTML = `<tr><td colspan="${Math.max(1, columns.length)}"><div class="sr-empty-state"><div><span><i class="mdi mdi-database-search-outline"></i></span><h3>Data belum ditemukan</h3><p>Coba ubah pencarian, periode, atau cabang laporan.</p></div></div></td></tr>`;
            } else {
                elements.tableBody.innerHTML = table.rows.map((row, index) =>
                    `<tr tabindex="0" aria-selected="false" data-row-index="${index}">${columns.map(column => renderCell(row[column.key], column)).join('')}</tr>`
                ).join('');
            }

            elements.tableHead.querySelectorAll('[data-sort]').forEach(button => button.addEventListener('click', () => sortBy(button.dataset.sort, table)));
            renderColumnMenu(columns);
            bindTableRows();
            applyColumnVisibility();
            renderPagination(table.pagination);
            elements.exportCsv.disabled = table.pagination.total === 0;
            requestAnimationFrame(updateTableViewport);
        }

        function renderCell(value, column) {
            const type = column.type || 'text';
            let content = escapeHtml(column.empty_label || 'Belum tersedia');
            let classes = numericType(type) ? 'is-number' : '';

            if (value !== null && value !== undefined && value !== '') {
                if (type === 'currency') content = escapeHtml(currencyFormatter.format(Number(value) || 0));
                else if (type === 'signed_currency') {
                    const number = Number(value) || 0;
                    content = escapeHtml(`${number > 0 ? '+' : ''}${currencyFormatter.format(number)}`);
                    classes += number < 0 ? ' is-negative' : (number > 0 ? ' is-positive' : '');
                } else if (type === 'number') content = escapeHtml(numberFormatter.format(Number(value) || 0));
                else if (type === 'percent') content = escapeHtml(`${numberFormatter.format(Number(value) || 0)}%`);
                else if (type === 'date') content = escapeHtml(formatDate(value));
                else if (type === 'datetime') content = escapeHtml(formatDateTime(value));
                else if (type === 'hour') {
                    const hour = Math.max(0, Math.min(23, Number(value) || 0));
                    content = `<span class="sr-badge">${String(hour).padStart(2, '0')}:00 — ${String((hour + 1) % 24).padStart(2, '0')}:00</span>`;
                } else if (type === 'transaction_type') content = `<span class="sr-badge">${escapeHtml(transactionTypes[value] || humanize(value))}</span>`;
                else if (type === 'payment_method') content = `<span class="sr-badge is-green">${escapeHtml(paymentMethods[value] || humanize(value))}</span>`;
                else if (type === 'refund_method') content = `<span class="sr-badge is-amber">${escapeHtml(refundMethods[value] || humanize(value))}</span>`;
                else if (type === 'shift_status') {
                    const tone = value === 'open' ? 'is-green' : (value === 'closed' ? '' : 'is-amber');
                    const label = value === 'open' ? 'Aktif' : (value === 'closed' ? 'Ditutup' : (value === 'unassigned' ? 'Tanpa shift' : value));
                    content = `<span class="sr-badge ${tone}">${escapeHtml(label)}</span>`;
                } else if (type === 'status') {
                    const valueKey = String(value);
                    const tone = ['approved', 'adjusted', 'selesai', 'posted', 'lunas', 'terjadwal'].includes(valueKey)
                        ? 'is-green'
                        : (['rejected', 'cancelled', 'terlambat'].includes(valueKey) ? 'is-red' : 'is-amber');
                    content = `<span class="sr-badge ${tone}">${escapeHtml(statuses[valueKey] || humanize(valueKey))}</span>`;
                } else {
                    const text = String(value);
                    content = column.key.includes('name') || ['product_name', 'transaction_number', 'return_number', 'shift_number'].includes(column.key)
                        ? `<span class="sr-cell-primary" title="${escapeHtml(text)}">${escapeHtml(text)}</span>`
                        : escapeHtml(text);
                }
            }

            if (['revenue', 'line_total', 'amount', 'return_value', 'returns_value', 'total_discount', 'cancelled_value', 'net_sales', 'gross_profit', 'purchase_value', 'selling_value', 'potential_margin', 'difference_value', 'movement_value', 'actual_value', 'outstanding_value', 'supplier_score'].includes(column.key)) classes += ' is-strong';
            if (state.hiddenColumns.has(column.key)) classes += ' is-column-hidden';
            return `<td class="${classes.trim()}" data-column-key="${escapeHtml(column.key)}">${content}</td>`;
        }

        function renderPagination(pagination) {
            const from = pagination.from || 0;
            const to = pagination.to || 0;
            elements.paginationInfo.textContent = pagination.total
                ? `Menampilkan ${integerFormatter.format(from)}–${integerFormatter.format(to)} dari ${integerFormatter.format(pagination.total)} data`
                : '0 data';
            elements.pageLabel.textContent = `Halaman ${pagination.current_page} / ${Math.max(1, pagination.last_page)}`;
            elements.pageNumbers.innerHTML = paginationItems(pagination.current_page, Math.max(1, pagination.last_page)).map(item => {
                if (item === 'ellipsis') return '<span class="sr-page-ellipsis" aria-hidden="true">&hellip;</span>';
                const active = item === pagination.current_page;
                return `<button type="button" class="sr-page-number ${active ? 'is-active' : ''}" data-page="${item}"
                    aria-label="Buka halaman ${item}" ${active ? 'aria-current="page"' : ''}>${item}</button>`;
            }).join('');
            elements.pageNumbers.querySelectorAll('[data-page]').forEach(button => {
                button.addEventListener('click', () => goToPage(Number(button.dataset.page)));
            });
            elements.previousPage.disabled = pagination.current_page <= 1;
            elements.nextPage.disabled = pagination.current_page >= pagination.last_page;
        }

        function paginationItems(currentPage, lastPage) {
            if (lastPage <= 7) return Array.from({ length: lastPage }, (_, index) => index + 1);

            const pages = [...new Set([1, lastPage, currentPage - 1, currentPage, currentPage + 1]
                .filter(page => page >= 1 && page <= lastPage))].sort((first, second) => first - second);
            const items = [];
            pages.forEach((page, index) => {
                const previous = pages[index - 1];
                if (previous && page - previous === 2) items.push(previous + 1);
                else if (previous && page - previous > 2) items.push('ellipsis');
                items.push(page);
            });
            return items;
        }

        function goToPage(page) {
            const lastPage = state.payload?.table?.pagination?.last_page || 1;
            const targetPage = Math.max(1, Math.min(lastPage, Number(page) || 1));
            if (targetPage === state.page) return;
            state.page = targetPage;
            loadReport({ tableOnly: true });
            if (!elements.tablePanel.classList.contains('is-focus-mode')) {
                elements.tablePanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function renderColumnMenu(columns) {
            const validKeys = new Set(columns.map(column => column.key));
            state.hiddenColumns = new Set([...state.hiddenColumns].filter(key => validKeys.has(key)));
            if (columns.length && state.hiddenColumns.size >= columns.length) state.hiddenColumns.delete(columns[0].key);

            const visibleCount = Math.max(0, columns.length - state.hiddenColumns.size);
            elements.columnMenuList.innerHTML = columns.map(column => {
                const visible = !state.hiddenColumns.has(column.key);
                return `<label class="sr-column-option">
                    <input type="checkbox" value="${escapeHtml(column.key)}" ${visible ? 'checked' : ''} ${visible && visibleCount === 1 ? 'disabled' : ''}>
                    <span title="${escapeHtml(column.label)}">${escapeHtml(column.label)}</span>
                </label>`;
            }).join('');

            elements.columnMenuList.querySelectorAll('input').forEach(input => {
                input.addEventListener('change', () => {
                    if (input.checked) state.hiddenColumns.delete(input.value);
                    else state.hiddenColumns.add(input.value);
                    writeTablePreference('hidden-columns', [...state.hiddenColumns]);
                    renderColumnMenu(columns);
                    applyColumnVisibility();
                });
            });
        }

        function applyColumnVisibility() {
            const columns = state.tableColumns || [];
            const firstVisibleKey = columns.find(column => !state.hiddenColumns.has(column.key))?.key;
            elements.table.querySelectorAll('[data-column-key]').forEach(cell => {
                const hidden = state.hiddenColumns.has(cell.dataset.columnKey);
                cell.classList.toggle('is-column-hidden', hidden);
                cell.classList.toggle('is-leading-column', !hidden && cell.dataset.columnKey === firstVisibleKey);
            });

            const visibleCount = Math.max(1, columns.length - state.hiddenColumns.size);
            elements.table.style.minWidth = `${Math.max(760, visibleCount * 150)}px`;
            elements.tableViewMeta.textContent = `${visibleCount} dari ${columns.length} kolom · ${state.density === 'compact' ? 'Ringkas' : 'Nyaman'}`;
            requestAnimationFrame(updateTableViewport);
        }

        function bindTableRows() {
            elements.tableBody.querySelectorAll('tr[data-row-index]').forEach(row => {
                const toggleRow = () => {
                    const selected = row.classList.contains('is-selected');
                    elements.tableBody.querySelectorAll('tr.is-selected').forEach(activeRow => {
                        activeRow.classList.remove('is-selected');
                        activeRow.setAttribute('aria-selected', 'false');
                    });
                    if (!selected) {
                        row.classList.add('is-selected');
                        row.setAttribute('aria-selected', 'true');
                    }
                };
                row.addEventListener('click', toggleRow);
                row.addEventListener('keydown', event => {
                    if (!['Enter', ' '].includes(event.key)) return;
                    event.preventDefault();
                    toggleRow();
                });
            });
        }

        function applyTableDensity() {
            const compact = state.density === 'compact';
            elements.table.classList.toggle('is-compact', compact);
            elements.densityToggle.setAttribute('aria-pressed', String(compact));
            elements.densityToggle.title = compact ? 'Gunakan tampilan nyaman' : 'Gunakan tampilan ringkas';
            elements.densityToggle.setAttribute('aria-label', elements.densityToggle.title);
            if (state.tableColumns.length) applyColumnVisibility();
        }

        function setColumnMenu(open) {
            elements.columnMenu.hidden = !open;
            elements.columnToggle.setAttribute('aria-expanded', String(open));
            elements.columnToggle.closest('.sr-column-control')?.classList.toggle('is-open', open);
        }

        function setTableFocus(open) {
            elements.tablePanel.classList.toggle('is-focus-mode', open);
            document.body.classList.toggle('sr-table-focus-active', open);
            elements.focusTable.setAttribute('aria-pressed', String(open));
            elements.focusTable.title = open ? 'Tutup mode fokus' : 'Buka mode fokus';
            elements.focusTable.setAttribute('aria-label', elements.focusTable.title);
            elements.focusTable.querySelector('i').className = `mdi ${open ? 'mdi-arrow-collapse-all' : 'mdi-arrow-expand-all'}`;
            requestAnimationFrame(updateTableViewport);
        }

        function updateTableViewport() {
            const maximumScroll = Math.max(0, elements.tableWrap.scrollWidth - elements.tableWrap.clientWidth);
            elements.tableViewport.classList.toggle('is-scrolled', elements.tableWrap.scrollLeft > 2);
            elements.tableViewport.classList.toggle('can-scroll-right', elements.tableWrap.scrollLeft < maximumScroll - 2);
        }

        function tablePreferenceKey(name) {
            return `medcare-report-table:${app.dataset.module || 'penjualan'}:${app.dataset.report}:${name}`;
        }

        function readTablePreference(name, fallback) {
            try {
                const value = window.localStorage.getItem(tablePreferenceKey(name));
                return value === null ? fallback : JSON.parse(value);
            } catch (error) {
                return fallback;
            }
        }

        function readHiddenColumnPreference() {
            const columns = readTablePreference('hidden-columns', []);
            return Array.isArray(columns) ? columns.filter(column => typeof column === 'string') : [];
        }

        function writeTablePreference(name, value) {
            try {
                window.localStorage.setItem(tablePreferenceKey(name), JSON.stringify(value));
            } catch (error) {
                // The table remains usable when browser storage is unavailable.
            }
        }

        function renderActiveFilters(meta) {
            const chips = [
                `<span class="sr-filter-chip"><i class="mdi mdi-map-marker-outline"></i>${escapeHtml(meta.branch_label)}</span>`,
                ...(meta.supplier_label ? [`<span class="sr-filter-chip"><i class="mdi mdi-truck-outline"></i>${escapeHtml(meta.supplier_label)}</span>`] : []),
                `<span class="sr-filter-chip"><i class="mdi mdi-calendar-range"></i>${escapeHtml(formatDate(meta.date_start))} — ${escapeHtml(formatDate(meta.date_end))}</span>`,
            ];
            if (state.search) chips.push(`<span class="sr-filter-chip"><i class="mdi mdi-magnify"></i>“${escapeHtml(state.search)}”</span>`);
            elements.activeFilters.innerHTML = chips.join('');
            elements.clearSearch.hidden = !state.search;
        }

        function setLoading(loading, tableOnly = false) {
            elements.apply.disabled = loading;
            elements.refresh.disabled = loading;
            app.classList.toggle('is-loading', loading);
            app.setAttribute('aria-busy', String(loading));
            if (loading) {
                setFilterStatus('loading');
                elements.apply.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i><span>Mengolah laporan...</span>';
                const colspan = state.payload?.table?.columns?.length || 1;
                elements.tableBody.innerHTML = `<tr><td colspan="${colspan}"><div class="sr-loading-state"><i class="mdi mdi-loading mdi-spin"></i><span>Mengambil data laporan...</span></div></td></tr>`;
                if (!tableOnly) {
                    elements.metricGrid.innerHTML = Array.from({ length: 4 }, () => '<article class="sr-metric-card is-loading"><span class="sr-metric-icon"></span><div><small>Memuat data</small><strong>&nbsp;</strong><p>&nbsp;</p></div></article>').join('');
                    elements.chart.innerHTML = `<div class="sr-loading-state"><i class="mdi mdi-loading mdi-spin"></i><span>${escapeHtml(app.dataset.chartLoading || 'Menyusun visual laporan...')}</span></div>`;
                }
            } else {
                elements.apply.innerHTML = '<i class="mdi mdi-filter-check-outline"></i><span>Tampilkan data</span>';
            }
        }

        function setFilterStatus(status) {
            const labels = {
                active: 'Filter aktif',
                dirty: 'Belum diterapkan',
                loading: 'Memperbarui data',
                error: 'Gagal diperbarui',
            };
            elements.filterStatus.classList.toggle('is-dirty', status === 'dirty' || status === 'error');
            elements.filterStatus.classList.toggle('is-loading', status === 'loading');
            elements.filterStatus.innerHTML = `<i></i>${labels[status] || labels.active}`;
        }

        function syncPresetSelection() {
            const activeRange = (() => {
                if (state.dateEnd !== today) return '';
                if (state.dateStart === today) return 'today';
                if (state.dateStart === shiftDate(today, -6)) return '7';
                if (state.dateStart === shiftDate(today, -29)) return '30';
                if (state.dateStart === today.slice(0, 8) + '01') return 'mtd';
                if (state.dateStart === today.slice(0, 4) + '-01-01') return 'ytd';
                return '';
            })();
            document.querySelectorAll('.sr-period-presets [data-range]').forEach(button => {
                button.classList.toggle('is-active', button.dataset.range === activeRange);
                button.setAttribute('aria-pressed', String(button.dataset.range === activeRange));
            });
        }

        function applyCurrentFilters() {
            if (!elements.dateStart.value || !elements.dateEnd.value || elements.dateStart.value > elements.dateEnd.value) {
                toast('Tanggal akhir harus sama atau setelah tanggal mulai.', true);
                return;
            }
            state.branchId = elements.branch.value;
            state.supplierId = elements.supplier?.value || '';
            state.dateStart = elements.dateStart.value;
            state.dateEnd = elements.dateEnd.value;
            state.page = 1;
            state.filterDirty = false;
            syncPresetSelection();
            loadReport();
        }

        function renderError(message) {
            const colspan = state.payload?.table?.columns?.length || 1;
            elements.tableBody.innerHTML = `<tr><td colspan="${colspan}"><div class="sr-empty-state"><div><span><i class="mdi mdi-alert-circle-outline"></i></span><h3>Laporan belum dapat dimuat</h3><p>${escapeHtml(message)}</p></div></div></td></tr>`;
            if (!state.payload) {
                elements.chart.innerHTML = `<div class="sr-chart-empty"><div><i class="mdi mdi-alert-outline"></i><strong>Visual belum tersedia</strong><p>${escapeHtml(message)}</p></div></div>`;
                elements.tableInfo.textContent = 'Terjadi kendala saat memuat data';
            }
        }

        function sortBy(key, table) {
            const currentSort = state.sort || table.default_sort;
            const currentDirection = state.sort ? state.direction : table.default_direction;
            if (currentSort === key) state.direction = currentDirection === 'asc' ? 'desc' : 'asc';
            else { state.sort = key; state.direction = 'asc'; }
            if (!state.sort) state.sort = key;
            state.page = 1;
            loadReport({ tableOnly: true });
        }

        async function exportCsv() {
            if (!state.payload || elements.exportCsv.disabled) return;
            const buttonHtml = elements.exportCsv.innerHTML;
            elements.exportCsv.disabled = true;
            elements.exportCsv.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menyiapkan...';
            try {
                const total = state.payload.table.pagination.total;
                const maxRows = 10000;
                const pages = Math.min(Math.ceil(total / 100), Math.ceil(maxRows / 100));
                const results = [];
                for (let startPage = 1; startPage <= pages; startPage += 5) {
                    const batch = [];
                    for (let page = startPage; page < Math.min(startPage + 5, pages + 1); page++) {
                        batch.push(fetch(buildUrl({ page, per_page: 100 }), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(response => response.ok ? response.json() : Promise.reject(new Error('Gagal mengambil data ekspor.'))));
                    }
                    results.push(...await Promise.all(batch));
                }
                const columns = state.payload.table.columns;
                const rows = results.flatMap(result => result.report.table.rows);
                const csv = [columns.map(column => csvValue(column.label)).join(',')]
                    .concat(rows.map(row => columns.map(column => csvValue(exportValue(row[column.key], column.type))).join(',')))
                    .join('\r\n');
                const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `laporan-${app.dataset.module || 'penjualan'}-${state.report}-${state.dateStart}-${state.dateEnd}.csv`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(link.href);
                toast(total > maxRows ? `10.000 dari ${integerFormatter.format(total)} baris berhasil diekspor.` : `${integerFormatter.format(rows.length)} baris berhasil diekspor.`);
            } catch (error) {
                toast(error.message || 'Ekspor CSV gagal.', true);
            } finally {
                elements.exportCsv.innerHTML = buttonHtml;
                elements.exportCsv.disabled = !state.payload?.table?.pagination?.total;
            }
        }

        function exportValue(value, type) {
            if (value === null || value === undefined) return '';
            if (type === 'transaction_type') return transactionTypes[value] || humanize(value);
            if (type === 'payment_method') return paymentMethods[value] || humanize(value);
            if (type === 'refund_method') return refundMethods[value] || humanize(value);
            if (type === 'shift_status') return value === 'open' ? 'Aktif' : (value === 'closed' ? 'Ditutup' : value);
            if (type === 'status') return statuses[value] || humanize(value);
            if (type === 'hour') {
                const hour = Number(value) || 0;
                return `${String(hour).padStart(2, '0')}:00 - ${String((hour + 1) % 24).padStart(2, '0')}:00`;
            }
            return value;
        }

        function syncUrl() {
            const url = new URL(window.location.href);
            const values = {
                branch_id: state.branchId, date_start: state.dateStart, date_end: state.dateEnd,
                supplier_id: state.supplierId,
                search: state.search, sort: state.sort, direction: state.direction,
                page: state.page > 1 ? state.page : '', per_page: state.perPage !== 25 ? state.perPage : '',
            };
            Object.entries(values).forEach(([key, value]) => value ? url.searchParams.set(key, value) : url.searchParams.delete(key));
            window.history.replaceState({}, '', url);
        }

        function toast(message, isError = false) {
            clearTimeout(state.toastTimer);
            elements.toast.hidden = false;
            elements.toast.classList.toggle('is-error', isError);
            elements.toast.querySelector('i').className = `mdi ${isError ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline'}`;
            elements.toast.querySelector('span').textContent = message;
            state.toastTimer = setTimeout(() => { elements.toast.hidden = true; }, 3800);
        }

        function responseMessage(result, status) {
            if (result?.message) return result.message;
            if (result?.errors) return Object.values(result.errors).flat()[0] || 'Parameter laporan tidak valid.';
            return status === 422 ? 'Parameter laporan tidak valid.' : 'Laporan belum dapat dimuat.';
        }

        function formatValue(value, format) {
            const numeric = Number(value) || 0;
            if (format === 'currency') return currencyFormatter.format(numeric);
            if (format === 'signed_currency') return `${numeric > 0 ? '+' : ''}${currencyFormatter.format(numeric)}`;
            if (format === 'percent') return `${numberFormatter.format(numeric)}%`;
            return numberFormatter.format(numeric);
        }

        function compactCurrency(value) {
            return Number(value) === 0 ? '0' : `Rp${compactCurrencyFormatter.format(Number(value)).replace(/\s/g, '')}`;
        }

        function formatDate(value) {
            const date = parseDate(value);
            return date ? dateFormatter.format(date) : '—';
        }

        function formatDateTime(value) {
            const date = parseDate(value, true);
            return date ? dateTimeFormatter.format(date).replace('.', ':') : '—';
        }

        function parseDate(value, withTime = false) {
            if (!value) return null;
            const normalized = String(value).replace(' ', 'T');
            const date = withTime ? new Date(normalized) : new Date(normalized.slice(0, 10) + 'T00:00:00');
            return Number.isNaN(date.getTime()) ? null : date;
        }

        function shortChartLabel(value) {
            if (/^\d{4}-\d{2}-\d{2}$/.test(String(value))) {
                const date = parseDate(value);
                return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short' }).format(date);
            }
            return String(value);
        }

        function formatChartTooltipLabel(value) {
            return /^\d{4}-\d{2}-\d{2}$/.test(String(value)) ? formatDate(value) : String(value || '—');
        }

        function localDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function shiftDate(value, days) {
            const date = parseDate(value);
            date.setDate(date.getDate() + days);
            return localDate(date);
        }

        function humanize(value) {
            return String(value || '—').replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase());
        }

        function numericType(type) {
            return ['number', 'currency', 'signed_currency', 'percent'].includes(type);
        }

        function csvValue(value) {
            let text = String(value ?? '');
            if (/^[=+\-@]/.test(text)) text = "'" + text;
            return `"${text.replace(/"/g, '""')}"`;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, character => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
            })[character]);
        }

        function unique(values) { return [...new Set(values)]; }

        elements.form.addEventListener('submit', event => {
            event.preventDefault();
            applyCurrentFilters();
        });

        document.querySelectorAll('.sr-period-presets [data-range]').forEach(button => {
            button.addEventListener('click', () => {
                const range = button.dataset.range;
                const end = today;
                let start = end;
                if (/^\d+$/.test(range)) start = shiftDate(end, -(Number(range) - 1));
                else if (range === 'mtd') start = end.slice(0, 8) + '01';
                else if (range === 'ytd') start = end.slice(0, 4) + '-01-01';
                elements.dateStart.value = start;
                elements.dateEnd.value = end;
                state.dateStart = start;
                state.dateEnd = end;
                state.branchId = elements.branch.value;
                state.supplierId = elements.supplier?.value || '';
                state.page = 1;
                state.filterDirty = false;
                syncPresetSelection();
                loadReport();
            });
        });

        [elements.dateStart, elements.dateEnd].forEach(input => input.addEventListener('change', () => {
            document.querySelectorAll('.sr-period-presets [data-range]').forEach(button => button.classList.remove('is-active'));
            state.filterDirty = true;
            setFilterStatus('dirty');
        }));
        elements.branch.addEventListener('change', () => {
            state.filterDirty = true;
            setFilterStatus('dirty');
        });
        elements.supplier?.addEventListener('change', () => {
            state.filterDirty = true;
            setFilterStatus('dirty');
        });

        elements.search.addEventListener('input', () => {
            clearTimeout(state.searchTimer);
            elements.clearSearch.hidden = !elements.search.value;
            state.searchTimer = setTimeout(() => {
                state.search = elements.search.value.trim();
                state.page = 1;
                loadReport({ tableOnly: true });
            }, 420);
        });

        elements.clearSearch.addEventListener('click', () => {
            elements.search.value = '';
            state.search = '';
            state.page = 1;
            elements.clearSearch.hidden = true;
            loadReport({ tableOnly: true });
            elements.search.focus();
        });

        elements.pageSize.addEventListener('change', () => {
            state.perPage = Number(elements.pageSize.value) || 25;
            state.page = 1;
            loadReport({ tableOnly: true });
        });

        elements.previousPage.addEventListener('click', () => {
            goToPage(state.page - 1);
        });

        elements.nextPage.addEventListener('click', () => {
            goToPage(state.page + 1);
        });

        elements.filterToggle.addEventListener('click', () => {
            const collapsed = elements.filterBody.classList.toggle('is-collapsed');
            elements.filterToggle.setAttribute('aria-expanded', String(!collapsed));
            elements.filterToggle.title = collapsed ? 'Tampilkan filter' : 'Sembunyikan filter';
            elements.filterToggle.querySelector('i').className = `mdi ${collapsed ? 'mdi-chevron-down' : 'mdi-chevron-up'}`;
        });

        elements.resetFilter.addEventListener('click', () => {
            state.branchId = '';
            state.supplierId = '';
            state.dateStart = initialStart;
            state.dateEnd = initialEnd;
            state.search = '';
            state.sort = '';
            state.direction = '';
            state.page = 1;
            state.perPage = 25;
            state.filterDirty = false;
            elements.branch.value = '';
            if (elements.supplier) elements.supplier.value = '';
            elements.dateStart.value = initialStart;
            elements.dateEnd.value = initialEnd;
            elements.search.value = '';
            elements.pageSize.value = '25';
            syncPresetSelection();
            loadReport();
        });

        elements.refresh.addEventListener('click', () => loadReport());
        elements.exportCsv.addEventListener('click', exportCsv);
        elements.print.addEventListener('click', () => window.print());
        elements.columnToggle.addEventListener('click', () => setColumnMenu(elements.columnMenu.hidden));
        elements.resetColumns.addEventListener('click', () => {
            state.hiddenColumns.clear();
            writeTablePreference('hidden-columns', []);
            renderColumnMenu(state.tableColumns);
            applyColumnVisibility();
            toast('Semua kolom ditampilkan kembali.');
        });
        elements.densityToggle.addEventListener('click', () => {
            state.density = state.density === 'compact' ? 'comfortable' : 'compact';
            writeTablePreference('density', state.density);
            applyTableDensity();
        });
        elements.focusTable.addEventListener('click', () => {
            setTableFocus(!elements.tablePanel.classList.contains('is-focus-mode'));
        });
        elements.tableWrap.addEventListener('scroll', updateTableViewport, { passive: true });

        document.addEventListener('click', event => {
            if (!event.target.closest('.sr-column-control')) setColumnMenu(false);
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                if (!elements.columnMenu.hidden) setColumnMenu(false);
                else if (elements.tablePanel.classList.contains('is-focus-mode')) setTableFocus(false);
                return;
            }
            const target = event.target;
            const typing = ['INPUT', 'SELECT', 'TEXTAREA'].includes(target.tagName) || target.isContentEditable;
            if (event.key === '/' && !typing && !elements.search.disabled) {
                event.preventDefault();
                elements.search.focus();
            }
        });

        document.querySelectorAll('.sr-report-link').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                const target = new URL(link.href, window.location.origin);
                if (state.branchId) target.searchParams.set('branch_id', state.branchId);
                if (state.supplierId) target.searchParams.set('supplier_id', state.supplierId);
                target.searchParams.set('date_start', state.dateStart);
                target.searchParams.set('date_end', state.dateEnd);
                window.location.assign(target.toString());
            });
        });

        function syncReportNavControls() {
            if (!elements.reportNav || !elements.reportPrevious || !elements.reportNext) return;
            const maximum = Math.max(0, elements.reportNav.scrollWidth - elements.reportNav.clientWidth);
            elements.reportPrevious.disabled = elements.reportNav.scrollLeft <= 2;
            elements.reportNext.disabled = elements.reportNav.scrollLeft >= maximum - 2;
        }

        function scrollReportNav(direction) {
            const distance = Math.max(220, elements.reportNav.clientWidth * .72);
            elements.reportNav.scrollBy({ left: direction * distance, behavior: 'smooth' });
        }

        elements.reportPrevious?.addEventListener('click', () => scrollReportNav(-1));
        elements.reportNext?.addEventListener('click', () => scrollReportNav(1));
        elements.reportNav?.addEventListener('scroll', syncReportNavControls, { passive: true });
        elements.reportNav?.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            scrollReportNav(event.key === 'ArrowLeft' ? -1 : 1);
        });
        window.addEventListener('resize', () => {
            syncReportNavControls();
            updateTableViewport();
        }, { passive: true });
        requestAnimationFrame(() => {
            elements.reportNav?.querySelector('.is-active')?.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
            syncReportNavControls();
        });

        loadReport();
    })();
</script>
