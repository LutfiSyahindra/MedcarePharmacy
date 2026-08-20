<style>
    .document-page {
        --document-ink: #17233c;
        --document-muted: #718096;
        --document-line: #e5eaf2;
        --document-primary: #3157d5;
        --document-narcotic: #cf405b;
        --document-psychotropic: #5267dc;
        --document-precursor: #bd7518;
        color: var(--document-ink);
    }

    .document-hero {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        min-height: 250px;
        overflow: hidden;
        margin-bottom: 18px;
        padding: clamp(28px, 4vw, 46px);
        border-radius: 22px;
        color: #fff;
        background:
            radial-gradient(circle at 75% 12%, rgba(130, 203, 255, .26), transparent 28%),
            linear-gradient(135deg, #172e73 0%, #3157d5 56%, #477ee9 100%);
        box-shadow: 0 22px 50px rgba(40, 75, 174, .22);
    }

    .document-hero::before,
    .document-hero::after {
        position: absolute;
        content: "";
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 50%;
    }

    .document-hero::before { width: 350px; height: 350px; right: -105px; top: -180px; }
    .document-hero::after { width: 240px; height: 240px; right: 118px; bottom: -190px; }
    .document-hero-copy { position: relative; z-index: 2; max-width: 760px; }

    .document-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        padding: 6px 11px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 999px;
        background: rgba(255, 255, 255, .1);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .document-hero h2 { max-width: 700px; margin: 0 0 12px; font-size: clamp(25px, 3vw, 38px); font-weight: 850; line-height: 1.18; letter-spacing: -.035em; }
    .document-hero p { max-width: 670px; margin: 0; color: rgba(255, 255, 255, .8); font-size: 14px; line-height: 1.7; }
    .document-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .document-hero-actions .btn { display: inline-flex; align-items: center; gap: 7px; min-height: 40px; padding-inline: 17px; border-radius: 10px; font-weight: 750; }

    .document-hero-visual { position: relative; z-index: 1; min-height: 160px; }
    .document-folder-back,
    .document-folder-front,
    .document-sheet { position: absolute; left: 50%; transform: translateX(-50%); }
    .document-folder-back { bottom: 35px; width: 210px; height: 125px; border-radius: 18px; background: rgba(10, 24, 69, .35); transform: translateX(-50%) rotate(-3deg); }
    .document-folder-back::before { position: absolute; width: 90px; height: 25px; top: -12px; left: 16px; content: ""; border-radius: 10px 10px 0 0; background: rgba(10, 24, 69, .35); }
    .document-sheet { display: grid; place-items: center; width: 135px; height: 170px; bottom: 32px; border-radius: 10px; color: #3157d5; background: #fff; box-shadow: 0 12px 30px rgba(10, 24, 69, .24); font-size: 42px; }
    .document-sheet::after { position: absolute; width: 78px; height: 36px; bottom: 27px; content: ""; border-top: 5px solid #dfe6f5; border-bottom: 5px solid #dfe6f5; }
    .document-sheet.is-first { margin-left: -28px; transform: translateX(-50%) rotate(-11deg); }
    .document-sheet.is-second { margin-left: 28px; transform: translateX(-50%) rotate(8deg); color: #1a9a75; }
    .document-folder-front { display: grid; place-items: center; width: 230px; height: 95px; bottom: 5px; border: 1px solid rgba(255, 255, 255, .28); border-radius: 15px 15px 24px 24px; color: rgba(255, 255, 255, .75); background: linear-gradient(160deg, rgba(92, 148, 255, .96), rgba(46, 80, 184, .96)); box-shadow: 0 18px 34px rgba(8, 25, 73, .28); font-size: 35px; }

    .document-sync-note {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding: 12px 15px;
        border: 1px solid #dce7ff;
        border-radius: 12px;
        color: #38527f;
        background: #f4f7ff;
    }

    .document-sync-note > i { display: grid; place-items: center; flex: 0 0 34px; height: 34px; border-radius: 9px; color: #3157d5; background: #e5ebff; font-size: 19px; }
    .document-sync-note div { display: grid; gap: 2px; }
    .document-sync-note strong { font-size: 12px; }
    .document-sync-note span { color: #7888a5; font-size: 11px; }

    .document-template-library {
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid var(--document-line);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(31, 45, 77, .06);
    }

    .document-template-heading { display: flex; align-items: end; justify-content: space-between; gap: 18px; margin-bottom: 14px; }
    .document-template-title { display: flex; align-items: center; gap: 12px; }
    .document-template-title > span { display: grid; place-items: center; flex: 0 0 43px; height: 43px; border-radius: 12px; color: #3157d5; background: #edf1ff; font-size: 23px; }
    .document-template-title small { display: block; margin-bottom: 2px; color: #8793a6; font-size: 8px; font-weight: 850; letter-spacing: .08em; text-transform: uppercase; }
    .document-template-title h5 { margin: 0 0 3px; color: #24314a; font-size: 16px; font-weight: 850; }
    .document-template-title p { margin: 0; color: #8792a5; font-size: 10px; }
    .document-template-branch { display: grid; flex: 0 0 240px; gap: 5px; }
    .document-template-branch label { margin: 0; color: #7d899c; font-size: 8px; font-weight: 850; letter-spacing: .05em; text-transform: uppercase; }
    .document-template-branch .form-select { min-height: 39px; border-color: #dfe4ed; border-radius: 9px; color: #455269; font-size: 10px; }

    .document-template-profile { display: flex; align-items: center; gap: 9px; margin-bottom: 14px; padding: 10px 12px; border: 1px solid #dfe7f5; border-radius: 10px; color: #53627b; background: #f8faff; }
    .document-template-profile > i { font-size: 19px; }
    .document-template-profile div { display: grid; gap: 2px; }
    .document-template-profile strong { color: #344158; font-size: 10px; }
    .document-template-profile span { color: #8390a4; font-size: 9px; }
    .document-template-profile.is-complete { border-color: #cfeadf; color: #0b8967; background: #f0fbf7; }
    .document-template-profile.is-warning { border-color: #f2ddb8; color: #ae6c13; background: #fff8ec; }

    .document-template-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .document-template-card { position: relative; display: grid; grid-template-columns: 42px minmax(0, 1fr); gap: 11px; min-height: 148px; padding: 15px; overflow: hidden; border: 1px solid #e3e8f0; border-radius: 13px; background: #fbfcfe; }
    .document-template-card::after { position: absolute; width: 70px; height: 90px; right: -19px; top: -28px; content: ""; border: 1px solid rgba(49, 87, 213, .08); border-radius: 9px; transform: rotate(16deg); }
    .document-template-card-icon { display: grid; place-items: center; align-self: start; width: 42px; height: 42px; border-radius: 11px; color: #3157d5; background: #edf1ff; font-size: 21px; }
    .document-template-card.is-narcotic .document-template-card-icon { color: #cf405b; background: #fff0f3; }
    .document-template-card.is-psychotropic .document-template-card-icon { color: #5267dc; background: #eef0ff; }
    .document-template-card.is-precursor .document-template-card-icon { color: #bd7518; background: #fff5e6; }
    .document-template-card-copy { min-width: 0; }
    .document-template-card-copy small { color: #909bad; font-size: 8px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
    .document-template-card-copy h6 { margin: 2px 0 4px; color: #2e3a52; font-size: 12px; font-weight: 850; }
    .document-template-card-copy p { margin: 0; color: #8b96a8; font-size: 9px; line-height: 1.45; }
    .document-template-card-actions { position: relative; z-index: 1; display: flex; align-items: center; grid-column: 1 / -1; gap: 7px; margin-top: auto; }
    .document-template-card-actions .btn { display: inline-flex; align-items: center; justify-content: center; flex: 1; gap: 5px; min-height: 33px; border-radius: 8px; font-size: 9px; font-weight: 800; }
    .document-template-print { display: grid; place-items: center; flex: 0 0 34px; height: 34px; border: 1px solid #d7dfef; border-radius: 8px; color: #3157d5; background: #fff; font-size: 17px; }
    .document-template-print:hover { color: #fff; border-color: #3157d5; background: #3157d5; }
    .document-template-empty { display: flex; align-items: center; justify-content: center; gap: 10px; min-height: 110px; padding: 20px; border: 1px dashed #d9e0eb; border-radius: 12px; color: #8a96a8; background: #fafbfd; }
    .document-template-empty > i { font-size: 28px; }
    .document-template-empty div { display: grid; gap: 2px; }
    .document-template-empty strong { color: #657187; font-size: 11px; }
    .document-template-empty span { font-size: 9px; }

    .document-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
    .document-metric {
        display: flex;
        align-items: center;
        min-width: 0;
        min-height: 105px;
        padding: 17px;
        border: 1px solid var(--document-line);
        border-radius: 16px;
        text-align: left;
        color: inherit;
        background: #fff;
        box-shadow: 0 8px 24px rgba(31, 45, 77, .06);
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .document-metric:hover { z-index: 1; border-color: #ccd5e6; box-shadow: 0 13px 30px rgba(31, 45, 77, .1); transform: translateY(-2px); }
    .document-metric-icon { display: grid; place-items: center; flex: 0 0 46px; height: 46px; margin-right: 13px; border-radius: 13px; color: #3157d5; background: #edf1ff; font-size: 23px; }
    .document-metric.is-narcotic .document-metric-icon { color: var(--document-narcotic); background: #fff0f3; }
    .document-metric.is-psychotropic .document-metric-icon { color: var(--document-psychotropic); background: #eef0ff; }
    .document-metric.is-precursor .document-metric-icon { color: var(--document-precursor); background: #fff5e6; }
    .document-metric-copy { display: grid; min-width: 0; }
    .document-metric-copy small { color: #8a96a9; font-size: 10px; font-weight: 750; text-transform: uppercase; letter-spacing: .06em; }
    .document-metric-copy strong { margin: 1px 0; color: var(--document-ink); font-size: 25px; font-weight: 900; line-height: 1.15; }
    .document-metric-copy span { overflow: hidden; color: #778398; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
    .document-metric-copy span b { color: #4a5870; }
    .document-metric-arrow { margin-left: auto; color: #c3cad7; font-size: 20px; }

    .document-archive { overflow: hidden; border: 1px solid var(--document-line); border-radius: 18px; background: #fff; box-shadow: 0 12px 34px rgba(31, 45, 77, .07); }
    .document-archive-heading { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 20px 22px; border-bottom: 1px solid #edf0f5; }
    .document-archive-heading > div { display: flex; align-items: center; gap: 12px; }
    .document-heading-icon { display: grid; place-items: center; flex: 0 0 42px; height: 42px; border-radius: 12px; color: #3157d5; background: #edf1ff; font-size: 22px; }
    .document-archive-heading h5 { margin: 0 0 3px; font-size: 16px; font-weight: 850; }
    .document-archive-heading p { margin: 0; color: #8490a3; font-size: 11px; }
    .document-refresh-button { display: grid; place-items: center; width: 39px; height: 39px; border: 1px solid #dfe4ed; border-radius: 10px; color: #5d6a7d; background: #fff; font-size: 20px; }
    .document-refresh-button:hover { color: #3157d5; border-color: #bdc9ed; background: #f7f9ff; }

    .document-toolbar { display: flex; align-items: end; gap: 12px; padding: 16px 22px; background: #fbfcfe; }
    .document-search { position: relative; display: flex; align-items: center; flex: 1 1 370px; min-width: 240px; }
    .document-search > i { position: absolute; left: 13px; z-index: 1; color: #9ba5b5; font-size: 20px; }
    .document-search input { width: 100%; height: 41px; padding: 8px 39px; border: 1px solid #dfe4ed; border-radius: 10px; outline: 0; color: #26334a; background: #fff; font-size: 12px; transition: border-color .15s, box-shadow .15s; }
    .document-search input:focus { border-color: #91a5e6; box-shadow: 0 0 0 3px rgba(49, 87, 213, .09); }
    .document-search button { position: absolute; right: 7px; display: none; place-items: center; width: 28px; height: 28px; border: 0; border-radius: 7px; color: #8d98a9; background: transparent; }
    .document-search.has-value button { display: grid; }
    .document-field { display: grid; flex: 0 0 auto; gap: 5px; }
    .document-field label { margin: 0; color: #788498; font-size: 9px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .document-field .form-select,
    .document-field .form-control { min-height: 39px; border-color: #dfe4ed; border-radius: 9px; color: #46536a; background-color: #fff; font-size: 11px; }
    .document-period { display: flex; align-items: end; gap: 7px; }
    .document-period > span { padding-bottom: 10px; color: #a1aaba; font-size: 9px; }
    .document-period .form-control { max-width: 135px; }
    .document-page-size .form-select { width: 72px; }

    .document-type-tabs { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; padding: 0 22px 15px; border-bottom: 1px solid #edf0f5; background: #fbfcfe; }
    .document-type-tabs button { display: inline-flex; align-items: center; gap: 7px; min-height: 31px; padding: 6px 11px; border: 1px solid #e0e5ee; border-radius: 999px; color: #647187; background: #fff; font-size: 10px; font-weight: 750; }
    .document-type-tabs button:hover,
    .document-type-tabs button.is-active { color: #3157d5; border-color: #b9c7ef; background: #eff3ff; }
    .document-type-tabs .document-reset-filter { margin-left: auto; border-color: transparent; background: transparent; }
    .type-dot { width: 7px; height: 7px; border-radius: 50%; background: #8a96aa; }
    .type-dot.is-narcotic { background: var(--document-narcotic); }
    .type-dot.is-psychotropic { background: var(--document-psychotropic); }
    .type-dot.is-precursor { background: var(--document-precursor); }

    .document-table-wrap { min-height: 320px; }
    .document-table { width: 100% !important; margin: 0 !important; }
    .document-table thead th { padding: 12px 13px; border: 0; border-bottom: 1px solid #e7ebf1 !important; color: #8b96a8; background: #fff; font-size: 9px; font-weight: 850; letter-spacing: .055em; text-transform: uppercase; white-space: nowrap; }
    .document-table tbody td { padding: 13px; border-color: #edf0f4; color: #536075; font-size: 11px; vertical-align: middle; }
    .document-table tbody tr:hover td { background: #fafbfe; }
    .document-number-cell { display: grid; gap: 4px; min-width: 170px; }
    .document-number-cell strong { color: #23304a; font-size: 11px; font-weight: 850; }
    .document-number-cell small { color: #909bad; font-size: 9px; }
    .document-extra-count { display: inline-flex; width: fit-content; padding: 2px 6px; border-radius: 99px; color: #3157d5; background: #edf1ff; font-size: 8px; font-weight: 800; }
    .document-type-badge,
    .document-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 8px; border-radius: 99px; font-size: 9px; font-weight: 800; white-space: nowrap; }
    .document-type-badge::before { width: 6px; height: 6px; content: ""; border-radius: 50%; }
    .document-type-badge.is-narcotic { color: #b8334d; background: #fff0f3; }
    .document-type-badge.is-narcotic::before { background: var(--document-narcotic); }
    .document-type-badge.is-psychotropic { color: #465ac6; background: #eef0ff; }
    .document-type-badge.is-psychotropic::before { background: var(--document-psychotropic); }
    .document-type-badge.is-precursor { color: #9e6115; background: #fff5e6; }
    .document-type-badge.is-precursor::before { background: var(--document-precursor); }
    .document-reference { display: grid; gap: 3px; min-width: 130px; }
    .document-reference strong { color: #334057; font-size: 10px; }
    .document-reference small { max-width: 180px; overflow: hidden; color: #8a95a7; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
    .document-content-count { display: grid; gap: 2px; min-width: 90px; }
    .document-content-count strong { color: #344158; font-size: 10px; }
    .document-content-count small { color: #909bad; font-size: 9px; }
    .document-status-badge { color: #657186; background: #f0f2f6; }
    .document-status-badge.is-ready { color: #087a5b; background: #e8f8f2; }
    .document-status-badge.is-waiting { color: #9b651a; background: #fff4df; }
    .document-status-badge.is-rejected { color: #b53d52; background: #fff0f2; }
    .document-action-group { display: flex; gap: 5px; }
    .document-action-button { display: grid; place-items: center; width: 31px; height: 31px; border: 1px solid #dfe4ed; border-radius: 8px; color: #657187; background: #fff; font-size: 16px; }
    .document-action-button:hover { color: #3157d5; border-color: #b7c5ed; background: #f4f7ff; }
    .document-action-button.is-print { color: #fff; border-color: #3157d5; background: #3157d5; }
    .document-action-button.is-print:hover { background: #2749ba; }
    .document-table-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 20px; border-top: 1px solid #edf0f4; }
    .document-table-footer .dataTables_info { padding: 0 !important; color: #8994a5; font-size: 10px; }
    .document-table-footer .dataTables_paginate { padding: 0 !important; }
    .document-table-footer .page-link { min-width: 30px; border-color: #e0e5ed; color: #637087; font-size: 10px; text-align: center; }
    .document-table-footer .page-item.active .page-link { border-color: #3157d5; background: #3157d5; }
    .document-table .dataTables_empty { padding: 50px 16px; color: #8e99aa; }

    .document-detail-modal { overflow: hidden; border: 0; border-radius: 18px; box-shadow: 0 25px 70px rgba(20, 31, 56, .22); }
    .document-detail-modal .modal-header { padding: 18px 22px; border-bottom-color: #e9edf3; background: #f8faff; }
    .document-modal-title { display: flex; align-items: center; gap: 12px; }
    .document-modal-title > span { display: grid; place-items: center; flex: 0 0 45px; height: 45px; border-radius: 12px; color: #3157d5; background: #e9eeff; font-size: 23px; }
    .document-modal-title > span.is-narcotic { color: #cf405b; background: #fff0f3; }
    .document-modal-title > span.is-psychotropic { color: #5267dc; background: #eef0ff; }
    .document-modal-title > span.is-precursor { color: #bd7518; background: #fff5e6; }
    .document-modal-title small { display: block; color: #8b96a8; font-size: 9px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .document-modal-title h5 { margin: 1px 0 2px; color: #1d2a43; font-size: 16px; font-weight: 850; }
    .document-modal-title p { margin: 0; color: #738097; font-size: 10px; }
    .document-detail-modal .modal-body { padding: 22px; background: #fbfcfe; }
    .document-detail-loading { display: flex; align-items: center; justify-content: center; gap: 9px; min-height: 230px; color: #7d899c; font-size: 12px; }
    .document-detail-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
    .document-detail-summary > div { display: grid; gap: 4px; min-width: 0; padding: 12px; border: 1px solid #e6eaf1; border-radius: 11px; background: #fff; }
    .document-detail-summary span { color: #8b96a8; font-size: 8px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
    .document-detail-summary strong { overflow: hidden; color: #344158; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
    .document-number-section,
    .document-item-section { margin-bottom: 15px; padding: 15px; border: 1px solid #e4e9f1; border-radius: 13px; background: #fff; }
    .document-section-title { display: flex; align-items: center; gap: 10px; margin-bottom: 13px; }
    .document-section-title > span { display: grid; place-items: center; flex: 0 0 34px; height: 34px; border-radius: 9px; color: #3157d5; background: #edf1ff; font-size: 18px; }
    .document-section-title h6 { margin: 0 0 2px; color: #2f3b52; font-size: 12px; font-weight: 850; }
    .document-section-title p { margin: 0; color: #929cad; font-size: 9px; }
    .document-number-list { display: flex; flex-wrap: wrap; gap: 7px; }
    .document-number-list span { display: inline-flex; align-items: center; gap: 6px; padding: 6px 9px; border: 1px solid #dce3f3; border-radius: 8px; color: #41516f; background: #f8faff; font-size: 10px; font-weight: 750; }
    .document-number-list span i { color: #3157d5; }
    .document-item-table { margin: 0; }
    .document-item-table th { padding: 8px 10px; color: #919bac; background: #f8f9fc; font-size: 8px; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .document-item-table td { padding: 10px; border-color: #edf0f4; color: #556176; font-size: 10px; }
    .document-item-table td strong { color: #344158; }
    .document-detail-note { display: flex; align-items: start; gap: 8px; padding: 11px 13px; border: 1px solid #dfe7f8; border-radius: 10px; color: #657794; background: #f5f8ff; font-size: 10px; line-height: 1.5; }
    .document-detail-note i { color: #3157d5; font-size: 16px; }
    .document-detail-modal .modal-footer { padding: 13px 20px; border-top-color: #e8ecf2; }
    .document-detail-modal .modal-footer .btn { display: inline-flex; align-items: center; gap: 6px; border-radius: 9px; font-size: 11px; font-weight: 750; }

    /* Skala baca bersama untuk seluruh fitur Dokumen. */
    .document-kicker { font-size: 12px; }
    .document-hero p { font-size: 15px; }
    .document-sync-note strong { font-size: 14px; }
    .document-sync-note span { font-size: 13px; line-height: 1.5; }
    .document-template-title small { font-size: 11px; }
    .document-template-title h5 { font-size: 18px; }
    .document-template-title p { font-size: 13px; line-height: 1.5; }
    .document-template-branch label { font-size: 11px; }
    .document-template-branch .form-select { font-size: 13px; }
    .document-template-profile strong { font-size: 13px; }
    .document-template-profile span { font-size: 11px; line-height: 1.45; }
    .document-template-card { min-height: 166px; }
    .document-template-card-copy small { font-size: 11px; }
    .document-template-card-copy h6 { font-size: 14px; }
    .document-template-card-copy p { font-size: 12px; line-height: 1.55; }
    .document-template-card-actions .btn { font-size: 11px; }
    .document-template-empty strong { font-size: 13px; }
    .document-template-empty span { font-size: 11px; }
    .document-metric-copy small { font-size: 12px; }
    .document-metric-copy strong { font-size: 28px; }
    .document-metric-copy span { font-size: 12px; }
    .document-archive-heading h5 { font-size: 18px; }
    .document-archive-heading p { font-size: 13px; line-height: 1.45; }
    .document-search input { font-size: 14px; }
    .document-field label { font-size: 11px; }
    .document-field .form-select,
    .document-field .form-control { font-size: 13px; }
    .document-type-tabs button { min-height: 35px; padding: 7px 13px; font-size: 12px; }
    .document-table thead th { font-size: 11px; }
    .document-table tbody td { font-size: 13px; }
    .document-number-cell strong { font-size: 13px; }
    .document-number-cell small,
    .document-reference small,
    .document-content-count small { font-size: 11px; }
    .document-extra-count { font-size: 10px; }
    .document-type-badge,
    .document-status-badge { font-size: 11px; }
    .document-reference strong,
    .document-content-count strong { font-size: 12px; }
    .document-table-footer .dataTables_info,
    .document-table-footer .page-link { font-size: 12px; }
    .document-modal-title small { font-size: 11px; }
    .document-modal-title h5 { font-size: 18px; }
    .document-modal-title p { font-size: 12px; }
    .document-detail-loading { font-size: 14px; }
    .document-detail-summary span { font-size: 11px; }
    .document-detail-summary strong { font-size: 12px; }
    .document-section-title h6 { font-size: 14px; }
    .document-section-title p { font-size: 11px; }
    .document-number-list span { font-size: 12px; }
    .document-item-table th { font-size: 11px; }
    .document-item-table td { font-size: 12px; }
    .document-detail-note { font-size: 12px; }
    .document-detail-modal .modal-footer .btn { font-size: 13px; }

    @media (max-width: 1199.98px) {
        .document-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .document-template-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .document-toolbar { flex-wrap: wrap; }
        .document-search { flex-basis: 100%; }
        .document-detail-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 767.98px) {
        .document-hero { grid-template-columns: 1fr; padding: 26px 22px; }
        .document-hero-visual { display: none; }
        .document-metrics { grid-template-columns: 1fr; }
        .document-template-library { padding: 15px; }
        .document-template-heading { align-items: stretch; flex-direction: column; }
        .document-template-branch { flex-basis: auto; }
        .document-template-grid { grid-template-columns: 1fr; }
        .document-toolbar { align-items: stretch; padding-inline: 15px; }
        .document-search { min-width: 100%; }
        .document-period { display: grid; grid-template-columns: 1fr 1fr; }
        .document-period > span { display: none; }
        .document-period .form-control { max-width: none; }
        .document-page-size .form-select { width: 100%; }
        .document-type-tabs { padding-inline: 15px; }
        .document-type-tabs .document-reset-filter { width: 100%; margin-left: 0; justify-content: center; }
        .document-archive-heading { padding-inline: 15px; }
        .document-detail-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .document-table-footer { flex-direction: column; }
    }
</style>
