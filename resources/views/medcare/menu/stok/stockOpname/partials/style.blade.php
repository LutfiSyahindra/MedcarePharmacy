<style>
    :root {
        --opname-primary: #0f766e;
        --opname-primary-strong: #0b5f59;
        --opname-primary-soft: #ecfdf5;
        --opname-blue: #2563eb;
        --opname-amber: #d97706;
        --opname-red: #dc2626;
        --opname-indigo: #4f46e5;
        --opname-ink: #0f172a;
        --opname-body: #334155;
        --opname-muted: #64748b;
        --opname-line: #e5e7eb;
        --opname-soft-line: #edf1f5;
        --opname-surface: #fff;
        --opname-canvas: #f8fafc;
        --opname-radius: 8px;
        --opname-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .opname-page {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        color: var(--opname-ink);
    }

    .opname-toolbar,
    .opname-insight-main,
    .opname-insight-metrics,
    .opname-policy-card,
    .opname-flow,
    .opname-stat,
    .opname-filter-bar,
    .opname-table-card {
        border: 1px solid var(--opname-line);
        border-radius: var(--opname-radius);
        background: var(--opname-surface);
        box-shadow: var(--opname-shadow);
    }

    .opname-toolbar {
        position: relative;
        display: flex;
        min-height: 90px;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        overflow: hidden;
    }

    .opname-toolbar::before {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 3px;
        content: "";
        background: linear-gradient(90deg, var(--opname-primary), #22c55e 55%, #60a5fa);
    }

    .opname-title,
    .opname-modal-heading,
    .opname-detail-heading,
    .opname-card-title,
    .opname-filter-copy,
    .opname-flow-heading,
    .opname-form-section-title {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: .75rem;
    }

    .opname-title-icon,
    .opname-card-title > span,
    .opname-filter-copy > span,
    .opname-flow-heading > span,
    .opname-modal-heading > span,
    .opname-detail-icon,
    .opname-form-section-title > span {
        display: inline-flex;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        color: #fff;
        border-radius: var(--opname-radius);
        background: var(--opname-primary);
        font-size: 1.2rem;
    }

    .opname-title h4,
    .opname-card-title h5,
    .opname-modal-heading h5,
    .opname-detail-heading h5 {
        margin: 0;
        color: var(--opname-ink);
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: -.015em;
    }

    .opname-title p,
    .opname-card-title p,
    .opname-modal-heading p,
    .opname-detail-heading p {
        margin: .15rem 0 0;
        color: var(--opname-muted);
        font-size: .82rem;
    }

    .opname-eyebrow,
    .opname-modal-heading small,
    .opname-detail-heading > div > small {
        display: block;
        margin-bottom: .12rem;
        color: var(--opname-primary);
        font-size: .62rem;
        font-weight: 850;
        letter-spacing: .1em;
    }

    .opname-toolbar-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .opname-toolbar-actions .btn,
    .opname-filter-controls .btn {
        min-height: 38px;
        border-radius: var(--opname-radius);
        font-size: .8rem;
        font-weight: 700;
    }

    .opname-access-chip {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        gap: .55rem;
        padding: .42rem .7rem;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: var(--opname-radius);
        background: var(--opname-canvas);
        font-size: .75rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .opname-access-chip > i {
        color: var(--opname-primary);
        font-size: 1.15rem;
    }

    .opname-access-chip span,
    .opname-access-chip small {
        display: block;
        line-height: 1.15;
    }

    .opname-access-chip small {
        margin-bottom: .14rem;
        color: #94a3b8;
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .opname-access-chip.is-counter {
        color: #1d4ed8;
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    .opname-access-chip.is-counter > i { color: var(--opname-blue); }

    .opname-lock-banner {
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr) auto;
        align-items: center;
        gap: .85rem;
        padding: .85rem 1rem;
        color: #134e4a;
        border: 1px solid #99f6e4;
        border-left: 4px solid var(--opname-primary);
        border-radius: var(--opname-radius);
        background: linear-gradient(90deg, #ecfdf5, #f8fffd);
        box-shadow: 0 10px 24px rgba(15, 118, 110, .08);
    }

    .opname-lock-icon {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        color: var(--opname-primary);
        border-radius: var(--opname-radius);
        background: #d1fae5;
        font-size: 1.3rem;
    }

    .opname-lock-banner .opname-eyebrow { color: #0f766e; }
    .opname-lock-banner strong { display: block; font-size: .88rem; }
    .opname-lock-banner p { margin: .16rem 0 0; color: #47716d; font-size: .75rem; }

    .opname-live-pill,
    .opname-health-badge,
    .opname-visible-chip,
    .blind-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .38rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .opname-live-pill {
        padding: .42rem .62rem;
        color: #047857;
        border: 1px solid #a7f3d0;
        background: #fff;
    }

    .opname-live-pill i {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
        animation: opnameLive 1.8s infinite;
    }

    @keyframes opnameLive {
        50% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    }

    .opname-insight-strip {
        display: grid;
        grid-template-columns: minmax(320px, 1.3fr) minmax(300px, 1fr) minmax(260px, .9fr);
        gap: .85rem;
    }

    .opname-insight-main,
    .opname-policy-card {
        min-height: 116px;
        padding: 1rem;
    }

    .opname-insight-main {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        align-items: center;
        gap: .8rem;
    }

    .opname-insight-icon,
    .opname-policy-card > span {
        display: inline-flex;
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        align-items: center;
        justify-content: center;
        color: var(--opname-primary);
        border-radius: var(--opname-radius);
        background: var(--opname-primary-soft);
        font-size: 1.35rem;
    }

    .opname-insight-copy,
    .opname-policy-card > div { min-width: 0; }

    .opname-insight-copy small,
    .opname-policy-card small,
    .opname-insight-metric small,
    .opname-stat small {
        display: block;
        color: var(--opname-muted);
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .opname-insight-copy > strong,
    .opname-policy-card strong {
        display: block;
        margin-top: .16rem;
        overflow: hidden;
        color: var(--opname-ink);
        font-size: .92rem;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .opname-insight-copy > span {
        display: block;
        margin-top: .12rem;
        overflow: hidden;
        color: var(--opname-muted);
        font-size: .74rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .opname-health-track {
        height: 6px;
        margin-top: .65rem;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .opname-health-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: var(--opname-primary);
        transition: width .25s ease, background .25s ease;
    }

    .opname-health-badge {
        min-width: 64px;
        padding: .42rem .68rem;
        color: #475569;
        background: #f1f5f9;
    }

    .opname-health-badge.is-safe { color: #047857; background: #ecfdf5; }
    .opname-health-badge.is-info { color: #1d4ed8; background: #eff6ff; }
    .opname-health-badge.is-warning { color: #b45309; background: #fffbeb; }

    .opname-insight-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .5rem;
        min-height: 116px;
        padding: .65rem;
    }

    .opname-insight-metric {
        display: flex;
        min-width: 0;
        flex-direction: column;
        justify-content: center;
        padding: .7rem;
        border-radius: var(--opname-radius);
        background: var(--opname-canvas);
    }

    .opname-insight-metric strong {
        display: block;
        margin-top: .14rem;
        color: var(--opname-ink);
        font-size: 1.18rem;
        font-weight: 850;
        line-height: 1.1;
    }

    .opname-insight-metric span {
        display: block;
        margin-top: .18rem;
        overflow: hidden;
        color: var(--opname-muted);
        font-size: .64rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .opname-insight-metric.is-blue { background: #f5f9ff; }
    .opname-insight-metric.is-amber { background: #fffbeb; }
    .opname-insight-metric.is-green { background: #f0fdf4; }
    .opname-insight-metric.is-blue strong { color: #1d4ed8; }
    .opname-insight-metric.is-amber strong { color: #b45309; }
    .opname-insight-metric.is-green strong { color: #047857; }

    .opname-policy-card {
        display: flex;
        align-items: center;
        gap: .8rem;
        background: linear-gradient(135deg, #fff, #f8fafc);
    }

    .opname-policy-card p {
        margin: .22rem 0 0;
        color: var(--opname-muted);
        font-size: .69rem;
        line-height: 1.45;
    }

    .opname-flow {
        display: grid;
        grid-template-columns: minmax(210px, .7fr) minmax(600px, 2.3fr);
        align-items: center;
        gap: 1rem;
        padding: .85rem 1rem;
    }

    .opname-flow-heading strong,
    .opname-flow-heading small,
    .opname-flow-step strong,
    .opname-flow-step small { display: block; }
    .opname-flow-heading strong { font-size: .82rem; }
    .opname-flow-heading small { margin-top: .14rem; color: var(--opname-muted); font-size: .68rem; }
    .opname-flow-heading > span { color: var(--opname-primary); background: var(--opname-primary-soft); }

    .opname-flow-track {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr) auto) minmax(0, 1fr);
        align-items: center;
        gap: .4rem;
    }

    .opname-flow-track > i { color: #cbd5e1; font-size: 1rem; }
    .opname-flow-step { display: flex; min-width: 0; align-items: center; gap: .48rem; }

    .opname-flow-step > span {
        display: grid;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        place-items: center;
        color: var(--opname-primary);
        border: 1px solid #a7f3d0;
        border-radius: var(--opname-radius);
        background: var(--opname-primary-soft);
        font-size: .68rem;
        font-weight: 850;
    }

    .opname-flow-step strong,
    .opname-flow-step small { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .opname-flow-step strong { font-size: .7rem; }
    .opname-flow-step small { margin-top: .08rem; color: var(--opname-muted); font-size: .58rem; }

    .opname-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .85rem;
    }

    .opname-stat {
        position: relative;
        display: flex;
        min-width: 0;
        align-items: center;
        gap: .75rem;
        padding: .9rem 1rem;
        overflow: hidden;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .opname-stat::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 3px;
        content: "";
        background: #94a3b8;
    }

    .opname-stat:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(15, 23, 42, .08); }
    .opname-stat-icon { display: grid; flex: 0 0 40px; width: 40px; height: 40px; place-items: center; color: #475569; border-radius: var(--opname-radius); background: #f1f5f9; font-size: 1.15rem; }
    .opname-stat > div { min-width: 0; }
    .opname-stat strong { display: block; margin-top: .08rem; color: var(--opname-ink); font-size: 1.2rem; font-weight: 850; line-height: 1; }
    .opname-stat > div > span { display: block; margin-top: .22rem; overflow: hidden; color: var(--opname-muted); font-size: .68rem; text-overflow: ellipsis; white-space: nowrap; }
    .opname-stat.is-blue .opname-stat-icon { color: var(--opname-blue); background: #eff6ff; }
    .opname-stat.is-blue::after { background: var(--opname-blue); }
    .opname-stat.is-amber .opname-stat-icon { color: var(--opname-amber); background: #fffbeb; }
    .opname-stat.is-amber::after { background: var(--opname-amber); }
    .opname-stat.is-indigo .opname-stat-icon { color: var(--opname-indigo); background: #eef2ff; }
    .opname-stat.is-indigo::after { background: var(--opname-indigo); }
    .opname-stat.is-green .opname-stat-icon { color: #059669; background: #ecfdf5; }
    .opname-stat.is-green::after { background: #059669; }

    .opname-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        padding: .8rem 1rem;
    }

    .opname-filter-copy strong,
    .opname-filter-copy small { display: block; }
    .opname-filter-copy strong { font-size: .8rem; }
    .opname-filter-copy small { margin-top: .12rem; color: var(--opname-muted); font-size: .66rem; }
    .opname-filter-copy > span { color: #475569; background: #f1f5f9; }

    .opname-filter-controls {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .opname-search,
    .opname-detail-search {
        position: relative;
        min-width: 280px;
    }

    .opname-search > i,
    .opname-detail-search > i {
        position: absolute;
        top: 50%;
        left: .7rem;
        z-index: 1;
        color: #94a3b8;
        transform: translateY(-50%);
    }

    .opname-search input,
    .opname-detail-search input {
        width: 100%;
        height: 38px;
        padding: 0 2.1rem;
        color: var(--opname-ink);
        border: 1px solid #cbd5e1;
        border-radius: var(--opname-radius);
        background: #fff;
        outline: 0;
        font-size: .8rem;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .opname-search input:focus,
    .opname-detail-search input:focus {
        border-color: var(--opname-primary);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .1);
    }

    .opname-search button,
    .opname-detail-search button {
        position: absolute;
        top: 50%;
        right: .45rem;
        display: none;
        padding: 0;
        color: #94a3b8;
        border: 0;
        background: transparent;
        transform: translateY(-50%);
    }

    .opname-search.has-value button,
    .opname-detail-search.has-value button { display: inline-flex; }
    .opname-filter-controls .form-select { width: auto; min-width: 170px; height: 38px; border-color: #cbd5e1; border-radius: var(--opname-radius); font-size: .78rem; }
    .opname-refresh { width: 38px; padding: 0; }
    .opname-refresh.is-loading i { animation: opnameRotate .8s linear infinite; }
    @keyframes opnameRotate { to { transform: rotate(360deg); } }

    .opname-table-card { overflow: hidden; }
    .opname-card-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--opname-line); }
    .opname-card-title > span { color: #475569; background: #f1f5f9; }
    .opname-visible-chip { padding: .45rem .68rem; color: #0f766e; border: 1px solid #a7f3d0; background: #ecfdf5; }
    .opname-table-wrap { padding: 0 1rem 1rem; }
    #stockOpnameTable { width: 100% !important; margin: 0 !important; }
    #stockOpnameTable thead th { padding: .72rem; color: #64748b; border-top: 0; border-bottom: 1px solid var(--opname-line); background: #f8fafc; font-size: .64rem; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
    #stockOpnameTable tbody td { padding: .78rem .72rem; color: var(--opname-body); border-color: var(--opname-soft-line); font-size: .75rem; vertical-align: middle; }
    #stockOpnameTable tbody tr { transition: background .14s ease; }
    #stockOpnameTable tbody tr:hover { background: #fbfefd; }
    #stockOpnameTable tbody tr td:first-child { border-left: 3px solid transparent; }
    #stockOpnameTable tbody tr.is-counting td:first-child { border-left-color: var(--opname-primary); }
    #stockOpnameTable tbody tr.is-awaiting_verification td:first-child,
    #stockOpnameTable tbody tr.is-awaiting_approval td:first-child { border-left-color: var(--opname-amber); }
    #stockOpnameTable tbody tr.is-approved td:first-child { border-left-color: var(--opname-indigo); }
    #stockOpnameTable tbody tr.is-adjusted td:first-child { border-left-color: #10b981; }
    .opname-row-number { display: inline-grid; min-width: 28px; height: 28px; place-items: center; color: #64748b; border-radius: var(--opname-radius); background: #f1f5f9; font-size: .67rem; font-weight: 800; }
    .opname-doc { display: flex; min-width: 190px; align-items: center; gap: .55rem; }
    .opname-doc-icon { display: grid; flex: 0 0 34px; width: 34px; height: 34px; place-items: center; color: var(--opname-primary); border-radius: var(--opname-radius); background: var(--opname-primary-soft); font-size: 1rem; }
    .opname-doc strong, .opname-doc small, .opname-location strong, .opname-location small { display: block; }
    .opname-doc strong { color: var(--opname-ink); font-size: .76rem; }
    .opname-doc small, .opname-location small { margin-top: .14rem; color: var(--opname-muted); font-size: .65rem; }
    .opname-location strong { color: var(--opname-body); font-size: .74rem; }

    .opname-mode-badge,
    .opname-status-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .34rem .52rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .64rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .opname-mode-badge.is-record { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }
    .opname-mode-badge.is-freeze { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
    .opname-status-badge.is-draft { color: #475569; border-color: #e2e8f0; background: #f8fafc; }
    .opname-status-badge.is-counting { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
    .opname-status-badge.is-awaiting_verification,
    .opname-status-badge.is-awaiting_approval { color: #b45309; border-color: #fde68a; background: #fffbeb; }
    .opname-status-badge.is-approved { color: #4338ca; border-color: #c7d2fe; background: #eef2ff; }
    .opname-status-badge.is-adjusted { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
    .opname-progress { min-width: 145px; }
    .opname-progress-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .28rem; color: var(--opname-muted); font-size: .62rem; }
    .opname-progress-head strong { color: var(--opname-body); font-size: .66rem; }
    .opname-progress-line { height: 6px; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
    .opname-progress-line span { display: block; height: 100%; border-radius: inherit; background: var(--opname-primary); transition: width .22s ease; }
    .opname-progress small { display: block; margin-top: .25rem; color: var(--opname-muted); font-size: .61rem; }
    .opname-actions { display: flex; justify-content: flex-end; gap: .3rem; }
    .opname-actions .btn { display: grid; width: 32px; height: 32px; padding: 0; place-items: center; border-radius: var(--opname-radius); }

    .opname-page .dataTables_wrapper .dataTables_info,
    .opname-page .dataTables_wrapper .dataTables_paginate { color: var(--opname-muted); font-size: .75rem; }
    .opname-page .dataTables_wrapper .dataTables_paginate .paginate_button { border: 1px solid var(--opname-line) !important; border-radius: var(--opname-radius) !important; color: #475569 !important; background: #fff !important; }
    .opname-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .opname-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover { color: #fff !important; border-color: var(--opname-primary) !important; background: var(--opname-primary) !important; }

    .opname-modal {
        overflow: hidden;
        border: 0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .25);
    }

    .opname-modal .modal-header {
        padding: 1rem 1.15rem;
        color: var(--opname-ink);
        border-bottom: 1px solid var(--opname-line);
        background: #fff;
    }

    .opname-modal .modal-body { padding: 1rem; background: var(--opname-canvas); }
    #opnameFormModal .modal-dialog { max-height: calc(100vh - 1rem); max-height: calc(100dvh - 1rem); }
    #opnameFormModal .modal-header,
    #opnameFormModal .modal-footer { flex: 0 0 auto; }
    #opnameFormModal .modal-body {
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        -webkit-overflow-scrolling: touch;
    }
    .opname-modal .modal-footer { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .85rem 1rem; border-top: 1px solid var(--opname-line); background: #fff; }
    .opname-modal .modal-footer > div { display: flex; gap: .45rem; }
    .opname-modal .btn { border-radius: var(--opname-radius); font-size: .78rem; font-weight: 700; }
    .opname-modal .form-label { margin-bottom: .38rem; color: var(--opname-body); font-size: .72rem; font-weight: 800; }
    .opname-modal .form-label > span { color: var(--opname-red); }
    .opname-modal .form-control,
    .opname-modal .form-select { min-height: 42px; color: var(--opname-body); border-color: #cbd5e1; border-radius: var(--opname-radius); font-size: .8rem; }
    .opname-modal .form-control:focus,
    .opname-modal .form-select:focus { border-color: var(--opname-primary); box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .1); }
    .opname-modal-heading > span,
    .opname-detail-icon { flex-basis: 44px; width: 44px; height: 44px; }

    .opname-form-notice {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        margin-bottom: .8rem;
        padding: .72rem .8rem;
        color: #1e3a5f;
        border: 1px solid #bfdbfe;
        border-radius: var(--opname-radius);
        background: #eff6ff;
    }

    .opname-form-notice > i { margin-top: .04rem; color: var(--opname-blue); font-size: 1.1rem; }
    .opname-form-notice strong,
    .opname-form-notice span { display: block; }
    .opname-form-notice strong { font-size: .76rem; }
    .opname-form-notice span { margin-top: .14rem; color: #56708f; font-size: .67rem; }
    .opname-form-section { margin-top: .75rem; padding: .9rem; border: 1px solid var(--opname-line); border-radius: var(--opname-radius); background: #fff; }
    .opname-form-section:first-of-type { margin-top: 0; }
    .opname-form-section-title { margin-bottom: .8rem; padding-bottom: .7rem; border-bottom: 1px solid var(--opname-soft-line); }
    .opname-form-section-title > span { flex-basis: 34px; width: 34px; height: 34px; color: var(--opname-primary); background: var(--opname-primary-soft); font-size: 1rem; }
    .opname-form-section-title strong,
    .opname-form-section-title small { display: block; }
    .opname-form-section-title strong { font-size: .76rem; }
    .opname-form-section-title small { margin-top: .1rem; color: var(--opname-muted); font-size: .64rem; }
    .field-help { display: flex; align-items: center; gap: .3rem; margin-top: .38rem; color: var(--opname-muted); font-size: .64rem; }
    .field-help i { color: var(--opname-amber); }
    .opname-mode-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; }
    .opname-mode-option { position: relative; display: grid; grid-template-columns: 40px minmax(0, 1fr) 18px; align-items: center; gap: .65rem; margin: 0; padding: .78rem; border: 1px solid #d6dee9; border-radius: var(--opname-radius); cursor: pointer; transition: border-color .16s ease, background .16s ease, box-shadow .16s ease; }
    .opname-mode-option:hover { border-color: #94a3b8; }
    .opname-mode-option:has(input:checked) { border-color: var(--opname-primary); background: #f3fffb; box-shadow: 0 0 0 2px rgba(15, 118, 110, .08); }
    .opname-mode-option input { position: absolute; opacity: 0; pointer-events: none; }
    .mode-icon { display: grid; width: 40px; height: 40px; place-items: center; border-radius: var(--opname-radius); font-size: 1.12rem; }
    .mode-icon.is-blue { color: var(--opname-blue); background: #eff6ff; }
    .mode-icon.is-red { color: var(--opname-red); background: #fef2f2; }
    .opname-mode-option strong,
    .opname-mode-option small,
    .opname-mode-option em { display: block; }
    .opname-mode-option strong { color: var(--opname-ink); font-size: .72rem; }
    .opname-mode-option small { margin-top: .14rem; color: var(--opname-muted); font-size: .62rem; line-height: 1.4; }
    .opname-mode-option em { margin-top: .24rem; color: var(--opname-primary); font-size: .58rem; font-style: normal; font-weight: 800; }
    .mode-check { color: #cbd5e1; }
    .opname-mode-option:has(input:checked) .mode-check { color: var(--opname-primary); }
    .opname-footer-security { display: inline-flex; align-items: center; gap: .35rem; color: var(--opname-muted); font-size: .66rem; }

    .opname-detail-modal { height: min(94vh, 920px); }
    .opname-detail-modal .modal-body { min-height: 0; overflow-y: auto; }
    .opname-loading { display: flex; min-height: 320px; align-items: center; justify-content: center; flex-direction: column; gap: .45rem; color: var(--opname-muted); text-align: center; }
    .opname-loading .spinner-border { width: 2rem; height: 2rem; margin-bottom: .35rem; color: var(--opname-primary); }
    .opname-loading strong { color: var(--opname-body); font-size: .78rem; }
    .opname-loading small { font-size: .67rem; }
    .opname-detail-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .6rem; margin-bottom: .65rem; }
    .opname-summary-item { display: grid; grid-template-columns: 32px minmax(0, 1fr); min-width: 0; align-items: center; gap: .55rem; padding: .65rem; border: 1px solid var(--opname-line); border-radius: var(--opname-radius); background: #fff; }
    .opname-summary-icon { display: grid; width: 32px; height: 32px; place-items: center; color: #64748b; border-radius: var(--opname-radius); background: #f1f5f9; font-size: .9rem; }
    .opname-summary-item.is-blue .opname-summary-icon { color: #2563eb; background: #eff6ff; }
    .opname-summary-item.is-green .opname-summary-icon { color: #059669; background: #ecfdf5; }
    .opname-summary-item.is-amber .opname-summary-icon { color: #d97706; background: #fffbeb; }
    .opname-summary-item.is-indigo .opname-summary-icon { color: #4f46e5; background: #eef2ff; }
    .opname-summary-item > div { min-width: 0; }
    .opname-summary-item small,
    .opname-summary-item strong { display: block; }
    .opname-summary-item small { color: var(--opname-muted); font-size: .6rem; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; }
    .opname-summary-item strong { margin-top: .3rem; overflow: hidden; color: var(--opname-body); font-size: .7rem; text-overflow: ellipsis; white-space: nowrap; }
    .opname-summary-item .opname-status-badge,
    .opname-summary-item .opname-mode-badge { max-width: 100%; }

    .opname-detail-flow { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .4rem; margin-bottom: .7rem; }
    .opname-detail-flow-step { position: relative; padding: .55rem .4rem; color: #94a3b8; border: 1px solid var(--opname-line); border-radius: var(--opname-radius); background: #fff; text-align: center; }
    .opname-detail-flow-step:not(:last-child)::after { position: absolute; z-index: 2; top: 50%; right: -.28rem; width: .42rem; height: 1px; content: ""; background: #dbe2ea; }
    .opname-detail-flow-step i { display: block; font-size: .95rem; }
    .opname-detail-flow-step strong { display: block; margin-top: .12rem; font-size: .58rem; }
    .opname-detail-flow-step.is-done { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
    .opname-detail-flow-step.is-current { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; box-shadow: inset 0 -2px #3b82f6; }

    .opname-tabs { gap: .15rem; border-bottom: 1px solid #dbe2ea; }
    .opname-tabs .nav-link { padding: .65rem .78rem; color: var(--opname-muted); border: 0; border-bottom: 2px solid transparent; border-radius: 0; background: transparent; font-size: .68rem; font-weight: 800; }
    .opname-tabs .nav-link:hover { color: var(--opname-primary); }
    .opname-tabs .nav-link.active { color: var(--opname-primary); border-bottom-color: var(--opname-primary); background: transparent; }
    .opname-tabs .nav-link > span { display: inline-grid; min-width: 20px; height: 20px; margin-left: .25rem; padding: 0 .3rem; place-items: center; border-radius: 999px; background: #e2e8f0; font-size: .58rem; }
    .opname-tabs .nav-link.active > span { color: #fff; background: var(--opname-primary); }
    .opname-tab-content { min-height: 300px; overflow: hidden; border: 1px solid var(--opname-line); border-top: 0; border-radius: 0 0 var(--opname-radius) var(--opname-radius); background: #fff; }

    .opname-count-toolbar { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .75rem .8rem; border-bottom: 1px solid var(--opname-line); background: #fff; }
    .opname-count-copy { min-width: 190px; }
    .opname-count-copy strong,
    .opname-count-copy small { display: block; }
    .opname-count-copy strong { color: var(--opname-ink); font-size: .72rem; }
    .opname-count-copy small { margin-top: .12rem; color: var(--opname-muted); font-size: .61rem; }
    .opname-count-tools { display: flex; align-items: center; justify-content: flex-end; gap: .42rem; flex-wrap: wrap; }
    .opname-detail-search { min-width: 240px; }
    .opname-detail-search input { height: 34px; font-size: .72rem; }
    .opname-pending-toggle { position: relative; margin: 0; cursor: pointer; }
    .opname-pending-toggle input { position: absolute; opacity: 0; }
    .opname-pending-toggle span { display: inline-flex; min-height: 34px; align-items: center; gap: .32rem; padding: .35rem .55rem; color: #475569; border: 1px solid #cbd5e1; border-radius: var(--opname-radius); background: #fff; font-size: .65rem; font-weight: 800; white-space: nowrap; transition: .15s ease; }
    .opname-pending-toggle input:checked + span { color: #b45309; border-color: #fcd34d; background: #fffbeb; }
    #detailFillZero { min-height: 34px; }

    .opname-count-status { display: flex; align-items: center; gap: .45rem; padding: .5rem .8rem; border-bottom: 1px solid var(--opname-soft-line); background: var(--opname-canvas); }
    .opname-count-status > span { display: inline-flex; align-items: center; gap: .3rem; color: var(--opname-muted); font-size: .62rem; }
    .opname-count-status > span > i { color: #94a3b8; }
    .opname-count-status > span > strong { color: #475569; font-size: .62rem; }
    .blind-count-badge { margin-left: auto; padding: .35rem .55rem; color: #0f766e !important; border: 1px solid #a7f3d0; background: #ecfdf5; }
    .opname-count-table-wrap { max-height: 450px; overflow: auto; }
    .opname-count-table { min-width: 760px; margin: 0; }
    .opname-count-table.is-comparison { min-width: 1280px; }
    .opname-count-table thead { position: sticky; z-index: 3; top: 0; }
    .opname-count-table thead th { padding: .65rem .7rem; color: var(--opname-muted); border-color: var(--opname-line); background: #f8fafc; font-size: .59rem; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
    .opname-count-table tbody td { padding: .68rem .7rem; color: var(--opname-body); border-color: var(--opname-soft-line); font-size: .67rem; vertical-align: middle; }
    .opname-count-table tbody tr { transition: background .14s ease; }
    .opname-count-table tbody tr:hover { background: #fbfefd; }
    .opname-count-table tbody tr.is-complete { background: #fbfffd; }
    .opname-count-table tbody tr.is-autofilled { animation: opnameAutofill .7s ease; }
    @keyframes opnameAutofill { 0% { background: #d1fae5; } 100% { background: #fbfffd; } }
    .opname-count-table .item-main strong,
    .opname-count-table .item-main small { display: block; }
    .opname-count-table .item-main strong { color: var(--opname-ink); font-size: .69rem; }
    .opname-count-table .item-main small { margin-top: .13rem; color: var(--opname-muted); font-size: .59rem; }
    .opname-batch-empty { color: #b45309; }
    .physical-input-wrap { position: relative; width: 155px; }
    .physical-input-wrap input { height: 38px; padding-right: 50px; color: #0f5f57; border: 1px solid #86d8cd; border-radius: var(--opname-radius); background: #f5fffd; font-size: .8rem; font-weight: 850; text-align: right; }
    .physical-input-wrap input:focus { border-color: var(--opname-primary); background: #fff; box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .1); }
    .physical-input-wrap span { position: absolute; top: 50%; right: .6rem; max-width: 38px; overflow: hidden; color: var(--opname-primary); font-size: .57rem; font-weight: 800; text-overflow: ellipsis; transform: translateY(-50%); }
    .physical-value { color: var(--opname-primary); font-size: .8rem; }
    .physical-value small { color: var(--opname-muted); font-size: .57rem; }
    .count-meta { color: var(--opname-muted); font-size: .59rem; line-height: 1.5; white-space: nowrap; }
    .opname-no-result { padding: 2.5rem 1rem; color: var(--opname-muted); text-align: center; }
    .opname-no-result i { display: block; margin-bottom: .4rem; color: #cbd5e1; font-size: 1.8rem; }
    .opname-no-result strong,
    .opname-no-result span { display: block; }
    .opname-no-result strong { color: var(--opname-body); font-size: .74rem; }
    .opname-no-result span { margin-top: .16rem; font-size: .64rem; }

    .opname-empty { padding: 3rem 1rem; color: var(--opname-muted); text-align: center; }
    .opname-empty i { display: block; color: #cbd5e1; font-size: 2rem; }
    .opname-empty strong,
    .opname-empty small { display: block; }
    .opname-empty strong { margin-top: .45rem; color: var(--opname-body); font-size: .74rem; }
    .opname-empty small { margin-top: .16rem; font-size: .64rem; }
    .opname-movement-table { min-width: 1050px; margin: 0; }
    .opname-movement-table th,
    .opname-movement-table td { padding: .65rem; border-color: var(--opname-soft-line); font-size: .64rem; }
    .opname-movement-table th { color: var(--opname-muted); background: #f8fafc; font-size: .58rem; font-weight: 800; text-transform: uppercase; }
    .opname-movement-table td { vertical-align: middle; }
    .movement-time { color: var(--opname-muted); white-space: nowrap; }
    .movement-identity,
    .movement-batch { display: grid; min-width: 145px; gap: .16rem; }
    .movement-identity { min-width: 190px; }
    .movement-identity strong,
    .movement-batch strong { color: var(--opname-body); font-size: .67rem; font-weight: 800; }
    .movement-identity small,
    .movement-batch small { color: var(--opname-muted); font-size: .57rem; }
    .opname-audit-list { padding: .8rem; }
    .opname-audit-item { display: grid; grid-template-columns: 34px minmax(0, 1fr) auto; align-items: start; gap: .65rem; padding: .68rem 0; border-bottom: 1px solid var(--opname-soft-line); }
    .opname-audit-item:last-child { border-bottom: 0; }
    .opname-audit-icon { display: grid; width: 34px; height: 34px; place-items: center; color: var(--opname-primary); border-radius: var(--opname-radius); background: var(--opname-primary-soft); }
    .opname-audit-item strong,
    .opname-audit-item small { display: block; }
    .opname-audit-item strong { color: var(--opname-body); font-size: .67rem; }
    .opname-audit-item p { margin: .16rem 0 0; color: var(--opname-muted); font-size: .62rem; }
    .opname-audit-item small { color: #94a3b8; font-size: .57rem; white-space: nowrap; }
    .opname-detail-footer #detailFooterNote { display: flex; min-width: 0; align-items: center; gap: .4rem; color: var(--opname-muted); font-size: .65rem; }
    .opname-detail-footer #detailFooterNote i { color: var(--opname-primary); }
    .opname-detail-footer #detailActions { display: flex; justify-content: flex-end; gap: .4rem; flex-wrap: wrap; }
    .opname-count-table .difference-pill { display: inline-flex; min-width: 56px; justify-content: center; padding: .3rem .55rem; border-radius: 999px; font-weight: 850; }
    .opname-count-table .difference-pill.is-minus { color: #b91c1c; background: #fee2e2; }
    .opname-count-table .difference-pill.is-plus { color: #047857; background: #d1fae5; }
    .opname-count-table .difference-pill.is-match { color: #475569; background: #e2e8f0; }
    .opname-count-table tr.is-minus { box-shadow: inset 3px 0 #ef4444; }
    .opname-count-table tr.is-plus { box-shadow: inset 3px 0 #10b981; }
    .reason-input { min-width: 180px; font-size: .62rem; resize: vertical; }
    .difference-reason { display: block; min-width: 150px; color: var(--opname-body); line-height: 1.45; }
    .opname-match { display: inline-flex; align-items: center; gap: .28rem; color: #047857; font-weight: 750; white-space: nowrap; }
    .cost-cell,
    .reconcile-cell { display: grid; min-width: 135px; gap: .16rem; }
    .cost-cell strong,
    .reconcile-cell strong { color: var(--opname-body); font-size: .66rem; }
    .cost-cell small,
    .reconcile-cell small { color: var(--opname-muted); font-size: .57rem; line-height: 1.35; }
    .opname-posting-summary { padding: 1rem; }
    .posting-summary-hero { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem; color: #fff; border-radius: 16px; background: linear-gradient(135deg, #0f172a, #1e3a8a); }
    .posting-summary-hero > div { display: grid; gap: .18rem; }
    .posting-summary-hero small { font-size: .56rem; font-weight: 800; letter-spacing: .1em; opacity: .72; }
    .posting-summary-hero strong { font-size: 1rem; }
    .posting-summary-hero span { font-size: .62rem; opacity: .85; }
    .posting-summary-hero .summary-net { display: grid; min-width: 180px; gap: .15rem; padding: .65rem .8rem; text-align: right; border: 1px solid rgba(255,255,255,.18); border-radius: 12px; background: rgba(255,255,255,.1); }
    .posting-summary-hero .summary-net strong { font-size: 1.1rem; }
    .posting-summary-hero .summary-net.is-loss strong { color: #fecaca; }
    .posting-summary-hero .summary-net.is-surplus strong { color: #a7f3d0; }
    .posting-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .65rem; margin-top: .75rem; }
    .posting-summary-grid article { display: grid; gap: .22rem; padding: .8rem; border: 1px solid var(--opname-soft-line); border-radius: 12px; background: #fff; }
    .posting-summary-grid article small { color: var(--opname-muted); font-size: .57rem; font-weight: 750; text-transform: uppercase; }
    .posting-summary-grid article strong { color: var(--opname-body); font-size: .9rem; }
    .posting-summary-grid article span { color: var(--opname-muted); font-size: .58rem; }
    .posting-summary-grid article.is-danger { border-color: #fecaca; background: #fff7f7; }
    .posting-summary-grid article.is-danger strong { color: #b91c1c; }
    .posting-summary-grid article.is-success { border-color: #a7f3d0; background: #f0fdf4; }
    .posting-summary-grid article.is-success strong { color: #047857; }

    /* Control Document — premium command center */
    #opnameDetailModal .modal-dialog {
        width: calc(100% - 2rem);
        max-width: 1480px;
    }

    .opname-detail-modal {
        height: min(95vh, 960px);
        height: min(95dvh, 960px);
        overflow: hidden;
        border: 0;
        border-radius: 22px;
        background: #f4f7fb;
        box-shadow: 0 32px 90px rgba(2, 12, 27, .32), 0 8px 24px rgba(15, 23, 42, .12);
    }

    .opname-detail-modal .opname-control-header {
        position: relative;
        z-index: 2;
        min-height: 88px;
        padding: 1rem 1.35rem;
        overflow: hidden;
        color: #fff;
        border: 0;
        background:
            radial-gradient(circle at 72% -140%, rgba(45, 212, 191, .42), transparent 42%),
            linear-gradient(125deg, #071a2f 0%, #0b2943 48%, #0d4a50 100%);
    }

    .opname-detail-modal .opname-control-header::before,
    .opname-detail-modal .opname-control-header::after {
        position: absolute;
        content: "";
        pointer-events: none;
    }

    .opname-detail-modal .opname-control-header::before {
        right: 13%;
        bottom: -55px;
        width: 190px;
        height: 120px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
        transform: rotate(-12deg);
    }

    .opname-detail-modal .opname-control-header::after {
        right: 0;
        bottom: 0;
        left: 0;
        height: 3px;
        background: linear-gradient(90deg, #2dd4bf, #38bdf8 50%, rgba(255,255,255,.05));
    }

    .opname-control-header .opname-detail-heading,
    .opname-control-header .opname-header-actions { position: relative; z-index: 1; }
    .opname-control-header .opname-detail-heading { flex: 1; }

    .opname-control-header .opname-detail-icon {
        flex-basis: 48px;
        width: 48px;
        height: 48px;
        color: #99f6e4;
        border: 1px solid rgba(153, 246, 228, .24);
        border-radius: 14px;
        background: rgba(15, 118, 110, .25);
        box-shadow: inset 0 1px rgba(255,255,255,.08);
        font-size: 1.35rem;
    }

    .opname-control-header .opname-detail-heading > div > small {
        margin-bottom: .2rem;
        color: #5eead4;
        font-size: .61rem;
        letter-spacing: .16em;
    }

    .opname-control-header .opname-detail-heading h5 {
        overflow: hidden;
        color: #fff;
        font-size: 1.13rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .opname-control-header .opname-detail-heading p {
        margin-top: .22rem;
        color: #b9c9d8;
        font-size: .72rem;
    }

    .opname-header-actions { display: flex; align-items: center; gap: .65rem; }
    .opname-header-status {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        gap: .45rem;
        padding: .42rem .72rem;
        color: #dbeafe;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(8px);
        font-size: .66rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .opname-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #93c5fd;
        box-shadow: 0 0 0 4px rgba(147,197,253,.14);
    }

    .opname-header-status.is-counting .opname-status-dot { background: #38bdf8; box-shadow: 0 0 0 4px rgba(56,189,248,.14); animation: opnameHeaderPulse 1.8s ease-in-out infinite; }
    .opname-header-status.is-awaiting_verification .opname-status-dot,
    .opname-header-status.is-awaiting_approval .opname-status-dot { background: #fbbf24; box-shadow: 0 0 0 4px rgba(251,191,36,.14); }
    .opname-header-status.is-approved .opname-status-dot,
    .opname-header-status.is-adjusted .opname-status-dot { background: #34d399; box-shadow: 0 0 0 4px rgba(52,211,153,.14); }
    @keyframes opnameHeaderPulse { 50% { box-shadow: 0 0 0 7px rgba(56,189,248,0); } }

    .opname-modal-close {
        display: grid;
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        padding: 0;
        place-items: center;
        color: #dbeafe;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 11px;
        background: rgba(255,255,255,.07);
        font-size: 1.05rem;
        transition: color .15s ease, background .15s ease, transform .15s ease;
    }

    .opname-modal-close:hover { color: #fff; background: rgba(255,255,255,.16); transform: translateY(-1px); }

    .opname-detail-modal .modal-body {
        padding: 1rem;
        overflow-x: hidden;
        overflow-y: auto;
        background:
            radial-gradient(circle at 100% 0, rgba(45, 212, 191, .06), transparent 28%),
            #f4f7fb;
        scrollbar-gutter: stable;
        overscroll-behavior: contain;
    }

    .opname-control-overview {
        display: grid;
        grid-template-columns: minmax(390px, .88fr) minmax(0, 1.12fr);
        gap: .8rem;
    }

    .opname-stage-card,
    .opname-metric-panel,
    .opname-workflow-card,
    .opname-workbench {
        border: 1px solid #dce5ef;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .045);
    }

    .opname-stage-card {
        position: relative;
        padding: 1rem;
        overflow: hidden;
        color: #d8e5ef;
        border-color: #173c51;
        background:
            radial-gradient(circle at 105% -15%, rgba(45,212,191,.2), transparent 38%),
            linear-gradient(145deg, #0b2239, #103b4b);
        box-shadow: 0 14px 30px rgba(8, 34, 50, .16);
    }

    .opname-stage-card::after {
        position: absolute;
        right: -35px;
        bottom: -70px;
        width: 170px;
        height: 170px;
        content: "";
        border: 28px solid rgba(255,255,255,.025);
        border-radius: 50%;
        pointer-events: none;
    }

    .opname-stage-top { position: relative; z-index: 1; display: grid; grid-template-columns: 42px minmax(0, 1fr) 74px; align-items: center; gap: .72rem; }
    .opname-stage-icon { display: grid; width: 42px; height: 42px; place-items: center; color: #5eead4; border: 1px solid rgba(94,234,212,.18); border-radius: 12px; background: rgba(45,212,191,.1); font-size: 1.1rem; }
    .opname-stage-copy { min-width: 0; }
    .opname-stage-copy small { display: block; color: #5eead4; font-size: .55rem; font-weight: 850; letter-spacing: .13em; }
    .opname-stage-copy h6 { margin: .22rem 0 0; color: #fff; font-size: .92rem; font-weight: 850; }
    .opname-stage-copy p { margin: .22rem 0 0; color: #aabfce; font-size: .62rem; line-height: 1.45; }

    .opname-progress-ring {
        position: relative;
        display: grid;
        width: 70px;
        height: 70px;
        align-content: center;
        justify-items: center;
        border-radius: 50%;
        background: conic-gradient(#5eead4 var(--progress), rgba(255,255,255,.11) 0);
        isolation: isolate;
        transition: background .35s ease;
    }

    .opname-progress-ring::before { position: absolute; z-index: -1; inset: 6px; content: ""; border-radius: inherit; background: #102d3f; }
    .opname-progress-ring span { color: #fff; font-size: .86rem; font-weight: 850; line-height: 1; }
    .opname-progress-ring small { margin-top: .18rem; color: #8fb2c1; font-size: .48rem; font-weight: 750; text-transform: uppercase; }

    .opname-stage-progress { position: relative; z-index: 1; margin-top: .85rem; padding-top: .75rem; border-top: 1px solid rgba(255,255,255,.09); }
    .opname-stage-progress > div { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .42rem; }
    .opname-stage-progress span,
    .opname-stage-progress strong { font-size: .58rem; }
    .opname-stage-progress > div > span { color: #91abba; }
    .opname-stage-progress strong { color: #dff9f5; }
    .opname-stage-progress-track { display: block; height: 5px; overflow: hidden; border-radius: 999px; background: rgba(255,255,255,.1); }
    .opname-stage-progress-track i { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #2dd4bf, #7dd3fc); box-shadow: 0 0 12px rgba(45,212,191,.45); transition: width .3s ease; }

    .opname-document-facts { position: relative; z-index: 1; display: grid; grid-template-columns: 1fr 1fr; gap: .45rem; margin-top: .75rem; }
    .opname-document-fact { display: grid; grid-template-columns: 27px minmax(0, 1fr); align-items: center; gap: .45rem; min-width: 0; padding: .5rem; border: 1px solid rgba(255,255,255,.08); border-radius: 10px; background: rgba(255,255,255,.045); }
    .opname-document-fact > i { display: grid; width: 27px; height: 27px; place-items: center; color: #7dd3fc; border-radius: 8px; background: rgba(125,211,252,.09); font-size: .85rem; }
    .opname-document-fact small,
    .opname-document-fact strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .opname-document-fact small { color: #809dab; font-size: .49rem; font-weight: 750; text-transform: uppercase; }
    .opname-document-fact strong { margin-top: .12rem; color: #e8f4f7; font-size: .6rem; }

    .opname-document-note { position: relative; z-index: 1; display: flex; align-items: flex-start; gap: .5rem; margin-top: .55rem; padding: .58rem .65rem; border: 1px solid rgba(251,191,36,.17); border-radius: 10px; background: rgba(251,191,36,.07); }
    .opname-document-note > i { margin-top: .05rem; color: #fbbf24; }
    .opname-document-note small { display: block; color: #e9c76b; font-size: .49rem; font-weight: 800; letter-spacing: .08em; }
    .opname-document-note p { margin: .15rem 0 0; color: #d2dce2; font-size: .58rem; line-height: 1.45; }

    .opname-metric-panel { padding: .85rem; }
    .opname-panel-heading { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .7rem; }
    .opname-panel-heading > div > small,
    .opname-panel-heading > div > strong { display: block; }
    .opname-panel-heading > div > small { color: var(--opname-primary); font-size: .53rem; font-weight: 850; letter-spacing: .12em; }
    .opname-panel-heading > div > strong { margin-top: .15rem; color: var(--opname-ink); font-size: .75rem; }
    .opname-panel-heading > span { display: inline-flex; align-items: center; gap: .28rem; padding: .32rem .5rem; color: #517085; border: 1px solid #dce5ef; border-radius: 999px; background: #f8fafc; font-size: .54rem; font-weight: 750; white-space: nowrap; }
    .opname-panel-heading > span i { color: var(--opname-primary); }

    .opname-detail-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .55rem; margin: 0; }
    .opname-summary-item { position: relative; grid-template-columns: 38px minmax(0, 1fr); min-height: 74px; gap: .65rem; padding: .68rem; overflow: hidden; border-color: #e3eaf2; border-radius: 12px; background: linear-gradient(145deg, #fff, #fafcff); }
    .opname-summary-item::after { position: absolute; right: -16px; bottom: -24px; width: 55px; height: 55px; content: ""; border: 10px solid rgba(100,116,139,.035); border-radius: 50%; }
    .opname-summary-icon { width: 38px; height: 38px; border-radius: 11px; font-size: 1rem; }
    .opname-summary-item small { font-size: .51rem; letter-spacing: .075em; }
    .opname-summary-item strong { margin-top: .25rem; color: #17324a; font-size: .76rem; }
    .opname-summary-item em { display: block; margin-top: .13rem; overflow: hidden; color: #8292a5; font-size: .52rem; font-style: normal; text-overflow: ellipsis; white-space: nowrap; }
    .opname-summary-item.is-red .opname-summary-icon { color: #dc2626; background: #fef2f2; }

    .opname-workflow-card { margin-top: .8rem; padding: .75rem .85rem .85rem; }
    .opname-workflow-card .opname-panel-heading { margin-bottom: .55rem; }
    .opname-detail-flow { grid-template-columns: repeat(6, minmax(112px, 1fr)); gap: .5rem; margin: 0; }
    .opname-detail-flow-step { display: grid; grid-template-columns: 30px minmax(0, 1fr); align-items: center; gap: .45rem; min-height: 52px; padding: .48rem; color: #8493a5; border-color: #e1e8f0; border-radius: 11px; background: #fbfcfe; text-align: left; transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease; }
    .opname-detail-flow-step:not(:last-child)::after { top: 50%; right: -.34rem; width: .2rem; background: #cbd5e1; }
    .opname-detail-flow-step:hover { border-color: #b9c8d7; transform: translateY(-1px); box-shadow: 0 5px 12px rgba(15,23,42,.05); }
    .opname-flow-marker { display: grid; width: 30px; height: 30px; place-items: center; border: 1px solid #dbe3ec; border-radius: 9px; background: #fff; font-size: .85rem; }
    .opname-detail-flow-step small,
    .opname-detail-flow-step strong,
    .opname-detail-flow-step em { display: block; }
    .opname-detail-flow-step small { color: #a3afbd; font-size: .45rem; font-weight: 850; letter-spacing: .08em; }
    .opname-detail-flow-step strong { margin-top: .08rem; color: #5f7185; font-size: .59rem; }
    .opname-detail-flow-step em { margin-top: .08rem; overflow: hidden; font-size: .48rem; font-style: normal; text-overflow: ellipsis; white-space: nowrap; }
    .opname-detail-flow-step.is-done { color: #047857; border-color: #b7ead7; background: #f4fdf9; }
    .opname-detail-flow-step.is-done .opname-flow-marker { color: #059669; border-color: #a7f3d0; background: #ecfdf5; }
    .opname-detail-flow-step.is-done strong { color: #047857; }
    .opname-detail-flow-step.is-current { color: #0369a1; border-color: #7dd3fc; background: linear-gradient(145deg, #effaff, #f8fdff); box-shadow: inset 0 -2px #0ea5e9, 0 6px 14px rgba(14,165,233,.09); }
    .opname-detail-flow-step.is-current .opname-flow-marker { color: #0284c7; border-color: #bae6fd; background: #e0f2fe; }
    .opname-detail-flow-step.is-current strong { color: #075985; }

    .opname-workbench { margin-top: .8rem; overflow: hidden; }
    .opname-workbench-head { display: flex; min-height: 62px; align-items: center; justify-content: space-between; gap: 1rem; padding: .65rem .8rem; border-bottom: 1px solid #dde5ee; background: linear-gradient(180deg, #fff, #fbfcfe); }
    .opname-workbench-head > div > small,
    .opname-workbench-head > div > strong { display: block; }
    .opname-workbench-head > div > small { color: var(--opname-primary); font-size: .5rem; font-weight: 850; letter-spacing: .12em; }
    .opname-workbench-head > div > strong { margin-top: .14rem; color: var(--opname-ink); font-size: .72rem; }
    .opname-workbench .opname-tabs { gap: .28rem; margin: 0; padding: .25rem; border: 1px solid #dce4ed; border-radius: 12px; background: #f1f5f9; }
    .opname-workbench .opname-tabs .nav-link { display: inline-flex; min-height: 34px; align-items: center; gap: .32rem; padding: .4rem .62rem; color: #64748b; border: 0; border-radius: 9px; font-size: .61rem; box-shadow: none; }
    .opname-workbench .opname-tabs .nav-link:hover { color: #0f766e; background: rgba(255,255,255,.65); }
    .opname-workbench .opname-tabs .nav-link.active { color: #0f5f59; background: #fff; box-shadow: 0 3px 9px rgba(15,23,42,.08); }
    .opname-workbench .opname-tabs .nav-link > .opname-tab-label { display: inline; min-width: 0; height: auto; margin: 0; padding: 0; color: inherit; border-radius: 0; background: transparent; font-size: inherit; }
    .opname-workbench .opname-tabs .nav-link > b { display: inline-grid; min-width: 18px; height: 18px; padding: 0 .25rem; place-items: center; color: #64748b; border-radius: 999px; background: #e2e8f0; font-size: .5rem; }
    .opname-workbench .opname-tabs .nav-link.active > b { color: #fff; background: var(--opname-primary); }
    .opname-workbench .opname-tab-content { min-height: 310px; border: 0; border-radius: 0; }

    .opname-workbench .opname-count-toolbar { padding: .72rem .8rem; background: #fff; }
    .opname-count-copy { display: flex; align-items: center; gap: .55rem; min-width: 230px; }
    .opname-count-copy > span { display: grid; flex: 0 0 34px; width: 34px; height: 34px; place-items: center; color: var(--opname-primary); border-radius: 10px; background: var(--opname-primary-soft); }
    .opname-count-copy strong { font-size: .68rem; }
    .opname-count-copy small { font-size: .57rem; }
    .opname-detail-search { min-width: 260px; }
    .opname-detail-search input { border-radius: 10px; background: #f8fafc; }

    .opname-row-filters { display: inline-flex; gap: 2px; padding: 3px; border: 1px solid #dbe3ec; border-radius: 10px; background: #f8fafc; }
    .opname-row-filter { display: inline-flex; min-height: 28px; align-items: center; gap: .22rem; padding: .25rem .48rem; color: #64748b; border: 0; border-radius: 7px; background: transparent; font-size: .56rem; font-weight: 800; white-space: nowrap; transition: color .15s ease, background .15s ease, box-shadow .15s ease; }
    .opname-row-filter:hover { color: #0f766e; }
    .opname-row-filter.is-active { color: #0f5f59; background: #fff; box-shadow: 0 2px 7px rgba(15,23,42,.08); }
    .opname-row-filter b { display: inline-grid; min-width: 17px; height: 17px; padding: 0 .22rem; place-items: center; color: #718096; border-radius: 999px; background: #e8edf3; font-size: .47rem; }
    .opname-row-filter.is-active b { color: #fff; background: var(--opname-primary); }
    #detailDifferenceFilter.is-active { color: #b45309; }
    #detailDifferenceFilter.is-active b { background: #d97706; }
    #detailFillZero { border-radius: 9px; }

    .opname-workbench .opname-count-status { padding: .5rem .8rem; background: #f8fafc; }
    .opname-workbench .blind-count-badge { border-radius: 999px; }
    .opname-workbench .blind-count-badge.is-visible { color: #0369a1 !important; border-color: #bae6fd; background: #f0f9ff; }
    .opname-workbench .blind-count-badge.is-protected { color: #0f766e !important; border-color: #a7f3d0; background: #ecfdf5; }
    .opname-workbench .opname-count-table-wrap { max-height: 465px; scrollbar-color: #b8c6d4 #f1f5f9; scrollbar-width: thin; }
    .opname-workbench .opname-count-table thead th { padding-block: .7rem; background: #f4f7fa; box-shadow: inset 0 -1px #dfe7ef; }
    .opname-workbench .opname-count-table tbody td { padding-block: .72rem; }
    .opname-workbench .opname-count-table tbody tr:hover { background: #f5fbfa; }
    .opname-workbench .opname-count-table tbody tr.is-dirty { background: #fffbeb; box-shadow: inset 3px 0 #f59e0b; }
    .opname-workbench .opname-count-table tbody tr.is-dirty .count-physical,
    .opname-workbench .opname-count-table tbody tr.is-dirty .reason-input { border-color: #fbbf24; background: #fffef5; }

    .opname-audit-list { position: relative; padding: .9rem 1rem; }
    .opname-audit-item { position: relative; grid-template-columns: 38px minmax(0, 1fr) auto; padding: .72rem 0; border: 0; }
    .opname-audit-item:not(:last-child)::before { position: absolute; top: 43px; bottom: -5px; left: 18px; width: 1px; content: ""; background: #dbe8e5; }
    .opname-audit-icon { width: 38px; height: 38px; border: 1px solid #ccebe5; border-radius: 11px; }
    .opname-audit-item strong { font-size: .7rem; }
    .opname-audit-item p { line-height: 1.5; }
    .opname-audit-item > small { padding: .25rem .4rem; border-radius: 7px; background: #f8fafc; }

    .opname-movement-table thead { position: sticky; z-index: 2; top: 0; }
    .opname-movement-table th { padding-block: .72rem; background: #f4f7fa; }
    .opname-movement-table tbody tr:hover { background: #f8fcfb; }

    .opname-detail-modal .opname-detail-footer {
        min-height: 72px;
        padding: .72rem 1rem;
        border-top: 1px solid #dce5ef;
        background: rgba(255,255,255,.96);
        box-shadow: 0 -8px 24px rgba(15,23,42,.04);
        backdrop-filter: blur(10px);
    }

    .opname-detail-footer #detailFooterNote { gap: .55rem; }
    .opname-footer-note-icon { display: grid; flex: 0 0 34px; width: 34px; height: 34px; place-items: center; color: #b45309; border-radius: 10px; background: #fffbeb; }
    .opname-detail-footer #detailFooterNote > div > small,
    .opname-detail-footer #detailFooterNote > div > span { display: block; }
    .opname-detail-footer #detailFooterNote > div > small { color: #8b9aaa; font-size: .48rem; font-weight: 850; letter-spacing: .1em; }
    .opname-detail-footer #detailFooterNote > div > span { margin-top: .13rem; color: #526579; font-size: .62rem; line-height: 1.35; }
    .opname-detail-footer #detailFooterNote.is-warning > div > small,
    .opname-detail-footer #detailFooterNote.is-warning > div > span { color: #9a5b07; }
    .opname-detail-footer #detailFooterNote.is-warning .opname-footer-note-icon { color: #dc2626; background: #fef2f2; }
    .opname-detail-footer #detailActions .btn { min-height: 38px; padding-inline: .8rem; border-radius: 10px; box-shadow: 0 2px 5px rgba(15,23,42,.04); }
    .opname-detail-footer #detailActions .btn-primary,
    .opname-detail-footer #detailActions .btn-success { box-shadow: 0 5px 12px rgba(15,118,110,.18); }

    .opname-loading { min-height: 480px; }
    .opname-loading-orbit { position: relative; display: grid; width: 64px; height: 64px; margin-bottom: .5rem; place-items: center; color: var(--opname-primary); border: 1px solid #cce7e2; border-radius: 18px; background: #fff; box-shadow: 0 10px 24px rgba(15,118,110,.1); font-size: 1.45rem; }
    .opname-loading-orbit::after { position: absolute; inset: -7px; content: ""; border: 2px solid transparent; border-top-color: #2dd4bf; border-radius: 22px; animation: opnameLoadingOrbit 1.2s linear infinite; }
    .opname-loading-line { width: 170px; height: 4px; margin-top: .7rem; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
    .opname-loading-line i { display: block; width: 45%; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #14b8a6, #38bdf8); animation: opnameLoadingLine 1.15s ease-in-out infinite; }
    @keyframes opnameLoadingOrbit { to { transform: rotate(360deg); } }
    @keyframes opnameLoadingLine { 0% { transform: translateX(-120%); } 100% { transform: translateX(340%); } }

    @media (max-width: 1200px) {
        .opname-insight-strip { grid-template-columns: 1fr 1fr; }
        .opname-policy-card { grid-column: 1 / -1; min-height: auto; }
        .opname-flow { grid-template-columns: 1fr; }
        .opname-stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .opname-filter-bar { align-items: flex-start; flex-direction: column; }
        .opname-filter-controls { width: 100%; justify-content: flex-start; }
        .opname-search { flex: 1; }
    }

    @media (max-width: 991px) {
        .opname-toolbar { align-items: flex-start; flex-direction: column; }
        .opname-toolbar-actions { width: 100%; justify-content: flex-start; }
        .opname-flow-track { overflow-x: auto; padding-bottom: .2rem; }
        .opname-flow-step { min-width: 105px; }
        .opname-detail-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .opname-count-toolbar { align-items: flex-start; flex-direction: column; }
        .opname-count-tools { width: 100%; justify-content: flex-start; }
        .opname-detail-search { flex: 1; }
        .posting-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 767px) {
        .opname-title-icon { flex-basis: 40px; width: 40px; height: 40px; }
        .opname-title p { line-height: 1.45; }
        .opname-toolbar-actions .opname-access-chip { width: 100%; }
        .opname-toolbar-actions .btn { flex: 1; justify-content: center; }
        .opname-lock-banner { grid-template-columns: 42px minmax(0, 1fr); }
        .opname-live-pill { display: none; }
        .opname-insight-strip { grid-template-columns: 1fr; }
        .opname-policy-card { grid-column: auto; }
        .opname-insight-main { grid-template-columns: 42px minmax(0, 1fr); }
        .opname-insight-main .opname-health-badge { grid-column: 2; justify-self: start; }
        .opname-insight-icon { width: 42px; height: 42px; flex-basis: 42px; }
        .opname-stat-grid { grid-template-columns: 1fr 1fr; }
        .opname-flow-heading { align-items: flex-start; }
        .opname-filter-copy { display: none; }
        .opname-filter-controls { display: grid; grid-template-columns: 1fr 1fr 38px; }
        .opname-search { grid-column: 1 / -1; min-width: 0; }
        .opname-filter-controls .form-select { width: 100%; min-width: 0; }
        .opname-card-header { align-items: flex-start; flex-direction: column; }
        .opname-visible-chip { align-self: stretch; justify-content: flex-start; }
        .opname-table-wrap { padding-inline: .5rem; }
        .opname-mode-grid { grid-template-columns: 1fr; }
        .opname-footer-security { display: none; }
        .opname-modal .modal-footer { align-items: stretch; flex-direction: column; }
        .opname-modal .modal-footer > div { width: 100%; }
        .opname-modal .modal-footer .btn { flex: 1; }
        .opname-detail-summary { grid-template-columns: 1fr 1fr; }
        .opname-detail-flow { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .opname-detail-flow-step::after { display: none; }
        .opname-tabs { overflow-x: auto; flex-wrap: nowrap; }
        .opname-tabs .nav-link { white-space: nowrap; }
        .opname-count-tools { display: grid; grid-template-columns: 1fr auto; }
        .opname-detail-search { grid-column: 1 / -1; min-width: 0; }
        .opname-count-status { flex-wrap: wrap; }
        .blind-count-badge { width: 100%; margin-left: 0; justify-content: flex-start; }
        .opname-detail-footer { align-items: stretch !important; flex-direction: column; }
        .opname-detail-footer #detailActions { width: 100%; }
        .opname-detail-footer #detailActions .btn { flex: 1; }
        .posting-summary-hero { align-items: stretch; flex-direction: column; }
        .posting-summary-hero .summary-net { min-width: 0; text-align: left; }
        .posting-summary-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 480px) {
        .opname-stat-grid { grid-template-columns: 1fr; }
        .opname-insight-metrics { grid-template-columns: 1fr; }
        .opname-insight-metric { min-height: 76px; }
        .opname-filter-controls { grid-template-columns: 1fr 38px; }
        .opname-filter-controls .form-select { grid-column: 1 / -1; }
        .opname-refresh { grid-column: 2; grid-row: 4; }
        .opname-detail-summary { grid-template-columns: 1fr; }
        .opname-count-tools { grid-template-columns: 1fr; }
        .opname-pending-toggle span,
        #detailFillZero { width: 100%; justify-content: center; }
    }

    @media (max-width: 1199.98px) {
        #opnameDetailModal .modal-dialog { width: 100%; max-width: none; height: 100%; margin: 0; }
        .opname-detail-modal { width: 100%; height: 100vh; height: 100dvh; max-height: none; border-radius: 0; }
    }

    @media (max-width: 991px) {
        .opname-control-overview { grid-template-columns: 1fr; }
        .opname-detail-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .opname-summary-item { grid-template-columns: 34px minmax(0, 1fr); min-height: 68px; }
        .opname-summary-icon { width: 34px; height: 34px; }
        .opname-detail-flow { overflow-x: auto; grid-template-columns: repeat(6, minmax(125px, 1fr)); padding-bottom: .2rem; }
        .opname-workbench-head { align-items: flex-start; flex-direction: column; }
        .opname-workbench .opname-tabs { width: 100%; overflow-x: auto; flex-wrap: nowrap; }
        .opname-workbench .opname-tabs .nav-item { flex: 1 0 auto; }
        .opname-workbench .opname-tabs .nav-link { width: 100%; justify-content: center; }
    }

    @media (max-width: 767px) {
        .opname-detail-modal .opname-control-header { min-height: 76px; padding: .78rem; }
        .opname-control-header .opname-detail-icon { flex-basis: 40px; width: 40px; height: 40px; border-radius: 12px; }
        .opname-control-header .opname-detail-heading { gap: .55rem; }
        .opname-control-header .opname-detail-heading h5 { max-width: calc(100vw - 155px); font-size: .92rem; }
        .opname-control-header .opname-detail-heading p { max-width: calc(100vw - 155px); overflow: hidden; font-size: .61rem; text-overflow: ellipsis; white-space: nowrap; }
        .opname-control-header .opname-detail-heading > div > small { font-size: .49rem; letter-spacing: .1em; }
        .opname-header-status { width: 34px; min-height: 34px; justify-content: center; padding: 0; overflow: hidden; color: transparent; font-size: 0; }
        .opname-header-status .opname-status-dot { flex: 0 0 7px; }
        .opname-modal-close { flex-basis: 34px; width: 34px; height: 34px; }
        .opname-detail-modal .modal-body { padding: .65rem; }
        .opname-control-overview,
        .opname-workflow-card,
        .opname-workbench { margin-top: .6rem; }
        .opname-control-overview { gap: .6rem; margin-top: 0; }
        .opname-stage-card,
        .opname-metric-panel,
        .opname-workflow-card,
        .opname-workbench { border-radius: 13px; }
        .opname-stage-card { padding: .8rem; }
        .opname-stage-top { grid-template-columns: 36px minmax(0, 1fr) 62px; gap: .55rem; }
        .opname-stage-icon { width: 36px; height: 36px; border-radius: 10px; }
        .opname-progress-ring { width: 60px; height: 60px; }
        .opname-progress-ring span { font-size: .75rem; }
        .opname-document-facts { grid-template-columns: 1fr 1fr; }
        .opname-detail-summary { grid-template-columns: 1fr 1fr; }
        .opname-summary-item { min-height: 66px; padding: .58rem; }
        .opname-workflow-card { padding: .65rem; }
        .opname-workflow-card .opname-panel-heading > span { display: none; }
        .opname-detail-flow { display: grid; overflow: visible; grid-template-columns: 1fr 1fr; gap: .4rem; }
        .opname-detail-flow-step { min-height: 48px; }
        .opname-detail-flow-step::after { display: none; }
        .opname-workbench-head { padding: .6rem; }
        .opname-workbench-head > div { display: none; }
        .opname-workbench .opname-tabs { padding: .2rem; }
        .opname-workbench .opname-tabs .nav-link { min-height: 32px; padding: .35rem .48rem; }
        .opname-workbench .opname-count-toolbar { padding: .65rem; }
        .opname-count-copy { min-width: 0; }
        .opname-count-tools { display: grid; grid-template-columns: 1fr auto; }
        .opname-detail-search { grid-column: 1 / -1; min-width: 0; }
        .opname-row-filters { min-width: 0; overflow-x: auto; }
        .opname-row-filter { flex: 0 0 auto; }
        .opname-workbench .opname-count-status { padding: .45rem .6rem; }
        .opname-detail-modal .opname-detail-footer { min-height: 0; padding: .58rem .65rem; }
        .opname-detail-footer #detailFooterNote { display: none; }
        .opname-detail-footer #detailActions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .opname-detail-footer #detailActions .btn { width: 100%; min-height: 40px; justify-content: center; }
    }

    @media (max-width: 480px) {
        .opname-control-header .opname-detail-heading h5,
        .opname-control-header .opname-detail-heading p { max-width: calc(100vw - 140px); }
        .opname-stage-top { grid-template-columns: 34px minmax(0, 1fr) 58px; }
        .opname-stage-icon { width: 34px; height: 34px; }
        .opname-progress-ring { width: 56px; height: 56px; }
        .opname-document-facts { grid-template-columns: 1fr; }
        .opname-detail-summary { grid-template-columns: 1fr; }
        .opname-summary-item { min-height: 62px; }
        .opname-workbench .opname-tabs .nav-link { padding-inline: .42rem; }
        .opname-workbench .opname-tabs .nav-link .opname-tab-label { display: none; }
        .opname-workbench .opname-tabs .nav-link i { font-size: .95rem; }
        .opname-count-tools { grid-template-columns: 1fr; }
        .opname-row-filters,
        #detailFillZero { width: 100%; }
        .opname-row-filter { flex: 1; justify-content: center; }
        .opname-detail-footer #detailActions { grid-template-columns: 1fr; }
    }

    @media (prefers-reduced-motion: reduce) {
        .opname-live-pill i,
        .opname-refresh.is-loading i,
        .opname-count-table tbody tr.is-autofilled,
        .opname-header-status.is-counting .opname-status-dot,
        .opname-loading-orbit::after,
        .opname-loading-line i { animation: none; }
        .opname-stat,
        .opname-health-track span,
        .opname-progress-line span { transition: none; }
    }
</style>
