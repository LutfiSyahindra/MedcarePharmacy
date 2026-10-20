<style>
    #pembelianModal .purchase-mobile-navigation,
    #pembelianModal .purchase-review-total,
    #pembelianModal .purchase-footer-item-count,
    #pembelianModal .purchase-additional-costs > summary { display: none; }
    #pembelianModal .purchase-field-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .25rem .5rem; margin-bottom: .35rem; }
    #pembelianModal .purchase-field-heading .form-label { margin: 0; }
    #pembelianModal .purchase-field-heading .purchase-number-mode { margin: 0; font-size: .68rem; }
    #pembelianModal .purchase-footer-total-copy { display: flex; flex-wrap: wrap; align-items: baseline; gap: .25rem .75rem; min-width: 0; }
    #pembelianModal .purchase-mobile-total { text-align: left; }
    #pembelianModal .purchase-mobile-total strong { max-width: 100%; }

    @media (max-width: 767.98px), (max-width: 991.98px) and (pointer: coarse) {
        #pembelianModal { --purchase-mobile-canvas: #f3f6f7; --purchase-mobile-line: #e3eaed; }
        #pembelianModal .modal-content { background: var(--purchase-mobile-canvas); }
        #pembelianModal .modal-header { padding: calc(.8rem + env(safe-area-inset-top, 0px)) calc(3.5rem + env(safe-area-inset-right, 0px)) .65rem max(.95rem, env(safe-area-inset-left, 0px)); border: 0; color: var(--purchase-text); background: #fff; }
        #pembelianModal .modal-title-wrap { gap: .65rem; }
        #pembelianModal .modal-title-icon { display: grid; width: 36px; height: 36px; flex: 0 0 36px; border: 1px solid #d9ebe6; border-radius: 12px; color: var(--purchase-primary); background: #eef7f4; font-size: 1.2rem; }
        #pembelianModal .modal-title { color: var(--purchase-text); font-size: 1.05rem; font-weight: 750; letter-spacing: -.025em; line-height: 1.3; }
        #pembelianModal .purchase-modal-header-meta { margin: -.2rem 0 0 3rem; }
        #pembelianModal #purchaseDraftStatus { display: flex; align-items: baseline; gap: .35rem; padding: 0; border: 0; border-radius: 0; color: #667580; background: transparent; font-size: .65rem; font-weight: 500; line-height: 1.5; }
        #pembelianModal #purchaseDraftStatus::before { content: ''; display: block; flex: 0 0 5px; width: 5px; height: 5px; border-radius: 50%; background: #8a9ba5; }
        #pembelianModal .modal-header .btn-close { top: calc(.85rem + env(safe-area-inset-top, 0px)); right: max(.5rem, env(safe-area-inset-right, 0px)); width: 40px; height: 40px; border: 1px solid var(--purchase-mobile-line); border-radius: 12px; background-color: #f8fafb; background-size: 11px; filter: none; opacity: .8; }

        #pembelianModal .purchase-mobile-navigation { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); flex-shrink: 0; gap: .35rem; padding: .35rem max(.85rem, env(safe-area-inset-right, 0px)) .75rem max(.85rem, env(safe-area-inset-left, 0px)); border-bottom: 1px solid var(--purchase-mobile-line); background: #fff; }
        #pembelianModal .purchase-section-link { display: flex; justify-content: center; align-items: center; gap: .4rem; min-width: 0; min-height: 44px; padding: .45rem .25rem; border: 1px solid transparent; border-radius: 12px; color: #63717d; background: #f5f7f8; font: inherit; font-size: .72rem; font-weight: 600; cursor: pointer; touch-action: manipulation; }
        #pembelianModal .purchase-step-number { display: grid; place-items: center; flex: 0 0 22px; width: 22px; height: 22px; border: 1px solid #dde5e8; border-radius: 8px; color: #687783; background: #fff; font-size: .65rem; font-variant-numeric: tabular-nums; }
        #pembelianModal .purchase-section-link.is-current { border-color: #cce5de; color: var(--purchase-primary-strong); background: #edf7f3; }
        #pembelianModal .is-current .purchase-step-number { border-color: var(--purchase-primary); color: #fff; background: var(--purchase-primary); }
        #pembelianModal .purchase-section-link:focus-visible,
        #pembelianModal .purchase-review-total:focus-visible { outline: 2px solid var(--purchase-primary); outline-offset: 2px; }

        #pembelianModal .modal-body { padding: 1rem max(.85rem, env(safe-area-inset-right, 0px)) 1.25rem max(.85rem, env(safe-area-inset-left, 0px)); background: var(--purchase-mobile-canvas); scroll-padding-block: 1rem; }
        #pembelianModal .purchase-form-section { border: 1px solid var(--purchase-mobile-line); border-radius: 18px; background: #fff; box-shadow: 0 3px 14px rgba(29, 53, 61, .025); margin-bottom: 1.1rem; }
        #pembelianModal .purchase-form-section-header { padding: 1rem; border-bottom: 1px solid #edf1f3; border-radius: 18px 18px 0 0; background: #fff; }
        #pembelianModal .purchase-form-section-title { gap: .65rem; align-items: center; }
        #pembelianModal .purchase-form-section-title > i { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 11px; color: var(--purchase-primary); background: #edf6f3; font-size: 1.15rem; }
        #pembelianModal .purchase-form-section-title strong { color: var(--purchase-text); font-size: .9rem; font-weight: 700; letter-spacing: -.015em; line-height: 1.4; }
        #pembelianModal .purchase-form-section-title small { color: #687784; font-size: .72rem; font-weight: 400; line-height: 1.55; }
        #pembelianModal .purchase-form-section-body { padding: 1rem; }
        #pembelianModal .purchase-form-section-body .row { --bs-gutter-y: 1rem; }
        #pembelianModal .form-label { color: #435361; font-size: .76rem; font-weight: 600; margin-bottom: .45rem; line-height: 1.5; }
        #pembelianModal .form-control,
        #pembelianModal .form-select { min-height: 48px; padding: .7rem .8rem; border-color: #dbe4e8; border-radius: 12px; color: #243744; background-color: #fff; box-shadow: 0 1px 2px rgba(22, 43, 54, .02); }
        #pembelianModal .form-control:focus,
        #pembelianModal .form-select:focus { border-color: #76b7a9; box-shadow: 0 0 0 3px rgba(15, 118, 110, .09); }
        #pembelianModal .form-control.is-invalid,
        #pembelianModal .form-select.is-invalid { border-color: var(--purchase-danger); }
        #pembelianModal .form-control[readonly],
        #pembelianModal .form-select:disabled { color: #687784; background-color: #f5f8f9; box-shadow: none; }
        #pembelianModal .purchase-input-icon .form-control { padding-left: 2.3rem; }
        #pembelianModal .input-group-text { padding-inline: .8rem; border-color: #dbe4e8; border-radius: 12px; color: #687784; background: #f8fafb; font-size: .85rem; }
        #pembelianModal .input-group > .input-group-text:first-child { border-top-right-radius: 0; border-bottom-right-radius: 0; }
        #pembelianModal .input-group > .input-group-text:last-child:not(:first-child) { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        #pembelianModal .input-group > .form-control:not(:last-child) { border-top-right-radius: 0; border-bottom-right-radius: 0; }
        #pembelianModal .input-group > .form-control:not(:first-child) { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        #pembelianModal .purchase-field-heading .purchase-number-mode { padding: .2rem .45rem; border: 1px solid #e0ece7; border-radius: 6px; color: var(--purchase-primary-strong); background: #f2f8f5; font-size: .6rem; }
        #pembelianModal .purchase-information-section .purchase-field-hint { color: #687784; font-size: .7rem; line-height: 1.5; }
        #pembelianModal .purchase-notes-details summary { gap: .4rem; border-color: var(--purchase-mobile-line); border-radius: 12px; color: #536471; background: #fafcfc; font-size: .75rem; }
        #pembelianModal .purchase-notes-details summary > i { color: var(--purchase-primary); font-size: 1.05rem; }
        #pembelianModal .select2-container--default .select2-selection--single { min-height: 48px; border-color: #dbe4e8; border-radius: 12px; background: #fff; }
        #pembelianModal .select2-container--default .select2-selection--single .select2-selection__rendered { padding: .75rem 1.8rem .75rem .8rem; color: #243744; line-height: 1.5; }
        #pembelianModal .select2-container--open .select2-selection--single { border-color: #76b7a9; box-shadow: 0 0 0 3px rgba(15, 118, 110, .09); }
        #pembelianModal .select2-dropdown { overflow: hidden; border-color: #dbe4e8; border-radius: 12px; box-shadow: 0 12px 30px rgba(22, 43, 54, .12); }
        #pembelianModal .select2-search--dropdown { padding: .6rem; }
        #pembelianModal .select2-search--dropdown .select2-search__field { min-height: 44px; border: 1px solid #dbe4e8; border-radius: 8px; padding: .5rem; }

        #pembelianModal .purchase-items-section { border: 0; background: transparent; box-shadow: none; }
        #pembelianModal .purchase-items-section > .purchase-form-section-header { padding: .15rem .15rem .9rem; border: 0; background: transparent; }
        #pembelianModal .purchase-items-section > .purchase-form-section-body { padding: 0; }
        #pembelianModal .purchase-detail-actions { gap: .5rem; margin-top: .25rem; }
        #pembelianModal .purchase-detail-actions .btn { min-height: 46px; padding: .65rem .5rem; border-radius: 12px; font-size: .74rem; font-weight: 600; }
        #pembelianModal .purchase-detail-actions #toggleMedicinePicker { border-color: var(--purchase-primary); color: #fff; background: var(--purchase-primary); box-shadow: 0 4px 10px rgba(15, 118, 110, .12); }
        #pembelianModal #addDetail { border-color: #d8e3e5; color: #49616c; background: #fff; }
        #pembelianModal .purchase-detail-actions .btn i { font-size: 1.05rem; }
        #pembelianModal .purchase-entry-tools { border-color: var(--purchase-mobile-line); border-radius: 12px; background: #fff; }
        #pembelianModal .purchase-entry-tools > summary { padding: .6rem .8rem; color: #536471; font-size: .74rem; }
        #pembelianModal .purchase-entry-tools > summary > span > i { color: var(--purchase-primary); font-size: 1.05rem; }
        #pembelianModal .purchase-detail-summary { gap: .4rem; margin-bottom: .8rem; }
        #pembelianModal .purchase-detail-summary span { padding: .35rem .55rem; border: 1px solid #dfe8e9; border-radius: 7px; color: #536471; background: #fff; font-size: .65rem; font-weight: 500; }
        #pembelianModal .purchase-entry-table tbody { gap: 1rem; }
        #pembelianModal .purchase-detail-row { grid-template-columns: minmax(0, 1fr) 100px; gap: 1rem .75rem; padding: 1rem; border: 1px solid var(--purchase-mobile-line); border-radius: 18px; background: #fff; box-shadow: 0 4px 16px rgba(29, 53, 61, .035); }
        #pembelianModal .purchase-detail-row > .purchase-row-selection { border-color: #edf1f3; padding-bottom: .65rem; }
        #pembelianModal .purchase-detail-number { min-height: 28px; padding: .25rem .55rem; border: 1px solid #dbece6; border-radius: 8px; background: #f0f8f4; font-size: .7rem; }
        #pembelianModal .purchase-row-choice { min-height: 44px; margin-block: -.4rem; color: #7c8993; font-size: .7rem; }
        #pembelianModal .purchase-row-check { width: 20px; height: 20px; border-color: #c8d5db; border-radius: 6px; }
        #pembelianModal .purchase-current-stock { gap: .3rem; margin-top: .55rem; color: #687784; font-size: .7rem; }
        #pembelianModal .purchase-current-stock.is-empty { color: #6e7f8b; font-size: .65rem; }
        #pembelianModal .purchase-current-stock.is-available { color: #42725e; }
        #pembelianModal .purchase-current-stock.is-empty-stock { color: #b9515c; }
        #pembelianModal .purchase-unit-tools:has(> .d-none):not(:has(> :not(.d-none))) { margin: 0; }
        #pembelianModal .purchase-unit-price { margin-top: .45rem; color: #687784; font-size: .7rem; line-height: 1.5; }
        #pembelianModal .purchase-detail-row > .purchase-subtotal-field { grid-row: auto; padding: .7rem .8rem; border: 1px solid #dfede8; border-radius: 12px; background: #f0f8f4; }
        #pembelianModal .purchase-subtotal-field .form-label { font-size: .7rem; color: #557267; }
        #pembelianModal .purchase-subtotal-field .form-control { min-height: 30px; padding: 0; border: 0; color: var(--purchase-primary-strong); background: transparent; font-size: 1rem; }
        #pembelianModal .purchase-row-actions { gap: .6rem; }
        #pembelianModal .purchase-item-adjustments summary { padding: .65rem .75rem; border-color: #e3eaed; border-radius: 12px; background: #f8fafb; }
        #pembelianModal .purchase-adjustments-title { color: #435864; font-size: .72rem; }
        #pembelianModal .purchase-adjustments-value { margin-top: .25rem; color: #687784; font-size: .65rem; line-height: 1.5; }
        #pembelianModal .purchase-row-actions .remove-detail { border-color: #f2dfe1; border-radius: 12px; color: #bf5360; background: #fff8f8; }
        #pembelianModal .purchase-row-actions .remove-detail:disabled { opacity: .4; }
        #pembelianModal .purchase-detail-row.is-selected { border-color: #6bad9a; background: #fbfefc; box-shadow: 0 0 0 2px rgba(15, 118, 110, .06); }
        #pembelianModal .purchase-detail-row.has-issues { border-color: #db8790; }
        #pembelianModal .purchase-add-another { min-height: 48px; margin-top: 1rem; border-color: #b5d6ca; border-radius: 12px; color: var(--purchase-primary-strong); background: #edf5f1; font-size: .8rem; font-weight: 600; }

        #pembelianModal .purchase-form-aside { gap: .85rem; }
        #pembelianModal .purchase-additional-costs > summary { display: flex; align-items: center; gap: .5rem; min-height: 44px; color: #435864; font-size: .76rem; list-style: none; cursor: pointer; }
        #pembelianModal .purchase-additional-costs > summary::-webkit-details-marker { display: none; }
        #pembelianModal .purchase-additional-costs > summary > span:first-child { display: flex; align-items: center; gap: .4rem; }
        #pembelianModal .purchase-additional-costs > summary > span:first-child > i { color: var(--purchase-primary); font-size: 1.05rem; }
        #pembelianModal #purchaseAdditionalCostsValue { margin-left: auto; color: #687784; font-size: .65rem; text-align: right; overflow-wrap: anywhere; }
        #pembelianModal .purchase-additional-costs > summary > i { color: #81909b; }
        #pembelianModal .purchase-additional-costs[open] > summary > i { transform: rotate(180deg); }
        #pembelianModal .purchase-additional-costs-fields { padding-top: .85rem; }
        #pembelianModal .purchase-total-panel { min-width: 0; padding: 1.1rem; border: 1px solid #d6e7df; border-radius: 18px; color: var(--purchase-primary-strong); background: #fafffc; box-shadow: 0 4px 16px rgba(29, 53, 61, .025); }
        #pembelianModal .purchase-total-panel span { color: #64786e; text-transform: none; letter-spacing: 0; }
        #pembelianModal .purchase-total-copy > span { color: #284f43; font-size: .9rem; font-weight: 700; }
        #pembelianModal .purchase-total-copy > small { margin-top: .35rem; color: #687f73; font-size: .7rem; line-height: 1.6; }
        #pembelianModal .purchase-total-stats { gap: .8rem; margin: 1.1rem 0; }
        #pembelianModal .purchase-total-stats span { border-radius: 0; font-size: .73rem; font-weight: 400; }
        #pembelianModal .purchase-total-stats strong { color: #345c4d; font-size: .82rem; font-weight: 600; font-variant-numeric: tabular-nums; }
        #pembelianModal .purchase-grand-total { align-items: baseline; padding-top: 1rem; border-color: #dcebe2; }
        #pembelianModal .purchase-grand-total span { color: #284f43; font-size: .8rem; font-weight: 600; }
        #pembelianModal .purchase-grand-total strong { font-size: 1.35rem; font-weight: 750; letter-spacing: -.025em; font-variant-numeric: tabular-nums; }
        #pembelianModal .purchase-save-hint { display: flex; align-items: baseline; gap: .4rem; padding-inline: .35rem; font-size: .7rem; line-height: 1.6; }

        #pembelianModal .purchase-form-footer { gap: .7rem; margin: 0; padding: .75rem max(1rem, env(safe-area-inset-right, 0px)) calc(.75rem + env(safe-area-inset-bottom, 0px)) max(1rem, env(safe-area-inset-left, 0px)); border-top: 1px solid var(--purchase-mobile-line); border-radius: 18px 18px 0 0; background: #fff; box-shadow: 0 -4px 22px rgba(29, 53, 61, .055); }
        #pembelianModal .purchase-mobile-total { flex-wrap: nowrap; align-items: center; gap: .75rem; }
        #pembelianModal .purchase-footer-total-copy { display: grid; gap: .1rem; }
        #pembelianModal .purchase-footer-total-copy > span { color: #687784; font-size: .65rem; line-height: 1.5; }
        #pembelianModal .purchase-footer-item-count { display: inline; }
        #pembelianModal .purchase-mobile-total strong { max-width: 100%; color: #234f42; font-size: 1.15rem; font-weight: 750; letter-spacing: -.025em; text-align: left; line-height: 1.35; font-variant-numeric: tabular-nums; }
        #pembelianModal .purchase-review-total { display: flex; flex-shrink: 0; align-items: center; gap: .3rem; min-height: 44px; padding: .4rem 0 .4rem .5rem; border: 0; border-radius: 6px; color: var(--purchase-primary); background: transparent; font: inherit; font-size: .72rem; font-weight: 600; cursor: pointer; }
        #pembelianModal .purchase-review-total i { font-size: 1.1rem; }
        #pembelianModal .purchase-save-actions { grid-template-columns: minmax(0, .95fr) minmax(0, 1.25fr); gap: .6rem; }
        #pembelianModal .purchase-save-actions .btn { gap: .35rem; min-height: 48px; padding: .65rem .5rem; border-radius: 12px; font-size: .78rem; font-weight: 650; }
        #pembelianModal .purchase-save-actions .btn i { display: inline-block; font-size: 1.05rem; }
        #pembelianModal #saveDraftForm { border-color: #d9e5df; color: var(--purchase-primary-strong); background: #f4f9f6; }
        #pembelianModal #submitForm { border-color: var(--purchase-primary); color: #fff; background: var(--purchase-primary); box-shadow: 0 4px 12px rgba(15, 118, 110, .15); }
        #pembelianModal #submitForm:hover { background: var(--purchase-primary-strong); }
        #pembelianModal .purchase-medicine-picker { border-color: #dce5e7; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 18px rgba(29, 53, 61, .04); }
        #pembelianModal .purchase-picker-table tbody tr { border-radius: 12px; }
        #pembelianModal .purchase-picker-table-wrap { background: #f3f6f7; }
        #pembelianModal .purchase-medicine-picker-footer { background: #f6fbf8; }
        #pembelianModal .purchase-medicine-picker-footer .btn { min-height: 48px; border-radius: 12px; }
    }

    @media (min-width: 576px) and (max-width: 991.98px) and (max-height: 500px) and (pointer: coarse) {
        #pembelianModal .modal-header { padding-block: .4rem; min-height: 52px; }
        #pembelianModal .purchase-modal-header-meta { margin: 0 0 0 auto; }
        #pembelianModal .modal-header .btn-close { top: .35rem; }
        #pembelianModal .purchase-mobile-navigation { padding-block: .25rem; }
        #pembelianModal .purchase-section-link { min-height: 40px; padding-block: .25rem; }
        #pembelianModal .purchase-form-footer { padding-block: .5rem; border-radius: 0; }
        #pembelianModal .purchase-review-total { display: none; }
        #pembelianModal .purchase-save-actions .btn { min-height: 44px; }
    }
    @media (prefers-reduced-motion: reduce) {
        #pembelianModal *, #pembelianModal *::before, #pembelianModal *::after { scroll-behavior: auto !important; transition: none !important; }
    }
</style>
