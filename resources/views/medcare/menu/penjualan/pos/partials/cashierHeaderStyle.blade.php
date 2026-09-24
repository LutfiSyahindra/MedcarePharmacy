<style>
    /* Cashier header: one clear search focus, grouped context, and compact utilities. */
    .pos-premium .pos-appbar {
        min-height: 72px;
        grid-template-columns: minmax(270px, 286px) minmax(330px, 1fr) auto;
        gap: 10px;
        padding: 9px 10px 9px 12px;
    }

    .pos-premium .pos-brand {
        min-height: 72px;
        margin: -9px 0 -9px -12px;
        gap: 10px;
        padding-right: 12px;
    }

    .pos-premium .pos-brand-copy {
        display: grid;
        min-width: 0;
        gap: 2px;
    }

    .pos-premium .pos-brand-title {
        min-width: 0;
    }

    .pos-premium .pos-brand h1 {
        font-size: 1.12rem;
    }

    .pos-premium .pos-brand-title > span {
        max-width: 98px;
    }

    .pos-premium .pos-brand-meta {
        min-width: 0;
        gap: 6px;
        margin-top: 0;
    }

    .pos-premium .pos-session-status,
    .pos-premium .pos-clock {
        flex: 0 0 auto;
        font-size: .61rem;
    }

    .pos-premium .pos-clock::before {
        margin-right: 1px;
    }

    .pos-premium #posClock {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pos-premium .pos-appbar-center {
        min-width: 0;
        align-self: stretch;
    }

    .pos-premium .pos-appbar-center .pos-search-box {
        min-height: 52px;
        grid-template-columns: minmax(110px, 128px) minmax(210px, 1fr);
        gap: 9px;
        padding: 5px 6px 5px 10px;
        border-color: #dfe4eb;
        border-radius: 12px;
        background: #f8fafc;
        box-shadow: inset 0 1px 2px rgba(29, 43, 66, .025);
        transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
    }

    .pos-premium .pos-appbar-center .pos-search-box:focus-within {
        border-color: #9eaae7;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(83, 103, 214, .09);
    }

    .pos-premium .pos-appbar-center .pos-search-title {
        grid-template-columns: 22px minmax(0, 1fr);
        grid-template-rows: auto auto;
        align-items: center;
        column-gap: 7px;
        font-size: .76rem;
        font-weight: 750;
    }

    .pos-premium .pos-appbar-center .pos-search-title::before {
        display: grid;
        width: 22px;
        height: 22px;
        grid-row: 1 / span 2;
        place-items: center;
        border-radius: 7px;
        background: #eef1ff;
        font-size: 1rem;
    }

    .pos-premium .pos-appbar-center .pos-search-title small {
        max-width: 104px;
        font-size: .62rem !important;
        line-height: 1.2;
    }

    .pos-premium .pos-appbar-center .pos-search-control .select2-container--default .select2-selection--single {
        border-color: #d6dce5;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(27, 40, 63, .04);
    }

    .pos-premium .pos-appbar-center .pos-search-control .select2-container--default.select2-container--focus .select2-selection--single,
    .pos-premium .pos-appbar-center .pos-search-control .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #8291df;
        box-shadow: 0 0 0 3px rgba(83, 103, 214, .09);
    }

    .pos-premium .pos-appbar-center .pos-search-leading {
        color: #5266cf;
    }

    .pos-premium .pos-appbar-center .pos-search-ready {
        right: 9px;
        padding: 3px 6px;
        color: #087858;
        border: 1px solid #cce9df;
        border-radius: 999px;
        background: #effaf6;
        font-size: .55rem;
        font-weight: 800;
        letter-spacing: .045em;
    }

    .pos-premium .pos-appbar-actions {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 6px;
    }

    .pos-premium .pos-header-context,
    .pos-premium .pos-header-tools {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 4px;
    }

    .pos-premium .pos-header-context {
        padding: 3px;
        border: 1px solid #e1e6ec;
        border-radius: 11px;
        background: #f4f6f9;
    }

    .pos-premium .pos-header-context .pos-btn-quiet {
        min-height: 40px;
        height: 40px;
        gap: 7px;
        padding: 5px 8px;
        border-color: transparent;
        background: transparent;
    }

    .pos-premium .pos-header-context .pos-btn-quiet:hover:not(:disabled) {
        border-color: #dce2e9;
        background: #fff;
    }

    .pos-premium .pos-header-context .pos-btn-quiet > i:first-child {
        display: grid;
        width: 26px;
        height: 26px;
        flex: 0 0 26px;
        place-items: center;
        color: #5265ca;
        border-radius: 8px;
        background: #e9edff;
        font-size: .95rem;
    }

    .pos-premium .pos-header-context .pos-btn-quiet > span {
        display: grid !important;
        min-width: 0;
        line-height: 1.08;
        text-align: left;
    }

    .pos-premium .pos-header-context .pos-btn-quiet small {
        color: #8a95a5;
        font-size: .6rem !important;
        font-weight: 500;
        line-height: 1.1;
    }

    .pos-premium .pos-header-context .pos-btn-quiet strong {
        overflow: hidden;
        color: #344257;
        font-size: .69rem;
        font-weight: 750;
        line-height: 1.25;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pos-premium .pos-header-context .pos-branch-button {
        width: auto;
        min-width: 126px;
        max-width: 158px;
    }

    .pos-premium .pos-header-context .pos-branch-button > span {
        flex: 1;
    }

    .pos-premium .pos-header-context .pos-branch-button > i:last-child {
        color: #99a3b0;
        font-size: .9rem;
    }

    .pos-premium .pos-header-context .pos-shift-button {
        min-width: 122px;
        max-width: 154px;
    }

    .pos-premium .pos-header-context .pos-shift-button.is-open > i:first-child {
        color: #087858;
        background: #e2f6ef;
    }

    .pos-premium .pos-header-context .pos-shift-button.is-open strong {
        color: #087858;
    }

    .pos-premium .pos-header-divider {
        width: 1px;
        height: 28px;
        flex: 0 0 1px;
        margin: 0 1px;
        background: #e1e5eb;
    }

    .pos-premium .pos-header-tools {
        gap: 5px;
    }

    .pos-premium .pos-header-tools .pos-header-tool {
        position: relative;
        width: 40px;
        min-width: 40px;
        height: 40px;
        min-height: 40px;
        justify-content: center;
        padding: 0;
        border-color: #dfe4ea;
        border-radius: 10px;
        background: #fff;
        font-size: .9rem;
    }

    .pos-premium .pos-header-tools .pos-header-tool > span,
    .pos-premium .pos-header-tools .pos-header-tool > kbd {
        display: none;
    }

    .pos-premium .pos-header-tools .pos-header-tool--primary {
        width: auto;
        min-width: 72px;
        gap: 5px;
        padding: 0 11px;
        border-color: var(--cashier-navy);
        background: var(--cashier-navy);
        font-size: .72rem;
    }

    .pos-premium .pos-header-tools .pos-header-tool--primary > span {
        display: inline !important;
    }

    .pos-premium .pos-header-tools .pos-printer-button.is-connected::after {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 7px;
        height: 7px;
        content: '';
        border: 2px solid #fff;
        border-radius: 50%;
        background: #12a879;
        box-sizing: content-box;
    }

    .pos-premium .pos-header-tools .pos-printer-button.is-connecting::after {
        background: #d5912d;
    }

    .pos-premium .pos-cashier-profile {
        min-height: 40px;
        gap: 7px;
        margin-left: 1px;
        padding: 3px 5px;
        border: 0;
        border-left: 1px solid #e1e5eb;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .pos-premium .pos-cashier-profile > span:last-child {
        display: grid;
    }

    .pos-premium .pos-cashier-profile small {
        font-size: .6rem !important;
        line-height: 1.05;
    }

    .pos-premium .pos-cashier-profile strong {
        max-width: 76px;
        font-size: .69rem;
        line-height: 1.25;
    }

    @media (max-width: 1540px) {
        .pos-premium .pos-appbar {
            grid-template-columns: minmax(238px, 252px) minmax(300px, 1fr) auto;
        }

        .pos-premium .pos-brand-title > span,
        .pos-premium .pos-cashier-profile > span:last-child {
            display: none;
        }

        .pos-premium .pos-cashier-profile {
            padding-left: 6px;
        }

        .pos-premium .pos-header-context .pos-branch-button {
            min-width: 116px;
            max-width: 134px;
        }

        .pos-premium .pos-header-context .pos-shift-button {
            min-width: 112px;
            max-width: 132px;
        }
    }

    @media (max-width: 1320px) {
        .pos-premium .pos-appbar {
            grid-template-columns: minmax(170px, 190px) minmax(270px, 1fr) auto;
        }

        .pos-premium .pos-brand-meta {
            display: none;
        }

        .pos-premium .pos-header-context .pos-btn-quiet,
        .pos-premium .pos-header-context .pos-branch-button,
        .pos-premium .pos-header-context .pos-shift-button {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            justify-content: center;
            padding: 0;
        }

        .pos-premium .pos-header-context .pos-btn-quiet > span,
        .pos-premium .pos-header-context .pos-branch-button > i:last-child {
            display: none !important;
        }
    }

    @media (max-width: 1100px) {
        .pos-premium .pos-appbar {
            grid-template-columns: minmax(190px, 230px) minmax(0, 1fr);
            grid-template-rows: auto auto;
            gap: 8px;
        }

        .pos-premium .pos-brand {
            grid-column: 1;
            grid-row: 1;
            margin-bottom: 0;
            border-radius: 15px 0 0 0;
        }

        .pos-premium .pos-brand-copy {
            display: block;
        }

        .pos-premium .pos-appbar-actions {
            grid-column: 2;
            grid-row: 1;
        }

        .pos-premium .pos-appbar-center {
            display: flex;
            grid-column: 1 / -1;
            grid-row: 2;
        }

        .pos-premium .pos-appbar-center .pos-search-box {
            grid-template-columns: minmax(110px, 128px) minmax(0, 1fr);
        }
    }

    @media (max-width: 760px) {
        .pos-premium .pos-appbar {
            min-height: 0;
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-rows: 48px auto;
            padding: 7px;
        }

        .pos-premium .pos-brand {
            min-height: 48px;
            align-self: stretch;
            margin: -7px 0 0 -7px;
            padding: 5px 10px;
            border-radius: 12px 0 10px 0;
        }

        .pos-premium .pos-brand-mark {
            width: 36px;
            height: 36px;
            flex-basis: 36px;
            border-radius: 10px;
            font-size: 18px;
        }

        .pos-premium .pos-brand-copy {
            display: block;
        }

        .pos-premium .pos-brand h1 {
            font-size: 1rem;
        }

        .pos-premium .pos-brand-title > span,
        .pos-premium .pos-brand-meta,
        .pos-premium .pos-header-divider,
        .pos-premium .pos-cashier-profile,
        .pos-premium .pos-header-tools .pos-header-tool:not(.pos-header-tool--primary) {
            display: none;
        }

        .pos-premium .pos-appbar-actions {
            align-self: center;
            gap: 5px;
        }

        .pos-premium .pos-header-context {
            border: 0;
            background: transparent;
        }

        .pos-premium .pos-header-context .pos-btn-quiet,
        .pos-premium .pos-header-context .pos-branch-button,
        .pos-premium .pos-header-context .pos-shift-button,
        .pos-premium .pos-header-tools .pos-header-tool--primary {
            width: 38px;
            min-width: 38px;
            max-width: 38px;
            height: 38px;
            min-height: 38px;
            padding: 0;
        }

        .pos-premium .pos-header-tools .pos-header-tool--primary > span {
            display: none !important;
        }

        .pos-premium .pos-appbar-center {
            grid-column: 1 / -1;
            grid-row: 2;
        }

        .pos-premium .pos-appbar-center .pos-search-box {
            min-height: 48px;
            grid-template-columns: minmax(0, 1fr);
            padding: 4px;
        }

        .pos-premium .pos-appbar-center .pos-search-title {
            display: none;
        }
    }

    @media (max-width: 420px) {
        .pos-premium .pos-brand {
            gap: 7px;
        }

        .pos-premium .pos-brand-mark {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
        }

        .pos-premium .pos-brand h1 {
            font-size: .92rem;
        }

        .pos-premium .pos-header-context,
        .pos-premium .pos-header-tools {
            gap: 2px;
        }

        .pos-premium .pos-header-context .pos-btn-quiet,
        .pos-premium .pos-header-context .pos-branch-button,
        .pos-premium .pos-header-context .pos-shift-button,
        .pos-premium .pos-header-tools .pos-header-tool--primary {
            width: 36px;
            min-width: 36px;
            max-width: 36px;
            height: 36px;
            min-height: 36px;
        }

        .pos-premium .pos-appbar-center .pos-search-ready {
            display: none;
        }
    }
</style>
