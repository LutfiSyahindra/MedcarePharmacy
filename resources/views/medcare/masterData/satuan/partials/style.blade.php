<style>
    .satuan-page {
        --satuan-primary: #0f766e;
        --satuan-primary-strong: #0d5f59;
        --satuan-accent: #2563eb;
        --satuan-success: #16a34a;
        --satuan-danger: #dc2626;
        --satuan-warning: #d97706;
        --satuan-surface: #ffffff;
        --satuan-soft: #e7f8f4;
        --satuan-soft-blue: #eef6ff;
        --satuan-soft-amber: #fff7e8;
        --satuan-border: #dbe7f5;
        --satuan-text: #172033;
        --satuan-muted: #667085;
        color: var(--satuan-text);
    }

    .satuan-page .page-breadcrumb {
        margin-bottom: 1rem;
    }

    .satuan-page .breadcrumb {
        margin-bottom: 0;
    }

    .satuan-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 380px);
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

    .satuan-kicker {
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

    .satuan-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .satuan-hero p {
        max-width: 650px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .satuan-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .satuan-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .satuan-hero-actions .btn-light {
        color: var(--satuan-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .satuan-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .satuan-flow-panel {
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

    .satuan-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .satuan-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .satuan-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .satuan-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .satuan-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .satuan-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--satuan-border);
        border-radius: 8px;
        background: var(--satuan-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .satuan-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--satuan-primary-strong);
        background: var(--satuan-soft);
        font-size: 1.35rem;
    }

    .satuan-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .satuan-stat span {
        display: block;
        color: var(--satuan-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .satuan-stat small {
        display: block;
        color: var(--satuan-muted);
        line-height: 1.35;
    }

    .satuan-table-section {
        border: 1px solid var(--satuan-border);
        border-radius: 8px;
        background: var(--satuan-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .satuan-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--satuan-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .satuan-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .satuan-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--satuan-accent);
        background: var(--satuan-soft-blue);
        font-size: 1.25rem;
    }

    .satuan-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .satuan-table-title p {
        margin: .1rem 0 0;
        color: var(--satuan-muted);
    }

    .satuan-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .satuan-search {
        display: flex;
        align-items: center;
        min-width: min(360px, 48vw);
        border: 1px solid var(--satuan-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .satuan-search i {
        padding-left: .85rem;
        color: var(--satuan-muted);
        font-size: 1.05rem;
    }

    .satuan-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--satuan-text);
    }

    .satuan-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .satuan-table {
        margin-bottom: .5rem !important;
        color: var(--satuan-text);
    }

    .satuan-table thead th {
        border-bottom: 0 !important;
        color: var(--satuan-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .satuan-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .satuan-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .satuan-table tbody tr:hover,
    .satuan-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .satuan-code-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        color: var(--satuan-primary-strong);
        background: var(--satuan-soft);
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .satuan-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 200px;
    }

    .satuan-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--satuan-primary), var(--satuan-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .satuan-identity strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .satuan-identity small,
    .satuan-muted {
        color: var(--satuan-muted);
    }

    .satuan-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .satuan-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .satuan-status-text.is-active {
        color: var(--satuan-success);
    }

    .satuan-status-text.is-inactive {
        color: var(--satuan-muted);
    }

    .satuan-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .satuan-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--satuan-success);
    }

    .satuan-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .satuan-action-btn {
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

    .satuan-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .satuan-action-edit {
        color: var(--satuan-accent) !important;
        background: var(--satuan-soft-blue) !important;
    }

    .satuan-action-delete {
        color: var(--satuan-danger) !important;
        background: #fff0f0 !important;
    }

    .satuan-table-meta,
    .satuan-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--satuan-muted);
    }

    .satuan-table-footer {
        border-top: 1px solid var(--satuan-border);
    }

    .satuan-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--satuan-border);
    }

    .satuan-table-footer .pagination {
        margin-bottom: 0;
    }

    .satuan-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--satuan-primary-strong);
        border-color: var(--satuan-border);
    }

    .satuan-table-footer .active .page-link {
        color: #fff;
        background: var(--satuan-primary);
        border-color: var(--satuan-primary);
    }

    .satuan-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--satuan-primary-strong);
        font-weight: 700;
    }

    .satuan-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .satuan-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--satuan-primary), var(--satuan-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .satuan-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .satuan-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .satuan-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .satuan-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .satuan-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .satuan-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .satuan-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--satuan-border);
    }

    .satuan-modal .modal-footer .btn,
    .satuan-batch-toolbar .btn,
    .satuan-import-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .satuan-form-intro,
    .satuan-batch-toolbar,
    .satuan-import-panel {
        margin-bottom: .85rem;
        padding: .85rem;
        border: 1px solid var(--satuan-border);
        border-radius: 8px;
        background: #fff;
    }

    .satuan-form-intro {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .satuan-form-intro i {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 8px;
        color: var(--satuan-primary-strong);
        background: var(--satuan-soft);
        font-size: 1.2rem;
    }

    .satuan-form-intro strong {
        display: block;
        font-weight: 800;
    }

    .satuan-form-intro small {
        color: var(--satuan-muted);
    }

    .satuan-batch-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
    }

    .satuan-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--satuan-primary-strong);
        background: var(--satuan-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .satuan-batch-list {
        display: grid;
        gap: .75rem;
    }

    .satuan-batch-row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        align-items: start;
        padding: .9rem;
        border: 1px solid var(--satuan-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .satuan-batch-row.has-error {
        border-color: rgba(220, 38, 38, .36);
        box-shadow: 0 8px 20px rgba(220, 38, 38, .06);
    }

    .satuan-field {
        margin-bottom: 0;
    }

    .satuan-field.is-code {
        grid-column: span 3;
    }

    .satuan-field.is-name {
        grid-column: span 8;
    }

    .satuan-field.is-action {
        grid-column: span 1;
        align-self: end;
    }

    .satuan-field .form-label {
        margin-bottom: .45rem;
        color: var(--satuan-text);
        font-weight: 800;
    }

    .satuan-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--satuan-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .satuan-input-shell:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .satuan-input-shell .satuan-input-icon {
        display: grid;
        width: 42px;
        flex: 0 0 42px;
        place-items: center;
        color: var(--satuan-muted);
        font-size: 1.05rem;
    }

    .satuan-input-shell .form-control {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .satuan-input-shell .form-control.is-invalid {
        background-image: none;
    }

    .satuan-field.has-error .invalid-feedback {
        display: block;
    }

    .satuan-row-remove {
        display: inline-grid !important;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px !important;
        padding: 0 !important;
    }

    .satuan-form-hint {
        display: block;
        margin-top: .35rem;
        color: var(--satuan-muted);
        font-size: .78rem;
    }

    .satuan-import-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }

    .satuan-import-steps {
        margin: .45rem 0 0;
        padding-left: 1.15rem;
        color: var(--satuan-muted);
    }

    .satuan-import-steps li + li {
        margin-top: .2rem;
    }

    .satuan-page .dropify-wrapper {
        border: 1px dashed rgba(15, 118, 110, .35);
        border-radius: 8px;
        background: #fff;
    }

    .satuan-page .shake {
        animation: satuan-shake .42s linear;
    }

    @keyframes satuan-shake {
        0%, 100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-6px);
        }

        75% {
            transform: translateX(6px);
        }
    }

    @media (max-width: 991.98px) {
        .satuan-hero {
            grid-template-columns: 1fr;
        }

        .satuan-stats-grid {
            grid-template-columns: 1fr;
        }

        .satuan-table-toolbar,
        .satuan-table-tools,
        .satuan-table-meta,
        .satuan-table-footer,
        .satuan-import-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .satuan-import-panel {
            display: flex;
        }

        .satuan-search {
            min-width: 100%;
        }

        .satuan-field.is-code,
        .satuan-field.is-name,
        .satuan-field.is-action {
            grid-column: 1 / -1;
        }

        .satuan-field.is-action {
            justify-self: start;
        }
    }

    @media (max-width: 575.98px) {
        .satuan-hero {
            padding: 1rem;
        }

        .satuan-hero h2 {
            font-size: 1.35rem;
        }

        .satuan-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .satuan-batch-row {
            padding: .75rem;
        }
    }
</style>
