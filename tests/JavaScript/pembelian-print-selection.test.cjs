const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname,
    '../../resources/views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'), 'utf8');
const script = source.slice(source.indexOf('<script>') + 8, source.lastIndexOf('</script>'))
    .replace(/\{\{\s*route\("([^"]+)"[^}]*\)\s*\}\}/g, (_, route) => `/${route}/:id`)
    .replace(/\{\{[\s\S]*?\}\}/g, '0');

function printSelection({ blocked = false } = {}) {
    const button = {
        disabled: true,
        handlers: {},
        on(event, callback) { this.handlers[event] = callback; return this; },
        prop(name, value) {
            if (value === undefined) return this[name];
            this[name] = value;
            return this;
        },
        attr(name, value) { this[name] = value; return this; },
        removeClass() { return this; },
        addClass() { return this; },
        find() { return this; },
        text(value) { this.label = value; return this; },
    };
    const modal = { handlers: {}, on(event, callback) { this.handlers[event] = callback; } };
    const dialogs = [];
    const opened = [];
    const validation = [];
    const context = vm.createContext({
        detailPurchaseOrderId: null,
        detailPrintDocuments: [],
        window: {
            open(url, target) {
                const popup = blocked ? null : { opener: 'parent' };
                opened.push({ url, target, popup });
                return popup;
            },
        },
        Swal: {
            fire(options) { dialogs.push(options); },
            showValidationMessage(message) { validation.push(message); },
        },
        $(selector) {
            if (selector === '#btnPrintPDF' || selector === button) return button;
            if (selector === '#pembelianModalDetail') return modal;
            throw new Error(`Unexpected selector: ${selector}`);
        },
    });
    const start = script.indexOf("        $('#pembelianModalDetail').on('hidden.bs.modal'");
    const end = script.indexOf('        // =================== Inisiasi Event Handler', start);
    vm.runInContext(script.slice(start, end), context);
    const documentsStart = script.indexOf('                    const regularCount =');
    const documentsEnd = script.indexOf('                    // Header', documentsStart);
    vm.runInContext(`function loadPrintDocuments(po) {
        detailPurchaseOrderId = po.id;
        ${script.slice(documentsStart, documentsEnd)}
    }`, context);

    return {
        button, dialogs, opened, validation,
        load(counts) { context.loadPrintDocuments({ id: 42, ...counts }); },
        click() { button.handlers.click.call(button); },
        close() { modal.handlers['hidden.bs.modal'](); },
    };
}

test('purchase order script parses after Blade routes are resolved', () => {
    assert.doesNotThrow(() => new vm.Script(script));
});

test('Prekursor and OOT are both offered and confirming opens only the selected letter', () => {
    for (const selectedType of ['Prekursor', 'OOT']) {
        const ui = printSelection();
        ui.load({ precursor_item_count: 2, oot_item_count: 1 });
        ui.click();

        assert.equal(ui.opened.length, 0);
        const dialog = ui.dialogs[0];
        assert.equal(dialog.input, 'select');
        assert.equal(dialog.showCancelButton, true);
        assert.equal(dialog.target, '#pembelianModalDetail');
        assert.deepEqual(Object.values(dialog.inputOptions), ['Prekursor (2 item)', 'OOT (1 item)']);
        const selectedIndex = Object.keys(dialog.inputOptions)
            .find(index => dialog.inputOptions[index].startsWith(selectedType));
        dialog.preConfirm(selectedIndex);

        assert.equal(ui.opened.length, 1);
        assert.equal(ui.opened[0].url,
            `/pembelian.suratPesanan${selectedType === 'OOT' ? 'Oot' : selectedType}/42`);
        assert.equal(ui.opened[0].target, '_blank');
        assert.equal(ui.opened[0].popup.opener, null);
    }
});

test('all available SP types remain selectable when OOT is present', () => {
    const ui = printSelection();
    ui.load({ regular_item_count: 1, narcotic_item_count: 1, psychotropic_item_count: 1,
        precursor_item_count: 1, oot_item_count: 1 });
    ui.click();
    assert.equal(ui.button.disabled, false);
    assert.deepEqual(Object.values(ui.dialogs[0].inputOptions), [
        'Reguler (1 item)', 'Narkotika (1 item)', 'Psikotropika (1 item)',
        'Prekursor (1 item)', 'OOT (1 item)',
    ]);
    assert.equal(ui.opened.length, 0);
});

test('one SP type opens directly without a selection dialog', () => {
    const ui = printSelection();
    ui.load({ oot_item_count: 3 });
    ui.click();
    assert.equal(ui.dialogs.length, 0);
    assert.equal(ui.opened.length, 1);
    assert.equal(ui.opened[0].url, '/pembelian.suratPesananOot/42');
});

test('an empty selection keeps the chooser open without printing', () => {
    const ui = printSelection();
    ui.load({ regular_item_count: 1, precursor_item_count: 1 });
    ui.click();
    assert.equal(ui.dialogs[0].preConfirm(''), false);
    assert.equal(ui.opened.length, 0);
    assert.ok(ui.validation[0].includes('Pilih jenis'));
});

test('closing the detail modal resets the print state for the next PO', () => {
    const ui = printSelection();
    ui.load({ precursor_item_count: 1, oot_item_count: 1 });
    ui.close();
    ui.click();
    assert.equal(ui.button.disabled, true);
    assert.equal(ui.dialogs.length, 0);
    assert.equal(ui.opened.length, 0);
    ui.load({ regular_item_count: 2 });
    ui.click();
    assert.equal(ui.opened[0].url, '/pembelian.suratPesananReguler/42');
});

test('a PO without printable items cannot open a letter or chooser', () => {
    const ui = printSelection();
    ui.load({});
    ui.click();
    assert.equal(ui.button.disabled, true);
    assert.equal(ui.dialogs.length, 0);
    assert.equal(ui.opened.length, 0);
});

test('a blocked popup keeps the chooser open and explains how to retry', () => {
    const ui = printSelection({ blocked: true });
    ui.load({ precursor_item_count: 1, oot_item_count: 1 });
    ui.click();
    assert.equal(ui.dialogs[0].preConfirm('1'), false);
    assert.equal(ui.opened.length, 1);
    assert.ok(ui.validation[0].includes('Izinkan pop-up'));
});

test('a blocked popup for a single SP shows a warning', () => {
    const ui = printSelection({ blocked: true });
    ui.load({ oot_item_count: 1 });
    ui.click();
    assert.equal(ui.dialogs[0].icon, 'warning');
    assert.ok(ui.dialogs[0].text.includes('Izinkan pop-up'));
});
