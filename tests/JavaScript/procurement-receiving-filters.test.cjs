const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname,
    '../../resources/views/medcare/menu/analisisPengadaan/partials/script.blade.php'), 'utf8');

function loadFunction(context, name, nextName) {
    const start = source.indexOf(`    function ${name}(`);
    const end = source.indexOf(`    ${nextName}`, start);
    assert.ok(start >= 0 && end > start);
    vm.runInContext(source.slice(start, end), context);
}

test('procurement script parses', () => {
    assert.doesNotThrow(() => new vm.Script(source.replace(/^<script>\s*|\s*<\/script>\s*$/g, '')));
});

test('receiving requests combine the dedicated filters with the global date and branch scope', () => {
    const forms = {
        '#paFilterForm': [['branch_id', '7'], ['date_start', '2026-08-01'], ['supplier_id', '']],
        '#paReceivingFilterForm': [['receiving_supplier_id', '42'], ['receiving_status', 'outstanding'], ['receiving_search', 'PO & obat']],
    };
    const context = vm.createContext({
        URLSearchParams,
        $: selector => forms[selector],
        FormData: class {
            constructor(entries) { this.entries = entries; }
            forEach(callback) { this.entries.forEach(([key, value]) => callback(value, key)); }
        },
    });
    loadFunction(context, 'queryString', 'async function requestJson');
    const query = new URLSearchParams(context.queryString());
    assert.equal(query.get('branch_id'), '7');
    assert.equal(query.get('date_start'), '2026-08-01');
    assert.equal(query.get('receiving_supplier_id'), '42');
    assert.equal(query.get('receiving_status'), 'outstanding');
    assert.equal(query.get('receiving_search'), 'PO & obat');
    assert.equal(query.has('supplier_id'), false);
});

test('receiving KPIs and tables show the filtered response and safely display search text', () => {
    const elements = new Map();
    const element = selector => {
        if (!elements.has(selector)) elements.set(selector, { innerHTML: '', textContent: '' });
        return elements.get(selector);
    };
    const options = [{ value: '', textContent: 'Semua item' }, { value: 'partial', textContent: 'Diterima sebagian' }];
    const context = vm.createContext({
        $: element,
        $$: () => options,
        number: value => String(Number(value || 0)),
        money: value => `Rp ${Number(value || 0)}`,
        escapeHtml: value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char])),
        formatDate: value => value,
        emptyRow: (columns, message) => `<tr><td colspan="${columns}">${message}</td></tr>`,
        renderReceivingTables: receiving => {
            context.renderReceived(receiving.received_items || []);
            context.renderOutstanding(receiving.outstanding || []);
            context.renderCancelled(receiving.cancelled_orders || []);
        },
        receivingTableDefinitions: {
            received: { prefix: 'paReceived', value: 'received_value' },
            outstanding: { prefix: 'paOutstanding', value: 'outstanding_value' },
            cancelled: { prefix: 'paCancelled', value: 'order_value' },
        },
    });
    loadFunction(context, 'renderReceiving', 'function receivingFilterError');
    loadFunction(context, 'renderReceived', 'function renderOutstanding');
    loadFunction(context, 'renderOutstanding', 'function renderCancelled');
    loadFunction(context, 'renderCancelled', 'function renderNeed');
    const row = {
        no_po: 'PO-ONE', date: '2026-08-25', medicine_id: 1, medicine: 'Obat <A>', supplier: 'OneMed',
        ordered_qty: 100, received_qty: 60, outstanding_qty: 40, received_value: 6000,
        outstanding_value: 4200, unit: 'Tablet', receipt_status: 'partial', status_label: 'Approved',
    };
    context.renderReceiving({
        filters: { supplier_id: 42, status: 'partial', search: '<img src=x>' },
        summary: {
            total_po: 1, unique_items: 1, received_items: 1, outstanding_items: 1,
            partial_received_items: 1, received_qty: 60, outstanding_qty: 40,
            received_value: 6000, outstanding_value: 4200,
        },
        received_items: [row], outstanding: [row], cancelled_orders: [],
    }, { suppliers: [{ id: 42, name: 'OneMed' }] });
    assert.equal(element('#paReceivingScope').textContent, 'OneMed · Diterima sebagian · Pencarian: <img src=x> · 1 PO / 1 jenis barang');
    const cards = element('#paReceivingKpis').innerHTML;
    assert.equal((cards.match(/<article /g) || []).length, 6);
    assert.ok(cards.includes('Rp 6000') && cards.includes('Rp 4200'));
    assert.ok(cards.includes('<strong>60</strong>') && cards.includes('<strong>40</strong>'));
    assert.ok(element('#paReceivedBody').innerHTML.includes('Obat &lt;A&gt;'));
    assert.ok(element('#paOutstandingBody').innerHTML.includes('Rp 4200'));
    assert.equal(element('#paReceivedCount').textContent, '1 item');
    assert.equal(element('#paOutstandingCount').textContent, '1 item');

    context.renderReceiving({ summary: {}, received_items: [], outstanding: [] }, { suppliers: [] });
    assert.equal(element('#paReceivedCount').textContent, '0 item');
    assert.equal(element('#paOutstandingCount').textContent, '0 item');
    assert.ok(element('#paReceivingKpis').innerHTML.includes('Rp 0'));
    assert.ok(element('#paOutstandingBody').innerHTML.includes('Tidak ada item dengan sisa penerimaan'));
    assert.ok(!element('#paReceivedBody').innerHTML.includes('PO-ONE'));
});

