const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname,
    '../../resources/views/medcare/menu/pembelianPenerimaan/penerimaan/jsMain.blade.php'), 'utf8');

function receiptSave({ mode = 'single', id = '', serialized = 'nomor_faktur=INV-TEST' } = {}) {
    const fields = new Map(Object.entries({
        '#penerimaan_id': id,
        '#purchase_order_id': '60',
        'input[name="nomor_faktur"]': 'INV-TEST',
        'input[name="tanggal_faktur"]': '21-10-2026',
    }));
    const alerts = [];
    const hiddenCallbacks = [];
    const requests = [];
    let submit;
    let hide;
    let closed = 0;
    let nextInvoices = 0;
    let reloads = 0;
    let buttonsDisabled = false;
    let dialogResult = { isConfirmed: true };
    const context = vm.createContext({
        URLSearchParams, JSON, Number, String, Object,
        document: { activeElement: {} },
        receiptSaving: false,
        receiptDirty: false,
        receiptAllowClose: false,
        receiptInvoiceMode: mode,
        editMode: Boolean(id),
        recalculateReceiveTotals() {},
        collectReceiveStats: () => ({ itemCount: 1, invalidRows: 0 }),
        serializePenerimaanForm: () => serialized,
        openNextInvoice(poId) { assert.equal(poId, '60'); nextInvoices++; },
        PenerimaanTable: { ajax: { reload() { reloads++; } } },
        Swal: { DismissReason: { cancel: 'cancel' }, fire(...args) { alerts.push(args); return Promise.resolve(dialogResult); } },
        $(selector) {
            const element = {
                val(value) {
                    if (value === undefined) return fields.get(selector) || '';
                    fields.set(selector, value);
                    return element;
                },
                filter() { return element; },
                trigger() { return element; },
                text() { return element; },
                addClass() { return element; },
                removeClass() { return element; },
                prop(name, value) {
                    if (selector === '#submitPenerimaanForm, #submitPenerimaanNextInvoice') buttonsDisabled = value;
                    return element;
                },
                on(event, handler) {
                    if (selector === '#penerimaanForm' && event === 'submit') submit = handler;
                    if (selector === '#penerimaanModal' && event === 'hide.bs.modal') hide = handler;
                    return element;
                },
                one(event, handler) { hiddenCallbacks.push(handler); return element; },
                modal(action) {
                    if (action === 'hide') {
                        let prevented = false;
                        hide?.({ preventDefault() { prevented = true; } });
                        if (!prevented) closed++;
                    }
                    return element;
                },
            };
            return element;
        },
    });
    context.$.ajax = options => requests.push(options);
    for (const name of ['penerimaanJsonPayload', 'showReceiptSaveError']) {
        const start = source.indexOf(`        function ${name}(`);
        if (start < 0) continue;
        const end = source.indexOf('\n        }', start) + '\n        }'.length;
        vm.runInContext(source.slice(start, end), context);
    }
    const start = source.indexOf("        $('#penerimaanForm').on('submit'");
    const end = source.indexOf('\n\n        window.editPenerimaan', start);
    vm.runInContext(source.slice(start, end), context);
    const hideStart = source.indexOf("        $('#penerimaanModal').on('hide.bs.modal'");
    if (hideStart >= 0) {
        const hideEnd = source.indexOf('\n        });', hideStart) + '\n        });'.length;
        vm.runInContext(source.slice(hideStart, hideEnd), context);
    }

    return {
        context, fields, alerts, requests,
        dialogResult(value) { dialogResult = value; },
        submit(submitter = 'submitPenerimaanForm') {
            submit({ preventDefault() {}, originalEvent: { submitter: { id: submitter } } });
        },
        finish() {
            requests.at(-1).complete();
            hiddenCallbacks.splice(0).forEach(callback => callback());
        },
        tryClose() {
            let prevented = false;
            hide?.({ preventDefault() { prevented = true; } });
            return prevented;
        },
        closed: () => closed,
        nextInvoices: () => nextInvoices,
        reloads: () => reloads,
        buttonsDisabled: () => buttonsDisabled,
    };
}

