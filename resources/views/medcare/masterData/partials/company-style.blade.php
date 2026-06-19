<style>
    .company-page {
        --company-primary: #0f766e;
        --company-primary-strong: #0d5f59;
        --company-accent: #2563eb;
        --company-success: #16a34a;
        --company-danger: #dc2626;
        --company-surface: #ffffff;
        --company-soft: #e7f8f4;
        --company-soft-blue: #eef6ff;
        --company-border: #dbe7f5;
        --company-text: #172033;
        --company-muted: #667085;
        color: var(--company-text);
    }

    .company-page .page-breadcrumb,
    .company-page .breadcrumb {
        margin-bottom: 1rem;
    }

    .company-hero {
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

    .company-kicker {
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

    .company-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .company-hero p {
        max-width: 650px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .company-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .company-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .company-hero-actions .btn-light {
        color: var(--company-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .company-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .company-flow-panel {
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

    .company-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .company-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .company-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .company-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .company-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .company-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--company-border);
        border-radius: 8px;
        background: var(--company-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .company-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--company-primary-strong);
        background: var(--company-soft);
        font-size: 1.35rem;
    }

    .company-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .company-stat span {
        display: block;
        color: var(--company-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .company-stat small {
        display: block;
        color: var(--company-muted);
        line-height: 1.35;
    }

    .company-table-section {
        border: 1px solid var(--company-border);
        border-radius: 8px;
        background: var(--company-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .company-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--company-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .company-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .company-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--company-accent);
        background: var(--company-soft-blue);
        font-size: 1.25rem;
    }

    .company-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .company-table-title p {
        margin: .1rem 0 0;
        color: var(--company-muted);
    }

    .company-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .company-search {
        display: flex;
        align-items: center;
        min-width: min(360px, 48vw);
        border: 1px solid var(--company-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .company-search i {
        padding-left: .85rem;
        color: var(--company-muted);
        font-size: 1.05rem;
    }

    .company-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--company-text);
    }

    .company-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .company-table {
        margin-bottom: .5rem !important;
        color: var(--company-text);
    }

    .company-table thead th {
        border-bottom: 0 !important;
        color: var(--company-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .company-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .company-table tbody tr:hover,
    .company-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .company-code-badge,
    .company-contact-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
    }

    .company-code-badge {
        color: var(--company-primary-strong);
        background: var(--company-soft);
        white-space: nowrap;
    }

    .company-contact-badge {
        max-width: 260px;
        color: var(--company-accent);
        background: var(--company-soft-blue);
        white-space: normal;
    }

    .company-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 210px;
    }

    .company-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--company-primary), var(--company-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .company-identity strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .company-identity small,
    .company-muted {
        color: var(--company-muted);
    }

    .company-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .company-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .company-status-text.is-active {
        color: var(--company-success);
    }

    .company-status-text.is-inactive {
        color: var(--company-muted);
    }

    .company-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .company-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--company-success);
    }

    .company-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .company-action-btn {
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

    .company-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .company-action-edit {
        color: var(--company-accent) !important;
        background: var(--company-soft-blue) !important;
    }

    .company-action-delete {
        color: var(--company-danger) !important;
        background: #fff0f0 !important;
    }

    .company-table-meta,
    .company-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--company-muted);
    }

    .company-table-footer {
        border-top: 1px solid var(--company-border);
    }

    .company-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--company-border);
    }

    .company-table-footer .pagination {
        margin-bottom: 0;
    }

    .company-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--company-primary-strong);
        border-color: var(--company-border);
    }

    .company-table-footer .active .page-link {
        color: #fff;
        background: var(--company-primary);
        border-color: var(--company-primary);
    }

    .company-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--company-primary-strong);
        font-weight: 700;
    }

    .company-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .company-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--company-primary), var(--company-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .company-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .company-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .company-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .company-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .company-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .company-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .company-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--company-border);
    }

    .company-modal .modal-footer .btn,
    .company-batch-toolbar .btn,
    .company-import-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .company-form-intro,
    .company-batch-toolbar,
    .company-import-panel {
        margin-bottom: .85rem;
        padding: .85rem;
        border: 1px solid var(--company-border);
        border-radius: 8px;
        background: #fff;
    }

    .company-form-intro {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .company-form-intro i {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 8px;
        color: var(--company-primary-strong);
        background: var(--company-soft);
        font-size: 1.2rem;
    }

    .company-form-intro strong {
        display: block;
        font-weight: 800;
    }

    .company-form-intro small,
    .company-form-hint {
        color: var(--company-muted);
    }

    .company-batch-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
    }

    .company-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--company-primary-strong);
        background: var(--company-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .company-batch-list {
        display: grid;
        gap: .75rem;
    }

    .company-batch-row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        align-items: start;
        padding: .9rem;
        border: 1px solid var(--company-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .company-batch-row.has-error {
        border-color: rgba(220, 38, 38, .36);
        box-shadow: 0 8px 20px rgba(220, 38, 38, .06);
    }

    .company-field {
        margin-bottom: 0;
    }

    .company-field.is-code {
        grid-column: span 2;
    }

    .company-field.is-name {
        grid-column: span 3;
    }

    .company-field.is-address {
        grid-column: span 3;
    }

    .company-field.is-phone,
    .company-field.is-email {
        grid-column: span 2;
    }

    .company-field.is-action {
        grid-column: 1 / -1;
        justify-self: end;
    }

    .company-field .form-label {
        margin-bottom: .45rem;
        color: var(--company-text);
        font-weight: 800;
    }

    .company-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--company-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .company-input-shell:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .company-input-shell .company-input-icon {
        display: grid;
        width: 42px;
        flex: 0 0 42px;
        place-items: center;
        color: var(--company-muted);
        font-size: 1.05rem;
    }

    .company-input-shell .form-control {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .company-input-shell .form-control.is-invalid {
        background-image: none;
    }

    .company-field.has-error .invalid-feedback {
        display: block;
    }

    .company-row-remove {
        display: inline-flex !important;
        align-items: center;
        gap: .35rem;
        border-radius: 8px !important;
    }

    .company-form-hint {
        display: block;
        margin-top: .35rem;
        font-size: .78rem;
    }

    .company-import-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }

    .company-import-steps {
        margin: .45rem 0 0;
        padding-left: 1.15rem;
        color: var(--company-muted);
    }

    .company-import-steps li + li {
        margin-top: .2rem;
    }

    .company-page .dropify-wrapper {
        border: 1px dashed rgba(15, 118, 110, .35);
        border-radius: 8px;
        background: #fff;
    }

    .company-page .shake {
        animation: company-shake .42s linear;
    }

    @keyframes company-shake {
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
        .company-hero {
            grid-template-columns: 1fr;
        }

        .company-stats-grid {
            grid-template-columns: 1fr;
        }

        .company-table-toolbar,
        .company-table-tools,
        .company-table-meta,
        .company-table-footer,
        .company-import-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .company-import-panel {
            display: flex;
        }

        .company-search {
            min-width: 100%;
        }

        .company-field.is-code,
        .company-field.is-name,
        .company-field.is-address,
        .company-field.is-phone,
        .company-field.is-email,
        .company-field.is-action {
            grid-column: 1 / -1;
        }

        .company-field.is-action {
            justify-self: start;
        }
    }

    @media (max-width: 575.98px) {
        .company-hero {
            padding: 1rem;
        }

        .company-hero h2 {
            font-size: 1.35rem;
        }

        .company-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .company-batch-row {
            padding: .75rem;
        }
    }
</style>
