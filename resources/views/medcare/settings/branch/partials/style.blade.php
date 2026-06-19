<style>
    .branch-page {
        --branch-primary: #0f766e;
        --branch-primary-strong: #0d5f59;
        --branch-accent: #2563eb;
        --branch-success: #16a34a;
        --branch-warning: #d97706;
        --branch-danger: #dc2626;
        --branch-surface: #ffffff;
        --branch-soft: #e7f8f4;
        --branch-soft-blue: #eef6ff;
        --branch-border: #dbe7f5;
        --branch-text: #172033;
        --branch-muted: #667085;
        color: var(--branch-text);
    }

    .branch-page .page-breadcrumb {
        margin-bottom: 1rem;
    }

    .branch-page .breadcrumb {
        margin-bottom: 0;
    }

    .branch-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
        gap: 1rem;
        align-items: stretch;
        padding: 1.5rem;
        border-radius: 8px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .96), rgba(37, 99, 235, .94)),
            linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
        background-size: auto, 32px 32px;
        box-shadow: 0 16px 40px rgba(15, 118, 110, .16);
        overflow: hidden;
    }

    .branch-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .branch-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .branch-hero p {
        max-width: 620px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .branch-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .branch-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .branch-hero-actions .btn-light {
        color: var(--branch-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .branch-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .branch-flow-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: .65rem;
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
        backdrop-filter: blur(8px);
    }

    .branch-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .branch-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .branch-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .branch-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .branch-switcher {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        margin: 1rem 0;
    }

    .branch-switcher a {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .65rem .85rem;
        border: 1px solid var(--branch-border);
        border-radius: 8px;
        color: var(--branch-muted);
        background: var(--branch-surface);
        font-weight: 700;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, color .18s ease;
    }

    .branch-switcher a:hover,
    .branch-switcher a.is-active {
        color: var(--branch-primary-strong);
        border-color: rgba(15, 118, 110, .28);
        background: var(--branch-soft);
        box-shadow: 0 10px 24px rgba(15, 118, 110, .1);
        transform: translateY(-1px);
    }

    .branch-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin-bottom: 1rem;
    }

    .branch-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--branch-border);
        border-radius: 8px;
        background: var(--branch-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .branch-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--branch-primary-strong);
        background: var(--branch-soft);
        font-size: 1.35rem;
    }

    .branch-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .branch-stat span {
        display: block;
        color: var(--branch-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .branch-stat small {
        display: block;
        color: var(--branch-muted);
        line-height: 1.35;
    }

    .branch-table-section {
        border: 1px solid var(--branch-border);
        border-radius: 8px;
        background: var(--branch-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .branch-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--branch-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .branch-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .branch-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--branch-accent);
        background: var(--branch-soft-blue);
        font-size: 1.25rem;
    }

    .branch-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .branch-table-title p {
        margin: .1rem 0 0;
        color: var(--branch-muted);
    }

    .branch-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .branch-search {
        display: flex;
        align-items: center;
        min-width: min(340px, 48vw);
        border: 1px solid var(--branch-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .branch-search i {
        padding-left: .85rem;
        color: var(--branch-muted);
        font-size: 1.05rem;
    }

    .branch-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--branch-text);
    }

    .branch-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .branch-table {
        margin-bottom: .5rem !important;
        color: var(--branch-text);
    }

    .branch-table thead th {
        border-bottom: 0 !important;
        color: var(--branch-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .branch-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .branch-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .branch-table tbody tr:hover,
    .branch-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .branch-code-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        color: var(--branch-primary-strong);
        background: var(--branch-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .branch-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 200px;
    }

    .branch-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--branch-primary), var(--branch-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .branch-identity strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .branch-identity small,
    .branch-muted {
        color: var(--branch-muted);
    }

    .branch-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .branch-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .branch-status-text.is-active {
        color: var(--branch-success);
    }

    .branch-status-text.is-inactive {
        color: var(--branch-muted);
    }

    .branch-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .branch-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--branch-success);
    }

    .branch-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .branch-action-btn {
        display: inline-grid !important;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 8px !important;
        border: 1px solid transparent !important;
        padding: 0 !important;
        color: inherit;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .branch-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .branch-action-edit {
        color: var(--branch-accent) !important;
        background: var(--branch-soft-blue) !important;
    }

    .branch-action-delete {
        color: var(--branch-danger) !important;
        background: #fff0f0 !important;
    }

    .branch-action-assign {
        color: var(--branch-primary-strong) !important;
        background: var(--branch-soft) !important;
    }

    .branch-table-meta,
    .branch-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--branch-muted);
    }

    .branch-table-footer {
        border-top: 1px solid var(--branch-border);
    }

    .branch-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--branch-border);
    }

    .branch-table-footer .pagination {
        margin-bottom: 0;
    }

    .branch-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--branch-primary-strong);
        border-color: var(--branch-border);
    }

    .branch-table-footer .active .page-link {
        color: #fff;
        background: var(--branch-primary);
        border-color: var(--branch-primary);
    }

    .branch-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--branch-primary-strong);
        font-weight: 700;
    }

    .branch-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .branch-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--branch-primary), var(--branch-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .branch-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .branch-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .branch-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .branch-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .branch-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .branch-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .branch-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .branch-field {
        margin-bottom: 1rem;
    }

    .branch-field.is-wide {
        grid-column: 1 / -1;
    }

    .branch-field .form-label {
        margin-bottom: .45rem;
        color: var(--branch-text);
        font-weight: 800;
    }

    .branch-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--branch-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .branch-input-shell:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .branch-input-shell .branch-input-icon {
        display: grid;
        width: 42px;
        place-items: center;
        color: var(--branch-muted);
        font-size: 1.05rem;
    }

    .branch-input-shell .form-control {
        border: 0;
        box-shadow: none;
        background: transparent;
        padding-left: 0;
    }

    .branch-input-shell .form-control.is-invalid {
        background-image: none;
    }

    .branch-field.has-error .invalid-feedback {
        display: block;
    }

    .branch-form-hint {
        display: block;
        margin-top: .35rem;
        color: var(--branch-muted);
        font-size: .78rem;
    }

    .branch-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--branch-border);
    }

    .branch-modal .modal-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .branch-select-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
        margin-bottom: .75rem;
        padding: .75rem;
        border: 1px solid var(--branch-border);
        border-radius: 8px;
        background: #fff;
    }

    .branch-select-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }

    .branch-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--branch-primary-strong);
        background: var(--branch-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .branch-page .select2-container--default .select2-selection--multiple {
        min-height: 46px;
        border-color: var(--branch-border);
        border-radius: 8px;
        padding: .25rem .35rem;
    }

    .branch-page .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .branch-page .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border: 0;
        border-radius: 999px;
        color: var(--branch-primary-strong);
        background: var(--branch-soft);
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .branch-hero {
            grid-template-columns: 1fr;
        }

        .branch-stats-grid {
            grid-template-columns: 1fr;
        }

        .branch-table-toolbar,
        .branch-table-tools,
        .branch-table-meta,
        .branch-table-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .branch-search {
            min-width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .branch-hero {
            padding: 1rem;
        }

        .branch-hero h2 {
            font-size: 1.35rem;
        }

        .branch-switcher a {
            width: 100%;
            justify-content: center;
        }

        .branch-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .branch-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
