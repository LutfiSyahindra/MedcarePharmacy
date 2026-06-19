<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#subCategoryModal';
        const formSelector = '#subCategoryForm';
        const wrapperSelector = '#subCategoryInputWrapper';
        const submitSelector = '#submitSubCategoryForm';
        const addButtonSelector = '#addSubCategoryInput';

        if ($.fn.dropify) {
            $('#subCategoryExcelInput').dropify();
        }

        function normalizeSelectedIds(selectedIds) {
            if (!selectedIds) return [];
            if (!Array.isArray(selectedIds)) selectedIds = [selectedIds];
            return selectedIds.map((item) => String(item));
        }

        function loadMainCategoryOptions($select, selectedIds = []) {
            const selected = normalizeSelectedIds(selectedIds);

            return $.ajax({
                url: '{{ route("kategori.subKategori.mainKategori") }}',
                type: 'GET',
                success: function(response) {
                    $select.each(function() {
                        const $current = $(this);

                        if ($current.data('select2')) {
                            $current.select2('destroy');
                        }

                        $current.empty().append('<option value=""></option>');
                        response.forEach(function(mainCategory) {
                            const id = String(mainCategory.id);
                            const isSelected = selected.includes(id) ? 'selected' : '';
                            $current.append(
                                `<option value="${CategoryUI.escapeHtml(id)}" ${isSelected}>${CategoryUI.escapeHtml(mainCategory.name)}</option>`
                            );
                        });

                        $current.select2({
                            dropdownParent: $(modalSelector),
                            placeholder: 'Pilih Main Kategori',
                            allowClear: false,
                            width: '100%'
                        });
                    });
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data main kategori tidak bisa dimuat.');
                }
            });
        }

        function subCategoryRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const mainCategoryName = isEdit ? 'main_category_id' : 'main_category_id[]';
            const codeName = isEdit ? 'code' : 'code[]';
            const nameName = isEdit ? 'name' : 'name[]';
            const codeValue = CategoryUI.escapeHtml(data.code || '');
            const nameValue = CategoryUI.escapeHtml(data.name || '');

            return `
                <div class="category-batch-row">
                    <div class="category-field is-parent">
                        <label class="form-label">Main Kategori</label>
                        <select name="${mainCategoryName}" class="js-main-category form-select" data-width="100%"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${codeName}" type="text" value="${codeValue}" placeholder="Contoh: TAB">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-name">
                        <label class="form-label">Sub Kategori Obat</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-format-list-bulleted-type"></i></span>
                            <input class="form-control" name="${nameName}" type="text" value="${nameValue}" placeholder="Masukkan nama sub kategori">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="category-field is-action">
                            <button type="button" class="btn btn-outline-danger category-row-remove remove-sub-category-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#subCategoryModalLabel').text('Tambah Sub Kategori');
            $('#subCategoryModalSubtitle').text('Pilih main kategori lalu tambahkan detail klasifikasinya.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#subCategoryId').val('');
            $('#subCategoryBatchToolbar').show();
            $form.trigger('reset');
            CategoryUI.clearValidation(formSelector);
            $(wrapperSelector).html(subCategoryRow());
            loadMainCategoryOptions($(wrapperSelector).find('.js-main-category'));
            CategoryUI.updateBatchCount(wrapperSelector, '#subCategoryRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            const $row = $(subCategoryRow());
            $(wrapperSelector).append($row);
            loadMainCategoryOptions($row.find('.js-main-category'));
            CategoryUI.updateBatchCount(wrapperSelector, '#subCategoryRowCount');
        });

        $(document).on('click', '.remove-sub-category-input', function() {
            if ($(wrapperSelector).find('.category-batch-row').length <= 1) {
                const $row = $(this).closest('.category-batch-row');
                $row.find('input').val('');
                $row.find('select').val('').trigger('change');
                CategoryUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            const $row = $(this).closest('.category-batch-row');
            $row.find('.js-main-category').each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
            });
            $row.remove();
            CategoryUI.updateBatchCount(wrapperSelector, '#subCategoryRowCount');
        });

        const subCategoryTable = $('#tableSubKategori').DataTable(CategoryUI.dataTableOptions({
            ajax: {
                url: "{{ route("kategori.subKategori.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'main_category_id',
                    name: 'main_category_id',
                    render: function(data) {
                        return CategoryUI.parentBadge(data, 'mdi-shape-outline');
                    }
                },
                {
                    data: 'code',
                    name: 'code',
                    render: function(data) {
                        return CategoryUI.codeBadge(data);
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data) {
                        return CategoryUI.identity(data, 'Sub kategori');
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
            table: subCategoryTable,
            tableSelector: '#tableSubKategori',
            searchSelector: '#subKategoriSearch',
            totalTarget: '#subKategoriTotal',
            filteredTarget: '#subKategoriFiltered',
            selectedTarget: '#subKategoriSelected'
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const subCategoryId = $('#subCategoryId').val();
            const url = subCategoryId ?
                "{{ route("kategori.subKategori.update", ":id") }}".replace(':id', subCategoryId) :
                "{{ route("kategori.subKategori.store") }}";
            const method = subCategoryId ? 'PUT' : 'POST';
            const normalHtml = subCategoryId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            CategoryUI.clearValidation(formSelector);
            CategoryUI.setButtonLoading(submitSelector, true, subCategoryId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        CategoryUI.toast('success', response.message);
                        subCategoryTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = CategoryUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        CategoryUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    CategoryUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan sub kategori.');
                },
                complete: function() {
                    CategoryUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editSubCategory = function(id) {
            $.ajax({
                url: "{{ route("kategori.subKategori.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#subCategoryModalLabel').text('Edit Sub Kategori');
                    $('#subCategoryModalSubtitle').text('Perbarui induk, kode, dan nama sub kategori.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#subCategoryBatchToolbar').hide();
                    $('#subCategoryId').val(response.id);
                    $(wrapperSelector).html(subCategoryRow('edit', response));
                    loadMainCategoryOptions($(wrapperSelector).find('.js-main-category'), response.main_category_id);
                    CategoryUI.clearValidation(formSelector);
                    CategoryUI.updateBatchCount(wrapperSelector, '#subCategoryRowCount');
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data sub kategori tidak bisa dimuat.');
                }
            });
        };

        window.deleteSubCategory = function(id) {
            Swal.fire({
                title: 'Hapus sub kategori?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("kategori.subKategori.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            CategoryUI.toast('success', 'Berhasil Dihapus', response.message);
                            subCategoryTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus sub kategori.', 'error');
                    }
                });
            });
        };

        function resetSubExcelForm() {
            $('#subCategoryExcelForm')[0].reset();
            const dropify = $('#subCategoryExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#subCategoryModalExcell').on('hidden.bs.modal', resetSubExcelForm);

        $('#subDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("kategori.subKategori.exportTemplate") }}";
        });

        $('#subSubmitExcel').on('click', function() {
            const fileInput = $('#subCategoryExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#subCategoryExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#subCategoryExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
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
            CategoryUI.setButtonLoading('#subSubmitExcel', true, 'Mengupload...', normalHtml);

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
                url: "{{ route("kategori.subKategori.import") }}",
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
                                $('#subCategoryModalExcell').modal('hide');
                                subCategoryTable.ajax.reload(null, false);
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
                    CategoryUI.setButtonLoading('#subSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
