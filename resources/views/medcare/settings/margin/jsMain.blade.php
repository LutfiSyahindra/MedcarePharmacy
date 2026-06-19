<script>
    $(document).ready(function() {
        const marginSubmitDefault = '<i class="mdi mdi-content-save-outline"></i>Simpan Margin';
        const marginSubmitUpdate = '<i class="mdi mdi-content-save-edit-outline"></i>Update Margin';
        let isEditMode = false;

        function updateMarginPreview() {
            const factor = Number($('#faktor_jual').val() || 0);
            const percent = Number.isFinite(factor) ? ((factor - 1) * 100) : 0;
            $('#marginPreview').text(`${percent.toLocaleString('id-ID', { maximumFractionDigits: 2 })}%`);
        }

        function updateReferenceCount() {
            const value = $('#reference_idSelect').val();
            const count = Array.isArray(value) ? value.length : (value ? 1 : 0);
            $('#referenceSelectionCount').text(Number(count).toLocaleString('id-ID'));
        }

        function initReferenceSelect(isMultiple = true) {
            const referenceSelect = $('#reference_idSelect');

            if (referenceSelect.hasClass('select2-hidden-accessible')) {
                referenceSelect.select2('destroy');
            }

            if (isMultiple) {
                referenceSelect.attr('multiple', 'multiple').attr('name', 'reference_id[]');
                $('#selectAllReferences').prop('disabled', false);
            } else {
                referenceSelect.removeAttr('multiple').attr('name', 'reference_id');
                $('#selectAllReferences').prop('disabled', true);
            }

            referenceSelect.select2({
                dropdownParent: $('#marginsModal'),
                placeholder: 'Pilih reference',
                allowClear: true,
                width: '100%'
            });

            referenceSelect.off('change.marginReferences').on('change.marginReferences', updateReferenceCount);
            updateReferenceCount();
        }

        function resetReferenceSelect() {
            const referenceSelect = $('#reference_idSelect');
            if (referenceSelect.hasClass('select2-hidden-accessible')) {
                referenceSelect.select2('destroy');
            }
            referenceSelect.empty();
            initReferenceSelect(true);
            $('#referenceHint').text('Pilih tingkat terlebih dahulu.');
            updateReferenceCount();
        }

        function loadReferences(tingkat, selectedIds = [], isMultiple = true) {
            const referenceSelect = $('#reference_idSelect');
            const normalizedSelected = selectedIds.map(String);

            if (!tingkat) {
                resetReferenceSelect();
                return $.Deferred().resolve().promise();
            }

            $('#referenceHint').text('Memuat reference...');
            referenceSelect.prop('disabled', true);

            return $.ajax({
                url: "{{ route("margin.getReferences", ":tingkat") }}".replace(':tingkat', tingkat),
                type: 'GET'
            }).done(function(response) {
                if (referenceSelect.hasClass('select2-hidden-accessible')) {
                    referenceSelect.select2('destroy');
                }

                referenceSelect.empty();
                (response || []).forEach(function(item) {
                    const label = item.nama ?? item.name ?? `Reference ${item.id}`;
                    const isSelected = normalizedSelected.includes(String(item.id)) ? 'selected' : '';
                    referenceSelect.append(
                        `<option value="${item.id}" ${isSelected}>${MarginUI.escapeHtml(label)}</option>`
                    );
                });

                initReferenceSelect(isMultiple);
                referenceSelect.val(isMultiple ? normalizedSelected : (normalizedSelected[0] || null)).trigger('change');

                if (!response || response.length === 0) {
                    $('#referenceHint').text('Belum ada reference untuk tingkat ini.');
                } else {
                    $('#referenceHint').text(isMultiple ?
                        'Pilih satu atau lebih reference untuk dibuat sekaligus.' :
                        'Pilih satu reference untuk margin ini.');
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat data reference.'
                });
                $('#referenceHint').text('Gagal memuat reference.');
            }).always(function() {
                referenceSelect.prop('disabled', false).trigger('change.select2');
            });
        }

        $('#marginsModal').on('show.bs.modal', function() {
            const form = $('#marginsForm');
            $('#marginsModalLabel').text('Tambah Margin');
            form.trigger('reset');
            isEditMode = false;
            MarginUI.clearValidation('#marginsForm');
            $('#marginsId').val('');
            $('#submitForm').html(marginSubmitDefault).prop('disabled', false);
            resetReferenceSelect();
            updateMarginPreview();
        });

        $('#tingkat').on('change', function() {
            loadReferences($(this).val(), [], !isEditMode);
        });

        $('#faktor_jual').on('input', updateMarginPreview);

        $('#selectAllReferences').on('click', function() {
            $('#reference_idSelect option').prop('selected', true);
            $('#reference_idSelect').trigger('change');
        });

        $('#clearReferences').on('click', function() {
            $('#reference_idSelect').val(null).trigger('change');
        });

        let marginTable = $('#tableMargin').DataTable(MarginUI.dataTableOptions({
            ajax: {
                url: "{{ route("margin.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'reference_id',
                    name: 'reference_id',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;

                        const safeReference = MarginUI.escapeHtml(data);
                        return `
                            <div class="margin-reference">
                                <span class="margin-avatar">${MarginUI.getInitials(data)}</span>
                                <div>
                                    <strong title="${safeReference}">${safeReference}</strong>
                                    <small>${MarginUI.tierLabel(row.tingkat)}</small>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'faktor_jual',
                    name: 'faktor_jual',
                    render: function(data, type) {
                        if (type !== 'display') return data;
                        const value = Number(data || 0);
                        return `
                            <span class="margin-factor-badge">
                                <i class="mdi mdi-multiplication"></i>
                                x${value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                        `;
                    }
                },
                {
                    data: 'persentase',
                    name: 'persentase',
                    render: function(data, type) {
                        if (type !== 'display') return data;
                        return `
                            <span class="margin-percent-badge">
                                <i class="mdi mdi-trending-up"></i>
                                ${MarginUI.escapeHtml(data)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'tingkat',
                    name: 'tingkat',
                    render: function(data, type) {
                        if (type !== 'display') return data;
                        return `
                            <span class="margin-tier-badge">
                                <i class="mdi mdi-layers-outline"></i>
                                ${MarginUI.escapeHtml(MarginUI.tierLabel(data))}
                            </span>
                        `;
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        const isActive = Number(data) === 1;
                        const checked = isActive ? 'checked' : '';
                        const label = isActive ? 'Aktif' : 'Nonaktif';
                        const statusClass = isActive ? 'is-active' : 'is-inactive';

                        return `
                            <div class="margin-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-status"
                                        data-id="${row.id}" ${checked} id="switch${row.id}">
                                    <label class="form-check-label" for="switch${row.id}"></label>
                                </div>
                                <span class="margin-status-text ${statusClass}">${label}</span>
                            </div>
                        `;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        }));

        MarginUI.initTableTools({
            table: marginTable,
            tableSelector: '#tableMargin',
            searchSelector: '#marginSearch',
            totalTarget: '#marginTotalCount',
            filteredTarget: '#marginFilteredCount',
            selectedTarget: '#marginSelectedCount'
        });

        $('#tableMargin').on('change', '.toggle-status', function() {
            const $toggle = $(this);
            const marginsId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;
            const $statusText = $toggle.closest('.margin-status-wrap').find('.margin-status-text');

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("margin.updateStatus") }}",
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: marginsId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status !== 1)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status margin berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    $toggle.prop('checked', previousStatus === 1);
                    $statusText
                        .toggleClass('is-active', previousStatus === 1)
                        .toggleClass('is-inactive', previousStatus !== 1)
                        .text(previousStatus === 1 ? 'Aktif' : 'Nonaktif');

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat mengubah status.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                }
            });
        });

        $('#marginsForm').on('submit', function(e) {
            e.preventDefault();
            MarginUI.clearValidation('#marginsForm');

            const formData = $(this).serialize();
            const marginsId = $('#marginsId').val();
            const url = marginsId ? "{{ route("margin.update", ":id") }}".replace(':id', marginsId) :
                "{{ route("margin.store") }}";
            const method = marginsId ? 'PUT' : 'POST';
            const normalLabel = marginsId ? marginSubmitUpdate : marginSubmitDefault;

            Swal.fire({
                title: marginsId ? 'Perbarui margin ini?' : 'Tambahkan margin baru?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                MarginUI.setButtonLoading('#submitForm', true, 'Menyimpan...', normalLabel);

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#marginsModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });

                            $('#marginsForm')[0].reset();
                            $('#marginsId').val('');
                            marginTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = [];

                            for (let key in errors) {
                                const message = errors[key][0];
                                errorMessages.push(message);

                                if (key.startsWith('reference_id')) {
                                    $('#error-reference_id').text(message);
                                    $('#reference_idSelect').addClass('is-invalid')
                                        .closest('.margin-field').addClass('has-error');
                                } else {
                                    MarginUI.markInvalid(key, message);
                                }
                            }

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
                            return;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menyimpan margin.'
                        });
                    },
                    complete: function() {
                        MarginUI.setButtonLoading('#submitForm', false, 'Menyimpan...', normalLabel);
                    }
                });
            });
        });

        window.editMargins = function(id) {
            $('#marginsModal').modal('show');
            isEditMode = true;
            MarginUI.clearValidation('#marginsForm');
            $('#marginsModalLabel').text('Edit Margin');
            $('#submitForm').html(marginSubmitUpdate).prop('disabled', false);

            $.ajax({
                url: "{{ route("margin.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $('#marginsId').val(response.id);
                    $('#tingkat').val(response.tingkat);
                    $('#faktor_jual').val(response.faktor_jual);
                    updateMarginPreview();

                    loadReferences(response.tingkat, [response.reference_id], false).done(function() {
                        if (response.reference_id && !$('#reference_idSelect').find(
                                `option[value="${response.reference_id}"]`).length) {
                            const option = new Option(
                                response.reference_text ?? `Reference ${response.reference_id}`,
                                response.reference_id,
                                true,
                                true
                            );
                            $('#reference_idSelect').append(option).trigger('change');
                        }
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal mengambil data margin.'
                    });
                    $('#marginsModal').modal('hide');
                }
            });
        }

        window.deleteMargins = function(id) {
            Swal.fire({
                title: 'Hapus margin ini?',
                text: 'Margin akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("margin.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            marginTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus margin.', 'error');
                    }
                });
            });
        }
    });
</script>
