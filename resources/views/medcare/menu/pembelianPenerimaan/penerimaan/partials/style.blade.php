<style>
    .purchase-page {
        --purchase-primary: #0f766e;
        --purchase-primary-strong: #0b5f59;
        --purchase-accent: #2563eb;
        --purchase-success: #16a34a;
        --purchase-warning: #d97706;
        --purchase-danger: #dc2626;
        --purchase-info: #0891b2;
        --purchase-surface: #ffffff;
        --purchase-soft: #e8f8f5;
        --purchase-soft-blue: #eef6ff;
        --purchase-soft-yellow: #fff7e8;
        --purchase-soft-red: #fff1f2;
        --purchase-border: #dbe7f5;
        --purchase-text: #172033;
        --purchase-muted: #667085;
        color: var(--purchase-text);
    }

    .purchase-page .page-breadcrumb,
    .purchase-page .breadcrumb {
        margin-bottom: 1rem;
    }

    .purchase-hero {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(300px, .65fr);
        gap: 1rem;
        align-items: stretch;
        padding: 1.5rem;
        border-radius: 10px;
        color: #fff;
        background:
            radial-gradient(circle at 88% 12%, rgba(255, 255, 255, .2), transparent 26%),
            linear-gradient(135deg, rgba(15, 118, 110, .98), rgba(37, 99, 235, .95));
        box-shadow: 0 18px 44px rgba(15, 118, 110, .17);
        overflow: hidden;
    }

    .purchase-hero::after {
        position: absolute;
        right: -72px;
        bottom: -92px;
        width: 230px;
        height: 230px;
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 50%;
        content: "";
    }

    .purchase-hero-copy,
    .purchase-flow {
        position: relative;
        z-index: 1;
    }

    .purchase-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .38rem .7rem;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 999px;
        background: rgba(255, 255, 255, .13);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .purchase-hero h2 {
        max-width: 760px;
        margin: .85rem 0 .45rem;
        color: #fff;
        font-size: clamp(1.45rem, 2.4vw, 2rem);
        font-weight: 800;
        line-height: 1.25;
    }

    .purchase-hero p {
        max-width: 720px;
        margin: 0 0 1rem;
        color: rgba(255, 255, 255, .8);
        line-height: 1.65;
    }

    .purchase-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .purchase-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .purchase-hero-actions .btn-light {
        border: 0;
        color: var(--purchase-primary-strong);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .14);
    }

    .purchase-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .45);
        color: #fff;
    }

    .purchase-flow {
        display: grid;
        align-content: center;
        gap: .6rem;
        padding: .9rem;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 10px;
        background: rgba(255, 255, 255, .1);
        backdrop-filter: blur(8px);
    }

    .purchase-flow-item {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) auto;
        gap: .7rem;
        align-items: center;
        padding: .68rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .11);
    }

    .purchase-flow-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.1rem;
    }

    .purchase-flow-item strong,
    .purchase-flow-item small {
        display: block;
    }

    .purchase-flow-item strong {
        color: #fff;
        font-weight: 800;
    }

    .purchase-flow-item small {
        margin-top: .1rem;
        color: rgba(255, 255, 255, .68);
    }

    .purchase-flow-arrow {
        color: rgba(255, 255, 255, .55);
        font-size: 1.1rem;
    }

    .purchase-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .purchase-stat {
        position: relative;
        display: flex;
        gap: .8rem;
        align-items: center;
        min-width: 0;
        min-height: 104px;
        padding: 1rem;
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        background: var(--purchase-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .045);
        overflow: hidden;
    }

    .purchase-stat::after {
        position: absolute;
        top: 0;
        right: 0;
        width: 72px;
        height: 72px;
        border-radius: 0 0 0 72px;
        background: currentColor;
        content: "";
        opacity: .035;
    }

    .purchase-stat-icon {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        place-items: center;
        border-radius: 10px;
        font-size: 1.35rem;
    }

    .purchase-stat.is-total .purchase-stat-icon {
        color: var(--purchase-accent);
        background: var(--purchase-soft-blue);
    }

    .purchase-stat.is-draft .purchase-stat-icon {
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
    }

    .purchase-stat.is-approved .purchase-stat-icon {
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .purchase-stat.is-value .purchase-stat-icon {
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
    }

    .purchase-stat-copy {
        min-width: 0;
    }

    .purchase-stat strong {
        display: block;
        overflow: hidden;
        color: var(--purchase-text);
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.15;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .purchase-stat span,
    .purchase-stat small {
        display: block;
    }

    .purchase-stat span {
        margin-top: .2rem;
        color: var(--purchase-muted);
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .purchase-stat small {
        margin-top: .15rem;
        color: var(--purchase-muted);
        line-height: 1.35;
    }

    .purchase-table-section {
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        background: var(--purchase-surface);
        box-shadow: 0 14px 38px rgba(15, 23, 42, .055);
        overflow: hidden;
    }

    .receive-insight-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 0 0 1rem;
    }

    .receive-insight-card {
        display: flex;
        gap: .78rem;
        align-items: center;
        min-width: 0;
        padding: .95rem;
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        background: linear-gradient(180deg, #fff, #f8fbff);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
    }

    .receive-insight-icon {
        display: grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        border-radius: 10px;
        font-size: 1.25rem;
    }

    .receive-insight-icon.is-primary {
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
    }

    .receive-insight-icon.is-warning {
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
    }

    .receive-insight-icon.is-danger {
        color: var(--purchase-danger);
        background: var(--purchase-soft-red);
    }

    .receive-insight-card small,
    .receive-insight-card span {
        display: block;
        color: var(--purchase-muted);
    }

    .receive-insight-card small {
        font-size: .73rem;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .receive-insight-card strong {
        display: block;
        margin-top: .12rem;
        color: var(--purchase-text);
        font-size: 1.28rem;
        font-weight: 900;
        line-height: 1.15;
    }

    .receive-insight-card span {
        margin-top: .18rem;
        line-height: 1.35;
    }

    .purchase-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--purchase-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .purchase-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .purchase-table-title-icon {
        display: grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        border-radius: 10px;
        color: var(--purchase-accent);
        background: var(--purchase-soft-blue);
        font-size: 1.25rem;
    }

    .purchase-table-title h5 {
        margin: 0;
        color: var(--purchase-text);
        font-weight: 800;
    }

    .purchase-table-title p {
        margin: .12rem 0 0;
        color: var(--purchase-muted);
    }

    .purchase-table-tools {
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .purchase-search {
        display: flex;
        align-items: center;
        min-width: min(390px, 42vw);
        border: 1px solid var(--purchase-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
        overflow: hidden;
    }

    .purchase-search:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .purchase-search > i {
        padding-left: .85rem;
        color: var(--purchase-muted);
        font-size: 1.05rem;
    }

    .purchase-search input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        padding: .72rem .65rem .72rem .75rem;
        background: transparent;
        color: var(--purchase-text);
    }

    .purchase-search-clear {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        margin-right: .25rem;
        border: 0;
        border-radius: 8px;
        color: var(--purchase-muted);
        background: transparent;
        opacity: 0;
        pointer-events: none;
        transition: opacity .16s ease, background .16s ease;
    }

    .purchase-search.has-value .purchase-search-clear {
        opacity: 1;
        pointer-events: auto;
    }

    .purchase-search-clear:hover {
        color: var(--purchase-danger);
        background: var(--purchase-soft-red);
    }

    .purchase-icon-btn {
        display: inline-grid !important;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px !important;
        padding: 0 !important;
        color: var(--purchase-primary-strong) !important;
        border-color: var(--purchase-border) !important;
        background: #fff !important;
    }

    .purchase-icon-btn.is-loading i {
        animation: purchase-spin .75s linear infinite;
    }

    .purchase-filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .8rem 1rem;
        border-bottom: 1px solid var(--purchase-border);
        background: #fff;
    }

    .purchase-filter-group,
    .purchase-filter-controls,
    .purchase-date-filter,
    .purchase-page-size {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem;
    }

    .purchase-filter-controls {
        justify-content: flex-end;
        margin-left: auto;
    }

    .purchase-filter-label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-right: .2rem;
        color: var(--purchase-muted);
        font-size: .8rem;
        font-weight: 800;
    }

    .purchase-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid var(--purchase-border);
        border-radius: 999px;
        padding: .42rem .72rem;
        color: var(--purchase-muted);
        background: #fff;
        font-size: .78rem;
        font-weight: 800;
        transition: color .16s ease, border-color .16s ease, background .16s ease, transform .16s ease;
    }

    .purchase-filter-chip:hover {
        color: var(--purchase-primary-strong);
        border-color: rgba(15, 118, 110, .35);
        transform: translateY(-1px);
    }

    .purchase-filter-chip.is-active {
        color: #fff;
        border-color: var(--purchase-primary);
        background: var(--purchase-primary);
        box-shadow: 0 7px 18px rgba(15, 118, 110, .16);
    }

    .purchase-filter-chip .purchase-filter-count {
        display: inline-grid;
        min-width: 22px;
        min-height: 22px;
        place-items: center;
        border-radius: 999px;
        padding: 0 .35rem;
        background: rgba(102, 112, 133, .1);
        font-size: .7rem;
    }

    .purchase-filter-chip.is-active .purchase-filter-count {
        background: rgba(255, 255, 255, .18);
    }

    .purchase-date-filter > label,
    .purchase-page-size label {
        margin: 0;
        color: var(--purchase-muted);
        font-size: .8rem;
        font-weight: 700;
    }

    .purchase-date-filter > label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .purchase-date-input {
        display: flex;
        align-items: center;
        width: 245px;
        min-height: 35px;
        border: 1px solid var(--purchase-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
        overflow: hidden;
    }

    .purchase-date-input:focus-within,
    .purchase-date-input.has-value {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .18rem rgba(15, 118, 110, .07);
    }

    .purchase-date-input > i {
        padding-left: .7rem;
        color: var(--purchase-primary-strong);
        font-size: 1rem;
    }

    .purchase-date-input input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        padding: .48rem .55rem;
        color: var(--purchase-text);
        background: transparent;
        font-size: .8rem;
    }

    .purchase-date-input button {
        display: grid;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        place-items: center;
        margin-right: .18rem;
        border: 0;
        border-radius: 7px;
        color: var(--purchase-muted);
        background: transparent;
        opacity: 0;
        pointer-events: none;
        transition: opacity .16s ease, color .16s ease, background .16s ease;
    }

    .purchase-date-input.has-value button {
        opacity: 1;
        pointer-events: auto;
    }

    .purchase-date-input button:hover {
        color: var(--purchase-danger);
        background: var(--purchase-soft-red);
    }

    #purchaseDatePreset,
    #receiveDatePreset {
        width: auto;
        min-width: 138px;
        border-color: var(--purchase-border);
        border-radius: 8px;
    }

    .purchase-page-size select {
        width: auto;
        min-width: 72px;
        border-color: var(--purchase-border);
        border-radius: 8px;
    }

    .purchase-table-wrap {
        padding: 0 1rem 1rem;
    }

    .purchase-table {
        width: 100% !important;
        margin-bottom: .5rem !important;
        color: var(--purchase-text);
    }

    .purchase-table thead th {
        border-bottom: 0 !important;
        padding: .8rem .65rem !important;
        color: var(--purchase-muted);
        background: #f5f8fc;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .purchase-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding: .82rem .65rem !important;
        white-space: nowrap;
    }

    .purchase-table tbody tr {
        transition: background .16s ease, box-shadow .16s ease;
    }

    .purchase-table tbody tr:hover {
        background: #f5fbff;
        box-shadow: inset 3px 0 0 var(--purchase-primary);
    }

    .purchase-row-number {
        display: inline-grid;
        min-width: 30px;
        min-height: 30px;
        place-items: center;
        border-radius: 8px;
        color: var(--purchase-muted);
        background: #f2f5f9;
        font-size: .76rem;
        font-weight: 800;
    }

    .purchase-po-number {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        color: var(--purchase-text);
        font-weight: 800;
    }

    .purchase-po-number i {
        color: var(--purchase-accent);
    }

    .purchase-date {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: var(--purchase-muted);
    }

    .purchase-badge,
    .purchase-status,
    .purchase-approval,
    .purchase-money {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .4rem .62rem;
        font-size: .76rem;
        font-weight: 800;
    }

    .purchase-badge.is-branch {
        color: var(--purchase-accent);
        background: var(--purchase-soft-blue);
    }

    .purchase-badge.is-distributor {
        max-width: 210px;
        overflow: hidden;
        color: var(--purchase-info);
        background: #ecfeff;
        text-overflow: ellipsis;
    }

    .purchase-money {
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .purchase-status.is-approved,
    .purchase-approval.is-approved {
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .purchase-status.is-draft,
    .purchase-approval.is-pending {
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
    }

    .purchase-status.is-rejected {
        color: var(--purchase-danger);
        background: var(--purchase-soft-red);
    }

    .purchase-approval.is-rejected {
        color: var(--purchase-danger);
        background: var(--purchase-soft-red);
    }

    .purchase-status.is-other {
        color: var(--purchase-muted);
        background: #f2f4f7;
    }

    .purchase-note {
        display: block;
        max-width: 210px;
        overflow: hidden;
        color: var(--purchase-muted);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .purchase-user {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    .purchase-user-avatar {
        display: inline-grid;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        place-items: center;
        border-radius: 9px;
        color: #fff;
        background: linear-gradient(135deg, var(--purchase-primary), var(--purchase-accent));
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .purchase-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .purchase-action-group .btn {
        display: inline-grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .purchase-action-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .purchase-action-group .btn-approve-pembelian {
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .purchase-action-group .btn-edit-pembelian {
        color: var(--purchase-accent);
        background: var(--purchase-soft-blue);
    }

    .purchase-action-group .btn-warning {
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
    }

    .purchase-action-group .btn-secondary {
        color: var(--purchase-muted);
        background: #f2f4f7;
    }

    .purchase-action-group .btn-info {
        color: var(--purchase-info);
        background: #ecfeff;
    }

    .purchase-action-group .btn-danger {
        color: var(--purchase-danger);
        background: var(--purchase-soft-red);
    }

    .purchase-table-section .dataTables_filter,
    .purchase-table-section .dataTables_length {
        display: none !important;
    }

    .purchase-table-section .dataTables_wrapper .dataTables_info,
    .purchase-table-section .dataTables_wrapper .dataTables_paginate {
        padding-top: .8rem;
        color: var(--purchase-muted);
        font-size: .82rem;
    }

    .purchase-table-section .dataTables_wrapper .pagination {
        margin-bottom: 0;
    }

    .purchase-table-section .dataTables_wrapper .page-link {
        margin: 0 .12rem;
        border-color: var(--purchase-border);
        border-radius: 8px;
        color: var(--purchase-primary-strong);
    }

    .purchase-table-section .dataTables_wrapper .active .page-link {
        color: #fff;
        border-color: var(--purchase-primary);
        background: var(--purchase-primary);
    }

    .purchase-table-section .dataTables_processing {
        top: 72px !important;
        width: auto !important;
        min-width: 190px;
        margin-left: -95px !important;
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        padding: .8rem 1rem !important;
        color: var(--purchase-primary-strong);
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 14px 34px rgba(15, 23, 42, .12);
        font-weight: 800;
    }

    .purchase-table-section .dataTables_empty {
        height: 160px;
        color: var(--purchase-muted);
        text-align: center;
    }

    .purchase-table-hint {
        display: flex;
        align-items: center;
        gap: .4rem;
        padding: .7rem 1rem;
        border-top: 1px solid var(--purchase-border);
        color: var(--purchase-muted);
        background: #fbfdff;
        font-size: .78rem;
    }

    .purchase-modal .modal-content {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 26px 70px rgba(15, 23, 42, .22);
        overflow: hidden;
    }

    .purchase-modal .receive-modal-dialog {
        max-width: min(1560px, calc(100vw - 1.5rem));
    }

    .purchase-modal .modal-dialog-scrollable .modal-content {
        max-height: calc(100vh - 1.75rem);
    }

    .purchase-modal .modal-header {
        align-items: flex-start;
        gap: .8rem;
        padding: 1rem 1.2rem;
        border-bottom: 0;
        color: #fff;
        background: linear-gradient(135deg, var(--purchase-primary), var(--purchase-accent));
    }

    .purchase-modal .modal-title-wrap {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 0;
    }

    .purchase-modal .modal-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 10px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .purchase-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .purchase-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .72);
    }

    .purchase-modal-header-meta {
        display: inline-flex;
        align-items: center;
        gap: .7rem;
        margin-left: auto;
    }

    .purchase-modal-status {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        padding: .42rem .72rem;
        color: #fff;
        background: rgba(255, 255, 255, .14);
        font-size: .76rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .purchase-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .82;
    }

    .purchase-modal .modal-body {
        max-height: calc(100vh - 150px) !important;
        padding: 1.15rem;
        background: #f7faff;
        overflow-y: auto;
    }

    .purchase-modal-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .receive-form-progress {
        display: grid;
        grid-template-columns: minmax(150px, 1fr) 42px minmax(150px, 1fr) 42px minmax(150px, 1fr);
        align-items: center;
        gap: .35rem;
        margin-bottom: 1rem;
        padding: .85rem;
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .035);
    }

    .receive-progress-step {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
        color: var(--purchase-muted);
    }

    .receive-progress-step > span {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 999px;
        color: var(--purchase-muted);
        background: #f2f4f7;
        font-size: .82rem;
        font-weight: 900;
    }

    .receive-progress-step strong,
    .receive-progress-step small {
        display: block;
    }

    .receive-progress-step strong {
        color: var(--purchase-text);
        font-weight: 900;
        line-height: 1.2;
    }

    .receive-progress-step small {
        margin-top: .08rem;
        line-height: 1.3;
    }

    .receive-progress-step.is-active > span {
        color: #fff;
        background: var(--purchase-accent);
        box-shadow: 0 8px 18px rgba(37, 99, 235, .16);
    }

    .receive-progress-step.is-complete > span {
        color: #fff;
        background: var(--purchase-success);
        box-shadow: 0 8px 18px rgba(22, 163, 74, .16);
    }

    .receive-progress-step.is-warning > span {
        color: #fff;
        background: var(--purchase-warning);
        box-shadow: 0 8px 18px rgba(217, 119, 6, .14);
    }

    .receive-progress-line {
        height: 2px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--purchase-border), #edf2f7);
    }

    .receive-command-center {
        position: sticky;
        top: 0;
        z-index: 6;
        display: grid;
        grid-template-columns: minmax(280px, .95fr) minmax(320px, 1fr) auto;
        gap: .75rem;
        align-items: center;
        margin-bottom: 1rem;
        padding: .85rem;
        border: 1px solid rgba(15, 118, 110, .16);
        border-radius: 10px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 12px 34px rgba(15, 23, 42, .09);
        backdrop-filter: blur(12px);
    }

    .receive-command-status {
        display: flex;
        align-items: center;
        gap: .72rem;
        min-width: 0;
    }

    .receive-command-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 10px;
        color: #fff;
        background: linear-gradient(135deg, var(--purchase-primary), var(--purchase-accent));
        font-size: 1.18rem;
        box-shadow: 0 9px 20px rgba(15, 118, 110, .16);
    }

    .receive-command-status strong,
    .receive-command-status small {
        display: block;
    }

    .receive-command-status strong {
        color: var(--purchase-text);
        font-weight: 900;
        line-height: 1.2;
    }

    .receive-command-status small {
        margin-top: .1rem;
        color: var(--purchase-muted);
        line-height: 1.35;
    }

    .receive-requirements {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .45rem;
        min-width: 0;
    }

    .receive-requirement {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        min-height: 38px;
        border: 1px solid var(--purchase-border);
        border-radius: 8px;
        color: var(--purchase-muted);
        background: #f8fbff;
        font-size: .78rem;
        font-weight: 900;
        transition: color .16s ease, border-color .16s ease, background .16s ease, box-shadow .16s ease;
    }

    .receive-requirement:hover {
        color: var(--purchase-primary-strong);
        border-color: rgba(15, 118, 110, .3);
        background: var(--purchase-soft);
    }

    .receive-requirement.is-active {
        color: var(--purchase-accent);
        border-color: rgba(37, 99, 235, .3);
        background: var(--purchase-soft-blue);
    }

    .receive-requirement.is-warning {
        color: var(--purchase-warning);
        border-color: rgba(217, 119, 6, .28);
        background: var(--purchase-soft-yellow);
    }

    .receive-requirement.is-complete {
        color: var(--purchase-success);
        border-color: rgba(22, 163, 74, .24);
        background: #ecfdf3;
    }

    .receive-command-actions {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .45rem;
    }

    .receive-command-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 8px;
        font-weight: 900;
    }

    .receive-problem-pill,
    .receive-section-status {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .42rem .7rem;
        font-size: .74rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .receive-problem-pill,
    .receive-section-status {
        color: var(--purchase-muted);
        background: #f2f4f7;
    }

    .receive-problem-pill.is-warning,
    .receive-section-status.is-warning {
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
    }

    .receive-problem-pill.is-active,
    .receive-section-status.is-active {
        color: var(--purchase-accent);
        background: var(--purchase-soft-blue);
    }

    .receive-problem-pill.is-complete,
    .receive-section-status.is-complete {
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .receive-cockpit {
        position: relative;
        display: grid;
        grid-template-columns: minmax(260px, .72fr) minmax(0, 1fr);
        gap: .85rem;
        margin-bottom: 1rem;
        padding: .95rem;
        border: 1px solid rgba(15, 118, 110, .16);
        border-radius: 10px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .98), rgba(37, 99, 235, .94)),
            #0f766e;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
        overflow: hidden;
    }

    .receive-cockpit::before {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(255, 255, 255, .13) 1px, transparent 1px),
            linear-gradient(180deg, rgba(255, 255, 255, .09) 1px, transparent 1px);
        background-size: 46px 46px;
        content: "";
        opacity: .16;
        pointer-events: none;
    }

    .receive-cockpit-main,
    .receive-cockpit-grid {
        position: relative;
        z-index: 1;
    }

    .receive-cockpit-main {
        display: grid;
        align-content: center;
        min-width: 0;
        padding: .35rem;
    }

    .receive-cockpit-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        width: max-content;
        max-width: 100%;
        margin-bottom: .35rem;
        padding: .34rem .58rem;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 999px;
        color: rgba(255, 255, 255, .88);
        background: rgba(255, 255, 255, .12);
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .receive-cockpit-main strong {
        display: block;
        overflow: hidden;
        color: #fff;
        font-size: clamp(1.55rem, 2.4vw, 2.25rem);
        font-weight: 900;
        line-height: 1.08;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .receive-cockpit-main small {
        display: block;
        margin-top: .35rem;
        color: rgba(255, 255, 255, .76);
        line-height: 1.4;
    }

    .receive-cockpit-meter {
        height: 9px;
        margin-top: .8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .18);
        overflow: hidden;
    }

    .receive-cockpit-meter span {
        display: block;
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #fef3c7, #bbf7d0);
        transition: width .2s ease;
    }

    .receive-cockpit-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
    }

    .receive-cockpit-card {
        min-width: 0;
        padding: .82rem;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 9px;
        background: rgba(255, 255, 255, .12);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
        transition: background .16s ease, transform .16s ease, border-color .16s ease;
    }

    .receive-cockpit-card:hover {
        border-color: rgba(255, 255, 255, .3);
        background: rgba(255, 255, 255, .17);
        transform: translateY(-1px);
    }

    .receive-cockpit-card span,
    .receive-cockpit-card small,
    .receive-cockpit-card strong {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .receive-cockpit-card span {
        display: flex;
        align-items: center;
        gap: .32rem;
        color: rgba(255, 255, 255, .68);
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .receive-cockpit-card strong {
        margin-top: .38rem;
        color: #fff;
        font-size: 1rem;
        font-weight: 900;
    }

    .receive-cockpit-card small {
        margin-top: .18rem;
        color: rgba(255, 255, 255, .72);
        line-height: 1.35;
    }

    .purchase-overview-item {
        display: flex;
        align-items: center;
        gap: .72rem;
        min-width: 0;
        padding: .86rem;
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .035);
    }

    .purchase-overview-item > span {
        display: grid;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        place-items: center;
        border-radius: 10px;
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
        font-size: 1.12rem;
    }

    .purchase-overview-item strong,
    .purchase-overview-item small {
        display: block;
    }

    .purchase-overview-item strong {
        color: var(--purchase-text);
        font-weight: 800;
    }

    .purchase-overview-item small {
        margin-top: .08rem;
        color: var(--purchase-muted);
        line-height: 1.35;
    }

    .purchase-modal .modal-footer {
        margin-top: 1rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--purchase-border);
    }

    .purchase-modal .modal-content > .modal-footer {
        margin-top: 0;
        padding: .9rem 1.15rem;
        background: #fff;
    }

    .purchase-modal .modal-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .purchase-form-section {
        scroll-margin-top: 92px;
        margin-bottom: 1rem;
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .035);
        overflow: hidden;
    }

    .purchase-form-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .85rem 1rem;
        border-bottom: 1px solid var(--purchase-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .purchase-form-section-title {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .purchase-form-section-title > i {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border-radius: 8px;
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
        font-size: 1.05rem;
    }

    .purchase-form-section-title strong,
    .purchase-form-section-title small {
        display: block;
    }

    .purchase-form-section-title strong {
        color: var(--purchase-text);
        font-weight: 800;
    }

    .purchase-form-section-title small {
        color: var(--purchase-muted);
    }

    .purchase-form-section-body {
        padding: 1rem;
    }

    .purchase-modal .form-label {
        color: var(--purchase-text);
        font-weight: 800;
    }

    .purchase-modal .form-control,
    .purchase-modal .form-select,
    .purchase-modal .input-group-text {
        border-color: var(--purchase-border);
        border-radius: 8px;
    }

    .purchase-modal .form-control:focus,
    .purchase-modal .form-select:focus {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .purchase-input-icon {
        position: relative;
    }

    .purchase-input-icon > i {
        position: absolute;
        top: 50%;
        left: .78rem;
        z-index: 2;
        color: var(--purchase-muted);
        transform: translateY(-50%);
        pointer-events: none;
    }

    .purchase-input-icon .form-control {
        padding-left: 2.25rem;
    }

    .purchase-field-hint {
        display: block;
        margin-top: .32rem;
        color: var(--purchase-muted);
        font-size: .76rem;
        line-height: 1.35;
    }

    .receive-invoice-status-pill {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        flex: 0 0 auto;
        border-radius: 999px;
        padding: .42rem .72rem;
        font-size: .76rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .receive-invoice-status-pill.is-unpaid {
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
    }

    .receive-invoice-status-pill.is-partial {
        color: var(--purchase-accent);
        background: var(--purchase-soft-blue);
    }

    .receive-invoice-status-pill.is-paid {
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .receive-invoice-board {
        display: grid;
        grid-template-columns: minmax(240px, .85fr) minmax(320px, 1fr) auto;
        gap: .8rem;
        align-items: center;
        margin-bottom: 1rem;
        padding: .95rem;
        border: 1px solid rgba(15, 118, 110, .18);
        border-radius: 10px;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .08), rgba(37, 99, 235, .07)),
            #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
    }

    .receive-invoice-main {
        min-width: 0;
    }

    .receive-invoice-main span,
    .receive-payment-summary span,
    .receive-payment-hint {
        display: block;
        color: var(--purchase-muted);
        font-size: .74rem;
        font-weight: 800;
    }

    .receive-invoice-main span,
    .receive-payment-summary span {
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .receive-invoice-main strong {
        display: block;
        margin-top: .12rem;
        color: var(--purchase-primary-strong);
        font-size: 1.55rem;
        font-weight: 900;
        line-height: 1.15;
        white-space: nowrap;
    }

    .receive-invoice-main small {
        display: block;
        margin-top: .2rem;
        color: var(--purchase-muted);
    }

    .receive-payment-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .6rem;
        min-width: 0;
    }

    .receive-payment-summary > div {
        min-width: 0;
        padding-left: .7rem;
        border-left: 2px solid rgba(15, 118, 110, .16);
    }

    .receive-payment-summary strong {
        display: block;
        overflow: hidden;
        margin-top: .16rem;
        color: var(--purchase-text);
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .receive-payment-meter {
        grid-column: 1 / -1;
        height: 9px;
        border-radius: 999px;
        background: #e8eef6;
        overflow: hidden;
    }

    .receive-payment-meter span {
        display: block;
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--purchase-primary), var(--purchase-success));
        transition: width .2s ease;
    }

    .receive-payment-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .45rem;
    }

    .receive-payment-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(15, 118, 110, .14);
        border-radius: 8px;
        color: var(--purchase-primary-strong);
        font-weight: 900;
    }

    .receive-payment-actions .btn:hover {
        border-color: rgba(15, 118, 110, .32);
        background: var(--purchase-soft);
    }

    .receive-payment-actions .btn.is-active {
        border-color: var(--purchase-primary);
        color: #fff;
        background: var(--purchase-primary);
        box-shadow: 0 8px 18px rgba(15, 118, 110, .14);
    }

    .receive-payment-hint {
        grid-column: 1 / -1;
        line-height: 1.35;
    }

    .receive-money-field {
        position: relative;
    }

    .receive-money-field > i {
        position: absolute;
        top: 50%;
        left: .78rem;
        z-index: 2;
        color: var(--purchase-muted);
        font-size: 1rem;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .receive-money-field.is-primary > i {
        color: var(--purchase-primary-strong);
    }

    .receive-money-field .form-control {
        padding-left: 2.25rem;
    }

    .receive-money-field:focus-within > i {
        color: var(--purchase-primary-strong);
    }

    .purchase-modal .invoice-money {
        text-align: right;
        font-weight: 800;
        letter-spacing: 0;
    }

    .purchase-modal .invoice-money[readonly] {
        color: var(--purchase-text);
        background: #f8fbff;
    }

    .receive-invoice-section {
        transition: border-color .18s ease, box-shadow .18s ease, opacity .18s ease;
    }

    .receive-invoice-section.is-locked {
        border-color: rgba(217, 119, 6, .28);
        box-shadow: 0 8px 22px rgba(217, 119, 6, .055);
    }

    .receive-invoice-section.is-locked .receive-invoice-board,
    .receive-invoice-section.is-locked .row.g-3 {
        opacity: .62;
    }

    .receive-invoice-section.is-locked input:not([readonly]),
    .receive-invoice-section.is-locked select,
    .receive-invoice-section.is-locked button {
        cursor: not-allowed;
    }

    .receive-invoice-lock {
        display: flex;
        align-items: center;
        gap: .55rem;
        margin-bottom: .85rem;
        padding: .72rem .85rem;
        border: 1px solid rgba(217, 119, 6, .22);
        border-radius: 8px;
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
        font-weight: 800;
        line-height: 1.35;
    }

    .receive-invoice-lock i {
        font-size: 1.05rem;
    }

    .receive-next-step {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        margin-top: .85rem;
        padding: .85rem;
        border: 1px solid rgba(15, 118, 110, .18);
        border-radius: 8px;
        background: var(--purchase-soft);
    }

    .receive-next-step strong,
    .receive-next-step small {
        display: block;
    }

    .receive-next-step strong {
        color: var(--purchase-primary-strong);
        font-weight: 900;
    }

    .receive-next-step small {
        margin-top: .1rem;
        color: var(--purchase-muted);
    }

    .receive-next-step .btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 8px;
        font-weight: 900;
        white-space: nowrap;
    }

    .purchase-detail-summary {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-bottom: .85rem;
    }

    .purchase-detail-summary span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .42rem .7rem;
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .purchase-modal .detail-item {
        position: relative;
        margin: 0 0 .8rem !important;
        padding: 0 !important;
        border: 1px solid var(--purchase-border) !important;
        border-radius: 10px;
        background: #fbfdff;
        overflow: hidden;
    }

    .purchase-modal .detail-item:last-child {
        margin-bottom: 0 !important;
    }

    .purchase-detail-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .78rem .9rem;
        border-bottom: 1px solid var(--purchase-border);
        background: linear-gradient(180deg, #fff, #f7fbff);
    }

    .purchase-detail-card-head > div {
        display: flex;
        align-items: center;
        gap: .58rem;
        min-width: 0;
    }

    .purchase-detail-card-head strong,
    .purchase-detail-card-head small {
        display: block;
    }

    .purchase-detail-card-head strong {
        color: var(--purchase-text);
        font-weight: 800;
        line-height: 1.2;
    }

    .purchase-detail-card-head small {
        color: var(--purchase-muted);
        line-height: 1.35;
    }

    .purchase-detail-number {
        display: inline-grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 9px;
        color: #fff;
        background: linear-gradient(135deg, var(--purchase-primary), var(--purchase-accent));
        font-size: .78rem;
        font-weight: 900;
    }

    .purchase-detail-card > .row {
        padding: .95rem;
    }

    .purchase-modal .remove-detail {
        border-radius: 8px;
        font-weight: 800;
    }

    .purchase-add-detail {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .purchase-total-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid rgba(22, 163, 74, .2);
        border-radius: 10px;
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .purchase-total-copy {
        min-width: min(100%, 300px);
    }

    .purchase-total-panel span {
        display: block;
        color: #15803d;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .purchase-total-panel small {
        display: block;
        margin-top: .18rem;
        color: #4b7d5b;
    }

    .purchase-total-panel strong {
        font-size: 1.35rem;
        white-space: nowrap;
    }

    .purchase-total-stats {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .45rem;
        margin-left: auto;
    }

    .purchase-total-stats span {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        padding: .4rem .65rem;
        color: #15803d;
        background: rgba(255, 255, 255, .62);
        text-transform: none;
        letter-spacing: 0;
    }

    .purchase-total-stats strong {
        font-size: .78rem;
    }

    .purchase-modal .select2-container {
        width: 100% !important;
        z-index: 1065;
    }

    .purchase-modal .select2-container--open,
    .purchase-modal .select2-dropdown {
        z-index: 1085 !important;
    }

    .purchase-modal .select2-dropdown {
        border-color: rgba(15, 118, 110, .35);
        border-radius: 8px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .16);
        overflow: hidden;
    }

    .purchase-modal .select2-results__option {
        padding: .55rem .75rem;
    }

    .purchase-modal .select2-search--dropdown {
        padding: .55rem;
    }

    .purchase-modal .select2-search--dropdown .select2-search__field {
        border-color: var(--purchase-border);
        border-radius: 8px;
        outline: 0;
        padding: .48rem .6rem;
    }

    .purchase-modal .select2-search--dropdown .select2-search__field:focus {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .16rem rgba(15, 118, 110, .08);
    }

    .purchase-modal .select2-container--default .select2-selection--single {
        min-height: 38px;
        border-color: var(--purchase-border);
        border-radius: 8px;
    }

    .purchase-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }

    .purchase-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .purchase-detail-table thead th {
        color: var(--purchase-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .receive-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }

    .receive-summary-grid > div {
        min-width: 0;
        padding: .8rem;
        border: 1px solid var(--purchase-border);
        border-radius: 8px;
        background: #f8fbff;
    }

    .receive-summary-grid span,
    .receive-qty-stack small {
        display: block;
        color: var(--purchase-muted);
        font-size: .74rem;
        font-weight: 700;
    }

    .receive-summary-grid strong {
        display: block;
        overflow: hidden;
        margin-top: .25rem;
        color: var(--purchase-text);
        font-size: .92rem;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .receive-progress-meter {
        height: 9px;
        margin-top: .85rem;
        border-radius: 999px;
        background: #eef2f7;
        overflow: hidden;
    }

    .receive-progress-meter span {
        display: block;
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--purchase-primary), var(--purchase-success));
        transition: width .2s ease;
    }

    .receive-detail-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: .45rem;
    }

    .receive-detail-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .receive-empty-state {
        display: grid;
        min-height: 180px;
        place-items: center;
        align-content: center;
        gap: .3rem;
        border: 1px dashed var(--purchase-border);
        border-radius: 10px;
        background: #f8fbff;
        color: var(--purchase-muted);
        text-align: center;
    }

    .receive-empty-state i {
        color: var(--purchase-primary);
        font-size: 2.1rem;
    }

    .receive-empty-state strong {
        color: var(--purchase-text);
        font-size: 1rem;
    }

    .receive-detail-live-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
        margin-bottom: .85rem;
    }

    .receive-detail-live-summary > div {
        min-width: 0;
        padding: .78rem .85rem;
        border: 1px solid var(--purchase-border);
        border-radius: 8px;
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .receive-detail-live-summary span,
    .receive-field-note,
    .receive-row-hint {
        display: block;
        color: var(--purchase-muted);
        font-size: .72rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .receive-detail-live-summary span {
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    .receive-detail-live-summary strong {
        display: block;
        overflow: hidden;
        margin-top: .18rem;
        color: var(--purchase-text);
        font-size: .96rem;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .receive-detail-editor .table {
        min-width: 1480px;
    }

    .receive-detail-editor {
        border: 1px solid var(--purchase-border);
        border-radius: 10px;
        background: #fff;
    }

    .receive-detail-editor .purchase-detail-table {
        margin-bottom: 0;
    }

    .receive-detail-editor .purchase-detail-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .receive-detail-row td {
        vertical-align: middle;
        border-color: #eef3f8;
    }

    .receive-detail-row {
        transition: background .16s ease, box-shadow .16s ease;
    }

    .receive-detail-row.is-filled {
        background: #fbfffd;
        box-shadow: inset 3px 0 0 var(--purchase-success);
    }

    .receive-detail-row.is-warning {
        background: #fffaf0;
        box-shadow: inset 3px 0 0 var(--purchase-warning);
    }

    .receive-detail-row.is-warning .receive-qty-control,
    .receive-detail-row.is-warning input[name="no_batch[]"],
    .receive-detail-row.is-warning input[name="expired_date[]"] {
        border-color: rgba(217, 119, 6, .42);
        box-shadow: 0 0 0 .12rem rgba(217, 119, 6, .08);
    }

    .receive-detail-row.is-empty {
        background: #fff;
    }

    .receive-detail-row:hover {
        background: #f8fbff;
    }

    .receive-detail-row:focus-within {
        background: #f5fbff;
        box-shadow: inset 3px 0 0 var(--purchase-accent), 0 0 0 .08rem rgba(37, 99, 235, .08);
    }

    .receive-item-cell {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        min-width: 250px;
    }

    .receive-item-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 10px;
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
        font-size: 1.05rem;
    }

    .receive-item-copy {
        min-width: 0;
    }

    .receive-item-copy strong,
    .receive-item-copy small {
        display: block;
    }

    .receive-item-copy strong {
        color: var(--purchase-text);
        font-weight: 900;
        line-height: 1.2;
    }

    .receive-item-copy small {
        margin-top: .1rem;
    }

    .receive-detail-row .form-control-sm {
        min-width: 96px;
        border-color: var(--purchase-border);
        border-radius: 7px;
    }

    .receive-detail-row .receive-price {
        min-width: 130px;
        text-align: right;
        font-weight: 800;
        letter-spacing: 0;
    }

    .receive-detail-row input[name="no_batch[]"] {
        min-width: 170px;
    }

    .receive-detail-row input[name="expired_date[]"] {
        min-width: 125px;
    }

    .receive-batch-picker {
        display: grid;
        min-width: 230px;
        gap: .38rem;
    }

    .receive-batch-picker .select2-container--default .select2-selection--single {
        min-height: 32px;
        border-color: var(--purchase-border);
        border-radius: 7px;
    }

    .receive-batch-picker .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px;
        font-size: .78rem;
    }

    .receive-batch-picker .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 30px;
    }

    .receive-batch-picker .form-control[readonly] {
        background: #f8fafc;
        color: var(--purchase-muted);
    }

    .receive-batch-mode {
        display: block;
        color: var(--purchase-muted);
        font-size: .69rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .receive-batch-mode.is-existing {
        color: var(--purchase-primary-strong);
    }

    .receive-batch-mode.is-warning {
        color: var(--purchase-warning);
    }

    .receive-qty-control {
        display: flex;
        align-items: center;
        min-width: 132px;
        border: 1px solid var(--purchase-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .receive-qty-control .form-control-sm {
        min-width: 76px;
        border: 0;
        border-radius: 0;
    }

    .receive-qty-control .receive-fill-max {
        align-self: stretch;
        border: 0;
        border-left: 1px solid var(--purchase-border);
        border-radius: 0;
        color: var(--purchase-primary-strong);
        font-size: .72rem;
        font-weight: 900;
    }

    .receive-row-total {
        color: var(--purchase-primary-strong);
        white-space: nowrap;
    }

    .receive-qty-stack strong {
        display: block;
        color: var(--purchase-text);
        font-weight: 800;
    }

    .receive-row-check {
        display: inline-flex;
        align-items: center;
        gap: .32rem;
        border-radius: 999px;
        padding: .35rem .55rem;
        font-size: .72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .receive-row-check.is-empty {
        color: var(--purchase-muted);
        background: #f2f4f7;
    }

    .receive-row-check.is-warning {
        color: var(--purchase-warning);
        background: var(--purchase-soft-yellow);
    }

    .receive-row-check.is-ok {
        color: var(--purchase-success);
        background: #ecfdf3;
    }

    .receive-invoice-header-actions {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .45rem;
    }

    .supplier-compensation-alert {
        overflow: hidden;
        margin-bottom: 1rem;
        border: 1px solid #fcd34d;
        border-radius: 12px;
        background: #fffbeb;
        box-shadow: 0 12px 28px rgba(217, 119, 6, .08);
    }

    .supplier-compensation-alert.is-overdue {
        border-color: #fca5a5;
        background: #fff7f7;
        box-shadow: 0 12px 28px rgba(220, 38, 38, .08);
    }

    .supplier-compensation-alert-header {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: 1rem;
        border-bottom: 1px solid rgba(217, 119, 6, .18);
    }

    .supplier-compensation-alert.is-overdue .supplier-compensation-alert-header {
        border-bottom-color: rgba(220, 38, 38, .16);
    }

    .supplier-compensation-alert-icon {
        display: inline-grid;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 12px;
        color: #a16207;
        background: #fef3c7;
    }

    .supplier-compensation-alert.is-overdue .supplier-compensation-alert-icon {
        color: #b91c1c;
        background: #fee2e2;
    }

    .supplier-compensation-alert-icon i {
        font-size: 1.35rem;
    }

    .supplier-compensation-alert-copy {
        flex: 1 1 auto;
        min-width: 0;
    }

    .supplier-compensation-alert-copy strong {
        display: block;
        color: #78350f;
        font-size: .92rem;
        font-weight: 900;
    }

    .supplier-compensation-alert.is-overdue .supplier-compensation-alert-copy strong {
        color: #991b1b;
    }

    .supplier-compensation-alert-copy p {
        margin-top: .15rem;
        color: #92400e;
        font-size: .78rem;
    }

    .supplier-compensation-alert.is-overdue .supplier-compensation-alert-copy p {
        color: #b91c1c;
    }

    .supplier-compensation-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .7rem;
        padding: .85rem 1rem;
    }

    .supplier-compensation-metrics > div {
        padding: .7rem .8rem;
        border: 1px solid rgba(217, 119, 6, .16);
        border-radius: 9px;
        background: rgba(255, 255, 255, .72);
    }

    .supplier-compensation-metrics span,
    .supplier-compensation-metrics strong {
        display: block;
    }

    .supplier-compensation-metrics span {
        color: #92400e;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .supplier-compensation-metrics strong {
        margin-top: .18rem;
        color: #78350f;
        font-size: 1rem;
        font-weight: 900;
    }

    .supplier-compensation-table {
        border-top: 1px solid rgba(217, 119, 6, .14);
        background: rgba(255, 255, 255, .66);
    }

    .supplier-compensation-table th {
        color: #7c2d12;
        background: rgba(254, 243, 199, .7);
        font-size: .68rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .supplier-compensation-table td {
        color: var(--purchase-text);
        font-size: .77rem;
    }

    .supplier-compensation-status {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        padding: .35rem .55rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .supplier-compensation-status.is-waiting {
        color: #a16207;
        background: #fef3c7;
    }

    .supplier-compensation-status.is-partial {
        color: #0369a1;
        background: #e0f2fe;
    }

    .supplier-compensation-status.is-overdue {
        color: #b91c1c;
        background: #fee2e2;
    }

    .supplier-compensation-more {
        display: block;
        padding: .75rem 1rem;
        color: #92400e;
        border-top: 1px solid rgba(217, 119, 6, .14);
        font-weight: 700;
    }

    .supplier-compensation-apply {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(210px, .42fr);
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: .9rem 1rem;
        border: 1px solid rgba(124, 58, 237, .2);
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(124, 58, 237, .07), rgba(37, 99, 235, .05));
    }

    .supplier-compensation-apply-copy {
        display: flex;
        align-items: center;
        gap: .7rem;
        min-width: 0;
    }

    .supplier-compensation-apply-icon {
        display: inline-grid;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 10px;
        color: #6d28d9;
        background: #ede9fe;
    }

    .supplier-compensation-apply-copy strong,
    .supplier-compensation-apply-copy small {
        display: block;
    }

    .supplier-compensation-apply-copy strong {
        color: #4c1d95;
        font-size: .84rem;
        font-weight: 900;
    }

    .supplier-compensation-apply-copy small {
        margin-top: .15rem;
        color: #6b7280;
        font-size: .72rem;
    }

    .supplier-compensation-switch {
        padding: .55rem .7rem .55rem 2.7rem;
        border: 1px solid rgba(124, 58, 237, .16);
        border-radius: 999px;
        background: rgba(255, 255, 255, .7);
        white-space: nowrap;
    }

    .supplier-compensation-switch .form-check-input:checked {
        border-color: #7c3aed;
        background-color: #7c3aed;
    }

    .supplier-compensation-switch .form-check-label {
        color: #4c1d95;
        font-size: .76rem;
        font-weight: 800;
    }

    .supplier-compensation-amount label,
    .supplier-compensation-amount small {
        display: block;
    }

    .supplier-compensation-amount label {
        margin-bottom: .3rem;
        color: #4c1d95;
        font-size: .7rem;
        font-weight: 900;
    }

    .supplier-compensation-amount small {
        margin-top: .25rem;
        color: #6b7280;
        font-size: .68rem;
    }

    .supplier-compensation-amount .form-control:disabled {
        color: #94a3b8;
        background: #f8fafc;
    }

    /* Clean receiving form */
    #penerimaanModal .receive-modal-dialog {
        max-width: min(1320px, calc(100vw - 2rem));
    }

    #penerimaanModal .modal-content {
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 18px;
        background: #f8fafc;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .2);
    }

    #penerimaanModal .modal-header {
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e8edf3;
        color: var(--purchase-text);
        background: rgba(255, 255, 255, .98);
    }

    #penerimaanModal .modal-title-wrap {
        gap: .7rem;
    }

    #penerimaanModal .modal-title-icon {
        width: 40px;
        height: 40px;
        flex-basis: 40px;
        border-radius: 12px;
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
        font-size: 1.15rem;
    }

    #penerimaanModal .modal-title {
        color: var(--purchase-text);
        font-size: 1.03rem;
        font-weight: 800;
        letter-spacing: -.01em;
    }

    #penerimaanModal .modal-subtitle {
        margin-top: .12rem;
        color: var(--purchase-muted);
        font-size: .78rem;
    }

    #penerimaanModal .purchase-modal-status {
        border-color: #e2e8f0;
        padding: .4rem .68rem;
        color: var(--purchase-primary-strong);
        background: #f8fafc;
        font-size: .72rem;
    }

    #penerimaanModal .btn-close {
        width: .8rem;
        height: .8rem;
        margin-left: .1rem;
        filter: none;
        opacity: .55;
    }

    #penerimaanModal .btn-close:hover {
        opacity: .9;
    }

    #penerimaanModal .modal-body {
        max-height: calc(100vh - 112px) !important;
        padding: 1rem 1.25rem 0;
        background: #f8fafc;
        scrollbar-color: #cbd5e1 transparent;
        scrollbar-width: thin;
    }

    .receive-form-progress {
        grid-template-columns: minmax(135px, 1fr) minmax(24px, .25fr) minmax(155px, 1fr) minmax(24px, .25fr) minmax(135px, 1fr);
        gap: .5rem;
        margin-bottom: .85rem;
        padding: .65rem .85rem;
        border-color: #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .025);
    }

    .receive-progress-step {
        gap: .55rem;
    }

    .receive-progress-step > span {
        width: 30px;
        height: 30px;
        flex-basis: 30px;
        background: #f1f5f9;
        font-size: .74rem;
        box-shadow: none !important;
    }

    .receive-progress-step strong {
        font-size: .79rem;
        font-weight: 800;
    }

    .receive-progress-step small {
        color: #8491a3;
        font-size: .69rem;
    }

    .receive-progress-line {
        background: #e2e8f0;
    }

    #penerimaanModal .purchase-form-section {
        margin-bottom: .85rem;
        border-color: #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #penerimaanModal .purchase-form-section-header {
        min-height: 58px;
        padding: .8rem 1rem;
        border-bottom-color: #edf1f5;
        background: #fff;
    }

    #penerimaanModal .purchase-form-section-title {
        gap: .6rem;
        min-width: 0;
    }

    #penerimaanModal .purchase-form-section-title > i {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 10px;
        font-size: 1rem;
    }

    #penerimaanModal .purchase-form-section-title strong {
        font-size: .88rem;
        font-weight: 800;
    }

    #penerimaanModal .purchase-form-section-title small {
        margin-top: .08rem;
        color: #7b8798;
        font-size: .72rem;
        line-height: 1.35;
    }

    #penerimaanModal .purchase-form-section-body {
        padding: 1rem;
    }

    #penerimaanModal .form-label {
        margin-bottom: .38rem;
        color: #344054;
        font-size: .76rem;
        font-weight: 750;
    }

    #penerimaanModal .form-control,
    #penerimaanModal .form-select,
    #penerimaanModal .input-group-text {
        min-height: 42px;
        border-color: #d8e0ea;
        border-radius: 10px;
        font-size: .82rem;
    }

    #penerimaanModal textarea.form-control {
        min-height: 42px;
        resize: vertical;
    }

    #penerimaanModal .form-control[readonly] {
        color: #475467;
        background: #f8fafc;
    }

    #penerimaanModal .input-group .form-control {
        border-radius: 10px 0 0 10px;
    }

    #penerimaanModal .input-group .input-group-text {
        min-width: 42px;
        justify-content: center;
        border-radius: 0 10px 10px 0;
        color: #64748b;
        background: #f8fafc;
    }

    #penerimaanModal .select2-container--default .select2-selection--single {
        min-height: 42px;
        border-color: #d8e0ea;
        border-radius: 10px;
    }

    #penerimaanModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
    }

    #penerimaanModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }

    #penerimaanModal .purchase-field-hint {
        margin-top: .28rem;
        color: #8491a3;
        font-size: .69rem;
    }

    #receivePoSummary {
        position: relative;
        border-color: #dce8e6;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .045);
    }

    #receivePoSummary::before {
        position: absolute;
        z-index: 1;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
        background: linear-gradient(180deg, #0f766e, #14b8a6);
        content: '';
    }

    #receivePoSummary .purchase-form-section-header {
        min-height: 62px;
        padding: .82rem 1.05rem .78rem 1.15rem;
        border-bottom-color: #edf3f2;
        background: linear-gradient(135deg, #ffffff 0%, #fbfefd 100%);
    }

    #receivePoSummary .purchase-form-section-title > i {
        color: #0f766e;
        background: #e9f7f5;
    }

    #receivePoSummary .purchase-form-section-title strong {
        letter-spacing: -.01em;
    }

    #receivePoSummary .receive-summary-reference {
        display: flex;
        align-items: center;
        gap: .42rem;
        margin-top: .12rem;
    }

    #receivePoSummary .receive-summary-reference span {
        display: inline;
    }

    #receivePoSummary .receive-summary-reference i {
        width: 3px;
        height: 3px;
        flex: 0 0 3px;
        border-radius: 50%;
        background: #a7b5b3;
    }

    #receivePoSummary .purchase-form-section-body {
        padding: .82rem 1rem .9rem 1.15rem;
    }

    .receive-summary-progress,
    .receive-item-count {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .38rem .66rem;
        color: var(--purchase-primary-strong);
        background: var(--purchase-soft);
        font-size: .73rem;
        font-weight: 800;
        white-space: nowrap;
    }

    #receivePoSummary .receive-summary-progress {
        gap: .42rem;
        padding: .32rem .42rem .32rem .64rem;
        border: 1px solid #d6efeb;
        color: #55706c;
        background: #f2fbf9;
        font-size: .66rem;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    #receivePoSummary .receive-summary-progress strong {
        display: inline-grid;
        min-width: 38px;
        min-height: 25px;
        place-items: center;
        border-radius: 999px;
        color: #0b6b63;
        background: #fff;
        box-shadow: 0 1px 4px rgba(15, 118, 110, .1);
        font-size: .72rem;
        letter-spacing: 0;
    }

    #receivePoSummary .receive-summary-grid {
        grid-template-columns: minmax(230px, 1.55fr) minmax(150px, 1fr) minmax(105px, .65fr) minmax(140px, .9fr);
        gap: 0;
        border: 1px solid #e7eeed;
        border-radius: 11px;
        background: #fbfdfd;
        overflow: hidden;
    }

    #receivePoSummary .receive-summary-grid > div {
        min-height: 60px;
        padding: .68rem .9rem;
        border: 0;
        border-left: 1px solid #e7eeed;
        border-radius: 0;
        background: transparent;
    }

    #receivePoSummary .receive-summary-grid > div:first-child {
        border-left: 0;
    }

    #receivePoSummary .receive-summary-label {
        display: block;
        color: #72817f;
        font-size: .64rem;
        font-weight: 750;
        letter-spacing: .045em;
        line-height: 1.25;
        text-transform: uppercase;
    }

    #receivePoSummary .receive-summary-metric > strong {
        display: block;
        margin-top: .26rem;
        color: #142522;
        font-size: .88rem;
        font-weight: 800;
        line-height: 1.3;
    }

    #receivePoSummary .receive-summary-metric--branch > strong {
        white-space: normal;
    }

    #receivePoSummary .receive-summary-quantity {
        display: flex;
        align-items: baseline;
        gap: .32rem;
        white-space: nowrap;
    }

    #receivePoSummary .receive-summary-quantity span {
        display: inline;
        color: inherit;
        font-size: inherit;
        font-weight: inherit;
    }

    #receivePoSummary .receive-summary-quantity-divider {
        color: #a1afad;
        font-weight: 600;
    }

    #receivePoSummary .receive-progress-meter {
        height: 5px;
        margin-top: .72rem;
        background: #edf2f1;
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, .04);
    }

    #receivePoSummary .receive-progress-meter span {
        background: linear-gradient(90deg, #0f766e, #21b9a9);
        box-shadow: 0 0 8px rgba(20, 184, 166, .2);
    }

    .receive-detail-actions .btn {
        min-height: 34px;
        padding: .36rem .62rem;
        border-radius: 9px;
        font-size: .72rem;
        font-weight: 750;
    }

    .receive-detail-live-summary {
        display: flex;
        gap: 0;
        margin-bottom: .75rem;
        border: 1px solid #e7edf3;
        border-radius: 10px;
        background: #fafcfe;
        overflow: hidden;
    }

    .receive-detail-live-summary > div {
        flex: 1 1 0;
        padding: .6rem .8rem;
        border: 0;
        border-left: 1px solid #e7edf3;
        border-radius: 0;
        background: transparent;
    }

    .receive-detail-live-summary > div:first-child {
        border-left: 0;
    }

    .receive-detail-live-summary span {
        font-size: .65rem;
        letter-spacing: .02em;
    }

    .receive-detail-live-summary strong {
        margin-top: .12rem;
        font-size: .82rem;
    }

    .receive-empty-state {
        min-height: 140px;
        border-color: #d8e0ea;
        border-radius: 12px;
        background: #fbfcfe;
    }

    .receive-empty-state i {
        color: #94a3b8;
        font-size: 1.75rem;
    }

    .receive-detail-editor {
        border-color: #e2e8f0;
        border-radius: 12px;
    }

    #penerimaanModal .purchase-detail-table thead th {
        padding-top: .68rem;
        padding-bottom: .68rem;
        border-color: #e7edf3;
        color: #667085;
        background: #f8fafc;
        font-size: .67rem;
        letter-spacing: .025em;
    }

    .receive-detail-row td {
        padding-top: .65rem;
        padding-bottom: .65rem;
    }

    .receive-invoice-board {
        display: flex;
        grid-template-columns: none;
        align-items: center;
        gap: 1rem;
        margin-bottom: .9rem;
        padding: .72rem .85rem;
        border-color: rgba(15, 118, 110, .14);
        border-radius: 10px;
        background: #f0fdfa;
        box-shadow: none;
    }

    .receive-invoice-main strong {
        margin-top: .06rem;
        font-size: 1.25rem;
    }

    .receive-invoice-main small {
        margin-top: .12rem;
        font-size: .7rem;
    }

    .receive-payment-hint {
        margin-left: auto;
        color: #4f6f6a;
        font-weight: 700;
        text-align: right;
    }

    .receive-invoice-lock {
        margin-bottom: .75rem;
        padding: .62rem .75rem;
        border-radius: 9px;
        font-size: .76rem;
    }

    .receive-form-actions {
        position: sticky;
        bottom: 0;
        z-index: 8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin: 1rem -1.25rem 0;
        padding: .85rem 1.25rem;
        border-top: 1px solid #e2e8f0;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 -10px 30px rgba(15, 23, 42, .06);
        backdrop-filter: blur(12px);
    }

    .receive-form-total {
        display: grid;
        grid-template-columns: auto auto;
        column-gap: .65rem;
        align-items: baseline;
    }

    .receive-form-total > span {
        color: #667085;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    .receive-form-total > strong {
        grid-row: span 2;
        color: var(--purchase-primary-strong);
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: -.025em;
        white-space: nowrap;
    }

    .receive-form-total > small {
        color: #8491a3;
        font-size: .7rem;
    }

    .receive-form-buttons {
        display: flex;
        gap: .55rem;
    }

    .receive-form-buttons .btn {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: .38rem;
        border-radius: 10px;
        padding: .58rem 1rem;
        font-size: .8rem;
        font-weight: 800;
    }

    .receive-form-buttons .btn-light {
        border-color: #e2e8f0;
        color: #475467;
        background: #fff;
    }

    .receive-form-buttons .btn-primary {
        border-color: var(--purchase-primary);
        background: var(--purchase-primary);
        box-shadow: 0 8px 18px rgba(15, 118, 110, .16);
    }

    /* Premium posting confirmation */
    .swal2-container.receive-posting-container {
        box-sizing: border-box;
        max-width: 100vw;
        overflow-x: auto !important;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
    }

    .swal2-popup.receive-posting-popup {
        --posting-primary: #0f766e;
        --posting-primary-strong: #0b5f59;
        --posting-blue: #2563eb;
        --posting-text: #172033;
        --posting-muted: #667085;
        --posting-border: #dfe8f1;
        display: flex !important;
        flex-direction: column;
        width: calc(100vw - 2rem) !important;
        min-width: 0;
        max-width: 76rem !important;
        max-height: calc(100vh - 1.5rem);
        max-height: calc(100dvh - 1.5rem);
        padding: 0 !important;
        border: 1px solid rgba(15, 118, 110, .14);
        border-radius: 20px;
        background: #f7fafc;
        box-shadow: 0 32px 80px rgba(15, 23, 42, .24);
        overflow: hidden;
    }

    .receive-posting-title {
        flex: 0 0 auto;
        width: 100%;
        justify-content: flex-start !important;
        margin: 0 !important;
        padding: 1.2rem 4rem 1.15rem 1.5rem !important;
        border-bottom: 1px solid #e6edf4;
        color: #172033 !important;
        background: #fff;
        font-size: 1.14rem !important;
        font-weight: 900 !important;
        letter-spacing: -.015em;
        text-align: left !important;
    }

    .receive-posting-close {
        top: .72rem !important;
        right: .9rem !important;
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        color: #7b8797 !important;
        transition: color .2s ease, background .2s ease, transform .2s ease !important;
    }

    .receive-posting-close:hover {
        color: #172033 !important;
        background: #eef3f7 !important;
        transform: rotate(4deg);
    }

    .receive-posting-html {
        flex: 1 1 auto;
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        max-width: 100%;
        min-height: 0;
        max-height: calc(100vh - 166px);
        margin: 0 !important;
        padding: 0 1.4rem !important;
        color: var(--posting-text) !important;
        overflow-x: auto;
        overflow-y: auto;
        overscroll-behavior: contain;
        touch-action: pan-x pan-y;
        -webkit-overflow-scrolling: touch;
        text-align: left !important;
    }

    .receive-posting-html::-webkit-scrollbar,
    .receive-posting-table-shell::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .receive-posting-html::-webkit-scrollbar-thumb,
    .receive-posting-table-shell::-webkit-scrollbar-thumb {
        border: 2px solid transparent;
        border-radius: 999px;
        background: #b9c6d5;
        background-clip: padding-box;
    }

    .receive-posting-shell {
        display: grid;
        gap: 1rem;
        width: 100%;
        min-width: 0;
        max-width: 100%;
        padding: 1.15rem 0 1.25rem;
    }

    .receive-posting-shell > *,
    .receive-posting-preview,
    .receive-selling-preview,
    .receive-posting-details {
        min-width: 0;
        max-width: 100%;
    }

    .receive-posting-intro {
        position: relative;
        display: flex;
        align-items: center;
        gap: .9rem;
        min-height: 86px;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(15, 118, 110, .16);
        border-radius: 15px;
        background:
            radial-gradient(circle at 92% 0, rgba(37, 99, 235, .12), transparent 34%),
            linear-gradient(135deg, #effbf8, #f4f8ff);
        overflow: hidden;
    }

    .receive-posting-intro::after {
        position: absolute;
        right: -28px;
        bottom: -54px;
        width: 135px;
        height: 135px;
        border: 1px solid rgba(15, 118, 110, .09);
        border-radius: 50%;
        content: "";
    }

    .receive-posting-intro-icon,
    .receive-posting-section-icon,
    .receive-posting-policy-icon {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
    }

    .receive-posting-intro-icon {
        width: 48px;
        height: 48px;
        border: 1px solid rgba(15, 118, 110, .15);
        border-radius: 14px;
        color: var(--posting-primary);
        background: rgba(255, 255, 255, .85);
        box-shadow: 0 8px 22px rgba(15, 118, 110, .1);
        font-size: 1.55rem;
    }

    .receive-posting-intro > div {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    .receive-posting-intro strong {
        display: block;
        color: #133a38;
        font-size: .94rem;
        font-weight: 900;
    }

    .receive-posting-intro p {
        margin: .18rem 0 0;
        color: #607386;
        font-size: .76rem;
        line-height: 1.55;
    }

    .receive-posting-draft-pill,
    .receive-posting-ready,
    .receive-posting-required,
    .receive-posting-payment-amount {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .32rem;
        margin-left: auto;
        border-radius: 999px;
        white-space: nowrap;
    }

    .receive-posting-draft-pill {
        position: relative;
        z-index: 1;
        padding: .42rem .72rem;
        border: 1px solid #f4d49b;
        color: #9a5b08;
        background: #fff8e8;
        font-size: .68rem;
        font-weight: 900;
        text-transform: uppercase;
    }

    .receive-posting-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .65rem;
        color: #98a3b1;
        font-size: .7rem;
    }

    .receive-posting-steps > span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .receive-posting-steps > span::before {
        display: inline-flex;
        width: 22px;
        height: 22px;
        align-items: center;
        justify-content: center;
        border: 1px solid #d9e2ec;
        border-radius: 50%;
        background: #fff;
        content: "";
    }

    .receive-posting-steps > span.is-done::before {
        display: none;
    }

    .receive-posting-steps > span.is-done i {
        display: inline-flex;
        width: 22px;
        height: 22px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        background: var(--posting-primary);
    }

    .receive-posting-steps > span.is-done,
    .receive-posting-steps > span.is-active {
        color: var(--posting-primary-strong);
    }

    .receive-posting-steps > span.is-active::before {
        border: 5px solid #c9f0e8;
        background: var(--posting-primary);
        box-shadow: 0 0 0 3px #effaf8;
    }

    .receive-posting-choice-section,
    .receive-posting-payment,
    .receive-selling-preview {
        padding: 1rem;
        border: 1px solid var(--posting-border);
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 7px 22px rgba(30, 50, 80, .045);
    }

    .receive-posting-section-head {
        display: flex;
        align-items: center;
        gap: .72rem;
        margin-bottom: .85rem;
    }

    .receive-posting-section-icon {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        color: var(--posting-primary);
        background: #e9f8f5;
        font-size: 1.15rem;
    }

    .receive-posting-section-icon.is-money {
        color: #2563eb;
        background: #edf4ff;
    }

    .receive-posting-section-head > div {
        min-width: 0;
    }

    .receive-posting-section-head strong,
    .receive-posting-section-head small {
        display: block;
    }

    .receive-posting-section-head strong {
        color: #253147;
        font-size: .82rem;
        font-weight: 900;
    }

    .receive-posting-section-head small {
        margin-top: .12rem;
        color: #7b8797;
        font-size: .67rem;
    }

    .receive-posting-required {
        padding: .3rem .58rem;
        color: #9a5b08;
        background: #fff5df;
        font-size: .6rem;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .receive-posting-choice-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .receive-posting-choice {
        position: relative;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: .75rem;
        align-items: center;
        min-height: 112px;
        margin: 0;
        padding: .9rem;
        border: 1px solid #dfe7ef;
        border-radius: 13px;
        color: #475467;
        background: #fbfcfd;
        cursor: pointer;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, background .2s ease;
    }

    .receive-posting-choice:hover {
        border-color: #9ecfc8;
        background: #fff;
        box-shadow: 0 9px 24px rgba(15, 118, 110, .08);
        transform: translateY(-2px);
    }

    .receive-posting-choice.is-selected {
        border-color: var(--posting-primary);
        background: linear-gradient(145deg, #f1fcfa, #fff);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .08), 0 10px 24px rgba(15, 118, 110, .08);
    }

    .receive-posting-choice:focus-within {
        outline: 3px solid rgba(37, 99, 235, .15);
        outline-offset: 2px;
    }

    .receive-posting-choice-icon {
        display: inline-flex;
        width: 46px;
        height: 46px;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        font-size: 1.35rem;
    }

    .receive-posting-choice-icon.is-patient {
        color: #0f766e;
        background: #dff6f1;
    }

    .receive-posting-choice-icon.is-pharmacy {
        color: #2563eb;
        background: #e8f1ff;
    }

    .receive-posting-choice-copy,
    .receive-posting-choice-copy > * {
        display: block;
    }

    .receive-posting-choice-copy small {
        margin-bottom: .15rem;
        color: #8994a3;
        font-size: .59rem;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .receive-posting-choice-copy strong {
        margin-bottom: .2rem;
        color: #273349;
        font-size: .85rem;
        font-weight: 900;
    }

    .receive-posting-choice-copy > span {
        color: #6f7d90;
        font-size: .66rem;
        line-height: 1.5;
    }

    .receive-posting-choice-check {
        display: inline-flex;
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border: 1px solid #d7e1ea;
        border-radius: 50%;
        color: transparent;
        background: #fff;
        transition: all .2s ease;
    }

    .receive-posting-choice.is-selected .receive-posting-choice-check {
        border-color: var(--posting-primary);
        color: #fff;
        background: var(--posting-primary);
        box-shadow: 0 4px 10px rgba(15, 118, 110, .25);
    }

    .receive-posting-payment {
        background: linear-gradient(160deg, #fff, #f8fbff);
    }

    .receive-posting-payment-amount {
        padding: .42rem .75rem;
        color: #175cd3;
        background: #eaf2ff;
        font-size: .74rem;
        font-weight: 900;
    }

    .receive-posting-payment-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }

    .receive-posting-field label {
        display: block;
        margin: 0 0 .35rem;
        color: #566176;
        font-size: .66rem;
        font-weight: 800;
    }

    .receive-posting-field label span {
        color: #dc2626;
    }

    .receive-posting-control {
        position: relative;
    }

    .receive-posting-control > i {
        position: absolute;
        top: 50%;
        left: .72rem;
        z-index: 2;
        color: #8491a3;
        font-size: 1rem;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .receive-posting-control .form-control,
    .receive-posting-control .form-select {
        min-height: 42px;
        padding-left: 2.15rem;
        border-color: #dbe4ed;
        border-radius: 10px;
        color: #344054;
        background-color: #fff;
        font-size: .72rem;
    }

    .receive-posting-control .form-control:focus,
    .receive-posting-control .form-select:focus {
        border-color: #74b8af;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .1);
    }

    .receive-posting-payment-note {
        display: flex;
        align-items: center;
        gap: .35rem;
        margin-top: .7rem;
        color: #708095;
        font-size: .64rem;
    }

    .receive-posting-preview {
        position: relative;
        border-radius: 15px;
        transition: opacity .2s ease;
    }

    .receive-posting-preview.is-loading {
        min-height: 220px;
        overflow: hidden;
    }

    .receive-posting-preview.is-loading::before {
        position: absolute;
        inset: 0;
        z-index: 8;
        background: rgba(247, 250, 252, .82);
        backdrop-filter: blur(2px);
        content: "";
    }

    .receive-posting-preview.is-loading::after {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 9;
        padding: .68rem 1rem;
        border: 1px solid #cce7e2;
        border-radius: 999px;
        color: var(--posting-primary-strong);
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .12);
        content: "Memperbarui preview harga…";
        font-size: .7rem;
        font-weight: 900;
        white-space: nowrap;
        transform: translate(-50%, -50%);
    }

    .receive-posting-document-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .85rem;
    }

    .receive-posting-document-head > div > strong,
    .receive-posting-document-head > div > small,
    .receive-posting-eyebrow {
        display: block;
    }

    .receive-posting-eyebrow {
        margin-bottom: .14rem;
        color: var(--posting-primary);
        font-size: .59rem;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .receive-posting-document-head > div > strong {
        color: #253147;
        font-size: .95rem;
        font-weight: 900;
    }

    .receive-posting-document-head > div > small {
        margin-top: .22rem;
        color: #7a8799;
        font-size: .66rem;
    }

    .receive-posting-document-head > div > small i {
        margin-right: .25rem;
    }

    .receive-posting-ready {
        padding: .4rem .67rem;
        color: #08745f;
        background: #e6f8f3;
        font-size: .65rem;
        font-weight: 900;
    }

    .receive-posting-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: .85rem;
        border: 1px solid #e3eaf1;
        border-radius: 12px;
        background: #fbfcfe;
        overflow: hidden;
    }

    .receive-posting-summary article {
        min-width: 0;
        padding: .78rem .85rem;
        border-left: 1px solid #e3eaf1;
    }

    .receive-posting-summary article:first-child {
        border-left: 0;
    }

    .receive-posting-summary article.is-highlight {
        background: linear-gradient(145deg, #eefaf7, #f7fffd);
    }

    .receive-posting-summary span,
    .receive-posting-summary strong,
    .receive-posting-summary small {
        display: block;
    }

    .receive-posting-summary span {
        color: #7b8798;
        font-size: .6rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .receive-posting-summary span i {
        margin-right: .2rem;
        color: #7ba49f;
    }

    .receive-posting-summary strong {
        margin: .24rem 0 .08rem;
        color: #27344a;
        font-size: .84rem;
        font-weight: 900;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .receive-posting-summary article.is-highlight strong {
        color: var(--posting-primary-strong);
    }

    .receive-posting-summary small {
        color: #97a1af;
        font-size: .58rem;
    }

    .receive-posting-policy {
        display: flex;
        gap: .72rem;
        align-items: flex-start;
        margin-bottom: .75rem;
        padding: .75rem .82rem;
        border: 1px solid #d9e8f7;
        border-radius: 12px;
        background: #f4f9ff;
    }

    .receive-posting-policy.is-patient {
        border-color: #cce9e3;
        background: #f0fbf8;
    }

    .receive-posting-policy-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        color: #2563eb;
        background: #e3eeff;
        font-size: 1.05rem;
    }

    .receive-posting-policy.is-patient .receive-posting-policy-icon {
        color: var(--posting-primary);
        background: #dff4ef;
    }

    .receive-posting-policy small,
    .receive-posting-policy strong {
        display: block;
    }

    .receive-posting-policy small {
        color: #7a8798;
        font-size: .58rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .receive-posting-policy strong {
        margin: .05rem 0 .12rem;
        color: #2a3850;
        font-size: .75rem;
        font-weight: 900;
    }

    .receive-posting-policy p {
        margin: 0;
        color: #68778a;
        font-size: .63rem;
        line-height: 1.5;
    }

    .receive-posting-warning {
        display: flex;
        align-items: flex-start;
        gap: .55rem;
        margin-bottom: .75rem;
        padding: .68rem .78rem;
        border: 1px solid #f1d392;
        border-radius: 11px;
        color: #8a570b;
        background: #fff9eb;
    }

    .receive-posting-warning > i {
        margin-top: .03rem;
        font-size: 1.05rem;
    }

    .receive-posting-warning strong,
    .receive-posting-warning span {
        display: block;
    }

    .receive-posting-warning strong {
        font-size: .7rem;
        font-weight: 900;
    }

    .receive-posting-warning span {
        margin-top: .1rem;
        font-size: .62rem;
    }

    .receive-posting-details {
        border: 1px solid #e1e8ef;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }

    .receive-posting-details > summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem .85rem;
        background: #f8fafc;
        cursor: pointer;
        list-style: none;
        user-select: none;
    }

    .receive-posting-details > summary::-webkit-details-marker {
        display: none;
    }

    .receive-posting-details > summary > span:first-child {
        display: flex;
        align-items: center;
        gap: .55rem;
    }

    .receive-posting-details > summary > span:first-child > i {
        color: var(--posting-primary);
        font-size: 1.1rem;
    }

    .receive-posting-details > summary strong,
    .receive-posting-details > summary small {
        display: block;
    }

    .receive-posting-details > summary strong {
        color: #344054;
        font-size: .7rem;
        font-weight: 900;
    }

    .receive-posting-details > summary small {
        margin-top: .08rem;
        color: #8792a2;
        font-size: .58rem;
    }

    .receive-posting-details-meta {
        color: #758296;
        font-size: .62rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .receive-posting-details-meta i {
        display: inline-block;
        margin-left: .2rem;
        transition: transform .2s ease;
    }

    .receive-posting-details[open] .receive-posting-details-meta i {
        transform: rotate(180deg);
    }

    .receive-posting-table-shell {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        max-height: 360px;
        overflow-x: auto;
        overflow-y: auto;
        border-top: 1px solid #e4ebf2;
        overscroll-behavior-x: contain;
        touch-action: pan-x pan-y;
        -webkit-overflow-scrolling: touch;
        scrollbar-gutter: stable;
    }

    .receive-posting-table {
        width: 100%;
        min-width: 1060px;
        border-collapse: separate;
        border-spacing: 0;
        color: #445066;
        font-size: .75rem;
        line-height: 1.45;
    }

    .receive-posting-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: .68rem .7rem;
        border-bottom: 1px solid #dfe7ef;
        color: #68758a;
        background: #f8fafc;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .025em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .receive-posting-table tbody td {
        padding: .72rem .7rem;
        border-bottom: 1px solid #edf1f5;
        vertical-align: top;
    }

    .receive-posting-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .receive-posting-table tbody tr:hover td {
        background: #fbfdfd;
    }

    .receive-posting-table td strong,
    .receive-posting-table td small,
    .receive-posting-table td .badge {
        display: block;
    }

    .receive-posting-table td strong {
        color: #354157;
        font-size: .77rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .receive-posting-table td small {
        margin-top: .12rem;
        color: #8490a1 !important;
        font-size: .67rem;
        line-height: 1.4;
        white-space: nowrap;
    }

    .receive-posting-table td .badge {
        width: fit-content;
        margin: .2rem auto 0;
        border-radius: 999px;
        font-size: .62rem;
    }

    .receive-posting-agreement {
        position: relative;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: .7rem;
        align-items: center;
        margin: 0;
        padding: .82rem .9rem;
        border: 1px solid #dbe4ed;
        border-radius: 13px;
        color: #566176;
        background: #fff;
        cursor: pointer;
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
    }

    .receive-posting-agreement > input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .receive-posting-agreement.is-checked {
        border-color: #76bdb3;
        background: #f2fbf9;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .07);
    }

    .receive-posting-agreement:focus-within {
        outline: 3px solid rgba(37, 99, 235, .14);
        outline-offset: 2px;
    }

    .receive-posting-agreement-box {
        display: inline-flex;
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border: 1px solid #cdd8e3;
        border-radius: 7px;
        color: transparent;
        background: #fff;
        transition: all .2s ease;
    }

    .receive-posting-agreement.is-checked .receive-posting-agreement-box {
        border-color: var(--posting-primary);
        color: #fff;
        background: var(--posting-primary);
    }

    .receive-posting-agreement strong,
    .receive-posting-agreement small {
        display: block;
    }

    .receive-posting-agreement strong {
        color: #354157;
        font-size: .72rem;
        font-weight: 900;
    }

    .receive-posting-agreement small {
        margin-top: .12rem;
        color: #7c899a;
        font-size: .61rem;
        line-height: 1.45;
    }

    .receive-posting-actions {
        position: relative;
        z-index: 3;
        flex: 0 0 auto;
        display: flex !important;
        justify-content: flex-end !important;
        gap: .65rem;
        width: 100%;
        margin: 0 !important;
        padding: .9rem 1.4rem !important;
        border-top: 1px solid #e1e8ef;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 -10px 30px rgba(15, 23, 42, .055);
        backdrop-filter: blur(12px);
    }

    .receive-posting-confirm,
    .receive-posting-cancel {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: .42rem;
        margin: 0 !important;
        border-radius: 10px;
        padding: .62rem 1rem;
        font-size: .74rem;
        font-weight: 900;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .receive-posting-confirm {
        min-width: 188px;
        border: 1px solid var(--posting-primary);
        color: #fff;
        background: linear-gradient(135deg, var(--posting-primary), #0b8278);
        box-shadow: 0 9px 20px rgba(15, 118, 110, .2);
    }

    .receive-posting-confirm:hover:not(:disabled) {
        color: #fff;
        box-shadow: 0 12px 25px rgba(15, 118, 110, .28);
        transform: translateY(-1px);
    }

    .receive-posting-confirm:disabled {
        border-color: #cbd5df;
        color: #8995a5;
        background: #e8edf2;
        box-shadow: none;
        cursor: not-allowed;
        opacity: 1;
    }

    .receive-posting-cancel {
        border: 1px solid #d8e0e8;
        color: #5c6879;
        background: #fff;
    }

    .receive-posting-cancel:hover {
        color: #273349;
        background: #f5f7f9;
        transform: translateY(-1px);
    }

    .receive-posting-validation {
        flex: 0 0 auto;
        align-self: stretch;
        width: auto !important;
        box-sizing: border-box;
        margin: .75rem 1.4rem 0 !important;
        border-radius: 10px !important;
        color: #9b1c1c !important;
        background: #fff0f0 !important;
        font-size: .74rem !important;
    }

    .swal2-popup.receive-posting-status-popup {
        width: min(29rem, calc(100vw - 2rem));
        padding: 1.5rem 1.6rem 1.25rem;
        border: 1px solid #dfe9e7;
        border-radius: 18px;
        box-shadow: 0 28px 65px rgba(15, 23, 42, .22);
    }

    .receive-posting-status-title {
        color: #253147 !important;
        font-size: 1rem !important;
        font-weight: 900 !important;
    }

    .receive-posting-status-html {
        margin: .5rem 0 0 !important;
    }

    .receive-posting-progress {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #667085;
    }

    .receive-posting-progress > span {
        display: inline-flex;
        width: 52px;
        height: 52px;
        align-items: center;
        justify-content: center;
        margin-bottom: .7rem;
        border-radius: 15px;
        color: #0f766e;
        background: #e7f8f4;
        font-size: 1.5rem;
        animation: posting-breathe 1.4s ease-in-out infinite;
    }

    .receive-posting-progress strong,
    .receive-posting-progress small {
        display: block;
    }

    .receive-posting-progress strong {
        color: #475467;
        font-size: .76rem;
        font-weight: 900;
    }

    .receive-posting-progress small {
        margin-top: .2rem;
        color: #8b96a5;
        font-size: .65rem;
    }

    .receive-posting-status-popup .swal2-loader {
        width: 1.7em;
        height: 1.7em;
        margin: .8rem auto 0;
        border-color: #0f766e transparent #0f766e transparent;
    }

    @keyframes posting-breathe {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(15, 118, 110, .12);
            transform: scale(1);
        }

        50% {
            box-shadow: 0 0 0 10px rgba(15, 118, 110, 0);
            transform: scale(1.04);
        }
    }

    @media (max-width: 767.98px) {
        .swal2-container.receive-posting-container {
            padding: .35rem;
        }

        .swal2-container:has(.receive-posting-popup) {
            padding: .35rem;
        }

        .swal2-popup.receive-posting-popup {
            width: calc(100vw - .7rem) !important;
            height: calc(100vh - .7rem);
            height: calc(100dvh - .7rem);
            max-height: calc(100vh - .7rem);
            max-height: calc(100dvh - .7rem);
            border-radius: 15px;
        }

        .receive-posting-close {
            top: .55rem !important;
            right: .55rem !important;
            width: 44px !important;
            height: 44px !important;
        }

        .receive-posting-title {
            min-height: 58px;
            padding: .9rem 3.55rem .85rem .9rem !important;
            font-size: 1rem !important;
            line-height: 1.3 !important;
        }

        .receive-posting-html {
            max-height: none;
            padding: 0 .7rem !important;
        }

        .receive-posting-intro {
            align-items: flex-start;
            min-height: 0;
            padding: .85rem;
        }

        .receive-posting-draft-pill {
            display: none;
        }

        .receive-posting-choice-grid,
        .receive-posting-payment-grid {
            grid-template-columns: 1fr;
        }

        .receive-posting-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .receive-posting-summary article:nth-child(3) {
            border-left: 0;
        }

        .receive-posting-summary article:nth-child(n + 3) {
            border-top: 1px solid #e3eaf1;
        }

        .receive-posting-summary strong {
            overflow: visible;
            overflow-wrap: anywhere;
            text-overflow: clip;
            white-space: normal;
        }

        .receive-posting-control .form-control,
        .receive-posting-control .form-select {
            width: 100%;
            min-width: 0;
            min-height: 44px;
            font-size: 1rem;
        }

        .receive-posting-details > summary > span:first-child {
            min-width: 0;
        }

        .receive-posting-table-shell {
            max-height: none;
            padding: .65rem;
            background: #f5f8fb;
            overflow: visible;
        }

        .receive-posting-table {
            display: block;
            min-width: 0;
        }

        .receive-posting-table thead {
            display: none;
        }

        .receive-posting-table tbody {
            display: grid;
            gap: .65rem;
        }

        .receive-posting-table tbody tr {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            border: 1px solid #dfe7ef;
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 4px 14px rgba(30, 50, 80, .05);
            overflow: hidden;
        }

        .receive-posting-table tbody td {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: flex-start;
            gap: .12rem;
            padding: .62rem .68rem !important;
            border-bottom: 1px solid #edf1f5;
            border-left: 1px solid #edf1f5;
            text-align: left !important;
        }

        .receive-posting-table tbody td:nth-child(even) {
            border-left: 0;
        }

        .receive-posting-table tbody td:first-child {
            grid-column: 1 / -1;
            border-left: 0;
            background: #f9fbfc;
        }

        .receive-posting-table tbody td:last-child {
            grid-column: 1 / -1;
            border-left: 0;
            border-bottom: 0;
            background: #f3fbf9;
        }

        .receive-posting-table tbody td::before {
            display: block;
            margin-bottom: .12rem;
            color: #7b8798;
            content: attr(data-mobile-label);
            font-size: .58rem;
            font-weight: 900;
            letter-spacing: .035em;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .receive-posting-table td strong,
        .receive-posting-table td small,
        .receive-posting-table td span {
            max-width: 100%;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .receive-posting-table td .badge {
            margin-right: 0;
            margin-left: 0;
        }

        .receive-posting-table tbody td.receive-posting-empty {
            grid-column: 1 / -1;
            align-items: center;
            border: 0;
        }

        .receive-posting-table tbody td.receive-posting-empty::before {
            display: none;
        }

        .receive-posting-actions {
            padding: .7rem .7rem max(.7rem, env(safe-area-inset-bottom)) !important;
        }
    }

    @media (max-width: 575.98px) {
        .receive-posting-shell {
            gap: .75rem;
            padding-top: .8rem;
            padding-bottom: .85rem;
        }

        .receive-posting-intro {
            gap: .7rem;
        }

        .receive-posting-intro-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            font-size: 1.3rem;
        }

        .receive-posting-intro p {
            font-size: .7rem;
        }

        .receive-posting-steps {
            justify-content: space-between;
            gap: .25rem;
        }

        .receive-posting-steps > i,
        .receive-posting-steps b {
            display: none;
        }

        .receive-posting-section-head {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .receive-posting-required,
        .receive-posting-payment-amount {
            margin-left: 0;
        }

        .receive-posting-choice {
            min-height: 104px;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: .6rem;
            padding: .78rem;
        }

        .receive-posting-choice-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            font-size: 1.2rem;
        }

        .receive-posting-choice-copy > span {
            font-size: .7rem;
        }

        .receive-posting-document-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .receive-posting-ready {
            margin-left: 0;
        }

        .receive-posting-details > summary {
            gap: .55rem;
            padding: .7rem;
        }

        .receive-posting-details > summary small {
            display: none;
        }

        .receive-posting-details-meta {
            font-size: .58rem;
        }

        .receive-posting-agreement {
            align-items: flex-start;
            padding: .75rem;
        }

        .receive-posting-agreement-box {
            width: 26px;
            height: 26px;
        }

        .receive-posting-actions {
            display: grid !important;
            grid-template-columns: minmax(0, .75fr) minmax(0, 1.25fr);
        }

        .receive-posting-confirm,
        .receive-posting-cancel {
            width: 100%;
            min-width: 0;
            min-height: 46px;
            padding-inline: .55rem;
        }
    }

    @media (max-width: 379.98px) {
        .receive-posting-actions {
            grid-template-columns: 1fr;
        }

        .receive-posting-cancel {
            order: 2;
        }

        .receive-posting-confirm {
            order: 1;
        }

        .receive-posting-table tbody tr {
            grid-template-columns: 1fr;
        }

        .receive-posting-table tbody td,
        .receive-posting-table tbody td:nth-child(even),
        .receive-posting-table tbody td:last-child {
            grid-column: auto;
            border-bottom: 1px solid #edf1f5;
            border-left: 0;
        }

        .receive-posting-table tbody td:last-child {
            border-bottom: 0;
        }
    }

    @keyframes purchase-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 1199.98px) {
        .purchase-stats-grid,
        .receive-insight-strip,
        .receive-detail-live-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .purchase-hero {
            grid-template-columns: 1fr;
        }

        .purchase-modal-overview {
            grid-template-columns: 1fr;
        }

        .receive-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .receive-invoice-board {
            grid-template-columns: 1fr;
        }

        .supplier-compensation-apply {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .supplier-compensation-switch {
            width: max-content;
        }

        .receive-payment-actions {
            justify-content: flex-start;
        }

        .receive-form-progress {
            grid-template-columns: 1fr;
        }

        .receive-command-center {
            grid-template-columns: 1fr;
        }

        .receive-cockpit {
            grid-template-columns: 1fr;
        }

        .receive-command-actions {
            justify-content: flex-start;
        }

        .receive-progress-line {
            display: none;
        }

        .purchase-table-toolbar,
        .purchase-table-tools {
            align-items: stretch;
            flex-direction: column;
        }

        .purchase-search {
            min-width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        .purchase-filter-bar,
        .purchase-total-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .purchase-total-stats {
            justify-content: flex-start;
            margin-left: 0;
        }

        .purchase-filter-group,
        .purchase-filter-controls {
            width: 100%;
        }

        .purchase-filter-controls {
            align-items: stretch;
            justify-content: flex-start;
            margin-left: 0;
        }

        .purchase-filter-label {
            width: 100%;
            margin-bottom: .15rem;
        }

        .purchase-date-filter {
            width: 100%;
        }

        .purchase-date-filter > label {
            width: 100%;
        }

        .purchase-date-input {
            width: min(100%, 320px);
        }

        .purchase-page-size {
            justify-content: space-between;
        }

        .supplier-compensation-alert-header {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .supplier-compensation-alert-header .btn {
            width: 100%;
            justify-content: center;
        }

        .supplier-compensation-metrics {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .purchase-hero {
            padding: 1rem;
        }

        .purchase-stats-grid,
        .receive-insight-strip {
            grid-template-columns: 1fr;
        }

        .receive-summary-grid {
            grid-template-columns: 1fr;
        }

        .receive-payment-summary {
            grid-template-columns: 1fr;
        }

        .receive-requirements,
        .receive-cockpit-grid,
        .receive-detail-live-summary {
            grid-template-columns: 1fr;
        }

        .receive-payment-summary > div {
            padding-left: 0;
            border-left: 0;
        }

        .purchase-table-wrap {
            padding: 0 .75rem .75rem;
        }

        .purchase-modal .modal-body {
            padding: .85rem;
        }

        .purchase-modal .modal-header,
        .purchase-modal-header-meta,
        .purchase-detail-card-head {
            align-items: flex-start;
        }

        .purchase-modal .modal-header {
            flex-direction: column;
        }

        .purchase-modal-header-meta {
            width: 100%;
            justify-content: space-between;
            margin-left: 0;
        }

        .receive-invoice-status-pill {
            width: 100%;
            justify-content: center;
        }

        .receive-invoice-header-actions,
        .receive-detail-actions,
        .receive-command-actions {
            width: 100%;
        }

        .receive-command-actions .btn,
        .receive-problem-pill,
        .receive-section-status,
        .receive-invoice-status-pill {
            justify-content: center;
        }

        .receive-next-step {
            align-items: stretch;
            flex-direction: column;
        }

        .receive-next-step .btn {
            justify-content: center;
        }

        .purchase-detail-card-head {
            flex-direction: column;
        }
    }

    @media (max-width: 991.98px) {
        .receive-form-progress {
            grid-template-columns: minmax(120px, 1fr) 24px minmax(140px, 1fr) 24px minmax(120px, 1fr);
        }

        .receive-progress-line {
            display: block;
        }

        #penerimaanModal .purchase-form-section-header {
            flex-wrap: wrap;
        }

        .receive-invoice-board {
            display: flex;
        }
    }

    @media (max-width: 767.98px) {
        #penerimaanModal .receive-modal-dialog {
            max-width: calc(100vw - 1rem);
            margin: .5rem auto;
        }

        #penerimaanModal .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 1rem);
        }

        #penerimaanModal .modal-body {
            padding: .75rem .75rem 0;
        }

        .receive-form-actions {
            margin: .75rem -.75rem 0;
            padding: .75rem;
        }

        .receive-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: .75rem;
        }

        .receive-summary-grid > div:nth-child(3) {
            padding-left: 0;
            border-left: 0;
        }

        .receive-detail-live-summary {
            flex-wrap: wrap;
        }

        .receive-detail-live-summary > div {
            flex-basis: 50%;
        }

        .receive-detail-live-summary > div:nth-child(3) {
            border-left: 0;
            border-top: 1px solid #e7edf3;
        }

        .receive-detail-live-summary > div:nth-child(4) {
            border-top: 1px solid #e7edf3;
        }

        .receive-invoice-board {
            align-items: flex-start;
            flex-direction: column;
            gap: .35rem;
        }

        .receive-payment-hint {
            margin-left: 0;
            text-align: left;
        }
    }

    @media (max-width: 575.98px) {
        #penerimaanModal .modal-header {
            align-items: center;
            flex-direction: row;
            padding: .85rem 1rem;
        }

        #penerimaanModal .purchase-modal-header-meta {
            width: auto;
            margin-left: auto;
        }

        #penerimaanModal .purchase-modal-status,
        #penerimaanModal .modal-subtitle,
        .receive-progress-step small {
            display: none;
        }

        .receive-form-progress {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .35rem;
            padding: .55rem;
        }

        .receive-progress-line {
            display: none;
        }

        .receive-progress-step {
            justify-content: center;
            gap: .35rem;
        }

        .receive-progress-step > span {
            width: 26px;
            height: 26px;
            flex-basis: 26px;
        }

        .receive-progress-step strong {
            font-size: .7rem;
        }

        .receive-detail-actions {
            justify-content: flex-start;
        }

        .receive-form-actions {
            align-items: stretch;
            flex-direction: column;
            gap: .65rem;
        }

        .receive-form-total {
            grid-template-columns: 1fr auto;
        }

        .receive-form-buttons .btn {
            flex: 1 1 0;
        }
    }

    @media (max-width: 767.98px) {
        #receivePoSummary .receive-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 0;
        }

        #receivePoSummary .receive-summary-grid > div:nth-child(3) {
            border-left: 0;
        }

        #receivePoSummary .receive-summary-grid > div:nth-child(n + 3) {
            border-top: 1px solid #e7eeed;
        }
    }

    @media (max-width: 575.98px) {
        #receivePoSummary .purchase-form-section-header {
            padding-right: .8rem;
            padding-left: .9rem;
        }

        #receivePoSummary .purchase-form-section-body {
            padding: .75rem .8rem .82rem .9rem;
        }

        #receivePoSummary .receive-summary-progress > span {
            display: none;
        }

        #receivePoSummary .receive-summary-progress {
            padding: .28rem;
        }

        #receivePoSummary .receive-summary-grid {
            grid-template-columns: 1fr;
        }

        #receivePoSummary .receive-summary-grid > div {
            min-height: 56px;
            border-top: 1px solid #e7eeed;
            border-left: 0;
        }

        #receivePoSummary .receive-summary-grid > div:first-child {
            border-top: 0;
        }
    }

    /* Mobile receiving workspace: turn dense tables into touch-friendly cards. */
    @media (max-width: 767.98px) {
        .purchase-page {
            width: 100%;
            min-width: 0;
            overflow-x: clip;
        }

        .purchase-page .page-breadcrumb,
        .purchase-page .breadcrumb {
            margin-bottom: .65rem;
            font-size: .72rem;
        }

        .purchase-page .btn,
        .purchase-page button,
        .purchase-modal .btn,
        .purchase-modal button {
            min-height: 44px;
        }

        .purchase-hero {
            gap: .75rem;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(15, 118, 110, .16);
        }

        .purchase-hero h2 {
            margin-top: .7rem;
            font-size: clamp(1.18rem, 6vw, 1.5rem);
            line-height: 1.3;
        }

        .purchase-hero p {
            margin-bottom: .8rem;
            font-size: .8rem;
            line-height: 1.5;
        }

        .purchase-hero-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
        }

        .purchase-hero-actions .btn {
            justify-content: center;
            min-width: 0;
            padding-inline: .55rem;
            font-size: .75rem;
        }

        .purchase-flow {
            display: flex;
            align-items: stretch;
            gap: .5rem;
            padding: .55rem;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scroll-snap-type: inline mandatory;
            scrollbar-width: none;
        }

        .purchase-flow::-webkit-scrollbar,
        .purchase-filter-group::-webkit-scrollbar,
        .receive-insight-strip::-webkit-scrollbar {
            display: none;
        }

        .purchase-flow-item {
            flex: 0 0 min(245px, calc(100vw - 3rem));
            min-width: 0;
            scroll-snap-align: start;
        }

        .purchase-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
            margin: .75rem 0;
        }

        .purchase-stat {
            min-height: 88px;
            gap: .55rem;
            padding: .72rem;
            border-radius: 12px;
        }

        .purchase-stat-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            font-size: 1.05rem;
        }

        .purchase-stat-copy strong {
            font-size: 1.05rem;
        }

        .purchase-stat span {
            font-size: .63rem;
            letter-spacing: .02em;
        }

        .purchase-stat small {
            display: none;
        }

        .receive-insight-strip {
            display: flex;
            gap: .55rem;
            margin-bottom: .75rem;
            padding-bottom: .1rem;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scroll-snap-type: inline mandatory;
            scrollbar-width: none;
        }

        .receive-insight-card {
            flex: 0 0 min(78vw, 290px);
            min-height: 86px;
            padding: .75rem;
            border-radius: 12px;
            scroll-snap-align: start;
        }

        .purchase-table-section {
            border-radius: 14px;
        }

        .purchase-table-toolbar {
            gap: .75rem;
            padding: .8rem;
        }

        .purchase-table-title {
            align-items: flex-start;
        }

        .purchase-table-title-icon {
            width: 40px;
            height: 40px;
            flex-basis: 40px;
        }

        .purchase-table-title h5 {
            font-size: .95rem;
        }

        .purchase-table-title p {
            font-size: .72rem;
            line-height: 1.4;
        }

        .purchase-table-tools {
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr);
            gap: .5rem;
            width: 100%;
        }

        .purchase-search {
            grid-column: 1 / -1;
            min-width: 0;
            min-height: 46px;
        }

        .purchase-search input {
            font-size: 16px;
        }

        #refreshReceiveTable {
            width: 44px;
            height: 44px;
        }

        #openPenerimaanModalToolbar {
            width: 100%;
            justify-content: center;
        }

        .purchase-filter-bar {
            gap: .7rem;
            padding: .75rem .8rem;
        }

        .purchase-filter-group {
            flex-wrap: nowrap;
            gap: .4rem;
            padding-bottom: .15rem;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scrollbar-width: none;
        }

        .purchase-filter-label {
            display: none;
        }

        .purchase-filter-chip {
            flex: 0 0 auto;
            min-height: 40px !important;
            padding-inline: .7rem;
        }

        .purchase-filter-controls {
            display: grid;
            gap: .65rem;
        }

        .purchase-date-filter {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(118px, .48fr);
            gap: .45rem;
        }

        .purchase-date-filter > label {
            grid-column: 1 / -1;
        }

        .purchase-date-input,
        #receiveDatePreset {
            width: 100%;
            max-width: none;
            min-height: 44px;
        }

        .purchase-date-input input,
        #receiveDatePreset,
        #receivePageLength {
            font-size: 16px;
        }

        .purchase-page-size {
            width: 100%;
            justify-content: space-between;
        }

        .purchase-page-size select {
            min-height: 44px;
        }

        .purchase-table-wrap {
            padding: .7rem;
            overflow: visible;
        }

        #tablePenerimaan,
        #tablePenerimaan tbody {
            display: block;
            width: 100% !important;
            min-width: 0;
        }

        #tablePenerimaan thead,
        #tablePenerimaan tbody tr.child {
            display: none !important;
        }

        #tablePenerimaan tbody {
            display: grid;
            gap: .7rem;
        }

        #tablePenerimaan tbody tr:not(.child) {
            display: block;
            width: 100%;
            border: 1px solid #dfe8f2;
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 7px 20px rgba(15, 23, 42, .055);
            overflow: hidden;
        }

        #tablePenerimaan tbody tr:not(.child):hover {
            background: #fff;
            box-shadow: 0 9px 24px rgba(15, 23, 42, .08);
        }

        #tablePenerimaan tbody tr:not(.child) > td,
        #tablePenerimaan.dataTable tbody tr:not(.child) > td.dtr-hidden {
            display: grid !important;
            grid-template-columns: minmax(88px, .42fr) minmax(0, 1fr);
            gap: .65rem;
            align-items: center;
            min-height: 43px;
            padding: .52rem .72rem !important;
            border: 0;
            border-top: 1px solid #edf2f7;
            background: transparent;
            text-align: left !important;
            white-space: normal;
        }

        #tablePenerimaan tbody tr:not(.child) > td::before {
            align-self: start;
            color: #7b8798;
            content: attr(data-mobile-label);
            font-size: .65rem;
            font-weight: 850;
            grid-column: 1;
            grid-row: 1;
            letter-spacing: .035em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        #tablePenerimaan tbody tr:not(.child) > td > * {
            grid-column: 2;
        }

        #tablePenerimaan tbody tr:not(.child) > td:first-child {
            display: none !important;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(2) {
            border-top: 0;
            background: #f8fbfd;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(3) {
            min-height: 52px;
            color: var(--purchase-primary-strong);
            background: #f0fdfa;
        }

        #tablePenerimaan .purchase-badge.is-distributor,
        #tablePenerimaan .purchase-note,
        #tablePenerimaan .purchase-po-number {
            max-width: 100%;
            min-width: 0;
            white-space: normal;
            word-break: break-word;
        }

        #tablePenerimaan .purchase-action-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(44px, 1fr));
            gap: .45rem;
            width: 100%;
        }

        #tablePenerimaan .purchase-action-group .btn {
            width: 100%;
            height: 44px;
        }

        #tablePenerimaan td.dataTables_empty {
            display: block !important;
            min-height: 120px;
            padding: 2.5rem 1rem !important;
            border: 0;
            text-align: center !important;
        }

        #tablePenerimaan td.dataTables_empty::before {
            display: none;
        }

        .purchase-table-section .dataTables_wrapper .dataTables_info,
        .purchase-table-section .dataTables_wrapper .dataTables_paginate {
            float: none;
            width: 100%;
            padding-top: .65rem;
            text-align: center;
        }

        .purchase-table-section .dataTables_wrapper .pagination {
            flex-wrap: wrap;
            justify-content: center;
            gap: .2rem;
        }

        .purchase-table-section .dataTables_wrapper .page-link {
            display: grid;
            min-width: 42px;
            min-height: 42px;
            place-items: center;
            margin: 0;
        }

        .purchase-table-hint {
            align-items: flex-start;
            padding: .72rem .8rem;
            font-size: .7rem;
            line-height: 1.45;
        }

        .purchase-modal {
            padding: 0 !important;
        }

        .purchase-modal .modal-dialog,
        #penerimaanModal .receive-modal-dialog {
            width: 100%;
            max-width: none !important;
            height: 100%;
            min-height: 100%;
            margin: 0 auto;
        }

        .purchase-modal .modal-dialog-scrollable .modal-content {
            width: 100%;
            height: 100dvh;
            max-height: 100dvh !important;
            border-radius: 0;
        }

        .purchase-modal .modal-header {
            flex: 0 0 auto;
            padding: calc(.75rem + env(safe-area-inset-top)) .85rem .75rem;
        }

        .purchase-modal .modal-title-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
        }

        .purchase-modal .modal-title {
            font-size: .94rem;
        }

        .purchase-modal .modal-body,
        #penerimaanModal .modal-body {
            padding: .7rem .7rem 0;
            overscroll-behavior-y: contain;
        }

        #penerimaanModal .form-control,
        #penerimaanModal .form-select,
        #penerimaanModal .select2-selection--single,
        #penerimaanModalDetail .form-control {
            min-height: 46px;
            font-size: 16px;
        }

        #penerimaanModal .purchase-form-section,
        #penerimaanModalDetail .purchase-form-section {
            margin-bottom: .7rem;
            border-radius: 12px;
        }

        #penerimaanModal .purchase-form-section-header,
        #penerimaanModalDetail .purchase-form-section-header {
            gap: .65rem;
            padding: .72rem .75rem;
        }

        #penerimaanModal .purchase-form-section-body,
        #penerimaanModalDetail .purchase-form-section-body {
            padding: .75rem;
        }

        #penerimaanModal .purchase-form-section-title,
        #penerimaanModalDetail .purchase-form-section-title {
            min-width: 0;
        }

        #penerimaanModal .purchase-form-section-title > i,
        #penerimaanModalDetail .purchase-form-section-title > i {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
        }

        .receive-detail-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .45rem;
            width: 100%;
        }

        .receive-detail-actions .receive-item-count {
            grid-column: 1 / -1;
            width: fit-content;
        }

        .receive-detail-actions .btn {
            justify-content: center;
            min-height: 44px;
            padding-inline: .45rem;
            white-space: normal;
        }

        .receive-detail-live-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .receive-detail-live-summary > div,
        .receive-detail-live-summary > div:nth-child(3) {
            flex-basis: auto;
            border-top: 0;
            border-left: 1px solid #e7edf3;
        }

        .receive-detail-live-summary > div:nth-child(odd) {
            border-left: 0;
        }

        .receive-detail-live-summary > div:nth-child(n + 3) {
            border-top: 1px solid #e7edf3;
        }

        .receive-detail-editor,
        #penerimaanModalDetail .table-responsive,
        .supplier-compensation-alert .table-responsive {
            overflow: visible;
        }

        .receive-detail-editor {
            border: 0;
            background: transparent;
        }

        .receive-detail-editor .table,
        #detailReceiveTable,
        .supplier-compensation-table,
        .receive-detail-editor tbody,
        #detailReceiveTable tbody,
        .supplier-compensation-table tbody {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .receive-detail-editor thead,
        #detailReceiveTable thead,
        .supplier-compensation-table thead {
            display: none;
        }

        .receive-detail-editor tbody,
        #detailReceiveTable tbody,
        .supplier-compensation-table tbody {
            display: grid;
            gap: .7rem;
        }

        .receive-detail-row,
        #detailReceiveTable tbody tr,
        .supplier-compensation-table tbody tr {
            display: block;
            width: 100%;
            border: 1px solid #dfe7ef;
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .05);
            overflow: hidden;
        }

        .receive-detail-row.is-filled,
        .receive-detail-row.is-warning,
        .receive-detail-row:focus-within {
            box-shadow: inset 3px 0 0 var(--purchase-primary), 0 6px 18px rgba(15, 23, 42, .05);
        }

        .receive-detail-row > td,
        #detailReceiveTable tbody td,
        .supplier-compensation-table tbody td {
            display: grid;
            grid-template-columns: minmax(88px, .4fr) minmax(0, 1fr);
            gap: .65rem;
            align-items: center;
            width: 100%;
            padding: .62rem .7rem !important;
            border: 0;
            border-top: 1px solid #edf2f7;
            background: transparent;
            text-align: left !important;
            white-space: normal;
        }

        .receive-detail-row > td::before,
        #detailReceiveTable tbody td::before,
        .supplier-compensation-table tbody td::before {
            align-self: start;
            color: #7b8798;
            content: attr(data-mobile-label);
            font-size: .64rem;
            font-weight: 850;
            grid-column: 1;
            grid-row: 1;
            letter-spacing: .035em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .receive-detail-row > td > *,
        #detailReceiveTable tbody td > *,
        .supplier-compensation-table tbody td > * {
            grid-column: 2;
        }

        .receive-detail-row > td:first-child,
        #detailReceiveTable tbody td:first-child,
        .supplier-compensation-table tbody td:first-child {
            border-top: 0;
            background: #f8fbfd;
        }

        .receive-detail-row > td:first-child {
            grid-template-columns: 1fr;
        }

        .receive-detail-row > td:first-child::before {
            grid-column: 1;
            grid-row: auto;
            margin-bottom: -.15rem;
        }

        .receive-detail-row > td:first-child > * {
            grid-column: 1;
        }

        .receive-item-cell,
        .receive-detail-row .form-control-sm,
        .receive-detail-row .receive-price,
        .receive-detail-row input[name="no_batch[]"],
        .receive-detail-row input[name="expired_date[]"],
        .receive-batch-picker,
        .receive-batch-picker .select2-container,
        .receive-qty-control {
            width: 100% !important;
            min-width: 0;
        }

        .receive-detail-row .form-control-sm,
        .receive-detail-row .form-select-sm {
            min-height: 44px;
            font-size: 16px;
        }

        .receive-qty-control .receive-fill-max {
            min-width: 58px;
            min-height: 44px;
        }

        .receive-row-check {
            justify-self: start;
        }

        .supplier-compensation-alert {
            padding: .75rem;
        }

        .supplier-compensation-table tbody td strong,
        .supplier-compensation-table tbody td small {
            white-space: normal;
        }

        .receive-payment-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .4rem;
        }

        .receive-payment-actions .btn {
            justify-content: center;
            padding-inline: .35rem;
            white-space: normal;
        }

        .receive-form-actions {
            bottom: 0;
            gap: .6rem;
            margin: .75rem -.7rem 0;
            padding: .7rem .7rem calc(.7rem + env(safe-area-inset-bottom));
        }

        .receive-form-buttons {
            display: grid;
            grid-template-columns: minmax(0, .72fr) minmax(0, 1.28fr);
            width: 100%;
        }

        .receive-form-buttons .btn {
            width: 100%;
            min-height: 46px;
            justify-content: center;
        }

        #penerimaanModalDetail .purchase-total-panel {
            gap: .5rem;
            padding: .8rem;
        }

        #penerimaanModalDetail .purchase-total-panel strong {
            font-size: 1.05rem;
            text-align: right;
        }

        #penerimaanModalDetail .modal-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
            padding: .7rem .7rem calc(.7rem + env(safe-area-inset-bottom));
        }

        #penerimaanModalDetail .modal-footer .btn {
            width: 100%;
            min-width: 0;
            min-height: 46px;
            justify-content: center;
            padding-inline: .45rem !important;
        }
    }

    @media (max-width: 575.98px) {
        .purchase-hero {
            padding: .85rem;
        }

        .purchase-kicker {
            font-size: .65rem;
        }

        .purchase-stat.is-value .purchase-stat-copy strong {
            font-size: .9rem;
        }

        #penerimaanModal .modal-header {
            padding-top: calc(.75rem + env(safe-area-inset-top));
        }

        .receive-form-progress {
            position: sticky;
            z-index: 7;
            top: -.7rem;
            margin: -.7rem -.7rem .7rem;
            padding: .55rem .7rem;
            border-radius: 0;
            background: rgba(255, 255, 255, .97);
            box-shadow: 0 5px 16px rgba(15, 23, 42, .06);
            backdrop-filter: blur(10px);
        }

        .receive-progress-step {
            min-width: 0;
        }

        .receive-progress-step strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .receive-form-total > strong {
            font-size: 1.08rem;
        }
    }

    /* Mobile compact mode: keep the first screen focused and reveal secondary data on demand. */
    .receive-mobile-filter-toggle,
    .receive-mobile-more,
    .receive-mobile-section-toggle {
        display: none !important;
    }

    @media (max-width: 767.98px) {
        .purchase-page .page-breadcrumb {
            display: none;
        }

        .purchase-hero {
            padding: .8rem .85rem;
        }

        .purchase-hero h2 {
            margin: .52rem 0 .7rem;
            font-size: 1.08rem;
            line-height: 1.35;
        }

        .purchase-hero p,
        .purchase-flow,
        .receive-insight-strip,
        #scrollReceiveTable {
            display: none;
        }

        .purchase-hero-actions {
            grid-template-columns: 1fr;
        }

        .purchase-hero-actions .btn {
            min-height: 42px;
        }

        .purchase-stats-grid {
            gap: 0;
            margin: .6rem 0;
            border: 1px solid var(--purchase-border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 7px 20px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .purchase-stat {
            min-height: 66px;
            gap: .45rem;
            padding: .58rem .65rem;
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        .purchase-stat:nth-child(even) {
            border-left: 1px solid var(--purchase-border);
        }

        .purchase-stat:nth-child(n + 3) {
            border-top: 1px solid var(--purchase-border);
        }

        .purchase-stat-icon {
            width: 32px;
            height: 32px;
            flex-basis: 32px;
            border-radius: 8px;
            font-size: .95rem;
        }

        .purchase-stat-copy strong {
            font-size: .92rem;
        }

        .purchase-stat span {
            margin-top: .12rem;
            font-size: .58rem;
        }

        .purchase-table-title p {
            display: none;
        }

        .purchase-table-toolbar {
            gap: .6rem;
            padding: .72rem;
        }

        .purchase-table-tools {
            grid-template-columns: 44px 44px minmax(0, 1fr);
        }

        .receive-mobile-filter-toggle,
        .receive-mobile-more {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .receive-mobile-filter-toggle.is-active {
            color: #fff !important;
            border-color: var(--purchase-primary) !important;
            background: var(--purchase-primary) !important;
        }

        #openPenerimaanModalToolbar {
            min-width: 0;
            padding-inline: .55rem;
            font-size: .76rem;
        }

        .purchase-filter-bar {
            display: none;
        }

        .purchase-filter-bar.is-mobile-open {
            display: flex;
            animation: receive-mobile-reveal .18s ease-out;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(4),
        #tablePenerimaan tbody tr:not(.child) > td:nth-child(6),
        #tablePenerimaan tbody tr:not(.child) > td:nth-child(7),
        #tablePenerimaan tbody tr:not(.child) > td:nth-child(11) {
            display: none !important;
        }

        #tablePenerimaan tbody tr.is-mobile-expanded > td:nth-child(4),
        #tablePenerimaan tbody tr.is-mobile-expanded > td:nth-child(6),
        #tablePenerimaan tbody tr.is-mobile-expanded > td:nth-child(7),
        #tablePenerimaan tbody tr.is-mobile-expanded > td:nth-child(11) {
            display: flex !important;
            animation: receive-mobile-reveal .16s ease-out;
        }

        #tablePenerimaan tbody tr:not(.child) {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        #tablePenerimaan tbody tr:not(.child) > td {
            display: flex !important;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            gap: .24rem;
            min-height: 38px;
            padding: .44rem .65rem !important;
            border-left: 1px solid #edf2f7;
        }

        #tablePenerimaan tbody tr:not(.child) > td::before {
            align-self: stretch;
            grid-column: auto;
            grid-row: auto;
            font-size: .59rem;
            line-height: 1.25;
        }

        #tablePenerimaan tbody tr:not(.child) > td > * {
            grid-column: auto;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(1) {
            display: none !important;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(2) {
            order: 2;
            border-left: 0;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(3) {
            grid-column: 1 / -1;
            order: 1;
            border-left: 0;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(4) {
            order: 7;
            border-left: 0;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(5) {
            grid-column: 1 / -1;
            order: 4;
            border-left: 0;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(6) {
            order: 8;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(7) {
            order: 9;
            border-left: 0;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(8) {
            order: 3;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(9) {
            order: 5;
            border-left: 0;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(10) {
            order: 6;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(11) {
            order: 10;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(12) {
            grid-column: 1 / -1;
            order: 11;
            border-left: 0;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(2) {
            padding-block: .38rem !important;
        }

        #tablePenerimaan tbody tr:not(.child) > td:nth-child(3) {
            min-height: 46px;
        }

        #tablePenerimaan .purchase-action-group {
            grid-template-columns: repeat(auto-fit, minmax(52px, 1fr));
        }

        #tablePenerimaan .receive-mobile-more {
            gap: .25rem;
            color: var(--purchase-primary-strong);
            border-color: var(--purchase-border);
            background: var(--purchase-soft);
            font-size: .72rem;
            font-weight: 800;
        }

        #receivePoSummary:not(.is-mobile-details-open) .purchase-form-section-body {
            display: none;
        }

        .receive-mobile-section-toggle {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: .25rem;
            min-height: 34px !important;
            margin-left: auto;
            border: 1px solid var(--purchase-border);
            border-radius: 8px;
            padding: .3rem .5rem;
            color: var(--purchase-primary-strong);
            background: #fff;
            font-size: .66rem;
            font-weight: 800;
        }

        .receive-mobile-section-toggle[aria-expanded="true"] {
            border-color: rgba(15, 118, 110, .25);
            background: var(--purchase-soft);
        }

        #penerimaanModal .purchase-form-section-title small,
        #penerimaanModalDetail .purchase-form-section-title small,
        .receive-info-section .purchase-field-hint {
            display: none;
        }

        .receive-info-section .purchase-form-section-body > .row,
        .receive-invoice-section .purchase-form-section-body > .row,
        #penerimaanModalDetail .purchase-form-section-body > .row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .65rem;
            margin: 0;
        }

        .receive-info-section .purchase-form-section-body > .row > *,
        .receive-invoice-section .purchase-form-section-body > .row > *,
        #penerimaanModalDetail .purchase-form-section-body > .row > * {
            width: 100%;
            max-width: none;
            padding: 0;
        }

        .receive-info-section .purchase-form-section-body > .row > :nth-child(2),
        .receive-info-section .purchase-form-section-body > .row > :nth-child(3),
        .receive-info-section .purchase-form-section-body > .row > :nth-child(6),
        .receive-invoice-section .purchase-form-section-body > .row > :nth-child(1),
        .receive-invoice-section .purchase-form-section-body > .row > :nth-child(9),
        .receive-invoice-section .purchase-form-section-body > .row > :nth-child(10),
        .receive-invoice-section .purchase-form-section-body > .row > :nth-child(11) {
            grid-column: 1 / -1;
        }

        .receive-info-section .purchase-form-section-body > .row > :nth-child(2) {
            order: -2;
        }

        .receive-info-section .purchase-form-section-body > .row > :nth-child(3) {
            order: -1;
        }

        .receive-invoice-section:not(.is-mobile-details-open) .purchase-form-section-body > .row > :nth-child(4),
        .receive-invoice-section:not(.is-mobile-details-open) .purchase-form-section-body > .row > :nth-child(5),
        .receive-invoice-section:not(.is-mobile-details-open) .purchase-form-section-body > .row > :nth-child(6),
        .receive-invoice-section:not(.is-mobile-details-open) .purchase-form-section-body > .row > :nth-child(7),
        .receive-invoice-section:not(.is-mobile-details-open) .purchase-form-section-body > .row > :nth-child(8) {
            display: none;
        }

        .receive-invoice-board {
            padding: .7rem;
        }

        .receive-invoice-main strong {
            font-size: 1.08rem;
        }

        #penerimaanModalDetail .purchase-form-section-body > .row > :last-child:nth-child(odd) {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 575.98px) {
        .receive-form-progress {
            gap: .2rem;
            padding: .45rem .55rem;
        }

        .receive-progress-step {
            gap: .28rem;
        }

        .receive-progress-step > span {
            width: 24px;
            height: 24px;
            flex-basis: 24px;
            font-size: .68rem;
        }

        .receive-progress-step strong {
            font-size: .64rem;
        }

        .receive-detail-editor tbody,
        #detailReceiveTable tbody {
            gap: .6rem;
        }

        .receive-detail-row,
        #detailReceiveTable tbody tr {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .receive-detail-row > td,
        #detailReceiveTable tbody td {
            display: flex !important;
            flex-direction: column;
            align-items: stretch;
            gap: .3rem;
            min-width: 0;
            padding: .55rem .6rem !important;
            border-top: 1px solid #edf2f7;
            border-left: 1px solid #edf2f7;
        }

        .receive-detail-row > td:nth-child(odd),
        #detailReceiveTable tbody td:nth-child(odd) {
            border-left: 0;
        }

        .receive-detail-row > td::before,
        #detailReceiveTable tbody td::before {
            display: block;
            align-self: stretch;
            margin: 0;
            font-size: .6rem;
            line-height: 1.25;
        }

        .receive-detail-row > td > *,
        #detailReceiveTable tbody td > * {
            grid-column: auto;
        }

        .receive-detail-row > td:nth-child(1),
        .receive-detail-row > td:nth-child(4),
        #detailReceiveTable tbody td:nth-child(1) {
            grid-column: 1 / -1;
            border-left: 0;
        }

        .receive-detail-row > td:first-child,
        #detailReceiveTable tbody td:first-child {
            display: flex !important;
        }

        .receive-detail-row .receive-field-note,
        .receive-detail-row .receive-row-hint,
        .receive-detail-row .receive-batch-mode {
            display: none;
        }

        .receive-detail-live-summary {
            margin-bottom: .6rem;
        }

        .receive-detail-live-summary > div {
            padding: .55rem;
        }

        .receive-detail-live-summary span {
            font-size: .58rem;
        }

        .receive-detail-live-summary strong {
            font-size: .82rem;
        }

        .receive-payment-actions .btn {
            min-height: 42px;
            font-size: .68rem;
        }
    }

    @keyframes receive-mobile-reveal {
        from {
            opacity: 0;
            transform: translateY(-4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 359.98px) {
        .purchase-stats-grid,
        .purchase-hero-actions,
        .receive-detail-actions,
        .receive-payment-actions {
            grid-template-columns: 1fr;
        }

        .receive-detail-actions .receive-item-count {
            grid-column: auto;
        }

        .purchase-stat:nth-child(even) {
            border-left: 0;
        }

        .purchase-stat:nth-child(n + 2) {
            border-top: 1px solid var(--purchase-border);
        }

        .purchase-date-filter {
            grid-template-columns: 1fr;
        }

        .purchase-date-filter > label {
            grid-column: auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .purchase-page *,
        .purchase-page *::before,
        .purchase-page *::after {
            scroll-behavior: auto !important;
            transition: none !important;
        }
    }
</style>
