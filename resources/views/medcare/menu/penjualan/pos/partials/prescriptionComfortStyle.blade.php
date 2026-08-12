<style>
    /*
     * Comfort-first prescription workspace
     * One identity checkpoint, followed by one focused preparation area.
     */
    .rx-simple .pos-prescription-cashier-step {
        --rx-comfort-gap: 10px;
    }

    .rx-simple .pos-prescription-cashier-body {
        display: flex;
        align-items: stretch;
        padding: var(--rx-comfort-gap);
    }

    .rx-simple .pos-prescription-cashier-layout {
        display: grid;
        width: 100%;
        height: auto;
        min-height: 0;
        flex: 1 1 auto;
        grid-template-areas:
            "details editor"
            "catalog editor";
        grid-template-columns: minmax(292px, 324px) minmax(0, 1fr);
        grid-template-rows: minmax(320px, 1.75fr) minmax(175px, 1fr);
        gap: var(--rx-comfort-gap);
    }

    .rx-simple .pos-prescription-modal-details {
        grid-area: details;
        max-height: none;
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-width: none;
    }

    .rx-simple .pos-prescription-modal-details::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    .rx-simple .pos-prescription-modal-catalog {
        grid-area: catalog;
        min-height: 0;
        scrollbar-width: none;
    }

    .rx-simple .pos-prescription-modal-catalog::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    .rx-simple .pos-prescription-modal-editor {
        grid-area: editor;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
    }

    /* Identity stays available as a compact checkpoint beside the main work canvas. */
    .rx-simple .pos-prescription-modal-details .pos-rx-panel-header {
        min-height: 48px;
        padding: 6px 9px;
    }

    .rx-simple .pos-prescription-modal-details .pos-rx-panel-index {
        width: 32px;
        height: 32px;
    }

    .rx-simple .pos-prescription-modal-details .pos-transaction-form-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: start;
        gap: 5px;
        padding: 7px;
    }

    .rx-simple .pos-prescription-modal-details .pos-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 5px 6px;
        padding: 0;
    }

    .rx-simple .pos-prescription-modal-details .pos-form-grid::before {
        margin-bottom: -1px;
    }

    .rx-simple .pos-prescription-modal-details .pos-prescription-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: center;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
    }

    .rx-simple .pos-prescription-modal-details .pos-prescription-mode-summary {
        height: auto;
        min-height: 40px;
        margin: 0;
        align-content: center;
        border-radius: 10px 10px 0 0;
    }

    .rx-simple .pos-prescription-modal-details .pos-prescription-mode-copy > span {
        display: none;
    }

    .rx-simple .pos-prescription-modal-details .pos-prescription-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: end;
        gap: 5px 6px;
        padding: 6px;
        border: 1px solid var(--rx-line);
        border-top: 0;
        border-radius: 0 0 10px 10px;
        background: #fff;
    }

    .rx-simple .pos-prescription-modal-details .pos-prescription-guidance,
    .rx-simple .pos-prescription-modal-details .pos-prescription-mode-note,
    .rx-simple .pos-prescription-modal-details .pos-compound-setup,
    .rx-simple .pos-prescription-modal-details .pos-prescription-head {
        display: none !important;
    }

    .rx-simple .pos-prescription-modal-details .pos-field label {
        margin-bottom: 2px;
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-modal-details .form-control,
    .rx-simple .pos-prescription-modal-details .form-select {
        min-height: 34px;
        padding-top: 5px;
        padding-bottom: 5px;
        font-size: 10px !important;
    }

    /* The product finder remains useful without turning into another scrolling card. */
    .rx-simple .pos-prescription-modal-catalog .pos-catalog-hero {
        min-height: 52px;
        padding: 7px 10px;
    }

    .rx-simple .pos-prescription-modal-catalog .pos-catalog-step {
        width: 32px;
        height: 32px;
        font-size: 0;
    }

    .rx-simple .pos-prescription-modal-catalog .pos-catalog-step::before {
        content: "+";
        font-size: 16px;
        font-weight: 700;
    }

    .rx-simple .pos-prescription-modal-catalog .pos-search-box {
        padding: 9px 10px;
    }

    .rx-simple .pos-prescription-modal-catalog .pos-search-title {
        margin-bottom: 5px;
    }

    .rx-simple .pos-prescription-modal-catalog .pos-search-control .select2-selection--single {
        height: 44px;
        min-height: 44px;
    }

    /* The preparation panel owns the full workspace height. */
    .rx-simple .pos-prescription-modal-editor {
        display: grid;
        grid-template-areas:
            "preparation-header"
            "preparation-content";
        grid-template-columns: minmax(0, 1fr);
        grid-template-rows: auto minmax(0, 1fr);
    }

    .rx-simple .pos-prescription-modal-editor > .pos-rx-panel-header {
        grid-area: preparation-header;
    }

    .rx-simple #prescriptionModalCartSlot {
        display: flex;
        min-width: 0;
        min-height: 0;
        flex-direction: column;
        grid-area: preparation-content;
        overflow: hidden;
    }

    /* In racikan mode, navigation and active-racikan content are separate columns. */
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-modal-editor {
        grid-template-areas:
            "preparation-header preparation-header"
            "compound-navigation preparation-content";
        grid-template-columns: minmax(258px, 286px) minmax(0, 1fr);
        grid-template-rows: auto minmax(0, 1fr);
        overflow: hidden;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-rx-compound-slot {
        min-width: 0;
        min-height: 0;
        grid-area: compound-navigation;
        overflow-x: hidden;
        overflow-y: auto;
        border-right: 1px solid var(--rx-line);
        background: #f7f5f9;
        scrollbar-width: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-rx-compound-slot::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    /* Active compound controls live where compounding work actually happens. */
    .rx-simple .pos-rx-compound-slot:empty {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-compound-slot {
        display: none;
    }

    .rx-simple .pos-rx-compound-slot {
        padding: 10px;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-setup {
        margin: 0;
        overflow: hidden;
        border: 1px solid #d7cee4;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 13px rgba(73, 56, 103, .045);
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-steps {
        display: none;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-steps > span {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 7px;
        padding: 5px 7px;
        border: 1px solid #e6dfec;
        border-radius: 8px;
        background: rgba(255, 255, 255, .78);
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-steps > i {
        display: none;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-controls {
        grid-template-columns: minmax(0, 1fr);
        align-items: stretch;
        gap: 8px;
        padding: 8px;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-group-control {
        align-content: center;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-group-control > div {
        grid-template-columns: minmax(0, 1fr);
    }

    .rx-simple .pos-rx-compound-slot .pos-new-compound-group {
        width: 100%;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-group-control > small {
        margin-top: 1px;
        white-space: normal;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-save-rule {
        min-height: 57px;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-group-summary {
        display: grid;
        min-height: 0;
        grid-template-columns: minmax(0, 1fr);
        padding: 7px;
    }

    .rx-simple .pos-rx-compound-slot .pos-compound-summary-chip {
        width: 100%;
        min-width: 0;
        justify-content: space-between;
        border-radius: 8px;
    }

    /* Preparation has one vertical scrolling surface and never scrolls sideways. */
    .rx-simple .pos-prescription-modal-editor .pos-cart-toolbar {
        position: sticky;
        z-index: 5;
        top: 64px;
        margin: 0;
        padding: 9px 10px 7px;
        border-bottom: 1px solid var(--rx-line);
        background: rgba(255, 255, 255, .97);
        backdrop-filter: blur(12px);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-modal-editor .pos-cart-toolbar {
        position: static;
        margin-top: 0;
        border-bottom: 0;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] #prescriptionToolbarPayBtn {
        display: none;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-wrap {
        min-height: 180px;
        max-height: none;
        flex: 1 1 auto;
        margin: 0 10px 10px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table tbody,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table tr,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table td {
        max-width: 100%;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table tbody {
        padding: 7px;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row {
        grid-template-columns: minmax(150px, .72fr) minmax(150px, .72fr) minmax(180px, .9fr);
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:first-child,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6) {
        grid-column: 1 / -1;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(5) {
        min-width: 0;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6) {
        justify-content: flex-end;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6)::before {
        margin-right: auto;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-empty {
        min-height: 230px;
    }

    .rx-simple .pos-prescription-modal-editor .pos-compound-workspace-empty {
        display: grid;
        min-height: 132px;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        align-items: center;
        gap: 13px;
        margin: 0;
        padding: 18px;
        text-align: left;
        border: 0;
        border-radius: 12px;
        background: #f7f8fb;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-wrap:has(.pos-compound-workspace-empty) {
        border-color: transparent;
        background: transparent;
    }

    .rx-simple .pos-compound-workspace-empty .pos-cart-empty-visual {
        width: 48px;
        height: 48px;
        margin: 0;
        border: 1px solid #ddd6e7;
        border-radius: 12px;
        background: #fff;
        box-shadow: none;
        font-size: 22px;
    }

    .rx-simple .pos-compound-empty-copy {
        display: grid;
        min-width: 0;
        gap: 2px;
    }

    .rx-simple .pos-compound-empty-copy strong {
        color: var(--rx-ink);
        font-size: 12px;
        line-height: 1.35;
    }

    .rx-simple .pos-compound-empty-copy small {
        color: var(--rx-muted);
        font-size: 9px !important;
        line-height: 1.45;
    }

    .rx-simple .pos-compound-workspace-empty .pos-empty-search-button {
        min-height: 38px;
        margin: 0;
        padding: 8px 12px;
        white-space: nowrap;
    }

    .rx-simple .pos-prescription-modal-editor .pos-compound-group-card,
    .rx-simple .pos-prescription-modal-editor .pos-prescription-item-editor {
        width: 100%;
        min-width: 0;
    }

    .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields > label:nth-child(n) {
        grid-column: auto;
    }

    .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields > label:last-child {
        grid-column: span 2;
    }

    .rx-simple .pos-prescription-modal-editor .pos-prescription-item-grid.is-compound-component {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    /* One racikan reads as a clear parent card followed by grouped component cards. */
    .rx-simple .pos-compound-section-label {
        display: block;
        margin-bottom: 2px;
        color: #755f8b;
        font-size: 8px;
        font-style: normal;
        font-weight: 800;
        letter-spacing: .08em;
        line-height: 1.25;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-cart-table tbody {
        gap: 0;
        padding: 9px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-compound-group-row {
        margin-bottom: 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-compound-product-row {
        z-index: 1;
        margin: 0;
        border-bottom-color: #e9e4ee;
        border-radius: 12px 12px 0 0;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-compound-product-row + .pos-prescription-detail-row {
        margin: 0 0 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-compound-product-row + .pos-prescription-detail-row .pos-compound-component-editor {
        border-top: 0;
        border-left-width: 1px;
        border-radius: 0 0 12px 12px;
        background: #faf9fc;
        box-shadow: 0 7px 16px rgba(54, 42, 74, .055);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-compound-product-row .pos-item-group-badge {
        color: #fff;
        border-color: #75618a;
        background: #75618a;
    }

    /* Keep the footer compact so the working canvas receives the height. */
    body.pos-fullscreen-mode .rx-simple footer.pos-prescription-cashier-footer {
        min-height: 68px;
        padding: 7px 12px;
    }

    .rx-simple .pos-prescription-flow-readiness > span {
        width: 36px;
        height: 36px;
    }

    .rx-simple .pos-prescription-flow-metrics > span {
        padding-top: 5px;
        padding-bottom: 5px;
    }

    .rx-simple .pos-finish-prescription-button {
        min-height: 46px;
    }

    @media (max-width: 1240px) {
        .rx-simple .pos-prescription-modal-details .pos-transaction-form-shell {
            grid-template-columns: minmax(0, 1fr);
        }

        .rx-simple .pos-prescription-modal-details .pos-prescription-workspace {
            grid-template-columns: minmax(0, 1fr);
        }

        .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(5) {
            grid-column: 1 / -1;
        }

        .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields,
        .rx-simple .pos-prescription-modal-editor .pos-prescription-item-grid.is-compound-component {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1101px) and (max-height: 760px) {
        .rx-simple .pos-prescription-cashier-body {
            padding: 7px;
        }

        .rx-simple .pos-prescription-cashier-layout {
            grid-template-rows: minmax(320px, 1.75fr) minmax(165px, 1fr);
            gap: 7px;
        }
    }

    @media (max-width: 1100px) {
        .rx-simple .pos-prescription-cashier-body {
            overflow-x: hidden;
            overflow-y: auto;
        }

        .rx-simple .pos-prescription-cashier-layout {
            height: auto;
            grid-template-areas:
                "details"
                "catalog"
                "editor";
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto auto auto;
        }

        .rx-simple .pos-prescription-modal-details .pos-transaction-form-shell {
            grid-template-columns: 1fr;
        }

        .rx-simple .pos-prescription-modal-catalog,
        .rx-simple .pos-prescription-modal-editor {
            overflow: visible;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-modal-editor {
            grid-template-areas:
                "preparation-header"
                "compound-navigation"
                "preparation-content";
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto auto auto;
            overflow: visible;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-rx-compound-slot {
            overflow: visible;
            border-right: 0;
            border-bottom: 1px solid var(--rx-line);
        }

        .rx-simple #prescriptionModalCartSlot {
            overflow: visible;
        }

        .rx-simple .pos-prescription-modal-editor .pos-cart-wrap {
            flex: none;
            overflow: visible;
        }

        .rx-simple .pos-prescription-modal-editor .pos-cart-toolbar,
        .rx-simple .pos-rx-panel-header {
            position: static;
        }

        .rx-simple .pos-rx-compound-slot .pos-compound-controls {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .rx-simple .pos-prescription-modal-editor .pos-compound-workspace-empty {
            grid-template-columns: 44px minmax(0, 1fr);
            padding: 14px;
        }

        .rx-simple .pos-compound-workspace-empty .pos-cart-empty-visual {
            width: 44px;
            height: 44px;
        }

        .rx-simple .pos-compound-workspace-empty .pos-empty-search-button {
            width: 100%;
            grid-column: 1 / -1;
        }

        .rx-simple .pos-prescription-modal-details .pos-form-grid,
        .rx-simple .pos-prescription-modal-details .pos-prescription-form-grid,
        .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row,
        .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields,
        .rx-simple .pos-prescription-modal-editor .pos-prescription-item-grid.is-compound-component {
            grid-template-columns: 1fr;
        }

        .rx-simple .pos-prescription-modal-details .pos-prescription-workspace {
            grid-template-columns: 1fr;
        }

        .rx-simple .pos-prescription-modal-details .pos-prescription-mode-summary {
            min-height: 54px;
            border-radius: 10px 10px 0 0;
        }

        .rx-simple .pos-prescription-modal-details .pos-prescription-form-grid {
            border-top: 0;
            border-left: 1px solid var(--rx-line);
            border-radius: 0 0 10px 10px;
        }

        .rx-simple .pos-rx-compound-slot .pos-compound-group-control > div {
            grid-template-columns: 1fr;
        }

        .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(5),
        .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6),
        .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields > label:last-child {
            grid-column: auto;
        }
    }
</style>
