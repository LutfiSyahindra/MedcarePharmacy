<style>
    .nota-page {
        --receipt-primary: #0b7b68;
        --receipt-primary-dark: #075c4f;
    }

    .nota-page .document-command {
        background:
            radial-gradient(circle at 82% -20%, rgba(121, 232, 206, .42), transparent 30%),
            radial-gradient(circle at 50% 150%, rgba(25, 166, 125, .4), transparent 42%),
            linear-gradient(126deg, #123f4a 0%, #087a62 57%, #18a17d 100%);
        box-shadow: 0 18px 46px rgba(8, 113, 91, .2);
    }

    .nota-page .document-command-art span { color: var(--receipt-primary); }
    .nota-page .document-command-art .is-middle { color: #5267dc; }
    .nota-page .document-command-art .is-front { color: #bd7518; }
    .nota-page .document-heading-icon,
    .nota-page .document-template-title > span { color: var(--receipt-primary); background: #e8f7f3; }
    .nota-page .document-refresh-button:hover { color: var(--receipt-primary); border-color: #a9d8cc; background: #f1faf7; }
    .nota-page .document-total-card { background: linear-gradient(135deg, #075c4f, #0b8c74); box-shadow: 0 9px 22px rgba(8, 127, 103, .18); }
    .nota-page .document-type-tabs button:hover,
    .nota-page .document-type-tabs button.is-active { color: var(--receipt-primary); border-color: #afd9cf; background: #edf9f6; }
    .nota-page .document-type-tabs button.is-active > span:last-child:not(.type-dot) { color: var(--receipt-primary); background: #dff3ee; }
    .nota-page .document-table-footer .page-item.active .page-link { border-color: var(--receipt-primary); background: var(--receipt-primary); }
    .nota-page .document-action-button:hover { color: var(--receipt-primary); border-color: #afd9cf; background: #f1faf7; }
    .nota-page .document-action-button.is-print { border-color: var(--receipt-primary); background: var(--receipt-primary); }
    .nota-page .document-action-button.is-print:hover { color: #fff; background: var(--receipt-primary-dark); }

    #toggleReceiptTemplates {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 36px;
        padding: 7px 11px;
        border: 1px solid #d9e0ed;
        border-radius: 9px;
        color: #536178;
        background: #fff;
        font-size: 10px;
        font-weight: 800;
    }

    #toggleReceiptTemplates:hover { color: var(--receipt-primary); border-color: #afd9cf; background: #f1faf7; }
    #toggleReceiptTemplates i { transition: transform .22s ease; }
    #toggleReceiptTemplates[aria-expanded="true"] i { transform: rotate(180deg); }
    #resetReceiptFilters { flex: 0 0 auto; border: 0; color: var(--receipt-primary); background: transparent; font-size: 9px; font-weight: 800; }

    .receipt-customer-cell,
    .receipt-content-cell,
    .receipt-total-cell { display: grid; min-width: 145px; gap: 3px; }
    .receipt-customer-cell strong,
    .receipt-content-cell strong,
    .receipt-total-cell strong { color: #334057; font-size: 10px; }
    .receipt-customer-cell small,
    .receipt-content-cell small,
    .receipt-total-cell small { max-width: 190px; overflow: hidden; color: #8a95a7; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
    .receipt-total-cell { min-width: 105px; }
    .receipt-total-cell strong { color: var(--receipt-primary-dark); font-size: 11px; }
    .receipt-payment-badge { display: inline-flex; width: max-content; padding: 2px 6px; border-radius: 99px; color: #087a5b; background: #e8f8f2; font-size: 8px; font-weight: 800; }
    .receipt-payment-badge.is-credit { color: #9b651a; background: #fff4df; }

    @media (max-width: 1199.98px) {
        .nota-page .document-template-grid { grid-template-columns: repeat(3, minmax(170px, 1fr)); }
    }

    @media (max-width: 767.98px) {
        .nota-page .document-template-grid { grid-template-columns: 1fr; }
    }
</style>
