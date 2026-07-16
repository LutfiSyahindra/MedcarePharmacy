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

    @media (max-width: 575.98px) {
        .return-action-group {
            gap: .2rem;
            padding: .2rem;
        }

        .return-action-btn {
            width: 32px;
            height: 32px;
        }
    }
</style>
