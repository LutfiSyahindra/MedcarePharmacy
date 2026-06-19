<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#rakModal';
        const formSelector = '#rakForm';
        const wrapperSelector = '#rakInputWrapper';
        const submitSelector = '#submitRakForm';
        const addButtonSelector = '#addRakInput';

        if ($.fn.dropify) {
            $('#rakExcelInput').dropify();
        }

        function rakRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const kodeName = isEdit ? 'kode' : 'kode[]';
            const namaName = isEdit ? 'nama' : 'nama[]';
            const lokasiName = isEdit ? 'lokasi' : 'lokasi[]';
            const kodeValue = RakUI.escapeHtml(data.kode || '');
            const namaValue = RakUI.escapeHtml(data.nama || '');
            const lokasiValue = RakUI.escapeHtml(data.lokasi || '');

            return `
                <div class="rak-batch-row">
                    <div class="rak-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="rak-input-shell">
                            <span class="rak-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${kodeName}" type="text" value="${kodeValue}" placeholder="A-01">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="rak-field is-name">
                        <label class="form-label">Rak Penyimpanan</label>
                        <div class="rak-input-shell">
                            <span class="rak-input-icon"><i class="mdi mdi-archive-outline"></i></span>
                            <input class="form-control" name="${namaName}" type="text" value="${namaValue}" placeholder="Contoh: Rak Obat Generik">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="rak-field is-location">
                        <label class="form-label">Lokasi</label>
                        <div class="rak-input-shell">
                            <span class="rak-input-icon"><i class="mdi mdi-map-marker-outline"></i></span>
                            <input class="form-control" name="${lokasiName}" type="text" value="${lokasiValue}" placeholder="Contoh: Gudang A Lorong 2">
                        </div>
                        <small class="rak-form-hint">Opsional, isi detail area agar pencarian fisik lebih cepat.</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="rak-field is-action">
                            <button type="button" class="btn btn-outline-danger rak-row-remove remove-rak-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#rakModalLabel').text('Tambah Rak Penyimpanan');
            $('#rakModalSubtitle').text('Buat satu atau beberapa rak penyimpanan obat.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#rakId').val('');
            $('#rakBatchToolbar').show();
            $form.trigger('reset');
            RakUI.clearValidation(formSelector);
            $(wrapperSelector).html(rakRow());
            RakUI.updateBatchCount(wrapperSelector, '#rakRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            $(wrapperSelector).append(rakRow());
            RakUI.updateBatchCount(wrapperSelector, '#rakRowCount');
        });

        $(document).on('click', '.remove-rak-input', function() {
            if ($(wrapperSelector).find('.rak-batch-row').length <= 1) {
                $(this).closest('.rak-batch-row').find('input').val('');
                RakUI.toast('info', 'Baris Dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            $(this).closest('.rak-batch-row').remove();
            RakUI.updateBatchCount(wrapperSelector, '#rakRowCount');
        });

        const rakTable = $('#tableRak').DataTable(RakUI.dataTableOptions({
            ajax: {
                url: "{{ route("rak.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'kode',
                    name: 'kode',
                    render: function(data) {
                        return RakUI.codeBadge(data);
                    }
                },
                {
                    data: 'nama',
                    name: 'nama',
                    render: function(data, type, row) {
                        return RakUI.identity(data, row.kode ? `Kode ${row.kode}` : 'Rak penyimpanan');
                    }
                },
                {
                    data: 'lokasi',
                    name: 'lokasi',
                    render: function(data) {
                        return RakUI.locationBadge(data);
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        const checked = data == 1 ? 'checked' : '';
                        const textClass = data == 1 ? 'is-active' : 'is-inactive';
                        const text = data == 1 ? 'Aktif' : 'Nonaktif';

                        return `
                            <div class="rak-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-rak-status" data-id="${row.id}" ${checked} id="rakSwitch${row.id}">
                                    <label class="form-check-label" for="rakSwitch${row.id}"></label>
                                </div>
                                <span class="rak-status-text ${textClass}">${text}</span>
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

        RakUI.initTableTools({
            table: rakTable,
            tableSelector: '#tableRak',
            searchSelector: '#rakSearch',
            totalTarget: '#rakTotal',
            filteredTarget: '#rakFiltered',
            selectedTarget: '#rakSelected'
        });

        $('#tableRak').on('change', '.toggle-rak-status', function() {
            const $toggle = $(this);
            const $statusText = $toggle.closest('.rak-status-wrap').find('.rak-status-text');
            const rakId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("rak.updateStatus") }}",
                method: 'PUT',
                data: {
                    status: status,
                    id: rakId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status === 0)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    RakUI.toast('success', 'Status Diperbarui', status === 1 ? 'Rak penyimpanan sekarang aktif.' : 'Rak penyimpanan sekarang nonaktif.');
                },
                error: function() {
                    $toggle.prop('checked', previousStatus === 1);
                    $statusText
                        .toggleClass('is-active', previousStatus === 1)
                        .toggleClass('is-inactive', previousStatus === 0)
                        .text(previousStatus === 1 ? 'Aktif' : 'Nonaktif');

                    RakUI.toast('error', 'Gagal Mengubah Status', 'Terjadi kesalahan saat mengubah status rak.');
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                }
            });
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const rakId = $('#rakId').val();
            const url = rakId ?
                "{{ route("rak.update", ":id") }}".replace(':id', rakId) :
                "{{ route("rak.store") }}";
            const method = rakId ? 'PUT' : 'POST';
            const normalHtml = rakId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            RakUI.clearValidation(formSelector);
            RakUI.setButtonLoading(submitSelector, true, rakId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        RakUI.toast('success', response.message);
                        rakTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = RakUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        RakUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    RakUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan rak penyimpanan.');
                },
                complete: function() {
                    RakUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editRak = function(id) {
            $.ajax({
                url: "{{ route("rak.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#rakModalLabel').text('Edit Rak Penyimpanan');
                    $('#rakModalSubtitle').text('Perbarui kode, nama, dan lokasi rak yang dipilih.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#rakBatchToolbar').hide();
                    $('#rakId').val(response.id);
                    $(wrapperSelector).html(rakRow('edit', response));
                    RakUI.clearValidation(formSelector);
                    RakUI.updateBatchCount(wrapperSelector, '#rakRowCount');
                },
                error: function() {
                    RakUI.toast('error', 'Gagal Memuat', 'Data rak penyimpanan tidak bisa dimuat.');
                }
            });
        };

        window.deleteRak = function(id) {
            Swal.fire({
                title: 'Hapus rak penyimpanan?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("rak.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            RakUI.toast('success', 'Berhasil Dihapus', response.message);
                            rakTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus rak penyimpanan.', 'error');
                    }
                });
            });
        };

        function resetRakExcelForm() {
            $('#rakExcelForm')[0].reset();
            const dropify = $('#rakExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#rakModalExcell').on('hidden.bs.modal', resetRakExcelForm);

        $('#rakDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("rak.exportTemplate") }}";
        });

        $('#rakSubmitExcel').on('click', function() {
            const fileInput = $('#rakExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#rakExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#rakExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
                }, 600);

                Swal.fire({
                    icon: 'warning',
                    title: 'File belum dipilih',
                    text: 'Silakan pilih file Excel terlebih dahulu.'
                });
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            RakUI.setButtonLoading('#rakSubmitExcel', true, 'Mengupload...', normalHtml);

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
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#uploadProgressBar').css('width', percentComplete + '%').text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                url: "{{ route("rak.import") }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Berhasil',
                            html: `
                                <p>${response.added} data berhasil ditambahkan.</p>
                                <p>${response.skipped} data dilewati.</p>
                            `,
                            timer: 2600,
                            showConfirmButton: false,
                            willClose: function() {
                                $('#rakModalExcell').modal('hide');
                                rakTable.ajax.reload(null, false);
                            }
                        });
                        return;
                    }

                    Swal.fire('Gagal', response.message || 'Terjadi kesalahan saat import data.', 'error');
                },
                error: function(xhr) {
                    Swal.close();
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal mengupload file.';
                    Swal.fire('Gagal Import', message, 'error');
                },
                complete: function() {
                    RakUI.setButtonLoading('#rakSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
