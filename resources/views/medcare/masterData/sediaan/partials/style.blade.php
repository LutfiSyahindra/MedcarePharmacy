<style>
    .sediaan-page {
        --sediaan-primary: #0f766e;
        --sediaan-primary-strong: #0d5f59;
        --sediaan-accent: #2563eb;
        --sediaan-success: #16a34a;
        --sediaan-danger: #dc2626;
        --sediaan-surface: #ffffff;
        --sediaan-soft: #e7f8f4;
        --sediaan-soft-blue: #eef6ff;
        --sediaan-border: #dbe7f5;
        --sediaan-text: #172033;
        --sediaan-muted: #667085;
        color: var(--sediaan-text);
    }

    .sediaan-page .page-breadcrumb,
    .sediaan-page .breadcrumb {
        margin-bottom: 1rem;
    }

    .sediaan-hero {
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

    .sediaan-kicker {
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

    .sediaan-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .sediaan-hero p {
        max-width: 650px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .sediaan-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .sediaan-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .sediaan-hero-actions .btn-light {
        color: var(--sediaan-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .sediaan-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .sediaan-flow-panel {
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

    .sediaan-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .sediaan-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .sediaan-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .sediaan-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .sediaan-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .sediaan-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--sediaan-border);
        border-radius: 8px;
        background: var(--sediaan-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .sediaan-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--sediaan-primary-strong);
        background: var(--sediaan-soft);
        font-size: 1.35rem;
    }

    .sediaan-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .sediaan-stat span {
        display: block;
        color: var(--sediaan-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .sediaan-stat small {
        display: block;
        color: var(--sediaan-muted);
        line-height: 1.35;
    }

    .sediaan-table-section {
        border: 1px solid var(--sediaan-border);
        border-radius: 8px;
        background: var(--sediaan-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .sediaan-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--sediaan-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .sediaan-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .sediaan-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--sediaan-accent);
        background: var(--sediaan-soft-blue);
        font-size: 1.25rem;
    }

    .sediaan-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .sediaan-table-title p {
        margin: .1rem 0 0;
        color: var(--sediaan-muted);
    }

    .sediaan-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .sediaan-search {
        display: flex;
        align-items: center;
        min-width: min(360px, 48vw);
        border: 1px solid var(--sediaan-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .sediaan-search i {
        padding-left: .85rem;
        color: var(--sediaan-muted);
        font-size: 1.05rem;
    }

    .sediaan-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--sediaan-text);
    }

    .sediaan-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .sediaan-table {
        margin-bottom: .5rem !important;
        color: var(--sediaan-text);
    }

    .sediaan-table thead th {
        border-bottom: 0 !important;
        color: var(--sediaan-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sediaan-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .sediaan-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .sediaan-table tbody tr:hover,
    .sediaan-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .sediaan-code-badge,
    .sediaan-description-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
    }

    .sediaan-code-badge {
        color: var(--sediaan-primary-strong);
        background: var(--sediaan-soft);
        white-space: nowrap;
    }

    .sediaan-description-badge {
        max-width: 300px;
        color: var(--sediaan-accent);
        background: var(--sediaan-soft-blue);
        white-space: normal;
    }

    .sediaan-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 200px;
    }

    .sediaan-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--sediaan-primary), var(--sediaan-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .sediaan-identity strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sediaan-identity small,
    .sediaan-muted {
        color: var(--sediaan-muted);
    }

    .sediaan-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .sediaan-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .sediaan-status-text.is-active {
        color: var(--sediaan-success);
    }

    .sediaan-status-text.is-inactive {
        color: var(--sediaan-muted);
    }

    .sediaan-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .sediaan-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--sediaan-success);
    }

    .sediaan-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .sediaan-action-btn {
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

    .sediaan-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .sediaan-action-edit {
        color: var(--sediaan-accent) !important;
        background: var(--sediaan-soft-blue) !important;
    }

    .sediaan-action-delete {
        color: var(--sediaan-danger) !important;
        background: #fff0f0 !important;
    }

    .sediaan-table-meta,
    .sediaan-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--sediaan-muted);
    }

    .sediaan-table-footer {
        border-top: 1px solid var(--sediaan-border);
    }

    .sediaan-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--sediaan-border);
    }

    .sediaan-table-footer .pagination {
        margin-bottom: 0;
    }

    .sediaan-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--sediaan-primary-strong);
        border-color: var(--sediaan-border);
    }

    .sediaan-table-footer .active .page-link {
        color: #fff;
        background: var(--sediaan-primary);
        border-color: var(--sediaan-primary);
    }

    .sediaan-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--sediaan-primary-strong);
        font-weight: 700;
    }

    .sediaan-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .sediaan-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--sediaan-primary), var(--sediaan-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .sediaan-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .sediaan-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .sediaan-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .sediaan-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .sediaan-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .sediaan-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .sediaan-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--sediaan-border);
    }

    .sediaan-modal .modal-footer .btn,
    .sediaan-batch-toolbar .btn,
    .sediaan-import-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .sediaan-form-intro,
    .sediaan-batch-toolbar,
    .sediaan-import-panel {
        margin-bottom: .85rem;
        padding: .85rem;
        border: 1px solid var(--sediaan-border);
        border-radius: 8px;
        background: #fff;
    }

    .sediaan-form-intro {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .sediaan-form-intro i {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 8px;
        color: var(--sediaan-primary-strong);
        background: var(--sediaan-soft);
        font-size: 1.2rem;
    }

    .sediaan-form-intro strong {
        display: block;
        font-weight: 800;
    }

    .sediaan-form-intro small,
    .sediaan-form-hint {
        color: var(--sediaan-muted);
    }

    .sediaan-batch-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
    }

    .sediaan-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--sediaan-primary-strong);
        background: var(--sediaan-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .sediaan-batch-list {
        display: grid;
        gap: .75rem;
    }

    .sediaan-batch-row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        align-items: start;
        padding: .9rem;
        border: 1px solid var(--sediaan-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .sediaan-batch-row.has-error {
        border-color: rgba(220, 38, 38, .36);
        box-shadow: 0 8px 20px rgba(220, 38, 38, .06);
    }

    .sediaan-field {
        margin-bottom: 0;
    }

    .sediaan-field.is-code {
        grid-column: span 2;
    }

    .sediaan-field.is-name {
        grid-column: span 4;
    }

    .sediaan-field.is-description {
        grid-column: span 5;
    }

    .sediaan-field.is-action {
        grid-column: span 1;
        align-self: end;
    }

    .sediaan-field .form-label {
        margin-bottom: .45rem;
        color: var(--sediaan-text);
        font-weight: 800;
    }

    .sediaan-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--sediaan-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .sediaan-input-shell:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .sediaan-input-shell .sediaan-input-icon {
        display: grid;
        width: 42px;
        flex: 0 0 42px;
        place-items: center;
        color: var(--sediaan-muted);
        font-size: 1.05rem;
    }

    .sediaan-input-shell .form-control {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .sediaan-input-shell .form-control.is-invalid {
        background-image: none;
    }

    .sediaan-field.has-error .invalid-feedback {
        display: block;
    }

    .sediaan-row-remove {
        display: inline-grid !important;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px !important;
        padding: 0 !important;
    }

    .sediaan-form-hint {
        display: block;
        margin-top: .35rem;
        font-size: .78rem;
    }

    .sediaan-import-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }

    .sediaan-import-steps {
        margin: .45rem 0 0;
        padding-left: 1.15rem;
        color: var(--sediaan-muted);
    }

    .sediaan-import-steps li + li {
        margin-top: .2rem;
    }

    .sediaan-page .dropify-wrapper {
        border: 1px dashed rgba(15, 118, 110, .35);
        border-radius: 8px;
        background: #fff;
    }

    .sediaan-page .shake {
        animation: sediaan-shake .42s linear;
    }

    @keyframes sediaan-shake {
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
        .sediaan-hero {
            grid-template-columns: 1fr;
        }

        .sediaan-stats-grid {
            grid-template-columns: 1fr;
        }

        .sediaan-table-toolbar,
        .sediaan-table-tools,
        .sediaan-table-meta,
        .sediaan-table-footer,
        .sediaan-import-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .sediaan-import-panel {
            display: flex;
        }

        .sediaan-search {
            min-width: 100%;
        }

        .sediaan-field.is-code,
        .sediaan-field.is-name,
        .sediaan-field.is-description,
        .sediaan-field.is-action {
            grid-column: 1 / -1;
        }

        .sediaan-field.is-action {
            justify-self: start;
        }
    }

    @media (max-width: 575.98px) {
        .sediaan-hero {
            padding: 1rem;
        }

        .sediaan-hero h2 {
            font-size: 1.35rem;
        }

        .sediaan-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .sediaan-batch-row {
            padding: .75rem;
        }
    }
</style>
