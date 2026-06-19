<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#satuanModal';
        const formSelector = '#satuanForm';
        const wrapperSelector = '#satuanInputWrapper';
        const submitSelector = '#submitSatuanForm';
        const addButtonSelector = '#addSatuanInput';

        if ($.fn.dropify) {
            $('#satuanExcelInput').dropify();
        }

        function satuanRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const kodeName = isEdit ? 'kode' : 'kode[]';
            const namaName = isEdit ? 'nama' : 'nama[]';
            const kodeValue = SatuanUI.escapeHtml(data.kode || '');
            const namaValue = SatuanUI.escapeHtml(data.nama || '');

            return `
                <div class="satuan-batch-row">
                    <div class="satuan-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="satuan-input-shell">
                            <span class="satuan-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${kodeName}" type="text" value="${kodeValue}" placeholder="Contoh: TAB">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="satuan-field is-name">
                        <label class="form-label">Satuan</label>
                        <div class="satuan-input-shell">
                            <span class="satuan-input-icon"><i class="mdi mdi-ruler-square"></i></span>
                            <input class="form-control" name="${namaName}" type="text" value="${namaValue}" placeholder="Masukkan nama satuan">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="satuan-field is-action">
                            <button type="button" class="btn btn-outline-danger satuan-row-remove remove-satuan-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#satuanModalLabel').text('Tambah Satuan');
            $('#satuanModalSubtitle').text('Buat satu atau beberapa satuan obat.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#satuanId').val('');
            $('#satuanBatchToolbar').show();
            $form.trigger('reset');
            SatuanUI.clearValidation(formSelector);
            $(wrapperSelector).html(satuanRow());
            SatuanUI.updateBatchCount(wrapperSelector, '#satuanRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            $(wrapperSelector).append(satuanRow());
            SatuanUI.updateBatchCount(wrapperSelector, '#satuanRowCount');
        });

        $(document).on('click', '.remove-satuan-input', function() {
            if ($(wrapperSelector).find('.satuan-batch-row').length <= 1) {
                $(this).closest('.satuan-batch-row').find('input').val('');
                SatuanUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            $(this).closest('.satuan-batch-row').remove();
            SatuanUI.updateBatchCount(wrapperSelector, '#satuanRowCount');
        });

        const satuanTable = $('#tableSatuan').DataTable(SatuanUI.dataTableOptions({
            ajax: {
                url: "{{ route("satuan.table") }}",
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
                        return SatuanUI.codeBadge(data);
                    }
                },
                {
                    data: 'nama',
                    name: 'nama',
                    render: function(data) {
                        return SatuanUI.identity(data, 'Satuan obat');
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
                            <div class="satuan-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-satuan-status" data-id="${row.id}" ${checked} id="satuanSwitch${row.id}">
                                    <label class="form-check-label" for="satuanSwitch${row.id}"></label>
                                </div>
                                <span class="satuan-status-text ${textClass}">${text}</span>
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

        SatuanUI.initTableTools({
            table: satuanTable,
            tableSelector: '#tableSatuan',
            searchSelector: '#satuanSearch',
            totalTarget: '#satuanTotal',
            filteredTarget: '#satuanFiltered',
            selectedTarget: '#satuanSelected'
        });

        $('#tableSatuan').on('change', '.toggle-satuan-status', function() {
            const $toggle = $(this);
            const $statusText = $toggle.closest('.satuan-status-wrap').find('.satuan-status-text');
            const satuanId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("satuan.updateStatus") }}",
                method: 'PUT',
                data: {
                    status: status,
                    id: satuanId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status === 0)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    SatuanUI.toast('success', 'Status Diperbarui', status === 1 ? 'Satuan sekarang aktif.' : 'Satuan sekarang nonaktif.');
                },
                error: function() {
                    $toggle.prop('checked', previousStatus === 1);
                    $statusText
                        .toggleClass('is-active', previousStatus === 1)
                        .toggleClass('is-inactive', previousStatus === 0)
                        .text(previousStatus === 1 ? 'Aktif' : 'Nonaktif');

                    SatuanUI.toast('error', 'Gagal Mengubah Status', 'Terjadi kesalahan saat mengubah status satuan.');
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                }
            });
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const satuanId = $('#satuanId').val();
            const url = satuanId ?
                "{{ route("satuan.update", ":id") }}".replace(':id', satuanId) :
                "{{ route("satuan.store") }}";
            const method = satuanId ? 'PUT' : 'POST';
            const normalHtml = satuanId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            SatuanUI.clearValidation(formSelector);
            SatuanUI.setButtonLoading(submitSelector, true, satuanId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        SatuanUI.toast('success', response.message);
                        satuanTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = SatuanUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        SatuanUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    SatuanUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan satuan.');
                },
                complete: function() {
                    SatuanUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editSatuan = function(id) {
            $.ajax({
                url: "{{ route("satuan.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#satuanModalLabel').text('Edit Satuan');
                    $('#satuanModalSubtitle').text('Perbarui kode dan nama satuan yang dipilih.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#satuanBatchToolbar').hide();
                    $('#satuanId').val(response.id);
                    $(wrapperSelector).html(satuanRow('edit', response));
                    SatuanUI.clearValidation(formSelector);
                    SatuanUI.updateBatchCount(wrapperSelector, '#satuanRowCount');
                },
                error: function() {
                    SatuanUI.toast('error', 'Gagal Memuat', 'Data satuan tidak bisa dimuat.');
                }
            });
        };

        window.deleteSatuan = function(id) {
            Swal.fire({
                title: 'Hapus satuan?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("satuan.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            SatuanUI.toast('success', 'Berhasil Dihapus', response.message);
                            satuanTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus satuan.', 'error');
                    }
                });
            });
        };

        function resetSatuanExcelForm() {
            $('#satuanExcelForm')[0].reset();
            const dropify = $('#satuanExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#satuanModalExcell').on('hidden.bs.modal', resetSatuanExcelForm);

        $('#satuanDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("satuan.exportTemplate") }}";
        });

        $('#satuanSubmitExcel').on('click', function() {
            const fileInput = $('#satuanExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#satuanExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#satuanExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
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
            SatuanUI.setButtonLoading('#satuanSubmitExcel', true, 'Mengupload...', normalHtml);

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
                url: "{{ route("satuan.import") }}",
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
                                $('#satuanModalExcell').modal('hide');
                                satuanTable.ajax.reload(null, false);
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
                    SatuanUI.setButtonLoading('#satuanSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
