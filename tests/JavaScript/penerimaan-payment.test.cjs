const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname,
    '../../resources/views/medcare/menu/pembelianPenerimaan/penerimaan/jsMain.blade.php'), 'utf8');

class Field {
    constructor(name = '', value = '', data = {}) {
        this.name = name;
        this.value = value;
        this.metadata = data;
        this.focused = false;
        this.checked = false;
    }

    val(value) {
        if (value === undefined) return this.value;
        this.value = String(value);
        return this;
    }

    data(key, value) {
        if (value === undefined) return this.metadata[key];
        this.metadata[key] = value;
        return this;
    }

    is(selector) {
        return selector === ':focus' ? this.focused : this.checked;
    }

    first() { return this; }
    text() { return this; }
    html() { return this; }
    removeClass() { return this; }
    addClass() { return this; }
}

function collection(elements) {
    return { each(callback) { elements.forEach(element => callback.call(element)); } };
}

function receiptForm(rows, { additionalCost = 0, orderedQty = 10, compensation = 0 } = {}) {
    const fields = Object.fromEntries([
        'subtotal', 'diskon', 'pajak', 'biaya_lain', 'supplier_compensation_discount',
        'total_faktur', 'jumlah_dibayar', 'sisa_hutang', 'status_pembayaran',
    ].map(name => [name, new Field(name, '0')]));
    const compensationCheckbox = new Field();
    compensationCheckbox.checked = compensation > 0;
    fields.supplier_compensation_discount.val(compensation);
    const detailRows = rows.map(({ qty = 2, price = 100000, discounts = [10, 5, 2], tax = 11 } = {}) => {
        const row = new Field('', '', {
            max: orderedQty, konversi: 1, satuan: 'PCS', 'satuan-stok': 'PCS',
        });
        const inputs = {
            '.receive-qty': new Field('qty_diterima[]', qty),
            '.receive-price': new Field('harga_beli[]', price),
            '[name="diskon_1[]"]': new Field('diskon_1[]', discounts[0]),
            '[name="diskon_2[]"]': new Field('diskon_2[]', discounts[1]),
            '[name="diskon_3[]"]': new Field('diskon_3[]', discounts[2]),
            '.receive-tax': new Field('ppn[]', tax),
            '.receive-batch-select': new Field('stok_batch_id[]'),
            'input[name="no_batch[]"]': new Field('no_batch[]', 'BATCH-TEST'),
            'input[name="expired_date[]"]': new Field('expired_date[]', '04-10-2027'),
        };
        row.inputs = inputs;
        row.find = selector => inputs[selector] || new Field();
        Object.values(inputs).forEach(input => {
            input.closest = () => row;
            input.attr = name => name === 'name' ? input.name : undefined;
            input.hasClass = name => name === 'receive-discount' && input.name.startsWith('diskon_');
        });
        return row;
    });
    const moneyFields = [...Object.values(fields).filter(field => field.name !== 'status_pembayaran'),
        ...detailRows.map(row => row.inputs['.receive-price'])];
    const form = {
        find: () => collection(moneyFields),
        serialize: () => new URLSearchParams([
            ...moneyFields,
            ...detailRows.flatMap(row => Object.values(row.inputs).filter(input => input.name.startsWith('diskon_'))),
        ].map(field => [field.name, field.val()])).toString(),
    };
    let paymentClick;
    let inputChange;
    let batchRefreshes = 0;
    const document = {};
    const context = vm.createContext({
        Intl, Number, Math, document,
        paymentPreset: 'manual', currentPoAdditionalCost: additionalCost,
        currentPoTotalQty: orderedQty, supplierCompensationAvailable: compensation,
        updateInvoicePaymentUi() {},
        setReceiveBatchMode() { batchRefreshes++; },
        $(selector) {
            if (selector === document) return { on(event, selectors, callback) {
                assert.ok(selectors.includes('.receive-discount'));
                inputChange = callback;
            } };
            if (selector instanceof Field) return selector;
            if (selector === '#penerimaanForm') return form;
            if (selector === '.receive-detail-row') return collection(detailRows);
            if (selector === '#supplierCompensationDiscount') return fields.supplier_compensation_discount;
            if (selector === '#applySupplierCompensation') return compensationCheckbox;
            if (selector === '.receive-payment-action') return { on(event, callback) { paymentClick = callback; } };
            const name = selector.match(/input\[name="([^"]+)"\]/)?.[1];
            return fields[name] || new Field();
        },
    });
    for (const name of [
        'roundMoney', 'formatRupiah', 'formatDecimal', 'formatMoneyInputValue', 'parseCurrencyValue', 'moneyEditValue',
        'invoiceMoneyInput', 'setMoneyInput', 'getMoneyInput', 'setMoneyElement',
        'serializePenerimaanForm', 'paymentStatusFromAmounts', 'conversionText',
        'normalizedPercent', 'normalizedDiscount', 'tieredDiscountNet', 'effectiveTieredDiscount',
        'collectReceiveStats', 'syncInvoiceTotals',
    ]) {
        const start = source.indexOf(`        function ${name}(`);
        assert.ok(start >= 0, `Function ${name} exists`);
        const end = source.indexOf('\n        }', start) + '\n        }'.length;
        vm.runInContext(source.slice(start, end), context);
    }
    context.recalculateReceiveTotals = () => context.syncInvoiceTotals(context.collectReceiveStats());
    const changeStart = source.indexOf("        $(document).on('input change', '.receive-qty");
    const changeEnd = source.indexOf('\n        });', changeStart) + '\n        });'.length;
    vm.runInContext(source.slice(changeStart, changeEnd), context);
    const clickStart = source.indexOf("        $('.receive-payment-action').on('click'");
    const clickEnd = source.indexOf('\n        });', clickStart) + '\n        });'.length;
    vm.runInContext(source.slice(clickStart, clickEnd), context);

    return {
        fields, detailRows, context,
        recalculate: context.recalculateReceiveTotals,
        clickPayment(action) { paymentClick.call(new Field('', '', { 'payment-action': action })); },
        changeDiscount(rowIndex, tier, value) {
            const input = detailRows[rowIndex].inputs[`[name="diskon_${tier}[]"]`];
            input.val(value);
            inputChange.call(input);
        },
        batchRefreshes: () => batchRefreshes,
        payload: () => new URLSearchParams(context.serializePenerimaanForm()),
    };
}

