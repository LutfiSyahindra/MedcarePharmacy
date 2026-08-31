<style>
    .sales-report-page {
        --sr-navy: #12233f;
        --sr-blue: #2563eb;
        --sr-cyan: #0891b2;
        --sr-teal: #0f9f83;
        --sr-green: #14946a;
        --sr-amber: #d99416;
        --sr-red: #dc4b56;
        --sr-violet: #7457d9;
        --sr-ink: #17233a;
        --sr-muted: #6e7d93;
        --sr-line: #e5eaf2;
        --sr-soft: #f5f7fb;
        --sr-shadow: 0 16px 40px rgba(28, 45, 75, .08);
        color: var(--sr-ink);
        font-family: "Plus Jakarta Sans", sans-serif;
        padding-bottom: 40px;
    }

    .sales-report-page *,
    .sales-report-page *::before,
    .sales-report-page *::after { box-sizing: border-box; }

    .sales-report-page .page-breadcrumb { margin-bottom: 18px; }
    .sales-report-page .breadcrumb { font-size: 12px; font-weight: 700; }
    .sales-report-page .breadcrumb a { color: #527099; }

    .sr-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(330px, .75fr);
        gap: 34px;
        min-height: 270px;
        padding: 40px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 26px;
        color: #fff;
        background:
            radial-gradient(circle at 15% 0%, rgba(45, 123, 255, .3), transparent 35%),
            linear-gradient(135deg, #101e37 0%, #182e50 52%, #123f56 100%);
        box-shadow: 0 24px 54px rgba(14, 35, 64, .2);
    }

    .sr-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        opacity: .12;
        background-image: linear-gradient(rgba(255,255,255,.2) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.2) 1px, transparent 1px);
        background-size: 38px 38px;
        mask-image: linear-gradient(90deg, #000, transparent 85%);
    }

    .sr-hero-glow { position: absolute; z-index: -1; border-radius: 999px; filter: blur(2px); pointer-events: none; }
    .sr-hero-glow-one { right: -70px; top: -90px; width: 270px; height: 270px; background: rgba(36, 211, 191, .13); }
    .sr-hero-glow-two { left: 46%; bottom: -160px; width: 320px; height: 320px; background: rgba(102, 117, 255, .13); }

    .sr-hero-main { display: flex; align-items: center; gap: 26px; min-width: 0; }
    .sr-hero-mark {
        flex: 0 0 82px;
        width: 82px;
        height: 82px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 24px;
        color: #dffbff;
        background: rgba(255,255,255,.1);
        box-shadow: inset 0 1px rgba(255,255,255,.16), 0 18px 34px rgba(0,0,0,.14);
        backdrop-filter: blur(10px);
    }
    .sr-hero-mark i { font-size: 39px; }
    .sr-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 12px;
        color: #91ecdf;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .13em;
        text-transform: uppercase;
    }
    .sr-hero-copy h1 { margin: 0 0 11px; color: #fff; font-size: clamp(27px, 3vw, 40px); font-weight: 800; letter-spacing: -.04em; }
    .sr-hero-copy > p { max-width: 700px; margin: 0; color: rgba(234, 243, 255, .76); font-size: 14px; line-height: 1.8; }
    .sr-hero-pills { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 23px; }
    .sr-hero-pills span { display: inline-flex; align-items: center; gap: 7px; padding: 7px 11px; border: 1px solid rgba(255,255,255,.12); border-radius: 999px; color: rgba(244,248,255,.83); background: rgba(255,255,255,.07); font-size: 10px; font-weight: 700; }
    .sr-hero-pills span > i:not(.mdi) { width: 7px; height: 7px; border-radius: 50%; background: #4de3bd; box-shadow: 0 0 0 4px rgba(77,227,189,.12); }

    .sr-hero-context {
        align-self: stretch;
        display: grid;
        align-content: center;
        gap: 11px;
        padding: 16px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 20px;
        background: rgba(7, 21, 42, .22);
        backdrop-filter: blur(12px);
    }
    .sr-hero-context > div { display: flex; align-items: center; gap: 12px; padding: 11px; border-radius: 14px; transition: background .2s ease; }
    .sr-hero-context > div:hover { background: rgba(255,255,255,.06); }
    .sr-context-icon { width: 38px; height: 38px; flex: 0 0 38px; display: grid; place-items: center; border-radius: 12px; color: #9beee6; background: rgba(255,255,255,.08); font-size: 19px; }
    .sr-hero-context small, .sr-hero-context strong { display: block; }
    .sr-hero-context small { margin-bottom: 3px; color: rgba(224,234,248,.55); font-size: 9px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
    .sr-hero-context strong { color: #f8fbff; font-size: 12px; font-weight: 700; line-height: 1.45; }

    .sr-report-switcher, .sr-filter-card, .sr-panel {
        border: 1px solid var(--sr-line);
        border-radius: 22px;
        background: #fff;
        box-shadow: var(--sr-shadow);
    }
    .sr-report-switcher { margin-top: 22px; padding: 25px; }
    .sr-section-intro { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; }
    .sr-section-intro h2 { margin: 4px 0 0; color: var(--sr-ink); font-size: 19px; font-weight: 800; letter-spacing: -.025em; }
    .sr-section-intro > p { max-width: 540px; margin: 0; color: var(--sr-muted); font-size: 11px; line-height: 1.65; text-align: right; }
    .sr-kicker { color: #6780a2; font-size: 9px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }

    .sr-report-nav {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-top: 20px;
    }
    .sr-report-link {
        --link-tone: #5676a6;
        position: relative;
        overflow: hidden;
        min-height: 78px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px;
        border: 1px solid #e9edf4;
        border-radius: 15px;
        color: var(--sr-ink);
        background: #fbfcfe;
        text-decoration: none !important;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .sr-report-link::before { content: ""; position: absolute; left: 0; top: 12px; bottom: 12px; width: 3px; border-radius: 0 4px 4px 0; background: var(--link-tone); opacity: 0; }
    .sr-report-link:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--link-tone) 28%, #dfe5ee); color: var(--sr-ink); box-shadow: 0 10px 24px rgba(33,53,84,.08); }
    .sr-report-link.is-active { border-color: color-mix(in srgb, var(--link-tone) 36%, #dfe5ee); background: color-mix(in srgb, var(--link-tone) 6%, #fff); box-shadow: 0 10px 25px color-mix(in srgb, var(--link-tone) 10%, transparent); }
    .sr-report-link.is-active::before { opacity: 1; }
    .sr-report-link-icon { width: 38px; height: 38px; flex: 0 0 38px; display: grid; place-items: center; border-radius: 12px; color: var(--link-tone); background: color-mix(in srgb, var(--link-tone) 10%, #fff); font-size: 20px; }
    .sr-report-link-copy { min-width: 0; }
    .sr-report-link-copy small { display: block; margin-bottom: 3px; color: #a0aabd; font-size: 8px; font-weight: 800; }
    .sr-report-link-copy b { display: block; overflow: hidden; color: #34425a; font-size: 10.5px; font-weight: 800; line-height: 1.35; text-overflow: ellipsis; white-space: nowrap; }
    .sr-report-arrow { position: absolute; right: 7px; top: 6px; color: var(--link-tone); font-size: 12px; opacity: 0; transform: translate(-2px, 2px); transition: .18s ease; }
    .sr-report-link:hover .sr-report-arrow, .sr-report-link.is-active .sr-report-arrow { opacity: .65; transform: translate(0,0); }
    .sr-link-tone-navy { --link-tone: #35577f; } .sr-link-tone-blue { --link-tone: #3678dd; }
    .sr-link-tone-teal { --link-tone: #169d88; } .sr-link-tone-violet { --link-tone: #765ed2; }
    .sr-link-tone-indigo { --link-tone: #5967cc; } .sr-link-tone-cyan { --link-tone: #1597b1; }
    .sr-link-tone-green { --link-tone: #23946d; } .sr-link-tone-purple { --link-tone: #9356b6; }
    .sr-link-tone-orange { --link-tone: #db7a33; } .sr-link-tone-amber { --link-tone: #d99a25; }
    .sr-link-tone-red { --link-tone: #db5361; } .sr-link-tone-rose { --link-tone: #c95783; }

    .sr-filter-card { margin-top: 22px; overflow: hidden; }
    .sr-filter-head { display: flex; align-items: center; justify-content: space-between; gap: 15px; padding: 20px 23px; border-bottom: 1px solid var(--sr-line); }
    .sr-panel-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .sr-panel-heading h2 { margin: 0 0 4px; color: var(--sr-ink); font-size: 15px; font-weight: 800; letter-spacing: -.02em; }
    .sr-panel-heading p { margin: 0; color: var(--sr-muted); font-size: 10px; line-height: 1.5; }
    .sr-heading-icon { width: 40px; height: 40px; flex: 0 0 40px; display: grid; place-items: center; border-radius: 12px; color: #376eae; background: #edf5ff; font-size: 20px; }
    .sr-heading-icon.is-chart { color: #4771bf; background: #edf3ff; }
    .sr-heading-icon.is-table { color: #167f71; background: #e9f8f4; }
    .sr-icon-button { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid #dfe5ed; border-radius: 10px; color: #607088; background: #fff; font-size: 18px; transition: .18s ease; }
    .sr-icon-button:hover { border-color: #bdcadb; color: var(--sr-blue); background: #f7faff; }
    .sr-filter-body { padding: 20px 23px 18px; }
    .sr-filter-body.is-collapsed { display: none; }
    .sr-period-presets { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; margin-bottom: 17px; }
    .sr-period-presets > span { margin-right: 4px; color: #7f8da2; font-size: 9px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .sr-period-presets button { padding: 7px 11px; border: 1px solid #e1e7ef; border-radius: 9px; color: #637289; background: #fff; font-size: 9px; font-weight: 800; transition: .16s ease; }
    .sr-period-presets button:hover { border-color: #b9c7db; color: #315f9f; }
    .sr-period-presets button.is-active { border-color: #bed5f4; color: #2260ad; background: #ecf5ff; box-shadow: inset 0 0 0 1px rgba(37,99,235,.04); }
    .sr-filter-grid { display: grid; grid-template-columns: minmax(230px, 1.45fr) repeat(2, minmax(170px, 1fr)) minmax(175px, .82fr); gap: 13px; align-items: end; }
    .sr-filter-grid.has-supplier { grid-template-columns: minmax(190px, 1.2fr) minmax(190px, 1.2fr) repeat(2, minmax(155px, .9fr)) minmax(165px, .78fr); }
    .sr-field { display: grid; gap: 7px; margin: 0; }
    .sr-field > span:first-child { color: #64738a; font-size: 9px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
    .sr-field-control { position: relative; display: block; }
    .sr-field-control > i { position: absolute; left: 12px; top: 50%; z-index: 2; color: #8191a8; font-size: 17px; transform: translateY(-50%); pointer-events: none; }
    .sr-field-control .form-control, .sr-field-control .form-select { min-height: 43px; padding-left: 38px; border-color: #dfe5ed; border-radius: 11px; color: #33425a; background-color: #fff; font-size: 11px; font-weight: 650; box-shadow: none; }
    .sr-field-control .form-control:focus, .sr-field-control .form-select:focus { border-color: #9abce7; box-shadow: 0 0 0 3px rgba(37,99,235,.08); }
    .sr-apply-button { min-height: 43px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 0; border-radius: 11px; color: #fff; background: linear-gradient(135deg, #235fba, #1b7e9e); font-size: 10px; font-weight: 800; box-shadow: 0 10px 20px rgba(34,101,164,.2); }
    .sr-apply-button:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 13px 25px rgba(34,101,164,.25); }
    .sr-apply-button:disabled { opacity: .75; transform: none; }
    .sr-active-filter { display: flex; align-items: center; gap: 13px; margin-top: 17px; padding: 10px 12px; border: 1px solid #e5ebf2; border-radius: 11px; color: #63738a; background: #f8fafc; font-size: 9px; }
    .sr-active-filter > span { flex: 0 0 auto; color: #466789; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
    .sr-active-filter > div { flex: 1; display: flex; flex-wrap: wrap; gap: 6px; }
    .sr-active-filter em { color: #8a96a8; font-style: normal; }
    .sr-filter-chip { display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: 999px; color: #4d6581; background: #edf2f7; font-weight: 700; }
    .sr-active-filter button { border: 0; color: #7b8798; background: transparent; font-size: 9px; font-weight: 800; }
    .sr-active-filter button:hover { color: var(--sr-red); }

    .sr-results-intro { margin: 30px 2px 15px; }
    .sr-metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px; }
    .sr-metric-card {
        --metric-tone: #315f99;
        position: relative;
        overflow: hidden;
        min-height: 143px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 20px;
        border: 1px solid #e5eaf1;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 13px 30px rgba(29,47,77,.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .sr-metric-card::after { content: ""; position: absolute; right: -36px; top: -42px; width: 110px; height: 110px; border-radius: 50%; background: color-mix(in srgb, var(--metric-tone) 7%, transparent); }
    .sr-metric-card:hover { transform: translateY(-3px); box-shadow: 0 18px 36px rgba(29,47,77,.1); }
    .sr-metric-icon { flex: 0 0 43px; width: 43px; height: 43px; display: grid; place-items: center; border-radius: 13px; color: var(--metric-tone); background: color-mix(in srgb, var(--metric-tone) 10%, #fff); font-size: 22px; }
    .sr-metric-card > div { min-width: 0; }
    .sr-metric-card small { display: block; margin: 1px 0 8px; color: #728198; font-size: 9px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .sr-metric-card strong { display: block; overflow: hidden; color: #1d2c45; font-size: clamp(19px, 2vw, 25px); font-weight: 800; line-height: 1.15; letter-spacing: -.035em; text-overflow: ellipsis; white-space: nowrap; }
    .sr-metric-card p { margin: 9px 0 0; color: #8793a5; font-size: 9px; line-height: 1.5; }
    .sr-metric-tone-navy { --metric-tone: #355b89; } .sr-metric-tone-blue { --metric-tone: #3679d6; }
    .sr-metric-tone-teal { --metric-tone: #15977f; } .sr-metric-tone-green { --metric-tone: #209369; }
    .sr-metric-tone-amber { --metric-tone: #cf901d; } .sr-metric-tone-orange { --metric-tone: #db7534; }
    .sr-metric-tone-violet { --metric-tone: #7659d0; } .sr-metric-tone-red { --metric-tone: #d84e5c; }
    .sr-metric-card.is-loading .sr-metric-icon, .sr-metric-card.is-loading strong, .sr-metric-card.is-loading p { color: transparent; background: linear-gradient(90deg,#eef1f5 25%,#f8f9fb 50%,#eef1f5 75%); background-size: 200% 100%; animation: srShimmer 1.3s infinite; }
    .sr-metric-card.is-loading strong { width: 120px; height: 24px; border-radius: 5px; }
    .sr-metric-card.is-loading p { width: 150px; height: 10px; border-radius: 4px; }
    @keyframes srShimmer { to { background-position: -200% 0; } }

    .sr-insight-layout { display: grid; grid-template-columns: minmax(0, 2.15fr) minmax(260px, .75fr); gap: 16px; margin-top: 16px; }
    .sr-chart-panel { min-height: 390px; padding: 21px; }
    .sr-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; }
    .sr-chart-legend { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 12px; color: #718097; font-size: 9px; font-weight: 700; }
    .sr-chart-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .sr-chart-legend i { width: 8px; height: 8px; border-radius: 3px; background: #316fbd; }
    .sr-chart-legend span:nth-child(2) i { background: #9bded5; }
    .sr-chart-shell { position: relative; min-height: 300px; margin-top: 18px; }
    .sr-chart-shell svg { display: block; width: 100%; height: 300px; overflow: visible; }
    .sr-chart-grid { stroke: #e8edf4; stroke-width: 1; stroke-dasharray: 4 5; }
    .sr-chart-axis-label { fill: #8d99ab; font-size: 9px; font-family: "Plus Jakarta Sans", sans-serif; }
    .sr-chart-area { fill: url(#srAreaGradient); }
    .sr-chart-line { fill: none; stroke: #296cba; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; filter: drop-shadow(0 5px 6px rgba(41,108,186,.16)); }
    .sr-chart-bar { fill: #d9f1ed; rx: 3; opacity: .9; }
    .sr-chart-point { fill: #fff; stroke: #296cba; stroke-width: 2; opacity: .75; }
    .sr-chart-hit { fill: transparent; cursor: crosshair; }
    .sr-chart-empty { min-height: 285px; display: grid; place-items: center; text-align: center; color: #7e8ca0; }
    .sr-chart-empty i { display: block; margin-bottom: 8px; color: #b1bdcc; font-size: 36px; }
    .sr-loading-state { min-height: 150px; display: flex; align-items: center; justify-content: center; gap: 9px; color: #75849a; font-size: 10px; }
    .sr-loading-state i { color: #3675c5; font-size: 21px; }
    .sr-chart-tooltip { position: absolute; z-index: 5; min-width: 150px; padding: 10px 12px; border: 1px solid rgba(255,255,255,.1); border-radius: 11px; color: #fff; background: rgba(17,34,59,.94); box-shadow: 0 12px 30px rgba(18,35,63,.22); pointer-events: none; transform: translate(-50%, -112%); }
    .sr-chart-tooltip small { display: block; margin-bottom: 5px; color: #aebbd0; font-size: 8px; }
    .sr-chart-tooltip strong { display: block; font-size: 11px; }
    .sr-chart-tooltip span { display: block; margin-top: 3px; color: #9fe1d8; font-size: 9px; }

    .sr-insight-stack { display: grid; grid-template-rows: 1fr auto; gap: 16px; }
    .sr-insight-card { position: relative; overflow: hidden; min-height: 230px; padding: 22px; border-radius: 20px; color: #fff; background: linear-gradient(145deg, #18345d 0%, #265b7a 100%); box-shadow: 0 17px 35px rgba(21,53,85,.17); }
    .sr-insight-card::after { content: ""; position: absolute; right: -55px; top: -55px; width: 160px; height: 160px; border-radius: 50%; background: rgba(72,218,192,.12); }
    .sr-insight-top { display: flex; align-items: center; gap: 9px; }
    .sr-insight-top > span { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 11px; color: #8ef0dd; background: rgba(255,255,255,.09); font-size: 18px; }
    .sr-insight-top small { color: rgba(231,240,250,.58); font-size: 8px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .sr-insight-card h3 { position: relative; z-index: 1; margin: 21px 0 9px; color: #fff; font-size: 17px; font-weight: 800; line-height: 1.4; }
    .sr-insight-card > p { position: relative; z-index: 1; margin: 0; color: rgba(233,242,252,.7); font-size: 10px; line-height: 1.7; }
    .sr-insight-peak { position: relative; z-index: 1; margin-top: 19px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,.11); }
    .sr-insight-peak small, .sr-insight-peak strong { display: block; }
    .sr-insight-peak small { margin-bottom: 4px; color: rgba(226,237,250,.5); font-size: 8px; text-transform: uppercase; }
    .sr-insight-peak strong { color: #a4ecdf; font-size: 16px; font-weight: 800; }
    .sr-health-card { padding: 18px; border: 1px solid var(--sr-line); border-radius: 18px; background: #fff; box-shadow: 0 12px 28px rgba(29,47,77,.06); }
    .sr-health-head { display: flex; align-items: center; gap: 10px; }
    .sr-health-head > span { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 10px; color: #168f77; background: #e9f8f4; font-size: 17px; }
    .sr-health-head small, .sr-health-head b { display: block; }
    .sr-health-head small { color: #8a96a8; font-size: 8px; text-transform: uppercase; }
    .sr-health-head b { color: #46566d; font-size: 10px; }
    .sr-health-card > strong { display: block; margin: 15px 0 9px; color: #20314b; font-size: 18px; font-weight: 800; letter-spacing: -.025em; }
    .sr-health-track { height: 5px; overflow: hidden; border-radius: 999px; background: #edf1f5; }
    .sr-health-track span { display: block; width: 100%; height: 100%; border-radius: inherit; background: linear-gradient(90deg,#23a383,#4dc3ae); transition: width .4s ease; }
    .sr-health-card p { margin: 8px 0 0; color: #8390a2; font-size: 8.5px; line-height: 1.5; }
    .sr-health-card p span { color: #197d6c; font-weight: 800; }

    .sr-table-panel { margin-top: 16px; overflow: hidden; }
    .sr-table-toolbar { min-height: 78px; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 18px 21px; border-bottom: 1px solid var(--sr-line); }
    .sr-table-actions { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 8px; }
    .sr-table-search { position: relative; margin: 0; }
    .sr-table-search > i { position: absolute; left: 11px; top: 50%; color: #8491a4; font-size: 16px; transform: translateY(-50%); pointer-events: none; }
    .sr-table-search input { width: 215px; height: 36px; padding: 0 34px 0 34px; border: 1px solid #dfe5ed; border-radius: 9px; outline: 0; color: #34445d; background: #fff; font-size: 9.5px; }
    .sr-table-search input:focus { border-color: #9abce7; box-shadow: 0 0 0 3px rgba(37,99,235,.07); }
    .sr-table-search button { position: absolute; right: 5px; top: 50%; width: 26px; height: 26px; padding: 0; border: 0; color: #8895a6; background: transparent; transform: translateY(-50%); }
    .sr-page-size { display: flex; align-items: center; gap: 5px; margin: 0 3px; color: #7a8799; font-size: 9px; font-weight: 700; }
    .sr-page-size select { width: 66px; height: 34px; border-color: #dfe5ed; border-radius: 9px; font-size: 9px; }
    .sr-table-actions .btn { min-height: 35px; display: inline-flex; align-items: center; gap: 5px; border-radius: 9px; font-size: 9px; font-weight: 800; }
    .sr-table-wrap { min-height: 285px; max-height: 590px; }
    .sr-table { min-width: 980px; margin: 0; }
    .sr-table thead { position: sticky; top: 0; z-index: 3; }
    .sr-table thead th { padding: 12px 13px; border: 0; border-bottom: 1px solid #e4e9f0; color: #67758a; background: #f7f9fc; font-size: 8.5px; font-weight: 800; letter-spacing: .055em; text-transform: uppercase; white-space: nowrap; }
    .sr-table thead th button { display: inline-flex; align-items: center; gap: 4px; padding: 0; border: 0; color: inherit; background: transparent; font: inherit; letter-spacing: inherit; text-transform: inherit; }
    .sr-table thead th button:hover { color: #315f9f; }
    .sr-table thead th .mdi { color: #99a5b5; font-size: 13px; }
    .sr-table thead th.is-sorted { color: #295f9f; background: #f0f5fb; }
    .sr-table thead th.is-sorted .mdi { color: #2f72bf; }
    .sr-table tbody td { padding: 12px 13px; border-color: #edf0f4; color: #47566b; font-size: 9.5px; line-height: 1.5; vertical-align: middle; }
    .sr-table tbody tr { transition: background .15s ease; }
    .sr-table tbody tr:hover { background: #fafcff; }
    .sr-table td.is-number, .sr-table th.is-number { text-align: right; font-variant-numeric: tabular-nums; }
    .sr-table td.is-strong { color: #22344f; font-weight: 800; }
    .sr-table td.is-negative { color: #cc4655; font-weight: 800; }
    .sr-table td.is-positive { color: #16836f; font-weight: 800; }
    .sr-cell-primary { display: block; max-width: 230px; color: #263954; font-weight: 750; }
    .sr-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: 999px; color: #315f91; background: #edf4fc; font-size: 8px; font-weight: 800; white-space: nowrap; }
    .sr-badge::before { content: ""; width: 5px; height: 5px; border-radius: 50%; background: currentColor; opacity: .75; }
    .sr-badge.is-green { color: #147c67; background: #e9f8f3; }
    .sr-badge.is-amber { color: #a26a10; background: #fff6df; }
    .sr-badge.is-red { color: #c44553; background: #fff0f1; }
    .sr-empty-state { min-height: 260px; display: grid; place-items: center; padding: 35px; text-align: center; }
    .sr-empty-state span { width: 58px; height: 58px; display: grid; place-items: center; margin: 0 auto 12px; border-radius: 18px; color: #7b91ad; background: #edf3f8; font-size: 29px; }
    .sr-empty-state h3 { margin: 0 0 6px; color: #34445c; font-size: 14px; font-weight: 800; }
    .sr-empty-state p { margin: 0; color: #8592a5; font-size: 10px; }
    .sr-pagination { min-height: 62px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 20px; border-top: 1px solid var(--sr-line); color: #7d8a9b; background: #fbfcfe; font-size: 9.5px; }
    .sr-pagination > div { display: flex; align-items: center; gap: 8px; }
    .sr-pagination button { width: 32px; height: 32px; display: grid; place-items: center; padding: 0; border: 1px solid #dfe5ed; border-radius: 9px; color: #52657d; background: #fff; font-size: 17px; }
    .sr-pagination button:not(:disabled):hover { border-color: #abc0dc; color: #2e68ab; }
    .sr-pagination button:disabled { opacity: .45; cursor: not-allowed; }
    .sr-pagination #srPageLabel { min-width: 80px; text-align: center; font-weight: 750; }

    .sr-toast { position: fixed; right: 24px; bottom: 25px; z-index: 1080; display: flex; align-items: center; gap: 9px; max-width: 340px; padding: 12px 16px; border-radius: 12px; color: #fff; background: #183a55; box-shadow: 0 16px 34px rgba(17,40,61,.24); font-size: 10px; font-weight: 700; }
    .sr-toast i { color: #76e2c8; font-size: 18px; }
    .sr-toast.is-error { background: #8c3440; }
    .sr-toast.is-error i { color: #ffd1d6; }

    @media (max-width: 1399px) {
        .sr-report-nav { grid-template-columns: repeat(4, minmax(0,1fr)); }
        .sr-filter-grid { grid-template-columns: 1.3fr 1fr 1fr .8fr; }
        .sr-filter-grid.has-supplier { grid-template-columns: repeat(2, minmax(180px, 1.15fr)) repeat(2, minmax(150px, .9fr)) minmax(155px, .75fr); }
    }
    @media (max-width: 1199px) {
        .sr-hero { grid-template-columns: 1fr; }
        .sr-hero-context { grid-template-columns: repeat(3, minmax(0,1fr)); }
        .sr-filter-grid.has-supplier { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .sr-filter-grid.has-supplier .sr-apply-button { grid-column: 1 / -1; }
        .sr-insight-layout { grid-template-columns: 1fr; }
        .sr-insight-stack { grid-template-columns: 1.2fr 1fr; grid-template-rows: none; }
        .sr-insight-card { min-height: 205px; }
        .sr-metric-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .sr-table-toolbar { align-items: flex-start; flex-direction: column; }
        .sr-table-actions { width: 100%; justify-content: flex-start; }
    }
    @media (max-width: 991px) {
        .sr-report-nav { display: flex; overflow-x: auto; padding-bottom: 7px; scroll-snap-type: x mandatory; }
        .sr-report-link { flex: 0 0 165px; scroll-snap-align: start; }
        .sr-filter-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .sr-filter-grid.has-supplier { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .sr-filter-grid.has-supplier .sr-field-branch { grid-column: auto; }
        .sr-field-branch { grid-column: 1 / -1; }
        .sr-apply-button { grid-column: 1 / -1; }
    }
    @media (max-width: 767px) {
        .sr-hero { padding: 25px 21px; border-radius: 20px; }
        .sr-hero-main { align-items: flex-start; flex-direction: column; gap: 17px; }
        .sr-hero-mark { width: 62px; height: 62px; flex-basis: 62px; border-radius: 18px; }
        .sr-hero-mark i { font-size: 30px; }
        .sr-hero-context { grid-template-columns: 1fr; }
        .sr-report-switcher { padding: 19px; }
        .sr-section-intro { align-items: flex-start; flex-direction: column; gap: 7px; }
        .sr-section-intro > p { text-align: left; }
        .sr-filter-body, .sr-filter-head { padding-left: 17px; padding-right: 17px; }
        .sr-filter-grid { grid-template-columns: 1fr; }
        .sr-filter-grid.has-supplier { grid-template-columns: 1fr; }
        .sr-filter-grid.has-supplier .sr-field-branch { grid-column: auto; }
        .sr-field-branch, .sr-apply-button { grid-column: auto; }
        .sr-active-filter { align-items: flex-start; flex-wrap: wrap; }
        .sr-active-filter > div { order: 3; flex-basis: 100%; }
        .sr-metric-grid { grid-template-columns: 1fr; }
        .sr-insight-stack { grid-template-columns: 1fr; }
        .sr-panel-head { align-items: flex-start; flex-direction: column; }
        .sr-chart-legend { justify-content: flex-start; }
        .sr-table-actions { align-items: stretch; }
        .sr-table-search { flex: 1 0 100%; }
        .sr-table-search input { width: 100%; }
        .sr-pagination { align-items: flex-start; flex-direction: column; }
    }

    @media print {
        body { background: #fff !important; }
        .sidebar, .navbar, .footer, .medcare-tab-shell, .page-breadcrumb,
        .sr-report-switcher, .sr-filter-card, .sr-table-actions, .sr-pagination,
        .sr-toast { display: none !important; }
        .page-wrapper, .page-content { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .sales-report-page { color: #111; }
        .sr-hero { min-height: 0; grid-template-columns: 1fr; padding: 24px; border-radius: 12px; box-shadow: none; print-color-adjust: exact; }
        .sr-hero-context { grid-template-columns: repeat(3,1fr); }
        .sr-metric-card, .sr-panel { box-shadow: none; break-inside: avoid; }
        .sr-insight-layout { grid-template-columns: 2fr 1fr; }
        .sr-insight-stack { grid-template-columns: 1fr; }
        .sr-table-wrap { max-height: none; overflow: visible !important; }
        .sr-table { min-width: 100%; }
        .sr-table thead { position: static; }
    }

    /* Premium workspace refinement: clearer hierarchy, denser navigation, larger readable type. */
    .sales-report-page {
        --sr-ink: #142238;
        --sr-muted: #65748a;
        --sr-line: #e1e7ef;
        --sr-soft: #f4f7fb;
        --sr-shadow: 0 12px 34px rgba(26, 43, 72, .075);
        scroll-behavior: smooth;
    }

    .sales-report-page :is(button, a, input, select):focus-visible {
        outline: 3px solid rgba(37, 99, 235, .2);
        outline-offset: 2px;
    }

    .sr-breadcrumb { margin-bottom: 14px !important; }
    .sr-breadcrumb .breadcrumb { margin-bottom: 0; font-size: 11px; }
    .sr-breadcrumb .breadcrumb a { display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }

    .sr-hero {
        grid-template-columns: minmax(0, 1.65fr) minmax(290px, .7fr);
        gap: 28px;
        min-height: 230px;
        padding: 30px 32px;
        border-radius: 24px;
        background:
            radial-gradient(circle at 4% -10%, rgba(56, 139, 255, .35), transparent 35%),
            radial-gradient(circle at 96% 110%, rgba(26, 188, 169, .22), transparent 38%),
            linear-gradient(132deg, #0d1c34 0%, #173151 52%, #10475a 100%);
        box-shadow: 0 22px 50px rgba(11, 30, 56, .2);
    }

    .sr-hero-main { align-items: flex-start; gap: 22px; }
    .sr-hero-mark { width: 68px; height: 68px; flex-basis: 68px; border-radius: 20px; }
    .sr-hero-mark i { font-size: 32px; }
    .sr-eyebrow { margin-bottom: 9px; font-size: 10px; }
    .sr-hero-copy h1 { margin-bottom: 8px; font-size: clamp(27px, 2.7vw, 36px); }
    .sr-hero-copy > p { max-width: 730px; font-size: 13px; line-height: 1.65; }
    .sr-hero-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 17px; }
    .sr-hero-actions a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid rgba(255, 255, 255, .13);
        border-radius: 9px;
        color: rgba(242, 248, 255, .8);
        background: rgba(255, 255, 255, .065);
        font-size: 10px;
        font-weight: 750;
        text-decoration: none;
        transition: .18s ease;
    }
    .sr-hero-actions a:hover { border-color: rgba(139, 235, 220, .4); color: #fff; background: rgba(255, 255, 255, .11); transform: translateY(-1px); }
    .sr-hero-actions i { color: #8de4d7; font-size: 15px; }
    .sr-hero-pills { margin-top: 13px; }
    .sr-hero-pills span { padding: 0; border: 0; color: rgba(229, 238, 249, .62); background: transparent; font-size: 9px; }

    .sr-hero-context { gap: 7px; padding: 11px; border-radius: 18px; }
    .sr-hero-context > div { gap: 10px; padding: 9px 10px; }
    .sr-context-icon { width: 34px; height: 34px; flex-basis: 34px; border-radius: 10px; font-size: 17px; }
    .sr-hero-context small { font-size: 8px; }
    .sr-hero-context strong { font-size: 11px; }

    .sr-report-switcher { margin-top: 16px; padding: 18px 20px 16px; border-radius: 18px; }
    .sr-switcher-intro { align-items: center; }
    .sr-section-intro h2 { font-size: 18px; }
    .sr-section-intro > p { font-size: 11px; }
    .sr-kicker { font-size: 9px; }
    .sr-switcher-tools { display: flex; align-items: center; justify-content: flex-end; gap: 13px; }
    .sr-switcher-tools > p { max-width: 410px; margin: 0; color: var(--sr-muted); font-size: 10px; line-height: 1.55; text-align: right; }
    .sr-rail-controls { display: inline-flex; gap: 6px; }
    .sr-rail-controls button {
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        padding: 0;
        border: 1px solid #dce4ee;
        border-radius: 9px;
        color: #50637d;
        background: #fff;
        font-size: 18px;
        transition: .16s ease;
    }
    .sr-rail-controls button:not(:disabled):hover { border-color: #aebfd5; color: #245f9f; background: #f5f9ff; }
    .sr-rail-controls button:disabled { opacity: .35; cursor: default; }
    .sr-report-nav {
        display: flex;
        gap: 8px;
        margin-top: 14px;
        padding: 2px 1px 5px;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        scroll-behavior: smooth;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }
    .sr-report-nav::-webkit-scrollbar { display: none; }
    .sr-report-link {
        flex: 0 0 164px;
        min-height: 62px;
        gap: 9px;
        padding: 10px 11px;
        border-radius: 13px;
        scroll-snap-align: start;
    }
    .sr-report-link.is-active { flex-basis: 178px; }
    .sr-report-link::before { top: 10px; bottom: 10px; }
    .sr-report-link-icon { width: 34px; height: 34px; flex-basis: 34px; border-radius: 10px; font-size: 18px; }
    .sr-report-link-copy small { margin-bottom: 2px; font-size: 7.5px; letter-spacing: .035em; text-transform: uppercase; }
    .sr-report-link-copy b { font-size: 10px; }
    .sr-report-arrow { right: 8px; top: 7px; opacity: .38; }

    .sr-filter-card { margin-top: 16px; border-radius: 18px; }
    .sr-filter-head { padding: 15px 19px; }
    .sr-panel-heading h2 { font-size: 14px; }
    .sr-panel-heading p { font-size: 10px; }
    .sr-heading-icon { width: 38px; height: 38px; flex-basis: 38px; }
    .sr-filter-head-actions { display: flex; align-items: center; gap: 10px; }
    .sr-filter-status { display: inline-flex; align-items: center; gap: 7px; color: #3f6b61; font-size: 9px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .sr-filter-status > i { width: 7px; height: 7px; border-radius: 50%; background: #22a484; box-shadow: 0 0 0 4px rgba(34, 164, 132, .1); }
    .sr-filter-status.is-dirty { color: #9a6716; }
    .sr-filter-status.is-dirty > i { background: #e2a32e; box-shadow: 0 0 0 4px rgba(226, 163, 46, .12); }
    .sr-filter-status.is-loading { color: #3569a8; }
    .sr-filter-status.is-loading > i { background: #3979c8; animation: srStatusPulse 1s ease-in-out infinite alternate; }
    @keyframes srStatusPulse { to { opacity: .35; } }
    .sr-filter-body { padding: 17px 19px 16px; }
    .sr-period-presets { margin-bottom: 14px; }
    .sr-period-presets > span, .sr-period-presets button, .sr-field > span:first-child { font-size: 9px; }
    .sr-period-presets button { min-height: 31px; padding: 6px 11px; }
    .sr-field-control .form-control, .sr-field-control .form-select { min-height: 42px; font-size: 11px; }
    .sr-apply-button { min-height: 42px; font-size: 10px; }
    .sr-active-filter { margin-top: 14px; font-size: 9px; }

    #srSummarySection, #srInsightsSection, #srTablePanel { scroll-margin-top: 82px; }
    .sr-results-intro { margin-top: 24px; }
    .sr-results-intro > p {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 6px 10px;
        border-radius: 999px;
        color: #526781;
        background: #edf2f8;
        font-weight: 700;
    }
    .sr-metric-card { min-height: 148px; padding: 20px; border-radius: 17px; }
    .sr-metric-card::before { content: ""; position: absolute; left: 20px; right: 20px; bottom: 0; height: 3px; border-radius: 3px 3px 0 0; background: var(--metric-tone); opacity: .72; }
    .sr-metric-card small { font-size: 9px; }
    .sr-metric-card strong { font-size: clamp(20px, 2vw, 26px); }
    .sr-metric-card p { font-size: 9.5px; }

    .sr-insight-layout { margin-top: 15px; }
    .sr-chart-panel { min-height: 410px; padding: 22px; }
    .sr-chart-shell { margin-top: 20px; }
    .sr-chart-axis-label, .sr-chart-legend { font-size: 9px; }
    .sr-insight-card h3 { font-size: 18px; }
    .sr-insight-card > p { font-size: 10.5px; }
    .sr-health-card p { font-size: 9px; }

    .sr-table-panel { margin-top: 18px; border-radius: 18px; }
    .sr-table-toolbar { padding: 17px 20px; }
    .sr-table-actions { flex-wrap: nowrap; }
    .sr-table-search input { width: 230px; height: 38px; font-size: 10px; }
    .sr-page-size { font-size: 9px; }
    .sr-export-actions { display: inline-flex; gap: 7px; }
    .sr-table-actions .btn { min-height: 37px; padding-inline: 10px; font-size: 9px; }
    .sr-table-guide { display: flex; align-items: center; gap: 7px; padding: 9px 20px; border-bottom: 1px solid #e7ecf2; color: #687890; background: #f9fbfd; font-size: 10.5px; }
    .sr-table-guide i { color: #4e78aa; font-size: 14px; }
    .sr-table-wrap { min-height: 300px; max-height: 620px; }
    .sr-table thead th { padding: 14px 15px; font-size: 10px; line-height: 1.4; }
    .sr-table thead th .mdi { font-size: 15px; }
    .sr-table tbody td { padding: 14px 15px; font-size: 12px; line-height: 1.55; }
    .sr-table .sr-cell-primary { font-size: 12px; line-height: 1.5; }
    .sr-table .sr-badge { padding: 5px 9px; font-size: 10px; line-height: 1.35; }
    .sr-table :is(th, td):first-child { position: sticky; left: 0; z-index: 1; background: #fff; box-shadow: 8px 0 14px -14px rgba(27, 45, 73, .7); }
    .sr-table thead th:first-child { z-index: 4; background: #f7f9fc; }
    .sr-table tbody tr:hover td { background: #fafcff; }
    .sr-pagination { font-size: 10.5px; }

    .sales-report-page.is-loading .sr-filter-status:not(.is-dirty) { color: #3569a8; }

    @media (max-width: 1199px) {
        .sr-hero { grid-template-columns: 1fr; }
        .sr-hero-context { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .sr-switcher-tools > p { display: none; }
        .sr-table-actions { flex-wrap: wrap; }
    }

    @media (max-width: 767px) {
        .sales-report-page { padding-bottom: 28px; }
        .sr-hero, .sr-report-switcher, .sr-filter-card, .sr-panel { max-width: 100%; }
        .sr-breadcrumb .breadcrumb-item:not(:last-child) { display: none; }
        .sr-breadcrumb .breadcrumb-item:last-child::before { display: none; }
        .sr-hero { padding: 22px 18px; border-radius: 18px; }
        .sr-hero-main { flex-direction: row; gap: 14px; }
        .sr-hero-mark { width: 48px; height: 48px; flex-basis: 48px; border-radius: 14px; }
        .sr-hero-mark i { font-size: 25px; }
        .sr-hero-copy h1 { font-size: 25px; }
        .sr-hero-copy > p { font-size: 12px; }
        .sr-hero-actions { margin-left: -62px; margin-top: 17px; }
        .sr-hero-pills { margin-left: -62px; }
        .sr-hero-context { grid-template-columns: 1fr; }
        .sr-report-switcher { padding: 16px 15px 13px; }
        .sr-switcher-intro { flex-direction: row; align-items: flex-end; }
        .sr-switcher-intro h2 { font-size: 16px; }
        .sr-rail-controls { display: none; }
        .sr-report-link { flex-basis: 150px; }
        .sr-report-link.is-active { flex-basis: 164px; }
        .sr-filter-head, .sr-filter-body { padding-left: 15px; padding-right: 15px; }
        .sr-filter-status { display: none; }
        .sr-period-presets { flex-wrap: nowrap; margin-right: -15px; padding-right: 15px; overflow-x: auto; scrollbar-width: none; }
        .sr-period-presets::-webkit-scrollbar { display: none; }
        .sr-period-presets > * { flex: 0 0 auto; }
        .sr-filter-grid, .sr-field, .sr-field-control { min-width: 0; max-width: 100%; }
        .sr-field-control .form-control, .sr-field-control .form-select, .sr-apply-button { width: 100%; }
        .sr-results-intro > p { padding: 0; background: transparent; }
        .sr-metric-card { min-height: 130px; }
        .sr-table-toolbar { padding: 15px; }
        .sr-table-actions { display: grid; grid-template-columns: 1fr auto; width: 100%; }
        .sr-table-search { grid-column: 1 / -1; }
        .sr-page-size { grid-column: 1; }
        .sr-export-actions { grid-column: 1 / -1; }
        .sr-export-actions .btn { flex: 1; justify-content: center; }
        .sr-table-actions > .sr-icon-button { grid-column: 2; grid-row: 2; }
        .sr-table-guide { align-items: flex-start; padding-inline: 15px; line-height: 1.55; }
    }

    /* Interactive data-table workspace shared by sales, purchase, and inventory reports. */
    .sr-table-panel {
        position: relative;
        border: 1px solid #dfe7f1;
        background: #fff;
        box-shadow: 0 18px 46px rgba(31, 50, 79, .09), 0 2px 7px rgba(31, 50, 79, .04);
    }
    .sr-table-toolbar {
        position: relative;
        z-index: 8;
        background: linear-gradient(135deg, #fff 0%, #fbfdff 62%, #f6f9fd 100%);
    }
    .sr-table-toolbar::after {
        content: "";
        position: absolute;
        left: 20px;
        right: 20px;
        bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, #dfe7f1 12%, #dfe7f1 88%, transparent);
    }
    .sr-view-actions, .sr-export-actions {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }
    .sr-view-actions {
        padding-left: 7px;
        border-left: 1px solid #e4e9f0;
    }
    .sr-view-actions .sr-icon-button {
        width: 37px;
        height: 37px;
        border-color: #dfe6ef;
        color: #596c84;
        background: #fff;
        box-shadow: 0 3px 9px rgba(37, 56, 84, .035);
    }
    .sr-view-actions .sr-icon-button:hover,
    .sr-view-actions .sr-icon-button[aria-pressed="true"],
    .sr-column-control.is-open > .sr-icon-button {
        border-color: #9fb9d9;
        color: #245f9f;
        background: #f0f6fd;
        box-shadow: 0 4px 13px rgba(46, 104, 171, .11);
    }
    .sr-column-control { position: relative; }
    .sr-column-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        z-index: 20;
        width: min(310px, calc(100vw - 36px));
        overflow: hidden;
        border: 1px solid #dce5ef;
        border-radius: 15px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 20px 48px rgba(24, 47, 77, .2), 0 3px 10px rgba(24, 47, 77, .07);
        transform-origin: top right;
        animation: srMenuReveal .16s ease-out;
    }
    @keyframes srMenuReveal {
        from { opacity: 0; transform: translateY(-5px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .sr-column-menu-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 15px 12px;
        border-bottom: 1px solid #e7ecf2;
        background: linear-gradient(135deg, #f8fbff, #fff);
    }
    .sr-column-menu-head small,
    .sr-column-menu-head strong { display: block; }
    .sr-column-menu-head small {
        margin-bottom: 2px;
        color: #8b98aa;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .sr-column-menu-head strong { color: #263a55; font-size: 12px; }
    .sr-column-menu-head button {
        padding: 0;
        border: 0;
        color: #326ba9;
        background: transparent;
        font-size: 9px;
        font-weight: 800;
    }
    .sr-column-menu-list {
        max-height: 300px;
        overflow-y: auto;
        padding: 8px;
        scrollbar-color: #b8c7d9 transparent;
        scrollbar-width: thin;
    }
    .sr-column-option {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        margin: 0;
        padding: 7px 9px;
        border-radius: 9px;
        color: #4a5d75;
        font-size: 10.5px;
        font-weight: 700;
        cursor: pointer;
        transition: background .14s ease, color .14s ease;
    }
    .sr-column-option:hover { color: #265f9d; background: #f2f7fc; }
    .sr-column-option input {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        margin: 0;
        accent-color: #2f70b8;
    }
    .sr-column-option input:disabled { opacity: .55; cursor: not-allowed; }
    .sr-column-option span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .sr-table-guide {
        position: relative;
        z-index: 5;
        justify-content: space-between;
        min-height: 38px;
        background: linear-gradient(90deg, #f8fbfe, #fbfcfe);
    }
    .sr-table-guide > span { display: inline-flex; align-items: center; gap: 7px; }
    .sr-table-guide > strong {
        flex: 0 0 auto;
        color: #526b88;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .025em;
    }
    .sr-table-viewport { position: relative; min-height: 300px; overflow: hidden; background: #fff; }
    .sr-table-viewport::before,
    .sr-table-viewport::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 10px;
        z-index: 6;
        width: 22px;
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
    }
    .sr-table-viewport::before { left: 0; background: linear-gradient(90deg, rgba(31, 53, 82, .14), transparent); }
    .sr-table-viewport::after { right: 0; background: linear-gradient(270deg, rgba(31, 53, 82, .14), transparent); }
    .sr-table-viewport.is-scrolled::before,
    .sr-table-viewport.can-scroll-right::after { opacity: 1; }
    .sr-table-wrap {
        min-height: 300px;
        max-height: 620px;
        scrollbar-color: #b8c8da #f1f4f8;
        scrollbar-width: thin;
        overscroll-behavior: contain;
    }
    .sr-table-wrap::-webkit-scrollbar { width: 10px; height: 10px; }
    .sr-table-wrap::-webkit-scrollbar-track { background: #f1f4f8; }
    .sr-table-wrap::-webkit-scrollbar-thumb { border: 2px solid #f1f4f8; border-radius: 999px; background: #b8c8da; }
    .sr-table-wrap::-webkit-scrollbar-thumb:hover { background: #90a7c0; }
    .sr-table {
        border-collapse: separate;
        border-spacing: 0;
        transition: min-width .18s ease;
    }
    .sr-table thead th {
        border-right: 1px solid rgba(226, 232, 240, .8);
        background: linear-gradient(180deg, #fafcff 0%, #f4f7fb 100%);
        box-shadow: inset 0 -1px 0 #dfe6ee;
    }
    .sr-table thead th:last-child { border-right: 0; }
    .sr-table thead th button {
        width: 100%;
        justify-content: space-between;
        border-radius: 6px;
        outline: 0;
    }
    .sr-table thead th button:focus-visible {
        box-shadow: 0 0 0 3px rgba(48, 112, 183, .13);
    }
    .sr-table thead th.is-sorted {
        color: #245e9d;
        background: linear-gradient(180deg, #f1f7fe 0%, #eaf2fb 100%);
        box-shadow: inset 0 -2px 0 #3b7dc3;
    }
    .sr-table tbody tr[data-row-index] { cursor: pointer; }
    .sr-table tbody tr:nth-child(even) td { background: #fbfcfe; }
    .sr-table tbody tr:hover td { background: #f3f8fe; }
    .sr-table tbody tr.is-selected td {
        color: #263e5e;
        background: #eaf3fd;
        box-shadow: inset 0 1px 0 rgba(72, 128, 188, .08), inset 0 -1px 0 rgba(72, 128, 188, .08);
    }
    .sr-table :is(th, td).is-leading-column {
        position: sticky;
        left: 0;
        z-index: 2;
        box-shadow: 8px 0 14px -14px rgba(27, 45, 73, .7);
    }
    .sr-table thead th.is-leading-column { z-index: 5; background: linear-gradient(180deg, #fafcff 0%, #f4f7fb 100%); }
    .sr-table thead th.is-leading-column.is-sorted { background: linear-gradient(180deg, #f1f7fe 0%, #eaf2fb 100%); }
    .sr-table tbody td.is-leading-column { background: #fff; }
    .sr-table tbody tr:nth-child(even) td.is-leading-column { background: #fbfcfe; }
    .sr-table tbody tr:hover td.is-leading-column { background: #f3f8fe; }
    .sr-table tbody tr.is-selected td.is-leading-column { background: #eaf3fd; }
    .sr-table tbody tr.is-selected td:first-child,
    .sr-table tbody tr.is-selected td.is-leading-column { box-shadow: inset 3px 0 0 #3478bd, 8px 0 14px -14px rgba(27, 45, 73, .7); }
    .sr-table tbody tr:focus-visible { outline: 3px solid rgba(52, 120, 189, .24); outline-offset: -3px; }
    .sr-table td.is-column-hidden,
    .sr-table th.is-column-hidden { display: none; }
    .sr-table.is-compact tbody td { padding-top: 8px; padding-bottom: 8px; font-size: 10.5px; }
    .sr-table.is-compact thead th { padding-top: 10px; padding-bottom: 10px; font-size: 9px; }
    .sr-table.is-compact .sr-badge { padding: 3px 7px; font-size: 9px; }

    body.sr-table-focus-active { overflow: hidden; }
    .sr-table-panel.is-focus-mode {
        position: fixed;
        inset: 14px;
        z-index: 1060;
        display: flex;
        flex-direction: column;
        width: auto;
        height: auto;
        margin: 0;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 28px 90px rgba(13, 29, 51, .36), 0 0 0 9999px rgba(18, 32, 51, .52);
        animation: srFocusReveal .2s ease-out;
    }
    @keyframes srFocusReveal {
        from { opacity: 0; transform: scale(.985); }
        to { opacity: 1; transform: scale(1); }
    }
    .sr-table-panel.is-focus-mode .sr-table-viewport { flex: 1 1 auto; min-height: 0; }
    .sr-table-panel.is-focus-mode .sr-table-wrap { height: 100%; max-height: none; }
    .sr-table-panel.is-focus-mode .sr-table-toolbar { flex: 0 0 auto; }
    .sr-table-panel.is-focus-mode .sr-pagination { flex: 0 0 auto; }

    .sr-pagination-navigation { display: flex; align-items: center; gap: 6px; }
    .sr-page-numbers { display: inline-flex; align-items: center; gap: 5px; }
    .sr-pagination .sr-page-number {
        width: 32px;
        height: 32px;
        font-size: 10px;
        font-weight: 800;
    }
    .sr-pagination .sr-page-number.is-active {
        border-color: #2f6fae;
        color: #fff;
        background: linear-gradient(135deg, #2f70b6, #285f9c);
        box-shadow: 0 5px 12px rgba(45, 105, 167, .2);
    }
    .sr-page-ellipsis { width: 20px; color: #9ba6b4; text-align: center; }
    .sr-page-label {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
    }

    @media (max-width: 991px) {
        .sr-table-actions { align-items: center; }
        .sr-view-actions { margin-left: auto; }
    }

    @media (max-width: 767px) {
        .sr-table-actions { display: grid; grid-template-columns: 1fr; }
        .sr-table-search, .sr-page-size, .sr-export-actions, .sr-view-actions { grid-column: 1 / -1; }
        .sr-page-size { justify-self: start; }
        .sr-export-actions .btn { flex: 1 1 0; justify-content: center; }
        .sr-view-actions { width: 100%; margin-left: 0; padding: 8px 0 0; border-top: 1px solid #e7ecf2; border-left: 0; justify-content: flex-end; }
        .sr-column-control { position: static; }
        .sr-column-menu { left: 15px; right: 15px; top: auto; width: auto; }
        .sr-table-guide > span { max-width: 75%; }
        .sr-table-panel.is-focus-mode { inset: 6px; border-radius: 15px; }
        .sr-table-panel.is-focus-mode .sr-table-toolbar { max-height: 42vh; overflow-y: auto; }
        .sr-page-numbers { display: none; }
        .sr-page-label { position: static; width: auto; height: auto; overflow: visible; clip: auto; min-width: 82px; text-align: center; white-space: nowrap; }
    }

    @media (prefers-reduced-motion: reduce) {
        .sales-report-page, .sr-report-nav { scroll-behavior: auto; }
        .sales-report-page *, .sales-report-page *::before, .sales-report-page *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }

    @media print {
        .sr-hero-actions, .sr-rail-controls, .sr-table-guide, .sr-view-actions, .sr-column-menu { display: none !important; }
        .sr-table-panel.is-focus-mode { position: static; display: block; box-shadow: none; }
        .sr-table :is(th, td):first-child, .sr-table :is(th, td).is-leading-column { position: static; box-shadow: none; }
    }
</style>
