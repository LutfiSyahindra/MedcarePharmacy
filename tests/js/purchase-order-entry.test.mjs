import { test } from 'node:test';
import assert from 'node:assert/strict';
import '../../public/assets/js/purchase-order-entry.js';

const { parsePaste, splitBatches, rowIssues, formPayload } = globalThis.PurchaseOrderEntry;
const catalog = [
    { id: 1, kode_obat: 'OBT001', nama_obat: 'Obat A', harga_beli: 1500 },
    { id: 2, kode_obat: 'OBT002', nama_obat: 'Obat B', harga_beli: 2000 },
];

test('Excel paste preserves decimal quantities, optional catalog prices, zero prices, and source line numbers', () => {
    const rows = parsePaste('obt001\t2,5\n\nOBT002\t3\t0\r\n', catalog);
    assert.deepEqual(rows.map(row => [row.medicine.id, row.qty, row.price, row.lineNumber, row.errors]), [
        [1, 2.5, 1500, 1, []], [2, 3, 0, 3, []],
    ]);
});

test('paste rejects unknown codes, duplicate medicines, and codes already in the PO', () => {
    const rows = parsePaste('OBT001\t2\nOBT001\t3\nOBT002\t1\nUNKNOWN\t1', catalog, ['2']);
    assert.equal(rows[0].errors.length, 0);
    assert.match(rows[1].errors.join(), /berulang/);
    assert.match(rows[2].errors.join(), /sudah ada/);
    assert.match(rows[3].errors.join(), /tidak ditemukan/);
});

test('paste rejects malformed or negative numbers without silently turning them into zero', () => {
    for (const raw of ['OBT001\t0', 'OBT001\t-2', 'OBT001\t1\tRp 2.000', 'OBT001\t1\t1.234,56', 'OBT001,2,1000', 'OBT001\t1\tInfinity']) {
        assert.ok(parsePaste(raw, catalog)[0].errors.length > 0, raw);
    }
});

test('ambiguous codes in the master cannot resolve to an arbitrary medicine', () => {
    const rows = parsePaste('OBT001\t2', [...catalog, { id: 3, kode_obat: 'obt001' }]);
    assert.match(rows[0].errors.join(), /tidak unik/);
});

test('a 601-medicine request is batched within the endpoint limit with every unique ID retained', () => {
    const ids = Array.from({ length: 601 }, (_, index) => index + 1);
    const batches = splitBatches([...ids, 1, 2]);
    assert.deepEqual(batches.map(batch => batch.length), [250, 250, 101]);
    assert.deepEqual(batches.flat(), ids.map(String));
});

test('missing conversion permits a draft, while incomplete requests and invalid prices still fail', () => {
    const item = { medicineId: '1', unitId: '0', unitLoading: false, qty: '2', price: '0', adjustments: ['0', '0', '0', '11'] };
    assert.deepEqual(rowIssues(item, true), []);
    assert.match(rowIssues(item).join(), /konversi/);
    assert.match(rowIssues({ ...item, unitLoading: true }, true).join(), /dimuat/);
    assert.match(rowIssues({ ...item, unitId: '', price: '' }, true).join(), /Harga/);
    assert.match(rowIssues({ ...item, adjustments: ['101'] }, true).join(), /0–100/);
});

test('JSON payload retains all 600 items and their aligned fields beyond PHP form input limits', () => {
    const fields = [{ name: 'no_po', value: 'PO-LARGE' }];
    for (let index = 0; index < 600; index++) {
        fields.push({ name: 'obat_id[]', value: String(index + 1) }, { name: 'qty[]', value: String(index + 2) }, { name: 'harga_estimasi[]', value: String(index * 100) });
    }
    const payload = JSON.parse(JSON.stringify(formPayload(fields, true)));
    assert.equal(payload.obat_id.length, 600);
    assert.equal(payload.qty.length, 600);
    assert.equal(payload.obat_id[599], '600');
    assert.equal(payload.qty[599], '601');
    assert.equal(payload.harga_estimasi[599], '59900');
    assert.equal(payload.no_po, 'PO-LARGE');
    assert.equal(payload.save_as_draft, true);
});
