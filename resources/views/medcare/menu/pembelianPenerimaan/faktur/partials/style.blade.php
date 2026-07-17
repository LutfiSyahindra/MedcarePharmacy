<style>
    .invoice-page {
        --invoice-ink: #172033;
        --invoice-muted: #667085;
        --invoice-border: #dbe7f5;
        --invoice-surface: #ffffff;
        --invoice-teal: #0f766e;
        --invoice-blue: #2563eb;
        --invoice-green: #16a34a;
        --invoice-amber: #d97706;
        --invoice-red: #dc2626;
        --invoice-cyan: #0891b2;
        --invoice-soft-green: #ecfdf3;
        --invoice-soft-blue: #eef6ff;
        --invoice-soft-amber: #fff7e8;
        --invoice-soft-red: #fff1f2;
        --invoice-soft-cyan: #ecfeff;
    }

    .invoice-command-center {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(280px, 380px);
        gap: .9rem;
        align-items: stretch;
        padding: 1.15rem;
        border: 1px solid rgba(15, 118, 110, .18);
        border-radius: 8px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(15, 118, 110, .98), rgba(37, 99, 235, .94));
        box-shadow: 0 16px 38px rgba(15, 118, 110, .16);
        overflow: hidden;
    }

    .invoice-command-copy {
        min-width: 0;
    }

    .invoice-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .34rem .68rem;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 999px;
        background: rgba(255, 255, 255, .13);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .invoice-command-center h2 {
        margin: .75rem 0 .25rem;
        color: #fff;
        font-size: 1.55rem;
        font-weight: 900;
        line-height: 1.2;
        letter-spacing: 0;
    }

    .invoice-command-center p {
        max-width: 620px;
        margin: 0;
        color: rgba(255, 255, 255, .78);
        line-height: 1.5;
    }

    .invoice-health-panel {
        display: grid;
        align-content: center;
        gap: .8rem;
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 8px;
        background: rgba(255, 255, 255, .12);
    }

    .invoice-health-copy span,
    .invoice-health-copy small {
        display: block;
        color: rgba(255, 255, 255, .72);
    }

    .invoice-health-copy span {
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .invoice-health-copy strong {
        display: block;
        overflow-wrap: anywhere;
        margin-top: .16rem;
        color: #fff;
        font-size: 1.42rem;
        font-weight: 900;
        line-height: 1.16;
    }

    .invoice-health-copy small {
        margin-top: .2rem;
    }

    .invoice-health-meter,
    .invoice-inline-meter,
    .invoice-row-meter,
    .invoice-payment-meter {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .22);
        overflow: hidden;
    }

    .invoice-health-meter span,
    .invoice-inline-meter span,
    .invoice-row-meter span,
    .invoice-payment-meter span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #22c55e, #67e8f9);
        transition: width .22s ease;
    }

    .invoice-stats-grid {
        margin-top: 1rem;
    }

    .invoice-insight-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
        margin: 0 0 1rem;
    }

    .invoice-insight-card {
        display: flex;
        gap: .78rem;
        align-items: center;
        min-width: 0;
        padding: .95rem;
        border: 1px solid var(--invoice-border);
        border-radius: 8px;
        background: linear-gradient(180deg, #fff, #f8fbff);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
    }

    .invoice-insight-icon {
        display: grid;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        place-items: center;
        border-radius: 8px;
        font-size: 1.25rem;
    }

    .invoice-insight-icon.is-paid {
        color: var(--invoice-green);
        background: var(--invoice-soft-green);
    }

    .invoice-insight-icon.is-soon {
        color: var(--invoice-amber);
        background: var(--invoice-soft-amber);
    }

    .invoice-insight-icon.is-open {
        color: var(--invoice-blue);
        background: var(--invoice-soft-blue);
    }

    .invoice-insight-card small,
    .invoice-insight-card span {
        display: block;
        color: var(--invoice-muted);
    }

    .invoice-insight-card small {
        font-size: .73rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .invoice-insight-card strong {
        display: block;
        overflow-wrap: anywhere;
        margin-top: .12rem;
        color: var(--invoice-ink);
        font-size: 1.2rem;
        font-weight: 900;
        line-height: 1.15;
    }

    .invoice-insight-card span {
        margin-top: .16rem;
        line-height: 1.35;
    }

    .invoice-filter-bar {
        align-items: flex-start;
    }

    .invoice-due-filter {
        flex: 1 1 320px;
    }

    .invoice-due-chip {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        border: 1px solid var(--invoice-border);
        border-radius: 999px;
        padding: .38rem .68rem;
        color: var(--invoice-muted);
        background: #fff;
        font-size: .78rem;
        font-weight: 800;
        transition: color .16s ease, border-color .16s ease, background .16s ease, transform .16s ease;
    }

    .invoice-due-chip:hover {
        color: var(--invoice-blue);
        border-color: rgba(37, 99, 235, .35);
        transform: translateY(-1px);
    }

    .invoice-due-chip.is-active {
        color: #fff;
        border-color: var(--invoice-blue);
        background: var(--invoice-blue);
        box-shadow: 0 7px 18px rgba(37, 99, 235, .14);
    }

    .invoice-table .invoice-reference-stack,
    .invoice-table .invoice-money-stack,
    .invoice-table .invoice-progress-stack {
        display: grid;
        gap: .18rem;
        min-width: 0;
    }

    .invoice-reference-stack strong,
    .invoice-money-stack strong {
        color: var(--invoice-ink);
        font-weight: 900;
    }

    .invoice-reference-stack small,
    .invoice-money-stack small,
    .invoice-progress-stack small {
        color: var(--invoice-muted);
        line-height: 1.25;
    }

    .invoice-due-status,
    .invoice-payment-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .4rem .62rem;
        font-size: .76rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .invoice-due-status.is-lunas,
    .invoice-payment-badge.is-lunas {
        color: var(--invoice-green);
        background: var(--invoice-soft-green);
    }

    .invoice-due-status.is-overdue {
        color: var(--invoice-red);
        background: var(--invoice-soft-red);
    }

    .invoice-due-status.is-due-soon,
    .invoice-payment-badge.is-sebagian {
        color: var(--invoice-amber);
        background: var(--invoice-soft-amber);
    }

    .invoice-due-status.is-not-due {
        color: var(--invoice-cyan);
        background: var(--invoice-soft-cyan);
    }

    .invoice-due-status.is-no-due,
    .invoice-payment-badge.is-belum-dibayar {
        color: var(--invoice-muted);
        background: #f2f4f7;
    }

    .invoice-due-status.is-cancelled {
        color: var(--invoice-red);
        background: var(--invoice-soft-red);
    }

    .invoice-row-meter {
        height: 7px;
        min-width: 120px;
        background: #edf2f7;
    }

    .invoice-row-meter span {
        background: linear-gradient(90deg, var(--invoice-teal), var(--invoice-green));
    }

    .invoice-action-group {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .invoice-action-btn {
        display: inline-grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 8px;
        padding: 0;
        font-size: 1rem;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .invoice-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .12);
    }

    .invoice-action-btn.is-detail {
        color: var(--invoice-cyan);
        background: var(--invoice-soft-cyan);
    }

    .invoice-action-btn.is-edit {
        color: var(--invoice-blue);
        background: var(--invoice-soft-blue);
    }

    .invoice-action-btn.is-paid {
        color: var(--invoice-green);
        background: var(--invoice-soft-green);
    }

    .invoice-action-btn.is-reset {
        color: var(--invoice-amber);
        background: var(--invoice-soft-amber);
    }

    .invoice-detail-body,
    .invoice-payment-body {
        background: #f7f9fc;
    }

    .invoice-detail-overview,
    .invoice-payment-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        margin-bottom: .9rem;
    }

    .invoice-payment-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .invoice-detail-card,
    .invoice-payment-summary > div,
    .invoice-info-panel,
    .invoice-items-panel,
    .invoice-payment-meter-card,
    .invoice-calculation-panel,
    .invoice-note-panel {
        border: 1px solid var(--invoice-border);
        border-radius: 8px;
        background: var(--invoice-surface);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .045);
    }

    .invoice-detail-card,
    .invoice-payment-summary > div {
        min-width: 0;
        padding: .9rem;
    }

    .invoice-detail-card small,
    .invoice-payment-summary small {
        display: block;
        color: var(--invoice-muted);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .invoice-detail-card strong,
    .invoice-payment-summary strong {
        display: block;
        overflow-wrap: anywhere;
        margin-top: .18rem;
        color: var(--invoice-ink);
        font-size: 1.12rem;
        font-weight: 900;
        line-height: 1.18;
    }

    .invoice-detail-card span {
        display: block;
        margin-top: .22rem;
        color: var(--invoice-muted);
        line-height: 1.3;
    }

    .invoice-detail-card.is-main {
        border-color: rgba(15, 118, 110, .24);
        background: linear-gradient(180deg, #fff, #f0fdfa);
    }

    .invoice-inline-meter {
        height: 7px;
        margin-top: .48rem;
        background: #e8eef6;
    }

    .invoice-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: .8rem;
        margin-bottom: .9rem;
    }

    .invoice-info-panel,
    .invoice-items-panel,
    .invoice-payment-meter-card,
    .invoice-note-panel {
        padding: .95rem;
    }

    .invoice-section-heading {
        display: flex;
        gap: .65rem;
        align-items: center;
        margin-bottom: .75rem;
    }

    .invoice-section-heading > span {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 8px;
        color: var(--invoice-blue);
        background: var(--invoice-soft-blue);
        font-size: 1.15rem;
    }

    .invoice-section-heading strong,
    .invoice-section-heading small {
        display: block;
    }

    .invoice-section-heading strong {
        color: var(--invoice-ink);
        font-weight: 900;
    }

    .invoice-section-heading small {
        color: var(--invoice-muted);
    }

    .invoice-info-list,
    .invoice-money-list,
    .invoice-calculation-panel {
        display: grid;
        gap: .6rem;
    }

    .invoice-info-list > div,
    .invoice-money-list > div,
    .invoice-calculation-panel > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .62rem .7rem;
        border-radius: 8px;
        background: #f8fafc;
    }

    .invoice-info-list span,
    .invoice-money-list span,
    .invoice-calculation-panel span {
        color: var(--invoice-muted);
        font-size: .82rem;
        font-weight: 700;
    }

    .invoice-info-list strong,
    .invoice-money-list strong,
    .invoice-calculation-panel strong {
        color: var(--invoice-ink);
        font-weight: 900;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .invoice-money-list .is-emphasis,
    .invoice-calculation-panel .is-total {
        background: var(--invoice-soft-green);
    }

    .invoice-item-table {
        margin-bottom: 0;
    }

    .invoice-item-table thead th {
        border-bottom: 0;
        color: var(--invoice-muted);
        background: #f5f8fc;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .invoice-item-table td {
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .invoice-note-panel {
        display: flex;
        gap: .65rem;
        align-items: flex-start;
        margin-top: .9rem;
        color: var(--invoice-muted);
    }

    .invoice-note-panel span {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 8px;
        color: var(--invoice-teal);
        background: #f0fdfa;
        font-size: 1rem;
    }

    .invoice-note-panel p {
        margin: .25rem 0 0;
        line-height: 1.5;
    }

    .invoice-payment-meter-card {
        margin-bottom: .9rem;
    }

    .invoice-payment-meter-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .55rem;
    }

    .invoice-payment-meter-head span {
        color: var(--invoice-teal);
        font-size: 1.15rem;
        font-weight: 900;
    }

    .invoice-payment-meter-head strong {
        color: var(--invoice-ink);
        font-weight: 900;
        text-align: right;
    }

    .invoice-payment-meter {
        height: 10px;
        background: #e8eef6;
    }

    .invoice-payment-shortcuts {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .75rem;
    }

    .invoice-shortcut-btn {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        border: 1px solid var(--invoice-border);
        border-radius: 8px;
        padding: .48rem .7rem;
        color: var(--invoice-ink);
        background: #fff;
        font-weight: 800;
        transition: color .16s ease, border-color .16s ease, background .16s ease, transform .16s ease;
    }

    .invoice-shortcut-btn:hover {
        color: var(--invoice-teal);
        border-color: rgba(15, 118, 110, .35);
        background: #f0fdfa;
        transform: translateY(-1px);
    }

    .invoice-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .8rem;
        margin-bottom: .9rem;
    }

    .invoice-field .form-label {
        color: var(--invoice-ink);
        font-size: .82rem;
        font-weight: 800;
    }

    .invoice-field .form-control,
    .invoice-field .input-group-text {
        border-color: var(--invoice-border);
    }

    .invoice-field .input-group-text {
        color: var(--invoice-teal);
        background: #f8fafc;
        font-weight: 800;
    }

    .invoice-calculation-panel {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: .9rem;
        padding: .8rem;
    }

    .invoice-calculation-panel > div {
        display: grid;
        justify-items: start;
        min-width: 0;
    }

    .invoice-calculation-panel strong {
        margin-top: .15rem;
        text-align: left;
    }

    @media (max-width: 1199.98px) {
        .invoice-command-center,
        .invoice-detail-grid {
            grid-template-columns: 1fr;
        }

        .invoice-detail-overview {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .invoice-insight-strip,
        .invoice-payment-summary,
        .invoice-calculation-panel {
            grid-template-columns: 1fr;
        }

        .invoice-form-grid {
            grid-template-columns: 1fr;
        }

        .invoice-filter-bar {
            align-items: stretch;
        }

        .invoice-due-filter,
        .invoice-filter-bar .purchase-filter-controls {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .invoice-command-center {
            padding: .95rem;
        }

        .invoice-detail-overview {
            grid-template-columns: 1fr;
        }

        .invoice-command-center h2 {
            font-size: 1.32rem;
        }

        .invoice-health-copy strong,
        .invoice-insight-card strong {
            font-size: 1.08rem;
        }
    }
</style>
