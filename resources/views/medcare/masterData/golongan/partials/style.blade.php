<style>
    .golongan-page {
        --golongan-primary: #0f766e;
        --golongan-primary-strong: #0d5f59;
        --golongan-accent: #2563eb;
        --golongan-success: #16a34a;
        --golongan-danger: #dc2626;
        --golongan-surface: #ffffff;
        --golongan-soft: #e7f8f4;
        --golongan-soft-blue: #eef6ff;
        --golongan-soft-amber: #fff7e8;
        --golongan-border: #dbe7f5;
        --golongan-text: #172033;
        --golongan-muted: #667085;
        color: var(--golongan-text);
    }

    .golongan-page .page-breadcrumb,
    .golongan-page .breadcrumb {
        margin-bottom: 1rem;
    }

    .golongan-hero {
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

    .golongan-kicker {
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

    .golongan-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .golongan-hero p {
        max-width: 650px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .golongan-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .golongan-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .golongan-hero-actions .btn-light {
        color: var(--golongan-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .golongan-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .golongan-flow-panel {
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

    .golongan-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .golongan-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .golongan-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .golongan-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .golongan-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .golongan-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--golongan-border);
        border-radius: 8px;
        background: var(--golongan-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .golongan-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--golongan-primary-strong);
        background: var(--golongan-soft);
        font-size: 1.35rem;
    }

    .golongan-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .golongan-stat span {
        display: block;
        color: var(--golongan-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .golongan-stat small {
        display: block;
        color: var(--golongan-muted);
        line-height: 1.35;
    }

    .golongan-table-section {
        border: 1px solid var(--golongan-border);
        border-radius: 8px;
        background: var(--golongan-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .golongan-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--golongan-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .golongan-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .golongan-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--golongan-accent);
        background: var(--golongan-soft-blue);
        font-size: 1.25rem;
    }

    .golongan-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .golongan-table-title p {
        margin: .1rem 0 0;
        color: var(--golongan-muted);
    }

    .golongan-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .golongan-search {
        display: flex;
        align-items: center;
        min-width: min(360px, 48vw);
        border: 1px solid var(--golongan-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .golongan-search i {
        padding-left: .85rem;
        color: var(--golongan-muted);
        font-size: 1.05rem;
    }

    .golongan-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--golongan-text);
    }

    .golongan-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .golongan-table {
        margin-bottom: .5rem !important;
        color: var(--golongan-text);
    }

    .golongan-table thead th {
        border-bottom: 0 !important;
        color: var(--golongan-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .golongan-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .golongan-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .golongan-table tbody tr:hover,
    .golongan-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .golongan-code-badge,
    .golongan-description-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
    }

    .golongan-code-badge {
        color: var(--golongan-primary-strong);
        background: var(--golongan-soft);
        white-space: nowrap;
    }

    .golongan-description-badge {
        max-width: 300px;
        color: var(--golongan-accent);
        background: var(--golongan-soft-blue);
        white-space: normal;
    }

    .golongan-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 200px;
    }

    .golongan-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--golongan-primary), var(--golongan-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .golongan-identity strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .golongan-identity small,
    .golongan-muted {
        color: var(--golongan-muted);
    }

    .golongan-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .golongan-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .golongan-status-text.is-active {
        color: var(--golongan-success);
    }

    .golongan-status-text.is-inactive {
        color: var(--golongan-muted);
    }

    .golongan-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .golongan-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--golongan-success);
    }

    .golongan-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .golongan-action-btn {
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

    .golongan-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .golongan-action-edit {
        color: var(--golongan-accent) !important;
        background: var(--golongan-soft-blue) !important;
    }

    .golongan-action-delete {
        color: var(--golongan-danger) !important;
        background: #fff0f0 !important;
    }

    .golongan-table-meta,
    .golongan-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--golongan-muted);
    }

    .golongan-table-footer {
        border-top: 1px solid var(--golongan-border);
    }

    .golongan-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--golongan-border);
    }

    .golongan-table-footer .pagination {
        margin-bottom: 0;
    }

    .golongan-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--golongan-primary-strong);
        border-color: var(--golongan-border);
    }

    .golongan-table-footer .active .page-link {
        color: #fff;
        background: var(--golongan-primary);
        border-color: var(--golongan-primary);
    }

    .golongan-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--golongan-primary-strong);
        font-weight: 700;
    }

    .golongan-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .golongan-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--golongan-primary), var(--golongan-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .golongan-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .golongan-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .golongan-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .golongan-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .golongan-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .golongan-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .golongan-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--golongan-border);
    }

    .golongan-modal .modal-footer .btn,
    .golongan-batch-toolbar .btn,
    .golongan-import-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .golongan-form-intro,
    .golongan-batch-toolbar,
    .golongan-import-panel {
        margin-bottom: .85rem;
        padding: .85rem;
        border: 1px solid var(--golongan-border);
        border-radius: 8px;
        background: #fff;
    }

    .golongan-form-intro {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .golongan-form-intro i {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 8px;
        color: var(--golongan-primary-strong);
        background: var(--golongan-soft);
        font-size: 1.2rem;
    }

    .golongan-form-intro strong {
        display: block;
        font-weight: 800;
    }

    .golongan-form-intro small,
    .golongan-form-hint {
        color: var(--golongan-muted);
    }

    .golongan-batch-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
    }

    .golongan-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--golongan-primary-strong);
        background: var(--golongan-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .golongan-batch-list {
        display: grid;
        gap: .75rem;
    }

    .golongan-batch-row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        align-items: start;
        padding: .9rem;
        border: 1px solid var(--golongan-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .golongan-batch-row.has-error {
        border-color: rgba(220, 38, 38, .36);
        box-shadow: 0 8px 20px rgba(220, 38, 38, .06);
    }

    .golongan-field {
        margin-bottom: 0;
    }

    .golongan-field.is-code {
        grid-column: span 2;
    }

    .golongan-field.is-name {
        grid-column: span 4;
    }

    .golongan-field.is-description {
        grid-column: span 5;
    }

    .golongan-field.is-action {
        grid-column: span 1;
        align-self: end;
    }

    .golongan-field .form-label {
        margin-bottom: .45rem;
        color: var(--golongan-text);
        font-weight: 800;
    }

    .golongan-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--golongan-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .golongan-input-shell:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .golongan-input-shell .golongan-input-icon {
        display: grid;
        width: 42px;
        flex: 0 0 42px;
        place-items: center;
        color: var(--golongan-muted);
        font-size: 1.05rem;
    }

    .golongan-input-shell .form-control {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .golongan-input-shell .form-control.is-invalid {
        background-image: none;
    }

    .golongan-field.has-error .invalid-feedback {
        display: block;
    }

    .golongan-row-remove {
        display: inline-grid !important;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px !important;
        padding: 0 !important;
    }

    .golongan-form-hint {
        display: block;
        margin-top: .35rem;
        font-size: .78rem;
    }

    .golongan-import-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }

    .golongan-import-steps {
        margin: .45rem 0 0;
        padding-left: 1.15rem;
        color: var(--golongan-muted);
    }

    .golongan-import-steps li + li {
        margin-top: .2rem;
    }

    .golongan-page .dropify-wrapper {
        border: 1px dashed rgba(15, 118, 110, .35);
        border-radius: 8px;
        background: #fff;
    }

    .golongan-page .shake {
        animation: golongan-shake .42s linear;
    }

    @keyframes golongan-shake {
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
        .golongan-hero {
            grid-template-columns: 1fr;
        }

        .golongan-stats-grid {
            grid-template-columns: 1fr;
        }

        .golongan-table-toolbar,
        .golongan-table-tools,
        .golongan-table-meta,
        .golongan-table-footer,
        .golongan-import-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .golongan-import-panel {
            display: flex;
        }

        .golongan-search {
            min-width: 100%;
        }

        .golongan-field.is-code,
        .golongan-field.is-name,
        .golongan-field.is-description,
        .golongan-field.is-action {
            grid-column: 1 / -1;
        }

        .golongan-field.is-action {
            justify-self: start;
        }
    }

    @media (max-width: 575.98px) {
        .golongan-hero {
            padding: 1rem;
        }

        .golongan-hero h2 {
            font-size: 1.35rem;
        }

        .golongan-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .golongan-batch-row {
            padding: .75rem;
        }
    }
</style>