test('global reset clears both forms and restores the default date range', () => {
    const resets = [];
    let requests = 0;
    const elements = new Map();
    const context = vm.createContext({
        root: { dataset: { defaultStart: '2026-08-01', defaultEnd: '2026-08-31' } },
        $: selector => {
            if (!elements.has(selector)) elements.set(selector, { reset: () => resets.push(selector) });
            return elements.get(selector);
        },
        $$: () => [],
        loadData: () => { requests++; },
        resetReceivingTableFilters: () => resets.push('table filters'),
    });
    loadFunction(context, 'resetFilters', 'function toast');
    context.resetFilters();
    assert.deepEqual(resets, ['#paFilterForm', '#paReceivingFilterForm', 'table filters']);
    assert.equal(elements.get('#paDateStart').value, '2026-08-01');
    assert.equal(elements.get('#paDateEnd').value, '2026-08-31');
    assert.equal(requests, 1);
});

test('failed filter requests identify the previous summary instead of presenting it as the new result', () => {
    const elements = new Map();
    const element = selector => {
        if (!elements.has(selector)) elements.set(selector, {
            innerHTML: '', textContent: '', attributes: {},
            setAttribute(name, value) { this.attributes[name] = value; },
        });
        return elements.get(selector);
    };
    const context = vm.createContext({ $: element });
    loadFunction(context, 'setLoading', 'function render(');
    context.setLoading(true);
    assert.equal(element('#paReceivingApply').disabled, true);
    assert.equal(element('#paReceivingKpis').attributes['aria-busy'], 'true');
    context.setLoading(false, 'Request failed');
    assert.equal(element('#paReceivingApply').disabled, false);
    assert.equal(element('#paReceivingKpis').attributes['aria-busy'], 'false');
    assert.ok(element('#paReceivingScope').textContent.includes('filter sebelumnya'));
});

