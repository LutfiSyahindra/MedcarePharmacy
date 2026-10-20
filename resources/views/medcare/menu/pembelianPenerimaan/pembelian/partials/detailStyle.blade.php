<style>
    .purchase-order-detail .modal-dialog {
        width: calc(100% - 3rem);
        max-width: 1440px;
        height: calc(100% - 3.5rem);
        margin: 1.75rem auto;
    }

    .purchase-order-detail .modal-dialog-scrollable .modal-content { max-height: 100%; }
    .purchase-order-detail .modal-content { border-radius: 20px; }
    .purchase-order-detail .modal-header { padding: 1.25rem 1.5rem; background: #fff; border-bottom: 1px solid var(--purchase-border); }
    .purchase-order-detail .modal-title-icon { color: var(--purchase-primary); background: var(--purchase-soft); border-radius: 12px; }
    .purchase-order-detail .modal-title { color: var(--purchase-text); font-size: 1.05rem; }
    .purchase-order-detail .modal-subtitle { color: var(--purchase-muted); font-size: .8rem; }
    .purchase-order-detail .btn-close { filter: none; margin-top: .2rem; }
    .purchase-order-detail .modal-body { padding: 1.5rem; background: #f5f8fa; max-height: none !important; }
    .purchase-order-detail .modal-footer { justify-content: space-between; gap: .75rem; padding: 1rem 1.5rem; }
    .purchase-order-detail .modal-footer .btn { min-height: 42px; border-radius: 10px; font-size: .8rem; }
    .purchase-detail-overview { margin-bottom: 1.25rem; padding: 1.5rem; border: 1px solid var(--purchase-border); border-radius: 16px; background: #fff; }
    .purchase-detail-reference { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1.25rem; }
    .purchase-detail-reference > div { min-width: 0; }
    .purchase-detail-eyebrow { display: block; color: var(--purchase-muted); font-size: .65rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    .purchase-detail-reference h3 { margin: .4rem 0 0; color: var(--purchase-text); font-size: clamp(1.25rem, 2vw, 1.65rem); font-weight: 800; letter-spacing: -.035em; overflow-wrap: anywhere; }
    .purchase-detail-reference .purchase-status { flex-shrink: 0; }
    .purchase-detail-metadata { display: grid; grid-template-columns: 1.25fr 1fr 1fr; gap: 1.5rem; margin: 0; padding-top: 1.25rem; border-top: 1px solid #edf1f5; }
    .purchase-detail-metadata > div { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .purchase-detail-meta-icon { display: grid; width: 40px; height: 40px; flex: 0 0 40px; place-items: center; border-radius: 10px; color: #63758b; background: #f3f6f9; font-size: 1.2rem; }
    .purchase-detail-metadata dt { margin-bottom: .25rem; color: var(--purchase-muted); font-size: .72rem; font-weight: 500; }
    .purchase-detail-metadata dd { margin: 0; color: var(--purchase-text); font-size: .84rem; font-weight: 700; overflow-wrap: anywhere; }
    .purchase-detail-layout { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 1.25rem; align-items: start; }
    .purchase-detail-main { min-width: 0; }
    .purchase-detail-surface { border: 1px solid var(--purchase-border); border-radius: 16px; background: #fff; box-shadow: 0 4px 16px rgba(25, 45, 65, .025); }
    .purchase-detail-surface-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.25rem; }
    .purchase-order-detail h6 { margin: .3rem 0 0; color: var(--purchase-text); font-size: .95rem; font-weight: 800; }
    .purchase-detail-item-count { display: inline-flex; align-items: center; gap: .35rem; padding: .4rem .65rem; border-radius: 8px; color: var(--purchase-primary-strong); background: var(--purchase-soft); font-size: .74rem; font-weight: 700; white-space: nowrap; }
    .purchase-detail-toolbar { display: flex; align-items: flex-end; gap: .75rem; padding: 0 1.25rem; }
    .purchase-detail-search { flex: 1; min-width: 0; }
    .purchase-detail-search label { display: block; margin-bottom: .45rem; color: var(--purchase-text); font-size: .75rem; font-weight: 600; }
    .purchase-detail-toolbar .btn { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; min-height: 44px; padding: .5rem .7rem; border: 1px solid var(--purchase-border); border-radius: 10px; background: #fff; font-size: .72rem; white-space: nowrap; }
    .purchase-detail-toolbar .btn:disabled { opacity: .45; }
    .purchase-detail-search .select2-container .select2-selection--single { height: 44px; min-height: 44px; border: 1px solid #dce4eb; border-radius: 10px; }
    .purchase-detail-search .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left: .8rem; padding-right: 2.8rem; color: var(--purchase-text); font-size: .8rem; line-height: 42px; }
    .purchase-detail-search .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px; right: 8px; }
    .purchase-detail-search .select2-container--default .select2-selection--single .select2-selection__clear { margin-right: 1rem; color: var(--purchase-muted); }
    .purchase-detail-search .select2-container--focus .select2-selection--single,
    .purchase-detail-search .select2-container--open .select2-selection--single { border-color: var(--purchase-primary); box-shadow: 0 0 0 3px rgba(15, 118, 110, .1); }
    .purchase-order-detail .select2-dropdown { border-color: #dce4eb; border-radius: 10px; box-shadow: 0 14px 32px rgba(25, 45, 65, .12); overflow: hidden; font-size: .8rem; }
    .purchase-order-detail .select2-dropdown .select2-search--dropdown { padding: .65rem; }
    .purchase-order-detail .select2-dropdown .select2-search__field { min-height: 38px; border: 1px solid #dce4eb !important; border-radius: 7px; padding: .5rem !important; }
    .purchase-order-detail .select2-dropdown .select2-results__option { padding: .7rem .85rem; overflow-wrap: anywhere; }
    .purchase-order-detail .select2-dropdown .select2-results__option--highlighted[aria-selected] { color: #fff; background: #0f766e; }
    .purchase-detail-results { padding: .75rem 1.25rem; color: var(--purchase-muted); font-size: .7rem; }
    .purchase-detail-table-wrap { border-radius: 0 0 16px 16px; }
    .purchase-order-detail .purchase-detail-table { width: 100%; min-width: 810px; margin: 0; }
    .purchase-order-detail .purchase-detail-table thead th { padding: .85rem .7rem; border-top: 1px solid #edf1f5; border-bottom: 1px solid var(--purchase-border); background: #f7f9fb; font-size: .63rem; letter-spacing: .025em; }
    .purchase-order-detail .purchase-detail-table thead th:first-child { padding-left: 1.25rem; }
    .purchase-order-detail .purchase-detail-table thead th:last-child { padding-right: 1.25rem; }
    .purchase-order-detail .purchase-detail-table tbody td { padding: 1rem .7rem; border-color: #edf1f5; color: #475569; font-size: .76rem; font-variant-numeric: tabular-nums; }
    .purchase-order-detail .purchase-detail-table tbody tr:last-child td { border-bottom: 0; }
    .purchase-order-detail .purchase-detail-table tbody tr:hover { background: #fafdfc; }
    .purchase-order-detail .purchase-detail-table tbody td:first-child { min-width: 200px; padding-left: 1.25rem; }
    .purchase-order-detail .purchase-detail-table tbody td:last-child { padding-right: 1.25rem; color: var(--purchase-text); font-weight: 800; }
    .purchase-detail-medicine strong { display: block; color: var(--purchase-text); font-size: .8rem; font-weight: 700; white-space: normal; line-height: 1.5; }
    .purchase-detail-medicine small { display: block; margin-top: .2rem; color: var(--purchase-muted); font-size: .67rem; }
    .purchase-detail-classifications { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }
    .purchase-order-detail .purchase-sp-badge { padding: .2rem .4rem; border: 0; border-radius: 5px; font-size: .6rem; font-weight: 600; }
    .purchase-detail-unit { display: inline-block; padding: .25rem .45rem; border-radius: 6px; color: #526176; background: #f1f5f9; font-size: .7rem; }
    .purchase-detail-money, .purchase-detail-line-total { white-space: nowrap; }
    .purchase-detail-discounts { display: flex; gap: .3rem; font-size: .7rem; white-space: nowrap; }
    .purchase-detail-discount-divider { color: #c4ced8; }
    .purchase-detail-note { display: flex; gap: .75rem; margin-top: 1rem; padding: 1.1rem 1.25rem; border: 1px solid var(--purchase-border); border-radius: 14px; background: #fff; }
    .purchase-detail-note > i { color: var(--purchase-muted); font-size: 1.2rem; }
    .purchase-detail-note > div { min-width: 0; }
    .purchase-detail-note h6 { margin: 0; font-size: .78rem; }
    .purchase-detail-note p { margin: .4rem 0 0; color: var(--purchase-muted); font-size: .78rem; line-height: 1.65; white-space: pre-wrap; overflow-wrap: anywhere; }
    .purchase-detail-cost-summary { position: sticky; top: 0; padding: 1.25rem; }
    .purchase-detail-cost-summary-icon { display: grid; width: 42px; height: 42px; margin-bottom: .9rem; place-items: center; border-radius: 12px; color: var(--purchase-primary); background: var(--purchase-soft); font-size: 1.3rem; }
    .purchase-detail-cost-summary-heading p { margin: .35rem 0 0; color: var(--purchase-muted); font-size: .72rem; }
    .purchase-detail-costs { display: grid; gap: 1rem; margin: 1.25rem 0; padding-top: 1.25rem; border-top: 1px solid #edf1f5; }
    .purchase-detail-costs > div { display: flex; flex-wrap: wrap; align-items: start; justify-content: space-between; gap: .35rem .75rem; }
    .purchase-detail-costs dt { color: var(--purchase-muted); font-size: .75rem; font-weight: 500; }
    .purchase-detail-costs dt small { display: block; margin-top: .25rem; font-size: .6rem; }
    .purchase-detail-costs dd { margin: 0 0 0 auto; color: var(--purchase-text); font-size: .77rem; font-weight: 700; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .purchase-detail-costs .purchase-detail-discount-total dd { color: var(--purchase-success); }
    .purchase-detail-costs .purchase-detail-cost-subtotal { padding-top: 1rem; border-top: 1px dashed var(--purchase-border); }
    .purchase-detail-costs .purchase-detail-cost-subtotal dt { color: var(--purchase-text); font-weight: 700; }
    .purchase-detail-grand-total { padding: 1rem; border: 1px solid #cfebe4; border-radius: 12px; background: #f0faf6; }
    .purchase-detail-grand-total > span { display: block; color: #42655b; font-size: .72rem; font-weight: 600; }
    .purchase-detail-grand-total > strong { display: block; margin-top: .45rem; color: var(--purchase-primary-strong); font-size: clamp(1rem, 1.6vw, 1.4rem); font-weight: 800; letter-spacing: -.035em; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .purchase-detail-cost-summary-hint { display: flex; gap: .4rem; margin: 1rem 0 0; color: var(--purchase-muted); font-size: .67rem; line-height: 1.6; }
    .purchase-detail-cost-summary-hint i { flex-shrink: 0; font-size: .9rem; }
    .purchase-detail-empty { display: grid; justify-items: center; gap: .65rem; padding: 2.5rem 1.25rem; text-align: center; }
    .purchase-detail-empty > i { color: #8ba99f; font-size: 2rem; }
    .purchase-detail-empty strong { color: var(--purchase-text); font-size: .85rem; }
    .purchase-detail-empty > span { color: var(--purchase-muted); font-size: .75rem; }

    @media (max-width: 1199.98px) {
        .purchase-detail-layout { grid-template-columns: minmax(0, 1fr); }
        .purchase-detail-cost-summary { position: static; }
        .purchase-detail-cost-summary-heading { display: grid; grid-template-columns: 42px 1fr; column-gap: .75rem; }
        .purchase-detail-cost-summary-icon { grid-row: 1 / 3; margin: 0; }
        .purchase-detail-costs { gap: .85rem; }
        .purchase-detail-grand-total > strong { font-size: 1.5rem; }
    }

    @media (max-width: 767.98px) {
        .purchase-order-detail .modal-dialog { width: auto; height: calc(100% - 1rem); min-height: calc(100% - 1rem); margin: .5rem; }
        .purchase-order-detail .modal-dialog-scrollable .modal-content { max-height: calc(100dvh - 1rem); border-radius: 16px; }
        .purchase-order-detail .modal-header { padding: 1rem; }
        .purchase-order-detail .modal-title { font-size: .92rem; }
        .purchase-order-detail .modal-subtitle { font-size: .7rem; }
        .purchase-order-detail .modal-body { padding: .85rem; }
        .purchase-detail-overview { padding: 1rem; }
        .purchase-detail-reference { flex-wrap: wrap; gap: .7rem; padding-bottom: 1rem; }
        .purchase-detail-metadata { grid-template-columns: 1fr; gap: 1rem; padding-top: 1rem; }
        .purchase-detail-meta-icon { width: 34px; height: 34px; flex-basis: 34px; font-size: 1rem; }
        .purchase-detail-surface-heading { padding: 1rem; }
        .purchase-detail-toolbar { flex-wrap: wrap; padding: 0 1rem; }
        .purchase-detail-search { flex-basis: 100%; }
        .purchase-detail-toolbar .btn { min-height: 38px; margin-left: auto; }
        .purchase-detail-results { padding: .65rem 1rem; }
        .purchase-detail-table-wrap { padding: 0 .75rem .75rem; }
        .purchase-order-detail .purchase-detail-table { min-width: 0; }
        .purchase-order-detail .purchase-detail-table tbody tr { margin-bottom: .75rem; border-radius: 12px; }
        .purchase-order-detail .purchase-detail-table tbody tr:last-child { margin-bottom: 0; }
        .purchase-order-detail .purchase-detail-table tbody td { padding: .75rem; text-align: left !important; }
        .purchase-order-detail .purchase-detail-table tbody td:first-child { min-width: 0; padding: .85rem; background: #f8fafb; }
        .purchase-order-detail .purchase-detail-table tbody td:first-child::before { display: none; }
        .purchase-order-detail .purchase-detail-table tbody td:last-child { padding: .85rem; text-align: right !important; background: #f0faf6; color: var(--purchase-primary-strong); }
        .purchase-detail-medicine strong { font-size: .85rem; }
        .purchase-detail-unit { align-self: start; }
        .purchase-detail-note { padding: 1rem; }
        .purchase-order-detail .modal-footer { padding: .85rem; }
        .purchase-order-detail .modal-footer #btnPrintPDF { flex: 1; min-width: 0; justify-content: center; }
        .purchase-order-detail .modal-footer #btnPrintPDF span { white-space: normal; text-align: left; }
        .purchase-order-detail .modal-footer .btn { font-size: .7rem; }
    }
</style>
