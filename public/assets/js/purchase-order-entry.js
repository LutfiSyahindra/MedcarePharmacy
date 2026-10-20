(function (root) {
    'use strict';

    const moneyFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
    const fractionalMoneyFormatter = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const roundMoney = value => Math.round((Math.max(0, Number(value) || 0) + Number.EPSILON) * 100) / 100;
    const percentage = value => roundMoney(Math.min(100, Math.max(0, Number(value) || 0)));

    function formatMoney(value) {
        const amount = roundMoney(value);
        return 'Rp ' + (Number.isInteger(amount) ? moneyFormatter : fractionalMoneyFormatter).format(amount);
    }

    function moneyInput(value, caret = String(value).length) {
        const raw = String(value);
        const clean = raw.replace(/^Rp\s*/i, '').replace(/\./g, '').replace(/\s/g, '');
        if (clean === '') return { display: '', amount: '', caret: 0 };
        // Keep invalid pasted prices visible so validation can explain the problem.
        if (!/^\d*(?:,\d{0,2})?$/.test(clean) || clean === ',') {
            return { display: raw, amount: '', caret };
        }
        const [integer, fraction] = clean.split(',');
        const digits = (integer || '0').replace(/^0+(?=\d)/, '');
        const grouped = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        const display = 'Rp ' + grouped + (fraction === undefined ? '' : ',' + fraction);
        const amount = digits + (fraction ? '.' + fraction : '');
        let remaining = (raw.slice(0, caret).match(/[\d,]/g) || []).length;
        remaining -= Math.max(0, integer.length - digits.length);
        let position = 3;
        while (position < display.length && remaining > 0) {
            if (/[\d,]/.test(display[position])) remaining--;
            position++;
        }
        return { display, amount, caret: position };
    }

    function calculateAmounts({ qty, price, conversion = 1, discounts = [], tax = 11 }) {
        const unitPrice = roundMoney(Number(price) * Number(conversion));
        const rawGross = Math.max(0, Number(qty) || 0) * unitPrice;
        const gross = roundMoney(rawGross);
        const net = roundMoney(discounts.reduce((amount, discount) => amount * (1 - percentage(discount) / 100), rawGross));
        const subtotal = roundMoney(net * (1 + percentage(tax) / 100));
        return { unitPrice, gross, discount: roundMoney(gross - net), net, tax: roundMoney(subtotal - net), subtotal };
    }

    function summarizeAmounts(rows, additionalCost = 0) {
        const sums = { gross: 0, discount: 0, tax: 0, subtotal: 0 };
        rows.forEach(row => Object.keys(sums).forEach(key => { sums[key] += row[key]; }));
        Object.keys(sums).forEach(key => { sums[key] = roundMoney(sums[key]); });
        return { ...sums, additionalCost: roundMoney(additionalCost), total: roundMoney(sums.subtotal + roundMoney(additionalCost)) };
    }

    function splitBatches(ids, size = 250) {
        const unique = Array.from(new Set(ids.map(String).filter(Boolean)));
        const batches = [];
        for (let offset = 0; offset < unique.length; offset += size) {
            batches.push(unique.slice(offset, offset + size));
        }
        return batches;
    }

    function parsePaste(raw, catalog, existingIds = []) {
        const byCode = new Map();
        catalog.forEach(medicine => {
            const code = String(medicine.kode_obat || '').trim().toLocaleLowerCase('id-ID');
            byCode.set(code, byCode.has(code) ? null : medicine);
        });
        const existing = new Set(existingIds.map(String));
        const seen = new Set();
        const number = value => /^\d+(?:[.,]\d+)?$/.test(value) ? Number(value.replace(',', '.')) : NaN;

        return String(raw).replace(/\r/g, '').split('\n')
            .map((line, index) => ({ line, lineNumber: index + 1 }))
            .filter(item => item.line.trim() !== '')
            .map(({ line, lineNumber }) => {
                const columns = line.split('\t').map(value => value.trim());
                const [code, qtyText = '', priceText = ''] = columns;
                const medicine = byCode.get(code.toLocaleLowerCase('id-ID'));
                const qty = number(qtyText);
                const price = priceText === '' ? Number(medicine?.harga_beli || 0) : number(priceText);
                const errors = [];
                if (columns.length < 2 || columns.length > 3) errors.push('Gunakan 2 atau 3 kolom yang dipisahkan Tab.');
                if (!medicine) errors.push(byCode.has(code.toLocaleLowerCase('id-ID')) ? 'Kode obat tidak unik di master.' : 'Kode obat tidak ditemukan.');
                if (!Number.isFinite(qty) || qty < 1) errors.push('Qty harus berupa angka minimal 1.');
                if (!Number.isFinite(price) || price < 0) errors.push('Harga harus berupa angka minimal 0.');
                if (medicine && existing.has(String(medicine.id))) errors.push('Obat sudah ada dalam PO.');
                if (medicine && seen.has(String(medicine.id))) errors.push('Obat berulang dalam data tempel.');
                if (medicine) seen.add(String(medicine.id));
                return { lineNumber, code, medicine, qty, price, errors };
            });
    }

    function rowIssues(item, draft = false) {
        const issues = [];
        if (!item.medicineId) issues.push('Pilih obat.');
        if (item.medicineId && item.unitLoading) issues.push('Satuan belum selesai dimuat.');
        if (item.medicineId && !item.unitLoading && !(Number(item.unitId) > 0) && (!draft || String(item.unitId) !== '0')) issues.push('Lengkapi satuan konversi.');
        if (item.qty === '' || !Number.isFinite(Number(item.qty)) || Number(item.qty) < 1) issues.push('Qty minimal 1.');
        if (item.price === '' || !Number.isFinite(Number(item.price)) || Number(item.price) < 0) issues.push('Harga minimal 0.');
        if (item.adjustments.some(value => value === '' || !Number.isFinite(Number(value)) || Number(value) < 0 || Number(value) > 100)) issues.push('Diskon dan PPN harus antara 0–100%.');
        return issues;
    }

    function formPayload(fields, saveAsDraft) {
        const payload = {};
        fields.forEach(({ name, value }) => {
            if (name.endsWith('[]')) {
                const key = name.slice(0, -2);
                (payload[key] ||= []).push(value);
            } else {
                payload[name] = value;
            }
        });
        payload.save_as_draft = saveAsDraft;
        return payload;
    }

    root.PurchaseOrderEntry = Object.freeze({ formatMoney, moneyInput, calculateAmounts, summarizeAmounts, splitBatches, parsePaste, rowIssues, formPayload });
})(globalThis);
