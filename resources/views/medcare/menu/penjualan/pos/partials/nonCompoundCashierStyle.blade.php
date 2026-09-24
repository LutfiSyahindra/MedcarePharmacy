<style>
    /*
     * Non-compound prescription refinement.
     * The standard flow is intentionally quieter than the compound workspace:
     * one identity card, one medicine card, and one persistent checkout action.
     */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] {
        --rx-standard-bg: #f4f6f5;
        --rx-standard-border: #dce4e0;
        --rx-standard-border-strong: #ccd8d2;
        --rx-standard-muted: #73817a;
        --rx-standard-surface: #ffffff;
        --rx-standard-soft: #f7f9f8;
        grid-template-rows: 68px minmax(0, 1fr) 70px;
        background: var(--rx-standard-bg);
    }

    /* A compact utility header: title, product search, and exit only. */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-header {
        min-height: 68px;
        grid-template-columns: minmax(190px, 250px) minmax(360px, 1fr) auto;
        gap: 14px;
        padding: 7px 14px;
        border-bottom-color: var(--rx-standard-border);
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 3px 14px rgba(22, 45, 37, .04);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-brand-mark {
        width: 38px !important;
        height: 38px !important;
        border-radius: 11px !important;
        box-shadow: none !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-brand-mark b {
        font-size: 18px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-brand {
        gap: 10px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-brand small,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-brand p,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-validation-state {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-brand strong {
        margin: 0;
        font-size: 16px !important;
        letter-spacing: -.02em;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search .pos-search-title {
        font-size: 12px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search .pos-search-box {
        min-height: 48px;
        grid-template-columns: minmax(82px, 100px) minmax(0, 1fr);
        gap: 8px;
        padding: 4px 5px 4px 9px;
        border-color: var(--rx-standard-border);
        border-radius: 11px;
        background: var(--rx-standard-soft);
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search .pos-search-title small {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search .pos-search-ready {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search .pos-search-control .select2-selection--single {
        height: 38px;
        min-height: 38px;
        border-color: var(--rx-standard-border-strong);
        border-radius: 9px;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search .pos-search-control .select2-selection__rendered {
        line-height: 36px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-cancel {
        min-height: 36px;
        padding: 6px 9px;
        color: #6f7874;
        border-color: var(--rx-standard-border);
        background: #fff;
    }

    /* The workspace uses two clear surfaces with more breathing room. */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-body {
        padding: 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-layout {
        grid-template-columns: minmax(330px, 360px) minmax(0, 1fr);
        gap: 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-work-panel {
        border-color: var(--rx-standard-border);
        border-radius: 14px;
        background: var(--rx-standard-surface);
        box-shadow: 0 8px 28px rgba(23, 48, 39, .055);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-header {
        position: sticky;
        top: 0;
        min-height: 58px;
        grid-template-columns: 28px minmax(0, 1fr) auto;
        gap: 9px;
        padding: 8px 11px;
        border-bottom-color: var(--rx-standard-border);
        background: rgba(255, 255, 255, .98);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-index {
        width: 28px;
        height: 28px;
        color: var(--rx-primary);
        border: 0;
        border-radius: 8px;
        background: #edf5f2;
        font-size: 10px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-copy small {
        color: #89948f;
        font-size: 8px !important;
        letter-spacing: .08em;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-copy strong {
        font-size: 14px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-copy p,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-live {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-state {
        padding: 4px 0;
        color: #a16e24;
        border: 0;
        background: transparent;
        font-size: 9px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details.is-complete .pos-rx-panel-state {
        color: var(--rx-primary);
        border: 0;
        background: transparent;
    }

    /* Patient and prescription data: no nested cards or repeated explanations. */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-transaction-form-shell {
        gap: 10px;
        padding: 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-form-grid::before {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-patient-search-field,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-form-grid > .pos-field:last-child {
        grid-column: 1 / -1;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-general-customer-button {
        min-height: 24px;
        padding: 3px 7px;
        color: var(--rx-primary);
        border: 0;
        background: transparent;
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-prescription-workspace {
        gap: 0;
        padding-top: 2px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-prescription-mode-summary {
        min-height: 36px;
        margin: 0;
        padding: 5px 0 7px;
        border: 0;
        border-bottom: 1px solid var(--rx-standard-border);
        border-radius: 0;
        background: transparent;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-mode-icon,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-mode-copy > small {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-mode-copy > strong {
        color: #41514a;
        font-size: 11px !important;
        font-weight: 650 !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-mode-copy > strong::before {
        display: inline-block;
        width: 6px;
        height: 6px;
        margin-right: 5px;
        border-radius: 50%;
        background: var(--rx-primary);
        content: "";
        vertical-align: 1px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-change-prescription-type {
        min-height: 28px;
        padding: 4px 7px;
        color: #64736c;
        border: 0;
        background: transparent;
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-prescription-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        padding: 10px 0 0;
        border: 0;
        border-radius: 0;
        background: transparent;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-field label {
        margin-bottom: 4px;
        color: #53615b;
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .form-control,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .form-select {
        min-height: 38px;
        border-color: var(--rx-standard-border-strong);
        border-radius: 9px;
        background: #fff;
        font-size: 11px !important;
    }

    /* The outer panel title is enough; clear stays in that header and no second toolbar row is rendered. */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor {
        position: relative;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor .pos-cart-toolbar {
        position: absolute;
        z-index: 8;
        top: 13px;
        right: 11px;
        min-height: 0;
        justify-content: flex-end;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        backdrop-filter: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor .pos-cart-title,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] #prescriptionToolbarPayBtn,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-live-label,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-footnote {
        display: none !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-clear-button {
        min-height: 32px;
        padding: 5px 8px;
        color: #986069;
        border-color: #eadde0;
        border-radius: 8px;
        background: #fff;
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor .pos-cart-wrap {
        min-height: 0;
        margin: 0;
        padding: 12px;
        border: 0;
        border-radius: 0;
        background: var(--rx-standard-soft);
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor .pos-cart-table tbody {
        gap: 0;
        padding: 0;
    }

    /* A product and its label read as one continuous card. */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row {
        grid-template-columns: minmax(190px, 1.55fr) minmax(140px, .82fr) minmax(220px, 1.25fr) minmax(120px, .62fr);
        gap: 10px;
        padding: 13px;
        border-color: var(--rx-standard-border);
        border-radius: 12px 12px 0 0;
        background: #fff;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td {
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0;
        background: transparent !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td::before {
        margin-bottom: 5px;
        color: #86928c;
        font-size: 8px;
        letter-spacing: .07em;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:nth-child(2)::before {
        content: "SATUAN";
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:nth-child(5)::before {
        content: "TOTAL";
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:first-child {
        grid-column: 1 / -1;
        padding-bottom: 11px !important;
        border-bottom: 1px solid #e7ece9 !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:nth-child(5) {
        display: grid;
        grid-column: auto;
        align-content: end;
        justify-content: stretch;
        padding: 0 !important;
        text-align: right;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:nth-child(5)::before {
        margin: 0 0 5px;
        text-align: right;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-item-symbol {
        width: 32px;
        height: 32px;
        color: var(--rx-primary);
        border: 0;
        border-radius: 9px;
        background: #edf5f2;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-item-copy strong {
        color: #26362f;
        font-size: 13px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-item-copy > small {
        color: var(--rx-standard-muted);
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-remove-item {
        width: 28px;
        height: 28px;
        color: #a15a64;
        border: 0;
        border-radius: 8px;
        background: #fff5f6;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-unit-editor {
        max-width: none;
        margin: 0;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-unit-editor select.form-select,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-qty-stepper,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-discount-control input.form-control {
        min-height: 38px;
        height: 38px;
        border-color: var(--rx-standard-border-strong);
        border-radius: 8px;
        background-color: #fff;
        font-size: 11px !important;
        font-weight: 500;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-qty-stepper {
        grid-template-columns: 34px minmax(48px, 1fr) 34px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-qty-stepper button {
        min-height: 36px;
        color: #69766f;
        background: #f7f9f8;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-qty-stepper input.form-control {
        height: 36px;
        border-radius: 0;
        font-size: 11px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-discount-editor {
        grid-template-columns: minmax(70px, .75fr) minmax(110px, 1.25fr);
        gap: 7px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-money {
        color: var(--rx-primary);
        font-size: 14px !important;
        font-weight: 750 !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-product-row + .pos-prescription-detail-row {
        margin-bottom: 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-editor {
        padding: 12px 13px 13px;
        border: 1px solid var(--rx-standard-border-strong);
        border-top: 0;
        border-left: 1px solid var(--rx-standard-border-strong);
        border-radius: 0 0 12px 12px;
        background: #fbfcfb;
        box-shadow: 0 8px 18px rgba(23, 48, 39, .045);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-editor.is-complete {
        border-color: #bcd8cc;
        border-top: 0;
        border-left: 1px solid #bcd8cc;
        background: #fbfdfc;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-head > span:first-child {
        width: 28px;
        height: 28px;
        color: var(--rx-primary);
        border-radius: 8px;
        background: #edf5f2;
        font-size: 14px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-head strong {
        color: #31423a;
        font-size: 11px !important;
        font-weight: 700 !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-head small {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-state {
        font-size: 9px !important;
        font-weight: 650;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-grid {
        grid-template-columns: minmax(240px, 1.55fr) minmax(180px, .95fr) minmax(110px, .56fr) minmax(190px, 1fr);
        gap: 9px;
        margin-top: 10px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-grid label > span {
        color: #65726c;
        font-size: 9px !important;
        font-weight: 650;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-grid .form-control,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-grid .form-select {
        min-height: 38px;
        border-color: var(--rx-standard-border-strong);
        border-radius: 8px;
        background: #fff;
        font-size: 11px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-signa-quick {
        gap: 5px;
        margin-top: 8px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-signa-quick > span {
        color: #8a958f;
        font-size: 8px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-signa-chip {
        padding: 3px 7px;
        color: #52675e;
        border-color: #d9e2de;
        background: #fff;
        font-size: 8px !important;
    }

    /* A restrained empty state keeps the next action obvious. */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-empty {
        min-height: 220px;
        gap: 7px;
        padding: 28px;
        background: transparent;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-empty-visual {
        width: 46px;
        height: 46px;
        margin: 0 0 3px;
        color: var(--rx-primary);
        border: 0;
        border-radius: 14px;
        background: #eaf3ef;
        box-shadow: none;
        font-size: 21px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-empty-visual > b,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-empty-meta {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-empty strong {
        color: #34453d;
        font-size: 13px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-empty > small {
        max-width: 360px;
        color: var(--rx-standard-muted);
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-empty-search-button {
        min-height: 34px;
        margin-top: 4px;
        padding: 6px 10px;
        border-radius: 8px;
        background: var(--rx-primary);
        box-shadow: none;
    }

    /* One fixed summary is the single source of truth for total and payment. */
    body.pos-fullscreen-mode .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] footer.pos-prescription-cashier-footer {
        min-height: 70px;
        padding: 8px 14px;
        border-top-color: var(--rx-standard-border);
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 -4px 16px rgba(22, 45, 37, .045);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-readiness > span {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 10px;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-readiness small,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-metrics small {
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-readiness strong {
        font-size: 12px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-metrics > span {
        padding: 6px 9px;
        border: 0;
        border-left: 1px solid var(--rx-standard-border);
        border-radius: 0;
        background: transparent;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-metrics strong {
        font-size: 13px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-draft-button {
        min-height: 40px;
        border-color: var(--rx-standard-border);
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-finish-prescription-button {
        min-width: 160px;
        min-height: 44px;
        border-radius: 10px;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-finish-prescription-button > span {
        font-size: 13px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-finish-prescription-button small {
        font-size: 10px !important;
    }

    @media (max-width: 1220px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:nth-child(5) {
            grid-column: 1 / -1;
            grid-template-columns: auto auto;
            align-items: center;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:nth-child(5)::before {
            margin: 0 auto 0 0;
            text-align: left;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] {
            height: 100dvh;
            grid-template-rows: auto minmax(0, 1fr) auto;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-header {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search {
            grid-column: 1 / -1;
            grid-row: 2;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-body {
            overflow: auto;
            padding: 8px;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-layout {
            grid-template-columns: minmax(0, 1fr);
            gap: 8px;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor {
            overflow: visible;
        }
    }

    @media (max-width: 620px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-form-grid,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details .pos-prescription-form-grid,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-item-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-cart-table .pos-product-row > td:nth-child(5) {
            grid-column: 1;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor .pos-cart-wrap {
            padding: 8px;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-panel-state,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-metrics,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-draft-button {
            display: none;
        }
    }
</style>
