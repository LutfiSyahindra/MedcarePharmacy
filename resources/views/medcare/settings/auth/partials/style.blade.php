<style>
    .auth-page {
        --auth-primary: #2563eb;
        --auth-primary-strong: #1d4ed8;
        --auth-accent: #0f766e;
        --auth-success: #16a34a;
        --auth-warning: #d97706;
        --auth-danger: #dc2626;
        --auth-surface: #ffffff;
        --auth-soft: #eef6ff;
        --auth-soft-accent: #e7f8f4;
        --auth-border: #dbe7f5;
        --auth-text: #172033;
        --auth-muted: #667085;
        color: var(--auth-text);
    }

    .auth-page .page-breadcrumb {
        margin-bottom: 1rem;
    }

    .auth-page .breadcrumb {
        margin-bottom: 0;
    }

    .auth-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
        gap: 1rem;
        align-items: stretch;
        padding: 1.5rem;
        border-radius: 8px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(37, 99, 235, .96), rgba(15, 118, 110, .94)),
            linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
        background-size: auto, 32px 32px;
        box-shadow: 0 16px 40px rgba(37, 99, 235, .16);
        overflow: hidden;
    }

    .auth-kicker {
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

    .auth-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .auth-hero p {
        max-width: 620px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .auth-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .auth-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .auth-hero-actions .btn-light {
        color: var(--auth-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .auth-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .auth-flow-panel {
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

    .auth-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .auth-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .auth-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .auth-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .auth-switcher {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        margin: 1rem 0;
    }

    .auth-switcher a {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .65rem .85rem;
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        color: var(--auth-muted);
        background: var(--auth-surface);
        font-weight: 700;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, color .18s ease;
    }

    .auth-switcher a:hover,
    .auth-switcher a.is-active {
        color: var(--auth-primary-strong);
        border-color: rgba(37, 99, 235, .28);
        background: var(--auth-soft);
        box-shadow: 0 10px 24px rgba(37, 99, 235, .1);
        transform: translateY(-1px);
    }

    .auth-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin-bottom: 1rem;
    }

    .auth-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        background: var(--auth-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .auth-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--auth-primary-strong);
        background: var(--auth-soft);
        font-size: 1.35rem;
    }

    .auth-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .auth-stat span {
        display: block;
        color: var(--auth-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .auth-stat small {
        display: block;
        color: var(--auth-muted);
        line-height: 1.35;
    }

    .auth-table-section {
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        background: var(--auth-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .auth-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--auth-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .auth-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .auth-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--auth-accent);
        background: var(--auth-soft-accent);
        font-size: 1.25rem;
    }

    .auth-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .auth-table-title p {
        margin: .1rem 0 0;
        color: var(--auth-muted);
    }

    .auth-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .auth-search {
        display: flex;
        align-items: center;
        min-width: min(340px, 48vw);
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .auth-search i {
        padding-left: .85rem;
        color: var(--auth-muted);
        font-size: 1.05rem;
    }

    .auth-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--auth-text);
    }

    .auth-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .auth-table {
        margin-bottom: .5rem !important;
        color: var(--auth-text);
    }

    .auth-table thead th {
        border-bottom: 0 !important;
        color: var(--auth-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .auth-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
    }

    .auth-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .auth-table tbody tr:hover,
    .auth-table tbody tr.is-selected {
        background: #f4f9ff;
    }

    .auth-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 190px;
    }

    .auth-avatar {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--auth-primary), var(--auth-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .auth-identity strong {
        display: block;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .auth-identity small,
    .auth-muted {
        color: var(--auth-muted);
    }

    .auth-email-link {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: var(--auth-primary-strong);
        font-weight: 600;
    }

    .auth-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .auth-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .auth-status-text.is-active {
        color: var(--auth-success);
    }

    .auth-status-text.is-inactive {
        color: var(--auth-muted);
    }

    .auth-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .auth-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--auth-success);
    }

    .auth-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .auth-action-btn {
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

    .auth-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .auth-action-edit {
        color: var(--auth-primary-strong) !important;
        background: #eaf1ff !important;
    }

    .auth-action-delete {
        color: var(--auth-danger) !important;
        background: #fff0f0 !important;
    }

    .auth-action-assign {
        color: var(--auth-warning) !important;
        background: #fff7e8 !important;
    }

    .user-opname-count {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid var(--auth-border);
        border-radius: 999px;
        padding: .35rem .65rem;
        color: var(--auth-muted);
        background: #f8fafc;
        font-size: .76rem;
        transition: border-color .16s ease, color .16s ease, background .16s ease, transform .16s ease;
    }

    .user-opname-count strong {
        color: inherit;
        font-size: .85rem;
    }

    .user-opname-count.has-transactions {
        border-color: rgba(37, 99, 235, .2);
        color: var(--auth-primary-strong);
        background: var(--auth-soft);
    }

    .user-opname-count:hover {
        transform: translateY(-1px);
        border-color: rgba(37, 99, 235, .38);
        color: var(--auth-primary-strong);
    }

    .user-opname-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .user-opname-summary article {
        display: flex;
        align-items: center;
        gap: .65rem;
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        padding: .8rem;
        background: #fff;
    }

    .user-opname-summary article > span {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: 8px;
        color: var(--auth-primary-strong);
        background: var(--auth-soft);
        font-size: 1.05rem;
    }

    .user-opname-summary strong,
    .user-opname-summary small {
        display: block;
    }

    .user-opname-summary strong {
        color: var(--auth-text);
        font-size: 1.1rem;
        line-height: 1.1;
    }

    .user-opname-summary small {
        margin-top: .15rem;
        color: var(--auth-muted);
    }

    .user-opname-state {
        display: flex;
        min-height: 180px;
        align-items: center;
        justify-content: center;
        gap: .55rem;
        color: var(--auth-primary-strong);
    }

    .user-opname-state.is-empty {
        flex-direction: column;
        text-align: center;
        color: var(--auth-muted);
    }

    .user-opname-state.is-empty i {
        font-size: 2.4rem;
        color: #94a3b8;
    }

    .user-opname-state.is-empty strong {
        color: var(--auth-text);
        font-size: 1rem;
    }

    .user-opname-table-wrap {
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        background: #fff;
    }

    .user-opname-primary,
    .user-opname-roles,
    .user-opname-activity {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: .25rem;
    }

    .user-opname-primary strong,
    .user-opname-activity strong {
        color: var(--auth-text);
    }

    .user-opname-primary small,
    .user-opname-roles small,
    .user-opname-activity small {
        color: var(--auth-muted);
    }

    .user-opname-activity span {
        display: block;
        max-width: 280px;
        overflow: hidden;
        color: var(--auth-muted);
        font-size: .75rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-opname-roles {
        max-width: 240px;
        flex-flow: row wrap;
    }

    .user-opname-roles small {
        width: 100%;
    }

    .user-opname-role,
    .user-opname-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .27rem .5rem;
        font-size: .7rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .user-opname-role {
        color: #5b21b6;
        background: #f3e8ff;
    }

    .user-opname-status.is-draft {
        color: #475569;
        background: #e2e8f0;
    }

    .user-opname-status.is-counting {
        color: #9a3412;
        background: #ffedd5;
    }

    .user-opname-status.is-waiting {
        color: #92400e;
        background: #fef3c7;
    }

    .user-opname-status.is-approved {
        color: #166534;
        background: #dcfce7;
    }

    .user-opname-status.is-completed {
        color: #1e40af;
        background: #dbeafe;
    }

    .auth-table-meta,
    .auth-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--auth-muted);
    }

    .auth-table-footer {
        border-top: 1px solid var(--auth-border);
    }

    .auth-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--auth-border);
    }

    .auth-table-footer .pagination {
        margin-bottom: 0;
    }

    .auth-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--auth-primary-strong);
        border-color: var(--auth-border);
    }

    .auth-table-footer .active .page-link {
        color: #fff;
        background: var(--auth-primary);
        border-color: var(--auth-primary);
    }

    .auth-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--auth-primary-strong);
        font-weight: 700;
    }

    .auth-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .auth-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--auth-primary), var(--auth-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .auth-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .auth-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .auth-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .auth-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .auth-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .auth-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .auth-field {
        margin-bottom: 1rem;
    }

    .auth-field .form-label {
        margin-bottom: .45rem;
        color: var(--auth-text);
        font-weight: 800;
    }

    .auth-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .auth-input-shell:focus-within {
        border-color: rgba(37, 99, 235, .45);
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .08);
    }

    .auth-input-shell .auth-input-icon {
        display: grid;
        width: 42px;
        place-items: center;
        color: var(--auth-muted);
        font-size: 1.05rem;
    }

    .auth-input-shell .form-control {
        border: 0;
        box-shadow: none;
        background: transparent;
        padding-left: 0;
    }

    .auth-input-shell .form-control.is-invalid {
        background-image: none;
    }

    .auth-input-shell:has(.is-invalid) {
        border-color: var(--auth-danger);
    }

    .auth-field.has-error .invalid-feedback,
    .auth-field .auth-input-shell:has(.is-invalid) + .invalid-feedback {
        display: block;
    }

    .auth-password-toggle {
        display: grid;
        width: 42px;
        height: 40px;
        place-items: center;
        border: 0;
        color: var(--auth-muted);
        background: transparent;
    }

    .auth-form-hint {
        display: block;
        margin-top: .35rem;
        color: var(--auth-muted);
        font-size: .78rem;
    }

    .auth-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--auth-border);
    }

    .auth-modal .modal-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .auth-select-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
        margin-bottom: .75rem;
        padding: .75rem;
        border: 1px solid var(--auth-border);
        border-radius: 8px;
        background: #fff;
    }

    .auth-select-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }

    .auth-count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .36rem .6rem;
        border-radius: 999px;
        color: var(--auth-primary-strong);
        background: var(--auth-soft);
        font-size: .78rem;
        font-weight: 800;
    }

    .auth-page .select2-container--default .select2-selection--multiple {
        min-height: 46px;
        border-color: var(--auth-border);
        border-radius: 8px;
        padding: .25rem .35rem;
    }

    .auth-page .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: rgba(37, 99, 235, .45);
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .08);
    }

    .auth-page .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border: 0;
        border-radius: 999px;
        color: var(--auth-primary-strong);
        background: var(--auth-soft);
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .auth-hero {
            grid-template-columns: 1fr;
        }

        .auth-stats-grid {
            grid-template-columns: 1fr;
        }

        .auth-table-toolbar,
        .auth-table-tools,
        .auth-table-meta,
        .auth-table-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .auth-search {
            min-width: 100%;
        }

        .user-opname-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .auth-hero {
            padding: 1rem;
        }

        .auth-hero h2 {
            font-size: 1.35rem;
        }

        .auth-switcher a {
            width: 100%;
            justify-content: center;
        }

        .auth-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .user-opname-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
