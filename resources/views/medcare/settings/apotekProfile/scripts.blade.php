<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('apotekProfileForm');
        if (!form) return;

        const branchSelector = document.getElementById('profileBranchSelector');
        const initialBranch = branchSelector?.value;
        let formDirty = false;

        form.addEventListener('input', function (event) {
            if (event.target !== branchSelector) formDirty = true;
        });

        branchSelector?.addEventListener('change', function () {
            if (formDirty && !window.confirm('Perubahan yang belum disimpan akan hilang. Lanjut pindah cabang?')) {
                this.value = initialBranch;
                return;
            }

            const url = new URL(@json(route('settings.apotek-profile.index')), window.location.origin);
            url.searchParams.set('branch_id', this.value);
            window.location.assign(url.toString());
        });

        const logoInput = document.getElementById('logoInput');
        const logoImage = document.getElementById('apotekLogoImage');
        const logoPlaceholder = document.getElementById('apotekLogoPlaceholder');
        const removeLogoInput = document.getElementById('removeLogoInput');
        const removeLogoButton = document.getElementById('removeLogoButton');

        logoInput?.addEventListener('change', function () {
            const file = this.files?.[0];
            if (!file) return;

            logoImage.src = URL.createObjectURL(file);
            logoImage.hidden = false;
            logoPlaceholder.hidden = true;
            removeLogoInput.value = '0';
            if (removeLogoButton) removeLogoButton.innerHTML = '<i data-feather="trash-2"></i> Hapus logo saat disimpan';
            window.feather?.replace();
        });

        removeLogoButton?.addEventListener('click', function () {
            const markedForRemoval = removeLogoInput.value === '1';
            removeLogoInput.value = markedForRemoval ? '0' : '1';
            logoInput.value = '';
            logoImage.hidden = !markedForRemoval;
            logoPlaceholder.hidden = markedForRemoval;
            this.innerHTML = markedForRemoval
                ? '<i data-feather="trash-2"></i> Hapus logo saat disimpan'
                : '<i data-feather="rotate-ccw"></i> Batalkan hapus logo';
            window.feather?.replace();
        });

        document.querySelectorAll('.apotek-day-toggle input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const row = this.closest('.apotek-hours-row');
                row.classList.toggle('is-open', this.checked);
                row.querySelector('.apotek-day-status').textContent = this.checked ? 'Buka' : 'Tutup';
            });
        });

        const footer = document.getElementById('receipt_footer');
        const footerCount = document.getElementById('receiptFooterCount');
        const updateFooterCount = () => footerCount.textContent = String(footer?.value.length || 0);
        footer?.addEventListener('input', updateFooterCount);
        updateFooterCount();

        initializeMap();

        function initializeMap() {
            const mapElement = document.getElementById('apotekLocationMap');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const status = document.getElementById('mapStatus');
            const openMapLink = document.getElementById('openMapLink');

            if (mapElement._leaflet_id) return;

            if (!window.L) {
                mapElement.innerHTML = '<div class="h-100 d-flex align-items-center justify-content-center text-muted px-4 text-center">Peta tidak dapat dimuat. Periksa koneksi internet, lalu muat ulang halaman.</div>';
                return;
            }

            const savedLat = parseFloat(mapElement.dataset.latitude);
            const savedLng = parseFloat(mapElement.dataset.longitude);
            const hasSavedPoint = Number.isFinite(savedLat) && Number.isFinite(savedLng);
            const initialCenter = hasSavedPoint ? [savedLat, savedLng] : [-2.5489, 118.0149];
            const map = L.map(mapElement, {
                zoomControl: true,
                fadeAnimation: false,
                markerZoomAnimation: false,
                zoomAnimation: true,
                zoomAnimationThreshold: 3,
                wheelDebounceTime: 45,
                wheelPxPerZoomLevel: 100,
            }).setView(initialCenter, hasSavedPoint ? 16 : 5, { animate: false });
            let marker = null;

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                maxNativeZoom: 19,
                updateWhenIdle: true,
                updateWhenZooming: false,
                keepBuffer: 6,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            if (hasSavedPoint) setPoint(savedLat, savedLng, false);

            map.on('click', function (event) {
                setPoint(event.latlng.lat, event.latlng.lng, false);
                setStatus('Titik lokasi dipilih. Koordinat sudah diperbarui.');
            });

            function setPoint(latitude, longitude, moveMap = true) {
                const lat = Number(latitude);
                const lng = Number(longitude);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                if (!marker) {
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    marker.on('dragend', function () {
                        const point = marker.getLatLng();
                        writeCoordinates(point.lat, point.lng);
                        setStatus('Pin dipindahkan. Koordinat sudah diperbarui.');
                    });
                } else {
                    marker.setLatLng([lat, lng]);
                }

                writeCoordinates(lat, lng);
                if (moveMap) map.setView([lat, lng], Math.max(map.getZoom(), 16), { animate: false });
            }

            function writeCoordinates(lat, lng) {
                latitudeInput.value = Number(lat).toFixed(7);
                longitudeInput.value = Number(lng).toFixed(7);
                openMapLink.href = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=18/${lat}/${lng}`;
                openMapLink.hidden = false;
                formDirty = true;
            }

            function setStatus(message, isError = false) {
                status.textContent = message;
                status.style.color = isError ? '#c55262' : '';
            }

            [latitudeInput, longitudeInput].forEach(function (input) {
                input.addEventListener('change', function () {
                    const lat = parseFloat(latitudeInput.value);
                    const lng = parseFloat(longitudeInput.value);
                    if (Number.isFinite(lat) && Number.isFinite(lng)) setPoint(lat, lng);
                });
            });

            document.getElementById('currentLocationButton')?.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    setStatus('Browser tidak mendukung akses lokasi.', true);
                    return;
                }

                setStatus('Mengambil lokasi perangkat...');
                navigator.geolocation.getCurrentPosition(function (position) {
                    setPoint(position.coords.latitude, position.coords.longitude);
                    setStatus('Lokasi perangkat berhasil digunakan.');
                }, function () {
                    setStatus('Lokasi tidak dapat diakses. Pastikan izin lokasi sudah diberikan.', true);
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            const searchInput = document.getElementById('mapSearchInput');
            const searchButton = document.getElementById('mapSearchButton');

            searchButton?.addEventListener('click', searchLocation);
            searchInput?.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchLocation();
                }
            });

            async function searchLocation() {
                const query = searchInput.value.trim();
                if (query.length < 3) {
                    setStatus('Masukkan minimal 3 karakter untuk mencari lokasi.', true);
                    return;
                }

                searchButton.disabled = true;
                searchButton.textContent = 'Mencari...';
                setStatus('Mencari lokasi pada peta...');

                try {
                    const params = new URLSearchParams({ format: 'jsonv2', limit: '1', countrycodes: 'id', q: query });
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?${params.toString()}`, {
                        headers: { 'Accept-Language': 'id' }
                    });
                    if (!response.ok) throw new Error('Pencarian lokasi gagal.');

                    const results = await response.json();
                    if (!results.length) {
                        setStatus('Lokasi tidak ditemukan. Coba kata kunci yang lebih lengkap.', true);
                        return;
                    }

                    setPoint(parseFloat(results[0].lat), parseFloat(results[0].lon));
                    setStatus(`Lokasi ditemukan: ${results[0].display_name}`);
                } catch (error) {
                    setStatus('Pencarian lokasi sedang tidak tersedia. Anda tetap dapat memilih titik langsung pada peta.', true);
                } finally {
                    searchButton.disabled = false;
                    searchButton.textContent = 'Cari lokasi';
                }
            }

            let resizeFrame = null;
            let mapWidth = mapElement.clientWidth;
            let mapHeight = mapElement.clientHeight;

            const refreshMapSize = function () {
                if (resizeFrame) window.cancelAnimationFrame(resizeFrame);

                resizeFrame = window.requestAnimationFrame(function () {
                    const nextWidth = mapElement.clientWidth;
                    const nextHeight = mapElement.clientHeight;

                    if (nextWidth === mapWidth && nextHeight === mapHeight) return;

                    mapWidth = nextWidth;
                    mapHeight = nextHeight;
                    map.invalidateSize({ pan: false, animate: false });
                });
            };

            if ('ResizeObserver' in window) {
                const resizeObserver = new ResizeObserver(refreshMapSize);
                resizeObserver.observe(mapElement);
            } else {
                window.addEventListener('resize', refreshMapSize, { passive: true });
            }

            window.requestAnimationFrame(function () {
                map.invalidateSize({ pan: false, animate: false });
            });
        }
    });
</script>
