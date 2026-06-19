<style>
    .category-page {
        --category-primary: #0f766e;
        --category-primary-strong: #0d5f59;
        --category-accent: #2563eb;
        --category-success: #16a34a;
        --category-warning: #d97706;
        --category-danger: #dc2626;
        --category-surface: #ffffff;
        --category-soft: #e7f8f4;
        --category-soft-blue: #eef6ff;
        --category-soft-amber: #fff7e8;
        --category-border: #dbe7f5;
        --category-text: #172033;
        --category-muted: #667085;
        color: var(--category-text);
    }

    .category-page .page-breadcrumb {
        margin-bottom: 1rem;
    }

    .category-page .breadcrumb {
        margin-bottom: 0;
    }

    .category-hero {
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

    .category-kicker {
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

    .category-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .category-hero p {
        max-width: 650px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .category-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .category-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .category-hero-actions .btn-light {
        color: var(--category-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .category-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .category-flow-panel {
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

    .category-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
        transition: background .18s ease, transform .18s ease;
    }

    .category-flow-item.is-active {
        background: rgba(255, 255, 255, .22);
        transform: translateX(2px);
    }

    .category-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .category-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .category-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .category-switcher {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        margin: 1rem 0;
    }

    .category-switcher a {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .65rem .85rem;
        border: 1px solid var(--category-border);
        border-radius: 8px;
        color: var(--category-muted);
        background: var(--category-surface);
        font-weight: 700;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, color .18s ease;
    }

    .category-switcher a:hover,
    .category-switcher a.is-active {
        color: var(--category-primary-strong);
        border-color: rgba(15, 118, 110, .28);
        background: var(--category-soft);
        box-shadow: 0 10px 24px rgba(15, 118, 110, .1);
        transform: translateY(-1px);
    }

    .category-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin-bottom: 1rem;
    }

    .category-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--category-border);
        border-radius: 8px;
        background: var(--category-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .category-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--category-primary-strong);
        background: var(--category-soft);
        font-size: 1.35rem;
    }

    .category-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .category-stat span {
        display: block;
        color: var(--category-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .category-stat small {
        display: block;
        color: var(--category-muted);
        line-height: 1.35;
    }

    .category-table-section {
        border: 1px solid var(--category-border);
        border-radius: 8px;
        background: var(--category-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .category-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--category-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .category-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .category-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--category-accent);
        background: var(--category-soft-blue);
        font-size: 1.25rem;
    }

    .category-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .category-table-title p {
        margin: .1rem 0 0;
        color: var(--category-muted);
    }

    .category-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .category-search {
        display: flex;
        align-items: center;
        min-width: min(360px, 48vw);
        border: 1px solid var(--category-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .category-search i {
        padding-left: .85rem;
        color: var(--category-muted);
        font-size: 1.05rem;
    }

    .category-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--category-text);
    }

    .category-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .category-table {
        margin-bottom: .5rem !important;
        color: var(--category-text);
    }

    .category-table thead th {
        border-bottom: 0 !important;
        color: var(--category-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .category-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .category-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .category-table tbody tr:hover,
    .category-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .category-code-badge,
    .category-parent-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .category-code-badge {
        color: var(--category-primary-strong);
        background: var(--category-soft);
    }

    .category-parent-badge {
        color: var(--category-accent);
        background: var(--category-soft-blue);
    }

    .category-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 200px;
    }

    .category-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--category-primary), var(--category-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .category-identity strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .category-identity small,
    .category-muted {
        color: var(--category-muted);
    }

    .category-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .category-action-btn {
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

    .category-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .category-action-edit {
        color: var(--category-accent) !important;
        background: var(--category-soft-blue) !important;
    }

    .category-action-delete {
        color: var(--category-danger) !important;
        background: #fff0f0 !important;
    }

    .category-table-meta,
    .category-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--category-muted);
    }

    .category-table-footer {
        border-top: 1px solid var(--category-border);
    }

    .category-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--category-border);
    }

    .category-table-footer .pagination {
        margin-bottom: 0;
    }

    .category-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--category-primary-strong);
        border-color: var(--category-border);
    }

    .category-table-footer .active .page-link {
        color: #fff;
        background: var(--category-primary);
        border-color: var(--category-primary);
    }

    .category-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--category-primary-strong);
        font-weight: 700;
    }

    .category-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .category-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--category-primary), var(--category-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .category-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .category-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .category-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .category-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .category-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .category-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .category-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--category-border);
    }

    .category-modal .modal-footer .btn,
    .category-batch-toolbar .btn,
    .category-import-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .category-form-intro,
    .category-batch-toolbar,
    .category-import-panel {
        margin-bottom: .85rem;
        padding: .85rem;
        border: 1px solid var(--category-border);
        border-radius: 8px;
        background: #fff;
    }

    .category-form-intro {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .category-form-intro i {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 8px;
        color: var(--category-primary-strong);
        background: var(--category-soft);
        font-size: 1.2rem;
    }

    .category-form-intro strong {
        display: block;
        font-weight: 800;
    }

    .category-form-intro small {
        color: var(--category-muted);
    }

    .category-batch-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
    }

    .category-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--category-primary-strong);
        background: var(--category-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .category-batch-list {
        display: grid;
        gap: .75rem;
    }

    .category-batch-row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        align-items: start;
        padding: .9rem;
        border: 1px solid var(--category-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .category-batch-row.has-error {
        border-color: rgba(220, 38, 38, .36);
        box-shadow: 0 8px 20px rgba(220, 38, 38, .06);
    }

    .category-field {
        margin-bottom: 0;
    }

    .category-field.is-parent {
        grid-column: span 4;
    }

    .category-field.is-code {
        grid-column: span 3;
    }

    .category-field.is-name {
        grid-column: span 4;
    }

    .category-field.is-name-wide {
        grid-column: span 8;
    }

    .category-field.is-action {
        grid-column: span 1;
        align-self: end;
    }

    .category-field .form-label {
        margin-bottom: .45rem;
        color: var(--category-text);
        font-weight: 800;
    }

    .category-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--category-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .category-input-shell:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .category-input-shell .category-input-icon {
        display: grid;
        width: 42px;
        flex: 0 0 42px;
        place-items: center;
        color: var(--category-muted);
        font-size: 1.05rem;
    }

    .category-input-shell .form-control,
    .category-input-shell .form-select {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .category-input-shell .form-control.is-invalid,
    .category-input-shell .form-select.is-invalid {
        background-image: none;
    }

    .category-field.has-error .invalid-feedback {
        display: block;
    }

    .category-row-remove {
        display: inline-grid !important;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px !important;
        padding: 0 !important;
    }

    .category-form-hint {
        display: block;
        margin-top: .35rem;
        color: var(--category-muted);
        font-size: .78rem;
    }

    .category-import-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }

    .category-import-steps {
        margin: .45rem 0 0;
        padding-left: 1.15rem;
        color: var(--category-muted);
    }

    .category-import-steps li + li {
        margin-top: .2rem;
    }

    .category-page .select2-container--default .select2-selection--single {
        min-height: 44px;
        border-color: var(--category-border);
        border-radius: 8px;
        padding: .25rem .35rem;
    }

    .category-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px;
    }

    .category-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    .category-page .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .category-page .dropify-wrapper {
        border: 1px dashed rgba(15, 118, 110, .35);
        border-radius: 8px;
        background: #fff;
    }

    .category-page .shake {
        animation: category-shake .42s linear;
    }

    @keyframes category-shake {
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
        .category-hero {
            grid-template-columns: 1fr;
        }

        .category-stats-grid {
            grid-template-columns: 1fr;
        }

        .category-table-toolbar,
        .category-table-tools,
        .category-table-meta,
        .category-table-footer,
        .category-import-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .category-import-panel {
            display: flex;
        }

        .category-search {
            min-width: 100%;
        }

        .category-field.is-parent,
        .category-field.is-code,
        .category-field.is-name,
        .category-field.is-name-wide,
        .category-field.is-action {
            grid-column: 1 / -1;
        }

        .category-field.is-action {
            justify-self: start;
        }
    }

    @media (max-width: 575.98px) {
        .category-hero {
            padding: 1rem;
        }

        .category-hero h2 {
            font-size: 1.35rem;
        }

        .category-switcher a {
            width: 100%;
            justify-content: center;
        }

        .category-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .category-batch-row {
            padding: .75rem;
        }
    }
</style>
