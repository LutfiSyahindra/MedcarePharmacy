<style>
    .etiket-page {
        --label-primary: #087f67;
        --label-primary-dark: #075d4d;
        --label-compound: #8a5a19;
    }

    .etiket-page .document-hero {
        background:
            radial-gradient(circle at 76% 12%, rgba(153, 246, 222, .25), transparent 30%),
            linear-gradient(135deg, #123f4a 0%, #087f67 56%, #1a9d7c 100%);
        box-shadow: 0 22px 50px rgba(8, 127, 103, .2);
    }

    .etiket-page .document-sheet { color: var(--label-primary); }
    .etiket-page .document-sheet.is-second { color: var(--label-compound); }
    .etiket-page .document-folder-front {
        background: linear-gradient(160deg, rgba(54, 182, 148, .96), rgba(8, 111, 91, .96));
    }

    .etiket-page .document-sync-note { border-color: #cfe9e1; color: #315f55; background: #f1faf7; }
    .etiket-page .document-sync-note > i { color: var(--label-primary); background: #dff4ed; }
    .etiket-page .document-heading-icon { color: var(--label-primary); background: #e8f7f2; }
    .etiket-page .document-refresh-button:hover { color: var(--label-primary); border-color: #aad8cb; background: #f2fbf8; }

    .etiket-page .document-metric.is-label-total .document-metric-icon,
    .etiket-page .document-metric.is-label-set .document-metric-icon { color: var(--label-primary); background: #e8f7f2; }
    .etiket-page .document-metric.is-non-compound .document-metric-icon { color: #2477c7; background: #eaf4ff; }
    .etiket-page .document-metric.is-compound .document-metric-icon { color: var(--label-compound); background: #fff5e6; }

    .etiket-page .label-template-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .etiket-page .document-template-card.is-non-compound .document-template-card-icon { color: #2477c7; background: #eaf4ff; }
    .etiket-page .document-template-card.is-compound .document-template-card-icon { color: var(--label-compound); background: #fff5e6; }
    .etiket-page .document-template-card-actions .btn-outline-success { color: var(--label-primary); border-color: #aed8cc; }
    .etiket-page .document-template-card-actions .btn-outline-success:hover { color: #fff; border-color: var(--label-primary); background: var(--label-primary); }
    .etiket-page .document-template-print { color: var(--label-primary); }
    .etiket-page .document-template-print:hover { color: #fff; border-color: var(--label-primary); background: var(--label-primary); }

    .type-dot.is-non-compound { background: #2477c7; }
    .type-dot.is-compound { background: var(--label-compound); }
    .etiket-page .document-type-tabs button:hover,
    .etiket-page .document-type-tabs button.is-active { color: var(--label-primary); border-color: #a9d9cb; background: #edf9f5; }

    .document-type-badge.is-non-compound { color: #1f67a9; background: #eaf4ff; }
    .document-type-badge.is-non-compound::before { background: #2477c7; }
    .document-type-badge.is-compound { color: #8a5a19; background: #fff5e6; }
    .document-type-badge.is-compound::before { background: #b47725; }

    .label-patient-cell,
    .label-content-cell { display: grid; gap: 3px; min-width: 150px; }
    .label-patient-cell strong,
    .label-content-cell strong { color: #334057; font-size: 13px; }
    .label-patient-cell small,
    .label-content-cell small { max-width: 210px; overflow: hidden; color: #8a95a7; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
    .label-content-cell strong { color: var(--label-primary-dark); }

    .etiket-page .document-action-button:hover { color: var(--label-primary); border-color: #a8d5c9; background: #f1faf7; }
    .etiket-page .document-action-button.is-print { border-color: var(--label-primary); background: var(--label-primary); }
    .etiket-page .document-action-button.is-print:hover { color: #fff; background: var(--label-primary-dark); }
    .etiket-page .document-table-footer .page-item.active .page-link { border-color: var(--label-primary); background: var(--label-primary); }

    @media (max-width: 1199.98px) {
        .etiket-page .label-branch-field { flex: 1 1 190px; }
    }

    @media (max-width: 767.98px) {
        .etiket-page .label-template-grid { grid-template-columns: 1fr; }
    }
</style>
