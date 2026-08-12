<style>
    /*
     * Medcare cashier workspace
     * A quiet, single-viewport layer on top of the shared POS components.
     */
    .pos-premium {
        --cashier-canvas: #f2f4f7;
        --cashier-surface: #ffffff;
        --cashier-surface-soft: #f8fafb;
        --cashier-line: #e2e7ec;
        --cashier-line-strong: #d3dae2;
        --cashier-ink: #162033;
        --cashier-text: #3e4b5f;
        --cashier-muted: #7a8698;
        --cashier-navy: #1e314f;
        --cashier-navy-hover: #263d60;
        --cashier-accent: #5367d6;
        --cashier-accent-soft: #eef1ff;
        --cashier-green: #087c5b;
        --cashier-green-hover: #066b4e;
        --cashier-green-soft: #ebf8f3;
        --cashier-amber: #b76b17;
        --cashier-amber-soft: #fff7e8;
        width: 100%;
        height: 100dvh;
        min-height: 0;
        gap: 12px;
        overflow: hidden;
        padding: 12px;
        color: var(--cashier-text);
        background: var(--cashier-canvas);
    }

    .pos-premium::before,
    .pos-premium .pos-catalog-hero::after,
    .pos-premium .pos-transaction-hero::before,
    .pos-premium .pos-checkout-hero::after,
    .pos-premium .pos-checkout-stage::before { display: none; }

    .pos-premium button,
    .pos-premium input,
    .pos-premium select { font: inherit; }

    /* Unified command bar */
    .pos-premium .pos-appbar {
        position: relative;
        z-index: 30;
        display: grid;
        min-height: 72px;
        grid-template-columns: minmax(260px, 300px) minmax(480px, 1fr) auto;
        align-items: center;
        gap: 14px;
        overflow: visible;
        padding: 9px 12px;
        color: var(--cashier-text);
        border: 1px solid #dbe1e8;
        border-radius: 16px;
        background:
            radial-gradient(circle at 70% -90%, rgba(83, 103, 214, .1), transparent 43%),
            linear-gradient(120deg, #fff 0%, #fbfcff 64%, #f7f9fc 100%);
        box-shadow: 0 10px 28px rgba(23, 35, 55, .09), 0 2px 7px rgba(23, 35, 55, .04), inset 0 1px 0 #fff;
    }

    .pos-premium .pos-appbar::after {
        position: absolute;
        z-index: 3;
        top: -1px;
        right: 18px;
        left: 18px;
        height: 2px;
        content: '';
        border-radius: 0 0 999px 999px;
        background: linear-gradient(90deg, #203654 0%, #5367d6 47%, #16a078 100%);
        opacity: .78;
    }

    .pos-premium .pos-appbar > * { position: relative; z-index: 2; }
    .pos-premium .pos-brand {
        min-width: 0;
        align-self: stretch;
        gap: 11px;
        margin: -9px 0 -9px -12px;
        padding: 10px 16px 10px 13px;
        overflow: hidden;
        border-radius: 15px 0 0 15px;
        background:
            radial-gradient(circle at 15% 0%, rgba(103, 126, 231, .28), transparent 42%),
            linear-gradient(135deg, #172942 0%, #203754 58%, #294667 100%);
        box-shadow: 10px 0 24px rgba(25, 43, 68, .11), inset -1px 0 rgba(255, 255, 255, .07);
    }

    .pos-premium .pos-brand::before {
        position: absolute;
        right: -24px;
        bottom: -38px;
        width: 110px;
        height: 110px;
        content: '';
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
        box-shadow: 0 0 0 18px rgba(255, 255, 255, .025), 0 0 0 38px rgba(255, 255, 255, .018);
        pointer-events: none;
    }

    .pos-premium .pos-brand-mark {
        position: relative;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 12px;
        background: linear-gradient(145deg, rgba(255, 255, 255, .17), rgba(255, 255, 255, .07));
        box-shadow: 0 8px 18px rgba(7, 18, 34, .2), inset 0 1px rgba(255, 255, 255, .16);
        backdrop-filter: blur(8px);
        font-size: 22px;
    }

    .pos-premium .pos-brand-mark::after {
        position: absolute;
        right: -2px;
        bottom: -2px;
        width: 10px;
        height: 10px;
        content: '';
        border: 2px solid #203754;
        border-radius: 50%;
        background: #2bc99a;
        box-shadow: 0 0 0 3px rgba(43, 201, 154, .12), 0 0 10px rgba(43, 201, 154, .62);
    }

    .pos-premium .pos-brand-copy,
    .pos-premium .pos-brand-title { min-width: 0; }
    .pos-premium .pos-brand-title { display: flex; align-items: baseline; gap: 7px; }
    .pos-premium .pos-brand h1 {
        margin: 0;
        color: #fff;
        font-size: 1.03rem;
        font-weight: 820;
        letter-spacing: -.025em;
        white-space: nowrap;
        text-shadow: 0 2px 8px rgba(4, 13, 26, .18);
    }

    .pos-premium .pos-brand-title > span {
        overflow: hidden;
        padding: 2px 6px;
        color: #c8d4e3;
        border: 1px solid rgba(255, 255, 255, .11);
        border-radius: 999px;
        background: rgba(255, 255, 255, .065);
        font-size: .55rem;
        font-weight: 720;
        letter-spacing: .025em;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .pos-premium .pos-brand-meta { display: flex; align-items: center; gap: 8px; margin-top: 3px; }
    .pos-premium .pos-session-status {
        min-height: 0;
        gap: 4px;
        padding: 0;
        color: #6ee7bd;
        border: 0;
        background: transparent;
        font-size: .64rem;
        font-weight: 750;
    }

    .pos-premium .pos-session-status i { color: #43d3a6; font-size: 8px; filter: drop-shadow(0 0 4px rgba(67, 211, 166, .65)); }
    .pos-premium .pos-session-status.is-offline { color: #ffb3bd; border: 0; background: transparent; }
    .pos-premium .pos-clock { gap: 4px; color: #aebed0; font-size: .64rem; }
    .pos-premium .pos-clock::before { content: ''; width: 1px; height: 10px; margin-right: 3px; background: rgba(255, 255, 255, .16); }
    .pos-premium .pos-clock i { color: #9eb1c8; font-size: 12px; }

    .pos-premium .pos-appbar-center { display: flex; min-width: 0; justify-content: center; }
    .pos-premium .pos-flow {
        display: flex;
        width: 100%;
        max-width: 720px;
        min-height: 50px;
        align-items: center;
        gap: 3px;
        padding: 4px;
        border: 1px solid #e0e5ed;
        border-radius: 12px;
        background: rgba(242, 245, 249, .86);
        box-shadow: inset 0 1px 3px rgba(28, 42, 64, .045), 0 1px 0 rgba(255, 255, 255, .8);
    }

    .pos-premium .pos-flow-step {
        display: flex;
        min-width: 96px;
        min-height: 40px;
        flex: 1 1 0;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        padding: 5px 8px;
        color: #7f8998;
        border: 0;
        border-radius: 9px;
        background: transparent;
        text-align: left;
        cursor: pointer;
        transition: color .16s ease, background .16s ease, box-shadow .16s ease;
    }

    .pos-premium .pos-flow-step::before { display: none !important; }
    .pos-premium .pos-flow-step:hover { color: var(--cashier-text); background: #fff; }
    .pos-premium .pos-flow-step.is-active {
        color: #4155bc;
        border: 1px solid #e0e4f6;
        background: linear-gradient(145deg, #fff 0%, #f8f9ff 100%);
        box-shadow: 0 5px 12px rgba(44, 58, 94, .1), inset 0 1px #fff;
    }

    .pos-premium .pos-flow-step.is-complete { color: var(--cashier-green); }
    .pos-premium .pos-flow-icon {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        color: #7b8798;
        border: 0;
        border-radius: 8px;
        background: #e9edf1;
        font-size: 14px;
    }

    .pos-premium .pos-flow-icon b { display: none; }
    .pos-premium .pos-flow-step.is-active .pos-flow-icon { color: #fff; background: linear-gradient(145deg, #6579e5, #485cc9); box-shadow: 0 5px 10px rgba(76, 94, 204, .23); transform: none; }
    .pos-premium .pos-flow-step.is-complete .pos-flow-icon { color: var(--cashier-green); background: var(--cashier-green-soft); }
    .pos-premium .pos-flow-step > span:last-child { display: grid; min-width: 0; gap: 0; }
    .pos-premium .pos-flow-step small { color: #9aa3b0; font-size: .52rem; font-weight: 700; line-height: 1; text-transform: none; }
    .pos-premium .pos-flow-step strong { overflow: hidden; margin: 2px 0 0; color: inherit; font-size: .7rem; font-weight: 790; white-space: nowrap; text-overflow: ellipsis; }
    .pos-premium .pos-flow-divider { display: inline-grid; flex: 0 0 13px; place-items: center; color: #c5cbd3; font-size: 13px; }
    .pos-premium .pos-flow-summary {
        display: flex;
        min-width: 150px;
        align-self: stretch;
        align-items: stretch;
        margin: 0 0 0 3px;
        padding-left: 4px;
        border: 0;
        border-left: 1px solid #e2e6eb;
        border-radius: 0;
        background: transparent;
    }

    .pos-premium .pos-flow-summary > span { display: grid; min-width: 48px; flex: 0 0 auto; align-content: center; gap: 1px; padding: 2px 7px; border: 0; }
    .pos-premium .pos-flow-summary > span + span { min-width: 96px; flex: 1; border-left: 1px solid #e8ebef; }
    .pos-premium .pos-flow-summary small { color: #929cab; font-size: .54rem; font-weight: 720; letter-spacing: .02em; text-transform: none; }
    .pos-premium .pos-flow-summary strong { color: var(--cashier-text); font-size: .72rem; font-weight: 820; }
    .pos-premium .pos-flow-summary > span:last-child strong { color: var(--cashier-ink); font-size: .82rem; }

    .pos-premium .pos-appbar-actions { min-width: 0; justify-content: flex-end; gap: 5px; }
    .pos-premium .pos-btn-quiet {
        min-height: 39px;
        gap: 6px;
        padding: 6px 9px;
        color: #526075;
        border: 1px solid #dce2e8;
        border-radius: 9px;
        background: #fff;
        box-shadow: none;
        backdrop-filter: none;
        font-size: .7rem;
        font-weight: 740;
    }

    .pos-premium .pos-btn-quiet:hover:not(:disabled) { color: var(--cashier-ink); border-color: #c8d0da; background: #f8f9fa; box-shadow: none; transform: none; }
    .pos-premium .pos-btn-quiet:disabled { color: #a5adba; border-color: #e7eaee; background: #f7f8f9; }
    .pos-premium .pos-btn-quiet kbd { display: none; }
    .pos-premium #newTransactionBtn { color: #fff; border-color: var(--cashier-navy); background: var(--cashier-navy); }
    .pos-premium #newTransactionBtn { box-shadow: 0 5px 12px rgba(30, 49, 79, .16), inset 0 1px rgba(255, 255, 255, .09); }
    .pos-premium #newTransactionBtn:hover { color: #fff; border-color: var(--cashier-navy-hover); background: var(--cashier-navy-hover); box-shadow: 0 7px 15px rgba(30, 49, 79, .2); }
    .pos-premium .pos-branch-button { min-width: 152px; max-width: 190px; }
    .pos-premium .pos-branch-button small { font-size: .52rem; }
    .pos-premium .pos-branch-button strong { font-size: .67rem; }
    .pos-premium .pos-cashier-profile { gap: 7px; margin-left: 2px; padding: 3px 5px 3px 7px; border: 1px solid #e1e5ed; border-radius: 10px; background: rgba(255, 255, 255, .72); box-shadow: 0 2px 6px rgba(23, 35, 55, .035); }
    .pos-premium .pos-cashier-avatar { width: 34px; height: 34px; color: #fff; border: 0; border-radius: 9px; background: linear-gradient(145deg, #7484dd, #5769c7); box-shadow: 0 4px 9px rgba(83, 103, 214, .18); }
    .pos-premium .pos-cashier-profile small { color: #98a2b1; font-size: .53rem; }
    .pos-premium .pos-cashier-profile strong { max-width: 78px; overflow: hidden; color: var(--cashier-text); font-size: .67rem; text-overflow: ellipsis; }

    /* Two-pane selling workspace */
    .pos-premium .pos-workspace {
        display: grid;
        min-height: 0;
        flex: 1;
        grid-template-columns: minmax(315px, 350px) minmax(0, 1fr);
        gap: 12px;
        overflow: hidden;
    }

    .pos-premium .pos-catalog { position: relative; top: auto; height: 100%; min-height: 0; overflow: auto; scrollbar-width: thin; scrollbar-color: #cbd2da transparent; }
    .pos-premium .pos-checkout { display: grid; height: 100%; min-height: 0; grid-template-rows: auto minmax(0, 1fr); gap: 10px; }
    .pos-premium .pos-surface,
    .pos-premium .pos-panel,
    .pos-premium .pos-toolbar,
    .pos-premium .pos-checkout-stage {
        border: 1px solid var(--cashier-line);
        border-radius: 14px;
        background: var(--cashier-surface);
        box-shadow: 0 4px 15px rgba(23, 35, 55, .045);
    }

    .pos-premium .pos-product-panel { min-height: 100%; overflow: hidden; background: var(--cashier-surface); }
    .pos-premium .pos-transaction-panel { position: relative; display: flex; height: 100%; min-height: 0; overflow: hidden; flex-direction: column; padding-bottom: 94px; background: var(--cashier-surface); }
    .pos-premium .pos-catalog-hero,
    .pos-premium .pos-transaction-hero,
    .pos-premium .pos-checkout-hero { color: var(--cashier-text); background: #fff; }

    .pos-premium .pos-catalog-hero { min-height: 62px; padding: 11px 12px; border-bottom: 1px solid #edf0f3; border-radius: 14px 14px 0 0; }
    .pos-premium .pos-catalog-hero-main { gap: 9px; }
    .pos-premium .pos-catalog-step,
    .pos-premium .pos-transaction-number,
    .pos-premium .pos-checkout-step {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        color: #4659c2;
        border: 0;
        border-radius: 9px;
        background: var(--cashier-accent-soft);
        box-shadow: none;
        font-size: .72rem;
    }

    .pos-premium .pos-catalog-hero h2,
    .pos-premium .pos-transaction-heading h2,
    .pos-premium .pos-checkout-title h2 { margin: 0; color: var(--cashier-ink); font-size: .94rem; font-weight: 820; letter-spacing: -.018em; }
    .pos-premium .pos-catalog-hero p,
    .pos-premium .pos-transaction-heading p,
    .pos-premium .pos-checkout-title p { margin-top: 2px; color: var(--cashier-muted); font-size: .66rem; line-height: 1.35; }
    .pos-premium .pos-catalog-ready { color: var(--cashier-green); border-color: #cde8de; background: var(--cashier-green-soft); }

    /* Product composer: search first, details only when relevant. */
    .pos-premium .pos-catalog-rail,
    .pos-premium .pos-search-topline,
    .pos-premium .pos-shortcut-card { display: none; }
    .pos-premium .pos-search-box { margin: 0; padding: 13px 12px 12px; border-bottom: 1px solid #edf0f3; background: #fbfcfd; }
    .pos-premium .pos-search-title { margin-bottom: 7px; color: var(--cashier-text); font-size: .72rem; font-weight: 780; }
    .pos-premium .pos-search-title small { margin-top: 2px; color: #98a2b1; font-size: .61rem; font-weight: 520; }
    .pos-premium .pos-search-control .select2-container--default .select2-selection--single {
        height: 48px;
        min-height: 48px;
        border: 1px solid #ccd4de;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 5px rgba(22, 32, 51, .025);
    }

    .pos-premium .pos-search-control .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left: 47px; padding-right: 54px; color: var(--cashier-text); line-height: 46px; font-size: .72rem; }
    .pos-premium .pos-search-control .select2-container--default.select2-container--open .select2-selection--single { border-color: #8290da; box-shadow: 0 0 0 3px rgba(83, 103, 214, .1); }
    .pos-premium .pos-search-leading { left: 8px; width: 32px; height: 32px; color: #4d60c7; border-radius: 8px; background: var(--cashier-accent-soft); box-shadow: none; }
    .pos-premium .pos-search-ready { right: 8px; color: var(--cashier-green); font-size: .53rem; }
    .pos-premium .pos-search-feedback { margin-top: 7px; }
    .pos-premium .pos-search-feedback p { color: var(--cashier-muted); font-size: .62rem; }
    .pos-premium .pos-search-scope { display: none; }
    .pos-premium .pos-search-box.is-attention { animation: pos-premium-attention .55s ease both; }
    @keyframes pos-premium-attention { 0%, 100% { background: #fbfcfd; } 50% { background: #fff7e8; box-shadow: inset 0 0 0 1px rgba(183, 107, 23, .2); } }

    .pos-premium .pos-catalog-block { margin: 10px 10px 0; border: 1px solid var(--cashier-line); border-radius: 11px; background: #fff; box-shadow: none; }
    .pos-premium .pos-catalog-block:last-child { margin-bottom: 10px; }
    .pos-premium .pos-catalog-block-head { min-height: 45px; padding: 7px 9px; background: var(--cashier-surface-soft); }
    .pos-premium .pos-catalog-block-head > div { gap: 7px; }
    .pos-premium .pos-catalog-block-icon { width: 28px; height: 28px; border-radius: 8px; box-shadow: none; }
    .pos-premium .pos-catalog-block-head small { font-size: .53rem; }
    .pos-premium .pos-catalog-block-head strong { font-size: .69rem; }
    .pos-premium .pos-block-state { font-size: .56rem; }
    .pos-premium .pos-product-card { padding: 9px; }
    .pos-premium .pos-selection-block .pos-product-card.has-product { border-color: #d8dee7; background: #fff; box-shadow: none; }
    .pos-premium .pos-empty-state { min-height: 92px; }
    .pos-premium .pos-empty-state .pos-empty-icon { width: 36px; height: 36px; font-size: 18px; }
    .pos-premium .pos-empty-state strong { font-size: .7rem; }
    .pos-premium .pos-empty-state > span:last-child { max-width: 220px; font-size: .61rem; }
    .pos-premium .pos-product-summary span { background: #f5f7f9; }
    .pos-premium .pos-config-block:not(.has-selection),
    .pos-premium .pos-stock-block:not(.has-selection) { display: none; }
    .pos-premium .pos-add-card { padding: 9px; }
    .pos-premium .pos-add-fields { gap: 7px; }
    .pos-premium .pos-field label { margin-bottom: 5px; color: #576477; font-size: .64rem; }
    .pos-premium .pos-field .form-control,
    .pos-premium .pos-field .form-select { min-height: 38px; border-color: #d4dae2; border-radius: 8px; color: var(--cashier-text); background-color: #fff; font-size: .7rem; }
    .pos-premium .pos-field .form-control:focus,
    .pos-premium .pos-field .form-select:focus { border-color: #8492dc; box-shadow: 0 0 0 3px rgba(83, 103, 214, .09); }
    .pos-premium .pos-add-button { min-height: 42px; border-radius: 9px; background: var(--cashier-navy); box-shadow: none; font-size: .72rem; }
    .pos-premium .pos-add-button:hover:not(:disabled) { background: var(--cashier-navy-hover); box-shadow: none; transform: none; }
    .pos-premium .pos-stock-block > summary { cursor: pointer; }
    .pos-premium .pos-quote-box { gap: 6px; padding: 8px; }
    .pos-premium .pos-quote-box > div { border-radius: 8px; background: #f7f8fa; }

    /* Cart: a calm editor with a decision dock at the bottom. */
    .pos-premium .pos-transaction-hero { flex: 0 0 auto; padding: 10px 12px; border-bottom: 1px solid #edf0f3; border-radius: 14px 14px 0 0; }
    .pos-premium .pos-transaction-head { gap: 10px; }
    .pos-premium .pos-transaction-heading { gap: 9px; }
    .pos-premium .pos-transaction-heading .pos-section-kicker { display: none; }
    .pos-premium .pos-draft-pill { min-height: 28px; padding: 4px 8px; color: #697588; border: 1px solid #dfe4e9; background: #f8f9fa; box-shadow: none; font-size: .61rem; }
    .pos-premium .pos-cart-readiness,
    .pos-premium .pos-cart-footnote,
    .pos-premium .pos-live-label { display: none; }

    .pos-premium .pos-customer-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--cashier-line);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(23, 35, 55, .04);
    }
    .pos-premium .pos-customer-card:has(.pos-transaction-details[open]) { max-height: min(440px, 46vh); overflow: auto; scrollbar-width: thin; }
    .pos-premium .pos-customer-card .pos-transaction-details { margin: 0; border: 0; border-radius: 0; background: #fff; box-shadow: none; }
    .pos-premium .pos-customer-card .pos-transaction-details > summary { min-height: 48px; padding: 7px 10px; background: #fff; }
    .pos-premium .pos-customer-card .pos-transaction-details[open] > summary { position: sticky; z-index: 2; top: 0; border-bottom: 1px solid #edf0f3; background: #fff; }
    .pos-premium .pos-customer-card .pos-transaction-form-shell { padding: 12px; background: #fafbfc; }
    .pos-premium .pos-customer-avatar { width: 29px; height: 29px; border-radius: 8px; }
    .pos-premium .pos-detail-customer small { font-size: .52rem; }
    .pos-premium .pos-detail-customer strong { font-size: .68rem; }
    .pos-premium .pos-detail-summary { font-size: .6rem; }
    .pos-premium .pos-transaction-form-shell { padding: 10px; }
    .pos-premium .pos-transaction-form-intro { margin-bottom: 9px; }
    .pos-premium .pos-form-grid { gap: 8px; }

    .pos-premium .pos-cart-toolbar { min-height: 44px; flex: 0 0 auto; margin: 7px 10px 5px; }
    .pos-premium .pos-cart-title { gap: 7px; }
    .pos-premium .pos-cart-title-icon { width: 29px; height: 29px; border-radius: 8px; }
    .pos-premium .pos-cart-title strong { color: var(--cashier-ink); font-size: .7rem; }
    .pos-premium .pos-cart-title small { color: var(--cashier-muted); font-size: .58rem; }
    .pos-premium .pos-cart-actions { gap: 5px; }
    .pos-premium .pos-clear-button,
    .pos-premium .pos-prescription-toolbar-pay { min-height: 34px; border-radius: 8px; font-size: .65rem; }

    .pos-premium .pos-cart-wrap {
        min-height: 0;
        max-height: none;
        flex: 1;
        margin: 0 10px;
        overflow: auto;
        border: 1px solid var(--cashier-line);
        border-radius: 10px;
        background: #fff;
        box-shadow: none;
        scrollbar-width: thin;
        scrollbar-color: #ccd3dc transparent;
    }

    .pos-premium .pos-cart-table { margin: 0; border-collapse: separate; border-spacing: 0 4px; }
    .pos-premium .pos-cart-table thead { position: sticky; z-index: 2; top: 0; }
    .pos-premium .pos-cart-table thead th { padding-top: 9px; padding-bottom: 9px; color: #7a8595; border-bottom: 1px solid #e7ebef; background: #f7f8fa; font-size: .57rem; }
    .pos-premium .pos-cart-table tbody tr:not(.pos-cart-empty-row) td { border-color: #e7eaee; background: #fff; box-shadow: none; }
    .pos-premium .pos-cart-table tbody tr:not(.pos-cart-empty-row):hover td { border-color: #d8dee6; background: #fafbfc; }
    .pos-premium .pos-cart-empty { min-height: 190px; gap: 6px; }
    .pos-premium .pos-cart-empty-visual { color: #5266cd; background: var(--cashier-accent-soft); box-shadow: none; }
    .pos-premium .pos-cart-empty > strong { color: var(--cashier-ink); font-size: .82rem; }
    .pos-premium .pos-cart-empty > small { max-width: 380px; font-size: .66rem; }
    .pos-premium .pos-cart-empty-meta { display: none; }
    .pos-premium .pos-empty-search-button { min-height: 38px; color: #fff; border: 0; border-radius: 8px; background: var(--cashier-navy); box-shadow: none; font-size: .68rem; }

    .pos-premium .pos-cart-dock {
        position: absolute;
        z-index: 5;
        right: 10px;
        bottom: 10px;
        left: 10px;
        display: flex !important;
        min-height: 75px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin: 0;
        padding: 8px 9px 8px 11px;
        border: 1px solid #dce2e8;
        border-radius: 11px;
        background: #f9fafb;
        box-shadow: 0 4px 12px rgba(22, 32, 51, .04);
    }

    .pos-premium .pos-cart-dock .pos-transaction-insights { display: flex; min-width: 0; align-items: center; gap: 0; margin: 0; padding: 0; }
    .pos-premium .pos-cart-dock .pos-insight-card { display: flex; min-width: 105px; min-height: 36px; align-items: center; gap: 7px; padding: 2px 14px 2px 0; border: 0; border-radius: 0; background: transparent; box-shadow: none; }
    .pos-premium .pos-cart-dock .pos-insight-card + .pos-insight-card { margin-left: 12px; padding-left: 12px; border-left: 1px solid var(--cashier-line); }
    .pos-premium .pos-insight-icon { width: 29px; height: 29px; flex: 0 0 29px; border-radius: 8px; box-shadow: none; }
    .pos-premium .pos-insight-card > span:last-child { display: grid; gap: 1px; }
    .pos-premium .pos-insight-card small { color: #929dab; font-size: .5rem; font-weight: 790; }
    .pos-premium .pos-insight-card strong { color: #536075; font-size: .64rem; font-weight: 790; }
    .pos-premium .pos-cart-dock-total { display: flex; flex: 0 0 auto; align-items: center; gap: 12px; }
    .pos-premium .pos-cart-dock-total > span { display: grid; min-width: 140px; gap: 2px; text-align: right; }
    .pos-premium .pos-cart-dock-total > span small { color: #7e8999; font-size: .59rem; }
    .pos-premium .pos-cart-dock-total > span strong { color: var(--cashier-ink); font-size: 1.14rem; font-weight: 850; letter-spacing: -.025em; }
    .pos-premium .pos-go-payment-button { min-width: 158px; min-height: 48px; gap: 12px; padding: 7px 12px 7px 14px; color: #fff; border: 0; border-radius: 10px; background: var(--cashier-navy); box-shadow: 0 5px 12px rgba(30, 49, 79, .14); font-size: .72rem; font-weight: 800; }
    .pos-premium .pos-go-payment-button > span { display: grid; gap: 1px; text-align: left; }
    .pos-premium .pos-go-payment-button small { color: #c9d2df; font-size: .51rem; font-weight: 650; }
    .pos-premium .pos-go-payment-button:hover:not(:disabled) { color: #fff; background: var(--cashier-navy-hover); box-shadow: 0 5px 12px rgba(30, 49, 79, .18); transform: none; }
    .pos-premium .pos-go-payment-button:disabled { color: #9ea8b5; background: #e5e9ee; box-shadow: none; }
    .pos-premium .pos-go-payment-button:disabled { display: inline-flex; }
    .pos-premium .pos-go-payment-button:disabled small { color: #aeb6c0; }

    /* Payment replaces the editor so the cashier has one clear decision. */
    .pos-premium .pos-workspace.is-stage-product .pos-checkout-stage,
    .pos-premium .pos-workspace.is-stage-cart .pos-checkout-stage { display: none !important; }
    .pos-premium .pos-workspace.is-stage-payment { grid-template-columns: minmax(0, 1fr); justify-content: center; }
    .pos-premium .pos-workspace.is-stage-payment > .pos-catalog,
    .pos-premium .pos-workspace.is-stage-payment .pos-transaction-panel,
    .pos-premium .pos-workspace.is-stage-payment .pos-customer-card { display: none !important; }
    .pos-premium .pos-workspace.is-stage-payment > .pos-checkout { display: block; width: 100%; max-width: 1240px; justify-self: center; }
    .pos-premium .pos-workspace.is-stage-payment .pos-checkout-stage { display: flex !important; height: 100%; min-height: 0; margin: 0; overflow: hidden; flex-direction: column; }

    .pos-premium .pos-checkout-hero { min-height: 68px; flex: 0 0 auto; gap: 11px; padding: 9px 11px; border-bottom: 1px solid #edf0f3; border-radius: 14px 14px 0 0; }
    .pos-premium .pos-checkout-title { gap: 9px; }
    .pos-premium .pos-checkout-title .pos-section-kicker { display: none; }
    .pos-premium .pos-checkout-hero-side { gap: 8px; }
    .pos-premium .pos-checkout-state { min-height: 29px; border-radius: 8px; font-size: .6rem; }
    .pos-premium .pos-checkout-due { min-width: 190px; padding: 7px 10px; border: 1px solid #cae4d9; border-radius: 9px; background: var(--cashier-green-soft); box-shadow: none; }
    .pos-premium .pos-checkout-due small { color: #68877d; font-size: .55rem; }
    .pos-premium .pos-checkout-due strong { color: var(--cashier-green); font-size: 1.06rem; }
    .pos-premium .pos-payment-back,
    .pos-premium .pos-payment-edit-prescription { display: inline-flex; min-height: 36px; flex: 0 0 auto; align-items: center; justify-content: center; gap: 5px; padding: 5px 9px; color: #526075; border: 1px solid #d9dfe7; border-radius: 8px; background: #fff; font-size: .66rem; font-weight: 760; }
    .pos-premium .pos-payment-back:hover,
    .pos-premium .pos-payment-edit-prescription:hover { color: var(--cashier-ink); border-color: #c6cfda; background: #f7f8fa; }
    .pos-premium .pos-payment-edit-prescription { color: #4658bb; border-color: #d9def5; background: #f7f8ff; }
    .pos-premium .pos-payment-edit-prescription.d-none { display: none !important; }

    .pos-premium .pos-payment-layout { min-height: 0; flex: 1; grid-template-columns: minmax(0, 1fr) minmax(330px, 375px); gap: 12px; overflow: auto; padding: 12px; scrollbar-width: thin; }
    .pos-premium .pos-payment-panel,
    .pos-premium .pos-total-panel { border: 1px solid var(--cashier-line); border-radius: 11px; background: #fff; box-shadow: none; }
    .pos-premium .pos-payment-panel { padding: 12px; }
    .pos-premium .pos-payment-head { padding-bottom: 10px; }
    .pos-premium .pos-payment-title-icon,
    .pos-premium .pos-total-heading-icon { border-radius: 9px; box-shadow: none; }
    .pos-premium .pos-payment-title-copy strong { color: var(--cashier-ink); font-size: .78rem; }
    .pos-premium .pos-payment-title-copy small { color: var(--cashier-muted); font-size: .62rem; }
    .pos-premium .pos-add-payment-button { border-radius: 8px; }
    .pos-premium .pos-payment-rows-head { color: #8d97a6; font-size: .55rem; }
    .pos-premium .pos-payment-row { border: 1px solid var(--cashier-line); border-radius: 9px; background: #fff; box-shadow: none; }
    .pos-premium .pos-payment-row.is-active { border-color: #aeb8e7; box-shadow: 0 0 0 2px rgba(83, 103, 214, .07); }
    .pos-premium .pos-payment-quick { padding: 9px; border: 1px solid #e2e6eb; border-radius: 9px; background: #f8f9fa; }
    .pos-premium .pos-payment-quick-copy span { font-size: .66rem; }
    .pos-premium .pos-payment-quick-copy small { font-size: .58rem; }
    .pos-premium .pos-quick-payment { min-height: 34px; border-radius: 8px; font-size: .64rem; }
    .pos-premium .pos-quick-payment.is-exact { color: #fff; border-color: var(--cashier-green); background: var(--cashier-green); box-shadow: none; }
    .pos-premium .pos-payment-coverage { padding: 8px 9px; border-radius: 9px; }
    .pos-premium .pos-total-panel { padding: 13px; }
    .pos-premium .pos-total-heading { padding-bottom: 10px; border-bottom-color: #edf0f3; }
    .pos-premium .pos-total-heading strong { color: var(--cashier-ink); }
    .pos-premium .pos-adjustment-card { border: 1px solid #e1e5ea; border-radius: 9px; background: #fafbfc; }
    .pos-premium .pos-total-row.is-grand { min-height: 68px; padding: 11px 12px; color: #fff; border: 0; border-radius: 10px; background: var(--cashier-navy); box-shadow: none; }
    .pos-premium .pos-total-row.is-grand > span,
    .pos-premium .pos-total-row.is-grand strong { color: #fff; }
    .pos-premium .pos-total-row.is-grand > span small { color: #bdc8d8; }
    .pos-premium .pos-total-row.is-grand strong { font-size: 1.16rem; }
    .pos-premium .pos-balance-row > div { border-radius: 8px; }

    .pos-premium .pos-submit-bar { position: relative; z-index: 10; right: auto; bottom: auto; left: auto; min-height: 62px; flex: 0 0 auto; margin: 0 12px 12px; padding: 8px 9px; border: 1px solid #d8dee6; border-radius: 10px; background: #fff; box-shadow: 0 5px 14px rgba(22, 32, 51, .07); backdrop-filter: none; }
    .pos-premium .pos-submit-status-icon { width: 32px; height: 32px; border-radius: 8px; }
    .pos-premium .pos-submit-status small { font-size: .52rem; }
    .pos-premium .pos-submit-status p { font-size: .65rem; }
    .pos-premium .pos-save-draft-button { min-height: 41px; border-color: #d6dde5; border-radius: 8px; background: #fff; }
    .pos-premium .pos-complete-button { min-width: 222px; min-height: 45px; border-radius: 9px; background: var(--cashier-green); box-shadow: none; }
    .pos-premium .pos-complete-button:hover:not(:disabled) { background: var(--cashier-green-hover); box-shadow: none; transform: none; }
    .pos-premium .pos-complete-button:disabled { box-shadow: none; }

    .swal2-popup.pos-buyer-reminder-modal {
        width: min(440px, calc(100vw - 28px));
        padding: 24px 24px 20px;
        border: 1px solid #dce3ea;
        border-radius: 20px;
        background: linear-gradient(145deg, #fff 0%, #f8fafc 100%);
        box-shadow: 0 28px 70px rgba(20, 34, 54, .23), inset 0 1px #fff;
    }
    .swal2-popup.pos-buyer-reminder-modal .swal2-icon { margin-top: 4px; transform: scale(.82); }
    .swal2-popup.pos-buyer-reminder-modal .swal2-title { padding-top: 2px; color: #17253a; font-size: 1.18rem; font-weight: 820; letter-spacing: -.02em; }
    .swal2-popup.pos-buyer-reminder-modal .swal2-html-container { margin: 8px 0 0; }
    .pos-buyer-reminder-copy { display: grid; gap: 8px; color: #5d6879; text-align: center; }
    .pos-buyer-reminder-copy p { max-width: 350px; margin: 0 auto; font-size: .82rem; line-height: 1.55; }
    .pos-buyer-reminder-copy small { color: #7e8998; font-size: .72rem; }
    .pos-buyer-reminder-copy b { color: #34455d; font-weight: 750; }
    .swal2-popup.pos-buyer-reminder-modal .swal2-actions { width: 100%; gap: 7px; margin-top: 20px; }
    .swal2-popup.pos-buyer-reminder-modal .swal2-styled { min-height: 42px; margin: 0; padding: 8px 14px; border-radius: 9px; font-size: .76rem; font-weight: 760; box-shadow: none; }
    .swal2-popup.pos-buyer-reminder-modal .pos-buyer-reminder-confirm { box-shadow: 0 6px 14px rgba(30, 49, 79, .16); }
    .swal2-popup.pos-buyer-reminder-modal .pos-buyer-reminder-confirm i { margin-right: 5px; font-size: 1rem; vertical-align: -1px; }

    /* Prescription handoff keeps the same compact workspace contract. */
    .pos-premium .pos-workspace.is-prescription-payment-mode.is-stage-cart .pos-transaction-panel { display: grid; height: 100%; align-content: center; padding: 14px; }
    .pos-premium .pos-workspace.is-prescription-payment-mode.is-stage-cart .pos-prescription-payment-handoff { margin: 0; }
    .pos-premium .pos-transaction-panel.is-prescription-handoff { padding-bottom: 0; }
    .pos-premium .pos-transaction-panel.is-prescription-handoff .pos-cart-dock { display: none !important; }

    .pos-premium .btn:focus-visible,
    .pos-premium button:focus-visible,
    .pos-premium input:focus-visible,
    .pos-premium select:focus-visible,
    .pos-premium summary:focus-visible { outline: 3px solid rgba(83, 103, 214, .2); outline-offset: 2px; }

    @media (max-width: 1480px) {
        .pos-premium .pos-appbar { grid-template-columns: minmax(175px, .55fr) minmax(430px, 1.45fr) auto; gap: 9px; }
        .pos-premium .pos-brand-title > span { display: none; }
        .pos-premium .pos-flow-step { min-width: 82px; padding: 5px 6px; }
        .pos-premium .pos-flow-summary { min-width: 132px; }
        .pos-premium .pos-flow-summary > span + span { min-width: 84px; }
        .pos-premium #printLastReceiptBtn span,
        .pos-premium .pos-cashier-profile > span:last-child { display: none; }
        .pos-premium #printLastReceiptBtn { width: 39px; justify-content: center; padding: 0; }
    }

    @media (max-width: 1240px) {
        .pos-premium .pos-appbar { grid-template-columns: auto minmax(390px, 1fr) auto; }
        .pos-premium .pos-brand-meta { display: none; }
        .pos-premium .pos-brand-title { display: block; }
        .pos-premium .pos-branch-button { min-width: 42px; width: 42px; justify-content: center; padding: 0; }
        .pos-premium .pos-branch-button > span,
        .pos-premium .pos-branch-button > i:last-child { display: none; }
        .pos-premium .pos-btn-quiet span { display: none; }
        .pos-premium .pos-btn-quiet { width: 39px; justify-content: center; padding: 0; }
        .pos-premium .pos-cashier-profile { padding-left: 5px; }
        .pos-premium .pos-cart-dock .pos-insight-card:nth-child(2) { display: none; }
    }

    /* Tablet: tabs switch between catalog and cart. */
    @media (max-width: 1080px) {
        .pos-premium .pos-appbar { grid-template-columns: auto minmax(360px, 1fr) auto; }
        .pos-premium .pos-brand-mark { width: 40px; height: 40px; flex-basis: 40px; }
        .pos-premium .pos-brand-copy { display: none; }
        .pos-premium .pos-flow-summary { display: none; }
        .pos-premium .pos-workspace { grid-template-columns: minmax(0, 1fr); }
        .pos-premium .pos-workspace.is-stage-product > .pos-catalog { display: block; }
        .pos-premium .pos-workspace.is-stage-product > .pos-checkout { display: none; }
        .pos-premium .pos-workspace.is-stage-cart > .pos-catalog { display: none; }
        .pos-premium .pos-workspace.is-stage-cart > .pos-checkout { display: block; }
        .pos-premium .pos-product-panel { display: grid; grid-template-columns: minmax(285px, .8fr) minmax(340px, 1.2fr); align-content: start; }
        .pos-premium .pos-product-panel > .pos-catalog-hero { grid-column: 1 / -1; }
        .pos-premium .pos-product-panel > .pos-search-box { grid-column: 1; }
        .pos-premium .pos-product-panel > .pos-selection-block { grid-column: 2; grid-row: 2; }
        .pos-premium .pos-product-panel > .pos-config-block { grid-column: 1; }
        .pos-premium .pos-product-panel > .pos-stock-block { grid-column: 2; grid-row: 3; }
        .pos-premium .pos-payment-layout { grid-template-columns: minmax(0, 1fr); }
        .pos-premium .pos-total-panel { display: flex; }
    }

    @media (max-width: 760px) {
        .pos-premium { gap: 7px; padding: 7px; }
        .pos-premium .pos-appbar { min-height: 108px; grid-template-columns: minmax(0, 1fr) auto; grid-template-rows: auto auto; gap: 6px; padding: 7px; border-radius: 13px; }
        .pos-premium .pos-brand { grid-column: 1; grid-row: 1; }
        .pos-premium .pos-brand-copy { display: block; }
        .pos-premium .pos-brand-title > span,
        .pos-premium .pos-brand-meta { display: none; }
        .pos-premium .pos-appbar-actions { grid-column: 2; grid-row: 1; }
        .pos-premium .pos-cashier-profile { display: none; }
        .pos-premium .pos-appbar-center { width: 100%; min-width: 0; grid-column: 1 / -1; grid-row: 2; }
        .pos-premium .pos-flow { min-width: 0; max-width: none; min-height: 45px; padding: 3px; }
        .pos-premium .pos-flow-step { min-width: 0; min-height: 37px; justify-content: center; padding: 3px; }
        .pos-premium .pos-flow-step > span:last-child { display: none; }
        .pos-premium .pos-flow-icon { width: 29px; height: 29px; flex-basis: 29px; }
        .pos-premium .pos-product-panel { display: block; }
        .pos-premium .pos-product-panel > .pos-search-box,
        .pos-premium .pos-product-panel > .pos-catalog-block { margin-right: 9px; margin-left: 9px; }
        .pos-premium .pos-cart-toolbar { align-items: center; }
        .pos-premium .pos-cart-title small { display: none; }
        .pos-premium .pos-cart-dock { align-items: stretch; flex-direction: column; gap: 8px; }
        .pos-premium .pos-cart-dock .pos-transaction-insights { display: none; }
        .pos-premium .pos-cart-dock-total { width: 100%; }
        .pos-premium .pos-cart-dock-total > span { min-width: 0; flex: 1; text-align: left; }
        .pos-premium .pos-go-payment-button { min-width: 150px; }
        .pos-premium .pos-checkout-hero { align-items: stretch; flex-wrap: wrap; }
        .pos-premium .pos-checkout-title { flex: 1; }
        .pos-premium .pos-checkout-hero-side { width: 100%; }
        .pos-premium .pos-checkout-due { min-width: 0; flex: 1; text-align: left; }
        .pos-premium .pos-payment-panel,
        .pos-premium .pos-total-panel { padding: 10px; }
        .pos-premium .pos-payment-quick { align-items: stretch; flex-direction: column; }
        .pos-premium .pos-payment-quick-options { width: 100%; }
        .pos-premium .pos-payment-quick-options .pos-quick-payment { flex: 1 1 80px; }
        .pos-premium .pos-submit-status { display: none; }
        .pos-premium .pos-submit-bar { align-items: stretch; }
        .pos-premium .pos-submit-bar > div:last-child { width: 100%; }
        .pos-premium .pos-save-draft-button { flex: 0 0 42px; width: 42px; overflow: hidden; padding: 0; font-size: 0; }
        .pos-premium .pos-save-draft-button i { font-size: 1rem; }
        .pos-premium .pos-complete-button { min-width: 0; flex: 1; }
    }

    @media (max-width: 480px) {
        .pos-premium .pos-appbar-actions > a,
        .pos-premium #printLastReceiptBtn { display: none; }
        .pos-premium .pos-cart-title-icon { display: none; }
        .pos-premium .pos-clear-button { width: 34px; overflow: hidden; padding: 0; font-size: 0; }
        .pos-premium .pos-clear-button i { font-size: .94rem; }
        .pos-premium .pos-checkout-state { display: none; }
        .pos-premium .pos-quick-payment { flex-basis: calc(50% - 5px); }
    }

    @media (prefers-reduced-motion: reduce) {
        .pos-premium *,
        .pos-premium *::before,
        .pos-premium *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; }
    }
</style>
