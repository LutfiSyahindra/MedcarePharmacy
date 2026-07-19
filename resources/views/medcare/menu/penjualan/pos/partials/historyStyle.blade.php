<style>
    .pos-history-page {
        --history-ink: #17233d;
        --history-muted: #6f7b91;
        --history-line: #e3e8f0;
        --history-surface: #ffffff;
        --history-soft: #f6f8fc;
        --history-primary: #4f63e9;
        --history-primary-dark: #3449cc;
        --history-success: #13966f;
        --history-warning: #d98227;
        --history-danger: #d65364;
        gap: 14px;
        max-width: 1800px;
        margin: 0 auto;
        color: var(--history-ink);
    }

    .pos-history-page [hidden],
    .history-drawer [hidden],
    .history-drawer-backdrop[hidden] { display: none !important; }

    .history-hero {
        position: relative;
        display: grid;
        overflow: hidden;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        padding: 22px 24px 18px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 20px;
        background:
            radial-gradient(circle at 88% 10%, rgba(105, 126, 255, .35), transparent 28%),
            linear-gradient(120deg, #111b35 0%, #203a75 58%, #344fc3 100%);
        box-shadow: 0 18px 42px rgba(25, 42, 88, .2);
    }

    .history-hero::after {
        position: absolute;
        top: -100px;
        right: 9%;
        width: 230px;
        height: 230px;
        content: '';
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 50%;
    }

    .history-hero-main,
    .history-hero-actions,
    .history-hero-context,
    .history-section-heading {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
    }

    .history-hero-main { min-width: 0; gap: 14px; }

    .history-hero-icon {
        display: inline-flex;
        width: 54px;
        height: 54px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        color: #e5e9ff;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 16px;
        background: rgba(255, 255, 255, .1);
        font-size: 27px;
        box-shadow: inset 0 1px rgba(255, 255, 255, .08);
    }

    .history-eyebrow {
        display: block;
        margin-bottom: 4px;
        color: #aebcf0;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .history-hero h1 {
        margin: 0;
        color: #fff;
        font-size: clamp(21px, 2vw, 28px);
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .history-hero p {
        max-width: 720px;
        margin: 5px 0 0;
        color: #c1cbe3;
        font-size: 12px;
        line-height: 1.5;
    }

    .history-hero-actions { gap: 8px; align-self: center; }

    .history-icon-button,
    .history-drawer-close {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        color: inherit;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 12px;
        outline: none;
        background: rgba(255, 255, 255, .08);
        font-size: 20px;
        transition: .18s ease;
    }

    .history-icon-button:hover { color: #fff; background: rgba(255, 255, 255, .15); transform: translateY(-1px); }

    .history-primary-button {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 15px;
        color: #243250;
        border: 0;
        border-radius: 12px;
        background: #fff;
        font-size: 12px;
        font-weight: 850;
        box-shadow: 0 8px 22px rgba(10, 19, 43, .18);
    }

    .history-primary-button:hover { color: var(--history-primary-dark); background: #f6f7ff; }

    .history-hero-context {
        grid-column: 1 / -1;
        gap: 18px;
        padding-top: 14px;
        color: #aeb9d3;
        border-top: 1px solid rgba(255, 255, 255, .1);
        font-size: 10px;
    }

    .history-hero-context span { display: inline-flex; align-items: center; gap: 6px; }
    .history-hero-context i { color: #aebdff; font-size: 15px; }
    .history-hero-context strong { color: #eef1ff; font-weight: 800; }

    .history-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .history-summary-card {
        position: relative;
        display: flex;
        min-width: 0;
        min-height: 112px;
        align-items: center;
        gap: 12px;
        padding: 15px;
        color: inherit;
        border: 1px solid var(--history-line);
        border-radius: 16px;
        outline: none;
        background: var(--history-surface);
        text-align: left;
        box-shadow: 0 8px 23px rgba(38, 52, 84, .06);
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    button.history-summary-card { cursor: pointer; }
    button.history-summary-card:hover { border-color: #cfd7ee; box-shadow: 0 12px 28px rgba(38, 52, 84, .1); transform: translateY(-2px); }

    .history-summary-icon {
        display: inline-flex;
        width: 43px;
        height: 43px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        font-size: 21px;
    }

    .history-summary-card.is-primary .history-summary-icon { color: #5267dc; background: #edf0ff; }
    .history-summary-card.is-success .history-summary-icon { color: #0c8b67; background: #e7f8f1; }
    .history-summary-card.is-warning .history-summary-icon { color: #bf6a16; background: #fff3e4; }
    .history-summary-card.is-danger .history-summary-icon { color: #c6475a; background: #fff0f2; }

    .history-summary-copy { display: grid; min-width: 0; gap: 2px; }
    .history-summary-copy small { color: var(--history-muted); font-size: 10px; font-weight: 750; }
    .history-summary-copy > strong { overflow: hidden; color: var(--history-ink); font-size: clamp(18px, 1.6vw, 23px); font-weight: 900; line-height: 1.25; letter-spacing: -.03em; text-overflow: ellipsis; white-space: nowrap; }
    .history-summary-copy span { color: #8a94a7; font-size: 9px; }
    .history-summary-copy span b { color: #59677d; font-weight: 850; }
    .history-summary-arrow { margin-left: auto; color: #b1bacb; font-size: 20px; }

    .history-workspace {
        display: grid;
        grid-template-columns: 285px minmax(0, 1fr);
        align-items: start;
        gap: 12px;
    }

    .history-filter-panel,
    .history-results-panel {
        border: 1px solid var(--history-line);
        border-radius: 17px;
        background: var(--history-surface);
        box-shadow: 0 8px 24px rgba(38, 52, 84, .05);
    }

    .history-filter-panel {
        position: sticky;
        top: 74px;
        display: grid;
        gap: 13px;
        padding: 16px;
    }

    .history-section-heading { min-width: 0; gap: 9px; }
    .history-section-heading > span:first-child { display: inline-flex; width: 35px; height: 35px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5266dc; border-radius: 10px; background: #eef1ff; font-size: 17px; }
    .history-section-heading h2 { margin: 0; color: var(--history-ink); font-size: 13px; font-weight: 900; letter-spacing: -.015em; }
    .history-section-heading p { margin: 2px 0 0; color: var(--history-muted); font-size: 9px; }

    .history-filter-count {
        display: inline-flex;
        width: 24px;
        height: 24px;
        margin-left: auto;
        align-items: center;
        justify-content: center;
        color: #fff;
        border-radius: 999px;
        background: var(--history-primary);
        font-size: 9px;
        font-weight: 850;
    }

    .history-filter-field { display: grid; gap: 6px; }
    .history-filter-field > label { margin: 0; color: #4f5d73; font-size: 10px; font-weight: 850; }
    .history-filter-field .form-select { min-height: 38px; color: #344159; border-color: #dce2eb; border-radius: 10px; background-color: #fff; font-size: 11px; }
    .history-filter-field .form-select:focus { border-color: #9ca8ee; box-shadow: 0 0 0 3px rgba(79, 99, 233, .08); }

    .history-search-box { position: relative; }
    .history-search-box > i { position: absolute; top: 50%; left: 10px; color: #929daf; font-size: 17px; transform: translateY(-50%); }
    .history-search-box input { width: 100%; min-height: 39px; padding: 8px 34px; color: #334158; border: 1px solid #dce2eb; border-radius: 10px; outline: none; background: #fff; font-size: 11px; }
    .history-search-box input:focus { border-color: #9ca8ee; box-shadow: 0 0 0 3px rgba(79, 99, 233, .08); }
    .history-search-box button { position: absolute; top: 50%; right: 6px; display: inline-flex; width: 27px; height: 27px; align-items: center; justify-content: center; color: #9aa4b5; border: 0; background: transparent; transform: translateY(-50%); }

    .history-range-presets { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
    .history-range-presets button { min-height: 30px; padding: 5px 3px; color: #718096; border: 1px solid #e0e5ed; border-radius: 8px; outline: none; background: #f8f9fc; font-size: 8px; font-weight: 800; }
    .history-range-presets button:hover { color: var(--history-primary); border-color: #cfd7f6; }
    .history-range-presets button.is-active { color: #fff; border-color: var(--history-primary); background: var(--history-primary); box-shadow: 0 5px 13px rgba(79, 99, 233, .18); }

    .history-date-inputs { display: grid; grid-template-columns: 1fr auto 1fr; align-items: end; gap: 5px; }
    .history-date-inputs > div { display: grid; gap: 3px; }
    .history-date-inputs span { color: #909bad; font-size: 8px; }
    .history-date-inputs input { width: 100%; min-width: 0; min-height: 34px; padding: 5px 6px; color: #506078; border: 1px solid #e0e5ed; border-radius: 8px; outline: none; background: #fff; font-size: 9px; }
    .history-date-inputs > i { margin-bottom: 9px; color: #a8b1c1; font-size: 13px; }

    .history-filter-actions { display: grid; grid-template-columns: 1fr auto; gap: 7px; padding-top: 2px; }
    .history-apply-button { display: inline-flex; min-height: 38px; align-items: center; justify-content: center; gap: 6px; color: #fff; border: 0; border-radius: 10px; background: linear-gradient(135deg, var(--history-primary), #6476ef); font-size: 10px; font-weight: 850; }
    .history-apply-button:hover { color: #fff; background: var(--history-primary-dark); }
    .history-reset-button { color: #68768c; border: 1px solid #dfe4ec; border-radius: 10px; background: #fff; font-size: 10px; font-weight: 800; }
    .history-reset-button:hover { color: var(--history-danger); border-color: #f0cbd1; background: #fff7f8; }
    .history-filter-note { display: flex; gap: 5px; margin: -2px 0 0; color: #929daf; font-size: 8px; line-height: 1.45; }
    .history-filter-note i { color: #6f80dc; font-size: 12px; }

    .history-results-panel { min-width: 0; overflow: hidden; }
    .history-results-header { display: flex; min-height: 68px; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--history-line); }
    .history-table-state span { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; color: #178362; border-radius: 999px; background: #eaf8f3; font-size: 8px; font-weight: 800; }
    .history-table-state span.is-loading { color: #5669d4; background: #eef1ff; }
    .history-table-state span.is-error { color: #bf4455; background: #fff0f2; }

    .history-table-wrap { min-height: 370px; overflow: auto; }
    .history-table { min-width: 980px; margin: 0 !important; }
    .history-table thead th { padding: 11px 12px; color: #7e899c; border-top: 0; border-bottom: 1px solid #dfe4ec !important; background: #f8f9fc; font-size: 9px; font-weight: 850; letter-spacing: .035em; text-transform: uppercase; vertical-align: middle; }
    .history-table tbody td { padding: 12px; color: #3e4c63; border-color: #edf0f5; background: #fff; font-size: 10px; vertical-align: middle; }
    .history-table tbody tr { cursor: pointer; transition: .15s ease; }
    .history-table tbody tr:hover td { background: #fafbff; }
    .history-table tbody tr:hover td:first-child { box-shadow: inset 3px 0 0 #697af0; }

    .history-transaction-cell,
    .history-customer-cell,
    .history-type-cell,
    .history-payment-cell { display: grid; gap: 3px; }
    .history-transaction-cell strong { color: #283a66; font-size: 11px; font-weight: 900; }
    .history-transaction-cell span,
    .history-customer-cell span,
    .history-type-cell span,
    .history-payment-cell span { display: inline-flex; align-items: center; gap: 4px; color: #8a95a8; font-size: 9px; }
    .history-customer-cell strong,
    .history-type-cell strong { color: #344158; font-size: 10px; font-weight: 850; }
    .history-item-count { display: inline-flex; align-items: center; gap: 5px; color: #566479; font-weight: 800; white-space: nowrap; }
    .history-item-count i { display: inline-flex; width: 25px; height: 25px; align-items: center; justify-content: center; color: #6c7bdd; border-radius: 8px; background: #f0f2ff; font-size: 13px; }
    .history-payment-cell strong { color: #24334e; font-size: 11px; font-weight: 900; white-space: nowrap; }
    .history-payment-cell .has-due { color: #c34b5b; }

    .history-status-stack { display: grid; justify-items: start; gap: 4px; }
    .history-status-badge,
    .history-payment-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 7px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 850;
        white-space: nowrap;
    }

    .history-status-badge.is-completed { color: #087754; background: #e7f8f1; }
    .history-status-badge.is-draft { color: #6852b8; background: #f0edff; }
    .history-status-badge.is-cancelled { color: #bd4655; background: #fff0f2; }
    .history-payment-badge { color: #6d798c; background: #f1f3f7; }
    .history-payment-badge.is-paid { color: #087754; background: #eef9f5; }
    .history-payment-badge.is-credit { color: #b56517; background: #fff3e6; }
    .history-payment-badge.is-void { color: #a54c58; background: #fff1f3; }

    .history-row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }
    .history-row-action { display: inline-flex; width: 31px; height: 31px; align-items: center; justify-content: center; color: #647087; border: 1px solid #dfe4ec; border-radius: 9px; outline: none; background: #fff; font-size: 15px; transition: .15s ease; }
    .history-row-action:hover { color: var(--history-primary); border-color: #cbd3f3; background: #f4f5ff; }
    .history-row-action.is-primary { color: #fff; border-color: var(--history-primary); background: var(--history-primary); }
    .history-row-action.is-primary:hover { border-color: var(--history-primary-dark); background: var(--history-primary-dark); }
    .history-row-action.is-danger:hover { color: var(--history-danger); border-color: #f0cbd1; background: #fff5f6; }

    .history-results-panel .dataTables_wrapper > .row:first-child { display: none; }
    .history-results-panel .dataTables_wrapper > .row:last-child { align-items: center; margin: 0; padding: 12px 15px; border-top: 1px solid var(--history-line); }
    .history-results-panel .dataTables_info { padding: 0 !important; color: #7d899c; font-size: 9px; }
    .history-results-panel .dataTables_paginate { padding: 0 !important; }
    .history-results-panel .page-link { min-width: 30px; color: #66748b; border-color: #e0e5ed; font-size: 9px; text-align: center; }
    .history-results-panel .page-item.active .page-link { color: #fff; border-color: var(--history-primary); background: var(--history-primary); }
    .history-results-panel .dataTables_processing { z-index: 3; top: 80px; width: auto; min-width: 180px; margin: 0; padding: 10px 14px; color: #4e60c8; border: 1px solid #dfe3fb; border-radius: 10px; background: #f3f5ff; box-shadow: 0 10px 24px rgba(41, 55, 114, .12); font-size: 10px; transform: translateX(-50%); }
    .history-results-panel td.dataTables_empty { height: 245px; color: #8995a7; text-align: center; }

    body.history-drawer-open { overflow: hidden; }
    .history-drawer-backdrop { position: fixed; z-index: 1055; inset: 0; opacity: 0; background: rgba(14, 22, 43, .45); backdrop-filter: blur(2px); transition: opacity .22s ease; }
    .history-drawer-backdrop.is-open { opacity: 1; }
    .history-drawer { position: fixed; z-index: 1056; top: 0; right: 0; display: grid; width: min(680px, 94vw); height: 100vh; grid-template-rows: auto minmax(0, 1fr) auto; color: var(--history-ink); background: #f6f8fc; box-shadow: -22px 0 48px rgba(18, 29, 58, .22); transform: translateX(105%); transition: transform .25s ease; }
    .history-drawer.is-open { transform: translateX(0); }
    .history-drawer-header { display: flex; min-height: 100px; align-items: flex-start; justify-content: space-between; gap: 15px; padding: 19px 20px 16px; color: #fff; background: linear-gradient(122deg, #131d38, #2c478d); }
    .history-drawer-header h2 { margin: 0; color: #fff; font-size: 19px; font-weight: 900; letter-spacing: -.025em; }
    .history-drawer-header .history-eyebrow { color: #aebcf0; }
    #transactionDrawerMeta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; }
    #transactionDrawerMeta > span { display: inline-flex; align-items: center; gap: 4px; padding: 4px 7px; color: #d9e0f4; border: 1px solid rgba(255, 255, 255, .13); border-radius: 999px; background: rgba(255, 255, 255, .08); font-size: 8px; }
    .history-drawer-close { flex: 0 0 auto; color: #fff; }
    .history-drawer-close:hover { background: rgba(255, 255, 255, .16); }
    .history-drawer-loading { display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 5px; color: #66738a; background: #fff; text-align: center; }
    .history-drawer-loading > i { margin-bottom: 6px; color: var(--history-primary); font-size: 29px; }
    .history-drawer-loading strong { color: #344159; font-size: 12px; }
    .history-drawer-loading span { color: #8b96a8; font-size: 9px; }
    .history-drawer-body { overflow-y: auto; padding: 14px; }
    .history-detail-stack { display: grid; gap: 11px; }
    .history-detail-card { padding: 14px; border: 1px solid var(--history-line); border-radius: 14px; background: #fff; box-shadow: 0 6px 18px rgba(38, 52, 84, .04); }
    .history-detail-card-heading { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 11px; }
    .history-detail-card-heading strong { display: inline-flex; align-items: center; gap: 6px; color: #2e3c55; font-size: 11px; font-weight: 900; }
    .history-detail-card-heading strong i { color: #6172da; font-size: 17px; }
    .history-detail-card-heading span { color: #8b96a8; font-size: 8px; }
    .history-detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .history-detail-info { min-width: 0; padding: 9px; border-radius: 10px; background: #f7f9fc; }
    .history-detail-info small { display: block; margin-bottom: 3px; color: #8d98aa; font-size: 8px; }
    .history-detail-info strong { display: block; overflow: hidden; color: #3b4960; font-size: 10px; font-weight: 850; text-overflow: ellipsis; white-space: nowrap; }
    .history-detail-items { overflow: hidden; padding: 0; }
    .history-detail-items .history-detail-card-heading { margin: 0; padding: 13px 14px; border-bottom: 1px solid var(--history-line); }
    .history-detail-table-wrap { overflow-x: auto; }
    .history-detail-table { min-width: 600px; margin: 0; }
    .history-detail-table th { padding: 8px 10px; color: #8792a4; border-color: #edf0f5; background: #f8f9fc; font-size: 8px; text-transform: uppercase; }
    .history-detail-table td { padding: 10px; color: #46536a; border-color: #edf0f5; font-size: 9px; vertical-align: top; }
    .history-detail-product strong { display: block; color: #2e3d56; font-size: 10px; }
    .history-detail-product span { display: block; margin-top: 2px; color: #8a95a7; font-size: 8px; }
    .history-batch-list { display: grid; gap: 2px; color: #7b879a; font-size: 8px; }
    .history-detail-payment-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(220px, .85fr); gap: 12px; }
    .history-payment-list { display: grid; align-content: start; gap: 6px; }
    .history-payment-entry { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px; border: 1px solid #e7eaf0; border-radius: 9px; }
    .history-payment-entry span { display: grid; gap: 2px; color: #57657a; font-size: 9px; }
    .history-payment-entry small { color: #929daf; font-size: 8px; }
    .history-payment-entry strong { color: #2d3a52; font-size: 10px; white-space: nowrap; }
    .history-financial-summary { display: grid; align-content: start; gap: 6px; padding: 11px; border-radius: 11px; background: #f6f8fc; }
    .history-financial-row { display: flex; justify-content: space-between; gap: 10px; color: #788499; font-size: 9px; }
    .history-financial-row strong { color: #455268; font-weight: 850; }
    .history-financial-row.is-total { margin-top: 4px; padding-top: 8px; color: #2b3951; border-top: 1px solid #dfe4ec; font-size: 12px; font-weight: 900; }
    .history-financial-row.is-total strong { color: var(--history-primary-dark); font-size: 13px; }
    .history-financial-row.is-due strong { color: var(--history-danger); }
    .history-detail-note { margin: 0; color: #657288; font-size: 9px; line-height: 1.55; white-space: pre-line; }
    .history-cancellation-card { border-color: #f1ccd2; background: #fff8f9; }
    .history-cancellation-card .history-detail-card-heading strong,
    .history-cancellation-card .history-detail-card-heading i { color: #bd4655; }
    .history-drawer-footer { display: flex; align-items: center; justify-content: flex-end; gap: 7px; padding: 12px 15px; border-top: 1px solid var(--history-line); background: #fff; box-shadow: 0 -8px 20px rgba(35, 49, 79, .05); }
    .history-drawer-footer .btn { display: inline-flex; min-height: 36px; align-items: center; justify-content: center; gap: 5px; border-radius: 9px; font-size: 10px; font-weight: 800; }

    @media (max-width: 1280px) {
        .history-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .history-workspace { grid-template-columns: 255px minmax(0, 1fr); }
    }

    @media (max-width: 900px) {
        .history-workspace { grid-template-columns: 1fr; }
        .history-filter-panel { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .history-filter-panel > .history-section-heading,
        .history-filter-panel > .history-filter-actions,
        .history-filter-panel > .history-filter-note { grid-column: 1 / -1; }
    }

    @media (max-width: 720px) {
        .pos-history-page { padding: 9px; }
        .history-hero { grid-template-columns: 1fr; padding: 17px; border-radius: 17px; }
        .history-hero-main { align-items: flex-start; }
        .history-hero-icon { width: 46px; height: 46px; border-radius: 14px; font-size: 23px; }
        .history-hero p { font-size: 10px; }
        .history-hero-actions { display: grid; grid-template-columns: 42px 1fr; }
        .history-hero-context { display: grid; grid-column: auto; gap: 7px; }
        .history-summary-grid { grid-template-columns: 1fr; }
        .history-summary-card { min-height: 93px; }
        .history-filter-panel { grid-template-columns: 1fr; }
        .history-filter-panel > .history-section-heading,
        .history-filter-panel > .history-filter-actions,
        .history-filter-panel > .history-filter-note { grid-column: auto; }
        .history-results-header { align-items: flex-start; }
        .history-table-state { display: none; }
        .history-detail-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .history-detail-payment-layout { grid-template-columns: 1fr; }
        .history-drawer-footer { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .history-drawer-footer .btn { width: 100%; }
    }

    /* Match the readable Roboto scale used by Pembelian and Stok. */
    .pos-history-page,
    .history-drawer {
        font-family: "Roboto", Arial, sans-serif;
        font-size: .875rem;
        font-weight: 400;
        line-height: 1.5;
        font-synthesis: none;
        text-rendering: optimizeLegibility;
    }

    .history-eyebrow {
        font-size: .75rem;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: .07em;
    }

    .history-hero h1 {
        font-weight: 900;
        line-height: 1.2;
    }

    .history-hero p {
        font-size: .86rem;
        font-weight: 400;
        line-height: 1.55;
    }

    .history-primary-button {
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .history-hero-context {
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .history-hero-context strong { font-weight: 700; }
    .history-summary-copy { gap: .2rem; }

    .history-summary-copy small {
        font-size: .78rem;
        font-weight: 500;
        line-height: 1.35;
    }

    .history-summary-copy > strong {
        font-size: clamp(1.3rem, 1.7vw, 1.55rem);
        font-weight: 900;
        line-height: 1.2;
        letter-spacing: -.02em;
    }

    .history-summary-copy span {
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .history-summary-copy span b { font-weight: 700; }

    .history-section-heading h2 {
        font-size: .95rem;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: 0;
    }

    .history-section-heading p {
        margin-top: .2rem;
        font-size: .78rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .history-filter-count {
        font-size: .72rem;
        font-weight: 700;
        line-height: 1;
    }

    .history-filter-field > label {
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .history-filter-field .form-select,
    .history-search-box input {
        font-size: .82rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .history-range-presets button {
        min-height: 34px;
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .history-date-inputs span {
        font-size: .72rem;
        font-weight: 500;
        line-height: 1.3;
    }

    .history-date-inputs input {
        min-height: 36px;
        font-size: .78rem;
        font-weight: 400;
        line-height: 1.3;
    }

    .history-apply-button,
    .history-reset-button {
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .history-filter-note {
        font-size: .72rem;
        font-weight: 400;
        line-height: 1.5;
    }

    .history-table-state span {
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .history-table { min-width: 1060px; }

    .history-table thead th {
        font-size: .75rem;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: .02em;
    }

    .history-table tbody td {
        font-size: .86rem;
        font-weight: 400;
        line-height: 1.45;
    }

    .history-transaction-cell,
    .history-customer-cell,
    .history-type-cell,
    .history-payment-cell { gap: .22rem; }

    .history-transaction-cell strong,
    .history-payment-cell strong {
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .history-customer-cell strong,
    .history-type-cell strong {
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .history-transaction-cell span,
    .history-customer-cell span,
    .history-type-cell span,
    .history-payment-cell span {
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .history-item-count {
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .history-status-badge,
    .history-payment-badge {
        padding: .35rem .55rem;
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .history-results-panel .dataTables_info {
        font-size: .8rem;
        font-weight: 500;
        line-height: 1.4;
    }

    .history-results-panel .page-link {
        font-size: .78rem;
        font-weight: 500;
        line-height: 1.3;
    }

    .history-results-panel .dataTables_processing {
        font-size: .82rem;
        font-weight: 500;
        line-height: 1.4;
    }

    .history-results-panel td.dataTables_empty {
        font-size: .86rem;
        line-height: 1.5;
    }

    .history-drawer-header h2 {
        font-size: 1.25rem;
        font-weight: 900;
        line-height: 1.25;
        letter-spacing: -.015em;
    }

    #transactionDrawerMeta > span {
        font-size: .72rem;
        font-weight: 500;
        line-height: 1.3;
    }

    .history-drawer-loading strong {
        font-size: .875rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .history-drawer-loading span {
        font-size: .78rem;
        font-weight: 400;
        line-height: 1.45;
    }

    .history-detail-card-heading strong {
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .history-detail-card-heading span {
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .history-detail-info small {
        font-size: .72rem;
        font-weight: 500;
        line-height: 1.35;
    }

    .history-detail-info strong {
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .history-detail-table th {
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: .015em;
    }

    .history-detail-table td {
        font-size: .82rem;
        font-weight: 400;
        line-height: 1.45;
    }

    .history-detail-product strong {
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .history-detail-product span,
    .history-batch-list {
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .history-payment-entry span,
    .history-payment-entry strong,
    .history-financial-row {
        font-size: .82rem;
        line-height: 1.4;
    }

    .history-payment-entry small {
        font-size: .72rem;
        font-weight: 400;
        line-height: 1.35;
    }

    .history-payment-entry strong,
    .history-financial-row strong { font-weight: 700; }

    .history-financial-row.is-total {
        font-size: .95rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .history-financial-row.is-total strong {
        font-size: 1rem;
        font-weight: 900;
    }

    .history-detail-note {
        font-size: .82rem;
        font-weight: 400;
        line-height: 1.6;
    }

    .history-drawer-footer .btn {
        font-size: .8rem;
        font-weight: 700;
        line-height: 1.35;
    }

    @media (min-width: 1281px) {
        .history-workspace { grid-template-columns: 310px minmax(0, 1fr); }
    }

    @media (min-width: 901px) and (max-width: 1280px) {
        .history-workspace { grid-template-columns: 290px minmax(0, 1fr); }
    }

    @media (max-width: 720px) {
        .history-hero p { font-size: .82rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .history-summary-card,
        .history-row-action,
        .history-drawer,
        .history-drawer-backdrop { transition-duration: .01ms !important; }
    }
</style>
