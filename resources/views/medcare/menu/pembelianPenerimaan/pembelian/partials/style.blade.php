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

    #purchaseDatePreset {
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

    .purchase-status.is-partial {
        color: var(--purchase-info);
        background: #ecfeff;
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

    .purchase-modal .detail-item.is-oot {
        border-color: #86cfa5 !important;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, .08);
    }

    .purchase-oot-option {
        min-height: 64px;
        padding: .55rem .7rem;
        border: 1px solid var(--purchase-border);
        border-radius: 8px;
        background: #fff;
    }

    .purchase-oot-option .form-check-label {
        color: var(--purchase-text);
        font-size: .82rem;
        font-weight: 800;
        cursor: pointer;
    }

    .purchase-oot-option small {
        display: block;
        margin-top: .2rem;
        color: var(--purchase-muted);
        font-size: .72rem;
        line-height: 1.3;
    }

    .purchase-oot-option .form-check-input:checked {
        border-color: #198754;
        background-color: #198754;
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

    @keyframes purchase-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 1199.98px) {
        .purchase-stats-grid {
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
    }

    @media (max-width: 575.98px) {
        .purchase-hero {
            padding: 1rem;
        }

        .purchase-stats-grid {
            grid-template-columns: 1fr;
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

        .purchase-detail-card-head {
            flex-direction: column;
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
