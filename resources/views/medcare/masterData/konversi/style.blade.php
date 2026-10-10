<style>
    .konversi-page {
        --konversi-primary: #087f73;
        --konversi-blue: #3563a3;
        --konversi-warning: #a96713;
        --konversi-success: #087f5b;
        --obat-primary: #087f73;
        --obat-primary-strong: #09665e;
        --obat-accent: #3563a3;
        --obat-border: #e3eae9;
        --obat-text: #1c3436;
        --obat-muted: #647777;
        min-width: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .konversi-page .page-breadcrumb { margin-bottom: 1.15rem; }
    .konversi-page .breadcrumb { font-size: .78rem; }
    .konversi-page .btn { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; min-height: 44px; border-radius: 10px; font-weight: 700; font-size: .8rem; transition: background .18s, box-shadow .18s, border-color .18s; }
    .konversi-page .btn i { font-size: 1.15rem; }
    .konversi-page .btn-primary { background: var(--konversi-primary); border-color: var(--konversi-primary); box-shadow: 0 4px 10px #087f731a; }
    .konversi-page .btn-primary:hover { background: #09665e; border-color: #09665e; }
    .konversi-page .btn-outline-primary { color: #09665e; border-color: #bad7d1; background: #fff; }
    .konversi-page .btn-outline-primary:hover { background: #edf7f4; border-color: #087f73; }
    .konversi-page :is(button, a, input, select, summary):focus-visible { outline: 3px solid #37b9a4; outline-offset: 3px; }
    .konversi-page .btn:disabled { opacity: .5; box-shadow: none; }

    .konversi-hero { position: relative; display: grid; grid-template-columns: 1.1fr 1fr; gap: 2rem; align-items: center; padding: clamp(1.4rem, 3vw, 2.6rem); border: 1px solid #166b61; border-radius: 22px; background: radial-gradient(ellipse at 95% 0%, #1e7f6c 0, transparent 60%), #104d47; color: #fff; box-shadow: 0 12px 32px #104d4714; overflow: hidden; }
    .konversi-hero-copy { position: relative; min-width: 0; }
    .konversi-eyebrow { display: inline-flex; align-items: center; gap: .55rem; color: #b5e4d7; font-size: .65rem; font-weight: 700; letter-spacing: .16em; }
    .konversi-eyebrow > span { width: 6px; height: 6px; border-radius: 50%; background: #91e0c4; box-shadow: 0 0 0 4px #91e0c415; }
    .konversi-hero h1 { margin: .8rem 0 .85rem; color: #fff; font-size: clamp(1.7rem, 2.7vw, 2.5rem); line-height: 1.25; font-weight: 800; letter-spacing: -.045em; }
    .konversi-hero h1 span { color: #bbebd9; }
    .konversi-hero p { max-width: 540px; margin: 0; color: #c6ddd8; font-size: .82rem; line-height: 1.8; }
    .konversi-hero-actions { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: 1.35rem; }
    .konversi-page .konversi-btn-white { padding: .7rem 1.1rem; border-color: #fff; color: #104d47; background: #fff; }
    .konversi-page .konversi-btn-white:hover { background: #e5f4ee; border-color: #e5f4ee; }
    .konversi-page .konversi-btn-glass { padding: .7rem 1rem; border-color: #ffffff40; color: #fff; background: #ffffff08; }
    .konversi-page .konversi-btn-glass:hover { background: #ffffff18; }
    .konversi-equation { position: relative; min-width: 0; padding: 1.25rem; border: 1px solid #ffffff2e; border-radius: 16px; background: #ffffff0b; }
    .konversi-equation-heading { display: flex; align-items: center; gap: .45rem; color: #dcf4ec; font-size: .75rem; font-weight: 600; }
    .konversi-equation-heading > i { font-size: 1.15rem; color: #9bdecb; }
    .konversi-equation-heading small { margin-left: auto; padding: .25rem .4rem; border-radius: 5px; background: #ffffff12; color: #c8e7dd; font-size: .55rem; letter-spacing: .1em; }
    .konversi-equation-units { display: grid; grid-template-columns: 1fr 16px 1fr 16px 1fr; align-items: center; gap: .35rem; padding: 1.25rem 0; }
    .konversi-equation-units > div { display: grid; justify-items: center; text-align: center; gap: .35rem; min-width: 0; }
    .konversi-equation-units > div > i { display: grid; place-items: center; width: 44px; height: 44px; margin-bottom: .3rem; border: 1px solid #ffffff26; border-radius: 12px; background: #ffffff0c; color: #bce9d8; font-size: 1.4rem; }
    .konversi-equation-units strong { color: #fff; font-size: 1.3rem; font-weight: 800; white-space: nowrap; }
    .konversi-equation-units strong span { font-size: .78rem; font-weight: 600; }
    .konversi-equation-units small { color: #b9d7ce; font-size: .6rem; }
    .konversi-equation-equals { color: #89b9ab; font-size: 1.2rem; text-align: center; }
    .konversi-equation-foot { display: flex; gap: .4rem; align-items: flex-start; padding-top: .8rem; border-top: 1px solid #ffffff1c; color: #bddbd0; font-size: .63rem; line-height: 1.6; }

    .konversi-page .konversi-stats { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .8rem; margin: 1.25rem 0; }
    .konversi-page .obat-stat { align-items: flex-start; flex-direction: row; justify-content: flex-start; text-align: left; gap: .75rem; padding: 1.1rem; min-height: 120px; border-radius: 14px; box-shadow: 0 3px 12px #1c343603; }
    .konversi-page .obat-stat > div { min-width: 0; }
    .konversi-page .obat-stat-icon { display: grid; width: 36px; height: 36px; flex-basis: 36px; border-radius: 10px; font-size: 1.1rem; background: #edf5f3; color: #376e68; }
    .konversi-page .obat-stat span:not(.obat-stat-icon) { margin-bottom: .45rem; font-size: .65rem; letter-spacing: 0; text-transform: none; font-weight: 600; }
    .konversi-page .obat-stat strong { margin-bottom: .4rem; font-size: 1.75rem; font-weight: 800; letter-spacing: -.05em; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .konversi-page .obat-stat small { display: block; font-size: .6rem; line-height: 1.5; }
    .konversi-stat-ready .obat-stat-icon { color: #087f5b; background: #e8f7ef; }
    .konversi-stat-pending .obat-stat-icon { color: #a96713; background: #fff5e5; }
    .konversi-stat-po .obat-stat-icon { color: #986154; background: #faf0ec; }
    .konversi-stat-filtered .obat-stat-icon { color: #3563a3; background: #edf2fb; }

    .konversi-page .konversi-table-section { overflow: hidden; border-radius: 18px; box-shadow: 0 6px 24px #1c343606; }
    .konversi-table-heading { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 1.35rem 1.35rem 1rem; }
    .konversi-page .obat-table-title { min-width: 0; gap: .7rem; }
    .konversi-page .obat-table-title-icon { width: 40px; height: 40px; flex: 0 0 40px; color: #087f73; background: #edf7f4; border-radius: 12px; }
    .konversi-table-heading h2 { margin: 0; color: var(--obat-text); font-size: .98rem; font-weight: 800; letter-spacing: -.025em; }
    .konversi-page .obat-table-title p { font-size: .72rem; margin-top: .3rem; line-height: 1.6; }
    .konversi-page .konversi-refresh { flex-shrink: 0; color: var(--obat-muted); border: 1px solid var(--obat-border); background: #fff; padding: .45rem .75rem; font-size: .7rem; }
    .konversi-page .konversi-refresh:hover { color: #087f73; background: #f2f8f6; }
    .konversi-table-toolbar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: .85rem; padding: 0 1.35rem 1.1rem; border-bottom: 1px solid var(--obat-border); }
    .konversi-status-filter { display: flex; flex-wrap: wrap; gap: .2rem; padding: .3rem; border: 1px solid #e8eeec; border-radius: 12px; background: #f5f8f7; }
    .konversi-page .konversi-status-filter .btn { padding: .45rem .7rem; min-height: 38px; border: 1px solid transparent; border-radius: 8px; color: #647777; font-size: .7rem; white-space: nowrap; }
    .konversi-page .konversi-status-filter .btn i { font-size: .95rem; }
    .konversi-page .konversi-status-filter .btn:hover { color: #09665e; background: #eaf3ef; }
    .konversi-page .konversi-status-filter .btn.is-active { border-color: #dae8e1; color: #09665e; background: #fff; box-shadow: 0 2px 5px #1c343608; }
    .konversi-page .obat-search { min-width: 0; width: 320px; max-width: 100%; min-height: 44px; margin: 0; border-radius: 10px; }
    .konversi-page .obat-search:focus-within { border-color: #087f73; box-shadow: 0 0 0 3px #087f7312; }
    .konversi-page .obat-search input { min-width: 0; padding: .7rem; font-size: .74rem; }
    .konversi-page .obat-search input::placeholder { color: #849591; }
    .konversi-page .obat-table-section .table-responsive { padding: 0 1.35rem .75rem; }
    .konversi-page .obat-table thead th { background: #f6f9f8; color: #71827f; font-size: .63rem; padding: .9rem .75rem; letter-spacing: .04em; }
    .konversi-page .obat-table tbody td { padding: 1rem .75rem; font-size: .78rem; }
    .konversi-page .obat-table tbody tr:hover { background: #f8fbfa; }
    .konversi-page .obat-identity { min-width: 210px; gap: .65rem; }
    .konversi-page .obat-avatar { border: 1px solid #d9ebe4; border-radius: 11px; background: #edf6f2; color: #36796a; font-size: .74rem; }
    .konversi-page .obat-identity strong { font-size: .78rem; font-weight: 700; }
    .konversi-page .obat-identity small { font-size: .64rem; }
    .konversi-po-reference { display: block; margin-top: .3rem; color: var(--obat-muted); font-size: .65rem; line-height: 1.6; white-space: normal; overflow-wrap: anywhere; }
    .konversi-page .obat-tag-badge { padding: .4rem .55rem; color: #536d68; background: #f0f5f3; font-size: .66rem; font-weight: 600; }
    .konversi-status-badge, .konversi-empty-badge { display: inline-flex; flex-wrap: wrap; align-items: center; gap: .35rem; max-width: 100%; padding: .4rem .6rem; border-radius: 8px; font-size: .65rem; font-weight: 700; line-height: 1.5; }
    .konversi-status-badge.is-ready { color: #087f5b; background: #eaf7f0; }
    .konversi-status-badge.is-empty, .konversi-empty-badge { color: #9b631d; background: #fff5e6; }
    .konversi-status-badge small { font-size: .6rem; font-weight: 500; }
    .konversi-chip-list { display: flex; flex-wrap: wrap; gap: .4rem; min-width: 0; max-width: 460px; white-space: normal; }
    .konversi-chip { display: inline-flex; align-items: center; gap: .3rem; max-width: 100%; padding: .4rem .55rem; border: 1px solid #e3eae9; border-radius: 7px; color: #526e6b; background: #f8faf9; font-size: .65rem; font-weight: 600; line-height: 1.55; overflow-wrap: anywhere; }
    .konversi-chip.is-default { color: #09665e; border-color: #cee8df; background: #f0f9f4; }
    .konversi-inline-empty { display: inline-flex; align-items: center; gap: .4rem; min-height: 44px; padding: .4rem .65rem; border: 1px dashed #dcc7a8; border-radius: 8px; color: #9b631d; background: #fffbf4; font-size: .7rem; font-weight: 600; }
    .konversi-inline-empty:hover { background: #fff4de; }
    .konversi-page .obat-action-group { display: flex; }
    .konversi-page .konversi-manage-btn { width: auto; padding: .4rem .7rem; color: #09665e; border: 1px solid #d4e7de; background: #f3faf6; font-size: .7rem; }
    .konversi-page .konversi-manage-btn::after { content: none; }
    .konversi-page .obat-table-meta, .konversi-page .obat-table-footer { padding: .9rem 0; color: var(--obat-muted); font-size: .7rem; }
    .konversi-page .obat-table-footer .active .page-link { background: #087f73; border-color: #087f73; }

    /* Modal headers and footers remain outside the scrolling form body. */
    .konversi-page .konversi-modal .modal-dialog { margin: 1.5rem auto; }
    .konversi-page .konversi-modal .modal-content { max-height: 100%; border: 1px solid #e5eeea; border-radius: 20px; box-shadow: 0 28px 90px #103c3433; }
    .konversi-page .konversi-modal .modal-header { align-items: center; padding: 1.3rem 1.5rem; border-bottom: 1px solid var(--obat-border); background: #fff; color: var(--obat-text); }
    .konversi-page .konversi-modal .modal-title-wrap { min-width: 0; gap: .85rem; }
    .konversi-page .konversi-modal .modal-title-wrap > div { min-width: 0; }
    .konversi-page .konversi-modal .modal-icon { width: 46px; height: 46px; flex: 0 0 46px; border: 1px solid #dceee6; border-radius: 13px; background: #edf7f2; color: #087f73; }
    .konversi-modal-kicker { display: block; margin-bottom: .25rem; color: #087f73; font-size: .6rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .konversi-page .konversi-modal .modal-title { color: var(--obat-text); font-size: 1.08rem; font-weight: 800; letter-spacing: -.025em; }
    .konversi-page .konversi-modal .modal-subtitle { margin: .35rem 0 0; color: var(--obat-muted); font-size: .72rem; line-height: 1.6; }
    .konversi-page .konversi-modal .btn-close { flex: 0 0 24px; filter: none; opacity: .6; padding: .6rem; margin-left: .5rem; }
    .konversi-page .konversi-modal .modal-body { min-height: 0; padding: 1.5rem; background: #f7f9f8; overscroll-behavior: contain; }
    .konversi-page .konversi-modal .modal-footer { position: static; flex: 0 0 auto; display: flex; flex-wrap: nowrap; gap: .65rem; margin: 0; padding: 1rem 1.5rem; border-top: 1px solid var(--obat-border); background: #fff; box-shadow: 0 -4px 16px #1c343604; }
    .konversi-page .konversi-modal .modal-footer > * { margin: 0; }
    .konversi-footer-hint { margin-right: auto !important; color: var(--obat-muted); font-size: .65rem; }
    .konversi-footer-hint i { margin-right: .25rem; color: #087f73; }
    .konversi-obat-context { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .8rem; margin-bottom: 1.25rem; padding: 1rem 1.15rem; border: 1px solid #d7e9e1; border-radius: 12px; background: #f0f7f3; }
    .konversi-obat-context > div { min-width: 0; }
    .konversi-context-label { display: block; margin-bottom: .25rem; color: #568174; font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
    .konversi-obat-context strong { display: block; color: var(--obat-text); font-size: .94rem; font-weight: 800; overflow-wrap: anywhere; }
    .konversi-obat-context #konversiObatMeta { display: block; margin-top: .3rem; color: var(--obat-muted); font-size: .7rem; }
    .konversi-section-heading { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .85rem; }
    .konversi-section-heading h3 { margin: 0; color: var(--obat-text); font-size: .85rem; font-weight: 800; }
    .konversi-section-heading p { margin: .25rem 0 0; color: var(--obat-muted); font-size: .68rem; line-height: 1.6; }
    .konversi-input-card { min-width: 0; overflow: hidden; border: 1px solid var(--obat-border); border-radius: 12px; background: #fff; box-shadow: 0 3px 8px #1c343603; }
    .konversi-input-card-header { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .6rem .95rem; border-bottom: 1px solid #edf1ef; background: #fcfdfc; }
    .konversi-input-card-header strong { display: flex; align-items: center; gap: .4rem; color: #47635d; font-size: .7rem; font-weight: 700; }
    .konversi-input-card-header strong i { color: #087f73; font-size: 1.1rem; }
    .konversi-page .konversi-input-card-header .btn { min-height: 36px; padding: .3rem .55rem; background: transparent; color: #93685f; font-size: .65rem; }
    .konversi-page .konversi-input-card-header .btn:hover { background: #fff1ec; color: #ae433a; }
    .konversi-input-card-body { padding: 1rem; }
    .konversi-input-card .obat-field.is-satuan { grid-column: span 5; }
    .konversi-input-card .obat-field.is-konversi { grid-column: span 4; }
    .konversi-input-card .obat-field.is-default { grid-column: span 3; }
    .konversi-page .obat-field { min-width: 0; }
    .konversi-page .konversi-modal .form-label { display: block; margin-bottom: .5rem; color: #4b6460; font-size: .68rem; font-weight: 700; }
    .konversi-page .konversi-modal .form-control, .konversi-page .konversi-modal .form-select { min-height: 44px; border-color: #dce5e1; border-radius: 8px; font-size: .78rem; }
    .konversi-page .obat-input-shell { min-width: 0; min-height: 44px; border-radius: 9px; }
    .konversi-page .obat-input-shell .form-control, .konversi-page .obat-input-shell .form-select { min-width: 0; }
    .konversi-page .obat-input-icon { width: 34px; flex-basis: 34px; }
    .konversi-default-box { display: flex; align-items: center; gap: .5rem; min-height: 44px; padding: .6rem .7rem; border: 1px solid #dce5e1; border-radius: 9px; background: #f8fbf9; font-size: .67rem; color: #47635d; cursor: pointer; }
    .konversi-default-box:has(:checked) { border-color: #8ac9af; background: #edf8f1; color: #09665e; }
    .konversi-default-box .form-check-input { flex: 0 0 17px; width: 17px; height: 17px; margin: 0; cursor: pointer; }
    .konversi-default-box .form-check-input:checked { background-color: #087f73; border-color: #087f73; }
    .konversi-add-row { width: 100%; border-style: dashed !important; }
    .konversi-preview-panel { margin-top: 1.15rem; padding: .9rem 1rem; border: 1px solid #dcebe3; border-radius: 12px; background: #f1f8f4; }
    .konversi-preview-label { display: flex; align-items: center; gap: .35rem; margin-bottom: .55rem; color: #3c7162; font-size: .64rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
    .konversi-live-preview { color: var(--obat-muted); font-size: .72rem; line-height: 1.7; overflow-wrap: anywhere; }
    .konversi-modal-note { display: flex; gap: .6rem; padding: .85rem; border: 1px solid #e4ece7; border-radius: 10px; background: #fff; color: var(--obat-muted); font-size: .68rem; line-height: 1.7; }
    .konversi-modal-note > i { flex-shrink: 0; color: #087f73; font-size: 1.15rem; }
    .konversi-modal-note > div { min-width: 0; flex: 1; }
    .konversi-policy { margin-top: 1rem; color: var(--obat-muted); font-size: .68rem; line-height: 1.7; }
    .konversi-policy summary { width: fit-content; color: #526d64; cursor: pointer; font-weight: 600; }
    .konversi-policy p { margin: .6rem 0 0; padding: .75rem; border-left: 2px solid #9dc9b6; background: #eef5f1; }
    .konversi-batch-layout { display: grid; grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); gap: 1.25rem; align-items: start; }
    .konversi-batch-step { min-width: 0; padding: 1.15rem; border: 1px solid var(--obat-border); border-radius: 14px; background: #fff; }
    .konversi-step-heading { display: flex; align-items: center; gap: .65rem; margin-bottom: 1rem; }
    .konversi-step-number { display: grid; place-items: center; width: 30px; height: 30px; flex: 0 0 30px; border-radius: 9px; background: #edf7f2; color: #087f73; font-size: .65rem; font-weight: 800; }
    .konversi-step-heading h3 { margin: 0; color: var(--obat-text); font-size: .8rem; font-weight: 800; }
    .konversi-step-heading p { margin: .25rem 0 0; color: var(--obat-muted); font-size: .64rem; }
    .konversi-field-help { display: block; margin-top: .35rem; color: var(--obat-muted); font-size: .64rem; line-height: 1.6; }
    .konversi-batch-step .konversi-input-card .obat-field.is-satuan, .konversi-batch-step .konversi-input-card .obat-field.is-konversi { grid-column: span 6; }
    .konversi-batch-step .konversi-input-card .obat-field.is-default { grid-column: 1 / -1; }
    .konversi-batch-step .konversi-input-card-body { padding: .85rem; }
    .konversi-batch-targets { max-height: 265px; overflow: auto; margin-top: .65rem; border: 1px solid #e1ebe5; border-radius: 8px; background: #fff; }
    .konversi-batch-targets table { font-size: .65rem; min-width: 460px; }
    .konversi-batch-targets th { position: sticky; top: 0; z-index: 1; padding: .7rem; background: #f0f6f2; font-size: .6rem; white-space: nowrap; }
    .konversi-batch-targets td { padding: .65rem; max-width: 180px; white-space: normal; overflow-wrap: anywhere; }
    .konversi-page .konversi-modal .select2-container { min-width: 0; max-width: 100%; width: 100% !important; }
    .konversi-page .konversi-modal .select2-selection--single { height: 44px; border: 1px solid #dce5e1; border-radius: 8px; }
    .konversi-page .obat-input-shell .select2-selection--single { border: 0; background: transparent; }
    .konversi-page .konversi-modal .select2-selection--single .select2-selection__rendered { width: 100%; max-width: 100%; padding-left: .75rem; padding-right: 2rem; line-height: 42px; font-size: .78rem; color: var(--obat-text); }
    .konversi-page .obat-input-shell .select2-selection--single .select2-selection__rendered { padding-left: 0; }
    .konversi-page .konversi-modal .select2-selection--single .select2-selection__arrow { height: 42px; }
    .konversi-page .konversi-modal .select2-selection--single .select2-selection__clear { margin-right: .4rem; color: #647777; }
    .konversi-page .konversi-modal .select2-selection--multiple { min-height: 44px; padding: .3rem .4rem; border: 1px solid #dce5e1; border-radius: 8px; }
    .konversi-page .konversi-modal .select2-selection__choice { max-width: 100%; margin: .2rem !important; border-color: #d7e7dd; background: #eef6f1; color: #3b6555; font-size: .68rem; white-space: normal; overflow-wrap: anywhere; }
    .konversi-page .konversi-modal .select2-search__field { max-width: 100%; color: var(--obat-text); }
    .konversi-page .konversi-modal .select2-dropdown { border-color: #ccded4; border-radius: 9px; overflow: hidden; box-shadow: 0 10px 25px #1c343614; }
    .konversi-page .konversi-modal .select2-results__option { padding: .65rem .75rem; font-size: .75rem; overflow-wrap: anywhere; }
    .konversi-page .konversi-modal .select2-results__option--highlighted[aria-selected] { background: #087f73; }
    .konversi-page .konversi-modal .select2-container--focus .select2-selection, .konversi-page .konversi-modal .select2-container--open .select2-selection { border-color: #087f73; }
    .konversi-import-steps { display: flex; gap: .6rem; margin: 0 0 1.25rem; padding: 0; list-style: none; }
    .konversi-import-steps li { display: flex; flex: 1; align-items: center; gap: .5rem; color: #647777; font-size: .68rem; }
    .konversi-import-steps li span { display: grid; place-items: center; width: 26px; height: 26px; flex: 0 0 26px; border: 1px solid #d5e7dc; border-radius: 50%; background: #edf7f1; color: #087f73; font-size: .65rem; font-weight: 800; }
    .konversi-import-template { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .8rem; margin-bottom: 1rem; padding: 1rem; border: 1px solid var(--obat-border); border-radius: 12px; background: #fff; }
    .konversi-import-template strong { display: block; color: var(--obat-text); font-size: .8rem; }
    .konversi-import-template p { margin: .25rem 0 0; color: var(--obat-muted); font-size: .68rem; line-height: 1.6; }
    .konversi-import-upload { padding: 1.1rem; border: 1px solid var(--obat-border); border-radius: 12px; background: #fff; }
    .konversi-page .dropify-wrapper { min-height: 180px; border: 1px dashed #bed6c9; border-radius: 10px; background: #fafdfb; font-family: inherit; }
    .konversi-page .dropify-wrapper .dropify-message p { color: #647777; font-size: .75rem; line-height: 1.7; }
    .konversi-page .dropify-wrapper .dropify-message span.file-icon { color: #77a58f; }
    .konversi-target-scroll-hint { display: none; }

    @media (max-width: 1199.98px) {
        .konversi-hero { gap: 1.25rem; }
        .konversi-page .obat-stat { display: block; padding: .9rem; }
        .konversi-page .obat-stat-icon { margin-bottom: .65rem; }
        .konversi-table-toolbar { align-items: stretch; }
        .konversi-page .obat-search { flex: 1 1 260px; }
    }
    @media (max-width: 991.98px) {
        .konversi-hero { grid-template-columns: 1fr; }
        .konversi-equation { max-width: 620px; width: 100%; }
        .konversi-batch-layout { grid-template-columns: 1fr; }
        .konversi-page .konversi-modal .modal-dialog { width: calc(100% - 2rem); }
        .konversi-input-card .obat-field.is-satuan, .konversi-input-card .obat-field.is-konversi { grid-column: span 6; }
        .konversi-input-card .obat-field.is-default { grid-column: 1 / -1; }
    }
    @media (max-width: 767.98px) {
        .konversi-hero { padding: 1.35rem; border-radius: 16px; gap: 1.25rem; }
        .konversi-hero h1 { font-size: 1.8rem; }
        .konversi-hero p { font-size: .76rem; line-height: 1.75; }
        .konversi-eyebrow { font-size: .57rem; letter-spacing: .12em; }
        .konversi-hero-actions { display: grid; grid-template-columns: 1fr; gap: .55rem; margin-top: 1rem; }
        .konversi-hero-actions .btn { width: 100%; min-height: 46px; }
        .konversi-equation { display: none; }
        .konversi-equation-units { padding: 1rem 0; }
        .konversi-equation-heading { font-size: .64rem; }
        .konversi-equation-units strong { font-size: 1.15rem; }
        .konversi-equation-units strong span { font-size: .65rem; }
        .konversi-equation-units small { font-size: .52rem; }
        .konversi-equation-foot { font-size: .58rem; }
        .konversi-page .konversi-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem; margin: 1rem 0; }
        .konversi-page .obat-stat { display: flex; padding: .85rem; min-height: 104px; border-radius: 12px; gap: .6rem; }
        .konversi-page .obat-stat-icon { width: 30px; height: 30px; flex-basis: 30px; margin: 0; font-size: 1rem; }
        .konversi-page .obat-stat strong { font-size: 1.5rem; }
        .konversi-page .obat-stat span:not(.obat-stat-icon) { font-size: .6rem; }
        .konversi-page .obat-stat small { font-size: .56rem; }
        .konversi-stat-total { grid-column: 1 / -1; align-items: center !important; min-height: 76px !important; }
        .konversi-stat-total > div { display: grid; flex: 1; grid-template-columns: 1fr auto; column-gap: 1rem; }
        .konversi-stat-total strong { grid-column: 2; grid-row: 1 / 3; align-self: center; margin: 0 !important; font-size: 1.8rem !important; }
        .konversi-table-heading { padding: 1rem; gap: .55rem; }
        .konversi-table-heading h2 { font-size: .86rem; }
        .konversi-page .obat-table-title p { font-size: .64rem; }
        .konversi-page .obat-table-title-icon { width: 34px; height: 34px; flex-basis: 34px; }
        .konversi-page .konversi-refresh { min-width: 44px; padding: .45rem; }
        .konversi-refresh span { display: none; }
        .konversi-table-toolbar { padding: 0 1rem 1rem; gap: .75rem; }
        .konversi-status-filter { display: grid; width: 100%; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .25rem; }
        .konversi-page .konversi-status-filter .btn { min-height: 44px; padding: .45rem .25rem; font-size: .65rem; }
        .konversi-page .obat-search { width: 100%; flex: 1 1 100%; min-height: 46px; }
        .konversi-page .obat-search input { font-size: 16px; }
        .konversi-page .obat-table-section .table-responsive { padding: .85rem; background: #f5f8f6; }
        /* Six conversion columns need their own card areas, independent of master obat. */
        #tableKonversi tbody tr:not(.obat-detail-row) { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); grid-template-areas: "identity identity" "stock status" "conversions conversions" "action action"; gap: .65rem; padding: .9rem; border-radius: 13px; border-color: #e0e9e4; box-shadow: 0 3px 12px #1c343605; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td { display: flex; min-width: 0; padding: .6rem !important; border-radius: 8px; background: #f5f9f6 !important; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td::before { content: none; font-size: .58rem; text-transform: none; letter-spacing: 0; font-weight: 600; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(1) { display: none; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(2) { grid-area: identity; padding: 0 0 .8rem !important; border-bottom: 1px solid #e8efeb !important; border-radius: 0; background: #fff !important; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(3) { display: flex; grid-area: stock; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(3)::before { content: "Satuan stok"; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(4) { grid-area: status; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(4)::before { content: "Status konversi"; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(5) { grid-area: conversions; padding: .25rem 0 !important; border: 0 !important; border-radius: 0; background: #fff !important; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(5)::before { content: "Konversi tersedia"; margin-bottom: .25rem; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td:nth-child(6) { grid-area: action; padding: .3rem 0 0 !important; background: #fff !important; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td.dataTables_empty { display: flex; grid-area: 1 / 1 / auto / -1; min-height: 140px; padding: 1rem !important; }
        #tableKonversi tbody tr:not(.obat-detail-row) > td.dataTables_empty::before { content: none; }
        .konversi-page .obat-identity { min-width: 0; width: 100%; }
        .konversi-page .obat-identity > div { min-width: 0; }
        .konversi-page .obat-identity strong { white-space: normal; overflow-wrap: anywhere; font-size: .84rem; }
        .konversi-page .obat-identity small { display: block; overflow-wrap: anywhere; line-height: 1.6; }
        .konversi-page .obat-action-group { width: 100%; display: flex; }
        .konversi-page .konversi-manage-btn { width: 100% !important; min-height: 44px; font-size: .73rem; }
        .konversi-chip-list { max-width: 100%; }
        .konversi-status-badge { font-size: .6rem; }
        .konversi-page .obat-table-meta, .konversi-page .obat-table-footer { padding: .75rem 0; }
        .konversi-page .konversi-modal .modal-dialog { width: 100%; height: 100%; height: 100dvh; min-height: 0; max-width: none; margin: 0; }
        .konversi-page .konversi-modal .modal-content { height: 100%; min-height: 0; max-height: 100%; border: 0; border-radius: 0; }
        .konversi-page .konversi-modal .modal-header { flex: 0 0 auto; padding: 1rem; gap: .5rem; }
        .konversi-page .konversi-modal .modal-title-wrap { gap: .65rem; }
        .konversi-page .konversi-modal .modal-icon { width: 38px; height: 38px; flex-basis: 38px; border-radius: 11px; }
        .konversi-page .konversi-modal .modal-title { font-size: .94rem; }
        .konversi-page .konversi-modal .modal-subtitle { font-size: .65rem; }
        .konversi-page .konversi-modal .btn-close { box-sizing: border-box; width: 44px; height: 44px; flex: 0 0 44px; margin: 0; padding: .8rem; background-size: .75rem; }
        .konversi-page .konversi-modal .modal-body { flex: 1 1 auto; padding: 1rem; overflow-y: auto; }
        .konversi-page .konversi-modal .modal-footer { padding: .85rem 1rem calc(.85rem + env(safe-area-inset-bottom)); gap: .6rem; }
        .konversi-page .konversi-modal .modal-footer .btn { flex: 1 1 0; min-height: 48px; font-size: .75rem; }
        .konversi-page .konversi-modal .modal-footer .btn-primary { flex-grow: 1.6; }
        .konversi-footer-hint { display: none; }
        .konversi-page .konversi-modal :is(.form-control, .form-select, .select2-search__field) { font-size: 16px; }
        .konversi-page .konversi-modal .form-label { font-size: .7rem; }
        .konversi-page .konversi-modal .select2-selection--single .select2-selection__rendered { font-size: 16px; }
        .konversi-page .konversi-modal .select2-container--open { max-width: 100%; }
        .konversi-page .konversi-modal .select2-results__option { min-height: 44px; font-size: .8rem; }
        .konversi-page .konversi-modal .select2-search--dropdown .select2-search__field { min-height: 44px; }
        .konversi-page .konversi-modal .select2-selection__choice { font-size: .75rem; }
        .konversi-input-card .obat-field.is-satuan, .konversi-input-card .obat-field.is-konversi, .konversi-input-card .obat-field.is-default,
        .konversi-batch-step .konversi-input-card .obat-field.is-satuan, .konversi-batch-step .konversi-input-card .obat-field.is-konversi { grid-column: 1 / -1; }
        .konversi-input-card-body { padding: .85rem; }
        .konversi-page .konversi-input-card-header .btn { min-height: 44px; }
        .konversi-default-box { min-height: 46px; font-size: .75rem; }
        .konversi-obat-context { padding: .9rem; gap: .6rem; }
        .konversi-obat-context strong { font-size: .86rem; }
        .konversi-batch-step { padding: .9rem; border-radius: 12px; }
        .konversi-target-scroll-hint { display: block; }
        .konversi-import-steps { gap: .4rem; }
        .konversi-import-steps li { font-size: .62rem; gap: .35rem; }
        .konversi-import-template { padding: .9rem; }
        .konversi-import-template .btn { width: 100%; }
        .konversi-import-upload { padding: .9rem; }
    }
    @media (max-width: 359.98px) {
        .konversi-hero { padding: 1rem; }
        .konversi-hero h1 { font-size: 1.6rem; }
        .konversi-page .obat-stat { display: block; }
        .konversi-page .obat-stat-icon { margin-bottom: .5rem; }
        .konversi-page .konversi-stat-total { display: flex; }
        .konversi-page .konversi-stat-total .obat-stat-icon { margin: 0; }
        .konversi-equation-heading small { display: none; }
        #tableKonversi tbody tr:not(.obat-detail-row) { grid-template-columns: 1fr; grid-template-areas: "identity" "stock" "status" "conversions" "action"; }
        .konversi-import-steps { flex-wrap: wrap; }
        .konversi-import-steps li { flex-basis: 100%; }
    }
</style>
