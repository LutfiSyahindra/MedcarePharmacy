<script>
    $(document).ready(function() {
        const ui = window.MasterObatUI || {};
        let currentStatus = 'all';
        let latestSummary = {};
        let satuanOptions = [];
        let satuanRequest = null;
        let activeObatRow = null;
        let konversiTable = null;

        if ($.fn.dropify) {
            $('#myDropify').dropify();
        }

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        function escapeHtml(value) {
            if (ui.escapeHtml) {
                return ui.escapeHtml(value);
            }

            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatNumber(value) {
            if (ui.formatNumber) {
                return ui.formatNumber(value);
            }

            const numeric = Number(String(value ?? 0).replace(/[^\d.-]/g, ''));
            return Number.isFinite(numeric) ? numeric.toLocaleString('id-ID') : '0';
        }

        function toast(icon, title, html = null) {
            if (ui.toast) {
                ui.toast(icon, title, html);
                return;
            }

            Swal.fire({
                icon: icon,
                title: title,
                html: html,
                toast: true,
                position: 'top-end',
                timer: icon === 'error' ? 4600 : 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }

        function clearValidation() {
            if (ui.clearValidation) {
                ui.clearValidation('#konversiForm');
                return;
            }

            $('#konversiForm').find('.invalid-feedback').text('');
            $('#konversiForm').find('.form-control, .form-select').removeClass('is-invalid');
            $('#konversiForm').find('.obat-field').removeClass('has-error');
        }

        function markValidation(errors) {
            const messages = [];
            clearValidation();

            Object.keys(errors || {}).forEach(function(key) {
                const message = errors[key][0] || 'Field tidak valid';
                const parts = key.split('.');
                const field = parts[0];
                const index = Number(parts[1]);
                const $row = Number.isInteger(index) ? $('#input-wrapper .input-group-item').eq(index) : $('#input-wrapper .input-group-item').first();
                const $input = $row.find(`[name="${field}[]"]`).first();

                if ($input.length) {
                    $input.addClass('is-invalid');
                    $input.closest('.obat-field').addClass('has-error');
                    $input.closest('.obat-field').find('.invalid-feedback').first().text(message);
                }

                messages.push(message);
            });

            if (messages.length) {
                toast('error', 'Validasi Gagal', messages.join('<br>'));
            }
        }

        function updateSummary(summary) {
            latestSummary = summary || latestSummary || {};

            $('#konversiTotalObat').text(formatNumber(latestSummary.total_obat || 0));
            $('#konversiWith').text(formatNumber(latestSummary.sudah_konversi || 0));
            $('#konversiWithout').text(formatNumber(latestSummary.belum_konversi || 0));

            if (konversiTable && konversiTable.page) {
                const info = konversiTable.page.info();
                $('#konversiFiltered').text(formatNumber(info.recordsDisplay || 0));
            }
        }

        function renderStatus(row) {
            const isReady = row.has_konversi;
            const icon = isReady ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline';
            const className = isReady ? 'is-ready' : 'is-empty';
            const countText = isReady ? `${formatNumber(row.conversion_count)} satuan` : 'Perlu diisi';

            return `
                <span class="konversi-status-badge ${className}">
                    <i class="mdi ${icon}"></i>
                    ${escapeHtml(row.status_label)}
                    <small>${escapeHtml(countText)}</small>
                </span>
            `;
        }

        function renderConversions(row) {
            if (!row.has_konversi || !Array.isArray(row.conversions) || !row.conversions.length) {
                return `
                    <button type="button" class="konversi-inline-empty" onclick="manageKonversiObat(${row.id})">
                        <i class="mdi mdi-plus-circle-outline"></i>
                        Isi konversi sekarang
                    </button>
                `;
            }

            const chips = row.conversions.map(function(item) {
                return `
                    <span class="konversi-chip ${Number(item.is_default) === 1 ? 'is-default' : ''}" title="${escapeHtml(item.label)}">
                        <i class="mdi ${Number(item.is_default) === 1 ? 'mdi-star-outline' : 'mdi-swap-horizontal-bold'}"></i>
                        ${escapeHtml(item.label)}
                    </span>
                `;
            }).join('');

            return `<div class="konversi-chip-list">${chips}</div>`;
        }

        function findTableRow(obatId) {
            const target = String(obatId);
            const rows = konversiTable.rows().data().toArray();

            return rows.find((row) => String(row.id) === target);
        }

        function setStatusFilter(status) {
            currentStatus = status || 'all';
            $('.konversi-status-filter .btn').removeClass('is-active');
            $(`.konversi-status-filter .btn[data-status="${currentStatus}"]`).addClass('is-active');
            konversiTable.ajax.reload(null, true);
        }

        konversiTable = $('#tableKonversi').DataTable((ui.dataTableOptions || function(options) {
            return options;
        })({
            ajax: {
                url: "{{ route("konversiSatuanObat.table") }}",
                type: "GET",
                data: function(data) {
                    data.status = currentStatus;
                },
                dataSrc: function(json) {
                    updateSummary(json.summary || {});
                    return json.data || [];
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'search_text',
                    name: 'search_text',
                    render: function(data, type, row) {
                        if (type !== 'display') {
                            return data || '';
                        }

                        const subtitle = `${row.kode_obat || '-'} - Satuan stok: ${row.satuan_stok || 'PCS'}`;

                        if (ui.identity) {
                            return ui.identity(row.nama_obat, subtitle);
                        }

                        return `<strong>${escapeHtml(row.nama_obat || '-')}</strong><br><small>${escapeHtml(subtitle)}</small>`;
                    }
                },
                {
                    data: 'satuan_stok',
                    name: 'satuan_stok',
                    render: function(data, type) {
                        if (type !== 'display') return data;

                        if (ui.tagBadge) {
                            return ui.tagBadge(data || 'PCS', 'mdi-scale-balance');
                        }

                        return `<span class="badge bg-light text-dark">${escapeHtml(data || 'PCS')}</span>`;
                    }
                },
                {
                    data: 'status_label',
                    name: 'status_label',
                    render: function(data, type, row) {
                        if (type !== 'display') {
                            return `${data || ''} ${row.status_key || ''}`;
                        }

                        return renderStatus(row);
                    }
                },
                {
                    data: 'conversion_summary',
                    name: 'conversion_summary',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;

                        return renderConversions(row);
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        }));

        $('.dataTables_filter').hide();

        let searchTimer = null;
        $('#searchKonversi').on('input', function() {
            const value = this.value;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                konversiTable.search(value).draw();
            }, 220);
        });

        $('.konversi-status-filter .btn').on('click', function() {
            setStatusFilter($(this).data('status'));
        });

        $('.konversi-hero-filter').on('click', function() {
            setStatusFilter($(this).data('status'));
            document.querySelector('.obat-table-section')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        $('.obat-refresh-table').on('click', function() {
            const $button = $(this);
            $button.addClass('disabled');
            konversiTable.ajax.reload(function() {
                $button.removeClass('disabled');
            }, false);
        });

        $('#tableKonversi tbody').on('click', 'tr', function(event) {
            if ($(event.target).closest('button, a, input, label, .form-check').length) {
                return;
            }

            $(this).toggleClass('is-selected');
        });

        konversiTable.on('draw', function() {
            updateSummary(latestSummary);

            if (ui.initTooltips) {
                ui.initTooltips();
            }
        });

        function loadSatuanOptions() {
            if (satuanOptions.length) {
                return $.Deferred().resolve(satuanOptions).promise();
            }

            if (satuanRequest) {
                return satuanRequest;
            }

            satuanRequest = $.ajax({
                url: "{{ route("konversiSatuanObat.getSatuan") }}",
                type: "GET"
            }).then(function(data) {
                satuanOptions = data || [];
                return satuanOptions;
            }).always(function() {
                satuanRequest = null;
            });

            return satuanRequest;
        }

        function satuanOptionsHtml(selectedId = null) {
            const selectedValue = String(selectedId || '');
            let html = '<option value="">-- Pilih Satuan --</option>';

            satuanOptions.forEach(function(item) {
                const selected = String(item.id) === selectedValue ? 'selected' : '';
                html += `<option value="${escapeHtml(item.id)}" ${selected}>${escapeHtml(item.nama)}</option>`;
            });

            return html;
        }

        function initSatuanSelect($select, selectedId = null) {
            loadSatuanOptions().then(function() {
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }

                $select.html(satuanOptionsHtml(selectedId));

                if ($.fn.select2) {
                    $select.select2({
                        dropdownParent: $('#konversiModal'),
                        width: '100%',
                        placeholder: '-- Pilih Satuan --',
                        allowClear: true
                    });
                }

                $select.val(selectedId || '').trigger('change.select2');
                updateLivePreview();
            });
        }

        function getKonversiRow(data = {}) {
            const id = data.id || '';
            const konversi = data.konversi || '';
            const isDefault = Number(data.is_default || 0) === 1;

            return `
                <div class="konversi-input-card input-group-item mb-3">
                    <input type="hidden" name="conversion_id[]" value="${escapeHtml(id)}">
                    <div class="konversi-input-card-header">
                        <strong><i class="mdi mdi-swap-horizontal-bold"></i> Satuan Konversi</strong>
                        <button type="button" class="btn btn-sm btn-light remove-input" title="Hapus baris">
                            <i class="mdi mdi-trash-can-outline me-1"></i>Hapus
                        </button>
                    </div>
                    <div class="konversi-input-card-body">
                        <div class="obat-fields">
                            <div class="obat-field is-satuan">
                                <label class="form-label">Satuan Pembelian</label>
                                <div class="obat-input-shell">
                                    <span class="obat-input-icon"><i class="mdi mdi-package-variant"></i></span>
                                    <select class="form-select satuanSelect" name="satuan_id[]" required>
                                        <option value="">Memuat data...</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="obat-field is-konversi">
                                <label class="form-label">Isi Konversi</label>
                                <div class="obat-input-shell">
                                    <span class="obat-input-icon"><i class="mdi mdi-calculator-variant-outline"></i></span>
                                    <input class="form-control konversiValue" type="number" name="konversi[]" value="${escapeHtml(konversi)}" min="1" placeholder="Contoh: 10" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="obat-field is-default">
                                <label class="form-label">Default</label>
                                <div class="konversi-default-box">
                                    <input type="hidden" name="is_default[]" value="${isDefault ? '1' : '0'}" class="defaultHidden">
                                    <input class="form-check-input defaultCheck" type="checkbox" value="1" ${isDefault ? 'checked' : ''}>
                                    <label class="form-check-label">Utama</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function appendKonversiRow(data = {}) {
            const $row = $(getKonversiRow(data));
            $('#input-wrapper').append($row);
            initSatuanSelect($row.find('.satuanSelect'), data.satuan_id || null);
            updateLivePreview();
        }

        function ensureOneRow() {
            if ($('#input-wrapper .input-group-item').length === 0) {
                appendKonversiRow();
            }
        }

        function updateDefaultHidden() {
            $('#input-wrapper .defaultCheck').each(function() {
                $(this).closest('.konversi-default-box').find('.defaultHidden').val($(this).is(':checked') ? '1' : '0');
            });
        }

        function updateLivePreview() {
            updateDefaultHidden();

            const satuanStok = activeObatRow?.satuan_stok || 'satuan stok';
            const previews = [];

            $('#input-wrapper .input-group-item').each(function() {
                const $row = $(this);
                const satuan = $row.find('.satuanSelect option:selected').text();
                const satuanId = $row.find('.satuanSelect').val();
                const konversi = $row.find('.konversiValue').val();
                const isDefault = $row.find('.defaultCheck').is(':checked');

                if (!satuanId || !konversi) {
                    return;
                }

                previews.push(`
                    <span class="konversi-chip ${isDefault ? 'is-default' : ''}">
                        <i class="mdi ${isDefault ? 'mdi-star-outline' : 'mdi-swap-horizontal-bold'}"></i>
                        1 ${escapeHtml(satuan)} = ${formatNumber(konversi)} ${escapeHtml(satuanStok)}
                    </span>
                `);
            });

            $('#konversiLivePreview').html(previews.length
                ? `<div class="konversi-chip-list">${previews.join('')}</div>`
                : 'Belum ada baris konversi yang siap disimpan.'
            );
        }

        function openEditor(row) {
            activeObatRow = row;
            clearValidation();

            $('#activeObatId').val(row.id);
            $('#konversiModalLabel').text(`Kelola Konversi Satuan`);
            $('#konversiModalSubtitle').text('Tambah atau perbarui satuan pembelian untuk obat terpilih.');
            $('#konversiObatName').text(row.nama_obat || '-');
            $('#konversiObatMeta').text(`${row.kode_obat || '-'} - Satuan stok: ${row.satuan_stok || 'PCS'}`);
            $('#konversiModalNote').text(`Contoh: 1 Box = 100 ${row.satuan_stok || 'satuan stok'}, 1 Strip = 10 ${row.satuan_stok || 'satuan stok'}.`);
            $('#konversiObatStatus')
                .removeClass('konversi-empty-badge konversi-status-badge is-ready is-empty')
                .addClass(row.has_konversi ? 'konversi-status-badge is-ready' : 'konversi-empty-badge')
                .html(`
                    <i class="mdi ${row.has_konversi ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline'}"></i>
                    ${escapeHtml(row.status_label || 'Belum Ada')}
                `);

            $('#input-wrapper').empty();

            if (Array.isArray(row.conversions) && row.conversions.length) {
                row.conversions.forEach(function(item) {
                    appendKonversiRow(item);
                });
            } else {
                appendKonversiRow();
            }

            $('#submitForm').html('<i class="mdi mdi-content-save-outline"></i>Simpan Konversi');
            $('#konversiModal').modal('show');
            updateLivePreview();
        }

        window.manageKonversiObat = function(obatId) {
            const row = findTableRow(obatId);

            if (!row) {
                toast('warning', 'Data obat belum siap', 'Silakan refresh tabel lalu coba kembali.');
                return;
            }

            openEditor(row);
        };

        $(document).on('click', '#addInput', function() {
            appendKonversiRow();
        });

        $(document).on('change', '.defaultCheck', function() {
            if ($(this).is(':checked')) {
                $('#input-wrapper .defaultCheck').not(this).prop('checked', false);
            }

            updateLivePreview();
        });

        $(document).on('change input', '.satuanSelect, .konversiValue', function() {
            updateLivePreview();
        });

        $(document).on('click', '.remove-input', function() {
            const $row = $(this).closest('.input-group-item');
            const conversionId = $row.find('input[name="conversion_id[]"]').val();

            if (!conversionId) {
                if ($('#input-wrapper .input-group-item').length === 1) {
                    $row.find('select').val('').trigger('change');
                    $row.find('input[type="number"]').val('');
                    $row.find('.defaultCheck').prop('checked', false);
                    updateLivePreview();
                    return;
                }

                $row.remove();
                updateLivePreview();
                return;
            }

            deleteConversion(conversionId, $row);
        });

        function deleteConversion(id, $row = null) {
            Swal.fire({
                title: 'Hapus konversi?',
                text: 'Satuan konversi ini akan dihapus dari obat terkait.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route("konversiSatuanObat.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (!response.success) {
                            toast('error', 'Gagal menghapus', response.message || 'Konversi tidak dapat dihapus.');
                            return;
                        }

                        if ($row && $row.length) {
                            $row.remove();
                            ensureOneRow();
                            updateLivePreview();
                        }

                        konversiTable.ajax.reload(null, false);
                        toast('success', 'Konversi dihapus');
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Konversi tidak dapat dihapus. Pastikan data ini belum dipakai transaksi.';
                        toast('error', 'Gagal menghapus', message);
                    }
                });
            });
        }

        window.deleteKonversiSatuanObat = function(id) {
            deleteConversion(id);
        };

        $('#konversiForm').on('submit', function(e) {
            e.preventDefault();

            const obatId = $('#activeObatId').val();

            if (!obatId) {
                toast('warning', 'Pilih obat dulu', 'Klik tombol kelola pada salah satu obat di tabel.');
                return;
            }

            updateDefaultHidden();
            clearValidation();

            const $button = $('#submitForm');
            const normalHtml = $button.data('normal-html') || $button.html();

            if (ui.setButtonLoading) {
                ui.setButtonLoading($button, true, 'Menyimpan...', normalHtml);
            } else {
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Menyimpan...');
            }

            $.ajax({
                url: "{{ route("konversiSatuanObat.sync", ":id") }}".replace(':id', obatId),
                method: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    $('#konversiModal').modal('hide');
                    konversiTable.ajax.reload(null, false);
                    toast('success', response.message || 'Konversi berhasil disimpan');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        markValidation(xhr.responseJSON.errors || {});
                        return;
                    }

                    toast('error', 'Gagal menyimpan', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan konversi.');
                },
                complete: function() {
                    if (ui.setButtonLoading) {
                        ui.setButtonLoading($button, false, 'Menyimpan...', normalHtml);
                    } else {
                        $button.prop('disabled', false).html(normalHtml);
                    }
                }
            });
        });

        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("konversiSatuanObat.exportTemplate") }}";
        });

        $('#submitFormExcell').on('click', function() {
            let fileInput = $('#myDropify')[0];
            let file = fileInput.files[0];

            if (!file) {
                $('#myDropify').addClass('shake border-danger');

                setTimeout(() => {
                    $('#myDropify').removeClass('shake border-danger');
                }, 600);

                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih file Excel terlebih dahulu!',
                });

                return;
            }

            let formData = new FormData();
            formData.append('file', file);

            Swal.fire({
                title: 'Mengupload File...',
                html: `
                    <div class="progress" style="height: 20px;">
                        <div id="uploadProgressBar"
                            class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <p class="mt-2 mb-0 text-muted">Mohon tunggu, proses import sedang berlangsung.</p>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#uploadProgressBar')
                                .css('width', percentComplete + '%')
                                .text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                url: "{{ route("konversiSatuanObat.import") }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: `
                                <p>${response.added} data berhasil ditambahkan.</p>
                                <p>${response.skipped} data dilewati (sudah ada).</p>
                            `,
                            timer: 2500,
                            showConfirmButton: false,
                            willClose: () => {
                                $('#konversiModalExcell').modal('hide');
                                konversiTable.ajax.reload(null, false);
                            }
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan saat import data.',
                    });
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengupload file: ' + xhr.responseText,
                    });
                }
            });
        });
    });
</script>
