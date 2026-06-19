<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#golonganModal';
        const formSelector = '#golonganForm';
        const wrapperSelector = '#golonganInputWrapper';
        const submitSelector = '#submitGolonganForm';
        const addButtonSelector = '#addGolonganInput';

        if ($.fn.dropify) {
            $('#golonganExcelInput').dropify();
        }

        function golonganRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const kodeName = isEdit ? 'kode' : 'kode[]';
            const namaName = isEdit ? 'nama' : 'nama[]';
            const keteranganName = isEdit ? 'keterangan' : 'keterangan[]';
            const kodeValue = GolonganUI.escapeHtml(data.kode || '');
            const namaValue = GolonganUI.escapeHtml(data.nama || '');
            const keteranganValue = GolonganUI.escapeHtml(data.keterangan || '');

            return `
                <div class="golongan-batch-row">
                    <div class="golongan-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="golongan-input-shell">
                            <span class="golongan-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${kodeName}" type="text" value="${kodeValue}" placeholder="ANT">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="golongan-field is-name">
                        <label class="form-label">Golongan</label>
                        <div class="golongan-input-shell">
                            <span class="golongan-input-icon"><i class="mdi mdi-shape-outline"></i></span>
                            <input class="form-control" name="${namaName}" type="text" value="${namaValue}" placeholder="Contoh: Antibiotik">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="golongan-field is-description">
                        <label class="form-label">Keterangan</label>
                        <div class="golongan-input-shell">
                            <span class="golongan-input-icon"><i class="mdi mdi-text-box-outline"></i></span>
                            <input class="form-control" name="${keteranganName}" type="text" value="${keteranganValue}" placeholder="Deskripsi tambahan opsional">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="golongan-field is-action">
                            <button type="button" class="btn btn-outline-danger golongan-row-remove remove-golongan-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#golonganModalLabel').text('Tambah Golongan');
            $('#golonganModalSubtitle').text('Buat satu atau beberapa golongan obat.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#golonganId').val('');
            $('#golonganBatchToolbar').show();
            $form.trigger('reset');
            GolonganUI.clearValidation(formSelector);
            $(wrapperSelector).html(golonganRow());
            GolonganUI.updateBatchCount(wrapperSelector, '#golonganRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            $(wrapperSelector).append(golonganRow());
            GolonganUI.updateBatchCount(wrapperSelector, '#golonganRowCount');
        });

        $(document).on('click', '.remove-golongan-input', function() {
            if ($(wrapperSelector).find('.golongan-batch-row').length <= 1) {
                $(this).closest('.golongan-batch-row').find('input').val('');
                GolonganUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            $(this).closest('.golongan-batch-row').remove();
            GolonganUI.updateBatchCount(wrapperSelector, '#golonganRowCount');
        });

        const golonganTable = $('#tableGolongan').DataTable(GolonganUI.dataTableOptions({
            ajax: {
                url: "{{ route("golongan.table") }}",
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
                        return GolonganUI.codeBadge(data);
                    }
                },
                {
                    data: 'nama',
                    name: 'nama',
                    render: function(data) {
                        return GolonganUI.identity(data, 'Golongan obat');
                    }
                },
                {
                    data: 'keterangan',
                    name: 'keterangan',
                    render: function(data) {
                        return GolonganUI.descriptionBadge(data);
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
                            <div class="golongan-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-golongan-status" data-id="${row.id}" ${checked} id="golonganSwitch${row.id}">
                                    <label class="form-check-label" for="golonganSwitch${row.id}"></label>
                                </div>
                                <span class="golongan-status-text ${textClass}">${text}</span>
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

        GolonganUI.initTableTools({
            table: golonganTable,
            tableSelector: '#tableGolongan',
            searchSelector: '#golonganSearch',
            totalTarget: '#golonganTotal',
            filteredTarget: '#golonganFiltered',
            selectedTarget: '#golonganSelected'
        });

        $('#tableGolongan').on('change', '.toggle-golongan-status', function() {
            const $toggle = $(this);
            const $statusText = $toggle.closest('.golongan-status-wrap').find('.golongan-status-text');
            const golonganId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("golongan.updateStatus") }}",
                method: 'PUT',
                data: {
                    status: status,
                    id: golonganId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status === 0)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    GolonganUI.toast('success', 'Status Diperbarui', status === 1 ? 'Golongan sekarang aktif.' : 'Golongan sekarang nonaktif.');
                },
                error: function() {
                    $toggle.prop('checked', previousStatus === 1);
                    $statusText
                        .toggleClass('is-active', previousStatus === 1)
                        .toggleClass('is-inactive', previousStatus === 0)
                        .text(previousStatus === 1 ? 'Aktif' : 'Nonaktif');

                    GolonganUI.toast('error', 'Gagal Mengubah Status', 'Terjadi kesalahan saat mengubah status golongan.');
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                }
            });
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const golonganId = $('#golonganId').val();
            const url = golonganId ?
                "{{ route("golongan.update", ":id") }}".replace(':id', golonganId) :
                "{{ route("golongan.store") }}";
            const method = golonganId ? 'PUT' : 'POST';
            const normalHtml = golonganId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            GolonganUI.clearValidation(formSelector);
            GolonganUI.setButtonLoading(submitSelector, true, golonganId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        GolonganUI.toast('success', response.message);
                        golonganTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = GolonganUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        GolonganUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    GolonganUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan golongan.');
                },
                complete: function() {
                    GolonganUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editGolongan = function(id) {
            $.ajax({
                url: "{{ route("golongan.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#golonganModalLabel').text('Edit Golongan');
                    $('#golonganModalSubtitle').text('Perbarui kode, nama, dan keterangan golongan yang dipilih.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#golonganBatchToolbar').hide();
                    $('#golonganId').val(response.id);
                    $(wrapperSelector).html(golonganRow('edit', response));
                    GolonganUI.clearValidation(formSelector);
                    GolonganUI.updateBatchCount(wrapperSelector, '#golonganRowCount');
                },
                error: function() {
                    GolonganUI.toast('error', 'Gagal Memuat', 'Data golongan tidak bisa dimuat.');
                }
            });
        };

        window.deleteGolongan = function(id) {
            Swal.fire({
                title: 'Hapus golongan?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("golongan.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            GolonganUI.toast('success', 'Berhasil Dihapus', response.message);
                            golonganTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus golongan.', 'error');
                    }
                });
            });
        };

        function resetGolonganExcelForm() {
            $('#golonganExcelForm')[0].reset();
            const dropify = $('#golonganExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#golonganModalExcell').on('hidden.bs.modal', resetGolonganExcelForm);

        $('#golonganDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("golongan.exportTemplate") }}";
        });

        $('#golonganSubmitExcel').on('click', function() {
            const fileInput = $('#golonganExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#golonganExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#golonganExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
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
            GolonganUI.setButtonLoading('#golonganSubmitExcel', true, 'Mengupload...', normalHtml);

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
                url: "{{ route("golongan.import") }}",
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
                                $('#golonganModalExcell').modal('hide');
                                golonganTable.ajax.reload(null, false);
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
                    GolonganUI.setButtonLoading('#golonganSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
