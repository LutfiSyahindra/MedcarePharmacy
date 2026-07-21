<style>
    :root {
        --medcare-tab-sidebar-width: var(--medcare-sidebar-width, 284px);
        --medcare-tab-sidebar-folded-width: var(--medcare-sidebar-folded-width, 82px);
        --medcare-tab-height: 72px;
        --medcare-tab-gutter: clamp(.8rem, 1.4vw, 1.3rem);
        --medcare-tab-primary: #2563eb;
        --medcare-tab-cyan: #06b6d4;
        --medcare-tab-mint: #10b981;
        --medcare-tab-ink: #18233a;
        --medcare-tab-muted: #718096;
        --medcare-tab-line: #dfe8f3;
    }

    .medcare-tab-shell {
        position: fixed;
        top: 60px;
        right: auto;
        left: var(--medcare-tab-sidebar-width);
        z-index: 970;
        display: grid;
        width: calc(100vw - var(--medcare-tab-sidebar-width));
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: .8rem;
        min-width: 0;
        min-height: var(--medcare-tab-height);
        padding: .65rem var(--medcare-tab-gutter);
        border-bottom: 1px solid rgba(207, 219, 233, .92);
        color: var(--medcare-tab-ink);
        background:
            radial-gradient(circle at 10% -80%, rgba(37, 99, 235, .11), transparent 32%),
            radial-gradient(circle at 90% 150%, rgba(6, 182, 212, .08), transparent 30%),
            rgba(255, 255, 255, .965);
        box-shadow: 0 14px 34px rgba(31, 57, 91, .075), inset 0 1px 0 rgba(255, 255, 255, .95);
        -webkit-backdrop-filter: blur(18px) saturate(145%);
        backdrop-filter: blur(18px) saturate(145%);
        transition: left .24s ease, width .24s ease, padding .18s ease, opacity .18s ease,
            transform .18s ease, box-shadow .2s ease;
    }

    .medcare-tab-shell::before {
        position: absolute;
        top: 0;
        right: var(--medcare-tab-gutter);
        left: var(--medcare-tab-gutter);
        height: 2px;
        border-radius: 0 0 999px 999px;
        content: "";
        background: linear-gradient(90deg, transparent, #60a5fa 23%, #22d3ee 52%, #34d399 76%, transparent);
        opacity: .58;
    }

    .main-wrapper .page-wrapper .page-content {
        margin-top: calc(60px + var(--medcare-tab-height));
    }

    @media (min-width: 992px) {
        .sidebar-folded:not(.open-sidebar-folded) .medcare-tab-shell {
            left: var(--medcare-tab-sidebar-folded-width);
            width: calc(100vw - var(--medcare-tab-sidebar-folded-width));
        }

        .sidebar-folded.open-sidebar-folded .medcare-tab-shell {
            left: var(--medcare-tab-sidebar-width);
            width: calc(100vw - var(--medcare-tab-sidebar-width));
        }
    }

    .medcare-tab-brand {
        display: inline-flex;
        min-width: 0;
        align-items: center;
        gap: .62rem;
        padding-right: .15rem;
        white-space: nowrap;
    }

    .medcare-tab-brand-icon {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border: 2px solid #fff;
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(145deg, #3b82f6 0%, #168ed5 55%, #10b9a0 100%);
        box-shadow: 0 8px 18px rgba(37, 99, 235, .2), 0 0 0 1px rgba(37, 99, 235, .08);
    }

    .medcare-tab-brand-icon svg {
        width: 17px;
        height: 17px;
    }

    .medcare-tab-brand-copy {
        display: flex;
        flex-direction: column;
        line-height: 1.05;
    }

    .medcare-tab-brand-copy strong {
        color: #1d3d70;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .075em;
        text-transform: uppercase;
    }

    .medcare-tab-brand-copy small {
        margin-top: 5px;
        color: #8a9ab0;
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .medcare-tab-strip {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: .42rem;
        min-width: 0;
    }

    .medcare-tab-track {
        position: relative;
        min-width: 0;
        overflow: hidden;
        border-radius: 13px;
    }

    .medcare-tab-track::before,
    .medcare-tab-track::after {
        position: absolute;
        top: 0;
        bottom: 0;
        z-index: 2;
        width: 32px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .18s ease;
        content: "";
    }

    .medcare-tab-track::before {
        left: 0;
        background: linear-gradient(90deg, rgba(249, 252, 255, .98), rgba(249, 252, 255, 0));
    }

    .medcare-tab-track::after {
        right: 0;
        background: linear-gradient(270deg, rgba(249, 252, 255, .98), rgba(249, 252, 255, 0));
    }

    .medcare-tab-shell.is-overflowing:not(.is-at-start) .medcare-tab-track::before,
    .medcare-tab-shell.is-overflowing:not(.is-at-end) .medcare-tab-track::after {
        opacity: 1;
    }

    .medcare-page-tabs {
        display: flex;
        width: 100%;
        min-width: 0;
        align-items: center;
        gap: .48rem;
        padding: .2rem .1rem .28rem;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        scroll-padding-inline: 2.4rem;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }

    .medcare-page-tabs::-webkit-scrollbar {
        display: none;
    }

    .medcare-page-tab {
        position: relative;
        display: inline-grid;
        min-width: 0;
        max-width: clamp(168px, 18vw, 248px);
        min-height: 42px;
        flex: 0 0 auto;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        overflow: hidden;
        border: 1px solid #dfe7f1;
        border-radius: 13px;
        color: #5e6d83;
        background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        box-shadow: 0 7px 17px rgba(34, 59, 92, .055), inset 0 1px 0 #fff;
        scroll-snap-align: start;
        transition: border-color .17s ease, box-shadow .17s ease, transform .17s ease, color .17s ease,
            background .17s ease;
    }

    .medcare-page-tab::before {
        position: absolute;
        top: 9px;
        bottom: 9px;
        left: 0;
        width: 3px;
        border-radius: 0 4px 4px 0;
        content: "";
        background: transparent;
        transition: background .17s ease, box-shadow .17s ease;
    }

    .medcare-page-tab:hover {
        z-index: 1;
        border-color: #c9dafa;
        color: #315f9f;
        background: linear-gradient(180deg, #fff, #f2f7ff);
        box-shadow: 0 11px 24px rgba(37, 99, 235, .095);
        transform: translateY(-1px);
    }

    .medcare-page-tab.is-active {
        color: #174b9b;
        border-color: #bcd5fb;
        background:
            radial-gradient(circle at 100% 0, rgba(6, 182, 212, .1), transparent 38%),
            linear-gradient(120deg, #eaf3ff 0%, #f1f8ff 58%, #effcfb 100%);
        box-shadow: 0 11px 25px rgba(37, 99, 235, .13), inset 0 1px 0 rgba(255, 255, 255, .92);
    }

    .medcare-page-tab.is-active::before {
        background: linear-gradient(180deg, var(--medcare-tab-primary), var(--medcare-tab-cyan));
        box-shadow: 0 0 10px rgba(37, 99, 235, .28);
    }

    .medcare-page-tab-link {
        display: inline-flex;
        min-width: 0;
        align-items: center;
        gap: .5rem;
        padding: .48rem .32rem .48rem .62rem;
        color: inherit;
        font-size: .76rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .medcare-page-tab-link:hover,
    .medcare-page-tab-link:focus {
        color: inherit;
        text-decoration: none;
    }

    .medcare-page-tab-link:focus-visible,
    .medcare-page-tab-close:focus-visible,
    .medcare-tab-clear:focus-visible,
    .medcare-tab-nav-button:focus-visible {
        outline: 3px solid rgba(59, 130, 246, .19);
        outline-offset: 1px;
    }

    .medcare-page-tab-icon {
        display: inline-grid;
        width: 26px;
        height: 26px;
        flex: 0 0 26px;
        place-items: center;
        border: 1px solid #dce8f7;
        border-radius: 8px;
        color: #58779e;
        background: #f1f6fc;
    }

    .medcare-page-tab-icon svg {
        width: 13px;
        height: 13px;
    }

    .medcare-page-tab.is-active .medcare-page-tab-icon {
        border-color: rgba(255, 255, 255, .72);
        color: #fff;
        background: linear-gradient(145deg, #3b82f6, #168ed5 56%, #10b9a0);
        box-shadow: 0 5px 12px rgba(37, 99, 235, .2);
    }

    .medcare-page-tab-title {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .medcare-page-tab-close {
        display: inline-grid;
        width: 27px;
        height: 27px;
        margin-right: .36rem;
        place-items: center;
        border: 0;
        border-radius: 9px;
        color: #8796aa;
        background: transparent;
        cursor: pointer;
        line-height: 1;
        opacity: .75;
        transition: color .15s ease, background .15s ease, opacity .15s ease, transform .15s ease;
    }

    .medcare-page-tab-close svg {
        width: 13px;
        height: 13px;
    }

    .medcare-page-tab-close:hover,
    .medcare-page-tab-close:focus {
        color: #e54861;
        background: #fff0f2;
        opacity: 1;
        transform: scale(1.04);
    }

    .medcare-tab-shell.is-single-tab .medcare-page-tab-close {
        display: none;
        pointer-events: none;
    }

    .medcare-tab-tools {
        display: inline-flex;
        min-width: 0;
        align-items: center;
        gap: .45rem;
        white-space: nowrap;
    }

    .medcare-tab-count {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        gap: .38rem;
        padding: .42rem .64rem;
        border: 1px solid #dfe8f3;
        border-radius: 11px;
        color: #5f7087;
        background: rgba(255, 255, 255, .86);
        box-shadow: 0 6px 15px rgba(33, 57, 89, .045);
        font-size: .7rem;
        font-weight: 800;
    }

    .medcare-tab-count svg {
        width: 14px;
        height: 14px;
        color: #3476d6;
    }

    .medcare-tab-clear,
    .medcare-tab-nav-button {
        display: inline-grid;
        place-items: center;
        border: 1px solid #dbe6f3;
        border-radius: 11px;
        color: #65778f;
        background: rgba(255, 255, 255, .92);
        box-shadow: 0 6px 15px rgba(33, 57, 89, .05);
        transition: color .15s ease, border-color .15s ease, background .15s ease, box-shadow .15s ease,
            transform .15s ease, opacity .15s ease;
    }

    .medcare-tab-clear {
        min-height: 38px;
        grid-auto-flow: column;
        gap: .38rem;
        padding: .42rem .66rem;
        font-size: .7rem;
        font-weight: 800;
    }

    .medcare-tab-nav-button {
        width: 36px;
        height: 36px;
    }

    .medcare-tab-clear svg,
    .medcare-tab-nav-button svg {
        width: 15px;
        height: 15px;
    }

    .medcare-tab-nav-button:hover:not(:disabled) {
        color: #2563eb;
        border-color: #bfd4f5;
        background: #eff6ff;
        box-shadow: 0 8px 18px rgba(37, 99, 235, .1);
        transform: translateY(-1px);
    }

    .medcare-tab-clear:hover:not(:disabled) {
        color: #df3f5a;
        border-color: #f5c5ce;
        background: #fff4f5;
        box-shadow: 0 8px 18px rgba(223, 63, 90, .08);
        transform: translateY(-1px);
    }

    .medcare-tab-clear:disabled,
    .medcare-tab-nav-button:disabled {
        cursor: not-allowed;
        box-shadow: none;
        opacity: .38;
        transform: none;
    }

    .medcare-tab-shell:not(.is-overflowing) .medcare-tab-nav-button {
        display: none;
    }

    @media (min-width: 1200px) and (max-width: 1399.98px) {
        .medcare-tab-brand-copy {
            display: none;
        }

        .medcare-tab-brand {
            padding-right: 0;
        }

        .medcare-page-tab {
            max-width: clamp(160px, 18vw, 220px);
        }
    }

    @media (min-width: 992px) and (max-width: 1199.98px) {
        .medcare-tab-shell {
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .55rem;
            padding-right: .75rem;
            padding-left: .75rem;
        }

        .medcare-tab-brand,
        .medcare-tab-count {
            display: none;
        }

        .medcare-tab-clear {
            width: 38px;
            padding: 0;
        }

        .medcare-tab-clear span {
            display: none;
        }

        .medcare-page-tab {
            max-width: min(28vw, 215px);
        }
    }

    @media (max-width: 991.98px) {
        .medcare-tab-shell,
        .sidebar-folded .medcare-tab-shell,
        .sidebar-folded.open-sidebar-folded .medcare-tab-shell,
        .sidebar-folded:not(.open-sidebar-folded) .medcare-tab-shell {
            left: 0;
            width: 100vw;
            z-index: 970;
        }

        .sidebar-open .medcare-tab-shell {
            opacity: 0;
            pointer-events: none;
            transform: translateY(-5px);
        }
    }

    @media (max-width: 767.98px) {
        :root {
            --medcare-tab-height: 64px;
            --medcare-tab-gutter: .72rem;
        }

        .medcare-tab-shell {
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .48rem;
        }

        .medcare-tab-brand,
        .medcare-tab-count {
            display: none;
        }

        .medcare-tab-strip {
            gap: .28rem;
        }

        .medcare-page-tab {
            max-width: min(66vw, 215px);
            min-height: 40px;
        }

        .medcare-tab-clear {
            width: 38px;
            padding: 0;
        }

        .medcare-tab-clear span {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        :root {
            --medcare-tab-gutter: .52rem;
        }

        .medcare-tab-shell {
            gap: .34rem;
        }

        .medcare-page-tabs {
            gap: .36rem;
        }

        .medcare-page-tab {
            max-width: min(70vw, 190px);
        }

        .medcare-page-tab-link {
            gap: .4rem;
            padding-left: .52rem;
            font-size: .7rem;
        }

        .medcare-page-tab-icon {
            width: 24px;
            height: 24px;
            flex-basis: 24px;
        }

        .medcare-page-tab-close {
            width: 26px;
            height: 26px;
            margin-right: .25rem;
        }

        .medcare-tab-clear,
        .medcare-tab-nav-button {
            width: 34px;
            height: 34px;
            min-height: 34px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .medcare-tab-shell,
        .medcare-tab-shell * {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="medcare-tab-shell is-single-tab is-at-start is-at-end" aria-label="Navigasi halaman terbuka">
    <div class="medcare-tab-brand" aria-hidden="true">
        <span class="medcare-tab-brand-icon">
            <i data-feather="layers"></i>
        </span>
        <span class="medcare-tab-brand-copy">
            <strong>Workspace</strong>
            <small>Navigasi cepat</small>
        </span>
    </div>

    <div class="medcare-tab-strip">
        <button type="button" class="medcare-tab-nav-button" id="medcarePageTabPrev" aria-label="Geser tab ke kiri"
            title="Tab sebelumnya">
            <i data-feather="chevron-left"></i>
        </button>

        <div class="medcare-tab-track">
            <nav class="medcare-page-tabs" id="medcarePageTabs" aria-label="Daftar halaman terbuka">
                <div class="medcare-page-tab is-active">
                    <a href="{{ url()->current() }}" class="medcare-page-tab-link">
                        <span class="medcare-page-tab-icon">
                            <i data-feather="file-text"></i>
                        </span>
                        <span class="medcare-page-tab-title">Halaman Ini</span>
                    </a>
                    <button type="button" class="medcare-page-tab-close" aria-label="Tutup tab Halaman Ini">
                        <i data-feather="x"></i>
                    </button>
                </div>
            </nav>
        </div>

        <button type="button" class="medcare-tab-nav-button" id="medcarePageTabNext" aria-label="Geser tab ke kanan"
            title="Tab berikutnya">
            <i data-feather="chevron-right"></i>
        </button>
    </div>

    <div class="medcare-tab-tools">
        <span class="medcare-tab-count" id="medcarePageTabCount">
            <i data-feather="copy"></i>
            <span>1 tab</span>
        </span>
        <button type="button" class="medcare-tab-clear" id="medcarePageTabClear" aria-label="Bersihkan tab lain"
            title="Bersihkan tab lain">
            <i data-feather="x-circle"></i>
            <span>Bersihkan</span>
        </button>
    </div>
</div>
