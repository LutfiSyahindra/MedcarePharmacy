<style>
    .margin-page {
        --margin-primary: #d97706;
        --margin-primary-strong: #b45309;
        --margin-accent: #2563eb;
        --margin-success: #16a34a;
        --margin-danger: #dc2626;
        --margin-surface: #ffffff;
        --margin-soft: #fff7e8;
        --margin-soft-blue: #eef6ff;
        --margin-border: #dbe7f5;
        --margin-text: #172033;
        --margin-muted: #667085;
        color: var(--margin-text);
    }

    .margin-page .page-breadcrumb,
    .margin-page .breadcrumb {
        margin-bottom: 1rem;
    }

    .margin-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
        gap: 1rem;
        align-items: stretch;
        padding: 1.5rem;
        border-radius: 8px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(217, 119, 6, .96), rgba(37, 99, 235, .94)),
            linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
        background-size: auto, 32px 32px;
        box-shadow: 0 16px 40px rgba(217, 119, 6, .16);
        overflow: hidden;
    }

    .margin-kicker {
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

    .margin-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .margin-hero p {
        max-width: 620px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .margin-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .margin-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .margin-hero-actions .btn-light {
        color: var(--margin-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .margin-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .margin-flow-panel {
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

    .margin-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .margin-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .margin-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .margin-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .margin-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .margin-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        background: var(--margin-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .margin-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--margin-primary-strong);
        background: var(--margin-soft);
        font-size: 1.35rem;
    }

    .margin-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .margin-stat span {
        display: block;
        color: var(--margin-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .margin-stat small {
        display: block;
        color: var(--margin-muted);
        line-height: 1.35;
    }

    .margin-priority-section {
        margin: 1rem 0;
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        background: var(--margin-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .margin-priority-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--margin-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .margin-priority-header .btn,
    .margin-priority-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .margin-priority-form {
        padding: 1rem;
    }

    .margin-priority-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
    }

    .margin-priority-item {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr) auto;
        gap: .75rem;
        align-items: center;
        min-height: 88px;
        padding: .85rem;
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        background: #fff;
    }

    .margin-priority-number {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--margin-primary), var(--margin-accent));
        font-weight: 900;
    }

    .margin-priority-body .form-label {
        margin-bottom: .35rem;
        color: var(--margin-muted);
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .margin-priority-select {
        min-height: 42px;
        border-color: var(--margin-border);
        border-radius: 8px;
        font-weight: 700;
    }

    .margin-priority-controls {
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .margin-priority-move {
        display: grid;
        width: 34px;
        height: 34px;
        padding: 0;
        place-items: center;
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        color: var(--margin-accent);
        background: var(--margin-soft-blue);
        font-size: 1rem;
        line-height: 1;
    }

    .margin-priority-move:hover:not(:disabled) {
        color: #fff;
        border-color: var(--margin-accent);
        background: var(--margin-accent);
    }

    .margin-priority-move:disabled {
        opacity: .38;
        cursor: not-allowed;
    }

    .margin-priority-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--margin-border);
    }

    #marginPrioritySummary {
        color: var(--margin-muted);
        font-size: .86rem;
        font-weight: 700;
    }

    .margin-table-section {
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        background: var(--margin-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .margin-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--margin-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .margin-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .margin-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--margin-accent);
        background: var(--margin-soft-blue);
        font-size: 1.25rem;
    }

    .margin-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .margin-table-title p {
        margin: .1rem 0 0;
        color: var(--margin-muted);
    }

    .margin-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .margin-search {
        display: flex;
        align-items: center;
        min-width: min(360px, 48vw);
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .margin-search i {
        padding-left: .85rem;
        color: var(--margin-muted);
        font-size: 1.05rem;
    }

    .margin-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--margin-text);
    }

    .margin-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .margin-table {
        margin-bottom: .5rem !important;
        color: var(--margin-text);
    }

    .margin-table thead th {
        border-bottom: 0 !important;
        color: var(--margin-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .margin-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .margin-table tbody tr:hover,
    .margin-table tbody tr.is-selected {
        background: #fffaf0;
    }

    .margin-reference {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 210px;
    }

    .margin-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--margin-primary), var(--margin-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .margin-reference strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .margin-reference small,
    .margin-muted {
        color: var(--margin-muted);
    }

    .margin-factor-badge,
    .margin-tier-badge,
    .margin-percent-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .margin-factor-badge {
        color: var(--margin-primary-strong);
        background: var(--margin-soft);
    }

    .margin-tier-badge {
        color: var(--margin-accent);
        background: var(--margin-soft-blue);
    }

    .margin-percent-badge {
        color: var(--margin-success);
        background: #eaf8ef;
    }

    .margin-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .margin-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .margin-status-text.is-active {
        color: var(--margin-success);
    }

    .margin-status-text.is-inactive {
        color: var(--margin-muted);
    }

    .margin-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .margin-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--margin-success);
    }

    .margin-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .margin-action-btn {
        display: inline-grid !important;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 8px !important;
        border: 1px solid transparent !important;
        padding: 0 !important;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .margin-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .margin-action-edit {
        color: var(--margin-accent) !important;
        background: var(--margin-soft-blue) !important;
    }

    .margin-action-delete {
        color: var(--margin-danger) !important;
        background: #fff0f0 !important;
    }

    .margin-table-meta,
    .margin-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--margin-muted);
    }

    .margin-table-footer {
        border-top: 1px solid var(--margin-border);
    }

    .margin-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--margin-border);
    }

    .margin-table-footer .pagination {
        margin-bottom: 0;
    }

    .margin-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--margin-primary-strong);
        border-color: var(--margin-border);
    }

    .margin-table-footer .active .page-link {
        color: #fff;
        background: var(--margin-primary);
        border-color: var(--margin-primary);
    }

    .margin-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--margin-primary-strong);
        font-weight: 700;
    }

    .margin-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .margin-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--margin-primary), var(--margin-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .margin-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .margin-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .margin-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .margin-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .margin-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .margin-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .margin-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .margin-field {
        margin-bottom: 1rem;
    }

    .margin-field.is-wide {
        grid-column: 1 / -1;
    }

    .margin-field .form-label {
        margin-bottom: .45rem;
        color: var(--margin-text);
        font-weight: 800;
    }

    .margin-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .margin-input-shell:focus-within {
        border-color: rgba(217, 119, 6, .45);
        box-shadow: 0 0 0 .2rem rgba(217, 119, 6, .08);
    }

    .margin-input-shell .margin-input-icon {
        display: grid;
        width: 42px;
        place-items: center;
        color: var(--margin-muted);
        font-size: 1.05rem;
    }

    .margin-input-shell .form-control,
    .margin-input-shell .form-select {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .margin-input-shell .form-control.is-invalid,
    .margin-input-shell .form-select.is-invalid {
        background-image: none;
    }

    .margin-field.has-error .invalid-feedback {
        display: block;
    }

    .margin-form-hint {
        display: block;
        margin-top: .35rem;
        color: var(--margin-muted);
        font-size: .78rem;
    }

    .margin-reference-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
        margin-bottom: .75rem;
        padding: .75rem;
        border: 1px solid var(--margin-border);
        border-radius: 8px;
        background: #fff;
    }

    .margin-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--margin-primary-strong);
        background: var(--margin-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .margin-reference-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }

    .margin-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--margin-border);
    }

    .margin-modal .modal-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .margin-page .select2-container--default .select2-selection--multiple,
    .margin-page .select2-container--default .select2-selection--single {
        min-height: 46px;
        border-color: var(--margin-border);
        border-radius: 8px;
        padding: .25rem .35rem;
    }

    .margin-page .select2-container--default.select2-container--focus .select2-selection--multiple,
    .margin-page .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: rgba(217, 119, 6, .45);
        box-shadow: 0 0 0 .2rem rgba(217, 119, 6, .08);
    }

    .margin-page .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border: 0;
        border-radius: 999px;
        color: var(--margin-primary-strong);
        background: var(--margin-soft);
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .margin-hero {
            grid-template-columns: 1fr;
        }

        .margin-stats-grid {
            grid-template-columns: 1fr;
        }

        .margin-priority-grid {
            grid-template-columns: 1fr;
        }

        .margin-table-toolbar,
        .margin-priority-header,
        .margin-priority-footer,
        .margin-table-tools,
        .margin-table-meta,
        .margin-table-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .margin-search {
            min-width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .margin-hero {
            padding: 1rem;
        }

        .margin-hero h2 {
            font-size: 1.35rem;
        }

        .margin-form-grid {
            grid-template-columns: 1fr;
        }

        .margin-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }
    }
</style>
