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

        if (!window.Echo) return setTimeout(notifNavbar, 100);
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
                        listEl.innerHTML += `
                        <a href="javascript:;" 
                           class="dropdown-item d-flex align-items-start py-2"
                           onclick="readNotif('${n.id}', '${n.url}')">
                            <div class="me-2 text-primary">
                                <i data-feather="bell"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-semibold">${n.title}</p>
                                <small class="text-muted">${n.message}</small><br>
                                <small class="text-muted">${n.time}</small>
                            </div>
                        </a>`;
                    });

                    feather.replace();
                });
        }

        // initial load
        loadNotif();

        // realtime update
        Echo.private(`App.Models.User.{{ auth()->id() }}`)
            .listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', () => {
                loadNotif();
            });

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
                if (window.playNotifSound) {
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
            });
    }
</script>
