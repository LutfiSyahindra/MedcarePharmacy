import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import '../../public/assets/js/purchase-order-entry.js';

const { requestWithCsrfRecovery, withTimeout, recoveryStore, newRequestKey } = globalThis.PurchaseOrderEntry;

test('a stalled task times out even when transport cancellation throws', async () => {
    let aborts = 0;
    await assert.rejects(withTimeout(() => new Promise(() => {}), 5, () => {
        aborts++;
        throw new Error('Transport did not abort');
    }), error => error.status === 0 && error.statusText === 'timeout');
    assert.equal(aborts, 1);
});

test('successful tasks and synchronous or asynchronous errors clear their deadline', async () => {
    let aborts = 0;
    const abort = () => { aborts++; };
    assert.deepEqual(await withTimeout(() => ({ id: 5 }), 5, abort), { id: 5 });
    const failure = new Error('Preparation failed');
    await assert.rejects(withTimeout(() => { throw failure; }, 5, abort), error => error === failure);
    await assert.rejects(withTimeout(() => Promise.reject(failure), 5, abort), error => error === failure);
    await new Promise(resolve => setTimeout(resolve, 15));
    assert.equal(aborts, 0);
});

function ajaxHarness(transport) {
    const source = fs.readFileSync(new URL('../../resources/views/medcare/menu/pembelianPenerimaan/pembelian/partials/entryRecovery.blade.php', import.meta.url), 'utf8');
    const request = source.slice(source.indexOf('        function purchaseAjax('),
        source.indexOf('        function sendPendingPurchaseSave('));
    return new Function('environment', `with (environment) { ${request}; return purchaseAjax; }`)({
        $: { ajax: transport }, PurchaseOrderEntry: globalThis.PurchaseOrderEntry
    });
}

test('a missing AJAX callback times out and aborts without sending a second request', async () => {
    let calls = 0, aborts = 0, success;
    const send = ajaxHarness(() => {
        calls++;
        return {
            done(callback) { success = callback; return this; }, fail() { return this; },
            abort() { aborts++; }
        };
    });
    await assert.rejects(send({ timeout: 5 }), error => error.statusText === 'timeout');
    success({ id: 5 });
    assert.equal(calls, 1);
    assert.equal(aborts, 1);
});

test('AJAX requests cancelled before handlers attach are rejected immediately', async () => {
    const failure = { status: 0, statusText: 'canceled' };
    const send = ajaxHarness(() => ({
        done() { return this; },
        fail(callback) { callback(failure, 'canceled'); return this; }
    }));
    await assert.rejects(send({ timeout: 1000 }), error => error === failure);
});

