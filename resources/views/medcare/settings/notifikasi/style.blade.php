<style>
    .notif-settings-page {
        display: grid;
        gap: 18px;
    }

    .notif-settings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 22px;
        color: #102033;
        background: linear-gradient(135deg, #f8fbff 0%, #eef7f3 58%, #fff6e6 100%);
        border: 1px solid #dbe7f0;
        border-radius: 8px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, .07);
    }

    .notif-settings-kicker {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
        color: #0f766e;
        background: rgba(20, 184, 166, .12);
        border-radius: 6px;
    }

    .notif-settings-header h4,
    .notif-panel-title h5 {
        margin: 0;
        font-weight: 800;
        letter-spacing: 0;
    }

    .notif-settings-header p,
    .notif-panel-title p {
        margin: 4px 0 0;
        color: #64748b;
    }

    .notif-save-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 16px;
        color: #fff;
        font-weight: 800;
        background: #0f766e;
        border: 0;
        border-radius: 7px;
        box-shadow: 0 12px 24px rgba(15, 118, 110, .22);
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .notif-save-button:hover {
        background: #115e59;
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(15, 118, 110, .27);
    }

    .notif-save-button:disabled {
        opacity: .72;
        transform: none;
    }

    .notif-settings-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .notif-stat-tile,
    .notif-config-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .055);
    }

    .notif-stat-tile {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 16px;
    }

    .notif-stat-icon,
    .notif-panel-title > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        font-size: 22px;
    }

    .notif-stat-icon.is-pembelian {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .notif-stat-icon.is-penerimaan {
        color: #047857;
        background: #d1fae5;
    }

    .notif-stat-icon.is-retur_pembelian {
        color: #b45309;
        background: #fef3c7;
    }

    .notif-stat-tile strong {
        display: block;
        color: #111827;
        font-size: 24px;
        line-height: 1;
    }

    .notif-stat-tile span {
        display: block;
        margin-top: 4px;
        color: #334155;
        font-weight: 800;
    }

    .notif-stat-tile small {
        color: #64748b;
    }

    .notif-settings-form {
        display: grid;
        gap: 16px;
    }

    .notif-role-setting-callout {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        background: #eff6ff;
    }

    .notif-role-setting-callout > span {
        display: grid;
        place-items: center;
        flex: 0 0 42px;
        height: 42px;
        border-radius: 8px;
        color: #1d4ed8;
        background: #dbeafe;
        font-size: 22px;
    }

    .notif-role-setting-callout > div { flex: 1; }
    .notif-role-setting-callout strong { display: block; color: #1e293b; }
    .notif-role-setting-callout small { display: block; margin-top: 2px; color: #64748b; }
    .notif-role-setting-callout a {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #1d4ed8;
        font-weight: 800;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .notif-role-setting-callout { align-items: flex-start; flex-wrap: wrap; }
        .notif-role-setting-callout a { width: 100%; padding-left: 54px; }
    }

    .notif-config-panel {
        padding: 18px;
    }

    .notif-panel-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .notif-panel-title > span {
        color: #1d4ed8;
        background: #eff6ff;
    }

    .notif-module-grid,
    .notif-role-grid,
    .notif-behavior-grid {
        display: grid;
        gap: 12px;
    }

    .notif-module-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .notif-module-option {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 92px;
        padding: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .notif-module-option:hover,
    .notif-role-option:hover,
    .notif-behavior-row:hover {
        border-color: #93c5fd;
        box-shadow: 0 12px 24px rgba(30, 64, 175, .08);
        transform: translateY(-1px);
    }

    .notif-toggle-source {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .notif-module-switch {
        position: relative;
        width: 48px;
        height: 28px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background .18s ease;
        flex: 0 0 auto;
    }

    .notif-module-switch::after,
    .notif-behavior-row i::after {
        content: "";
        position: absolute;
        top: 4px;
        left: 4px;
        width: 20px;
        height: 20px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 3px 8px rgba(15, 23, 42, .18);
        transition: transform .18s ease;
    }

    .notif-module-option input:checked + .notif-module-switch,
    .notif-behavior-row input:checked + i {
        background: #0f766e;
    }

    .notif-module-option input:checked + .notif-module-switch::after,
    .notif-behavior-row input:checked + i::after {
        transform: translateX(20px);
    }

    .notif-module-copy strong,
    .notif-module-copy small {
        display: block;
    }

    .notif-module-copy strong {
        color: #111827;
        font-weight: 800;
    }

    .notif-module-copy small {
        margin-top: 4px;
        color: #64748b;
    }

    .notif-role-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .notif-role-option {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 72px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .notif-role-option span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        color: #334155;
        background: #f1f5f9;
        border-radius: 8px;
        font-size: 22px;
    }

    .notif-role-option input:checked + span {
        color: #fff;
        background: #1d4ed8;
    }

    .notif-role-option strong {
        font-weight: 800;
        color: #111827;
    }

    .notif-behavior-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .notif-behavior-row {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-height: 78px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .notif-behavior-row strong,
    .notif-behavior-row small {
        display: block;
    }

    .notif-behavior-row strong {
        color: #111827;
        font-weight: 800;
    }

    .notif-behavior-row small {
        margin-top: 3px;
        color: #64748b;
    }

    .notif-behavior-row i {
        position: relative;
        width: 48px;
        height: 28px;
        border-radius: 999px;
        background: #cbd5e1;
        flex: 0 0 auto;
        transition: background .18s ease;
    }

    .notif-limit-control {
        margin-top: 16px;
        padding: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .notif-limit-control label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #334155;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .notif-limit-control strong {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        min-height: 28px;
        color: #0f766e;
        background: #ccfbf1;
        border-radius: 6px;
    }

    .notif-limit-control input[type="range"] {
        width: 100%;
        accent-color: #0f766e;
    }

    @media (max-width: 991.98px) {
        .notif-settings-header {
            align-items: stretch;
            flex-direction: column;
        }

        .notif-save-button {
            width: 100%;
        }

        .notif-settings-grid,
        .notif-module-grid,
        .notif-behavior-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .notif-role-grid {
            grid-template-columns: 1fr;
        }

        .notif-config-panel,
        .notif-settings-header {
            padding: 16px;
        }
    }
</style>
