        const posThermalPrinter = (() => {
            const storageKey = 'medcare.pos.thermal-printer.v1';
            const bluetoothServiceUuids = [
                '0000ff00-0000-1000-8000-00805f9b34fb',
                '0000ffe0-0000-1000-8000-00805f9b34fb',
                '0000ae30-0000-1000-8000-00805f9b34fb',
                '000018f0-0000-1000-8000-00805f9b34fb',
                '0000fff0-0000-1000-8000-00805f9b34fb',
                '6e400001-b5a3-f393-e0a9-e50e24dcca9e',
                '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
            ];
            const bluetoothConnectTimeoutMs = 15000;
            const bluetoothDiscoveryTimeoutMs = 12000;
            const serialOpenTimeoutMs = 12000;
            const disconnectTimeoutMs = 3000;
            const fallbackConfig = {
                formatVersion: 3,
                mode: null,
                name: '',
                deviceId: '',
                columns: 48,
                autoCut: false
            };
            let config = loadConfig();
            let bluetoothDevice = null;
            let bluetoothCharacteristic = null;
            let serialPort = null;
            let selectingDevice = false;
            let connecting = false;
            let returningToReceipt = false;
            let restoreAttempted = false;
            let activeDirectPrintPromise = null;

            function loadConfig() {
                try {
                    const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');

                    return {
                        ...fallbackConfig,
                        ...saved,
                        formatVersion: fallbackConfig.formatVersion,
                        columns: Number(saved.columns) === 42 ? 42 : 48,
                        // Reset pilihan cutter setiap kali format transport berubah.
                        // Sejumlah Putian tanpa cutter salah membaca perintah feed-and-cut
                        // sebagai feed berkelanjutan sampai kertas habis.
                        autoCut: Number(saved.formatVersion) >= fallbackConfig.formatVersion
                            && saved.autoCut === true
                    };
                } catch (error) {
                    return { ...fallbackConfig };
                }
            }

            function saveConfig(changes = {}) {
                config = { ...config, ...changes };
                try {
                    localStorage.setItem(storageKey, JSON.stringify(config));
                } catch (error) {
                    // Koneksi tetap dapat dipakai walau penyimpanan browser dinonaktifkan.
                }
                syncOptions();
                renderState();
            }

            function printerModal() {
                const element = document.getElementById('posPrinterModal');

                return element && window.bootstrap?.Modal
                    ? bootstrap.Modal.getOrCreateInstance(element)
                    : null;
            }

            function isBluetoothReady() {
                return Boolean(bluetoothDevice?.gatt?.connected && bluetoothCharacteristic);
            }

            function isSerialReady() {
                return Boolean(serialPort?.writable);
            }

            function isDirectReady() {
                return isBluetoothReady() || isSerialReady();
            }

            function connectionName() {
                return config.name || (config.mode === 'serial' ? 'Putian POS (Bluetooth COM)' : 'Putian POS 80 mm');
            }

            function syncOptions() {
                $('#posPrinterColumns').val(String(config.columns));
                $('#posPrinterAutoCut').prop('checked', config.autoCut === true);
            }

            function renderState(override = null) {
                let state = override?.state;
                let title = override?.title;
                let copy = override?.copy;
                let badge = override?.badge;
                let icon = override?.icon;

                if (!override) {
                    if (connecting) {
                        state = 'connecting';
                        title = 'Menghubungkan printer...';
                        copy = 'Tunggu hingga printer selesai menyiapkan koneksi.';
                        badge = 'PROSES';
                        icon = 'mdi-loading mdi-spin';
                    } else if (isDirectReady()) {
                        state = 'connected';
                        title = connectionName();
                        copy = config.mode === 'serial'
                            ? 'Terhubung melalui Bluetooth COM - raster ESC/POS 80 mm'
                            : 'Terhubung melalui Bluetooth langsung - raster ESC/POS 80 mm';
                        badge = 'ONLINE';
                        icon = 'mdi-printer-check';
                    } else if (config.mode === 'browser') {
                        state = 'connected';
                        title = 'Dialog cetak browser';
                        copy = 'Putian POS dipilih dari daftar printer sistem saat mencetak.';
                        badge = 'SIAP';
                        icon = 'mdi-printer-settings';
                    } else if (config.mode) {
                        state = 'idle';
                        title = `${connectionName()} tersimpan`;
                        copy = 'Printer sedang offline. Hubungkan kembali untuk mencetak langsung.';
                        badge = 'OFFLINE';
                        icon = 'mdi-printer-alert';
                    } else {
                        state = 'idle';
                        title = 'Belum ada printer terhubung';
                        copy = 'Pilih cara koneksi yang sesuai dengan Bluetooth printer.';
                        badge = 'OFFLINE';
                        icon = 'mdi-printer-alert';
                    }
                }

                const directReady = isDirectReady();
                $('#posPrinterConnectionState').attr('data-state', state);
                $('#posPrinterConnectionTitle').text(title);
                $('#posPrinterConnectionCopy').text(copy);
                $('#posPrinterConnectionBadge').text(badge);
                $('.pos-printer-state-icon').html(`<i class="mdi ${icon}"></i>`);
                $('#posPrinterButton')
                    .toggleClass('is-connected', directReady)
                    .toggleClass('is-connecting', connecting);
                $('#posPrinterStatus').text(directReady ? connectionName() : (config.mode === 'browser' ? 'Dialog browser' : 'Belum terhubung'));
                $('#testThermalPrinterBtn').prop('disabled', !directReady || connecting);
                $('#releasePrinterBtn').toggleClass('d-none', !config.mode && !directReady);
                $('#receiptPrinterSettingsBtn')
                    .toggleClass('is-connected', directReady)
                    .find('span').text(directReady ? connectionName() : 'Atur printer');
                updatePrintButton();
            }

            function updatePrintButton() {
                const documentType = typeof activeReceiptDocument === 'undefined'
                    ? 'receipt'
                    : activeReceiptDocument;
                const isLabels = documentType === 'labels';
                const direct = isDirectReady();
                const label = isLabels
                    ? (direct ? 'Cetak etiket langsung' : 'Cetak etiket')
                    : (direct ? 'Cetak sesuai preview' : 'Cetak struk');
                const icon = direct ? 'mdi-bluetooth-connect' : 'mdi-printer-outline';

                $('#printReceiptModalBtn').find('i').attr('class', `mdi ${icon}`);
                $('#printReceiptModalBtn').find('span').text(label);
            }

            function setBusy(value) {
                connecting = value;
                $('.pos-printer-method').prop('disabled', value);
                renderState();
            }

            function connectionError(message) {
                renderState({
                    state: 'error',
                    title: 'Printer belum dapat dihubungkan',
                    copy: message,
                    badge: 'GAGAL',
                    icon: 'mdi-printer-alert'
                });
            }

            function timeoutError(message) {
                const error = new Error(message);
                error.name = 'TimeoutError';

                return error;
            }

            function withTimeout(operation, duration, message) {
                let timeoutId = null;
                const deadline = new Promise((resolve, reject) => {
                    timeoutId = window.setTimeout(() => reject(timeoutError(message)), duration);
                });

                return Promise.race([Promise.resolve(operation), deadline])
                    .finally(() => window.clearTimeout(timeoutId));
            }

            async function writableCharacteristic(server) {
                const services = await server.getPrimaryServices();

                for (const service of services) {
                    let characteristics = [];
                    try {
                        characteristics = await service.getCharacteristics();
                    } catch (error) {
                        continue;
                    }

                    const writable = characteristics.find(characteristic => (
                        characteristic.properties.writeWithoutResponse || characteristic.properties.write
                    ));

                    if (writable) return writable;
                }

                throw new Error('Layanan cetak ESC/POS tidak ditemukan pada printer ini.');
            }

            async function connectBluetoothDevice(device, remember = true) {
                bluetoothDevice = device;
                bluetoothDevice.addEventListener('gattserverdisconnected', handleBluetoothDisconnect, { once: true });
                let connectOperation = null;

                try {
                    if (!device.gatt) {
                        throw new Error('Perangkat ini bukan printer Bluetooth BLE yang dapat diakses browser.');
                    }

                    if (!device.gatt.connected) {
                        connectOperation = device.gatt.connect();
                    }
                    const server = device.gatt.connected
                        ? device.gatt
                        : await withTimeout(
                            connectOperation,
                            bluetoothConnectTimeoutMs,
                            'Printer tidak merespons dalam 15 detik. Pastikan printer mendukung Bluetooth BLE, bukan hanya Bluetooth Classic/SPP.'
                        );
                    bluetoothCharacteristic = await withTimeout(
                        writableCharacteristic(server),
                        bluetoothDiscoveryTimeoutMs,
                        'Kanal cetak BLE tidak ditemukan dalam 12 detik. Printer kemungkinan memakai Bluetooth Classic/SPP.'
                    );

                    if (remember) {
                        saveConfig({
                            mode: 'bluetooth',
                            name: device.name || 'Putian POS 80 mm',
                            deviceId: device.id || ''
                        });
                    } else {
                        renderState();
                    }
                } catch (error) {
                    bluetoothCharacteristic = null;
                    if (device.gatt?.connected) device.gatt.disconnect();
                    if (bluetoothDevice === device) bluetoothDevice = null;

                    // Web Bluetooth tidak menyediakan AbortSignal untuk gatt.connect().
                    // Jika Android menyelesaikannya setelah timeout, tutup koneksi yang
                    // terlambat supaya percobaan berikutnya tidak terkunci.
                    if (error?.name === 'TimeoutError' && connectOperation) {
                        connectOperation.then(() => {
                            if (device.gatt?.connected) device.gatt.disconnect();
                        }).catch(() => {});
                    }

                    throw error;
                }
            }

            async function searchBluetooth() {
                if (!window.isSecureContext || !navigator.bluetooth?.requestDevice) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pencarian Bluetooth tidak tersedia',
                        text: 'Buka Medcare melalui HTTPS di Chrome. Jika printer Putian memakai Bluetooth Classic, gunakan Hubungkan Putian di Chrome Android terbaru atau Chrome/Edge desktop.',
                        confirmButtonText: 'Mengerti'
                    });
                    return;
                }
                if (selectingDevice || connecting) return;

                selectingDevice = true;
                let errorMessage = null;
                try {
                    const device = await navigator.bluetooth.requestDevice({
                        acceptAllDevices: true,
                        optionalServices: bluetoothServiceUuids
                    });
                    selectingDevice = false;
                    setBusy(true);
                    await disconnectConnections(false);
                    await connectBluetoothDevice(device);
                    Swal.fire({
                        icon: 'success',
                        title: 'Printer terhubung',
                        text: `${connectionName()} siap menerima cetak langsung.`,
                        timer: 1900,
                        showConfirmButton: false
                    });
                } catch (error) {
                    if (error?.name !== 'NotFoundError') {
                        errorMessage = bluetoothErrorMessage(error);
                    }
                } finally {
                    selectingDevice = false;
                    setBusy(false);
                    if (errorMessage) connectionError(errorMessage);
                }
            }

            function bluetoothErrorMessage(error) {
                if (error?.name === 'TimeoutError') {
                    return error.message;
                }
                if (error?.name === 'SecurityError') {
                    return 'Izin Bluetooth ditolak. Pastikan situs dibuka melalui HTTPS dan Bluetooth aktif.';
                }
                if (error?.name === 'NetworkError') {
                    return 'Koneksi Bluetooth terputus. Dekatkan printer, nyalakan ulang, lalu coba lagi.';
                }

                return error?.message || 'Printer tidak ditemukan atau tidak menyediakan kanal cetak yang didukung.';
            }

            async function connectSerial(port, remember = true) {
                if (!port.readable && !port.writable) {
                    const openOperation = port.open({ baudRate: 9600, bufferSize: 4096 });
                    try {
                        await withTimeout(
                            openOperation,
                            serialOpenTimeoutMs,
                            'Port printer tidak merespons dalam 12 detik. Matikan dan nyalakan printer, lalu coba kembali.'
                        );
                    } catch (error) {
                        if (error?.name === 'TimeoutError') {
                            openOperation.then(async () => {
                                try {
                                    if (port.readable || port.writable) await port.close();
                                } catch (closeError) {
                                    // Port yang selesai terlambat dapat sudah ditutup sistem.
                                }
                            }).catch(() => {});
                        }
                        throw error;
                    }
                }
                serialPort = port;

                if (remember) {
                    saveConfig({
                        mode: 'serial',
                        name: 'Putian POS (Bluetooth COM)',
                        deviceId: ''
                    });
                } else {
                    renderState();
                }
            }

            async function searchSerial() {
                if (!window.isSecureContext || !navigator.serial?.requestPort) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Bluetooth COM tidak tersedia',
                        text: 'Perbarui Chrome Android ke versi terbaru atau gunakan Chrome/Edge desktop melalui HTTPS. Jika tetap tidak tersedia, gunakan printer BLE atau dialog cetak browser.',
                        confirmButtonText: 'Mengerti'
                    });
                    return;
                }
                if (selectingDevice || connecting) return;

                selectingDevice = true;
                let errorMessage = null;
                try {
                    const port = await navigator.serial.requestPort();
                    selectingDevice = false;
                    setBusy(true);
                    await disconnectConnections(false);
                    await connectSerial(port);
                    Swal.fire({
                        icon: 'success',
                        title: 'Port printer terhubung',
                        text: 'Putian POS siap menerima perintah ESC/POS 80 mm.',
                        timer: 1900,
                        showConfirmButton: false
                    });
                } catch (error) {
                    if (error?.name !== 'NotFoundError') {
                        errorMessage = error?.message || 'Port Bluetooth printer tidak dapat dibuka.';
                    }
                } finally {
                    selectingDevice = false;
                    setBusy(false);
                    if (errorMessage) connectionError(errorMessage);
                }
            }

            function handleBluetoothDisconnect() {
                bluetoothCharacteristic = null;
                renderState();
            }

            async function disconnectConnections(forget = false) {
                const device = bluetoothDevice;

                bluetoothCharacteristic = null;
                if (device?.gatt?.connected) device.gatt.disconnect();
                bluetoothDevice = null;

                if (serialPort) {
                    try {
                        await withTimeout(
                            serialPort.close(),
                            disconnectTimeoutMs,
                            'Port printer terlalu lama ditutup.'
                        );
                    } catch (error) {
                        // Port dapat sudah ditutup oleh sistem saat printer mati.
                    }
                    serialPort = null;
                }

                if (forget && device?.forget) {
                    try {
                        await withTimeout(
                            device.forget(),
                            disconnectTimeoutMs,
                            'Izin printer terlalu lama dilepas.'
                        );
                    } catch (error) {
                        // Browser tertentu belum mendukung pencabutan izin perangkat.
                    }
                }
            }

            async function releasePrinter() {
                setBusy(true);
                try {
                    await disconnectConnections(true);
                    config = { ...fallbackConfig, columns: config.columns, autoCut: config.autoCut };
                    try {
                        localStorage.setItem(storageKey, JSON.stringify(config));
                    } catch (error) {
                        // Abaikan bila penyimpanan browser dinonaktifkan.
                    }
                    renderState();
                } finally {
                    setBusy(false);
                }
            }

            async function useBrowserPrinter() {
                setBusy(true);
                try {
                    await disconnectConnections(false);
                    saveConfig({ mode: 'browser', name: 'Printer sistem', deviceId: '' });
                    Swal.fire({
                        icon: 'success',
                        title: 'Dialog browser dipilih',
                        text: 'Pilih Putian POS dengan kertas Receipt/Continuous 80 mm, margin none, dan skala 100%. Hindari ukuran A4 atau 80 x 297 mm.',
                        timer: 3600,
                        showConfirmButton: false
                    });
                } finally {
                    setBusy(false);
                }
            }

            function asciiBytes(text) {
                const replacements = {
                    '\u00b7': '-',
                    '\u2013': '-',
                    '\u2014': '-',
                    '\u00d7': 'x',
                    '\u2022': '-',
                    '\u2018': "'",
                    '\u2019': "'",
                    '\u201c': '"',
                    '\u201d': '"'
                };
                const normalized = String(text)
                    .replace(/[\u00b7\u2013\u2014\u00d7\u2022\u2018\u2019\u201c\u201d]/g, character => replacements[character])
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^\x09\x0a\x0d\x20-\x7e]/g, '?');

                return new TextEncoder().encode(normalized);
            }

            function joinBytes(...parts) {
                const length = parts.reduce((total, part) => total + part.length, 0);
                const joined = new Uint8Array(length);
                let offset = 0;

                parts.forEach(part => {
                    joined.set(part, offset);
                    offset += part.length;
                });

                return joined;
            }

            function wait(milliseconds) {
                return new Promise(resolve => window.setTimeout(resolve, milliseconds));
            }

            function escPosDocument(text, options = {}) {
                const initialize = new Uint8Array([0x1b, 0x40]);
                const alignCenter = new Uint8Array([0x1b, 0x61, 0x01]);
                const alignLeft = new Uint8Array([0x1b, 0x61, 0x00]);
                const boldOn = new Uint8Array([0x1b, 0x45, 0x01]);
                const boldOff = new Uint8Array([0x1b, 0x45, 0x00]);
                const doubleSize = new Uint8Array([0x1d, 0x21, 0x11]);
                const normalSize = new Uint8Array([0x1d, 0x21, 0x00]);
                // Sisakan jarak secukupnya ke pisau potong tanpa membuang banyak kertas.
                const feed = new Uint8Array([0x0a, 0x0a]);
                const cut = config.autoCut ? new Uint8Array([0x1d, 0x56, 0x00]) : new Uint8Array([]);

                if (options.test) {
                    return joinBytes(
                        initialize,
                        alignCenter,
                        boldOn,
                        doubleSize,
                        asciiBytes('MEDCARE\n'),
                        normalSize,
                        asciiBytes('PUTIAN POS - THERMAL 80MM\n'),
                        boldOff,
                        asciiBytes('================================\n'),
                        asciiBytes('Koneksi printer berhasil\n'),
                        asciiBytes(`${new Date().toLocaleString('id-ID')}\n`),
                        asciiBytes('================================\n'),
                        asciiBytes('Siap mencetak struk ESC/POS\n'),
                        feed,
                        cut
                    );
                }

                return joinBytes(initialize, alignLeft, asciiBytes(text.replace(/\n+$/, '')), feed, cut);
            }

            function wrapLine(line, width) {
                const clean = line.replace(/\s+/g, ' ').trim();
                if (!clean) return [''];
                if (clean.length <= width) return [clean];

                const words = clean.split(' ');
                const lines = [];
                let current = '';

                words.forEach(word => {
                    if (word.length > width) {
                        if (current) lines.push(current);
                        for (let offset = 0; offset < word.length; offset += width) {
                            lines.push(word.slice(offset, offset + width));
                        }
                        current = '';
                        return;
                    }

                    const candidate = current ? `${current} ${word}` : word;
                    if (candidate.length > width) {
                        lines.push(current);
                        current = word;
                    } else {
                        current = candidate;
                    }
                });

                if (current) lines.push(current);

                return lines;
            }

            function receiptText(frameWindow) {
                const document = frameWindow?.document;
                const receipts = document ? [...document.querySelectorAll('main.receipt')] : [];
                if (!receipts.length) throw new Error('Isi struk belum selesai dimuat.');

                const width = Number(config.columns) === 42 ? 42 : 48;
                const separator = '-'.repeat(width);

                return receipts.map(receipt => {
                    const lines = (receipt.innerText || receipt.textContent || '')
                        .replace(/\u00a0/g, ' ')
                        .split(/\r?\n/)
                        .map(line => line.trim())
                        .filter(Boolean);

                    return lines.flatMap(line => wrapLine(line, width)).join('\n');
                }).join(`\n${separator}\n`);
            }

            function printableElements(frameDocument) {
                return [...frameDocument.querySelectorAll('main.receipt, article.label-sheet')];
            }

            function imageDataUrl(blob) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.addEventListener('load', () => resolve(reader.result), { once: true });
                    reader.addEventListener('error', () => reject(new Error('Logo tidak dapat diproses untuk printer.')), { once: true });
                    reader.readAsDataURL(blob);
                });
            }

            async function waitForPrintableAssets(frameDocument, elements) {
                if (frameDocument.fonts?.ready) await frameDocument.fonts.ready;

                const images = elements.flatMap(element => [...element.querySelectorAll('img')]);
                await Promise.all(images.map(async image => {
                    if (!image.complete) {
                        await new Promise(resolve => {
                            image.addEventListener('load', resolve, { once: true });
                            image.addEventListener('error', resolve, { once: true });
                        });
                    }

                    if (!image.naturalWidth || !image.naturalHeight) {
                        throw new Error('Logo apotek belum dapat dimuat. Periksa file logo lalu coba kembali.');
                    }

                    if (typeof image.decode === 'function') {
                        try {
                            await image.decode();
                        } catch (error) {
                            // naturalWidth sudah memastikan gambar siap diraster.
                        }
                    }
                }));
            }

            async function inlinePrintableImages(frameDocument, elements) {
                const frameOrigin = frameDocument.defaultView?.location?.origin;
                const sources = [...new Set(elements.flatMap(element => (
                    [...element.querySelectorAll('img')].map(image => image.currentSrc || image.src)
                )).filter(Boolean))];
                const inlined = new Map();

                await Promise.all(sources.map(async source => {
                    try {
                        const sourceUrl = new URL(source, frameDocument.location.href);
                        const fetchUrl = frameOrigin && sourceUrl.origin !== frameOrigin
                            ? `${frameOrigin}${sourceUrl.pathname}${sourceUrl.search}`
                            : sourceUrl.href;
                        const response = await fetch(fetchUrl, {
                            credentials: 'same-origin',
                            cache: 'force-cache'
                        });
                        if (!response.ok) return;

                        const dataUrl = await imageDataUrl(await response.blob());
                        inlined.set(sourceUrl.href, dataUrl);
                        inlined.set(fetchUrl, dataUrl);
                    } catch (error) {
                        // Gambar yang sudah termuat tetap dapat dipakai oleh html2canvas.
                    }
                }));

                return inlined;
            }

            async function printableCanvas(frameDocument, printable, inlinedImages) {
                const bounds = printable.getBoundingClientRect();
                const sourceWidth = Math.max(1, Math.ceil(bounds.width));
                const sourceHeight = Math.max(1, Math.ceil(bounds.height));
                const printWidth = 576;
                const printHeight = Math.ceil(sourceHeight * (printWidth / sourceWidth));

                if (printHeight > 30000) {
                    throw new Error('Nota terlalu panjang untuk sekali cetak. Kurangi item lalu coba kembali.');
                }

                if (typeof window.html2canvas !== 'function') {
                    throw new Error('Renderer nota belum termuat. Muat ulang halaman POS lalu coba kembali.');
                }

                return window.html2canvas(printable, {
                    backgroundColor: '#ffffff',
                    scale: printWidth / sourceWidth,
                    width: sourceWidth,
                    height: sourceHeight,
                    useCORS: true,
                    allowTaint: false,
                    foreignObjectRendering: false,
                    imageTimeout: 3000,
                    logging: false,
                    removeContainer: true,
                    onclone(clonedDocument) {
                        const pageOrigin = frameDocument.defaultView?.location?.origin;
                        clonedDocument.querySelectorAll('main.receipt img, article.label-sheet img').forEach(image => {
                            image.removeAttribute('srcset');
                            try {
                                const sourceUrl = new URL(image.src, clonedDocument.location.href);
                                const sameOriginUrl = pageOrigin && sourceUrl.origin !== pageOrigin
                                    ? `${pageOrigin}${sourceUrl.pathname}${sourceUrl.search}`
                                    : sourceUrl.href;
                                image.src = inlinedImages.get(sourceUrl.href)
                                    || inlinedImages.get(sameOriginUrl)
                                    || sameOriginUrl;
                            } catch (error) {
                                image.remove();
                            }
                        });

                        const style = clonedDocument.createElement('style');
                        style.textContent = `
                            main.receipt,
                            article.label-sheet {
                                margin: 0 !important;
                                background: #fff !important;
                                box-shadow: none !important;
                            }
                            main.receipt,
                            main.receipt *,
                            main.receipt *::before,
                            main.receipt *::after,
                            article.label-sheet,
                            article.label-sheet *,
                            article.label-sheet *::before,
                            article.label-sheet *::after {
                                color: #000 !important;
                                border-color: #000 !important;
                                text-shadow: none !important;
                            }
                            main.receipt *,
                            article.label-sheet * {
                                background-color: transparent !important;
                                background-image: none !important;
                            }
                            article.label-sheet {
                                border-right: 0 !important;
                                border-bottom: 0 !important;
                                border-left: 0 !important;
                                border-radius: 0 !important;
                            }
                        `;
                        clonedDocument.head.appendChild(style);
                    }
                });
            }

            async function printableCanvases(frameWindow) {
                const frameDocument = frameWindow?.document;
                const printables = frameDocument ? printableElements(frameDocument) : [];
                if (!printables.length) throw new Error('Dokumen cetak belum selesai dimuat.');

                await waitForPrintableAssets(frameDocument, printables);
                const inlinedImages = await inlinePrintableImages(frameDocument, printables);
                const canvases = [];

                for (const printable of printables) {
                    canvases.push(await printableCanvas(frameDocument, printable, inlinedImages));
                }

                return canvases;
            }

            function compactThermalCanvas(canvas, edgePadding = 12, maxBlankRows = 32) {
                const context = canvas.getContext('2d', { willReadFrequently: true });
                const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
                const inkRows = new Uint8Array(canvas.height);
                let firstInkRow = -1;
                let lastInkRow = -1;

                for (let y = 0; y < canvas.height; y += 1) {
                    for (let x = 0; x < canvas.width; x += 1) {
                        const pixel = ((y * canvas.width) + x) * 4;
                        const alpha = pixels[pixel + 3] / 255;
                        const luminance = (
                            (pixels[pixel] * 0.299)
                            + (pixels[pixel + 1] * 0.587)
                            + (pixels[pixel + 2] * 0.114)
                        ) * alpha + (255 * (1 - alpha));

                        if (luminance < 245) {
                            inkRows[y] = 1;
                            if (firstInkRow < 0) firstInkRow = y;
                            lastInkRow = y;
                            break;
                        }
                    }
                }

                if (firstInkRow < 0 || lastInkRow < 0) return canvas;

                const sections = [];
                let compactHeight = edgePadding * 2;
                let row = firstInkRow;

                while (row <= lastInkRow) {
                    const isInk = inkRows[row] === 1;
                    const sectionStart = row;

                    while (row <= lastInkRow && (inkRows[row] === 1) === isInk) row += 1;

                    const sourceHeight = row - sectionStart;
                    const outputHeight = isInk ? sourceHeight : Math.min(sourceHeight, maxBlankRows);
                    sections.push({ isInk, sectionStart, sourceHeight, outputHeight });
                    compactHeight += outputHeight;
                }

                const originalSpan = lastInkRow - firstInkRow + 1 + (edgePadding * 2);
                if (compactHeight >= canvas.height && originalSpan >= canvas.height) return canvas;

                const compacted = document.createElement('canvas');
                compacted.width = canvas.width;
                compacted.height = Math.max(1, compactHeight);
                const compactedContext = compacted.getContext('2d');
                compactedContext.fillStyle = '#ffffff';
                compactedContext.fillRect(0, 0, compacted.width, compacted.height);

                let destinationRow = edgePadding;
                sections.forEach(section => {
                    if (section.isInk) {
                        compactedContext.drawImage(
                            canvas,
                            0,
                            section.sectionStart,
                            canvas.width,
                            section.sourceHeight,
                            0,
                            destinationRow,
                            canvas.width,
                            section.sourceHeight
                        );
                    }

                    destinationRow += section.outputHeight;
                });

                return compacted;
            }

            function monochromeThermalCanvas(canvas, threshold = 175) {
                const sourceContext = canvas.getContext('2d', { willReadFrequently: true });
                const sourceImage = sourceContext.getImageData(0, 0, canvas.width, canvas.height);
                const monochrome = document.createElement('canvas');
                monochrome.width = canvas.width;
                monochrome.height = canvas.height;

                const monochromeContext = monochrome.getContext('2d');
                const outputImage = monochromeContext.createImageData(canvas.width, canvas.height);

                for (let pixel = 0; pixel < sourceImage.data.length; pixel += 4) {
                    const alpha = sourceImage.data[pixel + 3] / 255;
                    const luminance = (
                        (sourceImage.data[pixel] * 0.299)
                        + (sourceImage.data[pixel + 1] * 0.587)
                        + (sourceImage.data[pixel + 2] * 0.114)
                    ) * alpha + (255 * (1 - alpha));
                    const thermalColor = luminance < threshold ? 0 : 255;

                    outputImage.data[pixel] = thermalColor;
                    outputImage.data[pixel + 1] = thermalColor;
                    outputImage.data[pixel + 2] = thermalColor;
                    outputImage.data[pixel + 3] = 255;
                }

                monochromeContext.putImageData(outputImage, 0, 0);

                return monochrome;
            }

            function cropMonochromeThermalCanvas(canvas, edgePadding = 4) {
                const context = canvas.getContext('2d', { willReadFrequently: true });
                const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
                let firstInkRow = -1;
                let lastInkRow = -1;

                for (let y = 0; y < canvas.height; y += 1) {
                    for (let x = 0; x < canvas.width; x += 1) {
                        if (pixels[((y * canvas.width) + x) * 4] === 0) {
                            if (firstInkRow < 0) firstInkRow = y;
                            lastInkRow = y;
                            break;
                        }
                    }
                }

                if (firstInkRow < 0 || lastInkRow < 0) return canvas;

                const cropped = document.createElement('canvas');
                cropped.width = canvas.width;
                cropped.height = (lastInkRow - firstInkRow + 1) + (edgePadding * 2);
                const croppedContext = cropped.getContext('2d');
                croppedContext.fillStyle = '#ffffff';
                croppedContext.fillRect(0, 0, cropped.width, cropped.height);
                croppedContext.drawImage(
                    canvas,
                    0,
                    firstInkRow,
                    canvas.width,
                    lastInkRow - firstInkRow + 1,
                    0,
                    edgePadding,
                    canvas.width,
                    lastInkRow - firstInkRow + 1
                );

                return cropped;
            }

            async function printBrowserFrame(frameWindow) {
                const canvases = (await printableCanvases(frameWindow))
                    .map(canvas => compactThermalCanvas(canvas))
                    // Samakan hasil dialog browser dengan bitmap 1-bit yang dikirim
                    // langsung ke Putian. Dengan demikian teks dan logo tidak lagi
                    // diraster ulang sebagai abu-abu oleh driver printer thermal.
                    .map(canvas => monochromeThermalCanvas(canvas))
                    // Pangkas lagi sesudah monokrom. Piksel abu-abu samar yang sudah
                    // menjadi putih tidak boleh ikut memperpanjang gulungan kertas.
                    .map(canvas => cropMonochromeThermalCanvas(canvas));
                const paperWidthMm = 80;
                const printableWidthMm = 72;
                const interDocumentGapMm = 0.8;
                const pixelsPerMillimeter = 96 / 25.4;
                const imageHeightsMm = canvases.map(canvas => canvas.height * printableWidthMm / canvas.width);
                const contentHeightMm = imageHeightsMm.reduce((height, imageHeight) => height + imageHeight, 0)
                    + (Math.max(0, canvases.length - 1) * interDocumentGapMm);
                let pageHeightMm = Math.max(5, Math.ceil((contentHeightMm + 0.2) * 10) / 10);
                const printFrame = document.createElement('iframe');

                printFrame.setAttribute('aria-hidden', 'true');
                printFrame.setAttribute('title', 'Dokumen cetak thermal 80 mm');
                Object.assign(printFrame.style, {
                    position: 'fixed',
                    left: '-10000px',
                    top: '0',
                    width: `${paperWidthMm}mm`,
                    height: `${pageHeightMm}mm`,
                    border: '0',
                    opacity: '0',
                    pointerEvents: 'none'
                });
                document.body.appendChild(printFrame);

                const printDocument = printFrame.contentDocument;
                const printWindow = printFrame.contentWindow;
                if (!printDocument || !printWindow) {
                    printFrame.remove();
                    throw new Error('Jendela cetak browser tidak dapat disiapkan.');
                }

                printDocument.documentElement.lang = 'id';
                const charset = printDocument.createElement('meta');
                charset.setAttribute('charset', 'utf-8');
                const title = printDocument.createElement('title');
                title.textContent = frameWindow?.document?.title || 'Cetak Thermal 80 mm';
                const style = printDocument.createElement('style');
                const printStyles = heightMm => `
                    @page { size: ${paperWidthMm}mm ${heightMm}mm; margin: 0; }
                    * { box-sizing: border-box; }
                    html, body {
                        width: ${paperWidthMm}mm !important;
                        min-width: ${paperWidthMm}mm !important;
                        height: auto !important;
                        min-height: 0 !important;
                        max-height: none !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #fff !important;
                    }
                    body {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .thermal-print-image {
                        display: block;
                        width: ${printableWidthMm}mm !important;
                        max-width: none !important;
                        margin: 0 auto !important;
                        image-rendering: pixelated;
                    }
                    .thermal-print-image + .thermal-print-image {
                        margin-top: ${interDocumentGapMm}mm !important;
                    }
                `;
                style.textContent = printStyles(pageHeightMm);
                printDocument.head.replaceChildren(charset, title, style);

                const images = canvases.map((canvas, index) => {
                    const image = printDocument.createElement('img');
                    image.className = 'thermal-print-image';
                    image.alt = `Dokumen thermal ${index + 1}`;
                    image.style.height = `${imageHeightsMm[index]}mm`;
                    image.src = canvas.toDataURL('image/png');
                    printDocument.body.appendChild(image);
                    return image;
                });

                await Promise.all(images.map(image => (
                    typeof image.decode === 'function'
                        ? image.decode()
                        : new Promise(resolve => {
                            if (image.complete) return resolve();
                            image.addEventListener('load', resolve, { once: true });
                            image.addEventListener('error', resolve, { once: true });
                        })
                )));
                await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                // Ukur hasil akhir di iframe, bukan tinggi elemen sumber. Pembulatan
                // CSS dan driver dapat berbeda beberapa piksel dan sebelumnya ikut
                // menghasilkan ekor kertas putih yang tidak diperlukan.
                const renderedBottomPx = images.reduce((bottom, image) => (
                    Math.max(bottom, image.offsetTop + image.offsetHeight)
                ), 0);
                pageHeightMm = Math.max(
                    5,
                    Math.ceil(((renderedBottomPx / pixelsPerMillimeter) + 0.2) * 10) / 10
                );
                printFrame.style.height = `${pageHeightMm}mm`;
                style.textContent = printStyles(pageHeightMm);
                await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                let removed = false;
                const cleanup = () => {
                    if (removed) return;
                    removed = true;
                    printFrame.remove();
                };
                printWindow.addEventListener('afterprint', cleanup, { once: true });

                try {
                    printWindow.focus();
                    printWindow.print();
                    window.setTimeout(cleanup, 120000);
                } catch (error) {
                    cleanup();
                    throw error;
                }
            }

            function rasterCommands(canvas) {
                const context = canvas.getContext('2d', { willReadFrequently: true });
                const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
                const bytesPerRow = Math.ceil(canvas.width / 8);
                const commands = [];
                let inkPixels = 0;

                // Potongan pendek menjaga buffer printer kecil agar tidak meluap.
                // Blok pendek membatasi kerusakan bila bridge Bluetooth kehilangan
                // satu paket: parser segera bertemu header raster berikutnya.
                for (let top = 0; top < canvas.height; top += 64) {
                    const blockHeight = Math.min(64, canvas.height - top);
                    const bitmap = new Uint8Array(bytesPerRow * blockHeight);

                    for (let row = 0; row < blockHeight; row += 1) {
                        for (let x = 0; x < canvas.width; x += 1) {
                            const pixel = ((top + row) * canvas.width + x) * 4;
                            const alpha = pixels[pixel + 3] / 255;
                            const luminance = (
                                (pixels[pixel] * 0.299)
                                + (pixels[pixel + 1] * 0.587)
                                + (pixels[pixel + 2] * 0.114)
                            ) * alpha + (255 * (1 - alpha));

                            if (luminance < 175) {
                                bitmap[row * bytesPerRow + Math.floor(x / 8)] |= 0x80 >> (x % 8);
                                inkPixels += 1;
                            }
                        }
                    }

                    commands.push(joinBytes(
                        new Uint8Array([
                            0x1d, 0x76, 0x30, 0x00,
                            bytesPerRow & 0xff, (bytesPerRow >> 8) & 0xff,
                            blockHeight & 0xff, (blockHeight >> 8) & 0xff
                        ]),
                        bitmap
                    ));
                }

                if (inkPixels < 100) {
                    throw new Error('Gambar nota kosong sehingga cetak dibatalkan untuk menghemat kertas.');
                }

                return commands;
            }

            async function rasterReceiptDocument(frameWindow) {
                const canvases = (await printableCanvases(frameWindow)).map(canvas => compactThermalCanvas(canvas));

                const initialize = new Uint8Array([0x1b, 0x40]);
                const singleFeed = new Uint8Array([0x0a]);
                const finalFeed = new Uint8Array([0x0a]);
                const cut = config.autoCut === true
                    ? new Uint8Array([0x1d, 0x56, 0x00])
                    : new Uint8Array([]);
                const parts = [initialize];

                for (let index = 0; index < canvases.length; index += 1) {
                    parts.push(...rasterCommands(canvases[index]));
                    if (index < canvases.length - 1) parts.push(singleFeed);
                }

                parts.push(finalFeed);
                if (cut.length) parts.push(cut);
                // Kembalikan parser printer ke keadaan awal setelah satu job. Ini
                // mencegah sisa mode raster diteruskan sebagai feed kosong.
                parts.push(initialize);

                return parts;
            }

            async function writeBluetooth(bytes) {
                if (!isBluetoothReady()) throw new Error('Koneksi Bluetooth printer terputus.');
                const writeWithResponse = bluetoothCharacteristic.properties.write
                    && typeof bluetoothCharacteristic.writeValueWithResponse === 'function';
                const writeWithoutResponse = bluetoothCharacteristic.properties.writeWithoutResponse
                    && typeof bluetoothCharacteristic.writeValueWithoutResponse === 'function';

                for (let offset = 0; offset < bytes.length; offset += 20) {
                    const chunk = bytes.slice(offset, offset + 20);
                    if (writeWithResponse) {
                        await bluetoothCharacteristic.writeValueWithResponse(chunk);
                        await wait(3);
                    } else if (writeWithoutResponse) {
                        await bluetoothCharacteristic.writeValueWithoutResponse(chunk);
                        // Promise write-without-response selesai sebelum buffer fisik
                        // printer siap. Jeda ini mencegah byte gambar berubah menjadi
                        // karakter acak atau perintah feed tanpa akhir.
                        await wait(8);
                    } else {
                        await bluetoothCharacteristic.writeValue(chunk);
                    }
                }
            }

            async function writeSerial(bytes) {
                if (!isSerialReady()) throw new Error('Port Bluetooth COM printer terputus.');
                const writer = serialPort.writable.getWriter();

                try {
                    // Web Serial dapat menyelesaikan write setelah data masuk buffer
                    // sistem, sebelum bridge Bluetooth/printer siap. Pecah data agar
                    // buffer tidak meluap lalu membaca bitmap sebagai perintah feed.
                    for (let offset = 0; offset < bytes.length; offset += 256) {
                        await writer.write(bytes.slice(offset, offset + 256));
                        if (offset + 256 < bytes.length) await wait(24);
                    }
                } finally {
                    writer.releaseLock();
                }
            }

            async function write(bytes) {
                if (isBluetoothReady()) return writeBluetooth(bytes);
                if (isSerialReady()) return writeSerial(bytes);

                throw new Error('Hubungkan printer sebelum mencetak langsung.');
            }

            async function writeCommandSequence(commands) {
                for (const command of commands) {
                    if (!command.length) continue;

                    await write(command);
                    if (command.length > 8) await wait(24);
                }
            }

            async function testPrint() {
                const button = $('#testThermalPrinterBtn');
                const html = button.html();
                let failed = false;
                button.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Mencetak...');

                try {
                    await write(escPosDocument('', { test: true }));
                    Swal.fire({
                        icon: 'success',
                        title: 'Tes cetak dikirim',
                        text: 'Periksa hasil pada printer Putian POS.',
                        timer: 1800,
                        showConfirmButton: false
                    });
                } catch (error) {
                    failed = true;
                    connectionError(error?.message || 'Tes cetak gagal dikirim ke printer.');
                    Swal.fire('Tes cetak gagal', error?.message || 'Periksa koneksi dan kertas printer.', 'error');
                } finally {
                    button.html(html);
                    if (!failed) renderState();
                }
            }

            function printReceiptFrame(frameWindow) {
                // Klik otomatis setelah transaksi dan klik manual yang berdekatan
                // harus menunggu job yang sama, bukan mengirim dokumen dua kali.
                if (activeDirectPrintPromise) return activeDirectPrintPromise;

                const job = (async () => {
                    const commands = await rasterReceiptDocument(frameWindow);
                    await writeCommandSequence(commands);
                })();
                activeDirectPrintPromise = job;

                job.then(
                    () => { if (activeDirectPrintPromise === job) activeDirectPrintPromise = null; },
                    () => { if (activeDirectPrintPromise === job) activeDirectPrintPromise = null; }
                );

                return job;
            }

            async function restoreRememberedPrinter() {
                if (restoreAttempted || !config.mode || config.mode === 'browser') return;
                restoreAttempted = true;
                setBusy(true);

                try {
                    if (config.mode === 'bluetooth' && navigator.bluetooth?.getDevices) {
                        const devices = await withTimeout(
                            navigator.bluetooth.getDevices(),
                            bluetoothDiscoveryTimeoutMs,
                            'Daftar perangkat Bluetooth tersimpan tidak merespons.'
                        );
                        const device = devices.find(item => item.id === config.deviceId) || (devices.length === 1 ? devices[0] : null);
                        if (device) await connectBluetoothDevice(device, false);
                    } else if (config.mode === 'serial' && navigator.serial?.getPorts) {
                        const ports = await withTimeout(
                            navigator.serial.getPorts(),
                            serialOpenTimeoutMs,
                            'Daftar port printer tersimpan tidak merespons.'
                        );
                        if (ports.length === 1) await connectSerial(ports[0], false);
                    }
                } catch (error) {
                    // Printer tersimpan boleh tetap offline sampai pengguna menghubungkannya kembali.
                } finally {
                    setBusy(false);
                }
            }

            function show(returnToReceipt = false) {
                const canReturnToReceipt = returnToReceipt
                    && typeof posReceiptModalInstance === 'function'
                    && document.getElementById('posReceiptModal');
                returningToReceipt = Boolean(canReturnToReceipt);
                restoreRememberedPrinter();

                if (!returningToReceipt) {
                    printerModal()?.show();
                    return;
                }

                const receiptElement = document.getElementById('posReceiptModal');
                const receiptModal = posReceiptModalInstance();
                receiptElement?.addEventListener('hidden.bs.modal', () => printerModal()?.show(), { once: true });
                receiptModal?.hide();
            }

            $('#posPrinterButton').on('click', () => show(false));
            $('#receiptPrinterSettingsBtn').on('click', () => show(true));
            $('#searchBluetoothPrinterBtn').on('click', searchBluetooth);
            $('#connectSerialPrinterBtn').on('click', searchSerial);
            $('#useBrowserPrinterBtn').on('click', useBrowserPrinter);
            $('#releasePrinterBtn').on('click', releasePrinter);
            $('#testThermalPrinterBtn').on('click', testPrint);
            $('#posPrinterColumns').on('change', function() {
                saveConfig({ columns: Number(this.value) === 42 ? 42 : 48 });
            });
            $('#posPrinterAutoCut').on('change', function() {
                saveConfig({ autoCut: this.checked });
            });
            navigator.serial?.addEventListener?.('disconnect', event => {
                if (event.target !== serialPort) return;
                serialPort = null;
                renderState();
            });
            $('#posPrinterModal').on('hidden.bs.modal', function() {
                const receiptId = typeof lastReceiptId === 'undefined' ? null : lastReceiptId;
                if (!returningToReceipt || !receiptId) return;
                returningToReceipt = false;
                const receiptElement = document.getElementById('posReceiptModal');
                const documentType = typeof activeReceiptDocument === 'undefined'
                    ? 'receipt'
                    : activeReceiptDocument;
                if (typeof loadReceiptDocument === 'function') {
                    receiptElement?.addEventListener('shown.bs.modal', () => loadReceiptDocument(documentType, false), { once: true });
                }
                if (typeof posReceiptModalInstance === 'function') posReceiptModalInstance()?.show();
            });

            syncOptions();
            renderState();
            window.setTimeout(restoreRememberedPrinter, 250);

            return {
                isDirectReady,
                printBrowserFrame,
                printReceiptFrame,
                updatePrintButton,
                show
            };
        })();

        window.posThermalPrinter = posThermalPrinter;
