<style>
    .notification-page {
        --notif-primary: #2563eb;
        --notif-primary-dark: #1d4ed8;
        --notif-teal: #0f766e;
        --notif-ink: #0f172a;
        --notif-copy: #334155;
        --notif-muted: #64748b;
        --notif-line: #e2e8f0;
        --notif-soft: #f8fafc;
        --notif-surface: #ffffff;
        --notif-radius: 16px;
        --notif-shadow: 0 14px 38px rgba(15, 23, 42, .07);
        --notif-shadow-hover: 0 20px 46px rgba(15, 23, 42, .11);
        display: grid;
        gap: 20px;
        width: 100%;
        max-width: 1680px;
        margin: 0 auto;
        color: var(--notif-copy);
    }

    .notification-page *,
    .notification-page *::before,
    .notification-page *::after { box-sizing: border-box; }

    .notification-breadcrumb { margin-bottom: -4px; }
    .notification-breadcrumb .breadcrumb { margin-bottom: 0; }
    .notification-breadcrumb a {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: var(--notif-muted);
        text-decoration: none;
    }
    .notification-breadcrumb a:hover { color: var(--notif-primary); }

    .notification-command {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 190px;
        padding: 30px 32px;
        overflow: hidden;
        color: #fff;
        background:
            radial-gradient(circle at 78% 15%, rgba(255, 255, 255, .17) 0, rgba(255, 255, 255, 0) 28%),
            linear-gradient(125deg, #0f172a 0%, #123a72 52%, #0f766e 115%);
        border: 1px solid rgba(148, 163, 184, .2);
        border-radius: 20px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
        isolation: isolate;
    }

    .notification-command::before,
    .notification-command::after {
        position: absolute;
        z-index: -1;
        content: "";
        pointer-events: none;
    }

    .notification-command::before {
        top: -115px;
        right: 8%;
        width: 250px;
        height: 250px;
        border: 1px solid rgba(255, 255, 255, .13);
        border-radius: 50%;
        box-shadow: 0 0 0 38px rgba(255, 255, 255, .035), 0 0 0 76px rgba(255, 255, 255, .025);
    }

    .notification-command::after {
        right: -54px;
        bottom: -110px;
        width: 240px;
        height: 240px;
        background: rgba(45, 212, 191, .14);
        border-radius: 44% 56% 64% 36%;
        transform: rotate(18deg);
        filter: blur(1px);
    }

    .notification-command-copy {
        position: relative;
        z-index: 1;
        max-width: 760px;
    }

    .notification-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 28px;
        padding: 0 11px;
        margin-bottom: 13px;
        color: #dbeafe;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        backdrop-filter: blur(8px);
    }

    .notification-live-dot {
        position: relative;
        width: 7px;
        height: 7px;
        background: #5eead4;
        border-radius: 50%;
        box-shadow: 0 0 0 4px rgba(94, 234, 212, .14);
    }

    .notification-command h1 {
        margin: 0;
        color: #fff;
        font-size: clamp(25px, 3vw, 36px);
        font-weight: 800;
        letter-spacing: -.035em;
        line-height: 1.08;
    }

    .notification-command p {
        max-width: 680px;
        margin: 10px 0 0;
        color: rgba(226, 232, 240, .86);
        font-size: 14px;
        line-height: 1.65;
    }

    .notification-command-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 9px 16px;
        margin-top: 17px;
    }

    .notification-command-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: rgba(226, 232, 240, .82);
        font-size: 11px;
        font-weight: 700;
    }

    .notification-command-meta i { color: #5eead4; font-size: 15px; }

    .notification-command-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    .notification-icon-button,
    .notification-mark-read,
    .notif-action-btn,
    .notification-modal-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        font: inherit;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease;
    }

    .notification-icon-button,
    .notification-mark-read {
        min-height: 44px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }

    .notification-icon-button { width: 44px; background: rgba(255, 255, 255, .1); font-size: 20px; }

    .notification-mark-read {
        gap: 8px;
        padding: 0 17px;
        color: #0f172a;
        background: #fff;
        font-size: 12px;
        font-weight: 800;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .2);
    }

    .notification-icon-button:hover,
    .notification-mark-read:hover,
    .notif-action-btn:hover,
    .notification-modal-action:hover { transform: translateY(-2px); }
    .notification-icon-button:hover { background: rgba(255, 255, 255, .18); }
    .notification-mark-read:hover {
        color: var(--notif-primary-dark);
        box-shadow: 0 16px 34px rgba(15, 23, 42, .26);
    }

    .notification-icon-button:focus-visible,
    .notification-mark-read:focus-visible,
    .notification-filter:focus-visible,
    .notification-search-clear:focus-visible,
    .notif-action-btn:focus-visible,
    .notification-modal-action:focus-visible,
    .notification-modal-tab:focus-visible {
        outline: 3px solid rgba(59, 130, 246, .3);
        outline-offset: 2px;
    }
    .notification-icon-button.is-loading i { animation: notification-spin .75s linear infinite; }

    .notification-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .notification-metric {
        position: relative;
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
        min-height: 112px;
        padding: 18px;
        overflow: hidden;
        background: var(--notif-surface);
        border: 1px solid var(--notif-line);
        border-radius: var(--notif-radius);
        box-shadow: var(--notif-shadow);
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .notification-metric::before {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 3px;
        content: "";
        background: #3b82f6;
    }
    .notification-metric.is-unread::before { background: #f59e0b; }
    .notification-metric.is-action::before { background: #f43f5e; }
    .notification-metric.is-result::before { background: #10b981; }

    .notification-metric:hover {
        transform: translateY(-3px);
        border-color: #cbd5e1;
        box-shadow: var(--notif-shadow-hover);
    }

    .metric-icon,
    .notif-document-icon,
    .notification-modal-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 13px;
        font-size: 23px;
    }
    .metric-icon.is-total { color: #1d4ed8; background: #eff6ff; }
    .metric-icon.is-unread { color: #b45309; background: #fffbeb; }
    .metric-icon.is-action { color: #be123c; background: #fff1f2; }
    .metric-icon.is-result { color: #047857; background: #ecfdf5; }

    .notification-metric-copy { position: relative; z-index: 1; min-width: 0; }
    .notification-metric-label,
    .notification-metric strong,
    .notification-metric small { display: block; }

    .notification-metric-label {
        margin-bottom: 5px;
        color: var(--notif-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .notification-metric strong {
        color: var(--notif-ink);
        font-size: 25px;
        font-weight: 800;
        letter-spacing: -.035em;
        line-height: 1;
    }

    .notification-metric small {
        margin-top: 6px;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
    }

    .metric-decoration {
        position: absolute;
        right: -7px;
        bottom: -14px;
        color: #2563eb;
        font-size: 62px;
        opacity: .045;
        transform: rotate(-9deg);
    }
    .notification-metric.is-unread .metric-decoration { color: #d97706; }
    .notification-metric.is-action .metric-decoration { color: #e11d48; }
    .notification-metric.is-result .metric-decoration { color: #059669; }

    .notification-inbox {
        min-width: 0;
        overflow: hidden;
        background: var(--notif-surface);
        border: 1px solid var(--notif-line);
        border-radius: 18px;
        box-shadow: var(--notif-shadow);
    }

    .notification-inbox-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 19px 22px;
        border-bottom: 1px solid var(--notif-line);
    }

    .notification-inbox-heading > div {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .notification-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        color: var(--notif-primary-dark);
        background: #eff6ff;
        border-radius: 11px;
        font-size: 21px;
    }

    .notification-inbox-heading h2 {
        margin: 0;
        color: var(--notif-ink);
        font-size: 16px;
        font-weight: 800;
        letter-spacing: -.015em;
    }

    .notification-inbox-heading p {
        margin: 3px 0 0;
        color: var(--notif-muted);
        font-size: 11px;
        font-weight: 600;
    }

    .notification-result-count {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex: 0 0 auto;
        min-height: 32px;
        padding: 0 10px;
        color: var(--notif-muted);
        background: var(--notif-soft);
        border: 1px solid var(--notif-line);
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
    }
    .notification-result-count i { color: var(--notif-primary); font-size: 15px; }
    .notification-result-count strong { color: var(--notif-ink); font-weight: 900; }

    .notification-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 15px 22px;
        background: #fbfdff;
        border-bottom: 1px solid var(--notif-line);
    }

    .notification-filters {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    .notification-filter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex: 0 0 auto;
        min-height: 36px;
        padding: 0 12px;
        color: #526178;
        background: #fff;
        border: 1px solid var(--notif-line);
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
        transition: color .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .notification-filter i { font-size: 15px; }
    .notification-filter:hover { color: var(--notif-primary-dark); border-color: #bfdbfe; }
    .notification-filter.is-active {
        color: #fff;
        background: var(--notif-primary);
        border-color: var(--notif-primary);
        box-shadow: 0 8px 18px rgba(37, 99, 235, .2);
    }

    .notification-search {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 0 1 350px;
        width: min(350px, 100%);
        min-height: 40px;
        padding: 0 10px 0 12px;
        color: var(--notif-muted);
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 11px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .025);
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .notification-search:focus-within { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59, 130, 246, .11); }
    .notification-search > i { flex: 0 0 auto; font-size: 18px; }

    .notification-search input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        color: var(--notif-ink);
        background: transparent;
        font-size: 11px;
        font-weight: 650;
    }
    .notification-search input::placeholder { color: #94a3b8; }

    .notification-search-clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 25px;
        height: 25px;
        padding: 0;
        color: #94a3b8;
        background: transparent;
        border: 0;
        border-radius: 50%;
        font-size: 16px;
        opacity: 0;
        pointer-events: none;
        transition: color .18s ease, opacity .18s ease;
    }
    .notification-search.has-value .notification-search-clear { opacity: 1; pointer-events: auto; }
    .notification-search-clear:hover { color: #ef4444; }

    .notification-table-shell {
        min-width: 0;
        padding: 0 18px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .notification-table-shell .dataTables_wrapper { min-width: 0; }

    .notification-table {
        width: 100% !important;
        min-width: 980px;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 10px !important;
    }

    .notification-table thead th {
        padding: 8px 13px !important;
        color: #7a8aa0;
        background: transparent;
        border: 0 !important;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .075em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .notification-table thead th:first-child { text-align: center; }

    .notification-table tbody tr {
        position: relative;
        background: #fff;
        filter: drop-shadow(0 5px 13px rgba(15, 23, 42, .04));
        transition: filter .2s ease, transform .2s ease;
    }
    .notification-table tbody tr:hover {
        z-index: 1;
        filter: drop-shadow(0 11px 22px rgba(15, 23, 42, .09));
        transform: translateY(-1px);
    }

    .notification-table tbody td {
        padding: 14px 13px !important;
        background: #fff;
        border-top: 1px solid var(--notif-line) !important;
        border-bottom: 1px solid var(--notif-line) !important;
        vertical-align: middle;
    }

    .notification-table tbody td:first-child {
        color: #94a3b8;
        border-left: 1px solid var(--notif-line);
        border-radius: 12px 0 0 12px;
        font-size: 10px;
        font-weight: 900;
        text-align: center;
    }
    .notification-table tbody td:last-child { border-right: 1px solid var(--notif-line); border-radius: 0 12px 12px 0; }

    .notification-table tbody tr.notif-unread td {
        background: #fffdf7;
        border-top-color: #fde8b5 !important;
        border-bottom-color: #fde8b5 !important;
    }
    .notification-table tbody tr.notif-unread td:first-child { color: #b45309; border-left: 3px solid #f59e0b; }
    .notification-table tbody tr.notif-unread td:last-child { border-right-color: #fde8b5; }

    .notif-document {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 215px;
    }

    .notif-document-icon {
        width: 40px;
        height: 40px;
        color: var(--notif-primary-dark);
        background: #eff6ff;
        border-radius: 11px;
        font-size: 20px;
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, .06);
    }

    .notif-document > div,
    .notif-summary { min-width: 0; }
    .notif-document strong,
    .notif-document small,
    .notif-summary strong,
    .notif-summary span,
    .notif-summary small { display: block; }

    .notif-document strong {
        color: var(--notif-ink);
        font-size: 11px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }
    .notif-document small {
        margin-top: 4px;
        color: var(--notif-muted);
        font-size: 9px;
        font-weight: 700;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .notif-summary { min-width: 270px; max-width: 520px; }
    .notif-summary strong { color: var(--notif-ink); font-size: 11px; font-weight: 900; line-height: 1.4; }
    .notif-summary span {
        display: -webkit-box;
        margin-top: 3px;
        overflow: hidden;
        color: #526178;
        font-size: 10px;
        line-height: 1.45;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .notif-summary small { margin-top: 5px; color: #8593a6; font-size: 9px; font-weight: 700; line-height: 1.45; }
    .notif-summary .notif-items-line {
        display: -webkit-box;
        color: var(--notif-teal) !important;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
    }

    .notif-status-stack { display: flex; flex-wrap: wrap; gap: 5px; max-width: 175px; }
    .notif-state,
    .notif-read,
    .notif-action-state {
        display: inline-flex;
        align-items: center;
        min-height: 23px;
        padding: 0 8px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .015em;
        line-height: 1.15;
        white-space: nowrap;
    }
    .notif-state.is-success,
    .notif-read.is-read { color: #047857; background: #ecfdf5; border-color: #d1fae5; }
    .notif-state.is-warning,
    .notif-read.is-unread,
    .notif-action-state.is-actionable { color: #a16207; background: #fffbeb; border-color: #fef3c7; }
    .notif-state.is-danger { color: #be123c; background: #fff1f2; border-color: #ffe4e6; }
    .notif-state.is-info,
    .notif-state.is-primary { color: #1d4ed8; background: #eff6ff; border-color: #dbeafe; }
    .notif-state.is-muted,
    .notif-action-state.is-passive { color: #526178; background: #f8fafc; border-color: #e2e8f0; }

    .notification-table tbody td:nth-child(5) {
        color: var(--notif-muted);
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .notif-table-actions { display: flex; align-items: center; gap: 7px; }
    .notif-action-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        color: #526178;
        background: #f8fafc;
        border: 1px solid var(--notif-line);
        border-radius: 10px;
        text-decoration: none;
        font-size: 17px;
    }
    .notif-action-btn:hover {
        color: var(--notif-primary-dark);
        background: #eff6ff;
        border-color: #bfdbfe;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    }
    .notif-action-btn.is-primary {
        color: #fff;
        background: var(--notif-primary);
        border-color: var(--notif-primary);
        box-shadow: 0 7px 16px rgba(37, 99, 235, .2);
    }
    .notif-action-btn.is-primary:hover { background: var(--notif-primary-dark); }

    .notification-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-width: 980px;
        padding: 13px 3px 17px;
        color: var(--notif-muted);
        font-size: 10px;
    }
    .notification-table-footer .dataTables_info,
    .notification-table-footer .dataTables_paginate { padding: 0 !important; }
    .notification-table-footer .pagination { display: flex; gap: 5px; margin: 0; }
    .notification-table-footer .page-item .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 31px;
        height: 31px;
        padding: 0 8px;
        color: #526178;
        background: #fff;
        border: 1px solid var(--notif-line);
        border-radius: 9px !important;
        font-size: 10px;
        font-weight: 800;
        box-shadow: none;
    }
    .notification-table-footer .page-item.active .page-link,
    .notification-table-footer .page-item:not(.disabled) .page-link:hover { color: #fff; background: var(--notif-primary); border-color: var(--notif-primary); }
    .notification-table-footer .page-item.disabled .page-link { color: #cbd5e1; background: #f8fafc; }

    .notification-table td.dataTables_empty {
        height: 230px;
        color: var(--notif-muted);
        border: 1px dashed #cbd5e1 !important;
        border-radius: 14px !important;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
    }

    .notification-table-shell div.dataTables_wrapper div.dataTables_processing {
        position: absolute;
        top: 88px;
        left: 50%;
        z-index: 5;
        width: auto;
        min-width: 150px;
        height: auto;
        padding: 11px 15px;
        margin: 0;
        color: var(--notif-primary-dark);
        background: rgba(255, 255, 255, .96);
        border: 1px solid #dbeafe;
        border-radius: 999px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .13);
        font-size: 10px;
        font-weight: 800;
        transform: translateX(-50%);
    }

    /* Modal detail */
    .notification-modal .modal-dialog { max-width: min(1140px, calc(100% - 32px)); }
    .notification-modal-content {
        max-height: calc(100vh - 3rem);
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid rgba(148, 163, 184, .26);
        border-radius: 20px;
        box-shadow: 0 30px 90px rgba(15, 23, 42, .3);
    }

    .notification-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #fff 0%, #f1f7ff 52%, #effcf8 100%);
        border-bottom: 1px solid var(--notif-line);
    }

    .notification-modal-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .notification-modal-icon { color: #fff; background: var(--notif-primary); box-shadow: 0 12px 26px rgba(37, 99, 235, .22); }
    .notification-modal-title > div { min-width: 0; }
    .notification-modal-title h5 {
        margin: 0;
        overflow-wrap: anywhere;
        color: var(--notif-ink);
        font-size: 16px;
        font-weight: 900;
    }
    .notification-modal-title small {
        display: block;
        margin-top: 3px;
        overflow-wrap: anywhere;
        color: var(--notif-muted);
        font-size: 10px;
        font-weight: 700;
    }

    .notification-modal-header-actions { display: inline-flex; align-items: center; gap: 10px; flex: 0 0 auto; }
    .notification-modal-status,
    .notification-kind-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 29px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .notification-modal-icon.is-success,
    .notification-kind-pill.is-success,
    .notification-modal-status.is-success { color: #047857; background: #d1fae5; }
    .notification-modal-icon.is-danger,
    .notification-kind-pill.is-danger,
    .notification-modal-status.is-danger { color: #be123c; background: #ffe4e6; }
    .notification-modal-icon.is-warning,
    .notification-kind-pill.is-warning,
    .notification-modal-status.is-warning { color: #a16207; background: #fef3c7; }
    .notification-modal-icon.is-info,
    .notification-kind-pill.is-info,
    .notification-modal-status.is-info,
    .notification-modal-icon.is-primary,
    .notification-kind-pill.is-primary,
    .notification-modal-status.is-primary { color: #1d4ed8; background: #dbeafe; }
    .notification-modal-icon.is-muted,
    .notification-kind-pill.is-muted,
    .notification-modal-status.is-muted { color: #526178; background: #e2e8f0; }

    .notification-modal-body {
        min-height: 0;
        padding: 18px;
        overflow-x: hidden;
        overflow-y: auto;
        background: #f8fafc;
        overscroll-behavior: contain;
    }

    .notification-modal-empty {
        display: grid;
        justify-items: center;
        gap: 6px;
        padding: 48px 18px;
        color: var(--notif-muted);
        text-align: center;
    }

    .notification-modal-empty span,
    .notification-items-panel.is-empty > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        color: var(--notif-primary-dark);
        background: #dbeafe;
        border-radius: 13px;
        font-size: 24px;
    }
    .notification-modal-empty strong,
    .notification-modal-empty small,
    .notification-items-panel.is-empty strong,
    .notification-items-panel.is-empty small { display: block; }
    .notification-modal-empty strong,
    .notification-items-panel.is-empty strong { color: var(--notif-ink); font-weight: 900; }

    .notification-modal-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(230px, 280px);
        gap: 16px;
        padding: 17px;
        background: #fff;
        border: 1px solid #dbe3ef;
        border-top: 4px solid var(--notif-primary);
        border-radius: 15px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .06);
    }
    .notification-modal-hero.is-success { border-top-color: #059669; }
    .notification-modal-hero.is-danger { border-top-color: #e11d48; }
    .notification-modal-hero.is-warning { border-top-color: #d97706; }
    .notification-modal-hero.is-muted { border-top-color: #64748b; }
    .notification-hero-main,
    .notification-hero-value { min-width: 0; }

    .notification-hero-main h6 {
        margin: 11px 0 5px;
        overflow-wrap: anywhere;
        color: var(--notif-ink);
        font-size: 20px;
        font-weight: 900;
    }
    .notification-hero-main p { margin: 0; color: #526178; font-size: 11px; font-weight: 600; line-height: 1.6; }
    .notification-hero-meta { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 13px; }
    .notification-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
        min-height: 28px;
        padding: 0 9px;
        overflow-wrap: anywhere;
        color: #526178;
        background: #f8fafc;
        border: 1px solid var(--notif-line);
        border-radius: 8px;
        font-size: 9px;
        font-weight: 800;
    }

    .notification-hero-value {
        display: grid;
        align-content: center;
        gap: 4px;
        padding: 16px;
        color: #dffcf6;
        background: linear-gradient(135deg, #0f766e, #115e59);
        border-radius: 13px;
        box-shadow: 0 13px 25px rgba(15, 118, 110, .18);
    }
    .notification-hero-value span,
    .notification-hero-value small { font-size: 9px; font-weight: 800; opacity: .88; }
    .notification-hero-value strong { overflow-wrap: anywhere; color: #fff; font-size: 24px; font-weight: 900; line-height: 1.1; }

    .notification-modal-tabs {
        display: flex;
        gap: 7px;
        margin-top: 14px;
        padding: 5px;
        background: #e8eef5;
        border-radius: 12px;
    }
    .notification-modal-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        flex: 1 1 0;
        min-height: 38px;
        padding: 0 12px;
        color: #526178;
        background: transparent;
        border: 0;
        border-radius: 9px;
        font-size: 10px;
        font-weight: 900;
        white-space: nowrap;
        transition: color .18s ease, background .18s ease, box-shadow .18s ease;
    }
    .notification-modal-tab.is-active { color: var(--notif-ink); background: #fff; box-shadow: 0 7px 18px rgba(15, 23, 42, .08); }
    .notification-modal-pane { display: none; margin-top: 14px; }
    .notification-modal-pane.is-active { display: block; animation: notification-fade-in .2s ease; }

    .notification-insight-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 11px; }
    .notification-insight-card {
        display: flex;
        gap: 10px;
        min-width: 0;
        padding: 12px;
        background: #fff;
        border: 1px solid var(--notif-line);
        border-radius: 13px;
    }
    .notification-insight-card > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 37px;
        height: 37px;
        color: var(--notif-primary-dark);
        background: #dbeafe;
        border-radius: 10px;
        font-size: 19px;
    }
    .notification-insight-card.is-success > span { color: #047857; background: #d1fae5; }
    .notification-insight-card.is-warning > span { color: #a16207; background: #fef3c7; }
    .notification-insight-card.is-danger > span { color: #be123c; background: #ffe4e6; }
    .notification-insight-card div { min-width: 0; }
    .notification-insight-card small,
    .notification-insight-card strong,
    .notification-insight-card em { display: block; overflow-wrap: anywhere; }
    .notification-insight-card small { color: var(--notif-muted); font-size: 8px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .notification-insight-card strong { margin-top: 3px; color: var(--notif-ink); font-size: 10px; font-weight: 900; }
    .notification-insight-card em { margin-top: 2px; color: var(--notif-muted); font-size: 9px; font-style: normal; font-weight: 700; }

    .notification-message-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 14px;
        padding: 14px;
        color: var(--notif-copy);
        background: #fff;
        border: 1px solid var(--notif-line);
        border-left: 4px solid var(--notif-primary);
        border-radius: 12px;
    }
    .notification-message-panel.is-success { border-left-color: #059669; }
    .notification-message-panel.is-danger { border-left-color: #e11d48; }
    .notification-message-panel.is-warning { border-left-color: #d97706; }
    .notification-message-panel strong,
    .notification-message-panel span,
    .notification-message-panel small { display: block; }
    .notification-message-panel strong { color: var(--notif-ink); font-size: 10px; font-weight: 900; }
    .notification-message-panel span { margin-top: 4px; color: #526178; font-size: 10px; line-height: 1.55; }
    .notification-message-panel small { flex: 0 0 auto; color: var(--notif-muted); font-size: 9px; font-weight: 800; }

    .notification-timeline { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 11px; margin-top: 14px; }
    .notification-timeline-item {
        display: flex;
        gap: 10px;
        min-width: 0;
        padding: 12px;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
    }
    .notification-timeline-item > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        color: var(--notif-teal);
        background: #ccfbf1;
        border-radius: 10px;
        font-size: 17px;
    }
    .notification-timeline-item strong,
    .notification-timeline-item small,
    .notification-timeline-item em { display: block; overflow-wrap: anywhere; }
    .notification-timeline-item strong { color: var(--notif-ink); font-size: 10px; font-weight: 900; }
    .notification-timeline-item small,
    .notification-timeline-item em { color: var(--notif-muted); font-size: 9px; font-style: normal; font-weight: 700; }

    .notification-detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 11px; }
    .notification-detail-field {
        min-width: 0;
        padding: 12px;
        background: #fff;
        border: 1px solid var(--notif-line);
        border-radius: 12px;
    }
    .notification-detail-field span,
    .notification-detail-field strong { overflow-wrap: anywhere; }
    .notification-detail-field span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--notif-muted);
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .notification-detail-field strong { display: block; margin-top: 5px; color: var(--notif-ink); font-size: 10px; font-weight: 900; }

    .notification-items-panel {
        margin-top: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--notif-line);
        border-radius: 13px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
    }
    .notification-items-panel.is-empty { display: flex; align-items: center; gap: 12px; min-height: 110px; padding: 15px; }
    .notification-items-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        background: #fbfdff;
        border-bottom: 1px solid var(--notif-line);
    }
    .notification-items-heading strong,
    .notification-items-heading small { display: block; }
    .notification-items-heading strong { color: var(--notif-ink); font-size: 11px; font-weight: 900; }
    .notification-items-heading small { margin-top: 3px; color: var(--notif-muted); font-size: 9px; font-weight: 700; }

    .notification-items-search {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: min(330px, 100%);
        min-height: 37px;
        padding: 0 11px;
        color: var(--notif-muted);
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
    }
    .notification-items-search input {
        width: 100%;
        min-width: 0;
        color: var(--notif-ink);
        background: transparent;
        border: 0;
        outline: 0;
        font-size: 10px;
        font-weight: 700;
    }

    .notification-items-table-wrap { max-width: 100%; overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; }
    .notification-items-table { width: 100%; min-width: 960px; border-collapse: collapse; }
    .notification-items-table th,
    .notification-items-table td { padding: 11px 12px; border-bottom: 1px solid var(--notif-line); vertical-align: top; }
    .notification-items-table th {
        color: var(--notif-muted);
        background: #fff;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .notification-items-table tbody tr { transition: background .18s ease; }
    .notification-items-table tbody tr:hover { background: #f8fafc; }
    .notification-items-table tbody tr:last-child td { border-bottom: 0; }
    .notification-items-table strong,
    .notification-items-table small,
    .notification-items-table span { display: block; font-size: 9px; }
    .notification-items-table strong { color: var(--notif-ink); font-weight: 900; }
    .notification-items-table small,
    .notification-items-table span { color: var(--notif-muted); font-weight: 700; }
    .notification-item-number { color: var(--notif-muted); font-weight: 900; }
    .notification-item-note { min-width: 180px; color: #526178; font-weight: 700; }

    .notification-items-empty-state { display: none; justify-items: center; gap: 4px; padding: 28px; color: var(--notif-muted); text-align: center; }
    .notification-items-empty-state.is-visible { display: grid; }
    .notification-items-empty-state i { color: var(--notif-primary); font-size: 28px; }
    .notification-items-empty-state strong,
    .notification-items-empty-state small { display: block; }

    .notification-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 14px 18px;
        background: #fff;
        border-top: 1px solid var(--notif-line);
    }
    .notification-modal-action-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .notification-modal-action-group.is-secondary { justify-content: flex-end; }

    .notification-modal-action {
        gap: 7px;
        min-height: 38px;
        padding: 0 12px;
        color: #334155;
        background: #e2e8f0;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
    }
    .notification-modal-action:hover { color: inherit; box-shadow: 0 9px 20px rgba(15, 23, 42, .1); }
    .notification-modal-action:disabled,
    .notification-modal-action.is-loading { cursor: progress; opacity: .7; transform: none; }
    .notification-modal-action.is-static { color: var(--notif-muted); background: #f1f5f9; box-shadow: none; cursor: default; }
    .notification-modal-action.is-primary { color: #fff; background: var(--notif-primary); }
    .notification-modal-action.is-success { color: #fff; background: #059669; }
    .notification-modal-action.is-danger { color: #fff; background: #e11d48; }
    .notification-modal-action.is-warning { color: #111827; background: #facc15; }
    .notification-modal-action.is-copy { color: var(--notif-teal); background: #ccfbf1; }
    .notification-modal-action.is-detail { color: #9a3412; background: #ffedd5; }
    .notification-modal-action.is-page { color: var(--notif-primary-dark); background: #dbeafe; }

    /* Premium modal treatment */
    .modal-backdrop.show {
        background: #07111f;
        opacity: .64;
        backdrop-filter: blur(5px);
    }

    .notification-modal .modal-dialog {
        max-width: min(1180px, calc(100% - 36px));
    }

    .notification-modal-content {
        position: relative;
        max-height: calc(100vh - 2.5rem);
        background: #f4f7fb;
        border: 1px solid rgba(255, 255, 255, .72);
        border-radius: 24px;
        box-shadow: 0 34px 110px rgba(2, 8, 23, .42), 0 0 0 1px rgba(15, 23, 42, .08);
        isolation: isolate;
    }

    .notification-modal-content::before {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        z-index: 3;
        height: 4px;
        content: "";
        background: linear-gradient(90deg, #2563eb 0%, #38bdf8 45%, #2dd4bf 100%);
    }

    .notification-modal-header {
        position: relative;
        min-height: 98px;
        padding: 20px 24px;
        overflow: hidden;
        background:
            radial-gradient(circle at 82% -15%, rgba(45, 212, 191, .18), transparent 34%),
            linear-gradient(130deg, #ffffff 0%, #f4f8ff 58%, #effcf9 100%);
    }

    .notification-modal-header::after {
        position: absolute;
        right: 19%;
        bottom: -54px;
        width: 170px;
        height: 100px;
        content: "";
        border: 1px solid rgba(37, 99, 235, .08);
        border-radius: 50%;
        box-shadow: 0 0 0 22px rgba(37, 99, 235, .025), 0 0 0 44px rgba(15, 118, 110, .018);
        pointer-events: none;
    }

    .notification-modal-header-glow {
        position: absolute;
        top: -40px;
        left: 23%;
        width: 190px;
        height: 100px;
        background: rgba(59, 130, 246, .08);
        border-radius: 50%;
        filter: blur(28px);
        pointer-events: none;
    }

    .notification-modal-title,
    .notification-modal-header-actions {
        position: relative;
        z-index: 1;
    }

    .notification-modal-icon {
        width: 52px;
        height: 52px;
        border: 1px solid rgba(255, 255, 255, .6);
        border-radius: 16px;
        font-size: 25px;
        box-shadow: 0 14px 28px rgba(37, 99, 235, .2), inset 0 1px 0 rgba(255, 255, 255, .35);
    }

    .notification-modal-title-copy { min-width: 0; }

    .notification-modal-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .notification-modal-eyebrow i {
        color: #0f766e;
        font-size: 13px;
    }

    .notification-modal-title h5 {
        letter-spacing: -.025em;
    }

    .notification-modal-status {
        min-height: 32px;
        padding-inline: 12px;
        border: 1px solid rgba(148, 163, 184, .18);
        box-shadow: 0 7px 18px rgba(15, 23, 42, .05);
    }

    .notification-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        padding: 0;
        color: #475569;
        background: rgba(255, 255, 255, .82);
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        box-shadow: 0 7px 18px rgba(15, 23, 42, .07);
        font-size: 20px;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
    }

    .notification-modal-close:hover {
        color: #be123c;
        background: #fff1f2;
        border-color: #fecdd3;
        transform: rotate(4deg);
    }

    .notification-modal-close:focus-visible {
        outline: 3px solid rgba(59, 130, 246, .28);
        outline-offset: 2px;
    }

    .notification-modal-body {
        padding: 22px;
        background:
            radial-gradient(circle at 3% 8%, rgba(37, 99, 235, .045), transparent 22%),
            linear-gradient(180deg, #f7f9fc 0%, #f3f6fa 100%);
    }

    .notification-modal-hero {
        position: relative;
        gap: 20px;
        padding: 20px;
        overflow: hidden;
        border: 1px solid #dce5f0;
        border-top: 0;
        border-radius: 18px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .08), inset 4px 0 0 #2563eb;
        isolation: isolate;
    }

    .notification-modal-hero::before {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 4px;
        content: "";
        background: linear-gradient(90deg, #2563eb, #38bdf8);
    }

    .notification-modal-hero.is-success { box-shadow: 0 16px 38px rgba(15, 23, 42, .08), inset 4px 0 0 #059669; }
    .notification-modal-hero.is-success::before { background: linear-gradient(90deg, #059669, #2dd4bf); }
    .notification-modal-hero.is-danger { box-shadow: 0 16px 38px rgba(15, 23, 42, .08), inset 4px 0 0 #e11d48; }
    .notification-modal-hero.is-danger::before { background: linear-gradient(90deg, #e11d48, #fb7185); }
    .notification-modal-hero.is-warning { box-shadow: 0 16px 38px rgba(15, 23, 42, .08), inset 4px 0 0 #d97706; }
    .notification-modal-hero.is-warning::before { background: linear-gradient(90deg, #d97706, #facc15); }
    .notification-modal-hero.is-muted { box-shadow: 0 16px 38px rgba(15, 23, 42, .08), inset 4px 0 0 #64748b; }
    .notification-modal-hero.is-muted::before { background: linear-gradient(90deg, #64748b, #94a3b8); }

    .notification-hero-watermark {
        position: absolute;
        right: 31%;
        bottom: -35px;
        z-index: -1;
        color: #2563eb;
        font-size: 150px;
        opacity: .035;
        transform: rotate(-10deg);
        pointer-events: none;
    }

    .notification-hero-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    .notification-module-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 29px;
        padding: 0 10px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 850;
    }

    .notification-hero-main h6 {
        margin-top: 14px;
        letter-spacing: -.025em;
    }

    .notification-hero-value {
        grid-template-columns: auto minmax(0, 1fr);
        align-items: center;
        align-content: center;
        gap: 12px;
        min-height: 126px;
        background:
            radial-gradient(circle at 100% 0%, rgba(255, 255, 255, .18), transparent 37%),
            linear-gradient(145deg, #0f766e 0%, #0f5f5b 52%, #0c4a6e 125%);
        border: 1px solid rgba(255, 255, 255, .13);
        border-radius: 16px;
        box-shadow: 0 18px 32px rgba(15, 118, 110, .2), inset 0 1px 0 rgba(255, 255, 255, .15);
    }

    .notification-hero-value-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        color: #fff;
        background: rgba(255, 255, 255, .13);
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 13px;
        font-size: 21px !important;
        opacity: 1 !important;
    }

    .notification-hero-value > div { min-width: 0; }
    .notification-hero-value > div > span,
    .notification-hero-value > div > strong,
    .notification-hero-value > div > small { display: block; }

    .notification-modal-tabs {
        position: relative;
        gap: 6px;
        padding: 6px;
        background: #e7edf5;
        border: 1px solid #dde5ef;
        border-radius: 14px;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, .04);
    }

    .notification-modal-tab {
        min-height: 42px;
        border-radius: 10px;
    }

    .notification-modal-tab.is-active {
        color: #1d4ed8;
        box-shadow: 0 9px 22px rgba(15, 23, 42, .1), inset 0 -2px 0 #2563eb;
    }

    .notification-insight-card,
    .notification-timeline-item,
    .notification-detail-field {
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .notification-insight-card {
        position: relative;
        padding: 14px;
        overflow: hidden;
        border-color: #dce5ef;
        border-radius: 15px;
        box-shadow: 0 9px 22px rgba(15, 23, 42, .045);
    }

    .notification-insight-card::after {
        position: absolute;
        right: -18px;
        bottom: -22px;
        width: 58px;
        height: 58px;
        content: "";
        background: #dbeafe;
        border-radius: 50%;
        opacity: .32;
    }

    .notification-insight-card:hover,
    .notification-detail-field:hover {
        transform: translateY(-2px);
        border-color: #bfdbfe;
        box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
    }

    .notification-message-panel {
        padding: 16px;
        background: linear-gradient(135deg, #fff 0%, #fbfdff 100%);
        border-radius: 14px;
        box-shadow: 0 9px 22px rgba(15, 23, 42, .04);
    }

    .notification-timeline-item {
        padding: 14px;
        border-color: #cbd5e1;
        border-radius: 14px;
    }

    .notification-timeline-item:hover {
        border-color: #99f6e4;
        box-shadow: 0 12px 26px rgba(15, 118, 110, .07);
    }

    .notification-detail-field {
        padding: 14px;
        border-color: #dce5ef;
        border-radius: 14px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .035);
    }

    .notification-items-panel {
        border-color: #dce5ef;
        border-radius: 15px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .06);
    }

    .notification-modal-footer {
        align-items: flex-end;
        padding: 16px 22px 18px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 -10px 30px rgba(15, 23, 42, .035);
    }

    .notification-modal-footer-section {
        display: grid;
        gap: 7px;
        min-width: 0;
    }

    .notification-modal-footer-section.is-secondary {
        justify-items: end;
    }

    .notification-modal-footer-label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #7c8ba1;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .075em;
        text-transform: uppercase;
    }

    .notification-modal-footer-label i {
        color: #2563eb;
        font-size: 13px;
    }

    .notification-modal-action {
        min-height: 42px;
        padding-inline: 14px;
        border: 1px solid transparent;
        border-radius: 11px;
    }

    .notification-modal-action.is-static { border-color: #e2e8f0; }
    .notification-modal-action.is-primary { box-shadow: 0 10px 22px rgba(37, 99, 235, .2); }
    .notification-modal-action.is-success { box-shadow: 0 10px 22px rgba(5, 150, 105, .18); }
    .notification-modal-action.is-danger { box-shadow: 0 10px 22px rgba(225, 29, 72, .18); }
    .notification-modal-action.is-copy { border-color: #99f6e4; }
    .notification-modal-action.is-detail { border-color: #fed7aa; }
    .notification-modal-action.is-page { border-color: #bfdbfe; }

    /* Readability pass: keep operational information comfortable at normal zoom. */
    .notification-kicker { font-size: 11px; }
    .notification-command-meta span { font-size: 12px; }
    .notification-mark-read { font-size: 13px; }

    .notification-metric-label { font-size: 11px; }
    .notification-metric strong { font-size: 28px; }
    .notification-metric small { font-size: 11px; }

    .notification-inbox-heading h2 { font-size: 18px; }
    .notification-inbox-heading p { font-size: 12px; }
    .notification-result-count { font-size: 11px; }
    .notification-filter { font-size: 12px; }
    .notification-search input { font-size: 12px; }

    .notification-table thead th { font-size: 10.5px; }
    .notification-table tbody td:first-child { font-size: 11px; }
    .notif-document strong { font-size: 12.5px; }
    .notif-document small { font-size: 10.5px; }
    .notif-summary strong { font-size: 12.5px; }
    .notif-summary span { font-size: 11.5px; line-height: 1.55; }
    .notif-summary small { font-size: 10.5px; }
    .notif-state,
    .notif-read,
    .notif-action-state { min-height: 25px; font-size: 9.5px; }
    .notification-table tbody td:nth-child(5) { font-size: 10.5px; }
    .notification-table-footer { font-size: 11px; }
    .notification-table-footer .page-item .page-link { font-size: 11px; }
    .notification-table td.dataTables_empty,
    .notification-table-shell div.dataTables_wrapper div.dataTables_processing { font-size: 12px; }

    .notification-modal-title h5 { font-size: 18px; }
    .notification-modal-title small { font-size: 11px; }
    .notification-modal-status,
    .notification-kind-pill { font-size: 10px; }
    .notification-hero-main h6 { font-size: 22px; }
    .notification-hero-main p { font-size: 12.5px; }
    .notification-hero-meta span { font-size: 10.5px; }
    .notification-hero-value span,
    .notification-hero-value small { font-size: 10.5px; }
    .notification-hero-value strong { font-size: 27px; }
    .notification-modal-tab { font-size: 11.5px; }
    .notification-insight-card small { font-size: 9.5px; }
    .notification-insight-card strong { font-size: 11.5px; }
    .notification-insight-card em { font-size: 10.5px; }
    .notification-message-panel strong { font-size: 11.5px; }
    .notification-message-panel span { font-size: 11.5px; }
    .notification-message-panel small { font-size: 10.5px; }
    .notification-timeline-item strong { font-size: 11.5px; }
    .notification-timeline-item small,
    .notification-timeline-item em { font-size: 10.5px; }
    .notification-detail-field span { font-size: 9.5px; }
    .notification-detail-field strong { font-size: 11.5px; }
    .notification-items-heading strong { font-size: 12.5px; }
    .notification-items-heading small { font-size: 10.5px; }
    .notification-items-search input { font-size: 11.5px; }
    .notification-items-table th { font-size: 9.5px; }
    .notification-items-table strong,
    .notification-items-table small,
    .notification-items-table span { font-size: 10.5px; }
    .notification-modal-action { font-size: 11.5px; }

    @keyframes notification-spin { to { transform: rotate(360deg); } }
    @keyframes notification-fade-in {
        from { opacity: 0; transform: translateY(3px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1199.98px) {
        .notification-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .notification-toolbar { align-items: stretch; flex-direction: column; }
        .notification-search { flex-basis: auto; width: 100%; }
    }

    @media (max-width: 991.98px) {
        .notification-command { align-items: flex-start; flex-direction: column; min-height: 0; padding: 26px; }
        .notification-command-actions { width: 100%; }
        .notification-mark-read { flex: 1 1 auto; }
        .notification-insight-grid,
        .notification-detail-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .notification-modal-hero { grid-template-columns: 1fr; }
        .notification-items-heading,
        .notification-message-panel,
        .notification-modal-footer { align-items: stretch; flex-direction: column; }
        .notification-items-search,
        .notification-modal-action-group,
        .notification-modal-action-group.is-secondary { width: 100%; }
        .notification-modal-action-group.is-secondary { justify-content: flex-start; }
        .notification-modal-footer-section,
        .notification-modal-footer-section.is-secondary {
            justify-items: start;
            width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        .notification-page {
            gap: 15px;
            min-width: 0;
            overflow-x: clip;
        }
        .notification-page > * { min-width: 0; }
        .notification-breadcrumb .breadcrumb-item:not(:last-child) { display: none; }
        .notification-breadcrumb .breadcrumb-item:last-child::before { display: none; }
        .notification-command { padding: 22px; border-radius: 17px; }
        .notification-command h1 { font-size: clamp(26px, 8vw, 34px); }
        .notification-command p { font-size: 13px; line-height: 1.65; }
        .notification-command-meta { gap: 8px 12px; }
        .notification-command-actions { gap: 9px; }
        .notification-icon-button,
        .notification-mark-read { min-height: 44px; }
        .notification-icon-button { width: 44px; height: 44px; }
        .notification-inbox-heading { align-items: flex-start; padding: 16px; }
        .notification-result-count { min-height: 29px; }
        .notification-toolbar { padding: 13px 16px; }

        .notification-filters {
            width: calc(100% + 16px);
            padding-right: 16px;
            overflow-x: auto;
            scrollbar-width: none;
            scroll-snap-type: x proximity;
        }
        .notification-filters::-webkit-scrollbar { display: none; }
        .notification-filter {
            flex: 0 0 auto;
            min-height: 42px;
            scroll-snap-align: start;
        }
        .notification-search { min-height: 46px; }
        .notification-search-clear {
            width: 38px;
            height: 38px;
            margin-right: -8px;
        }

        .notification-table-shell { padding: 0; overflow: visible; }
        .notification-table,
        .notification-table tbody { display: block; width: 100% !important; min-width: 0; }
        .notification-table { border-spacing: 0 !important; }
        .notification-table thead { display: none; }
        .notification-table tbody { padding: 13px; }

        .notification-table tbody tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px 10px;
            margin-bottom: 12px;
            padding: 15px;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--notif-line);
            border-radius: 15px;
            filter: drop-shadow(0 8px 17px rgba(15, 23, 42, .05));
        }
        .notification-table tbody tr:last-child { margin-bottom: 0; }
        .notification-table tbody tr:hover { transform: none; }
        .notification-table tbody tr.notif-unread {
            background: #fffdf7;
            border-color: #fde8b5;
            box-shadow: inset 3px 0 0 #f59e0b;
        }

        .notification-table tbody td,
        .notification-table tbody tr.notif-unread td {
            display: block;
            min-width: 0;
            padding: 0 !important;
            background: transparent;
            border: 0 !important;
            border-radius: 0;
        }
        .notification-table tbody td:nth-child(1) {
            grid-column: 2;
            grid-row: 1;
            align-self: start;
            min-width: 26px;
            min-height: 26px;
            padding: 5px !important;
            color: var(--notif-muted);
            background: #f1f5f9;
            border-radius: 8px;
            text-align: center;
        }
        .notification-table tbody tr.notif-unread td:nth-child(1) { color: #a16207; background: #fef3c7; }
        .notification-table tbody td:nth-child(2) { grid-column: 1; grid-row: 1; }
        .notification-table tbody td:nth-child(3),
        .notification-table tbody td:nth-child(4) { grid-column: 1 / -1; }
        .notification-table tbody td:nth-child(5) { grid-column: 1; display: flex; align-items: center; gap: 5px; }
        .notification-table tbody td:nth-child(5)::before {
            content: "Diterima";
            color: #94a3b8;
            font-size: 9.5px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .notification-table tbody td:nth-child(6) { grid-column: 2; justify-self: end; }
        .notif-document,
        .notif-summary { min-width: 0; max-width: none; }
        .notif-document-icon { width: 38px; height: 38px; }
        .notif-status-stack { max-width: none; }
        .notif-summary span { -webkit-line-clamp: 3; }
        .notif-action-btn {
            width: 42px;
            height: 42px;
            font-size: 19px;
        }

        .notification-table tbody td.dataTables_empty {
            grid-column: 1 / -1;
            grid-row: 1;
            display: grid;
            place-items: center;
            min-height: 180px;
            padding: 20px !important;
            color: var(--notif-muted);
            background: #f8fafc;
            border: 1px dashed #cbd5e1 !important;
            border-radius: 12px;
        }

        .notification-table-footer {
            align-items: center;
            flex-direction: column;
            min-width: 0;
            padding: 6px 16px 17px;
            text-align: center;
        }
        .notification-table-footer .page-item .page-link {
            min-width: 38px;
            height: 38px;
        }

        .notification-modal .modal-dialog { max-width: calc(100% - 16px); margin: .5rem auto; }
        .notification-modal-content { max-height: calc(100vh - 1rem); border-radius: 18px; }
        .notification-modal-header,
        .notification-modal-body,
        .notification-modal-footer { padding: 14px; }
        .notification-modal-header {
            align-items: flex-start;
            flex-direction: column;
            min-height: 0;
        }
        .notification-modal-header-actions {
            justify-content: space-between;
            width: 100%;
        }
        .notification-modal-body { padding-top: 16px; }
        .notification-modal-hero { padding: 17px; border-radius: 15px; }
        .notification-hero-watermark { right: -8%; font-size: 120px; }
        .notification-modal-tabs { overflow-x: auto; scrollbar-width: none; }
        .notification-modal-tabs::-webkit-scrollbar { display: none; }
        .notification-modal-tab { flex: 1 0 auto; min-width: 108px; }
        .notification-timeline { grid-template-columns: 1fr; }
    }

    @media (max-width: 575.98px) {
        .notification-command {
            gap: 20px;
            padding: 20px;
            background:
                radial-gradient(circle at 95% 5%, rgba(255, 255, 255, .14) 0, rgba(255, 255, 255, 0) 27%),
                linear-gradient(145deg, #0f172a 0%, #123a72 58%, #0f766e 125%);
        }
        .notification-command::before { right: -82px; }
        .notification-command-actions { align-items: stretch; }
        .notification-mark-read {
            justify-content: center;
            padding-inline: 12px;
        }
        .notification-metrics { gap: 10px; }
        .notification-metric { align-items: flex-start; flex-direction: column; min-height: 142px; padding: 15px; }
        .metric-icon { width: 39px; height: 39px; border-radius: 11px; font-size: 20px; }
        .notification-metric strong { font-size: 25px; }
        .notification-inbox-heading {
            align-items: stretch;
            flex-direction: column;
            gap: 11px;
        }
        .notification-inbox-heading p { max-width: none; }
        .notification-result-count { align-self: flex-start; }
        .notification-section-icon { display: none; }
        .notification-toolbar { padding: 12px; }
        .notification-filters {
            width: calc(100% + 12px);
            padding-right: 12px;
        }
        .notification-table tbody { padding: 10px; }
        .notification-table tbody tr {
            gap: 11px 9px;
            margin-bottom: 10px;
            padding: 13px;
            border-radius: 14px;
        }
        .notification-table-footer .pagination { max-width: 100%; }
        .notification-table-footer .page-item:not(.previous):not(.next) { display: none; }
        .notification-table-footer .page-item.previous .page-link,
        .notification-table-footer .page-item.next .page-link {
            min-width: 48px;
            padding-inline: 14px;
        }

        .notification-insight-grid,
        .notification-detail-grid { grid-template-columns: 1fr; }

        .notification-modal { padding: 0 !important; }
        .notification-modal .modal-dialog,
        .notification-modal .modal-dialog-scrollable {
            align-items: stretch;
            width: 100%;
            max-width: none;
            height: 100%;
            min-height: 100%;
            margin: 0;
        }
        .notification-modal-content {
            width: 100%;
            height: 100vh;
            max-height: 100vh;
            border: 0;
            border-radius: 0;
        }
        .notification-modal-header {
            align-items: flex-start;
            flex: 0 0 auto;
            flex-direction: column;
            gap: 12px;
            padding: calc(14px + env(safe-area-inset-top)) 14px 13px;
        }
        .notification-modal-title { width: 100%; }
        .notification-modal-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            font-size: 22px;
        }
        .notification-modal-title h5 {
            max-width: 100%;
            font-size: 16px;
            white-space: normal;
        }
        .notification-modal-title small {
            display: -webkit-box;
            overflow: hidden;
            line-height: 1.45;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .notification-modal-header-actions { justify-content: space-between; width: 100%; }
        .notification-modal-status { min-height: 36px; }
        .notification-modal-close { width: 42px; height: 42px; }
        .notification-modal-body {
            min-height: 0;
            padding: 13px;
            padding-top: 13px;
            overscroll-behavior: contain;
        }
        .notification-modal-hero {
            gap: 14px;
            padding: 15px;
            border-radius: 14px;
        }
        .notification-hero-main h6 {
            margin-top: 12px;
            font-size: 18px;
        }
        .notification-hero-main p { line-height: 1.6; }
        .notification-hero-meta { gap: 7px; }
        .notification-hero-meta span { white-space: normal; }
        .notification-hero-value {
            min-height: 0;
            padding: 14px;
            border-radius: 13px;
        }
        .notification-hero-value strong {
            font-size: 21px;
            overflow-wrap: anywhere;
        }
        .notification-modal-tabs {
            position: sticky;
            top: 0;
            z-index: 4;
            gap: 4px;
            margin-top: 12px;
            overflow: visible;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
        }
        .notification-modal-tab {
            flex: 1 1 33.333%;
            min-width: 0;
            min-height: 44px;
            padding-inline: 7px;
        }
        .notification-message-panel { padding: 14px; }
        .notification-timeline { gap: 9px; }
        .notification-insight-card,
        .notification-timeline-item,
        .notification-detail-field { padding: 13px; }

        .notification-items-heading { padding: 13px; }
        .notification-items-search { min-height: 44px; }
        .notification-items-table-wrap {
            padding: 10px;
            overflow: visible;
            background: #f8fafc;
        }
        .notification-items-table,
        .notification-items-table tbody {
            display: block;
            width: 100%;
            min-width: 0;
        }
        .notification-items-table thead { display: none; }
        .notification-items-table tbody {
            display: grid;
            gap: 10px;
        }
        .notification-items-table tbody tr {
            position: relative;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
            overflow: hidden;
            background: #fff;
            border: 1px solid #dce5f0;
            border-radius: 13px;
            box-shadow: 0 7px 18px rgba(15, 23, 42, .05);
        }
        .notification-items-table tbody tr:hover { background: #fff; }
        .notification-items-table td {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 10px !important;
            overflow-wrap: anywhere;
            border-right: 1px solid #eef2f7;
            border-bottom: 1px solid #eef2f7;
        }
        .notification-items-table td::before {
            display: block;
            margin-bottom: 4px;
            color: #94a3b8;
            content: attr(data-label);
            font-size: 8.5px;
            font-weight: 900;
            letter-spacing: .055em;
            text-transform: uppercase;
        }
        .notification-items-table td:nth-child(even) { border-right: 0; }
        .notification-items-table td:nth-last-child(-n + 2) { border-bottom: 0; }
        .notification-items-table td:nth-child(1) {
            position: absolute;
            top: 11px;
            right: 11px;
            z-index: 1;
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            padding: 0 !important;
            color: #1d4ed8;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 9px;
        }
        .notification-items-table td:nth-child(1)::before { display: none; }
        .notification-items-table td:nth-child(2),
        .notification-items-table td:last-child.notification-item-note {
            grid-column: 1 / -1;
            padding-right: 48px !important;
            border-right: 0;
        }
        .notification-items-table td:last-child.notification-item-note { padding-right: 10px !important; }
        .notification-items-table td[data-label="Total"] {
            grid-column: 1 / -1;
            color: #0f766e;
            background: #f0fdfa;
            border-right: 0;
        }
        .notification-items-table td[data-label="Total"] strong { color: #0f766e; font-size: 12px; }
        .notification-items-table td:last-child { border-bottom: 0; }

        .notification-modal-footer {
            flex: 0 0 auto;
            gap: 12px;
            max-height: 42vh;
            padding: 12px 13px calc(12px + env(safe-area-inset-bottom));
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .notification-modal-action-group,
        .notification-modal-action-group.is-secondary {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }
        .notification-modal-action-group.is-secondary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .notification-modal-action {
            justify-content: center;
            width: 100%;
            min-width: 0;
            min-height: 46px;
            padding-inline: 10px;
            text-align: center;
        }
        .notification-modal-action-group.is-secondary .notification-modal-action.is-page { grid-column: 1 / -1; }
    }

    @supports (height: 100dvh) {
        @media (max-width: 575.98px) {
            .notification-modal-content {
                height: 100dvh;
                max-height: 100dvh;
            }
        }
    }

    @media (max-width: 389.98px) {
        .notification-metrics { grid-template-columns: 1fr; }
        .notification-metric { align-items: center; flex-direction: row; min-height: 100px; }
        .notification-command-meta span:last-child { display: none; }
        .notification-modal-tab {
            gap: 4px;
            padding-inline: 5px;
            font-size: 10.5px;
        }
        .notification-items-table tbody tr { grid-template-columns: 1fr; }
        .notification-items-table td,
        .notification-items-table td:nth-child(even) {
            grid-column: 1;
            border-right: 0;
            border-bottom: 1px solid #eef2f7;
        }
        .notification-items-table td:nth-child(2),
        .notification-items-table td:last-child.notification-item-note,
        .notification-items-table td[data-label="Total"] { grid-column: 1; }
        .notification-result-count { padding-inline: 8px; }
        .notification-result-count i,
        .notification-result-count strong { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .notification-page *,
        .notification-page *::before,
        .notification-page *::after {
            scroll-behavior: auto !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>
