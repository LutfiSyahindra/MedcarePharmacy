<style>
    /* POS focus mode: keep the transaction tools, remove visual noise. */
    .pos-simple {
        gap: 12px;
        padding: 12px;
        background: #f4f6fa;
    }

    .pos-simple::before { display: none; }

    .pos-simple .pos-appbar {
        min-height: 64px;
        grid-template-columns: minmax(190px, auto) minmax(280px, 1fr) auto;
        gap: 14px;
        overflow: visible;
        padding: 10px 14px;
        color: #26344f;
        border: 1px solid #dfe4ec;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 43, 70, .06);
    }

    .pos-simple .pos-appbar::after,
    .pos-simple .pos-catalog-hero::after,
    .pos-simple .pos-transaction-hero::before,
    .pos-simple .pos-checkout-hero::after,
    .pos-simple .pos-checkout-stage::before { display: none; }

    .pos-simple .pos-brand { gap: 9px; }
    .pos-simple .pos-brand-mark {
        width: 40px;
        height: 40px;
        color: #4f63d8;
        border: 0;
        border-radius: 10px;
        background: #eef1ff;
        box-shadow: none;
        font-size: 21px;
    }

    .pos-simple .pos-eyebrow { display: none; }
    .pos-simple .pos-brand-title { display: grid; gap: 0; }
    .pos-simple .pos-brand h1 { margin: 0; color: #1f2d46; font-size: 1.2rem; }
    .pos-simple .pos-brand-title > span { color: #8792a4; font-size: .76rem; }

    .pos-simple .pos-appbar-center { justify-content: flex-end; }
    .pos-simple .pos-session-status {
        padding: 6px 8px;
        color: #087c59;
        border-color: #cce9df;
        background: #f0faf6;
    }

    .pos-simple .pos-session-status.is-offline {
        color: #b74353;
        border-color: #f0cbd1;
        background: #fff2f4;
    }

    .pos-simple .pos-clock { color: #657187; }
    .pos-simple .pos-clock i { color: #7b87a1; }
    .pos-simple .pos-cashier-profile { border-left-color: #e2e7ef; }
    .pos-simple .pos-cashier-avatar {
        color: #4254bd;
        border-color: #dce2fb;
        border-radius: 9px;
        background: #eef1ff;
    }
    .pos-simple .pos-cashier-profile small { color: #99a2b1; }
    .pos-simple .pos-cashier-profile strong { color: #34425a; }

    .pos-simple .pos-page .pos-btn-quiet,
    .pos-simple .pos-btn-quiet {
        min-height: 38px;
        color: #4f5d73;
        border-color: #dce2eb;
        border-radius: 9px;
        background: #fff;
        backdrop-filter: none;
    }

    .pos-simple .pos-btn-quiet:hover:not(:disabled) {
        color: #3e51c0;
        border-color: #bdc7ed;
        background: #f7f8ff;
        transform: none;
    }

    .pos-simple .pos-btn-quiet:disabled {
        color: #a7afbc;
        border-color: #e7eaf0;
        background: #f7f8fa;
    }

    .pos-simple .pos-btn-quiet kbd { display: none; }
    .pos-simple > .pos-flow { display: none; }

    .pos-simple .pos-branch-button {
        min-width: 168px;
        justify-content: flex-start;
        text-align: left;
    }
    .pos-simple .pos-branch-button > span { display: grid; min-width: 0; flex: 1; line-height: 1.1; }
    .pos-simple .pos-branch-button small { color: #98a2b3; font-size: .62rem; font-weight: 700; text-transform: uppercase; }
    .pos-simple .pos-branch-button strong { overflow: hidden; color: #33425a; font-size: .74rem; white-space: nowrap; text-overflow: ellipsis; }
    .pos-simple .pos-workspace.is-branch-locked { opacity: .45; pointer-events: none; user-select: none; }

    .pos-branch-modal .modal-dialog { max-width: 500px; }
    .pos-branch-modal .modal-content { overflow: hidden; border: 0; border-radius: 18px; box-shadow: 0 24px 70px rgba(31, 43, 70, .22); }
    .pos-branch-modal .modal-header { display: flex; align-items: center; gap: 13px; padding: 22px 24px 18px; border: 0; background: linear-gradient(145deg, #f5f7ff, #fff); }
    .pos-branch-modal-icon { display: grid; width: 48px; height: 48px; flex: 0 0 48px; place-items: center; color: #4256c5; border-radius: 14px; background: #e9edff; font-size: 24px; }
    .pos-branch-modal .modal-header small { color: #6677c5; font-size: .65rem; font-weight: 800; letter-spacing: .08em; }
    .pos-branch-modal .modal-title { margin: 2px 0 3px; color: #22304a; font-size: 1.25rem; font-weight: 800; }
    .pos-branch-modal .modal-header p { margin: 0; color: #7a869a; font-size: .78rem; }
    .pos-branch-modal .modal-body { padding: 20px 24px; }
    .pos-branch-modal .form-label { margin-bottom: 7px; color: #46536a; font-size: .76rem; font-weight: 700; }
    .pos-branch-modal .form-select { min-height: 48px; border-color: #ccd4e2; border-radius: 10px; color: #2d3b53; font-weight: 600; }
    .pos-branch-modal .form-select:focus { border-color: #7b8be1; box-shadow: 0 0 0 3px rgba(79, 99, 216, .1); }
    .pos-branch-modal-help { display: flex; align-items: center; gap: 6px; margin: 10px 0 0; color: #7f8a9c; font-size: .72rem; }
    .pos-branch-modal-help i { color: #15916a; font-size: 16px; }
    .pos-branch-modal .modal-footer { padding: 14px 24px 20px; border: 0; }
    .pos-confirm-branch { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; gap: 8px; padding: 0 18px; color: #fff; border: 0; border-radius: 10px; background: #4f63d8; font-size: .8rem; font-weight: 750; }
    .pos-confirm-branch:hover { color: #fff; background: #4053c2; }
    .pos-confirm-branch:disabled { color: #a5adbb; background: #edf0f4; }
    .pos-branch-empty { display: grid; justify-items: center; gap: 6px; padding: 18px; color: #7b8698; text-align: center; border: 1px dashed #d7dde7; border-radius: 12px; background: #fafbfc; }
    .pos-branch-empty > i { color: #d38a24; font-size: 36px; }
    .pos-branch-empty strong { color: #36445c; }
    .pos-branch-empty span { font-size: .76rem; }

    .pos-receipt-modal .modal-dialog { width: min(920px, calc(100% - 24px)); max-width: 920px; }
    .pos-receipt-modal .modal-content { overflow: hidden; border: 0; border-radius: 18px; box-shadow: 0 24px 70px rgba(31, 43, 70, .24); }
    .pos-receipt-modal .modal-header { display: flex; align-items: center; gap: 12px; padding: 16px 19px; border-color: #e5e9f0; background: #fff; }
    .pos-receipt-modal-icon { display: grid; width: 43px; height: 43px; flex: 0 0 43px; place-items: center; color: #168262; border-radius: 12px; background: #e9f8f3; font-size: 22px; }
    .pos-receipt-modal .modal-header > div { min-width: 0; flex: 1; }
    .pos-receipt-modal .modal-header small { color: #15916a; font-size: .62rem; font-weight: 800; letter-spacing: .08em; }
    .pos-receipt-modal .modal-title { margin: 1px 0 2px; color: #27364f; font-size: 1.08rem; font-weight: 800; }
    .pos-receipt-modal .modal-header p { margin: 0; color: #8490a2; font-size: .74rem; }
    .pos-receipt-document-tabs {
        display: inline-flex;
        flex: 0 0 auto !important;
        align-items: center;
        gap: 4px;
        padding: 4px;
        border: 1px solid #dce3e9;
        border-radius: 10px;
        background: #f4f7f8;
    }
    .pos-receipt-document-tabs button {
        display: inline-flex;
        min-height: 32px;
        align-items: center;
        gap: 6px;
        padding: 0 10px;
        color: #687786;
        border: 0;
        border-radius: 7px;
        background: transparent;
        font-size: .7rem;
        font-weight: 750;
    }
    .pos-receipt-document-tabs button.is-active { color: #fff; background: #31506a; box-shadow: 0 4px 10px rgba(36, 61, 82, .16); }
    .pos-receipt-document-tabs button[data-receipt-document="labels"].is-active { background: #0c8c6a; }
    .pos-receipt-modal .modal-body { height: min(68vh, 720px); padding: 0; background: #eef1f5; }
    .pos-receipt-frame-shell { position: relative; width: 100%; height: 100%; }
    .pos-receipt-frame-shell iframe { width: 100%; height: 100%; border: 0; background: #f5f6f8; }
    .pos-receipt-loading { position: absolute; z-index: 2; inset: 0; display: grid; align-content: center; justify-items: center; gap: 8px; color: #69758a; background: #f4f6f9; font-size: .78rem; }
    .pos-receipt-loading i { color: #4f63d8; font-size: 28px; }
    .pos-receipt-loading.is-hidden { display: none; }
    .pos-receipt-modal .modal-footer { gap: 8px; padding: 12px 18px; border-color: #e5e9f0; }
    .pos-receipt-close,
    .pos-receipt-print { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 7px; padding: 0 16px; border-radius: 9px; font-size: .78rem; font-weight: 750; }
    .pos-receipt-close { color: #536078; border: 1px solid #d7dde7; background: #fff; }
    .pos-receipt-print { color: #fff; border: 0; background: #4f63d8; }
    .pos-receipt-print:hover { color: #fff; background: #4053c2; }
    .pos-receipt-print:disabled { color: #a5adbb; background: #e9edf3; }

    .pos-simple .pos-workspace {
        grid-template-columns: minmax(310px, 350px) minmax(0, 1fr);
        gap: 12px;
    }

    .pos-simple .pos-catalog { top: 12px; }
    .pos-simple .pos-surface,
    .pos-simple .pos-shortcut-card,
    .pos-simple .pos-panel,
    .pos-simple .pos-toolbar {
        border-color: #dfe4ec;
        border-radius: 13px;
        box-shadow: 0 4px 14px rgba(31, 43, 70, .05);
    }

    .pos-simple .pos-product-panel {
        display: block;
        overflow: visible;
        background: #fff;
    }

    .pos-simple .pos-catalog-hero,
    .pos-simple .pos-transaction-hero,
    .pos-simple .pos-checkout-hero {
        color: #26344f;
        background: #fff;
    }

    .pos-simple .pos-catalog-hero {
        min-height: 64px;
        padding: 12px 13px;
        border-bottom: 1px solid #e7eaf0;
        border-radius: 13px 13px 0 0;
    }

    .pos-simple .pos-catalog-hero-main { gap: 9px; }
    .pos-simple .pos-catalog-step,
    .pos-simple .pos-transaction-number,
    .pos-simple .pos-checkout-step {
        width: 34px;
        height: 34px;
        color: #4256c5;
        border: 0;
        border-radius: 50%;
        background: #eef1ff;
        box-shadow: none;
        font-size: .84rem;
    }

    .pos-simple .pos-catalog-hero .pos-section-kicker,
    .pos-simple .pos-catalog-hero p,
    .pos-simple .pos-catalog-ready,
    .pos-simple .pos-catalog-rail { display: none; }
    .pos-simple .pos-catalog-hero h2 { margin: 0; color: #26344f; font-size: 1rem; }

    .pos-simple .pos-search-box {
        margin: 0;
        padding: 13px;
        border: 0;
        border-radius: 0;
        background: #fff;
        box-shadow: none;
        transform: none;
    }

    .pos-simple .pos-search-box::before,
    .pos-simple .pos-product-card.has-product::after { display: none; }

    .pos-simple .pos-search-topline { display: none; }
    .pos-simple .pos-search-title { margin-bottom: 7px; color: #34425a; }
    .pos-simple .pos-search-title small { margin-top: 1px; color: #929baa; }
    .pos-simple .pos-search-control .select2-container--default .select2-selection--single {
        height: 48px;
        min-height: 48px;
        border-color: #cfd6e3;
        border-radius: 10px;
        box-shadow: none;
    }
    .pos-simple .pos-search-control .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 46px; }
    .pos-simple .pos-search-leading { left: 13px; }
    .pos-simple .pos-search-ready { right: 11px; }
    .pos-simple .pos-search-feedback { margin-top: 7px; }
    .pos-simple .pos-search-feedback p { color: #7c8799; }
    .pos-simple .pos-search-scope { display: none; }

    .pos-simple .pos-selection-block,
    .pos-simple .pos-config-block,
    .pos-simple .pos-stock-block { display: none; }

    .pos-simple .pos-selection-block.has-selection,
    .pos-simple .pos-config-block.has-selection,
    .pos-simple .pos-stock-block.has-selection { display: block; }

    .pos-simple .pos-catalog-block {
        margin: 0 13px 10px;
        border-color: #e2e6ed;
        border-radius: 10px;
        box-shadow: none;
    }

    .pos-simple .pos-selection-block .pos-catalog-block-head { display: none; }
    .pos-simple .pos-product-card { padding: 11px; }
    .pos-simple .pos-selection-block .pos-product-card.has-product { background: #fff; }
    .pos-simple .pos-product-name { align-items: flex-start; }
    .pos-simple .pos-product-name .badge {
        color: #087a58 !important;
        border: 1px solid #c6e7db;
        background: #eef9f5 !important;
    }

    .pos-simple .pos-product-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 8px;
    }

    .pos-simple .pos-product-summary span {
        padding: 3px 7px;
        color: #647188;
        border: 1px solid #e1e5ec;
        border-radius: 999px;
        background: #f8f9fb;
        font-size: .74rem;
    }

    .pos-simple .pos-product-more { margin-top: 9px; border-top: 1px solid #edf0f4; }
    .pos-simple .pos-product-more > summary {
        display: flex;
        align-items: center;
        gap: 5px;
        padding-top: 8px;
        color: #5d6b82;
        cursor: pointer;
        list-style: none;
        font-size: .76rem;
        font-weight: 600;
    }
    .pos-simple .pos-product-more > summary::-webkit-details-marker { display: none; }
    .pos-simple .pos-product-more > summary i:last-child { margin-left: auto; transition: transform .2s ease; }
    .pos-simple .pos-product-more[open] > summary i:last-child { transform: rotate(180deg); }
    .pos-simple .pos-product-more .pos-product-meta { margin-top: 8px; }
    .pos-simple .pos-product-clinical { margin-top: 8px; color: #778397; font-size: .75rem; line-height: 1.55; }

    .pos-simple .pos-catalog-block-head {
        min-height: 42px;
        padding: 7px 9px;
        background: #fafbfc;
    }
    .pos-simple .pos-catalog-block-icon { width: 28px; height: 28px; }
    .pos-simple .pos-block-number { display: none; }
    .pos-simple .pos-add-card { padding: 10px; }
    .pos-simple .pos-add-button {
        min-height: 42px;
        border-radius: 9px;
        background: #4f63d8;
        box-shadow: none;
    }
    .pos-simple .pos-add-button:hover:not(:disabled) { background: #4053c2; box-shadow: none; transform: none; }
    .pos-simple .pos-add-button > span:nth-child(2) { display: block; }

    .pos-simple .pos-stock-block > summary { cursor: pointer; list-style: none; }
    .pos-simple .pos-stock-block > summary::-webkit-details-marker { display: none; }
    .pos-simple .pos-stock-block > summary::after {
        margin-left: 2px;
        color: #8a95a7;
        content: "\F0140";
        font-family: "Material Design Icons";
        font-size: 16px;
        transition: transform .2s ease;
    }
    .pos-simple .pos-stock-block[open] > summary::after { transform: rotate(180deg); }
    .pos-simple .pos-stock-block .pos-fefo-badge { margin-left: auto; }
    .pos-simple .pos-shortcut-card { display: none; }

    .pos-simple .pos-transaction-panel { overflow: hidden; padding: 0; background: #fff; }
    .pos-simple .pos-transaction-orbit { display: none; }
    .pos-simple .pos-transaction-hero {
        padding: 12px 14px;
        border-bottom: 1px solid #e7eaf0;
    }
    .pos-simple .pos-transaction-head { gap: 10px; }
    .pos-simple .pos-transaction-heading { gap: 9px; }
    .pos-simple .pos-transaction-heading .pos-section-kicker,
    .pos-simple .pos-transaction-heading p { display: none; }
    .pos-simple .pos-transaction-heading h2 { margin: 0; color: #26344f; font-size: 1rem; }
    .pos-simple .pos-draft-pill {
        margin-left: auto;
        padding: 5px 8px;
        color: #6c788c;
        border-color: #dfe4eb;
        background: #f8f9fb;
    }

    .pos-simple .pos-transaction-insights {
        display: flex;
        align-items: center;
        gap: 18px;
        margin: 10px 0 0 43px;
    }
    .pos-simple .pos-insight-card {
        min-height: 0;
        gap: 6px;
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
    }
    .pos-simple .pos-insight-card:nth-child(2),
    .pos-simple #cartHealthCard { display: none; }
    .pos-simple .pos-insight-icon { width: 27px; height: 27px; border-radius: 8px; }
    .pos-simple .pos-insight-card small { display: none; }
    .pos-simple .pos-insight-card strong { color: #4b5870; }
    .pos-simple .pos-cart-readiness { display: none; }

    .pos-simple .pos-transaction-details { margin: 10px 12px 0; border-radius: 10px; box-shadow: none; }
    .pos-simple .pos-transaction-details > summary { min-height: 48px; padding: 8px 10px; }
    .pos-simple .pos-cart-toolbar { margin: 12px 12px 8px; }
    .pos-simple .pos-cart-title-icon { width: 30px; height: 30px; }
    .pos-simple .pos-live-label { display: none; }
    .pos-simple .pos-go-payment-button {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        color: #fff;
        border: 1px solid #4f63d8;
        border-radius: 8px;
        background: #4f63d8;
        font-size: .78rem;
    }
    .pos-simple .pos-go-payment-button:hover:not(:disabled) { color: #fff; background: #4053c2; }
    .pos-simple .pos-go-payment-button:disabled { display: none; }
    .pos-prescription-cashier-step .pos-go-payment-button { display: none; }

    .pos-simple .pos-cart-wrap {
        min-height: 200px;
        max-height: 50vh;
        margin: 0 12px 12px;
        border-radius: 10px;
        background: #f7f8fa;
        box-shadow: none;
    }
    .pos-simple .pos-cart-table { min-width: 720px; border-spacing: 0 5px; }
    .pos-simple .pos-cart-table th:nth-child(3),
    .pos-simple .pos-cart-table td:nth-child(3),
    .pos-simple .pos-cart-table th:nth-child(5),
    .pos-simple .pos-cart-table td:nth-child(5) { display: none; }
    .pos-simple .pos-cart-table td { padding-top: 8px; padding-bottom: 8px; }
    .pos-simple .pos-cart-unit-editor { max-width: 260px; }
    .pos-simple .pos-cart-unit-editor select.form-select { height: 31px; font-size: .72rem; }
    .pos-simple .pos-item-quick-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 5px;
        color: #7b8799;
        font-size: .72rem;
    }
    .pos-simple .pos-item-quick-meta > span { display: inline-flex; align-items: center; gap: 3px; }
    .pos-simple .pos-item-quick-meta .is-warning { color: #bc4858; }
    .pos-simple .pos-cart-empty { min-height: 190px; background: transparent; }
    .pos-simple .pos-cart-empty-meta,
    .pos-simple .pos-cart-footnote { display: none; }

    .pos-simple .pos-checkout-stage {
        overflow: hidden;
        margin-top: 12px;
        border: 1px solid #dfe4ec;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(31, 43, 70, .05);
    }
    .pos-simple .pos-checkout-stage:not(.has-items) { display: none; }
    .pos-simple .pos-checkout-hero {
        min-height: 72px;
        padding: 12px 14px;
        border-bottom: 1px solid #e7eaf0;
        border-radius: 0;
    }
    .pos-simple .pos-checkout-title { gap: 9px; }
    .pos-simple .pos-checkout-title .pos-section-kicker,
    .pos-simple .pos-checkout-title p { display: none; }
    .pos-simple .pos-checkout-title h2 { margin: 0; color: #26344f; font-size: 1.05rem; }
    .pos-simple .pos-checkout-hero-side { gap: 10px; }
    .pos-simple .pos-checkout-state { color: #9a651e; border-color: #f0dec0; background: #fff8ec; }
    .pos-simple .pos-checkout-stage.is-short .pos-checkout-state { color: #9a651e; background: #fff8ec; }
    .pos-simple .pos-checkout-stage.is-ready .pos-checkout-state { color: #087c59; border-color: #c8e7dc; background: #eef9f5; }
    .pos-simple .pos-checkout-due {
        min-width: 180px;
        padding: 7px 10px;
        color: #29465d;
        border: 1px solid #cfe8df;
        border-radius: 10px;
        background: #f0faf6;
        text-align: right;
    }
    .pos-simple .pos-checkout-due small { color: #668b80; }
    .pos-simple .pos-checkout-due strong { color: #087a58; font-size: 1.3rem; }

    .pos-simple .pos-payment-layout { gap: 12px; padding: 12px; }
    .pos-simple .pos-payment-panel,
    .pos-simple .pos-total-panel {
        padding: 14px;
        border-color: #e1e5ec;
        border-radius: 11px;
        box-shadow: none;
    }
    .pos-simple .pos-payment-title-copy small { display: none; }
    .pos-simple .pos-payment-row { border-radius: 10px; background: #fff; }
    .pos-simple .pos-payment-quick { padding: 10px; border-radius: 10px; background: #f8f9fb; }
    .pos-simple .pos-payment-quick-copy small { display: none; }
    .pos-simple .pos-payment-coverage { padding: 9px 10px; border-radius: 10px; }

    .pos-simple .pos-adjustment-card {
        overflow: hidden;
        margin: 4px 0 10px;
        padding: 0;
        border-radius: 10px;
    }
    .pos-simple .pos-adjustment-card-head {
        display: flex;
        align-items: center;
        padding: 10px;
        border: 0;
        cursor: pointer;
        list-style: none;
    }
    .pos-simple .pos-adjustment-card-head::-webkit-details-marker { display: none; }
    .pos-simple .pos-adjustment-card-head small { display: inline-flex; align-items: center; gap: 4px; }
    .pos-simple .pos-adjustment-card-head small i { color: #929cad; transition: transform .2s ease; }
    .pos-simple .pos-adjustment-card[open] .pos-adjustment-card-head {
        border-bottom: 1px solid #e8ecf2;
    }
    .pos-simple .pos-adjustment-card[open] .pos-adjustment-card-head small i { transform: rotate(180deg); }
    .pos-simple .pos-adjustment-card-body { padding: 0 10px 4px; }

    .pos-simple .pos-total-row.is-grand {
        min-height: 66px;
        color: #263b87;
        background: #eef1ff;
        box-shadow: none;
    }
    .pos-simple .pos-total-row.is-grand > span,
    .pos-simple .pos-total-row.is-grand strong { color: #263b87; }
    .pos-simple .pos-total-row.is-grand > span small { color: #7583b8; }

    .pos-simple .pos-submit-bar {
        bottom: 8px;
        margin: 0 12px 12px;
        border-radius: 11px;
        box-shadow: 0 5px 16px rgba(31, 43, 70, .09);
        backdrop-filter: none;
    }
    .pos-simple .pos-save-draft-button,
    .pos-simple .pos-complete-button { border-radius: 9px; box-shadow: none; }
    .pos-simple .pos-complete-button { background: #0b9569; }
    .pos-simple .pos-complete-button:hover:not(:disabled) { background: #087e59; box-shadow: none; transform: none; }

    /* Premium polish: restrained depth, richer contrast, and clearer focal actions. */
    .pos-simple {
        --pos-premium-navy: #17223f;
        --pos-premium-navy-soft: #24365f;
        --pos-premium-indigo: #5668df;
        --pos-premium-indigo-dark: #4052c3;
        --pos-premium-emerald: #0d9b70;
        --pos-premium-line: #dce2ec;
        --pos-premium-ink: #1c2942;
        --pos-premium-muted: #768298;
        background:
            radial-gradient(circle at 4% 0, rgba(98, 114, 221, .085), transparent 28%),
            radial-gradient(circle at 96% 18%, rgba(26, 164, 119, .055), transparent 24%),
            linear-gradient(180deg, #f5f7fb 0%, #eef2f7 100%);
    }

    .pos-simple .pos-appbar {
        min-height: 70px;
        padding: 11px 15px;
        color: #f8faff;
        border-color: rgba(255, 255, 255, .11);
        border-radius: 17px;
        background:
            radial-gradient(circle at 72% -120%, rgba(122, 142, 255, .5), transparent 40%),
            linear-gradient(118deg, #121b34 0%, #1a294d 58%, #263a70 100%);
        box-shadow: 0 14px 32px rgba(24, 35, 67, .2), inset 0 1px 0 rgba(255, 255, 255, .09);
    }

    .pos-simple .pos-brand { gap: 11px; }
    .pos-simple .pos-brand-mark {
        width: 43px;
        height: 43px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .19);
        border-radius: 12px;
        background: linear-gradient(145deg, #7282f2, #5063d7);
        box-shadow: 0 8px 18px rgba(7, 12, 39, .28), inset 0 1px 0 rgba(255, 255, 255, .25);
        font-size: 22px;
    }

    .pos-simple .pos-eyebrow {
        display: block;
        margin-bottom: 1px;
        color: #aebbf0;
        font-size: .55rem;
        font-weight: 800;
        letter-spacing: .14em;
    }

    .pos-simple .pos-brand h1 { color: #fff; font-size: 1.18rem; letter-spacing: -.025em; }
    .pos-simple .pos-brand-title > span { color: #aeb9d2; }
    .pos-simple .pos-appbar-center { justify-content: center; }
    .pos-simple .pos-session-status {
        color: #bff3dd;
        border-color: rgba(82, 220, 163, .17);
        background: rgba(41, 194, 133, .11);
    }
    .pos-simple .pos-session-status.is-offline {
        color: #ffd2d9;
        border-color: rgba(255, 109, 128, .18);
        background: rgba(225, 80, 99, .14);
    }
    .pos-simple .pos-clock { color: #d1d9ec; }
    .pos-simple .pos-clock i { color: #9eacd2; }
    .pos-simple .pos-cashier-profile { border-left-color: rgba(255, 255, 255, .14); }
    .pos-simple .pos-cashier-avatar {
        color: #2b396c;
        border-color: rgba(255, 255, 255, .3);
        background: linear-gradient(145deg, #f8f9ff, #ced6ff);
        box-shadow: 0 4px 10px rgba(4, 10, 33, .15);
    }
    .pos-simple .pos-cashier-profile small { color: #94a2c5; }
    .pos-simple .pos-cashier-profile strong { color: #f4f6ff; }

    .pos-simple .pos-page .pos-btn-quiet,
    .pos-simple .pos-btn-quiet {
        color: #eef2ff;
        border-color: rgba(255, 255, 255, .12);
        background: rgba(255, 255, 255, .065);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
        backdrop-filter: blur(10px);
    }
    .pos-simple .pos-btn-quiet:hover:not(:disabled) {
        color: #fff;
        border-color: rgba(255, 255, 255, .24);
        background: rgba(255, 255, 255, .13);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08);
        transform: translateY(-1px);
    }
    .pos-simple .pos-btn-quiet:disabled {
        color: #7784a4;
        border-color: transparent;
        background: rgba(255, 255, 255, .025);
    }

    .pos-simple .pos-surface,
    .pos-simple .pos-panel,
    .pos-simple .pos-toolbar,
    .pos-simple .pos-checkout-stage {
        border-color: var(--pos-premium-line);
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(30, 43, 72, .075), inset 0 1px 0 rgba(255, 255, 255, .9);
    }
    .pos-simple .pos-product-panel,
    .pos-simple .pos-transaction-panel,
    .pos-simple .pos-checkout-stage { background: rgba(255, 255, 255, .98); }

    .pos-simple .pos-catalog-hero,
    .pos-simple .pos-transaction-hero,
    .pos-simple .pos-checkout-hero {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fd 100%);
    }
    .pos-simple .pos-catalog-hero { border-radius: 16px 16px 0 0; }
    .pos-simple .pos-catalog-step,
    .pos-simple .pos-transaction-number,
    .pos-simple .pos-checkout-step {
        color: #fff;
        border-radius: 10px;
        background: linear-gradient(145deg, #7180ee, #5265d7);
        box-shadow: 0 6px 14px rgba(70, 87, 193, .21), inset 0 1px 0 rgba(255, 255, 255, .25);
    }
    .pos-simple .pos-catalog-hero h2,
    .pos-simple .pos-transaction-heading h2,
    .pos-simple .pos-checkout-title h2 { color: var(--pos-premium-ink); font-weight: 850; letter-spacing: -.02em; }

    .pos-simple .pos-search-box { padding: 15px 14px 14px; }
    .pos-simple .pos-search-title { color: #2c3b57; font-size: .79rem; }
    .pos-simple .pos-search-control .select2-container--default .select2-selection--single {
        height: 51px;
        min-height: 51px;
        border-color: #d3daea;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 5px 14px rgba(39, 52, 93, .065), inset 0 1px 0 #fff;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .pos-simple .pos-search-control .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 49px; }
    .pos-simple .pos-search-control .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #8494e8;
        box-shadow: 0 0 0 3px rgba(86, 104, 223, .11), 0 8px 18px rgba(39, 52, 93, .08);
    }
    .pos-simple .pos-search-leading {
        background: linear-gradient(145deg, #6c7bee, #4d60d4);
        box-shadow: 0 5px 12px rgba(68, 83, 190, .24);
    }

    .pos-simple .pos-catalog-block {
        border-color: #dfe4ed;
        border-radius: 12px;
        box-shadow: 0 5px 14px rgba(31, 43, 70, .045);
    }
    .pos-simple .pos-catalog-block-head { background: linear-gradient(145deg, #fcfdff, #f7f9fc); }
    .pos-simple .pos-product-card { border-radius: 11px; }
    .pos-simple .pos-selection-block .pos-product-card.has-product {
        border-color: #cbd3f5;
        background: linear-gradient(145deg, #fff, #f7f8ff);
        box-shadow: 0 7px 17px rgba(63, 79, 171, .07);
    }
    .pos-simple .pos-product-summary span { background: #f7f8fb; }
    .pos-simple .pos-add-button {
        background: linear-gradient(135deg, #5c6fe3, #4558c7);
        box-shadow: 0 7px 15px rgba(65, 82, 190, .2);
    }
    .pos-simple .pos-add-button:hover:not(:disabled) {
        background: linear-gradient(135deg, #5265d8, #3e50b9);
        box-shadow: 0 9px 18px rgba(65, 82, 190, .25);
        transform: translateY(-1px);
    }

    .pos-simple .pos-draft-pill {
        border-color: #dce2eb;
        background: rgba(255, 255, 255, .82);
        box-shadow: 0 2px 7px rgba(33, 46, 77, .04);
    }
    .pos-simple .pos-insight-icon { box-shadow: inset 0 0 0 1px rgba(74, 91, 188, .07); }
    .pos-simple .pos-transaction-details,
    .pos-simple .pos-cart-wrap { border: 1px solid #e1e6ee; }
    .pos-simple .pos-cart-wrap {
        background: #f5f7fa;
        box-shadow: inset 0 1px 2px rgba(35, 48, 76, .025);
    }
    .pos-simple .pos-cart-table thead th { background: rgba(245, 247, 250, .97); }
    .pos-simple .pos-cart-table td { box-shadow: 0 2px 6px rgba(35, 48, 76, .025); }
    .pos-simple .pos-cart-table tbody tr:not(.pos-cart-empty-row):hover td {
        border-color: #cfd7ed;
        background: #fbfcff;
    }
    .pos-simple .pos-go-payment-button {
        border-color: #5366d8;
        background: linear-gradient(135deg, #5d70e2, #475ac7);
        box-shadow: 0 5px 11px rgba(67, 83, 189, .17);
    }
    .pos-simple .pos-go-payment-button:hover:not(:disabled) {
        background: linear-gradient(135deg, #5366d8, #4052b9);
        box-shadow: 0 7px 14px rgba(67, 83, 189, .22);
        transform: translateY(-1px);
    }
    .pos-prescription-toolbar-pay { display: none; }

    .pos-simple .pos-checkout-stage { box-shadow: 0 12px 32px rgba(29, 42, 72, .085), inset 0 1px 0 #fff; }
    .pos-simple .pos-checkout-due {
        border-color: #cae6dc;
        background: linear-gradient(145deg, #f4fcf9, #ebf8f4);
        box-shadow: inset 0 1px 0 #fff;
    }
    .pos-simple .pos-payment-panel,
    .pos-simple .pos-total-panel {
        border-color: #dde3ec;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 6px 17px rgba(31, 44, 74, .055), inset 0 1px 0 #fff;
    }
    .pos-simple .pos-payment-row {
        border-color: #e0e5ed;
        box-shadow: 0 3px 9px rgba(34, 47, 77, .035);
    }
    .pos-simple .pos-payment-quick { border: 1px solid #e2e7ef; background: linear-gradient(145deg, #fafbfc, #f5f7fa); }
    .pos-simple .pos-adjustment-card { border-color: #e0e5ed; background: #fafbfc; }
    .pos-simple .pos-total-row.is-grand {
        min-height: 72px;
        color: #fff;
        border: 0;
        background:
            radial-gradient(circle at 100% 0, rgba(128, 148, 255, .38), transparent 48%),
            linear-gradient(135deg, var(--pos-premium-navy), var(--pos-premium-navy-soft));
        box-shadow: 0 10px 22px rgba(29, 44, 83, .19), inset 0 1px 0 rgba(255, 255, 255, .1);
    }
    .pos-simple .pos-total-row.is-grand > span,
    .pos-simple .pos-total-row.is-grand strong { color: #fff; }
    .pos-simple .pos-total-row.is-grand > span small { color: #b9c6e8; }

    .pos-simple .pos-submit-bar {
        border-color: rgba(210, 218, 231, .92);
        background: rgba(255, 255, 255, .94);
        box-shadow: 0 10px 24px rgba(27, 40, 70, .12), inset 0 1px 0 #fff;
        backdrop-filter: blur(14px);
    }
    .pos-simple .pos-save-draft-button { border-color: #d5dce8; background: #fff; }
    .pos-simple .pos-complete-button {
        background: linear-gradient(135deg, #0c966c, #16ae7e);
        box-shadow: 0 8px 17px rgba(13, 155, 112, .23);
    }
    .pos-simple .pos-complete-button:hover:not(:disabled) {
        background: linear-gradient(135deg, #087e5a, #0d966b);
        box-shadow: 0 10px 20px rgba(13, 155, 112, .28);
        transform: translateY(-1px);
    }

    /* Simple premium prescription flow: one language for non-compound and compound prescriptions. */
    .pos-prescription-type-modal.rx-simple {
        --rx-navy: #17223f;
        --rx-navy-soft: #263a70;
        --rx-indigo: #5668da;
        --rx-indigo-dark: #4052ba;
        --rx-emerald: #0d966b;
        --rx-violet: #7758bd;
        --rx-line: #dce2ec;
        --rx-ink: #1d2a43;
        --rx-muted: #758197;
    }

    .rx-simple .modal-content {
        background:
            radial-gradient(circle at 4% 0, rgba(92, 109, 215, .1), transparent 27rem),
            linear-gradient(180deg, #f3f6fa, #edf1f6);
    }

    /* Prescription type chooser */
    .rx-simple .pos-prescription-type-step {
        padding: 18px;
        background:
            radial-gradient(circle at 9% 0, rgba(91, 108, 216, .13), transparent 28rem),
            radial-gradient(circle at 93% 95%, rgba(23, 158, 113, .07), transparent 22rem),
            linear-gradient(145deg, #edf1f7, #f8fafc);
    }
    .rx-simple .pos-prescription-type-step-shell {
        width: min(780px, 100%);
        border: 1px solid rgba(214, 221, 233, .9);
        border-radius: 18px;
        box-shadow: 0 22px 56px rgba(23, 34, 67, .18), inset 0 1px 0 rgba(255, 255, 255, .9);
    }
    .rx-simple .pos-prescription-type-step .modal-header {
        align-items: center;
        padding: 18px 20px;
        background:
            radial-gradient(circle at 88% -100%, rgba(113, 229, 187, .34), transparent 48%),
            linear-gradient(118deg, #131d37, #1d2d54 62%, #2a4079);
    }
    .rx-simple .pos-prescription-modal-heading { gap: 11px; }
    .rx-simple .pos-prescription-modal-heading > span {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(145deg, rgba(124, 140, 242, .95), rgba(76, 94, 205, .95));
        box-shadow: 0 7px 16px rgba(5, 10, 34, .24), inset 0 1px 0 rgba(255, 255, 255, .22);
        font-size: 21px;
    }
    .rx-simple .pos-prescription-modal-heading small { color: #aebbf0; letter-spacing: .13em; }
    .rx-simple .pos-prescription-modal-heading h2 { margin: 1px 0 0; font-size: 18px; letter-spacing: -.02em; }
    .rx-simple .pos-prescription-modal-heading p { margin-top: 2px; color: #b8c3dc; }
    .rx-simple .pos-prescription-type-step .modal-body { padding: 18px; background: #f8f9fc; }
    .rx-simple .pos-prescription-modal-intro,
    .rx-simple .pos-prescription-modal-help { display: none; }
    .rx-simple .pos-prescription-type-grid { gap: 10px; }
    .rx-simple .pos-prescription-type-option {
        min-height: 142px;
        align-items: center;
        gap: 12px;
        padding: 16px;
        border: 1px solid #dfe4ed;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 5px 15px rgba(34, 47, 77, .045);
    }
    .rx-simple .pos-prescription-type-option:hover {
        border-color: #bfc9ed;
        background: #fbfcff;
        box-shadow: 0 10px 22px rgba(51, 65, 114, .09);
        transform: translateY(-1px);
    }
    .rx-simple .pos-prescription-type-icon {
        width: 45px;
        height: 45px;
        border: 1px solid #dfe4fa;
        border-radius: 12px;
        background: #eef1ff;
        box-shadow: inset 0 1px 0 #fff;
        font-size: 21px;
    }
    .rx-simple .pos-prescription-type-option:nth-child(2) .pos-prescription-type-icon {
        color: var(--rx-violet);
        border-color: #e8def6;
        background: #f5f0fb;
    }
    .rx-simple .pos-prescription-type-copy > small { color: #8994a8; letter-spacing: .09em; }
    .rx-simple .pos-prescription-type-copy > strong { color: #2d3b57; letter-spacing: -.01em; }
    .rx-simple .pos-prescription-type-copy > span:not(.pos-prescription-type-points) { margin-top: 4px; color: #7e899c; }
    .rx-simple .pos-prescription-type-points { gap: 4px; margin-top: 9px; }
    .rx-simple .pos-prescription-type-points b {
        padding: 3px 6px;
        color: #657188;
        border: 1px solid #e7eaf0;
        background: #f7f8fa;
    }
    .rx-simple .pos-prescription-type-check { top: 11px; right: 11px; font-size: 18px; }
    .rx-simple .pos-prescription-type-option.is-active {
        border-color: #8f9ee3;
        background: linear-gradient(145deg, #f5f7ff, #fff);
        box-shadow: 0 8px 20px rgba(66, 82, 156, .1);
    }
    .rx-simple .pos-prescription-type-option.is-active .pos-prescription-type-icon {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(145deg, #6d7de5, #4b5fc9);
        box-shadow: 0 6px 14px rgba(65, 82, 181, .2), inset 0 1px 0 rgba(255, 255, 255, .2);
    }
    .rx-simple .pos-prescription-type-option:nth-child(2).is-active .pos-prescription-type-icon {
        background: linear-gradient(145deg, #8b69c8, #6750aa);
    }
    .rx-simple .pos-prescription-type-step .modal-footer {
        min-height: 54px;
        justify-content: flex-start;
        padding: 11px 18px;
        border-color: #e4e8ef;
    }
    .rx-simple .pos-prescription-type-step .modal-footer small { display: none; }
    .rx-simple .pos-prescription-modal-cancel {
        min-height: 35px;
        border-color: #d7dde7;
        border-radius: 9px;
        background: #fff;
    }

    /* Full prescription workspace */
    .rx-simple .pos-prescription-cashier-step {
        background:
            radial-gradient(circle at 3% 0, rgba(91, 108, 216, .075), transparent 24rem),
            linear-gradient(180deg, #f3f6fa, #edf1f6);
    }
    .rx-simple .pos-prescription-cashier-header {
        min-height: 66px;
        grid-template-columns: minmax(280px, 1fr) auto;
        gap: 14px;
        padding: 10px 14px;
        background:
            radial-gradient(circle at 79% -110%, rgba(112, 229, 187, .3), transparent 39%),
            linear-gradient(118deg, #121b34, #1c2b50 61%, #293f78);
        box-shadow: 0 9px 23px rgba(23, 35, 69, .2), inset 0 1px 0 rgba(255, 255, 255, .07);
    }
    .rx-simple .pos-prescription-cashier-brand { gap: 10px; }
    .rx-simple .pos-prescription-cashier-brand > span {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(145deg, #7282ed, #5063d2);
        box-shadow: 0 7px 16px rgba(5, 10, 34, .25), inset 0 1px 0 rgba(255, 255, 255, .22);
        font-size: 21px;
    }
    .rx-simple .pos-prescription-cashier-brand small { color: #aebbef; letter-spacing: .12em; }
    .rx-simple .pos-prescription-cashier-brand strong { margin-top: 0; letter-spacing: -.015em; }
    .rx-simple .pos-prescription-cashier-brand p { display: none; }
    .rx-simple .pos-prescription-flow-steps { display: none; }
    .rx-simple .pos-prescription-flow-steps span {
        gap: 5px;
        padding: 4px 7px 4px 4px;
        color: #aeb9d2;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 9px;
        background: rgba(255, 255, 255, .035);
    }
    .rx-simple .pos-prescription-flow-steps span b {
        width: 22px;
        height: 22px;
        border-radius: 7px;
    }
    .rx-simple .pos-prescription-flow-steps span.is-active {
        color: #fff;
        border-color: rgba(123, 140, 239, .25);
        background: rgba(91, 108, 216, .16);
    }
    .rx-simple .pos-prescription-flow-steps > i { display: none; }
    .rx-simple .pos-prescription-flow-cancel {
        min-height: 36px;
        padding: 7px 10px;
        border-radius: 9px;
        background: rgba(190, 60, 80, .12);
    }

    .rx-simple .pos-prescription-cashier-body { padding: 10px; }
    .rx-simple .pos-prescription-cashier-layout {
        grid-template-columns: minmax(300px, 340px) minmax(0, 1fr);
        gap: 10px;
    }
    .rx-simple .pos-prescription-modal-catalog,
    .rx-simple .pos-prescription-modal-editor {
        border-color: var(--rx-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .97);
        box-shadow: 0 9px 24px rgba(30, 43, 73, .07), inset 0 1px 0 rgba(255, 255, 255, .9);
    }
    .rx-simple .pos-prescription-modal-editor { padding-bottom: 6px; }

    /* Compact catalog: reveal configuration only after a medicine is selected. */
    .rx-simple .pos-prescription-cashier-step .pos-product-panel {
        display: block;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: #fff;
        box-shadow: none;
    }
    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero {
        min-height: 60px;
        padding: 11px 12px;
        color: var(--rx-ink);
        border-bottom: 1px solid #e5e9f0;
        border-radius: 0;
        background: linear-gradient(145deg, #fff, #f8f9fd);
    }
    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero::after,
    .rx-simple .pos-prescription-cashier-step .pos-catalog-rail,
    .rx-simple .pos-prescription-cashier-step .pos-catalog-ready,
    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero .pos-section-kicker,
    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero p,
    .rx-simple .pos-prescription-cashier-step .pos-search-topline,
    .rx-simple .pos-prescription-cashier-step .pos-search-scope,
    .rx-simple .pos-prescription-cashier-step .pos-shortcut-card { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-catalog-step {
        width: 34px;
        height: 34px;
        color: #fff;
        border: 0;
        border-radius: 10px;
        background: linear-gradient(145deg, #7080e9, #5063d0);
        box-shadow: 0 5px 12px rgba(65, 82, 181, .18), inset 0 1px 0 rgba(255, 255, 255, .22);
        font-size: 12px;
    }
    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero h2 {
        margin: 0;
        color: var(--rx-ink);
        font-size: 15px !important;
    }
    .rx-simple .pos-prescription-cashier-step .pos-search-box {
        margin: 0;
        padding: 13px;
        border: 0;
        border-radius: 0;
        background: #fff;
        box-shadow: none;
        transform: none;
    }
    .rx-simple .pos-prescription-cashier-step .pos-search-box::before { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-search-title {
        margin-bottom: 7px;
        color: #33415b;
        font-size: 13px !important;
    }
    .rx-simple .pos-prescription-cashier-step .pos-search-title small { margin-top: 1px; color: #8d97a8; }
    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-selection--single {
        height: 49px;
        min-height: 49px;
        border-color: #d3daea;
        border-radius: 11px;
        box-shadow: 0 5px 13px rgba(39, 52, 93, .055);
    }
    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-container { width: 100% !important; }
    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-selection__rendered {
        padding: 0 70px 0 58px !important;
        line-height: 47px !important;
        text-overflow: ellipsis;
    }
    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-selection__placeholder { padding-left: 0; }
    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-selection__arrow { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-selection__clear {
        right: 55px;
        margin: 0;
    }
    .rx-simple .pos-prescription-cashier-step .pos-search-leading { left: 12px; }
    .rx-simple .pos-prescription-cashier-step .pos-search-feedback { margin-top: 7px; }
    .rx-simple .pos-prescription-cashier-step .pos-selection-block,
    .rx-simple .pos-prescription-cashier-step .pos-config-block,
    .rx-simple .pos-prescription-cashier-step .pos-stock-block { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-selection-block.has-selection,
    .rx-simple .pos-prescription-cashier-step .pos-config-block.has-selection,
    .rx-simple .pos-prescription-cashier-step .pos-stock-block.has-selection { display: block; }
    .rx-simple .pos-prescription-cashier-step .pos-catalog-block {
        margin: 0 12px 9px;
        border-color: #e0e5ed;
        border-radius: 11px;
        box-shadow: 0 4px 12px rgba(31, 43, 70, .035);
    }
    .rx-simple .pos-prescription-cashier-step .pos-selection-block .pos-catalog-block-head { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-product-card { min-height: 0; margin: 0; padding: 10px; border-radius: 10px; }
    .rx-simple .pos-prescription-cashier-step .pos-product-card.has-product {
        border-color: #cbd3f2;
        background: linear-gradient(145deg, #fff, #f7f8ff);
        box-shadow: 0 6px 15px rgba(62, 78, 168, .065);
    }
    .rx-simple .pos-prescription-cashier-step .pos-product-card.has-product::after { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-catalog-block-head {
        min-height: 42px;
        padding: 7px 9px;
        background: linear-gradient(145deg, #fcfdff, #f7f9fc);
    }
    .rx-simple .pos-prescription-cashier-step .pos-block-number { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-add-card { padding: 9px; }
    .rx-simple .pos-prescription-cashier-step .pos-add-button {
        min-height: 41px;
        border-radius: 9px;
        background: linear-gradient(135deg, #5b6edc, #4558c1);
        box-shadow: 0 6px 14px rgba(65, 82, 181, .18);
    }
    .rx-simple .pos-prescription-cashier-step .pos-stock-block > summary { cursor: pointer; list-style: none; }
    .rx-simple .pos-prescription-cashier-step .pos-stock-block > summary::-webkit-details-marker { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-stock-block > summary::after {
        margin-left: 3px;
        color: #8a95a7;
        content: "\F0140";
        font-family: "Material Design Icons";
        transition: transform .2s ease;
    }
    .rx-simple .pos-prescription-cashier-step .pos-stock-block[open] > summary::after { transform: rotate(180deg); }

    /* Data and clinical form */
    .rx-simple .pos-prescription-modal-editor .pos-transaction-details {
        margin: 10px;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .rx-simple .pos-prescription-modal-editor .pos-transaction-details > summary,
    .rx-simple .pos-prescription-modal-editor .pos-transaction-form-intro,
    .rx-simple .pos-prescription-modal-editor .pos-transaction-type-field,
    .rx-simple .pos-prescription-modal-editor .pos-transaction-type-selector-field { display: none; }
    .rx-simple .pos-prescription-modal-editor .pos-transaction-form-shell { padding: 0; background: transparent; }
    .rx-simple .pos-prescription-modal-editor .pos-form-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        padding: 0 0 10px;
    }
    .rx-simple .pos-prescription-modal-editor .pos-form-grid::before {
        grid-column: 1 / -1;
        margin-bottom: -1px;
        color: #7d89a3;
        content: "DATA PASIEN";
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .1em;
    }
    .rx-simple .pos-prescription-cashier-step .pos-field label { color: #59667c; }
    .rx-simple .pos-prescription-cashier-step .form-control,
    .rx-simple .pos-prescription-cashier-step .form-select,
    .rx-simple .pos-prescription-cashier-step .input-group-text {
        border-color: #d9e0ea;
        border-radius: 9px;
        background-color: #fff;
    }
    .rx-simple .pos-prescription-cashier-step .form-control:focus,
    .rx-simple .pos-prescription-cashier-step .form-select:focus {
        border-color: #91a0e7;
        box-shadow: 0 0 0 3px rgba(86, 104, 218, .09);
    }
    .rx-simple .pos-prescription-workspace {
        margin-top: 0;
        border-color: #d7deea;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 7px 19px rgba(31, 44, 74, .06);
    }
    .rx-simple .pos-prescription-head {
        min-height: 58px;
        gap: 9px;
        padding: 10px 11px;
        color: var(--rx-ink);
        border-bottom: 1px solid #e3e7ee;
        background: linear-gradient(145deg, #fff, #f7f9fc);
    }
    .rx-simple .pos-prescription-head-icon {
        width: 35px;
        height: 35px;
        color: #fff;
        border: 0;
        border-radius: 10px;
        background: linear-gradient(145deg, #7080e6, #5063cb);
        box-shadow: 0 5px 12px rgba(65, 82, 181, .17), inset 0 1px 0 rgba(255, 255, 255, .2);
        font-size: 18px;
    }
    .rx-simple .pos-prescription-head > div small { color: #7e8ab0; }
    .rx-simple .pos-prescription-head > div strong { color: #2b3a55; }
    .rx-simple .pos-prescription-head > div p { display: none; }
    .rx-simple .pos-prescription-status {
        color: #9a681f;
        border-color: #ecdcbf;
        background: #fff8eb;
    }
    .rx-simple .pos-prescription-status.is-ready {
        color: #0b7858;
        border-color: #c6e5d9;
        background: #eef9f5;
    }
    .rx-simple .pos-prescription-mode-summary {
        margin: 9px 10px 0;
        padding: 8px 9px;
        border-color: #e0e5ed;
        background: #fafbfc;
    }
    .rx-simple .pos-prescription-mode-icon { width: 32px; height: 32px; border-radius: 9px; box-shadow: none; }
    .rx-simple .pos-change-prescription-type {
        min-height: 32px;
        padding: 5px 8px;
        color: #4e60ba;
        border: 1px solid #cfd7ef;
        border-radius: 8px;
        background: #f7f8ff;
    }
    .rx-simple .pos-prescription-mode-note { display: none; }
    .rx-simple .pos-prescription-form-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        padding: 10px;
    }
    .rx-simple .pos-prescription-guidance { padding: 8px 10px; background: #fafbfc; }

    /* Racikan controls stay visible, but read as one compact configuration card. */
    .rx-simple .pos-compound-setup {
        margin: 9px 10px 0;
        border-color: #dddff0;
        border-radius: 11px;
        box-shadow: 0 4px 12px rgba(55, 68, 117, .035);
    }
    .rx-simple .pos-compound-steps {
        justify-content: flex-start;
        gap: 6px;
        padding: 7px 9px;
        color: #68748a;
        background: linear-gradient(90deg, #f7f8ff, #f7fbfa);
    }
    .rx-simple .pos-compound-steps b {
        width: 18px;
        height: 18px;
        background: linear-gradient(145deg, #6f7fdf, #5163c5);
    }
    .rx-simple .pos-compound-controls {
        grid-template-columns: minmax(0, 1.35fr) minmax(250px, .65fr);
        align-items: stretch;
        gap: 10px;
        padding: 10px;
    }
    .rx-simple .pos-compound-controls > label,
    .rx-simple .pos-compound-save-rule { min-height: 58px; }
    .rx-simple .pos-new-compound-group { border-radius: 8px; }
    .rx-simple .pos-compound-summary-chip { border-radius: 8px; }
    .rx-simple .pos-compound-summary-chip.is-active {
        border-color: #5366c9;
        background: linear-gradient(145deg, #6678d7, #4c5fbd);
        box-shadow: 0 4px 10px rgba(67, 84, 168, .16);
    }

    /* Prescription item editor */
    .rx-simple .pos-prescription-modal-editor .pos-cart-toolbar { margin: 10px 10px 7px; }
    .rx-simple .pos-prescription-modal-editor .pos-cart-title-icon { width: 30px; height: 30px; border-radius: 9px; }
    .rx-simple .pos-prescription-modal-editor .pos-cart-title small,
    .rx-simple .pos-prescription-modal-editor .pos-live-label,
    .rx-simple .pos-prescription-modal-editor .pos-cart-footnote { display: none; }
    .rx-simple .pos-prescription-cashier-step .pos-prescription-toolbar-pay {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        color: #fff;
        border: 0;
        border-radius: 9px;
        background: linear-gradient(135deg, #6877b8, #4d5d9d);
        box-shadow: 0 6px 13px rgba(66, 82, 150, .18);
    }
    .rx-simple .pos-prescription-cashier-step .pos-prescription-toolbar-pay:hover {
        color: #fff;
        background: linear-gradient(135deg, #5c6cac, #43538f);
        box-shadow: 0 8px 16px rgba(66, 82, 150, .23);
        transform: translateY(-1px);
    }
    .rx-simple .pos-prescription-cashier-step .pos-prescription-toolbar-pay.is-ready {
        background: linear-gradient(135deg, #0e996e, #087d5b);
        box-shadow: 0 7px 15px rgba(10, 132, 94, .22);
    }
    .rx-simple .pos-prescription-cashier-step .pos-prescription-toolbar-pay.is-ready:hover {
        background: linear-gradient(135deg, #0b8963, #066f50);
        box-shadow: 0 9px 18px rgba(10, 132, 94, .27);
    }
    .rx-simple .pos-prescription-modal-editor .pos-cart-wrap {
        min-height: 230px;
        margin: 0 10px 8px;
        border: 1px solid #e0e5ed;
        border-radius: 11px;
        background: #f5f7fa;
        box-shadow: inset 0 1px 2px rgba(35, 48, 76, .025);
    }
    .rx-simple .pos-prescription-modal-editor .pos-cart-table { min-width: 1080px; border-spacing: 0 5px; }
    .rx-simple .pos-prescription-modal-editor .pos-cart-table td { box-shadow: 0 2px 6px rgba(35, 48, 76, .025); }
    .rx-simple .pos-prescription-modal-editor .pos-cart-table th:nth-child(4),
    .rx-simple .pos-prescription-modal-editor .pos-cart-table td:nth-child(4) {
        width: 230px !important;
        min-width: 230px;
    }
    .rx-simple .pos-prescription-modal-editor .pos-discount-editor {
        grid-template-columns: minmax(88px, .82fr) minmax(118px, 1.18fr);
        gap: 6px;
    }
    .rx-simple .pos-prescription-modal-editor .pos-discount-control { min-width: 0; }
    .rx-simple .pos-prescription-modal-editor .pos-discount-control input.form-control {
        width: 100%;
        min-width: 0;
        height: 38px;
        padding: 6px 29px 6px 10px;
        font-size: 12px !important;
        line-height: 1.2 !important;
        appearance: textfield;
    }
    .rx-simple .pos-prescription-modal-editor .pos-discount-control.is-nominal input.form-control {
        padding-right: 8px;
        padding-left: 30px;
    }
    .rx-simple .pos-prescription-modal-editor .pos-discount-control input.form-control::-webkit-inner-spin-button,
    .rx-simple .pos-prescription-modal-editor .pos-discount-control input.form-control::-webkit-outer-spin-button {
        margin: 0;
        appearance: none;
    }
    .rx-simple .pos-prescription-modal-editor .pos-discount-control span {
        right: 9px;
        font-size: 11px !important;
    }
    .rx-simple .pos-prescription-modal-editor .pos-discount-control.is-nominal span {
        right: auto;
        left: 9px;
    }
    .rx-simple .pos-prescription-item-editor,
    .rx-simple .pos-compound-group-card { border-radius: 12px; box-shadow: 0 5px 14px rgba(38, 51, 83, .05); }

    /* Racikan is a small workflow: compose, save, then continue to the next card. */
    .rx-simple .pos-compound-save-rule {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 10px;
        padding: 9px 11px;
        color: #7a5a23;
        border: 1px solid #eadbbf;
        border-radius: 9px;
        background: #fffaf0;
    }
    .rx-simple .pos-compound-save-rule > span {
        display: inline-flex;
        width: 30px;
        height: 30px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff0ce;
        font-size: 16px;
    }
    .rx-simple .pos-compound-save-rule > div { display: grid; min-width: 0; }
    .rx-simple .pos-compound-save-rule strong { color: #59441f; font-size: 11px; line-height: 1.3; }
    .rx-simple .pos-compound-save-rule small { overflow: hidden; font-size: 9px; line-height: 1.4; white-space: nowrap; text-overflow: ellipsis; }
    .rx-simple .pos-compound-save-rule.is-ready { color: #0b7958; border-color: #bfe2d5; background: #effaf6; }
    .rx-simple .pos-compound-save-rule.is-ready > span { background: #d9f3e9; }
    .rx-simple .pos-new-compound-group:disabled { color: #9ba4b5; border-color: #e1e5ec; background: #f4f5f7; opacity: 1; cursor: not-allowed; }
    .rx-simple .pos-compound-summary-chip.is-saved:not(.is-active) { color: #167858; border-color: #bee2d5; background: #f0faf6; }
    .rx-simple .pos-compound-summary-chip.is-saved:not(.is-active) b { color: #167858; }

    .rx-simple .pos-compound-workspace-empty {
        min-height: 270px;
        margin: 10px;
        padding: 28px 20px;
        border: 1px dashed #cbd5e7;
        border-radius: 12px;
        background: linear-gradient(145deg, #fbfcff, #f5f8fc);
    }
    .rx-simple .pos-compound-workspace-empty .pos-cart-empty-visual {
        color: #5d6fc5;
        border-color: #d6ddf2;
        background: linear-gradient(145deg, #fff, #e9edfb);
    }
    .rx-simple .pos-compound-workspace-empty .pos-cart-empty-visual > b {
        border-color: #f7f9fc;
        background: #15936b;
        font-size: 9px;
    }
    .rx-simple .pos-compound-empty-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 4px;
        padding: 5px 8px;
        color: #5f6f88;
        border: 1px solid #d8e0ec;
        border-radius: 999px;
        background: #fff;
        font-size: 9px;
        font-weight: 750;
    }
    .rx-simple .pos-compound-empty-eyebrow.is-saved { color: #0d7757; border-color: #bee1d4; background: #eff9f5; }
    .rx-simple .pos-compound-workspace-empty > strong { color: #35445d; font-size: 14px; }
    .rx-simple .pos-compound-workspace-empty > small { max-width: 520px; font-size: 10px !important; }
    .rx-simple .pos-compound-empty-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px; margin-top: 6px; }
    .rx-simple .pos-compound-workspace-empty .pos-empty-search-button { min-height: 38px; padding: 8px 12px; border-radius: 9px; font-size: 10px; }
    .rx-simple .pos-compound-empty-pay {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        color: #fff;
        border: 0;
        border-radius: 9px;
        background: linear-gradient(135deg, #15936b, #0b7858);
        box-shadow: 0 7px 16px rgba(12, 126, 91, .2);
        font-size: 10px;
        font-weight: 750;
    }
    .rx-simple .pos-compound-empty-pay:hover { color: #fff; background: linear-gradient(135deg, #118560, #086d50); transform: translateY(-1px); }

    .rx-simple .pos-compound-group-card {
        overflow: hidden;
        border: 1px solid #cbd5e7;
        border-top: 3px solid #6073cf;
        background: #fff;
        box-shadow: 0 8px 20px rgba(45, 59, 96, .07);
    }
    .rx-simple .pos-compound-group-card.is-active { border-color: #aebbed; border-top-color: #5267ca; }
    .rx-simple .pos-compound-group-card.is-saved { border-color: #b9dfd1; border-top-color: #15936b; background: #fff; }
    .rx-simple .pos-compound-group-head {
        display: grid;
        min-height: 64px;
        grid-template-columns: 78px minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid #e3e8f1;
        background: linear-gradient(90deg, #f5f7ff 0, #fff 72%);
    }
    .rx-simple .pos-compound-group-card.is-saved .pos-compound-group-head { background: linear-gradient(90deg, #effaf6, #fff); }
    .rx-simple .pos-compound-group-mark {
        display: grid;
        width: 78px;
        min-width: 78px;
        height: 42px;
        align-content: center;
        padding: 0 10px;
        text-align: left;
        border-radius: 9px;
    }
    .rx-simple .pos-compound-group-mark small { color: rgba(255, 255, 255, .74); font-size: 8px; font-weight: 750; line-height: 1.1; }
    .rx-simple .pos-compound-group-mark b { font-size: 11px; line-height: 1.35; }
    .rx-simple .pos-compound-group-copy { gap: 2px; }
    .rx-simple .pos-compound-group-copy strong { overflow: hidden; color: #334155; font-size: 12px !important; white-space: nowrap; text-overflow: ellipsis; }
    .rx-simple .pos-compound-group-copy small { color: #7c8799; font-size: 10px !important; }
    .rx-simple .pos-compound-save-state {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        color: #976820;
        border: 1px solid #ead8b7;
        border-radius: 999px;
        background: #fff9ed;
        font-size: 9px !important;
        font-weight: 750;
        white-space: nowrap;
    }
    .rx-simple .pos-compound-save-state.is-saved { color: #0d7757; border-color: #bce0d3; background: #edf9f5; }
    .rx-simple .pos-use-compound-group {
        min-height: 32px;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 9px !important;
        white-space: nowrap;
    }
    .rx-simple .pos-compound-group-fields {
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 11px 10px;
        padding: 14px 12px 12px;
        background: #fff;
    }
    .rx-simple .pos-compound-group-fields > label:nth-child(1),
    .rx-simple .pos-compound-group-fields > label:nth-child(2),
    .rx-simple .pos-compound-group-fields > label:nth-child(3) { grid-column: span 2; }
    .rx-simple .pos-compound-group-fields > label:nth-child(4),
    .rx-simple .pos-compound-group-fields > label:nth-child(5) { grid-column: span 1; }
    .rx-simple .pos-compound-group-fields > label:nth-child(6),
    .rx-simple .pos-compound-group-fields > label:nth-child(7) { grid-column: span 2; }
    .rx-simple .pos-compound-group-fields label { align-content: start; gap: 5px; }
    .rx-simple .pos-compound-group-fields label > span:first-child {
        display: flex;
        min-height: 18px;
        align-items: center;
        gap: 4px;
        color: #536176;
        font-size: 10px !important;
        line-height: 1.35;
    }
    .rx-simple .pos-compound-group-fields .form-control,
    .rx-simple .pos-compound-group-fields .form-select,
    .rx-simple .pos-compound-group-fields .input-group-text {
        min-height: 40px;
        border-color: #d7dee9;
        font-size: 11px !important;
    }
    .rx-simple .pos-compound-group-fields .input-group { flex-wrap: nowrap; }
    .rx-simple .pos-compound-group-fields .input-group-text { border-color: #d9e0ea; background: #f6f8fb; }
    .rx-simple .pos-compound-group-fields :disabled { color: #5f6c7d; background-color: #f2f5f6; opacity: 1; }
    .rx-simple .pos-compound-auto-strip {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 12px 12px;
        padding: 8px 10px;
        color: #68768b;
        border: 1px solid #dce3ed;
        border-radius: 9px;
        background: #f8fafc;
    }
    .rx-simple .pos-compound-auto-strip > span {
        display: inline-flex;
        width: 30px;
        height: 30px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        color: #6576c8;
        border-radius: 8px;
        background: #edf1ff;
        font-size: 16px;
    }
    .rx-simple .pos-compound-auto-strip > div { display: grid; min-width: 0; gap: 1px; }
    .rx-simple .pos-compound-auto-strip b { color: #46556b; font-size: 10px; line-height: 1.35; }
    .rx-simple .pos-compound-auto-strip small { color: inherit; font-size: 9px; line-height: 1.45; }
    .rx-simple .pos-compound-auto-strip.is-calculated { color: #177457; border-color: #c3e2d7; background: #f0faf6; }
    .rx-simple .pos-compound-auto-strip.is-calculated > span { color: #0c805e; background: #dff4ec; }
    .rx-simple .pos-compound-auto-strip.is-calculated b { color: #106f54; }
    .rx-simple .pos-compound-group-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-top: 1px solid #e6eaf0;
        background: #fafbfc;
    }
    .rx-simple .pos-compound-group-footer > span { display: inline-flex; align-items: center; gap: 5px; color: #6f7b8e; font-size: 9px; line-height: 1.4; }
    .rx-simple .pos-save-compound-group {
        min-width: 174px;
        min-height: 38px;
        padding: 7px 12px;
        color: #fff;
        border: 0;
        border-radius: 8px;
        background: linear-gradient(135deg, #6577d4, #4d60bd);
        box-shadow: 0 5px 12px rgba(67, 84, 168, .18);
        font-size: 10px;
        font-weight: 750;
    }
    .rx-simple .pos-save-compound-group:hover { color: #fff; transform: translateY(-1px); }
    .rx-simple .pos-save-compound-group.is-edit { color: #116f54; border: 1px solid #b9ddcf; background: #edf9f5; box-shadow: none; }
    .rx-simple .pos-cart-table .pos-compound-group-row td,
    .rx-simple .pos-cart-table .pos-prescription-detail-row td { box-shadow: none; }
    .rx-simple .pos-compound-product-row td { border-color: #dde3ee; background: #fff; }
    .rx-simple .pos-compound-product-row.is-locked td { background: #fbfdfc; }
    .rx-simple .pos-compound-product-row.is-locked .pos-qty-stepper :disabled,
    .rx-simple .pos-compound-product-row.is-locked .pos-remove-item:disabled { opacity: .52; cursor: not-allowed; }
    .rx-simple .pos-compound-component-editor {
        margin: 0;
        padding: 11px 12px;
        border: 1px solid #d9e1ee;
        border-left: 4px solid #7384c9;
        border-radius: 11px;
        background: #fbfcff;
    }
    .rx-simple .pos-compound-component-editor.is-locked { border-left-color: #25a17a; background: #f8fcfa; }
    .rx-simple .pos-compound-component-editor .pos-prescription-item-head {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr) auto;
        gap: 9px;
    }
    .rx-simple .pos-compound-component-editor .pos-prescription-item-head > span:first-child { width: 32px; height: 32px; }
    .rx-simple .pos-compound-component-editor .pos-prescription-item-head strong { font-size: 11px !important; }
    .rx-simple .pos-compound-component-editor .pos-prescription-item-head small { font-size: 9px !important; line-height: 1.45; }
    .rx-simple .pos-prescription-item-grid.is-compound-component {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 9px;
        margin-top: 10px;
    }
    .rx-simple .pos-prescription-item-grid.is-compound-component label > span { font-size: 10px !important; }
    .rx-simple .pos-prescription-item-grid.is-compound-component .form-control { min-height: 40px; font-size: 11px !important; }
    .rx-simple .pos-compound-take-field {
        align-content: center;
        gap: 2px !important;
        padding: 6px 10px;
        border: 1px solid #d7dfeb;
        border-radius: 9px;
        background: #f4f7fb;
    }
    .rx-simple .pos-compound-take-field strong { color: #33435e; font-size: 13px; line-height: 1.35; }
    .rx-simple .pos-compound-take-field small { color: #7f8a9b; font-size: 8px; line-height: 1.35; }
    .rx-simple .pos-auto-field-badge {
        display: inline-flex;
        align-items: center;
        margin-left: 4px;
        padding: 2px 5px;
        color: #65738c;
        border: 1px solid #d7dfeb;
        border-radius: 999px;
        background: #f6f8fb;
        font-size: 7px;
        font-style: normal;
        font-weight: 800;
        letter-spacing: .04em;
        vertical-align: middle;
    }
    .rx-simple .pos-auto-field-badge.is-active,
    .rx-simple .pos-compound-take-field .pos-auto-field-badge { color: #0b7958; border-color: #bfe3d6; background: #edf9f5; }
    .rx-simple .pos-compound-calculation-note {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 9px;
        padding: 8px 9px;
        color: #66758b;
        border: 1px solid #dce3ed;
        border-radius: 8px;
        background: #f8fafc;
        font-size: 9px;
        line-height: 1.4;
    }
    .rx-simple .pos-compound-calculation-note i { flex: 0 0 auto; font-size: 13px; }
    .rx-simple .pos-compound-calculation-note.is-calculated { color: #0d7658; border-color: #bee1d4; background: #eff9f5; }
    .rx-simple .pos-compound-calculation-note.is-manual { color: #8a6021; border-color: #ead9ba; background: #fff9ee; }

    /* Compact footer keeps status, total, and primary action in one line. */
    .rx-simple .pos-prescription-cashier-footer {
        min-height: 70px;
        grid-template-columns: minmax(220px, 1fr) auto auto;
        gap: 12px;
        padding: 9px 14px;
        border-color: #d9e0e9;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 -8px 22px rgba(30, 43, 73, .08);
        backdrop-filter: blur(14px);
    }
    .rx-simple .pos-prescription-flow-readiness > span { border-radius: 10px; }
    .rx-simple .pos-prescription-flow-metrics { gap: 6px; }
    .rx-simple .pos-prescription-flow-metrics > span {
        min-width: 104px;
        padding: 6px 9px;
        border-color: #e0e5ed;
        border-radius: 9px;
        background: #f8f9fb;
    }
    .rx-simple .pos-prescription-draft-button {
        min-height: 42px;
        border-color: #d0d8ea;
        border-radius: 9px;
        background: #f8f9ff;
    }
    .rx-simple .pos-finish-prescription-button {
        min-width: 172px;
        min-height: 46px;
        padding-right: 14px;
        padding-left: 15px;
        border-radius: 10px;
        background: linear-gradient(135deg, #6676bd, #4b5b9d);
        box-shadow: 0 7px 16px rgba(66, 82, 150, .18);
    }
    .rx-simple .pos-finish-prescription-button > span { font-size: 13px; }
    .rx-simple .pos-finish-prescription-button > i { font-size: 20px; }
    .rx-simple .pos-finish-prescription-button.is-ready {
        background: linear-gradient(135deg, #0e996e, #087d5b);
        box-shadow: 0 8px 18px rgba(10, 132, 94, .22);
    }

    /* Mandatory compound prescription checkpoint before the payment workspace. */
    .rx-simple .pos-compound-preview-step {
        display: grid;
        height: 100vh;
        min-height: 0;
        grid-template-rows: auto minmax(0, 1fr) auto;
        color: #1d2b3d;
        font-family: "Segoe UI Variable", "Segoe UI", Inter, Arial, sans-serif;
        font-size: 14px;
        line-height: 1.45;
        font-kerning: normal;
        font-synthesis: none;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        background:
            radial-gradient(circle at 8% 0, rgba(63, 91, 177, .11), transparent 26rem),
            linear-gradient(180deg, #f2f5f8, #e9eef2);
    }
    .rx-simple .pos-compound-preview-header {
        display: flex;
        min-height: 76px;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 12px 20px;
        color: #fff;
        background:
            radial-gradient(circle at 82% -120%, rgba(92, 224, 177, .32), transparent 38%),
            linear-gradient(118deg, #13233a, #1d3f58 62%, #116e5d);
        box-shadow: 0 8px 24px rgba(24, 48, 68, .18);
    }
    .rx-simple .pos-compound-preview-brand,
    .rx-simple .pos-compound-preview-status,
    .rx-simple .pos-compound-preview-assurance,
    .rx-simple .pos-compound-preview-actions { display: flex; align-items: center; }
    .rx-simple .pos-compound-preview-header-actions { display: flex; flex: 0 0 auto; align-items: center; gap: 10px; }
    .rx-simple .pos-compound-preview-brand { gap: 12px; }
    .rx-simple .pos-compound-preview-brand > span {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 13px;
        background: rgba(255, 255, 255, .12);
        font-size: 23px;
    }
    .rx-simple .pos-compound-preview-brand > div { display: grid; gap: 1px; }
    .rx-simple .pos-compound-preview-brand small { color: #a9e9d9; font-size: 11px; font-weight: 700; letter-spacing: .08em; line-height: 1.3; }
    .rx-simple .pos-compound-preview-brand strong { font-size: 21px; font-weight: 700; letter-spacing: -.01em; line-height: 1.25; }
    .rx-simple .pos-compound-preview-brand p { margin: 1px 0 0; color: #d6e2e8; font-size: 12px; line-height: 1.35; }
    .rx-simple .pos-compound-preview-status { gap: 9px; padding: 8px 11px; border: 1px solid rgba(255, 255, 255, .2); border-radius: 11px; background: rgba(255, 255, 255, .09); }
    .rx-simple .pos-compound-preview-status > i { color: #77dfbd; font-size: 23px; }
    .rx-simple .pos-compound-preview-status span { display: grid; }
    .rx-simple .pos-compound-preview-status small { color: #b8d4da; font-size: 10px; font-weight: 700; letter-spacing: .07em; line-height: 1.3; }
    .rx-simple .pos-compound-preview-status strong { font-size: 13px; font-weight: 700; line-height: 1.35; }

    .rx-simple .pos-compound-preview-body { min-height: 0; overflow: auto; padding: 16px; }
    .rx-simple .pos-compound-preview-shell { width: min(1160px, 100%); margin: 0 auto; }
    .rx-simple .pos-compound-preview-summary {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        margin-bottom: 12px;
        padding: 14px;
        border: 1px solid #d3dde4;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 6px 20px rgba(29, 49, 65, .06);
    }
    .rx-simple .pos-compound-preview-identity { display: grid; grid-template-columns: auto repeat(4, minmax(100px, 1fr)); gap: 10px; align-items: center; }
    .rx-simple .pos-compound-preview-logo { display: grid; width: 50px; height: 50px; place-items: center; overflow: hidden; border: 1px solid #dbe4e8; border-radius: 12px; background: #fff; }
    .rx-simple .pos-compound-preview-logo img { width: 100%; height: 100%; padding: 4px; object-fit: contain; }
    .rx-simple .pos-compound-preview-meta { min-width: 0; padding-left: 10px; border-left: 1px solid #e0e6ea; }
    .rx-simple .pos-compound-preview-meta small { display: block; color: #667986; font-size: 10px; font-weight: 700; letter-spacing: .04em; line-height: 1.3; }
    .rx-simple .pos-compound-preview-meta strong { display: block; overflow: hidden; margin-top: 4px; color: #20394c; font-size: 14px; font-weight: 700; line-height: 1.35; white-space: nowrap; text-overflow: ellipsis; }
    .rx-simple .pos-compound-preview-total { display: grid; min-width: 155px; align-content: center; justify-items: end; padding: 8px 12px; border-radius: 10px; background: #edf8f4; }
    .rx-simple .pos-compound-preview-total small { color: #456f64; font-size: 10px; font-weight: 700; letter-spacing: .05em; line-height: 1.3; }
    .rx-simple .pos-compound-preview-total strong { margin-top: 3px; color: #087456; font-size: 20px; font-weight: 700; line-height: 1.3; }

    .rx-simple .pos-compound-preview-groups { display: grid; gap: 12px; }
    .rx-simple .pos-compound-preview-group { overflow: hidden; border: 1px solid #ccd8df; border-radius: 14px; background: #fff; box-shadow: 0 7px 22px rgba(31, 50, 67, .065); }
    .rx-simple .pos-compound-preview-group-head { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 11px; padding: 11px 13px; border-bottom: 1px solid #dfe7eb; background: linear-gradient(90deg, #edf7f4, #fff 72%); }
    .rx-simple .pos-compound-preview-number { display: grid; width: 44px; height: 44px; place-items: center; color: #fff; border-radius: 11px; background: linear-gradient(145deg, #138d6a, #0b7157); font-size: 13px; font-weight: 700; line-height: 1; }
    .rx-simple .pos-compound-preview-group-title { min-width: 0; }
    .rx-simple .pos-compound-preview-group-title strong { display: block; color: #20394c; font-size: 16px; font-weight: 700; line-height: 1.3; }
    .rx-simple .pos-compound-preview-group-title small { display: block; margin-top: 3px; color: #687b88; font-size: 11px; line-height: 1.35; }
    .rx-simple .pos-compound-preview-saved { display: inline-flex; align-items: center; gap: 5px; padding: 6px 9px; color: #087456; border: 1px solid #baddd2; border-radius: 99px; background: #eff9f6; font-size: 10px; font-weight: 700; line-height: 1.2; }
    .rx-simple .pos-compound-preview-content { display: grid; grid-template-columns: minmax(0, 1fr) 270px; gap: 12px; padding: 12px; }
    .rx-simple .pos-compound-preview-clinical { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 7px; margin-bottom: 10px; }
    .rx-simple .pos-compound-preview-clinical span { min-width: 0; padding: 7px 8px; border-radius: 8px; background: #f4f7f8; }
    .rx-simple .pos-compound-preview-clinical small { display: block; color: #687a87; font-size: 10px; font-weight: 700; letter-spacing: .03em; line-height: 1.3; }
    .rx-simple .pos-compound-preview-clinical strong { display: block; overflow: hidden; margin-top: 4px; color: #293f4e; font-size: 12px; font-weight: 700; line-height: 1.35; white-space: nowrap; text-overflow: ellipsis; }
    .rx-simple .pos-compound-preview-table { width: 100%; margin: 0; border-collapse: collapse; }
    .rx-simple .pos-compound-preview-table th { padding: 8px; color: #617481; border-bottom: 1px solid #dfe6ea; background: #f8fafb; font-size: 11px; font-weight: 700; line-height: 1.35; letter-spacing: .03em; text-align: left; }
    .rx-simple .pos-compound-preview-table td { padding: 9px 8px; color: #344c5b; border-bottom: 1px solid #edf1f3; font-size: 12px; line-height: 1.4; }
    .rx-simple .pos-compound-preview-table tr:last-child td { border-bottom: 0; }
    .rx-simple .pos-compound-preview-table td:first-child { color: #20394c; font-weight: 700; }
    .rx-simple .pos-compound-preview-table th:not(:first-child),
    .rx-simple .pos-compound-preview-table td:not(:first-child) { text-align: right; }

    .rx-simple .pos-compound-label-preview { overflow: hidden; align-self: start; border: 1px solid #a9bbc5; border-radius: 10px; background: #fff; box-shadow: 0 5px 14px rgba(27, 48, 63, .08); }
    .rx-simple .pos-compound-label-preview-head { display: flex; align-items: center; gap: 8px; padding: 8px; color: #fff; background: linear-gradient(115deg, #17324d, #0b8067); }
    .rx-simple .pos-compound-label-preview-head span { display: grid; width: 28px; height: 28px; place-items: center; overflow: hidden; border-radius: 6px; background: #fff; }
    .rx-simple .pos-compound-label-preview-head img { width: 100%; height: 100%; padding: 2px; object-fit: contain; }
    .rx-simple .pos-compound-label-preview-head strong { display: block; max-width: 180px; overflow: hidden; font-size: 12px; font-weight: 700; line-height: 1.3; white-space: nowrap; text-overflow: ellipsis; }
    .rx-simple .pos-compound-label-preview-head small { display: block; margin-top: 1px; color: #d4e7ea; font-size: 10px; line-height: 1.3; }
    .rx-simple .pos-compound-label-preview-body { padding: 10px; }
    .rx-simple .pos-compound-label-preview-body > small { color: #617481; font-size: 10px; font-weight: 700; letter-spacing: .04em; line-height: 1.3; }
    .rx-simple .pos-compound-label-preview-body > strong { display: block; overflow: hidden; margin-top: 3px; color: #18394c; font-size: 14px; font-weight: 700; line-height: 1.35; white-space: nowrap; text-overflow: ellipsis; }
    .rx-simple .pos-compound-label-directions { margin-top: 8px; padding: 9px; color: #104b3d; border: 1px solid #a9d6c9; border-radius: 7px; background: #eaf7f3; font-size: 14px; font-weight: 700; line-height: 1.4; }
    .rx-simple .pos-compound-label-preview-body p { margin: 7px 0 0; color: #70491f; font-size: 10px; font-weight: 600; line-height: 1.4; }

    .rx-simple .pos-compound-preview-footer { display: flex; min-height: 74px; align-items: center; justify-content: space-between; gap: 18px; padding: 10px 18px; border-top: 1px solid #d4dde3; background: rgba(255, 255, 255, .96); box-shadow: 0 -8px 23px rgba(27, 46, 61, .08); backdrop-filter: blur(14px); }
    .rx-simple .pos-compound-preview-assurance { gap: 9px; color: #516575; }
    .rx-simple .pos-compound-preview-assurance > i { color: #0d8c69; font-size: 22px; }
    .rx-simple .pos-compound-preview-assurance span { display: grid; }
    .rx-simple .pos-compound-preview-assurance strong { color: #273e50; font-size: 12px; font-weight: 700; line-height: 1.35; }
    .rx-simple .pos-compound-preview-assurance small { color: #687a87; font-size: 10px; line-height: 1.35; }
    .rx-simple .pos-compound-preview-actions { gap: 8px; }
    .rx-simple .pos-compound-preview-edit,
    .rx-simple .pos-compound-preview-confirm { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; gap: 8px; border-radius: 10px; font-size: 13px !important; font-weight: 700; line-height: 1.3; }
    .rx-simple .pos-compound-preview-edit { padding: 0 14px; color: #536476; border: 1px solid #cad5dc; background: #fff; }
    .rx-simple .pos-compound-preview-confirm { min-width: 220px; padding: 0 15px; color: #fff; border: 0; background: linear-gradient(135deg, #0e956d, #087858); box-shadow: 0 8px 18px rgba(10, 128, 93, .22); }
    .rx-simple .pos-compound-preview-confirm:hover { color: #fff; background: linear-gradient(135deg, #0b8562, #076c50); transform: translateY(-1px); }
    .rx-simple .pos-compound-preview-confirm span { display: grid; text-align: left; }
    .rx-simple .pos-compound-preview-confirm small { color: #d2f4e9; font-size: 10px; font-weight: 600; line-height: 1.25; }
    .rx-simple .pos-compound-preview-pay { min-width: 160px; min-height: 50px; border: 1px solid rgba(255, 255, 255, .32); box-shadow: 0 9px 22px rgba(3, 42, 34, .3); }
    .rx-simple .pos-compound-preview-pay > i { font-size: 20px; }

    @media (max-width: 1280px) {
        .pos-simple .pos-appbar { grid-template-columns: 1fr auto; }
        .pos-simple .pos-appbar-center { display: none; }
        .pos-simple .pos-workspace { grid-template-columns: minmax(290px, 320px) minmax(0, 1fr); }
        .pos-simple .pos-payment-layout { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 1020px) {
        .pos-simple { padding: 9px; }
        .pos-simple .pos-workspace { grid-template-columns: 1fr; }
        .pos-simple .pos-catalog { position: static; }
        .pos-simple .pos-product-panel { display: block; }
        .pos-simple .pos-product-panel > .pos-search-box,
        .pos-simple .pos-product-panel > .pos-catalog-block { margin-right: 13px; margin-left: 13px; }
    }

    @media (max-width: 720px) {
        .pos-simple .pos-appbar { grid-template-columns: 1fr; gap: 8px; }
        .pos-simple .pos-brand-title > span { display: block; }
        .pos-simple .pos-appbar-actions { grid-template-columns: repeat(3, 1fr); }
        .pos-simple .pos-branch-button { grid-column: 1 / -1; width: 100%; }
        .pos-receipt-modal .modal-dialog { width: calc(100% - 12px); margin: 6px; }
        .pos-receipt-modal .modal-body { height: 72vh; }
        .pos-receipt-modal .modal-header p { display: none; }
        .pos-receipt-modal .modal-header { flex-wrap: wrap; }
        .pos-receipt-modal .modal-footer { flex-wrap: wrap; }
        .pos-label-size-control { width: 100%; margin: 0 0 4px; grid-template-columns: 92px minmax(0, 1fr); }
        .pos-receipt-document-tabs { order: 4; width: 100%; }
        .pos-receipt-document-tabs button { flex: 1; justify-content: center; }
        .pos-simple .pos-btn-quiet { justify-content: center; }
        .pos-simple .pos-catalog-hero { padding: 11px 12px; }
        .pos-simple .pos-transaction-insights { margin-left: 0; }
        .pos-simple .pos-draft-pill span { max-width: 110px; overflow: hidden; text-overflow: ellipsis; }
        .pos-simple .pos-cart-title small { display: none; }
        .pos-simple .pos-cart-wrap { max-height: none; }
        .pos-simple .pos-checkout-hero { align-items: stretch; flex-direction: column; }
        .pos-simple .pos-checkout-hero-side { width: 100%; }
        .pos-simple .pos-checkout-due { flex: 1; min-width: 0; text-align: left; }
        .pos-simple .pos-payment-panel,
        .pos-simple .pos-total-panel { padding: 12px; }
        .pos-simple .pos-submit-status { display: none; }
        .pos-simple .pos-submit-bar { align-items: stretch; }
        .pos-simple .pos-submit-bar > div:last-child { width: 100%; }
    }

    @media (max-width: 1240px) {
        .rx-simple .pos-prescription-cashier-header { grid-template-columns: minmax(0, 1fr) auto; }
        .rx-simple .pos-prescription-flow-steps { display: none; }
        .rx-simple .pos-prescription-cashier-footer { grid-template-columns: minmax(200px, 1fr) auto; }
        .rx-simple .pos-prescription-flow-metrics { display: none; }
        .rx-simple .pos-compound-group-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rx-simple .pos-compound-group-fields > label:nth-child(n) { grid-column: auto; }
        .rx-simple .pos-compound-group-fields > label:nth-child(7) { grid-column: 1 / -1; }
    }

    @media (max-width: 1020px) {
        .rx-simple .pos-prescription-cashier-body { overflow: auto; }
        .rx-simple .pos-prescription-cashier-layout { height: auto; grid-template-columns: 1fr; }
        .rx-simple .pos-prescription-modal-catalog,
        .rx-simple .pos-prescription-modal-editor { overflow: visible; }
        .rx-simple .pos-prescription-modal-editor .pos-form-grid,
        .rx-simple .pos-prescription-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rx-simple .pos-compound-group-fields,
        .rx-simple .pos-prescription-item-grid.is-compound-component { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rx-simple .pos-compound-group-head { grid-template-columns: 78px minmax(0, 1fr) auto; }
        .rx-simple .pos-compound-save-state { grid-column: 3; grid-row: 1; }
        .rx-simple .pos-use-compound-group { grid-column: 3; grid-row: 2; }
        .rx-simple .pos-compound-preview-summary { grid-template-columns: 1fr; }
        .rx-simple .pos-compound-preview-identity { grid-template-columns: auto repeat(2, minmax(100px, 1fr)); }
        .rx-simple .pos-compound-preview-total { justify-items: start; }
        .rx-simple .pos-compound-preview-content { grid-template-columns: 1fr; }
        .rx-simple .pos-compound-label-preview { width: min(360px, 100%); }
        .rx-simple .pos-compound-preview-clinical { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 720px) {
        .rx-simple .pos-prescription-type-step { padding: 9px; }
        .rx-simple .pos-prescription-type-step-shell { border-radius: 15px; }
        .rx-simple .pos-prescription-type-step .modal-header,
        .rx-simple .pos-prescription-type-step .modal-body { padding: 14px; }
        .rx-simple .pos-prescription-type-grid { grid-template-columns: 1fr; }
        .rx-simple .pos-prescription-type-option { min-height: 0; padding: 13px; }
        .rx-simple .pos-prescription-type-step .modal-footer { padding: 10px 14px; }
        .rx-simple .pos-prescription-type-step .modal-footer small { display: none; }
        .rx-simple .pos-prescription-cashier-header { padding: 8px 9px; }
        .rx-simple .pos-prescription-cashier-brand > span { width: 38px; height: 38px; font-size: 19px; }
        .rx-simple .pos-prescription-flow-cancel { width: 35px; height: 35px; overflow: hidden; padding: 0; font-size: 0 !important; }
        .rx-simple .pos-prescription-flow-cancel i { font-size: 17px; }
        .rx-simple .pos-prescription-cashier-body { padding: 7px; }
        .rx-simple .pos-prescription-modal-editor .pos-form-grid,
        .rx-simple .pos-prescription-form-grid,
        .rx-simple .pos-compound-controls,
        .rx-simple .pos-compound-group-fields,
        .rx-simple .pos-prescription-item-grid.is-compound-component { grid-template-columns: 1fr; }
        .rx-simple .pos-compound-group-fields > label:nth-child(7) { grid-column: auto; }
        .rx-simple .pos-compound-group-head {
            grid-template-columns: 78px minmax(0, 1fr);
            align-items: center;
            gap: 8px 10px;
        }
        .rx-simple .pos-compound-save-state { grid-column: 1; grid-row: 2; justify-self: start; }
        .rx-simple .pos-use-compound-group { grid-column: 2; grid-row: 2; justify-self: end; }
        .rx-simple .pos-compound-auto-strip { align-items: flex-start; }
        .rx-simple .pos-compound-group-footer { align-items: stretch; flex-direction: column; }
        .rx-simple .pos-save-compound-group { width: 100%; }
        .rx-simple .pos-prescription-cashier-footer { grid-template-columns: minmax(0, 1fr) minmax(178px, auto); gap: 7px; padding: 7px 8px; }
        .rx-simple .pos-compound-preview-header { padding: 10px 12px; }
        .rx-simple .pos-compound-preview-status { display: none; }
        .rx-simple .pos-compound-preview-header-actions { margin-left: auto; }
        .rx-simple .pos-compound-preview-pay { min-width: 116px; min-height: 44px; padding: 0 10px; flex: 0 0 auto; }
        .rx-simple .pos-compound-preview-pay small { display: none; }
        .rx-simple .pos-compound-preview-body { padding: 8px; }
        .rx-simple .pos-compound-preview-identity { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rx-simple .pos-compound-preview-logo { display: none; }
        .rx-simple .pos-compound-preview-meta { padding-left: 0; border-left: 0; }
        .rx-simple .pos-compound-preview-content { padding: 8px; }
        .rx-simple .pos-compound-preview-clinical { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rx-simple .pos-compound-preview-footer { align-items: stretch; flex-direction: column; gap: 8px; padding: 8px; }
        .rx-simple .pos-compound-preview-assurance { display: none; }
        .rx-simple .pos-compound-preview-actions { width: 100%; }
        .rx-simple .pos-compound-preview-edit { flex: 1; }
        .rx-simple .pos-compound-preview-confirm { min-width: 0; flex: 1.6; }
        .rx-simple .pos-prescription-flow-readiness small,
        .rx-simple .pos-prescription-draft-button { display: none; }
        .rx-simple .pos-finish-prescription-button { min-width: 178px; padding: 8px 9px; }
    }

    /* Keep the Resep -> Pembayaran handoff in a single, centered column. */
    .pos-simple .pos-workspace.is-prescription-payment-mode {
        grid-template-columns: minmax(0, 1fr);
        justify-content: stretch;
    }
    .pos-simple .pos-workspace.is-prescription-payment-mode > .pos-catalog { display: none; }
    .pos-simple .pos-workspace.is-prescription-payment-mode > .pos-checkout {
        width: 100%;
        max-width: 1180px;
        grid-column: 1;
        justify-self: center;
    }
</style>