test('Lunas displays and submits the exact invoice amount, including cents', () => {
    const form = receiptForm([{ qty: 3, price: 1234.56 }]);
    form.recalculate();
    form.clickPayment('full');
    const payload = form.payload();
    assert.equal(form.fields.total_faktur.val().replace(/\s/g, ''), 'Rp3.444,67');
    assert.equal(payload.get('total_faktur'), '3444.67');
    assert.equal(payload.get('jumlah_dibayar'), '3444.67');
    assert.equal(payload.get('sisa_hutang'), '0.00');
    assert.equal(form.fields.status_pembayaran.val(), 'lunas');
});

test('manually entering the displayed invoice total submits the same amount', () => {
    const form = receiptForm([{ qty: 3, price: 1234.56 }]);
    form.recalculate();
    form.fields.jumlah_dibayar.val(form.fields.total_faktur.val());
    form.recalculate();
    assert.equal(form.payload().get('jumlah_dibayar'), '3444.67');
    assert.equal(form.payload().get('sisa_hutang'), '0.00');
});

test('subtotal, tiered discount and tax are rounded per item before summing', () => {
    const form = receiptForm([{ qty: 3, price: 1234.56 }]);
    const stats = form.context.collectReceiveStats();
    assert.equal(stats.totalSubtotal, 3703.68);
    assert.equal(stats.totalDiscount, 600.37);
    assert.equal(stats.totalTax, 341.36);
    assert.equal(stats.grandTotal, 3444.67);
    form.recalculate();
    form.clickPayment('full');
    assert.equal(form.payload().get('jumlah_dibayar'), '3444.67');
    assert.equal(form.payload().get('harga_beli[]'), '1234.56');
});

test('multiple rows and allocated additional costs produce a cent-rounded invoice', () => {
    const form = receiptForm([
        { qty: 0.2, price: 1234.56 }, { qty: 0.3, price: 1234.56 },
    ], { additionalCost: 1 });
    form.recalculate();
    form.clickPayment('full');
    assert.equal(form.payload().get('biaya_lain'), '0.05');
    assert.equal(form.payload().get('total_faktur'), '574.17');
    assert.equal(form.payload().get('jumlah_dibayar'), '574.17');
});

test('Lunas pays the net invoice after supplier compensation', () => {
    const form = receiptForm([{ qty: 3, price: 1234.56 }], { compensation: 1000.25 });
    form.recalculate();
    form.clickPayment('full');
    assert.equal(form.payload().get('total_faktur'), '3444.67');
    assert.equal(form.payload().get('supplier_compensation_discount'), '1000.25');
    assert.equal(form.payload().get('jumlah_dibayar'), '2444.42');
    assert.equal(form.payload().get('sisa_hutang'), '0.00');
});

test('half payment rounds to cents and preserves the correct remaining debt', () => {
    const form = receiptForm([{ qty: 3, price: 1234.56 }]);
    form.recalculate();
    form.clickPayment('half');
    assert.equal(form.payload().get('jumlah_dibayar'), '1722.34');
    assert.equal(form.payload().get('sisa_hutang'), '1722.33');
    assert.equal(form.fields.status_pembayaran.val(), 'sebagian');
});

