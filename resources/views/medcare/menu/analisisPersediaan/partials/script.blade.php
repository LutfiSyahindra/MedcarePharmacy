<script>
    document.addEventListener('DOMContentLoaded', function () {
        const app = document.getElementById('inventoryAnalysisApp');
        if (!app) return;

        const type = app.dataset.analysis;
        const dataUrl = app.dataset.url;
        const createPoUrl = app.dataset.createPoUrl || '';
        const stockCardUrl = @json(route('kartuStok.kartuStok'));
        const distributorOptions = @json($distributors->map(fn ($distributor) => [
            'id' => (int) $distributor->id,
            'label' => $distributor->nama.($distributor->kode ? ' · '.$distributor->kode : ''),
        ])->values());
        const state = {
            rows: [], page: 1, pageSize: 25, controller: null, branchesLoaded: false,
            meta: null, empty: null, sortIndex: null, sortDirection: 'asc', priorityId: null,
            appliedParams: '', toastTimer: null, selectedIds: new Set(), creatingPo: false,
        };
        const element = id => document.getElementById(id);

        const escapeHtml = value => String(value ?? '')
            .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        const number = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
        const decimal = value => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(Number(value || 0));
        const currency = value => `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value || 0))}`;
        const percent = value => `${number(value)}%`;
        const dateTime = value => {
            if (!value) return '<span class="ia-muted">Belum ada</span>';
            const parsed = new Date(String(value).replace(' ', 'T'));
            return Number.isNaN(parsed.getTime()) ? escapeHtml(value) : parsed.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        };
        const date = value => {
            if (!value) return '<span class="ia-muted">-</span>';
            const parsed = new Date(`${String(value).slice(0, 10)}T00:00:00`);
            return Number.isNaN(parsed.getTime()) ? escapeHtml(value) : parsed.toLocaleDateString('id-ID', { dateStyle: 'medium' });
        };
        const dateText = value => {
            if (!value) return '-';
            const parsed = new Date(`${String(value).slice(0, 10)}T00:00:00`);
            return Number.isNaN(parsed.getTime()) ? String(value) : parsed.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        };
        const toIsoDate = value => {
            const year = value.getFullYear();
            const month = String(value.getMonth() + 1).padStart(2, '0');
            const day = String(value.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };
        const numeric = (value, suffix = '') => `<span class="ia-number">${decimal(value)}${suffix}</span>`;
        const money = value => `<span class="ia-number">${currency(value)}</span>`;
        const statusTone = value => ({
            'A': 'green', 'B': 'amber', 'C': 'red', 'Tinggi': 'red', 'Sedang': 'amber',
            'Terencana': 'green', 'Kosong': 'red', 'Menipis': 'amber'
        }[value] || 'blue');
        const badge = value => `<span class="ia-status is-${statusTone(value)}">${escapeHtml(value || '-')}</span>`;
        const product = row => `
            <a class="ia-product text-decoration-none" href="${stockCardUrl}?obat_id=${encodeURIComponent(row.id)}">
                <span class="ia-product-mark"><i class="mdi mdi-pill"></i></span>
                <span><strong title="${escapeHtml(row.name)}">${escapeHtml(row.name)}</strong><small>${escapeHtml(row.code)} &middot; ${escapeHtml(row.unit)}</small></span>
            </a>`;
        const inactivity = row => row.days_since_out === null
            ? '<span class="ia-status is-red">Belum pernah keluar</span>'
            : numeric(row.days_since_out, ' hari');
        const daysCover = value => value === null
            ? '<span class="ia-muted">Tanpa penjualan</span>'
            : numeric(value, ' hari');
        const stockTurnover = value => value === null
            ? '<span class="ia-muted">Tidak dapat dihitung</span>'
            : numeric(value, 'x');
        const hasSinglePoBranch = () => Boolean(state.meta?.selected_branch_id)
            || (state.meta?.branches || []).length === 1;
        const purchaseSelector = row => {
            const ready = Boolean(row.can_create_po) && hasSinglePoBranch();
            const reason = !hasSinglePoBranch()
                ? 'Pilih satu cabang untuk membuat PO.'
                : (row.po_issue || 'Pilih produk untuk dibuatkan PO.');

            return `<label class="ia-po-selector" title="${escapeHtml(reason)}">
                <input type="checkbox" class="ia-po-row-check" value="${escapeHtml(row.id)}"
                    ${state.selectedIds.has(String(row.id)) ? 'checked' : ''} ${ready ? '' : 'disabled'}
                    aria-label="Pilih ${escapeHtml(row.name)} untuk dibuatkan PO">
                <span><i class="mdi ${ready ? 'mdi-cart-plus' : 'mdi-alert-circle-outline'}"></i></span>
            </label>`;
        };

        const configs = {
            'pergerakan-stok': {
                distributionTitle: 'Produk dengan arus terbesar',
                distributionSubtitle: 'Gabungan kuantitas masuk dan keluar selama periode aktif.',
                distributionFormat: decimal,
                method: meta => `Arus dihitung dari kartu stok tanggal ${meta.date_start} sampai ${meta.date_end}. Turnover periode = stok keluar ÷ rata-rata stok, sedangkan rata-rata stok = (stok awal + stok akhir) ÷ 2. Jika rata-rata stok nol, turnover tidak dapat dihitung.`,
                columns: [
                    { label: '#', value: row => row.rank, render: row => numeric(row.rank) },
                    { label: 'Produk', value: row => `${row.code} ${row.name}`, render: product },
                    { label: 'Stok awal', value: row => row.opening_stock, render: row => numeric(row.opening_stock, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Stok akhir', value: row => row.ending_stock, render: row => numeric(row.ending_stock, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Rata-rata stok', value: row => row.average_stock, render: row => numeric(row.average_stock, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Masuk', value: row => row.qty_in, render: row => `<span class="ia-status is-green">+${decimal(row.qty_in)}</span>` },
                    { label: 'Keluar', value: row => row.qty_out, render: row => `<span class="ia-status is-orange">-${decimal(row.qty_out)}</span>` },
                    { label: 'Bersih', value: row => row.net_movement, render: row => numeric(row.net_movement) },
                    { label: 'Turnover periode', value: row => row.stock_turnover ?? '', render: row => stockTurnover(row.stock_turnover) },
                    { label: 'Transaksi stok', value: row => row.movement_count, render: row => numeric(row.movement_count) },
                    { label: 'Terakhir bergerak', value: row => row.last_movement_at || '', render: row => dateTime(row.last_movement_at) },
                ],
            },
            'pareto-abc': {
                distributionTitle: 'Nilai omzet per kelas',
                distributionSubtitle: 'Kontribusi omzet bersih produk dalam kelompok A, B, dan C.',
                distributionFormat: currency,
                method: meta => `Produk diurutkan berdasarkan omzet bersih periode ${meta.period_days} hari. Kelas dihitung dari kumulatif setelah kontribusi produk ditambahkan: A hingga 80%, B di atas 80% hingga 95%, dan C di atas 95%. Produk beromzet terbesar tetap masuk kelas A jika kontribusinya sendiri melewati 80%.`,
                columns: [
                    { label: 'Peringkat', value: row => row.rank, render: row => numeric(row.rank) },
                    { label: 'Kelas', value: row => row.abc_class, render: row => badge(row.abc_class) },
                    { label: 'Produk', value: row => `${row.code} ${row.name}`, render: product },
                    { label: 'Qty terjual', value: row => row.sales_qty, render: row => numeric(row.sales_qty, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Omzet bersih', value: row => row.revenue, render: row => money(row.revenue) },
                    { label: 'Kontribusi', value: row => row.contribution, render: row => numeric(row.contribution, '%') },
                    { label: 'Kumulatif', value: row => row.cumulative, render: row => numeric(row.cumulative, '%') },
                    { label: 'Stok saat ini', value: row => row.current_stock, render: row => numeric(row.current_stock) },
                ],
            },
            'stok-hampir-habis': {
                distributionTitle: 'Status ketersediaan kritis',
                distributionSubtitle: 'Perbandingan produk kosong dan produk yang masih menipis.',
                distributionFormat: number,
                method: () => 'Produk masuk daftar ketika stok saat ini kurang dari atau sama dengan stok minimum pada master obat. Prioritaskan stok kosong, kemudian kekurangan terbesar dan hari ketersediaan terpendek.',
                columns: [
                    { label: 'Produk', value: row => `${row.code} ${row.name}`, render: product },
                    { label: 'Status', value: row => row.stock_status, render: row => badge(row.stock_status) },
                    { label: 'Stok saat ini', value: row => row.current_stock, render: row => numeric(row.current_stock, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Stok minimum', value: row => row.minimum_stock, render: row => numeric(row.minimum_stock) },
                    { label: 'Kekurangan', value: row => row.shortage, render: row => `<span class="ia-number text-danger">${decimal(row.shortage)}</span>` },
                    { label: 'Estimasi bertahan', value: row => row.days_cover ?? '', render: row => daysCover(row.days_cover) },
                    { label: 'Keluar terakhir', value: row => row.last_out_at || '', render: row => dateTime(row.last_out_at) },
                    { label: 'Distributor', value: row => row.supplier, render: row => escapeHtml(row.supplier) },
                ],
            },
            'slow-moving': {
                distributionTitle: 'Umur tidak bergerak',
                distributionSubtitle: 'Sebaran produk berdasarkan lamanya tanpa pergerakan keluar.',
                distributionFormat: number,
                method: meta => `Slow moving adalah stok positif yang tidak memiliki pergerakan keluar selama ${meta.slow_days} hingga ${meta.dead_days - 1} hari. Umur dihitung dari transaksi keluar terakhir atau tanggal awal stok jika belum pernah keluar.`,
                columns: [
                    { label: 'Produk', value: row => `${row.code} ${row.name}`, render: product },
                    { label: 'Stok saat ini', value: row => row.current_stock, render: row => numeric(row.current_stock, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Nilai stok', value: row => row.stock_value, render: row => money(row.stock_value) },
                    { label: 'Keluar terakhir', value: row => row.last_out_at || '', render: row => dateTime(row.last_out_at) },
                    { label: 'Tidak bergerak', value: row => row.days_since_out ?? '', render: inactivity },
                    { label: 'Batch aktif', value: row => row.batch_count, render: row => numeric(row.batch_count) },
                    { label: 'ED terdekat', value: row => row.nearest_expiry || '', render: row => date(row.nearest_expiry) },
                    { label: 'Distributor', value: row => row.supplier, render: row => escapeHtml(row.supplier) },
                ],
            },
            'dead-stock': {
                distributionTitle: 'Modal tertahan terbesar',
                distributionSubtitle: 'Produk dead stock dengan nilai persediaan tertinggi.',
                distributionFormat: currency,
                method: meta => `Dead stock adalah stok positif tanpa pergerakan keluar selama minimal ${meta.dead_days} hari. Produk yang belum pernah keluar juga masuk bila umur stoknya telah melewati batas tersebut.`,
                columns: [
                    { label: 'Produk', value: row => `${row.code} ${row.name}`, render: product },
                    { label: 'Stok mati', value: row => row.current_stock, render: row => numeric(row.current_stock, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Modal tertahan', value: row => row.stock_value, render: row => money(row.stock_value) },
                    { label: 'Harga beli rata-rata', value: row => row.average_purchase_price, render: row => money(row.average_purchase_price) },
                    { label: 'Keluar terakhir', value: row => row.last_out_at || '', render: row => dateTime(row.last_out_at) },
                    { label: 'Tidak bergerak', value: row => row.days_since_out ?? '', render: inactivity },
                    { label: 'ED terdekat', value: row => row.nearest_expiry || '', render: row => date(row.nearest_expiry) },
                    { label: 'Distributor', value: row => row.supplier, render: row => escapeHtml(row.supplier) },
                ],
            },
            'saran-pembelian': {
                distributionTitle: 'Prioritas pembelian',
                distributionSubtitle: 'Jumlah produk pada setiap tingkat urgensi pengadaan.',
                distributionFormat: number,
                method: meta => `Hanya produk dengan permintaan pada periode aktif yang disarankan. Target stok = kebutuhan rata-rata harian × ${meta.cover_days} hari + stok minimum. Stok expired dikeluarkan dan sisa PO berjalan dikurangkan. Prioritas tinggi berarti stok akan habis dalam lead time ${meta.lead_days} hari.`,
                columns: [
                    { label: 'Pilih', value: row => row.id, render: purchaseSelector, sortable: false, export: false, selection: true },
                    { label: 'Produk', value: row => `${row.code} ${row.name}`, render: product },
                    { label: 'Prioritas', value: row => row.priority, render: row => badge(row.priority) },
                    { label: 'Stok usable', value: row => row.current_stock, render: row => numeric(row.current_stock, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Rata-rata / hari', value: row => row.average_daily_demand, render: row => numeric(row.average_daily_demand) },
                    { label: 'Estimasi bertahan', value: row => row.days_cover ?? '', render: row => daysCover(row.days_cover) },
                    { label: 'Target stok', value: row => row.target_stock, render: row => numeric(row.target_stock) },
                    { label: 'PO berjalan', value: row => row.pending_order_qty, render: row => numeric(row.pending_order_qty, ` ${escapeHtml(row.unit)}`) },
                    { label: 'Saran beli bersih', value: row => row.suggested_qty, render: row => `<span class="ia-status is-green">${decimal(row.suggested_qty)} ${escapeHtml(row.unit)}</span>` },
                    { label: 'Harga acuan', value: row => row.average_purchase_price, render: row => money(row.average_purchase_price) },
                    { label: 'Estimasi', value: row => row.estimated_purchase, render: row => money(row.estimated_purchase) },
                ],
            },
        };
        const config = configs[type];

        const toneColors = {
            blue: '#4f6cf7', green: '#0d9f73', amber: '#d99400', orange: '#e66c24',
            red: '#d9445a', violet: '#7457e8',
        };

        function formatSummary(item) {
            if (item.format === 'currency') return currency(item.value);
            if (item.format === 'percent') return percent(item.value);
            if (item.format === 'days') return `${number(item.value)} hari`;
            return number(item.value);
        }

        function loading(active) {
            app.classList.toggle('is-loading', active);
            element('iaApplyFilter').disabled = active;
            element('iaRefresh').disabled = active;
            element('iaApplyFilter').innerHTML = active
                ? '<i class="mdi mdi-loading mdi-spin"></i> Mengolah...'
                : '<i class="mdi mdi-chart-box-plus-outline"></i> Terapkan Analisis';
            if (active) {
                element('iaTableBody').innerHTML = `<tr><td colspan="${config.columns.length}"><div class="ia-loading-state"><i class="mdi mdi-loading mdi-spin"></i> Mengambil dan mengolah data persediaan...</div></td></tr>`;
            }
        }

        function populateBranches(meta) {
            if (state.branchesLoaded) return;
            const select = element('iaBranch');
            (meta.branches || []).forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.id;
                option.textContent = `${branch.code || '-'} — ${branch.name}`;
                select.appendChild(option);
            });
            if (meta.selected_branch_id) select.value = String(meta.selected_branch_id);
            state.branchesLoaded = true;
        }

        function notify(message, icon = 'mdi-check-circle-outline') {
            const toast = element('iaToast');
            window.clearTimeout(state.toastTimer);
            toast.querySelector('i').className = `mdi ${icon}`;
            toast.querySelector('p').textContent = message;
            toast.hidden = false;
            state.toastTimer = window.setTimeout(() => { toast.hidden = true; }, 3200);
        }

        function updateCreatePoAction() {
            if (type !== 'saran-pembelian') return;
            const button = element('iaCreatePo');
            const count = state.selectedIds.size;
            button.disabled = state.creatingPo || count === 0 || !hasSinglePoBranch();
            button.title = !hasSinglePoBranch()
                ? 'Pilih satu cabang sebelum membuat purchase order.'
                : (count ? `${count} produk siap dilengkapi dalam form PO.` : 'Pilih minimal satu produk.');
            button.innerHTML = state.creatingPo
                ? '<i class="mdi mdi-loading mdi-spin"></i> Membuat PO...'
                : `<i class="mdi mdi-cart-check"></i> Buat PO <span id="iaCreatePoCount">(${number(count)})</span>`;
        }

        function summaryFootnote(item) {
            if (item.format === 'currency') return 'Nilai rupiah pada cakupan aktif';
            if (item.format === 'percent') return 'Proporsi dari hasil analisis';
            if (item.format === 'days') return 'Rata-rata umur persediaan';
            return `${number(state.meta?.period_days || 0)} hari periode analisis`;
        }

        function renderSummary(items) {
            element('iaSummaryGrid').innerHTML = items.map(item => `
                <article class="ia-summary-card is-${escapeHtml(item.tone)}">
                    <span><i class="mdi ${escapeHtml(item.icon)}"></i></span>
                    <div><small>${escapeHtml(item.label)}</small><strong>${escapeHtml(formatSummary(item))}</strong>
                        <span class="ia-summary-footnote"><i class="mdi mdi-chart-timeline-variant"></i>${escapeHtml(summaryFootnote(item))}</span>
                    </div>
                </article>`).join('');
        }

        function renderDistribution(items) {
            const container = element('iaDistribution');
            element('iaDistributionTitle').textContent = config.distributionTitle;
            element('iaDistributionSubtitle').textContent = config.distributionSubtitle;
            const values = (items || []).map(item => Math.abs(Number(item.value || 0)));
            const maximum = Math.max(1, ...values);
            const total = values.reduce((sum, value) => sum + value, 0);
            const donut = element('iaDistributionDonut');
            element('iaDistributionTotal').textContent = config.distributionFormat(total);
            donut.setAttribute('aria-label', `Total distribusi ${config.distributionFormat(total)}`);
            if (!items?.length || items.every(item => Number(item.value || 0) === 0)) {
                donut.style.background = 'conic-gradient(#e9edf4 0 100%)';
                container.innerHTML = '<div class="ia-empty-state"><span><i class="mdi mdi-chart-bar"></i><br>Belum ada distribusi yang dapat ditampilkan.</span></div>';
                return;
            }
            let cursor = 0;
            const segments = items.map(item => {
                const start = cursor;
                cursor += Math.abs(Number(item.value || 0)) / total * 100;
                return `${toneColors[item.tone] || toneColors.blue} ${start}% ${cursor}%`;
            });
            donut.style.background = `conic-gradient(${segments.join(',')})`;
            container.innerHTML = items.map(item => {
                const width = Math.max(1.5, Math.abs(Number(item.value || 0)) / maximum * 100);
                const tone = escapeHtml(item.tone || 'blue');
                return `<div class="ia-distribution-row is-${tone}"><span class="ia-distribution-label" title="${escapeHtml(item.label)}">${escapeHtml(item.label)}</span><span class="ia-distribution-track"><span class="is-${tone}" style="width:${width}%"></span></span><strong class="ia-distribution-value">${escapeHtml(config.distributionFormat(item.value))}</strong></div>`;
            }).join('');
        }

        function renderPriority(rows) {
            const title = element('iaPriorityTitle');
            const copy = element('iaPriorityCopy');
            const button = element('iaViewPriority');
            state.priorityId = null;

            if (!rows.length) {
                title.textContent = 'Belum ada tindakan prioritas';
                copy.textContent = 'Tidak ada produk yang perlu disorot untuk parameter analisis saat ini.';
                button.disabled = true;
                return;
            }

            let row = rows[0];
            if (type === 'slow-moving') row = [...rows].sort((a, b) => Number(b.days_since_out || 0) - Number(a.days_since_out || 0))[0];
            if (type === 'dead-stock') row = [...rows].sort((a, b) => Number(b.stock_value || 0) - Number(a.stock_value || 0))[0];
            state.priorityId = row.id;
            const insights = {
                'pergerakan-stok': [`${row.name} paling aktif`, `Total arus ${decimal(Number(row.qty_in || 0) + Number(row.qty_out || 0))} ${row.unit}. Tinjau pola masuk-keluar dan turnover produk ini.`],
                'pareto-abc': [`${row.name} memimpin kontribusi`, `Produk kelas ${row.abc_class} ini menyumbang ${number(row.contribution)}% omzet bersih pada periode aktif.`],
                'stok-hampir-habis': [`${row.name} perlu diprioritaskan`, `Kekurangan ${decimal(row.shortage)} ${row.unit} dari stok minimum dengan status ${String(row.stock_status).toLowerCase()}.`],
                'slow-moving': [`${row.name} paling lama tertahan`, `Tidak bergerak selama ${number(row.days_since_out)} hari dengan nilai stok ${currency(row.stock_value)}.`],
                'dead-stock': [`${currency(row.stock_value)} modal tertahan`, `${row.name} menjadi dead stock bernilai terbesar dan perlu keputusan redistribusi atau tindak lanjut.`],
                'saran-pembelian': [`${row.name} menjadi prioritas pembelian`, `Disarankan membeli ${decimal(row.suggested_qty)} ${row.unit} dengan estimasi ${currency(row.estimated_purchase)}.`],
            }[type];
            title.textContent = insights[0];
            copy.textContent = insights[1];
            button.disabled = false;
        }

        function renderContext(meta) {
            const period = `${dateText(meta.date_start)} – ${dateText(meta.date_end)}`;
            element('iaHeroPeriod').textContent = `${number(meta.period_days)} hari · ${period}`;
            element('iaSummaryContext').textContent = `${meta.branch_label || 'Belum ada cabang'} · ${period} · diperbarui ${dateText(meta.generated_at)}`;

            const chips = [
                `<span class="ia-filter-chip"><i class="mdi mdi-map-marker-outline"></i>&nbsp;${escapeHtml(meta.branch_label || 'Belum ada cabang')}</span>`,
                `<span class="ia-filter-chip"><i class="mdi mdi-calendar-range"></i>&nbsp;${escapeHtml(period)}</span>`,
            ];
            if (['slow-moving', 'dead-stock'].includes(type)) {
                chips.push(`<span class="ia-filter-chip">Slow ${number(meta.slow_days)}H · Dead ${number(meta.dead_days)}H</span>`);
            }
            if (type === 'saran-pembelian') {
                chips.push(`<span class="ia-filter-chip">Target ${number(meta.cover_days)} hari</span>`);
                chips.push(`<span class="ia-filter-chip">Lead time ${number(meta.lead_days)} hari</span>`);
            }
            const search = element('iaSearch').value.trim();
            if (search) chips.push(`<span class="ia-filter-chip"><i class="mdi mdi-magnify"></i>&nbsp;“${escapeHtml(search)}”</span>`);
            element('iaActiveFilterSummary').innerHTML = chips.join('');
        }

        function sortedRows() {
            if (state.sortIndex === null) return [...state.rows];
            const column = config.columns[state.sortIndex];
            const direction = state.sortDirection === 'asc' ? 1 : -1;
            return [...state.rows].sort((first, second) => {
                const a = column.value(first);
                const b = column.value(second);
                const aNumber = Number(a);
                const bNumber = Number(b);
                if (a !== '' && b !== '' && Number.isFinite(aNumber) && Number.isFinite(bNumber)) {
                    return (aNumber - bNumber) * direction;
                }
                return String(a ?? '').localeCompare(String(b ?? ''), 'id', { numeric: true, sensitivity: 'base' }) * direction;
            });
        }

        function renderTable() {
            const columns = config.columns;
            const total = state.rows.length;
            const pages = Math.max(1, Math.ceil(total / state.pageSize));
            state.page = Math.min(state.page, pages);
            const start = (state.page - 1) * state.pageSize;
            const visibleRows = sortedRows().slice(start, start + state.pageSize);
            element('iaTableHead').innerHTML = columns.map((column, index) => {
                if (column.selection) {
                    return '<th class="ia-po-select-heading"><input type="checkbox" id="iaSelectPage" aria-label="Pilih semua produk pada halaman ini"></th>';
                }
                if (column.sortable === false) return `<th>${escapeHtml(column.label)}</th>`;
                const active = state.sortIndex === index;
                const icon = active ? (state.sortDirection === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down') : 'mdi-unfold-more-horizontal';
                const ariaSort = active ? (state.sortDirection === 'asc' ? 'ascending' : 'descending') : 'none';
                return `<th aria-sort="${ariaSort}"><button type="button" class="ia-sort-button ${active ? 'is-active' : ''}" data-sort-index="${index}" aria-label="Urutkan berdasarkan ${escapeHtml(column.label)}">${escapeHtml(column.label)} <i class="mdi ${icon}"></i></button></th>`;
            }).join('');

            const selectableRows = visibleRows.filter(row => row.can_create_po && hasSinglePoBranch());
            const selectedOnPage = selectableRows.filter(row => state.selectedIds.has(String(row.id))).length;
            const selectPage = element('iaSelectPage');
            if (selectPage) {
                selectPage.disabled = selectableRows.length === 0;
                selectPage.checked = selectableRows.length > 0 && selectedOnPage === selectableRows.length;
                selectPage.indeterminate = selectedOnPage > 0 && selectedOnPage < selectableRows.length;
            }

            if (!visibleRows.length) {
                element('iaTableBody').innerHTML = `<tr><td colspan="${columns.length}"><div class="ia-empty-state"><span><i class="mdi mdi-database-search-outline fs-3"></i><br>${escapeHtml(state.empty || 'Data tidak ditemukan.')}</span></div></td></tr>`;
            } else {
                element('iaTableBody').innerHTML = visibleRows.map(row => `<tr data-row-id="${escapeHtml(row.id)}" class="${String(row.id) === String(state.priorityId) ? 'is-priority-row' : ''}">${columns.map(column => `<td data-label="${escapeHtml(column.label)}">${column.render(row)}</td>`).join('')}</tr>`).join('');
            }

            const sortLabel = state.sortIndex === null ? '' : ` · diurutkan ${columns[state.sortIndex].label.toLowerCase()}`;
            element('iaResultInfo').textContent = `${number(total)} produk sesuai parameter aktif${sortLabel}`;
            element('iaPaginationInfo').textContent = total ? `Menampilkan ${number(start + 1)}–${number(Math.min(start + state.pageSize, total))} dari ${number(total)} data` : '0 data';
            element('iaPageLabel').textContent = `Halaman ${state.page} / ${pages}`;
            element('iaPreviousPage').disabled = state.page <= 1;
            element('iaNextPage').disabled = state.page >= pages;
            element('iaExportCsv').disabled = total === 0;
            element('iaClearSort').hidden = state.sortIndex === null;
            updateCreatePoAction();
        }

        function showError(message) {
            state.rows = [];
            state.empty = message;
            state.priorityId = null;
            renderTable();
            element('iaSummaryGrid').innerHTML = `<article class="ia-summary-card is-red" style="grid-column:1/-1"><span><i class="mdi mdi-alert-circle-outline"></i></span><div><small>Analisis gagal dimuat</small><strong class="ia-summary-error">${escapeHtml(message)}</strong></div></article>`;
            element('iaDistribution').innerHTML = `<div class="ia-empty-state">${escapeHtml(message)}</div>`;
            element('iaDistributionDonut').style.background = 'conic-gradient(#e9edf4 0 100%)';
            element('iaDistributionTotal').textContent = '—';
            renderPriority([]);
            notify(message, 'mdi-alert-circle-outline');
        }

        async function loadData(resetPage = true, announce = false) {
            state.controller?.abort();
            const controller = new AbortController();
            state.controller = controller;
            state.selectedIds.clear();
            updateCreatePoAction();
            loading(true);
            if (resetPage) state.page = 1;
            const params = new URLSearchParams(new FormData(element('iaFilterForm')));

            try {
                const response = await fetch(`${dataUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const validation = payload.errors ? Object.values(payload.errors).flat()[0] : null;
                    throw new Error(validation || payload.message || 'Data analisis tidak dapat dimuat.');
                }

                const analysis = payload.analysis || {};
                state.rows = analysis.rows || [];
                state.empty = analysis.empty;
                state.meta = analysis.meta || {};
                state.appliedParams = params.toString();
                populateBranches(state.meta);
                element('iaBranchLabel').textContent = state.meta.branch_label || 'Belum ada cabang';
                element('iaGeneratedAt').textContent = `Diperbarui ${dateTime(state.meta.generated_at).replace(/<[^>]+>/g, '')}`;
                element('iaMethodCopy').textContent = config.method(state.meta);
                renderContext(state.meta);
                renderSummary(analysis.summary || []);
                renderDistribution(analysis.distribution || []);
                renderPriority(state.rows);
                renderTable();
                document.querySelector('.ia-filter-card')?.classList.remove('is-dirty');
                if (announce) notify(`Analisis diperbarui · ${number(state.rows.length)} produk ditemukan.`);
            } catch (error) {
                if (error.name !== 'AbortError') showError(error.message);
            } finally {
                if (state.controller === controller) loading(false);
            }
        }

        const selectedPurchaseRows = () => state.rows.filter(row => state.selectedIds.has(String(row.id)));
        const purchaseLine = row => {
            const conversionFactor = Math.max(1, Number(row.purchase_conversion_factor || 1));
            const qty = Math.ceil(Number(row.suggested_qty || 0) / conversionFactor);
            const price = Math.round(Number(row.average_purchase_price || 0) * conversionFactor * 100) / 100;

            return { qty, price, gross: qty * price };
        };
        const tieredNet = (gross, discounts) => Math.round(discounts.reduce(
            (amount, discount) => amount * (1 - Math.min(100, Math.max(0, Number(discount || 0))) / 100),
            Math.max(0, Number(gross || 0)),
        ) * 100) / 100;

        function togglePurchaseOrderModal(action) {
            const modalElement = element('iaCreatePoModal');
            if (!modalElement) return;

            if (window.bootstrap?.Modal) {
                const instance = window.bootstrap.Modal.getOrCreateInstance
                    ? window.bootstrap.Modal.getOrCreateInstance(modalElement)
                    : new window.bootstrap.Modal(modalElement);
                instance[action]();
            } else if (window.jQuery) {
                window.jQuery(modalElement).modal(action);
            }
        }

        function purchaseOrderItems() {
            return [...element('iaPoItemsBody').querySelectorAll('[data-po-line]')].map(line => ({
                medicine_id: Number(line.dataset.medicineId),
                distributor_id: Number(line.querySelector('.ia-po-line-distributor').value),
                harga_estimasi: Number(line.querySelector('.ia-po-estimated-price').value),
                diskon_1: Number(line.querySelector('[data-discount-tier="1"]').value || 0),
                diskon_2: Number(line.querySelector('[data-discount-tier="2"]').value || 0),
                diskon_3: Number(line.querySelector('[data-discount-tier="3"]').value || 0),
            }));
        }

        function updatePurchaseOrderEstimate() {
            let grossTotal = 0;
            let netTotal = 0;
            const selectedDistributorIds = new Set();
            element('iaPoItemsBody').querySelectorAll('[data-po-line]').forEach(line => {
                const qty = Number(line.dataset.purchaseQty || 0);
                const price = Math.max(0, Number(line.querySelector('.ia-po-estimated-price').value || 0));
                const gross = Math.round(qty * price * 100) / 100;
                const discounts = [...line.querySelectorAll('.ia-po-discount')].map(input => Number(input.value || 0));
                const distributorId = line.querySelector('.ia-po-line-distributor').value;
                const net = tieredNet(gross, discounts);
                if (distributorId) selectedDistributorIds.add(distributorId);
                grossTotal += gross;
                netTotal += net;
                line.querySelector('[data-po-subtotal]').textContent = currency(net);
            });
            element('iaPoGrossTotal').textContent = currency(grossTotal);
            element('iaPoNetTotal').textContent = currency(netTotal);
            element('iaPoOrderCount').textContent = selectedDistributorIds.size
                ? `${number(selectedDistributorIds.size)} PO`
                : 'Pilih distributor';
        }

        function openPurchaseOrderModal() {
            if (type !== 'saran-pembelian' || !createPoUrl || state.selectedIds.size === 0) return;

            if (!hasSinglePoBranch()) {
                notify('Pilih satu cabang sebelum membuat purchase order.', 'mdi-alert-circle-outline');
                return;
            }

            const selectedRows = selectedPurchaseRows();
            const form = element('iaCreatePoForm');
            form.reset();
            form.classList.remove('was-validated');
            element('iaPoSelectedCount').textContent = `${number(selectedRows.length)} produk`;
            element('iaPoBranchName').textContent = state.meta?.branch_label || '-';
            element('iaPoItemsBody').innerHTML = selectedRows.map(row => {
                const line = purchaseLine(row);
                const distributorChoices = distributorOptions.map(distributor =>
                    `<option value="${escapeHtml(distributor.id)}">${escapeHtml(distributor.label)}</option>`
                ).join('');
                return `<tr data-po-line data-medicine-id="${escapeHtml(row.id)}" data-purchase-qty="${line.qty}">
                    <td><div class="ia-po-product"><strong title="${escapeHtml(row.name)}">${escapeHtml(row.name)}</strong><small>${escapeHtml(row.code)} &middot; saran ${decimal(row.suggested_qty)} ${escapeHtml(row.unit)}</small></div></td>
                    <td><select class="form-select form-select-sm ia-po-line-distributor" required aria-label="Distributor ${escapeHtml(row.name)}"><option value="">-- Pilih Distributor --</option>${distributorChoices}</select></td>
                    <td><b>${decimal(line.qty)}</b> ${escapeHtml(row.purchase_unit || row.unit)}</td>
                    <td><input type="number" class="form-control form-control-sm ia-po-estimated-price" min="0" max="9999999999999.99" step="0.01" value="${escapeHtml(line.price)}" required aria-label="Harga estimasi ${escapeHtml(row.name)}"></td>
                    ${[1, 2, 3].map(tier => `<td><input type="number" class="form-control form-control-sm ia-po-discount" data-discount-tier="${tier}" min="0" max="100" step="0.01" value="0" aria-label="Diskon ${tier} ${escapeHtml(row.name)}"></td>`).join('')}
                    <td><strong class="ia-po-line-total" data-po-subtotal>${currency(line.gross)}</strong></td>
                </tr>`;
            }).join('');
            updatePurchaseOrderEstimate();
            togglePurchaseOrderModal('show');
        }

        async function createPurchaseOrders(event) {
            event.preventDefault();
            if (type !== 'saran-pembelian' || !createPoUrl || state.selectedIds.size === 0) return;

            const form = element('iaCreatePoForm');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            state.creatingPo = true;
            updateCreatePoAction();
            element('iaSubmitPo').disabled = true;
            element('iaSubmitPo').innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Membuat PO...';
            const payload = Object.fromEntries(new FormData(element('iaFilterForm')).entries());
            payload.medicine_ids = [...state.selectedIds].map(Number);
            payload.items = purchaseOrderItems();

            try {
                const response = await fetch(createPoUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(payload),
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const validation = result.errors ? Object.values(result.errors).flat()[0] : null;
                    throw new Error(validation || result.message || 'Purchase order tidak dapat dibuat.');
                }

                state.selectedIds.clear();
                togglePurchaseOrderModal('hide');
                await loadData(true);
                const orderNumbers = (result.orders || []).map(order => order.no_po).join(', ');
                const successText = `${result.message}${orderNumbers ? ` Nomor PO: ${orderNumbers}.` : ''} `
                    + 'Tinjau satuan, jumlah, harga, dan penandaan OOT sebelum approval.';

                if (window.Swal) {
                    const success = await Swal.fire({
                        icon: 'success',
                        title: 'Purchase order berhasil dibuat',
                        text: successText,
                        showCancelButton: true,
                        confirmButtonText: 'Buka menu Pembelian',
                        cancelButtonText: 'Tetap di sini',
                    });
                    if (success.isConfirmed && result.redirect_url) window.location.href = result.redirect_url;
                } else {
                    notify(result.message, 'mdi-cart-check');
                }
            } catch (error) {
                if (window.Swal) {
                    await Swal.fire({ icon: 'error', title: 'PO gagal dibuat', text: error.message });
                } else {
                    notify(error.message, 'mdi-alert-circle-outline');
                }
            } finally {
                state.creatingPo = false;
                element('iaSubmitPo').disabled = false;
                element('iaSubmitPo').innerHTML = '<i class="mdi mdi-cart-check"></i> Buat PO';
                updateCreatePoAction();
            }
        }

        function exportCsv() {
            if (!state.rows.length) return;
            const separator = ';';
            const exportColumns = config.columns.filter(column => column.export !== false);
            const lines = [exportColumns.map(column => `"${String(column.label).replaceAll('"', '""')}"`).join(separator)];
            sortedRows().forEach(row => {
                lines.push(exportColumns.map(column => {
                    const value = column.value(row);
                    return `"${String(value ?? '').replaceAll('"', '""')}"`;
                }).join(separator));
            });
            const blob = new Blob([`\ufeff${lines.join('\r\n')}`], { type: 'text/csv;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `analisis-persediaan-${type}-${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
            notify(`File CSV berisi ${number(state.rows.length)} produk berhasil disiapkan.`, 'mdi-file-check-outline');
        }

        function syncPreset() {
            const start = new Date(`${element('iaDateStart').value}T00:00:00`);
            const end = new Date(`${element('iaDateEnd').value}T00:00:00`);
            const days = Math.round((end - start) / 86400000) + 1;
            document.querySelectorAll('.ia-period-presets button').forEach(button => {
                const range = button.dataset.range;
                const isMonthToDate = range === 'mtd' && start.getDate() === 1 && start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear();
                button.classList.toggle('is-active', isMonthToDate || Number(range) === days);
            });
        }

        function markDirty() {
            const current = new URLSearchParams(new FormData(element('iaFilterForm'))).toString();
            const dirty = Boolean(state.appliedParams && current !== state.appliedParams);
            document.querySelector('.ia-filter-card')?.classList.toggle('is-dirty', dirty);
            if (dirty && state.selectedIds.size) {
                state.selectedIds.clear();
                renderTable();
            }
            element('iaClearSearch').hidden = element('iaSearch').value.length === 0;
            syncPreset();
        }

        element('iaFilterForm').addEventListener('submit', event => { event.preventDefault(); loadData(true, true); });
        element('iaFilterForm').addEventListener('input', markDirty);
        element('iaFilterForm').addEventListener('change', markDirty);
        element('iaResetFilter').addEventListener('click', () => {
            element('iaFilterForm').reset();
            element('iaClearSearch').hidden = true;
            syncPreset();
            loadData(true, true);
        });
        element('iaRefresh').addEventListener('click', () => loadData(false, true));
        element('iaExportCsv').addEventListener('click', exportCsv);
        element('iaPageSize').addEventListener('change', event => { state.pageSize = Number(event.target.value); state.page = 1; renderTable(); });
        element('iaPreviousPage').addEventListener('click', () => { if (state.page > 1) { state.page--; renderTable(); element('iaTable').scrollIntoView({ behavior: 'smooth', block: 'start' }); } });
        element('iaNextPage').addEventListener('click', () => { if (state.page * state.pageSize < state.rows.length) { state.page++; renderTable(); element('iaTable').scrollIntoView({ behavior: 'smooth', block: 'start' }); } });
        element('iaTableHead').addEventListener('click', event => {
            const button = event.target.closest('[data-sort-index]');
            if (!button) return;
            const index = Number(button.dataset.sortIndex);
            if (state.sortIndex === index) {
                state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                state.sortIndex = index;
                state.sortDirection = 'asc';
            }
            state.page = 1;
            renderTable();
        });
        element('iaClearSort').addEventListener('click', () => {
            state.sortIndex = null;
            state.sortDirection = 'asc';
            state.page = 1;
            renderTable();
        });
        element('iaTableBody').addEventListener('change', event => {
            const checkbox = event.target.closest('.ia-po-row-check');
            if (!checkbox) return;
            if (checkbox.checked) state.selectedIds.add(String(checkbox.value));
            else state.selectedIds.delete(String(checkbox.value));
            renderTable();
        });
        element('iaTableHead').addEventListener('change', event => {
            if (event.target.id !== 'iaSelectPage') return;
            const start = (state.page - 1) * state.pageSize;
            const visibleRows = sortedRows().slice(start, start + state.pageSize);
            visibleRows.filter(row => row.can_create_po && hasSinglePoBranch()).forEach(row => {
                if (event.target.checked) state.selectedIds.add(String(row.id));
                else state.selectedIds.delete(String(row.id));
            });
            renderTable();
        });
        if (type === 'saran-pembelian') {
            element('iaCreatePo').addEventListener('click', openPurchaseOrderModal);
            element('iaCreatePoForm').addEventListener('submit', createPurchaseOrders);
            element('iaPoItemsBody').addEventListener('input', event => {
                if (event.target.matches('.ia-po-estimated-price, .ia-po-discount')) updatePurchaseOrderEstimate();
            });
            element('iaPoItemsBody').addEventListener('change', event => {
                if (event.target.matches('.ia-po-line-distributor')) updatePurchaseOrderEstimate();
            });
        }
        element('iaFilterToggle').addEventListener('click', event => {
            const card = event.currentTarget.closest('.ia-filter-card');
            const collapsed = card.classList.toggle('is-collapsed');
            event.currentTarget.setAttribute('aria-expanded', String(!collapsed));
            event.currentTarget.querySelector('i').className = `mdi ${collapsed ? 'mdi-chevron-down' : 'mdi-chevron-up'}`;
            event.currentTarget.querySelector('span').textContent = collapsed ? 'Tampilkan' : 'Sembunyikan';
        });
        document.querySelectorAll('.ia-period-presets button').forEach(button => {
            button.addEventListener('click', () => {
                const end = new Date();
                const start = new Date(end);
                if (button.dataset.range === 'mtd') start.setDate(1);
                else start.setDate(end.getDate() - Number(button.dataset.range) + 1);
                element('iaDateStart').value = toIsoDate(start);
                element('iaDateEnd').value = toIsoDate(end);
                syncPreset();
                loadData(true, true);
            });
        });
        element('iaClearSearch').addEventListener('click', () => {
            element('iaSearch').value = '';
            element('iaClearSearch').hidden = true;
            loadData(true, true);
        });
        element('iaViewPriority').addEventListener('click', () => {
            if (state.priorityId === null) return;
            const priorityIndex = sortedRows().findIndex(row => String(row.id) === String(state.priorityId));
            if (priorityIndex >= 0) {
                state.page = Math.floor(priorityIndex / state.pageSize) + 1;
                renderTable();
            }
            element('iaTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        loadData(true);
    });
</script>
