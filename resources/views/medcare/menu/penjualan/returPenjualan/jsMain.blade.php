<script>
    $(function() {
        const urls = {
            table: @json(route('returPenjualan.table')),
            transactions: @json(route('returPenjualan.transactions')),
            transaction: @json(route('returPenjualan.transaction', ':id')),
            generateNumber: @json(route('returPenjualan.generateNumber')),
            store: @json(route('returPenjualan.store')),
            show: @json(route('returPenjualan.show', ':id')),
            edit: @json(route('returPenjualan.edit', ':id')),
            update: @json(route('returPenjualan.update', ':id')),
            post: @json(route('returPenjualan.post', ':id')),
            cancel: @json(route('returPenjualan.cancel', ':id')),
            destroy: @json(route('returPenjualan.destroy', ':id'))
        };
        const initialTransactionId = Number(@json((int) request('transaction', 0)));
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let selectedTransaction = null;
        let searchTimer = null;

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

        const escapeHtml = value => String(value ?? '')
            .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        const formatCurrency = value => new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', maximumFractionDigits: 0
        }).format(Number(value || 0));
        const formatNumber = value => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(value || 0));
        const statusClass = status => ['draft', 'posted', 'cancelled'].includes(status) ? status : 'draft';
        const methodLabels = @json($refundMethods);

        const table = $('#salesReturnTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            ajax: {
                url: urls.table,
                data: function(data) {
                    data.return_search = $('#returnSearch').val();
                    data.status = $('[data-return-status].is-active').data('return-status') || '';
                    data.branch_id = $('#returnBranch').val();
                    data.date_start = $('#returnDateStart').val();
                    data.date_end = $('#returnDateEnd').val();
                },
                dataSrc: function(json) {
                    updateSummary(json.summary || {});
                    return json.data || [];
                },
                error: xhr => showAjaxError(xhr, 'Daftar retur gagal dimuat.')
            },
            columns: [
                {
                    data: 'nomor_retur',
                    render: (data, type, row) => type === 'display' ? `
                        <strong>${escapeHtml(data)}</strong>
                        <span class="sales-return-status is-${statusClass(row.status)}">${escapeHtml(row.status_label)}</span>
                    ` : data
                },
                {
                    data: 'nomor_transaksi',
                    orderable: false,
                    render: (data, type, row) => type === 'display' ? `<strong>${escapeHtml(data)}</strong><small>${escapeHtml(row.customer_name)}</small>` : data
                },
                {
                    data: 'tanggal_retur',
                    render: (data, type, row) => type === 'display' ? `<strong>${escapeHtml(data)}</strong><small>${escapeHtml(row.branch_name)}</small>` : data
                },
                {
                    data: 'total_item',
                    className: 'text-center',
                    render: (data, type, row) => type === 'display' ? `<strong>${formatNumber(data)} item</strong><small>${formatNumber(row.total_qty)} qty jual</small>` : data
                },
                {
                    data: 'refund_method_label',
                    orderable: false,
                    render: (data, type, row) => type === 'display' ? `<strong>${escapeHtml(data)}</strong><small>${escapeHtml(row.refund_reference || 'Tanpa referensi')}</small>` : data
                },
                {
                    data: 'grand_total',
                    className: 'text-end',
                    render: (data, type) => type === 'display' ? `<strong>${formatCurrency(data)}</strong>` : data
                },
                {
                    data: 'created_by_name',
                    orderable: false,
                    render: data => `<span>${escapeHtml(data)}</span>`
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function(data, type, row) {
                        if (type !== 'display') return row.id;
                        const actions = [`<button type="button" data-return-action="show" data-id="${row.id}" title="Lihat detail"><i class="mdi mdi-eye-outline"></i></button>`];
                        if (row.status === 'draft') {
                            actions.push(`<button type="button" data-return-action="edit" data-id="${row.id}" title="Edit draft"><i class="mdi mdi-pencil-outline"></i></button>`);
                            actions.push(`<button type="button" data-return-action="post" data-id="${row.id}" title="Posting retur"><i class="mdi mdi-send-check-outline"></i></button>`);
                            actions.push(`<button type="button" class="is-danger" data-return-action="delete" data-id="${row.id}" title="Hapus draft"><i class="mdi mdi-delete-outline"></i></button>`);
                        }
                        if (row.status === 'posted') {
                            actions.push(`<button type="button" class="is-danger" data-return-action="cancel" data-id="${row.id}" title="Batalkan retur"><i class="mdi mdi-cancel"></i></button>`);
                        }
                        return `<div class="sales-return-actions">${actions.join('')}</div>`;
                    }
                }
            ],
            language: {
                processing: 'Memuat retur...', emptyTable: 'Belum ada retur penjualan.', zeroRecords: 'Retur tidak ditemukan.',
                lengthMenu: 'Tampilkan _MENU_', info: 'Menampilkan _START_–_END_ dari _TOTAL_ retur', infoEmpty: 'Tidak ada data',
                paginate: { previous: '‹', next: '›' }
            }
        });

        function updateSummary(summary) {
            $('#returnStatTotal').text(formatNumber(summary.total));
            $('#returnStatDraft').text(formatNumber(summary.draft));
            $('#returnStatPosted').text(formatNumber(summary.posted));
            $('#returnStatQty').text(`${formatNumber(summary.total_qty)} qty dikembalikan`);
            $('#returnStatValue').text(formatCurrency(summary.grand_total));
        }

        $('#returnTransaction').select2({
            dropdownParent: $('#salesReturnModal'),
            width: '100%',
            placeholder: 'Cari nomor transaksi atau pelanggan',
            allowClear: true,
            ajax: {
                url: urls.transactions,
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term || '', limit: 60 }),
                processResults: data => ({ results: data })
            },
            templateResult: function(item) {
                if (item.loading) return item.text;
                return $(`<div><strong>${escapeHtml(item.nomor_transaksi)}</strong><br><small>${escapeHtml(item.customer_name)} · ${escapeHtml(item.branch)} · ${item.returnable_items} item dapat diretur</small></div>`);
            }
        });

        $('#returnTransaction').on('select2:select', event => loadTransaction(Number(event.params.data.id)));
        $('#returnTransaction').on('select2:clear', function() {
            selectedTransaction = null;
            $('#returnTransactionInfo').prop('hidden', true).empty();
            $('#salesReturnItems').html('<tr><td colspan="8" class="text-center text-muted py-4">Pilih transaksi terlebih dahulu.</td></tr>');
            calculateFormTotal();
        });

        function resetForm() {
            $('#salesReturnForm')[0].reset();
            $('#salesReturnId, #returnNumber').val('');
            $('#returnDate').val(@json(now()->format('Y-m-d')));
            $('#returnTransaction').empty().trigger('change');
            $('#returnTransaction').prop('disabled', false);
            $('#salesReturnModalLabel').text('Buat Retur Penjualan');
            $('#selectAllReturnable').html('<i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i>Pilih Semua');
            $('#returnTransactionInfo').prop('hidden', true).empty();
            $('#salesReturnItems').html('<tr><td colspan="8" class="text-center text-muted py-4">Pilih transaksi terlebih dahulu.</td></tr>');
            selectedTransaction = null;
            calculateFormTotal();
        }

        function openForm(transactionId = null) {
            resetForm();
            $('#salesReturnModal').modal('show');
            if (transactionId) loadTransactionOption(transactionId);
        }

        $('#openSalesReturnForm, #openSalesReturnFormToolbar').on('click', () => openForm());

        function loadTransactionOption(id) {
            $('#salesReturnItems').html('<tr><td colspan="8" class="text-center py-4"><i class="mdi mdi-loading mdi-spin me-1"></i>Memuat transaksi...</td></tr>');
            $.get(urls.transaction.replace(':id', id))
                .done(function(transaction) {
                    const option = new Option(`${transaction.nomor_transaksi} - ${transaction.customer_name}`, transaction.id, true, true);
                    $('#returnTransaction').append(option).trigger('change');
                    renderTransaction(transaction);
                    generateReturnNumber(transaction.branch_id);
                })
                .fail(xhr => showAjaxError(xhr, 'Transaksi tidak dapat digunakan untuk retur.'));
        }

        function loadTransaction(id) {
            $('#salesReturnItems').html('<tr><td colspan="8" class="text-center py-4"><i class="mdi mdi-loading mdi-spin me-1"></i>Memuat item transaksi...</td></tr>');
            $.get(urls.transaction.replace(':id', id))
                .done(function(transaction) {
                    renderTransaction(transaction);
                    generateReturnNumber(transaction.branch_id);
                })
                .fail(xhr => showAjaxError(xhr, 'Detail transaksi gagal dimuat.'));
        }

        function generateReturnNumber(branchId) {
            if ($('#salesReturnId').val()) return;
            $.get(urls.generateNumber, { branch_id: branchId, tanggal_retur: $('#returnDate').val() })
                .done(response => $('#returnNumber').val(response.number || ''));
        }

        $('#returnDate').on('change', function() {
            if (selectedTransaction) generateReturnNumber(selectedTransaction.branch_id);
        });

        function renderTransaction(transaction, selectedDetails = {}) {
            selectedTransaction = transaction;
            $('#returnTransactionInfo').prop('hidden', false).html(`
                <div><small>Nomor transaksi</small><strong>${escapeHtml(transaction.nomor_transaksi)}</strong></div>
                <div><small>Tanggal transaksi</small><strong>${escapeHtml(transaction.tanggal_transaksi)}</strong></div>
                <div><small>Pelanggan</small><strong>${escapeHtml(transaction.customer_name)}</strong></div>
                <div><small>Cabang</small><strong>${escapeHtml(transaction.branch)}</strong></div>
                <div><small>Total transaksi</small><strong>${formatCurrency(transaction.grand_total)}</strong></div>
            `);

            const rows = (transaction.details || []).filter(detail => Number(detail.returnable_qty) > 0).map(function(detail) {
                const selected = selectedDetails[detail.id] || null;
                const checked = Boolean(selected);
                const qty = selected ? Number(selected.qty_jual) : '';
                const reason = selected?.alasan_item || '';
                const batches = (detail.batches || []).filter(batch => Number(batch.returnable_qty_stok) > 0)
                    .map(batch => `${escapeHtml(batch.no_batch || '-')} (${formatNumber(batch.returnable_qty_stok)} ${escapeHtml(detail.satuan_stok || '')})`).join(', ');
                return `
                    <tr class="sales-return-item-row" data-sale-detail-id="${detail.id}" data-returnable-qty="${Number(detail.returnable_qty)}" data-returnable-value="${Number(detail.returnable_value)}">
                        <td data-label="Pilih"><input type="checkbox" class="return-item-check" ${checked ? 'checked' : ''} aria-label="Pilih ${escapeHtml(detail.nama_obat)}"></td>
                        <td data-label="Obat"><strong>${escapeHtml(detail.nama_obat)}</strong><small>${escapeHtml(detail.kode_obat || '-')} · ${escapeHtml(detail.satuan_jual || '-')}</small><small title="Batch tersedia">${batches || 'Batch tidak tersedia'}</small></td>
                        <td class="text-end" data-label="Terjual"><strong>${formatNumber(detail.qty_jual)}</strong><small>${escapeHtml(detail.satuan_jual || '')}</small></td>
                        <td class="text-end" data-label="Sudah retur"><strong>${formatNumber(detail.returned_qty)}</strong></td>
                        <td class="text-end" data-label="Sisa"><strong>${formatNumber(detail.returnable_qty)}</strong></td>
                        <td data-label="Qty retur"><input type="number" class="form-control form-control-sm return-item-qty" min="0.01" max="${Number(detail.returnable_qty)}" step="0.01" value="${qty}" ${checked ? '' : 'disabled'}></td>
                        <td class="text-end" data-label="Estimasi"><strong class="return-item-estimate">${formatCurrency(0)}</strong></td>
                        <td data-label="Alasan"><input type="text" class="form-control form-control-sm return-item-reason" maxlength="500" value="${escapeHtml(reason)}" placeholder="Opsional" ${checked ? '' : 'disabled'}></td>
                    </tr>`;
            }).join('');
            $('#salesReturnItems').html(rows || '<tr><td colspan="8" class="text-center text-muted py-4">Semua item pada transaksi ini sudah diretur.</td></tr>');
            calculateFormTotal();
        }

        $(document).on('change', '.return-item-check', function() {
            const row = $(this).closest('tr');
            const checked = this.checked;
            row.find('.return-item-qty, .return-item-reason').prop('disabled', !checked);
            if (checked && !row.find('.return-item-qty').val()) row.find('.return-item-qty').val(row.data('returnable-qty'));
            if (!checked) row.find('.return-item-qty').val('');
            calculateFormTotal();
        });
        $(document).on('input change', '.return-item-qty', calculateFormTotal);

        $('#selectAllReturnable').on('click', function() {
            const checks = $('.return-item-check');
            const shouldSelect = checks.filter(':not(:checked)').length > 0;
            checks.prop('checked', shouldSelect).trigger('change');
            $(this).html(shouldSelect
                ? '<i class="mdi mdi-checkbox-multiple-blank-outline me-1"></i>Batalkan Semua'
                : '<i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i>Pilih Semua');
        });

        function calculateFormTotal() {
            let total = 0;
            $('#salesReturnItems tr').each(function() {
                const row = $(this);
                if (!row.find('.return-item-check').is(':checked')) {
                    row.find('.return-item-estimate').text(formatCurrency(0));
                    return;
                }
                const availableQty = Number(row.data('returnable-qty') || 0);
                const availableValue = Number(row.data('returnable-value') || 0);
                const qty = Math.min(availableQty, Math.max(0, Number(row.find('.return-item-qty').val() || 0)));
                const value = availableQty > 0 ? availableValue * (qty / availableQty) : 0;
                row.find('.return-item-estimate').text(formatCurrency(value));
                total += value;
            });
            $('#returnFormTotal').text(formatCurrency(total));
        }

        function formPayload() {
            const details = [];
            $('#salesReturnItems tr').each(function() {
                const row = $(this);
                if (!row.find('.return-item-check').is(':checked')) return;
                details.push({
                    penjualan_transaction_detail_id: Number(row.data('sale-detail-id')),
                    qty: Number(row.find('.return-item-qty').val()),
                    alasan_item: row.find('.return-item-reason').val()
                });
            });
            return {
                penjualan_transaction_id: Number($('#returnTransaction').val()),
                tanggal_retur: $('#returnDate').val(),
                refund_method: $('#returnRefundMethod').val(),
                refund_reference: $('#returnRefundReference').val(),
                alasan: $('#returnReason').val(),
                catatan: $('#returnNotes').val(),
                details
            };
        }

        $('#salesReturnForm').on('submit', function(event) {
            event.preventDefault();
            const id = Number($('#salesReturnId').val() || 0);
            const payload = formPayload();
            if (!payload.details.length) {
                Swal.fire('Belum ada item', 'Pilih minimal satu item yang akan diretur.', 'warning');
                return;
            }
            const button = $('#saveSalesReturn').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i>Menyimpan...');
            $.ajax({
                url: id ? urls.update.replace(':id', id) : urls.store,
                type: id ? 'PUT' : 'POST',
                data: payload
            }).done(function(response) {
                $('#salesReturnModal').modal('hide');
                notifySuccess(response.message);
                table.ajax.reload(null, false);
            }).fail(xhr => showAjaxError(xhr, 'Retur penjualan gagal disimpan.'))
              .always(() => button.prop('disabled', false).html('<i class="mdi mdi-content-save-outline me-1"></i>Simpan Draft'));
        });

        $(document).on('click', '[data-return-action]', function() {
            const action = $(this).data('return-action');
            const id = Number($(this).data('id'));
            if (action === 'show') showReturn(id);
            if (action === 'edit') editReturn(id);
            if (action === 'post') postReturn(id);
            if (action === 'cancel') cancelReturn(id);
            if (action === 'delete') deleteReturn(id);
        });

        function editReturn(id) {
            resetForm();
            $('#salesReturnModalLabel').text('Edit Draft Retur Penjualan');
            $('#salesReturnModal').modal('show');
            $('#salesReturnItems').html('<tr><td colspan="8" class="text-center py-4"><i class="mdi mdi-loading mdi-spin me-1"></i>Memuat draft...</td></tr>');
            $.get(urls.edit.replace(':id', id)).done(function(response) {
                const data = response.return;
                $('#salesReturnId').val(data.id);
                $('#returnNumber').val(data.nomor_retur);
                $('#returnDate').val(data.tanggal_retur);
                $('#returnRefundMethod').val(data.refund_method);
                $('#returnRefundReference').val(data.refund_reference);
                $('#returnReason').val(data.alasan);
                $('#returnNotes').val(data.catatan);
                const option = new Option(`${data.nomor_transaksi} - ${data.customer_name}`, data.penjualan_transaction_id, true, true);
                $('#returnTransaction').append(option).trigger('change').prop('disabled', true);
                const selected = Object.fromEntries((data.details || []).map(detail => [detail.penjualan_transaction_detail_id, detail]));
                renderTransaction(response.transaction, selected);
            }).fail(xhr => {
                $('#salesReturnModal').modal('hide');
                showAjaxError(xhr, 'Draft retur gagal dimuat.');
            });
        }

        function showReturn(id) {
            $('#salesReturnDetailTitle').text('Memuat retur...');
            $('#salesReturnDetailBody').html('<div class="text-center py-5"><i class="mdi mdi-loading mdi-spin fs-3"></i></div>');
            $('#salesReturnDetailModal').modal('show');
            $.get(urls.show.replace(':id', id)).done(renderReturnDetail)
                .fail(xhr => showAjaxError(xhr, 'Detail retur gagal dimuat.'));
        }

        function detailInfo(label, value) {
            return `<div class="sales-return-detail-info"><small>${escapeHtml(label)}</small><strong>${escapeHtml(value || '-')}</strong></div>`;
        }

        function renderReturnDetail(data) {
            $('#salesReturnDetailTitle').text(data.nomor_retur);
            const rows = (data.details || []).map(detail => {
                const batches = (detail.batches || []).map(batch => `${escapeHtml(batch.no_batch || '-')} (${formatNumber(batch.qty_stok)} ${escapeHtml(detail.satuan_stok || '')})`).join('<br>');
                return `<tr><td><strong>${escapeHtml(detail.nama_obat)}</strong><small>${escapeHtml(detail.kode_obat || '-')}</small></td><td class="text-end">${formatNumber(detail.qty_jual)} ${escapeHtml(detail.satuan_jual || '')}</td><td>${batches || '-'}</td><td class="text-end">${formatCurrency(detail.subtotal_gross)}</td><td class="text-end">${formatCurrency(Number(detail.diskon_item_nominal) + Number(detail.diskon_transaksi_nominal))}</td><td class="text-end"><strong>${formatCurrency(detail.total)}</strong></td><td>${escapeHtml(detail.alasan_item || '-')}</td></tr>`;
            }).join('');
            const cancellation = data.status === 'cancelled' ? `<div class="alert alert-danger mt-3 mb-0"><strong>Dibatalkan ${escapeHtml(data.cancelled_at || '')} oleh ${escapeHtml(data.cancelled_by || '-')}</strong><br>${escapeHtml(data.cancellation_reason || '-')}</div>` : '';
            $('#salesReturnDetailBody').html(`
                <div class="sales-return-detail-grid">
                    ${detailInfo('Status', data.status === 'posted' ? 'Posted' : data.status === 'cancelled' ? 'Dibatalkan' : 'Draft')}
                    ${detailInfo('Transaksi', data.nomor_transaksi)}
                    ${detailInfo('Pelanggan', data.customer_name)}
                    ${detailInfo('Cabang', data.branch)}
                    ${detailInfo('Tanggal retur', data.tanggal_retur)}
                    ${detailInfo('Metode refund', data.refund_method_label)}
                    ${detailInfo('Referensi refund', data.refund_reference)}
                    ${detailInfo('Dibuat oleh', data.created_by)}
                </div>
                <div class="alert alert-light border"><strong>Alasan retur</strong><br>${escapeHtml(data.alasan || '-')} ${data.catatan ? `<hr><strong>Catatan</strong><br>${escapeHtml(data.catatan)}` : ''}</div>
                <div class="table-responsive"><table class="table sales-return-items-table sales-return-detail-items-table"><thead><tr><th>Obat</th><th class="text-end">Qty</th><th>Batch Asal</th><th class="text-end">Bruto</th><th class="text-end">Diskon</th><th class="text-end">Refund</th><th>Alasan Item</th></tr></thead><tbody>${rows}</tbody></table></div>
                <div class="sales-return-detail-total">
                    <div><span>Subtotal bruto</span><strong>${formatCurrency(data.subtotal_gross)}</strong></div>
                    <div><span>Diskon item</span><strong>- ${formatCurrency(data.diskon_item_total)}</strong></div>
                    <div><span>Diskon transaksi</span><strong>- ${formatCurrency(data.diskon_transaksi_total)}</strong></div>
                    <div><span>Pajak</span><strong>${formatCurrency(data.pajak_total)}</strong></div>
                    <div><span>Total refund</span><strong>${formatCurrency(data.grand_total)}</strong></div>
                </div>${cancellation}`);
        }

        function postReturn(id) {
            Swal.fire({ title: 'Posting retur?', text: 'Stok akan dikembalikan ke batch penjualan asal.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Posting', cancelButtonText: 'Kembali', reverseButtons: true })
                .then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: urls.post.replace(':id', id), type: 'PUT' })
                        .done(response => { notifySuccess(response.message); table.ajax.reload(null, false); })
                        .fail(xhr => showAjaxError(xhr, 'Retur gagal diposting.'));
                });
        }

        function cancelReturn(id) {
            Swal.fire({ title: 'Batalkan retur?', text: 'Stok yang telah dikembalikan akan dikeluarkan lagi dari batch.', input: 'textarea', inputLabel: 'Alasan pembatalan', inputPlaceholder: 'Wajib diisi', showCancelButton: true, confirmButtonText: 'Batalkan Retur', cancelButtonText: 'Kembali', confirmButtonColor: '#cf5663', reverseButtons: true, preConfirm: value => { if (!value?.trim()) { Swal.showValidationMessage('Alasan pembatalan wajib diisi.'); return false; } return value.trim(); } })
                .then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: urls.cancel.replace(':id', id), type: 'PUT', data: { reason: result.value } })
                        .done(response => { notifySuccess(response.message); table.ajax.reload(null, false); })
                        .fail(xhr => showAjaxError(xhr, 'Retur gagal dibatalkan.'));
                });
        }

        function deleteReturn(id) {
            Swal.fire({ title: 'Hapus draft retur?', text: 'Draft yang dihapus tidak dapat dikembalikan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Kembali', confirmButtonColor: '#cf5663', reverseButtons: true })
                .then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: urls.destroy.replace(':id', id), type: 'DELETE' })
                        .done(response => { notifySuccess(response.message); table.ajax.reload(null, false); })
                        .fail(xhr => showAjaxError(xhr, 'Draft gagal dihapus.'));
                });
        }

        function notifySuccess(message) {
            Swal.fire({ icon: 'success', title: message, toast: true, position: 'top-end', timer: 2800, timerProgressBar: true, showConfirmButton: false });
        }

        function showAjaxError(xhr, fallback) {
            const errors = xhr.responseJSON?.errors || {};
            const first = Object.values(errors)[0];
            Swal.fire('Gagal', (Array.isArray(first) ? first[0] : first) || xhr.responseJSON?.message || fallback, 'error');
        }

        $('#returnSearch').on('input', function() { clearTimeout(searchTimer); searchTimer = setTimeout(() => table.ajax.reload(), 350); });
        $('[data-return-status]').on('click', function() { $('[data-return-status]').removeClass('is-active'); $(this).addClass('is-active'); table.ajax.reload(); });
        $('#returnDateStart, #returnDateEnd, #returnBranch').on('change', () => table.ajax.reload());
        $('#refreshSalesReturns').on('click', () => table.ajax.reload(null, false));
        $('#resetReturnFilters').on('click', function() { $('#returnSearch, #returnDateStart, #returnDateEnd').val(''); if ($('#returnBranch').is('select')) $('#returnBranch').val(''); $('[data-return-status]').removeClass('is-active').first().addClass('is-active'); table.ajax.reload(); });
        $('#salesReturnModal, #salesReturnDetailModal').on('shown.bs.modal', function() {
            $(this).find('.modal-body').scrollTop(0);
        });

        if (initialTransactionId > 0) openForm(initialTransactionId);
    });
</script>
