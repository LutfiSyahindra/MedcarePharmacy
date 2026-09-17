<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('financeApp');
    if (!app) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const el = id => document.getElementById(id);
    const money = value => `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value || 0))}`;
    const number = value => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const localDateTime = () => { const date = new Date(); date.setMinutes(date.getMinutes() - date.getTimezoneOffset()); return date.toISOString().slice(0, 16); };
    const firstError = payload => { const first = Object.values(payload?.errors || {})[0]; return Array.isArray(first) ? first[0] : (first || payload?.message || 'Permintaan belum dapat diproses.'); };
    const state = { rows: new Map(), branchesLoaded: false, controller: null, deepLinkOpened: false };

    function setText(id, value) { const target = el(id); if (target) target.textContent = value; }

    function loadBranches(meta) {
        if (state.branchesLoaded) return;
        const select = el('fnObligationBranch');
        (meta.branches || []).forEach(branch => select.add(new Option(`${branch.name}${branch.code ? ` · ${branch.code}` : ''}`, branch.id)));
        state.branchesLoaded = true;
    }

    function dueBadge(row) {
        const tone = row.due_state === 'overdue' ? 'is-overdue' : (row.due_state === 'due_soon' ? 'is-soon' : 'is-open');
        return `<span class="fn-due-badge ${tone}"><i class="mdi ${row.due_state === 'overdue' ? 'mdi-alert-circle-outline' : 'mdi-clock-outline'}"></i>${escapeHtml(row.due_label)}</span>`;
    }

    function rowMarkup(row) {
        state.rows.set(`${row.kind}-${row.id}`, row);
        const isPayable = row.kind === 'payable';
        return `<tr>
            <td><span class="fn-reference"><b>${escapeHtml(row.reference)}</b><small>${escapeHtml(row.secondary_reference || '')}</small></span></td>
            <td><strong>${escapeHtml(row.counterparty)}</strong></td>
            <td>${escapeHtml(row.branch_name)}</td>
            <td><span class="fn-reference"><b>${escapeHtml(row.date_label)}</b><small>${isPayable ? `Tempo ${escapeHtml(row.due_date_label || '-')}` : escapeHtml(row.due_label)}</small></span></td>
            <td class="text-end fn-amount">${money(row.total)}${Number(row.adjustment) > 0 ? `<small class="fn-adjustment">Potongan ${money(row.adjustment)}</small>` : ''}</td>
            <td class="text-end fn-amount is-income">${money(row.paid)}</td>
            <td class="text-end fn-amount is-expense">${money(row.remaining)}</td>
            <td>${isPayable ? dueBadge(row) : `<span class="fn-due-badge is-open"><i class="mdi mdi-timer-sand"></i>${escapeHtml(row.due_label)}</span>`}</td>
            <td><button type="button" class="fn-settle-button" data-settle-kind="${row.kind}" data-settle-id="${row.id}"><i class="mdi ${isPayable ? 'mdi-bank-transfer-out' : 'mdi-bank-transfer-in'}"></i>${isPayable ? 'Bayar' : 'Terima'}</button></td>
        </tr>`;
    }

    function render(data) {
        state.rows.clear();
        loadBranches(data.meta || {});
        const summary = data.summary || {};
        setText('fnPayableTotal', money(summary.payable));
        setText('fnReceivableTotal', money(summary.receivable));
        setText('fnObligationNet', money(Number(summary.receivable || 0) - Number(summary.payable || 0)));
        setText('fnOverdueTotal', money(summary.overdue_payable));
        setText('fnPayableCount', `${number(summary.payable_count)} faktur aktif`);
        setText('fnReceivableCount', `${number(summary.receivable_count)} transaksi aktif`);
        setText('fnOverdueCount', `${number(summary.overdue_count)} faktur`);
        setText('fnPayableRowsCount', `${number((data.payables || []).length)} faktur`);
        setText('fnReceivableRowsCount', `${number((data.receivables || []).length)} transaksi`);
        setText('fnHeroLabel', 'TOTAL HUTANG SUPPLIER');
        setText('fnHeroValue', money(summary.payable));
        setText('fnHeroPeriod', `Piutang aktif ${money(summary.receivable)}`);
        setText('fnHeroBranch', data.meta?.branch_label || 'Semua cabang');

        el('fnPayableRows').innerHTML = (data.payables || []).length
            ? data.payables.map(rowMarkup).join('')
            : '<tr><td colspan="9" class="fn-empty">Tidak ada hutang supplier yang cocok dengan filter.</td></tr>';
        el('fnReceivableRows').innerHTML = (data.receivables || []).length
            ? data.receivables.map(rowMarkup).join('')
            : '<tr><td colspan="9" class="fn-empty">Tidak ada piutang pelanggan yang cocok dengan filter.</td></tr>';

        if (!state.deepLinkOpened) {
            const params = new URLSearchParams(window.location.search);
            const kind = params.has('payable') ? 'payable' : (params.has('receivable') ? 'receivable' : '');
            const id = params.get(kind);
            if (kind && id && state.rows.has(`${kind}-${id}`)) openSettlement(state.rows.get(`${kind}-${id}`));
            state.deepLinkOpened = true;
        }
    }

    async function load() {
        state.controller?.abort();
        state.controller = new AbortController();
        el('fnObligationLoading').hidden = false;
        el('fnObligationError').hidden = true;
        el('fnObligationApply').disabled = true;
        const params = new URLSearchParams(new FormData(el('fnObligationFilter')));
        [...params.entries()].forEach(([key, value]) => { if (!value) params.delete(key); });
        try {
            const response = await fetch(`${app.dataset.obligationsUrl}?${params}`, { headers:{Accept:'application/json'}, signal:state.controller.signal });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(firstError(payload));
            render(payload.obligations);
        } catch (error) {
            if (error.name === 'AbortError') return;
            setText('fnObligationErrorCopy', error.message || 'Saldo gagal dimuat.');
            el('fnObligationError').hidden = false;
        } finally {
            el('fnObligationLoading').hidden = true;
            el('fnObligationApply').disabled = false;
        }
    }

    function syncCashNotice() {
        const cash = el('fnSettlementMethod').value === 'tunai';
        el('fnSettlementCashNotice').hidden = !cash;
        el('fnSettlementDate').readOnly = cash;
        if (cash) el('fnSettlementDate').value = localDateTime();
    }

    function openSettlement(row) {
        const payable = row.kind === 'payable';
        el('fnSettlementForm').reset();
        el('fnSettlementKind').value = row.kind;
        el('fnSettlementId').value = row.id;
        el('fnSettlementMethod').value = 'transfer';
        el('fnSettlementDate').value = localDateTime();
        el('fnSettlementAmount').value = Number(row.remaining).toFixed(2);
        el('fnSettlementAmount').max = Number(row.remaining).toFixed(2);
        setText('fnSettlementKicker', payable ? 'PEMBAYARAN HUTANG SUPPLIER' : 'PENERIMAAN PIUTANG');
        setText('fnSettlementTitle', payable ? `Bayar ${row.reference}` : `Terima ${row.reference}`);
        setText('fnSettlementSubtitle', payable ? 'Pengeluaran dan saldo faktur akan diperbarui bersamaan.' : 'Kas masuk dan saldo transaksi penjualan akan diperbarui bersamaan.');
        setText('fnSettlementReference', row.reference);
        setText('fnSettlementCounterparty', row.counterparty);
        setText('fnSettlementRemaining', money(row.remaining));
        setText('fnSettlementLimit', `Maksimal ${money(row.remaining)}`);
        syncCashNotice();
        bootstrap.Modal.getOrCreateInstance(el('fnSettlementModal')).show();
    }

    document.querySelectorAll('#fnPayableRows, #fnReceivableRows').forEach(target => target.addEventListener('click', event => {
        const button = event.target.closest('[data-settle-kind]');
        if (!button) return;
        const row = state.rows.get(`${button.dataset.settleKind}-${button.dataset.settleId}`);
        if (row) openSettlement(row);
    }));

    el('fnObligationFilter').addEventListener('submit', event => { event.preventDefault(); load(); });
    el('fnObligationReset').addEventListener('click', () => { el('fnObligationFilter').reset(); load(); });
    el('fnObligationRetry').addEventListener('click', load);
    el('fnSettlementMethod').addEventListener('change', syncCashNotice);
    el('fnSettlementForm').addEventListener('submit', async event => {
        event.preventDefault();
        const kind = el('fnSettlementKind').value;
        const id = el('fnSettlementId').value;
        const base = kind === 'payable' ? app.dataset.payableBase : app.dataset.receivableBase;
        const button = el('fnSaveSettlement');
        button.disabled = true;
        button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...';
        try {
            const response = await fetch(`${base}/${id}/payments`, { method:'POST', headers:{Accept:'application/json','X-CSRF-TOKEN':csrf}, body:new FormData(event.currentTarget) });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(firstError(payload));
            bootstrap.Modal.getInstance(el('fnSettlementModal'))?.hide();
            await Swal.fire({icon:'success',title:'Pembayaran tercatat',text:`${payload.message} Jurnal ${payload.transaction_number}.`,timer:2200,showConfirmButton:false});
            load();
        } catch (error) {
            Swal.fire('Pembayaran gagal', error.message, 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="mdi mdi-content-save-check-outline"></i> Simpan pembayaran';
        }
    });

    load();
});
</script>
