<style>
    .pos-printer-modal .modal-dialog { width: min(640px, calc(100% - 24px)); max-width: 640px; }
    .pos-printer-modal .modal-content { overflow: hidden; border: 0; border-radius: 20px; box-shadow: 0 24px 70px rgba(31, 43, 70, .25); }
    .pos-printer-modal .modal-header { display: flex; align-items: center; gap: 13px; padding: 20px 22px 17px; border-color: #e5e9f0; background: linear-gradient(145deg, #f4f8ff, #fff); }
    .pos-printer-modal-icon { display: grid; width: 48px; height: 48px; flex: 0 0 48px; place-items: center; color: #2879c7; border-radius: 14px; background: #e6f2ff; font-size: 25px; }
    .pos-printer-modal .modal-header > div { min-width: 0; flex: 1; }
    .pos-printer-modal .modal-header small { color: #397bb9; font-size: .72rem; font-weight: 800; letter-spacing: .09em; }
    .pos-printer-modal .modal-title { margin: 1px 0 2px; color: #27364f; font-size: 1.12rem; font-weight: 800; }
    .pos-printer-modal .modal-header p { margin: 0; color: #7d899c; font-size: .78rem; }
    .pos-printer-modal .modal-body { padding: 18px 22px 20px; background: #f7f9fc; }
    .pos-printer-state { display: flex; align-items: center; gap: 12px; padding: 14px; border: 1px solid #dae1eb; border-radius: 14px; background: #fff; }
    .pos-printer-state-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; color: #7f8b9c; border-radius: 12px; background: #edf1f5; font-size: 22px; }
    .pos-printer-state > div { min-width: 0; flex: 1; }
    .pos-printer-state small { display: block; margin-bottom: 2px; color: #8b95a5; font-size: .68rem; font-weight: 800; letter-spacing: .08em; }
    .pos-printer-state strong { display: block; overflow: hidden; color: #334158; font-size: .85rem; text-overflow: ellipsis; white-space: nowrap; }
    .pos-printer-state p { margin: 2px 0 0; color: #828da0; font-size: .75rem; }
    .pos-printer-state-badge { flex: 0 0 auto; padding: 5px 8px; color: #687486; border-radius: 99px; background: #edf1f5; font-size: .65rem; font-weight: 850; letter-spacing: .07em; }
    .pos-printer-state[data-state="connecting"] .pos-printer-state-icon { color: #a46c17; background: #fff5de; }
    .pos-printer-state[data-state="connecting"] .pos-printer-state-badge { color: #8b5a0e; background: #ffefc7; }
    .pos-printer-state[data-state="connected"] { border-color: #bee6d7; background: #f8fffc; }
    .pos-printer-state[data-state="connected"] .pos-printer-state-icon { color: #087554; background: #ddf7ed; }
    .pos-printer-state[data-state="connected"] .pos-printer-state-badge { color: #087554; background: #d9f6eb; }
    .pos-printer-state[data-state="error"] { border-color: #f1c8c8; background: #fffafa; }
    .pos-printer-state[data-state="error"] .pos-printer-state-icon { color: #b23a3a; background: #fde8e8; }
    .pos-printer-state[data-state="error"] .pos-printer-state-badge { color: #a43131; background: #fbe2e2; }
    .pos-printer-methods { display: grid; gap: 8px; margin-top: 13px; }
    .pos-printer-method { display: flex; width: 100%; align-items: center; gap: 11px; padding: 11px 12px; color: inherit; border: 1px solid #dde3ec; border-radius: 12px; background: #fff; text-align: left; transition: .16s ease; }
    .pos-printer-method:hover { border-color: #98b9dc; box-shadow: 0 6px 18px rgba(45, 86, 126, .08); transform: translateY(-1px); }
    .pos-printer-method:disabled { cursor: wait; opacity: .62; transform: none; }
    .pos-printer-method > span { display: grid; width: 38px; height: 38px; flex: 0 0 38px; place-items: center; color: #347bbd; border-radius: 10px; background: #eaf4ff; font-size: 20px; }
    .pos-printer-method > div { min-width: 0; flex: 1; }
    .pos-printer-method strong { display: block; color: #35445b; font-size: .82rem; }
    .pos-printer-method small { display: block; margin-top: 2px; color: #8490a2; font-size: .73rem; line-height: 1.35; }
    .pos-printer-method > i { color: #9da7b5; font-size: 18px; }
    .pos-printer-help { display: flex; align-items: flex-start; gap: 9px; margin-top: 12px; padding: 10px 11px; color: #68758a; border-radius: 10px; background: #eef3f8; }
    .pos-printer-help > i { flex: 0 0 auto; color: #397bb9; font-size: 18px; }
    .pos-printer-help p { margin: 0; font-size: .73rem; line-height: 1.45; }
    .pos-printer-options { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, .75fr); gap: 8px; margin-top: 12px; }
    .pos-printer-options label { display: flex; min-width: 0; align-items: center; gap: 10px; padding: 10px 11px; border: 1px solid #e0e5ed; border-radius: 10px; background: #fff; }
    .pos-printer-options label > span { min-width: 0; flex: 1; }
    .pos-printer-options strong, .pos-printer-options small { display: block; }
    .pos-printer-options strong { color: #46536a; font-size: .78rem; }
    .pos-printer-options small { margin-top: 1px; color: #929cab; font-size: .69rem; }
    .pos-printer-options .form-select { width: auto; min-width: 150px; border-color: #d6dde7; font-size: .75rem; }
    .pos-printer-cut-option .form-check-input { width: 2.1em; height: 1.15em; flex: 0 0 auto; margin: 0; }
    .pos-printer-modal .modal-footer { gap: 8px; padding: 13px 22px 18px; border: 0; }
    .pos-printer-release, .pos-printer-test, .pos-printer-done { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 7px; padding: 0 14px; border-radius: 9px; font-size: .78rem; font-weight: 750; }
    .pos-printer-release { margin-right: auto; color: #a43b3b; border: 1px solid #eccbcb; background: #fff; }
    .pos-printer-test { color: #31506a; border: 1px solid #cbd8e3; background: #f7fafc; }
    .pos-printer-test:disabled { color: #a5adbb; border-color: #e1e5eb; background: #f3f5f7; }
    .pos-printer-done { color: #fff; border: 0; background: #4f63d8; }

    @media (max-width: 720px) {
        .pos-printer-modal .modal-dialog { width: calc(100% - 12px); margin: 6px; }
        .pos-printer-modal .modal-header { align-items: flex-start; padding: 16px; }
        .pos-printer-modal .modal-header p { display: none; }
        .pos-printer-modal .modal-body { padding: 14px; }
        .pos-printer-state { align-items: flex-start; }
        .pos-printer-state-badge { display: none; }
        .pos-printer-options { grid-template-columns: 1fr; }
        .pos-printer-modal .modal-footer { flex-wrap: wrap; padding: 12px 14px 15px; }
        .pos-printer-release { order: 3; width: 100%; margin: 0; }
    }
</style>
