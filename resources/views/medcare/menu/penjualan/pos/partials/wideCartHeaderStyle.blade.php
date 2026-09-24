<style>
    /* Keep product search in the command bar and let the cart own the workspace width. */
    .pos-premium .pos-appbar {
        grid-template-columns: minmax(240px, 300px) minmax(300px, 1fr) auto;
        gap: 10px;
    }

    .pos-premium .pos-appbar-center {
        width: 100%;
        justify-content: stretch;
    }

    .pos-premium .pos-workspace {
        grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
        grid-template-rows: auto minmax(0, 1fr);
        gap: 8px;
    }

    .pos-premium .pos-selling-header {
        display: block;
        min-width: 0;
        grid-column: 1;
        grid-row: 1;
    }

    .pos-premium .pos-selling-header > .pos-customer-card {
        width: 100%;
        min-width: 0;
    }

    .pos-premium .pos-appbar-center .pos-search-box {
        position: relative;
        display: grid;
        width: 100%;
        min-height: 52px;
        height: 100%;
        grid-template-columns: minmax(105px, auto) minmax(220px, 1fr);
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 5px 7px 5px 10px;
        overflow: visible;
        border: 1px solid var(--cashier-line);
        border-radius: 11px;
        background: #fff;
        box-shadow: inset 0 1px 0 #fff, 0 3px 10px rgba(23, 35, 55, .035);
    }

    .pos-premium .pos-appbar-center .pos-search-box::before {
        display: none;
    }

    .pos-premium .pos-appbar-center .pos-search-box.has-selection {
        border-color: #bcded1;
        background: linear-gradient(90deg, #f8fffc 0%, #fff 42%);
    }

    .pos-premium .pos-appbar-center .pos-search-title {
        display: grid;
        min-width: 0;
        gap: 2px;
        margin: 0;
        color: var(--cashier-ink);
        font-size: .72rem;
        line-height: 1.2;
    }

    .pos-premium .pos-appbar-center .pos-search-title::before {
        color: #5367d6;
        content: "\F0349";
        font-family: "Material Design Icons";
        font-size: 1rem;
        line-height: 1;
    }

    .pos-premium .pos-appbar-center .pos-search-title small {
        overflow: hidden;
        color: #8b96a6;
        font-size: .58rem;
        font-weight: 520;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .pos-premium .pos-appbar-center .pos-search-control {
        min-width: 0;
    }

    .pos-premium .pos-appbar-center .pos-search-control .select2-container--default .select2-selection--single {
        height: 40px;
        min-height: 40px;
    }

    .pos-premium .pos-appbar-center .pos-search-control .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }

    .pos-premium .pos-appbar-center .pos-search-feedback {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        margin: -1px !important;
        border: 0 !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
    }

    .pos-premium .pos-selling-header > .pos-customer-card {
        height: max-content;
        border-radius: 12px;
    }

    .pos-premium .pos-selling-header > .pos-customer-card .pos-transaction-details > summary {
        min-height: 62px;
        padding: 10px 12px;
        background: linear-gradient(135deg, #fff 0%, #f8faff 100%);
    }

    .pos-premium .pos-selling-header > .pos-customer-card {
        border-color: #dce2ea;
        box-shadow: 0 7px 20px rgba(27, 39, 62, .06);
    }

    .pos-premium .pos-selling-header > .pos-customer-card:has(.pos-transaction-details[open]) {
        max-height: min(560px, 60vh);
    }

    .pos-premium .pos-selling-header .pos-detail-customer {
        gap: 10px;
    }

    .pos-premium .pos-selling-header .pos-customer-avatar {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        color: #465bc7;
        background: linear-gradient(145deg, #f2f5ff, #e6ebff);
        font-size: .76rem;
    }

    .pos-premium .pos-selling-header .pos-detail-customer small {
        color: #8994a5;
        font-size: .56rem;
        letter-spacing: .12em;
    }

    .pos-premium .pos-selling-header .pos-detail-customer strong {
        margin-top: 2px;
        color: #26364d;
        font-size: .78rem;
    }

    .pos-premium .pos-selling-header .pos-detail-summary {
        max-width: 105px;
        padding: 5px 8px;
        color: #57657a;
        border: 1px solid #e1e5eb;
        border-radius: 999px;
        background: #fff;
        font-size: .58rem;
        text-align: center;
    }

    .pos-premium .pos-selling-header .pos-detail-chevron {
        width: 30px;
        height: 30px;
        color: #5265c7;
        border-color: #dce2f4;
        background: #f2f4ff;
    }

    .pos-premium .pos-selling-header .pos-transaction-details[open] > summary {
        border-bottom-color: #e5e9ef;
        background: #fff;
    }

    .pos-premium .pos-selling-header .pos-transaction-form-shell {
        padding: 11px 12px 12px;
        background: #fbfcfd;
    }

    .pos-premium .pos-selling-header .pos-general-customer-button {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 5px 8px;
        color: #4f61b8;
        border-color: #d9deed;
        border-radius: 8px;
        background: #fff;
        font-size: .6rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .pos-premium .pos-selling-header .pos-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 9px;
    }

    .pos-premium .pos-selling-header .pos-field-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    .pos-premium .pos-selling-header .pos-field-head label {
        margin-bottom: 0;
    }

    .pos-premium .pos-selling-header .pos-field label {
        margin-bottom: 5px;
        color: #526074;
        font-size: .62rem;
        font-weight: 700;
    }

    .pos-premium .pos-selling-header .pos-field label b {
        color: #d34b5b;
    }

    .pos-premium .pos-selling-header .pos-field .form-control,
    .pos-premium .pos-selling-header .pos-field .form-select {
        min-height: 42px;
        border-color: #d5dce5;
        border-radius: 9px;
        background-color: #fff;
        font-size: .68rem;
    }

    .pos-premium .pos-selling-header .pos-field .form-control::placeholder {
        color: #a1aab6;
    }

    .pos-premium .pos-selling-header .select2-container {
        width: 100% !important;
    }

    .pos-premium .pos-selling-header .select2-container--default .select2-selection--single {
        height: 42px;
        min-height: 42px;
        border-color: #d5dce5;
        border-radius: 9px;
        background: #fff;
    }

    .pos-premium .pos-selling-header .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 12px;
        color: #344257;
        line-height: 40px;
        font-size: .68rem;
    }

    /* Keep buyer/product controls on the left and give the cart the full right pane. */
    .pos-premium .pos-workspace > .pos-catalog {
        position: relative;
        top: auto;
        display: block;
        width: 100%;
        height: 100%;
        min-height: 0;
        max-height: none;
        grid-column: 1;
        grid-row: 2;
        overflow: auto;
    }

    .pos-premium .pos-workspace > .pos-catalog:not(.has-selection) {
        display: none;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-product-panel {
        display: grid;
        min-height: 0;
        grid-template-columns: minmax(0, 1fr);
        align-items: start;
        gap: 8px;
        padding: 8px;
        overflow: visible;
        border-radius: 12px;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-catalog-hero,
    .pos-premium .pos-workspace > .pos-catalog .pos-catalog-rail {
        display: none;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-catalog-block {
        min-width: 0;
        margin: 0;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-selection-block {
        grid-column: 1;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-config-block {
        grid-column: 1;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-stock-block {
        grid-column: 1;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-selection-block .pos-product-card {
        min-height: 92px;
    }

    .pos-premium .pos-workspace > .pos-catalog .pos-stock-block > summary {
        border-radius: 10px;
    }

    /* Cart stays on the right beside the buyer section. */
    .pos-premium .pos-checkout {
        display: grid;
        width: 100%;
        height: 100%;
        min-height: 0;
        grid-column: 2;
        grid-row: 1 / span 2;
        grid-template-rows: minmax(0, 1fr);
        gap: 0;
    }

    .pos-premium .pos-transaction-panel {
        width: 100%;
        height: 100%;
        min-height: 0;
    }

    .pos-premium .pos-workspace.is-stage-payment > .pos-selling-header,
    .pos-premium .pos-workspace.is-stage-payment > .pos-catalog {
        display: none !important;
    }

    .pos-premium .pos-workspace.is-stage-payment > .pos-checkout {
        display: block;
        grid-column: 1;
        grid-row: 1;
    }

    @media (min-width: 1181px) {
        .pos-premium .pos-transaction-panel .pos-cart-table {
            min-width: 820px;
            width: 100%;
        }
    }

    @media (max-width: 1480px) {
        .pos-premium .pos-appbar {
            grid-template-columns: minmax(175px, 240px) minmax(280px, 1fr) auto;
            gap: 8px;
        }
    }

    @media (max-width: 1240px) {
        .pos-premium .pos-appbar {
            grid-template-columns: auto minmax(260px, 1fr) auto;
        }
    }

    @media (max-width: 1080px) {
        .pos-premium .pos-appbar {
            grid-template-columns: auto minmax(220px, 1fr) auto;
        }

        .pos-premium .pos-workspace {
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto auto minmax(520px, 1fr);
        }

        .pos-premium .pos-selling-header {
            grid-column: 1;
            grid-row: 1;
        }

        .pos-premium .pos-selling-header .pos-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pos-premium .pos-workspace.is-stage-product > .pos-checkout,
        .pos-premium .pos-workspace.is-stage-cart > .pos-checkout {
            display: grid;
            grid-column: 1;
            grid-row: 3;
        }

        .pos-premium .pos-workspace.is-stage-product > .pos-catalog.has-selection,
        .pos-premium .pos-workspace.is-stage-cart > .pos-catalog.has-selection {
            display: block;
            grid-column: 1;
            grid-row: 2;
        }

        .pos-premium .pos-workspace > .pos-catalog .pos-product-panel {
            grid-template-columns: minmax(0, 1fr) minmax(290px, .75fr);
        }

        .pos-premium .pos-workspace > .pos-catalog .pos-config-block {
            grid-column: 2;
        }

        .pos-premium .pos-workspace > .pos-catalog .pos-stock-block {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 760px) {
        .pos-premium .pos-appbar {
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-rows: auto auto;
            min-height: 0;
        }

        .pos-premium .pos-brand {
            grid-column: 1 / -1;
            grid-row: 1;
        }

        .pos-premium .pos-appbar-center {
            display: flex;
            grid-column: 1;
            grid-row: 2;
        }

        .pos-premium .pos-appbar-actions {
            grid-column: 2;
            grid-row: 2;
        }

        .pos-premium .pos-workspace {
            overflow: auto;
        }

        .pos-premium .pos-appbar-center .pos-search-box {
            min-height: 50px;
            grid-template-columns: minmax(0, 1fr);
            padding: 5px;
        }

        .pos-premium .pos-appbar-center .pos-search-title {
            display: none;
        }

        .pos-premium .pos-selling-header > .pos-customer-card .pos-transaction-details > summary {
            min-height: 54px;
        }

        .pos-premium .pos-selling-header .pos-form-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .pos-premium .pos-workspace > .pos-catalog {
            height: auto;
            max-height: 43vh;
        }

        .pos-premium .pos-workspace > .pos-catalog .pos-product-panel {
            grid-template-columns: minmax(0, 1fr);
        }

        .pos-premium .pos-workspace > .pos-catalog .pos-selection-block,
        .pos-premium .pos-workspace > .pos-catalog .pos-config-block,
        .pos-premium .pos-workspace > .pos-catalog .pos-stock-block {
            grid-column: 1;
        }

        .pos-premium .pos-checkout,
        .pos-premium .pos-transaction-panel {
            min-height: 520px;
        }
    }

    @media (max-width: 480px) {
        .pos-premium .pos-appbar-center .pos-search-ready {
            display: none;
        }

        .pos-premium .pos-appbar-center .pos-search-control .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-right: 30px;
        }
    }
</style>
