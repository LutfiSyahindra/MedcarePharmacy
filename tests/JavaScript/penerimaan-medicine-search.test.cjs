const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname,
    '../../resources/views/medcare/menu/pembelianPenerimaan/penerimaan/jsMain.blade.php'), 'utf8');

test('receipt script parses after Blade values are resolved', () => {
    const script = source.slice(source.indexOf('<script>') + 8, source.lastIndexOf('</script>'))
        .replace(/@json\([^;]+\)/g, 'null');
    assert.doesNotThrow(() => new vm.Script(script));
});

test('selecting a medicine searches every date, keeps the PO scope, and clearing restores all medicines', () => {
    let changed;
    let selectOptions;
    let reloads = 0;
    let dateClears = 0;
    let hint = '';
    let preset = 'this_month';
    const medicineField = {
        select2(options) { selectOptions = options; return this; },
        on(event, callback) { changed = callback; return this; },
        find() { return { text: () => 'Paracetamol (PAR-500)' }; },
    };
    const element = {
        prop() { return this; },
        toggleClass() { return this; },
    };
    const context = vm.createContext({
        receiveMedicineId: '', receiveDateStart: '2026-10-01', receiveDateEnd: '2026-10-31',
        selectedPurchaseOrderId: 42,
        receiveDatePicker: { clear() { dateClears++; } },
        PenerimaanTable: { ajax: { reload() { reloads++; } } },
        renderReceiveMedicineOption: medicine => medicine.text,
        $(selector) {
            if (selector === '#receiveMedicineFilter' || typeof selector === 'object') return medicineField;
            if (selector === '#receiveDatePreset') return { val(value) { preset = value; } };
            if (selector === '#receiveMedicineFilterHint') return { text(value) { hint = value; } };
            if (['#clearReceiveMedicineFilter', '#receiveTableSection', '#receiveMedicineResults'].includes(selector)) return element;
            return { closest() { return { removeClass() {} }; } };
        },
    });
    const start = source.indexOf("        $('#receiveMedicineFilter').select2({");
    const end = source.indexOf('\n        });', start) + '\n        });'.length;
    vm.runInContext(source.slice(start, end), context);
    assert.equal(selectOptions.ajax.data({ term: 'PAR', page: 2 }).purchase_order_id, 42);

    changed.call({ value: '7' });
    assert.equal(context.receiveMedicineId, '7');
    assert.equal(context.receiveDateStart, '');
    assert.equal(context.receiveDateEnd, '');
    assert.equal(preset, '');
    assert.equal(dateClears, 1);
    assert.equal(reloads, 1);
    assert.ok(hint.includes('semua tanggal'));

    const tableStart = source.indexOf("let PenerimaanTable = $('#tablePenerimaan').DataTable({");
    const requestStart = source.indexOf('data: function(request) {', tableStart) + 'data: '.length;
    const requestEnd = source.indexOf('\n                },', requestStart) + '\n                }'.length;
    const buildRequest = vm.runInContext('(' + source.slice(requestStart, requestEnd) + ')', context);
    const request = {};
    buildRequest(request);
    assert.equal(request.obat_id, '7');
    assert.equal(request.purchase_order_id, 42);
    assert.equal(request.date_start, '');
    assert.equal(request.date_end, '');

    context.receiveDateStart = '2026-08-01';
    context.receiveDateEnd = '2026-08-31';
    changed.call({ value: '' });
    buildRequest(request);
    assert.equal(request.obat_id, '');
    assert.equal(request.date_start, '2026-08-01');
    assert.equal(request.date_end, '2026-08-31');
    assert.equal(dateClears, 1);
    assert.equal(reloads, 2);
});

