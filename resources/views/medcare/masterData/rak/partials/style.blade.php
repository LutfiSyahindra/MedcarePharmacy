<style>
    .rak-page {
        --rak-primary: #0369a1;
        --rak-primary-strong: #075985;
        --rak-accent: #0f766e;
        --rak-success: #16a34a;
        --rak-danger: #dc2626;
        --rak-surface: #ffffff;
        --rak-soft: #e0f2fe;
        --rak-soft-green: #e7f8f4;
        --rak-border: #dbe7f5;
        --rak-text: #172033;
        --rak-muted: #667085;
        color: var(--rak-text);
    }

    .rak-page .page-breadcrumb,
    .rak-page .breadcrumb {
        margin-bottom: 1rem;
    }

    .rak-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 380px);
        gap: 1rem;
        align-items: stretch;
        padding: 1.5rem;
        border-radius: 8px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(3, 105, 161, .97), rgba(15, 118, 110, .94)),
            linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
        background-size: auto, 34px 34px;
        box-shadow: 0 16px 40px rgba(3, 105, 161, .16);
        overflow: hidden;
    }

    .rak-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .rak-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .rak-hero p {
        max-width: 670px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .rak-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .rak-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .rak-hero-actions .btn-light {
        color: var(--rak-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .rak-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .rak-map-panel {
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

    .rak-map-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .rak-map-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .rak-map-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .rak-map-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .rak-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .rak-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--rak-border);
        border-radius: 8px;
        background: var(--rak-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .rak-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--rak-primary-strong);
        background: var(--rak-soft);
        font-size: 1.35rem;
    }

    .rak-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .rak-stat span {
        display: block;
        color: var(--rak-muted);
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .rak-stat small {
        display: block;
        color: var(--rak-muted);
        line-height: 1.35;
    }

    .rak-table-section {
        border: 1px solid var(--rak-border);
        border-radius: 8px;
        background: var(--rak-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .rak-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--rak-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .rak-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .rak-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--rak-accent);
        background: var(--rak-soft-green);
        font-size: 1.25rem;
    }

    .rak-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .rak-table-title p {
        margin: .1rem 0 0;
        color: var(--rak-muted);
    }

    .rak-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .rak-search {
        display: flex;
        align-items: center;
        min-width: min(380px, 48vw);
        border: 1px solid var(--rak-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .rak-search i {
        padding-left: .85rem;
        color: var(--rak-muted);
        font-size: 1.05rem;
    }

    .rak-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--rak-text);
    }

    .rak-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .rak-table {
        margin-bottom: .5rem !important;
        color: var(--rak-text);
    }

    .rak-table thead th {
        border-bottom: 0 !important;
        color: var(--rak-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .rak-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .rak-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .rak-table tbody tr:hover,
    .rak-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .rak-code-badge,
    .rak-location-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
    }

    .rak-code-badge {
        color: var(--rak-primary-strong);
        background: var(--rak-soft);
        white-space: nowrap;
    }

    .rak-location-badge {
        max-width: 320px;
        color: var(--rak-accent);
        background: var(--rak-soft-green);
        white-space: normal;
    }

    .rak-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 220px;
    }

    .rak-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--rak-primary), var(--rak-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .rak-identity strong {
        display: block;
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rak-identity small,
    .rak-muted {
        color: var(--rak-muted);
    }

    .rak-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .rak-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .rak-status-text.is-active {
        color: var(--rak-success);
    }

    .rak-status-text.is-inactive {
        color: var(--rak-muted);
    }

    .rak-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .rak-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--rak-success);
    }

    .rak-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .rak-action-btn {
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

    .rak-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .rak-action-edit {
        color: var(--rak-primary-strong) !important;
        background: var(--rak-soft) !important;
    }

    .rak-action-delete {
        color: var(--rak-danger) !important;
        background: #fff0f0 !important;
    }

    .rak-table-meta,
    .rak-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--rak-muted);
    }

    .rak-table-footer {
        border-top: 1px solid var(--rak-border);
    }

    .rak-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--rak-border);
    }

    .rak-table-footer .pagination {
        margin-bottom: 0;
    }

    .rak-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--rak-primary-strong);
        border-color: var(--rak-border);
    }

    .rak-table-footer .active .page-link {
        color: #fff;
        background: var(--rak-primary);
        border-color: var(--rak-primary);
    }

    .rak-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--rak-primary-strong);
        font-weight: 800;
    }

    .rak-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .rak-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--rak-primary), var(--rak-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .rak-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .rak-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .rak-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .rak-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .rak-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .rak-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .rak-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--rak-border);
    }

    .rak-modal .modal-footer .btn,
    .rak-batch-toolbar .btn,
    .rak-import-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .rak-form-intro,
    .rak-batch-toolbar,
    .rak-import-panel {
        margin-bottom: .85rem;
        padding: .85rem;
        border: 1px solid var(--rak-border);
        border-radius: 8px;
        background: #fff;
    }

    .rak-form-intro {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .rak-form-intro i {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 8px;
        color: var(--rak-primary-strong);
        background: var(--rak-soft);
        font-size: 1.2rem;
    }

    .rak-form-intro strong {
        display: block;
        font-weight: 800;
    }

    .rak-form-intro small,
    .rak-form-hint {
        color: var(--rak-muted);
    }

    .rak-batch-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
    }

    .rak-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--rak-primary-strong);
        background: var(--rak-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .rak-batch-list {
        display: grid;
        gap: .75rem;
    }

    .rak-batch-row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        align-items: start;
        padding: .9rem;
        border: 1px solid var(--rak-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .rak-batch-row.has-error {
        border-color: rgba(220, 38, 38, .36);
        box-shadow: 0 8px 20px rgba(220, 38, 38, .06);
    }

    .rak-field {
        margin-bottom: 0;
    }

    .rak-field.is-code {
        grid-column: span 2;
    }

    .rak-field.is-name {
        grid-column: span 4;
    }

    .rak-field.is-location {
        grid-column: span 5;
    }

    .rak-field.is-action {
        grid-column: span 1;
        align-self: end;
    }

    .rak-field .form-label {
        margin-bottom: .45rem;
        color: var(--rak-text);
        font-weight: 800;
    }

    .rak-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--rak-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .rak-input-shell:focus-within {
        border-color: rgba(3, 105, 161, .45);
        box-shadow: 0 0 0 .2rem rgba(3, 105, 161, .08);
    }

    .rak-input-shell .rak-input-icon {
        display: grid;
        width: 42px;
        flex: 0 0 42px;
        place-items: center;
        color: var(--rak-muted);
        font-size: 1.05rem;
    }

    .rak-input-shell .form-control {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .rak-input-shell .form-control.is-invalid {
        background-image: none;
    }

    .rak-field.has-error .invalid-feedback {
        display: block;
    }

    .rak-row-remove {
        display: inline-grid !important;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px !important;
        padding: 0 !important;
    }

    .rak-form-hint {
        display: block;
        margin-top: .35rem;
        font-size: .78rem;
    }

    .rak-import-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }

    .rak-import-steps {
        margin: .45rem 0 0;
        padding-left: 1.15rem;
        color: var(--rak-muted);
    }

    .rak-import-steps li + li {
        margin-top: .2rem;
    }

    .rak-page .dropify-wrapper {
        border: 1px dashed rgba(3, 105, 161, .35);
        border-radius: 8px;
        background: #fff;
    }

    .rak-page .shake {
        animation: rak-shake .42s linear;
    }

    @keyframes rak-shake {
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
        .rak-hero {
            grid-template-columns: 1fr;
        }

        .rak-stats-grid {
            grid-template-columns: 1fr;
        }

        .rak-table-toolbar,
        .rak-table-tools,
        .rak-table-meta,
        .rak-table-footer,
        .rak-import-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .rak-import-panel {
            display: flex;
        }

        .rak-search {
            min-width: 100%;
        }

        .rak-field.is-code,
        .rak-field.is-name,
        .rak-field.is-location,
        .rak-field.is-action {
            grid-column: 1 / -1;
        }

        .rak-field.is-action {
            justify-self: start;
        }
    }

    @media (max-width: 575.98px) {
        .rak-hero {
            padding: 1rem;
        }

        .rak-hero h2 {
            font-size: 1.35rem;
        }

        .rak-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .rak-batch-row {
            padding: .75rem;
        }
    }
</style>
