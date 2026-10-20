import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import '../../public/assets/js/purchase-order-entry.js';

const { requestWithCsrfRecovery, recoveryStore, newRequestKey } = globalThis.PurchaseOrderEntry;

test('a stale CSRF token refreshes the session and retries the same save exactly once', async () => {
    let calls = 0, refreshes = 0, token = 'old';
    const keys = [];
    const result = await requestWithCsrfRecovery(async () => {
        calls++;
        keys.push('same-request-key');
        if (token === 'old') throw { status: 419 };
        return { id: 5 };
    }, async () => { refreshes++; return { token: 'new', user_id: 1 }; }, session => { token = session.token; });
    assert.deepEqual(result, { id: 5 });
    assert.equal(calls, 2);
    assert.equal(refreshes, 1);
    assert.deepEqual(keys, ['same-request-key', 'same-request-key']);
});

test('repeated 419, expired login, and changed accounts stop without an unbounded retry', async () => {
    let calls = 0;
    await assert.rejects(requestWithCsrfRecovery(async () => { calls++; throw { status: 419 }; },
        async () => ({ token: 'new' }), () => {}), error => error.status === 419);
    assert.equal(calls, 2);
    for (const failure of [401, 403]) {
        calls = 0;
        await assert.rejects(requestWithCsrfRecovery(async () => { calls++; throw { status: 419 }; },
            async () => { if (failure === 401) throw { status: 401 }; return { user_id: 2 }; },
            () => { throw { status: 403 }; }), error => error.status === failure);
        assert.equal(calls, 1);
    }
});

test('network, timeout, server and validation failures never automatically repeat a write', async () => {
    for (const status of [0, 401, 403, 409, 422, 500, 502, 504]) {
        let calls = 0, refreshes = 0;
        const failure = { status };
        await assert.rejects(requestWithCsrfRecovery(async () => { calls++; throw failure; },
            async () => { refreshes++; }, () => {}), error => error === failure);
        assert.equal(calls, 1);
        assert.equal(refreshes, 0);
    }
});

test('reload restores 600 incomplete rows and an unresolved save without mixing user/branch keys', () => {
    const data = new Map();
    const storage = { getItem: key => data.get(key) ?? null, setItem: (key, value) => data.set(key, value), removeItem: key => data.delete(key) };
    const key = 'user:1:branch:1';
    const entry = { form: { catatan: 'Belum selesai', tanggal: '', pembelian_id: '' },
        rows: Array.from({ length: 600 }, (_, index) => ({ fields: { obat_id: String(index), qty: '', harga_estimasi_satuan: '' }, priceInput: 'Rp salah' })),
        pending: { key: newRequestKey(), payload: { qty: ['3'] } } };
    assert.equal(recoveryStore(() => storage, key).write(entry), true);
    const restored = recoveryStore(() => storage, key).read();
    assert.equal(restored.rows.length, 600);
    assert.deepEqual(restored.rows[599], entry.rows[599]);
    assert.deepEqual(restored.pending, entry.pending);
    assert.equal(recoveryStore(() => storage, 'user:2:branch:1').read(), null);
    assert.equal(recoveryStore(() => storage, 'user:1:branch:2').read(), null);
    recoveryStore(() => storage, key).clear();
    assert.equal(recoveryStore(() => storage, key).read(), null);
});

test('blocked storage, exhausted quota and corrupt backups do not crash the form', () => {
    const blocked = recoveryStore(() => { throw new Error('denied'); }, 'key');
    assert.equal(blocked.read(), null);
    assert.equal(blocked.write({}), false);
    assert.doesNotThrow(() => blocked.clear());
    const quota = recoveryStore(() => ({ setItem() { throw new Error('quota'); } }), 'key');
    assert.equal(quota.write({ form: {}, rows: [] }), false);
    for (const value of ['broken json', 'null', '{"version":2,"form":{},"rows":[]}']) {
        assert.equal(recoveryStore(() => ({ getItem: () => value }), 'key').read(), null);
    }
});

test('save request keys are distinct UUIDs suitable for HTTP intranet deployments', () => {
    const keys = Array.from({ length: 100 }, newRequestKey);
    assert.equal(new Set(keys).size, 100);
    keys.forEach(key => assert.match(key, /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/));
});

