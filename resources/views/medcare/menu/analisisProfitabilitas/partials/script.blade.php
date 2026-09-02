<script>
    document.addEventListener('DOMContentLoaded', function () {
        const app = document.getElementById('profitabilityAnalysisApp');
        if (!app) return;

        const element = id => document.getElementById(id);
        const state = { analysis: null, controller: null, branchesLoaded: false, toastTimer: null };
        const escapeHtml = value => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        const decimal = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
        const money = value => `${Number(value || 0) < 0 ? '-' : ''}Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.abs(Number(value || 0)))}`;
        const percent = value => value === null || value === undefined ? '—' : `${decimal(value)}%`;
        const gmroi = value => value === null || value === undefined ? '—' : `${decimal(value)}x`;
        const isoDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        const displayDate = value => {
            const date = new Date(`${value}T00:00:00`);
            return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        };
        const firstError = payload => {
            const errors = payload?.errors || {};
            const first = Object.values(errors)[0];
            return Array.isArray(first) ? first[0] : (first || payload?.message || 'Data analisis gagal dimuat.');
        };
        const metricTone = value => Number(value || 0) > 0 ? 'pa-positive' : (Number(value || 0) < 0 ? 'pa-negative' : 'pa-neutral');
        const metricPill = (value, formatter) => value === null || value === undefined
            ? '<span class="pa-metric-pill is-na">N/A</span>'
            : `<span class="pa-metric-pill ${Number(value) < 0 ? 'is-negative' : ''}">${formatter(value)}</span>`;
        const showToast = message => {
            const toast = element('paToast');
            toast.textContent = message;
            toast.classList.add('is-visible');
            window.clearTimeout(state.toastTimer);
            state.toastTimer = window.setTimeout(() => toast.classList.remove('is-visible'), 2600);
        };

        const productCell = row => `<div class="pa-product"><span><i class="mdi mdi-pill"></i></span><span><b title="${escapeHtml(row.name)}">${escapeHtml(row.name)}</b><small>${escapeHtml(row.code)} &middot; ${escapeHtml(row.category)}</small></span></div>`;

        function loadBranches(meta) {
            if (state.branchesLoaded) return;
            const select = element('paBranch');
            (meta.branches || []).forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.id;
                option.textContent = `${branch.name}${branch.code ? ` · ${branch.code}` : ''}`;
                select.appendChild(option);
            });
            state.branchesLoaded = true;
        }

        function renderSummary(analysis) {
            const summary = analysis.summary;
            const meta = analysis.meta;
            element('paBranchLabel').textContent = meta.branch_label;
            element('paPeriodLabel').textContent = `${displayDate(meta.date_start)} – ${displayDate(meta.date_end)}`;
            element('paGeneratedAt').textContent = `Dihitung ${String(meta.generated_at).replace(' ', ' · ')}`;
            element('paHeroGmroi').textContent = gmroi(summary.gmroi);
            element('paHeroGmroiCopy').textContent = summary.gmroi === null
                ? 'Belum ada rata-rata investasi persediaan yang dapat dijadikan pembagi.'
                : `Setiap Rp 1 modal persediaan menghasilkan Rp ${decimal(summary.gmroi)} laba kotor selama ${meta.period_days} hari.`;
            element('paNetSales').textContent = money(summary.net_sales);
            element('paNetHpp').textContent = money(summary.net_hpp);
            element('paGrossProfit').textContent = money(summary.gross_profit);
            element('paGrossProfit').className = metricTone(summary.gross_profit);
            element('paGrossMargin').textContent = percent(summary.gross_margin);
            element('paAverageInventory').textContent = money(summary.average_inventory_cost);
            element('paGmroi').textContent = gmroi(summary.gmroi);
            element('paProfitabilityCopy').textContent = `${summary.profitable_products} produk untung · ${summary.loss_products} produk rugi`;
            element('paGmroiCopy').textContent = summary.gmroi === null
                ? 'Tidak dapat dihitung karena rata-rata modal stok nol.'
                : `${percent(summary.gmroi_percent)} return terhadap modal stok.`;
            element('paProductCount').textContent = `${meta.displayed_products} dari ${meta.total_products} produk`;
            element('paTableNote').textContent = `${meta.revenue_basis} ${meta.displayed_products < meta.total_products ? `Tabel dibatasi ${meta.displayed_products} produk.` : ''}`;
            element('paInventoryBasis').textContent = meta.inventory_basis;
            loadBranches(meta);
        }

        function renderProducts(rows) {
            const body = element('paProductRows');
            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="9" class="pa-empty">Tidak ada produk dengan aktivitas penjualan atau retur pada filter ini.</td></tr>';
                return;
            }
            body.innerHTML = rows.map(row => `<tr>
                <td><span class="pa-rank ${row.profit_rank <= 3 ? 'is-top' : ''}">#${row.profit_rank ?? '-'}</span></td>
                <td>${productCell(row)}</td>
                <td class="text-end pa-number">${decimal(row.net_qty)}</td>
                <td class="text-end pa-number">${money(row.net_sales)}</td>
                <td class="text-end pa-number">${money(row.net_hpp)}</td>
                <td class="text-end pa-number ${metricTone(row.gross_profit)}">${money(row.gross_profit)}</td>
                <td class="text-end">${metricPill(row.gross_margin, percent)}</td>
                <td class="text-end pa-number">${money(row.average_inventory_cost)}</td>
                <td class="text-end">${metricPill(row.gmroi, gmroi)}</td>
            </tr>`).join('');
        }

        function renderRanking(targetId, rows, metricKey, formatter, tone = '') {
            const target = element(targetId);
            if (!rows.length) {
                target.innerHTML = `<div class="pa-empty">Belum ada produk yang dapat dihitung untuk metrik ini.</div>`;
                return;
            }
            const positiveValues = rows.map(row => Math.max(0, Number(row[metricKey] || 0)));
            const max = Math.max(...positiveValues, 0.0001);
            target.innerHTML = rows.map((row, index) => {
                const value = row[metricKey];
                const width = Math.max(3, (Math.max(0, Number(value || 0)) / max) * 100);
                return `<div class="pa-ranking-row ${tone}"><b>${index + 1}</b><div class="pa-ranking-copy"><div><strong title="${escapeHtml(row.name)}">${escapeHtml(row.name)}</strong><small>${formatter(value)}</small></div><div class="pa-bar"><span style="width:${width}%"></span></div></div><span class="pa-ranking-note">${escapeHtml(row.code)}</span></div>`;
            }).join('');
        }

        function renderTopProfit(rows) {
            const target = element('paTopProfitRows');
            if (!rows.length) {
                target.innerHTML = '<div class="pa-empty">Belum ada produk dengan laba pada periode ini.</div>';
                return;
            }
            target.innerHTML = rows.slice(0, 10).map((row, index) => `<article class="pa-top-card"><span>0${index + 1}</span><i class="mdi ${index === 0 ? 'mdi-trophy' : 'mdi-star-outline'}"></i><h3 title="${escapeHtml(row.name)}">${escapeHtml(row.name)}</h3><small>${escapeHtml(row.code)} · ${escapeHtml(row.category)}</small><strong class="${metricTone(row.gross_profit)}">${money(row.gross_profit)}</strong><p>Margin ${percent(row.gross_margin)} · GMROI ${gmroi(row.gmroi)}</p></article>`).join('');
        }

        function render(analysis) {
            state.analysis = analysis;
            renderSummary(analysis);
            renderProducts(analysis.rows || []);
            renderRanking('paMarginRows', analysis.margin_leaders || [], 'gross_margin', percent);
            renderRanking('paGmroiRows', analysis.gmroi_leaders || [], 'gmroi', gmroi, 'is-violet');
            renderTopProfit(analysis.top_profit_products || []);
        }

        async function load() {
            state.controller?.abort();
            state.controller = new AbortController();
            element('paLoading').hidden = false;
            element('paError').hidden = true;
            element('paApply').disabled = true;
            app.setAttribute('aria-busy', 'true');
            const params = new URLSearchParams(new FormData(element('paFilterForm')));
            [...params.entries()].forEach(([key, value]) => { if (!value) params.delete(key); });
            try {
                const response = await fetch(`${app.dataset.url}?${params.toString()}`, { headers: { Accept: 'application/json' }, signal: state.controller.signal });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(firstError(payload));
                render(payload.analysis);
            } catch (error) {
                if (error.name === 'AbortError') return;
                element('paErrorCopy').textContent = error.message || 'Data analisis gagal dimuat.';
                element('paError').hidden = false;
            } finally {
                element('paLoading').hidden = true;
                element('paApply').disabled = false;
                app.removeAttribute('aria-busy');
            }
        }

        function setRange(days) {
            const end = new Date();
            const start = new Date(end);
            if (days === 'mtd') start.setDate(1); else start.setDate(end.getDate() - Number(days) + 1);
            element('paDateStart').value = isoDate(start);
            element('paDateEnd').value = isoDate(end);
            document.querySelectorAll('.pa-presets button').forEach(button => button.classList.toggle('is-active', button.dataset.days === String(days)));
            load();
        }

        function exportCsv() {
            const rows = state.analysis?.rows || [];
            if (!rows.length) return showToast('Tidak ada data untuk diekspor.');
            const headers = ['Peringkat Profit','Kode','Produk','Kategori','Qty Neto','Penjualan Neto','HPP Neto','Laba Kotor','Margin (%)','Stok Awal (Rp)','Stok Akhir (Rp)','Rata-rata Modal Stok (Rp)','GMROI (x)'];
            const csvRows = rows.map(row => [row.profit_rank,row.code,row.name,row.category,row.net_qty,row.net_sales,row.net_hpp,row.gross_profit,row.gross_margin ?? '',row.opening_inventory_cost,row.closing_inventory_cost,row.average_inventory_cost,row.gmroi ?? '']);
            const quote = value => `"${String(value ?? '').replaceAll('"', '""')}"`;
            const csv = '\uFEFF' + [headers, ...csvRows].map(row => row.map(quote).join(',')).join('\r\n');
            const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
            const link = document.createElement('a');
            link.href = url;
            link.download = `analisis-profitabilitas-${state.analysis.meta.date_start}-${state.analysis.meta.date_end}.csv`;
            link.click();
            URL.revokeObjectURL(url);
            showToast('Data profitabilitas berhasil diekspor.');
        }

        element('paFilterForm').addEventListener('submit', event => { event.preventDefault(); load(); });
        element('paReset').addEventListener('click', () => {
            element('paFilterForm').reset();
            element('paSearch').value = '';
            setRange(30);
        });
        element('paRetry').addEventListener('click', load);
        element('paExportCsv').addEventListener('click', exportCsv);
        document.querySelectorAll('.pa-presets button').forEach(button => button.addEventListener('click', () => setRange(button.dataset.days)));
        document.querySelectorAll('.pa-section-nav a').forEach(link => link.addEventListener('click', () => {
            document.querySelectorAll('.pa-section-nav a').forEach(item => item.classList.remove('is-active'));
            link.classList.add('is-active');
        }));
        load();
    });
</script>