test('editing or blurring a decimal price preserves the price and invoice total', () => {
    const form = receiptForm([{ qty: 3, price: 1234.56 }]);
    const price = form.detailRows[0].inputs['.receive-price'];
    price.val(form.context.moneyEditValue(price.val()));
    form.context.setMoneyElement(price, form.context.parseCurrencyValue(price.val()));
    form.recalculate();
    form.clickPayment('full');
    assert.equal(form.payload().get('harga_beli[]'), '1234.56');
    assert.equal(form.payload().get('jumlah_dibayar'), '3444.67');
});

test('whole-rupiah invoices and unpaid invoices retain their existing behavior', () => {
    const form = receiptForm([{ qty: 1, price: 100000, discounts: [10, 0, 0] }]);
    form.recalculate();
    form.clickPayment('full');
    assert.equal(form.payload().get('jumlah_dibayar'), '99900.00');
    form.clickPayment('none');
    assert.equal(form.payload().get('jumlah_dibayar'), '0.00');
    assert.equal(form.payload().get('sisa_hutang'), '99900.00');
    assert.equal(form.fields.status_pembayaran.val(), 'belum_dibayar');
});

test('half-cent discount rounding matches the server despite floating-point precision', () => {
    const form = receiptForm([{ qty: 0.2, price: 3.5, discounts: [5, 0, 0] }]);
    form.recalculate();
    form.clickPayment('full');
    assert.equal(form.payload().get('subtotal'), '0.70');
    assert.equal(form.payload().get('diskon'), '0.03');
    assert.equal(form.payload().get('pajak'), '0.07');
    assert.equal(form.payload().get('jumlah_dibayar'), '0.74');
    assert.equal(form.payload().get('sisa_hutang'), '0.00');
});

test('editing all three discounts recalculates tax, full payment and batch matching', () => {
    const form = receiptForm([{ qty: 2, price: 100000 }]);
    form.recalculate();
    form.clickPayment('full');
    form.changeDiscount(0, 1, 20);
    form.changeDiscount(0, 2, 10);
    form.changeDiscount(0, 3, 5);

    const payload = form.payload();
    assert.equal(payload.get('diskon_1[]'), '20');
    assert.equal(payload.get('diskon_2[]'), '10');
    assert.equal(payload.get('diskon_3[]'), '5');
    assert.equal(payload.get('diskon'), '63200.00');
    assert.equal(payload.get('pajak'), '15048.00');
    assert.equal(payload.get('total_faktur'), '151848.00');
    assert.equal(payload.get('jumlah_dibayar'), '151848.00');
    assert.equal(payload.get('sisa_hutang'), '0.00');
    assert.equal(form.batchRefreshes(), 3);
});

test('removing discounts on one row keeps the other row discounts and updates debt', () => {
    const form = receiptForm([{ qty: 1, discounts: [20, 10, 5] }, { qty: 1 }]);
    form.recalculate();
    form.clickPayment('none');
    for (let tier = 1; tier <= 3; tier++) form.changeDiscount(0, tier, 0);
    const payload = form.payload();
    assert.deepEqual(payload.getAll('diskon_1[]'), ['0', '10']);
    assert.equal(payload.get('total_faktur'), '204006.90');
    assert.equal(payload.get('sisa_hutang'), '204006.90');
});

test('reopening a receipt renders its saved discounts, including zero, instead of PO discounts', () => {
    const { context } = receiptForm([]);
    for (const name of ['escapeHtml', 'receiveBatchOptionsHtml', 'detailRowTemplate']) {
        const start = source.indexOf(`        function ${name}(`);
        const end = source.indexOf('\n        }', start) + '\n        }'.length;
        vm.runInContext(source.slice(start, end), context);
    }
    context.receiptInvoiceMode = '';
    const item = {
        id: 1, obat_id: 2, nama_obat: 'Obat', satuan: 'PCS', outstanding_qty: 2,
        diskon_1: 10, diskon_2: 5, diskon_3: 2, diskon_efektif: 16.21,
    };
    const saved = context.detailRowTemplate(item, { diskon_1: 0, diskon_2: 7.5, diskon_3: 3 });
    const initial = context.detailRowTemplate(item);
    for (const [index, value] of [0, 7.5, 3].entries()) {
        assert.match(saved, new RegExp(`name="diskon_${index + 1}\\[\\]"[^>]*value="${value}"`));
    }
    assert.ok(saved.includes('Efektif 10,28%'));
    for (const [index, value] of [10, 5, 2].entries()) {
        assert.match(initial, new RegExp(`name="diskon_${index + 1}\\[\\]"[^>]*value="${value}"`));
    }
});
