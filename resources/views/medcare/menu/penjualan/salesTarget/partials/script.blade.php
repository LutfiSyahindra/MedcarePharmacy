<script>
(() => {
    'use strict';

    const app = document.getElementById('salesTargetApp');
    if (!app) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const modalElement = document.getElementById('salesTargetModal');
    const modal = window.bootstrap ? new bootstrap.Modal(modalElement) : null;
    let dashboard = null;
    let activeTargetId = null;

    const $ = selector => document.querySelector(selector);
    const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
    const number = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
    const percent = value => `${number(value)}%`;
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;' }[char]));
    const firstError = payload => Object.values(payload?.errors || {})[0]?.[0] || payload?.message;

    function query() {
        const params = new URLSearchParams({ year: $('#stYearFilter').value });
        if ($('#stBranchFilter').value) params.set('branch_id', $('#stBranchFilter').value);
        return params;
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, ...(options.headers || {}) },
            ...options,
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(firstError(payload) || 'Permintaan belum dapat diproses.');
        return payload;
    }

    async function load() {
        app.classList.add('st-loading');
        try {
            const payload = await request(`${app.dataset.url}?${query()}`);
            dashboard = payload.dashboard;
            render(dashboard);
        } catch (error) {
            $('#stMonthlyBody').innerHTML = `<tr><td colspan="8"><div class="st-error"><i class="mdi mdi-alert-circle-outline me-1"></i>${escapeHtml(error.message)}</div></td></tr>`;
            notify('error', 'Data gagal dimuat', error.message);
        } finally {
            app.classList.remove('st-loading');
        }
    }

    function render(data) {
        const meta = data.meta || {};
        const summary = data.summary || {};
        $('#stBranchLabel').textContent = meta.branch_label || '-';
        $('#stYearLabel').textContent = meta.year || '-';
        $('#stUpdatedAt').textContent = String(meta.generated_at || '-').slice(11, 16);
        $('#stTargetTotal').textContent = money(summary.target);
        $('#stRealizationTotal').textContent = money(summary.realization);
        $('#stAchievementTotal').textContent = percent(summary.achievement_percent);
        $('#stRemainingTotal').textContent = money(summary.remaining);
        $('#stTargetCoverage').textContent = `${number(summary.configured_configurations)} dari ${number(summary.expected_configurations)} target tersimpan`;
        $('#stAchievedMonths').textContent = `${number(summary.achieved_months)} bulan mencapai target`;
        $('#stConfiguredMonths').textContent = `${number(summary.configured_months)} dari 12 bulan diatur`;
        $('#stProgressPercent').textContent = percent(summary.achievement_percent);
        $('#stProgressGoal').textContent = `Target ${money(summary.target)}`;
        $('#stProgressBar').style.width = `${Math.min(100, Math.max(0, Number(summary.achievement_percent || 0)))}%`;
        renderCurrent(data.rows || [], meta.current_period);
        renderMonths(data.rows || [], meta.branch_id);
        renderBranches(data.branches || []);
    }

    function renderCurrent(rows, currentPeriod) {
        const row = rows.find(item => item.period === currentPeriod) || rows.find(item => item.period === `${$('#stYearFilter').value}-01`) || rows[0];
        if (!row) return;
        $('#stCurrentMonth').textContent = row.label;
        $('#stCurrentTarget').textContent = money(row.target);
        $('#stCurrentRealization').textContent = money(row.realization);
        $('#stCurrentRemaining').textContent = money(row.remaining);
        $('#stCurrentMessage').textContent = row.target > 0
            ? `Realisasi mencapai ${percent(row.achievement_percent)} dari target.`
            : 'Target pada bulan ini belum diatur.';
        const status = $('#stCurrentStatus');
        status.textContent = row.status;
        status.className = `st-status is-${row.status_key}`;
    }

    function renderMonths(rows, selectedBranchId) {
        $('#stMonthlyBody').innerHTML = rows.length ? rows.map(row => {
            const varianceClass = Number(row.variance) >= 0 ? 'is-positive' : 'is-negative';
            const config = row.expected_branches > 1 ? `${row.configured_branches}/${row.expected_branches} cabang` : (row.configured_branches ? 'Target tersimpan' : 'Belum diatur');
            const action = selectedBranchId ? `<button type="button" class="st-action-button" data-edit-period="${row.period}" title="Atur target ${escapeHtml(row.label)}"><i class="mdi mdi-pencil-outline"></i></button>` : '';
            return `<tr class="${row.period === dashboard.meta.current_period ? 'is-current' : ''}">
                <td><div class="st-period"><span class="st-period-icon"><i class="mdi mdi-calendar-month-outline"></i></span><div><strong>${escapeHtml(row.label)}</strong><small>${number(row.transactions)} transaksi selesai</small></div></div></td>
                <td class="text-end"><span class="st-money">${money(row.target)}</span><small class="st-config">${escapeHtml(config)}</small></td>
                <td class="text-end"><span class="st-money">${money(row.realization)}</span><small class="st-config">Kotor ${money(row.gross_sales)}</small></td>
                <td class="text-end"><span class="st-money is-negative">${money(row.returns)}</span></td>
                <td><div class="st-row-progress"><div><span style="width:${Math.min(100, Math.max(0, Number(row.achievement_percent || 0)))}%"></span></div><strong>${percent(row.achievement_percent)}</strong></div></td>
                <td class="text-end"><span class="st-money ${varianceClass}">${Number(row.variance) >= 0 ? '+' : ''}${money(row.variance)}</span></td>
                <td><span class="st-status is-${escapeHtml(row.status_key)}">${escapeHtml(row.status)}</span></td>
                <td class="text-end">${action}</td>
            </tr>`;
        }).join('') : '<tr><td colspan="8" class="st-empty">Belum ada data untuk ditampilkan.</td></tr>';

        document.querySelectorAll('[data-edit-period]').forEach(button => button.addEventListener('click', () => {
            const row = rows.find(item => item.period === button.dataset.editPeriod);
            openModal({ branchId: selectedBranchId, period: row.period, amount: row.target, targetId: row.target_id });
        }));
    }

    function renderBranches(rows) {
        $('#stBranchPanel').hidden = rows.length <= 1;
        $('#stBranchBody').innerHTML = rows.map(row => `<tr>
            <td><div class="st-branch-name"><strong>${escapeHtml(row.name)}</strong><small>${escapeHtml(row.code || 'Tanpa kode')}</small></div></td>
            <td class="text-end"><span class="st-money">${money(row.target)}</span></td>
            <td class="text-end"><span class="st-money">${money(row.realization)}</span></td>
            <td><div class="st-row-progress"><div><span style="width:${Math.min(100, Math.max(0, Number(row.achievement_percent || 0)))}%"></span></div><strong>${percent(row.achievement_percent)}</strong></div></td>
            <td class="text-center">${number(row.configured_months)} / 12</td><td class="text-center">${number(row.achieved_months)}</td>
        </tr>`).join('');
    }

    function openModal({ branchId = '', period = '', amount = '', targetId = null } = {}) {
        activeTargetId = targetId ? Number(targetId) : null;
        $('#stTargetBranch').value = String(branchId || $('#stBranchFilter').value || app.dataset.defaultBranch || '');
        $('#stTargetPeriod').value = period || `${$('#stYearFilter').value}-${String(new Date().getMonth() + 1).padStart(2, '0')}`;
        $('#stTargetAmount').value = Number(amount || 0) || '';
        $('#stTargetBranch').disabled = Boolean(activeTargetId);
        $('#stTargetPeriod').disabled = Boolean(activeTargetId);
        $('#stDeleteTarget').hidden = !activeTargetId;
        $('#salesTargetModalLabel').textContent = activeTargetId ? 'Ubah Target Penjualan' : 'Atur Target Penjualan';
        modal?.show();
    }

    async function save(event) {
        event.preventDefault();
        const button = event.currentTarget.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            const payload = await request(app.dataset.storeUrl, {
                method: 'POST',
                body: JSON.stringify({
                    branch_id: $('#stTargetBranch').value,
                    period: $('#stTargetPeriod').value,
                    target_amount: $('#stTargetAmount').value,
                }),
            });
            modal?.hide();
            notify('success', 'Target tersimpan', payload.message);
            await load();
        } catch (error) {
            notify('error', 'Target gagal disimpan', error.message);
        } finally {
            button.disabled = false;
        }
    }

    async function remove() {
        if (!activeTargetId) return;
        const confirmation = window.Swal ? await Swal.fire({
            title: 'Hapus target bulanan?',
            text: 'Realisasi transaksi tidak ikut terhapus.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#d95252', reverseButtons: true,
        }) : { isConfirmed: window.confirm('Hapus target bulanan ini?') };
        if (!confirmation.isConfirmed) return;
        const button = $('#stDeleteTarget');
        button.disabled = true;
        try {
            const url = app.dataset.destroyUrl.replace('__TARGET__', String(activeTargetId));
            const payload = await request(url, { method: 'DELETE' });
            modal?.hide();
            notify('success', 'Target dihapus', payload.message);
            await load();
        } catch (error) {
            notify('error', 'Target gagal dihapus', error.message);
        } finally {
            button.disabled = false;
        }
    }

    function notify(icon, title, text) {
        if (window.Swal) {
            Swal.fire({ icon, title, text, timer: icon === 'success' ? 1800 : undefined, showConfirmButton: icon !== 'success' });
        }
    }

    $('#stYearFilter').addEventListener('change', load);
    $('#stBranchFilter').addEventListener('change', load);
    $('#stRefreshButton').addEventListener('click', load);
    $('#stOpenTargetButton').addEventListener('click', () => openModal());
    $('#salesTargetForm').addEventListener('submit', save);
    $('#stDeleteTarget').addEventListener('click', remove);
    load();
})();
</script>