function submissionHarness(server) {
    const script = fs.readFileSync(new URL('../../resources/views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php', import.meta.url), 'utf8');
    const workflow = fs.readFileSync(new URL('../../resources/views/medcare/menu/pembelianPenerimaan/pembelian/partials/entryWorkflow.blade.php', import.meta.url), 'utf8');
    const submit = script.slice(script.indexOf('        function submitPurchaseOrder('), script.indexOf('        // --- Submit PO ke alur approval'))
        .replace(/{{.*?}}/g, '/purchase-order/:id');
    const signatures = workflow.slice(workflow.indexOf('        function purchaseEntrySignature('), workflow.indexOf('        function clearPurchaseFilters('));
    let complete;
    const state = {
        purchasePendingSave: null, purchaseSaveInProgress: false, purchaseConversionSaves: new Set(), purchaseConversionContext: null,
        purchaseDirty: true, purchaseSubmittedSignature: '', purchaseAllowClose: false, editMode: false, closed: false, backup: null,
        fields: [{ name: '_token', value: 'stale' }, { name: 'no_po', value: 'PREVIEW' }, { name: 'qty[]', value: '2' }],
        id: '', number: 'PREVIEW', errors: [], PurchaseOrderEntry: globalThis.PurchaseOrderEntry,
        validatePurchaseEntry: () => true, selectedDistributorUsesManualPoNumber: () => false,
        ensurePurchaseOrderNumber: () => Promise.resolve(), waitForPendingUnitRequests: () => Promise.resolve(),
        updatePurchaseOrderNumberMode() {}, recalculatePurchaseRow() {}, hitungTotal() {}, confirmPurchaseSubmission() {},
        Swal: { fire() {} }, PembelianTable: { ajax: { reload() {} } },
        updateUnitLoadingState() { if (!state.purchaseSaveInProgress) complete(); },
        persistPurchaseRecovery() { state.backup = structuredClone({ fields: state.fields, pending: state.purchasePendingSave }); },
        clearPurchaseRecovery() { state.backup = null; },
        sendPendingPurchaseSave() { return server(state.purchasePendingSave); },
        showPurchaseSaveError(error) { state.errors.push(error); },
        $(selector) {
            return {
                prop() { return this; }, text() { return this; }, each() { return this; }, toArray() { return []; },
                serializeArray() { return structuredClone(state.fields); },
                modal() { state.closed = true; },
                val(value) {
                    const property = selector === '#pembelian_id' ? 'id' : 'number';
                    if (arguments.length === 0) return state[property];
                    state[property] = value;
                    return this;
                }
            };
        }
    };
    const run = new Function('environment', `with (environment) { ${signatures} ${submit}; return submitPurchaseOrder; }`)(state);
    return { state, submit: () => new Promise(resolve => { complete = resolve; run(false, true); }) };
}

test('the form reuses a pending request after a lost response and clears its backup only after success', async () => {
    const requests = new Map();
    let calls = 0;
    const form = submissionHarness(async attempt => {
        calls++;
        requests.set(attempt.key, structuredClone(attempt));
        if (calls === 1) throw { status: 0 };
        return { status: 'success', data: { id: 11, no_po: 'PO-11' } };
    });
    await form.submit();
    const key = form.state.purchasePendingSave.key;
    assert.equal(form.state.closed, false);
    assert.equal(form.state.backup.pending.key, key);
    assert.equal('_token' in form.state.purchasePendingSave.payload, false);
    await form.submit();
    assert.equal(requests.size, 1);
    assert.equal(form.state.id, 11);
    assert.equal(form.state.closed, true);
    assert.equal(form.state.backup, null);
    assert.equal(form.state.purchaseSaveInProgress, false);
});

test('edits after a lost response stay in the form while the original request is reconciled', async () => {
    const attempts = [];
    const form = submissionHarness(async attempt => {
        attempts.push(structuredClone(attempt));
        if (attempts.length === 1) throw { status: 500 };
        return { status: 'success', data: { id: 11, no_po: 'PO-11' } };
    });
    await form.submit();
    form.state.fields.find(field => field.name === 'qty[]').value = '3';
    await form.submit();
    assert.equal(attempts[0].key, attempts[1].key);
    assert.deepEqual(attempts[1].payload.qty, ['2']);
    assert.equal(form.state.closed, false);
    assert.equal(form.state.purchaseDirty, true);
    assert.equal(form.state.backup.pending, null);
    assert.equal(form.state.backup.fields.find(field => field.name === 'qty[]').value, '3');
    await form.submit();
    assert.notEqual(attempts[2].key, attempts[1].key);
    assert.equal(attempts[2].method, 'PUT');
    assert.deepEqual(attempts[2].payload.qty, ['3']);
    assert.equal(form.state.closed, true);
});

test('validation errors release the pending request while preserving entered values for correction', async () => {
    const attempts = [];
    const form = submissionHarness(async attempt => {
        attempts.push(structuredClone(attempt));
        if (attempts.length === 1) throw { status: 422 };
        return { status: 'success', data: { id: 11, no_po: 'PO-11' } };
    });
    await form.submit();
    assert.equal(form.state.purchasePendingSave, null);
    assert.equal(form.state.closed, false);
    assert.deepEqual(form.state.backup.fields, form.state.fields);
    await form.submit();
    assert.notEqual(attempts[0].key, attempts[1].key);
    assert.equal(form.state.closed, true);
});
