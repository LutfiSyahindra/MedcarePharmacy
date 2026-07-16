<!-- core:js -->
<script src="{{ asset("assets/vendors/core/core.js") }}"></script>
<!-- endinject -->

<!-- Plugin js for this page -->
<script src="{{ asset("assets/vendors/flatpickr/flatpickr.min.js") }}"></script>
<script src="{{ asset("assets/vendors/apexcharts/apexcharts.min.js") }}"></script>
<!-- End plugin js for this page -->

<!-- inject:js -->
<script src="{{ asset("assets/vendors/feather-icons/feather.min.js") }}"></script>
<script src="{{ asset("assets/js/template.js") }}"></script>
<!-- endinject -->

<!-- Custom js for this page -->
<script src="{{ asset("assets/js/dashboard-light.js") }}"></script>
<!-- End custom js for this page -->

<script>
    (function medcarePageTabs() {
        const storageKey = 'medcare_open_page_tabs_v1';
        const maxTabs = 10;
        let scrollControlsReady = false;

        function normalizeUrl(url) {
            try {
                const parsed = new URL(url, window.location.origin);
                return parsed.pathname + parsed.search;
            } catch (error) {
                return url || '/';
            }
        }

        function readTabs() {
            try {
                const tabs = JSON.parse(localStorage.getItem(storageKey)) || [];

                if (!Array.isArray(tabs)) {
                    return [];
                }

                return tabs
                    .filter(tab => tab && tab.url)
                    .map(tab => ({
                        title: String(tab.title || 'Halaman').trim() || 'Halaman',
                        url: normalizeUrl(tab.url),
                        icon: sanitizeIcon(tab.icon),
                        openedAt: Number(tab.openedAt) || Date.now()
                    }));
            } catch (error) {
                return [];
            }
        }

        function writeTabs(tabs) {
            const cleanTabs = tabs
                .filter(tab => tab && tab.url)
                .map(tab => ({
                    title: String(tab.title || 'Halaman').trim() || 'Halaman',
                    url: normalizeUrl(tab.url),
                    icon: sanitizeIcon(tab.icon),
                    openedAt: Number(tab.openedAt) || Date.now()
                }))
                .slice(-maxTabs);

            localStorage.setItem(storageKey, JSON.stringify(cleanTabs));
        }

        function getMenuTitle(url) {
            const normalizedUrl = normalizeUrl(url);
            const links = document.querySelectorAll('.sidebar-body a.nav-link[href]');

            for (const link of links) {
                const href = link.getAttribute('href');

                if (!href || href === '#' || href.startsWith('javascript:')) {
                    continue;
                }

                if (normalizeUrl(href) === normalizedUrl) {
                    return link.textContent.trim().replace(/\s+/g, ' ');
                }
            }

            return '';
        }

        function getMenuIcon(url) {
            const normalizedUrl = normalizeUrl(url);
            const links = document.querySelectorAll('.sidebar-body a.nav-link[href]');

            for (const link of links) {
                const href = link.getAttribute('href');

                if (!href || href === '#' || href.startsWith('javascript:')) {
                    continue;
                }

                if (normalizeUrl(href) !== normalizedUrl) {
                    continue;
                }

                const parentMenu = link.closest('.collapse')?.previousElementSibling;
                const icon = parentMenu?.querySelector('[data-feather]') || link.querySelector('[data-feather]');

                return icon?.getAttribute('data-feather') || 'file-text';
            }

            return 'file-text';
        }

        function getCurrentTitle() {
            const breadcrumbTitle = document.querySelector('.breadcrumb-item.active')?.textContent?.trim();
            const menuTitle = getMenuTitle(window.location.href);

            return breadcrumbTitle || menuTitle || document.title || 'Halaman';
        }

        function upsertTab(tab) {
            let tabs = readTabs().filter(item => item.url !== normalizeUrl(tab.url));
            tabs.push({
                title: tab.title,
                url: normalizeUrl(tab.url),
                icon: sanitizeIcon(tab.icon),
                openedAt: tab.openedAt || Date.now()
            });
            writeTabs(tabs);
            renderTabs();
        }

        function addCurrentPage() {
            const currentUrl = normalizeUrl(window.location.href);

            upsertTab({
                title: getCurrentTitle(),
                url: currentUrl,
                icon: getMenuIcon(currentUrl),
                openedAt: Date.now()
            });
        }

        function closeTab(url) {
            const currentUrl = normalizeUrl(window.location.href);
            const normalizedUrl = normalizeUrl(url);
            const tabs = readTabs();
            const removedIndex = tabs.findIndex(tab => tab.url === normalizedUrl);
            const nextTabs = tabs.filter(tab => tab.url !== normalizedUrl);

            if (!nextTabs.length) {
                clearTabs();
                return;
            }

            writeTabs(nextTabs);

            if (normalizedUrl === currentUrl) {
                const nextIndex = Math.max(0, Math.min(removedIndex - 1, nextTabs.length - 1));
                window.location.href = nextTabs[nextIndex].url;
                return;
            }

            renderTabs();
        }

        function renderTabs() {
            const shell = document.querySelector('.medcare-tab-shell');
            const wrapper = document.getElementById('medcarePageTabs');
            const countEl = document.getElementById('medcarePageTabCount');
            const clearButton = document.getElementById('medcarePageTabClear');

            if (!wrapper) {
                return;
            }

            const currentUrl = normalizeUrl(window.location.href);
            const tabs = readTabs();

            wrapper.innerHTML = '';
            shell?.classList.toggle('is-single-tab', tabs.length <= 1);

            if (!tabs.length) {
                if (countEl) {
                    countEl.innerHTML = '<i data-feather="copy"></i><span>0 tab</span>';
                }
                if (clearButton) {
                    clearButton.disabled = true;
                }
                replaceFeatherIcons();
                updateScrollState();
                return;
            }

            if (countEl) {
                countEl.innerHTML = `<i data-feather="copy"></i><span>${tabs.length} tab</span>`;
            }
            if (clearButton) {
                clearButton.disabled = tabs.length <= 1;
            }

            const fragment = document.createDocumentFragment();

            tabs.forEach(tab => {
                const item = buildTabElement(tab, currentUrl);

                const closeButton = item.querySelector('.medcare-page-tab-close');

                closeButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    closeTab(tab.url);
                });

                closeButton.addEventListener('keydown', function(event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    closeTab(tab.url);
                });

                fragment.appendChild(item);
            });

            wrapper.appendChild(fragment);
            replaceFeatherIcons();
            revealActiveTab();
            window.requestAnimationFrame(updateScrollState);
        }

        function buildTabElement(tab, currentUrl) {
            const isActive = tab.url === currentUrl;
            const item = document.createElement('div');
            item.className = 'medcare-page-tab' + (isActive ? ' is-active' : '');
            item.dataset.url = tab.url;
            item.setAttribute('role', 'listitem');

            if (isActive) {
                item.setAttribute('aria-current', 'page');
            }

            const link = document.createElement('a');
            link.href = tab.url;
            link.className = 'medcare-page-tab-link';
            link.title = tab.title;

            const iconWrap = document.createElement('span');
            iconWrap.className = 'medcare-page-tab-icon';

            const icon = document.createElement('i');
            icon.setAttribute('data-feather', sanitizeIcon(tab.icon));

            const title = document.createElement('span');
            title.className = 'medcare-page-tab-title';
            title.textContent = tab.title;

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'medcare-page-tab-close';
            closeButton.setAttribute('aria-label', `Tutup tab ${tab.title}`);
            closeButton.innerHTML = '<i data-feather="x"></i>';

            iconWrap.appendChild(icon);
            link.appendChild(iconWrap);
            link.appendChild(title);
            item.appendChild(link);
            item.appendChild(closeButton);

            return item;
        }

        function clearTabs() {
            const currentUrl = normalizeUrl(window.location.href);
            writeTabs([{
                title: getCurrentTitle(),
                url: currentUrl,
                icon: getMenuIcon(currentUrl),
                openedAt: Date.now()
            }]);
            renderTabs();
        }

        function updateScrollState() {
            const shell = document.querySelector('.medcare-tab-shell');
            const wrapper = document.getElementById('medcarePageTabs');
            const prevButton = document.getElementById('medcarePageTabPrev');
            const nextButton = document.getElementById('medcarePageTabNext');

            if (!wrapper) {
                return;
            }

            const maxScroll = Math.max(0, wrapper.scrollWidth - wrapper.clientWidth);
            const canScroll = maxScroll > 2;
            const isAtStart = wrapper.scrollLeft <= 2;
            const isAtEnd = wrapper.scrollLeft >= maxScroll - 2;

            shell?.classList.toggle('is-overflowing', canScroll);
            shell?.classList.toggle('is-at-start', !canScroll || isAtStart);
            shell?.classList.toggle('is-at-end', !canScroll || isAtEnd);

            if (prevButton) {
                prevButton.disabled = !canScroll || isAtStart;
            }

            if (nextButton) {
                nextButton.disabled = !canScroll || isAtEnd;
            }
        }

        function revealActiveTab() {
            const wrapper = document.getElementById('medcarePageTabs');
            const activeTab = wrapper?.querySelector('.medcare-page-tab.is-active');

            if (!activeTab) {
                return;
            }

            activeTab.scrollIntoView({
                block: 'nearest',
                inline: 'center',
                behavior: 'smooth'
            });
        }

        function scrollTabs(direction) {
            const wrapper = document.getElementById('medcarePageTabs');

            if (!wrapper) {
                return;
            }

            wrapper.scrollBy({
                left: direction * Math.max(180, wrapper.clientWidth * .68),
                behavior: 'smooth'
            });
        }

        function handleTabKeyboard(event) {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                return;
            }

            const wrapper = document.getElementById('medcarePageTabs');
            const links = Array.from(wrapper?.querySelectorAll('.medcare-page-tab-link') || []);

            if (!links.length) {
                return;
            }

            const currentLink = document.activeElement?.closest?.('.medcare-page-tab-link');
            let index = links.indexOf(currentLink);

            if (index < 0) {
                index = links.findIndex(link => link.closest('.medcare-page-tab')?.classList.contains('is-active'));
            }

            if (event.key === 'Home') {
                index = 0;
            } else if (event.key === 'End') {
                index = links.length - 1;
            } else {
                const direction = event.key === 'ArrowRight' ? 1 : -1;
                index = (Math.max(index, 0) + direction + links.length) % links.length;
            }

            event.preventDefault();
            links[index]?.focus();
            links[index]?.closest('.medcare-page-tab')?.scrollIntoView({
                block: 'nearest',
                inline: 'center',
                behavior: 'smooth'
            });
        }

        function wireScrollControls() {
            if (scrollControlsReady) {
                return;
            }

            const wrapper = document.getElementById('medcarePageTabs');
            const prevButton = document.getElementById('medcarePageTabPrev');
            const nextButton = document.getElementById('medcarePageTabNext');

            if (!wrapper) {
                return;
            }

            prevButton?.addEventListener('click', () => scrollTabs(-1));
            nextButton?.addEventListener('click', () => scrollTabs(1));

            wrapper.addEventListener('scroll', function() {
                window.requestAnimationFrame(updateScrollState);
            }, {
                passive: true
            });

            wrapper.addEventListener('wheel', function(event) {
                if (wrapper.scrollWidth <= wrapper.clientWidth || Math.abs(event.deltaX) > Math.abs(event.deltaY)) {
                    return;
                }

                event.preventDefault();
                wrapper.scrollLeft += event.deltaY;
                updateScrollState();
            }, {
                passive: false
            });

            wrapper.addEventListener('keydown', handleTabKeyboard);
            window.addEventListener('resize', () => window.requestAnimationFrame(updateScrollState));

            scrollControlsReady = true;
        }

        function sanitizeIcon(icon) {
            const value = String(icon || 'file-text').trim();
            return /^[a-z0-9-]+$/i.test(value) ? value : 'file-text';
        }

        function replaceFeatherIcons() {
            if (window.feather) {
                feather.replace();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            wireScrollControls();
            addCurrentPage();

            document.querySelectorAll('.sidebar-body a.nav-link[href]').forEach(link => {
                link.addEventListener('click', function() {
                    const href = this.getAttribute('href');

                    if (!href || href === '#' || href.startsWith('javascript:') || this.dataset.bsToggle) {
                        return;
                    }

                    upsertTab({
                        title: this.textContent.trim().replace(/\s+/g, ' ') || 'Halaman',
                        url: normalizeUrl(href),
                        icon: getMenuIcon(href),
                        openedAt: Date.now()
                    });
                });
            });

            document.getElementById('medcarePageTabClear')?.addEventListener('click', clearTabs);
            window.requestAnimationFrame(updateScrollState);
        });
    })();
