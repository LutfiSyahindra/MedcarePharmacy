import { test } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import '../../public/assets/js/purchase-order-entry.js';

const { formatMoney, moneyInput, calculateAmounts, summarizeAmounts, formPayload } = globalThis.PurchaseOrderEntry;

test('Rupiah formatting preserves zero, thousands, and cents', () => {
    assert.equal(formatMoney(0), 'Rp 0');
    assert.equal(formatMoney(1500.5), 'Rp 1.500,50');
    assert.equal(formatMoney(1234567.89), 'Rp 1.234.567,89');
});

test('editing and pasting Indonesian prices retains numeric values and cursor position', () => {
    assert.deepEqual(moneyInput('1500'), { display: 'Rp 1.500', amount: '1500', caret: 8 });
    assert.deepEqual(moneyInput('Rp 1.500,50'), { display: 'Rp 1.500,50', amount: '1500.50', caret: 11 });
    assert.deepEqual(moneyInput('Rp 12.345', 5), { display: 'Rp 12.345', amount: '12345', caret: 5 });
    assert.deepEqual(moneyInput('Rp 01500'), { display: 'Rp 1.500', amount: '1500', caret: 8 });
    assert.equal(moneyInput('Rp 1.500,').amount, '1500');
    assert.equal(moneyInput('Rp 1.500,').display, 'Rp 1.500,');
    assert.equal(moneyInput('0,05').amount, '0.05');
});

test('empty, negative, and malformed pasted prices remain invalid rather than becoming zero', () => {
    for (const input of ['', 'Rp ', '-1500', 'abc', '1,2,3', '1,234', 'Infinity']) {
        assert.equal(moneyInput(input).amount, '', input);
    }
});

test('tiered discounts apply sequentially before tax using the purchase conversion', () => {
    const amounts = calculateAmounts({ qty: 2, price: 10000, conversion: 10, discounts: [10, 5, 2], tax: 11 });
    assert.deepEqual(amounts, {
        unitPrice: 100000, gross: 200000, discount: 32420,
        net: 167580, tax: 18433.8, subtotal: 186013.8,
    });
});

test('mixed taxes and discounts reconcile with the total without counting tax twice', () => {
    const rows = [
        calculateAmounts({ qty: 2, price: 10000, conversion: 10, discounts: [10, 5, 2], tax: 11 }),
        calculateAmounts({ qty: 1.5, price: 1500.5, tax: 0 }),
    ];
    const summary = summarizeAmounts(rows, 4000);
    assert.deepEqual(summary, {
        gross: 202250.75, discount: 32420, tax: 18433.8,
        subtotal: 188264.55, additionalCost: 4000, total: 192264.55,
    });
    assert.equal(Math.round((summary.gross - summary.discount + summary.tax + summary.additionalCost) * 100), Math.round(summary.total * 100));
});

test('zero tax, full discount, and fractional converted prices match the numeric payload', () => {
    assert.equal(calculateAmounts({ qty: 1, price: 1500, discounts: [100], tax: 11 }).subtotal, 0);
    const amounts = calculateAmounts({ qty: 2.5, price: 123.45, conversion: 1.25, tax: 0 });
    assert.equal(amounts.unitPrice, 154.31);
    assert.equal(amounts.subtotal, 385.78);
    const payload = formPayload([
        { name: 'harga_estimasi_satuan[]', value: moneyInput('Rp 123,45').amount },
        { name: 'harga_estimasi[]', value: amounts.unitPrice.toFixed(2) },
        { name: 'subtotal[]', value: amounts.subtotal.toFixed(2) },
    ], true);
    assert.deepEqual(payload.harga_estimasi_satuan, ['123.45']);
    assert.deepEqual(payload.harga_estimasi, ['154.31']);
    assert.deepEqual(payload.subtotal, ['385.78']);
    assert.ok(!JSON.stringify(payload).includes('Rp'));
});

test('many fractional rows retain cents and empty orders have zero totals', () => {
    const rows = Array.from({ length: 600 }, () => calculateAmounts({ qty: 1, price: 0.1, tax: 11 }));
    assert.deepEqual(summarizeAmounts(rows), {
        gross: 60, discount: 0, tax: 6, subtotal: 66, additionalCost: 0, total: 66,
    });
    assert.equal(summarizeAmounts([]).total, 0);
});

test('apply-all tax updates filtered and unselected rows, recalculates totals, and keeps the default for new rows', () => {
    const source = fs.readFileSync(new URL('../../resources/views/medcare/menu/pembelianPenerimaan/pembelian/partials/entryWorkflow.blade.php', import.meta.url), 'utf8');
    const handler = source.slice(source.indexOf("        $('#applyPurchaseAllTax').on"), source.indexOf("        $('#detail-wrapper').on('keydown'"));
    const rows = [
        { tax: 11, hidden: false, selected: true },
        { tax: 0, hidden: true, selected: false },
        { tax: 12, hidden: false, selected: false },
    ];
    let callback, dirty = false, totalUpdates = 0, status = '', validityReports = 0;
    const field = { value: '7.25', validity: { valid: true }, reportValidity() { validityReports++; } };
    const $ = target => {
        if (target === '#applyPurchaseAllTax') return { on(event, fn) { callback = fn; } };
        if (target === '#detail-wrapper .detail-item') return { length: rows.length, each(fn) { rows.forEach(row => fn.call(row)); } };
        if (target === '#purchaseAllTaxStatus') return { text(value) { status = value; } };
        return { item: target, find() { return { val(value) { target.tax = value; } }; }, removeData() {} };
    };
    const defaultTax = new Function('$', 'document', 'normalizedDiscount', 'recalculatePurchaseRow', 'markPurchaseDirty', 'hitungTotal', 'formatStockQuantity', `let purchaseDefaultTax = 11; ${handler}; return () => purchaseDefaultTax;`)(
        $, { getElementById() { return field; } }, Number,
        row => { row.item.amounts = calculateAmounts({ qty: 1, price: 1000, tax: row.item.tax }); },
        () => { dirty = true; }, () => { totalUpdates++; }, String,
    );
    callback();
    assert.deepEqual(rows.map(row => row.tax), [7.25, 7.25, 7.25]);
    assert.deepEqual(rows.map(row => row.amounts.subtotal), [1072.5, 1072.5, 1072.5]);
    assert.equal(defaultTax(), 7.25);
    assert.equal(dirty, true);
    assert.equal(totalUpdates, 1);
    assert.match(status, /3 baris/);
    field.value = '101'; field.validity.valid = false;
    callback();
    assert.equal(defaultTax(), 7.25);
    assert.equal(validityReports, 1);
    assert.equal(totalUpdates, 1);
});
