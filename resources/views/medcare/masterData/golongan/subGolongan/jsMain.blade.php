<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#subGolonganModal';
        const formSelector = '#subGolonganForm';
        const wrapperSelector = '#subGolonganInputWrapper';
        const submitSelector = '#submitSubGolonganForm';
        const addButtonSelector = '#addSubGolonganInput';

        if ($.fn.dropify) {
            $('#subGolonganExcelInput').dropify();
        }

        function normalizeSelectedIds(selectedIds) {
            if (!selectedIds) return [];
            if (!Array.isArray(selectedIds)) selectedIds = [selectedIds];
            return selectedIds.map((item) => String(item));
        }

        function loadMainGolonganOptions($select, selectedIds = []) {
            const selected = normalizeSelectedIds(selectedIds);

            return $.ajax({
                url: '{{ route("golongan.subGolongan.mainGolongan") }}',
                type: 'GET',
                success: function(response) {
                    $select.each(function() {
                        const $current = $(this);

                        if ($current.data('select2')) {
                            $current.select2('destroy');
                        }

                        $current.empty().append('<option value=""></option>');
                        response.forEach(function(mainGolongan) {
                            const id = String(mainGolongan.id);
                            const label = mainGolongan.kode ? `${mainGolongan.kode} - ${mainGolongan.nama}` : mainGolongan.nama;
                            const isSelected = selected.includes(id) ? 'selected' : '';
                            $current.append(
                                `<option value="${CategoryUI.escapeHtml(id)}" ${isSelected}>${CategoryUI.escapeHtml(label)}</option>`
                            );
                        });

                        $current.select2({
                            dropdownParent: $(modalSelector),
                            placeholder: 'Pilih Main Golongan',
                            allowClear: false,
                            width: '100%'
                        });
                    });
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data main golongan tidak bisa dimuat.');
                }
            });
        }

        function subGolonganRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const mainGolonganName = isEdit ? 'main_golongan_id' : 'main_golongan_id[]';
            const kodeName = isEdit ? 'kode' : 'kode[]';
            const namaName = isEdit ? 'nama' : 'nama[]';
            const kodeValue = CategoryUI.escapeHtml(data.kode || '');
            const namaValue = CategoryUI.escapeHtml(data.nama || '');

            return `
                <div class="category-batch-row">
                    <div class="category-field is-parent">
                        <label class="form-label">Main Golongan</label>
                        <select name="${mainGolonganName}" class="js-main-golongan form-select" data-width="100%"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${kodeName}" type="text" value="${kodeValue}" placeholder="Contoh: BET">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-name">
                        <label class="form-label">Sub Golongan Obat</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-format-list-bulleted-type"></i></span>
                            <input class="form-control" name="${namaName}" type="text" value="${namaValue}" placeholder="Masukkan nama sub golongan">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="category-field is-action">
                            <button type="button" class="btn btn-outline-danger category-row-remove remove-sub-golongan-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#subGolonganModalLabel').text('Tambah Sub Golongan');
            $('#subGolonganModalSubtitle').text('Pilih main golongan lalu tambahkan detail turunannya.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#subGolonganId').val('');
            $('#subGolonganBatchToolbar').show();
            $form.trigger('reset');
            CategoryUI.clearValidation(formSelector);
            $(wrapperSelector).html(subGolonganRow());
            loadMainGolonganOptions($(wrapperSelector).find('.js-main-golongan'));
            CategoryUI.updateBatchCount(wrapperSelector, '#subGolonganRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            const $row = $(subGolonganRow());
            $(wrapperSelector).append($row);
            loadMainGolonganOptions($row.find('.js-main-golongan'));
            CategoryUI.updateBatchCount(wrapperSelector, '#subGolonganRowCount');
        });

        $(document).on('click', '.remove-sub-golongan-input', function() {
            if ($(wrapperSelector).find('.category-batch-row').length <= 1) {
                const $row = $(this).closest('.category-batch-row');
                $row.find('input').val('');
                $row.find('select').val('').trigger('change');
                CategoryUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            const $row = $(this).closest('.category-batch-row');
            $row.find('.js-main-golongan').each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
            });
            $row.remove();
            CategoryUI.updateBatchCount(wrapperSelector, '#subGolonganRowCount');
        });

        const subGolonganTable = $('#tableSubGolongan').DataTable(CategoryUI.dataTableOptions({
            ajax: {
                url: "{{ route("golongan.subGolongan.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'main_golongan_id',
                    name: 'main_golongan_id',
                    render: function(data) {
                        return CategoryUI.parentBadge(data, 'mdi-shape-plus-outline');
                    }
                },
                {
                    data: 'kode',
                    name: 'kode',
                    render: function(data) {
                        return CategoryUI.codeBadge(data);
                    }
                },
                {
                    data: 'nama',
                    name: 'nama',
                    render: function(data) {
                        return CategoryUI.identity(data, 'Sub golongan');
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

        CategoryUI.initTableTools({
            table: subGolonganTable,
            tableSelector: '#tableSubGolongan',
            searchSelector: '#subGolonganSearch',
            totalTarget: '#subGolonganTotal',
            filteredTarget: '#subGolonganFiltered',
            selectedTarget: '#subGolonganSelected'
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const subGolonganId = $('#subGolonganId').val();
            const url = subGolonganId ?
                "{{ route("golongan.subGolongan.update", ":id") }}".replace(':id', subGolonganId) :
                "{{ route("golongan.subGolongan.store") }}";
            const method = subGolonganId ? 'PUT' : 'POST';
            const normalHtml = subGolonganId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            CategoryUI.clearValidation(formSelector);
            CategoryUI.setButtonLoading(submitSelector, true, subGolonganId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        CategoryUI.toast('success', response.message);
                        subGolonganTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = CategoryUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        CategoryUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    CategoryUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan sub golongan.');
                },
                complete: function() {
                    CategoryUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editSubGolongan = function(id) {
            $.ajax({
                url: "{{ route("golongan.subGolongan.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#subGolonganModalLabel').text('Edit Sub Golongan');
                    $('#subGolonganModalSubtitle').text('Perbarui induk, kode, dan nama sub golongan.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#subGolonganBatchToolbar').hide();
                    $('#subGolonganId').val(response.id);
                    $(wrapperSelector).html(subGolonganRow('edit', response));
                    loadMainGolonganOptions($(wrapperSelector).find('.js-main-golongan'), response.main_golongan_id);
                    CategoryUI.clearValidation(formSelector);
                    CategoryUI.updateBatchCount(wrapperSelector, '#subGolonganRowCount');
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data sub golongan tidak bisa dimuat.');
                }
            });
        };

        window.deleteSubGolongan = function(id) {
            Swal.fire({
                title: 'Hapus sub golongan?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("golongan.subGolongan.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            CategoryUI.toast('success', 'Berhasil Dihapus', response.message);
                            subGolonganTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus sub golongan.', 'error');
                    }
                });
            });
        };

        function resetSubGolonganExcelForm() {
            $('#subGolonganExcelForm')[0].reset();
            const dropify = $('#subGolonganExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#subGolonganModalExcell').on('hidden.bs.modal', resetSubGolonganExcelForm);

        $('#subGolonganDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("golongan.subGolongan.exportTemplate") }}";
        });

        $('#subGolonganSubmitExcel').on('click', function() {
            const fileInput = $('#subGolonganExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#subGolonganExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#subGolonganExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
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
            CategoryUI.setButtonLoading('#subGolonganSubmitExcel', true, 'Mengupload...', normalHtml);

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
                url: "{{ route("golongan.subGolongan.import") }}",
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
                                $('#subGolonganModalExcell').modal('hide');
                                subGolonganTable.ajax.reload(null, false);
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
                    CategoryUI.setButtonLoading('#subGolonganSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