const receivedDefinition = { qty: 'received_qty', value: 'received_value', prefix: 'paReceived', unit: 'item' };
const tableRows = [
    { id: 1, no_po: 'PO-10', date: '2026-08-10', medicine_id: 1, medicine: 'Obat A', medicine_code: 'ABC-01', supplier_id: 7, supplier: 'Supplier A', received_qty: 5, outstanding_qty: 2, received_value: 100, outstanding_value: 40, status: 'partial', item_count: 2 },
    { id: 2, no_po: 'PO-2', date: '2026-08-20', medicine_id: 2, medicine: 'Obat B', medicine_code: 'ABC-02', supplier_id: 8, supplier: 'Supplier B', received_qty: 10, outstanding_qty: 0, received_value: 200, outstanding_value: 0, status: 'completed', item_count: 4 },
    { id: 3, no_po: 'PO-30', date: '2026-08-30', medicine_id: 1, medicine: 'Obat A', medicine_code: 'ABC-01', supplier_id: 7, supplier: 'Supplier A', received_qty: 0, outstanding_qty: 20, received_value: 0, outstanding_value: 300, status: 'approved', item_count: 1 },
];

function filteringContext(extra = {}) {
    const context = vm.createContext(extra);
    loadFunction(context, 'receivingFilterError', 'function filterReceivingRows');
    loadFunction(context, 'filterReceivingRows', 'function renderReceivingTables');
    return context;
}

test('table filters combine search, inclusive dates, supplier, medicine, receipt status and numeric ranges', () => {
    const context = filteringContext();
    const filters = {
        search: ' abc-01 ', date_start: '2026-08-10', date_end: '2026-08-10',
        supplier: '7', medicine: '1', receipt_status: 'partial',
        qty_min: '5', qty_max: '5', value_min: '100', value_max: '100',
    };
    assert.deepEqual(Array.from(context.filterReceivingRows(tableRows, filters, receivedDefinition), row => row.id), [1]);
    assert.equal(context.filterReceivingRows(tableRows, { ...filters, supplier: '8' }, receivedDefinition).length, 0);
    assert.deepEqual(Array.from(context.filterReceivingRows(tableRows, { qty_max: '0', value_max: '0' }, receivedDefinition), row => row.id), [3]);
    assert.deepEqual(Array.from(context.filterReceivingRows(tableRows, { receipt_status: 'complete' }, receivedDefinition), row => row.id), [2]);
    assert.deepEqual(Array.from(context.filterReceivingRows(tableRows, { search: 'SUPPLIER B' }, receivedDefinition), row => row.id), [2]);
});

test('outstanding and cancelled filters use their own quantities and values and sort without mutating source data', () => {
    const context = filteringContext();
    const outstanding = { qty: 'outstanding_qty', value: 'outstanding_value' };
    assert.deepEqual(Array.from(context.filterReceivingRows(tableRows, { receipt_status: 'none', status: 'approved', qty_min: '20', value_min: '300' }, outstanding), row => row.id), [3]);
    const cancelled = { qty: 'ordered_qty', value: 'order_value' };
    const cancelledRows = tableRows.map(row => ({ ...row, ordered_qty: row.item_count * 10, order_value: row.item_count * 100 }));
    assert.deepEqual(Array.from(context.filterReceivingRows(cancelledRows, { item_min: '2', item_max: '4', qty_min: '20', value_max: '400', sort: 'value:desc' }, cancelled), row => row.id), [2, 1]);
    assert.deepEqual(Array.from(context.filterReceivingRows(tableRows, { sort: 'no_po:asc' }, receivedDefinition), row => row.id), [2, 1, 3]);
    assert.deepEqual(Array.from(context.filterReceivingRows(tableRows, { sort: 'date:desc' }, receivedDefinition), row => row.id), [3, 2, 1]);
    assert.deepEqual(tableRows.map(row => row.id), [1, 2, 3]);
});

test('invalid ranges are rejected while zero and equal boundaries remain valid', () => {
    const context = filteringContext();
    assert.ok(context.receivingFilterError({ date_start: '2026-08-20', date_end: '2026-08-10' }));
    for (const field of ['qty', 'value', 'item']) {
        assert.ok(context.receivingFilterError({ [`${field}_min`]: '2', [`${field}_max`]: '1' }));
        assert.ok(context.receivingFilterError({ [`${field}_min`]: '-1' }));
        assert.equal(context.receivingFilterError({ [`${field}_min`]: '0', [`${field}_max`]: '0' }), '');
    }
    assert.equal(context.receivingFilterError({}), '');
});

