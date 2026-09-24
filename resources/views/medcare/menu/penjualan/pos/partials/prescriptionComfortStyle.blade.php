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

    /* Non-compound prescriptions keep the primary product action in the page header. */
    .rx-simple .pos-prescription-header-search {
        display: none;
        min-width: 0;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-header {
        grid-template-columns: minmax(240px, 300px) minmax(360px, 1fr) auto;
        gap: 14px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search {
        display: block;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-flow-steps {
        display: none;
    }

    .rx-simple .pos-prescription-header-search .pos-search-box {
        display: grid;
        min-height: 54px;
        grid-template-columns: minmax(96px, 116px) minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 5px 6px 5px 10px;
        overflow: visible;
        border: 1px solid #d6e2dd;
        border-radius: 12px;
        background: #f7faf9;
        box-shadow: inset 0 1px 2px rgba(24, 43, 35, .025);
        transform: none;
    }

    .rx-simple .pos-prescription-header-search .pos-search-box::before,
    .rx-simple .pos-prescription-header-search .pos-search-topline,
    .rx-simple .pos-prescription-header-search .pos-search-feedback {
        display: none;
    }

    .rx-simple .pos-prescription-header-search .pos-search-box:focus-within {
        border-color: #80a999;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(24, 91, 73, .08);
    }

    .rx-simple .pos-prescription-header-search .pos-search-title {
        margin: 0;
        color: var(--rx-ink-soft);
        font-size: 11px !important;
        line-height: 1.25;
    }

    .rx-simple .pos-prescription-header-search .pos-search-title small {
        margin-top: 2px;
        color: var(--rx-muted);
        font-size: 8px !important;
        line-height: 1.25;
    }

    .rx-simple .pos-prescription-header-search .pos-search-control .select2-container,
    .rx-simple .pos-prescription-header-search .pos-search-control .select2-selection--single {
        width: 100% !important;
    }

    .rx-simple .pos-prescription-header-search .pos-search-control .select2-selection--single {
        height: 42px;
        min-height: 42px;
        border-color: #cddbd5;
        border-radius: 10px;
        box-shadow: 0 2px 7px rgba(24, 43, 35, .04);
    }

    .rx-simple .pos-prescription-header-search .pos-search-control .select2-selection__rendered {
        line-height: 40px !important;
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
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(5) {
        grid-column: 1 / -1;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(5) {
        min-width: 0;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(5) {
        justify-content: flex-end;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(5)::before {
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
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-header {
            grid-template-columns: minmax(180px, 220px) minmax(280px, 1fr) auto;
            gap: 8px;
        }

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

    @media (max-width: 900px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-validation-state {
            display: none;
        }
    }

    @media (max-width: 720px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-header {
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 7px;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-header-search {
            grid-column: 1 / -1;
            grid-row: 2;
        }

        .rx-simple .pos-prescription-header-search .pos-search-box {
            min-height: 50px;
            grid-template-columns: minmax(82px, 98px) minmax(0, 1fr);
            gap: 7px;
            padding-left: 8px;
        }

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
        .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields > label:last-child {
            grid-column: auto;
        }
    }

    /*
     * Product preview behavior
     * Non-racikan keeps the primary canvas dedicated to identity + prescription,
     * then opens product configuration as a focused premium dialog.
     */
    .rx-simple .pos-rx-product-preview {
        min-width: 0;
        min-height: 0;
        grid-area: catalog;
    }

    .rx-simple .pos-rx-product-preview-dialog,
    .rx-simple .pos-rx-product-preview-content {
        min-width: 0;
        min-height: 0;
        height: 100%;
    }

    .rx-simple .pos-rx-product-preview-backdrop,
    .rx-simple .pos-rx-product-preview-header,
    .rx-simple .pos-rx-product-preview-footer {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-layout {
        grid-template-areas: "details editor";
        grid-template-columns: minmax(310px, 372px) minmax(0, 1fr);
        grid-template-rows: minmax(0, 1fr);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details {
        max-height: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview {
        position: fixed;
        z-index: 1090;
        inset: 0;
        display: none;
        width: 100vw;
        min-height: 100vh;
        height: 100dvh;
        place-items: center;
        padding: clamp(12px, 2.25vw, 28px);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview.is-open {
        display: grid;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-backdrop {
        position: absolute;
        display: block;
        width: 100%;
        height: 100%;
        padding: 0;
        border: 0;
        background:
            radial-gradient(circle at 50% 15%, rgba(61, 106, 89, .16), transparent 32rem),
            rgba(13, 25, 21, .68);
        backdrop-filter: blur(7px);
        inset: 0;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-dialog {
        position: relative;
        z-index: 1;
        display: grid;
        width: min(1040px, 100%);
        height: auto;
        max-height: min(720px, calc(100dvh - clamp(24px, 4.5vw, 56px)));
        grid-template-columns: minmax(0, 1fr);
        grid-template-rows: auto minmax(0, 1fr) auto;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .76);
        border-radius: 20px;
        background: #f7faf8;
        box-shadow: 0 34px 90px rgba(7, 19, 15, .34), 0 8px 24px rgba(7, 19, 15, .16);
        animation: posRxProductPreviewIn .22s ease-out both;
    }

    @keyframes posRxProductPreviewIn {
        from { opacity: 0; transform: translateY(14px) scale(.982); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-header {
        position: relative;
        display: flex;
        width: 100%;
        min-height: 76px;
        grid-column: 1;
        grid-row: 1;
        align-items: center;
        gap: 14px;
        overflow: hidden;
        padding: 12px 16px;
        color: #fff;
        border-bottom: 1px solid rgba(255, 255, 255, .1);
        background:
            radial-gradient(circle at 86% -80%, rgba(135, 206, 178, .42), transparent 18rem),
            linear-gradient(118deg, #172c25 0%, #1c5746 58%, #26745c 100%);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-header::after {
        position: absolute;
        right: 18%;
        bottom: -52px;
        width: 155px;
        height: 155px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
        content: "";
    }

    .rx-simple .pos-rx-product-preview-heading {
        position: relative;
        z-index: 1;
        display: flex;
        flex: 1 1 auto;
        min-width: 0;
        align-items: center;
        gap: 12px;
    }

    .rx-simple .pos-rx-product-preview-heading > span:last-child {
        display: grid;
        min-width: 0;
    }

    .rx-simple .pos-rx-product-preview-mark {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 auto;
        place-items: center;
        color: #dff8ee;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 14px;
        background: rgba(255, 255, 255, .1);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
        font-size: 23px;
    }

    .rx-simple .pos-rx-product-preview-heading small {
        color: #9ed3c0;
        font-size: 8px !important;
        font-weight: 800 !important;
        letter-spacing: .12em;
    }

    .rx-simple .pos-rx-product-preview-heading strong {
        overflow: hidden;
        color: #fff;
        font-size: 16px !important;
        font-weight: 750 !important;
        letter-spacing: -.015em;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .rx-simple .pos-rx-product-preview-heading p {
        margin: 2px 0 0;
        color: #c3d9d1;
        font-size: 10px;
        line-height: 1.35;
    }

    .rx-simple .pos-rx-product-preview-security {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
        padding: 7px 10px;
        color: #dff8ee;
        border: 1px solid rgba(190, 239, 221, .2);
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }

    .rx-simple .pos-rx-product-preview-close {
        position: relative;
        z-index: 1;
        display: inline-flex;
        min-height: 38px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 10px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 11px;
        background: rgba(255, 255, 255, .09);
        font-size: 10px !important;
        font-weight: 700;
        white-space: nowrap;
    }

    .rx-simple .pos-rx-product-preview-close i {
        font-size: 17px;
    }

    .rx-simple .pos-rx-product-preview-close:hover,
    .rx-simple .pos-rx-product-preview-close:focus-visible {
        color: #fff;
        border-color: rgba(255, 255, 255, .34);
        background: rgba(255, 255, 255, .17);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content {
        width: 100%;
        height: auto;
        grid-area: auto;
        grid-column: 1;
        grid-row: 2;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 18px;
        border: 0;
        border-radius: 0;
        background: #f7faf8;
        box-shadow: none;
        scrollbar-color: #b9ccc4 transparent;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content > .pos-catalog {
        position: static;
        width: 100%;
        height: auto;
        overflow: visible;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-panel {
        display: grid;
        grid-template-areas:
            "selection configuration"
            "selection stock";
        grid-template-columns: minmax(0, 1.15fr) minmax(300px, .85fr);
        grid-template-rows: auto auto;
        align-items: start;
        gap: 14px;
        padding: 0;
        background: transparent;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-catalog-hero,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-catalog-rail,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-shortcut-card,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-block-number {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-catalog-block {
        margin: 0;
        overflow: hidden;
        border-color: #d9e5e0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(19, 52, 41, .055);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-selection-block {
        display: flex;
        min-height: 100%;
        flex-direction: column;
        grid-area: selection;
        border-color: #cbded6;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-config-block {
        display: block;
        grid-area: configuration;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-stock-block {
        display: block;
        grid-area: stock;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-selection-block .pos-catalog-block-head {
        display: flex;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-catalog-block-head {
        min-height: 50px;
        padding: 8px 11px;
        border-bottom-color: #e5ece8;
        background: #fbfdfc;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-catalog-block-head small {
        color: #7d8d86;
        font-size: 8px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-catalog-block-head strong {
        color: #263b33;
        font-size: 11px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-selection-block .pos-product-card {
        min-height: 285px;
        flex: 1 1 auto;
        margin: 0;
        padding: 18px;
        border: 0;
        border-radius: 0;
        background: linear-gradient(145deg, #fff, #f3f8f6);
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-name {
        align-items: flex-start;
        gap: 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-name strong {
        color: #183129;
        font-size: 16px !important;
        line-height: 1.35;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-name .badge {
        padding: 6px 9px;
        color: #176148;
        border: 1px solid #c6e4d8;
        background: #eaf7f2 !important;
        font-size: 9px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-summary {
        gap: 7px;
        margin-top: 14px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-summary span {
        padding: 6px 9px;
        color: #52645d;
        border-color: #dce7e2;
        background: #fff;
        font-size: 9px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-more {
        margin-top: 14px;
        border-top: 1px solid #dfe9e5;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-more > summary {
        min-height: 42px;
        color: #365a4c;
        font-size: 10px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-config-block .pos-add-card {
        padding: 12px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-add-fields {
        gap: 10px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-field label {
        margin-bottom: 5px;
        color: #4e625a;
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .form-control,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .form-select {
        min-height: 42px;
        border-color: #d5e0db;
        border-radius: 10px;
        font-size: 11px !important;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-add-button {
        min-height: 46px;
        margin-top: 11px;
        border-radius: 10px;
        background: var(--rx-primary);
        box-shadow: 0 8px 18px rgba(24, 91, 73, .18);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-add-button:hover:not(:disabled),
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-add-button:focus-visible:not(:disabled) {
        background: var(--rx-primary-hover);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-quote-box {
        gap: 0;
        margin: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-quote-box > div {
        min-width: 0;
        padding: 10px 8px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-quote-box strong {
        font-size: 11px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-fefo-zone {
        margin: 0;
        padding: 10px 11px 12px;
        border-top: 1px solid #e5ece8;
        background: #fbfdfc;
    }

    .rx-simple .pos-rx-product-preview-footer {
        min-height: 48px;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 9px 17px;
        color: #61726b;
        border-top: 1px solid #dce7e2;
        background: #fff;
        font-size: 9px;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-footer {
        display: flex !important;
        width: 100%;
        grid-column: 1;
        grid-row: 3;
    }

    .rx-simple .pos-rx-product-preview-footer > span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .rx-simple .pos-rx-product-preview-footer i {
        color: var(--rx-primary);
        font-size: 13px;
    }

    .rx-simple .pos-rx-product-preview-footer kbd {
        padding: 2px 6px;
        color: #4f625a;
        border: 1px solid #d4e0db;
        border-bottom-width: 2px;
        border-radius: 5px;
        background: #f5f8f7;
        box-shadow: none;
        font-size: 8px;
    }

    body.pos-rx-product-preview-open {
        overflow: hidden;
    }

    @media (max-width: 900px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-cashier-layout {
            grid-template-areas:
                "details"
                "editor";
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto auto;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-details,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-prescription-modal-editor {
            overflow: visible;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview {
            padding: 12px;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-dialog {
            width: 100%;
            max-height: calc(100dvh - 24px);
            border-radius: 17px;
        }
    }

    @media (max-width: 760px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content {
            padding: 12px;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-product-panel {
            display: grid;
            grid-template-areas:
                "selection"
                "configuration"
                "stock";
            grid-template-columns: minmax(0, 1fr);
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-selection-block,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-config-block,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-stock-block {
            grid-row: auto;
            grid-column: 1;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-selection-block .pos-product-card {
            min-height: 0;
        }

        .rx-simple .pos-rx-product-preview-header {
            min-height: 72px !important;
            padding: 10px 12px !important;
        }

        .rx-simple .pos-rx-product-preview-security,
        .rx-simple .pos-rx-product-preview-heading p,
        .rx-simple .pos-rx-product-preview-footer > span:last-child {
            display: none;
        }

        .rx-simple .pos-rx-product-preview-heading strong {
            font-size: 14px !important;
        }

        .rx-simple .pos-rx-product-preview-mark {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            font-size: 19px;
        }
    }

    @media (max-width: 520px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview {
            padding: 0;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-dialog {
            max-height: 100dvh;
            border: 0;
            border-radius: 0;
        }

        .rx-simple .pos-rx-product-preview-heading small,
        .rx-simple .pos-rx-product-preview-security {
            display: none !important;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-footer {
            display: none !important;
        }

        .rx-simple .pos-rx-product-preview-close span {
            display: none;
        }

        .rx-simple .pos-rx-product-preview-close {
            width: 38px;
            padding: 0;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-add-fields,
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-quote-box {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (min-width: 761px) and (max-height: 660px) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-dialog {
            max-height: calc(100dvh - 24px);
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content {
            padding: 12px;
        }

        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-content .pos-selection-block .pos-product-card {
            min-height: 210px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rx-simple .pos-prescription-cashier-step[data-prescription-mode="standard"] .pos-rx-product-preview-dialog {
            animation: none;
        }
    }
</style>
