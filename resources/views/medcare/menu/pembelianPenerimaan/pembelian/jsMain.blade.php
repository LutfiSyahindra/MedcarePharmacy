<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {

        // ==== Inisiasi Variable Global dan Function ====
        // --- Variabel global
        let editMode = false;

        // --- Variabel select2
        let DistributorSelect = $('select[name="distributor_id"]');
        let PembelianSelect2Parent = $('#pembelianModal');

        // --- Inisialisasi flatpickr   
        flatpickr("#flatpickr-date", {
            dateFormat: "d-m-Y"
        });

        // --- Inisialisasi Select2
        $('.js-example-basic-single').select2({
            placeholder: "-- Pilih --",
            allowClear: false,
            width: 'resolve',
            dropdownParent: PembelianSelect2Parent
        });

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        function detailItemTemplate(options = {}) {
            let selectClass = options.selectClass ? ` ${options.selectClass}` : '';
            let satuanAttr = options.satuanTerpilih ? ` data-satuan-terpilih="${options.satuanTerpilih}"` : '';
            let loadingOption = options.loadingOption ?
                `<option value="${options.obatId || ''}">Loading...</option>` : '';

            return `
                <div class="detail-item purchase-detail-card"${satuanAttr}>
                    <div class="purchase-detail-card-head">
                        <div>
                            <span class="purchase-detail-number">1</span>
                            <strong>Item Obat</strong>
                            <small>Pilih obat, satuan, qty, dan harga estimasi.</small>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                            <i class="mdi mdi-trash-can-outline"></i> Hapus
                        </button>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Obat</label>
                            <select class="js-example-basic-single form-select${selectClass}" data-width="100%" name="obat_id[]" required>
                                ${loadingOption}
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Satuan</label>
                            <select class="form-select satuan-select" name="satuan_id[]" disabled required>
                                <option value="">-- Pilih Satuan --</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label class="form-label">Qty</label>
                            <input type="number" class="form-control" name="qty[]" min="1" value="${options.qty ?? 1}" required>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label class="form-label">Harga Estimasi</label>
                            <input type="number" class="form-control harga_estimasi" name="harga_estimasi[]" min="0" step="0.01" value="${options.harga ?? 0}">
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label class="form-label">Subtotal</label>
                            <input type="number" class="form-control subtotal" name="subtotal[]" value="${options.subtotal ?? ''}" readonly>
                        </div>
                    </div>
                </div>
            `;
        }

        function refreshDetailNumbers() {
            $('#detail-wrapper .detail-item').each(function(index) {
                $(this).find('.purchase-detail-number').text(index + 1);
            });
        }

        $(document).on('select2:opening', '#pembelianModal select[name="obat_id[]"]', function() {
            let modalBody = $('#pembelianModal .modal-body');
            let row = $(this).closest('.detail-item');

            if (!modalBody.length || !row.length) {
                return;
            }

            modalBody.scrollTop(modalBody.scrollTop() + row.position().top - 96);
        });
        // ==== End Inisiasi Variable Global dan Function ====

        // =================== Inisiasi Modal ===================
        // --- Reset modal ketika dibuka
        $('#pembelianModal').on('show.bs.modal', function() {
            let form = $('#pembelianForm');
            $('#pembelianModalLabel').text('Form Purchase Order');
            form.trigger('reset');

            $('#submitForm').html('<i class="mdi mdi-content-save-outline"></i> Simpan Purchase Order');
            $('#total_estimasi').text(formatRupiah(0));
            $('#total_estimasi_input').val(0);
            $('#pembelian_id').val('');

            // Reset error state
            form.find('.invalid-feedback').text('');
            form.find('.form-control, .form-select').removeClass('is-invalid');

            // Reset detail-wrapper jadi hanya 1 baris kosong
            $('#detail-wrapper').html(detailItemTemplate());
            hitungTotal();

            // Generate No PO hanya kalau TAMBAH
            $(this).one('shown.bs.modal', function() {
                if (!editMode) {
                    $.get('{{ route("pembelian.generateNoPO") }}', function(res) {
                        form.find('input[name="no_po"]').val(res);
                    });
                }
            });

            let $obatSelects = $('#detail-wrapper').find('select[name="obat_id[]"]');
            loadObatInto($obatSelects);
        });

        // --- Reset modal ketika ditutup
        $('#pembelianModal').on('hide.bs.modal', function() {
            editMode = false;
            $('#pembelian_id').val('');

        })
        // =================== End Inisiasi Modal ===============

        // =================== Inisiasi Event Handler ===================
        // --- Tambah baris detail obat baru
        $(document).on('click', '#addDetail', function() {
            let newDetail = detailItemTemplate();
            $('#detail-wrapper').append(newDetail);
            refreshDetailNumbers();
            hitungTotal();
            // 3. Ambil select obat di BARIS BARU saja
            let $newSelect = $('#detail-wrapper .detail-item').last().find('select[name="obat_id[]"]');

            // 4. Jalankan load obat + Select2 hanya untuk select BARU
            loadObatInto($newSelect);
        });

        // --- Hapus baris detail obat
        $(document).on('click', '.remove-detail', function() {
            if ($('.detail-item').length > 1) {
                $(this).closest('.detail-item').remove();
                refreshDetailNumbers();
                hitungTotal();
            }
        });

        // --- Hitung subtotal dan total otomatis
        $(document).on('input', '.harga_estimasi, [name="qty[]"]', function() {
            const row = $(this).closest('.detail-item');
            const qty = parseFloat(row.find('[name="qty[]"]').val()) || 0;
            const harga = parseFloat(row.find('.harga_estimasi').val()) || 0;
            const subtotal = qty * harga;
            row.find('.subtotal').val(subtotal.toFixed(2));
            hitungTotal();
        });

        // --- Fungsi hitung total estimasi keseluruhan
        function hitungTotal() {
            let total = 0;
            let totalQty = 0;
            $('.subtotal').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('[name="qty[]"]').each(function() {
                totalQty += parseFloat($(this).val()) || 0;
            });
            let itemCount = $('.detail-item').length;
            let average = itemCount > 0 ? total / itemCount : 0;

            refreshDetailNumbers();
            $('#total_estimasi').text(formatRupiah(total));
            $('#total_estimasi_input').val(total);
            $('#purchaseModalLineCount, #purchaseModalItemCount').text(itemCount.toLocaleString('id-ID'));
            $('#purchaseModalQtyCount').text(totalQty.toLocaleString('id-ID'));
            $('#purchaseModalAverage').text(formatRupiah(average));
        }

        // --- Get data distributor
        DistributorSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("pembelian.getDistributor") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                DistributorSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Distributor --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    DistributorSelect.append(
                        '<option value="">Tidak ada Distributor tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    DistributorSelect.append(option);
                });

                DistributorSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Distributor:', xhr);
                DistributorSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        // --- Get data obat
        function loadObatInto($select, selectedValue = null) {
            if (!$select || $select.length === 0) return;

            $select.each(function() {
                $(this).prop('disabled', true).html('<option>Memuat data...</option>');
            });

            $.ajax({
                url: "{{ route("pembelian.getObat") }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {

                    let data = Array.isArray(response) ? response : (response.data || []);

                    $select.each(function() {
                        let s = $(this);

                        s.prop('disabled', false)
                            .empty()
                            .append('<option value="">-- Pilih Obat --</option>');

                        if (!data || data.length === 0) {
                            s.append('<option value="">Tidak ada Obat tersedia</option>');
                        } else {
                            data.forEach(item => {
                                let text = (item.nama_obat || 'Tanpa Nama') +
                                    (item.kode_obat ? (' (' + item.kode_obat +
                                        ')') : '');

                                let option = new Option(text, item.id, false,
                                    false);

                                // Simpan harga beli
                                $(option).attr('data-harga', item.harga_beli || 0);

                                s.append(option);
                            });
                        }

                        // destroy select2 sebelumnya
                        if (s.hasClass('select2-hidden-accessible')) {
                            s.select2('destroy');
                        }

                        // init select2
                        s.select2({
                            placeholder: "-- Pilih Obat --",
                            width: 'resolve',
                            dropdownParent: PembelianSelect2Parent
                        });

                        // ========== FIX PALING PENTING ==========
                        // Set nilai obat yang harus dipilih saat edit
                        if (selectedValue) {
                            s.val(selectedValue).trigger('change');
                        }
                        // =========================================
                    });
                },
                error: function(xhr) {
                    console.error('Error Obat:', xhr);

                    $select.each(function() {
                        let s = $(this);

                        s.prop('disabled', false)
                            .empty()
                            .append('<option value="">Gagal memuat data obat</option>');

                        if (s.hasClass('select2-hidden-accessible')) {
                            s.select2('destroy');
                        }

                        s.select2({
                            placeholder: "-- Pilih Obat --",
                            width: 'resolve',
                            dropdownParent: PembelianSelect2Parent
                        });
                    });
                }
            });
        }

        // Ketika pengguna memilih obat
        $(document).on('change', 'select[name="obat_id[]"]', function() {

            let row = $(this).closest('.detail-item');
            let obatId = $(this).val();
            let selectedOption = $(this).find('option:selected');

            let $satuanSelect = row.find('.satuan-select');

            // reset
            $satuanSelect
                .html('<option value="">-- Pilih Satuan --</option>')
                .prop('disabled', true);

            row.find('.harga_estimasi').val(0);
            row.find('.subtotal').val(0);

            if (!obatId) {
                hitungTotal();
                return;
            }

            $.ajax({
                url: "{{ route("pembelian.getKonversiSatuan") }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    obat_id: obatId
                },
                success: function(data) {
                    // ===== NORMALISASI (OBJECT / ARRAY) =====
                    let list = Array.isArray(data) ? data : [data];

                    if (!list.length || !list[0]?.satuan) {
                        fallbackHargaDefault();
                        return;
                    }

                    // urutkan konversi terkecil
                    list.sort((a, b) => a.konversi - b.konversi);

                    let options = '<option value="">-- Pilih Satuan --</option>';

                    list.forEach(item => {
                        options += `
                    <option value="${item.id}"
                            data-konversi="${item.konversi}">
                        ${item.satuan.nama}
                    </option>
                `;
                    });

                    $satuanSelect
                        .html(options)
                        .prop('disabled', false);

                    // ================= EDIT MODE =================
                    if (editMode) {
                        let satuanLama = row.data('satuan-terpilih');
                        if (satuanLama) {
                            $satuanSelect.val(satuanLama).trigger('change');
                        }
                    }
                    // ================= TAMBAH MODE =================
                    else {
                        $satuanSelect.prop('selectedIndex', 1).trigger('change');
                    }
                },
                error: function() {
                    fallbackHargaDefault();
                }
            });

            // ===== FALLBACK JIKA TIDAK ADA KONVERSI =====
            function fallbackHargaDefault() {
                let harga = parseFloat(selectedOption.data('harga')) || 0;
                let qty = parseFloat(row.find('[name="qty[]"]').val()) || 1;

                row.find('.harga_estimasi').val(harga);
                row.find('.subtotal').val((harga * qty).toFixed(2));

                hitungTotal();
            }
        });

        // --- Format Rupiah 
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(Number(angka) || 0);
        }

        function escapeHtml(value) {
            return String(value ?? '-').replace(/[&<>"']/g, function(character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [character];
            });
        }

        function getInitials(value) {
            let words = String(value || '-').trim().split(/\s+/).filter(Boolean);
            return words.slice(0, 2).map(word => word.charAt(0)).join('') || '-';
        }

        function updatePurchaseSummary(summary, recordsTotal) {
            let total = Number(summary.total ?? recordsTotal) || 0;
            let draft = Number(summary.draft) || 0;
            let waiting = Number(summary.waiting_approval) || 0;
            let pending = Number(summary.pending ?? (draft + waiting)) || 0;
            let approved = Number(summary.approved) || 0;
            let rejected = Number(summary.rejected) || 0;

            $('#purchaseTotalCount, #purchaseAllFilterCount').text(total.toLocaleString('id-ID'));
            $('#purchasePendingCount').text(pending.toLocaleString('id-ID'));
            $('#purchaseDraftFilterCount').text(draft.toLocaleString('id-ID'));
            $('#purchaseWaitingFilterCount').text(waiting.toLocaleString('id-ID'));
            $('#purchaseApprovedCount, #purchaseApprovedFilterCount').text(approved.toLocaleString('id-ID'));
            $('#purchaseRejectedFilterCount').text(rejected.toLocaleString('id-ID'));
            $('#purchaseTotalValue').text(formatRupiah(summary.total_estimasi));
        }

        // --- Konversi Satuan
        $(document).on('change', '.satuan-select', function() {

            let row = $(this).closest('.detail-item');
            let selected = $(this).find(':selected');

            let hargaDasar = parseFloat(
                row.find('select[name="obat_id[]"] option:selected').data('harga')
            ) || 0;

            let konversi = parseFloat(selected.data('konversi')) || 1;
            let qty = parseFloat(row.find('[name="qty[]"]').val()) || 1;

            let harga = hargaDasar * konversi;

            row.find('.harga_estimasi').val(harga);
            row.find('.subtotal').val((harga * qty).toFixed(2));

            hitungTotal();
        });

        // =================== End Inisiasi Event Handler ===============

        // =================== Inisiasi DataTable ======================
        // --- DataTable
        let currentMonthStart = moment().startOf('month');
        let currentMonthEnd = moment().endOf('month');
        let purchaseDateStart = currentMonthStart.format('YYYY-MM-DD');
        let purchaseDateEnd = currentMonthEnd.format('YYYY-MM-DD');

        let PembelianTable = $('#tablePembelian').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [
                [3, 'desc']
            ],
            ajax: {
                url: "{{ route("pembelian.table") }}",
                type: "GET",
                data: function(request) {
                    request.date_start = purchaseDateStart;
                    request.date_end = purchaseDateEnd;
                },
                dataSrc: function(response) {
                    updatePurchaseSummary(response.summary || {}, response.recordsTotal);
                    return response.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `<span class="purchase-row-number">${escapeHtml(data)}</span>`;
                    }
                },
                {
                    data: 'approved_by',
                    name: 'approved_by',
                    render: function(data, type, row) {
                        let status = String(row.status || '').toLowerCase();

                        if (status === 'rejected') {
                            return `
                                <span class="purchase-approval is-rejected">
                                    <i class="mdi mdi-close-circle-outline"></i>
                                    Ditolak
                                </span>
                            `;
                        }

                        if (!data && status !== 'approved') {
                            return `
                                <span class="purchase-approval is-pending">
                                    <i class="mdi mdi-clock-outline"></i>
                                    Menunggu
                                </span>
                            `;
                        }

                        return `
                            <span class="purchase-approval is-approved" title="Approval tercatat">
                                <i class="mdi mdi-check-circle-outline"></i>
                                Disetujui
                                <br>
                                <small class="text-muted">
                                    ${data}
                                </small>
                            </span>
                        `;
                    }
                },
                {
                    data: 'no_po',
                    name: 'no_po',
                    render: function(data) {
                        return `
                            <span class="purchase-po-number">
                                <i class="mdi mdi-file-document-outline"></i>
                                ${escapeHtml(data)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'tanggal_po',
                    name: 'tanggal_po',
                    render: function(data) {
                        let date = moment(data);
                        let formattedDate = date.isValid() ? date.format('DD-MM-YYYY') :
                            escapeHtml(data);
                        return `
                            <span class="purchase-date">
                                <i class="mdi mdi-calendar-blank-outline"></i>
                                ${formattedDate}
                            </span>
                        `;
                    }
                },
                {
                    data: 'branch_id',
                    name: 'branch_id',
                    render: function(data) {
                        return `
                            <span class="purchase-badge is-branch">
                                <i class="mdi mdi-hospital-building"></i>
                                ${escapeHtml(data)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'distributor_id',
                    name: 'distributor_id',
                    render: function(data) {
                        let distributor = escapeHtml(data);
                        return `
                            <span class="purchase-badge is-distributor" title="${distributor}">
                                <i class="mdi mdi-truck-delivery-outline"></i>
                                ${distributor}
                            </span>
                        `;
                    }
                },
                {
                    data: 'total_estimasi',
                    name: 'total_estimasi',
                    render: function(data) {
                        return `
                            <span class="purchase-money">
                                <i class="mdi mdi-cash"></i>
                                ${formatRupiah(data)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        let status = String(data || '').toLowerCase();
                        let statusClass =
                            (status === 'approved' || status === 'selesai') ? 'is-approved' :
                            (status === 'draft' || status === 'waiting_approval') ? 'is-draft' :
                            status === 'diterima_sebagian' ? 'is-partial' :
                            status === 'rejected' ? 'is-rejected' :
                            'is-other';
                        let statusLabel =
                            status === 'approved' ? 'Disetujui' :
                            status === 'diterima_sebagian' ? 'Diterima Sebagian' :
                            status === 'selesai' ? 'Selesai' :
                            status === 'draft' ? 'Draft' :
                            status === 'waiting_approval' ? 'Menunggu Approval' :
                            status === 'rejected' ? 'Ditolak' :
                            (data || '-');
                        let statusIcon =
                            status === 'approved' ? 'mdi-check-circle-outline' :
                            status === 'diterima_sebagian' ? 'mdi-progress-check' :
                            status === 'selesai' ? 'mdi-package-variant-closed-check' :
                            (status === 'draft' || status === 'waiting_approval') ?
                            'mdi-file-clock-outline' :
                            status === 'rejected' ? 'mdi-close-circle-outline' :
                            'mdi-help-circle-outline';

                        return `
                            <span class="purchase-status ${statusClass}">
                                <i class="mdi ${statusIcon}"></i>
                                ${escapeHtml(statusLabel)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'catatan',
                    name: 'catatan',
                    render: function(data) {
                        let note = escapeHtml(data || '-');
                        return `<span class="purchase-note" title="${note}">${note}</span>`;
                    }
                },
                {
                    data: 'created_by',
                    name: 'created_by',
                    render: function(data) {
                        let user = escapeHtml(data || '-');
                        return `
                            <span class="purchase-user">
                                <span class="purchase-user-avatar">${escapeHtml(getInitials(data))}</span>
                                <span>${user}</span>
                            </span>
                        `;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `<div class="purchase-action-group">${data || ''}</div>`;
                    }
                }
            ],
            columnDefs: [{
                targets: [0, 10],
                className: 'text-center'
            }],
            drawCallback: function() {
                let table = $('#tablePembelian');
                table.find('.purchase-action-group .btn-approve-pembelian')
                    .attr({
                        title: 'Setujui purchase order',
                        'aria-label': 'Setujui purchase order'
                    });
                table.find('.purchase-action-group .btn-reject-pembelian')
                    .attr({
                        title: 'Tolak purchase order',
                        'aria-label': 'Tolak purchase order'
                    });
                table.find('.purchase-action-group .btn-reopen-pembelian')
                    .attr({
                        title: 'Buka approval purchase order',
                        'aria-label': 'Buka approval purchase order'
                    });
                table.find('.purchase-action-group .btn-edit-pembelian')
                    .attr({
                        title: 'Edit purchase order',
                        'aria-label': 'Edit purchase order'
                    });
                table.find('.purchase-action-group .btn-info')
                    .attr({
                        title: 'Lihat detail purchase order',
                        'aria-label': 'Lihat detail purchase order'
                    });
                table.find('.purchase-action-group .btn-danger')
                    .attr({
                        title: 'Hapus purchase order',
                        'aria-label': 'Hapus purchase order'
                    });
            },
            language: {
                processing: '<span class="d-inline-flex align-items-center gap-2"><i class="mdi mdi-loading mdi-spin"></i> Memuat purchase order...</span>',
                emptyTable: 'Belum ada purchase order.',
                zeroRecords: 'Purchase order yang dicari tidak ditemukan.',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        let purchaseSearchTimer;

        $('#searchPembelian').on('input', function() {
            let searchValue = this.value;
            $(this).closest('.purchase-search').toggleClass('has-value', Boolean(searchValue));
            clearTimeout(purchaseSearchTimer);
            purchaseSearchTimer = setTimeout(function() {
                PembelianTable.search(searchValue).draw();
            }, 250);
        });

        $('#clearPurchaseSearch').on('click', function() {
            $('#searchPembelian').val('').trigger('input').focus();
        });

        $('.purchase-filter-chip').on('click', function() {
            $('.purchase-filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            PembelianTable.column(7).search($(this).data('status') || '').draw();
        });

        function formatDateParameter(date) {
            return moment(date).format('YYYY-MM-DD');
        }

        function applyPurchaseDateRange(startDate, endDate) {
            purchaseDateStart = formatDateParameter(startDate);
            purchaseDateEnd = formatDateParameter(endDate);
            $('#purchaseDateRange').closest('.purchase-date-input').addClass('has-value');
            PembelianTable.ajax.reload();
        }

        let purchaseDatePicker = flatpickr('#purchaseDateRange', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            defaultDate: [
                purchaseDateStart,
                purchaseDateEnd
            ],
            locale: {
                rangeSeparator: ' - '
            },
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    $('#purchaseDatePreset').val('');
                    applyPurchaseDateRange(selectedDates[0], selectedDates[1]);
                }
            },
            onClose: function(selectedDates) {
                if (selectedDates.length === 1) {
                    this.clear();
                }
            }
        });

        $('#purchaseDatePreset').val('this_month');
        $('#purchaseDateRange').closest('.purchase-date-input').addClass('has-value');

        $('#clearPurchaseDateRange').on('click', function() {
            purchaseDateStart = '';
            purchaseDateEnd = '';
            purchaseDatePicker.clear();
            $('#purchaseDatePreset').val('');
            $('#purchaseDateRange').closest('.purchase-date-input').removeClass('has-value');
            PembelianTable.ajax.reload();
        });

        $('#purchaseDatePreset').on('change', function() {
            let preset = this.value;

            if (!preset) {
                return;
            }

            let today = moment().startOf('day');
            let startDate = today.clone();
            let endDate = today.clone();

            if (preset === '7days') {
                startDate.subtract(6, 'days');
            } else if (preset === '30days') {
                startDate.subtract(29, 'days');
            } else if (preset === 'this_month') {
                startDate.startOf('month');
                endDate.endOf('month');
            }

            purchaseDatePicker.setDate([
                startDate.format('YYYY-MM-DD'),
                endDate.format('YYYY-MM-DD')
            ], false);
            applyPurchaseDateRange(startDate, endDate);
        });

        $('#purchasePageLength').on('change', function() {
            PembelianTable.page.len(Number(this.value)).draw();
        });

        $('#refreshPurchaseTable').on('click', function() {
            $(this).addClass('is-loading').prop('disabled', true);
            PembelianTable.ajax.reload(null, false);
        });

        $('#tablePembelian').on('xhr.dt', function() {
            $('#refreshPurchaseTable').removeClass('is-loading').prop('disabled', false);
        });

        $('#scrollPurchaseTable').on('click', function() {
            document.getElementById('purchaseTableSection')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
        // =================== End Inisiasi DataTable ==================

        // =================== Inisiasi Action =========================
        // --- Submit form
        $('#pembelianForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let pembelian_id = $('#pembelian_id').val();

            let url = pembelian_id ?
                "{{ route("pembelian.update", ":id") }}".replace(':id', pembelian_id) :
                "{{ route("pembelian.store") }}";

            let method = pembelian_id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#pembelianModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#pembelianForm')[0].reset();
                        $('#pembelian_id').val('');
                        PembelianTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#pembelianForm').find('.invalid-feedback').text('');
                        $('#pembelianForm').find('.form-control').removeClass(
                            'is-invalid');

                        for (let key in errors) {
                            // contoh key: "code.0", "name.1"
                            let messages = errors[key];
                            errorMessages.push(messages[0]);

                            // cari input sesuai index
                            let parts = key.split('.');
                            let field = parts[0]; // code / name
                            let index = parts[1]; // index array

                            // ambil row ke-index lalu kasih error
                            let row = $('#input-wrapper .input-group-item').eq(index);
                            row.find(`input[name="${field}[]"]`).addClass('is-invalid');
                            row.find('.invalid-feedback').first().text(messages[0]);
                        }

                        // tampilkan semua error di toast juga
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            html: errorMessages.join('<br>'),
                            toast: true,
                            position: 'top-end',
                            timer: 4000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });
                    }
                }
            });
        });

        // --- Edit Pembelian
        window.approvePembelian = function(id) {
            Swal.fire({
                title: 'Setujui purchase order?',
                text: 'PO ini akan ditandai sudah disetujui oleh admin.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route("pembelian.approve", ":id") }}".replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: response.status === 'info' ? 'info' : 'success',
                            title: response.message || 'Pembelian berhasil disetujui.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        PembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyetujui PO.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Approval gagal',
                            text: message,
                        });
                    }
                });
            });
        };

        window.rejectPembelian = function(id) {
            Swal.fire({
                title: 'Tolak purchase order?',
                text: 'PO ini akan ditandai ditolak dan tidak masuk approval.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route("pembelian.reject", ":id") }}".replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: response.status === 'info' ? 'info' : 'success',
                            title: response.message || 'Pembelian berhasil ditolak.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        PembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menolak PO.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Reject gagal',
                            text: message,
                        });
                    }
                });
            });
        };

        window.reopenPembelian = function(id) {
            Swal.fire({
                title: 'Buka approval purchase order?',
                text: 'Status PO akan kembali menunggu approval sehingga bisa diedit ulang.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, buka approval',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route("pembelian.reopenApproval", ":id") }}".replace(':id', id),
                    type: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: response.status === 'info' ? 'info' : 'success',
                            title: response.message || 'Approval pembelian berhasil dibuka.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        PembelianTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat membuka approval PO.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Buka approval gagal',
                            text: message,
                        });
                    }
                });
            });
        };

        window.editPembelian = function(id) {

            editMode = true;

            $.ajax({
                url: "{{ route("pembelian.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {

                    let header = response;
                    let detail = response.details ?? [];

                    $('#pembelianModal').modal('show');
                    $('#pembelianModalLabel').text('Edit Purchase Order');
                    $('#submitForm').html(
                        '<i class="mdi mdi-content-save-edit-outline"></i> Update Purchase Order'
                    );

                    $('#pembelian_id').val(header.id);
                    $('input[name="no_po"]').val(header.no_po);
                    $('#distributor_id').val(header.distributor_id).trigger('change');

                    $('input[name="tanggal"]').val(
                        moment(header.tanggal_po).format('DD-MM-YYYY')
                    );

                    $('textarea[name="catatan"]').val(header.catatan ?? '');

                    $('#detail-wrapper').empty();

                    detail.forEach(item => {

                        let row = detailItemTemplate({
                            obatId: item.obat_id,
                            satuanTerpilih: item.satuan_konversi?.id,
                            qty: item.qty,
                            harga: item.harga_estimasi,
                            subtotal: item.subtotal,
                            selectClass: 'obatSelect',
                            loadingOption: true
                        });

                        $('#detail-wrapper').append(row);
                    });


                    // load obat + trigger change otomatis
                    $('.obatSelect').each(function(i) {
                        loadObatInto($(this), detail[i].obat_id);
                    });

                    hitungTotal();
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message ||
                        'Terjadi kesalahan saat memuat data pembelian.';

                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak bisa edit',
                        text: message,
                    });
                }
            });
        };

        // --- Detail
        window.lihatPembelian = function(id) {
            $.ajax({
                url: "{{ route("pembelian.show", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(res) {
                    const po = res?.data ?? res?.original ?? res;

                    if (!po || typeof po !== 'object' || !Array.isArray(po.details)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Detail PO tidak dapat ditampilkan',
                            text: 'Respons detail purchase order tidak valid. Silakan muat ulang halaman.'
                        });
                        return;
                    }

                    const detail = po.details;

                    // Header
                    $('#detail_no_po').val(po.no_po);
                    $('#detail_distributor').val(po.distributor_name ?? 'Tidak diketahui');
                    $('#detail_tanggal_po').val(po.tanggal_po ? moment(po.tanggal_po).format('DD-MM-YYYY') : '-');
                    $('#detail_catatan').val(po.catatan ?? '-');

                    // Kosongkan tabel
                    $('#detailObatTable tbody').empty();
                    // Tampilkan jumlah item
                    $('#detail_total_item').text(detail.length);


                    // Detail obat
                    detail.forEach(item => {
                        const unitName = item.satuan_konversi?.satuan?.nama ?? '-';

                        $('#detailObatTable tbody').append(`
                    <tr>
                        <td>${escapeHtml(item.nama_obat ?? '-')}</td>
                        <td>${escapeHtml(unitName)}</td>
                        <td>${Number(item.qty ?? 0).toLocaleString('id-ID')}</td>
                        <td>Rp ${Number(item.harga_estimasi ?? 0).toLocaleString('id-ID')}</td>
                        <td>Rp ${Number(item.subtotal ?? 0).toLocaleString('id-ID')}</td>
                    </tr>
                `);
                    });

                    // Total Estimasi
                    $('#detail_total_estimasi').text(
                        "Rp " + Number(po.total_estimasi).toLocaleString("id-ID")
                    );

                    // Tampilkan modal
                    $('#pembelianModalDetail').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Detail PO tidak dapat ditampilkan',
                        text: xhr.responseJSON?.message ||
                            (xhr.status === 404
                                ? 'Purchase order tidak ditemukan atau tidak dapat diakses dari branch Anda.'
                                : 'Terjadi kesalahan saat memuat detail purchase order.')
                    });
                }
            });
        };

        // --- Hapus
        window.deletePembelian = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'PO ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("pembelian.destroy", ":id") }}".replace(
                            ':id',
                            id),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Dihapus!',
                                    response.message,
                                    'success'
                                );
                                PembelianTable.ajax.reload(); // Reload DataTables
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menghapus PO.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
        // ==================== End Inisiasi Action ====================

    });
</script>
