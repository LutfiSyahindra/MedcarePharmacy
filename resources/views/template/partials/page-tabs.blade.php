<style>
    :root {
        --medcare-tab-sidebar-width: 240px;
        --medcare-tab-sidebar-folded-width: 70px;
        --medcare-tab-height: 62px;
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
        gap: .75rem;
        min-width: 0;
        min-height: var(--medcare-tab-height);
        padding: .58rem var(--medcare-tab-gutter);
        border-bottom: 1px solid #dbe7f5;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .94)),
            linear-gradient(90deg, rgba(15, 118, 110, .07), rgba(37, 99, 235, .05));
        box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
        backdrop-filter: blur(14px);
        transition: left .1s ease, width .1s ease, padding .16s ease;
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
        gap: .55rem;
        min-width: 0;
        padding-right: .4rem;
        color: #0f766e;
        font-size: .76rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .medcare-tab-brand-icon {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, #0f766e, #1d4ed8);
        box-shadow: 0 8px 18px rgba(15, 118, 110, .2);
    }

    .medcare-page-tabs {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-width: 0;
        padding: .08rem .15rem .18rem;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        scroll-padding-inline: .5rem;
        scroll-snap-type: x proximity;
        scrollbar-width: thin;
    }

    .medcare-page-tabs::-webkit-scrollbar {
        height: 6px;
    }

    .medcare-page-tabs::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #cbd5e1;
    }

    .medcare-page-tab {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: .48rem;
        min-width: 0;
        max-width: clamp(150px, 18vw, 230px);
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        padding: .48rem .5rem .48rem .62rem;
        color: #475569;
        background: #fff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .045);
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        scroll-snap-align: start;
        transition: color .15s ease, border-color .15s ease, background .15s ease, box-shadow .15s ease,
            transform .15s ease;
    }

    .medcare-page-tab::before {
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        border-radius: 999px;
        background: #cbd5e1;
        content: "";
    }

    .medcare-page-tab:hover {
        color: #0f766e;
        border-color: rgba(15, 118, 110, .35);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .medcare-page-tab.is-active {
        color: #fff;
        border-color: #0f766e;
        background: linear-gradient(135deg, #0f766e, #1d4ed8);
        box-shadow: 0 11px 24px rgba(15, 118, 110, .22);
    }

    .medcare-page-tab.is-active::before {
        background: #86efac;
        box-shadow: 0 0 0 4px rgba(134, 239, 172, .18);
    }

    .medcare-page-tab-icon {
        display: inline-grid;
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        place-items: center;
        border-radius: 7px;
        color: #0f766e;
        background: #e8f8f5;
        font-size: .82rem;
    }

    .medcare-page-tab.is-active .medcare-page-tab-icon {
        color: #fff;
        background: rgba(255, 255, 255, .18);
    }

    .medcare-page-tab-title {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .medcare-page-tab-close {
        display: inline-grid;
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        place-items: center;
        border: 0;
        border-radius: 999px;
        color: inherit;
        background: transparent;
        cursor: pointer;
        line-height: 1;
        opacity: .75;
    }

    .medcare-page-tab-close:hover {
        background: rgba(15, 23, 42, .08);
        opacity: 1;
    }

    .medcare-page-tab.is-active .medcare-page-tab-close:hover {
        background: rgba(255, 255, 255, .18);
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
        gap: .35rem;
        border-radius: 8px;
        padding: .4rem .65rem;
        color: #64748b;
        background: #fff;
        font-size: .76rem;
        font-weight: 800;
    }

    .medcare-tab-clear {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid #dbe4ef;
        border-radius: 8px;
        padding: .4rem .68rem;
        color: #64748b;
        background: #fff;
        font-size: .76rem;
        font-weight: 800;
        transition: color .15s ease, border-color .15s ease, background .15s ease;
    }

    .medcare-tab-clear:hover {
        color: #dc2626;
        border-color: rgba(220, 38, 38, .25);
        background: #fff1f2;
    }

    .medcare-tab-clear:disabled {
        cursor: not-allowed;
        opacity: .48;
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
            --medcare-tab-height: 58px;
            --medcare-tab-gutter: .85rem;
        }

        .medcare-tab-shell {
            grid-template-columns: 1fr auto;
            gap: .5rem;
        }

        .medcare-tab-brand {
            display: none;
        }

        .medcare-page-tab {
            max-width: min(68vw, 210px);
        }

        .medcare-tab-count {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        .medcare-tab-shell {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .medcare-page-tabs {
            gap: .4rem;
        }

        .medcare-page-tab {
            max-width: min(72vw, 190px);
            padding: .44rem .46rem .44rem .56rem;
        }

        .medcare-page-tab-icon {
            width: 20px;
            height: 20px;
            flex-basis: 20px;
        }

        .medcare-tab-clear {
            width: 38px;
            height: 38px;
            justify-content: center;
            padding: 0;
        }

        .medcare-tab-clear span {
            display: none;
        }
    }
</style>

<div class="medcare-tab-shell" aria-label="Navigasi halaman terbuka">
    <div class="medcare-tab-brand">
        <span class="medcare-tab-brand-icon">
            <i data-feather="layers"></i>
        </span>
        Workspace
    </div>

    <nav class="medcare-page-tabs" id="medcarePageTabs" aria-label="Daftar halaman terbuka">
        <a href="{{ url()->current() }}" class="medcare-page-tab is-active">
            <span class="medcare-page-tab-icon">
                <i data-feather="file-text"></i>
            </span>
            <span class="medcare-page-tab-title">Halaman Ini</span>
        </a>
    </nav>

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
