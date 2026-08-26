<style>
    .command-center {
        --cc-ink: #233451;
        --cc-muted: #7a879b;
        --cc-line: #e5eaf1;
        --cc-primary: #315ca7;
        color: var(--cc-ink);
        font-family: "Plus Jakarta Sans", sans-serif;
    }
    .command-center *, .command-center *::before, .command-center *::after { box-sizing: border-box; }
    .cc-hero {
        position: relative; display: grid; grid-template-columns: minmax(0, 1fr) minmax(500px, .82fr); gap: 26px;
        overflow: hidden; margin: -1px -1px 25px; padding: 32px; border: 1px solid rgba(255,255,255,.09); border-radius: 23px;
        color: #fff; background: radial-gradient(circle at 7% 12%, rgba(100,197,186,.27), transparent 26%),
            radial-gradient(circle at 86% 90%, rgba(80,113,211,.34), transparent 33%), linear-gradient(118deg, #10243e, #183b5a 52%, #263f76);
        box-shadow: 0 18px 45px rgba(25,45,79,.17);
    }
    .cc-hero::before { position:absolute; width:270px; height:270px; top:-150px; right:31%; content:""; border:1px solid rgba(255,255,255,.08); border-radius:50%; }
    .cc-hero-copy, .cc-filter-shell { position: relative; z-index: 1; }
    .cc-hero-copy { align-self: center; }
    .cc-eyebrow { display:inline-flex; align-items:center; gap:7px; margin-bottom:9px; color:#9fe0d5; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .cc-eyebrow i { font-size: 16px; }
    .cc-hero h1 { margin:0; font-size:clamp(24px,2.3vw,36px); font-weight:850; letter-spacing:-.045em; }
    .cc-hero-copy > p { max-width:680px; margin:10px 0 17px; color:#c8d6e7; font-size:12px; line-height:1.65; }
    .cc-live-line { display:flex; flex-wrap:wrap; align-items:center; gap:7px; color:#b7c8dc; font-size:9px; }
    .cc-live-line strong { color:#e7f5f1; font-size:9px; }
    .cc-live-dot { width:7px; height:7px; border:2px solid rgba(93,218,181,.28); border-radius:50%; background:#5cdab4; box-shadow:0 0 0 4px rgba(92,218,180,.1); animation:ccPulse 2s infinite; }
    @keyframes ccPulse { 50% { box-shadow:0 0 0 7px rgba(92,218,180,0); } }
    .cc-filter-shell { align-self:center; padding:17px; border:1px solid rgba(255,255,255,.12); border-radius:17px; background:rgba(9,25,47,.38); box-shadow:inset 0 1px rgba(255,255,255,.05); backdrop-filter:blur(12px); }
    .cc-filter-topline { display:flex; align-items:center; justify-content:space-between; margin-bottom:13px; }
    .cc-filter-topline small, .cc-filter-topline strong { display:block; }
    .cc-filter-topline small { margin-bottom:2px; color:#8fa9c4; font-size:8px; font-weight:800; letter-spacing:.09em; text-transform:uppercase; }
    .cc-filter-topline strong { color:#fff; font-size:13px; }
    .cc-refresh-button { display:inline-flex; min-height:33px; align-items:center; gap:5px; padding:7px 11px; border:1px solid rgba(255,255,255,.15); border-radius:9px; color:#dbe7f4; background:rgba(255,255,255,.07); font-size:9px; font-weight:800; transition:.2s ease; }
    .cc-refresh-button:hover { color:#fff; background:rgba(255,255,255,.13); }
    .cc-refresh-button.is-loading i { animation:ccSpin .8s linear infinite; }
    @keyframes ccSpin { to { transform:rotate(360deg); } }
    .cc-filter-grid { display:grid; grid-template-columns:minmax(170px,.75fr) minmax(260px,1.25fr); gap:10px; }
    .cc-field > span, .cc-custom-range label > span { display:block; margin-bottom:5px; color:#91a9c2; font-size:8px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
    .cc-field .form-select { min-height:37px; padding:7px 32px 7px 10px; border-color:rgba(255,255,255,.13); border-radius:9px; color:#eff5fb; background-color:rgba(255,255,255,.08); font-size:10px; font-weight:700; }
    .cc-field .form-select option { color:#243550; background:#fff; }
    .cc-period-tabs { display:grid; grid-template-columns:repeat(6,1fr); gap:4px; padding:3px; border:1px solid rgba(255,255,255,.09); border-radius:10px; background:rgba(6,18,35,.25); }
    .cc-period-tabs button { min-height:29px; padding:5px; border:0; border-radius:7px; color:#9eb2c9; background:transparent; font-size:8px; font-weight:800; white-space:nowrap; }
    .cc-period-tabs button:hover, .cc-period-tabs button.is-active { color:#17334e; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,.15); }
    .cc-custom-range { display:none; grid-template-columns:1fr auto 1fr auto; align-items:end; gap:7px; margin-top:10px; padding-top:10px; border-top:1px solid rgba(255,255,255,.09); }
    .cc-custom-range.is-open { display:grid; }
    .cc-custom-range > i { align-self:center; margin-top:14px; color:#8fa8c3; }
    .cc-custom-range input { width:100%; min-height:34px; padding:6px 8px; border:1px solid rgba(255,255,255,.13); border-radius:8px; color:#fff; color-scheme:dark; background:rgba(255,255,255,.07); font-size:9px; }
    .cc-custom-range button { min-height:34px; padding:7px 12px; border:0; border-radius:8px; color:#143752; background:#8fd9c9; font-size:9px; font-weight:850; }
    .cc-filter-caption { display:flex; align-items:center; gap:5px; margin-top:10px; color:#9cb0c6; font-size:8px; }
    .cc-system-message { margin:-10px 0 18px; padding:11px 14px; border:1px solid #f1c7ce; border-radius:10px; color:#a23a4d; background:#fff1f3; font-size:10px; }
    .cc-empty-scope { display:flex; align-items:center; gap:13px; margin-bottom:20px; padding:18px; border:1px solid #f0c7ce; border-radius:14px; color:#aa4052; background:#fff4f5; }
    .cc-empty-scope > i { font-size:27px; }.cc-empty-scope strong,.cc-empty-scope span{display:block}.cc-empty-scope strong{font-size:12px}.cc-empty-scope span{margin-top:3px;font-size:9px}
    .cc-section-heading { display:flex; align-items:end; justify-content:space-between; gap:20px; margin:0 2px 13px; }
    .cc-section-heading--compact { margin-top:23px; }
    .cc-section-heading span { display:block; margin-bottom:2px; color:#3a70b3; font-size:8px; font-weight:850; letter-spacing:.11em; text-transform:uppercase; }
    .cc-section-heading h2 { margin:0; color:#293a56; font-size:16px; font-weight:850; letter-spacing:-.025em; }
    .cc-section-heading p { max-width:430px; margin:0; color:#8a96a8; font-size:8px; text-align:right; }
    .cc-alert-total { padding:7px 10px; border:1px solid #e5eaf1; border-radius:99px; color:#7c889a; background:#fff; font-size:8px; }
    .cc-alert-total b { color:#d34d61; font-size:10px; }
    .cc-kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .cc-kpi-card { position:relative; overflow:hidden; min-height:139px; padding:17px; border:1px solid var(--cc-line); border-radius:16px; background:#fff; box-shadow:0 8px 22px rgba(27,45,75,.055); transition:.2s ease; }
    .cc-kpi-card:hover { transform:translateY(-2px); box-shadow:0 13px 28px rgba(27,45,75,.09); }
    .cc-kpi-card::after { position:absolute; width:80px; height:80px; right:-29px; bottom:-35px; content:""; border-radius:50%; background:var(--cc-kpi-soft); }
    .cc-kpi-card.is-primary{--cc-kpi:#315ca7;--cc-kpi-soft:#edf2fc}.cc-kpi-card.is-success{--cc-kpi:#16866b;--cc-kpi-soft:#eaf8f3}.cc-kpi-card.is-info{--cc-kpi:#3186bd;--cc-kpi-soft:#edf7fc}.cc-kpi-card.is-teal{--cc-kpi:#168c94;--cc-kpi-soft:#e9f8f8}.cc-kpi-card.is-violet{--cc-kpi:#7559bb;--cc-kpi-soft:#f1edfb}.cc-kpi-card.is-warning{--cc-kpi:#c98528;--cc-kpi-soft:#fff6e8}.cc-kpi-card.is-danger{--cc-kpi:#d64c60;--cc-kpi-soft:#fff0f2}.cc-kpi-card.is-orange{--cc-kpi:#dd7441;--cc-kpi-soft:#fff2eb}
    .cc-kpi-top { display:flex; align-items:start; justify-content:space-between; }
    .cc-kpi-icon { display:grid; width:38px; height:38px; place-items:center; border-radius:11px; color:var(--cc-kpi); background:var(--cc-kpi-soft); font-size:19px; }
    .cc-kpi-change { display:inline-flex; align-items:center; gap:2px; padding:4px 6px; border-radius:99px; color:#66758c; background:#f3f5f8; font-size:8px; font-weight:800; }
    .cc-kpi-change.is-up { color:#12755f; background:#e9f7f2; }.cc-kpi-change.is-down { color:#c3475a; background:#fff0f2; }
    .cc-kpi-card > small { display:block; margin:13px 0 3px; color:#8490a2; font-size:8px; font-weight:750; letter-spacing:.02em; }
    .cc-kpi-value { position:relative; z-index:1; overflow:hidden; color:#293a56; font-size:clamp(16px,1.45vw,22px); font-weight:850; letter-spacing:-.035em; text-overflow:ellipsis; white-space:nowrap; }
    .cc-kpi-meta { position:relative; z-index:1; display:flex; align-items:center; gap:4px; margin-top:4px; color:#9aa4b3; font-size:7px; }
    .cc-action-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .cc-action-card { --action:#315ca7; --action-soft:#edf2fb; display:grid; grid-template-columns:auto minmax(0,1fr) auto; align-items:center; gap:10px; min-height:82px; padding:13px; border:1px solid var(--cc-line); border-radius:14px; color:inherit; background:#fff; box-shadow:0 6px 17px rgba(27,45,75,.045); transition:.2s ease; }
    .cc-action-card:hover { color:inherit; border-color:color-mix(in srgb,var(--action) 30%,#e5eaf1); transform:translateY(-2px); }
    .cc-action-card.is-danger{--action:#d64c60;--action-soft:#fff0f2}.cc-action-card.is-warning{--action:#cf8627;--action-soft:#fff5e5}.cc-action-card.is-info{--action:#3186bd;--action-soft:#edf7fc}.cc-action-card.is-violet{--action:#7559bb;--action-soft:#f1edfb}
    .cc-action-icon { display:grid; width:39px; height:39px; place-items:center; border-radius:11px; color:var(--action); background:var(--action-soft); font-size:19px; }
    .cc-action-copy strong,.cc-action-copy span{display:block}.cc-action-copy strong{font-size:9px}.cc-action-copy span{margin-top:3px;color:#8d98a8;font-size:7px;line-height:1.35}.cc-action-count{min-width:27px;padding:5px;border-radius:8px;color:var(--action);background:var(--action-soft);font-size:12px;font-weight:850;text-align:center}
    .cc-main-grid { display:grid; grid-template-columns:minmax(0,1.75fr) minmax(330px,.75fr); gap:14px; margin-top:14px; }
    .cc-insight-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin-top:14px; }
    .cc-panel { min-width:0; overflow:hidden; border:1px solid var(--cc-line); border-radius:17px; background:#fff; box-shadow:0 8px 22px rgba(27,45,75,.05); }
    .cc-panel-header { display:flex; min-height:68px; align-items:center; justify-content:space-between; gap:13px; padding:14px 17px; border-bottom:1px solid #edf0f5; }
    .cc-panel-title { display:flex; min-width:0; align-items:center; gap:10px; }.cc-panel-title small,.cc-panel-title h3{display:block}.cc-panel-title small{margin-bottom:1px;color:#8c98a9;font-size:7px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.cc-panel-title h3{margin:0;color:#2b3c58;font-size:12px;font-weight:850;letter-spacing:-.01em}
    .cc-panel-icon { display:grid; flex:0 0 auto; width:36px; height:36px; place-items:center; border-radius:10px; font-size:18px; }.cc-panel-icon.is-primary{color:#315ca7;background:#edf2fb}.cc-panel-icon.is-danger{color:#d64c60;background:#fff0f2}.cc-panel-icon.is-success{color:#16866b;background:#eaf8f3}.cc-panel-icon.is-violet{color:#7559bb;background:#f1edfb}.cc-panel-icon.is-orange{color:#dd7441;background:#fff2eb}.cc-panel-icon.is-info{color:#3186bd;background:#edf7fc}.cc-panel-icon.is-teal{color:#168c94;background:#e9f8f8}
    .cc-panel-link { display:inline-flex; flex:0 0 auto; align-items:center; gap:3px; color:#5273a5; font-size:8px; font-weight:800; }.cc-panel-link:hover{color:#315ca7}.cc-panel-badge{padding:5px 7px;border-radius:99px;color:#6e7d92;background:#f1f4f8;font-size:7px;font-weight:800}
    .cc-chart { min-height:326px; padding:4px 8px 0; }
    .cc-chart-legend { display:flex; gap:10px; color:#7f8b9d; font-size:7px; font-weight:750; }.cc-chart-legend span{display:flex;align-items:center;gap:4px}.cc-chart-legend i{width:7px;height:7px;border-radius:50%}.cc-chart-legend i.is-sales{background:#315ca7}.cc-chart-legend i.is-transaction{background:#48aa98}
    .cc-alert-panel { display:flex; min-height:395px; flex-direction:column; }
    .cc-alert-tabs { display:grid; grid-template-columns:repeat(4,1fr); gap:4px; padding:9px 11px; border-bottom:1px solid #edf0f5; background:#fbfcfe; }
    .cc-alert-tabs button { display:flex; min-width:0; min-height:29px; align-items:center; justify-content:center; gap:3px; padding:5px; border:0; border-radius:7px; color:#8490a2; background:transparent; font-size:7px; font-weight:800; white-space:nowrap; }
    .cc-alert-tabs button.is-active { color:#365c92; background:#eaf0f9; }.cc-alert-tabs button > i{width:5px;height:5px;border-radius:50%}.cc-alert-tabs i.is-critical{background:#d64c60}.cc-alert-tabs i.is-warning{background:#d58a27}.cc-alert-tabs i.is-info{background:#3186bd}.cc-alert-tabs b{font-size:7px}
    .cc-alert-list { flex:1; max-height:318px; overflow:auto; padding:5px 11px 11px; scrollbar-width:thin; }
    .cc-alert-item { --alert:#7a879b; --alert-soft:#f3f5f8; display:grid; grid-template-columns:auto minmax(0,1fr) auto; gap:9px; padding:11px 2px; border-bottom:1px solid #edf0f4; color:inherit; }
    .cc-alert-item:last-child{border-bottom:0}.cc-alert-item.is-critical{--alert:#d64c60;--alert-soft:#fff0f2}.cc-alert-item.is-warning{--alert:#d58a27;--alert-soft:#fff5e6}.cc-alert-item.is-info{--alert:#3186bd;--alert-soft:#edf7fc}
    .cc-alert-item-icon { display:grid; width:31px; height:31px; place-items:center; border-radius:9px; color:var(--alert); background:var(--alert-soft); font-size:15px; }
    .cc-alert-copy { min-width:0; }.cc-alert-copy strong,.cc-alert-copy span,.cc-alert-copy small{display:block}.cc-alert-copy strong{overflow:hidden;color:#34445e;font-size:8px;text-overflow:ellipsis;white-space:nowrap}.cc-alert-copy span{margin-top:2px;color:#8995a5;font-size:7px;line-height:1.35}.cc-alert-copy small{margin-top:3px;color:var(--alert);font-size:7px;font-weight:800}.cc-alert-arrow{align-self:center;color:#a4adba;font-size:15px}.cc-alert-item:hover .cc-alert-arrow{color:var(--alert);transform:translateX(2px)}
    .cc-empty-block { display:flex; min-height:150px; align-items:center; justify-content:center; flex-direction:column; gap:5px; padding:20px; color:#98a3b2; text-align:center; }.cc-empty-block i{color:#c0c8d3;font-size:29px}.cc-empty-block strong{color:#66758a;font-size:9px}.cc-empty-block span{font-size:7px}
    .cc-inventory-layout,.cc-payment-layout { display:grid; grid-template-columns:220px minmax(0,1fr); align-items:center; min-height:268px; padding:8px 17px 14px; }.cc-donut-chart{min-height:210px}
    .cc-inventory-legend,.cc-payment-list{display:grid; gap:7px}.cc-inventory-row,.cc-payment-row{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:7px;padding:7px 0;border-bottom:1px solid #f0f2f5}.cc-inventory-row:last-child,.cc-payment-row:last-child{border-bottom:0}.cc-legend-dot{width:8px;height:8px;border-radius:3px}.cc-inventory-row span,.cc-payment-row span{color:#758296;font-size:8px}.cc-inventory-row b,.cc-payment-row b{color:#35465f;font-size:9px}.cc-payment-row small{display:block;color:#a0a9b6;font-size:6px}.cc-payment-color{width:4px;height:25px;border-radius:99px}
    .cc-ranking-list,.cc-branch-list{min-height:268px;padding:7px 17px 14px}.cc-ranking-item,.cc-branch-item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #edf0f4}.cc-ranking-item:last-child,.cc-branch-item:last-child{border-bottom:0}.cc-rank{display:grid;width:27px;height:27px;place-items:center;border-radius:8px;color:#8b6a28;background:#fff3d8;font-size:8px;font-weight:850}.cc-rank.is-top{color:#fff;background:linear-gradient(135deg,#d59d30,#f0bd55);box-shadow:0 5px 12px rgba(212,156,46,.22)}.cc-ranking-copy,.cc-branch-copy{min-width:0}.cc-ranking-copy strong,.cc-ranking-copy small,.cc-branch-copy strong,.cc-branch-copy small{display:block}.cc-ranking-copy strong,.cc-branch-copy strong{overflow:hidden;color:#35465f;font-size:8px;text-overflow:ellipsis;white-space:nowrap}.cc-ranking-copy small,.cc-branch-copy small{margin-top:2px;color:#929dac;font-size:7px}.cc-progress{height:3px;margin-top:6px;overflow:hidden;border-radius:99px;background:#edf0f4}.cc-progress i{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#315ca7,#56aa9b)}.cc-ranking-value,.cc-branch-value{text-align:right}.cc-ranking-value strong,.cc-ranking-value small,.cc-branch-value strong,.cc-branch-value small{display:block}.cc-ranking-value strong,.cc-branch-value strong{color:#34465f;font-size:8px}.cc-ranking-value small,.cc-branch-value small{margin-top:2px;color:#8e99a9;font-size:7px}.cc-branch-avatar{display:grid;width:30px;height:30px;place-items:center;border-radius:9px;color:#315ca7;background:#edf2fb;font-size:8px;font-weight:850}.cc-branch-avatar.is-inactive{color:#8b96a5;background:#f0f2f5}
    .cc-activity-panel { margin-top:14px; }.cc-live-badge{display:inline-flex;align-items:center;gap:5px;color:#718197;font-size:7px;font-weight:800}.cc-live-badge i{width:6px;height:6px;border-radius:50%;background:#36ac87;box-shadow:0 0 0 3px #e5f7f1}
    .cc-activity-list { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:0; min-height:118px; }.cc-activity-item{position:relative;display:grid;grid-template-columns:auto minmax(0,1fr);gap:9px;padding:17px;border-right:1px solid #edf0f4;color:inherit}.cc-activity-item:nth-child(4n){border-right:0}.cc-activity-item:nth-child(n+5){border-top:1px solid #edf0f4}.cc-activity-icon{display:grid;width:32px;height:32px;place-items:center;border-radius:9px;color:#315ca7;background:#edf2fb;font-size:15px}.cc-activity-item.is-success .cc-activity-icon{color:#16866b;background:#eaf8f3}.cc-activity-copy{min-width:0}.cc-activity-copy strong,.cc-activity-copy span,.cc-activity-copy small{display:block}.cc-activity-copy strong{overflow:hidden;color:#35465f;font-size:8px;text-overflow:ellipsis;white-space:nowrap}.cc-activity-copy span{overflow:hidden;margin-top:2px;color:#8995a6;font-size:7px;text-overflow:ellipsis;white-space:nowrap}.cc-activity-copy small{margin-top:5px;color:#718096;font-size:7px}.cc-activity-copy small b{color:#315ca7}.cc-loading-overlay{position:relative}.cc-loading-overlay::before{position:absolute;z-index:20;inset:0;content:"";border-radius:18px;background:rgba(246,248,251,.72);backdrop-filter:blur(2px)}

    /* Dashboard text is intentionally kept at a readable 10–14px minimum. */
    .command-center { font-size:13px; -webkit-font-smoothing:antialiased; }
    .cc-eyebrow { font-size:11px; }
    .cc-hero-copy > p { font-size:14px; line-height:1.6; }
    .cc-live-line, .cc-live-line strong { font-size:11px; }
    .cc-filter-topline small { font-size:10px; }
    .cc-filter-topline strong { font-size:14px; }
    .cc-refresh-button { min-height:38px; font-size:11px; }
    .cc-field > span, .cc-custom-range label > span { font-size:10px; }
    .cc-field .form-select { min-height:42px; font-size:12px; }
    .cc-period-tabs button { min-height:34px; font-size:10px; }
    .cc-custom-range input, .cc-custom-range button { min-height:38px; font-size:11px; }
    .cc-filter-caption { font-size:10px; }
    .cc-system-message { font-size:12px; }
    .cc-empty-scope strong { font-size:13px; }
    .cc-empty-scope span { font-size:11px; }
    .cc-section-heading span { font-size:10px; }
    .cc-section-heading h2 { font-size:18px; }
    .cc-section-heading p { font-size:11px; line-height:1.5; }
    .cc-alert-total { font-size:10px; }
    .cc-alert-total b { font-size:12px; }
    .cc-kpi-card { min-height:158px; }
    .cc-kpi-change { font-size:10px; }
    .cc-kpi-card > small { font-size:11px; }
    .cc-kpi-value { font-size:clamp(20px,1.65vw,27px); }
    .cc-kpi-meta { font-size:10px; line-height:1.4; }
    .cc-action-card { min-height:96px; }
    .cc-action-copy strong { font-size:12px; }
    .cc-action-copy span { font-size:10px; line-height:1.45; }
    .cc-action-count { font-size:14px; }
    .cc-panel-header { min-height:74px; }
    .cc-panel-title small { font-size:10px; }
    .cc-panel-title h3 { font-size:14px; }
    .cc-panel-link { font-size:10px; }
    .cc-panel-badge { font-size:10px; }
    .cc-chart-legend { font-size:10px; }
    .cc-alert-tabs button, .cc-alert-tabs b { font-size:10px; }
    .cc-alert-item-icon { width:36px; height:36px; font-size:18px; }
    .cc-alert-copy strong { font-size:11px; }
    .cc-alert-copy span { font-size:10px; line-height:1.45; }
    .cc-alert-copy small { font-size:10px; }
    .cc-empty-block strong { font-size:12px; }
    .cc-empty-block span { font-size:10px; }
    .cc-inventory-row span, .cc-payment-row span { font-size:11px; }
    .cc-inventory-row b, .cc-payment-row b { font-size:11px; }
    .cc-payment-row small { margin-top:2px; font-size:10px; }
    .cc-rank, .cc-branch-avatar { font-size:10px; }
    .cc-ranking-copy strong, .cc-branch-copy strong { font-size:11px; }
    .cc-ranking-copy small, .cc-branch-copy small { font-size:10px; }
    .cc-ranking-value strong, .cc-branch-value strong { font-size:10px; }
    .cc-ranking-value small, .cc-branch-value small { font-size:10px; }
    .cc-live-badge { font-size:10px; }
    .cc-activity-copy strong { font-size:11px; }
    .cc-activity-copy span { font-size:10px; }
    .cc-activity-copy small { font-size:10px; }

    .cc-analysis-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .cc-kpi-grid > .cc-empty-block, .cc-action-grid > .cc-empty-block, .cc-analysis-grid > .cc-empty-block { grid-column:1/-1; border:1px dashed var(--cc-line); border-radius:16px; background:#fff; }
    .cc-analysis-card { --analysis:#315ca7; --analysis-soft:#edf2fb; display:grid; min-width:0; min-height:132px; grid-template-columns:auto minmax(0,1fr); align-items:start; gap:12px; padding:17px; border:1px solid var(--cc-line); border-radius:16px; background:#fff; box-shadow:0 7px 20px rgba(27,45,75,.045); }
    .cc-analysis-card.is-success{--analysis:#16866b;--analysis-soft:#eaf8f3}.cc-analysis-card.is-info{--analysis:#3186bd;--analysis-soft:#edf7fc}.cc-analysis-card.is-warning{--analysis:#c98528;--analysis-soft:#fff6e8}.cc-analysis-card.is-danger{--analysis:#d64c60;--analysis-soft:#fff0f2}.cc-analysis-card.is-violet{--analysis:#7559bb;--analysis-soft:#f1edfb}
    .cc-analysis-icon { display:grid; width:40px; height:40px; place-items:center; border-radius:11px; color:var(--analysis); background:var(--analysis-soft); font-size:20px; }
    .cc-analysis-copy { min-width:0; }
    .cc-analysis-copy small, .cc-analysis-copy strong, .cc-analysis-copy span { display:block; }
    .cc-analysis-copy small { color:#7d899b; font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .cc-analysis-copy strong { margin-top:4px; color:#2b3c58; font-size:18px; font-weight:800; letter-spacing:-.02em; }
    .cc-analysis-copy span { margin-top:5px; color:#7f8b9d; font-size:10px; line-height:1.5; }

    @media (max-width:1199.98px){.cc-hero{grid-template-columns:1fr}.cc-kpi-grid,.cc-action-grid,.cc-analysis-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.cc-main-grid{grid-template-columns:1fr}.cc-alert-panel{min-height:360px}.cc-activity-list{grid-template-columns:repeat(2,minmax(0,1fr))}.cc-activity-item:nth-child(2n){border-right:0}.cc-activity-item:nth-child(n+3){border-top:1px solid #edf0f4}}
    @media (max-width:767.98px){.cc-hero{margin:0 0 19px;padding:22px 17px;border-radius:17px}.cc-filter-grid{grid-template-columns:1fr}.cc-period-tabs{grid-template-columns:repeat(3,1fr)}.cc-custom-range{grid-template-columns:1fr 1fr}.cc-custom-range>i{display:none}.cc-custom-range button{grid-column:1/-1}.cc-section-heading{align-items:start;flex-direction:column;gap:5px}.cc-section-heading p{text-align:left}.cc-kpi-grid,.cc-action-grid,.cc-analysis-grid,.cc-insight-grid{grid-template-columns:1fr}.cc-main-grid{display:block}.cc-alert-panel{margin-top:14px}.cc-inventory-layout,.cc-payment-layout{grid-template-columns:1fr}.cc-donut-chart{min-height:190px}.cc-activity-list{grid-template-columns:1fr}.cc-activity-item{border-right:0}.cc-activity-item:nth-child(n+2){border-top:1px solid #edf0f4}.cc-chart{min-height:285px}.cc-chart-legend{display:none}}
    @media (prefers-reduced-motion:reduce){.cc-live-dot{animation:none}.cc-kpi-card,.cc-action-card,.cc-alert-arrow{transition:none}}
</style>
