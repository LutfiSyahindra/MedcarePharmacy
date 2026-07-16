<style>
    .notification-page {
        display: grid;
        gap: 18px;
    }

    .notification-command,
    .notification-inbox,
    .notification-metric {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
    }

    .notification-command {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 22px;
        color: #102033;
        background: linear-gradient(135deg, #f8fbff 0%, #eef7f3 56%, #fff7ed 100%);
    }

    .notification-kicker {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        margin-bottom: 8px;
        color: #0f766e;
        background: rgba(20, 184, 166, .13);
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .notification-command h4 {
        margin: 0;
        color: #0f172a;
        font-weight: 800;
        letter-spacing: 0;
    }

    .notification-command p {
        margin: 5px 0 0;
        color: #64748b;
    }

    .notification-command-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    .notification-icon-button,
    .notification-mark-read,
    .notif-action-btn,
    .notification-modal-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 7px;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .notification-icon-button {
        width: 42px;
        height: 42px;
        color: #1d4ed8;
        background: #dbeafe;
        font-size: 20px;
    }

    .notification-mark-read {
        gap: 8px;
        min-height: 42px;
        padding: 0 14px;
        color: #fff;
        background: #0f766e;
        font-weight: 800;
        box-shadow: 0 12px 24px rgba(15, 118, 110, .22);
    }

    .notification-icon-button:hover,
    .notification-mark-read:hover,
    .notif-action-btn:hover,
    .notification-modal-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
    }

    .notification-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .notification-metric {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
    }

    .metric-icon,
    .notif-document-icon,
    .notification-modal-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 42px;
        height: 42px;
        border-radius: 8px;
        font-size: 22px;
    }

    .metric-icon.is-total {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .metric-icon.is-unread {
        color: #b45309;
        background: #fef3c7;
    }

    .metric-icon.is-action {
        color: #be123c;
        background: #ffe4e6;
    }

    .metric-icon.is-result {
        color: #047857;
        background: #d1fae5;
    }

    .notification-metric strong {
        display: block;
        color: #111827;
        font-size: 24px;
        line-height: 1;
    }

    .notification-metric span {
        display: block;
        margin-top: 4px;
        color: #475569;
        font-weight: 800;
    }

    .notification-inbox {
        padding: 18px;
    }

    .notification-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .notification-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .notification-filter {
        min-height: 36px;
        padding: 0 12px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        font-weight: 800;
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }

    .notification-filter.is-active,
    .notification-filter:hover {
        color: #fff;
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .notification-search {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: min(360px, 100%);
        min-height: 40px;
        padding: 0 12px;
        color: #64748b;
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 7px;
    }

    .notification-search input {
        width: 100%;
        border: 0;
        outline: 0;
        color: #0f172a;
        background: transparent;
        font-weight: 600;
    }

    .notification-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .notification-table thead th {
        color: #64748b;
        border: 0 !important;
        font-size: 11px;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .notification-table tbody tr {
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
    }

    .notification-table tbody tr.notif-unread td {
        background: #fffbeb;
    }

    .notification-table tbody td {
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .notification-table tbody td:first-child {
        border-left: 1px solid #e2e8f0;
        border-radius: 8px 0 0 8px;
        color: #64748b;
        font-weight: 800;
    }

    .notification-table tbody td:last-child {
        border-right: 1px solid #e2e8f0;
        border-radius: 0 8px 8px 0;
    }

    .notif-document {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 220px;
    }

    .notif-document-icon {
        color: #1d4ed8;
        background: #eff6ff;
    }

    .notif-document strong,
    .notif-document small,
    .notif-summary strong,
    .notif-summary span,
    .notif-summary small {
        display: block;
    }

    .notif-document strong {
        color: #111827;
        font-weight: 900;
    }

    .notif-document small {
        margin-top: 3px;
        color: #64748b;
        font-weight: 700;
    }

    .notif-summary {
        min-width: 280px;
    }

    .notif-summary strong {
        color: #0f172a;
        font-weight: 900;
    }

    .notif-summary span {
        margin-top: 3px;
        color: #475569;
    }

    .notif-summary small {
        margin-top: 5px;
        color: #64748b;
        font-weight: 700;
    }

    .notif-items-line {
        color: #0f766e !important;
    }

    .notif-status-stack {
        display: grid;
        gap: 5px;
        justify-items: start;
    }

    .notif-state,
    .notif-read,
    .notif-action-state {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 900;
    }

    .notif-state.is-success,
    .notif-read.is-read {
        color: #047857;
        background: #d1fae5;
    }

    .notif-state.is-warning,
    .notif-read.is-unread,
    .notif-action-state.is-actionable {
        color: #b45309;
        background: #fef3c7;
    }

    .notif-state.is-danger {
        color: #be123c;
        background: #ffe4e6;
    }

    .notif-state.is-info {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .notif-state.is-muted,
    .notif-action-state.is-passive {
        color: #475569;
        background: #f1f5f9;
    }

    .notif-table-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notif-action-btn {
        width: 34px;
        height: 34px;
        color: #475569;
        background: #f1f5f9;
        text-decoration: none;
        font-size: 18px;
    }

    .notif-action-btn.is-primary {
        color: #fff;
        background: #1d4ed8;
    }

    .notification-modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        overflow: hidden;
    }

    .notification-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px;
        color: #102033;
        background: linear-gradient(135deg, #f8fbff, #eef7f3);
        border-bottom: 1px solid #e2e8f0;
    }

    .notification-modal-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .notification-modal-icon {
        color: #fff;
        background: #1d4ed8;
    }

    .notification-modal-title h5 {
        margin: 0;
        color: #0f172a;
        font-weight: 900;
    }

    .notification-modal-title small {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-weight: 700;
    }

    .notification-modal-body {
        padding: 18px;
    }

    .notification-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .notification-detail-field {
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .notification-detail-field span,
    .notification-detail-field strong {
        display: block;
    }

    .notification-detail-field span {
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .notification-detail-field strong {
        margin-top: 4px;
        color: #0f172a;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .notification-message-panel {
        margin-top: 14px;
        padding: 14px;
        color: #334155;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .notification-items-panel {
        margin-top: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .notification-items-panel.is-empty {
        padding: 14px;
        background: #f8fafc;
    }

    .notification-items-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .notification-items-heading strong,
    .notification-items-heading small {
        display: block;
    }

    .notification-items-heading strong {
        color: #0f172a;
        font-weight: 900;
    }

    .notification-items-heading small {
        color: #64748b;
        font-weight: 800;
    }

    .notification-items-table-wrap {
        overflow-x: auto;
    }

    .notification-items-table {
        width: 100%;
        min-width: 820px;
        border-collapse: collapse;
    }

    .notification-items-table th,
    .notification-items-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .notification-items-table th {
        color: #64748b;
        background: #fff;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .notification-items-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .notification-items-table strong,
    .notification-items-table small,
    .notification-items-table span {
        display: block;
    }

    .notification-items-table strong {
        color: #0f172a;
        font-weight: 900;
    }

    .notification-items-table small,
    .notification-items-table span {
        color: #64748b;
        font-weight: 700;
    }

    .notification-modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .notification-modal-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .notification-modal-action {
        gap: 7px;
        min-height: 38px;
        padding: 0 12px;
        color: #475569;
        background: #e2e8f0;
        font-weight: 900;
    }

    .notification-modal-action.is-success {
        color: #fff;
        background: #047857;
    }

    .notification-modal-action.is-danger {
        color: #fff;
        background: #be123c;
    }

    .notification-modal-action.is-page {
        color: #1d4ed8;
        background: #dbeafe;
        text-decoration: none;
    }

    .notification-modal .modal-dialog {
        max-width: min(1120px, calc(100% - 32px));
    }

    .notification-modal-content {
        max-height: calc(100vh - 3.5rem);
        background: #f8fafc;
        border: 1px solid rgba(148, 163, 184, .26);
        box-shadow: 0 28px 90px rgba(15, 23, 42, .28);
    }

    .notification-modal-header {
        padding: 18px 20px;
        background: linear-gradient(135deg, #ffffff 0%, #f2faf7 54%, #fff7ed 100%);
    }

    .notification-modal-title {
        min-width: 0;
    }

    .notification-modal-title h5,
    .notification-modal-title small {
        overflow-wrap: anywhere;
    }

    .notification-modal-header-actions {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    .notification-modal-icon {
        box-shadow: 0 12px 26px rgba(29, 78, 216, .24);
    }

    .notification-modal-icon.is-success,
    .notification-kind-pill.is-success,
    .notification-modal-status.is-success {
        color: #047857;
        background: #d1fae5;
    }

    .notification-modal-icon.is-danger,
    .notification-kind-pill.is-danger,
    .notification-modal-status.is-danger {
        color: #be123c;
        background: #ffe4e6;
    }

    .notification-modal-icon.is-warning,
    .notification-kind-pill.is-warning,
    .notification-modal-status.is-warning {
        color: #b45309;
        background: #fef3c7;
    }

    .notification-modal-icon.is-info,
    .notification-kind-pill.is-info,
    .notification-modal-status.is-info,
    .notification-modal-icon.is-primary,
    .notification-kind-pill.is-primary,
    .notification-modal-status.is-primary {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .notification-modal-icon.is-muted,
    .notification-kind-pill.is-muted,
    .notification-modal-status.is-muted {
        color: #475569;
        background: #f1f5f9;
    }

    .notification-modal-status {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .notification-modal-body {
        padding: 18px;
        background: #f8fafc;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .notification-modal-empty {
        display: grid;
        justify-items: center;
        gap: 6px;
        padding: 44px 18px;
        text-align: center;
        color: #64748b;
    }

    .notification-modal-empty span,
    .notification-items-panel.is-empty > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        color: #1d4ed8;
        background: #dbeafe;
        border-radius: 8px;
        font-size: 24px;
    }

    .notification-modal-empty strong,
    .notification-modal-empty small,
    .notification-items-panel.is-empty strong,
    .notification-items-panel.is-empty small {
        display: block;
    }

    .notification-modal-empty strong,
    .notification-items-panel.is-empty strong {
        color: #0f172a;
        font-weight: 900;
    }

    .notification-modal-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(230px, 280px);
        gap: 16px;
        padding: 16px;
        background: #fff;
        border: 1px solid #dbe3ef;
        border-top: 4px solid #1d4ed8;
        border-radius: 8px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .07);
    }

    .notification-modal-hero.is-success {
        border-top-color: #047857;
    }

    .notification-modal-hero.is-danger {
        border-top-color: #be123c;
    }

    .notification-modal-hero.is-warning {
        border-top-color: #d97706;
    }

    .notification-modal-hero.is-muted {
        border-top-color: #64748b;
    }

    .notification-hero-main,
    .notification-hero-value {
        min-width: 0;
    }

    .notification-kind-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .notification-hero-main h6 {
        margin: 11px 0 5px;
        color: #0f172a;
        font-size: 21px;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .notification-hero-main p {
        margin: 0;
        color: #475569;
        font-weight: 600;
        line-height: 1.55;
    }

    .notification-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 13px;
    }

    .notification-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 28px;
        padding: 0 9px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 800;
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .notification-hero-value {
        display: grid;
        align-content: center;
        gap: 4px;
        padding: 15px;
        background: #0f766e;
        border-radius: 8px;
        color: #ecfeff;
    }

    .notification-hero-value span,
    .notification-hero-value small {
        font-weight: 800;
        opacity: .88;
    }

    .notification-hero-value strong {
        color: #fff;
        font-size: 26px;
        line-height: 1.1;
        overflow-wrap: anywhere;
    }

    .notification-modal-tabs {
        display: flex;
        gap: 8px;
        margin-top: 14px;
        padding: 5px;
        background: #e2e8f0;
        border-radius: 8px;
    }

    .notification-modal-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        flex: 1 1 0;
        min-height: 38px;
        color: #475569;
        background: transparent;
        border: 0;
        border-radius: 7px;
        font-weight: 900;
        transition: color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .notification-modal-tab.is-active {
        color: #0f172a;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .09);
    }

    .notification-modal-pane {
        display: none;
        margin-top: 14px;
    }

    .notification-modal-pane.is-active {
        display: block;
    }

    .notification-insight-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .notification-insight-card {
        display: flex;
        gap: 10px;
        min-width: 0;
        padding: 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .notification-insight-card > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        color: #1d4ed8;
        background: #dbeafe;
        border-radius: 8px;
        font-size: 20px;
    }

    .notification-insight-card.is-success > span {
        color: #047857;
        background: #d1fae5;
    }

    .notification-insight-card.is-warning > span {
        color: #b45309;
        background: #fef3c7;
    }

    .notification-insight-card.is-danger > span {
        color: #be123c;
        background: #ffe4e6;
    }

    .notification-insight-card div {
        min-width: 0;
    }

    .notification-insight-card small,
    .notification-insight-card strong,
    .notification-insight-card em {
        display: block;
    }

    .notification-insight-card small {
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .notification-insight-card strong {
        margin-top: 3px;
        color: #0f172a;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .notification-insight-card em {
        margin-top: 2px;
        color: #64748b;
        font-size: 12px;
        font-style: normal;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .notification-message-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        background: #fff;
        border-left: 4px solid #1d4ed8;
    }

    .notification-message-panel.is-success {
        border-left-color: #047857;
    }

    .notification-message-panel.is-danger {
        border-left-color: #be123c;
    }

    .notification-message-panel.is-warning {
        border-left-color: #d97706;
    }

    .notification-message-panel strong,
    .notification-message-panel span,
    .notification-message-panel small {
        display: block;
    }

    .notification-message-panel strong {
        color: #0f172a;
        font-weight: 900;
    }

    .notification-message-panel span {
        margin-top: 4px;
        color: #475569;
        line-height: 1.55;
    }

    .notification-message-panel small {
        flex: 0 0 auto;
        color: #64748b;
        font-weight: 800;
    }

    .notification-timeline {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 14px;
    }

    .notification-timeline-item {
        display: flex;
        gap: 10px;
        min-width: 0;
        padding: 12px;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
    }

    .notification-timeline-item > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        color: #0f766e;
        background: #ccfbf1;
        border-radius: 8px;
        font-size: 18px;
    }

    .notification-timeline-item strong,
    .notification-timeline-item small,
    .notification-timeline-item em {
        display: block;
        overflow-wrap: anywhere;
    }

    .notification-timeline-item strong {
        color: #0f172a;
        font-weight: 900;
    }

    .notification-timeline-item small,
    .notification-timeline-item em {
        color: #64748b;
        font-style: normal;
        font-weight: 700;
    }

    .notification-detail-field span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .notification-items-panel {
        margin-top: 0;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
    }

    .notification-items-table-wrap {
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .notification-items-panel.is-empty {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 110px;
        background: #fff;
    }

    .notification-items-search {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: min(330px, 100%);
        min-height: 38px;
        padding: 0 11px;
        color: #64748b;
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 7px;
    }

    .notification-items-search input {
        width: 100%;
        min-width: 0;
        color: #0f172a;
        background: transparent;
        border: 0;
        outline: 0;
        font-weight: 700;
    }

    .notification-items-table {
        min-width: 960px;
    }

    .notification-items-table tbody tr {
        transition: background .18s ease;
    }

    .notification-items-table tbody tr:hover {
        background: #f8fafc;
    }

    .notification-item-number {
        color: #64748b;
        font-weight: 900;
    }

    .notification-item-note {
        min-width: 180px;
        color: #475569;
        font-weight: 700;
    }

    .notification-items-empty-state {
        display: none;
        justify-items: center;
        gap: 4px;
        padding: 28px;
        color: #64748b;
        text-align: center;
    }

    .notification-items-empty-state.is-visible {
        display: grid;
    }

    .notification-items-empty-state i {
        color: #1d4ed8;
        font-size: 28px;
    }

    .notification-items-empty-state strong,
    .notification-items-empty-state small {
        display: block;
    }

    .notification-modal-footer {
        padding: 14px 18px;
        background: #fff;
    }

    .notification-modal-action-group.is-secondary {
        justify-content: flex-end;
    }

    .notification-modal-action {
        color: #334155;
        text-decoration: none;
        cursor: pointer;
    }

    .notification-modal-action:disabled,
    .notification-modal-action.is-loading {
        cursor: progress;
        opacity: .72;
        transform: none;
    }

    .notification-modal-action.is-static {
        cursor: default;
        color: #64748b;
        background: #f1f5f9;
        box-shadow: none;
    }

    .notification-modal-action.is-primary {
        color: #fff;
        background: #1d4ed8;
    }

    .notification-modal-action.is-warning {
        color: #111827;
        background: #facc15;
    }

    .notification-modal-action.is-copy {
        color: #0f766e;
        background: #ccfbf1;
    }

    .notification-modal-action.is-detail {
        color: #7c2d12;
        background: #ffedd5;
        text-decoration: none;
    }

    @media (max-width: 991.98px) {
        .notification-command,
        .notification-toolbar,
        .notification-modal-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .notification-command-actions,
        .notification-search {
            width: 100%;
        }

        .notification-metrics,
        .notification-detail-grid,
        .notification-insight-grid,
        .notification-timeline {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .notification-modal-hero {
            grid-template-columns: 1fr;
        }

        .notification-items-heading,
        .notification-message-panel {
            align-items: stretch;
            flex-direction: column;
        }

        .notification-items-search,
        .notification-modal-action-group,
        .notification-modal-action-group.is-secondary {
            width: 100%;
        }

        .notification-modal-action-group.is-secondary {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .notification-metrics,
        .notification-detail-grid,
        .notification-insight-grid,
        .notification-timeline {
            grid-template-columns: 1fr;
        }

        .notification-command,
        .notification-inbox {
            padding: 15px;
        }

        .notification-modal .modal-dialog {
            max-width: calc(100% - 16px);
        }

        .notification-modal-header,
        .notification-modal-body,
        .notification-modal-footer {
            padding: 14px;
        }

        .notification-modal-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .notification-modal-header-actions {
            justify-content: space-between;
            width: 100%;
        }

        .notification-modal-tabs {
            flex-direction: column;
        }

        .notification-modal-action {
            flex: 1 1 auto;
            min-width: 0;
        }
    }
</style>
