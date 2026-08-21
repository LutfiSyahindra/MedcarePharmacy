<style>
    .document-page {
        --document-ink: #17233c;
        --document-muted: #718096;
        --document-line: #e5eaf2;
        --document-soft: #f6f8fc;
        --document-primary: #3157d5;
        --document-primary-dark: #203f9f;
        --document-regular: #0f766e;
        --document-narcotic: #cf405b;
        --document-psychotropic: #5267dc;
        --document-precursor: #bd7518;
        --document-oot: #198754;
        max-width: 1680px;
        margin: 0 auto;
        color: var(--document-ink);
    }

    .document-page button,
    .document-page a {
        transition: color .18s ease, background-color .18s ease, border-color .18s ease,
            box-shadow .18s ease, transform .18s ease, opacity .18s ease;
    }

    .document-page button:focus-visible,
    .document-page a:focus-visible,
    .document-page input:focus-visible,
    .document-page select:focus-visible {
        outline: 3px solid rgba(49, 87, 213, .18);
        outline-offset: 2px;
    }

    .document-command {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto 160px;
        align-items: center;
        gap: 28px;
        min-height: 198px;
        overflow: hidden;
        padding: 30px 32px;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 22px;
        color: #fff;
        background:
            radial-gradient(circle at 82% -20%, rgba(118, 176, 255, .46), transparent 30%),
            radial-gradient(circle at 50% 150%, rgba(75, 120, 235, .42), transparent 42%),
            linear-gradient(126deg, #15285f 0%, #2349b5 57%, #356ce2 100%);
        box-shadow: 0 18px 46px rgba(31, 64, 157, .2);
    }

    .document-command::before {
        position: absolute;
        inset: 0;
        content: "";
        opacity: .22;
        background-image:
            linear-gradient(rgba(255, 255, 255, .12) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, .12) 1px, transparent 1px);
        background-size: 34px 34px;
        mask-image: linear-gradient(90deg, transparent 30%, #000 100%);
        pointer-events: none;
    }

    .document-command-copy,
    .document-command-actions,
    .document-command-art {
        position: relative;
        z-index: 1;
    }

    .document-command-copy { max-width: 780px; }

    .document-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 10px;
        color: #cbd9ff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .document-kicker i { font-size: 17px; }

    .document-command h2 {
        margin: 0 0 9px;
        color: #fff;
        font-size: clamp(25px, 2.6vw, 36px);
        font-weight: 850;
        letter-spacing: -.035em;
        line-height: 1.16;
    }

    .document-command-copy > p {
        max-width: 720px;
        margin: 0;
        color: rgba(238, 244, 255, .78);
        font-size: 13px;
        line-height: 1.65;
    }

    .document-command-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 9px;
        margin-top: 17px;
    }

    .document-command-meta > span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 29px;
        padding: 5px 10px;
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 999px;
        color: rgba(255, 255, 255, .82);
        background: rgba(255, 255, 255, .08);
        font-size: 10px;
        font-weight: 650;
        backdrop-filter: blur(8px);
    }

    .document-live-status > i {
        width: 7px;
        height: 7px;
        border: 2px solid rgba(255, 255, 255, .4);
        border-radius: 50%;
        background: #5de1b2;
        box-shadow: 0 0 0 3px rgba(93, 225, 178, .13);
    }

    .document-command-actions {
        display: grid;
        gap: 9px;
        min-width: 168px;
    }

    .document-command-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 41px;
        padding: 9px 15px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .document-button-primary {
        border: 1px solid #fff;
        color: #2447ac;
        background: #fff;
        box-shadow: 0 8px 20px rgba(8, 28, 79, .18);
    }

    .document-button-primary:hover {
        color: #173991;
        background: #f3f6ff;
        transform: translateY(-1px);
    }

    .document-button-secondary {
        border: 1px solid rgba(255, 255, 255, .28);
        color: #fff;
        background: rgba(255, 255, 255, .09);
        backdrop-filter: blur(8px);
    }

    .document-button-secondary:hover {
        color: #fff;
        border-color: rgba(255, 255, 255, .48);
        background: rgba(255, 255, 255, .16);
    }

    .document-command-art {
        height: 128px;
    }

    .document-command-art span {
        position: absolute;
        top: 50%;
        left: 50%;
        display: grid;
        place-items: center;
        width: 84px;
        height: 108px;
        border: 1px solid rgba(255, 255, 255, .42);
        border-radius: 12px;
        color: #3157d5;
        background: rgba(255, 255, 255, .95);
        box-shadow: 0 16px 30px rgba(8, 27, 77, .2);
        font-size: 29px;
    }

    .document-command-art span::after {
        position: absolute;
        right: 13px;
        bottom: 17px;
        left: 13px;
        height: 18px;
        content: "";
        border-top: 3px solid #dfe6f5;
        border-bottom: 3px solid #dfe6f5;
    }

    .document-command-art .is-back { transform: translate(-75%, -50%) rotate(-13deg); opacity: .64; }
    .document-command-art .is-middle { transform: translate(-44%, -51%) rotate(4deg); color: var(--document-regular); opacity: .82; }
    .document-command-art .is-front { transform: translate(-18%, -48%) rotate(12deg); }

    .document-overview {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        gap: 12px;
        margin-top: 17px;
        padding: 12px;
        border: 1px solid var(--document-line);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(31, 45, 77, .06);
    }

    .document-total-card,
    .document-type-metric {
        min-width: 0;
        border: 1px solid transparent;
        text-align: left;
    }

    .document-total-card {
        display: flex;
        align-items: center;
        min-height: 88px;
        padding: 14px 16px;
        border-radius: 13px;
        color: #fff;
        background: linear-gradient(135deg, #203f9f, #3157d5);
        box-shadow: 0 9px 22px rgba(49, 87, 213, .18);
    }

    .document-total-card:hover { transform: translateY(-1px); }

    .document-total-icon {
        display: grid;
        place-items: center;
        flex: 0 0 42px;
        height: 42px;
        margin-right: 12px;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 11px;
        background: rgba(255, 255, 255, .1);
        font-size: 22px;
    }

    .document-total-copy {
        display: grid;
        min-width: 0;
        gap: 1px;
    }

    .document-total-copy > small:first-child {
        color: rgba(255, 255, 255, .65);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .055em;
        text-transform: uppercase;
    }

    .document-total-copy > span { font-size: 11px; font-weight: 650; }
    .document-total-copy > span strong { margin-right: 3px; font-size: 25px; font-weight: 900; line-height: 1; }
    .document-total-copy > small:last-child { overflow: hidden; color: rgba(255, 255, 255, .68); font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
    .document-total-copy b { color: #fff; }
    .document-total-arrow { margin-left: auto; color: rgba(255, 255, 255, .58); font-size: 20px; }

    .document-overview-types {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        gap: 8px;
    }

    .document-type-metric {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 88px;
        padding: 12px;
        overflow: hidden;
        border-color: #edf0f5;
        border-radius: 12px;
        color: #56637a;
        background: #fafbfe;
    }

    .document-type-metric::before {
        position: absolute;
        top: 13px;
        bottom: 13px;
        left: 0;
        width: 3px;
        content: "";
        border-radius: 0 3px 3px 0;
        background: var(--metric-tone, #3157d5);
        opacity: .8;
    }

    .document-type-metric:hover,
    .document-type-metric.is-active {
        border-color: color-mix(in srgb, var(--metric-tone, #3157d5) 28%, #e3e8f2);
        background: color-mix(in srgb, var(--metric-tone, #3157d5) 5%, #fff);
        box-shadow: 0 8px 18px rgba(31, 45, 77, .07);
        transform: translateY(-1px);
    }

    .document-type-metric.is-regular { --metric-tone: var(--document-regular); }
    .document-type-metric.is-narcotic { --metric-tone: var(--document-narcotic); }
    .document-type-metric.is-psychotropic { --metric-tone: var(--document-psychotropic); }
    .document-type-metric.is-precursor { --metric-tone: var(--document-precursor); }
    .document-type-metric.is-oot { --metric-tone: var(--document-oot); }

    .document-type-metric-icon {
        display: grid;
        place-items: center;
        flex: 0 0 36px;
        height: 36px;
        margin-right: 9px;
        border-radius: 10px;
        color: var(--metric-tone, #3157d5);
        background: color-mix(in srgb, var(--metric-tone, #3157d5) 10%, #fff);
        font-size: 19px;
    }

    .document-type-metric > span:nth-child(2) { display: grid; min-width: 0; gap: 2px; }
    .document-type-metric small { overflow: hidden; color: #7d899d; font-size: 10px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .document-type-metric strong { color: #27354e; font-size: 20px; font-weight: 900; line-height: 1; }
    .document-type-metric > i:last-child { margin-left: auto; color: #c0c8d5; font-size: 18px; }

    .document-archive {
        overflow: hidden;
        margin-top: 17px;
        border: 1px solid var(--document-line);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 12px 34px rgba(31, 45, 77, .07);
        scroll-margin-top: 150px;
    }

    .document-archive-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf0f5;
    }

    .document-heading-copy { display: flex; align-items: center; gap: 12px; min-width: 0; }

    .document-heading-icon {
        display: grid;
        place-items: center;
        flex: 0 0 43px;
        height: 43px;
        border-radius: 12px;
        color: #3157d5;
        background: #edf1ff;
        font-size: 22px;
    }

    .document-eyebrow {
        display: block;
        margin-bottom: 2px;
        color: #8793a6;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .075em;
        text-transform: uppercase;
    }

    .document-archive-heading h5,
    .document-template-heading h5 { margin: 0 0 3px; color: #24314a; font-size: 17px; font-weight: 850; }

    .document-archive-heading p,
    .document-template-heading p { margin: 0; color: #8490a3; font-size: 11px; line-height: 1.45; }

    .document-archive-state { display: flex; align-items: center; gap: 9px; }

    .document-result-label,
    .document-last-sync {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 31px;
        padding: 5px 9px;
        border: 1px solid #e4e8f0;
        border-radius: 999px;
        color: #778398;
        background: #fafbfc;
        font-size: 9px;
        white-space: nowrap;
    }

    .document-result-label b { color: #3a4963; }
    .document-last-sync i { color: #318c73; font-size: 15px; }

    .document-refresh-button {
        display: grid;
        place-items: center;
        flex: 0 0 37px;
        width: 37px;
        height: 37px;
        border: 1px solid #dfe4ed;
        border-radius: 10px;
        color: #5d6a7d;
        background: #fff;
        font-size: 19px;
    }

    .document-refresh-button:hover { color: #3157d5; border-color: #bdc9ed; background: #f7f9ff; }

    .document-toolbar {
        display: flex;
        align-items: end;
        gap: 10px;
        padding: 15px 22px;
        background: #fbfcfe;
    }

    .document-search {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1 1 520px;
        min-width: 260px;
    }

    .document-search > i {
        position: absolute;
        left: 13px;
        z-index: 1;
        color: #8f9bad;
        font-size: 20px;
        pointer-events: none;
    }

    .document-search input {
        width: 100%;
        height: 42px;
        padding: 8px 62px 8px 40px;
        border: 1px solid #dfe4ed;
        border-radius: 10px;
        outline: 0;
        color: #26334a;
        background: #fff;
        font-size: 12px;
    }

    .document-search input::placeholder { color: #9aa5b6; }
    .document-search input:focus { border-color: #91a5e6; box-shadow: 0 0 0 3px rgba(49, 87, 213, .09); }

    .document-search-shortcut {
        position: absolute;
        right: 11px;
        display: grid;
        place-items: center;
        width: 22px;
        height: 22px;
        border: 1px solid #e1e5ec;
        border-radius: 6px;
        color: #98a2b2;
        background: #f8f9fb;
        font-size: 10px;
    }

    .document-search button {
        position: absolute;
        right: 7px;
        display: none;
        place-items: center;
        width: 28px;
        height: 28px;
        border: 0;
        border-radius: 7px;
        color: #7f8b9e;
        background: #f0f3f8;
    }

    .document-search.has-value button { display: grid; }
    .document-search.has-value .document-search-shortcut { display: none; }

    .document-filter-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 42px;
        padding: 8px 12px;
        border: 1px solid #dfe4ed;
        border-radius: 10px;
        color: #536178;
        background: #fff;
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
    }

    .document-filter-toggle:hover,
    .document-filter-toggle.is-active { color: #3157d5; border-color: #b9c7ef; background: #f4f7ff; }

    .document-filter-toggle > span {
        display: grid;
        place-items: center;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        border-radius: 99px;
        color: #fff;
        background: #3157d5;
        font-size: 9px;
    }

    .document-filter-chevron { margin-left: 1px; transition: transform .18s ease; }
    .document-filter-toggle[aria-expanded="true"] .document-filter-chevron { transform: rotate(180deg); }

    .document-field { display: grid; flex: 0 0 auto; gap: 5px; }
    .document-field label { margin: 0; color: #788498; font-size: 9px; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; }
    .document-field .form-select,
    .document-field .form-control { min-height: 40px; border-color: #dfe4ed; border-radius: 9px; color: #46536a; background-color: #fff; font-size: 11px; }
    .document-page-size .form-select { width: 102px; }

    .document-advanced-filters {
        display: grid;
        grid-template-columns: minmax(210px, 310px) minmax(145px, 190px) auto minmax(145px, 190px) auto;
        align-items: end;
        justify-content: start;
        gap: 10px;
        max-height: 0;
        padding: 0 22px;
        overflow: hidden;
        border-top: 1px solid transparent;
        background: #fbfcfe;
        opacity: 0;
        transition: max-height .24s ease, padding .24s ease, opacity .18s ease, border-color .18s ease;
    }

    .document-advanced-filters.is-open {
        max-height: 110px;
        padding-top: 14px;
        padding-bottom: 14px;
        border-top-color: #edf0f5;
        opacity: 1;
    }

    .document-date-divider { padding-bottom: 11px; color: #9ba5b4; font-size: 9px; }

    .document-reset-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 40px;
        padding: 8px 12px;
        border: 1px solid #e0e5ed;
        border-radius: 9px;
        color: #6e7a8e;
        background: #fff;
        font-size: 10px;
        font-weight: 750;
    }

    .document-reset-button:hover { color: #b33d52; border-color: #efcbd2; background: #fff7f8; }

    .document-type-tabs {
        display: flex;
        align-items: center;
        gap: 7px;
        overflow-x: auto;
        padding: 0 22px 14px;
        border-bottom: 1px solid #edf0f5;
        background: #fbfcfe;
        scrollbar-width: thin;
    }

    .document-type-tabs button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
        min-height: 32px;
        padding: 6px 9px 6px 11px;
        border: 1px solid #e0e5ee;
        border-radius: 999px;
        color: #647187;
        background: #fff;
        font-size: 10px;
        font-weight: 750;
    }

    .document-type-tabs button:hover,
    .document-type-tabs button.is-active { color: #3157d5; border-color: #b9c7ef; background: #eff3ff; }

    .document-type-tabs button > span:last-child:not(.type-dot) {
        display: grid;
        place-items: center;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        border-radius: 99px;
        color: #6e7c94;
        background: #f0f2f6;
        font-size: 8px;
    }

    .document-type-tabs button.is-active > span:last-child:not(.type-dot) { color: #3157d5; background: #dfe7ff; }
    .type-dot { width: 7px; height: 7px; border-radius: 50%; background: #8a96aa; }
    .type-dot.is-regular { background: var(--document-regular); }
    .type-dot.is-narcotic { background: var(--document-narcotic); }
    .type-dot.is-psychotropic { background: var(--document-psychotropic); }
    .type-dot.is-precursor { background: var(--document-precursor); }
    .type-dot.is-oot { background: var(--document-oot); }

    .document-active-filters {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 22px;
        border-bottom: 1px solid #edf0f5;
        color: #7a879a;
        background: #f9faff;
        font-size: 9px;
    }

    .document-active-filters > span { font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .document-active-filters > div { display: flex; flex: 1; flex-wrap: wrap; gap: 5px; }

    .document-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 7px;
        border: 1px solid #dbe3f5;
        border-radius: 99px;
        color: #4e6084;
        background: #fff;
        font-size: 9px;
    }

    .document-filter-chip:hover { color: #bd3e55; border-color: #efcbd2; }

    #resetDocumentFilters {
        flex: 0 0 auto;
        border: 0;
        color: #3157d5;
        background: transparent;
        font-size: 9px;
        font-weight: 800;
    }

    .document-archive-alert {
        align-items: center;
        gap: 8px;
        margin: 12px 22px 0;
        padding: 10px 12px;
        border: 1px solid #f0c9d0;
        border-radius: 9px;
        color: #a93d51;
        background: #fff5f7;
        font-size: 10px;
    }

    .document-archive-alert:not(.d-none) { display: flex; }
    .document-archive-alert i { font-size: 17px; }

    .document-table-wrap { min-height: 330px; }
    .document-table { width: 100% !important; margin: 0 !important; }

    .document-table thead th {
        padding: 12px 14px;
        border: 0;
        border-bottom: 1px solid #e7ebf1 !important;
        color: #8b96a8;
        background: #fff;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .055em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .document-table tbody td {
        padding: 13px 14px;
        border-color: #edf0f4;
        color: #536075;
        background: #fff;
        font-size: 11px;
        vertical-align: middle;
    }

    .document-table tbody tr { transition: box-shadow .18s ease, transform .18s ease; }
    .document-table tbody tr:hover td { background: #fafbfe; }

    .document-cell { display: flex; align-items: center; gap: 10px; min-width: 218px; }

    .document-cell-icon {
        display: grid;
        place-items: center;
        flex: 0 0 36px;
        height: 42px;
        border-radius: 9px;
        color: var(--cell-tone, #3157d5);
        background: color-mix(in srgb, var(--cell-tone, #3157d5) 9%, #fff);
        font-size: 19px;
    }

    .document-cell-icon.is-regular { --cell-tone: var(--document-regular); }
    .document-cell-icon.is-narcotic { --cell-tone: var(--document-narcotic); }
    .document-cell-icon.is-psychotropic { --cell-tone: var(--document-psychotropic); }
    .document-cell-icon.is-precursor { --cell-tone: var(--document-precursor); }
    .document-cell-icon.is-oot { --cell-tone: var(--document-oot); }

    .document-cell-copy { display: grid; min-width: 0; gap: 3px; }

    .document-number-link {
        max-width: 230px;
        overflow: hidden;
        padding: 0;
        border: 0;
        color: #23304a;
        background: transparent;
        font-size: 11px;
        font-weight: 850;
        text-align: left;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .document-number-link:hover { color: #3157d5; }
    .document-cell-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 5px; }

    .document-extra-count {
        display: inline-flex;
        padding: 2px 6px;
        border-radius: 99px;
        color: #3157d5;
        background: #edf1ff;
        font-size: 8px;
        font-weight: 800;
    }

    .document-type-badge,
    .document-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 7px;
        border-radius: 99px;
        font-size: 8px;
        font-weight: 800;
        white-space: nowrap;
    }

    .document-type-badge::before { width: 6px; height: 6px; content: ""; border-radius: 50%; }
    .document-type-badge.is-regular { color: #0b665f; background: #e9f8f6; }
    .document-type-badge.is-regular::before { background: var(--document-regular); }
    .document-type-badge.is-narcotic { color: #b8334d; background: #fff0f3; }
    .document-type-badge.is-narcotic::before { background: var(--document-narcotic); }
    .document-type-badge.is-psychotropic { color: #465ac6; background: #eef0ff; }
    .document-type-badge.is-psychotropic::before { background: var(--document-psychotropic); }
    .document-type-badge.is-precursor { color: #9e6115; background: #fff5e6; }
    .document-type-badge.is-precursor::before { background: var(--document-precursor); }
    .document-type-badge.is-oot { color: #137044; background: #eaf8f0; }
    .document-type-badge.is-oot::before { background: var(--document-oot); }

    .document-date-cell { display: grid; min-width: 94px; gap: 2px; }
    .document-date-cell strong { color: #344158; font-size: 10px; }
    .document-date-cell small { color: #929cad; font-size: 9px; }

    .document-reference { display: grid; min-width: 145px; gap: 3px; }
    .document-reference strong { color: #334057; font-size: 10px; }
    .document-reference small { max-width: 190px; overflow: hidden; color: #8a95a7; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }

    .document-branch-cell { display: inline-flex; align-items: center; gap: 6px; min-width: 110px; color: #526078; font-size: 10px; }
    .document-branch-cell i { color: #8794a8; font-size: 15px; }

    .document-content-count { display: grid; min-width: 105px; gap: 2px; }
    .document-content-count strong { color: #344158; font-size: 10px; }
    .document-content-count small { color: #909bad; font-size: 9px; }

    .document-status-badge { color: #657186; background: #f0f2f6; }
    .document-status-badge::before { width: 6px; height: 6px; content: ""; border-radius: 50%; background: #909bad; }
    .document-status-badge.is-ready { color: #087a5b; background: #e8f8f2; }
    .document-status-badge.is-ready::before { background: #19a67d; }
    .document-status-badge.is-waiting { color: #9b651a; background: #fff4df; }
    .document-status-badge.is-waiting::before { background: #d9962e; }
    .document-status-badge.is-rejected { color: #b53d52; background: #fff0f2; }
    .document-status-badge.is-rejected::before { background: #d65268; }

    .document-action-group { display: flex; align-items: center; gap: 5px; }

    .document-action-primary,
    .document-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 15px;
    }

    .document-action-primary {
        gap: 5px;
        min-height: 32px;
        padding: 6px 9px;
        border: 1px solid #c9d4f2;
        color: #3157d5;
        background: #f4f7ff;
        font-size: 9px;
        font-weight: 800;
    }

    .document-action-primary:hover { color: #fff; border-color: #3157d5; background: #3157d5; }

    .document-action-button {
        width: 32px;
        height: 32px;
        border: 1px solid #dfe4ed;
        color: #657187;
        background: #fff;
    }

    .document-action-button:hover { color: #3157d5; border-color: #b7c5ed; background: #f4f7ff; }
    .document-action-button.is-print { color: #fff; border-color: #3157d5; background: #3157d5; }
    .document-action-button.is-print:hover { background: #2749ba; transform: translateY(-1px); }

    .document-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 20px;
        border-top: 1px solid #edf0f4;
        background: #fff;
    }

    .document-table-footer .dataTables_info { padding: 0 !important; color: #8994a5; font-size: 10px; }
    .document-table-footer .dataTables_paginate { padding: 0 !important; }
    .document-table-footer .page-link { min-width: 30px; border-color: #e0e5ed; color: #637087; font-size: 10px; text-align: center; }
    .document-table-footer .page-item.active .page-link { border-color: #3157d5; background: #3157d5; }

    .document-table .dataTables_empty { height: 300px; padding: 30px 16px !important; color: #8e99aa; }

    .document-empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 5px;
        min-height: 190px;
        color: #909bad;
    }

    .document-empty-state i { display: grid; place-items: center; width: 52px; height: 52px; margin-bottom: 5px; border-radius: 14px; color: #6e82bd; background: #eef2fd; font-size: 27px; }
    .document-empty-state strong { color: #536178; font-size: 12px; }
    .document-empty-state span { font-size: 10px; }

    div.dataTables_wrapper div.dataTables_processing {
        top: 115px;
        width: auto;
        min-width: 180px;
        margin: 0;
        padding: 11px 16px;
        border: 1px solid #dbe2f1;
        border-radius: 10px;
        color: #536178;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 10px 28px rgba(31, 45, 77, .13);
        transform: translateX(-50%);
        font-size: 10px;
    }

    .document-template-library {
        margin-top: 17px;
        overflow: hidden;
        border: 1px solid var(--document-line);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(31, 45, 77, .06);
        scroll-margin-top: 150px;
    }

    .document-template-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px 22px;
        cursor: default;
    }

    .document-template-title { display: flex; align-items: center; gap: 12px; min-width: 0; }

    .document-template-title > span {
        display: grid;
        place-items: center;
        flex: 0 0 43px;
        height: 43px;
        border-radius: 12px;
        color: #3157d5;
        background: #edf1ff;
        font-size: 22px;
    }

    .document-template-controls { display: flex; align-items: center; gap: 9px; }

    .document-template-count {
        padding: 5px 9px;
        border: 1px solid #e1e6ee;
        border-radius: 99px;
        color: #7a879a;
        background: #fafbfc;
        font-size: 9px;
        font-weight: 750;
        white-space: nowrap;
    }

    #toggleDocumentTemplates {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 36px;
        padding: 7px 11px;
        border: 1px solid #d9e0ed;
        border-radius: 9px;
        color: #536178;
        background: #fff;
        font-size: 10px;
        font-weight: 800;
    }

    #toggleDocumentTemplates:hover { color: #3157d5; border-color: #b9c7ef; background: #f5f7ff; }
    #toggleDocumentTemplates i { transition: transform .22s ease; }
    #toggleDocumentTemplates[aria-expanded="true"] i { transform: rotate(180deg); }

    .document-template-content {
        max-height: 900px;
        padding: 18px 22px 22px;
        overflow: hidden;
        border-top: 1px solid #edf0f5;
        opacity: 1;
        transition: max-height .3s ease, padding .3s ease, opacity .2s ease, border-color .2s ease;
    }

    .document-template-library.is-collapsed .document-template-content {
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        border-top-color: transparent;
        opacity: 0;
    }

    .document-template-context {
        display: grid;
        grid-template-columns: minmax(230px, 330px) minmax(0, 1fr);
        align-items: end;
        gap: 12px;
        margin-bottom: 15px;
    }

    .document-template-branch { display: grid; gap: 5px; }
    .document-template-branch label { margin: 0; color: #7d899c; font-size: 9px; font-weight: 850; letter-spacing: .05em; text-transform: uppercase; }
    .document-template-branch .form-select { min-height: 40px; border-color: #dfe4ed; border-radius: 9px; color: #455269; font-size: 11px; }

    .document-template-profile {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 40px;
        padding: 8px 11px;
        border: 1px solid #dfe7f5;
        border-radius: 9px;
        color: #53627b;
        background: #f8faff;
    }

    .document-template-profile > i { font-size: 18px; }
    .document-template-profile div { display: grid; min-width: 0; gap: 1px; }
    .document-template-profile strong { color: #344158; font-size: 10px; }
    .document-template-profile span { overflow: hidden; color: #8390a4; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
    .document-template-profile.is-complete { border-color: #cfeadf; color: #0b8967; background: #f0fbf7; }
    .document-template-profile.is-warning { border-color: #f2ddb8; color: #ae6c13; background: #fff8ec; }

    .document-template-grid { display: grid; grid-template-columns: repeat(5, minmax(170px, 1fr)); gap: 10px; }

    .document-template-card {
        --template-tone: #3157d5;
        position: relative;
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 9px;
        min-height: 137px;
        padding: 13px;
        overflow: hidden;
        border: 1px solid #e3e8f0;
        border-radius: 12px;
        background: #fbfcfe;
    }

    .document-template-card::before {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        height: 3px;
        content: "";
        background: var(--template-tone);
        opacity: .7;
    }

    .document-template-card:hover { border-color: color-mix(in srgb, var(--template-tone) 25%, #dce2eb); box-shadow: 0 10px 22px rgba(31, 45, 77, .08); transform: translateY(-2px); }
    .document-template-card.is-regular { --template-tone: var(--document-regular); }
    .document-template-card.is-narcotic { --template-tone: var(--document-narcotic); }
    .document-template-card.is-psychotropic { --template-tone: var(--document-psychotropic); }
    .document-template-card.is-precursor { --template-tone: var(--document-precursor); }
    .document-template-card.is-oot { --template-tone: var(--document-oot); }

    .document-template-card-icon {
        display: grid;
        place-items: center;
        align-self: start;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        color: var(--template-tone);
        background: color-mix(in srgb, var(--template-tone) 9%, #fff);
        font-size: 19px;
    }

    .document-template-card-copy { min-width: 0; }
    .document-template-card-copy small { color: #909bad; font-size: 8px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
    .document-template-card-copy h6 { margin: 1px 0 3px; color: #2e3a52; font-size: 11px; font-weight: 850; }
    .document-template-card-copy p { margin: 0; color: #8b96a8; font-size: 9px; line-height: 1.4; }

    .document-template-card-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        grid-column: 1 / -1;
        gap: 6px;
        margin-top: auto;
    }

    .document-template-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        gap: 5px;
        min-height: 31px;
        border: 1px solid #d8e0f2;
        border-radius: 8px;
        color: #3157d5;
        background: #fff;
        font-size: 9px;
        font-weight: 800;
    }

    .document-template-view:hover { color: #fff; border-color: #3157d5; background: #3157d5; }

    .document-template-print {
        display: grid;
        place-items: center;
        flex: 0 0 32px;
        height: 32px;
        border: 1px solid #d7dfef;
        border-radius: 8px;
        color: #3157d5;
        background: #fff;
        font-size: 16px;
    }

    .document-template-print:hover { color: #fff; border-color: #3157d5; background: #3157d5; }

    .document-template-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 110px;
        padding: 20px;
        border: 1px dashed #d9e0eb;
        border-radius: 12px;
        color: #8a96a8;
        background: #fafbfd;
    }

    .document-template-empty > i { font-size: 28px; }
    .document-template-empty div { display: grid; gap: 2px; }
    .document-template-empty strong { color: #657187; font-size: 11px; }
    .document-template-empty span { font-size: 9px; }

    .document-detail-modal { overflow: hidden; border: 0; border-radius: 18px; box-shadow: 0 25px 70px rgba(20, 31, 56, .22); }
    .document-detail-modal .modal-header { padding: 18px 22px; border-bottom-color: #e9edf3; background: linear-gradient(110deg, #f8faff, #fff); }
    .document-modal-title { display: flex; align-items: center; gap: 12px; min-width: 0; }

    .document-modal-title > span {
        display: grid;
        place-items: center;
        flex: 0 0 45px;
        height: 45px;
        border-radius: 12px;
        color: #3157d5;
        background: #e9eeff;
        font-size: 23px;
    }

    .document-modal-title > span.is-regular { color: var(--document-regular); background: #e9f8f6; }
    .document-modal-title > span.is-narcotic { color: #cf405b; background: #fff0f3; }
    .document-modal-title > span.is-psychotropic { color: #5267dc; background: #eef0ff; }
    .document-modal-title > span.is-precursor { color: #bd7518; background: #fff5e6; }
    .document-modal-title > span.is-oot { color: #198754; background: #eaf8f0; }
    .document-modal-title small { display: block; color: #8b96a8; font-size: 9px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .document-modal-title h5 { margin: 1px 0 2px; color: #1d2a43; font-size: 16px; font-weight: 850; }

    .document-modal-number { display: flex; align-items: center; gap: 5px; }
    .document-modal-number p { max-width: 500px; overflow: hidden; margin: 0; color: #738097; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
    .document-modal-number button { display: grid; place-items: center; width: 24px; height: 22px; border: 0; border-radius: 6px; color: #7f8ca2; background: transparent; font-size: 13px; }
    .document-modal-number button:hover { color: #3157d5; background: #e9eeff; }
    .document-modal-number button.is-copied { color: #087a5b; background: #e8f8f2; }

    .document-detail-modal .modal-body { padding: 22px; background: #fbfcfe; }
    .document-detail-loading { display: flex; align-items: center; justify-content: center; gap: 9px; min-height: 230px; color: #7d899c; font-size: 12px; }
    .document-detail-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
    .document-detail-summary > div { display: grid; gap: 4px; min-width: 0; padding: 12px; border: 1px solid #e6eaf1; border-radius: 11px; background: #fff; }
    .document-detail-summary span { color: #8b96a8; font-size: 8px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
    .document-detail-summary strong { overflow: hidden; color: #344158; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }

    .document-number-section,
    .document-item-section { margin-bottom: 15px; padding: 15px; border: 1px solid #e4e9f1; border-radius: 13px; background: #fff; }
    .document-section-title { display: flex; align-items: center; gap: 10px; margin-bottom: 13px; }
    .document-section-title > span:first-child { display: grid; place-items: center; flex: 0 0 34px; height: 34px; border-radius: 9px; color: #3157d5; background: #edf1ff; font-size: 18px; }
    .document-section-title h6 { margin: 0 0 2px; color: #2f3b52; font-size: 12px; font-weight: 850; }
    .document-section-title p { margin: 0; color: #929cad; font-size: 9px; }

    .document-item-total { margin-left: auto; padding: 4px 8px; border-radius: 99px; color: #3157d5; background: #edf1ff; font-size: 9px; font-weight: 800; }
    .document-number-list { display: flex; flex-wrap: wrap; gap: 7px; }
    .document-number-list span { display: inline-flex; align-items: center; gap: 6px; padding: 6px 9px; border: 1px solid #dce3f3; border-radius: 8px; color: #41516f; background: #f8faff; font-size: 10px; font-weight: 750; }
    .document-number-list span i { color: #3157d5; }
    .document-item-table { margin: 0; }
    .document-item-table th { padding: 8px 10px; color: #919bac; background: #f8f9fc; font-size: 8px; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .document-item-table td { padding: 10px; border-color: #edf0f4; color: #556176; font-size: 10px; }
    .document-item-table td strong { color: #344158; }

    .document-detail-note { display: flex; align-items: start; gap: 8px; padding: 11px 13px; border: 1px solid #dfe7f8; border-radius: 10px; color: #657794; background: #f5f8ff; font-size: 10px; line-height: 1.5; }
    .document-detail-note i { color: #3157d5; font-size: 16px; }
    .document-detail-modal .modal-footer { padding: 13px 20px; border-top-color: #e8ecf2; background: #fff; }
    .document-detail-modal .modal-footer .btn { display: inline-flex; align-items: center; gap: 6px; border-radius: 9px; font-size: 11px; font-weight: 750; }

    @media (max-width: 1399.98px) {
        .document-command { grid-template-columns: minmax(0, 1fr) auto; }
        .document-command-art { display: none; }
        .document-overview { grid-template-columns: 240px minmax(0, 1fr); }
        .document-overview-types { grid-template-columns: repeat(5, minmax(110px, 1fr)); }
        .document-type-metric { padding-inline: 10px; }
        .document-type-metric-icon { flex-basis: 32px; width: 32px; height: 32px; }
        .document-template-grid { grid-template-columns: repeat(3, minmax(185px, 1fr)); }
    }

    @media (max-width: 1199.98px) {
        .document-overview { grid-template-columns: 220px minmax(0, 1fr); }
        .document-overview-types { grid-template-columns: repeat(3, minmax(135px, 1fr)); }
        .document-archive-heading { align-items: flex-start; }
        .document-archive-state { flex-wrap: wrap; justify-content: flex-end; }
        .document-toolbar { flex-wrap: wrap; }
        .document-search { flex-basis: calc(100% - 210px); }
        .document-detail-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 991.98px) {
        .document-command { grid-template-columns: 1fr; gap: 20px; }
        .document-command-actions { display: flex; min-width: 0; }
        .document-command-actions .btn { flex: 0 1 auto; }
        .document-overview { grid-template-columns: 1fr; }
        .document-overview-types { grid-template-columns: repeat(5, minmax(138px, 1fr)); overflow-x: auto; padding-bottom: 3px; }
        .document-type-metric { min-width: 138px; }
        .document-archive-heading { align-items: stretch; flex-direction: column; }
        .document-archive-state { justify-content: flex-start; }
        .document-template-grid { grid-template-columns: repeat(2, minmax(185px, 1fr)); }
    }

    @media (max-width: 767.98px) {
        .document-command { min-height: 0; padding: 24px 21px; border-radius: 18px; }
        .document-command h2 { font-size: 26px; }
        .document-command-copy > p { font-size: 12px; }
        .document-command-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
        .document-command-actions .btn { width: 100%; padding-inline: 10px; }
        .document-overview { padding: 9px; border-radius: 15px; }
        .document-overview-types { grid-template-columns: repeat(5, 136px); }
        .document-archive { border-radius: 15px; }
        .document-archive-heading { padding: 17px 15px; }
        .document-result-label { display: none; }
        .document-toolbar { align-items: stretch; padding: 13px 15px; }
        .document-search { flex-basis: 100%; min-width: 100%; }
        .document-filter-toggle { flex: 1; align-self: end; }
        .document-page-size { flex: 1; }
        .document-page-size .form-select { width: 100%; }
        .document-advanced-filters { grid-template-columns: 1fr 1fr; padding-inline: 15px; }
        .document-advanced-filters.is-open { max-height: 250px; }
        .document-advanced-filters > .document-field:first-child { grid-column: 1 / -1; }
        .document-date-divider { display: none; }
        .document-reset-button { grid-column: 1 / -1; }
        .document-type-tabs { padding-inline: 15px; }
        .document-active-filters { align-items: flex-start; flex-wrap: wrap; padding-inline: 15px; }
        .document-active-filters > div { order: 3; flex-basis: 100%; }
        .document-archive-alert { margin-inline: 15px; }
        .document-table-footer { flex-direction: column; }
        .document-template-heading { align-items: flex-start; flex-direction: column; padding: 16px 15px; }
        .document-template-controls { width: 100%; justify-content: space-between; }
        .document-template-content { padding-inline: 15px; }
        .document-template-context { grid-template-columns: 1fr; align-items: stretch; }
        .document-template-grid { grid-template-columns: 1fr; }
        .document-template-profile span { white-space: normal; }
        .document-detail-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .document-detail-modal .modal-body { padding: 15px; }
    }

    @media (max-width: 479.98px) {
        .document-command-actions { grid-template-columns: 1fr; }
        .document-heading-icon { display: none; }
        .document-archive-state { display: grid; grid-template-columns: 1fr auto; }
        .document-last-sync { justify-content: center; }
        .document-advanced-filters { grid-template-columns: 1fr; }
        .document-advanced-filters.is-open { max-height: 340px; }
        .document-advanced-filters > .document-field:first-child,
        .document-reset-button { grid-column: auto; }
        .document-detail-summary { grid-template-columns: 1fr; }
        .document-detail-modal .modal-footer { display: grid; grid-template-columns: 1fr 1fr; }
        .document-detail-modal .modal-footer .btn:first-child { grid-column: 1 / -1; }
        .document-detail-modal .modal-footer .btn { justify-content: center; margin: 0; }
    }

    /* Skala baca utama: nyaman untuk operasional harian tanpa mengubah kepadatan layout. */
    .document-kicker { font-size: 13px; }
    .document-command-copy > p { font-size: 15px; }
    .document-command-meta > span { font-size: 12px; }
    .document-command-actions .btn { font-size: 13px; }

    .document-total-copy > small:first-child { font-size: 11px; }
    .document-total-copy > span { font-size: 13px; }
    .document-total-copy > span strong { font-size: 29px; }
    .document-total-copy > small:last-child { font-size: 11px; }
    .document-type-metric small { font-size: 12px; }
    .document-type-metric strong { font-size: 24px; }

    .document-eyebrow { font-size: 11px; }
    .document-archive-heading h5,
    .document-template-heading h5 { font-size: 20px; }
    .document-archive-heading p,
    .document-template-heading p { font-size: 13px; }
    .document-result-label,
    .document-last-sync { font-size: 11px; }

    .document-search input { font-size: 14px; }
    .document-filter-toggle { font-size: 13px; }
    .document-field label,
    .document-template-branch label { font-size: 11px; }
    .document-field .form-select,
    .document-field .form-control,
    .document-template-branch .form-select { font-size: 13px; }
    .document-date-divider { font-size: 11px; }
    .document-reset-button { font-size: 12px; }
    .document-type-tabs button { min-height: 36px; padding-block: 7px; font-size: 12px; }
    .document-type-tabs button > span:last-child:not(.type-dot) { font-size: 10px; }
    .document-active-filters,
    .document-filter-chip,
    #resetDocumentFilters { font-size: 11px; }
    .document-archive-alert { font-size: 12px; }

    .document-table thead th { font-size: 11px; }
    .document-table tbody td { font-size: 13px; }
    .document-number-link { font-size: 13px; }
    .document-extra-count,
    .document-type-badge,
    .document-status-badge { font-size: 10px; }
    .document-date-cell strong,
    .document-reference strong,
    .document-content-count strong { font-size: 12px; }
    .document-date-cell small,
    .document-reference small,
    .document-content-count small { font-size: 11px; }
    .document-branch-cell { font-size: 12px; }
    .document-action-primary { font-size: 11px; }
    .document-table-footer .dataTables_info,
    .document-table-footer .page-link { font-size: 12px; }
    .document-empty-state strong { font-size: 14px; }
    .document-empty-state span,
    div.dataTables_wrapper div.dataTables_processing { font-size: 12px; }

    .document-template-count { font-size: 11px; }
    #toggleDocumentTemplates { font-size: 12px; }
    .document-template-profile strong { font-size: 12px; }
    .document-template-profile span { font-size: 11px; }
    .document-template-card-copy small { font-size: 10px; }
    .document-template-card-copy h6 { font-size: 13px; }
    .document-template-card-copy p { font-size: 11px; }
    .document-template-view { font-size: 11px; }
    .document-template-empty strong { font-size: 13px; }
    .document-template-empty span { font-size: 11px; }

    .document-modal-title small { font-size: 11px; }
    .document-modal-title h5 { font-size: 19px; }
    .document-modal-number p { font-size: 12px; }
    .document-detail-loading { font-size: 14px; }
    .document-detail-summary span { font-size: 10px; }
    .document-detail-summary strong { font-size: 12px; }
    .document-section-title h6 { font-size: 14px; }
    .document-section-title p { font-size: 11px; }
    .document-item-total { font-size: 11px; }
    .document-number-list span { font-size: 12px; }
    .document-item-table th { font-size: 10px; }
    .document-item-table td,
    .document-detail-note { font-size: 12px; }
    .document-detail-modal .modal-footer .btn { font-size: 13px; }

    @media (prefers-reduced-motion: reduce) {
        .document-page *,
        .document-page *::before,
        .document-page *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
    }
</style>
