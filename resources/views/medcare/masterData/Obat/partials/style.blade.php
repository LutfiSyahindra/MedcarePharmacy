<style>
    .obat-page {
        --obat-primary: #0f766e;
        --obat-primary-strong: #0d5f59;
        --obat-accent: #2563eb;
        --obat-warning: #d97706;
        --obat-success: #16a34a;
        --obat-danger: #dc2626;
        --obat-surface: #ffffff;
        --obat-soft: #e7f8f4;
        --obat-soft-blue: #eef6ff;
        --obat-soft-yellow: #fff7ed;
        --obat-border: #dbe7f5;
        --obat-text: #172033;
        --obat-muted: #667085;
        color: var(--obat-text);
    }

    .obat-page .page-breadcrumb,
    .obat-page .breadcrumb {
        margin-bottom: 1rem;
    }

    .obat-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(280px, 420px);
        gap: 1rem;
        align-items: stretch;
        padding: 1.5rem;
        border-radius: 8px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .97), rgba(37, 99, 235, .94)),
            linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
        background-size: auto, 34px 34px;
        box-shadow: 0 16px 40px rgba(15, 118, 110, .16);
        overflow: hidden;
    }

    .obat-kicker {
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

    .obat-hero h2 {
        margin: .8rem 0 .45rem;
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
    }

    .obat-hero p {
        max-width: 720px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, .78);
    }

    .obat-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
    }

    .obat-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .obat-hero-actions .btn-light {
        color: var(--obat-primary-strong);
        border: 0;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
    }

    .obat-hero-actions .btn-outline-light {
        border-color: rgba(255, 255, 255, .42);
        color: #fff;
    }

    .obat-flow-panel {
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

    .obat-flow-item {
        display: grid;
        grid-template-columns: 38px 1fr;
        gap: .75rem;
        align-items: center;
        padding: .65rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .obat-flow-item i {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 1.15rem;
    }

    .obat-flow-item span {
        display: block;
        color: #fff;
        font-weight: 800;
        line-height: 1.2;
    }

    .obat-flow-item small {
        display: block;
        margin-top: .12rem;
        color: rgba(255, 255, 255, .72);
    }

    .obat-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 1rem 0;
    }

    .obat-stat {
        display: flex;
        gap: .8rem;
        align-items: center;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: var(--obat-surface);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .obat-stat-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 8px;
        color: var(--obat-primary-strong);
        background: var(--obat-soft);
        font-size: 1.35rem;
    }

    .obat-stat strong {
        display: block;
        font-size: 1.35rem;
        line-height: 1.1;
    }

    .obat-stat span {
        display: block;
        color: var(--obat-muted);
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .obat-stat small {
        display: block;
        color: var(--obat-muted);
        line-height: 1.35;
    }

    .obat-table-section {
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: var(--obat-surface);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .obat-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid var(--obat-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .obat-table-title {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .obat-table-title-icon {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border-radius: 8px;
        color: var(--obat-accent);
        background: var(--obat-soft-blue);
        font-size: 1.25rem;
    }

    .obat-table-title h5 {
        margin: 0;
        font-weight: 800;
    }

    .obat-table-title p {
        margin: .1rem 0 0;
        color: var(--obat-muted);
    }

    .obat-table-tools {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .obat-search {
        display: flex;
        align-items: center;
        min-width: min(420px, 48vw);
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .obat-search i {
        padding-left: .85rem;
        color: var(--obat-muted);
        font-size: 1.05rem;
    }

    .obat-search input {
        width: 100%;
        border: 0;
        outline: 0;
        padding: .7rem .85rem;
        background: transparent;
        color: var(--obat-text);
    }

    .obat-table-section .table-responsive {
        padding: 0 1rem 1rem;
    }

    .obat-table {
        margin-bottom: .5rem !important;
        color: var(--obat-text);
    }

    .obat-table thead th {
        border-bottom: 0 !important;
        color: var(--obat-muted);
        background: #f5f8fc;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .obat-table tbody td {
        vertical-align: middle;
        border-color: #eef2f7;
        padding-top: .85rem;
        padding-bottom: .85rem;
        white-space: nowrap;
    }

    .obat-table tbody tr {
        transition: background .18s ease, box-shadow .18s ease;
    }

    .obat-table tbody tr:hover,
    .obat-table tbody tr.is-selected {
        background: #f4fbff;
    }

    .dataTables_wrapper .dataTable th.obat-sticky-detail,
    .dataTables_wrapper .dataTable td.obat-sticky-detail,
    .dataTables_wrapper .dataTable th.obat-sticky-action,
    .dataTables_wrapper .dataTable td.obat-sticky-action {
        position: sticky;
        z-index: 6;
        background: #fff;
        box-shadow: 4px 0 16px rgba(15, 23, 42, .05);
    }

    .dataTables_wrapper .dataTable th.obat-sticky-detail,
    .dataTables_wrapper .dataTable td.obat-sticky-detail {
        left: 0;
        min-width: 62px;
    }

    .dataTables_wrapper .dataTable th.obat-sticky-no,
    .dataTables_wrapper .dataTable td.obat-sticky-no {
        position: sticky;
        z-index: 5;
        background: #fff;
    }

    .obat-code-badge,
    .obat-tag-badge,
    .obat-money-badge,
    .obat-stock-badge,
    .obat-type-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
    }

    .obat-code-badge {
        color: var(--obat-primary-strong);
        background: var(--obat-soft);
    }

    .obat-tag-badge {
        color: var(--obat-accent);
        background: var(--obat-soft-blue);
    }

    .obat-money-badge {
        color: var(--obat-success);
        background: #ecfdf3;
    }

    .obat-stock-badge.is-safe {
        color: var(--obat-success);
        background: #ecfdf3;
    }

    .obat-stock-badge.is-low {
        color: var(--obat-warning);
        background: var(--obat-soft-yellow);
    }

    .obat-type-badge.is-generic {
        color: var(--obat-primary-strong);
        background: var(--obat-soft);
    }

    .obat-type-badge.is-paten {
        color: var(--obat-warning);
        background: var(--obat-soft-yellow);
    }

    .obat-identity {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 260px;
    }

    .obat-avatar {
        display: grid;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--obat-primary), var(--obat-accent));
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .obat-identity strong {
        display: block;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .obat-identity small,
    .obat-muted {
        color: var(--obat-muted);
    }

    .obat-mini-stack,
    .obat-price-stack {
        display: grid;
        gap: .18rem;
        min-width: 170px;
    }

    .obat-mini-stack strong,
    .obat-price-stack strong {
        color: var(--obat-text);
        font-weight: 800;
        line-height: 1.2;
    }

    .obat-mini-stack small,
    .obat-price-stack small {
        color: var(--obat-muted);
        line-height: 1.25;
    }

    .obat-status-wrap {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
    }

    .obat-status-text {
        font-size: .78rem;
        font-weight: 800;
    }

    .obat-status-text.is-active {
        color: var(--obat-success);
    }

    .obat-status-text.is-inactive {
        color: var(--obat-muted);
    }

    .obat-page .form-switch .form-check-input {
        width: 2.55rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #c7d5e8;
        background-color: #d7e1ee;
    }

    .obat-page .form-switch .form-check-input:checked {
        border-color: rgba(22, 163, 74, .35);
        background-color: var(--obat-success);
    }

    .obat-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .obat-action-btn {
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

    .obat-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .obat-detail-toggle {
        display: inline-grid !important;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 8px !important;
        border: 1px solid var(--obat-border) !important;
        padding: 0 !important;
        color: var(--obat-primary-strong) !important;
        background: var(--obat-soft) !important;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .obat-detail-toggle i {
        transition: transform .18s ease;
    }

    .obat-detail-toggle:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .obat-detail-toggle.is-open i {
        transform: rotate(180deg);
    }

    .obat-action-edit {
        color: var(--obat-accent) !important;
        background: var(--obat-soft-blue) !important;
    }

    .obat-action-delete {
        color: var(--obat-danger) !important;
        background: #fff0f0 !important;
    }

    .obat-table-meta,
    .obat-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        color: var(--obat-muted);
    }

    .obat-table-footer {
        border-top: 1px solid var(--obat-border);
    }

    .obat-table-meta select {
        margin: 0 .35rem;
        border-radius: 8px;
        border-color: var(--obat-border);
    }

    .obat-table-footer .pagination {
        margin-bottom: 0;
    }

    .obat-table-footer .page-link {
        border-radius: 8px;
        margin: 0 .12rem;
        color: var(--obat-primary-strong);
        border-color: var(--obat-border);
    }

    .obat-table-footer .active .page-link {
        color: #fff;
        background: var(--obat-primary);
        border-color: var(--obat-primary);
    }

    .obat-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--obat-primary-strong);
        font-weight: 800;
    }

    #tableObat tbody tr.shown {
        background: #f4fbff;
    }

    #tableObat tbody tr.obat-detail-row td {
        padding: 0 1rem 1rem !important;
        background: #f8fbff !important;
        border-top: 0;
        white-space: normal;
    }

    .obat-detail-panel {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem;
        padding: 1rem;
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .7);
    }

    .obat-detail-group {
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: linear-gradient(180deg, #fff, #fbfdff);
        overflow: hidden;
    }

    .obat-detail-group.is-wide {
        grid-column: 1 / -1;
    }

    .obat-detail-group h6 {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin: 0;
        padding: .75rem .85rem;
        border-bottom: 1px solid var(--obat-border);
        color: var(--obat-text);
        font-weight: 800;
    }

    .obat-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
        padding: .85rem;
    }

    .obat-detail-item {
        display: grid;
        gap: .25rem;
        min-width: 0;
        padding: .65rem;
        border-radius: 8px;
        background: #f8fbff;
    }

    .obat-detail-item span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: var(--obat-muted);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .obat-detail-item strong {
        color: var(--obat-text);
        line-height: 1.35;
        white-space: normal;
        word-break: break-word;
    }

    .obat-modal .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .2);
        overflow: hidden;
    }

    .obat-modal .modal-header {
        align-items: flex-start;
        gap: .85rem;
        color: #fff;
        background: linear-gradient(135deg, var(--obat-primary), var(--obat-accent));
        border-bottom: 0;
        padding: 1.1rem 1.25rem;
    }

    .obat-modal .modal-title-wrap {
        display: flex;
        gap: .8rem;
        align-items: center;
    }

    .obat-modal .modal-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .17);
        font-size: 1.25rem;
    }

    .obat-modal .modal-title {
        color: #fff;
        font-weight: 800;
    }

    .obat-modal .modal-subtitle {
        margin: .1rem 0 0;
        color: rgba(255, 255, 255, .74);
    }

    .obat-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: .8;
    }

    .obat-modal .modal-body {
        padding: 1.25rem;
        background: #f8fbff;
    }

    .obat-modal .modal-footer {
        margin-top: .25rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--obat-border);
    }

    .obat-modal .modal-footer .btn,
    .obat-import-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: .42rem;
        border-radius: 8px;
        font-weight: 800;
    }

    .obat-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem;
    }

    .obat-form-section {
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .obat-form-section.is-wide {
        grid-column: 1 / -1;
    }

    .obat-section-header {
        display: flex;
        gap: .7rem;
        align-items: center;
        padding: .85rem;
        border-bottom: 1px solid var(--obat-border);
        background: linear-gradient(180deg, #fff, #f8fbff);
    }

    .obat-section-header i {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border-radius: 8px;
        color: var(--obat-primary-strong);
        background: var(--obat-soft);
        font-size: 1.05rem;
    }

    .obat-section-header strong {
        display: block;
        font-weight: 800;
    }

    .obat-section-header small {
        display: block;
        color: var(--obat-muted);
    }

    .obat-section-body {
        padding: .9rem;
    }

    .obat-fields {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .8rem;
    }

    .obat-field {
        grid-column: span 6;
        margin-bottom: 0;
    }

    .obat-field.is-wide {
        grid-column: 1 / -1;
    }

    .obat-field.is-third {
        grid-column: span 4;
    }

    .obat-field .form-label {
        margin-bottom: .45rem;
        color: var(--obat-text);
        font-weight: 800;
    }

    .obat-input-shell {
        display: flex;
        align-items: center;
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .obat-input-shell:focus-within {
        border-color: rgba(15, 118, 110, .45);
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .08);
    }

    .obat-input-icon {
        display: grid;
        width: 42px;
        flex: 0 0 42px;
        place-items: center;
        color: var(--obat-muted);
        font-size: 1.05rem;
    }

    .obat-input-shell .form-control,
    .obat-input-shell .form-select {
        border: 0;
        box-shadow: none;
        background-color: transparent;
        padding-left: 0;
    }

    .obat-input-shell .form-control.is-invalid,
    .obat-input-shell .form-select.is-invalid {
        background-image: none;
    }

    .obat-field.has-error .invalid-feedback {
        display: block;
    }

    .obat-field .select2-container--default .select2-selection--single {
        min-height: 38px;
        border: 0;
        background: transparent;
        display: flex;
        align-items: center;
    }

    .obat-field .select2-container {
        width: 100% !important;
        flex: 1 1 auto;
    }

    .obat-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        color: var(--obat-text);
        line-height: 38px;
    }

    .obat-field .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }

    .obat-form-hint,
    .obat-import-steps {
        color: var(--obat-muted);
    }

    .obat-form-hint {
        display: block;
        margin-top: .35rem;
        font-size: .78rem;
    }

    .obat-import-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
        margin-bottom: .85rem;
        padding: .85rem;
        border: 1px solid var(--obat-border);
        border-radius: 8px;
        background: #fff;
    }

    .obat-import-steps {
        margin: .45rem 0 0;
        padding-left: 1.15rem;
    }

    .obat-import-steps li + li {
        margin-top: .2rem;
    }

    .obat-page .dropify-wrapper {
        border: 1px dashed rgba(15, 118, 110, .35);
        border-radius: 8px;
        background: #fff;
    }

    .obat-page .shake {
        animation: obat-shake .42s linear;
    }

    @keyframes obat-shake {
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

    @media (max-width: 1199.98px) {
        .obat-form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991.98px) {
        .obat-hero {
            grid-template-columns: 1fr;
        }

        .obat-stats-grid {
            grid-template-columns: 1fr;
        }

        .obat-table-toolbar,
        .obat-table-tools,
        .obat-table-meta,
        .obat-table-footer,
        .obat-import-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .obat-import-panel {
            display: flex;
        }

        .obat-search {
            min-width: 100%;
        }

        .obat-field,
        .obat-field.is-third {
            grid-column: 1 / -1;
        }

        .obat-detail-panel,
        .obat-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .obat-hero {
            padding: 1rem;
        }

        .obat-hero h2 {
            font-size: 1.35rem;
        }

        .obat-table-section .table-responsive {
            padding: 0 .75rem .75rem;
        }

        .obat-modal .modal-body {
            padding: 1rem;
        }
    }
</style>
