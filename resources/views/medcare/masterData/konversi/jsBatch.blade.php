        let batchTargets = [];
        let batchPreviewRequest = null;
        let batchPreviewVersion = 0;
        let batchOptionsReady = false;
        let batchSaving = false;
        let batchOpenVersion = 0;
        let batchFieldKey = 0;

        function batchError(message = '') {
            $('#konversiBatchError').text(message).toggleClass('d-none', !message);
        }

        function batchRequestError(xhr) {
            const errors = xhr.responseJSON?.errors || {};
            return Object.values(errors).flat().join(' ') || xhr.responseJSON?.message || 'Data tidak dapat diproses. Silakan coba kembali.';
        }

        function invalidateBatchTargets() {
            batchPreviewVersion++;
            if (batchPreviewRequest) {
                batchPreviewRequest.abort();
                batchPreviewRequest = null;
            }
            batchTargets = [];
            $('#batchTargetsBody').empty();
            $('#batchTargetsContainer').addClass('d-none');
            $('#batchTargetSummary').text('Klik Lihat Target Obat untuk memeriksa pilihan terbaru.');
            $('#batchSaveButton').prop('disabled', true);
            $('#batchPreviewButton').prop('disabled', !batchOptionsReady);
            updateBatchConversionPreview();
        }

        function batchSelection() {
            return {
                scope: $('#batchScope').val(),
                satuan_stok_ids: $('#batchStockUnits').val() || [],
                obat_ids: $('#batchMedicines').val() || []
            };
        }

        function updateBatchConversionPreview() {
            const stockNames = [...new Set(batchTargets.map(row => row.satuan_stok))];
            const stocks = stockNames.length ? stockNames.join(' / ') : 'satuan stok masing-masing obat';
            const previews = [];
            $('#batchConversionRows .batch-conversion-row').each(function() {
                const $row = $(this);
                const unitId = $row.find('.batch-conversion-unit').val();
                const amount = $row.find('.batch-conversion-value').val();
                if (unitId && amount) {
                    const isDefault = $row.find('.batch-conversion-default').is(':checked');
                    previews.push(`<span class="konversi-chip ${isDefault ? 'is-default' : ''}">1 ${escapeHtml($row.find('.batch-conversion-unit option:selected').text())} = ${formatNumber(amount)} ${escapeHtml(stocks)}${isDefault ? ' (Default)' : ''}</span>`);
                }
            });
            $('#batchConversionPreview').html(previews.length
                ? `<div class="konversi-chip-list">${previews.join('')}</div>`
                : 'Isi satuan pembelian dan jumlah konversinya.');
        }

        function appendBatchConversion() {
            if ($('#batchConversionRows .batch-conversion-row').length >= 20) {
                batchError('Maksimal 20 satuan konversi dalam satu batch.');
                return;
            }
            const rowNumber = ++batchFieldKey;
            const $row = $(`
                <div class="konversi-input-card batch-conversion-row mb-3">
                    <div class="konversi-input-card-header">
                        <strong><i class="mdi mdi-swap-horizontal-bold"></i>Satuan Konversi</strong>
                        <button type="button" class="btn btn-sm btn-light batch-remove-conversion" title="Hapus baris"><i class="mdi mdi-trash-can-outline me-1"></i>Hapus</button>
                    </div>
                    <div class="konversi-input-card-body">
                        <div class="obat-fields">
                            <div class="obat-field is-satuan">
                                <label class="form-label" for="batchUnit${rowNumber}">Satuan pembelian</label>
                                <select id="batchUnit${rowNumber}" class="form-select batch-conversion-unit" aria-label="Satuan pembelian baris ${rowNumber}">${satuanOptionsHtml()}</select>
                            </div>
                            <div class="obat-field is-konversi">
                                <label class="form-label" for="batchValue${rowNumber}">Isi per satuan pembelian</label>
                                <input id="batchValue${rowNumber}" class="form-control batch-conversion-value" type="number" inputmode="numeric" min="1" max="2147483647" step="1" placeholder="Contoh: 10" aria-label="Isi konversi baris ${rowNumber}">
                            </div>
                            <div class="obat-field is-default">
                                <span class="form-label d-block">Prioritas PO</span>
                                <label class="konversi-default-box"><input class="form-check-input batch-conversion-default" type="checkbox">Utama untuk PO</label>
                            </div>
                        </div>
                    </div>
                </div>`);
            $('#batchConversionRows').append($row);
            if ($.fn.select2) {
                $row.find('.batch-conversion-unit').select2({
                    dropdownParent: $('#konversiBatchModal'), width: '100%',
                    placeholder: '-- Pilih Satuan --', allowClear: true
                });
            }
            updateBatchConversionPreview();
        }

        function fillBatchPicker($select, html, placeholder) {
            if ($select.data('select2')) $select.select2('destroy');
            $select.html(html);
            if ($.fn.select2) {
                $select.select2({ dropdownParent: $('#konversiBatchModal'), width: '100%', placeholder, allowClear: true });
            }
        }

        $('.konversi-open-batch').on('click', function() {
            if (batchSaving) return;
            const openVersion = ++batchOpenVersion;
            batchError();
            batchOptionsReady = false;
            $('#batchStockUnits, #batchMedicines').val([]).trigger('change.select2').prop('disabled', true);
            $('#batchScope').val(currentStatus === 'po_without' ? 'po_without' : (currentStatus === 'without' ? 'without' : 'all'));
            $('#batchConversionRows .batch-conversion-unit').each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
            });
            $('#batchConversionRows').empty();
            $('#batchAddConversion').prop('disabled', true);
            invalidateBatchTargets();
            $('#batchTargetSummary').text('Memuat pilihan satuan stok dan obat...');
            $('#konversiBatchModal').modal('show');

            $.when(loadSatuanOptions(), $.getJSON("{{ route('konversiSatuanObat.getObat') }}"))
                .done(function(units, medicineResponse) {
                    if (openVersion !== batchOpenVersion) return;
                    const medicines = medicineResponse[0] || [];
                    const stockIds = new Set(medicines.map(row => String(row.satuan_id)));
                    fillBatchPicker($('#batchStockUnits'), satuanOptions.filter(unit => stockIds.has(String(unit.id)))
                        .map(unit => `<option value="${escapeHtml(unit.id)}">${escapeHtml(unit.nama)}</option>`).join(''), 'Pilih satuan stok');
                    const unitNames = new Map(satuanOptions.map(unit => [String(unit.id), unit.nama]));
                    fillBatchPicker($('#batchMedicines'), medicines.map(row => `<option value="${escapeHtml(row.id)}">${escapeHtml(row.kode_obat || '-')} - ${escapeHtml(row.nama_obat)} (${escapeHtml(unitNames.get(String(row.satuan_id)) || 'Tanpa satuan stok')})</option>`).join(''), 'Cari dan pilih obat');
                    batchOptionsReady = true;
                    $('#batchStockUnits, #batchMedicines, #batchPreviewButton, #batchAddConversion').prop('disabled', false);
                    appendBatchConversion();
                    $('#batchTargetSummary').text('Pilih target, lalu klik Lihat Target Obat.');
                    if ($('#batchScope').val() === 'po_without') $('#batchPreviewButton').trigger('click');
                }).fail(function() {
                    if (openVersion !== batchOpenVersion) return;
                    batchError('Pilihan obat atau satuan gagal dimuat. Tutup dan buka kembali form batch.');
                    $('#batchTargetSummary').text('Pilihan target belum tersedia.');
                });
        });

        $('#batchStockUnits, #batchMedicines, #batchScope').on('change', function() {
            batchError();
            invalidateBatchTargets();
        });

        $('#batchPreviewButton').on('click', function() {
            batchError();
            invalidateBatchTargets();
            const selection = batchSelection();
            if (!selection.satuan_stok_ids.length && !selection.obat_ids.length && selection.scope !== 'po_without') {
                batchError('Pilih satuan stok atau obat untuk menentukan target batch.');
                return;
            }
            const version = batchPreviewVersion;
            $('#batchPreviewButton').prop('disabled', true);
            $('#batchTargetSummary').text('Memuat target obat...');
            batchPreviewRequest = $.ajax({
                url: "{{ route('konversiSatuanObat.batchPreview') }}", method: 'GET', data: selection,
                success: function(response) {
                    if (version !== batchPreviewVersion) return;
                    batchTargets = response.data || [];
                    $('#batchTargetSummary').text(`${formatNumber(batchTargets.length)} obat menjadi target batch.`);
                    $('#batchTargetsContainer').toggleClass('d-none', !batchTargets.length);
                    $('#batchTargetsBody').html(batchTargets.map(row => `<tr>
                        <td><strong>${escapeHtml(row.nama_obat)}</strong><br><small>${escapeHtml(row.kode_obat)}</small></td>
                        <td>${escapeHtml(row.satuan_stok)}</td>
                        <td>${escapeHtml(row.conversion_summary)}</td>
                        <td>${escapeHtml((row.purchase_orders || []).join(', ') || '-')}${Number(row.po_missing_unit_count) > 0 ? `<small class="d-block">${formatNumber(row.po_missing_unit_count)} item belum diisi satuannya</small>` : ''}</td>
                    </tr>`).join(''));
                    $('#batchSaveButton').prop('disabled', !batchTargets.length);
                    updateBatchConversionPreview();
                },
                error: function(xhr, status) {
                    if (status === 'abort' || version !== batchPreviewVersion) return;
                    batchError(batchRequestError(xhr));
                    $('#batchTargetSummary').text('Target belum dapat ditampilkan.');
                },
                complete: function() {
                    if (version !== batchPreviewVersion) return;
                    batchPreviewRequest = null;
                    $('#batchPreviewButton').prop('disabled', false);
                }
            });
        });

        $('#batchAddConversion').on('click', appendBatchConversion);
        $('#batchConversionRows').on('click', '.batch-remove-conversion', function() {
            const $row = $(this).closest('.batch-conversion-row');
            if ($row.find('.batch-conversion-unit').data('select2')) $row.find('.batch-conversion-unit').select2('destroy');
            $row.remove();
            if (!$('#batchConversionRows .batch-conversion-row').length) appendBatchConversion();
            updateBatchConversionPreview();
        }).on('change input', '.batch-conversion-unit, .batch-conversion-value, .batch-conversion-default', function() {
            if ($(this).is('.batch-conversion-default:checked')) {
                $('#batchConversionRows .batch-conversion-default').not(this).prop('checked', false);
            }
            updateBatchConversionPreview();
        });

        $('#konversiBatchModal').on('hide.bs.modal', function(event) {
            if (batchSaving) {
                event.preventDefault();
                return;
            }
            batchOpenVersion++;
            invalidateBatchTargets();
        });

        $('#konversiBatchForm').on('submit', function(event) {
            event.preventDefault();
            if (batchSaving) return;
            batchError();
            if (!batchTargets.length) {
                batchError('Muat pratinjau target obat sebelum menyimpan.');
                return;
            }
            const conversions = [];
            $('#batchConversionRows .batch-conversion-row').each(function() {
                const $row = $(this);
                conversions.push({
                    satuan_id: $row.find('.batch-conversion-unit').val(),
                    konversi: Number($row.find('.batch-conversion-value').val()),
                    is_default: $row.find('.batch-conversion-default').is(':checked') ? 1 : 0
                });
            });
            if (!conversions.length || conversions.some(row => !row.satuan_id || !Number.isInteger(row.konversi) || row.konversi < 1 || row.konversi > 2147483647)) {
                batchError('Lengkapi semua satuan pembelian dan isi konversi dengan bilangan bulat minimal 1.');
                return;
            }
            if (new Set(conversions.map(row => row.satuan_id)).size !== conversions.length) {
                batchError('Satuan pembelian tidak boleh berulang dalam satu batch.');
                return;
            }
            const payload = { ...batchSelection(), target_ids: batchTargets.map(row => row.id), conversions };
            batchSaving = true;
            const $button = $('#batchSaveButton');
            const normalHtml = $button.html();
            $('#konversiBatchForm, #konversiBatchModal .modal-footer').find('input, select, button').prop('disabled', true);
            $button.html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
            $.ajax({
                url: "{{ route('konversiSatuanObat.batchStore') }}", method: 'POST',
                contentType: 'application/json', data: JSON.stringify(payload),
                success: function(response) {
                    batchSaving = false;
                    $('#konversiBatchModal').modal('hide');
                    konversiTable.ajax.reload(null, false);
                    toast('success', 'Batch selesai', escapeHtml(response.message));
                },
                error: function(xhr) {
                    batchError(batchRequestError(xhr));
                    if (xhr.responseJSON?.errors?.target_ids) invalidateBatchTargets();
                },
                complete: function() {
                    batchSaving = false;
                    $('#konversiBatchForm, #konversiBatchModal .modal-footer').find('input, select, button').prop('disabled', false);
                    $button.html(normalHtml).prop('disabled', !batchTargets.length);
                }
            });
        });