test('quick periods include today and handle month, year and leap-year boundaries', () => {
    const context = vm.createContext({
        dateInput: date => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-'),
    });
    loadFunction(context, 'quickPeriodDates', 'function updateReceivingTablePeriod');
    const today = new Date(2026, 0, 3, 15);
    for (const [range, start] of [['today', '2026-01-03'], ['7', '2025-12-28'], ['30', '2025-12-05'], ['mtd', '2026-01-01'], ['ytd', '2026-01-01']]) {
        const dates = context.quickPeriodDates(range, today);
        assert.equal(dates.start, start);
        assert.equal(dates.end, '2026-01-03');
    }
    assert.equal(context.quickPeriodDates('7', new Date(2024, 2, 1)).start, '2024-02-24');
    assert.equal(context.quickPeriodDates('scope', today).start, '');
    assert.equal(context.quickPeriodDates('scope', today).end, '');
    assert.equal(context.quickPeriodDates('custom', today), null);
});

test('pagination renders every matching row and totals cover all pages', () => {
    const elements = new Map();
    const element = selector => {
        if (!elements.has(selector)) elements.set(selector, { textContent: '', hidden: false });
        return elements.get(selector);
    };
    let rendered = [];
    const definition = { ...receivedDefinition, render: rows => { rendered = Array.from(rows); } };
    const table = {
        rows: Array.from({ length: 61 }, (_, index) => ({ ...tableRows[0], id: index + 1 })),
        filters: {}, page: 1, pageSize: '25', form: {}, section: {},
    };
    const context = filteringContext({
        state: { receivingTables: { received: table } }, receivingTableDefinitions: { received: definition },
        $: element, number: String, money: value => `Rp ${value}`,
    });
    loadFunction(context, 'renderReceivingTable', 'function resetReceivingTableFilters');
    context.renderReceivingTable('received');
    assert.equal(rendered.length, 25);
    assert.equal(element('#paReceivedCount').textContent, '61 item');
    assert.ok(element('[data-table-summary]').textContent.includes('Qty 305'));
    assert.ok(element('[data-table-summary]').textContent.includes('Rp 6100'));
    assert.equal(element('[data-table-page="-1"]').disabled, true);
    assert.equal(element('[data-table-page="1"]').disabled, false);
    table.page = 3;
    context.renderReceivingTable('received');
    assert.equal(rendered.length, 11);
    assert.equal(rendered[0].id, 51);
    assert.equal(element('[data-table-page="1"]').disabled, true);
    table.filters = { qty_min: '6' };
    context.renderReceivingTable('received');
    assert.equal(rendered.length, 0);
    assert.equal(table.page, 1);
    assert.equal(element('#paReceivedCount').textContent, '0 / 61 item');
    table.filters = {};
    table.pageSize = 'all';
    context.renderReceivingTable('received');
    assert.equal(rendered.length, 61);
});

