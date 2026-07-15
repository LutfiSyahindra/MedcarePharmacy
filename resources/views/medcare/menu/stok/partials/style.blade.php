<style>
    .stock-page {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .stock-toolbar,
    .stock-filter-bar,
    .stock-table-section,
    .stock-modal-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .stock-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
    }

    .stock-toolbar-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 0;
    }

    .stock-toolbar-title-icon,
    .stock-stat-icon,
    .stock-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 8px;
        color: #fff;
        background: #0f766e;
    }

    .stock-toolbar-title h4,
    .stock-section-title h5 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }

    .stock-toolbar-title p,
    .stock-section-title p {
        margin: .15rem 0 0;
        color: #64748b;
        font-size: .82rem;
    }

    .stock-toolbar-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .stock-live-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: .45rem .65rem;
        color: #475569;
        background: #f8fafc;
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .stock-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }

    .stock-stat {
        display: flex;
        align-items: center;
        gap: .8rem;
        min-width: 0;
        padding: 1rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .stock-stat-icon.is-blue {
        background: #2563eb;
    }

    .stock-stat-icon.is-green {
        background: #0f766e;
    }

    .stock-stat-icon.is-amber {
        background: #d97706;
    }

    .stock-stat-icon.is-red {
        background: #dc2626;
    }

    .stock-stat-copy {
        min-width: 0;
    }

    .stock-stat-copy strong {
        display: block;
        color: #0f172a;
        font-size: 1.25rem;
        line-height: 1.1;
    }

    .stock-stat-copy span {
        display: block;
        margin-top: .2rem;
        color: #475569;
        font-size: .82rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stock-insight-strip {
        display: grid;
        grid-template-columns: minmax(280px, 1.35fr) minmax(260px, .95fr) minmax(240px, .8fr);
        gap: .85rem;
        align-items: stretch;
    }

    .stock-insight-main,
    .stock-insight-metrics,
    .stock-filter-snapshot {
        min-width: 0;
        min-height: 104px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .stock-insight-main {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        align-items: center;
        gap: .8rem;
        padding: 1rem;
    }

    .stock-insight-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 8px;
        color: #0f766e;
        background: #ecfdf5;
        font-size: 1.35rem;
    }

    .stock-insight-icon.is-indigo {
        color: #4338ca;
        background: #eef2ff;
    }

    .stock-insight-copy {
        min-width: 0;
    }

    .stock-insight-copy small,
    .stock-filter-snapshot small,
    .stock-insight-metric small {
        display: block;
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .stock-insight-copy strong,
    .stock-filter-snapshot strong {
        display: block;
        margin-top: .15rem;
        color: #0f172a;
        font-size: .98rem;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stock-health-track,
    .stock-flow-track,
    .stock-progress {
        display: block;
        overflow: hidden;
        height: 7px;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .stock-health-track,
    .stock-flow-track {
        margin-top: .65rem;
    }

    .stock-health-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: #0f766e;
        transition: width .22s ease;
    }

    .stock-flow-track {
        display: flex;
    }

    .stock-flow-track span {
        display: block;
        height: 100%;
        transition: width .22s ease;
    }

    .stock-flow-track .is-in {
        background: #0f766e;
    }

    .stock-flow-track .is-out {
        background: #dc2626;
    }

    .stock-health-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        border-radius: 999px;
        padding: .4rem .7rem;
        color: #475569;
        background: #f1f5f9;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .stock-health-badge.is-safe {
        color: #047857;
        background: #ecfdf5;
    }

    .stock-health-badge.is-warning {
        color: #b45309;
        background: #fffbeb;
    }

    .stock-health-badge.is-danger {
        color: #b91c1c;
        background: #fef2f2;
    }

    .stock-insight-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
        padding: .75rem;
    }

    .stock-insight-metric {
        display: flex;
        min-width: 0;
        flex-direction: column;
        justify-content: center;
        border-radius: 8px;
        padding: .75rem;
        background: #f8fafc;
    }

    .stock-insight-metric strong {
        display: block;
        margin-top: .2rem;
        color: #0f172a;
        font-size: 1.12rem;
        font-weight: 800;
    }

    .stock-filter-snapshot {
        display: flex;
        min-width: 0;
        flex-direction: column;
        justify-content: center;
        padding: 1rem;
    }

    .stock-filter-snapshot span {
        display: block;
        margin-top: .2rem;
        color: #64748b;
        font-size: .82rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stock-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1rem;
        flex-wrap: wrap;
    }

    .stock-chip-group,
    .stock-filter-controls {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .stock-chip {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 8px;
        padding: .45rem .7rem;
        font-size: .82rem;
        font-weight: 600;
        line-height: 1.2;
        transition: .16s ease;
    }

    .stock-chip:hover,
    .stock-chip.is-active {
        border-color: #0f766e;
        background: #ecfdf5;
        color: #0f766e;
        box-shadow: 0 8px 18px rgba(15, 118, 110, .12);
    }

    .stock-search {
        display: flex;
        align-items: center;
        gap: .45rem;
        min-width: 280px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: .45rem .65rem;
        background: #fff;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .stock-search:focus-within {
        border-color: #0f766e;
        box-shadow: 0 0 0 .2rem rgba(15, 118, 110, .12);
    }

    .stock-search input {
        width: 100%;
        border: 0;
        outline: 0;
        font-size: .86rem;
        color: #0f172a;
    }

    .stock-search button {
        display: none;
        border: 0;
        background: transparent;
        color: #64748b;
        padding: 0;
    }

    .stock-search.has-value button {
        display: inline-flex;
    }

    .stock-table-section {
        overflow: hidden;
    }

    .stock-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .stock-section-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 0;
    }

    .stock-section-icon {
        background: #334155;
    }

    .stock-section-tools {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .stock-table-wrap {
        padding: 0 1rem 1rem;
    }

    .stock-page .dataTables_wrapper .row:first-child,
    .stock-page .dataTables_wrapper .row:last-child {
        align-items: center;
        row-gap: .75rem;
    }

    .stock-page .dataTables_wrapper .dataTables_length,
    .stock-page .dataTables_wrapper .dataTables_filter,
    .stock-page .dataTables_wrapper .dataTables_info {
        color: #64748b;
        font-size: .8rem;
        font-weight: 600;
    }

    .stock-page .dataTables_wrapper .dataTables_length select,
    .stock-page .dataTables_wrapper .dataTables_filter input,
    .stock-page .form-control,
    .stock-page .form-select,
    .stock-page .select2-container--default .select2-selection--single {
        border-color: #cbd5e1;
        border-radius: 8px;
    }

    .stock-page .select2-container--default .select2-selection--single {
        min-height: 38px;
        display: flex;
        align-items: center;
    }

    .stock-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155;
        line-height: 36px;
        font-size: .86rem;
        font-weight: 600;
    }

    .stock-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .stock-page .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        color: #475569 !important;
        background: #fff !important;
        margin: 0 .12rem;
        padding: .35rem .55rem;
        font-size: .8rem;
        font-weight: 700;
    }

    .stock-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .stock-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border-color: #0f766e !important;
        color: #0f766e !important;
        background: #ecfdf5 !important;
    }

    .stock-table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    .stock-table thead th {
        color: #475569;
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: 0;
        border-bottom: 1px solid #e5e7eb !important;
        white-space: nowrap;
    }

    .stock-table tbody td {
        vertical-align: middle;
        color: #334155;
        font-size: .86rem;
    }

    .stock-table tbody tr {
        transition: background-color .16s ease, box-shadow .16s ease;
    }

    .stock-table tbody tr:hover {
        background: #f8fafc;
    }

    .stock-table tbody tr.is-row-kosong,
    .stock-table tbody tr.is-row-expired,
    .stock-table tbody tr.is-row-keluar,
    .stock-table tbody tr.is-row-penyesuaian_keluar,
    .stock-table tbody tr.is-row-pembatalan_penerimaan {
        box-shadow: inset 3px 0 0 #ef4444;
    }

    .stock-table tbody tr.is-row-menipis,
    .stock-table tbody tr.is-row-akan_expired {
        box-shadow: inset 3px 0 0 #f59e0b;
    }

    .stock-table tbody tr.is-row-masuk,
    .stock-table tbody tr.is-row-penyesuaian_masuk,
    .stock-table tbody tr.is-row-saldo_awal {
        box-shadow: inset 3px 0 0 #10b981;
    }

    .stock-item {
        display: flex;
        flex-direction: column;
        gap: .15rem;
        min-width: 190px;
    }

    .stock-item strong {
        color: #0f172a;
        font-weight: 700;
    }

    .stock-item small {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: #64748b;
    }

    .stock-item small span {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .08rem .38rem;
        color: #475569;
        background: #f1f5f9;
        font-size: .7rem;
        font-weight: 700;
    }

    .stock-money,
    .stock-number {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: #0f172a;
        font-weight: 700;
        white-space: nowrap;
    }

    .stock-reason {
        display: inline-block;
        min-width: 220px;
        max-width: 360px;
        color: #334155;
        font-size: .82rem;
        font-weight: 600;
        line-height: 1.35;
        white-space: normal;
    }

    .stock-qty-value {
        display: inline-flex;
        align-items: baseline;
        gap: .35rem;
        color: #0f172a;
        white-space: nowrap;
    }

    .stock-qty-value i {
        align-self: center;
        color: #0f766e;
    }

    .stock-qty-value strong {
        font-size: .98rem;
        font-weight: 800;
    }

    .stock-qty-value small {
        color: #64748b;
        font-size: .74rem;
        font-weight: 700;
    }

    .stock-qty-stack {
        display: inline-flex;
        flex-direction: column;
        gap: .28rem;
        min-width: 132px;
    }

    .stock-qty-stack > small {
        color: #64748b;
        font-size: .72rem;
    }

    .stock-progress {
        width: 100%;
        height: 6px;
    }

    .stock-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .stock-progress.is-safe span {
        background: #0f766e;
    }

    .stock-progress.is-warning span {
        background: #d97706;
    }

    .stock-progress.is-danger span {
        background: #dc2626;
    }

    .stock-date-pill,
    .stock-date-time {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        min-width: 0;
        border-radius: 8px;
        padding: .42rem .55rem;
        background: #f8fafc;
        color: #334155;
        white-space: nowrap;
    }

    .stock-date-pill i,
    .stock-date-time i {
        color: #64748b;
    }

    .stock-date-pill strong,
    .stock-date-time strong {
        color: #0f172a;
        font-size: .8rem;
    }

    .stock-date-pill small {
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
    }

    .stock-date-pill.is-safe {
        background: #ecfdf5;
        color: #047857;
    }

    .stock-date-pill.is-safe i,
    .stock-date-pill.is-safe strong {
        color: #047857;
    }

    .stock-date-pill.is-warning {
        background: #fffbeb;
    }

    .stock-date-pill.is-warning i,
    .stock-date-pill.is-warning strong {
        color: #b45309;
    }

    .stock-date-pill.is-danger {
        background: #fef2f2;
    }

    .stock-date-pill.is-danger i,
    .stock-date-pill.is-danger strong {
        color: #b91c1c;
    }

    .stock-flow-number {
        display: inline-flex;
        align-items: center;
        gap: .32rem;
        border-radius: 999px;
        padding: .28rem .52rem;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .stock-flow-number.is-in {
        color: #047857;
        background: #ecfdf5;
    }

    .stock-flow-number.is-out {
        color: #b91c1c;
        background: #fef2f2;
    }

    .stock-flow-number.is-empty {
        color: #94a3b8;
        background: #f8fafc;
    }

    .stock-flow-number small {
        font-size: .7rem;
        font-weight: 700;
    }

    .stock-reference-pill,
    .stock-user-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        max-width: 180px;
        border-radius: 999px;
        padding: .32rem .55rem;
        color: #475569;
        background: #f1f5f9;
        font-size: .76rem;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stock-reference-pill {
        color: #1d4ed8;
        background: #eff6ff;
    }

    .stock-note-cell {
        display: block;
        max-width: 260px;
        color: #475569;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stock-status {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .32rem .55rem;
        font-size: .75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .stock-status.is-aman {
        background: #ecfdf5;
        color: #047857;
    }

    .stock-status.is-menipis,
    .stock-status.is-akan_expired {
        background: #fffbeb;
        color: #b45309;
    }

    .stock-status.is-expired,
    .stock-status.is-kosong {
        background: #fef2f2;
        color: #b91c1c;
    }

    .stock-status.is-masuk,
    .stock-status.is-penyesuaian_masuk {
        background: #ecfdf5;
        color: #047857;
    }

    .stock-status.is-saldo_awal {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .stock-status.is-keluar,
    .stock-status.is-expired,
    .stock-status.is-penyesuaian_keluar,
    .stock-status.is-pembatalan_penerimaan {
        background: #fef2f2;
        color: #b91c1c;
    }

    .stock-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        white-space: nowrap;
    }

    .stock-action-group .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        padding: 0;
        box-shadow: none;
    }

    .stock-batch-filter-note {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: #475569;
        font-size: .82rem;
        border-radius: 999px;
        padding: .32rem .55rem;
        background: #f8fafc;
        white-space: nowrap;
    }

    .stock-batch-filter-note strong {
        color: #0f172a;
    }

    .stock-modal-panel {
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8fafc;
    }

    .stock-modal-panel h6 {
        color: #0f172a;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    .stock-modal-panel p {
        color: #64748b;
        font-size: .82rem;
        margin-bottom: 0;
    }

    .stock-field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem;
    }

    .stock-field-grid .is-full {
        grid-column: 1 / -1;
    }

    .stock-field label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-bottom: .35rem;
        color: #334155;
        font-size: .78rem;
        font-weight: 700;
    }

    .stock-help {
        color: #64748b;
        font-size: .76rem;
        margin-top: .25rem;
    }

    .stock-mutation-modal .modal-dialog {
        max-width: min(1120px, calc(100vw - 1.5rem));
    }

    .stock-mutation-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 8px;
        max-height: calc(100dvh - 1.5rem);
        background: #f8fafc;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
    }

    .stock-mutation-form {
        display: flex;
        flex-direction: column;
        min-height: 0;
        max-height: calc(100dvh - 1.5rem);
    }

    .stock-mutation-header {
        flex: 0 0 auto;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: #0f766e;
        color: #fff;
        border-bottom: 0;
    }

    .stock-mutation-header .btn-close {
        width: 36px;
        height: 36px;
        margin: 0;
        border-radius: 8px;
        background-color: rgba(255, 255, 255, .86);
        opacity: 1;
    }

    .stock-mutation-title {
        display: flex;
        align-items: center;
        gap: .8rem;
        min-width: 0;
    }

    .stock-mutation-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 8px;
        color: #0f766e;
        background: #fff;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .18);
    }

    .stock-mutation-title h5 {
        margin: 0;
        font-weight: 800;
        color: #fff;
    }

    .stock-mutation-title p {
        margin: .18rem 0 0;
        color: rgba(255, 255, 255, .86);
        font-size: .84rem;
        line-height: 1.35;
    }

    .stock-mutation-body {
        display: grid;
        grid-template-columns: 290px minmax(0, 1fr);
        gap: 1rem;
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding: 1rem;
    }

    .stock-mutation-side,
    .stock-mutation-section {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }

    .stock-mutation-side {
        position: sticky;
        top: 0;
        align-self: start;
        display: flex;
        flex-direction: column;
        gap: .85rem;
        padding: .85rem;
    }

    .stock-mutation-mode {
        display: grid;
        grid-template-columns: 1fr;
        gap: .45rem;
    }

    .stock-mutation-mode-btn {
        display: flex;
        align-items: center;
        gap: .6rem;
        width: 100%;
        min-height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        color: #334155;
        padding: .55rem .65rem;
        text-align: left;
        font-size: .83rem;
        font-weight: 700;
        transition: .16s ease;
    }

    .stock-mutation-mode-btn i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        border-radius: 8px;
        color: #0f766e;
        background: #ecfdf5;
        font-size: 1rem;
    }

    .stock-mutation-mode-btn:hover,
    .stock-mutation-mode-btn.is-active {
        border-color: #0f766e;
        background: #ecfdf5;
        color: #0f766e;
    }

    .stock-mutation-mode-btn.is-danger i,
    .stock-mutation-mode-btn[data-mutasi="keluar"] i,
    .stock-mutation-mode-btn[data-mutasi="expired"] i,
    .stock-mutation-mode-btn[data-mutasi="penyesuaian_keluar"] i {
        color: #b91c1c;
        background: #fef2f2;
    }

    .stock-mutation-mode-btn[data-mutasi="keluar"].is-active,
    .stock-mutation-mode-btn[data-mutasi="expired"].is-active,
    .stock-mutation-mode-btn[data-mutasi="penyesuaian_keluar"].is-active {
        border-color: #dc2626;
        background: #fef2f2;
        color: #b91c1c;
    }

    .stock-mutation-impact {
        display: flex;
        gap: .7rem;
        padding: .8rem;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .stock-mutation-impact-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 8px;
        color: #fff;
        background: #0f766e;
    }

    .stock-mutation-impact small,
    .stock-mutation-mini small {
        display: block;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .stock-mutation-impact strong {
        display: block;
        color: #0f172a;
        font-size: .95rem;
        margin-top: .1rem;
    }

    .stock-mutation-impact p {
        margin: .2rem 0 0;
        color: #64748b;
        font-size: .78rem;
        line-height: 1.35;
    }

    .stock-mutation-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
    }

    .stock-mutation-mini {
        min-width: 0;
        padding: .75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
    }

    .stock-mutation-mini strong {
        display: block;
        margin-top: .18rem;
        color: #0f172a;
        font-size: .95rem;
        overflow-wrap: anywhere;
    }

    .stock-mutation-main {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
    }

    .stock-mutation-section {
        overflow: hidden;
    }

    .stock-mutation-section-title {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .85rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }

    .stock-mutation-section-title > i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 8px;
        color: #0f766e;
        background: #ecfdf5;
        font-size: 1rem;
    }

    .stock-mutation-section-title strong {
        display: block;
        color: #0f172a;
        font-size: .92rem;
    }

    .stock-mutation-section-title span {
        display: block;
        color: #64748b;
        font-size: .78rem;
        margin-top: .1rem;
    }

    .stock-mutation-section .stock-field-grid {
        padding: 1rem;
    }

    .stock-mutation-modal .form-control,
    .stock-mutation-modal .form-select,
    .stock-mutation-modal .select2-container--default .select2-selection--single {
        min-height: 42px;
        border-color: #cbd5e1;
        border-radius: 8px;
    }

    .stock-mutation-modal textarea.form-control {
        min-height: 94px;
        resize: vertical;
    }

    .stock-mutation-footer {
        position: sticky;
        bottom: 0;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        flex: 0 0 auto;
        padding: .85rem 1rem;
        border-top: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 -12px 24px rgba(15, 23, 42, .08);
    }

    .stock-mutation-footer-summary {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        min-width: 0;
        color: #475569;
        font-size: .82rem;
        font-weight: 700;
    }

    .stock-mutation-footer-summary span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stock-mutation-footer-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex: 0 0 auto;
    }

    .stock-mutation-footer-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        min-height: 40px;
        border-radius: 8px;
        font-weight: 700;
        white-space: nowrap;
    }

    .stock-refresh.is-loading i {
        animation: stock-spin .8s linear infinite;
    }

    @keyframes stock-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 1199px) {
        .stock-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .stock-insight-strip {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .stock-toolbar,
        .stock-filter-bar,
        .stock-section-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .stock-toolbar-actions,
        .stock-filter-controls,
        .stock-chip-group,
        .stock-section-tools {
            width: 100%;
            justify-content: flex-start;
        }

        .stock-live-chip {
            width: 100%;
            justify-content: center;
        }

        .stock-insight-main {
            grid-template-columns: 42px minmax(0, 1fr);
        }

        .stock-insight-icon {
            width: 42px;
            height: 42px;
            font-size: 1.15rem;
        }

        .stock-health-badge {
            grid-column: 1 / -1;
            justify-self: start;
        }

        .stock-chip-group {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .2rem;
        }

        .stock-chip {
            flex: 0 0 auto;
        }

        .stock-filter-controls > .form-select,
        .stock-filter-controls > .form-control,
        .stock-filter-controls > .select2-container {
            width: 100% !important;
            min-width: 100% !important;
        }

        .stock-stat-grid,
        .stock-field-grid {
            grid-template-columns: 1fr;
        }

        .stock-search {
            min-width: 100%;
        }

        .stock-table-wrap {
            padding: 0 .75rem .75rem;
        }

        .stock-batch-filter-note {
            max-width: 100%;
            white-space: normal;
        }

        .stock-mutation-modal .modal-dialog {
            width: 100%;
            max-width: 100%;
            height: 100dvh;
            margin: 0;
        }

        .stock-mutation-modal .modal-content,
        .stock-mutation-form {
            height: 100dvh;
            max-height: 100dvh;
            border-radius: 0;
        }

        .stock-mutation-header {
            padding: .85rem;
        }

        .stock-mutation-title-icon {
            width: 40px;
            height: 40px;
            flex-basis: 40px;
        }

        .stock-mutation-title h5 {
            font-size: 1rem;
        }

        .stock-mutation-title p {
            font-size: .78rem;
        }

        .stock-mutation-body {
            grid-template-columns: 1fr;
            gap: .8rem;
            padding: .8rem;
        }

        .stock-mutation-side {
            position: static;
        }

        .stock-mutation-mode {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .stock-mutation-mode-btn {
            min-height: 44px;
            font-size: .78rem;
        }

        .stock-mutation-mode-btn i {
            width: 26px;
            height: 26px;
            flex-basis: 26px;
        }

        .stock-mutation-section-title {
            align-items: flex-start;
            padding: .8rem;
        }

        .stock-mutation-section .stock-field-grid {
            padding: .8rem;
        }

        .stock-mutation-footer {
            align-items: stretch;
            flex-direction: column;
            padding: .75rem .8rem;
        }

        .stock-mutation-footer-summary {
            width: 100%;
        }

        .stock-mutation-footer-actions {
            width: 100%;
            display: grid;
            grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr);
        }

        .stock-mutation-footer-actions .btn {
            justify-content: center;
            width: 100%;
        }
    }

    @media (max-width: 420px) {
        .stock-insight-metrics {
            grid-template-columns: 1fr;
        }

        .stock-mutation-mode {
            grid-template-columns: 1fr;
        }

        .stock-mutation-mini-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