test('receipt rows show multiple batches and display escaped medicine text only once', () => {
    const context = vm.createContext({
        formatDateDisplay: value => value || '-',
    });
    for (const name of ['formatDecimal', 'escapeHtml', 'renderReceiveItems']) {
        const start = source.indexOf(`        function ${name}(`);
        const end = source.indexOf('\n        }', start) + '\n        }'.length;
        vm.runInContext(source.slice(start, end), context);
    }
    const row = { total_barang: 4, total_qty: 10 };
    assert.equal(context.renderReceiveItems(row, 'display'), '4 item / 10');
    row.obat_dipilih_details = [
        { nama_obat: 'Obat &amp; Vitamin', qty_diterima: 2.5, satuan: 'BOX', no_batch: 'B-1', expired_date: '2028-01-01' },
        { nama_obat: 'Obat &lt;script&gt;alert(1)&lt;/script&gt;', qty_diterima: 1, satuan: 'PCS', no_batch: '&lt;img src=x&gt;', expired_date: null },
    ];
    const html = context.renderReceiveItems(row, 'display');
    assert.equal((html.match(/class="receive-medicine-match"/g) || []).length, 2);
    assert.ok(html.includes('2,5 BOX'));
    assert.ok(html.includes('Obat &amp; Vitamin'));
    assert.equal((html.match(/Obat &amp; Vitamin/g) || []).length, 1);
    assert.ok(!html.includes('&amp;amp;'));
    assert.ok(!html.includes('<script>'));
    assert.ok(!html.includes('<img'));
    assert.ok(html.includes('ED: -'));
    assert.equal(context.renderReceiveItems(row, 'sort'), '4 item / 10');
});

test('medicine picker displays name and code safely, while preserving loading and placeholder text', () => {
    const context = vm.createContext({ $: html => html });
    for (const name of ['escapeHtml', 'renderReceiveMedicineOption']) {
        const start = source.indexOf(`        function ${name}(`);
        const end = source.indexOf('\n        }', start) + '\n        }'.length;
        vm.runInContext(source.slice(start, end), context);
    }
    const html = context.renderReceiveMedicineOption({
        id: 7, nama_obat: 'Obat & <script>alert(1)</script>', kode_obat: '<img src=x>',
    });
    assert.ok(html.includes('Obat &amp; &lt;script&gt;'));
    assert.ok(html.includes('Kode: &lt;img src=x&gt;'));
    assert.ok(!html.includes('<script>'));
    assert.ok(!html.includes('<img'));
    assert.equal(context.renderReceiveMedicineOption({ text: 'Mencari obat...' }), 'Mencari obat...');
    assert.equal(context.renderReceiveMedicineOption({ id: 7, text: 'Obat lama' }), 'Obat lama');
});

test('medicine result cards follow the current page and clear correctly when the filter is removed', () => {
    const elements = new Map();
    const context = vm.createContext({
        receiveMedicineId: '7',
        formatDecimal: value => String(value),
        renderReceiveMedicineCard: row => `<article>${row.id}</article>`,
        $(selector) {
            if (!elements.has(selector)) {
                elements.set(selector, {
                    toggleClass(name, value) { this.classActive = value; return this; },
                    prop(name, value) { this[name] = value; return this; },
                    attr(name, value) { this[name] = value; return this; },
                    text(value) { this.content = value; return this; },
                    html(value) { this.content = value; return this; },
                    empty() { this.content = ''; return this; },
                });
            }
            return elements.get(selector);
        },
    });
    const start = source.indexOf('        function updateReceiveMedicineResults(');
    const end = source.indexOf('\n        }', start) + '\n        }'.length;
    vm.runInContext(source.slice(start, end), context);
    let rows = [{ id: 11 }, { id: 12 }];
    const table = {
        rows(options) {
            assert.equal(options.page, 'current');
            return { data: () => ({ toArray: () => rows }) };
        },
        page: { info: () => ({ start: 10, end: 12, recordsDisplay: 12 }) },
    };
    context.updateReceiveMedicineResults(table);
    assert.equal(elements.get('#receiveMedicineResultCount').content, '11–12 dari 12 penerimaan');
    assert.equal(elements.get('#receiveMedicineResultList').content, '<article>11</article><article>12</article>');
    assert.equal(elements.get('#receiveMedicineResults').hidden, false);
    assert.equal(elements.get('#receiveMedicineResults')['aria-busy'], 'false');

    rows = [];
    context.updateReceiveMedicineResults(table);
    assert.equal(elements.get('#receiveMedicineResultCount').content, '0 penerimaan ditemukan');
    assert.ok(elements.get('#receiveMedicineResultList').content.includes('Belum ada penerimaan yang sesuai'));

    context.receiveMedicineId = '';
    context.updateReceiveMedicineResults(table);
    assert.equal(elements.get('#receiveTableSection').classActive, false);
    assert.equal(elements.get('#receiveMedicineResults').hidden, true);
    assert.equal(elements.get('#receiveMedicineResultList').content, '');
    assert.equal(elements.get('#receiveMedicineResultCount').content, '');
});