for (const [label, mode, button, id] of [
    ['single invoice draft', 'single', 'submitPenerimaanForm', ''],
    ['split invoice draft', 'split', 'submitPenerimaanForm', ''],
    ['split and next invoice', 'split', 'submitPenerimaanNextInvoice', ''],
    ['draft update', '', 'submitPenerimaanForm', '17'],
]) {
    test(`${label} only closes on a confirmed receipt response`, () => {
        const form = receiptSave({ mode, id });
        form.submit(button);
        assert.equal(form.requests.length, 1);
        assert.equal(form.requests[0].type, id ? 'PUT' : 'POST');
        form.requests[0].success({ status: 'success', message: 'Tersimpan',
            data: { id: id || 17, nomor_penerimaan: 'PB-2026-10-0017' } });
        form.finish();
        assert.equal(form.closed(), 1);
        assert.equal(form.reloads(), 1);
        assert.equal(form.nextInvoices(), button === 'submitPenerimaanNextInvoice' ? 1 : 0);
        assert.equal(form.alerts[0][0].icon, 'success');
        assert.equal(form.buttonsDisabled(), false);
    });

    test(`${label} retains the form for an HTML page or incomplete success response`, () => {
        for (const response of [
            '<!DOCTYPE html><html>Login or validation redirect</html>',
            null, {}, { status: 'error', message: 'Ditolak' },
            { status: 'success' }, { status: 'success', data: { id: 17 } },
            { status: 'success', data: { id: 0, nomor_penerimaan: 'PB-INVALID' } },
            { status: 'success', data: { id: 17, nomor_penerimaan: ' ' } },
            { status: 'success', data: { id: 17, nomor_penerimaan: {} } },
        ]) {
            const form = receiptSave({ mode, id });
            form.submit(button);
            form.requests[0].success(response);
            form.finish();
            assert.equal(form.closed(), 0);
            assert.equal(form.reloads(), 0);
            assert.equal(form.nextInvoices(), 0);
            assert.equal(form.alerts[0][0].icon, 'error');
            assert.equal(form.fields.get('input[name="nomor_faktur"]'), 'INV-TEST');
            assert.equal(form.buttonsDisabled(), false);
        }
    });
}

test('99 PO rows are sent as JSON with every parallel field and the invoice fields after them', () => {
    const values = new URLSearchParams();
    const row = {
        purchase_order_detail_id: '100', obat_id: '5', qty_diterima: '1',
        stok_batch_id: '', no_batch: 'BATCH-A&B + 1', expired_date: '21-10-2027',
        harga_beli: '1234.56', diskon_1: '10', diskon_2: '5', diskon_3: '2', ppn: '11',
    };
    for (let index = 0; index < 99; index++) {
        for (const [name, value] of Object.entries(row)) values.append(`${name}[]`, value);
    }
    values.append('nomor_faktur', 'INV-99');
    values.append('tanggal_faktur', '21-10-2026');
    const form = receiptSave({ serialized: values.toString() });
    form.submit();
    const request = form.requests[0];
    assert.equal(request.contentType, 'application/json; charset=UTF-8');
    assert.equal(request.dataType, 'json');
    assert.equal(request.headers.Accept, 'application/json');
    const payload = JSON.parse(request.data);
    for (const [name, value] of Object.entries(row)) {
        assert.deepEqual(payload[name], Array(99).fill(value));
    }
    assert.equal(payload.nomor_faktur, 'INV-99');
    assert.equal(payload.tanggal_faktur, '21-10-2026');
});

test('validation, expired sessions, parser errors and network failures retain the form', () => {
    for (const [xhr, status, message] of [
        [{ status: 422, responseJSON: { errors: { no_batch: ['Nomor batch wajib diisi.'] } } }, 'error', /Nomor batch/],
        [{ status: 401 }, 'error', /sesi|masuk/i],
        [{ status: 419 }, 'error', /sesi|masuk/i],
        [{ status: 200, responseText: '<html>Redirect</html>' }, 'parsererror', /belum.*dipastikan|belum.*terkonfirmasi/i],
        [{ status: 0 }, 'error', /belum.*dipastikan|belum.*terkonfirmasi/i],
        [{ status: 500 }, 'error', /belum.*dipastikan|belum.*terkonfirmasi/i],
    ]) {
        const form = receiptSave({ mode: 'split' });
        form.submit('submitPenerimaanNextInvoice');
        form.requests[0].error(xhr, status);
        form.finish();
        assert.equal(form.closed(), 0);
        assert.equal(form.nextInvoices(), 0);
        assert.equal(form.buttonsDisabled(), false);
        assert.match(form.alerts[0][0].text, message);
    }
});

test('a pending save cannot be submitted again or closed', () => {
    const form = receiptSave();
    form.submit();
    form.submit();
    assert.equal(form.requests.length, 1);
    assert.equal(form.buttonsDisabled(), true);
    assert.equal(form.tryClose(), true);
    form.requests[0].error({ status: 422, responseJSON: { message: 'Periksa data' } }, 'error');
    form.finish();
    assert.equal(form.tryClose(), false);
});

test('closing an unsaved form keeps the entered data until the user chooses to discard it', async () => {
    const form = receiptSave();
    form.context.receiptDirty = true;
    assert.equal(form.tryClose(), true);
    await Promise.resolve();
    assert.equal(form.closed(), 0);
    assert.equal(form.context.receiptDirty, true);
    assert.equal(form.fields.get('input[name="nomor_faktur"]'), 'INV-TEST');
    form.dialogResult({ dismiss: 'cancel' });
    assert.equal(form.tryClose(), true);
    await Promise.resolve();
    assert.equal(form.closed(), 1);
    assert.equal(form.context.receiptDirty, false);
});