test('AJAX success, CSRF rejection and invalid JSON settle with the original result or error', async () => {
    for (const statusText of ['success', 'error', 'parsererror']) {
        const data = { id: 5 };
        const failure = { status: statusText === 'parsererror' ? 200 : 419, statusText: 'OK' };
        let aborts = 0;
        const send = ajaxHarness(() => ({
            done(callback) { if (statusText === 'success') callback(data); return this; },
            fail(callback) { if (statusText !== 'success') callback(failure, statusText); return this; },
            abort() { aborts++; }
        }));
        if (statusText === 'success') assert.deepEqual(await send({ timeout: 5 }), data);
        else await assert.rejects(send({ timeout: 5 }), error => statusText === 'parsererror'
            ? error.status === 200 && error.statusText === 'parsererror' : error === failure);
        assert.equal(aborts, 0);
    }
});

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
    const state = {
        purchasePendingSave: null, purchaseSaveInProgress: false, purchaseLoadingDetails: false, purchaseAddingItems: false,
        purchaseConversionSaves: new Set(), purchaseConversionContext: null,
        purchaseDirty: true, purchaseSubmittedSignature: '', purchaseAllowClose: false, editMode: false, closed: false, backup: null,
        fields: [{ name: '_token', value: 'stale' }, { name: 'no_po', value: 'PREVIEW' }, { name: 'qty[]', value: '2' }],
        id: '', number: 'PREVIEW', errors: [], inert: false, buttonsDisabled: false, status: '',
        PurchaseOrderEntry: globalThis.PurchaseOrderEntry,
        validatePurchaseEntry: () => true, selectedDistributorUsesManualPoNumber: () => false,
        ensurePurchaseOrderNumber: () => Promise.resolve(), waitForPendingUnitRequests: () => Promise.resolve(),
        updatePurchaseOrderNumberMode() {}, recalculatePurchaseRow() {}, hitungTotal() {}, confirmPurchaseSubmission() {},
        Swal: { fire() {} }, PembelianTable: { ajax: { reload() {} } },
        updateUnitLoadingState() {
            state.buttonsDisabled = state.purchaseSaveInProgress;
        },
        persistPurchaseRecovery() { state.backup = structuredClone({ fields: state.fields, pending: state.purchasePendingSave }); },
        clearPurchaseRecovery() { state.backup = null; },
        sendPendingPurchaseSave() { return server(state.purchasePendingSave); },
        showPurchaseSaveError(error) { state.errors.push(error); },
        $(selector) {
            return {
                prop(name, value) {
                    if (selector === '#pembelianForm' && name === 'inert') state.inert = value;
                    return this;
                },
                text(value) {
                    if (selector === '#purchaseDraftStatus') state.status = value;
                    return this;
                },
                each() { return this; }, toArray() { return []; },
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
    return { state, trySubmit: () => run(false, true), submit: (draft = false) => run(draft, true) };
}

test('synchronous preparation failures unlock both draft and approval saves before any write', async () => {
    for (const draft of [true, false]) {
        for (const step of ['ensurePurchaseOrderNumber', 'waitForPendingUnitRequests']) {
            let calls = 0;
            const failure = new TypeError('Browser preparation failed');
            const form = submissionHarness(async () => { calls++; });
            form.state[step] = () => { throw failure; };

            await assert.doesNotReject(form.submit(draft));
            assert.equal(calls, 0);
            assert.equal(form.state.closed, false);
            assert.equal(form.state.purchaseSaveInProgress, false);
            assert.equal(form.state.buttonsDisabled, false);
            assert.equal(form.state.inert, false);
            assert.equal(form.state.purchasePendingSave, null);
            assert.deepEqual(form.state.errors, [failure]);
            assert.match(form.state.status, /Gagal disimpan/);
            assert.deepEqual(form.state.backup.fields, form.state.fields);
        }
    }
});

test('a failure while preparing the locked form is handled and allows a later save', async () => {
    let calls = 0;
    const form = submissionHarness(async () => {
        calls++;
        return { status: 'success', data: { id: 11, no_po: 'PO-11' } };
    });
    const update = form.state.updateUnitLoadingState;
    form.state.updateUnitLoadingState = function() {
        update();
        if (form.state.purchaseSaveInProgress) throw new Error('Failed to update form controls');
    };

    await assert.doesNotReject(form.submit(true));
    assert.equal(calls, 0);
    assert.equal(form.state.purchaseSaveInProgress, false);
    assert.equal(form.state.buttonsDisabled, false);
    assert.equal(form.state.inert, false);
    assert.match(form.state.status, /Gagal disimpan/);

    form.state.updateUnitLoadingState = update;
    await form.submit(true);
    assert.equal(calls, 1);
    assert.equal(form.state.closed, true);
});

test('repeated clicks during preparation send only one save and release the controls after success', async () => {
    let calls = 0;
    let finishPreparation;
    const preparation = new Promise(resolve => { finishPreparation = resolve; });
    const form = submissionHarness(async () => {
        calls++;
        return { status: 'success', data: { id: 11, no_po: 'PO-11' } };
    });
    form.state.ensurePurchaseOrderNumber = () => preparation;

    const saving = form.submit(true);
    await form.submit(true);
    assert.equal(form.state.purchaseSaveInProgress, true);
    assert.equal(form.state.buttonsDisabled, true);
    assert.equal(form.state.inert, true);
    assert.equal(form.state.status, 'Menyimpan...');
    assert.equal(calls, 0);

    finishPreparation();
    await saving;
    assert.equal(calls, 1);
    assert.equal(form.state.closed, true);
    assert.equal(form.state.purchaseSaveInProgress, false);
    assert.equal(form.state.buttonsDisabled, false);
    assert.equal(form.state.inert, false);
});

test('stalled preparation unlocks draft and approval forms and never saves on a late completion', async () => {
    for (const draft of [true, false]) {
        let calls = 0, finishPreparation;
        const preparation = new Promise(resolve => { finishPreparation = resolve; });
        const form = submissionHarness(async () => {
            calls++;
            return { status: 'success', data: { id: 11, no_po: 'PO-11' } };
        });
        form.state.PurchaseOrderEntry = { ...globalThis.PurchaseOrderEntry,
            withTimeout: (task, milliseconds, abort) => withTimeout(task, 5, abort) };
        form.state.waitForPendingUnitRequests = () => preparation;

        await form.submit(draft);
        assert.equal(form.state.purchaseSaveInProgress, false);
        assert.equal(form.state.buttonsDisabled, false);
        assert.equal(form.state.inert, false);
        assert.equal(form.state.closed, false);
        assert.equal(form.state.purchasePendingSave, null);
        assert.deepEqual(form.state.backup.fields, form.state.fields);
        assert.equal(form.state.errors[0].statusText, 'timeout');

        finishPreparation();
        await new Promise(resolve => setImmediate(resolve));
        assert.equal(calls, 0);
        assert.equal(form.state.closed, false);

        form.state.waitForPendingUnitRequests = () => Promise.resolve();
        await form.submit(draft);
        assert.equal(calls, 1);
        assert.equal(form.state.closed, true);
    }
});

test('a timed-out save ignores its late response and an explicit retry reuses the same request key', async () => {
    const saved = new Map();
    const attempts = [];
    let finishOriginal;
    const response = { status: 'success', data: { id: 11, no_po: 'PO-11' } };
    const form = submissionHarness(async attempt => {
        attempts.push(attempt.key);
        if (!saved.has(attempt.key)) {
            saved.set(attempt.key, response);
            return new Promise(resolve => { finishOriginal = resolve; });
        }
        return saved.get(attempt.key);
    });
    const send = form.state.sendPendingPurchaseSave;
    form.state.sendPendingPurchaseSave = () => withTimeout(send, 5);

    await form.submit(true);
    const key = form.state.purchasePendingSave.key;
    assert.equal(form.state.backup.pending.key, key);
    assert.equal(form.state.purchaseSaveInProgress, false);
    assert.equal(form.state.buttonsDisabled, false);
    assert.equal(form.state.inert, false);
    finishOriginal(response);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(form.state.closed, false);
    assert.equal(form.state.purchasePendingSave.key, key);

    await form.submit(true);
    assert.equal(saved.size, 1);
    assert.deepEqual(attempts, [key, key]);
    assert.equal(form.state.closed, true);
    assert.equal(form.state.backup, null);
});

test('a browser backup snapshot error cannot prevent a PO save or leave the form locked', async () => {
    let calls = 0;
    const form = submissionHarness(async () => {
        calls++;
        return { status: 'success', data: { id: 11, no_po: 'PO-11' } };
    });
    const source = fs.readFileSync(new URL('../../resources/views/medcare/menu/pembelianPenerimaan/pembelian/partials/entryRecovery.blade.php', import.meta.url), 'utf8');
    const snapshot = source.slice(source.indexOf('        function persistPurchaseRecovery('),
        source.indexOf('        function showPurchaseRecoveryNotice('));
    Object.assign(form.state, { purchaseRecoveryTimer: null, purchaseRecovering: false, purchaseBackupAvailable: true });
    const select = form.state.$;
    form.state.$ = function(selector) {
        const element = select(selector);
        element.hasClass = () => true;
        if (selector === '#pembelianForm [name]') {
            element.each = () => { throw new Error('Browser snapshot unavailable'); };
        }
        return element;
    };
    form.state.persistPurchaseRecovery = new Function('environment',
        `with (environment) { ${snapshot}; return persistPurchaseRecovery; }`)(form.state);

    await assert.doesNotReject(form.submit(true));
    assert.equal(calls, 1);
    assert.equal(form.state.purchaseBackupAvailable, false);
    assert.equal(form.state.closed, true);
    assert.equal(form.state.purchaseSaveInProgress, false);
    assert.equal(form.state.buttonsDisabled, false);
    assert.equal(form.state.inert, false);
    assert.deepEqual(form.state.errors, []);
});

test('saving waits until editing details and bulk item additions have finished', () => {
    let calls = 0;
    const form = submissionHarness(async () => { calls++; });
    for (const flag of ['purchaseLoadingDetails', 'purchaseAddingItems']) {
        form.state[flag] = true;
        form.trySubmit();
        assert.equal(form.state.purchaseSaveInProgress, false);
        assert.equal(form.state.purchasePendingSave, null);
        assert.equal(calls, 0);
        form.state[flag] = false;
    }
});

test('opening recovered PO entries retains their preloaded conversions while a new form refreshes them', () => {
    const source = fs.readFileSync(new URL('../../resources/views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php', import.meta.url), 'utf8');
    const handler = source.slice(source.indexOf("        $('#pembelianModal').on('show.bs.modal'"),
        source.indexOf('        // --- Reset state modal setelah'));
    for (const recovering of [true, false]) {
        let open;
        const element = {
            on(event, callback) { open = callback; return this; },
            one() { return this; }, trigger() { return this; }, text() { return this; },
            html() { return this; }, val() { return this; }, find() { return this; },
            prop() { return this; }, removeClass() { return this; },
        };
        const cache = new Map([['42', [{ id: 3, konversi: 10 }]]]);
        const environment = {
            purchaseRecovering: recovering, purchaseRecoveryOpening: recovering, editMode: recovering,
            purchaseRecoveryStore: { read: () => null }, purchaseSaveInProgress: false,
            medicineUnitCache: cache, purchaseEditRequestSequence: 0,
            currentPoNumberMode: null, currentPoDistributorId: null,
            purchaseFormDatePicker: { setDate() {} }, DistributorSelect: element,
            $: () => element, resetPurchaseEntry() {}, updateUnitLoadingState() {},
            resetMedicinePicker() {}, detailItemTemplate() { return ''; }, formatRupiah: String, hitungTotal() {}, loadObatInto() {},
        };
        new Function('environment', `with (environment) { ${handler} }`)(environment);
        open.call(element, { preventDefault() { throw new Error('Opening should be allowed'); } });
        assert.equal(cache.has('42'), recovering);
    }
});

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
