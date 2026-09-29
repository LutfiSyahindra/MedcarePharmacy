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

    .obat-filter-panel {
        padding: 1rem;
        border-bottom: 1px solid var(--obat-border);
        background: #f8fbff;
    }

    .obat-filter-panel.has-active-filter {
        background: linear-gradient(180deg, #f0fdfa, #f8fbff);
        box-shadow: inset 3px 0 0 var(--obat-primary);
    }

    .obat-filter-mobile-toggle {
        display: none;
    }

    .obat-filter-heading,
    .obat-filter-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }

    .obat-filter-kicker {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: var(--obat-primary-strong);
        font-size: .8rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .obat-filter-heading p {
        margin: .2rem 0 0;
        color: var(--obat-muted);
        font-size: .82rem;
    }

    .obat-filter-status > span {
        padding: .35rem .6rem;
        border-radius: 999px;
        color: var(--obat-muted);
        background: #fff;
        font-size: .76rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .obat-filter-panel.has-active-filter .obat-filter-status > span {
        color: var(--obat-primary-strong);
        background: var(--obat-soft);
    }

    .obat-filter-status .btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .obat-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        margin-top: .85rem;
    }

    .obat-filter-field {
        display: grid;
        gap: .4rem;
        min-width: 0;
        margin: 0;
    }

    .obat-filter-field > span {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: var(--obat-text);
        font-size: .78rem;
        font-weight: 800;
    }

    .obat-filter-field > span i {
        color: var(--obat-primary);
        font-size: .95rem;
    }

    .obat-filter-field .select2-container {
        width: 100% !important;
    }

    .obat-filter-field .select2-container--default .select2-selection--single {
        height: 42px;
        border-color: var(--obat-border);
        border-radius: 8px;
        background: #fff;
    }

    .obat-filter-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: .75rem;
        padding-right: 2rem;
        color: var(--obat-text);
        font-size: .82rem;
        line-height: 40px;
    }

    .obat-filter-field .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: .3rem;
    }

    .obat-filter-field .select2-container--default.select2-container--disabled .select2-selection--single {
        background: #f1f5f9;
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

        .obat-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .obat-hero {
            grid-template-columns: 1fr;
        }

        .obat-stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .obat-table-toolbar,
        .obat-table-tools,
        .obat-filter-heading,
        .obat-filter-status,
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

        .obat-filter-heading,
        .obat-filter-status {
            align-items: stretch;
            flex-direction: column;
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

    @media (max-width: 767.98px) {
        .obat-page {
            min-width: 0;
        }

        .obat-page .page-breadcrumb,
        .obat-page .breadcrumb {
            margin-bottom: .75rem;
        }

        .obat-page .breadcrumb {
            flex-wrap: nowrap;
            overflow: hidden;
            font-size: .76rem;
            white-space: nowrap;
        }

        .obat-page .breadcrumb-item {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .obat-hero {
            gap: 0;
            padding: 1rem;
            border-radius: 12px;
            background:
                radial-gradient(circle at 100% 0, rgba(255, 255, 255, .18), transparent 42%),
                linear-gradient(135deg, rgba(15, 118, 110, .98), rgba(37, 99, 235, .95));
        }

        .obat-kicker {
            padding: .3rem .55rem;
            font-size: .68rem;
        }

        .obat-hero h2 {
            margin-top: .65rem;
            font-size: 1.4rem;
        }

        .obat-hero p {
            margin-bottom: .85rem;
            font-size: .82rem;
            line-height: 1.55;
        }

        .obat-flow-panel {
            display: none;
        }

        .obat-hero-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
        }

        .obat-hero-actions .btn {
            min-height: 44px;
            justify-content: center;
            padding: .65rem .6rem;
            font-size: .78rem;
        }

        .obat-hero-actions .btn:first-child {
            grid-column: 1 / -1;
        }

        .obat-stats-grid {
            gap: .45rem;
            margin: .75rem 0;
        }

        .obat-stat {
            min-width: 0;
            min-height: 108px;
            flex-direction: column;
            justify-content: center;
            gap: .4rem;
            padding: .65rem .3rem;
            text-align: center;
            border-radius: 10px;
        }

        .obat-stat-icon {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
            border-radius: 9px;
            font-size: 1rem;
        }

        .obat-stat > div {
            min-width: 0;
        }

        .obat-stat strong {
            font-size: 1.05rem;
        }

        .obat-stat span {
            font-size: .62rem;
            line-height: 1.25;
        }

        .obat-stat small {
            display: none;
        }

        .obat-table-section {
            border-radius: 12px;
            overflow: visible;
        }

        .obat-table-toolbar {
            gap: .75rem;
            padding: .85rem;
            border-radius: 12px 12px 0 0;
        }

        .obat-table-title {
            align-items: flex-start;
        }

        .obat-table-title-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
        }

        .obat-table-title h5 {
            font-size: .95rem;
        }

        .obat-table-title p {
            margin-top: .15rem;
            font-size: .72rem;
            line-height: 1.4;
        }

        .obat-search {
            min-height: 46px;
            border-radius: 10px;
        }

        .obat-search input {
            min-width: 0;
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 16px;
        }

        .obat-filter-panel {
            padding: .85rem;
        }

        .obat-filter-mobile-toggle {
            display: flex;
            width: 100%;
            min-height: 46px;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin: 0;
            padding: .7rem .8rem;
            border: 1px solid var(--obat-border);
            border-radius: 10px;
            color: var(--obat-primary-strong);
            background: #fff;
            font-size: .8rem;
            font-weight: 800;
        }

        .obat-filter-mobile-toggle > span {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
        }

        .obat-filter-mobile-chevron {
            font-size: 1.05rem;
            transition: transform .18s ease;
        }

        .obat-filter-panel.is-mobile-expanded .obat-filter-mobile-chevron {
            transform: rotate(180deg);
        }

        .obat-filter-heading {
            display: block;
        }

        .obat-filter-heading > div:first-child {
            display: none;
        }

        .obat-filter-status {
            flex-direction: row;
            align-items: center;
            gap: .5rem;
            margin-top: .65rem;
        }

        .obat-filter-status > span {
            min-width: 0;
            flex: 1 1 auto;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .obat-filter-status .btn {
            min-height: 44px;
            justify-content: center;
        }

        .obat-filter-grid {
            grid-template-columns: 1fr;
            gap: .65rem;
            margin-top: .75rem;
        }

        .obat-filter-panel:not(.is-mobile-expanded) .obat-filter-grid {
            display: none;
        }

        .obat-filter-field .select2-container--default .select2-selection--single {
            height: 46px;
            border-radius: 10px;
        }

        .obat-filter-field .select2-container--default .select2-selection--single .select2-selection__rendered {
            font-size: .78rem;
            line-height: 44px;
        }

        .obat-filter-field .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
        }

        .obat-table-section .table-responsive {
            overflow: visible;
            padding: .75rem;
            background: #f4f8fc;
        }

        .obat-table-section .dataTables_wrapper,
        .obat-table-section .dataTables_scroll,
        .obat-table-section .dataTables_scrollBody {
            width: 100%;
            min-width: 0;
        }

        .obat-table-section .dataTables_scrollHead {
            display: none !important;
        }

        .obat-table-section .dataTables_scrollBody {
            overflow: visible !important;
            border: 0 !important;
        }

        .obat-table-section .dataTables_scrollBody > table,
        .obat-table {
            display: block;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
        }

        .obat-table thead {
            display: none;
        }

        .obat-table tbody {
            display: grid;
            width: 100%;
            gap: .75rem;
        }

        .obat-table tbody tr:not(.obat-detail-row) {
            display: grid;
            width: 100%;
            min-width: 0;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            grid-template-areas:
                "identity identity identity identity identity identity"
                "code code code type type type"
                "category category category category category category"
                "stock stock stock price price price"
                "status status status status status status"
                "detail detail action action action action";
            gap: .55rem;
            padding: .75rem;
            border: 1px solid var(--obat-border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .055);
        }

        .obat-table tbody tr:not(.obat-detail-row):hover,
        .obat-table tbody tr:not(.obat-detail-row).is-selected,
        #tableObat tbody tr.shown {
            background: #fff;
        }

        .obat-table tbody tr:not(.obat-detail-row).is-selected {
            border-color: rgba(37, 99, 235, .42);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, .08), 0 8px 22px rgba(15, 23, 42, .055);
        }

        .obat-table tbody tr:not(.obat-detail-row) > td {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            gap: .35rem;
            padding: .6rem !important;
            border: 0 !important;
            border-radius: 9px;
            background: #f8fbff !important;
            white-space: normal !important;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td::before {
            color: var(--obat-muted);
            font-size: .62rem;
            font-weight: 800;
            letter-spacing: .035em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(1) {
            grid-area: detail;
            padding: 0 !important;
            background: transparent !important;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(2) {
            grid-area: action;
            padding: 0 !important;
            background: transparent !important;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(3) {
            display: none;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(4) {
            grid-area: code;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(4)::before {
            content: "Kode";
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(5) {
            grid-area: identity;
            padding: .1rem .1rem .65rem !important;
            border-bottom: 1px solid var(--obat-border) !important;
            border-radius: 0;
            background: #fff !important;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(6) {
            grid-area: category;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(6)::before {
            content: "Kategori / Golongan";
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(7) {
            grid-area: stock;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(7)::before {
            content: "Stok minimum";
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(8) {
            grid-area: price;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(8)::before {
            content: "Harga beli";
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(9) {
            grid-area: type;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(9)::before {
            content: "Jenis";
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(10) {
            grid-area: status;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(10)::before {
            content: "Status data";
        }

        .obat-table tbody tr:not(.obat-detail-row) > td.dataTables_empty {
            display: flex;
            min-height: 120px;
            grid-area: 1 / 1 / auto / -1;
            align-items: center;
            justify-content: center;
            color: var(--obat-muted);
            text-align: center;
        }

        .obat-identity {
            width: 100%;
            min-width: 0;
        }

        .obat-identity > div {
            min-width: 0;
        }

        .obat-identity strong {
            max-width: none;
            font-size: .92rem;
        }

        .obat-avatar {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
        }

        .obat-code-badge,
        .obat-tag-badge,
        .obat-money-badge,
        .obat-stock-badge,
        .obat-type-badge {
            max-width: 100%;
            padding: .35rem .5rem;
            font-size: .7rem;
            white-space: normal;
            word-break: break-word;
        }

        .obat-mini-stack,
        .obat-price-stack {
            width: 100%;
            min-width: 0;
        }

        .obat-mini-stack strong,
        .obat-mini-stack small {
            overflow-wrap: anywhere;
        }

        .dataTables_wrapper .dataTable th.obat-sticky-detail,
        .dataTables_wrapper .dataTable td.obat-sticky-detail,
        .dataTables_wrapper .dataTable th.obat-sticky-action,
        .dataTables_wrapper .dataTable td.obat-sticky-action,
        .dataTables_wrapper .dataTable th.obat-sticky-no,
        .dataTables_wrapper .dataTable td.obat-sticky-no {
            position: static;
            box-shadow: none;
        }

        .obat-detail-toggle,
        .obat-action-btn {
            width: 100% !important;
            min-width: 0;
            height: 44px;
            gap: .3rem;
            font-size: .72rem;
        }

        .obat-detail-toggle {
            display: flex !important;
        }

        .obat-detail-toggle::after {
            content: "Detail";
            font-weight: 800;
        }

        .obat-detail-toggle.is-open::after {
            content: "Tutup";
        }

        .obat-action-group {
            display: grid;
            width: 100%;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .45rem;
        }

        .obat-action-btn {
            display: flex !important;
            justify-content: center;
        }

        .obat-action-edit::after {
            content: "Edit";
            font-weight: 800;
        }

        .obat-action-delete::after {
            content: "Hapus";
            font-weight: 800;
        }

        #tableObat tbody tr.obat-detail-row {
            display: block;
            width: 100%;
        }

        #tableObat tbody tr.obat-detail-row td {
            display: block;
            width: 100%;
            padding: 0 !important;
            background: transparent !important;
        }

        .obat-detail-panel {
            gap: .65rem;
            padding: .7rem;
            border-radius: 12px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
        }

        .obat-detail-group h6 {
            padding: .7rem;
            font-size: .8rem;
        }

        .obat-detail-grid {
            gap: .5rem;
            padding: .65rem;
        }

        .obat-detail-item {
            padding: .6rem;
        }

        .obat-table-meta,
        .obat-table-footer {
            gap: .65rem;
            padding: .75rem;
            text-align: center;
        }

        .obat-table-meta .dataTables_length,
        .obat-table-footer .dataTables_info,
        .obat-table-footer .dataTables_paginate {
            width: 100%;
        }

        .obat-table-meta .dataTables_length label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
        }

        .obat-table-meta select {
            min-height: 42px;
        }

        .obat-table-footer .dataTables_paginate {
            overflow-x: auto;
            padding-bottom: .1rem;
        }

        .obat-table-footer .pagination {
            min-width: max-content;
            justify-content: center;
        }

        .obat-table-footer .page-link {
            min-width: 44px;
            min-height: 44px;
            display: grid;
            place-items: center;
        }

        .obat-modal .modal-dialog {
            width: 100%;
            max-width: none;
            min-height: 100%;
            margin: 0;
        }

        .obat-modal .modal-content {
            min-height: 100vh;
            min-height: 100dvh;
            border-radius: 0;
        }

        .obat-modal .modal-header {
            flex: 0 0 auto;
            padding: .85rem;
        }

        .obat-modal .modal-title-wrap {
            min-width: 0;
            gap: .65rem;
        }

        .obat-modal .modal-title-wrap > div {
            min-width: 0;
        }

        .obat-modal .modal-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
        }

        .obat-modal .btn-close {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            margin: -.35rem -.35rem 0 0;
            padding: .8rem;
            background-size: .8rem;
        }

        .obat-modal .modal-title {
            font-size: .95rem;
        }

        .obat-modal .modal-subtitle {
            font-size: .7rem;
            line-height: 1.35;
        }

        .obat-modal .modal-body {
            padding: .85rem;
        }

        .obat-form-grid {
            gap: .7rem;
        }

        .obat-form-section {
            border-radius: 10px;
        }

        .obat-section-header {
            padding: .72rem;
        }

        .obat-section-header i {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
        }

        .obat-section-header strong {
            font-size: .82rem;
        }

        .obat-section-header small {
            font-size: .68rem;
            line-height: 1.35;
        }

        .obat-section-body {
            padding: .75rem;
        }

        .obat-fields {
            gap: .7rem;
        }

        .obat-field .form-label {
            margin-bottom: .35rem;
            font-size: .76rem;
        }

        .obat-input-shell {
            min-height: 46px;
            border-radius: 10px;
        }

        .obat-input-icon {
            width: 40px;
            flex-basis: 40px;
        }

        .obat-input-shell .form-control,
        .obat-input-shell .form-select {
            min-height: 44px;
            font-size: 16px;
        }

        .obat-field .select2-container--default .select2-selection--single {
            min-height: 44px;
        }

        .obat-field .select2-container--default .select2-selection--single .select2-selection__rendered {
            max-width: calc(100vw - 105px);
            line-height: 44px;
        }

        .obat-field .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
        }

        .obat-modal .modal-footer {
            position: sticky;
            bottom: -.85rem;
            z-index: 8;
            flex-wrap: nowrap;
            gap: .6rem;
            margin: .8rem -.85rem -.85rem;
            padding: .75rem .85rem calc(.75rem + env(safe-area-inset-bottom));
            border-top: 1px solid var(--obat-border);
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 -10px 24px rgba(15, 23, 42, .07);
            backdrop-filter: blur(8px);
        }

        .obat-modal .modal-footer .btn {
            min-height: 46px;
            flex: 1 1 0;
            justify-content: center;
        }

        .obat-import-panel {
            gap: .75rem;
            padding: .75rem;
        }

        .obat-import-panel .btn {
            min-height: 44px;
            justify-content: center;
        }

        .obat-page .dropify-wrapper {
            min-height: 180px;
        }

        .select2-container--open {
            max-width: calc(100vw - 1.7rem);
        }

        .select2-search--dropdown .select2-search__field {
            min-height: 44px;
            border-radius: 8px;
            font-size: 16px;
        }

        .select2-results__option {
            padding: .72rem .75rem;
            font-size: .82rem;
            line-height: 1.35;
        }
    }

    @media (max-width: 575.98px) {
        .obat-table-section .table-responsive {
            padding: .65rem;
        }

        .obat-filter-grid {
            grid-template-columns: 1fr;
        }

        .obat-modal .modal-body {
            padding: .75rem;
        }

        .obat-modal .modal-footer {
            bottom: -.75rem;
            margin-right: -.75rem;
            margin-bottom: -.75rem;
            margin-left: -.75rem;
            padding-right: .75rem;
            padding-left: .75rem;
        }

        .obat-import-steps {
            padding-left: 1rem;
            font-size: .74rem;
        }
    }

    @media (max-width: 359.98px) {
        .obat-hero-actions {
            grid-template-columns: 1fr;
        }

        .obat-hero-actions .btn:first-child {
            grid-column: auto;
        }

        .obat-stat span {
            font-size: .58rem;
        }

        .obat-table tbody tr:not(.obat-detail-row) {
            grid-template-areas:
                "identity identity identity identity identity identity"
                "code code code code code code"
                "type type type status status status"
                "category category category category category category"
                "stock stock stock price price price"
                "detail detail action action action action";
        }

        .obat-table tbody tr:not(.obat-detail-row) > td:nth-child(10) {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .obat-page *,
        .obat-page *::before,
        .obat-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
        }
    }
</style>
