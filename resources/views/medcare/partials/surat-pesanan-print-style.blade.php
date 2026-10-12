/* Keep the preview and the A4 print layout at the same dimensions. */
body { font-size: 10pt; line-height: 1.2; }
.order-sheet,
.regular-order-sheet,
.narcotic-order-sheet,
.psychotropic-order-sheet,
.precursor-order-sheet,
.oot-order-sheet {
    min-height: 275mm;
    padding: 7mm 8mm 10mm;
}
main:last-of-type { page-break-after: auto; break-after: auto; }
.document-header h1 { font-size: 12pt; }
.document-number { font-size: 10.5pt; }
.section { margin-top: 3.5mm; }
.section.compact { margin-top: 2.2mm; }
.section > p { margin-bottom: 1mm; }
.field-table td { padding: .4mm 0; }
.medicine-table { margin-top: 2mm; font-size: 9pt; line-height: 1.2; }
.medicine-table th,
.medicine-table td { padding: 1.2mm 1.5mm; }
.medicine-table th { font-size: 9pt; }
.medicine-table th.number-column { white-space: nowrap; }
.medicine-name { font-size: 9.25pt; line-height: 1.2; }
.medicine-table.commercial-detail-table { font-size: 9pt; line-height: 1.2; }
.medicine-table.commercial-detail-table th,
.medicine-table.commercial-detail-table td { padding: 1.2mm 1.5mm; }
.medicine-table.commercial-detail-table th { padding-right: 1mm; padding-left: 1mm; font-size: 8.5pt; }
.medicine-table.commercial-detail-table .number-column { width: 4%; }
.medicine-table.commercial-detail-table .name-column { width: 44%; }
.medicine-table.commercial-detail-table .order-quantity-column { width: 6%; }
.medicine-table.commercial-detail-table .order-unit-column { width: 9%; }
.medicine-table.commercial-detail-table .price-column { width: 11%; white-space: normal; }
.medicine-table.commercial-detail-table .discount-column { width: 14%; }
.medicine-table.commercial-detail-table .total-price-column { width: 12%; white-space: normal; }
.medicine-table.commercial-detail-table .medicine-name,
.order-quantity-value,
.price-value,
.total-price-value { font-size: 9pt; }
.medicine-meta { margin-top: .6mm; font-size: 8.25pt; line-height: 1.2; }
.discount-tier { display: inline-block; font-size: 8.25pt; line-height: 1.2; }
.commercial-section-heading { margin: 1mm 0 1.5mm; }
.commercial-section-heading strong { font-size: 10pt; }
.order-closing {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 82mm;
    align-items: end;
    gap: 3mm;
    margin-top: 3mm;
    page-break-inside: avoid;
    break-inside: avoid;
}
.order-closing .signature-wrap { grid-column: 2; grid-row: 1; margin-top: 0; }
.order-closing .signature { width: 100%; font-size: 9.5pt; }
.order-closing .signature p { margin-bottom: 1.2mm; }
.order-closing .signature-space { height: 20mm; }
.order-closing .signature-space.has-signature { height: 24mm; }
.order-closing .pharmacist-signature-image { width: 60mm; height: 22mm; }
.order-closing .signature-role { margin: .5mm 0; font-size: 9pt; }
.order-closing .notes { grid-column: 1; grid-row: 1; margin-top: 0; font-size: 9pt; }
.order-closing .notes p { margin-bottom: 1mm; }
.copy-mark { bottom: 4mm; font-size: 7.5pt; }
@media print {
    main {
        -webkit-box-decoration-break: clone;
        box-decoration-break: clone;
    }
    .medicine-table thead { display: table-header-group; }
    .field-table tr,
    .notes { page-break-inside: avoid; break-inside: avoid; }
}
