<style>
    .po-setting-page {
        --po-primary: #0f766e;
        --po-primary-dark: #115e59;
        --po-ink: #0f172a;
        --po-muted: #64748b;
        --po-border: #dbe5e7;
        display: grid;
        gap: 16px;
    }

    .po-setting-hero {
        position: relative;
        display: flex;
        justify-content: space-between;
        gap: 24px;
        overflow: hidden;
        padding: 24px;
        color: #fff;
        border-radius: 18px;
        background: linear-gradient(125deg, #0f766e, #0f4c5c 72%, #0b3b47);
        box-shadow: 0 16px 34px rgba(15, 76, 92, .2);
    }

    .po-setting-hero::after {
        position: absolute;
        right: -50px;
        bottom: -95px;
        width: 260px;
        height: 260px;
        content: "";
        border: 34px solid rgba(255, 255, 255, .07);
        border-radius: 50%;
    }

    .po-setting-hero-copy, .po-setting-hero-actions { position: relative; z-index: 1; }
    .po-setting-hero-copy { max-width: 720px; }
    .po-setting-kicker { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .85; }
    .po-setting-hero h4 { margin: 7px 0 5px; font-size: 28px; font-weight: 850; }
    .po-setting-hero p { margin: 0; max-width: 680px; color: rgba(255,255,255,.84); font-size: 13px; line-height: 1.6; }
    .po-setting-note { display: flex; align-items: center; gap: 8px; margin-top: 14px; padding: 9px 12px; width: fit-content; color: #d9fffa; border: 1px solid rgba(255,255,255,.14); border-radius: 10px; background: rgba(255,255,255,.08); font-size: 11px; }
    .po-setting-hero-actions { display: flex; align-items: flex-start; justify-content: flex-end; gap: 8px; min-width: 310px; }
    .po-setting-hero-actions .btn { display: inline-flex; align-items: center; gap: 7px; min-height: 40px; padding: 0 14px; color: #fff; border-radius: 10px; font-size: 12px; font-weight: 800; }
    .po-setting-secondary { border: 1px solid rgba(255,255,255,.28); background: rgba(255,255,255,.08); }
    .po-setting-save { border: 1px solid #fff; background: #fff; color: var(--po-primary) !important; }
    .po-setting-hero-actions .btn:disabled { opacity: .55; }
    #poSettingDirtyCount { display: grid; place-items: center; min-width: 19px; height: 19px; padding: 0 5px; color: #fff; border-radius: 99px; background: #f59e0b; font-size: 10px; }

    .po-setting-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 11px; }
    .po-setting-summary-card { display: flex; align-items: center; gap: 12px; width: 100%; padding: 14px; text-align: left; border: 1px solid var(--po-border); border-radius: 14px; background: #fff; transition: .18s ease; }
    .po-setting-summary-card:hover, .po-setting-summary-card.is-active { transform: translateY(-1px); border-color: #5eead4; box-shadow: 0 8px 22px rgba(15, 118, 110, .09); }
    .po-setting-summary-card > span { display: grid; place-items: center; width: 39px; height: 39px; flex: 0 0 39px; border-radius: 11px; font-size: 19px; }
    .po-setting-summary-card strong, .po-setting-summary-card small { display: block; }
    .po-setting-summary-card strong { color: var(--po-ink); font-size: 20px; line-height: 1; }
    .po-setting-summary-card small { margin-top: 5px; color: var(--po-muted); font-size: 10px; font-weight: 700; }
    .po-setting-summary-card.is-all > span { color: #0369a1; background: #e0f2fe; }
    .po-setting-summary-card.is-automatic > span { color: #047857; background: #d1fae5; }
    .po-setting-summary-card.is-manual > span { color: #b45309; background: #fef3c7; }
    .po-setting-summary-card.is-active-distributor > span { color: #6d28d9; background: #ede9fe; }

    .po-setting-workspace { overflow: hidden; border: 1px solid var(--po-border); border-radius: 16px; background: #fff; box-shadow: 0 8px 24px rgba(15,23,42,.05); }
    .po-setting-toolbar { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid var(--po-border); background: #f8fafc; }
    .po-setting-search { display: flex; align-items: center; gap: 8px; width: min(430px, 100%); height: 39px; padding: 0 11px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; }
    .po-setting-search:focus-within { border-color: #2dd4bf; box-shadow: 0 0 0 3px rgba(45,212,191,.12); }
    .po-setting-search > i { color: #94a3b8; }
    .po-setting-search input { min-width: 0; flex: 1; border: 0; outline: 0; background: transparent; font-size: 12px; }
    .po-setting-search button { display: none; padding: 0; color: #94a3b8; border: 0; background: transparent; }
    .po-setting-visible { display: flex; align-items: baseline; gap: 4px; color: #94a3b8; font-size: 10px; white-space: nowrap; }
    .po-setting-visible strong { color: var(--po-ink); font-size: 13px; }
    .po-setting-bulk-actions { display: flex; justify-content: flex-end; gap: 7px; margin-left: auto; }
    .po-setting-bulk-actions .btn { color: #475569; border: 1px solid #cbd5e1; background: #fff; font-size: 10px; font-weight: 800; }
    .po-setting-bulk-actions .btn:hover { color: var(--po-primary); border-color: #5eead4; }

    .po-setting-list { display: grid; gap: 0; }
    .po-setting-row { display: grid; grid-template-columns: minmax(280px, 1.4fr) auto minmax(280px, 1fr); align-items: center; gap: 18px; padding: 15px 18px; border-bottom: 1px solid #edf2f4; transition: background .16s ease; }
    .po-setting-row:last-child { border-bottom: 0; }
    .po-setting-row:hover { background: #fbfefd; }
    .po-setting-row.is-dirty { background: #fffbeb; box-shadow: inset 3px 0 #f59e0b; }
    .po-setting-row.is-hidden { display: none; }
    .po-setting-identity { display: flex; align-items: center; gap: 11px; min-width: 0; }
    .po-setting-avatar { display: grid; place-items: center; width: 40px; height: 40px; flex: 0 0 40px; color: #0f766e; border-radius: 11px; background: #ccfbf1; font-size: 12px; font-weight: 900; }
    .po-setting-identity > div { min-width: 0; }
    .po-setting-identity strong { display: block; overflow: hidden; color: var(--po-ink); font-size: 13px; text-overflow: ellipsis; white-space: nowrap; }
    .po-setting-identity small { display: flex; align-items: center; gap: 8px; min-width: 0; margin-top: 4px; color: var(--po-muted); font-size: 10px; }
    .po-setting-identity code { padding: 2px 5px; color: #0f766e; border-radius: 5px; background: #f0fdfa; font-size: 9px; font-weight: 800; }
    .po-setting-identity small span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .po-setting-status-wrap { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }
    .po-setting-distributor-status, .po-setting-mode-badge { display: inline-flex; align-items: center; gap: 4px; padding: 5px 8px; border: 1px solid; border-radius: 99px; font-size: 9px; font-weight: 800; white-space: nowrap; }
    .po-setting-distributor-status.is-active { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
    .po-setting-distributor-status.is-inactive { color: #64748b; border-color: #e2e8f0; background: #f8fafc; }
    .po-setting-mode-badge.is-automatic { color: #0369a1; border-color: #bae6fd; background: #f0f9ff; }
    .po-setting-mode-badge.is-manual { color: #b45309; border-color: #fde68a; background: #fffbeb; }
    .po-setting-switch { display: flex; align-items: center; gap: 10px; margin: 0; cursor: pointer; }
    .po-setting-switch input { position: absolute; opacity: 0; pointer-events: none; }
    .po-setting-switch-track { position: relative; width: 42px; height: 23px; flex: 0 0 42px; border-radius: 99px; background: #cbd5e1; transition: .18s ease; }
    .po-setting-switch-track span { position: absolute; top: 3px; left: 3px; width: 17px; height: 17px; border-radius: 50%; background: #fff; box-shadow: 0 2px 5px rgba(15,23,42,.25); transition: .18s ease; }
    .po-setting-switch input:checked + .po-setting-switch-track { background: #f59e0b; }
    .po-setting-switch input:checked + .po-setting-switch-track span { transform: translateX(19px); }
    .po-setting-switch input:focus-visible + .po-setting-switch-track { outline: 3px solid rgba(45,212,191,.25); }
    .po-setting-switch-copy strong, .po-setting-switch-copy small { display: block; }
    .po-setting-switch-copy strong { color: #334155; font-size: 11px; }
    .po-setting-switch-copy small { margin-top: 2px; color: #94a3b8; font-size: 9px; line-height: 1.35; }
    .po-setting-empty, .po-setting-no-result { display: grid; justify-items: center; gap: 5px; padding: 54px 20px; color: #94a3b8; text-align: center; }
    .po-setting-empty i, .po-setting-no-result i { font-size: 34px; }
    .po-setting-empty strong, .po-setting-no-result strong { color: #475569; }
    .po-setting-empty span, .po-setting-no-result span { font-size: 11px; }

    @media (max-width: 1100px) {
        .po-setting-row { grid-template-columns: minmax(250px, 1fr) auto; }
        .po-setting-switch { grid-column: 1 / -1; padding-left: 51px; }
    }
    @media (max-width: 850px) {
        .po-setting-hero { flex-direction: column; }
        .po-setting-hero-actions { justify-content: flex-start; min-width: 0; }
        .po-setting-summary { grid-template-columns: repeat(2, 1fr); }
        .po-setting-toolbar { flex-wrap: wrap; }
        .po-setting-search { width: 100%; }
        .po-setting-bulk-actions { width: 100%; margin-left: 0; justify-content: flex-start; }
    }
    @media (max-width: 600px) {
        .po-setting-hero { padding: 19px; }
        .po-setting-hero-actions { flex-wrap: wrap; }
        .po-setting-summary { grid-template-columns: 1fr; }
        .po-setting-row { grid-template-columns: 1fr; gap: 11px; }
        .po-setting-status-wrap { justify-content: flex-start; padding-left: 51px; }
        .po-setting-switch { grid-column: auto; padding-left: 51px; }
    }
</style>
