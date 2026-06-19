<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#mainCategoryModal';
        const formSelector = '#mainCategoryForm';
        const wrapperSelector = '#mainCategoryInputWrapper';
        const submitSelector = '#submitMainCategoryForm';
        const addButtonSelector = '#addMainCategoryInput';

        if ($.fn.dropify) {
            $('#mainCategoryExcelInput').dropify();
        }

        function normalizeSelectedIds(selectedIds) {
            if (!selectedIds) return [];
            if (!Array.isArray(selectedIds)) selectedIds = [selectedIds];
            return selectedIds.map((item) => String(item));
        }

        function loadKategoriUtamaOptions($select, selectedIds = []) {
            const selected = normalizeSelectedIds(selectedIds);

            return $.ajax({
                url: '{{ route("kategoriMain.kategoriUtama") }}',
                type: 'GET',
                success: function(response) {
                    $select.each(function() {
                        const $current = $(this);

                        if ($current.data('select2')) {
                            $current.select2('destroy');
                        }

                        $current.empty().append('<option value=""></option>');
                        response.forEach(function(kategoriUtama) {
                            const id = String(kategoriUtama.id);
                            const isSelected = selected.includes(id) ? 'selected' : '';
                            $current.append(
                                `<option value="${CategoryUI.escapeHtml(id)}" ${isSelected}>${CategoryUI.escapeHtml(kategoriUtama.name)}</option>`
                            );
                        });

                        $current.select2({
                            dropdownParent: $(modalSelector),
                            placeholder: 'Pilih Kategori Utama',
                            allowClear: false,
                            width: '100%'
                        });
                    });
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data kategori utama tidak bisa dimuat.');
                }
            });
        }

        function mainCategoryRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const categoryName = isEdit ? 'category_id' : 'category_id[]';
            const codeName = isEdit ? 'code' : 'code[]';
            const nameName = isEdit ? 'name' : 'name[]';
            const codeValue = CategoryUI.escapeHtml(data.code || '');
            const nameValue = CategoryUI.escapeHtml(data.name || '');

            return `
                <div class="category-batch-row">
                    <div class="category-field is-parent">
                        <label class="form-label">Kategori Utama</label>
                        <select name="${categoryName}" class="js-kategori-utama form-select" data-width="100%"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${codeName}" type="text" value="${codeValue}" placeholder="Contoh: ANT">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-name">
                        <label class="form-label">Main Kategori Obat</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-shape-outline"></i></span>
                            <input class="form-control" name="${nameName}" type="text" value="${nameValue}" placeholder="Masukkan nama main kategori">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="category-field is-action">
                            <button type="button" class="btn btn-outline-danger category-row-remove remove-main-category-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#mainCategoryModalLabel').text('Tambah Main Kategori');
            $('#mainCategoryModalSubtitle').text('Pilih kategori utama lalu tambahkan kelompok obat turunannya.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#mainCategoryId').val('');
            $('#mainCategoryBatchToolbar').show();
            $form.trigger('reset');
            CategoryUI.clearValidation(formSelector);
            $(wrapperSelector).html(mainCategoryRow());
            loadKategoriUtamaOptions($(wrapperSelector).find('.js-kategori-utama'));
            CategoryUI.updateBatchCount(wrapperSelector, '#mainCategoryRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            const $row = $(mainCategoryRow());
            $(wrapperSelector).append($row);
            loadKategoriUtamaOptions($row.find('.js-kategori-utama'));
            CategoryUI.updateBatchCount(wrapperSelector, '#mainCategoryRowCount');
        });

        $(document).on('click', '.remove-main-category-input', function() {
            if ($(wrapperSelector).find('.category-batch-row').length <= 1) {
                const $row = $(this).closest('.category-batch-row');
                $row.find('input').val('');
                $row.find('select').val('').trigger('change');
                CategoryUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            const $row = $(this).closest('.category-batch-row');
            $row.find('.js-kategori-utama').each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
            });
            $row.remove();
            CategoryUI.updateBatchCount(wrapperSelector, '#mainCategoryRowCount');
        });

        const mainCategoryTable = $('#tableMainKategori').DataTable(CategoryUI.dataTableOptions({
            ajax: {
                url: "{{ route("kategori.mainKategori.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'category_id',
                    name: 'category_id',
                    render: function(data) {
                        return CategoryUI.parentBadge(data, 'mdi-tag-outline');
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
                        return CategoryUI.identity(data, 'Main kategori');
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
            table: mainCategoryTable,
            tableSelector: '#tableMainKategori',
            searchSelector: '#mainKategoriSearch',
            totalTarget: '#mainKategoriTotal',
            filteredTarget: '#mainKategoriFiltered',
            selectedTarget: '#mainKategoriSelected'
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const mainCategoryId = $('#mainCategoryId').val();
            const url = mainCategoryId ?
                "{{ route("kategori.mainKategori.update", ":id") }}".replace(':id', mainCategoryId) :
                "{{ route("kategori.mainKategori.store") }}";
            const method = mainCategoryId ? 'PUT' : 'POST';
            const normalHtml = mainCategoryId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            CategoryUI.clearValidation(formSelector);
            CategoryUI.setButtonLoading(submitSelector, true, mainCategoryId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        CategoryUI.toast('success', response.message);
                        mainCategoryTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = CategoryUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        CategoryUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    CategoryUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan main kategori.');
                },
                complete: function() {
                    CategoryUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editMainCategory = function(id) {
            $.ajax({
                url: "{{ route("kategori.mainKategori.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#mainCategoryModalLabel').text('Edit Main Kategori');
                    $('#mainCategoryModalSubtitle').text('Perbarui induk, kode, dan nama main kategori.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#mainCategoryBatchToolbar').hide();
                    $('#mainCategoryId').val(response.id);
                    $(wrapperSelector).html(mainCategoryRow('edit', response));
                    loadKategoriUtamaOptions($(wrapperSelector).find('.js-kategori-utama'), response.category_id);
                    CategoryUI.clearValidation(formSelector);
                    CategoryUI.updateBatchCount(wrapperSelector, '#mainCategoryRowCount');
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data main kategori tidak bisa dimuat.');
                }
            });
        };

        window.deleteMainCategory = function(id) {
            Swal.fire({
                title: 'Hapus main kategori?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("kategori.mainKategori.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            CategoryUI.toast('success', 'Berhasil Dihapus', response.message);
                            mainCategoryTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus main kategori.', 'error');
                    }
                });
            });
        };

        function resetMainExcelForm() {
            $('#mainCategoryExcelForm')[0].reset();
            const dropify = $('#mainCategoryExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#mainCategoryModalExcell').on('hidden.bs.modal', resetMainExcelForm);

        $('#mainDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("kategori.mainKategori.exportTemplate") }}";
        });

        $('#mainSubmitExcel').on('click', function() {
            const fileInput = $('#mainCategoryExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#mainCategoryExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#mainCategoryExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
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
            CategoryUI.setButtonLoading('#mainSubmitExcel', true, 'Mengupload...', normalHtml);

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
                url: "{{ route("kategori.mainKategori.import") }}",
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
                                $('#mainCategoryModalExcell').modal('hide');
                                mainCategoryTable.ajax.reload(null, false);
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
                    CategoryUI.setButtonLoading('#mainSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
