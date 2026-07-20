<style>
    .return-action-group {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        padding: .24rem;
        border: 1px solid rgba(219, 231, 245, .95);
        border-radius: 999px;
        background: linear-gradient(180deg, #fff, #f8fbff);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        white-space: nowrap;
    }

    .return-action-btn {
        display: inline-grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 999px;
        padding: 0;
        background: transparent;
        color: var(--purchase-muted);
        transition:
            transform .16s ease,
            color .16s ease,
            background .16s ease,
            box-shadow .16s ease;
    }

    .return-action-btn i {
        font-size: 1.08rem;
        line-height: 1;
    }

    .return-action-btn:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 .18rem rgba(37, 99, 235, .18);
    }

    .return-action-btn:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    .return-action-btn.is-detail {
        color: var(--purchase-info);
    }

    .return-action-btn.is-post {
        color: var(--purchase-accent);
    }

    .return-action-btn.is-edit {
        color: var(--purchase-success);
    }

    .return-action-btn.is-cancel {
        color: var(--purchase-warning);
    }

    .return-action-btn.is-delete {
        color: var(--purchase-danger);
    }

    .return-action-btn.is-compensation {
        color: #7c3aed;
    }

    .return-action-btn.is-detail:hover {
        background: var(--purchase-info);
        box-shadow: 0 9px 18px rgba(8, 145, 178, .22);
    }

    .return-action-btn.is-post:hover {
        background: var(--purchase-accent);
        box-shadow: 0 9px 18px rgba(37, 99, 235, .22);
    }

    .return-action-btn.is-edit:hover {
        background: var(--purchase-success);
        box-shadow: 0 9px 18px rgba(22, 163, 74, .22);
    }

    .return-action-btn.is-cancel:hover {
        background: var(--purchase-warning);
        box-shadow: 0 9px 18px rgba(217, 119, 6, .2);
    }

    .return-action-btn.is-delete:hover {
        background: var(--purchase-danger);
        box-shadow: 0 9px 18px rgba(220, 38, 38, .2);
    }

    .return-action-btn.is-compensation:hover {
        color: #fff;
        background: #7c3aed;
        box-shadow: 0 9px 18px rgba(124, 58, 237, .22);
    }

    .return-action-btn:disabled,
    .return-action-btn.is-disabled {
        color: #a8b3c4;
        background: #f2f5f9;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .purchase-table td:last-child {
        text-align: right;
    }

    .purchase-table th:last-child {
        text-align: right;
    }

    .return-qty-control {
        display: grid;
        grid-template-columns: minmax(82px, .8fr) minmax(112px, 1.2fr) auto;
        align-items: center;
        gap: .4rem;
        min-width: 285px;
    }

    .return-qty-control .return-unit {
        min-width: 112px;
    }

    .compensation-status {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .12rem;
        width: max-content;
        padding: .42rem .72rem;
        border: 1px solid transparent;
        border-radius: .72rem;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }

    .compensation-status > span {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .compensation-status small {
        font-size: .66rem;
        font-weight: 600;
        opacity: .82;
    }

    .compensation-status.is-waiting {
        color: #a16207;
        border-color: #fde68a;
        background: #fffbeb;
    }

    .compensation-status.is-partial {
        color: #0369a1;
        border-color: #bae6fd;
        background: #f0f9ff;
    }

    .compensation-status.is-settled {
        color: #15803d;
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .compensation-status.is-overdue {
        color: #b91c1c;
        border-color: #fecaca;
        background: #fef2f2;
    }

    .compensation-status.is-neutral {
        color: #64748b;
        border-color: #e2e8f0;
        background: #f8fafc;
    }

    .compensation-plan-alert {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        padding: .8rem 1rem;
        border: 1px solid #bfdbfe;
        border-radius: .8rem;
        color: #1d4ed8;
        background: #eff6ff;
        font-size: .82rem;
    }

    .compensation-plan-alert i {
        font-size: 1.1rem;
    }

    .compensation-plan-alert.is-neutral {
        color: #475569;
        border-color: #e2e8f0;
        background: #f8fafc;
    }

    .compensation-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .8rem;
    }

    .compensation-summary-grid > div {
        display: flex;
        flex-direction: column;
        gap: .3rem;
        min-width: 0;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: .9rem;
        background: linear-gradient(180deg, #fff, #f8fafc);
    }

    .compensation-summary-grid span {
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .compensation-summary-grid strong {
        color: #0f172a;
        font-size: 1rem;
    }

    .compensation-summary-grid .is-outstanding {
        border-color: #fecaca;
        background: #fff7f7;
    }

    .compensation-summary-grid .is-outstanding strong {
        color: #b91c1c;
    }

    .compensation-note {
        padding: .75rem .9rem;
        border-left: 3px solid #94a3b8;
        color: #475569;
        background: #f8fafc;
        font-size: .84rem;
    }

    .compensation-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .55rem;
        padding: 1.6rem;
        color: #64748b;
        border: 1px dashed #cbd5e1;
        border-radius: .85rem;
        background: #f8fafc;
    }

    .is-disabled-section {
        opacity: .64;
    }

    .is-cancelled-entry {
        opacity: .62;
        text-decoration: line-through;
    }

    #returCompensationModal .modal-header {
        gap: 1rem;
    }

    #returCompensationModal .btn-close {
        margin-left: 0;
    }

    .purchase-stat.is-clickable {
        cursor: pointer;
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .purchase-stat.is-clickable:hover,
    .purchase-stat.is-clickable:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 18px 35px rgba(15, 23, 42, .12);
        outline: none;
    }

    .compensation-filter-group {
        padding-left: .8rem;
        border-left: 1px solid #e2e8f0;
    }

    @media (max-width: 991.98px) {
        .compensation-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .return-action-group {
            gap: .2rem;
            padding: .2rem;
        }

        .return-action-btn {
            width: 32px;
            height: 32px;
        }

        .compensation-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
