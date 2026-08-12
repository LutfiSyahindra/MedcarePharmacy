<style>
    /*
     * Prescription cashier 2026
     * A calm, clinical visual layer shared by standard and compound prescriptions.
     */
    .pos-prescription-type-modal.rx-simple {
        --rx-bg: #f3f5f4;
        --rx-surface: #ffffff;
        --rx-surface-soft: #f8faf9;
        --rx-ink: #172520;
        --rx-ink-soft: #3f5049;
        --rx-muted: #75837d;
        --rx-line: #dfe5e1;
        --rx-line-strong: #cfd8d3;
        --rx-primary: #185b49;
        --rx-primary-hover: #114a3b;
        --rx-primary-soft: #eaf4f0;
        --rx-accent: #c79a45;
        --rx-accent-soft: #fbf5e8;
        --rx-danger: #a74851;
        --rx-compound: #65528d;
        --rx-compound-soft: #f2eef8;
        --rx-shadow-sm: 0 5px 18px rgba(24, 43, 35, .055);
        --rx-shadow-md: 0 18px 50px rgba(24, 43, 35, .1);
        color: var(--rx-ink);
        font-family: "Segoe UI Variable", "Segoe UI", Inter, sans-serif;
    }

    .rx-simple .modal-content {
        color: var(--rx-ink);
        background:
            radial-gradient(circle at 5% 0, rgba(32, 103, 82, .055), transparent 26rem),
            var(--rx-bg);
    }

    .rx-simple .pos-rx-brand-mark {
        display: grid !important;
        width: 46px !important;
        height: 46px !important;
        flex: 0 0 auto;
        place-items: center;
        color: #fff;
        border: 0 !important;
        border-radius: 14px !important;
        background: var(--rx-primary) !important;
        box-shadow: 0 8px 20px rgba(24, 91, 73, .18) !important;
    }

    .rx-simple .pos-rx-brand-mark b {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 22px;
        font-weight: 700 !important;
        letter-spacing: -.06em;
        line-height: 1;
    }

    .rx-simple .pos-rx-brand-mark em {
        position: relative;
        top: 2px;
        margin-left: 1px;
        color: #cfe8df;
        font-size: .62em;
        font-weight: 600;
    }

    /* Prescription type chooser */
    .rx-simple .pos-prescription-type-step {
        min-height: 100dvh;
        padding: clamp(16px, 3vw, 42px);
        background:
            linear-gradient(rgba(243, 245, 244, .88), rgba(243, 245, 244, .96)),
            radial-gradient(circle at 12% 14%, rgba(24, 91, 73, .13), transparent 24rem),
            radial-gradient(circle at 88% 86%, rgba(199, 154, 69, .1), transparent 24rem);
    }

    .rx-simple .pos-prescription-type-step-shell {
        width: min(980px, 100%);
        overflow: hidden;
        border: 1px solid rgba(207, 216, 211, .88);
        border-radius: 24px;
        background: rgba(255, 255, 255, .98);
        box-shadow: var(--rx-shadow-md), inset 0 1px 0 rgba(255, 255, 255, .9);
    }

    .rx-simple .pos-prescription-type-step .modal-header {
        position: relative;
        align-items: center;
        padding: 24px 28px 18px;
        color: var(--rx-ink);
        border: 0;
        background: #fff;
    }

    .rx-simple .pos-prescription-type-step .modal-header::after {
        position: absolute;
        right: 28px;
        bottom: 0;
        left: 28px;
        height: 1px;
        background: var(--rx-line);
        content: "";
    }

    .rx-simple .pos-prescription-modal-heading {
        align-items: center;
        gap: 14px;
    }

    .rx-simple .pos-prescription-modal-heading small {
        color: var(--rx-primary);
        font-size: 10px !important;
        font-weight: 750;
        letter-spacing: .12em;
    }

    .rx-simple .pos-prescription-modal-heading h2 {
        margin: 3px 0 0;
        color: var(--rx-ink);
        font-size: 22px;
        font-weight: 720;
        letter-spacing: -.035em;
        line-height: 1.2;
    }

    .rx-simple .pos-prescription-modal-heading p {
        margin: 3px 0 0;
        color: var(--rx-muted);
        font-size: 12px;
    }

    .rx-simple .pos-prescription-type-step .btn-close {
        width: 34px;
        height: 34px;
        margin: 0;
        padding: 0;
        border: 1px solid var(--rx-line);
        border-radius: 10px;
        background-color: var(--rx-surface-soft);
        filter: none;
        opacity: .72;
        transition: opacity .18s ease, background .18s ease;
    }

    .rx-simple .pos-prescription-type-step .btn-close:hover {
        background-color: #edf1ef;
        opacity: 1;
    }

    .rx-simple .pos-prescription-type-step .modal-body {
        padding: 22px 28px 26px;
        background: #fff;
    }

    .rx-simple .pos-prescription-modal-intro {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px;
        margin: 0 0 18px;
        padding: 0;
        border: 0;
        background: transparent;
    }

    .rx-simple .pos-prescription-modal-intro > span:first-child {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        color: var(--rx-primary);
        border: 1px solid #d4e5de;
        border-radius: 11px;
        background: var(--rx-primary-soft);
        font-size: 18px;
    }

    .rx-simple .pos-prescription-modal-intro > div {
        display: grid;
        gap: 1px;
    }

    .rx-simple .pos-prescription-modal-intro > div small {
        color: #8b9791;
        font-size: 9px !important;
        font-weight: 750;
        letter-spacing: .1em;
    }

    .rx-simple .pos-prescription-modal-intro > div strong {
        color: var(--rx-ink);
        font-size: 15px !important;
        font-weight: 680;
    }

    .rx-simple .pos-prescription-modal-intro > div p {
        margin: 1px 0 0;
        color: var(--rx-muted);
        font-size: 11px;
    }

    .rx-simple .pos-rx-intro-badge {
        display: inline-flex;
        width: auto !important;
        height: auto !important;
        align-self: center;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        color: #376958 !important;
        border: 1px solid #d4e4dd;
        border-radius: 999px;
        background: #f5faf8 !important;
        font-size: 10px !important;
        font-weight: 650;
        white-space: nowrap;
    }

    .rx-simple .pos-prescription-type-grid {
        gap: 14px;
    }

    .rx-simple .pos-prescription-type-option {
        display: grid;
        min-height: 294px;
        grid-template-rows: auto minmax(0, 1fr) auto;
        align-items: stretch;
        gap: 15px;
        padding: 20px;
        color: var(--rx-ink-soft);
        border: 1px solid var(--rx-line);
        border-radius: 18px;
        background: var(--rx-surface);
        box-shadow: none;
        isolation: isolate;
    }

    .rx-simple .pos-prescription-type-option::before {
        position: absolute;
        z-index: -1;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(145deg, rgba(234, 244, 240, .62), transparent 50%);
        content: "";
        opacity: 0;
        transition: opacity .18s ease;
    }

    .rx-simple .pos-prescription-type-option:nth-child(2)::before {
        background: linear-gradient(145deg, rgba(242, 238, 248, .75), transparent 50%);
    }

    .rx-simple .pos-prescription-type-option:hover {
        border-color: #b9cec5;
        background: #fff;
        box-shadow: 0 14px 34px rgba(28, 54, 44, .095);
        transform: translateY(-3px);
    }

    .rx-simple .pos-prescription-type-option:nth-child(2):hover {
        border-color: #cabfe0;
    }

    .rx-simple .pos-prescription-type-option:hover::before,
    .rx-simple .pos-prescription-type-option.is-active::before {
        opacity: 1;
    }

    .rx-simple .pos-rx-option-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rx-simple .pos-prescription-type-icon {
        display: grid;
        width: 52px;
        height: 52px;
        place-items: center;
        color: var(--rx-primary);
        border: 1px solid #d5e5df;
        border-radius: 15px;
        background: var(--rx-primary-soft);
        box-shadow: none;
        font-size: 25px;
    }

    .rx-simple .pos-prescription-type-option:nth-child(2) .pos-prescription-type-icon {
        color: var(--rx-compound);
        border-color: #dfd6ec;
        background: var(--rx-compound-soft);
    }

    .rx-simple .pos-rx-option-number {
        color: #b4beb9;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 18px;
        letter-spacing: .04em;
    }

    .rx-simple .pos-prescription-type-copy {
        display: grid;
        align-content: start;
    }

    .rx-simple .pos-prescription-type-copy > small {
        color: #87948e;
        font-size: 9px !important;
        font-weight: 750;
        letter-spacing: .1em;
    }

    .rx-simple .pos-prescription-type-copy > strong {
        margin-top: 5px;
        color: var(--rx-ink);
        font-size: 22px !important;
        font-weight: 700 !important;
        letter-spacing: -.035em;
    }

    .rx-simple .pos-prescription-type-copy > span:not(.pos-prescription-type-points) {
        max-width: 350px;
        margin-top: 7px;
        color: var(--rx-muted);
        font-size: 12px !important;
        line-height: 1.55;
    }

    .rx-simple .pos-prescription-type-points {
        display: grid;
        gap: 6px;
        margin-top: 15px;
    }

    .rx-simple .pos-prescription-type-points b {
        display: inline-flex;
        width: fit-content;
        align-items: center;
        gap: 6px;
        padding: 0;
        color: #516159;
        border: 0;
        border-radius: 0;
        background: transparent;
        font-size: 11px !important;
        font-weight: 550;
    }

    .rx-simple .pos-prescription-type-points i {
        color: var(--rx-primary);
        font-size: 14px;
    }

    .rx-simple .pos-prescription-type-option:nth-child(2) .pos-prescription-type-points i {
        color: var(--rx-compound);
    }

    .rx-simple .pos-rx-option-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 13px;
        color: var(--rx-primary);
        border-top: 1px solid var(--rx-line);
        font-size: 11px;
        font-weight: 700;
    }

    .rx-simple .pos-prescription-type-option:nth-child(2) .pos-rx-option-action {
        color: var(--rx-compound);
    }

    .rx-simple .pos-rx-option-action i {
        font-size: 17px;
        transition: transform .18s ease;
    }

    .rx-simple .pos-prescription-type-option:hover .pos-rx-option-action i {
        transform: translateX(4px);
    }

    .rx-simple .pos-prescription-type-check {
        top: 15px;
        right: 15px;
        color: transparent;
        font-size: 20px;
    }

    .rx-simple .pos-prescription-type-option.is-active {
        border-color: #8eb4a5;
        background: #fff;
        box-shadow: 0 14px 34px rgba(28, 74, 57, .11), inset 0 0 0 1px #8eb4a5;
    }

    .rx-simple .pos-prescription-type-option:nth-child(2).is-active {
        border-color: #a797c6;
        box-shadow: 0 14px 34px rgba(78, 59, 111, .1), inset 0 0 0 1px #a797c6;
    }

    .rx-simple .pos-prescription-type-option.is-active .pos-prescription-type-icon,
    .rx-simple .pos-prescription-type-option:nth-child(2).is-active .pos-prescription-type-icon {
        color: #fff;
        border-color: transparent;
        background: var(--rx-primary);
        box-shadow: 0 8px 18px rgba(24, 91, 73, .18);
    }

    .rx-simple .pos-prescription-type-option:nth-child(2).is-active .pos-prescription-type-icon {
        background: var(--rx-compound);
        box-shadow: 0 8px 18px rgba(101, 82, 141, .18);
    }

    .rx-simple .pos-prescription-type-option.is-active .pos-prescription-type-check {
        color: var(--rx-primary);
    }

    .rx-simple .pos-prescription-type-option:nth-child(2).is-active .pos-prescription-type-check {
        color: var(--rx-compound);
    }

    .rx-simple .pos-prescription-modal-help {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-top: 14px;
        padding: 10px 12px;
        color: #74643f;
        border: 1px solid #ebdfc5;
        border-radius: 11px;
        background: var(--rx-accent-soft);
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-modal-help > i {
        color: #b5832e;
        font-size: 18px;
    }

    .rx-simple .pos-prescription-modal-help strong {
        color: #574a2e;
    }

    .rx-simple .pos-prescription-type-step .modal-footer {
        min-height: 62px;
        align-items: center;
        justify-content: space-between;
        padding: 12px 28px;
        border-top: 1px solid var(--rx-line);
        background: var(--rx-surface-soft);
    }

    .rx-simple .pos-rx-chooser-assurance {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--rx-muted);
        font-size: 10px;
    }

    .rx-simple .pos-rx-chooser-assurance i {
        color: var(--rx-primary);
        font-size: 15px;
    }

    .rx-simple .pos-prescription-modal-cancel {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        color: #55635d;
        border: 1px solid var(--rx-line-strong);
        border-radius: 10px;
        background: #fff;
        font-size: 11px !important;
    }

    /* Main prescription workspace */
    .rx-simple .pos-prescription-cashier-step {
        height: 100dvh;
        grid-template-rows: 76px minmax(0, 1fr) 78px;
        color: var(--rx-ink);
        background:
            radial-gradient(circle at 3% 0, rgba(24, 91, 73, .05), transparent 25rem),
            var(--rx-bg);
    }

    .rx-simple .pos-prescription-cashier-header {
        display: grid;
        min-height: 76px;
        grid-template-columns: minmax(300px, .85fr) minmax(430px, 1.25fr) auto;
        gap: 20px;
        padding: 11px 18px;
        color: var(--rx-ink);
        border-bottom: 1px solid var(--rx-line);
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 5px 18px rgba(24, 43, 35, .045);
        backdrop-filter: blur(16px);
    }

    .rx-simple .pos-prescription-cashier-brand {
        gap: 12px;
    }

    .rx-simple .pos-prescription-cashier-brand small {
        color: var(--rx-primary);
        font-size: 9px !important;
        font-weight: 750 !important;
        letter-spacing: .11em;
    }

    .rx-simple .pos-prescription-cashier-brand strong {
        margin-top: 2px;
        color: var(--rx-ink);
        font-size: 17px !important;
        font-weight: 720 !important;
        letter-spacing: -.025em;
    }

    .rx-simple .pos-prescription-cashier-brand p {
        display: block;
        margin: 1px 0 0;
        color: var(--rx-muted);
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-flow-steps {
        display: flex;
        gap: 10px;
    }

    .rx-simple .pos-prescription-flow-steps span {
        display: inline-flex;
        gap: 7px;
        padding: 0;
        color: #8b9691;
        border: 0;
        border-radius: 0;
        background: transparent;
        font-size: 10px !important;
        font-style: normal;
        font-weight: 600 !important;
    }

    .rx-simple .pos-prescription-flow-steps span em {
        font-style: normal;
    }

    .rx-simple .pos-prescription-flow-steps span b {
        display: grid;
        width: 27px;
        height: 27px;
        place-items: center;
        color: #7d8983;
        border: 1px solid var(--rx-line-strong);
        border-radius: 9px;
        background: #fff;
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-flow-steps span.is-active {
        color: var(--rx-ink);
        background: transparent;
    }

    .rx-simple .pos-prescription-flow-steps span.is-active b {
        color: #fff;
        border-color: var(--rx-primary);
        background: var(--rx-primary);
        box-shadow: 0 5px 12px rgba(24, 91, 73, .16);
    }

    .rx-simple [data-prescription-mode="compound"] .pos-prescription-flow-steps span.is-active b,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-flow-steps span.is-active b {
        border-color: var(--rx-compound);
        background: var(--rx-compound);
        box-shadow: 0 5px 12px rgba(101, 82, 141, .16);
    }

    .rx-simple .pos-prescription-flow-steps > i {
        color: #c3cbc7;
        font-size: 16px;
    }

    .rx-simple .pos-rx-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .rx-simple .pos-rx-validation-state {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        color: #34715d;
        border: 1px solid #d1e4dc;
        border-radius: 999px;
        background: #f3faf7;
        font-size: 10px;
        font-weight: 650;
        white-space: nowrap;
    }

    .rx-simple .pos-rx-validation-state i {
        font-size: 15px;
    }

    .rx-simple .pos-prescription-flow-cancel {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        color: #7b5a5d;
        border: 1px solid #eadadd;
        border-radius: 10px;
        background: #fffafa;
        font-size: 10px !important;
    }

    .rx-simple .pos-prescription-flow-cancel:hover {
        color: #8f3e47;
        border-color: #dfc1c6;
        background: #fff3f4;
    }

    .rx-simple .pos-prescription-cashier-body {
        min-height: 0;
        overflow: hidden;
        padding: 12px;
    }

    .rx-simple .pos-prescription-cashier-layout {
        display: grid;
        height: 100%;
        min-height: 0;
        grid-template-areas:
            "catalog details"
            "catalog editor";
        grid-template-columns: minmax(292px, 326px) minmax(0, 1fr);
        grid-template-rows: auto minmax(300px, 1fr);
        gap: 10px;
    }

    .rx-simple .pos-prescription-modal-catalog,
    .rx-simple .pos-rx-work-panel {
        min-width: 0;
        overflow: auto;
        border: 1px solid var(--rx-line);
        border-radius: 15px;
        background: rgba(255, 255, 255, .98);
        box-shadow: var(--rx-shadow-sm);
        scrollbar-width: thin;
        scrollbar-color: #c9d3ce transparent;
    }

    .rx-simple .pos-prescription-modal-catalog { grid-area: catalog; }
    .rx-simple .pos-prescription-modal-details { grid-area: details; max-height: 390px; }
    .rx-simple .pos-prescription-modal-editor { grid-area: editor; padding: 0; }

    .rx-simple .pos-rx-panel-header {
        position: sticky;
        z-index: 7;
        top: 0;
        display: grid;
        min-height: 64px;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-bottom: 1px solid var(--rx-line);
        background: rgba(255, 255, 255, .97);
        backdrop-filter: blur(14px);
    }

    .rx-simple .pos-rx-panel-index {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        color: var(--rx-primary);
        border: 1px solid #d4e4dd;
        border-radius: 10px;
        background: var(--rx-primary-soft);
        font-size: 10px;
        font-weight: 750;
    }

    .rx-simple .pos-rx-panel-copy {
        display: grid;
        min-width: 0;
    }

    .rx-simple .pos-rx-panel-copy small {
        color: #8a9690;
        font-size: 8px !important;
        font-weight: 750 !important;
        letter-spacing: .09em;
    }

    .rx-simple .pos-rx-panel-copy strong {
        color: var(--rx-ink);
        font-size: 13px !important;
        font-weight: 700 !important;
        line-height: 1.3;
    }

    .rx-simple .pos-rx-panel-copy p {
        overflow: hidden;
        margin: 1px 0 0;
        color: var(--rx-muted);
        font-size: 9px;
        line-height: 1.3;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .rx-simple .pos-rx-panel-state,
    .rx-simple .pos-rx-panel-live {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 700;
        white-space: nowrap;
    }

    .rx-simple .pos-rx-panel-state {
        color: #956c2b;
        border: 1px solid #eadbbd;
        background: #fff9ed;
    }

    .rx-simple .pos-prescription-modal-details.is-complete .pos-rx-panel-state {
        color: #176b53;
        border-color: #c9e0d7;
        background: #f0f8f5;
    }

    .rx-simple .pos-rx-panel-live {
        color: #27715a;
        border: 1px solid #cee3da;
        background: #f1f9f6;
    }

    .rx-simple .pos-rx-panel-live i {
        color: #15916c;
        filter: drop-shadow(0 0 4px rgba(21, 145, 108, .28));
    }

    /* Catalog */
    .rx-simple .pos-prescription-cashier-step .pos-product-panel {
        background: #fff;
    }

    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero {
        position: sticky;
        z-index: 6;
        top: 0;
        min-height: 64px;
        padding: 10px 12px;
        color: var(--rx-ink);
        border-bottom: 1px solid var(--rx-line);
        background: rgba(255, 255, 255, .97);
        backdrop-filter: blur(14px);
    }

    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero-main {
        gap: 10px;
    }

    .rx-simple .pos-prescription-cashier-step .pos-catalog-step {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        color: var(--rx-primary);
        border: 1px solid #d4e4dd;
        border-radius: 10px;
        background: var(--rx-primary-soft);
        box-shadow: none;
        font-size: 10px;
    }

    .rx-simple .pos-prescription-cashier-step .pos-catalog-hero h2 {
        color: var(--rx-ink);
        font-size: 13px !important;
        letter-spacing: -.01em;
    }

    .rx-simple .pos-prescription-cashier-step .pos-search-box {
        padding: 12px;
        border-bottom: 1px solid #edf1ef;
        background: #fff;
    }

    .rx-simple .pos-prescription-cashier-step .pos-search-title {
        margin-bottom: 7px;
        color: var(--rx-ink-soft);
        font-size: 12px !important;
    }

    .rx-simple .pos-prescription-cashier-step .pos-search-title small {
        color: var(--rx-muted);
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-selection--single {
        height: 48px;
        min-height: 48px;
        border: 1px solid var(--rx-line-strong);
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 3px 10px rgba(24, 43, 35, .045);
    }

    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-selection--single:focus,
    .rx-simple .pos-prescription-cashier-step .pos-search-control .select2-container--open .select2-selection--single {
        border-color: #80a999;
        box-shadow: 0 0 0 3px rgba(24, 91, 73, .08);
    }

    .rx-simple .pos-prescription-cashier-step .pos-search-leading {
        color: var(--rx-primary);
        background: var(--rx-primary-soft);
    }

    .rx-simple .pos-prescription-cashier-step .pos-search-ready {
        color: #3f7664;
        border-color: #d4e5de;
        background: #f4faf7;
    }

    .rx-simple .pos-prescription-cashier-step .pos-catalog-block {
        border-color: var(--rx-line);
        background: #fff;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step .pos-product-card.has-product {
        border-color: #c9ddd5;
        background: #f7fbf9;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step .pos-add-button {
        min-height: 43px;
        border-radius: 10px;
        background: var(--rx-primary);
        box-shadow: 0 7px 16px rgba(24, 91, 73, .17);
    }

    .rx-simple .pos-prescription-cashier-step .pos-add-button:hover:not(:disabled) {
        background: var(--rx-primary-hover);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-catalog-step,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-search-leading {
        color: var(--rx-compound);
        border-color: #ded6eb;
        background: var(--rx-compound-soft);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-add-button {
        background: var(--rx-compound);
        box-shadow: 0 7px 16px rgba(101, 82, 141, .17);
    }

    /* Patient and prescription data panel */
    .rx-simple .pos-prescription-modal-details .pos-transaction-details {
        margin: 0;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-modal-details .pos-transaction-details > summary,
    .rx-simple .pos-prescription-modal-details .pos-transaction-form-intro,
    .rx-simple .pos-prescription-modal-details .pos-transaction-type-field,
    .rx-simple .pos-prescription-modal-details .pos-transaction-type-selector-field {
        display: none;
    }

    .rx-simple .pos-prescription-modal-details .pos-transaction-form-shell {
        padding: 12px;
        background: transparent;
    }

    /* The outer panel already carries the title and state; remove the nested duplicate to reclaim vertical space. */
    .rx-simple .pos-prescription-modal-details .pos-prescription-head {
        display: none;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-modal-details .pos-prescription-guidance {
        display: none;
    }

    .rx-simple .pos-prescription-modal-details .pos-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 9px;
        padding: 0 0 11px;
    }

    .rx-simple .pos-prescription-modal-details .pos-form-grid::before {
        grid-column: 1 / -1;
        margin-bottom: -2px;
        color: #8b9791;
        content: "DATA PASIEN";
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .1em;
    }

    .rx-simple .pos-prescription-cashier-step .pos-field label {
        margin-bottom: 5px;
        color: #53625b;
        font-size: 10px !important;
        font-weight: 650 !important;
    }

    .rx-simple .pos-prescription-cashier-step .pos-field label i {
        color: #819089;
    }

    .rx-simple .pos-prescription-cashier-step .form-control,
    .rx-simple .pos-prescription-cashier-step .form-select,
    .rx-simple .pos-prescription-cashier-step .input-group-text {
        min-height: 39px;
        color: #283832;
        border-color: var(--rx-line-strong);
        border-radius: 9px;
        background-color: #fff;
        font-size: 11px !important;
    }

    .rx-simple .pos-prescription-cashier-step .form-control::placeholder {
        color: #a5aea9;
    }

    .rx-simple .pos-prescription-cashier-step .form-control:focus,
    .rx-simple .pos-prescription-cashier-step .form-select:focus {
        border-color: #80a999;
        box-shadow: 0 0 0 3px rgba(24, 91, 73, .08);
    }

    .rx-simple .pos-prescription-workspace {
        margin: 0;
        overflow: hidden;
        border: 1px solid var(--rx-line);
        border-radius: 13px;
        background: #fff;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-head {
        min-height: 55px;
        padding: 9px 10px;
        color: var(--rx-ink);
        border-bottom: 1px solid var(--rx-line);
        background: var(--rx-surface-soft);
    }

    .rx-simple .pos-prescription-head-icon {
        width: 34px;
        height: 34px;
        color: var(--rx-primary);
        border: 1px solid #d3e3dc;
        border-radius: 10px;
        background: var(--rx-primary-soft);
        box-shadow: none;
        font-size: 17px;
    }

    .rx-simple .pos-prescription-head > div small {
        color: #87958e;
        font-size: 8px !important;
    }

    .rx-simple .pos-prescription-head > div strong {
        color: var(--rx-ink);
        font-size: 12px !important;
    }

    .rx-simple .pos-prescription-status {
        padding: 5px 7px;
        color: #926a2b;
        border-color: #ebdcbc;
        background: #fff9ee;
        font-size: 9px !important;
    }

    .rx-simple .pos-prescription-status.is-ready {
        color: #176b53;
        border-color: #c9e0d7;
        background: #f0f8f5;
    }

    .rx-simple .pos-prescription-mode-summary {
        margin: 8px 9px 0;
        padding: 7px 8px;
        border: 1px solid var(--rx-line);
        border-radius: 10px;
        background: #fff;
    }

    .rx-simple .pos-prescription-mode-icon {
        color: var(--rx-primary);
        background: var(--rx-primary-soft);
    }

    .rx-simple .pos-prescription-mode-summary.is-compound .pos-prescription-mode-icon {
        color: var(--rx-compound);
        background: var(--rx-compound-soft);
    }

    .rx-simple .pos-change-prescription-type {
        color: var(--rx-primary);
        border-color: #cbded6;
        background: #f5faf8;
    }

    .rx-simple .pos-prescription-form-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        padding: 9px;
    }

    .rx-simple .pos-prescription-guidance {
        padding: 8px 9px;
        border-top-color: var(--rx-line);
        background: var(--rx-surface-soft);
    }

    .rx-simple .pos-prescription-guidance > span:first-child {
        color: var(--rx-primary);
        background: var(--rx-primary-soft);
    }

    .rx-simple .pos-prescription-counter {
        color: var(--rx-primary);
        border-color: #d1e2db;
        background: #f2f8f5;
    }

    /* Compound controls */
    .rx-simple .pos-compound-setup {
        margin: 8px 9px 0;
        border-color: #ddd7e7;
        border-radius: 11px;
        box-shadow: none;
    }

    .rx-simple .pos-compound-steps {
        justify-content: flex-start;
        gap: 5px;
        padding: 7px 9px;
        color: #716785;
        border-bottom-color: #e6e1ed;
        background: #f8f5fb;
    }

    .rx-simple .pos-compound-steps b {
        background: var(--rx-compound);
    }

    .rx-simple .pos-compound-controls {
        grid-template-columns: minmax(0, 1.25fr) minmax(230px, .75fr);
        gap: 8px;
        padding: 9px;
    }

    .rx-simple .pos-new-compound-group {
        color: var(--rx-compound);
        border-color: #d7cde6;
        background: #f6f2fa;
    }

    .rx-simple .pos-new-compound-group:hover {
        border-color: var(--rx-compound);
        background: var(--rx-compound);
    }

    .rx-simple .pos-compound-summary-chip.is-active {
        border-color: var(--rx-compound);
        background: var(--rx-compound);
        box-shadow: 0 4px 10px rgba(101, 82, 141, .15);
    }

    /* Medicine and label panel */
    .rx-simple .pos-prescription-modal-editor .pos-cart-toolbar {
        margin: 9px 10px 7px;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-title-icon {
        color: var(--rx-primary);
        border-color: #d4e4dd;
        background: var(--rx-primary-soft);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-modal-editor .pos-cart-title-icon {
        color: var(--rx-compound);
        border-color: #dfd8e9;
        background: var(--rx-compound-soft);
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-wrap {
        min-height: 220px;
        margin: 0 10px 9px;
        overflow-x: hidden;
        overflow-y: auto;
        border: 1px solid var(--rx-line);
        border-radius: 11px;
        background: #f5f7f6;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table {
        display: block;
        width: 100%;
        min-width: 0 !important;
        border-spacing: 0 5px;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table thead {
        display: none;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table tbody {
        display: grid;
        width: 100%;
        gap: 8px;
        padding: 8px;
    }

    /* Product rows become readable cards instead of a 1080px-wide table. */
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row {
        position: relative;
        display: grid;
        width: 100%;
        min-width: 0;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
        padding: 8px;
        border: 1px solid var(--rx-line-strong);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(24, 43, 35, .045);
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td {
        display: block;
        width: auto !important;
        min-width: 0;
        padding: 9px !important;
        border: 1px solid #e7ece9 !important;
        border-radius: 9px;
        background: var(--rx-surface-soft) !important;
        box-shadow: none !important;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td::before {
        display: block;
        margin-bottom: 6px;
        color: #84918a;
        content: attr(data-label);
        font-size: 8px;
        font-weight: 750;
        letter-spacing: .075em;
        line-height: 1.3;
        text-transform: uppercase;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:first-child {
        grid-column: 1 / -1;
        padding: 8px 48px 8px 8px !important;
        border-color: #dce5e1 !important;
        background: #fff !important;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:first-child::before,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:last-child::before {
        display: none;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6) {
        display: flex;
        grid-column: 1 / -1;
        align-items: center;
        gap: 7px;
        padding: 10px 11px !important;
        border-color: #d5e4de !important;
        background: #f3f9f6 !important;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6)::before {
        margin: 0 auto 0 0;
        color: #527164;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6) .pos-money {
        color: var(--rx-primary);
        font-size: 14px !important;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:nth-child(6) .pos-money-note {
        margin: 0;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row > td:last-child {
        position: absolute;
        z-index: 2;
        top: 14px;
        right: 14px;
        width: auto;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-discount-editor {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-qty-stepper,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-discount-control,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .cart-unit-select {
        width: 100%;
        min-width: 0;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-fefo-stack,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-fefo-allocation {
        width: 100%;
        min-width: 0;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-fefo-allocation > span,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-fefo-allocation b,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row .pos-fefo-allocation small {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-compound-group-row,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-prescription-detail-row,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-cart-empty-row {
        display: block;
        width: 100%;
        min-width: 0;
    }

    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-compound-group-row > td,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-prescription-detail-row > td,
    .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-cart-empty-row > td {
        display: block;
        width: 100%;
        min-width: 0;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields > label:nth-child(n) {
        grid-column: auto;
    }

    .rx-simple .pos-prescription-modal-editor .pos-prescription-item-grid.is-compound-component {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rx-simple .pos-prescription-item-editor,
    .rx-simple .pos-compound-group-card {
        border-color: var(--rx-line-strong);
        border-radius: 12px;
        box-shadow: 0 5px 14px rgba(24, 43, 35, .045);
    }

    .rx-simple .pos-prescription-item-editor {
        border-left-color: var(--rx-primary);
        background: #fbfdfc;
    }

    .rx-simple .pos-compound-group-card {
        border-color: #d8d1e3;
        border-top-color: var(--rx-compound);
    }

    .rx-simple .pos-compound-group-card.is-active {
        border-color: #b9aacd;
        border-top-color: var(--rx-compound);
    }

    .rx-simple .pos-compound-group-card.is-saved {
        border-color: #bdd9ce;
        border-top-color: var(--rx-primary);
    }

    .rx-simple .pos-compound-group-head {
        border-bottom-color: #e7e3eb;
        background: linear-gradient(90deg, #f6f2fa, #fff 72%);
    }

    .rx-simple .pos-compound-group-card.is-saved .pos-compound-group-head {
        background: linear-gradient(90deg, #eff8f4, #fff 72%);
    }

    .rx-simple .pos-save-compound-group {
        background: var(--rx-compound);
        box-shadow: 0 5px 12px rgba(101, 82, 141, .17);
    }

    .rx-simple .pos-save-compound-group.is-edit {
        color: var(--rx-primary);
        border-color: #bfd9cf;
        background: #eff8f4;
    }

    .rx-simple .pos-prescription-cashier-step .pos-prescription-toolbar-pay {
        color: var(--rx-primary);
        border: 1px solid #c8ded5;
        background: #f3f9f6;
        box-shadow: none;
    }

    .rx-simple .pos-prescription-cashier-step .pos-prescription-toolbar-pay:hover,
    .rx-simple .pos-prescription-cashier-step .pos-prescription-toolbar-pay.is-ready {
        color: #fff;
        border-color: var(--rx-primary);
        background: var(--rx-primary);
        box-shadow: 0 6px 14px rgba(24, 91, 73, .17);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-toolbar-pay {
        color: var(--rx-compound);
        border-color: #d5cbe3;
        background: #f6f3f9;
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-toolbar-pay:hover,
    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-prescription-toolbar-pay.is-ready {
        color: #fff;
        border-color: var(--rx-compound);
        background: var(--rx-compound);
        box-shadow: 0 6px 14px rgba(101, 82, 141, .17);
    }

    /* Persistent checkout footer */
    body.pos-fullscreen-mode .rx-simple footer.pos-prescription-cashier-footer {
        display: grid !important;
        min-height: 78px;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 16px;
        padding: 9px 14px;
        border-top: 1px solid var(--rx-line);
        background: rgba(255, 255, 255, .97);
        box-shadow: 0 -7px 20px rgba(24, 43, 35, .055);
        backdrop-filter: blur(16px);
    }

    .rx-simple .pos-rx-footer-status {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
    }

    .rx-simple .pos-prescription-flow-readiness {
        min-width: 240px;
        flex: 1;
        gap: 9px;
    }

    .rx-simple .pos-prescription-flow-readiness > span {
        width: 39px;
        height: 39px;
        color: #a27731;
        border: 1px solid #eddfc3;
        border-radius: 11px;
        background: #fff9ed;
    }

    .rx-simple .pos-prescription-flow-readiness small {
        color: #89958f;
        font-size: 8px !important;
        letter-spacing: .09em;
    }

    .rx-simple .pos-prescription-flow-readiness strong {
        color: var(--rx-ink-soft);
        font-size: 11px !important;
    }

    .rx-simple .pos-prescription-flow-readiness.is-ready > span {
        color: var(--rx-primary);
        border-color: #c9e0d7;
        background: #eff8f4;
    }

    .rx-simple .pos-prescription-flow-readiness.is-ready strong {
        color: var(--rx-primary);
    }

    .rx-simple .pos-prescription-flow-metrics {
        display: flex;
        gap: 7px;
    }

    .rx-simple .pos-prescription-flow-metrics > span {
        min-width: 112px;
        padding: 7px 10px;
        border-color: var(--rx-line);
        border-radius: 10px;
        background: var(--rx-surface-soft);
    }

    .rx-simple .pos-prescription-flow-metrics > span:last-child {
        min-width: 154px;
    }

    .rx-simple .pos-prescription-flow-metrics small {
        color: #89958f;
        font-size: 8px !important;
    }

    .rx-simple .pos-prescription-flow-metrics strong {
        color: var(--rx-ink);
        font-size: 12px !important;
    }

    .rx-simple .pos-prescription-flow-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rx-simple .pos-prescription-draft-button {
        min-height: 44px;
        padding: 8px 12px;
        color: #596861;
        border: 1px solid var(--rx-line-strong);
        border-radius: 10px;
        background: #fff;
    }

    .rx-simple .pos-prescription-draft-button:hover {
        color: var(--rx-primary);
        border-color: #a9c6ba;
        background: var(--rx-primary-soft);
    }

    .rx-simple .pos-finish-prescription-button {
        min-width: 205px;
        min-height: 50px;
        padding: 9px 13px 9px 16px;
        border-radius: 11px;
        background: #6e7b75;
        box-shadow: 0 7px 16px rgba(58, 72, 65, .16);
    }

    .rx-simple .pos-finish-prescription-button.is-ready {
        background: var(--rx-primary);
        box-shadow: 0 9px 20px rgba(24, 91, 73, .22);
    }

    .rx-simple .pos-prescription-cashier-step[data-prescription-mode="compound"] .pos-finish-prescription-button.is-ready {
        background: var(--rx-compound);
        box-shadow: 0 9px 20px rgba(101, 82, 141, .22);
    }

    .rx-simple .pos-finish-prescription-button > span {
        font-size: 12px !important;
    }

    .rx-simple .pos-finish-prescription-button small {
        color: rgba(255, 255, 255, .72);
        font-size: 9px !important;
    }

    /* Compound preview uses the same quiet visual language. */
    .rx-simple .pos-compound-preview-step {
        color: var(--rx-ink);
        background: var(--rx-bg);
    }

    .rx-simple .pos-compound-preview-header {
        min-height: 78px;
        padding: 12px 20px;
        color: var(--rx-ink);
        border-bottom: 1px solid var(--rx-line);
        background: rgba(255, 255, 255, .97);
        box-shadow: 0 5px 18px rgba(24, 43, 35, .045);
    }

    .rx-simple .pos-compound-preview-brand > span {
        color: var(--rx-compound);
        border: 1px solid #ddd5e8;
        background: var(--rx-compound-soft);
    }

    .rx-simple .pos-compound-preview-brand small {
        color: var(--rx-compound);
    }

    .rx-simple .pos-compound-preview-brand strong {
        color: var(--rx-ink);
    }

    .rx-simple .pos-compound-preview-brand p {
        color: var(--rx-muted);
    }

    .rx-simple .pos-compound-preview-status {
        color: #376958;
        border-color: #d1e3dc;
        background: #f2f9f6;
    }

    .rx-simple .pos-compound-preview-status small {
        color: #688078;
    }

    .rx-simple .pos-compound-preview-status strong {
        color: var(--rx-primary);
    }

    .rx-simple .pos-compound-preview-pay {
        border: 0;
        background: var(--rx-primary);
        box-shadow: 0 8px 18px rgba(24, 91, 73, .2);
    }

    .rx-simple .pos-compound-preview-pay:hover {
        background: var(--rx-primary-hover);
    }

    .rx-simple .pos-compound-preview-body {
        background:
            radial-gradient(circle at 5% 0, rgba(101, 82, 141, .045), transparent 24rem),
            var(--rx-bg);
    }

    .rx-simple .pos-compound-preview-summary,
    .rx-simple .pos-compound-preview-group {
        border-color: var(--rx-line);
        box-shadow: var(--rx-shadow-sm);
    }

    .rx-simple .pos-compound-preview-number {
        background: var(--rx-compound);
    }

    .rx-simple .pos-compound-preview-group-head {
        border-bottom-color: #e5e1e9;
        background: linear-gradient(90deg, var(--rx-compound-soft), #fff 72%);
    }

    .rx-simple .pos-compound-label-preview-head {
        background: linear-gradient(115deg, #24352f, var(--rx-primary));
    }

    body.pos-fullscreen-mode .rx-simple footer.pos-compound-preview-footer {
        display: flex !important;
        border-top-color: var(--rx-line);
        box-shadow: 0 -7px 20px rgba(24, 43, 35, .055);
    }

    /* Full-height working cards on common cashier desktop displays. */
    @media (min-width: 1181px) {
        .rx-simple .pos-prescription-cashier-layout {
            grid-template-areas: "catalog details editor";
            grid-template-columns: minmax(250px, 280px) minmax(430px, 480px) minmax(0, 1fr);
            grid-template-rows: minmax(0, 1fr);
        }

        .rx-simple .pos-prescription-modal-details {
            max-height: none;
        }

        .rx-simple .pos-prescription-modal-details .pos-form-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .rx-simple .pos-prescription-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1280px) {
        .rx-simple .pos-prescription-cashier-header {
            grid-template-columns: minmax(280px, 1fr) auto;
        }

        .rx-simple .pos-prescription-flow-steps {
            display: none;
        }

        .rx-simple .pos-prescription-flow-metrics > span:first-child {
            display: none;
        }
    }

    @media (max-width: 1020px) {
        .rx-simple .pos-prescription-cashier-step {
            height: 100dvh;
            grid-template-rows: auto minmax(0, 1fr) auto;
        }

        .rx-simple .pos-prescription-cashier-body {
            overflow: auto;
        }

        .rx-simple .pos-prescription-cashier-layout {
            height: auto;
            grid-template-areas:
                "details"
                "catalog"
                "editor";
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto auto auto;
        }

        .rx-simple .pos-prescription-modal-catalog,
        .rx-simple .pos-rx-work-panel {
            overflow: visible;
        }

        .rx-simple .pos-prescription-modal-details {
            max-height: none;
        }

        .rx-simple .pos-prescription-modal-catalog > .pos-catalog {
            width: 100%;
        }

        .rx-simple .pos-prescription-cashier-step .pos-catalog-hero,
        .rx-simple .pos-rx-panel-header {
            position: static;
        }

        .rx-simple .pos-rx-footer-status {
            gap: 8px;
        }

        .rx-simple .pos-prescription-flow-metrics {
            display: none;
        }
    }

    @media (max-width: 720px) {
        .rx-simple .pos-prescription-type-step {
            align-items: start;
            padding: 8px;
            overflow: auto;
        }

        .rx-simple .pos-prescription-type-step-shell {
            border-radius: 17px;
        }

        .rx-simple .pos-prescription-type-step .modal-header {
            padding: 16px;
        }

        .rx-simple .pos-prescription-type-step .modal-header::after {
            right: 16px;
            left: 16px;
        }

        .rx-simple .pos-prescription-type-step .modal-body {
            padding: 16px;
        }

        .rx-simple .pos-prescription-modal-heading p,
        .rx-simple .pos-rx-intro-badge,
        .rx-simple .pos-rx-chooser-assurance {
            display: none;
        }

        .rx-simple .pos-prescription-modal-intro {
            grid-template-columns: auto minmax(0, 1fr);
        }

        .rx-simple .pos-prescription-type-grid {
            grid-template-columns: 1fr;
        }

        .rx-simple .pos-prescription-type-option {
            min-height: 0;
            padding: 16px;
        }

        .rx-simple .pos-prescription-type-step .modal-footer {
            justify-content: flex-end;
            padding: 10px 16px;
        }

        .rx-simple .pos-prescription-cashier-header {
            min-height: 64px;
            grid-template-columns: minmax(0, 1fr) auto;
            padding: 8px 9px;
        }

        .rx-simple .pos-rx-brand-mark {
            width: 40px !important;
            height: 40px !important;
            border-radius: 12px !important;
        }

        .rx-simple .pos-prescription-cashier-brand p,
        .rx-simple .pos-rx-validation-state {
            display: none;
        }

        .rx-simple .pos-prescription-flow-cancel {
            width: 38px;
            height: 38px;
            overflow: hidden;
            justify-content: center;
            padding: 0;
            font-size: 0 !important;
        }

        .rx-simple .pos-prescription-flow-cancel i {
            font-size: 17px;
        }

        .rx-simple .pos-prescription-cashier-body {
            padding: 7px;
        }

        .rx-simple .pos-prescription-modal-details .pos-form-grid,
        .rx-simple .pos-prescription-form-grid,
        .rx-simple .pos-compound-controls,
        .rx-simple .pos-compound-group-fields,
        .rx-simple .pos-prescription-item-grid.is-compound-component {
            grid-template-columns: 1fr;
        }

        .rx-simple .pos-prescription-modal-editor .pos-cart-table .pos-product-row,
        .rx-simple .pos-prescription-modal-editor .pos-compound-group-fields,
        .rx-simple .pos-prescription-modal-editor .pos-prescription-item-grid.is-compound-component {
            grid-template-columns: 1fr;
        }

        .rx-simple .pos-rx-panel-state,
        .rx-simple .pos-rx-panel-copy p {
            display: none;
        }

        .rx-simple .pos-prescription-cashier-footer {
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 7px;
            padding: 7px 8px;
        }

        .rx-simple .pos-rx-footer-status {
            min-width: 0;
        }

        .rx-simple .pos-prescription-flow-readiness {
            min-width: 0;
        }

        .rx-simple .pos-prescription-flow-readiness small,
        .rx-simple .pos-prescription-draft-button {
            display: none;
        }

        .rx-simple .pos-prescription-flow-readiness strong {
            white-space: normal;
        }

        .rx-simple .pos-finish-prescription-button {
            min-width: 158px;
            padding: 8px 10px;
        }
    }

    @media (max-width: 480px) {
        .rx-simple .pos-prescription-modal-heading h2 {
            font-size: 18px;
        }

        .rx-simple .pos-prescription-type-copy > strong {
            font-size: 19px !important;
        }

        .rx-simple .pos-prescription-modal-help {
            align-items: flex-start;
        }

        .rx-simple .pos-prescription-cashier-brand small {
            display: none;
        }

        .rx-simple .pos-prescription-cashier-brand strong {
            font-size: 14px !important;
        }

        .rx-simple .pos-prescription-flow-readiness > span {
            width: 34px;
            height: 34px;
        }

        .rx-simple .pos-finish-prescription-button {
            min-width: 138px;
        }

        .rx-simple .pos-finish-prescription-button small {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rx-simple *,
        .rx-simple *::before,
        .rx-simple *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
        }
    }
</style>
