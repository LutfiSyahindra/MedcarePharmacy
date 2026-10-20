<style>
    /* List-page styles are scoped so receipt forms and detail modals keep their own layout. */
    .receive-workspace {
        --purchase-border: #e2e9ee;
        --purchase-text: #1c303c;
        --purchase-muted: #637580;
        font-family: "Plus Jakarta Sans", "Roboto", Arial, sans-serif;
    }

    .receive-workspace .page-breadcrumb,
    .receive-workspace .breadcrumb { margin-bottom: .9rem; }

    .receive-page-hero {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(290px, 1fr);
        align-items: center;
        gap: 2.5rem;
        padding: 1.4rem 1.75rem;
        border: 1px solid #187c7d;
        border-radius: 16px;
        color: #fff;
        background: linear-gradient(115deg, #0d655f 0%, #117c7b 60%, #246a97 100%);
        overflow: hidden;
    }

    .receive-page-hero::after {
        position: absolute;
        right: -100px;
        top: -160px;
        width: 430px;
        height: 430px;
        border: 65px solid rgba(255, 255, 255, .035);
        border-radius: 50%;
        content: "";
        pointer-events: none;
    }

    .receive-hero-copy, .receive-steps { position: relative; z-index: 1; min-width: 0; }
    .receive-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: #c1ece5;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }
    .receive-eyebrow i { font-size: 1rem; }
    .receive-page-hero h1 {
        margin: .55rem 0 .6rem;
        color: #fff;
        font-size: clamp(1.5rem, 2vw, 1.8rem);
        font-weight: 800;
        letter-spacing: -.04em;
        line-height: 1.25;
    }
    .receive-page-hero p {
        max-width: 540px;
        margin: 0;
        color: #d9eeed;
        font-size: .8rem;
        line-height: 1.7;
    }
    .receive-hero-actions { display: flex; align-items: center; flex-wrap: wrap; gap: .65rem; margin-top: 1rem; }
    .receive-hero-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 44px;
        padding: .65rem 1rem;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 700;
    }
    .receive-hero-actions .btn-light { border-color: #fff; color: #0b625c; background: #fff; }
    .receive-hero-actions .btn-light:hover { background: #e8f8f5; border-color: #e8f8f5; }
    .receive-hero-actions .receive-hero-link { color: #e2f4f1; background: transparent; }
    .receive-hero-actions .receive-hero-link:hover { background: rgba(255, 255, 255, .1); }
    .receive-workspace .receive-hero-actions .btn:focus-visible { outline-color: #fff; }
    .receive-steps { display: grid; gap: 0; margin: 0; padding: .3rem 1rem; list-style: none; border: 1px solid #ffffff30; border-radius: 12px; background: #ffffff0a; }
    .receive-steps li { display: grid; grid-template-columns: 30px minmax(0, 1fr) 20px; align-items: center; gap: .7rem; padding: .5rem 0; }
    .receive-steps li + li { border-top: 1px solid #ffffff20; }
    .receive-step-number { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 50%; color: #dbf4ed; background: #ffffff13; font-size: .66rem; font-weight: 700; }
    .receive-steps strong { display: block; color: #fff; font-size: .77rem; font-weight: 700; }
    .receive-steps small { display: block; margin-top: .2rem; color: #d0e9e8; font-size: .68rem; line-height: 1.5; }
    .receive-steps li > i { color: #b6e0dd; font-size: 1.1rem; }

    .receive-overview { margin: 1.15rem 0; }
    .receive-section-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .4rem; margin-bottom: .75rem; }
    .receive-section-heading h2 { margin: 0; color: var(--purchase-text); font-size: .83rem; font-weight: 700; }
    .receive-section-heading > span { color: var(--purchase-muted); font-size: .68rem; }
    .receive-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)) minmax(0, 1.4fr); border: 1px solid var(--purchase-border); border-radius: 12px 12px 0 0; background: #fff; overflow: hidden; }
    .receive-metric { min-width: 0; padding: .85rem 1.25rem; }
    .receive-metric + .receive-metric { border-left: 1px solid var(--purchase-border); }
    .receive-metric-label { display: flex; align-items: center; justify-content: space-between; min-height: 30px; gap: .5rem; color: #536873; font-size: .73rem; font-weight: 600; }
    .receive-metric-label i { display: grid; flex: 0 0 30px; place-items: center; height: 30px; border-radius: 8px; color: #56728b; background: #f0f5f9; font-size: 1.1rem; }
    .receive-metric > strong { display: block; margin: .3rem 0 .2rem; font-size: 1.5rem; font-weight: 800; letter-spacing: -.04em; line-height: 1.3; overflow-wrap: anywhere; font-variant-numeric: tabular-nums; }
    .receive-metric > small { display: block; color: var(--purchase-muted); font-size: .68rem; }
    .receive-metric.is-draft .receive-metric-label i { color: #ad6a0c; background: #fff5e2; }
    .receive-metric.is-posted .receive-metric-label i { color: #128365; background: #eaf8f1; }
    .receive-metric.is-value { background: #f6fbfa; }
    .receive-metric.is-value .receive-metric-label i { color: #0f766e; background: #e2f2ee; }
    .receive-metric.is-value > strong { color: #0b6d64; font-size: clamp(1.1rem, 1.65vw, 1.65rem); }
    .receive-overview-footer { display: flex; flex-wrap: wrap; gap: .5rem 1.6rem; padding: .5rem 1.25rem; border: 1px solid var(--purchase-border); border-top: 0; border-radius: 0 0 12px 12px; color: var(--purchase-muted); background: #fafcfd; font-size: .69rem; }
    .receive-overview-footer > span { display: inline-flex; flex-wrap: wrap; align-items: center; gap: .35rem; }
    .receive-overview-footer i { color: #7c939c; font-size: .95rem; }
    .receive-overview-footer strong { color: var(--purchase-text); font-weight: 700; }
    .receive-overview-footer small { font-size: inherit; }

    .receive-workspace #receiveTableSection { border-radius: 14px; box-shadow: 0 4px 20px #253c4b04; scroll-margin-top: 90px; }
    .receive-list-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; }
    .receive-list-heading .purchase-table-title { min-width: 0; }
    .receive-list-heading .purchase-table-title-icon { width: 40px; height: 40px; flex-basis: 40px; color: #0f766e; background: #eaf6f3; font-size: 1.2rem; }
    .receive-list-heading h2 { margin: 0; color: var(--purchase-text); font-size: .95rem; font-weight: 800; letter-spacing: -.025em; }
    .receive-list-heading p { margin: .3rem 0 0; font-size: .72rem; line-height: 1.6; }
    .receive-list-actions { display: flex; align-items: center; gap: .5rem; }
    .receive-list-actions .btn { display: inline-flex; align-items: center; justify-content: center; gap: .35rem; min-height: 40px; border-radius: 8px; font-size: .74rem; font-weight: 600; }
    .receive-list-actions .purchase-icon-btn { width: 40px; border-color: var(--purchase-border); color: #637580; background: #fff; }
    .receive-workspace .receive-create-button { border-color: #0f766e; color: #fff; background: #0f766e; }
    .receive-workspace .receive-create-button:hover { border-color: #0b5f59; background: #0b5f59; }
    .receive-list-actions .receive-filter-toggle { display: none; border-color: var(--purchase-border); color: #536873; }
    .receive-list-actions .receive-filter-toggle:is(:hover, :focus) { color: #0b6d64; background: #eaf6f3; }
    .receive-workspace #openPenerimaanModalToolbar { width: auto; font-size: .74rem; }

    .receive-search-panel { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 1.25rem; margin: 0 1.25rem; padding: .85rem 1rem; border: 1px solid #e6edee; border-radius: 10px; background: #f8fafb; }
    .receive-document-search { display: grid; align-content: start; gap: .55rem; min-width: 0; }
    .receive-document-search > label,
    .receive-workspace .receive-medicine-filter-heading label { margin: 0; color: #3c535f; font-size: .74rem; font-weight: 700; line-height: 1.5; }
    .receive-workspace #receiveTableSection .purchase-search { min-width: 0; min-height: 46px; flex: none; border-color: #d5e0e5; border-radius: 8px; }
    .receive-workspace .purchase-search input { width: 100%; min-width: 0; padding-block: .7rem; font-size: .76rem; }
    .receive-workspace .purchase-search:focus-within { border-color: #0f766e; }
    .receive-workspace .receive-medicine-filter { gap: .55rem; padding: 0; border: 0; background: transparent; align-content: start; }
    .receive-workspace .receive-medicine-filter-heading label i { display: none; }
    .receive-workspace .receive-medicine-filter-input { gap: .4rem; }
    .receive-workspace .receive-medicine-select .select2-selection--single { min-height: 46px; border-color: #d5e0e5; padding-block: .6rem; }
    .receive-workspace .receive-medicine-select .select2-selection--single .select2-selection__rendered { font-family: inherit; font-size: .76rem; }
    .receive-workspace #clearReceiveMedicineFilter { min-height: 46px; min-width: 40px; padding: .5rem; border-color: #d5e0e5; }
    .receive-workspace #clearReceiveMedicineFilter span { display: none; }
    .receive-document-search > p,
    .receive-workspace #receiveMedicineFilterHint { margin: 0; color: var(--purchase-muted); font-size: .67rem; line-height: 1.65; }

    .receive-status-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .4rem 1rem; margin: 1rem 1.25rem 0; border-bottom: 1px solid var(--purchase-border); }
    .receive-period-caption { display: inline-flex; align-items: center; gap: .35rem; padding-block: .5rem; color: var(--purchase-muted); font-size: .65rem; }
    .receive-status-tabs { display: flex; flex-wrap: wrap; gap: .25rem 1rem; }
    .receive-workspace .purchase-filter-chip { position: relative; justify-content: center; gap: .4rem; min-height: 44px; margin-bottom: -1px; padding: .6rem .25rem .8rem; border: 0; border-bottom: 2px solid transparent; border-radius: 0; color: #637580; background: transparent; font-size: .73rem; font-weight: 600; box-shadow: none; }
    .receive-workspace .purchase-filter-chip:hover { color: #0b6d64; background: #f6fbfa; transform: none; }
    .receive-workspace .purchase-filter-chip.is-active { border-bottom-color: #0f766e; color: #0b6d64; background: transparent; font-weight: 800; box-shadow: none; }
    .receive-workspace .purchase-filter-chip .purchase-filter-count { display: inline-flex; align-items: center; justify-content: center; min-width: 23px; height: 21px; padding-inline: .3rem; color: #60717c; background: #f0f3f6; font-size: .64rem; }
    .receive-workspace .purchase-filter-chip.is-active .purchase-filter-count { color: #0b6d64; background: #e4f4ee; }
    .receive-status-dot { width: 6px; height: 6px; border-radius: 50%; background: #c38b2e; }
    .receive-status-dot.is-posted { background: #299b77; }
    .receive-list-filters { display: grid; grid-template-columns: minmax(200px, 1fr) minmax(150px, .7fr) minmax(120px, .5fr); gap: .8rem; align-items: end; padding: 1rem 1.25rem; }
    .receive-list-filters > div { display: grid; gap: .45rem; min-width: 0; }
    .receive-list-filters label { margin: 0; color: #637580; font-size: .68rem; font-weight: 600; }
    .receive-workspace .purchase-date-input { width: 100%; min-width: 0; height: 40px; border-color: #d5e0e5; }
    .receive-workspace .purchase-date-input input { min-width: 0; width: 100%; font-family: inherit; font-size: .74rem; }
    .receive-list-filters .form-select { width: 100%; height: 40px; border-color: #d5e0e5; border-radius: 8px; padding-left: .75rem; color: #3c535f; font-size: .74rem; }
    .receive-workspace #tablePenerimaan { margin-top: .3rem; }
    .receive-workspace #tablePenerimaan thead th { padding-block: .85rem; border-bottom-color: #e2e9ee; color: #6b7b88; background: #f6f8fa; font-size: .63rem; font-weight: 700; letter-spacing: .025em; }
    .receive-workspace #tablePenerimaan tbody td { padding-block: 1rem; font-size: .75rem; }
    .receive-workspace #tablePenerimaan .purchase-po-number { font-size: .74rem; }
    .receive-workspace #tablePenerimaan .purchase-money { color: #24524c; background: transparent; font-weight: 700; }
    .receive-workspace #tablePenerimaan .purchase-badge.is-distributor { color: #3c535f; background: #f4f7f9; }
    .receive-workspace .purchase-table-hint { align-items: flex-start; gap: .5rem; padding: .85rem 1.25rem; background: #fafcfd; font-size: .68rem; line-height: 1.7; }
    .receive-workspace .purchase-table-hint > i { color: #0f766e; font-size: .9rem; }

    @media (min-width: 1200px) {
        .receive-filter-panel { display: flex; flex-wrap: wrap; align-items: end; gap: 1rem; padding: .9rem 1.25rem; }
        .receive-filter-panel .receive-status-row { flex: 0 0 auto; margin: 0; }
        .receive-filter-panel .receive-period-caption { display: none; }
        .receive-filter-panel .receive-list-filters { flex: 1 1 460px; padding: 0; grid-template-columns: minmax(200px, 1fr) minmax(140px, .6fr) 85px; }
        .receive-filter-panel .receive-status-tabs { gap: .7rem; }
    }

    @media (max-width: 1199.98px) {
        .receive-page-hero { gap: 1.5rem; padding: 1.5rem; }
        .receive-metric { padding: 1rem; }
        .receive-metric-label { font-size: .68rem; }
        .receive-metric-label i { flex-basis: 26px; height: 26px; }
    }

    @media (max-width: 991.98px) {
        .receive-page-hero { grid-template-columns: 1fr; gap: 1.25rem; }
        .receive-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .8rem; padding: .85rem; }
        .receive-steps li { grid-template-columns: 26px minmax(0, 1fr); align-items: start; gap: .5rem; padding: 0; }
        .receive-steps li + li { border-top: 0; }
        .receive-steps li > i { display: none; }
        .receive-step-number { width: 26px; height: 26px; }
        .receive-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .receive-metric:nth-child(3) { border-left: 0; }
        .receive-metric:nth-child(n + 3) { border-top: 1px solid var(--purchase-border); }
        .receive-metric.is-value > strong { font-size: 1.4rem; }
        .receive-search-panel { gap: 1rem; }
    }

    @media (max-width: 767.98px) {
        .receive-page-hero { padding: 1.2rem; border-radius: 12px; }
        .receive-eyebrow { font-size: .58rem; }
        .receive-page-hero h1 { font-size: 1.5rem; margin-block: .45rem; }
        .receive-page-hero p { font-size: .75rem; line-height: 1.7; }
        .receive-hero-actions { margin-top: 1rem; }
        .receive-hero-actions .btn { min-height: 44px; font-size: .73rem; padding-inline: .8rem; }
        .receive-steps { display: none; }
        .receive-overview { margin: 1rem 0; }
        .receive-section-heading { gap: .3rem; margin-bottom: .6rem; }
        .receive-section-heading h2 { font-size: .76rem; }
        .receive-section-heading > span { font-size: .6rem; }
        .receive-metric { padding: .7rem .9rem; }
        .receive-metric-label { font-size: .67rem; }
        .receive-metric-label i { flex-basis: 25px; height: 25px; font-size: .95rem; }
        .receive-metric > strong { margin-block: .3rem .15rem; font-size: 1.35rem; }
        .receive-metric.is-value > strong { font-size: 1.05rem; }
        .receive-metric > small { display: none; }
        .receive-overview-footer { gap: .4rem 1rem; padding: .55rem .9rem; font-size: .62rem; }
        .receive-overview-footer small { display: none; }
        .receive-list-heading { padding: 1rem; gap: .75rem; }
        .receive-list-heading h2 { font-size: .86rem; line-height: 1.5; }
        .receive-list-heading .purchase-table-title-icon { display: none; }
        .receive-list-heading .purchase-table-title p { display: block; font-size: .67rem; }
        .receive-list-actions { width: 100%; flex-wrap: wrap; }
        .receive-list-actions .btn { min-height: 44px; }
        .receive-list-actions .purchase-icon-btn { width: 44px; }
        .receive-list-actions .receive-filter-toggle { display: inline-flex; }
        .receive-list-actions .receive-filter-toggle.is-active { border-color: #0f766e; color: #0b6d64; background: #eaf6f3; }
        .receive-list-actions .receive-create-button { margin-left: auto; }
        .receive-workspace #openPenerimaanModalToolbar { font-size: .72rem; }
        .receive-search-panel { grid-template-columns: 1fr; gap: .9rem; margin-inline: 1rem; padding: .8rem; }
        .receive-workspace .purchase-search input,
        .receive-workspace .receive-medicine-select .select2-selection--single .select2-selection__rendered { font-size: 16px; }
        .receive-workspace #clearReceiveMedicineFilter { min-height: 46px; min-width: 44px; }
        .receive-document-search > p { display: none; }
        .receive-workspace #receiveMedicineFilterHint { font-size: .63rem; }
        .receive-status-row { margin: .8rem 1rem 0; }
        .receive-period-caption { padding-block: .2rem .6rem; font-size: .63rem; }
        .receive-status-tabs { width: 100%; }
        .receive-status-tabs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .25rem; }
        .receive-workspace .purchase-filter-chip { flex-wrap: wrap; align-content: center; gap: .2rem; min-height: 50px; padding: .5rem 0; font-size: .65rem; }
        .receive-workspace .purchase-filter-chip .purchase-filter-count { min-width: 17px; height: 19px; font-size: .6rem; }
        .receive-status-dot { display: none; }
        .receive-list-filters { display: none; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: .9rem 1rem; }
        .receive-list-filters.is-mobile-open { display: grid; }
        .receive-date-field { grid-column: 1 / -1; }
        .receive-workspace .purchase-date-input,
        .receive-list-filters .form-select { min-height: 44px; }
        .receive-workspace .purchase-date-input input,
        .receive-list-filters .form-select { font-size: 16px; }
        .receive-workspace #tablePenerimaan tbody td { font-size: .75rem; }
        .receive-workspace .purchase-table-hint { padding: .75rem 1rem; font-size: .64rem; }
    }

    @media (max-width: 359.98px) {
        .receive-page-hero { padding: 1rem; }
        .receive-hero-actions { gap: .25rem; }
        .receive-hero-actions .btn { padding-inline: .65rem; font-size: .68rem; }
        .receive-metric { padding: .7rem; }
        .receive-metric-label i { display: none; }
        .receive-list-heading, .receive-list-filters { padding-inline: .8rem; }
        .receive-search-panel, .receive-status-row { margin-inline: .8rem; }
        .receive-list-actions .btn { font-size: .68rem; padding-inline: .6rem; }
        .receive-list-actions .receive-filter-toggle span { display: none; }
    }
</style>