test('each table applies and resets filters independently without changing accordion state', () => {
    const sections = ['received', 'outstanding', 'cancelled'].map(kind => {
        const elements = new Map();
        const form = {
            values: {}, listeners: {}, checkValidity: () => true,
            reset() { this.values = {}; },
            addEventListener(type, callback) { this.listeners[type] = callback; },
        };
        const periods = ['scope', 'today', '7', '30', 'mtd', 'ytd', 'custom'].map(range => ({
            dataset: { tableRange: range }, listeners: {}, attributes: {},
            classList: { toggle() {} },
            setAttribute(name, value) { this.attributes[name] = value; },
            addEventListener(type, callback) { this.listeners[type] = callback; },
        }));
        return { dataset: { receivingTable: kind }, open: true, form, elements, periods };
    });
    const renders = [];
    const state = { receivingTables: {} };
    const context = filteringContext({
        state,
        $: (selector, scope) => {
            if (selector === '[data-table-filter]') return scope.form;
            const section = sections.find(item => item === scope || item.form === scope);
            if (!section.elements.has(selector)) section.elements.set(selector, {
                textContent: '', listeners: {}, focused: false,
                focus() { this.focused = true; },
                addEventListener(type, callback) { this.listeners[type] = callback; },
            });
            const dateField = selector.match(/^\[name="(date_start|date_end)"\]$/)?.[1];
            if (dateField && !Object.getOwnPropertyDescriptor(section.elements.get(selector), 'value')) {
                Object.defineProperty(section.elements.get(selector), 'value', {
                    get: () => section.form.values[dateField] || '',
                    set: value => { section.form.values[dateField] = value; },
                });
            }
            return section.elements.get(selector);
        },
        $$: (selector, scope) => selector === '[data-receiving-table]' ? sections
            : (selector === '[data-table-range]' ? sections.find(section => section.form === scope).periods : []),
        FormData: class { constructor(form) { return Object.entries(form.values); } },
        window: { matchMedia: () => ({ matches: true }) },
        renderReceivingTable: kind => renders.push(kind),
        quickPeriodDates: range => range === 'scope' ? { start: '', end: '' } : { start: '2026-08-25', end: '2026-08-31' },
    });
    loadFunction(context, 'updateReceivingTablePeriod', 'function resetReceivingTableFilters');
    loadFunction(context, 'resetReceivingTableFilters', 'function initializeReceivingTables');
    loadFunction(context, 'initializeReceivingTables', 'function renderReceived');
    context.initializeReceivingTables();
    const received = sections[0];
    assert.equal(received.elements.get('[data-table-filter-details]').open, false);
    assert.equal(state.receivingTables.received.pageSize, '5');
    assert.equal(received.elements.get('[data-table-page-size]').value, '5');
    assert.equal(sections[1].open, false);
    assert.equal(sections[2].open, false);
    received.open = false;
    received.form.values = { search: 'PO-2', qty_min: '1' };
    received.form.listeners.input();
    assert.equal(state.receivingTables.received.filters.search, 'PO-2');
    assert.equal(state.receivingTables.outstanding.filters.search, undefined);
    assert.equal(state.receivingTables.cancelled.filters.search, undefined);
    received.form.values = { qty_min: '10', qty_max: '2' };
    received.form.listeners.input();
    assert.equal(state.receivingTables.received.filters.search, 'PO-2');
    assert.equal(renders.length, 1);
    received.form.values = { search: 'PO-2' };
    received.periods.find(button => button.dataset.tableRange === '7').listeners.click();
    assert.equal(state.receivingTables.received.filters.date_start, '2026-08-25');
    assert.equal(state.receivingTables.received.filters.date_end, '2026-08-31');
    assert.equal(state.receivingTables.received.filters.search, 'PO-2');
    assert.equal(state.receivingTables.received.range, '7');
    assert.equal(received.periods.find(button => button.dataset.tableRange === '7').attributes['aria-pressed'], 'true');
    assert.equal(state.receivingTables.outstanding.range, 'scope');
    received.form.listeners.input({ target: { name: 'date_start' } });
    assert.equal(state.receivingTables.received.range, 'custom');
    received.periods.find(button => button.dataset.tableRange === 'scope').listeners.click();
    assert.equal(state.receivingTables.received.filters.date_start, '');
    assert.equal(state.receivingTables.received.filters.date_end, '');
    assert.equal(state.receivingTables.received.filters.search, 'PO-2');
    received.periods.find(button => button.dataset.tableRange === 'custom').listeners.click();
    assert.equal(received.elements.get('[name="date_start"]').focused, true);
    received.elements.get('[data-table-reset]').listeners.click();
    assert.equal(state.receivingTables.received.filters.search, undefined);
    assert.equal(state.receivingTables.received.range, 'scope');
    assert.equal(received.open, false);
    context.resetReceivingTableFilters();
    assert.equal(Object.values(state.receivingTables).every(table => Object.keys(table.filters).length === 0 && table.page === 1 && table.range === 'scope'), true);
});
