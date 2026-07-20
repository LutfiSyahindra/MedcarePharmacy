<style>
    .apotek-profile-page { --apotek-ink:#17233c; --apotek-muted:#728099; --apotek-line:#e7ebf3; --apotek-blue:#4258e8; --apotek-soft:#f5f7fb; color:var(--apotek-ink); }
    .apotek-breadcrumb { margin-bottom:18px; }
    .apotek-breadcrumb .breadcrumb { font-size:13px; }
    .apotek-breadcrumb a { color:var(--apotek-blue); text-decoration:none; }
    .apotek-hero { position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between; gap:24px; min-height:176px; margin-bottom:22px; padding:30px 34px; border-radius:22px; color:#fff; background:linear-gradient(118deg,#243a9b 0%,#4258e8 51%,#7884f4 100%); box-shadow:0 20px 42px rgba(48,67,176,.18); }
    .apotek-hero::before { content:""; position:absolute; width:320px; height:320px; right:-90px; top:-190px; border:56px solid rgba(255,255,255,.08); border-radius:50%; }
    .apotek-hero::after { content:""; position:absolute; width:170px; height:170px; right:200px; bottom:-132px; border:34px solid rgba(255,255,255,.06); border-radius:50%; }
    .apotek-hero-copy,.apotek-branch-badge { position:relative; z-index:1; }
    .apotek-kicker { display:inline-flex; align-items:center; gap:7px; margin-bottom:9px; padding:6px 10px; border:1px solid rgba(255,255,255,.22); border-radius:999px; background:rgba(255,255,255,.1); font-size:11px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; }
    .apotek-kicker svg { width:14px; height:14px; }
    .apotek-hero h1 { margin:0 0 7px; font-size:31px; line-height:1.15; font-weight:800; letter-spacing:-.03em; }
    .apotek-hero p { max-width:650px; margin:0; color:rgba(255,255,255,.78); font-size:14px; }
    .apotek-branch-badge { display:flex; align-items:center; gap:12px; min-width:218px; padding:14px 16px; border:1px solid rgba(255,255,255,.2); border-radius:15px; background:rgba(19,28,93,.28); backdrop-filter:blur(8px); }
    .apotek-branch-badge span:last-child { display:grid; gap:2px; }
    .apotek-branch-badge small { color:rgba(255,255,255,.62); font-size:10px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .apotek-branch-badge strong { font-size:13px; }
    .apotek-status-dot { width:11px; height:11px; border:3px solid rgba(255,255,255,.25); border-radius:50%; background:#a6adc5; box-sizing:content-box; }
    .apotek-status-dot.is-active { background:#55e6ad; box-shadow:0 0 0 4px rgba(85,230,173,.12); }
    .apotek-alert { display:flex; align-items:center; gap:12px; border:0; border-radius:14px; padding:14px 16px; box-shadow:0 8px 20px rgba(20,32,72,.05); }
    .apotek-alert > svg { width:22px; height:22px; flex:0 0 auto; }
    .apotek-alert > div { display:grid; flex:1; }
    .apotek-alert strong { font-size:13px; }
    .apotek-alert span { font-size:12px; opacity:.8; }
    .apotek-alert-success { color:#176b50; background:#eafaf4; }
    .apotek-alert-danger { color:#a63b4b; background:#fff0f2; }
    .apotek-toolbar { display:flex; align-items:flex-end; gap:18px; margin-bottom:18px; padding:15px 18px; border:1px solid var(--apotek-line); border-radius:16px; background:#fff; box-shadow:0 8px 30px rgba(26,43,84,.05); }
    .apotek-branch-selector { display:grid; gap:7px; width:min(410px,100%); margin:0; }
    .apotek-branch-selector > span { display:flex; align-items:center; gap:7px; color:#53617b; font-size:11px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .apotek-branch-selector svg { width:14px; height:14px; color:var(--apotek-blue); }
    .apotek-toolbar-note { display:flex; align-items:center; gap:8px; flex:1; padding-bottom:10px; color:var(--apotek-muted); font-size:12px; }
    .apotek-toolbar-note svg { width:16px; height:16px; color:#8b97ae; }
    .apotek-save-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:0 18px; border:0; border-radius:11px; color:#fff; background:linear-gradient(135deg,#4258e8,#6072ee); font-size:13px; font-weight:700; box-shadow:0 8px 18px rgba(66,88,232,.22); }
    .apotek-save-button:hover { color:#fff; transform:translateY(-1px); box-shadow:0 11px 24px rgba(66,88,232,.28); }
    .apotek-save-button svg { width:17px; height:17px; }
    .apotek-layout { display:grid; grid-template-columns:minmax(245px,285px) minmax(0,1fr); align-items:start; gap:20px; }
    .apotek-side-column,.apotek-main-column { display:grid; gap:20px; }
    .apotek-card { padding:24px; border:1px solid var(--apotek-line); border-radius:18px; background:#fff; box-shadow:0 8px 30px rgba(26,43,84,.045); }
    .apotek-card-heading { display:flex; align-items:center; gap:12px; margin-bottom:21px; }
    .apotek-card-heading.compact { align-items:flex-start; }
    .apotek-card-heading h2 { margin:0 0 3px; color:var(--apotek-ink); font-size:15px; font-weight:800; }
    .apotek-card-heading p { margin:0; color:var(--apotek-muted); font-size:11px; }
    .apotek-section-icon { display:grid; place-items:center; width:39px; height:39px; flex:0 0 39px; border-radius:12px; color:#4258e8; background:#edf0ff; }
    .apotek-section-icon svg { width:18px; height:18px; }
    .apotek-section-icon.coral { color:#e36c57; background:#fff0ec; }
    .apotek-section-icon.amber { color:#c98518; background:#fff7df; }
    .apotek-section-icon.mint { color:#15966d; background:#e7f8f2; }
    .apotek-section-icon.violet { color:#8b5fc0; background:#f4edfc; }
    .apotek-card .form-label { margin-bottom:7px; color:#43506a; font-size:11px; font-weight:700; }
    .apotek-card .form-label em { color:#e55766; font-style:normal; }
    .apotek-card .form-control,.apotek-toolbar .form-select { min-height:42px; border-color:#dde3ed; border-radius:10px; color:#273550; font-size:13px; background-color:#fff; }
    .apotek-card textarea.form-control { min-height:auto; padding-top:11px; }
    .apotek-card .form-control:focus,.apotek-toolbar .form-select:focus { border-color:#8794ee; box-shadow:0 0 0 3px rgba(66,88,232,.09); }
    .apotek-input-icon { position:relative; }
    .apotek-input-icon > svg { position:absolute; z-index:2; width:15px; height:15px; left:13px; top:14px; color:#98a4b8; pointer-events:none; }
    .apotek-input-icon .form-control { padding-left:39px; }
    .apotek-field-error { margin-top:5px; color:#dc4457; font-size:11px; }
    .apotek-logo-card { text-align:center; }
    .apotek-logo-card .apotek-card-heading { text-align:left; }
    .apotek-logo-preview { display:grid; place-items:center; width:160px; height:160px; margin:3px auto 18px; overflow:hidden; border:1px dashed #ccd4e3; border-radius:30px; background:linear-gradient(145deg,#f8f9fd,#edf1f8); }
    .apotek-logo-preview img { width:100%; height:100%; object-fit:contain; padding:13px; }
    .apotek-logo-placeholder { display:grid; place-items:center; color:#7c8ba6; }
    .apotek-logo-placeholder span { display:grid; place-items:center; width:62px; height:62px; border-radius:20px; color:#fff; background:linear-gradient(145deg,#4358dc,#7a87f6); font-size:29px; font-weight:800; box-shadow:0 12px 24px rgba(66,88,232,.24); }
    .apotek-logo-placeholder small { margin-top:10px; font-size:9px; font-weight:800; letter-spacing:.22em; }
    .apotek-upload-button { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; min-height:41px; border:1px solid #d7dded; border-radius:10px; color:#3948b2; background:#f8f9ff; font-size:12px; font-weight:700; cursor:pointer; }
    .apotek-upload-button:hover { border-color:#98a4ef; background:#f1f3ff; }
    .apotek-upload-button svg,.apotek-remove-logo svg { width:15px; height:15px; }
    .apotek-file-help { margin:10px 0 0; color:#8a96aa; font-size:10px; line-height:1.45; }
    .apotek-remove-logo { display:inline-flex; align-items:center; gap:6px; margin-top:11px; padding:0; border:0; color:#c55262; background:transparent; font-size:11px; }
    .apotek-completeness-card { background:linear-gradient(180deg,#fff,#fbfcff); }
    .apotek-completion-score { display:flex; justify-content:space-between; align-items:end; margin-bottom:9px; }
    .apotek-completion-score strong { color:#2d3cb0; font-size:24px; line-height:1; }
    .apotek-completion-score span { color:#8b96aa; font-size:10px; }
    .apotek-progress { height:6px; border-radius:999px; background:#e8ecf4; }
    .apotek-progress .progress-bar { border-radius:999px; background:linear-gradient(90deg,#5266e8,#6dd8bb); }
    .apotek-check-list { display:grid; gap:10px; margin:17px 0 0; padding:0; list-style:none; }
    .apotek-check-list li { display:flex; align-items:center; gap:9px; color:#8c98aa; font-size:11px; }
    .apotek-check-list li svg { width:15px; height:15px; }
    .apotek-check-list li.is-complete { color:#278967; }
    .apotek-map-panel { margin-top:20px; overflow:visible; border:1px solid #dde4ed; border-radius:15px; background:#f7f9fc; }
    .apotek-map-toolbar { display:flex; gap:10px; padding:11px; border-bottom:1px solid #e3e8f0; border-radius:14px 14px 0 0; background:#fff; }
    .apotek-map-search { display:flex; align-items:center; flex:1; overflow:hidden; border:1px solid #dce3ed; border-radius:10px; background:#fff; }
    .apotek-map-search > svg { width:16px; height:16px; margin-left:12px; color:#92a0b5; }
    .apotek-map-search input { min-width:0; flex:1; height:38px; padding:0 10px; border:0; outline:0; color:#33415b; font-size:12px; }
    .apotek-map-search button { align-self:stretch; padding:0 15px; border:0; color:#fff; background:#3348c3; font-size:11px; font-weight:700; }
    .apotek-location-button { display:flex; align-items:center; gap:7px; padding:0 13px; border:1px solid #dce3ed; border-radius:10px; color:#3d4b66; background:#fff; font-size:11px; font-weight:700; }
    .apotek-location-button svg { width:15px; height:15px; color:#4258e8; }
    #apotekLocationMap { position:relative; z-index:0; height:380px; overflow:hidden; isolation:isolate; contain:layout paint; background:#e7ebf1; }
    #apotekLocationMap .leaflet-map-pane,#apotekLocationMap .leaflet-tile-pane { backface-visibility:hidden; }
    #apotekLocationMap .leaflet-tile { image-rendering:auto; }
    .apotek-map-footer { display:flex; justify-content:space-between; align-items:center; gap:12px; min-height:45px; padding:0 13px; border-top:1px solid #e3e8f0; border-radius:0 0 14px 14px; background:#fff; color:#77849a; font-size:10px; }
    .apotek-map-footer span,.apotek-map-footer a { display:flex; align-items:center; gap:6px; }
    .apotek-map-footer svg { width:13px; height:13px; }
    .apotek-map-footer a { color:#4258e8; font-weight:700; text-decoration:none; }
    .apotek-coordinate-row { margin-top:4px; }
    .apotek-hours-list { display:grid; gap:8px; }
    .apotek-hours-row { display:grid; grid-template-columns:minmax(120px,1fr) auto 70px; align-items:center; gap:16px; min-height:62px; padding:9px 14px; border:1px solid #e8ebf1; border-radius:12px; background:#fafbfd; transition:.2s ease; }
    .apotek-hours-row.is-open { border-color:#dce5f5; background:#fff; }
    .apotek-day-toggle { display:flex; align-items:center; gap:11px; margin:0; cursor:pointer; }
    .apotek-day-toggle input { position:absolute; opacity:0; pointer-events:none; }
    .apotek-toggle-track { position:relative; width:34px; height:19px; flex:0 0 34px; border-radius:999px; background:#cdd4df; transition:.2s ease; }
    .apotek-toggle-track span { position:absolute; width:13px; height:13px; left:3px; top:3px; border-radius:50%; background:#fff; box-shadow:0 2px 5px rgba(30,42,68,.25); transition:.2s ease; }
    .apotek-day-toggle input:checked + .apotek-toggle-track { background:#4d63e6; }
    .apotek-day-toggle input:checked + .apotek-toggle-track span { transform:translateX(15px); }
    .apotek-day-toggle strong { font-size:12px; }
    .apotek-hours-fields { display:flex; align-items:center; gap:8px; }
    .apotek-hours-fields label { display:flex; align-items:center; gap:7px; margin:0; }
    .apotek-hours-fields label span { color:#8792a5; font-size:9px; font-weight:700; text-transform:uppercase; }
    .apotek-hours-fields input { width:105px; height:37px; padding:0 9px; border:1px solid #dfe4ec; border-radius:8px; color:#33405a; background:#fff; font-size:11px; }
    .apotek-hours-row:not(.is-open) .apotek-hours-fields { opacity:.45; pointer-events:none; }
    .apotek-hours-separator { color:#a6afbd; }
    .apotek-day-status { justify-self:end; padding:5px 9px; border-radius:999px; color:#9a5360; background:#fff0f2; font-size:9px; font-weight:800; text-transform:uppercase; }
    .apotek-hours-row.is-open .apotek-day-status { color:#208261; background:#e8f8f2; }
    .apotek-character-count { margin-top:7px; color:#939daf; font-size:10px; text-align:right; }
    .apotek-mobile-save { display:flex; align-items:center; justify-content:space-between; gap:18px; padding:17px 20px; border:1px solid #dfe4ee; border-radius:16px; background:#fff; box-shadow:0 8px 28px rgba(26,43,84,.06); }
    .apotek-mobile-save > span { display:grid; }
    .apotek-mobile-save strong { font-size:12px; }
    .apotek-mobile-save small { color:#8994a8; font-size:10px; }
    .apotek-empty-state { display:grid; place-items:center; min-height:380px; padding:50px; border:1px solid var(--apotek-line); border-radius:20px; background:#fff; text-align:center; }
    .apotek-empty-state > * { margin-block:0; }
    .apotek-empty-icon { display:grid; place-items:center; width:70px; height:70px; margin-bottom:16px; border-radius:22px; color:#5367e6; background:#eff2ff; }
    .apotek-empty-state h2 { margin-bottom:7px; font-size:18px; font-weight:800; }
    .apotek-empty-state p { color:var(--apotek-muted); font-size:12px; }
    .leaflet-control-attribution { font-size:9px; }
    @media (max-width:1050px) { .apotek-layout { grid-template-columns:1fr; } .apotek-side-column { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:780px) { .apotek-hero { align-items:flex-start; flex-direction:column; padding:25px; } .apotek-branch-badge { width:100%; } .apotek-toolbar { align-items:stretch; flex-direction:column; } .apotek-toolbar-note { padding:0; } .apotek-side-column { grid-template-columns:1fr; } .apotek-hours-row { grid-template-columns:1fr auto; gap:9px; } .apotek-hours-fields { grid-column:1/-1; justify-content:flex-start; padding-left:45px; } .apotek-day-status { grid-row:1; grid-column:2; } .apotek-map-toolbar { flex-direction:column; } .apotek-location-button { justify-content:center; min-height:38px; } }
    @media (max-width:520px) { .apotek-card { padding:19px 16px; } .apotek-hero h1 { font-size:26px; } .apotek-map-search button { padding:0 10px; } .apotek-location-button span { display:inline; } #apotekLocationMap { height:310px; } .apotek-map-footer { align-items:flex-start; flex-direction:column; padding-block:10px; } .apotek-hours-fields { padding-left:0; } .apotek-hours-fields input { width:92px; } .apotek-hours-fields label span { display:none; } .apotek-mobile-save { align-items:stretch; flex-direction:column; } }
</style>
