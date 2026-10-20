<style>
    #penerimaanModalDetail { --receipt-ink: #203b45; --receipt-muted: #697c85; --receipt-line: #e3ebee; --receipt-teal: #0e756c; }
    #penerimaanModalDetail .modal-dialog { width: calc(100% - 2rem); max-width: 1420px; margin: 1rem auto; }
    #penerimaanModalDetail .modal-content { max-height: calc(100dvh - 2rem); border-radius: 18px; color: var(--receipt-ink); }
    #penerimaanModalDetail .modal-header { flex-direction: row; align-items: center; padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--receipt-line); background: #fff; }
    #penerimaanModalDetail .modal-title-wrap { flex: 1; }
    #penerimaanModalDetail .modal-title-icon { width: 48px; height: 48px; flex-basis: 48px; color: var(--receipt-teal); background: #eaf5f2; border: 1px solid #d5ebe4; border-radius: 14px; font-size: 1.5rem; }
    #penerimaanModalDetail .modal-title { margin: .15rem 0 .3rem; color: var(--receipt-ink); font-size: 1.15rem; letter-spacing: -.025em; }
    #penerimaanModalDetail .modal-subtitle { color: var(--receipt-muted); font-size: .76rem; line-height: 1.5; }
    #penerimaanModalDetail .receipt-detail-eyebrow { color: var(--receipt-teal); font-size: .61rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    #penerimaanModalDetail .receipt-detail-header-actions { display: flex; align-items: center; gap: 1rem; }
    #penerimaanModalDetail .btn-close { flex: 0 0 auto; filter: none; opacity: .55; margin: 0; width: 36px; height: 36px; padding: 0; background-size: 11px; border-radius: 50%; background-color: #f2f6f7; }
    #penerimaanModalDetail .modal-body { padding: 1.25rem 1.5rem; max-height: none !important; background: #f5f8f9; }
    #penerimaanModalDetail .receipt-detail-metrics { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
    #penerimaanModalDetail .receipt-detail-metric { min-width: 0; padding: 1.1rem 1.25rem; border: 1px solid var(--receipt-line); border-radius: 13px; background: #fff; }
    #penerimaanModalDetail .receipt-detail-metric > span { display: flex; align-items: center; gap: .45rem; color: var(--receipt-muted); font-size: .73rem; font-weight: 600; }
    #penerimaanModalDetail .receipt-detail-metric > span i { font-size: 1.1rem; }
    #penerimaanModalDetail .receipt-detail-metric > strong { display: block; margin: .45rem 0 .35rem; color: var(--receipt-ink); font-size: clamp(1.05rem, 1.7vw, 1.5rem); font-weight: 800; letter-spacing: -.035em; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    #penerimaanModalDetail .receipt-detail-metric > small { display: block; color: var(--receipt-muted); font-size: .67rem; line-height: 1.5; }
    #penerimaanModalDetail .receipt-detail-metric.is-total { background: #105e59; border-color: #105e59; box-shadow: 0 6px 16px #105e5912; }
    #penerimaanModalDetail .receipt-detail-metric.is-total > span, #penerimaanModalDetail .receipt-detail-metric.is-total > small { color: #c7e7df; }
    #penerimaanModalDetail .receipt-detail-metric.is-total > strong { color: #fff; }
    #penerimaanModalDetail .is-discount > strong, #penerimaanModalDetail .is-discount dd { color: var(--receipt-teal); }
    #penerimaanModalDetail .receipt-detail-layout { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 1.25rem; align-items: start; }
    #penerimaanModalDetail .receipt-detail-main { display: grid; gap: 1.25rem; min-width: 0; }
    #penerimaanModalDetail .receipt-detail-section { min-width: 0; background: #fff; border: 1px solid var(--receipt-line); border-radius: 13px; box-shadow: 0 3px 10px #203b4503; }
    #penerimaanModalDetail .receipt-detail-section-heading { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: 1rem 1.1rem; border-bottom: 1px solid #edf2f4; }
    #penerimaanModalDetail .receipt-detail-section-heading > div { display: flex; align-items: center; gap: .6rem; min-width: 0; }
    #penerimaanModalDetail .receipt-detail-section-heading h6 { margin: 0; color: var(--receipt-ink); font-size: .82rem; font-weight: 800; }
    #penerimaanModalDetail .receipt-detail-section-heading i { color: #84969e; font-size: 1.1rem; }
    #penerimaanModalDetail .receipt-detail-step { display: grid; place-items: center; width: 25px; height: 25px; border: 1px solid #dfebe8; border-radius: 7px; color: var(--receipt-teal); background: #f2f8f6; font-size: .62rem; font-weight: 800; }
    #penerimaanModalDetail dl { margin: 0; }
    #penerimaanModalDetail dt { color: var(--receipt-muted); font-size: .69rem; font-weight: 500; }
    #penerimaanModalDetail dd { margin: .35rem 0 0; color: var(--receipt-ink); font-size: .76rem; font-weight: 600; line-height: 1.6; overflow-wrap: anywhere; }
    #penerimaanModalDetail .receipt-detail-metadata { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.2rem 1rem; padding: 1.1rem; }
    #penerimaanModalDetail .receipt-detail-metadata > div { min-width: 0; }
    #penerimaanModalDetail #detailNomorPenerimaan { color: var(--receipt-teal); font-weight: 800; }
    #penerimaanModalDetail .receipt-detail-note { grid-column: 1 / -1; padding: .75rem .85rem; border: 1px solid #edf2f4; border-radius: 8px; background: #f9fbfc; }
    #penerimaanModalDetail .receipt-detail-note dd { font-weight: 400; white-space: pre-line; }
    #penerimaanModalDetail .receipt-detail-count { flex-shrink: 0; padding: .3rem .6rem; border-radius: 6px; color: var(--receipt-teal); background: #edf6f3; font-size: .69rem; font-weight: 700; }
    #penerimaanModalDetail .receipt-detail-searchbar { display: flex; gap: .75rem; align-items: end; padding: 1rem 1.1rem .6rem; }
    #penerimaanModalDetail .receipt-detail-search-field { position: relative; min-width: 0; flex: 1; }
    #penerimaanModalDetail .receipt-detail-search-field label { display: flex; gap: .35rem; align-items: center; margin-bottom: .45rem; color: var(--receipt-muted); font-size: .7rem; font-weight: 600; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-container { max-width: 100%; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-selection--single { height: 42px; border: 1px solid #dbe5e9; border-radius: 8px; background: #fff; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-selection__rendered { padding: 0 2.5rem 0 .75rem; color: var(--receipt-ink); line-height: 40px; font-size: .75rem; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-selection__placeholder { color: #7b8c94; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-selection__arrow { height: 40px; right: .4rem; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-selection__clear { margin-right: .4rem; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-container--focus .select2-selection, #penerimaanModalDetail .receipt-detail-search-field .select2-container--open .select2-selection { border-color: #32978b; box-shadow: 0 0 0 3px #0e756c0d; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-dropdown { border-color: #dbe5e9; border-radius: 8px; box-shadow: 0 12px 28px #203b451a; overflow: hidden; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-results__option { padding: .7rem .8rem; font-size: .76rem; overflow-wrap: anywhere; }
    #penerimaanModalDetail .receipt-detail-search-field .select2-results__option--highlighted[aria-selected] { background: #0e756c; color: #fff; }
    #penerimaanModalDetail .receipt-detail-reset { min-height: 42px; padding: .5rem .65rem; border: 1px solid #e2e9ed; background: #f8fafb; color: #5a6f79; font-size: .69rem; white-space: nowrap; }
    #penerimaanModalDetail .receipt-detail-reset:disabled { opacity: .5; }
    #penerimaanModalDetail .receipt-detail-filter-count { margin: 0; padding: 0 1.1rem .8rem; color: var(--receipt-muted); font-size: .66rem; }
    #penerimaanModalDetail .receipt-detail-table-wrap { overflow-x: auto; }
    #penerimaanModalDetail #detailReceiveTable { min-width: 870px; margin: 0; border-color: #edf2f4; }
    #penerimaanModalDetail #detailReceiveTable th { padding: .75rem .8rem; background: #f6f9fa; color: #73858e; border-bottom: 1px solid var(--receipt-line); font-size: .59rem; letter-spacing: .035em; }
    #penerimaanModalDetail #detailReceiveTable td { padding: 1rem .8rem; color: var(--receipt-ink); font-size: .73rem; vertical-align: top; }
    #penerimaanModalDetail #detailReceiveTable td:first-child { width: 22%; min-width: 170px; }
    #penerimaanModalDetail #detailReceiveTable td:nth-child(4) { min-width: 135px; }
    #penerimaanModalDetail #detailReceiveTable td:last-child { text-align: right; white-space: nowrap; }
    #penerimaanModalDetail #detailReceiveTable tbody tr:last-child td { border-bottom: 0; }
    #penerimaanModalDetail #detailReceiveTable tbody tr:hover { background: #fbfdfd; }
    #penerimaanModalDetail #detailReceiveTable strong { display: block; font-weight: 700; line-height: 1.5; overflow-wrap: anywhere; }
    #penerimaanModalDetail #detailReceiveTable small { display: block; margin-top: .3rem; color: #7b8d96; font-size: .63rem; line-height: 1.6; overflow-wrap: anywhere; }
    #penerimaanModalDetail .receipt-detail-discounts { display: flex; gap: .2rem; white-space: nowrap; }
    #penerimaanModalDetail .receipt-detail-discounts span { padding: .2rem .3rem; border: 1px solid #e3eee9; border-radius: 4px; color: var(--receipt-teal); background: #f4f9f6; font-size: .63rem; }
    #penerimaanModalDetail #detailReceiveTable .receipt-detail-line-total { color: var(--receipt-teal); font-variant-numeric: tabular-nums; }
    #penerimaanModalDetail .receipt-detail-table-note { display: flex; gap: .4rem; align-items: baseline; margin: 0; padding: .85rem 1.1rem; border-top: 1px solid #edf2f4; color: var(--receipt-muted); font-size: .64rem; line-height: 1.7; }
    #penerimaanModalDetail .receipt-detail-empty { display: grid; justify-items: center; gap: .45rem; padding: 2rem 1rem; text-align: center; }
    #penerimaanModalDetail [hidden] { display: none !important; }
    #penerimaanModalDetail .receipt-detail-empty i { color: #a2b7bd; font-size: 2rem; }
    #penerimaanModalDetail .receipt-detail-empty strong { font-size: .8rem; }
    #penerimaanModalDetail .receipt-detail-empty span { font-size: .72rem; color: var(--receipt-muted); }
    #penerimaanModalDetail .receipt-detail-invoice { position: sticky; top: 0; }
    #penerimaanModalDetail .receipt-detail-invoice-meta { display: grid; gap: .9rem; padding: 1.1rem; border-bottom: 1px dashed var(--receipt-line); }
    #penerimaanModalDetail .receipt-detail-invoice-meta > div { display: flex; gap: .75rem; justify-content: space-between; align-items: baseline; }
    #penerimaanModalDetail .receipt-detail-invoice-meta dd { max-width: 60%; margin: 0; text-align: right; font-size: .7rem; }
    #penerimaanModalDetail .receipt-detail-breakdown { padding: .8rem 1.1rem; }
    #penerimaanModalDetail .receipt-detail-breakdown > div, #penerimaanModalDetail .receipt-detail-payment dl > div { display: flex; justify-content: space-between; align-items: baseline; gap: .75rem; padding: .5rem 0; }
    #penerimaanModalDetail .receipt-detail-breakdown dt { line-height: 1.6; }
    #penerimaanModalDetail .receipt-detail-breakdown dt small { font-size: .64rem; }
    #penerimaanModalDetail .receipt-detail-breakdown dd, #penerimaanModalDetail .receipt-detail-payment dd { flex-shrink: 0; max-width: 60%; margin: 0; text-align: right; font-size: .73rem; font-variant-numeric: tabular-nums; }
    #penerimaanModalDetail .receipt-detail-invoice-total { margin-top: .4rem; border-top: 1px solid var(--receipt-line); }
    #penerimaanModalDetail .receipt-detail-invoice-total dt, #penerimaanModalDetail .receipt-detail-invoice-total dd { color: var(--receipt-ink); font-weight: 800; }
    #penerimaanModalDetail .receipt-detail-breakdown .receipt-detail-payable { margin: .5rem -.3rem 0; padding: .75rem .6rem; border-radius: 7px; background: #edf6f3; }
    #penerimaanModalDetail .receipt-detail-payable dt, #penerimaanModalDetail .receipt-detail-payable dd { color: var(--receipt-teal); font-weight: 800; }
    #penerimaanModalDetail .receipt-detail-payment { padding: 1rem 1.1rem; border-top: 1px dashed var(--receipt-line); }
    #penerimaanModalDetail .receipt-detail-payment-heading { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; margin-bottom: .6rem; }
    #penerimaanModalDetail .receipt-detail-payment-heading > span { font-size: .72rem; font-weight: 800; }
    #penerimaanModalDetail #detailStatusPembayaran { padding: .25rem .5rem; border-radius: 5px; background: #f1f4f6; color: #637580; font-size: .6rem; }
    #penerimaanModalDetail #detailStatusPembayaran.is-paid { background: #e9f6ee; color: #25734b; }
    #penerimaanModalDetail #detailStatusPembayaran.is-partial { background: #fff5df; color: #926c18; }
    #penerimaanModalDetail .receipt-detail-debt { margin-top: .3rem; border-top: 1px solid var(--receipt-line); }
    #penerimaanModalDetail .receipt-detail-debt dd { font-weight: 800; }
    #penerimaanModalDetail .modal-footer { gap: .6rem; padding: .9rem 1.5rem; }
    #penerimaanModalDetail .modal-footer .btn { min-height: 42px; margin: 0; justify-content: center; font-size: .75rem; }
    #penerimaanModalDetail #detailPrintPenerimaan { background: #0e756c; border-color: #0e756c; }
    #penerimaanModalDetail #detailPrintPenerimaan:hover { background: #0b5e57; }
    #penerimaanModalDetail .receipt-detail-footer-note { display: flex; align-items: center; gap: .4rem; margin-right: auto; color: #84949d; font-size: .66rem; }
    #penerimaanModalDetail :is(button, a, .select2-selection):focus-visible { outline: 3px solid #66b7ab; outline-offset: 3px; }

    @media (max-width: 1199.98px) {
        #penerimaanModalDetail .receipt-detail-layout { grid-template-columns: minmax(0, 1fr) 275px; gap: 1rem; }
        #penerimaanModalDetail .receipt-detail-metadata { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #penerimaanModalDetail .receipt-detail-searchbar { flex-wrap: wrap; }
        #penerimaanModalDetail .receipt-detail-search-field { flex-basis: 100%; }
    }
    @media (max-width: 991.98px) {
        #penerimaanModalDetail .receipt-detail-layout { grid-template-columns: minmax(0, 1fr); }
        #penerimaanModalDetail .receipt-detail-invoice { position: static; }
        #penerimaanModalDetail .receipt-detail-metric { padding: 1rem; }
        #penerimaanModalDetail .receipt-detail-metric > strong { font-size: 1.15rem; }
        #penerimaanModalDetail .receipt-detail-searchbar { flex-wrap: nowrap; }
        #penerimaanModalDetail .receipt-detail-search-field { flex-basis: auto; }
    }
    @media (max-width: 767.98px) {
        #penerimaanModalDetail .modal-dialog { width: 100%; height: 100%; min-height: 100%; margin: 0; }
        #penerimaanModalDetail .modal-content { height: 100dvh; max-height: 100dvh !important; border-radius: 0; }
        #penerimaanModalDetail .modal-header { align-items: flex-start; padding: calc(.9rem + env(safe-area-inset-top)) 1rem .9rem; gap: .65rem; }
        #penerimaanModalDetail .modal-title-icon { display: none; }
        #penerimaanModalDetail .modal-title { font-size: .96rem; line-height: 1.5; }
        #penerimaanModalDetail .modal-subtitle { font-size: .67rem; }
        #penerimaanModalDetail .receipt-detail-header-actions { flex-direction: column-reverse; gap: .5rem; align-items: flex-end; }
        #penerimaanModalDetail .receipt-detail-header-actions .purchase-status { padding: .25rem .45rem; font-size: .6rem; white-space: nowrap; }
        #penerimaanModalDetail .btn-close { width: 36px; height: 36px; }
        #penerimaanModalDetail .modal-body { padding: .85rem; }
        #penerimaanModalDetail .receipt-detail-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; margin-bottom: .85rem; }
        #penerimaanModalDetail .receipt-detail-metric { padding: .85rem; border-radius: 10px; }
        #penerimaanModalDetail .receipt-detail-metric.is-total { grid-column: 1 / -1; }
        #penerimaanModalDetail .receipt-detail-metric.is-total > strong { font-size: 1.6rem; }
        #penerimaanModalDetail .receipt-detail-metric > strong { font-size: 1.05rem; }
        #penerimaanModalDetail .receipt-detail-metric > span { gap: .3rem; font-size: .68rem; }
        #penerimaanModalDetail .receipt-detail-metric > small { font-size: .6rem; }
        #penerimaanModalDetail .receipt-detail-main, #penerimaanModalDetail .receipt-detail-layout { gap: .85rem; }
        #penerimaanModalDetail .receipt-detail-section-heading { padding: .85rem; }
        #penerimaanModalDetail .receipt-detail-section-heading h6 { font-size: .76rem; }
        #penerimaanModalDetail .receipt-detail-metadata { padding: .85rem; gap: 1rem .75rem; }
        #penerimaanModalDetail dt { font-size: .64rem; }
        #penerimaanModalDetail dd { font-size: .7rem; }
        #penerimaanModalDetail .receipt-detail-searchbar { padding: .85rem .85rem .6rem; flex-wrap: wrap; gap: .5rem; }
        #penerimaanModalDetail .receipt-detail-search-field { flex-basis: 100%; }
        #penerimaanModalDetail .receipt-detail-reset { min-height: 44px; }
        #penerimaanModalDetail .receipt-detail-search-field .select2-selection--single { height: 46px; }
        #penerimaanModalDetail .receipt-detail-search-field .select2-selection__rendered { line-height: 44px; font-size: .75rem; }
        #penerimaanModalDetail .receipt-detail-search-field .select2-selection__arrow { height: 44px; }
        #penerimaanModalDetail .receipt-detail-search-field .select2-search__field { font-size: 16px; min-height: 44px; }
        #penerimaanModalDetail .receipt-detail-search-field .select2-results__option { min-height: 44px; }
        #penerimaanModalDetail .receipt-detail-filter-count { padding-inline: .85rem; }
        #penerimaanModalDetail .receipt-detail-table-wrap { padding: 0 .85rem .85rem; overflow: visible; }
        #penerimaanModalDetail #detailReceiveTable { min-width: 0; }
        #penerimaanModalDetail #detailReceiveTable tbody { gap: .75rem; }
        #penerimaanModalDetail #detailReceiveTable tbody tr { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border-color: #dfe8ec; border-radius: 9px; box-shadow: none; }
        #penerimaanModalDetail #detailReceiveTable tbody td { display: block; min-width: 0; width: auto; padding: .7rem !important; border-top: 1px solid #edf2f4; font-size: .73rem; overflow-wrap: anywhere; }
        #penerimaanModalDetail #detailReceiveTable tbody td::before { display: block; margin-bottom: .4rem; font-size: .57rem; letter-spacing: .04em; }
        #penerimaanModalDetail #detailReceiveTable tbody td:first-child { grid-column: 1 / -1; width: auto; min-width: 0; border-top: 0; background: #f6faf9; }
        #penerimaanModalDetail #detailReceiveTable tbody td:first-child::before { display: none; }
        #penerimaanModalDetail #detailReceiveTable tbody td:nth-child(even) { border-right: 1px solid #edf2f4; }
        #penerimaanModalDetail #detailReceiveTable tbody td:last-child { text-align: left; white-space: normal; }
        #penerimaanModalDetail #detailReceiveTable strong { font-size: .74rem; }
        #penerimaanModalDetail .receipt-detail-discounts { flex-wrap: wrap; }
        #penerimaanModalDetail .receipt-detail-table-note { padding: .75rem .85rem; }
        #penerimaanModalDetail .modal-footer { grid-template-columns: minmax(0, .65fr) minmax(0, 1.35fr); padding: .75rem .85rem calc(.75rem + env(safe-area-inset-bottom)); gap: .5rem; }
        #penerimaanModalDetail .receipt-detail-footer-note { display: none; }
        #penerimaanModalDetail .modal-footer .btn { min-height: 46px; padding-inline: .5rem !important; font-size: .7rem; }
    }
</style>
