<style>
    :root {
        --ro-ink: #132f43; --ro-muted: #6c8090; --ro-border: #dfe9ec; --ro-soft: #f4f8f9;
        --ro-teal: #0f8f83; --ro-teal-dark: #087166; --ro-navy: #102f4a; --ro-green: #16a36a;
        --ro-blue: #3478f6; --ro-violet: #7959d9; --ro-orange: #ea8b32; --ro-red: #dc4f64;
        --ro-shadow: 0 14px 38px rgba(24, 58, 76, .08);
    }
    .ro-page { color: var(--ro-ink); padding-bottom: 28px; }
    .ro-breadcrumb { margin-bottom: 14px; }
    .ro-breadcrumb .breadcrumb { margin: 0; }
    .ro-breadcrumb a { color: var(--ro-teal-dark); }
    .ro-hero { position: relative; overflow: hidden; display: flex; justify-content: space-between; gap: 28px; min-height: 250px; padding: 36px 38px; border-radius: 22px; color: #fff; background: linear-gradient(125deg, #102f4a 0%, #0e5263 54%, #0b897f 100%); box-shadow: 0 20px 54px rgba(17, 63, 78, .2); }
    .ro-hero::after { content: ''; position: absolute; inset: auto 0 0; height: 3px; background: linear-gradient(90deg, #56e3cb, #85b8ff, transparent); }
    .ro-hero-orb { position: absolute; border-radius: 999px; background: rgba(255,255,255,.08); filter: blur(1px); }
    .ro-orb-one { width: 260px; height: 260px; right: 23%; top: -150px; }
    .ro-orb-two { width: 180px; height: 180px; right: -50px; bottom: -80px; }
    .ro-hero-copy, .ro-hero-side { position: relative; z-index: 1; }
    .ro-hero-copy { max-width: 720px; }
    .ro-eyebrow { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 12px; color: #87f0dd; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .ro-hero h1 { margin: 0 0 10px; color: #fff; font-size: clamp(30px, 4vw, 48px); line-height: 1.05; letter-spacing: -.035em; }
    .ro-hero p { margin: 0; max-width: 660px; color: rgba(255,255,255,.76); font-size: 15px; line-height: 1.7; }
    .ro-hero-meta { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 24px; }
    .ro-hero-meta span { display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; border: 1px solid rgba(255,255,255,.15); border-radius: 999px; background: rgba(255,255,255,.08); color: rgba(255,255,255,.88); font-size: 11px; }
    .ro-hero-side { display: flex; width: 310px; flex: 0 0 310px; flex-direction: column; justify-content: center; gap: 10px; }
    .ro-hero-side > div:not(.ro-export-group) { padding: 12px 14px; border: 1px solid rgba(255,255,255,.14); border-radius: 13px; background: rgba(3,31,46,.2); backdrop-filter: blur(8px); }
    .ro-hero-side small { display: block; margin-bottom: 2px; color: rgba(255,255,255,.58); font-size: 10px; letter-spacing: .08em; text-transform: uppercase; }
    .ro-hero-side strong { display: block; color: #fff; font-size: 13px; }
    .ro-export-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 7px; margin-top: 3px; }
    .ro-export-group button { border: 1px solid rgba(255,255,255,.2); border-radius: 10px; padding: 9px 6px; background: rgba(255,255,255,.1); color: #fff; font-size: 11px; font-weight: 700; transition: .2s ease; }
    .ro-export-group button:hover { transform: translateY(-1px); background: rgba(255,255,255,.2); }
    .ro-panel { margin-top: 18px; border: 1px solid var(--ro-border); border-radius: 18px; background: #fff; box-shadow: var(--ro-shadow); }
    .ro-filter-panel { position: relative; z-index: 2; margin-top: -18px; }
    .ro-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 20px 22px 16px; }
    .ro-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .ro-heading > span { display: grid; width: 40px; height: 40px; flex: 0 0 40px; place-items: center; border-radius: 12px; color: var(--ro-navy); background: #eaf1f5; font-size: 20px; }
    .ro-heading > span.is-teal { color: var(--ro-teal); background: #e2f7f3; }.ro-heading > span.is-blue { color: var(--ro-blue); background: #e9f0ff; }.ro-heading > span.is-violet { color: var(--ro-violet); background: #f0ebff; }.ro-heading > span.is-indigo { color: #5369cf; background: #ebedff; }.ro-heading > span.is-green { color: var(--ro-green); background: #e6f7ef; }.ro-heading > span.is-orange { color: var(--ro-orange); background: #fff1df; }.ro-heading > span.is-rose { color: var(--ro-red); background: #feecef; }.ro-heading > span.is-cyan { color: #168fa4; background: #e4f7fa; }.ro-heading > span.is-amber { color: #d68b0f; background: #fff4d9; }
    .ro-heading h2 { margin: 0; color: var(--ro-ink); font-size: 16px; font-weight: 800; letter-spacing: -.01em; }
    .ro-heading p { margin: 3px 0 0; color: var(--ro-muted); font-size: 11px; }
    .ro-filter-toggle { display: grid; width: 36px; height: 36px; place-items: center; border: 1px solid var(--ro-border); border-radius: 10px; background: #fff; color: var(--ro-muted); }
    .ro-presets { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; padding: 0 22px 14px; }
    .ro-presets > span { margin-right: 5px; color: var(--ro-muted); font-size: 11px; font-weight: 700; }
    .ro-presets button { border: 1px solid var(--ro-border); border-radius: 999px; padding: 6px 10px; background: #fff; color: #536a7b; font-size: 11px; font-weight: 700; }
    .ro-presets button:hover, .ro-presets button.is-active { border-color: #8ddbd1; background: #eaf9f6; color: var(--ro-teal-dark); }
    .ro-filter-form { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; padding: 16px 22px; border-top: 1px solid #edf2f3; background: #fbfdfd; }
    .ro-field { display: flex; min-width: 0; flex-direction: column; gap: 6px; margin: 0; }
    .ro-field > span { color: #526a7b; font-size: 10px; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; }
    .ro-field input, .ro-field select { width: 100%; height: 40px; border: 1px solid #d9e5e8; border-radius: 10px; padding: 0 10px; outline: 0; background: #fff; color: #2e4658; font-size: 12px; }
    .ro-field input:focus, .ro-field select:focus { border-color: #54bfb3; box-shadow: 0 0 0 3px rgba(15,143,131,.1); }
    .ro-filter-actions { display: flex; align-items: end; justify-content: end; grid-column: span 2; gap: 8px; }
    .ro-btn { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 7px; border: 0; border-radius: 10px; padding: 0 15px; font-size: 12px; font-weight: 800; }
    .ro-btn-primary { color: #fff; background: linear-gradient(135deg, var(--ro-teal), var(--ro-teal-dark)); box-shadow: 0 8px 18px rgba(15,143,131,.2); }
    .ro-btn-ghost { border: 1px solid var(--ro-border); color: #5d7181; background: #fff; }
    .ro-active-filters { display: flex; align-items: center; gap: 12px; padding: 11px 22px; border-top: 1px solid #eaf0f1; }
    .ro-active-filters > span { color: var(--ro-teal-dark); font-size: 11px; font-weight: 800; white-space: nowrap; }
    .ro-active-filters > div { display: flex; flex-wrap: wrap; gap: 6px; }
    .ro-active-filters b { padding: 4px 8px; border-radius: 999px; background: var(--ro-soft); color: #607586; font-size: 10px; font-weight: 700; }
    .ro-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-top: 18px; }
    .ro-kpi { position: relative; overflow: hidden; display: flex; min-height: 138px; gap: 13px; padding: 18px; border: 1px solid var(--ro-border); border-radius: 17px; background: #fff; box-shadow: 0 10px 28px rgba(30,66,83,.07); }
    .ro-kpi::after { content: ''; position: absolute; width: 90px; height: 90px; right: -48px; top: -48px; border-radius: 50%; background: var(--kpi-soft, #e8f7f4); }
    .ro-kpi-icon { display: grid; width: 43px; height: 43px; flex: 0 0 43px; place-items: center; border-radius: 13px; color: var(--kpi-color, var(--ro-teal)); background: var(--kpi-soft, #e8f7f4); font-size: 21px; }
    .ro-kpi small { display: block; color: var(--ro-muted); font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .ro-kpi strong { display: block; margin: 7px 0 5px; color: var(--ro-ink); font-size: clamp(18px, 2vw, 24px); line-height: 1.1; letter-spacing: -.03em; }
    .ro-kpi p { margin: 0; color: #81929f; font-size: 10px; line-height: 1.45; }
    .ro-kpi.tone-blue { --kpi-color: #3478f6; --kpi-soft: #e8f0ff; }.ro-kpi.tone-violet { --kpi-color: #7959d9; --kpi-soft: #f0ebff; }.ro-kpi.tone-amber { --kpi-color: #d88a11; --kpi-soft: #fff4d9; }.ro-kpi.tone-red { --kpi-color: #dc4f64; --kpi-soft: #feecef; }.ro-kpi.tone-green { --kpi-color: #16a36a; --kpi-soft: #e6f7ef; }
    .is-loading { animation: roPulse 1.35s ease-in-out infinite; }
    @keyframes roPulse { 0%,100% { opacity: .52 } 50% { opacity: 1 } }
    .ro-trend-panel { min-height: 430px; }
    .ro-chart { min-height: 270px; padding: 0 10px 10px; }
    .ro-chart-large { min-height: 345px; }
    .ro-loading, .ro-empty { display: flex; min-height: 240px; align-items: center; justify-content: center; gap: 8px; color: #80919f; font-size: 12px; }
    .ro-chart-legend { display: flex; flex-wrap: wrap; gap: 12px; color: #6a7d8c; font-size: 10px; }
    .ro-chart-legend span { display: flex; align-items: center; gap: 5px; }.ro-chart-legend i { width: 8px; height: 8px; border-radius: 50%; }.ro-chart-legend i.current { background: var(--ro-teal); }.ro-chart-legend i.previous { background: #9cb0bd; }.ro-chart-legend i.transactions { background: #3478f6; }
    .ro-products-panel { overflow: hidden; }
    .ro-soft-badge { padding: 6px 9px; border-radius: 999px; color: var(--ro-teal-dark); background: #e7f8f4; font-size: 10px; font-weight: 800; }
    .ro-table { margin: 0; font-size: 11px; }
    .ro-table thead th { padding: 10px 13px; border-top: 1px solid #e9f0f2; border-bottom: 1px solid #e3ebee; background: #f7fafb; color: #6b7e8d; font-size: 9px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
    .ro-table td { padding: 12px 13px; border-color: #edf2f3; color: #3d5668; vertical-align: middle; }
    .ro-table tbody tr:hover td { background: #fbfefd; }
    .ro-rank { display: grid; width: 27px; height: 27px; place-items: center; border-radius: 8px; background: #edf3f5; color: #536a7b; font-weight: 900; }
    .ro-rank.is-top { color: #fff; background: linear-gradient(135deg, #0f9688, #087166); }
    .ro-product-name strong { display: block; color: var(--ro-ink); font-size: 11px; }.ro-product-name small { color: #8796a1; }
    .ro-growth { display: inline-flex; align-items: center; gap: 3px; border-radius: 999px; padding: 4px 7px; font-size: 9px; font-weight: 900; }.ro-growth.up { color: #128153; background: #e4f7ee; }.ro-growth.down { color: #c44457; background: #fdebed; }.ro-growth.flat { color: #667c8c; background: #edf2f4; }
    .ro-grid { display: grid; gap: 18px; }.ro-grid-two { grid-template-columns: repeat(2, minmax(0, 1fr)); }.ro-grid .ro-panel { min-width: 0; }
    .ro-split-chart { display: grid; grid-template-columns: minmax(240px, .9fr) 1.1fr; align-items: center; }
    .ro-mini-list, .ro-summary-list { padding: 0 18px 16px; }
    .ro-mini-row, .ro-summary-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; padding: 9px 0; border-bottom: 1px solid #edf2f3; }
    .ro-mini-row:last-child, .ro-summary-row:last-child { border: 0; }.ro-mini-row strong, .ro-summary-row strong { overflow: hidden; color: #334e61; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }.ro-mini-row small, .ro-summary-row small { display: block; color: #8495a2; font-size: 9px; }.ro-mini-row > span, .ro-summary-row > span { color: var(--ro-ink); font-size: 11px; font-weight: 800; text-align: right; }
    .ro-table-compact td { padding-top: 8px; padding-bottom: 8px; }
    .ro-cashier-table { max-height: 390px; overflow: auto; }
    .ro-bottom-grid { grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); }
    .ro-target-panel { background: linear-gradient(145deg, #fff, #f4fbf9); }
    .ro-status { border-radius: 999px; padding: 6px 9px; background: #edf2f4; color: #667b8a; font-size: 9px; font-weight: 900; text-transform: uppercase; }.ro-status.achieved { color: #107c50; background: #dff6e9; }.ro-status.near { color: #a46b08; background: #fff1cd; }.ro-status.attention { color: #bd4153; background: #fde7eb; }
    .ro-target-values { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; padding: 4px 22px 18px; }.ro-target-values div { padding: 13px; border: 1px solid #dfebe9; border-radius: 12px; background: rgba(255,255,255,.76); }.ro-target-values small { display: block; color: #7b8e9a; font-size: 9px; text-transform: uppercase; }.ro-target-values strong { display: block; margin-top: 4px; color: var(--ro-ink); font-size: 15px; }
    .ro-progress-meta { display: flex; justify-content: space-between; padding: 0 22px 7px; color: #617686; font-size: 10px; }.ro-progress-meta strong { color: var(--ro-teal-dark); }
    .ro-progress { height: 10px; margin: 0 22px 18px; overflow: hidden; border-radius: 999px; background: #deebe9; }.ro-progress span { display: block; width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #11a494, #4fd0bb); transition: width .45s ease; }
    .ro-target-form { padding: 15px 22px 20px; border-top: 1px solid #e2ecea; }.ro-target-form label { width: 100%; margin: 0; }.ro-target-form label > span { display: block; margin-bottom: 7px; color: #536b7b; font-size: 10px; font-weight: 800; text-transform: uppercase; }.ro-target-form label > div { display: flex; overflow: hidden; border: 1px solid #d7e5e3; border-radius: 11px; background: #fff; }.ro-target-form label > div > span { padding: 11px; color: #738994; background: #f1f6f5; font-size: 11px; font-weight: 800; }.ro-target-form input { min-width: 0; flex: 1; border: 0; padding: 0 11px; outline: 0; color: var(--ro-ink); }.ro-target-form .ro-btn { border-radius: 0; }.ro-target-form p { margin: 7px 0 0; color: #8596a2; font-size: 9px; }
    .ro-insight-list { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; padding: 4px 22px 22px; }.ro-insight { display: flex; gap: 10px; padding: 13px; border: 1px solid #e7edef; border-radius: 13px; background: #fbfdfd; }.ro-insight > span { display: grid; width: 34px; height: 34px; flex: 0 0 34px; place-items: center; border-radius: 10px; color: var(--ro-teal); background: #e5f7f3; font-size: 17px; }.ro-insight h3 { margin: 1px 0 4px; color: var(--ro-ink); font-size: 11px; }.ro-insight p { margin: 0; color: #748794; font-size: 9px; line-height: 1.5; }.ro-insight.tone-negative > span { color: #d1495c; background: #fee9ed; }.ro-insight.tone-blue > span { color: #3478f6; background: #e8f0ff; }.ro-insight.tone-violet > span { color: #7959d9; background: #f0ebff; }.ro-insight.tone-orange > span { color: #dc7f24; background: #fff0df; }.ro-insight.tone-teal > span { color: #0f8f83; background: #e2f7f3; }.ro-insight.tone-green > span { color: #16a36a; background: #e4f7ed; }
    .ro-toast { position: fixed; z-index: 1100; right: 24px; bottom: 24px; display: flex; align-items: center; gap: 8px; max-width: 370px; padding: 12px 15px; border-radius: 12px; color: #fff; background: #153b50; box-shadow: 0 14px 35px rgba(0,0,0,.2); font-size: 11px; }.ro-toast.is-error { background: #b84253; }
    @media (max-width: 1199px) { .ro-filter-form { grid-template-columns: repeat(4, minmax(0,1fr)); }.ro-kpi-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (max-width: 991px) { .ro-hero { flex-direction: column; }.ro-hero-side { width: 100%; flex: auto; display: grid; grid-template-columns: repeat(2,1fr); }.ro-export-group { grid-column: 1 / -1; }.ro-grid-two, .ro-bottom-grid { grid-template-columns: 1fr; }.ro-filter-form { grid-template-columns: repeat(3, minmax(0,1fr)); } }
    @media (max-width: 767px) { .ro-hero { padding: 28px 22px; border-radius: 17px; }.ro-hero-side { grid-template-columns: 1fr; }.ro-export-group { grid-column: auto; }.ro-filter-form { grid-template-columns: repeat(2, minmax(0,1fr)); }.ro-filter-actions { grid-column: 1 / -1; }.ro-kpi-grid { grid-template-columns: 1fr; }.ro-split-chart { grid-template-columns: 1fr; }.ro-panel-head { align-items: flex-start; }.ro-chart-legend { display: none; }.ro-insight-list { grid-template-columns: 1fr; }.ro-target-values { grid-template-columns: 1fr; } }
    @media (max-width: 479px) { .ro-filter-form { grid-template-columns: 1fr; }.ro-filter-actions { grid-column: auto; }.ro-filter-actions .ro-btn { flex: 1; }.ro-active-filters { align-items: flex-start; flex-direction: column; }.ro-presets { overflow-x: auto; flex-wrap: nowrap; }.ro-presets button, .ro-presets > span { white-space: nowrap; } }
    /* Premium analytics layer */
    .page-content:has(.ro-page) {
        background:
            radial-gradient(circle at 8% 4%, rgba(34, 197, 171, .08), transparent 25%),
            radial-gradient(circle at 94% 12%, rgba(69, 112, 246, .07), transparent 24%),
            #f5f8fa;
    }
    .ro-page {
        --ro-ink: #102a3d;
        --ro-muted: #708493;
        --ro-border: rgba(199, 216, 221, .72);
        --ro-soft: #f1f6f7;
        --ro-shadow: 0 18px 48px rgba(28, 61, 78, .075), 0 2px 7px rgba(28, 61, 78, .035);
        position: relative;
        max-width: 1680px;
        margin: 0 auto;
    }
    .ro-breadcrumb {
        padding-left: 4px;
        font-size: 11px;
    }
    .ro-breadcrumb .breadcrumb-item + .breadcrumb-item::before { color: #a8b7c0; }
    .ro-hero {
        min-height: 278px;
        align-items: center;
        padding: 42px 44px;
        border: 1px solid rgba(150, 236, 221, .16);
        border-radius: 28px;
        background:
            radial-gradient(circle at 72% -25%, rgba(68, 223, 199, .32), transparent 36%),
            radial-gradient(circle at 108% 115%, rgba(76, 128, 246, .3), transparent 38%),
            linear-gradient(128deg, #0b263b 0%, #0b3c4e 48%, #087569 100%);
        box-shadow: 0 30px 80px rgba(10, 50, 67, .25);
    }
    .ro-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .22;
        background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);
        background-size: 38px 38px;
        mask-image: linear-gradient(90deg, transparent 35%, #000 100%);
    }
    .ro-hero::after {
        width: 62%;
        height: 3px;
        right: 0;
        left: auto;
        background: linear-gradient(90deg, transparent, rgba(106, 238, 215, .9), rgba(117, 158, 255, .8));
    }
    .ro-hero-copy { max-width: 760px; }
    .ro-hero-brandline { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
    .ro-hero-mark {
        display: grid;
        width: 44px;
        height: 44px;
        place-items: center;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 14px;
        color: #88f5df;
        background: linear-gradient(145deg, rgba(255,255,255,.14), rgba(255,255,255,.04));
        box-shadow: inset 0 1px rgba(255,255,255,.18);
        font-size: 22px;
    }
    .ro-hero-brandline .ro-eyebrow { margin: 0; }
    .ro-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 2px;
        padding: 5px 9px;
        border: 1px solid rgba(137, 246, 221, .18);
        border-radius: 999px;
        color: rgba(225,255,249,.86);
        background: rgba(42, 213, 183, .1);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .ro-live-badge i { width: 6px; height: 6px; border-radius: 50%; background: #5af0c9; box-shadow: 0 0 0 5px rgba(90,240,201,.12); animation: roLive 1.8s ease-in-out infinite; }
    @keyframes roLive { 50% { box-shadow: 0 0 0 8px rgba(90,240,201,0); } }
    .ro-hero h1 { max-width: 620px; margin-bottom: 14px; font-size: clamp(36px, 4.5vw, 58px); font-weight: 750; letter-spacing: -.055em; }
    .ro-hero p { max-width: 700px; color: rgba(237, 250, 250, .72); font-size: 14px; }
    .ro-hero-meta span { padding: 8px 11px; border-color: rgba(255,255,255,.11); background: rgba(4, 28, 42, .18); backdrop-filter: blur(10px); }
    .ro-hero-side { width: 330px; flex-basis: 330px; gap: 12px; }
    .ro-hero-side > .ro-context-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 68px;
        padding: 13px 15px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 17px;
        background: linear-gradient(135deg, rgba(255,255,255,.11), rgba(255,255,255,.045));
        box-shadow: inset 0 1px rgba(255,255,255,.1), 0 12px 30px rgba(0,0,0,.08);
        backdrop-filter: blur(14px);
    }
    .ro-context-card > span { display: grid; width: 38px; height: 38px; flex: 0 0 38px; place-items: center; border-radius: 12px; color: #91f3df; background: rgba(113, 236, 214, .11); font-size: 18px; }
    .ro-context-card > div { min-width: 0; }
    .ro-context-card strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ro-export-group { gap: 9px; }
    .ro-export-group button {
        min-height: 42px;
        border-color: rgba(255,255,255,.15);
        border-radius: 12px;
        background: rgba(255,255,255,.075);
        box-shadow: inset 0 1px rgba(255,255,255,.08);
    }
    .ro-export-group button:hover { border-color: rgba(130,242,220,.35); background: rgba(92,225,200,.14); }
    .ro-dashboard-nav {
        position: relative;
        z-index: 4;
        display: flex;
        width: fit-content;
        max-width: calc(100% - 44px);
        align-items: center;
        gap: 3px;
        margin: -23px auto 0;
        padding: 7px;
        border: 1px solid rgba(205, 221, 224, .76);
        border-radius: 16px;
        background: rgba(255,255,255,.92);
        box-shadow: 0 16px 38px rgba(24, 57, 73, .13);
        backdrop-filter: blur(16px);
    }
    .ro-dashboard-nav a { display: inline-flex; align-items: center; gap: 7px; padding: 9px 13px; border-radius: 11px; color: #617787; font-size: 10px; font-weight: 800; transition: .2s ease; }
    .ro-dashboard-nav a:hover, .ro-dashboard-nav a:focus { color: #08766b; background: #eaf8f5; text-decoration: none; transform: translateY(-1px); }
    .ro-dashboard-nav i { font-size: 15px; }
    .ro-panel {
        border-color: var(--ro-border);
        border-radius: 22px;
        box-shadow: var(--ro-shadow);
        transition: border-color .22s ease, box-shadow .22s ease, transform .22s ease;
    }
    .ro-panel:hover { border-color: rgba(151, 190, 194, .72); box-shadow: 0 22px 58px rgba(28, 61, 78, .095), 0 2px 7px rgba(28,61,78,.04); }
    .ro-filter-panel {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        margin-top: 20px;
        border-color: rgba(132, 190, 188, .62);
        background: rgba(255,255,255,.96);
        box-shadow: 0 24px 64px rgba(21, 64, 78, .105), 0 3px 9px rgba(21,64,78,.04), inset 0 1px rgba(255,255,255,.9);
        backdrop-filter: blur(18px);
    }
    .ro-filter-panel:hover { border-color: rgba(107, 178, 174, .74); box-shadow: 0 28px 72px rgba(21, 64, 78, .12), 0 3px 9px rgba(21,64,78,.045); }
    .ro-filter-panel::before { content: ''; position: absolute; z-index: 4; inset: 0 0 auto; height: 4px; pointer-events: none; background: linear-gradient(90deg, #0a7d73 0%, #2bc5ae 42%, #68a6f8 72%, transparent 100%); }
    .ro-filter-aura { position: absolute; z-index: 0; pointer-events: none; border-radius: 50%; filter: blur(2px); }
    .ro-filter-aura-one { width: 280px; height: 280px; top: -190px; right: 12%; background: rgba(37, 201, 176, .1); }
    .ro-filter-aura-two { width: 210px; height: 210px; bottom: -155px; left: 4%; background: rgba(69, 120, 246, .065); }
    .ro-filter-progress { position: absolute; z-index: 5; inset: 0 0 auto; overflow: hidden; height: 4px; opacity: 0; transition: opacity .2s ease; }
    .ro-filter-progress span { display: block; width: 38%; height: 100%; border-radius: 999px; background: linear-gradient(90deg, transparent, #87f2df, #4d91fb, transparent); transform: translateX(-120%); }
    .ro-filter-panel.is-loading .ro-filter-progress { opacity: 1; }
    .ro-filter-panel.is-loading .ro-filter-progress span { animation: roFilterProgress 1.15s ease-in-out infinite; }
    @keyframes roFilterProgress { to { transform: translateX(360%); } }
    .ro-filter-panel .ro-filter-header { position: relative; z-index: 2; min-height: 96px; padding: 22px 26px; border-bottom: 1px solid rgba(218, 231, 233, .74); background: linear-gradient(115deg, rgba(247,252,252,.96), rgba(255,255,255,.92) 56%, rgba(239,250,248,.72)); transition: border-color .28s ease; }
    .ro-filter-panel.is-collapsed .ro-filter-header { border-bottom-color: transparent; }
    .ro-filter-heading > span { color: #fff; background: linear-gradient(145deg, #1bb09e, #087269); box-shadow: 0 10px 22px rgba(15,143,131,.24), inset 0 1px rgba(255,255,255,.28); }
    .ro-filter-eyebrow { display: block; margin-bottom: 3px; color: #0b8c80; font-size: 8px; font-weight: 900; letter-spacing: .16em; text-transform: uppercase; }
    .ro-filter-heading h2 { font-size: 18px; }
    .ro-filter-head-actions { display: flex; align-items: center; gap: 10px; }
    .ro-filter-state { display: flex; min-width: 164px; align-items: center; gap: 9px; padding: 8px 11px; border: 1px solid #dce9e9; border-radius: 13px; color: #0e887c; background: rgba(255,255,255,.78); box-shadow: 0 6px 16px rgba(22,66,78,.05); transition: color .2s ease, border-color .2s ease, background .2s ease; }
    .ro-filter-state > i { font-size: 20px; }
    .ro-filter-state span { line-height: 1.12; }
    .ro-filter-state small, .ro-filter-state strong { display: block; }
    .ro-filter-state small { margin-bottom: 3px; color: #82939d; font-size: 7px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .ro-filter-state strong { color: currentColor; font-size: 10px; }
    .ro-filter-state.is-dirty { border-color: #ecd59f; color: #a76d0b; background: #fffaf0; }
    .ro-filter-state.is-loading { border-color: #bcdedb; color: #087c72; background: #effaf8; }
    .ro-filter-state.is-error { border-color: #f0c8ce; color: #c5475a; background: #fff5f6; }
    .ro-filter-toggle { display: inline-flex; width: auto; min-width: 88px; height: 42px; align-items: center; justify-content: center; gap: 7px; border-radius: 13px; color: #4f6878; box-shadow: 0 5px 14px rgba(20,55,70,.055); font-size: 10px; font-weight: 800; transition: transform .18s ease, border-color .18s ease, color .18s ease, background .18s ease; }
    .ro-filter-toggle:hover { transform: translateY(-1px); border-color: #acd4d0; color: #08766b; background: #f1faf8; }
    .ro-filter-toggle i { font-size: 17px; transition: transform .28s ease; }
    .ro-filter-panel.is-collapsed .ro-filter-toggle i { transform: rotate(-180deg); }
    .ro-filter-body { display: grid; position: relative; z-index: 1; grid-template-rows: 1fr; opacity: 1; transition: grid-template-rows .38s cubic-bezier(.4,0,.2,1), opacity .25s ease; }
    .ro-filter-body.is-collapsed { grid-template-rows: 0fr; opacity: 0; }
    .ro-filter-body-inner { min-height: 0; overflow: hidden; }
    .ro-presets { display: flex; align-items: center; flex-wrap: nowrap; gap: 18px; padding: 15px 26px; border-bottom: 1px solid #e7eff0; background: rgba(255,255,255,.82); }
    .ro-preset-label { display: flex; flex: 0 0 auto; align-items: center; gap: 9px; padding-right: 18px; border-right: 1px solid #e2eaec; }
    .ro-preset-label > i { display: grid; width: 31px; height: 31px; place-items: center; border-radius: 10px; color: #0d8a7e; background: #e9f8f5; font-size: 16px; }
    .ro-preset-label span, .ro-preset-label strong, .ro-preset-label small { display: block; }
    .ro-preset-label strong { color: #294b5d; font-size: 9px; letter-spacing: .035em; text-transform: uppercase; }
    .ro-preset-label small { margin-top: 2px; color: #8a9aa4; font-size: 8px; }
    .ro-preset-options { display: flex; min-width: 0; flex: 1; align-items: center; gap: 7px; }
    .ro-presets button { display: inline-flex; min-height: 34px; align-items: center; justify-content: center; gap: 5px; border-color: #dce7e9; padding: 7px 11px; color: #607686; background: #f9fbfc; box-shadow: inset 0 1px #fff; transition: transform .18s ease, border-color .18s ease, color .18s ease, background .18s ease, box-shadow .18s ease; }
    .ro-presets button i { font-size: 13px; }
    .ro-presets button:hover { transform: translateY(-2px); border-color: #a9d4cf; color: #08766b; background: #f2faf9; }
    .ro-presets button.is-active { border-color: #7fd1c5; color: #fff; background: linear-gradient(135deg, #13a292, #08746a); box-shadow: 0 7px 16px rgba(15,143,131,.2); }
    .ro-filter-form { display: block; padding: 0; border-top: 0; background: linear-gradient(180deg, rgba(249,252,252,.96), rgba(245,249,250,.92)); }
    .ro-filter-group { padding: 18px 26px 20px; }
    .ro-filter-group + .ro-filter-group { border-top: 1px dashed #dce7e9; }
    .ro-filter-group-detail { background: rgba(244,249,249,.62); }
    .ro-filter-group-head { display: flex; align-items: center; gap: 9px; margin-bottom: 13px; }
    .ro-filter-group-head > span:first-child { display: grid; width: 25px; height: 25px; place-items: center; border: 1px solid #cbe3e0; border-radius: 8px; color: #0a8277; background: #edf9f7; font-size: 8px; font-weight: 900; }
    .ro-filter-group-head > div { line-height: 1.15; }
    .ro-filter-group-head strong, .ro-filter-group-head small { display: block; }
    .ro-filter-group-head strong { color: #28495b; font-size: 10px; }
    .ro-filter-group-head small { margin-top: 3px; color: #8999a4; font-size: 8px; }
    .ro-optional-badge { width: auto !important; height: auto !important; margin-left: auto; border: 0 !important; border-radius: 999px !important; padding: 4px 7px; color: #78909d !important; background: #edf2f4 !important; font-size: 7px !important; letter-spacing: .06em; text-transform: uppercase; }
    .ro-filter-grid { display: grid; gap: 13px; }
    .ro-filter-grid-primary { grid-template-columns: 1.25fr repeat(2, 1fr) 1.15fr; }
    .ro-filter-grid-detail { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .ro-field { gap: 7px; }
    .ro-field-command-only { display: none; }
    .ro-field > span { display: flex; align-items: center; gap: 5px; color: #536b7b; font-size: 8.5px; letter-spacing: .07em; transition: color .18s ease; }
    .ro-field > span i { color: #8ba0aa; font-size: 12px; transition: color .18s ease; }
    .ro-field:focus-within > span, .ro-field:focus-within > span i { color: #078176; }
    .ro-field input, .ro-field select { height: 44px; border-color: #d5e2e5; border-radius: 12px; padding: 0 12px; background-color: rgba(255,255,255,.96); box-shadow: 0 3px 8px rgba(20,55,70,.025), inset 0 1px rgba(255,255,255,.9); transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .ro-field input:hover, .ro-field select:hover { transform: translateY(-1px); border-color: #a7cbc9; box-shadow: 0 7px 15px rgba(20,55,70,.055); }
    .ro-field input:focus, .ro-field select:focus { transform: translateY(-1px); border-color: #2eaa9e; box-shadow: 0 0 0 4px rgba(15,143,131,.09), 0 8px 18px rgba(20,55,70,.06); }
    .ro-field.is-selected input, .ro-field.is-selected select { border-color: #a9d7d2; background-color: #fbfffe; }
    .ro-filter-actions { display: flex; align-items: center; justify-content: space-between; grid-column: auto; gap: 18px; padding: 16px 26px; border-top: 1px solid #dde9ea; background: rgba(255,255,255,.9); }
    .ro-filter-actions > div:last-child { display: flex; align-items: center; gap: 9px; }
    .ro-filter-action-copy { display: flex; min-width: 0; align-items: center; gap: 9px; color: #738893; }
    .ro-filter-action-copy > i { display: grid; width: 30px; height: 30px; flex: 0 0 30px; place-items: center; border-radius: 10px; color: #168f83; background: #eaf8f5; font-size: 15px; }
    .ro-filter-action-copy span, .ro-filter-action-copy strong, .ro-filter-action-copy small { display: block; }
    .ro-filter-action-copy strong { color: #446072; font-size: 9px; }
    .ro-filter-action-copy small { margin-top: 2px; font-size: 8px; }
    .ro-filter-panel.has-pending-changes .ro-filter-action-copy > i { color: #aa710f; background: #fff4da; }
    .ro-filter-panel.has-pending-changes .ro-filter-action-copy strong { color: #9b680e; }
    .ro-btn { min-height: 44px; border-radius: 12px; transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease; }
    .ro-btn:hover:not(:disabled) { transform: translateY(-2px); }
    .ro-btn:disabled { cursor: wait; opacity: .68; }
    .ro-btn-primary { min-width: 142px; background: linear-gradient(135deg, #12a190, #086d65); box-shadow: 0 10px 22px rgba(15,143,131,.2); }
    .ro-btn-primary:hover:not(:disabled) { box-shadow: 0 14px 28px rgba(15,143,131,.27); }
    .ro-btn-ghost:hover { color: #1b5366; box-shadow: 0 7px 15px rgba(27,75,94,.07); }
    .ro-active-filters { min-height: 52px; padding: 12px 26px; border-top-color: #e2ebed; background: linear-gradient(90deg, #fbfdfd, #fff); }
    .ro-active-filters > span { display: inline-flex; align-items: center; gap: 6px; }
    .ro-active-filters > span > i { font-size: 14px; }
    .ro-active-filters > span b { display: grid; min-width: 20px; height: 20px; place-items: center; padding: 0 5px; color: #fff; background: linear-gradient(135deg, #10a091, #08746a); box-shadow: 0 4px 9px rgba(15,143,131,.16); }
    .ro-active-filters > div { min-width: 0; }
    .ro-active-filters > div b { display: inline-flex; align-items: center; gap: 4px; padding: 5px 9px; border: 1px solid #dce8e9; color: #566e7e; background: #f5f8f9; }
    .ro-active-filters > div b::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #22aa98; }
    .ro-results-heading { display: flex; align-items: end; justify-content: space-between; gap: 18px; margin: 30px 3px 15px; }
    .ro-results-heading span { color: #0c897d; font-size: 9px; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
    .ro-results-heading h2 { margin: 4px 0 0; color: #132f43; font-size: 21px; font-weight: 780; letter-spacing: -.025em; }
    .ro-results-heading p { margin: 0; color: #7b8e9b; font-size: 10px; }
    .ro-results-heading p i { margin-right: 4px; color: #0f9587; }
    .ro-results-heading p strong { color: #516979; }
    .ro-kpi-grid { gap: 16px; margin-top: 0; }
    .ro-kpi {
        min-height: 154px;
        align-items: flex-start;
        gap: 15px;
        padding: 21px;
        border-color: rgba(207, 221, 225, .84);
        border-radius: 20px;
        background: linear-gradient(145deg, #fff 0%, #fbfdfd 100%);
        box-shadow: 0 14px 38px rgba(29,63,80,.065), inset 0 1px #fff;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .ro-kpi::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 3px; background: linear-gradient(180deg, transparent, var(--kpi-color, #0f8f83), transparent); opacity: .8; }
    .ro-kpi::after { content: var(--ro-card-index); width: auto; height: auto; right: 16px; top: 13px; color: rgba(21,59,78,.055); background: transparent; font-size: 35px; font-weight: 900; line-height: 1; }
    .ro-kpi:hover { z-index: 1; transform: translateY(-4px); border-color: color-mix(in srgb, var(--kpi-color, #0f8f83) 28%, #dce7e9); box-shadow: 0 24px 54px rgba(29,63,80,.11); }
    .ro-kpi-glow { position: absolute; width: 110px; height: 110px; right: -48px; bottom: -62px; border-radius: 50%; background: var(--kpi-soft, #e8f7f4); opacity: .6; filter: blur(3px); }
    .ro-kpi-icon { width: 47px; height: 47px; flex-basis: 47px; border: 1px solid color-mix(in srgb, var(--kpi-color, #0f8f83) 15%, transparent); border-radius: 15px; box-shadow: inset 0 1px rgba(255,255,255,.7); }
    .ro-kpi small { letter-spacing: .075em; }
    .ro-kpi strong { margin: 10px 0 7px; font-size: clamp(19px, 1.75vw, 25px); }
    .ro-kpi p { max-width: 190px; }
    .ro-panel-head { min-height: 82px; padding: 22px 25px 17px; }
    .ro-heading > span { width: 43px; height: 43px; flex-basis: 43px; border: 1px solid rgba(255,255,255,.72); border-radius: 14px; box-shadow: 0 7px 16px rgba(38,74,91,.07), inset 0 1px #fff; }
    .ro-heading h2 { font-size: 16px; letter-spacing: -.02em; }
    .ro-heading p { margin-top: 4px; font-size: 10px; }
    .ro-filter-panel .ro-filter-heading > span { color: #fff; background: linear-gradient(145deg, #1bb09e, #087269); box-shadow: 0 10px 22px rgba(15,143,131,.24), inset 0 1px rgba(255,255,255,.28); }
    .ro-filter-panel .ro-filter-heading h2 { font-size: 18px; }
    .ro-panel-tools { display: flex; align-items: flex-end; flex-direction: column; gap: 9px; }
    .ro-panel-tools.is-inline { align-items: center; flex-direction: row; }
    .ro-segment { display: inline-flex; gap: 3px; padding: 4px; border: 1px solid #dce7e9; border-radius: 12px; background: #f3f7f8; }
    .ro-segment button { min-height: 28px; border: 0; border-radius: 8px; padding: 5px 10px; color: #718593; background: transparent; font-size: 9px; font-weight: 800; transition: .18s ease; }
    .ro-segment button:hover { color: #1b5c6d; background: rgba(255,255,255,.72); }
    .ro-segment button.is-active { color: #fff; background: linear-gradient(135deg, #159f90, #08756b); box-shadow: 0 5px 12px rgba(15,143,131,.2); }
    .ro-chart-legend { gap: 10px; font-size: 9px; }
    .ro-chart-legend span { padding: 3px 7px; border-radius: 999px; background: #f6f9fa; }
    .ro-trend-panel { overflow: hidden; min-height: 450px; }
    .ro-trend-panel .ro-chart { margin: 0 14px 15px; border: 1px solid #edf2f3; border-radius: 16px; background: linear-gradient(180deg, #fff, #fbfdfd); }
    .ro-products-panel .table-responsive { border-top: 1px solid #e9f0f2; }
    #roTrendPanel, #roFastMovingPanel, #roProductsPanel, #roHourlyPanel, #roMarketBasketPanel { scroll-margin-top: 88px; }
    .ro-analysis-panel { overflow: hidden; margin-top: 20px; }
    .ro-heading small { display: block; margin-top: 4px; color: #95a3ad; font-size: 8px; }
    .ro-basket-guide { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; padding: 0 25px 18px; }
    .ro-basket-guide span { display: inline-flex; align-items: center; gap: 5px; padding: 7px 10px; border: 1px solid #e0e9eb; border-radius: 999px; color: #748794; background: #f7fafb; font-size: 8px; }
    .ro-basket-guide strong { color: #31576b; font-weight: 900; }
    .ro-basket-pair { display: flex; min-width: 300px; align-items: center; gap: 9px; }
    .ro-basket-pair > span { display: grid; min-width: 0; grid-template-columns: 20px minmax(120px, 1fr); column-gap: 7px; align-items: center; }
    .ro-basket-pair b { display: grid; width: 20px; height: 20px; grid-row: 1 / span 2; place-items: center; border-radius: 7px; color: #fff; background: #0f8f83; font-size: 8px; }
    .ro-basket-pair > span:last-child b { background: #596fd2; }
    .ro-basket-pair strong, .ro-basket-pair small { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ro-basket-pair strong { color: #294b5d; font-size: 10px; }
    .ro-basket-pair small { color: #91a0aa; font-size: 8px; }
    .ro-basket-pair > i { color: #a8b5bd; }
    .ro-association { display: inline-flex; min-width: 58px; justify-content: center; border-radius: 999px; padding: 5px 8px; color: #6d7f8b; background: #eef2f4; font-size: 8px; font-weight: 900; }
    .ro-association.is-kuat { color: #087468; background: #ddf5ef; }
    .ro-association.is-positif { color: #2462b2; background: #e4efff; }
    .ro-association.is-lemah { color: #a76113; background: #fff0d9; }
    .ro-basket-table td { vertical-align: middle; }
    .ro-soft-badge { border: 1px solid #d5eee8; padding: 6px 10px; }
    .ro-table thead th { padding: 12px 15px; border-color: #e5edef; color: #617786; background: linear-gradient(180deg, #f8fafb, #f3f7f8); }
    .ro-table td { padding: 14px 15px; }
    .ro-table tbody tr { transition: background-color .16s ease; }
    .ro-rank { width: 31px; height: 31px; border: 1px solid #e0e9eb; border-radius: 10px; background: #f4f7f8; }
    .ro-rank.is-top { border-color: transparent; box-shadow: 0 6px 14px rgba(15,143,131,.2); }
    .ro-product-name strong { font-size: 12px; }
    .ro-grid { gap: 20px; }
    .ro-split-chart { padding-right: 8px; }
    .ro-mini-row, .ro-summary-row { padding: 11px 0; }
    .ro-mini-row strong, .ro-summary-row strong { font-size: 11px; }
    .ro-cashier-table { margin: 0 18px 18px; border: 1px solid #e9f0f2; border-radius: 14px; }
    .ro-target-panel { overflow: hidden; background: radial-gradient(circle at 100% 100%, rgba(64, 211, 184, .13), transparent 35%), linear-gradient(145deg, #fff, #f6fbfa); }
    .ro-target-values { gap: 12px; padding: 5px 25px 20px; }
    .ro-target-values div { padding: 15px; border-radius: 14px; box-shadow: 0 6px 16px rgba(31,79,88,.04); }
    .ro-progress { height: 11px; margin-right: 25px; margin-left: 25px; box-shadow: inset 0 2px 4px rgba(13,83,75,.08); }
    .ro-progress span { position: relative; overflow: hidden; background: linear-gradient(90deg, #0b8277, #45cfb5); }
    .ro-progress span::after { content: ''; position: absolute; inset: 0; background: linear-gradient(105deg, transparent 25%, rgba(255,255,255,.35) 45%, transparent 65%); animation: roProgressShine 2.5s linear infinite; }
    @keyframes roProgressShine { from { transform: translateX(-100%); } to { transform: translateX(200%); } }
    .ro-target-form { padding-right: 25px; padding-left: 25px; }
    .ro-insight-list { gap: 12px; padding: 5px 25px 25px; }
    .ro-insight { min-height: 89px; padding: 14px; border-radius: 15px; background: linear-gradient(145deg, #fff, #fafcfc); transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .ro-insight:hover { transform: translateY(-2px); border-color: #cddfe1; box-shadow: 0 10px 22px rgba(31,68,84,.07); }
    .ro-insight > span { width: 38px; height: 38px; flex-basis: 38px; border: 1px solid rgba(255,255,255,.75); border-radius: 12px; box-shadow: 0 5px 12px rgba(29,72,87,.06); }
    .ro-insight h3 { font-size: 11px; }
    .ro-insight p { font-size: 9.5px; }
    .apexcharts-tooltip { border: 0 !important; border-radius: 12px !important; box-shadow: 0 12px 32px rgba(20, 48, 64, .15) !important; }
    .apexcharts-tooltip-title { border-bottom-color: #edf2f3 !important; background: #f7fafb !important; }
    .ro-toast { right: 26px; bottom: 26px; border: 1px solid rgba(255,255,255,.12); border-radius: 14px; box-shadow: 0 18px 42px rgba(11,41,56,.25); backdrop-filter: blur(12px); }
    @media (max-width: 1199px) {
        .ro-hero { padding: 36px; }
        .ro-panel-tools { align-items: flex-end; }
        .ro-filter-grid-detail { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ro-preset-options { overflow-x: auto; padding: 3px 2px 7px; scrollbar-width: thin; }
        .ro-presets button { flex: 0 0 auto; }
    }
    @media (max-width: 991px) {
        .ro-hero { align-items: stretch; }
        .ro-hero-side { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ro-dashboard-nav { width: calc(100% - 36px); justify-content: center; }
        .ro-dashboard-nav a { flex: 1; justify-content: center; }
        .ro-panel-head { align-items: flex-start; }
        .ro-panel-tools { align-items: flex-end; }
        .ro-filter-grid-primary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ro-filter-panel .ro-filter-header { align-items: center; }
    }
    @media (max-width: 767px) {
        .ro-hero { padding: 30px 24px 36px; border-radius: 22px; }
        .ro-hero::before { display: none; }
        .ro-hero h1 { font-size: 38px; }
        .ro-hero-side { grid-template-columns: 1fr; }
        .ro-dashboard-nav { justify-content: flex-start; overflow-x: auto; margin-top: -19px; }
        .ro-dashboard-nav a { flex: 0 0 auto; }
        .ro-results-heading { align-items: flex-start; flex-direction: column; }
        .ro-panel-head { flex-direction: column; }
        .ro-panel-tools, .ro-panel-tools.is-inline { width: 100%; align-items: flex-start; flex-direction: column; }
        .ro-segment { max-width: 100%; overflow-x: auto; }
        .ro-trend-panel .ro-chart { margin-right: 8px; margin-left: 8px; }
        .ro-filter-panel .ro-filter-header { min-height: 88px; align-items: center; flex-direction: row; padding: 18px 20px; }
        .ro-filter-head-actions { margin-left: auto; }
        .ro-filter-state { min-width: 42px; width: 42px; height: 42px; justify-content: center; padding: 0; }
        .ro-filter-state > i { font-size: 19px; }
        .ro-filter-state span { position: absolute; overflow: hidden; width: 1px; height: 1px; clip: rect(0 0 0 0); white-space: nowrap; }
        .ro-filter-toggle { min-width: 42px; width: 42px; }
        .ro-filter-toggle > span { display: none; }
        .ro-presets { gap: 12px; padding-right: 20px; padding-left: 20px; }
        .ro-preset-label { padding-right: 12px; }
        .ro-filter-group { padding-right: 20px; padding-left: 20px; }
        .ro-filter-grid-detail { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ro-filter-actions { align-items: stretch; flex-direction: column; padding-right: 20px; padding-left: 20px; }
        .ro-filter-actions > div:last-child { justify-content: flex-end; }
        .ro-active-filters { padding-right: 20px; padding-left: 20px; }
        .ro-basket-guide { padding-right: 20px; padding-left: 20px; }
    }
    @media (max-width: 479px) {
        .ro-hero-brandline { align-items: flex-start; }
        .ro-live-badge { margin-left: 54px; }
        .ro-dashboard-nav { max-width: calc(100% - 20px); width: calc(100% - 20px); }
        .ro-dashboard-nav a span { display: none; }
        .ro-dashboard-nav a { min-width: 42px; }
        .ro-kpi { min-height: 142px; }
        .ro-filter-panel .ro-filter-header { gap: 10px; padding: 16px; }
        .ro-filter-heading { gap: 9px; }
        .ro-filter-panel .ro-filter-heading > span { width: 39px; height: 39px; flex-basis: 39px; border-radius: 12px; font-size: 18px; }
        .ro-filter-panel .ro-filter-heading h2 { font-size: 15px; }
        .ro-filter-heading p { display: none; }
        .ro-filter-eyebrow { font-size: 7px; }
        .ro-filter-state { display: none; }
        .ro-preset-label { display: none; }
        .ro-presets { padding: 12px 16px 8px; }
        .ro-filter-grid-primary, .ro-filter-grid-detail { grid-template-columns: 1fr; }
        .ro-filter-group { padding: 17px 16px 19px; }
        .ro-filter-actions { padding: 15px 16px; }
        .ro-filter-action-copy small { max-width: 240px; line-height: 1.4; }
        .ro-filter-actions > div:last-child { width: 100%; }
        .ro-filter-actions .ro-btn { flex: 1; min-width: 0; padding-right: 10px; padding-left: 10px; }
        .ro-active-filters { padding-right: 16px; padding-left: 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .ro-filter-panel *, .ro-filter-panel *::before, .ro-filter-panel *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }
    @media print {
        .sidebar, .navbar, .footer, .page-tabs, .ro-breadcrumb, .ro-dashboard-nav, .ro-filter-panel, .ro-export-group, .ro-target-form, .ro-toast { display: none !important; }
        .page-wrapper, .page-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .ro-page { color: #111; }.ro-hero { min-height: 0; padding: 18px; box-shadow: none; print-color-adjust: exact; }.ro-hero-side { width: 260px; flex-basis: 260px; }.ro-hero-meta { display: none; }.ro-panel, .ro-kpi { break-inside: avoid; box-shadow: none; }.ro-kpi-grid { grid-template-columns: repeat(4,1fr); }.ro-grid-two, .ro-bottom-grid { grid-template-columns: repeat(2,1fr); }.ro-chart { min-height: 220px; }.ro-table { font-size: 8px; }
    }
</style>
