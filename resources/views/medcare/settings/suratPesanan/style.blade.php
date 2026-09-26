<style>
    .sp-settings-page { display: grid; gap: 18px; }
    .sp-hero { display: flex; align-items: center; justify-content: space-between; gap: 22px; padding: 24px; border: 1px solid #dce7ef; border-radius: 10px; background: linear-gradient(135deg, #f8fbff 0%, #eef9f6 55%, #fff8e9 100%); box-shadow: 0 14px 34px rgba(15, 23, 42, .07); }
    .sp-kicker { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 8px; padding: 5px 9px; border-radius: 6px; color: #0f766e; background: rgba(20, 184, 166, .12); font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .sp-hero h4 { margin: 0; color: #102033; font-weight: 800; }
    .sp-hero-copy > p { margin: 5px 0 12px; color: #64748b; }
    .sp-priority-note { display: inline-flex; align-items: center; gap: 8px; color: #475569; font-size: 12px; }
    .sp-priority-note i { color: #0f766e; font-size: 18px; }
    .sp-hero-actions { display: flex; flex: 0 0 auto; gap: 9px; }
    .sp-save-button, .sp-secondary-button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 0 15px; border-radius: 7px; font-weight: 800; }
    .sp-save-button { color: #fff; border: 1px solid #0f766e; background: #0f766e; box-shadow: 0 10px 22px rgba(15, 118, 110, .2); }
    .sp-save-button:hover { color: #fff; background: #115e59; }
    .sp-save-button b { min-width: 21px; height: 21px; padding: 2px 6px; border-radius: 999px; color: #0f766e; background: #fff; font-size: 11px; }
    .sp-secondary-button { color: #475569; border: 1px solid #cbd5e1; background: rgba(255,255,255,.75); }
    .sp-save-button:disabled, .sp-secondary-button:disabled { opacity: .55; box-shadow: none; }

    .sp-summary-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
    .sp-summary-card { display: grid; grid-template-columns: 42px 1fr; gap: 11px; min-width: 0; padding: 14px; text-align: left; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; box-shadow: 0 9px 22px rgba(15, 23, 42, .045); transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
    .sp-summary-card:hover, .sp-summary-card.is-active { transform: translateY(-2px); border-color: #94a3b8; box-shadow: 0 13px 27px rgba(15, 23, 42, .08); }
    .sp-summary-icon { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; grid-row: span 2; border-radius: 8px; font-size: 21px; }
    .sp-summary-card strong { display: block; color: #0f172a; font-size: 20px; line-height: 1.05; }
    .sp-summary-card small { display: block; margin-top: 3px; color: #475569; font-weight: 700; }
    .sp-summary-card em { grid-column: 2; color: #94a3b8; font-size: 10px; font-style: normal; }
    .sp-summary-card.is-overview .sp-summary-icon { color: #475569; background: #f1f5f9; }
    .sp-summary-card.is-narkotika .sp-summary-icon { color: #b91c1c; background: #fee2e2; }
    .sp-summary-card.is-psikotropika .sp-summary-icon { color: #6d28d9; background: #ede9fe; }
    .sp-summary-card.is-oot .sp-summary-icon { color: #047857; background: #d1fae5; }
    .sp-summary-card.is-prekursor .sp-summary-icon { color: #b45309; background: #fef3c7; }

    .sp-workspace { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; box-shadow: 0 14px 34px rgba(15, 23, 42, .06); }
    .sp-toolbar { display: grid; grid-template-columns: minmax(280px, 1fr) 180px 180px auto; align-items: center; gap: 10px; padding: 15px; border-bottom: 1px solid #e8eef4; }
    .sp-search-wrap { position: relative; }
    .sp-search-wrap > i { position: absolute; z-index: 2; top: 50%; left: 13px; transform: translateY(-50%); color: #94a3b8; font-size: 19px; }
    .sp-search-wrap input { height: 40px; padding-left: 40px; padding-right: 38px; border-color: #dbe4ec; border-radius: 7px; }
    .sp-search-wrap button { position: absolute; top: 50%; right: 9px; display: none; padding: 2px 5px; transform: translateY(-50%); color: #64748b; border: 0; background: transparent; }
    .sp-toolbar .form-select, .sp-bulk-controls .form-select { height: 40px; border-color: #dbe4ec; border-radius: 7px; }
    .sp-result-count { display: flex; align-items: baseline; gap: 5px; padding: 0 4px; white-space: nowrap; }
    .sp-result-count strong { color: #0f766e; font-size: 17px; }
    .sp-result-count span { color: #64748b; font-size: 11px; }
    .sp-bulk-bar { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 10px 15px; border-bottom: 1px solid #e8eef4; background: #f8fafc; }
    .sp-bulk-bar > div:first-child { display: flex; align-items: center; gap: 8px; color: #475569; font-size: 12px; font-weight: 700; }
    .sp-bulk-bar > div:first-child i { color: #0f766e; font-size: 18px; }
    .sp-bulk-controls { display: flex; align-items: center; gap: 8px; }
    .sp-bulk-controls .form-select { width: 205px; height: 34px; padding-top: 4px; padding-bottom: 4px; font-size: 12px; }
    .sp-bulk-controls .btn { height: 34px; padding: 0 12px; color: #0f766e; border: 1px solid #99d5cc; background: #ecfdf5; font-size: 12px; font-weight: 800; }

    .sp-table-wrap { max-height: 620px; }
    .sp-table { min-width: 940px; }
    .sp-table thead th { position: sticky; z-index: 3; top: 0; padding: 11px 14px; color: #64748b; background: #f8fafc; border-bottom: 1px solid #dfe7ef; font-size: 10px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .sp-table tbody td { padding: 11px 14px; border-color: #edf2f7; }
    .sp-setting-row.is-dirty { background: #fffdf3; }
    .sp-classification { display: flex; align-items: center; gap: 10px; min-width: 280px; }
    .sp-classification.sp-depth-1 { padding-left: 22px; }
    .sp-classification.sp-depth-2 { padding-left: 44px; }
    .sp-level-node { display: inline-flex; align-items: center; justify-content: center; width: 33px; height: 33px; flex: 0 0 33px; color: #0f766e; border: 1px solid #d7eee9; border-radius: 7px; background: #f0fdfa; font-size: 17px; }
    .sp-classification strong { display: block; color: #1e293b; font-size: 12px; }
    .sp-classification small { display: flex; align-items: center; gap: 7px; margin-top: 3px; color: #94a3b8; font-size: 10px; }
    .sp-classification code { padding: 2px 5px; color: #475569; border-radius: 4px; background: #f1f5f9; font-size: 9px; }
    .sp-level-badge, .sp-type-badge { display: inline-flex; align-items: center; width: fit-content; border-radius: 999px; font-size: 10px; font-weight: 800; }
    .sp-level-badge { padding: 5px 8px; color: #475569; background: #f1f5f9; }
    .sp-level-badge.is-golongan { color: #1d4ed8; background: #dbeafe; }
    .sp-level-badge.is-main_golongan { color: #6d28d9; background: #ede9fe; }
    .sp-level-badge.is-sub_golongan { color: #047857; background: #d1fae5; }
    .sp-medicine-count { display: inline-flex; justify-content: center; min-width: 29px; padding: 4px 7px; color: #475569; border-radius: 6px; background: #f1f5f9; font-size: 11px; font-weight: 800; }
    .sp-effective-wrap { display: grid; gap: 4px; }
    .sp-effective-wrap small { color: #94a3b8; font-size: 9px; }
    .sp-type-badge { gap: 5px; padding: 5px 8px; }
    .sp-type-badge.is-regular { color: #475569; background: #f1f5f9; }
    .sp-type-badge.is-narkotika { color: #b91c1c; background: #fee2e2; }
    .sp-type-badge.is-psikotropika { color: #6d28d9; background: #ede9fe; }
    .sp-type-badge.is-oot { color: #047857; background: #d1fae5; }
    .sp-type-badge.is-prekursor { color: #b45309; background: #fef3c7; }
    .sp-type-select { min-width: 190px; height: 37px; border-color: #dbe4ec; border-radius: 7px; font-size: 11px; font-weight: 700; }
    .sp-setting-row.is-dirty .sp-type-select { border-color: #d5a411; box-shadow: 0 0 0 2px rgba(245, 158, 11, .1); }
    .sp-empty-state, .sp-no-results { padding: 48px 20px !important; text-align: center; color: #94a3b8; }
    .sp-empty-state i, .sp-no-results i { display: block; margin-bottom: 6px; font-size: 34px; }
    .sp-empty-state strong, .sp-no-results strong { display: block; color: #475569; }
    .sp-empty-state span, .sp-no-results span { display: block; margin-top: 4px; font-size: 11px; }
    .sp-workspace-footer { display: flex; justify-content: space-between; gap: 18px; padding: 11px 15px; color: #64748b; border-top: 1px solid #e8eef4; background: #f8fafc; font-size: 10px; }
    .sp-workspace-footer i { margin-right: 4px; color: #0f766e; }

    @media (max-width: 1199.98px) { .sp-summary-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } .sp-toolbar { grid-template-columns: 1fr 170px 170px; } .sp-result-count { display: none; } }
    @media (max-width: 767.98px) { .sp-hero { align-items: stretch; flex-direction: column; } .sp-hero-actions { display: grid; grid-template-columns: 1fr 1fr; } .sp-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .sp-toolbar { grid-template-columns: 1fr; } .sp-bulk-bar, .sp-workspace-footer { align-items: stretch; flex-direction: column; } .sp-bulk-controls { display: grid; grid-template-columns: 1fr auto; } .sp-bulk-controls .form-select { width: 100%; } }
    @media (max-width: 480px) { .sp-summary-grid { grid-template-columns: 1fr; } .sp-hero-actions { grid-template-columns: 1fr; } }
</style>