</script>

<script>
    (function notificationSoundSetup() {

        let audio = new Audio("{{ asset("sound/mixkit-bell-notification-933.wav") }}");
        audio.preload = 'auto';

        // 🔓 unlock audio setelah interaksi user pertama
        let unlocked = false;

        function unlockAudio() {
            if (unlocked) return;

            audio.play().then(() => {
                audio.pause();
                audio.currentTime = 0;
                unlocked = true;
                console.log('🔊 Audio unlocked');
            }).catch(() => {});

            document.removeEventListener('click', unlockAudio);
        }

        document.addEventListener('click', unlockAudio);

        // 🔔 expose global function
        window.playNotifSound = function() {
            if (!unlocked) return;

            audio.currentTime = 0;
            audio.play().catch(() => {});
        };

    })();
</script>

<script>
    (function notifNavbar() {

        if (window.__navbarNotifReady) return;
        window.__navbarNotifReady = true;

        const listEl = document.getElementById('navbar-notif-list');
        const badgeEl = document.getElementById('navbar-notif-badge');

        function loadNotif() {
            fetch("{{ route("notifikasi.latest") }}")
                .then(res => res.json())
                .then(data => {
                    listEl.innerHTML = '';

                    if (!data.length) {
                        listEl.innerHTML = `
                        <div class="text-center text-muted py-3">
                            Tidak ada notifikasi
                        </div>`;
                        badgeEl.innerHTML = '';
                        return;
                    }

                    badgeEl.innerHTML = `
                    <span class="badge bg-danger rounded-pill">${data.length}</span>
                `;

                    data.forEach(n => {
                        const actionPill = n.can_action ?
                            '<span class="badge bg-warning text-dark mt-1">Perlu aksi</span>' :
                            `<span class="badge bg-light text-muted mt-1">${escapeNotifHtml(n.status_label || '')}</span>`;
                        const itemsLine = n.items_summary ?
                            `<small class="text-muted d-block">Isi: ${escapeNotifHtml(n.items_summary)}</small>` :
                            '';

                        listEl.innerHTML += `
                        <a href="javascript:;" 
                           class="dropdown-item d-flex align-items-start py-2"
                           onclick="readNotif('${n.id}', '${n.url}')">
                            <div class="me-2 text-primary">
                                <i data-feather="bell"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 fw-semibold">${escapeNotifHtml(n.document_no || n.title)}</p>
                                <small class="text-muted">${escapeNotifHtml(n.module_label || 'Notifikasi')} · ${escapeNotifHtml(n.message || '')}</small><br>
                                ${itemsLine}
                                <small class="text-muted">${escapeNotifHtml(n.time || '')}</small><br>
                                ${actionPill}
                            </div>
                        </a>`;
                    });

                    feather.replace();
                });
        }

        function escapeNotifHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        window.loadNotif = loadNotif;

        // initial load
        loadNotif();

        // realtime update
        (function bindNavbarEcho() {
            if (!window.Echo) return setTimeout(bindNavbarEcho, 100);

            Echo.private(`App.Models.User.{{ auth()->id() }}`)
                .listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', () => {
                    loadNotif();
                });
        })();

        window.readNotif = function(id, url) {
            fetch("{{ route("notifikasi.readNotifikasi") }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    id
                })
            }).then(() => {
                window.location.href = url;
            });
        };

    })();
