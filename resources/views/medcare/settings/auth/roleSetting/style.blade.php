<style>
    .role-setting-page {
        --rs-primary: #0f766e;
        --rs-primary-dark: #115e59;
        --rs-ink: #172033;
        --rs-muted: #64748b;
        --rs-line: #e2e8f0;
        display: grid;
        gap: 18px;
    }

    .role-setting-breadcrumb a { color: var(--rs-primary); }

    .role-setting-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 24px;
        border: 1px solid #dbe7f0;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8fbff 0%, #eefaf6 55%, #fff8e9 100%);
        box-shadow: 0 16px 38px rgba(15, 23, 42, .07);
    }

    .role-setting-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
        padding: 5px 9px;
        color: var(--rs-primary);
        background: rgba(15, 118, 110, .1);
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .role-setting-hero h1 {
        margin: 0;
        color: var(--rs-ink);
        font-size: clamp(24px, 3vw, 32px);
        font-weight: 800;
    }

    .role-setting-hero p { margin: 6px 0 0; color: var(--rs-muted); }

    .role-setting-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 17px;
        border: 0;
        border-radius: 8px;
        color: #fff;
        background: var(--rs-primary);
        box-shadow: 0 12px 26px rgba(15, 118, 110, .23);
        font-weight: 800;
        white-space: nowrap;
        transition: .18s ease;
    }

    .role-setting-save:hover { background: var(--rs-primary-dark); transform: translateY(-1px); }
    .role-setting-save:disabled { opacity: .7; transform: none; }

    .role-setting-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .role-setting-summary article {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 88px;
        padding: 15px;
        border: 1px solid var(--rs-line);
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .045);
    }

    .role-setting-summary article > span {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        font-size: 22px;
    }

    .role-setting-summary .is-role { color: #1d4ed8; background: #dbeafe; }
    .role-setting-summary .is-branch { color: #7c3aed; background: #ede9fe; }
    .role-setting-summary .is-approval { color: #047857; background: #d1fae5; }
    .role-setting-summary .is-notification { color: #b45309; background: #fef3c7; }
    .role-setting-summary strong { display: block; color: var(--rs-ink); font-size: 24px; line-height: 1; }
    .role-setting-summary small { display: block; margin-top: 6px; color: var(--rs-muted); font-weight: 700; }

    .role-setting-guide {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid #bfdbfe;
        border-radius: 9px;
        color: #1e3a5f;
        background: #eff6ff;
    }

    .role-setting-guide > span { color: #2563eb; font-size: 22px; line-height: 1; }
    .role-setting-guide strong { display: block; margin-bottom: 2px; }
    .role-setting-guide p { margin: 0; color: #475569; font-size: 13px; }

    .role-setting-panel {
        overflow: hidden;
        border: 1px solid var(--rs-line);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .055);
    }

    .role-setting-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--rs-line);
        background: #fbfdff;
    }

    .role-setting-search {
        display: flex;
        align-items: center;
        gap: 8px;
        width: min(330px, 100%);
        min-height: 40px;
        padding: 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #94a3b8;
        background: #fff;
    }

    .role-setting-search:focus-within { border-color: #5eead4; box-shadow: 0 0 0 3px rgba(20, 184, 166, .12); }
    .role-setting-search input { width: 100%; border: 0; outline: 0; color: var(--rs-ink); background: transparent; }

    .role-setting-filters { display: flex; gap: 7px; flex-wrap: wrap; }
    .role-setting-filters button {
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        color: #475569;
        background: #fff;
        font-size: 12px;
        font-weight: 800;
    }
    .role-setting-filters button:hover,
    .role-setting-filters button.is-active { color: #fff; border-color: var(--rs-primary); background: var(--rs-primary); }

    .role-setting-table-head,
    .role-setting-row {
        display: grid;
        grid-template-columns: minmax(180px, 1.05fr) minmax(170px, .85fr) minmax(210px, 1fr) minmax(230px, 1.1fr);
    }

    .role-setting-table-head {
        padding: 11px 18px;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid var(--rs-line);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .role-setting-table-head span:not(:first-child) { padding-left: 16px; }

    .role-setting-row { border-bottom: 1px solid var(--rs-line); transition: background .18s ease; }
    .role-setting-row:last-child { border-bottom: 0; }
    .role-setting-row:hover { background: #fcfefe; }
    .role-setting-row.is-hidden { display: none; }

    .role-setting-identity,
    .role-setting-control { min-width: 0; padding: 16px 18px; }

    .role-setting-control { border-left: 1px solid #edf2f7; }

    .role-setting-identity {
        display: flex;
        align-items: flex-start;
        gap: 11px;
    }

    .role-setting-avatar {
        display: grid;
        place-items: center;
        flex: 0 0 40px;
        height: 40px;
        border-radius: 9px;
        color: #0f766e;
        background: #ccfbf1;
        font-size: 13px;
        font-weight: 900;
    }

    .role-setting-identity strong { display: block; color: var(--rs-ink); overflow-wrap: anywhere; }
    .role-setting-identity small { display: block; margin-top: 3px; color: var(--rs-muted); }

    .role-setting-switch-row {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
    }

    .role-setting-switch-row strong { display: block; color: #334155; font-size: 13px; }
    .role-setting-switch-row small { display: block; margin-top: 2px; color: #94a3b8; font-size: 11px; line-height: 1.35; }
    .role-setting-toggle { position: absolute; opacity: 0; pointer-events: none; }

    .role-setting-switch-row > i {
        position: relative;
        flex: 0 0 42px;
        width: 42px;
        height: 24px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: .18s ease;
    }

    .role-setting-switch-row > i::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .22);
        transition: transform .18s ease;
    }

    .role-setting-toggle:checked + i { background: var(--rs-primary); }
    .role-setting-toggle:checked + i::after { transform: translateX(18px); }
    .role-setting-toggle:focus-visible + i { outline: 3px solid rgba(20, 184, 166, .25); outline-offset: 2px; }

    .role-setting-scope { display: block; margin-top: 12px; }
    .role-setting-scope > span { display: block; margin-bottom: 5px; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .role-setting-scope .form-select { min-height: 36px; padding-top: 6px; padding-bottom: 6px; border-color: #dbe3ec; font-size: 12px; }
    .role-setting-scope .form-select:disabled { color: #94a3b8; background-color: #f1f5f9; opacity: 1; }
    .role-setting-mobile-label { display: none; }

    .role-setting-empty,
    .role-setting-no-result {
        display: grid;
        justify-items: center;
        gap: 3px;
        padding: 38px 20px;
        color: var(--rs-muted);
        text-align: center;
    }

    .role-setting-empty i,
    .role-setting-no-result i { color: #94a3b8; font-size: 32px; }
    .role-setting-empty strong,
    .role-setting-no-result strong { color: #334155; }
    .role-setting-empty p { margin: 0; }
    .role-setting-no-result[hidden] { display: none; }

    @media (max-width: 1199.98px) {
        .role-setting-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .role-setting-table-head { display: none; }
        .role-setting-row { grid-template-columns: repeat(3, minmax(0, 1fr)); padding: 0 16px 16px; }
        .role-setting-identity { grid-column: 1 / -1; padding: 16px 0 13px; border-bottom: 1px solid var(--rs-line); }
        .role-setting-control { padding: 14px; border-left: 0; }
        .role-setting-control + .role-setting-control { border-left: 1px solid #edf2f7; }
        .role-setting-mobile-label { display: block; margin-bottom: 11px; color: #64748b; font-size: 10px; font-weight: 900; text-transform: uppercase; }
    }

    @media (max-width: 767.98px) {
        .role-setting-hero,
        .role-setting-toolbar { align-items: stretch; flex-direction: column; }
        .role-setting-save { width: 100%; }
        .role-setting-summary { grid-template-columns: 1fr; }
        .role-setting-search { width: 100%; }
        .role-setting-row { grid-template-columns: 1fr; }
        .role-setting-identity { grid-column: auto; }
        .role-setting-control { padding: 15px 0; }
        .role-setting-control + .role-setting-control { border-top: 1px dashed #dbe3ec; border-left: 0; }
    }
</style>
