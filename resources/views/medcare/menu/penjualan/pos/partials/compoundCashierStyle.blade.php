<style>
    /*
     * Racikan uses the same quiet cashier shell as non-racikan.
     * Only the controls required to define and save a compound are added.
     */
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription {
        --rx-compound-accent: var(--rx-primary);
        --rx-compound-soft: #edf5f2;
        --rx-compound-line: var(--rx-standard-border);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-prescription-modal-editor {
        display: grid;
        grid-template-areas:
            "preparation-header"
            "compound-controls"
            "preparation-content";
        grid-template-columns: minmax(0, 1fr);
        grid-template-rows: auto auto minmax(0, 1fr);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-rx-compound-slot {
        display: block !important;
        min-width: 0;
        min-height: 0;
        grid-area: compound-controls;
        padding: 9px 12px;
        overflow: visible;
        border: 0;
        border-bottom: 1px solid var(--rx-compound-line);
        background: #fff;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-rx-compound-slot .pos-compound-setup {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: center;
        gap: 8px 12px;
        margin: 0;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-steps,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-control > small,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-save-rule,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-summary {
        display: none !important;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-controls {
        display: grid;
        min-width: 0;
        grid-template-columns: minmax(280px, 1fr);
        align-items: end;
        gap: 8px;
        padding: 0;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-control {
        display: grid;
        min-width: 0;
        gap: 4px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-control > span {
        color: #65726c;
        font-size: 9px;
        font-weight: 700;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-control > div {
        display: grid;
        grid-template-columns: minmax(130px, 1fr) auto;
        gap: 7px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription #activeCompoundGroup,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-new-compound-group {
        width: auto;
        min-height: 36px;
        height: 36px;
        border-color: var(--rx-standard-border-strong);
        border-radius: 8px;
        box-shadow: none;
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-new-compound-group {
        padding: 6px 9px;
        color: var(--rx-primary);
        background: var(--rx-compound-soft);
        white-space: nowrap;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-cart-wrap {
        margin: 0 !important;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-row {
        margin-bottom: 10px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-card {
        overflow: hidden;
        border: 1px solid var(--rx-standard-border-strong);
        border-top: 1px solid var(--rx-standard-border-strong);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 7px 18px rgba(23, 48, 39, .045);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-card.is-active {
        border-color: #a9cabc;
        box-shadow: 0 0 0 2px rgba(24, 91, 73, .06);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-card.is-saved {
        border-color: #bad8cc;
        background: #fff;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-head {
        min-height: 54px;
        grid-template-columns: 56px minmax(0, 1fr) auto auto;
        gap: 8px;
        padding: 8px 10px;
        border-bottom: 1px solid var(--rx-compound-line);
        background: #fbfcfb;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-card.is-saved .pos-compound-group-head {
        background: #f7fbf9;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-mark {
        width: 56px;
        min-width: 56px;
        height: 34px;
        padding: 0 7px;
        border-radius: 8px;
        background: var(--rx-primary);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-mark small {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-mark b {
        font-size: 10px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-section-label,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-copy small,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-footer > span {
        display: none !important;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-copy strong {
        color: #31423a;
        font-size: 12px !important;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-save-state,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-use-compound-group {
        min-height: 28px;
        padding: 4px 7px;
        border-radius: 7px;
        font-size: 8px !important;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 9px;
        padding: 11px;
        background: #fff;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields > label:nth-child(n) {
        grid-column: auto;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields label > span:first-child,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-prescription-item-grid.is-compound-component label > span {
        min-height: 0;
        color: #65726c;
        font-size: 9px !important;
        font-weight: 650;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields .form-control,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields .form-select,
    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields .input-group-text {
        min-height: 38px;
        height: 38px;
        border-color: var(--rx-standard-border-strong);
        border-radius: 8px;
        background-color: #fff;
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-auto-strip {
        min-height: 34px;
        margin: 0 11px 10px;
        padding: 6px 8px;
        color: #65766e;
        border-color: var(--rx-compound-line);
        border-radius: 8px;
        background: var(--rx-standard-soft);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-auto-strip > span {
        width: 24px;
        height: 24px;
        color: var(--rx-primary);
        background: var(--rx-compound-soft);
        font-size: 13px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-auto-strip b {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-auto-strip small {
        font-size: 8px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-footer {
        justify-content: flex-end;
        padding: 8px 10px;
        border-top-color: var(--rx-compound-line);
        background: #fbfcfb;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-save-compound-group {
        min-width: 130px;
        min-height: 34px;
        padding: 6px 10px;
        border-radius: 8px;
        background: var(--rx-primary);
        box-shadow: none;
        font-size: 9px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-save-compound-group.is-edit {
        color: var(--rx-primary);
        border-color: #bdd7cc;
        background: var(--rx-compound-soft);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-product-row .pos-item-group-badge {
        color: var(--rx-primary);
        border-color: #c9ddd5;
        background: var(--rx-compound-soft);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-product-row + .pos-prescription-detail-row .pos-compound-component-editor {
        border-top: 0;
        border-left: 1px solid var(--rx-standard-border-strong);
        border-radius: 0 0 12px 12px;
        background: #fbfcfb;
        box-shadow: 0 8px 18px rgba(23, 48, 39, .045);
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-calculation-note {
        margin-top: 8px;
        padding: 6px 8px;
        border-radius: 7px;
        font-size: 8px;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-workspace-empty {
        display: grid;
        min-height: 210px;
        grid-template-columns: 46px minmax(0, 280px);
        place-content: center;
        text-align: left;
        background: transparent;
    }

    .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-workspace-empty .pos-empty-search-button {
        grid-column: 2;
        justify-self: start;
    }

    @media (max-width: 1220px) {
        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields,
        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-prescription-item-grid.is-compound-component {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-rx-compound-slot .pos-compound-setup {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 720px) {
        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-controls,
        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-fields,
        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-prescription-item-grid.is-compound-component {
            grid-template-columns: minmax(0, 1fr);
        }

        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-compound-group-head {
            grid-template-columns: 52px minmax(0, 1fr) auto;
        }

        .rx-simple .pos-prescription-cashier-step.is-compound-prescription .pos-use-compound-group {
            display: none;
        }
    }
</style>
