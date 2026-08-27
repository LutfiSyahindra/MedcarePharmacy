<style>
    .inventory-analysis-page {
        --ia-blue: #4f6cf7;
        --ia-blue-dark: #334fc7;
        --ia-green: #0d9f73;
        --ia-amber: #d99400;
        --ia-orange: #e66c24;
        --ia-red: #d9445a;
        --ia-violet: #7457e8;
        --ia-ink: #223047;
        --ia-muted: #68778e;
        --ia-subtle: #8894a7;
        --ia-line: #e4e9f1;
        --ia-surface: #ffffff;
        --ia-surface-soft: #f7f9fc;
        --ia-shadow-sm: 0 8px 28px rgba(37, 53, 81, .06);
        --ia-shadow-md: 0 16px 42px rgba(37, 53, 81, .09);
        color: var(--ia-ink);
        font-size: 14px;
        line-height: 1.5;
    }

    .inventory-analysis-page *,
    .inventory-analysis-page *::before,
    .inventory-analysis-page *::after {
        box-sizing: border-box;
    }

    .inventory-analysis-page .breadcrumb {
        gap: 2px;
        margin-bottom: 20px;
        font-size: 13px;
    }

    .inventory-analysis-page .breadcrumb a {
        color: #5f6f87;
        font-weight: 600;
        text-decoration: none;
    }

    .inventory-analysis-page .breadcrumb a:hover {
        color: var(--ia-blue-dark);
    }

    .inventory-analysis-page .breadcrumb-item.active {
        color: #8a95a6;
    }

    .ia-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
        overflow: hidden;
        min-height: 170px;
        margin-bottom: 20px;
        padding: 32px 34px;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 24px;
        color: #fff;
        background:
            radial-gradient(circle at 87% 18%, rgba(255, 255, 255, .19), transparent 25%),
            linear-gradient(125deg, #2949c7 0%, #536ff1 56%, #6f83f5 100%);
        box-shadow: 0 20px 45px rgba(42, 72, 170, .21);
    }

    .ia-hero::before,
    .ia-hero::after {
        position: absolute;
        content: "";
        pointer-events: none;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 50%;
    }

    .ia-hero::before {
        top: -120px;
        right: 16%;
        width: 260px;
        height: 260px;
    }

    .ia-hero::after {
        right: -64px;
        bottom: -178px;
        width: 310px;
        height: 310px;
        border-width: 48px;
        border-color: rgba(255, 255, 255, .075);
    }

    .ia-hero.ia-tone-violet {
        background:
            radial-gradient(circle at 87% 18%, rgba(255, 255, 255, .18), transparent 25%),
            linear-gradient(125deg, #5134bf 0%, #7658df 56%, #957be9 100%);
    }

    .ia-hero.ia-tone-amber {
        background:
            radial-gradient(circle at 87% 18%, rgba(255, 255, 255, .2), transparent 25%),
            linear-gradient(125deg, #a55f08 0%, #d18b19 56%, #e9ad43 100%);
    }

    .ia-hero.ia-tone-orange {
        background:
            radial-gradient(circle at 87% 18%, rgba(255, 255, 255, .18), transparent 25%),
            linear-gradient(125deg, #b74a18 0%, #df6d28 56%, #ee914f 100%);
    }

    .ia-hero.ia-tone-red {
        background:
            radial-gradient(circle at 87% 18%, rgba(255, 255, 255, .17), transparent 25%),
            linear-gradient(125deg, #a92e43 0%, #d54b5e 56%, #e87180 100%);
    }

    .ia-hero.ia-tone-green {
        background:
            radial-gradient(circle at 87% 18%, rgba(255, 255, 255, .17), transparent 25%),
            linear-gradient(125deg, #08735d 0%, #109772 56%, #35b78e 100%);
    }

    .ia-hero-copy {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 20px;
        min-width: 0;
    }

    .ia-hero-icon {
        display: grid;
        flex: 0 0 68px;
        place-items: center;
        height: 68px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 20px;
        background: rgba(255, 255, 255, .15);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .16);
        font-size: 34px;
        backdrop-filter: blur(8px);
    }

    .ia-eyebrow {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 5px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
        opacity: .83;
    }

    .ia-eyebrow i {
        font-size: 16px;
    }

    .ia-hero h1 {
        margin: 0;
        color: #fff;
        font-size: clamp(26px, 2vw, 31px);
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -.025em;
    }

    .ia-hero p {
        max-width: 720px;
        margin: 8px 0 0;
        color: rgba(255, 255, 255, .84);
        font-size: 14px;
        line-height: 1.65;
    }

    .ia-hero-live {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 15px;
        color: rgba(255, 255, 255, .7);
        font-size: 11px;
    }

    .ia-hero-live span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 5px 9px;
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 999px;
        color: #fff;
        background: rgba(9, 24, 64, .13);
        font-weight: 750;
    }

    .ia-hero-live span i {
        width: 7px;
        height: 7px;
        border: 2px solid rgba(255, 255, 255, .4);
        border-radius: 50%;
        background: #75f1c0;
        box-shadow: 0 0 0 4px rgba(117, 241, 192, .12);
    }

    .ia-hero-live b {
        font-weight: 650;
    }

    .ia-hero-status {
        position: relative;
        z-index: 1;
        display: grid;
        flex: 0 0 252px;
        gap: 9px;
    }

    .ia-hero-status-label {
        grid-column: 1 / -1;
        padding-left: 2px;
        color: rgba(255, 255, 255, .58);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ia-hero-meta {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
        padding: 10px 12px;
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 13px;
        background: rgba(255, 255, 255, .11);
        backdrop-filter: blur(8px);
    }

    .ia-hero-meta > i {
        display: grid;
        flex: 0 0 32px;
        place-items: center;
        height: 32px;
        border-radius: 9px;
        background: rgba(255, 255, 255, .12);
        font-size: 18px;
    }

    .ia-hero-meta span {
        display: grid;
        min-width: 0;
    }

    .ia-hero-meta small {
        color: rgba(255, 255, 255, .64);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .ia-hero-meta b {
        overflow: hidden;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ia-analysis-nav {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 7px;
        margin-bottom: 20px;
        padding: 8px;
        border: 1px solid var(--ia-line);
        border-radius: 18px;
        background: var(--ia-surface);
        box-shadow: 0 8px 28px rgba(37, 53, 81, .06);
    }

    .ia-analysis-link {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        min-height: 58px;
        padding: 9px 11px;
        border: 1px solid transparent;
        border-radius: 12px;
        color: #66758c;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.3;
        text-align: left;
        text-decoration: none;
        transition: color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .ia-analysis-link i {
        flex: 0 0 auto;
        font-size: 20px;
    }

    .ia-analysis-icon {
        display: grid;
        flex: 0 0 36px;
        place-items: center;
        height: 36px;
        border-radius: 11px;
        color: var(--ia-blue-dark);
        background: #edf1ff;
        transition: color .18s ease, background .18s ease;
    }

    .ia-analysis-link > span:last-child {
        display: grid;
        min-width: 0;
    }

    .ia-analysis-link b {
        overflow: hidden;
        font-size: 11px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ia-analysis-link small {
        margin-top: 2px;
        color: #a1aaba;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .11em;
    }

    .ia-analysis-link:hover {
        color: var(--ia-blue-dark);
        background: #f2f5ff;
        transform: translateY(-1px);
    }

    .ia-analysis-link.is-active {
        color: #fff;
        background: linear-gradient(135deg, var(--ia-blue-dark), var(--ia-blue));
        box-shadow: 0 8px 18px rgba(79, 108, 247, .25);
    }

    .ia-analysis-link.is-active .ia-analysis-icon {
        color: #fff;
        background: rgba(255, 255, 255, .14);
    }

    .ia-analysis-link.is-active small {
        color: rgba(255, 255, 255, .63);
    }

    .ia-analysis-link:focus-visible,
    .inventory-analysis-page .btn:focus-visible,
    .ia-table-wrap:focus-visible {
        outline: 3px solid rgba(79, 108, 247, .2);
        outline-offset: 2px;
    }

    .ia-filter-card,
    .ia-panel {
        margin-bottom: 20px;
        border: 1px solid var(--ia-line);
        border-radius: 20px;
        background: var(--ia-surface);
        box-shadow: 0 10px 32px rgba(37, 53, 81, .065);
    }

    .ia-filter-card {
        overflow: hidden;
        padding: 23px 24px 0;
    }

    .ia-section-heading,
    .ia-panel-heading > div {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ia-section-heading {
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }

    .ia-section-icon {
        display: grid;
        flex: 0 0 42px;
        place-items: center;
        height: 42px;
        border-radius: 12px;
        color: var(--ia-blue-dark);
        background: #edf1ff;
        font-size: 21px;
    }

    .ia-section-heading h2,
    .ia-panel-heading h2 {
        margin: 0;
        color: #293a53;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.3;
    }

    .ia-section-heading p,
    .ia-panel-heading p {
        margin: 3px 0 0;
        color: #7e8a9d;
        font-size: 12px;
        line-height: 1.5;
    }

    .ia-section-heading .btn {
        min-height: 38px;
        padding-inline: 13px;
        border: 1px solid #e3e8ef;
        border-radius: 10px;
        color: #5d6c82;
        background: #f7f9fc;
        font-size: 12px;
        font-weight: 700;
    }

    .ia-filter-heading-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ia-filter-heading-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .ia-filter-body {
        display: grid;
        gap: 18px;
        overflow: hidden;
        max-height: 460px;
        padding-bottom: 22px;
        opacity: 1;
        transition: max-height .28s ease, opacity .2s ease, padding .28s ease;
    }

    .ia-filter-card.is-collapsed .ia-filter-body {
        max-height: 0;
        padding-bottom: 0;
        opacity: 0;
        pointer-events: none;
    }

    .ia-period-presets {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 9px 11px;
        border: 1px solid #e8ecf3;
        border-radius: 12px;
        background: #fafbfe;
    }

    .ia-period-presets > span {
        flex: 0 0 auto;
        color: #7c899c;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .ia-period-presets > div {
        display: flex;
        align-items: center;
        gap: 6px;
        overflow-x: auto;
    }

    .ia-period-presets button {
        min-height: 30px;
        padding: 5px 11px;
        border: 1px solid transparent;
        border-radius: 8px;
        color: #66758b;
        background: transparent;
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
        transition: color .16s ease, border-color .16s ease, background .16s ease, box-shadow .16s ease;
    }

    .ia-period-presets button:hover {
        color: var(--ia-blue-dark);
        background: #f0f3ff;
    }

    .ia-period-presets button.is-active {
        border-color: #d9e0ff;
        color: var(--ia-blue-dark);
        background: #fff;
        box-shadow: 0 4px 12px rgba(62, 86, 181, .1);
    }

    .ia-filter-grid {
        display: flex;
        align-items: flex-end;
        gap: 13px;
        flex-wrap: wrap;
    }

    .ia-field {
        display: grid;
        flex: 1 1 160px;
        gap: 7px;
        min-width: 0;
        margin: 0;
    }

    .ia-field:first-child {
        flex-basis: 225px;
    }

    .ia-search-field {
        flex: 2 1 280px;
    }

    .ia-field > span:first-child {
        color: #596980;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .01em;
    }

    .ia-field .form-control,
    .ia-field .form-select {
        height: 44px;
        border-color: #dbe2ec;
        border-radius: 11px;
        color: #33445d;
        background-color: #fbfcfe;
        font-size: 13px;
        box-shadow: none;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .ia-field .form-control:hover,
    .ia-field .form-select:hover {
        border-color: #c6d0df;
        background-color: #fff;
    }

    .ia-field .form-control:focus,
    .ia-field .form-select:focus {
        border-color: #8ba0f6;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(79, 108, 247, .1);
    }

    .ia-field .form-control::placeholder {
        color: #9aa5b5;
    }

    .ia-input-icon {
        position: relative;
    }

    .ia-input-icon i {
        position: absolute;
        z-index: 1;
        top: 11px;
        left: 13px;
        color: #8b97a9;
        font-size: 20px;
        pointer-events: none;
    }

    .ia-input-icon input {
        padding-right: 38px;
        padding-left: 42px;
    }

    .ia-input-icon button {
        position: absolute;
        z-index: 2;
        top: 8px;
        right: 8px;
        display: grid;
        width: 28px;
        height: 28px;
        padding: 0;
        place-items: center;
        border: 0;
        border-radius: 8px;
        color: #8490a2;
        background: #edf0f5;
        font-size: 16px;
    }

    .ia-input-icon button:hover {
        color: #3f5068;
        background: #e4e8ef;
    }

    .ia-apply-filter {
        flex: 0 0 auto;
        min-height: 44px;
        padding-inline: 20px;
        border: 0;
        border-radius: 11px;
        background: linear-gradient(135deg, var(--ia-blue-dark), var(--ia-blue));
        box-shadow: 0 8px 18px rgba(79, 108, 247, .22);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ia-apply-filter:hover {
        box-shadow: 0 10px 22px rgba(79, 108, 247, .3);
        transform: translateY(-1px);
    }

    .ia-active-filters {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 49px;
        margin-inline: -24px;
        padding: 9px 24px;
        border-top: 1px solid #ebeff5;
        background: #fafbfe;
    }

    .ia-active-filters > span {
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 6px;
        color: #69778b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
    }

    .ia-active-filters > span i {
        color: var(--ia-blue);
        font-size: 17px;
    }

    .ia-active-filters > div {
        display: flex;
        align-items: center;
        gap: 7px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .ia-active-filters > div::-webkit-scrollbar {
        display: none;
    }

    .ia-active-filters em,
    .ia-filter-chip {
        white-space: nowrap;
    }

    .ia-active-filters em {
        color: #8a96a8;
        font-size: 11px;
        font-style: normal;
    }

    .ia-filter-chip {
        display: inline-flex;
        align-items: center;
        min-height: 27px;
        padding: 4px 9px;
        border: 1px solid #e2e7ef;
        border-radius: 999px;
        color: #5c6b81;
        background: #fff;
        font-size: 10px;
        font-weight: 700;
    }

    .ia-filter-card.is-dirty {
        border-color: #cfd8ff;
        box-shadow: 0 12px 34px rgba(61, 87, 190, .1);
    }

    .ia-content-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin: 26px 2px 13px;
    }

    .ia-content-heading span {
        color: var(--ia-blue-dark);
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .ia-content-heading h2 {
        margin: 3px 0 0;
        color: #263850;
        font-size: 19px;
        font-weight: 850;
        letter-spacing: -.015em;
    }

    .ia-content-heading p {
        max-width: 420px;
        margin: 0;
        color: #7b889b;
        font-size: 11px;
        text-align: right;
    }

    .ia-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .ia-summary-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 15px;
        overflow: hidden;
        min-height: 108px;
        padding: 20px;
        border: 1px solid var(--ia-line);
        border-radius: 18px;
        background: var(--ia-surface);
        box-shadow: 0 9px 28px rgba(37, 53, 81, .06);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .ia-summary-card::before {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        content: "";
        background: linear-gradient(90deg, var(--ia-blue), transparent 82%);
        opacity: .65;
    }

    .ia-summary-card::after {
        position: absolute;
        top: 0;
        right: 0;
        width: 54px;
        height: 54px;
        content: "";
        border-radius: 0 0 0 54px;
        background: rgba(79, 108, 247, .035);
    }

    .ia-summary-card:hover {
        border-color: #d7deea;
        box-shadow: 0 14px 34px rgba(37, 53, 81, .09);
        transform: translateY(-2px);
    }

    .ia-summary-card > span {
        display: grid;
        flex: 0 0 50px;
        place-items: center;
        height: 50px;
        border-radius: 15px;
        color: var(--ia-blue);
        background: #edf1ff;
        font-size: 26px;
    }

    .ia-summary-card small {
        display: block;
        color: #78869a;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.35;
    }

    .ia-summary-card strong {
        display: block;
        margin-top: 4px;
        color: #273850;
        font-size: clamp(20px, 1.55vw, 25px);
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -.02em;
    }

    .ia-summary-card .ia-summary-footnote {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 7px;
        color: #98a2b2;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .ia-summary-card .ia-summary-footnote i {
        color: #a9b4c3;
        font-size: 13px;
    }

    .ia-summary-card .ia-summary-error {
        font-size: 14px;
        line-height: 1.5;
        letter-spacing: 0;
    }

    .ia-summary-card.is-green > span {
        color: var(--ia-green);
        background: #e6f7f1;
    }

    .ia-summary-card.is-green::before { background: linear-gradient(90deg, var(--ia-green), transparent 82%); }

    .ia-summary-card.is-amber > span {
        color: var(--ia-amber);
        background: #fff5d8;
    }

    .ia-summary-card.is-amber::before { background: linear-gradient(90deg, var(--ia-amber), transparent 82%); }

    .ia-summary-card.is-orange > span {
        color: var(--ia-orange);
        background: #ffede1;
    }

    .ia-summary-card.is-orange::before { background: linear-gradient(90deg, var(--ia-orange), transparent 82%); }

    .ia-summary-card.is-red > span {
        color: var(--ia-red);
        background: #ffe8ec;
    }

    .ia-summary-card.is-red::before { background: linear-gradient(90deg, var(--ia-red), transparent 82%); }

    .ia-summary-card.is-violet > span {
        color: var(--ia-violet);
        background: #f0ecff;
    }

    .ia-summary-card.is-violet::before { background: linear-gradient(90deg, var(--ia-violet), transparent 82%); }

    .ia-summary-card.is-loading {
        animation: iaPulse 1.2s infinite;
    }

    .ia-summary-card.is-loading > span,
    .ia-summary-card.is-loading strong {
        color: transparent;
        background: #edf0f5;
    }

    .ia-insight-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(310px, .75fr);
        gap: 20px;
    }

    .ia-panel {
        padding: 23px 24px;
    }

    .ia-panel-heading {
        margin-bottom: 20px;
    }

    .ia-distribution {
        display: grid;
        gap: 14px;
        min-height: 124px;
    }

    .ia-distribution-layout {
        display: grid;
        grid-template-columns: 168px minmax(0, 1fr);
        align-items: center;
        gap: 28px;
        min-height: 180px;
    }

    .ia-donut-shell {
        display: grid;
        place-items: center;
    }

    .ia-donut {
        position: relative;
        display: grid;
        width: 146px;
        height: 146px;
        place-items: center;
        border-radius: 50%;
        background: conic-gradient(#e9edf4 0 100%);
        box-shadow: inset 0 0 0 1px rgba(41, 58, 83, .03), 0 12px 25px rgba(39, 57, 86, .08);
        transition: background .28s ease;
    }

    .ia-donut::before {
        position: absolute;
        width: 101px;
        height: 101px;
        content: "";
        border: 1px solid #edf0f5;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 5px 18px rgba(47, 65, 90, .08);
    }

    .ia-donut > span {
        position: relative;
        z-index: 1;
        display: grid;
        max-width: 86px;
        text-align: center;
    }

    .ia-donut small {
        color: #909cad;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .ia-donut strong {
        overflow: hidden;
        margin-top: 3px;
        color: #293a53;
        font-size: 13px;
        font-weight: 850;
        line-height: 1.25;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ia-distribution-row {
        display: grid;
        grid-template-columns: minmax(125px, 175px) minmax(120px, 1fr) auto;
        align-items: center;
        gap: 13px;
    }

    .ia-distribution-row::before {
        width: 8px;
        height: 8px;
        grid-column: 1;
        grid-row: 1;
        content: "";
        border-radius: 50%;
        background: var(--ia-blue);
        box-shadow: 0 0 0 4px rgba(79, 108, 247, .08);
    }

    .ia-distribution-row.is-green::before { background: var(--ia-green); box-shadow: 0 0 0 4px rgba(13, 159, 115, .08); }
    .ia-distribution-row.is-amber::before { background: var(--ia-amber); box-shadow: 0 0 0 4px rgba(217, 148, 0, .08); }
    .ia-distribution-row.is-orange::before { background: var(--ia-orange); box-shadow: 0 0 0 4px rgba(230, 108, 36, .08); }
    .ia-distribution-row.is-red::before { background: var(--ia-red); box-shadow: 0 0 0 4px rgba(217, 68, 90, .08); }
    .ia-distribution-row.is-violet::before { background: var(--ia-violet); box-shadow: 0 0 0 4px rgba(116, 87, 232, .08); }

    .ia-distribution-label {
        overflow: hidden;
        color: #52627a;
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ia-distribution-row .ia-distribution-label {
        padding-left: 17px;
        grid-column: 1;
        grid-row: 1;
    }

    .ia-distribution-track {
        overflow: hidden;
        height: 10px;
        border-radius: 999px;
        background: #edf1f5;
    }

    .ia-distribution-track span {
        display: block;
        min-width: 3px;
        height: 100%;
        border-radius: inherit;
        background: var(--ia-blue);
        animation: iaGrow .45s ease-out;
    }

    .ia-distribution-track span.is-green { background: var(--ia-green); }
    .ia-distribution-track span.is-amber { background: var(--ia-amber); }
    .ia-distribution-track span.is-orange { background: var(--ia-orange); }
    .ia-distribution-track span.is-red { background: var(--ia-red); }
    .ia-distribution-track span.is-violet { background: var(--ia-violet); }

    .ia-distribution-value {
        color: #2f415a;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ia-method-card {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 15px;
        overflow: hidden;
        min-height: 206px;
        padding: 24px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 20px;
        color: #fff;
        background:
            radial-gradient(circle at 95% 2%, rgba(112, 147, 222, .26), transparent 35%),
            linear-gradient(145deg, #203553, #304d78);
        box-shadow: 0 15px 34px rgba(32, 53, 83, .18);
    }

    .ia-insight-stack {
        display: grid;
        gap: 13px;
    }

    .ia-priority-card {
        position: relative;
        overflow: hidden;
        padding: 20px;
        border: 1px solid #dfe5ff;
        border-radius: 20px;
        background:
            radial-gradient(circle at 100% 0, rgba(79, 108, 247, .12), transparent 40%),
            linear-gradient(145deg, #fbfcff, #f5f7ff);
        box-shadow: 0 12px 30px rgba(53, 75, 158, .08);
    }

    .ia-priority-card::after {
        position: absolute;
        right: -32px;
        bottom: -45px;
        width: 110px;
        height: 110px;
        content: "";
        border: 20px solid rgba(79, 108, 247, .035);
        border-radius: 50%;
    }

    .ia-priority-top {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .ia-priority-top > span {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 10px;
        color: #fff;
        background: linear-gradient(135deg, var(--ia-blue-dark), var(--ia-violet));
        box-shadow: 0 6px 14px rgba(79, 108, 247, .2);
        font-size: 17px;
    }

    .ia-priority-top small {
        color: var(--ia-blue-dark);
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .ia-priority-card h3 {
        position: relative;
        z-index: 1;
        margin: 13px 0 6px;
        color: #273951;
        font-size: 15px;
        font-weight: 850;
        line-height: 1.35;
    }

    .ia-priority-card p {
        position: relative;
        z-index: 1;
        margin: 0;
        color: #6f7e92;
        font-size: 11px;
        line-height: 1.6;
    }

    .ia-priority-card button {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 13px;
        padding: 0;
        border: 0;
        color: var(--ia-blue-dark);
        background: transparent;
        font-size: 10px;
        font-weight: 800;
    }

    .ia-priority-card button:hover:not(:disabled) {
        color: #20399f;
        text-decoration: underline;
    }

    .ia-priority-card button:disabled {
        color: #9ba5b4;
    }

    .ia-insight-stack .ia-method-card {
        min-height: 0;
        padding: 19px 20px;
        border-radius: 18px;
    }

    .ia-insight-stack .ia-method-icon {
        flex-basis: 38px;
        height: 38px;
        border-radius: 11px;
        font-size: 20px;
    }

    .ia-insight-stack .ia-method-card h3 {
        margin: 3px 0 5px;
        font-size: 13px;
    }

    .ia-insight-stack .ia-method-card p {
        font-size: 10px;
        line-height: 1.55;
    }

    .ia-method-card::after {
        position: absolute;
        right: -42px;
        bottom: -56px;
        width: 140px;
        height: 140px;
        content: "";
        border: 24px solid rgba(255, 255, 255, .035);
        border-radius: 50%;
    }

    .ia-method-icon {
        position: relative;
        z-index: 1;
        display: grid;
        flex: 0 0 46px;
        place-items: center;
        height: 46px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 13px;
        background: rgba(255, 255, 255, .1);
        font-size: 24px;
    }

    .ia-method-card > div {
        position: relative;
        z-index: 1;
    }

    .ia-method-card small {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .09em;
        text-transform: uppercase;
        opacity: .63;
    }

    .ia-method-card h3 {
        margin: 5px 0 9px;
        color: #fff;
        font-size: 16px;
        font-weight: 800;
    }

    .ia-method-card p {
        margin: 0;
        color: rgba(255, 255, 255, .75);
        font-size: 12px;
        line-height: 1.7;
    }

    .ia-table-panel {
        overflow: hidden;
        padding: 0;
    }

    .ia-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 24px;
        border-bottom: 1px solid #e9edf3;
    }

    .ia-table-toolbar .ia-panel-heading {
        margin: 0;
    }

    .ia-table-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .ia-table-actions > label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 0;
        color: #68778c;
        font-size: 12px;
        font-weight: 600;
    }

    .ia-table-actions .form-select {
        width: 72px;
        height: 38px;
        border-color: #dce3ed;
        border-radius: 9px;
        color: #3f5067;
        font-size: 12px;
        box-shadow: none;
    }

    .ia-table-actions .btn {
        min-height: 38px;
        padding: 8px 12px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
    }

    .ia-table-actions .ia-clear-sort {
        color: #5d6d83;
        border-color: #e0e5ed;
        background: #f8f9fc;
    }

    .ia-table-actions #iaRefresh {
        width: 38px;
        padding-inline: 0;
        font-size: 17px;
    }

    .ia-table-wrap {
        min-height: 210px;
        scrollbar-color: #c9d2df #f4f6f9;
        scrollbar-width: thin;
    }

    .ia-table {
        min-width: max-content;
        margin: 0;
    }

    .ia-table thead th {
        position: sticky;
        z-index: 3;
        top: 0;
        padding: 13px 15px;
        border: 0;
        background: #f6f8fb;
        color: #627188;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ia-sort-button {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin: -6px -7px;
        padding: 6px 7px;
        border: 0;
        border-radius: 7px;
        color: inherit;
        background: transparent;
        font: inherit;
        letter-spacing: inherit;
        text-transform: inherit;
    }

    .ia-sort-button i {
        color: #a8b1bf;
        font-size: 14px;
    }

    .ia-sort-button:hover {
        color: var(--ia-blue-dark);
        background: #eceffd;
    }

    .ia-sort-button.is-active {
        color: var(--ia-blue-dark);
        background: #e8edff;
    }

    .ia-sort-button.is-active i {
        color: var(--ia-blue);
    }

    .ia-table tbody td {
        padding: 14px 15px;
        border-color: #edf0f4;
        color: #4e5e75;
        font-size: 12px;
        line-height: 1.45;
        vertical-align: middle;
    }

    .ia-table tbody tr {
        transition: background .15s ease;
    }

    .ia-table tbody tr:hover {
        background: #f9fbff;
    }

    .ia-table tbody tr.is-priority-row {
        background: linear-gradient(90deg, rgba(79, 108, 247, .055), transparent 48%);
    }

    .ia-table tbody tr.is-priority-row td:first-child {
        box-shadow: inset 3px 0 var(--ia-blue);
    }

    .ia-po-select-heading {
        width: 54px;
        text-align: center;
    }

    .ia-po-select-heading input,
    .ia-po-row-check {
        width: 17px;
        height: 17px;
        border-color: #b8c4d4;
        accent-color: var(--ia-green);
        cursor: pointer;
    }

    .ia-po-selector {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-width: 45px;
        margin: 0;
    }

    .ia-po-selector > span {
        display: grid;
        place-items: center;
        width: 27px;
        height: 27px;
        border-radius: 8px;
        color: #087b5b;
        background: #e4f7f0;
        font-size: 15px;
    }

    .ia-po-row-check:disabled {
        cursor: not-allowed;
    }

    .ia-po-row-check:disabled + span {
        color: #9a6a00;
        background: #fff3ce;
    }

    #iaCreatePo:disabled {
        cursor: not-allowed;
    }

    .ia-product {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 210px;
    }

    .ia-product-mark {
        display: grid;
        flex: 0 0 38px;
        place-items: center;
        height: 38px;
        border-radius: 11px;
        color: var(--ia-blue-dark);
        background: #edf1ff;
        font-size: 19px;
    }

    .ia-product strong {
        display: block;
        max-width: 260px;
        overflow: hidden;
        color: #293a53;
        font-size: 13px;
        font-weight: 750;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ia-product small {
        display: block;
        margin-top: 2px;
        color: #8a96a8;
        font-size: 11px;
    }

    .ia-number {
        color: #2f415a;
        font-weight: 800;
        white-space: nowrap;
    }

    .ia-muted {
        color: #8e99aa;
    }

    .ia-status {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 5px 9px;
        border-radius: 999px;
        color: #3d5bc4;
        background: #edf1ff;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .ia-status.is-green { color: #087b5b; background: #e4f7f0; }
    .ia-status.is-amber { color: #926200; background: #fff3ce; }
    .ia-status.is-orange { color: #b64f16; background: #ffe9dc; }
    .ia-status.is-red { color: #b93449; background: #ffe6ea; }
    .ia-status.is-violet { color: #6244cc; background: #eee9ff; }

    .ia-empty-state,
    .ia-loading-state {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 140px;
        padding: 24px;
        color: #7f8b9d;
        font-size: 13px;
        line-height: 1.6;
        text-align: center;
    }

    .ia-loading-state i {
        font-size: 21px;
    }

    .ia-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 15px 24px;
        border-top: 1px solid #e9edf3;
        color: #788598;
        background: #fcfdff;
        font-size: 12px;
    }

    .ia-pagination > div {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ia-pagination .btn {
        display: grid;
        width: 34px;
        height: 34px;
        padding: 0;
        place-items: center;
        border: 1px solid #e1e6ee;
        border-radius: 9px;
        color: #4f5f75;
        background: #fff;
        font-size: 18px;
    }

    .ia-pagination .btn:hover:not(:disabled) {
        color: var(--ia-blue-dark);
        border-color: #cdd7fb;
        background: #f1f4ff;
    }

    .ia-po-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        box-shadow: 0 24px 70px rgba(28, 43, 69, .24);
    }

    .ia-po-modal form {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        max-height: calc(100vh - 56px);
        overflow: hidden;
    }

    .ia-po-modal .modal-header {
        align-items: flex-start;
        padding: 21px 24px;
        border-bottom: 1px solid #e9edf3;
        background: linear-gradient(135deg, #f7fbfa, #f5f8ff);
    }

    .ia-po-modal-title {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .ia-po-modal-title > span {
        display: grid;
        flex: 0 0 46px;
        place-items: center;
        height: 46px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, #087a5d, #12ab7e);
        box-shadow: 0 9px 20px rgba(13, 159, 115, .2);
        font-size: 23px;
    }

    .ia-po-modal-title small {
        color: var(--ia-green);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ia-po-modal-title h5 {
        margin: 1px 0 2px;
        color: #25354d;
        font-size: 18px;
        font-weight: 800;
    }

    .ia-po-modal-title p {
        margin: 0;
        color: #758297;
        font-size: 12px;
    }

    .ia-po-modal .modal-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 22px 24px;
        background: #fbfcfe;
    }

    .ia-po-modal-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(190px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .ia-po-selection-card {
        min-width: 0;
        padding: 14px 15px;
        border: 1px solid #e1e7ef;
        border-radius: 13px;
        background: #fff;
    }

    .ia-po-selection-card {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .ia-po-selection-card > span {
        display: grid;
        flex: 0 0 38px;
        place-items: center;
        height: 38px;
        border-radius: 11px;
        color: #4661cc;
        background: #edf1ff;
        font-size: 19px;
    }

    .ia-po-selection-card div {
        display: grid;
        min-width: 0;
    }

    .ia-po-selection-card small {
        color: #8793a5;
        font-size: 10px;
        font-weight: 700;
    }

    .ia-po-selection-card strong {
        overflow: hidden;
        margin-top: 2px;
        color: #2e3f57;
        font-size: 13px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ia-po-discount-note {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        padding: 10px 12px;
        border: 1px solid #dbe4fb;
        border-radius: 10px;
        color: #526681;
        background: #f3f6ff;
        font-size: 11px;
    }

    .ia-po-discount-note i {
        color: #5270dc;
        font-size: 17px;
    }

    .ia-po-item-wrap {
        border: 1px solid #e1e6ee;
        border-radius: 13px;
        background: #fff;
    }

    .ia-po-item-table {
        min-width: 1160px;
    }

    .ia-po-item-table thead th {
        padding: 11px 12px;
        border: 0;
        background: #f5f7fa;
        color: #66758b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ia-po-item-table tbody td {
        padding: 12px;
        border-color: #edf0f4;
        color: #4c5c72;
        font-size: 11px;
        white-space: nowrap;
    }

    .ia-po-product {
        display: grid;
        min-width: 210px;
        max-width: 300px;
    }

    .ia-po-product strong {
        overflow: hidden;
        color: #2e4059;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ia-po-product small {
        margin-top: 2px;
        color: #8a96a8;
        font-size: 10px;
    }

    .ia-po-discount,
    .ia-po-estimated-price {
        width: 88px;
        min-height: 36px;
        border-color: #dae1ea;
        border-radius: 8px;
        font-size: 11px;
        text-align: right;
        box-shadow: none;
    }

    .ia-po-estimated-price {
        width: 130px;
    }

    .ia-po-line-distributor {
        width: 220px;
        min-height: 36px;
        border-color: #dae1ea;
        border-radius: 8px;
        font-size: 11px;
        box-shadow: none;
    }

    .ia-po-line-distributor:focus {
        border-color: #9eade5;
        box-shadow: 0 0 0 3px rgba(79, 108, 247, .1);
    }

    .ia-po-discount:focus,
    .ia-po-estimated-price:focus {
        border-color: #9eade5;
        box-shadow: 0 0 0 3px rgba(79, 108, 247, .1);
    }

    .ia-po-line-total {
        color: #087b5b;
        font-size: 12px;
    }

    .ia-po-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 16px 24px;
        border-top: 1px solid #e6ebf1;
        background: #fff;
    }

    .ia-po-total-summary {
        display: flex;
        align-items: center;
        gap: 18px;
        color: #748196;
        font-size: 11px;
    }

    .ia-po-total-summary span {
        display: grid;
        gap: 1px;
    }

    .ia-po-total-summary b {
        color: #4a5b72;
        font-size: 12px;
    }

    .ia-po-total-summary strong {
        color: #087b5b;
        font-size: 18px;
    }

    .ia-po-footer-actions {
        display: flex;
        gap: 9px;
    }

    .ia-po-footer-actions .btn {
        min-width: 105px;
        min-height: 40px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 750;
    }

    .ia-toast {
        position: fixed;
        z-index: 1080;
        right: 24px;
        bottom: 24px;
        display: flex;
        align-items: center;
        gap: 11px;
        width: min(350px, calc(100vw - 32px));
        padding: 13px 15px;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 14px;
        color: #fff;
        background: rgba(32, 53, 83, .96);
        box-shadow: 0 18px 45px rgba(24, 39, 63, .26);
        backdrop-filter: blur(12px);
        animation: iaToastIn .24s ease-out;
    }

    .ia-toast > span {
        display: grid;
        flex: 0 0 34px;
        place-items: center;
        height: 34px;
        border-radius: 10px;
        color: #76e1bd;
        background: rgba(118, 225, 189, .12);
        font-size: 19px;
    }

    .ia-toast p {
        margin: 0;
        color: rgba(255, 255, 255, .88);
        font-size: 11px;
        font-weight: 650;
        line-height: 1.5;
    }

    .ia-load-progress {
        position: fixed;
        z-index: 1100;
        top: 0;
        right: 0;
        left: 0;
        overflow: hidden;
        height: 3px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .15s ease;
    }

    .inventory-analysis-page.is-loading .ia-load-progress {
        opacity: 1;
    }

    .ia-load-progress span {
        display: block;
        width: 38%;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--ia-blue), #8f70ee, var(--ia-blue));
        box-shadow: 0 0 12px rgba(79, 108, 247, .45);
        animation: iaProgress 1.05s ease-in-out infinite;
    }

    @keyframes iaPulse {
        50% { opacity: .58; }
    }

    @keyframes iaGrow {
        from { width: 0; }
    }

    @keyframes iaProgress {
        from { transform: translateX(-110%); }
        to { transform: translateX(300%); }
    }

    @keyframes iaToastIn {
        from { opacity: 0; transform: translateY(8px); }
    }

    @media (max-width: 1199.98px) {
        .ia-analysis-nav {
            grid-template-columns: repeat(3, 1fr);
        }

        .ia-summary-card {
            padding: 17px;
        }
    }

    @media (max-width: 991.98px) {
        .ia-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .ia-hero-status {
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }

        .ia-summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .ia-insight-grid {
            grid-template-columns: 1fr;
        }

        .ia-insight-stack {
            grid-template-columns: 1fr 1fr;
        }

        .ia-method-card {
            min-height: auto;
        }

        .ia-po-modal-overview {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .inventory-analysis-page .breadcrumb {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .ia-hero {
            min-height: 0;
            padding: 25px 20px;
            border-radius: 20px;
        }

        .ia-hero-copy {
            align-items: flex-start;
            gap: 14px;
        }

        .ia-hero-icon {
            flex-basis: 54px;
            height: 54px;
            border-radius: 16px;
            font-size: 28px;
        }

        .ia-hero h1 {
            font-size: 24px;
        }

        .ia-hero p {
            font-size: 13px;
        }

        .ia-hero-status {
            grid-template-columns: 1fr;
        }

        .ia-analysis-nav {
            display: flex;
            overflow-x: auto;
            padding: 7px;
            scroll-snap-type: x proximity;
        }

        .ia-analysis-link {
            flex: 0 0 174px;
            min-width: 174px;
            scroll-snap-align: start;
        }

        .ia-filter-card,
        .ia-panel {
            border-radius: 17px;
        }

        .ia-filter-card,
        .ia-panel {
            padding: 20px 18px;
        }

        .ia-filter-card {
            padding-bottom: 0;
        }

        .ia-filter-heading-actions .btn span {
            display: none;
        }

        .ia-period-presets {
            align-items: flex-start;
            flex-direction: column;
            gap: 7px;
        }

        .ia-active-filters {
            align-items: flex-start;
            flex-direction: column;
            gap: 7px;
            margin-inline: -18px;
            padding-inline: 18px;
        }

        .ia-active-filters > div {
            width: 100%;
        }

        .ia-content-heading {
            align-items: flex-start;
            flex-direction: column;
            gap: 6px;
        }

        .ia-content-heading p {
            text-align: left;
        }

        .ia-section-heading {
            align-items: flex-start;
        }

        .ia-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
        }

        .ia-field,
        .ia-search-field {
            width: 100%;
        }

        .ia-apply-filter {
            width: 100%;
        }

        .ia-summary-grid {
            grid-template-columns: 1fr;
        }

        .ia-summary-card {
            min-height: 96px;
        }

        .ia-table-panel {
            padding: 0;
        }

        .ia-table-toolbar {
            align-items: flex-start;
            flex-direction: column;
            padding: 20px 18px;
        }

        .ia-table-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .ia-table-actions #iaExportCsv {
            margin-left: auto;
        }

        .ia-distribution-row {
            grid-template-columns: minmax(100px, 130px) 1fr auto;
        }

        .ia-distribution-layout {
            grid-template-columns: 132px minmax(0, 1fr);
            gap: 16px;
        }

        .ia-donut {
            width: 122px;
            height: 122px;
        }

        .ia-donut::before {
            width: 84px;
            height: 84px;
        }

        .ia-pagination {
            align-items: flex-start;
            flex-direction: column;
            padding: 14px 18px;
        }

        .ia-po-modal-footer,
        .ia-po-total-summary {
            align-items: stretch;
            flex-direction: column;
        }

        .ia-po-modal-footer {
            gap: 12px;
        }

        .ia-po-total-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .ia-po-footer-actions .btn {
            flex: 1 1 0;
        }
    }

    @media (max-width: 479.98px) {
        .ia-hero-copy {
            flex-direction: column;
        }

        .ia-section-heading {
            flex-direction: column;
        }

        .ia-section-heading .btn {
            align-self: flex-start;
        }

        .ia-filter-card .ia-section-heading {
            align-items: center;
            flex-direction: row;
        }

        .ia-filter-card .ia-section-heading > div:first-child .ia-section-icon {
            display: none;
        }

        .ia-filter-heading-actions #iaResetFilter {
            font-size: 0;
        }

        .ia-filter-heading-actions #iaResetFilter i {
            font-size: 17px;
        }

        .ia-insight-stack {
            grid-template-columns: 1fr;
        }

        .ia-distribution-layout {
            grid-template-columns: 1fr;
        }

        .ia-distribution-row {
            grid-template-columns: 1fr auto;
        }

        .ia-distribution-track {
            grid-column: 1 / -1;
            grid-row: 2;
        }

        .ia-toast {
            right: 16px;
            bottom: 16px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ia-summary-card,
        .ia-analysis-link,
        .ia-apply-filter,
        .ia-distribution-track span,
        .ia-filter-body,
        .ia-load-progress span,
        .ia-toast {
            animation: none;
            transition: none;
        }
    }
</style>
