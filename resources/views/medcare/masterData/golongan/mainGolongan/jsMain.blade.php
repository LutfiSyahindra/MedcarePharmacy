<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#mainGolonganModal';
        const formSelector = '#mainGolonganForm';
        const wrapperSelector = '#mainGolonganInputWrapper';
        const submitSelector = '#submitMainGolonganForm';
        const addButtonSelector = '#addMainGolonganInput';

        if ($.fn.dropify) {
            $('#mainGolonganExcelInput').dropify();
        }

        function normalizeSelectedIds(selectedIds) {
            if (!selectedIds) return [];
            if (!Array.isArray(selectedIds)) selectedIds = [selectedIds];
            return selectedIds.map((item) => String(item));
        }

        function loadGolonganOptions($select, selectedIds = []) {
            const selected = normalizeSelectedIds(selectedIds);

            return $.ajax({
                url: '{{ route("golongan.mainGolongan.golongan") }}',
                type: 'GET',
                success: function(response) {
                    $select.each(function() {
                        const $current = $(this);

                        if ($current.data('select2')) {
                            $current.select2('destroy');
                        }

                        $current.empty().append('<option value=""></option>');
                        response.forEach(function(golongan) {
                            const id = String(golongan.id);
                            const label = golongan.kode ? `${golongan.kode} - ${golongan.nama}` : golongan.nama;
                            const isSelected = selected.includes(id) ? 'selected' : '';
                            $current.append(
                                `<option value="${CategoryUI.escapeHtml(id)}" ${isSelected}>${CategoryUI.escapeHtml(label)}</option>`
                            );
                        });

                        $current.select2({
                            dropdownParent: $(modalSelector),
                            placeholder: 'Pilih Golongan',
                            allowClear: false,
                            width: '100%'
                        });
                    });
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data golongan tidak bisa dimuat.');
                }
            });
        }

        function mainGolonganRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const golonganName = isEdit ? 'golongan_id' : 'golongan_id[]';
            const kodeName = isEdit ? 'kode' : 'kode[]';
            const namaName = isEdit ? 'nama' : 'nama[]';
            const kodeValue = CategoryUI.escapeHtml(data.kode || '');
            const namaValue = CategoryUI.escapeHtml(data.nama || '');

            return `
                <div class="category-batch-row">
                    <div class="category-field is-parent">
                        <label class="form-label">Golongan</label>
                        <select name="${golonganName}" class="js-golongan form-select" data-width="100%"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${kodeName}" type="text" value="${kodeValue}" placeholder="Contoh: ABT">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-name">
                        <label class="form-label">Main Golongan Obat</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-shape-plus-outline"></i></span>
                            <input class="form-control" name="${namaName}" type="text" value="${namaValue}" placeholder="Masukkan nama main golongan">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="category-field is-action">
                            <button type="button" class="btn btn-outline-danger category-row-remove remove-main-golongan-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#mainGolonganModalLabel').text('Tambah Main Golongan');
            $('#mainGolonganModalSubtitle').text('Pilih golongan lalu tambahkan kelompok turunannya.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#mainGolonganId').val('');
            $('#mainGolonganBatchToolbar').show();
            $form.trigger('reset');
            CategoryUI.clearValidation(formSelector);
            $(wrapperSelector).html(mainGolonganRow());
            loadGolonganOptions($(wrapperSelector).find('.js-golongan'));
            CategoryUI.updateBatchCount(wrapperSelector, '#mainGolonganRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            const $row = $(mainGolonganRow());
            $(wrapperSelector).append($row);
            loadGolonganOptions($row.find('.js-golongan'));
            CategoryUI.updateBatchCount(wrapperSelector, '#mainGolonganRowCount');
        });

        $(document).on('click', '.remove-main-golongan-input', function() {
            if ($(wrapperSelector).find('.category-batch-row').length <= 1) {
                const $row = $(this).closest('.category-batch-row');
                $row.find('input').val('');
                $row.find('select').val('').trigger('change');
                CategoryUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            const $row = $(this).closest('.category-batch-row');
            $row.find('.js-golongan').each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
            });
            $row.remove();
            CategoryUI.updateBatchCount(wrapperSelector, '#mainGolonganRowCount');
        });

        const mainGolonganTable = $('#tableMainGolongan').DataTable(CategoryUI.dataTableOptions({
            ajax: {
                url: "{{ route("golongan.mainGolongan.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'golongan_id',
                    name: 'golongan_id',
                    render: function(data) {
                        return CategoryUI.parentBadge(data, 'mdi-shape-outline');
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
                        return CategoryUI.identity(data, 'Main golongan');
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
            table: mainGolonganTable,
            tableSelector: '#tableMainGolongan',
            searchSelector: '#mainGolonganSearch',
            totalTarget: '#mainGolonganTotal',
            filteredTarget: '#mainGolonganFiltered',
            selectedTarget: '#mainGolonganSelected'
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const mainGolonganId = $('#mainGolonganId').val();
            const url = mainGolonganId ?
                "{{ route("golongan.mainGolongan.update", ":id") }}".replace(':id', mainGolonganId) :
                "{{ route("golongan.mainGolongan.store") }}";
            const method = mainGolonganId ? 'PUT' : 'POST';
            const normalHtml = mainGolonganId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            CategoryUI.clearValidation(formSelector);
            CategoryUI.setButtonLoading(submitSelector, true, mainGolonganId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        CategoryUI.toast('success', response.message);
                        mainGolonganTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = CategoryUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        CategoryUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    CategoryUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan main golongan.');
                },
                complete: function() {
                    CategoryUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editMainGolongan = function(id) {
            $.ajax({
                url: "{{ route("golongan.mainGolongan.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#mainGolonganModalLabel').text('Edit Main Golongan');
                    $('#mainGolonganModalSubtitle').text('Perbarui induk, kode, dan nama main golongan.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#mainGolonganBatchToolbar').hide();
                    $('#mainGolonganId').val(response.id);
                    $(wrapperSelector).html(mainGolonganRow('edit', response));
                    loadGolonganOptions($(wrapperSelector).find('.js-golongan'), response.golongan_id);
                    CategoryUI.clearValidation(formSelector);
                    CategoryUI.updateBatchCount(wrapperSelector, '#mainGolonganRowCount');
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data main golongan tidak bisa dimuat.');
                }
            });
        };

        window.deleteMainGolongan = function(id) {
            Swal.fire({
                title: 'Hapus main golongan?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("golongan.mainGolongan.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            CategoryUI.toast('success', 'Berhasil Dihapus', response.message);
                            mainGolonganTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus main golongan.', 'error');
                    }
                });
            });
        };

        function resetMainGolonganExcelForm() {
            $('#mainGolonganExcelForm')[0].reset();
            const dropify = $('#mainGolonganExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#mainGolonganModalExcell').on('hidden.bs.modal', resetMainGolonganExcelForm);

        $('#mainGolonganDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("golongan.mainGolongan.exportTemplate") }}";
        });

        $('#mainGolonganSubmitExcel').on('click', function() {
            const fileInput = $('#mainGolonganExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#mainGolonganExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#mainGolonganExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
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
            CategoryUI.setButtonLoading('#mainGolonganSubmitExcel', true, 'Mengupload...', normalHtml);

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
                url: "{{ route("golongan.mainGolongan.import") }}",
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
                                $('#mainGolonganModalExcell').modal('hide');
                                mainGolonganTable.ajax.reload(null, false);
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
                    CategoryUI.setButtonLoading('#mainGolonganSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
