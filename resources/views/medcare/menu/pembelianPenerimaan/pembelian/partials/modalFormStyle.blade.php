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
    #pembelianModal .purchase-mobile-text, #pembelianModal .purchase-select-all-mobile, #pembelianModal .purchase-row-choice-text { display: none; }
    #pembelianModal .purchase-row-choice { display: inline-flex; align-items: center; margin: 0; cursor: pointer; }
    #pembelianModal .purchase-items-toolbar, #pembelianModal .purchase-bulk-toolbar, #pembelianModal .purchase-picker-pagination { display: flex; align-items: center; flex-wrap: wrap; gap: .65rem; margin-bottom: .85rem; }
    #pembelianModal .purchase-items-toolbar > input { flex: 1 1 240px; max-width: 460px; }
    #pembelianModal .purchase-filter-check { display: inline-flex; align-items: center; gap: .5rem; margin: 0; min-height: 40px; }
    #pembelianModal .purchase-filter-check input { margin: 0; }
    #pembelianModal .purchase-entry-tools > summary { display: none; }
    #pembelianModal .purchase-bulk-toolbar { padding: .75rem; border-radius: 8px; background: var(--purchase-soft); }
    #pembelianModal .purchase-bulk-toolbar select { width: auto; }
    #pembelianModal .purchase-bulk-toolbar input { width: 100px; }
    #pembelianModal .purchase-tax-toolbar { display: flex; align-items: center; flex-wrap: wrap; gap: .65rem; margin-bottom: .85rem; padding: .75rem; border: 1px solid var(--purchase-border); border-radius: 8px; background: #f8fafc; }
    #pembelianModal .purchase-tax-toolbar .form-label { margin: 0; }
    #pembelianModal .purchase-tax-toolbar input { width: 95px; }
    #pembelianModal .purchase-tax-toolbar small { color: var(--purchase-muted); font-size: .75rem; }
    #pembelianModal #purchaseAllTaxStatus:not(:empty) { flex-basis: 100%; color: var(--purchase-primary-strong); }
    #pembelianModal .purchase-picker-pagination { justify-content: flex-end; margin: 0; padding: .5rem .9rem; border-bottom: 1px solid var(--purchase-border); }
    #pembelianModal .purchase-detail-summary { gap: .6rem; margin-bottom: .75rem; }
    #pembelianModal .purchase-form-section { overflow: visible; }
    /* Keep hidden labels and Select2 inputs inside the table's scroll area. */
    #pembelianModal .purchase-entry-table-wrap { position: relative; max-height: 60vh; overflow: auto; border: 1px solid var(--purchase-border); border-radius: 10px; }
    #pembelianModal .purchase-entry-table { --purchase-fixed-columns: 850px; width: 100%; min-width: 1120px; margin: 0; table-layout: fixed; }
    #pembelianModal .purchase-entry-table th { position: sticky; top: 0; z-index: 2; background: #f1f5f9; color: var(--purchase-text); padding: .8rem .75rem; vertical-align: middle; font-size: .78rem; text-transform: none; white-space: normal; }
    #pembelianModal .purchase-entry-table th small { display: block; margin-top: .25rem; color: var(--purchase-muted); font-size: .7rem; font-weight: 400; }
    #pembelianModal .purchase-selection-column { width: 48px; }
    #pembelianModal .purchase-medicine-column { width: calc(100% - var(--purchase-fixed-columns)); }
    #pembelianModal .purchase-unit-column { width: 210px; }
    #pembelianModal .purchase-qty-column { width: 80px; text-align: center; }
    #pembelianModal .purchase-price-column { width: 160px; text-align: right; }
    #pembelianModal .purchase-subtotal-column { width: 180px; text-align: right; }
    #pembelianModal .purchase-actions-column { width: 172px; }
    #pembelianModal .purchase-selection-column { text-align: center; }
    #pembelianModal .purchase-detail-row > td { padding: .85rem .75rem; vertical-align: top; border-bottom: 1px solid var(--purchase-border); background: #fff; white-space: normal; }
    #pembelianModal .purchase-detail-row:last-child > td { border-bottom: 0; }
    #pembelianModal .purchase-detail-row.is-selected > td { background: #f0fdfa; }
    #pembelianModal .purchase-detail-row.has-issues > td:first-child { box-shadow: inset 3px 0 var(--purchase-danger); }
    #pembelianModal .purchase-row-selection { text-align: center; }
    #pembelianModal .purchase-detail-number { display: block; width: auto; height: auto; margin: 0 0 .45rem; padding: 0; background: none; color: var(--purchase-muted); border-radius: 0; font-size: .75rem; }
    #pembelianModal .purchase-row-check, #pembelianModal #purchaseSelectVisible { width: 18px; height: 18px; margin: .2rem 0; }
    #pembelianModal .purchase-cell-label { position: absolute; width: 1px; height: 1px; padding: 0; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
    #pembelianModal .purchase-medicine-field .select2-container { max-width: 100%; }
    #pembelianModal .purchase-medicine-field .select2-selection--single { height: auto; }
    #pembelianModal .purchase-medicine-field .select2-selection--single .select2-selection__rendered { padding: .6rem 1.8rem .6rem .65rem; line-height: 1.45; white-space: normal; overflow-wrap: anywhere; }
    #pembelianModal .purchase-medicine-field .select2-selection--single .select2-selection__arrow { top: 50%; transform: translateY(-50%); }
    #pembelianModal .purchase-current-stock { display: flex; align-items: baseline; gap: .3rem; margin-top: .5rem; padding: 0; background: none; border: 0; font-size: .75rem; font-weight: 400; line-height: 1.45; }
    #pembelianModal .purchase-current-stock i { margin-right: .25rem; }
    #pembelianModal .purchase-row-errors, #pembelianModal .purchase-unit-price, #pembelianModal .purchase-unit-warning { display: block; max-width: 100%; margin-top: .35rem; font-size: .75rem; line-height: 1.45; white-space: normal; overflow-wrap: anywhere; }
    #pembelianModal .purchase-row-errors:empty { display: none; }
    #pembelianModal .purchase-unit-price { color: var(--purchase-muted); text-align: right; }
    #pembelianModal .purchase-unit-tools { display: flex; flex-direction: column; align-items: flex-start; gap: .45rem; margin-top: .5rem; }
    #pembelianModal .purchase-unit-tools .purchase-unit-warning { margin: 0; }
    #pembelianModal .purchase-set-conversion { display: inline-flex; align-items: center; justify-content: center; gap: .3rem; max-width: 100%; min-height: 32px; padding: .3rem .6rem; font-size: .73rem; line-height: 1.4; white-space: normal; }
    #pembelianModal .purchase-set-conversion i { flex-shrink: 0; }
    #pembelianModal .purchase-conversion-success { display: block; width: 100%; font-size: .73rem; line-height: 1.45; white-space: normal; overflow-wrap: anywhere; }
    #purchaseConversionModal { width: min(860px, calc(100vw - 2rem)); max-height: calc(100dvh - 2rem); padding: 0; border: 1px solid rgba(255, 255, 255, .7); border-radius: 20px; overflow: visible; background: #fff; color: var(--purchase-text); box-shadow: 0 32px 100px rgba(15, 23, 42, .28); }
    #purchaseConversionModal[open], #purchaseConversionForm { display: flex; flex-direction: column; min-height: 0; }
    #purchaseConversionForm { flex: 1; }
    #purchaseConversionModal::backdrop { background: rgba(15, 23, 42, .6); backdrop-filter: blur(5px); }
    #purchaseConversionModal .purchase-conversion-header { display: flex; flex-shrink: 0; justify-content: space-between; align-items: start; gap: 1rem; padding: 1.4rem 1.5rem; border-bottom: 1px solid var(--purchase-border); border-radius: 20px 20px 0 0; background: linear-gradient(125deg, #f0fdfa, #f8fafc 70%); }
    #purchaseConversionModal .purchase-conversion-heading { display: flex; align-items: center; gap: .9rem; min-width: 0; }
    #purchaseConversionModal .purchase-conversion-icon { display: grid; place-items: center; flex-shrink: 0; width: 52px; height: 52px; border: 1px solid #cce8e2; border-radius: 15px; color: var(--purchase-primary); background: #fff; box-shadow: 0 4px 12px rgba(15, 118, 110, .06); font-size: 1.7rem; }
    #purchaseConversionModal .purchase-conversion-eyebrow { display: block; margin-bottom: .3rem; color: var(--purchase-primary); font-size: .65rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    #purchaseConversionModal .purchase-conversion-header h5 { margin: 0; color: var(--purchase-text); font-size: 1.25rem; font-weight: 700; }
    #purchaseConversionModal .purchase-conversion-header p { margin: .35rem 0 0; color: var(--purchase-muted); font-size: .8rem; line-height: 1.5; }
    #purchaseConversionModal .purchase-conversion-header .btn-close { flex-shrink: 0; min-height: 0; padding: .65rem; border-radius: 50%; background-color: #fff; opacity: .65; }
    #purchaseConversionModal .purchase-conversion-body { min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 1.25rem 1.5rem; background: #fff; }
    #purchaseConversionModal .purchase-conversion-medicine-card { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--purchase-border); border-radius: 12px; background: #f8fafc; }
    #purchaseConversionModal .purchase-conversion-medicine-copy { min-width: 0; }
    #purchaseConversionModal .purchase-conversion-code { display: inline-block; margin-bottom: .4rem; color: var(--purchase-muted); font-size: .7rem; font-weight: 600; letter-spacing: .04em; }
    #purchaseConversionModal .purchase-conversion-medicine-copy > strong { display: block; color: var(--purchase-text); font-size: .9rem; line-height: 1.5; overflow-wrap: anywhere; }
    #purchaseConversionModal .purchase-conversion-stock { display: flex; flex-shrink: 0; align-items: center; gap: .65rem; padding-left: 1rem; border-left: 1px solid var(--purchase-border); }
    #purchaseConversionModal .purchase-conversion-stock > i { color: var(--purchase-primary); font-size: 1.4rem; }
    #purchaseConversionModal .purchase-conversion-stock span { display: block; color: var(--purchase-muted); font-size: .7rem; }
    #purchaseConversionModal .purchase-conversion-stock strong { display: block; margin-top: .25rem; font-size: .85rem; }
    #purchaseConversionModal .purchase-conversion-section-heading { display: flex; align-items: start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
    #purchaseConversionModal .purchase-conversion-section-heading h6 { margin: 0 0 .35rem; font-size: .9rem; font-weight: 700; }
    #purchaseConversionModal .purchase-conversion-section-heading p { max-width: 540px; margin: 0; color: var(--purchase-muted); font-size: .78rem; line-height: 1.55; }
    #purchaseConversionModal .purchase-conversion-count { flex-shrink: 0; padding: .35rem .6rem; border-radius: 6px; color: var(--purchase-primary); background: var(--purchase-soft); font-size: .7rem; font-weight: 600; }
    #purchaseConversionModal .purchase-conversion-loading { display: flex; align-items: center; justify-content: center; gap: .6rem; min-height: 110px; color: var(--purchase-muted); font-size: .85rem; }
    #purchaseConversionModal .purchase-conversion-row { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr) 130px; gap: .75rem; margin-bottom: .85rem; padding: 1rem; border: 1px solid var(--purchase-border); border-radius: 12px; background: #fff; transition: border-color .15s, background-color .15s; }
    #purchaseConversionModal .purchase-conversion-row.is-selected { border-color: #8fc9bd; background: #f6fcfa; box-shadow: 0 0 0 1px rgba(15, 118, 110, .06); }
    #purchaseConversionModal .purchase-conversion-row-heading { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    #purchaseConversionModal .purchase-conversion-row-meta { display: flex; align-items: center; gap: .5rem; }
    #purchaseConversionModal .purchase-conversion-row-number { color: var(--purchase-text); font-size: .75rem; font-weight: 700; }
    #purchaseConversionModal .purchase-conversion-row-status { color: var(--purchase-muted); font-size: .68rem; }
    #purchaseConversionModal .purchase-conversion-row-status.is-existing { padding: .2rem .5rem; border-radius: 5px; color: var(--purchase-primary); background: #e8f5f0; }
    #purchaseConversionModal .form-label { display: block; margin-bottom: .4rem; color: var(--purchase-muted); font-size: .75rem; font-weight: 500; }
    #purchaseConversionModal .form-control, #purchaseConversionModal .form-select { min-height: 44px; border-radius: 8px; font-size: .85rem; }
    #purchaseConversionModal .form-control:focus { border-color: #78b9aa; box-shadow: 0 0 0 3px rgba(15, 118, 110, .1); }
    #purchaseConversionModal .form-control[readonly] { background: #f1f5f9; color: var(--purchase-muted); }
    #purchaseConversionModal .purchase-conversion-unit-field { min-width: 0; }
    #purchaseConversionModal .purchase-conversion-unit-field .select2-container { width: 100% !important; max-width: 100%; }
    #purchaseConversionModal .purchase-conversion-unit-field .select2-selection--single { height: 44px; min-height: 44px; border: 1px solid var(--purchase-border); border-radius: 8px; background: #fff; }
    #purchaseConversionModal .purchase-conversion-unit-field .select2-selection__rendered { padding-left: .75rem; padding-right: 2rem; line-height: 42px; font-size: .85rem; }
    #purchaseConversionModal .purchase-conversion-unit-field .select2-selection__arrow { height: 42px; right: .35rem; }
    #purchaseConversionModal .select2-container--open .select2-dropdown { border-color: #b6d8d0; border-radius: 8px; box-shadow: 0 12px 30px rgba(15, 23, 42, .14); overflow: hidden; }
    #purchaseConversionModal .select2-search--dropdown { padding: .6rem; }
    #purchaseConversionModal .select2-search--dropdown .select2-search__field { min-height: 38px; padding: .45rem .65rem; border: 1px solid var(--purchase-border); border-radius: 6px; font-size: .85rem; }
    #purchaseConversionModal .select2-results__options { max-height: 200px; }
    #purchaseConversionModal .select2-results__option { padding: .65rem .8rem; font-size: .85rem; }
    #purchaseConversionModal .select2-results__option--highlighted[aria-selected] { background: var(--purchase-primary); }
    #purchaseConversionModal .select2-container--focus .select2-selection--single { border-color: #78b9aa; box-shadow: 0 0 0 3px rgba(15, 118, 110, .1); }
    #purchaseConversionModal .is-invalid + .select2 .select2-selection--single { border-color: var(--purchase-danger); }
    #purchaseConversionModal .purchase-conversion-factor-input { position: relative; }
    #purchaseConversionModal .purchase-conversion-factor-input input { padding-right: 5.5rem; font-variant-numeric: tabular-nums; }
    #purchaseConversionModal .purchase-conversion-stock-label { position: absolute; top: 50%; right: .7rem; max-width: 4rem; transform: translateY(-50%); color: var(--purchase-muted); font-size: .72rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; pointer-events: none; }
    #purchaseConversionModal .purchase-conversion-row-actions { display: flex; flex-direction: column; align-items: stretch; justify-content: end; gap: .5rem; }
    #purchaseConversionModal .purchase-conversion-choice { display: flex; align-items: center; justify-content: center; gap: .5rem; min-height: 44px; margin: 0; padding: .5rem; border: 1px solid var(--purchase-border); border-radius: 8px; background: #fff; white-space: nowrap; font-size: .75rem; cursor: pointer; }
    #purchaseConversionModal .is-selected .purchase-conversion-choice { border-color: #8fc9bd; color: var(--purchase-primary-strong); font-weight: 600; }
    #purchaseConversionModal .purchase-conversion-row-actions input { margin: 0; }
    #purchaseConversionModal .purchase-conversion-use:checked { border-color: var(--purchase-primary); background-color: var(--purchase-primary); }
    #purchaseConversionModal .purchase-remove-conversion { min-height: 32px; padding: .25rem .5rem; border-color: transparent; font-size: .7rem; }
    #purchaseConversionModal .purchase-conversion-equation { grid-column: 1 / -1; display: flex; align-items: center; gap: .45rem; padding-top: .7rem; border-top: 1px dashed var(--purchase-border); color: var(--purchase-primary-strong); font-size: .78rem; font-weight: 600; line-height: 1.5; }
    #purchaseConversionModal .purchase-conversion-equation > i { font-size: 1.1rem; }
    #purchaseConversionModal .purchase-conversion-lock-note { grid-column: 1 / -1; color: var(--purchase-muted); font-size: .7rem; line-height: 1.5; }
    #purchaseConversionModal .purchase-conversion-unit-field .select2-container--disabled .select2-selection--single { background: #f1f5f9; }
    #purchaseConversionModal .purchase-conversion-add { display: flex; align-items: center; justify-content: center; gap: .4rem; width: 100%; min-height: 44px; margin-top: .25rem; border: 1px dashed #b0cfc7; border-radius: 10px; color: var(--purchase-primary); background: #f8fcfb; font-size: .8rem; font-weight: 600; }
    #purchaseConversionModal .purchase-conversion-add:hover { border-color: var(--purchase-primary); background: var(--purchase-soft); }
    #purchaseConversionModal .purchase-conversion-footer { display: flex; flex-shrink: 0; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; padding: 1rem 1.5rem; border-top: 1px solid var(--purchase-border); border-radius: 0 0 20px 20px; background: #f8fafc; }
    #purchaseConversionModal .purchase-conversion-footer > p { display: flex; align-items: center; gap: .4rem; margin: 0; color: var(--purchase-muted); font-size: .72rem; }
    #purchaseConversionModal .purchase-conversion-footer > p i { color: var(--purchase-primary); font-size: 1rem; }
    #purchaseConversionModal .purchase-conversion-save-actions { display: flex; gap: .5rem; }
    #purchaseConversionModal .purchase-conversion-save-actions .btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; min-height: 42px; padding: .55rem 1rem; border-radius: 9px; font-size: .8rem; font-weight: 600; }
    #purchaseConversionModal .purchase-conversion-save-actions .btn-primary { border-color: var(--purchase-primary); background: var(--purchase-primary); box-shadow: 0 4px 10px rgba(15, 118, 110, .15); }
    #purchaseConversionModal .purchase-conversion-save-actions .btn-primary:hover { border-color: var(--purchase-primary-strong); background: var(--purchase-primary-strong); }
    #purchaseConversionModal .purchase-conversion-save-actions .btn-light { border-color: var(--purchase-border); background: #fff; }
    #purchaseConversionModal .btn:focus-visible { outline: 2px solid var(--purchase-primary); outline-offset: 2px; }
    @media (max-width: 575.98px) {
        #purchaseConversionModal { width: calc(100vw - 1rem); max-height: calc(100dvh - 1rem); border-radius: 16px; }
        #purchaseConversionModal .purchase-conversion-header, #purchaseConversionModal .purchase-conversion-body, #purchaseConversionModal .purchase-conversion-footer { padding: 1rem; }
        #purchaseConversionModal .purchase-conversion-icon { width: 42px; height: 42px; font-size: 1.4rem; }
        #purchaseConversionModal .purchase-conversion-header h5 { font-size: 1.1rem; }
        #purchaseConversionModal .purchase-conversion-medicine-card { flex-wrap: wrap; }
        #purchaseConversionModal .purchase-conversion-stock { padding: .65rem 0 0; border-left: 0; border-top: 1px solid var(--purchase-border); width: 100%; }
        #purchaseConversionModal .purchase-conversion-row { grid-template-columns: minmax(0, 1fr); padding: .85rem; }
        #purchaseConversionModal .purchase-conversion-row-actions { grid-column: 1 / -1; flex-direction: row; align-items: center; justify-content: space-between; }
        #purchaseConversionModal .purchase-conversion-choice { flex: 1; }
        #purchaseConversionModal .purchase-conversion-save-actions { width: 100%; }
        #purchaseConversionModal .purchase-conversion-save-actions .btn { flex: 1; }
        #purchaseConversionModal .form-control, #purchaseConversionModal .purchase-conversion-unit-field .select2-selection__rendered, #purchaseConversionModal .select2-search--dropdown .select2-search__field { font-size: 16px; }
    }
    #pembelianModal .purchase-price-field input, #pembelianModal .purchase-subtotal-field input { text-align: right; font-variant-numeric: tabular-nums; padding-inline: .5rem; }
    #pembelianModal .purchase-qty-field input { text-align: center; padding-inline: .35rem; font-variant-numeric: tabular-nums; }
    #pembelianModal .purchase-subtotal-field input { color: var(--purchase-primary-strong); background: var(--purchase-soft); font-weight: 700; }
    #pembelianModal .purchase-item-adjustments { border: 0; background: transparent; }
    #pembelianModal .purchase-item-adjustments summary { display: block; min-height: 40px; padding: .5rem .6rem; border: 1px solid var(--purchase-border); border-radius: 8px; background: #f8fafc; color: var(--purchase-text); font-size: .76rem; cursor: pointer; list-style: none; }
    #pembelianModal .purchase-item-adjustments summary::-webkit-details-marker { display: none; }
    #pembelianModal .purchase-adjustments-title { display: flex; justify-content: space-between; align-items: center; gap: .35rem; font-weight: 600; }
    #pembelianModal .purchase-item-adjustments[open] .purchase-adjustments-title i { transform: rotate(180deg); }
    #pembelianModal .purchase-adjustments-value { display: block; margin-top: .2rem; color: var(--purchase-muted); font-size: .72rem; overflow-wrap: anywhere; }
    #pembelianModal .purchase-adjustment-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; margin: .5rem 0; }
    #pembelianModal .purchase-adjustment-fields .form-label { font-size: .72rem; }
    #pembelianModal .purchase-adjustment-fields input { padding-inline: .4rem; }
    #pembelianModal .purchase-adjustment-hint { display: block; margin-bottom: .5rem; color: var(--purchase-muted); font-size: .72rem; }
    #pembelianModal .remove-detail { display: inline-flex; align-items: center; gap: .25rem; min-height: 32px; margin-top: .5rem; padding: .25rem .5rem; font-size: .72rem; }
    #pembelianModal .purchase-add-another { display: block; width: 100%; margin-top: .75rem; border-style: dashed; }
    #pembelianModal .purchase-form-aside { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    #pembelianModal .purchase-form-aside > .purchase-form-section { margin: 0; }
    #pembelianModal .purchase-form-aside .row > div { width: 50%; }
    #pembelianModal .purchase-total-panel { display: block; padding: 1rem; margin: 0; }
    #pembelianModal .purchase-total-stats { display: grid; gap: .6rem; margin: .8rem 0; }
    #pembelianModal .purchase-total-stats span { display: flex; justify-content: space-between; gap: .75rem; padding: 0; background: none; }
    #pembelianModal .purchase-total-stats .purchase-medicine-total { padding-top: .6rem; border-top: 1px solid rgba(22, 163, 74, .2); }
    #pembelianModal .purchase-grand-total { display: flex; justify-content: space-between; flex-wrap: wrap; gap: .5rem; padding-top: .75rem; border-top: 1px solid var(--purchase-border); }
    #pembelianModal .purchase-save-hint { grid-column: 1 / -1; margin: 0; color: var(--purchase-muted); font-size: .8rem; }
    #pembelianModal .purchase-form-footer { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .75rem; }
    #pembelianModal .purchase-mobile-total { display: flex; align-items: baseline; flex-wrap: wrap; gap: .75rem; color: var(--purchase-muted); font-size: .8rem; }
    #pembelianModal .purchase-mobile-total strong { color: var(--purchase-primary-strong); font-size: 1.15rem; }
    #pembelianModal .is-invalid + .select2 .select2-selection { border-color: var(--purchase-danger); }
    #pembelianModal summary:focus-visible, #pembelianModal .btn:focus-visible { outline: 2px solid var(--purchase-primary); outline-offset: -2px; }
    @media (max-width: 767.98px), (max-width: 991.98px) and (pointer: coarse) {
        #pembelianModal .modal-dialog { height: 100vh; height: 100dvh; min-height: 0; }
        #pembelianModal .modal-content { max-height: 100vh; max-height: 100dvh; }
        #pembelianModal .modal-header { position: relative; display: block; flex-shrink: 0; padding: calc(.7rem + env(safe-area-inset-top, 0px)) calc(3.5rem + env(safe-area-inset-right, 0px)) .7rem calc(.85rem + env(safe-area-inset-left, 0px)); }
        #pembelianModal .modal-body { padding: .75rem max(.625rem, env(safe-area-inset-right, 0px)) .75rem max(.625rem, env(safe-area-inset-left, 0px)); overflow-x: hidden; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; }
        #pembelianModal .purchase-desktop-text { display: none; }
        #pembelianModal .purchase-mobile-text { display: inline; }
        #pembelianModal .modal-title-icon, #pembelianModal .modal-subtitle { display: none; }
        #pembelianModal .modal-title { font-size: 1rem; white-space: normal; }
        #pembelianModal #purchaseDraftStatus { display: inline-flex; max-width: 100%; font-size: .65rem; padding: .25rem .5rem; }
        #pembelianModal .purchase-modal-header-meta { position: static; display: block; width: auto; margin: .3rem 0 0; transform: none; }
        #pembelianModal .modal-header .btn-close { position: absolute; right: calc(.4rem + env(safe-area-inset-right, 0px)); top: calc(.6rem + env(safe-area-inset-top, 0px)); box-sizing: border-box; width: 44px; height: 44px; margin: 0; }
        #pembelianModal .purchase-form-section-header, #pembelianModal .purchase-form-section-body { padding: .75rem; }
        #pembelianModal .purchase-form-section { margin-bottom: .875rem; border-radius: 12px; box-shadow: none; }
        #pembelianModal .purchase-form-section-body .row { --bs-gutter-x: .75rem; --bs-gutter-y: .875rem; }
        #pembelianModal .purchase-items-section .purchase-form-section-body { padding: .625rem; }
        #pembelianModal .purchase-information-section .purchase-field-hint { margin-top: .3rem; font-size: .73rem; line-height: 1.45; }
        #pembelianModal .purchase-notes-details summary { display: flex; align-items: center; gap: .35rem; min-height: 44px; padding: .6rem .75rem; border: 1px solid var(--purchase-border); border-radius: 8px; background: #f8fafc; list-style: none; }
        #pembelianModal .purchase-notes-details summary::-webkit-details-marker { display: none; }
        #pembelianModal .purchase-notes-details summary::after { content: '+'; margin-left: auto; font-size: 1.1rem; }
        #pembelianModal .purchase-notes-details[open] summary::after { content: '−'; }
        #pembelianModal .purchase-form-section-title { min-width: 0; align-items: flex-start; }
        #pembelianModal .purchase-form-section-title > i { flex-shrink: 0; }
        #pembelianModal .purchase-detail-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); width: 100%; }
        #pembelianModal .purchase-detail-actions .btn { display: flex; min-width: 0; gap: .35rem; justify-content: center; padding: .6rem .5rem; font-size: .8rem; white-space: normal; line-height: 1.4; }
        #pembelianModal .purchase-entry-table-wrap { max-height: none; overflow: visible; border: 0; border-radius: 0; padding: 0; background: transparent; }
        #pembelianModal .purchase-entry-table { display: block; min-width: 0; }
        #pembelianModal .purchase-entry-table thead { display: none; }
        #pembelianModal .purchase-entry-table tbody { display: grid; grid-template-columns: minmax(0, 1fr); gap: .875rem; }
        #pembelianModal .purchase-detail-row { display: grid; grid-template-columns: minmax(0, 1fr) 96px; gap: .75rem .625rem; border: 1px solid var(--purchase-border); border-top: 3px solid var(--purchase-primary); border-radius: 10px; margin: 0; padding: .75rem; background: #fff; box-shadow: 0 2px 6px rgba(15, 23, 42, .04); scroll-margin-block: 1rem; }
        #pembelianModal .purchase-detail-row > td { display: block; border: 0; padding: 0; min-width: 0; background: transparent; }
        #pembelianModal .purchase-detail-row > .purchase-row-selection { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: .65rem; text-align: left; border-bottom: 1px solid var(--purchase-border); padding-bottom: .35rem; }
        #pembelianModal .purchase-detail-number { display: inline-flex; align-items: center; gap: .25rem; min-height: 30px; margin: 0; padding: .3rem .6rem; border-radius: 6px; background: var(--purchase-soft); color: var(--purchase-primary-strong); font-size: .8rem; font-weight: 700; }
        #pembelianModal .purchase-row-choice { min-height: 44px; gap: .5rem; padding-inline: .35rem; font-size: .8rem; }
        #pembelianModal .purchase-row-choice-text { display: inline; }
        #pembelianModal .purchase-row-check { width: 22px; height: 22px; margin: 0; flex-shrink: 0; }
        #pembelianModal .purchase-row-selection .purchase-detail-number::before { content: 'Item '; }
        #pembelianModal .purchase-medicine-field, #pembelianModal .purchase-price-field, #pembelianModal .purchase-subtotal-field, #pembelianModal .purchase-row-actions { grid-column: 1 / -1; }
        #pembelianModal .purchase-unit-field { grid-column: 1; }
        #pembelianModal .purchase-qty-field { grid-column: 2; }
        #pembelianModal .purchase-cell-label { position: static; width: auto; height: auto; padding: 0; clip-path: none; white-space: normal; }
        #pembelianModal .purchase-current-stock { flex-wrap: wrap; }
        #pembelianModal .purchase-unit-price { text-align: left; }
        #pembelianModal .purchase-set-conversion { width: 100%; }
        #pembelianModal .purchase-detail-row > .purchase-subtotal-field { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); align-items: center; gap: .5rem; padding: .5rem .65rem; border-radius: 8px; background: var(--purchase-soft); }
        #pembelianModal .purchase-subtotal-field .form-control { border: 0; padding-inline: 0; min-height: 36px; box-shadow: none; }
        #pembelianModal .purchase-subtotal-field .form-label { margin: 0; color: var(--purchase-primary-strong); font-size: .75rem; line-height: 1.45; }
        #pembelianModal .purchase-row-actions { display: grid !important; grid-template-columns: minmax(0, 1fr) 44px; align-items: start; gap: .5rem; }
        #pembelianModal .purchase-item-adjustments { min-width: 0; }
        #pembelianModal .purchase-item-adjustments summary { min-height: 44px; padding: .6rem .75rem; }
        #pembelianModal .purchase-adjustment-fields { gap: .65rem; padding: .25rem; }
        #pembelianModal .purchase-row-actions .remove-detail { justify-content: center; width: 44px; height: 44px; margin-top: 0; padding: 0; font-size: 1.15rem; }
        #pembelianModal .purchase-remove-label { display: none; }
        #pembelianModal .purchase-detail-row.is-selected { border-color: var(--purchase-primary); background: #f0fdfa; }
        #pembelianModal .purchase-detail-row.is-selected > td { background: transparent; }
        #pembelianModal .purchase-detail-row.has-issues { border-color: var(--purchase-danger); }
        #pembelianModal .purchase-detail-row.has-issues > td:first-child { box-shadow: none; }
        #pembelianModal .form-control, #pembelianModal .form-select, #pembelianModal .select2-selection__rendered, #pembelianModal .select2-search__field, #pembelianModal .purchase-picker-search input, #pembelianModal .flatpickr-mobile { font-size: 16px; }
        #pembelianModal .form-control, #pembelianModal .form-select, #pembelianModal .btn, #pembelianModal .input-group-text { min-height: 44px; }
        #pembelianModal .select2-container--default .select2-selection--single { min-height: 44px; height: auto; }
        #pembelianModal .select2-container--default .select2-selection--single .select2-selection__rendered { white-space: normal; overflow-wrap: anywhere; line-height: 1.45; padding: .65rem 1.8rem .65rem .65rem; }
        #pembelianModal .select2-container--default .select2-selection--single .select2-selection__arrow { top: 50%; transform: translateY(-50%); }
        #pembelianModal .select2-results__option { min-height: 44px; padding: .65rem .75rem; }
        #pembelianModal .select2-results__options { max-height: min(240px, 40dvh); }
        #pembelianModal .purchase-form-aside { grid-template-columns: 1fr; }
        #pembelianModal .purchase-form-aside .row > div { width: 100%; }
        #pembelianModal .purchase-picker-pagination, #pembelianModal .purchase-medicine-picker-footer { flex-wrap: wrap; }
        #pembelianModal .purchase-medicine-picker-head { align-items: flex-start; padding: .75rem; }
        #pembelianModal .purchase-medicine-picker-head > div { min-width: 0; }
        #pembelianModal .purchase-picker-close { flex: 0 0 44px; width: 44px; }
        #pembelianModal .purchase-medicine-picker-toolbar, #pembelianModal .purchase-medicine-picker-footer { flex-direction: column; align-items: stretch; gap: .6rem; padding: .75rem; }
        #pembelianModal .purchase-picker-search, #pembelianModal .purchase-picker-selection { width: 100%; min-width: 0; }
        #pembelianModal .purchase-picker-search button { flex: 0 0 44px; height: 44px; }
        #pembelianModal .purchase-picker-result-count { white-space: normal; }
        #pembelianModal .purchase-picker-table-wrap { max-height: 420px; max-height: min(420px, 55dvh); padding: .5rem; background: #f8fafc; overscroll-behavior-y: contain; }
        #pembelianModal .purchase-picker-table, #pembelianModal .purchase-picker-table tbody { display: block; width: 100%; min-width: 0; }
        #pembelianModal .purchase-picker-table thead { display: none; }
        #pembelianModal .purchase-picker-table tbody tr { position: relative; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: .6rem; border: 1px solid var(--purchase-border); border-radius: 9px; background: #fff; overflow: hidden; }
        #pembelianModal .purchase-picker-table tbody td { display: flex; flex-direction: column; align-items: flex-start; min-width: 0; gap: .25rem; padding: .6rem !important; border: 0; border-bottom: 1px solid #edf2f8; text-align: left !important; white-space: normal; overflow-wrap: anywhere; }
        #pembelianModal .purchase-picker-table tbody td::before { content: attr(data-label); color: var(--purchase-muted); font-size: .65rem; font-weight: 600; }
        #pembelianModal .purchase-picker-table tbody td:nth-child(3) { grid-column: 1 / -1; grid-row: 1; min-height: 62px; padding-right: 3.5rem !important; background: #fbfdff; }
        #pembelianModal .purchase-picker-table tbody td:nth-child(1) { position: absolute; top: .25rem; right: .25rem; z-index: 1; width: auto; padding: 0 !important; border: 0; }
        #pembelianModal .purchase-picker-table tbody td:nth-child(1)::before { display: none; }
        #pembelianModal .purchase-picker-name, #pembelianModal .purchase-picker-code { max-width: 100%; white-space: normal; overflow-wrap: anywhere; }
        #pembelianModal .purchase-picker-table tbody td.purchase-picker-state { position: static; display: block; grid-column: 1 / -1; width: 100%; padding: 2rem .75rem !important; text-align: center !important; }
        #pembelianModal .purchase-picker-table tbody td.purchase-picker-state::before { display: none; }
        #pembelianModal .purchase-picker-pagination { justify-content: space-between; gap: .5rem; }
        #pembelianModal .purchase-picker-pagination .btn { flex: 1; min-width: 0; }
        #pembelianModal .purchase-medicine-picker-footer .btn { width: 100%; white-space: normal; }
        #purchaseConversionModal .purchase-conversion-header .btn-close { box-sizing: border-box; flex: 0 0 44px; width: 44px; height: 44px; padding: 0; }
        #purchaseConversionModal .purchase-conversion-heading { align-items: flex-start; }
        #purchaseConversionModal .purchase-conversion-header { gap: .5rem; }
        #purchaseConversionModal .purchase-conversion-save-actions .btn { min-height: 44px; white-space: normal; }
        #pembelianModal .purchase-bulk-toolbar > strong { width: 100%; }
        #pembelianModal .purchase-bulk-toolbar { display: grid; grid-template-columns: minmax(0, 1fr) 88px; }
        #pembelianModal .purchase-bulk-toolbar > strong, #pembelianModal .purchase-bulk-toolbar .btn { grid-column: 1 / -1; }
        #pembelianModal .purchase-bulk-toolbar select, #pembelianModal .purchase-bulk-toolbar input { width: 100%; min-width: 0; }
        #pembelianModal .purchase-items-toolbar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .5rem; }
        #pembelianModal .purchase-entry-tools { margin-bottom: .75rem; border: 1px solid var(--purchase-border); border-radius: 9px; background: #f8fafc; }
        #pembelianModal .purchase-entry-tools > summary { display: flex; align-items: center; gap: .5rem; min-height: 44px; padding: .65rem .75rem; color: var(--purchase-text); font-size: .8rem; font-weight: 600; list-style: none; cursor: pointer; }
        #pembelianModal .purchase-entry-tools > summary::-webkit-details-marker { display: none; }
        #pembelianModal .purchase-entry-tools > summary > span:first-child { display: flex; align-items: center; gap: .4rem; }
        #pembelianModal .purchase-tools-chevron { margin-left: auto; font-size: 1.1rem; }
        #pembelianModal .purchase-entry-tools[open] > summary { border-bottom: 1px solid var(--purchase-border); }
        #pembelianModal .purchase-entry-tools[open] .purchase-tools-chevron { transform: rotate(180deg); }
        #pembelianModal .purchase-entry-tools-body { padding: .75rem; }
        #pembelianModal .purchase-entry-tools .purchase-tax-toolbar { margin-bottom: 0; padding: .65rem 0 0; border: 0; border-top: 1px solid var(--purchase-border); border-radius: 0; background: transparent; }
        #pembelianModal .purchase-filter-status { padding: .2rem .4rem; border-radius: 5px; color: var(--purchase-primary-strong); background: var(--purchase-soft); font-size: .65rem; }
        #pembelianModal .purchase-items-toolbar > input { grid-column: 1 / -1; width: 100%; max-width: none; }
        #pembelianModal .purchase-filter-check, #pembelianModal .purchase-select-all-mobile { min-height: 44px; font-size: .8rem; line-height: 1.4; }
        #pembelianModal .purchase-select-all-mobile { grid-column: 1 / -1; display: flex; align-items: center; gap: .5rem; margin: 0; padding: .35rem .5rem; border-radius: 8px; background: #f8fafc; cursor: pointer; }
        #pembelianModal .purchase-items-toolbar .form-check-input { width: 22px; height: 22px; margin: 0; flex-shrink: 0; }
        #pembelianModal #purchaseNextIssue { min-width: 0; font-size: .8rem; white-space: normal; }
        #pembelianModal .purchase-tax-toolbar { display: grid; grid-template-columns: minmax(0, 1fr) 88px; gap: .6rem; }
        #pembelianModal .purchase-tax-toolbar input { width: 100%; }
        #pembelianModal .purchase-tax-toolbar .btn, #pembelianModal .purchase-tax-toolbar small { grid-column: 1 / -1; min-width: 0; white-space: normal; }
        #pembelianModal .purchase-detail-summary { gap: .4rem; }
        #pembelianModal .purchase-detail-summary span { padding: .35rem .5rem; font-size: .72rem; }
        #pembelianModal .purchase-total-stats span { align-items: baseline; font-weight: 500; line-height: 1.5; }
        #pembelianModal .purchase-total-stats strong { flex-shrink: 0; max-width: 65%; font-size: .85rem; text-align: right; white-space: normal; overflow-wrap: anywhere; }
        #pembelianModal .purchase-grand-total strong { font-size: 1.2rem; white-space: normal; overflow-wrap: anywhere; }
        #pembelianModal .purchase-form-footer { display: grid; grid-template-columns: minmax(0, 1fr); flex-shrink: 0; gap: .5rem; padding: .65rem max(.75rem, env(safe-area-inset-right, 0px)) calc(.65rem + env(safe-area-inset-bottom, 0px)) max(.75rem, env(safe-area-inset-left, 0px)); background: #fff; box-shadow: 0 -5px 16px rgba(15, 23, 42, .08); }
        #pembelianModal .purchase-form-footer > * { margin: 0; }
        #pembelianModal .purchase-mobile-total { width: 100%; justify-content: space-between; min-width: 0; gap: .5rem; }
        #pembelianModal .purchase-mobile-total strong { max-width: 70%; font-size: 1rem; text-align: right; white-space: normal; overflow-wrap: anywhere; }
        #pembelianModal .purchase-save-actions { width: 100%; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; }
        #pembelianModal .purchase-save-actions .btn-light { display: none; }
        #pembelianModal .purchase-save-actions .btn { width: 100%; min-width: 0; min-height: 48px; justify-content: center; font-size: .8rem; line-height: 1.4; padding: .5rem; white-space: normal; margin: 0; }
        #pembelianModal .purchase-save-actions .btn i { display: none; }
    }
    @media (min-width: 576px) and (max-width: 991.98px) and (max-height: 500px) and (pointer: coarse) {
        #pembelianModal .modal-header { display: flex; align-items: center; gap: .75rem; min-height: 54px; padding-top: calc(.45rem + env(safe-area-inset-top, 0px)); padding-bottom: .45rem; }
        #pembelianModal .purchase-modal-header-meta { display: flex; margin: 0 0 0 auto; }
        #pembelianModal .modal-header .btn-close { top: calc(.3rem + env(safe-area-inset-top, 0px)); }
        #pembelianModal .purchase-form-footer { grid-template-columns: minmax(0, 1fr) minmax(0, 1.8fr); align-items: center; gap: .75rem; }
        #pembelianModal .purchase-mobile-total { flex-direction: column; align-items: flex-start; gap: .1rem; }
        #pembelianModal .purchase-mobile-total strong { max-width: 100%; text-align: left; }
    }
</style>
