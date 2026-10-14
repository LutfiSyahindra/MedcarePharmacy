<style>
    #pembelianModal .modal-dialog { width: 100%; max-width: none; height: 100%; margin: 0; }
    #pembelianModal .modal-content { height: 100%; max-height: 100dvh; border: 0; border-radius: 0; }
    #pembelianModal .modal-header { padding: .75rem 1.25rem; }
    #pembelianModal .modal-body { min-height: 0; max-height: none !important; padding: 1rem 1.25rem; scroll-padding-block: 1rem; }
    #pembelianModal .purchase-form-layout { display: block; }
    #pembelianModal .purchase-form-main { min-width: 0; }
    #pembelianModal .purchase-notes-details summary { cursor: pointer; font-size: .8rem; color: var(--purchase-text); }
    #pembelianModal .purchase-modal-header-meta { min-width: 0; }
    #pembelianModal #purchaseDraftStatus { max-width: 45vw; white-space: normal; overflow-wrap: anywhere; }
    #pembelianModal .purchase-form-section-header { gap: .75rem; flex-wrap: wrap; padding: .75rem 1rem; }
    #pembelianModal .purchase-form-section-title > div { min-width: 0; }
    #pembelianModal .purchase-form-section-title small { margin-top: .2rem; }
    #pembelianModal .form-label { margin-bottom: .35rem; font-size: .8rem; }
    #pembelianModal .form-control, #pembelianModal .form-select, #pembelianModal .btn { min-height: 40px; }
    #pembelianModal .select2-container--default .select2-selection--single { min-height: 40px; height: 40px; }
    #pembelianModal .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
    #pembelianModal .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    #pembelianModal .purchase-required, #pembelianModal .purchase-row-errors { color: var(--purchase-danger); }
    #pembelianModal .purchase-number-mode { display: inline-block; margin-top: .3rem; font-size: .75rem; color: var(--purchase-primary-strong); }
    #pembelianModal .purchase-detail-actions { gap: .5rem; }
    #pembelianModal .purchase-items-toolbar, #pembelianModal .purchase-bulk-toolbar, #pembelianModal .purchase-paste-actions, #pembelianModal .purchase-picker-pagination { display: flex; align-items: center; flex-wrap: wrap; gap: .65rem; margin-bottom: .85rem; }
    #pembelianModal .purchase-items-toolbar > input { flex: 1 1 240px; max-width: 460px; }
    #pembelianModal .purchase-filter-check { display: inline-flex; align-items: center; gap: .5rem; margin: 0; min-height: 40px; }
    #pembelianModal .purchase-filter-check input { margin: 0; }
    #pembelianModal .purchase-bulk-toolbar { padding: .75rem; border-radius: 8px; background: var(--purchase-soft); }
    #pembelianModal .purchase-bulk-toolbar select { width: auto; }
    #pembelianModal .purchase-bulk-toolbar input { width: 100px; }
    #pembelianModal .purchase-paste-panel { padding: 1rem; margin-bottom: 1rem; border: 1px solid var(--purchase-border); border-radius: 8px; background: #f8fafc; }
    #pembelianModal .purchase-paste-panel > small { display: block; margin: .5rem 0 .75rem; }
    #pembelianModal .purchase-paste-preview-wrap { max-height: 280px; overflow: auto; }
    #pembelianModal .purchase-picker-pagination { justify-content: flex-end; margin: 0; padding: .5rem .9rem; border-bottom: 1px solid var(--purchase-border); }
    #pembelianModal .purchase-detail-summary { gap: .6rem; margin-bottom: .75rem; }
    #pembelianModal .purchase-form-section { overflow: visible; }
    #pembelianModal .purchase-entry-table-wrap { max-height: 60vh; overflow: auto; }
    #pembelianModal .purchase-entry-table { width: 100%; min-width: 1120px; margin: 0; table-layout: fixed; }
    #pembelianModal .purchase-entry-table th { position: sticky; top: 0; z-index: 2; background: #f1f5f9; color: var(--purchase-text); padding: .7rem .5rem; vertical-align: middle; font-size: .75rem; }
    #pembelianModal .purchase-selection-column { width: 48px; }
    #pembelianModal .purchase-medicine-column { width: 25%; }
    #pembelianModal .purchase-unit-column { width: 20%; }
    #pembelianModal .purchase-qty-column { width: 80px; }
    #pembelianModal .purchase-price-column { width: 14%; }
    #pembelianModal .purchase-subtotal-column { width: 14%; }
    #pembelianModal .purchase-actions-column { width: 190px; }
    #pembelianModal .purchase-detail-row > td { padding: .6rem .5rem; vertical-align: top; border-bottom: 1px solid var(--purchase-border); background: #fff; }
    #pembelianModal .purchase-detail-row.is-selected > td { background: #f0fdfa; }
    #pembelianModal .purchase-detail-row.has-issues > td:first-child { box-shadow: inset 3px 0 var(--purchase-danger); }
    #pembelianModal .purchase-row-selection { text-align: center; }
    #pembelianModal .purchase-detail-number { display: block; width: auto; height: auto; margin: 0 0 .45rem; padding: 0; background: none; color: var(--purchase-muted); border-radius: 0; font-size: .75rem; }
    #pembelianModal .purchase-row-check, #pembelianModal #purchaseSelectVisible { width: 18px; height: 18px; margin: .2rem 0; }
    #pembelianModal .purchase-cell-label { position: absolute; width: 1px; height: 1px; padding: 0; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
    #pembelianModal .purchase-current-stock { display: block; margin-top: .4rem; padding: 0; background: none; border: 0; font-size: .75rem; }
    #pembelianModal .purchase-current-stock i { margin-right: .25rem; }
    #pembelianModal .purchase-row-errors, #pembelianModal .purchase-unit-price, #pembelianModal .purchase-unit-warning { display: block; max-width: 100%; margin-top: .35rem; font-size: .75rem; line-height: 1.45; white-space: normal; overflow-wrap: anywhere; }
    #pembelianModal .purchase-unit-price { color: var(--purchase-muted); }
    #pembelianModal .purchase-price-field input, #pembelianModal .purchase-subtotal-field input { text-align: right; font-variant-numeric: tabular-nums; padding-inline: .5rem; }
    #pembelianModal .purchase-subtotal-field input { color: var(--purchase-primary-strong); background: var(--purchase-soft); font-weight: 700; }
    #pembelianModal .purchase-item-adjustments { border: 0; background: transparent; }
    #pembelianModal .purchase-item-adjustments summary { display: block; padding: .1rem 0 .4rem; color: var(--purchase-text); font-size: .78rem; cursor: pointer; list-style: none; }
    #pembelianModal .purchase-item-adjustments summary::-webkit-details-marker { display: none; }
    #pembelianModal .purchase-adjustments-value { display: block; margin-top: .2rem; color: var(--purchase-muted); font-size: .72rem; overflow-wrap: anywhere; }
    #pembelianModal .purchase-adjustment-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; margin: .5rem 0; }
    #pembelianModal .purchase-adjustment-fields .form-label { font-size: .72rem; }
    #pembelianModal .purchase-adjustment-fields input { padding-inline: .4rem; }
    #pembelianModal .purchase-adjustment-hint { display: block; margin-bottom: .5rem; color: var(--purchase-muted); font-size: .72rem; }
    #pembelianModal .remove-detail { min-height: 32px; padding: .25rem .5rem; font-size: .72rem; }
    #pembelianModal .purchase-add-another { display: block; width: 100%; margin-top: .75rem; border-style: dashed; }
    #pembelianModal .purchase-form-aside { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    #pembelianModal .purchase-form-aside > .purchase-form-section { margin: 0; }
    #pembelianModal .purchase-form-aside .row > div { width: 50%; }
    #pembelianModal .purchase-total-panel { display: block; padding: 1rem; margin: 0; }
    #pembelianModal .purchase-total-stats { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .75rem; margin: .8rem 0; }
    #pembelianModal .purchase-total-stats span { padding: 0; background: none; }
    #pembelianModal .purchase-grand-total { display: flex; justify-content: space-between; flex-wrap: wrap; gap: .5rem; padding-top: .75rem; border-top: 1px solid var(--purchase-border); }
    #pembelianModal .purchase-save-hint { grid-column: 1 / -1; margin: 0; color: var(--purchase-muted); font-size: .8rem; }
    #pembelianModal .purchase-form-footer { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .75rem; }
    #pembelianModal .purchase-mobile-total { display: flex; align-items: baseline; flex-wrap: wrap; gap: .75rem; color: var(--purchase-muted); font-size: .8rem; }
    #pembelianModal .purchase-mobile-total strong { color: var(--purchase-primary-strong); font-size: 1.15rem; }
    #pembelianModal .is-invalid + .select2 .select2-selection { border-color: var(--purchase-danger); }
    #pembelianModal summary:focus-visible, #pembelianModal .btn:focus-visible { outline: 2px solid var(--purchase-primary); outline-offset: -2px; }
    @media (max-width: 767.98px) {
        #pembelianModal .modal-header { position: relative; display: block; padding: .7rem 3.5rem .7rem .85rem; }
        #pembelianModal .modal-body { padding: .75rem; }
        #pembelianModal .modal-title-icon, #pembelianModal .modal-subtitle { display: none; }
        #pembelianModal .modal-title { font-size: 1rem; white-space: normal; }
        #pembelianModal #purchaseDraftStatus { display: inline-flex; max-width: 100%; font-size: .65rem; padding: .25rem .5rem; }
        #pembelianModal .purchase-modal-header-meta { position: static; display: block; width: auto; margin: .3rem 0 0; transform: none; }
        #pembelianModal .modal-header .btn-close { position: absolute; right: .4rem; top: .6rem; margin: 0; }
        #pembelianModal .purchase-form-section-header, #pembelianModal .purchase-form-section-body { padding: .75rem; }
        #pembelianModal .purchase-detail-actions { width: 100%; }
        #pembelianModal .purchase-detail-actions .btn { flex: 1 1 140px; justify-content: center; }
        #pembelianModal .purchase-entry-table-wrap { max-height: none; overflow: visible; }
        #pembelianModal .purchase-entry-table { display: block; min-width: 0; }
        #pembelianModal .purchase-entry-table thead { display: none; }
        #pembelianModal .purchase-entry-table tbody { display: block; }
        #pembelianModal .purchase-detail-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); border: 1px solid var(--purchase-border); border-radius: 8px; margin-bottom: .75rem; padding: .5rem; background: #fff; }
        #pembelianModal .purchase-detail-row > td { display: block; border: 0; padding: .4rem; min-width: 0; background: transparent; }
        #pembelianModal .purchase-detail-row > .purchase-row-selection { grid-column: 1 / -1; display: flex; align-items: center; gap: .65rem; text-align: left; }
        #pembelianModal .purchase-detail-number { margin: 0; }
        #pembelianModal .purchase-row-selection .purchase-detail-number::before { content: 'Item '; }
        #pembelianModal .purchase-medicine-field, #pembelianModal .purchase-row-actions { grid-column: 1 / -1; }
        #pembelianModal .purchase-cell-label { position: static; width: auto; height: auto; padding: 0; clip-path: none; white-space: normal; }
        #pembelianModal .purchase-row-actions { display: flex !important; gap: .5rem; align-items: start; justify-content: space-between; }
        #pembelianModal .purchase-item-adjustments { flex: 1; }
        #pembelianModal .purchase-detail-row.is-selected { background: #f0fdfa; }
        #pembelianModal .purchase-detail-row.is-selected > td { background: transparent; }
        #pembelianModal .purchase-detail-row.has-issues { border-color: var(--purchase-danger); }
        #pembelianModal .purchase-detail-row.has-issues > td:first-child { box-shadow: none; }
        #pembelianModal .form-control, #pembelianModal .form-select, #pembelianModal .select2-selection__rendered, #pembelianModal .select2-search__field { font-size: 16px; }
        #pembelianModal .form-control, #pembelianModal .form-select, #pembelianModal .btn { min-height: 44px; }
        #pembelianModal .purchase-form-aside { grid-template-columns: 1fr; }
        #pembelianModal .purchase-form-aside .row > div { width: 100%; }
        #pembelianModal .purchase-picker-pagination, #pembelianModal .purchase-medicine-picker-footer { flex-wrap: wrap; }
        #pembelianModal .purchase-bulk-toolbar > strong { width: 100%; }
        #pembelianModal .purchase-bulk-toolbar select { flex: 1 1 140px; }
        #pembelianModal .purchase-items-toolbar > input { max-width: none; }
        #pembelianModal .purchase-form-footer { padding: .65rem .75rem calc(.65rem + env(safe-area-inset-bottom, 0px)); }
        #pembelianModal .purchase-mobile-total { width: 100%; justify-content: space-between; }
        #pembelianModal .purchase-save-actions { width: 100%; display: flex; }
        #pembelianModal .purchase-save-actions .btn { flex: 1 1 auto; justify-content: center; font-size: .75rem; padding-inline: .5rem; }
        #pembelianModal .purchase-save-actions .btn i { display: none; }
    }
</style>
