<script>
    $(document).ready(function() {
        const csrfToken = "{{ csrf_token() }}";

        window.NotifikasiTable = $('#tableNotifikasi').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("notifikasi.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'document',
                    name: 'document_no'
                },
                {
                    data: 'summary',
                    name: 'message'
                },
                {
                    data: 'status_badge',
                    className: 'text-start'
                },
                {
                    data: 'waktu'
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            rowCallback: function(row, data) {
                $(row).toggleClass('notif-unread', Boolean(data.is_unread));
            }
        });

        $('.dataTables_filter').hide();

        $('#searchNotifikasi').on('keyup search', function() {
            window.NotifikasiTable.search(this.value).draw();
        });

        $('.notification-filter').on('click', function() {
            $('.notification-filter').removeClass('is-active');
            $(this).addClass('is-active');
            window.NotifikasiTable.search($(this).data('filter') || '').draw();
        });

        $('#refreshNotificationTable').on('click', function() {
            window.NotifikasiTable.ajax.reload(null, false);
            if (typeof window.loadNotif === 'function') {
                window.loadNotif();
            }
        });

        window.openNotification = function(id) {
            const row = $(`button[onclick*="${id}"]`).closest('tr');
            const wasUnread = row.hasClass('notif-unread');

            $.ajax({
                url: "{{ route("notifikasi.readNotifikasi") }}",
                type: "POST",
                data: {
                    _token: csrfToken,
                    id: id
                },
                success: function(response) {
                    if (!response.notification) {
                        Swal.fire('Gagal', 'Notifikasi tidak ditemukan.', 'error');
                        return;
                    }

                    renderNotificationModal(response.notification);
                    $('#modalReadNotif').modal('show');

                    if (wasUnread) {
                        decrementNotificationBadges();
                    }

                    window.NotifikasiTable.ajax.reload(null, false);

                    if (typeof window.loadNotif === 'function') {
                        window.loadNotif();
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Tidak dapat membuka notifikasi.', 'error');
                }
            });
        };

        function renderNotificationModal(notification) {
            const actionButtons = notification.action_buttons || [];
            const amount = formatRupiah(notification.amount || 0);
            const items = Array.isArray(notification.items) ? notification.items : [];
            const itemTotal = items.reduce((total, item) => total + Number(item.total || item.subtotal || 0), 0);
            const tone = safeTone(notification.status_tone);
            const documentNo = notification.document_no || '-';
            const moduleLabel = notification.module_label || 'Notifikasi';
            const kindLabel = notification.kind_label || 'Notifikasi';
            const statusLabel = notification.status_label || '-';
            const pageUrl = notification.page_url || notification.url || '#';

            $('#notif-modal-icon')
                .attr('class', `notification-modal-icon is-${tone}`)
                .html(`<i class="mdi ${safeIcon(notification.module_icon)}"></i>`);
            $('#notif-modal-title').text(notification.title || 'Notifikasi');
            $('#notif-modal-subtitle').text(`${moduleLabel} | ${documentNo} | ${statusLabel}`);
            $('#notif-modal-status')
                .attr('class', `notification-modal-status is-${tone}`)
                .text(statusLabel);

            $('#notif-modal-body').html(`
                <div class="notification-modal-hero is-${tone}">
                    <div class="notification-hero-main">
                        <span class="notification-kind-pill is-${tone}">
                            <i class="mdi ${notification.can_action ? 'mdi-cursor-default-click-outline' : 'mdi-check-decagram-outline'}"></i>
                            ${escapeHtml(kindLabel)}
                        </span>
                        <h6>${escapeHtml(documentNo)}</h6>
                        <p>${escapeHtml(notification.message || '-')}</p>
                        <div class="notification-hero-meta">
                            <span><i class="mdi mdi-domain"></i>${escapeHtml(notification.branch_name || '-')}</span>
                            <span><i class="mdi mdi-account-outline"></i>${escapeHtml(notification.creator_name || '-')}</span>
                            <span><i class="mdi mdi-clock-outline"></i>${escapeHtml(notification.time || '-')}</span>
                        </div>
                    </div>
                    <div class="notification-hero-value">
                        <span>Nilai transaksi</span>
                        <strong>${amount}</strong>
                        <small>${Number(notification.item_count || items.length || 0).toLocaleString('id-ID')} item tercatat</small>
                    </div>
                </div>

                <div class="notification-modal-tabs" role="tablist" aria-label="Detail notifikasi">
                    <button type="button" class="notification-modal-tab is-active" data-target="#notifPaneSummary">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span>Ringkasan</span>
                    </button>
                    <button type="button" class="notification-modal-tab" data-target="#notifPaneItems">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span>Item</span>
                    </button>
                    <button type="button" class="notification-modal-tab" data-target="#notifPaneDetails">
                        <i class="mdi mdi-card-text-outline"></i>
                        <span>Rincian</span>
                    </button>
                </div>

                <div class="notification-modal-pane is-active" id="notifPaneSummary">
                    <div class="notification-insight-grid">
                        ${insightCard('mdi-file-document-outline', 'Dokumen', documentNo, `Ref ${notification.reference_no || '-'}`, 'info')}
                        ${insightCard('mdi-storefront-outline', 'Supplier', notification.supplier || '-', moduleLabel, 'success')}
                        ${insightCard('mdi-cash-multiple', 'Nilai', amount, `${formatRupiah(itemTotal)} dari item`, 'warning')}
                        ${insightCard('mdi-shield-check-outline', 'Status', statusLabel, notification.read_label || '-', tone)}
                    </div>

                    <div class="notification-message-panel is-${tone}">
                        <div>
                            <strong>${escapeHtml(kindLabel)}</strong>
                            <span>${escapeHtml(notification.message || '-')}</span>
                        </div>
                        ${notification.actor_name ? `<small>Diproses oleh ${escapeHtml(notification.actor_name)}</small>` : ''}
                    </div>

                    <div class="notification-timeline">
                        ${timelineItem('mdi-calendar-check-outline', 'Tanggal dokumen', formatDisplayDate(notification.document_date), moduleLabel)}
                        ${timelineItem('mdi-bell-ring-outline', 'Notifikasi masuk', formatDateTime(notification.created_at), notification.time || '-')}
                        ${timelineItem('mdi-flag-checkered', 'Status saat ini', statusLabel, notification.can_action ? 'Menunggu aksi pengguna berwenang' : kindLabel)}
                    </div>
                </div>

                <div class="notification-modal-pane" id="notifPaneItems">
                    ${itemDetailsHtml(items, itemTotal)}
                </div>

                <div class="notification-modal-pane" id="notifPaneDetails">
                    <div class="notification-detail-grid">
                        ${detailField('Dokumen', documentNo, 'mdi-file-document-outline')}
                        ${detailField('Referensi', notification.reference_no || '-', 'mdi-link-variant')}
                        ${detailField('Status', statusLabel, 'mdi-progress-check')}
                        ${detailField('Supplier', notification.supplier || '-', 'mdi-storefront-outline')}
                        ${detailField('Branch', notification.branch_name || '-', 'mdi-domain')}
                        ${detailField('Pembuat', notification.creator_name || '-', 'mdi-account-outline')}
                        ${detailField('Diproses Oleh', notification.actor_name || '-', 'mdi-account-check-outline')}
                        ${detailField('Tanggal', formatDisplayDate(notification.document_date), 'mdi-calendar-outline')}
                        ${detailField('Item', `${Number(notification.item_count || items.length || 0).toLocaleString('id-ID')} item`, 'mdi-package-variant')}
                        ${detailField('Nilai', amount, 'mdi-cash-multiple')}
                        ${detailField('Dibuat', formatDateTime(notification.created_at), 'mdi-clock-plus-outline')}
                        ${detailField('Dibaca', notification.read_label || '-', 'mdi-email-open-outline')}
                    </div>
                </div>
            `);

            const actionHtml = actionButtons.length ?
                actionButtons.map(action => actionButtonHtml(action, notification)).join('') :
                `<span class="notification-modal-action is-static"><i class="mdi mdi-information-outline"></i><span>Tidak ada aksi aktif</span></span>`;

            $('#notif-modal-footer').html(`
                <div class="notification-modal-action-group">
                    ${actionHtml}
                </div>
                <div class="notification-modal-action-group is-secondary">
                    <button type="button" class="notification-modal-action is-copy notification-copy-doc" data-copy="${escapeAttribute(documentNo)}">
                        <i class="mdi mdi-content-copy"></i>
                        <span>Salin No</span>
                    </button>
                    <button type="button" class="notification-modal-action is-detail notification-show-details">
                        <i class="mdi mdi-file-search-outline"></i>
                        <span>Detail</span>
                    </button>
                    <a href="${escapeAttribute(pageUrl)}" class="notification-modal-action is-page">
                        <i class="mdi mdi-open-in-new"></i>
                        <span>Halaman</span>
                    </a>
                </div>
            `);
        }

        function actionButtonHtml(action, notification) {
            return `
                <button type="button"
                    class="notification-modal-action is-${escapeAttribute(action.tone || 'primary')} notification-run-action"
                    data-url="${escapeAttribute(action.url)}"
                    data-method="${escapeAttribute(action.method || 'POST')}"
                    data-label="${escapeAttribute(action.label || 'Proses')}"
                    data-document="${escapeAttribute(notification.document_no || '-')}">
                    <i class="mdi ${safeIcon(action.icon || 'mdi-check-circle-outline')}"></i>
                    <span>${escapeHtml(action.label || 'Proses')}</span>
                </button>
            `;
        }

        function itemDetailsHtml(items, itemTotal) {
            if (!items.length) {
                return `
                    <div class="notification-items-panel is-empty">
                        <span><i class="mdi mdi-package-variant-closed"></i></span>
                        <div>
                            <strong>Isi Transaksi</strong>
                            <small>Detail item belum tersedia pada notifikasi ini.</small>
                        </div>
                    </div>
                `;
            }

            const hasNotes = items.some(item => String(item.note || '').trim() !== '');
            const noteHeader = hasNotes ? '<th>Catatan</th>' : '';
            const noteRows = hasNotes
                ? (item) => `<td class="notification-item-note">${escapeHtml(item.note || '-')}</td>`
                : () => '';

            return `
                <div class="notification-items-panel">
                    <div class="notification-items-heading">
                        <div>
                            <strong>Isi Transaksi</strong>
                            <small>${items.length.toLocaleString('id-ID')} item detail | Total item ${formatRupiah(itemTotal)}</small>
                        </div>
                        <label class="notification-items-search">
                            <i class="mdi mdi-magnify"></i>
                            <input type="search" class="notification-item-search" placeholder="Cari obat, batch, atau catatan">
                        </label>
                    </div>
                    <div class="notification-items-table-wrap">
                        <table class="notification-items-table">
                            <thead>
                                <tr>
                                    <th width="44">No</th>
                                    <th>Obat</th>
                                    <th>Qty</th>
                                    <th>Batch / ED</th>
                                    <th>Harga</th>
                                    <th>Diskon / PPN</th>
                                    <th>Total</th>
                                    ${noteHeader}
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(item => itemRowHtml(item, noteRows)).join('')}
                            </tbody>
                        </table>
                        <div class="notification-items-empty-state">
                            <i class="mdi mdi-file-search-outline"></i>
                            <strong>Item tidak ditemukan</strong>
                            <small>Coba kata kunci lain.</small>
                        </div>
                    </div>
                </div>
            `;
        }

        function itemRowHtml(item, noteCell) {
            const stockQty = Number(item.stock_qty || 0);
            const conversion = Number(item.unit_conversion || 1);
            const stockLine = stockQty > 0 && (conversion > 1 || item.stock_unit) ?
                `<small>${formatDecimal(stockQty, 0, 2)} ${escapeHtml(item.stock_unit || 'satuan stok')}</small>` :
                '';
            const batchLine = item.no_batch ?
                `<strong>${escapeHtml(item.no_batch)}</strong><small>ED ${formatDisplayDate(item.expired_date)}</small>` :
                '<span>-</span>';
            const discountTax = discountTaxHtml(item);
            const rowSearch = itemSearchText(item);

            return `
                <tr data-search="${escapeAttribute(rowSearch)}">
                    <td class="notification-item-number">${Number(item.row_no || 0) > 0 ? Number(item.row_no).toLocaleString('id-ID') : '-'}</td>
                    <td>
                        <strong>${escapeHtml(item.nama_obat || '-')}</strong>
                        <small>${escapeHtml(item.kode_obat || '-')}</small>
                    </td>
                    <td>
                        <strong>${formatDecimal(item.qty, 0, 2)} ${escapeHtml(item.unit || 'satuan')}</strong>
                        ${stockLine}
                    </td>
                    <td>${batchLine}</td>
                    <td>
                        <strong>${formatRupiah(item.price || 0)}</strong>
                        <small>Subtotal ${formatRupiah(item.subtotal || item.total || 0)}</small>
                    </td>
                    <td>${discountTax}</td>
                    <td><strong>${formatRupiah(item.total || item.subtotal || 0)}</strong></td>
                    ${noteCell(item)}
                </tr>
            `;
        }

        function discountTaxHtml(item) {
            const discount = Number(item.discount || 0);
            const discount1 = Number(item.discount_1 || 0);
            const discount2 = Number(item.discount_2 || 0);
            const discount3 = Number(item.discount_3 || 0);
            const tax = Number(item.tax || 0);
            const parts = [];

            if (discount1 > 0 || discount2 > 0 || discount3 > 0) {
                parts.push(`D1 ${formatDecimal(discount1, 0, 2)}% · D2 ${formatDecimal(discount2, 0, 2)}% · D3 ${formatDecimal(discount3, 0, 2)}%`);
                parts.push(`${formatDecimal(discount, 0, 2)}% efektif`);
            } else if (discount > 0) {
                parts.push(`${formatDecimal(discount, 0, 2)}% diskon`);
            }

            if (tax > 0) {
                parts.push(`${formatDecimal(tax, 0, 2)}% PPN`);
            }

            return parts.length ? parts.map(part => `<span>${escapeHtml(part)}</span>`).join('') : '<span>-</span>';
        }

        $(document).on('click', '.notification-run-action', function() {
            const button = $(this);
            const url = button.data('url');
            const method = button.data('method') || 'POST';
            const label = button.data('label') || 'Proses';
            const documentNo = button.data('document') || '-';

            Swal.fire({
                title: `${label} dokumen?`,
                text: `Dokumen ${documentNo} akan diproses.`,
                icon: button.hasClass('is-danger') ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: label,
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                button.prop('disabled', true).addClass('is-loading');

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: csrfToken
                    },
                    success: function(response) {
                        $('#modalReadNotif').modal('hide');
                        Swal.fire({
                            icon: response.status === 'error' ? 'error' : 'success',
                            title: response.message || 'Aksi berhasil diproses.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });

                        window.NotifikasiTable.ajax.reload(null, false);

                        if (typeof window.loadNotif === 'function') {
                            window.loadNotif();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Aksi tidak dapat diproses.'
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false).removeClass('is-loading');
                    }
                });
            });
        });

        $(document).on('click', '.notification-modal-tab', function() {
            const target = $(this).data('target');
            if (!target) return;

            activateNotificationPane(target);
        });

        $(document).on('click', '.notification-show-details', function() {
            activateNotificationPane('#notifPaneDetails');
        });

        $(document).on('input', '.notification-item-search', function() {
            const keyword = String($(this).val() || '').trim().toLowerCase();
            const rows = $('.notification-items-table tbody tr');
            let visible = 0;

            rows.each(function() {
                const haystack = String($(this).data('search') || '').toLowerCase();
                const match = keyword === '' || haystack.includes(keyword);
                $(this).toggle(match);
                if (match) visible++;
            });

            $('.notification-items-empty-state').toggleClass('is-visible', visible === 0);
        });

        $(document).on('click', '.notification-copy-doc', function() {
            const value = String($(this).data('copy') || '').trim();
            if (!value || value === '-') return;

            copyText(value).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Nomor dokumen disalin',
                    toast: true,
                    position: 'top-end',
                    timer: 1800,
                    showConfirmButton: false
                });
            }).catch(() => {
                Swal.fire('Gagal', 'Nomor dokumen tidak dapat disalin.', 'error');
            });
        });

        function detailField(label, value, icon = 'mdi-information-outline') {
            return `
                <div class="notification-detail-field">
                    <span><i class="mdi ${safeIcon(icon)}"></i>${escapeHtml(label)}</span>
                    <strong>${escapeHtml(value)}</strong>
                </div>
            `;
        }

        function activateNotificationPane(target) {
            $('.notification-modal-tab').removeClass('is-active');
            $(`.notification-modal-tab[data-target="${target}"]`).addClass('is-active');
            $('.notification-modal-pane').removeClass('is-active');
            $(target).addClass('is-active');
            $('#notif-modal-body').scrollTop(0);
        }

        function insightCard(icon, label, value, meta, tone = 'info') {
            return `
                <div class="notification-insight-card is-${safeTone(tone)}">
                    <span><i class="mdi ${safeIcon(icon)}"></i></span>
                    <div>
                        <small>${escapeHtml(label)}</small>
                        <strong>${escapeHtml(value)}</strong>
                        <em>${escapeHtml(meta || '-')}</em>
                    </div>
                </div>
            `;
        }

        function timelineItem(icon, title, value, meta) {
            return `
                <div class="notification-timeline-item">
                    <span><i class="mdi ${safeIcon(icon)}"></i></span>
                    <div>
                        <strong>${escapeHtml(title)}</strong>
                        <small>${escapeHtml(value || '-')}</small>
                        <em>${escapeHtml(meta || '-')}</em>
                    </div>
                </div>
            `;
        }

        function itemSearchText(item) {
            return [
                item.row_no,
                item.kode_obat,
                item.nama_obat,
                item.unit,
                item.stock_unit,
                item.no_batch,
                item.expired_date,
                item.note
            ].filter(value => value !== null && value !== undefined)
                .join(' ');
        }

        function copyText(value) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(value);
            }

            const textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);

            return Promise.resolve();
        }

        function decrementNotificationBadges() {
            decrementBadge('#notif-count');
            decrementBadge('#navbar-notif-badge span');

            const summary = $('#notificationUnreadSummary');
            if (summary.length) {
                const nextValue = Math.max(0, parseInt(summary.text().replace(/\D/g, '') || '0') - 1);
                summary.text(nextValue.toLocaleString('id-ID'));
            }
        }

        function decrementBadge(selector) {
            const badge = $(selector);
            if (!badge.length) return;

            const count = parseInt(badge.text() || '0');

            if (count > 1) {
                badge.text(count - 1);
                return;
            }

            badge.remove();
        }

        function formatDecimal(value, minimumFractionDigits = 0, maximumFractionDigits = 2) {
            return Number(value || 0).toLocaleString('id-ID', {
                minimumFractionDigits,
                maximumFractionDigits
            });
        }

        function formatDisplayDate(value) {
            if (!value) {
                return '-';
            }

            const text = String(value);
            const match = text.match(/^(\d{4})-(\d{2})-(\d{2})/);

            return match ? `${match[3]}-${match[2]}-${match[1]}` : text;
        }

        function formatDateTime(value) {
            if (!value) {
                return '-';
            }

            const text = String(value);
            const match = text.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);

            if (!match) {
                return text;
            }

            const date = `${match[3]}-${match[2]}-${match[1]}`;
            const time = match[4] && match[5] ? ` ${match[4]}:${match[5]}` : '';

            return `${date}${time}`;
        }

        function formatRupiah(value) {
            const number = Number(value || 0);
            return `Rp ${number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            })}`;
        }

        function safeIcon(icon) {
            const value = String(icon || 'mdi-bell-outline');
            return /^[a-z0-9-]+$/i.test(value) ? value : 'mdi-bell-outline';
        }

        function safeTone(tone) {
            const value = String(tone || 'muted');
            const allowed = ['success', 'danger', 'warning', 'info', 'muted', 'primary'];

            return allowed.includes(value) ? value : 'muted';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeAttribute(value) {
            return escapeHtml(value);
        }
    });
</script>
