<style>
    .patient-page { --patient-blue:#2563eb; --patient-navy:#12213a; --patient-mint:#0f9f7f; color:#253450; }
    .patient-page .breadcrumb { margin-bottom:1rem; font-size:.78rem; }
    .patient-hero { position:relative; display:grid; grid-template-columns:minmax(0,1.45fr) minmax(310px,.55fr); gap:2rem; overflow:hidden; margin-bottom:1.2rem; padding:clamp(1.5rem,3vw,2.5rem); border-radius:24px; color:#fff; background:radial-gradient(circle at 86% 10%,rgba(45,212,191,.28),transparent 28%),linear-gradient(125deg,#12336c 0%,#1f66c5 58%,#0d9d8c 100%); box-shadow:0 20px 42px rgba(24,76,149,.18); }
    .patient-hero::after { position:absolute; right:-70px; bottom:-110px; width:310px; height:310px; border:42px solid rgba(255,255,255,.07); border-radius:50%; content:""; }
    .patient-hero-copy,.patient-hero-visual { position:relative; z-index:1; }
    .patient-kicker { display:inline-flex; align-items:center; gap:.4rem; margin-bottom:.8rem; padding:.38rem .7rem; border:1px solid rgba(255,255,255,.24); border-radius:999px; background:rgba(255,255,255,.1); font-size:.68rem; font-weight:800; letter-spacing:.09em; text-transform:uppercase; }
    .patient-hero h1 { margin:0 0 .55rem; font-size:clamp(1.75rem,3vw,2.65rem); font-weight:900; letter-spacing:-.035em; }
    .patient-hero p { max-width:720px; margin:0; color:rgba(255,255,255,.79); font-size:.91rem; line-height:1.7; }
    .patient-hero-actions { display:flex; flex-wrap:wrap; gap:.65rem; margin-top:1.25rem; }
    .patient-hero-actions .btn { border-radius:10px; font-weight:800; }
    .patient-hero-visual { display:grid; align-content:center; gap:.7rem; }
    .patient-hero-icon { display:grid; width:64px; height:64px; place-items:center; margin-bottom:.15rem; border:1px solid rgba(255,255,255,.28); border-radius:19px; background:rgba(255,255,255,.14); box-shadow:inset 0 1px 0 rgba(255,255,255,.2); font-size:2rem; }
    .patient-hero-visual div { display:flex; flex-direction:column; padding:.72rem .9rem; border:1px solid rgba(255,255,255,.16); border-radius:12px; background:rgba(11,38,79,.2); backdrop-filter:blur(8px); }
    .patient-hero-visual strong { font-size:.78rem; }
    .patient-hero-visual small { margin-top:.18rem; color:rgba(255,255,255,.68); font-size:.68rem; }
    .patient-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; margin-bottom:1.2rem; }
    .patient-stats article { display:flex; align-items:center; gap:.9rem; min-height:112px; padding:1.15rem; border:1px solid #e0e9f3; border-radius:18px; background:#fff; box-shadow:0 10px 28px rgba(24,52,89,.055); }
    .patient-stats article>span { display:grid; width:48px; height:48px; flex:0 0 48px; place-items:center; border-radius:14px; color:#2368c4; background:#eaf3ff; font-size:1.4rem; }
    .patient-stats .is-filtered>span { color:#0b947a; background:#e5faf4; }
    .patient-stats .is-branch>span { color:#7c55c7; background:#f1ebff; }
    .patient-stats div { display:grid; grid-template-columns:auto 1fr; align-items:end; column-gap:.55rem; }
    .patient-stats small { grid-column:1/-1; color:#7f8ba0; font-size:.68rem; font-weight:800; letter-spacing:.055em; text-transform:uppercase; }
    .patient-stats strong { color:#172c4a; font-size:1.65rem; line-height:1; }
    .patient-stats p { margin:0 0 .1rem; color:#8793a5; font-size:.68rem; }
    .patient-visit-panel { position:relative; overflow:hidden; margin-bottom:1.2rem; border:1px solid #dee7f1; border-radius:20px; background:#fff; box-shadow:0 14px 35px rgba(25,51,87,.06); }
    .patient-visit-panel.is-loading::after { position:absolute; inset:0; z-index:5; display:grid; place-items:center; color:#2563eb; background:rgba(255,255,255,.58); backdrop-filter:blur(1px); content:"Memuat data kunjungan..."; font-size:.72rem; font-weight:800; }
    .patient-visit-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.2rem 1.35rem; border-bottom:1px solid #e7edf4; }
    .patient-visit-kicker { display:inline-flex; align-items:center; gap:.35rem; margin-bottom:.35rem; color:#2563eb; font-size:.62rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .patient-visit-heading h2 { margin:0; color:#172c4a; font-size:1.05rem; font-weight:900; }
    .patient-visit-heading p { margin:.24rem 0 0; color:#7b899e; font-size:.7rem; }
    .patient-period-tabs { display:inline-flex; padding:4px; border:1px solid #dce6f1; border-radius:11px; background:#f5f8fc; }
    .patient-period-tabs button { min-width:72px; padding:.45rem .7rem; border:0; border-radius:8px; color:#718098; background:transparent; font-size:.68rem; font-weight:800; transition:.18s; }
    .patient-period-tabs button.is-active { color:#fff; background:#2563eb; box-shadow:0 5px 12px rgba(37,99,235,.2); }
    .patient-visit-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.8rem; padding:1rem 1.35rem 0; }
    .patient-visit-summary article { display:flex; align-items:center; gap:.72rem; padding:.85rem; border:1px solid #e2eaf3; border-radius:14px; background:#f9fbfe; }
    .patient-visit-summary article>span { display:grid; width:40px; height:40px; flex:0 0 40px; place-items:center; border-radius:12px; color:#2563eb; background:#e8f1ff; font-size:1.15rem; }
    .patient-visit-summary .is-unique>span { color:#0b947a; background:#e2f8f2; }
    .patient-visit-summary .is-repeat>span { color:#7c55c7; background:#f0eaff; }
    .patient-visit-summary article div { display:grid; grid-template-columns:auto 1fr; align-items:end; gap:.1rem .45rem; }
    .patient-visit-summary small { grid-column:1/-1; color:#7e8ba0; font-size:.61rem; font-weight:900; letter-spacing:.05em; text-transform:uppercase; }
    .patient-visit-summary strong { color:#172c4a; font-size:1.35rem; line-height:1; }
    .patient-visit-summary p { margin:0 0 .05rem; color:#8793a5; font-size:.62rem; }
    .patient-visit-content { display:grid; grid-template-columns:minmax(0,1.65fr) minmax(300px,.85fr); gap:1rem; padding:1rem 1.35rem 1.3rem; }
    .patient-visit-chart-shell,.patient-top-visitors { min-width:0; padding:1rem; border:1px solid #e4ebf3; border-radius:15px; }
    .patient-subheading { display:flex; flex-direction:column; gap:.15rem; margin-bottom:.45rem; }
    .patient-subheading strong { color:#263a56; font-size:.76rem; }
    .patient-subheading small { color:#8b97a9; font-size:.62rem; }
    .patient-visit-chart { min-height:285px; }
    .patient-top-item { display:grid; grid-template-columns:22px 36px minmax(0,1fr) auto; align-items:center; gap:.55rem; padding:.68rem 0; border-bottom:1px solid #edf1f6; }
    .patient-top-item:last-child { border-bottom:0; }
    .patient-top-rank { display:grid; width:22px; height:22px; place-items:center; border-radius:7px; color:#687891; background:#edf2f8; font-size:.62rem; font-weight:900; }
    .patient-top-item:first-child .patient-top-rank { color:#9a6900; background:#fff1c2; }
    .patient-top-avatar { display:grid; width:36px; height:36px; place-items:center; border-radius:11px; color:#fff; background:linear-gradient(145deg,#3478d4,#17a58e); font-size:.68rem; font-weight:900; }
    .patient-top-copy { display:flex; min-width:0; flex-direction:column; gap:.13rem; }
    .patient-top-copy strong { overflow:hidden; color:#2a3d58; font-size:.7rem; text-overflow:ellipsis; white-space:nowrap; }
    .patient-top-copy small { overflow:hidden; color:#8a97a9; font-size:.58rem; text-overflow:ellipsis; white-space:nowrap; }
    .patient-top-item>b { display:flex; flex-direction:column; color:#2563eb; font-size:.82rem; text-align:right; }
    .patient-top-item>b small { color:#8b97aa; font-size:.54rem; font-weight:700; }
    .patient-visit-empty { display:flex; min-height:160px; align-items:center; justify-content:center; flex-direction:column; gap:.45rem; color:#8a97aa; font-size:.68rem; text-align:center; }
    .patient-visit-empty i { color:#b1bdcc; font-size:1.7rem; }
    .patient-visit-error { margin:0 1.35rem 1.2rem; padding:.7rem .85rem; border:1px solid #f2c9cf; border-radius:10px; color:#b32f43; background:#fff2f4; font-size:.7rem; }
    .patient-panel { overflow:hidden; border:1px solid #dee7f1; border-radius:20px; background:#fff; box-shadow:0 14px 35px rgba(25,51,87,.06); }
    .patient-panel-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.25rem 1.35rem; border-bottom:1px solid #e7edf4; }
    .patient-panel-heading h2 { margin:0; color:#172c4a; font-size:1.05rem; font-weight:900; }
    .patient-panel-heading p { margin:.25rem 0 0; color:#7b899e; font-size:.74rem; }
    .patient-panel-heading .btn { border-radius:10px; font-size:.78rem; font-weight:800; }
    .patient-toolbar { display:grid; grid-template-columns:minmax(240px,1fr) minmax(180px,240px) auto; gap:.7rem; padding:1rem 1.35rem; background:#f8fbff; }
    .patient-search { position:relative; margin:0; }
    .patient-search i { position:absolute; top:50%; left:.85rem; color:#7f91a9; font-size:1.05rem; transform:translateY(-50%); }
    .patient-search input { width:100%; height:40px; padding:0 .9rem 0 2.45rem; border:1px solid #d9e3ee; border-radius:10px; outline:0; background:#fff; font-size:.78rem; transition:.18s; }
    .patient-search input:focus { border-color:#8db7ef; box-shadow:0 0 0 3px rgba(37,99,235,.09); }
    .patient-toolbar .form-select,.patient-toolbar .btn { min-height:40px; border-radius:10px; font-size:.75rem; }
    .patient-table { width:100%!important; margin:0!important; }
    .patient-table thead th { padding:.82rem .9rem; border-bottom:1px solid #dfe7ef!important; color:#78879b; background:#fff; font-size:.63rem; font-weight:900; letter-spacing:.055em; text-transform:uppercase; white-space:nowrap; }
    .patient-table tbody td { padding:.82rem .9rem; border-color:#edf1f6; font-size:.75rem; vertical-align:middle; }
    .patient-rm { display:inline-flex; padding:.34rem .55rem; border:1px solid #cfe0f5; border-radius:8px; color:#245f9e; background:#f0f6fd; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:.68rem; font-weight:800; white-space:nowrap; }
    .patient-identity { display:flex; min-width:190px; align-items:center; gap:.65rem; }
    .patient-avatar { display:grid; width:36px; height:36px; flex:0 0 36px; place-items:center; border-radius:11px; color:#fff; background:linear-gradient(145deg,#3478d4,#17a58e); font-weight:900; }
    .patient-identity div,.patient-contact { display:flex; min-width:0; flex-direction:column; gap:.14rem; }
    .patient-identity strong { overflow:hidden; color:#203553; font-size:.78rem; text-overflow:ellipsis; white-space:nowrap; }
    .patient-identity small,.patient-profile small,.patient-contact small { color:#8a97a9; font-size:.65rem; }
    .patient-profile { display:flex; min-width:135px; flex-direction:column; gap:.18rem; }
    .patient-profile strong,.patient-contact strong { color:#435471; font-size:.72rem; }
    .patient-branch-pill,.patient-status-pill { display:inline-flex; align-items:center; gap:.3rem; padding:.34rem .55rem; border-radius:999px; font-size:.65rem; font-weight:800; white-space:nowrap; }
    .patient-branch-pill { color:#5c4b94; background:#f1edfc; }
    .patient-status-pill.is-active { color:#08775f; background:#def7ee; }
    .patient-status-pill.is-inactive { color:#8a5960; background:#f5e9eb; }
    .patient-actions { display:flex; justify-content:flex-end; gap:.3rem; }
    .patient-action { display:grid; width:31px; height:31px; padding:0; place-items:center; border:1px solid #dce6f0; border-radius:9px; color:#63758d; background:#fff; }
    .patient-action:hover { color:#2563eb; border-color:#bfd5f3; background:#eff6ff; }
    .patient-action.is-delete:hover { color:#dc3e55; border-color:#f0c4cb; background:#fff1f3; }
    .patient-panel .dataTables_info,.patient-panel .dataTables_paginate { padding:1rem 1.35rem!important; color:#7c8a9e; font-size:.7rem; }
    .patient-panel .dataTables_empty { padding:3rem!important; color:#8a97a9; }
    .patient-modal .modal-content,.patient-detail-modal .modal-content { overflow:hidden; border:0; border-radius:20px; box-shadow:0 30px 80px rgba(11,31,59,.25); }
    .patient-modal .modal-header,.patient-detail-modal .modal-header { padding:1.2rem 1.4rem; border:0; color:#fff; background:linear-gradient(120deg,#173b73,#246bc8 62%,#119984); }
    .patient-modal-title,.patient-detail-title { display:flex; align-items:center; gap:.8rem; }
    .patient-modal-title>span,.patient-detail-title>span { display:grid; width:46px; height:46px; flex:0 0 46px; place-items:center; border:1px solid rgba(255,255,255,.24); border-radius:14px; background:rgba(255,255,255,.12); font-size:1.35rem; font-weight:900; }
    .patient-modal-title small,.patient-detail-title small { display:block; margin-bottom:.15rem; color:rgba(255,255,255,.68); font-size:.62rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .patient-modal-title h5,.patient-detail-title h5 { margin:0; font-size:1.05rem; font-weight:900; }
    .patient-modal-title p,.patient-detail-title p { margin:.18rem 0 0; color:rgba(255,255,255,.72); font-size:.68rem; }
    .patient-modal .modal-body { padding:1.3rem; background:#f7fafe; }
    .patient-form-section { margin-bottom:1rem; padding:1.2rem; border:1px solid #e1e9f2; border-radius:16px; background:#fff; }
    .patient-form-section:last-child { margin-bottom:0; }
    .patient-form-section.is-clinical { border-color:#d6eee8; background:linear-gradient(135deg,#fff,#f6fffc); }
    .patient-section-heading { display:flex; align-items:center; gap:.7rem; margin-bottom:1rem; }
    .patient-section-heading>span { display:grid; width:34px; height:34px; place-items:center; border-radius:10px; color:#2c68ae; background:#e9f2fe; font-size:.67rem; font-weight:900; }
    .patient-section-heading h6 { margin:0; color:#213752; font-size:.8rem; font-weight:900; }
    .patient-section-heading p { margin:.14rem 0 0; color:#8996a8; font-size:.65rem; }
    .patient-form-section .form-label { margin-bottom:.38rem; color:#506079; font-size:.7rem; font-weight:800; }
    .patient-form-section .form-label b,.patient-required-note b { color:#dc4259; }
    .patient-form-section .form-control,.patient-form-section .form-select { min-height:42px; border-color:#dbe4ee; border-radius:10px; font-size:.77rem; }
    .patient-form-section textarea.form-control { min-height:82px; }
    .patient-form-section .form-control:focus,.patient-form-section .form-select:focus { border-color:#80abe7; box-shadow:0 0 0 3px rgba(37,99,235,.08); }
    .patient-modal .modal-footer,.patient-detail-modal .modal-footer { padding:.9rem 1.4rem; border-top:1px solid #e7edf4; background:#fff; }
    .patient-modal .modal-footer { display:flex; }
    .patient-required-note { margin-right:auto; color:#8693a5; font-size:.68rem; }
    .patient-modal .modal-footer .btn,.patient-detail-modal .modal-footer .btn { min-width:105px; border-radius:10px; font-size:.75rem; font-weight:800; }
    .patient-detail-modal .modal-body { padding:1.35rem; background:#f7fafe; }
    .patient-detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; }
    .patient-detail-item { padding:.85rem; border:1px solid #e0e8f1; border-radius:12px; background:#fff; }
    .patient-detail-item.is-wide { grid-column:1/-1; }
    .patient-detail-item small { display:block; margin-bottom:.28rem; color:#8a97aa; font-size:.61rem; font-weight:900; letter-spacing:.055em; text-transform:uppercase; }
    .patient-detail-item strong { display:block; color:#283c59; font-size:.77rem; line-height:1.55; white-space:pre-line; }
    @media (max-width:991.98px) { .patient-hero { grid-template-columns:1fr; } .patient-hero-visual { display:none; } .patient-toolbar { grid-template-columns:1fr 1fr; } .patient-visit-content { grid-template-columns:1fr; } }
    @media (max-width:767.98px) { .patient-stats,.patient-visit-summary { grid-template-columns:1fr; } .patient-stats article { min-height:94px; } .patient-visit-heading { align-items:flex-start; flex-direction:column; } .patient-period-tabs { width:100%; } .patient-period-tabs button { flex:1; } .patient-panel-heading { align-items:flex-start; } .patient-toolbar { grid-template-columns:1fr; } .patient-detail-grid { grid-template-columns:1fr; } .patient-detail-item.is-wide { grid-column:auto; } }
    @media (max-width:575.98px) { .patient-hero { padding:1.3rem; border-radius:18px; } .patient-panel-heading { flex-direction:column; } .patient-panel-heading .btn { width:100%; } .patient-modal .modal-dialog,.patient-detail-modal .modal-dialog { margin:.5rem; } }
</style>
