<style>
    .role-setting-page {
        --rs-primary: #0f766e;
        --rs-primary-dark: #115e59;
        --rs-ink: #172033;
        --rs-muted: #64748b;
        --rs-line: #e2e8f0;
        --rs-soft: #f8fafc;
        display: grid;
        gap: 18px;
        padding-bottom: 10px;
    }

    .role-setting-breadcrumb a { color: var(--rs-primary); }

    .role-setting-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 27px 30px;
        border: 1px solid #d8e5eb;
        border-radius: 12px;
        background: linear-gradient(118deg, #f8fbff 0%, #edf9f5 62%, #fff9ec 100%);
        box-shadow: 0 16px 38px rgba(15, 23, 42, .07);
    }

    .role-setting-hero::after {
        content: "";
        position: absolute;
        z-index: -1;
        top: -95px;
        right: 20%;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        background: rgba(20, 184, 166, .09);
        filter: blur(2px);
    }

    .role-setting-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
        padding: 5px 9px;
        color: var(--rs-primary);
        border-radius: 6px;
        background: rgba(15, 118, 110, .1);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .role-setting-hero h1 {
        margin: 0;
        color: var(--rs-ink);
        font-size: clamp(25px, 3vw, 34px);
        font-weight: 850;
        letter-spacing: -.025em;
    }

    .role-setting-hero p {
        max-width: 680px;
        margin: 7px 0 0;
        color: var(--rs-muted);
        line-height: 1.6;
    }

    .role-setting-hero-action {
        display: flex;
        align-items: flex-end;
        flex-direction: column;
        gap: 8px;
    }

    .role-setting-save-state {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #047857;
        font-size: 11px;
        font-weight: 750;
    }

    .role-setting-page.has-unsaved .role-setting-save-state { color: #b45309; }

    .role-setting-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 18px;
        border: 0;
        border-radius: 8px;
        color: #fff;
        background: var(--rs-primary);
        box-shadow: 0 12px 26px rgba(15, 118, 110, .23);
        font-weight: 800;
        white-space: nowrap;
        transition: transform .18s ease, background .18s ease, opacity .18s ease;
    }

    .role-setting-save:hover { color: #fff; background: var(--rs-primary-dark); transform: translateY(-1px); }
    .role-setting-save:disabled { opacity: .68; transform: none; cursor: wait; }

    .role-setting-overview {
        display: grid;
        grid-template-columns: minmax(190px, .75fr) minmax(0, 2.6fr);
        gap: 12px;
    }

    .role-setting-overview-copy,
    .role-setting-metrics {
        border: 1px solid var(--rs-line);
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .045);
    }

    .role-setting-overview-copy {
        display: flex;
        justify-content: center;
        flex-direction: column;
        padding: 17px 19px;
    }

    .role-setting-overview-copy > span {
        color: var(--rs-primary);
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .065em;
        text-transform: uppercase;
    }

    .role-setting-overview-copy strong { margin-top: 3px; color: var(--rs-ink); font-size: 15px; }
    .role-setting-overview-copy p { margin: 5px 0 0; color: var(--rs-muted); font-size: 11px; line-height: 1.5; }

    .role-setting-metrics {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        overflow: hidden;
    }

    .role-setting-metrics article {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        min-height: 88px;
        padding: 14px;
    }

    .role-setting-metrics article + article { border-left: 1px solid var(--rs-line); }

    .role-setting-metrics article > span {
        display: grid;
        place-items: center;
        flex: 0 0 38px;
        height: 38px;
        border-radius: 8px;
        font-size: 20px;
    }

    .role-setting-metrics .is-role { color: #1d4ed8; background: #dbeafe; }
    .role-setting-metrics .is-branch { color: #7c3aed; background: #ede9fe; }
    .role-setting-metrics .is-pos { color: #be123c; background: #ffe4e6; }
    .role-setting-metrics .is-approval { color: #047857; background: #d1fae5; }
    .role-setting-metrics .is-notification { color: #b45309; background: #fef3c7; }
    .role-setting-metrics strong { display: block; color: var(--rs-ink); font-size: 22px; line-height: 1; }
    .role-setting-metrics small { display: block; margin-top: 6px; color: var(--rs-muted); font-size: 10px; font-weight: 750; line-height: 1.3; }

    .role-setting-guide {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 17px;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        color: #1e3a5f;
        background: #eff6ff;
    }

    .role-setting-guide > span {
        display: grid;
        place-items: center;
        flex: 0 0 38px;
        height: 38px;
        border-radius: 8px;
        color: #2563eb;
        background: #dbeafe;
        font-size: 21px;
    }

    .role-setting-guide > div:nth-child(2) { flex: 1; min-width: 0; }
    .role-setting-guide strong { display: block; margin-bottom: 2px; }
    .role-setting-guide p { margin: 0; color: #475569; font-size: 12px; line-height: 1.5; }

    .role-setting-guide-tags { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 6px; }
    .role-setting-guide-tags span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 8px;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        color: #315577;
        background: rgba(255, 255, 255, .72);
        font-size: 10px;
        font-weight: 750;
    }

    .role-setting-workspace {
        border: 1px solid var(--rs-line);
        border-radius: 12px;
        background: #f8fafc;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .055);
    }

    .role-setting-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--rs-line);
        border-radius: 12px 12px 0 0;
        background: #fff;
    }

    .role-setting-toolbar-main { display: flex; align-items: center; gap: 11px; min-width: 0; }

    .role-setting-search {
        display: flex;
        align-items: center;
        gap: 8px;
        width: min(300px, 100%);
        min-height: 40px;
        padding: 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #94a3b8;
        background: #fff;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .role-setting-search:focus-within { border-color: #5eead4; box-shadow: 0 0 0 3px rgba(20, 184, 166, .12); }
    .role-setting-search input { width: 100%; border: 0; outline: 0; color: var(--rs-ink); background: transparent; }
    .role-setting-visible-count { color: #94a3b8; font-size: 10px; font-weight: 750; white-space: nowrap; }

    .role-setting-filters { display: flex; justify-content: flex-end; gap: 6px; flex-wrap: wrap; }
    .role-setting-filters button {
        min-height: 33px;
        padding: 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        color: #475569;
        background: #fff;
        font-size: 11px;
        font-weight: 800;
        transition: .16s ease;
    }

    .role-setting-filters button:hover { border-color: #5eead4; color: var(--rs-primary); }
    .role-setting-filters button.is-active { color: #fff; border-color: var(--rs-primary); background: var(--rs-primary); }

    .role-setting-list { display: grid; gap: 14px; padding: 16px; }

    .role-setting-card {
        overflow: hidden;
        border: 1px solid #dce4ec;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 7px 18px rgba(15, 23, 42, .045);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .role-setting-card:hover { border-color: #b9d8d4; box-shadow: 0 10px 24px rgba(15, 23, 42, .07); }
    .role-setting-card.is-hidden { display: none; }

    .role-setting-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--rs-line);
        background: linear-gradient(90deg, #fff 0%, #fbfdfd 100%);
    }

    .role-setting-identity { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .role-setting-avatar {
        display: grid;
        place-items: center;
        flex: 0 0 44px;
        height: 44px;
        border: 1px solid #99f6e4;
        border-radius: 10px;
        color: #0f766e;
        background: #ccfbf1;
        font-size: 13px;
        font-weight: 900;
    }

    .role-setting-role-label { display: block; color: #94a3b8; font-size: 9px; font-weight: 850; letter-spacing: .07em; text-transform: uppercase; }
    .role-setting-identity h2 { margin: 1px 0 2px; color: var(--rs-ink); font-size: 16px; font-weight: 850; overflow-wrap: anywhere; }
    .role-setting-identity small { display: flex; align-items: center; gap: 4px; color: var(--rs-muted); font-size: 10px; }

    .role-setting-statuses { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 6px; }
    .role-setting-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-height: 26px;
        padding: 0 8px;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        color: #64748b;
        background: #f8fafc;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .role-setting-status.is-active { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
    .role-setting-status.is-all { color: #6d28d9; border-color: #ddd6fe; background: #f5f3ff; }
    .role-setting-status.is-off { color: #94a3b8; background: #f8fafc; }

    .role-setting-card-body {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        padding: 14px;
    }

    .role-setting-feature {
        min-width: 0;
        margin: 0;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
        transition: border-color .18s ease, background .18s ease;
    }

    .role-setting-feature:focus-within { border-color: #99f6e4; background: #fafffe; }

    .role-setting-feature-head { display: flex; align-items: center; gap: 10px; }
    .role-setting-feature-head > span:first-child {
        display: grid;
        place-items: center;
        flex: 0 0 36px;
        height: 36px;
        border-radius: 8px;
        font-size: 19px;
    }

    .role-setting-feature.is-data .role-setting-feature-head > span:first-child { color: #6d28d9; background: #ede9fe; }
    .role-setting-feature.is-pos .role-setting-feature-head > span:first-child { color: #be123c; background: #ffe4e6; }
    .role-setting-feature.is-approval .role-setting-feature-head > span:first-child { color: #047857; background: #d1fae5; }
    .role-setting-feature.is-notification .role-setting-feature-head > span:first-child { color: #b45309; background: #fef3c7; }
    .role-setting-feature-head > div { flex: 1; min-width: 0; }
    .role-setting-feature-head strong { display: block; color: #334155; font-size: 13px; }
    .role-setting-feature-head small { display: block; margin-top: 1px; color: #94a3b8; font-size: 10px; line-height: 1.35; }

    .role-setting-switch { position: relative; flex: 0 0 42px; height: 24px; margin: 0; cursor: pointer; }
    .role-setting-switch input[type="checkbox"] { position: absolute; opacity: 0; pointer-events: none; }
    .role-setting-switch > i {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background .18s ease;
    }

    .role-setting-switch > i::after {
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

    .role-setting-switch input:checked + i { background: var(--rs-primary); }
    .role-setting-switch input:checked + i::after { transform: translateX(18px); }
    .role-setting-switch input:focus-visible + i { outline: 3px solid rgba(20, 184, 166, .25); outline-offset: 2px; }

    .role-setting-segment-label {
        margin: 13px 0 6px;
        color: #64748b;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .role-setting-segment {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 4px;
        padding: 4px;
        border: 1px solid #dbe3ec;
        border-radius: 8px;
        background: #eef2f6;
    }

    .role-setting-segment input { position: absolute; opacity: 0; pointer-events: none; }
    .role-setting-segment label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 0;
        min-height: 34px;
        margin: 0;
        padding: 5px 8px;
        border: 1px solid transparent;
        border-radius: 6px;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        text-align: center;
        cursor: pointer;
        transition: .16s ease;
    }

    .role-setting-segment label:hover { color: var(--rs-primary); }
    .role-setting-segment input:checked + label {
        color: var(--rs-primary-dark);
        border-color: #cbdedb;
        background: #fff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .08);
    }
    .role-setting-segment input:focus-visible + label { outline: 3px solid rgba(20, 184, 166, .23); outline-offset: 1px; }

    .role-setting-feature-off {
        display: none;
        align-items: center;
        gap: 4px;
        margin-top: 8px;
        color: #94a3b8;
        font-size: 9px;
        line-height: 1.4;
    }

    .role-setting-feature.is-off { border-style: dashed; background: #fbfcfd; }
    .role-setting-feature.is-off .role-setting-feature-off { display: flex; }
    .role-setting-feature.is-off .role-setting-feature-head > span:first-child { filter: grayscale(1); opacity: .7; }

    .role-setting-empty,
    .role-setting-no-result {
        display: grid;
        justify-items: center;
        gap: 3px;
        padding: 42px 20px;
        color: var(--rs-muted);
        text-align: center;
    }

    .role-setting-empty i,
    .role-setting-no-result i { color: #94a3b8; font-size: 34px; }
    .role-setting-empty strong,
    .role-setting-no-result strong { color: #334155; }
    .role-setting-empty p { margin: 0; }
    .role-setting-no-result[hidden] { display: none; }

    .role-setting-save-dock {
        position: sticky;
        z-index: 20;
        bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        width: min(660px, calc(100% - 24px));
        margin: -2px auto 0;
        padding: 11px 12px 11px 16px;
        border: 1px solid #334155;
        border-radius: 10px;
        color: #fff;
        background: #172033;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .28);
    }

    .role-setting-save-dock[hidden] { display: none; }
    .role-setting-save-dock > div { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .role-setting-save-dock > div > span { color: #fbbf24; font-size: 21px; }
    .role-setting-save-dock strong { display: block; font-size: 12px; }
    .role-setting-save-dock small { display: block; margin-top: 1px; color: #cbd5e1; font-size: 9px; }
    .role-setting-save-dock .role-setting-save { min-height: 39px; padding: 0 14px; font-size: 11px; box-shadow: none; }

    @media (max-width: 1199.98px) {
        .role-setting-overview { grid-template-columns: 1fr; }
        .role-setting-overview-copy { display: none; }
        .role-setting-toolbar { align-items: flex-start; flex-direction: column; }
        .role-setting-toolbar-main { width: 100%; }
        .role-setting-filters { justify-content: flex-start; }
    }

    @media (max-width: 991.98px) {
        .role-setting-metrics { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .role-setting-metrics article:nth-child(4) { border-left: 0; border-top: 1px solid var(--rs-line); }
        .role-setting-metrics article:nth-child(5) { border-top: 1px solid var(--rs-line); }
        .role-setting-guide { align-items: flex-start; }
        .role-setting-guide-tags { display: none; }
    }

    @media (max-width: 767.98px) {
        .role-setting-hero { align-items: stretch; flex-direction: column; padding: 22px; }
        .role-setting-hero-action { align-items: stretch; }
        .role-setting-save-state { justify-content: center; }
        .role-setting-save { width: 100%; }
        .role-setting-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .role-setting-metrics article:nth-child(3) { border-top: 1px solid var(--rs-line); border-left: 0; }
        .role-setting-metrics article:nth-child(4) { border-left: 1px solid var(--rs-line); }
        .role-setting-metrics article:nth-child(5) { border-left: 0; }
        .role-setting-toolbar-main { align-items: stretch; flex-direction: column; }
        .role-setting-search { width: 100%; }
        .role-setting-card-header { align-items: flex-start; flex-direction: column; }
        .role-setting-statuses { justify-content: flex-start; }
        .role-setting-card-body { grid-template-columns: 1fr; }
        .role-setting-save-dock { align-items: stretch; flex-direction: column; }
        .role-setting-save-dock .role-setting-save { width: 100%; }
    }

    @media (max-width: 479.98px) {
        .role-setting-metrics { grid-template-columns: 1fr; }
        .role-setting-metrics article + article,
        .role-setting-metrics article:nth-child(4) { border-top: 1px solid var(--rs-line); border-left: 0; }
        .role-setting-filters { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); width: 100%; }
        .role-setting-filters button:first-child { grid-column: 1 / -1; }
        .role-setting-list { padding: 10px; }
        .role-setting-card-header { padding: 14px; }
        .role-setting-card-body { padding: 10px; }
        .role-setting-segment label { padding-inline: 5px; font-size: 9px; }
    }
</style>
