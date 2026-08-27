<style>
    body.pos-fullscreen-mode {
        overflow-x: hidden;
        background: #eef2f8;
    }

    body.pos-fullscreen-mode .sidebar,
    body.pos-fullscreen-mode .navbar,
    body.pos-fullscreen-mode .medcare-tab-shell,
    body.pos-fullscreen-mode .page-tabs,
    body.pos-fullscreen-mode footer:not(.pos-submit-bar),
    body.pos-fullscreen-mode .page-breadcrumb {
        display: none !important;
    }

    body.pos-fullscreen-mode .page-wrapper {
        width: 100%;
        min-height: 100vh;
        margin-left: 0;
        background: #eef2f8;
    }

    body.pos-fullscreen-mode .page-content,
    body.pos-fullscreen-mode .main-wrapper {
        min-height: 100vh;
        margin: 0 !important;
        padding: 0 !important;
    }

    .pos-page {
        --pos-ink: #152039;
        --pos-muted: #748197;
        --pos-subtle: #96a1b2;
        --pos-line: #e3e8f1;
        --pos-line-strong: #d7deea;
        --pos-primary: #4f63e9;
        --pos-primary-dark: #3548ca;
        --pos-primary-soft: #eef0ff;
        --pos-emerald: #0fa776;
        --pos-emerald-dark: #087e5a;
        --pos-danger: #e25264;
        --pos-amber: #e99a2c;
        --pos-surface: #ffffff;
        position: relative;
        isolation: isolate;
        display: flex;
        min-height: 100vh;
        flex-direction: column;
        gap: 16px;
        padding: 16px;
        color: var(--pos-ink);
        background:
            radial-gradient(circle at 9% 3%, rgba(96, 111, 230, .12), transparent 22rem),
            radial-gradient(circle at 92% 14%, rgba(15, 167, 118, .08), transparent 20rem),
            linear-gradient(180deg, #f7f9fc 0, #eef2f8 42%, #f4f6fa 100%);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .pos-page::before {
        position: fixed;
        z-index: -1;
        inset: 0;
        pointer-events: none;
        content: "";
        opacity: .36;
        background-image: radial-gradient(rgba(91, 107, 137, .18) .6px, transparent .6px);
        background-size: 16px 16px;
        mask-image: linear-gradient(to bottom, #000, transparent 70%);
    }

    .pos-page *,
    .pos-page *::before,
    .pos-page *::after {
        box-sizing: border-box;
    }

    .pos-page .btn,
    .pos-page button,
    .pos-page input,
    .pos-page select {
        font-family: inherit;
    }

    .pos-page .btn {
        border-radius: 11px;
        font-weight: 750;
        box-shadow: none;
        transition: transform .18s ease, color .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .pos-page .btn:focus-visible,
    .pos-page button:focus-visible,
    .pos-page input:focus-visible,
    .pos-page select:focus-visible,
    .pos-page summary:focus-visible {
        outline: 3px solid rgba(79, 99, 233, .22);
        outline-offset: 2px;
    }

    .pos-appbar {
        position: relative;
        display: grid;
        min-height: 82px;
        grid-template-columns: minmax(245px, .8fr) minmax(390px, 1.2fr) minmax(320px, 1fr);
        align-items: center;
        gap: 20px;
        overflow: hidden;
        padding: 13px 15px;
        color: #f7f9ff;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 20px;
        background:
            radial-gradient(circle at 78% -80%, rgba(126, 146, 255, .58), transparent 36%),
            linear-gradient(118deg, #111a34 0%, #1a2850 54%, #283a78 100%);
        box-shadow: 0 18px 45px rgba(27, 39, 81, .2), inset 0 1px 0 rgba(255, 255, 255, .08);
    }

    .pos-appbar::after {
        position: absolute;
        right: 25%;
        bottom: -70px;
        width: 210px;
        height: 120px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
        content: "";
        transform: rotate(-12deg);
    }

    .pos-brand,
    .pos-brand-title,
    .pos-appbar-center,
    .pos-appbar-actions,
    .pos-cashier-profile,
    .pos-flow-step,
    .pos-flow-summary,
    .pos-panel-heading,
    .pos-transaction-head,
    .pos-payment-head,
    .pos-cart-toolbar,
    .pos-cart-title,
    .pos-submit-bar,
    .pos-submit-bar > div,
    .pos-submit-status,
    .pos-payment-quick,
    .pos-tax-switch,
    .pos-balance-row {
        display: flex;
        align-items: center;
    }

    .pos-brand {
        z-index: 1;
        min-width: 0;
        gap: 12px;
    }

    .pos-brand-mark {
        display: inline-flex;
        width: 49px;
        height: 49px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 15px;
        background: linear-gradient(145deg, #7181ff, #4d5dd8);
        box-shadow: 0 10px 25px rgba(6, 11, 42, .32), inset 0 1px 1px rgba(255, 255, 255, .28);
        font-size: 26px;
    }

    .pos-brand-copy { min-width: 0; }

    .pos-eyebrow,
    .pos-section-kicker,
    .pos-total-heading,
    .pos-transaction-details summary,
    .pos-payment-quick > span {
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .105em;
        text-transform: uppercase;
    }

    .pos-eyebrow { color: #b6c2ff; }
    .pos-brand-title { min-width: 0; gap: 9px; }
    .pos-brand h1 { margin: 1px 0 0; color: #fff; font-size: 24px; font-weight: 850; letter-spacing: -.04em; }
    .pos-brand-title > span { overflow: hidden; color: #afbbd6; font-size: 10px; white-space: nowrap; text-overflow: ellipsis; }

    .pos-appbar-center {
        z-index: 1;
        justify-content: center;
        gap: 8px;
    }

    .pos-session-status,
    .pos-clock,
    .pos-draft-pill,
    .pos-search-shortcut {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 750;
    }

    .pos-session-status {
        padding: 7px 9px;
        color: #bff7df;
        border: 1px solid rgba(89, 224, 163, .14);
        background: rgba(48, 201, 136, .12);
    }

    .pos-session-status i { color: #53e3a2; font-size: 9px; filter: drop-shadow(0 0 5px rgba(83, 227, 162, .65)); }
    .pos-session-status.is-offline { color: #ffd2d8; border-color: rgba(255, 111, 128, .18); background: rgba(226, 82, 100, .14); }
    .pos-session-status.is-offline i { color: #ff7082; }
    .pos-clock { padding: 7px 3px; color: #d4dcf2; }
    .pos-clock i { color: #9cabdc; }

    .pos-cashier-profile {
        min-width: 0;
        gap: 7px;
        padding-left: 9px;
        border-left: 1px solid rgba(255, 255, 255, .14);
    }

    .pos-cashier-avatar {
        display: inline-flex;
        width: 30px;
        height: 30px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        color: #273565;
        border: 2px solid rgba(255, 255, 255, .34);
        border-radius: 10px;
        background: linear-gradient(145deg, #f6f8ff, #cfd7ff);
        font-size: 12px;
        font-weight: 900;
    }

    .pos-cashier-profile > span:last-child { display: grid; min-width: 0; }
    .pos-cashier-profile small { color: #94a3ca; font-size: 8px; line-height: 1.1; }
    .pos-cashier-profile strong { overflow: hidden; max-width: 92px; color: #f2f5ff; font-size: 10px; white-space: nowrap; text-overflow: ellipsis; }

    .pos-appbar-actions {
        z-index: 1;
        justify-content: flex-end;
        gap: 6px;
    }

    .pos-page .pos-btn-quiet {
        display: inline-flex;
        min-height: 39px;
        align-items: center;
        gap: 6px;
        padding: 8px 10px;
        color: #e8edff;
        border: 1px solid rgba(255, 255, 255, .12);
        background: rgba(255, 255, 255, .07);
        font-size: 11px;
        backdrop-filter: blur(8px);
    }

    .pos-page .pos-btn-quiet:hover:not(:disabled) {
        color: #fff;
        border-color: rgba(255, 255, 255, .23);
        background: rgba(255, 255, 255, .14);
        transform: translateY(-1px);
    }

    .pos-page .pos-btn-quiet:disabled { cursor: not-allowed; color: #7784a6; border-color: transparent; background: rgba(255, 255, 255, .035); }

    .pos-page kbd {
        padding: 2px 5px;
        color: #69758b;
        border: 1px solid #d6dce7;
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 1px 0 #dce1e9;
        font-family: inherit;
        font-size: 9px;
        font-weight: 800;
    }

    .pos-btn-quiet kbd { color: #c7d0ef; border-color: rgba(255, 255, 255, .17); background: rgba(0, 0, 0, .13); box-shadow: none; }

    .pos-flow {
        position: relative;
        display: grid;
        min-height: 70px;
        grid-template-columns: repeat(3, minmax(150px, .72fr)) minmax(220px, 1fr);
        align-items: center;
        gap: 12px;
        overflow: hidden;
        padding: 10px 13px;
        border: 1px solid rgba(218, 224, 235, .9);
        border-radius: 18px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 10px 28px rgba(31, 44, 79, .055), inset 0 1px 0 #fff;
        backdrop-filter: blur(14px);
    }

    .pos-flow-track {
        position: absolute;
        right: calc(25% + 56px);
        bottom: 0;
        left: 16px;
        height: 3px;
        background: #edf0f5;
    }

    .pos-flow-track > span {
        display: block;
        width: 8%;
        height: 100%;
        border-radius: 0 99px 99px 0;
        background: linear-gradient(90deg, var(--pos-primary), #7180f2, var(--pos-emerald));
        box-shadow: 0 0 10px rgba(79, 99, 233, .28);
        transition: width .35s cubic-bezier(.22, .8, .3, 1);
    }

    .pos-flow-step { position: relative; min-width: 0; gap: 9px; color: #9aa4b5; }
    .pos-flow-step + .pos-flow-step::before { position: absolute; left: -11px; width: 8px; height: 1px; content: ""; background: #d8deea; }

    .pos-flow-icon {
        position: relative;
        display: inline-flex;
        width: 37px;
        height: 37px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        color: #8d98aa;
        border: 1px solid #e0e5ee;
        border-radius: 12px;
        background: #f6f8fb;
        transition: all .25s ease;
        font-size: 17px;
    }

    .pos-flow-icon b {
        position: absolute;
        right: -4px;
        bottom: -4px;
        display: inline-flex;
        width: 15px;
        height: 15px;
        align-items: center;
        justify-content: center;
        color: #8792a5;
        border: 2px solid #fff;
        border-radius: 99px;
        background: #e9edf4;
        font-size: 7px;
    }

    .pos-flow-step > span:last-child { display: grid; min-width: 0; }
    .pos-flow-step small { color: #a1aaba; font-size: 8px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .pos-flow-step strong { overflow: hidden; margin-top: 1px; color: #68758b; font-size: 11px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-flow-step.is-active .pos-flow-icon { color: #fff; border-color: #5669e8; background: linear-gradient(145deg, #7180f2, #4f62df); box-shadow: 0 7px 16px rgba(79, 99, 233, .23); transform: translateY(-1px); }
    .pos-flow-step.is-active .pos-flow-icon b { color: #4556c8; background: #dfe3ff; }
    .pos-flow-step.is-active strong { color: #3447c7; }
    .pos-flow-step.is-complete .pos-flow-icon { color: #087a57; border-color: #bcead8; background: #e7f9f2; }
    .pos-flow-step.is-complete .pos-flow-icon b { color: #fff; background: var(--pos-emerald); }
    .pos-flow-step.is-complete strong { color: #28745d; }

    .pos-flow-summary {
        align-self: stretch;
        justify-content: flex-end;
        gap: 0;
        margin: -2px -4px -2px 0;
        overflow: hidden;
        border: 1px solid #e2e7f0;
        border-radius: 13px;
        background: linear-gradient(135deg, #fafbfe, #f3f5fa);
    }

    .pos-flow-summary > span { display: grid; min-width: 85px; padding: 8px 12px; text-align: right; }
    .pos-flow-summary > span + span { min-width: 145px; border-left: 1px solid #e2e7f0; }
    .pos-flow-summary small { color: #929cad; font-size: 8px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .pos-flow-summary strong { margin-top: 1px; color: #273553; font-size: 12px; }
    .pos-flow-summary > span:last-child strong { color: var(--pos-primary-dark); font-size: 14px; }

    .pos-workspace {
        display: grid;
        grid-template-columns: minmax(340px, 382px) minmax(0, 1fr);
        align-items: start;
        gap: 16px;
        min-height: 0;
    }

    .pos-catalog { position: sticky; top: 16px; min-width: 0; }
    .pos-checkout { min-width: 0; }

    .pos-surface,
    .pos-shortcut-card,
    .pos-panel,
    .pos-toolbar {
        border: 1px solid var(--pos-line);
        border-radius: 18px;
        background: var(--pos-surface);
        box-shadow: 0 12px 34px rgba(34, 47, 77, .065), inset 0 1px 0 #fff;
    }

    .pos-surface { padding: 17px; }
    .pos-product-panel { position: relative; overflow: hidden; padding: 0; background: linear-gradient(180deg, #f8f9fc, #fff 30%); }

    .pos-panel-heading { position: relative; z-index: 1; min-width: 0; gap: 10px; }

    .pos-heading-icon {
        display: inline-flex;
        width: 39px;
        height: 39px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 20px;
    }

    .pos-heading-icon.is-blue { color: #465bdd; background: #edf0ff; box-shadow: inset 0 0 0 1px #e0e4ff; }
    .pos-heading-icon.is-violet { color: #8053ce; background: #f3edff; box-shadow: inset 0 0 0 1px #e9ddff; }
    .pos-heading-icon.is-amber { color: #c67a18; background: #fff4df; box-shadow: inset 0 0 0 1px #ffe9c0; }
    .pos-section-kicker { display: block; color: #96a0b1; line-height: 1.1; }
    .pos-panel-heading h2 { margin: 2px 0 0; color: var(--pos-ink); font-size: 16px; font-weight: 850; letter-spacing: -.02em; }

    .pos-catalog-hero { position: relative; display: flex; min-height: 96px; align-items: center; justify-content: space-between; gap: 12px; overflow: hidden; padding: 15px; color: #fff; background: radial-gradient(circle at 110% -20%, rgba(129, 148, 255, .65), transparent 44%), linear-gradient(125deg, #142044, #273b78); }
    .pos-catalog-hero::after { position: absolute; right: -34px; bottom: -58px; width: 130px; height: 130px; border: 1px solid rgba(255, 255, 255, .09); border-radius: 50%; content: ""; }
    .pos-catalog-hero-main { position: relative; z-index: 1; display: flex; min-width: 0; align-items: center; gap: 11px; }
    .pos-catalog-hero-main > div { min-width: 0; }
    .pos-catalog-step { display: inline-flex; width: 48px; height: 48px; flex: 0 0 auto; align-items: center; justify-content: center; color: #fff; border: 1px solid rgba(255, 255, 255, .2); border-radius: 15px; background: linear-gradient(145deg, #7183f4, #5267d9); box-shadow: 0 9px 20px rgba(7, 13, 45, .27), inset 0 1px 0 rgba(255, 255, 255, .2); font-size: 16px; font-weight: 900; }
    .pos-catalog-hero .pos-section-kicker { color: #b9c6fa; }
    .pos-catalog-hero h2 { overflow: hidden; margin: 2px 0 0; color: #fff; font-size: 16px; font-weight: 850; letter-spacing: -.02em; white-space: nowrap; text-overflow: ellipsis; }
    .pos-catalog-hero p { margin: 3px 0 0; color: #b4c0dd; font-size: 9px; line-height: 1.35; }
    .pos-catalog-ready { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 4px; margin-left: auto; padding: 5px 7px; color: #13795b; border: 1px solid #c8eadc; border-radius: 999px; background: #eefaf6; font-size: 8px; font-weight: 800; white-space: nowrap; }
    .pos-catalog-ready i { color: #12a276; font-size: 10px; }
    .pos-catalog-hero .pos-catalog-ready { position: relative; z-index: 1; color: #c5fae8; border-color: rgba(99, 231, 178, .19); background: rgba(39, 196, 136, .13); }
    .pos-catalog-hero .pos-catalog-ready i { color: #5be6ad; filter: drop-shadow(0 0 5px rgba(91, 230, 173, .55)); }

    .pos-catalog-rail { display: grid; grid-template-columns: 1fr auto 1fr auto 1fr; align-items: center; gap: 5px; margin: 11px 12px 0; padding: 8px; border: 1px solid #e2e6ef; border-radius: 12px; background: #fff; box-shadow: 0 5px 13px rgba(36, 49, 80, .045); }
    .pos-catalog-rail > i { color: #c0c8d5; font-size: 13px; }
    .pos-catalog-phase { display: flex; min-width: 0; align-items: center; gap: 6px; color: #8994a7; }
    .pos-catalog-phase > span { display: inline-flex; width: 27px; height: 27px; flex: 0 0 auto; align-items: center; justify-content: center; color: #8994aa; border: 1px solid #e2e6ee; border-radius: 8px; background: #f5f7fa; font-size: 13px; transition: all .2s ease; }
    .pos-catalog-phase small { overflow: hidden; font-size: 8px; font-weight: 800; white-space: nowrap; text-overflow: ellipsis; }
    .pos-catalog-phase.is-active { color: #4157ca; }
    .pos-catalog-phase.is-active > span { color: #fff; border-color: #5b6fe3; background: linear-gradient(145deg, #7181ed, #5267da); box-shadow: 0 5px 11px rgba(76, 94, 205, .2); }
    .pos-catalog-phase.is-complete { color: #177a5d; }
    .pos-catalog-phase.is-complete > span { color: #0b8a65; border-color: #bee8d9; background: #eaf9f3; }

    .pos-search-box {
        position: relative;
        z-index: 1;
        margin: 11px 12px 0;
        padding: 13px;
        overflow: hidden;
        border: 1px solid #cfd7fa;
        border-radius: 16px;
        background:
            radial-gradient(circle at 100% 0, rgba(112, 127, 239, .14), transparent 42%),
            linear-gradient(145deg, #f8f9ff, #f1f4ff);
        box-shadow: 0 10px 23px rgba(68, 82, 173, .09), inset 0 1px 0 #fff;
        transition: border-color .22s ease, box-shadow .22s ease, transform .22s ease;
    }

    .pos-search-box::before { position: absolute; top: 0; right: 0; left: 0; height: 3px; content: ""; background: linear-gradient(90deg, #5368e6, #8a72e6, #18aa7c); }
    .pos-search-box.is-open { border-color: #91a0ee; box-shadow: 0 0 0 4px rgba(79, 99, 233, .09), 0 14px 28px rgba(68, 82, 173, .13); transform: translateY(-1px); }
    .pos-search-box.has-selection { border-color: #a9dfcd; background: radial-gradient(circle at 100% 0, rgba(20, 171, 124, .12), transparent 40%), linear-gradient(145deg, #f8fffc, #f0faf7); }
    .pos-search-box.has-selection::before { background: linear-gradient(90deg, #0c9e70, #41c297); }

    .pos-search-topline { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 9px; }
    .pos-search-eyebrow { display: inline-flex; align-items: center; gap: 5px; color: #5a69b8; font-size: 8px; font-weight: 900; letter-spacing: .11em; text-transform: uppercase; }
    .pos-search-eyebrow i { color: #e4a12d; font-size: 11px; }
    .pos-search-shortcut { padding: 0; border: 0; background: transparent; }
    .pos-search-shortcut kbd { color: #5969b5; border-color: #ced5f4; background: rgba(255, 255, 255, .85); }

    .pos-search-title { display: block; margin: 0 0 8px; color: #263453; font-size: 12px; font-weight: 850; line-height: 1.3; }
    .pos-search-title small { display: block; margin-top: 2px; color: #8b96aa; font-size: 8px; font-weight: 600; }

    .pos-search-control { position: relative; min-width: 0; }
    .pos-search-leading { position: absolute; z-index: 4; top: 50%; left: 11px; display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; color: #fff; border-radius: 9px; background: linear-gradient(145deg, #6879ec, #4f62d8); box-shadow: 0 5px 11px rgba(70, 86, 193, .25); font-size: 17px; pointer-events: none; transform: translateY(-50%); transition: background .2s ease, box-shadow .2s ease; }
    .pos-search-box.has-selection .pos-search-leading { background: linear-gradient(145deg, #23b486, #0b936a); box-shadow: 0 5px 11px rgba(15, 159, 113, .22); }
    .pos-search-ready { position: absolute; z-index: 4; top: 50%; right: 10px; display: inline-flex; align-items: center; gap: 4px; padding: 4px 6px; color: #73809b; border: 1px solid #e2e6ef; border-radius: 999px; background: #f8f9fb; font-size: 7px; font-weight: 900; pointer-events: none; transform: translateY(-50%); }
    .pos-search-ready i { color: #18ad7d; font-size: 6px; filter: drop-shadow(0 0 4px rgba(24, 173, 125, .6)); }

    .pos-search-feedback { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-top: 9px; }
    .pos-search-feedback p { display: flex; min-width: 0; align-items: flex-start; gap: 5px; margin: 0; color: #7f8a9d; font-size: 8px; line-height: 1.4; }
    .pos-search-feedback p i { flex: 0 0 auto; margin-top: 1px; color: #6878ce; font-size: 11px; }
    .pos-search-feedback p span { min-width: 0; }
    .pos-search-feedback p.is-success { color: #17795d; }
    .pos-search-feedback p.is-success i { color: #0da576; }
    .pos-search-feedback p.is-warning { color: #ad6d1e; }
    .pos-search-feedback p.is-warning i { color: #df922e; }
    .pos-search-scope { display: flex; flex: 0 0 auto; align-items: center; gap: 4px; }
    .pos-search-scope span { display: inline-flex; align-items: center; gap: 3px; padding: 3px 5px; color: #7d87a0; border: 1px solid rgba(211, 217, 237, .88); border-radius: 6px; background: rgba(255, 255, 255, .68); font-size: 7px; font-weight: 750; }
    .pos-search-scope i { color: #6d7ccc; }

    .pos-catalog-block { margin: 11px 12px 0; overflow: hidden; border: 1px solid #e0e5ed; border-radius: 14px; background: #fff; box-shadow: 0 6px 16px rgba(35, 48, 78, .045); }
    .pos-selection-block.has-selection { border-color: #bcc7f3; box-shadow: 0 7px 19px rgba(72, 87, 177, .08); }
    .pos-catalog-block:last-child { margin-bottom: 12px; }
    .pos-catalog-block-head { display: flex; min-height: 48px; align-items: center; justify-content: space-between; gap: 9px; padding: 8px 10px; border-bottom: 1px solid #e8ebf1; background: linear-gradient(145deg, #fbfcfe, #f7f8fb); }
    .pos-catalog-block-head > div { display: flex; min-width: 0; align-items: center; gap: 8px; }
    .pos-catalog-block-head > div > span:last-child { display: grid; min-width: 0; }
    .pos-catalog-block-head small { color: #929daf; font-size: 7px; font-weight: 850; letter-spacing: .09em; }
    .pos-catalog-block-head strong { overflow: hidden; margin-top: 1px; color: #31405a; font-size: 10px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-catalog-block-icon { display: inline-flex; width: 30px; height: 30px; flex: 0 0 auto; align-items: center; justify-content: center; color: #4c61d2; border: 1px solid #dde2fa; border-radius: 9px; background: #eef1ff; font-size: 15px; }
    .pos-catalog-block-icon.is-violet { color: #7652be; border-color: #eadffc; background: #f5efff; }
    .pos-catalog-block-icon.is-emerald { color: #0a8a65; border-color: #ccebdd; background: #edf9f5; }
    .pos-block-state,
    .pos-fefo-badge { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 4px; padding: 4px 6px; color: #8792a5; border: 1px solid #e0e5ed; border-radius: 999px; background: #fff; font-size: 7px; font-weight: 800; white-space: nowrap; }
    .pos-block-state.is-ready { color: #13775a; border-color: #bde7d7; background: #ecf9f4; }
    .pos-block-state.is-ready i { color: #10a477; }
    .pos-block-number { display: inline-flex; width: 23px; height: 23px; align-items: center; justify-content: center; color: #7662a6; border-radius: 7px; background: #f0ebfb; font-size: 8px; font-weight: 900; }
    .pos-fefo-badge { color: #14775b; border-color: #c3e8d9; background: #eefaf5; }
    .pos-fefo-badge i { color: #0da677; }

    .pos-product-card {
        position: relative;
        min-height: 150px;
        margin: 12px 0;
        padding: 13px;
        overflow: hidden;
        border: 1px dashed #ced6e4;
        border-radius: 14px;
        background: #fbfcfe;
        transition: border-color .22s ease, background .22s ease, box-shadow .22s ease, transform .22s ease;
    }

    .pos-product-card.has-product { border-style: solid; border-color: #b9c4f6; background: linear-gradient(145deg, #fafbff, #f1f3ff); box-shadow: 0 9px 22px rgba(65, 81, 180, .1); }
    .pos-product-card.has-product::after { position: absolute; top: -45px; right: -45px; width: 105px; height: 105px; border-radius: 50%; content: ""; background: rgba(101, 116, 234, .09); }
    .pos-product-card.pos-pulse { animation: posProductReveal .4s ease both; }

    @keyframes posProductReveal {
        from { opacity: .55; transform: translateY(4px) scale(.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .pos-empty-state { display: flex; min-height: 122px; flex-direction: column; align-items: center; justify-content: center; gap: 5px; color: #7e899b; text-align: center; font-size: 11px; }
    .pos-empty-icon { display: inline-flex; width: 43px; height: 43px; align-items: center; justify-content: center; margin-bottom: 2px; color: #8694af; border: 1px solid #e2e7ef; border-radius: 14px; background: #f0f3f8; font-size: 21px; }
    .pos-empty-state strong { color: #526078; font-size: 12px; }

    .pos-product-name { position: relative; z-index: 1; display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .pos-product-name > div { min-width: 0; }
    .pos-product-name strong { display: block; overflow: hidden; color: #1b2946; font-size: 14px; line-height: 1.25; text-overflow: ellipsis; }
    .pos-product-name small { display: block; margin-top: 3px; font-size: 9px; }
    .pos-product-name .badge { padding: 6px 8px; border-radius: 8px; background: #5468de !important; font-size: 9px; font-weight: 800; }
    .pos-product-meta { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin-top: 10px; }
    .pos-product-meta span { min-width: 0; padding: 6px 7px; overflow-wrap: anywhere; color: #43516b; border: 1px solid #e0e5f4; border-radius: 8px; background: rgba(255, 255, 255, .75); font-size: 10px; }
    .pos-product-meta small { display: block; margin-bottom: 1px; color: #949eb0; font-size: 8px; font-weight: 800; text-transform: uppercase; }
    .pos-product-card > .mt-3 { position: relative; z-index: 1; padding-top: 8px; border-top: 1px solid rgba(215, 221, 238, .8); font-size: 9px !important; line-height: 1.45; }

    .pos-selection-block .pos-product-card { min-height: 137px; margin: 0; border: 0; border-radius: 0; background: #fcfdff; box-shadow: none; }
    .pos-selection-block .pos-product-card.has-product { background: linear-gradient(145deg, #fbfcff, #f3f5ff); }

    .pos-add-card { padding: 11px; border: 0; border-radius: 0; background: #fff; box-shadow: none; }
    .pos-add-fields { display: grid; grid-template-columns: minmax(0, 1fr) 88px; gap: 8px; }
    .pos-field { min-width: 0; }
    .pos-field label { display: block; margin-bottom: 5px; color: #536079; font-size: 10px; font-weight: 800; }
    .pos-field label i { margin-right: 2px; color: #7482c8; }
    .pos-add-button { display: flex; width: 100%; min-height: 47px; align-items: center; justify-content: space-between; gap: 8px; margin-top: 9px; padding: 7px 10px; color: #fff; border: 0; background: linear-gradient(135deg, #4f63e9, #6676e8); box-shadow: 0 8px 16px rgba(79, 99, 233, .2); font-size: 11px; text-align: left; }
    .pos-add-button-icon { display: inline-flex; width: 31px; height: 31px; flex: 0 0 auto; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, .16); border-radius: 9px; background: rgba(255, 255, 255, .1); font-size: 17px; }
    .pos-add-button > span:nth-child(2) { display: grid; flex: 1; }
    .pos-add-button > span:nth-child(2) small { color: #cdd5ff; font-size: 7px; font-weight: 650; }
    .pos-add-button > i:last-child { color: #dbe1ff; font-size: 16px; }
    .pos-add-button:hover:not(:disabled) { color: #fff; box-shadow: 0 11px 20px rgba(79, 99, 233, .27); transform: translateY(-1px); }
    .pos-add-button:disabled { cursor: not-allowed; color: #a8b0c5; background: #e9edf4; box-shadow: none; }

    .pos-quote-box { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin: 10px; overflow: hidden; border: 1px solid #e2e7ef; border-radius: 11px; background: #f9fafc; }
    .pos-quote-box > div { min-width: 0; padding: 9px; border-right: 1px solid #e2e7ef; }
    .pos-quote-box > div:last-child { border-right: 0; }
    .pos-quote-box small { display: block; color: #929cad; font-size: 8px; font-weight: 700; white-space: nowrap; }
    .pos-quote-box small i { color: #7b87c9; }
    .pos-quote-box strong { display: block; overflow: hidden; margin-top: 2px; color: #25334e; font-size: 10px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-quote-box.is-loading strong { color: transparent; border-radius: 4px; background: linear-gradient(90deg, #e9edf4 25%, #f7f8fb 45%, #e9edf4 65%); background-size: 300% 100%; animation: posShimmer 1s infinite linear; }

    @keyframes posShimmer { to { background-position: -100% 0; } }

    .pos-fefo-zone { padding: 0 10px 10px; }
    .pos-fefo-label { display: inline-flex; align-items: center; gap: 4px; color: #7d899d; font-size: 8px; font-weight: 800; }
    .pos-fefo-label i { color: #7382c8; }
    .pos-fefo-list { display: flex; min-height: 36px; flex-wrap: wrap; gap: 5px; margin-top: 5px; padding: 6px; border: 1px solid #e8ebf1; border-radius: 9px; background: #fbfcfd; }
    .pos-fefo-chip { display: inline-flex; align-items: center; gap: 4px; padding: 5px 6px; color: #5d687c; border: 1px solid #e1e6ed; border-radius: 7px; background: #fff; font-size: 9px; }
    .pos-fefo-chip i { color: #8793ab; }
    .pos-fefo-placeholder { align-self: center; color: #9aa3b2; font-size: 8px; }

    .pos-shortcut-card { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 10px; padding: 9px 11px; color: #657087; background: rgba(255, 255, 255, .8); font-size: 9px; }
    .pos-shortcut-card > span { color: #3b4861; font-weight: 850; }
    .pos-shortcut-card > span i { color: var(--pos-amber); }
    .pos-shortcut-card > div { display: flex; align-items: center; gap: 8px; }
    .pos-shortcut-card > div span { white-space: nowrap; }

    .pos-transaction-panel { overflow: hidden; padding: 0; border-color: #dce3ef; background: linear-gradient(180deg, #f9fbff, #fff 36%); }
    .pos-transaction-hero { position: relative; overflow: hidden; padding: 18px 18px 13px; color: #fff; background: radial-gradient(circle at 86% -25%, rgba(116, 138, 255, .56), transparent 35%), radial-gradient(circle at 15% 120%, rgba(37, 208, 170, .16), transparent 34%), linear-gradient(125deg, #121c38 0%, #1c2c58 55%, #273c79 100%); }
    .pos-transaction-hero::before { position: absolute; inset: 0; content: ""; opacity: .14; pointer-events: none; background-image: linear-gradient(rgba(255, 255, 255, .16) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .16) 1px, transparent 1px); background-size: 32px 32px; mask-image: linear-gradient(90deg, #000, transparent 68%); }
    .pos-transaction-orbit { position: absolute; right: -62px; top: -105px; width: 255px; height: 255px; border: 1px solid rgba(255, 255, 255, .1); border-radius: 50%; pointer-events: none; }
    .pos-transaction-orbit::before,
    .pos-transaction-orbit::after { position: absolute; border: 1px solid rgba(255, 255, 255, .075); border-radius: inherit; content: ""; }
    .pos-transaction-orbit::before { inset: 30px; }
    .pos-transaction-orbit::after { inset: 61px; }
    .pos-transaction-head { position: relative; z-index: 1; justify-content: space-between; gap: 16px; }
    .pos-transaction-heading { align-items: center; }
    .pos-transaction-number { display: inline-flex; width: 51px; height: 51px; flex: 0 0 auto; align-items: center; justify-content: center; color: #fff; border: 1px solid rgba(255, 255, 255, .22); border-radius: 16px; background: linear-gradient(145deg, rgba(131, 149, 255, .9), rgba(75, 94, 210, .75)); box-shadow: 0 11px 24px rgba(6, 12, 39, .28), inset 0 1px 0 rgba(255, 255, 255, .24); font-size: 16px; font-weight: 900; }
    .pos-transaction-heading .pos-section-kicker { display: flex; align-items: center; gap: 5px; color: #b8c6fb; }
    .pos-transaction-heading .pos-section-kicker i { color: #65e5b2; font-size: 7px; filter: drop-shadow(0 0 5px rgba(101, 229, 178, .85)); }
    .pos-transaction-heading h2 { margin-top: 3px; color: #fff; font-size: 18px; }
    .pos-transaction-heading p { margin: 3px 0 0; color: #aebbd9; font-size: 9px; }
    .pos-draft-pill { position: relative; z-index: 1; padding: 7px 10px; color: #ffe3ad; border: 1px solid rgba(255, 211, 128, .2); background: rgba(230, 155, 39, .13); backdrop-filter: blur(8px); }
    .pos-draft-pill i { color: #f3b957; font-size: 16px; line-height: 0; }
    .pos-draft-pill.is-ready { color: #c9f9e7; border-color: rgba(99, 226, 177, .22); background: rgba(22, 170, 115, .14); }
    .pos-draft-pill.is-ready i { color: #5be2ad; }
    .pos-draft-pill.is-draft { color: #ddd8ff; border-color: rgba(181, 169, 255, .22); background: rgba(119, 99, 221, .17); }
    .pos-draft-pill.is-draft i { color: #b7adff; }
    .pos-transaction-insights { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-top: 15px; }
    .pos-insight-card { display: flex; min-width: 0; align-items: center; gap: 8px; padding: 9px; border: 1px solid rgba(255, 255, 255, .1); border-radius: 12px; background: rgba(255, 255, 255, .065); box-shadow: inset 0 1px 0 rgba(255, 255, 255, .045); backdrop-filter: blur(8px); transition: border-color .18s ease, background .18s ease, transform .18s ease; }
    .pos-insight-card:hover { border-color: rgba(255, 255, 255, .18); background: rgba(255, 255, 255, .09); transform: translateY(-1px); }
    .pos-insight-card > span:last-child { min-width: 0; }
    .pos-insight-card small { display: block; color: #9dacca; font-size: 7px; font-weight: 800; letter-spacing: .09em; }
    .pos-insight-card strong { display: block; overflow: hidden; margin-top: 2px; color: #fff; font-size: 11px; font-weight: 850; white-space: nowrap; text-overflow: ellipsis; }
    .pos-insight-icon { display: inline-flex; width: 31px; height: 31px; flex: 0 0 auto; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, .11); border-radius: 10px; font-size: 16px; }
    .pos-insight-icon.is-indigo { color: #d6dcff; background: rgba(112, 132, 255, .18); }
    .pos-insight-icon.is-cyan { color: #bceefa; background: rgba(48, 185, 215, .14); }
    .pos-insight-icon.is-amber { color: #ffe0a8; background: rgba(229, 157, 42, .14); }
    .pos-insight-icon.is-emerald { color: #bdf6df; background: rgba(27, 188, 129, .14); }
    .pos-insight-card.has-warning { border-color: rgba(255, 126, 143, .24); background: rgba(211, 62, 82, .13); }
    .pos-insight-card.has-warning .pos-insight-icon { color: #ffd0d7; background: rgba(226, 82, 100, .18); }
    .pos-cart-readiness { position: relative; z-index: 1; display: grid; grid-template-columns: auto minmax(100px, 1fr) auto; align-items: center; gap: 9px; margin-top: 10px; color: #aebbd4; font-size: 8px; }
    .pos-cart-readiness > span { display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
    .pos-cart-readiness > span i { color: #92a4e9; }
    .pos-cart-readiness b { color: #d6def2; font-weight: 750; }
    .pos-cart-readiness-track { height: 4px; overflow: hidden; border-radius: 999px; background: rgba(255, 255, 255, .1); }
    .pos-cart-readiness-track span { display: block; width: 8%; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #6f82ee, #54ddae); box-shadow: 0 0 8px rgba(84, 221, 174, .42); transition: width .35s ease; }
    .pos-cart-readiness small { white-space: nowrap; }
    .pos-cart-readiness small i { color: #62ddae; }

    .pos-transaction-details { margin: 14px 15px 0; overflow: hidden; border: 1px solid #dfe5ef; border-radius: 14px; background: #fff; box-shadow: 0 7px 20px rgba(35, 48, 79, .045); }
    .pos-transaction-details summary { display: flex; min-height: 56px; align-items: center; justify-content: space-between; gap: 12px; padding: 9px 11px; color: #526079; cursor: pointer; list-style: none; transition: background .18s ease; }
    .pos-transaction-details summary::-webkit-details-marker { display: none; }
    .pos-transaction-details summary:hover { background: #fafbff; }
    .pos-detail-customer,
    .pos-detail-toggle { display: flex; min-width: 0; align-items: center; gap: 9px; }
    .pos-detail-customer > span:last-child { display: grid; min-width: 0; }
    .pos-detail-customer small { color: #9aa4b5; font-size: 7px; font-weight: 850; letter-spacing: .095em; }
    .pos-detail-customer strong { overflow: hidden; margin-top: 1px; color: #33415b; font-size: 11px; font-weight: 850; letter-spacing: 0; text-transform: none; white-space: nowrap; text-overflow: ellipsis; }
    .pos-customer-avatar { display: inline-flex; width: 36px; height: 36px; flex: 0 0 auto; align-items: center; justify-content: center; color: #4f61ca; border: 1px solid #dce2fc; border-radius: 11px; background: linear-gradient(145deg, #f3f5ff, #e7ebff); box-shadow: inset 0 1px 0 #fff; font-size: 12px; font-weight: 900; }
    .pos-detail-toggle { justify-content: flex-end; }
    .pos-detail-summary { max-width: 210px; overflow: hidden; color: #8994a7; font-size: 9px; font-weight: 650; letter-spacing: 0; text-transform: none; white-space: nowrap; text-overflow: ellipsis; }
    .pos-detail-chevron { display: inline-flex; width: 28px; height: 28px; flex: 0 0 auto; align-items: center; justify-content: center; color: #6c79a9; border: 1px solid #e2e6f0; border-radius: 9px; background: #f7f8fc; font-size: 15px; }
    .pos-detail-chevron i { transition: transform .2s ease; }
    .pos-transaction-details[open] summary { border-bottom: 1px solid #e5e9f0; background: #f8f9fd; }
    .pos-transaction-details[open] .pos-detail-chevron i { transform: rotate(180deg); }
    .pos-transaction-form-shell { padding: 11px; background: linear-gradient(180deg, #fbfcff, #fff); }
    .pos-transaction-form-intro { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; padding: 8px; border: 1px solid #e6eaf1; border-radius: 11px; background: #f8f9fc; }
    .pos-transaction-form-intro > span { display: inline-flex; width: 30px; height: 30px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5267d5; border-radius: 9px; background: #e9edff; font-size: 16px; }
    .pos-transaction-form-intro > div { display: grid; min-width: 0; flex: 1; }
    .pos-transaction-form-intro strong { color: #3a4760; font-size: 10px; }
    .pos-transaction-form-intro small { color: #8b95a6; font-size: 8px; line-height: 1.35; }
    .pos-general-customer-button { flex: 0 0 auto; padding: 6px 8px; color: #5364bd; border: 1px solid #d5dbf4; background: #fff; font-size: 9px; }
    .pos-general-customer-button:hover { color: #3f51b3; border-color: #bec8ee; background: #f2f4ff; }
    .pos-form-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 9px; padding: 0; }
    .pos-patient-search-field { grid-column: 1 / -1; }
    .pos-patient-search-hint { display: flex; align-items: center; gap: 3px; margin-top: 4px; color: #7b889c; font-size: 8px; }
    .pos-patient-search-hint i { color: #1a9b79; }
    .pos-patient-result { display: flex; align-items: center; gap: 9px; padding: 3px 1px; }
    .pos-patient-result > span { display: inline-flex; width: 30px; height: 30px; flex: 0 0 30px; align-items: center; justify-content: center; color: #5267c8; border-radius: 9px; background: #edf0ff; font-size: 15px; }
    .pos-patient-result > div { display: grid; min-width: 0; }
    .pos-patient-result strong { overflow: hidden; color: #33415b; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
    .pos-patient-result small { color: #7f8da2; font-size: 8px; }

    .pos-prescription-workspace { margin-top: 11px; overflow: hidden; border: 1px solid #cfd9f5; border-radius: 14px; background: linear-gradient(145deg, #f8faff, #f4fbfa); box-shadow: 0 8px 22px rgba(45, 65, 129, .06); transition: border-color .2s ease, box-shadow .2s ease; }
    .pos-prescription-workspace.is-ready { border-color: #a9dfce; box-shadow: 0 8px 24px rgba(24, 144, 108, .09); }
    .pos-prescription-head { display: flex; align-items: center; gap: 10px; padding: 11px 12px; color: #fff; background: radial-gradient(circle at 91% -80%, rgba(101, 229, 178, .4), transparent 46%), linear-gradient(120deg, #283968, #4256ad); }
    .pos-prescription-head-icon { display: inline-flex; width: 38px; height: 38px; flex: 0 0 auto; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, .2); border-radius: 12px; background: rgba(255, 255, 255, .1); font-size: 20px; box-shadow: inset 0 1px 0 rgba(255, 255, 255, .14); }
    .pos-prescription-head > div { display: grid; min-width: 0; flex: 1; }
    .pos-prescription-head > div small { color: #c7d2ff; font-size: 8px; font-weight: 850; letter-spacing: .09em; }
    .pos-prescription-head > div strong { margin-top: 1px; font-size: 12px; font-weight: 850; }
    .pos-prescription-head > div p { margin: 1px 0 0; color: #cbd4ee; font-size: 8px; line-height: 1.4; }
    .pos-prescription-status { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 4px; padding: 6px 8px; color: #ffe5a8; border: 1px solid rgba(255, 226, 157, .25); border-radius: 999px; background: rgba(117, 76, 15, .2); font-size: 8px; font-weight: 800; white-space: nowrap; }
    .pos-prescription-status.is-ready { color: #c9fae7; border-color: rgba(174, 244, 218, .28); background: rgba(6, 96, 70, .24); }
    .pos-prescription-mode-summary { display: flex; align-items: center; gap: 9px; margin: 10px 11px 0; padding: 9px 10px; border: 1px solid #dce3f3; border-radius: 11px; background: rgba(255, 255, 255, .82); }
    .pos-prescription-mode-icon { display: inline-flex; width: 34px; height: 34px; flex: 0 0 auto; align-items: center; justify-content: center; color: #fff; border-radius: 10px; background: linear-gradient(145deg, #6377db, #4153b6); font-size: 17px; box-shadow: 0 5px 12px rgba(65, 83, 182, .18); }
    .pos-prescription-mode-summary.is-compound .pos-prescription-mode-icon { background: linear-gradient(145deg, #9c65c5, #6950b7); }
    .pos-prescription-mode-copy { display: grid; min-width: 0; flex: 1; }
    .pos-prescription-mode-copy > small { color: #98a1b2; font-size: 7px; font-weight: 850; letter-spacing: .08em; }
    .pos-prescription-mode-copy > strong { color: #3f4d68; font-size: 10px; font-weight: 850; }
    .pos-prescription-mode-copy > span { color: #8b95a6; font-size: 8px; line-height: 1.35; }
    .pos-change-prescription-type { flex: 0 0 auto; padding: 6px 9px; color: #5062bd; border: 1px solid #ccd5f3; border-radius: 9px; background: #f6f7ff; font-size: 8px; font-weight: 800; white-space: nowrap; }
    .pos-change-prescription-type:hover { color: #fff; border-color: #5367c9; background: #5367c9; }

    .pos-prescription-payment-handoff { display: flex; align-items: center; gap: 13px; margin: 15px; padding: 16px; border: 1px solid #bfded4; border-radius: 16px; background: linear-gradient(135deg, #f1fbf7, #f7f9ff); box-shadow: 0 10px 25px rgba(27, 122, 94, .08); }
    .pos-prescription-handoff-icon { display: inline-flex; width: 48px; height: 48px; flex: 0 0 auto; align-items: center; justify-content: center; color: #fff; border-radius: 14px; background: linear-gradient(145deg, #2fb287, #168363); box-shadow: 0 8px 18px rgba(26, 137, 103, .22); font-size: 24px; }
    .pos-prescription-handoff-copy { display: grid; min-width: 0; flex: 1; }
    .pos-prescription-handoff-copy small,
    .pos-prescription-handoff-total small { color: #82938e; font-size: 8px; font-weight: 850; letter-spacing: .08em; }
    .pos-prescription-handoff-copy strong { color: #2e4d45; font-size: 13px; font-weight: 850; }
    .pos-prescription-handoff-copy span { margin-top: 2px; color: #72857f; font-size: 9px; }
    .pos-prescription-handoff-total { display: grid; flex: 0 0 auto; padding: 8px 15px; border-right: 1px solid #d7e6e1; border-left: 1px solid #d7e6e1; text-align: right; }
    .pos-prescription-handoff-total strong { color: #16805f; font-size: 16px; font-weight: 900; }
    .pos-edit-prescription-button { flex: 0 0 auto; padding: 9px 12px; color: #4b5fc0; border: 1px solid #cbd4f2; background: #fff; font-size: 9px; }
    .pos-edit-prescription-button:hover { color: #fff; border-color: #5267ca; background: #5267ca; }
    .pos-workspace.is-prescription-payment-mode { grid-template-columns: minmax(0, 1180px); justify-content: center; }
    .pos-workspace.is-prescription-payment-mode > .pos-catalog { display: none; }
    .pos-transaction-panel.is-prescription-handoff > .pos-transaction-hero,
    .pos-transaction-panel.is-prescription-handoff > .pos-transaction-details,
    .pos-transaction-panel.is-prescription-handoff > .pos-cart-toolbar,
    .pos-transaction-panel.is-prescription-handoff > .pos-cart-wrap,
    .pos-transaction-panel.is-prescription-handoff > .pos-cart-footnote { display: none; }

    .pos-prescription-type-modal { --pos-ink: #152039; --pos-muted: #748197; --pos-line: #e3e8f1; --pos-primary: #4f63e9; --pos-primary-dark: #3548ca; --pos-emerald: #0fa776; --pos-surface: #fff; z-index: 1095; font-family: "Segoe UI", Roboto, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; }
    .pos-prescription-type-modal .btn,
    .pos-prescription-type-modal button,
    .pos-prescription-type-modal input,
    .pos-prescription-type-modal select { font-family: inherit; }
    .pos-prescription-type-modal .btn { border-radius: 11px; font-weight: 750; box-shadow: none; }
    body > .select2-container--open { z-index: 1110 !important; }
    .pos-prescription-cashier-step > .select2-container--open { z-index: 1110 !important; }
    .swal2-container { z-index: 1200 !important; }
    .pos-prescription-type-modal .modal-dialog { width: 100%; max-width: none; height: 100%; margin: 0; }
    .pos-prescription-type-modal .modal-content { min-height: 100vh; overflow: hidden; border: 0; border-radius: 0; background: #eef2f8; box-shadow: none; }
    .pos-prescription-type-step { display: grid; min-height: 100vh; place-items: center; padding: 24px; background: radial-gradient(circle at 14% 0, rgba(90, 108, 218, .16), transparent 28rem), linear-gradient(145deg, #e9eef8, #f7f9fc); }
    .pos-prescription-type-step-shell { width: min(900px, 100%); overflow: hidden; border-radius: 22px; background: #fff; box-shadow: 0 24px 70px rgba(24, 34, 72, .22); }
    .pos-prescription-type-step .modal-header { align-items: flex-start; padding: 22px 24px; border: 0; color: #fff; background: radial-gradient(circle at 90% -70%, rgba(94, 226, 178, .42), transparent 48%), linear-gradient(120deg, #273866, #4256ad); }
    .pos-prescription-type-modal .btn-close { margin-top: 2px; opacity: .8; }
    .pos-prescription-modal-heading { display: flex; min-width: 0; align-items: center; gap: 13px; }
    .pos-prescription-modal-heading > span { display: inline-flex; width: 46px; height: 46px; flex: 0 0 auto; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, .2); border-radius: 14px; background: rgba(255, 255, 255, .1); font-size: 24px; }
    .pos-prescription-modal-heading > div { display: grid; min-width: 0; }
    .pos-prescription-modal-heading small { color: #c8d2ff; font-size: 9px; font-weight: 850; letter-spacing: .1em; }
    .pos-prescription-modal-heading h2 { margin-top: 2px; color: #fff; font-size: 19px; font-weight: 850; }
    .pos-prescription-modal-heading p { margin: 3px 0 0; color: #d1d9ef; font-size: 11px; line-height: 1.5; }
    .pos-prescription-type-step .modal-body { padding: 24px; background: #f7f9fd; }
    .pos-prescription-modal-intro { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; padding: 11px 12px; border: 1px solid #dfe5f2; border-radius: 12px; background: #fff; }
    .pos-prescription-modal-intro > span { display: inline-flex; width: 34px; height: 34px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5368ce; border-radius: 10px; background: #edf1ff; font-size: 18px; }
    .pos-prescription-modal-intro > div { display: grid; }
    .pos-prescription-modal-intro strong { color: #3f4d67; font-size: 12px; }
    .pos-prescription-modal-intro small { margin-top: 1px; color: #8b95a6; font-size: 10px; }
    .pos-prescription-type-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; }
    .pos-prescription-type-option { position: relative; display: flex; min-width: 0; align-items: flex-start; gap: 12px; min-height: 176px; padding: 18px; color: #536079; text-align: left; border: 2px solid #e0e5ef; border-radius: 16px; background: #fff; transition: border-color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease; }
    .pos-prescription-type-option:hover { border-color: #bdc8ee; background: #fbfcff; box-shadow: 0 10px 25px rgba(57, 70, 119, .08); transform: translateY(-2px); }
    .pos-prescription-type-icon { display: inline-flex; width: 48px; height: 48px; flex: 0 0 auto; align-items: center; justify-content: center; color: #6979d3; border-radius: 14px; background: #eef1ff; font-size: 24px; }
    .pos-prescription-type-copy { display: grid; min-width: 0; flex: 1; align-content: start; }
    .pos-prescription-type-copy > small { color: #9099ac; font-size: 8px; font-weight: 850; letter-spacing: .08em; }
    .pos-prescription-type-copy > strong { margin-top: 3px; color: #33415e; font-size: 15px; font-weight: 850; }
    .pos-prescription-type-copy > span:not(.pos-prescription-type-points) { margin-top: 6px; color: #7f8a9d; font-size: 10px; line-height: 1.55; }
    .pos-prescription-type-points { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 12px; }
    .pos-prescription-type-points b { display: inline-flex; align-items: center; gap: 3px; padding: 4px 7px; color: #61708d; border-radius: 999px; background: #f2f4f8; font-size: 8px; font-weight: 750; }
    .pos-prescription-type-points i { color: #24a579; }
    .pos-prescription-type-check { position: absolute; top: 13px; right: 13px; color: #d5dbea; font-size: 20px; }
    .pos-prescription-type-option.is-active { border-color: #8697e2; background: linear-gradient(145deg, #f2f5ff, #fff); box-shadow: 0 10px 26px rgba(67, 84, 168, .12); }
    .pos-prescription-type-option.is-active .pos-prescription-type-icon { color: #fff; background: linear-gradient(145deg, #6377db, #4153b6); }
    .pos-prescription-type-option.is-active .pos-prescription-type-check { color: #22a477; }
    .pos-prescription-modal-help { display: flex; align-items: center; gap: 8px; margin-top: 14px; padding: 9px 11px; color: #6f7890; border: 1px dashed #cfd7e9; border-radius: 10px; background: #fbfcff; font-size: 9px; }
    .pos-prescription-modal-help > i { color: #d69a2e; font-size: 17px; }
    .pos-prescription-modal-help strong { color: #46536b; }
    .pos-prescription-type-step .modal-footer { justify-content: space-between; padding: 14px 24px; border-color: #e5e9f0; background: #fff; }
    .pos-prescription-type-step .modal-footer small { color: #929bad; font-size: 9px; }
    .pos-prescription-modal-cancel { color: #657087; border: 1px solid #d9dfe9; background: #fff; font-size: 10px; font-weight: 750; }
    .pos-prescription-modal-cancel:hover { color: #46536b; background: #f5f7fa; }

    .pos-prescription-cashier-step { position: relative; display: grid; height: 100vh; min-height: 0; grid-template-rows: auto minmax(0, 1fr) auto; color: #17223c; background: #eef2f8; font-size: 12px; line-height: 1.4; }
    .pos-prescription-cashier-step small { font-size: 10px !important; line-height: 1.4; }
    .pos-prescription-cashier-step label,
    .pos-prescription-cashier-step label > span { font-size: 10px !important; font-weight: 700; }
    .pos-prescription-cashier-step .btn { font-size: 10px !important; }
    .pos-prescription-cashier-step .form-control,
    .pos-prescription-cashier-step .form-select,
    .pos-prescription-cashier-step .input-group-text { font-size: 11px !important; font-weight: 400; line-height: 1.35; }
    .pos-prescription-cashier-step .pos-cart-table thead th { font-size: 10px; }
    .pos-prescription-cashier-step .pos-cart-table tbody td { font-size: 10px; }
    .pos-prescription-cashier-step .pos-section-kicker,
    .pos-prescription-cashier-step .pos-search-eyebrow,
    .pos-prescription-cashier-step .pos-fefo-label,
    .pos-prescription-cashier-step .pos-catalog-block-head small,
    .pos-prescription-cashier-step .pos-prescription-type-copy > small { font-size: 10px; }
    .pos-prescription-cashier-step .pos-item-copy strong,
    .pos-prescription-cashier-step .pos-cart-title strong,
    .pos-prescription-cashier-step .pos-prescription-item-head strong,
    .pos-prescription-cashier-step .pos-compound-group-copy strong { font-size: 11px !important; }
    .pos-prescription-cashier-step .pos-signa-chip,
    .pos-prescription-cashier-step .pos-compound-summary-chip { font-size: 9px !important; }
    .pos-prescription-cashier-header { display: grid; grid-template-columns: minmax(300px, .9fr) minmax(430px, 1.2fr) auto; align-items: center; gap: 18px; padding: 12px 16px; color: #fff; background: radial-gradient(circle at 78% -90%, rgba(117, 228, 189, .32), transparent 36%), linear-gradient(118deg, #111a34, #213266 60%, #304886); box-shadow: 0 8px 24px rgba(24, 38, 77, .18); }
    .pos-prescription-cashier-brand { display: flex; min-width: 0; align-items: center; gap: 10px; }
    .pos-prescription-cashier-brand > span { display: inline-flex; width: 42px; height: 42px; flex: 0 0 auto; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, .18); border-radius: 13px; background: rgba(255, 255, 255, .1); font-size: 22px; }
    .pos-prescription-cashier-brand > div { display: grid; min-width: 0; }
    .pos-prescription-cashier-brand small { color: #bac7ef; font-size: 8px; font-weight: 850; letter-spacing: .1em; }
    .pos-prescription-cashier-brand strong { margin-top: 1px; font-size: 14px; font-weight: 850; }
    .pos-prescription-cashier-brand p { overflow: hidden; margin: 1px 0 0; color: #c7d1e9; font-size: 8px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-prescription-flow-steps { display: flex; min-width: 0; align-items: center; justify-content: center; gap: 8px; }
    .pos-prescription-flow-steps span { display: inline-flex; align-items: center; gap: 5px; color: #b8c4df; font-size: 9px; font-weight: 750; white-space: nowrap; }
    .pos-prescription-flow-steps span b { display: inline-flex; width: 24px; height: 24px; align-items: center; justify-content: center; color: #cad4ee; border: 1px solid rgba(255, 255, 255, .16); border-radius: 8px; background: rgba(255, 255, 255, .08); font-size: 9px; }
    .pos-prescription-flow-steps span.is-active { color: #fff; }
    .pos-prescription-flow-steps span.is-active b { color: #fff; border-color: #6479db; background: #5267ca; }
    .pos-prescription-flow-steps > i { color: #7f8dac; }
    .pos-prescription-flow-cancel { padding: 8px 10px; color: #f1d5da; border: 1px solid rgba(255, 180, 190, .2); background: rgba(185, 55, 76, .14); font-size: 9px; white-space: nowrap; }
    .pos-prescription-flow-cancel:hover { color: #fff; border-color: rgba(255, 190, 198, .35); background: rgba(201, 62, 84, .28); }
    .pos-prescription-cashier-body { min-height: 0; overflow: hidden; padding: 12px; }
    .pos-prescription-cashier-layout { display: grid; height: 100%; min-height: 0; grid-template-columns: minmax(330px, 390px) minmax(0, 1fr); gap: 12px; }
    .pos-prescription-modal-catalog,
    .pos-prescription-modal-editor { min-width: 0; overflow: auto; border: 1px solid #dde3ed; border-radius: 16px; background: #f8f9fc; box-shadow: 0 10px 28px rgba(34, 47, 77, .07); scrollbar-width: thin; scrollbar-color: #c5cedc transparent; }
    .pos-prescription-modal-catalog { align-self: stretch; }
    .pos-prescription-modal-catalog > .pos-catalog { position: static; top: auto; }
    .pos-prescription-modal-catalog .pos-shortcut-card { margin-bottom: 0; }
    .pos-prescription-modal-editor { padding-bottom: 8px; }
    .pos-prescription-modal-editor .pos-transaction-details { margin: 12px; }
    .pos-prescription-modal-editor .pos-transaction-details[open] summary { position: sticky; z-index: 4; top: 0; }
    .pos-prescription-modal-editor .pos-transaction-type-field { display: none; }
    .pos-prescription-modal-editor .pos-cart-toolbar { margin-top: 14px; }
    .pos-prescription-modal-editor .pos-cart-wrap { min-height: 310px; max-height: none; }
    .pos-prescription-modal-editor .pos-cart-footnote { margin-bottom: 6px; }
    .pos-prescription-cashier-footer { display: grid; min-height: 76px; grid-template-columns: minmax(260px, 1fr) auto minmax(245px, auto); align-items: center; gap: 18px; padding: 10px 16px; border-top: 1px solid #dce2eb; background: #fff; box-shadow: 0 -10px 26px rgba(34, 47, 77, .08); }
    .pos-prescription-flow-readiness { display: flex; min-width: 0; align-items: center; gap: 9px; }
    .pos-prescription-flow-readiness > span { display: inline-flex; width: 38px; height: 38px; flex: 0 0 auto; align-items: center; justify-content: center; color: #b67a22; border-radius: 11px; background: #fff5df; font-size: 20px; }
    .pos-prescription-flow-readiness > div { display: grid; min-width: 0; }
    .pos-prescription-flow-readiness small { color: #919bac; font-size: 7px; font-weight: 850; letter-spacing: .09em; }
    .pos-prescription-flow-readiness strong { overflow: hidden; color: #4d596e; font-size: 10px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-prescription-flow-readiness.is-ready > span { color: #16805f; background: #eaf9f3; }
    .pos-prescription-flow-readiness.is-ready strong { color: #17785b; }
    .pos-prescription-flow-metrics { display: flex; align-items: center; gap: 8px; }
    .pos-prescription-flow-metrics > span { display: grid; min-width: 110px; padding: 7px 12px; border: 1px solid #e0e5ee; border-radius: 11px; background: #f8f9fc; }
    .pos-prescription-flow-metrics small { color: #919bad; font-size: 7px; font-weight: 850; letter-spacing: .06em; }
    .pos-prescription-flow-metrics strong { margin-top: 1px; color: #34425c; font-size: 11px; }
    .pos-prescription-flow-actions { display: flex; align-items: center; justify-content: flex-end; gap: 7px; }
    .pos-prescription-draft-button { min-height: 45px; padding: 8px 10px; color: #5263ae; border: 1px solid #cfd7ef; background: #f7f8ff; font-size: 9px; }
    .pos-prescription-draft-button:hover { color: #fff; border-color: #5267ca; background: #5267ca; }
    .pos-finish-prescription-button { display: flex; min-width: 245px; align-items: center; justify-content: space-between; gap: 14px; padding: 9px 12px 9px 15px; color: #fff; border: 0; background: linear-gradient(135deg, #6474bd, #4c5c9f); box-shadow: 0 9px 20px rgba(66, 82, 150, .2); }
    .pos-finish-prescription-button.is-ready { background: linear-gradient(135deg, #159a70, #087c5a); box-shadow: 0 9px 20px rgba(10, 132, 94, .23); }
    .pos-finish-prescription-button:hover { color: #fff; box-shadow: 0 12px 25px rgba(54, 70, 139, .28); transform: translateY(-1px); }
    .pos-finish-prescription-button.is-ready:hover { box-shadow: 0 12px 25px rgba(10, 132, 94, .3); }
    .pos-finish-prescription-button > span { display: grid; text-align: left; font-size: 10px; }
    .pos-finish-prescription-button small { color: #baf1df; font-size: 7px; font-weight: 650; }
    .pos-finish-prescription-button > i { font-size: 18px; }
    .pos-prescription-mode-note { display: flex; align-items: center; gap: 8px; margin: 8px 11px 0; padding: 8px 9px; border: 1px solid #dce3f3; border-radius: 10px; background: rgba(255, 255, 255, .76); }
    .pos-prescription-mode-note > span { display: inline-flex; width: 27px; height: 27px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5368ce; border-radius: 8px; background: #edf1ff; font-size: 14px; }
    .pos-prescription-mode-note > div { display: grid; min-width: 0; }
    .pos-prescription-mode-note strong { color: #46536a; font-size: 9px; }
    .pos-prescription-mode-note small { color: #8a94a5; font-size: 8px; line-height: 1.35; }
    .pos-compound-setup { margin: 8px 11px 0; overflow: hidden; border: 1px solid #d8def0; border-radius: 12px; background: #fff; box-shadow: 0 5px 14px rgba(55, 68, 117, .045); }
    .pos-compound-steps { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 7px 9px; color: #6d7890; border-bottom: 1px solid #e5e9f2; background: linear-gradient(90deg, #f6f8ff, #f6fcfa); font-size: 8px; font-weight: 750; }
    .pos-compound-steps span { display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
    .pos-compound-steps b { display: inline-flex; width: 18px; height: 18px; align-items: center; justify-content: center; color: #fff; border-radius: 999px; background: #5669cd; font-size: 8px; }
    .pos-compound-steps > i { color: #a6afc1; }
    .pos-compound-controls { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(210px, .6fr); gap: 10px; padding: 9px; }
    .pos-compound-controls > label { display: grid; min-width: 0; gap: 4px; margin: 0; }
    .pos-compound-controls > label > span { color: #4f5d74; font-size: 8px; font-weight: 800; }
    .pos-compound-controls > label > small { color: #929baa; font-size: 7px; }
    .pos-compound-group-control > div { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px; }
    .pos-compound-controls .form-select,
    .pos-compound-controls .form-control,
    .pos-compound-controls .input-group-text { min-height: 32px; border-color: #d9dfeb; font-size: 9px !important; }
    .pos-new-compound-group { min-height: 32px; padding: 5px 8px; color: #4c60c0; border: 1px solid #ccd5f3; background: #f5f7ff; font-size: 8px; font-weight: 800; white-space: nowrap; }
    .pos-new-compound-group:hover { color: #fff; border-color: #566bd0; background: #566bd0; }
    .pos-embalase-control .input-group-text { color: #536079; background: #f5f7fa; }
    .pos-compound-group-summary { display: flex; min-height: 34px; flex-wrap: wrap; align-items: center; gap: 5px; padding: 7px 9px; border-top: 1px solid #e8ebf2; background: #fafbfe; }
    .pos-compound-summary-empty { color: #8a94a5; font-size: 8px; }
    .pos-compound-summary-chip { display: inline-flex; align-items: center; gap: 5px; padding: 4px 7px; color: #667188; border: 1px solid #dfe4ed; border-radius: 999px; background: #fff; font-size: 8px; }
    button.pos-compound-summary-chip { cursor: pointer; }
    .pos-compound-summary-chip b { color: #4659b8; }
    .pos-compound-summary-chip.is-active { color: #fff; border-color: #5267ca; background: #5267ca; box-shadow: 0 3px 9px rgba(67, 84, 168, .18); }
    .pos-compound-summary-chip.is-active b { color: #fff; }
    .pos-prescription-form-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 9px; padding: 10px 11px; }
    .pos-prescription-form-grid .pos-field label b,
    .pos-prescription-item-grid label > span b { color: #d45463; }
    .pos-prescription-guidance { display: flex; align-items: center; gap: 8px; padding: 8px 11px; border-top: 1px solid #e0e6f2; background: rgba(255, 255, 255, .72); }
    .pos-prescription-guidance > span:first-child { display: inline-flex; width: 28px; height: 28px; flex: 0 0 auto; align-items: center; justify-content: center; color: #208b6c; border-radius: 8px; background: #e9f8f3; font-size: 15px; }
    .pos-prescription-guidance > div { display: grid; min-width: 0; flex: 1; }
    .pos-prescription-guidance strong { color: #435168; font-size: 9px; }
    .pos-prescription-guidance small { color: #8b95a5; font-size: 8px; }
    .pos-prescription-counter { flex: 0 0 auto; padding: 5px 7px; color: #5867b4; border: 1px solid #d9def3; border-radius: 999px; background: #f5f6ff; font-size: 8px; font-weight: 800; }

    .pos-cart-toolbar { justify-content: space-between; gap: 10px; margin: 16px 15px 9px; }
    .pos-cart-title { min-width: 0; gap: 8px; }
    .pos-cart-title > span:last-child { display: grid; min-width: 0; }
    .pos-cart-title strong { color: #34425c; font-size: 11px; font-weight: 850; }
    .pos-cart-title small { overflow: hidden; color: #919bad; font-size: 8px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-cart-title-icon { display: inline-flex; width: 32px; height: 32px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5367d5; border: 1px solid #dfe4f9; border-radius: 10px; background: #f0f2ff; font-size: 16px; }
    .pos-cart-actions { display: flex; align-items: center; gap: 7px; }
    .pos-live-label { display: inline-flex; align-items: center; gap: 4px; padding: 5px 7px; color: #168261; border: 1px solid #c9eadf; border-radius: 999px; background: #effaf6; font-size: 8px; font-weight: 850; letter-spacing: .06em; white-space: nowrap; }
    .pos-live-label i { color: var(--pos-emerald); filter: drop-shadow(0 0 4px rgba(15, 167, 118, .35)); }
    .pos-clear-button { padding: 6px 8px; color: #c94e5e; border: 1px solid #f0d0d5; background: #fff; font-size: 9px; }
    .pos-clear-button:hover { color: #b13e4d; border-color: #edbbc3; background: #fff6f7; }

    .pos-cart-wrap { min-height: 250px; max-height: 42vh; margin: 0 15px; overflow: auto; border: 1px solid #dfe5ee; border-radius: 14px; background: #f4f6fa; box-shadow: inset 0 1px 5px rgba(35, 48, 78, .035); scrollbar-width: thin; scrollbar-color: #cbd3e0 transparent; }
    .pos-cart-wrap.is-updated { animation: posCartUpdate .35s ease; }
    @keyframes posCartUpdate { 50% { border-color: #aebaf1; box-shadow: 0 0 0 3px rgba(79, 99, 233, .07), inset 0 1px 5px rgba(35, 48, 78, .035); } }
    .pos-cart-table { min-width: 1030px; margin-bottom: 0; border-collapse: separate; border-spacing: 0 7px; }
    .pos-cart-table thead th { position: sticky; z-index: 2; top: 0; padding: 10px 9px 8px; color: #7d899d; border: 0; background: rgba(244, 246, 250, .96); box-shadow: 0 1px 0 #dfe4ec; font-size: 8px; font-weight: 850; letter-spacing: .07em; text-transform: uppercase; backdrop-filter: blur(9px); }
    .pos-cart-table thead th:first-child { padding-left: 13px; }
    .pos-cart-table thead i { margin-right: 3px; color: #7a88c4; font-size: 11px; }
    .pos-cart-table td { padding: 10px 9px; border-top: 1px solid #e4e8ef; border-bottom: 1px solid #e4e8ef; background: #fff; vertical-align: middle; transition: border-color .18s ease, background .18s ease; }
    .pos-cart-table td:first-child { padding-left: 11px; border-left: 1px solid #e4e8ef; border-radius: 11px 0 0 11px; }
    .pos-cart-table td:last-child { border-right: 1px solid #e4e8ef; border-radius: 0 11px 11px 0; }
    .pos-cart-table tbody tr:not(.pos-cart-empty-row):hover td { border-color: #d8def0; background: #fafbff; }
    .pos-cart-table tbody tr.is-unavailable td { border-color: #f1cdd2; background: #fff8f9; }
    .pos-cart-table .pos-prescription-detail-row td { padding: 0; border-color: #dce4f1; border-radius: 12px; background: transparent; }
    .pos-cart-table .pos-prescription-detail-row:hover td { border-color: #cbd7eb; background: transparent; }
    .pos-cart-table .pos-compound-group-row td { padding: 0; border: 0; border-radius: 12px; background: transparent; }
    .pos-cart-table .pos-compound-group-row:hover td { border: 0; background: transparent; }
    .pos-compound-group-card { overflow: hidden; border: 1px solid #cad5ef; border-left: 4px solid #596dce; border-radius: 12px; background: linear-gradient(145deg, #f5f7ff, #fff); box-shadow: 0 5px 14px rgba(48, 61, 114, .05); }
    .pos-compound-group-card.is-complete { border-color: #b7e0d3; border-left-color: #24a579; background: linear-gradient(145deg, #f3fcf8, #fff); }
    .pos-compound-group-head { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-bottom: 1px solid #e1e6f1; }
    .pos-compound-group-mark { display: inline-flex; min-width: 45px; height: 29px; align-items: center; justify-content: center; padding: 0 8px; color: #fff; border-radius: 8px; background: linear-gradient(145deg, #6679db, #4659b8); font-size: 10px; font-weight: 900; }
    .pos-compound-group-copy { display: grid; min-width: 0; flex: 1; }
    .pos-compound-group-copy strong { color: #3f4d65; font-size: 9px; }
    .pos-compound-group-copy small { color: #8b95a6; font-size: 8px; }
    .pos-use-compound-group { padding: 4px 7px; color: #5265c2; border: 1px solid #d3daf1; border-radius: 999px; background: #fff; font-size: 8px; font-weight: 800; }
    .pos-use-compound-group.is-active { color: #16805f; border-color: #bfe4d8; background: #eefaf6; }
    .pos-compound-group-fields { display: grid; grid-template-columns: 2fr 1.1fr .65fr 1.2fr; gap: 7px; padding: 8px 10px 9px; }
    .pos-compound-group-fields label { display: grid; min-width: 0; gap: 3px; margin: 0; }
    .pos-compound-group-fields label > span { color: #667287; font-size: 8px; font-weight: 750; }
    .pos-compound-group-fields label > span b { color: #d45463; }
    .pos-compound-group-fields .form-control,
    .pos-compound-group-fields .form-select { min-height: 31px; padding-top: 5px; padding-bottom: 5px; border-color: #dbe2ed; border-radius: 8px; background-color: #fff; font-size: 9px !important; }
    .pos-compound-group-card .pos-signa-quick { padding: 0 10px 9px; margin-top: -2px; }
    .pos-prescription-item-editor { padding: 10px 11px; border-left: 3px solid #e1a544; border-radius: 11px; background: linear-gradient(145deg, #fffdf7, #fff); }
    .pos-prescription-item-editor.is-complete { border-left-color: #24a579; background: linear-gradient(145deg, #f7fdfa, #fff); }
    .pos-prescription-item-head { display: flex; align-items: center; gap: 7px; }
    .pos-prescription-item-head > span:first-child { display: inline-flex; width: 29px; height: 29px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5367ca; border-radius: 8px; background: #edf1ff; font-size: 15px; }
    .pos-prescription-item-head > span:nth-child(2) { display: grid; min-width: 0; flex: 1; }
    .pos-prescription-item-head strong { color: #3f4d65; font-size: 9px; font-weight: 850; }
    .pos-prescription-item-head small { color: #9099a8; font-size: 8px; }
    .pos-prescription-item-state { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 4px; color: #a66c18; font-size: 8px; font-weight: 800; }
    .is-complete .pos-prescription-item-state { color: #16805f; }
    .pos-prescription-item-grid { display: grid; grid-template-columns: 2fr 1.2fr .7fr 1.3fr; gap: 7px; margin-top: 8px; }
    .pos-prescription-item-grid.is-compound { grid-template-columns: .75fr 1fr 1.7fr 1.05fr .65fr 1.2fr; }
    .pos-prescription-item-grid.is-compound-component { grid-template-columns: minmax(160px, .45fr) minmax(220px, .75fr) minmax(240px, 1.2fr); }
    .pos-prescription-item-grid label { display: grid; min-width: 0; gap: 3px; margin: 0; }
    .pos-prescription-item-grid label > span { color: #6b7688; font-size: 8px; font-weight: 750; }
    .pos-prescription-item-grid .form-control,
    .pos-prescription-item-grid .form-select { min-height: 31px; padding-top: 5px; padding-bottom: 5px; border-color: #dbe2ed; border-radius: 8px; background-color: #fff; font-size: 9px !important; }
    .pos-compound-component-help { align-self: end; padding: 7px 8px; color: #758096; border: 1px dashed #d8deea; border-radius: 8px; background: #fafbfc; font-size: 8px; line-height: 1.35; }
    .pos-item-badges .pos-item-group-badge { color: #485cb9; border-color: #d5dcf4; background: #f2f4ff; font-weight: 800; }
    .pos-signa-quick { display: flex; align-items: center; gap: 5px; margin-top: 7px; }
    .pos-signa-quick > span { color: #8a94a4; font-size: 8px; }
    .pos-signa-chip { padding: 3px 6px; color: #5968af; border: 1px solid #d8def1; border-radius: 999px; background: #f7f8ff; font-size: 8px; transition: border-color .16s ease, background .16s ease; }
    .pos-signa-chip:hover { border-color: #b8c3e9; background: #eef1ff; }
    .pos-embalase-total { color: #5f6b80; }
    .pos-embalase-total i { color: #6b79bf; }
    .pos-item-cell { display: flex; min-width: 0; align-items: center; gap: 8px; }
    .pos-item-symbol { display: inline-flex; width: 34px; height: 34px; flex: 0 0 auto; align-items: center; justify-content: center; color: #586bd1; border: 1px solid #e0e5fa; border-radius: 10px; background: linear-gradient(145deg, #f4f6ff, #e9edff); font-size: 17px; }
    .is-unavailable .pos-item-symbol { color: #c34d5d; border-color: #f1d1d6; background: #fff0f2; }
    .pos-item-copy { display: grid; min-width: 0; }
    .pos-item-copy strong { overflow: hidden; max-width: 230px; color: #26344f; font-size: 10px; line-height: 1.25; white-space: nowrap; text-overflow: ellipsis; }
    .pos-item-copy > small { margin-top: 2px; color: #8c96a8; font-size: 8px; }
    .pos-cart-unit-editor { display: flex; max-width: 230px; align-items: center; gap: 5px; margin: 5px 0 0; }
    .pos-cart-unit-editor > i { flex: 0 0 auto; color: #6374c8; font-size: 13px; }
    .pos-cart-unit-editor select.form-select { min-width: 0; height: 29px; padding: 3px 27px 3px 7px; border-color: #d8deeb; border-radius: 7px; background-color: #f9faff; color: #45536d; font-size: 8px; font-weight: 750; box-shadow: none; }
    .pos-cart-unit-editor select.form-select:focus { border-color: #8492d6; box-shadow: 0 0 0 2px rgba(92, 110, 201, .1); }
    .pos-cart-unit-editor select.form-select:disabled { color: #788398; background-color: #f1f3f6; opacity: .72; cursor: not-allowed; }
    .pos-item-badges { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
    .pos-item-badges span { padding: 2px 5px; color: #68758b; border: 1px solid #e4e7ed; border-radius: 5px; background: #f8f9fb; font-size: 7px; }
    .pos-money { display: block; color: #283750; font-size: 10px; font-weight: 850; white-space: nowrap; }
    .pos-money-note { display: block; margin-top: 2px; color: #98a1b0; font-size: 7px; white-space: nowrap; }
    .pos-line-total { color: #344ac1; font-size: 11px; }
    .pos-qty-stepper { display: grid; grid-template-columns: 29px minmax(52px, 1fr) 29px; overflow: hidden; border: 1px solid #dbe1eb; border-radius: 9px; background: #fff; }
    .pos-qty-stepper button { display: inline-flex; min-height: 31px; align-items: center; justify-content: center; padding: 0; color: #63718a; border: 0; background: #f5f7fa; font-size: 14px; transition: color .16s ease, background .16s ease; }
    .pos-qty-stepper button:hover { color: #4056ca; background: #ecefff; }
    .pos-qty-stepper input.form-control { min-width: 0; height: 31px; padding: 4px; border: 0; border-right: 1px solid #e2e6ed; border-left: 1px solid #e2e6ed; border-radius: 0; box-shadow: none; font-size: 9px; font-weight: 800; text-align: center; }
    .pos-stock-caption { display: flex; align-items: center; gap: 3px; margin-top: 4px; color: #8b95a5; font-size: 7px; }
    .pos-stock-caption i { color: #17a678; }
    .pos-discount-editor { display: grid; grid-template-columns: .72fr 1.28fr; gap: 5px; }
    .pos-discount-control { position: relative; }
    .pos-discount-control input.form-control { min-width: 0; height: 31px; padding: 5px 22px 5px 7px; font-size: 9px; }
    .pos-discount-control.is-nominal input.form-control { padding: 5px 6px 5px 24px; }
    .pos-discount-control span { position: absolute; top: 50%; right: 7px; color: #8b96a8; font-size: 8px; font-weight: 800; transform: translateY(-50%); pointer-events: none; }
    .pos-discount-control.is-nominal span { right: auto; left: 7px; }
    .pos-discount-result { display: block; margin-top: 3px; color: #15906a; font-size: 7px; }
    .pos-fefo-stack { display: grid; gap: 3px; }
    .pos-fefo-allocation { display: flex; align-items: center; gap: 5px; padding: 4px 5px; color: #657187; border: 1px solid #e3e7ee; border-radius: 7px; background: #f8f9fb; font-size: 7px; }
    .pos-fefo-allocation > i { color: #6678d2; font-size: 12px; }
    .pos-fefo-allocation > span { display: grid; min-width: 0; }
    .pos-fefo-allocation b { overflow: hidden; color: #4a5871; font-size: 7px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-fefo-allocation small { color: #929cad; font-size: 6px; }
    .pos-stock-warning { display: inline-flex; align-items: center; gap: 4px; padding: 5px 6px; color: #b94757; border: 1px solid #f0cbd1; border-radius: 7px; background: #fff0f2; font-size: 8px; font-weight: 750; }
    .pos-cart-table .pos-remove-item { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; padding: 0; color: #bd5160; border: 1px solid #efd2d6; border-radius: 9px; background: #fff; }
    .pos-cart-table .pos-remove-item:hover { color: #a13b49; border-color: #ebb9c1; background: #fff0f2; transform: translateY(-1px); }
    .pos-cart-empty-row td { padding: 0; border: 0; background: transparent; }
    .pos-cart-empty-row td:first-child,
    .pos-cart-empty-row td:last-child { border: 0; border-radius: 0; }
    .pos-cart-empty { display: flex; min-height: 235px; flex-direction: column; align-items: center; justify-content: center; gap: 5px; padding: 24px; color: #818c9e; text-align: center; background: radial-gradient(circle at 50% 52%, rgba(100, 119, 224, .075), transparent 15rem); }
    .pos-cart-empty-visual { position: relative; display: inline-flex; width: 58px; height: 58px; align-items: center; justify-content: center; margin-bottom: 4px; color: #6877c7; border: 1px solid #dce2f5; border-radius: 19px; background: linear-gradient(145deg, #f9faff, #e9edfb); box-shadow: 0 10px 24px rgba(53, 71, 132, .1), inset 0 1px 0 #fff; font-size: 25px; }
    .pos-cart-empty-visual > b { position: absolute; right: -5px; bottom: -4px; display: inline-flex; width: 23px; height: 23px; align-items: center; justify-content: center; color: #fff; border: 3px solid #f4f6fa; border-radius: 50%; background: #5267dd; font-size: 12px; }
    .pos-cart-empty strong { color: #45536d; font-size: 13px; }
    .pos-cart-empty > small { max-width: 440px; color: #8c96a7; font-size: 9px; line-height: 1.45; }
    .pos-empty-search-button { display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; padding: 7px 10px; color: #fff; border: 0; background: linear-gradient(135deg, #5368df, #6b7bea); box-shadow: 0 8px 17px rgba(79, 99, 213, .22); font-size: 9px; }
    .pos-empty-search-button:hover { color: #fff; box-shadow: 0 10px 22px rgba(79, 99, 213, .29); transform: translateY(-1px); }
    .pos-empty-search-button kbd { margin-left: 2px; color: #dce2ff; border-color: rgba(255, 255, 255, .15); background: rgba(255, 255, 255, .11); }
    .pos-cart-empty-meta { display: flex; width: auto; height: auto; flex-wrap: wrap; gap: 5px 12px; margin-top: 6px; color: #8f99aa; border: 0; border-radius: 0; background: transparent; box-shadow: none; font-size: 7px; }
    .pos-cart-empty-meta span { display: inline-flex; align-items: center; gap: 3px; }
    .pos-cart-empty-meta i { color: #7180c7; }
    .pos-cart-footnote { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 8px 15px 13px; color: #929cac; font-size: 7px; }
    .pos-cart-footnote span { display: inline-flex; align-items: center; gap: 4px; }
    .pos-cart-footnote i { color: #7180bd; font-size: 10px; }
    .pos-cart-footnote span:last-child { color: #17815f; white-space: nowrap; }

    .pos-checkout-stage {
        position: relative;
        overflow: visible;
        margin-top: 16px;
        border: 1px solid #dfe5ef;
        border-radius: 20px;
        background: #f7f9fc;
        box-shadow: 0 16px 42px rgba(35, 49, 82, .08), inset 0 1px 0 #fff;
    }

    .pos-checkout-stage::before {
        position: absolute;
        z-index: 3;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        content: "";
        background: linear-gradient(90deg, #5267df 0 54%, #13a879 54% 100%);
    }

    .pos-checkout-hero {
        position: relative;
        display: flex;
        min-height: 112px;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        overflow: hidden;
        padding: 20px 22px;
        color: #fff;
        border-radius: 19px 19px 0 0;
        background:
            radial-gradient(circle at 86% -95%, rgba(120, 143, 255, .62), transparent 44%),
            linear-gradient(120deg, #17213e 0%, #24396f 58%, #304985 100%);
    }

    .pos-checkout-hero::after {
        position: absolute;
        right: 32%;
        bottom: -108px;
        width: 230px;
        height: 170px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
        content: "";
        transform: rotate(-13deg);
    }

    .pos-checkout-title,
    .pos-checkout-hero-side,
    .pos-checkout-state,
    .pos-payment-head > div,
    .pos-payment-head-actions,
    .pos-payment-title-copy,
    .pos-payment-quick-copy,
    .pos-payment-quick-options,
    .pos-payment-coverage,
    .pos-payment-coverage-copy,
    .pos-total-heading,
    .pos-total-heading > div,
    .pos-total-heading > div > span:last-child,
    .pos-adjustment-card-head,
    .pos-tax-switch > span,
    .pos-submit-status > span:last-child {
        display: flex;
        align-items: center;
    }

    .pos-checkout-title { position: relative; z-index: 1; min-width: 0; gap: 14px; }
    .pos-checkout-title > div { min-width: 0; }
    .pos-checkout-step { display: inline-flex; width: 56px; height: 56px; flex: 0 0 auto; align-items: center; justify-content: center; color: #fff; border: 1px solid rgba(255, 255, 255, .22); border-radius: 17px; background: linear-gradient(145deg, #7183f4, #5267d9); box-shadow: 0 11px 26px rgba(5, 12, 42, .3), inset 0 1px 0 rgba(255, 255, 255, .24); font-size: 17px; font-weight: 900; }
    .pos-checkout-hero .pos-section-kicker { color: #b9c7f8; }
    .pos-checkout-title h2 { margin: 3px 0 0; color: #fff; font-size: 21px; font-weight: 850; letter-spacing: -.025em; }
    .pos-checkout-title p { margin: 3px 0 0; color: #b9c5df; font-size: 10px; line-height: 1.45; }
    .pos-checkout-hero-side { position: relative; z-index: 1; flex: 0 0 auto; gap: 16px; }
    .pos-checkout-state { gap: 6px; padding: 6px 9px; color: #f8dba8; border: 1px solid rgba(255, 215, 144, .2); border-radius: 999px; background: rgba(231, 156, 46, .14); font-size: 9px; font-weight: 750; white-space: nowrap; }
    .pos-checkout-state i { font-size: 13px; }
    .pos-checkout-stage.is-short .pos-checkout-state { color: #ffd5a3; background: rgba(232, 150, 39, .16); }
    .pos-checkout-stage.is-ready .pos-checkout-state { color: #bdf4df; border-color: rgba(102, 225, 176, .25); background: rgba(23, 178, 120, .16); }
    .pos-checkout-due { min-width: 190px; padding-left: 16px; border-left: 1px solid rgba(255, 255, 255, .16); text-align: right; }
    .pos-checkout-due small,
    .pos-checkout-due strong { display: block; }
    .pos-checkout-due small { color: #b8c5e2; font-size: 9px; }
    .pos-checkout-due strong { margin-top: 2px; color: #fff; font-size: clamp(21px, 2.1vw, 29px); font-weight: 900; letter-spacing: -.035em; }

    .pos-payment-layout { display: grid; min-width: 0; grid-template-columns: minmax(0, 1.22fr) minmax(340px, .78fr); align-items: stretch; gap: 14px; padding: 14px; }
    .pos-payment-panel,
    .pos-total-panel { min-width: 0; border: 1px solid #e1e6ef; border-radius: 16px; background: #fff; box-shadow: 0 8px 24px rgba(39, 52, 82, .055); }
    .pos-payment-panel { overflow: hidden; padding: 17px; }
    .pos-payment-head { justify-content: space-between; gap: 12px; padding-bottom: 14px; border-bottom: 1px solid #edf0f5; }
    .pos-payment-head > div:first-child { min-width: 0; gap: 10px; }
    .pos-payment-title-icon,
    .pos-total-heading-icon { display: inline-flex; width: 38px; height: 38px; flex: 0 0 auto; align-items: center; justify-content: center; color: #4c61d2; border: 1px solid #dfe4fb; border-radius: 11px; background: #f0f2ff; font-size: 19px; }
    .pos-payment-title-copy { min-width: 0; align-items: flex-start; flex-direction: column; }
    .pos-payment-title-copy strong { color: #263550; font-size: 13px; }
    .pos-payment-title-copy small { margin-top: 1px; color: #8b96a8; font-size: 8px; }
    .pos-payment-head-actions { flex: 0 0 auto; gap: 7px; }
    .pos-payment-count { padding: 5px 8px; color: #718096; border: 1px solid #e1e6ed; border-radius: 999px; background: #f7f9fb; font-size: 9px; font-weight: 700; white-space: nowrap; }
    .pos-add-payment-button { display: inline-flex; min-height: 34px; align-items: center; gap: 5px; padding: 7px 10px; color: #fff; border: 0; background: linear-gradient(135deg, #586de2, #4559cc); box-shadow: 0 7px 15px rgba(71, 89, 203, .2); font-size: 10px; }
    .pos-add-payment-button:hover { color: #fff; box-shadow: 0 9px 20px rgba(71, 89, 203, .28); transform: translateY(-1px); }

    .pos-payment-rows-head { display: grid; min-width: 0; grid-template-columns: minmax(0, .82fr) minmax(0, 1fr) minmax(0, 1fr) 36px; gap: 9px; padding: 12px 11px 5px 43px; color: #96a0b0; font-size: 8px; font-weight: 750; letter-spacing: .04em; text-transform: uppercase; }
    .pos-payment-rows { display: grid; min-width: 0; gap: 8px; }
    .pos-payment-row { position: relative; display: grid; width: 100%; min-width: 0; max-width: 100%; grid-template-columns: 30px minmax(0, .82fr) minmax(0, 1fr) minmax(0, 1fr) 36px; align-items: center; gap: 9px; padding: 10px; border: 1px solid #e3e8f0; border-radius: 13px; background: #fbfcfe; transition: border-color .18s ease, background .18s ease, box-shadow .18s ease; animation: posRowIn .25s ease both; }
    .pos-payment-row:hover { border-color: #d5dceb; background: #fff; }
    .pos-payment-row.is-active { border-color: #bdc8f3; background: #f8f9ff; box-shadow: 0 0 0 3px rgba(82, 103, 218, .07); }
    @keyframes posRowIn { from { opacity: 0; transform: translateY(-5px); } }
    .pos-payment-row-number { display: inline-flex; width: 27px; height: 27px; align-items: center; justify-content: center; color: #5c6b84; border: 1px solid #dde3eb; border-radius: 8px; background: #fff; font-size: 9px; font-weight: 800; }
    .pos-payment-field { position: relative; display: block; min-width: 0; }
    .pos-payment-field > label { display: none; margin-bottom: 4px; color: #7d889a; font-size: 8px; font-weight: 700; }
    .pos-payment-field .form-control,
    .pos-payment-field .form-select { width: 100%; min-width: 0; max-width: 100%; height: 37px; min-height: 37px; border-color: #dce2eb; border-radius: 9px; background-color: #fff; font-size: 10px; }
    .pos-payment-field .form-control:focus,
    .pos-payment-field .form-select:focus { border-color: #9eacf0; box-shadow: 0 0 0 .16rem rgba(79, 99, 233, .1); }
    .pos-payment-amount-wrap { position: relative; }
    .pos-payment-amount-wrap > span { position: absolute; z-index: 1; top: 50%; left: 10px; color: #79869a; font-size: 9px; font-weight: 800; transform: translateY(-50%); pointer-events: none; }
    .pos-payment-amount-wrap .form-control { padding-left: 30px; color: #273753; font-weight: 750; }
    .pos-payment-reference-wrap > i { position: absolute; z-index: 1; top: 50%; left: 10px; color: #9ba5b4; font-size: 14px; transform: translateY(-50%); pointer-events: none; }
    .pos-payment-reference-wrap { position: relative; display: block; }
    .pos-payment-reference-wrap .form-control { padding-left: 31px; }
    .pos-payment-row .remove-payment-row { display: inline-flex; width: 35px; height: 35px; align-items: center; justify-content: center; padding: 0; color: #a56a74; border: 1px solid #eadde0; border-radius: 9px; background: #fff; }
    .pos-payment-row .remove-payment-row:hover { color: #c13d50; border-color: #f0c2ca; background: #fff1f3; }

    .pos-payment-quick { justify-content: space-between; gap: 12px; margin-top: 12px; padding: 12px; border: 1px solid #e6eaf2; border-radius: 13px; background: linear-gradient(135deg, #fafbfe, #f5f7fb); }
    .pos-payment-quick-copy { min-width: 120px; align-items: flex-start; flex-direction: column; }
    .pos-payment-quick-copy > span { display: inline-flex; align-items: center; gap: 4px; color: #4a5870; font-size: 9px; font-weight: 800; letter-spacing: .03em; text-transform: uppercase; }
    .pos-payment-quick-copy > span i { color: #e89a2c; }
    .pos-payment-quick-copy small { margin-top: 2px; color: #929cad; font-size: 8px; }
    .pos-payment-quick-options { flex-wrap: wrap; justify-content: flex-end; gap: 6px; }
    .pos-quick-payment { min-height: 31px; padding: 5px 9px; color: #536178; border: 1px solid #d9dfe9; border-radius: 8px; background: #fff; box-shadow: 0 2px 4px rgba(38, 51, 80, .03); font-size: 9px; font-weight: 750; transition: all .17s ease; }
    .pos-quick-payment:hover { color: #354ac3; border-color: #b9c4f2; background: #f3f5ff; transform: translateY(-1px); }
    .pos-quick-payment.is-exact { display: inline-flex; align-items: center; gap: 4px; color: #087b59; border-color: #bde3d6; background: #edf9f5; }

    .pos-payment-coverage { align-items: flex-start; gap: 10px; margin-top: 12px; padding: 11px 12px; border: 1px solid #f0dec0; border-radius: 13px; background: #fff9ef; }
    .pos-payment-coverage-icon { display: inline-flex; width: 32px; height: 32px; flex: 0 0 auto; align-items: center; justify-content: center; color: #b9761e; border-radius: 9px; background: #fff0d7; font-size: 16px; }
    .pos-payment-coverage > div { flex: 1 1 auto; min-width: 0; }
    .pos-payment-coverage-copy { justify-content: space-between; gap: 12px; }
    .pos-payment-coverage-copy strong { color: #795221; font-size: 10px; }
    .pos-payment-coverage-copy small { overflow: hidden; color: #9a7a4f; font-size: 8px; text-align: right; white-space: nowrap; text-overflow: ellipsis; }
    .pos-payment-progress { display: block; height: 5px; margin-top: 7px; overflow: hidden; border-radius: 99px; background: #f2dfbf; }
    .pos-payment-progress > span { display: block; width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #e6a13d, #efb65f); transition: width .25s ease, background .25s ease; }
    .pos-payment-coverage.is-ready { border-color: #c8e8dc; background: #f0faf6; }
    .pos-payment-coverage.is-ready .pos-payment-coverage-icon { color: #0a805d; background: #dff5ec; }
    .pos-payment-coverage.is-ready strong { color: #186b54; }
    .pos-payment-coverage.is-ready small { color: #5e907f; }
    .pos-payment-coverage.is-ready .pos-payment-progress { background: #d5eee5; }
    .pos-payment-coverage.is-ready .pos-payment-progress > span { background: linear-gradient(90deg, #0da373, #22be8b); }
    .pos-total-panel { display: flex; flex-direction: column; padding: 17px; }
    .pos-total-heading { justify-content: space-between; gap: 12px; padding-bottom: 13px; border-bottom: 1px solid #edf0f5; font-weight: 400; letter-spacing: 0; text-transform: none; }
    .pos-total-heading > div { min-width: 0; gap: 9px; }
    .pos-total-heading-icon { color: #087d5a; border-color: #d4ede4; background: #ecf9f4; }
    .pos-total-heading > div > span:last-child { align-items: flex-start; flex-direction: column; }
    .pos-total-heading small { color: #98a2b2; font-size: 8px; font-weight: 800; letter-spacing: .07em; }
    .pos-total-heading strong { color: #293850; font-size: 13px; }
    .pos-summary-item-count { padding: 5px 8px; color: #66748a; border: 1px solid #e1e6ed; border-radius: 999px; background: #f7f9fb; font-size: 9px; font-weight: 700; white-space: nowrap; }
    .pos-total-breakdown { padding: 8px 0 6px; }
    .pos-total-row { display: flex; min-height: 34px; align-items: center; justify-content: space-between; gap: 12px; padding: 7px 2px; }
    .pos-total-row span { color: #6f7b8f; font-size: 10px; }
    .pos-total-row > strong { color: #2c3a52; font-size: 11px; white-space: nowrap; }
    .pos-total-row.is-saving span i,
    .pos-total-row.is-saving strong { color: #0b9168; }

    .pos-adjustment-card { margin: 2px 0 12px; padding: 10px; border: 1px solid #e5e9f0; border-radius: 12px; background: #f8fafc; }
    .pos-adjustment-card-head { justify-content: space-between; gap: 8px; padding: 0 1px 8px; border-bottom: 1px solid #e8ecf2; color: #5b687c; font-size: 9px; font-weight: 800; }
    .pos-adjustment-card-head > span { display: inline-flex; align-items: center; gap: 5px; }
    .pos-adjustment-card-head i { color: #6577d3; font-size: 13px; }
    .pos-adjustment-card-head small { padding: 3px 6px; color: #929cad; border-radius: 999px; background: #fff; font-size: 7px; }
    .pos-adjustment-row { align-items: flex-end; border-bottom: 1px dashed #dfe4ec; }
    .pos-adjustment-row > div { min-width: 0; }
    .pos-adjustment-row > div > label { color: #677489; font-size: 9px; font-weight: 700; }
    .pos-adjustment-fields { display: flex; gap: 6px; margin-top: 5px; }
    .pos-adjustment-fields > label { position: relative; display: block; width: 66px; margin: 0; }
    .pos-adjustment-fields > label.is-rupiah { width: 106px; }
    .pos-adjustment-fields > label > span { position: absolute; z-index: 1; top: 50%; right: 8px; color: #8792a4; font-size: 8px; font-weight: 800; transform: translateY(-50%); pointer-events: none; }
    .pos-adjustment-fields > label.is-rupiah > span { right: auto; left: 8px; }
    .pos-total-panel .form-control { min-width: 0; height: 30px; min-height: 30px; padding: 4px 22px 4px 7px; color: #314058; border-color: #dce2eb; border-radius: 7px; background: #fff; font-size: 9px; }
    .pos-adjustment-fields .is-rupiah .form-control { padding-right: 7px; padding-left: 27px; }
    .pos-total-panel .form-control:focus { border-color: #9faef0; box-shadow: 0 0 0 .14rem rgba(79, 99, 233, .09); }
    .pos-tax-row { align-items: center; gap: 8px; padding-top: 9px; }
    .pos-tax-switch { min-width: 0; gap: 7px; cursor: pointer; }
    .pos-tax-switch .form-check-input { width: 30px; flex: 0 0 auto; margin: 0; cursor: pointer; }
    .pos-tax-switch > span { min-width: 0; align-items: flex-start; flex-direction: column; white-space: normal; }
    .pos-tax-switch b { color: #68758a; font-size: 9px; }
    .pos-tax-switch small { color: #9aa3b2; font-size: 7px; line-height: 1.25; }
    .pos-tax-value { display: flex; min-width: 0; align-items: center; justify-content: flex-end; gap: 7px; }
    .pos-tax-value > label { position: relative; width: 84px; min-width: 84px; margin: 0; }
    .pos-tax-value > label .form-control { height: 36px; min-height: 36px; padding-right: 28px; font-size: 11px; }
    .pos-tax-value > label span { position: absolute; top: 50%; right: 7px; color: #8792a4; font-size: 8px; font-weight: 800; transform: translateY(-50%); pointer-events: none; }
    .pos-tax-value strong { min-width: 62px; color: #344259; font-size: 10px; text-align: right; white-space: nowrap; }
    .pos-embalase-total { border-top: 1px dashed #dfe4ec; }
    .pos-embalase-total i { color: #6275d2; }

    .pos-total-row.is-grand { min-height: 74px; margin-top: auto; padding: 13px 14px; color: #fff; border: 0; border-radius: 13px; background: radial-gradient(circle at 100% 0, rgba(133, 151, 255, .4), transparent 48%), linear-gradient(135deg, #1d2d56, #2d4786); box-shadow: 0 11px 24px rgba(34, 56, 105, .18); }
    .pos-total-row.is-grand > span { display: grid; color: #fff; font-size: 12px; font-weight: 800; }
    .pos-total-row.is-grand > span small { margin-bottom: 2px; color: #b9c7e7; font-size: 8px; font-weight: 700; letter-spacing: .07em; }
    .pos-total-row.is-grand strong { color: #fff; font-size: clamp(20px, 2vw, 27px); font-weight: 900; letter-spacing: -.035em; }
    .pos-balance-row { justify-content: space-between; gap: 8px; margin-top: 9px; }
    .pos-balance-row > div { flex: 1 1 0; padding: 8px 9px; border: 1px solid #e4e8ef; border-radius: 10px; background: #fafbfc; }
    .pos-balance-row > div:last-child { text-align: right; }
    .pos-balance-row span,
    .pos-balance-row strong { display: block; }
    .pos-balance-row span { color: #8590a1; font-size: 8px; }
    .pos-balance-row span i { margin-right: 3px; color: #16916c; }
    .pos-balance-row strong { margin-top: 2px; color: #314057; font-size: 11px; }
    .pos-total-panel.is-short .pos-balance-row > div:last-child { border-color: #f0dfc2; background: #fff9ef; }
    .pos-total-panel.is-short .pos-balance-row > div:last-child span,
    .pos-total-panel.is-short .pos-balance-row > div:last-child strong { color: #a16b24; }
    .pos-total-panel.is-ready .pos-balance-row > div:last-child { border-color: #cde8df; background: #f1faf7; }
    .pos-total-panel.is-ready .pos-balance-row > div:last-child span,
    .pos-total-panel.is-ready .pos-balance-row > div:last-child strong { color: #147557; }

    .pos-submit-bar {
        position: sticky;
        z-index: 5;
        bottom: 10px;
        justify-content: space-between;
        gap: 14px;
        margin: 0 14px 14px;
        padding: 11px 12px;
        border: 1px solid rgba(216, 223, 234, .94);
        border-radius: 14px;
        background: rgba(255, 255, 255, .94);
        box-shadow: 0 12px 28px rgba(26, 39, 69, .1), inset 0 1px 0 #fff;
        backdrop-filter: blur(16px);
    }

    .pos-submit-status { min-width: 0; gap: 8px; }
    .pos-submit-status-icon { display: inline-flex; width: 38px; height: 38px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5064d6; border-radius: 11px; background: #eef1ff; font-size: 18px; }
    .pos-submit-status > span:last-child { min-width: 0; align-items: flex-start; flex-direction: column; }
    .pos-submit-status small { color: #9aa3b1; font-size: 7px; font-weight: 800; letter-spacing: .07em; }
    .pos-submit-status p { overflow: hidden; margin: 1px 0 0; color: #6f7b8e; font-size: 10px; line-height: 1.35; white-space: nowrap; text-overflow: ellipsis; }
    .pos-submit-bar.is-ready .pos-submit-status-icon { color: #087c59; background: #e8f9f2; }
    .pos-submit-bar.is-warning .pos-submit-status-icon { color: #bb7519; background: #fff3df; }
    .pos-submit-bar > div:last-child { flex: 0 0 auto; gap: 7px; }
    .pos-save-draft-button { display: inline-flex; min-height: 45px; align-items: center; gap: 6px; padding: 9px 13px; color: #5e6a80; border: 1px solid #d6dde8; background: #fff; font-size: 11px; }
    .pos-save-draft-button:hover { color: #3d4a63; border-color: #bfc8d7; background: #fafbfc; }
    .pos-complete-button { display: inline-flex; min-height: 48px; align-items: center; gap: 9px; padding: 8px 13px 8px 15px; color: #fff; border: 0; background: linear-gradient(135deg, #0b9669, #16b27f); box-shadow: 0 9px 19px rgba(15, 167, 118, .24); font-size: 11px; }
    .pos-complete-button > span { display: flex; align-items: flex-start; flex-direction: column; text-align: left; }
    .pos-complete-button > span small { margin-bottom: 1px; color: #c7f2e2; font-size: 7px !important; font-weight: 500; }
    .pos-complete-button > i { font-size: 17px; }
    .pos-complete-button:hover:not(:disabled) { color: #fff; box-shadow: 0 12px 23px rgba(15, 167, 118, .3); transform: translateY(-1px); }
    .pos-complete-button:disabled { cursor: not-allowed; color: #8ebcaf; background: #d9eee7; box-shadow: none; transform: none; }
    .pos-complete-button kbd { color: #d9ffef; border-color: rgba(255, 255, 255, .24); background: rgba(0, 0, 0, .1); box-shadow: none; }

    .pos-page .form-control,
    .pos-page .form-select,
    .pos-page .select2-container--default .select2-selection--single {
        min-height: 35px;
        color: #303d57;
        border-color: #dbe1eb;
        border-radius: 9px;
        background-color: #fff;
        font-size: 10px;
    }

    .pos-page .form-control::placeholder { color: #a5adba; }
    .pos-page .form-control:focus,
    .pos-page .form-select:focus,
    .pos-page .select2-container--default.select2-container--focus .select2-selection--single,
    .pos-page .select2-container--default.select2-container--open .select2-selection--single { border-color: #9eacf1; box-shadow: 0 0 0 .16rem rgba(79, 99, 233, .1); }
    .pos-page .form-control:disabled,
    .pos-page .form-select:disabled { color: #9aa3b1; background-color: #f1f3f7; }
    .pos-page .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left: 10px; color: #4a566b; line-height: 33px; font-size: 10px; }
    .pos-page .select2-container--default .select2-selection--single .select2-selection__arrow { top: 4px; }
    .select2-container--open .select2-dropdown { z-index: 1060; overflow: hidden; border-color: #bac4ed; border-radius: 10px; box-shadow: 0 14px 30px rgba(32, 45, 81, .16); }
    .select2-results__option { padding: 8px 10px; font-size: 10px; }

    .pos-page .pos-search-control .select2-container--default .select2-selection--single {
        height: 52px;
        min-height: 52px;
        overflow: hidden;
        border: 1px solid #d7def0;
        border-radius: 13px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 6px 15px rgba(49, 61, 115, .065), inset 0 1px 0 #fff;
    }

    .pos-page .pos-search-control .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 0 66px 0 51px;
        overflow: hidden;
        color: #273650;
        line-height: 50px;
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .pos-page .pos-search-control .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9aa4b5; font-weight: 600; }
    .pos-page .pos-search-control .select2-container--default .select2-selection--single .select2-selection__arrow { display: none; }
    .pos-page .pos-search-control .select2-container--default .select2-selection--single .select2-selection__clear { position: absolute; z-index: 5; top: 50%; right: 57px; margin: 0; color: #8b96aa; font-size: 17px; line-height: 1; transform: translateY(-54%); }
    .pos-page .pos-search-control .select2-container--default.select2-container--open .select2-selection--single { border-color: #8e9eeb; box-shadow: 0 0 0 3px rgba(79, 99, 233, .1); }

    .select2-dropdown.pos-product-dropdown { overflow: hidden; border: 1px solid #cbd4ed; border-radius: 14px; background: #fff; box-shadow: 0 18px 38px rgba(31, 43, 79, .19); }
    .pos-product-dropdown .select2-search--dropdown { padding: 10px; border-bottom: 1px solid #e9edf4; background: #f8f9fc; }
    .pos-product-dropdown .select2-search--dropdown .select2-search__field { min-height: 39px; padding: 8px 11px; color: #2b3952; border: 1px solid #d7deea; border-radius: 10px; outline: none; font-size: 10px; }
    .pos-product-dropdown .select2-search--dropdown .select2-search__field:focus { border-color: #97a5ed; box-shadow: 0 0 0 3px rgba(79, 99, 233, .08); }
    .pos-product-dropdown .select2-results__options { max-height: min(430px, 52vh) !important; padding: 6px; scrollbar-width: thin; scrollbar-color: #cbd3df transparent; }
    .pos-product-dropdown .select2-results__option { margin: 2px 0; padding: 0; overflow: hidden; border: 1px solid transparent; border-radius: 11px; }
    body .select2-container--default .select2-dropdown.pos-product-dropdown .select2-results__option--highlighted[aria-selected] { color: #24324c !important; border-color: #cfd7f6 !important; background: #eef1ff !important; box-shadow: inset 3px 0 0 #6073df; }
    body .select2-container--default .select2-dropdown.pos-product-dropdown .select2-results__option[aria-selected="true"] { color: #24324c !important; border-color: #c7e8dc !important; background: #edf9f5 !important; box-shadow: inset 3px 0 0 #16a579; }
    body .pos-product-dropdown .select2-results__option--highlighted .pos-search-result-name,
    body .pos-product-dropdown .select2-results__option[aria-selected="true"] .pos-search-result-name { color: #1f2e48 !important; }
    body .pos-product-dropdown .select2-results__option--highlighted .pos-search-result-meta,
    body .pos-product-dropdown .select2-results__option--highlighted .pos-search-result-code,
    body .pos-product-dropdown .select2-results__option[aria-selected="true"] .pos-search-result-meta,
    body .pos-product-dropdown .select2-results__option[aria-selected="true"] .pos-search-result-code { color: #6f7c91 !important; }
    body .pos-product-dropdown .select2-results__option--highlighted .pos-search-result-price,
    body .pos-product-dropdown .select2-results__option[aria-selected="true"] .pos-search-result-price { color: #263651 !important; }
    .pos-product-dropdown .select2-results__message,
    .pos-product-dropdown .loading-results { padding: 17px 12px; color: #7e899b; background: #fafbfc; text-align: center; font-size: 10px; }

    .pos-search-result { display: grid; grid-template-columns: 38px minmax(0, 1fr) auto; align-items: center; gap: 9px; padding: 9px; }
    .pos-search-result-icon { display: inline-flex; width: 38px; height: 38px; align-items: center; justify-content: center; color: #5266d9; border: 1px solid #dfe4f8; border-radius: 11px; background: #eef1ff; font-size: 19px; }
    .pos-search-result-icon.is-empty { color: #b66c20; border-color: #f1ddc5; background: #fff5e9; }
    .pos-search-result-body { min-width: 0; }
    .pos-search-result-name { display: block; overflow: hidden; color: #24324c; font-size: 11px; font-weight: 850; line-height: 1.3; white-space: nowrap; text-overflow: ellipsis; }
    .pos-search-result-code { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin-top: 3px; color: #8993a4; font-size: 8px; }
    .pos-search-result-code b { padding: 2px 5px; color: #5666b0; border-radius: 5px; background: #eef1fb; font-size: 7px; letter-spacing: .04em; }
    .pos-search-result-meta { display: block; overflow: hidden; margin-top: 3px; color: #778397; font-size: 8px; white-space: nowrap; text-overflow: ellipsis; }
    .pos-search-result-side { display: grid; min-width: 82px; gap: 4px; text-align: right; }
    .pos-search-result-price { color: #283753; font-size: 10px; font-weight: 900; }
    .pos-search-result-stock { justify-self: end; padding: 3px 6px; color: #087859; border-radius: 999px; background: #e9f8f2; font-size: 7px; font-weight: 850; }
    .pos-search-result-stock.is-empty { color: #b55432; background: #fff0eb; }
    .pos-search-loading { display: flex; align-items: center; justify-content: center; gap: 7px; padding: 14px; color: #69758a; font-size: 9px; }
    .pos-search-loading i { color: #5367df; font-size: 16px; }

    /* Shared history screen */
    .pos-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 17px; color: #edf2ff; border-color: rgba(255, 255, 255, .1); background: linear-gradient(118deg, #131d38, #233d78); box-shadow: 0 16px 35px rgba(24, 40, 80, .18); }
    .pos-toolbar-title,
    .pos-panel-header { display: flex; min-width: 0; align-items: center; gap: 11px; }
    .pos-toolbar-title h4,
    .pos-panel-header h5 { margin: 0; font-weight: 850; letter-spacing: -.02em; }
    .pos-toolbar-title h4 { color: #fff; }
    .pos-toolbar-title p,
    .pos-panel-header p { margin: 2px 0 0; color: #778399; font-size: 11px; }
    .pos-toolbar-title p { color: #b7c2dd; }
    .pos-toolbar-icon,
    .pos-panel-header > span { display: inline-flex; width: 42px; height: 42px; flex: 0 0 auto; align-items: center; justify-content: center; color: #5266dc; border-radius: 13px; background: #eef1ff; font-size: 21px; }
    .pos-toolbar-icon { color: #d6ddff; background: rgba(255, 255, 255, .1); }
    .pos-toolbar-actions,
    .pos-history-tools,
    .pos-history-summary { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .pos-panel { padding: 17px; }
    .pos-history-summary { margin: 13px 0; }
    .pos-history-summary > span { padding: 7px 9px; color: #6b768a; border: 1px solid #e2e7ef; border-radius: 9px; background: #f8f9fc; font-size: 10px; }
    .pos-history-summary strong { color: #263550; }
    .pos-history-tools { justify-content: space-between; margin-bottom: 11px; }
    .pos-history-search { position: relative; min-width: 260px; flex: 1 1 330px; }
    .pos-history-search > i { position: absolute; z-index: 1; top: 50%; left: 11px; color: #8c97a9; transform: translateY(-50%); }
    .pos-history-search input { width: 100%; min-height: 36px; padding: 7px 34px; color: #35425a; border: 1px solid #dbe1ea; border-radius: 10px; outline: none; background: #fff; font-size: 10px; }
    .pos-history-search input:focus { border-color: #a5b0ef; box-shadow: 0 0 0 3px rgba(79, 99, 233, .08); }
    .pos-history-search button { position: absolute; top: 50%; right: 7px; color: #8d97a8; border: 0; background: transparent; transform: translateY(-50%); }
    .pos-history-tools .form-select { width: auto; min-width: 145px; }
    .pos-history-wrap { overflow: auto; border: 1px solid #e0e5ed; border-radius: 12px; background: #fff; }
    .pos-history-table { min-width: 1050px; margin-bottom: 0; }
    .pos-history-table thead th { color: #788498; border-bottom: 1px solid #dfe5ed; background: #f7f9fc; font-size: 9px; font-weight: 850; text-transform: uppercase; }
    .pos-history-table td { font-size: 10px; }
    .pos-status { display: inline-flex; padding: 4px 7px; border-radius: 999px; font-size: 9px; font-weight: 800; }
    .pos-status.is-completed { color: #087754; background: #e7f8f1; }
    .pos-status.is-draft { color: #6952b8; background: #f0edff; }
    .pos-status.is-cancelled { color: #bd4655; background: #fff0f2; }

    /* Workspace resep memakai skala font tersendiri agar tetap tajam dan mudah dibaca. */
    .pos-prescription-type-modal {
        font-synthesis: none;
    }

    .pos-prescription-cashier-step {
        --rx-text-xs: 11px;
        --rx-text-sm: 12px;
        --rx-text-md: 13px;
        --rx-text-lg: 15px;
        --rx-text-xl: 18px;
        font-size: var(--rx-text-md) !important;
        font-weight: 400;
        letter-spacing: normal;
        line-height: 1.5;
    }

    .pos-prescription-cashier-step *,
    .pos-prescription-type-step * {
        text-rendering: optimizeLegibility;
    }

    .pos-prescription-cashier-step strong,
    .pos-prescription-cashier-step b,
    .pos-prescription-cashier-step h1,
    .pos-prescription-cashier-step h2,
    .pos-prescription-cashier-step h3 {
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step small {
        font-size: var(--rx-text-xs) !important;
        font-weight: 400;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-section-kicker,
    .pos-prescription-cashier-step .pos-search-eyebrow,
    .pos-prescription-cashier-step .pos-fefo-label,
    .pos-prescription-cashier-step .pos-catalog-block-head small,
    .pos-prescription-cashier-step .pos-prescription-head > div small,
    .pos-prescription-cashier-step .pos-prescription-mode-copy > small,
    .pos-prescription-cashier-step .pos-prescription-flow-readiness small,
    .pos-prescription-cashier-step .pos-prescription-flow-metrics small {
        font-size: var(--rx-text-xs) !important;
        font-weight: 700 !important;
        letter-spacing: .045em;
    }

    .pos-prescription-cashier-brand small {
        font-size: var(--rx-text-xs) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-brand strong {
        font-size: 17px !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-brand p {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-flow-steps span,
    .pos-prescription-flow-steps span b,
    .pos-prescription-flow-cancel {
        font-size: var(--rx-text-xs) !important;
        font-weight: 600 !important;
    }

    .pos-prescription-cashier-step .pos-catalog-hero h2 {
        font-size: var(--rx-text-xl) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step .pos-catalog-hero p,
    .pos-prescription-cashier-step .pos-catalog-ready,
    .pos-prescription-cashier-step .pos-catalog-phase small,
    .pos-prescription-cashier-step .pos-search-feedback p,
    .pos-prescription-cashier-step .pos-search-scope span,
    .pos-prescription-cashier-step .pos-block-state,
    .pos-prescription-cashier-step .pos-fefo-badge,
    .pos-prescription-cashier-step .pos-fefo-placeholder,
    .pos-prescription-cashier-step .pos-fefo-chip {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-search-title {
        font-size: var(--rx-text-lg) !important;
        font-weight: 700 !important;
        line-height: 1.4;
    }

    .pos-prescription-cashier-step .pos-search-title small {
        margin-top: 3px;
        font-size: var(--rx-text-xs) !important;
    }

    .pos-prescription-cashier-step .pos-search-ready,
    .pos-prescription-cashier-step .pos-search-ready i {
        font-size: var(--rx-text-xs) !important;
    }

    .pos-prescription-cashier-step .pos-catalog-block-head strong,
    .pos-prescription-cashier-step .pos-product-name strong,
    .pos-prescription-cashier-step .pos-cart-title strong {
        font-size: 14px !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step .pos-block-number,
    .pos-prescription-cashier-step .pos-shortcut-card,
    .pos-prescription-cashier-step .pos-live-label,
    .pos-prescription-cashier-step .pos-use-compound-group,
    .pos-prescription-cashier-step .pos-transaction-details summary {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.4;
    }

    .pos-prescription-cashier-step .pos-quote-box strong,
    .pos-prescription-cashier-step .pos-add-button > span:nth-child(2) {
        font-size: var(--rx-text-md) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step .pos-product-name small,
    .pos-prescription-cashier-step .pos-product-name .badge,
    .pos-prescription-cashier-step .pos-product-meta span,
    .pos-prescription-cashier-step .pos-product-meta small,
    .pos-prescription-cashier-step .pos-product-card > .mt-3 {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-cart-readiness,
    .pos-prescription-cashier-step .pos-detail-summary,
    .pos-prescription-cashier-step .pos-signa-quick > span {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-detail-customer strong,
    .pos-prescription-cashier-step .pos-transaction-form-intro strong {
        font-size: var(--rx-text-md) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step .pos-general-customer-button {
        font-size: var(--rx-text-xs) !important;
        font-weight: 600 !important;
    }

    .pos-prescription-cashier-step .pos-field label,
    .pos-prescription-cashier-step .pos-prescription-item-grid label > span,
    .pos-prescription-cashier-step .pos-compound-group-fields label > span,
    .pos-prescription-cashier-step .pos-compound-controls > label > span {
        font-size: var(--rx-text-sm) !important;
        font-weight: 600 !important;
        line-height: 1.4;
    }

    .pos-prescription-cashier-step .form-control,
    .pos-prescription-cashier-step .form-select,
    .pos-prescription-cashier-step .input-group-text,
    .pos-prescription-cashier-step .select2-selection__rendered,
    .pos-prescription-cashier-step .select2-search__field {
        font-size: var(--rx-text-md) !important;
        font-weight: 400 !important;
        line-height: 1.4 !important;
    }

    .pos-prescription-cashier-step .pos-field .form-control,
    .pos-prescription-cashier-step .pos-field .form-select,
    .pos-prescription-cashier-step .pos-compound-controls .form-control,
    .pos-prescription-cashier-step .pos-compound-controls .form-select,
    .pos-prescription-cashier-step .pos-prescription-item-grid .form-control,
    .pos-prescription-cashier-step .pos-prescription-item-grid .form-select,
    .pos-prescription-cashier-step .pos-compound-group-fields .form-control,
    .pos-prescription-cashier-step .pos-compound-group-fields .form-select {
        min-height: 38px;
    }

    .pos-prescription-cashier-step .btn,
    .pos-prescription-cashier-step .pos-signa-chip,
    .pos-prescription-cashier-step .pos-compound-summary-chip {
        font-size: var(--rx-text-xs) !important;
        font-weight: 600 !important;
        line-height: 1.35;
    }

    .pos-prescription-cashier-step .pos-prescription-head > div strong,
    .pos-prescription-cashier-step .pos-prescription-mode-copy > strong,
    .pos-prescription-cashier-step .pos-prescription-mode-note strong,
    .pos-prescription-cashier-step .pos-prescription-guidance strong {
        font-size: var(--rx-text-md) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step .pos-prescription-head > div p,
    .pos-prescription-cashier-step .pos-prescription-status,
    .pos-prescription-cashier-step .pos-prescription-mode-copy > span,
    .pos-prescription-cashier-step .pos-prescription-mode-note small,
    .pos-prescription-cashier-step .pos-prescription-guidance small,
    .pos-prescription-cashier-step .pos-prescription-counter {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-compound-steps,
    .pos-prescription-cashier-step .pos-compound-steps b,
    .pos-prescription-cashier-step .pos-compound-controls > label > small,
    .pos-prescription-cashier-step .pos-compound-summary-empty,
    .pos-prescription-cashier-step .pos-compound-summary-chip,
    .pos-prescription-cashier-step .pos-compound-component-help {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-cart-table thead th {
        font-size: var(--rx-text-xs) !important;
        font-weight: 700 !important;
        letter-spacing: .035em;
    }

    .pos-prescription-cashier-step .pos-cart-table tbody td {
        font-size: var(--rx-text-sm) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-item-copy strong,
    .pos-prescription-cashier-step .pos-prescription-item-head strong,
    .pos-prescription-cashier-step .pos-compound-group-copy strong,
    .pos-prescription-cashier-step .pos-money,
    .pos-prescription-cashier-step .pos-line-total {
        font-size: var(--rx-text-md) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step .pos-item-copy > small,
    .pos-prescription-cashier-step .pos-item-badges span,
    .pos-prescription-cashier-step .pos-money-note,
    .pos-prescription-cashier-step .pos-stock-caption,
    .pos-prescription-cashier-step .pos-discount-result,
    .pos-prescription-cashier-step .pos-fefo-allocation,
    .pos-prescription-cashier-step .pos-fefo-allocation b,
    .pos-prescription-cashier-step .pos-fefo-allocation small,
    .pos-prescription-cashier-step .pos-stock-warning {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.4;
    }

    .pos-prescription-cashier-step .pos-prescription-item-head small,
    .pos-prescription-cashier-step .pos-prescription-item-state,
    .pos-prescription-cashier-step .pos-compound-group-copy small,
    .pos-prescription-cashier-step .pos-compound-group-mark {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step .pos-qty-stepper input.form-control,
    .pos-prescription-cashier-step .pos-discount-control input.form-control,
    .pos-prescription-cashier-step .pos-discount-control span {
        font-size: var(--rx-text-sm) !important;
    }

    .pos-prescription-cashier-step .pos-cart-empty > small,
    .pos-prescription-cashier-step .pos-cart-empty-meta,
    .pos-prescription-cashier-step .pos-cart-footnote {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-cashier-step > .select2-container .pos-search-result-name,
    .pos-prescription-cashier-step > .select2-container .pos-search-result-price {
        font-size: var(--rx-text-md) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-cashier-step > .select2-container .pos-search-result-code,
    .pos-prescription-cashier-step > .select2-container .pos-search-result-code b,
    .pos-prescription-cashier-step > .select2-container .pos-search-result-meta,
    .pos-prescription-cashier-step > .select2-container .pos-search-result-stock,
    .pos-prescription-cashier-step > .select2-container .pos-search-loading,
    .pos-prescription-cashier-step > .select2-container .select2-results__message,
    .pos-prescription-cashier-step > .select2-container .loading-results,
    .pos-prescription-cashier-step > .select2-container .select2-search__field {
        font-size: var(--rx-text-xs) !important;
        line-height: 1.45;
    }

    .pos-prescription-flow-readiness strong,
    .pos-prescription-flow-metrics strong {
        font-size: var(--rx-text-sm) !important;
        font-weight: 700 !important;
    }

    .pos-prescription-draft-button,
    .pos-finish-prescription-button {
        font-size: var(--rx-text-sm) !important;
        font-weight: 600 !important;
    }

    .pos-finish-prescription-button small {
        font-size: var(--rx-text-xs) !important;
    }

    .pos-prescription-type-step small,
    .pos-prescription-type-step .pos-prescription-type-copy > small,
    .pos-prescription-type-step .pos-prescription-type-points b,
    .pos-prescription-type-step .pos-prescription-modal-help,
    .pos-prescription-type-step .modal-footer small {
        font-size: 11px !important;
        line-height: 1.45;
    }

    .pos-prescription-type-step .pos-prescription-type-copy > strong {
        font-size: 16px !important;
        font-weight: 700 !important;
    }

    .pos-prescription-type-step .pos-prescription-type-copy > span:not(.pos-prescription-type-points),
    .pos-prescription-type-step .pos-prescription-modal-intro strong {
        font-size: 12px !important;
        line-height: 1.5;
    }

    .pos-prescription-type-step .pos-prescription-modal-cancel {
        font-size: 12px !important;
        font-weight: 600 !important;
    }

    @media (max-width: 1450px) {
        .pos-appbar { grid-template-columns: minmax(220px, .8fr) auto minmax(280px, 1fr); }
        .pos-cashier-profile { display: none; }
        .pos-flow { grid-template-columns: repeat(3, minmax(135px, .72fr)) minmax(200px, 1fr); }
        .pos-payment-layout { grid-template-columns: minmax(0, 1fr); }
        .pos-total-panel { display: flex; }
        .pos-form-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .pos-prescription-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pos-prescription-item-grid.is-compound { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .pos-compound-group-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pos-prescription-cashier-header { grid-template-columns: minmax(280px, 1fr) auto auto; }
        .pos-prescription-flow-steps span { font-size: 0; }
        .pos-prescription-flow-steps span b { font-size: 9px; }
        .pos-prescription-cashier-layout { grid-template-columns: minmax(320px, 360px) minmax(0, 1fr); }
    }

    @media (max-width: 1240px) {
        .pos-appbar { grid-template-columns: 1fr auto; }
        .pos-appbar-center { display: none; }
        .pos-flow { grid-template-columns: repeat(3, minmax(125px, 1fr)); }
        .pos-flow-summary { display: none; }
        .pos-flow-track { right: 16px; }
        .pos-prescription-flow-steps { display: none; }
        .pos-prescription-cashier-header { grid-template-columns: minmax(0, 1fr) auto; }
        .pos-prescription-cashier-footer { grid-template-columns: minmax(220px, 1fr) auto; }
        .pos-prescription-flow-metrics { display: none; }
    }

    @media (max-width: 1020px) {
        .pos-workspace { grid-template-columns: 1fr; }
        .pos-catalog { position: static; }
        .pos-product-panel { display: grid; grid-template-columns: minmax(280px, .92fr) minmax(310px, 1.08fr); gap: 0; }
        .pos-product-panel > .pos-catalog-hero,
        .pos-product-panel > .pos-catalog-rail { grid-column: 1 / -1; }
        .pos-product-panel > .pos-search-box { grid-column: 1; margin-right: 6px; }
        .pos-product-panel > .pos-selection-block { grid-column: 2; grid-row: 3; margin-left: 6px; }
        .pos-product-panel > .pos-config-block { grid-column: 1; margin-right: 6px; }
        .pos-product-panel > .pos-stock-block { grid-column: 2; grid-row: 4; margin-left: 6px; }
        .pos-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pos-prescription-item-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pos-prescription-item-grid.is-compound-component { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pos-prescription-cashier-body { overflow: auto; }
        .pos-prescription-cashier-layout { height: auto; grid-template-columns: 1fr; }
        .pos-prescription-modal-catalog,
        .pos-prescription-modal-editor { overflow: visible; }
        .pos-prescription-modal-catalog > .pos-catalog { width: 100%; }
    }

    @media (max-width: 720px) {
        .pos-page { gap: 10px; padding: 9px; }
        .pos-appbar { grid-template-columns: 1fr; gap: 10px; padding: 11px; border-radius: 16px; }
        .pos-brand-mark { width: 43px; height: 43px; }
        .pos-brand-title > span { display: none; }
        .pos-appbar-actions { display: grid; grid-template-columns: repeat(3, 1fr); justify-content: stretch; }
        .pos-page .pos-btn-quiet { justify-content: center; padding: 7px 5px; font-size: 9px; }
        .pos-btn-quiet kbd { display: none; }
        .pos-flow { min-height: 59px; grid-template-columns: repeat(3, 1fr); gap: 5px; padding: 8px; border-radius: 14px; }
        .pos-flow-step { justify-content: center; gap: 5px; }
        .pos-flow-icon { width: 32px; height: 32px; border-radius: 10px; font-size: 15px; }
        .pos-flow-step small { display: none; }
        .pos-flow-step strong { font-size: 9px; }
        .pos-flow-step + .pos-flow-step::before { display: none; }
        .pos-surface,
        .pos-panel { padding: 13px; border-radius: 14px; }
        .pos-product-panel { display: block; padding: 0; }
        .pos-transaction-panel { padding: 0; }
        .pos-product-panel > .pos-search-box,
        .pos-product-panel > .pos-catalog-block { margin: 11px 10px 0; }
        .pos-product-panel > .pos-catalog-block:last-child { margin-bottom: 10px; }
        .pos-catalog-hero { padding: 13px; }
        .pos-catalog-step { width: 43px; height: 43px; }
        .pos-catalog-hero h2 { font-size: 14px; }
        .pos-catalog-ready { display: none; }
        .pos-search-box { padding: 12px; }
        .pos-search-feedback { flex-direction: column; }
        .pos-search-scope { width: 100%; }
        .pos-search-scope span { flex: 1 1 0; justify-content: center; }
        .pos-search-result { grid-template-columns: 35px minmax(0, 1fr); }
        .pos-search-result-icon { width: 35px; height: 35px; }
        .pos-search-result-side { grid-column: 2; grid-template-columns: auto auto; justify-content: start; text-align: left; }
        .pos-search-result-stock { justify-self: start; }
        .pos-form-grid,
        .pos-add-fields { grid-template-columns: 1fr; }
        .pos-prescription-head { align-items: flex-start; flex-wrap: wrap; }
        .pos-prescription-head > div { flex-basis: calc(100% - 50px); }
        .pos-prescription-status { margin-left: 48px; }
        .pos-prescription-type-grid,
        .pos-prescription-form-grid { grid-template-columns: 1fr; }
        .pos-prescription-mode-summary { align-items: flex-start; flex-wrap: wrap; }
        .pos-prescription-mode-copy { flex-basis: calc(100% - 46px); }
        .pos-change-prescription-type { margin-left: 43px; }
        .pos-prescription-type-step { padding: 10px; }
        .pos-prescription-type-step-shell { border-radius: 16px; }
        .pos-prescription-type-step .modal-header,
        .pos-prescription-type-step .modal-body { padding: 16px; }
        .pos-prescription-type-option { min-height: 0; padding: 14px; }
        .pos-prescription-type-step .modal-footer { align-items: flex-start; padding: 12px 16px; }
        .pos-prescription-modal-heading > span { width: 40px; height: 40px; font-size: 20px; }
        .pos-prescription-modal-heading h2 { font-size: 16px; }
        .pos-prescription-modal-heading p { display: none; }
        .pos-prescription-cashier-step { height: 100dvh; }
        .pos-prescription-cashier-header { gap: 8px; padding: 9px; }
        .pos-prescription-cashier-brand > span { width: 37px; height: 37px; font-size: 19px; }
        .pos-prescription-cashier-brand p { display: none; }
        .pos-prescription-flow-cancel { width: 35px; height: 35px; overflow: hidden; padding: 0; font-size: 0; }
        .pos-prescription-flow-cancel i { font-size: 17px; }
        .pos-prescription-cashier-body { padding: 7px; }
        .pos-prescription-cashier-footer { min-height: 68px; grid-template-columns: minmax(0, 1fr) minmax(190px, auto); gap: 8px; padding: 8px; }
        .pos-prescription-flow-readiness > span { width: 34px; height: 34px; font-size: 17px; }
        .pos-prescription-flow-readiness small { display: none; }
        .pos-prescription-flow-readiness strong { white-space: normal; }
        .pos-prescription-draft-button { display: none; }
        .pos-finish-prescription-button { min-width: 190px; padding: 8px 10px; }
        .pos-prescription-payment-handoff { align-items: flex-start; flex-wrap: wrap; margin: 10px; padding: 12px; }
        .pos-prescription-handoff-copy { flex-basis: calc(100% - 62px); }
        .pos-prescription-handoff-total { flex: 1 1 auto; border-right: 0; border-left: 0; text-align: left; }
        .pos-edit-prescription-button { flex: 0 0 auto; }
        .pos-compound-steps { justify-content: flex-start; overflow-x: auto; }
        .pos-compound-controls,
        .pos-compound-group-fields,
        .pos-prescription-item-grid.is-compound-component { grid-template-columns: 1fr; }
        .pos-prescription-guidance { align-items: flex-start; flex-wrap: wrap; }
        .pos-prescription-counter { margin-left: 36px; }
        .pos-shortcut-card { display: none; }
        .pos-transaction-hero { padding: 14px 12px 11px; }
        .pos-transaction-head { align-items: flex-start; }
        .pos-transaction-number { width: 43px; height: 43px; border-radius: 13px; font-size: 13px; }
        .pos-transaction-heading h2 { font-size: 15px; }
        .pos-transaction-heading p { display: none; }
        .pos-draft-pill { font-size: 9px; }
        .pos-transaction-insights { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin-top: 12px; }
        .pos-insight-card { padding: 7px; }
        .pos-insight-icon { width: 28px; height: 28px; font-size: 14px; }
        .pos-cart-readiness { grid-template-columns: auto minmax(70px, 1fr); }
        .pos-cart-readiness > small { display: none; }
        .pos-transaction-details { margin: 11px 10px 0; }
        .pos-detail-summary { max-width: 100px; }
        .pos-transaction-form-intro { align-items: flex-start; flex-wrap: wrap; }
        .pos-transaction-form-intro > div { flex-basis: calc(100% - 40px); }
        .pos-general-customer-button { margin-left: 38px; }
        .pos-cart-toolbar { align-items: flex-start; margin: 13px 10px 8px; }
        .pos-cart-title small { white-space: normal; }
        .pos-cart-actions { flex: 0 0 auto; }
        .pos-live-label { display: none; }
        .pos-cart-wrap { max-height: none; margin: 0 10px; }
        .pos-cart-footnote { align-items: flex-start; margin: 8px 10px 11px; }
        .pos-cart-footnote span:last-child { display: none; }
        .pos-checkout-stage { border-radius: 15px; }
        .pos-checkout-hero { border-radius: 14px 14px 0 0; }
        .pos-checkout-hero { min-height: 0; align-items: flex-start; flex-direction: column; gap: 13px; padding: 16px 14px; }
        .pos-checkout-step { width: 45px; height: 45px; border-radius: 13px; font-size: 14px; }
        .pos-checkout-title h2 { font-size: 17px; }
        .pos-checkout-title p { display: none; }
        .pos-checkout-hero-side { width: 100%; justify-content: space-between; gap: 10px; }
        .pos-checkout-due { min-width: 0; padding-left: 10px; }
        .pos-checkout-due strong { font-size: 20px; }
        .pos-payment-layout { gap: 10px; padding: 10px; }
        .pos-payment-panel,
        .pos-total-panel { padding: 13px; border-radius: 13px; }
        .pos-payment-head { align-items: flex-start; }
        .pos-payment-head > div:first-child { align-items: flex-start; }
        .pos-payment-title-copy small { white-space: normal; }
        .pos-payment-head-actions { align-items: flex-end; flex-direction: column-reverse; }
        .pos-payment-count { display: none; }
        .pos-payment-rows-head { display: none; }
        .pos-payment-rows { margin-top: 10px; }
        .pos-payment-row { grid-template-columns: 28px minmax(0, 1fr) 34px; align-items: start; gap: 7px; padding: 9px; }
        .pos-payment-row-number { grid-column: 1; grid-row: 1; }
        .pos-payment-row .pos-payment-field { grid-column: 2; }
        .pos-payment-field > label { display: block; }
        .pos-payment-row .remove-payment-row { grid-column: 3; grid-row: 1 / 4; align-self: stretch; height: auto; min-height: 35px; }
        .pos-payment-quick { align-items: flex-start; flex-direction: column; }
        .pos-payment-quick-options { width: 100%; justify-content: flex-start; }
        .pos-payment-coverage-copy { align-items: flex-start; flex-direction: column; gap: 1px; }
        .pos-payment-coverage-copy small { width: 100%; text-align: left; white-space: normal; }
        .pos-total-panel { display: flex; }
        .pos-submit-bar { position: static; align-items: stretch; flex-direction: column; margin: 0 10px 10px; }
        .pos-submit-bar > div:last-child { display: grid; grid-template-columns: .8fr 1.2fr; }
        .pos-submit-bar .btn { justify-content: center; }
        .pos-quote-box { grid-template-columns: 1fr; }
        .pos-quote-box > div { display: flex; align-items: center; justify-content: space-between; border-right: 0; border-bottom: 1px solid #e2e7ef; }
        .pos-quote-box > div:last-child { border-bottom: 0; }
        .pos-toolbar { align-items: flex-start; flex-direction: column; }
        .pos-toolbar-actions { width: 100%; }
        .pos-toolbar-actions .btn { width: 100%; }
    }

    /* Cashier typography aligned with the clear scale used by Pembelian and Stok. */
    .pos-page--cashier,
    .pos-product-dropdown {
        --pos-readable-caption: .75rem;
        --pos-readable-small: .8rem;
        --pos-readable-body: .86rem;
        --pos-readable-heading: 1rem;
        font-family: "Roboto", Arial, sans-serif;
        font-size: var(--pos-readable-body);
        font-weight: 400;
        line-height: 1.5;
        font-synthesis: none;
        text-rendering: optimizeLegibility;
    }

    .pos-page--cashier button,
    .pos-page--cashier input,
    .pos-page--cashier select,
    .pos-page--cashier textarea,
    .pos-product-dropdown input {
        font-family: inherit;
        font-synthesis: none;
    }

    .pos-page--cashier .btn { font-weight: 700; }

    .pos-page--cashier .pos-session-status,
    .pos-page--cashier .pos-clock,
    .pos-page--cashier .pos-cashier-profile strong,
    .pos-page--cashier .pos-detail-summary,
    .pos-page--cashier .pos-shortcut-card,
    .pos-page--cashier .pos-stock-warning { font-weight: 500; }

    .pos-page--cashier .pos-shortcut-card > span,
    .pos-page--cashier .pos-total-row.is-grand > span { font-weight: 700; }

    .pos-page--cashier .form-control,
    .pos-page--cashier .form-select,
    .pos-page--cashier .select2-container--default .select2-selection--single .select2-selection__rendered,
    .pos-page--cashier .pos-qty-stepper input.form-control,
    .pos-page--cashier .pos-discount-control input.form-control,
    .pos-page--cashier .pos-total-panel .form-control { font-weight: 400 !important; }

    .pos-page--cashier .pos-search-control .select2-container--default .select2-selection--single .select2-selection__rendered {
        font-weight: 500 !important;
    }

    .pos-page--cashier .pos-discount-control span,
    .pos-page--cashier .pos-fefo-allocation,
    .pos-page--cashier .pos-fefo-allocation b,
    .pos-page--cashier .pos-fefo-allocation small { font-weight: 500; }

    .pos-page--cashier strong,
    .pos-page--cashier b { font-weight: 700; }

    .pos-page--cashier small {
        font-size: var(--pos-readable-caption) !important;
        font-weight: 400;
        line-height: 1.45;
    }

    .pos-page--cashier .pos-brand h1 {
        font-size: 1.35rem;
        font-weight: 900;
        line-height: 1.2;
        letter-spacing: -.025em;
    }

    .pos-page--cashier .pos-panel-heading h2,
    .pos-page--cashier .pos-catalog-hero h2,
    .pos-page--cashier .pos-transaction-heading h2 {
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: 0;
    }

    .pos-page--cashier .pos-eyebrow,
    .pos-page--cashier .pos-section-kicker,
    .pos-page--cashier .pos-total-heading,
    .pos-page--cashier .pos-search-eyebrow,
    .pos-page--cashier .pos-fefo-label,
    .pos-page--cashier .pos-live-label,
    .pos-page--cashier .pos-payment-quick > span,
    .pos-page--cashier .pos-cart-table thead th,
    .pos-page--cashier .pos-stock-caption,
    .pos-page--cashier .pos-catalog-block-head small {
        font-size: var(--pos-readable-caption) !important;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: .04em;
    }

    .pos-page--cashier .pos-brand-title > span,
    .pos-page--cashier .pos-session-status,
    .pos-page--cashier .pos-clock,
    .pos-page--cashier .pos-cashier-profile strong,
    .pos-page--cashier .pos-catalog-hero p,
    .pos-page--cashier .pos-search-feedback p,
    .pos-page--cashier .pos-search-scope span,
    .pos-page--cashier .pos-flow-step small,
    .pos-page--cashier .pos-flow-summary small,
    .pos-page--cashier .pos-catalog-phase small,
    .pos-page--cashier .pos-fefo-placeholder,
    .pos-page--cashier .pos-cart-readiness,
    .pos-page--cashier .pos-discount-result,
    .pos-page--cashier .pos-fefo-allocation,
    .pos-page--cashier .pos-stock-warning,
    .pos-page--cashier .pos-cart-empty-meta,
    .pos-page--cashier .pos-cart-footnote,
    .pos-page--cashier .pos-money-note {
        font-size: var(--pos-readable-caption) !important;
        font-weight: 400;
        line-height: 1.45;
    }

    .pos-page--cashier .pos-btn-quiet,
    .pos-page--cashier .pos-draft-pill,
    .pos-page--cashier .pos-catalog-ready,
    .pos-page--cashier .pos-block-state,
    .pos-page--cashier .pos-fefo-badge,
    .pos-page--cashier .pos-block-number,
    .pos-page--cashier .pos-product-name .badge,
    .pos-page--cashier .pos-product-card > .mt-3,
    .pos-page--cashier .pos-fefo-chip,
    .pos-page--cashier .pos-shortcut-card,
    .pos-page--cashier .pos-detail-summary,
    .pos-page--cashier .pos-general-customer-button,
    .pos-page--cashier .pos-clear-button,
    .pos-page--cashier .pos-item-badges span,
    .pos-page--cashier .pos-discount-control span,
    .pos-page--cashier .pos-empty-search-button,
    .pos-page--cashier .pos-add-payment-button,
    .pos-page--cashier .pos-quick-payment,
    .pos-page--cashier .pos-total-row span,
    .pos-page--cashier .pos-balance-row span,
    .pos-page--cashier .pos-submit-status p,
    .pos-page--cashier .pos-search-loading {
        font-size: var(--pos-readable-small) !important;
        line-height: 1.45;
    }

    .pos-page--cashier .pos-btn-quiet,
    .pos-page--cashier .pos-catalog-ready,
    .pos-page--cashier .pos-block-state,
    .pos-page--cashier .pos-fefo-badge,
    .pos-page--cashier .pos-block-number,
    .pos-page--cashier .pos-product-name .badge,
    .pos-page--cashier .pos-general-customer-button,
    .pos-page--cashier .pos-clear-button,
    .pos-page--cashier .pos-item-badges span,
    .pos-page--cashier .pos-empty-search-button,
    .pos-page--cashier .pos-add-payment-button,
    .pos-page--cashier .pos-quick-payment { font-weight: 700; }

    .pos-page--cashier .pos-flow-step strong,
    .pos-page--cashier .pos-flow-summary strong,
    .pos-page--cashier .pos-catalog-block-head strong,
    .pos-page--cashier .pos-product-meta span,
    .pos-page--cashier .pos-field label,
    .pos-page--cashier .pos-add-button,
    .pos-page--cashier .pos-quote-box strong,
    .pos-page--cashier .pos-insight-card strong,
    .pos-page--cashier .pos-detail-customer strong,
    .pos-page--cashier .pos-transaction-form-intro strong,
    .pos-page--cashier .pos-cart-title strong,
    .pos-page--cashier .pos-item-copy strong,
    .pos-page--cashier .pos-money,
    .pos-page--cashier .pos-line-total,
    .pos-page--cashier .pos-total-row > strong,
    .pos-page--cashier .pos-tax-value strong,
    .pos-page--cashier .pos-balance-row strong,
    .pos-page--cashier .pos-save-draft-button,
    .pos-page--cashier .pos-complete-button,
    .pos-page--cashier .form-control,
    .pos-page--cashier .form-select,
    .pos-page--cashier .select2-container--default .select2-selection--single,
    .pos-page--cashier .select2-container--default .select2-selection--single .select2-selection__rendered,
    .pos-page--cashier .pos-cart-table tbody td {
        font-size: var(--pos-readable-body) !important;
        line-height: 1.45;
    }

    .pos-page--cashier .pos-flow-step strong,
    .pos-page--cashier .pos-catalog-block-head strong,
    .pos-page--cashier .pos-field label,
    .pos-page--cashier .pos-add-button,
    .pos-page--cashier .pos-quote-box strong,
    .pos-page--cashier .pos-insight-card strong,
    .pos-page--cashier .pos-detail-customer strong,
    .pos-page--cashier .pos-transaction-form-intro strong,
    .pos-page--cashier .pos-cart-title strong,
    .pos-page--cashier .pos-item-copy strong,
    .pos-page--cashier .pos-money,
    .pos-page--cashier .pos-line-total,
    .pos-page--cashier .pos-total-row > strong,
    .pos-page--cashier .pos-tax-value strong,
    .pos-page--cashier .pos-balance-row strong,
    .pos-page--cashier .pos-save-draft-button,
    .pos-page--cashier .pos-complete-button { font-weight: 700; }

    .pos-page--cashier .pos-search-title,
    .pos-page--cashier .pos-cart-title strong,
    .pos-page--cashier .pos-payment-head h2 {
        font-size: .95rem !important;
        font-weight: 700;
        line-height: 1.4;
    }

    .pos-page--cashier .pos-product-name strong {
        font-size: 1rem !important;
        font-weight: 700;
        line-height: 1.35;
    }

    .pos-page--cashier .pos-product-name small,
    .pos-page--cashier .pos-product-meta span,
    .pos-page--cashier .pos-product-card > .mt-3,
    .pos-page--cashier .pos-quote-box strong,
    .pos-page--cashier .pos-fefo-chip,
    .pos-page--cashier .pos-shortcut-card {
        font-size: var(--pos-readable-small) !important;
    }

    .pos-page--cashier .pos-field label { font-size: var(--pos-readable-small) !important; }

    .pos-page--cashier .pos-add-button,
    .pos-page--cashier .pos-save-draft-button,
    .pos-page--cashier .pos-complete-button {
        font-size: var(--pos-readable-body) !important;
        font-weight: 700;
    }

    .pos-page--cashier .pos-cart-table thead th {
        font-weight: 700;
        letter-spacing: .02em;
    }

    .pos-page--cashier .pos-cart-table tbody td {
        font-weight: 400;
        color: #334155;
    }

    .pos-page--cashier .pos-total-row.is-grand,
    .pos-page--cashier .pos-total-row.is-grand strong,
    .pos-page--cashier .pos-balance-row strong,
    .pos-page--cashier .pos-flow-summary > span:last-child strong {
        font-size: 1.05rem !important;
        font-weight: 900;
        line-height: 1.35;
    }

    .pos-page--cashier kbd,
    .pos-page--cashier .pos-flow-icon b,
    .pos-page--cashier .pos-search-ready {
        font-size: var(--pos-readable-caption) !important;
        font-weight: 700;
        line-height: 1;
    }

    /* Select2 mounts this product menu outside .pos-page--cashier. */
    .pos-product-dropdown .select2-search__field,
    .pos-product-dropdown .select2-results__option,
    .pos-product-dropdown .pos-search-result-name,
    .pos-product-dropdown .pos-search-result-price {
        font-size: var(--pos-readable-body) !important;
        line-height: 1.45;
    }

    .pos-product-dropdown .pos-search-result-name,
    .pos-product-dropdown .pos-search-result-price { font-weight: 700; }

    .pos-product-dropdown .pos-search-result-code,
    .pos-product-dropdown .pos-search-result-meta,
    .pos-product-dropdown .pos-search-result-stock {
        font-size: var(--pos-readable-caption) !important;
        font-weight: 400;
        line-height: 1.4;
    }

    .pos-product-dropdown .pos-search-result-stock { font-weight: 700; }

    .pos-product-dropdown .pos-search-result-code b {
        font-size: var(--pos-readable-caption) !important;
        font-weight: 700;
    }

    /* Checkout keeps the global readable scale while preserving its compact controls. */
    .pos-page--cashier .pos-checkout-title h2 {
        font-size: 1.2rem;
        font-weight: 800;
    }

    .pos-page--cashier .pos-payment-title-copy strong,
    .pos-page--cashier .pos-total-heading strong,
    .pos-page--cashier .pos-payment-coverage-copy strong {
        font-size: var(--pos-readable-body);
        font-weight: 700;
    }

    .pos-page--cashier .pos-payment-title-copy small,
    .pos-page--cashier .pos-payment-quick-copy small,
    .pos-page--cashier .pos-payment-coverage-copy small,
    .pos-page--cashier .pos-tax-switch small {
        font-size: var(--pos-readable-caption) !important;
    }

    .pos-page--cashier .pos-payment-row-number,
    .pos-page--cashier .pos-payment-field > label,
    .pos-page--cashier .pos-payment-count,
    .pos-page--cashier .pos-summary-item-count,
    .pos-page--cashier .pos-adjustment-card-head,
    .pos-page--cashier .pos-adjustment-row > div > label,
    .pos-page--cashier .pos-tax-switch b {
        font-size: var(--pos-readable-small) !important;
    }

    .pos-page--cashier .pos-adjustment-fields > label > span,
    .pos-page--cashier .pos-tax-value > label span,
    .pos-page--cashier .pos-adjustment-card-head small,
    .pos-page--cashier .pos-complete-button > span small,
    .pos-page--cashier .pos-submit-status small {
        font-size: var(--pos-readable-caption) !important;
    }

    @media (max-width: 480px) {
        .pos-checkout-state { display: none; }
        .pos-checkout-hero-side { justify-content: flex-end; }
        .pos-checkout-due { width: 100%; padding: 9px 10px; border: 1px solid rgba(255, 255, 255, .14); border-radius: 11px; background: rgba(255, 255, 255, .06); text-align: left; }
        .pos-payment-head { align-items: stretch; flex-direction: column; }
        .pos-payment-head-actions { width: 100%; align-items: stretch; }
        .pos-add-payment-button { justify-content: center; }
        .pos-quick-payment { flex: 1 1 calc(33.333% - 6px); }
        .pos-quick-payment.is-exact { flex-basis: 100%; justify-content: center; }
        .pos-tax-row { align-items: stretch; flex-direction: column; }
        .pos-tax-value { justify-content: space-between; }
        .pos-submit-bar > div:last-child { grid-template-columns: 1fr; }
        .pos-save-draft-button { min-height: 40px; }
        .pos-complete-button { min-height: 50px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pos-page *,
        .pos-page *::before,
        .pos-page *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }
</style>
