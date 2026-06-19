<style>
    .medcare-tab-shell {
        position: fixed;
        top: 60px;
        right: 0;
        left: 240px;
        z-index: 1025;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: .8rem;
        min-height: 58px;
        padding: .5rem 1.5rem;
        border-bottom: 1px solid #dbe7f5;
        background:
            linear-gradient(90deg, rgba(15, 118, 110, .08), transparent 28%),
            rgba(248, 250, 252, .97);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .055);
        backdrop-filter: blur(10px);
        transition: left .1s ease;
    }

    .main-wrapper .page-wrapper .page-content {
        margin-top: 118px;
    }

    .sidebar-folded .medcare-tab-shell {
        left: 70px;
    }

    .medcare-tab-brand {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        min-width: 0;
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
        border-radius: 10px;
        color: #fff;
        background: linear-gradient(135deg, #0f766e, #2563eb);
        box-shadow: 0 8px 16px rgba(15, 118, 110, .2);
    }

    .medcare-page-tabs {
        display: flex;
        align-items: center;
        gap: .48rem;
        min-width: 0;
        overflow-x: auto;
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
        gap: .52rem;
        min-width: 0;
        max-width: 230px;
        border: 1px solid #dbe4ef;
        border-radius: 999px;
        padding: .45rem .48rem .45rem .62rem;
        color: #64748b;
        background: #fff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: color .15s ease, border-color .15s ease, background .15s ease, box-shadow .15s ease,
            transform .15s ease;
    }

    .medcare-page-tab::before {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 999px;
        background: #cbd5e1;
        content: "";
    }

    .medcare-page-tab:hover {
        color: #0f766e;
        border-color: rgba(15, 118, 110, .35);
        box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .medcare-page-tab.is-active {
        color: #fff;
        border-color: #0f766e;
        background: linear-gradient(135deg, #0f766e, #2563eb);
        box-shadow: 0 10px 22px rgba(15, 118, 110, .2);
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
        border-radius: 8px;
        color: #0f766e;
        background: #e8f8f5;
        font-size: .82rem;
    }

    .medcare-page-tab.is-active .medcare-page-tab-icon {
        color: #fff;
        background: rgba(255, 255, 255, .18);
    }

    .medcare-page-tab-title {
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
        gap: .5rem;
        white-space: nowrap;
    }

    .medcare-tab-count {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
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
        border-radius: 999px;
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
        }
    }

    @media (max-width: 767.98px) {
        .medcare-tab-shell {
            grid-template-columns: 1fr auto;
            padding: .5rem .85rem;
        }

        .medcare-tab-brand {
            display: none;
        }

        .medcare-page-tab {
            max-width: 170px;
        }

        .medcare-tab-count {
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
            Bersihkan
        </button>
    </div>
</div>
