<style>
    :root {
        --pa-ink: #172b3a; --pa-muted: #6c7f8b; --pa-line: #e2eaee; --pa-bg: #f3f7f8;
        --pa-navy: #153f5b; --pa-blue: #3277e6; --pa-teal: #0e9384; --pa-green: #26a269;
        --pa-violet: #7656d6; --pa-amber: #d28b17; --pa-red: #d24f55; --pa-cyan: #178aa4;
        --pa-shadow: 0 12px 34px rgba(25, 55, 71, .075); --pa-radius: 18px;
    }
    .pa-page { color: var(--pa-ink); padding-bottom: 42px; }
    .pa-breadcrumb { margin-bottom: 14px; }
    .pa-breadcrumb a { color: #617783; }
    .pa-hero { position: relative; overflow: hidden; display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 30px; align-items: center; min-height: 230px; padding: 35px 38px; border-radius: 24px; color: #fff; background: linear-gradient(125deg, #103d58 0%, #126b72 54%, #15937f 100%); box-shadow: 0 22px 52px rgba(16, 77, 88, .24); }
    .pa-hero:after { content: ""; position: absolute; inset: auto 0 0; height: 4px; background: linear-gradient(90deg, #53d6cb, #8fd7ff, #b3efc6); opacity: .8; }
    .pa-hero-orb { position: absolute; border-radius: 50%; pointer-events: none; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.08); }
    .pa-orb-one { width: 260px; height: 260px; right: 220px; top: -155px; }
    .pa-orb-two { width: 160px; height: 160px; left: 50%; bottom: -110px; }
    .pa-hero-copy, .pa-hero-context { position: relative; z-index: 1; }
    .pa-brandline { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .pa-brandmark { display: grid; place-items: center; width: 39px; height: 39px; border-radius: 12px; font-size: 21px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.17); }
    .pa-eyebrow { font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #baf4ea; }
    .pa-live { display: inline-flex; align-items: center; gap: 6px; margin-left: 2px; padding: 4px 9px; border-radius: 999px; font-size: 10px; font-weight: 700; background: rgba(255,255,255,.11); }
    .pa-live i { width: 6px; height: 6px; border-radius: 50%; background: #79f0aa; box-shadow: 0 0 0 4px rgba(121,240,170,.13); }
    .pa-hero h1 { margin: 0 0 8px; font-size: clamp(29px, 3vw, 42px); font-weight: 800; letter-spacing: -.035em; }
    .pa-hero-copy > p { max-width: 760px; margin: 0; color: rgba(255,255,255,.82); line-height: 1.65; font-size: 14px; }
    .pa-hero-points { display: flex; flex-wrap: wrap; gap: 9px 18px; margin-top: 18px; color: rgba(255,255,255,.84); font-size: 11px; font-weight: 650; }
    .pa-hero-points span { display: inline-flex; gap: 7px; align-items: center; }
    .pa-hero-context { display: grid; gap: 9px; }
    .pa-hero-context > div { display: grid; grid-template-columns: 38px 1fr; column-gap: 10px; padding: 11px 13px; border-radius: 14px; background: rgba(9,44,57,.28); border: 1px solid rgba(255,255,255,.12); backdrop-filter: blur(5px); }
    .pa-hero-context > div > span { grid-row: span 2; display: grid; place-items: center; width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,.11); font-size: 18px; }
    .pa-hero-context small { font-size: 9px; text-transform: uppercase; letter-spacing: .1em; color: #add9da; }
    .pa-hero-context strong { font-size: 12px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .pa-hero-context button, .pa-hero-context > a { display: flex; align-items: center; justify-content: center; gap: 7px; border: 0; padding: 10px 14px; border-radius: 11px; color: #174b54; background: #e7fffa; font-weight: 750; font-size: 12px; transition: .2s; }
    .pa-hero-context button:hover, .pa-hero-context > a:hover { color: #174b54; text-decoration: none; transform: translateY(-1px); background: #fff; }

    .pa-tabs { position: sticky; top: 68px; z-index: 20; display: grid; grid-template-columns: repeat(5, 1fr); gap: 5px; margin: 18px 0; padding: 6px; border-radius: 16px; background: rgba(255,255,255,.96); border: 1px solid var(--pa-line); box-shadow: 0 8px 26px rgba(27,54,68,.08); backdrop-filter: blur(8px); }
    .pa-tabs button { display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 43px; border: 0; border-radius: 11px; color: #687d89; background: transparent; font-size: 12px; font-weight: 720; transition: .2s; }
    .pa-tabs button i { font-size: 17px; }
    .pa-tabs button:hover { color: var(--pa-navy); background: #f2f7f8; }
    .pa-tabs button.is-active { color: #fff; background: linear-gradient(125deg, #174c67, #138678); box-shadow: 0 7px 18px rgba(18,111,107,.2); }

    .pa-panel { margin-bottom: 18px; border: 1px solid var(--pa-line); border-radius: var(--pa-radius); background: #fff; box-shadow: var(--pa-shadow); }
    .pa-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 19px 21px 14px; }
    .pa-heading { display: flex; align-items: center; gap: 11px; min-width: 0; }
    .pa-heading > span { display: grid; place-items: center; flex: 0 0 auto; width: 39px; height: 39px; border-radius: 12px; font-size: 19px; color: var(--pa-blue); background: #eaf2ff; }
    .pa-heading > span.is-navy { color: var(--pa-navy); background: #e9f1f4; } .pa-heading > span.is-teal { color: var(--pa-teal); background: #e6f7f4; }
    .pa-heading > span.is-green { color: var(--pa-green); background: #e9f8f0; } .pa-heading > span.is-violet { color: var(--pa-violet); background: #f0ecfc; }
    .pa-heading > span.is-amber, .pa-heading > span.is-orange { color: var(--pa-amber); background: #fff5e3; } .pa-heading > span.is-cyan { color: var(--pa-cyan); background: #e8f6f9; }
    .pa-heading > span.is-red, .pa-heading > span.is-rose { color: var(--pa-red); background: #fcecee; }
    .pa-heading small { display: block; margin-bottom: 1px; color: var(--pa-teal); font-size: 9px; font-weight: 800; letter-spacing: .11em; text-transform: uppercase; }
    .pa-heading h2 { margin: 0; font-size: 15px; font-weight: 790; letter-spacing: -.01em; }
    .pa-heading p { margin: 3px 0 0; color: var(--pa-muted); font-size: 10.5px; }
    .pa-filter-panel { position: relative; overflow: hidden; }
    .pa-filter-panel:before { content: ""; position: absolute; inset: 0 0 auto; height: 3px; background: linear-gradient(90deg, var(--pa-blue), var(--pa-teal), #62c69b); }
    .pa-filter-head { padding: 20px 22px 15px; }
    .pa-filter-head-actions { display: flex; align-items: center; gap: 8px; }
    .pa-filter-status { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; border-radius: 9px; color: #19795e; background: #eaf8f2; font-size: 10px; font-weight: 700; }
    .pa-filter-status.is-loading { color: #8b671e; background: #fff5dc; }
    .pa-filter-status.is-error { color: #a33f45; background: #fdebed; }
    .pa-icon-btn { display: grid; place-items: center; width: 34px; height: 34px; border: 1px solid var(--pa-line); border-radius: 9px; color: #617984; background: #fff; }
    .pa-filter-body { padding: 0 22px 20px; overflow: hidden; transition: .25s; }
    .pa-filter-panel.is-collapsed .pa-filter-body { display: none; }
    .pa-presets { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; padding: 11px; margin-bottom: 14px; border-radius: 12px; background: #f5f8f9; border: 1px solid #e8eef0; }
    .pa-presets > span { display: inline-flex; align-items: center; gap: 6px; margin-right: 4px; color: #59717e; font-size: 10px; }
    .pa-presets button { border: 1px solid #dce6e9; border-radius: 8px; padding: 6px 10px; color: #617683; background: #fff; font-size: 10px; font-weight: 680; }
    .pa-presets button:hover, .pa-presets button.is-active { color: #fff; border-color: #167c76; background: #167c76; }
    .pa-filter-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 11px; }
    .pa-filter-grid label { display: block; margin: 0; }
    .pa-filter-grid label > span { display: block; margin: 0 0 6px; color: #526a77; font-size: 10px; font-weight: 720; }
    .pa-filter-grid label > span i { color: #218f82; font-size: 13px; margin-right: 3px; }
    .pa-filter-grid select, .pa-filter-grid input { width: 100%; height: 38px; padding: 0 10px; border: 1px solid #dce5e8; border-radius: 9px; color: #263f4d; outline: 0; background: #fbfcfd; font-size: 10.5px; transition: .18s; }
    .pa-filter-grid select:focus, .pa-filter-grid input:focus { border-color: #3d9d93; box-shadow: 0 0 0 3px rgba(61,157,147,.11); background: #fff; }
    .pa-filter-footer { display: flex; justify-content: space-between; align-items: center; gap: 14px; padding-top: 15px; margin-top: 15px; border-top: 1px dashed #dce5e8; }
    .pa-active-filters { display: flex; align-items: center; gap: 7px; color: #78909b; font-size: 10px; min-width: 0; }
    .pa-active-filters span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pa-btn { border: 0; border-radius: 10px; min-height: 37px; padding: 0 14px; font-size: 10.5px; font-weight: 750; }
    .pa-btn + .pa-btn { margin-left: 7px; }
    .pa-btn-primary { color: #fff; background: linear-gradient(125deg, #1b6381, #168d7d); box-shadow: 0 7px 16px rgba(18,111,107,.17); }
    .pa-btn-ghost { color: #61747e; background: #eef3f5; }

    .pa-tab-panel { display: none; animation: paFade .22s ease; } .pa-tab-panel.is-active { display: block; }
    @keyframes paFade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }
    .pa-section-label { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; margin: 24px 2px 13px; }
    .pa-section-label span { color: var(--pa-teal); font-size: 9px; text-transform: uppercase; font-weight: 800; letter-spacing: .12em; }
    .pa-section-label h2 { margin: 2px 0 0; font-size: 20px; font-weight: 800; letter-spacing: -.025em; }
    .pa-section-label > p { margin: 0; color: #7a8c95; font-size: 10px; }
    .pa-kpi-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
    .pa-kpi { position: relative; overflow: hidden; display: flex; gap: 11px; align-items: flex-start; min-height: 128px; padding: 17px 15px; border: 1px solid var(--pa-line); border-radius: 16px; background: #fff; box-shadow: var(--pa-shadow); }
    .pa-kpi:after { content: var(--pa-index); position: absolute; right: 8px; top: 2px; font-size: 39px; line-height: 1; font-weight: 850; color: #f0f4f5; }
    .pa-kpi > span { position: relative; z-index: 1; display: grid; place-items: center; flex: 0 0 auto; width: 38px; height: 38px; border-radius: 11px; color: var(--pa-blue); background: #eaf2ff; font-size: 18px; }
    .pa-kpi.tone-green > span { color: var(--pa-green); background: #e7f7ef; } .pa-kpi.tone-violet > span { color: var(--pa-violet); background: #efebfb; }
    .pa-kpi.tone-red > span { color: var(--pa-red); background: #fcebec; } .pa-kpi.tone-amber > span { color: var(--pa-amber); background: #fff4df; }
    .pa-kpi.tone-teal > span { color: var(--pa-teal); background: #e6f7f4; }
    .pa-kpi > div { position: relative; z-index: 1; min-width: 0; }
    .pa-kpi small { display: block; color: #72858f; font-size: 9px; font-weight: 750; text-transform: uppercase; letter-spacing: .055em; }
    .pa-kpi strong { display: block; margin: 5px 0 7px; font-size: 17px; line-height: 1.15; font-weight: 820; letter-spacing: -.025em; white-space: nowrap; }
    .pa-kpi p { margin: 0; font-size: 9px; color: #82939b; }
    .pa-change { display: inline-flex; align-items: center; gap: 2px; margin-right: 3px; font-weight: 780; color: #1f9464; }
    .pa-change.is-down { color: #cb5058; }
    .pa-kpi.is-loading { min-height: 128px; }
    .pa-kpi.is-loading > span, .pa-kpi.is-loading small, .pa-kpi.is-loading strong, .pa-kpi.is-loading p { color: transparent; background: linear-gradient(90deg,#edf1f3,#f7f9fa,#edf1f3); background-size: 200% 100%; animation: paShimmer 1.2s infinite; border-radius: 6px; }
    @keyframes paShimmer { to { background-position: -200% 0; } }

    .pa-grid { display: grid; gap: 18px; } .pa-grid > .pa-panel { min-width: 0; }
    .pa-grid-wide { grid-template-columns: 1.25fr .75fr; } .pa-grid-four { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .pa-grid-two { grid-template-columns: repeat(2, minmax(0,1fr)); } .pa-grid-three { grid-template-columns: repeat(3,minmax(0,1fr)); }
    .pa-chart { min-height: 265px; padding: 0 10px 10px; } .pa-chart-lg { min-height: 335px; }
    .pa-loading, .pa-empty { display: flex; align-items: center; justify-content: center; gap: 7px; min-height: 220px; color: #8797a0; font-size: 11px; }
    .pa-legend { display: flex; gap: 10px; color: #778a94; font-size: 9px; }
    .pa-legend span { display: flex; align-items: center; gap: 5px; } .pa-legend i { width: 18px; height: 3px; border-radius: 5px; background: var(--pa-blue); }
    .pa-legend i.previous { background: #a7b3ba; }
    .pa-split { display: grid; grid-template-columns: 1fr 170px; align-items: center; padding-right: 16px; }
    .pa-mini-list { display: grid; gap: 7px; }
    .pa-mini-list > div { display: grid; grid-template-columns: 8px 1fr auto; align-items: center; gap: 7px; font-size: 9px; color: #687d88; }
    .pa-mini-list i { width: 7px; height: 7px; border-radius: 50%; } .pa-mini-list b { color: #263e4a; }
    .pa-insights { overflow: hidden; }
    .pa-insight-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 10px; padding: 0 20px 20px; }
    .pa-insight { display: grid; grid-template-columns: 34px 1fr; gap: 9px; padding: 13px; border-radius: 13px; background: #f4f8fa; border: 1px solid #e7eef1; }
    .pa-insight > i { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 9px; color: var(--pa-blue); background: #e6f0ff; font-size: 17px; }
    .pa-insight.tone-danger > i { color: var(--pa-red); background: #fde9eb; } .pa-insight.tone-warning > i { color: var(--pa-amber); background: #fff1d8; }
    .pa-insight.tone-success > i { color: var(--pa-green); background: #e6f6ee; }
    .pa-insight b { display: block; margin-bottom: 3px; font-size: 10.5px; } .pa-insight p { margin: 0; color: #71848e; line-height: 1.45; font-size: 9px; }

    .pa-ranking-grid .pa-panel { overflow: hidden; }
    .pa-rank-list { padding: 0 18px 16px; }
    .pa-rank { display: grid; grid-template-columns: 27px minmax(0,1fr) auto; align-items: center; gap: 9px; padding: 10px 0; border-top: 1px solid #edf1f3; }
    .pa-rank:first-child { border-top: 0; }
    .pa-rank > span { display: grid; place-items: center; width: 25px; height: 25px; border-radius: 8px; color: #56717e; background: #eef3f5; font-size: 10px; font-weight: 800; }
    .pa-rank:nth-child(1) > span { color: #8c6718; background: #fff1c8; } .pa-rank:nth-child(2) > span { color: #58707d; background: #e7edef; } .pa-rank:nth-child(3) > span { color: #9b5f30; background: #f7e5d6; }
    .pa-medicine-link { border: 0; padding: 0; color: #1d536e; background: transparent; font-size: inherit; font-weight: 760; text-align: left; cursor: pointer; }
    .pa-medicine-link:hover { color: var(--pa-teal); text-decoration: underline; }
    .pa-rank small { display: block; margin-top: 2px; color: #8b9aa2; font-size: 8.5px; }
    .pa-rank b { font-size: 10px; white-space: nowrap; }
    .pa-soft-badge { padding: 5px 9px; border-radius: 999px; color: #55717e; background: #edf3f5; font-size: 9px; font-weight: 750; }
    .pa-table { margin: 0; font-size: 10px; }
    .pa-table thead th { padding: 10px 13px; border-top: 1px solid #edf1f3; border-bottom: 1px solid #dfe7ea; color: #6b7e88; background: #f6f9fa; font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .pa-table tbody td { padding: 11px 13px; vertical-align: middle; border-color: #edf1f3; color: #4a616c; }
    .pa-table tbody tr:hover { background: #f9fbfc; }
    .pa-table .pa-cell-main { display: block; color: #203c4b; font-weight: 740; } .pa-table .pa-cell-sub { display: block; margin-top: 2px; color: #8a9aa2; font-size: 8.5px; }
    .pa-table-compact tbody td { padding-top: 9px; padding-bottom: 9px; }
    .pa-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 7px; border-radius: 999px; color: #55707d; background: #eef3f5; font-size: 8.5px; font-weight: 760; white-space: nowrap; }
    .pa-badge.is-fast, .pa-badge.is-optimal { color: #16775a; background: #e4f6ed; }
    .pa-badge.is-slow, .pa-badge.is-evaluation { color: #91661a; background: #fff1d4; }
    .pa-badge.is-non_moving, .pa-badge.is-over, .pa-badge.is-under, .pa-badge.is-rejected { color: #ad4149; background: #fce8ea; }
    .pa-progress { display: inline-flex; width: 70px; height: 5px; margin-right: 6px; overflow: hidden; border-radius: 9px; background: #e7edef; vertical-align: middle; }
    .pa-progress i { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg,#2b84db,#18a57f); }
    .pa-delta { font-weight: 780; color: #1d8a61; } .pa-delta.is-up { color: #cb4e57; }
    .pa-moving-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 12px; padding: 0 20px 20px; }
    .pa-moving-card { position: relative; overflow: hidden; padding: 17px; border: 1px solid #e4ebee; border-radius: 14px; background: #f8fafb; }
    .pa-moving-card:after { content: ""; position: absolute; inset: 0 auto 0 0; width: 4px; background: var(--pa-green); }
    .pa-moving-card.is-slow:after { background: var(--pa-amber); } .pa-moving-card.is-non_moving:after { background: var(--pa-red); }
    .pa-moving-card small { color: #70838d; font-size: 9px; text-transform: uppercase; font-weight: 780; }
    .pa-moving-card strong { display: block; margin: 5px 0; font-size: 19px; } .pa-moving-card p { margin: 0; color: #7e9099; font-size: 9px; }
    .pa-need-legend { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; margin: -38px 2px 13px auto; }
    .pa-need-legend span { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; border-radius: 999px; color: #5d737e; background: #fff; border: 1px solid var(--pa-line); font-size: 8.5px; font-weight: 700; }
    .pa-need-legend i { width: 7px; height: 7px; border-radius: 50%; background: var(--pa-green); }
    .pa-need-legend .is-evaluation i { background: var(--pa-amber); } .pa-need-legend .is-over i, .pa-need-legend .is-under i { background: var(--pa-red); }

    .pa-toast { position: fixed; z-index: 90; right: 24px; bottom: 24px; display: flex; align-items: center; gap: 8px; max-width: 380px; padding: 12px 15px; border-radius: 12px; color: #fff; background: #174c5f; box-shadow: 0 15px 38px rgba(18,50,63,.28); font-size: 11px; }
    .pa-toast.is-error { background: #a63e47; }
    .pa-drawer-layer { position: fixed; z-index: 1050; inset: 0; }
    .pa-drawer-backdrop { position: absolute; inset: 0; width: 100%; border: 0; background: rgba(15,32,42,.48); backdrop-filter: blur(2px); }
    .pa-drawer { position: absolute; right: 0; top: 0; bottom: 0; width: min(690px, 94vw); overflow: hidden; color: var(--pa-ink); background: #f5f8f9; box-shadow: -20px 0 55px rgba(17,43,56,.23); animation: paDrawer .25s ease; }
    @keyframes paDrawer { from { transform: translateX(100%); } }
    .pa-drawer > header { display: flex; justify-content: space-between; gap: 20px; padding: 23px 24px; color: #fff; background: linear-gradient(125deg,#153f5b,#168274); }
    .pa-drawer > header span { color: #a9e7de; font-size: 9px; text-transform: uppercase; letter-spacing: .1em; font-weight: 800; }
    .pa-drawer > header h2 { margin: 3px 0; font-size: 21px; font-weight: 800; } .pa-drawer > header p { margin: 0; color: rgba(255,255,255,.72); font-size: 9.5px; }
    .pa-drawer > header button { display: grid; place-items: center; width: 35px; height: 35px; border: 1px solid rgba(255,255,255,.18); border-radius: 10px; color: #fff; background: rgba(255,255,255,.1); font-size: 18px; }
    .pa-drawer-body { height: calc(100% - 105px); padding: 20px; overflow-y: auto; }
    .pa-detail-kpis { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 9px; }
    .pa-detail-kpi { padding: 13px; border-radius: 12px; background: #fff; border: 1px solid var(--pa-line); }
    .pa-detail-kpi small { display: block; color: #7c8e97; font-size: 8.5px; } .pa-detail-kpi strong { display: block; margin-top: 5px; color: #203e4e; font-size: 13px; }
    .pa-detail-section { margin-top: 16px; border: 1px solid var(--pa-line); border-radius: 14px; background: #fff; overflow: hidden; }
    .pa-detail-section h3 { margin: 0; padding: 14px 16px 10px; font-size: 12px; font-weight: 800; }
    .pa-detail-chart { min-height: 230px; }
    body.pa-drawer-open { overflow: hidden; }

    @media (max-width: 1500px) { .pa-filter-grid { grid-template-columns: repeat(4,minmax(0,1fr)); } .pa-kpi-grid { grid-template-columns: repeat(3,minmax(0,1fr)); } }
    @media (max-width: 1100px) { .pa-hero { grid-template-columns: 1fr; } .pa-hero-context { grid-template-columns: repeat(2,minmax(0,1fr)); } .pa-grid-wide, .pa-grid-two { grid-template-columns: 1fr; } .pa-insight-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } }
    @media (max-width: 820px) { .pa-tabs { top: 62px; overflow-x: auto; grid-template-columns: repeat(5,minmax(145px,1fr)); justify-content: start; } .pa-filter-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } .pa-grid-three, .pa-grid-four { grid-template-columns: 1fr; } .pa-kpi-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } .pa-section-label { align-items: flex-start; flex-direction: column; gap: 4px; } .pa-need-legend { margin: 0 0 12px; justify-content: flex-start; } }
    @media (max-width: 560px) { .pa-hero { padding: 26px 22px; border-radius: 18px; } .pa-hero-context { grid-template-columns: 1fr; } .pa-hero-context button { grid-column: auto; } .pa-filter-grid, .pa-kpi-grid, .pa-insight-grid, .pa-moving-grid, .pa-detail-kpis { grid-template-columns: 1fr; } .pa-filter-footer { align-items: stretch; flex-direction: column; } .pa-filter-footer > div:last-child { display: grid; grid-template-columns: 1fr 1fr; } .pa-btn + .pa-btn { margin-left: 6px; } .pa-filter-status { display: none; } .pa-split { grid-template-columns: 1fr; } .pa-mini-list { padding: 0 18px 16px; } }
</style>
