<style>
    :root {
        --medcare-tab-sidebar-width: 240px;
        --medcare-tab-sidebar-folded-width: 70px;
        --medcare-tab-height: 66px;
        --medcare-tab-gutter: 1.25rem;
    }

    .medcare-tab-shell {
        position: fixed;
        top: 60px;
        right: 0;
        left: var(--medcare-tab-sidebar-width);
        z-index: 990;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: .8rem;
        min-width: 0;
        min-height: var(--medcare-tab-height);
        padding: .62rem var(--medcare-tab-gutter);
        border-bottom: 1px solid rgba(148, 163, 184, .22);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .92)),
            linear-gradient(90deg, rgba(20, 184, 166, .08), rgba(79, 70, 229, .06), rgba(245, 158, 11, .04));
        box-shadow: 0 18px 34px rgba(15, 23, 42, .075);
        -webkit-backdrop-filter: blur(16px);
        backdrop-filter: blur(16px);
        transition: left .12s ease, padding .16s ease, box-shadow .2s ease;
    }

    .main-wrapper .page-wrapper .page-content {
        margin-top: calc(60px + var(--medcare-tab-height));
    }

    .sidebar-folded .medcare-tab-shell {
        left: var(--medcare-tab-sidebar-folded-width);
    }

    .open-sidebar-folded .medcare-tab-shell {
        left: var(--medcare-tab-sidebar-width);
    }

    .medcare-tab-brand {
        display: inline-flex;
        align-items: center;
        gap: .58rem;
        min-width: 0;
        padding-right: .25rem;
        color: #0f766e;
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .medcare-tab-brand-icon {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .72);
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, #0f766e, #2563eb);
        box-shadow: 0 10px 22px rgba(15, 118, 110, .22);
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
    }

    .medcare-tab-track::before,
    .medcare-tab-track::after {
        position: absolute;
        top: 0;
        bottom: 0;
        z-index: 2;
        width: 28px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .18s ease;
        content: "";
    }

    .medcare-tab-track::before {
        left: 0;
        background: linear-gradient(90deg, rgba(248, 250, 252, .96), rgba(248, 250, 252, 0));
    }

    .medcare-tab-track::after {
        right: 0;
        background: linear-gradient(270deg, rgba(248, 250, 252, .96), rgba(248, 250, 252, 0));
    }

    .medcare-tab-shell.is-overflowing:not(.is-at-start) .medcare-tab-track::before,
    .medcare-tab-shell.is-overflowing:not(.is-at-end) .medcare-tab-track::after {
        opacity: 1;
    }

    .medcare-page-tabs {
        display: flex;
        align-items: center;
        gap: .48rem;
        min-width: 0;
        padding: .14rem .08rem .22rem;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        scroll-padding-inline: 2.2rem;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }

    .medcare-page-tabs::-webkit-scrollbar {
        display: none;
    }

    .medcare-page-tab {
        position: relative;
        display: inline-grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        flex: 0 0 auto;
        min-width: 0;
        max-width: clamp(165px, 18vw, 250px);
        border: 1px solid rgba(203, 213, 225, .9);
        border-radius: 8px;
        color: #475569;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .96));
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        scroll-snap-align: start;
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease, background .16s ease;
    }

    .medcare-page-tab::after {
        position: absolute;
        right: 12px;
        bottom: -1px;
        left: 12px;
        height: 2px;
        border-radius: 999px;
        background: transparent;
        content: "";
        transition: background .16s ease, box-shadow .16s ease;
    }

    .medcare-page-tab:hover {
        border-color: rgba(15, 118, 110, .32);
        box-shadow: 0 12px 26px rgba(15, 23, 42, .09);
        transform: translateY(-1px);
    }

    .medcare-page-tab.is-active {
        color: #0f172a;
        border-color: rgba(15, 118, 110, .42);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(236, 253, 245, .92)),
            linear-gradient(90deg, rgba(20, 184, 166, .12), rgba(59, 130, 246, .08));
        box-shadow: 0 14px 30px rgba(15, 118, 110, .14);
    }

    .medcare-page-tab.is-active::after {
        background: linear-gradient(90deg, #0f766e, #2563eb, #f59e0b);
        box-shadow: 0 0 14px rgba(37, 99, 235, .28);
    }

    .medcare-page-tab-link {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        min-width: 0;
        padding: .48rem .34rem .48rem .58rem;
        color: inherit;
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .medcare-page-tab-link:hover,
    .medcare-page-tab-link:focus {
        color: inherit;
        text-decoration: none;
    }

    .medcare-page-tab-icon {
        display: inline-grid;
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        place-items: center;
        border: 1px solid rgba(20, 184, 166, .12);
        border-radius: 7px;
        color: #0f766e;
        background: rgba(20, 184, 166, .09);
        font-size: .82rem;
    }

    .medcare-page-tab.is-active .medcare-page-tab-icon {
        color: #2563eb;
        border-color: rgba(37, 99, 235, .14);
        background: rgba(37, 99, 235, .09);
    }

    .medcare-page-tab-title {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .medcare-page-tab-close {
        display: inline-grid;
        width: 26px;
        height: 26px;
        margin-right: .34rem;
        place-items: center;
        border: 0;
        border-radius: 999px;
        color: #64748b;
        background: transparent;
        cursor: pointer;
        line-height: 1;
        opacity: .72;
        transition: color .15s ease, background .15s ease, opacity .15s ease, transform .15s ease;
    }

    .medcare-page-tab-close:hover,
    .medcare-page-tab-close:focus {
        color: #dc2626;
        background: rgba(220, 38, 38, .08);
        opacity: 1;
        transform: scale(1.04);
    }

    .medcare-tab-shell.is-single-tab .medcare-page-tab-close {
        display: none;
        pointer-events: none;
    }

    .medcare-tab-tools {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        min-width: 0;
        white-space: nowrap;
    }

    .medcare-tab-count {
        display: inline-flex;
        align-items: center;
        gap: .36rem;
        border: 1px solid rgba(203, 213, 225, .7);
        border-radius: 8px;
        padding: .42rem .66rem;
        color: #475569;
        background: rgba(255, 255, 255, .76);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .045);
        font-size: .76rem;
        font-weight: 800;
    }

    .medcare-tab-clear,
    .medcare-tab-nav-button {
        display: inline-grid;
        place-items: center;
        border: 1px solid rgba(203, 213, 225, .86);
        border-radius: 8px;
        color: #475569;
        background: rgba(255, 255, 255, .82);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .045);
        transition: color .15s ease, border-color .15s ease, background .15s ease, box-shadow .15s ease,
            transform .15s ease;
    }

    .medcare-tab-clear {
        grid-auto-flow: column;
        gap: .36rem;
        padding: .42rem .68rem;
        font-size: .76rem;
        font-weight: 800;
    }

    .medcare-tab-nav-button {
        width: 34px;
        height: 34px;
    }

    .medcare-tab-clear:hover,
    .medcare-tab-nav-button:hover:not(:disabled) {
        color: #0f766e;
        border-color: rgba(15, 118, 110, .32);
        background: #fff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
        transform: translateY(-1px);
    }

    .medcare-tab-clear:hover {
        color: #dc2626;
        border-color: rgba(220, 38, 38, .25);
        background: #fff7f7;
    }

    .medcare-tab-clear:disabled,
    .medcare-tab-nav-button:disabled {
        cursor: not-allowed;
        box-shadow: none;
        opacity: .42;
        transform: none;
    }

    .medcare-tab-shell:not(.is-overflowing) .medcare-tab-nav-button {
        opacity: 0;
        pointer-events: none;
    }

    @media (max-width: 991.98px) {
        .medcare-tab-shell {
            left: 0;
            z-index: 970;
        }

        .sidebar-open .medcare-tab-shell {
            opacity: 0;
            pointer-events: none;
        }
    }

    @media (max-width: 767.98px) {
        :root {
            --medcare-tab-height: 60px;
            --medcare-tab-gutter: .85rem;
        }

        .medcare-tab-shell {
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .55rem;
        }

        .medcare-tab-brand,
        .medcare-tab-count {
            display: none;
        }

        .medcare-tab-strip {
            gap: .28rem;
        }

        .medcare-page-tab {
            max-width: min(68vw, 220px);
        }

        .medcare-page-tab-link {
            padding-left: .52rem;
        }
    }

    @media (max-width: 575.98px) {
        .medcare-page-tabs {
            gap: .4rem;
        }

        .medcare-page-tab {
            max-width: min(72vw, 196px);
        }

        .medcare-page-tab-icon {
            width: 22px;
            height: 22px;
            flex-basis: 22px;
        }

        .medcare-page-tab-close,
        .medcare-tab-clear,
        .medcare-tab-nav-button {
            width: 36px;
            height: 36px;
        }

        .medcare-page-tab-close {
            margin-right: .28rem;
        }

        .medcare-tab-clear {
            justify-content: center;
            padding: 0;
        }

        .medcare-tab-clear span {
            display: none;
        }
    }
</style>

<div class="medcare-tab-shell is-single-tab is-at-start is-at-end" aria-label="Navigasi halaman terbuka">
    <div class="medcare-tab-brand">
        <span class="medcare-tab-brand-icon">
            <i data-feather="layers"></i>
        </span>
        Workspace
    </div>

    <div class="medcare-tab-strip">
        <button type="button" class="medcare-tab-nav-button" id="medcarePageTabPrev" aria-label="Geser tab ke kiri">
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

        <button type="button" class="medcare-tab-nav-button" id="medcarePageTabNext" aria-label="Geser tab ke kanan">
            <i data-feather="chevron-right"></i>
        </button>
    </div>

    <div class="medcare-tab-tools">
        <span class="medcare-tab-count" id="medcarePageTabCount">
            <i data-feather="copy"></i>
            1 tab
        </span>
        <button type="button" class="medcare-tab-clear" id="medcarePageTabClear">
            <i data-feather="x-circle"></i>
            <span>Bersihkan</span>
        </button>
    </div>
</div>