</script>

<script>
    (function waitForEchoGlobal() {

        if (!window.Echo) {
            return setTimeout(waitForEchoGlobal, 100);
        }

        // 🔒 cegah listener dobel
        if (window.__notifListenerRegistered) return;
        window.__notifListenerRegistered = true;

        console.log('🔔 Echo GLOBAL siap');

        Echo.private(`App.Models.User.{{ auth()->id() }}`)
            .listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (e) => {

                console.log('🔔 GLOBAL NOTIF', e);

                /* =====================
                 * 🔊 SOUND
                 * ===================== */
                if (e.sound_enabled !== false && window.playNotifSound) {
                    window.playNotifSound();
                }

                /* =====================
                 * 🔔 BADGE SIDEBAR
                 * ===================== */
                const container = document.getElementById('notif-badge-container');
                if (container) {
                    let badge = document.getElementById('notif-count');
                    if (badge) {
                        badge.innerText = parseInt(badge.innerText || '0') + 1;
                    } else {
                        const span = document.createElement('span');
                        span.id = 'notif-count';
                        span.className = 'badge bg-danger ms-1';
                        span.innerText = '1';
                        container.appendChild(span);
                    }
                }

                /* =====================
                 * 🔔 DROPDOWN NAVBAR
                 * ===================== */
                if (typeof window.loadNotif === 'function') {
                    window.loadNotif();
                }

                /* =====================
                 * 📊 DATATABLE (HALAMAN NOTIF)
                 * ===================== */
                if (window.NotifikasiTable) {
                    window.NotifikasiTable.ajax.reload(null, false);
                }
                const summaryUnread = document.getElementById('notificationUnreadSummary');
                if (summaryUnread) {
                    const current = parseInt((summaryUnread.textContent || '0').replace(/\D/g, '')) || 0;
                    summaryUnread.textContent = (current + 1).toLocaleString('id-ID');
                }

                /* =====================
                 * 🔔 TOAST NOTIFICATION
                 * ===================== */
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: e.title ?? 'Notifikasi',
                        text: e.message ?? '',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                }

            });

    })();
</script>

<script>
    window.markAllNotifRead = function() {

        fetch("{{ route("notifikasi.markAllRead") }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            })
            .then(() => {

                /* =====================
                 * 🔔 HAPUS BADGE SIDEBAR
                 * ===================== */
                document.getElementById('notif-count')?.remove();

                /* =====================
                 * 🔔 HAPUS BADGE NAVBAR
                 * ===================== */
                const navbarBadge = document.querySelector('#navbar-notif-badge span');
                if (navbarBadge) navbarBadge.remove();

                /* =====================
                 * 🔔 KOSONGKAN DROPDOWN
                 * ===================== */
                const listEl = document.getElementById('navbar-notif-list');
                if (listEl) {
                    listEl.innerHTML = `
                <div class="text-center text-muted py-3">
                    Tidak ada notifikasi
                </div>`;
                }

                /* =====================
                 * 📊 RELOAD DATATABLE
                 * ===================== */
                if (window.NotifikasiTable) {
                    window.NotifikasiTable.ajax.reload(null, false);
                }
                const summaryUnread = document.getElementById('notificationUnreadSummary');
                if (summaryUnread) {
                    summaryUnread.textContent = '0';
                }
            });
    }
</script>
